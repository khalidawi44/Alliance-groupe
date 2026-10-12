# CARTE DES CAPACITÉS — thème Alliance Groupe

> **But de ce fichier :** mémoire technique de « ce que je sais faire et relier » dans le site.
> Je le relis au lieu de réausciter tout le thème. Racine du thème : `alliance-groupe-theme/`.
> Dernière mise à jour : 12/10/2026. Si j'ajoute/modifie une brique, je mets ce fichier à jour (règle de répercussion).
>
> Repères : ~90 fichiers `inc/*.php`, 72 templates `templates/page-*.php`, `functions.php`, `ag-stripe-admin.php` (racine), plugin `ag-licence-manager/`. Chargement des modules : `functions.php` (bloc de `require_once`, chaque brique gardée par `function_exists`/`defined`).

---

## 0) BOÎTE À OUTILS — helpers réutilisables (à appeler, ne pas réécrire)

### Emails (marque) — `inc/ag-espaces.php`
- `ag_email_wrap($titre, $corps, $opts = [])` — gabarit HTML dark+or, responsive, preheader, **signature auto en bas** (`$opts['signature']`, ON par défaut ; `$opts['preheader']` = texte d'aperçu). `$corps`/`$titre` arrivent en HTML non échappé (l'appelant gère `esc_html`).
- `ag_email_button($label, $url)` — bouton dégradé or→orange.
- `ag_email_signature()` — bloc signature manuscrite : image (option `ag_email_signature_img`) sinon repli police cursive (`ag_email_signature_name`, `ag_email_signature_role`).
- `ag_email_card($html)` — encart/tableau mis en avant dans un email.
- `ag_email_brand($cle=null)` — palette + polices centralisées (or `#D4B45C`, orange `#F37A1F`, fonds sombres, polices titre/corps/manu).
- Expéditeur global : filtres `wp_mail_from`/`wp_mail_from_name` (→ « Alliance Groupe » / `contact@`). Logo email : `assets/images/logo-carte.jpg`.
- **Réglages :** Réglages → ✉️ Envoi des e-mails (`inc/ag-smtp.php`) — SMTP + upload signature + aperçu en direct.

### Notifications / diffusion — `inc/ag-prospection.php` (l.3367-3431)
- `ag_push($titre, $corps='', $group=false)` — alerte **à moi** (SMS Free + WhatsApp CallMeBot) ; pousse au groupe Telegram si `$group=true`.
- `ag_push_clients($titre, $corps='')` — diffusion canal clients Telegram.
- `ag_sms($texte)` — SMS vers **ma** ligne pro (API Free `ag_sms_free_user/_key`, ou webhook `ag_sms_webhook`).
- `ag_tg_send($chat_id, $texte)` — message Telegram brut. `ag_tg_cfg($k)` — config Telegram (`token,chat,chan,chan_link,group_link`).
- **SMS sortants vers des tiers** (distinct) — `inc/ag-sms-gateway.php` : `ag_sms_send($to,$msg)`, `ag_sms_send_bulk($pairs)`, opt-out `ag_sms_is_optout/_add/_remove`, inbound REST `/wp-json/ag/v1/inbound`. ⚠️ **En pause : attend une SIM physique** (voir HANDOFF point 9).
- **Push navigateur** — `inc/ag-push.php` : `ag_push_broadcast($titre,$corps,$url='',$audience='all')`, shortcode `[ag_notif]`.

### Autres helpers transverses
- Zones ambassadeurs : `ag_zone_of_owner($email)`, `ag_dept_names()`, `ag_recruit_message()` (`inc/ag-zones.php`).
- Comptes membres : `ag_create_member()`, `ag_espace_url($kind)`, `ag_espace_member_kind($user)`, `ag_send_member_welcome($user,$kind)` (`inc/ag-espaces.php`).
- Surclassement/CTA : `ag_surcl_cta($kind,$nom)` (`inc/ag-surclassement.php`).
- NAP / SEO local : `ag_nap_opt()`, shortcode `[ag_nap]` (`inc/ag-seo-localbiz.php`).

---

## 1) LA CHAÎNE COMMERCIALE AUTONOME (24/7)

**Déclencheur :** cron-job.org frappe `GET /wp-json/ag/v1/run?token=…` toutes ~15 min → `ag_auto_tick()` (synchrone, borné en temps). Filet : WP-Cron `ag_auto_wpcron` horaire. Fichier chef : `inc/ag-autopilot.php` (REST `/run` l.145, `/worker` l.171 ; menu « 🤖 Pilote automatique »).

