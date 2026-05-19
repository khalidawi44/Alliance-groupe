<?php
/**
 * Section "Alliance Groupe en vidéo" — visible en mode premium uniquement.
 *
 * @package AG_Starter_Artisan
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function ag_artisan_render_promo_video() {
	$is_premium = class_exists( 'ag_artisan_Presets' ) && ag_artisan_Presets::get_active_preset();
	if ( ! $is_premium ) return;

	$video_url = 'https://raw.githubusercontent.com/khalidawi44/Alliance-groupe/main/alliance-groupe-theme/assets/videos/promo-alliance-16x9.mp4';
	$cta_url   = 'https://alliancegroupe-inc.com/contact';
	$vid_id    = 'ag-artisan-promo-' . wp_rand( 1000, 9999 );
	?>
	<section class="ag-artisan-promo-video">
		<div class="ag-container">
			<div class="ag-artisan-promo-grid">
				<div class="ag-artisan-promo-text ag-anim">
					<span class="ag-artisan-promo-tag">🎬 Solution propulsée par Alliance Groupe</span>
					<h2 class="ag-artisan-promo-title">Un site artisan <em>professionnel</em>, déployé en 1 clic.</h2>
					<p class="ag-artisan-promo-lead">
						Ce template fait partie de la suite Alliance Groupe : 6 templates métier,
						100% gratuits, support inclus. Découvrez la solution complète en 15 secondes.
					</p>
					<a href="<?php echo esc_url( $cta_url ); ?>" class="ag-artisan-promo-cta" target="_blank" rel="noopener">
						🚀 Découvrir l'agence
					</a>
				</div>
				<div class="ag-artisan-promo-player ag-anim">
					<video
						id="<?php echo esc_attr( $vid_id ); ?>"
						class="ag-artisan-promo-vid"
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
						class="ag-artisan-promo-mute"
						aria-label="Activer le son"
						data-target="<?php echo esc_attr( $vid_id ); ?>"
					>🔇</button>
				</div>
			</div>
		</div>
		<script>
			(function(){
				var btn=document.querySelector('.ag-artisan-promo-mute[data-target="<?php echo esc_js( $vid_id ); ?>"]');
				var vid=document.getElementById('<?php echo esc_js( $vid_id ); ?>');
				if(!btn||!vid)return;
				btn.addEventListener('click',function(){vid.muted=!vid.muted;btn.textContent=vid.muted?'🔇':'🔊';if(!vid.muted&&vid.paused)vid.play();});
			})();
		</script>
	</section>
	<?php
}

add_shortcode( 'ag_artisan_promo_video', function () {
	ob_start();
	ag_artisan_render_promo_video();
	return ob_get_clean();
} );
