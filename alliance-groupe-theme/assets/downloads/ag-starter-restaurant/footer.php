<?php
/**
 * The template for displaying the footer.
 *
 * @package AG_Starter_Restaurant
 */

?>

<footer class="ag-site-footer" role="contentinfo">
	<div class="ag-container">
		<div class="ag-footer-grid">
			<div class="ag-footer-col">
				<h3><?php esc_html_e( 'Adresse', 'ag-starter-restaurant' ); ?></h3>
				<p>
					<?php esc_html_e( '[Votre entreprise]', 'ag-starter-restaurant' ); ?><br>
					<?php esc_html_e( '12 rue des Restaurants', 'ag-starter-restaurant' ); ?><br>
					<?php esc_html_e( '75001 Paris, France', 'ag-starter-restaurant' ); ?>
				</p>
			</div>
			<div class="ag-footer-col">
				<h3><?php esc_html_e( 'Horaires', 'ag-starter-restaurant' ); ?></h3>
				<ul>
					<li><?php esc_html_e( 'Lundi - Vendredi : 8h - 18h', 'ag-starter-restaurant' ); ?></li>
					<li><?php esc_html_e( 'Samedi : 9h - 12h', 'ag-starter-restaurant' ); ?></li>
					<li><?php esc_html_e( 'Urgences 7/7', 'ag-starter-restaurant' ); ?></li>
				</ul>
			</div>
			<div class="ag-footer-col">
				<h3><?php esc_html_e( 'Contact', 'ag-starter-restaurant' ); ?></h3>
				<p>
					<?php esc_html_e( 'Telephone : 06 00 00 00 00', 'ag-starter-restaurant' ); ?><br>
					<?php esc_html_e( 'Email : contact@votre-entreprise.fr', 'ag-starter-restaurant' ); ?>
				</p>
			</div>
		</div>
		<div class="ag-footer-bottom">
			<?php if ( function_exists( 'ag_restaurant_credit' ) ) ag_restaurant_credit(); ?>
		</div>
	</div>
</footer>

<script type="application/ld+json">
{"@context":"https://schema.org","@type":"Organization","name":"Alliance Groupe","url":"https://alliancegroupe-inc.com","logo":"https://alliancegroupe-inc.com/wp-content/themes/alliance-groupe-theme/assets/images/logo.jpg","description":"Agence web & IA franco-italo-marocaine. Sites WordPress sur-mesure pour restaurants, menuisiers, plombiers, électriciens.","areaServed":["FR","MA","IT"]}
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
