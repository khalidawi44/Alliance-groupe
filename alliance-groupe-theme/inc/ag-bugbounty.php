<?php
/**
 * AG Bug Bounty — tableau de bord PERSO des programmes de bug bounty.
 *
 * Objectif : réunir au même endroit les programmes qui RÉMUNÈRENT la découverte
 * de failles (YesWeHack, Intigriti, HackerOne, Bugcrowd…), suivre ton avancement
 * (à tester → en cours → rapport soumis → accepté → payé) et tes gains.
 *
 * ⚖️ LÉGAL : cet outil n'exécute AUCUNE attaque. Il ORGANISE un travail autorisé.
 * On ne teste QUE des programmes où l'entreprise donne son autorisation (bug bounty
 * / VDP) et UNIQUEMENT dans le périmètre (scope) qu'elle publie. Tester hors scope
 * ou un site sans programme d'autorisation est illégal (art. 323-1 et s. du Code pénal).
 *
 * Données : option `ag_bb_programs` (liste). Admin uniquement.
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ag_bb_get' ) ) {
	function ag_bb_get() { $p = get_option( 'ag_bb_programs', array() ); return is_array( $p ) ? $p : array(); }
	function ag_bb_save( $p ) { update_option( 'ag_bb_programs', array_values( $p ), false ); }
}

if ( ! function_exists( 'ag_bb_statuses' ) ) {
	/** Statuts d'avancement (clé => [libellé, couleur]). */
	function ag_bb_statuses() {
		return array(
			'todo'      => array( '🎯 À tester',       '#0e7490' ),
			'wip'       => array( '🔧 En cours',        '#bf6a02' ),
			'submitted' => array( '📤 Rapport soumis',  '#6d28d9' ),
			'accepted'  => array( '✅ Accepté',          '#1a7f37' ),
			'paid'      => array( '💰 Payé',             '#0a7d3c' ),
			'rejected'  => array( '🚫 Refusé / doublon', '#b91c1c' ),
		);
	}
	/** Programmes préconfigurés (ajout en 1 clic). Uniquement des programmes PUBLICS autorisés. */
	function ag_bb_presets() {
		return array(
			'doctolib' => array(
				'name'     => 'Doctolib — Public Bug Bounty',
				'platform' => 'YesWeHack',
				'url'      => 'https://yeswehack.com/programs/doctolib-public-bug-bounty-program',
				'scope'    => 'DANS LE SCOPE : www.doctolib.fr|de|it · pro.doctolib.fr|de|it · *.doctolib.fr|de|it|com|net · app iOS + Android Doctolib · *.siilo.com + apps Siilo. HORS SCOPE : community/info/status/store/media.doctolib, *.atlassian.net, *.zendesk.com, api.tanker.io, sous-domaines dangling, hôtes tiers (login.decathlon.net…), IP brutes, domaines typo.',
				'reward'   => 'Low 100€ · Medium 500€ · High 1 500–4 000€ · Critical 5 000–50 000€ (scénario Game Over / One Shot = 50 000€). Fuite account_id = 200€.',
				'status'   => 'todo',
				'gain'     => 0,
				'notes'    => 'RÈGLES : compte de TEST uniquement (créer sur /sessions/new, alias @yeswehack.ninja) — JAMAIS ton vrai compte. User-Agent obligatoire : "BugBounty/42 YWH". Max 10 req/s, outils auto en douceur. Ne jamais toucher/détruire de vraies données patients. Interdits : DoS, brute force, phishing, ingénierie sociale. Vérif d\'identité requise pour soumettre. Qualifiant : IDOR, XSS stocké/réfléchi, SQLi, RCE, SSRF/XXE/LFI, contournement auth/authz, fuite PII/santé. Contact : security@doctolib.com',
			),
		);
	}
	/** Plateformes de départ (liens officiels vers les programmes publics). */
	function ag_bb_platforms() {
		return array(
			'YesWeHack'      => 'https://www.yeswehack.com/programs',
			'Intigriti'      => 'https://app.intigriti.com/researcher/programs',
			'HackerOne'      => 'https://hackerone.com/directory/programs',
			'Bugcrowd'       => 'https://bugcrowd.com/engagements',
			'Open Bug Bounty'=> 'https://www.openbugbounty.org/',
			'FireBounty (annuaire)' => 'https://firebounty.com/',
		);
	}
}

