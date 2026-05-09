<?php
/**
 * Block patterns Gutenberg pour AG Starter Association.
 *
 * Permet a l'utilisateur d'inserer des sections pre-stylees (hero,
 * combats, evenements, dons, signer...) dans n'importe quelle page
 * via l'editeur de blocs (onglet "Patterns" / "Compositions").
 *
 * Le rendu utilise les memes classes CSS que le theme (.ag-asso-*),
 * donc visuellement identique aux sections de la page d'accueil.
 * L'utilisateur edite ensuite chaque texte directement dans le bloc.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', function () {
	if ( ! function_exists( 'register_block_pattern_category' ) ) return;

	register_block_pattern_category( 'ag-asso', array(
		'label' => __( 'AG Association — sections', 'ag-starter-association' ),
	) );

	// ─── 1. Hero ────────────────────────────────────────────────────
	register_block_pattern( 'ag-asso/hero', array(
		'title'       => __( 'AG — Hero (slogan + titre + CTAs)', 'ag-starter-association' ),
		'description' => __( 'Bandeau principal avec titre de mobilisation et 2 boutons.', 'ag-starter-association' ),
		'categories'  => array( 'ag-asso' ),
		'content'     => '<!-- wp:html -->
<section class="ag-asso-hero"><div class="ag-asso-hero__inner">
<span class="ag-asso-hero__tag">Pour une France plus juste</span>
<h1 class="ag-asso-hero__title">Le grand titre de mobilisation</h1>
<p class="ag-asso-hero__sub">Description courte du combat — 2 lignes maximum pour expliquer votre engagement.</p>
<div class="ag-asso-hero__ctas">
<a href="/signer/" class="ag-asso-btn ag-asso-btn--primary">Rejoindre le mouvement</a>
<a href="/don/" class="ag-asso-btn ag-asso-btn--ghost">Faire un don</a>
</div>
</div></section>
<!-- /wp:html -->',
	) );

	// ─── 2. Bandeau parallax ───────────────────────────────────────
	register_block_pattern( 'ag-asso/parallax', array(
		'title'       => __( 'AG — Bandeau parallax (titre + texte)', 'ag-starter-association' ),
		'description' => __( 'Bandeau plein largeur avec titre et phrase, fond sombre.', 'ag-starter-association' ),
		'categories'  => array( 'ag-asso' ),
		'content'     => '<!-- wp:html -->
<section class="ag-asso-parallax">
<h2 class="ag-asso-parallax__title">Notre vision</h2>
<p class="ag-asso-parallax__text">Une société plus juste, plus solidaire — c\'est notre combat quotidien.</p>
</section>
<!-- /wp:html -->',
	) );

	// ─── 3. Section avec titre (généraliste) ───────────────────────
	register_block_pattern( 'ag-asso/section', array(
		'title'       => __( 'AG — Section (titre + intro)', 'ag-starter-association' ),
		'description' => __( 'Section avec titre H2 (mot rouge en italique), phrase d\'intro et zone de contenu libre.', 'ag-starter-association' ),
		'categories'  => array( 'ag-asso' ),
		'content'     => '<!-- wp:html -->
<section class="ag-asso-section">
<div class="ag-asso-container">
<h2 class="ag-asso-section__title">Notre <em>section</em></h2>
<p class="ag-asso-section__lead">Une phrase d\'introduction qui explique le sujet de la section, courte et percutante.</p>
</div>
</section>
<!-- /wp:html -->

<!-- wp:paragraph -->
<p>Votre contenu ici (textes, images, listes...). L\'editeur Gutenberg standard fonctionne dans n\'importe quel bloc en dessous.</p>
<!-- /wp:paragraph -->',
	) );

	// ─── 4. Grille combats (6 cartes avec emoji + couleur) ─────────
	register_block_pattern( 'ag-asso/combats-grid', array(
		'title'       => __( 'AG — Grille combats (6 cartes colorées)', 'ag-starter-association' ),
		'description' => __( '6 cartes avec emoji et couleur de fond. Modifiez chaque emoji/titre/texte directement.', 'ag-starter-association' ),
		'categories'  => array( 'ag-asso' ),
		'content'     => '<!-- wp:html -->
<section class="ag-asso-section ag-asso-section--alt">
<div class="ag-asso-container">
<h2 class="ag-asso-section__title">Nos <em>combats</em></h2>
<p class="ag-asso-section__lead">Six grandes campagnes que nous portons.</p>
<div class="ag-asso-combats-grid">
<article class="ag-asso-combat"><div class="ag-asso-combat__icon" style="background:#1F8A3D;">🌍</div><h3>Justice climatique</h3><p>Description du combat 1.</p></article>
<article class="ag-asso-combat"><div class="ag-asso-combat__icon" style="background:#3B5998;">🏠</div><h3>Logement digne</h3><p>Description du combat 2.</p></article>
<article class="ag-asso-combat"><div class="ag-asso-combat__icon" style="background:#E10F1A;">🏥</div><h3>Service public fort</h3><p>Description du combat 3.</p></article>
<article class="ag-asso-combat"><div class="ag-asso-combat__icon" style="background:#8B1A8B;">🗳️</div><h3>Démocratie réelle</h3><p>Description du combat 4.</p></article>
<article class="ag-asso-combat"><div class="ag-asso-combat__icon" style="background:#0A0A0D;">🔍</div><h3>Transparence publique</h3><p>Description du combat 5.</p></article>
<article class="ag-asso-combat"><div class="ag-asso-combat__icon" style="background:#FFD23F;">⚖️</div><h3>Égalité réelle</h3><p>Description du combat 6.</p></article>
</div>
</div>
</section>
<!-- /wp:html -->',
	) );

	// ─── 5. Cartes de dons ─────────────────────────────────────────
	register_block_pattern( 'ag-asso/don-cards', array(
		'title'       => __( 'AG — Cartes de dons (montants + don libre)', 'ag-starter-association' ),
		'description' => __( '4 montants + don libre, avec calcul du coût réel après déduction fiscale.', 'ag-starter-association' ),
		'categories'  => array( 'ag-asso' ),
		'content'     => '<!-- wp:html -->
<section class="ag-asso-section ag-asso-section--alt">
<div class="ag-asso-container">
<h2 class="ag-asso-section__title">Faire un <em>don</em></h2>
<p class="ag-asso-section__lead">Indépendants des partis. 66% de votre don est déductible.</p>
<div class="ag-asso-don-grid">
<a href="/don/" class="ag-asso-don-card"><span class="ag-asso-don-card__amount">5€</span><span class="ag-asso-don-card__note">Coût réel : 1,70€</span></a>
<a href="/don/" class="ag-asso-don-card"><span class="ag-asso-don-card__amount">20€</span><span class="ag-asso-don-card__note">Coût réel : 6,80€</span></a>
<a href="/don/" class="ag-asso-don-card"><span class="ag-asso-don-card__amount">50€</span><span class="ag-asso-don-card__note">Coût réel : 17€</span></a>
<a href="/don/" class="ag-asso-don-card"><span class="ag-asso-don-card__amount">100€</span><span class="ag-asso-don-card__note">Coût réel : 34€</span></a>
<a href="/don/" class="ag-asso-don-card ag-asso-don-card--free"><span class="ag-asso-don-card__amount">Libre</span></a>
</div>
</div>
</section>
<!-- /wp:html -->',
	) );

	// ─── 6. CTA signature pétition ─────────────────────────────────
	register_block_pattern( 'ag-asso/cta-signer', array(
		'title'       => __( 'AG — CTA Signer la pétition', 'ag-starter-association' ),
		'description' => __( 'Appel à signer avec formulaire prénom/email/RGPD.', 'ag-starter-association' ),
		'categories'  => array( 'ag-asso' ),
		'content'     => '<!-- wp:html -->
<section class="ag-asso-section ag-asso-section--cta">
<div class="ag-asso-container">
<h2 class="ag-asso-section__title">Signez <em>l\'appel</em></h2>
<p class="ag-asso-section__lead">Pour une société plus juste, écologique et démocratique.</p>
<form class="ag-asso-form" action="#" method="post">
<input type="text" name="prenom" placeholder="Prénom" required>
<input type="text" name="nom" placeholder="Nom" required>
<input type="email" name="email" placeholder="Email" required>
<input type="text" name="cp" placeholder="Code postal" required>
<label class="ag-asso-form__rgpd"><input type="checkbox" required><span>J\'accepte que mes données soient traitées dans le cadre de cet engagement.</span></label>
<button type="submit" class="ag-asso-btn ag-asso-btn--primary">Je signe</button>
</form>
</div>
</section>
<!-- /wp:html -->',
	) );
} );
