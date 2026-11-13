Le seeder ne remplit pas les champs ajoutés depuis : aucune des 50 autos n'a de prix coûtant, de moteur, de traction ou d'autres caractéristiques techniques, aucune n'a d'équipement coché, et l'historique des statuts est vide. Le tableau de bord affiche donc « — » pour la marge, et les onglets Specifications, Equipment et History sont vides. Corrige les données d'exemple pour que la démo soit crédible.

- Chaque auto reçoit des caractéristiques techniques cohérentes avec sa marque, son modèle et sa version : moteur, cylindrée, cylindres, traction, portes, places, consommation ville et autoroute, couleur intérieure.
- Un prix coûtant réaliste, plus bas que le prix demandé (une marge de 5 à 15 %), sauf pour les autos volontairement sans prix.
- De l'équipement plausible pour chaque version (une version de base en a moins qu'une version haut de gamme), choisi dans la liste d'équipements existante.
- Un historique de statuts cohérent avec le statut actuel : les autos en attente et vendues ont leurs changements de statut, datés après leur date d'entrée. Quelques ventes tombent dans le mois en cours.
- Garde les trous volontaires actuels (sans photo, sans prix, sans description, sans couleur), les NIV fictifs valides et le comportement actuel des photos.
- Teste ces garanties dans le test du seeder (par exemple : chaque auto a ses caractéristiques techniques, le prix coûtant est sous le prix demandé, l'historique se termine par le statut actuel, l'équipement vient de la liste existante).

Travaille en ordre BDD, comme une boucle TDD rouge-vert : écris d'abord les tests Pest (feature, et unit seulement pour la logique pure), roule-les et montre-moi qu'ils échouent (ROUGE), puis implémente par petites tranches jusqu'à VERT, puis refactorise.

Pars du code existant : lis le modèle Car, CarController, les pages resources/js/Pages/Cars et les composants resources/js/Components avant d'écrire quoi que ce soit, et réutilise-les (AuthenticatedLayout, InputLabel, TextInput, InputError, Checkbox, PrimaryButton, SecondaryButton, DangerButton, Modal, CarCard, CarPhoto, Pagination, formatters.js). Garde le nouveau code dans les dossiers existants. Garde l'interface en anglais comme le reste de l'application. N'ajoute aucune dépendance.

Ne touche pas aux dossiers prompts et slides, ni à README.md.

Quand tu as fini, roule toute la suite de tests, Pint et `npm run build` avec Sail, et montre-moi la sortie des tests ROUGE et VERT. Ne fais pas de commit.
