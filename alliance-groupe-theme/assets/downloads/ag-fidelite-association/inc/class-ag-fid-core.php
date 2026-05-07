<?php
/**
 * Pack Fidélité — Core class. Singleton.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AG_Fid_Core {
	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) self::$instance = new self();
		return self::$instance;
	}
	private function __construct() {
		AG_Fid_CPT::instance();
		AG_Fid_Roles::instance();
		AG_Fid_Pages::instance();
		AG_Fid_Shortcodes::instance();
		AG_Fid_Recommendations::instance();

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 30 );
		add_action( 'customize_register', array( $this, 'register_customizer' ), 30 );
		add_action( 'admin_init',         array( $this, 'maybe_auto_reseed' ) );
		add_action( 'admin_init',         array( $this, 'maybe_regenerate_home' ) );
		add_action( 'admin_notices',      array( $this, 'maybe_show_update_notice' ) );
		add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widget' ) );
	}

	/**
	 * Bouton admin : "Regenerer l'accueil avec les blocs Gutenberg".
	 * Ecrase le contenu de la page Accueil avec le default a jour, puis
	 * redirige vers l'editeur de la page (ou l'utilisateur peut tout modifier).
	 */
	public function maybe_regenerate_home() {
		if ( empty( $_GET['ag_fid_regen_home'] ) || ! current_user_can( 'manage_options' ) ) return;
		check_admin_referer( 'ag_fid_regen_home' );
		$home = get_page_by_path( 'accueil' );
		if ( $home ) {
			wp_update_post( array(
				'ID'           => $home->ID,
				'post_content' => AG_Fid_Pages::default_home_content(),
			) );
			wp_safe_redirect( get_edit_post_link( $home->ID, 'raw' ) . '&ag_fid_home_regen=1' );
			exit;
		}
		wp_safe_redirect( admin_url( 'edit.php?post_type=page&ag_fid_home_regen_fail=1' ) );
		exit;
	}

	/**
	 * Re-seed automatique a chaque mise a jour du plugin. Compare la
	 * version stockee en option avec AG_FID_VERSION : si differentes,
	 * on relance pages + roles + cpts (force) puis on stocke la nouvelle
	 * version. Evite au user de cliquer manuellement le bouton apres
	 * chaque sync.
	 */
	public function maybe_auto_reseed() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$stored = get_option( 'ag_fid_seeded_version', '' );
		if ( $stored === AG_FID_VERSION ) return;
		AG_Fid_Roles::create_roles();
		AG_Fid_Pages::create_default_pages();
		AG_Fid_Pages::create_default_cpts( true );
		flush_rewrite_rules();
		update_option( 'ag_fid_seeded_version', AG_FID_VERSION );
		set_transient( 'ag_fid_just_updated_from', $stored, 60 );
	}

	public function maybe_show_update_notice() {
		$prev = get_transient( 'ag_fid_just_updated_from' );
		if ( $prev === false ) return;
		?>
		<div class="notice notice-success is-dismissible" style="border-left-width:4px;border-left-color:#E10F1A;padding:18px 22px;">
			<h3 style="margin:0 0 8px;font-size:1.2rem;">🚀 Pack Fidélité mis à jour : v<?php echo esc_html( AG_FID_VERSION ); ?> <?php if ( $prev ) : ?><small style="color:#888;">(depuis v<?php echo esc_html( $prev ); ?>)</small><?php endif; ?></h3>
			<p style="margin:0 0 6px;">Les pages, rôles, CPT et contenus de démo ont été automatiquement <strong>réinitialisés/mis à jour</strong> avec les dernières versions.</p>
			<p style="margin:0;">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=ag-fid-recommendations' ) ); ?>" class="button button-primary">Voir le Pack Fidélité</a>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="button" target="_blank">👁 Voir le site</a>
			</p>
		</div>
		<?php
		delete_transient( 'ag_fid_just_updated_from' );
	}
	public function enqueue_assets() {
		if ( file_exists( AG_FID_DIR . 'assets/fidelite.css' ) ) {
			wp_enqueue_style( 'ag-fid-style', AG_FID_URL . 'assets/fidelite.css', array(), AG_FID_VERSION );
		}
	}
	public function register_customizer( $wp_customize ) {
		$wp_customize->add_panel( 'ag_fid_panel', array(
			'title'    => __( 'Pack Fidélité — Association', 'ag-fidelite-association' ),
			'priority' => 35,
		) );
		$wp_customize->add_section( 'ag_fid_assoc', array(
			'title' => __( 'Identité de l\'association', 'ag-fidelite-association' ),
			'panel' => 'ag_fid_panel',
		) );
		$fields = array(
			'ag_fid_org_name'    => '[Nom officiel de l\'association]',
			'ag_fid_org_siret'   => '[SIRET (14 chiffres)]',
			'ag_fid_org_rna'     => '[N° RNA (W + 9 chiffres) — associations loi 1901]',
			'ag_fid_president'   => '[Président·e]',
			'ag_fid_iban'        => '[IBAN du compte association]',
			'ag_fid_cotisation'  => '20',
		);
		foreach ( $fields as $key => $default ) {
			$wp_customize->add_setting( $key, array(
				'default'           => $default,
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			) );
			$wp_customize->add_control( $key, array(
				'label'   => str_replace( 'ag_fid_', '', $key ),
				'section' => 'ag_fid_assoc',
				'type'    => 'text',
			) );
		}

		// Section visio Jitsi (AG, reunions a distance)
		$wp_customize->add_section( 'ag_fid_visio', array(
			'title' => __( 'Visio AG / réunion en ligne', 'ag-fidelite-association' ),
			'panel' => 'ag_fid_panel',
			'description' => 'Salle de tchat + visio chiffrée bout-en-bout via Jitsi Meet. Aucun compte requis. Idéale pour les AG, réunions de bureau, ateliers, débats avec les adhérent·es absent·es.',
		) );
		$wp_customize->add_setting( 'ag_fid_visio_room', array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_title',
		) );
		$wp_customize->add_control( 'ag_fid_visio_room', array(
			'label'       => __( 'Nom de la salle (slug)', 'ag-fidelite-association' ),
			'description' => __( 'Ex: mouvement-citoyen-ag2026. Si vide, un nom unique est généré automatiquement à partir du nom de l\'association.', 'ag-fidelite-association' ),
			'section'     => 'ag_fid_visio',
			'type'        => 'text',
		) );
		$wp_customize->add_setting( 'ag_fid_visio_members_only', array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
		) );
		$wp_customize->add_control( 'ag_fid_visio_members_only', array(
			'label'       => __( 'Réservé aux adhérent·es (login requis)', 'ag-fidelite-association' ),
			'description' => __( 'Si activé, seuls les utilisateurs connectés avec un rôle adhérent / militant / bureau peuvent accéder à la visio.', 'ag-fidelite-association' ),
			'section'     => 'ag_fid_visio',
			'type'        => 'checkbox',
		) );

		// Section RDV Cal.com (alternative gratuite a Calendly)
		$wp_customize->add_section( 'ag_fid_rdv', array(
			'title' => __( 'Rendez-vous (Cal.com)', 'ag-fidelite-association' ),
			'panel' => 'ag_fid_panel',
			'description' => 'Cal.com est une alternative open source et 100% gratuite à Calendly. Créez un compte gratuit sur cal.com puis collez votre nom d\'utilisateur ci-dessous.',
		) );
		$wp_customize->add_setting( 'ag_fid_cal_username', array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp_customize->add_control( 'ag_fid_cal_username', array(
			'label'       => __( 'Nom d\'utilisateur Cal.com', 'ag-fidelite-association' ),
			'description' => __( 'Votre identifiant public, ex: mouvement-citoyen (sans https:// ni cal.com/)', 'ag-fidelite-association' ),
			'section'     => 'ag_fid_rdv',
			'type'        => 'text',
		) );
		$wp_customize->add_setting( 'ag_fid_cal_event', array(
			'default'           => '30min',
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp_customize->add_control( 'ag_fid_cal_event', array(
			'label'       => __( 'Slug du type de rendez-vous', 'ag-fidelite-association' ),
			'description' => __( 'Ex: 30min, premiere-rencontre, info-adhesion. Doit correspondre à un type créé dans votre compte Cal.com.', 'ag-fidelite-association' ),
			'section'     => 'ag_fid_rdv',
			'type'        => 'text',
		) );
	}

	/**
	 * Encart dashboard admin : explique au client comment editer son site.
	 */
	public function add_dashboard_widget() {
		wp_add_dashboard_widget(
			'ag_fid_help_widget',
			'🚀 Pack Fidélité — Comment modifier votre site',
			array( $this, 'render_dashboard_widget' )
		);
	}

	public function render_dashboard_widget() {
		$home_id = (int) get_option( 'page_on_front' );
		?>
		<style>
			.ag-fid-help { font-size: .92rem; line-height: 1.6; }
			.ag-fid-help h4 { margin: 14px 0 6px; color: #E10F1A; font-size: .95rem; }
			.ag-fid-help ul { margin: 0; padding-left: 18px; }
			.ag-fid-help li { margin-bottom: 4px; }
			.ag-fid-help a { font-weight: 600; }
		</style>
		<div class="ag-fid-help">
			<p><strong>Bienvenue !</strong> Voici les 4 endroits où modifier votre site :</p>

			<h4>🏠 1. Page d'accueil — 100% Gutenberg, 100% éditable</h4>
			<ul>
				<li><a href="<?php echo esc_url( $home_id ? get_edit_post_link( $home_id ) : admin_url( 'edit.php?post_type=page' ) ); ?>"><strong>Pages → Accueil</strong></a> : ouvrez l'éditeur de blocs et changez TOUT (textes, titres, images, boutons, ordre des sections)</li>
				<li>Hero, manifeste, combats, événements, dons : chaque section est un bloc déplaçable / supprimable / modifiable</li>
				<li>Si la page est vide → les sections par défaut du thème s'affichent en fallback</li>
				<li><a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?ag_fid_regen_home=1' ), 'ag_fid_regen_home' ) ); ?>" onclick="return confirm('Cela va ECRASER le contenu actuel de la page Accueil avec les blocs par defaut. Continuer ?');" style="color:#E10F1A;font-weight:600;">🔄 Régénérer l'accueil avec les blocs par défaut</a> (utile si vous avez tout cassé)</li>
			</ul>

			<h4>🎨 2. Apparence du site (couleurs, typo, logo, sections)</h4>
			<ul>
				<li><a href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>">Apparence → Personnaliser → Mouvement militant</a></li>
				<li><strong>Sections visibles</strong> : on/off pour chaque section (manifeste, combats, équipe, dons…)</li>
				<li><strong>Couleurs</strong> : 6 préréglages ou personnalisé</li>
				<li><strong>Hero, Qui sommes-nous, Bandeau urgence, Réseaux sociaux</strong>… tout est là</li>
			</ul>

			<h4>📝 3. Contenu dynamique (combats, événements, articles)</h4>
			<ul>
				<li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=ag_combat' ) ); ?>">Combats</a> · <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=ag_evenement' ) ); ?>">Événements</a> · <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=ag_groupe' ) ); ?>">Groupes locaux</a> · <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=ag_petition' ) ); ?>">Pétitions</a> · <a href="<?php echo esc_url( admin_url( 'edit.php' ) ); ?>">Articles</a></li>
				<li>Ajouter / supprimer / modifier — affiche auto sur le site</li>
			</ul>

			<h4>📄 4. Pages statiques (manifeste, mentions, RGPD, statuts)</h4>
			<ul>
				<li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=page' ) ); ?>">Pages</a> → cliquez la page à modifier → Gutenberg</li>
			</ul>

			<p style="margin-top:14px;padding-top:10px;border-top:1px solid #eee;">
				<a href="<?php echo esc_url( admin_url( 'themes.php?page=ag-asso-welcome' ) ); ?>" class="button button-primary">🚀 Démarrage rapide</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=ag-fid-recommendations' ) ); ?>" class="button">Pack Fidélité</a>
			</p>
		</div>
		<?php
	}
}
