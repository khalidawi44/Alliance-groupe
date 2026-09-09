# 📋 BACKLOG — Alliance Groupe

Fichier de backlog pour suivre les chantiers en attente, les décisions
reportées et les idées à reprendre plus tard.

> **Note à Claude** : à chaque réponse importante, glisser un petit
> bloc "📋 En réserve" rappelant les 2-3 items les plus prioritaires
> de ce backlog pour éviter qu'ils soient oubliés.

---

## ⚠️ Priorité haute — La SYNC GitHub ne purge aucun cache de page

**Statut** : ⏸️ À faire. Remonté le 09/09/2026 par la session du site client
**L.A Environnement** (`khalidawi44/la-environnement`), qui utilise une copie
de `ag-github-sync.php`. Fabrice a demandé que la consigne soit déposée ici
plutôt qu'appliquée depuis là-bas.

### Le problème

`AG_GitHub_Sync::sync()` écrit les fichiers du thème et vide l'OPcache, mais
**ne purge aucun cache de page**. Si un cache de page est actif, le site
continue de servir l'ancienne page : le déploiement réussit et reste invisible.

Constaté en vrai sur `elagage-vertou.fr`, qui tourne sur le même moteur :

```
x-litespeed-cache: hit
age: 24180                       -> page générée 6 h 43 plus tôt
cache-control: max-age=604800    -> valable 7 jours
```

Le `style.css` était bien servi dans sa nouvelle version, mais **aucun** des
correctifs PHP n'était dans le HTML rendu. Conséquence la plus vicieuse : les
fichiers statiques (CSS, JS, images) sont servis directement et se mettent à
jour tout de suite, alors que le HTML reste figé — le site peut donc tourner en
**état mixte**, CSS neuf sur HTML ancien, et produire des défauts d'affichage
qui n'existent dans aucune des deux versions prises séparément.

### Pourquoi AG n'est pas touché aujourd'hui

Vérifié sur `alliancegroupe-inc.com` le 09/09 :

```
platform: hostinger
x-hcdn-cache-status: DYNAMIC     -> le CDN ne met pas la page en cache
(aucun en-tête x-litespeed-cache)
```

**Le cache de page LiteSpeed n'est pas actif sur AG.** Le site échappe au
problème par circonstance, pas par conception. Le jour où ce cache est activé
— volontairement, ou par un réglage Hostinger lors d'une migration — tous les
déploiements deviennent invisibles jusqu'à 7 jours, **sans aucun message
d'erreur**. C'est une panne silencieuse : la SYNC affichera « OK, N fichiers
mis à jour » et le site ne bougera pas.

Note au passage : `alliance-groupe/deploy/deploy.sh` est périmé, il vise
o2switch alors que le site est sur Hostinger.

### À faire

1. **Porter `purge_caches()`** dans `alliance-groupe-theme/inc/ag-github-sync.php`.
   Implémentation de référence, déjà écrite et testée, dans le dépôt **public**
   `khalidawi44/la-environnement` → `la-environnement-theme/inc/lae-github-sync.php`
   (chercher `purge_caches`). Elle purge LiteSpeed via l'action documentée
   `litespeed_purge_all` (no-op inoffensif sans le plugin), le cache objet via
   `wp_cache_flush()`, et les règles de réécriture. Un filtre
   `lae_github_sync_purge` permet d'y brancher un CDN.
2. **L'appeler sur les DEUX chemins de sync** — incrémental *et* repli tarball
   — et seulement quand au moins un fichier a changé.
3. **Corriger le libellé du bouton** « 🧹 Purger tous les caches » dans
   `alliance-groupe-theme/ag-import.php`. Il promet plus qu'il ne fait : c'est un
   `DELETE` SQL sur les transients (licences, companion, MAJ plugins/thèmes), il
   **ne purge aucun cache de page**. Quelqu'un qui clique en attendant que la
   page se rafraîchisse sera induit en erreur. Soit le renommer
   (« Purger les transients licences et MAJ »), soit lui ajouter la vraie purge.
4. Bumper la version du thème, comme d'habitude.

### Comment vérifier que c'est réparé

Ne pas se fier au message de la SYNC. Après un déploiement, lire le **HTML
réellement servi** et y chercher une chaîne introduite par le commit :

```bash
curl -sSI https://alliancegroupe-inc.com/ | grep -iE "x-litespeed|^age:"
curl -sS  https://alliancegroupe-inc.com/ | grep -c "UNE_CHAINE_DU_NOUVEAU_CODE"
```

Un `age:` élevé ou un `x-litespeed-cache: hit` signifie que la page servie est
ancienne, quoi qu'affiche l'écran d'administration.

---

## 🔥 Priorité haute — Fidélisation / Email-first

**Statut** : ⏸️ En réserve, validé en principe mais pas encore implémenté.

**Contexte** : le client a demandé un système de compte classique pour
que les utilisateurs retrouvent leurs infos. Après analyse, une
stratégie **email-first + magic link + séquence de nurturing** est
bien plus efficace pour son business (ventes sur-mesure à 1 500€+
via appel commercial, pas SaaS récurrent).

