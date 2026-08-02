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
				<h3><?php esc_html_e( 'Firm', 'ag-starter-avocat' ); ?></h3>
				<p>
					<?php echo nl2br( esc_html( ag_starter_avocat_get_option( 'ag_cabinet_address' ) ) ); ?>
				</p>
			</div>
			<div class="ag-footer-col">
				<h3><?php esc_html_e( 'Hours', 'ag-starter-avocat' ); ?></h3>
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
				<a href="<?php echo esc_url( ag_page_url( 'rendez-vous' ) ); ?>" class="ag-footer-rdv"><?php esc_html_e( 'Book an appointment', 'ag-starter-avocat' ); ?></a>
			</div>
		</div>
		<?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
			<div class="ag-footer-widgets">
				<?php dynamic_sidebar( 'footer-1' ); ?>
			</div>
		<?php endif; ?>
		<?php
		// ─── Bandeau déontologie / RGPD (template « déontologie-ready ») ───
		// Liens légaux obligatoires + rappels secret professionnel, hébergement
		// UE et absence de cookie de traçage. Chaque mention est personnalisable
		// via le Personnalisateur ; les liens ne s'affichent que si la page
		// correspondante existe (mentions-legales, confidentialite, cookies).
		$ag_legal_links = array(
			'mentions-legales'  => __( 'Legal notice', 'ag-starter-avocat' ),
			'confidentialite'   => __( 'Privacy policy (GDPR)', 'ag-starter-avocat' ),
			'cookies'           => __( 'Cookies', 'ag-starter-avocat' ),
		);
		$ag_barreau   = ag_avocat_opt( 'ag_maitre_barreau', '' );
		$ag_heberg    = ag_avocat_opt( 'ag_avocat_hebergement_note', __( 'Hosting in the European Union — data protected by attorney-client privilege.', 'ag-starter-avocat' ) );
		$ag_nocookie  = ag_avocat_opt( 'ag_avocat_nocookie_note', __( 'This site does not use any advertising tracking cookies.', 'ag-starter-avocat' ) );
		?>
		<div class="ag-footer-deonto">
			<nav class="ag-footer-legal" aria-label="<?php esc_attr_e( 'Legal information', 'ag-starter-avocat' ); ?>">
				<?php
				$ag_legal_out = array();
				foreach ( $ag_legal_links as $ag_slug => $ag_label ) {
					$ag_p = get_page_by_path( $ag_slug );
					if ( $ag_p ) {
						$ag_legal_out[] = '<a href="' . esc_url( get_permalink( $ag_p ) ) . '">' . esc_html( $ag_label ) . '</a>';
					}
				}
				echo wp_kses_post( implode( '<span class="ag-sep" aria-hidden="true">·</span>', $ag_legal_out ) );
				?>
			</nav>
			<p class="ag-footer-deonto__notes">
				<?php if ( $ag_barreau ) : ?>
					<span class="ag-deonto-item">⚖️ <?php echo esc_html( sprintf( /* translators: %s = nom du barreau */ __( 'Attorney admitted to the %s', 'ag-starter-avocat' ), $ag_barreau ) ); ?></span>
				<?php endif; ?>
				<?php if ( $ag_heberg ) : ?>
					<span class="ag-deonto-item">🇪🇺 <?php echo esc_html( $ag_heberg ); ?></span>
				<?php endif; ?>
				<?php if ( $ag_nocookie ) : ?>
					<span class="ag-deonto-item">🔒 <?php echo esc_html( $ag_nocookie ); ?></span>
				<?php endif; ?>
			</p>
		</div>
		<div class="ag-footer-bottom">
			<p>
				&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>.
				<?php esc_html_e( 'All rights reserved.', 'ag-starter-avocat' ); ?>
			</p>
			<?php if ( function_exists( 'ag_avocat_credit' ) ) ag_avocat_credit(); ?>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