| Agent | Fichier | Rôle | Interrupteur (défaut) |
|---|---|---|---|
| **Chasse** | `inc/ag-prospection.php` | Google Places (New), score d'achat, auto-amorçage `ag_cm_bank()` + rotation `ag_auto_search_i`, borné ~22 s | `ag_chasse_auto_on` ; clé `ag_places_key` ; plafond `ag_places_cap` (1000) |
| **Enrichisseur** | `inc/ag-enrichir.php` | relit le site public → email de contact + constat factuel (liste blanche, aucune faille) | `ag_enrich_on` (0) ; cron `ag_enrich_cron` 3h |
| **Scoreur** | `inc/ag-scoreur.php` | filtre `ag_closer_file` : trie la file d'Hugo par proba de signer (signaux stockés) | `ag_scoreur_on` (1) |
| **Hugo / Closer** | `inc/ag-closer.php` | séquence 4 emails perso, stop dès réponse, opt-out obligatoire, avocats email-only, plafond/jour | `ag_closer_on` (0) ; cap `ag_closer_cap_jour` (20) ; cron horaire |
| **Max (A/B)** | `inc/ag-ab.php` | variante d'objet collante par prospect, promeut la gagnante ; `ag_ab_applique()` dans l'envoi | `ag_ab_on` (1) ; menu « 🧪 Expériences » |
| **Léa / Tunnel** | `inc/ag-funnel.php` | journal segmenté (métier/ville), analyse quotidienne, oriente ; `ag_funnel_log()` écoute `ag_funnel_event` | option `ag_funnel_events` ; menu « 📈 Tunnel & Analyste » |
| **Gardien réputation** | `inc/ag-gardien.php` | 1×/j ajuste `ag_closer_cap_jour` (baisse si opt-out↑, monte si sain ; plancher 5, plafond `ag_gardien_cap_max` 50) | `ag_gardien_on` (1) ; menu « 🛡️ Réputation » |
| **CM / Croissance** | `inc/ag-cm.php` | détecte le blocage et réoriente (ajoute cibles, signale segment muet, pousse canaux alternatifs) | `ag_cm_on` (1), `ag_cm_autofeed` (1) ; menu « 🚀 Croissance » |
| **Alessia / Boîte** | `inc/ag-boite.php` | relève IMAP **lecture seule**, ne traite que les expéditeurs déjà dans `ag_prospects`, échoue bruyamment | `ag_boite_on` (0) ; cron 10 min |
| **Relanceur chaud** | `inc/ag-relance-chaud.php` | rattrape contrat non signé / intéressé muet / devis sans suite (email + SMS, 2 relances) | `ag_rc_on` ; cron `ag_rc_cron` 10h |
| **Coach** | `inc/ag-coach.php` | feuille de route quotidienne poussée Telegram+email | `ag_coach_on` (1) ; cron 8h |
| **Rapport du soir** | `inc/ag-rapport-soir.php` | digest 20h (email brandé + Telegram + SMS) | `ag_rapport_soir_on` (1) ; heure `ag_rapport_heure` (20) |

**Signature de contrat** (`inc/ag-signature.php`) : du contrat à la signature avec preuve (SHA-256, OTP email, horodatage/IP). 2 verrous : `ag_sign_contrat_relu` (0) et `ag_sign_auto_contresigne` (0). Émet `ag_funnel_event 'signed'` + SMS instantané avec lien `/signer?t=<token>`.

**Données CRM centrales (options) :** `ag_prospects` (fichier prospects), `ag_leads`, `ag_auto_searches`, `ag_funnel_events`, `ag_closer_journal`.

---

## 2) VENTE / PAIEMENT / COMMISSIONS
- **PayPal auto (webhooks)** — `inc/ag-paypal.php` : vérifie signature, rapproche la vente, crédite commission. Émet `ag_paypal_payment_verified`. Options `ag_paypal_*`. Menu « 💳 PayPal auto ».
- **Liens de paiement** — `ag-stripe-admin.php` (racine) : écran Réglages → Liens de paiement, options `ag_stripe_*_url`.
- **Licences templates** — `inc/ag-licence-paypal.php` + plugin `ag-licence-manager/` : paiement vérifié → génère la clé + email. Webhook Stripe `/wp-json/ag/v1/stripe-webhook`. Prix `ag_creator_price` (69) / `ag_licence_price_premium/_business`.
- **Ambassadeurs** — `inc/ag-ambassadeurs.php` : inscription + KYC + commissions 10 % (`AG_COMMISSION_RATE`), primes parrainage. Émet `ag_ambassadeur_registered`. Options `ag_ambassadeurs`, `ag_ambassadeur_ventes`, `ag_parrain_primes`, `ag_override_rate`.
- **Zones** — `inc/ag-zones.php` : 1 département = 1 zone, rotation 50/50 des leads, retrait auto après 7 j d'inactivité (`ag_amb_inactivity_cron`).
- **Recrutement international** — `inc/ag-recrut-intl.php` : 10 pays, messages prêts, liens parrain UTM.

