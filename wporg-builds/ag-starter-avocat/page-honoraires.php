<?php
/**
 * Template for the "Honoraires" page.
 * WordPress auto-loads this for the page with slug "honoraires".
 *
 * @package AG_Starter_Avocat
 */

get_header();
?>

<main id="ag-main" class="ag-main ag-page-single" role="main">

	<section class="ag-page-hero">
		<div class="ag-container">
			<h1 class="ag-page-hero__title"><?php esc_html_e( 'Fees', 'ag-starter-avocat' ); ?></h1>
			<p class="ag-page-hero__lead"><?php esc_html_e( 'Full pricing transparency: no bad surprises, a written quote before any commitment.', 'ag-starter-avocat' ); ?></p>
		</div>
	</section>

	<section class="ag-section ag-honoraires">
		<div class="ag-container">

			<div class="ag-honoraires__grid">
				<?php
				$tiers = array(
					array(
						'label' => ag_starter_avocat_get_option( 'ag_honoraires_first_label' ),
						'price' => ag_starter_avocat_get_option( 'ag_honoraires_first_price' ),
						'desc'  => ag_starter_avocat_get_option( 'ag_honoraires_first_desc' ),
					),
					array(
						'label' => ag_starter_avocat_get_option( 'ag_honoraires_pack_label' ),
						'price' => ag_starter_avocat_get_option( 'ag_honoraires_pack_price' ),
						'desc'  => ag_starter_avocat_get_option( 'ag_honoraires_pack_desc' ),
					),
					array(
						'label' => ag_starter_avocat_get_option( 'ag_honoraires_hour_label' ),
						'price' => ag_starter_avocat_get_option( 'ag_honoraires_hour_price' ),
						'desc'  => ag_starter_avocat_get_option( 'ag_honoraires_hour_desc' ),
					),
				);
				foreach ( $tiers as $t ) : ?>
					<div class="ag-honoraires__card">
						<div class="ag-honoraires__price"><?php echo esc_html( $t['price'] ); ?></div>
						<h3 class="ag-honoraires__label"><?php echo esc_html( $t['label'] ); ?></h3>
						<p class="ag-honoraires__desc"><?php echo esc_html( $t['desc'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>

			<?php $note = ag_starter_avocat_get_option( 'ag_honoraires_note' ); if ( $note ) : ?>
				<p class="ag-honoraires__note"><?php echo esc_html( $note ); ?></p>
			<?php endif; ?>

		</div>
	</section>

	<?php /* Extra: details on fee structure */ ?>
	<section class="ag-section" style="padding-top:0;">
		<div class="ag-container ag-page-content-wrap">
			<div class="ag-page-article">
				<div class="ag-entry-content">
					<h2><?php esc_html_e( 'How are our fees calculated?', 'ag-starter-avocat' ); ?></h2>
					<p><?php esc_html_e( 'Before any work begins, a written fee agreement is provided to you. It specifies the chosen billing method, the amount or applicable hourly rate, as well as any additional fees.', 'ag-starter-avocat' ); ?></p>
					<ul>
						<li><?php esc_html_e( 'Initial consultation: a first appointment to review your situation', 'ag-starter-avocat' ); ?></li>
						<li><?php esc_html_e( 'Flat fee: a fixed price agreed in advance for a defined case', 'ag-starter-avocat' ); ?></li>
						<li><?php esc_html_e( 'Time-based: hourly billing with a detailed statement', 'ag-starter-avocat' ); ?></li>
						<li><?php esc_html_e( 'Success fee: a supplement tied to a favorable outcome of the case', 'ag-starter-avocat' ); ?></li>
					</ul>
					<p><?php esc_html_e( 'Legal aid is accepted for eligible cases.', 'ag-starter-avocat' ); ?></p>
				</div>
			</div>
		</div>
	</section>

	<section class="ag-section ag-rdv-cta" style="text-align:center;padding-top:0;">
		<div class="ag-container">
			<a href="<?php echo esc_url( ag_page_url( 'rendez-vous' ) ); ?>" class="ag-btn"><?php esc_html_e( 'Request a free quote', 'ag-starter-avocat' ); ?></a>
		</div>
	</section>

</main>

<?php
get_footer();
