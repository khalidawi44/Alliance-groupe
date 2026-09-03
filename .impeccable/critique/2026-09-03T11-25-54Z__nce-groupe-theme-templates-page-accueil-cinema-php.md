---
target: page d accueil, mobile, artisan venu par SMS d ambassadeur
total_score: 12
max_score: 36
na_heuristics: 10
p0_count: 4
p1_count: 1
target_identity: "file:C:\\Users\\Utilisateur\\Documents\\GitHub\\Alliance-groupe\\alliance-groupe-theme\\templates\\page-accueil-cinema.php"
target_fingerprint: "sha256:63c28b9bc1e219f1597baafc72b087d30210785517f5e0b44e90b4aaf72500b4"
target_path: "C:\\Users\\Utilisateur\\Documents\\GitHub\\Alliance-groupe\\alliance-groupe-theme\\templates\\page-accueil-cinema.php"
timestamp: 2026-09-03T11-25-54Z
slug: nce-groupe-theme-templates-page-accueil-cinema-php
---
# Critique — Accueil (page-accueil-cinema.php), vue mobile par un artisan venu d'un SMS d'ambassadeur

Method: dual-agent (A: revue de design par lecture du code · B: detecteur mecanique + Playwright mobile en production)

## Design Health Score — 12/36 (heuristique 10 n/a) — casse pour le public prioritaire

| # | Heuristique | Note | Probleme cle |
|---|---|---|---|
| 1 | Visibilite de l'etat du systeme | 1 | 1225 px de scroll consommes par une scene epinglee sans repere ; « Voir les offres » sans retour |
| 2 | Correspondance systeme / monde reel | 2 | « sur-mesure », « propulse par l'IA », « Pack Boutique » ; 490 EUR et « livre en 5 jours » clairs mais tardifs |
| 3 | Controle et liberte | 1 | Scroll-jacking 5,6 ecrans, Lenis, prix cliquables sur 0,25 ecran, aucun moyen de sauter |
| 4 | Coherence et standards | 1 | 45 ecarts au design system sur ce fichier ; Inter declaree jamais chargee ; deux <footer> |
| 5 | Prevention des erreurs | 2 | « Choisir Essentiel — 490 EUR » ne choisit rien ; 11 liens externes sans avertissement |
| 6 | Reconnaitre plutot que se rappeler | 1 | Les 3 prix n'existent jamais ensemble sur telephone |
| 7 | Flexibilite et efficacite | 1 | L'ancre #offres est cassee sur mobile ; aucun raccourci « appeler » |
| 8 | Esthetique et sobriete | 2 | Belle, mais 12 options dans l'Atelier, marquee de 8 slogans, 5 couches flottantes sur le hero |
| 9 | Diagnostic et recuperation | 1 | Le repli fabrique 3 temoignages sous un logo Google |
| 10 | Aide et documentation | n/a | Landing de persuasion |

## Verdict de specificite
Forme signee, message interchangeable. Le langage visuel (dissolution canvas, offres jaillissant d'une paume,
marquee asservi au scroll, revelation du lion) est authored. Le contenu est un catalogue d'agence generique.
Le seul mecanisme non copiable — 490 EUR contre 3000-8000 EUR, en 5 jours, parce que la maison est deja
construite — n'apparait pas dans le premier ecran, et le mot « sur-mesure » dit l'inverse du positionnement.

Scan deterministe : 47 trouvailles sur page-accueil-cinema.php (exit 2), 11 sur header.php, 5 sur footer.php.
31 tailles de police, 10 couleurs, 4 rayons hors design system. 2 avertissements layout-transition portent sur
le bloc .hd, qui est du code mort (getElementById("hd") ne trouve rien) : detection juste, impact nul.

## Ce qui marche
1. La section Realisations (3 clients reels, chiffres reels, liens avis Google) est la meilleure page de vente
   du site — et elle est a l'ecran 12.
2. Le repli mobile de la scene epinglee est un vrai travail d'ingenierie (canvas remplace par un flou, video
   hors chemin critique, poster fetchpriority=high).
3. Les garde-fous factuels sont inscrits dans le code (« on n'invente jamais d'avis », « uniquement des clients REELS »).

## Problemes prioritaires

