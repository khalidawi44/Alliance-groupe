<?php
/**
 * Template Name: Système de prospection
 *
 * Offre PRODUIT : on vend le système de prospection/CRM lui-même
 * (le "logiciel de tri & recherche de clients" construit pour Alliance Groupe).
 * Prix décidé : 1 990 € installation + 49 €/mois.
 */
get_header();
?>
<main id="ag-main-content">

	<section class="ag-hero" style="min-height:58vh;">
		<div class="ag-hero__bg"><div class="ag-hero__orb ag-hero__orb--1"></div><div class="ag-hero__orb ag-hero__orb--2"></div></div>
		<div class="ag-hero__content">
			<span class="ag-tag ag-anim" data-anim="tag">Système exclusif Alliance Groupe</span>
			<h1 class="ag-hero__title">
				<span class="ag-line">Le logiciel qui <em>trouve</em></span>
				<span class="ag-line">vos clients à votre place.</span>
			</h1>
			<p class="ag-hero__sub">
				Le moteur de prospection &amp; le CRM qu'on a bâti pour notre propre croissance —
				maintenant disponible pour <strong>votre activité ou votre équipe commerciale</strong>.
			</p>
			<div class="ag-hero__buttons">
				<a href="<?php echo esc_url( home_url( '/contact?source=systeme-prospection' ) ); ?>" class="ag-btn-gold">Demander une démo →</a>
				<a href="#prix" class="ag-btn-outline">Voir le prix</a>
			</div>
		</div>
	</section>

	<section class="ag-section ag-section--graphite">
		<div class="ag-container">
			<span class="ag-tag ag-anim" data-anim="tag">Ce que fait le système</span>
			<h2 class="ag-section__title ag-anim" data-anim="title">Une <em>machine à clients</em>, clé en main</h2>
			<p class="ag-section__desc ag-anim" data-anim="desc">Il trouve, trie, priorise et vous met le bon contact entre les mains. Vous n'avez plus qu'à parler aux bonnes personnes.</p>
			<div class="ag-sp-grid">
				<?php
				$ag_sp = array(
					array( '🔎', 'Recherche automatique', 'Trouve les entreprises de n\'importe quelle ville/secteur via Google — repère celles <strong>sans vrai site</strong> (réseaux &amp; plateformes type Planity = pas un vrai site).' ),
					array( '🎯', 'Score de priorité', 'Classe chaque prospect par <strong>probabilité d\'achat</strong> (manque de site, avis, note, joignable) — vous attaquez les meilleurs d\'abord.' ),
					array( '🗺️', 'Zones &amp; équipe', 'Territoires par département, <strong>assignation automatique</strong> aux commerciaux — zéro doublon, zéro concurrence interne.' ),
					array( '📋', 'CRM complet', 'Suivi contacté / répondu / relancé, <strong>notes par fiche</strong>, anti-doublon global, liste d\'ignorés, historique des recherches.' ),
					array( '💬', 'Messages prêts', 'Messages <strong>personnalisés</strong> (WhatsApp / SMS / Email) générés à partir des problèmes repérés — envoi en 1 clic, suivi auto.' ),
					array( '📲', 'Pilotage mobile', 'Feuille de route quotidienne + alertes (nouveau lead, réponse, vente) <strong>directement sur le téléphone</strong> (Telegram).' ),
					array( '🏛️', 'Signaux exclusifs', 'Repère même les entreprises <strong>au tribunal</strong> (redressement) — données publiques, approche dédiée.' ),
					array( '🤝', 'Programme commerciaux', 'Recrutez des ambassadeurs, commissions automatiques, classement — votre force de vente intégrée.' ),
				);
				foreach ( $ag_sp as $f ) : ?>
					<div class="ag-sp-card ag-anim" data-anim="card"><span class="ag-sp-ic"><?php echo $f[0]; // phpcs:ignore ?></span><h3><?php echo $f[1]; // phpcs:ignore ?></h3><p><?php echo $f[2]; // phpcs:ignore ?></p></div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<?php
	// Galerie : affiche les captures présentes dans assets/images/systeme-prospection/
	$ag_sp_dir = get_stylesheet_directory() . '/assets/images/systeme-prospection/';
	$ag_sp_uri = get_stylesheet_directory_uri() . '/assets/images/systeme-prospection/';
	$ag_sp_imgs = array();
	foreach ( array( 'apercu', 'capture-1', 'capture-2', 'capture-3', 'capture-4', 'capture-5', 'capture-6' ) as $ag_sp_slug ) {
		foreach ( array( 'jpg', 'png', 'webp', 'jpeg' ) as $ag_sp_ext ) {
			if ( file_exists( $ag_sp_dir . $ag_sp_slug . '.' . $ag_sp_ext ) ) { $ag_sp_imgs[] = $ag_sp_uri . $ag_sp_slug . '.' . $ag_sp_ext; break; }
		}
	}
	if ( $ag_sp_imgs ) : ?>
	<section class="ag-section ag-section--onyx">
		<div class="ag-container">
			<span class="ag-tag ag-anim" data-anim="tag">En images</span>
			<h2 class="ag-section__title ag-anim" data-anim="title">Le système <em>en action</em></h2>
			<div class="ag-sp-shots">
				<?php foreach ( $ag_sp_imgs as $ag_sp_img ) : ?>
					<img src="<?php echo esc_url( $ag_sp_img ); ?>" alt="Système de prospection Alliance Groupe" loading="lazy">
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php elseif ( current_user_can( 'manage_options' ) ) : ?>
	<section class="ag-section ag-section--onyx">
		<div class="ag-container" style="text-align:center;color:var(--color-text-soft);">
			<p>🖼️ <strong>Admin :</strong> dépose tes captures dans <code>assets/images/systeme-prospection/</code> (nommées <code>apercu.jpg</code>, <code>capture-1.jpg</code> … <code>capture-6.jpg</code>) — elles s'afficheront ici automatiquement.</p>
		</div>
	</section>
	<?php endif; ?>

	<section class="ag-section ag-section--light">
		<div class="ag-container">
			<span class="ag-tag ag-anim" data-anim="tag">Pour qui ?</span>
			<h2 class="ag-section__title ag-anim" data-anim="title">Conçu pour ceux qui <em>veulent vendre</em></h2>
			<p class="ag-section__desc ag-anim" data-anim="desc">Agences web, indépendants, artisans ambitieux, réseaux de commerciaux, franchises : tous ceux qui veulent un flux de prospects qualifiés sans y passer leurs journées.</p>
		</div>
	</section>

	<section class="ag-section ag-section--graphite" id="prix">
		<div class="ag-container">
			<div class="ag-sp-cta ag-anim" data-anim="card">
				<span class="ag-tag">Tarif</span>
				<h2 class="ag-sp-cta__title">Votre machine à clients</h2>
				<p class="ag-sp-cta__price"><strong>1 990 €</strong> installation + <strong>49 €/mois</strong></p>
				<p class="ag-sp-cta__desc">Inclus : installation sur votre site, configuration de vos zones &amp; secteurs, formation, mises à jour et support. Sans engagement, résiliable à tout moment.</p>
				<a href="<?php echo esc_url( home_url( '/contact?source=systeme-prospection' ) ); ?>" class="ag-btn-gold ag-btn-gold--xl">Je veux ce système →</a>
				<p style="margin-top:14px;"><a href="<?php echo esc_url( home_url( '/sur-mesure' ) ); ?>" style="color:var(--color-gold);">Ou intégrez-le à un projet sur-mesure complet →</a></p>
			</div>
		</div>
	</section>

