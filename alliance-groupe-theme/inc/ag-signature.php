<?php
/**
 * ag-signature.php — le contrat et sa signature, avec preuve.
 *
 * AG Closer mene le prospect jusqu'ici ; ce module va du contrat a la
 * signature. Il ne signe JAMAIS a la place de quelqu'un.
 *
 * ── POURQUOI L'AGENT NE SIGNE PAS ────────────────────────────────────────
 * Signer au nom du client est un faux : le contrat ne vaut rien le jour ou
 * il faut le faire valoir, et le procede est penalement qualifie. Signer au
 * nom d'Alliance Groupe sans acte humain reveille Fabrice engage sur des
 * contrats qu'il n'a pas voulus. La version qui donne le meme resultat
 * commercial et qui tient : le client signe lui-meme, et on SCELLE la preuve.
 *
 * ── CE QUI FAIT LA PREUVE ────────────────────────────────────────────────
 * Une signature electronique « simple » au sens eIDAS vaut si l'on peut
 * montrer QUI a signe, QUOI, et QUE le document n'a pas bouge depuis. D'ou :
 * · empreinte SHA-256 du contrat au moment ou il est presente ;
 * · nom saisi a la main + case « lu et approuve » cochee ;
 * · controle de l'adresse email par un code a usage unique — c'est ce qui
 *   rattache la signature a une personne plutot qu'a un navigateur ;
 * · horodatage, adresse IP, navigateur.
 * Le tout est fige dans la fiche. On ne rejoue pas une preuve.
 *
 * ── DEUX VERROUS VOULUS ──────────────────────────────────────────────────
 * 1. Aucun contrat ne part tant que Fabrice n'a pas declare que le modele a
 *    ete RELU PAR UN AVOCAT. `templates/page-contrat-client.php` porte
 *    lui-meme la mention « modele a faire valider ». Tant qu'elle est vraie,
 *    envoyer a signer un document que personne n'a relu serait leger.
 * 2. La contresignature d'Alliance Groupe est MANUELLE par defaut. Elle peut
 *    etre automatisee, mais c'est un acte volontaire, pas un reglage oublie.
 *
 * @package Alliance_Groupe
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! defined( 'AG_SIGN_VER' ) ) { define( 'AG_SIGN_VER', '1.0.0' ); }

/** Duree de validite d'un lien de signature. */
if ( ! defined( 'AG_SIGN_VALIDITE' ) ) { define( 'AG_SIGN_VALIDITE', 30 * DAY_IN_SECONDS ); }

/* ── 1. Verrous ──────────────────────────────────────────────────────── */

if ( ! function_exists( 'ag_sign_pret' ) ) {
	/** Le modele de contrat a-t-il ete declare relu par un avocat ? */
	function ag_sign_pret() { return (bool) get_option( 'ag_sign_contrat_relu', 0 ); }
}
if ( ! function_exists( 'ag_sign_contresigne_auto' ) ) {
	/** Contresignature automatique d'Alliance Groupe. Defaut : NON. */
	function ag_sign_contresigne_auto() { return (bool) get_option( 'ag_sign_auto_contresigne', 0 ); }
}

/* ── 2. Stockage ─────────────────────────────────────────────────────── */

if ( ! function_exists( 'ag_sign_all' ) ) {
	function ag_sign_all() { return (array) get_option( 'ag_signatures', array() ); }
}
if ( ! function_exists( 'ag_sign_save' ) ) {
	function ag_sign_save( $list ) { update_option( 'ag_signatures', array_values( (array) $list ), false ); }
}
if ( ! function_exists( 'ag_sign_get' ) ) {
	/** Retrouve une demande par son jeton. @return array|null */
	function ag_sign_get( $token ) {
		foreach ( ag_sign_all() as $s ) {
			if ( hash_equals( (string) ( $s['token'] ?? '' ), (string) $token ) ) { return $s; }
		}
		return null;
	}
}
if ( ! function_exists( 'ag_sign_put' ) ) {
	/** Remplace une demande (par son id). */
	function ag_sign_put( $dossier ) {
		$list = ag_sign_all();
		foreach ( $list as $i => $s ) {
			if ( ( $s['id'] ?? '' ) === ( $dossier['id'] ?? '' ) ) { $list[ $i ] = $dossier; ag_sign_save( $list ); return; }
		}
		$list[] = $dossier;
		ag_sign_save( $list );
	}
}

