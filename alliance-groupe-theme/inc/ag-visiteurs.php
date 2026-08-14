<?php
/**
 * ag-visiteurs.php — Suivi de comportement des visiteurs « venus seuls » (inbound).
 *
 * But (demande Fabrice) : séparer les prospects que TU trouves (chasse Google
 * Places = outbound) de ceux qui arrivent SEULS sur le site (inbound), et avoir
 * un espace à part qui montre leur PARCOURS (pages vues, clics, comportement)
 * pour améliorer le site — et savoir lequel est devenu lead.
 *
 * 100 % maison (first-party, aucun tiers). RGPD : le traçage ne démarre QUE si
 * le visiteur a accepté les cookies « mesure d'audience » (système ag:consent
 * déjà en place). Les admins connectés ne sont pas suivis.
 *
 * Stockage : option `ag_visits` (plafonnée). Admin : Prospection → « 👣 Visiteurs ».
 *
 * @package Alliance_Groupe
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! defined( 'AG_VISITS_MAX' ) )      { define( 'AG_VISITS_MAX', 500 ); }  // nb de visiteurs conservés
if ( ! defined( 'AG_VISITS_EV_MAX' ) )   { define( 'AG_VISITS_EV_MAX', 40 ); } // nb d'événements par visiteur

/** Sources « inbound » : le visiteur est venu de lui-même sur le site. */
function ag_lead_inbound_sources() {
	return array( 'concierge-ia', 'devis-instant', 'refais-mon-site', 'tester-mon-site',
		'lead-guide-avocat', 'chat', 'tirage', 'audit', 'contact' );
}
/** Vrai si la source correspond à un lead venu seul (inbound). */
function ag_lead_is_inbound( $source ) {
	$source = (string) $source;
	if ( 0 === strpos( $source, 'template-' ) ) { return true; }
	return in_array( $source, ag_lead_inbound_sources(), true );
}

/** ID visiteur (cookie `ag_vid`), lisible côté serveur. */
function ag_visitor_id() {
	$v = isset( $_COOKIE['ag_vid'] ) ? preg_replace( '/[^a-zA-Z0-9]/', '', (string) $_COOKIE['ag_vid'] ) : '';
	return ( strlen( $v ) >= 8 && strlen( $v ) <= 40 ) ? $v : '';
}

/* ── Réception des événements (pageview / clic) ──────────────────────────── */
function ag_track_handler() {
	// Beacon depuis une page (souvent en cache) → pas de nonce fiable ; on
	// sécurise par validation stricte + plafonds + limite de débit par IP.
	$vid = isset( $_POST['vid'] ) ? preg_replace( '/[^a-zA-Z0-9]/', '', (string) wp_unslash( $_POST['vid'] ) ) : '';
	if ( strlen( $vid ) < 8 || strlen( $vid ) > 40 ) { wp_send_json_error(); }

	$rl = 'ag_trk_' . ( isset( $_SERVER['REMOTE_ADDR'] ) ? md5( (string) $_SERVER['REMOTE_ADDR'] ) : 'x' );
	$n  = (int) get_transient( $rl );
	if ( $n > 120 ) { wp_send_json_error(); } // max ~120 évènements / minute / IP
	set_transient( $rl, $n + 1, MINUTE_IN_SECONDS );

	$type = ( isset( $_POST['t'] ) && 'clk' === $_POST['t'] ) ? 'clk' : 'pv';
	$path = isset( $_POST['p'] ) ? sanitize_text_field( wp_unslash( $_POST['p'] ) ) : '';
	$path = mb_substr( $path, 0, 120 );
	$label = isset( $_POST['l'] ) ? sanitize_text_field( wp_unslash( $_POST['l'] ) ) : '';
	$label = mb_substr( $label, 0, 80 );

	$visits = (array) get_option( 'ag_visits', array() );
	if ( ! isset( $visits[ $vid ] ) ) {
		$visits[ $vid ] = array(
			'first' => time(),
			'iph'   => isset( $_SERVER['REMOTE_ADDR'] ) ? substr( md5( (string) $_SERVER['REMOTE_ADDR'] ), 0, 10 ) : '',
			'ev'    => array(),
			'lead'  => null,
		);
	}
	$visits[ $vid ]['last'] = time();
	$visits[ $vid ]['ev'][] = array( 't' => time(), 'k' => $type, 'p' => $path, 'l' => $label );
	// Plafond d'événements par visiteur.
	if ( count( $visits[ $vid ]['ev'] ) > AG_VISITS_EV_MAX ) {
		$visits[ $vid ]['ev'] = array_slice( $visits[ $vid ]['ev'], -AG_VISITS_EV_MAX );
	}
	// Plafond du nombre de visiteurs : on garde les plus récents.
	if ( count( $visits ) > AG_VISITS_MAX ) {
		uasort( $visits, function ( $a, $b ) { return ( $b['last'] ?? 0 ) <=> ( $a['last'] ?? 0 ); } );
		$visits = array_slice( $visits, 0, AG_VISITS_MAX, true );
	}
	update_option( 'ag_visits', $visits, false );
	wp_send_json_success();
}
add_action( 'wp_ajax_ag_track', 'ag_track_handler' );
add_action( 'wp_ajax_nopriv_ag_track', 'ag_track_handler' );

