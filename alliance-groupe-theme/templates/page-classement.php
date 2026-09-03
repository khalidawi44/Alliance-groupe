<?php
/**
 * Template Name: Classement
 *
 * Championnat des commerciaux : podium + classements du jour, du mois et général.
 * Données via ag_ambassadeur_leaderboard() (inc/ag-espaces.php).
 */
get_header();

$all   = function_exists( 'ag_ambassadeur_leaderboard' ) ? ag_ambassadeur_leaderboard( 'all' )   : array();
$month = function_exists( 'ag_ambassadeur_leaderboard' ) ? ag_ambassadeur_leaderboard( 'month' ) : array();
$day   = function_exists( 'ag_ambassadeur_leaderboard' ) ? ag_ambassadeur_leaderboard( 'day' )   : array();

$eur   = function ( $n ) { return number_format( (float) $n, 0, ',', ' ' ) . ' €'; };
$sname = function ( $r ) { return function_exists( 'ag_ambassadeur_short_name' ) ? ag_ambassadeur_short_name( $r['name'] ) : $r['name']; };
$medal = array( 1 => '', 2 => '', 3 => '' );

$podium = array_slice( $all, 0, 3 );

$render_table = function ( $rows, $empty ) use ( $eur, $sname, $medal ) {
	if ( empty( $rows ) ) { echo '<p class="ag-cls-empty">' . esc_html( $empty ) . '</p>'; return; }
	echo '<div class="ag-cls-table">';
	foreach ( array_slice( $rows, 0, 10 ) as $r ) {
		echo '<div class="ag-cls-row">';
		echo '<span class="ag-cls-row__rank">' . esc_html( $medal[ $r['rank'] ] ?? '#' . $r['rank'] ) . '</span>';
		echo '<span class="ag-cls-row__name">' . esc_html( $sname( $r ) ) . '</span>';
		echo '<span class="ag-cls-row__ca">' . esc_html( $eur( $r['ca'] ) ) . ' · ' . (int) $r['ventes'] . ' vente' . ( $r['ventes'] > 1 ? 's' : '' ) . '</span>';
		echo '</div>';
	}
	echo '</div>';
};
?>
<main id="ag-main-content" class="ag-cls">
	<section class="ag-section ag-section--graphite">
		<div class="ag-container">
			<span class="ag-tag ag-anim" data-anim="tag">Le championnat </span>
			<h1 class="ag-section__title ag-anim" data-anim="title">Classement des <em>commerciaux</em></h1>
			<p class="ag-section__desc ag-anim" data-anim="desc">Chaque vente compte. Monte sur le podium, décroche les primes du mois. Mis à jour en continu (ventes validées).</p>
			<div class="ag-cls-challenge"><strong>Challenge du mois</strong> — le n°1 du classement « Ce mois-ci » remporte une <strong>prime</strong>. Tout est remis à zéro chaque mois : tout le monde a sa chance.</div>

			<?php if ( ! empty( $podium ) ) : ?>
			<div class="ag-cls-podium">
				<?php
				$order = array( 1 => 'p1', 0 => 'p0', 2 => 'p2' ); // 2e, 1er, 3e
				foreach ( array( 1, 0, 2 ) as $i ) :
					if ( ! isset( $podium[ $i ] ) ) { echo '<div class="ag-cls-pod ag-cls-pod--empty"></div>'; continue; }
					$r = $podium[ $i ];
				?>
					<div class="ag-cls-pod ag-cls-pod--<?php echo (int) $r['rank']; ?>">
						<div class="ag-cls-pod__medal"><?php echo esc_html( $medal[ $r['rank'] ] ?? '' ); ?></div>
						<div class="ag-cls-pod__name"><?php echo esc_html( $sname( $r ) ); ?></div>
						<div class="ag-cls-pod__ca"><?php echo esc_html( $eur( $r['ca'] ) ); ?></div>
						<div class="ag-cls-pod__bar"></div>
					</div>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>

			<div class="ag-cls-grid">
				<div class="ag-cls-col">
					<h2>Aujourd'hui</h2>
					<?php $render_table( $day, 'Aucune vente validée aujourd\'hui. Sois le premier !' ); ?>
				</div>
				<div class="ag-cls-col">
					<h2>Ce mois-ci</h2>
					<?php $render_table( $month, 'Le championnat du mois est ouvert.' ); ?>
				</div>
				<div class="ag-cls-col">
					<h2>Général</h2>
					<?php $render_table( $all, 'Aucune vente pour l\'instant.' ); ?>
				</div>
			</div>

			<div class="ag-cls-cta">
				<?php if ( is_user_logged_in() && function_exists( 'ag_espace_member_kind' ) && ag_espace_member_kind() === 'ambassadeur' ) : ?>
					<a href="<?php echo esc_url( home_url( '/espace-ambassadeur' ) ); ?>" class="ag-btn-gold">Mon espace &amp; mon lien de vente →</a>
				<?php else : ?>
					<a href="<?php echo esc_url( home_url( '/ambassadeurs' ) ); ?>" class="ag-btn-gold">Entrer dans la course →</a>
				<?php endif; ?>
			</div>
		</div>
	</section>
</main>

<style>
.ag-cls-challenge{max-width:680px;margin:18px auto 0;padding:14px 20px;background:linear-gradient(160deg,rgba(212,180,92,.14),rgba(243,122,31,.06));border:1px solid rgba(212,180,92,.4);border-radius:14px;color:#fff;text-align:center;font-size:.95rem;}
.ag-cls-podium{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;align-items:end;max-width:680px;margin:46px auto 10px;}
.ag-cls-pod{text-align:center;padding:22px 14px;border-radius:16px 16px 0 0;background:rgba(255,255,255,.04);border:1px solid rgba(212,180,92,.2);border-bottom:none;}
.ag-cls-pod--1{background:linear-gradient(180deg,rgba(212,180,92,.25),rgba(243,122,31,.08));border-color:rgba(212,180,92,.6);transform:translateY(-18px);}
.ag-cls-pod__medal{font-size:2rem;}
.ag-cls-pod__name{color:#fff;font-weight:700;margin-top:6px;}
.ag-cls-pod__ca{color:#e8c766;font-weight:800;margin-top:4px;}
.ag-cls-pod__bar{height:8px;margin-top:14px;border-radius:6px;background:linear-gradient(90deg,#D4B45C,#F37A1F);}
.ag-cls-pod--1 .ag-cls-pod__bar{height:16px;}
.ag-cls-pod--3 .ag-cls-pod__bar{height:5px;opacity:.7;}
.ag-cls-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:22px;margin-top:40px;}
.ag-cls-col h2{font-family:var(--font-serif);color:#fff;font-size:1.25rem;margin:0 0 14px;}
.ag-cls-table{display:flex;flex-direction:column;gap:8px;}
.ag-cls-row{display:grid;grid-template-columns:42px 1fr;gap:8px;align-items:center;padding:12px 14px;background:rgba(255,255,255,.03);border:1px solid rgba(212,180,92,.14);border-radius:12px;}
.ag-cls-row__rank{font-size:1.1rem;font-weight:800;color:#e8c766;}
.ag-cls-row__name{color:#fff;font-weight:600;}
.ag-cls-row__ca{grid-column:2;color:var(--color-text-soft);font-size:.85rem;}
.ag-cls-empty{color:var(--color-text-muted);font-size:.92rem;}
.ag-cls-cta{text-align:center;margin-top:44px;}
@media(max-width:860px){.ag-cls-grid{grid-template-columns:1fr;}}
</style>

<?php get_footer(); ?>
