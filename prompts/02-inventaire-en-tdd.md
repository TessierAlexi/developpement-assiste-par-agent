Transforme la liste d'autos en vrai système d'inventaire. Prends comme modèle une application d'inventaire de concessionnaire classique et garde seulement les fonctionnalités les plus simples. Sépare les responsabilités, pense testabilité.

- Chaque auto a un statut (enum PHP : Available, OnHold, Sold), un numéro de stock unique et une date d'entrée en inventaire. Une auto vendue garde sa date de vente.
- On peut ajouter, modifier et supprimer une auto (seulement les champs déjà existants plus les nouveaux, pas de nouveaux champs techniques pour l'instant), et changer son statut.
- La liste montre le statut et peut être filtrée par statut, en plus de la recherche actuelle.
- Architecture : un service d'inventaire pour la logique (création, mise à jour, changement de statut), des Form Requests pour la validation, des contrôleurs minces, des routes nommées, et 4 pages Vue au total pour l'inventaire (Index, Show, Create, Edit).

Travaille en ordre BDD, comme une boucle TDD rouge-vert : écris d'abord les tests Pest (feature, et unit seulement pour la logique pure), roule-les et montre-moi qu'ils échouent (ROUGE), puis implémente par petites tranches jusqu'à VERT, puis refactorise.

Pars du code existant : lis le modèle Car, CarController, les pages resources/js/Pages/Cars et les composants resources/js/Components avant d'écrire quoi que ce soit, et réutilise-les (AuthenticatedLayout, InputLabel, TextInput, InputError, Checkbox, PrimaryButton, SecondaryButton, DangerButton, Modal, CarCard, CarPhoto, Pagination, formatters.js). Garde le nouveau code dans les dossiers existants. Garde l'interface en anglais comme le reste de l'application. N'ajoute aucune dépendance.

Ne touche pas aux dossiers prompts et slides, ni à README.md.

Quand tu as fini, roule toute la suite de tests, Pint et `npm run build` avec Sail, et montre-moi la sortie des tests ROUGE et VERT. Ne fais pas de commit.
