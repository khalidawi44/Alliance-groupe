<?php
/**
 * LE DIRECTEUR — un seul interrupteur pour toute la chaîne
 * ---------------------------------------------------------------------------
 * Fabrice veut que tout tourne de A à Z sans lui. Ce module donne UN bouton
 * qui arme la chaîne entière — mais un maître-interrupteur honnête n'allume
 * que ce qui est RÉELLEMENT prêt, et dit en clair ce qui manque.
 *
 * La chaîne, dans l'ordre :
 *   Matteo (chasse Places) → Compléter les fiches (email + constat) →
 *   Hugo (démarchage) → Alessia (relève boîte + qualifie) →
 *   Enzo (négociation) → Camille (contrat) → Margot (signature).
 *
 * ─────────────────────────────────────────────────────────────────────────
 * LE SEUL VERROU QUE LE DIRECTEUR NE FORCE PAS.
 *
 * L'envoi d'un contrat À SIGNER dépend de `ag_sign_contrat_relu` : « le modèle
 * de contrat a été relu par un professionnel du droit ». Ce booléen n'est pas
 * une préférence, c'est une AFFIRMATION que seul Fabrice peut faire — je ne
 * peux pas cocher à sa place qu'un avocat a lu le document, ce serait inventer
 * une relecture qui n'a pas eu lieu sur une pièce contractuelle.
 *
 * Donc : le Directeur arme TOUT le reste. La partie « envoyer à signer +
 * contresigner » ne s'arme QUE si ce verrou est déjà coché. Sinon elle reste
 * en attente, et l'écran le dit — la chaîne va jusqu'à la négociation, et
 * s'arrête à la porte de la signature jusqu'à cette relecture.
 *
 * @package alliance-groupe-theme
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! defined( 'AG_DIR_VER' ) ) { define( 'AG_DIR_VER', '1.0.0' ); }

/* ── 1. Le contrôle avant vol ────────────────────────────────────────── */

