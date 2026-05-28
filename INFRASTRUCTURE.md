# INFRASTRUCTURE.md — Base de connaissances réutilisable (Fabrice / Alliance Groupe)

> Mémo central de tout ce qu'on a construit, **réutilisable sur tous tes sites** (Alliance Groupe, quartierlibre.org, futurs repos).
> Mon accès GitHub est limité au repo `khalidawi44/alliance-groupe` : copie ce fichier dans tes autres repos pour l'y réutiliser.
> Résumé court = `CLAUDE.md` (lu auto à chaque session). Détail complet = ce fichier.

---

## 1. Workflow & raccourcis

- **Branche de dev** : on travaille sur la branche de travail courante (ex. aujourd'hui `claude/infrastructure-md-review-6R3D2`) → **merge ff-only dans `main`** → push. La branche change selon le chantier ; toujours vérifier `git branch --show-current`.
- **Mise en ligne** : WordPress → **Apparence → SYNC GitHub** : « Vérifier MAJ » → « SYNC FICHIERS DU THÈME » → **purger le cache** → **Ctrl+F5** (recharger l'onglet sur mobile).
- **Avant chaque commit** : `php -l fichier.php` (jamais committer du PHP cassé). **Indentation = tabulations**.
- **Thème autonome** (pas d'Elementor). Templates = `templates/page-*.php` (entête `Template Name`). Logique = `inc/*.php` chargés par `functions.php`. Blocs = `template-parts/*.php`.
- Chaque brique est **indépendante** (guards `function_exists` / `if (!defined)`), donc copiable seule. Capacité admin requise partout : `manage_options`.

## 2. Création de VIDÉO (3 méthodes)

1. **Studio in-browser (GRATUIT, recommandé)** — `templates/page-studio.php`
   - Canvas HTML5 + `captureStream` + `MediaRecorder` → MP4/WebM, **sans serveur ni clé**.
   - Textes **animés séquentiels** (titre→messages), **police** (serif/sans) + **couleur** au choix, **fonds villes propres** (pas de texte sur texte), import de sa propre photo/vidéo.
   - **Pièges iOS résolus** : préférer MP4 (iOS ne partage pas le WebM) ; vidéo source NON hors-écran (sinon iOS bride le décodage) ; `requestVideoFrameCallback` pour coller à la cadence ; rendu fond statique = lecture fluide.
   - Partage = `navigator.share({files})` (fichier seul = + d'apps) + **légende copiée auto** (le lien va dans la légende, pas sur la vidéo).
2. **IA (Picsart MCP)** — `genai-text2video` / `genai-image2video`. Nécessite la **connexion Picsart** (sinon 401). Pour générer des fonds de luxe (Naples, Ferrari…). Coûte des crédits.
3. **Remotion** (repo séparé `alliance-videos`) — rendu MP4 par code, servi via jsDelivr. Pour des vidéos « template » répétables.

## 3. Les AGENTS (tous gratuits / guidés, sans IA payante)

- **Équipe de chat prospection** — `template-parts/prospect-chat.php` : Léo (prospection) → Sofia (architecte, recommande un pack) → Karim (souscription) → Nadia (gestion clients). Capture les leads.
- **Robot de chasse** — `inc/ag-prospection.php` : Google **Places API (New)** (`ag_places_key`). Repère les entreprises **sans vrai site** (réseaux sociaux = pas un vrai site), **score « probabilité d'achat »** (avis, note, joignable, manque de site), **balayage tous secteurs** d'une ville, **cron quotidien** + email/Telegram récap.
- **Agent Coach** — `inc/ag-coach.php` : agent à règles (pas d'IA payante) qui construit la **feuille de route quotidienne** (prospects à relancer, leads, ventes, briefs) et la pousse **Telegram + email à 8h** (cron `ag_coach_daily`). Option `ag_coach_on` (défaut on), boutons admin « Envoyer maintenant » (`ag_coach_now`) / on-off (`ag_coach_toggle`).
- **Générateurs de messages** : message **émotionnel** personnalisé par prospect (métier + manque), « pourquoi il a besoin d'un site ».
- **Message quotidien clients** : banque de messages urgence/offre limitée/exclusivité, **cron 9h** vers le canal Telegram clients.

## 4. CRM partagé (anti-doublon, anti-spam)

- Option `ag_prospects`. Statuts : nouveau → contacté → relancé → sans réponse → intéressé → client → refusé → **ne plus contacter** (bloque tout le monde).
- **Anti-doublon global** (`ag_prospect_add_record` + `ag_prospect_find`), filtres/tri, **assignation à un ambassadeur** (1 propriétaire = pas de double-contact). Côté ambassadeur : « Mes prospects » dans l'espace.
- Conformité : contact **humain** (jamais d'envoi auto de masse = RGPD/spam/blacklist), mention de désinscription dans les messages.

## 5. Notifications & diffusion

- **Alertes SMS via API Free (ACTIVES)** sur la ligne pro `07 44 82 95 16` : déclenchées sur inscription, message client, devis (+ .ics Google Agenda). Bouton « Test SMS » dédié, options listées par opérateur (SFR/Orange/Bouygues). WhatsApp CallMeBot = **optionnel** (les SMS suffisent).
- `ag_push()` (interne équipe) : **WhatsApp** (CallMeBot, 1:1 sur ton numéro) + **Telegram canal interne**. `ag_push_clients()` : **canal général clients**.
- **2 canaux Telegram** : interne (alertes confidentielles prospects/ventes) + général (`@ALLIANCE_GROUPE`, annonces clients). Réglages → Notifications téléphone (token + chat IDs + bouton « Détecter mon Chat ID »).
- Évènements poussés : prospect qui répond, intéressé, vente, nouvel ambassadeur, message quotidien clients, feuille de route Coach.
- **Limites à connaître** : un bot **ne peut PAS** auto-ajouter des gens à un groupe/canal, ni poster dans un **groupe WhatsApp** ; Telegram **ne fournit pas d'email** (donc pas de compte site auto depuis Telegram). À l'inscription on **envoie le lien d'invitation** (ambassadeur→groupe interne, client→canal général).

## 6. Auth, Paiement, Commissions

- **Espaces membres** — `inc/ag-espaces.php` : rôles `ag_client`/`ag_ambassadeur`, `/connexion` + `/mot-de-passe` maison, wp-login/wp-admin bloqués pour non-admins, no-cache pour connectés.
- **Inscription email** + **Connexion Google** (Sign in with Google ; option `ag_google_client_id` ; ID redirige vers `admin-post.php?action=ag_google_login`). Écran de consentement à **publier** côté Google Cloud.
- **PayPal automatique** — `inc/ag-paypal.php` : webhooks REST (Client ID/Secret/Webhook ID/email), vérif signature → **crédite la commission** (rapprochement montant+email, 2 sens). URL webhook = `admin-post.php?action=ag_paypal_webhook`. Émet le hook **`ag_paypal_payment_verified`** (consommé aussi par les licences, §11).
- **Programme ambassadeurs** — commission 10 % (`AG_COMMISSION_RATE`), override parrainage (`ag_override_rate`), liens `?ref=` (vente) / `?parrain=` (recrutement), attribution auto au brief, classement jour/mois/général, page Programme + vidéo configurable (`ag_amb_guide_video`).
- **Inscription ambassadeur durcie (KYC)** : **selfie en direct obligatoire**, **Telegram obligatoire**, zone attribuée auto, **onboarding en assistant fléché** (1 étape à la fois).
- **Recrutement de recruteurs** : page « Deviens recruteur » + **classement des recruteurs**, **prime de parrainage 25 €** auto à la 1re vente du filleul, outil SMS/WhatsApp perso (`{prenom}`) + simulateur de gains + **mini-CRM des futurs ambassadeurs**.
- **Analytics / pub / SEO** : GA4 `G-RSQ6Y8DHK4` + Google Ads avec **Consent Mode RGPD** ; **AdSense** `ca-pub-4272988112057548` + `ads.txt` ; `robots.txt` ouvert. **Merchant Center** : pages `/retours` + `/livraison`. **Branding** : logo lion + « AG » (header transparent + bannière OG).

## 7. Design

- Sections sombres (`--graphite`/`--onyx`) + **sections claires** (`.ag-section--light`, ivoire + texte foncé) → alterner clair/sombre, accent **doré** partout.
- Nav : logo à gauche, accordéon (☰) à droite, barre compacte. Curseur perso désactivé. `scroll-padding-top` + padding 1er bloc pour que rien ne soit caché sous la nav fixe.
- Mobile-first (9/10 visiteurs). `@media(max-width:768px)`.

## 8. Expérience immersive 3D — « Le Voyage Alliance »

- **Template** — `templates/page-experience.php` (`Template Name: Expérience immersive (style theirisk.com)`, body class `page-template-page-experience`). Parcours plein écran (`position:fixed;inset:0`) en **4 stations**, navigation clavier (flèches), swipe tactile, boutons BACK/NEXT, et « Commencer le voyage » qui déclenche une transition « plongée » (zoom caméra + flash `warp`).
  - **01 Bureau** : modèle `macbook_pro_2021.glb`, fond vidéo Naples (`naples.mp4`).
  - **02 Vésuve** : `mt._vesuvius_italy.glb` (relief exagéré ×2.6), photo Naples en fond, rotation au drag.
  - **03 Marrakech** : `marrakech-tower.glb` (+ `moroccan_street_light.glb` prévu en extra), drag.
  - **04 L'Univers** : `need_some_space.glb` (galaxie en nuage de points) + **constellation** de 5 étoiles cliquables (orbs x/y en %, lignes SVG). Clic sur une étoile = « plongée dans l'étoile » → cartes d'offres flottantes (CSS) vers les vrais slugs (sites-express, programme-ambassadeur, audit-seo, programme-racines, studio…).
- **Techno** : **Three.js r0.160** en **modules ES** + `<script type="importmap">` depuis jsDelivr. Loaders **GLTFLoader** + **DRACOLoader** (décodeur Draco via jsDelivr). PBR via `RoomEnvironment` + `PMREMGenerator`.
- **Stockage** : la **lib Three vient du CDN**, mais **les `.glb` sont LOCAUX** dans `assets/images/img_3d/` (`macbook_pro_2021.glb`, `mt._vesuvius_italy.glb`, `marrakech-tower.glb`, `moroccan_street_light.glb`, `need_some_space.glb`). Photos villes : `assets/images/cities/`. Vidéos : `assets/videos/`. Option musique `ag_xp_music`.
- **Chargement** : **lazy par station** (`loadStation(i)` + cache + spinner `.agx__loader`), vidéo/audio `preload="none"`, `loadFirst()` essaie plusieurs modèles en fallback sans casser.
- **Pièges perf/mobile** : `setPixelRatio` capé à **1.6**, échec de modèle géré (warn + null, pas de crash), auto-centrage/cadrage par bounding sphere, `ResizeObserver`, `touch-action:pan-y`, nav/footer du thème masqués.
- ⚠️ **Limite connue** : pas de dossier `assets/audio/` → si `ag_xp_music` pointe vers `assets/audio/naples.mp3`, **404 sur le fond sonore** tant que le fichier n'est pas ajouté. Aussi : pas de garde `prefers-reduced-motion` sur cette page (contrairement aux scènes ci-dessous).
- **Scènes 3D réutilisables (accueil, pas la XP)** :
  - `template-parts/hero-3d-scene.php` : smartphone 3D + particules. Three via CDN `three.min.js` (`window.THREE`). Garde-fous : skip si `prefers-reduced-motion` ou `hardwareConcurrency<2`, qualité dégradée si <4 cœurs / <768px, DPR ≤1.5, pause hors viewport.
  - `template-parts/globe-3d.php` : globe terrestre + markers Nantes/Marrakech/Naples (⚠️ textures **distantes** depuis threejs.org), lazy-load, pause hors viewport.
  - `template-parts/atelier-3d.php` : dodécaèdre champagne + satellites, lazy via IntersectionObserver.

## 9. Acquisition / cadeaux (lead magnets)

- **Audit SEO gratuit** — `inc/ag-audit-seo.php` + `templates/page-audit-seo.php` : 12 checks serveur sur une URL, **score /100**, rapport HTML imprimable, **lead capté dans le CRM** (`ag_prospect_add_record` + `ag_push`). Endpoints `ag_audit_request` (priv + nopriv), nonce `ag_audit`, honeypot `hp_field`, rapport en transient 7 j.
- **Tirage au sort mensuel** — `inc/ag-tirage-mensuel.php` + `templates/page-tirage-au-sort.php` : 1 site gratuit/mois. Inscription front (`ag_tirage_join`), **cron mensuel** `ag_tirage_cron` (+ tirage manuel admin `ag_tirage_draw`). Options `ag_tirage_entries` / `ag_tirage_winners`. Lead → CRM + push.
- **Kit Print** — `inc/ag-kit-print.php` (sous-menu de `ag-ambassadeurs`) : génère cartes de visite, flyer A5, stickers, affiche A4 avec **QR code de parrainage** (api.qrserver.com), modes `ambassador`/`brand`. Impression via `ag_kp_print` (HTML brut).
- **Demo board (social proof)** — `inc/ag-demo-board.php` : injecte **4 ambassadeurs + 6 recruteurs démo** dans les classements **publics** (uniquement période « général »), via filtres `ag_ambassadeur_leaderboard` / `ag_recruiter_leaderboard`. Option `ag_demo_leaderboard_on` (défaut on). Emails en `*@demo.alliancegroupe.local`. **À couper le jour où il y a assez de vrais inscrits.**
- **Acquisition générale** : accueil priorisé (vendre → recruter → caritatif Racines), bloc Studio, bloc assos site gratuit, **pop-up ambassadeur** (`template-parts/ambassador-popup.php`), templates WordPress métiers gratuits (aimant à prospects).

## 10. Zones & recrutement (territorial + international)

- **Zones ambassadeurs** — `inc/ag-zones.php` : zones **par département** (FR + régions Maroc `MA-XXX`), **co-propriété multi-ambassadeurs** avec **rotation 50/50** des leads, mini-CRM de recrutement, **Chasseur Pro** et **zones supplémentaires payantes**. Options : `ag_zones`, `ag_recruits`, `ag_zone_price` (défaut 49 €), `ag_zone_paypal_url`, `ag_chasseur_paypal_url`. **Inactivité 7 jours → retrait auto de la zone** (cron `ag_amb_inactivity_cron`, daily 8h, + rappel). Carte admin `ag-zones-map`.
- **Recrutement international** — `inc/ag-recrut-intl.php` (sous-menu `ag-ambassadeurs`) : **10 pays francophones** (FR, BE, CH, QC, MA, DZ, TN, SN, CI, CM) avec **canaux curés** (forums/FB/Reddit/Telegram), **messages prêts par pays**, **liens parrain UTM-trackés** (`source=forum`, `campaign=recrut-intl`), marquage « posté » (`ag_ri_posts`). Placeholders `{prenom_recruteur}`, `{parrain_link}`, `{prime}`. Recrutement **manuel assisté** (conforme, pas de spam auto).

## 11. Licences de templates (vente de thèmes)

> Système **totalement séparé** du programme ambassadeurs/commissions (aucune référence croisée). Deux voies de paiement convergent vers la même génération de clé.

- **Liens de paiement** — `ag-stripe-admin.php` (Réglages → écran « Liens de paiement », mal nommé « stripe ») : on colle des liens HTTPS (PayPal/banque/SumUp) par offre. Options `ag_stripe_*_url` (premium, business, questions, consultations, packs express, maintenance…). `ag_stripe_premium_url` / `ag_stripe_business_url` sont relus par l'API companion.
- **Vente via PayPal** — `inc/ag-licence-paypal.php` : se branche sur le hook `ag_paypal_payment_verified` (de `ag-paypal.php`), agit sur `PAYMENT.CAPTURE.COMPLETED`, détermine le tier (`custom_id`/`invoice_id` préfixé `ag_licence:` sinon par montant), génère + envoie la clé. Options `ag_licence_price_premium` (99), `ag_licence_price_business` (149).
- **Plugin séparé `ag-licence-manager/`** (auto-déployé + activé par `ag-import.php` lors de la SYNC GitHub) :
  - **DB** `class-ag-licence-db.php` : table `{prefix}ag_licences`, clés `AGPRM…`/`AGBUS…` (uuid4), hash sha256 + chiffrement aes-256-cbc (constante `AG_LICENCE_HMAC_KEY` à définir en `wp-config`). Statuts inactive/active/expired/revoked, idempotence sur colonne `stripe_session`.
  - **API REST** `class-ag-licence-api.php` (namespace `ag/v1`, signature `X-AG-Signature`, rate limit 15/min) : `/licence/activate`, `/licence/resend`, `/licence/verify`, `/licence/deactivate`, `/update-check`, `/companion-update`. Tokens download en transient 1h.
  - **Webhook Stripe** `class-ag-licence-stripe.php` : `POST /wp-json/ag/v1/stripe-webhook`, event `checkout.session.completed`, signature HMAC (anti-rejeu 300s), tier via `metadata.ag_tier` sinon montant.
  - **Admin** `class-ag-licence-admin.php` : menu `ag-licence-manager` (liste/génération/édition/versions/stats, révoquer/réactiver/renvoyer/reset domaine/upgrade).
  - **Email** `class-ag-licence-email.php` : envoi de la clé (expéditeur `contact@alliancegroupe-inc.com`).
- **Import / SYNC** — `ag-import.php` (Outils → Import AG) : import de contenu + **SYNC GitHub du thème** (télécharge les fichiers, purge cache, auto-déploie le plugin licences). Options de vérif moteurs (`ag_gsc_verification`, `ag_bing_verification`, etc.).

## 12. Admin Hub & pages du site

- **Admin Hub** — `inc/ag-admin-hub.php` : **un seul écran de pilotage** (chiffres clés : prospects/leads/ventes/briefs/demandes sur-mesure, état des réglages, actions fréquentes) + **widget « Quoi de neuf »** sur le dashboard WordPress. Menu `ag-hub` (+ sous-menu `ag-sur-mesure`). Permet d'envoyer le message clients et de gérer l'auto-message quotidien.
- **Pages du site** (toutes avec `Template Name`, rattachables à une page WP, hero maison `.ag-hero`) :
  - **Bureaux** : `page-bureau-naples.php` (accent vert heritage), `page-bureau-nantes.php` (bleu France), `page-bureau-marrakech.php` (vert + zellige).
  - **Marque / confiance** : `page-fondateur.php` (storytelling fondateur), `page-pourquoi-alliance.php` (argumentaire Alliance vs templates ThemeForest).
  - **Services** : `page-services.php` (sommaire) + 6 pages de vente : `page-service-web.php`, `-seo.php`, `-ads.php`, `-ia.php`, `-brand.php`, `-conseil.php`.
  - **Offres / conversion** : `page-sites-express.php`, `page-sur-mesure.php` (devis + configurateur), `page-consultation.php` (1:1 payante + écran « paiement confirmé »), `page-questions-flash.php` (réponses écrites rapides), `page-systeme-prospection.php` (présentation du système), `page-programme-racines.php` (solidaire/caritatif).

## 13. Le MEILLEUR système selon le type de site/repo

| Type de site | Briques à activer | À éviter |
|---|---|---|
| **Agence / vente de services** (Alliance Groupe) | Sites Express + PayPal auto + Ambassadeurs/commissions + Zones + Studio + Prospection/CRM + Coach + Cadeaux (Audit/Tirage) + Notifs + Licences | — |
| **Association / militant** (quartierlibre.org ?) | Offre site **gratuit**, **canal Telegram** (mobilisation), espaces **adhérents/bénévoles**, chat de capture + CRM, notifs | volet vente/commissions/licences si non commercial |
| **Commerce / e-commerce** | Boutique (pack Boutique), Studio (promo produits), canal clients (offres), notifs commandes | — |
| **Communautaire / média** | Canal général (annonces), espaces membres, chat de capture | paiement si pas de monétisation |

## 14. Réutiliser sur un NOUVEAU site (checklist)

1. Copier `alliance-groupe-theme` (ou seulement les `inc/*.php` voulus) sur le nouveau site.
2. Adapter marque/couleurs/offres/textes.
3. Reconfigurer : ID client Google, PayPal (Client ID/Secret/Webhook), clé Places, canaux Telegram + token, WhatsApp CallMeBot, email de notif (`ag_calendar_notify_email`).
4. Si vente de templates : définir `AG_LICENCE_HMAC_KEY` en `wp-config`, renseigner les liens `ag_stripe_*_url` et prix licences, laisser la SYNC déployer `ag-licence-manager`.
5. Couper le **demo board** (`ag_demo_leaderboard_on`) dès qu'il y a de vrais inscrits.
6. `php -l` → commit → SYNC.

## 15. quartierlibre.org

- **Repo séparé** : je ne peux pas y pousser d'ici → me donner son dépôt/accès.
- Confirmer son **objectif** (adhésions ? dons ? mobilisation ?) puis porter les modules adaptés (section 13, ligne « Association »).
