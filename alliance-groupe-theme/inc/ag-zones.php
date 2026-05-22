<?php
/**
 * Alliance Groupe — Zones ambassadeurs (territoires par département) + enchères.
 *
 * - 1 ambassadeur = 1 zone (département à 2 chiffres) = exclusivité.
 * - Le robot assigne automatiquement les prospects au propriétaire de la zone.
 * - Si une zone est déjà prise, un autre ambassadeur peut ENCHÉRIR : l'admin
 *   attribue ensuite la zone au plus offrant (qui paie → tu gagnes de l'argent).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ── Données ────────────────────────────────────────────────────── */
if ( ! function_exists( 'ag_zones_get' ) ) {
	/** dept(2) => array( owner_email, owner_name, ts ) */
	function ag_zones_get() { return (array) get_option( 'ag_zones', array() ); }
}
if ( ! function_exists( 'ag_zone_bids_get' ) ) {
	/** dept(2) => array of array( email, name, amount, ts ) */
	function ag_zone_bids_get() { return (array) get_option( 'ag_zone_bids', array() ); }
}
if ( ! function_exists( 'ag_dept_norm' ) ) {
	function ag_dept_norm( $d ) { $d = preg_replace( '/[^0-9]/', '', (string) $d ); return substr( $d, 0, 2 ); }
}
if ( ! function_exists( 'ag_dept_names' ) ) {
	/** Départements (clé = code 2 chiffres tel que calculé par le système). */
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
if ( ! function_exists( 'ag_dept_from_text' ) ) {
	/** Extrait le département (2 chiffres) d'un code postal présent dans un texte. */
	function ag_dept_from_text( $text ) { return preg_match( '/\b(\d{2})\d{3}\b/', (string) $text, $m ) ? $m[1] : ''; }
}
if ( ! function_exists( 'ag_prospect_dept' ) ) {
	function ag_prospect_dept( $p ) {
		$d = ag_dept_from_text( $p['address'] ?? '' );
		if ( '' === $d ) $d = ag_dept_from_text( $p['city'] ?? '' );
		return $d;
	}
}
if ( ! function_exists( 'ag_zone_owner' ) ) {
	function ag_zone_owner( $dept ) { $z = ag_zones_get(); $d = ag_dept_norm( $dept ); return $z[ $d ]['owner_email'] ?? ''; }
}
if ( ! function_exists( 'ag_zone_of_owner' ) ) {
	/** Retourne le(s) département(s) possédé(s) par un email. */
	function ag_zone_of_owner( $email ) {
		$email = strtolower( (string) $email ); $out = array();
		foreach ( ag_zones_get() as $d => $z ) { if ( strtolower( $z['owner_email'] ?? '' ) === $email ) $out[] = $d; }
		return $out;
	}
}

/* ── Réassignation en masse des prospects existants à leur zone ──── */
if ( ! function_exists( 'ag_zones_reassign_all' ) ) {
	function ag_zones_reassign_all() {
		$list = (array) get_option( 'ag_prospects', array() );
		$n = 0;
		foreach ( $list as $k => $p ) {
			if ( ! empty( $p['owner_email'] ) ) continue; // déjà assigné : on ne vole pas
			$dept = ag_prospect_dept( $p );
			if ( '' === $dept ) continue;
			$owner = ag_zone_owner( $dept );
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
		$bids  = ag_zone_bids_get();
		$ambs  = array();
		foreach ( (array) get_option( 'ag_ambassadeurs', array() ) as $a ) { if ( ! empty( $a['email'] ) ) $ambs[ $a['email'] ] = $a['name'] ?? $a['email']; }
		$post  = admin_url( 'admin-post.php' );
		$msg   = isset( $_GET['zmsg'] ) ? sanitize_text_field( wp_unslash( $_GET['zmsg'] ) ) : '';
		?>
		<div class="wrap">
			<h1>🗺️ Zones ambassadeurs (départements)</h1>
			<p style="max-width:820px;color:#50575e;">Chaque ambassadeur possède <strong>un département</strong> (exclusivité). Le robot lui assigne automatiquement les prospects de sa zone. Si deux ambassadeurs veulent la même zone, le 2ᵉ <strong>enchérit</strong> et tu attribues au plus offrant.</p>
			<?php if ( 'assigned' === $msg ) : ?><div class="notice notice-success is-dismissible"><p>✅ Zone attribuée.</p></div>
			<?php elseif ( 'freed' === $msg ) : ?><div class="notice notice-success is-dismissible"><p>✅ Zone libérée.</p></div>
			<?php elseif ( 'awarded' === $msg ) : ?><div class="notice notice-success is-dismissible"><p>✅ Enchère attribuée au plus offrant.</p></div>
			<?php elseif ( 0 === strpos( $msg, 'reassigned' ) ) : ?><div class="notice notice-success is-dismissible"><p>✅ <?php echo (int) substr( $msg, 11 ); ?> prospect(s) réassigné(s) à leur zone.</p></div>
			<?php endif; ?>

			<?php
			$names = ag_dept_names();
			$prospects_all = (array) get_option( 'ag_prospects', array() );
			$pcount = array(); $unassigned = 0;
			foreach ( $prospects_all as $p ) {
				if ( in_array( $p['status'] ?? '', array( 'refus', 'ne_pas_contacter' ), true ) ) continue;
				$d = ag_prospect_dept( $p );
				if ( $d ) $pcount[ $d ] = ( $pcount[ $d ] ?? 0 ) + 1;
				if ( empty( $p['owner_email'] ) ) $unassigned++;
			}
			$nb_cov = count( $zones );
			$nb_auc = 0; foreach ( $bids as $bl ) { if ( ! empty( $bl ) ) $nb_auc++; }
			$nb_opp = 0; foreach ( $pcount as $d => $c ) { if ( empty( $zones[ $d ] ) && empty( $bids[ $d ] ) ) $nb_opp++; }
			$tot = count( $names );
			$box = function ( $emoji, $val, $label, $bg ) {
				return '<div style="flex:1;min-width:140px;background:' . $bg . ';border:1px solid #dcdcde;border-radius:12px;padding:14px 16px;"><div style="font-size:1.2rem;">' . $emoji . '</div><div style="font-size:1.7rem;font-weight:800;color:#1d2327;line-height:1.1;">' . (int) $val . '</div><div style="color:#646970;font-size:.82rem;font-weight:600;">' . esc_html( $label ) . '</div></div>';
			};
			?>
			<div style="display:flex;flex-wrap:wrap;gap:12px;margin:8px 0 18px;">
				<?php
				echo $box( '✅', $nb_cov, 'Zones couvertes', '#e9f7ee' );
				echo $box( '⚪', $tot - $nb_cov, 'Zones libres', '#fff' );
				echo $box( '💰', $nb_auc, 'Enchères à trancher', $nb_auc ? '#fff7e6' : '#fff' );
				echo $box( '🎯', $nb_opp, 'Zones à pourvoir (avec prospects)', $nb_opp ? '#eef4fd' : '#fff' );
				echo $box( '📥', $unassigned, 'Prospects non assignés', $unassigned ? '#fdeeee' : '#fff' );
				?>
			</div>

			<?php if ( $nb_auc || $unassigned || $nb_opp ) : ?>
			<div style="background:#fff;border:1px solid #dcdcde;border-left:4px solid #b32d2e;border-radius:8px;padding:14px 18px;margin-bottom:20px;max-width:820px;">
				<strong>📋 Ce qu'il reste à faire</strong>
				<ul style="margin:8px 0 0 18px;line-height:1.7;">
					<?php if ( $nb_auc ) : ?><li><strong><?php echo (int) $nb_auc; ?> enchère(s)</strong> à trancher → voir « Enchères en cours » plus bas.</li><?php endif; ?>
					<?php if ( $unassigned ) : ?><li><strong><?php echo (int) $unassigned; ?> prospect(s) non assignés</strong> → clique « 🔄 Réassigner » (ceux dont la zone a un propriétaire partiront automatiquement).</li><?php endif; ?>
					<?php if ( $nb_opp ) : ?><li><strong><?php echo (int) $nb_opp; ?> département(s) avec des prospects mais sans ambassadeur</strong> → recrute/place un ambassadeur (zones 🎯 bleues ci-dessous).</li><?php endif; ?>
				</ul>
			</div>
			<?php endif; ?>

			<h2>🗺️ Carte des départements</h2>
			<p style="color:#646970;font-size:.86rem;margin:4px 0 10px;"><span style="background:#1e7e34;color:#fff;border-radius:4px;padding:1px 6px;">couvert</span> <span style="background:#bd7b00;color:#fff;border-radius:4px;padding:1px 6px;">enchère</span> <span style="background:#2271b1;color:#fff;border-radius:4px;padding:1px 6px;">à pourvoir (prospects)</span> <span style="background:#e0e0e0;color:#333;border-radius:4px;padding:1px 6px;">libre</span></p>
			<div style="display:flex;flex-wrap:wrap;gap:6px;max-width:1000px;margin-bottom:26px;">
				<?php foreach ( $names as $code => $nom ) :
					if ( isset( $zones[ $code ] ) ) { $bg = '#1e7e34'; $fg = '#fff'; $sub = explode( ' ', trim( $zones[ $code ]['owner_name'] ?? '' ) )[0] ?: 'pris'; }
					elseif ( ! empty( $bids[ $code ] ) ) { $bg = '#bd7b00'; $fg = '#fff'; $sub = '💰' . count( $bids[ $code ] ); }
					elseif ( ! empty( $pcount[ $code ] ) ) { $bg = '#2271b1'; $fg = '#fff'; $sub = $pcount[ $code ] . ' pr.'; }
					else { $bg = '#e0e0e0'; $fg = '#333'; $sub = ! empty( $pcount[ $code ] ) ? $pcount[ $code ] : ''; }
				?>
					<div title="<?php echo esc_attr( $code . ' ' . $nom . ( ! empty( $pcount[ $code ] ) ? ' — ' . $pcount[ $code ] . ' prospect(s)' : '' ) ); ?>" style="width:96px;background:<?php echo $bg; ?>;color:<?php echo $fg; ?>;border-radius:8px;padding:6px 8px;font-size:.74rem;line-height:1.25;">
						<strong><?php echo esc_html( $code ); ?></strong> <?php echo esc_html( mb_strimwidth( $nom, 0, 13, '…' ) ); ?><?php echo $sub ? '<br><span style="opacity:.85;">' . esc_html( $sub ) . '</span>' : ''; ?>
					</div>
				<?php endforeach; ?>
			</div>

			<h2>Attribuer une zone</h2>
			<form method="post" action="<?php echo esc_url( $post ); ?>" style="margin-bottom:18px;">
				<input type="hidden" name="action" value="ag_zone_assign">
				<?php wp_nonce_field( 'ag_zone_admin', '_n' ); ?>
				<input type="text" name="dept" placeholder="Département (ex : 33)" maxlength="3" style="width:160px;" required>
				<select name="owner" required style="min-width:220px;">
					<option value="">— ambassadeur —</option>
					<?php foreach ( $ambs as $em => $nm ) : ?><option value="<?php echo esc_attr( $em ); ?>"><?php echo esc_html( $nm . ' (' . $em . ')' ); ?></option><?php endforeach; ?>
				</select>
				<?php submit_button( 'Attribuer', 'primary', 'submit', false ); ?>
			</form>

			<form method="post" action="<?php echo esc_url( $post ); ?>" style="margin-bottom:24px;" onsubmit="return confirm('Réassigner les prospects non attribués à leur zone ?');">
				<input type="hidden" name="action" value="ag_zone_reassign">
				<?php wp_nonce_field( 'ag_zone_admin', '_n' ); ?>
				<?php submit_button( '🔄 Réassigner les prospects existants à leur zone', 'secondary', 'submit', false ); ?>
			</form>

			<h2>Zones occupées</h2>
			<?php if ( empty( $zones ) ) : ?><p>Aucune zone attribuée pour l'instant.</p><?php else : ?>
			<table class="widefat striped" style="max-width:760px;"><thead><tr><th>Département</th><th>Ambassadeur</th><th>Depuis</th><th></th></tr></thead><tbody>
				<?php foreach ( $zones as $d => $z ) : ?>
				<tr>
					<td><strong><?php echo esc_html( $d ); ?></strong></td>
					<td><?php echo esc_html( ( $z['owner_name'] ?? '' ) . ' — ' . ( $z['owner_email'] ?? '' ) ); ?></td>
					<td><?php echo esc_html( ! empty( $z['ts'] ) ? wp_date( 'd/m/Y', (int) $z['ts'] ) : '—' ); ?></td>
					<td><form method="post" action="<?php echo esc_url( $post ); ?>" onsubmit="return confirm('Libérer cette zone ?');" style="margin:0;"><input type="hidden" name="action" value="ag_zone_free"><?php wp_nonce_field( 'ag_zone_admin', '_n' ); ?><input type="hidden" name="dept" value="<?php echo esc_attr( $d ); ?>"><button class="button button-small button-link-delete">Libérer</button></form></td>
				</tr>
				<?php endforeach; ?>
			</tbody></table>
			<?php endif; ?>

			<h2 style="margin-top:26px;">💰 Enchères en cours</h2>
			<?php
			$has_bids = false;
			foreach ( $bids as $d => $bl ) { if ( ! empty( $bl ) ) { $has_bids = true; break; } }
			if ( ! $has_bids ) : ?><p>Aucune enchère.</p><?php else : ?>
			<?php foreach ( $bids as $d => $bl ) : if ( empty( $bl ) ) continue;
				usort( $bl, function ( $a, $b ) { return (float) ( $b['amount'] ?? 0 ) <=> (float) ( $a['amount'] ?? 0 ); } );
				$cur = ag_zone_owner( $d );
			?>
				<div style="background:#fff;border:1px solid #ccd0d4;border-left:4px solid #bd7b00;border-radius:6px;padding:14px 18px;margin-bottom:12px;max-width:760px;">
					<strong>Département <?php echo esc_html( $d ); ?></strong> <?php echo $cur ? '— actuellement : ' . esc_html( $cur ) : '— libre'; ?>
					<table style="width:100%;margin-top:8px;border-collapse:collapse;">
						<?php foreach ( $bl as $b ) : ?>
							<tr style="border-bottom:1px solid #f0f0f1;"><td style="padding:6px 0;"><?php echo esc_html( ( $b['name'] ?? '' ) . ' — ' . ( $b['email'] ?? '' ) ); ?></td><td style="text-align:right;font-weight:700;"><?php echo esc_html( number_format( (float) ( $b['amount'] ?? 0 ), 0, ',', ' ' ) ); ?> €</td></tr>
						<?php endforeach; ?>
					</table>
					<form method="post" action="<?php echo esc_url( $post ); ?>" style="margin-top:10px;" onsubmit="return confirm('Attribuer le département <?php echo esc_js( $d ); ?> au plus offrant ?');">
						<input type="hidden" name="action" value="ag_zone_award">
						<?php wp_nonce_field( 'ag_zone_admin', '_n' ); ?>
						<input type="hidden" name="dept" value="<?php echo esc_attr( $d ); ?>">
						<?php submit_button( '🏆 Attribuer au plus offrant', 'primary', 'submit', false ); ?>
						<span style="color:#50575e;font-size:.85em;margin-left:8px;">Pense à lui envoyer le lien PayPal du montant gagné.</span>
					</form>
				</div>
			<?php endforeach; ?>
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
		$z = ag_zones_get();
		$rec = function_exists( 'ag_ambassadeur_record' ) ? ag_ambassadeur_record( $owner ) : null;
		$z[ $dept ] = array( 'owner_email' => $owner, 'owner_name' => $rec['name'] ?? '', 'ts' => time() );
		update_option( 'ag_zones', $z );
	}
	wp_safe_redirect( admin_url( 'admin.php?page=ag-zones&zmsg=assigned' ) ); exit;
} );
add_action( 'admin_post_ag_zone_free', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_zone_admin' ) ) wp_die( 'no' );
	$dept = ag_dept_norm( wp_unslash( $_POST['dept'] ?? '' ) );
	$z = ag_zones_get(); unset( $z[ $dept ] ); update_option( 'ag_zones', $z );
	wp_safe_redirect( admin_url( 'admin.php?page=ag-zones&zmsg=freed' ) ); exit;
} );
add_action( 'admin_post_ag_zone_reassign', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_zone_admin' ) ) wp_die( 'no' );
	$n = ag_zones_reassign_all();
	wp_safe_redirect( admin_url( 'admin.php?page=ag-zones&zmsg=reassigned' . $n ) ); exit;
} );
add_action( 'admin_post_ag_zone_award', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_zone_admin' ) ) wp_die( 'no' );
	$dept = ag_dept_norm( wp_unslash( $_POST['dept'] ?? '' ) );
	$bids = ag_zone_bids_get();
	$bl = $bids[ $dept ] ?? array();
	if ( ! empty( $bl ) ) {
		usort( $bl, function ( $a, $b ) { return (float) ( $b['amount'] ?? 0 ) <=> (float) ( $a['amount'] ?? 0 ); } );
		$win = $bl[0];
		$z = ag_zones_get();
		$z[ $dept ] = array( 'owner_email' => $win['email'] ?? '', 'owner_name' => $win['name'] ?? '', 'ts' => time() );
		update_option( 'ag_zones', $z );
		unset( $bids[ $dept ] ); update_option( 'ag_zone_bids', $bids );
		if ( function_exists( 'ag_push' ) ) ag_push( '🏆 Zone ' . $dept . ' attribuée', ( $win['name'] ?? $win['email'] ?? '' ) . ' remporte la zone pour ' . ( $win['amount'] ?? 0 ) . ' €. Pense à encaisser via PayPal.' );
	}
	wp_safe_redirect( admin_url( 'admin.php?page=ag-zones&zmsg=awarded' ) ); exit;
} );

