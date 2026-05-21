<?php
/**
 * Template Name: Programme Ambassadeur
 *
 * Le « programme » d'onboarding des ambassadeurs : comment vendre des sites
 * et comment recruter une équipe, étape par étape. Emplacement vidéo en haut
 * (configurable : Réglages > Programme ambassadeur). Accès gardé (ambassadeur/admin).
 */
get_header();

$u     = wp_get_current_user();
$email = ( $u && $u->ID ) ? $u->user_email : '';
$sale  = ( $email && function_exists( 'ag_ambassadeur_sale_link' ) ) ? ag_ambassadeur_sale_link( $email ) : home_url( '/sites-express' );
$recr  = ( $email && function_exists( 'ag_ambassadeur_recruit_link' ) ) ? ag_ambassadeur_recruit_link( $email ) : home_url( '/ambassadeurs' );
$video = function_exists( 'ag_amb_guide_video_embed' ) ? ag_amb_guide_video_embed() : '';
?>
<main id="ag-main-content" class="ag-esp ag-guide">
	<section class="ag-section ag-section--graphite">
		<div class="ag-container">
			<div class="ag-esp-head">
				<div><span class="ag-tag">Ton programme 🚀</span><h1 class="ag-section__title">Comment gagner avec <em>Alliance Groupe</em></h1></div>
				<a href="<?php echo esc_url( home_url( '/espace-ambassadeur' ) ); ?>" class="ag-btn-outline ag-esp-logout">← Mon compte</a>
			</div>
			<?php get_template_part( 'template-parts/espace-nav', null, array( 'active' => 'programme' ) ); ?>
			<p class="ag-section__desc">Tu as <strong>2 façons de gagner</strong> : vendre des sites (tu touches 10 %) et recruter une équipe (tu touches un bonus sur leurs ventes). Suis le programme, tout est déjà prêt pour toi.</p>

			<?php if ( $video ) : ?>
				<?php echo $video; // déjà échappé dans ag_amb_guide_video_embed() ?>
			<?php else : ?>
				<div class="ag-guide-novid">🎥 La vidéo explicative arrive bientôt. En attendant, suis les étapes ci-dessous — c'est tout aussi simple.</div>
			<?php endif; ?>
		</div>
	</section>

	<!-- VENDRE -->
	<section class="ag-section ag-section--onyx">
		<div class="ag-container">
			<h2 class="ag-section__title">🛒 Vendre des sites — <em>10 % par vente</em></h2>
			<div class="ag-guide-steps">
				<div class="ag-guide-step"><span class="ag-guide-step__n">1</span><div><strong>Récupère ton lien de vente</strong><p>Il est dans « Mon compte » et déjà intégré à tout ce que tu crées. Ton code y est : chaque clic est tracé pendant 30 jours.</p></div></div>
				<div class="ag-guide-step"><span class="ag-guide-step__n">2</span><div><strong>Crée un contenu au Studio</strong><p>Choisis un fond, écris ton accroche, génère ta vidéo (8 s) ou ton image. Ton lien est ajouté automatiquement.</p></div></div>
				<div class="ag-guide-step"><span class="ag-guide-step__n">3</span><div><strong>Partage + colle la légende</strong><p>Touche « Partager » : la légende (avec ton lien) est copiée toute seule. Sur TikTok/Snap : appui long → « Coller ». Et mets ton lien <strong>en bio</strong>.</p></div></div>
				<div class="ag-guide-step"><span class="ag-guide-step__n">4</span><div><strong>Un client commande = ta vente</strong><p>Quand quelqu'un passe par ton lien et commande, la vente se crédite à ton compte automatiquement. Tu touches 10 %.</p></div></div>
			</div>
			<div class="ag-guide-cta">
				<a href="<?php echo esc_url( home_url( '/studio' ) ); ?>" class="ag-btn-gold">🎬 Créer une vidéo de vente →</a>
				<button type="button" class="ag-btn-outline ag-guide-copy" data-copy="<?php echo esc_attr( $sale ); ?>">📋 Copier mon lien de vente</button>
			</div>
		</div>
	</section>

	<!-- RECRUTER -->
	<section class="ag-section ag-section--graphite">
		<div class="ag-container">
			<h2 class="ag-section__title">🤝 Recruter ton équipe — <em>un bonus sur leurs ventes</em></h2>
			<div class="ag-guide-steps">
				<div class="ag-guide-step"><span class="ag-guide-step__n">1</span><div><strong>Passe le Studio en mode « Recruter »</strong><p>Le bouton « 🤝 Recruter mon équipe » bascule ton lien vers l'inscription ambassadeur (lien d'équipe).</p></div></div>
				<div class="ag-guide-step"><span class="ag-guide-step__n">2</span><div><strong>Partage ton lien de recrutement</strong><p>« Gagne de l'argent en vendant des sites depuis ton tel. » Vidéos, stories, DM : tout est dans le Studio.</p></div></div>
				<div class="ag-guide-step"><span class="ag-guide-step__n">3</span><div><strong>Ils s'inscrivent = ton équipe</strong><p>Chaque personne inscrite via ton lien devient ton « filleul ». Tu les vois dans « Mon compte ».</p></div></div>
				<div class="ag-guide-step"><span class="ag-guide-step__n">4</span><div><strong>Ils vendent = tu touches un bonus</strong><p>Tu gagnes un bonus de parrainage sur <strong>les ventes de ton équipe</strong> (en plus de tes propres 10 %). Plus ton équipe vend, plus tu gagnes.</p></div></div>
			</div>
			<div class="ag-guide-cta">
				<a href="<?php echo esc_url( home_url( '/studio?mode=recruit' ) ); ?>" class="ag-btn-gold">🎬 Créer une vidéo de recrutement →</a>
				<button type="button" class="ag-btn-outline ag-guide-copy" data-copy="<?php echo esc_attr( $recr ); ?>">📋 Copier mon lien d'équipe</button>
			</div>
			<p class="ag-guide-note">Tu es payé sur les <strong>ventes</strong> de ton équipe, jamais pour le simple recrutement (c'est la règle, et c'est ce qui est légal).</p>
		</div>
	</section>

	<!-- POSTER -->
	<section class="ag-section ag-section--onyx">
		<div class="ag-container">
			<h2 class="ag-section__title">📲 Poster sur TikTok / Snap / Insta</h2>
			<p class="ag-section__desc">Sur mobile, aucune appli ne pré-remplit la légende automatiquement — voici la méthode la plus rapide (2 gestes) :</p>
			<div class="ag-guide-steps">
				<div class="ag-guide-step"><span class="ag-guide-step__n">A</span><div><strong>Touche « Partager ma vidéo »</strong><p>Ta légende (avec ton lien) est <strong>copiée automatiquement</strong>. Choisis ton appli dans le menu, OU « Enregistrer la vidéo » pour la prendre dans ta galerie.</p></div></div>
				<div class="ag-guide-step"><span class="ag-guide-step__n">B</span><div><strong>Dans l'appli : colle la légende</strong><p>Appui long sur le champ texte → « Coller ». Ajoute un son tendance, et publie 🚀</p></div></div>
			</div>
			<div class="ag-guide-tips">
				<h3>🔥 Pour percer</h3>
				<ul>
					<li><strong>Accroche dans les 2 premières secondes</strong>, sinon les gens scrollent.</li>
					<li><strong>1 vidéo par jour</strong>, format 9:16, sous-titres activés.</li>
					<li>Ton <strong>lien en bio</strong> + en légende (sinon le clic ne mène nulle part).</li>
					<li>Réutilise les <strong>idées &amp; scripts</strong> du Studio.</li>
				</ul>
			</div>
		</div>
	</section>

	<section class="ag-section ag-section--graphite">
		<div class="ag-container" style="text-align:center;">
			<h2 class="ag-section__title">Prêt ? À toi de jouer 💪</h2>
			<div class="ag-guide-cta" style="justify-content:center;">
				<a href="<?php echo esc_url( home_url( '/studio' ) ); ?>" class="ag-btn-gold">Ouvrir le Studio →</a>
				<a href="<?php echo esc_url( home_url( '/espace-ambassadeur' ) ); ?>" class="ag-btn-outline">Voir mes ventes</a>
			</div>
		</div>
	</section>
