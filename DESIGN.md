---
name: Alliance Groupe
description: Studio web indépendant à Nantes — noir d'atelier, or champagne rare, surfaces plates qui ne se soulèvent qu'au toucher.
colors:
  or-champagne: "#D4B45C"
  or-champagne-clair: "#E4C56E"
  or-champagne-profond: "#C5A44E"
  orange-projection: "#F37A1F"
  noir-atelier: "#080808"
  noir-graphite: "#0A0A0F"
  surface-carte: "#14141C"
  blanc-pur: "#FFFFFF"
  blanc-craie: "#E8E6E0"
  gris-lecture: "#B0B0BC"
  gris-sourdine: "#8A8A95"
  vert-validation: "#28A745"
  bleu-royal: "#002395"
  vert-heritage: "#00843D"
  papier-chaud: "#FBF8F1"
  papier-ombre: "#F4EEDF"
  encre-papier: "#2A261D"
typography:
  display:
    fontFamily: "Manrope, system-ui, sans-serif"
    fontSize: "clamp(2.2rem, 5.5vw, 3.8rem)"
    fontWeight: 800
    lineHeight: 1.1
  headline:
    fontFamily: "Manrope, system-ui, sans-serif"
    fontSize: "clamp(1.5rem, 3vw, 2.2rem)"
    fontWeight: 800
    lineHeight: 1.2
  title:
    fontFamily: "Manrope, system-ui, sans-serif"
    fontSize: "clamp(1.15rem, 2vw, 1.4rem)"
    fontWeight: 700
    lineHeight: 1.3
  body:
    fontFamily: "Manrope, system-ui, sans-serif"
    fontSize: "0.95rem"
    fontWeight: 400
    lineHeight: 1.6
  body-lg:
    fontFamily: "Manrope, system-ui, sans-serif"
    fontSize: "1.05rem"
    fontWeight: 400
    lineHeight: 1.65
  label:
    fontFamily: "Manrope, system-ui, sans-serif"
    fontSize: "0.85rem"
    fontWeight: 600
    lineHeight: 1.5
    letterSpacing: "0.5px"
  signature:
    fontFamily: "Playfair Display, Georgia, serif"
    fontSize: "inherit"
    fontWeight: 700
    lineHeight: 1.2
rounded:
  focus: "4px"
  sm: "8px"
  champ: "10px"
  bouton: "12px"
  carte: "16px"
  carte-lg: "18px"
  pastille: "100px"
spacing:
  xs: "6px"
  sm: "10px"
  md: "18px"
  lg: "28px"
  xl: "36px"
  gouttiere: "24px"
  section: "100px"
  section-mobile: "70px"
components:
  button-primary:
    backgroundColor: "{colors.or-champagne}"
    textColor: "{colors.noir-atelier}"
    typography: "{typography.body-lg}"
    rounded: "{rounded.bouton}"
    padding: "16px 36px"
  button-primary-hover:
    backgroundColor: "{colors.or-champagne-profond}"
    textColor: "{colors.noir-atelier}"
  button-primary-xl:
    backgroundColor: "{colors.or-champagne}"
    textColor: "{colors.noir-atelier}"
    rounded: "{rounded.bouton}"
    padding: "22px 48px"
  button-outline:
    textColor: "{colors.or-champagne}"
    typography: "{typography.body-lg}"
    rounded: "{rounded.bouton}"
    padding: "16px 36px"
  nav-cta:
    backgroundColor: "{colors.or-champagne}"
    textColor: "{colors.noir-atelier}"
    rounded: "{rounded.sm}"
    padding: "10px 20px"
  card-service:
    textColor: "{colors.blanc-pur}"
    rounded: "{rounded.carte}"
    padding: "36px 30px"
  card-media:
    rounded: "{rounded.carte-lg}"
  tag:
    textColor: "{colors.or-champagne}"
    typography: "{typography.label}"
    rounded: "{rounded.pastille}"
    padding: "6px 18px"
  input:
    textColor: "{colors.blanc-pur}"
    rounded: "{rounded.champ}"
    padding: "14px 18px"
