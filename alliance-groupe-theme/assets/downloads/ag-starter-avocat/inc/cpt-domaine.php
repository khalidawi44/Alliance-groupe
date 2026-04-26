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
		<label for="ag_domaine_icon"><strong><?php esc_html_e( 'Icône', 'ag-starter-avocat' ); ?></strong></label><br>
		<input type="text" id="ag_domaine_icon" name="ag_domaine_icon" value="<?php echo esc_attr( $icon ); ?>" maxlength="20" style="width:160px;font-size:1rem;text-align:center;" placeholder="scales">
		<br>
		<small><?php esc_html_e( 'Mots-clés SVG (recommandé) : scales, gavel, shield, briefcase, house, family, document, heart, lock, bank. Sinon emoji : ⚖️ 🏛️ 👨‍⚖️ 📋 🔒 💼 🏠 👨‍👩‍👧 💰', 'ag-starter-avocat' ); ?></small>
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

/**
 * Returns a background image URL for a domain based on its icon keyword
 * (or featured image priority, handled in the caller). Each keyword maps
 * to a free Unsplash URL representing the legal subject.
 *
 * @param string $icon Keyword (scales, gavel, shield, etc.).
 * @return string URL or empty string.
 */
function ag_starter_avocat_get_domaine_bg_url( $icon ) {
	$icon = strtolower( trim( (string) $icon ) );
	$map = array(
		'scales'    => 'https://images.unsplash.com/photo-1505664194779-8beaceb93744?w=1200&q=80', // courthouse columns
		'gavel'     => 'https://images.unsplash.com/photo-1589994965851-a8f479c573a9?w=1200&q=80', // gavel scales
		'shield'    => 'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?w=1200&q=80', // law library
		'briefcase' => 'https://images.unsplash.com/photo-1556761175-5973dc0f32e7?w=1200&q=80', // office meeting
		'house'     => 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=1200&q=80', // real estate
		'family'    => 'https://images.unsplash.com/photo-1511895426328-dc8714191300?w=1200&q=80', // family hands
		'document'  => 'https://images.unsplash.com/photo-1450101499163-c8848c66ca85?w=1200&q=80', // contract signing
		'heart'     => 'https://images.unsplash.com/photo-1519378058457-4c29a0a2efac?w=1200&q=80', // couple
		'lock'      => 'https://images.unsplash.com/photo-1563013544-824ae1b704d3?w=1200&q=80', // security
		'bank'      => 'https://images.unsplash.com/photo-1601597111158-2fceff292cdc?w=1200&q=80', // bank facade
	);
	return isset( $map[ $icon ] ) ? $map[ $icon ] : 'https://images.unsplash.com/photo-1505664194779-8beaceb93744?w=1200&q=80';
}

/**
 * Returns inline SVG markup for a known icon keyword, or the original
 * emoji wrapped in a span if the input is not a keyword. Kept for
 * backward compat (single domaine page may still use it).
 *
 * @param string $icon Either an SVG keyword or an emoji.
 * @return string HTML.
 */
function ag_starter_avocat_get_domaine_icon_html( $icon ) {
	$icon = trim( (string) $icon );
	if ( '' === $icon ) {
		$icon = 'scales';
	}
	$key = strtolower( $icon );

	// SVG library — vector icons (currentColor for CSS color control)
	$svgs = array(
		'scales'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v18M5 21h14M7 6h10M5 14a3 3 0 0 0 6 0L8 8 5 14zM13 14a3 3 0 0 0 6 0l-3-6-3 6z"/></svg>',
		'gavel'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m14 13-7.5 7.5a2.12 2.12 0 0 1-3-3L11 10"/><path d="m16 16 6-6"/><path d="m8 8 6-6"/><path d="m9 7 8 8"/><path d="m21 11-8-8"/></svg>',
		'shield'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>',
		'briefcase' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M2 13h20"/></svg>',
		'house'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 10 9-7 9 7v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg>',
		'family'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="7" r="3"/><circle cx="17" cy="9" r="2.5"/><path d="M3 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/><path d="M21 21v-1.5a3 3 0 0 0-3-3h-1"/></svg>',
		'document'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="13" y2="17"/></svg>',
		'heart'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 1 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>',
		'lock'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
		'bank'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 21 18 0"/><path d="m3 10 9-7 9 7"/><path d="M5 10v9M9 10v9M15 10v9M19 10v9"/></svg>',
	);

	if ( isset( $svgs[ $key ] ) ) {
		return '<span class="ag-icon-svg">' . $svgs[ $key ] . '</span>';
	}
	// Fallback : emoji ou texte libre
	return '<span class="ag-icon-emoji">' . esc_html( $icon ) . '</span>';
}
