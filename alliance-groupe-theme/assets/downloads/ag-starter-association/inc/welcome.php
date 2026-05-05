<?php
/**
 * Onboarding Association : notice geant apres activation + page admin
 * 'Demarrage rapide' avec checklist + auto-install Pack Fidelite +
 * toutes les extensions en 1 clic.
 *
 * @package AG_Starter_Association
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class AG_Asso_Welcome {
	const PLUGIN_ZIP_URL = 'https://raw.githubusercontent.com/khalidawi44/Alliance-groupe/main/alliance-groupe-theme/assets/downloads/ag-fidelite-association.zip';
	const FLAG          = 'ag_asso_welcome_seen';

	public static function init() {
		add_action( 'after_switch_theme', array( __CLASS__, 'on_activate' ) );
		add_action( 'admin_menu',         array( __CLASS__, 'menu' ) );
		add_action( 'admin_notices',      array( __CLASS__, 'notice' ) );
		add_action( 'admin_init',         array( __CLASS__, 'maybe_dismiss' ) );
		add_action( 'admin_init',         array( __CLASS__, 'maybe_install_plugin' ) );
		add_action( 'in_admin_header',    array( __CLASS__, 'kill_notices_on_welcome' ), 999 );
	}

	/**
	 * Sur la page Demarrage rapide, on retire toutes les notices
	 * d'autres plugins (Forminator, Pack Fidelite, etc.) pour ne pas
	 * polluer l'interface du wizard.
	 */
	public static function kill_notices_on_welcome() {
		$screen = get_current_screen();
		if ( ! $screen || $screen->id !== 'appearance_page_ag-asso-welcome' ) return;
		remove_all_actions( 'admin_notices' );
		remove_all_actions( 'all_admin_notices' );
		remove_all_actions( 'user_admin_notices' );
		remove_all_actions( 'network_admin_notices' );
	}

	public static function on_activate() {
		set_transient( 'ag_asso_just_activated', 1, MINUTE_IN_SECONDS * 30 );
	}

	public static function menu() {
		add_theme_page(
			'Démarrage rapide — Association',
			'🚀 Démarrage rapide',
			'manage_options',
			'ag-asso-welcome',
			array( __CLASS__, 'render_page' )
		);
	}

	public static function notice() {
		if ( get_option( self::FLAG ) ) return;
		$screen = get_current_screen();
		if ( $screen && $screen->id === 'appearance_page_ag-asso-welcome' ) return;
		?>
		<div class="notice ag-asso-welcome-notice" style="border-left:4px solid #E10F1A;background:linear-gradient(135deg,#fff 0%,#fff5f5 100%);padding:24px 28px;margin:20px 0;">
			<h2 style="margin:0 0 10px;color:#E10F1A;font-size:1.6rem;">🚀 Bienvenue dans <em>AG Starter Association</em> !</h2>
			<p style="font-size:1.05rem;line-height:1.6;margin:0 0 14px;color:#333;">
				Votre site militant est <strong>presque prêt</strong>. Une page de démarrage rapide vous attend
				pour installer le Pack Fidélité gratuit, créer toutes vos pages, et configurer votre thème en quelques clics.
			</p>
			<p style="margin:14px 0 0;">
				<a href="<?php echo esc_url( admin_url( 'themes.php?page=ag-asso-welcome' ) ); ?>" class="button button-primary button-hero" style="background:#E10F1A;border-color:#B30B14;text-shadow:none;font-size:1.05rem;">
					⚡ Lancer le démarrage rapide →
				</a>
				<a href="<?php echo esc_url( add_query_arg( 'ag_asso_welcome_dismiss', '1' ) ); ?>" style="margin-left:18px;color:#888;">Ne plus afficher</a>
			</p>
		</div>
		<?php
	}

	public static function maybe_dismiss() {
		if ( ! empty( $_GET['ag_asso_welcome_dismiss'] ) && current_user_can( 'manage_options' ) ) {
			update_option( self::FLAG, time() );
			wp_safe_redirect( remove_query_arg( 'ag_asso_welcome_dismiss' ) );
			exit;
		}
	}

	public static function maybe_install_plugin() {
		if ( empty( $_GET['ag_asso_install_fid'] ) || ! current_user_can( 'install_plugins' ) ) return;
		if ( ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'ag_asso_install_fid' ) ) return;

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/misc.php';
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		require_once ABSPATH . 'wp-admin/includes/class-automatic-upgrader-skin.php';

		// Telecharge le zip en local d'abord (raw.githubusercontent peut
		// renvoyer un content-type bizarre que Plugin_Upgrader rejette
		// en remote install).
		WP_Filesystem();
		$tmp = download_url( self::PLUGIN_ZIP_URL, 30 );
		if ( is_wp_error( $tmp ) ) {
			set_transient( 'ag_asso_fid_install_result', 'fail:' . $tmp->get_error_message(), 30 );
			wp_safe_redirect( admin_url( 'themes.php?page=ag-asso-welcome&ag_asso_fid_done=1' ) );
			exit;
		}
		$upgrader = new Plugin_Upgrader( new Automatic_Upgrader_Skin() );
		$result   = $upgrader->install( $tmp );
		@unlink( $tmp );
		$ok = ! is_wp_error( $result ) && $result;
		if ( $ok ) {
			$plugin_file = 'ag-fidelite-association/ag-fidelite-association.php';
			if ( file_exists( WP_PLUGIN_DIR . '/' . $plugin_file ) ) activate_plugin( $plugin_file );
		}
		set_transient( 'ag_asso_fid_install_result', $ok ? 'ok' : 'fail', 30 );
		wp_safe_redirect( admin_url( 'themes.php?page=ag-asso-welcome&ag_asso_fid_done=1' ) );
		exit;
	}

	private static function step_status( $done ) {
		return $done
			? '<span class="ag-asso-w__chip ag-asso-w__chip--ok">✓ Fait</span>'
			: '<span class="ag-asso-w__chip ag-asso-w__chip--todo">À faire</span>';
	}

	public static function render_page() {
		$plugin_active = is_plugin_active( 'ag-fidelite-association/ag-fidelite-association.php' );
		$plugin_inst   = file_exists( WP_PLUGIN_DIR . '/ag-fidelite-association/ag-fidelite-association.php' );
		$pages_count   = count( get_pages( array( 'post_status' => 'publish' ) ) );
		$pages_done    = $pages_count >= 8;
		$has_combat    = (bool) get_posts( array( 'post_type' => 'ag_combat', 'posts_per_page' => 1, 'post_status' => 'any' ) );
		$has_event     = (bool) get_posts( array( 'post_type' => 'ag_evenement', 'posts_per_page' => 1, 'post_status' => 'any' ) );
		$has_logo      = has_custom_logo();

		// Count extensions recommandees installees
		$reco_slugs = array( 'paid-memberships-pro', 'give', 'wp-stripe-checkout', 'mailpoet', 'wpforms-lite', 'the-events-calendar', 'polylang', 'wordpress-seo', 'complianz-gdpr', 'wordfence', 'updraftplus', 'leaflet-map', 'wp-polls' );
		$ext_installed = 0;
		if ( function_exists( 'get_plugins' ) ) {
			$all = array_keys( get_plugins() );
			foreach ( $reco_slugs as $slug ) {
				foreach ( $all as $p ) {
					if ( strpos( $p, $slug . '/' ) === 0 || strpos( $p, $slug . '.php' ) !== false ) { $ext_installed++; break; }
				}
			}
		}
		$ext_done = $ext_installed >= 5;
		$asso_name     = get_theme_mod( 'ag_asso_name', '' );
		$name_done     = $asso_name && strpos( $asso_name, '[' ) === false && strpos( $asso_name, 'Mouvement Citoyen Solidaire' ) === false;

		$install_url   = wp_nonce_url( add_query_arg( 'ag_asso_install_fid', '1', admin_url( 'themes.php?page=ag-asso-welcome' ) ), 'ag_asso_install_fid' );
		$reco_url      = admin_url( 'admin.php?page=ag-fid-recommendations' );
		$setup_url     = wp_nonce_url( add_query_arg( 'ag_fid_setup', '1', admin_url( 'admin.php?page=ag-fid-recommendations' ) ), 'ag_fid_setup' );
		$customize_url = admin_url( 'customize.php' );
		?>
		<style>
			.ag-asso-w { max-width: 1080px; margin: 24px auto; }
			.ag-asso-w__hero { background: linear-gradient(135deg, #0A0A0D 0%, #1a1a22 100%); color: #fff; padding: 40px 36px; border-radius: 12px; margin-bottom: 28px; border: 1px solid rgba(225,15,26,.4); }
			.ag-asso-w__hero h1 { font-size: 2.2rem; margin: 0 0 12px; color: #fff; }
			.ag-asso-w__hero p { font-size: 1.05rem; line-height: 1.6; color: rgba(255,255,255,.85); margin: 0; max-width: 720px; }
			.ag-asso-w__hero strong { color: #FFD23F; }
			.ag-asso-w__steps { display: grid; grid-template-columns: 1fr; gap: 16px; }
			.ag-asso-w__step { background: #fff; border: 2px solid #e5e5e5; border-radius: 10px; padding: 24px 28px; display: flex; gap: 22px; align-items: flex-start; transition: border-color .2s ease; }
			.ag-asso-w__step--done { border-color: #28a745; background: #f5fbf7; }
			.ag-asso-w__step--todo { border-color: #E10F1A; }
			.ag-asso-w__num { width: 50px; height: 50px; flex-shrink: 0; border-radius: 50%; background: #E10F1A; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; font-weight: 700; font-family: Anton, Impact, sans-serif; }
			.ag-asso-w__step--done .ag-asso-w__num { background: #28a745; }
			.ag-asso-w__body { flex: 1; }
			.ag-asso-w__body h3 { font-size: 1.2rem; margin: 0 0 6px; color: #0A0A0D; }
			.ag-asso-w__body p  { margin: 0 0 12px; color: #555; line-height: 1.55; }
			.ag-asso-w__chip { display: inline-block; padding: 3px 12px; border-radius: 100px; font-size: .78rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 8px; }
			.ag-asso-w__chip--ok   { background: #d4edda; color: #155724; }
			.ag-asso-w__chip--todo { background: #f8d7da; color: #721c24; }
			.ag-asso-w__cta { display: inline-block; padding: 10px 22px; background: #E10F1A; color: #fff; text-decoration: none; border-radius: 6px; font-weight: 700; transition: background .2s ease; }
			.ag-asso-w__cta:hover { background: #B30B14; color: #fff; }
			.ag-asso-w__cta--secondary { background: transparent; color: #E10F1A; border: 2px solid #E10F1A; padding: 8px 20px; }
			.ag-asso-w__cta--secondary:hover { background: #E10F1A; color: #fff; }
			.ag-asso-w__final { margin-top: 32px; padding: 28px; background: linear-gradient(135deg, #FFD23F 0%, #FFA500 100%); border-radius: 12px; text-align: center; color: #0A0A0D; }
			.ag-asso-w__final h2 { margin: 0 0 12px; }
			.ag-asso-w__final .button { background: #0A0A0D !important; color: #fff !important; border-color: #0A0A0D !important; font-size: 1.05rem; padding: 6px 30px; }
		</style>
		<div class="ag-asso-w wrap">
			<?php if ( ! empty( $_GET['ag_asso_fid_done'] ) ) : $r = get_transient( 'ag_asso_fid_install_result' ); ?>
				<div class="notice notice-<?php echo $r === 'ok' ? 'success' : 'error'; ?>"><p>
					<?php echo $r === 'ok' ? '<strong>✓ Pack Fidélité installé et activé.</strong> Continuez les étapes ci-dessous.' : '<strong>✗ Échec de l\'installation.</strong> Téléchargez manuellement depuis <a href="https://alliancegroupe-inc.com/wordpress-association">alliancegroupe-inc.com</a>.'; ?>
				</p></div>
			<?php endif; ?>

			<div class="ag-asso-w__hero">
				<h1>🚀 Démarrage rapide — Association</h1>
				<p>Bienvenue ! Suivez ces <strong>5 étapes</strong> pour mettre votre site militant en ligne en moins de 15 minutes. La plupart se font en 1 clic.</p>
			</div>

			<div class="ag-asso-w__steps">

				<!-- 1. Pack Fidélité -->
				<div class="ag-asso-w__step ag-asso-w__step--<?php echo $plugin_active ? 'done' : 'todo'; ?>">
					<div class="ag-asso-w__num">1</div>
					<div class="ag-asso-w__body">
						<?php echo self::step_status( $plugin_active ); ?>
						<h3>Installer le Pack Fidélité (gratuit)</h3>
						<p>Plugin compagnon qui crée toutes vos pages (manifeste, combats, événements, dons…), les rôles utilisateurs (sympathisant, adhérent, militant), 6 combats + 5 événements + 5 articles de démo, et la salle visio AG.</p>
						<?php if ( ! $plugin_active ) : ?>
							<a href="<?php echo esc_url( $install_url ); ?>" class="ag-asso-w__cta">⚡ Installer en 1 clic</a>
						<?php else : ?>
							<a href="<?php echo esc_url( $reco_url ); ?>" class="ag-asso-w__cta ag-asso-w__cta--secondary">Voir le tableau de bord</a>
						<?php endif; ?>
					</div>
				</div>

				<!-- 2. Pages + démo -->
				<div class="ag-asso-w__step ag-asso-w__step--<?php echo ( $pages_done && $has_combat && $has_event ) ? 'done' : 'todo'; ?>">
					<div class="ag-asso-w__num">2</div>
					<div class="ag-asso-w__body">
						<?php echo self::step_status( $pages_done && $has_combat && $has_event ); ?>
						<h3>Créer toutes les pages + contenu démo</h3>
						<p>Crée en 1 clic : 13 pages (manifeste, combats, dons, RGPD…), 6 combats militants, 5 événements avec dates/heures, 5 articles d'actualité, 3 pétitions, 5 groupes locaux. Tout est éditable ensuite.</p>
						<?php if ( $plugin_active ) : ?>
							<a href="<?php echo esc_url( $setup_url ); ?>" class="ag-asso-w__cta">📦 Lancer le setup</a>
						<?php else : ?>
							<em style="color:#888;">Installez d'abord le Pack Fidélité (étape 1).</em>
						<?php endif; ?>
					</div>
				</div>

				<!-- 3. Extensions recommandées -->
				<div class="ag-asso-w__step ag-asso-w__step--<?php echo $ext_done ? 'done' : 'todo'; ?>">
					<div class="ag-asso-w__num">3</div>
					<div class="ag-asso-w__body">
						<?php echo self::step_status( $ext_done ); ?>
						<h3>Installer les 13 extensions WordPress recommandées <small style="color:#888;font-weight:400;">(<?php echo (int) $ext_installed; ?>/13 installées)</small></h3>
						<p>Paid Memberships Pro (adhésions Stripe), GiveWP (dons + reçus fiscaux 66%), MailPoet (newsletter), WPForms, Yoast SEO, Complianz (RGPD), Wordfence (sécurité), UpdraftPlus (sauvegardes), Polylang (multilingue)…</p>
						<?php if ( $plugin_active ) : ?>
							<a href="<?php echo esc_url( $reco_url ); ?>" class="ag-asso-w__cta">🧩 Voir et tout installer</a>
						<?php else : ?>
							<em style="color:#888;">Disponible après installation du Pack Fidélité.</em>
						<?php endif; ?>
					</div>
				</div>

				<!-- 4. Personnalisation -->
				<div class="ag-asso-w__step ag-asso-w__step--<?php echo ( $name_done && $has_logo ) ? 'done' : 'todo'; ?>">
					<div class="ag-asso-w__num">4</div>
					<div class="ag-asso-w__body">
						<?php echo self::step_status( $name_done && $has_logo ); ?>
						<h3>Personnaliser couleurs, logo, textes</h3>
						<p>Choisissez votre <strong>préréglage de couleurs</strong> (rouge militant / bleu progressiste / vert écolo / violet féministe / orange solidaire / noir minimaliste) ou définissez vos propres couleurs. Uploadez votre logo et remplissez les infos de votre asso.</p>
						<a href="<?php echo esc_url( $customize_url ); ?>" class="ag-asso-w__cta">🎨 Ouvrir le Personnalisateur</a>
					</div>
				</div>

				<!-- 5. Voir le site -->
				<div class="ag-asso-w__step">
					<div class="ag-asso-w__num">5</div>
					<div class="ag-asso-w__body">
						<h3>Vérifier votre site</h3>
						<p>Ouvrez votre site dans un nouvel onglet pour vérifier le rendu. Cliquez sur la pilule "Template gratuit" à gauche pour voir le mini-panneau Alliance Groupe (visible uniquement par vos visiteurs).</p>
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" class="ag-asso-w__cta">👁 Voir le site →</a>
					</div>
				</div>

			</div>

			<div style="margin-top:28px;padding:18px 22px;background:#fff;border-left:4px solid #0073aa;border-radius:6px;">
				<h3 style="margin:0 0 6px;">🔄 Mises à jour du thème</h3>
				<p style="margin:0 0 10px;color:#555;">Version installée : <strong>v<?php echo esc_html( wp_get_theme()->get( 'Version' ) ); ?></strong>. Le thème se met à jour automatiquement depuis GitHub (vérification toutes les 12h, apparaît dans <strong>Apparence → Thèmes</strong>).</p>
				<p style="margin:0;">
					<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'themes.php?page=ag-asso-welcome&ag_asso_check_theme=1' ), 'ag_asso_check_theme' ) ); ?>" class="button">🔄 Vérifier maintenant</a>
				</p>
				<?php if ( ! empty( $_GET['ag_asso_theme_checked'] ) ) : ?>
					<p style="color:#155724;margin:10px 0 0;"><strong>✓ Vérification effectuée.</strong> Allez dans <a href="<?php echo esc_url( admin_url( 'themes.php' ) ); ?>">Apparence → Thèmes</a> pour voir si une mise à jour est dispo.</p>
				<?php endif; ?>
			</div>

			<div class="ag-asso-w__final">
				<h2>💡 Besoin d'aide ?</h2>
				<p style="max-width:680px;margin:0 auto 16px;line-height:1.6;">
					Ce thème est offert gratuitement par <strong>Alliance Groupe</strong> aux associations militantes. Si vous voulez aller plus loin (formation, accompagnement, hébergement managé), notre équipe répond gratuitement.
				</p>
				<a href="https://alliancegroupe-inc.com/wordpress-association" target="_blank" class="button button-primary">📞 Nous contacter</a>
			</div>
		</div>
		<?php
	}
}
AG_Asso_Welcome::init();
