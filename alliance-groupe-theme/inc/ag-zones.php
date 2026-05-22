<?php
/**
 * Alliance Groupe — Zones ambassadeurs (territoires par département).
 *
 * Modèle : 1 zone = 1 département. Une zone peut avoir PLUSIEURS ambassadeurs
 * (co-propriété). Les prospects de la zone sont distribués équitablement
 * entre eux (rotation 50/50) → pas de concurrence, on bosse ensemble.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ── Données ────────────────────────────────────────────────────── */
if ( ! function_exists( 'ag_zones_get' ) ) {
	/** dept(2) => array( 'owners' => [ {email,name,ts} ], 'rr' => int ) */
	function ag_zones_get() { return (array) get_option( 'ag_zones', array() ); }
}
if ( ! function_exists( 'ag_dept_norm' ) ) {
	function ag_dept_norm( $d ) { return substr( preg_replace( '/[^0-9]/', '', (string) $d ), 0, 2 ); }
}
if ( ! function_exists( 'ag_dept_from_text' ) ) {
	function ag_dept_from_text( $text ) { return preg_match( '/\b(\d{2})\d{3}\b/', (string) $text, $m ) ? $m[1] : ''; }
}
if ( ! function_exists( 'ag_prospect_dept' ) ) {
	function ag_prospect_dept( $p ) {
		$d = ag_dept_from_text( $p['address'] ?? '' );
		if ( '' === $d ) $d = ag_dept_from_text( $p['city'] ?? '' );
		return $d;
	}
}
if ( ! function_exists( 'ag_zone_owners' ) ) {
	/** Liste des co-propriétaires d'un département (tolère l'ancien format mono-owner). */
	function ag_zone_owners( $dept ) {
		$z = ag_zones_get(); $d = ag_dept_norm( $dept );
		if ( empty( $z[ $d ] ) ) return array();
		$e = $z[ $d ];
		if ( isset( $e['owners'] ) && is_array( $e['owners'] ) ) return $e['owners'];
		if ( ! empty( $e['owner_email'] ) ) return array( array( 'email' => $e['owner_email'], 'name' => $e['owner_name'] ?? '', 'ts' => $e['ts'] ?? time() ) );
		return array();
	}
}
if ( ! function_exists( 'ag_zone_emails' ) ) {
	function ag_zone_emails( $dept ) { return array_map( function ( $o ) { return strtolower( $o['email'] ?? '' ); }, ag_zone_owners( $dept ) ); }
}
if ( ! function_exists( 'ag_zone_add_owner' ) ) {
	/** Ajoute un ambassadeur à une zone (co-propriété). Retourne 'claimed' (zone vide), 'joined' (partage) ou 'mine'. */
	function ag_zone_add_owner( $dept, $email, $name ) {
		$z = ag_zones_get(); $d = ag_dept_norm( $dept ); $email = strtolower( $email );
		$owners = ag_zone_owners( $d );
		foreach ( $owners as $o ) { if ( strtolower( $o['email'] ?? '' ) === $email ) return 'mine'; }
		$was = count( $owners );
		$owners[] = array( 'email' => $email, 'name' => $name, 'ts' => time() );
		$z[ $d ] = array( 'owners' => $owners, 'rr' => (int) ( $z[ $d ]['rr'] ?? 0 ) );
		update_option( 'ag_zones', $z );
		return $was ? 'joined' : 'claimed';
	}
}
if ( ! function_exists( 'ag_zone_remove_owner' ) ) {
	function ag_zone_remove_owner( $dept, $email ) {
		$z = ag_zones_get(); $d = ag_dept_norm( $dept ); $email = strtolower( $email );
		$owners = array_values( array_filter( ag_zone_owners( $d ), function ( $o ) use ( $email ) { return strtolower( $o['email'] ?? '' ) !== $email; } ) );
		if ( empty( $owners ) ) unset( $z[ $d ] ); else $z[ $d ] = array( 'owners' => $owners, 'rr' => (int) ( $z[ $d ]['rr'] ?? 0 ) );
		update_option( 'ag_zones', $z );
	}
}
if ( ! function_exists( 'ag_zone_next_owner' ) ) {
	/** Prochain ambassadeur de la zone (rotation équitable 50/50). Avance le compteur. */
	function ag_zone_next_owner( $dept ) {
		$z = ag_zones_get(); $d = ag_dept_norm( $dept );
		$owners = ag_zone_owners( $d );
		if ( empty( $owners ) ) return '';
		$rr = (int) ( $z[ $d ]['rr'] ?? 0 );
		$pick = $owners[ $rr % count( $owners ) ];
		$z[ $d ] = array( 'owners' => $owners, 'rr' => $rr + 1 );
		update_option( 'ag_zones', $z );
		return $pick['email'] ?? '';
	}
}
if ( ! function_exists( 'ag_zone_of_owner' ) ) {
	function ag_zone_of_owner( $email ) {
		$email = strtolower( (string) $email ); $out = array();
		foreach ( ag_zones_get() as $d => $e ) { foreach ( ag_zone_owners( $d ) as $o ) { if ( strtolower( $o['email'] ?? '' ) === $email ) { $out[] = $d; break; } } }
		return $out;
	}
}
if ( ! function_exists( 'ag_dept_names' ) ) {
	function ag_dept_names() {
		return array(
			'01' => 'Ain', '02' => 'Aisne', '03' => 'Allier', '04' => 'Alpes-de-Hte-Provence', '05' => 'Hautes-Alpes',
			'06' => 'Alpes-Maritimes', '07' => 'Ardèche', '08' => 'Ardennes', '09' => 'Ariège', '10' => 'Aube',
			'11' => 'Aude', '12' => 'Aveyron', '13' => 'Bouches-du-Rhône', '14' => 'Calvados', '15' => 'Cantal',
			'16' => 'Charente', '17' => 'Charente-Maritime', '18' => 'Cher', '19' => 'Corrèze', '20' => 'Corse',
			'21' => "Côte-d'Or", '22' => "Côtes-d'Armor", '23' => 'Creuse', '24' => 'Dordogne', '25' => 'Doubs',
			'26' => 'Drôme', '27' => 'Eure', '28' => 'Eure-et-Loir', '29' => 'Finistère', '30' => 'Gard',
			'31' => 'Haute-Garonne', '32' => 'Gers', '33' => 'Gironde', '34' => 'Hérault', '35' => 'Ille-et-Vilaine',
			'36' => 'Indre', '37' => 'Indre-et-Loire', '38' => 'Isère', '39' => 'Jura', '40' => 'Landes',
			'41' => 'Loir-et-Cher', '42' => 'Loire', '43' => 'Haute-Loire', '44' => 'Loire-Atlantique', '45' => 'Loiret',
			'46' => 'Lot', '47' => 'Lot-et-Garonne', '48' => 'Lozère', '49' => 'Maine-et-Loire', '50' => 'Manche',
			'51' => 'Marne', '52' => 'Haute-Marne', '53' => 'Mayenne', '54' => 'Meurthe-et-Moselle', '55' => 'Meuse',
			'56' => 'Morbihan', '57' => 'Moselle', '58' => 'Nièvre', '59' => 'Nord', '60' => 'Oise',
			'61' => 'Orne', '62' => 'Pas-de-Calais', '63' => 'Puy-de-Dôme', '64' => 'Pyrénées-Atl.', '65' => 'Hautes-Pyrénées',
			'66' => 'Pyrénées-Or.', '67' => 'Bas-Rhin', '68' => 'Haut-Rhin', '69' => 'Rhône', '70' => 'Haute-Saône',
			'71' => 'Saône-et-Loire', '72' => 'Sarthe', '73' => 'Savoie', '74' => 'Haute-Savoie', '75' => 'Paris',
			'76' => 'Seine-Maritime', '77' => 'Seine-et-Marne', '78' => 'Yvelines', '79' => 'Deux-Sèvres', '80' => 'Somme',
			'81' => 'Tarn', '82' => 'Tarn-et-Garonne', '83' => 'Var', '84' => 'Vaucluse', '85' => 'Vendée',
			'86' => 'Vienne', '87' => 'Haute-Vienne', '88' => 'Vosges', '89' => 'Yonne', '90' => 'Belfort',
			'91' => 'Essonne', '92' => 'Hauts-de-Seine', '93' => 'Seine-St-Denis', '94' => 'Val-de-Marne', '95' => "Val-d'Oise",
			'97' => 'Outre-mer (DOM)',
		);
	}
}