---

# Design System: Alliance Groupe

## Overview

**Creative North Star: "L'Atelier d'Orfèvre"**

Un seul artisan, un établi sombre, et une lampe. Alliance Groupe est un studio solo : la page doit
donner la sensation d'un objet façonné à la main par quelqu'un dont on peut voir le geste, pas
d'une agence qui parle d'elle au pluriel. D'où un fond quasi noir (#080808) qui ne cherche jamais
à impressionner, des surfaces de contenu à peine plus claires que le fond (2,5 % de blanc), des
bordures d'un cheveu (6 % de blanc), et un or champagne qu'on ne sort que pour ce qui compte : le
bouton d'action, un chiffre, un mot d'un titre. Le luxe ici n'est pas la profusion, c'est la
retenue — l'or coûte cher, donc on en met peu.

La densité est calme et aérée : 100 px entre chaque section, une colonne de 1200 px maximum, du
texte à 1.6-1.7 d'interlignage. Rien ne clignote, rien ne flotte. Au repos, **tout est plat** :
aucune ombre portée sur une carte, aucune élévation gratuite. La profondeur n'apparaît que
lorsque le visiteur agit — la carte se soulève de 4 px et sa bordure s'ambre, le bouton monte de
2 px et son halo doré s'intensifie. C'est une interface qui répond au toucher plutôt qu'une
interface qui s'agite pour se faire remarquer.

La seule licence poétique est typographique : Playfair Display, **toujours en italique**, ne se
pose que sur un mot ou deux à l'intérieur d'un titre en Manrope. C'est la signature gravée sur
l'objet — le contraste entre la sans-serif industrieuse et l'empattement manuscrit dit exactement
ce que vend le studio : de la technique livrée avec du soin. Anti-références confirmées par le
code : pas de méga-menu déroulant (désactivé volontairement au profit d'un seul menu plein
écran), pas d'accent de couleur inventé hors palette, pas d'ombre décorative au repos.

**Key Characteristics:**

- Noir d'atelier presque total, jamais un gris de fond « thème sombre » standard
- Or champagne rare, jamais un aplat : accent, contour, halo
- Surfaces plates au repos ; la profondeur est une réponse, pas un décor
- Manrope 800 pour dire, Playfair italique pour signer
- Un seul menu plein écran, à toutes les tailles, PC inclus
- Rayons doux et constants (12 px bouton, 16-18 px carte, pastille 100 px)

## Colors

Une palette d'atelier : un noir dominant qui absorbe la lumière, un métal chaud, et des gris de
lecture froids qui empêchent l'ensemble de virer sépia.

### Primary

- **Or Champagne** (`#D4B45C`) : l'unique accent de marque. Boutons d'action, contours de
  pastilles, chiffres clés, mot signé en italique dans un titre, logo dans la barre de
  navigation, arrivée du focus clavier. Décliné en **Or Champagne Clair** (`#E4C56E`, survol de
  lien) et **Or Champagne Profond** (`#C5A44E`, fond du bouton primaire au survol).
- **Or transparent** : l'or vit surtout en opacité — remplissage de pastille à 10 %, bordure à
  25 %, voile de section à 6 %. C'est ce qui l'empêche de devenir clinquant.

### Secondary

- **Orange Projection** (`#F37A1F`) : accent chaud réservé à la **couche cinéma** (curseur
  personnalisé, point de curseur, dégradé de la barre de progression, survol des liens de carte).
  Il n'appartient pas au système de base : une page ordinaire ne doit pas l'utiliser.

### Tertiary

- **Bleu Royal** (`#002395`) et **Vert Héritage** (`#00843D`) : couleurs du bandeau tricolore et
  des variantes de pastilles héritage (France / Italie). Décoratifs et narratifs, jamais porteurs
  d'information ni d'état.
- **Vert Validation** (`#28A745`) : succès, disponibilité, pastille « en ligne ». Seul vert
  autorisé pour un état.

### Neutral

