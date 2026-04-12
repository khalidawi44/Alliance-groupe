<section class="ag-section ag-section--image-marble">
    <div class="ag-container">
        <span class="ag-tag ag-anim" data-anim="tag">FAQ</span>
        <h2 class="ag-section__title ag-anim" data-anim="title">Questions <em>fréquentes</em></h2>
        <p class="ag-section__desc ag-anim" data-anim="desc" style="margin-left:auto;margin-right:auto;text-align:center;">Les réponses aux questions que vous vous posez sûrement.</p>

        <div class="ag-faq__list">
            <?php
            $faqs = [
                ['q' => 'Combien coûte un site web ?', 'a' => 'Chaque projet est unique. Nos sites vitrines démarrent à partir de 1 500 € et les projets e-commerce à partir de 3 500 €. Nous établissons un devis précis après avoir compris vos besoins.'],
                ['q' => 'Quels sont vos délais de livraison ?', 'a' => 'Un site vitrine est généralement livré en 2 à 4 semaines. Les projets plus complexes (e-commerce, plateformes) prennent 4 à 8 semaines. Nous travaillons en sprints avec des points réguliers.'],
                ['q' => 'Est-ce que vous gérez l\'hébergement ?', 'a' => 'Oui, nous proposons des solutions d\'hébergement premium sur o2switch avec maintenance, sauvegardes automatiques et certificat SSL inclus.'],
                ['q' => 'Comment l\'IA peut aider mon entreprise ?', 'a' => 'L\'IA peut automatiser vos réponses clients (chatbots), analyser vos données, personnaliser l\'expérience utilisateur et optimiser vos campagnes publicitaires pour un meilleur ROI.'],
                ['q' => 'Proposez-vous un suivi après la mise en ligne ?', 'a' => 'Absolument. Nous proposons des contrats de maintenance et d\'accompagnement mensuel incluant mises à jour, optimisations SEO, reporting et support technique.'],
                ['q' => 'Comment prendre rendez-vous ?', 'a' => 'Vous pouvez nous contacter par téléphone au 06.23.52.60.74, par email à contact@alliancegroupe-inc.com, ou réserver directement un créneau via notre page contact.'],
            ];
            foreach ($faqs as $faq) :
            ?>
            <div class="ag-faq-item ag-anim" data-anim="faq-item">
                <button class="ag-faq-q" type="button">
                    <span><?php echo esc_html($faq['q']); ?></span>
                    <span class="ag-faq-icon">+</span>
                </button>
                <div class="ag-faq-a">
                    <p><?php echo esc_html($faq['a']); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<script type="application/ld+json">
<?php
$faq_schema = array(
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => array(),
);
$faqs_data = array(
    array('q' => 'Combien coûte un site web ?', 'a' => 'Nos sites vitrines démarrent à partir de 1 500 € et les projets e-commerce à partir de 3 500 €. Nous établissons un devis précis après avoir compris vos besoins.'),
    array('q' => 'Quels sont vos délais de livraison ?', 'a' => 'Un site vitrine est généralement livré en 2 à 4 semaines. Les projets plus complexes prennent 4 à 8 semaines.'),
    array('q' => 'Est-ce que vous gérez l\'hébergement ?', 'a' => 'Oui, nous proposons des solutions d\'hébergement premium sur o2switch avec maintenance, sauvegardes automatiques et certificat SSL inclus.'),
    array('q' => 'Comment l\'IA peut aider mon entreprise ?', 'a' => 'L\'IA peut automatiser vos réponses clients, analyser vos données, personnaliser l\'expérience utilisateur et optimiser vos campagnes publicitaires.'),
    array('q' => 'Proposez-vous un suivi après la mise en ligne ?', 'a' => 'Absolument. Nous proposons des contrats de maintenance et d\'accompagnement mensuel incluant mises à jour, optimisations SEO et support technique.'),
    array('q' => 'Comment prendre rendez-vous ?', 'a' => 'Vous pouvez nous contacter par téléphone au 06.23.52.60.74, par email à contact@alliancegroupe-inc.com, ou réserver directement un créneau via notre page contact.'),
);
foreach ( $faqs_data as $f ) {
    $faq_schema['mainEntity'][] = array(
        '@type' => 'Question',
        'name' => $f['q'],
        'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $f['a'] ),
    );
}
echo wp_json_encode( $faq_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
?>
</script>
