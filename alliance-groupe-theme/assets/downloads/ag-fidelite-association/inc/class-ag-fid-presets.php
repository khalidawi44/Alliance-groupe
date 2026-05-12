<?php
/**
 * Pack Fidélité — Presets de contenu prêts à appliquer en 1 clic.
 *
 * Permet a un client (ou un installateur Alliance Groupe) d'appliquer
 * en une fois un jeu de contenu cible (Customizer + pages + CPT)
 * sans avoir a copier-coller manuellement chaque champ.
 *
 * EXEMPLE : le preset 'lfi_nantes_clos_toreau' applique tous les textes
 * du groupe local LFI Nantes Sud Clos Toreau (logement, violences
 * policieres, services publics, justice climatique, democratie reelle,
 * solidarite internationale).
 *
 * Le preset par defaut 'generic_militant' = contenu generique deja
 * present par seed initial. Aucun preset n'est applique automatiquement.
 *
 * REGLE INTERNE : pour ajouter un nouveau preset, etendre le tableau
 * dans self::get_presets(). Chaque preset declare ses theme_mods,
 * ses pages (slug => [title, first_paragraph]), et ses CPT (combats).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AG_Fid_Presets {

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ), 28 );
		add_action( 'admin_init', array( __CLASS__, 'maybe_apply_preset' ) );
		add_action( 'admin_notices', array( __CLASS__, 'maybe_show_applied' ) );
	}

	public static function register_menu() {
		add_submenu_page(
			'ag-fid-recommendations',
			__( 'Presets de contenu', 'ag-fidelite-association' ),
			'🎯 ' . __( 'Presets de contenu', 'ag-fidelite-association' ),
			'manage_options',
			'ag-fid-presets',
			array( __CLASS__, 'render' )
		);
	}

	/**
	 * Definition des presets disponibles. Chaque preset = un identifiant
	 * + label + description + payload (theme_mods, pages, combats).
	 */
	private static function get_presets() {
		return array(

			'lfi_nantes_clos_toreau' => array(
				'label' => '✊ LFI Nantes Sud Clos Toreau',
				'desc'  => 'Groupe local La France Insoumise — combats : logement, violences policières, services publics, écologie populaire, démocratie réelle, solidarité internationale.',
				'mods'  => array(
					'ag_asso_name'         => 'LFI Nantes Sud Clos Toreau',
					'ag_asso_baseline'     => 'Groupe local La France Insoumise',
					'ag_asso_slogan'       => 'LFI Nantes Sud Clos Toreau',
					'ag_asso_hero_title'   => 'Pour le logement digne, contre les violences policières',
					'ag_asso_hero_sub'     => 'Notre groupe local lutte au quotidien dans nos quartiers pour la justice sociale, la dignité humaine et les services publics.',
					'ag_asso_cta_label'    => 'Rejoindre le groupe',
					'ag_asso_cta_url'      => '/adherer/',
					'ag_asso_cta2_label'   => 'Signer nos appels',
					'ag_asso_cta2_url'     => '/signer/',
					'ag_asso_stat1_value'  => '1',
					'ag_asso_stat1_label'  => 'groupe local Clos Toreau',
					'ag_asso_stat2_value'  => '30+',
					'ag_asso_stat2_label'  => 'adhérent·es actif·ves',
					'ag_asso_stat3_value'  => '200+',
					'ag_asso_stat3_label'  => 'sympathisant·es',
				),
				'pages' => array(
					'manifeste' => array(
						'title'   => 'Notre manifeste',
						'content' => "<!-- wp:paragraph -->\n<p>À Nantes Sud Clos Toreau, nous sommes des habitant·es engagé·es dans la lutte pour une société plus juste. Notre groupe local, affilié à La France Insoumise, porte sur le terrain nos combats prioritaires : le droit au logement, la fin des violences et intimidations policières dans nos quartiers, la défense des services publics, et la justice climatique populaire.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n<p><strong>Nous sommes des habitant·es de Nantes Sud.</strong> Pas des élu·es, pas des permanent·es de parti. Des voisin·es, des locataires, des salarié·es, des chômeur·euses, des étudiant·es, des retraité·es. Nous croyons qu'une autre société est possible — et nous nous organisons pour la construire à partir du quartier.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:heading -->\n<h2>Le logement d'abord</h2>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph -->\n<p>Quand des familles vivent dans des logements indignes, quand les loyers explosent et que les expulsions se multiplient, la République trahit sa promesse d'égalité.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:heading -->\n<h2>La sécurité pour tou·tes, par tou·tes</h2>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph -->\n<p>Les contrôles abusifs, les intrusions sans motif, les violences policières dans nos quartiers ne nous protègent pas — ils nous humilient et nous fracturent.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:heading -->\n<h2>Les services publics, fierté républicaine</h2>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph -->\n<p>L'hôpital, l'école, La Poste, les transports : ce qui faisait l'égalité géographique de la France est méthodiquement démantelé. Nous portons leur refondation.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:heading -->\n<h2>L'écologie populaire</h2>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph -->\n<p>Le dérèglement climatique frappe d'abord les plus modestes. Nous refusons l'écologie punitive : la transition doit être financée par les ultra-riches.</p>\n<!-- /wp:paragraph -->",
					),
					'combats' => array(
						'title'   => 'Nos combats',
						'content' => "<!-- wp:paragraph -->\n<p>Six combats prioritaires que nous portons jour après jour dans le quartier du Clos Toreau et au-delà : logement digne pour tou·tes, fin des violences et intimidations policières, sauvegarde des services publics, justice climatique populaire, démocratie réelle, solidarité internationale.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:shortcode -->\n[ag_fid_combats]\n<!-- /wp:shortcode -->",
					),
					'evenements' => array(
						'title'   => 'Mobilisations à venir',
						'content' => "<!-- wp:paragraph -->\n<p>Réunions publiques, manifestations, distributions de tracts, ateliers d'éducation populaire. Retrouvez-nous sur le terrain dans le quartier du Clos Toreau et plus largement à Nantes Sud.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:shortcode -->\n[ag_fid_evenements]\n<!-- /wp:shortcode -->",
					),
					'groupes' => array(
						'title'   => 'Le groupe Clos Toreau',
						'content' => "<!-- wp:paragraph -->\n<p>Notre groupe local LFI couvre Nantes Sud Clos Toreau et les quartiers voisins. Nous nous réunissons régulièrement dans le quartier — toutes et tous bienvenu·es, sans condition de prise de carte.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:shortcode -->\n[ag_fid_groupes]\n<!-- /wp:shortcode -->",
					),
					'actu' => array(
						'title'   => 'Actualités du quartier',
						'content' => "<!-- wp:paragraph -->\n<p>Suivez les actualités de notre groupe local : actions menées, mobilisations, prises de position publiques, comptes rendus de réunions.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:shortcode -->\n[ag_fid_actu]\n<!-- /wp:shortcode -->",
					),
					'signer' => array(
						'title'   => 'Signez nos appels',
						'content' => "<!-- wp:paragraph -->\n<p>Signez nos appels pour un encadrement réel des loyers à Nantes Métropole, contre les violences policières systémiques, et pour des services publics de qualité dans nos quartiers populaires.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:shortcode -->\n[ag_fid_signer]\n<!-- /wp:shortcode -->",
					),
					'don' => array(
						'title'   => 'Soutenir le groupe',
						'content' => "<!-- wp:paragraph -->\n<p>Indépendants des partis, des grandes entreprises et des grands donateurs, nous ne tenons que par vous. Chaque euro permet d'imprimer des tracts, louer des salles de réunion, organiser des actions publiques.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:shortcode -->\n[ag_fid_don]\n<!-- /wp:shortcode -->",
					),
				),
				'combats' => array(
					array( 'title' => 'Logement digne pour tou·tes',                'emoji' => '🏠', 'color' => '#3B5998', 'excerpt' => 'Encadrement effectif des loyers, réquisition des logements vacants, plan massif de logement social digne, moratoire sur les expulsions sans relogement.', 'content' => "À Clos Toreau comme partout dans nos quartiers, des familles vivent dans des logements indignes : moisissures, infiltrations, ascenseurs en panne, attentes interminables de relogement. Nous portons : <strong>encadrement effectif des loyers à Nantes Métropole, réquisition des logements vacants depuis plus de 18 mois, plan massif de logement social digne, moratoire sur les expulsions sans relogement.</strong>" ),
					array( 'title' => 'Stop aux violences et intimidations policières', 'emoji' => '⚖️', 'color' => '#E10F1A', 'excerpt' => 'Récépissé de contrôle obligatoire, formation antiraciste, démilitarisation de la doctrine du maintien de l\'ordre, démantèlement des BAC, justice indépendante.', 'content' => "Dans nos quartiers populaires, les contrôles abusifs, les intrusions sans motif et les violences quotidiennes humilient et fracturent notre population. Nous demandons : <strong>récépissé de contrôle obligatoire pour mesurer le contrôle au faciès, formation antiraciste obligatoire, démilitarisation de la doctrine du maintien de l'ordre, démantèlement des BAC, justice indépendante pour les victimes.</strong>" ),
					array( 'title' => 'Services publics dignes du nom',           'emoji' => '🏥', 'color' => '#1F8A3D', 'excerpt' => 'Moratoire sur les fermetures, plan de recrutement, gratuité progressive des transports, école publique forte.', 'content' => "Hôpital de Nantes saturé, classes surchargées, bureaux de poste fermés, lignes de bus supprimées : nos services publics ferment ou se dégradent. Nous luttons pour : <strong>moratoire sur les fermetures, plan de recrutement massif, gratuité progressive des transports en commun, école publique forte avec moins d'élèves par classe.</strong>" ),
					array( 'title' => 'Justice climatique populaire',             'emoji' => '🌍', 'color' => '#FFD23F', 'excerpt' => 'Transition financée par les ultra-riches, isolation gratuite, gratuité des transports en commun, fin des subventions aux fossiles.', 'content' => "Le dérèglement climatique frappe d'abord les plus modestes : passoires thermiques, factures qui explosent. Nous défendons une écologie populaire : <strong>transition financée par les ultra-riches et les multinationales, isolation gratuite des logements sociaux, gratuité progressive des transports en commun, fin des subventions aux énergies fossiles.</strong>" ),
					array( 'title' => 'Démocratie réelle',                         'emoji' => '🗳️', 'color' => '#8B1A8B', 'excerpt' => 'RIC, assemblée constituante tirée au sort, reconnaissance du vote blanc, révocation des élu·es.', 'content' => "La VIe République est urgente : <strong>Référendum d'Initiative Citoyenne (RIC) sur tous les sujets, assemblée constituante tirée au sort, reconnaissance du vote blanc dans les suffrages exprimés, révocation des élu·es par leurs électeur·rices.</strong>" ),
					array( 'title' => 'Solidarité internationale',                 'emoji' => '🤝', 'color' => '#FF6B35', 'excerpt' => 'Cessez-le-feu permanent à Gaza, paix juste pour la Palestine, accueil digne des migrant·es, refus de l\'extrême-droite et de la guerre.', 'content' => "<strong>Cessez-le-feu permanent à Gaza, paix juste pour la Palestine, accueil digne des migrant·es, solidarité avec les peuples en lutte.</strong> Notre internationalisme refuse l'extrême-droite et la guerre." ),
				),
			),
		);
	}

	public static function maybe_apply_preset() {
		if ( empty( $_POST['ag_fid_preset_apply'] ) || ! current_user_can( 'manage_options' ) ) return;
		check_admin_referer( 'ag_fid_apply_preset' );
		$key = sanitize_key( $_POST['ag_fid_preset_apply'] );
		$presets = self::get_presets();
		if ( ! isset( $presets[ $key ] ) ) return;
		$preset = $presets[ $key ];

		// 1. Theme mods (Customizer).
		if ( ! empty( $preset['mods'] ) ) {
			foreach ( $preset['mods'] as $mod => $val ) {
				set_theme_mod( $mod, $val );
			}
		}

		// 2. Pages : update title + content.
		if ( ! empty( $preset['pages'] ) ) {
			foreach ( $preset['pages'] as $slug => $data ) {
				$page = get_page_by_path( $slug );
				if ( ! $page ) continue;
				wp_update_post( array(
					'ID'           => $page->ID,
					'post_title'   => $data['title'],
					'post_content' => $data['content'],
				) );
			}
		}

		// 3. Combats CPT : delete existing + create new from preset.
		if ( ! empty( $preset['combats'] ) ) {
			$old = get_posts( array( 'post_type' => 'ag_combat', 'posts_per_page' => -1, 'post_status' => 'any', 'fields' => 'ids' ) );
			foreach ( $old as $pid ) wp_delete_post( $pid, true );
			foreach ( $preset['combats'] as $c ) {
				$pid = wp_insert_post( array(
					'post_type'    => 'ag_combat',
					'post_status'  => 'publish',
					'post_title'   => $c['title'],
					'post_excerpt' => $c['excerpt'],
					'post_content' => $c['content'],
				) );
				if ( $pid && ! is_wp_error( $pid ) ) {
					update_post_meta( $pid, '_ag_combat_emoji', $c['emoji'] );
					update_post_meta( $pid, '_ag_combat_color', $c['color'] );
				}
			}
		}

		set_transient( 'ag_fid_preset_applied', $key, 60 );
		wp_safe_redirect( admin_url( 'admin.php?page=ag-fid-presets&applied=1' ) );
		exit;
	}

	public static function maybe_show_applied() {
		$applied = get_transient( 'ag_fid_preset_applied' );
		if ( ! $applied ) return;
		$presets = self::get_presets();
		$label = isset( $presets[ $applied ] ) ? $presets[ $applied ]['label'] : $applied;
		?>
		<div class="notice notice-success is-dismissible" style="border-left-color:#1F8A3D;padding:14px 18px;">
			<p style="margin:0;font-size:1.05rem;"><strong>✓ Preset appliqué : <?php echo esc_html( $label ); ?></strong></p>
			<p style="margin:6px 0 0;">Tous les textes et combats ont été mis à jour. <a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank">👁 Voir le site</a> ou <a href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>">🎨 personnaliser davantage</a>.</p>
		</div>
		<?php
		delete_transient( 'ag_fid_preset_applied' );
	}

	public static function render() {
		$presets = self::get_presets();
		?>
		<div class="wrap" style="max-width:1100px;">
			<h1>🎯 Presets de contenu</h1>
			<p style="font-size:1.05rem;color:#555;">Appliquez en 1 clic un jeu de contenu prêt-à-l'emploi (textes Customizer + pages + combats). <strong>⚠ Cela remplace le contenu actuel</strong> — sauvegardez avant si vous avez beaucoup personnalisé.</p>

			<style>
				.ag-fid-preset-card { background:#fff; border:1px solid #e0e0e0; border-radius:8px; padding:20px 24px; margin-bottom:18px; }
				.ag-fid-preset-card h2 { margin:0 0 6px; font-size:1.3rem; }
				.ag-fid-preset-card .desc { color:#555; margin:0 0 14px; }
				.ag-fid-preset-card .meta { font-size:.9em; color:#888; margin:8px 0 14px; }
				.ag-fid-preset-card .meta strong { color:#444; }
			</style>

			<?php foreach ( $presets as $key => $preset ) :
				$mods_count    = isset( $preset['mods'] ) ? count( $preset['mods'] ) : 0;
				$pages_count   = isset( $preset['pages'] ) ? count( $preset['pages'] ) : 0;
				$combats_count = isset( $preset['combats'] ) ? count( $preset['combats'] ) : 0;
				?>
				<div class="ag-fid-preset-card">
					<h2><?php echo esc_html( $preset['label'] ); ?></h2>
					<p class="desc"><?php echo esc_html( $preset['desc'] ); ?></p>
					<p class="meta">
						<strong>Inclus :</strong>
						<?php echo (int) $mods_count; ?> champs Customizer ·
						<?php echo (int) $pages_count; ?> pages ·
						<?php echo (int) $combats_count; ?> combats
					</p>
					<form method="post" onsubmit="return confirm('⚠ Cela va REMPLACER le contenu actuel (Customizer hero/identité/stats + textes des pages + combats existants). Continuer ?');">
						<?php wp_nonce_field( 'ag_fid_apply_preset' ); ?>
						<input type="hidden" name="ag_fid_preset_apply" value="<?php echo esc_attr( $key ); ?>">
						<button type="submit" class="button button-primary" style="background:#E10F1A;border-color:#E10F1A;">
							🎯 Appliquer ce preset
						</button>
					</form>
				</div>
			<?php endforeach; ?>

			<div class="ag-fid-preset-card" style="background:#fffbe6;border-color:#f0d000;">
				<h2>💡 Créer votre propre preset</h2>
				<p>Les presets sont définis dans <code>inc/class-ag-fid-presets.php</code> (méthode <code>get_presets()</code>). Pour ajouter le vôtre, copiez le tableau d'un preset existant et adaptez les valeurs.</p>
				<p>Ou plus simple : utilisez le <strong>📖 Guide d'utilisation</strong> pour modifier chaque champ manuellement dans l'admin WordPress.</p>
			</div>
		</div>
		<?php
	}
}

AG_Fid_Presets::init();
