<?php
/**
 * Accueil : presence des avis Google, sans jamais en inventer.
 *
 * Contexte (voir CARTOGRAPHIE.md, journal 2026-11-20) :
 *  - La section d'avis de l'accueil (templates/page-accueil-cinema.php) n'affiche
 *    QUE de vrais avis lus via l'API Google Places (inc/ag-geo.php). Decision de
 *    Fabrice du 03/09/2026 : aucun repli ecrit a la main. Si l'API ne renvoie
 *    rien, la section disparait. C'est la raison pour laquelle elle n'est plus la.
 *  - Le nettoyage des pictogrammes du 03/09 avait aussi vide les etoiles
 *    (str_repeat( '', n )) : une section avec de vrais avis aurait montre une
 *    note sans etoiles.
 *
 * Ce module, charge depuis inc/ag-redirections.php, fait deux choses seulement :
 *  1. Repare les etoiles vides (.av__stars) a partir de la VRAIE note.
 *  2. Quand il n'y a aucun avis lisible, pose un bloc sobre « Nos avis sont sur
 *     Google » avec deux liens (lire / laisser un avis). Aucune note, aucun nom,
 *     aucun texte invente. Si l'API donne une vraie note, elle est montree.
 *
 * Le gabarit de l'accueil n'est pas modifie : le bloc est rendu dans le pied de
 * page puis deplace par JavaScript apres la grille des realisations.
 *
 * Pour retrouver de VRAIS avis : wp-admin > Avis Google (cle Places + Place ID),
 * ou JSON manuel de vrais avis, puis « Vider le cache des avis ».
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_footer', function () {
	if ( is_admin() || ! function_exists( 'ag_is_accueil_cinema' ) || ! ag_is_accueil_cinema() ) {
		return;
	}

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

	$read  = function_exists( 'ag_geo_review_url' ) ? (string) ag_geo_review_url() : '';
	$write = function_exists( 'ag_avis_link' ) ? (string) ag_avis_link() : '';
	if ( '' === $read ) {
		$read = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( 'Alliance Groupe Nantes' );
	}

	$full  = "\u{2605}";
	$empty = "\u{2606}";
	$n     = max( 0, min( 5, (int) round( $rating ) ) );
	$cfg   = array(
		'rating' => $rating,
		'full'   => $full,
		'empty'  => $empty,
		'hasText' => $has_text,
	);
	?>
	<style>
	.av--fb{max-width:760px;text-align:center}
	.av--fb .av__titre{font-family:var(--serif,Georgia,serif);font-size:clamp(1.25rem,2.4vw,1.6rem);margin:6px 0 6px}
	.av--fb .av__sub{color:#cdd4e2;font-size:.95rem;margin:0 0 18px}
	.av--fb .av__btns{display:flex;flex-wrap:wrap;gap:12px;justify-content:center}
	.av--fb .av__btns a{display:inline-block;padding:12px 22px;border-radius:8px;border:1px solid rgba(212,180,92,.55);color:#f1e6c2;text-decoration:none;font-weight:700;font-size:.92rem;transition:border-color .25s,background .25s}
	.av--fb .av__btns a:hover{border-color:#e8c870;background:rgba(212,180,92,.12)}
	.av--fb .av__btns a.av__btn--plein{background:linear-gradient(120deg,#c9a24a,#e8c870);color:#1a1206;border-color:transparent}
	</style>
	<?php if ( ! $has_text ) : ?>
	<template id="ag-av-fallback">
		<div class="av av--fb">
			<div class="av__head">
				<span class="av__g" aria-hidden="true"><svg viewBox="0 0 24 24" width="22" height="22"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.27-4.74 3.27-8.1z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.99.66-2.26 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84A11 11 0 0 0 12 23z"/><path fill="#FBBC05" d="M5.84 14.1a6.6 6.6 0 0 1 0-4.2V7.06H2.18a11 11 0 0 0 0 9.88l3.66-2.84z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84C6.71 7.31 9.14 5.38 12 5.38z"/></svg></span>
				<span class="av__rate">
					<?php if ( $rating > 0 ) : ?>
						<strong><?php echo esc_html( number_format_i18n( $rating, 1 ) ); ?></strong>
						<span class="av__stars" aria-hidden="true"><?php echo esc_html( str_repeat( $full, $n ) . str_repeat( $empty, 5 - $n ) ); ?></span>
					<?php endif; ?>
					<?php
					echo $total
						? esc_html( sprintf( _n( '%s avis Google', '%s avis Google', $total, 'alliance-groupe' ), number_format_i18n( $total ) ) )
						: 'Avis Google';
					?>
				</span>
			</div>
			<h3 class="av__titre">Nos avis sont sur Google</h3>
			<p class="av__sub">Lisez ce que nos clients disent de nous, ou laissez le vôtre en moins d'une minute.</p>
			<div class="av__btns">
				<a class="av__btn--plein" href="<?php echo esc_url( $read ); ?>" target="_blank" rel="noopener">Lire les avis sur Google</a>
				<?php if ( '' !== $write ) : ?>
					<a href="<?php echo esc_url( $write ); ?>" target="_blank" rel="noopener">Laisser un avis</a>
				<?php endif; ?>
			</div>
		</div>
	</template>
	<?php endif; ?>
	<script>
	(function () {
		var cfg = <?php echo wp_json_encode( $cfg ); ?>;
		function go() {
			/* 1. Etoiles vides : on les redessine depuis la vraie note. */
			var n = Math.max(0, Math.min(5, Math.round(cfg.rating || 0)));
			document.querySelectorAll('.av__stars').forEach(function (el) {
				if (el.textContent.trim() === '' && n > 0) {
					el.textContent = new Array(n + 1).join(cfg.full) + new Array(6 - n).join(cfg.empty);
				}
			});
			/* 2. Aucun avis lisible : bloc sobre apres la grille des realisations. */
			var tpl = document.getElementById('ag-av-fallback');
			if (!tpl || document.querySelector('.av:not(.av--fb)')) return;
			var grid = document.querySelector('.rz__grid');
			if (!grid || !grid.parentNode || document.querySelector('.av--fb')) return;
			grid.parentNode.insertBefore(tpl.content.cloneNode(true), grid.nextSibling);
		}
		if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', go); } else { go(); }
	})();
	</script>
	<?php
}, 98 );

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
