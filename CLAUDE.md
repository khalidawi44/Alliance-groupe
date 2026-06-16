# CLAUDE.md — Infrastructure Alliance Groupe (réutilisable multi-sites)

But de ce fichier : **mémoire de l'infrastructure** mise en place, pour la **réutiliser sur tous les sites gérés** (Alliance Groupe, quartierlibre.org, etc.) et **conseiller** le propriétaire (Fabrice / advise.alliance.group@gmail.com).

/ Lu automatiquement à chaque session : j'y puise pour aider et conseiller. /

> 📚 Détail complet (raccourcis, création vidéo, agents, meilleur système par type de site, config, limites) : **`INFRASTRUCTURE.md`**.

## Continuité entre conversations (IMPORTANT)
- Sur Claude Code web, **chaque conversation démarre dans un conteneur neuf** : seul ce qui est **commité + poussé** survit.
- **Hook `SessionStart`** (`.claude/hooks/session-start.sh`, enregistré dans `.claude/settings.json`) : à chaque nouvelle session il injecte automatiquement l'état réel (branche, derniers commits, travail non commité, version active, focus templates, en-tête `HANDOFF.md`). C'est ce qui **relie les conversations** entre elles.
- **Règle de fin de session** : avant de fermer, (1) mettre à jour les 3 premières lignes + §9 de `HANDOFF.md`, (2) commiter, (3) pousser. La conversation suivante reprendra exactement là.

## Convention UX (RÈGLE pour tout nouvel outil/liste)
- **Toute liste d'éléments** (prospects, cibles, fiches, résultats…) doit offrir par défaut : **TRI** (au moins priorité + nom + récent), **AJOUT** (bouton « + Suivre »/ajouter), et **SUPPRESSION EN MASSE** (cases à cocher + « tout sélectionner » + bouton « Supprimer la sélection » ; pour des prospects, AJAX `ag_prospect_delete_bulk`). Les actions de contact (Mail/SMS/WhatsApp/Appel) + **Relancer** sont présentes sur chaque élément, en respectant la déontologie (avocats = email/courrier uniquement).

