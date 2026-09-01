<?php
/**
 * AG Premium Barber — Statistiques du salon.
 *
 * Repond a une question simple que le patron se pose : « est-ce que mon site
 * sert a quelque chose ? ». On lui donne ses visiteurs, ses pages vues, d'ou
 * viennent les gens, combien de tickets sont pris, a quelles heures, et pour
 * quelles prestations.
 *
 * Choix techniques :
 *  - AUCUN service externe (pas de Google Analytics, pas de script tiers).
 *    Les donnees restent chez le client, sur son hebergement.
 *  - AUCUN cookie, AUCUNE adresse IP stockee. Un visiteur unique est
 *    reconnu par un hash sale (IP + navigateur + sel du jour) garde 24 h
 *    dans un transient ; le sel tourne chaque jour, donc rien n'est
 *    reconstituable. => pas de banniere cookies necessaire pour ce module.
 *  - Historique des tickets : la file d'attente du theme gratuit ne garde que
 *    les gens en attente (une fois servis ils disparaissent). On ecoute donc
 *    `update_option_ag_barber_queue` pour archiver chaque nouveau ticket au
 *    moment ou il apparait. Aucune modification du theme gratuit necessaire.
 *
 * @package AG_Premium_Barber
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AG_PB_Stats {

	/** Compteurs de trafic, agreges par jour. */
	const OPT_TRAFFIC = 'ag_pb_stats_traffic';

	/** Journal des tickets pris (date, heure, prestation). */
	const OPT_TICKETS = 'ag_pb_stats_tickets';

	/** Sel du jour pour le hash des visiteurs uniques. */
	const OPT_SALT = 'ag_pb_stats_salt';

	/** Nombre de jours d'historique conserves. */
	const KEEP_DAYS = 90;

	/** Nombre max de tickets archives (garde-fou taille). */
	const KEEP_TICKETS = 5000;

	public static function init() {
		add_action( 'template_redirect', array( __CLASS__, 'track' ), 99 );
		add_action( 'update_option_ag_barber_queue', array( __CLASS__, 'on_queue_change' ), 10, 2 );
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
	}

	/* ══════════════════════════════════════════════════════════════
	   COLLECTE
	   ══════════════════════════════════════════════════════════════ */

	/**
	 * Faut-il compter cette visite ?
	 * On ecarte l'admin, les robots, les prefetch et les requetes techniques.
	 */
	private static function should_count() {
		if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) return false;
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) return false;
		if ( is_404() || is_feed() || is_robots() || is_preview() ) return false;

		// Le patron qui regarde son propre site ne doit pas gonfler ses chiffres.
		if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) return false;

		// Prefetch navigateur : la page n'a pas ete reellement vue.
		if ( ! empty( $_SERVER['HTTP_PURPOSE'] ) && 'prefetch' === strtolower( $_SERVER['HTTP_PURPOSE'] ) ) return false;
		if ( ! empty( $_SERVER['HTTP_SEC_PURPOSE'] ) && false !== stripos( $_SERVER['HTTP_SEC_PURPOSE'], 'prefetch' ) ) return false;

		$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
		if ( '' === $ua ) return false;

		$bots = array( 'bot', 'crawl', 'spider', 'slurp', 'curl', 'wget', 'python', 'headless', 'lighthouse', 'pingdom', 'monitor', 'preview', 'facebookexternalhit', 'embedly', 'quora link', 'ahrefs', 'semrush', 'mj12', 'dotbot', 'petalbot', 'bytespider' );
		$ua_low = strtolower( $ua );
		foreach ( $bots as $bot ) {
			if ( false !== strpos( $ua_low, $bot ) ) return false;
		}
		return true;
	}

	/**
	 * Sel du jour. Tourne toutes les 24 h : impossible de relier les visites
	 * d'un jour a celles du lendemain, donc pas de suivi individuel.
	 */
	private static function daily_salt() {
		$today = wp_date( 'Y-m-d' );
		$salt  = get_option( self::OPT_SALT, array() );
		if ( ! is_array( $salt ) || ! isset( $salt['day'] ) || $salt['day'] !== $today ) {
			$salt = array( 'day' => $today, 'value' => wp_generate_password( 32, false, false ) );
			update_option( self::OPT_SALT, $salt, false );
		}
		return $salt['value'];
	}

	/** Empreinte anonyme du visiteur. Aucune IP n'est conservee. */
	private static function visitor_hash() {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '';
		$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
		return substr( hash_hmac( 'sha256', $ip . '|' . $ua, self::daily_salt() ), 0, 20 );
	}

	/** D'ou vient le visiteur, en langage de patron. */
	private static function source() {
		$ref = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '';
		if ( '' === $ref ) return 'Direct';

		$host = wp_parse_url( $ref, PHP_URL_HOST );
		if ( ! $host ) return 'Direct';
		$host = strtolower( preg_replace( '/^www\./', '', $host ) );

		$self = strtolower( preg_replace( '/^www\./', '', (string) wp_parse_url( home_url(), PHP_URL_HOST ) ) );
		if ( $host === $self ) return 'Direct';

		$map = array(
			'google'    => 'Google',
			'bing'      => 'Bing',
			'yahoo'     => 'Yahoo',
			'duckduckgo'=> 'DuckDuckGo',
			'ecosia'    => 'Ecosia',
			'qwant'     => 'Qwant',
			'facebook'  => 'Facebook',
			'fb.'       => 'Facebook',
			'instagram' => 'Instagram',
			'tiktok'    => 'TikTok',
			'snapchat'  => 'Snapchat',
			'youtube'   => 'YouTube',
			'twitter'   => 'X (Twitter)',
			'x.com'     => 'X (Twitter)',
			'linkedin'  => 'LinkedIn',
			'pinterest' => 'Pinterest',
			'whatsapp'  => 'WhatsApp',
			't.co'      => 'X (Twitter)',
			'maps.'     => 'Google Maps',
		);
		foreach ( $map as $needle => $label ) {
			if ( false !== strpos( $host, $needle ) ) return $label;
		}
		return $host;
	}

	/** Enregistre la visite. */
	public static function track() {
		if ( ! self::should_count() ) return;

		$today = wp_date( 'Y-m-d' );
		$stats = get_option( self::OPT_TRAFFIC, array() );
		if ( ! is_array( $stats ) ) $stats = array();

		if ( ! isset( $stats[ $today ] ) ) {
			$stats[ $today ] = array( 'v' => 0, 'u' => 0, 'p' => array(), 's' => array() );
		}

		// Vue.
		$stats[ $today ]['v']++;

		// Visiteur unique : premiere page vue dans les 24 h ?
		$key = 'ag_pb_u_' . self::visitor_hash();
		if ( false === get_transient( $key ) ) {
			$stats[ $today ]['u']++;
			set_transient( $key, 1, DAY_IN_SECONDS );

			// La source ne compte qu'a l'arrivee, pas a chaque page.
			$src = self::source();
			$stats[ $today ]['s'][ $src ] = isset( $stats[ $today ]['s'][ $src ] ) ? $stats[ $today ]['s'][ $src ] + 1 : 1;
		}

		// Page vue, en chemin relatif lisible.
		$path = wp_parse_url( home_url( add_query_arg( array() ) ), PHP_URL_PATH );
		$path = $path ? $path : '/';
		if ( strlen( $path ) > 120 ) $path = substr( $path, 0, 120 );
		$stats[ $today ]['p'][ $path ] = isset( $stats[ $today ]['p'][ $path ] ) ? $stats[ $today ]['p'][ $path ] + 1 : 1;

		// On ne garde que les pages qui comptent, sinon l'option gonfle.
		if ( count( $stats[ $today ]['p'] ) > 60 ) {
			arsort( $stats[ $today ]['p'] );
			$stats[ $today ]['p'] = array_slice( $stats[ $today ]['p'], 0, 40, true );
		}

		$stats = self::prune( $stats );
		update_option( self::OPT_TRAFFIC, $stats, false );
	}

	/** Ne conserve que les KEEP_DAYS derniers jours. */
	private static function prune( $stats ) {
		if ( count( $stats ) <= self::KEEP_DAYS ) return $stats;
		krsort( $stats );
		return array_slice( $stats, 0, self::KEEP_DAYS, true );
	}

	/**
	 * Archive chaque nouveau ticket au moment ou il entre dans la file.
	 * La file du theme gratuit ne garde pas d'historique : sans ca, on ne
	 * pourrait rien dire des heures de pointe ni des prestations.
	 */
	public static function on_queue_change( $old, $new ) {
		if ( ! is_array( $new ) ) return;

		$known = array();
		if ( is_array( $old ) ) {
			foreach ( $old as $t ) {
				if ( isset( $t['id'] ) ) $known[ $t['id'] ] = true;
			}
		}

		$log = get_option( self::OPT_TICKETS, array() );
		if ( ! is_array( $log ) ) $log = array();
		$added = false;

		foreach ( $new as $t ) {
			if ( empty( $t['id'] ) || isset( $known[ $t['id'] ] ) ) continue;
			$log[] = array(
				'id'   => (string) $t['id'],
				'd'    => wp_date( 'Y-m-d' ),
				'h'    => (int) wp_date( 'G' ),
				'svc'  => isset( $t['service'] ) ? (string) $t['service'] : '',
			);
			$added = true;
		}

		if ( ! $added ) return;

		if ( count( $log ) > self::KEEP_TICKETS ) {
			$log = array_slice( $log, -self::KEEP_TICKETS );
		}
		update_option( self::OPT_TICKETS, $log, false );
	}

	/* ══════════════════════════════════════════════════════════════
	   LECTURE
	   ══════════════════════════════════════════════════════════════ */

	/** Agrege le trafic sur les N derniers jours. */
	public static function traffic( $days = 30 ) {
		$stats = get_option( self::OPT_TRAFFIC, array() );
		if ( ! is_array( $stats ) ) $stats = array();

		$out = array( 'views' => 0, 'uniques' => 0, 'pages' => array(), 'sources' => array(), 'daily' => array() );

		for ( $i = $days - 1; $i >= 0; $i-- ) {
			$day = wp_date( 'Y-m-d', strtotime( "-$i days", current_time( 'timestamp' ) ) );
			$d   = isset( $stats[ $day ] ) ? $stats[ $day ] : array( 'v' => 0, 'u' => 0, 'p' => array(), 's' => array() );

			$out['daily'][ $day ] = array( 'v' => (int) $d['v'], 'u' => (int) $d['u'] );
			$out['views']   += (int) $d['v'];
			$out['uniques'] += (int) $d['u'];

			foreach ( (array) $d['p'] as $path => $n ) {
				$out['pages'][ $path ] = isset( $out['pages'][ $path ] ) ? $out['pages'][ $path ] + $n : $n;
			}
			foreach ( (array) $d['s'] as $src => $n ) {
				$out['sources'][ $src ] = isset( $out['sources'][ $src ] ) ? $out['sources'][ $src ] + $n : $n;
			}
		}
		arsort( $out['pages'] );
		arsort( $out['sources'] );
		return $out;
	}

	/** Agrege les tickets sur les N derniers jours. */
	public static function tickets( $days = 30 ) {
		$log = get_option( self::OPT_TICKETS, array() );
		if ( ! is_array( $log ) ) $log = array();

		$from = wp_date( 'Y-m-d', strtotime( "-$days days", current_time( 'timestamp' ) ) );
		$out  = array( 'total' => 0, 'hours' => array_fill( 0, 24, 0 ), 'services' => array(), 'daily' => array() );

		foreach ( $log as $t ) {
			if ( empty( $t['d'] ) || $t['d'] < $from ) continue;
			$out['total']++;
			$h = isset( $t['h'] ) ? max( 0, min( 23, (int) $t['h'] ) ) : 0;
			$out['hours'][ $h ]++;
			$svc = ! empty( $t['svc'] ) ? $t['svc'] : 'Non precise';
			$out['services'][ $svc ] = isset( $out['services'][ $svc ] ) ? $out['services'][ $svc ] + 1 : 1;
			$out['daily'][ $t['d'] ] = isset( $out['daily'][ $t['d'] ] ) ? $out['daily'][ $t['d'] ] + 1 : 1;
		}
		arsort( $out['services'] );
		return $out;
	}

	/* ══════════════════════════════════════════════════════════════
	   ECRAN D'ADMINISTRATION
	   ══════════════════════════════════════════════════════════════ */

	public static function register_menu() {
		add_menu_page(
			__( 'Statistiques du salon', 'ag-premium-barber' ),
			__( 'Statistiques', 'ag-premium-barber' ),
			'edit_posts',
			'ag-pb-stats',
			array( __CLASS__, 'render' ),
			'dashicons-chart-bar',
			3
		);
	}

	public static function render() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( esc_html__( 'Acces refuse.', 'ag-premium-barber' ) );
		}

		$days = isset( $_GET['periode'] ) ? absint( $_GET['periode'] ) : 30;
		if ( ! in_array( $days, array( 7, 30, 90 ), true ) ) $days = 30;

		$t  = self::traffic( $days );
		$tk = self::tickets( $days );

		$max_day = 1;
		foreach ( $t['daily'] as $d ) { if ( $d['v'] > $max_day ) $max_day = $d['v']; }

		$max_hour = 1;
		foreach ( $tk['hours'] as $n ) { if ( $n > $max_hour ) $max_hour = $n; }

		$peak = array_keys( $tk['hours'], max( $tk['hours'] ), true );
		$peak_hour = ( $tk['total'] > 0 && isset( $peak[0] ) ) ? $peak[0] : null;
		?>
		<div class="wrap ag-pb-stats">
			<h1><?php esc_html_e( 'Statistiques du salon', 'ag-premium-barber' ); ?></h1>
			<p class="description" style="max-width:70ch;">
				<?php esc_html_e( 'Vos chiffres, calcules sur votre hebergement. Aucun service exterieur, aucun cookie, aucune adresse IP conservee.', 'ag-premium-barber' ); ?>
			</p>

			<p class="ag-pb-periods">
				<?php foreach ( array( 7 => '7 jours', 30 => '30 jours', 90 => '90 jours' ) as $d => $label ) : ?>
					<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ag-pb-stats', 'periode' => $d ), admin_url( 'admin.php' ) ) ); ?>"
					   class="button <?php echo $days === $d ? 'button-primary' : ''; ?>"><?php echo esc_html( $label ); ?></a>
				<?php endforeach; ?>
			</p>

			<div class="ag-pb-cards">
				<div class="ag-pb-card">
					<span class="ag-pb-card__n"><?php echo esc_html( number_format_i18n( $t['uniques'] ) ); ?></span>
					<span class="ag-pb-card__l"><?php esc_html_e( 'visiteurs', 'ag-premium-barber' ); ?></span>
				</div>
				<div class="ag-pb-card">
					<span class="ag-pb-card__n"><?php echo esc_html( number_format_i18n( $t['views'] ) ); ?></span>
					<span class="ag-pb-card__l"><?php esc_html_e( 'pages vues', 'ag-premium-barber' ); ?></span>
				</div>
				<div class="ag-pb-card">
					<span class="ag-pb-card__n"><?php echo esc_html( number_format_i18n( $tk['total'] ) ); ?></span>
					<span class="ag-pb-card__l"><?php esc_html_e( 'tickets pris', 'ag-premium-barber' ); ?></span>
				</div>
				<div class="ag-pb-card">
					<span class="ag-pb-card__n"><?php
						echo $peak_hour !== null ? esc_html( sprintf( '%dh', $peak_hour ) ) : '—';
					?></span>
					<span class="ag-pb-card__l"><?php esc_html_e( 'heure de pointe', 'ag-premium-barber' ); ?></span>
				</div>
			</div>

			<h2><?php esc_html_e( 'Frequentation du site', 'ag-premium-barber' ); ?></h2>
			<?php if ( $t['views'] < 1 ) : ?>
				<p><?php esc_html_e( 'Aucune visite enregistree pour l\'instant. Les chiffres apparaitront des les premieres visites.', 'ag-premium-barber' ); ?></p>
			<?php else : ?>
			<div class="ag-pb-chart" role="img" aria-label="<?php esc_attr_e( 'Pages vues par jour', 'ag-premium-barber' ); ?>">
				<?php foreach ( $t['daily'] as $day => $d ) :
					$h = max( 2, (int) round( ( $d['v'] / $max_day ) * 100 ) ); ?>
					<span class="ag-pb-bar" style="height:<?php echo esc_attr( $h ); ?>%"
						  title="<?php echo esc_attr( sprintf( '%s — %d vues, %d visiteurs', wp_date( 'j M', strtotime( $day ) ), $d['v'], $d['u'] ) ); ?>"></span>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>

			<div class="ag-pb-cols">
				<div>
					<h2><?php esc_html_e( 'D\'ou viennent vos visiteurs', 'ag-premium-barber' ); ?></h2>
					<?php self::table( $t['sources'], __( 'Source', 'ag-premium-barber' ), __( 'Visiteurs', 'ag-premium-barber' ), __( 'Pas encore de donnees.', 'ag-premium-barber' ) ); ?>
				</div>
				<div>
					<h2><?php esc_html_e( 'Pages les plus consultees', 'ag-premium-barber' ); ?></h2>
					<?php self::table( $t['pages'], __( 'Page', 'ag-premium-barber' ), __( 'Vues', 'ag-premium-barber' ), __( 'Pas encore de donnees.', 'ag-premium-barber' ) ); ?>
				</div>
			</div>

			<h2><?php esc_html_e( 'Vos prestations les plus demandees', 'ag-premium-barber' ); ?></h2>
			<?php if ( $tk['total'] < 1 ) : ?>
				<p><?php esc_html_e( 'Aucun ticket pris sur la periode. Des que vos clients prendront leur tour, vous verrez ici ce qui marche le mieux.', 'ag-premium-barber' ); ?></p>
			<?php else : ?>
				<?php self::table( $tk['services'], __( 'Prestation', 'ag-premium-barber' ), __( 'Tickets', 'ag-premium-barber' ), '' ); ?>

				<h2><?php esc_html_e( 'Vos heures de pointe', 'ag-premium-barber' ); ?></h2>
				<div class="ag-pb-chart ag-pb-chart--hours" role="img" aria-label="<?php esc_attr_e( 'Tickets pris par heure', 'ag-premium-barber' ); ?>">
					<?php for ( $h = 6; $h <= 22; $h++ ) :
						$n  = (int) $tk['hours'][ $h ];
						$ph = max( 2, (int) round( ( $n / $max_hour ) * 100 ) ); ?>
						<span class="ag-pb-bar" style="height:<?php echo esc_attr( $ph ); ?>%"
							  title="<?php echo esc_attr( sprintf( '%dh — %d ticket(s)', $h, $n ) ); ?>"
							  data-h="<?php echo esc_attr( $h ); ?>"></span>
					<?php endfor; ?>
				</div>
				<p class="description"><?php esc_html_e( 'De 6h a 22h. Survolez une barre pour le detail.', 'ag-premium-barber' ); ?></p>
			<?php endif; ?>
		</div>

		<style>
		.ag-pb-stats .ag-pb-periods{margin:16px 0 24px}
		.ag-pb-cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;margin-bottom:28px}
		.ag-pb-card{background:#fff;border:1px solid #dcdcde;border-left:4px solid #d4b45c;border-radius:6px;padding:18px 20px}
		.ag-pb-card__n{display:block;font-size:2rem;font-weight:700;line-height:1.1;font-variant-numeric:tabular-nums}
		.ag-pb-card__l{display:block;color:#646970;font-size:.85rem;margin-top:4px}
		.ag-pb-chart{display:flex;align-items:flex-end;gap:2px;height:150px;background:#fff;border:1px solid #dcdcde;border-radius:6px;padding:12px}
		.ag-pb-chart--hours{gap:6px}
		.ag-pb-bar{flex:1;background:#d4b45c;border-radius:3px 3px 0 0;min-width:3px;transition:background .2s}
		.ag-pb-bar:hover{background:#a8873a}
		.ag-pb-cols{display:grid;grid-template-columns:1fr 1fr;gap:28px;margin-top:28px}
		.ag-pb-stats table{margin-top:8px}
		.ag-pb-stats td.num{text-align:right;font-variant-numeric:tabular-nums;width:90px}
		@media(max-width:782px){.ag-pb-cols{grid-template-columns:1fr}}
		</style>
		<?php
	}

	/** Petit tableau clef -> nombre. */
	private static function table( $rows, $col1, $col2, $empty ) {
		if ( empty( $rows ) ) {
			if ( $empty ) echo '<p>' . esc_html( $empty ) . '</p>';
			return;
		}
		echo '<table class="widefat striped"><thead><tr><th>' . esc_html( $col1 ) . '</th><th class="num">' . esc_html( $col2 ) . '</th></tr></thead><tbody>';
		$i = 0;
		foreach ( $rows as $label => $n ) {
			if ( ++$i > 10 ) break;
			echo '<tr><td>' . esc_html( $label ) . '</td><td class="num">' . esc_html( number_format_i18n( $n ) ) . '</td></tr>';
		}
		echo '</tbody></table>';
	}
}
