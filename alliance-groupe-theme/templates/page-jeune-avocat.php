<?php
/**
 * Template Name: Jeune avocat — 3 mois offerts
 *
 * Offre d'acquisition : 3 mois de Premium offerts aux avocats fraîchement
 * diplômés (via un code école / Jeune Barreau). Logique : inc/ag-jeune-avocat.php.
 */
get_header();
$state    = isset( $_GET['ag_ja'] ) ? sanitize_key( $_GET['ag_ja'] ) : '';
$post_url = esc_url( admin_url( 'admin-post.php' ) );
?>
<main id="ag-main-content">
	<section class="ag-hero" style="min-height:auto;padding:90px 0 30px;">
		<div class="ag-hero__bg"><div class="ag-hero__orb ag-hero__orb--1"></div></div>
		<div class="ag-hero__content" style="max-width:860px;">
			<span class="ag-tag">Vous venez de prêter serment ?</span>
			<h1 class="ag-hero__title"><span class="ag-line">Votre site d'avocat, <em>offert 3 mois</em></span></h1>
			<p class="ag-hero__sub">Un cadeau pour lancer votre cabinet : le Pack Premium du thème Avocat — recherche Judilibre, espace client sécurisé, RGPD &amp; déontologie. Sans engagement, sans carte bancaire.</p>
		</div>
	</section>

	<section class="ag-section ag-section--graphite" style="padding:10px 0 90px;">
		<div class="ag-container" style="max-width:1000px;display:grid;grid-template-columns:1fr 1fr;gap:40px;align-items:start;">

			<div>
				<h2 style="color:#D4B45C;font-size:1.2rem;text-transform:uppercase;letter-spacing:1px;margin:0 0 18px;">Ce que vous recevez</h2>
				<ul style="list-style:none;padding:0;margin:0;">
					<?php
					$items = array(
						'<strong>3 mois de Pack Premium</strong> offerts (puis libre à vous de continuer).',
						'Un site de cabinet <strong>prêt à l\'emploi</strong>, 100 % français.',
						'<strong>Recherche de jurisprudence Judilibre</strong> intégrée à votre back-office.',
						'<strong>Espace client sécurisé</strong> : vos clients déposent leurs pièces en privé.',
						'Conforme <strong>RGPD &amp; déontologie</strong> (RIN/CNB) dès le départ.',
					);
					foreach ( $items as $it ) :
					?>
						<li style="padding:11px 0 11px 30px;position:relative;border-bottom:1px solid rgba(255,255,255,.07);color:#e8e6e0;font-size:.97rem;line-height:1.5;">
							<span style="position:absolute;left:0;color:#28a745;font-weight:700;"></span>
							<?php echo wp_kses_post( $it ); ?>
						</li>
					<?php endforeach; ?>
				</ul>
				<p style="margin-top:18px;color:#9aa0a6;font-size:.85rem;">Offre réservée aux avocats récemment assermentés, via le code remis par votre école d'avocats ou votre Jeune Barreau.</p>
			</div>

			<div style="background:rgba(255,255,255,.03);border:1px solid rgba(212,180,92,.25);border-radius:16px;padding:30px 28px;">
				<?php if ( 'ok' === $state ) : ?>
					<div style="text-align:center;padding:18px 0;">
						<div style="font-size:2.6rem;"></div>
						<h2 style="color:#fff;margin:8px 0 10px;">C'est validé, bienvenue Maître !</h2>
						<p style="color:#cfcabd;line-height:1.6;">Votre clé Premium (3 mois) arrive par email avec la marche à suivre. Vérifiez vos spams.</p>
						<a href="<?php echo esc_url( home_url( '/wordpress-avocat' ) ); ?>" class="ag-btn-gold" style="margin-top:14px;">Découvrir le thème →</a>
					</div>
				<?php else : ?>
					<h2 style="color:#fff;margin:0 0 6px;font-size:1.35rem;">Activer mes 3 mois offerts</h2>
					<p style="color:#b0b0bc;font-size:.9rem;margin:0 0 16px;">Renseignez vos infos et le code de votre école / Jeune Barreau.</p>
					<?php if ( in_array( $state, array( 'champs', 'err', 'code' ), true ) ) : ?>
						<div style="background:rgba(220,53,69,.12);border:1px solid rgba(220,53,69,.4);color:#f1b0b7;padding:10px 14px;border-radius:8px;margin-bottom:14px;font-size:.9rem;">
							<?php echo 'code' === $state ? 'Code école invalide. Vérifiez auprès de votre école / Jeune Barreau.' : 'Merci de remplir les champs requis et de cocher le consentement.'; ?>
						</div>
					<?php endif; ?>
					<form method="post" action="<?php echo $post_url; ?>">
						<input type="hidden" name="action" value="ag_jeune_avocat">
						<?php wp_nonce_field( 'ag_jeune_avocat', 'ag_ja_nonce' ); ?>
						<input type="text" name="site_web_hp" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;" aria-hidden="true">
						<?php
						$f = 'width:100%;margin:6px 0 12px;padding:11px 12px;border-radius:8px;border:1px solid rgba(212,180,92,.3);background:#11111a;color:#fff;';
						?>
						<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
							<label style="color:#e8e6e0;font-size:.9rem;">Prénom<input type="text" name="prenom" required style="<?php echo $f; ?>"></label>
							<label style="color:#e8e6e0;font-size:.9rem;">Nom<input type="text" name="nom" required style="<?php echo $f; ?>"></label>
						</div>
						<label style="color:#e8e6e0;font-size:.9rem;">Email<input type="email" name="email" required style="<?php echo $f; ?>"></label>
						<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
							<label style="color:#e8e6e0;font-size:.9rem;">Barreau<input type="text" name="barreau" placeholder="Nantes…" style="<?php echo $f; ?>"></label>
							<label style="color:#e8e6e0;font-size:.9rem;">Date de serment<input type="month" name="serment" style="<?php echo $f; ?>"></label>
						</div>
						<label style="color:#e8e6e0;font-size:.9rem;">Code école / Jeune Barreau<input type="text" name="code" required placeholder="EX : UJANANTES" style="<?php echo $f; ?>text-transform:uppercase;"></label>
						<label style="display:flex;gap:10px;align-items:flex-start;color:#b0b0bc;font-size:.82rem;line-height:1.5;margin:6px 0 16px;">
							<input type="checkbox" name="rgpd" value="1" required style="margin-top:3px;">
							J'accepte d'être contacté par Alliance Groupe au sujet de cette offre (RGPD, désinscription à tout moment).
						</label>
						<button type="submit" class="ag-btn-gold" style="width:100%;justify-content:center;">Activer mes 3 mois offerts →</button>
					</form>
				<?php endif; ?>
			</div>

		</div>
	</section>
</main>
<?php
get_footer();
