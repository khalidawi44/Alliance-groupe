# Remplacer la photo figée par la scène animée

Fichier : `alliance-groupe-theme/templates/page-accueil-cinema.php`

## 1. Le HTML — section `.tab`

**Avant** (tel qu'il est aujourd'hui) :

```php
<div class="tab__img" data-tabimg><img src="<?php echo esc_url( $dir . '/assets/images/cinematique/allegorie-naples.jpg' ); ?>" alt="Allégorie de l'alliance devant la baie de Naples"></div>
```

**Après** :

```php
<div class="tab__img" data-tabimg>
  <img src="<?php echo esc_url( $dir . '/assets/images/cinematique/allegorie-naples.jpg' ); ?>" alt="Allégorie de l'alliance devant la baie de Naples">
  <video class="tab__vid" autoplay muted loop playsinline preload="metadata"
         poster="<?php echo esc_url( $dir . '/assets/videos/allegorie-naples-poster.jpg' ); ?>">
    <source src="<?php echo esc_url( $dir . '/assets/videos/allegorie-naples-anim.mp4' ); ?>" type="video/mp4">
  </video>
</div>
```

C'est exactement le motif que ton hero utilise déjà pour `hero-egerie-court.mp4` :
l'image reste dessous, la vidéo se pose par-dessus. Si la vidéo ne charge pas —
connexion lente, navigateur ancien, économiseur de données — le tableau d'origine
s'affiche et personne ne voit la différence.

## 2. Le CSS — à ajouter juste après la règle `.tab__img img`

```css
.tab__img .tab__vid{position:absolute;inset:0;width:100%;height:100%;
  object-fit:cover;opacity:0;transition:opacity .9s ease}
.tab__img .tab__vid[data-ok]{opacity:1}
@media (prefers-reduced-motion:reduce){.tab__img .tab__vid{display:none}}
```

L'opacité à 0 au départ, puis le fondu quand la vidéo est réellement prête :
sans ça, on voit une image noire pendant le chargement.

## 3. Le JS — deux ajouts

**a)** Révéler la vidéo quand elle peut jouer. À placer près des autres
initialisations :

```js
document.querySelectorAll(".tab__vid").forEach(function(v){
  if (matchMedia("(prefers-reduced-motion:reduce)").matches) { v.remove(); return; }
  v.addEventListener("canplay", function(){ v.setAttribute("data-ok",""); }, { once:true });
  v.play().catch(function(){});   // iOS refuse parfois : l'image reste, tant pis
});
```

**b)** Le Ken Burns GSAP actuel cible `[data-tabimg] img` — il ne touchera donc
pas la vidéo, et les deux couches se désaligneront au scroll. Élargis la cible :

```js
/* tableau : Ken Burns lent */
if (!matchMedia("(max-width:960px)").matches)
  G.fromTo("[data-tabimg] img, [data-tabimg] video",
    { scale:1.02, yPercent:-3 },
    { scale:1.16, yPercent:3, ease:"none",
      scrollTrigger:{ trigger:".tab", start:"top top", end:"bottom bottom", scrub:true }});
```

Le zoom au scroll et la parallaxe de la scène se cumulent alors : la caméra
bouge dans le tableau pendant que le tableau lui-même se rapproche.

## 4. Où déposer les fichiers rendus

```
blender/rendus/allegorie-naples-anim.mp4     →  alliance-groupe-theme/assets/videos/
blender/rendus/allegorie-naples-poster.jpg   →  alliance-groupe-theme/assets/videos/
```

## Poids — à surveiller

Une boucle de 8 s en 1080p H.264 pèse en général **2 à 5 Mo**. Au-delà de 6 Mo,
baisse `constant_rate_factor` sur `"MEDIUM"` dans le script, ou passe la sortie
en 1280×720 : la vidéo est floutée par le voile `.tab__veil` de toute façon,
personne ne verra la différence.
