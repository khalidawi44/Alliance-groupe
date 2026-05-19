<?php
/**
 * Comparatif visuel des 3 packs Pro / Premium / Business.
 *
 * Tableau de fonctionnalités avec ✓ / ✗ + CTA Stripe checkout.
 * Inclus dans page-templates.php pour structurer l'upsell.
 *
 * @package Alliance_Groupe_Theme
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$ag_packs = array(
	array(
		'slug'    => 'pro',
		'name'    => 'Pack Pro',
		'price'   => 49,
		'tagline' => 'Le minimum pour un site qui en jette',
		'best'    => false,
		'features'=> array(
			'theme_base'      => true,
			'fonts_premium'   => true,
			'animations'      => true,
			'sticky_header'   => true,
			'gutenberg_blocks'=> true,
			'testimonials'    => false,
			'multilang'       => false,
			'woocommerce'     => false,
			'support_12m'     => false,
			'install_visio'   => false,
			'audit_seo'       => false,
			'white_label'     => false,
		),
		'url'     => 'https://alliancegroupe-inc.com/contact?pack=pro',
	),
	array(
		'slug'    => 'premium',
		'name'    => 'Pack Premium',
		'price'   => 99,
		'tagline' => 'Le meilleur rapport qualité/prix',
		'best'    => true,
		'features'=> array(
			'theme_base'      => true,
			'fonts_premium'   => true,
			'animations'      => true,
			'sticky_header'   => true,
			'gutenberg_blocks'=> true,
			'testimonials'    => true,
			'multilang'       => '6 langues',
			'woocommerce'     => true,
			'support_12m'     => true,
			'install_visio'   => false,
			'audit_seo'       => false,
			'white_label'     => false,
		),
		'url'     => 'https://alliancegroupe-inc.com/contact?pack=premium',
	),
	array(
		'slug'    => 'business',
		'name'    => 'Pack Business',
		'price'   => 149,
		'tagline' => 'Pour les pros qui veulent tout, livré clé en main',
		'best'    => false,
		'features'=> array(
			'theme_base'      => true,
			'fonts_premium'   => true,
			'animations'      => true,
			'sticky_header'   => true,
			'gutenberg_blocks'=> true,
			'testimonials'    => true,
			'multilang'       => '6 langues',
			'woocommerce'     => true,
			'support_12m'     => true,
			'install_visio'   => true,
			'audit_seo'       => true,
			'white_label'     => true,
		),
		'url'     => 'https://alliancegroupe-inc.com/contact?pack=business',
	),
);

$ag_pack_features = array(
	'theme_base'       => 'Thème WordPress métier (6 disponibles)',
	'fonts_premium'    => 'Polices premium (Playfair, Manrope)',
	'animations'       => 'Animations scroll-reveal cinéma',
	'sticky_header'    => 'Header sticky qui change au scroll',
	'gutenberg_blocks' => 'Blocs Gutenberg sur-mesure',
	'testimonials'     => 'Section témoignages clients',
	'multilang'        => 'Multi-langue',
	'woocommerce'      => 'Compatible WooCommerce',
	'support_12m'      => 'Support email 12 mois',
	'install_visio'    => 'Installation guidée en visio (1h)',
	'audit_seo'        => 'Audit SEO personnalisé',
	'white_label'      => 'White-label (retrait pub Alliance)',
);
?>

<section class="ag-packs-compare">
	<div class="ag-container">
		<span class="ag-tag">Comparatif</span>
		<h2 class="ag-section__title">3 packs pour <em>booster</em> votre template</h2>
		<p class="ag-section__desc">Le template gratuit fait déjà le job. Les packs ajoutent du carburant — choisissez selon votre niveau d'ambition.</p>

		<div class="ag-packs-grid">
			<?php foreach ( $ag_packs as $pack ) : ?>
				<div class="ag-pack-card <?php echo $pack['best'] ? 'ag-pack-card--best' : ''; ?>">
					<?php if ( $pack['best'] ) : ?>
						<span class="ag-pack-badge">⭐ Le plus choisi</span>
					<?php endif; ?>
					<div class="ag-pack-head">
						<h3 class="ag-pack-name"><?php echo esc_html( $pack['name'] ); ?></h3>
						<div class="ag-pack-price">
							<span class="ag-pack-price-num"><?php echo (int) $pack['price']; ?></span>
							<span class="ag-pack-price-cur">€</span>
							<span class="ag-pack-price-meta">unique</span>
						</div>
						<p class="ag-pack-tagline"><?php echo esc_html( $pack['tagline'] ); ?></p>
					</div>

					<ul class="ag-pack-features">
						<?php foreach ( $ag_pack_features as $key => $label ) :
							$val = $pack['features'][ $key ] ?? false;
							$has = (bool) $val;
							$badge = is_string( $val ) ? $val : '';
						?>
							<li class="ag-pack-feature <?php echo $has ? 'is-included' : 'is-excluded'; ?>">
								<span class="ag-pack-feature-icon"><?php echo $has ? '✓' : '—'; ?></span>
								<span class="ag-pack-feature-label"><?php echo esc_html( $label ); ?></span>
								<?php if ( $badge ) : ?>
									<span class="ag-pack-feature-badge"><?php echo esc_html( $badge ); ?></span>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>

					<a href="<?php echo esc_url( $pack['url'] ); ?>" class="ag-pack-cta">
						<?php echo $pack['best'] ? '🚀 Choisir ce pack' : 'Choisir ' . esc_html( $pack['name'] ); ?>
					</a>
				</div>
			<?php endforeach; ?>
		</div>

		<p class="ag-packs-footer-note">
			💎 Besoin de plus ? <a href="<?php echo esc_url( home_url( '/contact' ) ); ?>">Demandez un site sur-mesure</a>
			— création complète, IA, automatisations, SEO, à partir de 1 500€.
		</p>
	</div>
</section>

<style>
.ag-packs-compare{padding:80px 24px;background:linear-gradient(180deg,#0a0a0f 0%,#14141c 100%);color:#fff}
.ag-packs-compare .ag-tag{display:inline-block;padding:6px 14px;background:rgba(212,180,92,.12);border:1px solid rgba(212,180,92,.4);border-radius:999px;color:#D4B45C;font-size:.78rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;margin-bottom:18px}
.ag-packs-compare .ag-section__title{font-family:Georgia,'Playfair Display',serif;font-size:clamp(2rem,4vw,3.2rem);font-weight:700;line-height:1.15;margin:0 0 12px;color:#fff;text-align:center}
.ag-packs-compare .ag-section__title em{background:linear-gradient(135deg,#D4B45C 0%,#F37A1F 100%);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;color:transparent;font-style:italic}
.ag-packs-compare .ag-section__desc{text-align:center;color:rgba(255,255,255,.7);max-width:680px;margin:0 auto 50px;font-size:1.05rem;line-height:1.6}
.ag-packs-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:20px;max-width:1180px;margin:0 auto}
.ag-pack-card{position:relative;background:linear-gradient(180deg,rgba(20,20,28,.9) 0%,rgba(10,10,15,.95) 100%);border:1px solid rgba(212,180,92,.15);border-radius:18px;padding:36px 28px;display:flex;flex-direction:column;transition:transform .4s cubic-bezier(.16,1,.3,1),border-color .35s ease,box-shadow .35s ease}
.ag-pack-card:hover{transform:translateY(-6px);border-color:rgba(212,180,92,.4);box-shadow:0 30px 80px rgba(0,0,0,.45),0 0 50px rgba(212,180,92,.12)}
.ag-pack-card--best{border-color:rgba(212,180,92,.5);background:linear-gradient(180deg,rgba(30,25,20,.95) 0%,rgba(20,15,10,.98) 100%);box-shadow:0 20px 60px rgba(212,180,92,.18)}
.ag-pack-card--best:hover{box-shadow:0 40px 100px rgba(0,0,0,.5),0 0 80px rgba(212,180,92,.25)}
.ag-pack-badge{position:absolute;top:-14px;left:50%;transform:translateX(-50%);padding:6px 16px;background:linear-gradient(135deg,#D4B45C 0%,#F37A1F 100%);color:#0a0a0f;font-size:.75rem;font-weight:800;letter-spacing:1.5px;text-transform:uppercase;border-radius:999px;box-shadow:0 6px 20px rgba(212,180,92,.4);white-space:nowrap}
.ag-pack-head{text-align:center;padding-bottom:28px;border-bottom:1px solid rgba(212,180,92,.12);margin-bottom:24px}
.ag-pack-name{font-family:Georgia,'Playfair Display',serif;font-size:1.6rem;font-weight:700;color:#fff;margin:0 0 16px}
.ag-pack-price{display:flex;align-items:baseline;justify-content:center;gap:4px;margin-bottom:10px}
.ag-pack-price-num{font-size:3.5rem;font-weight:800;color:#D4B45C;line-height:1;text-shadow:0 0 20px rgba(212,180,92,.3)}
.ag-pack-price-cur{font-size:1.8rem;font-weight:700;color:#D4B45C}
.ag-pack-price-meta{font-size:.85rem;color:rgba(255,255,255,.5);margin-left:6px;letter-spacing:1px;text-transform:uppercase}
.ag-pack-tagline{font-size:.92rem;color:rgba(255,255,255,.75);margin:0;line-height:1.5}
.ag-pack-features{list-style:none;padding:0;margin:0;flex:1}
.ag-pack-feature{display:flex;align-items:center;gap:10px;padding:9px 0;font-size:.92rem;color:rgba(255,255,255,.85);line-height:1.4}
.ag-pack-feature.is-excluded{color:rgba(255,255,255,.3);text-decoration:line-through;text-decoration-color:rgba(255,255,255,.15)}
.ag-pack-feature-icon{display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;border-radius:50%;font-size:.85rem;font-weight:700;flex-shrink:0}
.ag-pack-feature.is-included .ag-pack-feature-icon{background:rgba(34,197,94,.18);color:#22c55e}
.ag-pack-feature.is-excluded .ag-pack-feature-icon{background:rgba(255,255,255,.05);color:rgba(255,255,255,.3)}
.ag-pack-feature-label{flex:1}
.ag-pack-feature-badge{display:inline-block;padding:2px 8px;background:rgba(212,180,92,.15);border:1px solid rgba(212,180,92,.3);border-radius:999px;color:#D4B45C;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:1px}
.ag-pack-cta{display:block;margin-top:28px;padding:16px;background:rgba(255,255,255,.05);border:1px solid rgba(212,180,92,.25);border-radius:10px;color:#D4B45C;text-decoration:none;text-align:center;font-weight:700;letter-spacing:1px;text-transform:uppercase;font-size:.88rem;transition:all .3s cubic-bezier(.16,1,.3,1)}
.ag-pack-cta:hover{background:rgba(212,180,92,.12);border-color:#D4B45C;transform:translateY(-2px);box-shadow:0 8px 24px rgba(212,180,92,.25);color:#D4B45C;text-decoration:none}
.ag-pack-card--best .ag-pack-cta{background:linear-gradient(135deg,#D4B45C 0%,#F37A1F 100%);color:#0a0a0f;border:none;box-shadow:0 8px 24px rgba(212,180,92,.35)}
.ag-pack-card--best .ag-pack-cta:hover{transform:translateY(-3px);box-shadow:0 12px 32px rgba(212,180,92,.55);color:#0a0a0f}
.ag-packs-footer-note{text-align:center;margin:40px auto 0;color:rgba(255,255,255,.6);font-size:.95rem;max-width:600px}
.ag-packs-footer-note a{color:#D4B45C;text-decoration:underline;text-decoration-color:rgba(212,180,92,.3);text-underline-offset:3px}
.ag-packs-footer-note a:hover{text-decoration-color:#D4B45C;color:#F37A1F}
</style>
