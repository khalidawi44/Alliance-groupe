<?php
/**
 * ag-journal-ia.php — Journal public « Fait par l'IA ».
 *
 * Idée novatrice n°3 : une page publique qui montre, jour après jour, tout ce
 * que l'IA (Claude Code) a construit et amélioré sur ce site. Transparence
 * radicale + preuve vivante qu'on maîtrise la techno la plus récente. Aucune
 * autre agence n'expose ça. NE consomme PAS la clé API : lit un JSON généré
 * depuis git (`scripts/gen-journal.sh`).
 *
 * Page : /fait-par-lia (auto-créée). Shortcode : [ag_journal_ia].
 *
 * @package Alliance_Groupe
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Charge les entrées du journal depuis le JSON committé. */
function ag_journal_ia_data() {
	$path = get_stylesheet_directory() . '/assets/data/journal-ia.json';
	if ( ! file_exists( $path ) ) {
		return array( 'genere_le' => '', 'total' => 0, 'entrees' => array() );
	}
	$data = json_decode( (string) file_get_contents( $path ), true );
	if ( ! is_array( $data ) || empty( $data['entrees'] ) ) {
		return array( 'genere_le' => '', 'total' => 0, 'entrees' => array() );
	}
	return $data;
}

/**
 * Devine une catégorie + emoji à partir du titre de commit, pour un rendu
 * lisible par un visiteur non technique.
 */
function ag_journal_ia_tag( $titre ) {
	$t = function_exists( 'mb_strtolower' ) ? mb_strtolower( $titre, 'UTF-8' ) : strtolower( $titre );
	$map = array(
		'sécurit'   => array( '🛡️', 'Sécurité' ),
		'securit'   => array( '🛡️', 'Sécurité' ),
		'audit'     => array( '🛡️', 'Sécurité' ),
		'kali'      => array( '🛡️', 'Sécurité' ),
		'seo'       => array( '🔎', 'Référencement' ),
		'référenc'  => array( '🔎', 'Référencement' ),
		'composant' => array( '🎨', 'Design' ),
		'design'    => array( '🎨', 'Design' ),
		'studio'    => array( '🎬', 'Création' ),
		'vidéo'     => array( '🎬', 'Création' ),
		'robot'     => array( '🤖', 'Automatisation' ),
		'ia '       => array( '🤖', 'Automatisation' ),
		'agenda'    => array( '📅', 'Agenda' ),
		'sms'       => array( '📲', 'Contact' ),
		'whatsapp'  => array( '📲', 'Contact' ),
		'paiement'  => array( '💳', 'Paiement' ),
		'paypal'    => array( '💳', 'Paiement' ),
		'ambassad'  => array( '🚀', 'Ambassadeurs' ),
		'mission'   => array( '🚀', 'Ambassadeurs' ),
		'zone'      => array( '📍', 'Territoire' ),
		'marché'    => array( '📢', 'Appels d’offres' ),
		'appels d'  => array( '📢', 'Appels d’offres' ),
		'app '      => array( '📱', 'Application' ),
		'rapport'   => array( '📄', 'Rapports' ),
	);
	foreach ( $map as $needle => $pair ) {
		if ( false !== strpos( $t, $needle ) ) {
			return $pair;
		}
	}
	return array( '✨', 'Amélioration' );
}

