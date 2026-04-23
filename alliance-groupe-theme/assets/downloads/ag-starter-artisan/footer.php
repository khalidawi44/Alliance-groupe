<?php
/**
 * The template for displaying the footer.
 *
 * @package AG_Starter_Artisan
 */

?>

<footer class="ag-site-footer" role="contentinfo">
	<div class="ag-container">
		<div class="ag-footer-grid">
			<div class="ag-footer-col">
				<h3><?php esc_html_e( 'Adresse', 'ag-starter-artisan' ); ?></h3>
				<p>
					<?php esc_html_e( '[Votre entreprise]', 'ag-starter-artisan' ); ?><br>
					<?php esc_html_e( '12 rue des Artisans', 'ag-starter-artisan' ); ?><br>
					<?php esc_html_e( '75001 Paris, France', 'ag-starter-artisan' ); ?>
				</p>
			</div>
			<div class="ag-footer-col">
				<h3><?php esc_html_e( 'Horaires', 'ag-starter-artisan' ); ?></h3>
				<ul>
					<li><?php esc_html_e( 'Lundi - Vendredi : 8h - 18h', 'ag-starter-artisan' ); ?></li>
					<li><?php esc_html_e( 'Samedi : 9h - 12h', 'ag-starter-artisan' ); ?></li>
					<li><?php esc_html_e( 'Urgences 7/7', 'ag-starter-artisan' ); ?></li>
				</ul>
			</div>
			<div class="ag-footer-col">
				<h3><?php esc_html_e( 'Contact', 'ag-starter-artisan' ); ?></h3>
				<p>
					<?php esc_html_e( 'Telephone : 06 00 00 00 00', 'ag-starter-artisan' ); ?><br>
					<?php esc_html_e( 'Email : contact@votre-entreprise.fr', 'ag-starter-artisan' ); ?>
				</p>
			</div>
		</div>
		<div class="ag-footer-bottom">
			<?php
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