- **Noir Atelier** (`#080808`) : le fond de tout le site.
- **Noir Graphite** (`#0A0A0F`) : fond alternatif de section, dégradés de vignettes.
- **Surface Carte** (`#14141C`) : cartes pleines (bureaux, héritage). Les cartes légères
  préfèrent un voile `rgba(255,255,255,.025)` sur le fond plutôt qu'une couleur opaque.
- **Blanc Pur** (`#FFFFFF`) : titres et texte fort.
- **Blanc Craie** (`#E8E6E0`) : texte de navigation, légendes sur photo — un blanc réchauffé qui
  ne vibre pas sur le noir.
- **Gris Lecture** (`#B0B0BC`) : paragraphes courants.
- **Gris Sourdine** (`#8A8A95`) : mentions, notes de bas de carte. Interdit pour une information
  dont dépend une décision.

### L'encart papier

- **Papier Chaud** (`#FBF8F1`) → **Papier Ombre** (`#F4EEDF`) en dégradé à 160°, texte **Encre
  Papier** (`#2A261D`), or assombri à `#A8852F` pour tenir le contraste. C'est l'inversion
  complète du thème, réservée à une section qu'on veut faire respirer au milieu du noir.

### Named Rules

**La Règle de l'Or Rare.** L'or ne remplit jamais plus qu'un bouton ou une pastille. Test : sur
une capture d'écran de la page, si l'or dépasse une dizaine de pour cent des pixels, c'est trop —
retirez-en jusqu'à ce que le bouton principal redevienne l'endroit où l'œil tombe en premier.

**La Règle du Voile.** Une surface de contenu n'est pas une couleur, c'est un voile :
`rgba(255,255,255,.025)` sur bordure `rgba(255,255,255,.06)`. On ne crée pas un nouveau gris de
carte ; on empile de la transparence sur le noir d'atelier.

**La Règle de l'Orange Confiné.** `#F37A1F` n'existe que dans la couche cinéma. Sur une page
ordinaire, un accent chaud supplémentaire est un signe qu'on a raté la hiérarchie.

## Typography

**Display Font:** Manrope (avec repli `system-ui, sans-serif`)
**Body Font:** Manrope (même famille, poids et taille font la hiérarchie)
**Signature Font:** Playfair Display, italique uniquement (avec repli `Georgia, serif`)

Note technique : les piles déclarées insèrent les polices emoji (`Apple Color Emoji`,
`Segoe UI Emoji`, `Noto Color Emoji`) **avant** le repli générique. Sans ça, les emojis des menus
retombaient en glyphes monochromes. Ne pas les retirer.

**Character:** une grotesque contemporaine, dense et très appuyée (800) pour les titres, qui
donne le ton d'un studio technique ; puis, au milieu d'un titre, deux mots en Playfair italique
doré — le geste manuscrit sur l'objet fini. Le contraste ne vient pas de deux polices qui se
disputent, mais d'une police qui travaille et d'une qui signe.

### Hierarchy

- **Display** (800, `clamp(2.2rem, 5.5vw, 3.8rem)`, 1.1) : titre de hero, une fois par page.
- **Headline** (800, `clamp(1.5rem, 3vw, 2.2rem)`, 1.2) : titres de section. Le titre de section
  de premier plan monte à `clamp(2rem, 5vw, 3.2rem)`.
- **Title** (700, `clamp(1.15rem, 2vw, 1.4rem)`, 1.3) : titres de carte.
- **Body Large** (400, 1.05rem, 1.65) : chapô de section, en Gris Lecture, borné à 600 px de
  large pour rester lisible.
- **Body** (400, 0.95rem, 1.6) : texte courant, Gris Lecture. Le corps de page global est à 16px
  / 1.7.
- **Label** (600, 0.85rem, +0.5px, MAJUSCULES) : pastilles, rôles, sur-titres. Toujours doré ou
  dans sa variante héritage.
- **Signature** (Playfair Display, italique, doré) : `<em>` et `.ag-gold`. Reprend la taille du
  contexte, jamais la sienne.

### Named Rules