---

## 3) ACQUISITION / SEO / MARKETING
- **SEO meta** — `inc/ag-seo-meta.php` : titres/descriptions par page, schémas Offer/LocalBusiness, vérifs (`ag_gsc_verification`, `ag_bing_verification`…).
- **SEO local / Google** — `inc/ag-seo-localbiz.php` : NAP `ag_nap_*`, JSON-LD ProfessionalService, aggregateRating **seulement si avis Google réels** (`ag_geo_place_id`, `ag_places_key`), checklist GBP/Ads. Menu « 📍 SEO local / Google ».
- **Blog auto / SEO pages** — `inc/ag-seo-autopub.php` (cron `ag_autopub_cron` hebdo), `inc/ag-seo-pages.php`, `inc/ag-seo-blog.php`.
- **Avis** — `inc/ag-avis.php` (shortcodes `[ag_avis_qr]`, `[ag_avis_bouton]`), `inc/ag-google-reviews.php`.
- **Réseaux / feeds** — `inc/ag-social.php` (menu « 📣 Marketing »), `inc/ag-merchant-feed.php`, `inc/ag-meta-feed.php`, `inc/ag-meta-pixel.php`.
- **PWA / app** — `inc/ag-pwa.php` (shortcodes `[ag_install_app]`, `[telecharger_app]`), `inc/ag-app-promo.php`.

### Lead magnets (cadeaux d'acquisition)
- **Audit SEO gratuit** — `inc/ag-audit-seo.php` (anti-SSRF), template `/audit-seo`.
- **Tirage mensuel** — `inc/ag-tirage-mensuel.php` (cron `ag_tirage_cron` mensuel), template `/tirage-au-sort`.
- **Devis instantané IA** — `inc/ag-devis-instant.php` (`[ag_devis_instant]`). **Refais mon site IA** — `inc/ag-refais-mon-site.php` (`[ag_refais_mon_site]`).
- **Kit Print** — `inc/ag-kit-print.php` : cartes/flyers/stickers/affiches + QR parrainage.
- **Demo board** — `inc/ag-demo-board.php` : social proof (`ag_demo_leaderboard_on`, couper quand assez de vrais inscrits).

---

## 4) SÉCURITÉ / OUTILS SITE
- **Gardien de nuit** — `inc/ag-nuit.php` : audit+réparation nocturne (rewrite rules, pages système, liens cassés hors tel:/mailto:, auto-alt des images), badge `[ag_nuit_badge]`, cron `ag_nuit_cron`.
- **Testeur / scanner** — `inc/ag-tester.php` (`[ag_tester]`, menu « 🛡️ Sécurité »), `inc/ag-pentest-bridge.php` (REST `/pentest-next`, `/pentest-report`), `inc/ag-bugbounty.php`.
- **Durcissement** — `inc/ag-hardening.php`, `inc/ag-images-secu.php`.
- **Admin Hub** — `inc/ag-admin-hub.php` : menu racine `ag-hub`, chiffres clés + actions.

---

## 5) POINTS DE BRANCHEMENT (« ce que je peux relier »)

### Hooks custom (émis → écoutés)
| Hook | Émis par | Écouté par |
|---|---|---|
| `ag_funnel_event` (`sent`/`optout`/`signed`) | closer.php, signature.php | funnel.php `ag_funnel_log()` |
| `ag_prospect_added`, `ag_prospect_status_changed` | prospection.php | CRM / funnel |
| `ag_paypal_payment_verified` | paypal.php, licence-stripe, vente manuelle | licence-paypal, crédit commission |
| `ag_ambassadeur_registered` | ambassadeurs.php | espaces.php → `ag_create_member` |
| `ag_client_brief_submitted`, `ag_sur_mesure_saved` | functions.php | espaces.php |
| `ag_closer_file` (filtre) | closer.php | scoreur.php `ag_scoreur_trier` |

