<?php
/**
 * template-parts/atelier-gallery.php — Galerie « Atelier IA » réutilisable.
 *
 * Bloc autonome (style + filtres + grille + JS) : les outils IA & services
 * d'Alliance Groupe en vignettes cliquables filtrables. Utilisé sur la page
 * /atelier ET sous le hero de l'accueil.
 *
 * Args (via get_template_part(..., $args)) :
 *   - heading (string) : titre de section (vide = pas de titre, ex. sur /atelier).
 *   - sub     (string) : sous-titre optionnel.
 *
 * @package Alliance_Groupe
 */

$ag_atl_heading = isset( $args['heading'] ) ? (string) $args['heading'] : '';
$ag_atl_sub     = isset( $args['sub'] ) ? (string) $args['sub'] : '';
$img = get_stylesheet_directory_uri() . '/assets/images/atelier/';
$vid = get_stylesheet_directory_uri() . '/assets/videos/';

$cards = array(
	array( 'title' => 'Devis instantané',   'desc' => 'Décris ton projet, l\'IA te chiffre en 30 secondes.', 'img' => 'devis.webp',      'url' => home_url( '/devis-instant' ),       'cat' => 'ia',       'badge' => 'IA' ),
	array( 'title' => 'Refais mon site',    'desc' => 'Colle ton URL : l\'IA génère une maquette modernisée.', 'img' => 'refais.webp',   'url' => home_url( '/refais-mon-site' ),     'cat' => 'ia',       'badge' => 'IA' ),
	array( 'title' => 'Fait par l\'IA',     'desc' => 'Le journal en direct de ce que l\'IA fait pour toi.', 'img' => 'ia.webp',         'url' => home_url( '/fait-par-lia' ),        'cat' => 'ia',       'badge' => 'IA' ),
	array( 'title' => 'Studio créatif',     'desc' => 'Crée des vidéos & visuels prêts à publier.',        'img' => 'studio.webp',     'url' => home_url( '/studio' ),              'cat' => 'creer',    'badge' => 'Gratuit' ),
	array( 'title' => 'Création de sites',  'desc' => 'Ton site pro, sécurisé, livré en quelques jours.',  'img' => 'sites.webp',      'url' => home_url( '/sites-express' ),       'cat' => 'creer',    'badge' => 'Dès 490 €' ),
	array( 'title' => 'Audit de sécurité',  'desc' => 'Teste ton site : note /100 + failles détectées.',   'img' => 'securite.webp',   'url' => home_url( '/tester-mon-site' ),     'cat' => 'securite', 'badge' => 'Gratuit', 'vid' => 'embleme-securite-tuile' ),
	array( 'title' => 'Composants web',     'desc' => 'Boutons & effets à copier ou télécharger.',         'img' => 'composants.webp', 'url' => home_url( '/composants' ),          'cat' => 'creer',    'badge' => 'Gratuit' ),
	array( 'title' => 'Templates WordPress','desc' => '6 thèmes métier gratuits, prêts à installer.',       'img' => 'templates.webp',  'url' => home_url( '/templates-wordpress' ), 'cat' => 'creer',    'badge' => 'Gratuit' ),
);

