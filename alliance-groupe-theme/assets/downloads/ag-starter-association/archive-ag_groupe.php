<?php
/**
 * Archive Groupes locaux (CPT ag_groupe).
 * Liste des groupes existants + formulaire "Proposer un nouveau groupe".
 *
 * @package AG_Starter_Association
 */

// Traitement formulaire creation groupe
$form_msg = '';
if ( ! empty( $_POST['ag_groupe_propose'] ) && wp_verify_nonce( $_POST['_wpnonce'] ?? '', 'ag_groupe_propose' ) ) {
	$nom    = sanitize_text_field( wp_unslash( $_POST['nom']    ?? '' ) );
	$ville  = sanitize_text_field( wp_unslash( $_POST['ville']  ?? '' ) );
	$cp     = sanitize_text_field( wp_unslash( $_POST['cp']     ?? '' ) );
	$email  = sanitize_email     ( wp_unslash( $_POST['email']  ?? '' ) );
	$tel    = sanitize_text_field( wp_unslash( $_POST['tel']    ?? '' ) );
	$motiv  = sanitize_textarea_field( wp_unslash( $_POST['motiv'] ?? '' ) );

	if ( $nom && $ville && $email ) {
		$to    = get_theme_mod( 'ag_asso_email', get_option( 'admin_email' ) );
		$subj  = '[Nouveau groupe local] ' . $ville . ' — ' . $nom;
		$body  = "Demande de creation d'un groupe local :\n\n";
		$body .= "Nom : $nom\nVille : $ville ($cp)\nEmail : $email\nTel : $tel\n\nMotivation :\n$motiv\n";
		wp_mail( $to, $subj, $body );
		$form_msg = '<div class="ag-asso-form__success">Merci ! Votre demande a été envoyée. Nous vous recontactons sous 7 jours.</div>';
	} else {
		$form_msg = '<div class="ag-asso-form__error">Merci de remplir au moins le nom, la ville et l\'email.</div>';
	}
}

get_header();
?>
<main id="main">
	<section class="ag-asso-section" style="padding-top:60px;">
		<div class="ag-asso-container">
			<h1 class="ag-asso-section__title">Nos <em>groupes locaux</em></h1>
			<p class="ag-asso-section__lead">47 groupes locaux actifs partout en France. Découvrez celui le plus proche de chez vous, ou proposez-en un nouveau.</p>

			<div class="ag-asso-fid-grid">
				<?php
				$q = new WP_Query( array( 'post_type' => 'ag_groupe', 'posts_per_page' => 50 ) );
				if ( $q->have_posts() ) {
					while ( $q->have_posts() ) {
						$q->the_post();
						?>
						<article class="ag-asso-combat">
							<h3><?php the_title(); ?></h3>
							<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 30 ) ); ?></p>
							<a href="<?php the_permalink(); ?>">Voir le groupe →</a>
						</article>
						<?php
					}
					wp_reset_postdata();
				} else {
					echo '<p>Aucun groupe local référencé pour le moment.</p>';
				}
				?>
			</div>
		</div>
	</section>

	<section class="ag-asso-section ag-asso-section--cta">
		<div class="ag-asso-container">
			<h2 class="ag-asso-section__title">Pas de groupe ? <em>Créez-en un</em></h2>
			<p class="ag-asso-section__lead">Vous voulez fonder un groupe dans votre ville ? Nous vous accompagnons : méthodologie, outils, premier rassemblement public. Remplissez ce formulaire, on vous recontacte sous 7 jours.</p>
			<?php echo $form_msg; ?>
			<form class="ag-asso-form ag-asso-form--cgroup" method="post" action="">
				<?php wp_nonce_field( 'ag_groupe_propose' ); ?>
				<input type="hidden" name="ag_groupe_propose" value="1">
				<div class="ag-asso-form__row">
					<input type="text"  name="nom"    placeholder="Vos prénom et nom *" required>
					<input type="email" name="email"  placeholder="Email *" required>
				</div>
				<div class="ag-asso-form__row">
					<input type="text"  name="ville"  placeholder="Ville *" required>
					<input type="text"  name="cp"     placeholder="Code postal">
					<input type="tel"   name="tel"    placeholder="Téléphone (facultatif)">
				</div>
				<textarea name="motiv" rows="5" placeholder="En quelques lignes : pourquoi ce groupe, qui sont les autres fondateur·rices identifié·es, quel premier événement public envisagez-vous ?"></textarea>
				<label class="ag-asso-form__rgpd">
					<input type="checkbox" required>
					<span>J'accepte que mes données soient traitées dans le cadre de cette demande, conformément au RGPD.</span>
				</label>
				<button type="submit" class="ag-asso-btn ag-asso-btn--primary">Envoyer ma demande</button>
			</form>
		</div>
	</section>
</main>
<?php get_footer(); ?>
