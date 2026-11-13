<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Looks up the freely licensed lead photo of Wikipedia articles, with its Commons credit.
 *
 * An article without a free photo, or a Wikipedia that cannot be reached, simply yields no photo.
 */
class WikipediaPhotos
{
    /**
     * Wikipedia API endpoint used to look up the lead photo of an article.
     */
    protected const API = 'https://en.wikipedia.org/w/api.php';

    /**
     * Width in pixels of the photo thumbnails to request.
     */
    protected const PHOTO_WIDTH = 1280;

    /**
     * Seconds to wait for Wikipedia to accept a connection, and for its whole answer.
     */
    protected const CONNECT_TIMEOUT = 5;

    protected const TIMEOUT = 10;

    /**
     * Look up the free lead photo of each article, keyed by the article title as given.
     *
     * @param  array<int, string>  $articles
     * @return array<string, array{photo_url: string, photo_source_url: ?string, photo_author: ?string, photo_licence: ?string, photo_licence_url: ?string, photo_attribution_required: bool}>
     */
    public function forArticles(array $articles): array
    {
        $pageImages = $this->query([
            'prop' => 'pageimages',
            'piprop' => 'name',
            'pilicense' => 'free',
            'redirects' => 1,
        ], $articles);

        $fileTitles = collect($pageImages)
            ->filter(fn (array $page) => isset($page['pageimage']))
            ->map(fn (array $page) => 'File:'.str_replace('_', ' ', $page['pageimage']));

        $imageInfo = $this->query([
            'prop' => 'imageinfo',
            'iiprop' => 'url|extmetadata',
            'iiurlwidth' => self::PHOTO_WIDTH,
            'iiextmetadatafilter' => 'Artist|LicenseShortName|LicenseUrl|AttributionRequired',
        ], $fileTitles->values()->all());

        return $fileTitles
            ->map(fn (string $fileTitle) => isset($imageInfo[$fileTitle]['imageinfo'][0])
                ? $this->photoAttributes($imageInfo[$fileTitle]['imageinfo'][0])
                : null)
            ->filter()
            ->all();
    }

    /**
     * Query the Wikipedia API for the given titles, keyed by the requested title.
     *
     * @param  array<string, string|int>  $parameters
     * @param  array<int, string>  $titles
     * @return array<string, array<string, mixed>>
     */
    protected function query(array $parameters, array $titles): array
    {
        if ($titles === []) {
            return [];
        }

        try {
            $response = Http::withUserAgent(config('app.name').' car inventory (Laravel HTTP client)')
                ->connectTimeout(self::CONNECT_TIMEOUT)
                ->timeout(self::TIMEOUT)
                ->get(self::API, [
                    'action' => 'query',
                    'format' => 'json',
                    'formatversion' => 2,
                    'titles' => implode('|', $titles),
                    ...$parameters,
                ]);
        } catch (ConnectionException) {
            return [];
        }

        if ($response->failed()) {
            return [];
        }

        return $this->pagesByRequestedTitle($response, $titles);
    }

    /**
     * Map each requested title to its page, following title normalisation and redirects.
     *
     * @param  array<int, string>  $titles
     * @return array<string, array<string, mixed>>
     */
    protected function pagesByRequestedTitle(Response $response, array $titles): array
    {
        $pages = collect($response->json('query.pages', []))->keyBy('title');

        $renames = collect([...$response->json('query.normalized', []), ...$response->json('query.redirects', [])])
            ->pluck('to', 'from');

        return collect($titles)
            ->mapWithKeys(function (string $title) use ($pages, $renames) {
                $normalised = $renames->get($title, $title);

                return [$title => $pages->get($renames->get($normalised, $normalised))];
            })
            ->filter()
            ->all();
    }

    /**
     * Build the photo columns from a Commons image info record.
     *
     * @param  array<string, mixed>  $info
     * @return array{photo_url: string, photo_source_url: ?string, photo_author: ?string, photo_licence: ?string, photo_licence_url: ?string, photo_attribution_required: bool}
     */
    protected function photoAttributes(array $info): array
    {
        $metadata = fn (string $key): ?string => isset($info['extmetadata'][$key]['value'])
            ? Str::limit(trim(html_entity_decode(strip_tags($info['extmetadata'][$key]['value']))), 250)
            : null;

        return [
            'photo_url' => $info['thumburl'] ?? $info['url'],
            'photo_source_url' => $info['descriptionurl'] ?? null,
            'photo_author' => $metadata('Artist'),
            'photo_licence' => $metadata('LicenseShortName'),
            'photo_licence_url' => $metadata('LicenseUrl'),
            'photo_attribution_required' => $metadata('AttributionRequired') === 'true',
        ];
    }
}