/* ── Réassignation en masse des prospects existants (rotation équitable) ─ */
if ( ! function_exists( 'ag_zones_reassign_all' ) ) {
	function ag_zones_reassign_all() {
		$list = (array) get_option( 'ag_prospects', array() );
		$n = 0;
		foreach ( $list as $k => $p ) {
			if ( ! empty( $p['owner_email'] ) ) continue; // déjà assigné : on ne touche pas
			$dept = ag_prospect_dept( $p );
			if ( '' === $dept ) continue;
			$owner = ag_zone_next_owner( $dept );
			if ( '' === $owner ) continue;
			$rec = function_exists( 'ag_ambassadeur_record' ) ? ag_ambassadeur_record( $owner ) : null;
			$list[ $k ]['owner_email'] = $owner;
			$list[ $k ]['owner_name']  = $rec['name'] ?? '';
			$n++;
		}
		if ( $n ) update_option( 'ag_prospects', array_values( $list ) );
		return $n;
	}
}

/* ── Admin : page Zones (sous Prospection) ──────────────────────── */
add_action( 'admin_menu', function () {
	add_submenu_page( 'ag-prospects', 'Zones ambassadeurs', '🗺️ Zones', 'manage_options', 'ag-zones', 'ag_zones_render' );
}, 20 );