## Déploiement (workflow)
- Dév sur la branche de travail courante (ex. `claude/infrastructure-md-review-6R3D2` ; vérifier `git branch --show-current`), puis **merge ff-only dans `main`** + push.
- Le site applique via **Apparence → SYNC GitHub** : « Vérifier MAJ » puis « SYNC FICHIERS DU THÈME », puis purge cache + Ctrl/recharge.
- Thème autonome (pas d'Elementor). Templates = `alliance-groupe-theme/templates/page-*.php` (Template Name). Logique = `alliance-groupe-theme/inc/*.php`, chargés depuis `functions.php`.
- Toujours `php -l` avant commit. Indentation = tabulations.
- **MAJ des templates vendus (RÈGLE) : tout changement dans le code d'un template (`assets/downloads/<slug>/`) DOIT être publié via `bash scripts/release.sh <slug> <version>`** (bump version header+constante + `.json` + rebuild du `.zip` + push + merge main). Sinon l'acheteur ne reçoit JAMAIS la MAJ (auto-update = compare la version du `.json` sur `main` et télécharge le `.zip`). Vérifier à tout moment : `bash scripts/check-releases.sh` (versions cohérentes + zips = source). Le hook pre-commit alerte si on édite un template sans rebuild son zip.
- **PUSH d'un thème sur WordPress.org (RÈGLE, à faire SYSTÉMATIQUEMENT à chaque thème poussé) : produire un build « .org propre » via `bash scripts/wporg-clean.sh <slug>`** → sort dans `wporg-builds/<slug>/` + `.zip`. Le script retire AUTO (commun) : updater GitHub + theme-updater, client de licence phone-home, filtre auto-MAJ `ag_force_auto_updates`, désactivation des commentaires, unregister des widgets/sidebars, `remove_menu_page` core. Puis TRAITER À LA MAIN ce qu'il signale : **CPT/taxo dans le thème → déplacer dans le plugin Companion** (interdit .org), appels externes restants, **marque « WordPress » marketing** (description/readme — autorisée seulement en mention technique « Requires/Compatible »), **crédit footer = 1 seul lien `rel="nofollow"`**. Modèle Divi/Astra : le **gratuit** va sur wordpress.org (funnel), le **premium** se vend sur le site (licence + serveur de MAJ). ⚠️ « AG Sync » = mu-plugin de DEV local (`wp-content/mu-plugins/ag-dev-sync.php`), jamais dans le thème → à supprimer en local quand le template est final. Toujours finir par **Theme Check (0 ERROR)** côté Fabrice.

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
- Liens de paiement PayPal par offre (Réglages → Liens de paiement, options `ag_stripe_*_url`). Packs Sites Express 490/890/1490 + maintenance 29/59/99.
- **PayPal automatique (webhooks)** : Réglages → PayPal automatique (Client ID/Secret/Webhook ID/email). Vérif signature → crédite la commission ambassadeur (rapprochement montant+email, 2 sens). Émet le hook `ag_paypal_payment_verified` (réutilisé par les licences, brique 10).

### 3. Programme ambassadeurs & recruteurs — `inc/ag-ambassadeurs.php`, `templates/page-espace-ambassadeur.php`, `page-guide-ambassadeur.php`, `page-classement.php`
- Commission 10 % (`AG_COMMISSION_RATE`), parrainage/override (`ag_override_rate`), liens `?ref=` (vente) et `?parrain=` (recrutement), attribution auto au brief.
- Dashboard + Programme (onboarding **en assistant fléché**, 1 étape à la fois ; vidéo configurable `ag_amb_guide_video`) + Classement (jour/mois/général).
- **Inscription durcie (KYC)** : **selfie en direct obligatoire** + **Telegram obligatoire** + zone attribuée auto.
- **Recrutement de recruteurs** : page « Deviens recruteur » + **classement des recruteurs**, **prime de parrainage 25 €** auto à la 1re vente du filleul, outil SMS/WhatsApp perso (`{prenom}`) + simulateur de gains + **mini-CRM des futurs ambassadeurs**.
- **Zones** (`inc/ag-zones.php`) : zones **par département** (FR + régions Maroc `MA-XXX`), co-propriété multi-ambassadeurs + **rotation 50/50** des leads, **Chasseur Pro** et zones supplémentaires payantes (`ag_zone_price` défaut 49 €) ; conservées à vie **sauf inactivité 7 jours** (cron `ag_amb_inactivity_cron`, retrait auto + rappel). Carte admin `ag-zones-map`.
- **Recrutement international** (`inc/ag-recrut-intl.php`) : 10 pays francophones, canaux curés + messages prêts, liens parrain UTM, marquage « posté » (manuel, conforme).

### 4. Studio créatif — `templates/page-studio.php`
- Ouvert à tous. Vidéo (canvas + MediaRecorder, **textes animés** séquentiels, police/couleur, fonds villes propres) + image. Partage fichier natif + légende auto-copiée. Lien perso intégré si vendeur connecté (admin inclus via `ag_ensure_ambassador_for_user`).
- **Vidéos pro (Remotion) — on peut en créer ensemble** : dossier `video-remotion/` (`AGShort.tsx` = moteur de shorts verticaux 1080×1920 à la marque or/orange ; `scripts.ts` = les vidéos décrites en **données** (scènes : `hook`/`point`/`cta`, `headline`, `caption`, `bg`, durée) ; `Root.tsx` = les compositions). Pour ajouter/continuer une vidéo : éditer `scripts.ts` (+ enregistrer dans `Root.tsx`). Rendu sur le PC : copier ces fichiers dans le repo `alliance-videos` (src/) + images/clips dans `public/`, puis `npx remotion studio` (aperçu) / `npx remotion render <id> out/x.mp4`. Compositions actuelles : `AG-Recrutement` (Naples), `AG-Vente-247`, `AG-Luxe`, `AG-Naples-Suite`.

### 5. Prospection / CRM — `inc/ag-prospection.php` (menu **Prospection**)
- **Chat équipe** (template-parts/prospect-chat.php) : Léo→Sofia→Karim→Nadia, capture de leads.
- **Chasse** Google Places (New) (option `ag_places_key`) : repère sans vrai site (réseaux sociaux = pas un vrai site), **score « probabilité d'achat »** (avis, note, joignable), **balayage tous secteurs** d'une ville, **agent auto** (cron quotidien).
- **CRM partagé** : statuts (nouveau→contacté→relancé→sans réponse→intéressé→client→refusé→ne plus contacter), filtres/tri, **anti-doublon global**, **assignation** à un ambassadeur (1 proprio = pas de double-contact), message **émotionnel** personnalisé + « pourquoi ».
- **Agent Coach** (`inc/ag-coach.php`) : feuille de route quotidienne (relances/leads/ventes/briefs) poussée Telegram + email à 8h (cron `ag_coach_daily`, option `ag_coach_on`).

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

### 8. Expérience immersive 3D — `templates/page-experience.php` (« Le Voyage Alliance »)
- Parcours plein écran en **4 stations** (Bureau / Vésuve / Marrakech / Univers), nav clavier/swipe/boutons. Three.js r0.160 (CDN) + GLTFLoader/DRACOLoader.
- **Modèles `.glb` LOCAUX** dans `assets/images/img_3d/` ; chargement **lazy par station** + cache + spinner ; `pixelRatio` capé 1.6, auto-cadrage par bounding sphere, fallback sans crash.
- ⚠️ Pas de dossier `assets/audio/` → fond sonore `ag_xp_music` en 404 tant que le mp3 n'est pas ajouté. Pas de garde `prefers-reduced-motion` (≠ scènes accueil `hero-3d-scene`/`globe-3d`/`atelier-3d`).

### 9. Cadeaux d'acquisition (lead magnets)
- **Audit SEO gratuit** (`inc/ag-audit-seo.php` + `page-audit-seo.php`) : 12 checks, score /100, rapport imprimable, lead → CRM + push.
- **Tirage au sort mensuel** (`inc/ag-tirage-mensuel.php` + `page-tirage-au-sort.php`) : 1 site/mois, cron mensuel `ag_tirage_cron` + tirage manuel.
- **Kit Print** (`inc/ag-kit-print.php`, sous-menu ambassadeurs) : cartes/flyer/stickers/affiche avec QR de parrainage.
- **Demo board** (`inc/ag-demo-board.php`) : 4 ambassadeurs + 6 recruteurs démo dans le classement **général** (social proof). **Couper `ag_demo_leaderboard_on` dès assez de vrais inscrits.**

### 10. Licences de templates — `ag-stripe-admin.php`, `inc/ag-licence-paypal.php`, plugin `ag-licence-manager/`
- Système **séparé** des commissions ambassadeurs. 2 voies de paiement (webhook Stripe `/wp-json/ag/v1/stripe-webhook` + hook `ag_paypal_payment_verified`) → même génération de clé (`AG_Licence_DB::insert`) + email.
- Plugin **auto-déployé/activé par `ag-import.php`** à la SYNC GitHub. Table `{prefix}ag_licences`, clés `AGPRM…`/`AGBUS…`, chiffrées AES (constante `AG_LICENCE_HMAC_KEY` à définir en `wp-config`). Prix `ag_licence_price_premium` (99) / `_business` (149).

### 11. Admin Hub — `inc/ag-admin-hub.php`
- **Écran unique de pilotage** (menu `ag-hub`) : chiffres clés (prospects/leads/ventes/briefs/sur-mesure), état des réglages, actions fréquentes + **widget « Quoi de neuf »** sur le dashboard WordPress.

## Réutiliser sur un autre site
- Le thème est autonome : copier `alliance-groupe-theme` (ou les `inc/*.php` voulus) sur l'autre site, adapter marque/couleurs/offres. Chaque brique est indépendante (guards `function_exists`).
- Reconfigurer par site : ID client Google, PayPal, clé Places, canaux Telegram, email de notif (`ag_calendar_notify_email`).

## quartierlibre.org — à conseiller
- **Site séparé (autre dépôt)** : je ne peux pas y pousser de code d'ici. Pour agir dessus, il faut son dépôt/accès.
- Contexte présumé : projet communautaire/associatif/militant. À CONFIRMER avec le proprio (objet, public, but : adhésions ? dons ? mobilisation ?).
- Briques pertinentes à réutiliser : **offre asso/site gratuit**, **canal Telegram général** (mobilisation/annonces), **chat de capture + CRM** (bénévoles/adhérents), **espaces membres** (adhérents), **notifications**. Éviter le volet vente/commissions si non commercial.
- Prochaine étape : obtenir le dépôt quartierlibre.org + préciser son objectif, puis y porter les modules adaptés.