</main>

<style>
.ag-esp-head{display:flex;justify-content:space-between;align-items:flex-start;gap:20px;flex-wrap:wrap;}
.ag-esp-logout{flex-shrink:0;}
.ag-guide-video{position:relative;width:100%;max-width:560px;margin:24px auto 0;aspect-ratio:16/9;border-radius:16px;overflow:hidden;border:1px solid rgba(212,180,92,.3);background:#000;}
.ag-guide-video iframe,.ag-guide-video video{position:absolute;inset:0;width:100%;height:100%;border:0;}
.ag-guide-novid{margin-top:22px;padding:16px 20px;background:rgba(212,180,92,.08);border:1px solid rgba(212,180,92,.25);border-radius:14px;color:var(--color-text-soft);}
.ag-guide-steps{display:flex;flex-direction:column;gap:14px;margin-top:26px;max-width:760px;}
.ag-guide-step{display:flex;gap:16px;align-items:flex-start;padding:18px 20px;background:rgba(255,255,255,.03);border:1px solid rgba(212,180,92,.16);border-radius:14px;}
.ag-guide-step__n{flex-shrink:0;width:38px;height:38px;display:flex;align-items:center;justify-content:center;border-radius:50%;background:linear-gradient(135deg,#D4B45C,#F37A1F);color:#10100a;font-weight:800;font-size:1.05rem;}
.ag-guide-step strong{color:#fff;display:block;margin-bottom:3px;}
.ag-guide-step p{color:var(--color-text-soft);font-size:.92rem;line-height:1.55;margin:0;}
.ag-guide-cta{display:flex;flex-wrap:wrap;gap:14px;margin-top:26px;}
.ag-guide-note{margin-top:16px;color:var(--color-text-muted);font-size:.88rem;}
.ag-guide-tips{margin-top:26px;padding:22px 24px;background:rgba(212,180,92,.06);border:1px solid rgba(212,180,92,.2);border-radius:16px;max-width:760px;}
.ag-guide-tips h3{color:#fff;font-family:var(--font-serif);font-size:1.15rem;margin:0 0 12px;}
.ag-guide-tips ul{list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:9px;}
.ag-guide-tips li{position:relative;padding-left:24px;color:var(--color-text-soft);line-height:1.5;}
.ag-guide-tips li::before{content:'✅';position:absolute;left:0;}
@media(max-width:768px){#ag-main-content.ag-guide > .ag-section:first-child{padding-top:160px;}.ag-guide-cta .ag-btn-gold,.ag-guide-cta .ag-btn-outline{width:100%;justify-content:center;text-align:center;}}
</style>
<script>
(function(){
	document.querySelectorAll('.ag-guide-copy').forEach(function(b){
		b.addEventListener('click',function(){
			var t=b.getAttribute('data-copy'); if(!t) return;
			navigator.clipboard.writeText(t).then(function(){ var o=b.textContent; b.textContent='✓ Copié'; setTimeout(function(){ b.textContent=o; },1500); });
		});
	});
})();
</script>

<?php get_footer(); ?>
