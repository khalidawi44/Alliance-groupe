<?php
/**
 * Custom Post Type : Domaine d'expertise (Avocat)
 *
 * Registers the "ag_domaine" CPT so the lawyer can create / edit
 * each practice area from the WordPress admin (Articles > Domaines).
 * Each domain has a title, content, optional excerpt, and the theme
 * uses a few post_meta fields for icon, examples, etc.
 *
 * @package AG_Starter_Avocat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Domaine d'expertise CPT.
 */
function ag_starter_avocat_register_domaine_cpt() {
	$labels = array(
		'name'                  => _x( 'Domaines d\'expertise', 'Post type general name', 'ag-starter-avocat' ),
		'singular_name'         => _x( 'Domaine d\'expertise', 'Post type singular name', 'ag-starter-avocat' ),
		'menu_name'             => _x( 'Domaines', 'Admin Menu text', 'ag-starter-avocat' ),
		'name_admin_bar'        => _x( 'Domaine', 'Add New on Toolbar', 'ag-starter-avocat' ),
		'add_new'               => __( 'Ajouter', 'ag-starter-avocat' ),
		'add_new_item'          => __( 'Ajouter un domaine', 'ag-starter-avocat' ),
		'new_item'              => __( 'Nouveau domaine', 'ag-starter-avocat' ),
		'edit_item'             => __( 'Modifier le domaine', 'ag-starter-avocat' ),
		'view_item'             => __( 'Voir le domaine', 'ag-starter-avocat' ),
		'all_items'             => __( 'Tous les domaines', 'ag-starter-avocat' ),
		'search_items'          => __( 'Rechercher un domaine', 'ag-starter-avocat' ),
		'not_found'             => __( 'Aucun domaine trouve.', 'ag-starter-avocat' ),
		'not_found_in_trash'    => __( 'Aucun domaine dans la corbeille.', 'ag-starter-avocat' ),
		'featured_image'        => __( 'Illustration du domaine', 'ag-starter-avocat' ),
		'set_featured_image'    => __( 'Definir une illustration', 'ag-starter-avocat' ),
		'remove_featured_image' => __( 'Retirer l\'illustration', 'ag-starter-avocat' ),
		'use_featured_image'    => __( 'Utiliser comme illustration', 'ag-starter-avocat' ),
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'menu_icon'          => 'dashicons-portfolio',
		'menu_position'      => 22,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'domaine' ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'custom-fields' ),
		'show_in_rest'       => true,
		'menu_position'      => 22,
	);

	register_post_type( 'ag_domaine', $args );
}
add_action( 'init', 'ag_starter_avocat_register_domaine_cpt' );

/**
 * Add a meta box for the icon and the example cases on the
 * Domaine d'expertise edit screen.
 */
function ag_starter_avocat_domaine_metabox() {
	add_meta_box(
		'ag_domaine_meta',
		__( 'Détails du domaine', 'ag-starter-avocat' ),
		'ag_starter_avocat_domaine_metabox_render',
		'ag_domaine',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes', 'ag_starter_avocat_domaine_metabox' );

/**
 * Render the meta box.
 *
 * @param WP_Post $post Post object.
 */
function ag_starter_avocat_domaine_metabox_render( $post ) {
	wp_nonce_field( 'ag_domaine_meta_save', 'ag_domaine_meta_nonce' );
	$icon     = get_post_meta( $post->ID, '_ag_domaine_icon', true );
	$examples = get_post_meta( $post->ID, '_ag_domaine_examples', true );
	?>
	<p>
		<label for="ag_domaine_icon"><strong><?php esc_html_e( 'Icône (emoji)', 'ag-starter-avocat' ); ?></strong></label><br>
		<input type="text" id="ag_domaine_icon" name="ag_domaine_icon" value="<?php echo esc_attr( $icon ); ?>" maxlength="4" style="width:80px;font-size:1.4rem;text-align:center;" placeholder="⚖️">
		<br>
		<small><?php esc_html_e( 'Exemples : ⚖️ 🏛️ 👨‍⚖️ 📋 🔒 💼 🏠 👨‍👩‍👧 💰', 'ag-starter-avocat' ); ?></small>
	</p>
	<p>
		<label for="ag_domaine_examples"><strong><?php esc_html_e( 'Exemples de cas traités', 'ag-starter-avocat' ); ?></strong></label><br>
		<textarea id="ag_domaine_examples" name="ag_domaine_examples" rows="4" style="width:100%;"><?php echo esc_textarea( $examples ); ?></textarea>
		<br>
		<small><?php esc_html_e( '1 ligne par exemple. Affiché en liste à puces sur la fiche du domaine.', 'ag-starter-avocat' ); ?></small>
	</p>
	<?php
}

/**
 * Save the meta box values.
 *
 * @param int $post_id Post ID.
 */
function ag_starter_avocat_domaine_save_meta( $post_id ) {
	if ( ! isset( $_POST['ag_domaine_meta_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ag_domaine_meta_nonce'] ) ), 'ag_domaine_meta_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( isset( $_POST['ag_domaine_icon'] ) ) {
		update_post_meta( $post_id, '_ag_domaine_icon', sanitize_text_field( wp_unslash( $_POST['ag_domaine_icon'] ) ) );
	}
	if ( isset( $_POST['ag_domaine_examples'] ) ) {
		update_post_meta( $post_id, '_ag_domaine_examples', sanitize_textarea_field( wp_unslash( $_POST['ag_domaine_examples'] ) ) );
	}
}
add_action( 'save_post_ag_domaine', 'ag_starter_avocat_domaine_save_meta' );

/**
 * Helper : retrieve all published domaines as WP_Post objects, ordered
 * by menu_order then date.
 *
 * @param int $limit Number of domaines to return.
 * @return WP_Post[]
 */
function ag_starter_avocat_get_domaines( $limit = 6 ) {
	return get_posts(
		array(
			'post_type'      => 'ag_domaine',
			'post_status'    => 'publish',
			'posts_per_page' => max( 1, (int) $limit ),
			'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
		)
	);
}
