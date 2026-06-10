<?php
/**
 * Section "Alliance Groupe en vidéo" — visible en mode premium uniquement.
 *
 * @package AG_Starter_Restaurant
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function ag_restaurant_render_promo_video() {
	$is_premium = class_exists( 'ag_restaurant_Presets' ) && ag_restaurant_Presets::get_active_preset();
	if ( ! $is_premium ) return;

	// jsDelivr CDN (raw.githubusercontent.com sert en octet-stream nosniff = navigateurs refusent)
	$video_url = 'https://cdn.jsdelivr.net/gh/khalidawi44/Alliance-groupe@main/alliance-groupe-theme/assets/videos/promo-alliance-16x9.mp4';
	$cta_url   = 'https://alliancegroupe-inc.com/contact';
	$vid_id    = 'ag-restaurant-promo-' . wp_rand( 1000, 9999 );
	?>
	<section class="ag-restaurant-promo-video">
		<div class="ag-container">
			<div class="ag-restaurant-promo-grid">
				<div class="ag-restaurant-promo-text ag-anim">
					<span class="ag-restaurant-promo-tag">🎬 Solution propulsée par Alliance Groupe</span>
					<h2 class="ag-restaurant-promo-title">Un site restaurant <em>professionnel</em>, déployé en 1 clic.</h2>
					<p class="ag-restaurant-promo-lead">
						Ce template fait partie de la suite Alliance Groupe : 6 templates métier,
						100% gratuits, support inclus. Découvrez la solution complète en 15 secondes.
					</p>
					<a href="<?php echo esc_url( $cta_url ); ?>" class="ag-restaurant-promo-cta" target="_blank" rel="noopener">
						🚀 Découvrir l'agence
					</a>
				</div>
				<div class="ag-restaurant-promo-player ag-anim">
					<video
						id="<?php echo esc_attr( $vid_id ); ?>"
						class="ag-restaurant-promo-vid"
						preload="metadata"
						playsinline
						muted
						loop
						autoplay
					>
						<source src="<?php echo esc_url( $video_url ); ?>" type="video/mp4">
					</video>
					<button
						type="button"
						class="ag-restaurant-promo-mute"
						aria-label="Activer le son"
						data-target="<?php echo esc_attr( $vid_id ); ?>"
					>🔇</button>
				</div>
			</div>
		</div>
		<script>
			(function(){
				var btn=document.querySelector('.ag-restaurant-promo-mute[data-target="<?php echo esc_js( $vid_id ); ?>"]');
				var vid=document.getElementById('<?php echo esc_js( $vid_id ); ?>');
				if(!btn||!vid)return;
				btn.addEventListener('click',function(){vid.muted=!vid.muted;btn.textContent=vid.muted?'🔇':'🔊';if(!vid.muted&&vid.paused)vid.play();});
			})();
		</script>
	</section>
	<?php
}

add_shortcode( 'ag_restaurant_promo_video', function () {
	ob_start();
	ag_restaurant_render_promo_video();
	return ob_get_clean();
} );
