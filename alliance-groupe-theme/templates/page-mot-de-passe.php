<?php
/**
 * Template Name: Mot de passe
 *
 * Page sur-mesure (sans WordPress) : définir / réinitialiser son mot de passe
 * via une clé sécurisée, et demander une réinitialisation (mot de passe oublié).
 * Le traitement des POST est dans inc/ag-espaces.php (avant affichage).
 */
get_header();

$ag_key    = isset( $_GET['key'] ) ? sanitize_text_field( wp_unslash( $_GET['key'] ) ) : '';
$ag_login  = isset( $_GET['login'] ) ? sanitize_text_field( wp_unslash( $_GET['login'] ) ) : '';
$ag_pw     = isset( $_GET['pw'] ) ? sanitize_key( $_GET['pw'] ) : '';
$ag_lost   = isset( $_GET['lost'] ) ? sanitize_key( $_GET['lost'] ) : '';

$ag_valid_key = false;
if ( $ag_key && $ag_login ) {
	$ag_check = check_password_reset_key( $ag_key, $ag_login );
	$ag_valid_key = ! is_wp_error( $ag_check );
}
?>
<main id="ag-main-content">
	<section class="ag-section ag-section--graphite ag-pw">
		<div class="ag-container ag-container--narrow ag-pw__box">

			<?php if ( $ag_valid_key ) : ?>
				<span class="ag-tag">Sécurité</span>
				<h1 class="ag-section__title">Choisis ton mot de passe</h1>
				<p class="ag-section__desc">Crée un mot de passe pour accéder à ton espace.</p>

				<?php if ( 'short' === $ag_pw ) : ?><div class="ag-pw-alert ag-pw-alert--err">8 caractères minimum.</div><?php endif; ?>
				<?php if ( 'mismatch' === $ag_pw ) : ?><div class="ag-pw-alert ag-pw-alert--err">Les deux mots de passe ne correspondent pas.</div><?php endif; ?>

				<form class="ag-form" method="post" action="<?php echo esc_url( home_url( '/mot-de-passe' ) ); ?>">
					<?php wp_nonce_field( 'ag_setpass', 'ag_setpass_nonce' ); ?>
					<input type="hidden" name="ag_setpass_submit" value="1">
					<input type="hidden" name="key" value="<?php echo esc_attr( $ag_key ); ?>">
					<input type="hidden" name="login" value="<?php echo esc_attr( $ag_login ); ?>">
					<div class="ag-form__group"><label for="ag-p1">Nouveau mot de passe</label><input type="password" id="ag-p1" name="pass1" required minlength="8" autocomplete="new-password" placeholder="8 caractères min."></div>
					<div class="ag-form__group"><label for="ag-p2">Confirme le mot de passe</label><input type="password" id="ag-p2" name="pass2" required minlength="8" autocomplete="new-password" placeholder="Retape-le"></div>
					<button type="submit" class="ag-btn-gold" style="width:100%;justify-content:center;">Enregistrer et continuer →</button>
				</form>

			<?php elseif ( ( $ag_key && $ag_login ) || 'expired' === $ag_pw ) : ?>
				<span class="ag-tag">Lien expiré</span>
				<h1 class="ag-section__title">Ce lien n'est plus valide</h1>
				<p class="ag-section__desc">Pas de souci : redemande un lien et tu en recevras un nouveau par email.</p>
				<a href="<?php echo esc_url( home_url( '/mot-de-passe?action=lost' ) ); ?>" class="ag-btn-gold">Recevoir un nouveau lien →</a>

			<?php elseif ( 'sent' === $ag_lost ) : ?>
				<span class="ag-tag">Email envoyé</span>
				<h1 class="ag-section__title">Vérifie ta boîte mail 📬</h1>
				<p class="ag-section__desc">Si un compte existe avec cet email, tu viens de recevoir un lien pour choisir un nouveau mot de passe. Pense à regarder dans les spams.</p>
				<a href="<?php echo esc_url( home_url( '/connexion' ) ); ?>" class="ag-btn-outline">Retour à la connexion</a>

			<?php else : ?>
				<span class="ag-tag">Mot de passe oublié</span>
				<h1 class="ag-section__title">Réinitialiser mon mot de passe</h1>
				<p class="ag-section__desc">Entre ton email : on t'envoie un lien sécurisé pour en choisir un nouveau.</p>
				<form class="ag-form" method="post" action="<?php echo esc_url( home_url( '/mot-de-passe' ) ); ?>">
					<?php wp_nonce_field( 'ag_lost', 'ag_lost_nonce' ); ?>
					<input type="hidden" name="ag_lost_submit" value="1">
					<div class="ag-form__group"><label for="ag-em">Email</label><input type="email" id="ag-em" name="user_email" required autocomplete="email" placeholder="ton@email.com"></div>
					<button type="submit" class="ag-btn-gold" style="width:100%;justify-content:center;">Envoyer le lien →</button>
				</form>
				<p class="ag-pw-links"><a href="<?php echo esc_url( home_url( '/connexion' ) ); ?>">← Retour à la connexion</a></p>
			<?php endif; ?>

		</div>
	</section>
</main>

<style>
.ag-pw{min-height:78vh;display:flex;align-items:center;}
.ag-pw__box{max-width:480px;}
.ag-pw .ag-form__group{position:relative;margin-bottom:16px;}
.ag-pw .ag-form__group label{position:static;top:auto;left:auto;display:block;font-size:.88rem;font-weight:600;color:var(--color-text-soft);margin-bottom:8px;text-transform:none;letter-spacing:normal;pointer-events:auto;}
.ag-pw .ag-form__group input{padding:14px 18px;}
.ag-pw-alert{padding:12px 16px;border-radius:10px;margin:0 0 18px;font-size:.95rem;}
.ag-pw-alert--err{background:rgba(179,45,46,.14);border:1px solid rgba(179,45,46,.4);color:#ffb3b3;}
.ag-pw-links{margin-top:18px;}
.ag-pw-links a{color:var(--color-gold);}
</style>

<?php get_footer(); ?>
