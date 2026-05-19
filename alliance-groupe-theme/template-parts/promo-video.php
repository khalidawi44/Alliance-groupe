<?php
/**
 * Template part : section "Promo Alliance Groupe en vidéo".
 *
 * Lecteur HTML5 stylé (player custom, autoplay muted, controls visibles
 * au hover, ratio 16:9 responsive, lazy load via preload="metadata").
 *
 * Usage :
 *   - Shortcode `[ag_promo_video]` dans une page Gutenberg
 *   - Inclusion directe : get_template_part( 'template-parts/promo-video' );
 *
 * Args optionnels via $args (dispo seulement en get_template_part) :
 *   - title       string  Titre au-dessus (défaut : "Découvrez Alliance Groupe")
 *   - lead        string  Texte sous le titre
 *   - cta_label   string  Label du bouton CTA (défaut : "Demander un devis")
 *   - cta_url     string  URL du bouton CTA (défaut : /contact/)
 *   - poster      string  Image poster avant lecture (URL)
 *   - video_url   string  Override URL vidéo (défaut : raw GitHub)
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$ag_video_args = isset( $args ) && is_array( $args ) ? $args : array();

$ag_video_title  = ! empty( $ag_video_args['title'] )
	? $ag_video_args['title']
	: __( 'Découvrez Alliance Groupe', 'alliance-groupe' );

$ag_video_lead   = ! empty( $ag_video_args['lead'] )
	? $ag_video_args['lead']
	: __( 'En 15 secondes, comprenez comment nos templates WordPress gratuits transforment votre présence en ligne.', 'alliance-groupe' );

$ag_video_cta_label = ! empty( $ag_video_args['cta_label'] )
	? $ag_video_args['cta_label']
	: __( 'Demander un devis', 'alliance-groupe' );

$ag_video_cta_url   = ! empty( $ag_video_args['cta_url'] )
	? $ag_video_args['cta_url']
	: home_url( '/contact/' );

$ag_video_poster    = ! empty( $ag_video_args['poster'] )
	? $ag_video_args['poster']
	: '';

$ag_video_url       = ! empty( $ag_video_args['video_url'] )
	? $ag_video_args['video_url']
	: 'https://raw.githubusercontent.com/khalidawi44/Alliance-groupe/main/alliance-groupe-theme/assets/videos/promo-alliance-16x9.mp4';

$ag_video_id = 'ag-promo-video-' . wp_rand( 1000, 9999 );
?>

<section class="ag-promo-video-section" aria-labelledby="<?php echo esc_attr( $ag_video_id ); ?>-title">
	<div class="ag-promo-video-inner">
		<div class="ag-promo-video-text">
			<span class="ag-promo-video-tag">🎬 <?php esc_html_e( 'En vidéo', 'alliance-groupe' ); ?></span>
			<h2 id="<?php echo esc_attr( $ag_video_id ); ?>-title" class="ag-promo-video-title">
				<?php echo esc_html( $ag_video_title ); ?>
			</h2>
			<p class="ag-promo-video-lead"><?php echo esc_html( $ag_video_lead ); ?></p>
			<?php if ( $ag_video_cta_label && $ag_video_cta_url ) : ?>
				<a href="<?php echo esc_url( $ag_video_cta_url ); ?>" class="ag-promo-video-cta">
					<?php echo esc_html( $ag_video_cta_label ); ?>
				</a>
			<?php endif; ?>
		</div>

		<div class="ag-promo-video-player">
			<video
				id="<?php echo esc_attr( $ag_video_id ); ?>"
				class="ag-promo-video"
				preload="metadata"
				playsinline
				muted
				loop
				autoplay
				<?php if ( $ag_video_poster ) : ?>poster="<?php echo esc_url( $ag_video_poster ); ?>"<?php endif; ?>
			>
				<source src="<?php echo esc_url( $ag_video_url ); ?>" type="video/mp4">
				<?php esc_html_e( 'Votre navigateur ne supporte pas la lecture vidéo HTML5.', 'alliance-groupe' ); ?>
			</video>
			<button
				type="button"
				class="ag-promo-video-mute-toggle"
				aria-label="<?php esc_attr_e( 'Activer le son', 'alliance-groupe' ); ?>"
				data-target="<?php echo esc_attr( $ag_video_id ); ?>"
			>🔇</button>
		</div>
	</div>

	<style>
		.ag-promo-video-section{padding:80px 24px;background:linear-gradient(135deg,#0a0a0f 0%,#1a1a2e 100%);color:#fff;position:relative;overflow:hidden}
		.ag-promo-video-section::before{content:'';position:absolute;inset:0;background:radial-gradient(circle at 75% 30%,rgba(243,122,31,.18) 0%,transparent 60%);pointer-events:none}
		.ag-promo-video-inner{max-width:1200px;margin:0 auto;display:grid;grid-template-columns:1fr 1.3fr;gap:60px;align-items:center;position:relative;z-index:1}
		@media (max-width:900px){.ag-promo-video-inner{grid-template-columns:1fr;gap:32px}}
		.ag-promo-video-tag{display:inline-block;padding:6px 14px;background:rgba(243,122,31,.18);border:1px solid rgba(243,122,31,.4);border-radius:999px;color:#F37A1F;font-size:.85rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;margin-bottom:18px}
		.ag-promo-video-title{font-family:Georgia,'Playfair Display',serif;font-size:2.4rem;font-weight:800;line-height:1.15;margin:0 0 16px;color:#fff}
		.ag-promo-video-lead{font-size:1.08rem;line-height:1.65;color:rgba(255,255,255,.8);margin:0 0 28px}
		.ag-promo-video-cta{display:inline-block;background:linear-gradient(135deg,#F37A1F 0%,#FF8C42 100%);color:#fff;font-weight:700;padding:14px 32px;border-radius:8px;text-decoration:none;font-size:1rem;box-shadow:0 8px 24px rgba(243,122,31,.4);transition:transform .2s,box-shadow .2s}
		.ag-promo-video-cta:hover{transform:translateY(-2px);box-shadow:0 12px 32px rgba(243,122,31,.55);color:#fff}
		.ag-promo-video-player{position:relative;border-radius:16px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.5),0 0 0 1px rgba(243,122,31,.2)}
		.ag-promo-video{display:block;width:100%;height:auto;aspect-ratio:16/9;background:#000}
		.ag-promo-video-mute-toggle{position:absolute;bottom:16px;right:16px;background:rgba(0,0,0,.6);border:1px solid rgba(255,255,255,.2);color:#fff;width:48px;height:48px;border-radius:50%;font-size:1.3rem;cursor:pointer;backdrop-filter:blur(8px);transition:transform .2s,background .2s}
		.ag-promo-video-mute-toggle:hover{transform:scale(1.08);background:rgba(243,122,31,.8)}
	</style>

	<script>
		(function(){
			var btn = document.querySelector('.ag-promo-video-mute-toggle[data-target="<?php echo esc_js( $ag_video_id ); ?>"]');
			var vid = document.getElementById('<?php echo esc_js( $ag_video_id ); ?>');
			if(!btn||!vid)return;
			btn.addEventListener('click', function(){
				vid.muted = !vid.muted;
				btn.textContent = vid.muted ? '🔇' : '🔊';
				btn.setAttribute('aria-label', vid.muted ? 'Activer le son' : 'Couper le son');
				if(!vid.muted && vid.paused) vid.play();
			});
		})();
	</script>
</section>
