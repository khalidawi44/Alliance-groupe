# 🔓 DÉVERROUILLÉ — ag-premium-avocat

**Déverrouillé le `2026-06-16`** à la demande explicite du propriétaire (Fabrizio).

Raison : projet de **fusion des tiers Premium + Business** en une seule offre
« Premium » et liberté de modification du template. Le verrou d'intégrité
(`.LOCK.sha256`) a été retiré ; `scripts/check-premium-lock.sh` ne s'applique
donc plus. `ag-premium-avocat` reste listé dans `.AG_FOCUS` (commits autorisés).

## Historique
- 2026-04-28 : verrouillé (version Premium finalisée).
- 2026-06-16 : **déverrouillé** (fusion Premium/Business + reprise des modifs).

## Pour reverrouiller plus tard (si besoin)
1. `bash scripts/regenerate-premium-lock.sh` (régénère `.LOCK.sha256`)
2. Restaurer les règles « aucune modification ».
3. Retirer `ag-premium-avocat` de `.AG_FOCUS` si on veut bloquer les commits.
