<section class="ag-section ag-section--image-luxe">
    <div class="ag-container">
        <span class="ag-tag ag-anim" data-anim="tag">Notre méthode</span>
        <h2 class="ag-section__title ag-anim" data-anim="title">Un process <em>éprouvé</em></h2>
        <p class="ag-section__desc ag-anim" data-anim="desc">4 étapes pour transformer votre vision en résultats concrets.</p>

        <div class="ag-process__grid">
            <?php
            $steps = [
                ['num' => '01', 'title' => 'Découverte', 'text' => 'Audit de votre situation, analyse de vos concurrents et définition de vos objectifs business.'],
                ['num' => '02', 'title' => 'Stratégie', 'text' => 'Plan d\'action sur-mesure avec roadmap claire, KPIs et priorisation des leviers de croissance.'],
                ['num' => '03', 'title' => 'Exécution', 'text' => 'Développement, création de contenu et mise en place des outils avec des sprints de livraison rapides.'],
                ['num' => '04', 'title' => 'Optimisation', 'text' => 'Suivi des performances, A/B testing et amélioration continue pour maximiser votre ROI.'],
            ];
            foreach ($steps as $s) :
            ?>
            <div class="ag-pstep ag-anim" data-anim="step">
                <div class="ag-pstep__num"><?php echo esc_html($s['num']); ?></div>
                <h3 class="ag-pstep__title"><?php echo esc_html($s['title']); ?></h3>
                <p class="ag-pstep__text"><?php echo esc_html($s['text']); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
