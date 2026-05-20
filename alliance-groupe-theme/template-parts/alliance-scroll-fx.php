<?php
/**
 * Template part : Full Screen Scroll FX — section scroll-jacking GSAP
 * style Apple AirPods.
 *
 * Port vanilla JS + CSS du composant React 21st.dev `FullScreenScrollFX`
 * (https://21st.dev) adapté pour WordPress / PHP / vanilla.
 *
 * Dépendances chargées via CDN (auto-inline ici) :
 *   - gsap 3.13
 *   - ScrollTrigger plugin
 *
 * 6 sections = 6 templates métier Alliance Groupe.
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// 6 métiers — photos Unsplash spécifiques à CHAQUE métier (pas générique)
$ag_fx_sections = array(
	array(
		'id'    => 'coach',
		'left'  => 'Bien-être',
		'title' => 'Coach',
		'right' => 'Performance',
		// Femme coach en discussion avec un client (séance réelle)
		'bg'    => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=1920&q=85',
		'link'  => home_url( '/wordpress-coach' ),
	),
	array(
		'id'    => 'artisan',
		'left'  => 'Savoir-faire',
		'title' => 'Artisan',
		'right' => 'Métier',
		// Artisan menuisier mains au travail dans atelier
		'bg'    => 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=1920&q=85',
		'link'  => home_url( '/wordpress-artisan' ),
	),
	array(
		'id'    => 'avocat',
		'left'  => 'Conseil',
		'title' => 'Avocat',
		'right' => 'Justice',
		// Sculpture Justice + bibliothèque juridique
		'bg'    => 'https://images.unsplash.com/photo-1589994965851-a8f479c573a9?w=1920&q=85',
		'link'  => home_url( '/wordpress-avocat' ),
	),
	array(
		'id'    => 'barber',
		'left'  => 'Style',
		'title' => 'Barber',
		'right' => 'Élégance',
		// Barbier en action coupe avec rasoir
		'bg'    => 'https://images.unsplash.com/photo-1622286342621-4bd786c2447c?w=1920&q=85',
		'link'  => home_url( '/wordpress-barber' ),
	),
	array(
		'id'    => 'restaurant',
		'left'  => 'Gastronomie',
		'title' => 'Restaurant',
		'right' => 'Expérience',
		// Intérieur restaurant chic warm light
		'bg'    => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=1920&q=85',
		'link'  => home_url( '/wordpress-restaurant' ),
	),
	array(
		'id'    => 'association',
		'left'  => 'Engagement',
		'title' => 'Association',
		'right' => 'Communauté',
		// Bénévoles mains tendues (réelle action associative)
		'bg'    => 'https://images.unsplash.com/photo-1559027615-cd4628902d4a?w=1920&q=85',
		'link'  => home_url( '/wordpress-association' ),
	),
);

$ag_fx_total = count( $ag_fx_sections );
$ag_fx_uid   = 'agfx-' . wp_rand( 1000, 9999 );
?>

<section class="ag-fx" id="<?php echo esc_attr( $ag_fx_uid ); ?>" aria-label="Templates Alliance Groupe — défilement plein écran">
	<div class="ag-fx-fixed-section">
		<div class="ag-fx-fixed">
			<!-- Backgrounds -->
			<div class="ag-fx-bgs" aria-hidden="true">
				<?php foreach ( $ag_fx_sections as $i => $s ) : ?>
					<div class="ag-fx-bg">
						<img class="ag-fx-bg-img" data-bg-index="<?php echo (int) $i; ?>" src="<?php echo esc_url( $s['bg'] ); ?>" alt="">
						<div class="ag-fx-bg-overlay"></div>
					</div>
				<?php endforeach; ?>
			</div>

			<!-- Grid -->
			<div class="ag-fx-grid">
				<!-- Header -->
				<div class="ag-fx-header">
					<div>Templates</div>
					<div>WordPress</div>
				</div>

				<!-- Content : labels left / center title / labels right -->
				<div class="ag-fx-content">
					<!-- Left labels -->
					<div class="ag-fx-left" role="list">
						<div class="ag-fx-track" data-track="left">
							<?php foreach ( $ag_fx_sections as $i => $s ) : ?>
								<div class="ag-fx-item ag-fx-left-item <?php echo $i === 0 ? 'active' : ''; ?>"
								     data-jump="<?php echo (int) $i; ?>"
								     role="button" tabindex="0">
									<?php echo esc_html( $s['left'] ); ?>
								</div>
							<?php endforeach; ?>
						</div>
					</div>

					<!-- Center title -->
					<div class="ag-fx-center">
						<?php foreach ( $ag_fx_sections as $i => $s ) : ?>
							<div class="ag-fx-featured <?php echo $i === 0 ? 'active' : ''; ?>" data-featured-index="<?php echo (int) $i; ?>">
								<h3 class="ag-fx-featured-title" data-title="<?php echo esc_attr( $s['title'] ); ?>">
									<?php
									$words = preg_split( '/\s+/', $s['title'] );
									foreach ( $words as $wi => $word ) :
										?>
										<span class="ag-fx-word-mask"><span class="ag-fx-word"><?php echo esc_html( $word ); ?></span></span><?php echo $wi < count( $words ) - 1 ? ' ' : ''; ?>
									<?php endforeach; ?>
								</h3>
								<a class="ag-fx-cta" href="<?php echo esc_url( $s['link'] ); ?>">Découvrir →</a>
							</div>
						<?php endforeach; ?>
					</div>

					<!-- Right labels -->
					<div class="ag-fx-right" role="list">
						<div class="ag-fx-track" data-track="right">
							<?php foreach ( $ag_fx_sections as $i => $s ) : ?>
								<div class="ag-fx-item ag-fx-right-item <?php echo $i === 0 ? 'active' : ''; ?>"
								     data-jump="<?php echo (int) $i; ?>"
								     role="button" tabindex="0">
									<?php echo esc_html( $s['right'] ); ?>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>

				<!-- Footer + progress -->
				<div class="ag-fx-footer">
					<div class="ag-fx-footer-title">Choisissez votre métier</div>
					<div class="ag-fx-progress">
						<div class="ag-fx-progress-numbers">
							<span class="ag-fx-current">01</span>
							<span><?php echo esc_html( str_pad( $ag_fx_total, 2, '0', STR_PAD_LEFT ) ); ?></span>
						</div>
						<div class="ag-fx-progress-bar">
							<div class="ag-fx-progress-fill"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

</section>

<!-- (Le bloc CTA 'Téléchargez votre template' a été extrait dans
     template-parts/templates-cta.php pour pouvoir être inséré AVANT
     le scroll-fx au lieu d'après — cf page-accueil.php) -->

<style>
	#<?php echo esc_attr( $ag_fx_uid ); ?>{
		--fx-text:rgba(245,245,245,0.92);
		--fx-overlay:rgba(0,0,0,0.30);
		--fx-page-bg:#ffffff;
		--fx-stage-bg:#0a0a0f;
		--fx-accent:#F37A1F;
		--fx-gap:1rem;
		--fx-grid-px:2rem;
		--fx-row-gap:10px;
		width:100%;overflow:hidden;background:var(--fx-page-bg);color:#000;
		font-family:'Manrope','Rubik Wide',system-ui,-apple-system,'Segoe UI',Roboto,Arial,sans-serif;
		text-transform:uppercase;letter-spacing:-0.02em;
	}
	#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-fixed-section{height:<?php echo (int) ( $ag_fx_total + 1 ); ?>00vh;position:relative;z-index:10}
	#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-fixed{position:sticky;top:0;height:100vh;width:100%;overflow:hidden;background:var(--fx-stage-bg);z-index:11}
	#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-grid{display:grid;grid-template-columns:repeat(12,1fr);gap:var(--fx-gap);padding:0 var(--fx-grid-px);position:relative;height:100%;z-index:2}
	#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-bgs{position:absolute;inset:0;background:var(--fx-stage-bg);z-index:1}
	#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-bg{position:absolute;inset:0}
	#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-bg-img{position:absolute;inset:-10% 0 -10% 0;width:100%;height:120%;object-fit:cover;filter:brightness(0.95) contrast(1.02);opacity:0;will-change:transform,opacity}
	/* Fallback CSS si GSAP n'a pas chargé : montre au moins la 1re image */
	#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-bg-img[data-bg-index="0"]{opacity:1}
	/* Fallback featured : 1re section visible si JS désactivé */
	#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-featured[data-featured-index="0"]{opacity:1;visibility:visible}
	/* S'assure que tout passe AU-DESSUS des sections suivantes (parallax etc.) */
	#<?php echo esc_attr( $ag_fx_uid ); ?> *{box-sizing:border-box}
	#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-bg-overlay{position:absolute;inset:0;background:var(--fx-overlay)}
	#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-header{grid-column:1/13;align-self:start;padding-top:5vh;font-size:clamp(0.7rem,1.1vw,1rem);line-height:1.2;text-align:center;color:var(--fx-text);font-weight:700;letter-spacing:6px;position:relative;z-index:3;opacity:.55;text-transform:uppercase}
	#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-header>*{display:inline}
	#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-header>*:first-child::after{content:" · ";opacity:.5}
	#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-content{grid-column:1/13;position:absolute;inset:0;display:grid;grid-template-columns:1fr 1.3fr 1fr;align-items:center;height:100%;padding:0 var(--fx-grid-px);z-index:2}
	#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-left,#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-right{height:60vh;overflow:hidden;display:grid;align-content:center}
	#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-left{justify-items:start}
	#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-right{justify-items:end}
	#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-track{will-change:transform}
	#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-item{color:var(--fx-text);font-weight:800;letter-spacing:0;line-height:1;margin:calc(var(--fx-row-gap)/2) 0;opacity:0.35;transition:opacity 0.3s ease,transform 0.3s ease;position:relative;font-size:clamp(1rem,2.4vw,1.8rem);user-select:none;cursor:pointer}
	#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-left-item.active,#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-right-item.active{opacity:1}
	#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-left-item.active{transform:translateX(10px);padding-left:16px}
	#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-right-item.active{transform:translateX(-10px);padding-right:16px}
	#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-left-item.active::before,#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-right-item.active::after{content:"";position:absolute;top:50%;transform:translateY(-50%);width:8px;height:8px;background:var(--fx-accent);border-radius:50%;box-shadow:0 0 12px var(--fx-accent)}
	#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-left-item.active::before{left:0}
	#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-right-item.active::after{right:0}
	#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-center{display:grid;place-items:center;text-align:center;height:60vh;overflow:hidden;position:relative}
	#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-featured{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);opacity:0;visibility:hidden;display:flex;flex-direction:column;align-items:center;gap:24px;width:100%;pointer-events:none}
	#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-featured.active{opacity:1;visibility:visible;pointer-events:auto}
	#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-featured-title{margin:0;color:var(--fx-text);font-weight:900;letter-spacing:-0.01em;font-size:clamp(2rem,5.5vw,4.5rem);line-height:1;max-width:90vw;word-break:break-word;hyphens:auto;text-shadow:0 4px 30px rgba(0,0,0,.6),0 2px 10px rgba(0,0,0,.5)}
	#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-word-mask{display:inline-block;overflow:hidden;vertical-align:middle}
	#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-word{display:inline-block;vertical-align:middle}
	#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-cta{display:inline-block;padding:14px 28px;background:var(--fx-accent);color:#fff;font-weight:800;font-size:0.85rem;letter-spacing:2px;border-radius:999px;text-decoration:none;text-transform:uppercase;box-shadow:0 8px 24px rgba(243,122,31,0.4);transition:transform 0.2s,box-shadow 0.2s;opacity:0;transform:translateY(20px)}
	#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-featured.active .ag-fx-cta{opacity:1;transform:translateY(0);transition:transform 0.6s ease 0.3s,opacity 0.6s ease 0.3s,box-shadow 0.2s}
	#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-cta:hover{transform:translateY(-2px);box-shadow:0 12px 32px rgba(243,122,31,0.55);color:#fff;text-decoration:none}
	#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-footer{grid-column:1/13;align-self:end;padding-bottom:5vh;text-align:center;position:relative;z-index:3}
	#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-footer-title{color:var(--fx-text);font-size:clamp(1.2rem,3vw,2rem);font-weight:700;letter-spacing:3px;line-height:0.9;text-transform:uppercase}
	#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-progress{width:200px;height:2px;margin:1.5rem auto 0;background:rgba(245,245,245,0.28);position:relative}
	#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-progress-fill{position:absolute;inset:0 auto 0 0;width:0%;background:var(--fx-accent);height:100%;transition:width 0.4s ease;box-shadow:0 0 12px var(--fx-accent)}
	#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-progress-numbers{position:absolute;inset:auto 0 100% 0;display:flex;justify-content:space-between;font-size:0.8rem;color:var(--fx-text);margin-bottom:8px;font-weight:700;letter-spacing:1px}
	@media (max-width:900px){
		#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-content{grid-template-columns:1fr;row-gap:3vh;place-items:center}
		#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-left,#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-right{height:auto;display:none}
		#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-center{height:auto;min-height:55vh;width:100%;display:grid;place-items:center;position:relative;}
	}
	/* ── Mobile fix (<=768px) : section pas trop haute + textes lisibles ── */
	@media (max-width:768px){
		#<?php echo esc_attr( $ag_fx_uid ); ?>{--fx-grid-px:1rem;--fx-overlay:rgba(0,0,0,0.45);}
		/* Sticky en svh (small viewport height) = sans la barre Safari mobile */
		#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-fixed{height:100svh;}
		/* Center cell : doit avoir une hauteur reelle pour que le featured (absolute)
		   se positionne correctement. min-height + flex centrage = featured visible */
		#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-content{align-items:stretch;}
		#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-center{min-height:50vh;align-self:center;}
		/* Featured : passe en static (flow) au lieu d'absolute pour s'afficher
		   meme si parent n'a pas de hauteur exploitable */
		#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-featured{position:absolute;inset:auto;top:50%;left:50%;transform:translate(-50%,-50%);width:100%;gap:18px;}
		/* Header (TEMPLATES · WORDPRESS) : evite collision avec burger top */
		#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-header{padding-top:140px;font-size:.7rem;letter-spacing:3px;opacity:.7;}
		/* Featured title centre : plus gros + ombre forte pour lisibilité */
		#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-featured-title{font-size:clamp(2.4rem,11vw,3.6rem);text-shadow:0 4px 24px rgba(0,0,0,.7),0 2px 8px rgba(0,0,0,.5);max-width:92vw;}
		#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-cta{padding:12px 24px;font-size:.78rem;letter-spacing:1.5px;}
		/* Footer "CHOISISSEZ VOTRE MÉTIER" : letter-spacing tue le texte sur 390px */
		#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-footer{padding-bottom:24px;padding-left:16px;padding-right:16px;}
		#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-footer-title{font-size:.78rem;letter-spacing:1.5px;line-height:1.4;margin-bottom:28px;}
		/* Numbers (01/06) sous la barre au lieu d'au-dessus pour ne pas chevaucher le titre */
		#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-progress{width:160px;margin-top:8px;}
		#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-progress-numbers{font-size:.7rem;inset:100% 0 auto 0;margin-top:8px;margin-bottom:0;}
		/* Force les mots du titre featured a etre visibles (GSAP les met a yPercent:100
		   par defaut sur sections >0, et si l'animation rate sur mobile = invisible) */
		#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-word{transform:translate3d(0,0,0) !important;opacity:1 !important;}
		#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-word-mask{overflow:visible;}
		/* CTA du featured : visible aussi (etait opacity:0 par defaut, anime via .active) */
		#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-featured.active .ag-fx-cta{opacity:1 !important;transform:translateY(0) !important;}
	}
	/* Petit mobile (<=480px) : encore plus serre */
	@media (max-width:480px){
		#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-header{padding-top:130px;font-size:.65rem;letter-spacing:2.5px;}
		#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-featured-title{font-size:clamp(2rem,12vw,3rem);}
		#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-footer-title{font-size:.72rem;letter-spacing:1px;margin-bottom:22px;}
		#<?php echo esc_attr( $ag_fx_uid ); ?> .ag-fx-progress{width:140px;}
	}
	/* Cache le back-to-top tant qu'on est dans la section scroll-fx mobile
	   pour eviter qu'il chevauche le titre footer */
	@media (max-width:768px){
		body.ag-in-scroll-fx .ag-totop{opacity:0 !important;pointer-events:none !important;visibility:hidden !important;}
	}
