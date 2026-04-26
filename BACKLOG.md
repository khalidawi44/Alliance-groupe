# 📋 BACKLOG — Alliance Groupe

Fichier de backlog pour suivre les chantiers en attente, les décisions
reportées et les idées à reprendre plus tard.

> **Note à Claude** : à chaque réponse importante, glisser un petit
> bloc "📋 En réserve" rappelant les 2-3 items les plus prioritaires
> de ce backlog pour éviter qu'ils soient oubliés.

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