**La Règle de l'Italique.** Playfair ne s'écrit jamais en romain, jamais en paragraphe, jamais
sur plus de deux ou trois mots. Un titre entier en Playfair, c'est un site de mariage — pas un
atelier.

**La Règle des Classes.** Les tailles passent par les utilitaires (`.ag-h1`, `.ag-h2`, `.ag-h3`,
`.ag-body-lg`, `.ag-body`, `.ag-small`), jamais par un `font-size` en ligne. C'est écrit dans le
CSS et c'est ce qui garde l'échelle cohérente d'une page à l'autre.

## Layout

Colonne unique et centrée : `.ag-container` à 1200 px maximum avec 24 px de gouttière,
`.ag-container--narrow` à 800 px pour la lecture longue. La barre de navigation vit plus large
(1360 px, 32 px de gouttière) pour que le logo respire jusqu'au bord.

Le rythme vertical est franc : 100 px de respiration par section, ramenés à 70 px sous 1024 px.
Un chapô de section est borné à 600 px et suivi de 48 px de vide avant le contenu — ce blanc est
volontaire, il fait office de respiration entre l'argument et la preuve.

Les grilles sont explicites plutôt qu'automatiques : 3 colonnes pour les cartes de service et les
templates, 2 colonnes pour les cartes détaillées, `gap` de 28 px (40 px pour les grandes cartes).
**Sous 1024 px, tout retombe en une seule colonne d'un coup** — pas d'étape intermédiaire à deux
colonnes. Les paliers réellement utilisés sont 1024, 900, 768, 640/560 et 480 px ; le
défilement d'ancre est décalé de 110 px (`scroll-padding-top`) pour passer sous la barre fixe.

**La Règle de la Colonne Unique.** Sur mobile, on n'essaie pas de sauver une grille : une seule
colonne, boutons pleine largeur et centrés. C'est déjà le comportement du CSS ; toute nouvelle
grille doit s'y plier.

## Elevation & Depth

Le système est **plat par défaut**, et c'est une position, pas un oubli. La profondeur est
obtenue par empilement de transparences (voile blanc à 2,5 %, bordure à 6 %) et par contraste de
noirs, pas par des ombres portées permanentes. Aucune carte n'a d'ombre au repos.

L'ombre est réservée à trois rôles : **répondre à une action** (survol, focus), **détacher un
élément flottant** (barre de navigation une fois défilée, fenêtre modale) et **faire rayonner
l'or** (uniquement sous un élément déjà doré).

### Shadow Vocabulary

- **Halo doré, repos** (`box-shadow: 0 4px 25px rgba(212,180,92,.25)`) : sous le bouton primaire,
  en permanence. C'est la seule ombre colorée du système.
- **Halo doré, survol** (`box-shadow: 0 8px 35px rgba(212,180,92,.35)`) : le halo s'ouvre quand le
  bouton monte de 2 px. En version XL : `0 14px 40px rgba(212,180,92,.35)`.
- **Soulèvement de carte** (`box-shadow: 0 12px 40px rgba(0,0,0,.3)`) : au survol d'une carte de
  service, avec `translateY(-4px)`.
- **Soulèvement lourd** (`box-shadow: 0 24px 60px rgba(0,0,0,.5)`) : cartes photo de grand format.
- **Barre défilée** (`box-shadow: 0 2px 30px rgba(0,0,0,.4)`) : accompagne le passage de la
  navigation en fond translucide flouté à 18 px.
- **Fenêtre modale** (`box-shadow: 0 30px 80px rgba(0,0,0,.35)`) : superposition seulement.

### Named Rules

**La Règle du Repos Plat.** Une surface de contenu au repos n'a pas d'ombre. Si une carte a
besoin d'une ombre pour se détacher du fond, c'est sa bordure ou son voile qu'il faut corriger.

**La Règle du Halo Doré.** Les ombres colorées sont dorées, et uniquement sous un élément doré.
Une carte, un champ ou une image ne rayonnent jamais.

## Shapes