if ( ! function_exists( 'ag_zones_render' ) ) {
	function ag_zones_render() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$zones = ag_zones_get();
		$ambs  = array();
		foreach ( (array) get_option( 'ag_ambassadeurs', array() ) as $a ) { if ( ! empty( $a['email'] ) ) $ambs[ $a['email'] ] = $a['name'] ?? $a['email']; }
		$post  = admin_url( 'admin-post.php' );
		$msg   = isset( $_GET['zmsg'] ) ? sanitize_text_field( wp_unslash( $_GET['zmsg'] ) ) : '';
		$names = ag_dept_names();
		$prospects_all = (array) get_option( 'ag_prospects', array() );
		$pcount = array(); $unassigned = 0;
		foreach ( $prospects_all as $p ) {
			if ( in_array( $p['status'] ?? '', array( 'refus', 'ne_pas_contacter' ), true ) ) continue;
			$d = ag_prospect_dept( $p );
			if ( $d ) $pcount[ $d ] = ( $pcount[ $d ] ?? 0 ) + 1;
			if ( empty( $p['owner_email'] ) ) $unassigned++;
		}
		$nb_cov = 0; $nb_shared = 0;
		foreach ( $zones as $d => $e ) { $o = ag_zone_owners( $d ); if ( count( $o ) >= 1 ) $nb_cov++; if ( count( $o ) >= 2 ) $nb_shared++; }
		$nb_opp = 0; foreach ( $pcount as $d => $c ) { if ( empty( ag_zone_owners( $d ) ) ) $nb_opp++; }
		$tot = count( $names );
		$box = function ( $emoji, $val, $label, $bg ) {
			return '<div style="flex:1;min-width:140px;background:' . $bg . ';border:1px solid #dcdcde;border-radius:12px;padding:14px 16px;"><div style="font-size:1.2rem;">' . $emoji . '</div><div style="font-size:1.7rem;font-weight:800;color:#1d2327;line-height:1.1;">' . (int) $val . '</div><div style="color:#646970;font-size:.82rem;font-weight:600;">' . esc_html( $label ) . '</div></div>';
		};
		?>
		<div class="wrap">
			<h1>🗺️ Zones ambassadeurs (départements)</h1>
			<p style="max-width:820px;color:#50575e;">Chaque ambassadeur couvre un ou plusieurs <strong>départements</strong>. Le robot répartit les prospects de la zone <strong>équitablement (50/50)</strong> entre les ambassadeurs de cette zone — pas de concurrence, on bosse ensemble.</p>
			<?php if ( 'added' === $msg ) : ?><div class="notice notice-success is-dismissible"><p>✅ Ambassadeur ajouté à la zone.</p></div>
			<?php elseif ( 'removed' === $msg ) : ?><div class="notice notice-success is-dismissible"><p>✅ Retiré de la zone.</p></div>
			<?php elseif ( 0 === strpos( $msg, 'reassigned' ) ) : ?><div class="notice notice-success is-dismissible"><p>✅ <?php echo (int) substr( $msg, 11 ); ?> prospect(s) réassigné(s) à leur zone.</p></div>
			<?php endif; ?>

			<div style="display:flex;flex-wrap:wrap;gap:12px;margin:8px 0 18px;">
				<?php
				echo $box( '✅', $nb_cov, 'Zones couvertes', '#e9f7ee' );
				echo $box( '🤝', $nb_shared, 'Zones partagées (50/50)', $nb_shared ? '#eef4fd' : '#fff' );
				echo $box( '⚪', $tot - $nb_cov, 'Zones libres', '#fff' );
				echo $box( '🎯', $nb_opp, 'À pourvoir (avec prospects)', $nb_opp ? '#eef4fd' : '#fff' );
				echo $box( '📥', $unassigned, 'Prospects non assignés', $unassigned ? '#fdeeee' : '#fff' );
				?>
			</div>

			<?php if ( $unassigned || $nb_opp ) : ?>
			<div style="background:#fff;border:1px solid #dcdcde;border-left:4px solid #b32d2e;border-radius:8px;padding:14px 18px;margin-bottom:20px;max-width:820px;">
				<strong>📋 Ce qu'il reste à faire</strong>
				<ul style="margin:8px 0 0 18px;line-height:1.7;">
					<?php if ( $unassigned ) : ?><li><strong><?php echo (int) $unassigned; ?> prospect(s) non assignés</strong> → clique « 🔄 Réassigner » (ceux dont la zone a un ambassadeur partiront automatiquement).</li><?php endif; ?>
					<?php if ( $nb_opp ) : ?><li><strong><?php echo (int) $nb_opp; ?> département(s) avec des prospects mais sans ambassadeur</strong> → place un ambassadeur (zones 🎯 bleues ci-dessous).</li><?php endif; ?>
				</ul>
			</div>
			<?php endif; ?>

			<h2>🗺️ Carte des départements</h2>
			<p style="color:#646970;font-size:.86rem;margin:4px 0 10px;"><span style="background:#1e7e34;color:#fff;border-radius:4px;padding:1px 6px;">couvert</span> <span style="background:#2271b1;color:#fff;border-radius:4px;padding:1px 6px;">partagé / à pourvoir</span> <span style="background:#e0e0e0;color:#333;border-radius:4px;padding:1px 6px;">libre</span></p>
			<div style="display:flex;flex-wrap:wrap;gap:6px;max-width:1000px;margin-bottom:26px;">
				<?php foreach ( $names as $code => $nom ) :
					$ow = ag_zone_owners( $code );
					if ( count( $ow ) >= 2 ) { $bg = '#2271b1'; $fg = '#fff'; $sub = '🤝 ×' . count( $ow ); }
					elseif ( count( $ow ) === 1 ) { $bg = '#1e7e34'; $fg = '#fff'; $sub = explode( ' ', trim( $ow[0]['name'] ?? '' ) )[0] ?: 'pris'; }
					elseif ( ! empty( $pcount[ $code ] ) ) { $bg = '#2271b1'; $fg = '#fff'; $sub = $pcount[ $code ] . ' pr.'; }
					else { $bg = '#e0e0e0'; $fg = '#333'; $sub = ''; }
				?>
					<div title="<?php echo esc_attr( $code . ' ' . $nom . ( ! empty( $pcount[ $code ] ) ? ' — ' . $pcount[ $code ] . ' prospect(s)' : '' ) ); ?>" style="width:96px;background:<?php echo $bg; ?>;color:<?php echo $fg; ?>;border-radius:8px;padding:6px 8px;font-size:.74rem;line-height:1.25;">
						<strong><?php echo esc_html( $code ); ?></strong> <?php echo esc_html( mb_strimwidth( $nom, 0, 13, '…' ) ); ?><?php echo $sub ? '<br><span style="opacity:.85;">' . esc_html( $sub ) . '</span>' : ''; ?>
					</div>
				<?php endforeach; ?>
			</div>

			<h2>Ajouter un ambassadeur à une zone</h2>
			<form method="post" action="<?php echo esc_url( $post ); ?>" style="margin-bottom:18px;">
				<input type="hidden" name="action" value="ag_zone_assign">
				<?php wp_nonce_field( 'ag_zone_admin', '_n' ); ?>
				<input type="text" name="dept" placeholder="Département (ex : 33)" maxlength="3" style="width:160px;" required>
				<select name="owner" required style="min-width:220px;">
					<option value="">— ambassadeur —</option>
					<?php foreach ( $ambs as $em => $nm ) : ?><option value="<?php echo esc_attr( $em ); ?>"><?php echo esc_html( $nm . ' (' . $em . ')' ); ?></option><?php endforeach; ?>
				</select>
				<?php submit_button( 'Ajouter à la zone', 'primary', 'submit', false ); ?>
				<span style="color:#50575e;font-size:.85em;">Ajoute-en plusieurs sur le même département = partage 50/50.</span>
			</form>

			<form method="post" action="<?php echo esc_url( $post ); ?>" style="margin-bottom:24px;" onsubmit="return confirm('Réassigner les prospects non attribués à leur zone ?');">
				<input type="hidden" name="action" value="ag_zone_reassign">
				<?php wp_nonce_field( 'ag_zone_admin', '_n' ); ?>
				<?php submit_button( '🔄 Réassigner les prospects existants à leur zone', 'secondary', 'submit', false ); ?>
			</form>

			<h2>Zones couvertes</h2>
			<?php $any = false; foreach ( $zones as $d => $e ) { if ( ag_zone_owners( $d ) ) { $any = true; break; } } ?>
			<?php if ( ! $any ) : ?><p>Aucune zone attribuée pour l'instant.</p><?php else : ?>
			<table class="widefat striped" style="max-width:820px;"><thead><tr><th>Dépt</th><th>Ambassadeur(s) — partage 50/50</th></tr></thead><tbody>
				<?php foreach ( $names as $d => $nom ) : $ow = ag_zone_owners( $d ); if ( empty( $ow ) ) continue; ?>
				<tr>
					<td><strong><?php echo esc_html( $d ); ?></strong><br><small><?php echo esc_html( $nom ); ?></small></td>
					<td>
						<?php foreach ( $ow as $o ) : ?>
							<span style="display:inline-flex;align-items:center;gap:6px;background:#f0f0f1;border-radius:100px;padding:3px 6px 3px 12px;margin:0 6px 6px 0;">
								<?php echo esc_html( ( $o['name'] ?? '' ) ?: ( $o['email'] ?? '' ) ); ?>
								<form method="post" action="<?php echo esc_url( $post ); ?>" style="margin:0;" onsubmit="return confirm('Retirer de la zone ?');">
									<input type="hidden" name="action" value="ag_zone_remove"><?php wp_nonce_field( 'ag_zone_admin', '_n' ); ?>
									<input type="hidden" name="dept" value="<?php echo esc_attr( $d ); ?>"><input type="hidden" name="owner" value="<?php echo esc_attr( $o['email'] ?? '' ); ?>">
									<button class="button-link" style="color:#b32d2e;text-decoration:none;font-size:1.1em;line-height:1;">×</button>
								</form>
							</span>
						<?php endforeach; ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody></table>
			<?php endif; ?>
		</div>
		<?php
	}
}

