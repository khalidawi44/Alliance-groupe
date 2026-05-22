<?php
/**
 * Template Name: Espace Client
 *
 * Tableau de bord du client connecté : ses projets/briefs Sites Express,
 * sa maintenance, contact. Accès protégé dans inc/ag-espaces.php.
 */
get_header();

$u     = wp_get_current_user();
$email = $u->user_email;
$name  = $u->display_name ? $u->display_name : $u->user_login;
$briefs = function_exists( 'ag_client_briefs_for' ) ? ag_client_briefs_for( $email ) : array();
?>
<main id="ag-main-content" class="ag-esp">
	<section class="ag-section ag-section--graphite">
		<div class="ag-container">
			<div class="ag-esp-head">
				<div>
					<span class="ag-tag">Espace Client</span>
					<h1 class="ag-section__title">Bonjour, <em><?php echo esc_html( $name ); ?></em></h1>
				</div>
				<a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" class="ag-btn-outline ag-esp-logout">Déconnexion</a>
			</div>
			<p class="ag-section__desc">Retrouve ici tes projets de site et leur suivi. Pour toute demande, on reste joignable par email.</p>
			<?php $ag_tg_chan = function_exists( 'ag_tg_cfg' ) ? ag_tg_cfg( 'chan_link' ) : ''; if ( $ag_tg_chan ) : ?>
			<a href="<?php echo esc_url( $ag_tg_chan ); ?>" target="_blank" rel="noopener" class="ag-cli-tg">📣 <strong>Rejoins notre canal Telegram</strong> — offres exclusives &amp; nouveautés <span>→</span></a>
			<?php endif; ?>
		</div>
	</section>

	<section class="ag-section ag-section--onyx">
		<div class="ag-container">
			<h2 class="ag-section__title">Mes projets</h2>
			<?php if ( empty( $briefs ) ) : ?>
				<p class="ag-section__desc">Tu n'as pas encore de projet en cours. Choisis une offre et envoie ton brief — ton projet apparaîtra ici.</p>
				<a href="<?php echo esc_url( home_url( '/sites-express' ) ); ?>" class="ag-btn-gold">Voir les offres →</a>
			<?php else : ?>
				<div class="ag-esp-cards">
					<?php foreach ( $briefs as $b ) :
						$pack = $b['pack'] ?? '';
						$pack_label = $pack ? ucfirst( $pack ) : 'Site Express';
					?>
						<div class="ag-esp-card">
							<div class="ag-esp-card__top">
								<h3><?php echo esc_html( $b['business'] ?? 'Mon site' ); ?></h3>
								<span class="ag-esp-badge ag-esp-badge--prod">En production</span>
							</div>
							<ul class="ag-esp-card__meta">
								<li><strong>Offre :</strong> <?php echo esc_html( $pack_label ); ?></li>
								<?php if ( ! empty( $b['domain'] ) ) : ?><li><strong>Domaine :</strong> <?php echo esc_html( $b['domain'] ); ?></li><?php endif; ?>
								<?php if ( ! empty( $b['sector'] ) ) : ?><li><strong>Secteur :</strong> <?php echo esc_html( $b['sector'] ); ?></li><?php endif; ?>
								<li><strong>Brief envoyé le :</strong> <?php echo esc_html( $b['date'] ?? '' ); ?></li>
							</ul>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<section class="ag-section ag-section--graphite">
		<div class="ag-container ag-container--narrow">
			<h2 class="ag-section__title">Maintenance &amp; aide</h2>
			<p class="ag-section__desc">Garde ton site en ligne, sécurisé et à jour chaque mois, et contacte-nous quand tu veux.</p>
			<div class="ag-esp-actions">
				<a href="<?php echo esc_url( home_url( '/sites-express#maintenance' ) ); ?>" class="ag-btn-gold">Voir les forfaits maintenance →</a>
				<a href="mailto:contact@alliancegroupe-inc.com" class="ag-btn-outline">Nous écrire</a>
			</div>
		</div>
	</section>
</main>

<style>
.ag-esp-head{display:flex;justify-content:space-between;align-items:flex-start;gap:20px;flex-wrap:wrap;}
.ag-esp-logout{flex-shrink:0;}
.ag-cli-tg{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin:16px 0 0;padding:14px 20px;background:linear-gradient(135deg,rgba(90,176,255,.16),rgba(212,180,92,.06));border:1px solid rgba(90,176,255,.4);border-radius:14px;color:#fff;text-decoration:none;font-size:.95rem;transition:transform .2s;}
.ag-cli-tg:hover{transform:translateY(-2px);color:#fff;text-decoration:none;}
.ag-cli-tg strong{color:#9ad0ff;}
.ag-cli-tg span{margin-left:auto;color:#9ad0ff;font-weight:800;}
.ag-esp-cards{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:22px;margin-top:30px;}
.ag-esp-card{padding:26px 24px;background:rgba(255,255,255,.03);border:1px solid rgba(212,180,92,.18);border-radius:18px;}
.ag-esp-card__top{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:14px;}
.ag-esp-card__top h3{margin:0;font-family:var(--font-serif);color:#fff;font-size:1.25rem;}
.ag-esp-card__meta{list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:8px;}
.ag-esp-card__meta li{color:var(--color-text-soft);font-size:.92rem;}
.ag-esp-card__meta strong{color:#fff;font-weight:600;}
.ag-esp-badge{display:inline-block;padding:3px 12px;border-radius:20px;font-size:.78rem;font-weight:700;white-space:nowrap;}
.ag-esp-badge--prod{background:rgba(212,180,92,.18);color:#e8c766;}
.ag-esp-actions{display:flex;flex-wrap:wrap;gap:16px;margin-top:24px;}
</style>

<?php get_footer(); ?>
