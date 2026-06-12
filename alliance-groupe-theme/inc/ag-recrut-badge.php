<?php
/**
 * AG Badge « On recrute » — petit lien flottant DISCRET sur le site public,
 * qui mène à la page de candidature ambassadeur (funnel géré).
 *
 * - Coin bas-gauche, petit, semi-transparent (plus visible au survol).
 * - Fermable (masqué 30 jours après fermeture).
 * - Pas affiché dans l'admin, ni dans l'espace ambassadeur, ni sur la page
 *   de candidature elle-même.
 * - Shortcode [ag_recrute] pour le poser aussi en ligne (footer, article…).
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ag_recrute_url' ) ) {
	/** Lien de candidature ambassadeur (page gérée si dispo, sinon /ambassadeurs). */
	function ag_recrute_url() {
		foreach ( array( 'candidature-ambassadeur', 'devenir-ambassadeur' ) as $slug ) {
			$p = get_page_by_path( $slug );
			if ( $p && 'publish' === $p->post_status ) return get_permalink( $p );
		}
		if ( function_exists( 'ag_cand_program_url' ) ) return ag_cand_program_url();
		return home_url( '/ambassadeurs' );
	}
}

/* Shortcode : bouton « On recrute » en ligne (discret). */
add_shortcode( 'ag_recrute', function ( $atts ) {
	$a = shortcode_atts( array( 'texte' => 'On recrute' ), $atts );
	return '<a class="ag-recrute-inline" href="' . esc_url( ag_recrute_url() ) . '">👋 ' . esc_html( $a['texte'] ) . '</a>';
} );

/* Badge flottant discret (front public uniquement). */
add_action( 'wp_footer', function () {
	if ( is_admin() ) return;
	// Pas dans l'espace ambassadeur ni sur la page de candidature.
	if ( function_exists( 'ag_pwa_is_amb_context' ) && ag_pwa_is_amb_context() ) return;
	if ( function_exists( 'is_page' ) && is_page( array( 'candidature-ambassadeur', 'devenir-ambassadeur', 'ambassadeurs' ) ) ) return;
	$url   = esc_url( ag_recrute_url() );
	$texte = apply_filters( 'ag_recrute_texte', 'On recrute' );
	?>
	<style>
	.ag-recrute{position:fixed;left:12px;bottom:calc(12px + env(safe-area-inset-bottom));z-index:99990;display:flex;align-items:center;gap:7px;
		padding:8px 13px;border-radius:999px;background:rgba(16,16,22,.72);border:1px solid rgba(212,180,92,.35);
		color:#e9e2cc;font-family:-apple-system,Arial,sans-serif;font-size:12.5px;font-weight:600;text-decoration:none;
		box-shadow:0 6px 18px rgba(0,0,0,.3);opacity:.72;transition:opacity .2s,transform .2s;backdrop-filter:blur(4px)}
	.ag-recrute:hover{opacity:1;transform:translateY(-1px);color:#fff}
	.ag-recrute__dot{width:8px;height:8px;border-radius:50%;background:#2ecc71;box-shadow:0 0 0 0 rgba(46,204,113,.6);animation:agrPulse 2.4s infinite}
	.ag-recrute__x{margin-left:2px;color:#7a7a86;font-size:14px;line-height:1;text-decoration:none}
	.ag-recrute__x:hover{color:#fff}
	@keyframes agrPulse{0%{box-shadow:0 0 0 0 rgba(46,204,113,.5)}70%{box-shadow:0 0 0 7px rgba(46,204,113,0)}100%{box-shadow:0 0 0 0 rgba(46,204,113,0)}}
	@media(max-width:480px){.ag-recrute{font-size:12px;padding:7px 11px}}
	.ag-recrute-inline{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:999px;background:rgba(212,180,92,.12);border:1px solid rgba(212,180,92,.5);color:#D4B45C;font-weight:700;text-decoration:none;font-size:.9rem}
	.ag-recrute-inline:hover{background:rgba(212,180,92,.2)}
	</style>
	<div id="ag-recrute-wrap"></div>
	<script>
	(function(){
		try{
			var off = parseInt(localStorage.getItem('ag_recrute_off')||'0',10);
			if (off && (Date.now()-off) < 30*24*3600*1000) return; // masqué 30j après fermeture
		}catch(e){}
		var w = document.getElementById('ag-recrute-wrap');
		if(!w) return;
		w.innerHTML = '<a class="ag-recrute" href="<?php echo $url; // phpcs:ignore ?>"><span class="ag-recrute__dot"></span><?php echo esc_js( $texte ); ?><span class="ag-recrute__x" role="button" aria-label="Fermer">×</span></a>';
		var x = w.querySelector('.ag-recrute__x');
		if(x){ x.addEventListener('click', function(e){ e.preventDefault(); e.stopPropagation(); try{localStorage.setItem('ag_recrute_off', String(Date.now()));}catch(_){} var b=w.querySelector('.ag-recrute'); if(b) b.style.display='none'; }); }
	})();
	</script>
	<?php
}, 30 );
