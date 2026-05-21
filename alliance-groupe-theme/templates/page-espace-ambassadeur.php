<?php
/**
 * Template Name: Espace Commercial
 *
 * Tableau de bord de l'ambassadeur : statut, commissions, partage de son lien,
 * classement, déclaration et historique des ventes. La création de contenu
 * (vidéos, visuels, médiathèque) est sur la page /studio.
 * Accès protégé dans inc/ag-espaces.php.
 */
get_header();

$u      = wp_get_current_user();
$email  = $u->user_email;
$name   = $u->display_name ? $u->display_name : $u->user_login;
$rec    = function_exists( 'ag_ambassadeur_record' ) ? ag_ambassadeur_record( $email ) : null;
$actif  = $rec && ( ( $rec['status'] ?? '' ) === 'actif' );
$ventes = function_exists( 'ag_ambassadeur_ventes_for' ) ? ag_ambassadeur_ventes_for( $email ) : array();

$nb = 0; $ca = 0; $due = 0; $paid = 0;
foreach ( $ventes as $v ) {
	$st = $v['statut'] ?? '';
	if ( in_array( $st, array( 'validee', 'payee' ), true ) ) { $nb++; $ca += (float) $v['montant']; }
	if ( 'validee' === $st ) $due  += (float) $v['commission'];
	if ( 'payee' === $st )   $paid += (float) $v['commission'];
}
$eur = function ( $n ) { return number_format( (float) $n, 2, ',', ' ' ) . ' €'; };
$labels = array( 'declaree' => 'En attente', 'validee' => 'Validée', 'payee' => 'Payée' );
$ag_sale_link = function_exists( 'ag_ambassadeur_sale_link' ) ? ag_ambassadeur_sale_link( $email ) : home_url( '/sites-express' );
?>
<main id="ag-main-content" class="ag-esp">
	<section class="ag-section ag-section--graphite">
		<div class="ag-container">
			<div class="ag-esp-head">
				<div>
					<span class="ag-tag">Espace Commercial 🤝</span>
					<h1 class="ag-section__title">Bonjour, <em><?php echo esc_html( $name ); ?></em></h1>
				</div>
				<a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" class="ag-btn-outline ag-esp-logout">Déconnexion</a>
			</div>

			<?php if ( $actif ) : ?>
				<p class="ag-section__desc">Ton compte est <strong style="color:#4bbf77;">actif</strong>. Déclare tes ventes et suis tes commissions (10 %).</p>
			<?php else : ?>
				<div class="ag-esp-banner">⏳ Ton compte est <strong>en attente de validation</strong> (vérification d'identité + contrat). Tu pourras déclarer des ventes dès qu'il sera validé — on te prévient par email.</div>
			<?php endif; ?>

			<div class="ag-esp-stats">
				<div class="ag-esp-stat"><span class="ag-esp-stat__val"><?php echo (int) $nb; ?></span><span class="ag-esp-stat__lbl">Ventes validées</span></div>
				<div class="ag-esp-stat"><span class="ag-esp-stat__val"><?php echo esc_html( $eur( $ca ) ); ?></span><span class="ag-esp-stat__lbl">CA généré</span></div>
				<div class="ag-esp-stat ag-esp-stat--gold"><span class="ag-esp-stat__val"><?php echo esc_html( $eur( $due ) ); ?></span><span class="ag-esp-stat__lbl">Commission à recevoir</span></div>
				<div class="ag-esp-stat"><span class="ag-esp-stat__val"><?php echo esc_html( $eur( $paid ) ); ?></span><span class="ag-esp-stat__lbl">Déjà payé</span></div>
			</div>
		</div>
	</section>

	<section class="ag-section ag-section--onyx">
		<div class="ag-container">
			<div class="ag-studio-cta">
				<div>
					<h2 class="ag-section__title" style="margin:0 0 8px;">🎬 Studio créatif</h2>
					<p class="ag-section__desc" style="margin:0;">Plein de visuels déjà prêts, ton générateur de vidéos &amp; d'images — tout au même endroit, avec ton lien intégré. Tu crées et tu partages, sans quitter le site.</p>
				</div>
				<a href="<?php echo esc_url( home_url( '/studio' ) ); ?>" class="ag-btn-gold ag-studio-cta__btn">Ouvrir le Studio →</a>
			</div>
		</div>
	</section>

	<?php
	$ag_pitch = 'Ton site pro à prix fixe, livré en quelques jours — dès 490 €, sans rendez-vous. 👇';
	$ag_full  = $ag_pitch . ' ' . $ag_sale_link;
	$ag_u = rawurlencode( $ag_sale_link );
	$ag_t = rawurlencode( $ag_pitch );
	$ag_f = rawurlencode( $ag_full );
	$ag_wa   = 'https://wa.me/?text=' . $ag_f;
	$ag_fb   = 'https://www.facebook.com/sharer/sharer.php?u=' . $ag_u;
	$ag_x    = 'https://twitter.com/intent/tweet?text=' . $ag_t . '&url=' . $ag_u;
	$ag_tg   = 'https://t.me/share/url?url=' . $ag_u . '&text=' . $ag_t;
	$ag_sms  = 'sms:?&body=' . $ag_f;
	$ag_mail = 'mailto:?subject=' . rawurlencode( 'Une offre qui peut t\'intéresser' ) . '&body=' . $ag_f;
	?>
	<section class="ag-section ag-section--graphite">
		<div class="ag-container">
			<h2 class="ag-section__title">Partager mon lien 🚀</h2>
			<p class="ag-section__desc">Ton <strong>code de vente est déjà intégré</strong>. Dès qu'un client passe par ton lien et envoie son brief, la vente se crédite automatiquement — sans rien déclarer.</p>

			<div class="ag-share">
				<input id="ag-sharelink" type="text" readonly value="<?php echo esc_attr( $ag_sale_link ); ?>" onclick="this.select();">
				<button type="button" class="ag-btn-gold" id="ag-copybtn" onclick="navigator.clipboard.writeText(document.getElementById('ag-sharelink').value).then(function(){var b=document.getElementById('ag-copybtn');b.textContent='✓ Lien copié';setTimeout(function(){b.textContent='Copier le lien';},1500);});">Copier le lien</button>
			</div>

			<div class="ag-share-btns">
				<a class="ag-sbtn ag-sbtn--wa"   href="<?php echo esc_url( $ag_wa ); ?>"   target="_blank" rel="noopener">📲 WhatsApp</a>
				<a class="ag-sbtn ag-sbtn--fb"   href="<?php echo esc_url( $ag_fb ); ?>"   target="_blank" rel="noopener">f  Facebook</a>
				<a class="ag-sbtn ag-sbtn--x"    href="<?php echo esc_url( $ag_x ); ?>"    target="_blank" rel="noopener">𝕏  Twitter</a>
				<a class="ag-sbtn ag-sbtn--tg"   href="<?php echo esc_url( $ag_tg ); ?>"   target="_blank" rel="noopener">✈ Telegram</a>
				<a class="ag-sbtn ag-sbtn--sms"  href="<?php echo esc_url( $ag_sms ); ?>">💬 SMS</a>
				<a class="ag-sbtn ag-sbtn--mail" href="<?php echo esc_url( $ag_mail ); ?>">✉ Email</a>
				<button type="button" class="ag-sbtn ag-sbtn--native" id="ag-native">⤴ Partager…</button>
			</div>

			<div class="ag-share-msg">
				<div class="ag-share-msg__head">
					<strong>Message prêt à coller (TikTok, Insta, Snap, bio…)</strong>
					<button type="button" class="ag-btn-outline" id="ag-copymsg">Copier le message</button>
				</div>
				<textarea id="ag-msgtext" readonly rows="3"><?php echo esc_textarea( $ag_full ); ?></textarea>
			</div>
		</div>
	</section>
	<script>
	(function(){
		var link = <?php echo wp_json_encode( $ag_sale_link ); ?>;
		var pitch = <?php echo wp_json_encode( $ag_pitch ); ?>;
		var nb = document.getElementById('ag-native');
		if (nb) { if (navigator.share) { nb.addEventListener('click', function(){ navigator.share({title:'Alliance Groupe', text:pitch, url:link}).catch(function(){}); }); } else { nb.style.display='none'; } }
		var cm = document.getElementById('ag-copymsg');
		if (cm) cm.addEventListener('click', function(){ navigator.clipboard.writeText(document.getElementById('ag-msgtext').value).then(function(){ cm.textContent='✓ Copié'; setTimeout(function(){ cm.textContent='Copier le message'; },1500); }); });
	})();
	</script>

	<?php
	$ag_ref     = function_exists( 'ag_ambassadeur_ref' ) ? ag_ambassadeur_ref( $email ) : '';
	$ag_recruit = function_exists( 'ag_ambassadeur_recruit_link' ) ? ag_ambassadeur_recruit_link( $email ) : home_url( '/ambassadeurs' );
	$ag_team    = function_exists( 'ag_ambassadeur_override_for' ) ? ag_ambassadeur_override_for( $ag_ref ) : array( 'team' => 0, 'generated' => 0, 'paid' => 0 );
	?>
	<section class="ag-section ag-section--graphite">
		<div class="ag-container">
			<h2 class="ag-section__title">Recrute ton équipe 🌐</h2>
			<p class="ag-section__desc">Recrute d'autres commerciaux avec ce lien. Quand <strong>ils vendent</strong>, tu touches un bonus de parrainage sur leurs ventes (en plus de tes 10 %). Plus ton équipe vend, plus tu gagnes.</p>
			<div class="ag-esp-stats" style="grid-template-columns:repeat(2,1fr);max-width:560px;">
				<div class="ag-esp-stat"><span class="ag-esp-stat__val"><?php echo (int) $ag_team['team']; ?></span><span class="ag-esp-stat__lbl">Filleuls recrutés</span></div>
				<div class="ag-esp-stat ag-esp-stat--gold"><span class="ag-esp-stat__val"><?php echo esc_html( $eur( $ag_team['generated'] ) ); ?></span><span class="ag-esp-stat__lbl">Bonus parrainage généré</span></div>
			</div>
			<div class="ag-share" style="margin-top:22px;">
				<input id="ag-recruitlink" type="text" readonly value="<?php echo esc_attr( $ag_recruit ); ?>" onclick="this.select();">
				<button type="button" class="ag-btn-gold" id="ag-recruitcopy" onclick="navigator.clipboard.writeText(document.getElementById('ag-recruitlink').value).then(function(){var b=document.getElementById('ag-recruitcopy');b.textContent='✓ Copié';setTimeout(function(){b.textContent='Copier le lien de recrutement';},1500);});">Copier le lien de recrutement</button>
			</div>
			<p class="ag-share__note">Tu es payé sur les <strong>ventes</strong> de ton équipe, jamais pour le simple recrutement (c'est la règle).</p>
		</div>
	</section>

	<?php
	$lb    = function_exists( 'ag_ambassadeur_leaderboard' ) ? ag_ambassadeur_leaderboard() : array();
	$tiers = function_exists( 'ag_ambassadeur_tiers' ) ? ag_ambassadeur_tiers() : array();
	$cur = $tiers ? $tiers[0] : null; $next = null;
	foreach ( $tiers as $i => $t ) { if ( $ca >= $t['min_ca'] ) { $cur = $t; $next = $tiers[ $i + 1 ] ?? null; } }
	$myrank = 0;
	foreach ( $lb as $row ) { if ( strtolower( $row['email'] ) === strtolower( $email ) ) { $myrank = $row['rank']; break; } }
	$progress = 100;
	if ( $next && $cur ) { $span = max( 1, $next['min_ca'] - $cur['min_ca'] ); $progress = min( 100, max( 0, round( ( $ca - $cur['min_ca'] ) / $span * 100 ) ) ); }
	$medals = array( 1 => '🥇', 2 => '🥈', 3 => '🥉' );
	?>
	<section class="ag-section ag-section--onyx">
		<div class="ag-container">
			<h2 class="ag-section__title">Classement &amp; récompenses 🏆</h2>
			<p class="ag-section__desc">Plus tu vends, plus tu montes. Chaque mois, le top des commerciaux décroche des primes. <a href="<?php echo esc_url( home_url( '/classement' ) ); ?>" style="color:var(--color-gold);">Voir le championnat →</a></p>
			<div class="ag-lb-grid">
				<div class="ag-lb-tier">
					<div class="ag-lb-tier__badge"><?php echo esc_html( $cur ? $cur['emoji'] . ' ' . $cur['label'] : '—' ); ?></div>
					<?php if ( $myrank ) : ?><div class="ag-lb-rank"><?php echo esc_html( ( $medals[ $myrank ] ?? '#' . $myrank ) ); ?> au classement</div><?php endif; ?>
					<?php if ( $next ) : ?>
						<div class="ag-lb-progress"><span style="width:<?php echo (int) $progress; ?>%"></span></div>
						<p class="ag-lb-next">Plus que <strong><?php echo esc_html( $eur( max( 0, $next['min_ca'] - $ca ) ) ); ?></strong> de CA pour passer <?php echo esc_html( $next['emoji'] . ' ' . $next['label'] ); ?></p>
					<?php else : ?>
						<p class="ag-lb-next">Niveau maximum atteint 💎 Bravo !</p>
					<?php endif; ?>
					<p class="ag-lb-reward">🎁 <?php echo esc_html( $cur ? $cur['reward'] : '' ); ?></p>
				</div>
				<div class="ag-lb-board">
					<?php if ( empty( $lb ) ) : ?>
						<p class="ag-section__desc" style="margin:0;">Sois le premier à apparaître au classement 🚀</p>
					<?php else : $shown = 0; foreach ( $lb as $row ) :
						$me = strtolower( $row['email'] ) === strtolower( $email );
						if ( $shown >= 5 && ! $me ) continue;
						$shown++; ?>
						<div class="ag-lb-row<?php echo $me ? ' is-me' : ''; ?>">
							<span class="ag-lb-row__rank"><?php echo esc_html( $medals[ $row['rank'] ] ?? '#' . $row['rank'] ); ?></span>
							<span class="ag-lb-row__name"><?php echo esc_html( $me ? 'Toi' : ag_ambassadeur_short_name( $row['name'] ) ); ?></span>
							<span class="ag-lb-row__ca"><?php echo esc_html( $eur( $row['ca'] ) ); ?></span>
						</div>
					<?php endforeach; endif; ?>
				</div>
			</div>
		</div>
	</section>

	<?php if ( $actif ) : ?>
	<section class="ag-section ag-section--graphite">
		<div class="ag-container ag-container--narrow">
			<h2 class="ag-section__title">Déclarer une vente</h2>
			<p class="ag-section__desc">Vente faite en direct (sans ton lien) ? Déclare-la ici. Une fois encaissée et validée, ta commission de 10 % apparaît dans « à recevoir ».</p>
			<form class="ag-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="ag_ambassadeur_vente">
				<input type="hidden" name="email" value="<?php echo esc_attr( $email ); ?>">
				<?php wp_nonce_field( 'ag_amb_vente', 'ag_vente_nonce' ); ?>
				<div class="ag-form__row">
					<div class="ag-form__group"><label>Client *</label><input type="text" name="client" required placeholder="Nom du client / entreprise"></div>
					<div class="ag-form__group"><label>Activité</label><input type="text" name="activite" placeholder="restaurant, artisan…"></div>
				</div>
				<div class="ag-form__group"><label>Montant de la vente (€) *</label><input type="text" name="montant" required placeholder="ex : 890"></div>
				<button type="submit" class="ag-btn-gold">Déclarer la vente →</button>
			</form>
		</div>
	</section>
	<?php endif; ?>

	<section class="ag-section ag-section--onyx">
		<div class="ag-container">
			<h2 class="ag-section__title">Mes ventes</h2>
			<?php if ( empty( $ventes ) ) : ?>
				<p class="ag-section__desc">Aucune vente pour l'instant. Partage ton lien ou crée une vidéo dans le Studio 🚀</p>
			<?php else : ?>
				<div class="ag-esp-table-wrap">
					<table class="ag-esp-table">
						<thead><tr><th>Date</th><th>Client</th><th>Montant</th><th>Commission</th><th>Statut</th></tr></thead>
						<tbody>
						<?php foreach ( $ventes as $v ) : $st = $v['statut'] ?? ''; ?>
							<tr>
								<td><?php echo esc_html( $v['date'] ?? '' ); ?></td>
								<td><?php echo esc_html( $v['client'] ?? '' ); ?></td>
								<td><?php echo esc_html( $eur( $v['montant'] ?? 0 ) ); ?></td>
								<td><?php echo esc_html( $eur( $v['commission'] ?? 0 ) ); ?></td>
								<td><span class="ag-esp-badge ag-esp-badge--<?php echo esc_attr( $st ); ?>"><?php echo esc_html( $labels[ $st ] ?? $st ); ?></span></td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
		</div>
	</section>
</main>

<style>
.ag-esp-head{display:flex;justify-content:space-between;align-items:flex-start;gap:20px;flex-wrap:wrap;}
.ag-esp-logout{flex-shrink:0;}
.ag-esp-banner{background:rgba(212,180,92,.12);border:1px solid rgba(212,180,92,.4);border-radius:12px;padding:16px 20px;color:#fff;margin:6px 0 0;}
.ag-esp-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;margin-top:34px;}
.ag-esp-stat{padding:24px 20px;background:rgba(255,255,255,.03);border:1px solid rgba(212,180,92,.16);border-radius:16px;text-align:center;}
.ag-esp-stat--gold{border-color:rgba(212,180,92,.5);background:linear-gradient(160deg,rgba(212,180,92,.12),rgba(243,122,31,.05));}
.ag-esp-stat__val{display:block;font-family:var(--font-serif);font-size:1.9rem;font-weight:800;background:linear-gradient(135deg,#D4B45C,#F37A1F);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;color:transparent;}
.ag-esp-stat__lbl{display:block;margin-top:6px;color:var(--color-text-soft);font-size:.85rem;}
.ag-studio-cta{display:flex;justify-content:space-between;align-items:center;gap:22px;flex-wrap:wrap;padding:26px 28px;background:linear-gradient(160deg,rgba(212,180,92,.14),rgba(243,122,31,.06));border:1px solid rgba(212,180,92,.4);border-radius:18px;}
.ag-studio-cta__btn{flex-shrink:0;}
.ag-esp .ag-form__group{position:relative;margin-bottom:16px;}
.ag-esp .ag-form__group label{position:static;top:auto;left:auto;display:block;font-size:.88rem;font-weight:600;color:var(--color-text-soft);margin-bottom:8px;text-transform:none;letter-spacing:normal;pointer-events:auto;}
.ag-esp .ag-form__group input{padding:14px 18px;}
.ag-esp-table-wrap{margin-top:28px;overflow-x:auto;}
.ag-esp-table{width:100%;border-collapse:collapse;}
.ag-esp-table th,.ag-esp-table td{padding:14px 16px;text-align:left;border-bottom:1px solid rgba(212,180,92,.14);color:var(--color-text-soft);}
.ag-esp-table th{color:#fff;font-size:.85rem;text-transform:uppercase;letter-spacing:1px;}
.ag-esp-badge{display:inline-block;padding:3px 12px;border-radius:20px;font-size:.78rem;font-weight:700;}
.ag-esp-badge--declaree{background:rgba(255,255,255,.1);color:#cfc7b8;}
.ag-esp-badge--validee{background:rgba(34,113,177,.2);color:#79b6e6;}
.ag-esp-badge--payee{background:rgba(0,132,61,.2);color:#4bbf77;}
.ag-lb-grid{display:grid;grid-template-columns:1fr 1.2fr;gap:22px;margin-top:30px;align-items:start;}
.ag-lb-tier{padding:26px 24px;background:linear-gradient(160deg,rgba(212,180,92,.14),rgba(243,122,31,.06));border:1px solid rgba(212,180,92,.4);border-radius:18px;}
.ag-lb-tier__badge{font-family:var(--font-serif);font-size:1.7rem;font-weight:800;color:#fff;}
.ag-lb-rank{margin-top:6px;color:#e8c766;font-weight:700;}
.ag-lb-progress{height:10px;border-radius:10px;background:rgba(255,255,255,.1);overflow:hidden;margin:16px 0 10px;}
.ag-lb-progress span{display:block;height:100%;background:linear-gradient(90deg,#D4B45C,#F37A1F);}
.ag-lb-next{color:var(--color-text-soft);font-size:.9rem;margin:0 0 12px;}
.ag-lb-reward{color:#fff;font-size:.92rem;margin:0;}
.ag-lb-board{display:flex;flex-direction:column;gap:8px;}
.ag-lb-row{display:grid;grid-template-columns:48px 1fr auto;align-items:center;gap:12px;padding:14px 18px;background:rgba(255,255,255,.03);border:1px solid rgba(212,180,92,.14);border-radius:12px;}
.ag-lb-row.is-me{border-color:rgba(212,180,92,.6);background:linear-gradient(160deg,rgba(212,180,92,.16),rgba(243,122,31,.06));}
.ag-lb-row__rank{font-size:1.2rem;font-weight:800;color:#e8c766;}
.ag-lb-row__name{color:#fff;font-weight:600;}
.ag-lb-row__ca{color:var(--color-text-soft);font-weight:700;}
.ag-share{display:flex;gap:12px;flex-wrap:wrap;align-items:center;margin-top:24px;}
.ag-share input{flex:1;min-width:260px;padding:14px 18px;border-radius:12px;border:1px solid rgba(212,180,92,.3);background:rgba(255,255,255,.05);color:#fff;font-size:.95rem;}
.ag-share-btns{display:flex;flex-wrap:wrap;gap:10px;margin-top:18px;}
.ag-sbtn{display:inline-flex;align-items:center;gap:6px;padding:11px 16px;border-radius:12px;font-weight:700;font-size:.9rem;text-decoration:none;color:#fff;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.14);cursor:pointer;transition:transform .2s,border-color .2s,background .2s;}
.ag-sbtn:hover{transform:translateY(-2px);border-color:rgba(212,180,92,.5);color:#fff;text-decoration:none;}
.ag-sbtn--wa:hover{background:rgba(37,211,102,.18);}
.ag-sbtn--fb:hover{background:rgba(24,119,242,.18);}
.ag-sbtn--tg:hover{background:rgba(42,171,238,.18);}
.ag-sbtn--native{background:linear-gradient(135deg,rgba(212,180,92,.25),rgba(243,122,31,.12));border-color:rgba(212,180,92,.5);}
.ag-share-msg{margin-top:26px;}
.ag-share-msg__head{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:10px;}
.ag-share-msg__head strong{color:#fff;}
.ag-share-msg textarea{width:100%;padding:14px 16px;border-radius:12px;border:1px solid rgba(212,180,92,.3);background:rgba(255,255,255,.05);color:#fff;font-size:.95rem;line-height:1.5;resize:vertical;}
@media(max-width:768px){#ag-main-content.ag-esp > .ag-section:first-child{padding-top:170px;}}
@media(max-width:760px){.ag-esp-stats{grid-template-columns:1fr 1fr;}.ag-lb-grid{grid-template-columns:1fr;}.ag-studio-cta{flex-direction:column;align-items:flex-start;}.ag-studio-cta__btn{width:100%;justify-content:center;text-align:center;}.ag-share-btns .ag-sbtn{flex:1;justify-content:center;min-width:44%;}}
</style>

<?php get_footer(); ?>
