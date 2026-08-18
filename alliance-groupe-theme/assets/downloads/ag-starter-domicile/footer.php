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
				<h3><?php esc_html_e( 'Adresse', 'ag-starter-domicile' ); ?></h3>
				<p>
					<?php esc_html_e( 'Douceur de Vie', 'ag-starter-domicile' ); ?><br>
					<?php esc_html_e( '1 rue des Tilleuls', 'ag-starter-domicile' ); ?><br>
					<?php esc_html_e( '44000 Nantes, France', 'ag-starter-domicile' ); ?>
				</p>
			</div>
			<div class="ag-footer-col">
				<h3><?php esc_html_e( 'Horaires', 'ag-starter-domicile' ); ?></h3>
				<ul>
					<li><?php esc_html_e( 'Interventions 7j/7, jour et nuit', 'ag-starter-domicile' ); ?></li>
					<li><?php esc_html_e( 'Accueil tel. : Lun-Ven 8h-19h', 'ag-starter-domicile' ); ?></li>
					<li><?php esc_html_e( 'Samedi sur rendez-vous', 'ag-starter-domicile' ); ?></li>
				</ul>
			</div>
			<div class="ag-footer-col">
				<h3><?php esc_html_e( 'Contact', 'ag-starter-domicile' ); ?></h3>
				<p>
					<?php esc_html_e( 'Telephone : 06 00 00 00 00', 'ag-starter-domicile' ); ?><br>
					<?php esc_html_e( 'Email : contact@douceur-de-vie.fr', 'ag-starter-domicile' ); ?>
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
