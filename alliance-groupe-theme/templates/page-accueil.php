<?php
/**
 * Template Name: Accueil
 *
 * Accueil éditorial « scroll cinématique » : hero égérie (vidéo Naples) +
 * marquee + chapitres animés + offres/prix + galerie Atelier IA + modal devis.
 * Autonome (CSS + JS inline). Marque Alliance Groupe (or/sombre).
 *
 * @package Alliance_Groupe
 */

get_header();

$dir  = get_stylesheet_directory_uri();
$post = admin_url( 'admin-post.php' );

// Offres réelles (miroir de /sites-express).
$packs = array(
	array( 'nom' => 'Essentiel', 'prix' => '490 €',  'desc' => 'Le site vitrine qui te rend crédible.', 'feats' => array( 'One-page premium sur-mesure', 'Optimisé mobile + rapide', 'Contact + Google Maps', 'Livré en 5 jours' ), 'star' => false ),
	array( 'nom' => 'Pro',       'prix' => '890 €',  'desc' => 'Le site complet pour développer ton activité.', 'feats' => array( 'Jusqu\'à 6 pages', 'SEO optimisé Google', 'Blog + prise de RDV', 'Livré en 8 jours' ), 'star' => true ),
	array( 'nom' => 'Boutique',  'prix' => '1 490 €', 'desc' => 'Ta boutique en ligne, prête à vendre.', 'feats' => array( 'E-commerce WooCommerce', 'Jusqu\'à 30 produits', 'Paiement CB / PayPal', 'Livré en 12 jours' ), 'star' => false ),
);
$marquee = array( 'Sites sécurisés', 'Propulsé par l\'IA', 'SEO Google', 'De Naples à Nantes', 'Un seul interlocuteur', 'Livré en 5 jours', 'Design sur-mesure', 'Audit gratuit' );
$chapters = array(
	array( 'n' => '01', 't' => 'Un artisan, pas une usine', 'p' => 'De Naples à Nantes, un seul interlocuteur — du conseil à la livraison. Vous parlez directement à la personne qui fait le travail.', 'img' => 'team/fabrizio-nantes.jpg' ),
	array( 'n' => '02', 't' => 'La sécurité, incluse', 'p' => 'Chaque site est livré sécurisé — rare chez les agences classiques. On commence toujours par un audit gratuit, pour partir sur des bases saines.', 'img' => 'securite/nantes-cyber.jpg' ),
	array( 'n' => '03', 't' => 'Propulsé par l\'IA', 'p' => 'Devis en 30 secondes, maquette régénérée, contenu créé : l\'IA fait le gros du travail, vous gardez le contrôle et le style.', 'img' => 'atelier/ia.webp' ),
);
?>
<style>
.ag-lm{--gold:#d4b45c;--gold2:#f4d06f;background:#05050a;color:#eef1f6;overflow-x:hidden;}
.ag-lm *{box-sizing:border-box;}
/* HERO */
.ag-lm__hero{position:relative;min-height:100vh;min-height:100svh;display:flex;align-items:flex-end;overflow:hidden;}
/* Fond CINÉ-LOOP : slides empilés en fondu enchaîné (vidéo ⇄ photo) */
.ag-lm__hbg{position:absolute;inset:0;z-index:0;}
.ag-lm__slide{position:absolute;inset:0;opacity:0;transition:opacity 1.3s ease;background-size:cover;background-position:center 42%;will-change:opacity;}
.ag-lm__slide.is-on{opacity:1;}
.ag-lm__slide video,.ag-lm__slide img{width:100%;height:100%;object-fit:cover;object-position:center 45%;display:block;}
/* Temps vidéo : léger zoom cinéma continu (casse la boucle) */
.ag-lm__slide--vid video{animation:agKen1 22s ease-in-out infinite alternate;transform-origin:center 40%;}
/* Temps photo : la baie en fond + l'égérie détourée devant, travelling opposé */
.ag-lm__slide--photo{animation:agKen2 20s ease-in-out infinite alternate;transform-origin:60% 60%;}
.ag-lm__slidefig{position:absolute;right:clamp(-20px,3vw,80px);bottom:0;height:min(96vh,1100px);}
.ag-lm__slidefig img{height:100%;width:auto;object-fit:contain;filter:drop-shadow(0 24px 55px rgba(0,0,0,.6));}
@keyframes agKen1{from{transform:scale(1.04)}to{transform:scale(1.13)}}
@keyframes agKen2{from{transform:scale(1.08)}to{transform:scale(1.0)}}
@media(prefers-reduced-motion:reduce){.ag-lm__slide--vid video,.ag-lm__slide--photo{animation:none}}
.ag-lm__sgrad{position:absolute;inset:0;z-index:1;background:linear-gradient(180deg,rgba(5,5,10,.45),rgba(5,5,10,.08) 26%,rgba(5,5,10,.35) 66%,rgba(5,5,10,.95)),radial-gradient(115% 85% at 26% 55%,transparent 40%,rgba(5,5,10,.5));}
/* Égérie DÉTOURÉE, séparée du fond, posée devant */
.ag-lm__egerie{position:absolute;z-index:2;right:clamp(-10px,3vw,90px);bottom:0;height:min(94vh,1080px);display:block;pointer-events:none;animation:agEgIn 1s ease .1s both;}
.ag-lm__egerie img{height:100%;width:auto;display:block;filter:drop-shadow(0 24px 55px rgba(0,0,0,.6));}
@keyframes agEgIn{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:none}}
/* Badges flottants (volent + tournoient + parallaxe souris) */
.ag-lm__float{position:absolute;inset:0;z-index:2;pointer-events:none;}
.ag-lm__cw{position:absolute;will-change:transform;transition:transform .25s ease-out;}
.ag-lm__cw--1{top:16%;left:5%;}
.ag-lm__cw--2{top:12%;left:33%;}
.ag-lm__cw--3{top:30%;left:14%;}
.ag-lm__cw--4{top:26%;left:44%;}
.ag-lm__chip{display:inline-flex;align-items:center;gap:8px;background:rgba(14,14,22,.55);border:1px solid rgba(212,180,92,.55);color:var(--gold2);font-weight:700;font-size:.95rem;padding:10px 16px;border-radius:100px;-webkit-backdrop-filter:blur(6px);backdrop-filter:blur(6px);box-shadow:0 12px 34px -12px rgba(0,0,0,.7);white-space:nowrap;}
.ag-lm__cw--1 .ag-lm__chip{animation:agFlA 9s ease-in-out infinite;}
.ag-lm__cw--2 .ag-lm__chip{animation:agFlB 11s ease-in-out infinite;}
.ag-lm__cw--3 .ag-lm__chip{animation:agFlB 10s ease-in-out infinite reverse;}
.ag-lm__cw--4 .ag-lm__chip{animation:agFlA 12s ease-in-out infinite reverse;}
@keyframes agFlA{0%,100%{transform:translateY(0) rotate(-5deg)}50%{transform:translateY(-22px) rotate(6deg)}}
@keyframes agFlB{0%,100%{transform:translateY(0) rotate(4deg)}50%{transform:translateY(-16px) rotate(-6deg)}}
/* En-tête marque (coin haut gauche) */
.ag-lm__htop{position:absolute;z-index:3;top:0;left:0;right:0;padding:22px 26px;display:flex;align-items:center;gap:12px;}
.ag-lm__htop img{height:46px;width:auto;}
.ag-lm__htop .ag-lm__eyebrow{margin-left:2px;}
/* Bloc texte (bas gauche, devant le fond, à côté de l'égérie) */
.ag-lm__hin{position:relative;z-index:3;max-width:600px;margin:0;padding:0 24px clamp(46px,8vh,92px) clamp(22px,6vw,80px);text-align:left;}
.ag-lm__brand{display:none;}
.ag-lm__eyebrow{letter-spacing:.32em;text-transform:uppercase;font-size:.76rem;color:var(--gold);font-weight:700;}
.ag-lm__h1{font-size:clamp(2rem,5.2vw,3.8rem);line-height:1.06;font-weight:800;margin:.3em 0 .5em;text-wrap:balance;text-shadow:0 2px 24px rgba(0,0,0,.8);}
.ag-lm__h1 em{font-style:normal;color:var(--gold2);}
.ag-lm__hsub{color:#dbe1ee;font-size:clamp(.98rem,2.4vw,1.2rem);max-width:46ch;margin:0 0 26px;line-height:1.5;text-shadow:0 1px 14px rgba(0,0,0,.8);}
.ag-lm__cta{display:flex;flex-wrap:wrap;gap:14px;justify-content:flex-start;}
.ag-btnp{background:linear-gradient(120deg,var(--gold),var(--gold2));color:#1a1206;font-weight:800;text-decoration:none;border:0;cursor:pointer;border-radius:100px;padding:15px 30px;font-size:1rem;box-shadow:0 12px 40px -12px rgba(212,180,92,.7);transition:transform .2s;}
.ag-btnp:hover{transform:translateY(-2px);}
.ag-btno{background:rgba(255,255,255,.06);color:#fff;text-decoration:none;border:1px solid rgba(255,255,255,.2);border-radius:100px;padding:15px 28px;font-weight:700;font-size:1rem;transition:.2s;}
.ag-btno:hover{border-color:var(--gold);}
.ag-lm__scroll{position:absolute;bottom:22px;left:50%;transform:translateX(-50%);z-index:2;color:rgba(255,255,255,.6);font-size:.8rem;letter-spacing:.2em;animation:agBob 2s ease-in-out infinite;}
@keyframes agBob{0%,100%{transform:translate(-50%,0)}50%{transform:translate(-50%,8px)}}
/* MARQUEE */
.ag-lm__mq{border-top:1px solid rgba(212,180,92,.2);border-bottom:1px solid rgba(212,180,92,.2);background:#0a0a12;overflow:hidden;padding:16px 0;white-space:nowrap;}
.ag-lm__mqin{display:inline-flex;gap:36px;animation:agMq 26s linear infinite;}
.ag-lm__mqin span{font-size:1.05rem;font-weight:700;color:#e9edf5;}
.ag-lm__mqin b{color:var(--gold);margin-right:36px;}
@keyframes agMq{from{transform:translateX(0)}to{transform:translateX(-50%)}}
/* CHAPTERS */
.ag-lm__chapters{max-width:1180px;margin:0 auto;padding:clamp(60px,10vw,120px) 20px;}
.ag-lm__chap{display:grid;grid-template-columns:1fr 1fr;gap:clamp(24px,5vw,64px);align-items:center;margin-bottom:clamp(48px,8vw,96px);}
.ag-lm__chap:nth-child(even) .ag-lm__chtext{order:2;}
.ag-lm__chimg{border-radius:20px;overflow:hidden;aspect-ratio:4/3;border:1px solid rgba(255,255,255,.08);}
.ag-lm__chimg img{width:100%;height:100%;object-fit:cover;display:block;transition:transform 1.2s ease;}
.ag-lm__chap:hover .ag-lm__chimg img{transform:scale(1.05);}
.ag-lm__chn{font-size:.9rem;font-weight:800;letter-spacing:.2em;color:var(--gold);}
.ag-lm__cht{font-size:clamp(1.7rem,4vw,2.8rem);font-weight:800;line-height:1.08;margin:8px 0 14px;}
.ag-lm__chp{color:#aeb6c6;font-size:1.08rem;line-height:1.6;max-width:46ch;}
/* OFFRES */
.ag-lm__offres{max-width:1180px;margin:0 auto;padding:0 20px clamp(60px,10vw,110px);}
.ag-lm__stitle{text-align:center;font-size:clamp(1.8rem,4.6vw,3rem);font-weight:800;margin:0 0 6px;}
.ag-lm__sdesc{text-align:center;color:#aeb6c6;margin:0 auto 40px;max-width:52ch;}
.ag-lm__packs{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:22px;}
.ag-lm__pack{position:relative;background:#0e0e16;border:1px solid rgba(255,255,255,.09);border-radius:20px;padding:30px 26px;display:flex;flex-direction:column;}
.ag-lm__pack.star{border-color:var(--gold);box-shadow:0 24px 60px -30px rgba(212,180,92,.6);}
.ag-lm__pstar{position:absolute;top:-13px;left:50%;transform:translateX(-50%);background:linear-gradient(120deg,var(--gold),var(--gold2));color:#1a1206;font-weight:800;font-size:.75rem;padding:5px 14px;border-radius:100px;letter-spacing:.05em;}
.ag-lm__pnom{font-size:1.3rem;font-weight:800;}
.ag-lm__pprix{font-size:2.6rem;font-weight:800;color:var(--gold2);margin:6px 0 2px;}
.ag-lm__pdesc{color:#9aa4b6;font-size:.95rem;margin:0 0 16px;min-height:2.6em;}
.ag-lm__pfeats{list-style:none;margin:0 0 22px;padding:0;flex:1;}
.ag-lm__pfeats li{padding:7px 0;border-bottom:1px solid rgba(255,255,255,.06);color:#cfd6e4;font-size:.95rem;}
.ag-lm__pfeats li::before{content:"✓ ";color:var(--gold);font-weight:800;}
.ag-lm__maint{text-align:center;color:#9aa4b6;margin-top:26px;font-size:.98rem;}
.ag-lm__maint a{color:var(--gold2);}
/* REVEAL */
.ag-rv{opacity:0;transform:translateY(30px);transition:opacity .7s ease,transform .7s ease;}
.ag-rv.in{opacity:1;transform:none;}
/* MODAL */
.ag-md{position:fixed;inset:0;z-index:9999;display:none;align-items:center;justify-content:center;padding:20px;background:rgba(3,3,7,.82);-webkit-backdrop-filter:blur(6px);backdrop-filter:blur(6px);}
.ag-md.open{display:flex;}
.ag-md__box{background:#0e0e16;border:1px solid rgba(212,180,92,.4);border-radius:22px;max-width:520px;width:100%;padding:34px 30px;position:relative;}
.ag-md__x{position:absolute;top:14px;right:16px;background:none;border:0;color:#9aa4b6;font-size:1.6rem;cursor:pointer;line-height:1;}
.ag-md__h{font-size:1.6rem;font-weight:800;margin:0 0 6px;}
.ag-md__p{color:#aeb6c6;margin:0 0 20px;font-size:.98rem;}
.ag-md__form{display:flex;gap:8px;background:rgba(255,255,255,.06);border:1px solid rgba(212,180,92,.4);border-radius:100px;padding:6px 6px 6px 16px;margin-bottom:14px;}
.ag-md__form input{flex:1;min-width:0;background:transparent;border:0;outline:none;color:#fff;font-size:1rem;}
.ag-md__form input::placeholder{color:rgba(255,255,255,.55);}
.ag-md__or{text-align:center;color:#7a8296;font-size:.85rem;margin:10px 0;}
@media(prefers-reduced-motion:reduce){.ag-rv{opacity:1;transform:none}.ag-lm__mqin{animation:none}.ag-lm__scroll{animation:none}}
.ag-btnp,.ag-btno{will-change:transform;}
.ag-lm__cta .ag-btnp{animation:agBtnFloat 4.5s ease-in-out infinite;}
@keyframes agBtnFloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-6px)}}
.ag-lm__cta .ag-btnp:hover{transform:translateY(-4px) rotate(-1.5deg) scale(1.03);}
@media(max-width:900px){.ag-lm__egerie{height:min(80vh,860px);right:50%;transform:translateX(50%)}.ag-lm__hin{text-align:center;max-width:none;margin:0 auto;padding-bottom:34px}.ag-lm__cta{justify-content:center}.ag-lm__hsub{margin-left:auto;margin-right:auto}}
@media(max-width:760px){.ag-lm__chap{grid-template-columns:1fr}.ag-lm__chap:nth-child(even) .ag-lm__chtext{order:0}.ag-lm__float{display:none}.ag-lm__egerie{height:70vh;opacity:.98}}
@media(prefers-reduced-motion:reduce){.ag-lm__cw .ag-lm__chip,.ag-lm__cta .ag-btnp,.ag-lm__egerie{animation:none}}
</style>

<main class="ag-lm" id="ag-main-content">

	<!-- HERO ÉGÉRIE — fond Naples fixe + égérie détourée séparée -->
	<section class="ag-lm__hero">
		<!-- Fond VIDÉO : le long plan égérie (≈40–60 s), plein écran -->
		<div class="ag-lm__hbg" aria-hidden="true">
			<div class="ag-lm__slide is-on">
				<video autoplay muted loop playsinline preload="metadata"
					poster="<?php echo esc_url( $dir . '/assets/videos/hero-egerie-long-poster.jpg' ); ?>">
					<source src="<?php echo esc_url( $dir . '/assets/videos/hero-egerie-long.mp4' ); ?>" type="video/mp4">
				</video>
			</div>
		</div>
		<div class="ag-lm__sgrad"></div>
		<!-- Marque (coin haut gauche) -->
		<div class="ag-lm__htop">
			<img src="<?php echo esc_url( $dir . '/assets/images/logo-header.png' ); ?>" alt="Alliance Groupe">
			<span class="ag-lm__eyebrow">De Naples à Nantes</span>
		</div>
		<!-- Texte (bas gauche) -->
		<div class="ag-lm__hin">
			<h1 class="ag-lm__h1">Je crée &amp; <em>sécurise</em> votre site,<br>propulsé par l'<em>IA</em>.</h1>
			<p class="ag-lm__hsub">Un seul interlocuteur, du conseil à la livraison. Des sites premium, rapides et sécurisés — dès 490 €.</p>
			<div class="ag-lm__cta">
				<button type="button" class="ag-btnp" data-devis>🚀 Lancer mon projet</button>
				<a class="ag-btno" href="#offres">Voir les offres</a>
			</div>
		</div>
	</section>

	<!-- MARQUEE -->
	<div class="ag-lm__mq" aria-hidden="true">
		<div class="ag-lm__mqin">
			<?php for ( $r = 0; $r < 2; $r++ ) : ?>
				<span><?php foreach ( $marquee as $m ) : ?><b>✦</b><?php echo esc_html( $m ); ?><?php endforeach; ?></span>
			<?php endfor; ?>
		</div>
	</div>

	<!-- CHAPITRES -->
	<section class="ag-lm__chapters">
		<?php foreach ( $chapters as $c ) : ?>
			<div class="ag-lm__chap ag-rv">
				<div class="ag-lm__chtext">
					<div class="ag-lm__chn"><?php echo esc_html( $c['n'] ); ?></div>
					<h2 class="ag-lm__cht"><?php echo esc_html( $c['t'] ); ?></h2>
					<p class="ag-lm__chp"><?php echo esc_html( $c['p'] ); ?></p>
				</div>
				<div class="ag-lm__chimg"><img src="<?php echo esc_url( $dir . '/assets/images/' . $c['img'] ); ?>" alt="<?php echo esc_attr( $c['t'] ); ?>" loading="lazy"></div>
			</div>
		<?php endforeach; ?>
	</section>

	<!-- OFFRES / PRIX -->
	<section class="ag-lm__offres" id="offres">
		<h2 class="ag-lm__stitle ag-rv">Des offres claires, à prix fixe</h2>
		<p class="ag-lm__sdesc ag-rv">Pas de devis interminable. Vous choisissez, on livre — vite.</p>
		<div class="ag-lm__packs">
			<?php foreach ( $packs as $p ) : ?>
				<div class="ag-lm__pack ag-rv<?php echo $p['star'] ? ' star' : ''; ?>">
					<?php if ( $p['star'] ) : ?><span class="ag-lm__pstar">★ Le plus choisi</span><?php endif; ?>
					<div class="ag-lm__pnom"><?php echo esc_html( $p['nom'] ); ?></div>
					<div class="ag-lm__pprix"><?php echo esc_html( $p['prix'] ); ?></div>
					<p class="ag-lm__pdesc"><?php echo esc_html( $p['desc'] ); ?></p>
					<ul class="ag-lm__pfeats"><?php foreach ( $p['feats'] as $f ) : ?><li><?php echo esc_html( $f ); ?></li><?php endforeach; ?></ul>
					<a class="ag-btnp" style="text-align:center;text-decoration:none;" href="<?php echo esc_url( home_url( '/sites-express' ) ); ?>">Choisir <?php echo esc_html( $p['nom'] ); ?></a>
				</div>
			<?php endforeach; ?>
		</div>
		<p class="ag-lm__maint ag-rv">+ Maintenance &amp; hébergement à partir de <strong>29 €/mois</strong> — <a href="<?php echo esc_url( home_url( '/sites-express' ) ); ?>">voir les formules</a>.</p>
	</section>

	<!-- ATELIER IA (galerie des outils) -->
	<?php get_template_part( 'template-parts/atelier-gallery', null, array(
		'heading' => 'Que créons-nous aujourd\'hui ?',
		'sub'     => 'Ton atelier propulsé par l\'IA : chiffre un projet, refais ton site, sécurise-le, crée du contenu.',
	) ); ?>

</main>

<!-- MODAL DEVIS / TESTER -->
<div class="ag-md" id="ag-devis-modal" role="dialog" aria-modal="true" aria-label="Lancer mon projet">
	<div class="ag-md__box">
		<button type="button" class="ag-md__x" data-devis-close aria-label="Fermer">&times;</button>
		<h3 class="ag-md__h">Lancer mon projet 🚀</h3>
		<p class="ag-md__p">Commence par un <strong>diagnostic gratuit</strong> de ton site actuel (note /100 + failles). Pas de site ? Passe direct au devis IA.</p>
		<form method="post" action="<?php echo esc_url( $post ); ?>" onsubmit="var i=this.site_url; if(i.value && !/^https?:\/\//i.test(i.value)) i.value='https://'+i.value;">
			<input type="hidden" name="action" value="ag_tester_run">
			<?php wp_nonce_field( 'ag_tester' ); ?>
			<input type="text" name="hp_field" value="" tabindex="-1" autocomplete="off" aria-hidden="true" style="position:absolute;left:-9999px">
			<input type="hidden" name="result_page" value="<?php echo esc_url( home_url( '/tester-mon-site' ) ); ?>">
			<div class="ag-md__form">
				<input type="text" name="site_url" inputmode="url" placeholder="monsite.fr" required aria-label="Adresse de votre site">
				<button type="submit" class="ag-btnp">🔍 Diagnostic</button>
			</div>
		</form>
		<div class="ag-md__or">— ou —</div>
		<a class="ag-btnp" style="display:block;text-align:center;text-decoration:none;" href="<?php echo esc_url( home_url( '/devis-instant' ) ); ?>">🧾 Devis instantané par l'IA</a>
	</div>
</div>

<script>
(function(){
	var rv = [].slice.call(document.querySelectorAll('.ag-rv'));
	if('IntersectionObserver' in window){
		var io = new IntersectionObserver(function(es){ es.forEach(function(e){ if(e.isIntersecting){ e.target.classList.add('in'); io.unobserve(e.target); } }); }, {threshold:.14});
		rv.forEach(function(el){ io.observe(el); });
	} else { rv.forEach(function(el){ el.classList.add('in'); }); }
	// Ciné-loop du hero : fondu enchaîné entre les temps (vidéo ⇄ photo)
	var slides = [].slice.call(document.querySelectorAll('.ag-lm__hbg .ag-lm__slide'));
	if(slides.length > 1){
		var beat = 0;
		setInterval(function(){
			slides[beat].classList.remove('is-on');
			beat = (beat + 1) % slides.length;
			slides[beat].classList.add('is-on');
			var v = slides[beat].querySelector('video');
			if(v){ try{ v.currentTime = 0; v.play(); }catch(e){} }
		}, 6500);
	}
	// Parallaxe souris sur les badges flottants
	var hero = document.querySelector('.ag-lm__hero');
	var cws  = [].slice.call(document.querySelectorAll('.ag-lm__cw'));
	if(hero && cws.length && window.matchMedia && window.matchMedia('(pointer:fine)').matches){
		hero.addEventListener('mousemove', function(e){
			var r = hero.getBoundingClientRect();
			var x = (e.clientX - r.left)/r.width - .5, y = (e.clientY - r.top)/r.height - .5;
			cws.forEach(function(c){ var d = parseFloat(c.getAttribute('data-depth'))||24; c.style.transform = 'translate('+(x*d)+'px,'+(y*d)+'px)'; });
		});
		hero.addEventListener('mouseleave', function(){ cws.forEach(function(c){ c.style.transform=''; }); });
	}
	var md = document.getElementById('ag-devis-modal');
	function open(){ md.classList.add('open'); document.body.style.overflow='hidden'; var i=md.querySelector('input[name=site_url]'); if(i) setTimeout(function(){i.focus();},80); }
	function close(){ md.classList.remove('open'); document.body.style.overflow=''; }
	document.querySelectorAll('[data-devis]').forEach(function(b){ b.addEventListener('click',open); });
	md.addEventListener('click',function(e){ if(e.target===md) close(); });
	document.querySelectorAll('[data-devis-close]').forEach(function(b){ b.addEventListener('click',close); });
	document.addEventListener('keydown',function(e){ if(e.key==='Escape') close(); });
})();
</script>

<?php get_footer(); ?>
