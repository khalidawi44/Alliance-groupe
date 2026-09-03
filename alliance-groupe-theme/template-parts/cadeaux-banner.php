<?php
/**
 * Bandeau "3 cadeaux pour démarrer" — lead magnets sur la home.
 * Templates gratuits + Audit SEO + Tirage au sort mensuel.
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$ag_cadeaux = array(
	array(
		'emoji' => '',
		'title' => '6 templates WordPress',
		'desc'  => 'Thèmes métier 100 % français, prêts à installer. Téléchargement immédiat.',
		'cta'   => 'Choisir mon métier',
		'url'   => home_url( '/templates-wordpress' ),
		'glow'  => '#28a745',
	),
	array(
		'emoji' => '',
		'title' => 'Audit SEO gratuit',
		'desc'  => '12 points analysés, note /100, rapport PDF. Découvrez pourquoi Google ne vous trouve pas.',
		'cta'   => 'Lancer mon audit',
		'url'   => home_url( '/audit-seo' ),
		'glow'  => '#D4B45C',
	),
	array(
		'emoji' => '',
		'title' => '1 site gratuit / mois',
		'desc'  => 'Un site web pro (490 €) tiré au sort chaque mois. Participation gratuite, sans engagement.',
		'cta'   => 'Je participe',
		'url'   => home_url( '/tirage-au-sort' ),
		'glow'  => '#F37A1F',
	),
);
?>

<section class="ag-cadeaux">
	<div class="ag-cadeaux__head">
		<span class="ag-cadeaux__tag">Offert · 0 €</span>
		<h2 class="ag-cadeaux__title">3 cadeaux pour <em>démarrer</em></h2>
		<p class="ag-cadeaux__lead">Pas besoin d'acheter pour commencer. On vous offre de quoi tester, vous lancer, et peut-être gagner.</p>
	</div>
	<div class="ag-cadeaux__grid">
		<?php foreach ( $ag_cadeaux as $c ) : ?>
			<a href="<?php echo esc_url( $c['url'] ); ?>" class="ag-cadeau" style="--ag-glow:<?php echo esc_attr( $c['glow'] ); ?>">
				<span class="ag-cadeau__emoji"><?php echo $c['emoji']; // phpcs:ignore ?></span>
				<h3 class="ag-cadeau__title"><?php echo esc_html( $c['title'] ); ?></h3>
				<p class="ag-cadeau__desc"><?php echo esc_html( $c['desc'] ); ?></p>
				<span class="ag-cadeau__cta"><?php echo esc_html( $c['cta'] ); ?> →</span>
			</a>
		<?php endforeach; ?>
	</div>
</section>

<style>
.ag-cadeaux{padding:80px 24px;background:linear-gradient(180deg,#070710 0%,#0f0f18 100%);position:relative;overflow:hidden}
.ag-cadeaux::before{content:'';position:absolute;inset:0;background:radial-gradient(circle at 50% 0%,rgba(212,180,92,.07) 0%,transparent 55%);pointer-events:none}
.ag-cadeaux__head{max-width:680px;margin:0 auto 44px;text-align:center;position:relative;z-index:1}
.ag-cadeaux__tag{display:inline-block;color:#D4B45C;font-size:.8rem;letter-spacing:3px;text-transform:uppercase;font-weight:700;margin-bottom:12px}
.ag-cadeaux__title{font-family:Georgia,'Playfair Display',serif;font-size:clamp(1.9rem,4.2vw,3rem);font-weight:700;line-height:1.1;margin:0 0 12px;color:#fff}
.ag-cadeaux__title em{background:linear-gradient(135deg,#D4B45C,#F37A1F);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;font-style:italic}
.ag-cadeaux__lead{color:rgba(255,255,255,.72);font-size:1.02rem;line-height:1.6;margin:0}
.ag-cadeaux__grid{max-width:1100px;margin:0 auto;display:grid;grid-template-columns:repeat(3,1fr);gap:20px;position:relative;z-index:1}
@media (max-width:820px){.ag-cadeaux__grid{grid-template-columns:1fr;gap:16px}}
.ag-cadeau{display:flex;flex-direction:column;align-items:flex-start;padding:30px 26px;background:rgba(255,255,255,.03);border:1px solid rgba(212,180,92,.2);border-radius:18px;text-decoration:none;color:#fff;transition:transform .3s ease,border-color .3s ease,box-shadow .3s ease;position:relative;overflow:hidden}
.ag-cadeau::after{content:'';position:absolute;top:-40%;right:-30%;width:200px;height:200px;background:radial-gradient(circle,var(--ag-glow) 0%,transparent 65%);opacity:.12;transition:opacity .3s ease}
.ag-cadeau:hover{transform:translateY(-6px);border-color:var(--ag-glow);box-shadow:0 24px 60px rgba(0,0,0,.5);color:#fff;text-decoration:none}
.ag-cadeau:hover::after{opacity:.28}
.ag-cadeau__emoji{font-size:2.6rem;line-height:1;margin-bottom:16px;filter:drop-shadow(0 6px 16px rgba(0,0,0,.5))}
.ag-cadeau__title{font-family:Georgia,serif;font-size:1.35rem;font-weight:700;margin:0 0 10px;color:#fff}
.ag-cadeau__desc{color:rgba(255,255,255,.72);font-size:.92rem;line-height:1.55;margin:0 0 auto;padding-bottom:18px}
.ag-cadeau__cta{color:var(--ag-glow);font-weight:800;letter-spacing:1px;text-transform:uppercase;font-size:.82rem}
</style>
