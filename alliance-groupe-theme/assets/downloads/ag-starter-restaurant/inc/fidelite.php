<?php
/**
 * Carte de fidélité — programme à tampons (ou à points) pour le restaurant.
 *
 * Côté client (visiteur) : page /fidelite/ avec inscription, puis sa carte
 * digitale (tampons + récompense). Il retrouve sa carte par email/téléphone.
 *
 * Côté restaurant (admin) : menu « 🎁 Fidélité » pour ajouter un tampon /
 * des points, voir la progression, valider une récompense.
 *
 * Crédit automatique : chaque commande en ligne (inc/commande.php) ajoute
 * un tampon / des points au membre correspondant (même email ou téléphone).
 *
 * Tout est réglable par le client dans Personnaliser > « Carte de fidélité ».
 *
 * @package AG_Starter_Restaurant
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AG_Restaurant_Fidelite {

	const CPT = 'ag_fid_member';

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_cpt' ) );
		add_shortcode( 'ag_restaurant_fidelite', array( __CLASS__, 'render' ) );
		add_filter( 'the_content', array( __CLASS__, 'maybe_replace' ) );
		add_action( 'customize_register', array( __CLASS__, 'customize' ) );
		add_action( 'after_setup_theme', array( __CLASS__, 'maybe_create_page' ) );

		// Inscription + recherche de carte (visiteur).
		add_action( 'admin_post_nopriv_ag_fid_join', array( __CLASS__, 'handle_join' ) );
		add_action( 'admin_post_ag_fid_join', array( __CLASS__, 'handle_join' ) );
		add_action( 'admin_post_nopriv_ag_fid_lookup', array( __CLASS__, 'handle_lookup' ) );
		add_action( 'admin_post_ag_fid_lookup', array( __CLASS__, 'handle_lookup' ) );

		// Admin (restaurant).
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
		add_action( 'admin_post_ag_fid_admin', array( __CLASS__, 'handle_admin' ) );

		// Crédit auto à chaque commande en ligne.
		add_action( 'ag_restaurant_order_placed', array( __CLASS__, 'on_order' ), 10, 3 );
	}

	/* ---------------- Réglages (éditables par le client) ---------------- */

	public static function on()        { return (bool) get_theme_mod( 'ag_fid_on', true ); }
	public static function mode()      { return 'points' === get_theme_mod( 'ag_fid_mode', 'stamps' ) ? 'points' : 'stamps'; }
	public static function goal()      { return max( 1, (int) get_theme_mod( 'ag_fid_goal', 10 ) ); }
	public static function reward()    { return trim( (string) get_theme_mod( 'ag_fid_reward', 'Un plat offert 🎁' ) ); }
	public static function welcome()   { return max( 0, (int) get_theme_mod( 'ag_fid_welcome', 1 ) ); }
	public static function ppe()       { return max( 0, (float) get_theme_mod( 'ag_fid_points_per_euro', 1 ) ); }
	public static function auto_on()   { return (bool) get_theme_mod( 'ag_fid_auto', true ); }
	public static function intro()     { return trim( (string) get_theme_mod( 'ag_fid_intro', 'Cumulez des tampons à chaque visite et gagnez des récompenses. Inscription gratuite en 30 secondes.' ) ); }
	public static function unit()      { return 'points' === self::mode() ? 'points' : 'tampons'; }

	public static function customize( $wp ) {
		$wp->add_section( 'ag_fidelite', array(
			'title'       => '🎁 ' . __( 'Carte de fidélité', 'ag-starter-restaurant' ),
			'priority'    => 29,
			'description' => __( 'Programme de fidélité sur la page /fidelite/. Vous réglez tout : type, objectif, récompense.', 'ag-starter-restaurant' ),
		) );
		$add = function( $id, $label, $type, $default, $sanitize, $extra = array() ) use ( $wp ) {
			$wp->add_setting( $id, array( 'default' => $default, 'sanitize_callback' => $sanitize ) );
			$wp->add_control( $id, array_merge( array( 'label' => $label, 'section' => 'ag_fidelite', 'type' => $type ), $extra ) );
		};
		$add( 'ag_fid_on', __( 'Activer la carte de fidélité', 'ag-starter-restaurant' ), 'checkbox', true, 'wp_validate_boolean' );
		$add( 'ag_fid_mode', __( 'Type de programme', 'ag-starter-restaurant' ), 'select', 'stamps', array( __CLASS__, 'sanitize_mode' ), array( 'choices' => array( 'stamps' => __( 'Carte à tampons (1 visite = 1 tampon)', 'ag-starter-restaurant' ), 'points' => __( 'Points (selon le montant dépensé)', 'ag-starter-restaurant' ) ) ) );
		$add( 'ag_fid_goal', __( 'Objectif (nombre de tampons / points)', 'ag-starter-restaurant' ), 'number', 10, 'absint' );
		$add( 'ag_fid_reward', __( 'Récompense à l\'objectif', 'ag-starter-restaurant' ), 'text', 'Un plat offert 🎁', 'sanitize_text_field' );
		$add( 'ag_fid_welcome', __( 'Bonus de bienvenue (tampons / points offerts à l\'inscription)', 'ag-starter-restaurant' ), 'number', 1, 'absint' );
		$add( 'ag_fid_points_per_euro', __( 'Points par euro dépensé (mode Points)', 'ag-starter-restaurant' ), 'text', '1', array( __CLASS__, 'sanitize_float' ) );
		$add( 'ag_fid_auto', __( 'Créditer automatiquement à chaque commande en ligne', 'ag-starter-restaurant' ), 'checkbox', true, 'wp_validate_boolean' );
		$add( 'ag_fid_intro', __( 'Texte de présentation', 'ag-starter-restaurant' ), 'textarea', 'Cumulez des tampons à chaque visite et gagnez des récompenses. Inscription gratuite en 30 secondes.', 'sanitize_textarea_field' );
	}

	public static function sanitize_mode( $v )  { return 'points' === $v ? 'points' : 'stamps'; }
	public static function sanitize_float( $v ) { return (string) max( 0, (float) str_replace( ',', '.', preg_replace( '/[^0-9.,]/', '', (string) $v ) ) ); }

	/* ---------------- CPT membres ---------------- */

	public static function register_cpt() {
		register_post_type( self::CPT, array(
			'labels'       => array( 'name' => __( 'Membres fidélité', 'ag-starter-restaurant' ) ),
			'public'       => false,
			'show_ui'      => false,
			'supports'     => array( 'title' ),
		) );
	}

	private static function norm_phone( $p ) { return preg_replace( '/[^0-9+]/', '', (string) $p ); }

	private static function gen_code() {
		do {
			$code = 'FID-' . strtoupper( wp_generate_password( 6, false, false ) );
		} while ( self::find_by_code( $code ) );
		return $code;
	}

	public static function find_by_code( $code ) {
		$code = sanitize_text_field( $code );
		if ( ! $code ) return null;
		$q = get_posts( array( 'post_type' => self::CPT, 'post_status' => 'private', 'numberposts' => 1, 'fields' => 'ids', 'meta_key' => '_code', 'meta_value' => $code ) );
		return $q ? get_post( $q[0] ) : null;
	}

	public static function find_by_contact( $email, $phone ) {
		$mq = array( 'relation' => 'OR' );
		if ( $email ) $mq[] = array( 'key' => '_email', 'value' => strtolower( $email ) );
		if ( $phone ) $mq[] = array( 'key' => '_phone', 'value' => self::norm_phone( $phone ) );
		if ( count( $mq ) < 2 ) return null;
		$q = get_posts( array( 'post_type' => self::CPT, 'post_status' => 'private', 'numberposts' => 1, 'fields' => 'ids', 'meta_query' => $mq ) );
		return $q ? get_post( $q[0] ) : null;
	}

	/** Solde (tampons ou points) + récompenses disponibles / utilisées. */
	public static function balance( $id ) {
		$bal      = (int) get_post_meta( $id, '_balance', true );
		$redeemed = (int) get_post_meta( $id, '_redeemed', true );
		$goal     = self::goal();
		$earned   = intdiv( $bal, $goal );          // récompenses gagnées au total
		$available = max( 0, $earned - $redeemed ); // récompenses non encore utilisées
		return array( 'balance' => $bal, 'goal' => $goal, 'redeemed' => $redeemed, 'available' => $available, 'in_cycle' => $bal % $goal );
	}

	public static function add_balance( $id, $amount, $note = '' ) {
		$amount = (int) round( $amount );
		if ( ! $id || 0 === $amount ) return;
		$bal = max( 0, (int) get_post_meta( $id, '_balance', true ) + $amount );
		update_post_meta( $id, '_balance', $bal );
		$hist = get_post_meta( $id, '_history', true );
		$hist = is_array( $hist ) ? $hist : array();
		$hist[] = array( 't' => time(), 'n' => $amount, 'm' => $note );
		update_post_meta( $id, '_history', array_slice( $hist, -50 ) );

		// Récompense atteinte ? -> notifie.
		$b = self::balance( $id );
		if ( $b['available'] > 0 && ! get_post_meta( $id, '_notified_'.$b['available'], true ) ) {
			update_post_meta( $id, '_notified_'.$b['available'], 1 );
			self::notify_reward( $id );
		}
	}

	private static function notify_reward( $id ) {
		$name  = get_post_meta( $id, '_name', true );
		$email = get_post_meta( $id, '_email', true );
		$msg   = sprintf( "🎁 Récompense fidélité débloquée : %s\nClient : %s (%s)", self::reward(), $name, get_post_meta( $id, '_code', true ) );
		if ( $email ) {
			wp_mail( $email, '🎁 Votre récompense fidélité chez ' . get_bloginfo( 'name' ), "Bonne nouvelle " . $name . " !\n\nVous avez atteint votre objectif fidélité.\nRécompense : " . self::reward() . "\n\nPrésentez votre carte (" . get_post_meta( $id, '_code', true ) . ") lors de votre prochaine visite." );
		}
		if ( function_exists( 'ag_push' ) ) ag_push( $msg );
		wp_mail( self::notify_email(), '🎁 Récompense fidélité à honorer — ' . get_bloginfo( 'name' ), $msg );
	}

	private static function notify_email() {
		$e = sanitize_email( get_theme_mod( 'ag_order_email', '' ) );
		return $e ? $e : get_option( 'admin_email' );
	}

	/* ---------------- Crédit auto à la commande ---------------- */

	public static function on_order( $email, $phone, $total ) {
		if ( ! self::on() || ! self::auto_on() ) return;
		$m = self::find_by_contact( $email, $phone );
		if ( ! $m ) return; // pas (encore) membre
		if ( 'points' === self::mode() ) {
			self::add_balance( $m->ID, max( 1, floor( (float) $total * self::ppe() ) ), 'Commande en ligne' );
		} else {
			self::add_balance( $m->ID, 1, 'Commande en ligne' );
		}
	}

	/* ---------------- Page /fidelite/ ---------------- */

	public static function maybe_create_page() {
		if ( get_option( 'ag_fid_page_done' ) ) return;
		if ( ! get_page_by_path( 'fidelite' ) ) {
			wp_insert_post( array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_name'    => 'fidelite',
				'post_title'   => 'Fidélité',
				'post_content' => '[ag_restaurant_fidelite]',
			) );
		}
		update_option( 'ag_fid_page_done', 1 );
	}

	public static function maybe_replace( $content ) {
		if ( is_admin() || ! is_page() || ! in_the_loop() || ! is_main_query() ) return $content;
		$post = get_post();
		if ( ! $post || 'fidelite' !== $post->post_name ) return $content;
		if ( false !== strpos( $content, 'ag-fid-wrap' ) ) return $content;
		return self::render();
	}

	public static function render() {
		if ( ! self::on() ) return '<p style="text-align:center;color:var(--ag-color-muted)">Programme de fidélité bientôt disponible.</p>';

		$code = isset( $_GET['card'] ) ? sanitize_text_field( wp_unslash( $_GET['card'] ) ) : '';
		$member = $code ? self::find_by_code( $code ) : null;

		ob_start();
		echo self::styles();
		echo '<div class="ag-fid-wrap">';
		if ( $member ) {
			echo self::render_card( $member );
		} else {
			echo self::render_join();
		}
		echo '</div>';
		return ob_get_clean();
	}

	private static function render_join() {
		$msg   = isset( $_GET['fid'] ) ? sanitize_key( $_GET['fid'] ) : '';
		$brand = get_theme_mod( 'ag_hero_brand', get_bloginfo( 'name' ) );
		$post  = esc_url( admin_url( 'admin-post.php' ) );
		ob_start();
		?>
		<div class="ag-fid-card ag-fid-hero">
			<div class="ag-fid-badge">🎁</div>
			<h2>Carte de fidélité<?php echo $brand ? ' — ' . esc_html( $brand ) : ''; ?></h2>
			<p class="ag-fid-intro"><?php echo nl2br( esc_html( self::intro() ) ); ?></p>
			<p class="ag-fid-reward-line">Objectif : <strong><?php echo (int) self::goal() . ' ' . esc_html( self::unit() ); ?></strong> → <strong><?php echo esc_html( self::reward() ); ?></strong></p>
		</div>

		<?php if ( 'notfound' === $msg ) : ?><div class="ag-fid-alert ag-fid-alert--err">Aucune carte trouvée avec ces informations. Inscrivez-vous, c'est gratuit !</div><?php endif; ?>

		<div class="ag-fid-grid">
			<div class="ag-fid-card">
				<h3>Je crée ma carte</h3>
				<form method="post" action="<?php echo $post; ?>">
					<input type="hidden" name="action" value="ag_fid_join">
					<?php wp_nonce_field( 'ag_fid_join' ); ?>
					<label>Prénom *<input type="text" name="fid_name" required placeholder="Karim"></label>
					<label>Email<input type="email" name="fid_email" placeholder="vous@email.fr"></label>
					<label>Téléphone<input type="tel" name="fid_phone" placeholder="06 12 34 56 78"></label>
					<p class="ag-fid-fine">Email ou téléphone obligatoire (pour retrouver votre carte).</p>
					<button type="submit" class="ag-fid-btn">Créer ma carte gratuite</button>
				</form>
			</div>
			<div class="ag-fid-card">
				<h3>J'ai déjà une carte</h3>
				<form method="post" action="<?php echo $post; ?>">
					<input type="hidden" name="action" value="ag_fid_lookup">
					<?php wp_nonce_field( 'ag_fid_lookup' ); ?>
					<label>Email ou téléphone<input type="text" name="fid_contact" required placeholder="vous@email.fr ou 06…"></label>
					<button type="submit" class="ag-fid-btn ag-fid-btn--ghost">Retrouver ma carte</button>
				</form>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	private static function render_card( $member ) {
		$id    = $member->ID;
		$name  = get_post_meta( $id, '_name', true );
		$code  = get_post_meta( $id, '_code', true );
		$b     = self::balance( $id );
		$mode  = self::mode();
		$joined = isset( $_GET['fid'] ) && 'joined' === $_GET['fid'];

		ob_start();
		?>
		<div class="ag-fid-card ag-fid-digital">
			<?php if ( $joined ) : ?><div class="ag-fid-alert ag-fid-alert--ok">Bienvenue <?php echo esc_html( $name ); ?> ! Votre carte est créée. Ajoutez-la à vos favoris pour la retrouver.</div><?php endif; ?>
			<div class="ag-fid-dhead">
				<div>
					<span class="ag-fid-dlabel">Carte de fidélité</span>
					<h2><?php echo esc_html( $name ); ?></h2>
				</div>
				<span class="ag-fid-code"><?php echo esc_html( $code ); ?></span>
			</div>

			<?php if ( $b['available'] > 0 ) : ?>
				<div class="ag-fid-reward-banner">🎉 <strong><?php echo (int) $b['available']; ?> récompense<?php echo $b['available'] > 1 ? 's' : ''; ?> disponible<?php echo $b['available'] > 1 ? 's' : ''; ?> !</strong><br><?php echo esc_html( self::reward() ); ?> — présentez cette carte en caisse.</div>
			<?php endif; ?>

			<?php if ( 'stamps' === $mode ) : ?>
				<div class="ag-fid-stamps">
					<?php for ( $i = 1; $i <= $b['goal']; $i++ ) : $f = ( $i <= $b['in_cycle'] ); ?>
						<span class="ag-fid-stamp <?php echo $f ? 'is-on' : ''; ?>"><?php echo $f ? '★' : $i; ?></span>
					<?php endfor; ?>
				</div>
				<p class="ag-fid-progress"><strong><?php echo (int) $b['in_cycle']; ?>/<?php echo (int) $b['goal']; ?></strong> tampons — plus que <strong><?php echo (int) ( $b['goal'] - $b['in_cycle'] ); ?></strong> avant <em><?php echo esc_html( self::reward() ); ?></em>.</p>
			<?php else : ?>
				<div class="ag-fid-points"><span class="ag-fid-pnum"><?php echo (int) $b['in_cycle']; ?></span><span class="ag-fid-pof">/ <?php echo (int) $b['goal']; ?> points</span></div>
				<div class="ag-fid-bar"><i style="width:<?php echo (int) min( 100, round( $b['in_cycle'] / $b['goal'] * 100 ) ); ?>%"></i></div>
				<p class="ag-fid-progress">Plus que <strong><?php echo (int) ( $b['goal'] - $b['in_cycle'] ); ?> points</strong> avant <em><?php echo esc_html( self::reward() ); ?></em>.</p>
			<?php endif; ?>

			<p class="ag-fid-fine">Présentez votre carte (ou donnez votre email / téléphone) à chaque visite pour cumuler. Vos commandes en ligne sont créditées automatiquement.</p>
			<a href="<?php echo esc_url( remove_query_arg( array( 'card', 'fid' ) ) ); ?>" class="ag-fid-link">← Une autre carte</a>
		</div>
		<?php
		return ob_get_clean();
	}

	/* ---------------- Soumissions visiteur ---------------- */

	public static function handle_join() {
		check_admin_referer( 'ag_fid_join' );
		$name  = sanitize_text_field( $_POST['fid_name'] ?? '' );
		$email = sanitize_email( $_POST['fid_email'] ?? '' );
		$phone = self::norm_phone( $_POST['fid_phone'] ?? '' );
		$back  = self::page_url();

		if ( ! $name || ( ! $email && ! $phone ) ) {
			wp_safe_redirect( add_query_arg( 'fid', 'missing', $back ) ); exit;
		}
		// Déjà membre ? -> on renvoie sa carte.
		$exist = self::find_by_contact( $email, $phone );
		if ( $exist ) {
			wp_safe_redirect( add_query_arg( array( 'card' => get_post_meta( $exist->ID, '_code', true ) ), $back ) ); exit;
		}

		$code = self::gen_code();
		$mid  = wp_insert_post( array(
			'post_type'   => self::CPT,
			'post_status' => 'private',
			'post_title'  => $name . ' — ' . $code,
		) );
		if ( $mid && ! is_wp_error( $mid ) ) {
			update_post_meta( $mid, '_name', $name );
			update_post_meta( $mid, '_email', strtolower( $email ) );
			update_post_meta( $mid, '_phone', $phone );
			update_post_meta( $mid, '_code', $code );
			update_post_meta( $mid, '_balance', 0 );
			update_post_meta( $mid, '_redeemed', 0 );
			if ( self::welcome() > 0 ) self::add_balance( $mid, self::welcome(), 'Bienvenue' );

			if ( $email ) {
				wp_mail( $email, '🎁 Votre carte de fidélité ' . get_bloginfo( 'name' ), "Bienvenue " . $name . " !\n\nVotre carte de fidélité est active.\nNuméro de carte : " . $code . "\nObjectif : " . self::goal() . ' ' . self::unit() . " → " . self::reward() . "\n\nRetrouvez votre carte ici : " . add_query_arg( 'card', $code, $back ) );
			}
			if ( function_exists( 'ag_push' ) ) ag_push( "🎁 Nouveau membre fidélité : $name ($code)" );
		}
		wp_safe_redirect( add_query_arg( array( 'card' => $code, 'fid' => 'joined' ), $back ) );
		exit;
	}

	public static function handle_lookup() {
		check_admin_referer( 'ag_fid_lookup' );
		$contact = sanitize_text_field( $_POST['fid_contact'] ?? '' );
		$back    = self::page_url();
		$email   = is_email( $contact ) ? $contact : '';
		$phone   = $email ? '' : self::norm_phone( $contact );
		$m = self::find_by_contact( $email, $phone );
		if ( $m ) {
			wp_safe_redirect( add_query_arg( 'card', get_post_meta( $m->ID, '_code', true ), $back ) ); exit;
		}
		wp_safe_redirect( add_query_arg( 'fid', 'notfound', $back ) ); exit;
	}

	private static function page_url() {
		$p = get_page_by_path( 'fidelite' );
		return $p ? get_permalink( $p ) : home_url( '/fidelite/' );
	}

	/* ---------------- Admin restaurant ---------------- */

	public static function admin_menu() {
		add_menu_page( __( 'Fidélité', 'ag-starter-restaurant' ), '🎁 ' . __( 'Fidélité', 'ag-starter-restaurant' ), 'manage_options', 'ag-fidelite', array( __CLASS__, 'admin_page' ), 'dashicons-tickets-alt', 27 );
	}

	public static function admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
		$args = array( 'post_type' => self::CPT, 'post_status' => 'private', 'numberposts' => 50, 'orderby' => 'modified', 'order' => 'DESC' );
		if ( $search ) {
			$args['meta_query'] = array( 'relation' => 'OR',
				array( 'key' => '_name', 'value' => $search, 'compare' => 'LIKE' ),
				array( 'key' => '_email', 'value' => strtolower( $search ), 'compare' => 'LIKE' ),
				array( 'key' => '_phone', 'value' => self::norm_phone( $search ), 'compare' => 'LIKE' ),
				array( 'key' => '_code', 'value' => $search, 'compare' => 'LIKE' ),
			);
		}
		$members = get_posts( $args );
		$post = esc_url( admin_url( 'admin-post.php' ) );
		$notice = isset( $_GET['done'] ) ? sanitize_key( $_GET['done'] ) : '';
		?>
		<div class="wrap">
			<h1>🎁 Carte de fidélité</h1>
			<?php if ( $notice ) : ?><div class="notice notice-success is-dismissible"><p><?php echo 'redeemed' === $notice ? 'Récompense validée.' : ( 'added' === $notice ? 'Crédit ajouté.' : 'Fait.' ); ?></p></div><?php endif; ?>
			<p>Programme : <strong><?php echo esc_html( self::mode() === 'points' ? 'Points' : 'Tampons' ); ?></strong> · Objectif : <strong><?php echo (int) self::goal() . ' ' . esc_html( self::unit() ); ?></strong> → <strong><?php echo esc_html( self::reward() ); ?></strong> <em>(modifiable dans Apparence › Personnaliser › Carte de fidélité)</em></p>
			<form method="get"><input type="hidden" name="page" value="ag-fidelite"><input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Nom, email, téléphone, carte…"><button class="button">Rechercher</button></form>
			<table class="widefat striped" style="margin-top:14px">
				<thead><tr><th>Membre</th><th>Carte</th><th>Contact</th><th>Solde</th><th>Récompenses</th><th>Actions</th></tr></thead>
				<tbody>
				<?php if ( ! $members ) : ?>
					<tr><td colspan="6">Aucun membre<?php echo $search ? ' pour « ' . esc_html( $search ) . ' »' : ' pour l\'instant'; ?>.</td></tr>
				<?php else : foreach ( $members as $m ) : $b = self::balance( $m->ID ); ?>
					<tr>
						<td><strong><?php echo esc_html( get_post_meta( $m->ID, '_name', true ) ); ?></strong></td>
						<td><code><?php echo esc_html( get_post_meta( $m->ID, '_code', true ) ); ?></code></td>
						<td><?php echo esc_html( trim( get_post_meta( $m->ID, '_email', true ) . ' ' . get_post_meta( $m->ID, '_phone', true ) ) ); ?></td>
						<td><?php echo (int) $b['in_cycle'] . ' / ' . (int) $b['goal']; ?> <small>(total <?php echo (int) $b['balance']; ?>)</small></td>
						<td><?php echo $b['available'] > 0 ? '<strong style="color:#a06800">' . (int) $b['available'] . ' à honorer</strong>' : '—'; ?></td>
						<td>
							<form method="post" action="<?php echo $post; ?>" style="display:inline">
								<?php wp_nonce_field( 'ag_fid_admin' ); ?>
								<input type="hidden" name="action" value="ag_fid_admin"><input type="hidden" name="mid" value="<?php echo (int) $m->ID; ?>"><input type="hidden" name="op" value="add">
								<button class="button button-primary" title="Ajouter 1 <?php echo esc_attr( self::mode() === 'points' ? 'point' : 'tampon' ); ?>">+1</button>
							</form>
							<?php if ( self::mode() === 'points' ) : ?>
							<form method="post" action="<?php echo $post; ?>" style="display:inline">
								<?php wp_nonce_field( 'ag_fid_admin' ); ?>
								<input type="hidden" name="action" value="ag_fid_admin"><input type="hidden" name="mid" value="<?php echo (int) $m->ID; ?>"><input type="hidden" name="op" value="addn">
								<input type="number" name="n" value="10" style="width:60px" min="1"><button class="button">+ pts</button>
							</form>
							<?php endif; ?>
							<?php if ( $b['available'] > 0 ) : ?>
							<form method="post" action="<?php echo $post; ?>" style="display:inline">
								<?php wp_nonce_field( 'ag_fid_admin' ); ?>
								<input type="hidden" name="action" value="ag_fid_admin"><input type="hidden" name="mid" value="<?php echo (int) $m->ID; ?>"><input type="hidden" name="op" value="redeem">
								<button class="button" style="border-color:#a06800;color:#a06800">Valider récompense</button>
							</form>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	public static function handle_admin() {
		check_admin_referer( 'ag_fid_admin' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Non autorisé' );
		$mid = absint( $_POST['mid'] ?? 0 );
		$op  = sanitize_key( $_POST['op'] ?? '' );
		$done = '';
		if ( $mid && get_post_type( $mid ) === self::CPT ) {
			if ( 'add' === $op )       { self::add_balance( $mid, 1, 'Ajout manuel' ); $done = 'added'; }
			elseif ( 'addn' === $op )  { self::add_balance( $mid, max( 1, absint( $_POST['n'] ?? 1 ) ), 'Ajout manuel' ); $done = 'added'; }
			elseif ( 'redeem' === $op ){ update_post_meta( $mid, '_redeemed', (int) get_post_meta( $mid, '_redeemed', true ) + 1 ); $done = 'redeemed'; }
		}
		wp_safe_redirect( add_query_arg( 'done', $done ?: '1', admin_url( 'admin.php?page=ag-fidelite' ) ) );
		exit;
	}

	/* ---------------- Styles ---------------- */

	private static function styles() {
		return '<style>
		.ag-fid-wrap{max-width:840px;margin:30px auto 60px;padding:0 18px;color:var(--ag-color-text)}
		.ag-fid-card{background:linear-gradient(180deg,var(--ag-color-panel),var(--ag-color-panel));border:1px solid color-mix(in srgb, var(--ag-color-accent) 34%, transparent);border-radius:16px;padding:28px 26px;box-shadow:0 18px 50px rgba(0,0,0,.45);margin-bottom:18px}
		.ag-fid-card h2{font-family:"Playfair Display",Georgia,serif;color:var(--ag-color-heading);margin:0 0 8px;font-size:1.7rem}
		.ag-fid-card h3{color:var(--ag-color-heading);margin:0 0 14px;font-size:1.2rem}
		.ag-fid-hero{text-align:center}
		.ag-fid-badge{font-size:2.4rem}
		.ag-fid-intro{color:var(--ag-color-muted);line-height:1.6;margin:6px auto 14px;max-width:560px}
		.ag-fid-reward-line{color:var(--ag-color-muted)}.ag-fid-reward-line strong{color:var(--ag-color-accent)}
		.ag-fid-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}
		@media(max-width:640px){.ag-fid-grid{grid-template-columns:1fr}}
		.ag-fid-card label{display:block;color:var(--ag-color-muted);font-size:.88rem;font-weight:600;margin-bottom:12px}
		.ag-fid-card input{width:100%;padding:11px;margin-top:5px;border:1px solid color-mix(in srgb, var(--ag-color-accent) 45%, transparent);border-radius:8px;background:#f5efe1;color:#2a2017;font-size:1rem;box-sizing:border-box}
		.ag-fid-card input::placeholder{color:#9a8e72}
		.ag-fid-fine{color:var(--ag-color-muted);font-size:.82rem;margin:4px 0 12px}
		.ag-fid-btn{width:100%;background:var(--ag-color-accent);color:var(--ag-color-on-accent);border:0;font-weight:800;font-size:1.02rem;padding:13px;border-radius:10px;cursor:pointer}
		.ag-fid-btn:hover{filter:brightness(1.08)}
		.ag-fid-btn--ghost{background:transparent;border:1px solid var(--ag-color-accent);color:var(--ag-color-accent)}
		.ag-fid-alert{border-radius:10px;padding:12px 16px;margin-bottom:16px;font-weight:600}
		.ag-fid-alert--ok{background:rgba(60,160,90,.16);border:1px solid rgba(60,160,90,.5);color:#bfe9cb}
		.ag-fid-alert--err{background:rgba(225,90,70,.14);border:1px solid rgba(225,90,70,.5);color:#f3c4ba}
		.ag-fid-dhead{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;border-bottom:1px solid color-mix(in srgb, var(--ag-color-accent) 22%, transparent);padding-bottom:14px;margin-bottom:18px}
		.ag-fid-dlabel{color:var(--ag-color-accent);letter-spacing:2px;text-transform:uppercase;font-size:.72rem;font-weight:700}
		.ag-fid-code{background:rgba(255,255,255,.05);border:1px dashed color-mix(in srgb, var(--ag-color-accent) 50%, transparent);border-radius:8px;padding:6px 12px;color:var(--ag-color-accent);font-weight:800;white-space:nowrap;font-family:monospace}
		.ag-fid-reward-banner{background:linear-gradient(135deg,color-mix(in srgb, var(--ag-color-accent) 25%, transparent),color-mix(in srgb, var(--ag-color-accent2) 12%, transparent));border:1px solid color-mix(in srgb, var(--ag-color-accent) 50%, transparent);border-radius:12px;padding:14px 16px;margin-bottom:18px;color:var(--ag-color-heading);line-height:1.5}
		.ag-fid-stamps{display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin:6px 0 16px}
		@media(max-width:480px){.ag-fid-stamps{grid-template-columns:repeat(5,1fr);gap:8px}}
		.ag-fid-stamp{aspect-ratio:1;display:flex;align-items:center;justify-content:center;border-radius:50%;border:2px dashed color-mix(in srgb, var(--ag-color-accent) 45%, transparent);color:var(--ag-color-muted);font-weight:700;font-size:1.1rem}
		.ag-fid-stamp.is-on{background:var(--ag-color-accent);border-style:solid;border-color:var(--ag-color-accent);color:var(--ag-color-on-accent);box-shadow:0 4px 14px color-mix(in srgb, var(--ag-color-accent) 40%, transparent)}
		.ag-fid-points{display:flex;align-items:baseline;gap:8px;margin:4px 0 10px}
		.ag-fid-pnum{font-family:"Playfair Display",Georgia,serif;font-size:2.6rem;color:var(--ag-color-accent);font-weight:800;line-height:1}
		.ag-fid-pof{color:var(--ag-color-muted)}
		.ag-fid-bar{height:12px;border-radius:99px;background:rgba(255,255,255,.1);overflow:hidden;margin-bottom:12px}
		.ag-fid-bar i{display:block;height:100%;background:var(--ag-color-accent);border-radius:99px;transition:width .8s cubic-bezier(.23,1,.32,1)}
		.ag-fid-progress{color:var(--ag-color-muted);line-height:1.55}.ag-fid-progress strong{color:var(--ag-color-accent)}.ag-fid-progress em{color:var(--ag-color-heading);font-style:normal}
		.ag-fid-link{display:inline-block;margin-top:10px;color:var(--ag-color-accent);text-decoration:none;font-weight:600}
		</style>';
	}
}
AG_Restaurant_Fidelite::init();
