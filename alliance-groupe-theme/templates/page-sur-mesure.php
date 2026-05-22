<?php
/**
 * Template Name: Projet sur-mesure
 *
 * Offre sur-mesure (sans prix fixe, sur devis) pour les clients exigeants.
 * 100% gratuit côté outils : la prise de contact passe par le formulaire,
 * pas de service de rendez-vous payant.
 */
get_header();
?>

<main id="ag-main-content">

	<!-- Hero -->
	<section class="ag-hero" style="min-height:54vh;">
		<div class="ag-hero__bg">
			<div class="ag-hero__orb ag-hero__orb--1"></div>
			<div class="ag-hero__orb ag-hero__orb--2"></div>
		</div>
		<div class="ag-hero__content">
			<span class="ag-tag ag-anim" data-anim="tag">Sur-mesure ✦ Sur devis</span>
			<h1 class="ag-hero__title">
				<span class="ag-line">Un site <em>sur-mesure</em>,</span>
				<span class="ag-line">à la hauteur de vos ambitions.</span>
			</h1>
			<p class="ag-hero__sub">
				Pour les projets exigeants : e-commerce complexe, plateformes, espaces membres,
				fonctionnalités avancées, IA &amp; automatisations. On conçoit tout sur-mesure, sans limite.
				<strong>Devis gratuit, sans engagement.</strong>
			</p>
			<div class="ag-hero__buttons">
				<a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" class="ag-btn-gold">Parlons de votre projet →</a>
				<a href="<?php echo esc_url( home_url( '/sites-express' ) ); ?>" class="ag-btn-outline">Voir les offres à prix fixe</a>
			</div>
		</div>
	</section>

	<!-- Ce que comprend le sur-mesure -->
	<section class="ag-section ag-section--graphite">
		<div class="ag-container">
			<span class="ag-tag ag-anim" data-anim="tag">Sans limite</span>
			<h2 class="ag-section__title ag-anim" data-anim="title">Tout est <em>possible</em></h2>
			<p class="ag-section__desc ag-anim" data-anim="desc">Quand un pack standard ne suffit pas, on construit exactement ce dont votre activité a besoin.</p>
			<div class="ag-sm-grid">
				<?php
				$ag_sm_feats = array(
					array( '🧩', 'Pages &amp; fonctionnalités illimitées', 'Aucune contrainte de gabarit. On crée chaque page et chaque module selon votre besoin réel.' ),
					array( '🎨', 'Design 100% unique', 'Une identité visuelle entièrement à votre image, pensée pour marquer et convertir.' ),
					array( '🛒', 'E-commerce &amp; réservation avancés', 'Boutique complexe, paiements, abonnements, prise de commande, espace client.' ),
					array( '🤖', 'IA &amp; automatisation', 'Chatbots, workflows, génération de contenu, agents qui travaillent pour vous 24h/24.' ),
					array( '🔗', 'Intégrations sur-mesure', 'CRM, ERP, API, outils métiers, paiement : on connecte votre site à tout votre écosystème.' ),
					array( '⚡', 'Performance &amp; SEO poussés', 'Architecture optimisée, vitesse, référencement avancé, suivi des résultats.' ),
				);
				foreach ( $ag_sm_feats as $f ) : ?>
					<div class="ag-sm-card ag-anim" data-anim="card">
						<span class="ag-sm-card__ic"><?php echo $f[0]; // phpcs:ignore ?></span>
						<h3><?php echo $f[1]; // phpcs:ignore ?></h3>
						<p><?php echo $f[2]; // phpcs:ignore ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- Comment ça se passe -->
	<section class="ag-section ag-section--light">
		<div class="ag-container">
			<span class="ag-tag ag-anim" data-anim="tag">Simple &amp; gratuit</span>
			<h2 class="ag-section__title ag-anim" data-anim="title">Comment ça se <em>passe</em></h2>
			<p class="ag-section__desc ag-anim" data-anim="desc">Pas de rendez-vous imposé, pas d'outil payant. Tout démarre par un simple message.</p>
			<div class="ag-sm-steps">
				<div class="ag-sm-step ag-anim" data-anim="card"><span class="ag-sm-step__n">1</span><h3>Décrivez votre projet</h3><p>Via le formulaire de contact, en quelques lignes. C'est gratuit et sans engagement.</p></div>
				<div class="ag-sm-step ag-anim" data-anim="card"><span class="ag-sm-step__n">2</span><h3>Devis sur-mesure</h3><p>On étudie votre besoin et on vous envoie une proposition détaillée, claire et chiffrée.</p></div>
				<div class="ag-sm-step ag-anim" data-anim="card"><span class="ag-sm-step__n">3</span><h3>Conception &amp; production</h3><p>On crée votre site avec des points d'étape réguliers. Vous suivez l'avancement.</p></div>
				<div class="ag-sm-step ag-anim" data-anim="card"><span class="ag-sm-step__n">4</span><h3>Livraison &amp; suivi</h3><p>Mise en ligne, formation et accompagnement. On reste disponible après le lancement.</p></div>
			</div>
		</div>
	</section>

	<!-- Tarif + CTA -->
	<section class="ag-section ag-section--graphite">
		<div class="ag-container">
			<div class="ag-sm-cta ag-anim" data-anim="card">
				<span class="ag-tag">Sur devis</span>
				<h2 class="ag-sm-cta__title">Chaque projet est <em>unique</em></h2>
				<p class="ag-sm-cta__price">Généralement de <strong>1&nbsp;500&nbsp;€</strong> à <strong>15&nbsp;000&nbsp;€+</strong> selon l'ampleur — devis personnalisé et gratuit.</p>
				<a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" class="ag-btn-gold ag-btn-gold--xl">Décrivez-nous votre projet →</a>
				<div class="ag-sm-cta__reassure">
					<span>✓ Devis gratuit</span>
					<span>✓ Sans engagement</span>
					<span>✓ Réponse sous 24h</span>
				</div>
			</div>
		</div>
	</section>

