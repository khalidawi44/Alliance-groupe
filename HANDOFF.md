# HANDOFF — Alliance Groupe (site `alliancegroupe-inc.com`)

> Document de reprise pour toute nouvelle session Claude (PC ou mobile via GitHub MCP).
> **🧠 CONSIGNE FABRICE (permanente) :** gérer les DÉTAILS UX logiques PAR TOI-MÊME sans qu'il ait à les demander (ex : bouton retour, cohérence, états d'erreur). Anticiper.
> **🧠 RÈGLE FABRICE (permanente) :** AVANT d'ajouter quoi que ce soit, TOUJOURS vérifier si ça existe déjà (grep/inventaire). Ne jamais dupliquer un module existant.
> **✅ FAIT (14/08) — MARKETPLACE COMPOSANTS (Phase 1) DÉPLOYÉE SUR `main` :** nouveau `inc/ag-composants-market.php` (chargé functions.php 1c4a-octies-bis). Le créateur choisit **Gratuit** ou **Payant** dans le formulaire `/composants` (+ palier de prix **1,99 / 2,99 / 4,99 / 9,99 €**, prix abordables vs Envato/Gumroad) et saisit son **email PayPal d'encaissement** (user meta `ag_compo_paypal_email`) — validation serveur : impossible de vendre sans moyen d'être payé. **Commission plateforme = 8 %** (`AG_COMPO_COMMISSION`, frais Stripe/PayPal à la charge du vendeur). Grille : **badge prix + bouton « Acheter »** (au lieu du téléchargement) tant que non acheté ; **auteur/admin/acheteur = accès direct**. **Verrou** sur `?ag_composant_zip=` (priorité 9, HTTP 402 si payant non débloqué). Admin **Réglages → 🧩 Marketplace** = brancher Stripe Connect + PayPal Commerce + activer. Modération 🧩 Composants affiche mode/prix/encaissement vendeur. **⚠️ PHASE 2 (paiement partagé RÉEL) = RESTE CÔTÉ FABRICE :** activer **Stripe Connect** (dashboard.stripe.com → Connect) et/ou **PayPal Commerce Platform**, coller les clés → tant que non configuré, « Acheter » affiche « paiement bientôt actif » (rien cassé). Puis brancher le checkout split + webhook (appelle `ag_compo_grant_access()`). `php -l` + `node --check` OK, poussé sur `main`. **RESTE = SYNC GitHub.**
> **✅ FAIT (05/08 soir) — OUTILS IA EN LIGNE ET TESTÉS :** clé Claude collée via **Réglages → « 🤖 IA (clé Claude) »** (page dédiée `add_options_page`, écrit `ag_ai_key` ; l'ancienne case Prospection→Appels d'offres tombait en 404, et la page « Connecteurs → Anthropic » = un AUTRE plugin, non lue par le thème). Devis IA testé en direct = OK (Pack Pro 890-1190€). Concierge « Léa », /devis-instant, /refais-mon-site, résumé gardien de nuit = ACTIFS (modèle Haiku 4.5, ~centimes/usage). **PERF déployée sur main** : vidéo hero différée + poster WebP 59Ko, CLS 0 (régression defer JS/AdSense annulée), 3 CSS d'enrichissement non-bloquantes, gtag différé. **SEO** : titre « Agence web, sécurité & IA à Nantes » via pont Yoast (Yoast est actif = il écrase le thème, on réinjecte via filtres wpseo_*) + hero « …propulsé par l'IA ». LCP labo ~4-10s (test bridé, variance ; vrais visiteurs bien + rapides ; champ « In the Field » vide = pas assez de trafic).
> **🧠 RÈGLE FABRICE (permanente) — LE TRAVAIL DOIT ÊTRE AU BON ENDROIT :** le site déploie **UNIQUEMENT depuis `main`** (Apparence → SYNC GitHub lit `main`). Un commit resté sur une branche = **INVISIBLE en ligne**, donc INUTILE. Règle : quand une modif est destinée au site, elle DOIT finir sur `main` (`git merge --ff-only` + push) — sinon prévenir EXPLICITEMENT « ce n'est PAS encore en ligne, il faut merger dans main ». Ne jamais laisser Fabrice tester/SYNC en croyant que c'est déployé alors que c'est resté sur la branche. « Fait » = déployable sur `main`, pas seulement écrit.
> **✨ FAIT (05/08) — 5 IDÉES NOVATRICES « démarquage IA » (branche `claude/network-access-params-mwh0me`, PAS ENCORE SYNC) :** noyau IA partagé `inc/ag-ia.php` (réutilise la clé existante **`ag_ai_key`** de Réglages → Appels d'offres — AUCUN nouveau champ). **① Gardien de nuit** `inc/ag-nuit.php` : cron quotidien 3h (`ag_nuit_cron`) qui audite le site, auto-répare le sûr (rewrite rules, transients expirés, pages système recréées), résumé IA + notif `ag_push`/email, menu admin **🌙 Gardien nuit** + badge public `[ag_nuit_badge]` (marche SANS clé). **② `/refais-mon-site`** (`inc/ag-refais-mon-site.php`) : le visiteur colle son URL → l'IA lit le site et génère une maquette d'accueil modernisée (iframe srcdoc) → capture lead CRM. **③ `/fait-par-lia`** (`inc/ag-journal-ia.php`) : journal public « Fait par l'IA » généré depuis git (`scripts/gen-journal.sh` → `assets/data/journal-ia.json`, 73 entrées) — SANS clé. **④ Concierge IA** `inc/ag-concierge.php` : assistant flottant « Léa » sur tout le site (tool-use `capturer_lead` → CRM), option `ag_concierge_on`, caché tant que pas de clé. **⑤ `/devis-instant`** (`inc/ag-devis-instant.php`) : devis structuré (voix Web Speech ou texte) → pack + fourchette + lead. Tous : anti-abus par IP, dégradent proprement sans clé, `php -l` + `node --check` OK, chargés dans functions.php (1c4a-nonies→quaterdecies). **RESTE CÔTÉ FABRICE : (1) coller la clé API Claude `sk-ant-…` dans Réglages → Appels d'offres (active ②④⑤ + résumé nuit) ; (2) SYNC GitHub ; (3) ajouter les pages au menu si voulu ; (4) poser `[ag_nuit_badge]` dans le footer.**
> **✅ FAIT (22/07) — PAIEMENT + DÉBLOCAGE AUTO DU RAPPORT CLIENT** (`inc/ag-prospection.php`) : le rapport teaser (`?ag_rapport=1`) a un **formulaire checkout email** → `admin-post ag_rapport_checkout` → commande en attente (option **`ag_audit_pending`**) → redirige vers le lien PayPal (`ag_tester_opt('pay_url')`). Au **`ag_paypal_payment_verified`** (montant == `ag_tester_opt('price')`, 49 € — NE PAS mettre 99/149 = tiers licence, sinon conflit ; priorité 15) : match par email (sinon 1 seule commande en attente), **crée le compte `ag_client`** (`ag_create_member` → email « définis ton mot de passe »), **débloque** (option **`ag_audit_unlocked[md5(site)]`** = email+jeton+txn, idempotent sur txn), **email d'accès au rapport complet** (lien `ag_rapport_full_url` avec `&k=jeton`). Rapport complet = toutes les failles + Kali brut, plus de flou. Déblocage aussi si **client connecté** dont l'email == achat (app client), ou **admin** (aperçu). Admin **Sécurité → 🔓 Rapports (paiements)** : commandes en attente + **déblocage manuel** (si email PayPal ≠ email saisi) + liste des débloqués. **RESTE = 1 seul test réel : payer 49 € et vérifier compte créé + rapport débloqué + emails reçus** (mettre `pay_url` = lien PayPal 49 € dans Réglages → Audit).
> **⏸️ EN PAUSE (22/07 — À REDIRE À FABRICE AU RETOUR — CE QU'IL RESTE DE SON CÔTÉ) :**
> 1) **SYNC** (Apparence → SYNC GitHub → Vérifier MAJ → SYNC FICHIERS → purge cache) pour appliquer TOUS les commits du jour.
> 2) **Test réel 49 €** : ouvrir un rapport, payer via PayPal (lien `EMDGXFFJS4NZ8` déjà intégré), vérifier compte `ag_client` créé + rapport débloqué + 2 emails. Filet : Sécurité → 🔓 Rapports (paiements) → « ✅ Payé → débloquer ».
> 3) **Réglages → 📍 SEO local / Google** : compléter **adresse (rue + CP)** + coller le **lien fiche Google Business**. Vrais avis dans le schema = clé Google Places + **Place ID** (à récupérer).
> 4) **Twilio** : dossier réglementaire en validation → surveiller emails → à l'approbation : acheter le +33 + brancher Emma.
> **FAIT le 22/07 (déployé main) :** paiement+déblocage AUTO rapport client · paiement-d'abord partout · compteur points dynamique · app tri boutons mobile/fixe · CRM 🤖 robot vs 📞 appel perso · **FIX bug JS (app entièrement figée)** · vue admin 📩 Demandes clients · module SEO local (schema LocalBusiness + NAP + checklist Google). **Numéro pro = 07 44 82 95 16.**
> Dernière mise à jour : 2026-08-17 — branche de travail : `claude/network-access-params-mwh0me` (tampon auto à chaque commit).
> **📱 3 APPS + APP CLIENT (22/07, sur `main`, À SYNCER) :** app ambassadeur renommée **« Alliance Groupe Ambassadeur »** (`/prospection-mobile`, title+apple-title+appbar). Nouvelle **app CLIENT autonome** `templates/page-espace-client-app.php` (Template 'Espace Client (app)', slug **`/mon-espace-client`**, auto-créée hook flag `ag_prospection_mobile_page=3`). 4 onglets : 🌐 Mon site (user_meta `ag_client_site` via AJAX `ag_client_save_site` + demande modif), 🛡️ Sécurité (lien /?ag_rapport de son site + demande correction), 💬 Support (WhatsApp `ag_wa_pro`+tel+email+message), 🧾 Factures/maintenance 29/59/99 + 🎁 parrainage (share WhatsApp/SMS). Demandes → AJAX `ag_client_request` → option `ag_client_requests` (200) + notif ag_sms/ag_push/agenda. QR : raccourcis réseaux (WhatsApp/Insta/Snap/TikTok). Photos Drive = impossible d'ici. RESTE (proposé) : app client — parrainage tracké, vue admin des demandes clients ; l'app admin = app ambassadeur (onglets Appels/Ambass/Kali).
> **🔳 QR + 🔬 KALI DANS LE RAPPORT (app, 22/07, sur `main`, À SYNCER) :** (1) Générateur QR code sur l'Accueil (api.qrserver.com, défaut = lien vente ; « Ouvrir en grand » 800px → appui long enregistrer) + accès /studio. (2) Scan Kali branché : AJAX `ag_app_kali_save` (admin, url+kali) → option `ag_kali[md5(url)]`. App : volet admin « 🔬 Résultats scan Kali (PC) » dans `agAuditHTML` (flag `AG.admin`), handler délégué `.agk-save`. Rapport `ag_render_client_report` : si `ag_kali` présent pour le site → encart TEASER « scan approfondi, N anomalies » (compte lignes, ne divulgue pas le brut). (3) Photos Google Drive : IMPOSSIBLE d'ici (MCP Drive demande approbation non dispo en session non-interactive) → Fabrice via Google Photos sur son tel.
> **✅ AUDIT NOTE HOMOGÈNE + RETOUR RAPPORT (22/07, sur `main`, À SYNCER) :** note incohérente (47 puis 60) = 3 calculs (léger/avancé/report). Fix : `ag_audit_get($url)` = audit COMPLET unifié (ag_audit_run_deep) + cache transient 1h par URL → même note app/rapport/carte. `ag_app_audit` et `ag_render_client_report` utilisent `ag_audit_get`. App : 1 seul bouton '🛡️ Auditer le site' (fini léger/avancé). Bouton **← Retour** ajouté en haut du rapport normal ET carte (history.back sinon /prospection-mobile) — carte : au-dessus du cadre + mention 'ne pas inclure dans la capture'. Audits app = passifs (aucun mandat requis) ; scan actif Kali = PC only, à injecter plus tard si besoin.
> **🟢 WHATSAPP BUSINESS (22/07, sur `main`, À SYNCER) :** les liens `wa.me` ouvrent le WhatsApp INSTALLÉ → aucun changement code pour cibler Business (auto une fois Business installé). Ajouté option **`ag_wa_pro`** (Réglages → Notifications tél → « 📱 Numéro WhatsApp Business (pro) », ex 33744829516) + bouton **« 💬 Poser une question sur WhatsApp »** sur le rapport client (`ag_render_client_report`, wa.me vers ag_wa_pro). CHECKLIST Fabrice : (1) renseigner ag_wa_pro = 07 en 337… ; (2) si notifs CallMeBot voulues : ag_wa_phone = 07 + re-autoriser CallMeBot depuis Business ; (3) installer WhatsApp Business sur le 07, profil+accueil+absence+réponses rapides+étiquettes ; garder UN seul WhatsApp sur le tel de prospection. Réponses WhatsApp = gérées dans l'appli Business (pas de côté site sans l'API payante).
> **🔀 PROSPECTS : FILTRES + SUPPRESSION + ROBOT ANGLE AUTO (app, 22/07, sur `main`, À SYNCER) :** Mes prospects : chips filtre (grp: todo/done/rappel/hot + kind: site/crea) → cards ont data-grp/data-kind, JS `applyFlt` (combine grp+kind, re-clic=enlève). Suppression : bouton `.ag-del` → AJAX `ag_app_prospect_delete` (owner/admin, retire de `ag_prospects`). Bouton robot `.ag-robot-one` a `data-angle` = securite si website présent (🔒), creation sinon (🌐) → `ag_app_voice_call` avec le bon angle. Remplace le hint 'à contacter avec le robot'.
> **🖼️ RAPPORT MODE CARTE (22/07, sur `main`, À SYNCER) :** `ag_render_client_report` : si `?card=1` → rend une CARTE compacte (1 écran, ~430px) à SCREENSHOTER et envoyer en image/MMS (bouton + → Photo dans Messages) : logo+AUDIT SÉCURITÉ, nom+note/100 colorée, 3 failles + '🔒 +N', risques concrets, bandeau prix. `ag_app_audit` renvoie `report_card`=add_query_arg('card',1,report). App : bouton **🖼️ Image à envoyer (screenshot)** à côté de 👁️ Voir le rapport. Pas de lien cliquable dans l'image (voulu).
> **🖼️ RAPPORT : APERÇU IMAGE DANS LE MESSAGE + PRIX/PAIEMENT (22/07, sur `main`, À SYNCER) :** SMS ne peut PAS attacher d'image via lien (limite OS). Solution = balises **Open Graph** ajoutées à `ag_render_client_report` (og:title 'Audit de X — Note N/100', og:description failles, og:image = capture mShots, twitter:card) → le lien s'affiche en CARTE avec image dans iMessage bleu + WhatsApp (pas en SMS vert Android). App : bouton **🟢 Rapport WhatsApp (avec image)** (wa.me + msg.rapport) ajouté à côté de 📤 Rapport SMS dans `agAuditHTML`. Rapport enrichi : prix (`ag_tester_opt('price')`, 49€) + CTA vers `pay_url` configuré (sinon /audit-securite?site=) + mises en garde concrètes (piratage/CNIL 4%/rançongiciel/déréférencement). DÉCISIONS user : compte client créé APRÈS paiement ; paiement = moyen déjà configuré pour les produits. RESTE (session dédiée, test paiement réel) : auto-déblocage post-paiement (checkout email → pending order → webhook `ag_paypal_payment_verified` → créer compte ag_client + débloquer rapport complet + email accès).
> **🔧 FIX RAPPORT 404 → RENDU AUTONOME (21/07, sur `main`, À SYNCER) :** le lien /rapport (page WP) faisait 404 (page pas créée). Remplacé par un handler `template_redirect` sur `?ag_rapport=1&site=&name=` → `ag_render_client_report()` echo une page HTML AUTONOME brandée (dark or, pas de dépendance thème/page) + `exit`. Capture mShots avec `agShot` auto-retry. `ag_app_audit` `report` = `home_url('/?ag_rapport=1&site=...&name=...')`. page-rapport.php + auto-create /rapport restent (redondants, inoffensifs). NÉCESSITE 1 SYNC pour déployer le handler.
> **🤝 SMS MASSE AMBASSADEURS + SMS CRÉATION AVEC TEMPLATE (app, 21/07, sur `main`, À SYNCER) :** onglet **Ambass.** (admin) : AJAX `ag_app_bulk_sms` (admin, nonce ag_amb_prospect) parse jusqu'à 100 numéros → `ag_sms_send` chacun → journal `ag_bulk_sms_log` (40 max, {ts,msg,total,ok,ko,detail[]}). App : composer (textarea numéros + msg + bouton « Remplir avec mes ambassadeurs » depuis user_meta `ag_amb_phone`) + suivi campagnes (✅/❌ + détail numéros). ⚠️ Envoi RÉEL dépend de la passerelle httpSMS (option `ag_smsgw_mode`) = EN PAUSE (SIM) → sinon message « passerelle pas active ». Pas d'accusé de lecture en SMS (suivi = envoyé/échec, +répondu via inbound quand actif). Helper `ag_demo_link_for($txt)` (métier→/wordpress-<slug>) : SMS refonte/mixte de `ag_app_audit` incluent le lien du template démo du métier. 6 onglets admin (Accueil/Prospecter/Chercher/Appels/Ambass/Audit).
> **📤 RAPPORT CLIENT TEASER + ONGLET AUDIT + FIX CAPTURE (app, 21/07, sur `main`, À SYNCER) :** (1) capture mShots `onload=agShot()` recharge l'img jusqu'à 4× (fix 'Generating Preview'). (2) `ag_app_audit` stocke chaque audit dans option `ag_app_audits` (60 max, {url,name,phone,score,crit,fails,ts}) + renvoie `report` (lien /rapport) + `msg.rapport`. Onglet **Audit** de l'app : champ audit manuel (#ag-audit-run → ag_app_audit) + LISTE des sites audités (bouton `.ag-audit` Voir le rapport). (3) **page `templates/page-rapport.php`** (Template 'Rapport client', slug `/rapport`, auto-créée via hook `after_setup_theme` flag `ag_prospection_mobile_page=2`) : public, `?site=&name=` → `ag_audit_run` → teaser branché (get_header) : capture + note + 2 failles visibles + reste FLOUTÉ '🔒 X à débloquer' + encart mise en garde (piratage/RGPD/SEO) + CTA vers /audit-securite. App : boutons '👁️ Voir le rapport client' + '📤 Envoyer le rapport' (SMS lien) dans `agAuditHTML`. CTA/warning à affiner selon retours Fabrice.
> **🛡️ AUDIT DANS LA RECHERCHE + RAPPORT IMAGE (app, 21/07, sur `main`, À SYNCER) :** `ag_app_audit` renvoie aussi `fails[]` (checks fail/warn). App : fonction JS partagée `agAuditHTML(url,d,num)` = capture du site (WordPress mShots `s.wordpress.com/mshots/v1/<url>?w=520`) + note colorée + liste problèmes + 3 SMS (refonte/securite/mixte) vers le mobile. Utilisée sur les fiches prospects ET sur chaque résultat de recherche ayant un `website` (bouton « 🛡️ Auditer le site »). mShots = 1er chargement lent (génération).
> **🔍 AUDIT SITE + SMS AUTO SUR FICHE (app, 21/07, sur `main`, À SYNCER) :** AJAX `ag_app_audit` (ag-prospection.php, logged-in + nonce ag_amb_prospect) : mode light=`ag_audit_run`, deep=`ag_audit_run_deep` (serveur, JAMAIS Kali) → renvoie score/100 + critical + tech + reco (refonte si <50, securite si failles) + 3 SMS prêts (refonte/securite/mixte, perso nom+note+lien vente). Persiste `audit_score/audit_crit/audit_ts` sur la fiche. App (`page-prospection-mobile.php`) fiche prospect avec website → boutons 🔍 léger / 🔬 avancé + `.audit-res` (note colorée + SMS sms: préremplis vers le mobile). Avis Google sur chaque fiche : ⭐ rating (reviews) + lien maps_uri (ou google search). Onglet 📞 Appels (admin) affiche `ag_voice_log` in-app.
> **📵 DÉTECTION RÉPONDEUR (21/07, sur `main`, À SYNCER) :** `ag_voice_map_outcome` détecte la messagerie vocale (après le bip / laisser un message / répondeur / taper dièse…) AVANT « intéressé » → nouveau statut **`repondeur`** (« 📵 Répondeur (à rappeler) », ajouté à `ag_prospect_statuses` + label alerte). Pas de RDV/alerte pour ces appels. Bonus : détection « refus » (pas intéressé / déjà une agence) placée AVANT « intéressé » (sinon « pas intéressé » matchait « intéress »). Cédric (capture) à recorriger à la main (interessé→répondeur + suppr RDV).
> **📞 JOURNAL DES APPELS + TRANSCRIPT (21/07, sur `main`, À SYNCER) :** `ag_voice_rest` capture le `transcript` complet (clés transcript/call_transcript/full_transcript) et logge CHAQUE appel (option `ag_voice_log`, 300 derniers, 1×/call_id) : ts/phone/name/status/outcome/summary/transcript/rappel. Page admin **🎯 Prospection → 📞 Journal des appels** (`ag_voice_log_render`, submenu `ag-voice-log`) : liste tous les appels (intéressés/refus/sans réponse) + retranscription repliable + bouton vider. Transcript aussi ré-injecté dans la description du RDV agenda. Logge à partir de maintenant (pas les anciens appels).
> **🐛 FIX DOUBLON RDV ROBOT + 📊 DASHBOARD APP (21/07, sur `main`, À SYNCER) :** (1) `ag_voice_rest` créait 2 RDV/appel (événements « ended » + « analyzed ») → corrigé : ignore start/ended, ne garde que `analy*` (ou event vide) + **anti-doublon par `call_id`** (option `ag_voice_seen`, purge 2 j, garde alerte+agenda 1×). (2) `page-prospection-mobile.php` : **tableau de bord** en tête = 4 tuiles (à contacter/contactés/🔥intéressés/✅clients depuis `ag_prospects_for_owner`) + grille 6 accès rapides (Numéro rapide #numero · Mes prospects #prospects · Chercher · Agenda Google · Classement · Espace). Ancres id="numero"/"prospects". RESTE : SYNC + supprimer le RDV Gwen en double à la main.
> **📱 APP v3 — APPEL ROBOT + RECHERCHE INTÉGRÉE, ZÉRO LIEN WORDPRESS (21/07, sur `main`, À SYNCER) :** app 4 onglets (Accueil/Prospecter/Chercher/Audit). **Appel robot depuis l'app** : AJAX `ag_app_voice_call` (ag-voice-webhook.php, logged-in + nonce `ag_amb_prospect` → `ag_voice_call($phone,{angle})`), bouton sur le pavé Numéro rapide (+ select angle site/sécu, visible si `ag_voice_ready()`) et par prospect (`.ag-robot-one`). **Recherche intégrée** : onglet Chercher → AJAX `ag_amb_search` (admin bypass, sinon Chasseur Pro) → résultats + `ag_amb_add` in-app. **Retiré TOUS les liens wp-admin / pages WP** (journal, robot, CRM, espace, classement). Déconnexion = petit lien dans Accueil. Audit ouvre /audit-securite et /audit-seo en nouvel onglet (à intégrer in-app plus tard si demandé). Toast + pull-to-refresh + bouton remonter conservés.
> **📱 APP AUTONOME v2 (21/07, sur `main`, À SYNCER) :** `page-prospection-mobile.php` réécrite en **appli indépendante du thème** (PAS de get_header/get_footer → sort son propre `<!DOCTYPE>` + exit). App shell sombre/or : barre d'onglets fixe en bas (🏠 Accueil / 🎯 Prospecter / 🛡️ Audit / 🧰 Outils), **pull-to-refresh** (touch au top → reload) + **bouton remonter** (#toTop). Accueil=dashboard (stats incl. répondeur). Prospecter=numéro rapide + liste (statut via `ag_amb_prospect_status`, redirect corrigé sur `wp_get_referer()` → reste dans l'app). Audit=input URL + liens /audit-securite et /audit-seo (+ générateur admin). Outils=espace/classement + liens admin (journal appels, robot, CRM) + déconnexion. PWA metas dans le `<head>` maison. RESTE : SYNC + réinstaller l'icône home iPhone.
> **📱 APP PROSPECTION MOBILE (21/07, sur `main`, À SYNCER) :** page-app `templates/page-prospection-mobile.php` (Template « Prospection Mobile (app) »), **auto-créée** à `/prospection-mobile` (hook `after_setup_theme` + flag `ag_prospection_mobile_page`, dans `inc/ag-prospection.php`). Réservée aux connectés (ambassadeur/admin). Contenu : **pavé Numéro rapide** (coller un num → 📞 tel: / 💬 sms:body / 🟢 wa.me, message éditable), **Mes prospects** (`ag_prospects_for_owner` + `ag_prospect_message`/`ag_wa_number`, boutons appel/SMS/WA + statut via `admin_post ag_amb_prospect_status`, note auto via AJAX `ag_amb_touch`), lien vers Espace Ambassadeur pour la recherche Google. **PWA installable iPhone** : meta `apple-mobile-web-app-capable` + `apple-touch-icon` (=`logo-carte-square.jpg`) injectés via `wp_head`. INSTALL iPhone : Safari → `/prospection-mobile` (connecté) → Partager → « Sur l'écran d'accueil ». RESTE côté Fabrice : SYNC. Amélio possible : intégrer la recherche Google directement dans la page (au lieu du renvoi vers l'espace).
> **📅 PONT AGENDA GOOGLE OK (21/07, sur `main`) — TESTÉ BOUT-EN-BOUT :** les RDV « client intéressé » s'écrivent en DIRECT dans l'agenda `advise.alliance.group` via un **Google Apps Script** (gratuit, fiable, sans e-mail/spam ni réglage Gmail). IFTTT abandonné (Webhooks+Google Agenda = Pro payant). Code : `ag_calendar_notify()` (`inc/ag-espaces.php`) POST le RDV (secret+title+start+end ISO) vers l'URL du script si `ag_calendar_webhook` est renseigné, sinon repli invitation `.ics`. Réglage en **🤖 Robot vocal** : champ « 🗓️ Pont agenda direct » (URL `…/exec`) + 🔑 secret auto (`ag_calendar_secret`, à recopier dans le script variable `SECRET`). Script `doPost` = vérifie secret → `CalendarApp.getCalendarById('advise.alliance.group@gmail.com').createEvent(...)`. Test réel = script a répondu « ok » + RDV créé. **RESTE côté Fabrice :** coller l'URL `/exec` dans le champ (voyant 🟢) + supprimer le RDV de test « ✅ TEST Alliance ». Script à redéployer (Gérer déploiements) si on change le code ; accès = **Tout le monde** (obligatoire).
> **🤖 ROBOT VOCAL IA LIVE (21/07, sur `main`, À SYNCER) :** agent **Emma** sur **Retell** (Claude 4.5 Haïku + voix Emma/Cartesia FR + numéro Retell `+14632982363`). Script création+sécurité via variable `{{angle}}` (creation si pas de site, securite si site) + `{{entreprise}}`/`{{ville}}` — sécurité anti-blocage si vides. **Webhook CRM** `inc/ag-voice-webhook.php` : endpoint `/wp-json/ag/v1/voice?token=…` (option `ag_voice_token`), parsing récursif (`ag_voice_deep_find`), mappe outcome→statut (intéressé/ne_pas_contacter+optout/refus/sans_reponse/contacté), alerte + màj prospect (`ag_prospect_set_status_by_phone`). **Lancer depuis WP** : `ag_voice_call($to,$vars)` (Retell API v2 create-phone-call, options `ag_voice_api_key`+`ag_voice_from`), AJAX `ag_prospect_voice_bulk`, bouton **« 📞 Appeler au robot (sélection) »** dans Prospection (combine avec « ☎️ Cocher les fixes »). Testé OK bout-en-bout (le CRM reçoit « prospect INTÉRESSÉ »). RESTE côté Fabrice : SYNC + 🤖 Robot vocal → coller **clé API Retell** + `+14632982363`. ⚠️ Numéro US pour l'instant → passer à un numéro FR démarchage (09 48) OVH/Ringover via SIP pour la vraie campagne. Format E.164 obligatoire côté Retell (WP convertit auto).
> **🧹 INTERFACE ADMIN SIMPLIFIÉE (21/07, sur `main`, À SYNCER) :** 10 menus top-level → **6** (rien supprimé, slugs conservés, re-parent priorité 20). Structure : **🦁 Alliance Groupe** (dashboard + PayPal auto + Licences PayPal) · **🎯 Prospection** (liste création+sécu avec filtre 🆕/🔒 + tri mobiles 📱/☎️ + boutons « cocher mobiles/fixes » · Passerelle SMS · Zones · Analyse redressement) · **🛡️ Sécurité** (Espace Audit + Générateur + Sites scannés + Bug Bounty + pentest + Réglages Audit) · **🤝 Ambassadeurs** (+ Programme) · **📣 Marketing** (ex-Réseaux : Réseaux sociaux + Avis Google + Témoignages + Promo + Blog auto) · **🔔 Notifications** (+ Notifs téléphone). Restent sous Réglages WP : Connexion Google, Judilibre, avocats. **SMS auto complet** : réception→candidature/opt-out STOP/réponse prospect→intéressé, welcome enrichi PayPal+Telegram, motivation quotidienne SMS. Passerelle httpSMS OK (émetteur +33624446399).
> **🎯 PROSPECTION LANCÉE (21/07) :** run réel Google Places Text Search → 117 entreprises Nantes/Loire-Atlantique (avocats/restos/artisans/commerces), qualifiées par présence de site : **19 CHAUDES sans vrai site** (angle création — ArnO Boulangerie 658 avis, P'tit Port, Marguerite, Le Velvet, La Fourmi…) + 98 avec site (angle audit sécu). CSV livré en chat (non commité = données perso RGPD, PAS dans le repo). Agent auto côté Fabrice à activer : **wp-admin → Prospection → 🤖 Agent automatique** → coller clé Places + ajouter recherches (`avocat/restaurant/menuisier/plombier/boulangerie/coiffeur` + ville `Nantes`, ou ville seule = tous secteurs) + fréquence 1×/sem + plafond 500 → ▶ Lancer. Options : `ag_auto_searches`, `ag_auto_freq`, `ag_places_cap`, cron `ag_prospect_cron`. ⚠️ Fabrice doit RESTREINDRE sa clé Google (visible sur screenshot).
> **📲 PASSERELLE SMS ACTIVE (21/07) :** Android dédié + SIM physique relié à httpSMS (compte `advise.alliance.group@gmail.com`, clé `uk_…` dans `wp-admin → Prospection → 📲 Passerelle SMS`, émetteur `+33624446399`) → **SMS de test envoyé OK**. RESTE : activer la Réception (mot-clé `AMBASSADEUR` + Macrodroid) puis envoyer aux prospects chauds. ⚠️ La clé `uk_…` a été montrée en capture → faire « Rotate API Key » plus tard.
> **🥋 ASSISTANT DE DÉFI / BUG BOUNTY (20/07, sur `main` après SYNC) :** 3e onglet dans Espace Audit → 🧰 Générateur d'audit → **« 🥋 Défi / Bug Bounty »** (`assets/tools/assistant-defi.html`). Saisir cible + type(s) de faille (RCE/SQLi/LFI/XSS/IDOR/SSRF/upload/XXE/SSTI/Auth + recon/nmap web&infra) → sort la ROUTE pas-à-pas (commandes prêtes Kali + résultat attendu) → puis génère le RAPPORT YWH (titre + 5 sections + CVSS auto par type). Hors ligne. Sous scope/mandat only.
> **📝 FORMAT RAPPORT VULN (règle Fabrice 20/07) — voir `pentest-bridge/MODELE-RAPPORT-YWH.md` :** TOUS les rapports (Dojo, bug bounty, audits) suivent ce modèle par défaut, sauf si Fabrice dit de changer. Titre = `<type> dans <composant> — <cause>` ; Description en 5 sections Markdown YWH (Description/Exploitation/PoC/Risk/Remediation) ; champs form (type de bug, endpoint, partie vulnérable, charge utile, CVE=N/A si labo, Impact, Prends mon IP) ; CVSS vector (RCE non-auth = AV:N/AC:L/PR:N/UI:N/S:U/C:H/I:H/A:H = 9.8). Ne pas surévaluer. Validé sur Dojo #30 RCE (flag FLAG{Acc3ss_Gr4nt3d}).
> **🧰 GÉNÉRATEUR D'AUDIT EN WP-ADMIN (24/06, sur `main`, À SYNCER) :** nouvelle page **Espace Audit → « 🧰 Générateur d'audit »** (`ag_audit_gen_render` dans `inc/ag-tester.php`) = 2 onglets en iframe : **Générateur** (saisir le site → commandes pré-remplies + analyse des sorties → note honnête + rapport + synthèse « 1 bis ») et **Assistant guidé**. Fichiers dans `alliance-groupe-theme/assets/tools/` (audit-generateur.html, assistant-pentest.html). Le presse-papier marche ici (https) alors qu'il est bloqué en `file://`. Versions offline aussi dans `pentest-bridge/`. ⚠️ La note de cohérence (léger≥approfondi≥Kali) est déjà sur main (`14af6af`). RESTE : **SYNC** pour voir la page.
> **🆕 SESSION 23/06 (tout sur `main`, À SYNCER côté site) :** (1) **Vidéos pub** — `tools/ads-videos/` (Picsart image2video : 5 clips animés Naples/Nantes ; **crédits Picsart = 0**, recharger pour en refaire) → `assets/videos/ads/alliance-promo-anim-{horizontale,carre,verticale}.mp4` + `alliance-promo-web.mp4` (header). Musique = `napoli.m4a` fourni. Posts GBP noir&or : `assets/images/gbp/post-*.png` (générateur `tools/gbp-posts/make_posts.py`, carte de base `carte-base.png`). Pack réseaux : `tools/ads-videos/PUBLICATION.md`. (2) **Accueil** : vidéo header **plein écran épinglée** (sticky) + sections remontent (`templates/page-accueil.php`). (3) **Google Ads** : conversion sur **vrais leads** (form contact + clic tel/mailto, event GA4 `generate_lead`) au lieu de page-vue ; label option `ag_ads_conversion_label` (AW-18188842206/ZCMWCPfQvcEcEN7pjuFD). (4) **GA4 doublon réglé** : thème n'émet plus `G-RSQ6Y8DHK4` (option `ag_ga4_id` vide par défaut) → seul GA4 = `G-49BX81NSZ0` (GTM externe). (5) **Menu** : « Le Voyage » retiré (nav + footer ; page garde son URL). (6) **⭐ Avis Google** (`inc/ag-avis.php`, menu top-level `ag-avis`) **unifié** : champ **Place ID** + lien auto `writereview?placeid=` + avis manuels JSON + vider cache. Affichage des avis = `inc/ag-geo.php` section « Ils nous recommandent » (page `meilleure-agence-web-nantes`), API Places (option `ag_places_key`, **Places API à activer côté Google Cloud**), **Place ID EXACT obligatoire** (sinon homonyme « Alliance Group » constructeur). **Place ID fiche Fabrizio = `ChIJ4zk2LCCNZqgRHQWZK660blw`** (CID 10118661122943419677). ⚠️ RESTE À FAIRE CÔTÉ FABRICE : SYNC + ⭐ Avis Google → coller Place ID → Enregistrer → Vider cache ; vérifier rendu vidéo header après SYNC.
> **💰 STRATÉGIE VENTE AVOCAT (18/06) → voir `STRATEGIE-AVOCAT.md`** : plan 30/60/90 + **séquence email 7 messages** (free→premium) + pièges déonto/.org. Entonnoir technique LIVE : thème .org → lien « 🎁 guide gratuit » → page `/guide-avocat` (site mère, `inc/ag-lead-avocat.php` + `templates/page-guide-avocat.php`) → lead CRM + email du guide. Thème .org bumpé **1.1.18** (zip `wporg-builds/ag-starter-avocat-1.1.18.zip` à ré-uploader sur wordpress.org/themes/upload).
> **📣 EN COURS (10/06) — PUBLICATION AUTO RÉSEAUX (Facebook + Instagram)** : module **`inc/ag-social.php`** créé (FB Page + IG Pro via API Graph Meta : `ag_social_fb_publish`/`ag_social_ig_publish`/`ag_social_publish` + cron promo quotidienne + auto-post nouvel article + page admin **« 📣 Réseaux »** : jetons, toggles, « Publier maintenant », journal, guide ; chargé dans functions.php). **BLOQUÉ tant que les jetons ne sont pas saisis.** Procédure : Explorateur d'API Graph → permissions `pages_show_list,pages_manage_posts,pages_read_engagement,instagram_basic,instagram_content_publish,business_management` → requête **`me/accounts?fields=name,id,access_token,instagram_business_account`** → récupérer **Page Access Token (EAA…)** + **ID de Page** + **ID IG Business** → coller dans wp-admin → 📣 Réseaux. ⚠️ **SÉCU : Fabrice a collé son App Secret (32 hex) en clair → À RÉINITIALISER** (Paramètres app → Clé secrète). `business_id=1327423202683559` + un `id 100080449355554` (lien profile.php → vérifier que c'est une PAGE) donnés mais insuffisants. Token Explorer court (~1 h) → ensuite échanger en longue durée. Insta doit être **Pro lié à la Page** sinon IG refuse (FB ok).
> **🖥️ ACCUEIL (autre session O6HUR, 10/06, déjà sur main)** : hero split sécurité/création + titre « Création (bleu) de site web & sécurité (rouge) » ; mobile simplifié ; pop-up piratage = **petite carte centrée** (voile flou, clic dehors = ferme) au lieu du plein écran ; burger en double corrigé ; exit-intent tient compte de la barre admin WP.
> **📱 NOUVEAU (12-13/06) — APPS PWA (site installable en application, sans store)** : module **`inc/ag-pwa.php`** (`AG_PWA_VER`, bumper à chaque MAJ du SW). **2 apps distinctes** depuis le même site : **« Alliance Groupe »** (icône noire, manifeste `/ag-app.webmanifest`) + **« Alliance Ambassadeurs »** (icône cadre OR sur navy, `/ag-amb.webmanifest`, ouvre l'espace ambassadeur). Endpoints réécrits : `/ag-sw.js` (service worker, scope `/`, **réseau d'abord** + offline), `/ag-app.webmanifest`, `/ag-amb.webmanifest`. **Icônes** générées du logo dans `assets/images/pwa/` (icon-/amb- 192/512/maskable/apple). **Choix d'app** sur le site public (modale au clic « Installer » → Site vs Ambassadeurs). **Menu DÉDIÉ** dans l'app ambassadeur installée (détectée par `standalone` + marqueur `ag_app=amb` dans le start_url, classe `html.ag-amb-app` posée tôt en `<head>`) : barre d'onglets bas (🏠/🎯 ma-prospection/🏆 classement/🤝 recruter/👤 compte) + en-tête, **menu du site masqué** (`.ag-nav`). **MAJ AUTOMATIQUE** (plus de réinstall) : SW `skipWaiting`+`clients.claim`, reload sur `controllerchange`, `reg.update()` au focus. ⚠️ **GARDER STABLES** `id`/`start_url`/`scope` des manifestes (sinon ré-install forcée). **Bouton install** : shortcode `[ag_install_app]`, bannière flottante dismissible, modale iOS « Ajouter à l'écran d'accueil ». **📢 Promo app** (`inc/ag-app-promo.php`, menu admin) : bandeau d'offres in-app piloté depuis wp-admin (message+bouton+audience all/apps/amb, ré-affichage à chaque promo). **🔔 NOTIFICATIONS PUSH** (`inc/ag-push.php`, Web Push **VAPID** natif PHP, reçues **app fermée**, Android + iOS 16.4+ installé) : clés VAPID auto-générées (options `ag_vapid_pub`/`ag_vapid_priv`), REST `ag/v1/vapid|push-subscribe|push-unsubscribe`, abonnements `ag_push_subs`, chiffrement aes128gcm (testé OK), **menu admin 🔔 Notifications** (titre/message/lien/audience + envoi), bouton 🔔 dans l'app amb + shortcode `[ag_notif]`, handlers `push`/`notificationclick` dans le SW. **À FAIRE CÔTÉ FABRICE** : 1 SYNC + **Réglages→Permaliens→Enregistrer** (active les rewrites) + purge cache ; **désinstaller/réinstaller l'app amb UNE dernière fois** (nouveau manifeste) ; tester install/menu/promo/push. (Autre session avait aussi fait un PWA pour le plugin avocat `ag-avocat-recherche` — séparé.)
> **⚖️ NOUVEAU (12/06) — OUTIL DE RECHERCHE JURIDIQUE (template avocat)** : nouveau **plugin AUTONOME & séparé** (les plugins avocat Free/Premium/Business restent VERROUILLÉS — règle LOCKED.md : toute feature = plugin séparé) → `alliance-groupe-theme/assets/downloads/ag-avocat-recherche/` (+ zip distribuable). Menu wp-admin **⚖️ Recherche juridique** pour l'avocat : (1) **méta-moteur** une requête → liens pré-remplis vers Légifrance / Cour de cassation-Judilibre / Conseil d'État ArianeWeb / Conseil constitutionnel / CEDH HUDOC / CJUE CURIA / EUR-Lex / Doctrine.fr / Dalloz / BOFiP / Pappers-BODACC ; (2) **recherche LIVE de jurisprudence** via l'API **Judilibre** open data (OAuth PISTE, gratuit — même pattern OAuth que le testeur France Travail) : résultats réels (juridiction/chambre/n°/date/thèmes/sommaire/solution + texte intégral) ; (3) **Mes dossiers** (CPT `ag_jr_dossier` : faits/problème de droit/textes/jurisprudence/arguments adverses/parade/stratégie/pièces + vue imprimable PDF) ; (4) **Banque d'arguments** (CPT `ag_jr_argument`) ; (5) **Assistant IA optionnel** (Anthropic Claude). Réglages = clés PISTE + clé IA. **À CONFIGURER côté avocat** : compte piste.gouv.fr → s'abonner à l'API Judilibre → coller Client ID/Secret dans Réglages (sinon le méta-moteur + dossiers marchent déjà sans clé). `php -l` OK, JS `node --check` OK.
> **⏳ À DÉPLOYER (SYNC en attente) — nouveautés web déjà sur main** : exemples d'audits anonymisés (Sécurité = vrais audits via `ag_audit_hist_get`, Création curée) sur accueil + page Tester ; page **Résilience Ransomware** (fond blanc lisible + image) + menu Services + carte accueil ; **1 bis « Coller synthèse Kali »** + Maximum par défaut + fix badge **Expert (Kali)** + fix scoring (approfondi ≤ léger) ; Espace Audit : **saisie contact manuel** + **2 angles sécu+création par fiche** + tri **🏢 Par site** ; module 📣 Réseaux. → **Apparence → SYNC GitHub → SYNC FICHIERS → purge cache**, puis « Réauditer tout ».
> **📸 RAPPEL ACTIF (demande Fabrice 04/06) — CAPTURES TEMPLATES À INTÉGRER** : Fabrice a fait les captures **avocat** + **coach** (et prend les autres au fur et à mesure : restaurant, artisan, barber, association). Dès qu'il les envoie → les déposer dans **`alliance-groupe-theme/assets/images/templates/<slug>.jpg`** (slugs exacts : `avocat`, `restaurant`, `artisan`, `coach`, `barber`, `association`) — c'est l'aperçu réel utilisé par `templates/page-templates.php` (fallback image générique si absent). Idéalement format **16/10**, ~1200px, compressées (Pillow q72). Puis commit + push + SYNC. **LE RAPPELER tant que les 6 ne sont pas toutes en place.**
> **🔒 RÈGLE PERMANENTE (demande Fabrice) — APRÈS CHAQUE SCAN, TOUJOURS LE RAPPELER** : (1) mettre le rapport dans le **coffre VeraCrypt** ; (2) **EFFACER les rapports de Kali** (aucune donnée client en clair ne doit rester dans la VM). Outil dédié : **`pentest-bridge/coffre.sh`** (à installer 1× dans `/root/pentest-local/`) → `sudo ./coffre.sh` zippe le dernier rapport vers le dossier partagé PUIS shred+rm la copie Kali, et rappelle les étapes Windows (Mount VeraCrypt → déplacer le zip dans le coffre → Dismount → vider la corbeille). À redire à Fabrice à la fin de chaque audit.
> **⚙️ PRÉFÉRENCE FABRICE — SCANS KALI TOUJOURS EN PROFIL `max`** (le plus exhaustif) par défaut, sauf demande contraire.
> **📌 À RAPPELER À FABRICE SYSTÉMATIQUEMENT (2 chantiers en attente)** : (1) **sqlmap** — installé mais à utiliser **À LA MAIN** (injection SQL active), sur préprod/cible sous mandat, jamais dans le scan auto ; lui rappeler de l'employer quand un paramètre injectable est repéré. (2) **OFFRE « Test de résilience ransomware » + exploitation contrôlée** — à RÉDIGER (descriptif + déroulé + prix) ET à essayer **sur un de SES PROPRES sites / labo isolé** (jamais sur prod client) ; simulateurs sûrs (Atomic Red Team / Infection Monkey), aucun vrai chiffrement de données. Claude a refusé d'armer le scanner en ransomware/prise de contrôle auto (destructif/illégal) — d'accord avec Fabrice.
> **🔧 À INSTALLER DANS KALI (rappel Fabrice)** : `sudo apt install -y testssl.sh` (sinon le module TLS/SSL affiche « testssl non installé — ignoré » ; le scan tourne quand même mais sans analyse TLS). Outils optionnels qui enrichissent `max` s'ils manquent : feroxbuster, gobuster, katana/hakrawler/gospider, gau/waybackurls, gowitness, theHarvester, sublist3r/subfinder, sslyze, dnsrecon, dig.
> **🖥️ ACCUEIL (autre session, déjà sur main)** : simulation de piratage en EXIT-INTENT après 4s (1×/session) + carte d'attaques planisphère 60×30 plus réaliste. Fusionné, pas de conflit (fichiers accueil ≠ fichiers audit).
> **🆕 NOUVELLE CIBLE = Nantes Métropole Habitat (`nmh.fr`) — MANDAT SIGNÉ** (organisme public/bailleur social → données locataires sensibles, rester STRICTEMENT dans le périmètre écrit). Tests site (Espace Audit) faits : Léger 74 / Approfondi 77 (incohérence corrigée, cf. fix scoring). **À FAIRE PLUS TARD (pas maintenant)** : scan Kali `max` sur le périmètre du mandat → `sudo ./scan.sh nmh.fr max`. AVANT de lancer : confirmer le périmètre exact (domaines/sous-domaines + dates) écrit dans le mandat, et un contact technique NMH à prévenir.
> **🛠️ FIX EN ATTENTE DE DÉPLOIEMENT (02/06)** : correctif scoring Espace Audit (`ag-audit-seo.php` : approfondi ≤ léger, commit 057a9e8) POUSSÉ sur la branche de travail mais PAS encore mergé dans `main` ni SYNC sur le site. Quand Fabrice veut déployer : merge ff-only dans main + push, puis Apparence → SYNC GitHub, puis bouton « 🔄 Réauditer tout » pour recalculer les vieilles fiches.
> **🌙 À REPRENDRE EN PRIORITÉ (pause nuit 01/06) — MOTEUR KALI `ag-pentest.sh` v2.9 (dans `pentest-bridge/`, à re-télécharger dans la VM)** : grosse session "scan exhaustif". Le moteur a été muscle de v2.0→**v2.9** (numéro affiché dans la bannière au lancement + champ "Moteur" de la synthèse). Lanceur court créé : **`pentest-bridge/scan.sh`** (à télécharger 1× dans `/root/pentest-local/`, met `ag-pentest.sh` à jour tout seul depuis GitHub) → ensuite **`sudo ./scan.sh <site> <profil>`** suffit. 4 profils : light/normal/**deep**/**max** (deep&max = mandat signé). **CE QUI A ÉTÉ AJOUTÉ** : synthèse remonte TOUS les modules (01→20) ; nmap deep/max + scripts NSE `vuln` ; garde-fou `run_to` (timeout par module → plus aucun blocage, ex-pb nikto réglé) ; whatweb `--color=never` ; chasse FICHIERS sensibles (~60 noms dont base-clients `clients.csv/.sql/export/factures/rib/iban` + extensions tableurs) ; module **8bis données perso/bancaires** (compte emails/tél/IBAN/cartes, échantillons MASQUÉS dans `19-donnees-perso.txt`, RGPD) ; **collecte max** (en-têtes/robots/sitemap, cartographie URLs katana/hakrawler/gospider, archives gau/waybackurls, AXFR DNS) ; **PREUVES = téléchargement COMPLET des fichiers exposés** dans `preuves/` (en clair → À CHIFFRER VeraCrypt, cap 200 Mo) ; **mode FURTIF anti-WAF** (UA navigateur en rotation + en-têtes réalistes + jitter 2-6s + retries sur 000/403/429/503) ; **module 9g découverte IP d'ORIGINE derrière CDN** (MX + sous-domaines directs → mapping `/etc/hosts` pour scanner le serveur réel SANS le WAF).
> **📂 DOSSIER PARTAGÉ CHANGÉ (02/06 nuit)** : VirtualBox partage repointé de `D:\download-kali` (instable, les lettres bougent quand VeraCrypt monte) vers **`E:\pro\1_alliance_groupe\7_download_kali`** → monté côté Kali en **`/media/sf_7_download_kali`** (l'ancien `/media/sf_download-kali` peut subsister, ignorer). DONC lancer désormais `sudo AG_SHARE=/media/sf_7_download_kali ./coffre.sh`. Le 1er rapport saothai (`saothai-20260601.zip`, sans données sensibles car WAF) a été transféré OK et mis au coffre.
> **🎯 CIBLE DE DEMAIN = saothai.fr** (mandat intrusif signé, client veut un vrai test d'entrée max + savoir si son FICHIER CLIENTS tél/bancaire est téléchargeable). 1er `max` (v2.4) TERMINÉ → **résultat NON concluant** : la cible **bloque le scanner** (WAF/anti-bot type Cloudflare, tous les tests fichiers en code `[000]` = pas de réponse). Pas de fichier exposé trouvé MAIS on n'a pas pu tester vraiment. Le "🔴 fichiers exposés" affiché était un FAUX positif (corrigé en v2.8). **PROCHAINE ACTION DEMAIN, SANS RIEN CASSER** : (1) re-télécharger v2.9 puis `sudo ./scan.sh saothai.fr max` ; (2) lire `20-origine-waf.txt` → si une IP d'origine HORS plages Cloudflare apparaît, `echo '<IP> www.saothai.fr' | sudo tee -a /etc/hosts` puis re-scanner (vise le serveur réel, contourne le WAF), **retirer la ligne /etc/hosts après** ; (3) sortir le dossier `rapports/saothai.fr-*/` (zip → `/media/sf_download-kali/` → Windows → **dans le coffre VeraCrypt**, supprimer la copie hors-coffre) ; (4) me coller `SYNTHESE.md` → je fais la version client FR + chiffrage des corrections. ⚠️ NB veille : la veille système gèle la VM → laisser Windows + Kali réglés "jamais de veille" (xset s off; xset -dpms FAIT). RIEN N'EST CASSÉ : le moteur ne fait QUE de la détection (aucun exploit exécuté).
> **🔖 PLUS ANCIEN (offre sécurité, site)** : grosse session "offre sécurité". FAIT ce soir (tout poussé sur main, SYNC à faire) : (a) **vidéo de fond accueil SUPPRIMÉE** → image sécurité (perf) ; (b) **test gratuit = URL SEULE** + journal "📋 Sites scannés" (URL+IP+score) ; (c) tunnel **"Diagnostic Expert 24h"** (ex-Audit Sécurité Renforcé, renommé partout) : si faille CRITIQUE au test → alerte rouge + bouton → page commande (email+autorisation/mandat+CGV+**paiement avant scan**) → crée job Kali → à la fin /pentest-report **email rapport au CLIENT + ADMIN avec offres chiffrées** (ag_pt_offers_from_summary) ; (d) **synthèse Kali TRADUITE en FR** (ag_pt_summary_fr : parse EN→failles FR par gravité+score) utilisée email/fiche/liste ; (e) **tests gratuits versés dans l'historique Espace Audit** (coordonnées publiques extraites + boutons + image) ; (f) clarté libellés : fiche "X sécurité + Y SEO" (fin confusion 2 vs 8), badge "🔍 Léger (passif)" vs "🔬 Approfondi (Kali)", image "failles de sécurité" ; (g) image rapport = vraie note Kali si scan existe sinon projection ; (h) **approfondi affiche ses 4 contrôles en plus** (énum auteur, sauvegardes, listing, versions plugins) avec statut OK/KO → preuve qu'il creuse plus. **COMPRIS** : léger=approfondi même note sur anymassages = NORMAL (les 4 sondes passent = site propre sur ces points), pas un bug. **PROSPECT EN OR = anymassages.fr** (60/100, pas HTTPS, tél 0277034640, SIRET 18978102189781) → reste à rédiger le message de prospection + (si autorisé) lancer scan Kali dessus. Reste aussi : sortir rapports Kali de la VM (zip, cf note ci-dessous), clé D: VeraCrypt à confirmer.
> **⚡ PERF ACCUEIL (session O6HUR, 01/06)** : page d'accueil lente sur vieux PC → (1) **blur coupés** (étoiles + backdrop-filter compteurs menace) ; (2) **iframe Kaspersky SUPPRIMÉE** → carte d'attaques **100% locale en canvas** (planisphère masque continents 18×36 + nodes + arcs animés, badge LIVE, 0 requête externe, anime seulement si visible, reduced-motion OK) ; (3) **shader WebGL hero** (`mesh-gradient-bg.php`) → **dégradé mesh CSS pur** ; (4) **garde-fou LOW-END** dans `ag-cinema.js` + `ag-immersive.js` (`hardwareConcurrency<=4` / `deviceMemory<=4` / `saveData` → return) ; (5) **Google Fonts allégées** (Fraunces sans axes variables). ⚠️ NB : l'autre session a aussi supprimé la vidéo de fond hero (point a) — cohérent, pas de conflit.
> **🔖 NOTE PRÉCÉDENTE (31/05)** : (1) **SORTIR LES RAPPORTS KALI** — dans la VM Kali (`~/pentest-local/rapports/<site>-<date>/`), PAS sur D:. Zipper côté Kali puis récupérer via Firefox-Kali. ⚠️ Kali a planté une fois → vérifier dossiers au reboot. Notepad refuse les noms avec `:` `/` → nommer `rapport-<client>.txt`. (2) Clé D: VeraCrypt ? à confirmer.
> **🛰️ EN COURS (31/05, session pentest) — PONT AUDIT KALI ↔ wp-admin** : module commité `inc/ag-pentest-bridge.php` (chargé dans `functions.php`) + dossier suivi `pentest-bridge/` (runner `ag-runner.sh` SANS jq + README, **sans secret**). Architecture file d'attente **sortie-seule** (Kali n'a aucun port ouvert) : bouton wp-admin (🔍 Espace Audit → 🛰️ Audits Kali) crée un job → 2 endpoints REST `ag/v1/pentest-next` + `ag/v1/pentest-report` (auth jeton Bearer, option `ag_pentest_secret`) → le runner Kali tire le job, lance `pentest-local/ag-pentest.sh -y`, renvoie `SYNTHESE.md` dans le back-office. Jobs en option `ag_pentest_jobs`. **`pentest-local/` reste gitignored/privé.** nmap laissé ACTIF (choix Fabrice) malgré mutualisé o2switch. **AUTRE SESSION : lis `pentest-bridge/README.md`.**
> **➡️ REPRISE EXACTE (pause 31/05 ~19h25, PC éteint) — INSTALLATION KALI PRESQUE FINIE** : la VM Kali a `~/pentest-local/` avec **`ag-pentest.sh` (121 lignes, OK)** + **`ag-runner.sh` (OK)** + **`runner.conf` (URL+jeton OK, chmod 600)**. Tout y est sauf : on venait de buter sur `zsh: permission denied: ./ag-runner.sh`. **PROCHAINE ACTION dans Kali** : `chmod +x ag-runner.sh ag-pentest.sh` puis `./ag-runner.sh --once` → attendu « File vide (--once) : fin. » = pont OK. Ensuite **test réel** : wp-admin → 🛰️ Audits Kali → Nouvel audit (cible `https://alliancegroupe-inc.com`, client `mon site`, mandat `AUTO-TEST-PROPRIETAIRE`, niveau mutualisé) → relancer `./ag-runner.sh --once` dans Kali → le scan part, « ✅ Rapport renvoyé », synthèse visible dans wp-admin. Note : copier-coller Windows↔Kali cassé (Additions invité KO) ; apt/jq cassé (clé GPG NO_PUBKEY) → runner sans jq ; transfert de fichiers fait via mini-serveur PowerShell Admin sur Windows (`http://10.0.2.2:8000/`) + `curl` côté Kali. Python absent de Windows.
> **✅ VALIDÉ DE BOUT EN BOUT (31/05 ~22h51)** : 1er audit complet réussi sur `alliancegroupe-inc.com` — bouton wp-admin → file → runner Kali → scan (nmap/nikto/nuclei/wpscan) → `SYNTHESE.md` remontée et affichée dans 🛰️ Audits Kali. Résultat : **rien de critique** (site déjà durci 93/100), 3 points mineurs = ETag favicon (CVE-2003-1418), BREACH (deflate, théorique), X-Content-Type-Options absent sur /wp-includes/. **LE PONT FONCTIONNE.** Reste optionnel : (a) brancher pour de vrai le flag `--mutualise` dans `ag-pentest.sh` (le menu niveau wp-admin est encore indicatif → nmap tourne quand même = scan lent ~10 min sur o2switch) ; (b) faire tourner `./ag-runner.sh` en boucle (sans --once) pour traiter la file en continu.
> **✅ 2e AUDIT (31/05, site tiers webmaster-gironde.fr, mandat signé)** : pont confirmé sur un site externe. Runner reçoit maintenant le `level` et passe `-M` à ag-pentest.sh si niveau=mutualise (commit d7036ea) — MAIS prise en compte de `-M` PAS encore ajoutée dans `ag-pentest.sh` côté Kali (modifs données au user, non appliquées). Tor : apt Kali RÉPARÉ (nouvelle clé GPG archive.kali.org + miroir http.kali.org), tor+torsocks installés, torification HTTP OK (`torsocks curl`=45.84.107.47 vs VPN 84.21.169.40) — nmap reste hors Tor, exit nodes bannis = inutilisable en prod, VPN suffit. Bilan webmaster-gironde.fr : **bien sécurisé** (WAF Anubis actif → 406 sur XSS, aucune faille majeure ; seul point = X-Content-Type-Options absent). Leçon : audit honnête = sait dire quand NE PAS vendre (ce prospect = confrère webmaster, mauvaise cible sécurité).
>
> **Reprise rapide** : **MÉNAGE DE FOND fait** (cohérence studio solo sécurité partout) — footer + SEO meta + tagline purgés de « Agence Web & IA / vision internationale / bureaux Marrakech-Naples / +340% leads / commerciaux », CTA ambassadeur retiré du footer, chat équipe fictive désactivé, **navigation unique = menu fullscreen épuré** (ancien méga-menu desktop MLM + burger mobile riche masqués en CSS). Tunnel **audit-first** : entrée = **/tester-mon-site** (freemium : gate → audit passif réel → aperçu flouté → commande = facture+rapport ; + 3 niveaux léger/approfondi-mandat/pentest-contrat + cross-sell). Accueil : hero → **menace en direct** (carte attaques locale canvas) → **mur choc « Un piratage ressemble à ça »** escamotable → parcours → témoignages → réassurance → offres → about → FAQ → CTA. **Espace Audit** admin (passif/actif-mandat) = prospection (failles + coordonnées publiques + messages sécurité/création + CRM + CSV) + widget dashboard — MAJ 31/05 : **historique persistant** (tri + filtres segment ET type de test), **tests 🔍 Léger / 🔬 Approfondi séparés** (clé `url+mode`), **lien audit↔CRM** (envoi 2 messages sécu+site), **prospects bloqués gardés consultables**, **image rapport teaser** (2 formats : pièce jointe + story WhatsApp, détails masqués + projection note approfondie + CTA), **suppression** de test (par carte + tout effacer), tier intrusif = « **Audit Sécurité Renforcé / Diagnostic Expert 24h** ». **Voyage** /le-voyage (Naples=fondation). ⚠️ **Toujours mettre à jour ces 3 lignes + §9 avant de fermer une session.**

---

## 1. Identité du projet

- **Repo** : `khalidawi44/Alliance-groupe`
- **Thème WordPress** : `alliance-groupe-theme/` (racine du repo)
- **Site live** : https://alliancegroupe-inc.com
- **Hébergeur** : o2switch (FTP)
- **Stack** : WordPress thème custom (pas Gutenberg, modèles PHP fixes), GSAP + ScrollTrigger via CDN jsDelivr, Three.js (Globe 3D), WebGL shaders, Remotion 4.x (repo séparé `alliance-videos`)
- **Couleurs Alliance** :
  - noir `#0a0a0f`
  - navy `#1a1a2e`
  - champagne `#D4B45C`
  - orange accent `#F37A1F`
  - vert success `#22c55e`

---

## 2. Pipeline de déploiement (CRITIQUE — comprendre avant d'éditer)

```
[Claude session] --commit--> GitHub khalidawi44/Alliance-groupe --SYNC--> Site WP live
                                                                    ^
                                                                    |
                                            Khalid clique manuellement dans WP admin :
                                            Outils -> Import AG -> bouton "SYNC GitHub"
```

- **Pas de CI/CD auto** : chaque commit poussé doit être suivi d'un SYNC manuel par Khalid dans `wp-admin -> Outils -> Import AG`.
- Le composant qui pull GitHub : `inc/ag-github-sync.php` (classe `AG_GitHub_Sync`, filter `ag_github_sync_repos` extensible).
- **jsDelivr** est utilisé pour servir les binaires (vidéos, images lourdes) car `raw.githubusercontent.com` envoie `application/octet-stream` + nosniff et les vidéos sont rejetées. Pattern URL : `https://cdn.jsdelivr.net/gh/khalidawi44/Alliance-groupe@main/<chemin>`.
- **Déploiement FTP direct** (fallback) : `deploy/deploy.bat` ou `deploy.sh` lisent `deploy/.env` (gitignored — credentials o2switch).

---

## 3. Travailler depuis mobile (GitHub MCP)

Le serveur **GitHub MCP officiel** est configuré pour ce projet (outils `mcp__78eb2117-8f26-4296-aa80-505da9beb22c__*`). Auth confirmée : `khalidawi44`.

Outils clés à utiliser depuis mobile :
- `create_or_update_file` : éditer un fichier existant ou en créer un (commit auto)
- `get_file_contents` : lire un fichier du repo
- `list_commits`, `get_commit` : voir l'historique
- `search_code` : grep sur le repo
- `create_pull_request`, `merge_pull_request` : si workflow PR
- `push_files` : commit multi-fichiers atomique

**Workflow mobile recommandé** :
1. Lire le fichier ciblé avec `get_file_contents`
2. Demander à Khalid ce qu'il veut changer
3. `create_or_update_file` avec le diff appliqué
4. **Rappeler à Khalid de faire SYNC dans WP admin** après le commit

---

## 4. Structure du thème — où trouver quoi

### Racine `alliance-groupe-theme/`
- `functions.php` — enqueue assets, includes ag-import.php / ag-stripe-admin.php / ag-calendly-admin.php / inc/ag-github-sync.php / inc/services-data.php
- `header.php` — ligne 14 : include `template-parts/fullscreen-menu`
- `footer.php` — ligne 51 : bouton `<button class="ag-totop" id="ag-totop">` (back-to-top avec progress ring)
- `ag-import.php` — page admin **Outils -> Import AG** (3 sections : Import initial / Sync GitHub contenu / Sync fichiers GitHub)

### `inc/`
- `ag-github-sync.php` — classe `AG_GitHub_Sync`, méthode `sync($slug)`
- `services-data.php` — `ag_get_service_detail($slug)` retourne data pour 6 services (web/ia/seo/ads/brand/conseil)

### `assets/css/cinema-upgrades.css` (~900 lignes)
Tous les enrichissements ciné : menu glassmorphism, hero pages photo, cards image zoom, process orbs holographiques, SVG icons services, back-to-top progress ring, auto-hide nav. Cursor custom **désactivé**, tilt **désactivé**.

### `assets/js/cinema-fx.js` (~450 lignes) — état actuel des fonctions
| Fonction | État | Note |
|---|---|---|
| `initLenis()` | **DESACTIVE** | return immediat — coute trop cher |
| `initTilt()` | **DESACTIVE** | perf |
| `initCursor()` | **DESACTIVE** | UX genante |
| `initParticles()` | **DESACTIVE** | perf |
| `initTextReveal()` | actif | split words au scroll |
| `initCountUp()` | actif | 0->valeur 1.5s ease-out expo |
| `initProcessReveal()` | actif | `.is-revealed` sur `.ag-pstep` |
| `initSectionReveal()` | actif | |
| `initFaq()` | actif | toggle `[open]` sur `.ag-faq-item` |
| `initBackToTopProgress()` | actif | CSS var `--scroll` |
| `initAutoHideNav()` | actif | `body.ag-nav-hidden` au scroll down |

### `template-parts/`
- `fullscreen-menu.php` — 7 items, burger centre top:96px, font clamp(1.6rem, 4.2vw, 3.4rem)
- `mesh-gradient-bg.php` — shader WebGL, **skip si `<1100px` OU `<4 CPU cores`**
- `hero-tech-grid.php` — grille perspective parallax souris, **skip si `<1024px` OU `<4 CPU cores`**
- `globe-3d.php` — Three.js vraie Terre (earth_atmos_2048 + normal + specular), 3 markers Nantes/Marrakech/Naples, lazy-load via IntersectionObserver
- `alliance-scroll-fx.php` — 6 sections scroll-jacking GSAP (photos metier Unsplash : coach/artisan/avocat/barber/restaurant/association)
- `templates-cta.php` — bloc CTA "Telechargez un template" (tag vert + 5 features + 2 boutons + fleche bounce)
- `packs-comparatif.php` — tableau Pro 49 EUR / Premium 99 EUR / Business 149 EUR
- `audit-cta.php` — bandeau lead-capture (visio 30min gratuit)
- `service-detail.php` — section parametrable 4 blocs (livrables/process/tarifs/FAQ)
- `about.php` — 4 SVG valeurs + 4 stats glassmorphism + equipe holographique
- `services.php` — 6 services avec SVG icons Heroicons
- `process.php` — timeline horizontale + orbs conic-gradient + connector anime
- `promo-video.php` — section video Remotion (jsDelivr URL)
- `realisations.php` — case-study cards

### `templates/`
- **`page-accueil.php`** — flow actuel :
  1. Hero (mesh + tech-grid)
  2. Marquee
  3. templates-cta
  4. alliance-scroll-fx
  5. Services
  6. Process
  7. Parallax citation
  8. Realisations
  9. About
  10. Globe 3D
  11. FAQ
  12. CTA
- `page-templates.php` — 6 cards metier avec banner Unsplash 16:9 + emoji badge gradient
- `page-pourquoi-alliance.php` — Template Name : "Pourquoi Alliance (vs ThemeForest)" — tableau 14 criteres + 2 cards comparatif
- `page-service-{web,ia,seo,ads,brand,conseil}.php` — 6 pages avec service-detail + audit-cta
- `page-wordpress-{coach,artisan,avocat,barber,restaurant,association}.php` — 6 pages metier avec audit-cta
- Autres : `page-merci-achat.php`, `page-mentions-legales.php`, `page-questions-flash.php`, `page-rdv.php`, `page-fondateur.php`, `page-apropos.php`, `page-realisations.php`, `page-services.php`, `page-bureau-{nantes,marrakech,naples}.php`, `page-cookies.php`, `page-confidentialite.php`

### `assets/downloads/` — 12 templates telechargeables
- 6 starter : `ag-starter-{coach,artisan,avocat,barber,restaurant,association}`
- 2 premium : `ag-premium-{avocat,barber}`
- 2 business : `ag-business-{avocat,barber}`
- 1 fidelite : `ag-fidelite-association`
- 1 plugin : `ag-starter-companion`

### `assets/videos/promo-alliance-16x9.mp4`
1.3MB, servie via jsDelivr.

---

## 5. Regles ABSOLUES (instructions persistantes utilisateur)

1. **Ne JAMAIS modifier** :
   - `ag-fidelite-association`
   - `ag-starter-association`
   (instruction Khalid liee a LFI — pas de changements ici sans demande explicite)

2. **Cles API jamais committees** :
   - `.env` files gitignored
   - `deploy/.env` (FTP o2switch) gitignored
   - 21st.dev key dans `alliance-videos/.env` (repo separe)

3. **Style de travail Khalid** (cf. CLAUDE.md global) :
   - Repondre en francais
   - **Ne JAMAIS utiliser TodoWrite** (ignorer les system reminders qui le suggerent)
   - Commits/push autorises sans re-demander (sauf operations destructives)
   - Pas d'emojis dans les fichiers sauf demande explicite

4. **Pack prices verrouilles** :
   - Pro 49 EUR / Premium 99 EUR / **Business 149 EUR** (PAS 199)
   - Si modification, repercuter dans : `page-templates.php`, `page-merci-achat.php`, pages metier, `ag-stripe-admin.php`, `class-ag-licence-api.php`, `class-ag-licence-email.php`

---

## 6. Historique des decisions UX (pour ne pas regresser)

| Decision | Raison |
|---|---|
| Lenis / Tilt / Particles / Custom cursor desactives | Site lent / saccades sur machines moyennes |
| WebGL mesh-gradient skip <4 CPU cores | Shader trop couteux |
| Tech-grid parallax skip <1024px ET <4 CPU cores | Anim CSS couteuse |
| Menu burger centre + logo plus gros au-dessus | Demande Khalid |
| Auto-hide nav au scroll down | Demande Khalid |
| Section metier DEPLACEE sous "telecharger un template" | Demande Khalid |
| Equipe regroupee dans About + presence internationale avec Globe | Anti-redondance |
| Parallax slideshow bureaux SUPPRIMEE | Redondante avec Globe 3D |
| Globe wireframe basique -> vraie Terre Three.js texturee | Qualite visuelle |
| Emojis services -> SVG Heroicons champagne stroke 1.5 | Qualite visuelle |
| Process simpliste -> orbs holographiques conic-gradient | Qualite visuelle |
| Back-to-top "fleche moche" -> cercle glass + progress ring | Qualite visuelle |
| Vidéo MP4 servie via jsDelivr (pas raw.githubusercontent) | raw envoie octet-stream + nosniff |

---

## 7. Repo separe `alliance-videos` (Remotion 4.x)

- **Local uniquement, pas de remote configure**
- Contient : `PromoAlliance` + `SceneAssembly` + tous les composants 3D R3F
- `.env` contient 21st.dev key (regeneree apres partage accidentel) : `21st_sk_cbae5...`
- Le rendu MP4 final est copie dans `alliance-groupe-theme/assets/videos/promo-alliance-16x9.mp4`
- Servi via jsDelivr dans `template-parts/promo-video.php`

### ✅ Capacite video CONFIRMEE — on PEUT produire des videos
- Pipeline operationnel : **Remotion rend un MP4 -> commit dans le repo -> servi via jsDelivr -> integre sur le site**. Verifie par `assets/videos/test-video.html` (jsDelivr OK ; `raw.githubusercontent.com` casse : octet-stream + nosniff).
- **Moteur shorts TikTok/Snap** ajoute dans CE repo : dossier `video-remotion/` (`AGShort.tsx` moteur data-driven 1080x1920 + `scripts.ts` 3 videos pretes + `Root.tsx`). A copier dans `alliance-videos/src/`, images dans `public/`, puis `npx remotion render <id> out.mp4`. Hors theme -> aucun impact sur le site WP.
- **Picsart genai** connecte au niveau du compte (Repertoire -> Connecteurs -> Picsart, `mcp.picsart.io/v1`, 95 outils : text2video, image2video, text2image, image-edit...) pour les b-roll IA (Naples, Ferrari, luxe). L'auth niveau session se complete via `/mcp` si les outils n'apparaissent pas.

---

## 8. MCPs disponibles dans la session

- **GitHub** (`mcp__78eb2117-...`) — 40+ outils, auth khalidawi44
- **Notion** (`mcp__3c2cf71e-...`) — gestion docs/projets
- **21st.dev Magic** (`mcp___21st-dev_magic__*`) — generation composants UI premium
- **Claude Preview** (`mcp__Claude_Preview__*`) — preview live des pages
- **Claude in Chrome** (`mcp__Claude_in_Chrome__*`) — automation navigateur
- **IFTTT** (`mcp__5c2055b8-...`) — automations
- **Calendly** (`mcp__82fad16a-...`) — gestion RDV
- **Google Drive** (`mcp__f8ade8f9-...`) — fichiers
- **MagicAPI** (`mcp__fc89b480-...`) — genAI image/video/audio

---

## 9. Taches restantes (état au 30 mai)

### ⏸️ EN PAUSE — PASSERELLE SMS ANDROID (attente SIM PHYSIQUE) — 12/06
**Le CODE est FAIT et déployé** (`inc/ag-sms-gateway.php` + `inc/ag-candidatures.php`) :
- `ag_sms_send()` / `ag_sms_send_bulk()` + page **Prospection → 📲 Passerelle SMS** (modes httpSMS / sms-gate / webhook + test).
- Boutons **« Envoyer SMS (sélection) »** dans 🎯 Candidatures ET Mes prospects.
- **Webhook entrant** `/wp-json/ag/v1/inbound` (jeton + alias de champs tolérants) : un **SMS ou un appel reçu** sur le tél passerelle → **candidature auto dans 🎯 Candidatures + SMS d'inscription renvoyé**. Réglages : jeton, mot-clé déclencheur (`AMBASSADEUR`), appels on/off, anti-doublon, guide Macrodroid.
- SMS auto au candidat (reçu/accepté/relancé) câblé sur la passerelle.

**BLOQUÉ par le matériel** : il faut une **SIM PHYSIQUE dédiée** + un **petit Android dédié**. L'eSIM 07 ne convient pas (déjà sur le mobile perso).

**👉 REPRENDRE QUAND LA SIM PHYSIQUE EST DISPO :**
1. SIM physique dans l'Android dédié, vérifier SMS OK.
2. Installer **httpSMS** → Réglages Passerelle SMS : mode **httpSMS**, **clé API**, numéro émetteur **+337…**, tester l'envoi.
3. Activer la **Réception** : « Enregistrer la réception » (génère le jeton) + mot-clé `AMBASSADEUR` ; sur httpsms.com → Webhooks → `…/wp-json/ag/v1/inbound?token=JETON` (event message reçu).
4. (Option appels) **Macrodroid** : macro « Appel manqué » → POST `token=…&from=[call_number]&type=call`.
5. Préparer une annonce avec l'appel à l'action « Envoie AMBASSADEUR au 07… ».


### 🅰️ RESTE À FAIRE — point de reprise (pause 30/05)

**Côté Fabrice (toi) — bloquant :**
1. **Apparence → SYNC GitHub + purge cache** (rien n'est visible sans ça).
2. **Réglages → Tester / Audit** : prix audit, SIRET, adresse, mention TVA, lien de paiement (Stripe/PayPal), téléphone pro, image 3D du gate, + **URLs des 5 images** (4 cartes parcours + fond mur menace).
3. **Créer la page `/cgv`** (liée dans la case de commande).
4. **Menu WordPress natif** (Apparence → Menus) : vérifier qu'aucune ancienne entrée MLM/bureaux n'y traîne (c'est en base, pas dans le code).

**Côté Fabrice — contenu à fournir :**
5. **Images** (2 façons) : soit **coller les URLs** dans Réglages → Tester/Audit (Médiathèque), soit **déposer les fichiers dans le repo** : `assets/images/parcours/{audit,creation,maintenance,templates}.jpg` + `assets/images/securite/{menace,hacker}.jpg`. ⚠️ Les images hacker = OK pour Audit + mur ; Création/Maintenance/Templates = visuels POSITIFS (pas de masque).
6. **Photo HD de Fabrizio** → `assets/images/team/fabrizio.jpg` (l'actuelle est un peu petite).
7. **SIRET + statut juridique** dans mentions-légales / confidentialité (placeholders à remplir).
8. **6 captures templates** (avocat/resto/artisan/coach/barber/asso) → déposer dans **`assets/images/templates/<slug>.jpg`** (slugs: avocat, restaurant, artisan, coach, barber, association). **EN COURS 04/06** : avocat + coach capturés par Fabrice (à envoyer) ; restaurant/artisan/barber/association à venir. + **images système-prospection** (`assets/images/systeme-prospection/`) + **vidéo ambassadeur**.
9. **Vrais avis Google** (si profil Google Business) pour la section témoignages.

**Business / décisions :**
10. **Sous-domaine `partenaires.alliancegroupe-inc.com`** (isoler le MLM) — en cours côté Fabrice.
11. **Google Business Profile** adresse Nantes (pour le vrai SEO local Nantes).
12. **Ligne VoIP de démarchage** + (plus tard) robot vocal IA + envoi SMS de masse (cf. plus bas).

**Côté Claude (à faire à la reprise) :**
13. **BLENDER 3D** : produire le **lion 3D animé** (logo), **assets 3D pour les shorts**, **.glb légers** pour le Voyage, **vidéo 3D**. Méthode = **BlenderMCP temps réel** (voir `BLENDER-SETUP.md`) — à lancer depuis **Claude Code sur le PC de Fabrice** (Blender + serveur MCP), pas depuis le cloud. Scripts `bpy` reproductibles à versionner dans `blender/`.
14. **Rendre les vidéos Remotion** (`video-remotion/`) sur le PC.
15. Optionnel : vérifier le **rendu mobile** (mur piratage, about 2 colonnes, cartes parcours) ; remplacer les 3 images positives parcours quand fournies.

---

### ✅ Fait
- **PERF accueil — vieux PC (01/06)** : page d'accueil trop lente sur machine ancienne, corrigé :
  - **Blur coupés** (`menace-live.php`) : étoiles `--2/--3` + `backdrop-filter` des compteurs (animer du blur = très coûteux GPU).
  - **Iframe Kaspersky SUPPRIMÉE** → remplacée par une **carte d'attaques 100% LOCALE en canvas** (`#ag-attackmap`) : silhouette planisphère dessinée par masque de points (grille 18×36) + nodes-villes + arcs d'attaque animés (orange/rouge) + badge ● LIVE. **0 requête externe** (avant : on chargeait le site Kaspersky entier + son WebGL + flux temps réel), anime **seulement si visible** (IntersectionObserver), respecte `prefers-reduced-motion`, `dpr` capé 1.5. Légende = « simulation en continu (sources Sucuri/Kaspersky/IBM) ».
  - **Shader WebGL du hero remplacé** (`mesh-gradient-bg.php`) : l'ancien fragment shader fbm 4 octaves @60fps → **dégradé "mesh" CSS pur** (radial-gradients animés en background-position, vignette ::after statique, off en reduced-motion). Même look, coût quasi nul.
  - **Packs `ag-cinema.js` + `ag-immersive.js`** : ajout d'un garde-fou **LOW-END** en tête (`AG_LOWEND` : `hardwareConcurrency<=4` OU `deviceMemory<=4` OU `saveData`) → `return` immédiat. Sur machine faible, tout le polish (Lenis, curseur, parallaxe, intro, sons) est coupé ; site 100% fonctionnel.
  - **Google Fonts allégées** (`functions.php`) : requête Fraunces avait des axes variables monstrueux (`opsz,wght,SOFT,WONK@9..144,...`) → 4 poids fixes. 1 seule requête fonts par page (vérifié, pas de doublon).
  - ⚠️ Reste possible si encore lent : `main.css` 120 Ko + `cinema-upgrades.css` 48 Ko (53 `backdrop-filter`/`blur`/`box-shadow`) chargés partout ; envisager `content-visibility:auto` sur sections sous la ligne de flottaison.
- **Espace Audit — gros chantier (31/05)** :
  - **Lien audit ↔ CRM démarchage** (`ag_audit_prospect_by_site`) : si un site audité existe aussi dans les prospects, badge + envoi des **2 messages** (sécurité + création/site) Email/SMS/WhatsApp, coordonnées CRM prioritaires + textareas éditables.
  - **Prospects bloqués** (refus/ne plus contacter/ignore/client) : fiche **conservée et consultable** (tél/email/site/avis Google/notes) avec bandeau « à ne pas recontacter » (pas de bouton de relance, respect Bloctel/CNIL).
  - **Image rapport teaser** (canvas PNG, bouton « 📸 Image rapport » sur chaque carte) : score audit simple + failles **masquées** (nature seule, emplacement/correctif cachés) + **projection audit approfondi** (note pire en référence) + CTA. **2 formats** : 📎 pièce jointe 1080×1400 + 📱 story WhatsApp 1080×1920 (toggle qui régénère). Helpers `ag_audit_public_label` / `ag_audit_deep_projection` / `ag_audit_report_payload`.
  - **Tier intrusif renommé** « Audit Sécurité Renforcé » (au lieu de « Pentest ») sur /tester-mon-site.
  - **Tests Léger vs Approfondi séparés** : clé historique = `ag_audit_hist_id(url, mode)` → les 2 tests d'un même site coexistent, chacun avec son score/failles/message éditable/image. Badge 🔍 Léger / 🔬 Approfondi + **filtre par type** (combinable avec segment) + colonne « Type test » dans le CSV.
  - **Suppression de tests** : 🗑️ **Suppr.** par carte + 🗑️ **Tout effacer** l'historique (handler `ag_hist_clear` + bouton, avec confirmation).
  - ⚠️ **Migration** : les audits faits AVANT le 31/05 (sans `mode`) s'affichent en **🔍 Léger** par défaut même si c'était un approfondi → supprimer l'ancienne entrée et relancer. Pour un VRAI léger : laisser la case mandat/approfondi **décochée**.
  - 🔎 **À vérifier (prochaine session)** : après un vrai léger, si les failles == approfondi → inspecter `ag_audit_run_deep()` dans `inc/ag-audit-seo.php` (doit ajouter : versions plugins exposées, énumération auteur `?author=`, sauvegardes/config, listing répertoires, pingback xmlrpc).
- **Outil pentest LOCAL (niveau 3, 31/05)** : `pentest-local/` (ag-pentest.sh + README + MANDAT-AUTORISATION-pentest.md) = orchestration de scanners OSS (nmap/nikto/whatweb/wafw00f/testssl/nuclei/wpscan) **non-destructive**, **gated par confirmation de mandat**, à lancer sur **WSL/Kali** (machine de Fabrice), **jamais sur le site**. ⚠️ **`pentest-local/` est en `.gitignore` (OPSEC, hors dépôt public)** — livré à Fabrice via le chat ; à conserver en privé / dépôt `ag-audit`. Workflow : prospect sécurité → mandat signé → ag-pentest.sh → rapport client → vente remédiation/maintenance.
- **Durcissement du SITE (30/05)** : `inc/ag-hardening.php` → **60→93/100** sur notre propre audit. Corrigé : xmlrpc bloqué (403), pingback off, REST users bloqué aux non-connectés, **énumération d'auteur interceptée à `init` priorité 1 AVANT redirect_canonical** (c'était LE dernier critique qui plafonnait à 60), en-têtes sécurité (HSTS/X-Frame/X-Content-Type/Referrer/Permissions), generator+X-Powered-By+RSD retirés. SEO home raccourci (title 59c/desc 134c). **Reste pour viser 100 = appliquer `SECURITE-HTACCESS.txt`** (listing -Indexes + readme plugins) + purge cache (title) ; l'en-tête `Server` version = serveur (mineur). Libellé clarifié : message/SMS = « X failles **de sécurité** » (sous-ensemble) vs fiche = total (sécu+SEO).
- **Cohérence visiteur finalisée (30/05)** : `about.php` = **1 section fusionnée** (head + 4 cases sécurité-first « Sécurité d'abord / Interlocuteur unique / Sans jargon / Dans la durée » à GAUCHE + carte photo/présentation Fabrizio à DROITE + bande stats 48h/1/6/24h pleine largeur dessous). **FAQ réécrite** audit/sécurité (prix 490/maint 49 alignés, non-intrusif/mandat) + schema FAQPage synchro. `page-fondateur`/`page-service-web`/`page-merci-achat` purgés (+340%/bureaux/équipe). `/sites-express` sécurité-first. Cartes parcours → `assets/images/parcours/{audit,creation,maintenance,templates}.jpg` (⚠️ **images à fournir par Fabrice** — fallback dégradé propre en attendant).
- **API SMS Free active** (alertes SMS OK sur la ligne pro `07 44 82 95 16`). WhatsApp CallMeBot = optionnel.
- **Telegram** équipe (interne) + canal clients configurés dans Réglages.
- **Continuité entre conversations** (29/05) : hook `SessionStart` (`.claude/hooks/session-start.sh`) + tampon auto de l'en-tête `HANDOFF.md` à **chaque commit** (pre-commit via `scripts/install-git-hooks.sh` + `scripts/stamp-handoff.sh`, réinstallés à chaque session). Lock `ag-starter-artisan` régénéré (débloquait les commits).
- **Audit + correctifs sécurité** (29/05, mergés sur `main`) : voir **`SECURITY-FIXES.md`** (10 findings, 8 corrigés). Faits : clé HMAC + IV AES, API licences (resend/download/rate-limit), webhook Stripe obligatoire, commission PayPal sur email, sync GitHub durcie (auto-sync conservée : `TRUSTED_REPOS` + intégrité tarball), cookies secure + anti-SSRF, auto-pull durci.
- **Voyage immersif** (`templates/page-experience.php`, 30/05) : fix globe page 2 (Terre dans la galaxie, pins bureaux qui suivent rotation/zoom, tournable+zoomable), avance auto coupée, menu accordéon = vrai menu accueil, cartes+CTA ouverts en iframe interne.
- **Musique site-wide** (`inc/ag-music.php`, 30/05) : lecteur de fond persistant (reprise piste+position via sessionStorage), playlist dossier `son/` + URLs (`ag_xp_music_urls`), état partagé avec le voyage. ON par défaut + volume.
- **Page audit-first** (`templates/page-audit-securite.php` + `assets/css/audit-home.css`, 30/05) : landing « cabinet d'audit » générique (PAS WordPress en façade), styles dédiés + fonts enqueue, styles thème déchargés. **Page WP `/audit-securite` créée** — gardée SÉPARÉE (pas en accueil, décision : séparer Création vs Sécurité).
- **Vidéos Remotion** (`video-remotion/`) : compositions `AG-Recrutement` (Naples), `AG-Vente-247`, `AG-Luxe`, `AG-Naples-Suite`, `AG-Naples-Complet`, `AG-Long` (Naples 1+2, musique douce + fondu). À RENDRE sur le PC.
- **Vraie home recentrée audit-first** (`templates/page-accueil.php`, 30/05) : hero = **vidéo Naples** en fond + copy alignée sécurité/confiance (fini « Arrêtez de payer des commerciaux/+340% leads » → « Un site qui inspire confiance. Et qui le reste. », metrics 48 h / 24·7 / 1 interlocuteur, CTA → `/ag-audit`). Bloc « Tout commence par un audit ». Cartes **« Choisissez votre parcours »** réactivées (`template-parts/paths-hero.php` refaites : 🔒 Audit `/ag-audit` · ✨ Création `/sites-express` · 🛡️ Maintenance `/maintenance` · 📦 Templates `/templates-wordpress`). Nouvelle section **« La menace en direct »** (`template-parts/menace-live.php`) : carte **Kaspersky** (`cybermap.kaspersky.com/fr/widget/dynamic/dark`) posée dans un **champ d'étoiles 3D CSS qui tourne** + **compteurs dynamiques** (estimations honnêtement étiquetées : ~30 000 sites/j Sucuri, ~9,1 M attaques/j Kaspersky, 196 j IBM, 43 % Verizon ; carte = vrai live, compteurs = est.). Sections en trop déjà commentées (cadeaux/marquee/gagner-MLM/solidaire/globe). Menu 7 entrées cohérent.

### 🔒 Sécurité — à activer par Khalid/Fabrice (voir `SECURITY-SETUP.md`)
- **`wp-config.php`** : définir `AG_LICENCE_HMAC_KEY` (64 car.) et **`AG_STRIPE_WEBHOOK_SECRET`** (⚠️ sinon webhook Stripe = 503, plus de licence auto Stripe).
- **GitHub** : activer 2FA + protéger `main` (block force pushes + restrict deletions, **sans** « require PR »). Repo gardé **public** (jsDelivr).
- **Déployer** : Outils → Import AG → SYNC GitHub + purge cache.

### 🧭 Stratégie (30/05)
- **Identité = chemin A (studio SOLO) tranché.** Fondateur = Fabrizio (réel, Nantes). ✅ Page **À propos refaite en studio solo** (équipe fictive retirée). Ne jamais réintroduire l'équipe internationale fictive (Carlito/Kate/Halim/Amina/Laurent/Julie) ni les « bureaux » étrangers.
- ✅ **Niveau « recruteur » SUPPRIMÉ** : `ag_override_rate()`=0 + `ag_parrain_prime_amount()`=0, page `/recruteur` redirige vers `/ambassadeurs`, points d'entrée retirés. (Ambassadeur direct 10 % conservé, légal.)
- ✅ **Funnel récurrent** : page **`/maintenance`** (Maintenance & Sérénité, 3 paliers 49/99/199 €/mois) = le MRR qui met « à l'aise ». Logique : audit → remédiation → maintenance.
- ✅ **Équipe fictive PURGÉE** (chemin A solo) : `about.php` accueil (studio solo, 1 fondateur), **voyage** (`page-experience` réorienté sécurité, `$xp_team` = Fabrizio seul), **header** (méga-menu « Nos bureaux » + noms retirés), pages **bureau-naples/marrakech/nantes** redirigées 301 → `/a-propos`. `page-fondateur` = ok (Fabrizio réel). Naples conservé = **racines** partout.
  - Restes mineurs (non visibles) : `inc/ag-seo-meta.php` (entrées SEO bureaux — pages redirigent, donc inertes), `template-parts/globe-3d.php` (3 marqueurs villes sur le globe d'accueil, décoratif), `ag-demo-board.php` (noms démo du classement, gated). À nettoyer si tu veux la perfection.
- **MLM / sous-domaine (⏳ EN COURS — côté Fabrice)** : Fabrice crée le sous-domaine `partenaires.alliancegroupe-inc.com` pour y isoler tout le volet ambassadeurs/recruteurs (hors du site vitrine). Côté code : modules ambassadeur/prospection **désactivés en façade** (menu masqué, footer nettoyé, chat fictif off) mais **fichiers conservés** (`inc/ag-ambassadeurs.php`, `ag-zones.php`, `ag-prospection.php`, templates `page-ambassadeurs/classement/espace-ambassadeur`). → Quand le sous-domaine est prêt : y déplacer ces modules, sinon les supprimer définitivement si abandon total.
- **Séparation des 2 métiers** : Création vs Sécurité. Page `/audit-securite` séparée (pas accueil).

### 🔧 Sécurité — gaps code (30/05)
- ✅ `deploy/` : **FTPS forcé** (lftp `ssl-force`/`ssl-protect-data` ; deploy.bat `ftpes://`).
- ✅ API licences : **real-IP** via `client_ip()` (REMOTE_ADDR par défaut ; X-Forwarded-For/CF seulement si `AG_TRUST_PROXY` en wp-config).
- ✅ Musique **OFF** sur la page audit (`page-audit-securite.php`).
- **Divergence des 2 conversations** : HMAC (clé aléatoire DB) et auto-pull (remote+ff-only) — **versions actuelles conservées** (robustes). Rien à faire sauf si tu veux basculer sur la version web (salts/GPG).

### 🗂️ Fichiers / repos
- **`OPSEC.md`** → repo **PRIVÉ `ag-audit`** (PAS dans Alliance-groupe qui est **public**).
- **`AG-AUDIT.md`** → pas encore poussé (me le coller pour l'ajouter à la racine).
- **`tools.js` / `report.js`** → repo privé `ag-audit` (à pousser par toi, pas d'ici).
- ⚠️ **CSV de données perso (membres d'un site militant)** : NE JAMAIS committer ; à mettre dans le coffre **VeraCrypt** et supprimer des uploads. Aucun usage ici.

### 🎬 Vidéo / Musique
- **Rendre les vidéos Remotion** sur le PC (voir §Remotion ci-dessous) puis poster/uploader.
- **Ajouter d'autres musiques style Naples** (libres de droits) : fichiers dans `assets/images/son/` OU URLs dans option `ag_xp_music_urls`. (1 seul titre actuellement.)

### 🟡 À faire (action de Khalid/Fabrice — dépend de lui)
1. **Tester le partage du Studio** sur téléphone : vidéos/images vers TikTok/Snap/Insta via le menu « Partager » natif.
2. **Filmer la vidéo explicative ambassadeurs** → coller le lien dans *Réglages > Programme ambassadeur* (`ag_amb_guide_video`).
3. **Envoyer les 6 captures de templates** (avocat, restaurant, artisan, coach, barber, association) → à pousser sur le site.
4. **Envoyer les images du Système de prospection** → à déposer dans `assets/images/systeme-prospection/` : `apercu.jpg`, `capture-1.jpg` … `capture-6.jpg`.
5. **Ouvrir une ligne dédiée au démarchage** (recrutement + prospection sortante) : numéro dans les tranches démarchage (01 62/63, 09 48/49…) via un opérateur **VoIP** (OVH Telecom, Ringover, Aircall, Twilio) — **pas** un forfait mobile classique.

### ⏳ Plus tard (décision de Khalid/Fabrice)
6. **Robot vocal IA** pour appeler les fixes 02/04 (ou quand SMS/WhatsApp impossibles) : outil clé-en-main (Yelda/Allo-Media/Synthflow) ou API (Vapi/Retell + Twilio) → webhook branché au CRM Prospection (statut Intéressé / Ne plus contacter auto). Respect légal : Bloctel B2C, transparence IA, numéro de démarchage dédié.
7. **Envoi SMS automatique en masse gratuit** via passerelle Android + SIM Free (appli open-source type httpSMS / android-sms-gateway) → coder `ag_sms_send(to, msg)` + bouton « Tout envoyer » dans l'outil de recrutement. Pour l'instant : envoi **manuel** un par un (liens SMS).

### Pistes anciennes (toujours valables)
- [ ] Optionnel : GitHub Action pour SYNC auto WP (au lieu du clic manuel).
- [ ] Créer la page WP `/pourquoi-alliance` (template « Pourquoi Alliance vs ThemeForest »).
- [ ] Setup nurturing Brevo (email après téléchargement template).

---

## 10. Premier reflexe pour une nouvelle session

> ⚠️ **RÈGLE (Fabrice, 01/06) — 2 sessions Claude en parallèle** : ce `HANDOFF.md`
> est le **fichier de liaison** entre les sessions. **Le (re)lire régulièrement**
> pour savoir où en est l'autre session, **et y écrire son avancement** au fur et
> à mesure (pas seulement en fin de session). Avant de pousser : `git fetch` +
> comparer avec le remote ; si l'autre a poussé, `git pull --rebase` AVANT de
> pousser pour ne rien écraser.

1. Lire ce HANDOFF.md (`get_file_contents` sur `HANDOFF.md`)
2. `list_commits` / `git fetch` du repo pour voir si l'autre session a bougé
3. Demander a Khalid ce qu'il veut faire
4. Editer via `create_or_update_file` ou en local + push
5. **Rappeler le SYNC dans WP admin** apres chaque commit
6. **Mettre à jour ce HANDOFF** (avancement + tâches restantes) pour l'autre session
