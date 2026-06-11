<?php
/**
 * Section "Choisissez votre chemin" — 4 panneaux qui s'ouvrent au
 * hover (desktop) ou s'empilent (mobile). Calqué sur le pattern de
 * sites premium (theirisk.com style) : "Hero qui ouvre toutes les
 * possibilités".
 *
 * Chaque panneau cible une audience : commerçant, projet sur-mesure,
 * ambassadeur, association. Pure CSS, aucun JS requis pour l'expansion.
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$ag_img = get_stylesheet_directory_uri() . '/assets/images/parcours/';
// URL personnalisée (Réglages → Tester/Audit) sinon fichier local du dossier parcours/.
$ag_cardimg = function ( $key ) use ( $ag_img ) {
	$u = get_option( 'ag_tester_img_' . $key, '' );
	return $u ? $u : $ag_img . $key . '.jpg';
};
$ag_paths = array(
	array(
		'emoji' => '🔒',
		'tag'   => 'Sécurité · le 1er pas',
		'title' => 'Audit de votre site',
		'desc'  => 'Je révèle vos failles en 48 h : rapport clair, sans jargon, recommandations chiffrées. Le point de départ, presque obligatoire, vers la sérénité.',
		'cta'   => 'Démarrer l\'audit',
		'url'   => home_url( '/tester-mon-site' ),
		'tint'  => '#0f0f1c',
		'glow'  => '#F37A1F',
		'img'   => $ag_cardimg( 'audit' ),
	),
	array(
		'emoji' => '✨',
		'tag'   => 'Création',
		'title' => 'Un site qui inspire confiance',
		'desc'  => 'Vitrine, e-commerce, sur-mesure — conçu rapide, propre et sécurisé dès le départ. Dès 490 €, payable en 4× sans frais.',
		'cta'   => 'Voir les offres',
		'url'   => home_url( '/sites-express' ),
		'tint'  => '#1a1a26',
		'glow'  => '#D4B45C',
		'img'   => $ag_cardimg( 'creation' ),
	),
	array(
		'emoji' => '✦',
		'tag'   => 'Premium · sur devis',
		'title' => 'Site 100% sur-mesure',
		'desc'  => 'Un site unique, pensé pour convertir : design, fonctionnalités et SEO conçus rien que pour vous — du niveau de notre propre site. Sur devis.',
		'cta'   => 'Composer mon projet',
		'url'   => home_url( '/sur-mesure' ),
		'tint'  => '#14101c',
		'glow'  => '#B47CFF',
		'img'   => $ag_cardimg( 'sur-mesure' ),
	),
	array(
		'emoji' => '🛡️',
		'tag'   => 'Tranquillité',
		'title' => 'Maintenance & sérénité',
		'desc'  => 'Mises à jour, sauvegardes, surveillance, sécurité — votre site sain chaque mois, sans y penser. Dès 49 €/mois, sans engagement.',
		'cta'   => 'Voir les formules',
		'url'   => home_url( '/maintenance' ),
		'tint'  => '#0f1c14',
		'glow'  => '#28a745',
		'img'   => $ag_cardimg( 'maintenance' ),
	),
	array(
		'emoji' => '📦',
		'tag'   => 'Prêt à l\'emploi',
		'title' => 'Templates métier',
		'desc'  => '6 thèmes français (avocat, resto, artisan, coach, barber, asso) prêts à installer en 2 min. Téléchargement immédiat.',
		'cta'   => 'Choisir mon métier',
		'url'   => home_url( '/templates-wordpress' ),
		'tint'  => '#1c1410',
		'glow'  => '#D4B45C',
		'img'   => $ag_cardimg( 'templates' ),
	),
	array(
		'emoji' => '🛡️',
		'tag'   => 'Sécurité · résilience',
		'title' => 'Test de résilience ransomware',
		'desc'  => 'On simule une attaque ransomware sans toucher à vos vraies données : vos sauvegardes tiennent-elles, votre antivirus détecte-t-il ? Rapport clair + plan d\'action. Dès 490 €.',
		'cta'   => 'Tester ma résilience',
		'url'   => home_url( '/resilience-ransomware' ),
		'tint'  => '#1c0f12',
		'glow'  => '#E10F1A',
		// Image : option dédiée sinon parcours/resilience.jpg sinon une image sécurité existante.
		'img'   => get_option( 'ag_tester_img_resilience', '' )
			?: ( file_exists( get_stylesheet_directory() . '/assets/images/parcours/resilience.jpg' )
				? $ag_img . 'resilience.jpg'
				: get_stylesheet_directory_uri() . '/assets/images/securite/max-bender-XIVDN9cxOVc-unsplash.jpg' ),
	),
);
?>

<section class="ag-paths" aria-label="Choisissez votre parcours">
	<div class="ag-paths__head">
		<span class="ag-paths__tag">⟶ Choisissez votre parcours</span>
		<h2 class="ag-paths__title">Quel est <em>votre projet</em> ?</h2>
		<p class="ag-paths__lead">Sécuriser, créer, rester tranquille — touchez le panneau qui vous concerne. Tout commence par l'audit.</p>
	</div>

	<div class="ag-paths__grid">
		<?php foreach ( $ag_paths as $i => $p ) : ?>
			<a href="<?php echo esc_url( $p['url'] ); ?>" class="ag-path" style="--ag-tint:<?php echo esc_attr( $p['tint'] ); ?>;--ag-glow:<?php echo esc_attr( $p['glow'] ); ?>;" tabindex="0">
				<div class="ag-path__photo" aria-hidden="true" style="background-image:url('<?php echo esc_url( $p['img'] ); ?>');"></div>
				<div class="ag-path__veil" aria-hidden="true"></div>
				<div class="ag-path__bg" aria-hidden="true"></div>
				<div class="ag-path__inner">
					<div class="ag-path__top">
						<span class="ag-path__emoji"><?php echo $p['emoji']; // phpcs:ignore ?></span>
						<span class="ag-path__tag"><?php echo esc_html( $p['tag'] ); ?></span>
					</div>
					<h3 class="ag-path__title"><?php echo esc_html( $p['title'] ); ?></h3>
					<p class="ag-path__desc"><?php echo esc_html( $p['desc'] ); ?></p>
					<span class="ag-path__cta"><?php echo esc_html( $p['cta'] ); ?> →</span>
				</div>
			</a>
		<?php endforeach; ?>
	</div>
</section>

<style>
.ag-paths{padding:80px 24px 100px;background:linear-gradient(180deg,#0a0a0f 0%,#070710 100%);color:#fff;position:relative;overflow:hidden}
.ag-paths::before{content:'';position:absolute;inset:0;background:radial-gradient(circle at 50% 0%,rgba(212,180,92,.08) 0%,transparent 60%);pointer-events:none}
.ag-paths__head{max-width:760px;margin:0 auto 50px;text-align:center;position:relative;z-index:1}
.ag-paths__tag{display:inline-block;color:#D4B45C;font-size:.82rem;letter-spacing:3px;text-transform:uppercase;font-weight:700;margin-bottom:14px}
.ag-paths__title{font-family:Georgia,'Playfair Display',serif;font-size:clamp(2rem,4.5vw,3.4rem);font-weight:700;line-height:1.1;margin:0 0 14px}
.ag-paths__title em{background:linear-gradient(135deg,#D4B45C,#F37A1F);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;color:transparent;font-style:italic}
.ag-paths__lead{color:rgba(255,255,255,.7);font-size:1.05rem;line-height:1.6;margin:0}

/* Grille — desktop = 4 colonnes flex extensibles, mobile = stack */
.ag-paths__grid{max-width:1400px;margin:0 auto;display:flex;gap:14px;height:520px;position:relative;z-index:1}
.ag-path{flex:1;min-width:0;position:relative;overflow:hidden;border-radius:22px;text-decoration:none;color:#fff;border:1px solid rgba(212,180,92,.18);background:var(--ag-tint);transition:flex .55s cubic-bezier(.16,1,.3,1),border-color .35s ease,box-shadow .35s ease;cursor:pointer}
/* Photo de fond + voile sombre pour garder le texte lisible */
.ag-path__photo{position:absolute;inset:0;background-size:cover;background-position:center;opacity:.42;z-index:0;transition:opacity .55s ease,transform .7s ease;will-change:transform,opacity}
.ag-path__veil{position:absolute;inset:0;background:linear-gradient(180deg,rgba(7,7,14,.55) 0%,rgba(7,7,14,.62) 42%,var(--ag-tint) 100%);z-index:1}
.ag-path__bg{position:absolute;inset:0;z-index:2;background:
	radial-gradient(circle at 30% 20%,var(--ag-glow) 0%,transparent 38%),
	radial-gradient(circle at 75% 90%,var(--ag-glow) 0%,transparent 50%);
	opacity:.18;transition:opacity .55s ease,transform .55s ease;will-change:transform,opacity}
.ag-path__inner{position:relative;z-index:3;height:100%;padding:30px 28px;display:flex;flex-direction:column;justify-content:flex-end}
.ag-path__top{position:absolute;top:24px;left:28px;right:28px;display:flex;align-items:center;gap:12px}
.ag-path__emoji{font-size:2.2rem;line-height:1;flex-shrink:0;filter:drop-shadow(0 4px 12px rgba(0,0,0,.5))}
.ag-path__tag{color:var(--ag-glow);font-size:.72rem;letter-spacing:2px;text-transform:uppercase;font-weight:700;opacity:.85;line-height:1.2}
.ag-path__title{font-family:Georgia,'Playfair Display',serif;font-size:clamp(1.4rem,2.4vw,2.1rem);font-weight:700;line-height:1.15;margin:0 0 12px;color:#fff}
.ag-path__desc{color:rgba(255,255,255,.78);font-size:.92rem;line-height:1.55;margin:0 0 18px;max-height:0;opacity:0;overflow:hidden;transition:max-height .55s ease,opacity .35s ease,margin .35s ease}
.ag-path__cta{color:var(--ag-glow);font-weight:800;letter-spacing:1px;text-transform:uppercase;font-size:.85rem;opacity:.85;transition:opacity .35s ease,transform .35s ease}

/* Hover/focus = expand */
@media (min-width:900px){
	.ag-path:hover,.ag-path:focus{flex:2.6;border-color:var(--ag-glow);box-shadow:0 30px 80px rgba(0,0,0,.55),0 0 60px color-mix(in srgb,var(--ag-glow) 35%,transparent)}
	.ag-path:hover .ag-path__bg,.ag-path:focus .ag-path__bg{opacity:.42;transform:scale(1.05)}
	.ag-path:hover .ag-path__photo,.ag-path:focus .ag-path__photo{opacity:.72;transform:scale(1.06)}
	.ag-path:hover .ag-path__desc,.ag-path:focus .ag-path__desc{max-height:200px;opacity:1;margin-bottom:18px}
	.ag-path:hover .ag-path__cta,.ag-path:focus .ag-path__cta{opacity:1;transform:translateX(4px)}
}

/* Mobile / tablette = stack vertical, descriptions toujours visibles */
@media (max-width:900px){
	.ag-paths__grid{flex-direction:column;height:auto;gap:14px}
	.ag-path{min-height:200px;border-radius:18px}
	.ag-path__inner{padding:64px 22px 26px}
	.ag-path__bg{opacity:.30}
	.ag-path__desc{max-height:none;opacity:1;overflow:visible}
	.ag-path__cta{opacity:1}
	.ag-path:active{transform:scale(.985)}
}

/* Animation d'entrée séquentielle */
@keyframes ag-path-reveal{from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:none}}
.ag-path{animation:ag-path-reveal .8s cubic-bezier(.16,1,.3,1) backwards}
.ag-path:nth-child(1){animation-delay:.05s}
.ag-path:nth-child(2){animation-delay:.15s}
.ag-path:nth-child(3){animation-delay:.25s}
.ag-path:nth-child(4){animation-delay:.35s}
.ag-path:nth-child(5){animation-delay:.45s}

@media (prefers-reduced-motion:reduce){
	.ag-path,.ag-path__bg,.ag-path__desc,.ag-path__cta{transition:none;animation:none}
}
</style>
