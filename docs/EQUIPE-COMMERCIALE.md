# ÉQUIPE COMMERCIALE AUTONOME — Alliance Groupe

> Document vivant. Plan de l'organisation commerciale automatisée : les agents, leurs
> rôles, et surtout **la boucle qui fait apprendre l'équipe**. On le fait évoluer au fil
> des semaines (demande de Fabrice : l'analyste doit être **actif tout le temps**).

---

## 1. Le principe : la boucle d'apprentissage

Une grappe de robots qui envoient ≠ une équipe qui s'améliore. Ce qui fait la différence,
c'est la **boucle de rétroaction** :

```
   Terrain  →  Données  →  Analyste  →  Architecte  →  Manager  →  Terrain
 (les agents)  (journal)    (Léa)     (stratégie)   (réglages)   (ça recommence)
```

- **Terrain** : les agents opérationnels travaillent (chasse, envoi, relance, réponses).
- **Données** : chaque action laisse une trace datée et segmentée (journal du tunnel).
- **Analyste** : lit les traces, calcule *où ça convertit et où ça fuit*.
- **Architecte** : décide *quoi changer* (angle, niche, plafond, canal).
- **Manager** : applique les réglages aux agents.

Sans cette boucle, on envoie toujours de la même façon sans jamais savoir si un autre
message convertirait mieux.

---

## 2. Le tunnel (les « stats d'entrée »)

| Étape | Clé | Comment c'est capté | État |
|---|---|---|---|
| Prospect repéré | `sourced` | hook `ag_prospect_added` (chasse/import) | ✅ |
| Mail envoyé | `sent` | `do_action('ag_funnel_event','sent')` dans Hugo | ✅ |
| Intéressé | `interested` | hook `ag_prospect_status_changed` (statut `interesse`) | ✅ |
| Client / Refus | `client` / `refused` | idem (statuts `client`, `refus`, `ne_pas_contacter`) | ✅ |
| Contrat signé | `signed` | `do_action('ag_funnel_event','signed')` dans la signature | ✅ |
| Payé | `paid` | hook `ag_paypal_payment_verified` | ✅ |
| Ouverture / clic | — | pixel + liens tracés | ⏳ à instrumenter |

**Code : `inc/ag-funnel.php`** — journal borné (`ag_funnel_events`, 6000 derniers),
segmenté par **métier** et **ville**. Écran : **Prospection → 📈 Tunnel & Analyste**.

---

## 3. La couche « cerveau » (pilotage)

| Rôle | Nom | Entrée | Sortie | État |
|---|---|---|---|---|
| **Mémoire** | 🗂️ | `CLAUDE.md`, `INFRASTRUCTURE.md`, modules, `.json` | fiche de capacités : ce qui existe / est possible | ✅ skill `ag-memoire` |
| **Analyste** | Léa 📊 | le journal du tunnel | taux par étape + par segment, pépites & fuites, **orientation** | ✅ **actif 24/7** (1×/jour, poussé Telegram/SMS) |
| **Architecte** | 🧭 | l'orientation de Léa + tendances marché | stratégie : niche, angle, plafond, canal | 🧠 agent Claude (comité hebdo à armer) |
| **Manager** | 🎯 | la stratégie | réglages concrets sur les agents | 🧠 agent Claude |
| **Contrôleur de gestion** | Victor 💶 | CA, coût/canal (quota Places, SMS) | ROI/segment, CAC, « ce segment coûte plus qu'il ne rapporte » | ⏳ à bâtir |

**L'analyste tourne déjà tout seul** : `ag_funnel_cron_maybe()` est appelé par le pilote
automatique (`ag_auto_tick`) → une fois par jour il calcule, stocke l'orientation
(`ag_funnel_orientation`) et la pousse via `ag_push()`.

---

## 4. Les agents « intelligents » (à venir)

Ils donnent de la matière à l'analyste et musclent la boucle :

- **L'Expérimentateur — Max 🧪** *(priorité haute)* : fait tourner des **variantes** (objet,
  angle, heure) et mesure laquelle gagne. Sans variation, rien à analyser. C'est le moteur.
- **Le Scoreur prédictif 🔮** : apprend des signatures passées *qui signe*, re-priorise la file.
- **Le Veilleur marché — Inès 📰** : repère les **fenêtres de tir** réglementaires/techniques
  (facturation électronique, accessibilité, vieux CMS) → secteurs qui *doivent* bouger.
