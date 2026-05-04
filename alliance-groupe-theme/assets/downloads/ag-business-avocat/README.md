# AG Business Avocat

Plugin WordPress qui ajoute les fonctionnalités Business au thème **AG Starter Avocat** (Free).

## Installation

1. Le thème Free `ag-starter-avocat` doit être activé.
2. Le plugin `ag-premium-avocat` peut être installé en parallèle (pas requis, mais conseillé).
3. Téléverser ce dossier dans `wp-content/plugins/ag-business-avocat/`.
4. Activer le plugin depuis Extensions.
5. Auto-détection via `AG_Licence_Client::get_tier()` — actif uniquement si tier === business.

## Règles de développement

- **Préfixe CSS obligatoire** : toutes les classes commencent par `ag-business-*`.
- **Aucun override** des classes Free ou Premium.
- **Hooks** : utiliser ceux exposés par Free (`do_action('ag_after_domaines')`, etc.). Ne jamais éditer les templates Free ni les classes Premium.
- **Body class** : `ag-business-active` est ajoutée automatiquement quand le plugin tourne.

## Structure

```
ag-business-avocat/
├── ag-business-avocat.php           ← bootstrap
├── inc/
│   └── class-ag-business-avocat.php ← classe principale (singleton)
├── assets/
│   ├── business.css                 ← styles Business
│   └── business.js                  ← scripts Business
└── README.md
```
