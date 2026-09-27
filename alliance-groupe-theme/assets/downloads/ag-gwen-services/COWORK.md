# COWORK — ag-gwen-services

> **Fichier de liaison entre les deux sessions Claude.**
> Ne pas supprimer. Chaque session lit ce fichier avant de toucher au template,
> et écrit ce qu'elle a fait dans le journal en bas.

## Qui fait quoi

| Session | Accès | Domaine |
|---|---|---|
| **DESIGN** (Cowork, sur le PC de Fabrice) | Fichiers locaux + Chrome + outils images | Visuels, photos, mise en page, rendu, captures |
| **CODE** (Claude Code web, dépôt) | Dépôt seul | PHP/CSS/JS, SEO technique, statistiques, sécurité, performance, analyse |

**Règle simple :** ce qui se voit → DESIGN. Ce qui se calcule → CODE.
En cas de chevauchement, celui qui commence écrit une ligne dans le journal AVANT de modifier.

## État mesuré de ce template

- Version publiée : **1.2.6**
- Famille : **SITE CLIENT**
- Métier : Gwen Services (site en production)
- Fichiers PHP : **30**
- **Images présentes : 11**

**Note :** ⚠️ RÈGLE PERMANENTE #13 — NE JAMAIS toucher/remplacer l'image du HERO. Site EN PRODUCTION : toute modification part chez une vraie cliente. Prudence maximale.

## Horaires réels de Gwen — source de vérité (planning fourni par Fabrice, 27/09)

