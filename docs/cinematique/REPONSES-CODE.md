# Réponses — session CODE → session VISUELS

## Feu vert : les 8 vignettes de l'atelier IA
Fabrice a validé (19/08). **Refais les 8 vignettes** de l'atelier IA en palette or `#d4b45c`→`#f4d06f` sur noir `#05050a`, **une idée réelle par carte** (pas de circuit doré générique) :
1. **Devis instantané** — un devis/chiffre qui s'écrit à l'encre d'or sur parchemin noir.
2. **Refais mon site** — un site qui se reconstruit (blocs de marbre qui s'assemblent).
3. **Fait par l'IA** — une main de marbre qui trace une ligne d'or (écho main-poussiere-or).
4. **Studio créatif** — un plateau de tournage / clap doré.
5. **Création de sites** — une façade Renaissance napolitaine stylisée en or.
6. **Audit de sécurité** — un **bouclier de marbre** veiné d'or.
7. **(7e carte actuelle)** — décline dans le même esprit.
8. **(8e carte actuelle)** — idem.

Format : carré, ~1200², fond noir profond, sujet centré, lisible en vignette ~320 px.
Dépose-les dans **`alliance-groupe-theme/assets/images/atelier/`** (noms clairs :
`atelier-devis.jpg`, `atelier-refonte.jpg`, `atelier-ia.jpg`, `atelier-studio.jpg`,
`atelier-creation.jpg`, `atelier-securite.jpg`, …) + pousse sur `main`.
Dès qu'elles sont là, je les branche sur `template-parts/atelier-gallery` (avec le survol
qui révèle la description + filtres FLIP).

## En cours côté code
Chantier cinématique démarré : **socle (Lenis + GSAP/ScrollTrigger self-hostés)** +
**hero « approche de la main »** + **dissolution poussière d'or** (canvas), portés depuis
`alliance-demo.html`. Livraison du hero d'abord.
