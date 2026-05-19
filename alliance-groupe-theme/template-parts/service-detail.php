<?php
/**
 * Service detail — section exhaustive paramétrable pour chaque page service.
 *
 * Usage depuis page-service-*.php :
 *   $GLOBALS['ag_service_detail'] = array(
 *       'slug'        => 'web',
 *       'title'       => '...',
 *       'intro'       => '...',
 *       'deliverables'=> array( '...', '...' ),  // 4-8 livrables
 *       'process'     => array( array('title'=>..., 'desc'=>...), ... ), // 3-5 étapes
 *       'pricing'     => array( 'from'=>1500, 'to'=>15000, 'duration'=>'4-12 sem.' ),
 *       'faqs'        => array( array('q'=>..., 'a'=>...), ... ),
 *   );
 *   get_template_part('template-parts/service-detail');
 *
 * @package Alliance_Groupe_Theme
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$d = isset( $GLOBALS['ag_service_detail'] ) && is_array( $GLOBALS['ag_service_detail'] )
	? $GLOBALS['ag_service_detail'] : array();
if ( empty( $d['slug'] ) ) return;
?>

<!-- DÉTAIL EXHAUSTIF du service -->
<section class="ag-svc-detail" id="ag-svc-<?php echo esc_attr( $d['slug'] ); ?>">
	<div class="ag-container">
		<div class="ag-svc-detail__grid">

			<!-- Sticky sidebar : navigation interne -->
			<aside class="ag-svc-detail__nav">
				<div class="ag-svc-nav-inner">
					<span class="ag-svc-nav-tag">Sur cette page</span>
					<a href="#livrables-<?php echo esc_attr( $d['slug'] ); ?>" class="ag-svc-nav-link">01 — Ce que vous obtenez</a>
					<a href="#process-<?php echo esc_attr( $d['slug'] ); ?>" class="ag-svc-nav-link">02 — Comment ça se passe</a>
					<a href="#tarifs-<?php echo esc_attr( $d['slug'] ); ?>" class="ag-svc-nav-link">03 — Combien ça coûte</a>
					<a href="#faq-<?php echo esc_attr( $d['slug'] ); ?>" class="ag-svc-nav-link">04 — Questions fréquentes</a>
				</div>
			</aside>

			<!-- Contenu -->
			<div class="ag-svc-detail__body">

				<!-- 01 LIVRABLES -->
				<?php if ( ! empty( $d['deliverables'] ) ) : ?>
					<div class="ag-svc-block" id="livrables-<?php echo esc_attr( $d['slug'] ); ?>">
						<span class="ag-svc-block-num">01</span>
						<h2 class="ag-svc-block-title">Ce que vous obtenez<br><em>concrètement</em></h2>
						<?php if ( ! empty( $d['intro'] ) ) : ?>
							<p class="ag-svc-block-intro"><?php echo wp_kses_post( $d['intro'] ); ?></p>
						<?php endif; ?>
						<div class="ag-svc-deliverables">
							<?php foreach ( $d['deliverables'] as $i => $del ) : ?>
								<div class="ag-svc-del">
									<span class="ag-svc-del-check">✓</span>
									<span class="ag-svc-del-text"><?php echo wp_kses_post( $del ); ?></span>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>

				<!-- 02 PROCESS -->
				<?php if ( ! empty( $d['process'] ) ) : ?>
					<div class="ag-svc-block" id="process-<?php echo esc_attr( $d['slug'] ); ?>">
						<span class="ag-svc-block-num">02</span>
						<h2 class="ag-svc-block-title">Comment ça <em>se passe</em></h2>
						<div class="ag-svc-process">
							<?php foreach ( $d['process'] as $i => $step ) : ?>
								<div class="ag-svc-step">
									<div class="ag-svc-step-num"><?php echo str_pad( $i + 1, 2, '0', STR_PAD_LEFT ); ?></div>
									<div class="ag-svc-step-content">
										<h3 class="ag-svc-step-title"><?php echo esc_html( $step['title'] ); ?></h3>
										<p class="ag-svc-step-desc"><?php echo wp_kses_post( $step['desc'] ); ?></p>
										<?php if ( ! empty( $step['duration'] ) ) : ?>
											<span class="ag-svc-step-duration">⏱ <?php echo esc_html( $step['duration'] ); ?></span>
										<?php endif; ?>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>

				<!-- 03 TARIFS -->
				<?php if ( ! empty( $d['pricing'] ) ) : ?>
					<div class="ag-svc-block" id="tarifs-<?php echo esc_attr( $d['slug'] ); ?>">
						<span class="ag-svc-block-num">03</span>
						<h2 class="ag-svc-block-title">Combien <em>ça coûte</em></h2>
						<div class="ag-svc-pricing">
							<div class="ag-svc-pricing-card">
								<span class="ag-svc-pricing-label">À partir de</span>
								<div class="ag-svc-pricing-from">
									<span class="ag-svc-pricing-num"><?php echo number_format( (int) $d['pricing']['from'], 0, ',', ' ' ); ?></span>
									<span class="ag-svc-pricing-cur">€ HT</span>
								</div>
								<?php if ( ! empty( $d['pricing']['to'] ) ) : ?>
									<p class="ag-svc-pricing-range">jusqu'à <?php echo number_format( (int) $d['pricing']['to'], 0, ',', ' ' ); ?>€ HT selon ambition</p>
								<?php endif; ?>
								<?php if ( ! empty( $d['pricing']['duration'] ) ) : ?>
									<p class="ag-svc-pricing-duration">⏱ <?php echo esc_html( $d['pricing']['duration'] ); ?></p>
								<?php endif; ?>
								<?php if ( ! empty( $d['pricing']['includes'] ) ) : ?>
									<ul class="ag-svc-pricing-incl">
										<?php foreach ( $d['pricing']['includes'] as $inc ) : ?>
											<li>✓ <?php echo esc_html( $inc ); ?></li>
										<?php endforeach; ?>
									</ul>
								<?php endif; ?>
								<a href="<?php echo esc_url( home_url( '/rendez-vous' ) ); ?>" class="ag-svc-pricing-cta">Demander un devis →</a>
							</div>
						</div>
					</div>
				<?php endif; ?>

				<!-- 04 FAQ -->
				<?php if ( ! empty( $d['faqs'] ) ) : ?>
					<div class="ag-svc-block" id="faq-<?php echo esc_attr( $d['slug'] ); ?>">
						<span class="ag-svc-block-num">04</span>
						<h2 class="ag-svc-block-title">Questions <em>fréquentes</em></h2>
						<div class="ag-svc-faqs">
							<?php foreach ( $d['faqs'] as $faq ) : ?>
								<details class="ag-svc-faq">
									<summary class="ag-svc-faq-q"><?php echo esc_html( $faq['q'] ); ?><span class="ag-svc-faq-icon">+</span></summary>
									<div class="ag-svc-faq-a"><?php echo wp_kses_post( $faq['a'] ); ?></div>
								</details>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>

			</div>
		</div>
	</div>
</section>

<style>
.ag-svc-detail{padding:80px 24px;background:linear-gradient(180deg,#0a0a0f 0%,#14141c 50%,#0a0a0f 100%);color:#fff}
.ag-svc-detail__grid{display:grid;grid-template-columns:280px 1fr;gap:60px;max-width:1240px;margin:0 auto}
@media (max-width:900px){.ag-svc-detail__grid{grid-template-columns:1fr;gap:30px}}

.ag-svc-detail__nav{position:relative}
.ag-svc-nav-inner{position:sticky;top:120px;display:flex;flex-direction:column;gap:8px}
.ag-svc-nav-tag{display:inline-block;padding:6px 12px;background:rgba(212,180,92,.1);border:1px solid rgba(212,180,92,.3);border-radius:999px;color:#D4B45C;font-size:.7rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;margin-bottom:12px;width:fit-content}
.ag-svc-nav-link{color:rgba(255,255,255,.55);text-decoration:none;font-size:.92rem;font-weight:600;padding:10px 14px;border-radius:8px;transition:all .25s ease;border-left:2px solid transparent}
.ag-svc-nav-link:hover{color:#D4B45C;background:rgba(212,180,92,.06);border-left-color:#D4B45C;text-decoration:none;padding-left:18px}
@media (max-width:900px){.ag-svc-nav-inner{position:static;flex-direction:row;flex-wrap:wrap;gap:6px}.ag-svc-nav-link{font-size:.8rem;padding:6px 10px}.ag-svc-nav-tag{display:none}}

.ag-svc-block{margin-bottom:80px;position:relative}
.ag-svc-block:last-child{margin-bottom:0}
.ag-svc-block-num{display:inline-block;font-family:Georgia,serif;font-size:.85rem;color:#D4B45C;letter-spacing:6px;font-weight:700;margin-bottom:10px}
.ag-svc-block-title{font-family:Georgia,'Playfair Display',serif !important;font-size:clamp(1.8rem,3.5vw,2.8rem) !important;font-weight:700 !important;line-height:1.15 !important;color:#fff !important;margin:0 0 24px !important}
.ag-svc-block-title em{background:linear-gradient(135deg,#D4B45C 0%,#F37A1F 100%);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;color:transparent;font-style:italic}
.ag-svc-block-intro{color:rgba(255,255,255,.78);font-size:1.05rem;line-height:1.75;margin:0 0 32px;max-width:680px}

/* LIVRABLES */
.ag-svc-deliverables{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:14px}
.ag-svc-del{display:flex;align-items:flex-start;gap:12px;padding:18px 20px;background:rgba(255,255,255,.03);border:1px solid rgba(212,180,92,.12);border-radius:12px;transition:all .3s ease}
.ag-svc-del:hover{background:rgba(212,180,92,.06);border-color:rgba(212,180,92,.3);transform:translateX(4px)}
.ag-svc-del-check{display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;background:rgba(34,197,94,.15);color:#22c55e;border-radius:50%;font-size:.85rem;font-weight:700;flex-shrink:0;margin-top:2px}
.ag-svc-del-text{color:rgba(255,255,255,.85);line-height:1.5;font-size:.95rem}

/* PROCESS */
.ag-svc-process{display:flex;flex-direction:column;gap:24px;position:relative}
.ag-svc-process::before{content:'';position:absolute;left:24px;top:24px;bottom:24px;width:2px;background:linear-gradient(180deg,transparent 0%,rgba(212,180,92,.3) 10%,rgba(212,180,92,.3) 90%,transparent 100%);pointer-events:none}
.ag-svc-step{display:grid;grid-template-columns:50px 1fr;gap:24px;position:relative}
.ag-svc-step-num{width:50px;height:50px;border-radius:50%;background:linear-gradient(135deg,#D4B45C 0%,#F37A1F 100%);color:#0a0a0f;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:1rem;box-shadow:0 0 20px rgba(212,180,92,.4);position:relative;z-index:1}
.ag-svc-step-content{background:rgba(255,255,255,.03);border:1px solid rgba(212,180,92,.12);border-radius:14px;padding:24px;transition:border-color .3s ease}
.ag-svc-step:hover .ag-svc-step-content{border-color:rgba(212,180,92,.35)}
.ag-svc-step-title{font-family:Georgia,serif !important;font-size:1.2rem !important;color:#fff !important;margin:0 0 10px !important}
.ag-svc-step-desc{color:rgba(255,255,255,.75);line-height:1.65;font-size:.95rem;margin:0}
.ag-svc-step-duration{display:inline-block;margin-top:12px;padding:4px 10px;background:rgba(212,180,92,.08);border-radius:999px;color:#D4B45C;font-size:.78rem;letter-spacing:1px;font-weight:700}

/* TARIFS */
.ag-svc-pricing{max-width:560px}
.ag-svc-pricing-card{background:linear-gradient(135deg,rgba(30,25,20,.95) 0%,rgba(20,15,10,.98) 100%);border:1px solid rgba(212,180,92,.4);border-radius:18px;padding:36px;box-shadow:0 20px 60px rgba(212,180,92,.15)}
.ag-svc-pricing-label{display:block;color:#D4B45C;font-size:.78rem;letter-spacing:3px;text-transform:uppercase;font-weight:700;margin-bottom:12px}
.ag-svc-pricing-from{display:flex;align-items:baseline;gap:6px;margin-bottom:8px}
.ag-svc-pricing-num{font-size:3.4rem;font-weight:800;color:#fff;line-height:1}
.ag-svc-pricing-cur{font-size:1.4rem;font-weight:700;color:#D4B45C}
.ag-svc-pricing-range{color:rgba(255,255,255,.6);font-size:.92rem;margin:0 0 6px}
.ag-svc-pricing-duration{color:rgba(212,180,92,.8);font-size:.88rem;margin:0 0 20px;letter-spacing:.5px}
.ag-svc-pricing-incl{list-style:none;padding:0;margin:24px 0;border-top:1px solid rgba(212,180,92,.15);padding-top:20px}
.ag-svc-pricing-incl li{padding:6px 0;color:rgba(255,255,255,.78);font-size:.92rem;line-height:1.5}
.ag-svc-pricing-cta{display:block;text-align:center;margin-top:24px;padding:16px;background:linear-gradient(135deg,#D4B45C 0%,#F37A1F 100%);color:#0a0a0f !important;font-weight:800;letter-spacing:1.5px;text-transform:uppercase;font-size:.92rem;border-radius:10px;text-decoration:none;box-shadow:0 8px 24px rgba(212,180,92,.35);transition:transform .3s ease,box-shadow .3s ease}
.ag-svc-pricing-cta:hover{transform:translateY(-2px);box-shadow:0 12px 32px rgba(212,180,92,.55);color:#0a0a0f !important;text-decoration:none}

/* FAQ */
.ag-svc-faqs{display:flex;flex-direction:column;gap:10px;max-width:780px}
.ag-svc-faq{background:rgba(255,255,255,.03);border:1px solid rgba(212,180,92,.12);border-radius:12px;overflow:hidden;transition:all .3s ease}
.ag-svc-faq[open]{background:rgba(212,180,92,.05);border-color:rgba(212,180,92,.3)}
.ag-svc-faq-q{display:flex;justify-content:space-between;align-items:center;padding:18px 22px;cursor:pointer;color:#fff;font-weight:600;font-size:1rem;list-style:none;user-select:none}
.ag-svc-faq-q::-webkit-details-marker{display:none}
.ag-svc-faq[open] .ag-svc-faq-q{color:#D4B45C}
.ag-svc-faq-icon{display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:50%;background:rgba(212,180,92,.1);color:#D4B45C;font-size:1.1rem;font-weight:700;transition:transform .35s cubic-bezier(.16,1,.3,1)}
.ag-svc-faq[open] .ag-svc-faq-icon{transform:rotate(45deg);background:rgba(212,180,92,.25)}
.ag-svc-faq-a{padding:0 22px 22px;color:rgba(255,255,255,.78);line-height:1.7;font-size:.95rem}
</style>
<?php
// Cleanup pour pas polluer les autres get_template_part
unset( $GLOBALS['ag_service_detail'] );
