<?php
/**
 * Template-part shared by the 5 profession pages.
 *
 * Expects $ag_metier set via set_query_var('ag_metier', $data) from
 * the parent page template.
 *
 * @package Alliance_Groupe
 */

$ag_metier = get_query_var( 'ag_metier' );
if ( ! is_array( $ag_metier ) || empty( $ag_metier['slug'] ) ) {
    return;
}

$dl_base   = get_stylesheet_directory_uri() . '/assets/downloads/';

// Load the payment link URLs from options (same as page-templates.php).
$ag_stripe_placeholder    = 'STRIPE_PLACEHOLDER';
$ag_stripe_premium      = get_option( 'ag_stripe_premium_url', $ag_stripe_placeholder );

$ag_stripe_business = get_option( 'ag_stripe_business_url', $ag_stripe_placeholder );

$contact_base = home_url( '/contact' );
$premium_url      = ( $ag_stripe_premium      !== $ag_stripe_placeholder ) ? $ag_stripe_premium  : add_query_arg( array( 'pack' => 'premium', 'metier' => $ag_metier['slug'] ), $contact_base );

$business_url = ( $ag_stripe_business !== $ag_stripe_placeholder ) ? $ag_stripe_business : add_query_arg( array( 'pack' => 'business', 'metier' => $ag_metier['slug'] ), $contact_base );

$premium_target      = ( $ag_stripe_premium      !== $ag_stripe_placeholder ) ? ' target="_blank" rel="noopener"' : '';

$business_target = ( $ag_stripe_business !== $ag_stripe_placeholder ) ? ' target="_blank" rel="noopener"' : '';

$premium_label      = ( $ag_stripe_premium      !== $ag_stripe_placeholder ) ? 'Payer le Premium via PayPal →'  : 'Acheter le Premium';

$business_label = ( $ag_stripe_business !== $ag_stripe_placeholder ) ? 'Payer le Premium via PayPal →' : 'Acheter le Premium';

// Image de présentation : on privilégie une VRAIE capture du métier
// (assets/images/templates/<slug>/) ; sinon l'aperçu du thème (screenshot.png).
$ag_cover_gal   = function_exists( 'ag_template_gallery_images' ) ? ag_template_gallery_images( $ag_metier['slug'] ) : array();
$screenshot_url = ! empty( $ag_cover_gal )
    ? $ag_cover_gal[0]['url']
    : get_stylesheet_directory_uri() . '/assets/downloads/' . $ag_metier['slug_full'] . '/screenshot.png';
$screenshot_file = get_stylesheet_directory() . '/assets/downloads/' . $ag_metier['slug_full'] . '/screenshot.png';
$has_screenshot  = ! empty( $ag_cover_gal ) || file_exists( $screenshot_file );
?>

