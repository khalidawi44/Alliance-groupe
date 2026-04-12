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
	</div>
	<?php
}