/* ── 3. Le contrat ───────────────────────────────────────────────────── */

if ( ! function_exists( 'ag_sign_redige_contrat' ) ) {
	/**
	 * Compose le contrat a partir des mentions legales de la maison et des
	 * informations du client. Volontairement figé en HTML simple : c'est ce
	 * HTML exact dont on prendra l'empreinte, donc il ne doit dependre
	 * d'aucun style ni d'aucune donnee qui pourrait changer plus tard.
	 */
	function ag_sign_redige_contrat( $d ) {
		$lg = function_exists( 'ag_company_legal' ) ? ag_company_legal() : array();
		$l  = function ( $k, $def = '' ) use ( $lg ) { return trim( (string) ( $lg[ $k ] ?? $def ) ); };

		$prestataire = array_filter( array(
			$l( 'raison', 'Alliance Groupe' ),
			$l( 'forme' ),
			$l( 'adresse' ),
			$l( 'siret' ) ? 'SIRET ' . $l( 'siret' ) : '',
			$l( 'tva' ) ? 'TVA ' . $l( 'tva' ) : '',
			$l( 'email', 'contact@alliancegroupe-inc.com' ),
		) );

		$client = array_filter( array(
			(string) ( $d['client_entreprise'] ?? '' ),
			(string) ( $d['client_nom'] ?? '' ),
			(string) ( $d['client_adresse'] ?? '' ),
			(string) ( $d['client_email'] ?? '' ),
			(string) ( $d['client_tel'] ?? '' ),
		) );

		$h  = '<h1>Contrat de prestation</h1>';
		$h .= '<p><strong>Reference :</strong> ' . esc_html( (string) ( $d['id'] ?? '' ) ) . '<br>';
		$h .= '<strong>Etabli le :</strong> ' . esc_html( date_i18n( 'd/m/Y', (int) ( $d['created'] ?? time() ) ) ) . '</p>';

		$h .= '<h2>Entre les parties</h2>';
		$h .= '<p><strong>Le prestataire</strong><br>' . esc_html( implode( ' · ', $prestataire ) ) . '</p>';
		$h .= '<p><strong>Le client</strong><br>' . esc_html( implode( ' · ', $client ) ) . '</p>';

		$h .= '<h2>Objet</h2>';
		$h .= '<p>' . esc_html( (string) ( $d['objet'] ?? '' ) ) . '</p>';
		if ( ! empty( $d['details'] ) ) {
			$h .= '<ul>';
			foreach ( (array) $d['details'] as $ligne ) { $h .= '<li>' . esc_html( (string) $ligne ) . '</li>'; }
			$h .= '</ul>';
		}

		$h .= '<h2>Prix et paiement</h2>';
		$h .= '<p>Montant total : <strong>' . esc_html( (string) ( $d['montant'] ?? '' ) ) . '</strong>'
			. ( ! empty( $d['modalites'] ) ? '<br>' . esc_html( (string) $d['modalites'] ) : '' ) . '</p>';

		if ( ! empty( $d['delai'] ) ) {
			$h .= '<h2>Delai</h2><p>' . esc_html( (string) $d['delai'] ) . '</p>';
		}

		$h .= '<h2>Droit de retractation</h2>';
		$h .= '<p>Lorsque le client est un consommateur au sens du code de la consommation, il dispose d\'un delai de '
			. '<strong>quatorze (14) jours</strong> a compter de la conclusion du present contrat pour exercer son droit de '
			. 'retractation, sans avoir a motiver sa decision. Ce delai s\'exerce par simple courrier ou courriel adresse au '
			. 'prestataire. Lorsque le client est un professionnel agissant dans le cadre de son activite, ce droit ne '
			. 's\'applique pas.</p>';

		$h .= '<h2>Conditions generales</h2>';
		$h .= '<p>Les conditions generales applicables sont celles publiees a l\'adresse '
			. esc_html( home_url( '/contrat-client' ) ) . ', que le client declare avoir lues et acceptees.</p>';

		$h .= '<h2>Signature</h2>';
		$h .= '<p>Le present contrat est signe electroniquement. La signature est constituee de la saisie du nom du '
			. 'signataire, de l\'acceptation expresse des presentes, et de la verification de l\'adresse de courriel du '
			. 'signataire par un code a usage unique. L\'empreinte numerique du document, la date, l\'heure et l\'adresse '
			. 'IP du signataire sont conservees a titre de preuve.</p>';

		return $h;
	}
}

