<?php
/**
 * ENVOI DES E-MAILS — sortie authentifiee (SMTP)
 * ---------------------------------------------------------------------------
 * Pourquoi ce module existe.
 *
 * Par defaut, WordPress confie ses messages a la fonction mail() de PHP : le
 * site les depose sur le serveur web, qui les expedie lui-meme. Chez la
 * plupart des hebergeurs mutualises — Hostinger compris — cette sortie est
 * bridee ou coupee. Le site croit avoir envoye, le message ne part jamais, et
 * RIEN ne le signale : wp_mail() repond « vrai », aucune erreur n'apparait.
 *
 * Ce silence coute cher. Sans sortie qui fonctionne :
 *  · le code a usage unique de la signature n'arrive pas — PERSONNE ne peut
 *    signer un contrat, et le client ne comprend pas pourquoi ;
 *  · l'exemplaire du contrat signe n'arrive ni chez le client ni chez nous ;
 *  · les alertes de devis, de leads et de ventes disparaissent ;
 *  · l'agent commercial croit demarcher et ne parle a personne.
 *
 * Le remede est de passer par le relais de courrier de l'hebergeur, en
 * s'authentifiant comme le ferait un logiciel de messagerie. Deuxieme benefice,
 * aussi important : c'est ce relais-la qui appose la signature DKIM. Un
 * message depose par PHP sort non signe meme quand le DNS est parfait.
 *
 * Le mot de passe. Il est stocke en base parce qu'il faut bien le presenter au
 * serveur a chaque envoi. Pour le garder hors de la base, definir dans
 * wp-config.php :  define( 'AG_SMTP_PASS', '...' );  — la constante l'emporte.
 *
 * @package alliance-groupe-theme
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
	/** Eteint par defaut : tant que rien n'est renseigne, on ne change rien. */
	function ag_smtp_on() {
		return (bool) get_option( 'ag_smtp_on', 0 ) && '' !== ag_smtp_opt( 'host' );
	}
}

if ( ! function_exists( 'ag_smtp_pass' ) ) {
	/** La constante de wp-config l'emporte sur la base. */
	function ag_smtp_pass() {
		if ( defined( 'AG_SMTP_PASS' ) && '' !== (string) AG_SMTP_PASS ) {
			return (string) AG_SMTP_PASS;
		}
		return (string) get_option( 'ag_smtp_pass', '' );
	}
}

/* ── 2. La sortie elle-meme ──────────────────────────────────────────── */

add_action( 'phpmailer_init', function ( $mail ) {
	if ( ! ag_smtp_on() ) { return; }

	$mail->isSMTP();
	$mail->Host       = ag_smtp_opt( 'host' );
	$mail->Port       = (int) ( ag_smtp_opt( 'port', '465' ) ?: 465 );
	$mail->SMTPAuth   = true;
	$mail->Username   = ag_smtp_opt( 'user' );
	$mail->Password   = ag_smtp_pass();
	$mail->SMTPSecure = ( 'tls' === ag_smtp_opt( 'secu', 'ssl' ) ) ? 'tls' : 'ssl';

	/* L'expediteur DOIT appartenir au domaine authentifie. C'est la regle qui
	   fait tout marcher (ou tout casser) : le relais ne signe (DKIM) et n'aligne
	   (SPF/DMARC) QUE pour son propre domaine. Une adresse d'un autre domaine —
	   typiquement un sous-domaine « pro.… » qui n'existe pas comme boite — part
	   non signee et Gmail la met en quarantaine (elle « n'arrive pas »).

	   Donc : on force l'expediteur sur le domaine de l'identifiant. Si quelqu'un
	   a saisi une adresse d'un autre domaine, on l'ignore et on retombe sur
	   l'identifiant. Ce garde-fou empeche de se tirer une balle dans le pied. */
	$user      = ag_smtp_opt( 'user' );
	$dom_user  = strtolower( (string) substr( strrchr( $user, '@' ), 1 ) );
	$exp       = ag_smtp_opt( 'from' ) ?: $user;
	$dom_exp   = strtolower( (string) substr( strrchr( (string) $exp, '@' ), 1 ) );

	if ( ! is_email( $exp ) || ( $dom_user && $dom_exp !== $dom_user ) ) {
		$exp = $user; // adresse hors du domaine authentifie : on la refuse
	}
	if ( is_email( $exp ) ) {
		$nom = ag_smtp_opt( 'from_nom' ) ?: get_bloginfo( 'name' );
		/* true = ecrase tout From pose en amont (ex. l'adresse « pro.… » d'un
		   autre ecran) : c'est NOTRE From aligne qui gagne, toujours. */
		$mail->setFrom( $exp, $nom, true );
		$mail->Sender = $exp; /* enveloppe : c'est elle que SPF examine */
	}
} );

