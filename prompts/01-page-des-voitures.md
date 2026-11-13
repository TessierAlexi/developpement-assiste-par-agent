Ajoute un inventaire d'autos avec photos : une vue liste et une vue détail, accessibles seulement après connexion, avec un lien dans le menu principal.

Travaille en ordre BDD, comme une boucle TDD rouge-vert :
1. Écris d'abord des tests Pest (feature) qui décrivent le comportement : les invités sont redirigés, la liste montre les autos les plus récentes en premier avec pagination, la page détail montre une auto, une auto inconnue retourne une 404, le seeder donne une photo à chaque auto.
2. Roule-les et montre-moi qu'ils échouent (ROUGE) avant que l'implémentation existe.
3. Implémente par petites tranches jusqu'à ce qu'ils passent (VERT), puis refactorise.

Interface : bâtis les pages avec des composants Vue. Réutilise les composants Breeze existants dans resources/js/Components partout où ça s'applique (layout, liens de navigation, champ texte + libellé pour une boîte de recherche, boutons, la modale pour agrandir une photo), et extrais de petits composants réutilisables (carte d'auto, photo avec image de repli, pagination). Garde le nouveau code dans les dossiers existants. Garde l'interface en anglais comme le reste de l'application.

Photos : trouve une source de photos d'autos genre placekitten pour le seeder. Elle doit être gratuite, sans clé d'API, sans filigrane, et vérifiée pour retourner de vraies photos d'autos aujourd'hui. Si une photo ne charge pas, affiche une image de repli, et crédite la source de la photo sur la page détail quand sa licence l'exige. Simule (fake) les appels HTTP dans les tests.

Ajoute environ 30 autos réalistes dans le seeder : des marques, modèles et versions qui vont ensemble.

Quand tu as fini, roule toute la suite de tests, Pint et `npm run build` avec Sail, et montre-moi la sortie des tests ROUGE et VERT. Ne fais pas de commit.
