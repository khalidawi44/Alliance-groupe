# AG Business Barber

Pack Business du thème **AG Starter Barber** (WordPress).

## Inspiration

Design vintage / industriel à la Back Alive Barbershop (US), retravaillé en français pour barbershops haut de gamme :
- Palette dark charcoal + accents **rouge sang** + or vintage
- Typographie display industrielle (Bebas Neue uppercase XL + Cormorant italic pour les sous-titres)
- Textures grain + bordures or fines
- Ambiance edgy mais chic

## Sections ajoutées (vs Free)

Le plugin injecte dynamiquement (via JS, pas de modif du Free verrouillé) :

1. **Notre équipe** — barbiers avec photo, spécialité, années d'expérience, IG handle
2. **Galerie** — grille masonry de coupes / dégradés / barbes
3. **Témoignages** — cards d'avis clients style vintage
4. **Réservation** — calendrier date + créneau + service + barbier
5. **Contact + horaires** — adresse, téléphone, IG, plages d'ouverture

## Activation

Le pack ne s'active QUE si la licence est de tier `business` (vérifié via `AG_Licence_Client::get_tier()`).

Le body reçoit les classes :
- `ag-business-active`
- `ag-bb-active`

## Convention CSS

Toutes les classes de ce plugin sont préfixées **`ag-bb-*`** (= ag-business-barber). Aucune classe Free n'est surchargée — uniquement de nouvelles classes ou des sélecteurs combinés `body.ag-business-active.ag-bb-active ...`.

## Version

`0.1.0` — premier scaffold (sections injectées + style industriel).
