<?php
/**
 * Template Name: Cookies & Préférences
 *
 * Page d'information RGPD / CNIL sur les cookies utilisés par le site,
 * avec possibilité de rouvrir le panneau de consentement.
 */
get_header();
?>

<main id="ag-main-content">

    <section class="ag-hero" style="min-height:40vh;">
        <div class="ag-hero__bg">
            <div class="ag-hero__orb ag-hero__orb--1"></div>
        </div>
        <div class="ag-hero__content">
            <span class="ag-tag ag-anim" data-anim="tag">Politique de cookies</span>
            <h1 class="ag-hero__title">
                <span class="ag-line">Votre vie privée,</span>
                <span class="ag-line"><em>votre choix</em></span>
            </h1>
            <p class="ag-hero__sub">Transparence totale sur les cookies que nous utilisons, pourquoi, et comment vous gardez le contrôle à tout moment.</p>
        </div>
    </section>

    <section class="ag-section ag-section--graphite">
        <div class="ag-container">
            <div class="ag-cookies-page">

                <div style="display:flex;gap:14px;flex-wrap:wrap;margin-bottom:24px;">
                    <button type="button" class="ag-btn-gold" onclick="window.AGCookies && window.AGCookies.open();">Gérer mes préférences</button>
                    <button type="button" class="ag-btn-outline" onclick="window.AGCookies && window.AGCookies.reset();">Réinitialiser mon consentement</button>
                </div>

                <h2>Qu'est-ce qu'un cookie&nbsp;?</h2>
                <p>Un cookie est un petit fichier texte déposé sur votre appareil (ordinateur, tablette, smartphone) lors de votre visite sur un site internet. Il permet au site de se souvenir de vos actions et préférences (langue, connexion, panier…) pendant une certaine durée, afin que vous n'ayez pas à les ressaisir à chaque visite.</p>

                <h2>Vos droits</h2>
                <p>Conformément au <strong>Règlement Général sur la Protection des Données (RGPD)</strong> et aux recommandations de la <strong>CNIL</strong>, vous disposez à tout moment des droits suivants&nbsp;:</p>
                <ul>
                    <li>Refuser tous les cookies non essentiels — refuser est aussi simple qu'accepter</li>
                    <li>Personnaliser votre choix par catégorie de cookies</li>
                    <li>Retirer votre consentement à tout moment via le bouton ci-dessus</li>
                    <li>Accéder, rectifier ou supprimer vos données personnelles</li>
                    <li>Déposer une réclamation auprès de la CNIL (<a href="https://www.cnil.fr" target="_blank" rel="noopener">cnil.fr</a>)</li>
                </ul>
                <p>Votre choix est conservé pendant <strong>6 mois</strong>, puis la bannière réapparaît automatiquement.</p>

                <h2>Catégories de cookies utilisés</h2>

                <h3>1. Cookies essentiels <em style="font-weight:400;color:#8a8a95;">(toujours actifs)</em></h3>
                <p>Indispensables au fonctionnement du site. Ils ne peuvent pas être désactivés. Ils ne stockent aucune donnée personnelle identifiable et ne servent à aucun suivi marketing.</p>
                <table>
                    <thead>
                        <tr><th>Cookie</th><th>Finalité</th><th>Durée</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>ag_cookie_consent</td><td>Mémorise vos préférences de consentement</td><td>6 mois</td></tr>
                        <tr><td>PHPSESSID</td><td>Session utilisateur technique</td><td>Session</td></tr>
                        <tr><td>wordpress_*</td><td>Authentification de l'administration WordPress</td><td>Session</td></tr>
                    </tbody>
                </table>

                <h3>2. Cookies fonctionnels</h3>
                <p>Améliorent votre expérience utilisateur&nbsp;: mémorisation de vos choix, intégration de services tiers nécessaires à certaines fonctionnalités (prise de rendez-vous Calendly, lecture de vidéos intégrées).</p>
                <table>
                    <thead>
                        <tr><th>Service</th><th>Finalité</th><th>Durée</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>Calendly</td><td>Affichage du widget de prise de rendez-vous</td><td>Jusqu'à 1 an</td></tr>
                        <tr><td>Stripe</td><td>Traitement sécurisé des paiements en ligne</td><td>Jusqu'à 1 an</td></tr>
                    </tbody>
                </table>

                <h3>3. Cookies de mesure d'audience</h3>
                <p>Nous permettent de comprendre, de façon <strong>anonymisée</strong>, comment vous utilisez le site afin de l'améliorer&nbsp;: pages les plus visitées, temps passé, source de trafic. Aucune donnée n'est revendue à des tiers.</p>
                <table>
                    <thead>
                        <tr><th>Outil</th><th>Finalité</th><th>Durée</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>Analytics (anonymisé)</td><td>Statistiques de visite, temps passé, parcours</td><td>13 mois max</td></tr>
                    </tbody>
                </table>

                <h3>4. Cookies marketing</h3>
                <p>Servent à personnaliser les publicités sur ce site et sur d'autres sites partenaires, ainsi qu'à mesurer l'efficacité de nos campagnes publicitaires.</p>
                <table>
                    <thead>
                        <tr><th>Outil</th><th>Finalité</th><th>Durée</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>Meta Pixel</td><td>Mesure des conversions Facebook/Instagram</td><td>13 mois max</td></tr>
                        <tr><td>Google Ads</td><td>Remarketing et mesure de conversion</td><td>13 mois max</td></tr>
                    </tbody>
                </table>

                <h2>Comment gérer vos cookies&nbsp;?</h2>
                <p>Vous pouvez à tout moment&nbsp;:</p>
                <ul>
                    <li>Modifier vos préférences en cliquant sur le bouton <strong>"Gérer mes préférences"</strong> ci-dessus ou en bas de chaque page</li>
                    <li>Réinitialiser votre consentement (la bannière réapparaîtra à votre prochaine action)</li>
                    <li>Configurer directement votre navigateur pour refuser ou supprimer tous les cookies&nbsp;:
                        <ul>
                            <li><a href="https://support.google.com/chrome/answer/95647" target="_blank" rel="noopener">Google Chrome</a></li>
                            <li><a href="https://support.mozilla.org/fr/kb/protection-renforcee-contre-pistage-firefox-ordinateur" target="_blank" rel="noopener">Mozilla Firefox</a></li>
                            <li><a href="https://support.apple.com/fr-fr/guide/safari/sfri11471/mac" target="_blank" rel="noopener">Apple Safari</a></li>
                            <li><a href="https://support.microsoft.com/fr-fr/microsoft-edge" target="_blank" rel="noopener">Microsoft Edge</a></li>
                        </ul>
                    </li>
                </ul>
                <p style="color:#8a8a95;font-size:.88rem;margin-top:18px;"><em>À noter&nbsp;: refuser certains cookies peut limiter certaines fonctionnalités du site (prise de rendez-vous, vidéos intégrées, mesures d'audience).</em></p>

                <h2>Nous contacter</h2>
                <p>Pour toute question concernant notre politique de cookies ou l'exercice de vos droits&nbsp;:</p>
                <ul>
                    <li>Email&nbsp;: <a href="mailto:contact@alliancegroupe-inc.com">contact@alliancegroupe-inc.com</a></li>
                    <li>Téléphone&nbsp;: <a href="tel:+33623526074">06.23.52.60.74</a></li>
                </ul>
                <p style="color:#8a8a95;font-size:.85rem;margin-top:32px;">Dernière mise à jour&nbsp;: <?php echo esc_html( date_i18n( 'F Y' ) ); ?>.</p>

            </div>
        </div>
    </section>

</main>

<?php get_footer(); ?>