/** Rendu de la timeline (utilisé par le template + le shortcode). */
function ag_journal_ia_render() {
	$data    = ag_journal_ia_data();
	$entrees = $data['entrees'];
	$total   = (int) $data['total'];

	ob_start();
	?>
	<style>
		.agj-wrap{max-width:840px;margin:0 auto;padding:24px 18px 64px}
		.agj-head{text-align:center;margin:8px 0 32px}
		.agj-badge{display:inline-flex;align-items:center;gap:8px;background:linear-gradient(135deg,#c8962c,#e6b84a);color:#1a1205;font-weight:800;padding:7px 16px;border-radius:999px;font-size:.82rem;letter-spacing:.03em;text-transform:uppercase}
		.agj-head h1{font-size:clamp(1.8rem,5vw,2.6rem);margin:16px 0 8px;line-height:1.12}
		.agj-head p{color:#5a5348;max-width:620px;margin:0 auto;font-size:1.02rem;line-height:1.55}
		.agj-count{margin:22px auto 0;display:inline-flex;gap:22px;flex-wrap:wrap;justify-content:center}
		.agj-count b{display:block;font-size:1.7rem;color:#c8962c;line-height:1}
		.agj-count span{font-size:.78rem;color:#7a7266;text-transform:uppercase;letter-spacing:.04em}
		.agj-tl{position:relative;margin-top:36px;padding-left:26px;border-left:2px solid #eadfc6}
		.agj-day{margin:0 0 6px;font-weight:800;color:#1a1205;font-size:.95rem;position:relative}
		.agj-day::before{content:"";position:absolute;left:-33px;top:4px;width:12px;height:12px;border-radius:50%;background:#c8962c;box-shadow:0 0 0 4px #fdf6e6}
		.agj-item{background:#fff;border:1px solid #efe7d6;border-radius:12px;padding:12px 14px;margin:0 0 12px;box-shadow:0 1px 3px rgba(0,0,0,.04)}
		.agj-item .t{font-weight:600;color:#2a2317;line-height:1.4}
		.agj-item .m{margin-top:6px;display:flex;gap:8px;align-items:center;flex-wrap:wrap}
		.agj-cat{font-size:.72rem;background:#fdf6e6;color:#8a6a1e;border:1px solid #eadfc6;border-radius:999px;padding:2px 9px;font-weight:700}
		.agj-hash{font-family:ui-monospace,monospace;font-size:.7rem;color:#a89f8e}
		.agj-cta{margin-top:44px;text-align:center;background:#1a1205;color:#fdf6e6;border-radius:16px;padding:28px 20px}
		.agj-cta h2{margin:0 0 8px;font-size:1.4rem}
		.agj-cta p{margin:0 auto 16px;max-width:520px;color:#e9dcc0;line-height:1.5}
		.agj-cta a{display:inline-block;background:linear-gradient(135deg,#c8962c,#e6b84a);color:#1a1205;font-weight:800;text-decoration:none;padding:12px 26px;border-radius:999px}
		@media (prefers-color-scheme:dark){.agj-item{background:#211a0e;border-color:#3a2f1a}.agj-item .t{color:#f0e8d6}.agj-head p{color:#c9c0b0}}
	</style>
	<div class="agj-wrap">
		<div class="agj-head">
			<span class="agj-badge">⚡ Fait par l'IA</span>
			<h1>Le journal public de notre IA</h1>
			<p>Ce site est construit et amélioré <strong>chaque jour par une intelligence artificielle</strong> (Claude Code) pilotée par notre équipe. Voici, en toute transparence, le journal de tout ce que l'IA a réalisé. Aucune autre agence ne vous montre ça.</p>
			<div class="agj-count">
				<div><b><?php echo esc_html( number_format_i18n( $total ) ); ?></b><span>réalisations</span></div>
				<div><b><?php echo esc_html( count( array_unique( wp_list_pluck( $entrees, 'date' ) ) ) ); ?></b><span>journées de travail IA</span></div>
			</div>
		</div>

		<?php if ( empty( $entrees ) ) : ?>
			<p style="text-align:center;color:#7a7266">Le journal se remplit à chaque mise à jour du site.</p>
		<?php else : ?>
			<div class="agj-tl">
				<?php
				$last_day = '';
				foreach ( $entrees as $e ) {
					$titre = isset( $e['titre'] ) ? $e['titre'] : '';
					$date  = isset( $e['date'] ) ? $e['date'] : '';
					$hash  = isset( $e['hash'] ) ? $e['hash'] : '';
					if ( '' === $titre ) { continue; }
					list( $emoji, $cat ) = ag_journal_ia_tag( $titre );
					if ( $date !== $last_day ) {
						$last_day = $date;
						$jour     = $date ? date_i18n( 'j F Y', strtotime( $date ) ) : '';
						echo '<div class="agj-day">' . esc_html( $jour ) . '</div>';
					}
					echo '<div class="agj-item"><div class="t">' . esc_html( $emoji . ' ' . $titre ) . '</div>';
					echo '<div class="m"><span class="agj-cat">' . esc_html( $cat ) . '</span>';
					if ( $hash ) { echo '<span class="agj-hash">#' . esc_html( $hash ) . '</span>'; }
					echo '</div></div>';
				}
				?>
			</div>
		<?php endif; ?>

		<div class="agj-cta">
			<h2>Vous aussi, un site piloté par l'IA</h2>
			<p>On met cette même technologie au service de votre entreprise : un site sécurisé, référencé et amélioré en continu.</p>
			<a href="<?php echo esc_url( home_url( '/creer-mon-site' ) ); ?>">Je veux mon site →</a>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

add_shortcode( 'ag_journal_ia', 'ag_journal_ia_render' );

/** Titre SEO de la page journal. */
add_filter( 'document_title_parts', function ( $parts ) {
	if ( is_page( 'fait-par-lia' ) ) {
		$parts['title'] = "Fait par l'IA — le journal public de notre intelligence artificielle";
	}
	return $parts;
}, 20 );

/** Auto-création de la page /fait-par-lia (idempotent). */
add_action( 'init', function () {
	if ( get_option( 'ag_journal_ia_page_v1' ) ) {
		return;
	}
	if ( ! get_page_by_path( 'fait-par-lia' ) ) {
		$id = wp_insert_post( array(
			'post_title'   => "Fait par l'IA",
			'post_name'    => 'fait-par-lia',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '[ag_journal_ia]',
		) );
		if ( $id && ! is_wp_error( $id ) ) {
			$tpl = 'templates/page-journal-ia.php';
			if ( file_exists( get_stylesheet_directory() . '/' . $tpl ) ) {
				update_post_meta( $id, '_wp_page_template', $tpl );
			}
		}
	}
	update_option( 'ag_journal_ia_page_v1', 1 );
} );
