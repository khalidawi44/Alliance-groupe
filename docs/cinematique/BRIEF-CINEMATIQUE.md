# Brief — site Alliance Groupe « cinématique »

Note de la session **Cowork/visuels** (images, vidéos, montages) à la session **code** (thème WordPress).
Écrit le 19/08/2026. Tout ce qui est cité ici est **déjà dans le dépôt**, chemins exacts en bas.

---

## 1. D'où vient la demande

Fabrice a envoyé une vidéo TikTok (compte **@webloved**) qui montre un site défilé à la souris, avec une légende narquoise : *« Claude code can't do this »*. Il demande qu'on fasse **aussi bien, voire mieux**.

J'ai analysé la vidéo image par image. Ce qu'on y voit exactement :

- Un site éditorial en plein écran, fond **bleu ciel aplati** et **blanc cassé**, avec des **tableaux classiques détourés** (personnages en toge, drapés Renaissance) posés en collage sur ces aplats.
- Un fil rouge iconographique tenu de bout en bout : le **verger** — un poirier, une **poire dorée en 3D** qui tourne lentement, des personnages qui plantent, arrosent, récoltent. Chaque section reprend la métaphore.
- Des **transitions par désagrégation** : une main, une image, se dissolvent en particules quand on passe d'un chapitre au suivant.
- Du texte éditorial court, très gros, en serif, qui se compose au scroll (« No fees. A share of the upside. »).
- Un défilement **fluide et amorti** (scroll inertiel), pas le scroll natif saccadé.

**Le point important** : la prouesse n'est pas dans le code. GSAP + ScrollTrigger + un scroll amorti + un shader de dissolution, c'est du métier connu. Ce qui fait le niveau, c'est **la direction artistique et la cohérence de l'iconographie**. C'est là qu'il faut mettre l'effort.

---

## 2. Ce que j'ai déjà fait (côté visuels)

### a) Une démo autonome qui prouve la faisabilité
`docs/cinematique/alliance-demo.html` — une seule page HTML, tout embarqué (images en base64, GSAP/Lenis inlinés), ouvrable hors ligne. Fabrice l'a validée : *« c'est trop beau »*.

Elle contient, dans l'ordre :

1. **Hero** — baie de Naples en fond avec parallaxe lente, **l'égérie en vidéo** (extrait du clip *welcome*, elle tend la main vers le visiteur), titre `De Naples à Nantes` dont chaque mot monte à l'ouverture, lion en filigrane en `mix-blend-mode: screen`. **Au scroll, on se rapproche de sa main** : la vidéo est agrandie (`scale: 2.9`) avec `transform-origin: 62% 74%` — pile sur la main — puis disparaît en fondu. C'est la demande explicite de Fabrice.
2. **Dissolution** — une section `sticky` de 250svh : la photo du bureau de Naples est dessinée **nette** sur un `<canvas>`, et une grille de cellules de 12 px s'en détache progressivement en **carrés dorés** qui montent et rétrécissent, pilotée par la progression du scroll.
3. **Trois chapitres** — image révélée par `clip-path: inset(100% 0 0 0)` → `inset(0)`, photo qui glisse à l'intérieur de son cadre (parallaxe interne), numéros 01/02/03 en or.
4. **Bandeau défilant**, puis **révélation du lion** (scale + flou qui se lève, `scrub`), puis CTA.

### b) Les visuels d'art direction, générés pour ce projet
Dans `alliance-groupe-theme/assets/images/cinematique/` :

| Fichier | Ce que c'est | À quoi ça sert |
|---|---|---|
| `lion-or.jpg` | Tête de lion sculptée en or massif, fond noir pur, 1600² | L'objet-totem : révélation au scroll, favicon animé, séparateurs |
| `main-poussiere-or.jpg` | Main de marbre (style Michel-Ange) dont les doigts partent en poussière d'or, fond noir, 1600² | La transition « dissolution » — écho direct de la vidéo de référence |
| `allegorie-naples.jpg` | Peinture Renaissance : deux figures se serrant la main devant la baie de Naples, rameau d'olivier, Vésuve, 2400×1340 | **Le tableau-clé.** C'est notre « verger » à nous : l'alliance |
| `marbre-noir-or.jpg` | Marbre noir veiné d'or, 2400×1340 | Fond de section, cartouches, bandeaux |

