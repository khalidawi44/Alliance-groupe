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
				<h3><?php esc_html_e( 'Adresse', 'ag-gwen-services' ); ?></h3>
				<p>
					<strong><?php echo esc_html( ag_domicile_opt( 'ag_domicile_footer_name', 'Douceur de Vie' ) ); ?></strong><br>
					<?php echo nl2br( esc_html( ag_domicile_opt( 'ag_domicile_footer_address', '' ) ) ); ?>
				</p>
			</div>
			<div class="ag-footer-col">
				<h3><?php esc_html_e( 'Horaires', 'ag-gwen-services' ); ?></h3>
				<p><?php echo nl2br( esc_html( ag_domicile_opt( 'ag_domicile_footer_hours', '' ) ) ); ?></p>
			</div>
			<div class="ag-footer-col">
				<h3><?php esc_html_e( 'Contact', 'ag-gwen-services' ); ?></h3>
				<p>
					<?php
					$ag_f_phone = ag_domicile_opt( 'ag_domicile_footer_phone', '' );
					$ag_f_email = ag_domicile_opt( 'ag_domicile_footer_email', '' );
					if ( $ag_f_phone ) :
						?><?php esc_html_e( 'Telephone : ', 'ag-gwen-services' ); ?><a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $ag_f_phone ) ); ?>"><?php echo esc_html( $ag_f_phone ); ?></a><br><?php
					endif;
					if ( $ag_f_email ) :
						?><?php esc_html_e( 'Email : ', 'ag-gwen-services' ); ?><a href="mailto:<?php echo esc_attr( $ag_f_email ); ?>"><?php echo esc_html( $ag_f_email ); ?></a><?php
					endif;
					?>
				</p>
			</div>
		</div>
		<div class="ag-footer-bottom">
			<?php if ( function_exists( 'ag_domicile_credit' ) ) ag_domicile_credit(); ?>
		</div>
	</div>
</footer>

<script type="application/ld+json">
{"@context":"https://schema.org","@type":"Organization","name":"Alliance Groupe","url":"https://alliancegroupe-inc.com","logo":"https://alliancegroupe-inc.com/wp-content/themes/alliance-groupe-theme/assets/images/logo.jpg","description":"Agence web & IA franco-italo-marocaine. Création de sites WordPress sur-mesure pour les services à la personne et l'aide à domicile.","areaServed":["FR","MA","IT"]}
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
