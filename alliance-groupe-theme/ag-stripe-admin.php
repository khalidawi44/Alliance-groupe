<?php
/**
 * Alliance Groupe — Stripe Payment Links admin page
 *
 * Provides a minimal "Configuration Stripe AG" screen under the
 * Réglages menu so the user can paste the 3 Stripe Payment Link
 * URLs (Pro / Premium / Business) without editing any code. The
 * values are stored in standard wp_options entries read by
 * templates/page-templates.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'AG_STRIPE_ADMIN_LOADED' ) ) {
	return;
}
define( 'AG_STRIPE_ADMIN_LOADED', true );

/**
 * Register the submenu under Réglages.
 */
add_action( 'admin_menu', function () {
	add_options_page(
		'Configuration Stripe AG',
		'Stripe AG',
		'manage_options',
		'ag-stripe-config',
		'ag_stripe_admin_render'
	);
} );

/**
 * Register the 3 options through the Settings API.
 */
add_action( 'admin_init', function () {
	$fields = array(
		'ag_stripe_pro_url'      => array(
			'label'       => 'Pack Pro — 49€',
			'description' => 'URL du Payment Link Stripe pour le Pack Pro. Ex : https://buy.stripe.com/xxxxxxx',
		),
		'ag_stripe_premium_url'  => array(
			'label'       => 'Pack Premium — 99€',
			'description' => 'URL du Payment Link Stripe pour le Pack Premium.',
		),
		'ag_stripe_business_url' => array(
			'label'       => 'Pack Business — 149€',
			'description' => 'URL du Payment Link Stripe pour le Pack Business.',
		),
	);
	foreach ( $fields as $key => $meta ) {
		register_setting(
			'ag_stripe_config',
			$key,
			array(
				'type'              => 'string',
				'sanitize_callback' => 'ag_stripe_sanitize_url',
				'default'           => 'STRIPE_PLACEHOLDER',
				'show_in_rest'      => false,
			)
		);
	}
} );

/**
 * Sanitize the Stripe URL input : empty string or the placeholder
 * both map back to STRIPE_PLACEHOLDER so the front-end fallback
 * kicks in. Real URLs must start with https://buy.stripe.com/ or
 * https://checkout.stripe.com/ — anything else is rejected.
 *
 * @param string $value Raw value.
 * @return string
 */
function ag_stripe_sanitize_url( $value ) {
	$value = trim( (string) $value );
	if ( '' === $value || 'STRIPE_PLACEHOLDER' === $value ) {
		return 'STRIPE_PLACEHOLDER';
	}
	$url = esc_url_raw( $value );
	if ( ! $url ) {
		add_settings_error( 'ag_stripe_config', 'bad_url', 'URL invalide ignorée : ' . esc_html( $value ) );
		return 'STRIPE_PLACEHOLDER';
	}
	$host = wp_parse_url( $url, PHP_URL_HOST );
	$allowed = array( 'buy.stripe.com', 'checkout.stripe.com' );
	if ( ! in_array( $host, $allowed, true ) ) {
		add_settings_error(
			'ag_stripe_config',
			'bad_host',
			sprintf(
				'Hôte rejeté (%s). Seuls %s et %s sont acceptés.',
				esc_html( $host ),
				$allowed[0],
				$allowed[1]
			)
		);
		return 'STRIPE_PLACEHOLDER';
	}
	return $url;
}

/**
 * Render the admin page.
 */
