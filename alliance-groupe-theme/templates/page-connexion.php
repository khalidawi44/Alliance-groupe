<?php
/**
 * Template Name: Connexion
 *
 * Connexion sécurisée (wp_signon). Le traitement du POST + la redirection
 * des connectés sont gérés dans inc/ag-espaces.php (avant tout affichage).
 */
get_header();
$ag_msg = isset( $_GET['login'] ) ? sanitize_key( $_GET['login'] ) : '';
$ag_redirect = isset( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : '';
?>
<main id="ag-main-content">
	<section class="ag-section ag-section--graphite ag-login">
		<div class="ag-container ag-container--narrow ag-login__box">
			<span class="ag-tag">Espace membre</span>
			<h1 class="ag-section__title">Connexion</h1>
			<p class="ag-section__desc">Accède à ton espace client ou commercial.</p>

			<?php if ( 'failed' === $ag_msg ) : ?>
				<div class="ag-login-alert ag-login-alert--err">Identifiants incorrects. Réessaie ou réinitialise ton mot de passe.</div>
			<?php elseif ( 'nonce' === $ag_msg ) : ?>
				<div class="ag-login-alert ag-login-alert--err">Session expirée, merci de réessayer.</div>
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

			<p class="ag-login-links"><a href="<?php echo esc_url( wp_lostpassword_url() ); ?>">Mot de passe oublié ?</a></p>
			<p class="ag-login-note">Pas encore de compte ? Ton accès est créé automatiquement quand tu commandes un site (client) ou quand tu rejoins le <a href="<?php echo esc_url( home_url( '/ambassadeurs' ) ); ?>">programme commercial</a>. Tu reçois alors un email pour définir ton mot de passe.</p>
		</div>
	</section>
</main>

<style>
.ag-login{min-height:78vh;display:flex;align-items:center;}
.ag-login__box{max-width:480px;}
.ag-login .ag-form__group{position:relative;margin-bottom:16px;}
.ag-login .ag-form__group label{position:static;top:auto;left:auto;display:block;font-size:.88rem;font-weight:600;color:var(--color-text-soft);margin-bottom:8px;text-transform:none;letter-spacing:normal;pointer-events:auto;}
.ag-login .ag-form__group input{padding:14px 18px;}
.ag-login-alert{padding:12px 16px;border-radius:10px;margin:0 0 18px;font-size:.95rem;}
.ag-login-alert--err{background:rgba(179,45,46,.14);border:1px solid rgba(179,45,46,.4);color:#ffb3b3;}
.ag-login-remember{display:flex;align-items:center;gap:8px;color:var(--color-text-soft);font-size:.9rem;margin:0 0 18px;}
.ag-login-links{margin-top:16px;}
.ag-login-links a,.ag-login-note a{color:var(--color-gold);}
.ag-login-note{margin-top:22px;color:var(--color-text-muted);font-size:.85rem;line-height:1.6;}
</style>

<?php get_footer(); ?>
