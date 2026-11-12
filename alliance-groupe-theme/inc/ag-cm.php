<?php
/**
 * ag-cm.php — LE COMMUNITY MANAGER / CROISSANCE (autonome).
 *
 * Son rôle : que l'équipe TROUVE toujours des clients. Chaque jour, il regarde où
 * ça bloque et RÉORIENTE :
 *   - la chasse est à sec (0 nouveau prospect) → il relance la machine en ajoutant
 *     une nouvelle cible (secteur × ville) depuis une banque curée (borné par le
 *     plafond Places existant) ;
 *   - un segment reçoit des mails sans jamais répondre → il le signale (changer
 *     l'angle ou l'abandonner) ;
 *   - l'email est saturé (plafond atteint) → il pousse vers les canaux alternatifs
 *     (SMS/WhatsApp, robot vocal, ambassadeurs, réseautage, SEO local) ;
 *   - un blocage dur (clé Places, Hugo éteint) → il le remonte en clair.
 *
 * Il agit tout seul là où c'est sûr, et RECOMMANDE le reste (ce qui demande une
 * action humaine : poster, enregistrer une vidéo, mettre une SIM). Branché sur le
 * pilote 24/7 ; sa reco part dans le rapport du soir et par Telegram.
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! function_exists( 'ag_cm_on' ) ) {
	function ag_cm_on() { return (bool) get_option( 'ag_cm_on', 1 ); }
}
if ( ! function_exists( 'ag_cm_autofeed' ) ) {
	function ag_cm_autofeed() { return (bool) get_option( 'ag_cm_autofeed', 1 ); }
}

/* ── Banque de cibles (secteur × ville) pour relancer la chasse ──────── */
if ( ! function_exists( 'ag_cm_bank' ) ) {
	function ag_cm_bank() {
		/* Secteurs recentrés sur LE FILON du canal email (voir docs/FILON.md) :
		   des pros qui ONT un site transactionnel/vitrine à forte valeur (donc
		   joignables par contact@ et avec un vrai budget web) plutôt que des
		   micro-artisans souvent sans site. Depuis le 29/10 (docs/FILON.md) :
		   SANTÉ EN PREMIER (données patients = HDS + RGPD art. 32), puis
		   e-commerce / commerce, immobilier, hôtellerie-tourisme. */
		$sante = ag_cm_bank_sante();
		$secteurs = array( 'prêt-à-porter', 'bijouterie', 'caviste', 'magasin de meubles',
			'épicerie fine', 'concept store', 'agence immobilière', 'hôtel',
			'restaurant', 'camping', 'clinique vétérinaire' );
		$villes = ag_cm_bank_villes();
		$bank = array();
		foreach ( $villes as $v ) { foreach ( $sante as $s ) { $bank[] = array( 'q' => $s, 'city' => $v ); } }
		foreach ( $villes as $v ) { foreach ( $secteurs as $s ) { $bank[] = array( 'q' => $s, 'city' => $v ); } }
		return $bank;
	}
}
if ( ! function_exists( 'ag_cm_bank_villes' ) ) {
	function ag_cm_bank_villes() {
		return (array) apply_filters( 'ag_cm_bank_villes', array( 'Nantes', 'Saint-Nazaire', 'Rennes', 'Angers', 'Vannes',
			'La Roche-sur-Yon', 'Cholet', 'Saint-Herblain', 'Rezé', 'Lorient' ) );
	}
}
if ( ! function_exists( 'ag_cm_bank_sante' ) ) {
	/** Secteurs SANTÉ (segment #1 du filon) : pros qui ont un site, souvent une prise de RDV en ligne,
	 *  donc des données de patients. Les vétérinaires n'en font pas partie (pas de HDS). */
	function ag_cm_bank_sante() {
		return (array) apply_filters( 'ag_cm_bank_sante', array( 'cabinet dentaire', 'orthodontiste',
			'kinésithérapeute', 'ostéopathe', 'laboratoire d\'analyses médicales', 'centre médical',
			'maison de santé', 'cabinet de radiologie', 'ophtalmologue', 'pharmacie' ) );
	}
}
if ( ! function_exists( 'ag_cm_bank_reseed' ) ) {
	/**
	 * Re-semis VERSIONNÉ de la liste de chasse. La liste `ag_auto_searches` est
	 * persistée une fois pour toutes (amorçage), donc changer la banque ne change
	 * RIEN en prod tant qu'on ne la réinjecte pas. Ici : on place les cibles santé
	 * EN TÊTE (sans doublon, sans perdre les cibles ajoutées à la main) et on
	 * remet le pointeur de rotation à 0 → les prochains passages chassent la santé.
	 * Aucune dépense supplémentaire : même nombre de recherches par passage.
	 */
	function ag_cm_bank_reseed() {
		$v = 'sante-v1';
		if ( $v === (string) get_option( 'ag_cm_bank_v', '' ) ) { return 0; }
		$cur = (array) get_option( 'ag_auto_searches', array() );
		$head = array();
		foreach ( ag_cm_bank_villes() as $city ) {
			foreach ( ag_cm_bank_sante() as $q ) { $head[] = array( 'q' => $q, 'city' => $city ); }
		}
		$seen = array(); $out = array();
		foreach ( array_merge( $head, $cur ) as $s ) {
			$k = mb_strtolower( trim( (string) ( $s['q'] ?? '' ) ) . '|' . trim( (string) ( $s['city'] ?? '' ) ) );
			if ( '|' === $k || isset( $seen[ $k ] ) ) { continue; }
			$seen[ $k ] = 1; $out[] = array( 'q' => (string) $s['q'], 'city' => (string) ( $s['city'] ?? '' ) );
		}
		update_option( 'ag_auto_searches', $out, false );
		update_option( 'ag_auto_search_i', 0, false );
		update_option( 'ag_cm_bank_v', $v, false );
		if ( function_exists( 'ag_activity_log' ) ) {
			ag_activity_log( '🩺 Chasse recentrée SANTÉ (filon #1) : ' . count( $head ) . ' cibles santé placées en tête de la liste (' . count( $out ) . ' au total), rotation remise à zéro.' );
		}
		return count( $head );
	}
}