</style>

<script>
(function(){
	// Charge GSAP + ScrollTrigger via CDN si pas déjà présent
	function loadScript(src, cb){
		if (document.querySelector('script[src="'+src+'"]')) { cb && cb(); return; }
		var s = document.createElement('script');
		s.src = src; s.async = true; s.onload = cb;
		document.head.appendChild(s);
	}
	function init(){
		var ROOT = document.getElementById('<?php echo esc_js( $ag_fx_uid ); ?>');
		if (!ROOT || typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') return;
		gsap.registerPlugin(ScrollTrigger);

		var TOTAL = <?php echo (int) $ag_fx_total; ?>;
		var D = 0.7;       // durée animation
		var SNAP = 800;    // ms snap

		var fixedSection = ROOT.querySelector('.ag-fx-fixed-section');
		var fixed = ROOT.querySelector('.ag-fx-fixed');
		var bgs = ROOT.querySelectorAll('.ag-fx-bg-img');
		var featureds = ROOT.querySelectorAll('.ag-fx-featured');
		var leftItems = ROOT.querySelectorAll('.ag-fx-left-item');
		var rightItems = ROOT.querySelectorAll('.ag-fx-right-item');
		var leftTrack = ROOT.querySelector('[data-track="left"]');
		var rightTrack = ROOT.querySelector('[data-track="right"]');
		var progressFill = ROOT.querySelector('.ag-fx-progress-fill');
		var currentNum = ROOT.querySelector('.ag-fx-current');

		var lastIndex = 0;
		var isAnimating = false;
		var isSnapping = false;
		var sectionTops = [];
		var prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

		// État initial
		gsap.set(bgs, { opacity: 0, scale: 1.04, yPercent: 0 });
		if (bgs[0]) gsap.set(bgs[0], { opacity: 1, scale: 1 });

		// Init words (cachés sauf section active)
		featureds.forEach(function(feat, sIdx){
			var words = feat.querySelectorAll('.ag-fx-word');
			words.forEach(function(w){
				gsap.set(w, { yPercent: sIdx === 0 ? 0 : 100, opacity: sIdx === 0 ? 1 : 0 });
			});
		});

		function computePositions(){
			var top = fixedSection.offsetTop;
			var h = fixedSection.offsetHeight;
			sectionTops = [];
			for (var i = 0; i < TOTAL; i++) sectionTops.push(top + (h * i) / TOTAL);
		}

		function centerTrack(track, items, isRight, toIndex, animate){
			if (!track || items.length === 0) return;
			var container = track.parentElement;
			var first = items[0];
			var second = items[1];
			var contRect = container.getBoundingClientRect();
			var rowH = first.getBoundingClientRect().height;
			if (second) rowH = second.getBoundingClientRect().top - first.getBoundingClientRect().top;
			var targetY = contRect.height / 2 - rowH / 2 - toIndex * rowH;
			if (animate) gsap.to(track, { y: targetY, duration: D * 0.9, ease: 'power3.out' });
			else gsap.set(track, { y: targetY });
		}

		function measureAndCenter(toIndex, animate){
			requestAnimationFrame(function(){
				requestAnimationFrame(function(){
					centerTrack(leftTrack, leftItems, false, toIndex, animate);
					centerTrack(rightTrack, rightItems, true, toIndex, animate);
				});
			});
		}

		function changeSection(to){
			if (to === lastIndex || isAnimating) return;
			var from = lastIndex;
			var down = to > from;
			isAnimating = true;

			// progress
			if (currentNum) currentNum.textContent = String(to + 1).padStart(2, '0');
			if (progressFill) progressFill.style.width = ((to / (TOTAL - 1 || 1)) * 100) + '%';

			// featured active class
			featureds.forEach(function(f, i){ f.classList.toggle('active', i === to); });

			// Mots animés
			var outWords = featureds[from].querySelectorAll('.ag-fx-word');
			var inWords = featureds[to].querySelectorAll('.ag-fx-word');
			gsap.to(outWords, { yPercent: down ? -100 : 100, opacity: 0, duration: D * 0.6, stagger: down ? 0.03 : -0.03, ease: 'power3.out' });
			gsap.set(inWords, { yPercent: down ? 100 : -100, opacity: 0 });
			gsap.to(inWords, { yPercent: 0, opacity: 1, duration: D, stagger: down ? 0.05 : -0.05, ease: 'power3.out' });

			// Backgrounds fade
			var prevBg = bgs[from];
			var newBg = bgs[to];
			if (newBg){
				gsap.set(newBg, { opacity: 0, scale: 1.04, yPercent: down ? 1 : -1 });
				gsap.to(newBg, { opacity: 1, scale: 1, yPercent: 0, duration: D, ease: 'power2.out' });
			}
			if (prevBg){
				gsap.to(prevBg, { opacity: 0, yPercent: down ? -4 : 4, duration: D, ease: 'power2.out' });
			}

			// Listes
			measureAndCenter(to, true);
			leftItems.forEach(function(el, i){
				el.classList.toggle('active', i === to);
				gsap.to(el, { opacity: i === to ? 1 : 0.35, x: i === to ? 10 : 0, duration: D * 0.6, ease: 'power3.out' });
			});
			rightItems.forEach(function(el, i){
				el.classList.toggle('active', i === to);
				gsap.to(el, { opacity: i === to ? 1 : 0.35, x: i === to ? -10 : 0, duration: D * 0.6, ease: 'power3.out' });
			});

			gsap.delayedCall(D, function(){
				lastIndex = to;
				isAnimating = false;
			});
		}

		function goTo(to, withScroll){
			var clamped = Math.max(0, Math.min(TOTAL - 1, to));
			isSnapping = true;
			changeSection(clamped);
			var pos = sectionTops[clamped];
			if (withScroll !== false && pos !== undefined){
				window.scrollTo({ top: pos, behavior: 'smooth' });
				setTimeout(function(){ isSnapping = false; }, SNAP);
			} else {
				setTimeout(function(){ isSnapping = false; }, 10);
			}
		}

		computePositions();
		measureAndCenter(0, false);

		ScrollTrigger.create({
			trigger: fixedSection,
			start: 'top top',
			end: 'bottom bottom',
			pin: fixed,
			pinSpacing: true,
			onEnter: function(){ document.body.classList.add('ag-in-scroll-fx'); },
			onEnterBack: function(){ document.body.classList.add('ag-in-scroll-fx'); },
			onLeave: function(){ document.body.classList.remove('ag-in-scroll-fx'); },
			onLeaveBack: function(){ document.body.classList.remove('ag-in-scroll-fx'); },
			onUpdate: function(self){
				if (prefersReduced || isSnapping) return;
				var prog = self.progress;
				var target = Math.min(TOTAL - 1, Math.floor(prog * TOTAL));
				if (target !== lastIndex && !isAnimating){
					var nextIdx = lastIndex + (target > lastIndex ? 1 : -1);
					goTo(nextIdx, false);
				}
				if (progressFill){
					progressFill.style.width = ((lastIndex / (TOTAL - 1 || 1)) * 100) + '%';
				}
			}
		});

		// Resize
		var ro = new ResizeObserver(function(){
			computePositions();
			measureAndCenter(lastIndex, false);
			ScrollTrigger.refresh();
		});
		ro.observe(fixedSection);

		// Click handlers
		ROOT.querySelectorAll('[data-jump]').forEach(function(el){
			el.addEventListener('click', function(){
				goTo(parseInt(el.getAttribute('data-jump'), 10), true);
			});
			el.addEventListener('keydown', function(e){
				if (e.key === 'Enter' || e.key === ' '){
					e.preventDefault();
					goTo(parseInt(el.getAttribute('data-jump'), 10), true);
				}
			});
		});

		// Entrée des listes
		leftItems.forEach(function(el, i){
			gsap.fromTo(el, { opacity: 0, y: 20 }, { opacity: i === 0 ? 1 : 0.35, y: 0, duration: 0.5, delay: i * 0.06, ease: 'power3.out' });
		});
		rightItems.forEach(function(el, i){
			gsap.fromTo(el, { opacity: 0, y: 20 }, { opacity: i === 0 ? 1 : 0.35, y: 0, duration: 0.5, delay: 0.2 + i * 0.06, ease: 'power3.out' });
		});
	}

	loadScript('https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/gsap.min.js', function(){
		loadScript('https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/ScrollTrigger.min.js', function(){
			if (document.readyState === 'loading') {
				document.addEventListener('DOMContentLoaded', init);
			} else {
				init();
			}
		});
	});
})();
</script>