</main>

<style>
.ag-sm-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-top:36px;}
.ag-sm-card{background:rgba(255,255,255,.03);border:1px solid rgba(212,180,92,.22);border-radius:18px;padding:26px 24px;display:flex;flex-direction:column;gap:8px;}
.ag-sm-card__ic{font-size:2.1rem;}
.ag-sm-card h3{color:#fff;font-family:var(--font-serif);font-size:1.2rem;margin:0;}
.ag-sm-card p{color:var(--color-text-soft);line-height:1.55;margin:0;}
.ag-sm-steps{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;margin-top:36px;}
.ag-sm-step{background:#fff;border:1px solid rgba(212,180,92,.4);border-radius:18px;padding:26px 22px;box-shadow:0 12px 34px rgba(120,100,40,.12);}
.ag-sm-step__n{display:inline-flex;align-items:center;justify-content:center;width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#D4B45C,#F37A1F);color:#10100a;font-weight:800;font-size:1.1rem;margin-bottom:12px;}
.ag-sm-step h3{color:#17150f;font-family:var(--font-serif);font-size:1.15rem;margin:0 0 6px;}
.ag-sm-step p{color:#5b5446;line-height:1.5;margin:0;}
.ag-sm-cta{text-align:center;max-width:760px;margin:0 auto;background:rgba(255,255,255,.03);border:1px solid rgba(212,180,92,.3);border-radius:24px;padding:48px 32px;}
.ag-sm-cta__title{color:#fff;font-family:var(--font-serif);font-size:2rem;margin:14px 0 10px;}
.ag-sm-cta__price{color:var(--color-text-soft);font-size:1.05rem;line-height:1.6;margin:0 0 26px;}
.ag-sm-cta__price strong{color:#D4B45C;}
.ag-btn-gold--xl{font-size:1.05rem;padding:16px 30px;}
.ag-sm-cta__reassure{display:flex;flex-wrap:wrap;justify-content:center;gap:10px 22px;margin-top:24px;color:var(--color-text-soft);font-weight:600;font-size:.92rem;}
@media(max-width:900px){.ag-sm-grid{grid-template-columns:1fr;}.ag-sm-steps{grid-template-columns:1fr 1fr;}}
@media(max-width:560px){.ag-sm-steps{grid-template-columns:1fr;}}
</style>

<?php get_footer(); ?>
