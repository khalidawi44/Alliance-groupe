<?php
/**
 * Template Name: Consultation payante
 *
 * Offre d'appel/visio payant. Le client paie via PayPal, puis choisit son
 * créneau par simple retour de contact (aucun outil de RDV payant).
 */
get_header();

$contact_url = home_url( '/contact' );
$c_express   = get_option( 'ag_stripe_consult_express_url', 'STRIPE_PLACEHOLDER' );
$c_strat     = get_option( 'ag_stripe_consult_strategique_url', 'STRIPE_PLACEHOLDER' );
$btn_url = function ( $u ) use ( $contact_url ) {
	return ( 'STRIPE_PLACEHOLDER' === $u || '' === $u ) ? $contact_url . '?source=consultation' : $u;
};

$paid  = isset( $_GET['paid'] ) ? sanitize_key( $_GET['offre'] ?? '1' ) : '';
$labels = array( 'express' => 'Consultation Express (30 min)', 'strategique' => 'Consultation Stratégique (60 min)' );
$is_success = isset( $_GET['paid'] );
?>

<main id="ag-main-content">

	<?php if ( $is_success ) : ?>
	<!-- Confirmation après paiement -->
	<section class="ag-section ag-section--light" style="min-height:60vh;display:flex;align-items:center;">
		<div class="ag-container" style="text-align:center;max-width:680px;">
			<span class="ag-tag">Paiement confirmé </span>
			<h1 class="ag-section__title">Merci ! On cale le <em>créneau</em></h1>
			<p class="ag-section__desc">Ta <strong><?php echo esc_html( $labels[ $paid ] ?? 'consultation' ); ?></strong> est réservée. Écris-nous tes <strong>disponibilités</strong> (jour + heure) et le sujet à préparer — on te confirme le rendez-vous très vite.</p>
			<div class="ag-hero__buttons" style="justify-content:center;">
				<a href="<?php echo esc_url( $contact_url . '?source=consultation-payee' ); ?>" class="ag-btn-gold">Envoyer mes disponibilités →</a>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ag-btn-outline">Retour à l'accueil</a>
			</div>
		</div>
	</section>
	<?php else : ?>

	<!-- Hero -->
	<section class="ag-hero" style="min-height:48vh;">
		<div class="ag-hero__bg"><div class="ag-hero__orb ag-hero__orb--1"></div></div>
		<div class="ag-hero__content">
			<span class="ag-tag ag-anim" data-anim="tag">Consultation 1:1</span>
			<h1 class="ag-hero__title">
				<span class="ag-line">Un <em>expert</em> rien que</span>
				<span class="ag-line">pour ton projet.</span>
			</h1>
			<p class="ag-hero__sub">Un appel en visio, concret et sans jargon : on analyse ta situation et on repart avec un plan d'action clair. Tu réserves, tu paies, on cale le créneau ensemble.</p>
		</div>
	</section>

	<!-- Offres -->
	<section class="ag-section ag-section--light">
		<div class="ag-container">
			<span class="ag-tag ag-anim" data-anim="tag">Tarifs clairs</span>
			<h2 class="ag-section__title ag-anim" data-anim="title">Choisis ta <em>formule</em></h2>
			<p class="ag-section__desc ag-anim" data-anim="desc">Paiement sécurisé via PayPal. Après paiement, tu nous envoies tes disponibilités — pas de rendez-vous imposé.</p>

			<div class="ag-consult-grid">
				<div class="ag-consult-card ag-anim" data-anim="card">
					<h3>Consultation Express</h3>
					<div class="ag-consult-price"><strong>59 €</strong><small>30 minutes · visio</small></div>
					<ul>
						<li>Analyse rapide de ton besoin</li>
						<li>2-3 actions prioritaires</li>
						<li>Réponses à tes questions</li>
					</ul>
					<a href="<?php echo esc_url( $btn_url( $c_express ) ); ?>" class="ag-btn-outline" target="_blank" rel="noopener">Réserver — 59 € →</a>
				</div>
				<div class="ag-consult-card ag-consult-card--hero ag-anim" data-anim="card">
					<span class="ag-consult-badge">Recommandé</span>
					<h3>Consultation Stratégique</h3>
					<div class="ag-consult-price"><strong>119 €</strong><small>60 minutes · visio</small></div>
					<ul>
						<li>Audit complet de ta présence en ligne</li>
						<li>Plan d'action détaillé &amp; chiffré</li>
						<li>Compte-rendu écrit après l'appel</li>
					</ul>
					<a href="<?php echo esc_url( $btn_url( $c_strat ) ); ?>" class="ag-btn-gold" target="_blank" rel="noopener">Réserver — 119 € →</a>
				</div>
			</div>

			<p class="ag-consult-note">Tu hésites entre un site et une consultation ? Regarde d'abord nos <a href="<?php echo esc_url( home_url( '/sites-express' ) ); ?>">offres à prix fixe</a> ou le <a href="<?php echo esc_url( home_url( '/sur-mesure' ) ); ?>">sur-mesure</a>.</p>
		</div>
	</section>

	<?php endif; ?>

</main>

<style>
.ag-consult-grid{display:grid;grid-template-columns:1fr 1fr;gap:22px;margin-top:36px;max-width:820px;margin-left:auto;margin-right:auto;}
.ag-consult-card{position:relative;background:#fff;border:1px solid rgba(212,180,92,.4);border-radius:18px;padding:30px 26px;box-shadow:0 12px 34px rgba(120,100,40,.12);display:flex;flex-direction:column;gap:12px;}
.ag-consult-card--hero{border-color:rgba(212,180,92,.9);box-shadow:0 20px 50px rgba(120,100,40,.22);}
.ag-consult-badge{position:absolute;top:-12px;left:26px;background:linear-gradient(135deg,#D4B45C,#F37A1F);color:#10100a;font-weight:800;font-size:.74rem;padding:5px 12px;border-radius:100px;}
.ag-consult-card h3{color:#17150f;font-family:var(--font-serif);font-size:1.35rem;margin:0;}
.ag-consult-price{display:flex;align-items:baseline;gap:8px;}
.ag-consult-price strong{color:#17150f;font-size:2rem;font-weight:800;}
.ag-consult-price small{color:#7a7060;}
.ag-consult-card ul{list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:8px;color:#5b5446;line-height:1.4;}
.ag-consult-card .ag-btn-gold,.ag-consult-card .ag-btn-outline{margin-top:auto;}
.ag-consult-note{text-align:center;color:#5b5446;margin-top:26px;}
.ag-consult-note a{color:#b08524;font-weight:700;}
@media(max-width:760px){.ag-consult-grid{grid-template-columns:1fr;}}
</style>

<?php get_footer(); ?>
