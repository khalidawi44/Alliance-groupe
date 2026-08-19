<?php
/**
 * The template for displaying the footer.
 *
 * @package AG_Starter_Domicile
 */

?>

<footer class="ag-site-footer" role="contentinfo">
	<div class="ag-container">
		<div class="ag-footer-grid">
			<div class="ag-footer-col">
				<h3><?php esc_html_e( 'Zone d’intervention', 'ag-gwen-services' ); ?></h3>
				<p>
					<strong><?php echo esc_html( ag_domicile_opt( 'ag_domicile_footer_name', 'Gwen Services' ) ); ?></strong><br>
					<?php echo nl2br( esc_html( ag_domicile_opt( 'ag_domicile_footer_address', 'Nantes et alentours (44)' ) ) ); ?><br>
					<span class="ag-footer-note"><?php esc_html_e( 'Aide à domicile · crédit d’impôt 50 %', 'ag-gwen-services' ); ?></span>
				</p>
			</div>
			<div class="ag-footer-col">
				<h3><?php esc_html_e( 'Horaires', 'ag-gwen-services' ); ?></h3>
				<p>
					<strong><?php echo esc_html( ag_domicile_opt( 'ag_domicile_footer_hours', '7j/7 — de 07h à 21h' ) ); ?></strong><br>
					<span class="ag-footer-note"><?php esc_html_e( 'Interventions matin, soir & garde de nuit', 'ag-gwen-services' ); ?></span>
				</p>
			</div>
			<div class="ag-footer-col">
				<h3><?php esc_html_e( 'Contact', 'ag-gwen-services' ); ?></h3>
				<p>
					<?php
					$ag_f_phone = ag_domicile_opt( 'ag_domicile_footer_phone', '06 26 14 28 45' );
					$ag_f_email = ag_domicile_opt( 'ag_domicile_footer_email', '' );
					if ( $ag_f_phone ) :
						?><a class="ag-footer-phone" href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $ag_f_phone ) ); ?>"><?php echo esc_html( $ag_f_phone ); ?></a><br><?php
					endif;
					if ( $ag_f_email ) :
						?><?php esc_html_e( 'Email : ', 'ag-gwen-services' ); ?><a href="mailto:<?php echo esc_attr( $ag_f_email ); ?>"><?php echo esc_html( $ag_f_email ); ?></a><br><?php
					endif;
					?>
					<a class="ag-footer-cta" href="<?php echo esc_url( function_exists( 'ag_domicile_resolve_cta_url' ) ? ag_domicile_resolve_cta_url( '/devis/' ) : home_url( '/devis/' ) ); ?>"><?php esc_html_e( 'Demander un devis gratuit →', 'ag-gwen-services' ); ?></a>
				</p>
			</div>
		</div>
		<div class="ag-footer-bottom">
			<?php if ( function_exists( 'ag_domicile_credit' ) ) ag_domicile_credit(); ?>
		</div>
	</div>
</footer>

<style>
.ag-site-footer{background:radial-gradient(120% 120% at 85% -20%,rgba(79,157,107,.14),transparent 55%),linear-gradient(180deg,#12160f,#0f120c);border-top:1px solid rgba(111,191,138,.22);color:#c7c1b0;padding:clamp(46px,6vw,72px) 0 26px;margin-top:0;}
.ag-site-footer .ag-footer-grid{gap:2rem;}
.ag-site-footer .ag-footer-col h3{color:#8fd0a3;font-family:"Fraunces",Georgia,serif;font-size:.82rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;margin:0 0 12px;}
.ag-site-footer .ag-footer-col p{color:#c7c1b0;font-size:.98rem;line-height:1.7;margin:0;}
.ag-site-footer .ag-footer-col strong{color:#f6f1e4;font-weight:700;}
.ag-site-footer .ag-footer-note{color:#8f8a7c;font-size:.86rem;}
.ag-site-footer .ag-footer-phone{display:inline-block;color:#8fd0a3;font-size:1.5rem;font-weight:800;text-decoration:none;letter-spacing:.02em;margin-bottom:6px;transition:color .2s ease;}
.ag-site-footer .ag-footer-phone:hover{color:#b7e6c4;}
.ag-site-footer .ag-footer-cta{display:inline-block;margin-top:12px;background:linear-gradient(120deg,#2f7d54,#4f9d6b 55%,#6fbf8a);color:#fff;font-weight:800;font-size:.98rem;text-decoration:none;padding:13px 26px;border-radius:100px;box-shadow:0 14px 30px -14px rgba(47,125,84,.8);transition:transform .2s ease,filter .2s ease;}
.ag-site-footer .ag-footer-cta:hover{transform:translateY(-2px);filter:brightness(1.05);}
.ag-site-footer .ag-footer-bottom{border-top:1px solid rgba(111,191,138,.14);margin-top:34px;padding-top:20px;text-align:center;}
.ag-site-footer .ag-footer-bottom,.ag-site-footer .ag-credit small{color:#7d7869;font-size:.82rem;}
.ag-site-footer .ag-credit a{color:#8f8a7c;text-decoration:none;}
.ag-site-footer .ag-credit a:hover{color:#8fd0a3;}
@media(prefers-reduced-motion:reduce){.ag-site-footer .ag-footer-cta,.ag-site-footer .ag-footer-phone{transition:none;}}
</style>

<script type="application/ld+json">
{"@context":"https://schema.org","@type":"LocalBusiness","@id":"https://gwen-services.alliancegroupe-inc.com/#business","name":"Gwen Services","description":"Aide à domicile à Nantes : accompagnement des personnes âgées, aide au handicap léger et garde d'enfants (1 mois–8 ans). Crédit d'impôt de 50 %.","url":"https://gwen-services.alliancegroupe-inc.com","telephone":"+33626142845","areaServed":"Nantes","address":{"@type":"PostalAddress","addressLocality":"Nantes","postalCode":"44000","addressCountry":"FR"},"openingHoursSpecification":[{"@type":"OpeningHoursSpecification","dayOfWeek":["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"],"opens":"07:00","closes":"21:00"}],"priceRange":"€€"}
</script>

<script>
// Fix : detecte l'URL active et ajoute la classe 'current-menu-item' sur
// les items du menu qui matchent. Necessaire quand les items du menu
// sont en URL custom au lieu d'etre lies a un objet WP page.
(function () {
	var currentPath = window.location.pathname.replace(/\/+$/, '') || '/';
	var menus = document.querySelectorAll( '.ag-primary-menu li, .ag-primary-nav li' );
	Array.prototype.forEach.call( menus, function ( li ) {
		var a = li.querySelector( 'a' );
		if ( ! a ) return;
		var aPath;
		try { aPath = new URL( a.href ).pathname.replace(/\/+$/, '') || '/'; }
		catch ( e ) { aPath = ( a.getAttribute( 'href' ) || '' ).replace(/\/+$/, '') || '/'; }
		if ( aPath === currentPath ) {
			li.classList.add( 'current-menu-item' );
			li.classList.add( 'current_page_item' );
		}
	} );
})();
</script>

<?php wp_footer(); ?>
</body>
</html>
