<?php
/**
 * Alliance Groupe — Calendly admin page
 *
 * Single-field admin screen under Réglages so the user can paste
 * their actual Calendly URL without editing any code. The value is
 * read by templates/page-rdv.php and by the contact page CTA.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'AG_CALENDLY_ADMIN_LOADED' ) ) {
	return;
}
define( 'AG_CALENDLY_ADMIN_LOADED', true );

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
 * Register the option via the Settings API.
 */
add_action( 'admin_init', function () {
	register_setting(
		'ag_calendly_config',
		'ag_calendly_url',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'ag_calendly_sanitize_url',
			'default'           => '',
			'show_in_rest'      => false,
		)
	);
} );

/**
 * Sanitize the Calendly URL. Accepts only https://calendly.com/...
 * Empty string is valid (disables the embed cleanly).
 *
 * @param string $value Raw value.
 * @return string
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
 * Helper used by templates to get the current Calendly URL.
 * Returns an empty string if not configured.
 */
if ( ! function_exists( 'ag_get_calendly_url' ) ) {
	function ag_get_calendly_url() {
		$url = get_option( 'ag_calendly_url', '' );
		return is_string( $url ) ? trim( $url ) : '';
	}
}

/**
 * Render the admin page.
 */
function ag_calendly_admin_render() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$calendly_url = ag_get_calendly_url();
	$is_configured = ( '' !== $calendly_url );

	?>
	<div class="wrap">
		<h1>Configuration Calendly AG</h1>
		<p style="font-size:.95rem;color:#50575e;max-width:760px;">
			Collez ici l'URL de votre compte Calendly (ou d'un événement spécifique).
			Cette URL sera utilisée pour afficher le widget de réservation directement
			sur la page <em>/rendez-vous</em> et dans le bouton « Parlons-en » du menu.
		</p>

		<div style="max-width:760px;margin-top:16px;padding:18px 20px;background:#fff;border:1px solid #ccd0d4;border-left:4px solid #D4B45C;">
			<strong>Comment obtenir l'URL de votre Calendly ?</strong>
			<ol style="margin:8px 0 0 22px;">
				<li>Connectez-vous à votre <a href="https://calendly.com/event_types/user/me" target="_blank" rel="noopener">dashboard Calendly</a>.</li>
				<li>Cliquez sur l'événement que vous voulez proposer (ex. <em>« Appel découverte — 30 min »</em>).</li>
				<li>En haut à droite, cliquez sur <em>« Partager »</em> (ou <em>« Copier le lien »</em>).</li>
				<li>Copiez l'URL, généralement de la forme <code>https://calendly.com/votre-compte/appel-decouverte</code>.</li>
				<li>Collez-la ci-dessous et enregistrez.</li>
			</ol>
			<p style="margin:10px 0 0;color:#666;font-size:.9rem;">
				Astuce : vous pouvez aussi coller l'URL de base <code>https://calendly.com/votre-compte</code>
				pour afficher tous vos événements disponibles dans le widget.
			</p>
		</div>

		<form method="post" action="options.php" style="max-width:760px;margin-top:24px;">
			<?php settings_fields( 'ag_calendly_config' ); ?>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">
						<label for="ag_calendly_url">URL Calendly</label>
					</th>
					<td>
						<input type="url" name="ag_calendly_url" id="ag_calendly_url"
							value="<?php echo esc_attr( $calendly_url ); ?>"
							class="regular-text code"
							placeholder="https://calendly.com/votre-compte/appel-decouverte"
							style="width:100%;max-width:620px;">
						<p class="description">
							<?php if ( $is_configured ) : ?>
								<span style="display:inline-block;padding:3px 10px;background:#e7f5e9;color:#1a7a33;border:1px solid #a7d8b3;border-radius:12px;font-size:.8rem;font-weight:700;">✓ Calendly actif</span>
							<?php else : ?>
								<span style="display:inline-block;padding:3px 10px;background:#fff8c2;color:#8a6d00;border:1px solid #e8d676;border-radius:12px;font-size:.8rem;font-weight:700;">⚠ Non configuré — la page /rendez-vous affiche un message de configuration</span>
							<?php endif; ?>
						</p>
					</td>
				</tr>
			</table>

			<?php submit_button( 'Enregistrer l\'URL Calendly' ); ?>
		</form>

		<?php if ( $is_configured ) : ?>
		<div style="max-width:760px;margin-top:24px;padding:18px 20px;background:#f0f6fc;border:1px solid #c3dffb;border-radius:6px;">
			<strong>Prévisualisation :</strong>
			<p style="margin:8px 0 0;">
				Ouvrez <a href="<?php echo esc_url( home_url( '/rendez-vous' ) ); ?>" target="_blank" rel="noopener">
					<?php echo esc_html( home_url( '/rendez-vous' ) ); ?>
				</a> pour voir le widget en action.
			</p>
		</div>
		<?php endif; ?>
	</div>
	<?php
}
