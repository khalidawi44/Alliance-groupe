<?php
/**
 * Template for the "Prendre rendez-vous" page.
 * WordPress auto-loads this for the page with slug "rendez-vous".
 *
 * @package AG_Starter_Avocat
 */

get_header();
$status = ag_starter_avocat_get_rdv_status();
?>

<main id="ag-main" class="ag-main ag-page-single" role="main">

	<section class="ag-page-hero">
		<div class="ag-container">
			<h1 class="ag-page-hero__title"><?php echo esc_html( ag_starter_avocat_get_option( 'ag_rdv_title' ) ); ?></h1>
			<p class="ag-page-hero__lead"><?php echo esc_html( ag_starter_avocat_get_option( 'ag_rdv_subtitle' ) ); ?></p>
		</div>
	</section>

	<section class="ag-section ag-rdv">
		<div class="ag-container ag-container--narrow">

			<?php if ( $status ) : ?>
				<div class="ag-rdv__status ag-rdv__status--<?php echo esc_attr( $status['type'] ); ?>">
					<?php echo esc_html( $status['message'] ); ?>
				</div>
			<?php endif; ?>

			<form class="ag-rdv__form" method="post" action="<?php echo esc_url( ag_page_url( 'rendez-vous' ) ); ?>" novalidate>
				<?php wp_nonce_field( 'ag_rdv_send', 'ag_rdv_nonce' ); ?>

				<div class="ag-rdv__row">
					<div class="ag-rdv__field">
						<label for="ag_rdv_prenom"><?php esc_html_e( 'First name', 'ag-starter-avocat' ); ?></label>
						<input type="text" id="ag_rdv_prenom" name="ag_rdv_prenom" autocomplete="given-name">
					</div>
					<div class="ag-rdv__field">
						<label for="ag_rdv_nom"><?php esc_html_e( 'Name', 'ag-starter-avocat' ); ?> *</label>
						<input type="text" id="ag_rdv_nom" name="ag_rdv_nom" required autocomplete="family-name">
					</div>
				</div>
				<div class="ag-rdv__row">
					<div class="ag-rdv__field">
						<label for="ag_rdv_email"><?php esc_html_e( 'Email', 'ag-starter-avocat' ); ?> *</label>
						<input type="email" id="ag_rdv_email" name="ag_rdv_email" required autocomplete="email">
					</div>
					<div class="ag-rdv__field">
						<label for="ag_rdv_tel"><?php esc_html_e( 'Phone', 'ag-starter-avocat' ); ?></label>
						<input type="tel" id="ag_rdv_tel" name="ag_rdv_tel" autocomplete="tel">
					</div>
				</div>
				<div class="ag-rdv__row">
					<div class="ag-rdv__field">
						<label for="ag_rdv_domaine"><?php esc_html_e( 'Relevant area', 'ag-starter-avocat' ); ?></label>
						<select id="ag_rdv_domaine" name="ag_rdv_domaine">
							<option value=""><?php esc_html_e( '— Select —', 'ag-starter-avocat' ); ?></option>
							<?php
							$dropdown = ag_starter_avocat_get_domaines( 20 );
							if ( $dropdown ) {
								foreach ( $dropdown as $d ) {
									echo '<option value="' . esc_attr( get_the_title( $d ) ) . '">' . esc_html( get_the_title( $d ) ) . '</option>';
								}
							} else {
								echo '<option>' . esc_html__( 'General advice', 'ag-starter-avocat' ) . '</option>';
							}
							?>
							<option value="autre"><?php esc_html_e( 'Other / to be determined', 'ag-starter-avocat' ); ?></option>
						</select>
					</div>
					<div class="ag-rdv__field">
						<label for="ag_rdv_format"><?php esc_html_e( 'Preferred format', 'ag-starter-avocat' ); ?></label>
						<select id="ag_rdv_format" name="ag_rdv_format">
							<option value="cabinet"><?php esc_html_e( 'At the office', 'ag-starter-avocat' ); ?></option>
							<option value="visio"><?php esc_html_e( 'By video', 'ag-starter-avocat' ); ?></option>
							<option value="telephone"><?php esc_html_e( 'By phone', 'ag-starter-avocat' ); ?></option>
						</select>
					</div>
				</div>
				<div class="ag-rdv__field">
					<label for="ag_rdv_message"><?php esc_html_e( 'Case description (in a few lines)', 'ag-starter-avocat' ); ?> *</label>
					<textarea id="ag_rdv_message" name="ag_rdv_message" rows="6" required></textarea>
				</div>

				<div class="ag-rdv__honeypot" aria-hidden="true">
					<label>Site web</label>
					<input type="text" name="ag_rdv_website" tabindex="-1" autocomplete="off">
				</div>

				<div class="ag-rdv__rgpd">
					<label>
						<input type="checkbox" name="ag_rdv_rgpd" value="1" required>
						<span><?php echo esc_html( ag_starter_avocat_get_option( 'ag_rdv_rgpd_text' ) ); ?></span>
					</label>
				</div>

				<button type="submit" name="ag_rdv_submit" class="ag-btn ag-rdv__submit">
					<?php esc_html_e( 'Send my request', 'ag-starter-avocat' ); ?>
				</button>
				<p class="ag-rdv__legal"><?php esc_html_e( 'Confidential request protected by attorney-client privilege. Reply within 48 business hours.', 'ag-starter-avocat' ); ?></p>
			</form>

		</div>
	</section>

	<?php /* Contact info — 2 colonnes avec background fixe */ ?>
	<section class="ag-section ag-rdv-contact">
		<div class="ag-container">
			<div class="ag-rdv-contact__grid">
				<div class="ag-cabinet__block">
					<div class="ag-cabinet__block-icon">📞</div>
					<h3><?php esc_html_e( 'By phone', 'ag-starter-avocat' ); ?></h3>
					<?php $phone = ag_starter_avocat_get_option( 'ag_cabinet_phone' ); if ( $phone ) : ?>
						<p><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a></p>
					<?php endif; ?>
				</div>
				<div class="ag-cabinet__block">
					<div class="ag-cabinet__block-icon">✉️</div>
					<h3><?php esc_html_e( 'By email', 'ag-starter-avocat' ); ?></h3>
					<?php $email = ag_starter_avocat_get_option( 'ag_cabinet_email' ); if ( $email ) : ?>
						<p><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></p>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</section>

</main>

<?php
get_footer();
