<?php
/**
 * Mesh gradient — fond cinématographique pour le hero.
 *
 * Version CSS pure (légère) : plusieurs radial-gradients animés en
 * background-position + opacité via keyframes. Remplace l'ancien shader
 * WebGL fbm (4 octaves à 60fps) qui étranglait les vieux PC. Rendu visuel
 * très proche, coût quasi nul (composité GPU), désactivé en reduced-motion.
 *
 * À inclure DANS un container relatif (le hero) :
 *   get_template_part( 'template-parts/mesh-gradient-bg' );
 *
 * Couleurs Alliance par défaut. Override via filter `ag_mesh_gradient_colors`.
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$ag_mesh_colors = apply_filters( 'ag_mesh_gradient_colors', array(
	'#0a0a0f',  // noir Alliance
	'#1a1a2e',  // navy
	'#D4B45C',  // champagne
	'#F37A1F',  // orange Alliance
) );
$ag_c = array_map( 'sanitize_hex_color', $ag_mesh_colors );
$ag_mesh_uid = 'agmesh-' . wp_rand( 1000, 9999 );
?>

<div id="<?php echo esc_attr( $ag_mesh_uid ); ?>" class="ag-mesh-gradient" aria-hidden="true"></div>

<style>
#<?php echo esc_attr( $ag_mesh_uid ); ?>{
	position:absolute; inset:0; width:100%; height:100%;
	z-index:0; pointer-events:none; overflow:hidden;
	background-color:<?php echo esc_html( $ag_c[0] ?: '#0a0a0f' ); ?>;
	background-image:
		radial-gradient(42% 55% at 18% 28%, <?php echo esc_html( $ag_c[3] ?: '#F37A1F' ); ?>33 0%, transparent 60%),
		radial-gradient(46% 60% at 82% 22%, <?php echo esc_html( $ag_c[2] ?: '#D4B45C' ); ?>2b 0%, transparent 62%),
		radial-gradient(55% 65% at 70% 80%, <?php echo esc_html( $ag_c[1] ?: '#1a1a2e' ); ?>cc 0%, transparent 60%),
		radial-gradient(50% 60% at 25% 85%, <?php echo esc_html( $ag_c[3] ?: '#F37A1F' ); ?>22 0%, transparent 58%);
	background-size:200% 200%, 200% 200%, 200% 200%, 200% 200%;
	background-position:0% 0%, 100% 0%, 50% 100%, 0% 100%;
	opacity:.85;
	animation:ag-mesh-drift 26s ease-in-out infinite alternate;
	will-change:background-position;
}
/* voile-grain léger + vignette pour la profondeur (statique, 0 coût d'anim) */
#<?php echo esc_attr( $ag_mesh_uid ); ?>::after{
	content:""; position:absolute; inset:0;
	background:radial-gradient(120% 120% at 50% 45%, transparent 55%, rgba(0,0,0,.45) 100%);
}
@keyframes ag-mesh-drift{
	0%{   background-position:0% 0%,   100% 0%,   50% 100%, 0% 100%; }
	50%{  background-position:30% 20%, 70% 30%,   40% 70%,  20% 80%; }
	100%{ background-position:10% 40%, 90% 10%,   60% 90%,  5% 95%; }
}
@media (prefers-reduced-motion: reduce){
	#<?php echo esc_attr( $ag_mesh_uid ); ?>{ animation:none; }
}
</style>
