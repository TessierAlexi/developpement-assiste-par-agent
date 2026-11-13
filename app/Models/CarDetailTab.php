<?php

namespace App\Models;

/**
 * The tabs of the car detail page, in the order they are shown; the value is what appears in the URL.
 */
enum CarDetailTab: string
{
    use OffersOptions;

    case Overview = 'overview';
    case Specifications = 'specifications';
    case Equipment = 'equipment';
    case Pricing = 'pricing';
    case Photos = 'photos';
    case History = 'history';
    case Notes = 'notes';

    /**
     * Get the human-readable name of the tab.
     */
    public function label(): string
    {
        return $this->name;
    }
}
