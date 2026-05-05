<?php
/**
 * Notice admin recommandant les extensions WordPress utiles pour
 * une association. Détecte celles déjà installées et propose les
 * autres avec liens directs.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AG_Fid_Recommendations {
	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) self::$instance = new self();
		return self::$instance;
	}

	const DISMISSED_KEY = 'ag_fid_reco_dismissed';

	private function __construct() {
		add_action( 'admin_notices', array( $this, 'maybe_show_notice' ) );
		add_action( 'admin_init',    array( $this, 'maybe_dismiss' ) );
		add_action( 'admin_init',    array( $this, 'maybe_run_setup' ) );
		add_action( 'admin_init',    array( $this, 'maybe_install_all' ) );
		add_action( 'admin_menu',    array( $this, 'register_page' ) );
	}

	public function maybe_install_all() {
		if ( empty( $_GET['ag_fid_install_all'] ) || ! current_user_can( 'install_plugins' ) ) return;
		if ( ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'ag_fid_install_all' ) ) return;

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/misc.php';
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		require_once ABSPATH . 'wp-admin/includes/class-automatic-upgrader-skin.php';

		$installed = $skipped = $failed = 0;
		foreach ( $this->get_recos() as $reco ) {
			if ( $this->is_installed( $reco['slug'] ) ) { $skipped++; continue; }
			$api = plugins_api( 'plugin_information', array(
				'slug'   => $reco['slug'],
				'fields' => array( 'sections' => false, 'short_description' => false ),
			) );
			if ( is_wp_error( $api ) || empty( $api->download_link ) ) { $failed++; continue; }
			$upgrader = new Plugin_Upgrader( new Automatic_Upgrader_Skin() );
			$result   = $upgrader->install( $api->download_link );
			if ( is_wp_error( $result ) || ! $result ) { $failed++; } else { $installed++; }
		}
		set_transient( 'ag_fid_install_summary', compact( 'installed', 'skipped', 'failed' ), 60 );
		wp_safe_redirect( add_query_arg( 'ag_fid_installed', '1', remove_query_arg( array( 'ag_fid_install_all', '_wpnonce' ) ) ) );
		exit;
	}

	public function maybe_run_setup() {
		if ( empty( $_GET['ag_fid_setup'] ) || ! current_user_can( 'manage_options' ) ) return;
		if ( ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'ag_fid_setup' ) ) return;
		AG_Fid_Roles::create_roles();
		AG_Fid_Pages::create_default_pages();
		// On force toujours le re-seed pour recuperer les contenus de
		// demo de la derniere version (events avec date/heure, articles, etc.).
		AG_Fid_Pages::create_default_cpts( true );
		flush_rewrite_rules();
		wp_safe_redirect( add_query_arg( 'ag_fid_done', '1', remove_query_arg( array( 'ag_fid_setup', '_wpnonce', 'force' ) ) ) );
		exit;
	}

	public function get_recos() {
		return array(
			// === Adhésion / membres / dons ===
			array(
				'slug'   => 'paid-memberships-pro',
				'name'   => 'Paid Memberships Pro',
				'desc'   => 'Gestion d\'adhésions multi-niveaux (sympathisant / adhérent / militant) avec cotisations Stripe.',
				'cat'    => 'Adhésion',
			),
			array(
				'slug'   => 'give',
				'name'   => 'GiveWP',
				'desc'   => 'Plateforme de dons avec reçus fiscaux automatiques (66% pour les associations d\'intérêt général).',
				'cat'    => 'Dons',
			),
			array(
				'slug'   => 'wp-stripe-checkout',
				'name'   => 'WP Simple Pay (Stripe)',
				'desc'   => 'Paiements ponctuels et récurrents Stripe, idéal pour cotisations.',
				'cat'    => 'Paiement',
			),
			// === Communication ===
			array(
				'slug'   => 'mailpoet',
				'name'   => 'MailPoet',
				'desc'   => 'Newsletter aux adhérents directement depuis WordPress, sans Mailchimp.',
				'cat'    => 'Newsletter',
			),
			array(
				'slug'   => 'wpforms-lite',
				'name'   => 'WPForms',
				'desc'   => 'Constructeur de formulaires (signatures pétition, contact, sondages internes).',
				'cat'    => 'Formulaires',
			),
			// === Événements ===
			array(
				'slug'   => 'the-events-calendar',
				'name'   => 'The Events Calendar',
				'desc'   => 'Calendrier d\'événements visuel, RSVP, intégration Google Calendar/iCal.',
				'cat'    => 'Événements',
			),
			// === Multilingue ===
			array(
				'slug'   => 'polylang',
				'name'   => 'Polylang',
				'desc'   => 'Multilingue gratuit (français/anglais/arabe/espagnol selon vos cibles).',
				'cat'    => 'Multilingue',
			),
			// === SEO / RGPD ===
			array(
				'slug'   => 'wordpress-seo',
				'name'   => 'Yoast SEO',
				'desc'   => 'Référencement de base. Crucial pour être trouvé sur les requêtes liées à votre cause.',
				'cat'    => 'SEO',
			),
			array(
				'slug'   => 'complianz-gdpr',
				'name'   => 'Complianz (RGPD)',
				'desc'   => 'Bandeau cookies + politique RGPD/CCPA conforme CNIL. 100% gratuit illimité (pas de quota mensuel comme CookieYes), génération auto des mentions légales.',
				'cat'    => 'RGPD',
			),
			// === Sécurité / sauvegarde ===
			array(
				'slug'   => 'wordfence',
				'name'   => 'Wordfence Security',
				'desc'   => 'Pare-feu + scan malware. Indispensable pour un site associatif visible (cibles d\'attaques fréquentes).',
				'cat'    => 'Sécurité',
			),
			array(
				'slug'   => 'updraftplus',
				'name'   => 'UpdraftPlus',
				'desc'   => 'Sauvegarde automatique vers Google Drive / Dropbox. Restauration en 1 clic.',
				'cat'    => 'Sauvegarde',
			),
			// === Cartographie ===
			array(
				'slug'   => 'leaflet-map',
				'name'   => 'Leaflet Map',
				'desc'   => 'Carte interactive (OpenStreetMap, sans Google) pour afficher les groupes locaux.',
				'cat'    => 'Carte',
			),
			// === Vote / sondages ===
			array(
				'slug'   => 'wp-polls',
				'name'   => 'WP-Polls',
				'desc'   => 'Sondages internes pour vote AG simple (validez les motions en un clic).',
				'cat'    => 'Vote',
			),
		);
	}

	public function is_installed( $slug ) {
		$plugins = get_plugins();
		foreach ( $plugins as $path => $info ) {
			if ( strpos( $path, $slug . '/' ) === 0 || strpos( $path, $slug . '.php' ) !== false ) {
				return $path;
			}
		}
		return false;
	}

	public function maybe_show_notice() {
		if ( get_option( self::DISMISSED_KEY ) ) return;
		$screen = get_current_screen();
		if ( $screen && $screen->id === 'plugins' ) return; // évite double notice
		?>
		<div class="notice notice-info is-dismissible">
			<p><strong>AG Fidélité Association :</strong> 13 extensions recommandées pour gérer votre association (adhésions, dons, newsletter, événements, RGPD, sécurité…).</p>
			<p>
				<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=ag-fid-recommendations' ) ); ?>">Voir la liste</a>
				<a class="button" href="<?php echo esc_url( add_query_arg( 'ag_fid_dismiss', '1' ) ); ?>">Masquer</a>
			</p>
		</div>
		<?php
	}

	public function maybe_dismiss() {
		if ( ! empty( $_GET['ag_fid_dismiss'] ) && current_user_can( 'manage_options' ) ) {
			update_option( self::DISMISSED_KEY, time() );
			wp_safe_redirect( remove_query_arg( 'ag_fid_dismiss' ) );
			exit;
		}
	}

	public function register_page() {
		add_menu_page(
			'Pack Fidélité',
			'Pack Fidélité',
			'manage_options',
			'ag-fid-recommendations',
			array( $this, 'render_page' ),
			'dashicons-megaphone',
			3
		);
	}

	public function render_page() {
		$recos = $this->get_recos();
		?>
		<div class="wrap">
			<h1>Pack Fidélité — Association</h1>

			<?php if ( ! empty( $_GET['ag_fid_done'] ) ) : ?>
				<div class="notice notice-success"><p><strong>Setup terminé.</strong> Pages, rôles et permaliens ont été créés. Va voir tes pages dans <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=page' ) ); ?>">Pages</a>.</p></div>
			<?php endif; ?>

			<?php if ( ! empty( $_GET['ag_fid_installed'] ) ) :
				$s = get_transient( 'ag_fid_install_summary' );
				if ( $s ) : ?>
					<div class="notice notice-success"><p><strong>Installation extensions terminée.</strong> Installées : <?php echo (int) $s['installed']; ?> · Déjà présentes : <?php echo (int) $s['skipped']; ?> · Échecs : <?php echo (int) $s['failed']; ?>. Active-les depuis <a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>">Extensions</a>.</p></div>
				<?php endif;
			endif; ?>

			<div style="margin:18px 0;padding:18px;background:#fff;border-left:4px solid #2271b1;">
				<h2 style="margin-top:0;">Installer toutes les extensions recommandées en 1 clic</h2>
				<p>Télécharge et installe automatiquement les <strong>13 extensions</strong> recommandées (Paid Memberships Pro, GiveWP, MailPoet, WPForms, The Events Calendar, Polylang, Yoast SEO, CookieYes, Wordfence, UpdraftPlus, Leaflet Map, WP-Polls, WP Simple Pay). Tu pourras les activer ensuite une à une depuis la page Extensions.</p>
				<p>
					<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=ag-fid-recommendations&ag_fid_install_all=1' ), 'ag_fid_install_all' ) ); ?>" class="button button-primary button-large" onclick="return confirm('Cela va télécharger et installer 13 extensions depuis WordPress.org. Continuer ?');">Installer les 13 extensions recommandées</a>
				</p>
				<p><small>Idempotent : les extensions déjà installées sont sautées. Aucune ne sera activée automatiquement (sécurité).</small></p>
			</div>

			<div style="margin:18px 0;padding:18px;background:#fff;border-left:4px solid #28a745;">
				<h2 style="margin-top:0;">Configuration initiale</h2>
				<p>Si les pages séparées (manifeste, combats, événements, dons, adhérer…) et les rôles (sympathisant, adhérent, militant, trésorier, secrétaire, président·e) n'ont pas été créés automatiquement à l'activation du plugin, clique ci-dessous pour les créer maintenant.</p>
				<p>
					<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=ag-fid-recommendations&ag_fid_setup=1' ), 'ag_fid_setup' ) ); ?>" class="button button-primary button-large" onclick="return confirm('Cela va supprimer les CPT et articles de démo existants puis les recréer avec les dernières versions. Pages + rôles seront mis à jour. Confirmer ?');">Lancer / réinitialiser la démo</a>
				</p>
				<p><small>Crée pages + rôles + permaliens, et <strong>réinitialise les contenus de démo</strong> (combats, événements avec date/heure, groupes locaux, pétitions, 5 articles d'actualité). À cliquer après chaque mise à jour du thème pour récupérer les nouveaux contenus.</small></p>
			</div>

			<div style="margin:18px 0;padding:18px;background:#fff;border-left:4px solid #0073aa;">
				<h2 style="margin-top:0;">🔄 Mises à jour automatiques</h2>
				<p>Ce plugin se met à jour automatiquement depuis GitHub (vérification toutes les 12h). Quand une nouvelle version est disponible, elle apparaît dans <strong>Extensions → Mises à jour</strong> comme n'importe quel plugin.</p>
				<p>Version installée : <strong>v<?php echo esc_html( AG_FID_VERSION ); ?></strong></p>
				<p>
					<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=ag-fid-recommendations&ag_fid_check_update=1' ), 'ag_fid_check_update' ) ); ?>" class="button">🔄 Vérifier les mises à jour maintenant</a>
				</p>
				<?php if ( ! empty( $_GET['ag_fid_checked'] ) ) : ?>
					<p style="color:#155724;"><strong>✓ Vérification effectuée.</strong> Allez dans <a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>">Extensions</a> pour voir les mises à jour disponibles.</p>
				<?php endif; ?>
			</div>

			<h2>Extensions recommandées</h2>
			<p>Voici les outils que nous recommandons pour gérer une association moderne avec WordPress. Cliquez sur "Installer" pour ajouter une extension.</p>
			<p style="background:#d4edda;border-left:4px solid #28a745;padding:10px 14px;margin:12px 0;border-radius:4px;color:#155724;"><strong>✓ 100% versions gratuites</strong> — toutes ces extensions sont des plugins gratuits du dépôt WordPress.org, sans période d'essai ni quota mensuel imposé. Des fonctionnalités payantes optionnelles existent en addon mais ne sont jamais nécessaires.</p>

			<table class="widefat striped">
				<thead>
					<tr>
						<th>Catégorie</th>
						<th>Extension</th>
						<th>Description</th>
						<th>Statut</th>
						<th>Action</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $recos as $r ) :
					$installed = $this->is_installed( $r['slug'] );
					$active    = $installed && is_plugin_active( $installed );
					?>
					<tr>
						<td><?php echo esc_html( $r['cat'] ); ?></td>
						<td><strong><?php echo esc_html( $r['name'] ); ?></strong></td>
						<td><?php echo esc_html( $r['desc'] ); ?></td>
						<td>
							<?php if ( $active ) : ?>
								<span style="color:#28a745;">✓ Active</span>
							<?php elseif ( $installed ) : ?>
								<span style="color:#d4b45c;">Installée</span>
							<?php else : ?>
								<span style="color:#888;">Non installée</span>
							<?php endif; ?>
						</td>
						<td>
							<?php if ( $active ) : ?>
								—
							<?php elseif ( $installed ) : ?>
								<a class="button" href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>">Activer</a>
							<?php else : ?>
								<a class="button button-primary" href="<?php echo esc_url( admin_url( 'plugin-install.php?s=' . urlencode( $r['name'] ) . '&tab=search&type=term' ) ); ?>">Installer</a>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>

			<h2 style="margin-top:32px;">Fonctionnalités déjà incluses dans Pack Fidélité</h2>
			<ul style="list-style:disc;padding-left:24px;">
				<li>Pages séparées créées automatiquement (manifeste, combats, événements, groupes, signer, don, adhérer, mon espace, mentions, RGPD, statuts)</li>
				<li>Custom Post Types : combats, événements, groupes locaux, pétitions, PV/comptes-rendus</li>
				<li>Rôles utilisateurs : sympathisant, adhérent, militant, trésorier, secrétaire, président·e</li>
				<li>URLs personnalisées : <code>/adherent/jean-dupont</code>, <code>/militant/...</code>, etc.</li>
				<li>Espace membre avec accès conditionnel aux PV (adhérents uniquement)</li>
				<li>Customizer Identité association : SIRET, RNA, IBAN, président·e, montant cotisation</li>
				<li>Shortcodes pour formulaires (signer, adhérer, compte) et grilles (combats, événements, groupes, pétitions)</li>
			</ul>
		</div>
		<?php
	}
}
