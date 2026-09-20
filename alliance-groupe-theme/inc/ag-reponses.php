<?php
/**
 * ag-reponses.php — le dernier maillon : lire les reponses, qualifier, enchainer.
 *
 * Jusqu'ici la chaine s'arretait net : l'agent ecrivait, le prospect
 * repondait… dans une boite que personne ne lisait. Ce module ferme la
 * boucle. Une reponse arrive, elle est comprise, le CRM bouge, et si
 * l'intention est claire le juriste prepare le contrat.
 *
 * ── PAR OU ARRIVENT LES REPONSES ─────────────────────────────────────────
 * Les SMS et les appels passent deja par `ag/v1/inbound` (ag-sms-gateway).
 * Les emails n'avaient aucune porte : c'est `ag/v1/reponse`, ouverte ici.
 * Meme jeton (`ag_inbound_token`), pour n'avoir qu'un secret a gerer.
 *
 * Cote fournisseur, n'importe quelle route d'email entrant qui sait POSTer
 * un message analyse convient (Cloudflare Email Routing, Mailgun, Postmark,
 * une regle de redirection chez l'hebergeur). On accepte les noms de champs
 * les plus courants plutot que d'en imposer un.
 *
 * ── CE QUI DECLENCHE UN CONTRAT, ET CE QUI N'EN DECLENCHE PAS ────────────
 * Un contrat ne part que sur une intention d'achat EXPLICITE et sans
 * ambiguite — « c'est d'accord », « envoyez le devis », « je prends ».
 * « Combien ca coute ? » n'est pas un accord, c'est une question : le
 * prospect passe en « interesse » et Fabrice est prevenu. Envoyer un contrat
 * a quelqu'un qui demandait un prix, c'est perdre le client.
 *
 * Et meme sur une intention claire : le juriste relit AVANT que quoi que ce
 * soit parte. Il bloque, le contrat ne part pas.
 *
 * @package Alliance_Groupe
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! defined( 'AG_REPONSES_VER' ) ) { define( 'AG_REPONSES_VER', '1.0.0' ); }

/* ── 1. Reglages ─────────────────────────────────────────────────────── */

if ( ! function_exists( 'ag_reponses_contrat_auto' ) ) {
	/** Envoyer le contrat tout seul sur une intention d'achat claire ? Defaut : NON. */
	function ag_reponses_contrat_auto() { return (bool) get_option( 'ag_reponses_contrat_auto', 0 ); }
}
if ( ! function_exists( 'ag_reponses_pack' ) ) {
	/** Le pack propose par defaut quand l'agent enchaine seul. */
	function ag_reponses_pack() {
		$p = (string) get_option( 'ag_reponses_pack', 'essentiel' );
		return in_array( $p, array( 'essentiel', 'pro', 'boutique' ), true ) ? $p : 'essentiel';
	}
}

/* ── 2. La porte d'entree des emails ─────────────────────────────────── */

add_action( 'rest_api_init', function () {
	register_rest_route( 'ag/v1', '/reponse', array(
		'methods'             => 'POST',
		'permission_callback' => '__return_true',
		'callback'            => 'ag_reponses_rest',
	) );
} );

if ( ! function_exists( 'ag_reponses_rest' ) ) {
	function ag_reponses_rest( $req ) {
		/* Meme jeton que l'entree SMS : un seul secret a gerer, un seul a
		   faire tourner le jour ou il fuite. */
		$token = (string) get_option( 'ag_inbound_token', '' );
		$given = (string) $req->get_param( 'token' );
		if ( '' === $given ) { $given = (string) $req->get_header( 'x-ag-token' ); }
		if ( '' === $given && function_exists( 'ag_inbound_pick' ) ) {
			$given = (string) ag_inbound_pick( $req, array( 'token' ) );
		}
		if ( '' === $token || ! hash_equals( $token, $given ) ) {
			return new WP_REST_Response( array( 'error' => 'unauthorized' ), 401 );
		}

		$pick = function ( $keys ) use ( $req ) {
			if ( function_exists( 'ag_inbound_pick' ) ) { return (string) ag_inbound_pick( $req, $keys ); }
			foreach ( $keys as $k ) { $v = $req->get_param( $k ); if ( null !== $v && '' !== $v ) { return (string) $v; } }
			return '';
		};

		$from  = sanitize_email( $pick( array( 'from', 'sender', 'from_email', 'envelope_from', 'email' ) ) );
		$texte = sanitize_textarea_field( $pick( array( 'text', 'body-plain', 'body', 'plain', 'content', 'message', 'stripped-text' ) ) );
		$sujet = sanitize_text_field( $pick( array( 'subject', 'sujet', 'title' ) ) );

		if ( ! is_email( $from ) ) { return new WP_REST_Response( array( 'error' => 'no_from' ), 400 ); }
		if ( '' === trim( $texte ) ) { $texte = $sujet; }

		return new WP_REST_Response( ag_reponses_traiter( $from, $texte, $sujet ), 200 );
	}
}

