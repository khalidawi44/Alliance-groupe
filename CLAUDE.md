# CLAUDE.md — Infrastructure Alliance Groupe (réutilisable multi-sites)

But de ce fichier : **mémoire de l'infrastructure** mise en place, pour la **réutiliser sur tous les sites gérés** (Alliance Groupe, quartierlibre.org, etc.) et **conseiller** le propriétaire (Fabrice / advise.alliance.group@gmail.com).

/ Lu automatiquement à chaque session : j'y puise pour aider et conseiller. /

> 📚 Détail complet (raccourcis, création vidéo, agents, meilleur système par type de site, config, limites) : **`INFRASTRUCTURE.md`**.

## Déploiement (workflow)
- Dév sur la branche de travail courante (à ce jour `claude/site-config-commits-nZtU1`), puis **merge ff-only dans `main`** + push.
- Le site applique via **Apparence → SYNC GitHub** : « Vérifier MAJ » puis « SYNC FICHIERS DU THÈME », puis purge cache + Ctrl/recharge.
- Thème autonome (pas d'Elementor). Templates = `alliance-groupe-theme/templates/page-*.php` (Template Name). Logique = `alliance-groupe-theme/inc/*.php`, chargés depuis `functions.php`.
- Toujours `php -l` avant commit. Indentation = tabulations.

## Briques d'infrastructure (réutilisables)

### 1. Espaces membres & auth — `inc/ag-espaces.php`
- Rôles `ag_client` / `ag_ambassadeur` ; helpers `ag_espace_member_kind()`, `ag_espace_url()`.
- Auth front-end maison (`/connexion`, `/mot-de-passe`) ; wp-login & wp-admin bloqués pour non-admins.
- **Inscription email** + **Connexion Google** (Sign in with Google, ID client par défaut intégré ; option `ag_google_client_id`).
- Emails de bienvenue brandés (`ag_email_wrap`/`ag_email_button`, expéditeur « Alliance Groupe »).
- Pages auto-créées (flag `ag_espaces_pages_v5`).
- **No-cache** pour les connectés (évite la « déconnexion » après purge).
- **Notif agenda Google** (`ag_calendar_notify`) : .ics envoyé à l'email + push téléphone.

### 2. Paiement & commissions — `inc/ag-paypal.php`, `ag-stripe-admin.php`
- Liens de paiement PayPal par offre (Réglages → Liens de paiement). Packs Sites Express 490/890/1490 + maintenance 29/59/99.
- **PayPal automatique (webhooks)** : Réglages → PayPal automatique (Client ID/Secret/Webhook ID/email). Vérif signature → crédite la commission ambassadeur (rapprochement montant+email, 2 sens).

### 3. Programme ambassadeurs & recruteurs — `inc/ag-ambassadeurs.php`, `templates/page-espace-ambassadeur.php`, `page-guide-ambassadeur.php`, `page-classement.php`
- Commission 10 % (`AG_COMMISSION_RATE`), parrainage/override (`ag_override_rate`), liens `?ref=` (vente) et `?parrain=` (recrutement), attribution auto au brief.
- Dashboard + Programme (onboarding **en assistant fléché**, 1 étape à la fois ; vidéo configurable `ag_amb_guide_video`) + Classement (jour/mois/général).
- **Inscription durcie (KYC)** : **selfie en direct obligatoire** + **Telegram obligatoire** + zone attribuée auto.
- **Recrutement de recruteurs** : page « Deviens recruteur » + **classement des recruteurs**, **prime de parrainage 25 €** auto à la 1re vente du filleul, outil SMS/WhatsApp perso (`{prenom}`) + simulateur de gains + **mini-CRM des futurs ambassadeurs**.
- **Zones** : 1 zone max (sauf zones supplémentaires payées) ; conservées à vie **sauf inactivité 7 jours** (retrait auto + rappel).

### 4. Studio créatif — `templates/page-studio.php`
- Ouvert à tous. Vidéo (canvas + MediaRecorder, **textes animés** séquentiels, police/couleur, fonds villes propres) + image. Partage fichier natif + légende auto-copiée. Lien perso intégré si vendeur connecté (admin inclus via `ag_ensure_ambassador_for_user`).

### 5. Prospection / CRM — `inc/ag-prospection.php` (menu **Prospection**)
- **Chat équipe** (template-parts/prospect-chat.php) : Léo→Sofia→Karim→Nadia, capture de leads.
- **Chasse** Google Places (New) (option `ag_places_key`) : repère sans vrai site (réseaux sociaux = pas un vrai site), **score « probabilité d'achat »** (avis, note, joignable), **balayage tous secteurs** d'une ville, **agent auto** (cron quotidien).
- **CRM partagé** : statuts (nouveau→contacté→relancé→sans réponse→intéressé→client→refusé→ne plus contacter), filtres/tri, **anti-doublon global**, **assignation** à un ambassadeur (1 proprio = pas de double-contact), message **émotionnel** personnalisé + « pourquoi ».

### 6. Notifications & diffusion — `inc/ag-prospection.php` (Réglages → Notifications téléphone)
- `ag_push()` (interne : WhatsApp CallMeBot 1:1 + Telegram canal interne) ; `ag_push_clients()` (canal général Telegram).
- **2 canaux Telegram** : interne (équipe, alertes confidentielles) / général (clients, `@ALLIANCE_GROUPE`). Liens d'invitation envoyés à l'inscription (ambassadeur→groupe, client→canal).
- **Message quotidien clients** auto (banque urgence/offre limitée/exclusivité, cron 9h, option `ag_client_daily_on`) + **message quotidien groupe ambassadeurs**.
- **Alertes SMS via API Free (ACTIVES)** sur la ligne pro `07 44 82 95 16` : inscriptions, messages clients, devis (+ .ics Google Agenda). WhatsApp CallMeBot = optionnel. Bouton « Test SMS » dédié.
- Limites Telegram à retenir : un bot ne peut PAS auto-ajouter au groupe ni poster dans un groupe WhatsApp ; Telegram ne donne pas d'email (pas de compte auto depuis Telegram).

### 7. Acquisition / marketing
- Accueil priorisé (vendre → recruter → caritatif Racines) + bloc **Studio** + bloc **assos site gratuit**.
- **Pop-up ambassadeur** (template-parts/ambassador-popup.php). Menu « 🚀 Gagner » (Sites Express/Studio/Ambassadeur/Classement/Espace) + entrées « Deviens recruteur ».
- SEO meta `inc/ag-seo-meta.php`. Templates WordPress métiers gratuits (aimant à prospects).
- **Analytics & pub** : GA4 `G-RSQ6Y8DHK4` + Google Ads avec **Consent Mode RGPD** ; **AdSense** `ca-pub-4272988112057548` + `ads.txt` ; `robots.txt` ouvert (AdSense/AdsBot).
- **Conformité Merchant Center** : pages `/retours` (offres numériques) et `/livraison`.
- **Branding** : logo tête de lion + « AG » (header transparent `logo-header.png`, bannière OG) ; burger centré sur toutes tailles (PC inclus).

## Réutiliser sur un autre site
- Le thème est autonome : copier `alliance-groupe-theme` (ou les `inc/*.php` voulus) sur l'autre site, adapter marque/couleurs/offres. Chaque brique est indépendante (guards `function_exists`).
- Reconfigurer par site : ID client Google, PayPal, clé Places, canaux Telegram, email de notif (`ag_calendar_notify_email`).

## quartierlibre.org — à conseiller
- **Site séparé (autre dépôt)** : je ne peux pas y pousser de code d'ici. Pour agir dessus, il faut son dépôt/accès.
- Contexte présumé : projet communautaire/associatif/militant. À CONFIRMER avec le proprio (objet, public, but : adhésions ? dons ? mobilisation ?).
- Briques pertinentes à réutiliser : **offre asso/site gratuit**, **canal Telegram général** (mobilisation/annonces), **chat de capture + CRM** (bénévoles/adhérents), **espaces membres** (adhérents), **notifications**. Éviter le volet vente/commissions si non commercial.
- Prochaine étape : obtenir le dépôt quartierlibre.org + préciser son objectif, puis y porter les modules adaptés.
