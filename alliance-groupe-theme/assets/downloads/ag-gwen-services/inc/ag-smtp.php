<?php
/**
 * ENVOI DES E-MAILS — sortie authentifiee (SMTP) — thème Gwen Services.
 *
 * Par defaut, WordPress confie ses messages a la fonction mail() de PHP. Chez la
 * plupart des hebergeurs mutualises (Hostinger compris), cette sortie est bridee :
 * le site croit avoir envoye, le message ne part jamais, et RIEN ne le signale.
 * Consequence directe ici : les invitations de rendez-vous (.ics) et les
 * confirmations de reservation « partent » sans arriver.
 *
 * Le remede : passer par le relais de courrier de l'hebergeur en s'authentifiant.
 * C'est aussi ce relais qui appose la signature DKIM (un message depose par PHP
 * sort non signe meme quand le DNS est parfait).
 *
 * Mot de passe : stocke en base, sauf si defini dans wp-config.php via
 *   define( 'AG_SMTP_PASS', '...' );  — la constante l'emporte.
 *
 * @package ag-gwen-services
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! defined( 'AG_SMTP_VER' ) ) { define( 'AG_SMTP_VER', '1.0.0' ); }

/* ── 1. Reglages ─────────────────────────────────────────────────────── */

if ( ! function_exists( 'ag_smtp_opt' ) ) {
	function ag_smtp_opt( $cle, $defaut = '' ) {
		return trim( (string) get_option( 'ag_smtp_' . $cle, $defaut ) );
	}
}
if ( ! function_exists( 'ag_smtp_on' ) ) {
	function ag_smtp_on() {
		return (bool) get_option( 'ag_smtp_on', 0 ) && '' !== ag_smtp_opt( 'host' );
	}
}
if ( ! function_exists( 'ag_smtp_pass' ) ) {
	function ag_smtp_pass() {
		if ( defined( 'AG_SMTP_PASS' ) && '' !== (string) AG_SMTP_PASS ) { return (string) AG_SMTP_PASS; }
		return (string) get_option( 'ag_smtp_pass', '' );
	}
}

/* ── 2. La sortie ────────────────────────────────────────────────────── */

add_action( 'phpmailer_init', function ( $mail ) {
	if ( ! ag_smtp_on() ) { return; }
	$mail->isSMTP();
	$mail->Host       = ag_smtp_opt( 'host' );
	$mail->Port       = (int) ( ag_smtp_opt( 'port', '465' ) ?: 465 );
	$mail->SMTPAuth   = true;
	$mail->Username   = ag_smtp_opt( 'user' );
	$mail->Password   = ag_smtp_pass();
	$mail->SMTPSecure = ( 'tls' === ag_smtp_opt( 'secu', 'ssl' ) ) ? 'tls' : 'ssl';

	/* L'expediteur DOIT appartenir au domaine authentifie : le relais ne signe
	   (DKIM) et n'aligne (SPF) QUE son propre domaine. On force donc l'expediteur
	   sur le domaine de l'identifiant ; une adresse d'un autre domaine est refusee. */
	$user     = ag_smtp_opt( 'user' );
	$dom_user = strtolower( (string) substr( strrchr( $user, '@' ), 1 ) );
	$exp      = ag_smtp_opt( 'from' ) ?: $user;
	$dom_exp  = strtolower( (string) substr( strrchr( (string) $exp, '@' ), 1 ) );
	if ( ! is_email( $exp ) || ( $dom_user && $dom_exp !== $dom_user ) ) { $exp = $user; }
	if ( is_email( $exp ) ) {
		$nom = ag_smtp_opt( 'from_nom' ) ?: get_bloginfo( 'name' );
		$mail->setFrom( $exp, $nom, true );
		$mail->Sender = $exp;
	}
} );

/* ── 3. Ne plus jamais echouer en silence ────────────────────────────── */