/* ── Handlers front (ambassadeur) : demander une zone / enchérir ── */
add_action( 'admin_post_ag_zone_request', function () {
	if ( ! is_user_logged_in() || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_zone_front' ) ) wp_die( 'no' );
	$u = wp_get_current_user();
	$email = strtolower( $u->user_email );
	$name  = $u->display_name ?: $email;
	$dept  = ag_dept_norm( wp_unslash( $_POST['dept'] ?? '' ) );
	$amount = (float) preg_replace( '/[^0-9.]/', '', (string) wp_unslash( $_POST['amount'] ?? '' ) );
	$res = 'err';
	if ( $dept ) {
		$owner = ag_zone_owner( $dept );
		if ( '' === $owner ) {
			$z = ag_zones_get();
			$z[ $dept ] = array( 'owner_email' => $email, 'owner_name' => $name, 'ts' => time() );
			update_option( 'ag_zones', $z );
			if ( function_exists( 'ag_push' ) ) ag_push( '🗺️ Zone ' . $dept . ' réservée', $name . ' a pris la zone ' . $dept . '.' );
			$res = 'claimed';
		} elseif ( strtolower( $owner ) === $email ) {
			$res = 'mine';
		} else {
			if ( $amount > 0 ) {
				$bids = ag_zone_bids_get();
				$bids[ $dept ] = $bids[ $dept ] ?? array();
				$bids[ $dept ] = array_values( array_filter( $bids[ $dept ], function ( $b ) use ( $email ) { return strtolower( $b['email'] ?? '' ) !== $email; } ) );
				$bids[ $dept ][] = array( 'email' => $email, 'name' => $name, 'amount' => $amount, 'ts' => time() );
				update_option( 'ag_zone_bids', $bids );
				if ( function_exists( 'ag_push' ) ) ag_push( '💰 Enchère zone ' . $dept, $name . ' enchérit ' . $amount . ' € sur la zone ' . $dept . ' (tenue par ' . $owner . ').' );
				$res = 'bid';
			} else {
				$res = 'need_bid';
			}
		}
	}
	wp_safe_redirect( home_url( '/espace-ambassadeur?zone=' . $res . '#zone' ) ); exit;
} );
