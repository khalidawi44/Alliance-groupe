# Mode café — déploiement à distance

Ce dépôt peut être déployé **sans que le PC soit allumé**, directement depuis
le cloud, via le connecteur GitHub (compte propriétaire `khalidawi44`).

## Chaîne de déploiement

1. Écriture directe des fichiers → GitHub `main` (API GitHub, aucun PC requis)
2. GitHub Action `gwen-images.yml`
3. WP-Cron `ag-github-sync.php` (toutes les 5 min) → synchronise le thème
4. Site live : https://alliancegroupe-inc.com

## Sécurité (inchangée)

- Le token serveur `AG_GH_TOKEN` reste dans `wp-config.php` (jamais commité).
- Fichiers protégés jamais synchronisés : `wp-config.php`, `.env`, `.htaccess`.
- `_local-prive/`, secrets, rushes vidéo tiers : jamais publiés (`.gitignore`).

_Généré automatiquement lors de la validation du mode café._
