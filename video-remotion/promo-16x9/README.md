# Promo Alliance 16:9 (Remotion)

Source de `alliance-groupe-theme/assets/videos/promo-alliance-16x9.mp4` (1920×1080, ~16 s).
Animation d'entrée **déterministe** (pas de spring qui saute), zoom Ken Burns clampé.

## Re-render
1. `npm i @remotion/cli remotion react react-dom` dans un projet, copier `src/` + mettre les images dans `public/` (naples-1.jpg, naples-night.jpg = baie_naples_nuit.jpg, nantes-3.jpg).
2. `npx remotion render AG-Promo-16x9 out/promo.mp4 --crf=26`
3. Remplacer le mp4 dans `assets/videos/`.

Éditer le texte = `src/script.ts`.
