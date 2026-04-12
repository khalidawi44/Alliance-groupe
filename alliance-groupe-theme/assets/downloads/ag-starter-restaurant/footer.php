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
					<?php esc_html_e( '[Votre Restaurant]', 'ag-starter-restaurant' ); ?><br>
					<?php esc_html_e( '123 rue de la Gastronomie', 'ag-starter-restaurant' ); ?><br>
					<?php esc_html_e( '75001 Paris, France', 'ag-starter-restaurant' ); ?>
				</p>
			</div>
			<div class="ag-footer-col">
				<h3><?php esc_html_e( 'Horaires', 'ag-starter-restaurant' ); ?></h3>
				<ul>
					<li><?php esc_html_e( 'Lundi - Vendredi : 12h - 14h / 19h - 22h', 'ag-starter-restaurant' ); ?></li>
					<li><?php esc_html_e( 'Samedi : 19h - 23h', 'ag-starter-restaurant' ); ?></li>
					<li><?php esc_html_e( 'Dimanche : ferme', 'ag-starter-restaurant' ); ?></li>
				</ul>
			</div>
			<div class="ag-footer-col">
				<h3><?php esc_html_e( 'Contact', 'ag-starter-restaurant' ); ?></h3>
				<p>
					<?php esc_html_e( 'Telephone : 01 23 45 67 89', 'ag-starter-restaurant' ); ?><br>
					<?php esc_html_e( 'Email : contact@votrerestaurant.fr', 'ag-starter-restaurant' ); ?>
				</p>
			</div>
		</div>
		<div class="ag-footer-bottom">
			<?php
			$ag_custom_copy = ag_starter_restaurant_get_option( 'ag_footer_copyright' );
			if ( $ag_custom_copy ) :
				?>
				<p><?php echo esc_html( $ag_custom_copy ); ?></p>
			<?php else : ?>
				<p>
					&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>.
					<?php esc_html_e( 'Tous droits reserves.', 'ag-starter-restaurant' ); ?>
				</p>
			<?php endif; ?>
			<?php if ( ag_starter_restaurant_get_option( 'ag_footer_credits' ) ) : ?>
				<p>
					<?php
					/* translators: %s: Alliance Group website link. */
					printf( wp_kses( __( 'Theme gratuit par %s', 'ag-starter-restaurant' ), array( 'a' => array( 'href' => array(), 'rel' => array() ) ) ), '<a href="https://alliancegroupe-inc.com/templates-wordpress" rel="nofollow">Alliance Group</a>' );
					?>
				</p>
			<?php endif; ?>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
