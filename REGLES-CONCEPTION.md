# REGLES DE CONCEPTION - Alliance Groupe (loi permanente)

> Règle posée par Fabrice le 2026-11-20. Elle s'applique à CHAQUE travail d'interface : page, bloc, carrousel, formulaire, popup, email, document imprimé.
> Le hook de démarrage de session affiche ce fichier en entier. Ne jamais l'effacer, seulement le compléter (une ligne au journal de `CARTOGRAPHIE.md` à chaque changement).

## 1. Tous les supports, à chaque fois

Pour chaque travail, on définit une configuration pour CHAQUE support, on ne se contente pas de "faire du responsive" :

| Support | Largeur | Ce qu'on fixe |
|---|---|---|
| Mobile | jusqu'à 600 px (tester 360 et 390) | tailles, marges, nombre de colonnes, vitesses, gestes tactiles |
| Tablette | 601 à 1024 px (tester 768 et 1024) | idem, valeurs propres à la tablette |
| PC | 1025 px et plus (tester 1440) | idem, survol souris, grand écran |

Les valeurs de chaque support sont écrites dans le code (commentaire d'en-tête ou variables CSS), jamais laissées au hasard.

## 2. Marges et contraintes : rien ne dépasse

- Gouttière du site : 20 px de chaque côté (16 px minimum sur mobile). Le contenu occupe exactement la largeur utile.
- La page ne doit jamais avoir de défilement horizontal : `document.documentElement.scrollWidth` doit rester inférieur ou égal à `innerWidth`.
- Une marge négative n'est permise que si elle est égale au rembourrage du parent (le bloc touche alors le bord utile, pas plus).
- Images en `max-width:100%`, textes qui passent à la ligne, boutons dont le texte ne se coupe pas mal (réduire le rembourrage ou la police sur mobile plutôt que laisser déborder).
- Laisser la place aux ombres, aux badges qui dépassent (étoile "Le plus choisi") et aux encoches des téléphones.

## 3. Fluidité

- Mouvement continu : `requestAnimationFrame` avec le temps réel écoulé (jamais un `setInterval` à pas fixe), animations en `transform` et `opacity`.
- Tailles et espacements avec `clamp()` ou des variables CSS, pas des sauts brutaux.
- Respecter `prefers-reduced-motion` : pas de mouvement automatique, version statique lisible.
- Mettre en pause hors écran (IntersectionObserver) et quand l'onglet est caché.

## 4. Interactions propres à chaque support

- PC : le survol de la souris met en pause (`pointerenter` et `pointerleave` avec `pointerType` différent de `touch`).
- Mobile : le toucher met en pause (`touchstart`), reprise un peu après (`touchend`, 3 s). Ne jamais mettre une fonction essentielle derrière `:hover` seul.
- Clavier : `:focus-visible` met en pause et reste visible.
- Carrousel automatique : lent (environ 22 px/s mobile, 26 tablette, 30 PC), boucle sans couture, glissable au doigt, pause au survol ou au toucher.

## 5. Vérifier avant d'annoncer "c'est fait"

Test Playwright aux largeurs 360, 390, 768, 1024 et 1440 px : pas de débordement horizontal, pas d'erreur de script, mouvement et pauses mesurés, capture regardée. Méthode qui marche : rendre le gabarit PHP avec des fonctions WordPress factices (`get_header`, `esc_html`, `home_url`...) dans un petit script PHP, ouvrir le HTML obtenu dans Playwright. Ne rien annoncer qui n'a pas été vu.

## Applications connues

- 2026-11-20 : carrousel des offres de l'accueil (Essentiel, Pro, Boutique), module `inc/ag-accueil-carrousel.php`. PC 380 px de carte et 30 px/s, tablette 340 px et 26 px/s, mobile 80 % de l'écran (max 320 px) et 22 px/s.
