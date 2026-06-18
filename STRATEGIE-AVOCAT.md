# STRATÉGIE VENTE — Premium « AG Avocat » (freemium .org → site mère)

> Mémoire de la stratégie de vente du thème avocat. Lu/réutilisable entre sessions.
> Modèle : gratuit sur wordpress.org = aimant ; Premium (69 €) / Business (149 €) vendus sur `alliancegroupe-inc.com`.
> Entonnoir technique déjà en place : thème .org → lien « 🎁 guide gratuit » → page **`/guide-avocat`** (site mère, `inc/ag-lead-avocat.php`) → lead CRM + email du guide.

## Cap (positionnement)
Devenir LE thème WordPress du cabinet d'avocat **francophone**. Vendre le Premium **sur la sécurité des dossiers + la recherche juridique** (Judilibre, espace client chiffré), **PAS sur le design**. Cible prioritaire : cabinets d'affaires / à budget (« le riche »).

## Objectifs 30 / 60 / 90 jours (réalistes, niche FR)
| Indicateur | J30 | J60 | J90 |
|---|---|---|---|
| Installs actifs .org | 40-80 | 150-250 | 400-600 |
| Avis 5★ (note ≥ 4,7) | 8-12 | 20-30 | 35-50 |
| Emails captés (opt-in) | 30-50 | 120-180 | 300-400 |
| Taux free→premium | — | 2-3 % | 3 % |
| Ventes cumulées | 2-4 | 8-14 | 20-30 |
| CA cumulé | 140-280 € | 600-1 400 € | 2 000-3 500 € |

Le vrai jackpot = **Business 149 €** (espace client + Judilibre + maintenance an 1) → porte vers le sur-mesure et le récurrent (MRR).

## 3 leviers prioritaires
1. **Amorcer le flywheel à la main** : pousser les 1ères installs (toi + ambassadeurs), puis **notice d'avis à J+7 dans l'admin** (discrète, dismissible, JAMAIS bloquante — .org interdit de bloquer une fonction contre un avis).
2. **Séquence email 7 messages** (ci-dessous) — c'est TA liste qui vend, pas .org.
3. **SEO sur `/wordpress-avocat`** + 6 articles (RGPD avocat 2026, espace client sécurisé, recherche jurisprudence depuis son site, RIN/CNB & site, cyber cabinets, comparatif gratuit vs premium).

## Rôle des ambassadeurs
Commission 10 % via `?ref=` (6,90 € / Premium, 14,90 € / Business). Mission : installer le free en vitrine, diffuser les vidéos studio (LinkedIn = canal n°1 avocats), déposer des avis HONNÊTES après usage réel, approcher leur réseau avocat **par email/courrier UNIQUEMENT**. Prime 25 € (déjà codée) pour la 1ʳᵉ vente Business d'un filleul.

## Pièges à éviter (NON négociable)
**Déonto avocat (RIN/CNB)** : ❌ jamais SMS/appel/démarchage physique d'un avocat (email/courrier only) → **l'API SMS Free ne sert QUE pour les clients déjà engagés, jamais en prospection avocat** ; ❌ pas de témoignages de cabinets (interdit RIN) ; ❌ pas de pub comparative entre confrères (le comparatif free vs premium de NOTRE produit est OK).
**WordPress.org** : ❌ pas de vente/checkout dans le thème ; ❌ pas de phone-home sans consentement ; ❌ pas d'upsell agressif / nags répétés (un seul lien Premium, dismissible) ; ❌ jamais de faux avis (.org ou Google) = illégal + bannissement.

---

# LA SÉQUENCE EMAIL — 7 MESSAGES (free → premium)

Déclenchée à l'opt-in sur `/guide-avocat`. Destinataire = avocat qui a **demandé** le guide (consentement RGPD). Ton : sobre, factuel, **aucune promesse**, prix mentionné, lien de désinscription à chaque message. Variables : `{{prenom}}`, `{{lien_guide}}`, `{{lien_premium}}`, `{{code}}`, `{{date}}`.

### Message 1 — J0 (immédiat) — VALEUR PURE, zéro vente
**Objet : Votre thème est prêt — les 3 réglages à faire en premier**
> Bonjour {{prenom}},
> Merci d'avoir téléchargé AG Starter Avocat. Avant tout, 3 réglages qui changent tout sur un site de cabinet :
> 1. Renseignez votre **barreau d'inscription** et vos **mentions légales** (obligatoire).
> 2. Activez le **bandeau RGPD / secret professionnel** du pied de page.
> 3. Remplacez les textes entre [crochets] par vos infos (nom, domaines, honoraires).
> Le guide complet « 7 pages qui attirent des clients » : {{lien_guide}}
> Bien à vous, l'équipe Alliance Groupe — [Se désinscrire]