/* ── 3. Comprendre la reponse ────────────────────────────────────────── */

if ( ! function_exists( 'ag_reponses_qualifier' ) ) {
	/**
	 * Quelle est l'intention de cette reponse ?
	 *
	 * @return array array( 'intention' => string, 'sur' => bool, 'resume' => string )
	 *               intention : achat | interesse | plus_tard | refus | stop | question
	 */
	function ag_reponses_qualifier( $texte ) {
		/* Le refus explicite et la demande d'opposition se reconnaissent sans
		   IA, et doivent etre respectes meme si l'IA est en panne. */
		if ( preg_match( '/(^|\W)(stop|desabonne|désabonne|desinscri|désinscri|ne plus me contacter|ne me contactez plus|unsubscribe)(\W|$)/iu', $texte ) ) {
			return array( 'intention' => 'stop', 'sur' => true, 'resume' => 'Demande d\'opposition explicite.' );
		}

		if ( ! function_exists( 'ag_ia_ready' ) || ! ag_ia_ready() ) {
			/* Sans IA : on ne devine pas une intention d'achat. On classe en
			   « interesse » et un humain tranche. Se tromper ici coute un
			   client ; ne pas trancher ne coute qu'un coup d'oeil. */
			return array( 'intention' => 'interesse', 'sur' => false, 'resume' => 'IA indisponible — a qualifier a la main.' );
		}

		$systeme = "Tu classes la reponse d'un prospect a un email de prospection. Reponds UNIQUEMENT par un objet JSON, sans texte autour.\n"
			. "Champs :\n"
			. "- \"intention\" : l'une de ces valeurs exactement — achat, interesse, plus_tard, refus, stop, question\n"
			. "- \"sur\" : true seulement si l'intention est EXPLICITE et sans ambiguite\n"
			. "- \"resume\" : une phrase courte en francais\n"
			. "REGLES :\n"
			. "- \"achat\" UNIQUEMENT si la personne dit clairement oui : « c'est d'accord », « je prends », « envoyez le contrat », « on y va ».\n"
			. "- Une question sur le prix, le delai ou le contenu n'est PAS un achat : c'est \"question\".\n"
			. "- Un « pourquoi pas », « ca m'interesse », « rappelez-moi » est \"interesse\", jamais \"achat\".\n"
			. "- Dans le doute, \"sur\" vaut false. Se tromper en croyant a un achat coute un client.";

		$opts = array( 'max_tokens' => 300, 'temperature' => 0.1, 'timeout' => 30 );
		if ( function_exists( 'ag_ia_model' ) ) { $opts['model'] = ag_ia_model( 'fast' ); }

		$out = ag_ia_call( $systeme, "Reponse du prospect :\n" . $texte, $opts );
		if ( is_wp_error( $out ) ) {
			return array( 'intention' => 'interesse', 'sur' => false, 'resume' => 'IA en erreur — a qualifier a la main.' );
		}
		$out = trim( (string) $out );
		$out = preg_replace( '/\A```[a-zA-Z]*\s*\R?/', '', $out );
		$out = trim( preg_replace( '/\R?```\s*\z/', '', $out ) );

		$j = json_decode( $out, true );
		$valides = array( 'achat', 'interesse', 'plus_tard', 'refus', 'stop', 'question' );
		$intent  = is_array( $j ) ? (string) ( $j['intention'] ?? '' ) : '';
		if ( ! in_array( $intent, $valides, true ) ) {
			return array( 'intention' => 'interesse', 'sur' => false, 'resume' => 'Reponse de l\'IA illisible — a qualifier a la main.' );
		}
		return array(
			'intention' => $intent,
			'sur'       => ! empty( $j['sur'] ),
			'resume'    => sanitize_text_field( (string) ( $j['resume'] ?? '' ) ),
		);
	}
}

/* ── 4. Enchainer ────────────────────────────────────────────────────── */