if ( ! function_exists( 'ag_sign_empreinte' ) ) {
	/** Empreinte du contrat : c'est elle qui prouve qu'il n'a pas bouge. */
	function ag_sign_empreinte( $html ) { return hash( 'sha256', (string) $html ); }
}

/* ── 4. Creer une demande de signature ───────────────────────────────── */

if ( ! function_exists( 'ag_sign_creer' ) ) {
	/**
	 * Cree une demande et envoie le lien au client.
	 *
	 * @param array $d client_nom, client_email, client_entreprise, client_tel,
	 *                 client_adresse, objet, details[], montant, modalites, delai
	 * @return array|WP_Error
	 */
	function ag_sign_creer( $d ) {
		if ( ! ag_sign_pret() ) {
			return new WP_Error( 'ag_sign_verrou', 'Le modele de contrat n\'a pas ete declare relu par un avocat. Aucun contrat ne peut partir.' );
		}
		$email = sanitize_email( (string) ( $d['client_email'] ?? '' ) );
		if ( ! is_email( $email ) ) { return new WP_Error( 'ag_sign_email', 'Adresse du client invalide.' ); }

		$dossier = array(
			'id'                => 'AG-' . gmdate( 'Ymd' ) . '-' . strtoupper( wp_generate_password( 5, false, false ) ),
			'token'             => wp_generate_password( 40, false, false ),
			'created'           => time(),
			'statut'            => 'envoye',
			'client_nom'        => sanitize_text_field( (string) ( $d['client_nom'] ?? '' ) ),
			'client_entreprise' => sanitize_text_field( (string) ( $d['client_entreprise'] ?? '' ) ),
			'client_email'      => $email,
			'client_tel'        => sanitize_text_field( (string) ( $d['client_tel'] ?? '' ) ),
			'client_adresse'    => sanitize_text_field( (string) ( $d['client_adresse'] ?? '' ) ),
			'objet'             => sanitize_text_field( (string) ( $d['objet'] ?? '' ) ),
			'details'           => array_map( 'sanitize_text_field', (array) ( $d['details'] ?? array() ) ),
			'montant'           => sanitize_text_field( (string) ( $d['montant'] ?? '' ) ),
			'modalites'         => sanitize_text_field( (string) ( $d['modalites'] ?? '' ) ),
			'delai'             => sanitize_text_field( (string) ( $d['delai'] ?? '' ) ),
			'preuve'            => array(),
		);
		$dossier['contrat']   = ag_sign_redige_contrat( $dossier );
		$dossier['empreinte'] = ag_sign_empreinte( $dossier['contrat'] );

		ag_sign_put( $dossier );

		$lien  = add_query_arg( 't', $dossier['token'], home_url( '/signer' ) );
		$corps = '<p style="font-family:Arial,sans-serif;font-size:15px;line-height:1.6;color:#e8e6e0;">Bonjour,</p>'
			. '<p style="font-family:Arial,sans-serif;font-size:15px;line-height:1.6;color:#e8e6e0;">'
			. 'Voici le contrat correspondant a ce dont nous avons parle : <strong>' . esc_html( $dossier['objet'] ) . '</strong>, '
			. 'pour un montant de <strong>' . esc_html( $dossier['montant'] ) . '</strong>.</p>'
			. ( function_exists( 'ag_email_button' ) ? ag_email_button( 'Lire et signer le contrat', $lien ) : '<p><a href="' . esc_url( $lien ) . '">' . esc_html( $lien ) . '</a></p>' )
			. '<p style="font-family:Arial,sans-serif;font-size:13px;line-height:1.6;color:#b0b0bc;">'
			. 'Rien n\'est engage tant que vous n\'avez pas signe. Le lien est valable '
			. (int) ( AG_SIGN_VALIDITE / DAY_IN_SECONDS ) . ' jours. Si quelque chose ne va pas dans ce document, '
			. 'repondez simplement a ce message : on le corrige avant signature.</p>';

		wp_mail(
			$email,
			'Votre contrat — ' . $dossier['id'],
			function_exists( 'ag_email_wrap' ) ? ag_email_wrap( 'Votre contrat', $corps ) : $corps,
			array( 'Content-Type: text/html; charset=UTF-8' )
		);
		if ( function_exists( 'ag_activity_log' ) ) {
			ag_activity_log( '📄 Contrat ' . $dossier['id'] . ' envoye a ' . $email );
		}
		return $dossier;
	}
}

