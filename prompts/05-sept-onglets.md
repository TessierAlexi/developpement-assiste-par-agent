Réorganise la page détail d'une auto en 7 onglets : Overview, Specifications, Equipment, Pricing, Photos, History, Notes.

- Equipment : une liste d'équipements en 10 catégories (par exemple Safety, Comfort, Technology, Audio, Lighting, Seating, Exterior, Interior, Towing, Performance), environ 70 options au total, définies à un seul endroit côté PHP. On coche les options dans le formulaire d'édition, et l'onglet ne montre que celles qui sont cochées, regroupées par catégorie.
- History : chaque changement de statut est enregistré (ancien statut, nouveau statut, date, utilisateur) et affiché du plus récent au plus ancien.
- L'onglet actif se reflète dans l'URL (on peut partager un lien vers l'onglet Pricing).
- Les onglets sont un petit composant Vue réutilisable, accessible au clavier.

Travaille en ordre BDD, comme une boucle TDD rouge-vert : écris d'abord les tests Pest (feature, et unit seulement pour la logique pure), roule-les et montre-moi qu'ils échouent (ROUGE), puis implémente par petites tranches jusqu'à VERT, puis refactorise.

Pars du code existant : lis le modèle Car, CarController, les pages resources/js/Pages/Cars et les composants resources/js/Components avant d'écrire quoi que ce soit, et réutilise-les (AuthenticatedLayout, InputLabel, TextInput, InputError, Checkbox, PrimaryButton, SecondaryButton, DangerButton, Modal, CarCard, CarPhoto, Pagination, formatters.js). Garde le nouveau code dans les dossiers existants. Garde l'interface en anglais comme le reste de l'application. N'ajoute aucune dépendance.

Ne touche pas aux dossiers prompts et slides, ni à README.md.

Quand tu as fini, roule toute la suite de tests, Pint et `npm run build` avec Sail, et montre-moi la sortie des tests ROUGE et VERT. Ne fais pas de commit.
