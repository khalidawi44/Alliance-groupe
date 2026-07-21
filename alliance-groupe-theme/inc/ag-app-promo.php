<?php
/**
 * AG Annonces / Promos in-app — un bandeau que TU contrôles depuis wp-admin
 * et qui apparaît automatiquement dans les apps (et/ou sur le site), SANS que
 * personne ait besoin de réinstaller. Grâce au service worker « réseau d'abord »,
 * le message est récupéré frais à chaque ouverture de l'app.
 *
 * Public visé : tout le monde (site + apps), apps installées seulement, ou app
 * Ambassadeurs uniquement. Chaque enregistrement réinitialise les fermetures
 * (tout le monde revoit la nouvelle promo).
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ---------------------------------------------------------------- Réglages */
add_action( 'admin_init', function () {
	if ( isset( $_POST['ag_promo_save'] ) && check_admin_referer( 'ag_promo' ) ) {
		$prev_on  = get_option( 'ag_promo_on', '0' );
		$prev_msg = get_option( 'ag_promo_msg', '' );
		update_option( 'ag_promo_on', isset( $_POST['ag_promo_on'] ) ? '1' : '0' );
		update_option( 'ag_promo_msg', sanitize_text_field( wp_unslash( $_POST['ag_promo_msg'] ?? '' ) ) );
		update_option( 'ag_promo_link_label', sanitize_text_field( wp_unslash( $_POST['ag_promo_link_label'] ?? '' ) ) );
		update_option( 'ag_promo_link_url', esc_url_raw( wp_unslash( $_POST['ag_promo_link_url'] ?? '' ) ) );
		$aud = sanitize_text_field( wp_unslash( $_POST['ag_promo_audience'] ?? 'all' ) );
		update_option( 'ag_promo_audience', in_array( $aud, array( 'all', 'apps', 'amb' ), true ) ? $aud : 'all' );
		// Nouvelle promo (message changé ou réactivée) → on incrémente l'ID pour que tout le monde la revoie.
		if ( $prev_msg !== get_option( 'ag_promo_msg', '' ) || ( '1' === get_option( 'ag_promo_on' ) && '1' !== $prev_on ) || isset( $_POST['ag_promo_reset'] ) ) {
			update_option( 'ag_promo_id', (int) get_option( 'ag_promo_id', 0 ) + 1 );
		}
	}
} );
add_action( 'admin_menu', function () {
	add_submenu_page( 'ag-social', 'Annonce / Promo', '📢 Promo app', 'manage_options', 'ag-app-promo', 'ag_promo_render' );
}, 20 );
if ( ! function_exists( 'ag_promo_render' ) ) {
	function ag_promo_render() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$on  = '1' === get_option( 'ag_promo_on', '0' );
		$aud = get_option( 'ag_promo_audience', 'all' );
		echo '<div class="wrap"><h1>📢 Annonce / Promo dans les apps</h1>';
		echo '<p style="max-width:820px;color:#50575e;">Affiche un bandeau (offre, info, nouveauté) <strong>en haut</strong> du site et des apps. Dès que tu enregistres, il apparaît <strong>automatiquement</strong> à la prochaine ouverture — <strong>aucune réinstallation</strong>. Idéal pour les <strong>offres promotionnelles</strong>.</p>';
		echo '<form method="post"><table class="form-table"><tbody>';
		echo '<tr><th>Activer le bandeau</th><td><label><input type="checkbox" name="ag_promo_on" ' . checked( $on, true, false ) . '> Afficher la promo</label></td></tr>';
		echo '<tr><th>Message</th><td><input type="text" name="ag_promo_msg" value="' . esc_attr( get_option( 'ag_promo_msg', '' ) ) . '" style="width:560px" placeholder="🔥 -20% sur les Sites Express jusqu’à dimanche !"></td></tr>';
		echo '<tr><th>Texte du bouton</th><td><input type="text" name="ag_promo_link_label" value="' . esc_attr( get_option( 'ag_promo_link_label', '' ) ) . '" style="width:240px" placeholder="J’en profite"></td></tr>';
		echo '<tr><th>Lien du bouton</th><td><input type="text" name="ag_promo_link_url" value="' . esc_attr( get_option( 'ag_promo_link_url', '' ) ) . '" style="width:560px" placeholder="https://alliancegroupe-inc.com/sites-express"></td></tr>';
		echo '<tr><th>Pour qui ?</th><td><select name="ag_promo_audience">';
		foreach ( array( 'all' => 'Tout le monde (site + apps)', 'apps' => 'Apps installées seulement', 'amb' => 'App Ambassadeurs uniquement' ) as $k => $lab ) {
			echo '<option value="' . esc_attr( $k ) . '" ' . selected( $aud, $k, false ) . '>' . esc_html( $lab ) . '</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th>Re-montrer à tous</th><td><label><input type="checkbox" name="ag_promo_reset"> Forcer tout le monde à revoir le bandeau (même ceux qui l’ont fermé)</label></td></tr>';
		echo '</tbody></table>';
		wp_nonce_field( 'ag_promo' );
		echo '<button name="ag_promo_save" value="1" class="button button-primary">Enregistrer</button></form>';
		echo '<p style="margin-top:14px;color:#50575e;">💡 Pour des notifications qui arrivent <em>même app fermée</em> (vraies notifs push), dis-le-moi : c’est une étape suivante (clé VAPID + abonnements).</p>';
		echo '</div>';
	}
}

