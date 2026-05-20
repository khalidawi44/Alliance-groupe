<?php
/**
 * Programme Ambassadeurs — système autonome (sans Claude).
 *
 * - Inscription publique au programme (par email) -> validee par l'admin.
 * - Declaration de ventes par les ambassadeurs.
 * - Tableau de bord admin : ambassadeurs, ventes, commissions 10%, paiements
 *   (PayPal ou autre).
 *
 * Donnees stockees dans des options WP :
 *   ag_ambassadeurs        => liste des inscrits
 *   ag_ambassadeur_ventes  => liste des ventes declarees
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'AG_COMMISSION_RATE' ) ) {
	define( 'AG_COMMISSION_RATE', 0.10 ); // 10 % par vente
}

/* =====================================================================
   1. INSCRIPTION AU PROGRAMME
   ===================================================================== */
add_action( 'admin_post_nopriv_ag_ambassadeur_signup', 'ag_ambassadeur_signup' );
add_action( 'admin_post_ag_ambassadeur_signup', 'ag_ambassadeur_signup' );

if ( ! function_exists( 'ag_ambassadeur_signup' ) ) {
	function ag_ambassadeur_signup() {
		if ( ! isset( $_POST['ag_amb_nonce'] ) || ! wp_verify_nonce( $_POST['ag_amb_nonce'], 'ag_amb_signup' ) ) {
			wp_die( 'Nonce invalide.', 'Erreur', array( 'response' => 403 ) );
		}
		$name    = sanitize_text_field( $_POST['name']    ?? '' );
		$email   = sanitize_email(      $_POST['email']   ?? '' );
		$phone   = sanitize_text_field( $_POST['phone']   ?? '' );
		$city    = sanitize_text_field( $_POST['city']    ?? '' );
		$payout  = sanitize_text_field( $_POST['payout_method'] ?? 'PayPal' );
		$payout_id = sanitize_text_field( $_POST['payout_id'] ?? '' );
		$motiv   = sanitize_textarea_field( $_POST['motivation'] ?? '' );

		if ( empty( $name ) || ! is_email( $email ) ) {
			wp_die( 'Merci d\'indiquer au minimum un nom et un email valide.', 'Champs manquants', array( 'response' => 400, 'back_link' => true ) );
		}

		$list = get_option( 'ag_ambassadeurs', array() );
		if ( ! is_array( $list ) ) $list = array();

		// evite les doublons d'email
		foreach ( $list as $a ) {
			if ( isset( $a['email'] ) && strtolower( $a['email'] ) === strtolower( $email ) ) {
				wp_safe_redirect( add_query_arg( array( 'ambassadeur' => 'deja' ), home_url( '/ambassadeurs' ) ) . '#rejoindre' );
				exit;
			}
		}

		$list[] = array(
			'id'         => uniqid( 'amb_' ),
			'name'       => $name,
			'email'      => $email,
			'phone'      => $phone,
			'city'       => $city,
			'payout'     => $payout,
			'payout_id'  => $payout_id,
			'motivation' => $motiv,
			'status'     => 'en_attente',
			'date'       => current_time( 'd/m/Y H:i' ),
		);
		update_option( 'ag_ambassadeurs', $list );

		// Notif admin
		$body  = "Nouvelle inscription Programme Ambassadeurs\n\n";
		$body .= "Nom : $name\nEmail : $email\nTel : $phone\nVille : $city\n";
		$body .= "Paiement : $payout ($payout_id)\n\nMotivation :\n$motiv\n\n";
		$body .= 'Date : ' . current_time( 'd/m/Y H:i' );
		wp_mail( 'contact@alliancegroupe-inc.com', 'Nouvel ambassadeur : ' . $name, $body );

		// Confirmation candidat
		$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
		$c  = "Bonjour $name,\n\n";
		$c .= "Merci de vouloir rejoindre le programme de vente d'Alliance Groupe ! 🤝\n\n";
		$c .= "Tu touches 10% sur chaque vente que tu realises. On valide ton inscription ";
		$c .= "et on te recontacte rapidement avec tes outils de vente.\n\n";
		$c .= "L'equipe Alliance Groupe\ncontact@alliancegroupe-inc.com";
		wp_mail( $email, 'Bienvenue dans le programme ambassadeurs 🤝', $c, $headers );

		wp_safe_redirect( add_query_arg( array( 'ambassadeur' => 'ok' ), home_url( '/ambassadeurs' ) ) . '#rejoindre' );
		exit;
	}
}