if ( ! function_exists( 'ag_dir_etat' ) ) {
	/**
	 * L'état réel de chaque poste et de chaque dépendance. Rien n'est supposé :
	 * chaque ligne interroge le code ou une option.
	 *
	 * @return array chaque poste => array( actif, pret, manque[] )
	 */
	function ag_dir_etat() {
		$ia    = function_exists( 'ag_ia_ready' ) && ag_ia_ready();
		$smtp  = function_exists( 'ag_smtp_on' ) && ag_smtp_on();
		$places= '' !== trim( (string) get_option( 'ag_places_key', '' ) );
		$cibles= count( (array) get_option( 'ag_auto_searches', array() ) ) > 0;
		$imap  = function_exists( 'ag_boite_dispo' ) && ( true === ag_boite_dispo() );
		$relu  = function_exists( 'ag_sign_pret' ) && ag_sign_pret();

		$e = array();

		$e['smtp'] = array(
			'titre'  => 'Envoi des e-mails (SMTP)',
			'actif'  => $smtp,
			'pret'   => $smtp,
			'manque' => $smtp ? array() : array( 'Activer l\'envoi authentifié (écran « ✉️ Envoi des e-mails »).' ),
			'socle'  => true,
		);
		$e['ia'] = array(
			'titre'  => 'Rédaction par IA',
			'actif'  => $ia,
			'pret'   => $ia,
			'manque' => $ia ? array() : array( 'Sans clé IA, les messages retombent sur un gabarit sobre (ça marche, mais c\'est plat).' ),
			'socle'  => true,
		);

		$e['chasse'] = array(
			'titre'  => 'Matteo — chasse de prospects (Places)',
			'actif'  => 'off' !== get_option( 'ag_auto_freq', 'off' ) && 'weekly-default' !== get_option( 'ag_auto_freq', 'off' ),
			'pret'   => $places && $cibles,
			'manque' => array_filter( array(
				$places ? '' : 'Clé Google Places manquante.',
				$cibles ? '' : 'Aucune recherche enregistrée (ajoute au moins une ville dans « Agent automatique »).',
			) ),
		);
		// « actif » lu proprement : la chasse tourne si ag_auto_freq n'est pas off.
		$e['chasse']['actif'] = in_array( get_option( 'ag_auto_freq', 'off' ), array( 'daily', 'weekly' ), true );

		$e['enrich'] = array(
			'titre'  => 'Compléter les fiches (email + constat)',
			'actif'  => function_exists( 'ag_enrich_on' ) && ag_enrich_on(),
			'pret'   => true, // ne dépend de rien d'externe : relit les sites
			'manque' => array(),
		);
		$e['closer'] = array(
			'titre'  => 'Hugo — démarchage',
			'actif'  => function_exists( 'ag_closer_on' ) && ag_closer_on(),
			'pret'   => $smtp,
			'manque' => $smtp ? array() : array( 'L\'envoi des e-mails doit être actif d\'abord.' ),
		);
		$e['boite'] = array(
			'titre'  => 'Alessia — relève de la boîte',
			'actif'  => function_exists( 'ag_boite_on' ) && ag_boite_on(),
			'pret'   => $imap,
			'manque' => $imap ? array() : array( 'Extension IMAP indisponible sur le serveur.' ),
		);
		$e['nego'] = array(
			'titre'  => 'Enzo — négociation',
			'actif'  => function_exists( 'ag_nego_on' ) && ag_nego_on(),
			'pret'   => $smtp,
			'manque' => $smtp ? array() : array( 'L\'envoi des e-mails doit être actif d\'abord.' ),
		);
		$e['relance'] = array(
			'titre'  => 'Relance du chaud (contrats non signés, intéressés)',
			'actif'  => function_exists( 'ag_rc_on' ) && ag_rc_on(),
			'pret'   => $smtp,
			'manque' => $smtp ? array() : array( 'L\'envoi des e-mails doit être actif d\'abord.' ),
		);

		$e['signature'] = array(
			'titre'  => 'Camille + Margot — contrat & signature automatiques',
			'actif'  => ( function_exists( 'ag_reponses_contrat_auto' ) && ag_reponses_contrat_auto() )
				&& ( function_exists( 'ag_sign_contresigne_auto' ) && ag_sign_contresigne_auto() ),
			'pret'   => $relu && $smtp,
			'manque' => array_filter( array(
				$relu ? '' : 'VERROU : le modèle de contrat n\'est pas coché « relu par un professionnel du droit ».',
				$smtp ? '' : 'L\'envoi des e-mails doit être actif.',
			) ),
			'verrou' => ! $relu,
		);

		return $e;
	}
}

/* ── 2. Tout allumer / tout éteindre ─────────────────────────────────── */

