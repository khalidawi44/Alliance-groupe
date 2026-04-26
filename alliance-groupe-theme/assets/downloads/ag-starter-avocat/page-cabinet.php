<?php
/**
 * @package AG_Starter_Avocat
 */
get_header();
?>

<main id="ag-main" class="ag-main ag-page-single" role="main">

	<section class="ag-page-hero ag-page-hero--cabinet">
		<div class="ag-container">
			<h1 class="ag-page-hero__title"><?php esc_html_e( 'Le cabinet', 'ag-starter-avocat' ); ?></h1>
			<p class="ag-page-hero__lead"><?php esc_html_e( 'Notre engagement : rigueur, ecoute et confidentialite au service de vos interets.', 'ag-starter-avocat' ); ?></p>
		</div>
	</section>

	<?php if ( ag_starter_avocat_get_option( 'ag_maitre_show' ) ) :
		$maitre_photo = ag_starter_avocat_get_option( 'ag_maitre_photo' );
		?>
	<section class="ag-section ag-maitre">
		<div class="ag-container">
			<h2 class="ag-section-title"><?php esc_html_e( 'Votre avocat', 'ag-starter-avocat' ); ?></h2>
			<div class="ag-maitre__inner">
				<?php if ( $maitre_photo ) : ?>
					<div class="ag-maitre__photo">
						<img src="<?php echo esc_url( $maitre_photo ); ?>" alt="<?php echo esc_attr( ag_starter_avocat_get_option( 'ag_maitre_name' ) ); ?>" loading="lazy">
					</div>
				<?php endif; ?>
				<div class="ag-maitre__body">
					<span class="ag-maitre__tag"><?php esc_html_e( 'Le Maitre', 'ag-starter-avocat' ); ?></span>
					<h2 class="ag-maitre__name"><?php echo esc_html( ag_starter_avocat_get_option( 'ag_maitre_name' ) ); ?></h2>
					<div class="ag-maitre__meta">
						<span><?php echo esc_html( ag_starter_avocat_get_option( 'ag_maitre_barreau' ) ); ?></span>
						<?php $year = ag_starter_avocat_get_option( 'ag_maitre_year' ); if ( $year ) : ?>
							<span> · <?php printf( esc_html__( 'Inscrit depuis %s', 'ag-starter-avocat' ), esc_html( $year ) ); ?></span>
						<?php endif; ?>
					</div>
					<p class="ag-maitre__bio"><?php echo nl2br( esc_html( ag_starter_avocat_get_option( 'ag_maitre_bio' ) ) ); ?></p>
					<?php $spec = ag_starter_avocat_get_option( 'ag_maitre_specialties' ); if ( $spec ) : ?>
						<p class="ag-maitre__specialties"><strong><?php esc_html_e( 'Specialites :', 'ag-starter-avocat' ); ?></strong> <?php echo esc_html( $spec ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<section class="ag-section ag-cabinet-map-section">
		<div class="ag-container">
			<h2 class="ag-section-title"><?php esc_html_e( 'Nous trouver', 'ag-starter-avocat' ); ?></h2>
			<p class="ag-section-lead"><?php esc_html_e( 'Consultation au cabinet, en visio ou par telephone.', 'ag-starter-avocat' ); ?></p>

			<div class="ag-cabinet-full">
				<div class="ag-cabinet-full__map">
					<?php $map = ag_starter_avocat_get_option( 'ag_cabinet_map_embed' ); ?>
					<iframe src="<?php echo $map ? esc_url( $map ) : 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2624.9983685742377!2d2.3449!3d48.8534!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNDjCsDUxJzEyLjIiTiAywrAyMCc0MS42IkU!5e0!3m2!1sfr!2sfr!4v1'; ?>" width="100%" height="450" style="border:0;border-radius:16px;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
				</div>
				<div class="ag-cabinet-full__cards">
					<div class="ag-cabinet__block">
						<div class="ag-cabinet__block-icon">📍</div>
						<h3><?php esc_html_e( 'Adresse', 'ag-starter-avocat' ); ?></h3>
						<p><?php echo nl2br( esc_html( ag_starter_avocat_get_option( 'ag_cabinet_address' ) ) ); ?></p>
					</div>
					<div class="ag-cabinet__block">
						<div class="ag-cabinet__block-icon">🕓</div>
						<h3><?php esc_html_e( 'Horaires', 'ag-starter-avocat' ); ?></h3>
						<p><?php echo nl2br( esc_html( ag_starter_avocat_get_option( 'ag_cabinet_hours' ) ) ); ?></p>
					</div>
					<div class="ag-cabinet__block">
						<div class="ag-cabinet__block-icon">📞</div>
						<h3><?php esc_html_e( 'Contact', 'ag-starter-avocat' ); ?></h3>
						<p>
							<?php $phone = ag_starter_avocat_get_option( 'ag_cabinet_phone' ); if ( $phone ) : ?>
								<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a><br>
							<?php endif; ?>
							<?php $email = ag_starter_avocat_get_option( 'ag_cabinet_email' ); if ( $email ) : ?>
								<a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
							<?php endif; ?>
						</p>
						<?php $emergency = ag_starter_avocat_get_option( 'ag_cabinet_emergency' ); if ( $emergency ) : ?>
							<p class="ag-cabinet__emergency">
								<strong><?php esc_html_e( 'Garde a vue 24/7 :', 'ag-starter-avocat' ); ?></strong>
								<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $emergency ) ); ?>"><?php echo esc_html( $emergency ); ?></a>
							</p>
						<?php endif; ?>
					</div>
					<a href="<?php echo esc_url( ag_page_url( 'rendez-vous' ) ); ?>" class="ag-btn ag-cabinet-full__btn"><?php esc_html_e( 'Prendre rendez-vous →', 'ag-starter-avocat' ); ?></a>
				</div>
			</div>
		</div>
	</section>

</main>

<?php
get_footer();
