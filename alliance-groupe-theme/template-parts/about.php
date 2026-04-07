<section class="ag-section" style="background:#0c0c0f;">
    <div class="ag-container">
        <span class="ag-tag ag-anim" data-anim="tag">À propos</span>
        <h2 class="ag-section__title ag-anim" data-anim="title">Qui sommes-<em>nous</em></h2>

        <div class="ag-about__content">
            <div>
                <p class="ag-about__text ag-anim" data-anim="desc">
                    Alliance Groupe est une agence web & IA basée en France. Nous accompagnons les entreprises ambitieuses dans leur transformation digitale avec une approche centrée sur les résultats.
                </p>
                <p class="ag-about__text ag-anim" data-anim="desc">
                    Notre expertise combine design premium, développement performant et intelligence artificielle pour créer des expériences digitales qui convertissent.
                </p>
            </div>

            <div class="ag-valeurs__grid">
                <?php
                $valeurs = [
                    ['icon' => '🎯', 'title' => 'Résultats', 'text' => 'Chaque projet est piloté par des KPIs concrets et mesurables.'],
                    ['icon' => '⚡', 'title' => 'Performance', 'text' => 'Sites ultra-rapides, code optimisé, expérience utilisateur fluide.'],
                    ['icon' => '🤝', 'title' => 'Transparence', 'text' => 'Communication claire, reporting régulier, pas de jargon inutile.'],
                    ['icon' => '🚀', 'title' => 'Innovation', 'text' => 'IA, automatisation et technologies de pointe au service de votre croissance.'],
                ];
                foreach ($valeurs as $v) :
                ?>
                <div class="ag-valeur ag-anim" data-anim="valeur">
                    <div class="ag-valeur__icon"><?php echo $v['icon']; ?></div>
                    <h3 class="ag-valeur__title"><?php echo esc_html($v['title']); ?></h3>
                    <p class="ag-valeur__text"><?php echo esc_html($v['text']); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
