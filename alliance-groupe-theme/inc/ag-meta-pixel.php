<?php
/**
 * AG Meta Pixel — pixel Meta (Facebook / Instagram) avec Consent Mode RGPD.
 *
 * Permet de faire de la publicité Meta vers les pages d'offres du site sans
 * dépendre d'un catalogue Commerce Manager (les catalogues Meta visent des
 * produits physiques ; nos offres sont des services et des fichiers).
 *
 * Réglages → Meta Pixel : coller l'ID du pixel (Gestionnaire d'événements Meta).
 * Tant que l'ID est vide, RIEN n'est émis sur le site.
 *
 * Consentement : le pixel démarre en `consent revoke` (aucun cookie) et ne
 * passe en `grant` qu'après consentement « marketing », en réutilisant le même
 * mécanisme que le Consent Mode Google du thème (localStorage
 * `ag_cookie_consent` + événement `ag:consent`).
 *
 * Événements émis :
 *   - PageView      : toutes les pages (après consentement)
 *   - ViewContent   : pages d'offres, avec content_ids + value + currency
 *   - Lead          : envoi d'un formulaire de contact, clic tel: ou mailto:
 *   - InitiateCheckout : clic sur un lien de paiement (PayPal / Stripe)
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ── ID du pixel ─────────────────────────────────────────────────────── */
if ( ! function_exists( 'ag_meta_pixel_id' ) ) {
	function ag_meta_pixel_id() {
		$id = trim( (string) apply_filters( 'ag_meta_pixel_id', get_option( 'ag_meta_pixel_id', '' ) ) );
		// Un ID de pixel Meta est une suite de chiffres (15-16 caractères).
		$id = preg_replace( '/[^0-9]/', '', $id );
		return $id;
	}
}

/* ── Réglages → Meta Pixel ───────────────────────────────────────────── */
add_action( 'admin_menu', function () {
	add_options_page(
		'Meta Pixel',
		'Meta Pixel',
		'manage_options',
		'ag-meta-pixel',
		'ag_meta_pixel_settings_page'
	);
} );

add_action( 'admin_init', function () {
	register_setting( 'ag_meta_pixel', 'ag_meta_pixel_id', array(
		'type'              => 'string',
		'sanitize_callback' => function ( $v ) { return preg_replace( '/[^0-9]/', '', (string) $v ); },
		'default'           => '',
	) );
} );

if ( ! function_exists( 'ag_meta_pixel_settings_page' ) ) {
	function ag_meta_pixel_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$id = ag_meta_pixel_id();
		?>
		<div class="wrap">
			<h1>Meta Pixel</h1>
			<p>
				Collez ici l'ID du pixel Meta (Gestionnaire d'événements →
				Sources de données → votre pixel → ID). Tant que ce champ est
				vide, aucun script Meta n'est chargé sur le site.
			</p>
			<form method="post" action="options.php">
				<?php settings_fields( 'ag_meta_pixel' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="ag_meta_pixel_id">ID du pixel</label></th>
						<td>
							<input type="text" id="ag_meta_pixel_id" name="ag_meta_pixel_id"
								value="<?php echo esc_attr( $id ); ?>" class="regular-text"
								inputmode="numeric" placeholder="1234567890123456">
							<p class="description">Chiffres uniquement.</p>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
			<h2>État</h2>
			<p>
				<?php if ( $id ) : ?>
					✅ Pixel actif — <code><?php echo esc_html( $id ); ?></code>.
					Le pixel respecte le consentement : aucun cookie avant acceptation du volet « marketing ».
				<?php else : ?>
					⚠️ Aucun pixel configuré. Rien n'est émis sur le site public.
				<?php endif; ?>
			</p>
			<h2>Flux catalogue</h2>
			<p>
				Le jour où le portefeuille d'entreprises Meta est débloqué, le
				catalogue peut être alimenté automatiquement depuis&nbsp;:
				<code><?php echo esc_url( home_url( '/meta-catalog-feed.xml' ) ); ?></code>
				(Commerce Manager → Catalogue → Sources de données → Flux de
				données → récupération programmée).
			</p>
		</div>
		<?php
	}
}