Le fond noir de `lion-or` et `main-poussiere-or` se détache très bien en `mix-blend-mode: screen` sur un fond sombre — **pas besoin de détourage**.

### c) Ce qui existait déjà et qu'il faut réutiliser
Inventaire complet dans le doc projet `visuels-alliance.md`. En résumé : bureaux de Naples (`team/`), baie de nuit (`cities/baie_naples_nuit.jpg`), **égérie détourée** (`egerie/egerie-cutout.png`), les **3 clips vidéo** de l'égérie (`assets/videos/egerie_pub/`), le **montage 41 s** (`assets/videos/hero-egerie-long.mp4`), les cartes d'offres (`images/offres/`).

---

## 3. Ce qu'il te reste à faire (session code)

### Socle technique
- **Scroll amorti** : Lenis (`lenis` sur npm, ~3 ko). Sans lui, aucune animation au scroll ne « colle » — c'est 50 % de la sensation.
- **Séquences** : GSAP + ScrollTrigger. Brancher `lenis.on('scroll', ScrollTrigger.update)`.
- **Chargement** : `wp_enqueue_script` avec `defer`, versionnés par `filemtime`. Pas de plugin d'animation, pas de page builder.
- **3D (optionnel, phase 2)** : three.js pour faire tourner un lion doré en `.glb` — il y a déjà des modèles dans `assets/images/img_3d/` et Fabrice a Blender sur son poste.

### Règles d'animation (c'est ça qui fait « cher » ou « cheap »)
- Une seule famille d'easing : `power3.out` pour les entrées, `expo.out` pour les titres, `none` + `scrub` pour tout ce qui suit le doigt.
- Durées : 0,9 s à 1,3 s pour les entrées ; `stagger` de 0,08 à 0,12 s entre mots ou lignes.
- **Jamais d'animation sur `top/left/width`** — uniquement `transform` et `opacity`, plus `clip-path` pour les révélations.
- Le scroll ne doit **jamais** déclencher deux effets concurrents sur la même zone : un mouvement dominant par écran.
- Prévoir `@media (prefers-reduced-motion: reduce)` : tout devient statique, rien ne casse.

### Structure de page proposée (accueil)
1. Hero égérie + approche de la main (repris tel quel de la démo).
2. **Le tableau** : `allegorie-naples.jpg` en plein écran, texte court par-dessus — « Une alliance, pas un prestataire ». Effet Ken Burns très lent.
3. **Dissolution** : reprendre le canvas de la démo, mais avec `main-poussiere-or.jpg` en surimpression — c'est le clin d'œil assumé à la vidéo de référence.
4. Chapitres 01/02/03 (artisan / sécurité / IA) avec les photos de bureaux.
5. Offres : les trois cartes de `images/offres/`, révélées en cascade.
6. Révélation du lion + CTA.

