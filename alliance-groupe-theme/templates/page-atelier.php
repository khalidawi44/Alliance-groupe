<?php
/**
 * Template Name: Atelier IA
 *
 * Landing « atelier » inspirée des vitrines d'outils IA : hero à question,
 * filtres, et galerie de vignettes (générées par IA) menant à chaque outil /
 * service d'Alliance Groupe. Autonome (CSS + JS inline), à la marque or/sombre.
 *
 * @package Alliance_Groupe
 */

get_header();

$img = get_stylesheet_directory_uri() . '/assets/images/atelier/';

// Les vignettes : titre, sous-titre, image, lien, catégorie, badge.
$cards = array(
	array( 'title' => 'Devis instantané',   'desc' => 'Décris ton projet, l\'IA te chiffre en 30 secondes.', 'img' => 'devis.webp',      'url' => home_url( '/devis-instant' ),       'cat' => 'ia',       'badge' => 'IA' ),
	array( 'title' => 'Refais mon site',    'desc' => 'Colle ton URL : l\'IA génère une maquette modernisée.', 'img' => 'refais.webp',   'url' => home_url( '/refais-mon-site' ),     'cat' => 'ia',       'badge' => 'IA' ),
	array( 'title' => 'Fait par l\'IA',     'desc' => 'Le journal en direct de ce que l\'IA fait pour toi.', 'img' => 'ia.webp',         'url' => home_url( '/fait-par-lia' ),        'cat' => 'ia',       'badge' => 'IA' ),
	array( 'title' => 'Studio créatif',     'desc' => 'Crée des vidéos & visuels prêts à publier.',        'img' => 'studio.webp',     'url' => home_url( '/studio' ),              'cat' => 'creer',    'badge' => 'Gratuit' ),
	array( 'title' => 'Création de sites',  'desc' => 'Ton site pro, sécurisé, livré en quelques jours.',  'img' => 'sites.webp',      'url' => home_url( '/sites-express' ),       'cat' => 'creer',    'badge' => 'Dès 490 €' ),
	array( 'title' => 'Audit de sécurité',  'desc' => 'Teste ton site : note /100 + failles détectées.',   'img' => 'securite.webp',   'url' => home_url( '/tester-mon-site' ),     'cat' => 'securite', 'badge' => 'Gratuit' ),
	array( 'title' => 'Composants web',     'desc' => 'Boutons & effets à copier ou télécharger.',         'img' => 'composants.webp', 'url' => home_url( '/composants' ),          'cat' => 'creer',    'badge' => 'Gratuit' ),
	array( 'title' => 'Templates WordPress','desc' => '6 thèmes métier gratuits, prêts à installer.',       'img' => 'templates.webp',  'url' => home_url( '/templates-wordpress' ), 'cat' => 'creer',    'badge' => 'Gratuit' ),
);

