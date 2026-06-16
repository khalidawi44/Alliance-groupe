<?php
/**
 * Template Name: Template WordPress — Avocat
 *
 * Dedicated landing page for the AG Starter Avocat theme.
 * The actual rendering lives in template-parts/metier-page.php.
 */

get_header();

set_query_var( 'ag_metier', array(
    'slug'        => 'avocat',
    'slug_full'   => 'ag-starter-avocat',
    'icon'        => '⚖️',
    'name'        => 'Avocat',
    'audience_short' => 'cabinet',
    'palette'     => 'Navy &amp; champagne',

    'hero_title'    => 'Le thème WordPress pour les <em>cabinets d\'avocats</em>',
    'hero_subtitle' => 'Design sombre navy & champagne, 100% français, RGPD ready, formulaire RDV confidentiel intégré. Installation en 2 minutes, aucun plugin requis.',

    'description_long' => 'AG Starter Avocat est un thème WordPress gratuit spécifiquement pensé pour les cabinets d\'avocats, juristes et notaires francophones. Il inclut tout ce qu\'un professionnel du droit a besoin pour lancer sa vitrine en ligne : Custom Post Type pour gérer les domaines d\'expertise, formulaire de prise de rendez-vous confidentiel RGPD-compliant, présentation du Maître, honoraires transparents, et intégration Google Maps.',

    'free_features' => array(
        '<strong>CPT Domaines d\'expertise</strong> — gérez vos domaines depuis <code>Articles &gt; Domaines</code> avec icône, exemples de cas et image',
        '<strong>Formulaire RDV RGPD-compliant</strong> — nonce + honeypot + consentement explicite, envoi via wp_mail()',
        '<strong>Section "Le Maître"</strong> — photo, barreau, année, biographie, spécialités (configurable)',
        '<strong>Honoraires transparents</strong> — 3 paliers tarifaires (1er RDV, forfait, temps passé) + mention légale',
        '<strong>Section Cabinet</strong> — adresse, horaires, Google Maps embed, numéro garde à vue 24/7',
        '<strong>6 sections personnalisables</strong> depuis Apparence → Personnaliser → AG Starter',
        '<strong>Template single-domaine dédié</strong> — chaque domaine a sa propre page avec exemples',
        '100% responsive, blog, commentaires, recherche, compatible Gutenberg',
    ),

    'premium_features' => array(
        'Animations douces sur les domaines d\'expertise (fade-in + hover)',
        '<strong>Sticky header</strong> avec téléphone toujours visible — facilite l\'appel depuis mobile',
        '10 blocs Gutenberg juridiques (FAQ, timeline, témoignages anonymisés, "Avant/Après procédure")',
        'Customizer étendu : 50+ réglages (couleurs secondaires, bordures, espacements)',
        'Polices Google Fonts premium (Playfair, Cormorant — typographie sérieuse avocat)',
        'Notification par email plus stylée + copie automatique au demandeur',
        'Support email prioritaire 60 jours',
        'Documentation vidéo complète',
    ),

    'business_features' => array(
        '<strong>⚖️ Cabinet de recherche juridique inclus</strong> — Judilibre en direct, méta-moteur des sources officielles, salle d\'analyse de dossiers, banque d\'arguments + IA (voir ci-dessous)',
        '<strong>Tout Premium inclus</strong>',
        '<strong>Installation assistée en visio</strong> (1h avec notre équipe)',
        '<strong>Audit SEO juridique ciblé</strong> — mots-clés type "avocat divorce Paris", "avocat droit travail Lyon"',
        '<strong>Maintenance WordPress 1 an incluse</strong> (mises à jour, sauvegardes, sécurité)',
        'Rapport de performance trimestriel (trafic, conversion, position Google)',
        '<strong>Support prioritaire absolu</strong> (réponse sous 2h ouvrées)',
        '<strong>Publicité réduite</strong> — simple mention copyright Alliance Groupe dans le footer',
        'Intégration CRM (HubSpot, Pipedrive, Brevo) pour centraliser vos leads',
        'Appel stratégique de lancement avec Fabrizio (CEO Alliance Group)',
    ),

    'upsell_text' => 'Un template, même Premium, reste un template. Pour un cabinet qui joue pour gagner — défense pénale, gros dossiers d\'affaires, clientèle internationale — un site sur-mesure conçu par notre équipe va beaucoup plus loin : SEO juridique ciblé, stratégie de conversion éprouvée, et intégration IA pour automatiser la qualification des prospects. Premier appel avec Fabrizio gratuit, sans engagement.',
) );

get_template_part( 'template-parts/metier-page' );

/* ──────────────────────────────────────────────────────────────────────────
 * SECTION PROMO — ⚖️ Cabinet de recherche juridique (outil inclus avec le
 * template Avocat). Met en avant un atout exclusif vs les autres thèmes.
 * ──────────────────────────────────────────────────────────────────────── */
