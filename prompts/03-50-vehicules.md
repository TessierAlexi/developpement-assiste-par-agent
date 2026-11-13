Remplace les données d'exemple par 50 véhicules réalistes : des marques, modèles et versions qui vont ensemble, des prix et kilométrages crédibles pour l'année, et une répartition de statuts (surtout Available, quelques OnHold et Sold).

Mets volontairement des trous dans les données, comme dans une vraie base : quelques autos sans photo, sans prix, sans description ou sans couleur. L'interface doit rester propre avec ces trous (valeur « — » ou message clair, jamais « null », « $0 » ou une page qui plante). Teste que la liste et la page détail affichent correctement une auto à qui il manque ces champs.

Garde le comportement actuel des photos (Wikipédia, avec crédit), et simule les appels HTTP dans les tests.

Travaille en ordre BDD, comme une boucle TDD rouge-vert : écris d'abord les tests Pest (feature, et unit seulement pour la logique pure), roule-les et montre-moi qu'ils échouent (ROUGE), puis implémente par petites tranches jusqu'à VERT, puis refactorise.

Pars du code existant : lis le modèle Car, CarController, les pages resources/js/Pages/Cars et les composants resources/js/Components avant d'écrire quoi que ce soit, et réutilise-les (AuthenticatedLayout, InputLabel, TextInput, InputError, Checkbox, PrimaryButton, SecondaryButton, DangerButton, Modal, CarCard, CarPhoto, Pagination, formatters.js). Garde le nouveau code dans les dossiers existants. Garde l'interface en anglais comme le reste de l'application. N'ajoute aucune dépendance.

Ne touche pas aux dossiers prompts et slides, ni à README.md.

Quand tu as fini, roule toute la suite de tests, Pint et `npm run build` avec Sail, et montre-moi la sortie des tests ROUGE et VERT. Ne fais pas de commit.
