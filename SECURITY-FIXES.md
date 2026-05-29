# SECURITY-FIXES.md — suivi des correctifs de sécurité

> Tracker des failles identifiées sur le thème/plugins Alliance Groupe et leur état.
> ⚠️ Ce fichier DOIT rester commité (sinon il se perd au changement de conteneur).
> Légende : 🔴 critique · 🟠 élevé · 🟡 moyen · 🟢 faible · ✅ corrigé · ⏳ à traiter
> Audit complet : 2026-05-29.

---

## Synthèse priorisée (audit 2026-05-29)

| # | Sév | Fichier:ligne | Faille | État |
|---|-----|---------------|--------|------|
| 1 | 🔴 | `ag-licence-manager.php:26-28` ; `class-ag-licence-db.php:92,107` | Secret HMAC par défaut public → forge de réponses signées + déchiffrement de toutes les clés. IV AES statique (réutilisé). | ✅ corrigé |
| 2 | 🟠 | `class-ag-licence-api.php:150-184` | `resend` public : renvoie les clés sur simple email (divulgation + énumération). | ✅ corrigé (gardée + durcie) |
| 3 | 🟠 | `class-ag-licence-api.php:366-372` | Route `download` fantôme + token en GET (fuite URL), pas de lien licence/domaine, `file` non validé (path traversal ?). | ✅ corrigé |
| 4 | 🟠 | `class-ag-licence-api.php:54-58,321` | `update_check` / `companion_update` publics **sans** rate-limit. | ✅ corrigé |
| 5 | 🟠 (design) | `ag-github-sync.php` | Sync GitHub = RCE : code PHP distant écrit sans vérif d'intégrité. | ✅ durci (auto-sync conservée) |
| 6 | 🟡 | `ag-import.php:51` | SQL `LIKE` concaténé (pas d'injection ici — nonce+cap présents — conformité seulement). | ⏳ (cosmétique, laissé) |
| 7 | 🟡 | `class-ag-licence-stripe.php:29-34` | Signature webhook Stripe **contournable** si `AG_STRIPE_WEBHOOK_SECRET` non défini → licences frauduleuses. | ✅ corrigé |
| 8 | 🟡 | `ag-paypal.php:127-156` | Rapprochement paiement→commission par **montant seul** → détournement de commission. | ✅ corrigé |
| 9 | 🟢 | `ag-espaces.php:670` | OAuth Client ID en dur (public par nature, **non sensible**). Flux Google bien vérifié. | RAS |
| 10 | 🟢 | `ag-espaces.php:213,661,749` ; `ag-audit-seo.php:38` | Cookie secure = `is_ssl()` (forcer `true`) ; SSRF possible sur l'audit SEO. | ✅ corrigé |

**Points vérifiés SANS problème** : `ag-prospection.php` (nonces + capability + ownership partout ; `ag_lead` nopriv a nonce+honeypot+sanitize) ; `ag-audit-seo.php` (nonce+honeypot, sorties échappées) ; webhook **PayPal** correctement durci (vérif signature serveur, modèle à suivre) ; `class-ag-licence-db.php` requêtes via `$wpdb->prepare()`.

---

## ✅ Corrigé

### 1. 🔴 Secret HMAC par défaut + IV AES statique (commit du 2026-05-29)
- **`ag-licence-manager.php`** : suppression de la clé en dur `ag-default-hmac-change-me-in-wp-config`.
  Si `wp-config.php` ne définit pas `AG_LICENCE_HMAC_KEY`, génération d'une clé aléatoire de
  64 caractères (`wp_generate_password`) **persistée** dans l'option `ag_lm_hmac_key`
  (autoload=no). `wp-config.php` reste prioritaire (recommandé pour garder la même clé
  entre environnements).
- **`class-ag-licence-db.php`** : `encrypt_key()` utilise désormais un **IV aléatoire**
  (`random_bytes(16)`) par chiffrement, préfixé au ciphertext et tagué `v2:`.
  `decrypt_key()` lit le format `v2:` et **garde la compatibilité** avec l'ancien format
  (IV statique) pour les clés déjà chiffrées.
- ⚠️ **Caveat** : si des licences avaient été chiffrées avec l'ancienne clé par défaut,
  elles restent lisibles tant que la clé n'a pas changé. Dès qu'une vraie clé est posée en
  `wp-config`, les anciennes données chiffrées avec le défaut devront être régénérées.

---

### 2-3-4. 🟠 Durcissement de l'API licences (commit du 2026-05-29)
- **#2 `resend_key`** : fonction **conservée** (un client peut récupérer sa clé) mais durcie —
  rate-limit **par email** (3/h) en plus du rate-limit IP, et **réponse générique identique**
  que l'email existe ou non (`'Si une licence est associée à cet email…'`, plus de `count`)
  → fin de l'énumération d'emails clients.
- **#3 route `download`** : la route `ag/v1/download/{slug}` était **référencée mais jamais
  enregistrée** → elle existe désormais avec validation stricte : token **usage unique**
  (supprimé dès consultation), **15 min** au lieu d'1 h, **lié au domaine**, fichier validé
  (`basename` + `.zip` uniquement + `realpath` confiné au dossier downloads → anti path-traversal).
- **#4 rate-limit** : ajouté sur `update_check` **et** `companion_update` (ils en étaient dépourvus).

---

### 5-7-8-10. Lot final (commit du 2026-05-29)
- **#7 Webhook Stripe** : signature désormais **obligatoire** — 503 si `AG_STRIPE_WEBHOOK_SECRET` absent, 403 si signature manquante/invalide. Plus de licence frauduleuse possible.
- **#8 Commission PayPal** : crédit auto **uniquement sur correspondance email** (fin du rapprochement « montant seul »). Sans email correspondant → paiement réservé + **alerte** pour rapprochement manuel (rien n'est perdu).
- **#5 Sync GitHub** (auto-sync **conservée** à la demande du proprio) : (a) **verrou source** — seul `khalidawi44/Alliance-groupe` est synchronisable (`TRUSTED_REPOS`), tout autre dépôt injecté via filtre est refusé ; (b) **contrôle d'intégrité** — la racine du tarball doit correspondre au SHA du commit attendu ; (c) `PROTECTED` étendu (`.user.ini`, `php.ini`, `.htpasswd`, `web.config`, `.git`).
- **#10** : cookies d'auth `wp_signon`/`wp_set_auth_cookie` forcés en **secure** (HTTPS) ; **anti-SSRF** sur l'audit SEO via `wp_http_validate_url()` + `reject_unsafe_urls`.

---

## Reste (non bloquant)
- **#6** `ag-import.php:51` : requête `LIKE` concaténée — **pas une faille** (nonce + capability présents, aucune variable utilisateur). Laissé tel quel (cosmétique).
- 🔴 **Auto-pull supply-chain** (machine de dev) : décision opérationnelle — voir §Notes.

### Recommandations serveur (config, hors code)
- Définir `AG_LICENCE_HMAC_KEY` (64 car. aléatoires) et `AG_STRIPE_WEBHOOK_SECRET` dans `wp-config.php`.
- Protéger la branche `main` sur GitHub + 2FA (le dépôt fait partie du périmètre de prod via la sync auto).

---

## Notes — Auto-pull (supply-chain, machine de dev)
`auto-pull.bat` (lancé sans fenêtre par `auto-pull-silent.vbs`) fait un `git pull origin main`
répété. Couplé au bouton **SYNC GitHub** WordPress, tout commit sur `main` finit déployé
**sans revue**. Pistes : (1) repasser en pull **manuel** + revue avant SYNC ; (2) pull sur
branche tampon + diff avant merge ; (3) commits signés GPG ; (4) protéger `main` sur GitHub.
→ Décision opérationnelle du proprio (pas un correctif de code).
