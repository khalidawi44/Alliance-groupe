# ÉTAT DES OUTILS & CONNEXIONS — Alliance Groupe

> **But :** cartographie de CE QUI EXISTE ET EST DÉJÀ BRANCHÉ, pour ne pas redemander à Fabrice ce qui est fait.
> À RELIRE avant toute idée ou tout blocage. À RE-SONDER (voir §Méthode) quand un doute subsiste.
> Je ne lis pas la base WordPress d'ici : l'état se vérifie en **sondant le site en ligne** (curl) + en lisant le code.
> Dernière sonde : **23/10/2026** (site `alliancegroupe-inc.com`).

## ✅ DÉJÀ ACTIF — vérifié en ligne le 23/10 (ne pas redemander)
- **Google avis réels** affichés sur l'accueil : `aggregateRating` note **4**, **4 avis**. Clé Places opérante.
- **Fiche Google** faite ; **CID** `8772449461750899688` câblé (lien exact fiche/avis/`sameAs`). Place ID réel par défaut.
- **GA4** (`G-RSQ6Y8DHK4`) + **Google Ads/Consent Mode RGPD** (`consent,default`) actifs.
- **Meta Pixel** actif (`connect.facebook.net`, `fbq()` ×5).
- **AdSense** actif : `ads.txt` = `google.com, pub-4272988112057548, DIRECT, f08c47fec0942fa0`.
- **Schema** sur l'accueil : `Organization` + `LocalBusiness`/`ProfessionalService`.
- **Flux produits** : Merchant (`/?ag_merchant_feed=1`, 6 templates) + **Meta catalog** (`/?ag_meta_feed=1`, actif).
- **SEO technique** : `/sitemap.xml` 200, `/robots.txt` 200.
- **Pilote auto** : `/wp-json/ag/v1/run` répond **403** (jeton exigé) → route vivante, cron-job.org /15 min.
- **NAP** présent (44200, `contact@alliancegroupe-inc.com`).

## État par outil (option/constante · rôle · comment vérifier · constaté 23/10)

### Google
| Outil | Clé / option | Vérifier | État 23/10 |
|---|---|---|---|
| Places (chasse + avis) | `ag_places_key` | avis affichés sur accueil = clé OK | ✅ en place |
| Avis / note | `ag_geo_google_data()` (API Place Details) | accueil : `aggregateRating` | ✅ note 4 · 4 avis |
| Place ID | `ag_geo_place_id` (défaut `ChIJ4zk2LCCNZqgRHQWZK660bIw`) | code | ✅ par défaut |
| Fiche (CID) | `ag_nap_maps` (défaut `?cid=8772449461750899688`) | 📍 SEO local → bouton fiche | ✅ câblé 23/10 |
| Lien GBP (gestion) | `ag_nap_gbp` | 📍 SEO local | ⬜ optionnel (maps suffit) |
| Sign in with Google | `ag_google_client_id` (défaut intégré) | page /connexion | ✅ par défaut |
| Google Lead (form ads) | `ag_google_lead_key` + `/wp-json/ag/v1/google-lead` (POST) | écran dédié | ⚙️ selon clé |

### Analytics & publicité (déjà posés dans le `<head>`)
- **GA4** `G-RSQ6Y8DHK4`, **Google Ads** + **Consent Mode**, **Meta Pixel**, **AdSense** `ca-pub-4272988112057548` (+ `ads.txt`). Vérifier : `curl accueil | grep gtag/fbq/adsbygoogle`. État : ✅ tous actifs.

### Flux produits (Google Shopping / Meta)
- **Merchant** : `/google-merchant-feed.xml` (= `?ag_merchant_feed=1`), 6 templates numériques, source unique `ag_shop_products()`. Pages `/template-premium-<slug>` + schema Product + bloc Transparence. État : ✅ 6 items.
- **Meta catalog** : `/meta-catalog-feed.xml` (= `?ag_meta_feed=1`). État : ✅ actif (9,6 ko).
- ⚠️ Refus Merchant « vente de services » + « déclarations trompeuses » → corrigés 23/10 (langue « fichier à télécharger » + identité vendeur + flux↔page unifiés). Reste : **demander un examen** dans Merchant Center (Fabrice).

### Email (envoi + marque)
- **SMTP** : `ag_smtp_on/host/port/user/pass/from` (ou const `AG_SMTP_PASS`). Écran Réglages → ✉️ Envoi des e-mails. Délivrabilité 9,5/10 via `contact@`. État : ✅ (si « authentifier » coché).
- **Signature manuscrite** : `assets/images/signature-fabrice.png` + options `ag_email_signature_img/_name/_role`. État : ✅ câblée par défaut.