/* ── Lie le parcours au lead quand un prospect est créé ──────────────────── */
add_action( 'ag_prospect_added', function ( $rec ) {
	$vid = ag_visitor_id();
	if ( '' === $vid ) { return; }
	$visits = (array) get_option( 'ag_visits', array() );
	if ( ! isset( $visits[ $vid ] ) ) { return; }
	$visits[ $vid ]['lead'] = array(
		'name'   => isset( $rec['name'] ) ? $rec['name'] : '',
		'email'  => isset( $rec['email'] ) ? $rec['email'] : '',
		'phone'  => isset( $rec['phone'] ) ? $rec['phone'] : '',
		'source' => isset( $rec['source'] ) ? $rec['source'] : '',
		'ts'     => time(),
	);
	update_option( 'ag_visits', $visits, false );
}, 20 );

/* ── Traceur front-end (gated consentement, hors admin) ──────────────────── */
add_action( 'wp_footer', function () {
	if ( is_admin() ) { return; }
	if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) { return; } // pas de bruit admin
	$ajax = admin_url( 'admin-ajax.php' );
	?>
	<script>
	(function(){
		var AJAX=<?php echo wp_json_encode( $ajax ); ?>;
		/* RGPD : on ne trace QUE si « mesure d'audience » acceptée. */
		function consentOK(){ try{ var c=JSON.parse(localStorage.getItem('ag_cookie_consent')||'null'); return !!(c&&c.analytics); }catch(e){ return false; } }
		function vid(){
			var m=document.cookie.match(/(?:^|;\s*)ag_vid=([a-zA-Z0-9]+)/);
			if(m) return m[1];
			var id=''; try{ var a=new Uint8Array(12); (crypto||window.msCrypto).getRandomValues(a); id=Array.from(a).map(function(x){return (x%36).toString(36);}).join(''); }catch(e){ id=(Date.now().toString(36)+Math.random().toString(36).slice(2,8)); }
			var d=new Date(); d.setTime(d.getTime()+180*864e5);
			document.cookie='ag_vid='+id+';expires='+d.toUTCString()+';path=/;SameSite=Lax';
			return id;
		}
		var started=false;
		function send(t,label){
			try{
				var fd=new FormData();
				fd.append('action','ag_track'); fd.append('vid',vid()); fd.append('t',t);
				fd.append('p',(location.pathname||'/')); if(label) fd.append('l',label);
				if(navigator.sendBeacon){ navigator.sendBeacon(AJAX,fd); }
				else{ fetch(AJAX,{method:'POST',body:fd,keepalive:true}).catch(function(){}); }
			}catch(e){}
		}
		function start(){
			if(started) return; started=true;
			send('pv','');
			document.addEventListener('click',function(e){
				var el=e.target && e.target.closest ? e.target.closest('a,button') : null;
				if(!el) return;
				var lbl=(el.innerText||el.textContent||el.getAttribute('aria-label')||'').trim().replace(/\s+/g,' ').slice(0,60);
				if(el.tagName==='A' && el.getAttribute('href')){ lbl=(lbl||el.getAttribute('href')).slice(0,60); }
				if(lbl) send('clk',lbl);
			},true);
		}
		if(consentOK()){ start(); }
		document.addEventListener('ag:consent',function(e){ if(e.detail&&e.detail.analytics){ start(); } });
	})();
	</script>
	<?php
}, 100 );

/* ── Page admin : Prospection → « 👣 Visiteurs » ─────────────────────────── */
add_action( 'admin_menu', function () {
	add_submenu_page( 'ag-prospects', 'Visiteurs (comportement)', '👣 Visiteurs', 'manage_options', 'ag-visiteurs', 'ag_visiteurs_render' );
}, 11 );

/* Vider le journal des visiteurs. */
add_action( 'admin_post_ag_visits_clear', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_visits' ) ) { wp_die( 'Refusé' ); }
	delete_option( 'ag_visits' );
	wp_safe_redirect( add_query_arg( array( 'page' => 'ag-visiteurs', 'cleared' => 1 ), admin_url( 'admin.php' ) ) );
	exit;
} );