- **Le Gardien de réputation 🛡️** : surveille bounces/spam/opt-out, **freine Hugo** si ça se
  dégrade → protège le 9,5/10 de délivrabilité. Indispensable dès qu'on monte le volume.
- **Le Déontologue — Me Aubert ⚖️** : Bloctel, lien de désinscription, **avocats = email/courrier
  only**, **aucun scan actif** (art. 323-1), mentions légales. Bloque *avant* l'envoi.
- **Le Qualifieur (SDR) 🤝** : sur une réponse « intéressé », qualifie budget/besoin/délai.

---

## 5. Les agents opérationnels (terrain)

Déjà en place (crons PHP, 24/7) :

- **Le Chasseur** — `ag_prospect_cron` (Google Places, score d'achat). 1×/h via le pilote.
- **L'Enrichisseur** — `ag_enrich_cron` (trouve emails/infos).
- **Hugo le Démarcheur** — `ag_closer_cron` (email, angles création/refonte/sécurité, cap/jour).
- **Le Relanceur** — `ag_relance_cron` + relance-chaud `ag_rc_cron`.
- **Alessia la Boîte** — `ag_boite_cron` (lit les réponses en IMAP).
- **Le Coach** — `ag_coach_daily` (feuille de route poussée).

À activer / construire :

- **SMS/WhatsApp** — code prêt (`ag_sms_send*`), **en pause** (attend une SIM physique dédiée).
- **Robot vocal IA** — fixes 02/04 (Yelda/Synthflow ou Vapi/Retell+Twilio), webhook → CRM.

---

## 6. Le rythme : deux vitesses

- **Temps réel (crons PHP, 24/7)** : terrain + journal + analyste quotidien + garde-fous.
  Tourne tout seul sur le serveur, déclenché par cron-job.org toutes les 15 min.
- **Comité de direction (hebdo, cerveau Claude)** : une session programmée réveille
  l'architecte + le manager qui relisent la semaine, décident, et appliquent de nouveaux
  réglages. *À armer* (Routine hebdomadaire).

---

## 7. Feuille de route

1. ✅ **Fondation** : journal du tunnel + analyste quotidien + tableau de bord *(fait 06/10)*.
2. ⏳ **Max l'Expérimentateur** : A/B sur l'objet et l'angle des mails.
3. ⏳ **Gardien de réputation** : frein auto sur bounces/spam.
4. ⏳ **Contrôleur de gestion** : CA/coût/ROI par segment.
5. ⏳ **Comité hebdo Claude** : architecte + manager automatiques.
6. ⏳ **Ouverture/clic** : pixel + liens tracés (fin le tunnel).

---

## 9. Les skills de l'équipe (permanents, versionnés)

Chaque acteur a son **skill** dans `.claude/skills/` (invocable par `/<nom>`), sur le
modèle de la team avocat : **l'architecte dirige et tranche seul**, la Mémoire tourne
avant toute décision, au moins un agent est lancé à chaque message commercial.

- Chef : **`equipe-commerciale`** (carte + règle de déclenchement + routing)
- Mémoire : `ag-memoire`
- Cerveau : `ag-analyste`, `ag-architecte`, `ag-manager`, `ag-controleur`
- Intelligents : `ag-experimentateur`, `ag-scoreur`, `ag-veilleur`, `ag-gardien-reputation`, `ag-deontologue`, `ag-qualifieur`
- Terrain : `ag-chasseur`, `ag-enrichisseur`, `ag-demarcheur`, `ag-relanceur`, `ag-boite`, `ag-coach`

## 8. Garde-fous permanents (non négociables)

- **Jamais de scan actif** d'un site tiers (art. 323-1 CP) — uniquement signaux publics passifs.
- **Avocats** : email/courrier uniquement, jamais SMS/appel à froid (RIN).
- **Opt-out** présent sur chaque mail ; respect Bloctel ; expéditeur de démarchage dédié.
- **Délivrabilité** : SMTP authentifié `contact@alliancegroupe-inc.com` (9,5/10) ; chauffe du
  domaine ; monter le plafond **progressivement**, jamais multiplier des envois identiques.
- **Jamais inventer** une stat, une preuve, une fonctionnalité : l'analyste ne lit que du réel.
