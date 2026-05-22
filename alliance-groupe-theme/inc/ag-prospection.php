<?php
/**
 * Alliance Groupe — Prospection.
 * Capture des prospects depuis le chat du site (AJAX) + page admin "Prospects".
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ── Réception d'un prospect (chat) ─────────────────────────────── */
if ( ! function_exists( 'ag_lead_handler' ) ) {
	function ag_lead_handler() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'ag_lead' ) ) {
			wp_send_json_error( 'nonce', 400 );
		}
		if ( ! empty( $_POST['company'] ) ) { wp_send_json_success(); } // pot de miel anti-bot
		$lead = array(
			'name'     => sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) ),
			'email'    => sanitize_email( wp_unslash( $_POST['email'] ?? '' ) ),
			'phone'    => sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) ),
			'interest' => sanitize_text_field( wp_unslash( $_POST['interest'] ?? '' ) ),
			'message'  => sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) ),
			'date'     => current_time( 'd/m/Y H:i' ),
			'ts'       => time(),
		);
		if ( ! $lead['email'] && ! $lead['phone'] ) { wp_send_json_error( 'contact', 400 ); }

		$leads = (array) get_option( 'ag_leads', array() );
		$leads[] = $lead;
		update_option( 'ag_leads', array_slice( $leads, -1000 ) );

		$to = apply_filters( 'ag_calendar_notify_email', 'advise.alliance.group@gmail.com' );
		wp_mail(
			$to,
			'🎯 Nouveau prospect (chat) : ' . ( $lead['name'] ?: $lead['email'] ?: $lead['phone'] ),
			"Prospect via le chat du site.\nIntérêt : " . $lead['interest'] . "\nNom : " . $lead['name'] . "\nEmail : " . $lead['email'] . "\nTéléphone : " . $lead['phone'] . "\nMessage : " . $lead['message'] . "\nDate : " . $lead['date']
		);
		if ( function_exists( 'ag_calendar_notify' ) ) {
			ag_calendar_notify( '🎯 Prospect à rappeler : ' . ( $lead['name'] ?: $lead['email'] ?: $lead['phone'] ), 'Intérêt : ' . $lead['interest'] . "\nEmail : " . $lead['email'] . "\nTél : " . $lead['phone'] . "\n" . $lead['message'] );
		}
		wp_send_json_success();
	}
}
add_action( 'wp_ajax_nopriv_ag_lead', 'ag_lead_handler' );
add_action( 'wp_ajax_ag_lead', 'ag_lead_handler' );

/* ── Page admin : Prospects ─────────────────────────────────────── */
add_action( 'admin_menu', function () {
	add_menu_page( 'Prospects', 'Prospects', 'manage_options', 'ag-prospects', 'ag_prospects_render', 'dashicons-groups', 30 );
} );
if ( ! function_exists( 'ag_prospects_render' ) ) {
	function ag_prospects_render() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$leads = array_reverse( (array) get_option( 'ag_leads', array() ) );
		echo '<div class="wrap"><h1>Prospects (chat du site)</h1>';
		if ( empty( $leads ) ) {
			echo '<p>Aucun prospect pour l\'instant. Ils apparaîtront ici dès qu\'un visiteur laisse ses coordonnées dans le chat.</p></div>';
			return;
		}
		echo '<table class="widefat striped"><thead><tr><th>Date</th><th>Nom</th><th>Email</th><th>Téléphone</th><th>Intérêt</th><th>Message</th></tr></thead><tbody>';
		foreach ( $leads as $l ) {
			echo '<tr>'
				. '<td>' . esc_html( $l['date'] ?? '' ) . '</td>'
				. '<td>' . esc_html( $l['name'] ?? '' ) . '</td>'
				. '<td>' . ( ! empty( $l['email'] ) ? '<a href="mailto:' . esc_attr( $l['email'] ) . '">' . esc_html( $l['email'] ) . '</a>' : '' ) . '</td>'
				. '<td>' . ( ! empty( $l['phone'] ) ? '<a href="tel:' . esc_attr( $l['phone'] ) . '">' . esc_html( $l['phone'] ) . '</a>' : '' ) . '</td>'
				. '<td>' . esc_html( $l['interest'] ?? '' ) . '</td>'
				. '<td>' . esc_html( $l['message'] ?? '' ) . '</td>'
				. '</tr>';
		}
		echo '</tbody></table></div>';
	}
}
