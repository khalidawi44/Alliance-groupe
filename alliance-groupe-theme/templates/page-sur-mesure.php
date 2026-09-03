<?php
/**
 * Template Name: Projet sur-mesure
 *
 * Offre PREMIUM sur-mesure : le client compose son projet (type, style,
 * fonctionnalités, pages, budget) et envoie une demande de DEVIS personnalisé.
 * Prix sur devis (bien au-dessus des packs fixes). Démontre qu'on fait des
 * sites du niveau de notre propre site.
 */
get_header();
$ag_envoye = isset( $_GET['envoye'] ) && '1' === $_GET['envoye'];
?>

<main id="ag-main-content">

	<!-- Hero -->
	<section class="ag-hero" style="min-height:60vh;">
		<div class="ag-hero__bg"><div class="ag-hero__orb ag-hero__orb--1"></div><div class="ag-hero__orb ag-hero__orb--2"></div></div>
		<div class="ag-hero__content">
			<span class="ag-tag ag-anim" data-anim="tag">Sur-mesure Haut de gamme</span>
			<h1 class="ag-hero__title">
				<span class="ag-line">Un site <em>unique</em>, conçu</span>
				<span class="ag-line">de A à Z, à ton image.</span>
			</h1>
			<p class="ag-hero__sub">
				Tu choisis le style, les couleurs, les sections, les fonctionnalités — on conçoit un site
				du <strong>niveau de celui que tu regardes en ce moment</strong>. Sur devis, sans limite.
			</p>
			<div class="ag-hero__buttons"><a href="#configurateur" class="ag-btn-gold">Composer mon projet →</a></div>
		</div>
	</section>

	<!-- Tarifs indicatifs -->
	<section class="ag-section ag-section--graphite">
		<div class="ag-container">
			<span class="ag-tag ag-anim" data-anim="tag">Sur devis</span>
			<h2 class="ag-section__title ag-anim" data-anim="title">Un autre niveau que les <em>packs fixes</em></h2>
			<p class="ag-section__desc ag-anim" data-anim="desc">Le sur-mesure démarre là où s'arrêtent les Sites Express (1490 €). Voici des ordres de prix indicatifs — le devis final dépend de ton projet.</p>
			<div class="ag-sm-grid">
				<div class="ag-sm-card ag-anim" data-anim="card"><span class="ag-sm-card__ic"></span><h3>Premium</h3><p class="ag-sm-price">dès 2 500 €</p><p>Vitrine haut de gamme, design 100% unique, animations soignées, SEO avancé.</p></div>
				<div class="ag-sm-card ag-anim" data-anim="card"><span class="ag-sm-card__ic"></span><h3>Avancé</h3><p class="ag-sm-price">dès 5 000 €</p><p>E-commerce, réservation, espace membre, intégrations &amp; automatisations.</p></div>
				<div class="ag-sm-card ag-anim" data-anim="card"><span class="ag-sm-card__ic"></span><h3>Plateforme</h3><p class="ag-sm-price">10 000 €+</p><p>Application web, fonctionnalités complexes, IA, sur-mesure complet de A à Z — <strong>système de prospection inclus</strong>.</p></div>
			</div>
			<p style="text-align:center;color:var(--color-text-soft);margin-top:18px;font-size:.92rem;">Paiement échelonné possible · devis gratuit · accompagnement de A à Z.</p>
			<p style="text-align:center;margin-top:8px;"><a href="<?php echo esc_url( home_url( '/systeme-prospection' ) ); ?>" style="color:var(--color-gold);font-weight:700;">Découvrir notre Système de prospection (en option ou à part) →</a></p>
		</div>
	</section>

	<!-- Configurateur -->
	<section class="ag-section ag-section--light" id="configurateur">
		<div class="ag-container ag-container--narrow">
			<span class="ag-tag ag-anim" data-anim="tag">Compose ton projet</span>
			<h2 class="ag-section__title ag-anim" data-anim="title">Dessine ton site <em>sur-mesure</em></h2>
			<p class="ag-section__desc ag-anim" data-anim="desc">Réponds en 1 minute : on te recontacte avec un devis personnalisé. C'est gratuit et sans engagement.</p>

			<?php if ( $ag_envoye ) : ?>
				<div class="ag-sm-success">Demande envoyée ! On étudie ton projet et on te recontacte très vite avec un devis personnalisé.</div>
			<?php else : ?>
			<form class="ag-sm-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="ag_sur_mesure_submit">
				<?php wp_nonce_field( 'ag_sur_mesure', '_n' ); ?>

				<fieldset><legend>1. Type de projet</legend>
					<div class="ag-sm-opts">
						<?php foreach ( array( 'Vitrine premium', 'E-commerce / boutique', 'Plateforme ou application web', 'Refonte haut de gamme' ) as $i => $o ) : ?>
							<label class="ag-sm-opt"><input type="radio" name="type" value="<?php echo esc_attr( $o ); ?>" <?php checked( 0, $i ); ?>> <span><?php echo esc_html( $o ); ?></span></label>
						<?php endforeach; ?>
					</div>
				</fieldset>

				<fieldset><legend>2. Style visuel</legend>
					<div class="ag-sm-opts">
						<?php foreach ( array( 'Sombre &amp; luxe (comme ce site)', 'Clair &amp; épuré', 'Coloré &amp; dynamique', 'À définir ensemble' ) as $i => $o ) : ?>
							<label class="ag-sm-opt"><input type="radio" name="style" value="<?php echo esc_attr( wp_strip_all_tags( $o ) ); ?>" <?php checked( 0, $i ); ?>> <span><?php echo $o; // phpcs:ignore ?></span></label>
						<?php endforeach; ?>
					</div>
					<input type="text" name="couleurs" placeholder="Couleurs souhaitées (ex : noir &amp; doré, bleu marine…)" class="ag-sm-input">
				</fieldset>

				<fieldset><legend>3. Fonctionnalités</legend>
					<div class="ag-sm-opts ag-sm-opts--checks">
						<?php foreach ( array( 'Réservation / prise de RDV', 'Paiement en ligne', 'Espace membre / connexion', 'Multilingue', 'Blog / actualités', 'Chatbot / IA', 'Animations avancées', 'Référencement (SEO) poussé' ) as $o ) : ?>
							<label class="ag-sm-opt"><input type="checkbox" name="features[]" value="<?php echo esc_attr( $o ); ?>"> <span><?php echo esc_html( $o ); ?></span></label>
						<?php endforeach; ?>
					</div>
				</fieldset>

				<div class="ag-sm-row">
					<label>4. Nombre de pages
						<select name="pages"><option>1 à 5</option><option>6 à 15</option><option>15+</option><option>À conseiller</option></select>
					</label>
					<label>5. Budget envisagé
						<select name="budget"><option>2 000 – 5 000 €</option><option>5 000 – 10 000 €</option><option>10 000 – 20 000 €</option><option>20 000 €+</option><option>À conseiller</option></select>
					</label>
					<label>6. Délai
						<select name="delai"><option>Urgent</option><option>1 à 2 mois</option><option>Flexible</option></select>
					</label>
				</div>

				<label class="ag-sm-block">Décris ton projet
					<textarea name="description" rows="4" placeholder="Ton activité, ce que le site doit faire, exemples de sites que tu aimes…"></textarea>
				</label>

				<div class="ag-sm-row">
					<label>Ton nom *<input type="text" name="name" required placeholder="Prénom Nom"></label>
					<label>Email *<input type="email" name="email" required placeholder="ton@email.com"></label>
					<label>Téléphone<input type="tel" name="phone" placeholder="06 …"></label>
				</div>
				<div class="ag-sm-row">
					<label>Entreprise<input type="text" name="business" placeholder="Nom de ton activité"></label>
					<label>Pays (droit applicable)
						<select name="pays"><?php foreach ( ( function_exists( 'ag_countries' ) ? ag_countries() : array( 'France' ) ) as $ag_c ) : ?><option value="<?php echo esc_attr( $ag_c ); ?>" <?php selected( 'France', $ag_c ); ?>><?php echo esc_html( $ag_c ); ?></option><?php endforeach; ?></select>
					</label>
				</div>

				<label class="ag-sm-check"><input type="checkbox" name="cgv" value="1" required> <span>J'ai lu et j'accepte le <a href="<?php echo esc_url( home_url( '/contrat-client' ) ); ?>" target="_blank" rel="noopener">contrat de prestation &amp; les CGV</a>. *</span></label>

				<button type="submit" class="ag-btn-gold ag-btn-gold--xl">Recevoir mon devis sur-mesure →</button>
			</form>
			<?php endif; ?>
		</div>
	</section>

