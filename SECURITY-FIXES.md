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
| 2 | 🟠 | `class-ag-licence-api.php:150-184` | `resend` public : renvoie les clés sur simple email (divulgation + énumération). | ⏳ |
| 3 | 🟠 | `class-ag-licence-api.php:366-372` | Route `download` fantôme + token en GET (fuite URL), pas de lien licence/domaine, `file` non validé (path traversal ?). | ⏳ |
| 4 | 🟠 | `class-ag-licence-api.php:54-58,321` | `update_check` / `companion_update` publics **sans** rate-limit. | ⏳ |
| 5 | 🟠 (design) | `ag-github-sync.php:312-446` ; `ag-import.php:449-567` | Sync GitHub = RCE : code PHP distant écrit/activé sans vérif d'intégrité ; cron + auto-sync mobile permanents. | ⏳ |
| 6 | 🟡 | `ag-import.php:51` | SQL `LIKE` concaténé (pas d'injection ici — nonce+cap présents — conformité seulement). | ⏳ (cosmétique) |
| 7 | 🟡 | `class-ag-licence-stripe.php:29-34` | Signature webhook Stripe **contournable** si `AG_STRIPE_WEBHOOK_SECRET` non défini → licences frauduleuses. | ⏳ |
| 8 | 🟡 | `ag-paypal.php:127-156` | Rapprochement paiement→commission par **montant seul** → détournement de commission. | ⏳ |
| 9 | 🟢 | `ag-espaces.php:670` | OAuth Client ID en dur (public par nature, **non sensible**). Flux Google bien vérifié. | RAS |
| 10 | 🟢 | `ag-espaces.php:213,661,749` ; `ag-audit-seo.php:38` | Cookie secure = `is_ssl()` (forcer `true`) ; SSRF possible sur l'audit SEO (`wp_remote_get` vers URL fournie). | ⏳ |

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

## Prochaines actions recommandées (ordre de gravité)
1. **Webhook Stripe (Finding 7)** : refuser 403 si secret absent ou signature manquante. *(rapide)*
2. **Chaîne de sync GitHub (Finding 5)** : désactiver le cron auto + l'auto-sync mobile, exiger une action manuelle ; à terme signer les releases / pinner un SHA. Traiter le dépôt GitHub comme un actif de prod (2FA, protection de branche).
3. **API licences (Findings 2,3,4)** : durcir `resend` (preuve de possession + rate-limit par email + réponse générique), ajouter rate-limit à `update_check`, vérifier/supprimer la route `download`.
4. **Commission PayPal (Finding 8)** : exiger correspondance email/`custom_id` avant crédit auto.
5. **Divers (Finding 10)** : cookie auth `secure=true`, bloquer hôtes privés/loopback sur l'audit SEO (anti-SSRF).

---

## Notes — Auto-pull (supply-chain, machine de dev)
`auto-pull.bat` (lancé sans fenêtre par `auto-pull-silent.vbs`) fait un `git pull origin main`
répété. Couplé au bouton **SYNC GitHub** WordPress, tout commit sur `main` finit déployé
**sans revue**. Pistes : (1) repasser en pull **manuel** + revue avant SYNC ; (2) pull sur
branche tampon + diff avant merge ; (3) commits signés GPG ; (4) protéger `main` sur GitHub.
→ Décision opérationnelle du proprio (pas un correctif de code).
