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
// L'admin compte aussi dans le classement : on lui garantit une fiche ambassadeur (ref + statut actif).
if ( function_exists( 'ag_espace_member_kind' ) && 'admin' === ag_espace_member_kind() && function_exists( 'ag_ensure_ambassador_for_user' ) ) { ag_ensure_ambassador_for_user( $u ); }
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

			<?php get_template_part( 'template-parts/espace-nav', null, array( 'active' => 'compte' ) ); ?>

			<a href="<?php echo esc_url( home_url( '/programme-ambassadeur' ) ); ?>" class="ag-esp-guide">📚 <strong>Découvre ton programme</strong> — comment vendre des sites &amp; recruter ton équipe, étape par étape <span>→</span></a>

			<?php $ag_tg_group = function_exists( 'ag_tg_cfg' ) ? ag_tg_cfg( 'group_link' ) : ''; if ( $ag_tg_group ) : ?>
			<a href="<?php echo esc_url( $ag_tg_group ); ?>" target="_blank" rel="noopener" class="ag-esp-guide">💬 <strong>Rejoins le groupe Telegram de l'équipe</strong> — annonces, entraide, classement <span>→</span></a>
			<?php endif; ?>

			<?php
			$ob_phone = function_exists( 'ag_amb_phone' ) ? ag_amb_phone( get_current_user_id() ) : '';
			$ob_zones = function_exists( 'ag_zone_of_owner' ) ? ag_zone_of_owner( $email ) : array();
			$ob_tg    = function_exists( 'ag_tg_cfg' ) ? ag_tg_cfg( 'group_link' ) : '';
			$ob_dn    = function_exists( 'ag_dept_names' ) ? ag_dept_names() : array();
			$ob_zlbl  = $ob_zones ? ( $ob_zones[0] . ( isset( $ob_dn[ $ob_zones[0] ] ) ? ' · ' . $ob_dn[ $ob_zones[0] ] : '' ) ) : '';
			$ob_tg_ack = (bool) get_user_meta( get_current_user_id(), 'ag_onboard_tg', true );
			$ob_sugg   = ( function_exists( 'ag_dept_from_location' ) && $rec ) ? ag_dept_from_location( $rec['city'] ?? '', $rec['cp'] ?? '', $rec['address'] ?? '' ) : '';
			$ob_feed   = isset( $_GET['zone'] ) ? sanitize_text_field( wp_unslash( $_GET['zone'] ) ) : '';
			$ob_post   = admin_url( 'admin-post.php' );
			if ( ! $ob_tg_ack )           $ob_step = 1;
			elseif ( '' === $ob_phone )   $ob_step = 2;
			elseif ( empty( $ob_zones ) ) $ob_step = 3;
			else                          $ob_step = 4;
			?>
			<div class="ag-wiz" id="demarrage">
				<div class="ag-wiz__bar"><span style="width:<?php echo (int) ( $ob_step * 25 ); ?>%;"></span></div>
				<div class="ag-wiz__top">Étape <?php echo (int) $ob_step; ?> sur 4</div>

				<?php if ( 1 === $ob_step ) : ?>
					<div class="ag-wiz__step">
						<div class="ag-wiz__ic">💬</div>
						<h3 class="ag-wiz__q">Rejoins le groupe Telegram des ambassadeurs</h3>
						<p class="ag-wiz__d">C'est <strong>obligatoire</strong> : on y partage les annonces, l'entraide et les prospects. Ouvre-le, rejoins, puis reviens cliquer sur « J'ai rejoint ».</p>
						<?php if ( $ob_tg ) : ?><p><a href="<?php echo esc_url( $ob_tg ); ?>" target="_blank" rel="noopener" class="ag-btn-outline">📲 Ouvrir le groupe Telegram</a></p><?php else : ?><p class="ag-wiz__d">Le lien du groupe t'a été envoyé par email.</p><?php endif; ?>
						<form method="post" action="<?php echo esc_url( $ob_post ); ?>">
							<input type="hidden" name="action" value="ag_onboard_tg_ack">
							<?php wp_nonce_field( 'ag_zone_front', '_n' ); ?>
							<button type="submit" class="ag-btn-gold ag-wiz__next">✅ J'ai rejoint, continuer →</button>
						</form>
					</div>

				<?php elseif ( 2 === $ob_step ) : ?>
					<div class="ag-wiz__step">
						<div class="ag-wiz__ic">📱</div>
						<h3 class="ag-wiz__q">Vérifie ton numéro de téléphone</h3>
						<p class="ag-wiz__d">Un numéro = un ambassadeur (pas de multi-comptes). Ça débloque ta zone.</p>
						<?php if ( 'phone_dupe' === $ob_feed ) : ?><p class="ag-wiz__err">Ce numéro est déjà utilisé par un autre compte.</p>
						<?php elseif ( 'phone_err' === $ob_feed ) : ?><p class="ag-wiz__err">Numéro invalide. Format : 0612345678.</p><?php endif; ?>
						<form method="post" action="<?php echo esc_url( $ob_post ); ?>" class="ag-wiz__form">
							<input type="hidden" name="action" value="ag_amb_phone_save">
							<?php wp_nonce_field( 'ag_zone_front', '_n' ); ?>
							<input type="tel" name="phone" value="<?php echo esc_attr( $rec['phone'] ?? '' ); ?>" placeholder="Ton numéro (ex : 0612345678)" required class="ag-wiz__input">
							<button type="submit" class="ag-btn-gold ag-wiz__next">Valider mon numéro →</button>
						</form>
					</div>

				<?php elseif ( 3 === $ob_step ) : ?>
					<div class="ag-wiz__step">
						<div class="ag-wiz__ic">🗺️</div>
						<h3 class="ag-wiz__q">Choisis ta zone de prospection</h3>
						<p class="ag-wiz__d">Ton département, d'après ta ville<?php if ( $ob_sugg && isset( $ob_dn[ $ob_sugg ] ) ) : ?> : <strong style="color:var(--color-gold);"><?php echo esc_html( $ob_sugg . ' · ' . $ob_dn[ $ob_sugg ] ); ?></strong> (déjà pré-rempli)<?php endif; ?>. <strong>1 seule zone</strong> chacun.</p>
						<p class="ag-wiz__tip">💡 Seul sur la zone = <strong>tous</strong> les prospects pour toi. À plusieurs = partage <strong>50/50</strong>. Une zone vide = <strong>plus de prospects</strong>.</p>
						<?php if ( 'err' === $ob_feed ) : ?><p class="ag-wiz__err">Département invalide. Mets 2 chiffres (ex. 33).</p><?php endif; ?>
						<form method="post" action="<?php echo esc_url( $ob_post ); ?>" class="ag-wiz__form">
							<input type="hidden" name="action" value="ag_zone_request">
							<?php wp_nonce_field( 'ag_zone_front', '_n' ); ?>
							<input type="text" name="dept" maxlength="3" value="<?php echo esc_attr( $ob_sugg ); ?>" placeholder="Dép. (ex : 33)" required class="ag-wiz__input" style="max-width:160px;">
							<button type="submit" class="ag-btn-gold ag-wiz__next">Prendre cette zone →</button>
						</form>
					</div>

				<?php else : ?>
					<div class="ag-wiz__step">
						<div class="ag-wiz__ic">🎉</div>
						<h3 class="ag-wiz__q">Tu es prêt à prospecter !</h3>
						<p class="ag-wiz__d">Ta zone : <strong style="color:var(--color-gold);"><?php echo esc_html( $ob_zlbl ); ?></strong>. Les prospects arrivent <strong>automatiquement</strong>. Lance-toi.</p>
						<a href="#chercher" class="ag-btn-gold ag-wiz__next">🔎 Trouver des clients dans ma zone →</a>
						<p class="ag-wiz__d" style="margin-top:10px;"><a href="<?php echo esc_url( $ag_sale_link ); ?>" style="color:var(--color-gold);">…ou partage ton lien de vente</a></p>
					</div>
				<?php endif; ?>
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
			<?php
			$ag_primes = function_exists( 'ag_parrain_primes_for' ) ? ag_parrain_primes_for( $email ) : array();
			if ( $ag_primes ) { $ptot = 0; foreach ( $ag_primes as $pp ) { $ptot += (float) ( $pp['amount'] ?? 0 ); } ?>
				<p class="ag-section__desc" style="margin-top:14px;">🎁 <strong>Primes de parrainage</strong> : tu as gagné <strong style="color:var(--color-gold);"><?php echo esc_html( $eur( $ptot ) ); ?></strong> en recrutant <?php echo count( $ag_primes ); ?> ambassadeur(s) qui ont vendu. Continue à faire grandir ton équipe !</p>
			<?php } ?>
		</div>
	</section>

	<section class="ag-section ag-section--onyx" id="studio">
		<div class="ag-container">
			<span class="ag-tag">Espace création</span>
			<h2 class="ag-section__title">Le Studio 🎬 <em>— ton atelier de contenu</em></h2>
			<p class="ag-section__desc">C'est <strong>séparé de ton compte</strong> : ici tu crées ce que tu vas partager. Visuels déjà prêts, générateur de vidéos et d'images — ton lien de vente est toujours intégré automatiquement.</p>
			<div class="ag-studio-feats">
				<div class="ag-studio-feat"><span class="ag-studio-feat__ic">🖼️</span><strong>Visuels prêts</strong><span>Une médiathèque à poster direct, rien à chercher ailleurs.</span></div>
				<div class="ag-studio-feat"><span class="ag-studio-feat__ic">🎬</span><strong>Vidéos en 1 clic</strong><span>Transforme une image en vidéo verticale avec ton accroche.</span></div>
				<div class="ag-studio-feat"><span class="ag-studio-feat__ic">⤴</span><strong>Partage direct</strong><span>TikTok, Insta, Snap, WhatsApp — sans quitter le site.</span></div>
			</div>
			<a href="<?php echo esc_url( home_url( '/studio' ) ); ?>" class="ag-btn-gold ag-studio-cta__btn">Ouvrir le Studio →</a>
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
			<p style="margin-top:14px;"><a href="<?php echo esc_url( home_url( '/studio?mode=recruit' ) ); ?>" class="ag-btn-outline">🎬 Créer des visuels de recrutement →</a></p>
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

	<?php
	$ag_myzones = function_exists( 'ag_zone_of_owner' ) ? ag_zone_of_owner( $email ) : array();
	$ag_zfeed = isset( $_GET['zone'] ) ? sanitize_text_field( wp_unslash( $_GET['zone'] ) ) : '';
	$ag_phone = function_exists( 'ag_amb_phone' ) ? ag_amb_phone( get_current_user_id() ) : '';
	// Département suggéré d'après la ville/code postal déclarés à l'inscription.
	$ag_sugg = ( ! $ag_myzones && function_exists( 'ag_dept_from_location' ) && $rec ) ? ag_dept_from_location( $rec['city'] ?? '', $rec['cp'] ?? '', $rec['address'] ?? '' ) : '';
	$ag_quota  = function_exists( 'ag_zone_quota' ) ? ag_zone_quota( $email ) : 1;
	$ag_under  = count( $ag_myzones ) < $ag_quota; // peut encore prendre une zone
	$ag_zprice = function_exists( 'ag_zone_price' ) ? ag_zone_price() : 49;
	$ag_zurl   = get_option( 'ag_zone_paypal_url', '' );
	$ag_dn     = function_exists( 'ag_dept_names' ) ? ag_dept_names() : array();
	$ag_post   = admin_url( 'admin-post.php' );
	?>
	<?php if ( isset( $ob_step ) && 4 === $ob_step ) : ?>
	<section class="ag-section ag-section--onyx" id="zone">
		<div class="ag-container">
			<h2 class="ag-section__title">Ma zone 🗺️</h2>

			<?php if ( 'phone_ok' === $ag_zfeed ) : ?><p style="text-align:center;color:#5fd08a;font-weight:700;">✅ Numéro enregistré. Tu peux prendre ta zone.</p>
			<?php elseif ( 'phone_dupe' === $ag_zfeed ) : ?><p style="text-align:center;color:#d98b8b;">Ce numéro est déjà utilisé par un autre compte. Un numéro = un ambassadeur.</p>
			<?php elseif ( 'phone_err' === $ag_zfeed ) : ?><p style="text-align:center;color:#d98b8b;">Numéro invalide. Format : 0612345678.</p>
			<?php elseif ( 'need_phone' === $ag_zfeed ) : ?><p style="text-align:center;color:#e6b35a;">Renseigne d'abord ton numéro de téléphone pour débloquer les zones.</p>
			<?php elseif ( 'claimed' === $ag_zfeed ) : ?><p style="text-align:center;color:#5fd08a;font-weight:700;">✅ Zone prise ! Les prospects arrivent automatiquement.</p>
			<?php elseif ( 'joined' === $ag_zfeed ) : ?><p style="text-align:center;color:#5fd08a;font-weight:700;">🤝 Tu as rejoint la zone — partage 50/50 des prospects.</p>
			<?php elseif ( 'changed' === $ag_zfeed ) : ?><p style="text-align:center;color:#5fd08a;font-weight:700;">✅ Tu as changé de zone.</p>
			<?php elseif ( 'mine' === $ag_zfeed ) : ?><p style="text-align:center;color:var(--color-text-soft);">Cette zone est déjà la tienne.</p>
			<?php elseif ( 'err' === $ag_zfeed ) : ?><p style="text-align:center;color:#d98b8b;">Département invalide. Mets 2 chiffres (ex. 33).</p>
			<?php elseif ( 'released' === $ag_zfeed ) : ?><p style="text-align:center;color:#5fd08a;font-weight:700;">✅ Zone libérée.</p>
			<?php elseif ( 'quota' === $ag_zfeed ) : ?><p style="text-align:center;color:#e6b35a;font-weight:700;">Tu as atteint ton nombre de zones. Libère une zone, ou achète une zone en plus ci-dessous.</p>
			<?php endif; ?>

			<?php if ( '' === $ag_phone ) : ?>
				<p class="ag-section__desc">Avant de prendre une zone, <strong>vérifie ton numéro de téléphone</strong> (un numéro = un ambassadeur, pas de multi-comptes).</p>
				<form method="post" action="<?php echo esc_url( $ag_post ); ?>" style="display:flex;flex-wrap:wrap;gap:10px;justify-content:center;align-items:center;margin-top:14px;">
					<input type="hidden" name="action" value="ag_amb_phone_save">
					<?php wp_nonce_field( 'ag_zone_front', '_n' ); ?>
					<input type="tel" name="phone" placeholder="Ton numéro (ex : 0612345678)" required style="padding:10px 14px;border-radius:10px;border:1px solid rgba(212,180,92,.4);background:rgba(255,255,255,.05);color:#fff;width:240px;">
					<button type="submit" class="ag-btn-gold">Enregistrer mon numéro →</button>
				</form>
			<?php else : ?>
				<p class="ag-section__desc" style="text-align:center;">Tu peux couvrir <strong style="color:var(--color-gold);"><?php echo (int) $ag_quota; ?> zone<?php echo $ag_quota > 1 ? 's' : ''; ?></strong> (<?php echo count( $ag_myzones ); ?> utilisée<?php echo count( $ag_myzones ) > 1 ? 's' : ''; ?>). Les prospects de tes zones te sont <strong>attribués automatiquement</strong>.</p>

				<?php if ( $ag_myzones ) : ?>
					<div style="display:flex;flex-wrap:wrap;gap:8px;justify-content:center;margin:12px 0;">
						<?php foreach ( $ag_myzones as $mz ) : ?>
							<span style="display:inline-flex;align-items:center;gap:8px;background:rgba(212,180,92,.14);border:1px solid rgba(212,180,92,.45);border-radius:100px;padding:6px 12px;color:#fff;">
								<strong><?php echo esc_html( $mz . ( isset( $ag_dn[ $mz ] ) ? ' · ' . $ag_dn[ $mz ] : '' ) ); ?></strong>
								<form method="post" action="<?php echo esc_url( $ag_post ); ?>" style="margin:0;" onsubmit="return confirm('Libérer la zone <?php echo esc_attr( $mz ); ?> ? Tu ne recevras plus ses prospects.');">
									<input type="hidden" name="action" value="ag_zone_release"><?php wp_nonce_field( 'ag_zone_front', '_n' ); ?>
									<input type="hidden" name="dept" value="<?php echo esc_attr( $mz ); ?>">
									<button type="submit" title="Libérer cette zone" style="background:none;border:none;color:#e88;cursor:pointer;font-weight:800;">✕</button>
								</form>
							</span>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ( $ag_under ) : ?>
					<?php if ( ! $ag_myzones && $ag_sugg && isset( $ag_dn[ $ag_sugg ] ) ) : ?>
						<p class="ag-section__desc" style="text-align:center;"><strong style="color:var(--color-gold);">Suggéré selon ta ville : <?php echo esc_html( $ag_sugg . ' · ' . $ag_dn[ $ag_sugg ] ); ?></strong> (déjà pré-rempli).</p>
					<?php endif; ?>
					<form method="post" action="<?php echo esc_url( $ag_post ); ?>" style="display:flex;flex-wrap:wrap;gap:10px;justify-content:center;align-items:center;margin-top:6px;">
						<input type="hidden" name="action" value="ag_zone_request">
						<?php wp_nonce_field( 'ag_zone_front', '_n' ); ?>
						<input type="text" name="dept" maxlength="3" value="<?php echo esc_attr( $ag_myzones ? '' : $ag_sugg ); ?>" placeholder="Département (ex : 33)" required style="padding:10px 14px;border-radius:10px;border:1px solid rgba(212,180,92,.4);background:rgba(255,255,255,.05);color:#fff;width:180px;">
						<button type="submit" class="ag-btn-gold">Prendre cette zone →</button>
					</form>
					<p style="text-align:center;color:var(--color-text-soft);font-size:.85rem;margin-top:10px;">Si la zone est déjà couverte, vous la <strong>partagez 50/50</strong>. On joue collectif.</p>
				<?php else : ?>
					<div style="max-width:520px;margin:14px auto 0;text-align:center;background:rgba(212,180,92,.1);border:1px solid rgba(212,180,92,.4);border-radius:14px;padding:18px;">
						<p style="color:#fff;margin:0 0 6px;"><strong>Tu veux couvrir une zone de plus ?</strong></p>
						<p style="color:var(--color-text-soft);margin:0 0 12px;">1 zone = <strong style="color:var(--color-gold);"><?php echo esc_html( number_format( $ag_zprice, 0, ',', ' ' ) ); ?> €</strong> (paiement unique). Tu peux en acheter autant que tu veux — toute la France si tu veux ! Dès le paiement, ta zone se débloque et tu la choisis ici.</p>
						<?php if ( $ag_zurl ) : ?>
							<a href="<?php echo esc_url( $ag_zurl ); ?>" target="_blank" rel="noopener" class="ag-btn-gold">🗺️ Acheter une zone en plus →</a>
						<?php else : ?>
							<p style="color:var(--color-text-soft);font-size:.85rem;">Lien d'achat bientôt disponible — contacte-nous.</p>
						<?php endif; ?>
						<p style="color:var(--color-text-soft);font-size:.8rem;margin:10px 0 0;">…ou libère une de tes zones ci-dessus pour en prendre une autre gratuitement.</p>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</section>
	<?php endif; ?>

	<?php
	$ag_is_chasseur = function_exists( 'ag_is_chasseur' ) && ag_is_chasseur( get_current_user_id() );
	$ag_chasseur_link = get_option( 'ag_chasseur_paypal_url', '' );
	$ag_chasseur_nonce = wp_create_nonce( 'ag_amb_prospect' );
	$ag_chasseur_ajax = admin_url( 'admin-ajax.php' );
	?>
	<section class="ag-section ag-section--graphite" id="chercher">
		<div class="ag-container">
			<h2 class="ag-section__title">Trouver dans ma zone 🔎</h2>
			<?php if ( $ag_is_chasseur ) : ?>
				<p class="ag-section__desc">Cherche des entreprises de <strong>ta zone</strong> qui ont besoin d'un site. Ajoute-les en 1 clic à ta liste, puis contacte‑les. <span style="color:var(--color-text-soft);">(Restreint à ton département.)</span></p>
				<form id="ag-amb-search-form" onsubmit="return false;" style="display:flex;flex-wrap:wrap;gap:10px;justify-content:center;align-items:center;margin-top:12px;">
					<input type="text" id="ag-amb-city" placeholder="Ville de ta zone (ex : Bordeaux)" style="padding:10px 14px;border-radius:10px;border:1px solid rgba(212,180,92,.4);background:rgba(255,255,255,.05);color:#fff;width:240px;">
					<button type="submit" class="ag-btn-gold" id="ag-amb-search-btn">Chercher →</button>
				</form>
				<p id="ag-amb-search-info" style="text-align:center;color:var(--color-text-soft);font-size:.85rem;margin-top:8px;"></p>
				<div id="ag-amb-search-results" style="margin-top:14px;"></div>
				<script>
				(function(){
					var au=<?php echo wp_json_encode( $ag_chasseur_ajax ); ?>, n=<?php echo wp_json_encode( $ag_chasseur_nonce ); ?>;
					var form=document.getElementById('ag-amb-search-form'), out=document.getElementById('ag-amb-search-results'), info=document.getElementById('ag-amb-search-info'), btn=document.getElementById('ag-amb-search-btn');
					function esc(s){var d=document.createElement('div');d.textContent=(s==null?'':s);return d.innerHTML;}
					form.addEventListener('submit',function(){
						var city=document.getElementById('ag-amb-city').value.trim(); if(!city) return;
						btn.disabled=true; btn.textContent='…'; out.innerHTML=''; info.textContent='Recherche en cours…';
						var fd=new FormData(); fd.append('action','ag_amb_search'); fd.append('_n',n); fd.append('city',city);
						fetch(au,{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json();}).then(function(j){
							btn.disabled=false; btn.textContent='Chercher →';
							if(!j||!j.success){ info.textContent='⚠ '+((j&&j.data&&j.data.m)||'Erreur'); return; }
							var items=j.data.items||[]; info.textContent=items.length+' résultat(s) dans ta zone · '+j.data.left+' recherches restantes ce mois.';
							if(!items.length){ out.innerHTML='<p style="text-align:center;color:#b9b9c0;">Aucune entreprise (sans vrai site) trouvée dans ta zone pour cette ville.</p>'; return; }
							var h='<div style="display:flex;flex-direction:column;gap:10px;">';
							items.forEach(function(it,i){
								h+='<div style="background:rgba(255,255,255,.04);border:1px solid rgba(212,180,92,.25);border-radius:12px;padding:12px 14px;">'
								+'<strong style="color:#fff;">'+esc(it.name)+'</strong> <span style="color:#e6b35a;">'+(it.real?'':'❗ '+esc(it.kind))+'</span>'
								+'<br><small style="color:var(--color-text-soft);">'+esc(it.type)+' · '+esc(it.city)+(it.phone?' · '+esc(it.phone):'')+(it.reviews?' · '+it.reviews+'★avis':'')+'</small><br>'
								+(it.exists?'<span style="color:#b9b9c0;font-size:.85em;">déjà en liste</span>':'<button class="ag-btn-outline ag-amb-follow" data-i="'+i+'" style="padding:5px 12px;margin-top:6px;">+ Suivre</button>')
								+'</div>';
							});
							h+='</div>'; out.innerHTML=h;
							out.querySelectorAll('.ag-amb-follow').forEach(function(b){ b.addEventListener('click',function(){
								var it=items[+b.getAttribute('data-i')]; var fd=new FormData(); fd.append('action','ag_amb_add'); fd.append('_n',n);
								['name','type','city','phone','phone_intl','website','address','rating','reviews'].forEach(function(k){ fd.append(k, it[k]!=null?it[k]:''); });
								b.disabled=true; b.textContent='…';
								fetch(au,{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json();}).then(function(jj){ b.textContent=(jj&&jj.success)?'✓ Ajouté à mes prospects':'Erreur'; }).catch(function(){ b.textContent='Erreur'; b.disabled=false; });
							}); });
						}).catch(function(){ btn.disabled=false; btn.textContent='Chercher →'; info.textContent='⚠ Erreur réseau.'; });
					});
				})();
				</script>
			<?php else : ?>
				<p class="ag-section__desc">Tu reçois déjà des prospects <strong>automatiquement</strong> dans ta zone (gratuit). Pour <strong>chercher toi‑même</strong> de nouvelles entreprises quand tu veux, passe <strong>Chasseur Pro</strong>.</p>
				<div style="max-width:460px;margin:18px auto 0;background:rgba(255,255,255,.04);border:1px solid rgba(212,180,92,.4);border-radius:18px;padding:28px;text-align:center;">
					<div style="font-size:2rem;">💎</div>
					<h3 style="color:#fff;font-family:var(--font-serif);margin:6px 0;">Chasseur Pro</h3>
					<p style="color:var(--color-text-soft);">Recherche illimitée raisonnable dans ta zone (300/mois). <strong style="color:#fff;">19 €/mois</strong>, sans engagement.</p>
					<?php if ( $ag_chasseur_link ) : ?>
						<a href="<?php echo esc_url( $ag_chasseur_link ); ?>" target="_blank" rel="noopener" class="ag-btn-gold" style="margin-top:8px;display:inline-block;">S'abonner — 19 €/mois →</a>
						<p style="color:var(--color-text-soft);font-size:.8rem;margin-top:10px;">Après paiement, ton accès est activé sous peu.</p>
					<?php else : ?>
						<p style="color:#e6b35a;">Abonnement bientôt disponible — reviens vite !</p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<?php
	$ag_my_prospects = function_exists( 'ag_prospects_for_owner' ) ? ag_prospects_for_owner( $email ) : array();
	if ( $ag_my_prospects ) :
		$ag_pstat = function_exists( 'ag_prospect_statuses' ) ? ag_prospect_statuses() : array();
		$ag_ppost = admin_url( 'admin-post.php' );
		$ag_pnonce = wp_nonce_field( 'ag_amb_prospect', '_n', true, false );
		$ag_ajax_url = admin_url( 'admin-ajax.php' );
		$ag_ajax_nonce = wp_create_nonce( 'ag_amb_prospect' );
		$ag_pc = array( 'nouveau' => 0, 'contacte' => 0, 'sans_reponse' => 0, 'interesse' => 0, 'client' => 0, 'bloque' => 0 );
		foreach ( $ag_my_prospects as $ppp ) {
			$ss = $ppp['status'] ?? 'nouveau';
			if ( 'nouveau' === $ss ) $ag_pc['nouveau']++;
			elseif ( in_array( $ss, array( 'contacte', 'relance' ), true ) ) $ag_pc['contacte']++;
			elseif ( 'sans_reponse' === $ss ) $ag_pc['sans_reponse']++;
			elseif ( 'interesse' === $ss ) $ag_pc['interesse']++;
			elseif ( 'client' === $ss ) $ag_pc['client']++;
			elseif ( in_array( $ss, array( 'refus', 'ne_pas_contacter' ), true ) ) $ag_pc['bloque']++;
		}
	?>
	<section class="ag-section ag-section--graphite" id="prospects">
		<div class="ag-container">
			<h2 class="ag-section__title">Mes prospects à contacter 🎯</h2>
			<p class="ag-section__desc">Des entreprises qui ont besoin d'un site, <strong>rien que pour toi</strong> (pas de doublon avec l'équipe). Triées par priorité. Quand tu cliques WhatsApp ou Email, le contact est <strong>noté automatiquement</strong> (date + nombre de fois).</p>
			<div class="ag-amb-chips" style="display:flex;flex-wrap:wrap;gap:8px;justify-content:center;margin:10px 0 22px;">
				<?php
				$ag_chipsdef = array(
					array( 'all', 'Tous', count( $ag_my_prospects ), '#3a3a44' ),
					array( 'nouveau', '🆕 À contacter', $ag_pc['nouveau'], '#b32d2e' ),
					array( 'contacte', '📞 Contactés', $ag_pc['contacte'], '#2271b1' ),
					array( 'sans_reponse', '🔇 Sans réponse', $ag_pc['sans_reponse'], '#8a6d1f' ),
					array( 'interesse', '🔥 Intéressés', $ag_pc['interesse'], '#bd7b00' ),
					array( 'client', '✅ Clients', $ag_pc['client'], '#1e7e34' ),
				);
				foreach ( $ag_chipsdef as $c ) :
					if ( 'all' !== $c[0] && 0 === (int) $c[2] ) continue; ?>
					<button type="button" class="ag-amb-chip" data-f="<?php echo esc_attr( $c[0] ); ?>" style="cursor:pointer;border:none;border-radius:100px;padding:6px 14px;font-weight:700;font-size:.84rem;color:#fff;background:<?php echo esc_attr( $c[3] ); ?>;opacity:<?php echo 'all' === $c[0] ? '1' : '.85'; ?>;"><?php echo esc_html( $c[1] ); ?> <?php echo (int) $c[2]; ?></button>
				<?php endforeach; ?>
			</div>
			<div class="ag-esp-table-wrap">
				<table class="ag-esp-table">
					<thead><tr><th>Entreprise</th><th>Pourquoi</th><th>Contacter</th><th>Statut</th></tr></thead>
					<tbody>
					<?php foreach ( $ag_my_prospects as $pp ) :
						$pmsg = function_exists( 'ag_prospect_message' ) ? ag_prospect_message( $pp, $ag_sale_link ) : '';
						$pdig = function_exists( 'ag_wa_number' ) ? ag_wa_number( $pp['phone'] ?? '', $pp['phone_intl'] ?? '' ) : preg_replace( '/[^0-9]/', '', $pp['phone'] ?? '' );
						$pwa  = $pdig ? 'https://wa.me/' . $pdig . '?text=' . rawurlencode( $pmsg ) : '';
						$psnum = preg_replace( '/[^0-9+]/', '', $pp['phone_intl'] ?? '' ) ?: preg_replace( '/[^0-9+]/', '', $pp['phone'] ?? '' );
						$psms = $psnum ? 'sms:' . $psnum . '?body=' . rawurlencode( $pmsg ) : '';
						$pmail = ! empty( $pp['email'] ) ? 'mailto:' . rawurlencode( $pp['email'] ) . '?subject=' . rawurlencode( 'Votre site web — Alliance Groupe' ) . '&body=' . rawurlencode( $pmsg ) : '';
						$pstatus = $pp['status'] ?? 'nouveau';
						if ( in_array( $pstatus, array( 'refus', 'ne_pas_contacter' ), true ) ) continue; // bloqué : ne réapparaît plus
						$pgrp = in_array( $pstatus, array( 'contacte', 'relance' ), true ) ? 'contacte' : $pstatus;
						$pid  = $pp['id'] ?? '';
					?>
						<tr class="ag-amb-prow" data-grp="<?php echo esc_attr( $pgrp ); ?>">
							<td><strong><?php echo esc_html( $pp['name'] ?? '' ); ?></strong><br><small><?php echo esc_html( ( $pp['type'] ?? '' ) . ( ! empty( $pp['city'] ) ? ' · ' . $pp['city'] : '' ) ); ?></small></td>
							<td style="font-size:.85em;color:var(--color-text-soft);max-width:240px;"><?php echo esc_html( function_exists( 'ag_prospect_why' ) ? ag_prospect_why( $pp ) : '' ); ?></td>
							<td>
								<div class="ag-amb-suivi" data-id="<?php echo esc_attr( $pid ); ?>" style="font-size:.82em;margin-bottom:6px;line-height:1.5;<?php echo empty( $pp['date_contact'] ) ? 'color:#e6b35a;' : 'color:var(--color-text-soft);'; ?>">
									<?php if ( ! empty( $pp['date_contact'] ) ) : ?>
										📨 Contacté<?php echo ! empty( $pp['last_channel'] ) ? ' par <strong>' . esc_html( $pp['last_channel'] ) . '</strong>' : ''; ?> le <strong><?php echo esc_html( $pp['date_contact'] ); ?></strong> (×<?php echo (int) ( $pp['contact_count'] ?? 1 ); ?>)<br>
										<?php if ( ! empty( $pp['replied'] ) ) : ?>
											<span style="color:#5fd08a;font-weight:700;">✅ A répondu<?php echo $pp['date_reply'] ? ' le ' . esc_html( $pp['date_reply'] ) : ''; ?></span>
										<?php else : ?>
											<button type="button" class="ag-amb-reply ag-btn-outline" data-id="<?php echo esc_attr( $pid ); ?>" style="padding:3px 10px;font-size:.95em;">A répondu ?</button>
										<?php endif; ?>
									<?php else : ?>⏳ Pas encore contacté<?php endif; ?>
								</div>
								<?php if ( ! empty( $pp['phone'] ) ) : ?><a href="tel:<?php echo esc_attr( $pp['phone'] ); ?>" class="ag-btn-outline ag-amb-touch" data-id="<?php echo esc_attr( $pid ); ?>" data-channel="Appel" style="padding:6px 10px;">📞 Appeler</a> <?php endif; ?>
								<?php if ( $pwa ) : ?><a href="<?php echo esc_url( $pwa ); ?>" target="_blank" rel="noopener" class="ag-btn-outline ag-amb-touch" data-id="<?php echo esc_attr( $pid ); ?>" data-channel="WhatsApp" style="padding:6px 10px;">WhatsApp</a> <?php endif; ?>
								<?php if ( $psms ) : ?><a href="<?php echo esc_attr( $psms ); ?>" class="ag-btn-outline ag-amb-touch" data-id="<?php echo esc_attr( $pid ); ?>" data-channel="SMS" style="padding:6px 10px;">📱 SMS</a> <?php endif; ?>
								<?php if ( $pmail ) : ?><a href="<?php echo esc_url( $pmail ); ?>" class="ag-btn-outline ag-amb-touch" data-id="<?php echo esc_attr( $pid ); ?>" data-channel="Email" style="padding:6px 10px;">Email</a> <?php endif; ?>
								<details style="margin-top:6px;"><summary style="cursor:pointer;color:var(--color-gold);">Voir le message</summary><textarea readonly rows="8" style="width:100%;margin-top:6px;background:rgba(255,255,255,.05);color:#fff;border:1px solid rgba(212,180,92,.3);border-radius:10px;padding:10px;"><?php echo esc_textarea( $pmsg ); ?></textarea></details>
							</td>
							<td>
								<form method="post" action="<?php echo esc_url( $ag_ppost ); ?>">
									<input type="hidden" name="action" value="ag_amb_prospect_status">
									<?php echo $ag_pnonce; // phpcs:ignore ?>
									<input type="hidden" name="id" value="<?php echo esc_attr( $pp['id'] ?? '' ); ?>">
									<select name="status" onchange="this.form.submit()" style="padding:6px;border-radius:8px;">
										<?php foreach ( $ag_pstat as $sk => $sl ) : ?><option value="<?php echo esc_attr( $sk ); ?>" <?php selected( $pp['status'] ?? 'nouveau', $sk ); ?>><?php echo esc_html( $sl ); ?></option><?php endforeach; ?>
									</select>
								</form>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
		<script>
		(function(){
			var au=<?php echo wp_json_encode( $ag_ajax_url ); ?>, n=<?php echo wp_json_encode( $ag_ajax_nonce ); ?>;
			document.querySelectorAll('.ag-amb-touch').forEach(function(a){ a.addEventListener('click',function(){
				var id=a.getAttribute('data-id'); if(!id) return;
				var ch=a.getAttribute('data-channel')||'';
					var fd=new FormData(); fd.append('action','ag_amb_touch'); fd.append('_n',n); fd.append('id',id); fd.append('channel',ch);
				fetch(au,{method:'POST',body:fd,credentials:'same-origin',keepalive:true}).then(function(r){return r.json();}).then(function(j){
					if(j&&j.success){ var d=a.closest('td').querySelector('.ag-amb-suivi'); if(d){ var par=j.data.channel?(' par <strong>'+j.data.channel+'</strong>'):''; d.style.color='var(--color-text-soft)'; d.innerHTML='📨 Contacté'+par+' le <strong>'+j.data.date+'</strong> (×'+j.data.count+')<br><button type="button" class="ag-amb-reply ag-btn-outline" data-id="'+id+'" style="padding:3px 10px;font-size:.95em;">A répondu ?</button>'; bindReply(d.querySelector('.ag-amb-reply')); } }
				}).catch(function(){});
			}); });
			function bindReply(btn){ if(!btn) return; btn.addEventListener('click',function(){
				var id=btn.getAttribute('data-id'); var fd=new FormData(); fd.append('action','ag_amb_reply'); fd.append('_n',n); fd.append('id',id);
				btn.disabled=true; fetch(au,{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json();}).then(function(j){ if(j&&j.success){ btn.outerHTML='<span style="color:#5fd08a;font-weight:700;">✅ A répondu le '+j.data.date+'</span>'; } else { btn.disabled=false; } }).catch(function(){ btn.disabled=false; });
			}); }
			document.querySelectorAll('.ag-amb-reply').forEach(bindReply);
			document.querySelectorAll('.ag-amb-chip').forEach(function(c){ c.addEventListener('click',function(){
				var f=c.getAttribute('data-f');
				document.querySelectorAll('.ag-amb-chip').forEach(function(x){ x.style.opacity=(x===c)?'1':'.6'; });
				document.querySelectorAll('.ag-amb-prow').forEach(function(tr){ tr.style.display=(f==='all'||tr.getAttribute('data-grp')===f)?'':'none'; });
			}); });
		})();
		</script>
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
.ag-esp-guide{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin:16px 0 0;padding:15px 20px;background:linear-gradient(135deg,rgba(212,180,92,.16),rgba(243,122,31,.06));border:1px solid rgba(212,180,92,.45);border-radius:14px;color:#fff;text-decoration:none;font-size:.95rem;transition:transform .2s,border-color .2s;}
.ag-esp-guide:hover{transform:translateY(-2px);border-color:rgba(212,180,92,.7);color:#fff;text-decoration:none;}
.ag-esp-guide strong{color:#e8c766;}
.ag-esp-guide span{margin-left:auto;color:#e8c766;font-weight:800;}
.ag-wiz{margin:18px auto 0;max-width:620px;padding:24px 22px;background:rgba(255,255,255,.03);border:1px solid rgba(212,180,92,.4);border-radius:18px;text-align:center;}
.ag-wiz__bar{height:8px;background:rgba(255,255,255,.08);border-radius:100px;overflow:hidden;margin-bottom:10px;}
.ag-wiz__bar span{display:block;height:100%;background:linear-gradient(90deg,#D4B45C,#F37A1F);transition:width .4s;}
.ag-wiz__top{color:#e8c766;font-weight:800;font-size:.78rem;letter-spacing:.05em;text-transform:uppercase;margin-bottom:14px;}
.ag-wiz__ic{font-size:2.6rem;line-height:1;margin-bottom:6px;}
.ag-wiz__q{color:#fff;font-family:var(--font-serif,serif);font-size:1.5rem;margin:0 0 8px;}
.ag-wiz__d{color:#cfcfd6;font-size:.98rem;line-height:1.5;margin:0 auto 14px;max-width:520px;}
.ag-wiz__tip{color:#cfc7b8;font-size:.86rem;line-height:1.5;margin:0 auto 14px;max-width:520px;background:rgba(212,180,92,.1);border-radius:10px;padding:8px 12px;}
.ag-wiz__err{color:#ffb3b3;font-weight:700;margin:0 0 10px;}
.ag-wiz__form{display:flex;gap:10px;justify-content:center;flex-wrap:wrap;align-items:center;}
.ag-wiz__input{padding:12px 16px;border-radius:10px;border:1px solid rgba(212,180,92,.4);background:rgba(255,255,255,.06);color:#fff;font-size:1rem;width:260px;max-width:100%;}
.ag-wiz__next{font-size:1.05rem;}
.ag-esp-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;margin-top:34px;}
.ag-esp-stat{padding:24px 20px;background:rgba(255,255,255,.03);border:1px solid rgba(212,180,92,.16);border-radius:16px;text-align:center;}
.ag-esp-stat--gold{border-color:rgba(212,180,92,.5);background:linear-gradient(160deg,rgba(212,180,92,.12),rgba(243,122,31,.05));}
.ag-esp-stat__val{display:block;font-family:var(--font-serif);font-size:1.9rem;font-weight:800;background:linear-gradient(135deg,#D4B45C,#F37A1F);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;color:transparent;}
.ag-esp-stat__lbl{display:block;margin-top:6px;color:var(--color-text-soft);font-size:.85rem;}
.ag-studio-cta__btn{flex-shrink:0;margin-top:26px;}
.ag-studio-feats{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-top:28px;}
.ag-studio-feat{padding:22px 20px;background:rgba(255,255,255,.03);border:1px solid rgba(212,180,92,.18);border-radius:16px;display:flex;flex-direction:column;gap:6px;}
.ag-studio-feat__ic{font-size:1.7rem;}
.ag-studio-feat strong{color:#fff;font-size:1rem;}
.ag-studio-feat span:not(.ag-studio-feat__ic){color:var(--color-text-soft);font-size:.88rem;line-height:1.45;}
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
@media(max-width:760px){.ag-esp-stats{grid-template-columns:1fr 1fr;}.ag-lb-grid{grid-template-columns:1fr;}.ag-studio-feats{grid-template-columns:1fr;}.ag-studio-cta__btn{width:100%;justify-content:center;text-align:center;}.ag-share-btns .ag-sbtn{flex:1;justify-content:center;min-width:44%;}}
</style>

<?php get_footer(); ?>