/* ── 5. La page de signature ─────────────────────────────────────────── */

if ( ! function_exists( 'ag_sign_code_envoyer' ) ) {
	/** Envoie un code a usage unique a l'adresse du signataire. */
	function ag_sign_code_envoyer( &$dossier ) {
		$code = (string) wp_rand( 100000, 999999 );
		$dossier['code']     = wp_hash_password( $code );
		$dossier['code_exp'] = time() + 20 * MINUTE_IN_SECONDS;
		ag_sign_put( $dossier );

		$corps = '<p style="font-family:Arial,sans-serif;font-size:15px;color:#e8e6e0;">'
			. 'Votre code de signature pour le contrat <strong>' . esc_html( (string) $dossier['id'] ) . '</strong> :</p>'
			. '<p style="font-family:Arial,sans-serif;font-size:30px;letter-spacing:.22em;color:#D4B45C;font-weight:800;">'
			. esc_html( $code ) . '</p>'
			. '<p style="font-family:Arial,sans-serif;font-size:13px;color:#b0b0bc;">Il expire dans 20 minutes. '
			. 'Si vous n\'avez rien demande, ignorez ce message : sans ce code, aucune signature n\'est possible.</p>';
		wp_mail(
			(string) $dossier['client_email'],
			'Code de signature — ' . (string) $dossier['id'],
			function_exists( 'ag_email_wrap' ) ? ag_email_wrap( 'Code de signature', $corps ) : $corps,
			array( 'Content-Type: text/html; charset=UTF-8' )
		);
	}
}

if ( ! function_exists( 'ag_sign_sceller' ) ) {
	/**
	 * Scelle la signature. Une fois scellee, elle ne se rejoue pas.
	 */
	function ag_sign_sceller( &$dossier, $nom_saisi ) {
		$dossier['statut'] = 'signe';
		$dossier['preuve'] = array(
			'nom_saisi'      => sanitize_text_field( (string) $nom_saisi ),
			'signe_le'       => time(),
			'ip'             => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
			'navigateur'     => isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ), 0, 220 ) : '',
			'empreinte'      => (string) $dossier['empreinte'],
			'email_verifie'  => time(),
		);
		unset( $dossier['code'], $dossier['code_exp'] );

		if ( ag_sign_contresigne_auto() ) {
			$dossier['statut']         = 'contresigne';
			$dossier['contresigne_le'] = time();
			$dossier['contresigne_par'] = 'automatique (regle activee dans les reglages)';
		}
		ag_sign_put( $dossier );

		/* Copie aux deux parties : un contrat dont une partie n'a pas
		   d'exemplaire est un contrat qu'on ne peut pas opposer. */
		$recap = '<p style="font-family:Arial,sans-serif;font-size:15px;color:#e8e6e0;">'
			. 'Contrat <strong>' . esc_html( (string) $dossier['id'] ) . '</strong> signe le '
			. esc_html( date_i18n( 'd/m/Y a H:i', (int) $dossier['preuve']['signe_le'] ) ) . ' par '
			. esc_html( (string) $dossier['preuve']['nom_saisi'] ) . '.</p>'
			. '<p style="font-family:Arial,sans-serif;font-size:13px;color:#b0b0bc;">Empreinte du document (SHA-256) :<br>'
			. '<code style="font-size:11px;word-break:break-all;">' . esc_html( (string) $dossier['empreinte'] ) . '</code></p>'
			. '<div style="background:#fff;color:#111;padding:18px;border-radius:8px;font-family:Georgia,serif;">'
			. wp_kses_post( (string) $dossier['contrat'] ) . '</div>';

		$sujet = 'Contrat signe — ' . (string) $dossier['id'];
		$html  = function_exists( 'ag_email_wrap' ) ? ag_email_wrap( 'Contrat signe', $recap ) : $recap;
		wp_mail( (string) $dossier['client_email'], $sujet, $html, array( 'Content-Type: text/html; charset=UTF-8' ) );
		wp_mail( (string) get_option( 'admin_email' ), $sujet, $html, array( 'Content-Type: text/html; charset=UTF-8' ) );

		if ( function_exists( 'ag_push' ) ) {
			ag_push( '✍️ Contrat signe', (string) $dossier['client_nom'] . ' — ' . (string) $dossier['montant'] . ' (' . (string) $dossier['id'] . ')' );
		}
		if ( function_exists( 'ag_activity_log' ) ) {
			ag_activity_log( '✍️ Contrat ' . (string) $dossier['id'] . ' signe par ' . (string) $dossier['preuve']['nom_saisi'] );
		}
		/* Le prospect devient client dans le CRM. */
		if ( function_exists( 'ag_prospect_add_record' ) ) {
			ag_prospect_add_record( array(
				'name'   => (string) ( $dossier['client_entreprise'] ?: $dossier['client_nom'] ),
				'email'  => (string) $dossier['client_email'],
				'phone'  => (string) $dossier['client_tel'],
				'status' => 'client',
				'source' => 'contrat',
				'notes'  => 'Contrat ' . (string) $dossier['id'] . ' signe.',
			) );
		}
	}
}

