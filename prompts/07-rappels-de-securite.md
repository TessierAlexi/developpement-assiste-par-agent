Ajoute une intégration d'API externe qui ne dépend pas du NIV : les rappels de sécurité.

1. Données : chaque auto du seeder et de la factory a un NIV fictif mais valide (17 caractères, somme de contrôle correcte, unique). Le NIV n'est pas un des trous volontaires.
2. Rappels de sécurité : utilise l'API publique gratuite de la NHTSA, sans clé, qui cherche par marque, modèle et année (https://api.nhtsa.gov/recalls/recallsByVehicle?make=…&model=…&modelYear=…). Pour une auto, affiche ses rappels dans l'onglet Overview de la page détail : numéro de campagne, composante, résumé, conséquence et correctif, avec un message clair quand il n'y en a aucun ou quand l'API ne répond pas. Un bouton « Check recalls » relance la recherche.
3. Mets cette logique dans un service testable, avec un délai d'attente et une mise en cache raisonnable (par marque, modèle et année), et n'invente jamais de rappel. La route qui la déclenche est nommée, protégée par l'authentification et limitée en débit. Simule tous les appels HTTP dans les tests, y compris les erreurs.

Travaille en ordre BDD, comme une boucle TDD rouge-vert : écris d'abord les tests Pest (feature, et unit seulement pour la logique pure), roule-les et montre-moi qu'ils échouent (ROUGE), puis implémente par petites tranches jusqu'à VERT, puis refactorise.

Pars du code existant : lis le modèle Car, CarController, les pages resources/js/Pages/Cars et les composants resources/js/Components avant d'écrire quoi que ce soit, et réutilise-les (AuthenticatedLayout, InputLabel, TextInput, InputError, Checkbox, PrimaryButton, SecondaryButton, DangerButton, Modal, CarCard, CarPhoto, Pagination, formatters.js). Garde le nouveau code dans les dossiers existants. Garde l'interface en anglais comme le reste de l'application. N'ajoute aucune dépendance.

Ne touche pas aux dossiers prompts et slides, ni à README.md.

Quand tu as fini, roule toute la suite de tests, Pint et `npm run build` avec Sail, et montre-moi la sortie des tests ROUGE et VERT. Ne fais pas de commit.
