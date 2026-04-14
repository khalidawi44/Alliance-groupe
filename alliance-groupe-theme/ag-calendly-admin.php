<?php
/**
 * Alliance Groupe — Calendly admin page
 *
 * Multi-tier admin screen under Réglages so the user can paste
 * each Calendly event URL (free discovery + 3 paid tiers) without
 * editing code. Values are read by templates/page-rdv.php to power
 * the booking grid.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'AG_CALENDLY_ADMIN_LOADED' ) ) {
	return;
}
define( 'AG_CALENDLY_ADMIN_LOADED', true );

/**
 * Default Calendly base URL used as a fallback when a wp_option is
 * empty. The site owner can override each tier from Réglages >
 * Calendly AG without touching code.
 */
if ( ! defined( 'AG_CALENDLY_DEFAULT_URL' ) ) {
	define( 'AG_CALENDLY_DEFAULT_URL', 'https://calendly.com/advise-alliance-group/30min' );
}

/**
 * Tier definitions — single source of truth used by both the admin
 * form and the public /rendez-vous template.
 *
 * @return array[] key => label, option_name, default_url, price, duration, description
 */
function ag_calendly_tiers() {
	return array(
		'free' => array(
			'label'       => 'Appel découverte',
			'option'      => 'ag_calendly_url',          // legacy name, keeps existing value
			'default'     => AG_CALENDLY_DEFAULT_URL,
			'price'       => 'Gratuit',
			'duration'    => '30 min',
			'tagline'     => 'Discussion ouverte, sans engagement',
			'description' => 'Pour faire connaissance et voir si on peut travailler ensemble. Aucun livrable.',
		),
		'flash' => array(
			'label'       => 'Audit Flash',
			'option'      => 'ag_calendly_url_flash',
			'default'     => AG_CALENDLY_DEFAULT_URL,
			'price'       => '89 €',
			'duration'    => '45 min',
			'tagline'     => 'Diagnostic express',
			'description' => 'Appel + résumé écrit d\'1 page avec 3 actions prioritaires livrées sous 24h.',
		),
		'strategique' => array(
			'label'       => 'Audit Stratégique',
			'option'      => 'ag_calendly_url_strategique',
			'default'     => AG_CALENDLY_DEFAULT_URL,
			'price'       => '179 €',
			'duration'    => '1h15',
			'tagline'     => 'Le plus choisi',
			'description' => 'Appel + plan d\'action écrit de 3 pages + roadmap 30/60/90 jours chiffrée.',
		),
		'deep' => array(
			'label'       => 'Deep Dive Digital',
			'option'      => 'ag_calendly_url_deep',
			'default'     => AG_CALENDLY_DEFAULT_URL,
			'price'       => '390 €',
			'duration'    => '2h',
			'tagline'     => 'Audit approfondi',
			'description' => 'Appel long + audit détaillé 8-10 pages + priorisation chiffrée + 1 appel de suivi à J+30 offert.',
		),
	);
}

/**
 * Register the submenu under Réglages.
 */
add_action( 'admin_menu', function () {
	add_options_page(
		'Configuration Calendly AG',
		'Calendly AG',
		'manage_options',
		'ag-calendly-config',
		'ag_calendly_admin_render'
	);
} );

/**
 * Register all tier options via the Settings API.
 */
add_action( 'admin_init', function () {
	foreach ( ag_calendly_tiers() as $tier ) {
		register_setting(
			'ag_calendly_config',
			$tier['option'],
			array(
				'type'              => 'string',
				'sanitize_callback' => 'ag_calendly_sanitize_url',
				'default'           => $tier['default'],
				'show_in_rest'      => false,
			)
		);
	}
} );

/**
 * Sanitize a Calendly URL. Accepts only https://calendly.com/...
 * Empty string is valid (clean reset to default).
 */
function ag_calendly_sanitize_url( $value ) {
	$value = trim( (string) $value );
	if ( '' === $value ) {
		return '';
	}
	$url = esc_url_raw( $value );
	if ( ! $url ) {
		add_settings_error( 'ag_calendly_config', 'bad_url', 'URL invalide ignorée : ' . esc_html( $value ) );
		return '';
	}
	$host = wp_parse_url( $url, PHP_URL_HOST );
	if ( 'calendly.com' !== $host ) {
		add_settings_error(
			'ag_calendly_config',
			'bad_host',
			sprintf( 'Hôte rejeté (%s). Seul calendly.com est accepté.', esc_html( $host ) )
		);
		return '';
	}
	return $url;
}

/**
 * Helper used by templates to get the free-discovery Calendly URL
 * (kept for backwards compat with page-rdv.php and /contact CTA).
 */
if ( ! function_exists( 'ag_get_calendly_url' ) ) {
	function ag_get_calendly_url() {
		return ag_get_calendly_tier_url( 'free' );
	}
}

/**
 * Helper used by templates to get any tier URL. Falls back to
 * AG_CALENDLY_DEFAULT_URL if the option is empty.
 *
 * @param string $tier_key free|flash|strategique|deep
 * @return string
 */
