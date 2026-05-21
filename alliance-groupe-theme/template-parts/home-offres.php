<?php
/**
 * Accueil — Priorité 1 : VENDRE.
 * Teaser des offres Sites Express (prix fixes) avec les 3 visuels packs,
 * fond image + overlay pour la profondeur, lien vers /sites-express.
 */
$ag_offres = array(
	'essentiel' => array( 'nom' => 'Essentiel', 'prix' => '490 €' ),
	'pro'       => array( 'nom' => 'Pro', 'prix' => '890 €' ),
	'boutique'  => array( 'nom' => 'Boutique', 'prix' => '1 490 €' ),
);
$ag_img_base = get_stylesheet_directory_uri() . '/assets/images/produits/produit-';
$ag_offres_bg = get_stylesheet_directory_uri() . '/assets/images/cities/nantes-1.jpg';
?>
<section class="ag-section ag-section--graphite ag-home-offres" id="offres" style="--offres-bg:url('<?php echo esc_url( $ag_offres_bg ); ?>');">
	<div class="ag-home-offres__bg" aria-hidden="true"></div>
	<div class="ag-container ag-home-offres__inner">
		<span class="ag-tag ag-anim" data-anim="tag">Nos offres ⚡ Sites Express</span>
		<h2 class="ag-section__title ag-anim" data-anim="title">Un site pro, <em>à prix fixe</em></h2>
		<p class="ag-section__desc ag-anim" data-anim="desc">Pas de devis, pas d'attente. Tu choisis, tu paies en ligne, on livre — sans rendez-vous.</p>

		<div class="ag-home-offres__grid">
			<?php foreach ( $ag_offres as $key => $o ) : ?>
				<a class="ag-home-offres__card ag-anim" data-anim="card" href="<?php echo esc_url( home_url( '/sites-express#packs' ) ); ?>">
					<img src="<?php echo esc_url( $ag_img_base . $key . '.jpg' ); ?>" alt="<?php echo esc_attr( $o['nom'] . ' — ' . $o['prix'] ); ?>" loading="lazy" width="1024" height="1024">
				</a>
			<?php endforeach; ?>
		</div>

		<div class="ag-home-offres__cta">
			<a href="<?php echo esc_url( home_url( '/sites-express' ) ); ?>" class="ag-btn-gold">Voir les offres + maintenance →</a>
			<a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" class="ag-btn-outline">Besoin de sur-mesure ?</a>
		</div>
	</div>
</section>

<style>
.ag-home-offres{position:relative;overflow:hidden;}
.ag-home-offres__bg{position:absolute;inset:0;background-image:var(--offres-bg);background-size:cover;background-position:center;background-attachment:fixed;opacity:.12;}
.ag-home-offres__bg::after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,rgba(13,13,17,.55),rgba(13,13,17,.9));}
.ag-home-offres__inner{position:relative;z-index:2;}
.ag-home-offres__grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px;margin-top:46px;}
.ag-home-offres__card{display:block;border-radius:18px;overflow:hidden;border:1px solid rgba(212,180,92,.18);transition:transform .4s ease,border-color .4s ease,box-shadow .4s ease;}
.ag-home-offres__card:hover{transform:translateY(-6px);border-color:rgba(212,180,92,.5);box-shadow:0 30px 70px rgba(0,0,0,.45);}
.ag-home-offres__card img{display:block;width:100%;height:auto;}
.ag-home-offres__cta{display:flex;flex-wrap:wrap;gap:16px;justify-content:center;margin-top:40px;}
@media(max-width:900px){.ag-home-offres__grid{grid-template-columns:1fr;max-width:420px;margin-left:auto;margin-right:auto;}}
@media(max-width:768px){.ag-home-offres__bg{background-attachment:scroll;}}
</style>
