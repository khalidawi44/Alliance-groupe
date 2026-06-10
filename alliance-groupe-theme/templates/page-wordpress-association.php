<?php
/**
 * Template Name: Template WordPress — Association
 *
 * Offre UNIQUE 100% GRATUITE qualite Premium pour les associations.
 * Pubs discretes vers alliancegroupe-inc.com en complement.
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
            <span class="ag-tag" style="background:rgba(225,15,26,0.18);color:#ffb1b6;border-color:rgba(225,15,26,0.45);">🤝 100% gratuit — qualité Premium offerte aux associations</span>
            <h1 class="ag-hero__title">
                <span class="ag-line">Le thème WordPress <em>militant</em></span>
                <span class="ag-line">offert aux associations</span>
            </h1>
            <p class="ag-hero__sub">Notre équipe a conçu un thème WordPress complet pour les associations loi 1901, mouvements citoyens, syndicats et ONG. <strong style="color:#FFD23F;">Nous l'offrons gratuitement</strong>, avec toutes les fonctionnalités premium incluses : pages séparées, calendrier événements, page équipe, formulaire création de groupe local, dons, manifestes — sans limite, sans abonnement.</p>
            <div class="ag-hero__buttons" style="justify-content:center;flex-wrap:wrap;">
                <button type="button" class="ag-btn-gold ag-dl-trigger" data-template="association" data-file="<?php echo esc_url( $dl_base . 'ag-starter-association.zip' ); ?>">⚡ Télécharger gratuitement</button>
                <a href="#features" class="ag-btn-outline">Voir les fonctionnalités ↓</a>
            </div>
            <p style="margin-top:18px;color:#888;font-size:.85rem;">Pas d'inscription. Pas de pub dans votre site. Code 100% libre.</p>
        </div>
    </section>

    <!-- Mention caritative -->
    <section class="ag-section ag-section--graphite" style="padding:48px 0;">
        <div class="ag-container">
            <div style="max-width:880px;margin:0 auto;padding:32px 36px;background:linear-gradient(135deg,rgba(225,15,26,0.10) 0%,rgba(20,20,22,0.4) 100%);border:2px solid rgba(225,15,26,0.4);border-radius:16px;">
                <h2 style="font-size:1.5rem;margin:0 0 12px;color:#fff;">💝 Pourquoi c'est gratuit ?</h2>
                <p style="color:#e8e6e0;line-height:1.7;margin:0 0 12px;">
                    Les associations militantes ont rarement les moyens de se payer un développeur ou un thème premium. Or elles ont autant besoin que les autres d'un site solide pour mobiliser, signer, organiser et collecter des dons.
                </p>
                <p style="color:#e8e6e0;line-height:1.7;margin:0;">
                    On a donc conçu le thème <strong style="color:#FFD23F;">AG Starter Association</strong> pour combler ce manque. <strong>Vous l'utilisez librement</strong> — pour votre asso, pour celles que vous accompagnez, pour vos collectifs. Si ça vous a aidé, parlez de nous : c'est tout ce qu'on demande.
                </p>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="ag-section ag-section--marbre" id="features">
        <div class="ag-container">
            <span class="ag-tag ag-anim" data-anim="tag">Tout est inclus, rien à payer</span>
            <h2 class="ag-section__title ag-anim" data-anim="title">Un site militant <em>complet</em></h2>
            <p class="ag-section__desc ag-anim" data-anim="desc">Personnalisez tout depuis le Customizer (couleurs, typographies, hero, bandeau urgence, photos président·e et bénévoles, réseaux sociaux, identité légale).</p>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:22px;max-width:1180px;margin:40px auto 0;">
                <?php
                $features = array(
                    array( '🔥', 'Hero impactant',         'Pattern thématique mains levées + cœurs + groupes + étoiles dorées. Image de fond personnalisable, voile noir réglable, alignement libre, 2 CTAs.' ),
                    array( '📜', 'Manifeste structuré',    'Constat / principes / priorités, formulaire signer l\'appel, compteur de signatures dynamique.' ),
                    array( '⚔️', 'Combats',               'CPT dédié + 6 cartes préremplies (climat, logement, services publics, démocratie, transparence, égalité).' ),
                    array( '📅', 'Calendrier événements', 'Vraie page archive avec calendrier mensuel sticky, cartes date/heure stylées, 5 événements préremplis.' ),
                    array( '🗺️', 'Groupes locaux',         'Page archive avec grille des groupes existants + formulaire "Créer un groupe local" qui envoie une demande à l\'asso.' ),
                    array( '✊', 'Boutons participation',  'Sur chaque événement, boutons "Je participe" et "Je soutiens" avec compteurs animés AJAX.' ),
                    array( '💛', 'Dons',                   'Grille de montants suggérés avec calcul auto du coût réel après réduction d\'impôt 66% ou 75%.' ),
                    array( '📰', '5 articles d\'actu',    'Articles préremplis prêts à éditer ou supprimer.' ),
                    array( '👥', 'Qui sommes-nous',        'Page complète : président·e + bio, 3 étapes histoire, 6 bénévoles avec rôles, 3 engagements chiffrés.' ),
                    array( '📹', 'Visio AG sécurisée',     '<strong style="color:#FFD23F;">Tchat + visio chiffrée bout-en-bout</strong> via Jitsi Meet, intégrée au site. Pour les AG, réunions de bureau, ateliers — surtout pour les adhérent·es absent·es. Aucun compte, aucune limite, RGPD-compliant.' ),
                    array( '📅', 'Prise de rendez-vous',  '<strong style="color:#FFD23F;">Cal.com intégré</strong> (alternative open source à Calendly), 100% gratuit, illimité. Synchro Google/Outlook/iCloud, rappels email auto, RGPD.' ),
                    array( '👤', 'Rôles utilisateurs',    'Sympathisant, adhérent, militant, trésorier·e, secrétaire, président·e — avec URLs personnalisées.' ),
                    array( '⚖️', 'Mentions &amp; RGPD auto', 'Mentions légales et politique de confidentialité générées automatiquement depuis l\'identité légale du Customizer.' ),
                    array( '📋', 'Statuts loi 1901',       '12 articles standards prêts à éditer (constitution, objet, composition, CA, AG, ressources, dissolution).' ),
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

    <!-- CTA download -->
    <section class="ag-section ag-section--or" style="padding:60px 0;">
        <div class="ag-container">
            <div style="max-width:760px;margin:0 auto;padding:36px 32px;text-align:center;background:linear-gradient(135deg,rgba(225,15,26,0.12) 0%,rgba(20,20,22,0.6) 100%);border:2px solid rgba(225,15,26,0.45);border-radius:16px;">
                <span style="display:inline-block;padding:6px 18px;background:rgba(225,15,26,0.25);border:1px solid rgba(225,15,26,0.5);border-radius:100px;color:#ffb1b6;font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;margin-bottom:14px;">🤝 100% gratuit, à vie</span>
                <h2 style="font-size:clamp(1.5rem,3vw,2rem);margin:0 0 12px;line-height:1.3;color:#fff;">Téléchargez maintenant. Personnalisez. Mobilisez.</h2>
                <p style="color:#e8e6e0;line-height:1.7;margin:0 0 22px;">Aucune limite. Aucun watermark. Aucun module bridé. Le thème complet, prêt à l'emploi pour votre association.</p>
                <button type="button" class="ag-btn-gold ag-dl-trigger" data-template="association" data-file="<?php echo esc_url( $dl_base . 'ag-starter-association.zip' ); ?>">⚡ Télécharger AG Starter Association</button>
                <p style="color:#888;font-size:.82rem;margin-top:14px;font-style:italic;">+ Pack Fidélité (extensions, outils association) inclus dans le ZIP.</p>
            </div>
        </div>
    </section>

    <!-- Pub discrete : autres templates -->
    <section class="ag-section ag-section--graphite" style="padding:48px 0;">
        <div class="ag-container">
            <div style="max-width:760px;margin:0 auto;text-align:center;">
                <p style="color:#888;font-size:.95rem;line-height:1.7;margin:0;">
                    Vous gérez aussi un cabinet, un commerce, un salon ? Nous proposons <a href="<?php echo esc_url( home_url( '/templates-wordpress' ) ); ?>" style="color:#D4B45C;font-weight:600;">5 autres templates WordPress gratuits</a> par métier (avocat, restaurant, artisan, coach, barber).
                </p>
            </div>
        </div>
    </section>

</main>

<!-- CTA audit gratuit ajout stratu00e9gie de vente -->
<?php get_template_part( 'template-parts/audit-cta' ); ?>

<?php get_footer(); ?>
