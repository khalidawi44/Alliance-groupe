# Suivi des ventes & commissions — Équipe Alliance Groupe

Chaque commercial (les fils et neveux) touche **10 % du montant de chaque vente**.

Ce dossier permet de suivre les ventes et de calculer automatiquement les
commissions. Deux façons de l'utiliser — choisissez celle qui vous arrange.

---

## Option A — Avec le Manager Prospection (le plus simple)

Demandez au manager (l'agent) :
- « J'ai vendu un site à 1500 € à M. Dupont » → il enregistre la vente et vous
  donne votre commission (150 €) + votre total.
- « Montre mes stats du mois » / « Classement de l'équipe » / « Combien de
  commission je touche ce mois ? » → il calcule tout depuis `ventes.csv`.

Après avoir ajouté des ventes, **sauvegardez** (commit/push) le fichier
`equipe/ventes.csv` pour ne pas les perdre.

---

## Option B — Dans un tableur (Google Sheets ou Excel)

1. Ouvrez Google Sheets → Fichier → Importer → déposez `equipe/ventes.csv`.
2. Colonnes : `Date | Commercial | Client | Activité | Montant EUR | Statut | Commission 10% EUR`.
3. Supprimez les 2 lignes « EXEMPLE ».
4. Collez la **formule de commission** dans la colonne G (Commission), à partir
   de la ligne 2 :

   ```
   =E2*0,1
   ```
   (10 % du montant. Étirez la formule vers le bas.)

### Tableau de bord automatique (à mettre dans un 2e onglet)

- **Total vendu (ventes encaissées seulement) :**
  ```
  =SOMME.SI(Ventes!F:F;"Vendu";Ventes!E:E)
  ```
- **Total des commissions à verser (10 %, ventes encaissées) :**
  ```
  =SOMME.SI(Ventes!F:F;"Vendu";Ventes!G:G)
  ```
- **Total vendu par commercial** (remplacez `"Prénom"`) :
  ```
  =SOMMEPROD((Ventes!B:B="Prénom")*(Ventes!F:F="Vendu")*Ventes!E:E)
  ```
- **Commission due à un commercial** (10 %, ventes encaissées) :
  ```
  =SOMMEPROD((Ventes!B:B="Prénom")*(Ventes!F:F="Vendu")*Ventes!G:G)
  ```
- **Nombre de ventes d'un commercial :**
  ```
  =NB.SI.ENS(Ventes!B:B;"Prénom";Ventes!F:F;"Vendu")
  ```
- **Ventes du mois en cours :**
  ```
  =SOMMEPROD((TEXTE(Ventes!A:A;"AAAA-MM")="2026-05")*(Ventes!F:F="Vendu")*Ventes!E:E)
  ```

> Sur Excel anglais, remplacez `SOMME.SI`→`SUMIF`, `SOMMEPROD`→`SUMPRODUCT`,
> `NB.SI.ENS`→`COUNTIFS`, `TEXTE`→`TEXT`, et la virgule décimale `0,1`→`0.1`.

---

## Règles de commission

- **10 %** du montant de chaque vente, pour le commercial qui l'a réalisée.
- La commission compte quand la vente est **encaissée** (statut `Vendu`).
  Les `Devis` sont « en cours » (pas encore de commission due).
- Montant **HT** par défaut (à confirmer avec Fabrizio si besoin).
- Une vente = une ligne. Ne modifiez pas une ligne existante sans raison.

## Colonnes — rappel
| Colonne | Exemple | Note |
|---|---|---|
| Date | 2026-05-20 | format AAAA-MM-JJ (pour les filtres par mois) |
| Commercial | Karim | qui a vendu (= qui touche les 10 %) |
| Client | Restaurant Chez Marco | le client |
| Activité | Création site web | quel service vendu |
| Montant EUR | 1500 | montant de la vente |
| Statut | Vendu / Devis / Perdu | seuls les « Vendu » comptent pour la commission |
| Commission 10% EUR | 150 | = Montant × 0,10 |