/* ── Menu ── */
add_action( 'admin_menu', function () {
	add_menu_page( 'Bug Bounty', '🎯 Bug Bounty', 'manage_options', 'ag-bugbounty', 'ag_bb_render', 'dashicons-shield-alt', 4 );
} );

/* ── Actions (POST) ── */
add_action( 'admin_init', function () {
	if ( ! current_user_can( 'manage_options' ) ) { return; }

	// Enregistrer / modifier un programme.
	if ( isset( $_POST['ag_bb_save'] ) && check_admin_referer( 'ag_bb' ) ) {
		$items = ag_bb_get();
		$id    = sanitize_text_field( wp_unslash( $_POST['id'] ?? '' ) );
		$row = array(
			'id'       => $id ?: substr( md5( uniqid( 'bb', true ) ), 0, 10 ),
			'name'     => sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) ),
			'platform' => sanitize_text_field( wp_unslash( $_POST['platform'] ?? '' ) ),
			'url'      => esc_url_raw( wp_unslash( $_POST['url'] ?? '' ) ),
			'scope'    => sanitize_textarea_field( wp_unslash( $_POST['scope'] ?? '' ) ),
			'reward'   => sanitize_text_field( wp_unslash( $_POST['reward'] ?? '' ) ),
			'status'   => array_key_exists( sanitize_text_field( wp_unslash( $_POST['status'] ?? '' ) ), ag_bb_statuses() ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'todo',
			'gain'     => (float) str_replace( ',', '.', (string) ( $_POST['gain'] ?? 0 ) ),
			'notes'    => sanitize_textarea_field( wp_unslash( $_POST['notes'] ?? '' ) ),
			'ts'       => time(),
		);
		$found = false;
		foreach ( $items as $k => $it ) {
			if ( ( $it['id'] ?? '' ) === $row['id'] ) { $row['ts'] = $it['ts'] ?? time(); $items[ $k ] = $row; $found = true; break; }
		}
		if ( ! $found ) { $items[] = $row; }
		ag_bb_save( $items );
		add_settings_error( 'ag_bb', 's', '✅ Programme enregistré.', 'updated' );
	}

	// Mise à jour rapide statut + gain (depuis la ligne).
	if ( isset( $_POST['ag_bb_quickstatus'] ) && check_admin_referer( 'ag_bb' ) ) {
		$items = ag_bb_get();
		$id    = sanitize_text_field( wp_unslash( $_POST['id'] ?? '' ) );
		$st    = sanitize_text_field( wp_unslash( $_POST['status'] ?? 'todo' ) );
		$gn    = (float) str_replace( ',', '.', (string) ( $_POST['gain'] ?? 0 ) );
		foreach ( $items as $k => $it ) {
			if ( ( $it['id'] ?? '' ) === $id ) {
				$items[ $k ]['status'] = array_key_exists( $st, ag_bb_statuses() ) ? $st : 'todo';
				$items[ $k ]['gain']   = $gn;
				break;
			}
		}
		ag_bb_save( $items );
		add_settings_error( 'ag_bb', 'u', '✅ Mis à jour.', 'updated' );
	}

	// Ajouter un programme préconfiguré (1 clic).
	if ( isset( $_POST['ag_bb_preset'] ) && check_admin_referer( 'ag_bb' ) ) {
		$key     = sanitize_text_field( wp_unslash( $_POST['ag_bb_preset'] ) );
		$presets = ag_bb_presets();
		if ( isset( $presets[ $key ] ) ) {
			$items = ag_bb_get();
			$exists = false;
			foreach ( $items as $it ) { if ( ( $it['url'] ?? '' ) === $presets[ $key ]['url'] ) { $exists = true; break; } }
			if ( $exists ) {
				add_settings_error( 'ag_bb', 'p', 'ℹ️ Ce programme est déjà dans ton suivi.', 'updated' );
			} else {
				$row = $presets[ $key ];
				$row['id'] = substr( md5( uniqid( 'bb', true ) ), 0, 10 );
				$row['ts'] = time();
				$items[] = $row;
				ag_bb_save( $items );
				add_settings_error( 'ag_bb', 'p', '✅ « ' . esc_html( $row['name'] ) . ' » ajouté à ton suivi.', 'updated' );
			}
		}
	}

	// Supprimer.
	if ( isset( $_POST['ag_bb_del'] ) && check_admin_referer( 'ag_bb' ) ) {
		$id    = sanitize_text_field( wp_unslash( $_POST['id'] ?? '' ) );
		$items = array_values( array_filter( ag_bb_get(), function ( $it ) use ( $id ) { return ( $it['id'] ?? '' ) !== $id; } ) );
		ag_bb_save( $items );
		add_settings_error( 'ag_bb', 'd', '🗑️ Programme supprimé.', 'updated' );
	}
} );

