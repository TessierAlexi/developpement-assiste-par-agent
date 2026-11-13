Fais une page d'édition complète, d'environ 30 champs, avec un formulaire partagé entre la création et l'édition (un seul composant de formulaire réutilisé par Create et Edit).

Ajoute les champs techniques qui manquent pour un vrai concessionnaire : NIV (17 caractères, unique, optionnel), numéro de stock, couleurs extérieure et intérieure, moteur, cylindrée, nombre de cylindres, transmission, traction (FWD, RWD, AWD, 4WD), carburant, portes, places, consommation ville et autoroute, prix demandé, prix coûtant, date d'entrée, statut, description, notes internes. Regroupe-les en sections claires dans le formulaire.

Valide chaque champ dans les Form Requests (types, bornes réalistes, NIV valide), et affiche les erreurs sous chaque champ. Teste les règles de validation importantes et le fait que création et édition utilisent les mêmes règles.

Travaille en ordre BDD, comme une boucle TDD rouge-vert : écris d'abord les tests Pest (feature, et unit seulement pour la logique pure), roule-les et montre-moi qu'ils échouent (ROUGE), puis implémente par petites tranches jusqu'à VERT, puis refactorise.

Pars du code existant : lis le modèle Car, CarController, les pages resources/js/Pages/Cars et les composants resources/js/Components avant d'écrire quoi que ce soit, et réutilise-les (AuthenticatedLayout, InputLabel, TextInput, InputError, Checkbox, PrimaryButton, SecondaryButton, DangerButton, Modal, CarCard, CarPhoto, Pagination, formatters.js). Garde le nouveau code dans les dossiers existants. Garde l'interface en anglais comme le reste de l'application. N'ajoute aucune dépendance.

Ne touche pas aux dossiers prompts et slides, ni à README.md.

Quand tu as fini, roule toute la suite de tests, Pint et `npm run build` avec Sail, et montre-moi la sortie des tests ROUGE et VERT. Ne fais pas de commit.