</main>

<style>
.ag-sm-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-top:36px;}
.ag-sm-card{background:rgba(255,255,255,.03);border:1px solid rgba(212,180,92,.22);border-radius:18px;padding:26px 24px;display:flex;flex-direction:column;gap:6px;}
.ag-sm-card__ic{font-size:2rem;}
.ag-sm-card h3{color:#fff;font-family:var(--font-serif);font-size:1.3rem;margin:0;}
.ag-sm-price{color:#D4B45C;font-weight:800;font-size:1.25rem;margin:0;}
.ag-sm-card p{color:var(--color-text-soft);line-height:1.5;margin:0;}
.ag-sm-form{margin-top:30px;display:flex;flex-direction:column;gap:22px;text-align:left;}
.ag-sm-form fieldset{border:1px solid rgba(120,100,40,.25);border-radius:14px;padding:16px 18px;margin:0;}
.ag-sm-form legend{font-weight:800;color:#17150f;padding:0 8px;}
.ag-sm-opts{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;}
.ag-sm-opt{display:flex;align-items:center;gap:8px;background:#fff;border:1px solid rgba(120,100,40,.25);border-radius:10px;padding:10px 12px;cursor:pointer;color:#3d382e;font-size:.92rem;}
.ag-sm-opt input{flex:0 0 auto;accent-color:#b08524;}
.ag-sm-input{width:100%;margin-top:10px;padding:11px 14px;border:1px solid rgba(120,100,40,.3);border-radius:10px;}
.ag-sm-row{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;}
.ag-sm-row label,.ag-sm-block{display:flex;flex-direction:column;gap:6px;font-weight:600;color:#17150f;font-size:.9rem;}
.ag-sm-row select,.ag-sm-row input,.ag-sm-block textarea{padding:11px 14px;border:1px solid rgba(120,100,40,.3);border-radius:10px;font-size:.95rem;}
.ag-sm-block{margin-top:0;}
.ag-sm-check{display:flex;gap:10px;align-items:flex-start;color:#3d382e;font-size:.9rem;line-height:1.5;}
.ag-sm-check input{margin-top:3px;flex:0 0 auto;accent-color:#b08524;}
.ag-sm-check span{flex:1;}
.ag-sm-success{margin-top:24px;background:#e9f7ee;border:1px solid #1e7e34;color:#1e5a2a;border-radius:14px;padding:22px;text-align:center;font-weight:600;}
@media(max-width:760px){.ag-sm-grid,.ag-sm-row,.ag-sm-opts{grid-template-columns:1fr;}}
</style>

<?php get_footer(); ?>
