# AG Premium Avocat

Plugin WordPress qui ajoute les fonctionnalités Premium au thème **AG Starter Avocat** (Free).

## Installation

1. Le thème Free `ag-starter-avocat` doit être activé.
2. Téléverser ce dossier dans `wp-content/plugins/ag-premium-avocat/`.
3. Activer le plugin depuis Extensions.
4. Le plugin s'auto-détecte via `AG_Licence_Client::get_tier()` — actif uniquement si tier ∈ {premium, business}.

## Règles de développement

- **Préfixe CSS obligatoire** : toutes les classes commencent par `ag-premium-*`.
- **Aucun override** des classes Free (`ag-section`, `ag-domaine-card`, `ag-btn`, etc.).
- **Hooks** : utiliser ceux exposés par Free (`do_action('ag_after_domaines')`, `apply_filters('ag_domaine_bg_url', ...)`, etc.). Ne jamais éditer les templates Free.
- **Body class** : `ag-premium-active` est ajoutée automatiquement quand le plugin tourne.

## Structure

```
ag-premium-avocat/
├── ag-premium-avocat.php        ← bootstrap (header de plugin)
├── inc/
│   └── class-ag-premium-avocat.php  ← classe principale (singleton)
├── assets/
│   ├── premium.css              ← styles Premium
│   └── premium.js               ← scripts Premium
└── README.md
```