<main id="ag-main-content">

    <!-- Hero métier -->
    <section class="ag-hero" style="min-height:60vh;">
        <div class="ag-hero__bg">
            <div class="ag-hero__orb ag-hero__orb--1"></div>
        </div>
        <div class="ag-hero__content" style="max-width:820px;">
            <span class="ag-tag"><?php echo $ag_metier['icon']; // phpcs:ignore ?> <?php echo esc_html( $ag_metier['name'] ); ?></span>
            <h1 class="ag-hero__title">
                <span class="ag-line"><?php echo wp_kses_post( $ag_metier['hero_title'] ); ?></span>
            </h1>
            <p class="ag-hero__sub"><?php echo esc_html( $ag_metier['hero_subtitle'] ); ?></p>
            <div class="ag-hero__buttons" style="margin-top:20px;">
                <a href="#ag-configurator" class="ag-btn-gold">Choisir mon pack →</a>
                <a href="<?php echo esc_url( home_url( '/templates-wordpress' ) ); ?>" class="ag-btn-outline">← Tous les métiers</a>
            </div>
        </div>
    </section>

    <!-- Présentation du thème -->
    <section class="ag-section ag-section--graphite">
        <div class="ag-container">
            <span class="ag-tag ag-anim" data-anim="tag">Le thème en détail</span>
            <h2 class="ag-section__title ag-anim" data-anim="title">Ce que contient le thème <em>gratuit</em></h2>
            <p class="ag-section__desc ag-anim" data-anim="desc"><?php echo esc_html( $ag_metier['description_long'] ); ?></p>

            <div style="display:grid;grid-template-columns:<?php echo $has_screenshot ? '1fr 1fr' : '1fr'; ?>;gap:40px;max-width:1100px;margin:40px auto 0;align-items:start;">
                <?php if ( $has_screenshot ) : ?>
                <div>
                    <img src="<?php echo esc_url( $screenshot_url ); ?>"
                         alt="<?php echo esc_attr( sprintf( 'Aperçu du thème AG Starter %s', $ag_metier['name'] ) ); ?>"
                         loading="lazy"
                         style="width:100%;height:auto;border-radius:8px;border:1px solid rgba(212,180,92,.2);box-shadow:0 20px 50px rgba(0,0,0,.5);">
                </div>
                <?php endif; ?>

                <div>
                    <h3 style="font-size:1.2rem;color:#D4B45C;margin-bottom:18px;text-transform:uppercase;letter-spacing:1px;">✓ Inclus dans la version gratuite</h3>
                    <ul class="ag-metier-features-list" style="list-style:none;padding:0;margin:0 0 28px 0;">
                        <?php foreach ( $ag_metier['free_features'] as $feat ) : ?>
                        <li class="ag-metier-features-list__item" style="padding:10px 0 10px 30px;position:relative;border-bottom:1px solid rgba(255,255,255,.05);color:#e8e6e0;font-size:.95rem;">
                            <span style="position:absolute;left:0;color:#28a745;font-weight:700;">✓</span>
                            <?php echo wp_kses_post( $feat ); ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>

                    <div style="padding:16px 20px;background:rgba(40,167,69,.08);border:1px solid rgba(40,167,69,.25);border-radius:8px;">
                        <p style="color:#b3e6c0;font-size:.92rem;margin:0;line-height:1.6;">
                            <strong style="color:#28a745;">💡 100% gratuit, 100% français.</strong> Aucun plugin requis, aucune limite, installation en 2 minutes.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php
    // Galerie d'aperçus réels du template (captures dans assets/images/templates/<slug>/).
    $ag_gallery = function_exists( 'ag_template_gallery_images' ) ? ag_template_gallery_images( $ag_metier['slug'] ) : array();
    if ( ! empty( $ag_gallery ) ) : ?>
    <section class="ag-section ag-section--graphite" id="ag-apercu">
        <div class="ag-container">
            <span class="ag-tag ag-anim" data-anim="tag">Aperçu réel</span>
            <h2 class="ag-section__title ag-anim" data-anim="title">À quoi ressemble le thème <em><?php echo esc_html( $ag_metier['name'] ); ?></em></h2>
            <div class="ag-tpl-gallery" id="ag-gal">
                <?php foreach ( $ag_gallery as $i => $img ) : ?>
                    <a class="ag-tpl-gallery__item" data-i="<?php echo (int) $i; ?>" href="<?php echo esc_url( $img['url'] ); ?>">
                        <img src="<?php echo esc_url( $img['url'] ); ?>" alt="<?php echo esc_attr( sprintf( 'Aperçu %s — %s', $ag_metier['name'], $img['label'] ) ); ?>" loading="lazy">
                        <span class="ag-tpl-gallery__cap"><?php echo esc_html( $img['label'] ); ?></span>
                        <span class="ag-tpl-gallery__zoom" aria-hidden="true">🔍</span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Visionneuse (lightbox) -->
    <div class="ag-lb" id="ag-lb" aria-hidden="true" role="dialog" aria-label="Aperçu du thème">
        <button class="ag-lb__btn ag-lb__close" type="button" aria-label="Fermer">&times;</button>
        <button class="ag-lb__btn ag-lb__prev" type="button" aria-label="Image précédente">&lsaquo;</button>
        <button class="ag-lb__btn ag-lb__next" type="button" aria-label="Image suivante">&rsaquo;</button>
        <figure class="ag-lb__stage"><img class="ag-lb__img" src="" alt="" draggable="false"></figure>
        <div class="ag-lb__bar"><span class="ag-lb__cap"></span> <span class="ag-lb__count"></span> <span class="ag-lb__hint">· cliquez l'image pour zoomer · ←/→ naviguer · Échap fermer</span></div>
    </div>

    <style>
    .ag-tpl-gallery{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:18px;max-width:1180px;margin:40px auto 0}
    .ag-tpl-gallery__item{display:block;position:relative;border-radius:14px;overflow:hidden;border:1px solid rgba(212,180,92,.22);background:#0e0e14;text-decoration:none;cursor:zoom-in;transition:transform .35s cubic-bezier(.16,1,.3,1),border-color .3s,box-shadow .3s}
    .ag-tpl-gallery__item:hover{transform:translateY(-4px);border-color:rgba(212,180,92,.55);box-shadow:0 18px 50px rgba(0,0,0,.45)}
    .ag-tpl-gallery__item img{display:block;width:100%;height:auto}
    .ag-tpl-gallery__cap{position:absolute;left:0;right:0;bottom:0;padding:10px 14px;font-size:.82rem;font-weight:700;color:#fff;background:linear-gradient(180deg,transparent,rgba(0,0,0,.8))}
    .ag-tpl-gallery__zoom{position:absolute;top:10px;right:10px;width:34px;height:34px;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,.5);border-radius:50%;font-size:1rem;opacity:0;transition:opacity .25s}
    .ag-tpl-gallery__item:hover .ag-tpl-gallery__zoom{opacity:1}
    /* Lightbox */
    .ag-lb{position:fixed;inset:0;z-index:100000;background:rgba(6,6,10,.94);display:none;align-items:center;justify-content:center;-webkit-backdrop-filter:blur(4px);backdrop-filter:blur(4px)}
    .ag-lb.is-open{display:flex}
    .ag-lb__stage{margin:0;max-width:92vw;max-height:82vh;display:flex;align-items:center;justify-content:center;overflow:hidden}
    .ag-lb__img{max-width:92vw;max-height:82vh;width:auto;height:auto;border-radius:8px;box-shadow:0 24px 80px rgba(0,0,0,.6);cursor:zoom-in;transform:scale(1);transition:transform .25s ease}
    .ag-lb__img.is-zoom{cursor:move;transform:scale(2.1)}
    .ag-lb__btn{position:fixed;background:rgba(20,20,28,.7);border:1px solid rgba(212,180,92,.5);color:#F3D27A;width:52px;height:52px;border-radius:50%;font-size:2rem;line-height:1;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background .2s,transform .2s;z-index:2}
    .ag-lb__btn:hover{background:rgba(212,180,92,.25)}
    .ag-lb__close{top:20px;right:24px}
    .ag-lb__prev{left:24px;top:50%;transform:translateY(-50%)}
    .ag-lb__next{right:24px;top:50%;transform:translateY(-50%)}
    .ag-lb__prev:hover{transform:translateY(-50%) scale(1.08)}
    .ag-lb__next:hover{transform:translateY(-50%) scale(1.08)}
    .ag-lb__bar{position:fixed;bottom:16px;left:0;right:0;text-align:center;color:#e8e6e0;font-size:.9rem;padding:0 16px}
    .ag-lb__count{color:#F3D27A;font-weight:700}
    .ag-lb__hint{color:rgba(255,255,255,.45);font-size:.82rem}
    @media(max-width:600px){.ag-lb__btn{width:44px;height:44px;font-size:1.6rem}.ag-lb__hint{display:none}.ag-lb__img.is-zoom{transform:scale(1.8)}}
    </style>
    <script>
    (function(){
        var gal=document.getElementById('ag-gal'), lb=document.getElementById('ag-lb');
        if(!gal||!lb) return;
        var items=[].slice.call(gal.querySelectorAll('.ag-tpl-gallery__item'));
        var data=items.map(function(a){var c=a.querySelector('.ag-tpl-gallery__cap');return {url:a.getAttribute('href'),cap:c?c.textContent:''};});
        var img=lb.querySelector('.ag-lb__img'), cap=lb.querySelector('.ag-lb__cap'), cnt=lb.querySelector('.ag-lb__count');
        var idx=0;
        function show(i){idx=(i+data.length)%data.length;img.classList.remove('is-zoom');img.style.transformOrigin='center';img.src=data[idx].url;img.alt=data[idx].cap;cap.textContent=data[idx].cap;cnt.textContent=(idx+1)+' / '+data.length;}
        function open(i){show(i);lb.classList.add('is-open');lb.setAttribute('aria-hidden','false');document.body.style.overflow='hidden';}
        function close(){lb.classList.remove('is-open');lb.setAttribute('aria-hidden','true');document.body.style.overflow='';img.classList.remove('is-zoom');}
        items.forEach(function(a,i){a.addEventListener('click',function(e){e.preventDefault();open(i);});});
        lb.querySelector('.ag-lb__close').addEventListener('click',function(e){e.stopPropagation();close();});
        lb.querySelector('.ag-lb__prev').addEventListener('click',function(e){e.stopPropagation();show(idx-1);});
        lb.querySelector('.ag-lb__next').addEventListener('click',function(e){e.stopPropagation();show(idx+1);});
        lb.addEventListener('click',function(e){if(e.target===lb||e.target.classList.contains('ag-lb__stage'))close();});
        img.addEventListener('click',function(e){e.stopPropagation();img.classList.toggle('is-zoom');});
        img.addEventListener('mousemove',function(e){if(!img.classList.contains('is-zoom'))return;var r=img.getBoundingClientRect();img.style.transformOrigin=((e.clientX-r.left)/r.width*100)+'% '+((e.clientY-r.top)/r.height*100)+'%';});
        document.addEventListener('keydown',function(e){if(!lb.classList.contains('is-open'))return;if(e.key==='Escape')close();else if(e.key==='ArrowLeft')show(idx-1);else if(e.key==='ArrowRight')show(idx+1);});
    })();
    </script>
    <?php endif; ?>

    <!-- Configurateur "Je choisis mon pack" -->
    <section class="ag-section ag-section--marbre" id="ag-configurator">
        <div class="ag-container">
            <span class="ag-tag ag-anim" data-anim="tag">Je choisis mon pack</span>
            <h2 class="ag-section__title ag-anim" data-anim="title">Quel niveau d'<em>ambition</em> ?</h2>
            <p class="ag-section__desc ag-anim" data-anim="desc">Cliquez sur le niveau qui vous correspond pour voir le détail et passer à l'action.</p>

            <div class="ag-cfg" data-ag-cfg>
                <div class="ag-cfg__tiles" role="tablist">
                    <button type="button" class="ag-cfg__tile ag-cfg__tile--free is-active" data-tier="free" role="tab" aria-selected="true">
                        <span class="ag-cfg__tile-icon">🆓</span>
                        <strong class="ag-cfg__tile-name">Je démarre</strong>
                        <span class="ag-cfg__tile-price">Gratuit</span>
                    </button>
                    <?php $ag_pp = function_exists( 'ag_creator_price' ) ? (int) ag_creator_price() : 69; ?>
                    <button type="button" class="ag-cfg__tile ag-cfg__tile--premium" data-tier="premium" role="tab" aria-selected="false">
                        <span class="ag-cfg__tile-icon">💎</span>
                        <strong class="ag-cfg__tile-name">Je veux le Premium</strong>
                        <span class="ag-cfg__tile-price"><?php echo (int) $ag_pp; ?>€</span>
                    </button>
                </div>

                <!-- Panels -->
                <div class="ag-cfg__panels">

                    <!-- Free -->
                    <div class="ag-cfg__panel is-active" data-tier="free" role="tabpanel">
                        <div class="ag-cfg__panel-head">
                            <h3>🆓 Thème <?php echo esc_html( $ag_metier['name'] ); ?> gratuit</h3>
                            <span class="ag-cfg__panel-price" style="color:#28a745;">0€</span>
                        </div>
                        <p class="ag-cfg__panel-sub">Le thème WordPress de base, déjà fonctionnel. Parfait pour démarrer sans budget.</p>
                        <ul class="ag-cfg__features">
                            <?php foreach ( $ag_metier['free_features'] as $feat ) : ?>
                            <li><?php echo wp_kses_post( $feat ); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="ag-btn-gold ag-dl-trigger" style="background:#28a745;width:100%;max-width:460px;display:block;margin:24px auto 0;text-align:center;justify-content:center;" data-template="<?php echo esc_attr( $ag_metier['slug'] ); ?>" data-file="<?php echo esc_url( $dl_base . $ag_metier['slug_full'] . '.zip' ); ?>">
                            Télécharger gratuitement →
                        </button>
                        <p class="ag-cfg__panel-note">Aucune carte bancaire demandée. Juste un email pour vous envoyer le ZIP.</p>
                    </div>

                    <!-- Premium -->
                    <div class="ag-cfg__panel" data-tier="premium" role="tabpanel">
                        <div class="ag-cfg__panel-head">
                            <h3>💎 Pack Premium pour <?php echo esc_html( $ag_metier['name'] ); ?></h3>
                            <span class="ag-cfg__panel-price"><?php echo (int) $ag_pp; ?>€ une fois</span>
                        </div>
                        <p class="ag-cfg__panel-sub">Notre design le plus abouti : le thème basique passe en site professionnel finalisé.</p>
                        <ul class="ag-cfg__features">
                            <?php foreach ( $ag_metier['premium_features'] as $feat ) : ?>
                            <li><?php echo wp_kses_post( $feat ); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <a href="<?php echo esc_url( $business_url ); ?>"<?php echo $business_target; // phpcs:ignore ?> class="ag-btn-gold" style="width:100%;max-width:460px;display:block;margin:24px auto 0;text-align:center;justify-content:center;">
                            Choisir le Premium — <?php echo (int) $ag_pp; ?>€ →
                        </a>
                        <p class="ag-cfg__panel-note">Paiement unique, pas d'abonnement. Compatible avec tous les thèmes AG Starter.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Avertissement sur-mesure ciblé métier -->
    <section class="ag-section ag-section--or" style="padding:100px 0;">
        <div class="ag-container">
            <div style="max-width:900px;margin:0 auto;padding:48px 40px;background:linear-gradient(135deg,rgba(212,180,92,.10) 0%,rgba(20,20,22,.6) 100%);border:2px solid rgba(212,180,92,.4);border-radius:20px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,transparent,#D4B45C,transparent);"></div>

                <div style="text-align:center;">
                    <span style="display:inline-block;padding:6px 18px;background:rgba(212,180,92,.2);border:1px solid rgba(212,180,92,.5);border-radius:100px;color:#D4B45C;font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;margin-bottom:16px;">💎 Et si vous alliez plus loin ?</span>
                    <h2 style="font-size:clamp(1.6rem,3vw,2.2rem);margin-bottom:14px;line-height:1.2;">Votre <?php echo esc_html( $ag_metier['audience_short'] ); ?> mérite plus qu'un template</h2>
                    <p style="font-size:1.05rem;color:#e8e6e0;max-width:720px;margin:0 auto 24px;line-height:1.7;"><?php echo esc_html( $ag_metier['upsell_text'] ); ?></p>

                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,160px),1fr));gap:16px;margin:28px 0;max-width:640px;margin-left:auto;margin-right:auto;">
                        <div style="padding:16px 10px;background:rgba(255,255,255,.03);border:1px solid rgba(212,180,92,.2);border-radius:10px;">
                            <div style="font-size:1.8rem;font-weight:800;color:#D4B45C;">48 h</div>
                            <div style="color:#b0b0bc;font-size:.82rem;">Audit rendu</div>
                        </div>
                        <div style="padding:16px 10px;background:rgba(255,255,255,.03);border:1px solid rgba(212,180,92,.2);border-radius:10px;">
                            <div style="font-size:1.8rem;font-weight:800;color:#D4B45C;">1</div>
                            <div style="color:#b0b0bc;font-size:.82rem;">Interlocuteur</div>
                        </div>
                        <div style="padding:16px 10px;background:rgba(255,255,255,.03);border:1px solid rgba(212,180,92,.2);border-radius:10px;">
                            <div style="font-size:1.8rem;font-weight:800;color:#D4B45C;">0€</div>
                            <div style="color:#b0b0bc;font-size:.82rem;">Premier appel</div>
                        </div>
                    </div>

                    <div class="ag-hero__buttons" style="justify-content:center;flex-wrap:wrap;">
                        <a href="tel:+33744829516" class="ag-btn-gold">📞 Appeler Fabrizio</a>
                        <a href="<?php echo esc_url( add_query_arg( array( 'source' => 'metier', 'metier' => $ag_metier['slug'] ), $contact_base ) ); ?>" class="ag-btn-outline">Réserver un appel →</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

</main>

<?php /* Configurator JS + CSS scoped to the page */ ?>
<style>
.ag-cfg{max-width:1100px;margin:40px auto 0;}
.ag-cfg__tiles{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-bottom:32px;}
.ag-cfg__tile{display:flex;flex-direction:column;align-items:center;gap:6px;padding:18px 14px;background:rgba(255,255,255,.025);border:2px solid rgba(212,180,92,.15);border-radius:12px;color:#e8e6e0;cursor:pointer;transition:border-color .25s,background .25s,transform .2s;font-family:inherit;text-align:center;}
.ag-cfg__tile:hover{border-color:rgba(212,180,92,.4);background:rgba(212,180,92,.05);transform:translateY(-2px);}
.ag-cfg__tile.is-active{border-color:#D4B45C;background:rgba(212,180,92,.10);box-shadow:0 10px 30px rgba(212,180,92,.15);}
.ag-cfg__tile--free.is-active{border-color:#28a745;background:rgba(40,167,69,.10);box-shadow:0 10px 30px rgba(40,167,69,.15);}
.ag-cfg__tile-icon{font-size:1.8rem;}
.ag-cfg__tile-name{font-size:.92rem;color:#fff;font-weight:700;}
.ag-cfg__tile-price{font-size:.82rem;color:#D4B45C;font-weight:700;}
.ag-cfg__tile--free .ag-cfg__tile-price{color:#28a745;}
.ag-cfg__panels{position:relative;}
.ag-cfg__panel{display:none;padding:36px 32px;background:rgba(255,255,255,.02);border:1px solid rgba(212,180,92,.2);border-radius:14px;animation:ag-cfg-fade .3s ease;}
.ag-cfg__panel.is-active{display:block;}
@keyframes ag-cfg-fade{from{opacity:0;transform:translateY(6px);}to{opacity:1;transform:translateY(0);}}
.ag-cfg__panel-head{display:flex;align-items:baseline;justify-content:space-between;gap:14px;flex-wrap:wrap;margin-bottom:8px;padding-bottom:14px;border-bottom:1px solid rgba(212,180,92,.15);}
.ag-cfg__panel-head h3{margin:0;color:#fff;font-size:1.3rem;}
.ag-cfg__panel-price{color:#D4B45C;font-weight:800;font-size:1.1rem;}
.ag-cfg__panel-sub{color:#b0b0bc;font-size:.95rem;line-height:1.6;margin:14px 0 18px;}
.ag-cfg__features{list-style:none;padding:0;margin:0;}
.ag-cfg__features li{padding:10px 0 10px 28px;position:relative;color:#e8e6e0;font-size:.92rem;border-bottom:1px solid rgba(255,255,255,.05);line-height:1.6;}
.ag-cfg__features li::before{content:"✓";color:#D4B45C;position:absolute;left:0;font-weight:700;font-size:1.05rem;}
.ag-cfg__panel-note{text-align:center;color:#888;font-size:.82rem;margin-top:14px;font-style:italic;}
@media (max-width:640px){
    .ag-cfg__tiles{grid-template-columns:1fr 1fr;}
    .ag-cfg__tile{padding:14px 10px;}
    .ag-cfg__tile-icon{font-size:1.5rem;}
    .ag-cfg__panel{padding:24px 18px;}
    .ag-cfg__panel-head h3{font-size:1.1rem;}
}
</style>

<script>
(function(){
    var cfg = document.querySelector('[data-ag-cfg]');
    if (!cfg) return;
    var tiles  = cfg.querySelectorAll('.ag-cfg__tile');
    var panels = cfg.querySelectorAll('.ag-cfg__panel');
    tiles.forEach(function(tile){
        tile.addEventListener('click', function(){
            var tier = tile.getAttribute('data-tier');
            tiles.forEach(function(t){
                t.classList.remove('is-active');
                t.setAttribute('aria-selected','false');
            });
            tile.classList.add('is-active');
            tile.setAttribute('aria-selected','true');
            panels.forEach(function(p){
                p.classList.toggle('is-active', p.getAttribute('data-tier') === tier);
            });
        });
    });
})();
</script>

<?php get_template_part( 'template-parts/download-modal' ); ?>