/* ── Compte des événements tunnel sur une fenêtre ────────────────────── */
if ( ! function_exists( 'ag_cm_compte' ) ) {
	function ag_cm_compte( $type, $jours ) {
		$since = time() - $jours * DAY_IN_SECONDS; $n = 0;
		foreach ( (array) get_option( 'ag_funnel_events', array() ) as $e ) {
			if ( (int) ( $e['t'] ?? 0 ) >= $since && (string) ( $e['type'] ?? '' ) === $type ) { $n++; }
		}
		return $n;
	}
}

/* ── Diagnostic de croissance → signaux + recommandations ────────────── */
if ( ! function_exists( 'ag_cm_diagnostic' ) ) {
	function ag_cm_diagnostic() {
		$reco = array();            // recommandations (actions humaines)
		$auto = array();            // actions que le CM a faites tout seul
		$diag = function_exists( 'ag_auto_diag' ) ? ag_auto_diag() : array();
		$ana  = function_exists( 'ag_funnel_analyse' ) ? ag_funnel_analyse( 14 ) : array();

		// 1) Blocages durs (clé Places, Hugo éteint, aucun email…).
		foreach ( (array) ( $diag['bloquants'] ?? array() ) as $b ) { $reco[] = '⛔ ' . $b; }

		// 2) Chasse à sec (aucun nouveau prospect sur 7 jours).
		$sourced7 = ag_cm_compte( 'sourced', 7 );
		if ( 0 === $sourced7 ) {
			$reco[] = '🔭 La chasse ne ramène plus de nouveaux prospects : on élargit les cibles (secteurs/villes).';
		}

		// 3) Segments qui fuient (≥ 8 mails, 0 intéressé) → angle à revoir.
		foreach ( (array) ( $ana['fuites'] ?? array() ) as $metier => $n ) {
			$reco[] = '🧪 Segment « ' . $metier . ' » : ' . (int) $n . ' mails, 0 réponse → changer l\'angle du message (écran Hugo / Max) ou arrêter ce secteur.';
		}

		// 4) Email saturé (plafond atteint plusieurs jours) → canaux alternatifs.
		$jour = (array) get_option( 'ag_closer_jour', array() );
		$sent = ( ( $jour['d'] ?? '' ) === gmdate( 'Ymd' ) ) ? (int) ( $jour['n'] ?? 0 ) : 0;
		$cap  = function_exists( 'ag_closer_cap' ) ? ag_closer_cap() : 0;
		if ( $cap > 0 && $sent >= $cap ) {
			$reco[] = '📣 Email au plafond du jour (' . $sent . '/' . $cap . ') : diversifie les canaux — passerelle SMS/WhatsApp (attend ta SIM), robot vocal (fixes 02/04), relance des ambassadeurs, et post réseaux (contenu prêt dans Studio / recrutement international).';
		}

		// 5) Peu de conversion malgré du volume → revoir l'offre/preuve.
		$rates = (array) ( $ana['rates'] ?? array() );
		$sent14 = ag_cm_compte( 'sent', 14 );
		if ( $sent14 >= 30 && isset( $rates['sent_to_interested'] ) && (float) $rates['sent_to_interested'] < 2.0 ) {
			$reco[] = '📉 Taux de réponse faible (' . $rates['sent_to_interested'] . '% sur 14 j) : tester un nouvel objet (Max), ajouter une preuve concrète (maquette/audit du site du prospect), ou viser un secteur plus chaud.';
		}

		// 6) Pépite à exploiter : le meilleur métier → y mettre plus d'effort.
		$seg = (array) ( $ana['segments'] ?? array() );
		if ( $seg ) {
			$best = array_key_first( $seg );
			if ( $best && (int) ( $seg[ $best ]['interested'] ?? 0 ) > 0 ) {
				$reco[] = '🏆 « ' . $best . ' » convertit le mieux en ce moment : on élargit la chasse sur ce métier dans d\'autres villes.';
			}
		}

		return array( 'reco' => $reco, 'auto' => $auto, 'sourced7' => $sourced7 );
	}
}