### Performance — non négociable
- Images en **WebP** avec `srcset` (le thème sert du JPEG aujourd'hui) ; `loading="lazy"` partout sauf le hero, qui prend `fetchpriority="high"`.
- La vidéo du hero : `muted autoplay loop playsinline` + `poster` obligatoire, et une version ≤ 300 ko (celle de la démo fait 212 ko pour 3,4 s en 680 px de large — c'est le bon calibre).
- Le canvas de dissolution redessine à chaque `onUpdate` : garder la cellule à 12 px et ne redessiner **que** les cellules dont l'état change si tu veux gagner encore.
- Objectif : LCP < 2,5 s sur mobile 4G. Un site spectaculaire qui met 6 s à charger est un site raté.

### Le piège WordPress
Ces sites-là sont faits en Astro ou Next. Sur WP c'est faisable en thème sur-mesure, mais : pas de constructeur de page, CSS et JS écrits à la main, et surtout **une seule page traitée à fond** plutôt que dix pages tièdes. Commencer par l'accueil.

---

## 4. Comment faire *mieux* que la vidéo

Trois angles où on peut les dépasser, parce qu'on a une matière qu'ils n'ont pas :

1. **Le vrai lieu.** Eux ont des tableaux de musée ; nous avons Naples, le Vésuve, une vraie terrasse, un vrai bureau en marbre noir. Le collage classique **plus** la photo réelle, c'est plus fort que le collage seul.
2. **L'égérie en mouvement.** Leur site est figé, images fixes animées. Nous avons 15 s de clips et un montage de 41 s. Une présence humaine qui bouge et qui tend la main bat une nature morte.
3. **La cohérence de marque.** Le lion, l'or, les trois drapeaux : on a un emblème fort et déjà décliné partout. Eux tiennent sur une poire.

Ce qu'il ne faut **pas** copier : leur bleu ciel et leurs toiles de musée. Notre palette est or `#d4b45c` → `#f4d06f` sur noir `#05050a`, typo **Inter** + **Playfair Display** en italique pour les accents. Si on garde ça et qu'on met le même niveau d'exigence sur les timings, le résultat sera plus haut de gamme que la référence.

---

## 5. Répartition

- **Moi (session visuels)** : toutes les images et vidéos — génération, détourage, montage, cartes, textures. Je pousse tout sur `main` dès que c'est validé, dans `assets/images/…` et `assets/videos/…`. Si tu as besoin d'un visuel précis (un lion sous un autre angle, une main qui se dissout, une texture), demande-le dans ce fichier ou dans un commit, je le produis.
- **Toi (session code)** : le thème, les animations, l'intégration, les performances. Ne perds pas de temps à chercher des images : demande, je fabrique.

## 6. Chemins exacts

```
docs/cinematique/alliance-demo.html                     ← la démo validée par Fabrice
docs/cinematique/BRIEF-CINEMATIQUE.md                   ← ce fichier
alliance-groupe-theme/assets/images/cinematique/        ← lion-or, main-poussiere-or, allegorie-naples, marbre-noir-or
alliance-groupe-theme/assets/images/offres/             ← cartes Essentiel / Pro / Boutique
alliance-groupe-theme/assets/images/egerie/             ← égérie détourée (png + webp)
alliance-groupe-theme/assets/images/cities/             ← baie_naples_nuit.jpg et autres villes
alliance-groupe-theme/assets/images/team/               ← bureaux de Naples, équipe, Fabrizio
alliance-groupe-theme/assets/videos/egerie_pub/         ← 3 clips sources de l'égérie (5 s chacun)
alliance-groupe-theme/assets/videos/hero-egerie-long.mp4 ← montage 41 s, 9 plans, + poster
```

## 7. Critères d'acceptation

- [ ] Le scroll est amorti, aucune saccade sur un portable de 2019.
- [ ] Le hero se rapproche de la main de l'égérie et enchaîne sans coupure sur la section suivante.
- [ ] Au moins une dissolution en particules dorées, pilotée par le scroll.
- [ ] `allegorie-naples.jpg` occupe un plein écran avec un texte court par-dessus.
- [ ] Le lion apparaît une seule fois, en révélation, jamais en décoration gratuite.
- [ ] LCP < 2,5 s sur mobile, et tout reste lisible avec `prefers-reduced-motion`.


---

## 8. Passage en revue de la page d'accueil actuelle — section par section

Fabrice demande que **rien ne manque** par rapport à la page d'accueil en ligne, et donne le droit de **changer les choses en les améliorant**. Voici l'état actuel (relevé dans `templates/page-accueil.php` au 19/08) et le traitement proposé pour chaque bloc.

### 1. En-tête / marque (`ag-lm__htop`)
*Aujourd'hui* : logo + eyebrow figés en haut à gauche.
*À faire* : barre qui se compacte au scroll (hauteur et opacité du fond en `scrub`), logo qui passe du lion complet au lion seul sous 80 px de scroll. Fond `backdrop-filter: blur(10px)` sur `rgba(5,5,10,.6)` seulement après le hero — jamais au-dessus du plein écran.

### 2. Hero égérie (`ag-lm__hero`)
*Aujourd'hui* : le montage vidéo 41 s en fond plein écran + texte en bas à gauche. C'est déjà bien.
*À améliorer* : reprendre les trois gestes de la démo — (a) titre dont chaque mot monte (`yPercent:115` → 0, `expo.out`, `stagger .09`), (b) **approche de la main** : sur la fin du hero, `scale` de la vidéo jusqu'à 2.9 avec `transform-origin` sur la main, opacité qui tombe, (c) lion en filigrane `mix-blend-mode: screen` qui grossit doucement. Ajouter un bouton « Audit gratuit » à côté du CTA principal.

### 3. Marquee (`ag-lm__mq`)
*Aujourd'hui* : bandeau texte qui défile en boucle, vitesse fixe.
*À améliorer* : le passer en **serif or sur marbre** (`assets/images/cinematique/marbre-noir-or.jpg` en fond, `background-attachment: fixed`), et **lier la vitesse à la vitesse de scroll** (ScrollTrigger `onUpdate` → `self.getVelocity()`), avec inversion du sens quand on remonte. C'est un détail, mais c'est exactement ce qui donne la sensation « site cher ».

### 4. Chapitres 01 / 02 / 03 (`ag-lm__chapters`)
*Aujourd'hui* : trois blocs texte + image, alternés, avec un simple fondu.
*À améliorer* : révélation par `clip-path: inset(100% 0 0 0)` → `inset(0)` en 1,25 s `power4.out`, photo qui glisse **à l'intérieur** de son cadre (`scale 1.22 → 1.02`, `yPercent -6 → 6`, `scrub`), numéros en Playfair or. Et surtout : **intercaler le tableau** `cinematique/allegorie-naples.jpg` en plein écran entre le chapitre 01 et le 02, avec une phrase courte par-dessus (« Une alliance, pas un prestataire ») et un Ken Burns très lent. C'est notre équivalent du « verger » de la vidéo de référence.

### 5. Transition dissolution (nouveau)
Insérer entre les chapitres et les offres une section `sticky` de ~250svh : photo du bureau de Naples dessinée nette sur `<canvas>`, cellules de 12 px qui s'envolent en carrés dorés selon la progression du scroll, avec `cinematique/main-poussiere-or.jpg` en surimpression `mix-blend-mode: screen`. Le code complet est dans `docs/cinematique/alliance-demo.html` — copiable tel quel.

### 6. Offres (`ag-lm__offres`)
*Aujourd'hui* : les trois cartes que j'ai produites (`assets/images/offres/`), un bouton par carte, la mention « Maintenance & hébergement à partir de 29 €/mois ».
*À améliorer* : entrée **en cascade** (`stagger .12`, `y:40`, `power3.out`), la carte **Pro** qui monte un peu plus haut et garde un halo or, léger `rotateY` au survol (max 4°, `transform-style: preserve-3d`), fond de section en marbre noir très assombri. Garder la note 29 €/mois mais en petit filet or centré. Ne pas animer les prix (compteurs qui défilent = daté).

### 7. Atelier IA — « Que créons-nous aujourd'hui ? » (`template-parts/atelier-gallery`)
*Aujourd'hui* : titre, sous-titre, filtres (Tous / IA / Créer / Sécurité) et une grille de cartes : Devis instantané, Refais mon site, Fait par l'IA, Studio créatif, Création de sites, Audit de sécurité…
*Ce qui cloche* : les vignettes sont des visuels « IA générique » (circuits dorés, cerveau lumineux) qui ne racontent rien et se ressemblent toutes.
*À améliorer* : (a) filtres animés en **FLIP** (`gsap.utils.toArray` + `Flip.getState`) plutôt qu'un `display:none` brutal ; (b) survol qui révèle la description en glissant depuis le bas ; (c) **je refais les 8 vignettes** dans la palette or/noir, avec des objets réels et une idée par carte (un devis qui s'écrit, un site qui se reconstruit, un bouclier de marbre, un plateau de tournage…). Dis-moi si tu veux que je les produise, c'est une demi-heure.

### 8. Modal devis + bandeau PWA
*Aujourd'hui* : modal « Lancer mon projet » et une invite d'installation d'application en bas.
*À améliorer* : ouverture du modal en `scale .96 → 1` + fond flouté, focus piégé dans la boîte (accessibilité), fermeture à `Échap`. Le bandeau PWA doit apparaître **après** le premier scroll, pas au chargement — il masque le contenu au premier coup d'œil (visible sur la capture de Fabrice).

### 9. Pied de page
*À ajouter* : la révélation du lion (scale + flou qui se lève, `scrub`) puis le mot ALLIANCE GROUPE très espacé, avant les mentions. C'est la respiration finale.

### Ordre de bataille conseillé
1. Le socle (Lenis + ScrollTrigger + réglages d'easing communs) — tout le reste en dépend.
2. Hero (approche de la main) et dissolution : ce sont les deux moments « waouh ».
3. Chapitres + tableau allégorique.
4. Offres et atelier.
5. En-tête, modal, pied de page.

Ne livre pas les cinq d'un coup : le hero et la dissolution en ligne suffisent à changer la perception du site.
