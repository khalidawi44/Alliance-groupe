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
	}
}
