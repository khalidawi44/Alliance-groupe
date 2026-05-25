<?php
/**
 * Template Name: Deviens recruteur
 *
 * Programme recruteur : un ambassadeur recrute d'autres ambassadeurs et gagne
 * une prime fixe à leur 1re vente + un override à vie sur leurs ventes.
 * Affiche le lien de parrainage de l'ambassadeur connecté + le classement.
 */
get_header();

$u          = wp_get_current_user();
$is_amb     = is_user_logged_in() && function_exists( 'ag_ambassadeur_record' ) && ag_ambassadeur_record( $u->user_email );
$prime      = function_exists( 'ag_parrain_prime_amount' ) ? ag_parrain_prime_amount() : 25;
$ov         = function_exists( 'ag_override_rate' ) ? (int) round( ag_override_rate() * 100 ) : 10;
$link       = ( $is_amb && function_exists( 'ag_ambassadeur_parrain_link' ) ) ? ag_ambassadeur_parrain_link( $u->user_email ) : '';
$primes_tot = 0; $nb_actifs = 0;
if ( $is_amb && function_exists( 'ag_parrain_primes_for' ) ) { foreach ( ag_parrain_primes_for( $u->user_email ) as $p ) { $primes_tot += (float) ( $p['amount'] ?? 0 ); } }
$board = function_exists( 'ag_recruiter_leaderboard' ) ? ag_recruiter_leaderboard() : array();
$eur   = function ( $n ) { return number_format( (float) $n, 0, ',', ' ' ) . ' €'; };
$pitch = 'Rejoins l\'équipe Alliance Groupe : présente nos sites web, gagne 10% par vente. Inscris-toi 👇';
$share = rawurlencode( $pitch . ' ' . $link );
?>
<main id="ag-main-content">

	<section class="ag-hero" style="min-height:54vh;">
		<div class="ag-hero__bg"><div class="ag-hero__orb ag-hero__orb--1"></div><div class="ag-hero__orb ag-hero__orb--2"></div></div>
		<div class="ag-hero__content">
			<span class="ag-tag">Programme recruteur 🤝</span>
			<h1 class="ag-hero__title"><span class="ag-line">Recrute des ambassadeurs,</span><span class="ag-line">gagne <em>2 fois</em>.</span></h1>
			<p class="ag-hero__sub">Tu fais entrer quelqu'un dans l'équipe ? Tu touches <strong><?php echo esc_html( $eur( $prime ) ); ?> de prime</strong> dès sa 1ʳᵉ vente, <strong>+ <?php echo (int) $ov; ?>% sur toutes ses ventes, à vie</strong>. Sans plafond.</p>
			<?php if ( $is_amb ) : ?>
				<div class="ag-hero__buttons"><a href="#monlien" class="ag-btn-gold">Partager mon lien →</a></div>
			<?php else : ?>
				<div class="ag-hero__buttons"><a href="<?php echo esc_url( home_url( '/ambassadeurs' ) ); ?>" class="ag-btn-gold">Devenir ambassadeur d'abord →</a></div>
			<?php endif; ?>
		</div>
	</section>

	<!-- Comment ça marche -->
	<section class="ag-section ag-section--graphite">
		<div class="ag-container">
			<span class="ag-tag">Comment ça marche</span>
			<h2 class="ag-section__title">3 étapes, <em>2 revenus</em></h2>
			<div class="ag-sm-grid">
				<div class="ag-sm-card"><span class="ag-sm-card__ic">1️⃣</span><h3>Tu partages ton lien</h3><p>Ton lien de parrainage personnel. Toute personne qui s'inscrit via ce lien devient ton <strong>filleul</strong>.</p></div>
				<div class="ag-sm-card"><span class="ag-sm-card__ic">2️⃣</span><h3>Il fait sa 1ʳᵉ vente</h3><p>Tu gagnes une <strong>prime de <?php echo esc_html( $eur( $prime ) ); ?></strong>, créditée automatiquement.</p></div>
				<div class="ag-sm-card"><span class="ag-sm-card__ic">3️⃣</span><h3>Il continue à vendre</h3><p>Tu touches <strong><?php echo (int) $ov; ?>%</strong> de sa commission sur <strong>chaque</strong> vente, <strong>à vie</strong>.</p></div>
			</div>
		</div>
	</section>

	<?php if ( $is_amb ) : ?>
	<!-- Mon lien -->
	<section class="ag-section ag-section--onyx" id="monlien">
		<div class="ag-container">
			<h2 class="ag-section__title">Mon lien de recruteur 🔗</h2>
			<p class="ag-section__desc">Partage-le partout. Dès qu'un ambassadeur s'inscrit via ce lien, il est rattaché à toi.</p>
			<div class="ag-share">
				<input id="ag-reclink" type="text" readonly value="<?php echo esc_attr( $link ); ?>" onclick="this.select();">
				<button type="button" class="ag-btn-gold" id="ag-reccopy" onclick="navigator.clipboard.writeText(document.getElementById('ag-reclink').value).then(function(){var b=document.getElementById('ag-reccopy');b.textContent='✓ Copié';setTimeout(function(){b.textContent='Copier le lien';},1500);});">Copier le lien</button>
			</div>
			<div class="ag-share-btns">
				<a class="ag-sbtn ag-sbtn--wa" href="https://wa.me/?text=<?php echo $share; ?>" target="_blank" rel="noopener">📲 WhatsApp</a>
				<a class="ag-sbtn ag-sbtn--tg" href="https://t.me/share/url?url=<?php echo rawurlencode( $link ); ?>&text=<?php echo rawurlencode( $pitch ); ?>" target="_blank" rel="noopener">✈ Telegram</a>
				<a class="ag-sbtn ag-sbtn--sms" href="sms:?&body=<?php echo $share; ?>">💬 SMS</a>
				<a class="ag-sbtn ag-sbtn--mail" href="mailto:?subject=<?php echo rawurlencode( 'Rejoins l\'équipe Alliance Groupe' ); ?>&body=<?php echo $share; ?>">✉ Email</a>
			</div>
			<?php if ( $primes_tot > 0 ) : ?>
				<p class="ag-section__desc" style="margin-top:18px;">🎁 Tu as déjà gagné <strong style="color:var(--color-gold);"><?php echo esc_html( $eur( $primes_tot ) ); ?></strong> de primes de parrainage. Continue !</p>
			<?php endif; ?>
		</div>
	</section>
	<?php endif; ?>

	<!-- Classement des recruteurs -->
	<section class="ag-section ag-section--graphite">
		<div class="ag-container">
			<span class="ag-tag">Hall of fame</span>
			<h2 class="ag-section__title">🏆 Top recruteurs</h2>
			<?php if ( empty( $board ) ) : ?>
				<p class="ag-section__desc">Personne n'a encore recruté un ambassadeur actif. <strong>Sois le premier !</strong></p>
			<?php else : ?>
			<div style="max-width:680px;margin:24px auto 0;">
				<table style="width:100%;border-collapse:collapse;color:#fff;">
					<thead><tr style="text-align:left;color:var(--color-gold);"><th style="padding:8px;">#</th><th style="padding:8px;">Recruteur</th><th style="padding:8px;">Filleuls actifs</th><th style="padding:8px;">Primes</th></tr></thead>
					<tbody>
					<?php foreach ( array_slice( $board, 0, 20 ) as $row ) : $medal = array( 1 => '🥇', 2 => '🥈', 3 => '🥉' ); ?>
						<tr style="border-top:1px solid rgba(212,180,92,.2);">
							<td style="padding:8px;font-weight:800;"><?php echo isset( $medal[ $row['rank'] ] ) ? $medal[ $row['rank'] ] : (int) $row['rank']; ?></td>
							<td style="padding:8px;"><?php echo esc_html( $row['name'] ); ?></td>
							<td style="padding:8px;"><strong><?php echo (int) $row['actifs']; ?></strong> <span style="color:var(--color-text-soft);">/ <?php echo (int) $row['filleuls']; ?></span></td>
							<td style="padding:8px;color:var(--color-gold);font-weight:700;"><?php echo esc_html( $eur( $row['primes'] ) ); ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<?php endif; ?>
			<?php if ( ! $is_amb ) : ?>
				<div style="text-align:center;margin-top:28px;"><a href="<?php echo esc_url( home_url( '/ambassadeurs' ) ); ?>" class="ag-btn-gold">Je deviens ambassadeur &amp; je recrute →</a></div>
			<?php endif; ?>
		</div>
	</section>

</main>
<?php get_footer(); ?>
