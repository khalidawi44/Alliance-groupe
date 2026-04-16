<?php
/**
 * Template Name: Politique de confidentialité
 *
 * Page RGPD complète : collecte de données, finalités, durée de conservation,
 * droits des utilisateurs, sous-traitants, transferts.
 */
get_header();
?>

<main id="ag-main-content">

    <section class="ag-hero" style="min-height:40vh;">
        <div class="ag-hero__bg">
            <div class="ag-hero__orb ag-hero__orb--1"></div>
        </div>
        <div class="ag-hero__content">
            <span class="ag-tag ag-anim" data-anim="tag">RGPD</span>
            <h1 class="ag-hero__title">
                <span class="ag-line">Politique de</span>
                <span class="ag-line"><em>confidentialité</em></span>
            </h1>
            <p class="ag-hero__sub">Comment nous collectons, utilisons et protégeons vos données personnelles. Transparence totale, conformité RGPD.</p>
        </div>
    </section>

    <section class="ag-section ag-section--graphite">
        <div class="ag-container">
            <div class="ag-cookies-page">

                <!-- Sommaire -->
                <div style="background:rgba(212,180,92,.06);border:1px solid rgba(212,180,92,.25);border-radius:14px;padding:22px 26px;margin:0 0 40px;">
                    <h2 style="margin-top:0;font-size:1.1rem;color:var(--color-gold);">Sommaire</h2>
                    <ul style="margin:10px 0 0;">
                        <li><a href="#responsable">1. Responsable du traitement</a></li>
                        <li><a href="#donnees-collectees">2. Données collectées</a></li>
                        <li><a href="#finalites">3. Finalités du traitement</a></li>
                        <li><a href="#bases-legales">4. Bases légales</a></li>
                        <li><a href="#duree">5. Durée de conservation</a></li>
                        <li><a href="#destinataires">6. Destinataires &amp; sous-traitants</a></li>
                        <li><a href="#transferts">7. Transferts hors UE</a></li>
                        <li><a href="#droits">8. Vos droits</a></li>
                        <li><a href="#securite">9. Sécurité</a></li>
                        <li><a href="#mineurs">10. Mineurs</a></li>
                        <li><a href="#modifications">11. Modifications</a></li>
                        <li><a href="#contact-dpo">12. Contact</a></li>
                    </ul>
                </div>

                <!-- 1 -->
                <h2 id="responsable">1. Responsable du traitement</h2>
                <p>Le responsable du traitement de vos données personnelles est&nbsp;:</p>
                <p>
                    <strong>Alliance Groupe</strong><br>
                    Représenté par Fabrizio <span style="color:var(--color-gold);">[À COMPLÉTER — nom de famille]</span>, Fondateur &amp; CEO<br>
                    Siège social&nbsp;: <span style="color:var(--color-gold);">[À COMPLÉTER — adresse complète]</span><br>
                    SIRET&nbsp;: <span style="color:var(--color-gold);">[À COMPLÉTER]</span><br>
                    Email&nbsp;: <a href="mailto:contact@alliancegroupe-inc.com">contact@alliancegroupe-inc.com</a><br>
                    Téléphone&nbsp;: <a href="tel:+33623526074">06.23.52.60.74</a>
                </p>

                <!-- 2 -->
                <h2 id="donnees-collectees">2. Données personnelles collectées</h2>
                <p>Nous collectons uniquement les données strictement nécessaires à nos services. Voici le détail par point de collecte&nbsp;:</p>

                <h3>Formulaire de contact</h3>
                <ul>
                    <li>Nom et prénom</li>
                    <li>Adresse email</li>
                    <li>Numéro de téléphone (facultatif)</li>
                    <li>Message / description du projet</li>
                </ul>

                <h3>Prise de rendez-vous (Calendly)</h3>
                <ul>
                    <li>Nom et prénom</li>
                    <li>Adresse email</li>
                    <li>Réponses aux questions pré-rendez-vous (activité, objectifs)</li>
                    <li>Données de planification (date, heure choisies)</li>
                </ul>

                <h3>Questions Flash (consultation écrite payante)</h3>
                <ul>
                    <li>Nom complet</li>
                    <li>Adresse email</li>
                    <li>Description de l'activité</li>
                    <li>Question posée et contexte complémentaire</li>
                    <li>Pack acheté</li>
                </ul>

                <h3>Paiement en ligne (Stripe)</h3>
                <ul>
                    <li>Données de facturation (nom, adresse email)</li>
                    <li>Données de paiement (numéro de carte, date d'expiration) — <strong>traitées exclusivement par Stripe, jamais stockées sur nos serveurs</strong></li>
                </ul>

                <h3>Téléchargement de templates gratuits</h3>
                <ul>
                    <li>Nom</li>
                    <li>Adresse email</li>
                    <li>Numéro de téléphone (facultatif)</li>
                    <li>Template demandé</li>
                </ul>

                <h3>Navigation sur le site</h3>
                <ul>
                    <li>Adresse IP (anonymisée si analytics actifs)</li>
                    <li>Type de navigateur et système d'exploitation</li>
                    <li>Pages visitées, durée de visite</li>
                    <li>Cookies (voir notre <a href="<?php echo esc_url( home_url( '/cookies' ) ); ?>">Politique cookies</a>)</li>
                </ul>

                <!-- 3 -->
                <h2 id="finalites">3. Finalités du traitement</h2>
                <table>
                    <thead>
                        <tr><th>Finalité</th><th>Données utilisées</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>Répondre à vos demandes de contact</td><td>Nom, email, téléphone, message</td></tr>
                        <tr><td>Planifier et réaliser les consultations</td><td>Nom, email, données Calendly</td></tr>
                        <tr><td>Traiter les Questions Flash</td><td>Nom, email, activité, question, pack</td></tr>
                        <tr><td>Traiter les paiements</td><td>Données de facturation (via Stripe)</td></tr>
                        <tr><td>Envoyer les confirmations et livrables</td><td>Nom, email</td></tr>
                        <tr><td>Envoyer des communications marketing (avec consentement)</td><td>Nom, email</td></tr>
                        <tr><td>Améliorer le site (analytics)</td><td>Données de navigation anonymisées</td></tr>
                        <tr><td>Respecter nos obligations légales</td><td>Données de facturation</td></tr>
                    </tbody>
                </table>

                <!-- 4 -->
                <h2 id="bases-legales">4. Bases légales du traitement</h2>
                <table>
                    <thead>
                        <tr><th>Traitement</th><th>Base légale (art. 6 RGPD)</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>Réponse aux demandes de contact</td><td>Intérêt légitime (relation pré-contractuelle)</td></tr>
                        <tr><td>Exécution des consultations et Questions Flash</td><td>Exécution du contrat</td></tr>
                        <tr><td>Paiements</td><td>Exécution du contrat</td></tr>
                        <tr><td>Communications marketing</td><td>Consentement</td></tr>
                        <tr><td>Cookies analytics / marketing</td><td>Consentement (via bannière cookies)</td></tr>
                        <tr><td>Obligations comptables et fiscales</td><td>Obligation légale</td></tr>
                    </tbody>
                </table>

                <!-- 5 -->
                <h2 id="duree">5. Durée de conservation</h2>
                <table>
                    <thead>
                        <tr><th>Catégorie de données</th><th>Durée de conservation</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>Demandes de contact</td><td>3 ans à compter du dernier contact</td></tr>
                        <tr><td>Données clients (Questions Flash, audits)</td><td>5 ans après la fin de la relation commerciale (obligation légale)</td></tr>
                        <tr><td>Données de facturation</td><td>10 ans (obligation comptable, art. L.123-22 du Code de commerce)</td></tr>
                        <tr><td>Données de navigation / cookies</td><td>13 mois maximum (recommandation CNIL)</td></tr>
                        <tr><td>Consentement cookies</td><td>6 mois, puis re-demandé</td></tr>
                        <tr><td>Données de prospection (leads templates)</td><td>3 ans à compter de la collecte</td></tr>
                    </tbody>
                </table>
                <p>Au-delà de ces durées, vos données sont supprimées ou anonymisées de manière irréversible.</p>

                <!-- 6 -->
                <h2 id="destinataires">6. Destinataires &amp; sous-traitants</h2>
                <p>Vos données personnelles sont accessibles exclusivement aux personnes suivantes&nbsp;:</p>
                <ul>
                    <li><strong>L'équipe Alliance Groupe</strong> — dans la stricte limite de leurs fonctions</li>
                </ul>
                <p>Nous faisons appel aux sous-traitants suivants, tous conformes au RGPD&nbsp;:</p>
                <table>
                    <thead>
                        <tr><th>Sous-traitant</th><th>Service</th><th>Pays</th><th>Garanties</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>Stripe, Inc.</td><td>Paiement en ligne</td><td>USA</td><td>Certifié PCI-DSS, clauses contractuelles types (CCT)</td></tr>
                        <tr><td>Calendly, LLC</td><td>Prise de rendez-vous</td><td>USA</td><td>CCT, SOC 2 Type II</td></tr>
                        <tr><td>Hostinger International</td><td>Hébergement web</td><td>Lituanie (UE)</td><td>Serveurs UE, conforme RGPD</td></tr>
                        <tr><td>Google LLC</td><td>Analytics, Google Meet</td><td>USA</td><td>CCT, cadre de protection des données UE-US</td></tr>
                    </tbody>
                </table>
                <p><strong>Nous ne vendons jamais vos données à des tiers.</strong> Aucune donnée n'est cédée, louée ou échangée à des fins commerciales.</p>

                <!-- 7 -->
                <h2 id="transferts">7. Transferts hors Union européenne</h2>
                <p>Certains de nos sous-traitants (Stripe, Calendly, Google) sont basés aux États-Unis. Ces transferts sont encadrés par&nbsp;:</p>
                <ul>
                    <li>Les <strong>Clauses Contractuelles Types (CCT)</strong> adoptées par la Commission européenne</li>
                    <li>Le <strong>EU-U.S. Data Privacy Framework</strong> pour les entreprises certifiées</li>
                </ul>
                <p>Ces mécanismes garantissent un niveau de protection équivalent à celui offert au sein de l'UE.</p>

                <!-- 8 -->
                <h2 id="droits">8. Vos droits</h2>
                <p>Conformément au <strong>RGPD (articles 15 à 22)</strong> et à la <strong>loi Informatique et Libertés</strong>, vous disposez des droits suivants&nbsp;:</p>
                <table>
                    <thead>
                        <tr><th>Droit</th><th>Ce que ça signifie</th></tr>
                    </thead>
                    <tbody>
                        <tr><td><strong>Accès</strong></td><td>Obtenir une copie de toutes les données que nous détenons sur vous</td></tr>
                        <tr><td><strong>Rectification</strong></td><td>Corriger des données inexactes ou incomplètes</td></tr>
                        <tr><td><strong>Effacement</strong> ("droit à l'oubli")</td><td>Demander la suppression de vos données (sauf obligation légale de conservation)</td></tr>
                        <tr><td><strong>Limitation</strong></td><td>Geler temporairement le traitement de vos données</td></tr>
                        <tr><td><strong>Portabilité</strong></td><td>Recevoir vos données dans un format structuré et lisible par machine</td></tr>
                        <tr><td><strong>Opposition</strong></td><td>Vous opposer au traitement de vos données (marketing, profilage)</td></tr>
                        <tr><td><strong>Retrait du consentement</strong></td><td>Retirer votre consentement à tout moment (cookies, newsletter)</td></tr>
                    </tbody>
                </table>

                <h3>Comment exercer vos droits</h3>
                <p>Envoyez un email à <a href="mailto:contact@alliancegroupe-inc.com">contact@alliancegroupe-inc.com</a> avec l'objet <strong>"Exercice de droits RGPD"</strong> en précisant&nbsp;:</p>
                <ul>
                    <li>Votre nom complet et l'email utilisé sur notre site</li>
                    <li>Le droit que vous souhaitez exercer</li>
                    <li>Un justificatif d'identité (copie de pièce d'identité)</li>
                </ul>
                <p>Nous vous répondons sous <strong>30 jours maximum</strong> (délai légal). Ce délai peut être prolongé de 2 mois pour les demandes complexes, avec notification préalable.</p>
                <p>Si vous estimez que vos droits ne sont pas respectés, vous pouvez déposer une réclamation auprès de la <strong>CNIL</strong>&nbsp;: <a href="https://www.cnil.fr/fr/plaintes" target="_blank" rel="noopener">cnil.fr/fr/plaintes</a>.</p>

                <!-- 9 -->
                <h2 id="securite">9. Sécurité des données</h2>
                <p>Nous mettons en œuvre les mesures techniques et organisationnelles suivantes pour protéger vos données&nbsp;:</p>
                <ul>
                    <li><strong>Chiffrement HTTPS</strong> (TLS 1.2+) sur l'ensemble du site</li>
                    <li><strong>Mots de passe hashés</strong> — aucun mot de passe stocké en clair</li>
                    <li><strong>Accès restreint</strong> — seuls les membres autorisés de l'équipe accèdent aux données</li>
                    <li><strong>Sauvegardes régulières</strong> de la base de données</li>
                    <li><strong>Paiements PCI-DSS</strong> — données bancaires traitées exclusivement par Stripe, jamais stockées sur nos serveurs</li>
                    <li><strong>Mises à jour régulières</strong> de WordPress, des plugins et du thème</li>
                </ul>

                <!-- 10 -->
                <h2 id="mineurs">10. Mineurs</h2>
                <p>Nos services s'adressent aux <strong>professionnels et entrepreneurs majeurs</strong>. Nous ne collectons pas sciemment de données personnelles de mineurs de moins de 16 ans. Si nous découvrons qu'un mineur a fourni des données, elles seront supprimées dans les plus brefs délais.</p>

                <!-- 11 -->
                <h2 id="modifications">11. Modifications de cette politique</h2>
                <p>Nous nous réservons le droit de modifier cette politique à tout moment pour refléter l'évolution de nos pratiques ou de la réglementation. La date de dernière mise à jour est indiquée en bas de cette page. En cas de modification substantielle, nous vous en informerons par email ou par une notification visible sur le site.</p>

                <!-- 12 -->
                <h2 id="contact-dpo">12. Contact</h2>
                <p>Pour toute question relative à la protection de vos données personnelles&nbsp;:</p>
                <ul>
                    <li>Email&nbsp;: <a href="mailto:contact@alliancegroupe-inc.com">contact@alliancegroupe-inc.com</a></li>
                    <li>Téléphone&nbsp;: <a href="tel:+33623526074">06.23.52.60.74</a></li>
                    <li>Courrier&nbsp;: Alliance Groupe — <span style="color:var(--color-gold);">[À COMPLÉTER — adresse postale]</span></li>
                </ul>

                <p style="color:var(--color-text-muted);font-size:.85rem;margin-top:48px;padding-top:24px;border-top:1px solid rgba(255,255,255,.08);">
                    Dernière mise à jour&nbsp;: <?php echo esc_html( date_i18n( 'F Y' ) ); ?>.
                </p>

            </div>
        </div>
    </section>

</main>

<?php get_footer(); ?>
