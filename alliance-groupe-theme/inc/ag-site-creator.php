<?php
/**
 * AG Site Creator — « Créez votre site » (section « Mon métier n'est pas là »).
 *
 * Le visiteur saisit nom du business / métier / slogan / couleur / contact.
 * On capture le lead (push + CRM) puis on l'envoie payer le pack Business
 * (149 €, lien PayPal `ag_stripe_business_url`). Au paiement vérifié
 * (`ag_paypal_payment_verified`), on génère un ZIP PERSONNALISÉ basé sur le
 * thème Avocat Business (le plus beau) avec un `ag-prefill.json` qui
 * pré-remplit nom/métier/slogan/couleur, et on l'envoie au client.
 *
 * Côté admin : liste des demandes + bouton « Générer/Télécharger le ZIP »
 * pour livrer à la main si besoin.
 *
 * Shortcode : [ag_site_creator]
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Prix Business (par défaut 149 €) — aligné sur la licence. */
function ag_creator_price() {
	$p = (int) get_option( 'ag_licence_price_business', 149 );
	return $p > 0 ? $p : 149;
}

/** Lien de paiement PayPal Business. */
function ag_creator_pay_url() {
	return (string) get_option( 'ag_stripe_business_url', '' );
}

/** Récupère la liste des demandes (option, capée). */
function ag_creator_reqs() {
	$r = get_option( 'ag_site_creator_reqs', array() );
	return is_array( $r ) ? $r : array();
}

/** Sauve la liste des demandes (max 300, plus récentes en tête). */
function ag_creator_save_reqs( $reqs ) {
	if ( count( $reqs ) > 300 ) {
		$reqs = array_slice( $reqs, 0, 300 );
	}
	update_option( 'ag_site_creator_reqs', $reqs, false );
}

/* ============================================================
   FORMULAIRE (shortcode)
   ============================================================ */