### Notifications
| Canal | Options | État |
|---|---|---|
| Telegram (interne + clients) | `ag_tg_token/chat/chan/chan_link/group_link` | ✅ configuré |
| SMS vers MA ligne (alertes) | `ag_sms_free_user/_key` (API Free) | ✅ actif |
| WhatsApp perso (CallMeBot) | `ag_wa_phone/_apikey` | ⬜ optionnel |
| Push navigateur | `ag_vapid_pub/_priv`, `ag_push_subs` | ⚙️ selon clés VAPID |

### SMS vers des tiers (passerelle Android)
- `ag_sms_send()/_bulk()`, modes httpSMS / sms-gate / webhook, inbound `/wp-json/ag/v1/inbound` (POST). Code ✅ **déployé** — route vérifiée **live le 23/10 : POST sans jeton = 401** (plomberie OK, jeton exigé). **EN PAUSE** : attend une **SIM physique** dédiée + petit Android. (HANDOFF rappel 9.)
- **« Prêt » = `ag_sms_gateway_ready()`** (mode + clé API + numéro émetteur). Vérifier dans **Prospection → 📲 Passerelle SMS**. État 23/10 : route ✅ vivante ; identifiants = très probablement **pas encore** (bloqué sur la SIM).

### Robot vocal IA
- `inc/ag-voice-webhook.php` : **code prêt**, route vérifiée **live le 23/10 : POST /voice sans jeton = 401** (plomberie OK). Appel sortant `ag_voice_call` + webhook résultat `POST /wp-json/ag/v1/voice?token=<ag_voice_token>` (provider-agnostique Retell/Vapi/Bland/Vocode). Écran admin + test (`ag_voice_test`) + journal.
- **« Prêt » = `ag_voice_ready()` = `ag_voice_api_key` ET `ag_voice_from` renseignés.** Vérifier dans l'**écran Voice** (Prospection → Voice) : s'il indique « prêt » et que le bouton **Test** passe, c'est actif. État 23/10 : route ✅ vivante ; identifiants = **à confirmer dans l'écran** (non lisible à distance).

### Paiement & licences
- **PayPal auto** (webhooks) : `ag_paypal_*`, émet `ag_paypal_payment_verified`. Écran 💳 PayPal auto.
- **Liens de paiement** : `ag_stripe_*_url` (packs 490/890/1490), `ag_stripe_business_url` (templates 69 €), `ag_refais_boost_url`, `ag_access_url` (offre accessibilité). Réglages → Liens de paiement.
- **Licences** : plugin `ag-licence-manager`, webhook Stripe `/wp-json/ag/v1/stripe-webhook` (POST), clés `AGPRM/AGBUS`, const `AG_LICENCE_HMAC_KEY`.

### Pilote auto & prospection
- **Pilote** : `ag_auto_token` → `/wp-json/ag/v1/run?token=…` (cron-job.org /15 min) + filet WP-Cron. État : ✅ 403 sans jeton = vivant.
- **Chasse/CRM** : `ag_prospects`, `ag_leads`, `ag_auto_searches`, `ag_cm_bank()`. Agents : enrich, Hugo (closer), Max (A/B), Scoreur, Gardien, CM, Alessia (IMAP `ag_boite_*`), relanceur, coach. Tunnel : `ag_funnel_events` (écrans 📈 Tunnel & Analyste, 🎯 Signaux du filon).

## Méthode de sonde (à relancer quand un doute subsiste)
```bash
# Endpoints / flux (codes HTTP)
for u in wp-json/ag/v1/run google-merchant-feed.xml meta-catalog-feed.xml sitemap.xml robots.txt ads.txt; do
  curl -sS -o /dev/null -w "%{http_code} /$u\n" "https://alliancegroupe-inc.com/$u"; done
# Marqueurs posés dans le <head> (analytics, pixels, schema)
curl -sS "https://alliancegroupe-inc.com/" | grep -oiE "G-RSQ6Y8DHK4|gtag\(|fbq\(|adsbygoogle|consent','default|LocalBusiness|aggregateRating|ratingValue"
# Flux Merchant (les 6 produits + descriptions)
curl -sS "https://alliancegroupe-inc.com/?ag_merchant_feed=1" | sed -n '/<item>/,/<\/item>/p' | head -14
```
Lecture : **403** sur `/run` = route vivante (jeton) ; **404** sur un endpoint POST-only via GET = normal (vérifier via l'écran admin) ; un flux renvoie sa taille en octets s'il est actif.

## Règle
Avant de dire « il faudrait brancher X », **je sonde ici d'abord**. Si l'état n'est pas listé, je le vérifie (méthode ci-dessus) puis je **mets ce fichier à jour** avec la date.