Une famille de rayons douce et étroite, qui monte avec la taille de l'objet : 4 px pour un anneau
de focus, 8 px pour une action compacte (CTA de la barre de navigation), 10 px pour un champ de
saisie, 12 px pour un bouton, 16 px pour une carte de service, 18 px pour une carte à média,
20-24 px pour les grands blocs. Deux formes absolues complètent la famille : la **pastille**
(`100px`, pastilles, badges, incrustations sur photo) et le **disque** (`50%`, points et
puces). Aucun angle vif nulle part.

Les bordures sont toujours d'un pixel et presque invisibles au repos (`rgba(255,255,255,.06)`) ;
elles s'ambrent à l'interaction (`rgba(212,180,92,.2)` à `.25`). Le contour n'est pas un cadre,
c'est un seuil.

Une signature géométrique existe pour les fonds : un motif **zellige** (cercles et étoiles à huit
branches dorées, SVG inline) posé à 8 % d'opacité et masqué en dégradé radial pour qu'il
s'évanouisse sur les bords. Il ne se pose que sur les surfaces narratives, jamais derrière du
texte de lecture.

**La Règle du Seuil.** Une bordure ne délimite pas, elle réagit : 1 px, presque invisible au
repos, dorée à l'interaction. Une bordure épaisse et permanente est un corps étranger — sauf sur
le bouton fantôme, où les 2 px dorés *sont* le bouton.

## Components

### Buttons

- **Shape :** rayon doux de 12 px, contenu en `inline-flex` avec 10 px entre l'icône et le
  libellé.
- **Primaire :** fond Or Champagne, texte Noir Atelier, `16px 36px`, graisse 700, 1.05rem, halo
  doré permanent. Au survol : fond Or Champagne Profond (`#C5A44E`), `translateY(-2px)`, halo
  élargi. Transition de 0.3 s sur fond, translation et ombre.
- **Primaire XL :** même bouton en `22px 48px` / 1.15rem, montée de 3 px au survol. Réservé au
  point de conversion principal d'une page, une seule fois.
- **Fantôme :** fond transparent, texte doré, bordure de 2 px dorée. Au survol, remplissage
  `rgba(212,180,92,.1)` et même montée de 2 px. C'est le seul endroit où une bordure épaisse est
  légitime.
- **CTA de navigation :** version compacte (`10px 20px`, rayon 8 px, 0.92rem) pour tenir dans la
  barre.
- **Focus :** contour de 2 px doré avec 3 px de décalage et rayon de 4 px, sur toutes les cibles
  interactives. À ne jamais supprimer.

### Tags / Pastilles

- **Style :** pastille (100 px), fond `rgba(212,180,92,.1)`, texte doré, bordure
  `rgba(212,180,92,.25)`, MAJUSCULES 0.85rem / 600 / +0.5px, 18 px de marge basse.
- **Variantes héritage :** `--green` (vert Italie/Maroc) et `--blue` (bleu France) reprennent la
  même construction avec leur teinte. Décoratives uniquement.

### Cards / Containers

- **Carte de service :** voile `rgba(255,255,255,.025)`, bordure `rgba(255,255,255,.06)`, rayon
  16 px, padding `36px 30px`, colonne flex pour que le bouton se colle en bas. Au survol :
  bordure ambrée, montée de 4 px, ombre de soulèvement, et la flèche interne glisse de 6 px vers
  la droite. Transition longue (0.4 s), volontairement plus lente que celle des boutons.
- **Carte à média :** rayon 18 px, `overflow:hidden`, image en `object-fit:cover` qui zoome à
  1.06 en 0.6 s au survol de la carte. La carte monte, l'image respire : deux vitesses, un seul
  geste.
- **Carte pleine :** fond Surface Carte (`#14141C`), rayon 18-22 px, liseré coloré de 3 px en
  haut et halo radial à 8 % de la couleur du thème de la carte.
- **Incrustation sur photo :** pastille noire à 65 % avec flou d'arrière-plan de 10 px, en bas à
  gauche de l'image.

### Inputs / Fields