function ag_site_creator_shortcode() {
	$price = ag_creator_price();
	ob_start();
	?>
	<div class="ag-creator" id="ag-creator">
		<form class="ag-creator__form" id="ag-creator-form" autocomplete="off">
			<div class="ag-creator__grid">
				<label class="ag-creator__field">
					<span>Nom de votre entreprise *</span>
					<input type="text" name="business" required maxlength="80" placeholder="Ex. Maison Martin">
				</label>
				<label class="ag-creator__field">
					<span>Votre métier *</span>
					<input type="text" name="metier" required maxlength="60" placeholder="Ex. Boucherie, Plombier, Fleuriste…">
				</label>
				<label class="ag-creator__field ag-creator__field--full">
					<span>Votre slogan / phrase d'accroche</span>
					<input type="text" name="slogan" maxlength="140" placeholder="Ex. Le goût du vrai, depuis 1998.">
				</label>
				<label class="ag-creator__field">
					<span>Couleur principale</span>
					<input type="color" name="accent" value="#c9a96e">
				</label>
				<label class="ag-creator__field">
					<span>Email *</span>
					<input type="email" name="email" required placeholder="vous@exemple.fr">
				</label>
				<label class="ag-creator__field">
					<span>Téléphone</span>
					<input type="tel" name="tel" maxlength="24" placeholder="06 12 34 56 78">
				</label>
			</div>
			<input type="hidden" name="ag_creator_nonce" value="<?php echo esc_attr( wp_create_nonce( 'ag_site_creator' ) ); ?>">
			<button type="submit" class="ag-btn-gold ag-creator__btn">✨ Créer mon site — <?php echo esc_html( $price ); ?>€</button>
			<p class="ag-creator__hint">Design premium (base « Avocat Business », le plus élégant), personnalisé pour votre métier. Paiement unique, sans abonnement. Livraison du site (ZIP prêt à installer) par email après paiement.</p>
			<div class="ag-creator__result" id="ag-creator-result" hidden></div>
		</form>
	</div>
	<style>
	.ag-creator{max-width:820px;margin:0 auto}
	.ag-creator__grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
	.ag-creator__field{display:flex;flex-direction:column;gap:6px}
	.ag-creator__field--full{grid-column:1/-1}
	.ag-creator__field span{color:#cdd0d6;font-size:.86rem;font-weight:600}
	.ag-creator__field input{background:rgba(0,0,0,.35);border:1px solid rgba(212,180,92,.3);border-radius:10px;padding:12px 14px;color:#fff;font-size:1rem}
	.ag-creator__field input[type=color]{height:48px;padding:4px;cursor:pointer}
	.ag-creator__btn{margin-top:18px;width:100%;font-size:1.05rem;padding:16px}
	.ag-creator__hint{color:rgba(255,255,255,.6);font-size:.84rem;margin-top:12px;line-height:1.5;text-align:center}
	.ag-creator__result{margin-top:18px;border-radius:12px;padding:16px 18px;background:rgba(40,167,69,.12);border:1px solid rgba(40,167,69,.4);color:#d6ffe0;display:none}
	.ag-creator__result.is-show{display:block}
	.ag-creator__result a{color:#FFD23F;font-weight:700}
	@media(max-width:600px){.ag-creator__grid{grid-template-columns:1fr}}
	</style>
	<script>
	(function(){
		var f=document.getElementById('ag-creator-form'); if(!f) return;
		var res=document.getElementById('ag-creator-result');
		f.addEventListener('submit',function(e){
			e.preventDefault();
			var btn=f.querySelector('button[type=submit]'); btn.disabled=true; var lbl=btn.textContent; btn.textContent='⏳ Un instant…';
			var fd=new FormData(f); fd.append('action','ag_site_creator_submit');
			fetch('<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>',{method:'POST',body:fd,credentials:'same-origin'})
			.then(function(r){return r.json();})
			.then(function(j){
				btn.disabled=false; btn.textContent=lbl;
				if(j&&j.success){
					res.className='ag-creator__result is-show';
					res.innerHTML=j.data.html;
					if(j.data.pay){ setTimeout(function(){ window.location.href=j.data.pay; }, 1400); }
				} else {
					res.className='ag-creator__result is-show'; res.style.background='rgba(225,15,26,.12)'; res.style.borderColor='rgba(225,15,26,.4)'; res.style.color='#ffd6d6';
					res.textContent=(j&&j.data&&j.data.msg)?j.data.msg:'Erreur, réessayez.';
				}
			})
			.catch(function(){ btn.disabled=false; btn.textContent=lbl; res.className='ag-creator__result is-show'; res.textContent='Erreur réseau, réessayez.'; });
		});
	})();
	</script>
	<?php
	return ob_get_clean();
}
add_shortcode( 'ag_site_creator', 'ag_site_creator_shortcode' );

/* ============================================================
   AJAX : enregistrement de la demande + capture lead
   ============================================================ */
function ag_site_creator_submit() {
	if ( ! isset( $_POST['ag_creator_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ag_creator_nonce'] ) ), 'ag_site_creator' ) ) {
		wp_send_json_error( array( 'msg' => 'Session expirée, rechargez la page.' ) );
	}
	$business = isset( $_POST['business'] ) ? sanitize_text_field( wp_unslash( $_POST['business'] ) ) : '';
	$metier   = isset( $_POST['metier'] ) ? sanitize_text_field( wp_unslash( $_POST['metier'] ) ) : '';
	$slogan   = isset( $_POST['slogan'] ) ? sanitize_text_field( wp_unslash( $_POST['slogan'] ) ) : '';
	$accent   = isset( $_POST['accent'] ) ? sanitize_hex_color( wp_unslash( $_POST['accent'] ) ) : '';
	$email    = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$tel      = isset( $_POST['tel'] ) ? sanitize_text_field( wp_unslash( $_POST['tel'] ) ) : '';

	if ( '' === $business || '' === $metier || ! is_email( $email ) ) {
		wp_send_json_error( array( 'msg' => 'Merci de remplir le nom, le métier et un email valide.' ) );
	}

	$id   = uniqid( 'sc_', true );
	$reqs = ag_creator_reqs();
	array_unshift( $reqs, array(
		'id'       => $id,
		'token'    => wp_generate_password( 24, false ),
		'business' => $business,
		'metier'   => $metier,
		'slogan'   => $slogan,
		'accent'   => $accent ? $accent : '#c9a96e',
		'email'    => $email,
		'tel'      => $tel,
		'date'     => time(),
		'paid'     => 0,
		'txn'      => '',
		'zip'      => '',
	) );
	ag_creator_save_reqs( $reqs );

	// Notif équipe (SMS/Telegram) + CRM si dispo.
	if ( function_exists( 'ag_push' ) ) {
		ag_push( '🆕 Créateur de site', $business . ' — ' . $metier . "\n" . $email . ( $tel ? ' / ' . $tel : '' ) );
	}
	if ( function_exists( 'ag_prospect_capture' ) ) {
		ag_prospect_capture( array(
			'name'    => $business,
			'email'   => $email,
			'phone'   => $tel,
			'message' => 'Créateur de site — métier : ' . $metier . ( $slogan ? ' — ' . $slogan : '' ),
			'source'  => 'site-creator',
		) );
	}

	$pay = ag_creator_pay_url();
	$price = ag_creator_price();
	if ( $pay ) {
		$html = '<strong>Parfait, ' . esc_html( $business ) . ' !</strong><br>On vous redirige vers le paiement sécurisé (' . esc_html( $price ) . '€, paiement unique). <strong>Payez avec l\'email ' . esc_html( $email ) . '</strong> pour recevoir votre site personnalisé par retour de mail.<br><a href="' . esc_url( $pay ) . '">Aller au paiement →</a>';
		wp_send_json_success( array( 'html' => $html, 'pay' => $pay ) );
	}
	// Pas de lien de paiement configuré : on bascule en mode « on vous recontacte ».
	$html = '<strong>Merci ' . esc_html( $business ) . ' !</strong><br>Votre demande est enregistrée. Fabrizio vous recontacte très vite pour finaliser votre site sur-mesure, adapté à votre métier.';
	wp_send_json_success( array( 'html' => $html, 'pay' => '' ) );
}
add_action( 'wp_ajax_ag_site_creator_submit', 'ag_site_creator_submit' );
add_action( 'wp_ajax_nopriv_ag_site_creator_submit', 'ag_site_creator_submit' );

/* ============================================================
   GÉNÉRATION DU ZIP PERSONNALISÉ (base Avocat Business)
   ============================================================ */
/** Chemin du ZIP source du thème Avocat. */
function ag_creator_source_zip() {
	return get_stylesheet_directory() . '/assets/downloads/ag-starter-avocat.zip';
}

/**
 * Construit un ZIP personnalisé pour une demande : copie le thème Avocat et
 * injecte ag-starter-avocat/ag-prefill.json. Retourne array(path,url) ou WP_Error.
 */
function ag_creator_build_zip( $req ) {
	if ( ! class_exists( 'ZipArchive' ) ) {
		return new WP_Error( 'nozip', 'Extension ZipArchive PHP absente sur le serveur.' );
	}
	$src = ag_creator_source_zip();
	if ( ! file_exists( $src ) ) {
		return new WP_Error( 'nosrc', 'ZIP source du thème Avocat introuvable.' );
	}

	$up  = wp_upload_dir();
	$dir = trailingslashit( $up['basedir'] ) . 'ag-creator';
	if ( ! file_exists( $dir ) ) {
		wp_mkdir_p( $dir );
	}
	$token = ! empty( $req['token'] ) ? preg_replace( '/[^a-zA-Z0-9]/', '', $req['token'] ) : wp_generate_password( 24, false );
	$slug  = sanitize_title( $req['business'] ) ? sanitize_title( $req['business'] ) : 'site';
	$name  = 'site-' . $slug . '-' . $token . '.zip';
	$dest  = trailingslashit( $dir ) . $name;

	if ( ! copy( $src, $dest ) ) {
		return new WP_Error( 'copy', 'Copie du ZIP impossible.' );
	}

	$prefill = wp_json_encode( array(
		'business' => $req['business'],
		'metier'   => $req['metier'],
		'slogan'   => $req['slogan'],
		'accent'   => $req['accent'],
		'prefix'   => $req['metier'],
		'email'    => $req['email'],
		'tel'      => $req['tel'],
	) );

	$zip = new ZipArchive();
	if ( true !== $zip->open( $dest ) ) {
		@unlink( $dest ); // phpcs:ignore
		return new WP_Error( 'open', 'Ouverture du ZIP impossible.' );
	}
	$zip->addFromString( 'ag-starter-avocat/ag-prefill.json', $prefill );
	$zip->close();

	$url = trailingslashit( $up['baseurl'] ) . 'ag-creator/' . $name;
	return array( 'path' => $dest, 'url' => $url, 'name' => $name );
}

/* ============================================================
   LIVRAISON AUTO au paiement vérifié (149 € Business)
   ============================================================ */
add_action( 'ag_paypal_payment_verified', function ( $amount, $email, $txn = '', $type = '', $resource = array() ) {
	$amount   = (float) $amount;
	$business = (int) ag_creator_price();
	// On ne traite que les paiements au montant Business.
	if ( abs( $amount - $business ) > 0.5 ) {
		return;
	}
	$email = sanitize_email( (string) $email );
	$reqs  = ag_creator_reqs();
	$hit   = -1;
	foreach ( $reqs as $i => $r ) {
		if ( empty( $r['paid'] ) && isset( $r['email'] ) && strtolower( $r['email'] ) === strtolower( $email ) ) {
			$hit = $i;
			break;
		}
	}
	if ( $hit < 0 ) {
		return; // Aucun créateur en attente pour cet email (c'est peut-être une licence simple).
	}

	$built = ag_creator_build_zip( $reqs[ $hit ] );
	$reqs[ $hit ]['paid'] = 1;
	$reqs[ $hit ]['txn']  = (string) $txn;
	if ( ! is_wp_error( $built ) ) {
		$reqs[ $hit ]['zip'] = $built['url'];
	}
	ag_creator_save_reqs( $reqs );

	if ( is_wp_error( $built ) ) {
		if ( function_exists( 'ag_push' ) ) {
			ag_push( '⚠️ Créateur : ZIP non généré', $reqs[ $hit ]['business'] . ' — ' . $built->get_error_message() . ' (générer à la main : Outils > Créateur de site)' );
		}
		return;
	}

	// Email client avec le lien de téléchargement.
	$to   = $reqs[ $hit ]['email'];
	$subj = 'Votre site « ' . $reqs[ $hit ]['business'] . ' » est prêt 🎉';
	$body = "Bonjour,\n\nMerci pour votre confiance ! Votre site personnalisé (" . $reqs[ $hit ]['metier'] . ") est prêt.\n\n";
	$body .= "Téléchargez votre thème (ZIP) ici :\n" . $built['url'] . "\n\n";
	$body .= "Installation : WordPress > Apparence > Thèmes > Ajouter > Téléverser un thème > choisissez le ZIP > Installer > Activer. Votre nom, votre métier et vos couleurs sont déjà pré-remplis.\n\n";
	$body .= "Vous recevez aussi, séparément, votre clé de licence (pack Business activé).\n\nUne question ? Répondez à cet email.\n\nAlliance Groupe";
	if ( function_exists( 'ag_email_wrap' ) ) {
		wp_mail( $to, $subj, $body, array( 'Content-Type: text/plain; charset=UTF-8' ) );
	} else {
		wp_mail( $to, $subj, $body );
	}
	if ( function_exists( 'ag_push' ) ) {
		ag_push( '✅ Créateur : site livré', $reqs[ $hit ]['business'] . ' — ' . $reqs[ $hit ]['email'] );
	}
}, 20, 5 );

/* ============================================================
   ADMIN : liste des demandes + génération manuelle
   ============================================================ */
add_action( 'admin_menu', function () {
	add_submenu_page(
		'tools.php',
		'Créateur de site',
		'Créateur de site',
		'manage_options',
		'ag-site-creator',
		'ag_site_creator_admin'
	);
} );

function ag_site_creator_admin() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$reqs = ag_creator_reqs();
	echo '<div class="wrap"><h1>Créateur de site — demandes</h1>';
	if ( ! ag_creator_pay_url() ) {
		echo '<div class="notice notice-warning"><p>⚠️ Aucun <strong>lien de paiement Business</strong> configuré (Réglages → Liens de paiement, option PayPal Business). Tant qu\'il est vide, le formulaire enregistre la demande mais ne propose pas le paiement.</p></div>';
	}
	if ( empty( $reqs ) ) {
		echo '<p>Aucune demande pour l\'instant.</p></div>';
		return;
	}
	echo '<table class="widefat striped"><thead><tr><th>Date</th><th>Entreprise</th><th>Métier</th><th>Contact</th><th>Couleur</th><th>Payé</th><th>ZIP</th></tr></thead><tbody>';
	foreach ( $reqs as $r ) {
		$dl = wp_nonce_url( admin_url( 'admin-post.php?action=ag_creator_zip&id=' . rawurlencode( $r['id'] ) ), 'ag_creator_zip_' . $r['id'] );
		echo '<tr>';
		echo '<td>' . esc_html( wp_date( 'd/m/Y H:i', (int) $r['date'] ) ) . '</td>';
		echo '<td><strong>' . esc_html( $r['business'] ) . '</strong>' . ( $r['slogan'] ? '<br><small>' . esc_html( $r['slogan'] ) . '</small>' : '' ) . '</td>';
		echo '<td>' . esc_html( $r['metier'] ) . '</td>';
		echo '<td>' . esc_html( $r['email'] ) . ( $r['tel'] ? '<br>' . esc_html( $r['tel'] ) : '' ) . '</td>';
		echo '<td><span style="display:inline-block;width:16px;height:16px;border-radius:3px;vertical-align:middle;background:' . esc_attr( $r['accent'] ) . '"></span></td>';
		echo '<td>' . ( ! empty( $r['paid'] ) ? '✅' : '—' ) . '</td>';
		echo '<td><a class="button" href="' . esc_url( $dl ) . '">⬇ Générer / Télécharger</a></td>';
		echo '</tr>';
	}
	echo '</tbody></table></div>';
}

add_action( 'admin_post_ag_creator_zip', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Refusé.' );
	}
	$id = isset( $_GET['id'] ) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : '';
	check_admin_referer( 'ag_creator_zip_' . $id );
	$reqs = ag_creator_reqs();
	$req  = null;
	foreach ( $reqs as $r ) {
		if ( $r['id'] === $id ) {
			$req = $r;
			break;
		}
	}
	if ( ! $req ) {
		wp_die( 'Demande introuvable.' );
	}
	$built = ag_creator_build_zip( $req );
	if ( is_wp_error( $built ) ) {
		wp_die( esc_html( $built->get_error_message() ) );
	}
	header( 'Content-Type: application/zip' );
	header( 'Content-Disposition: attachment; filename="' . $built['name'] . '"' );
	header( 'Content-Length: ' . filesize( $built['path'] ) );
	readfile( $built['path'] ); // phpcs:ignore
	exit;
} );
