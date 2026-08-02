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

// Zone editable WP : contenu de la page "accueil" Gutenberg.
// On ne rend ce bloc QUE s'il contient un vrai contenu. La page Accueil
// ne contient en general que le placeholder "<!-- Rendu par front-page.php -->" :
// on l'ignore (sinon ca cree un bandeau blanc vide au-dessus du hero, sur
// lequel le menu dore devient illisible). Plus de fond blanc force non plus.
if ( have_posts() ) : while ( have_posts() ) : the_post();
    $ag_raw = preg_replace( '/<!--.*?-->/s', '', (string) get_the_content() );
    if ( '' !== trim( wp_strip_all_tags( $ag_raw ) ) ) :
        echo '<section class="ag-custom-content"><div style="max-width:1180px;margin:0 auto;padding:50px 24px;">';
        the_content();
        echo '</div></section>';
    endif;
endwhile; rewind_posts(); endif; ?>

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
				$ag_btn_href = ( strpos( $ag_btn_url, 'http' ) === 0 || strpos( $ag_btn_url, '#' ) === 0 ) ? $ag_btn_url : ag_page_url( trim( $ag_btn_url, '/' ) );
				?>
				<a href="<?php echo esc_url( $ag_btn_href ); ?>" class="ag-btn"><?php echo esc_html( $ag_btn_label ); ?></a>
			<?php endif; ?>
		</div>
	</section>
	<?php endif; ?>

	<?php
	// Business: trust badges + counters after hero
	if ( class_exists( 'AG_Pro_Features' ) ) {
		global $ag_pro;
		if ( ! isset( $ag_pro ) ) $ag_pro = new AG_Pro_Features( 'ag-starter-avocat' );
		$ag_pro->render_trust_badges();
		$ag_pro->render_counters();
	}
	?>

	<?php /* ─────────── 2. Domaines d'expertise (CPT) ─────────── */ ?>
	<?php list( $dom_title, $dom_lead ) = ag_avocat_page_section_text( 'expertise', ag_avocat_opt( 'ag_avocat_domaines_title', __( 'Practice areas', 'ag-starter-avocat' ) ), ag_avocat_opt( 'ag_avocat_domaines_lead', __( 'Advice and representation for individuals and businesses across the main areas of law.', 'ag-starter-avocat' ) ) ); ?>
	<section class="ag-section ag-domaines" id="ag-domaines">
		<div class="ag-container">
			<h2 class="ag-section-title"><?php echo ag_avocat_render_split_title( $dom_title ); ?></h2>
			<p class="ag-section-lead"><?php echo esc_html( $dom_lead ); ?></p>

			<?php
			$domaines = ag_starter_avocat_get_domaines( 6 );
			if ( $domaines ) :
				?>
				<div class="ag-domaines__grid">
					<?php foreach ( $domaines as $d ) :
						$icon     = get_post_meta( $d->ID, '_ag_domaine_icon', true );
						$examples = get_post_meta( $d->ID, '_ag_domaine_examples', true );
						$bg_url   = '';
						if ( has_post_thumbnail( $d->ID ) ) {
							$bg_url = get_the_post_thumbnail_url( $d->ID, 'large' );
						} elseif ( function_exists( 'ag_starter_avocat_get_domaine_bg_url' ) ) {
							$bg_url = ag_starter_avocat_get_domaine_bg_url( $icon );
						}
						?>
						<div class="ag-domaine-card ag-domaine-card--bg">
							<?php if ( $bg_url ) : ?>
								<div class="ag-domaine-card__bg" style="background-image:url('<?php echo esc_url( $bg_url ); ?>');"></div>
							<?php endif; ?>
							<div class="ag-domaine-card__overlay"></div>
							<div class="ag-domaine-card__content">
								<h3 class="ag-domaine-card__title"><?php echo esc_html( get_the_title( $d ) ); ?></h3>
								<p class="ag-domaine-card__excerpt"><?php echo esc_html( wp_strip_all_tags( get_the_excerpt( $d ) ) ); ?></p>
								<?php if ( $examples ) : ?>
									<ul class="ag-domaine-card__examples">
										<?php foreach ( array_slice( array_filter( array_map( 'trim', explode( "\n", $examples ) ) ), 0, 3 ) as $ex ) : ?>
											<li><?php echo esc_html( $ex ); ?></li>
										<?php endforeach; ?>
									</ul>
								<?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<div class="ag-domaines__empty">
					<p><?php echo esc_html( ag_avocat_opt( 'ag_avocat_domaines_empty', __( 'No practice area has been published yet.', 'ag-starter-avocat' ) ) ); ?></p>
					<?php if ( current_user_can( 'edit_posts' ) ) : ?>
						<p><a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=ag_domaine' ) ); ?>" class="ag-btn"><?php echo esc_html( ag_avocat_opt( 'ag_avocat_domaines_empty_btn', __( 'Add a first practice area', 'ag-starter-avocat' ) ) ); ?></a></p>
						<p class="ag-domaines__hint"><?php echo esc_html( ag_avocat_opt( 'ag_avocat_domaines_empty_hint', __( 'Tip: create 4 to 6 practice areas (Business law, Employment law, Family law, Real-estate law...) each with an emoji and 3 example cases.', 'ag-starter-avocat' ) ) ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<?php do_action( 'ag_after_domaines' ); ?>

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
					<span class="ag-maitre__tag"><?php echo esc_html( ag_avocat_opt( 'ag_avocat_maitre_tag', __( 'The Attorney', 'ag-starter-avocat' ) ) ); ?></span>
					<h2 class="ag-maitre__name"><?php echo esc_html( ag_starter_avocat_get_option( 'ag_maitre_name' ) ); ?></h2>
					<div class="ag-maitre__meta">
						<span><?php echo esc_html( ag_starter_avocat_get_option( 'ag_maitre_barreau' ) ); ?></span>
						<?php $year = ag_starter_avocat_get_option( 'ag_maitre_year' ); if ( $year ) : ?>
							<span> · <?php echo esc_html( ag_avocat_opt( 'ag_avocat_maitre_year_prefix', __( 'Admitted since', 'ag-starter-avocat' ) ) ); ?> <?php echo esc_html( $year ); ?></span>
						<?php endif; ?>
					</div>
					<p class="ag-maitre__bio"><?php echo nl2br( esc_html( ag_starter_avocat_get_option( 'ag_maitre_bio' ) ) ); ?></p>
					<?php $spec = ag_starter_avocat_get_option( 'ag_maitre_specialties' ); if ( $spec ) : ?>
						<p class="ag-maitre__specialties"><strong><?php echo esc_html( ag_avocat_opt( 'ag_avocat_maitre_specialties_label', __( 'Specialties:', 'ag-starter-avocat' ) ) ); ?></strong> <?php echo esc_html( $spec ); ?></p>
					<?php endif; ?>
					<?php do_action( 'ag_inside_maitre_body' ); ?>
				</div>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<?php do_action( 'ag_after_maitre' ); ?>

	<?php /* ─────────── 4. Honoraires ─────────── */ ?>
	<?php if ( ag_starter_avocat_get_option( 'ag_honoraires_show' ) ) :
		list( $hono_title, $hono_lead ) = ag_avocat_page_section_text( 'honoraires', ag_avocat_opt( 'ag_avocat_honoraires_title', __( 'Fees', 'ag-starter-avocat' ) ), ag_avocat_opt( 'ag_avocat_honoraires_lead', __( 'Full pricing transparency: no bad surprises, a written quote before any commitment.', 'ag-starter-avocat' ) ) );
	?>
	<section class="ag-section ag-honoraires" id="ag-honoraires">
		<div class="ag-container">
			<h2 class="ag-section-title"><?php echo ag_avocat_render_split_title( $hono_title ); ?></h2>
			<p class="ag-section-lead"><?php echo esc_html( $hono_lead ); ?></p>

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

	<?php do_action( 'ag_after_honoraires' ); ?>

	<?php /* ─────────── 5. Cabinet (adresse + horaires + contact — 3 colonnes + map) ─────────── */ ?>
	<?php list( $cab_title, $cab_lead ) = ag_avocat_page_section_text( 'cabinet', ag_avocat_opt( 'ag_avocat_cabinet_title', __( 'The firm', 'ag-starter-avocat' ) ), ag_avocat_opt( 'ag_avocat_cabinet_lead', __( 'Consultation at the office, by video or by phone.', 'ag-starter-avocat' ) ) ); ?>
	<section class="ag-section ag-cabinet" id="ag-cabinet">
		<div class="ag-container">
			<h2 class="ag-section-title"><?php echo ag_avocat_render_split_title( $cab_title ); ?></h2>
			<p class="ag-section-lead"><?php echo esc_html( $cab_lead ); ?></p>
			<div class="ag-cabinet__cards">
				<div class="ag-cabinet__block">
					<div class="ag-cabinet__block-icon">📍</div>
					<h3><?php echo esc_html( ag_avocat_opt( 'ag_avocat_cabinet_address_heading', __( 'Address', 'ag-starter-avocat' ) ) ); ?></h3>
					<p><?php echo nl2br( esc_html( ag_starter_avocat_get_option( 'ag_cabinet_address' ) ) ); ?></p>
				</div>
				<div class="ag-cabinet__block">
					<div class="ag-cabinet__block-icon">🕓</div>
					<h3><?php echo esc_html( ag_avocat_opt( 'ag_avocat_cabinet_hours_heading', __( 'Hours', 'ag-starter-avocat' ) ) ); ?></h3>
					<p><?php echo nl2br( esc_html( ag_starter_avocat_get_option( 'ag_cabinet_hours' ) ) ); ?></p>
				</div>
				<div class="ag-cabinet__block">
					<div class="ag-cabinet__block-icon">📞</div>
					<h3><?php echo esc_html( ag_avocat_opt( 'ag_avocat_cabinet_contact_heading', __( 'Contact', 'ag-starter-avocat' ) ) ); ?></h3>
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
							<strong><?php echo esc_html( ag_avocat_opt( 'ag_avocat_cabinet_emergency_label', __( 'Police custody 24/7:', 'ag-starter-avocat' ) ) ); ?></strong>
							<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $emergency ) ); ?>"><?php echo esc_html( $emergency ); ?></a>
						</p>
					<?php endif; ?>
				</div>
			</div>
			<?php $map = ag_starter_avocat_get_option( 'ag_cabinet_map_embed' ); if ( $map ) : ?>
				<div class="ag-cabinet__map" style="margin-top:40px;">
					<iframe src="<?php echo esc_url( $map ); ?>" width="100%" height="400" style="border:0;border-radius:16px;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<?php do_action( 'ag_after_cabinet' ); ?>

	<?php /* ─────────── 6. Prendre rendez-vous (form RGPD) ─────────── */ ?>
	<?php if ( ag_starter_avocat_get_option( 'ag_rdv_show' ) ) :
		$status = ag_starter_avocat_get_rdv_status();
		list( $rdv_title, $rdv_lead ) = ag_avocat_page_section_text( 'rendez-vous', ag_starter_avocat_get_option( 'ag_rdv_title' ), ag_starter_avocat_get_option( 'ag_rdv_subtitle' ) );
		?>
	<section class="ag-section ag-rdv" id="ag-rdv">
		<div class="ag-container ag-container--narrow">
			<h2 class="ag-section-title"><?php echo ag_avocat_render_split_title( $rdv_title ); ?></h2>
			<p class="ag-section-lead"><?php echo esc_html( $rdv_lead ); ?></p>

			<?php if ( $status ) : ?>
				<div class="ag-rdv__status ag-rdv__status--<?php echo esc_attr( $status['type'] ); ?>">
					<?php echo esc_html( $status['message'] ); ?>
				</div>
			<?php endif; ?>

			<form class="ag-rdv__form" method="post" action="<?php echo esc_url( home_url( '/#ag-rdv' ) ); ?>" novalidate>
				<?php wp_nonce_field( 'ag_rdv_send', 'ag_rdv_nonce' ); ?>

				<div class="ag-rdv__row">
					<div class="ag-rdv__field">
						<label for="ag_rdv_prenom"><?php echo esc_html( ag_avocat_opt( 'ag_avocat_rdv_label_prenom', __( 'First name', 'ag-starter-avocat' ) ) ); ?></label>
						<input type="text" id="ag_rdv_prenom" name="ag_rdv_prenom" autocomplete="given-name">
					</div>
					<div class="ag-rdv__field">
						<label for="ag_rdv_nom"><?php echo esc_html( ag_avocat_opt( 'ag_avocat_rdv_label_nom', __( 'Name', 'ag-starter-avocat' ) ) ); ?> *</label>
						<input type="text" id="ag_rdv_nom" name="ag_rdv_nom" required autocomplete="family-name">
					</div>
				</div>
				<div class="ag-rdv__row">
					<div class="ag-rdv__field">
						<label for="ag_rdv_email"><?php echo esc_html( ag_avocat_opt( 'ag_avocat_rdv_label_email', __( 'Email', 'ag-starter-avocat' ) ) ); ?> *</label>
						<input type="email" id="ag_rdv_email" name="ag_rdv_email" required autocomplete="email">
					</div>
					<div class="ag-rdv__field">
						<label for="ag_rdv_tel"><?php echo esc_html( ag_avocat_opt( 'ag_avocat_rdv_label_tel', __( 'Phone', 'ag-starter-avocat' ) ) ); ?></label>
						<input type="tel" id="ag_rdv_tel" name="ag_rdv_tel" autocomplete="tel">
					</div>
				</div>
				<div class="ag-rdv__row">
					<div class="ag-rdv__field">
						<label for="ag_rdv_domaine"><?php echo esc_html( ag_avocat_opt( 'ag_avocat_rdv_label_domaine', __( 'Relevant area', 'ag-starter-avocat' ) ) ); ?></label>
						<select id="ag_rdv_domaine" name="ag_rdv_domaine">
							<option value=""><?php echo esc_html( ag_avocat_opt( 'ag_avocat_rdv_domaine_select', __( '— Select —', 'ag-starter-avocat' ) ) ); ?></option>
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
							<option value="autre"><?php echo esc_html( ag_avocat_opt( 'ag_avocat_rdv_domaine_other', __( 'Other / to be determined', 'ag-starter-avocat' ) ) ); ?></option>
						</select>
					</div>
					<div class="ag-rdv__field">
						<label for="ag_rdv_format"><?php echo esc_html( ag_avocat_opt( 'ag_avocat_rdv_label_format', __( 'Preferred format', 'ag-starter-avocat' ) ) ); ?></label>
						<select id="ag_rdv_format" name="ag_rdv_format">
							<option value="cabinet"><?php echo esc_html( ag_avocat_opt( 'ag_avocat_rdv_format_cabinet', __( 'At the office', 'ag-starter-avocat' ) ) ); ?></option>
							<option value="visio"><?php echo esc_html( ag_avocat_opt( 'ag_avocat_rdv_format_visio', __( 'By video', 'ag-starter-avocat' ) ) ); ?></option>
							<option value="telephone"><?php echo esc_html( ag_avocat_opt( 'ag_avocat_rdv_format_phone', __( 'By phone', 'ag-starter-avocat' ) ) ); ?></option>
						</select>
					</div>
				</div>
				<div class="ag-rdv__field">
					<label for="ag_rdv_message"><?php echo esc_html( ag_avocat_opt( 'ag_avocat_rdv_label_message', __( 'Case description (in a few lines)', 'ag-starter-avocat' ) ) ); ?> *</label>
					<textarea id="ag_rdv_message" name="ag_rdv_message" rows="5" required></textarea>
				</div>

				<?php /* Honeypot — hidden from real users via CSS, bots fill it. */ ?>
				<div class="ag-rdv__honeypot" aria-hidden="true">
					<label>Website</label>
					<input type="text" name="ag_rdv_website" tabindex="-1" autocomplete="off">
				</div>

				<div class="ag-rdv__rgpd">
					<label>
						<input type="checkbox" name="ag_rdv_rgpd" value="1" required>
						<span><?php echo esc_html( ag_starter_avocat_get_option( 'ag_rdv_rgpd_text' ) ); ?></span>
					</label>
				</div>

				<button type="submit" name="ag_rdv_submit" class="ag-btn ag-rdv__submit">
					<?php echo esc_html( ag_avocat_opt( 'ag_avocat_rdv_submit_label', __( 'Send my request →', 'ag-starter-avocat' ) ) ); ?>
				</button>
				<p class="ag-rdv__legal"><?php echo esc_html( ag_avocat_opt( 'ag_avocat_rdv_legal_note', __( 'Confidential request protected by attorney-client privilege. Reply within 48 business hours.', 'ag-starter-avocat' ) ) ); ?></p>
			</form>
		</div>
	</section>
	<?php endif; ?>

	<?php
	// Pro: Testimonials section
	if ( class_exists( 'AG_Pro_Features' ) ) {
		global $ag_pro;
		if ( ! isset( $ag_pro ) ) $ag_pro = new AG_Pro_Features( 'ag-starter-avocat' );
		$ag_pro->render_testimonials();
	}
	?>

</main>

<?php
get_footer();
