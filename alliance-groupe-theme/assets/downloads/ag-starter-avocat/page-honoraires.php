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
			<h1 class="ag-page-hero__title"><?php esc_html_e( 'Honoraires', 'ag-starter-avocat' ); ?></h1>
			<p class="ag-page-hero__lead"><?php esc_html_e( 'Transparence totale sur les tarifs : pas de mauvaise surprise, devis ecrit avant tout engagement.', 'ag-starter-avocat' ); ?></p>
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
					<h2><?php esc_html_e( 'Comment sont calcules nos honoraires ?', 'ag-starter-avocat' ); ?></h2>
					<p><?php esc_html_e( 'Avant toute intervention, une convention d\'honoraires ecrite vous est remise. Elle precise le mode de facturation choisi, le montant ou le taux horaire applicable, ainsi que les eventuels frais annexes.', 'ag-starter-avocat' ); ?></p>
					<ul>
						<li><?php esc_html_e( 'Consultation initiale : premier rendez-vous pour analyser votre situation', 'ag-starter-avocat' ); ?></li>
						<li><?php esc_html_e( 'Forfait : prix fixe convenu a l\'avance pour un dossier defini', 'ag-starter-avocat' ); ?></li>
						<li><?php esc_html_e( 'Au temps passe : facturation horaire avec releve detaille', 'ag-starter-avocat' ); ?></li>
						<li><?php esc_html_e( 'Honoraires de resultat : complement lie a l\'issue favorable du dossier', 'ag-starter-avocat' ); ?></li>
					</ul>
					<p><?php esc_html_e( 'L\'aide juridictionnelle est acceptee pour les dossiers eligibles.', 'ag-starter-avocat' ); ?></p>
				</div>
			</div>
		</div>
	</section>

	<section class="ag-section ag-rdv-cta" style="text-align:center;padding-top:0;">
		<div class="ag-container">
			<a href="<?php echo esc_url( ag_page_url( 'rendez-vous' ) ); ?>" class="ag-btn"><?php esc_html_e( 'Demander un devis gratuit →', 'ag-starter-avocat' ); ?></a>
		</div>
	</section>

</main>

<?php
get_footer();
