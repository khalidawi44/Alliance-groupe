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

	// Pas d'email dans l'URL ? On récupère la commande la plus récente
	// enregistrée par le webhook PayPal (file ag_gcr_pending) et on la
	// « consomme » pour lancer l'enquête avec le vrai email de l'acheteur.
	if ( '' === $email ) {
		$list = get_option( 'ag_gcr_pending', array() );
		if ( is_array( $list ) && ! empty( $list ) ) {
			$now = time();
			for ( $i = count( $list ) - 1; $i >= 0; $i-- ) {
				$e = $list[ $i ];
				if ( empty( $e['used'] ) && ( $now - (int) ( $e['time'] ?? 0 ) ) < 1800 && ! empty( $e['email'] ) ) {
					$email   = sanitize_email( $e['email'] );
					$order   = ! empty( $e['order'] ) ? $e['order'] : $order;
					$country = ! empty( $e['country'] ) ? $e['country'] : $country;
					$list[ $i ]['used'] = 1;
					update_option( 'ag_gcr_pending', $list, false );
					break;
				}
			}
		}
	}

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

/**
 * Enregistre chaque paiement PayPal vérifié (email + n° de transaction) dans
 * une file courte. La page /merci-achat la consomme pour déclencher l'enquête
 * Google Avis clients avec le vrai email de l'acheteur (que PayPal ne passe
 * pas à la page de retour).
 */
function ag_gcr_record_order( $amount, $email, $txn, $type = '', $resource = array() ) {
	// On ne compte que les paiements réellement encaissés.
	if ( '' !== (string) $type && 'PAYMENT.CAPTURE.COMPLETED' !== $type ) {
		return;
	}
	$email = sanitize_email( (string) $email );
	if ( '' === $email ) {
		return;
	}

	$country = 'FR';
	if ( is_array( $resource ) ) {
		$cc = '';
		if ( ! empty( $resource['payer']['address']['country_code'] ) ) {
			$cc = $resource['payer']['address']['country_code'];
		} elseif ( ! empty( $resource['shipping']['address']['country_code'] ) ) {
			$cc = $resource['shipping']['address']['country_code'];
		}
		if ( $cc ) {
			$country = strtoupper( substr( (string) $cc, 0, 2 ) );
		}
	}

	$list = get_option( 'ag_gcr_pending', array() );
	if ( ! is_array( $list ) ) {
		$list = array();
	}
	$now  = time();
	// Purge les entrées de plus de 30 min.
	$list = array_values( array_filter( $list, function ( $e ) use ( $now ) {
		return ( $now - (int) ( $e['time'] ?? 0 ) ) < 1800;
	} ) );
	$list[] = array(
		'order'   => (string) $txn !== '' ? (string) $txn : ( 'AG-' . gmdate( 'YmdHis' ) ),
		'email'   => $email,
		'country' => $country,
		'time'    => $now,
		'used'    => 0,
	);
	if ( count( $list ) > 30 ) {
		$list = array_slice( $list, -30 );
	}
	update_option( 'ag_gcr_pending', $list, false );
}
add_action( 'ag_paypal_payment_verified', 'ag_gcr_record_order', 30, 5 );
