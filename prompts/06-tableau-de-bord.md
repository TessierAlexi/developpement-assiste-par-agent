Remplace le tableau de bord vide par un vrai tableau de bord d'inventaire, avec des jauges.

- Des jauges en demi-cercle (un composant Vue réutilisable, en SVG, sans librairie) pour : la part de l'inventaire disponible, le nombre moyen de jours en inventaire par rapport à une cible de 60 jours, et les ventes du mois par rapport à un objectif de 10.
- Des tuiles avec la valeur totale de l'inventaire disponible, le nombre d'autos par statut, et la marge moyenne des autos vendues (prix demandé moins prix coûtant, en ignorant celles sans prix).
- Une liste des 5 autos en inventaire depuis le plus longtemps, avec un lien vers chacune.
- Calcule les statistiques dans un service testé (y compris avec un inventaire vide et des autos sans prix), pas dans le contrôleur ni dans Vue.

Travaille en ordre BDD, comme une boucle TDD rouge-vert : écris d'abord les tests Pest (feature, et unit seulement pour la logique pure), roule-les et montre-moi qu'ils échouent (ROUGE), puis implémente par petites tranches jusqu'à VERT, puis refactorise.

Pars du code existant : lis le modèle Car, CarController, les pages resources/js/Pages/Cars et les composants resources/js/Components avant d'écrire quoi que ce soit, et réutilise-les (AuthenticatedLayout, InputLabel, TextInput, InputError, Checkbox, PrimaryButton, SecondaryButton, DangerButton, Modal, CarCard, CarPhoto, Pagination, formatters.js). Garde le nouveau code dans les dossiers existants. Garde l'interface en anglais comme le reste de l'application. N'ajoute aucune dépendance.

Ne touche pas aux dossiers prompts et slides, ni à README.md.

Quand tu as fini, roule toute la suite de tests, Pint et `npm run build` avec Sail, et montre-moi la sortie des tests ROUGE et VERT. Ne fais pas de commit.
