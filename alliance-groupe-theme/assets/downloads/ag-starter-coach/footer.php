<?php
/**
 * The template for displaying the footer.
 *
 * @package AG_Starter_Coach
 */

?>

<footer class="ag-site-footer" role="contentinfo">
	<div class="ag-container">
		<div class="ag-footer-grid">
			<div class="ag-footer-col">
				<h3><?php esc_html_e( 'Cabinet', 'ag-starter-coach' ); ?></h3>
				<p>
					<?php esc_html_e( '[Votre cabinet]', 'ag-starter-coach' ); ?><br>
					<?php esc_html_e( '3 rue de la Confiance', 'ag-starter-coach' ); ?><br>
					<?php esc_html_e( '75001 Paris, France', 'ag-starter-coach' ); ?>
				</p>
			</div>
			<div class="ag-footer-col">
				<h3><?php esc_html_e( 'Horaires', 'ag-starter-coach' ); ?></h3>
				<ul>
					<li><?php esc_html_e( 'Lundi - Vendredi : 9h - 19h', 'ag-starter-coach' ); ?></li>
					<li><?php esc_html_e( 'Samedi : sur rendez-vous', 'ag-starter-coach' ); ?></li>
					<li><?php esc_html_e( 'Visio disponible', 'ag-starter-coach' ); ?></li>
				</ul>
			</div>
			<div class="ag-footer-col">
				<h3><?php esc_html_e( 'Contact', 'ag-starter-coach' ); ?></h3>
				<p>
					<?php esc_html_e( 'Telephone : 06 00 00 00 00', 'ag-starter-coach' ); ?><br>
					<?php esc_html_e( 'Email : contact@votre-cabinet.fr', 'ag-starter-coach' ); ?>
				</p>
			</div>
		</div>
		<div class="ag-footer-bottom">
			<p>
				&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>.
				<?php esc_html_e( 'Tous droits reserves.', 'ag-starter-coach' ); ?>
			</p>
			<p>
				<?php
				/* translators: %s: Alliance Group website link. */
				printf( esc_html__( 'Theme gratuit par %s', 'ag-starter-coach' ), '<a href="https://alliancegroupe-inc.com" rel="nofollow">Alliance Group</a>' );
				?>
			</p>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
