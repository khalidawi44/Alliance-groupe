<?php
/**
 * Accueil — Priorité 1 : VENDRE.
 * Section TARIFS PREMIUM (noir & or) affichée via [ag_pricing_pro]
 * (inc/ag-pricing-pro.php) — 3 offres Sites Express, « Pro » mise en avant.
 * Conserve l'effet « remonte » (.ag-rise) sur la section précédente.
 * (Remplace l'ancienne grille de 3 images.)
 */
?>
<section class="ag-rise" id="offres" aria-label="Nos offres Sites Express">
	<?php echo function_exists( 'ag_pricing_pro_render' ) ? ag_pricing_pro_render() : ''; ?>
</section>

<style>
/* effet "remonte" : la section monte sur la precedente, coins arrondis + ombre.
   (.ag-rise est aussi utilisé par home-ambassadeurs, faq… : on le garde ici.) */
.ag-rise{position:relative;z-index:2;margin-top:-58px;border-top-left-radius:44px;border-top-right-radius:44px;box-shadow:0 -50px 90px rgba(0,0,0,.55);overflow:hidden;}
@media(max-width:768px){.ag-rise{margin-top:-38px;border-top-left-radius:30px;border-top-right-radius:30px;}}
</style>
