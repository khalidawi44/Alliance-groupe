<?php
/**
 * Témoignages — style « avis Google » : note 5 étoiles, logo Google,
 * citation du client + « Ce que j'ai fait » (simple et clair).
 * Remplace l'ancienne section « Réalisations » sur l'accueil.
 *
 * Clients réels (liens vérifiables). Pour ajouter un avis : compléter
 * le tableau $avis.
 *
 * @package Alliance_Groupe_Theme
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$avis = array(
	array(
		'name'   => 'Anna B.',
		'role'   => 'Photographe portraitiste · Nantes',
		'init'   => 'A',
		'quote'  => 'Refonte complète de mon site, design immersif pour mes portraits. Le trafic a presque triplé en six mois et je reçois enfin des demandes depuis Google.',
		'did'    => 'Création du site, design portfolio, SEO local.',
		'url'    => 'https://annaphoto.eu/',
	),
	array(
		'name'   => 'L.A Environnement',
		'role'   => 'Paysagiste · Loire-Atlantique',
		'init'   => 'L',
		'quote'  => 'Site vitrine pensé pour générer des devis en local. On est passé de 1 à 15 demandes par mois, et je suis dans le top 3 sur Google.',
		'did'    => 'Site vitrine, formulaires de devis, référencement local.',
		'url'    => 'https://www.paysagiste-environnement.com/',
	),
);
?>
<section class="ag-avis" aria-label="Avis clients">
	<div class="ag-avis__inner">
		<div class="ag-avis__head">
			<span class="ag-avis__tag">⟶ Ils m'ont fait confiance</span>
			<h2 class="ag-avis__title">Des projets concrets, <em>des résultats réels.</em></h2>
			<div class="ag-avis__google">
				<span class="ag-avis__g" aria-hidden="true">
					<svg viewBox="0 0 24 24" width="20" height="20"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.27-4.74 3.27-8.1z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.99.66-2.26 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84A11 11 0 0 0 12 23z"/><path fill="#FBBC05" d="M5.84 14.1a6.6 6.6 0 0 1 0-4.2V7.06H2.18a11 11 0 0 0 0 9.88l3.66-2.84z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84C6.71 7.31 9.14 5.38 12 5.38z"/></svg>
				</span>
				<span class="ag-avis__rate"><strong>5,0</strong> ★★★★★ · avis vérifiés</span>
			</div>
		</div>

		<div class="ag-avis__grid">
			<?php foreach ( $avis as $a ) : ?>
				<article class="ag-avis__card">
					<header class="ag-avis__top">
						<span class="ag-avis__avatar"><?php echo esc_html( $a['init'] ); ?></span>
						<div class="ag-avis__who">
							<span class="ag-avis__name"><?php echo esc_html( $a['name'] ); ?></span>
							<span class="ag-avis__role"><?php echo esc_html( $a['role'] ); ?></span>
						</div>
						<span class="ag-avis__glogo" aria-label="Avis Google">
							<svg viewBox="0 0 24 24" width="22" height="22"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.27-4.74 3.27-8.1z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.99.66-2.26 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84A11 11 0 0 0 12 23z"/><path fill="#FBBC05" d="M5.84 14.1a6.6 6.6 0 0 1 0-4.2V7.06H2.18a11 11 0 0 0 0 9.88l3.66-2.84z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84C6.71 7.31 9.14 5.38 12 5.38z"/></svg>
						</span>
					</header>
					<div class="ag-avis__stars" aria-label="5 étoiles sur 5">★★★★★</div>
					<p class="ag-avis__quote"><?php echo esc_html( $a['quote'] ); ?></p>
					<p class="ag-avis__did"><span>Ce que j'ai fait :</span> <?php echo esc_html( $a['did'] ); ?></p>
					<a class="ag-avis__link" href="<?php echo esc_url( $a['url'] ); ?>" target="_blank" rel="noopener noreferrer">Voir le site →</a>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<style>
.ag-avis{padding:80px 22px;background:linear-gradient(180deg,#070710 0%,#0a0a12 100%);color:#fff}
.ag-avis__inner{max-width:1000px;margin:0 auto}
.ag-avis__head{text-align:center;margin-bottom:40px}
.ag-avis__tag{display:inline-block;color:#D4B45C;font-size:.8rem;letter-spacing:3px;text-transform:uppercase;font-weight:800;margin-bottom:12px}
.ag-avis__title{font-family:Georgia,'Playfair Display',serif;font-size:clamp(1.7rem,3.6vw,2.6rem);line-height:1.15;margin:0 0 16px}
.ag-avis__title em{font-style:italic;background:linear-gradient(135deg,#D4B45C,#F37A1F);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;color:transparent}
.ag-avis__google{display:inline-flex;align-items:center;gap:10px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.12);border-radius:100px;padding:8px 18px}
.ag-avis__rate{font-size:.92rem;color:rgba(255,255,255,.85);letter-spacing:.5px}
.ag-avis__rate strong{color:#fff}

.ag-avis__grid{display:grid;grid-template-columns:repeat(2,1fr);gap:20px}
.ag-avis__card{background:#fff;color:#1a1a1a;border-radius:16px;padding:24px 22px;box-shadow:0 20px 50px rgba(0,0,0,.4);display:flex;flex-direction:column}
.ag-avis__top{display:flex;align-items:center;gap:12px;margin-bottom:12px}
.ag-avis__avatar{width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,#D4B45C,#F37A1F);color:#fff;font-weight:800;font-size:1.2rem;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.ag-avis__who{display:flex;flex-direction:column;flex:1;min-width:0}
.ag-avis__name{font-weight:700;font-size:1rem;color:#1a1a1a}
.ag-avis__role{font-size:.8rem;color:#666}
.ag-avis__glogo{flex-shrink:0}
.ag-avis__stars{color:#fbbc05;font-size:1.1rem;letter-spacing:2px;margin-bottom:10px}
.ag-avis__quote{font-size:.98rem;line-height:1.55;color:#2a2a2a;margin:0 0 14px}
.ag-avis__did{font-size:.86rem;color:#555;margin:0 0 16px;padding:10px 12px;background:#f4f4f6;border-radius:10px;border-left:3px solid #F37A1F}
.ag-avis__did span{font-weight:700;color:#1a1a1a}
.ag-avis__link{margin-top:auto;align-self:flex-start;color:#0a66c2;font-weight:700;text-decoration:none;font-size:.9rem}
.ag-avis__link:hover{text-decoration:underline}

@media(max-width:740px){
	.ag-avis__grid{grid-template-columns:1fr}
}
</style>