**Plan validé** :

1. **Modal de téléchargement modifié** — au lieu d'un download direct,
   le visiteur reçoit le ZIP par email avec un lien magique permanent
   `/mes-telechargements?token=xxxxx`.
2. **Page `/mes-telechargements?token=xxx`** — dashboard sans login
   qui liste tous les téléchargements passés de cet email. Le token
   est un hash unique généré au premier téléchargement.
3. **Stockage** — custom table `wp_ag_leads` avec colonnes `email`,
   `name`, `phone`, `token`, `downloads` (JSON), `created_at`,
   `last_seen`. Ou user_meta si on crée un user WP silencieux.
4. **Séquence email automatique sur 30 jours** (Brevo / Sendinblue
   gratuit jusqu'à 300 emails/jour) :
   - J0 : Bienvenue + lien download + téléphone direct Fabrizio
   - J2 : Guide "Les 5 erreurs des débutants WordPress"
   - J7 : Étude de cas L.A Environnement (+320% devis)
   - J14 : **CTA sur-mesure** "30 min gratuites avec Fabrizio"
   - J21 : Tutorial vidéo installation du template
   - J30 : **Upsell Pro/Premium** avec -20%
   - J60 : Réactivation + étude de cas restaurateur
5. **Segmentation par template téléchargé** :
   - Restaurant → séquence avec cas resto
   - Artisan → séquence avec cas artisan
   - Coach → séquence avec cas coach
6. **Outils nécessaires** :
   - Compte Brevo (gratuit jusqu'à 300 emails/j)
   - Plugin Fluent SMTP (gratuit) pour l'envoi via API
   - Template des 7 emails rédigés

**Effort estimé** : 1 journée de code + rédaction des 7 emails
+ création des séquences dans Brevo.

**ROI attendu** : 2–3 leads qualifiés pour 100 téléchargements
(contre 0 aujourd'hui).

**Décision prise** : option validée par le client, mise en réserve
le temps de stabiliser la page /templates-wordpress. À ressortir
dès que le client le demande ou quand il a fini de s'occuper de
la soumission wordpress.org.

---

## 🔧 Priorité moyenne — Stripe Payment Links à créer

**Statut** : ⏸️ Placeholders en place, le client doit créer les
Payment Links dans son dashboard Stripe.

Les 2 boutons Pack Premium / Business pointent actuellement
vers des URLs placeholder dans `templates/page-templates.php`.
Quand le client a créé les 2 Payment Links dans son dashboard
Stripe, il doit remplacer les placeholders ou utiliser les
options WordPress (`ag_stripe_premium_url`,
`ag_stripe_business_url`).

URLs type Stripe : `https://buy.stripe.com/xxxxxxx`.

---

## 🛠️ Priorité moyenne — wordpress.org submission

**Statut** : ⏸️ Préparation faite, soumission manuelle à faire par
le client.

Les 4 thèmes (restaurant, artisan, coach, avocat) + le plugin compagnon
sont prêts à être soumis à wordpress.org. Guide complet dans
`alliance-groupe-theme/assets/downloads/WORDPRESS-ORG-SUBMISSION.md`.

Actions restantes côté client :
1. Créer compte wordpress.org avec username `adminag` / display
   name `AGthèmes`.
2. Remplacer les 4 `screenshot.png` placeholder (1200×900)
   générés automatiquement par de vraies captures d'écran des
   thèmes installés.
3. Installer + lancer le plugin **Theme Check** sur chaque thème
   en local.
4. Upload sur https://wordpress.org/themes/upload/ (un thème par
   un thème).
5. Upload du plugin sur https://wordpress.org/plugins/developers/add/.
6. Attendre la review (2–8 semaines pour les thèmes, 1–4 pour le
   plugin).

---

## 💡 Priorité basse — Idées futures

- **Version multi-langue** : les traductions `.pot` / `.po` / `.mo`
  pour FR, EN, ES, IT, DE, AR — argument principal du pack Premium.
  Pour l'instant juste promis sur la landing page.
- **Plugins Premium et Business** : réellement coder les 2 plugins payants
  une fois qu'il y a les premiers acheteurs (ne pas construire en
  l'air). Aujourd'hui les boutons Stripe vendent une promesse —
  on devra livrer le code après le premier achat.
- **Intégration WooCommerce** complète (promise dans Business).
- **Real screenshots** pour les 4 thèmes, une fois testés en local.
- **Blog marketing** sur le site principal pour SEO long terme
  (articles : "Comment choisir un thème WordPress en 2026", "5
  erreurs des débutants", "Template gratuit vs site sur-mesure",
  etc.).

---

## 📝 Historique des reports

- **2026-04-12** — Plan de fidélisation email-first validé, mis en
  réserve.
- **2026-04-12** — Business pack 149€ restauré (avait été retiré lors
  du passage à 2 niveaux).
- **2026-04-12** — Stripe Payment Links placeholders en place, à
  remplacer côté client.