?>
<section class="ag-section ag-jr-promo" style="padding:80px 0;background:linear-gradient(160deg,#0b1020 0%,#10162b 60%,#0b1020 100%);color:#fff;">
    <div class="ag-container" style="max-width:1080px;margin:0 auto;padding:0 22px;">
        <p style="text-align:center;letter-spacing:3px;text-transform:uppercase;color:#D4B45C;font-size:.8rem;font-weight:700;margin:0 0 10px;">Exclusivité Alliance Groupe</p>
        <h2 style="text-align:center;font-size:clamp(1.7rem,4vw,2.6rem);margin:0 0 14px;line-height:1.15;">⚖️ Un cabinet de <span style="color:#D4B45C;">recherche juridique</span> intégré à votre site</h2>
        <p style="text-align:center;max-width:760px;margin:0 auto 16px;color:rgba(255,255,255,.78);font-size:1.05rem;">
            Bien plus qu'une vitrine : un véritable outil de travail privé pour le cabinet, accessible depuis votre propre site (espace réservé, connexion sécurisée). Aucun autre thème avocat ne le propose.
        </p>
        <p style="text-align:center;margin:0 auto 40px;">
            <span style="display:inline-block;background:rgba(212,180,92,.14);border:1px solid rgba(212,180,92,.5);color:#F1D98B;font-weight:700;padding:10px 20px;border-radius:999px;">✅ Recherche jurisprudence <strong>incluse</strong> — <strong>zéro configuration</strong>, aucun compte ni clé à créer</span>
        </p>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:18px;">
            <div style="background:rgba(255,255,255,.04);border:1px solid rgba(212,180,92,.25);border-radius:16px;padding:22px;">
                <div style="font-size:1.6rem;margin-bottom:8px;">🔎</div>
                <h3 style="margin:0 0 6px;font-size:1.1rem;color:#D4B45C;">Jurisprudence en direct</h3>
                <p style="margin:0;color:rgba(255,255,255,.72);font-size:.95rem;">Recherche LIVE dans l'open data officiel <strong>Judilibre</strong> (Cour de cassation + cours d'appel) : juridiction, chambre, date, sommaire, texte intégral. <strong>Incluse, sans compte ni clé à créer</strong> — filtres « font jurisprudence », année, matière.</p>
            </div>
            <div style="background:rgba(255,255,255,.04);border:1px solid rgba(212,180,92,.25);border-radius:16px;padding:22px;">
                <div style="font-size:1.6rem;margin-bottom:8px;">🌐</div>
                <h3 style="margin:0 0 6px;font-size:1.1rem;color:#D4B45C;">Toutes les sources</h3>
                <p style="margin:0;color:rgba(255,255,255,.72);font-size:.95rem;">Méta-moteur vers Légifrance, Conseil d'État, Conseil constitutionnel, CEDH, CJUE, EUR-Lex, doctrine, BOFiP, Pappers… une question, des liens pré-remplis.</p>
            </div>
            <div style="background:rgba(255,255,255,.04);border:1px solid rgba(212,180,92,.25);border-radius:16px;padding:22px;">
                <div style="font-size:1.6rem;margin-bottom:8px;">📁</div>
                <h3 style="margin:0 0 6px;font-size:1.1rem;color:#D4B45C;">Salle d'analyse de dossiers</h3>
                <p style="margin:0;color:rgba(255,255,255,.72);font-size:.95rem;">Faits · problème de droit · textes · jurisprudence favorable · arguments adverses · parade · stratégie · pièces. Vue imprimable / export PDF.</p>
            </div>
            <div style="background:rgba(255,255,255,.04);border:1px solid rgba(212,180,92,.25);border-radius:16px;padding:22px;">
                <div style="font-size:1.6rem;margin-bottom:8px;">🗂️</div>
                <h3 style="margin:0 0 6px;font-size:1.1rem;color:#D4B45C;">Banque d'arguments + IA</h3>
                <p style="margin:0;color:rgba(255,255,255,.72);font-size:.95rem;">Bibliothèque de moyens réutilisables, et assistant IA optionnel pour anticiper les arguments adverses et bâtir la stratégie.</p>
            </div>
        </div>
        <p style="text-align:center;margin:36px 0 0;color:rgba(255,255,255,.5);font-size:.82rem;max-width:760px;margin-left:auto;margin-right:auto;">
            Outil d'aide à la recherche et à l'analyse, réservé au cabinet — il ne donne pas de consultation et l'avocat vérifie toujours les sources primaires (déontologie respectée).
        </p>
        <div style="text-align:center;margin-top:26px;">
            <a href="<?php echo esc_url( home_url( '/templates-wordpress#ag-creer-mon-site' ) ); ?>" class="ag-btn-gold" style="display:inline-block;padding:14px 30px;border-radius:999px;background:linear-gradient(135deg,#D4B45C,#F37A1F);color:#10100a;font-weight:800;text-decoration:none;">Obtenir le template Avocat →</a>
        </div>
    </div>
</section>
<?php

get_footer();
