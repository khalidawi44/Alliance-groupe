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
					<?php echo nl2br( esc_html( ag_starter_avocat_get_option( 'ag_cabinet_address' ) ) ); ?>
				</p>
			</div>
			<div class="ag-footer-col">
				<h3><?php esc_html_e( 'Horaires', 'ag-starter-avocat' ); ?></h3>
				<p><?php echo nl2br( esc_html( ag_starter_avocat_get_option( 'ag_cabinet_hours' ) ) ); ?></p>
			</div>
			<div class="ag-footer-col">
				<h3><?php esc_html_e( 'Contact', 'ag-starter-avocat' ); ?></h3>
				<p>
					<?php $phone = ag_starter_avocat_get_option( 'ag_cabinet_phone' ); if ( $phone ) : ?>
						<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a><br>
					<?php endif; ?>
					<?php $email = ag_starter_avocat_get_option( 'ag_cabinet_email' ); if ( $email ) : ?>
						<a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
					<?php endif; ?>
				</p>
				<a href="<?php echo esc_url( home_url( '/rendez-vous/' ) ); ?>" class="ag-footer-rdv"><?php esc_html_e( 'Prendre rendez-vous →', 'ag-starter-avocat' ); ?></a>
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
