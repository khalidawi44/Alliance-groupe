<?php
/**
 * Template Name: Pourquoi Alliance (vs ThemeForest)
 *
 * Page de comparaison honnête vs marketplaces génériques (ThemeForest,
 * Wix, Templatemonster). Stratégie : assumer la comparaison plutôt que
 * la fuir → différenciation claire → conversion vers sur-mesure.
 */
get_header();
?>

<main>

<!-- HERO -->
<section class="ag-hero" style="min-height:60vh;">
	<div class="ag-hero__bg">
		<div class="ag-hero__orb ag-hero__orb--1"></div>
		<div class="ag-hero__orb ag-hero__orb--2"></div>
	</div>
	<div class="ag-hero__content">
		<span class="ag-tag ag-anim" data-anim="tag">Comparatif honnête</span>
		<h1 class="ag-hero__title">
			<span class="ag-line">ThemeForest vs <em>Alliance</em></span>
			<span class="ag-line">Le match en toute transparence</span>
		</h1>
		<p class="ag-hero__sub">Vous hésitez entre acheter un thème générique sur une marketplace internationale ou passer par une agence française ? Voici la comparaison sans fard.</p>
	</div>
</section>

<!-- INTRO -->
<section class="ag-section">
	<div class="ag-container" style="max-width:760px;">
		<h2 style="font-size:clamp(1.4rem,2.5vw,2rem);color:#fff;margin-bottom:18px;text-align:center;">On ne joue pas le même match</h2>
		<p style="color:rgba(255,255,255,.78);font-size:1.05rem;line-height:1.75;text-align:center;">
			ThemeForest, c'est <strong style="color:#D4B45C;">50 000 thèmes pour 30$</strong> — un supermarché du template. Excellent pour qui a déjà des compétences web. Alliance Groupe, c'est <strong style="color:#D4B45C;">6 thèmes par métier + une équipe française derrière</strong>. Excellent pour qui veut un résultat sans devenir développeur. <em>Ce sont deux philosophies, pas deux concurrents directs.</em>
		</p>
	</div>
</section>

<!-- TABLEAU COMPARATIF -->
<section class="ag-section ag-section--marbre">
	<div class="ag-container">
		<h2 class="ag-section__title" style="text-align:center;">Tableau de <em>comparaison</em></h2>

		<div class="ag-comparo-table">
			<div class="ag-comparo-row ag-comparo-row--head">
				<div class="ag-comparo-cell ag-comparo-cell--label">Critère</div>
				<div class="ag-comparo-cell ag-comparo-cell--them">ThemeForest</div>
				<div class="ag-comparo-cell ag-comparo-cell--us">Alliance Groupe</div>
			</div>

			<?php
			$rows = array(
				array( 'Catalogue', '50 000+ thèmes génériques', '6 thèmes par métier (Coach, Artisan, Avocat, Barber, Resto, Asso)' ),
				array( 'Prix d entrée', '30-60$ achat unique', '0€ gratuit + Premium 69€ (optionnel)' ),
				array( 'Adaptation au métier', 'Vous adaptez le thème', 'Déjà adapté à votre métier' ),
				array( 'Contenu pré-rédigé', 'Lorem ipsum à remplacer', 'Textes français déjà écrits' ),
				array( 'Support', 'Auteur (souvent absent)', 'Équipe FR 24h max' ),
				array( 'Installation', 'Vous installez tout seul', 'Simple : ZIP prêt à installer (aide en option)' ),
				array( 'Mise à jour 12 mois', 'Souvent payante', 'Incluse avec le Premium' ),
				array( 'Langue', 'Anglais', 'Français natif' ),
				array( 'Fuseau horaire', 'US/AU (réponse à J+1)', 'France (réponse même jour)' ),
				array( 'Audit SEO', 'Non', 'En option' ),
				array( 'Possibilité sur-mesure', 'Non', 'Oui — 1 500 à 15 000€' ),
				array( 'IA / Automatisations', 'Non', 'Oui (service dédié)' ),
				array( 'Photos métier intégrées', 'Photos stock génériques', 'Photos Unsplash sélectionnées par métier' ),
				array( 'Cas d usage', 'Bricoleur web confirmé', 'Indépendant / TPE qui veut un résultat' ),
			);
			foreach ( $rows as $r ) :
			?>
				<div class="ag-comparo-row">
					<div class="ag-comparo-cell ag-comparo-cell--label"><?php echo esc_html( $r[0] ); ?></div>
					<div class="ag-comparo-cell ag-comparo-cell--them"><?php echo esc_html( $r[1] ); ?></div>
					<div class="ag-comparo-cell ag-comparo-cell--us"><?php echo esc_html( $r[2] ); ?></div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- QUAND CHOISIR L'UN OU L'AUTRE -->
