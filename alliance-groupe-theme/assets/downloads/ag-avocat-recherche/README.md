# ⚖️ AG Recherche Juridique (Avocat)

Outil de **recherche et d'analyse juridique** pour le cabinet, intégré au tableau de bord WordPress du thème avocat. Plugin **autonome et séparé** des plugins avocat verrouillés (Free / Premium / Business) — il s'active indépendamment et ne touche à aucun fichier verrouillé.

## Ce que l'avocat peut faire

Menu **⚖️ Recherche juridique** dans wp-admin (réservé au cabinet) :

### 🔎 Recherche
- **Jurisprudence en direct** — recherche LIVE dans l'open data officiel **Judilibre** (Cour de cassation + cours d'appel). Résultats réels : juridiction, chambre, n°, date, thèmes, sommaire, solution, accès au **texte intégral**. *Gratuit* (clé PISTE).
- **Toutes les sources** — un méta-moteur : une question → des liens pré-remplis vers **Légifrance** (judiciaire, administratif, codes, JO), **Cour de cassation**, **Conseil d'État (ArianeWeb)**, **Conseil constitutionnel**, **CEDH (HUDOC)**, **CJUE (CURIA)**, **EUR-Lex**, **Doctrine.fr**, **Dalloz**, **BOFiP**, **Service-public**, **Pappers/BODACC** (vérifier un adversaire), etc.
- **Aide à l'analyse (IA)** — optionnel : formuler le problème de droit, anticiper les **arguments adverses + la parade**, résumer une décision, bâtir une stratégie.

### 📁 Mes dossiers
Salle d'analyse structurée par affaire : *Faits · Problème de droit · Textes applicables · Jurisprudence favorable · Arguments adverses · Parade/contre-arguments · Stratégie · Pièces*. Avec **vue imprimable / export PDF**.

### 🗂️ Banque d'arguments
Bibliothèque de moyens et contre-arguments réutilisables, à constituer au fil des dossiers.

## Installation
1. Installer/activer le plugin (zip ou copie du dossier dans `wp-content/plugins/`).
2. **Réglages → ⚙️ Réglages** : coller les identifiants PISTE (recherche live) et, en option, une clé IA Anthropic.

## Activer la recherche LIVE (gratuite)
1. Compte sur [piste.gouv.fr](https://piste.gouv.fr).
2. Créer une application, s'abonner à l'API **Judilibre**.
3. Coller **Client ID** + **Client Secret** dans les Réglages.

## Déontologie
Outil d'**aide** à la recherche et à l'analyse. Il ne donne pas de consultation et ne remplace pas le travail de l'avocat, qui **vérifie toujours les sources primaires**. Les clés API restent stockées sur le site et ne servent qu'aux appels directs aux API officielles.