add_action( 'wp_mail_failed', function ( $err ) {
	if ( ! is_wp_error( $err ) ) { return; }
	update_option( 'ag_smtp_derniere_erreur', array( 'quand' => time(), 'message' => (string) $err->get_error_message() ), false );
} );
if ( ! function_exists( 'ag_smtp_derniere_erreur' ) ) {
	function ag_smtp_derniere_erreur() {
		$e = get_option( 'ag_smtp_derniere_erreur', array() );
		return ( is_array( $e ) && ! empty( $e['message'] ) ) ? $e : null;
	}
}
if ( ! function_exists( 'ag_smtp_oublier_erreur' ) ) {
	function ag_smtp_oublier_erreur() { delete_option( 'ag_smtp_derniere_erreur' ); }
}

/* ── 4. Ecran de reglage (Reglages → Envoi des e-mails) ──────────────── */

add_action( 'admin_menu', function () {
	add_submenu_page( 'options-general.php', 'Envoi des e-mails', '✉️ Envoi des e-mails', 'manage_options', 'ag-smtp', 'ag_smtp_ecran' );
} );

if ( ! function_exists( 'ag_smtp_ecran' ) ) {
	function ag_smtp_ecran() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		$msg = '';
		if ( isset( $_POST['ag_smtp_save'] ) && check_admin_referer( 'ag_smtp' ) ) {
			update_option( 'ag_smtp_on', isset( $_POST['on'] ) ? 1 : 0 );
			update_option( 'ag_smtp_host', sanitize_text_field( wp_unslash( $_POST['host'] ?? '' ) ) );
			update_option( 'ag_smtp_port', absint( $_POST['port'] ?? 465 ) );
			update_option( 'ag_smtp_secu', ( 'tls' === ( $_POST['secu'] ?? '' ) ) ? 'tls' : 'ssl' );
			update_option( 'ag_smtp_user', sanitize_text_field( wp_unslash( $_POST['user'] ?? '' ) ) );
			update_option( 'ag_smtp_from', sanitize_email( wp_unslash( $_POST['from'] ?? '' ) ) );
			update_option( 'ag_smtp_from_nom', sanitize_text_field( wp_unslash( $_POST['from_nom'] ?? '' ) ) );
			$mdp = (string) wp_unslash( $_POST['pass'] ?? '' );
			if ( '' !== $mdp ) { update_option( 'ag_smtp_pass', $mdp, false ); }
			ag_smtp_oublier_erreur();
			$msg = 'Reglages enregistres.';
		}
		if ( isset( $_POST['ag_smtp_test'] ) && check_admin_referer( 'ag_smtp' ) ) {
			$dest = sanitize_email( wp_unslash( $_POST['test_mail'] ?? '' ) );
			if ( ! is_email( $dest ) ) {
				$msg = 'Adresse de test invalide : rien n\'a ete envoye.';
			} else {
				ag_smtp_oublier_erreur();
				$ok  = wp_mail( $dest, 'Test d\'envoi — ' . gmdate( 'd/m/Y H:i' ) . ' UTC',
					'<p style="font:15px/1.6 Arial,sans-serif">Message de controle envoye par le site de Gwen Services.</p>',
					array( 'Content-Type: text/html; charset=UTF-8' ) );
				$err = ag_smtp_derniere_erreur();
				$msg = ( $ok && ! $err )
					? 'Message accepte par le serveur d\'envoi, vers ' . esc_html( $dest ) . '. Verifiez qu\'il est bien ARRIVE (accepte n\'est pas arrive).'
					: 'ECHEC. ' . esc_html( $err['message'] ?? 'Le serveur a refuse sans message.' );
			}
		}
		$on   = ag_smtp_on();
		$err  = ag_smtp_derniere_erreur();
		$apwc = defined( 'AG_SMTP_PASS' ) && '' !== (string) AG_SMTP_PASS;
		$dom  = wp_parse_url( home_url(), PHP_URL_HOST );
		?>
		<div class="wrap">
			<h1>✉️ Envoi des e-mails</h1>
			<?php if ( $msg ) : ?><div class="notice notice-info"><p><?php echo wp_kses_post( $msg ); ?></p></div><?php endif; ?>
			<?php if ( ! $on ) : ?>
				<div class="notice notice-warning"><p><strong>Le site envoie par la fonction mail() de PHP.</strong>
					Chez la plupart des hebergeurs, cette sortie est bridee : le site croit avoir envoye, le message ne
					part jamais, et rien ne le signale. <strong>Les invitations de rendez-vous et confirmations n'arrivent pas.</strong></p></div>
			<?php endif; ?>
			<?php if ( $err ) : ?>
				<div class="notice notice-error"><p><strong>Dernier echec d'envoi</strong>
					(<?php echo esc_html( date_i18n( 'd/m/Y H:i', (int) $err['quand'] ) ); ?>) :<br>
					<code><?php echo esc_html( $err['message'] ); ?></code></p></div>
			<?php endif; ?>
			<form method="post">
				<?php wp_nonce_field( 'ag_smtp' ); ?>
				<table class="form-table">
					<tr><th scope="row">Passer par un serveur d'envoi</th><td>
						<label><input type="checkbox" name="on" value="1" <?php checked( (bool) get_option( 'ag_smtp_on', 0 ) ); ?>> Oui — authentifier chaque envoi</label>
					</td></tr>
					<tr><th scope="row">Serveur</th><td>
						<input type="text" name="host" class="regular-text" value="<?php echo esc_attr( ag_smtp_opt( 'host' ) ); ?>" placeholder="smtp.hostinger.com">
					</td></tr>
					<tr><th scope="row">Port et chiffrement</th><td>
						<input type="number" name="port" style="width:7em" value="<?php echo esc_attr( ag_smtp_opt( 'port', '465' ) ); ?>">
						<label style="margin-left:14px"><input type="radio" name="secu" value="ssl" <?php checked( 'ssl', ag_smtp_opt( 'secu', 'ssl' ) ); ?>> SSL (465)</label>
						<label style="margin-left:10px"><input type="radio" name="secu" value="tls" <?php checked( 'tls', ag_smtp_opt( 'secu', 'ssl' ) ); ?>> TLS (587)</label>
					</td></tr>
					<tr><th scope="row">Identifiant</th><td>
						<input type="text" name="user" class="regular-text" value="<?php echo esc_attr( ag_smtp_opt( 'user' ) ); ?>" placeholder="contact@<?php echo esc_attr( $dom ); ?>">
						<p class="description">L'adresse <strong>entiere</strong> d'une vraie boite mail (pas une redirection).</p>
					</td></tr>
					<tr><th scope="row">Mot de passe</th><td>
						<?php if ( $apwc ) : ?>
							<p class="description"><strong>Defini dans <code>wp-config.php</code></strong> (<code>AG_SMTP_PASS</code>) : ce champ est sans effet.</p>
						<?php else : ?>
							<input type="password" name="pass" class="regular-text" autocomplete="new-password" placeholder="<?php echo get_option( 'ag_smtp_pass', '' ) ? 'enregistre — laisser vide pour le garder' : ''; ?>">
							<p class="description">Celui de la <strong>boite mail</strong>, pas du compte d'hebergement. Laisser vide conserve l'actuel.</p>
						<?php endif; ?>
					</td></tr>
					<tr><th scope="row">Adresse d'expedition</th><td>
						<input type="email" name="from" class="regular-text" value="<?php echo esc_attr( ag_smtp_opt( 'from' ) ); ?>" placeholder="identique a l'identifiant">
						<input type="text" name="from_nom" class="regular-text" style="margin-left:8px" value="<?php echo esc_attr( ag_smtp_opt( 'from_nom' ) ); ?>" placeholder="Gwen Services">
						<p class="description">Elle doit appartenir au domaine authentifie (condition du DKIM/SPF).</p>
					</td></tr>
				</table>
				<p><button class="button button-primary" name="ag_smtp_save" value="1">Enregistrer</button></p>
				<h2>Essai</h2>
				<p><input type="email" name="test_mail" class="regular-text" placeholder="une adresse a vous">
					<button class="button" name="ag_smtp_test" value="1">Envoyer un message d'essai</button></p>
				<p class="description"><code>535</code> = identifiant ou mot de passe refuse (souvent parce que l'adresse est une <strong>redirection</strong>, pas une vraie boite).</p>
			</form>
		</div>
		<?php
	}
}
