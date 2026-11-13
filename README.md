# Développement assisté par agent

Le matériel de la présentation « Développement assisté par agent » (3W Québec, novembre 2026) :

- `slides/` : les diapositives, en PowerPoint et en PDF, avec leur source Marp ;
- `prompts/` : les 8 prompts complets de la démo, dans l'ordre ;
- le reste : le projet de départ, une application Laravel 13 + Inertia v2 + Vue 3 (Breeze) avec Pest et Laravel Boost, sans aucune fonctionnalité. Les prompts la transforment en système d'inventaire d'autos.

## Prérequis

- Docker (l'application roule dans Laravel Sail : PHP 8.5, MySQL, Redis, Mailpit)
- [Claude Code](https://claude.com/claude-code)

## Installer le projet de départ

```bash
docker run --rm -v "$(pwd):/app" -w /app composer/composer install --ignore-platform-reqs
cp .env.example .env
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed      # crée test@example.com / password
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev                 # à garder ouvert dans un deuxième terminal
```

Ouvre <http://localhost> et connecte-toi avec `test@example.com` / `password`.

Laravel Boost est déjà installé : `CLAUDE.md` (les règles du projet pour l'agent), `.claude/skills/` et `.mcp.json` (le serveur MCP de Boost). Lance `claude` à la racine du projet, puis vérifie avec `/mcp` que `laravel-boost` est connecté.

## Rouler les prompts

Un prompt à la fois, dans l'ordre. Chaque prompt part du résultat du précédent.

| # | Fichier | Ce que ça ajoute |
|---|---|---|
| 1 | `prompts/01-page-des-voitures.md` | Page des voitures avec recherche et photos |
| 2 | `prompts/02-inventaire-en-tdd.md` | Système d'inventaire en TDD (statuts, ajout, modification, suppression) |
| 3 | `prompts/03-50-vehicules.md` | 50 véhicules réalistes, avec des trous voulus dans les données |
| 4 | `prompts/04-page-edition.md` | Page d'édition complète, formulaire partagé |
| 5 | `prompts/05-sept-onglets.md` | 7 onglets, dont l'équipement et l'historique |
| 6 | `prompts/06-tableau-de-bord.md` | Tableau de bord avec jauges |
| 7 | `prompts/07-rappels-de-securite.md` | Rappels de sécurité (API publique de la NHTSA) |
| 8 | `prompts/08-donnees-completes.md` | Données d'exemple complètes |

Dans Claude Code, colle le contenu du fichier. Ou, sans interaction :

```bash
claude -p "$(cat prompts/01-page-des-voitures.md)" --permission-mode auto --mcp-config .mcp.json
```

Les prompts 2 à 8 se terminent tous par le même texte : l'ordre BDD (tests d'abord, ROUGE, puis VERT), les composants à réutiliser, et ce que « fini » veut dire.

Après chaque prompt, relis avant de passer au suivant : les tests, le diff, et les pages dans le navigateur. Puis fais un commit. `prompts/verifications.md` contient les questions à poser à l'agent en chemin.

Lors de la préparation, chaque prompt a pris de 4 à 9 minutes à un agent qui partait sans contexte, environ 50 minutes en tout, pour finir avec 259 tests Pest. Tes résultats vont varier : l'agent ne fait jamais deux fois exactement la même chose.

Le prompt 7 a été simplifié après la préparation : il n'a pas été roulé tel quel.

## Recommencer

```bash
git checkout . && git clean -fd
./vendor/bin/sail artisan migrate:fresh --seed
```

## Les diapositives

`slides/developpement-assiste-par-agent.pptx` et `.pdf` sont générés avec [Marp](https://marp.app) à partir de `slides/deck.md` et du thème `slides/theme-aet.css`. Pour les régénérer :

```bash
cd slides
docker run --rm --init -e MARP_USER=$(id -u):$(id -g) -v "$(pwd):/home/marp/app" \
  marpteam/marp-cli:v4.1.2 deck.md --theme-set theme-aet.css --html --allow-local-files \
  --pptx -o developpement-assiste-par-agent.pptx
```

Remplace `--pptx -o ….pptx` par `--pdf -o ….pdf` pour le PDF. Les sources citées sont sur la dernière diapositive.
