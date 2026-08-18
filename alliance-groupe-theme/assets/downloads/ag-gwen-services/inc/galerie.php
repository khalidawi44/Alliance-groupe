<?php
/**
 * Galerie photo « En images » — s'affiche automatiquement sur la page d'accueil
 * dès qu'au moins une photo est déposée dans le thème.
 *
 * CONVENTION (aucun réglage à faire) :
 *   - Déposer les photos dans  assets/galerie/  (jpg, jpeg, png, webp).
 *   - Elles s'affichent triées par nom : 01-xxx.jpg, 02-xxx.jpg, ...
 *   - Le texte alt vient du nom de fichier (les tirets deviennent des espaces),
 *     ex. « garde-enfants-gouter.jpg » -> « garde enfants gouter ».
 *   - Une légende optionnelle : préfixer par un numéro puis « __ » ->
 *     « 03__Sortie d'école à Rezé.jpg » affichera « Sortie d'école à Rezé ».
 *   - Si le dossier est vide, RIEN ne s'affiche (aucun trou dans la page).
 *
 * @package AG_Starter_Domicile
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Scanne assets/galerie/ et renvoie une liste [ ['url'=>, 'alt'=>, 'cap'=>], ... ].
 */
function ag_domicile_galerie_items() {
	$dir = get_theme_file_path( 'assets/galerie' );
	if ( ! is_dir( $dir ) ) {
		return array();
	}
	$files = glob( trailingslashit( $dir ) . '*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}', GLOB_BRACE );
	if ( empty( $files ) ) {
		return array();
	}
	sort( $files );
	$base  = trailingslashit( get_theme_file_uri( 'assets/galerie' ) );
	$items = array();
	foreach ( $files as $path ) {
		$file = basename( $path );
		$name = preg_replace( '/\.(jpe?g|png|webp)$/i', '', $file );
		$cap  = '';
		// Légende explicite après « __ » (ou « -- »).
		if ( preg_match( '/(?:__|--)(.+)$/', $name, $m ) ) {
			$cap  = trim( $m[1] );
			$name = preg_replace( '/(?:__|--).+$/', '', $name );
		}
		// alt : retirer le préfixe numérique de tri (« 03-… » / « 03_… »).
		$alt = preg_replace( '/^\d+[\s._-]+/', '', $name );
		$alt = trim( str_replace( array( '-', '_' ), ' ', $alt ) );
		if ( '' === $alt ) {
			$alt = 'Gwen Services';
		}
		$items[] = array(
			'url' => $base . rawurlencode( $file ),
			'alt' => $alt,
			'cap' => $cap,
		);
	}
	return $items;
}

/**
 * Affiche la galerie « En images » (rien si aucune photo).
 */
function ag_domicile_render_galerie() {
	$items = ag_domicile_galerie_items();
	if ( empty( $items ) ) {
		return;
	}
	?>
	<section class="ag-gwengal">
		<div class="ag-gwengal__in">
			<div class="ag-gwengal__head ag-anim">
				<span class="ag-gwengal__tag">En images</span>
				<h2 class="ag-gwengal__title">Des moments <em>de vrai lien</em></h2>
				<p class="ag-gwengal__lead">Auprès des personnes âgées comme des tout-petits — une présence douce, à Nantes et alentours.</p>
			</div>
			<div class="ag-gwengal__grid">
				<?php foreach ( $items as $i => $it ) : ?>
					<figure class="ag-gwengal__cell ag-anim<?php echo ( 0 === $i % 5 ) ? ' ag-gwengal__cell--wide' : ''; ?>">
						<img src="<?php echo esc_url( $it['url'] ); ?>" alt="<?php echo esc_attr( $it['alt'] ); ?>" loading="lazy" decoding="async" />
						<?php if ( $it['cap'] ) : ?>
							<figcaption class="ag-gwengal__cap"><?php echo esc_html( $it['cap'] ); ?></figcaption>
						<?php endif; ?>
					</figure>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<style>
	.ag-gwengal{background:linear-gradient(180deg,#171612,#12160f);padding:clamp(64px,9vw,110px) 20px;}
	.ag-gwengal__in{max-width:1180px;margin:0 auto;}
	.ag-gwengal__head{text-align:center;max-width:720px;margin:0 auto 46px;}
	.ag-gwengal__tag{display:inline-block;font-size:.8rem;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:#8fd0a3;background:rgba(79,157,107,.14);border:1px solid rgba(111,191,138,.4);padding:8px 18px;border-radius:100px;margin-bottom:18px;}
	.ag-gwengal__title{font-family:"Fraunces",Georgia,serif;font-weight:700;font-size:clamp(1.8rem,4.2vw,2.8rem);line-height:1.1;color:#f6f1e4;margin:0 0 14px;}
	.ag-gwengal__title em{font-style:italic;color:#6fbf8a;}
	.ag-gwengal__lead{color:#c7c1b0;font-size:clamp(1rem,2.2vw,1.14rem);line-height:1.6;margin:0;}
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