→ **Pour greffer un comportement commercial**, écouter `ag_funnel_event` (mesure) ou `ag_paypal_payment_verified` (vente), ou filtrer `ag_closer_file` (ordre d'envoi).

### Routes REST (`ag/v1`)
`/run`, `/worker` (pilote) · `/inbound` (SMS/appel entrant) · `/google-lead` · `/reponse` · `/voice` · `/vapid` `/push-subscribe` `/push-unsubscribe` · `/judilibre*` · `/pentest-next` `/pentest-report`. Plugin licences : `/stripe-webhook`, `/licence/*`, `/update-check`, `/download/{slug}`.

### AJAX (`wp_ajax_ag_*`) — gros pourvoyeurs
`inc/ag-prospection.php` (ag_lead, ag_prospect_add/_touch/_reply/_outcome/_relance/_delete_bulk/_sms_bulk…), concierge, refais-mon-site, devis-instant, site-creator, visiteurs, ma-prospection, voice-webhook, agences, composants, candidatures, appels-offres, missions, recrut-intl, tester.

### Crons (hook → fréquence)
`ag_prospect_cron` (daily, aussi via /run) · `ag_relance_cron` (daily 9h30) · `ag_closer_cron` (hourly) · `ag_boite_cron` (10 min) · `ag_enrich_cron` (3h) · `ag_rc_cron` (10h) · `ag_coach_daily` (8h) · `ag_nuit_cron` (daily) · `ag_social_daily` · `ag_autopub_cron` (hebdo) · `ag_cand_daily_cron` · `ag_amb_inactivity_cron` (8h) · `ag_reseau_cron` · `ag_boamp_daily` (8h15) · `ag_tirage_cron` (mensuel) · `ag_client_daily` (9h) · `ag_amb_daily` (8h30) · `ag_auto_wpcron` (hourly, filet).

---

## 6) ÉCRANS D'ADMIN (où trouver quoi)
- Racines : `ag-hub` (🏠 hub central), `ag-prospects` (Prospection), `ag-ambassadeurs`, `ag-espace-audit` (🛡️ Sécurité), `ag-composants` (🧩), `ag-social` (📣 Marketing), `ag-push` (🔔), `ag-nuit` (🌙).
- Sous **Prospection** : 🚀 Croissance, 🛡️ Réputation, 📈 Tunnel & Analyste, 🧪 Expériences, 🤖 Pilote automatique, 📊 Rapport du soir, 🗺️ Zones, Missions/Plateformes, Appels d'offres/Candidatures, Voice, 📲 Passerelle SMS, Visiteurs, Demandes clients.
- Sous **Ambassadeurs** : Recrutement intl, Recrut-robot, Candidatures, Kit Print, Tirage, Programme, Carte des zones.
- Sous **Hub** : Devis sur-mesure, 💳 PayPal auto, 🔑 Licences PayPal.
- Sous **Réglages** : Liens de paiement, ✉️ Envoi des e-mails (SMTP + signature), Jeunes avocats, Réseaux avocats.

---

## 7) TEMPLATES DE PAGE (72) — repères
Accueil (+ épurée / cinématique / audit-securite), experience (immersif 3D), apropos/fondateur/pourquoi-alliance, services (+6 déclinaisons), sites-express, studio, sur-mesure, templates, composants, realisations (+gwen/elagage), **6 fiches métier** wordpress-avocat/restaurant/artisan/coach/barber/association (+domicile), connexion/espace-client(+app)/espace-ambassadeur/guide-ambassadeur/classement/mot-de-passe, ambassadeurs, programme-racines, contrat-client/ambassadeur, audit-seo, tester, devis-instant, refais-mon-site, rapport, consultation, systeme-prospection, prospection-mobile, maintenance, resilience-ransomware, jeune-avocat/guide-avocat, tirage-au-sort, questions-flash, journal-ia, atelier, contact, mentions-legales/confidentialite/cookies/retours/livraison. (bureaux + recruteur = DÉSACTIVÉS/redirigés.)

---

## 8) INTERRUPTEURS & RÉGLAGES — mémo rapide
- **Agents ON par défaut** : cm, scoreur, gardien, ab, coach, rapport_soir, demo_leaderboard.
- **Agents OFF par défaut** (sécurité/délivrabilité) : `ag_closer_on`, `ag_enrich_on`, `ag_boite_on`, `ag_sign_contrat_relu`, `ag_sign_auto_contresigne` → activés par l'auto-config « tout ON » (`ag_auto_boot`) sauf les verrous de signature (volontairement manuels).
- **Clés externes** : `ag_places_key` (chasse), `ag_geo_place_id` (étoiles Google), `ag_google_client_id` (Sign in with Google), `ag_paypal_*`, `ag_smtp_*`, `ag_tg_*`, `ag_sms_free_*`.
- **Garde-fous jamais franchis** : aucun scan actif, avocats email/courrier only, Bloctel + opt-out, jamais inventer une stat/preuve/fonctionnalité.

---

### Comment j'utilise ce fichier
1. Question « est-ce que le site sait faire X ? » → je cherche ici d'abord (§0 helpers, §1 agents, §5 branchements).
2. « Relier A à B » → §5 (hook/REST/AJAX/cron à utiliser).
3. « Où se règle Y ? » → §6 (écran admin) + §8 (option).
4. Après toute nouvelle brique → **je mets ce fichier à jour** (règle de répercussion).
