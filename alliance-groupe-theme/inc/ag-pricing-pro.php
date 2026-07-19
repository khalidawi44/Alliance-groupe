<?php
/**
 * AG — Section TARIFS PREMIUM (noir & or) en shortcode : [ag_pricing_pro]
 *
 * Design guidé par la skill ui-ux-pro-max (pattern « Pricing cards 3 tiers » :
 * offre du milieu mise en avant « le plus choisi », CTA par carte, style dark
 * premium contraste fort + accents or). NON destructif : à poser sur n'importe
 * quelle page via le shortcode, sans toucher aux pages existantes.
 *
 * Données = les VRAIES offres Sites Express (mêmes libellés + mêmes liens PayPal
 * que templates/page-sites-express.php, via les options ag_stripe_express_*_url).
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ag_pricing_pro_data' ) ) {
	function ag_pricing_pro_data() {
		return array(
			'essentiel' => array(
				'nom'   => 'Essentiel',
				'prix'  => '490 €',
				'desc'  => 'Une présence pro, vite en ligne.',
				'url'   => get_option( 'ag_stripe_express_essentiel_url', 'https://www.paypal.com/ncp/payment/YNEZPTYSYR6EU' ),
				'star'  => false,
				'feats' => array( 'Site 1 page (one-page) premium', 'Design sur-mesure à ta marque', 'Optimisé mobile + rapide', 'Formulaire de contact + Google Maps', 'Référencement de base', 'Livré en 5 jours' ),
			),
			'pro' => array(
				'nom'   => 'Pro',
				'prix'  => '890 €',
				'desc'  => 'Le choix des pros qui veulent convertir.',
				'url'   => get_option( 'ag_stripe_express_pro_url', 'https://www.paypal.com/ncp/payment/G6EXAMPLE' ),
				'star'  => true,
				'feats' => array( 'Jusqu\'à 6 pages', 'Design sur-mesure premium', 'SEO optimisé (Google)', 'Blog / actualités', 'Prise de RDV en ligne', 'Connexion réseaux sociaux', 'Livré en 8 jours' ),
			),
			'boutique' => array(
				'nom'   => 'Boutique',
				'prix'  => '1 490 €',
				'desc'  => 'Ta boutique en ligne, prête à vendre.',
				'url'   => get_option( 'ag_stripe_express_boutique_url', 'https://www.paypal.com/ncp/payment/N9DCC5VWTS5LY' ),
				'star'  => false,
				'feats' => array( 'Boutique e-commerce (WooCommerce)', 'Jusqu\'à 30 produits intégrés', 'Paiement en ligne (CB, PayPal)', 'Design premium + SEO', 'Gestion stock & commandes', 'Formation à l\'utilisation', 'Livré en 12 jours' ),
			),
		);
	}
}

if ( ! function_exists( 'ag_pricing_pro_render' ) ) {
	function ag_pricing_pro_render( $atts = array() ) {
		$a = shortcode_atts( array(
			'titre'   => 'Un site pro, à prix fixe',
			'sstitre' => 'Pas de devis, pas d\'attente. Tu choisis, tu paies en ligne, on livre.',
		), $atts, 'ag_pricing_pro' );

		$packs = ag_pricing_pro_data();
		ob_start();
		?>
		<section class="agpp" aria-label="Nos offres Sites Express">
			<div class="agpp__wrap">
				<span class="agpp__eyebrow">⚡ Sites Express</span>
				<h2 class="agpp__title"><?php echo esc_html( $a['titre'] ); ?></h2>
				<p class="agpp__sub"><?php echo esc_html( $a['sstitre'] ); ?></p>

				<div class="agpp__grid">
					<?php foreach ( $packs as $key => $p ) :
						$star = ! empty( $p['star'] ); ?>
						<article class="agpp__card<?php echo $star ? ' agpp__card--star' : ''; ?>">
							<?php if ( $star ) : ?><span class="agpp__badge">★ Le plus choisi</span><?php endif; ?>
							<h3 class="agpp__name"><?php echo esc_html( $p['nom'] ); ?></h3>
							<p class="agpp__desc"><?php echo esc_html( $p['desc'] ); ?></p>
							<div class="agpp__price"><span class="agpp__amount"><?php echo esc_html( $p['prix'] ); ?></span><span class="agpp__once">une fois</span></div>
							<ul class="agpp__feats">
								<?php foreach ( $p['feats'] as $f ) : ?>
									<li><?php echo esc_html( $f ); ?></li>
								<?php endforeach; ?>
							</ul>
							<a class="agpp__cta<?php echo $star ? ' agpp__cta--star' : ''; ?>" href="<?php echo esc_url( $p['url'] ); ?>" rel="nofollow">Choisir <?php echo esc_html( $p['nom'] ); ?> →</a>
						</article>
					<?php endforeach; ?>
				</div>

				<ul class="agpp__trust">
					<li>💳 Payable en <strong>4× sans frais</strong></li>
					<li>🏷️ <strong>Prix fixe</strong>, sans surprise</li>
					<li>🔒 Paiement <strong>100 % sécurisé</strong> (PayPal)</li>
					<li>✅ <strong>Sans rendez-vous</strong></li>
				</ul>
				<p class="agpp__foot">+ Maintenance sérénité dès <strong>29 €/mois</strong> (sécurité, sauvegardes, retouches) — résiliable à tout moment.</p>
			</div>
		</section>
		<style>
		.agpp{--g:#e8c66a;--g2:#f4d98b;--ink:#0b0b10;--card:#14141c;--line:rgba(232,198,106,.22);--tx:#eef0f4;--mut:rgba(238,240,244,.62);
			background:radial-gradient(120% 90% at 50% -10%,#1a1a24 0%,#0b0b10 60%);padding:72px 20px;color:var(--tx);
			font-family:var(--font-sans,'Manrope',system-ui,sans-serif)}
		.agpp__wrap{max-width:1120px;margin:0 auto;text-align:center}
		.agpp__eyebrow{display:inline-block;letter-spacing:.18em;font-size:.78rem;font-weight:800;text-transform:uppercase;
			color:var(--g);border:1px solid var(--line);border-radius:999px;padding:6px 14px;margin-bottom:16px}
		.agpp__title{font-size:clamp(1.7rem,3.6vw,2.7rem);font-weight:800;line-height:1.12;margin:0 0 10px}
		.agpp__sub{color:var(--mut);font-size:1.02rem;max-width:620px;margin:0 auto 40px}
		.agpp__grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;align-items:stretch}
		@media(max-width:860px){.agpp__grid{grid-template-columns:1fr;max-width:420px;margin:0 auto}}
		.agpp__card{position:relative;display:flex;flex-direction:column;text-align:left;background:var(--card);
			border:1px solid rgba(255,255,255,.08);border-radius:20px;padding:26px 22px;transition:transform .25s,border-color .25s,box-shadow .25s}
		.agpp__card:hover{transform:translateY(-6px);border-color:var(--line);box-shadow:0 24px 60px rgba(0,0,0,.5)}
		.agpp__card--star{border-color:var(--g);background:linear-gradient(180deg,#1c1710 0%,#14141c 55%);
			box-shadow:0 0 0 1px var(--g),0 30px 70px rgba(0,0,0,.55);transform:scale(1.03)}
		@media(max-width:860px){.agpp__card--star{transform:none}}
		.agpp__badge{position:absolute;top:-13px;left:50%;transform:translateX(-50%);white-space:nowrap;
			background:linear-gradient(135deg,#f4d98b,#d6a94a);color:#1a1305;font-weight:800;font-size:.76rem;
			padding:5px 14px;border-radius:999px;box-shadow:0 6px 18px rgba(214,169,74,.45)}
		.agpp__name{font-size:1.35rem;font-weight:800;margin:6px 0 4px}
		.agpp__desc{color:var(--mut);font-size:.92rem;margin:0 0 16px;min-height:2.6em}
		.agpp__price{display:flex;align-items:baseline;gap:8px;margin-bottom:18px}
		.agpp__amount{font-size:2.3rem;font-weight:800;color:#fff;letter-spacing:-.02em}
		.agpp__card--star .agpp__amount{color:var(--g2)}
		.agpp__once{color:var(--mut);font-size:.85rem}
		.agpp__feats{list-style:none;margin:0 0 22px;padding:0;display:grid;gap:10px}
		.agpp__feats li{position:relative;padding-left:26px;font-size:.94rem;line-height:1.4;color:#dfe2ea}
		.agpp__feats li::before{content:'✓';position:absolute;left:0;top:0;width:18px;height:18px;color:var(--g);font-weight:800}
		.agpp__cta{margin-top:auto;display:block;text-align:center;text-decoration:none;font-weight:800;font-size:1rem;
			padding:14px 18px;border-radius:12px;border:1px solid var(--line);color:#fff;background:rgba(255,255,255,.05);transition:.2s}
		.agpp__cta:hover{background:rgba(232,198,106,.14);border-color:var(--g)}
		.agpp__cta--star{background:linear-gradient(135deg,#f4d98b,#d6a94a);color:#1a1305 !important;border:0}
		.agpp__cta--star:hover{filter:brightness(1.06);transform:translateY(-1px)}
		.agpp__trust{list-style:none;display:flex;flex-wrap:wrap;justify-content:center;gap:10px 22px;margin:36px 0 0;padding:0}
		.agpp__trust li{color:var(--mut);font-size:.9rem}
		.agpp__trust strong{color:#fff}
		.agpp__foot{color:var(--mut);font-size:.9rem;margin:16px 0 0}
		.agpp__foot strong{color:var(--g2)}
		</style>
		<?php
		return ob_get_clean();
	}
}

add_shortcode( 'ag_pricing_pro', 'ag_pricing_pro_render' );