if ( ! function_exists( 'ag_dir_tout_allumer' ) ) {
	/**
	 * Arme tout ce qui est prêt. Ce qui n'est pas prêt reste éteint et est
	 * signalé. La signature ne s'arme QUE si le verrou de relecture est levé.
	 *
	 * @return array arme[], attente[]
	 */
	function ag_dir_tout_allumer() {
		$etat = ag_dir_etat();
		$arme = array(); $attente = array();

		// Matteo : passe en quotidien s'il est prêt.
		if ( $etat['chasse']['pret'] ) {
			update_option( 'ag_auto_freq', 'daily' );
			if ( ! wp_next_scheduled( 'ag_prospect_cron' ) ) {
				wp_schedule_event( time() + 600, 'daily', 'ag_prospect_cron' );
			}
			$arme[] = $etat['chasse']['titre'];
		} else { $attente['chasse'] = $etat['chasse']['manque']; }

		// Compléter les fiches.
		update_option( 'ag_enrich_on', 1, false );
		$arme[] = $etat['enrich']['titre'];

		// Alessia.
		if ( $etat['boite']['pret'] ) {
			update_option( 'ag_boite_on', 1, false );
			$arme[] = $etat['boite']['titre'];
		} else { $attente['boite'] = $etat['boite']['manque']; }

		// Hugo.
		if ( $etat['closer']['pret'] ) {
			update_option( 'ag_closer_on', 1, false );
			$arme[] = $etat['closer']['titre'];
		} else { $attente['closer'] = $etat['closer']['manque']; }

		// Enzo.
		if ( $etat['nego']['pret'] ) {
			update_option( 'ag_nego_on', 1, false );
			$arme[] = $etat['nego']['titre'];
		} else { $attente['nego'] = $etat['nego']['manque']; }

		// Relance du chaud.
		if ( $etat['relance']['pret'] ) {
			update_option( 'ag_rc_on', 1, false );
			$arme[] = $etat['relance']['titre'];
		} else { $attente['relance'] = $etat['relance']['manque']; }

		// Signature : SEULEMENT si le verrou de relecture est levé.
		if ( $etat['signature']['pret'] ) {
			update_option( 'ag_reponses_contrat_auto', 1, false );
			update_option( 'ag_sign_auto_contresigne', 1, false );
			$arme[] = $etat['signature']['titre'];
		} else { $attente['signature'] = $etat['signature']['manque']; }

		update_option( 'ag_dir_allume_le', time(), false );
		if ( function_exists( 'ag_activity_log' ) ) {
			ag_activity_log( '▶️ Directeur : chaîne armée (' . count( $arme ) . ' postes).' );
		}
		return array( 'arme' => $arme, 'attente' => $attente );
	}
}

if ( ! function_exists( 'ag_dir_tout_eteindre' ) ) {
	/** Coupe tout d'un coup. Le bouton d'arrêt d'urgence. */
	function ag_dir_tout_eteindre() {
		update_option( 'ag_auto_freq', 'off' );
		$n = wp_next_scheduled( 'ag_prospect_cron' ); if ( $n ) { wp_unschedule_event( $n, 'ag_prospect_cron' ); }
		foreach ( array( 'ag_enrich_on', 'ag_boite_on', 'ag_closer_on', 'ag_nego_on', 'ag_rc_on',
			'ag_reponses_contrat_auto', 'ag_sign_auto_contresigne' ) as $opt ) {
			update_option( $opt, 0, false );
		}
		update_option( 'ag_dir_eteint_le', time(), false );
		if ( function_exists( 'ag_activity_log' ) ) { ag_activity_log( '⏹️ Directeur : tout coupé.' ); }
	}
}

/* ── 3. L'écran ──────────────────────────────────────────────────────── */

add_action( 'admin_menu', function () {
	add_submenu_page(
		'ag-hub', 'Le Directeur', '🎛️ Le Directeur',
		'manage_options', 'ag-directeur', 'ag_dir_ecran'
	);
}, 28 );

