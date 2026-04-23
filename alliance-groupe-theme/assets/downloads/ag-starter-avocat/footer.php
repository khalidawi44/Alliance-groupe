<?php
/**
 * The template for displaying the footer.
 *
 * @package AG_Starter_Avocat
 */

?>

<footer class="ag-site-footer" role="contentinfo">
	<div class="ag-container">
		<div class="ag-footer-grid">
			<div class="ag-footer-col">
				<h3><?php esc_html_e( 'Cabinet', 'ag-starter-avocat' ); ?></h3>
				<p>
					<?php esc_html_e( '[Votre cabinet]', 'ag-starter-avocat' ); ?><br>
					<?php esc_html_e( '15 boulevard du Palais', 'ag-starter-avocat' ); ?><br>
					<?php esc_html_e( '75001 Paris, France', 'ag-starter-avocat' ); ?>
				</p>
			</div>
			<div class="ag-footer-col">
				<h3><?php esc_html_e( 'Horaires', 'ag-starter-avocat' ); ?></h3>
				<ul>
					<li><?php esc_html_e( 'Lundi - Vendredi : 9h - 19h', 'ag-starter-avocat' ); ?></li>
					<li><?php esc_html_e( 'Samedi : sur rendez-vous', 'ag-starter-avocat' ); ?></li>
					<li><?php esc_html_e( 'Visio disponible', 'ag-starter-avocat' ); ?></li>
				</ul>
			</div>
			<div class="ag-footer-col">
				<h3><?php esc_html_e( 'Contact', 'ag-starter-avocat' ); ?></h3>
				<p>
					<?php esc_html_e( 'Telephone : 01 23 45 67 89', 'ag-starter-avocat' ); ?><br>
					<?php esc_html_e( 'Email : contact@votre-cabinet.fr', 'ag-starter-avocat' ); ?>
				</p>
			</div>
		</div>
		<div class="ag-footer-bottom">
			<p>
				&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>.
				<?php esc_html_e( 'Tous droits reserves.', 'ag-starter-avocat' ); ?>
			</p>
		</div>
	</div>
</footer>

<?php
// AG branding (rendered by pro-features.php based on tier)
// This replaces the old static credit — see render_footer_branding()
?>

<?php wp_footer(); ?>
</body>
</html>