/* ── Handlers admin ─────────────────────────────────────────────── */
add_action( 'admin_post_ag_zone_assign', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_zone_admin' ) ) wp_die( 'no' );
	$dept = ag_dept_norm( wp_unslash( $_POST['dept'] ?? '' ) );
	$owner = sanitize_email( wp_unslash( $_POST['owner'] ?? '' ) );
	if ( $dept && $owner ) {
		$rec = function_exists( 'ag_ambassadeur_record' ) ? ag_ambassadeur_record( $owner ) : null;
		ag_zone_add_owner( $dept, $owner, $rec['name'] ?? '' );
	}
	wp_safe_redirect( admin_url( 'admin.php?page=ag-zones&zmsg=added' ) ); exit;
} );
add_action( 'admin_post_ag_zone_remove', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_zone_admin' ) ) wp_die( 'no' );
	ag_zone_remove_owner( ag_dept_norm( wp_unslash( $_POST['dept'] ?? '' ) ), sanitize_email( wp_unslash( $_POST['owner'] ?? '' ) ) );
	wp_safe_redirect( admin_url( 'admin.php?page=ag-zones&zmsg=removed' ) ); exit;
} );
add_action( 'admin_post_ag_zone_reassign', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_zone_admin' ) ) wp_die( 'no' );
	$n = ag_zones_reassign_all();
	wp_safe_redirect( admin_url( 'admin.php?page=ag-zones&zmsg=reassigned' . $n ) ); exit;
} );

