# AG Fidélité Association

Pack **Fidélité** (top tier 99€) pour le thème AG Starter Association.

## Ce que ça apporte (vs Free)

### Pages séparées (créées auto à l'activation)
- `/manifeste/`
- `/combats/` (liste des combats — CPT)
- `/evenements/` (calendrier — CPT)
- `/groupes/` (groupes locaux — CPT)
- `/actu/` (articles WP)
- `/petitions/` (CPT)
- `/signer/` (formulaire pétition principale)
- `/don/` (paliers + reçus fiscaux)
- `/adherer/` (formulaire adhésion + niveaux)
- `/mon-compte/` (espace membre)
- `/mentions/`, `/rgpd/`, `/statuts/`

### Custom Post Types
- **Combats** (thématiques portées)
- **Événements** (marches, AG, débats)
- **Groupes locaux**
- **Pétitions**
- **PV & Comptes-rendus** (privé, accès adhérents)

### Rôles utilisateurs spécifiques association
- **Sympathisant** (compte simple, suit l'actu)
- **Adhérent** (cotisation payée, accès docs internes)
- **Militant** (adhérent actif, peut animer un groupe)
- **Trésorier**, **Secrétaire**, **Président·e**

URLs personnalisées : `/sympathisant/jean-dupont`, `/adherent/...`, `/militant/...`, etc.

### Customizer "Identité de l'association"
- Nom officiel, SIRET, RNA (W + 9 chiffres pour assoc loi 1901)
- Président·e
- IBAN
- Montant cotisation annuelle

### Outils inclus
- Espace membre avec accès conditionnel (PV adhérents only)
- Formulaire d'adhésion multi-niveaux
- Paliers de don avec calcul automatique réduction d'impôt 66%
- Shortcodes : `[ag_fid_combats]`, `[ag_fid_evenements]`, `[ag_fid_groupes]`, `[ag_fid_petitions]`, `[ag_fid_actu]`, `[ag_fid_signer]`, `[ag_fid_don]`, `[ag_fid_adhesion]`, `[ag_fid_compte]`, `[ag_fid_manifeste]`

### Extensions WP recommandées (avec page admin dédiée)
13 extensions catalogées par catégorie :
- **Adhésion** : Paid Memberships Pro
- **Dons** : GiveWP
- **Paiement** : WP Simple Pay (Stripe)
- **Newsletter** : MailPoet
- **Formulaires** : WPForms
- **Événements** : The Events Calendar
- **Multilingue** : Polylang
- **SEO** : Yoast SEO
- **RGPD** : CookieYes
- **Sécurité** : Wordfence
- **Sauvegarde** : UpdraftPlus
- **Carte** : Leaflet Map (OpenStreetMap, sans Google)
- **Vote** : WP-Polls

Accès via *WP Admin > Pack Fidélité*.

## Activation

À l'activation : crée auto les rôles + les 13 pages avec leurs shortcodes pré-remplis. Tu n'as plus qu'à éditer chaque page et remplacer les `[crochets]` par ton contenu réel.

Important : après activation, va dans *Réglages > Permaliens*, clique "Enregistrer les modifications" pour activer les rewrite rules des nouveaux CPTs et URLs `/adherent/...`.

## Convention CSS

Classes préfixées `ag-fid-*`. Utilise les variables CSS du thème parent (`--asso-red`, etc.) pour rester cohérent visuellement.

## Version

`0.1.0` — premier scaffold complet.