<section class="ag-section">
	<div class="ag-container">
		<h2 class="ag-section__title" style="text-align:center;">Choisissez selon <em>votre cas</em></h2>
		<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(360px,1fr));gap:24px;max-width:1100px;margin:40px auto 0;">
			<div style="background:linear-gradient(180deg,rgba(20,20,28,.9),rgba(10,10,15,.95));border:1px solid rgba(255,255,255,.1);border-radius:18px;padding:32px 28px;">
				<div style="font-size:2.4rem;margin-bottom:14px;">🛠️</div>
				<h3 style="font-family:Georgia,serif;font-size:1.5rem;color:#fff;margin:0 0 16px;">Allez sur ThemeForest si…</h3>
				<ul style="list-style:none;padding:0;margin:0;color:rgba(255,255,255,.8);font-size:.96rem;line-height:1.75;">
					<li style="padding:8px 0;border-bottom:1px solid rgba(255,255,255,.06);">✓ Vous savez installer un thème WP seul</li>
					<li style="padding:8px 0;border-bottom:1px solid rgba(255,255,255,.06);">✓ Vous êtes à l'aise en anglais</li>
					<li style="padding:8px 0;border-bottom:1px solid rgba(255,255,255,.06);">✓ Vous voulez un thème ultra-générique pour bricoler</li>
					<li style="padding:8px 0;border-bottom:1px solid rgba(255,255,255,.06);">✓ Vous n'avez pas besoin de support humain</li>
					<li style="padding:8px 0;">✓ Vous gérez votre SEO/Ads en interne</li>
				</ul>
			</div>
			<div style="background:linear-gradient(180deg,rgba(30,25,20,.95),rgba(20,15,10,.98));border:1px solid rgba(212,180,92,.4);border-radius:18px;padding:32px 28px;box-shadow:0 20px 60px rgba(212,180,92,.12);">
				<div style="font-size:2.4rem;margin-bottom:14px;">🇫🇷</div>
				<h3 style="font-family:Georgia,serif;font-size:1.5rem;color:#D4B45C;margin:0 0 16px;">Choisissez Alliance si…</h3>
				<ul style="list-style:none;padding:0;margin:0;color:rgba(255,255,255,.8);font-size:.96rem;line-height:1.75;">
					<li style="padding:8px 0;border-bottom:1px solid rgba(212,180,92,.1);">✓ Vous voulez un site adapté à votre métier dès l'install</li>
					<li style="padding:8px 0;border-bottom:1px solid rgba(212,180,92,.1);">✓ Vous parlez français et préférez du support FR</li>
					<li style="padding:8px 0;border-bottom:1px solid rgba(212,180,92,.1);">✓ Vous voulez un humain qui décroche quand ça coince</li>
					<li style="padding:8px 0;border-bottom:1px solid rgba(212,180,92,.1);">✓ Vous envisagez un site sur-mesure à terme</li>
					<li style="padding:8px 0;">✓ Vous voulez intégrer IA / automatisations / SEO local</li>
				</ul>
			</div>
		</div>
	</div>
</section>

<!-- ENCART : nos offres de templates -->
<section class="ag-section ag-section--or">
	<div class="ag-container">
		<div class="ag-pq-offres">
			<span class="ag-tag">Nos offres</span>
			<h2 class="ag-pq-offres__title">Découvrez nos <em>templates métier</em></h2>
			<p class="ag-pq-offres__sub">6 thèmes WordPress déjà adaptés à votre métier (gratuits + packs), ou un site clé en main livré en quelques jours. À vous de choisir.</p>
			<div class="ag-pq-offres__cards">
				<div class="ag-pq-offre">
					<span class="ag-pq-offre__ic">🎨</span>
					<h3>Templates WordPress</h3>
					<p>Coach, Artisan, Avocat, Barber, Resto, Asso. Gratuits, + version Premium (69€).</p>
					<a href="<?php echo esc_url( home_url( '/templates-wordpress' ) ); ?>" class="ag-btn-gold">Voir les templates →</a>
				</div>
				<div class="ag-pq-offre">
					<span class="ag-pq-offre__ic">⚡</span>
					<h3>Site clé en main</h3>
					<p>On crée tout pour vous : design, contenu, mise en ligne. Prix fixe, sans rendez-vous, livré en quelques jours.</p>
					<a href="<?php echo esc_url( home_url( '/sites-express' ) ); ?>" class="ag-btn-outline">Voir les packs Express →</a>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- CTA AUDIT GRATUIT -->
<?php
$GLOBALS['ag_audit_cta_args'] = array(
	'title'    => 'Pas sûr ? On vous aide à choisir.',
	'subtitle' => "30 min en visio, on regarde votre projet et on vous dit honnêtement : ThemeForest suffit, ou Alliance fait mieux dans votre cas. Sans engagement.",
	'cta'      => 'Audit gratuit →',
);
get_template_part( 'template-parts/audit-cta' );
unset( $GLOBALS['ag_audit_cta_args'] );
?>