[P0] 1. Sous prefers-reduced-motion, les prix ne s'affichent JAMAIS (mesure : opacity 0 constante jusqu'a
scrollY 9989). Etat initial GSAP inline pose avant le garde JS, jamais annule. Sans JS : .at{opacity:0} en dur
dans le CSS et .of{pointer-events:none} jamais leve. Fix : REDUIT = matchMedia(...).matches, ok = (G&&ST) && !REDUIT,
etat final force, retirer opacity:0 du CSS de .at. -> $impeccable animate

[P0] 2. Le premier ecran n'a ni prix, ni preuve, ni telephone, et dit « sur-mesure ». Premier prix a pleine
opacite : 6,2 ecrans (5240 px). Premier telephone : 16,1 ecrans. Le seul autre lien tel: du site est dans un menu
display:none. Contredit PRODUCT.md principe 1 et la Regle du Heros Immobile. Fix : titre « Votre site pro, 490 EUR,
en 5 jours », sous-titre avec l'ecart de prix agence, ligne de preuve (3 clients reels), 2e bouton = tel:+33744829516,
retirer data-split/data-hl du H1. -> $impeccable clarify

[P0] 3. « Voir les offres » atterrit sur une photo et un aphorisme sur mobile. L'ancre vise #ofStage
(absolute dans un sticky) : rect = haut de la scene = progression 0, offres a opacity:0. Fix : viser la progression
comme la branche desktop (repere 0,36), ou pointer vers /sites-express. -> $impeccable harden

[P0] 4. Le repli des avis fabrique 3 temoignages (« Anna B. », « Quartier Libre », « Gwen ») sous le logo Google,
5 etoiles en dur et « Avis Google verifies ». Quartier Libre n'est pas dans le portefeuille confirme.
Fix : ne rien afficher si la liste est vide ; logo/etoiles/mention uniquement si total > 0, etoiles depuis la note reelle.

[P1] 5. Les 3 prix ne coexistent jamais sur mobile (fenetre de 0,25 ecran chacun), leur contenu est enferme dans
des JPEG, .of__note (maintenance 29 EUR/mois) est masque sur mobile. Debordement horizontal de 37 px a 375 ET 390 px.
1,4 Mo charges dont 1031 Ko d'images pour un ecran sans prix. Fix : grille statique en colonne unique sous 960 px,
juste apres le hero, contenu des packs en texte reel. -> $impeccable layout

## Drapeaux rouges de persona
- Artisan sur chantier : 20 s accordees, prix a 6,2 ecrans, impossible d'appeler, impossible de comparer,
  « sur-mesure » comme premier mot, fenetre cliquable de 0,25 ecran au pouce, prix inexistant sous reduced-motion.
- Ambassadeur : a promis 490 EUR, la page ouvre sur « sur-mesure » ; le badge « On recrute » s'affiche au client ;
  faux avis = il a l'air d'avoir menti ; aucun element ne porte son lien ?ref=.
- Conjoint/associe qui valide la depense : contenu des packs en JPEG (non copiable, non lisible par lecteur d'ecran) ;
  alt « Reunion au bureau de Naples » + « Naples pour l'atelier » annoncent un 2e bureau et une equipe inexistants ;
  le hero ne montre jamais Fabrice.

## Observations mineures
Manrope absente (--sans:Inter jamais chargee) ; Playfair en romain sur tous les titres, graisse 500 non chargee ;
halos dores au repos sous une carte et une image ; lien d'evitement #ag-main-content pointant sur rien ;
aucun focus visible sur les liens du template ; deux <footer> empiles ; plus petite police 11,52 px ;
4 cibles tactiles < 44 px ; 10 domaines tiers + 1 iframe Google Merchant ; erreur console
« script resource is behind a redirect » (piste webmanifest 301, non confirmee) ; « Depuis 2019 » non source ;
bloc CSS .hd et getElementById("hd") = code mort ; « Livre en 5 jours » x4, « Audit gratuit » x5.

## Ecart depot / production
Aucun sur les points verifies : les 5 faux clients sont absents en ligne comme dans le depot, le JSON-LD servi ne
declare qu'une fiche LocalBusiness (Nantes), le pied de page dit « Studio web independant a Nantes ». Site a jour.

## Questions
1. DESIGN.md decrit une page qui n'existe pas : le template fait l'inverse de 7 de ses regles. Lequel est le mensonge ?
2. Combien d'heures dans le canvas de dissolution, combien dans les 4 lignes du hero qui decident de la vente ?
3. Qu'est-ce qui justifie 5,6 ecrans de scroll-jacking AVANT la seule preuve que la maison tient debout ?
