<?php
/**
 * Template Name: Connexion
 *
 * Connexion (wp_signon) + inscription libre (email/mot de passe) + bouton Google.
 * Le traitement des POST et les redirections sont gérés dans inc/ag-espaces.php
 * (avant tout affichage).
 */
get_header();

$ag_msg      = isset( $_GET['login'] ) ? sanitize_key( $_GET['login'] ) : '';
$ag_reg      = isset( $_GET['reg'] ) ? sanitize_key( $_GET['reg'] ) : '';
$ag_tab      = ( isset( $_GET['tab'] ) && 'inscription' === $_GET['tab'] ) || '' !== $ag_reg ? 'inscription' : 'connexion';
$ag_redirect = isset( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : '';
$ag_gid      = function_exists( 'ag_google_client_id' ) ? ag_google_client_id() : '';

$login_errs = array(
	'failed' => 'Identifiants incorrects. Réessaie ou réinitialise ton mot de passe.',
	'nonce'  => 'Session expirée, merci de réessayer.',
	'google' => 'La connexion Google a échoué. Réessaie, ou connecte-toi par email.',
);
$reg_errs = array(
	'nonce'  => 'Session expirée, merci de réessayer.',
	'spam'   => 'Inscription refusée (protection anti-robot). Réessaie.',
	'email'  => 'Adresse email invalide.',
	'pwd'    => 'Le mot de passe doit faire au moins 8 caractères.',
	'match'  => 'Les deux mots de passe ne correspondent pas.',
	'exists' => 'Un compte existe déjà avec cet email — connecte-toi plutôt.',
	'fail'   => 'Création du compte impossible. Réessaie dans un instant.',
);
?>
<main id="ag-main-content">
	<section class="ag-section ag-section--graphite ag-login">
		<div class="ag-container ag-container--narrow ag-login__box">
			<span class="ag-tag">Espace membre</span>
			<h1 class="ag-section__title">Bienvenue</h1>
			<p class="ag-section__desc">La façon la plus simple : connecte-toi en <strong>1 clic</strong> avec Google. (Ou par email si tu préfères.)</p>

			<?php if ( $ag_gid ) : ?>
				<div class="ag-auth-google-hero">
					<p class="ag-auth-google-hero__lbl">Recommandé — connexion en 1 clic, rien à retenir</p>
					<div id="g_id_onload"
						data-client_id="<?php echo esc_attr( $ag_gid ); ?>"
						data-login_uri="<?php echo esc_url( admin_url( 'admin-post.php?action=ag_google_login' ) ); ?>"
						data-ux_mode="redirect"
						data-auto_prompt="false"></div>
					<div class="g_id_signin" data-type="standard" data-theme="filled_black" data-text="continue_with" data-shape="pill" data-logo_alignment="center" data-size="large" data-width="340"></div>
					<script src="https://accounts.google.com/gsi/client" async defer></script>
				</div>
				<div class="ag-auth-or"><span>ou par email</span></div>
			<?php endif; ?>

			<div class="ag-auth-tabs" role="tablist">
				<button type="button" class="ag-auth-tab<?php echo 'connexion' === $ag_tab ? ' is-active' : ''; ?>" data-tab="connexion" role="tab">Se connecter</button>
				<button type="button" class="ag-auth-tab<?php echo 'inscription' === $ag_tab ? ' is-active' : ''; ?>" data-tab="inscription" role="tab">Créer un compte</button>
			</div>

			<!-- Panneau : Connexion -->
			<div class="ag-auth-panel" data-panel="connexion"<?php echo 'connexion' === $ag_tab ? '' : ' hidden'; ?>>
				<?php if ( isset( $login_errs[ $ag_msg ] ) ) : ?>
					<div class="ag-login-alert ag-login-alert--err"><?php echo esc_html( $login_errs[ $ag_msg ] ); ?></div>
				<?php endif; ?>
				<form class="ag-form" method="post" action="<?php echo esc_url( home_url( '/connexion' ) ); ?>">
					<?php wp_nonce_field( 'ag_login', 'ag_login_nonce' ); ?>
					<input type="hidden" name="ag_login_submit" value="1">
					<?php if ( $ag_redirect ) : ?><input type="hidden" name="redirect_to" value="<?php echo esc_attr( $ag_redirect ); ?>"><?php endif; ?>
					<div class="ag-form__group"><label for="ag-log">Email</label><input type="text" id="ag-log" name="log" required autocomplete="username" placeholder="ton@email.com"></div>
					<div class="ag-form__group"><label for="ag-pwd">Mot de passe</label><input type="password" id="ag-pwd" name="pwd" required autocomplete="current-password" placeholder="••••••••"></div>
					<label class="ag-login-remember"><input type="checkbox" name="rememberme" value="1"> Se souvenir de moi</label>
					<button type="submit" class="ag-btn-gold" style="width:100%;justify-content:center;">Se connecter →</button>
				</form>
				<p class="ag-login-links"><a href="<?php echo esc_url( home_url( '/mot-de-passe?action=lost' ) ); ?>">Mot de passe oublié ?</a></p>
				<p class="ag-login-note">Pas encore de compte ? <a href="#" class="ag-auth-switch" data-tab="inscription">Crée-le ici</a> ou rejoins le <a href="<?php echo esc_url( home_url( '/ambassadeurs' ) ); ?>">programme commercial</a>.</p>
			</div>

			<!-- Panneau : Inscription -->
			<div class="ag-auth-panel" data-panel="inscription"<?php echo 'inscription' === $ag_tab ? '' : ' hidden'; ?>>
				<?php if ( isset( $reg_errs[ $ag_reg ] ) ) : ?>
					<div class="ag-login-alert ag-login-alert--err"><?php echo esc_html( $reg_errs[ $ag_reg ] ); ?></div>
				<?php endif; ?>
				<form class="ag-form" method="post" action="<?php echo esc_url( home_url( '/connexion' ) ); ?>">
					<?php wp_nonce_field( 'ag_register', 'ag_register_nonce' ); ?>
					<input type="hidden" name="ag_register_submit" value="1">
					<?php if ( $ag_redirect ) : ?><input type="hidden" name="redirect_to" value="<?php echo esc_attr( $ag_redirect ); ?>"><?php endif; ?>
					<div class="ag-form__group"><label for="reg-name">Ton nom</label><input type="text" id="reg-name" name="reg_name" autocomplete="name" placeholder="Prénom Nom"></div>
					<div class="ag-form__group"><label for="reg-email">Email *</label><input type="email" id="reg-email" name="reg_email" required autocomplete="email" placeholder="ton@email.com"></div>
					<div class="ag-form__group"><label for="reg-pwd">Mot de passe * <small>(8 caractères min.)</small></label><input type="password" id="reg-pwd" name="reg_pwd" required minlength="8" autocomplete="new-password" placeholder="••••••••"></div>
					<div class="ag-form__group"><label for="reg-pwd2">Confirme le mot de passe *</label><input type="password" id="reg-pwd2" name="reg_pwd2" required minlength="8" autocomplete="new-password" placeholder="••••••••"></div>
					<div class="ag-hp" aria-hidden="true"><label>Ne pas remplir<input type="text" name="ag_hp" tabindex="-1" autocomplete="off"></label></div>
					<button type="submit" class="ag-btn-gold" style="width:100%;justify-content:center;">Créer mon compte →</button>
				</form>
				<p class="ag-login-note">En créant ton compte, tu accèdes à ton <strong>espace client</strong> (suivi de projet). Déjà inscrit ? <a href="#" class="ag-auth-switch" data-tab="connexion">Connecte-toi</a>.</p>
			</div>
		</div>
	</section>
</main>

<style>
.ag-login{min-height:78vh;display:flex;align-items:center;}
.ag-login__box{max-width:480px;}
.ag-auth-tabs{display:flex;gap:8px;margin:22px 0 20px;padding:6px;background:rgba(255,255,255,.04);border:1px solid rgba(212,180,92,.18);border-radius:14px;}
.ag-auth-tab{flex:1;padding:12px 10px;border:none;background:transparent;color:var(--color-text-soft);font-weight:700;font-size:.95rem;border-radius:10px;cursor:pointer;transition:background .2s,color .2s;}
.ag-auth-tab.is-active{background:linear-gradient(135deg,#D4B45C,#F37A1F);color:#10100a;}
.ag-auth-google{display:flex;justify-content:center;margin-bottom:8px;min-height:44px;}
.ag-auth-google-hero{display:flex;flex-direction:column;align-items:center;gap:12px;margin:22px 0 6px;padding:22px 18px;background:linear-gradient(160deg,rgba(212,180,92,.16),rgba(243,122,31,.06));border:1px solid rgba(212,180,92,.5);border-radius:18px;box-shadow:0 8px 30px rgba(0,0,0,.25);}
.ag-auth-google-hero__lbl{margin:0;color:#e8c766;font-weight:700;font-size:.92rem;text-align:center;}
.ag-auth-or{display:flex;align-items:center;gap:14px;margin:18px 0;color:var(--color-text-muted);font-size:.82rem;}
.ag-auth-or::before,.ag-auth-or::after{content:"";flex:1;height:1px;background:rgba(212,180,92,.2);}
.ag-auth-panel[hidden]{display:none;}
.ag-login .ag-form__group{position:relative;margin-bottom:16px;}
.ag-login .ag-form__group label{position:static;top:auto;left:auto;display:block;font-size:.88rem;font-weight:600;color:var(--color-text-soft);margin-bottom:8px;text-transform:none;letter-spacing:normal;pointer-events:auto;}
.ag-login .ag-form__group label small{color:var(--color-text-muted);font-weight:400;}
.ag-login .ag-form__group input{padding:14px 18px;}
.ag-login-alert{padding:12px 16px;border-radius:10px;margin:0 0 18px;font-size:.95rem;}
.ag-login-alert--err{background:rgba(179,45,46,.14);border:1px solid rgba(179,45,46,.4);color:#ffb3b3;}
.ag-login-remember{display:flex;align-items:center;gap:8px;color:var(--color-text-soft);font-size:.9rem;margin:0 0 18px;}
.ag-login-links{margin-top:16px;}
.ag-login-links a,.ag-login-note a{color:var(--color-gold);}
.ag-login-note{margin-top:22px;color:var(--color-text-muted);font-size:.85rem;line-height:1.6;}
.ag-hp{position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden;}
</style>
<script>
(function(){
	var tabs   = document.querySelectorAll('.ag-auth-tab');
	var panels = document.querySelectorAll('.ag-auth-panel');
	function show(tab){
		tabs.forEach(function(t){ t.classList.toggle('is-active', t.getAttribute('data-tab')===tab); });
		panels.forEach(function(p){ if(p.getAttribute('data-panel')===tab){ p.removeAttribute('hidden'); } else { p.setAttribute('hidden',''); } });
	}
	tabs.forEach(function(t){ t.addEventListener('click', function(){ show(t.getAttribute('data-tab')); }); });
	document.querySelectorAll('.ag-auth-switch').forEach(function(a){ a.addEventListener('click', function(e){ e.preventDefault(); show(a.getAttribute('data-tab')); }); });
})();
</script>

<?php get_footer(); ?>