function ag_visiteurs_render() {
	$visits = (array) get_option( 'ag_visits', array() );
	uasort( $visits, function ( $a, $b ) { return ( $b['last'] ?? 0 ) <=> ( $a['last'] ?? 0 ); } );
	$only_leads = isset( $_GET['leads'] ) && '1' === $_GET['leads'];
	$total = count( $visits );
	$conv  = 0;
	foreach ( $visits as $v ) { if ( ! empty( $v['lead'] ) ) { $conv++; } }

	echo '<div class="wrap"><h1>👣 Visiteurs venus seuls — comportement</h1>';
	echo '<p>Les visiteurs qui arrivent <strong>d\'eux-mêmes</strong> sur le site (inbound) et leur parcours : pages vues, clics, et lesquels sont devenus des leads. Le traçage respecte le consentement cookies (RGPD) et n\'inclut pas les admins.</p>';
	if ( isset( $_GET['cleared'] ) ) { echo '<div class="notice notice-success"><p>Journal vidé.</p></div>'; }

	echo '<p style="display:flex;gap:20px;align-items:center;flex-wrap:wrap">';
	echo '<span style="font-size:1.6em;font-weight:700;color:#c8962c">' . (int) $total . '</span> visiteurs suivis · ';
	echo '<span style="font-size:1.6em;font-weight:700;color:#1f7a3d">' . (int) $conv . '</span> devenus leads';
	echo ' &nbsp; <a href="' . esc_url( add_query_arg( array( 'page' => 'ag-visiteurs', 'leads' => $only_leads ? 0 : 1 ), admin_url( 'admin.php' ) ) ) . '" class="button">' . ( $only_leads ? '👀 Voir tous' : '🎯 Seulement les leads' ) . '</a>';
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:inline" onsubmit="return confirm(\'Vider tout le journal des visiteurs ?\')"><input type="hidden" name="action" value="ag_visits_clear"><input type="hidden" name="_n" value="' . esc_attr( wp_create_nonce( 'ag_visits' ) ) . '"><button class="button">🗑️ Vider</button></form>';
	echo '</p>';

	if ( empty( $visits ) ) {
		echo '<p><em>Aucun visiteur suivi pour l\'instant. Dès qu\'un visiteur accepte les cookies « mesure d\'audience » et navigue, son parcours apparaît ici.</em></p></div>';
		return;
	}

	echo '<div style="display:flex;flex-direction:column;gap:12px;max-width:900px">';
	foreach ( $visits as $vid => $v ) {
		if ( $only_leads && empty( $v['lead'] ) ) { continue; }
		$evs   = isset( $v['ev'] ) ? (array) $v['ev'] : array();
		$pv    = 0; $clk = 0;
		foreach ( $evs as $e ) { if ( ( $e['k'] ?? '' ) === 'pv' ) { $pv++; } else { $clk++; } }
		$last  = isset( $v['last'] ) ? $v['last'] : 0;
		$first = isset( $v['first'] ) ? $v['first'] : $last;
		$dur   = $last && $first ? human_time_diff( $first, $last ) : '';
		$lead  = ! empty( $v['lead'] ) ? $v['lead'] : null;

		echo '<div style="background:#fff;border:1px solid ' . ( $lead ? '#1f7a3d' : '#e2e4e7' ) . ';border-radius:10px;padding:14px 16px">';
		echo '<div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:baseline">';
		if ( $lead ) {
			echo '<div><span style="background:#1f7a3d;color:#fff;border-radius:100px;padding:2px 10px;font-size:.78rem;font-weight:700">🎯 LEAD</span> <strong>' . esc_html( $lead['name'] ?: '—' ) . '</strong> · ' . esc_html( $lead['email'] ?: ( $lead['phone'] ?? '' ) ) . ' <span style="color:#787c82">(' . esc_html( $lead['source'] ?? '' ) . ')</span></div>';
		} else {
			echo '<div><span style="background:#eef1f4;color:#5b6b78;border-radius:100px;padding:2px 10px;font-size:.78rem;font-weight:700">🌐 Anonyme</span> <span style="color:#787c82;font-family:monospace;font-size:.8rem">' . esc_html( substr( $vid, 0, 8 ) ) . '…</span></div>';
		}
		echo '<div style="color:#787c82;font-size:.85rem">' . (int) $pv . ' page(s) · ' . (int) $clk . ' clic(s)' . ( $dur ? ' · ' . esc_html( $dur ) . ' sur le site' : '' ) . ' · ' . ( $last ? esc_html( date_i18n( 'j M H:i', $last ) ) : '' ) . '</div>';
		echo '</div>';

		// Timeline
		echo '<ol style="margin:10px 0 0;padding-left:18px;color:#3c434a;font-size:.9rem">';
		foreach ( array_slice( $evs, -20 ) as $e ) {
			$icon = ( ( $e['k'] ?? '' ) === 'clk' ) ? '👆' : '📄';
			$txt  = ( $e['k'] ?? '' ) === 'clk' ? ( '« ' . ( $e['l'] ?? '' ) . ' »' . ( ! empty( $e['p'] ) ? ' <span style="color:#a7aaad">(' . esc_html( $e['p'] ) . ')</span>' : '' ) ) : esc_html( $e['p'] ?? '/' );
			echo '<li style="margin:2px 0">' . $icon . ' ' . ( ( $e['k'] ?? '' ) === 'clk' ? wp_kses_post( $txt ) : $txt ) . ' <span style="color:#a7aaad;font-size:.8rem">' . ( ! empty( $e['t'] ) ? esc_html( date_i18n( 'H:i', $e['t'] ) ) : '' ) . '</span></li>';
		}
		echo '</ol>';
		echo '</div>';
	}
	echo '</div></div>';
}
