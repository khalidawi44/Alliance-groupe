<section class="ag-section ag-section--graphite">
    <div class="ag-container">
        <span class="ag-tag ag-anim" data-anim="tag">Nos services</span>
        <h2 class="ag-section__title ag-anim" data-anim="title">Ce que nous faisons <em>de mieux</em></h2>
        <p class="ag-section__desc ag-anim" data-anim="desc">Des solutions digitales complètes pour transformer votre présence en ligne en machine à générer des leads.</p>

        <div class="ag-services__grid">
            <?php
            $services = [
                [
                    'icon'  => '🌐',
                    'title' => 'Création Web',
                    'text'  => 'Sites vitrines et e-commerce performants, optimisés pour convertir vos visiteurs en clients.',
                    'link'  => home_url('/service-creation-web'),
                ],
                [
                    'icon'  => '🤖',
                    'title' => 'IA & Automatisation',
                    'text'  => 'Chatbots, automatisation des process, intégration d\'IA pour gagner du temps et de l\'argent.',
                    'link'  => home_url('/service-ia'),
                ],
                [
                    'icon'  => '🔍',
                    'title' => 'SEO',
                    'text'  => 'Référencement naturel pour dominer les résultats Google et attirer un trafic qualifié.',
                    'link'  => home_url('/service-seo'),
                ],
                [
                    'icon'  => '📢',
                    'title' => 'Publicité Digitale',
                    'text'  => 'Campagnes Google Ads et Meta Ads avec un ROI mesurable et optimisé.',
                    'link'  => home_url('/service-publicite'),
                ],
                [
                    'icon'  => '🎨',
                    'title' => 'Branding',
                    'text'  => 'Identité visuelle forte et cohérente qui marque les esprits et inspire confiance.',
                    'link'  => home_url('/service-branding'),
                ],
                [
                    'icon'  => '💡',
                    'title' => 'Conseil Stratégique',
                    'text'  => 'Audit digital, stratégie de croissance et accompagnement sur-mesure.',
                    'link'  => home_url('/service-conseil'),
                ],
            ];
            foreach ($services as $s) :
            ?>
            <div class="ag-scard ag-anim" data-anim="card">
                <div class="ag-scard__icon"><?php echo $s['icon']; ?></div>
                <h3 class="ag-scard__title"><?php echo esc_html($s['title']); ?></h3>
                <p class="ag-scard__text"><?php echo esc_html($s['text']); ?></p>
                <a href="<?php echo esc_url($s['link']); ?>" class="ag-scard__arrow">En savoir plus →</a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