</main>
<style>
.ag-sp-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;margin-top:36px;}
.ag-sp-card{background:rgba(255,255,255,.03);border:1px solid rgba(212,180,92,.22);border-radius:16px;padding:22px 20px;display:flex;flex-direction:column;gap:8px;}
.ag-sp-ic{font-size:1.9rem;}
.ag-sp-card h3{color:#fff;font-family:var(--font-serif);font-size:1.12rem;margin:0;}
.ag-sp-card p{color:var(--color-text-soft);line-height:1.5;margin:0;font-size:.92rem;}
.ag-sp-cta{text-align:center;max-width:620px;margin:0 auto;background:rgba(255,255,255,.03);border:1px solid rgba(212,180,92,.35);border-radius:24px;padding:44px 30px;}
.ag-sp-cta__title{color:#fff;font-family:var(--font-serif);font-size:2rem;margin:12px 0 8px;}
.ag-sp-cta__price{font-size:1.5rem;color:var(--color-text-soft);margin:0 0 10px;}
.ag-sp-cta__price strong{color:#D4B45C;}
.ag-sp-cta__desc{color:var(--color-text-soft);line-height:1.6;margin:0 0 22px;}
.ag-sp-shots{display:grid;grid-template-columns:repeat(2,1fr);gap:18px;margin-top:34px;}
.ag-sp-shots img{width:100%;height:auto;border-radius:16px;border:1px solid rgba(212,180,92,.3);box-shadow:0 16px 40px rgba(0,0,0,.45);display:block;}
@media(max-width:760px){.ag-sp-shots{grid-template-columns:1fr;}}
@media(max-width:1000px){.ag-sp-grid{grid-template-columns:repeat(2,1fr);}}
@media(max-width:560px){.ag-sp-grid{grid-template-columns:1fr;}}
</style>
<?php get_footer(); ?>