/* ---------------------------------------------------------------- Bandeau (front) */
add_action( 'wp_footer', function () {
	if ( is_admin() ) return;
	if ( '1' !== get_option( 'ag_promo_on', '0' ) ) return;
	$msg = trim( (string) get_option( 'ag_promo_msg', '' ) );
	if ( '' === $msg ) return;
	$aud   = get_option( 'ag_promo_audience', 'all' );
	$id    = (int) get_option( 'ag_promo_id', 1 );
	$label = trim( (string) get_option( 'ag_promo_link_label', '' ) );
	$url   = trim( (string) get_option( 'ag_promo_link_url', '' ) );
	?>
	<style>
	.ag-promo{display:none;position:relative;z-index:99994;align-items:center;gap:10px;justify-content:center;flex-wrap:wrap;
		padding:9px 40px 9px 14px;background:linear-gradient(135deg,#D4B45C,#F37A1F);color:#10100a;font-family:-apple-system,Arial,sans-serif;font-size:13.5px;font-weight:700;text-align:center}
	.ag-promo a{display:inline-block;padding:5px 12px;border-radius:999px;background:rgba(0,0,0,.82);color:#fff;text-decoration:none;font-size:12.5px}
	.ag-promo__x{position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:0;color:#10100a;font-size:18px;cursor:pointer;line-height:1}
	html.ag-amb-app .ag-promo{margin-bottom:0}
	</style>
	<div class="ag-promo" id="ag-promo" data-aud="<?php echo esc_attr( $aud ); ?>" data-id="<?php echo (int) $id; ?>">
		<span><?php echo esc_html( $msg ); ?></span>
		<?php if ( $label && $url ) : ?><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a><?php endif; ?>
		<button class="ag-promo__x" aria-label="Fermer" onclick="agPromoClose()">×</button>
	</div>
	<script>
	(function(){
		var el = document.getElementById('ag-promo'); if(!el) return;
		var aud = el.getAttribute('data-aud'), id = el.getAttribute('data-id');
		var sa = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
		var which=''; try{ which = new URLSearchParams(location.search).get('ag_app') || sessionStorage.getItem('ag_app') || ''; }catch(e){}
		// Filtre d'audience.
		if (aud === 'apps' && !sa) return;
		if (aud === 'amb' && !(sa && which === 'amb')) return;
		// Déjà fermé CETTE promo ?
		try{ if (localStorage.getItem('ag_promo_seen') === id) return; }catch(e){}
		// Place le bandeau tout en haut du body.
		document.addEventListener('DOMContentLoaded', function(){
			if (el.parentNode !== document.body) document.body.insertBefore(el, document.body.firstChild);
			else document.body.insertBefore(el, document.body.firstChild);
			el.style.display='flex';
		});
		window.agPromoClose = function(){ try{ localStorage.setItem('ag_promo_seen', id); }catch(e){} el.style.display='none'; };
	})();
	</script>
	<?php
}, 4 );
