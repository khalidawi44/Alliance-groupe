<?php
/**
 * Template Name: Maintenance & Sérénité
 *
 * Funnel récurrent : transformer un audit / une création en abonnement mensuel
 * (mises à jour, sauvegardes, monitoring, sécurité, support). C'est le revenu
 * STABLE qui met à l'aise financièrement (objectif MRR).
 *
 * @package Alliance_Groupe_Theme
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$ag_contact = home_url( '/contact' );
$ag_audit   = home_url( '/tester-mon-site' );

// Paliers sécurité (modifiables ici). Prix indicatifs HT / mois.
// Filon (docs/FILON.md) : 3 niveaux sécurité, l'attestation « mesures art. 32 »
// = livrable phare du niveau Conformité (angle de la preuve, jamais la peur).
$ag_plans = array(
	array(
		'name'  => 'Sérénité',
		'price' => '29',
		'tag'   => 'Entretenu &amp; surveillé',
		'feats' => array(
			'Mises à jour CMS, extensions &amp; thème',
			'Sauvegardes quotidiennes (restaurables)',
			'Monitoring de disponibilité (uptime 24/7)',
			'Scan de code malveillant mensuel',
			'Support par email',
		),
		'best'  => false,
	),
	array(
		'name'  => 'Protection',
		'price' => '59',
		'tag'   => 'On bloque avant que ça rentre',
		'feats' => array(
			'Tout Sérénité, +',
			'Pare-feu applicatif (WAF) + anti-bruteforce',
			'Double authentification (2FA) gérée',
			'Veille des vulnérabilités d\'extensions (alerte rapide)',
			'Rapport mensuel',
		),
		'best'  => false,
	),
	array(
		'name'  => 'Conformité',
		'price' => '99',
		'tag'   => 'Et vous pouvez le prouver',
		'feats' => array(
			'Tout Protection, +',
			'Maintien conformité RGPD &amp; cookies',
			'Registre léger des traitements',
			'Veille réglementaire selon votre secteur (santé, e-commerce…)',
			'<strong>Attestation annuelle « mesures art. 32 »</strong>',
		),
		'best'  => true,
	),
);
?>
<main class="ag-maint">

	<section class="ag-section">
		<div class="ag-container" style="text-align:center;max-width:820px;">
			<span class="ag-tag">Maintenance &amp; sécurité</span>
			<h1 class="ag-section__title">Votre site, <em>protégé et à jour</em>. Chaque mois.</h1>
			<p class="ag-section__desc" style="margin:18px auto 0;">Un site, ça vit : mises à jour, failles, pannes. Plutôt que de réagir dans l'urgence (et de payer cher), on s'en occupe en continu — pour que vous n'y pensiez plus. <strong>Sans engagement</strong>, résiliable à tout moment.</p>
			<div style="margin-top:28px;">
				<a href="<?php echo esc_url( $ag_contact ); ?>" class="ag-btn-gold">Être rappelé →</a>
				<a href="<?php echo esc_url( $ag_audit ); ?>" class="ag-btn-ghost" style="margin-left:8px;">Commencer par un audit</a>
			</div>
		</div>
	</section>

	<section class="ag-section ag-section--graphite">
		<div class="ag-container">
			<div class="ag-maint-grid">
				<?php foreach ( $ag_plans as $p ) : ?>
					<div class="ag-maint-card<?php echo $p['best'] ? ' is-best' : ''; ?>">
						<?php if ( $p['best'] ) : ?><span class="ag-maint-badge"><?php echo esc_html( $p['tag'] ); ?></span><?php endif; ?>
						<h3 class="ag-maint-name"><?php echo esc_html( $p['name'] ); ?></h3>
						<?php if ( ! $p['best'] ) : ?><span class="ag-maint-tag"><?php echo esc_html( $p['tag'] ); ?></span><?php endif; ?>
						<div class="ag-maint-price"><span class="n"><?php echo esc_html( $p['price'] ); ?></span> €<small> / mois</small></div>
						<ul class="ag-maint-feats">
							<?php foreach ( $p['feats'] as $f ) : ?>
								<li><?php echo wp_kses( $f, array( 'strong' => array(), 'em' => array() ) ); ?></li>
							<?php endforeach; ?>
						</ul>
						<a href="<?php echo esc_url( $ag_contact ); ?>" class="<?php echo $p['best'] ? 'ag-btn-gold' : 'ag-btn-ghost'; ?> ag-maint-cta">Choisir <?php echo esc_html( $p['name'] ); ?></a>
					</div>
				<?php endforeach; ?>
			</div>
			<p style="text-align:center;color:var(--color-text-secondary,#9aa);font-size:.9rem;margin-top:28px;">Prix indicatifs HT / mois. Sérénité et Protection sans engagement ; <strong>Conformité</strong> aussi en formule annuelle engagée (avec l'attestation « mesures art. 32 »). Devis selon votre site. Création récente par mes soins→ <strong>1er mois offert</strong>.</p>
		</div>
	</section>

	<section class="ag-section">
		<div class="ag-container" style="max-width:820px;text-align:center;">
			<span class="ag-tag">Pourquoi un abonnement</span>
			<h2 class="ag-section__title">Être tranquille, et <em>pouvoir le prouver</em></h2>
			<p class="ag-section__desc" style="margin:18px auto 0;">La vraie question n'est pas seulement d'éviter l'incident : c'est de pouvoir <strong>prouver vos mesures</strong>. Le RGPD (article 32) rend chaque professionnel responsable de la sécurité des données qu'il traite. La majorité des failles WordPress passent par les extensions — une veille et des mises à jour suivies réduisent le risque, et l'attestation annuelle documente ce que vous avez mis en place.</p>
			<div style="margin-top:26px;">
				<a href="<?php echo esc_url( $ag_contact ); ?>" class="ag-btn-gold">Parler de mon site →</a>
			</div>
		</div>
	</section>

</main>

<style>
.ag-maint-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px;max-width:1040px;margin:0 auto}
.ag-maint-card{position:relative;background:rgba(255,255,255,.03);border:1px solid rgba(212,180,92,.25);border-radius:16px;padding:34px 26px;display:flex;flex-direction:column}
.ag-maint-card.is-best{border-color:#D4B45C;box-shadow:0 12px 40px rgba(212,180,92,.18);transform:translateY(-6px)}
.ag-maint-badge{position:absolute;top:-12px;left:50%;transform:translateX(-50%);background:linear-gradient(135deg,#F37A1F,#D4B45C);color:#0a0a0f;font-weight:800;font-size:.72rem;letter-spacing:1px;padding:5px 14px;border-radius:100px;text-transform:uppercase}
.ag-maint-name{font-family:Georgia,serif;font-size:1.6rem;color:#fff;margin:0 0 4px}
.ag-maint-tag{color:#D4B45C;font-size:.82rem;letter-spacing:.5px}
.ag-maint-price{margin:16px 0 18px;color:#fff}
.ag-maint-price .n{font-family:Georgia,serif;font-size:3rem;color:#D4B45C;line-height:1}
.ag-maint-price small{color:#9aa;font-size:.95rem}
.ag-maint-feats{list-style:none;margin:0 0 24px;padding:0;flex:1}
.ag-maint-feats li{padding:9px 0 9px 26px;position:relative;color:rgba(255,255,255,.85);font-size:.95rem;border-bottom:1px solid rgba(255,255,255,.06)}
.ag-maint-feats li::before{content:'';position:absolute;left:0;color:#D4B45C;font-weight:700}
.ag-maint-cta{display:block;text-align:center}
.ag-btn-ghost{display:inline-block;padding:13px 24px;border-radius:100px;border:1px solid rgba(212,180,92,.5);color:#D4B45C;text-decoration:none;font-weight:700;transition:.2s}
.ag-btn-ghost:hover{background:rgba(212,180,92,.12)}
@media(max-width:820px){.ag-maint-grid{grid-template-columns:1fr;max-width:420px}.ag-maint-card.is-best{transform:none}}
</style>

<?php get_footer(); ?>
