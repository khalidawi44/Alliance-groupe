<?php
/**
 * Section "Alliance Groupe en vidéo" — visible en mode premium uniquement.
 *
 * Rendue par ag_domicile_render_promo_video() (appel direct depuis front-page.php
 * ou via shortcode [ag_domicile_promo_video]).
 *
 * @package AG_Starter_Domicile
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Affiche la section vidéo promo Alliance Groupe.
 * Visible uniquement quand un preset domicile est actif (mode premium).
 */
function ag_domicile_render_promo_video() {
	// Section « Pourquoi nous choisir » — percutante, à la marque (aide à domicile).
	// Remplace l'ancien encart Alliance (hors sujet sur un site client).
	$devis = function_exists( 'ag_domicile_resolve_cta_url' ) ? ag_domicile_resolve_cta_url( '/devis/' ) : home_url( '/devis/' );
	$items = array(
		array( 'i' => '💚', 'g' => '#4f9d6b,#6fbf8a', 't' => 'Un intervenant de confiance', 'd' => 'La même personne, à l’écoute, dans la durée. Jamais un inconnu chez vous.' ),
		array( 'i' => '💰', 'g' => '#c9a36b,#e6c78a', 't' => 'Crédit d’impôt de 50 %', 'd' => 'Avec l’avance immédiate, vous ne réglez que la moitié — même sans être imposable.' ),
		array( 'i' => '🕐', 'g' => '#3f8f63,#5cb37e', 't' => 'Présents 7j/7, jour & nuit', 'd' => 'Disponibles quand vous en avez besoin, jusqu’à la garde de nuit.' ),
		array( 'i' => '👨‍👩‍👧', 'g' => '#5aa06f,#86c78f', 't' => 'Adapté à chacun', 'd' => 'Seniors, handicap léger, garde d’enfants de 1 mois à 8 ans.' ),
	);

	// Bande de chiffres animés (compteurs) — preuve sociale.
	$stats = array(
		array( 'n' => 120, 'suf' => '+', 'l' => 'Familles accompagnées', 'dec' => 0 ),
		array( 'n' => 12,  'suf' => ' ans', 'l' => 'D’expérience à Nantes', 'dec' => 0 ),
		array( 'n' => 4.9, 'suf' => '/5', 'l' => 'Note moyenne des familles', 'dec' => 1 ),
		array( 'n' => 7,   'suf' => 'j/7', 'l' => 'Disponibles, jour & nuit', 'dec' => 0 ),
	);

	// Bandeau de logos de confiance (labels officiels du secteur SAP).
	$trust = array(
		array( 'i' => '🛡️', 't' => 'Agréé Services à la personne', 's' => 'Déclaration SAP en règle' ),
		array( 'i' => '💶', 't' => 'Crédit d’impôt 50 %', 's' => 'Avance immédiate URSSAF' ),
		array( 'i' => '🧾', 't' => 'CESU accepté', 's' => 'Chèque emploi service' ),
		array( 'i' => '🤝', 't' => 'Sans engagement', 's' => 'Devis & évaluation gratuits' ),
	);
	?>
	<section class="ag-gwenwhy">
		<div class="ag-gwenwhy__in">
			<div class="ag-gwenwhy__head ag-anim">
				<span class="ag-gwenwhy__tag">Pourquoi nous choisir</span>
				<h2 class="ag-gwenwhy__title">Une aide à domicile <em>en qui vous avez confiance</em></h2>
				<p class="ag-gwenwhy__lead">Une présence humaine et fiable, à Nantes et alentours — pensée pour le bien-être de votre proche et la tranquillité de toute la famille.</p>
			</div>

			<div class="ag-gwenstats" role="list">
				<?php foreach ( $stats as $st ) : ?>
					<div class="ag-gwenstats__it ag-anim" role="listitem">
						<div class="ag-gwenstats__n"><span class="ag-gwencount" data-to="<?php echo esc_attr( $st['n'] ); ?>" data-dec="<?php echo esc_attr( $st['dec'] ); ?>">0</span><span class="ag-gwenstats__suf"><?php echo esc_html( $st['suf'] ); ?></span></div>
						<div class="ag-gwenstats__l"><?php echo esc_html( $st['l'] ); ?></div>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="ag-gwenwhy__grid">
				<?php foreach ( $items as $it ) : ?>
					<div class="ag-gwenwhy__card ag-anim">
						<span class="ag-gwenwhy__ic" style="background:linear-gradient(135deg,<?php echo esc_attr( $it['g'] ); ?>);"><?php echo esc_html( $it['i'] ); ?></span>
						<h3 class="ag-gwenwhy__ct"><?php echo esc_html( $it['t'] ); ?></h3>
						<p class="ag-gwenwhy__cd"><?php echo esc_html( $it['d'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="ag-gwentrust ag-anim">
				<?php foreach ( $trust as $tr ) : ?>
					<div class="ag-gwentrust__it">
						<span class="ag-gwentrust__ic"><?php echo esc_html( $tr['i'] ); ?></span>
						<span class="ag-gwentrust__tx">
							<strong><?php echo esc_html( $tr['t'] ); ?></strong>
							<small><?php echo esc_html( $tr['s'] ); ?></small>
						</span>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="ag-gwenwhy__cta ag-anim">
				<a href="<?php echo esc_url( $devis ); ?>" class="ag-gwenwhy__btn">Demander un devis gratuit →</a>
				<span class="ag-gwenwhy__note">Évaluation à domicile offerte, sans engagement.</span>
			</div>
		</div>
	</section>
	<style>
	.ag-gwenwhy{background:radial-gradient(120% 90% at 80% -10%,rgba(79,157,107,.18),transparent 55%),linear-gradient(180deg,#12160f,#171612);padding:clamp(64px,9vw,120px) 20px;}
	.ag-gwenwhy__in{max-width:1180px;margin:0 auto;}
	.ag-gwenwhy__head{text-align:center;max-width:760px;margin:0 auto 54px;}
	.ag-gwenwhy__tag{display:inline-block;font-size:.8rem;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:#8fd0a3;background:rgba(79,157,107,.14);border:1px solid rgba(111,191,138,.4);padding:8px 18px;border-radius:100px;margin-bottom:20px;}
	.ag-gwenwhy__title{font-family:"Fraunces",Georgia,serif;font-weight:700;font-size:clamp(1.9rem,4.4vw,3rem);line-height:1.1;color:#f6f1e4 !important;margin:0 0 16px;text-wrap:balance;}
	.ag-gwenwhy__title em{font-style:italic;color:#6fbf8a !important;}
	.ag-gwenwhy__lead{color:#c7c1b0;font-size:clamp(1rem,2.2vw,1.18rem);line-height:1.6;margin:0;}
	.ag-gwenwhy__grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:22px;}
	.ag-gwenwhy__card{background:linear-gradient(180deg,rgba(255,255,255,.045),rgba(255,255,255,.02));border:1px solid rgba(111,191,138,.18);border-radius:22px;padding:32px 26px;transition:transform .3s cubic-bezier(.2,.7,.2,1),box-shadow .3s ease,border-color .3s ease;position:relative;overflow:hidden;}
	.ag-gwenwhy__card::after{content:"";position:absolute;inset:0 0 auto 0;height:3px;background:linear-gradient(90deg,#4f9d6b,#c9a36b);transform:scaleX(0);transform-origin:left;transition:transform .35s ease;}
	.ag-gwenwhy__card:hover{transform:translateY(-8px);box-shadow:0 30px 60px -28px rgba(0,0,0,.7);border-color:rgba(111,191,138,.5);}
	.ag-gwenwhy__card:hover::after{transform:scaleX(1);}
	.ag-gwenwhy__ic{display:inline-flex;align-items:center;justify-content:center;width:66px;height:66px;border-radius:20px;font-size:32px;margin-bottom:20px;box-shadow:0 16px 30px -14px rgba(79,157,107,.7);}
	.ag-gwenwhy__ct{font-family:"Fraunces",Georgia,serif;font-weight:600;font-size:1.28rem;color:#f6f1e4 !important;margin:0 0 10px;}
	.ag-gwenwhy__cd{color:#b3ad9c;font-size:1rem;line-height:1.55;margin:0;}
	.ag-gwenwhy__cta{text-align:center;margin-top:52px;}
	.ag-gwenwhy__btn{display:inline-block;background:linear-gradient(120deg,#3f8f63,#6fbf8a);color:#0f180f;font-weight:800;font-size:1.15rem;text-decoration:none;padding:20px 42px;border-radius:100px;box-shadow:0 18px 40px -14px rgba(79,157,107,.75),0 0 0 6px rgba(79,157,107,.12);transition:transform .2s ease,box-shadow .2s ease,filter .2s ease;}
	.ag-gwenwhy__btn:hover{transform:translateY(-3px) scale(1.02);filter:brightness(1.05);box-shadow:0 26px 54px -16px rgba(79,157,107,.85),0 0 0 8px rgba(79,157,107,.16);}
	.ag-gwenwhy__note{display:block;color:#8f8a7c;font-size:.92rem;margin-top:14px;}
	/* Bande de chiffres animés */
	.ag-gwenstats{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;margin:0 0 56px;padding:30px 18px;background:linear-gradient(180deg,rgba(79,157,107,.10),rgba(79,157,107,.03));border:1px solid rgba(111,191,138,.22);border-radius:24px;}
	.ag-gwenstats__it{text-align:center;position:relative;}
	.ag-gwenstats__it:not(:last-child)::after{content:"";position:absolute;right:-9px;top:50%;transform:translateY(-50%);width:1px;height:52px;background:linear-gradient(180deg,transparent,rgba(111,191,138,.35),transparent);}
	.ag-gwenstats__n{font-family:"Fraunces",Georgia,serif;font-weight:700;font-size:clamp(2rem,4.6vw,2.9rem);line-height:1;color:#8fd0a3;display:flex;align-items:baseline;justify-content:center;gap:1px;}
	.ag-gwenstats__suf{font-size:.55em;color:#6fbf8a;font-weight:700;}
	.ag-gwenstats__l{color:#b3ad9c;font-size:.92rem;margin-top:10px;line-height:1.35;}
	/* Bandeau de logos de confiance */
	.ag-gwentrust{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin:56px 0 0;}
	.ag-gwentrust__it{display:flex;align-items:center;gap:12px;background:rgba(255,255,255,.04);border:1px solid rgba(111,191,138,.2);border-radius:16px;padding:16px 18px;transition:border-color .3s ease,background .3s ease;}
	.ag-gwentrust__it:hover{border-color:rgba(111,191,138,.5);background:rgba(79,157,107,.09);}
	.ag-gwentrust__ic{flex:none;width:44px;height:44px;display:inline-flex;align-items:center;justify-content:center;font-size:22px;border-radius:12px;background:rgba(79,157,107,.16);border:1px solid rgba(111,191,138,.3);}
	.ag-gwentrust__tx{display:flex;flex-direction:column;line-height:1.25;}
	.ag-gwentrust__tx strong{color:#f6f1e4;font-size:1rem;font-weight:700;}
	.ag-gwentrust__tx small{color:#8f8a7c;font-size:.82rem;margin-top:2px;}
	@media(max-width:820px){.ag-gwenstats{grid-template-columns:repeat(2,1fr);gap:26px 18px;}.ag-gwenstats__it:nth-child(2)::after{display:none;}.ag-gwentrust{grid-template-columns:repeat(2,1fr);}}
	@media(max-width:480px){.ag-gwenstats{grid-template-columns:repeat(2,1fr);}.ag-gwenstats__it::after{display:none;}}
	@media(prefers-reduced-motion:reduce){.ag-gwenwhy__card,.ag-gwenwhy__btn,.ag-gwentrust__it{transition:none;}}
	</style>
	<script>
	(function(){
		var els=document.querySelectorAll('.ag-gwencount');
		if(!els.length)return;
		function run(el){
			var to=parseFloat(el.getAttribute('data-to'))||0,dec=parseInt(el.getAttribute('data-dec'),10)||0,dur=1500,t0=null;
			function step(ts){
				if(!t0)t0=ts;var p=Math.min((ts-t0)/dur,1);
				var e=1-Math.pow(1-p,3); // easeOutCubic
				el.textContent=(to*e).toFixed(dec).replace('.',',');
				if(p<1)requestAnimationFrame(step);else el.textContent=to.toFixed(dec).replace('.',',');
			}
			requestAnimationFrame(step);
		}
		if('IntersectionObserver' in window){
			var io=new IntersectionObserver(function(en){
				en.forEach(function(x){if(x.isIntersecting){run(x.target);io.unobserve(x.target);}});
			},{threshold:.4});
			els.forEach(function(el){io.observe(el);});
		}else{els.forEach(run);}
	})();
	</script>
	<?php
}

// Shortcode au cas où le user veut l'insérer manuellement
add_shortcode( 'ag_domicile_promo_video', function () {
	ob_start();
	ag_domicile_render_promo_video();
	return ob_get_clean();
} );