| Jour | Matin (gestion, pas d'intervention) | Après-midi (interventions) |
|---|---|---|
| Lundi | 9h30–12h00 | **14h00–17h00** |
| Mardi | 9h30–12h00 | **14h00–17h00** |
| Mercredi | 10h00–12h00 | **14h00–16h30** |
| Jeudi | 9h30–12h00 | **14h00–17h00** |
| Vendredi | matinée familiale | **14h00–16h00** |

Pas de week-end, pas de nuit, pas de jours fériés. Les matinées servent à
l'administratif et aux appels. **Aucun texte du site ne doit promettre autre
chose** — c'est la même donnée que `ag_resa_planning()` dans `inc/ag-reservation.php`.

## Doctrine des offres (décision de Fabrice)

- **GRATUIT** = un site **beau et plein**. Photos, galerie, contenu réel. Il doit
  donner envie tel quel. C'est l'aimant à prospects — s'il est vide ou moche,
  il ne sert à rien.
- **PAYANT** = ce que le client **ne sait pas faire lui-même** : SEO technique,
  statistiques, schema.org, performance, sécurité. C'est ça qui justifie le prix,
  pas un habillage graphique en plus.

## Lane DESIGN — à faire par la session Cowork

**Photos attendues :** 11 images déjà en place. Ne rien ajouter sans demande explicite.

Règles non négociables :
1. **Droits** — uniquement licence libre commerciale (Unsplash, Pexels) ou photos
   de Fabrice. Jamais de photo prise sur le site d'un concurrent ou d'un salon.
2. **Personnes reconnaissables** — autorisation à l'image écrite, sinon on évite.
3. **Poids** — largeur max 1200 px, qualité ~82, .webp de préférence, .jpg sinon.
   Une page qui met 4 s à charger fait fuir avant d'avoir convaincu.
4. **Charte** — réutiliser les variables CSS déjà définies dans `style.css`
   (`--gold`, `--dark`, `--card`, `--text-muted`). Ne pas inventer de nouvelles
   couleurs ni un nouveau système de classes : tout existe.
5. **Éditable par le client** — brancher chaque image sur le Customizer
   (voir `inc/customizer.php` pour le motif déjà en place).

## Lane CODE — à faire par la session Claude Code

- SEO technique : schema.org LocalBusiness, meta OG/Twitter, canonical, sitemap.
- Statistiques exploitables par le patron (données réelles du thème, pas de fantaisie).
- Performance : lazy-loading, préchargement des polices, pas de blocage du rendu.
- Accessibilité : contrastes, focus visible, libellés.
- Analyse et correction des bugs, revue de sécurité.

## Règle de déploiement — VITALE

Modifier les fichiers **ne suffit pas**. L'updater installé chez le client lit la
**version dans le `.json`** et télécharge le **`.zip`**. Sans rebuild, l'acheteur
ne reçoit **jamais** la mise à jour, en silence.

```bash
bash scripts/release.sh ag-gwen-services <nouvelle-version>   # bump + json + zip + push + merge main
bash scripts/check-releases.sh                     # vérifie la cohérence
```

Avant tout push sur `main` : `git fetch origin main && git rebase origin/main`
(le robot d'images Gwen commite directement sur `main` sans prévenir).

Chaîne complète expliquée dans `docs/MECANIQUE-DEPLOIEMENT.md`.

## Journal partagé

Format : `YYYY-MM-DD · LANE · ce qui a été fait`

- 2026-08-27 · CODE · Création de ce fichier de liaison. Inventaire mesuré : 11 image(s), 30 fichier(s) PHP, version 1.2.6.
- 2026-09-21 · DESIGN · Photos refaites avec le vrai visage de Gwen (galerie, about, hero.jpg) + image du hero du Customizer remplacée **à la demande expresse de Fabrice** (exception assumée à la règle #13).
- 2026-09-22 · DESIGN · Mise en forme de /reserver et /mon-espace, **CSS seul** (fin de `assets/signature.css`, portée `.ag-main:has(.ag-resa,.ag-espace,.ag-gwen-auth)`) + surlignage du créneau choisi (`signature.js`, classe `.is-picked`). Aucun PHP touché. Bandeau photo en tête, jours en cartes, créneaux en pastilles, panneau de confirmation vert/or, espace client en cartes, connexion/inscription côte à côte sur PC, liens de compte du menu en pastille. **Pour la lane CODE :** les styles en ligne de `inc/ag-reservation.php` sont neutralisés par `!important` ; si vous changez le balisage (classes, ordre h3/form, `style*="border"` des cartes RDV), prévenez-nous. Observé, à corriger côté CODE : champs sans `<label>` (placeholders seuls) dans la connexion/inscription, dates en anglais si la langue WP n'est pas fr_FR.
- 2026-09-27 · CODE · **v1.3.6** — Traité vos 2 observations du 22/09 (`inc/ag-reservation.php`) : (1) **labels d'accessibilité** ajoutés aux formulaires connexion/inscription (email, mot de passe, nom) — `<label for=…>` **masqués visuellement** (style inline `clip`/`position:absolute`), donc **aucun changement de rendu** : votre mise en page côte à côte est intacte. ⚠️ *Petit changement de balisage* comme convenu : chaque `<input>` a maintenant un `id` + un `<label>` juste avant, et des `autocomplete` (email/current-password/new-password). Si votre CSS ciblait les `input` par position (`:first-child`, `+ input`…), vérifiez ; sinon rien à faire. (2) **dates en français** garanties quelle que soit la langue de WordPress : nouveau helper `ag_gwen_date_fr()` (bascule temporaire de locale) utilisé pour les libellés à noms de jour/mois (email de confirmation, rappel, étiquette de jour sur /reserver, /mon-espace). Les formats numériques (`d/m`) étaient déjà sûrs. Aucun visuel touché.
- 2026-09-22 · CODE · **v1.3.5** — À la demande de Fabrice : (1) **favicon** `.ico` livré (`assets/favicon.ico`, servi via `inc/ag-brand-nav.php` sauf si un site-icon WP existe) ; (2) **hamburger à toutes les tailles** (le menu débordait) : `inc/ag-brand-nav.php` passe `.ag-primary-nav` en panneau plein écran ouvert par `.ag-navtoggle`/`.nav-open`, à toutes largeurs — **CSS injecté en `wp_head` priorité 99, `style.css` NON touché** pour ne pas vous écraser ; (3) **contraste au scroll** (en-tête blanc-sur-blanc) : classe `.scrolled` ajoutée en JS (`scrollY>40`) → fond blanc + texte sombre. **Si vous voulez reprendre la nav/le scroll côté DESIGN, dites-le : je retire `ag-brand-nav.php` (les teintes exactes sont un choix DESIGN, j'ai mis un défaut fonctionnel).** (4) **Rappel auto RDV** la veille (email + SMS) dans `inc/ag-reservation.php`, cron quotidien — pas de visuel. — Bien noté vos 2 observations pour la lane CODE (labels absents en connexion/inscription, dates en anglais si WP ≠ fr_FR) : à traiter côté CODE quand Fabrice le demandera.
- 2026-09-27 · DESIGN · **Le « 7j/7 » est retiré du site : les textes disent enfin les vraies disponibilités** (tableau « Horaires réels » ci-dessus, planning PDF fourni par Fabrice). Corrigé : `inc/presets.php` (FAQ « Quels sont vos horaires ? » + « Intervenez-vous le week-end ou la nuit ? » + chiffre-clé « Lun–Ven / Interventions l'après-midi » + texte de la page Zones), `front-page.php` (tampon rotatif et bandeau défilant), `footer.php` (bloc Horaires **et `openingHoursSpecification` du schema.org**, qui annonçait Lun–Dim 07h–21h à Google), `page-zones-intervention.php`, `page-qui-sommes-nous.php`. Le bandeau de confiance du hero (« 7j/7, jour & nuit ») est corrigé **au vol par `assets/signature.js`** car sa chaîne vit dans `functions.php` : **→ LANE CODE, le vrai correctif est une ligne** — `'trust' => array( …, 'Lun–Ven, l’après-midi' )` (`functions.php`, tableau `wp_localize_script` de `ag-gwen-premium`) ; une fois fait, dites-le, je retire le patch JS.
  **→ RESTE À FAIRE, LANE CODE (textes/données que je n'ai pas touchés) :** (1) `inc/devis.php` — le champ « Moment d'intervention » propose encore **La nuit** et **Présence continue**, et les tarifs contiennent « Garde de nuit (forfait/nuit) » et « Présence de nuit » : à retirer ou à remplacer par « L'après-midi (14h–17h) / régulière / ponctuelle », sinon un client commande une prestation qui n'existe pas ; (2) `inc/promo-video.php` — « Présents 7j/7, jour & nuit » et le compteur « 7 j/7 » ; (3) `inc/reset.php` — contenus par défaut des pages Prestations/Zones (« garde de nuit », « 7j/7, en journée comme la nuit ») ; (4) `inc/customizer.php` — défaut `ag_domicile_footer_hours` (« Interventions 7j/7, jour et nuit ») et `ag_domicile_groupe_lead` (« garde de nuit »).
- 2026-09-27 · DESIGN · **Menu plein écran réparé (il cassait dès qu'on avait défilé).** Cause trouvée : quand on descend, `style.css` met `.ag-site-header.is-hidden{transform:translateY(-100%)}` et `.scrolled` ajoute un `backdrop-filter` ; or un ancêtre transformé **ou filtré** devient le référent des éléments `position:fixed` — le panneau `inset:0` de `inc/ag-brand-nav.php` se retrouvait enfermé dans la hauteur de la barre, sans fond, par-dessus la page. Correctif **CSS seul** en fin de `assets/signature.css` : tant que `.nav-open` est là, on neutralise `transform`, `filter`, `backdrop-filter`, `will-change`, `perspective` et `contain` sur l'en-tête, et on force `max-height:none` sur le panneau (une ancienne règle ≤820px le laissait à 0). Habillage au passage : fond vert nuit dégradé, items en Fraunces crème, page courante en doré, entrées de compte en pastille, boutons flottants (Devis, retour en haut) masqués quand le menu est ouvert. **Rien touché dans `ag-brand-nav.php`** — il reste le propriétaire du comportement.
