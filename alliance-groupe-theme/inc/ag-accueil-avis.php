<?php
/**
 * Accueil : presence des avis Google, sans jamais en inventer.
 *
 * Contexte (voir CARTOGRAPHIE.md, journal 2026-11-20) :
 *  - L'accueil en ligne utilise templates/page-accueil.php (verifie : classe de
 *    page "page-template-page-accueil"). Ce gabarit ne contient AUCUNE section
 *    d'avis. L'accueil cinema (page-accueil-cinema.php) en avait une, lisant
 *    uniquement de vrais avis via l'API Google Places (inc/ag-geo.php), qui se
 *    cachait quand l'API ne renvoyait rien (decision de Fabrice, 03/09/2026 :
 *    aucun temoignage ecrit a la main).
 *
 * Ce module pose la section d'avis sur l'accueil SANS modifier le gabarit : il
 * s'accroche au moment ou le gabarit charge template-parts/atelier-gallery
 * (action get_template_part_template-parts/atelier-gallery), donc juste apres
 * les offres. Deux cas :
 *  1. De vrais avis Google avec texte existent : on charge
 *     template-parts/temoignages.php (cartes, note, nombre d'avis).
 *  2. Aucun avis lisible : bloc sobre « Nos avis sont sur Google » avec deux
 *     liens (lire / laisser un avis). Ni note, ni nom, ni texte invente. Si
 *     l'API donne une vraie note, elle est montree.
 *
 * Pour retrouver de VRAIS avis : wp-admin > Avis Google (cle Places + Place ID),
 * ou JSON manuel de vrais avis, puis « Vider le cache des avis ».
 *
 * Ce fichier charge aussi le carrousel des offres (ag-accueil-carrousel.php),
 * en bas, pour ne pas toucher au chargeur ag-redirections.php.
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ag_accueil_avis_section' ) ) {
	/**
	 * Affiche la section d'avis de l'accueil (une seule fois par page).
	 */
	function ag_accueil_avis_section() {
		static $done = false;
		if ( $done || is_admin() || ! is_front_page() ) {
			return;
		}
		$done = true;

		$g      = function_exists( 'ag_geo_google_data' ) ? (array) ag_geo_google_data() : array();
		$rating = (float) ( $g['rating'] ?? 0 );
		$total  = (int) ( $g['total'] ?? 0 );

		$has_text = false;
		foreach ( (array) ( $g['reviews'] ?? array() ) as $rv ) {
			if ( '' !== trim( (string) ( $rv['text'] ?? '' ) ) ) {
				$has_text = true;
				break;
			}
		}

		if ( $has_text ) {
			get_template_part( 'template-parts/temoignages' );
			return;
		}

		$read  = function_exists( 'ag_geo_review_url' ) ? (string) ag_geo_review_url() : '';
		$write = function_exists( 'ag_avis_link' ) ? (string) ag_avis_link() : '';
		if ( '' === $read ) {
			$read = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( 'Alliance Groupe Nantes' );
		}
		$n = max( 0, min( 5, (int) round( $rating ) ) );
		?>
<section class="ag-avis-fb" aria-label="Avis Google">
	<div class="ag-avis-fb__in">
		<span class="ag-avis-fb__g" aria-hidden="true"><svg viewBox="0 0 24 24" width="24" height="24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.27-4.74 3.27-8.1z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.99.66-2.26 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84A11 11 0 0 0 12 23z"/><path fill="#FBBC05" d="M5.84 14.1a6.6 6.6 0 0 1 0-4.2V7.06H2.18a11 11 0 0 0 0 9.88l3.66-2.84z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84C6.71 7.31 9.14 5.38 12 5.38z"/></svg></span>
		<?php if ( $rating > 0 ) : ?>
			<p class="ag-avis-fb__note">
				<strong><?php echo esc_html( number_format_i18n( $rating, 1 ) ); ?></strong>
				<span class="ag-avis-fb__stars" aria-hidden="true"><?php echo esc_html( str_repeat( "\u{2605}", $n ) . str_repeat( "\u{2606}", 5 - $n ) ); ?></span>
				<?php
				if ( $total ) {
					echo esc_html( sprintf( _n( '%s avis Google', '%s avis Google', $total, 'alliance-groupe' ), number_format_i18n( $total ) ) );
				}
				?>
			</p>
		<?php endif; ?>
		<h2 class="ag-avis-fb__titre">Nos avis sont sur Google</h2>
		<p class="ag-avis-fb__sub">Lisez ce que nos clients disent de nous, ou laissez le vôtre en moins d'une minute.</p>
		<div class="ag-avis-fb__btns">
			<a class="ag-avis-fb__plein" href="<?php echo esc_url( $read ); ?>" target="_blank" rel="noopener">Lire les avis sur Google</a>
			<?php if ( '' !== $write ) : ?>
				<a href="<?php echo esc_url( $write ); ?>" target="_blank" rel="noopener">Laisser un avis</a>
			<?php endif; ?>
		</div>
	</div>
</section>
<style>
.ag-avis-fb{padding:clamp(48px,8vw,84px) 22px;background:#05050a;color:#eef1f6;text-align:center;border-top:1px solid rgba(212,180,92,.18)}
.ag-avis-fb__in{max-width:720px;margin:0 auto}
.ag-avis-fb__g{display:inline-flex;background:#fff;border-radius:50%;padding:9px;line-height:0;margin-bottom:14px}
.ag-avis-fb__note{margin:0 0 6px;color:#cdd4e2;font-size:.98rem}
.ag-avis-fb__note strong{font-size:1.6rem;color:#f4d06f;margin-right:8px}
.ag-avis-fb__stars{color:#d4b45c;letter-spacing:.1em;margin-right:8px}
.ag-avis-fb__titre{font-size:clamp(1.5rem,3.6vw,2.2rem);font-weight:800;line-height:1.12;margin:6px 0 10px}
.ag-avis-fb__sub{color:#aeb6c6;font-size:1.02rem;line-height:1.55;margin:0 0 24px}
.ag-avis-fb__btns{display:flex;flex-wrap:wrap;gap:12px;justify-content:center}
.ag-avis-fb__btns a{display:inline-block;padding:13px 24px;border-radius:100px;border:1px solid rgba(212,180,92,.55);color:#f1e6c2;text-decoration:none;font-weight:700;font-size:.95rem;transition:border-color .25s,background .25s}
.ag-avis-fb__btns a:hover{border-color:#f4d06f;background:rgba(212,180,92,.12)}
.ag-avis-fb__btns a.ag-avis-fb__plein{background:linear-gradient(120deg,#d4b45c,#f4d06f);color:#1a1206;border-color:transparent}
</style>
		<?php
	}
}

/* Accroche : juste avant l'Atelier IA, donc apres les offres, sur l'accueil seulement. */
add_action( 'get_template_part_template-parts/atelier-gallery', 'ag_accueil_avis_section', 5 );

/**
 * Anna Photo : le site est https://annaphoto.fr (plus .eu).
 * Trois gabarits (accueil cinema, accueil epure, audit securite) portent encore
 * l'ancienne adresse dans leur source. Reecriture cote serveur sur la page
 * rendue, donc visible aussi des robots. A retirer quand les sources seront
 * corrigees (voir CARTOGRAPHIE.md, points ouverts).
 */
add_action( 'template_redirect', function () {
	if ( is_admin() || is_feed() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}
	ob_start( function ( $html ) {
		return ( is_string( $html ) && false !== stripos( $html, 'annaphoto.eu' ) )
			? str_ireplace( 'annaphoto.eu', 'annaphoto.fr', $html )
			: $html;
	} );
}, 1 );

/* Carrousel des offres (module separe, voir ag-accueil-carrousel.php). */
$ag_car_mod = get_stylesheet_directory() . '/inc/ag-accueil-carrousel.php';
if ( file_exists( $ag_car_mod ) ) {
	require_once $ag_car_mod;
}