/* ── Téléphone ambassadeur (requis, unique = anti multi-comptes) ── */
if ( ! function_exists( 'ag_amb_phone' ) ) {
	function ag_amb_phone( $uid = 0 ) { $uid = $uid ?: get_current_user_id(); return trim( (string) get_user_meta( $uid, 'ag_amb_phone', true ) ); }
}
add_action( 'admin_post_ag_amb_phone_save', function () {
	if ( ! is_user_logged_in() || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_zone_front' ) ) wp_die( 'no' );
	$uid   = get_current_user_id();
	$phone = preg_replace( '/[^0-9+]/', '', (string) wp_unslash( $_POST['phone'] ?? '' ) );
	$res   = 'phone_err';
	if ( strlen( preg_replace( '/[^0-9]/', '', $phone ) ) >= 9 ) {
		$dupe = get_users( array( 'meta_key' => 'ag_amb_phone', 'meta_value' => $phone, 'exclude' => array( $uid ), 'fields' => 'ID', 'number' => 1 ) );
		if ( $dupe ) { $res = 'phone_dupe'; } else { update_user_meta( $uid, 'ag_amb_phone', $phone ); $res = 'phone_ok'; }
	}
	wp_safe_redirect( home_url( '/espace-ambassadeur?zone=' . $res . '#zone' ) ); exit;
} );

/* ── Handler front (ambassadeur) : prendre / changer de zone (1 seule zone) ── */
add_action( 'admin_post_ag_zone_request', function () {
	if ( ! is_user_logged_in() || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_zone_front' ) ) wp_die( 'no' );
	$u = wp_get_current_user();
	$email = strtolower( $u->user_email );
	$name  = $u->display_name ?: $email;
	$dept  = ag_dept_norm( wp_unslash( $_POST['dept'] ?? '' ) );
	if ( '' === ag_amb_phone( $u->ID ) ) { wp_safe_redirect( home_url( '/espace-ambassadeur?zone=need_phone#zone' ) ); exit; }
	$res = 'err';
	if ( $dept && isset( ag_dept_names()[ $dept ] ) ) {
		$mine = ag_zone_of_owner( $email );
		if ( in_array( $dept, $mine, true ) ) {
			$res = 'mine';
		} else {
			foreach ( $mine as $old ) { ag_zone_remove_owner( $old, $email ); } // 1 zone max : on quitte l'ancienne
			$add = ag_zone_add_owner( $dept, $email, $name ); // claimed | joined
			$res = $mine ? 'changed' : $add;
			if ( function_exists( 'ag_push' ) ) ag_push( '🗺️ Zone ' . $dept, $name . ' couvre le ' . $dept . ( $mine ? ' (changement de zone)' : '' ) . ( 'joined' === $add ? ' — partage 50/50' : '' ) . '.' );
		}
	}
	wp_safe_redirect( home_url( '/espace-ambassadeur?zone=' . $res . '#zone' ) ); exit;
} );