if ( ! function_exists( 'ag_reponses_traiter' ) ) {
	/**
	 * Retrouve le prospect, met le CRM a jour, et enchaine si c'est clair.
	 *
	 * @return array Compte rendu (renvoye au fournisseur d'email entrant).
	 */
	function ag_reponses_traiter( $from, $texte, $sujet = '' ) {
		$avis = ag_reponses_qualifier( $texte );

		$list  = (array) get_option( 'ag_prospects', array() );
		$index = null;
		foreach ( $list as $i => $p ) {
			if ( strtolower( (string) ( $p['email'] ?? '' ) ) === strtolower( $from ) ) { $index = $i; break; }
		}
		if ( null === $index ) {
			/* Quelqu'un qui repond sans etre au CRM est quand meme une piste :
			   on la garde plutot que de la jeter. */
			if ( function_exists( 'ag_prospect_add_record' ) ) {
				ag_prospect_add_record( array(
					'name'   => $from,
					'email'  => $from,
					'status' => 'interesse',
					'source' => 'reponse',
					'notes'  => 'A repondu sans etre au CRM : ' . mb_substr( $texte, 0, 300 ),
				) );
			}
			if ( function_exists( 'ag_push' ) ) { ag_push( '📩 Reponse d\'un inconnu', $from . ' — ' . mb_substr( $texte, 0, 140 ) ); }
			return array( 'ok' => true, 'connu' => false, 'intention' => $avis['intention'] );
		}

		$p = $list[ $index ];

		/* Quoi qu'il arrive : il a repondu, donc il sort de la sequence. */
		$p['replied']    = 1;
		$p['date_reply'] = gmdate( 'Y-m-d H:i' );
		$p['notes']      = trim( (string) ( $p['notes'] ?? '' ) . "\n" . gmdate( 'd/m/Y' ) . ' — reponse : ' . mb_substr( $texte, 0, 400 ) );

		$statuts = array(
			'stop'      => 'ne_pas_contacter',
			'refus'     => 'refus',
			'achat'     => 'interesse',
			'interesse' => 'interesse',
			'question'  => 'interesse',
			'plus_tard' => 'relance',
		);
		$p['status'] = $statuts[ $avis['intention'] ] ?? 'interesse';
		if ( 'stop' === $avis['intention'] ) { $p['closer_stop'] = time(); }

		$list[ $index ] = $p;
		update_option( 'ag_prospects', $list, false );

		if ( function_exists( 'ag_activity_log' ) ) {
			ag_activity_log( '📩 ' . (string) ( $p['name'] ?? $from ) . ' a repondu — ' . $avis['intention'] );
		}

		/* Le contrat ne part que si TOUT est reuni : intention d'achat,
		   certitude, et l'interrupteur explicitement arme. */
		$contrat = null;
		if ( 'achat' === $avis['intention'] && $avis['sur'] && ag_reponses_contrat_auto()
			&& function_exists( 'ag_juriste_affaire_depuis_prospect' ) ) {

			$affaire = ag_juriste_affaire_depuis_prospect( $p, ag_reponses_pack() );
			if ( ! is_wp_error( $affaire ) ) {
				$res = ag_juriste_envoyer_contrat( $affaire );
				if ( is_wp_error( $res ) ) {
					$contrat = 'bloque : ' . $res->get_error_message();
					if ( function_exists( 'ag_push' ) ) {
						ag_push( '⛔ Contrat bloque', (string) ( $p['name'] ?? $from ) . ' voulait signer — ' . $res->get_error_message() );
					}
				} else {
					$contrat = 'envoye : ' . (string) ( $res['id'] ?? '' );
				}
			}
		}

		if ( function_exists( 'ag_push' ) ) {
			$titres = array(
				'achat'     => '🤝 Un prospect dit OUI',
				'interesse' => '📩 Prospect interesse',
				'question'  => '❓ Question d\'un prospect',
				'plus_tard' => '⏳ A rappeler plus tard',
				'refus'     => '🙅 Refus',
				'stop'      => '🚫 Opposition',
			);
			ag_push(
				( $titres[ $avis['intention'] ] ?? '📩 Reponse' ),
				(string) ( $p['name'] ?? $from ) . ' — ' . $avis['resume']
					. ( $contrat ? "\nContrat : " . $contrat : '' )
					. ( ! $avis['sur'] ? "\n(a verifier : l'intention n'est pas certaine)" : '' )
			);
		}

		return array(
			'ok'        => true,
			'connu'     => true,
			'intention' => $avis['intention'],
			'sur'       => (bool) $avis['sur'],
			'contrat'   => $contrat,
		);
	}
}

/* ── 5. Reglages dans l'ecran de l'agent commercial ──────────────────── */

add_action( 'admin_menu', function () {
	add_submenu_page(
		'ag-prospects', 'Reponses entrantes', '📩 Reponses entrantes',
		'manage_options', 'ag-reponses', 'ag_reponses_ecran'
	);
}, 32 );

