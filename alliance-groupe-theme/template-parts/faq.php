<section class="ag-section ag-section--image-marble ag-rise">
    <div class="ag-container">
        <span class="ag-tag ag-anim" data-anim="tag">FAQ</span>
        <h2 class="ag-section__title ag-anim" data-anim="title">Questions <em>fréquentes</em></h2>
        <p class="ag-section__desc ag-anim" data-anim="desc" style="margin-left:auto;margin-right:auto;text-align:center;">Les réponses aux questions que vous vous posez sûrement.</p>

        <div class="ag-faq__list">
            <?php
            $faqs = [
                ['q' => 'C\'est quoi un audit de sécurité, concrètement ?', 'a' => 'Je passe votre site au crible (HTTPS, fichiers exposés, xmlrpc, en-têtes, version du CMS, comptes énumérables…) et je vous remets sous 48 h un rapport clair, sans jargon, avec les failles classées par gravité et comment les corriger.'],
                ['q' => 'Mon site est petit/récent, suis-je vraiment une cible ?', 'a' => 'Oui. Les attaques sont automatisées : des robots scannent le web 24h/24 à la recherche de failles connues, sans cibler personne en particulier. Près de la moitié des attaques visent les petites structures, justement parce qu\'elles sont moins protégées.'],
                ['q' => 'Combien coûte un site ?', 'a' => 'Les sites « Express » démarrent à 490 €, payables en 4× sans frais, sécurisés dès le départ. Pour un projet sur-mesure, je vous établis un devis clair après avoir compris votre besoin.'],
                ['q' => 'Et la maintenance, ça comprend quoi ?', 'a' => 'Mises à jour, sauvegardes, surveillance et sécurité — votre site reste sain chaque mois, sans y penser. À partir de 49 €/mois, sans engagement, résiliable à tout moment.'],
                ['q' => 'Vous touchez à mon site sans mon accord ?', 'a' => 'Non. Le diagnostic est non-intrusif : je lis uniquement ce qui est public, comme un visiteur. Un audit approfondi (qui sonde davantage) n\'est réalisé qu\'avec votre autorisation écrite. Tout est transparent.'],
                ['q' => 'Comment vous joindre ?', 'a' => 'Par téléphone au 07.44.82.95.16, par email à contact@alliancegroupe-inc.com, ou via la page contact. Un seul interlocuteur : moi.'],
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
    array('q' => 'C\'est quoi un audit de sécurité, concrètement ?', 'a' => 'Je passe votre site au crible (HTTPS, fichiers exposés, xmlrpc, en-têtes, version du CMS, comptes énumérables…) et je vous remets sous 48 h un rapport clair, sans jargon, avec les failles classées par gravité et comment les corriger.'),
    array('q' => 'Mon site est petit/récent, suis-je vraiment une cible ?', 'a' => 'Oui. Les attaques sont automatisées : des robots scannent le web 24h/24 à la recherche de failles connues. Près de la moitié des attaques visent les petites structures, justement parce qu\'elles sont moins protégées.'),
    array('q' => 'Combien coûte un site ?', 'a' => 'Les sites « Express » démarrent à 490 €, payables en 4× sans frais, sécurisés dès le départ. Pour un projet sur-mesure, je vous établis un devis clair après avoir compris votre besoin.'),
    array('q' => 'Et la maintenance, ça comprend quoi ?', 'a' => 'Mises à jour, sauvegardes, surveillance et sécurité — votre site reste sain chaque mois. À partir de 49 €/mois, sans engagement, résiliable à tout moment.'),
    array('q' => 'Vous touchez à mon site sans mon accord ?', 'a' => 'Non. Le diagnostic est non-intrusif : je lis uniquement ce qui est public, comme un visiteur. Un audit approfondi n\'est réalisé qu\'avec votre autorisation écrite.'),
    array('q' => 'Comment vous joindre ?', 'a' => 'Par téléphone au 07.44.82.95.16, par email à contact@alliancegroupe-inc.com, ou via la page contact. Un seul interlocuteur : moi.'),
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
