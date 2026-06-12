# CHASSE GOOGLE PLACES — Avocats Loire-Atlantique (semaine type)

> À utiliser dans **Prospection → Chasse** (Google Places New, `ag_places_key`).
> Méthode : 1 requête = 1 mot-clé + 1 ville. On balaie ville par ville, on laisse
> le **score « probabilité d'achat »** trier, puis on assigne 1 propriétaire par
> fiche (anti-doublon global déjà en place).
>
> Rappel déontologie (cf. `avocat.md` / `avocat-scripts.md`) : on retient en
> priorité les cabinets **sans vrai site**, **site non-HTTPS**, **formulaire
> douteux** ou **note < 4** → ce sont les plus convertibles. Approche **email +
> courrier uniquement**, jamais d'appel/SMS à froid.

---

## 1. Mots-clés à passer (du plus large au plus ciblé)

**Génériques (à passer en premier sur chaque ville) :**
- `avocat`
- `cabinet d'avocats`
- `avocats associés`

**Par spécialité — priorité « le plus à perdre » (données sensibles = meilleur angle sécurité/RGPD) :**
1. `avocat droit des affaires`  ← gros tickets, données entreprises
2. `avocat droit du travail`  /  `avocat droit social`
3. `avocat droit de la famille`  /  `avocat divorce`
4. `avocat droit pénal`
5. `avocat immobilier`
6. `avocat droit des sociétés`
7. `notaire` (cible voisine : mêmes enjeux secret pro + RGPD)

> Ordre conseillé par ville : les 3 génériques, puis spécialités 1→4 (les 4 à plus
> fort enjeu data). Les 5→7 en 2e passe si tu veux élargir.

---

## 2. Zones à balayer (ordre de priorité)

| # | Ville | Pourquoi |
|---|-------|----------|
| 1 | **Nantes** | plus gros vivier ; passer TOUS les mots-clés |
| 2 | **Saint-Herblain** | densité cabinets + zones d'affaires |
| 3 | **Rezé** | sud Loire, cabinets de proximité |
| 4 | **Saint-Nazaire** | 2e pôle du département, moins chassé |
| 5 | **Vertou** | périurbain aisé |
| 6 | **Carquefou** | zones d'activités, cabinets d'affaires |
| 7 | Orvault · Bouguenais · Couëron · La Baule | 2e cercle si besoin de volume |

> Pour Nantes (grosse ville), tu peux affiner par quartier si l'outil le permet :
> `avocat Nantes centre`, `avocat Nantes Decré`, `avocat Nantes Graslin`,
> `avocat Nantes île de Nantes`.

---

## 3. Plan de la semaine (objectif : ~50-100 fiches qualifiées au CRM)

- **Lundi — Nantes (bloc 1)** : 3 génériques + spé 1-2. Viser 20-30 fiches.
- **Mardi — Nantes (bloc 2)** : spé 3-4 + quartiers. Viser 15-20 fiches.
- **Mercredi — Saint-Herblain + Rezé** : 3 génériques + spé 1-3 chacune. ~20 fiches.
- **Jeudi — Saint-Nazaire** : 3 génériques + spé 1-4. ~15 fiches.
- **Vendredi — Vertou + Carquefou** : 3 génériques + spé 1-2. ~10-15 fiches.
- **Tri** (en continu) : garder en haut les scores élevés ; marquer « ne plus
  contacter » les cabinets déjà très équipés (site récent HTTPS + > 50 avis).

> Filtre de qualification rapide sur chaque fiche :
> - **Pas de vrai site** (juste Facebook/page jaune) → prioritaire (création + sécu).
> - **Site non-HTTPS / vieux** → prioritaire (audit sécu = porte d'entrée).
> - **Note < 4 ou < 10 avis** → levier « visibilité ».
> - **Formulaire de contact présent** → angle RGPD (où vont les données ?).

---

## 4. Enchaînement avec le reste du dispositif

1. **Chasse** (ci-dessus) → fiches au CRM, 1 propriétaire/fiche.
2. **Espace Audit** : lance un **scan passif** (non-intrusif) sur le site de chaque
   cabinet retenu → le site **alimente maintenant automatiquement le CRM** (nouveau,
   v1.1.13) avec le segment `audit-securite` / `audit-creation` et les failles.
3. **Email 1 « audit sécurité »** (cf. `avocat-scripts.md`) avec 3 points vrais
   tirés du scan. Relance email/courrier à J+3 / J+14.
4. **RDV** → Pack Cabinet Sérénité (ancrage haut) → closing maintenance (MRR).

---

## 5. À NE PAS faire (déontologie / légal)
- ❌ Pas d'appel téléphonique à froid, pas de SMS de sollicitation aux avocats.
- ❌ Pas de scan intrusif / pentest sans **mandat écrit signé**.
- ✅ Email B2B (intérêt légitime + opt-out) et courrier : OK.