/* ── Correspondance page → produits du catalogue ─────────────────────── */
if ( ! function_exists( 'ag_meta_pixel_view_content' ) ) {
	/**
	 * Retourne array( 'ids' => array, 'value' => float ) pour la page courante,
	 * ou un tableau vide si la page ne correspond à aucune offre.
	 */
	function ag_meta_pixel_view_content() {
		$path = trim( (string) wp_parse_url( add_query_arg( array() ), PHP_URL_PATH ), '/' );
		$path = strtolower( $path );

		$map = array(
			'sites-express'         => array( array( 'express-essentiel', 'express-pro', 'express-boutique' ), 490 ),
			'maintenance'           => array( array( 'maintenance-serenite', 'maintenance-croissance', 'maintenance-performance' ), 29 ),
			'ambassadeurs'          => array( array( 'zone-ambassadeur' ), 49 ),
			'wordpress-avocat'      => array( array( 'tpl-premium-avocat' ), 69 ),
			'wordpress-restaurant'  => array( array( 'tpl-premium-restaurant' ), 69 ),
			'wordpress-artisan'     => array( array( 'tpl-premium-artisan' ), 69 ),
			'wordpress-coach'       => array( array( 'tpl-premium-coach' ), 69 ),
			'wordpress-barber'      => array( array( 'tpl-premium-barber' ), 69 ),
			'wordpress-association' => array( array( 'tpl-premium-association' ), 69 ),
			'resilience-ransomware' => array( array( 'securite-resilience' ), 490 ),
			'tester-mon-site'       => array( array( 'securite-audit' ), 490 ),
		);

		$map = apply_filters( 'ag_meta_pixel_view_content_map', $map );

		if ( ! isset( $map[ $path ] ) ) {
			return array();
		}

		return array(
			'ids'   => (array) $map[ $path ][0],
			'value' => (float) $map[ $path ][1],
		);
	}
}

/* ── Code de base du pixel (wp_head) ─────────────────────────────────── */
add_action( 'wp_head', function () {
	$id = ag_meta_pixel_id();
	if ( '' === $id ) {
		return;
	}
	$vc = ag_meta_pixel_view_content();
	?>
<!-- Meta Pixel — Alliance Groupe, déclenché après consentement marketing -->
<script>
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
document,'script','https://connect.facebook.net/en_US/fbevents.js');
(function(){
  var marketing=false;
  try{var c=JSON.parse(localStorage.getItem('ag_cookie_consent')||'null');if(c&&c.marketing)marketing=true;}catch(e){}
  fbq('consent', marketing ? 'grant' : 'revoke');
  fbq('init','<?php echo esc_js( $id ); ?>');
  fbq('track','PageView');
<?php if ( ! empty( $vc ) ) : ?>
  fbq('track','ViewContent',{content_ids:<?php echo wp_json_encode( $vc['ids'] ); ?>,content_type:'product',value:<?php echo esc_js( $vc['value'] ); ?>,currency:'EUR'});
<?php endif; ?>
  document.addEventListener('ag:consent',function(e){
    var d=e.detail||{};
    fbq('consent', d.marketing ? 'grant' : 'revoke');
  });
})();
</script>
<noscript><img height="1" width="1" style="display:none" alt=""
src="https://www.facebook.com/tr?id=<?php echo esc_attr( $id ); ?>&ev=PageView&noscript=1"></noscript>
	<?php
}, 2 );

/* ── Événements de conversion (wp_footer) ────────────────────────────── */
add_action( 'wp_footer', function () {
	if ( '' === ag_meta_pixel_id() ) {
		return;
	}
	?>
<script>
(function(){
  function agFb(ev,params){ if(typeof fbq!=='function')return; try{fbq('track',ev,params||{});}catch(e){} }
  // Envoi d'un formulaire de contact
  document.addEventListener('submit',function(e){
    var f=e.target; if(!f||!f.matches)return;
    var path=(location.pathname||'').toLowerCase();
    if(f.matches('.wpcf7-form, form.ag-form, #ag-contact-form, form[action*="contact"]') || path.indexOf('contact')>-1){
      agFb('Lead',{content_name:document.title,currency:'EUR',value:1});
    }
  },true);
  // Clic téléphone / email
  document.addEventListener('click',function(e){
    var a=e.target.closest?e.target.closest('a[href^="tel:"],a[href^="mailto:"]'):null;
    if(a){ agFb('Lead',{content_name:'contact_direct',currency:'EUR',value:1}); }
  },true);
  // Clic sur un lien de paiement
  document.addEventListener('click',function(e){
    var a=e.target.closest?e.target.closest('a[href*="paypal.com"],a[href*="paypal.me"],a[href*="stripe.com"],a[href*="buy.stripe"]'):null;
    if(a){ agFb('InitiateCheckout',{content_name:document.title,currency:'EUR'}); }
  },true);
})();
</script>
	<?php
}, 99 );
