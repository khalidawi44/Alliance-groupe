<?php
/**
 * Template Name: Espace Commercial
 *
 * Tableau de bord de l'ambassadeur connecté : statut, commissions, ventes,
 * déclaration de vente. Accès protégé dans inc/ag-espaces.php.
 */
get_header();

$u      = wp_get_current_user();
$email  = $u->user_email;
$name   = $u->display_name ? $u->display_name : $u->user_login;
$rec    = function_exists( 'ag_ambassadeur_record' ) ? ag_ambassadeur_record( $email ) : null;
$actif  = $rec && ( ( $rec['status'] ?? '' ) === 'actif' );
$ventes = function_exists( 'ag_ambassadeur_ventes_for' ) ? ag_ambassadeur_ventes_for( $email ) : array();

$nb = 0; $ca = 0; $due = 0; $paid = 0; $pending = 0;
foreach ( $ventes as $v ) {
	$st = $v['statut'] ?? '';
	if ( in_array( $st, array( 'validee', 'payee' ), true ) ) { $nb++; $ca += (float) $v['montant']; }
	if ( 'validee' === $st )  $due     += (float) $v['commission'];
	if ( 'payee' === $st )    $paid    += (float) $v['commission'];
	if ( 'declaree' === $st ) $pending += (float) $v['commission'];
}
$eur = function ( $n ) { return number_format( (float) $n, 2, ',', ' ' ) . ' €'; };
$labels = array( 'declaree' => 'En attente', 'validee' => 'Validée', 'payee' => 'Payée' );
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

	<?php
	$ag_sale_link = function_exists( 'ag_ambassadeur_sale_link' ) ? ag_ambassadeur_sale_link( $email ) : home_url( '/sites-express' );
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
	<section class="ag-section ag-section--onyx">
		<div class="ag-container">
			<h2 class="ag-section__title">Partager les offres 🚀</h2>
			<p class="ag-section__desc">Ton <strong>code de vente est déjà intégré</strong> dans chaque partage. Dès qu'un client passe par ton lien et envoie son brief, la vente se crédite automatiquement sur ton compte — sans rien déclarer.</p>

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
				<p class="ag-share__note">TikTok / Instagram / Snapchat n'autorisent pas les liens cliquables dans les vidéos : colle ce message dans ta <strong>bio</strong> ou en <strong>DM</strong>. Sur mobile, « Partager… » ouvre directement TikTok, Snap, etc.</p>
			</div>
		</div>
	</section>
	<script>
	(function(){
		var link = <?php echo wp_json_encode( $ag_sale_link ); ?>;
		var pitch = <?php echo wp_json_encode( $ag_pitch ); ?>;
		var nb = document.getElementById('ag-native');
		if (nb) {
			if (navigator.share) { nb.addEventListener('click', function(){ navigator.share({title:'Alliance Groupe', text:pitch, url:link}).catch(function(){}); }); }
			else { nb.style.display = 'none'; }
		}
		var cm = document.getElementById('ag-copymsg');
		if (cm) cm.addEventListener('click', function(){ navigator.clipboard.writeText(document.getElementById('ag-msgtext').value).then(function(){ cm.textContent='✓ Copié'; setTimeout(function(){ cm.textContent='Copier le message'; },1500); }); });
	})();
	</script>

	<?php
	$ag_assets  = get_stylesheet_directory_uri() . '/assets/images/produits/';
	$ag_visuels = array(
		'produit-essentiel.jpg'   => 'Essentiel — 490 €',
		'produit-pro.jpg'         => 'Pro — 890 €',
		'produit-boutique.jpg'    => 'Boutique — 1 490 €',
		'produit-serenite.jpg'    => 'Maintenance — 29 €/mois',
		'produit-croissance.jpg'  => 'Maintenance — 59 €/mois',
		'produit-performance.jpg' => 'Maintenance — 99 €/mois',
	);
	?>
	<section class="ag-section ag-section--graphite">
		<div class="ag-container">
			<h2 class="ag-section__title">Visuels prêts à poster 🎨</h2>
			<p class="ag-section__desc">Télécharge un visuel, poste-le, et mets ton message (lien intégré) en légende.</p>
			<div class="ag-vis-grid">
				<?php foreach ( $ag_visuels as $file => $label ) : $src = $ag_assets . $file; ?>
					<div class="ag-vis">
						<img src="<?php echo esc_url( $src ); ?>" alt="<?php echo esc_attr( $label ); ?>" loading="lazy">
						<a class="ag-btn-gold ag-vis__dl" href="<?php echo esc_url( $src ); ?>" download>⬇ Télécharger</a>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="ag-section ag-section--onyx">
		<div class="ag-container ag-container--narrow">
			<h2 class="ag-section__title">Crée ton visuel perso 🖌️</h2>
			<p class="ag-section__desc">Mets <strong>ta</strong> photo et ton accroche — on ajoute la marque et ton lien automatiquement. Télécharge et poste sur TikTok, Insta, Snap…</p>
			<div class="ag-maker">
				<div class="ag-maker__preview"><canvas id="ag-canvas" width="1080" height="1350"></canvas></div>
				<div class="ag-maker__ctrl">
					<label>Ta photo<input type="file" id="ag-img" accept="image/*"></label>
					<label>Ton accroche<input type="text" id="ag-head" value="Ton site pro, dès 490 €" maxlength="48"></label>
					<label>Sous-texte<input type="text" id="ag-sub" value="Livré en quelques jours · sans rendez-vous" maxlength="64"></label>
					<button type="button" class="ag-btn-gold" id="ag-dl">⬇ Télécharger mon visuel</button>
				</div>
			</div>
		</div>
	</section>
	<script>
	(function(){
		var canvas=document.getElementById('ag-canvas'); if(!canvas) return;
		var ctx=canvas.getContext('2d'), W=canvas.width, H=canvas.height, userImg=null;
		var link=<?php echo wp_json_encode( $ag_sale_link ); ?>;
		function wrap(text,x,y,maxW,lh){var words=(text||'').split(' '),line='',yy=y;for(var i=0;i<words.length;i++){var t=line+words[i]+' ';if(ctx.measureText(t).width>maxW&&i>0){ctx.fillText(line.trim(),x,yy);line=words[i]+' ';yy+=lh;}else line=t;}ctx.fillText(line.trim(),x,yy);return yy;}
		function draw(){
			ctx.fillStyle='#0e0d11';ctx.fillRect(0,0,W,H);
			if(userImg){var ir=userImg.width/userImg.height,cr=W/H,dw,dh,dx,dy;if(ir>cr){dh=H;dw=H*ir;dx=(W-dw)/2;dy=0;}else{dw=W;dh=W/ir;dx=0;dy=(H-dh)/2;}ctx.drawImage(userImg,dx,dy,dw,dh);}
			else{ctx.fillStyle='#1a1820';ctx.fillRect(0,0,W,H);ctx.fillStyle='#6b6675';ctx.font='34px Arial';ctx.textAlign='center';ctx.fillText('Ajoute ta photo ↑',W/2,H/2);}
			var g=ctx.createLinearGradient(0,H*0.4,0,H);g.addColorStop(0,'rgba(7,7,12,0)');g.addColorStop(1,'rgba(7,7,12,.94)');ctx.fillStyle=g;ctx.fillRect(0,0,W,H);
			ctx.shadowColor='rgba(0,0,0,.6)';ctx.shadowBlur=12;
			ctx.textAlign='left';ctx.fillStyle='#D4B45C';ctx.font='bold 36px Georgia, serif';ctx.fillText('ALLIANCE GROUPE',60,84);
			ctx.fillStyle='#ffffff';ctx.font='bold 70px Georgia, serif';wrap((document.getElementById('ag-head').value||''),60,H-250,W-120,78);
			ctx.fillStyle='#E8C766';ctx.font='600 36px Arial';ctx.fillText((document.getElementById('ag-sub').value||''),60,H-150);
			ctx.shadowBlur=0;ctx.fillStyle='#cfc7b8';ctx.font='600 32px Arial';ctx.fillText(link.replace(/^https?:\/\//,''),60,H-88);
		}
		document.getElementById('ag-img').addEventListener('change',function(e){var f=e.target.files&&e.target.files[0];if(!f)return;var img=new Image();img.onload=function(){userImg=img;draw();};img.src=URL.createObjectURL(f);});
		document.getElementById('ag-head').addEventListener('input',draw);
		document.getElementById('ag-sub').addEventListener('input',draw);
		document.getElementById('ag-dl').addEventListener('click',function(){canvas.toBlob(function(b){var a=document.createElement('a');a.href=URL.createObjectURL(b);a.download='alliance-visuel.png';document.body.appendChild(a);a.click();a.remove();URL.revokeObjectURL(a.href);},'image/png');});
		draw();
	})();
	</script>

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
			<p class="ag-section__desc">Plus tu vends, plus tu montes. Chaque mois, le top des commerciaux décroche des primes.</p>
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
	<section class="ag-section ag-section--onyx">
		<div class="ag-container ag-container--narrow">
			<h2 class="ag-section__title">Déclarer une vente</h2>
			<p class="ag-section__desc">Dès qu'un client signe, déclare-la ici. Une fois encaissée et validée, ta commission de 10 % apparaît dans « à recevoir ».</p>
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

	<section class="ag-section ag-section--graphite">
		<div class="ag-container">
			<h2 class="ag-section__title">Mes ventes</h2>
			<?php if ( empty( $ventes ) ) : ?>
				<p class="ag-section__desc">Aucune vente déclarée pour l'instant.<?php echo $actif ? ' Déclare ta première vente ci-dessus 👆' : ''; ?></p>
			<?php else : ?>
				<div class="ag-esp-table-wrap">
					<table class="ag-esp-table">
						<thead><tr><th>Date</th><th>Client</th><th>Montant</th><th>Commission</th><th>Statut</th></tr></thead>
						<tbody>
						<?php foreach ( $ventes as $v ) : $st = $v['statut'] ?? ''; ?>
							<tr>
								<td data-l="Date"><?php echo esc_html( $v['date'] ?? '' ); ?></td>
								<td data-l="Client"><?php echo esc_html( $v['client'] ?? '' ); ?></td>
								<td data-l="Montant"><?php echo esc_html( $eur( $v['montant'] ?? 0 ) ); ?></td>
								<td data-l="Commission"><?php echo esc_html( $eur( $v['commission'] ?? 0 ) ); ?></td>
								<td data-l="Statut"><span class="ag-esp-badge ag-esp-badge--<?php echo esc_attr( $st ); ?>"><?php echo esc_html( $labels[ $st ] ?? $st ); ?></span></td>
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
.ag-share__note{margin-top:14px;color:var(--color-text-muted);font-size:.88rem;}
.ag-share-btns{display:flex;flex-wrap:wrap;gap:10px;margin-top:18px;}
.ag-sbtn{display:inline-flex;align-items:center;gap:6px;padding:11px 16px;border-radius:12px;font-weight:700;font-size:.9rem;text-decoration:none;color:#fff;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.14);cursor:pointer;transition:transform .2s,border-color .2s,background .2s;}
.ag-sbtn:hover{transform:translateY(-2px);border-color:rgba(212,180,92,.5);color:#fff;text-decoration:none;}
.ag-sbtn--wa:hover{background:rgba(37,211,102,.18);}
.ag-sbtn--fb:hover{background:rgba(24,119,242,.18);}
.ag-sbtn--x:hover{background:rgba(255,255,255,.14);}
.ag-sbtn--tg:hover{background:rgba(42,171,238,.18);}
.ag-sbtn--native{background:linear-gradient(135deg,rgba(212,180,92,.25),rgba(243,122,31,.12));border-color:rgba(212,180,92,.5);}
.ag-share-msg{margin-top:26px;}
.ag-share-msg__head{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:10px;}
.ag-share-msg__head strong{color:#fff;}
.ag-share-msg textarea{width:100%;padding:14px 16px;border-radius:12px;border:1px solid rgba(212,180,92,.3);background:rgba(255,255,255,.05);color:#fff;font-size:.95rem;line-height:1.5;resize:vertical;}
.ag-vis-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-top:30px;}
.ag-vis{background:rgba(255,255,255,.03);border:1px solid rgba(212,180,92,.18);border-radius:16px;overflow:hidden;display:flex;flex-direction:column;}
.ag-vis img{width:100%;height:auto;display:block;}
.ag-vis__dl{margin:14px;text-align:center;}
.ag-maker{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:28px;align-items:start;}
.ag-maker__preview{background:#0e0d11;border:1px solid rgba(212,180,92,.18);border-radius:14px;overflow:hidden;}
.ag-maker__preview canvas{width:100%;height:auto;display:block;}
.ag-maker__ctrl{display:flex;flex-direction:column;gap:16px;}
.ag-maker__ctrl label{display:flex;flex-direction:column;gap:8px;color:var(--color-text-soft);font-size:.9rem;font-weight:600;}
.ag-maker__ctrl input[type=text]{padding:13px 16px;border-radius:12px;border:1px solid rgba(212,180,92,.3);background:rgba(255,255,255,.05);color:#fff;font-size:.95rem;}
.ag-maker__ctrl input[type=file]{color:var(--color-text-soft);font-size:.88rem;}
@media(max-width:760px){.ag-vis-grid{grid-template-columns:1fr 1fr;}.ag-maker{grid-template-columns:1fr;}}
@media(max-width:760px){.ag-esp-stats{grid-template-columns:1fr 1fr;}.ag-lb-grid{grid-template-columns:1fr;}}
</style>

<?php get_footer(); ?>