/* ── 3. Ne plus jamais echouer en silence ────────────────────────────── */

add_action( 'wp_mail_failed', function ( $err ) {
	if ( ! is_wp_error( $err ) ) { return; }
	update_option( 'ag_smtp_derniere_erreur', array(
		'quand'   => time(),
		'message' => (string) $err->get_error_message(),
	), false );
} );

if ( ! function_exists( 'ag_smtp_derniere_erreur' ) ) {
	/** Retourne array('quand','message') ou null. */
	function ag_smtp_derniere_erreur() {
		$e = get_option( 'ag_smtp_derniere_erreur', array() );
		return ( is_array( $e ) && ! empty( $e['message'] ) ) ? $e : null;
	}
}

if ( ! function_exists( 'ag_smtp_oublier_erreur' ) ) {
	function ag_smtp_oublier_erreur() { delete_option( 'ag_smtp_derniere_erreur' ); }
}

/* ── 4. Ecran de reglage ─────────────────────────────────────────────── */

add_action( 'admin_menu', function () {
	add_submenu_page(
		'ag-hub', 'Envoi des e-mails', '✉️ Envoi des e-mails',
		'manage_options', 'ag-smtp', 'ag_smtp_ecran'
	);
}, 31 );

if ( ! function_exists( 'ag_smtp_ecran' ) ) {
	function ag_smtp_ecran() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		wp_enqueue_media(); // sélecteur d'image natif WordPress pour la signature
		$msg = '';

		if ( isset( $_POST['ag_smtp_save'] ) && check_admin_referer( 'ag_smtp' ) ) {
			update_option( 'ag_smtp_on', isset( $_POST['on'] ) ? 1 : 0 );
			update_option( 'ag_smtp_host', sanitize_text_field( wp_unslash( $_POST['host'] ?? '' ) ) );
			update_option( 'ag_smtp_port', absint( $_POST['port'] ?? 465 ) );
			update_option( 'ag_smtp_secu', ( 'tls' === ( $_POST['secu'] ?? '' ) ) ? 'tls' : 'ssl' );
			update_option( 'ag_smtp_user', sanitize_text_field( wp_unslash( $_POST['user'] ?? '' ) ) );
			update_option( 'ag_smtp_from', sanitize_email( wp_unslash( $_POST['from'] ?? '' ) ) );
			update_option( 'ag_smtp_from_nom', sanitize_text_field( wp_unslash( $_POST['from_nom'] ?? '' ) ) );

			/* Signature des emails (bloc « manuscrit » en bas de chaque message). */
			update_option( 'ag_email_signature_name', sanitize_text_field( wp_unslash( $_POST['sig_nom'] ?? '' ) ) );
			update_option( 'ag_email_signature_role', sanitize_text_field( wp_unslash( $_POST['sig_role'] ?? '' ) ) );
			update_option( 'ag_email_signature_img', esc_url_raw( wp_unslash( $_POST['sig_img'] ?? '' ) ) );

			/* Un champ mot de passe laisse vide veut dire « n'y touche pas »,
			   jamais « efface-le » : on ne perd pas un reglage qui marche
			   parce que le navigateur n'a pas rempli le champ. */
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
				$ok = wp_mail(
					$dest,
					'Test d\'envoi — ' . gmdate( 'd/m/Y H:i' ) . ' UTC',
					'<p style="font:15px/1.6 Arial,sans-serif">Message de controle envoye par le site.</p>',
					array( 'Content-Type: text/html; charset=UTF-8' )
				);
				$err = ag_smtp_derniere_erreur();
				if ( $ok && ! $err ) {
					$msg = 'Message accepte par le serveur d\'envoi, a destination de ' . esc_html( $dest )
						. '. Verifiez maintenant qu\'il est bien ARRIVE : accepte n\'est pas arrive.';
				} else {
					$msg = 'ECHEC. ' . esc_html( $err['message'] ?? 'Le serveur a refuse sans message.' );
				}
			}
		}

		$on   = ag_smtp_on();
		$err  = ag_smtp_derniere_erreur();
		$apwc = defined( 'AG_SMTP_PASS' ) && '' !== (string) AG_SMTP_PASS;
		?>
		<div class="wrap">
			<h1>✉️ Envoi des e-mails</h1>

			<?php if ( $msg ) : ?>
				<div class="notice notice-info"><p><?php echo wp_kses_post( $msg ); ?></p></div>
			<?php endif; ?>

			<?php if ( ! $on ) : ?>
				<div class="notice notice-warning"><p>
					<strong>Le site envoie par la fonction mail() de PHP.</strong>
					Chez la plupart des hebergeurs mutualises, cette sortie est bridee ou coupee :
					le site croit avoir envoye, le message ne part jamais, et rien ne le signale.
					Sans sortie qui fonctionne, <strong>personne ne peut signer un contrat</strong> —
					le code a usage unique n'arrive pas.
				</p></div>
			<?php endif; ?>

			<?php if ( $err ) : ?>
				<div class="notice notice-error"><p>
					<strong>Dernier echec d'envoi</strong>
					(<?php echo esc_html( date_i18n( 'd/m/Y H:i', (int) $err['quand'] ) ); ?>) :<br>
					<code><?php echo esc_html( $err['message'] ); ?></code>
				</p></div>
			<?php endif; ?>

			<form method="post">
				<?php wp_nonce_field( 'ag_smtp' ); ?>
				<table class="form-table">
					<tr><th scope="row">Passer par un serveur d'envoi</th><td>
						<label><input type="checkbox" name="on" value="1" <?php checked( (bool) get_option( 'ag_smtp_on', 0 ) ); ?>>
							Oui — authentifier chaque envoi</label>
					</td></tr>
					<tr><th scope="row">Serveur</th><td>
						<input type="text" name="host" class="regular-text"
							value="<?php echo esc_attr( ag_smtp_opt( 'host' ) ); ?>" placeholder="smtp.hostinger.com">
					</td></tr>
					<tr><th scope="row">Port et chiffrement</th><td>
						<input type="number" name="port" style="width:7em"
							value="<?php echo esc_attr( ag_smtp_opt( 'port', '465' ) ); ?>">
						<label style="margin-left:14px"><input type="radio" name="secu" value="ssl"
							<?php checked( 'ssl', ag_smtp_opt( 'secu', 'ssl' ) ); ?>> SSL (465)</label>
						<label style="margin-left:10px"><input type="radio" name="secu" value="tls"
							<?php checked( 'tls', ag_smtp_opt( 'secu', 'ssl' ) ); ?>> TLS (587)</label>
					</td></tr>
					<tr><th scope="row">Identifiant</th><td>
						<input type="text" name="user" class="regular-text"
							value="<?php echo esc_attr( ag_smtp_opt( 'user' ) ); ?>"
							placeholder="contact@alliancegroupe-inc.com">
						<p class="description">L'adresse <strong>entiere</strong>, pas seulement ce qui precede l'arobase.</p>
					</td></tr>
					<tr><th scope="row">Mot de passe</th><td>
						<?php if ( $apwc ) : ?>
							<p class="description"><strong>Defini dans <code>wp-config.php</code></strong>
								(constante <code>AG_SMTP_PASS</code>) : il n'est pas en base, et ce champ est sans effet.</p>
						<?php else : ?>
							<input type="password" name="pass" class="regular-text" autocomplete="new-password"
								placeholder="<?php echo get_option( 'ag_smtp_pass', '' ) ? 'enregistre — laisser vide pour le garder' : ''; ?>">
							<p class="description">Celui de la <strong>boite mail</strong>, pas celui du compte d'hebergement.
								Laisser vide conserve le mot de passe actuel.</p>
						<?php endif; ?>
					</td></tr>
					<tr><th scope="row">Adresse d'expedition</th><td>
						<input type="email" name="from" class="regular-text"
							value="<?php echo esc_attr( ag_smtp_opt( 'from' ) ); ?>"
							placeholder="identique a l'identifiant">
						<input type="text" name="from_nom" class="regular-text" style="margin-left:8px"
							value="<?php echo esc_attr( ag_smtp_opt( 'from_nom' ) ); ?>" placeholder="Alliance Groupe">
						<p class="description">Elle doit appartenir au domaine authentifie : c'est la condition
							pour que la signature DKIM soit apposee et que SPF s'aligne.</p>
					</td></tr>
				</table>

				<h2>Signature des emails</h2>
				<p class="description" style="max-width:640px">
					Chaque email de marque se termine par un bloc signature. Déposez votre
					<strong>vraie signature manuscrite</strong> (un scan ou une photo sur fond clair,
					idéalement un PNG transparent). Tant qu'aucune image n'est fournie, le prénom
					s'affiche automatiquement dans une <strong>police cursive</strong> (déjà élégant).
				</p>
				<table class="form-table">
					<tr><th scope="row">Image de signature</th><td>
						<input type="url" name="sig_img" id="ag_sig_img" class="regular-text"
							value="<?php echo esc_attr( get_option( 'ag_email_signature_img', '' ) ); ?>"
							placeholder="https://…/signature.png">
						<button type="button" class="button" id="ag_sig_pick">Choisir une image…</button>
						<button type="button" class="button" id="ag_sig_clear">Retirer</button>
						<div id="ag_sig_preview" style="margin-top:10px;padding:14px;background:#14141c;border-radius:10px;display:inline-block;<?php echo get_option( 'ag_email_signature_img', '' ) ? '' : 'display:none;'; ?>">
							<img src="<?php echo esc_url( get_option( 'ag_email_signature_img', '' ) ); ?>" alt="" style="max-height:80px;width:auto;display:block;">
						</div>
						<p class="description">Astuce : signez en noir sur papier blanc, photographiez, puis
							retirez le fond (ou importez un PNG transparent) pour un rendu net sur le fond sombre.</p>
					</td></tr>
					<tr><th scope="row">Nom affiché</th><td>
						<input type="text" name="sig_nom" class="regular-text"
							value="<?php echo esc_attr( get_option( 'ag_email_signature_name', '' ) ); ?>"
							placeholder="Fabrice">
					</td></tr>
					<tr><th scope="row">Fonction / sous-titre</th><td>
						<input type="text" name="sig_role" class="regular-text"
							value="<?php echo esc_attr( get_option( 'ag_email_signature_role', '' ) ); ?>"
							placeholder="Fondateur · Alliance Groupe">
					</td></tr>
				</table>

				<p><button class="button button-primary" name="ag_smtp_save" value="1">Enregistrer</button></p>

				<h2>Essai</h2>
				<p>
					<input type="email" name="test_mail" class="regular-text" placeholder="une adresse a vous">
					<button class="button" name="ag_smtp_test" value="1">Envoyer un message d'essai</button>
				</p>
				<p class="description">
					En cas d'echec, le message exact du serveur s'affiche ci-dessus. <code>535</code> signifie
					identifiant ou mot de passe refuse — le plus souvent parce que l'adresse n'est
					qu'une <strong>redirection</strong> et non une vraie boite : une redirection n'a pas de mot de passe.
				</p>
			</form>
			<script>
			(function($){
				var frame;
				$('#ag_sig_pick').on('click', function(e){
					e.preventDefault();
					if (frame) { frame.open(); return; }
					frame = wp.media({ title:'Signature manuscrite', library:{type:'image'}, button:{text:'Utiliser cette image'}, multiple:false });
					frame.on('select', function(){
						var a = frame.state().get('selection').first().toJSON();
						$('#ag_sig_img').val(a.url);
						$('#ag_sig_preview').find('img').attr('src', a.url);
						$('#ag_sig_preview').show();
					});
					frame.open();
				});
				$('#ag_sig_clear').on('click', function(e){
					e.preventDefault();
					$('#ag_sig_img').val('');
					$('#ag_sig_preview').hide();
				});
			})(jQuery);
			</script>

			<?php if ( function_exists( 'ag_email_wrap' ) ) :
				$apercu = ag_email_wrap(
					'Votre site est en ligne 🎉',
					'<p>Bonjour Marie,</p>'
					. '<p>Bonne nouvelle : votre nouveau site est <strong>en ligne</strong> et prêt à recevoir vos premiers clients. Voici l\'essentiel en un coup d\'œil :</p>'
					. ( function_exists( 'ag_email_card' ) ? ag_email_card(
						'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-family:inherit;font-size:14px;color:#e8e8ee;">'
						. '<tr><td style="padding:4px 0;">Adresse</td><td style="padding:4px 0;text-align:right;color:#D4B45C;">votre-site.fr</td></tr>'
						. '<tr><td style="padding:4px 0;">Pages livrées</td><td style="padding:4px 0;text-align:right;">5</td></tr>'
						. '<tr><td style="padding:4px 0;">Formulaire de contact</td><td style="padding:4px 0;text-align:right;">actif</td></tr>'
						. '</table>' ) : '' )
					. ( function_exists( 'ag_email_button' ) ? ag_email_button( 'Voir mon site', 'https://alliancegroupe-inc.com' ) : '' )
					. '<p>Je reste disponible pour toute question.</p>'
				);
			?>
				<h2>Aperçu de l'email</h2>
				<p class="description">Rendu réel du gabarit de marque, avec votre signature telle qu'elle sera envoyée.</p>
				<iframe title="Aperçu de l'email" style="width:100%;max-width:640px;height:760px;border:1px solid #ccd0d4;border-radius:8px;background:#0a0a0f;"
					srcdoc="<?php echo esc_attr( $apercu ); ?>"></iframe>
			<?php endif; ?>
		</div>
		<?php
	}
}
