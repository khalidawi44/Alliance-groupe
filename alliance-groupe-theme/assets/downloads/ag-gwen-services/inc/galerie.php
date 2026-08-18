<?php
/**
 * Galerie photo « En images » — s'affiche automatiquement sur la page d'accueil
 * dès qu'au moins une photo est déposée dans le thème.
 *
 * CONVENTION (aucun réglage à faire) :
 *   - Déposer les photos dans  assets/galerie/  (jpg, jpeg, png, webp).
 *   - DISPATCH PAR CATÉGORIE automatique, d'après le début du nom de fichier :
 *       • « aide-domicile-… »  ou « senior-… »  -> section « Auprès des personnes âgées »
 *       • « garde-enfants-… »  ou « enfant-… »  -> section « Garde d'enfants »
 *       • tout le reste                          -> section « En images »
 *   - Dans chaque section, tri par nom (01-…, 02-…).
 *   - Si le dossier est vide, RIEN ne s'affiche.
 *
 * @package AG_Starter_Domicile
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Détermine la catégorie d'une photo d'après son nom de fichier.
 *
 * @return string 'seniors' | 'enfants' | 'autres'
 */
function ag_domicile_galerie_cat( $name ) {
	$n = strtolower( $name );
	if ( preg_match( '/^(aide-?domicile|senior|personne-?agee|agee|ainee?)/', $n ) ) return 'seniors';
	if ( preg_match( '/^(garde-?enfants?|enfant|bebe|kid|child)/', $n ) )            return 'enfants';
	return 'autres';
}

/**
 * Scanne assets/galerie/ et renvoie les photos groupées par catégorie :
 *   [ 'seniors' => [ ['url','alt','cap'], ... ], 'enfants' => [...], 'autres' => [...] ]
 */
function ag_domicile_galerie_groups() {
	$dir = get_theme_file_path( 'assets/galerie' );
	if ( ! is_dir( $dir ) ) return array();
	$files = glob( trailingslashit( $dir ) . '*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}', GLOB_BRACE );
	if ( empty( $files ) ) return array();
	sort( $files );
	$base   = trailingslashit( get_theme_file_uri( 'assets/galerie' ) );
	$groups = array( 'seniors' => array(), 'enfants' => array(), 'autres' => array() );
	foreach ( $files as $path ) {
		$file = basename( $path );
		$name = preg_replace( '/\.(jpe?g|png|webp)$/i', '', $file );
		$cap  = '';
		if ( preg_match( '/(?:__|--)(.+)$/', $name, $m ) ) {
			$cap  = trim( $m[1] );
			$name = preg_replace( '/(?:__|--).+$/', '', $name );
		}
		$cat = ag_domicile_galerie_cat( $name );
		$alt = preg_replace( '/^\d+[\s._-]+/', '', $name );
		$alt = trim( str_replace( array( '-', '_' ), ' ', $alt ) );
		if ( '' === $alt ) $alt = 'Gwen Services';
		$groups[ $cat ][] = array(
			'url' => $base . rawurlencode( $file ),
			'alt' => $alt,
			'cap' => $cap,
		);
	}
	// Retirer les catégories vides.
	return array_filter( $groups, function ( $g ) { return ! empty( $g ); } );
}

/** Rend une grille de photos (une catégorie). */
function ag_domicile_galerie_grid( $items ) {
	echo '<div class="ag-gwengal__grid">';
	foreach ( $items as $i => $it ) {
		$wide = ( 0 === $i % 5 ) ? ' ag-gwengal__cell--wide' : '';
		echo '<figure class="ag-gwengal__cell ag-anim' . esc_attr( $wide ) . '">';
		echo '<img src="' . esc_url( $it['url'] ) . '" alt="' . esc_attr( $it['alt'] ) . '" loading="lazy" decoding="async" />';
		if ( $it['cap'] ) {
			echo '<figcaption class="ag-gwengal__cap">' . esc_html( $it['cap'] ) . '</figcaption>';
		}
		echo '</figure>';
	}
	echo '</div>';
}

/**
 * Affiche la galerie « En images », dispatchée par catégorie (rien si aucune photo).
 */