- **Style :** fond `rgba(255,255,255,.04)`, bordure `rgba(255,255,255,.1)`, rayon 10 px, padding
  `14px 18px`, texte blanc, 1rem, pleine largeur.
- **Focus :** la bordure passe à l'Or Champagne. Le contour natif est retiré sur le champ, mais
  l'anneau de focus doré reste actif partout ailleurs — ne pas généraliser ce retrait.
- **Groupes :** colonne flex avec 14 px entre les champs.

### Navigation

- **Barre :** fixe, transparente au chargement, 16 px de padding vertical. Au défilement, elle
  passe à `rgba(8,8,8,.95)` avec un flou d'arrière-plan de 18 px, se resserre à 10 px et prend
  son ombre. Transition de 0.4 s.
- **Logo :** Playfair 1.5rem doré, précédé de la tête de lion en 36 px.
- **Menu :** la liste horizontale est **volontairement désactivée** (`display:none !important`).
  Toute la navigation passe par un panneau plein écran qui glisse depuis la droite en 0.45 s
  (`cubic-bezier(.23,1,.32,1)`), fond `rgba(8,8,8,.98)` flouté à 24 px, liens en 1.3rem / 700
  séparés par un filet à 4 % de blanc.
- **États :** couleur de lien Blanc Craie, passage à l'or au survol ; anneau de focus doré.

### Signature : la couche cinéma

Module **optionnel**, réservé aux pages vitrines, entièrement désactivé au tactile
(`@media (hover:hover) and (pointer:fine)`) et sous `prefers-reduced-motion`. Il remplace le
curseur système par un anneau doré de 38 px suivi d'un point Orange Projection de 6 px, ajoute
des révélations au défilement et une barre de progression en dégradé conique or → orange. Il ne
fait pas partie du système de base : une nouvelle page n'a pas à le reprendre, et une page qui
s'en passe reste parfaitement dans la marque.

## Do's and Don'ts

### Do:

- **Do** poser les tailles avec les utilitaires `.ag-h1` / `.ag-h2` / `.ag-h3` / `.ag-body-lg` /
  `.ag-body` / `.ag-small` — le CSS le demande explicitement, et c'est ce qui empêche l'échelle
  de dériver page après page.
- **Do** construire une surface par empilement : voile `rgba(255,255,255,.025)` + bordure
  `rgba(255,255,255,.06)`, plutôt qu'un nouveau gris opaque.
- **Do** garder les deux vitesses d'interaction : 0.3 s pour un bouton (montée de 2 px), 0.4 s
  pour une carte (montée de 4 px), 0.6 s pour une image qui zoome.
- **Do** réserver l'or au point d'action, aux chiffres et à un mot signé en italique.
- **Do** conserver l'anneau de focus doré (2 px, décalage 3 px) sur toute cible interactive.
- **Do** respecter le coupe-circuit `prefers-reduced-motion` déjà présent dans `main.css`, et
  l'étendre à toute nouvelle animation.
- **Do** faire retomber les grilles en une seule colonne dès 1024 px.

### Don't:

- **Don't** ajouter une ombre à une carte au repos : la profondeur est une réponse à une action.
- **Don't** faire rayonner autre chose qu'un élément doré — pas de halo sous une carte, une
  image ou un champ.
- **Don't** utiliser l'Orange Projection (`#F37A1F`) hors de la couche cinéma, ni introduire un
  accent de couleur qui n'est pas dans la palette.
- **Don't** écrire Playfair Display en romain, en paragraphe, ou sur un titre entier.
- **Don't** réactiver le méga-menu horizontal : le menu plein écran est un choix, pas une
  limitation mobile.
- **Don't** employer le Gris Sourdine (`#8A8A95`) pour une information dont dépend une décision
  du visiteur — il est fait pour les mentions.
- **Don't** poser le motif zellige derrière du texte de lecture ; il vit sous les surfaces
  narratives, à 8 % d'opacité.
- **Don't** retirer les polices emoji des piles typographiques : sans elles, les emojis des menus
  retombent en glyphes monochromes.
