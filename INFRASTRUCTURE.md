# INFRASTRUCTURE.md — Base de connaissances réutilisable (Fabrice / Alliance Groupe)

> Mémo central de tout ce qu'on a construit, **réutilisable sur tous tes sites** (Alliance Groupe, quartierlibre.org, futurs repos).
> Mon accès GitHub est limité au repo `khalidawi44/alliance-groupe` : copie ce fichier dans tes autres repos pour l'y réutiliser.
> Résumé court = `CLAUDE.md` (lu auto à chaque session). Détail complet = ce fichier.

---

## 1. Workflow & raccourcis

- **Branche de dev** : branche de travail courante (à ce jour `claude/site-config-commits-nZtU1`) → **merge ff-only dans `main`** → push.
- **Mise en ligne** : WordPress → **Apparence → SYNC GitHub** : « Vérifier MAJ » → « SYNC FICHIERS DU THÈME » → **purger le cache** → **Ctrl+F5** (recharger l'onglet sur mobile).
- **Avant chaque commit** : `php -l fichier.php` (jamais committer du PHP cassé). **Indentation = tabulations**.
- **Thème autonome** (pas d'Elementor). Templates = `templates/page-*.php` (entête `Template Name`). Logique = `inc/*.php` chargés par `functions.php`. Blocs = `template-parts/*.php`.
- Chaque brique est **indépendante** (guards `function_exists` / `if (!defined)`), donc copiable seule.

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
- Évènements poussés : prospect qui répond, intéressé, vente, nouvel ambassadeur, message quotidien clients.
- **Limites à connaître** : un bot **ne peut PAS** auto-ajouter des gens à un groupe/canal, ni poster dans un **groupe WhatsApp** ; Telegram **ne fournit pas d'email** (donc pas de compte site auto depuis Telegram). À l'inscription on **envoie le lien d'invitation** (ambassadeur→groupe interne, client→canal général).

## 6. Auth, Paiement, Commissions

- **Espaces membres** — `inc/ag-espaces.php` : rôles `ag_client`/`ag_ambassadeur`, `/connexion` + `/mot-de-passe` maison, wp-login/wp-admin bloqués pour non-admins, no-cache pour connectés.
- **Inscription email** + **Connexion Google** (Sign in with Google ; option `ag_google_client_id` ; ID redirige vers `admin-post.php?action=ag_google_login`). Écran de consentement à **publier** côté Google Cloud.
- **PayPal automatique** — `inc/ag-paypal.php` : webhooks REST (Client ID/Secret/Webhook ID/email), vérif signature → **crédite la commission** (rapprochement montant+email, 2 sens). URL webhook = `admin-post.php?action=ag_paypal_webhook`.
- **Programme ambassadeurs** — commission 10 % (`AG_COMMISSION_RATE`), override parrainage (`ag_override_rate`), liens `?ref=` (vente) / `?parrain=` (recrutement), attribution auto au brief, classement jour/mois/général, page Programme + vidéo configurable (`ag_amb_guide_video`).
- **Inscription ambassadeur durcie (KYC)** : **selfie en direct obligatoire**, **Telegram obligatoire**, zone attribuée auto, **onboarding en assistant fléché** (1 étape à la fois).
- **Recrutement de recruteurs** : page « Deviens recruteur » + **classement des recruteurs**, **prime de parrainage 25 €** auto à la 1re vente du filleul, outil SMS/WhatsApp perso (`{prenom}`) + simulateur de gains + **mini-CRM des futurs ambassadeurs**.
- **Zones** : 1 zone max sauf zones supplémentaires payées (offre + quota) ; conservées à vie **sauf inactivité 7 jours** (retrait auto + rappel).
- **Analytics / pub / SEO** : GA4 `G-RSQ6Y8DHK4` + Google Ads avec **Consent Mode RGPD** ; **AdSense** `ca-pub-4272988112057548` + `ads.txt` ; `robots.txt` ouvert. **Merchant Center** : pages `/retours` + `/livraison`. **Branding** : logo lion + « AG » (header transparent + bannière OG).

## 7. Design

- Sections sombres (`--graphite`/`--onyx`) + **sections claires** (`.ag-section--light`, ivoire + texte foncé) → alterner clair/sombre, accent **doré** partout.
- Nav : logo à gauche, accordéon (☰) à droite, barre compacte. Curseur perso désactivé. `scroll-padding-top` + padding 1er bloc pour que rien ne soit caché sous la nav fixe.
- Mobile-first (9/10 visiteurs). `@media(max-width:768px)`.

## 8. Le MEILLEUR système selon le type de site/repo

| Type de site | Briques à activer | À éviter |
|---|---|---|
| **Agence / vente de services** (Alliance Groupe) | Sites Express + PayPal auto + Ambassadeurs/commissions + Studio + Prospection/CRM + Notifs | — |
| **Association / militant** (quartierlibre.org ?) | Offre site **gratuit**, **canal Telegram** (mobilisation), espaces **adhérents/bénévoles**, chat de capture + CRM, notifs | volet vente/commissions si non commercial |
| **Commerce / e-commerce** | Boutique (pack Boutique), Studio (promo produits), canal clients (offres), notifs commandes | — |
| **Communautaire / média** | Canal général (annonces), espaces membres, chat de capture | paiement si pas de monétisation |

## 9. Réutiliser sur un NOUVEAU site (checklist)

1. Copier `alliance-groupe-theme` (ou seulement les `inc/*.php` voulus) sur le nouveau site.
2. Adapter marque/couleurs/offres/textes.
3. Reconfigurer : ID client Google, PayPal (Client ID/Secret/Webhook), clé Places, canaux Telegram + token, WhatsApp CallMeBot, email de notif (`ag_calendar_notify_email`).
4. `php -l` → commit → SYNC.

## 10. quartierlibre.org

- **Repo séparé** : je ne peux pas y pousser d'ici → me donner son dépôt/accès.
- Confirmer son **objectif** (adhésions ? dons ? mobilisation ?) puis porter les modules adaptés (section 8, ligne « Association »).