$filters = array(
	'all'      => '✨ Tous',
	'ia'       => '🤖 IA',
	'creer'    => '🌐 Créer',
	'securite' => '🔒 Sécurité',
);
?>
<style>
.ag-atl{--gold:#d4b45c;--gold2:#f4d06f;background:#07070c;color:#eef1f6;overflow:hidden;}
.ag-atl__hero{position:relative;text-align:center;padding:clamp(70px,12vw,140px) 20px clamp(40px,7vw,80px);}
.ag-atl__hero::before,.ag-atl__hero::after{content:"";position:absolute;border-radius:50%;filter:blur(80px);opacity:.55;z-index:0;pointer-events:none;animation:agAtlFloat 14s ease-in-out infinite;}
.ag-atl__hero::before{width:38vw;height:38vw;left:-8vw;top:-6vw;background:radial-gradient(circle,#7a5cff55,transparent 70%);}
.ag-atl__hero::after{width:42vw;height:42vw;right:-10vw;top:2vw;background:radial-gradient(circle,#d4b45c55,transparent 70%);animation-delay:-6s;}
@keyframes agAtlFloat{0%,100%{transform:translate(0,0) scale(1)}50%{transform:translate(0,26px) scale(1.08)}}
.ag-atl__eyebrow{position:relative;z-index:1;display:inline-block;font-size:.8rem;letter-spacing:.22em;text-transform:uppercase;color:var(--gold);border:1px solid rgba(212,180,92,.35);border-radius:100px;padding:6px 16px;margin-bottom:22px;}
.ag-atl__title{position:relative;z-index:1;font-size:clamp(2rem,6vw,4.2rem);line-height:1.05;font-weight:800;margin:0 auto 18px;max-width:16ch;text-wrap:balance;background:linear-gradient(120deg,#fff 30%,var(--gold2));-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;}
.ag-atl__sub{position:relative;z-index:1;color:#aab3c5;max-width:60ch;margin:0 auto 30px;font-size:clamp(1rem,2.2vw,1.2rem);line-height:1.55;}
.ag-atl__filters{position:relative;z-index:1;display:flex;flex-wrap:wrap;gap:10px;justify-content:center;margin-bottom:8px;}
.ag-atl__pill{cursor:pointer;border:1px solid rgba(255,255,255,.14);background:rgba(255,255,255,.04);color:#cfd6e4;border-radius:100px;padding:9px 18px;font-size:.92rem;font-weight:600;transition:.2s;}
.ag-atl__pill:hover{border-color:var(--gold);color:#fff;}
.ag-atl__pill.on{background:linear-gradient(120deg,var(--gold),var(--gold2));color:#1a1206;border-color:transparent;}
.ag-atl__grid{position:relative;z-index:1;display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:22px;max-width:1240px;margin:36px auto 0;padding:0 20px clamp(60px,10vw,120px);}
.ag-atl__card{position:relative;display:block;text-decoration:none;color:inherit;border-radius:18px;overflow:hidden;background:#0e0e16;border:1px solid rgba(255,255,255,.08);opacity:0;transform:translateY(26px);transition:opacity .6s ease,transform .6s ease,border-color .3s,box-shadow .3s;}
.ag-atl__card.in{opacity:1;transform:none;}
.ag-atl__card:hover{border-color:rgba(212,180,92,.6);box-shadow:0 20px 50px -20px rgba(212,180,92,.5);}
.ag-atl__media{position:relative;aspect-ratio:16/9;overflow:hidden;}
.ag-atl__media img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .6s ease;}
.ag-atl__card:hover .ag-atl__media img{transform:scale(1.07);}
.ag-atl__media::after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,transparent 40%,rgba(7,7,12,.85));}
.ag-atl__badge{position:absolute;top:12px;left:12px;z-index:2;font-size:.72rem;font-weight:700;letter-spacing:.04em;background:rgba(7,7,12,.7);border:1px solid rgba(212,180,92,.5);color:var(--gold2);border-radius:100px;padding:4px 11px;backdrop-filter:blur(4px);}
.ag-atl__body{padding:16px 18px 20px;}
.ag-atl__ctitle{font-size:1.18rem;font-weight:700;margin:0 0 5px;}
.ag-atl__cdesc{color:#9aa4b6;font-size:.9rem;line-height:1.45;margin:0 0 12px;min-height:2.6em;}
.ag-atl__go{display:inline-flex;align-items:center;gap:6px;font-weight:700;font-size:.92rem;color:var(--gold2);transition:gap .25s;}
.ag-atl__card:hover .ag-atl__go{gap:12px;}
.ag-atl__cta{position:relative;z-index:1;text-align:center;padding:0 20px clamp(70px,12vw,130px);}
.ag-atl__cta a{display:inline-block;background:linear-gradient(120deg,var(--gold),var(--gold2));color:#1a1206;font-weight:800;text-decoration:none;border-radius:100px;padding:15px 34px;font-size:1.02rem;box-shadow:0 12px 40px -12px rgba(212,180,92,.6);transition:transform .2s;}
.ag-atl__cta a:hover{transform:translateY(-2px);}
.ag-atl__empty{grid-column:1/-1;text-align:center;color:#8a93a6;padding:40px;display:none;}
@media(prefers-reduced-motion:reduce){.ag-atl__hero::before,.ag-atl__hero::after{animation:none}.ag-atl__card{transition:none;opacity:1;transform:none}}
</style>

<main class="ag-atl">
	<section class="ag-atl__hero">
		<span class="ag-atl__eyebrow">Alliance Groupe · Web · Sécurité · IA</span>
		<h1 class="ag-atl__title">Que créons-nous aujourd'hui&nbsp;?</h1>
		<p class="ag-atl__sub">Ton atelier propulsé par l'IA : chiffre un projet, refais ton site, sécurise-le, crée du contenu — chaque brique s'ouvre en un clic.</p>
		<div class="ag-atl__filters" id="ag-atl-filters">
			<?php $first = true; foreach ( $filters as $key => $label ) : ?>
				<button type="button" class="ag-atl__pill<?php echo $first ? ' on' : ''; ?>" data-f="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></button>
			<?php $first = false; endforeach; ?>
		</div>
	</section>

	<div class="ag-atl__grid" id="ag-atl-grid">
		<?php foreach ( $cards as $c ) : ?>
			<a class="ag-atl__card" href="<?php echo esc_url( $c['url'] ); ?>" data-cat="<?php echo esc_attr( $c['cat'] ); ?>">
				<div class="ag-atl__media">
					<span class="ag-atl__badge"><?php echo esc_html( $c['badge'] ); ?></span>
					<img src="<?php echo esc_url( $img . $c['img'] ); ?>" alt="<?php echo esc_attr( $c['title'] ); ?>" loading="lazy" width="1000" height="563">
				</div>
				<div class="ag-atl__body">
					<h3 class="ag-atl__ctitle"><?php echo esc_html( $c['title'] ); ?></h3>
					<p class="ag-atl__cdesc"><?php echo esc_html( $c['desc'] ); ?></p>
					<span class="ag-atl__go">Ouvrir <span aria-hidden="true">→</span></span>
				</div>
			</a>
		<?php endforeach; ?>
		<div class="ag-atl__empty" id="ag-atl-empty">Rien dans cette catégorie.</div>
	</div>

	<div class="ag-atl__cta">
		<a href="<?php echo esc_url( home_url( '/devis-instant' ) ); ?>">🚀 Lancer mon projet — devis instantané</a>
	</div>
</main>

<script>
(function(){
	var grid = document.getElementById('ag-atl-grid');
	if(!grid) return;
	var cards = [].slice.call(grid.querySelectorAll('.ag-atl__card'));
	var empty = document.getElementById('ag-atl-empty');

	// Apparition au scroll.
	if('IntersectionObserver' in window){
		var io = new IntersectionObserver(function(ents){
			ents.forEach(function(e){ if(e.isIntersecting){ e.target.classList.add('in'); io.unobserve(e.target); } });
		}, {threshold:.12});
		cards.forEach(function(c){ io.observe(c); });
	} else { cards.forEach(function(c){ c.classList.add('in'); }); }

	// Filtres.
	document.getElementById('ag-atl-filters').addEventListener('click', function(ev){
		var b = ev.target.closest('.ag-atl__pill'); if(!b) return;
		this.querySelectorAll('.ag-atl__pill').forEach(function(x){ x.classList.remove('on'); });
		b.classList.add('on');
		var f = b.getAttribute('data-f'), shown = 0;
		cards.forEach(function(c){
			var ok = (f === 'all' || c.getAttribute('data-cat') === f);
			c.style.display = ok ? '' : 'none';
			if(ok){ shown++; c.classList.add('in'); }
		});
		empty.style.display = shown ? 'none' : 'block';
	});
})();
</script>

<?php get_footer(); ?>
