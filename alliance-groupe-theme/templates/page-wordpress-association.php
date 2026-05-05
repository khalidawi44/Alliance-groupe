<?php
/**
 * Template Name: Template WordPress — Association
 *
 * Landing page dediee au theme AG Starter Association.
 * Offre caritative : pas de Free, Premium 29,90€ + 14,90€/mois,
 * Pack Fidelite 99€ au lieu du Premium des autres templates.
 */

get_header();

$dl_base = get_stylesheet_directory_uri() . '/assets/downloads/';
?>

<main id="ag-main-content">

    <!-- Hero -->
    <section class="ag-hero" style="min-height:60vh;">
        <div class="ag-hero__bg">
            <div class="ag-hero__orb ag-hero__orb--1"></div>
        </div>
        <div class="ag-hero__content">
            <span class="ag-tag" style="background:rgba(225,15,26,0.18);color:#ffb1b6;border-color:rgba(225,15,26,0.45);">🤝 Offre caritative — engagement Alliance Groupe</span>
            <h1 class="ag-hero__title">
                <span class="ag-line">Le thème WordPress pour les <em>associations</em></span>
                <span class="ag-line">militantes, syndicales &amp; solidaires</span>
            </h1>
            <p class="ag-hero__sub">Conçu pour les mouvements citoyens, partis, syndicats, ONG et associations loi 1901. Design impactant rouge &amp; noir, pages séparées (manifeste, combats, événements, groupes locaux, dons), CPT prêts à l'emploi, rôles utilisateurs (sympathisant / adhérent / militant), génération auto des mentions légales et statuts.</p>
            <div class="ag-hero__buttons" style="justify-content:center;flex-wrap:wrap;">
                <button type="button" class="ag-btn-gold ag-dl-trigger" data-template="association" data-file="<?php echo esc_url( $dl_base . 'ag-starter-association.zip' ); ?>">⚡ Télécharger gratuitement</button>
                <a href="#packs" class="ag-btn-outline">Voir les packs payants ↓</a>
            </div>
        </div>
    </section>

    <!-- Mention caritative -->
    <section class="ag-section ag-section--graphite" style="padding:48px 0;">
        <div class="ag-container">
            <div style="max-width:880px;margin:0 auto;padding:32px 36px;background:linear-gradient(135deg,rgba(225,15,26,0.10) 0%,rgba(20,20,22,0.4) 100%);border:2px solid rgba(225,15,26,0.4);border-radius:16px;">
                <h2 style="font-size:1.5rem;margin:0 0 12px;color:#fff;">💝 Une offre solidaire, à prix réduit</h2>
                <p style="color:#e8e6e0;line-height:1.7;margin:0 0 12px;">
                    Parce que les associations ont rarement les budgets des entreprises, nous appliquons une <strong style="color:#FFD23F;">réduction sur tous nos tarifs habituels</strong> :
                </p>
                <ul style="color:#e8e6e0;line-height:1.8;margin:0 0 12px;padding-left:24px;">
                    <li><strong style="color:#FFD23F;">Pack Premium : 29,90€</strong> (au lieu de 99€) + <strong>14,90€/mois</strong> de maintenance courante (mises à jour, support, sécurité)</li>
                    <li><strong style="color:#FFD23F;">Pack Fidélité : 99€</strong> (équivalent du Pack Premium des autres templates) — pages séparées + 13 extensions WordPress recommandées installables en 1 clic + outils de gestion association complets</li>
                    <li><strong>Reçu fiscal</strong> possible si votre association est d'intérêt général — déduction 66% de l'impôt</li>
                </ul>
                <p style="color:#b0b0bc;line-height:1.7;margin:0;font-size:.92rem;font-style:italic;">
                    Cette tarification s'applique exclusivement aux associations loi 1901 et structures à but non lucratif. Justificatif (RNA, statuts) demandé à l'achat.
                </p>
            </div>
        </div>
    </section>

    <!-- Description longue -->
    <section class="ag-section ag-section--marbre" id="apercu">
        <div class="ag-container">
            <span class="ag-tag ag-anim" data-anim="tag">Aperçu du thème</span>
            <h2 class="ag-section__title ag-anim" data-anim="title">Tout ce qu'il faut pour <em>mobiliser</em></h2>
            <p class="ag-section__desc ag-anim" data-anim="desc">AG Starter Association est pensé pour celles et ceux qui veulent agir : un site militant prêt à l'emploi, sans dépendance à des prestataires. Personnalisez tout depuis le Customizer (couleurs, typographies, hero, bandeau urgence, photos président·e et bénévoles, réseaux sociaux, identité légale).</p>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:22px;max-width:1180px;margin:40px auto 0;">
                <?php
                $features = array(
                    array( '🔥', 'Hero impactant',         'Pattern thématique mains levées + cœurs + groupes + étoiles dorées. Image de fond personnalisable, voile noir réglable, alignement libre, 2 CTAs.' ),
                    array( '📜', 'Manifeste structuré',    'Page manifeste avec constat / principes / priorités, formulaire signer l\'appel, compteur de signatures dynamique.' ),
                    array( '⚔️', 'Combats',               'CPT dédié + 6 cartes de combat préremplies (climat, logement, services publics, démocratie, transparence, égalité).' ),
                    array( '📅', 'Calendrier événements', 'Vraie page archive avec calendrier mensuel sticky, cartes date/heure stylées, scroll smooth, 5 événements préremplis.' ),
                    array( '🗺️', 'Groupes locaux',         'Page archive avec grille des groupes existants + formulaire "Créer un groupe local" qui envoie une demande à l\'asso.' ),
                    array( '💛', 'Dons',                   'Grille de montants suggérés avec calcul auto du coût réel après réduction d\'impôt 66% ou 75%, intégration GiveWP recommandée.' ),
                    array( '📰', '5 articles d\'actu',    'Articles préremplis (hôpital, pétition, nouveau groupe, logement, AG) prêts à éditer ou supprimer.' ),
                    array( '👥', 'Qui sommes-nous',        'Page complète : président·e + bio, 3 étapes histoire, 6 bénévoles avec rôles + bios, 3 engagements chiffrés (indépendance / transparence / démocratie).' ),
                    array( '👤', 'Rôles utilisateurs',    'Sympathisant, adhérent, militant, trésorier·e, secrétaire, président·e — avec URLs personnalisées /adherent/jean-dupont, etc.' ),
                    array( '⚖️', 'Mentions &amp; RGPD auto', 'Mentions légales et politique de confidentialité générées automatiquement depuis l\'identité légale du Customizer (SIRET, RNA, IBAN, DPO, hébergeur).' ),
                    array( '📋', 'Statuts loi 1901',       '12 articles standards prêts à éditer (constitution, objet, composition, CA, AG, ressources, dissolution).' ),
                    array( '🚨', 'Bandeau urgence',         'Top de page activable en un clic depuis le Customizer pour annoncer une mobilisation urgente avec lien.' ),
                );
                foreach ( $features as $f ) : ?>
                <div class="ag-anim" data-anim="card" style="padding:24px;background:rgba(255,255,255,.025);border:1px solid rgba(212,180,92,.25);border-radius:12px;">
                    <div style="font-size:1.8rem;margin-bottom:10px;"><?php echo $f[0]; ?></div>
                    <h3 style="color:#fff;font-size:1.1rem;margin:0 0 8px;"><?php echo $f[1]; ?></h3>
                    <p style="color:#b0b0bc;font-size:.9rem;line-height:1.55;margin:0;"><?php echo $f[2]; // phpcs:ignore ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Packs payants -->
    <section class="ag-section ag-section--graphite" id="packs">
        <div class="ag-container">
            <span class="ag-tag ag-anim" data-anim="tag">2 packs payants — tarifs association</span>
            <h2 class="ag-section__title ag-anim" data-anim="title">Aller <em>plus loin</em></h2>
            <p class="ag-section__desc ag-anim" data-anim="desc">Le thème gratuit suffit déjà à lancer un site militant complet. Les packs payants apportent des outils avancés (extensions, maintenance, support) à un tarif solidaire.</p>

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:22px;max-width:1080px;margin:48px auto 0;">

                <!-- Gratuit -->
                <div style="padding:32px 28px;background:rgba(40,167,69,0.06);border:2px solid rgba(40,167,69,0.3);border-radius:16px;">
                    <div style="font-size:2rem;margin-bottom:8px;">🆓</div>
                    <h3 style="color:#28a745;margin:0 0 4px;">Gratuit</h3>
                    <strong style="display:block;color:#fff;font-size:2rem;margin:0 0 16px;">0€</strong>
                    <ul style="color:#e8e6e0;line-height:1.8;font-size:.92rem;padding-left:18px;margin:0 0 20px;">
                        <li>Thème complet</li>
                        <li>Customizer 50+ réglages</li>
                        <li>Hero, manifeste, combats, événements, groupes, dons, signer</li>
                        <li>5 articles + 5 événements + 6 combats + 5 groupes préremplis</li>
                    </ul>
                    <button type="button" class="ag-btn-outline ag-dl-trigger" data-template="association" data-file="<?php echo esc_url( $dl_base . 'ag-starter-association.zip' ); ?>" style="width:100%;">Télécharger</button>
                </div>

                <!-- Premium -->
                <div style="padding:32px 28px;background:rgba(212,180,92,.08);border:2px solid rgba(212,180,92,.4);border-radius:16px;position:relative;">
                    <span style="position:absolute;top:-12px;left:50%;transform:translateX(-50%);background:#D4B45C;color:#080808;font-size:.68rem;font-weight:700;padding:3px 12px;border-radius:100px;text-transform:uppercase;letter-spacing:1px;">Recommandé</span>
                    <div style="font-size:2rem;margin-bottom:8px;">⚡</div>
                    <h3 style="color:#D4B45C;margin:0 0 4px;">Pack Premium</h3>
                    <strong style="display:block;color:#fff;font-size:2rem;margin:0 0 4px;">29,90€</strong>
                    <span style="display:block;color:#FFD23F;font-size:.92rem;margin:0 0 4px;">+ 14,90€/mois maintenance</span>
                    <span style="display:block;color:#888;text-decoration:line-through;font-size:.85rem;margin:0 0 16px;">Tarif normal : 99€</span>
                    <ul style="color:#e8e6e0;line-height:1.7;font-size:.92rem;padding-left:18px;margin:0 0 20px;">
                        <li>Tout Gratuit inclus</li>
                        <li>Mises à jour WordPress &amp; thème automatiques</li>
                        <li>Sauvegardes hebdomadaires</li>
                        <li>Monitoring sécurité</li>
                        <li>Support email prioritaire</li>
                        <li>Animations scroll, sticky header avancé</li>
                    </ul>
                    <a href="<?php echo esc_url( home_url( '/contact?offre=association-premium' ) ); ?>" class="ag-btn-gold" style="width:100%;text-align:center;">Souscrire</a>
                </div>

                <!-- Pack Fidélité -->
                <div style="padding:32px 28px;background:rgba(225,15,26,0.08);border:2px solid rgba(225,15,26,0.45);border-radius:16px;">
                    <div style="font-size:2rem;margin-bottom:8px;">💎</div>
                    <h3 style="color:#ffb1b6;margin:0 0 4px;">Pack Fidélité</h3>
                    <strong style="display:block;color:#fff;font-size:2rem;margin:0 0 4px;">99€</strong>
                    <span style="display:block;color:#FFD23F;font-size:.92rem;margin:0 0 4px;">paiement unique</span>
                    <span style="display:block;color:#888;font-size:.82rem;margin:0 0 16px;">Équivalent Premium des autres templates</span>
                    <ul style="color:#e8e6e0;line-height:1.7;font-size:.92rem;padding-left:18px;margin:0 0 20px;">
                        <li><strong>Pages séparées</strong> auto-créées (manifeste, combats, événements, groupes, dons, adhérer, mentions, RGPD, statuts)</li>
                        <li><strong>13 extensions WP</strong> recommandées installables en 1 clic (Paid Memberships Pro, GiveWP, MailPoet, WPForms, The Events Calendar, Polylang, Yoast, CookieYes, Wordfence, UpdraftPlus, etc.)</li>
                        <li>Rôles utilisateurs avancés + URLs perso</li>
                        <li>Mentions légales + RGPD + statuts auto-générés</li>
                        <li>Espace adhérent privé (PV, comptes-rendus)</li>
                        <li>Customizer Identité légale (SIRET, RNA, IBAN, DPO)</li>
                    </ul>
                    <a href="<?php echo esc_url( home_url( '/contact?offre=association-fidelite' ) ); ?>" class="ag-btn-outline" style="width:100%;text-align:center;border-color:rgba(225,15,26,0.6);color:#ffb1b6;">Souscrire</a>
                </div>

            </div>

            <p style="text-align:center;color:#888;font-size:.88rem;margin-top:32px;font-style:italic;">
                💡 Les associations d'intérêt général peuvent obtenir un reçu fiscal — déduction 66% du montant payé.
            </p>
        </div>
    </section>

    <!-- CTA contact -->
    <section class="ag-section ag-section--or" style="padding:60px 0;">
        <div class="ag-container">
            <div style="max-width:760px;margin:0 auto;padding:36px 32px;text-align:center;background:linear-gradient(135deg,rgba(212,180,92,.12) 0%,rgba(20,20,22,.6) 100%);border:2px solid rgba(212,180,92,.4);border-radius:16px;">
                <h2 style="font-size:clamp(1.4rem,3vw,1.9rem);margin:0 0 14px;line-height:1.3;">Une question sur le thème, l'installation ou la tarification association ?</h2>
                <p style="color:#e8e6e0;line-height:1.7;margin:0 0 22px;">Notre équipe répond gratuitement à toutes les associations qui veulent lancer leur site militant. Pas de bullshit commercial, juste des conseils.</p>
                <div class="ag-hero__buttons" style="justify-content:center;flex-wrap:wrap;">
                    <a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" class="ag-btn-gold">Nous contacter →</a>
                    <a href="tel:+33623526074" class="ag-btn-outline">📞 06 23 52 60 74</a>
                </div>
            </div>
        </div>
    </section>

</main>

<?php get_footer(); ?>