function ag_get_calendly_tier_url( $tier_key ) {
	$tiers = ag_calendly_tiers();
	if ( ! isset( $tiers[ $tier_key ] ) ) {
		return AG_CALENDLY_DEFAULT_URL;
	}
	$tier = $tiers[ $tier_key ];
	$url  = get_option( $tier['option'], $tier['default'] );
	if ( ! is_string( $url ) || '' === trim( $url ) ) {
		return AG_CALENDLY_DEFAULT_URL;
	}
	return trim( $url );
}

/**
 * Render the admin page.
 */
function ag_calendly_admin_render() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	?>
	<div class="wrap">
		<h1>Configuration Calendly AG</h1>
		<p style="font-size:.95rem;color:#50575e;max-width:820px;">
			Collez l'URL Calendly de chaque offre proposée sur la page <em>/rendez-vous</em>.
			Les 3 offres payantes nécessitent d'avoir créé préalablement des <em>Paid event types</em>
			dans Calendly (intégration Stripe native). Les champs laissés vides retombent
			automatiquement sur l'URL par défaut du thème.
		</p>

		<div style="max-width:820px;margin-top:16px;padding:18px 20px;background:#fff;border:1px solid #ccd0d4;border-left:4px solid #D4B45C;">
			<strong>Comment créer un Calendly payant ?</strong>
			<ol style="margin:8px 0 0 22px;">
				<li>Dashboard Calendly → <em>Event Types</em> → <em>+ Create</em> → choisissez <strong>One-on-One</strong>.</li>
				<li>Dans les réglages de l'événement, onglet <em>Payment</em>, activez le paiement via <strong>Stripe</strong> et renseignez le montant (ex. 89 € / 179 € / 390 €).</li>
				<li>Fixez la durée exacte (45 min, 1h15, 2h selon l'offre).</li>
				<li>Dans l'onglet <em>What event is this?</em>, donnez un nom clair ("Audit Flash — 89 €").</li>
				<li>Sauvegardez puis copiez l'URL publique et collez-la dans le champ correspondant ci-dessous.</li>
			</ol>
		</div>

		<form method="post" action="options.php" style="max-width:820px;margin-top:24px;">
			<?php settings_fields( 'ag_calendly_config' ); ?>

			<table class="form-table" role="presentation">
				<?php foreach ( ag_calendly_tiers() as $key => $tier ) :
					$stored_url    = get_option( $tier['option'], $tier['default'] );
					$effective_url = ag_get_calendly_tier_url( $key );
					$is_default    = ( $effective_url === $tier['default'] );
				?>
				<tr>
					<th scope="row">
						<label for="<?php echo esc_attr( $tier['option'] ); ?>">
							<?php echo esc_html( $tier['label'] ); ?>
							<br>
							<small style="font-weight:400;color:#666;">
								<?php echo esc_html( $tier['duration'] ); ?> · <?php echo esc_html( $tier['price'] ); ?>
							</small>
						</label>
					</th>
					<td>
						<input type="url" name="<?php echo esc_attr( $tier['option'] ); ?>"
							id="<?php echo esc_attr( $tier['option'] ); ?>"
							value="<?php echo esc_attr( $stored_url ); ?>"
							class="regular-text code"
							placeholder="<?php echo esc_attr( $tier['default'] ); ?>"
							style="width:100%;max-width:620px;">
						<p class="description">
							<?php if ( $is_default ) : ?>
								<span style="display:inline-block;padding:3px 10px;background:#fff8c2;color:#8a6d00;border:1px solid #e8d676;border-radius:12px;font-size:.8rem;font-weight:700;">
									⚠ URL par défaut — à remplacer par votre vrai event <?php echo esc_html( $tier['label'] ); ?>
								</span>
							<?php else : ?>
								<span style="display:inline-block;padding:3px 10px;background:#e7f5e9;color:#1a7a33;border:1px solid #a7d8b3;border-radius:12px;font-size:.8rem;font-weight:700;">
									✓ Calendly configuré
								</span>
							<?php endif; ?>
							<br><em style="color:#666;"><?php echo esc_html( $tier['description'] ); ?></em>
						</p>
					</td>
				</tr>
				<?php endforeach; ?>
			</table>

			<?php submit_button( 'Enregistrer les URLs Calendly' ); ?>
		</form>

		<div style="max-width:820px;margin-top:24px;padding:18px 20px;background:#f0f6fc;border:1px solid #c3dffb;border-radius:6px;">
			<strong>Prévisualisation :</strong>
			<p style="margin:8px 0 0;">
				Ouvrez <a href="<?php echo esc_url( home_url( '/rendez-vous' ) ); ?>" target="_blank" rel="noopener">
					<?php echo esc_html( home_url( '/rendez-vous' ) ); ?>
				</a> pour voir la grille et les widgets en action.
			</p>
		</div>
	</div>
	<?php
}
