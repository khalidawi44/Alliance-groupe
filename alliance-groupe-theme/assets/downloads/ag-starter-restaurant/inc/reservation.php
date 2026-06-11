<?php
/**
 * Reservation de table — formulaire + capture de lead.
 *
 * - Shortcode [ag_restaurant_reservation]
 * - Injecte automatiquement le formulaire sur la page de slug "reservation"
 *   (le bouton "Reserver une table" du hero pointe vers /reservation/).
 * - A la soumission : cree un lead (CPT ag_devis_lead, partage avec le devis),
 *   notifie l'admin par email, et affiche une confirmation.
 *
 * Le formulaire est une "carte" blanche autonome avec couleurs explicites :
 * il reste lisible quel que soit le fond du theme (clair ou sombre).
 *
 * @package AG_Starter_Restaurant
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AG_Restaurant_Reservation {

	public static function init() {
		add_shortcode( 'ag_restaurant_reservation', array( __CLASS__, 'render_form' ) );
		add_action( 'admin_post_nopriv_ag_restaurant_reservation_submit', array( __CLASS__, 'handle_submit' ) );
		add_action( 'admin_post_ag_restaurant_reservation_submit', array( __CLASS__, 'handle_submit' ) );
		add_filter( 'the_content', array( __CLASS__, 'maybe_append_form' ) );
	}

	public static function maybe_append_form( $content ) {
		if ( is_admin() || ! is_page() || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}
		$post = get_post();
		if ( ! $post || 'reservation' !== $post->post_name ) {
			return $content;
		}
		// Évite le DOUBLON : si le formulaire est déjà rendu (markup) ou si le
		// shortcode [ag_restaurant_reservation] est présent dans la page, on
		// laisse le shortcode faire le rendu et on n'ajoute rien.
		if ( false !== strpos( $content, 'ag-resa-card' ) || has_shortcode( $content, 'ag_restaurant_reservation' ) ) {
			return $content;
		}
		return $content . self::render_form();
	}

	public static function render_form() {
		$accent  = get_theme_mod( 'ag_color_accent', '#B8860B' );
		$success = isset( $_GET['reserved'] ) && '1' === $_GET['reserved'];
		$name    = get_theme_mod( 'ag_hero_brand', '' );

		ob_start();
		?>
		<section class="ag-resa-wrap" style="max-width:680px;margin:40px auto;padding:0 20px;">
			<style>
			.ag-resa-card{background:var(--ag-color-panel,#1c1813);color:var(--ag-color-text,#ece4d2);border:1px solid color-mix(in srgb,var(--ag-color-accent,#c9a24b) 35%,transparent);border-radius:16px;padding:32px;box-shadow:0 18px 50px rgba(0,0,0,.45);}
			.ag-resa-card h2{color:var(--ag-color-heading,#f3ead4) !important;margin:0 0 6px;font-size:1.7rem;font-family:'Playfair Display',Georgia,serif;}
			.ag-resa-lead{color:var(--ag-color-muted,#b3a88c) !important;margin:0 0 22px;}
			.ag-resa-card label{display:block;color:var(--ag-color-muted,#d8cdb0) !important;font-size:.9rem;font-weight:600;}
			.ag-resa-card input,.ag-resa-card textarea{width:100%;padding:11px;margin-top:5px;border:1px solid color-mix(in srgb,var(--ag-color-accent,#c9a24b) 45%,transparent);border-radius:8px;background:#f5efe1;color:#2a2017;font-size:1rem;box-sizing:border-box;}
			.ag-resa-card input::placeholder,.ag-resa-card textarea::placeholder{color:#9a8e72;}
			.ag-resa-card input:focus,.ag-resa-card textarea:focus{outline:none;border-color:var(--ag-color-accent,#c9a24b);box-shadow:0 0 0 3px color-mix(in srgb,var(--ag-color-accent,#c9a24b) 30%,transparent);}
			.ag-resa-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
			@media(max-width:560px){.ag-resa-grid{grid-template-columns:1fr;}}
			.ag-resa-submit{margin-top:20px;width:100%;background:var(--ag-color-accent,#c9a24b);color:var(--ag-color-on-accent,#13110c) !important;border:0;font-weight:700;font-size:1.05rem;padding:15px;border-radius:10px;cursor:pointer;letter-spacing:.3px;}
			.ag-resa-submit:hover{filter:brightness(1.08);}
			.ag-resa-fine{margin:12px 0 0;text-align:center;font-size:.85rem;color:var(--ag-color-muted,#8c8268);}
			/* Barre de changement de mode (cohérente avec la carte) */
			.ag-resa-switch{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:10px;background:var(--ag-color-panel,#1c1813);border:1px solid color-mix(in srgb,var(--ag-color-accent,#c9a24b) 35%,transparent);border-radius:12px;padding:10px 16px;margin-bottom:14px;color:var(--ag-color-text,#ece4d2);font-weight:700}
			.ag-resa-switch__alt{display:flex;flex-wrap:wrap;gap:8px}
			.ag-resa-switch__alt a{text-decoration:none;font-size:.85rem;font-weight:700;padding:6px 12px;border-radius:999px;border:1px solid color-mix(in srgb,var(--ag-color-accent,#c9a24b) 45%,transparent);color:var(--ag-color-accent,#c9a24b) !important}
			.ag-resa-switch__alt a:hover{background:var(--ag-color-accent,#c9a24b);color:var(--ag-color-on-accent,#fff) !important}
			</style>
			<?php if ( $success ) : ?>
				<div class="ag-resa-card" style="text-align:center;">
					<div style="font-size:2.2rem;margin-bottom:8px;">✅</div>
					<h2>Demande de réservation envoyée !</h2>
					<p class="ag-resa-lead" style="margin:0;">Nous revenons vers vous très vite pour confirmer votre table. Merci de votre confiance.</p>
				</div>
			<?php else :
				$ag_carte_pg = get_page_by_path( 'carte' );
				$ag_carte    = $ag_carte_pg ? get_permalink( $ag_carte_pg ) : home_url( '/carte/' );
			?>
				<div class="ag-resa-switch">
					<span>🍽️ Vous réservez une table</span>
					<span class="ag-resa-switch__alt">
						<a href="<?php echo esc_url( add_query_arg( 'go', 'livraison', $ag_carte ) ); ?>">🛵 Livraison</a>
						<a href="<?php echo esc_url( add_query_arg( 'go', 'emporter', $ag_carte ) ); ?>">🥡 À emporter</a>
						<a href="<?php echo esc_url( add_query_arg( 'go', 'consulter', $ag_carte ) ); ?>">📖 Voir la carte</a>
					</span>
				</div>
				<div class="ag-resa-card">
					<h2>Réserver une table<?php echo $name ? ' — ' . esc_html( $name ) : ''; ?></h2>
					<p class="ag-resa-lead">Indiquez vos préférences, nous vous confirmons votre réservation au plus vite.</p>
					<form class="ag-resa-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="ag_restaurant_reservation_submit">
						<?php wp_nonce_field( 'ag_restaurant_reservation' ); ?>
						<div class="ag-resa-grid">
							<label>Date *
								<input type="date" name="resa_date" required>
							</label>
							<label>Heure *
								<input type="time" name="resa_time" required>
							</label>
							<label>Nombre de personnes *
								<input type="number" name="resa_guests" min="1" max="50" value="2" required>
							</label>
							<label>Prénom / Nom *
								<input type="text" name="resa_name" required placeholder="Ex : Karim B.">
							</label>
							<label>Téléphone *
								<input type="tel" name="resa_phone" required placeholder="06 12 34 56 78">
							</label>
							<label>Email
								<input type="email" name="resa_email" placeholder="vous@email.fr">
							</label>
						</div>
						<label style="margin-top:14px;">Message (allergies, occasion, demande spéciale…)
							<textarea name="resa_message" rows="3"></textarea>
						</label>
						<button type="submit" class="ag-resa-submit">Envoyer ma demande de réservation</button>
						<p class="ag-resa-fine">Réponse rapide — sans engagement.</p>
					</form>
				</div>
			<?php endif; ?>
		</section>
		<?php
		return ob_get_clean();
	}

	public static function handle_submit() {
		check_admin_referer( 'ag_restaurant_reservation' );

		$date    = sanitize_text_field( $_POST['resa_date'] ?? '' );
		$time    = sanitize_text_field( $_POST['resa_time'] ?? '' );
		$guests  = absint( $_POST['resa_guests'] ?? 0 );
		$name    = sanitize_text_field( $_POST['resa_name'] ?? '' );
		$phone   = sanitize_text_field( $_POST['resa_phone'] ?? '' );
		$email   = sanitize_email( $_POST['resa_email'] ?? '' );
		$message = sanitize_textarea_field( $_POST['resa_message'] ?? '' );

		$summary = sprintf(
			"Reservation : %s a %s — %d personne(s)\nNom : %s\nTel : %s\nEmail : %s\nMessage : %s",
			$date, $time, $guests, $name, $phone, $email, $message
		);

		$cpt = post_type_exists( 'ag_devis_lead' ) ? 'ag_devis_lead' : 'page';
		$lead_id = wp_insert_post( array(
			'post_type'    => $cpt,
			'post_status'  => 'private',
			'post_title'   => 'Réservation — ' . ( $name ?: 'client' ) . ' (' . $date . ' ' . $time . ')',
			'post_content' => $summary,
		) );
		if ( $lead_id && ! is_wp_error( $lead_id ) ) {
			update_post_meta( $lead_id, '_ag_lead_type', 'reservation' );
			update_post_meta( $lead_id, '_ag_resa_date', $date );
			update_post_meta( $lead_id, '_ag_resa_time', $time );
			update_post_meta( $lead_id, '_ag_resa_guests', $guests );
			update_post_meta( $lead_id, '_ag_resa_phone', $phone );
			update_post_meta( $lead_id, '_ag_resa_email', $email );
		}

		wp_mail(
			get_option( 'admin_email' ),
			'Nouvelle demande de réservation — ' . get_bloginfo( 'name' ),
			$summary
		);

		if ( function_exists( 'ag_push' ) ) {
			ag_push( "🍽️ Nouvelle réservation\n" . $summary );
		}

		// Débloque les recettes secrètes (aimant à clients).
		do_action( 'ag_restaurant_reservation_done', $email, $phone );

		$page = get_page_by_path( 'reservation' );
		$url  = $page ? get_permalink( $page->ID ) : home_url( '/reservation/' );
		wp_safe_redirect( add_query_arg( 'reserved', '1', $url ) );
		exit;
	}
}
AG_Restaurant_Reservation::init();
