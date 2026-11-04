# MACHINE AUDIT & PROSPECTION SÉCURITÉ — contexte complet (session 30/10/2026)

> Mémoire de session pour la reprise. Couvre la **machine de prospection par la
> sécurité** (le filon), tous les outils, **les connexions entre eux**, et le
> reste à faire. Complète `docs/FILON.md` (stratégie) et `HANDOFF.md` (résumé daté).
> Tout ce qui est décrit ici est **déployé sur `main`** (SYNC GitHub pour l'appliquer au site).

## 0. Le filon en une phrase
On ne vend plus « un site ». On vend la **sécurité + la PREUVE de conformité**
(RGPD art. 32). Cible n°1 = santé libérale. Escalier : test passif gratuit →
Diagnostic 290 € **sous mandat** (Kali) → remédiation → **récurrent (MRR)**.
Offres récurrentes = **Sérénité 29 / Protection 59 / Conformité 99** (attestation
art. 32 = livrable phare du 99). Signal de réussite : **audit payant → abonnement ≥ 30 %**.

## 1. UNE SEULE MACHINE, DEUX INTERFACES (la connexion clé)
L'**app mobile** (`/prospection-mobile`, template `page-prospection-mobile.php`) et
le **WordPress admin** partagent **les mêmes données** (options WordPress) :

| Donnée | Option WP | Partagée app ↔ admin |
|---|---|---|
| Prospects / CRM | `ag_prospects` | ✅ |
| Journal des appels (Emma) | `ag_voice_log` | ✅ |
| Historique des audits | `ag_audit_history` (admin) **+** `ag_app_audits` (app) | ✅ **unifié** : app écrit aussi dans `ag_audit_history` (`ag_audit_hist_upsert`), l'app fusionne `ag_audit_history` en lecture, + rattrapage unique `ag_audit_hist_backfill_v1` |
| Agences cartographiées + leurs créations | `ag_agencies` | ✅ |
| Rapports Kali (par site) | `ag_kali` (clé = `md5(site)`) | ✅ |
| Note d'audit sur la fiche prospect | `audit_score`/`audit_crit` dans `ag_prospects` | ✅ (autolink) |

**App intégrée dans WordPress** : menu admin **« 🚀 Application »** (`inc/ag-app-admin.php`)
= iframe plein écran de `/prospection-mobile` → 100 % des fonctions dans l'admin,
mêmes données. Page `/prospection-mobile` auto-créée (flag `ag_app_page_v1`).

## 2. MOTEUR D'AUDIT (le cœur)
- `ag_audit_run($url)` (`inc/ag-audit-seo.php`) = **passif** (~19 contrôles : HTTPS, en-têtes,
  SSL, .git/.env, énumération comptes, robots/sitemap/SEO, a11y RGAA…).
- `ag_audit_run_deep($url)` = **approfondi non-intrusif** (sous mandat) : + ?author=1,
  sauvegardes/config exposées, versions plugins, pingback, **+7 sondes conformité/email
  ajoutées cette session** : SPF, DMARC, politique de confidentialité/mentions légales,
  **traceurs vs consentement cookies**, Google Fonts externes (RGPD), security.txt,
  /wp-login exposé.
- `ag_audit_get($url)` (`inc/ag-prospection.php`) = lance le **deep**, cache 1h. Utilisé partout → note homogène.
- `ag_app_audit` (AJAX) = audit depuis l'app/WordPress : renvoie score, failles, messages,
  rapport, `is_agency`, `agency`, **`proof`**. Persiste la note sur la fiche prospect +
  historique (app + admin). **Kali = jamais lancé ici** (hébergement mutualisé) : Kali tourne
  sur la Linux de Fabrice, le rapport est **importé** (option `ag_kali`).

### PREUVE FORTE (vente) — `ag_audit_proof()` + `ag_kali_proof()`
- `ag_audit_proof($audit)` : extrait un élément **public mais cru privé** (login admin
  énuméré > comptes wp-json > fichier sensible accessible > version exacte). 100 % légal.
- `ag_kali_proof($site)` : extrait du rapport Kali importé la **preuve ultime** (mot de passe,
  identifiants valides, uid=/RCE, secret). **N'existe que si Kali a tourné = sous mandat**.
- Priorité Kali > passif. **Secrets MASQUÉS côté client** (`ag_mask_secret`, ex. `Su•••••••4`,
  « communiqué en entier en privé »). Valeur entière visible seulement dans l'app (Fabrice).
- Affichage : encadré « 🔎 Preuve » dans le **rapport client** (`ag_render_client_report`),
  dans les **messages**, et dans le **résultat d'audit de l'app**.
- **LIGNE ROUGE tenue** : jamais prétendre avoir un mot de passe/être « entré » sans mandat ;
  jamais de cracking dans le code (on LIT le rapport Kali importé) ; masquage côté client.

## 3. CARTOGRAPHIE DES AGENCES → LEURS CLIENTS (`inc/ag-agences.php`)
Audite une agence web → bouton **« Cartographier ses créations »** (AJAX `ag_app_agency_scan`) :
- `ag_agency_find_creations()` **suit les fiches projet internes** (`/portfolio/slug/`) et
  extrait le **site client** de chacune (priorité aux liens « Découvrir/Voir le site »).
  Fix majeur : **ancres `<a>` uniquement** (ne prend plus les `<link rel=preconnect>` = traceurs)
  + **grosse skip-list** (Clarity, HubSpot, Snitcher, Segment, CDN, CMP…). Validé sur jolipixel.fr.
- Les clients trouvés sont **injectés dans le CRM** (source `agence:<nom>`, anti-doublon par site).
- Nettoyage unique des faux prospects traceurs déjà créés (flag `ag_agency_cleanup_v1`).
- **Onglet « Trouvés »** (app, AJAX `ag_app_creations`) = liste à plat de toutes les créations,
  **séparée des sites audités**, badge **SÉCURITÉ par score** (🔴 À sécuriser / 🟠 À renforcer /
  🟢 Plutôt bon — PLUS jamais « pas de vrai site »), actions Voir/Auditer/+Ajouter. Après un scan,
  bascule auto sur Trouvés.

## 4. MESSAGES & RAPPORT PERSONNALISÉS
- Message **« agence »** (`$msg['agence']`) quand le prospect vient d'une agence : « votre site a
  été fait par <agence>, mais une agence web n'est pas une agence de sécurité… aperçu + débloquez
  le rapport ». Bouton vert dans l'app. Intro « agence » aussi dans le **rapport client** (param `&agence=`).
- Autres messages déjà là : propriétaire (`perso`), partenaire (agence apporteur %), rapport SMS.
- Rapport client (`ag_render_client_report`, déclenché par `?ag_rapport=1&site=&name=`) : score,
  2 points visibles + reste **verrouillé** (« Débloquer »), **bloc Kali** (par site), **encadré Preuve**,
  intro agence. Mode carte `?card=1` (image MMS).

## 5. DÉMARCHAGE AUTONOME (déjà en place, repackagé filon)
- **Hugo** (`inc/ag-closer.php`) = closer email. Angle **`conformite`** par défaut (pro avec vrai
  site) ; repli sans IA `ag_closer_gabarit` **a sa branche conformité**. Interrupteur `ag_closer_on`.
- **Emma** (robot vocal, `inc/ag-voice-webhook.php`) : `ag_voice_auto_on`, **heure préférée 15h30**
  (`ag_voice_heure`, fenêtre 15h30–16h30), heures légales L-V, avocats exclus, opt-out, cap économe 8/j.
  **Les appels ne partent que si un fournisseur voix (Retell/Twilio) est configuré.**
- **Pilote auto « TOUT ON »** (`inc/ag-autopilot.php`, flag `ag_auto_boot`, bumpé à **v3**) : à la
  SYNC, allume tous les agents **+ Emma** et fixe l'heure 15h30. Mode **économe** `ag_eco_mode` (ON).
- Offres 3 niveaux : `page-maintenance.php` + `page-sites-express.php` + `ag-meta-feed.php` (noms
  Sérénité/Protection/Conformité ; clés PayPal/IDs catalogue inchangés). Attestation art. 32 =
  `assets/tools/attestation-art32.html` (imprimable).

## 6. OUTILS ANTI-SPAM DÉMARCHAGE (perso Fabrice, `pentest-bridge/`)
- `trace-numero.py` (Kali) + `analyse-numero.html` (web/PWA, aussi dans `assets/tools/`) :
  identifie l'opérateur ARCEP d'un numéro, génère signalements (opérateur/SignalConso/33700).
- `indemnisation-demarchage.html` : générateur de mise en demeure / plainte (service AG).
- Snapshot ARCEP embarqué ; rafraîchir via `trace-numero.py --refresh` + `build-analyse-numero.py`.

## 7. UX / DIVERS (session)
- **Session persistante** (`inc/ag-session-persist.php`) : admin cookie **1 an** (jamais déconnecté),
  ambassadeurs **30 j** (`ag_team_long_session`). Prend effet à la prochaine **connexion**.
- **App** : boutons **‹ Retour** (historique d'onglets) + **⟳ Rafraîchir** ; le retour matériel du
  téléphone revient d'onglet.
- **Onglet « Promo »** : partage natif 1 clic + QR + **affiches A4 imprimables travaillées** (slogan +
  visuel + couleur par appli).
- **« Refais mon site »** (`inc/ag-refais-mon-site.php`) : champ URL affiné, aperçu « avant »
  fiabilisé (retry mShots + placeholder), **avant/après auto + glissable** (après = vraie homepage AG
  `assets/images/refais-apres.jpg`).

## 8. DÉPLOIEMENT (RÈGLE)
- Je développe **sur `main`** via worktrees jetables (`git worktree`), `php -l` avant commit, push `main`.
- Le site applique via **Apparence → SYNC GitHub** (Vérifier MAJ → SYNC FICHIERS DU THÈME) **+ purge cache**.
- Beaucoup d'effets prennent au **prochain chargement** (flags `*_v1/v3`, auto-créations de pages).
- Attribution commits : `Co-Authored-By: Claude Opus 4.8` + `Claude-Session`.

## 9. RESTE À FAIRE / idées ouvertes
1. **Fournisseur voix Emma** (Retell/Twilio) à configurer + **ligne de démarchage dédiée** (01 62 / 09 48)
   avant d'allumer les appels (rappel n°7). Sans ça, Emma reste muette (pas d'erreur).
2. **Liste santé qualifiée** : fichier `pentest-bridge/liste-sante-qualifiee.md` (critères) +
   `liste-sante-resultats.md` (17 cabinets WP vérifiés). **À injecter dans le CRM** + recentrer la
   chasse Google Places sur la santé (requêtes prêtes dans le .md). La chasse actuelle vise des cibles hors-filon.
3. **Déontologie avant volume** : faire valider les séquences Hugo/Emma par `ag-deontologue` +
   `ag-gardien-reputation` avant de monter les plafonds.
4. **Score en 2 notes** (option proposée à Fabrice) : séparer **Sécurité** et **SEO/Perf** pour
   que « beau site mais score bas » soit limpide. À faire s'il confirme.
5. **Ramener dans l'admin** (hors iframe) la cartographie/Trouvés en pages natives si l'iframe gêne.
6. **Insérer ses propres images/preuves** dans le rapport avant envoi (demandé, à préciser).
7. **Robot vocal numéros fixes 02/04** (rappel n°5/10) ; **passerelle SMS Android** en attente SIM physique (n°9).
8. Harmoniser le flux Meta/prose SEO restante si besoin (noms d'offres).

## 10. GARDE-FOUS (non négociables)
Pas de scan actif sans **mandat écrit** ; **jamais** de faille exploitable ni de mot de passe livré à un
prospect froid (masquage + gate Kali) ; pas de fausse urgence ni chiffre non sourcé ; **avocats =
email/courrier only** ; opt-out partout ; question commerciale → agents déonto avant action.