/* ── Rendu ── */
if ( ! function_exists( 'ag_bb_render' ) ) {
	function ag_bb_render() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		settings_errors( 'ag_bb' );
		$items = ag_bb_get();
		$stats = ag_bb_statuses();

		// Totaux.
		$total_gain = 0; $by = array();
		foreach ( $items as $it ) { $total_gain += (float) ( $it['gain'] ?? 0 ); $s = $it['status'] ?? 'todo'; $by[ $s ] = ( $by[ $s ] ?? 0 ) + 1; }

		echo '<div class="wrap"><h1>🎯 Bug Bounty — mes programmes rémunérés</h1>';

		// Bandeau légal.
		echo '<div class="notice notice-warning" style="border-left-color:#dba617"><p style="max-width:900px">⚖️ <strong>Uniquement des programmes qui t\'AUTORISENT</strong> (bug bounty / VDP). Lis le <strong>périmètre (scope)</strong> de chaque programme et reste <strong>strictement dedans</strong>. Tester un site sans programme d\'autorisation, ou hors scope, est <strong>illégal</strong>. Cet outil ne fait qu\'<strong>organiser</strong> ton travail — il n\'exécute aucune attaque.</p></div>';

		// Cartouches totaux.
		echo '<div style="display:flex;flex-wrap:wrap;gap:10px;margin:14px 0">';
		echo '<div style="background:#f6f7f7;border:1px solid #dcdcde;border-radius:10px;padding:12px 16px"><div style="font-size:1.6rem;font-weight:800">' . count( $items ) . '</div><div style="color:#646970;font-size:.82rem">Programmes suivis</div></div>';
		echo '<div style="background:#eafaf0;border:1px solid #b7e4c7;border-radius:10px;padding:12px 16px"><div style="font-size:1.6rem;font-weight:800;color:#0a7d3c">' . number_format_i18n( $total_gain, 0 ) . ' €</div><div style="color:#646970;font-size:.82rem">Gains cumulés</div></div>';
		foreach ( array( 'todo', 'wip', 'submitted', 'paid' ) as $s ) {
			echo '<div style="background:#fff;border:1px solid #dcdcde;border-radius:10px;padding:12px 16px"><div style="font-size:1.6rem;font-weight:800">' . (int) ( $by[ $s ] ?? 0 ) . '</div><div style="color:#646970;font-size:.82rem">' . esc_html( $stats[ $s ][0] ) . '</div></div>';
		}
		echo '</div>';

		// Plateformes (liens officiels pour trouver des programmes).
		echo '<h2 style="margin-top:6px">🚀 Où trouver des programmes</h2>';
		echo '<p style="color:#50575e;max-width:900px">Ouvre un annuaire, choisis un programme <strong>public</strong>, lis son scope, puis ajoute-le à ton suivi ci-dessous. <strong>YesWeHack</strong> (🇫🇷) et <strong>Intigriti</strong> (🇪🇺) sont les plus adaptés pour toi.</p>';
		echo '<p>';
		foreach ( ag_bb_platforms() as $name => $url ) {
			echo '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener" class="button" style="margin:0 6px 6px 0">' . esc_html( $name ) . ' ↗</a>';
		}
		echo '</p>';

		// Programmes préconfigurés (ajout 1 clic).
		$presets = ag_bb_presets();
		if ( $presets ) {
			echo '<p style="margin-top:4px"><strong style="color:#50575e;font-size:12px">Ajout rapide (préconfiguré) :</strong> ';
			foreach ( $presets as $pk => $pv ) {
				echo '<form method="post" style="display:inline;margin:0 6px 6px 0"><input type="hidden" name="ag_bb_preset" value="' . esc_attr( $pk ) . '">';
				wp_nonce_field( 'ag_bb' );
				echo '<button class="button button-secondary">➕ ' . esc_html( $pv['name'] ) . '</button></form>';
			}
			echo '</p>';
		}

		// Formulaire d'ajout.
		$edit = null;
		if ( isset( $_GET['edit'] ) ) {
			$eid = sanitize_text_field( wp_unslash( $_GET['edit'] ) );
			foreach ( $items as $it ) { if ( ( $it['id'] ?? '' ) === $eid ) { $edit = $it; break; } }
		}
		echo '<h2 style="margin-top:24px">' . ( $edit ? '✏️ Modifier le programme' : '➕ Ajouter un programme' ) . '</h2>';
		echo '<form method="post"><table class="form-table"><tbody>';
		echo '<input type="hidden" name="id" value="' . esc_attr( $edit['id'] ?? '' ) . '">';
		echo '<tr><th>Nom du programme</th><td><input type="text" name="name" required value="' . esc_attr( $edit['name'] ?? '' ) . '" class="regular-text" placeholder="ex. Doctolib — Public Program"></td></tr>';
		echo '<tr><th>Plateforme</th><td><input list="ag-bb-plats" name="platform" value="' . esc_attr( $edit['platform'] ?? '' ) . '" class="regular-text" placeholder="YesWeHack / Intigriti / HackerOne…"><datalist id="ag-bb-plats">';
		foreach ( array_keys( ag_bb_platforms() ) as $pn ) { echo '<option value="' . esc_attr( $pn ) . '">'; }
		echo '</datalist></td></tr>';
		echo '<tr><th>Lien du programme</th><td><input type="url" name="url" value="' . esc_attr( $edit['url'] ?? '' ) . '" class="regular-text" style="width:520px" placeholder="https://…"><p class="description">La page officielle où tu lis le scope et soumets ton rapport.</p></td></tr>';
		echo '<tr><th>Périmètre (scope)</th><td><textarea name="scope" rows="2" class="large-text" placeholder="Domaines/apps AUTORISÉS. Ex : *.exemple.com, app mobile iOS. (Copie du scope officiel.)">' . esc_textarea( $edit['scope'] ?? '' ) . '</textarea></td></tr>';
		echo '<tr><th>Récompenses</th><td><input type="text" name="reward" value="' . esc_attr( $edit['reward'] ?? '' ) . '" class="regular-text" placeholder="ex. 100 € (low) → 5 000 € (critical)"></td></tr>';
		echo '<tr><th>Statut</th><td><select name="status">';
		foreach ( $stats as $sk => $sv ) { echo '<option value="' . esc_attr( $sk ) . '" ' . selected( $edit['status'] ?? 'todo', $sk, false ) . '>' . esc_html( $sv[0] ) . '</option>'; }
		echo '</select> &nbsp; Gain perçu (€) <input type="text" name="gain" value="' . esc_attr( $edit['gain'] ?? '' ) . '" style="width:100px" placeholder="0"></td></tr>';
		echo '<tr><th>Notes</th><td><textarea name="notes" rows="2" class="large-text" placeholder="Failles repérées, statut du rapport, contact…">' . esc_textarea( $edit['notes'] ?? '' ) . '</textarea></td></tr>';
		echo '</tbody></table>';
		wp_nonce_field( 'ag_bb' );
		echo '<button name="ag_bb_save" value="1" class="button button-primary">' . ( $edit ? 'Enregistrer les modifications' : 'Ajouter au suivi' ) . '</button>';
		if ( $edit ) { echo ' <a href="' . esc_url( admin_url( 'admin.php?page=ag-bugbounty' ) ) . '" class="button">Annuler</a>'; }
		echo '</form>';

		// Liste.
		echo '<h2 style="margin-top:28px">📋 Mon suivi (' . count( $items ) . ')</h2>';
		if ( ! $items ) {
			echo '<p style="color:#646970">Aucun programme pour l\'instant. Ouvre <strong>YesWeHack</strong> ou <strong>Intigriti</strong> ci-dessus, choisis un programme public, et ajoute-le.</p>';
		} else {
			// Tri : à tester d'abord, puis récents.
			usort( $items, function ( $x, $y ) {
				$ord = array( 'todo' => 0, 'wip' => 1, 'submitted' => 2, 'accepted' => 3, 'paid' => 4, 'rejected' => 5 );
				$a = $ord[ $x['status'] ?? 'todo' ] ?? 9; $b = $ord[ $y['status'] ?? 'todo' ] ?? 9;
				if ( $a !== $b ) { return $a <=> $b; }
				return (int) ( $y['ts'] ?? 0 ) <=> (int) ( $x['ts'] ?? 0 );
			} );
			echo '<table class="widefat striped"><thead><tr><th>Programme</th><th>Plateforme</th><th>Récompenses</th><th>Statut / Gain</th><th>Actions</th></tr></thead><tbody>';
			foreach ( $items as $it ) {
				$sv = $stats[ $it['status'] ?? 'todo' ] ?? $stats['todo'];
				echo '<tr>';
				echo '<td><strong>' . esc_html( $it['name'] ?: '—' ) . '</strong>';
				if ( ! empty( $it['url'] ) ) { echo '<br><a href="' . esc_url( $it['url'] ) . '" target="_blank" rel="noopener" style="font-size:12px">ouvrir le programme ↗</a>'; }
				if ( ! empty( $it['scope'] ) ) { echo '<div style="font-size:11px;color:#646970;margin-top:4px"><strong>Scope :</strong> ' . esc_html( wp_trim_words( $it['scope'], 20 ) ) . '</div>'; }
				if ( ! empty( $it['notes'] ) ) { echo '<div style="font-size:11px;color:#8a8a8a;margin-top:2px">📝 ' . esc_html( wp_trim_words( $it['notes'], 18 ) ) . '</div>'; }
				echo '</td>';
				echo '<td>' . esc_html( $it['platform'] ?: '—' ) . '</td>';
				echo '<td style="font-size:12px">' . esc_html( $it['reward'] ?: '—' ) . '</td>';
				echo '<td><form method="post" style="display:flex;gap:4px;align-items:center;flex-wrap:wrap">';
				echo '<input type="hidden" name="id" value="' . esc_attr( $it['id'] ) . '">';
				echo '<span style="background:' . esc_attr( $sv[1] ) . ';color:#fff;border-radius:4px;padding:1px 7px;font-size:11px;white-space:nowrap">' . esc_html( $sv[0] ) . '</span><br>';
				echo '<select name="status" style="font-size:12px">';
				foreach ( $stats as $sk => $svv ) { echo '<option value="' . esc_attr( $sk ) . '" ' . selected( $it['status'] ?? 'todo', $sk, false ) . '>' . esc_html( $svv[0] ) . '</option>'; }
				echo '</select> <input type="text" name="gain" value="' . esc_attr( $it['gain'] ?? '' ) . '" style="width:70px" title="Gain €"> €';
				wp_nonce_field( 'ag_bb' );
				echo ' <button name="ag_bb_quickstatus" value="1" class="button button-small">💾</button>';
				echo '</form></td>';
				echo '<td style="white-space:nowrap"><a href="' . esc_url( admin_url( 'admin.php?page=ag-bugbounty&edit=' . rawurlencode( $it['id'] ) ) ) . '" class="button button-small">✏️</a> ';
				echo '<form method="post" style="display:inline" onsubmit="return confirm(\'Supprimer ce programme du suivi ?\')"><input type="hidden" name="id" value="' . esc_attr( $it['id'] ) . '">';
				wp_nonce_field( 'ag_bb' );
				echo '<button name="ag_bb_del" value="1" class="button button-small button-link-delete">🗑️</button></form></td>';
				echo '</tr>';
			}
			echo '</tbody></table>';
		}

		echo '<p style="color:#646970;font-size:12px;margin-top:16px;max-width:900px">💡 Bonnes pratiques : lis toujours les <em>Program rules</em> + le <em>scope</em>, respecte le <em>rate limit</em> demandé, ne touche pas aux données réelles des utilisateurs, et rédige un rapport clair (étapes de repro + impact + correctif). Sur YesWeHack/Intigriti, un bon rapport = paiement plus rapide.</p>';
		echo '</div>';
	}
}
