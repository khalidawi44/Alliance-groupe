<?php
/**
 * Process — refonte futuriste : timeline horizontale avec orbs glow,
 * connecteur lumineux animé qui se trace au scroll, numéros badge gradient.
 *
 * @package Alliance_Groupe_Theme
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<section class="ag-section ag-section--image-luxe ag-process ag-rise">
	<div class="ag-container">
		<span class="ag-tag ag-anim" data-anim="tag">Notre méthode</span>
		<h2 class="ag-section__title ag-anim" data-anim="title">Un process <em>éprouvé</em></h2>
		<p class="ag-section__desc ag-anim" data-anim="desc">4 étapes pour transformer votre vision en résultats concrets.</p>

		<div class="ag-process__timeline">
			<div class="ag-process__track" aria-hidden="true">
				<div class="ag-process__track-progress"></div>
			</div>

			<?php
			$steps = array(
				array(
					'num'   => '01',
					'title' => 'Découverte',
					'text'  => 'Audit de votre situation, analyse de vos concurrents et définition de vos objectifs business.',
					'duration' => '1 semaine',
					'svg'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>',
				),
				array(
					'num'   => '02',
					'title' => 'Stratégie',
					'text'  => "Plan d'action sur-mesure avec roadmap claire, KPIs et priorisation des leviers de croissance.",
					'duration' => '1-2 semaines',
					'svg'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 17l5-5 4 4 8-8"/><path d="M15 8h5v5"/></svg>',
				),
				array(
					'num'   => '03',
					'title' => 'Exécution',
					'text'  => 'Développement, création de contenu et mise en place des outils avec des sprints de livraison rapides.',
					'duration' => '4-8 semaines',
					'svg'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 4 14h7l-1 8 9-12h-7z"/></svg>',
				),
				array(
					'num'   => '04',
					'title' => 'Optimisation',
					'text'  => 'Suivi des performances, A/B testing et amélioration continue pour maximiser votre ROI.',
					'duration' => 'continu',
					'svg'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9"/><path d="M3 4v5h5"/><path d="m12 8 4 4-4 4"/></svg>',
				),
			);
			foreach ( $steps as $i => $s ) :
			?>
			<div class="ag-pstep ag-anim" data-anim="step" style="--i:<?php echo (int) $i; ?>;">
				<div class="ag-pstep__orb">
					<div class="ag-pstep__orb-ring"></div>
					<div class="ag-pstep__orb-glow"></div>
					<div class="ag-pstep__orb-icon"><?php echo $s['svg']; // phpcs:ignore ?></div>
					<div class="ag-pstep__orb-num"><?php echo esc_html( $s['num'] ); ?></div>
				</div>
				<div class="ag-pstep__content">
					<h3 class="ag-pstep__title"><?php echo esc_html( $s['title'] ); ?></h3>
					<p class="ag-pstep__text"><?php echo esc_html( $s['text'] ); ?></p>
					<span class="ag-pstep__duration">⏱ <?php echo esc_html( $s['duration'] ); ?></span>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<style>
.ag-process .ag-process__timeline{display:grid;grid-template-columns:repeat(4,1fr);gap:28px;margin-top:60px;position:relative}
@media (max-width:980px){.ag-process .ag-process__timeline{grid-template-columns:1fr 1fr}}
@media (max-width:560px){.ag-process .ag-process__timeline{grid-template-columns:1fr}}
.ag-process__track{position:absolute;top:70px;left:8%;right:8%;height:2px;background:linear-gradient(90deg,transparent 0%,rgba(212,180,92,.25) 8%,rgba(212,180,92,.25) 92%,transparent 100%);border-radius:999px;pointer-events:none;z-index:1}
.ag-process__track-progress{position:absolute;inset:0;width:0%;background:linear-gradient(90deg,#D4B45C 0%,#F37A1F 100%);border-radius:999px;box-shadow:0 0 16px rgba(212,180,92,.6);transition:width 1.8s cubic-bezier(.16,1,.3,1)}
.ag-process.is-revealed .ag-process__track-progress{width:100%}
@media (max-width:980px){.ag-process__track{display:none}}
.ag-process .ag-pstep{display:flex;flex-direction:column;align-items:center;text-align:center;position:relative;z-index:2;background:transparent !important;border:none !important;padding:0 !important;opacity:0;transform:translateY(40px);transition:opacity .7s cubic-bezier(.16,1,.3,1),transform .7s cubic-bezier(.16,1,.3,1);transition-delay:calc(var(--i) * 150ms)}
.ag-process.is-revealed .ag-pstep{opacity:1;transform:translateY(0)}
.ag-pstep__orb{position:relative;width:140px;height:140px;margin-bottom:24px;display:grid;place-items:center}
.ag-pstep__orb-ring{position:absolute;inset:0;border-radius:50%;background:conic-gradient(from 0deg,transparent 0deg,rgba(212,180,92,.6) 90deg,transparent 180deg,rgba(243,122,31,.4) 270deg,transparent 360deg);animation:agPstepRing 6s linear infinite;padding:2px;mask:linear-gradient(#000 0 0) content-box,linear-gradient(#000 0 0);-webkit-mask:linear-gradient(#000 0 0) content-box,linear-gradient(#000 0 0);mask-composite:exclude;-webkit-mask-composite:xor}
@keyframes agPstepRing{to{transform:rotate(360deg)}}
.ag-pstep__orb-glow{position:absolute;inset:10px;background:radial-gradient(circle,rgba(212,180,92,.25) 0%,transparent 70%);border-radius:50%;filter:blur(12px);opacity:.5;transition:opacity .4s ease,transform .4s ease}
.ag-pstep:hover .ag-pstep__orb-glow{opacity:1;transform:scale(1.12)}
.ag-pstep__orb::before{content:'';position:absolute;inset:14px;border-radius:50%;background:radial-gradient(circle at 30% 30%,rgba(212,180,92,.12) 0%,transparent 50%),linear-gradient(135deg,#14141c 0%,#0a0a0f 100%);border:1px solid rgba(212,180,92,.2)}
.ag-pstep__orb-icon{position:relative;z-index:2;width:48px;height:48px;color:#D4B45C;filter:drop-shadow(0 0 12px rgba(212,180,92,.5));transition:transform .4s cubic-bezier(.16,1,.3,1),color .3s ease}
.ag-pstep__orb-icon svg{width:100%;height:100%}
.ag-pstep:hover .ag-pstep__orb-icon{transform:scale(1.15);color:#F37A1F}
.ag-pstep__orb-num{position:absolute;bottom:0;right:0;width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,#D4B45C 0%,#F37A1F 100%);color:#0a0a0f;display:grid;place-items:center;font-family:Georgia,serif;font-size:.82rem;font-weight:800;letter-spacing:.5px;box-shadow:0 4px 16px rgba(212,180,92,.5),inset 0 -2px 4px rgba(0,0,0,.2);z-index:3}
.ag-pstep__content{max-width:280px}
.ag-pstep__title{font-family:Georgia,'Playfair Display',serif !important;font-size:1.35rem !important;font-weight:700 !important;color:#fff !important;margin:0 0 10px !important}
.ag-pstep__text{color:rgba(255,255,255,.72);font-size:.92rem;line-height:1.6;margin:0 0 12px}
.ag-pstep__duration{display:inline-block;padding:4px 10px;background:rgba(212,180,92,.08);border:1px solid rgba(212,180,92,.25);border-radius:999px;color:#D4B45C;font-size:.72rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase}
@media (prefers-reduced-motion:reduce){.ag-pstep__orb-ring{animation:none !important}.ag-process__track-progress{transition:none !important;width:100% !important}.ag-process .ag-pstep{opacity:1 !important;transform:none !important}}
</style>

<script>
(function(){var sec=document.querySelector('.ag-process');if(!sec||!('IntersectionObserver' in window))return;new IntersectionObserver(function(e){e.forEach(function(x){if(x.isIntersecting)sec.classList.add('is-revealed')})},{threshold:.2}).observe(sec)})();
</script>