/* La page. Autonome : elle ne depend d'aucun gabarit du theme, parce qu'un
   document contractuel ne doit pas changer d'apparence au gre d'une refonte. */
add_action( 'template_redirect', function () {
	if ( empty( $_GET['t'] ) ) { return; }
	$req = trim( (string) parse_url( (string) ( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH ), '/' );
	if ( 'signer' !== $req ) { return; }

	$token   = sanitize_text_field( wp_unslash( $_GET['t'] ) );
	$dossier = ag_sign_get( $token );
	nocache_headers();
	header( 'X-Robots-Tag: noindex, nofollow', true );

	if ( ! $dossier ) { wp_die( 'Ce lien de signature n\'existe pas ou a ete annule.', 'Lien inconnu', array( 'response' => 404 ) ); }
	if ( ( time() - (int) $dossier['created'] ) > AG_SIGN_VALIDITE ) {
		wp_die( 'Ce lien de signature a expire. Demandez-nous un nouveau contrat.', 'Lien expire', array( 'response' => 410 ) );
	}

	$erreur = '';
	$etape  = empty( $dossier['code'] ) ? 1 : 2;

	if ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) && isset( $_POST['_agsn'] ) && wp_verify_nonce( $_POST['_agsn'], 'ag_signer_' . $token ) ) {
		if ( 'deja' === (string) ( $dossier['statut'] ?? '' ) ) { $erreur = 'Ce contrat est deja signe.'; }

		if ( isset( $_POST['etape1'] ) ) {
			$nom = sanitize_text_field( wp_unslash( $_POST['nom'] ?? '' ) );
			if ( mb_strlen( $nom ) < 3 )        { $erreur = 'Saisissez votre nom et prenom.'; }
			elseif ( empty( $_POST['lu'] ) )    { $erreur = 'Vous devez cocher la case « lu et approuve ».'; }
			else {
				$dossier['nom_provisoire'] = $nom;
				ag_sign_code_envoyer( $dossier );
				$etape = 2;
			}
		} elseif ( isset( $_POST['etape2'] ) ) {
			$code = preg_replace( '/\D/', '', (string) wp_unslash( $_POST['code'] ?? '' ) );
			if ( empty( $dossier['code'] ) || time() > (int) ( $dossier['code_exp'] ?? 0 ) ) {
				$erreur = 'Le code a expire. Recommencez.';
				$etape  = 1;
			} elseif ( ! wp_check_password( $code, (string) $dossier['code'] ) ) {
				$erreur = 'Code incorrect.';
				$etape  = 2;
			} else {
				ag_sign_sceller( $dossier, (string) ( $dossier['nom_provisoire'] ?? '' ) );
			}
		}
	}

	$signe = in_array( (string) ( $dossier['statut'] ?? '' ), array( 'signe', 'contresigne' ), true );
	$css   = 'body{margin:0;background:#0d0d10;color:#e9e9ee;font:16px/1.65 Georgia,"Times New Roman",serif}'
		. '.w{max-width:760px;margin:0 auto;padding:34px 22px 70px}'
		. '.doc{background:#fff;color:#16161a;padding:30px 32px;border-radius:10px}'
		. '.doc h1{font-size:1.6rem;margin:0 0 18px}.doc h2{font-size:1.05rem;margin:24px 0 8px}'
		. '.doc p,.doc li{font-size:.95rem;line-height:1.62}'
		. '.f{margin-top:26px;background:#15151b;border:1px solid rgba(212,180,92,.3);border-radius:10px;padding:22px}'
		. 'label{display:block;margin:12px 0 6px;font:600 .9rem system-ui}'
		. 'input[type=text]{width:100%;padding:12px;border-radius:7px;border:1px solid #3a3a45;background:#0e0e13;color:#fff;font-size:1rem}'
		. '.ck{display:flex;gap:10px;align-items:flex-start;margin:16px 0;font:.88rem/1.5 system-ui}'
		. 'button{margin-top:14px;background:#D4B45C;color:#191203;border:0;border-radius:8px;padding:14px 26px;font:800 1rem system-ui;cursor:pointer}'
		. '.err{background:#3a1414;border:1px solid #7d2a2a;padding:11px 14px;border-radius:7px;font:.9rem system-ui;margin-bottom:10px}'
		. '.ok{background:#12301c;border:1px solid #2f7a4a;padding:18px;border-radius:9px;font:.95rem/1.6 system-ui}'
		. 'code{font-size:.72rem;word-break:break-all;color:#9aa3b4}'
		. '.note{font:.8rem/1.6 system-ui;color:#8f8f9c;margin-top:18px}';
	?><!doctype html>
<html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Contrat <?php echo esc_html( (string) $dossier['id'] ); ?></title>
<style><?php echo $css; // phpcs:ignore WordPress.Security.EscapeOutput ?></style></head><body>
<div class="w">
	<div class="doc"><?php echo wp_kses_post( (string) $dossier['contrat'] ); ?></div>

	<?php if ( $signe ) : ?>
		<div class="f"><div class="ok">
			<strong>Ce contrat est signe.</strong><br>
			Signe par <?php echo esc_html( (string) ( $dossier['preuve']['nom_saisi'] ?? '' ) ); ?>
			le <?php echo esc_html( date_i18n( 'd/m/Y a H:i', (int) ( $dossier['preuve']['signe_le'] ?? time() ) ) ); ?>.<br>
			Un exemplaire vous a ete envoye par courriel.
		</div>
		<p class="note">Empreinte du document : <code><?php echo esc_html( (string) $dossier['empreinte'] ); ?></code></p></div>

	<?php else : ?>
		<form class="f" method="post">
			<?php wp_nonce_field( 'ag_signer_' . $token, '_agsn' ); ?>
			<?php if ( $erreur ) : ?><div class="err"><?php echo esc_html( $erreur ); ?></div><?php endif; ?>

			<?php if ( 1 === $etape ) : ?>
				<label for="nom">Vos nom et prenom</label>
				<input type="text" id="nom" name="nom" autocomplete="name" required
					value="<?php echo esc_attr( (string) ( $dossier['client_nom'] ?? '' ) ); ?>">
				<div class="ck">
					<input type="checkbox" id="lu" name="lu" value="1" required>
					<label for="lu" style="margin:0;font-weight:400;">J'ai lu l'integralite du contrat ci-dessus et je l'approuve.</label>
				</div>
				<button type="submit" name="etape1" value="1">Continuer</button>
				<p class="note">Un code a usage unique sera envoye a
					<strong><?php echo esc_html( (string) $dossier['client_email'] ); ?></strong>.
					Il sert a prouver que c'est bien vous qui signez. Rien n'est engage avant cette etape.</p>

			<?php else : ?>
				<label for="code">Le code recu a <?php echo esc_html( (string) $dossier['client_email'] ); ?></label>
				<input type="text" id="code" name="code" inputmode="numeric" autocomplete="one-time-code"
					pattern="[0-9]{6}" maxlength="6" required>
				<button type="submit" name="etape2" value="1">Signer le contrat</button>
				<p class="note">En validant, vous signez electroniquement ce document. Sont conserves a titre de preuve :
					votre nom, la date et l'heure, votre adresse IP, et l'empreinte numerique du contrat.</p>
			<?php endif; ?>
		</form>
	<?php endif; ?>
</div></body></html>
	<?php
	exit;
}, 2 );
