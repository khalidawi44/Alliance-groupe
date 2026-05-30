# SECURITY-SETUP.md — à faire par le proprio (config, hors code)

> Les correctifs de code sont faits (voir `SECURITY-FIXES.md`). Il reste ces
> réglages que seul toi peux activer (wp-config + GitHub). Coche au fur et à mesure.

## 1. wp-config.php (sur o2switch, à la racine WordPress)

Ajoute ces 2 constantes **avant** la ligne `/* That's all, stop editing! */` :

```php
// Clé de chiffrement/signature des licences (64 caractères aléatoires, unique à ce site)
define( 'AG_LICENCE_HMAC_KEY', 'COLLE_ICI_64_CARACTERES_ALEATOIRES' );

// Secret du webhook Stripe (Dashboard Stripe → Developers → Webhooks → ton endpoint → "Signing secret", commence par whsec_)
define( 'AG_STRIPE_WEBHOOK_SECRET', 'whsec_xxxxxxxxxxxxxxxxxxxxxxxx' );

// OPTIONNEL — uniquement si le site est derrière un proxy/CDN de confiance
// (Cloudflare, load-balancer). Active l'IP réelle du visiteur pour le rate-limit
// de l'API licences. ⚠️ NE PAS activer sans proxy (X-Forwarded-For spoofable).
// define( 'AG_TRUST_PROXY', true );
```

- [ ] `AG_LICENCE_HMAC_KEY` défini — sinon une clé aléatoire est générée en base (OK mais propre à ce site).
- [ ] `AG_STRIPE_WEBHOOK_SECRET` défini — ⚠️ **obligatoire** : sans lui, le webhook Stripe refuse tout (503) et aucune licence ne se crée automatiquement via Stripe. (PayPal non concerné.)
- [ ] `AG_TRUST_PROXY` — **seulement si** derrière Cloudflare/LB (sinon laisser commenté).

> Générer 64 caractères aléatoires : https://roll.urandom.fr/ ou dans WP `wp_generate_password(64,true,true)`.

## 2. GitHub (le dépôt fait partie du périmètre de prod via la sync auto)

Repo `khalidawi44/Alliance-groupe` — **garder PUBLIC** (jsDelivr sert les vidéos/images du site). Donc :

- [ ] **Aucun secret dans le repo** (déjà corrigé : plus de clé HMAC par défaut). Ne jamais committer `wp-config.php`, clés API, mots de passe.
- [ ] **Activer la 2FA** sur ton compte GitHub : Settings → Password and authentication → Two-factor authentication.
- [ ] **Protéger la branche `main`** : Settings → Branches (ou Rules → Rulesets) → protéger `main` avec :
  - ☑ Block force pushes
  - ☑ Restrict deletions
  - (NE PAS cocher « Require a pull request » : ça bloquerait ton déploiement par push direct.)
- [ ] Vérifier qui a accès en écriture au repo (Settings → Collaborators) — le moins possible.

## 3. Auto-pull (machine de dev Windows)

`auto-pull.bat` a été durci (vérifie le remote de confiance + `--ff-only`). C'est acceptable
maintenant que (a) plus aucun secret n'est dans le repo, (b) la sync vérifie l'intégrité, (c) 2FA + protection de branche limitent qui peut pousser sur `main`.

- [ ] (Optionnel) Si tu veux une revue avant déploiement : retirer `auto-pull-silent.vbs` du démarrage et faire le pull manuellement.

## 4. Déploiement des correctifs déjà faits

- [ ] WP admin → Outils → Import AG → **SYNC GitHub** (Vérifier MAJ puis SYNC fichiers) → purge cache → Ctrl+recharge.
