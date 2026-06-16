# Activer la recherche LIVE Judilibre (identifiants PISTE) — pas à pas

> Judilibre = l'open data officiel de la jurisprudence (Cour de cassation + cours d'appel),
> diffusé via la plateforme **PISTE** de l'État. C'est **gratuit**. On récupère un
> **Client ID** + **Client Secret** à coller dans le plugin (⚖️ Recherche juridique → ⚙️ Réglages).

## 1. Créer un compte PISTE
1. Aller sur **https://piste.gouv.fr** → « S'inscrire » / « Créer un compte ».
2. Renseigner email pro + mot de passe, valider l'email de confirmation.
3. Se connecter.

## 2. Créer une application
1. Menu **« Applications »** → **« Créer une application »**.
2. Nom : `Alliance Groupe - Recherche juridique` (peu importe).
3. Type d'authentification : **OAuth2 / Client Credentials** (clé + secret).
4. Valider → l'application est créée. Note le **Client ID** et le **Client Secret**
   (le secret ne s'affiche parfois qu'une fois → copie-le tout de suite).

## 3. S'abonner à l'API Judilibre
1. Menu **« API »** (catalogue) → chercher **« Judilibre »**.
2. Choisir **Judilibre** (production — pas seulement le bac à sable « sandbox »,
   sinon les résultats sont fictifs).
3. Cliquer **« S'abonner »** et **rattacher l'abonnement à ton application**
   (celle créée à l'étape 2).
4. L'abonnement Judilibre est gratuit et généralement validé immédiatement.

## 4. Coller les identifiants dans le plugin
1. wp-admin → **⚖️ Recherche juridique → ⚙️ Réglages**.
2. Coller le **Client ID** (PISTE) et le **Client Secret** (PISTE).
3. Enregistrer.
4. Aller dans **🔎 Recherche** → onglet **Jurisprudence en direct** → tester une requête
   (ex. « licenciement sans cause réelle »). Tu dois voir de vraies décisions
   (juridiction, chambre, n°, date, sommaire, accès au texte intégral).

## En cas de souci
- **« Identifiants invalides »** : vérifier qu'il n'y a pas d'espace avant/après en collant,
  et que l'abonnement Judilibre est bien **rattaché à la même application** que la clé.
- **« 0 résultat »** alors que la clé marche : reformuler la requête (mots-clés juridiques).
- **Sandbox vs Production** : si les résultats semblent faux/de test, c'est qu'on est sur
  l'environnement « sandbox » → reprendre l'abonnement sur l'API **de production**.
- Le **méta-moteur « Toutes les sources »** (liens Légifrance, Conseil d'État, CEDH…)
  fonctionne **sans** identifiants — la clé PISTE ne sert qu'à la recherche LIVE intégrée.

## Bon à savoir (déontologie)
Outil d'**aide** à la recherche. L'avocat **vérifie toujours la source primaire**
avant de s'appuyer sur une décision. Les identifiants restent stockés sur le site
et ne servent qu'aux appels directs à l'API officielle.
