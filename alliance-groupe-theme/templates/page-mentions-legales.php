<?php
/**
 * Template Name: Mentions légales & CGV
 *
 * Page légale regroupant : mentions légales, conditions générales de vente,
 * politique de remboursement, droit de rétractation, responsabilité, données.
 */
get_header();
?>

<main id="ag-main-content">

    <section class="ag-hero" style="min-height:40vh;">
        <div class="ag-hero__bg">
            <div class="ag-hero__orb ag-hero__orb--1"></div>
        </div>
        <div class="ag-hero__content">
            <span class="ag-tag ag-anim" data-anim="tag">Informations légales</span>
            <h1 class="ag-hero__title">
                <span class="ag-line">Mentions légales</span>
                <span class="ag-line"><em>& Conditions générales</em></span>
            </h1>
            <p class="ag-hero__sub">Transparence, engagements, garanties — tout ce que vous devez savoir avant et après notre collaboration.</p>
        </div>
    </section>

    <section class="ag-section ag-section--graphite">
        <div class="ag-container">
            <div class="ag-cookies-page">

                <!-- Sommaire -->
                <div style="background:rgba(212,180,92,.06);border:1px solid rgba(212,180,92,.25);border-radius:14px;padding:22px 26px;margin:0 0 40px;">
                    <h2 style="margin-top:0;font-size:1.1rem;color:#d4b45c;">Sommaire</h2>
                    <ul style="margin:10px 0 0;">
                        <li><a href="#mentions">1. Mentions légales</a></li>
                        <li><a href="#cgv">2. Conditions générales de vente</a></li>
                        <li><a href="#remboursement">3. Politique de remboursement & rétractation</a></li>
                        <li><a href="#responsabilite">4. Responsabilité & garanties</a></li>
                        <li><a href="#propriete">5. Propriété intellectuelle</a></li>
                        <li><a href="#donnees">6. Données personnelles & cookies</a></li>
                        <li><a href="#litiges">7. Litiges & droit applicable</a></li>
                    </ul>
                </div>

                <!-- 1. Mentions légales -->
                <h2 id="mentions">1. Mentions légales</h2>

                <h3>Éditeur du site</h3>
                <p>
                    <strong>Alliance Groupe</strong><br>
                    Forme juridique&nbsp;: <span style="color:#d4b45c;">[À COMPLÉTER : SASU / SARL / EI / Auto-entrepreneur]</span><br>
                    Capital social&nbsp;: <span style="color:#d4b45c;">[À COMPLÉTER — ex. 1 000 €]</span><br>
                    SIRET&nbsp;: <span style="color:#d4b45c;">[À COMPLÉTER — 14 chiffres]</span><br>
                    RCS&nbsp;: <span style="color:#d4b45c;">[À COMPLÉTER — ex. Nantes B 123 456 789]</span><br>
                    N° TVA intracommunautaire&nbsp;: <span style="color:#d4b45c;">[À COMPLÉTER — ex. FR12345678900]</span><br>
                    Siège social&nbsp;: <span style="color:#d4b45c;">[À COMPLÉTER — adresse complète]</span><br>
                    Téléphone&nbsp;: <a href="tel:+33623526074">06.23.52.60.74</a><br>
                    Email&nbsp;: <a href="mailto:contact@alliancegroupe-inc.com">contact@alliancegroupe-inc.com</a>
                </p>

                <h3>Directeur de la publication</h3>
                <p>Fabrizio <span style="color:#d4b45c;">[À COMPLÉTER — nom de famille]</span>, en qualité de fondateur et responsable éditorial.</p>

                <h3>Hébergeur</h3>
                <p>
                    <strong><span style="color:#d4b45c;">[À COMPLÉTER — ex. OVH SAS]</span></strong><br>
                    Adresse&nbsp;: <span style="color:#d4b45c;">[ex. 2 rue Kellermann, 59100 Roubaix, France]</span><br>
                    Téléphone&nbsp;: <span style="color:#d4b45c;">[ex. 1007]</span><br>
                    Site web&nbsp;: <span style="color:#d4b45c;">[ex. www.ovhcloud.com]</span>
                </p>

                <!-- 2. CGV -->
                <h2 id="cgv">2. Conditions générales de vente</h2>

                <h3>2.1 Objet</h3>
                <p>Les présentes Conditions Générales de Vente (CGV) régissent l'ensemble des prestations fournies par Alliance Groupe&nbsp;: création de sites web, automatisation par intelligence artificielle, référencement naturel (SEO), publicité en ligne, branding, conseil stratégique, rendez-vous de conseil payants et consultations écrites ("Questions Flash"). Toute commande implique l'acceptation pleine et entière des présentes CGV.</p>

                <h3>2.2 Services proposés</h3>
                <ul>
                    <li><strong>Appel découverte</strong> — 30 minutes gratuites, sans engagement.</li>
                    <li><strong>Audit Flash</strong> — 89 €, 45 minutes, analyse ciblée en visio.</li>
                    <li><strong>Audit Stratégique</strong> — 179 €, 1 h 15, plan d'action complet.</li>
                    <li><strong>Deep Dive</strong> — 390 €, 2 h, audit approfondi + roadmap 90 jours.</li>
                    <li><strong>Question Flash (unitaire)</strong> — 45 €, réponse écrite sous 48 h ouvrées.</li>
                    <li><strong>Pack 3 Questions</strong> — 120 €, 3 questions utilisables sur 90 jours.</li>
                    <li><strong>Abonnement Expert</strong> — 199 €/mois, jusqu'à 8 questions par mois, résiliable à tout moment.</li>
                    <li><strong>Prestations sur devis</strong> — création web, IA, SEO, publicité, branding, conseil longue durée.</li>
                </ul>

                <h3>2.3 Prix & paiement</h3>
                <p>Les prix sont indiqués en euros, TTC. Le paiement s'effectue exclusivement en ligne via <strong>Stripe</strong>, prestataire de paiement sécurisé agréé. Aucune donnée bancaire n'est stockée sur nos serveurs.</p>
                <ul>
                    <li>Les prestations ponctuelles (audits, questions unitaires) sont réglées en une fois avant exécution.</li>
                    <li>Les prestations sur devis peuvent faire l'objet d'un acompte de 30 % à 50 % selon le montant, le solde étant dû à la livraison.</li>
                    <li>L'abonnement Expert est prélevé automatiquement chaque mois jusqu'à résiliation.</li>
                    <li>Tout retard de paiement entraîne, sans mise en demeure préalable, une pénalité égale à trois fois le taux d'intérêt légal ainsi qu'une indemnité forfaitaire de 40 € pour frais de recouvrement (articles L.441-10 et D.441-5 du Code de commerce).</li>
                </ul>

                <h3>2.4 Délais & exécution</h3>
                <ul>
                    <li><strong>Questions Flash</strong>&nbsp;: réponse écrite livrée sous 48 h ouvrées à compter de la réception de la question complète.</li>
                    <li><strong>Audits payants</strong>&nbsp;: RDV planifié via Calendly aux créneaux disponibles, dans un délai de 1 à 10 jours ouvrés selon l'agenda.</li>
                    <li><strong>Prestations sur devis</strong>&nbsp;: délais précisés dans chaque devis et acceptés à la signature.</li>
                </ul>
                <p>Le client s'engage à fournir toutes les informations nécessaires à la bonne exécution de la prestation. Tout retard imputable au client peut entraîner un report équivalent du délai de livraison.</p>

                <!-- 3. Remboursement -->
                <h2 id="remboursement">3. Politique de remboursement & droit de rétractation</h2>

                <h3>3.1 Droit de rétractation légal (consommateurs)</h3>
                <p>Conformément à l'article <strong>L.221-18 du Code de la consommation</strong>, le consommateur dispose d'un délai de <strong>14 jours</strong> à compter de la conclusion du contrat pour exercer son droit de rétractation, sans avoir à justifier de motifs ni à payer de pénalités.</p>

                <h3>3.2 Exceptions au droit de rétractation</h3>
                <p>Conformément à l'article <strong>L.221-28 du Code de la consommation</strong>, le droit de rétractation ne peut être exercé pour&nbsp;:</p>
                <ul>
                    <li>Les prestations de services <strong>pleinement exécutées avant la fin du délai de 14 jours</strong>, et dont l'exécution a commencé <strong>après accord préalable exprès</strong> du consommateur et renoncement exprès à son droit de rétractation (cas des Questions Flash et des audits réalisés dans le délai).</li>
                    <li>Les services de fourniture d'un contenu numérique <strong>non fourni sur support matériel</strong> dont l'exécution a commencé après accord exprès du consommateur.</li>
                </ul>
                <p><strong>En pratique&nbsp;:</strong> en validant votre commande (audit payant ou Question Flash), vous reconnaissez expressément que la prestation sera exécutée immédiatement et vous renoncez explicitement à votre droit de rétractation une fois la prestation commencée.</p>

                <h3>3.3 Notre engagement "Satisfait ou remboursé"</h3>
                <p>Au-delà du strict minimum légal, nous appliquons une politique commerciale volontairement favorable au client&nbsp;:</p>
                <ul>
                    <li><strong>Question Flash non traitée</strong> — Si pour une raison quelconque nous ne pouvons pas traiter votre question (hors périmètre de compétence, manque d'information impossible à combler), vous êtes intégralement remboursé sous 7 jours ouvrés.</li>
                    <li><strong>Audit payant annulé par vos soins</strong> — Annulation gratuite et remboursement intégral jusqu'à 24 h avant le RDV via le lien d'annulation Calendly inclus dans votre email de confirmation.</li>
                    <li><strong>Audit payant annulé moins de 24 h avant</strong> — Possibilité de reporter le RDV sans frais (1 fois). Au-delà, l'audit est considéré comme consommé.</li>
                    <li><strong>Insatisfaction sur un audit réalisé</strong> — Si la prestation ne correspond pas à ce qui avait été annoncé, vous disposez de 7 jours pour nous adresser une réclamation motivée. Nous vous proposerons soit une nouvelle session gratuite, soit un remboursement partiel ou intégral selon la situation.</li>
                    <li><strong>Abonnement Expert</strong> — Résiliable à tout moment depuis l'email de confirmation Stripe. Aucun remboursement au prorata du mois en cours, mais aucun prélèvement futur.</li>
                    <li><strong>Prestations sur devis</strong> — Les conditions de remboursement sont précisées dans chaque devis et dépendent de l'état d'avancement des travaux.</li>
                </ul>

                <h3>3.4 Procédure de remboursement</h3>
                <p>Pour toute demande de remboursement, contactez-nous par email à <a href="mailto:contact@alliancegroupe-inc.com">contact@alliancegroupe-inc.com</a> en précisant&nbsp;:</p>
                <ul>
                    <li>Votre nom et l'email utilisé lors du paiement</li>
                    <li>La prestation concernée et la date d'achat</li>
                    <li>Le motif de la demande</li>
                </ul>
                <p>Nous vous répondons sous <strong>48 h ouvrées</strong>. Le remboursement, lorsqu'il est accordé, est effectué sur le moyen de paiement d'origine sous <strong>7 à 14 jours ouvrés</strong>.</p>

                <!-- 4. Responsabilité -->
                <h2 id="responsabilite">4. Responsabilité & garanties</h2>

                <h3>4.1 Obligation de moyens</h3>
                <p>Alliance Groupe est soumise à une <strong>obligation de moyens</strong> et non de résultat. Nous nous engageons à mettre en œuvre tous les moyens professionnels raisonnables pour mener à bien la prestation confiée, dans le respect de l'état de l'art et des bonnes pratiques du secteur.</p>

                <h3>4.2 Limites de responsabilité</h3>
                <p>Alliance Groupe ne pourra être tenue responsable&nbsp;:</p>
                <ul>
                    <li>Des pertes indirectes subies par le client (perte de chiffre d'affaires, de clientèle, de données, d'image).</li>
                    <li>D'un dysfonctionnement imputable à un tiers (hébergeur, plateforme de paiement, API externes, outils d'IA).</li>
                    <li>D'un cas de force majeure au sens de l'article 1218 du Code civil.</li>
                    <li>D'une mauvaise utilisation des livrables par le client après livraison.</li>
                </ul>
                <p>En tout état de cause, la responsabilité d'Alliance Groupe est plafonnée au montant hors taxes effectivement perçu pour la prestation concernée.</p>

                <h3>4.3 Assurance Responsabilité Civile Professionnelle</h3>
                <p>Alliance Groupe est assurée en Responsabilité Civile Professionnelle auprès de <span style="color:#d4b45c;">[À COMPLÉTER — nom de l'assureur + n° de police]</span>.</p>

                <!-- 5. Propriété intellectuelle -->
                <h2 id="propriete">5. Propriété intellectuelle</h2>
                <p>L'ensemble des éléments du site <strong>alliancegroupe-inc.com</strong> (textes, images, logos, vidéos, code source, charte graphique, structure) sont la propriété exclusive d'Alliance Groupe ou de ses partenaires et sont protégés par le droit d'auteur, le droit des marques et le droit des bases de données.</p>
                <p>Toute reproduction, représentation, modification, publication ou adaptation, totale ou partielle, sans autorisation écrite préalable est interdite et constitue une contrefaçon sanctionnée par les articles L.335-2 et suivants du Code de la propriété intellectuelle.</p>
                <p>Les livrables (sites web, visuels, textes, stratégies) produits dans le cadre d'une prestation deviennent la propriété du client <strong>après paiement intégral</strong> du prix convenu. Avant paiement intégral, Alliance Groupe reste propriétaire de l'ensemble des éléments créés.</p>
                <p>Alliance Groupe se réserve le droit de mentionner les prestations réalisées à titre de référence commerciale (portfolio, études de cas), sauf opposition écrite du client.</p>

                <!-- 6. Données personnelles -->
                <h2 id="donnees">6. Données personnelles & cookies</h2>
                <p>Les données personnelles collectées sur ce site (nom, email, téléphone, contenu des questions) sont utilisées exclusivement pour&nbsp;:</p>
                <ul>
                    <li>Répondre à vos demandes et exécuter les prestations commandées</li>
                    <li>Envoyer les confirmations de commande et factures</li>
                    <li>Vous informer ponctuellement de nos nouveautés (uniquement avec votre consentement explicite)</li>
                </ul>
                <p>Conformément au <strong>RGPD</strong> et à la <strong>loi Informatique et Libertés</strong>, vous disposez d'un droit d'accès, de rectification, d'effacement, de portabilité, de limitation et d'opposition sur vos données. Pour exercer ces droits&nbsp;: <a href="mailto:contact@alliancegroupe-inc.com">contact@alliancegroupe-inc.com</a>.</p>
                <p>Vous pouvez également déposer une réclamation auprès de la <strong>CNIL</strong> (<a href="https://www.cnil.fr" target="_blank" rel="noopener">cnil.fr</a>).</p>
                <p>Pour en savoir plus sur les cookies utilisés sur ce site et gérer vos préférences&nbsp;: <a href="<?php echo esc_url( home_url( '/cookies' ) ); ?>">Politique cookies</a>.</p>

                <!-- 7. Litiges -->
                <h2 id="litiges">7. Litiges & droit applicable</h2>

                <h3>7.1 Médiation</h3>
                <p>Conformément à l'article L.612-1 du Code de la consommation, en cas de litige avec un consommateur, Alliance Groupe propose de recourir à un médiateur de la consommation en vue d'une résolution amiable. Le consommateur peut saisir gratuitement le médiateur suivant&nbsp;:</p>
                <p style="color:#d4b45c;">[À COMPLÉTER — nom et coordonnées du médiateur conventionné, ex. CNPM Médiation Consommation]</p>
                <p>La plateforme européenne de règlement en ligne des litiges (RLL) est également disponible&nbsp;: <a href="https://ec.europa.eu/consumers/odr" target="_blank" rel="noopener">ec.europa.eu/consumers/odr</a>.</p>

                <h3>7.2 Droit applicable</h3>
                <p>Les présentes CGV sont soumises au <strong>droit français</strong>. En cas de litige non résolu à l'amiable, les tribunaux français seront seuls compétents. Pour les litiges avec un professionnel, le tribunal de commerce du siège social d'Alliance Groupe sera seul compétent, nonobstant pluralité de défendeurs ou appel en garantie.</p>

                <p style="color:#8a8a95;font-size:.85rem;margin-top:48px;padding-top:24px;border-top:1px solid rgba(255,255,255,.08);">
                    Dernière mise à jour&nbsp;: <?php echo esc_html( date_i18n( 'F Y' ) ); ?>.<br>
                    Alliance Groupe se réserve le droit de modifier les présentes CGV à tout moment. La version en vigueur est celle consultable sur le site à la date de la commande.
                </p>

            </div>
        </div>
    </section>

</main>

<?php get_footer(); ?>
