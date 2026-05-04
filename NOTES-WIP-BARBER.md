# NOTES — État du Barber Business (snapshot avant pause)

**Date** : 2026-05-02
**Pourquoi** : pause pour créer un template militant (LFI). À reprendre après.

## Versions actuelles

- `ag-starter-barber` v1.0.0 (Free, locked)
- `ag-premium-barber` v0.12.0 (en focus)
- `ag-business-barber` v0.7.0 (en focus)

## Dernier état visuel

Inspiration : **Back Alive Barbershop (Dubai)** — palette dark + bleu électrique #1A8BFF + glow, typo Bebas Neue + Cormorant.

### Acquis (ce qui marche)

- Smart-header transparent → dark on scroll + auto-hide on scroll-down + reapparait on scroll-up
- Tous les onglets visibles direct dans le nav (Tarifs, File d'attente, Équipe, Galerie, Avis, Réserver, Contact) avec smooth-scroll
- CTA "Prendre un ticket" bouton bleu solide en haut-droite
- Logo personnalisé via Customizer (URL → image médiathèque WP, default `/wp-content/uploads/2026/05/logo.png`)
- Logo header gauche (petit) + logo centré sous le header (gros, position absolute) qui **tourne 360° en boucle 14s**
- Hero `padding-top: 280px` pour laisser place au logo
- Boutons hero (`Prendre ticket` + `Voir tarifs`) en `position: absolute; bottom: 10px`
- Tag "Alliance" caché du hero
- Titre h1 `clamp(1.6rem, 4vw, 3rem)` avec `<em>` qui fait un swing 3D Y-axis 5s
- 5 sections injectées par Business : Équipe, Galerie (12 imgs non-clickables), Témoignages, Réservation calendrier, Contact + horaires
- QR code dans `.ag-queue-status` (api.qrserver.com → `?ag_queue=join`) pour kiosk + TV + alerte phone
- Bouton WhatsApp flottant vert + badge "Premium" cernés bleus en bas-droite
- Footer dark + bleu accent + sparkle line top + nom Bebas glow
- Select `<option>` fix dark theme

### Mode test

Customizer → AG Premium Barber > Activation OU AG Business Barber > Activation : checkbox "Mode test" pour preview sans licence réelle.

## Ce qu'il reste à faire (potentiel)

- Vérifier le rendu final mobile complet
- Tester chaque section injectée (réservation form post, etc.)
- Synchroniser le compteur file d'attente vers borne / TV / alerte phone (mentionné par le user, pas encore implémenté côté backend)
- Possible : retoucher animation logo ou titre selon retour user (mp4 dans ShareX folder pas accessible)
- Locker barber quand tout finalisé : `bash scripts/regenerate-lock.sh ag-starter-barber` etc. + retirer de `.AG_FOCUS`

## Reprise

Quand on revient sur barber : `.AG_FOCUS` contient déjà les 3 slugs barber, donc juste éditer librement.