$filters = array(
	'all'      => 'Tous',
	'ia'       => 'IA',
	'creer'    => 'Créer',
	'securite' => 'Sécurité',
);
$icons = array(
	'Devis instantané'    => '',
	'Refais mon site'     => '',
	'Fait par l\'IA'      => '',
	'Studio créatif'      => '',
	'Création de sites'   => '',
	'Audit de sécurité'   => '',
	'Composants web'      => '',
	'Templates WordPress' => '',
);
?>
<style>
.ag-atl{--gold:#d4b45c;--gold2:#f4d06f;position:relative;z-index:2;background:#07070c;color:#eef1f6;padding:clamp(48px,7vw,90px) 0 clamp(56px,9vw,110px);}
.ag-atl__head{text-align:center;padding:0 20px;margin-bottom:26px;}
.ag-atl__eyebrow{display:inline-block;font-size:.78rem;letter-spacing:.22em;text-transform:uppercase;color:var(--gold);border:1px solid rgba(212,180,92,.35);border-radius:100px;padding:6px 16px;margin-bottom:16px;}
.ag-atl__h{font-size:clamp(1.7rem,4.6vw,3rem);line-height:1.08;font-weight:800;margin:0 auto 12px;max-width:18ch;text-wrap:balance;background:linear-gradient(120deg,#fff 30%,var(--gold2));-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;}
.ag-atl__sub{color:#aab3c5;max-width:60ch;margin:0 auto 4px;font-size:clamp(1rem,2.2vw,1.15rem);line-height:1.55;}
.ag-atl__filters{display:flex;flex-wrap:wrap;gap:10px;justify-content:center;margin:22px auto 0;padding:0 20px;}
.ag-atl__pill{cursor:pointer;border:1px solid rgba(255,255,255,.14);background:rgba(255,255,255,.04);color:#cfd6e4;border-radius:100px;padding:9px 18px;font-size:.92rem;font-weight:600;transition:.2s;}
.ag-atl__pill:hover{border-color:var(--gold);color:#fff;}
.ag-atl__pill.on{background:linear-gradient(120deg,var(--gold),var(--gold2));color:#1a1206;border-color:transparent;}
.ag-atl__grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:22px;max-width:1240px;margin:30px auto 0;padding:0 20px;}
.ag-atl__card{position:relative;display:block;text-decoration:none;color:inherit;border-radius:18px;overflow:hidden;background:#0e0e16;border:1px solid rgba(255,255,255,.08);opacity:0;transform:translateY(26px);transition:opacity .6s ease,transform .6s ease,border-color .3s,box-shadow .3s;}
.ag-atl__card.in{opacity:1;transform:none;}
.ag-atl__card:hover{border-color:rgba(212,180,92,.6);box-shadow:0 20px 50px -20px rgba(212,180,92,.5);}
.ag-atl__media{position:relative;aspect-ratio:16/9;overflow:hidden;}
.ag-atl__media img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .6s ease;}
.ag-atl__card:hover .ag-atl__media img{transform:scale(1.07);}
.ag-atl__vid{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;display:block;opacity:0;transition:opacity .7s ease;}
.ag-atl__vid[data-ok]{opacity:1;}
@media(prefers-reduced-motion:reduce){.ag-atl__vid{display:none;}}
.ag-atl__media::after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,transparent 40%,rgba(7,7,12,.85));}
.ag-atl__badge{position:absolute;top:12px;left:12px;z-index:2;font-size:.72rem;font-weight:700;letter-spacing:.04em;background:rgba(7,7,12,.7);border:1px solid rgba(212,180,92,.5);color:var(--gold2);border-radius:100px;padding:4px 11px;-webkit-backdrop-filter:blur(4px);backdrop-filter:blur(4px);}
.ag-atl__ic{position:absolute;top:10px;right:14px;z-index:2;font-size:2rem;line-height:1;filter:drop-shadow(0 4px 12px rgba(0,0,0,.7));transition:transform .3s ease;}
.ag-atl__card:hover .ag-atl__ic{transform:scale(1.15) rotate(-8deg);}
.ag-atl__grid{perspective:1100px;}
.ag-atl__body{padding:16px 18px 20px;}
.ag-atl__ctitle{font-size:1.18rem;font-weight:700;margin:0 0 5px;}
.ag-atl__cdesc{color:#9aa4b6;font-size:.9rem;line-height:1.45;margin:0 0 12px;min-height:2.6em;}
.ag-atl__go{display:inline-flex;align-items:center;gap:6px;font-weight:700;font-size:.92rem;color:var(--gold2);transition:gap .25s;}
.ag-atl__card:hover .ag-atl__go{gap:12px;}
.ag-atl__more{text-align:center;margin-top:34px;}
.ag-atl__more a{display:inline-block;background:linear-gradient(120deg,var(--gold),var(--gold2));color:#1a1206;font-weight:800;text-decoration:none;border-radius:100px;padding:14px 32px;font-size:1rem;box-shadow:0 12px 40px -12px rgba(212,180,92,.6);transition:transform .2s;}
.ag-atl__more a:hover{transform:translateY(-2px);}
.ag-atl__empty{grid-column:1/-1;text-align:center;color:#8a93a6;padding:40px;display:none;}
@media(prefers-reduced-motion:reduce){.ag-atl__card{transition:none;opacity:1;transform:none}}
</style>

<section class="ag-atl">
	<?php if ( '' !== $ag_atl_heading ) : ?>
	<div class="ag-atl__head">
		<span class="ag-atl__eyebrow">Alliance Groupe · Web · Sécurité · IA</span>
		<h2 class="ag-atl__h"><?php echo esc_html( $ag_atl_heading ); ?></h2>
		<?php if ( '' !== $ag_atl_sub ) : ?><p class="ag-atl__sub"><?php echo esc_html( $ag_atl_sub ); ?></p><?php endif; ?>
	</div>
	<?php endif; ?>

	<div class="ag-atl__filters" id="ag-atl-filters">
		<?php $first = true; foreach ( $filters as $key => $label ) : ?>
			<button type="button" class="ag-atl__pill<?php echo $first ? ' on' : ''; ?>" data-f="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></button>
		<?php $first = false; endforeach; ?>
	</div>

	<div class="ag-atl__grid" id="ag-atl-grid">
		<?php foreach ( $cards as $c ) : ?>
			<a class="ag-atl__card" href="<?php echo esc_url( $c['url'] ); ?>" data-cat="<?php echo esc_attr( $c['cat'] ); ?>">
				<div class="ag-atl__media">
					<span class="ag-atl__badge"><?php echo esc_html( $c['badge'] ); ?></span>
					<span class="ag-atl__ic"><?php echo isset( $icons[ $c['title'] ] ) ? $icons[ $c['title'] ] : ''; ?></span>
					<img src="<?php echo esc_url( $img . $c['img'] ); ?>" alt="<?php echo esc_attr( $c['title'] ); ?>" loading="lazy" width="1000" height="563">
					<?php if ( ! empty( $c['vid'] ) ) : ?>
						<video class="ag-atl__vid" muted loop playsinline preload="none"
						       data-webm="<?php echo esc_url( $vid . $c['vid'] . '.webm' ); ?>"
						       data-mp4="<?php echo esc_url( $vid . $c['vid'] . '.mp4' ); ?>"
						       aria-hidden="true"></video>
					<?php endif; ?>
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

	<div class="ag-atl__more">
		<a href="<?php echo esc_url( home_url( '/devis-instant' ) ); ?>">Lancer mon projet — devis instantané</a>
	</div>
</section>

<script>
(function(){
	var grid = document.getElementById('ag-atl-grid');
	if(!grid || grid.dataset.agInit) return;
	grid.dataset.agInit = '1';
	var cards = [].slice.call(grid.querySelectorAll('.ag-atl__card'));
	var empty = document.getElementById('ag-atl-empty');
	if('IntersectionObserver' in window){
		var io = new IntersectionObserver(function(ents){
			ents.forEach(function(e){ if(e.isIntersecting){ e.target.classList.add('in'); io.unobserve(e.target); } });
		}, {threshold:.12});
		cards.forEach(function(c){ io.observe(c); });
	} else { cards.forEach(function(c){ c.classList.add('in'); }); }
	// Effet 3D (tilt) au survol des vignettes
	if(window.matchMedia && window.matchMedia('(pointer:fine)').matches){
		cards.forEach(function(card){
			card.addEventListener('mousemove', function(e){
				var r = card.getBoundingClientRect();
				var x = (e.clientX - r.left)/r.width - .5, y = (e.clientY - r.top)/r.height - .5;
				card.style.transition = 'transform .08s ease-out';
				card.style.transform = 'perspective(900px) rotateY('+(x*8)+'deg) rotateX('+(-y*8)+'deg) translateY(-5px)';
			});
			card.addEventListener('mouseleave', function(){ card.style.transition = ''; card.style.transform = ''; });
		});
	}
	var fbar = document.getElementById('ag-atl-filters');
	if(fbar) fbar.addEventListener('click', function(ev){
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

	/* La tuile animee ne se telecharge que si elle entre a l'ecran, et elle
	   se met en pause des qu'elle en sort : une video de 400 Ko qui tourne
	   dans le vide, c'est de la batterie et des donnees pour rien. */
	var vids = document.querySelectorAll('.ag-atl__vid');
	if (vids.length && !matchMedia('(prefers-reduced-motion:reduce)').matches) {
		var voir = new IntersectionObserver(function(entrees){
			entrees.forEach(function(e){
				var v = e.target;
				if (e.isIntersecting) {
					if (!v.dataset.charge) {
						v.dataset.charge = '1';
						['webm', 'mp4'].forEach(function(k){
							var u = v.dataset[k]; if (!u) { return; }
							var s = document.createElement('source');
							s.src = u; s.type = 'video/' + k;
							v.appendChild(s);
						});
						v.load();
					}
					var j = v.play();
					if (j && j.catch) { j.catch(function(){}); }
					v.setAttribute('data-ok', '');
				} else {
					v.pause();
				}
			});
		}, { threshold: 0.25 });
		vids.forEach(function(v){ voir.observe(v); });
	}
})();
</script>
