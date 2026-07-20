<?php
/**
 * Section « 2 façons de concevoir votre site » — écran PARTAGÉ (comme le hero) :
 *  - GAUCHE (bleu)  : Création sur-mesure  -> /sites-express
 *  - DROITE (doré)  : Templates prêts à l'emploi (gratuits) -> /templates-wordpress
 * Séparés par une diagonale + ligne dorée. Léger (CSS pur, 0 animation lourde).
 *
 * @package Alliance_Groupe_Theme
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<section class="ag-2ways" aria-label="Deux façons de concevoir votre site">
	<div class="ag-2ways__head">
		<span class="ag-2ways__tag">2 façons de concevoir votre site</span>
		<h2 class="ag-2ways__title">Sur-mesure ou prêt à l'emploi — <em>vous choisissez.</em></h2>
	</div>

	<div class="ag-2ways__split">
		<!-- fonds colorés en diagonale -->
		<div class="ag-2ways__half ag-2ways__half--crea" aria-hidden="true"></div>
		<div class="ag-2ways__half ag-2ways__half--tpl" aria-hidden="true"></div>
		<svg class="ag-2ways__line" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
			<line x1="56" y1="0" x2="44" y2="100" stroke="#F6D77A" stroke-width="2" vector-effect="non-scaling-stroke"/>
		</svg>

		<div class="ag-2ways__grid">
			<div class="ag-2ways__panel">
				<span class="ag-2ways__ic">✨</span>
				<span class="ag-2ways__k ag-2ways__k--crea">Création · sur-mesure</span>
				<h3 class="ag-2ways__h">On crée votre site de A à Z</h3>
				<p class="ag-2ways__p">Design unique, rapide, <strong>sécurisé dès le départ</strong> et référencé sur Google. Vous parlez directement à la personne qui le construit. Dès <strong>490 €</strong>, payable en 4× sans frais.</p>
				<a href="<?php echo esc_url( home_url( '/sites-express' ) ); ?>" class="ag-2ways__btn ag-2ways__btn--crea">Créer mon site →</a>
			</div>
			<div class="ag-2ways__panel ag-2ways__panel--right">
				<span class="ag-2ways__ic">📦</span>
				<span class="ag-2ways__k ag-2ways__k--tpl">Prêt à l'emploi · gratuit</span>
				<h3 class="ag-2ways__h">Partez d'un template métier</h3>
				<p class="ag-2ways__p">6 thèmes français (avocat, resto, artisan, coach, barber, asso), contenu déjà rédigé, installés en <strong>2 min</strong>. <strong>100 % gratuits</strong> — il ne reste qu'à remplacer quelques éléments.</p>
				<a href="<?php echo esc_url( home_url( '/templates-wordpress' ) ); ?>" class="ag-2ways__btn ag-2ways__btn--tpl">Découvrir les templates →</a>
			</div>
		</div>
	</div>
</section>

<style>
/* padding-bas généreux : les boutons ne sont plus collés au bord bas. La section
   suivante (.ag-rise, remonte de -58px) recouvre d'abord cet espace vide → les
   boutons restent visibles et cliquables plus longtemps. */
.ag-2ways{background:#0a0a0f;padding:72px 0 120px;position:relative;overflow:hidden}
.ag-2ways__head{max-width:820px;margin:0 auto 36px;text-align:center;padding:0 22px}
.ag-2ways__tag{display:inline-block;color:#F3D27A;font-size:.78rem;letter-spacing:2px;text-transform:uppercase;font-weight:800;margin-bottom:12px}
.ag-2ways__title{font-family:Georgia,'Playfair Display',serif;font-size:clamp(1.7rem,3.6vw,2.6rem);line-height:1.18;color:#fff;margin:0}
.ag-2ways__title em{font-style:italic;color:#F3D27A;-webkit-text-fill-color:#F3D27A}
/* ── écran partagé ── */
.ag-2ways__split{position:relative;min-height:460px;overflow:hidden}
.ag-2ways__half{position:absolute;inset:0}
.ag-2ways__half--crea{clip-path:polygon(0 0,56% 0,44% 100%,0 100%);
	background:radial-gradient(120% 120% at 20% 30%,rgba(40,110,220,.35),transparent 60%),linear-gradient(160deg,#0c1733 0%,#0a1020 70%,#070a14 100%)}
.ag-2ways__half--tpl{clip-path:polygon(56% 0,100% 0,100% 100%,44% 100%);
	background:radial-gradient(120% 120% at 80% 35%,rgba(243,122,31,.30),transparent 60%),linear-gradient(160deg,#1c1206 0%,#140d06 70%,#0a0a0f 100%)}
.ag-2ways__line{position:absolute;inset:0;width:100%;height:100%;z-index:2;pointer-events:none}
.ag-2ways__grid{position:relative;z-index:3;max-width:1160px;margin:0 auto;display:grid;grid-template-columns:1fr 1fr;gap:0;min-height:460px}
.ag-2ways__panel{display:flex;flex-direction:column;align-items:flex-start;justify-content:center;gap:12px;padding:54px 56px 54px 40px;max-width:480px}
.ag-2ways__panel--right{margin-left:auto;align-items:flex-end;text-align:right;padding:54px 40px 54px 56px}
.ag-2ways__ic{font-size:2rem;line-height:1}
.ag-2ways__k{font-size:.74rem;letter-spacing:2px;text-transform:uppercase;font-weight:800}
.ag-2ways__k--crea{color:#7FB2FF}
.ag-2ways__k--tpl{color:#F3D27A}
.ag-2ways__h{font-family:Georgia,'Playfair Display',serif;font-size:clamp(1.3rem,2.4vw,1.9rem);color:#fff;margin:0;line-height:1.2}
.ag-2ways__p{color:rgba(255,255,255,.82);font-size:.96rem;line-height:1.6;margin:0}
.ag-2ways__p strong{color:#fff}
.ag-2ways__btn{display:inline-block;margin-top:8px;padding:14px 26px;border-radius:999px;font-weight:800;font-size:.92rem;text-decoration:none;transition:transform .2s ease,box-shadow .2s ease}
.ag-2ways__btn--crea{background:linear-gradient(135deg,#3b82f6,#1d4ed8);color:#fff !important;box-shadow:0 12px 30px rgba(37,99,235,.4)}
.ag-2ways__btn--tpl{background:linear-gradient(135deg,#F37A1F,#D4B45C);color:#0a0a0f !important;box-shadow:0 12px 30px rgba(243,122,31,.4)}
.ag-2ways__btn:hover{transform:translateY(-3px)}
@media(max-width:820px){
	.ag-2ways__split{min-height:0}
	.ag-2ways__half--crea,.ag-2ways__half--tpl{display:none}
	.ag-2ways__line{display:none}
	.ag-2ways__grid{grid-template-columns:1fr;min-height:0}
	.ag-2ways__panel{max-width:none;align-items:center !important;text-align:center !important;padding:48px 24px;
		background:radial-gradient(120% 120% at 50% 0,rgba(40,110,220,.3),transparent 60%),#0a1020}
	.ag-2ways__panel--right{background:radial-gradient(120% 120% at 50% 0,rgba(243,122,31,.28),transparent 60%),#140d06}
}
</style>