if ( ! function_exists( 'ag_reponses_ecran' ) ) {
	function ag_reponses_ecran() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }

		$msg = '';
		if ( isset( $_POST['ag_rep_save'] ) && check_admin_referer( 'ag_rep' ) ) {
			update_option( 'ag_reponses_contrat_auto', empty( $_POST['auto'] ) ? 0 : 1, false );
			update_option( 'ag_reponses_pack', sanitize_text_field( wp_unslash( $_POST['pack'] ?? 'essentiel' ) ), false );
			$msg = 'Reglages enregistres.';
		}
		$token = (string) get_option( 'ag_inbound_token', '' );
		?>
		<div class="wrap">
			<h1>📩 Reponses entrantes</h1>
			<?php if ( $msg ) : ?><div class="notice notice-success"><p><?php echo esc_html( $msg ); ?></p></div><?php endif; ?>

			<p class="description" style="max-width:840px">
				Sans cette porte, la chaine s'arrete net : l'agent ecrit, le prospect repond, et personne ne lit.
				Une reponse qui arrive ici est comprise, le CRM bouge, le prospect sort de la sequence de relance,
				et vous etes prevenu.
			</p>

			<h2>L'adresse a brancher</h2>
			<?php if ( '' === $token ) : ?>
				<div class="notice notice-error inline"><p>
					<strong>Aucun jeton d'entree.</strong> Generez-le dans <em>Reglages → Passerelle SMS</em> :
					c'est le meme pour les SMS et pour les emails, il n'y a qu'un secret a gerer.
				</p></div>
			<?php else : ?>
				<p>Faites POSTer vos emails entrants vers :</p>
				<p><code style="font-size:13px;user-select:all"><?php
					echo esc_html( rest_url( 'ag/v1/reponse' ) . '?token=' . $token );
				?></code></p>
				<p class="description">
					N'importe quelle route d'email entrant sait faire ca : Cloudflare Email Routing, Mailgun, Postmark,
					ou une simple regle chez l'hebergeur. Les noms de champs courants sont acceptes
					(<code>from</code>, <code>subject</code>, <code>text</code> ou <code>body-plain</code>).
				</p>
			<?php endif; ?>

			<form method="post">
				<?php wp_nonce_field( 'ag_rep' ); ?>
				<table class="form-table">
					<tr><th scope="row">Contrat automatique</th><td>
						<label><input type="checkbox" name="auto" value="1" <?php checked( ag_reponses_contrat_auto() ); ?>>
							Envoyer le contrat tout seul quand le prospect dit clairement oui</label>
						<p class="description">
							Uniquement sur une intention d'achat <strong>explicite et certaine</strong> — « c'est d'accord »,
							« je prends », « envoyez le contrat ». Une question sur le prix n'en est pas une : le prospect
							passe en « interesse » et vous etes prevenu.<br>
							Le juriste relit avant l'envoi dans tous les cas. S'il bloque, rien ne part et vous recevez le motif.
						</p>
					</td></tr>
					<tr><th scope="row">Offre proposee</th><td>
						<select name="pack">
							<?php
							$packs = function_exists( 'ag_sites_express_packs' ) ? (array) ag_sites_express_packs() : array();
							foreach ( $packs as $k => $o ) {
								printf(
									'<option value="%s"%s>%s — %s</option>',
									esc_attr( $k ), selected( ag_reponses_pack(), $k, false ),
									esc_html( (string) ( $o['nom'] ?? $k ) ), esc_html( (string) ( $o['prix'] ?? '' ) )
								);
							}
							?>
						</select>
						<p class="description">Le contrat envoye automatiquement porte cette formule. Un prospect qui en veut une autre repondra.</p>
					</td></tr>
				</table>
				<p><button class="button button-primary" name="ag_rep_save" value="1">Enregistrer</button></p>
			</form>

			<h2>Ce que l'agent comprend</h2>
			<table class="widefat" style="max-width:860px">
				<tr><th style="width:20%">Intention</th><th style="width:26%">Statut au CRM</th><th>Ce qui se passe</th></tr>
				<tr><td><strong>achat</strong></td><td>interesse</td><td>Contrat prepare et relu par le juriste, si l'interrupteur est arme.</td></tr>
				<tr><td>question</td><td>interesse</td><td>Rien d'automatique. Vous etes prevenu : c'est a vous de repondre.</td></tr>
				<tr><td>interesse</td><td>interesse</td><td>Idem — « ca m'interesse » n'est pas un oui.</td></tr>
				<tr><td>plus_tard</td><td>relance</td><td>Sort de la sequence, revient dans les relances.</td></tr>
				<tr><td>refus</td><td>refus</td><td>Plus jamais demarche.</td></tr>
				<tr><td>stop</td><td>ne plus contacter</td><td>Opposition enregistree, reconnue meme si l'IA est en panne.</td></tr>
			</table>
		</div>
		<?php
	}
}