if ( ! function_exists( 'ag_dir_ecran' ) ) {
	function ag_dir_ecran() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		$flash = null;

		if ( isset( $_POST['ag_dir_on'] ) && check_admin_referer( 'ag_dir' ) ) {
			$flash = ag_dir_tout_allumer();
		}
		if ( isset( $_POST['ag_dir_off'] ) && check_admin_referer( 'ag_dir' ) ) {
			ag_dir_tout_eteindre();
			$flash = 'off';
		}

		$etat = ag_dir_etat();
		?>
		<div class="wrap">
			<h1>🎛️ Le Directeur</h1>
			<p class="description" style="max-width:60em">
				Un seul endroit pour voir toute la chaîne, et un seul bouton pour la lancer. Le Directeur
				n'allume que ce qui est <strong>réellement prêt</strong> : ce qui manque reste éteint et vous
				est dit franchement, plutôt que de faire semblant de tourner.
			</p>

			<?php if ( 'off' === $flash ) : ?>
				<div class="notice notice-warning"><p><strong>Tout est coupé.</strong> Plus rien ne part.</p></div>
			<?php elseif ( is_array( $flash ) ) : ?>
				<div class="notice notice-success"><p>
					<strong><?php echo count( $flash['arme'] ); ?> poste(s) armé(s).</strong>
					<?php if ( $flash['arme'] ) : ?><br>▶️ <?php echo esc_html( implode( ' · ', $flash['arme'] ) ); ?><?php endif; ?>
				</p></div>
				<?php if ( ! empty( $flash['attente'] ) ) : ?>
					<div class="notice notice-warning"><p><strong>En attente (non armé) :</strong></p>
						<ul style="list-style:disc;padding-left:26px;margin:0 0 8px">
						<?php foreach ( $flash['attente'] as $cle => $raisons ) : ?>
							<li><strong><?php echo esc_html( $etat[ $cle ]['titre'] ); ?></strong> —
								<?php echo esc_html( implode( ' ', (array) $raisons ) ); ?></li>
						<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>
			<?php endif; ?>

			<h2>La chaîne, poste par poste</h2>
			<table class="widefat striped" style="max-width:70em">
				<thead><tr><th style="width:38%">Poste</th><th style="width:14%">État</th><th>Prêt à tourner ?</th></tr></thead>
				<tbody>
				<?php foreach ( $etat as $c ) : ?>
					<tr>
						<td><strong><?php echo esc_html( $c['titre'] ); ?></strong>
							<?php if ( ! empty( $c['socle'] ) ) : ?><br><small style="color:#8a8a94">socle : nécessaire aux autres</small><?php endif; ?></td>
						<td><?php echo ! empty( $c['actif'] )
							? '<span style="color:#1a7f37;font-weight:600">en marche</span>'
							: '<span style="color:#8a8a94">arrêté</span>'; ?></td>
						<td><?php
							if ( ! empty( $c['pret'] ) ) {
								echo '<span style="color:#1a7f37">prêt</span>';
							} elseif ( ! empty( $c['verrou'] ) ) {
								echo '<strong style="color:#b26b00">🔒 verrou : ' . esc_html( implode( ' ', (array) $c['manque'] ) ) . '</strong>';
							} else {
								echo '<span style="color:#b32d2e">' . esc_html( implode( ' ', (array) $c['manque'] ) ) . '</span>';
							}
						?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>

			<form method="post" style="margin-top:22px">
				<?php wp_nonce_field( 'ag_dir' ); ?>
				<button class="button button-primary button-hero" name="ag_dir_on" value="1"
					onclick="return confirm('Armer toute la chaîne prête ? De vrais messages partiront vers de vraies entreprises.');">
					▶️ Tout allumer (ce qui est prêt)</button>
				<button class="button button-hero" name="ag_dir_off" value="1" style="margin-left:12px"
					onclick="return confirm('Tout couper ?');">⏹️ Arrêt d'urgence</button>
			</form>

			<h2 style="margin-top:26px">Ce que « tout seul » veut dire, et sa limite</h2>
			<ul style="max-width:60em;list-style:disc;padding-left:22px">
				<li>Matteo trouve les entreprises, la nuit les fiches se complètent (email + constat vrai),
					Hugo démarche, Alessia lit les réponses, Enzo négocie. <strong>Tout ça sans vous.</strong></li>
				<li><strong>La seule porte que le Directeur ne force pas</strong> est la signature : un contrat
					ne part à signer que si le modèle est coché <em>« relu par un professionnel du droit »</em>
					(écran ✍️ Contrats signés). Je ne peux pas cocher à votre place qu'un avocat l'a lu — ce
					serait affirmer une relecture qui n'a pas eu lieu sur un document contractuel.</li>
				<li>Une fois ce verrou levé par vous, la chaîne va <strong>de l'entreprise repérée au contrat
					scellé</strong>, sans intervention.</li>
				<li><strong>L'arrêt d'urgence coupe tout d'un coup</strong>, à tout moment.</li>
			</ul>
		</div>
		<?php
	}
}
