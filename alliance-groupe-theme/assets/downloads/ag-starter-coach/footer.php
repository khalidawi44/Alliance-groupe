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
			<?php
			$ag_custom_copy = ag_starter_coach_get_option( 'ag_footer_copyright' );
			if ( $ag_custom_copy ) :
				?>
				<p><?php echo esc_html( $ag_custom_copy ); ?></p>
			<?php else : ?>
				<p>
					&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>.
					<?php esc_html_e( 'Tous droits reserves.', 'ag-starter-coach' ); ?>
				</p>
			<?php endif; ?>
			<?php if ( ag_starter_coach_get_option( 'ag_footer_credits' ) ) : ?>
				<p>
					<?php
					/* translators: %s: Alliance Group website link. */
					printf( wp_kses( __( 'Theme gratuit par %s', 'ag-starter-coach' ), array( 'a' => array( 'href' => array(), 'rel' => array() ) ) ), '<a href="https://alliancegroupe-inc.com/templates-wordpress" rel="nofollow">Alliance Group</a>' );
					?>
				</p>
			<?php endif; ?>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
