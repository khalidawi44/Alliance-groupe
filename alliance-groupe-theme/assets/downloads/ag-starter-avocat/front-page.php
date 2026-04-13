<?php
/**
 * Front page template — static landing page for the law firm.
 *
 * Sections (all configurable from the WordPress Customizer) :
 *   1. Hero
 *   2. Domaines d'expertise (CPT loop, fallback to a hint card)
 *   3. Le Maitre (bio + photo)
 *   4. Honoraires (pricing transparency)
 *   5. Cabinet (address + hours + map + contact)
 *   6. Prendre rendez-vous (RGPD-compliant form)
 *
 * @package AG_Starter_Avocat
 */

get_header();
?>

<main id="ag-main" class="ag-main" role="main">

	<?php /* ─────────── 1. Hero ─────────── */ ?>
	<?php if ( ag_starter_avocat_get_option( 'ag_hero_show' ) ) : ?>
	<section class="ag-hero">
		<div class="ag-container">
			<h1 class="ag-hero__title">
				<?php echo esc_html( ag_starter_avocat_get_option( 'ag_hero_prefix' ) ); ?>
				<span><?php echo esc_html( ag_starter_avocat_get_option( 'ag_hero_brand' ) ); ?></span>
			</h1>
			<p class="ag-hero__subtitle">
				<?php echo esc_html( ag_starter_avocat_get_option( 'ag_hero_subtitle' ) ); ?>
			</p>
			<?php
			$ag_btn_label = ag_starter_avocat_get_option( 'ag_hero_button' );
			$ag_btn_url   = ag_starter_avocat_get_option( 'ag_hero_button_url' );
			if ( $ag_btn_label ) :
				?>
				<a href="<?php echo esc_url( $ag_btn_url ); ?>" class="ag-btn"><?php echo esc_html( $ag_btn_label ); ?></a>
			<?php endif; ?>
		</div>
	</section>
	<?php endif; ?>

	<?php /* ─────────── 2. Domaines d'expertise (CPT) ─────────── */ ?>
	<section class="ag-section ag-domaines" id="ag-domaines">
		<div class="ag-container">
			<h2 class="ag-section-title"><?php esc_html_e( 'Domaines d\'expertise', 'ag-starter-avocat' ); ?></h2>
			<p class="ag-section-lead"><?php esc_html_e( 'Conseil et representation pour particuliers et entreprises. Cliquez sur un domaine pour decouvrir les cas que nous traitons.', 'ag-starter-avocat' ); ?></p>

			<?php
			$domaines = ag_starter_avocat_get_domaines( 6 );
			if ( $domaines ) :
				?>
				<div class="ag-domaines__grid">
					<?php foreach ( $domaines as $d ) :
						$icon     = get_post_meta( $d->ID, '_ag_domaine_icon', true );
						$examples = get_post_meta( $d->ID, '_ag_domaine_examples', true );
						?>
						<a href="<?php echo esc_url( get_permalink( $d->ID ) ); ?>" class="ag-domaine-card">
							<div class="ag-domaine-card__icon"><?php echo esc_html( $icon ? $icon : '⚖️' ); ?></div>
							<h3 class="ag-domaine-card__title"><?php echo esc_html( get_the_title( $d ) ); ?></h3>
							<p class="ag-domaine-card__excerpt"><?php echo esc_html( wp_strip_all_tags( get_the_excerpt( $d ) ) ); ?></p>
							<?php if ( $examples ) : ?>
								<ul class="ag-domaine-card__examples">
									<?php foreach ( array_slice( array_filter( array_map( 'trim', explode( "\n", $examples ) ) ), 0, 3 ) as $ex ) : ?>
										<li><?php echo esc_html( $ex ); ?></li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
							<span class="ag-domaine-card__more"><?php esc_html_e( 'En savoir plus →', 'ag-starter-avocat' ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<div class="ag-domaines__empty">
					<p><?php esc_html_e( 'Aucun domaine d\'expertise n\'est encore publie.', 'ag-starter-avocat' ); ?></p>
					<?php if ( current_user_can( 'edit_posts' ) ) : ?>
						<p><a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=ag_domaine' ) ); ?>" class="ag-btn"><?php esc_html_e( 'Ajouter un premier domaine', 'ag-starter-avocat' ); ?></a></p>
						<p class="ag-domaines__hint"><?php esc_html_e( 'Astuce : creez 4 a 6 domaines (Droit des affaires, Droit du travail, Droit de la famille, Droit immobilier...) avec un emoji et 3 exemples de cas chacun.', 'ag-starter-avocat' ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<?php /* ─────────── 3. Le Maitre ─────────── */ ?>
	<?php if ( ag_starter_avocat_get_option( 'ag_maitre_show' ) ) :
		$maitre_photo = ag_starter_avocat_get_option( 'ag_maitre_photo' );
		?>
	<section class="ag-section ag-maitre" id="ag-maitre">
		<div class="ag-container">
			<div class="ag-maitre__inner">
				<?php if ( $maitre_photo ) : ?>
					<div class="ag-maitre__photo">
						<img src="<?php echo esc_url( $maitre_photo ); ?>" alt="<?php echo esc_attr( ag_starter_avocat_get_option( 'ag_maitre_name' ) ); ?>" loading="lazy">
					</div>
				<?php endif; ?>
				<div class="ag-maitre__body">
					<span class="ag-maitre__tag"><?php esc_html_e( 'Le Maître', 'ag-starter-avocat' ); ?></span>
					<h2 class="ag-maitre__name"><?php echo esc_html( ag_starter_avocat_get_option( 'ag_maitre_name' ) ); ?></h2>
					<div class="ag-maitre__meta">
						<span><?php echo esc_html( ag_starter_avocat_get_option( 'ag_maitre_barreau' ) ); ?></span>
						<?php $year = ag_starter_avocat_get_option( 'ag_maitre_year' ); if ( $year ) : ?>
							<span> · <?php
							/* translators: %s : year (e.g. 2010). */
							printf( esc_html__( 'Inscrit depuis %s', 'ag-starter-avocat' ), esc_html( $year ) ); ?></span>
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

	<?php /* ─────────── 4. Honoraires ─────────── */ ?>
	<?php if ( ag_starter_avocat_get_option( 'ag_honoraires_show' ) ) : ?>
	<section class="ag-section ag-honoraires" id="ag-honoraires">
		<div class="ag-container">
			<h2 class="ag-section-title"><?php esc_html_e( 'Honoraires', 'ag-starter-avocat' ); ?></h2>
			<p class="ag-section-lead"><?php esc_html_e( 'Transparence totale sur les tarifs : pas de mauvaise surprise, devis ecrit avant tout engagement.', 'ag-starter-avocat' ); ?></p>

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
	<?php endif; ?>

	<?php /* ─────────── 5. Cabinet (adresse + horaires + plan) ─────────── */ ?>
	<section class="ag-section ag-cabinet" id="ag-cabinet">
		<div class="ag-container">
			<h2 class="ag-section-title"><?php esc_html_e( 'Le cabinet', 'ag-starter-avocat' ); ?></h2>
			<div class="ag-cabinet__grid">
				<div class="ag-cabinet__info">
					<div class="ag-cabinet__block">
						<h3>📍 <?php esc_html_e( 'Adresse', 'ag-starter-avocat' ); ?></h3>
						<p><?php echo nl2br( esc_html( ag_starter_avocat_get_option( 'ag_cabinet_address' ) ) ); ?></p>
					</div>
					<div class="ag-cabinet__block">
						<h3>🕓 <?php esc_html_e( 'Horaires', 'ag-starter-avocat' ); ?></h3>
						<p><?php echo nl2br( esc_html( ag_starter_avocat_get_option( 'ag_cabinet_hours' ) ) ); ?></p>
					</div>
					<div class="ag-cabinet__block">
						<h3>📞 <?php esc_html_e( 'Contact', 'ag-starter-avocat' ); ?></h3>
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
								🚨 <strong><?php esc_html_e( 'Garde à vue 24/7 :', 'ag-starter-avocat' ); ?></strong>
								<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $emergency ) ); ?>"><?php echo esc_html( $emergency ); ?></a>
							</p>
						<?php endif; ?>
					</div>
				</div>
				<?php $map = ag_starter_avocat_get_option( 'ag_cabinet_map_embed' ); if ( $map ) : ?>
					<div class="ag-cabinet__map">
						<iframe src="<?php echo esc_url( $map ); ?>" width="100%" height="320" style="border:0;border-radius:8px;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<?php /* ─────────── 6. Prendre rendez-vous (form RGPD) ─────────── */ ?>
	<?php if ( ag_starter_avocat_get_option( 'ag_rdv_show' ) ) :
		$status = ag_starter_avocat_get_rdv_status();
		?>
	<section class="ag-section ag-rdv" id="ag-rdv">
		<div class="ag-container ag-container--narrow">
			<h2 class="ag-section-title"><?php echo esc_html( ag_starter_avocat_get_option( 'ag_rdv_title' ) ); ?></h2>
			<p class="ag-section-lead"><?php echo esc_html( ag_starter_avocat_get_option( 'ag_rdv_subtitle' ) ); ?></p>

			<?php if ( $status ) : ?>
				<div class="ag-rdv__status ag-rdv__status--<?php echo esc_attr( $status['type'] ); ?>">
					<?php echo esc_html( $status['message'] ); ?>
				</div>
			<?php endif; ?>

			<form class="ag-rdv__form" method="post" action="<?php echo esc_url( home_url( '/#ag-rdv' ) ); ?>" novalidate>
				<?php wp_nonce_field( 'ag_rdv_send', 'ag_rdv_nonce' ); ?>

				<div class="ag-rdv__row">
					<div class="ag-rdv__field">
						<label for="ag_rdv_prenom"><?php esc_html_e( 'Prénom', 'ag-starter-avocat' ); ?></label>
						<input type="text" id="ag_rdv_prenom" name="ag_rdv_prenom" autocomplete="given-name">
					</div>
					<div class="ag-rdv__field">
						<label for="ag_rdv_nom"><?php esc_html_e( 'Nom', 'ag-starter-avocat' ); ?> *</label>
						<input type="text" id="ag_rdv_nom" name="ag_rdv_nom" required autocomplete="family-name">
					</div>
				</div>
				<div class="ag-rdv__row">
					<div class="ag-rdv__field">
						<label for="ag_rdv_email"><?php esc_html_e( 'Email', 'ag-starter-avocat' ); ?> *</label>
						<input type="email" id="ag_rdv_email" name="ag_rdv_email" required autocomplete="email">
					</div>
					<div class="ag-rdv__field">
						<label for="ag_rdv_tel"><?php esc_html_e( 'Téléphone', 'ag-starter-avocat' ); ?></label>
						<input type="tel" id="ag_rdv_tel" name="ag_rdv_tel" autocomplete="tel">
					</div>
				</div>
				<div class="ag-rdv__row">
					<div class="ag-rdv__field">
						<label for="ag_rdv_domaine"><?php esc_html_e( 'Domaine concerné', 'ag-starter-avocat' ); ?></label>
						<select id="ag_rdv_domaine" name="ag_rdv_domaine">
							<option value=""><?php esc_html_e( '— Sélectionnez —', 'ag-starter-avocat' ); ?></option>
							<?php
							$dropdown = ag_starter_avocat_get_domaines( 20 );
							if ( $dropdown ) {
								foreach ( $dropdown as $d ) {
									echo '<option value="' . esc_attr( get_the_title( $d ) ) . '">' . esc_html( get_the_title( $d ) ) . '</option>';
								}
							} else {
								echo '<option>' . esc_html__( 'Conseil general', 'ag-starter-avocat' ) . '</option>';
							}
							?>
							<option value="autre"><?php esc_html_e( 'Autre / a determiner', 'ag-starter-avocat' ); ?></option>
						</select>
					</div>
					<div class="ag-rdv__field">
						<label for="ag_rdv_format"><?php esc_html_e( 'Format souhaité', 'ag-starter-avocat' ); ?></label>
						<select id="ag_rdv_format" name="ag_rdv_format">
							<option value="cabinet"><?php esc_html_e( 'Au cabinet', 'ag-starter-avocat' ); ?></option>
							<option value="visio"><?php esc_html_e( 'En visio', 'ag-starter-avocat' ); ?></option>
							<option value="telephone"><?php esc_html_e( 'Par téléphone', 'ag-starter-avocat' ); ?></option>
						</select>
					</div>
				</div>
				<div class="ag-rdv__field">
					<label for="ag_rdv_message"><?php esc_html_e( 'Description du dossier (en quelques lignes)', 'ag-starter-avocat' ); ?> *</label>
					<textarea id="ag_rdv_message" name="ag_rdv_message" rows="5" required></textarea>
				</div>

				<?php /* Honeypot — hidden from real users via CSS, bots fill it. */ ?>
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
					<?php esc_html_e( 'Envoyer ma demande →', 'ag-starter-avocat' ); ?>
				</button>
				<p class="ag-rdv__legal"><?php esc_html_e( 'Demande confidentielle protégée par le secret professionnel. Réponse sous 48h ouvrées.', 'ag-starter-avocat' ); ?></p>
			</form>
		</div>
	</section>
	<?php endif; ?>

</main>

<?php
get_sidebar();
get_footer();