### Message 2 — J+2 — LE RISQUE (sécurité)
**Objet : 38 % des cabinets attaqués en 2025 — où sont vos pièces clients ?**
> Bonjour {{prenom}},
> Un chiffre qui fait réfléchir : **38 % des cabinets d'avocats français ont subi une cyberattaque en 2025**. L'article 66-5 vous oblige à protéger les pièces de vos clients, et la CNIL impose une notification sous **72 h** en cas de fuite.
> Recevoir les pièces par simple email, c'est le maillon faible.
> Le Pack Premium ajoute un **espace client sécurisé** : votre client se connecte et dépose ses documents dans un espace privé (stockage non public), au lieu de pièces jointes éparpillées.
> En savoir plus : {{lien_premium}} — [Se désinscrire]

### Message 3 — J+5 — LE GAIN DE TEMPS (Judilibre)
**Objet : Retrouver une jurisprudence en 2 clics, depuis votre propre site**
> Bonjour {{prenom}},
> Combien de temps passez-vous à chercher une décision sur Légifrance ou Judilibre ?
> Le Pack Premium intègre un **cabinet de recherche juridique** dans votre site : recherche en direct dans **Judilibre** (Cour de cassation + cours d'appel), filtres « font jurisprudence », année, matière — plus une salle d'analyse de dossiers et une banque d'arguments. Outil privé, réservé à vous, depuis votre back-office.
> Voir la démo : {{lien_premium}} — [Se désinscrire]

### Message 4 — J+9 — LEAD MAGNET + soft Business
**Objet : La checklist RGPD + secret professionnel de votre site**
> Bonjour {{prenom}},
> Pour être tranquille côté conformité, voici la **checklist du site d'avocat** (RGPD + art. 66-5) : {{lien_guide}}
> Au programme : hébergement en UE, mentions obligatoires, formulaire conforme, cookies, sécurisation des échanges de pièces.
> Si vous préférez qu'on s'en occupe (audit + mise en conformité + installation en visio), c'est inclus dans le **Pack Business**.
> [Se désinscrire]

### Message 5 — J+14 — COMPARATIF HONNÊTE + CTA achat
**Objet : Gratuit vs Premium : ce que le thème ne fait pas (encore)**
> Bonjour {{prenom}},
> La version gratuite vous donne une vitrine propre. Le Premium en fait un **outil de cabinet** :
> • Gratuit : pages, design navy/champagne, formulaire RDV, RGPD.
> • Premium (**69 €, paiement unique**) : recherche Judilibre, espace client sécurisé, en-tête fixe avec votre téléphone, blocs juridiques, support prioritaire.
> Pas d'abonnement. Mises à jour à vie.
> Passer au Premium : {{lien_premium}} — [Se désinscrire]

### Message 6 — J+21 — OFFRE LIMITÉE (légitime, pas de fausse urgence)
**Objet : -15 € sur le Premium cette semaine**
> Bonjour {{prenom}},
> Si vous hésitiez : cette semaine le **Pack Premium est à 54 € au lieu de 69 €** (code {{code}}), paiement unique.
> Vous gardez votre site gratuit ; le Premium débloque simplement l'espace client sécurisé et la recherche juridique.
> Profiter de l'offre : {{lien_premium}} — Offre valable jusqu'au {{date}}.
> [Se désinscrire]

### Message 7 — J+35 — RÉENGAGEMENT / objection
**Objet : Une question avant de vous laisser tranquille**
> Bonjour {{prenom}},
> Vous utilisez toujours la version gratuite — c'est parfait, elle est faite pour ça.
> Juste une question pour m'améliorer : qu'est-ce qui vous retient de passer au Premium ? Le prix, un doute technique, un besoin précis qui manque ?
> Répondez simplement à cet email, je lis tout.
> Bien à vous, Fabrizio — Alliance Groupe — [Se désinscrire]

---

## À faire (mise en œuvre de la séquence)
- [ ] Charger ces 7 emails dans un autorépondeur (Brevo/Mailchimp/MailPoet) déclenché à l'inscription `/guide-avocat`.
- [ ] Créer le PDF « Checklist RGPD + 66-5 du site d'avocat » (msg 4).
- [ ] Créer un code promo `{{code}}` -15 € côté paiement (msg 6).
- [ ] Notice d'avis J+7 dans le thème .org (dismissible).
- [ ] Articles SEO (×6) + page `/wordpress-avocat` optimisée (title risque+conformité, FAQ schema).

## Sources (datées)
- ILOVEWP — stats thèmes .org 2024 (peu d'installs/avis par thème).
- Elegant Themes 2025 — conversion freemium 1-5 %.
- CNIL / barreaux 2025-2026 — 38 % cabinets attaqués, ransomwares menace n°1, secret pro 66-5, notif CNIL 72 h.
- Lexbase / CNB vade-mecum — sollicitation personnalisée avocat = email/courrier uniquement.
