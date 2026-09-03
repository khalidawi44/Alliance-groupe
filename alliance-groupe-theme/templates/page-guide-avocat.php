<?php
/**
 * Template Name: Guide gratuit — Avocat (capture lead)
 *
 * Page d'atterrissage de l'entonnoir freemium : le thème gratuit AG Starter
 * Avocat (wordpress.org) renvoie ici. On capte l'email (opt-in), on envoie le
 * guide + on crée un lead CRM. Logique dans inc/ag-lead-avocat.php.
 */

get_header();

$state   = isset( $_GET['ag_lead'] ) ? sanitize_key( $_GET['ag_lead'] ) : '';
$items   = function_exists( 'ag_lead_avocat_guide_items' ) ? ag_lead_avocat_guide_items() : array();
$post_url = esc_url( admin_url( 'admin-post.php' ) );
?>
<main id="ag-main-content">
	<section class="ag-hero" style="min-height:auto;padding:80px 0 40px;">
		<div class="ag-hero__bg"><div class="ag-hero__orb ag-hero__orb--1"></div></div>
		<div class="ag-hero__content" style="max-width:840px;">
			<span class="ag-tag">Cabinets d'avocats</span>
			<h1 class="ag-hero__title"><span class="ag-line">Le guide gratuit : <em>7 pages qui font venir des clients</em> à votre cabinet</span></h1>
			<p class="ag-hero__sub">Recevez la checklist (déontologie respectée) + le thème WordPress gratuit déjà prêt à l'emploi. 100&nbsp;% français, RGPD.</p>
		</div>
	</section>

	<section class="ag-section ag-section--graphite" style="padding:20px 0 90px;">
		<div class="ag-container" style="max-width:1000px;display:grid;grid-template-columns:1fr 1fr;gap:40px;align-items:start;">

			<div>
				<h2 style="color:#D4B45C;font-size:1.2rem;text-transform:uppercase;letter-spacing:1px;margin:0 0 18px;">Ce que vous recevez</h2>
				<ul style="list-style:none;padding:0;margin:0;">
					<?php foreach ( $items as $i => $it ) : ?>
						<li style="padding:12px 0 12px 34px;position:relative;border-bottom:1px solid rgba(255,255,255,.07);color:#e8e6e0;font-size:.97rem;line-height:1.5;">
							<span style="position:absolute;left:0;top:10px;width:24px;height:24px;border-radius:50%;background:rgba(212,180,92,.15);color:#D4B45C;font-weight:700;font-size:.8rem;display:flex;align-items:center;justify-content:center;"><?php echo (int) ( $i + 1 ); ?></span>
							<?php echo esc_html( $it ); ?>
						</li>
					<?php endforeach; ?>
				</ul>
				<p style="margin-top:20px;color:#9aa0a6;font-size:.85rem;">Outil d'aide à la mise en ligne. L'avocat reste seul responsable du contenu et du respect de sa déontologie (RIN/CNB).</p>
			</div>

			<div style="background:rgba(255,255,255,.03);border:1px solid rgba(212,180,92,.25);border-radius:16px;padding:30px 28px;">
				<?php if ( 'ok' === $state ) : ?>
					<div style="text-align:center;padding:20px 0;">
						<div style="font-size:2.6rem;"></div>
						<h2 style="color:#fff;margin:8px 0 10px;">C'est envoyé !</h2>
						<p style="color:#cfcabd;line-height:1.6;">Votre guide arrive par email (vérifiez les spams). En attendant, téléchargez le thème gratuit :</p>
						<a href="https://wordpress.org/themes/" target="_blank" rel="noopener" class="ag-btn-gold" style="margin-top:14px;">Thème gratuit sur WordPress.org →</a>
						<p style="margin-top:18px;"><a href="<?php echo esc_url( add_query_arg( array( 'utm_source' => 'guide-merci', 'utm_medium' => 'page', 'utm_campaign' => 'premium' ), home_url( '/wordpress-avocat' ) ) ); ?>" style="color:#D4B45C;">Voir le Pack Premium →</a></p>
					</div>
				<?php else : ?>
					<h2 style="color:#fff;margin:0 0 6px;font-size:1.35rem;">Recevoir le guide gratuit</h2>
					<p style="color:#b0b0bc;font-size:.9rem;margin:0 0 18px;">Aucune carte bancaire. Juste votre email pour vous l'envoyer.</p>
					<?php if ( 'champs' === $state || 'err' === $state ) : ?>
						<div style="background:rgba(220,53,69,.12);border:1px solid rgba(220,53,69,.4);color:#f1b0b7;padding:10px 14px;border-radius:8px;margin-bottom:14px;font-size:.9rem;">Merci de renseigner votre prénom, un email valide et de cocher le consentement.</div>
					<?php endif; ?>
					<form method="post" action="<?php echo $post_url; ?>" class="ag-lead-form">
						<input type="hidden" name="action" value="ag_lead_avocat">
						<?php wp_nonce_field( 'ag_lead_avocat', 'ag_lead_nonce' ); ?>
						<input type="text" name="site_web_hp" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;" aria-hidden="true">
						<label style="display:block;margin-bottom:12px;color:#e8e6e0;font-size:.9rem;">Prénom
							<input type="text" name="prenom" required style="width:100%;margin-top:6px;padding:11px 12px;border-radius:8px;border:1px solid rgba(212,180,92,.3);background:#11111a;color:#fff;">
						</label>
						<label style="display:block;margin-bottom:12px;color:#e8e6e0;font-size:.9rem;">Email
							<input type="email" name="email" required style="width:100%;margin-top:6px;padding:11px 12px;border-radius:8px;border:1px solid rgba(212,180,92,.3);background:#11111a;color:#fff;">
						</label>
						<label style="display:block;margin-bottom:14px;color:#e8e6e0;font-size:.9rem;">Ville (facultatif)
							<input type="text" name="ville" style="width:100%;margin-top:6px;padding:11px 12px;border-radius:8px;border:1px solid rgba(212,180,92,.3);background:#11111a;color:#fff;">
						</label>
						<label style="display:flex;gap:10px;align-items:flex-start;color:#b0b0bc;font-size:.82rem;line-height:1.5;margin-bottom:18px;">
							<input type="checkbox" name="rgpd" value="1" required style="margin-top:3px;">
							J'accepte de recevoir le guide et des informations d'Alliance Groupe par email. Données traitées en UE, désinscription à tout moment (RGPD).
						</label>
						<button type="submit" class="ag-btn-gold" style="width:100%;justify-content:center;">Envoyez-moi le guide →</button>
					</form>
				<?php endif; ?>
			</div>

		</div>
	</section>
</main>
<?php
get_footer();
