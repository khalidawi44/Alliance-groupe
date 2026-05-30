<section class="ag-section ag-section--graphite">
    <div class="ag-container">
        <span class="ag-tag ag-anim" data-anim="tag">Nos services</span>
        <h2 class="ag-section__title ag-anim" data-anim="title">Ce que nous faisons <em>de mieux</em></h2>
        <p class="ag-section__desc ag-anim" data-anim="desc">Audit de sécurité, création de sites qui inspirent confiance et maintenance — un seul interlocuteur, du conseil à la livraison.</p>

        <div class="ag-services__grid">
            <?php
            // SVG icônes stroke style Heroicons (champagne), remplacent les emojis
            $services = [
                [
                    'svg'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/></svg>',
                    'title' => 'Création Web',
                    'text'  => 'Sites vitrines et e-commerce performants, optimisés pour convertir vos visiteurs en clients.',
                    'link'  => home_url('/service-creation-web'),
                ],
                [
                    'svg'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="6" width="16" height="14" rx="3"/><path d="M9 2v4M15 2v4M12 13v2M9 10h.01M15 10h.01"/><circle cx="9" cy="15" r=".5" fill="currentColor"/><circle cx="15" cy="15" r=".5" fill="currentColor"/></svg>',
                    'title' => 'IA & Automatisation',
                    'text'  => 'Chatbots, automatisation des process, intégration d\'IA pour gagner du temps et de l\'argent.',
                    'link'  => home_url('/service-ia'),
                ],
                [
                    'svg'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/><path d="M8 11h6M11 8v6" opacity=".5"/></svg>',
                    'title' => 'SEO',
                    'text'  => 'Référencement naturel pour dominer les résultats Google et attirer un trafic qualifié.',
                    'link'  => home_url('/service-seo'),
                ],
                [
                    'svg'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11v2a1 1 0 0 0 1 1h2l5 4V6L6 10H4a1 1 0 0 0-1 1z"/><path d="M15.5 8.5a4 4 0 0 1 0 7M18.5 5.5a8 8 0 0 1 0 13"/></svg>',
                    'title' => 'Publicité Digitale',
                    'text'  => 'Campagnes Google Ads et Meta Ads avec un ROI mesurable et optimisé.',
                    'link'  => home_url('/service-publicite'),
                ],
                [
                    'svg'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a9 9 0 0 0-9 9c0 5 4 7 6 7h2a2 2 0 0 0 0-4 1 1 0 0 1 0-2h2.5a4.5 4.5 0 0 0 4.5-4.5C18 4.4 15.3 2 12 2z"/><circle cx="7" cy="11" r="1" fill="currentColor"/><circle cx="9" cy="6.5" r="1" fill="currentColor"/><circle cx="14" cy="6" r="1" fill="currentColor"/><circle cx="17" cy="10" r="1" fill="currentColor"/></svg>',
                    'title' => 'Branding',
                    'text'  => 'Identité visuelle forte et cohérente qui marque les esprits et inspire confiance.',
                    'link'  => home_url('/service-branding'),
                ],
                [
                    'svg'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21h6M10 17h4M7 13a5 5 0 0 1-2-4 7 7 0 1 1 14 0 5 5 0 0 1-2 4v3a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1z"/><path d="M12 6v6" opacity=".4"/></svg>',
                    'title' => 'Conseil Stratégique',
                    'text'  => 'Audit digital, stratégie de croissance et accompagnement sur-mesure.',
                    'link'  => home_url('/service-conseil'),
                ],
            ];
            foreach ($services as $s) :
            ?>
            <div class="ag-scard ag-anim" data-anim="card">
                <div class="ag-scard__icon ag-scard__icon--svg"><?php echo $s['svg']; // phpcs:ignore — SVG inline contrôlé ?></div>
                <h3 class="ag-scard__title"><?php echo esc_html($s['title']); ?></h3>
                <p class="ag-scard__text"><?php echo esc_html($s['text']); ?></p>
                <a href="<?php echo esc_url($s['link']); ?>" class="ag-btn-outline ag-scard__btn">Découvrir →</a>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="ag-services__cta-box ag-anim" data-anim="card">
            <div class="ag-services__cta-glow" aria-hidden="true"></div>
            <h3 class="ag-services__cta-title">Vous avez un <em>projet</em> en tête ?</h3>
            <p class="ag-services__cta-sub">Parlons-en directement, sans détour. Un appel de 30 minutes suffit pour clarifier votre besoin et identifier les meilleures solutions.</p>
            <div class="ag-services__cta-btns">
                <a href="<?php echo esc_url(home_url('/sur-mesure')); ?>" class="ag-btn-gold ag-btn-gold--xl">✦ Demander un devis gratuit →</a>
                <a href="<?php echo esc_url(home_url('/contact')); ?>" class="ag-btn-outline">✉️ Nous écrire</a>
                <a href="tel:+33744829516" class="ag-btn-outline">📞 07.44.82.95.16</a>
            </div>
        </div>
    </div>
</section>
