<?php
/**
 * Custom Post Types : combat, evenement, groupe-local.
 * Permet d'avoir des pages séparées propres pour chaque entité.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AG_Fid_CPT {
	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) self::$instance = new self();
		return self::$instance;
	}
	private function __construct() {
		add_action( 'init', array( $this, 'register' ) );
		add_action( 'add_meta_boxes',   array( $this, 'register_combat_meta' ) );
		add_action( 'save_post_ag_combat', array( $this, 'save_combat_meta' ), 10, 2 );
	}

	/**
	 * Metabox sur Combats > edit : emoji + couleur de fond de la
	 * pastille (icone). L'utilisateur choisit son emoji et sa couleur
	 * pour chaque combat, sans coder.
	 */
	public function register_combat_meta() {
		add_meta_box(
			'ag_fid_combat_visual',
			__( '🎨 Apparence sur l\'accueil', 'ag-fidelite-association' ),
			array( $this, 'render_combat_meta' ),
			'ag_combat',
			'side',
			'high'
		);
	}

	public function render_combat_meta( $post ) {
		wp_nonce_field( 'ag_fid_combat_meta', 'ag_fid_combat_meta_nonce' );
		$emoji = get_post_meta( $post->ID, '_ag_combat_emoji', true );
		$color = get_post_meta( $post->ID, '_ag_combat_color', true );
		$presets = array(
			'#1F8A3D' => 'Vert',
			'#3B5998' => 'Bleu',
			'#E10F1A' => 'Rouge',
			'#8B1A8B' => 'Violet',
			'#0A0A0D' => 'Noir',
			'#FFD23F' => 'Jaune',
			'#FF6B35' => 'Orange',
		);
		?>
		<p style="margin:8px 0 4px;"><label for="ag_combat_emoji"><strong>Emoji icône</strong></label></p>
		<input type="text" id="ag_combat_emoji" name="ag_combat_emoji" value="<?php echo esc_attr( $emoji ); ?>" maxlength="4" style="width:100%;font-size:1.5rem;text-align:center;padding:8px;" placeholder="🌍">
		<p style="margin:4px 0 12px;font-size:.85em;color:#777;">Tapez ou collez un emoji (ex: 🌍 🏠 🏥 🗳️ ⚖️). Si vide, un emoji par défaut est utilisé.</p>

		<p style="margin:8px 0 4px;"><label for="ag_combat_color"><strong>Couleur de fond</strong></label></p>
		<input type="text" id="ag_combat_color" name="ag_combat_color" value="<?php echo esc_attr( $color ); ?>" placeholder="#E10F1A" style="width:100%;padding:6px;">
		<p style="margin:4px 0 6px;font-size:.85em;color:#777;">Code hex (ex: #E10F1A). Cliquez un préréglage :</p>
		<div style="display:flex;gap:4px;flex-wrap:wrap;">
		<?php foreach ( $presets as $hex => $name ) : ?>
			<button type="button" onclick="document.getElementById('ag_combat_color').value='<?php echo esc_js( $hex ); ?>';" title="<?php echo esc_attr( $name ); ?>" style="width:28px;height:28px;border:2px solid #ccc;border-radius:50%;background:<?php echo esc_attr( $hex ); ?>;cursor:pointer;"></button>
		<?php endforeach; ?>
		</div>
		<?php
	}

	public function save_combat_meta( $post_id, $post ) {
		if ( ! isset( $_POST['ag_fid_combat_meta_nonce'] ) ) return;
		if ( ! wp_verify_nonce( $_POST['ag_fid_combat_meta_nonce'], 'ag_fid_combat_meta' ) ) return;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;

		if ( isset( $_POST['ag_combat_emoji'] ) ) {
			update_post_meta( $post_id, '_ag_combat_emoji', sanitize_text_field( wp_unslash( $_POST['ag_combat_emoji'] ) ) );
		}
		if ( isset( $_POST['ag_combat_color'] ) ) {
			$c = sanitize_hex_color( $_POST['ag_combat_color'] );
			update_post_meta( $post_id, '_ag_combat_color', $c ?: '' );
		}
	}
	public function register() {
		// Combats / Thématiques
		register_post_type( 'ag_combat', array(
			'labels' => array(
				'name'          => __( 'Combats', 'ag-fidelite-association' ),
				'singular_name' => __( 'Combat', 'ag-fidelite-association' ),
				'add_new_item'  => __( 'Ajouter un combat', 'ag-fidelite-association' ),
				'edit_item'     => __( 'Modifier le combat', 'ag-fidelite-association' ),
			),
			'public'             => true,
			'has_archive'        => 'combats',
			'rewrite'            => array( 'slug' => 'combat' ),
			'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'menu_icon'          => 'dashicons-megaphone',
			'show_in_rest'       => true,
		) );

		// Événements
		register_post_type( 'ag_evenement', array(
			'labels' => array(
				'name'          => __( 'Événements', 'ag-fidelite-association' ),
				'singular_name' => __( 'Événement', 'ag-fidelite-association' ),
				'add_new_item'  => __( 'Ajouter un événement', 'ag-fidelite-association' ),
			),
			'public'        => true,
			'has_archive'   => 'evenements',
			'rewrite'       => array( 'slug' => 'evenement' ),
			'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'menu_icon'     => 'dashicons-calendar-alt',
			'show_in_rest'  => true,
		) );

		// Groupes locaux
		register_post_type( 'ag_groupe', array(
			'labels' => array(
				'name'          => __( 'Groupes locaux', 'ag-fidelite-association' ),
				'singular_name' => __( 'Groupe local', 'ag-fidelite-association' ),
				'add_new_item'  => __( 'Ajouter un groupe', 'ag-fidelite-association' ),
			),
			'public'        => true,
			'has_archive'   => 'groupes',
			'rewrite'       => array( 'slug' => 'groupe' ),
			'supports'      => array( 'title', 'editor', 'thumbnail' ),
			'menu_icon'     => 'dashicons-location-alt',
			'show_in_rest'  => true,
		) );

		// Pétitions
		register_post_type( 'ag_petition', array(
			'labels' => array(
				'name'          => __( 'Pétitions', 'ag-fidelite-association' ),
				'singular_name' => __( 'Pétition', 'ag-fidelite-association' ),
			),
			'public'        => true,
			'has_archive'   => 'petitions',
			'rewrite'       => array( 'slug' => 'petition' ),
			'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'menu_icon'     => 'dashicons-edit-large',
			'show_in_rest'  => true,
		) );

		// Procès-verbaux & comptes-rendus (privé, accès adhérents)
		register_post_type( 'ag_pv', array(
			'labels' => array(
				'name'          => __( 'PV & Comptes-rendus', 'ag-fidelite-association' ),
				'singular_name' => __( 'PV', 'ag-fidelite-association' ),
			),
			'public'        => false,
			'show_ui'       => true,
			'show_in_menu'  => true,
			'capability_type' => 'post',
			'supports'      => array( 'title', 'editor', 'author' ),
			'menu_icon'     => 'dashicons-media-document',
		) );
	}
}
