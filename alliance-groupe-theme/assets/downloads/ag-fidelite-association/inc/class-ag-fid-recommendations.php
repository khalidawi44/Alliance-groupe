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
		add_action( 'admin_menu',    array( $this, 'register_page' ) );
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
				'slug'   => 'cookie-law-info',
				'name'   => 'CookieYes (RGPD)',
				'desc'   => 'Bandeau cookies conforme CNIL/RGPD avec consentement granulaire.',
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
			<h1>Extensions recommandées pour votre association</h1>
			<p>Voici les outils que nous recommandons pour gérer une association moderne avec WordPress. Cliquez sur "Installer" pour ajouter une extension.</p>

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