function ag_domicile_render_galerie() {
	$groups = ag_domicile_galerie_groups();
	if ( empty( $groups ) ) return;

	// Libellés + ordre d'affichage des catégories.
	$labels = array(
		'seniors' => array( 'Auprès des personnes âgées', 'Accompagnement, présence et aide au quotidien, en douceur.' ),
		'enfants' => array( 'Garde d’enfants', 'De 1 mois à 8 ans — éveil, jeux et sécurité à la maison.' ),
		'autres'  => array( 'En images', 'Des moments de vrai lien.' ),
	);
	?>
	<section class="ag-gwengal">
		<div class="ag-gwengal__in">
			<div class="ag-gwengal__head ag-anim">
				<span class="ag-gwengal__tag">En images</span>
				<h2 class="ag-gwengal__title">Des moments <em>de vrai lien</em></h2>
				<p class="ag-gwengal__lead">Auprès des personnes âgées comme des tout-petits — une présence douce, à Nantes et alentours.</p>
			</div>

			<?php foreach ( array( 'seniors', 'enfants', 'autres' ) as $cat ) :
				if ( empty( $groups[ $cat ] ) ) continue;
				list( $cat_title, $cat_sub ) = $labels[ $cat ];
				?>
				<div class="ag-gwengal__cat ag-anim">
					<div class="ag-gwengal__cathead">
						<span class="ag-gwengal__catdot ag-gwengal__catdot--<?php echo esc_attr( $cat ); ?>"></span>
						<h3 class="ag-gwengal__cattitle"><?php echo esc_html( $cat_title ); ?></h3>
						<span class="ag-gwengal__catsub"><?php echo esc_html( $cat_sub ); ?></span>
					</div>
					<?php ag_domicile_galerie_grid( $groups[ $cat ] ); ?>
				</div>
			<?php endforeach; ?>
		</div>
	</section>
	<style>
	.ag-gwengal{background:linear-gradient(180deg,#171612,#12160f);padding:clamp(64px,9vw,110px) 20px;}
	.ag-gwengal__in{max-width:1180px;margin:0 auto;}
	.ag-gwengal__head{text-align:center;max-width:720px;margin:0 auto 40px;}
	.ag-gwengal__tag{display:inline-block;font-size:.8rem;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:#8fd0a3;background:rgba(79,157,107,.14);border:1px solid rgba(111,191,138,.4);padding:8px 18px;border-radius:100px;margin-bottom:18px;}
	.ag-gwengal__title{font-family:"Fraunces",Georgia,serif;font-weight:700;font-size:clamp(1.8rem,4.2vw,2.8rem);line-height:1.1;color:#f6f1e4 !important;margin:0 0 14px;}
	.ag-gwengal__title em{font-style:italic;color:#6fbf8a !important;}
	.ag-gwengal__lead{color:#c7c1b0;font-size:clamp(1rem,2.2vw,1.14rem);line-height:1.6;margin:0;}
	/* Catégories dispatchées */
	.ag-gwengal__cat{margin-top:46px;}
	.ag-gwengal__cat:first-of-type{margin-top:8px;}
	.ag-gwengal__cathead{display:flex;align-items:center;flex-wrap:wrap;gap:8px 14px;padding-bottom:16px;margin-bottom:22px;border-bottom:1px solid rgba(111,191,138,.18);}
	.ag-gwengal__catdot{width:12px;height:12px;border-radius:50%;flex:none;box-shadow:0 0 0 4px rgba(79,157,107,.12);}
	.ag-gwengal__catdot--seniors{background:#6fbf8a;}
	.ag-gwengal__catdot--enfants{background:#c9a36b;box-shadow:0 0 0 4px rgba(201,163,107,.14);}
	.ag-gwengal__catdot--autres{background:#8fd0a3;}
	.ag-gwengal__cattitle{font-family:"Fraunces",Georgia,serif;font-weight:600;font-size:clamp(1.3rem,3vw,1.7rem);color:#f6f1e4 !important;margin:0;}
	.ag-gwengal__catsub{color:#8f8a7c;font-size:.98rem;}
	.ag-gwengal__grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:16px;grid-auto-flow:dense;}
	.ag-gwengal__cell{position:relative;margin:0;border-radius:20px;overflow:hidden;aspect-ratio:4/5;box-shadow:0 24px 50px -30px rgba(0,0,0,.8);border:1px solid rgba(111,191,138,.16);}
	.ag-gwengal__cell--wide{grid-column:span 2;aspect-ratio:auto;}
	@media(max-width:560px){.ag-gwengal__cell--wide{grid-column:span 1;aspect-ratio:4/5;}}
	.ag-gwengal__cell img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .5s cubic-bezier(.2,.7,.2,1);}
	.ag-gwengal__cell:hover img{transform:scale(1.06);}
	.ag-gwengal__cap{position:absolute;left:0;right:0;bottom:0;padding:26px 16px 14px;color:#fff;font-size:.92rem;font-weight:600;background:linear-gradient(180deg,transparent,rgba(8,10,6,.82));}
	@media(prefers-reduced-motion:reduce){.ag-gwengal__cell img{transition:none;}}
	</style>
	<?php
}

// Shortcode manuel, au cas où.
add_shortcode( 'ag_domicile_galerie', function () {
	ob_start();
	ag_domicile_render_galerie();
	return ob_get_clean();
} );