</main>

<style>
.ag-comparo-table{max-width:1100px;margin:50px auto 0;background:rgba(20,20,28,.6);border:1px solid rgba(212,180,92,.15);border-radius:18px;overflow:hidden;backdrop-filter:blur(12px)}
.ag-comparo-row{display:grid;grid-template-columns:1.3fr 1fr 1fr;gap:0;border-bottom:1px solid rgba(255,255,255,.06)}
.ag-comparo-row:last-child{border-bottom:none}
.ag-comparo-row--head{background:linear-gradient(135deg,rgba(212,180,92,.15) 0%,rgba(243,122,31,.1) 100%);border-bottom:1px solid rgba(212,180,92,.3)}
.ag-comparo-row:not(.ag-comparo-row--head):hover{background:rgba(212,180,92,.04)}
.ag-comparo-cell{padding:16px 18px;font-size:.95rem;line-height:1.5;color:rgba(255,255,255,.82);border-right:1px solid rgba(255,255,255,.04)}
.ag-comparo-cell:last-child{border-right:none}
.ag-comparo-cell--label{font-weight:700;color:rgba(212,180,92,.9);background:rgba(0,0,0,.2)}
.ag-comparo-cell--them{color:rgba(255,255,255,.6)}
.ag-comparo-cell--us{color:#D4B45C;font-weight:600;background:rgba(212,180,92,.04)}
.ag-comparo-row--head .ag-comparo-cell{font-weight:800;letter-spacing:1.5px;text-transform:uppercase;font-size:.82rem;color:#D4B45C}
@media (max-width:768px){
	/* En-tete inutile une fois empile : chaque valeur est etiquetee ci-dessous */
	.ag-comparo-row--head{display:none}
	/* Chaque critere = une carte autonome, claire */
	.ag-comparo-table{background:transparent;border:none;border-radius:0;overflow:visible}
	.ag-comparo-row{grid-template-columns:1fr;background:rgba(20,20,28,.7);border:1px solid rgba(212,180,92,.18);border-radius:14px;margin-bottom:14px;overflow:hidden}
	.ag-comparo-cell{border-right:none;border-bottom:1px solid rgba(255,255,255,.06);padding:12px 16px}
	.ag-comparo-cell:last-child{border-bottom:none}
	.ag-comparo-cell--label{background:rgba(212,180,92,.14);font-size:1rem;font-weight:800;color:#D4B45C;letter-spacing:.3px}
	/* Etiquette chaque valeur : on sait toujours qui est qui */
	.ag-comparo-cell--them::before{content:"❌ ThemeForest";display:block;font-size:.7rem;font-weight:800;letter-spacing:1px;text-transform:uppercase;color:rgba(255,255,255,.45);margin-bottom:3px}
	.ag-comparo-cell--us::before{content:"✅ Alliance Groupe";display:block;font-size:.7rem;font-weight:800;letter-spacing:1px;text-transform:uppercase;color:#46b450;margin-bottom:3px}
	.ag-comparo-cell--them{color:rgba(255,255,255,.6)}
	.ag-comparo-cell--us{background:rgba(212,180,92,.06)}
}
.ag-pq-offres{text-align:center;max-width:1000px;margin:0 auto;}
.ag-pq-offres__title{font-family:Georgia,'Playfair Display',serif;font-size:clamp(1.8rem,3.5vw,2.6rem);color:#fff;margin:14px 0 12px;}
.ag-pq-offres__title em{background:linear-gradient(135deg,#D4B45C,#F37A1F);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;color:transparent;font-style:italic;}
.ag-pq-offres__sub{color:rgba(255,255,255,.72);max-width:620px;margin:0 auto 36px;line-height:1.6;}
.ag-pq-offres__cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,300px),1fr));gap:22px;text-align:left;}
.ag-pq-offre{display:flex;flex-direction:column;background:rgba(255,255,255,.03);border:1px solid rgba(212,180,92,.2);border-radius:18px;padding:30px 26px;}
.ag-pq-offre__ic{font-size:2.2rem;margin-bottom:12px;}
.ag-pq-offre h3{font-family:Georgia,'Playfair Display',serif;font-size:1.4rem;color:#fff;margin:0 0 10px;}
.ag-pq-offre p{color:rgba(255,255,255,.72);font-size:.96rem;line-height:1.6;margin:0 0 22px;flex:1;}
.ag-pq-offre .ag-btn-gold,.ag-pq-offre .ag-btn-outline{align-self:flex-start;}
</style>

<?php get_footer(); ?>