function ag_stripe_admin_render() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$pro      = get_option( 'ag_stripe_pro_url', 'STRIPE_PLACEHOLDER' );
	$premium  = get_option( 'ag_stripe_premium_url', 'STRIPE_PLACEHOLDER' );
	$business = get_option( 'ag_stripe_business_url', 'STRIPE_PLACEHOLDER' );

	$state_badge = function ( $value ) {
		if ( 'STRIPE_PLACEHOLDER' === $value || '' === $value ) {
			return '<span style="display:inline-block;padding:3px 10px;background:#fff8c2;color:#8a6d00;border:1px solid #e8d676;border-radius:12px;font-size:.8rem;font-weight:700;">⚠ Placeholder (fallback /contact actif)</span>';
		}
		return '<span style="display:inline-block;padding:3px 10px;background:#e7f5e9;color:#1a7a33;border:1px solid #a7d8b3;border-radius:12px;font-size:.8rem;font-weight:700;">✓ Stripe actif</span>';
	};

	?>
	<div class="wrap">
		<h1>Configuration Stripe AG</h1>
		<p style="font-size:.95rem;color:#50575e;max-width:760px;">
			Collez ici les URLs des Payment Links Stripe pour les 3 packs
			<strong>Pro</strong>, <strong>Premium</strong> et <strong>Business</strong>.
			Tant qu'une URL est à <code>STRIPE_PLACEHOLDER</code> (ou vide), le bouton
			correspondant sur la page <em>/templates-wordpress</em> retombe sur le
			formulaire <em>/contact</em> avec le pack en paramètre (le lead est quand
			même capturé).
		</p>

		<div style="max-width:760px;margin-top:16px;padding:18px 20px;background:#fff;border:1px solid #ccd0d4;border-left:4px solid #D4B45C;">
			<strong>Comment créer un Payment Link Stripe ?</strong>
			<ol style="margin:8px 0 0 22px;">
				<li>Connectez-vous à votre <a href="https://dashboard.stripe.com/payment-links" target="_blank" rel="noopener">dashboard Stripe</a>.</li>
				<li>Cliquez sur <em>Payment links</em> &gt; <em>Nouveau lien de paiement</em>.</li>
				<li>Créez un produit (ex. « AG Starter Pro »), tarif unique, montant 49,00&nbsp;€ TTC.</li>
				<li>Copiez le lien <code>https://buy.stripe.com/xxxxxxx</code> puis collez-le ci-dessous.</li>
				<li>Répétez pour Premium (99&nbsp;€) et Business (149&nbsp;€).</li>
			</ol>
		</div>

		<form method="post" action="options.php" style="max-width:760px;margin-top:24px;">
			<?php settings_fields( 'ag_stripe_config' ); ?>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">
						<label for="ag_stripe_pro_url">Pack Pro — 49€</label>
					</th>
					<td>
						<input type="url" name="ag_stripe_pro_url" id="ag_stripe_pro_url"
							value="<?php echo esc_attr( $pro ); ?>"
							class="regular-text code"
							placeholder="https://buy.stripe.com/...">
						<p class="description">
							<?php echo $state_badge( $pro ); // phpcs:ignore ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="ag_stripe_premium_url">Pack Premium — 99€</label>
					</th>
					<td>
						<input type="url" name="ag_stripe_premium_url" id="ag_stripe_premium_url"
							value="<?php echo esc_attr( $premium ); ?>"
							class="regular-text code"
							placeholder="https://buy.stripe.com/...">
						<p class="description">
							<?php echo $state_badge( $premium ); // phpcs:ignore ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="ag_stripe_business_url">Pack Business — 149€</label>
					</th>
					<td>
						<input type="url" name="ag_stripe_business_url" id="ag_stripe_business_url"
							value="<?php echo esc_attr( $business ); ?>"
							class="regular-text code"
							placeholder="https://buy.stripe.com/...">
						<p class="description">
							<?php echo $state_badge( $business ); // phpcs:ignore ?>
						</p>
					</td>
				</tr>
			</table>

			<?php submit_button( 'Enregistrer les URLs Stripe' ); ?>
		</form>

		<div style="max-width:760px;margin-top:24px;padding:18px 20px;background:#f0f6fc;border:1px solid #c3dffb;border-radius:6px;">
			<strong>Astuce :</strong> pour vider une URL et revenir au fallback <em>/contact</em>,
			laissez le champ vide et cliquez sur <em>Enregistrer</em>. Les hôtes acceptés sont
			<code>buy.stripe.com</code> et <code>checkout.stripe.com</code>, toute autre URL est
			rejetée avec un message d'erreur en haut de page.
		</div>

		<hr style="margin:40px 0 24px;border:none;border-top:1px solid #ddd;">

		<h2 style="font-size:1.4rem;">📋 Descriptions produit prêtes à coller dans Stripe</h2>
		<p style="color:#50575e;max-width:780px;">
			Pour chaque Payment Link Stripe, copiez-collez la description correspondante
			dans le champ <em>« Description du produit »</em> de Stripe lors de la création.
			Chaque description rappelle votre offre de site sur-mesure pour faire remonter
			les acheteurs vers le ticket le plus élevé.
		</p>

		<?php
		$thank_you_url    = home_url( '/merci-achat' );
		$contact_url      = home_url( '/contact' );
		$tel              = '+33623526074';

		$products = array(
			'pro' => array(
				'name'  => 'AG Starter Pro — 49€',
				'price' => '49,00 €',
				'desc'  => "Plugin WordPress qui transforme votre theme AG Starter gratuit en theme professionnel.\n\nInclus :\n✓ Animations, transitions, gradients, effets premium\n✓ 10 blocs Gutenberg personnalises (hero, CTA, stats, timeline)\n✓ Customizer avance : 50+ reglages couleurs, typo, espacements\n✓ Polices Google Fonts (Playfair, Manrope)\n✓ Sticky header + menu mobile stylise\n✓ Support email 60 jours\n✓ Documentation video complete\n\nCompatible avec les 3 themes AG Starter : Restaurant, Artisan, Coach.\nPaiement unique, pas d'abonnement, mises a jour gratuites pendant 1 an.\n\n— — —\n\n💎 Vous voulez un site qui genere vraiment des leads ?\nNotre offre de site sur-mesure est faite pour vous. Nos clients font +340% de leads en 3 mois en moyenne.\nReservez votre appel gratuit : " . $contact_url,
				'success' => "Merci pour votre achat ! Vous recevrez votre fichier ZIP par email d'ici quelques minutes.\n\n💎 Vous voulez aller plus loin ? Notre equipe peut creer pour vous un site sur-mesure (a partir de 1 500€) qui genere en moyenne +340% de leads en 3 mois.\n\nPremier appel gratuit, sans engagement : " . $contact_url,
			),
			'premium' => array(
				'name'  => 'AG Starter Premium — 99€',
				'price' => '99,00 €',
				'desc'  => "Plugin WordPress qui ajoute le multi-langue, WooCommerce et toutes les features Pro a votre theme AG Starter gratuit.\n\nInclus :\n✓ Tout ce qui est dans Pro (design, blocs, customizer)\n✓ 🌍 Multi-langue : 6 langues (FR, EN, ES, IT, DE, AR)\n✓ Switcher de langue automatique dans le menu\n✓ Sections premium : temoignages, galerie, pricing\n✓ Integration WooCommerce complete (e-commerce ready)\n✓ Import / export des reglages\n✓ Support prioritaire 12 mois (reponse sous 24h)\n✓ Mises a jour a vie\n✓ Appel de 30 min avec un expert\n\nCompatible avec les 3 themes AG Starter : Restaurant, Artisan, Coach.\nPaiement unique, pas d'abonnement.\n\n— — —\n\n💎 Vous ciblez une clientele exigeante ou plusieurs marches ?\nNotre offre de site sur-mesure va beaucoup plus loin. SEO multi-langue cible, strategie de conversion, +340% de leads en moyenne.\nReservez votre appel gratuit : " . $contact_url,
				'success' => "Merci pour votre achat ! Vous recevrez votre fichier ZIP par email d'ici quelques minutes.\n\n💎 Vous voulez un site sur-mesure pour toucher vraiment votre marche ? Premier appel gratuit avec Fabrizio : " . $contact_url,
			),
			'business' => array(
				'name'  => 'AG Starter Business — 149€',
				'price' => '149,00 €',
				'desc'  => "Pack tout-en-un pour les freelances et petites agences qui veulent un theme AG Starter installe, configure et maintenu par notre equipe pendant 1 an complet.\n\nInclus :\n✓ Tout ce qui est dans Premium (design, multi-langue, WooCommerce)\n✓ 🛠️ Installation assistee par notre equipe (visio 1h)\n✓ Maintenance WordPress 1 an incluse (mises a jour WP + plugins)\n✓ Audit SEO mensuel (3 rapports/an)\n✓ Rapport de performance trimestriel\n✓ Support prioritaire absolu (reponse sous 2h ouvrees)\n✓ White-label complet : retirer le credit AG du footer\n✓ Integration CRM (HubSpot, Pipedrive, Brevo)\n✓ Appel strategique de lancement avec Fabrizio\n\nCompatible avec les 3 themes AG Starter : Restaurant, Artisan, Coach.\nPaiement unique, pas d'abonnement.\n\n— — —\n\n💎 Vous voulez un site totalement sur-mesure plutot qu'un theme amenage ?\nA partir de 1 500€ pour un site complet conçu pour VOTRE marque. +340% de leads en moyenne. Reservez votre appel : " . $contact_url,
				'success' => "Merci pour votre achat du Pack Business ! Notre equipe va vous contacter sous 24h ouvrees pour planifier votre installation assistee et l'appel strategique de lancement avec Fabrizio.\n\nEn attendant, vous pouvez nous joindre directement au " . $tel . " ou repondre a cet email.",
			),
		);
		?>

		<div style="background:#fff8e6;border:1px solid #f5c64d;border-left:4px solid #D4B45C;padding:18px 22px;border-radius:6px;margin-bottom:24px;max-width:780px;">
			<strong>🎯 Page de remerciement à utiliser comme « URL de réussite » dans Stripe :</strong><br>
			<code style="background:#fff;padding:4px 8px;border-radius:3px;display:inline-block;margin-top:6px;font-size:.95rem;">
				<?php echo esc_html( $thank_you_url ); ?>?pack=pro
			</code><br>
			<small style="color:#665;">Remplacez <code>pro</code> par <code>premium</code> ou <code>business</code> selon le pack.</small>
			<p style="margin:10px 0 0;color:#665;font-size:.92rem;">
				Cette page affiche un message de confirmation + un gros call-to-action vers
				votre offre de site sur-mesure. <strong>Important</strong> : créez d'abord la
				page WordPress qui utilise le template <em>« Merci pour votre achat »</em>
				avec le slug <code>merci-achat</code> (Pages → Ajouter → choisir le template).
			</p>
		</div>

		<?php foreach ( $products as $key => $prod ) : ?>
			<div style="max-width:780px;margin-bottom:32px;padding:0;background:#fff;border:1px solid #ccd0d4;border-radius:8px;overflow:hidden;">
				<div style="padding:14px 22px;background:#1d2327;color:#fff;display:flex;justify-content:space-between;align-items:center;">
					<strong style="font-size:1.05rem;"><?php echo esc_html( $prod['name'] ); ?></strong>
					<span style="font-size:.8rem;color:#9aa0a6;text-transform:uppercase;letter-spacing:1px;">Pack <?php echo esc_html( $key ); ?></span>
				</div>

				<div style="padding:18px 22px;border-bottom:1px solid #eee;">
					<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
						<strong style="color:#1d2327;">📝 Description du produit</strong>
						<button type="button" class="button button-small ag-copy-btn" data-target="ag-desc-<?php echo esc_attr( $key ); ?>">📋 Copier</button>
					</div>
					<textarea id="ag-desc-<?php echo esc_attr( $key ); ?>" readonly rows="14" style="width:100%;font-family:Menlo,Consolas,monospace;font-size:.85rem;line-height:1.5;background:#f6f7f7;border:1px solid #ddd;padding:12px;border-radius:4px;resize:vertical;"><?php echo esc_textarea( $prod['desc'] ); ?></textarea>
				</div>

				<div style="padding:18px 22px;border-bottom:1px solid #eee;">
					<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
						<strong style="color:#1d2327;">💌 Message de confirmation après paiement</strong>
						<button type="button" class="button button-small ag-copy-btn" data-target="ag-success-<?php echo esc_attr( $key ); ?>">📋 Copier</button>
					</div>
					<textarea id="ag-success-<?php echo esc_attr( $key ); ?>" readonly rows="6" style="width:100%;font-family:Menlo,Consolas,monospace;font-size:.85rem;line-height:1.5;background:#f6f7f7;border:1px solid #ddd;padding:12px;border-radius:4px;resize:vertical;"><?php echo esc_textarea( $prod['success'] ); ?></textarea>
				</div>

				<div style="padding:14px 22px;background:#f6f7f7;font-size:.88rem;color:#50575e;">
					<strong>Prix Stripe :</strong> <?php echo esc_html( $prod['price'] ); ?> · paiement unique ·
					<strong>Devise :</strong> EUR ·
					<strong>URL de réussite recommandée :</strong>
					<code><?php echo esc_html( $thank_you_url . '?pack=' . $key ); ?></code>
				</div>
			</div>
		<?php endforeach; ?>

		<script>
		(function(){
			document.querySelectorAll('.ag-copy-btn').forEach(function(btn){
				btn.addEventListener('click', function(){
					var ta = document.getElementById(btn.getAttribute('data-target'));
					if (!ta) return;
					ta.select();
					try {
						document.execCommand('copy');
						var original = btn.textContent;
						btn.textContent = '✓ Copié !';
						btn.style.background = '#28a745';
						btn.style.borderColor = '#28a745';
						btn.style.color = '#fff';
						setTimeout(function(){
							btn.textContent = original;
							btn.style.background = '';
							btn.style.borderColor = '';
							btn.style.color = '';
						}, 1500);
					} catch(e) {}
				});
			});
		})();
		</script>
	</div>
	<?php
}