/* ── Action autonome : relancer la chasse quand elle est à sec ───────── */
if ( ! function_exists( 'ag_cm_autofeed_chasse' ) ) {
	function ag_cm_autofeed_chasse() {
		if ( ! ag_cm_autofeed() ) { return ''; }
		$searches = (array) get_option( 'ag_auto_searches', array() );
		// On n'ajoute que si la liste est courte (évite d'exploser le quota Places).
		if ( count( $searches ) >= 12 ) { return ''; }
		$bank = ag_cm_bank();
		if ( empty( $bank ) ) { return ''; }
		$i   = (int) get_option( 'ag_cm_bank_i', 0 ) % count( $bank );
		// Trouve la prochaine cible pas déjà présente.
		for ( $k = 0; $k < count( $bank ); $k++ ) {
			$cand = $bank[ ( $i + $k ) % count( $bank ) ];
			$exists = false;
			foreach ( $searches as $s ) {
				if ( strcasecmp( (string) ( $s['q'] ?? '' ), $cand['q'] ) === 0 && strcasecmp( (string) ( $s['city'] ?? '' ), $cand['city'] ) === 0 ) { $exists = true; break; }
			}
			if ( ! $exists ) {
				$searches[] = array( 'q' => $cand['q'], 'city' => $cand['city'] );
				update_option( 'ag_auto_searches', $searches, false );
				update_option( 'ag_cm_bank_i', ( $i + $k + 1 ) % count( $bank ), false );
				return $cand['q'] . ' — ' . $cand['city'];
			}
		}
		return '';
	}
}

/* ── Reco du jour (texte) + stockage ─────────────────────────────────── */
if ( ! function_exists( 'ag_cm_reco_texte' ) ) {
	function ag_cm_reco_texte() {
		$d = ag_cm_diagnostic();
		$lignes = array();
		// Action auto (chasse relancée) si à sec.
		if ( 0 === (int) $d['sourced7'] ) {
			$ajout = ag_cm_autofeed_chasse();
			if ( '' !== $ajout ) { $lignes[] = '✅ Chasse relancée : nouvelle cible ajoutée → ' . $ajout . '.'; }
		}
		foreach ( (array) $d['reco'] as $r ) { $lignes[] = $r; }
		if ( empty( $lignes ) ) { $lignes[] = '✅ Rien à débloquer : la machine tourne, on laisse le volume et le temps faire leur travail.'; }
		return implode( "\n", $lignes );
	}
}

/* ── Cadence : 1×/jour, branché sur le pilote ────────────────────────── */
if ( ! function_exists( 'ag_cm_cron_maybe' ) ) {
	function ag_cm_cron_maybe() {
		if ( ! ag_cm_on() ) { return false; }
		if ( time() - (int) get_option( 'ag_cm_last', 0 ) < 72000 ) { return false; } // ~20 h
		update_option( 'ag_cm_last', time(), false );
		$txt = ag_cm_reco_texte();
		update_option( 'ag_cm_reco', array( 'quand' => time(), 'texte' => $txt ), false );
		return true;
	}
}

/* ── Écran : Prospection → 🚀 Croissance (Community Manager) ──────────── */
add_action( 'admin_menu', function () {
	add_submenu_page( 'ag-prospects', 'Croissance', '🚀 Croissance', 'manage_options', 'ag-cm', 'ag_cm_render' );
}, 23 );

add_action( 'admin_init', function () {
	if ( isset( $_POST['ag_cm_save'] ) && check_admin_referer( 'ag_cm' ) && current_user_can( 'manage_options' ) ) {
		update_option( 'ag_cm_on', isset( $_POST['on'] ) ? 1 : 0, false );
		update_option( 'ag_cm_autofeed', isset( $_POST['autofeed'] ) ? 1 : 0, false );
	}
} );

if ( ! function_exists( 'ag_cm_render' ) ) {
	function ag_cm_render() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		echo '<div class="wrap"><h1>🚀 Croissance — Community Manager</h1>';
		echo '<p class="description">Chaque jour, il cherche où ça bloque et réoriente l\'équipe vers d\'autres sources de clients. Il relance la chasse tout seul quand elle est à sec, et te recommande les pivots (dans le rapport du soir aussi).</p>';

		echo '<form method="post" style="margin:10px 0 18px">';
		wp_nonce_field( 'ag_cm' );
		echo '<p><label><input type="checkbox" name="on" ' . checked( ag_cm_on(), true, false ) . '> Community Manager actif</label></p>';
		echo '<p><label><input type="checkbox" name="autofeed" ' . checked( ag_cm_autofeed(), true, false ) . '> Relancer automatiquement la chasse quand elle est à sec (ajoute des cibles, borné par le plafond Places)</label></p>';
		echo '<button class="button button-primary" name="ag_cm_save" value="1">Enregistrer</button>';
		echo '</form>';

		echo '<h2>Recommandations du jour</h2>';
		echo '<div style="background:#f0f6fc;border-left:4px solid #0073aa;padding:12px 16px;max-width:900px"><pre style="white-space:pre-wrap;margin:0;font-family:inherit">' . esc_html( ag_cm_reco_texte() ) . '</pre></div>';
		echo '</div>';
	}
}
