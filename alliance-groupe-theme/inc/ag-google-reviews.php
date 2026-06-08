<?php
/**
 * Google Avis Clients (Google Customer Reviews).
 *
 * 1) Opt-in (enquête) : à appeler sur la page de confirmation de commande
 *    (/merci-achat) via ag_google_reviews_optin(). Déclenche l'invitation
 *    Google à noter l'achat. Champs : merchant_id, order_id, email,
 *    delivery_country, estimated_delivery_date.
 * 2) Badge : widget « Avis clients » affiché en pied de page (wp_footer),
 *    désactivable via l'option ag_google_reviews_badge (1 par défaut).
 *
 * merchant_id : option ag_google_merchant_id (défaut 5795317386).
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** ID marchand Google Merchant Center. */
function ag_google_merchant_id() {
	return (string) get_option( 'ag_google_merchant_id', '5795317386' );
}

/**
 * Opt-in « Avis clients » — à placer sur la page de confirmation d'achat.
 * Récupère ce qu'on peut depuis l'URL (?order=, ?email=, ?country=) sinon
 * valeurs par défaut (produits numériques = livraison immédiate).
 */
function ag_google_reviews_optin( $args = array() ) {
	$mid = ag_google_merchant_id();
	if ( '' === $mid ) {
		return;
	}

	$order = isset( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	if ( '' === $order && isset( $_GET['txn'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$order = sanitize_text_field( wp_unslash( $_GET['txn'] ) );
	}
	if ( '' === $order ) {
		$order = 'AG-' . gmdate( 'YmdHis' ) . '-' . wp_rand( 100, 999 );
	}

	$email = isset( $_GET['email'] ) ? sanitize_email( wp_unslash( $_GET['email'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$country = isset( $_GET['country'] ) ? strtoupper( substr( sanitize_text_field( wp_unslash( $_GET['country'] ) ), 0, 2 ) ) : 'FR'; // phpcs:ignore WordPress.Security.NonceVerification

	// Produits numériques : livraison immédiate -> date du jour.
	$delivery = gmdate( 'Y-m-d' );

	$args = wp_parse_args( $args, array(
		'order_id' => $order,
		'email'    => $email,
		'country'  => $country,
		'delivery' => $delivery,
	) );
	?>
	<!-- Google Avis clients : opt-in enquête -->
	<script src="https://apis.google.com/js/platform.js?onload=renderOptIn" async defer></script>
	<script>
	window.renderOptIn = function () {
		window.gapi.load('surveyoptin', function () {
			window.gapi.surveyoptin.render({
				"merchant_id": <?php echo (int) $mid; ?>,
				"order_id": "<?php echo esc_js( $args['order_id'] ); ?>",
				"email": "<?php echo esc_js( $args['email'] ); ?>",
				"delivery_country": "<?php echo esc_js( $args['country'] ); ?>",
				"estimated_delivery_date": "<?php echo esc_js( $args['delivery'] ); ?>"
			});
		});
	};
	</script>
	<?php
}

/** Badge « Avis clients » en pied de page (toutes les pages). */
function ag_google_reviews_badge() {
	if ( ! get_option( 'ag_google_reviews_badge', 1 ) ) {
		return;
	}
	$mid = ag_google_merchant_id();
	if ( '' === $mid ) {
		return;
	}
	?>
	<!-- Google Avis clients : badge -->
	<script id="merchantWidgetScript" src="https://www.gstatic.com/shopping/merchant/merchantwidget.js" defer></script>
	<script>
	(function () {
		var s = document.getElementById('merchantWidgetScript');
		if (!s) return;
		s.addEventListener('load', function () {
			if (typeof merchantwidget !== 'undefined') {
				merchantwidget.start({
					merchant_id: <?php echo (int) $mid; ?>,
					position: 'BOTTOM_RIGHT'
				});
			}
		});
	})();
	</script>
	<?php
}
add_action( 'wp_footer', 'ag_google_reviews_badge', 99 );