/* =====================================================================
   2. DECLARATION D'UNE VENTE
   ===================================================================== */
add_action( 'admin_post_nopriv_ag_ambassadeur_vente', 'ag_ambassadeur_vente' );
add_action( 'admin_post_ag_ambassadeur_vente', 'ag_ambassadeur_vente' );

if ( ! function_exists( 'ag_ambassadeur_vente' ) ) {
	function ag_ambassadeur_vente() {
		if ( ! isset( $_POST['ag_vente_nonce'] ) || ! wp_verify_nonce( $_POST['ag_vente_nonce'], 'ag_amb_vente' ) ) {
			wp_die( 'Nonce invalide.', 'Erreur', array( 'response' => 403 ) );
		}
		$email   = sanitize_email(      $_POST['email']   ?? '' );
		$client  = sanitize_text_field( $_POST['client']  ?? '' );
		$activite= sanitize_text_field( $_POST['activite']?? '' );
		$montant = (float) str_replace( ',', '.', preg_replace( '/[^0-9.,]/', '', $_POST['montant'] ?? '0' ) );

		if ( ! is_email( $email ) || empty( $client ) || $montant <= 0 ) {
			wp_die( 'Merci d\'indiquer ton email, le client et un montant valide.', 'Champs manquants', array( 'response' => 400, 'back_link' => true ) );
		}

		// retrouve l'ambassadeur (nom) si inscrit
		$amb_name = '';
		foreach ( get_option( 'ag_ambassadeurs', array() ) as $a ) {
			if ( isset( $a['email'] ) && strtolower( $a['email'] ) === strtolower( $email ) ) {
				$amb_name = $a['name'];
				break;
			}
		}

		$ventes = get_option( 'ag_ambassadeur_ventes', array() );
		if ( ! is_array( $ventes ) ) $ventes = array();
		$ventes[] = array(
			'id'         => uniqid( 'v_' ),
			'email'      => $email,
			'name'       => $amb_name,
			'client'     => $client,
			'activite'   => $activite,
			'montant'    => $montant,
			'commission' => round( $montant * AG_COMMISSION_RATE, 2 ),
			'statut'     => 'declaree', // declaree -> validee -> payee
			'date'       => current_time( 'd/m/Y H:i' ),
			'date_paiement' => '',
		);
		update_option( 'ag_ambassadeur_ventes', $ventes );

		$body  = "Nouvelle vente declaree\n\n";
		$body .= "Ambassadeur : " . ( $amb_name ? $amb_name : '(non inscrit)' ) . " <$email>\n";
		$body .= "Client : $client\nActivite : $activite\n";
		$body .= 'Montant : ' . number_format( $montant, 2, ',', ' ' ) . " EUR\n";
		$body .= 'Commission (10%) : ' . number_format( $montant * AG_COMMISSION_RATE, 2, ',', ' ' ) . " EUR\n";
		$body .= 'Date : ' . current_time( 'd/m/Y H:i' );
		wp_mail( 'contact@alliancegroupe-inc.com', 'Vente declaree : ' . $client, $body );

		wp_safe_redirect( add_query_arg( array( 'vente' => 'ok' ), home_url( '/ambassadeurs' ) ) . '#declarer' );
		exit;
	}
}

/* =====================================================================
   3. TABLEAU DE BORD ADMIN
   ===================================================================== */
add_action( 'admin_menu', function () {
	add_menu_page(
		'Ambassadeurs',
		'Ambassadeurs',
		'manage_options',
		'ag-ambassadeurs',
		'ag_render_ambassadeurs_page',
		'dashicons-groups',
		28
	);
} );

