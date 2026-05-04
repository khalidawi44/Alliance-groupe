<?php
/**
 * Shortcodes affichés sur les pages séparées (créées par AG_Fid_Pages).
 * Chaque shortcode rend la liste/le formulaire correspondant.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AG_Fid_Shortcodes {
	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) self::$instance = new self();
		return self::$instance;
	}
	private function __construct() {
		add_shortcode( 'ag_fid_combats',     array( $this, 'render_combats' ) );
		add_shortcode( 'ag_fid_evenements',  array( $this, 'render_evenements' ) );
		add_shortcode( 'ag_fid_groupes',     array( $this, 'render_groupes' ) );
		add_shortcode( 'ag_fid_actu',        array( $this, 'render_actu' ) );
		add_shortcode( 'ag_fid_petitions',   array( $this, 'render_petitions' ) );
		add_shortcode( 'ag_fid_signer',      array( $this, 'render_signer' ) );
		add_shortcode( 'ag_fid_don',         array( $this, 'render_don' ) );
		add_shortcode( 'ag_fid_adhesion',    array( $this, 'render_adhesion' ) );
		add_shortcode( 'ag_fid_compte',      array( $this, 'render_compte' ) );
		add_shortcode( 'ag_fid_manifeste',   array( $this, 'render_manifeste' ) );
		add_shortcode( 'ag_fid_mentions',    array( $this, 'render_mentions' ) );
		add_shortcode( 'ag_fid_rgpd',        array( $this, 'render_rgpd' ) );
	}

	private function render_cpt_grid( $cpt, $empty_msg ) {
		$q = new WP_Query( array( 'post_type' => $cpt, 'posts_per_page' => 30 ) );
		if ( ! $q->have_posts() ) {
			return '<p>' . esc_html( $empty_msg ) . '</p>';
		}
		ob_start();
		echo '<div class="ag-fid-grid">';
		while ( $q->have_posts() ) {
			$q->the_post();
			?>
			<article class="ag-fid-card">
				<?php if ( has_post_thumbnail() ) : ?>
					<div class="ag-fid-card__img"><?php the_post_thumbnail( 'medium' ); ?></div>
				<?php endif; ?>
				<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
				<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?></p>
			</article>
			<?php
		}
		echo '</div>';
		wp_reset_postdata();
		return ob_get_clean();
	}

	public function render_combats()    { return $this->render_cpt_grid( 'ag_combat', 'Aucun combat publié.' ); }
	public function render_evenements() { return $this->render_cpt_grid( 'ag_evenement', 'Aucun événement à venir.' ); }
	public function render_groupes()    { return $this->render_cpt_grid( 'ag_groupe', 'Aucun groupe local référencé.' ); }
	public function render_petitions()  { return $this->render_cpt_grid( 'ag_petition', 'Aucune pétition active.' ); }
	public function render_actu()       { return $this->render_cpt_grid( 'post', 'Aucun article.' ); }

	public function render_manifeste() {
		return '<div class="ag-fid-manifeste-text"><p>[Texte du manifeste — éditer cette page pour remplacer.]</p></div>';
	}

	public function render_signer() {
		ob_start(); ?>
		<form class="ag-fid-form" method="post">
			<?php wp_nonce_field( 'ag_fid_signer', 'ag_fid_signer_nonce' ); ?>
			<div class="ag-fid-form__row">
				<input type="text" name="prenom" placeholder="Prénom" required>
				<input type="text" name="nom" placeholder="Nom" required>
			</div>
			<input type="email" name="email" placeholder="Email" required>
			<input type="text" name="cp" placeholder="Code postal" required>
			<label><input type="checkbox" required> J'accepte le traitement RGPD de mes données.</label>
			<button type="submit" class="ag-fid-btn ag-fid-btn--primary">Je signe</button>
		</form>
		<?php
		return ob_get_clean();
	}

	public function render_don() {
		ob_start(); ?>
		<div class="ag-fid-don-grid">
			<?php foreach ( array( 5, 20, 50, 100, 250 ) as $a ) : ?>
				<a href="#" class="ag-fid-don-card">
					<span class="ag-fid-don-card__amount"><?php echo $a; ?>€</span>
					<span class="ag-fid-don-card__note">Coût réel : <?php echo round( $a * 0.34 ); ?>€ après réduction d'impôt 66%</span>
				</a>
			<?php endforeach; ?>
		</div>
		<p class="ag-fid-don-info">Reçu fiscal envoyé automatiquement par email.</p>
		<?php
		return ob_get_clean();
	}

	public function render_adhesion() {
		$cot = get_theme_mod( 'ag_fid_cotisation', '20' );
		ob_start(); ?>
		<div class="ag-fid-adhesion">
			<h3>Cotisation annuelle : <?php echo esc_html( $cot ); ?>€</h3>
			<p>Adhérer vous donne accès aux votes en AG, aux PV internes, à la liste des groupes locaux et au forum interne.</p>
			<form class="ag-fid-form" method="post">
				<?php wp_nonce_field( 'ag_fid_adhesion', 'ag_fid_adhesion_nonce' ); ?>
				<div class="ag-fid-form__row">
					<input type="text" name="prenom" placeholder="Prénom" required>
					<input type="text" name="nom" placeholder="Nom" required>
				</div>
				<input type="email" name="email" placeholder="Email" required>
				<input type="tel" name="tel" placeholder="Téléphone (facultatif)">
				<input type="text" name="adresse" placeholder="Adresse complète" required>
				<select name="role" required>
					<option value="ag_sympathisant">Sympathisant (gratuit)</option>
					<option value="ag_adherent">Adhérent (<?php echo esc_html( $cot ); ?>€)</option>
					<option value="ag_militant">Militant (<?php echo esc_html( $cot ); ?>€ + engagement)</option>
				</select>
				<label><input type="checkbox" required> J'accepte les statuts et le RGPD.</label>
				<button type="submit" class="ag-fid-btn ag-fid-btn--primary">Adhérer</button>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}

	public function render_compte() {
		if ( ! is_user_logged_in() ) {
			return '<p>Veuillez vous <a href="' . esc_url( wp_login_url( get_permalink() ) ) . '">connecter</a> pour accéder à votre espace.</p>';
		}
		$user = wp_get_current_user();
		$role_label = '';
		foreach ( $user->roles as $r ) {
			$role_label = wp_roles()->roles[ $r ]['name'] ?? $r;
			break;
		}
		ob_start(); ?>
		<div class="ag-fid-compte">
			<h3>Bonjour <?php echo esc_html( $user->display_name ); ?></h3>
			<p>Statut : <strong><?php echo esc_html( $role_label ); ?></strong></p>
			<ul>
				<li><a href="<?php echo esc_url( admin_url( 'profile.php' ) ); ?>">Modifier mon profil</a></li>
				<li><a href="<?php echo esc_url( home_url( '/petitions' ) ); ?>">Mes pétitions signées</a></li>
				<?php if ( current_user_can( 'read_private_posts' ) ) : ?>
					<li><a href="<?php echo esc_url( home_url( '/?post_type=ag_pv' ) ); ?>">PV & Comptes-rendus</a> <small>(adhérents)</small></li>
				<?php endif; ?>
				<li><a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>">Se déconnecter</a></li>
			</ul>
		</div>
		<?php
		return ob_get_clean();
	}

	public function render_mentions() { return '<p>[Mentions légales : raison sociale, SIRET, RNA, hébergeur, contact DPO. Édite cette page.]</p>'; }
	public function render_rgpd()     { return '<p>[Politique de confidentialité — texte standard RGPD pour associations à coller ici.]</p>'; }
}