if ( ! function_exists( 'ag_render_ambassadeurs_page' ) ) {
	function ag_render_ambassadeurs_page() {
		if ( ! current_user_can( 'manage_options' ) ) return;

		// --- Actions admin (validees par nonce) ---
		if ( isset( $_GET['ag_action'], $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'ag_amb_admin' ) ) {
			$act = sanitize_key( $_GET['ag_action'] );
			$id  = sanitize_text_field( $_GET['id'] ?? '' );

			if ( $act === 'amb_valider' || $act === 'amb_suppr' ) {
				$list = get_option( 'ag_ambassadeurs', array() );
				foreach ( $list as $k => $a ) {
					if ( $a['id'] === $id ) {
						if ( $act === 'amb_valider' ) $list[ $k ]['status'] = 'actif';
						if ( $act === 'amb_suppr' )   unset( $list[ $k ] );
					}
				}
				update_option( 'ag_ambassadeurs', array_values( $list ) );
			}

			if ( in_array( $act, array( 'v_valider', 'v_payer', 'v_suppr' ), true ) ) {
				$ventes = get_option( 'ag_ambassadeur_ventes', array() );
				foreach ( $ventes as $k => $v ) {
					if ( $v['id'] === $id ) {
						if ( $act === 'v_valider' ) $ventes[ $k ]['statut'] = 'validee';
						if ( $act === 'v_payer' )  { $ventes[ $k ]['statut'] = 'payee'; $ventes[ $k ]['date_paiement'] = current_time( 'd/m/Y' ); }
						if ( $act === 'v_suppr' )  unset( $ventes[ $k ] );
					}
				}
				update_option( 'ag_ambassadeur_ventes', array_values( $ventes ) );
			}
			echo '<div class="notice notice-success is-dismissible"><p>Action effectuée.</p></div>';
		}

		$ambs   = array_reverse( get_option( 'ag_ambassadeurs', array() ) );
		$ventes = array_reverse( get_option( 'ag_ambassadeur_ventes', array() ) );

		// --- Synthese commissions par ambassadeur ---
		$synthese = array();
		foreach ( get_option( 'ag_ambassadeur_ventes', array() ) as $v ) {
			$key = strtolower( $v['email'] );
			if ( ! isset( $synthese[ $key ] ) ) {
				$synthese[ $key ] = array( 'name' => $v['name'] ?: $v['email'], 'email' => $v['email'], 'ventes' => 0, 'ca' => 0, 'due' => 0, 'payee' => 0 );
			}
			if ( in_array( $v['statut'], array( 'validee', 'payee' ), true ) ) {
				$synthese[ $key ]['ventes'] += 1;
				$synthese[ $key ]['ca']     += (float) $v['montant'];
			}
			if ( $v['statut'] === 'validee' ) $synthese[ $key ]['due']   += (float) $v['commission'];
			if ( $v['statut'] === 'payee' )   $synthese[ $key ]['payee'] += (float) $v['commission'];
		}
		uasort( $synthese, function ( $a, $b ) { return $b['ca'] <=> $a['ca']; } );

		$eur = function ( $n ) { return number_format( (float) $n, 2, ',', ' ' ) . ' €'; };
		$nonce_url = function ( $act, $id ) {
			return wp_nonce_url( admin_url( 'admin.php?page=ag-ambassadeurs&ag_action=' . $act . '&id=' . $id ), 'ag_amb_admin' );
		};

		echo '<div class="wrap"><h1>Programme Ambassadeurs</h1>';
		echo '<p>Commission : <strong>' . esc_html( (int) ( AG_COMMISSION_RATE * 100 ) ) . '%</strong> par vente. Les commissions « à payer » correspondent aux ventes validées non encore payées.</p>';

		// SYNTHESE
		echo '<h2>💰 Commissions par ambassadeur</h2>';
		if ( empty( $synthese ) ) {
			echo '<p>Aucune vente pour le moment.</p>';
		} else {
			echo '<table class="widefat striped"><thead><tr><th>Ambassadeur</th><th>Email</th><th>Ventes</th><th>CA généré</th><th>Commission à payer</th><th>Déjà payé</th></tr></thead><tbody>';
			$tot_due = 0; $tot_ca = 0;
			foreach ( $synthese as $s ) {
				$tot_due += $s['due']; $tot_ca += $s['ca'];
				echo '<tr><td><strong>' . esc_html( $s['name'] ) . '</strong></td>';
				echo '<td><a href="mailto:' . esc_attr( $s['email'] ) . '">' . esc_html( $s['email'] ) . '</a></td>';
				echo '<td>' . (int) $s['ventes'] . '</td>';
				echo '<td>' . esc_html( $eur( $s['ca'] ) ) . '</td>';
				echo '<td><strong style="color:#b8860b;">' . esc_html( $eur( $s['due'] ) ) . '</strong></td>';
				echo '<td>' . esc_html( $eur( $s['payee'] ) ) . '</td></tr>';
			}
			echo '<tr style="font-weight:700;background:#f6f7f7;"><td colspan="3">TOTAL</td><td>' . esc_html( $eur( $tot_ca ) ) . '</td><td>' . esc_html( $eur( $tot_due ) ) . '</td><td></td></tr>';
			echo '</tbody></table>';
		}

		// VENTES
		echo '<h2 style="margin-top:30px;">🧾 Ventes déclarées</h2>';
		if ( empty( $ventes ) ) {
			echo '<p>Aucune vente.</p>';
		} else {
			echo '<table class="widefat striped"><thead><tr><th>Date</th><th>Ambassadeur</th><th>Client</th><th>Activité</th><th>Montant</th><th>Commission</th><th>Statut</th><th>Actions</th></tr></thead><tbody>';
			foreach ( $ventes as $v ) {
				$badge = array( 'declaree' => '#999', 'validee' => '#2271b1', 'payee' => '#46b450' );
				$col = $badge[ $v['statut'] ] ?? '#999';
				echo '<tr>';
				echo '<td>' . esc_html( $v['date'] ) . '</td>';
				echo '<td>' . esc_html( $v['name'] ?: $v['email'] ) . '</td>';
				echo '<td>' . esc_html( $v['client'] ) . '</td>';
				echo '<td>' . esc_html( $v['activite'] ) . '</td>';
				echo '<td>' . esc_html( $eur( $v['montant'] ) ) . '</td>';
				echo '<td>' . esc_html( $eur( $v['commission'] ) ) . '</td>';
				echo '<td><span style="color:#fff;background:' . esc_attr( $col ) . ';padding:2px 8px;border-radius:10px;font-size:11px;">' . esc_html( strtoupper( $v['statut'] ) ) . '</span>';
				if ( $v['statut'] === 'payee' && ! empty( $v['date_paiement'] ) ) echo '<br><small>le ' . esc_html( $v['date_paiement'] ) . '</small>';
				echo '</td>';
				echo '<td>';
				if ( $v['statut'] === 'declaree' ) echo '<a class="button button-small" href="' . esc_url( $nonce_url( 'v_valider', $v['id'] ) ) . '">Valider</a> ';
				if ( $v['statut'] === 'validee' )  echo '<a class="button button-primary button-small" href="' . esc_url( $nonce_url( 'v_payer', $v['id'] ) ) . '">Marquer payé</a> ';
				echo '<a class="button button-small" style="color:#b32d2e;" href="' . esc_url( $nonce_url( 'v_suppr', $v['id'] ) ) . '" onclick="return confirm(\'Supprimer cette vente ?\')">✕</a>';
				echo '</td></tr>';
			}
			echo '</tbody></table>';
		}

		// AMBASSADEURS
		echo '<h2 style="margin-top:30px;">🤝 Ambassadeurs inscrits</h2>';
		if ( empty( $ambs ) ) {
			echo '<p>Aucun inscrit.</p>';
		} else {
			echo '<table class="widefat striped"><thead><tr><th>Date</th><th>Nom</th><th>Email</th><th>Tél</th><th>Ville</th><th>Paiement</th><th>Statut</th><th>Actions</th></tr></thead><tbody>';
			foreach ( $ambs as $a ) {
				echo '<tr>';
				echo '<td>' . esc_html( $a['date'] ?? '' ) . '</td>';
				echo '<td><strong>' . esc_html( $a['name'] ?? '' ) . '</strong></td>';
				echo '<td><a href="mailto:' . esc_attr( $a['email'] ?? '' ) . '">' . esc_html( $a['email'] ?? '' ) . '</a></td>';
				echo '<td>' . esc_html( $a['phone'] ?? '' ) . '</td>';
				echo '<td>' . esc_html( $a['city'] ?? '' ) . '</td>';
				echo '<td>' . esc_html( ( $a['payout'] ?? '' ) . ' ' . ( $a['payout_id'] ?? '' ) ) . '</td>';
				$actif = ( ( $a['status'] ?? '' ) === 'actif' );
				echo '<td>' . ( $actif ? '<span style="color:#46b450;font-weight:700;">ACTIF</span>' : '<span style="color:#999;">en attente</span>' ) . '</td>';
				echo '<td>';
				if ( ! $actif ) echo '<a class="button button-primary button-small" href="' . esc_url( $nonce_url( 'amb_valider', $a['id'] ) ) . '">Activer</a> ';
				echo '<a class="button button-small" style="color:#b32d2e;" href="' . esc_url( $nonce_url( 'amb_suppr', $a['id'] ) ) . '" onclick="return confirm(\'Supprimer cet ambassadeur ?\')">✕</a>';
				echo '</td></tr>';
			}
			echo '</tbody></table>';
		}

		echo '</div>';
	}
}
