<?php
/**
 * Alliance Groupe — Zones ambassadeurs (territoires par département).
 *
 * Modèle : 1 zone = 1 département. Une zone peut avoir PLUSIEURS ambassadeurs
 * (co-propriété). Les prospects de la zone sont distribués équitablement
 * entre eux (rotation 50/50) → pas de concurrence, on bosse ensemble.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ── Message de recrutement d'ambassadeurs (réutilisable) ──────────── */
if ( ! function_exists( 'ag_recruit_message' ) ) {
	function ag_recruit_message() {
		$link = home_url( '/ambassadeurs' );
		return "💸 Salut{prenom} ! Tu veux un revenu en plus, simple et flexible ?\n\nDeviens *ambassadeur Alliance Groupe* : tu présentes nos sites web à des commerçants/artisans de ta ville, et tu touches *10 % sur chaque vente, à vie* — payé sur PayPal, sans plafond.\n\n📊 *Le vrai potentiel* (commission de 10 %) :\n• 1 site Pro vendu (890 €) = *89 € pour toi*\n• 1 vente/semaine = *4 624 €/an*\n• 3 ventes/semaine = *13 884 €/an*\n• 6 ventes/semaine = *2 312 €/mois* = *27 768 €/an*\n\n✅ Aucune expérience requise\n✅ Outils & formation fournis\n✅ Tu bosses quand tu veux, depuis ton téléphone\n\n👉 Calcule ton revenu + inscris-toi : {$link}\n\nDes questions ? Réponds à ce message 🙂";
	}
}

/* ── CRM de recrutement (futurs ambassadeurs contactés) ───────────── */
if ( ! function_exists( 'ag_recruit_statuses' ) ) {
	function ag_recruit_statuses() {
		return array(
			'a_contacter'      => '🆕 À contacter',
			'contacte'         => '📞 Contacté',
			'repondu'          => '💬 A répondu',
			'interesse'        => '🔥 Intéressé',
			'pas_interesse'    => '🙁 Pas intéressé',
			'inscrit'          => '✅ Inscrit (ambassadeur)',
			'ne_pas_contacter' => '🚫 Ne plus contacter',
		);
	}
}
if ( ! function_exists( 'ag_recruit_replied' ) ) {
	/** A donné une réponse (peu importe laquelle). */
	function ag_recruit_replied( $status ) { return in_array( $status, array( 'repondu', 'interesse', 'pas_interesse', 'inscrit' ), true ); }
}
if ( ! function_exists( 'ag_recruits_get' ) ) {
	function ag_recruits_get() { return (array) get_option( 'ag_recruits', array() ); }
}
if ( ! function_exists( 'ag_recruit_phone_norm' ) ) {
	function ag_recruit_phone_norm( $raw ) { return preg_replace( '/[^0-9]/', '', (string) $raw ); }
}
if ( ! function_exists( 'ag_recruits_add_line' ) ) {
	/** Ajoute un futur ambassadeur (anti-doublon par numéro). Retourne true si ajouté. */
	function ag_recruits_add_line( $phone, $name ) {
		$phone = trim( (string) $phone ); $name = trim( (string) $name );
		$norm  = ag_recruit_phone_norm( $phone );
		if ( '' === $norm ) return false;
		$list = ag_recruits_get();
		foreach ( $list as &$r ) {
			if ( ag_recruit_phone_norm( $r['phone'] ?? '' ) === $norm ) {
				if ( '' !== $name && '' === trim( (string) ( $r['name'] ?? '' ) ) ) { $r['name'] = $name; update_option( 'ag_recruits', $list, false ); }
				return false; // déjà dans la liste
			}
		}
		unset( $r );
		$list[] = array( 'id' => uniqid( 'r' ), 'name' => $name, 'phone' => $phone, 'status' => 'a_contacter', 'note' => '', 'ts' => time() );
		update_option( 'ag_recruits', $list, false );
		return true;
	}
}

/* ── Données ────────────────────────────────────────────────────── */
if ( ! function_exists( 'ag_zones_get' ) ) {
	/** dept(2) => array( 'owners' => [ {email,name,ts} ], 'rr' => int ) */
	function ag_zones_get() { return (array) get_option( 'ag_zones', array() ); }
}
if ( ! function_exists( 'ag_dept_norm' ) ) {
	function ag_dept_norm( $d ) {
		$d = (string) $d;
		// Zones Maroc : passent telles quelles (ex. MA-CAS).
		if ( 0 === strpos( $d, 'MA-' ) ) return strtoupper( substr( $d, 0, 6 ) );
		return substr( preg_replace( '/[^0-9]/', '', $d ), 0, 2 );
	}
}
if ( ! function_exists( 'ag_ma_cities' ) ) {
	/** Villes principales du Maroc → région (MA-XXX). Clés en minuscules, sans accents. */
	function ag_ma_cities() {
		return array(
			'casablanca' => 'MA-CAS', 'casa' => 'MA-CAS', 'mohammedia' => 'MA-CAS', 'settat' => 'MA-CAS', 'el jadida' => 'MA-CAS', 'berrechid' => 'MA-CAS',
			'rabat' => 'MA-RAB', 'sale' => 'MA-RAB', 'kenitra' => 'MA-RAB', 'temara' => 'MA-RAB', 'skhirat' => 'MA-RAB',
			'marrakech' => 'MA-MAR', 'safi' => 'MA-MAR', 'essaouira' => 'MA-MAR', 'youssoufia' => 'MA-MAR',
			'tanger' => 'MA-TAN', 'tetouan' => 'MA-TAN', 'al hoceima' => 'MA-TAN', 'larache' => 'MA-TAN', 'chefchaouen' => 'MA-TAN', 'asilah' => 'MA-TAN',
			'fes' => 'MA-FES', 'meknes' => 'MA-FES', 'taza' => 'MA-FES', 'ifrane' => 'MA-FES', 'sefrou' => 'MA-FES',
			'oujda' => 'MA-ORI', 'nador' => 'MA-ORI', 'berkane' => 'MA-ORI', 'taourirt' => 'MA-ORI', 'jerada' => 'MA-ORI',
			'agadir' => 'MA-SOU', 'inezgane' => 'MA-SOU', 'tiznit' => 'MA-SOU', 'taroudant' => 'MA-SOU', 'ait melloul' => 'MA-SOU',
			'beni mellal' => 'MA-BEN', 'khouribga' => 'MA-BEN', 'khenifra' => 'MA-BEN', 'fkih ben salah' => 'MA-BEN',
			'errachidia' => 'MA-DRA', 'ouarzazate' => 'MA-DRA', 'tinghir' => 'MA-DRA', 'midelt' => 'MA-DRA', 'zagora' => 'MA-DRA',
			'guelmim' => 'MA-GUE', 'tan tan' => 'MA-GUE', 'sidi ifni' => 'MA-GUE',
			'laayoune' => 'MA-LAA', 'boujdour' => 'MA-LAA',
			'dakhla' => 'MA-DAK',
		);
	}
}
if ( ! function_exists( 'ag_ma_dept_from_text' ) ) {
	/** Cherche une ville marocaine dans le texte → renvoie la région MA-XXX. */
	function ag_ma_dept_from_text( $text ) {
		$t = function_exists( 'remove_accents' ) ? remove_accents( (string) $text ) : (string) $text;
		$t = strtolower( $t );
		if ( '' === trim( $t ) ) return '';
		foreach ( ag_ma_cities() as $city => $dept ) {
			if ( preg_match( '/\b' . preg_quote( $city, '/' ) . '\b/', $t ) ) return $dept;
		}
		return '';
	}
}
if ( ! function_exists( 'ag_dept_from_text' ) ) {
	function ag_dept_from_text( $text ) {
		$t = (string) $text;
		// Si le pays Maroc est explicitement marqué → tente d'abord la détection MA
		// (les codes postaux MA 20000/10000/40000 entreraient sinon en collision avec
		// les regex de codes postaux FR).
		if ( preg_match( '/\b(maroc|morocco|kingdom of morocco)\b/i', $t ) ) {
			$d = ag_ma_dept_from_text( $t );
			if ( '' !== $d ) return $d;
		}
		// Code postal FR (XX XXX).
		if ( preg_match( '/\b(\d{2})\d{3}\b/', $t, $m ) ) return $m[1];
		// Sinon, tente de matcher une ville marocaine connue.
		return ag_ma_dept_from_text( $t );
	}
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
/* ── Quota de zones : 1 gratuite + zones supplémentaires payées/accordées ── */
if ( ! function_exists( 'ag_zone_extra' ) ) {
	function ag_zone_extra( $uid ) { return max( 0, (int) get_user_meta( $uid, 'ag_zone_extra', true ) ); }
}
if ( ! function_exists( 'ag_zone_quota' ) ) {
	/** Nombre TOTAL de zones autorisées pour un ambassadeur (1 + payées). Admin = illimité. */
	function ag_zone_quota( $email ) {
		$u = get_user_by( 'email', strtolower( (string) $email ) );
		if ( ! $u ) return 1;
		if ( user_can( $u->ID, 'manage_options' ) ) return 999;
		return 1 + ag_zone_extra( $u->ID );
	}
}
if ( ! function_exists( 'ag_zone_price' ) ) {
	function ag_zone_price() { return (float) get_option( 'ag_zone_price', 49 ); }
}
if ( ! function_exists( 'ag_zone_extra_activate_by_email' ) ) {
	/** Paiement d'une zone supplémentaire (~prix) : +1 au quota de l'ambassadeur, automatiquement. */
	function ag_zone_extra_activate_by_email( $email, $amount ) {
		$price = ag_zone_price();
		if ( $price <= 0 || abs( (float) $amount - $price ) > 1.5 ) return false;
		$u = get_user_by( 'email', trim( (string) $email ) );
		if ( ! $u ) return false;
		update_user_meta( $u->ID, 'ag_zone_extra', ag_zone_extra( $u->ID ) + 1 );
		if ( function_exists( 'ag_push' ) ) ag_push( '🗺️ Zone supplémentaire achetée', ( $u->display_name ?: $email ) . ' a payé une zone en plus — il peut en prendre une de plus.' );
		if ( function_exists( 'ag_activity_log' ) ) ag_activity_log( '🗺️ ' . ( $u->display_name ?: $email ) . ' a acheté une zone supplémentaire' );
		return true;
	}
}
/* ── Activité ambassadeur : on garde la zone à vie, sauf inactivité 7 jours ── */
if ( ! function_exists( 'ag_amb_mark_active' ) ) {
	function ag_amb_mark_active( $uid = 0 ) {
		$uid = $uid ?: get_current_user_id();
		if ( ! $uid ) return;
		$u = get_user_by( 'id', $uid );
		if ( $u && in_array( 'ag_ambassadeur', (array) $u->roles, true ) ) update_user_meta( $uid, 'ag_amb_last_active', time() );
	}
}
add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'ag_amb_inactivity_cron' ) ) wp_schedule_event( strtotime( 'tomorrow 8:00' ), 'daily', 'ag_amb_inactivity_cron' );
} );
add_action( 'ag_amb_inactivity_cron', function () {
	$limit = 7 * DAY_IN_SECONDS;
	$now   = time();
	foreach ( get_users( array( 'role' => 'ag_ambassadeur' ) ) as $u ) {
		$zones = ag_zone_of_owner( $u->user_email );
		if ( empty( $zones ) ) continue; // pas de zone = rien à retirer
		$last = (int) get_user_meta( $u->ID, 'ag_amb_last_active', true );
		if ( ! $last ) { $last = strtotime( $u->user_registered ) ?: $now; }
		if ( ( $now - $last ) < $limit ) continue; // actif récemment : il garde sa zone
		foreach ( $zones as $d ) ag_zone_remove_owner( $d, $u->user_email );
		update_user_meta( $u->ID, 'ag_amb_zone_paused', $now );
		$nm = $u->display_name ?: $u->user_email;
		wp_mail(
			$u->user_email,
			'⏸️ Ta zone est en pause (inactivité) — reprends-la !',
			"Salut $nm,\n\nTu n'as pas été actif depuis 7 jours, alors ta zone (" . implode( ', ', $zones ) . ") a été libérée pour laisser la place à l'équipe.\nAucun souci : reconnecte-toi et reprends une zone en 1 clic depuis ton espace.\n\n👉 " . home_url( '/espace-ambassadeur#demarrage' ) . "\n\nOn t'attend 💪\nAlliance Groupe"
		);
		if ( function_exists( 'ag_push' ) ) ag_push( '⏸️ Zone libérée (inactivité)', $nm . ' retiré de sa zone (' . implode( ', ', $zones ) . ') après 7 j sans activité. Rappel envoyé.' ); // alerte perso admin, pas le groupe
		if ( function_exists( 'ag_activity_log' ) ) ag_activity_log( '⏸️ ' . $nm . ' retiré de sa zone pour inactivité (7 j)' );
	}
} );
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
			// Maroc — 12 régions administratives.
			'MA-CAS' => '🇲🇦 Casablanca-Settat',
			'MA-RAB' => '🇲🇦 Rabat-Salé-Kénitra',
			'MA-MAR' => '🇲🇦 Marrakech-Safi',
			'MA-TAN' => '🇲🇦 Tanger-Tétouan-Al Hoceïma',
			'MA-FES' => '🇲🇦 Fès-Meknès',
			'MA-ORI' => '🇲🇦 Oriental',
			'MA-SOU' => '🇲🇦 Souss-Massa',
			'MA-BEN' => '🇲🇦 Béni Mellal-Khénifra',
			'MA-DRA' => '🇲🇦 Drâa-Tafilalet',
			'MA-GUE' => '🇲🇦 Guelmim-Oued Noun',
			'MA-LAA' => '🇲🇦 Laâyoune-Sakia El Hamra',
			'MA-DAK' => '🇲🇦 Dakhla-Oued Ed Dahab',
		);
	}
}
if ( ! function_exists( 'ag_dept_from_city' ) ) {
	/** Devine le département depuis un nom de ville (grandes communes françaises). */
	function ag_dept_from_city( $city ) {
		$n = function_exists( 'remove_accents' ) ? remove_accents( (string) $city ) : (string) $city;
		$n = preg_replace( '/[^a-z]/', '', strtolower( $n ) );
		if ( '' === $n ) return '';
		$map = array(
			'paris' => '75',
			'marseille' => '13', 'aixenprovence' => '13', 'aubagne' => '13', 'martigues' => '13', 'arles' => '13', 'salondeprovence' => '13',
			'lyon' => '69', 'villeurbanne' => '69', 'venissieux' => '69',
			'toulouse' => '31', 'colomiers' => '31',
			'nice' => '06', 'cannes' => '06', 'antibes' => '06', 'grasse' => '06', 'cagnessurmer' => '06',
			'nantes' => '44', 'saintnazaire' => '44', 'saintherblain' => '44', 'reze' => '44',
			'montpellier' => '34', 'beziers' => '34', 'sete' => '34',
			'strasbourg' => '67', 'haguenau' => '67',
			'bordeaux' => '33', 'merignac' => '33', 'pessac' => '33', 'talence' => '33', 'arcachon' => '33',
			'lille' => '59', 'roubaix' => '59', 'tourcoing' => '59', 'dunkerque' => '59', 'villeneuvedascq' => '59', 'valenciennes' => '59',
			'rennes' => '35', 'saintmalo' => '35', 'fougeres' => '35',
			'reims' => '51', 'chalonsenchampagne' => '51', 'epernay' => '51',
			'saintetienne' => '42', 'roanne' => '42',
			'toulon' => '83', 'laseynesurmer' => '83', 'hyeres' => '83', 'frejus' => '83', 'draguignan' => '83',
			'grenoble' => '38', 'echirolles' => '38', 'vienne' => '38',
			'dijon' => '21', 'beaune' => '21',
			'angers' => '49', 'cholet' => '49', 'saumur' => '49',
			'nimes' => '30', 'ales' => '30',
			'clermontferrand' => '63', 'thiers' => '63',
			'lehavre' => '76', 'rouen' => '76', 'dieppe' => '76', 'sottevillelesrouen' => '76',
			'argenteuil' => '95', 'cergy' => '95', 'pontoise' => '95', 'sarcelles' => '95',
			'montreuil' => '93', 'aubervilliers' => '93', 'bobigny' => '93', 'drancy' => '93', 'pantin' => '93', 'saintdenis' => '93', 'aulnaysousbois' => '93', 'noisylegrand' => '93',
			'nanterre' => '92', 'boulognebillancourt' => '92', 'courbevoie' => '92', 'colombes' => '92', 'asnieressurseine' => '92', 'antony' => '92', 'levalloisperret' => '92', 'issylesmoulineaux' => '92', 'rueilmalmaison' => '92', 'neuillysurseine' => '92', 'clichy' => '92', 'meudon' => '92', 'clamart' => '92',
			'creteil' => '94', 'vitrysurseine' => '94', 'vincennes' => '94', 'ivrysurseine' => '94', 'maisonsalfort' => '94', 'champignysurmarne' => '94', 'fontenaysousbois' => '94',
			'versailles' => '78', 'saintgermainenlaye' => '78', 'manteslajolie' => '78', 'sartrouville' => '78', 'poissy' => '78',
			'melun' => '77', 'meaux' => '77', 'chelles' => '77',
			'evry' => '91', 'corbeilessonnes' => '91', 'massy' => '91', 'savignysurorge' => '91', 'palaiseau' => '91',
			'nancy' => '54', 'vandoeuvrelesnancy' => '54',
			'metz' => '57', 'thionville' => '57',
			'mulhouse' => '68', 'colmar' => '68',
			'besancon' => '25', 'montbeliard' => '25',
			'caen' => '14', 'lisieux' => '14',
			'orleans' => '45', 'montargis' => '45',
			'tours' => '37', 'joue' => '37',
			'limoges' => '87',
			'amiens' => '80', 'abbeville' => '80',
			'perpignan' => '66',
			'brest' => '29', 'quimper' => '29', 'morlaix' => '29',
			'lorient' => '56', 'vannes' => '56', 'lanester' => '56',
			'pau' => '64', 'bayonne' => '64', 'biarritz' => '64', 'anglet' => '64',
			'bourges' => '18', 'vierzon' => '18',
			'avignon' => '84', 'carpentras' => '84', 'orange' => '84',
			'annecy' => '74', 'annemasse' => '74', 'thononlesbains' => '74', 'chamonix' => '74',
			'chambery' => '73', 'aixlesbains' => '73',
			'valence' => '26', 'montelimar' => '26',
			'troyes' => '10',
			'poitiers' => '86', 'chatellerault' => '86',
			'larochelle' => '17', 'rochefort' => '17', 'saintes' => '17',
			'angouleme' => '16', 'cognac' => '16',
			'niort' => '79',
			'chartres' => '28', 'dreux' => '28',
			'blois' => '41', 'vendome' => '41',
			'lemans' => '72',
			'laval' => '53',
			'cherbourg' => '50', 'saintlo' => '50',
			'evreux' => '27', 'vernon' => '27',
			'beauvais' => '60', 'compiegne' => '60', 'creil' => '60',
			'arras' => '62', 'calais' => '62', 'boulognesurmer' => '62', 'lens' => '62', 'bethune' => '62',
			'charlevillemezieres' => '08', 'sedan' => '08',
			'belfort' => '90',
			'chaumont' => '52', 'saintdizier' => '52',
			'epinal' => '88',
			'barleduc' => '55', 'verdun' => '55',
			'macon' => '71', 'chalonsursaone' => '71',
			'nevers' => '58',
			'auxerre' => '89', 'sens' => '89',
			'moulins' => '03', 'montlucon' => '03', 'vichy' => '03',
			'aurillac' => '15',
			'lepuyenvelay' => '43',
			'mende' => '48',
			'rodez' => '12', 'millau' => '12',
			'albi' => '81', 'castres' => '81',
			'montauban' => '82',
			'cahors' => '46',
			'agen' => '47',
			'montdemarsan' => '40', 'dax' => '40',
			'tarbes' => '65', 'lourdes' => '65',
			'foix' => '09', 'pamiers' => '09',
			'carcassonne' => '11', 'narbonne' => '11',
			'gap' => '05',
			'dignelesbains' => '04', 'manosque' => '04',
			'privas' => '07', 'aubenas' => '07',
			'bourgenbresse' => '01', 'oyonnax' => '01',
			'saintbrieuc' => '22', 'lannion' => '22', 'dinan' => '22',
			'larochesuryon' => '85', 'lessablesdolonne' => '85', 'challans' => '85',
			'ajaccio' => '20', 'bastia' => '20',
			'gueret' => '23',
			'tulle' => '19', 'brive' => '19', 'brivelagaillarde' => '19',
			'perigueux' => '24', 'bergerac' => '24',
			'auch' => '32',
			// Maroc — villes principales (sans espaces ni accents).
			'casablanca' => 'MA-CAS', 'casa' => 'MA-CAS', 'mohammedia' => 'MA-CAS', 'settat' => 'MA-CAS', 'eljadida' => 'MA-CAS', 'berrechid' => 'MA-CAS',
			'rabat' => 'MA-RAB', 'sale' => 'MA-RAB', 'kenitra' => 'MA-RAB', 'temara' => 'MA-RAB', 'skhirat' => 'MA-RAB',
			'marrakech' => 'MA-MAR', 'safi' => 'MA-MAR', 'essaouira' => 'MA-MAR', 'youssoufia' => 'MA-MAR',
			'tanger' => 'MA-TAN', 'tetouan' => 'MA-TAN', 'alhoceima' => 'MA-TAN', 'larache' => 'MA-TAN', 'chefchaouen' => 'MA-TAN', 'asilah' => 'MA-TAN',
			'fes' => 'MA-FES', 'meknes' => 'MA-FES', 'taza' => 'MA-FES', 'ifrane' => 'MA-FES', 'sefrou' => 'MA-FES',
			'oujda' => 'MA-ORI', 'nador' => 'MA-ORI', 'berkane' => 'MA-ORI', 'taourirt' => 'MA-ORI', 'jerada' => 'MA-ORI',
			'agadir' => 'MA-SOU', 'inezgane' => 'MA-SOU', 'tiznit' => 'MA-SOU', 'taroudant' => 'MA-SOU', 'aitmelloul' => 'MA-SOU',
			'benimellal' => 'MA-BEN', 'khouribga' => 'MA-BEN', 'khenifra' => 'MA-BEN', 'fkihbensalah' => 'MA-BEN',
			'errachidia' => 'MA-DRA', 'ouarzazate' => 'MA-DRA', 'tinghir' => 'MA-DRA', 'midelt' => 'MA-DRA', 'zagora' => 'MA-DRA',
			'guelmim' => 'MA-GUE', 'tantan' => 'MA-GUE', 'sidiifni' => 'MA-GUE',
			'laayoune' => 'MA-LAA', 'boujdour' => 'MA-LAA',
			'dakhla' => 'MA-DAK',
		);
		return $map[ $n ] ?? '';
	}
}
if ( ! function_exists( 'ag_dept_from_location' ) ) {
	/** Département à partir du code postal (prioritaire), sinon adresse, sinon nom de ville. */
	function ag_dept_from_location( $city = '', $cp = '', $address = '' ) {
		$cpd = preg_replace( '/[^0-9]/', '', (string) $cp );
		if ( strlen( $cpd ) >= 2 ) { $d = substr( $cpd, 0, 2 ); return ( '97' === $d || '98' === $d ) ? '97' : $d; }
		$d = ag_dept_from_text( $address ); if ( '' !== $d ) return $d; // code postal présent dans l'adresse
		$d = ag_dept_from_text( $city );    if ( '' !== $d ) return $d;
		return ag_dept_from_city( $city );
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

			<!-- Recruter des ambassadeurs (SMS / WhatsApp) -->
			<div style="background:#fff;border:1px solid #ccd0d4;border-left:4px solid #1e7e34;border-radius:8px;padding:16px 18px;margin:12px 0 22px;max-width:980px;">
				<h2 style="margin-top:0;">📣 Recruter des ambassadeurs (SMS / WhatsApp)</h2>
				<p style="color:#50575e;font-size:.9rem;">Une ligne par personne, au format <code>numéro, prénom</code> (le prénom est optionnel). Le message est <strong>personnalisé automatiquement</strong> : <code>{prenom}</code> est remplacé par le prénom de chacun. Clique <strong>Générer</strong> → tu obtiens un lien <strong>SMS</strong> (depuis ton tél) et <strong>WhatsApp</strong> par personne, message + lien d'inscription + gains déjà dedans.</p>
				<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;align-items:start;">
					<div>
						<label style="font-weight:600;display:block;margin-bottom:4px;">Numéros &amp; prénoms (un par ligne)</label>
						<textarea id="ag-recruit-nums" rows="6" style="width:100%;" placeholder="0612345678, Marc&#10;0699887766, Sofia&#10;0788990011"></textarea>
					</div>
					<div>
						<label style="font-weight:600;display:block;margin-bottom:4px;">Message <span style="font-weight:400;color:#646970;">(utilise <code>{prenom}</code>)</span></label>
						<textarea id="ag-recruit-msg" rows="6" style="width:100%;"><?php echo esc_textarea( ag_recruit_message() ); ?></textarea>
					</div>
				</div>
				<p style="margin:10px 0 0;">
					<button type="button" class="button button-primary" id="ag-recruit-go">Générer les liens personnalisés</button>
					<button type="button" class="button" id="ag-recruit-save" style="margin-left:6px;">💾 Enregistrer dans ma liste</button>
					<span style="color:#646970;font-size:.85em;margin-left:6px;">(les garde pour suivre qui a répondu)</span>
				</p>
				<p style="margin:6px 0 0;color:#646970;font-size:.85em;">📱 Pour envoyer avec ta <strong>ligne Free</strong> : iPhone → Réglages → Messages → <em>Envoyer depuis</em> → choisis ta ligne Free (ou choisis-la en haut de la conversation). Les liens « SMS » utilisent la ligne par défaut de ton tél.</p>
				<form method="post" action="<?php echo esc_url( $post ); ?>" id="ag-recruit-save-form" style="display:none;">
					<input type="hidden" name="action" value="ag_recruits_add">
					<?php wp_nonce_field( 'ag_recruit_crm', '_n' ); ?>
					<input type="hidden" name="lines" id="ag-recruit-lines" value="">
				</form>
				<div id="ag-recruit-out" style="margin-top:12px;"></div>
				<script>
				(function(){
					var go=document.getElementById('ag-recruit-go'); if(!go) return;
					function wa(num){ var d=(num||'').replace(/[^0-9]/g,''); if(d.indexOf('00')===0)d=d.substr(2); if(d.charAt(0)==='0')d='33'+d.replace(/^0+/,''); return d; }
					function esc(s){ return (s||'').replace(/[<>&]/g,function(c){return {'<':'&lt;','>':'&gt;','&':'&amp;'}[c];}); }
					go.addEventListener('click',function(){
						var lines=document.getElementById('ag-recruit-nums').value.split(/\n+/).map(function(s){return s.trim();}).filter(Boolean);
						var tpl=document.getElementById('ag-recruit-msg').value;
						var out=document.getElementById('ag-recruit-out'); if(!lines.length){ out.innerHTML='<em>Ajoute au moins un numéro.</em>'; return; }
						var h='<table class="widefat striped"><thead><tr><th>Prénom</th><th>Numéro</th><th>Envoyer</th></tr></thead><tbody>';
						lines.forEach(function(line){
							var m=line.match(/^\s*([+0-9 ().-]{6,})\s*[,;:–-]?\s*(.*)$/);
							var rawNum=m?m[1].trim():line;
							var name=m?m[2].trim():'';
							var num=rawNum.replace(/[^0-9+]/g,'');
							var token=name?' '+name:'';                       // "Salut{prenom} !" -> "Salut Marc !" / "Salut !"
							var msg=tpl.replace(/\{prenom\}/g,token);
							var sms='sms:'+num+'?body='+encodeURIComponent(msg);
							var w='https://wa.me/'+wa(rawNum)+'?text='+encodeURIComponent(msg);
							h+='<tr><td><strong>'+esc(name||'—')+'</strong></td><td>'+esc(num)+'</td><td><a class="button button-small" href="'+sms+'">📱 SMS</a> <a class="button button-small" target="_blank" rel="noopener" href="'+w+'">WhatsApp</a></td></tr>';
						});
						h+='</tbody></table>'; out.innerHTML=h;
					});
					var sv=document.getElementById('ag-recruit-save');
					if(sv) sv.addEventListener('click',function(){
						var v=document.getElementById('ag-recruit-nums').value.trim();
						if(!v){ alert('Ajoute au moins un numéro.'); return; }
						document.getElementById('ag-recruit-lines').value=v;
						document.getElementById('ag-recruit-save-form').submit();
					});
				})();
				</script>
			</div>

			<!-- CRM de recrutement : suivi des futurs ambassadeurs -->
			<?php
			$recruits = ag_recruits_get();
			$rlabels  = ag_recruit_statuses();
			$rf       = isset( $_GET['rf'] ) ? sanitize_text_field( wp_unslash( $_GET['rf'] ) ) : '';
			$rsort    = isset( $_GET['rsort'] ) ? sanitize_text_field( wp_unslash( $_GET['rsort'] ) ) : 'date';
			// Comptes par statut + "ont répondu".
			$rc = array_fill_keys( array_keys( $rlabels ), 0 ); $rc_rep = 0;
			foreach ( $recruits as $r ) { $st = $r['status'] ?? 'a_contacter'; if ( isset( $rc[ $st ] ) ) $rc[ $st ]++; if ( ag_recruit_replied( $st ) ) $rc_rep++; }
			// Filtre.
			$rview = $recruits;
			if ( 'repondu_all' === $rf ) { $rview = array_filter( $rview, function ( $r ) { return ag_recruit_replied( $r['status'] ?? '' ); } ); }
			elseif ( '' !== $rf ) { $rview = array_filter( $rview, function ( $r ) use ( $rf ) { return ( $r['status'] ?? 'a_contacter' ) === $rf; } ); }
			$rview = array_values( $rview );
			usort( $rview, function ( $a, $b ) use ( $rsort, $rlabels ) {
				if ( 'nom' === $rsort )    return strcasecmp( $a['name'] ?? '', $b['name'] ?? '' );
				if ( 'statut' === $rsort ) return strcmp( $a['status'] ?? '', $b['status'] ?? '' );
				return ( $b['ts'] ?? 0 ) <=> ( $a['ts'] ?? 0 );
			} );
			$base = admin_url( 'admin.php?page=ag-zones' );
			?>
			<div id="recruits" style="background:#fff;border:1px solid #ccd0d4;border-left:4px solid #2271b1;border-radius:8px;padding:16px 18px;margin:0 0 22px;max-width:980px;">
				<h2 style="margin-top:0;">📇 Mes futurs ambassadeurs (<?php echo count( $recruits ); ?>)</h2>
				<p style="color:#50575e;font-size:.9rem;">Tout ce que tu as déjà contacté est gardé ici. Édite le prénom, change le statut selon leur réponse, relance par SMS/WhatsApp. Trie par <strong>qui a répondu</strong> ou par réponse.</p>
				<?php if ( empty( $recruits ) ) : ?>
					<p style="color:#646970;">Personne encore. Génère des liens ci-dessus puis clique <strong>« 💾 Enregistrer dans ma liste »</strong>.</p>
				<?php else : ?>
					<!-- Puces de classement -->
					<div style="display:flex;flex-wrap:wrap;gap:8px;margin:4px 0 14px;">
						<a href="<?php echo esc_url( $base ); ?>" class="button button-small<?php echo '' === $rf ? ' button-primary' : ''; ?>">Tous (<?php echo count( $recruits ); ?>)</a>
						<a href="<?php echo esc_url( add_query_arg( 'rf', 'repondu_all', $base ) ); ?>" class="button button-small<?php echo 'repondu_all' === $rf ? ' button-primary' : ''; ?>">💬 Ont répondu (<?php echo (int) $rc_rep; ?>)</a>
						<?php foreach ( $rlabels as $sk => $sl ) : ?>
							<a href="<?php echo esc_url( add_query_arg( 'rf', $sk, $base ) ); ?>" class="button button-small<?php echo $rf === $sk ? ' button-primary' : ''; ?>"><?php echo esc_html( $sl ); ?> (<?php echo (int) $rc[ $sk ]; ?>)</a>
						<?php endforeach; ?>
					</div>
					<p style="margin:0 0 8px;font-size:.85rem;color:#646970;">Trier :
						<a href="<?php echo esc_url( add_query_arg( array( 'rf' => $rf, 'rsort' => 'date' ), $base ) ); ?>"<?php echo 'date' === $rsort ? ' style="font-weight:700;"' : ''; ?>>récent</a> ·
						<a href="<?php echo esc_url( add_query_arg( array( 'rf' => $rf, 'rsort' => 'nom' ), $base ) ); ?>"<?php echo 'nom' === $rsort ? ' style="font-weight:700;"' : ''; ?>>prénom</a> ·
						<a href="<?php echo esc_url( add_query_arg( array( 'rf' => $rf, 'rsort' => 'statut' ), $base ) ); ?>"<?php echo 'statut' === $rsort ? ' style="font-weight:700;"' : ''; ?>>statut</a>
					</p>
					<div style="display:flex;gap:8px;font-weight:600;color:#646970;font-size:.82rem;padding:0 4px 4px;border-bottom:1px solid #e0e0e0;">
						<span style="width:130px;">Prénom</span><span style="width:130px;">Numéro</span><span style="width:190px;">Statut (réponse)</span><span style="width:150px;">Note</span><span style="flex:1;">Contacter / fiche</span>
					</div>
					<?php foreach ( $rview as $r ) :
						$rid = $r['id'] ?? ''; $rname = $r['name'] ?? ''; $rphone = $r['phone'] ?? ''; $rstatus = $r['status'] ?? 'a_contacter';
						$tok  = '' !== trim( $rname ) ? ' ' . $rname : '';
						$pmsg = str_replace( '{prenom}', $tok, ag_recruit_message() );
						$smsn = preg_replace( '/[^0-9+]/', '', $rphone );
						$wan  = ag_wa_number( $rphone );
						$sms  = 'sms:' . $smsn . '?body=' . rawurlencode( $pmsg );
						$wa   = 'https://wa.me/' . $wan . '?text=' . rawurlencode( $pmsg );
					?>
						<form method="post" action="<?php echo esc_url( $post ); ?>" style="display:flex;gap:8px;align-items:center;padding:7px 4px;border-bottom:1px solid #f0f0f1;flex-wrap:wrap;">
							<input type="hidden" name="action" value="ag_recruit_update">
							<?php wp_nonce_field( 'ag_recruit_crm', '_n' ); ?>
							<input type="hidden" name="id" value="<?php echo esc_attr( $rid ); ?>">
							<input type="text" name="name" value="<?php echo esc_attr( $rname ); ?>" placeholder="prénom" style="width:130px;">
							<span style="width:130px;font-size:.9rem;"><?php echo esc_html( $rphone ); ?></span>
							<select name="status" onchange="this.form.submit()" style="width:190px;">
								<?php foreach ( $rlabels as $sk => $sl ) : ?><option value="<?php echo esc_attr( $sk ); ?>" <?php selected( $rstatus, $sk ); ?>><?php echo esc_html( $sl ); ?></option><?php endforeach; ?>
							</select>
							<input type="text" name="note" value="<?php echo esc_attr( $r['note'] ?? '' ); ?>" placeholder="note…" style="width:150px;">
							<span style="flex:1;white-space:nowrap;">
								<a class="button button-small" href="<?php echo esc_url( $sms ); ?>">📱 SMS</a>
								<a class="button button-small" target="_blank" rel="noopener" href="<?php echo esc_url( $wa ); ?>">WhatsApp</a>
								<button class="button button-small" title="Enregistrer prénom/note">💾</button>
								<button class="button button-small" name="delete" value="1" onclick="return confirm('Supprimer cette fiche ?');" style="color:#b32d2e;" title="Supprimer">✕</button>
							</span>
						</form>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
			<?php if ( 'added' === $msg ) : ?><div class="notice notice-success is-dismissible"><p>✅ Ambassadeur ajouté à la zone.</p></div>
			<?php elseif ( 'removed' === $msg ) : ?><div class="notice notice-success is-dismissible"><p>✅ Retiré de la zone.</p></div>
			<?php elseif ( 'quota' === $msg ) : ?><div class="notice notice-error is-dismissible"><p>⛔ Quota atteint : cet ambassadeur a déjà son nombre de zones autorisées. Donne-lui une « zone supplémentaire » (payée/accordée) ci-dessous avant d'en ajouter une autre.</p></div>
			<?php elseif ( 'extra' === $msg ) : ?><div class="notice notice-success is-dismissible"><p>✅ Réglage des zones supplémentaires enregistré.</p></div>
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

			<h2 style="margin-top:28px;">💎 Chasseur Pro — recherche payante (19 €/mois)</h2>
			<p style="max-width:820px;color:#50575e;">Les ambassadeurs reçoivent les prospects du robot <strong>gratuitement</strong>. La <strong>recherche à la demande</strong> dans leur zone est payante (couvre le coût Google). Colle le <strong>lien d'abonnement PayPal (19 €/mois)</strong> ci‑dessous : dès qu'un ambassadeur paie, son accès s'<strong>active automatiquement</strong> (via le webhook PayPal). Le bouton manuel reste un secours.</p>
			<form method="post" action="<?php echo esc_url( $post ); ?>" style="margin-bottom:14px;">
				<input type="hidden" name="action" value="ag_chasseur_link">
				<?php wp_nonce_field( 'ag_zone_admin', '_n' ); ?>
				<input type="url" name="url" value="<?php echo esc_attr( get_option( 'ag_chasseur_paypal_url', '' ) ); ?>" placeholder="https://www.paypal.com/.../abonnement-19" style="width:420px;">
				<?php submit_button( 'Enregistrer le lien', 'secondary', 'submit', false ); ?>
			</form>
			<?php $amb_users = get_users( array( 'role' => 'ag_ambassadeur' ) ); if ( $amb_users ) : ?>
			<table class="widefat striped" style="max-width:820px;"><thead><tr><th>Ambassadeur</th><th>Téléphone</th><th>Chasseur Pro</th><th></th></tr></thead><tbody>
				<?php foreach ( $amb_users as $au ) :
					$until = (int) get_user_meta( $au->ID, 'ag_chasseur_until', true );
					$active = $until > time();
				?>
				<tr>
					<td><strong><?php echo esc_html( $au->display_name ?: $au->user_email ); ?></strong><br><small><?php echo esc_html( $au->user_email ); ?></small></td>
					<td><?php echo esc_html( get_user_meta( $au->ID, 'ag_amb_phone', true ) ?: '—' ); ?></td>
					<td><?php echo $active ? '<span style="color:#1e7e34;font-weight:700;">✅ Actif jusqu\'au ' . esc_html( wp_date( 'd/m/Y', $until ) ) . '</span>' : '<span style="color:#646970;">— inactif</span>'; ?></td>
					<td>
						<form method="post" action="<?php echo esc_url( $post ); ?>" style="margin:0;">
							<input type="hidden" name="action" value="ag_chasseur_toggle"><?php wp_nonce_field( 'ag_zone_admin', '_n' ); ?>
							<input type="hidden" name="uid" value="<?php echo (int) $au->ID; ?>">
							<input type="hidden" name="on" value="<?php echo $active ? '0' : '1'; ?>">
							<button class="button button-small"><?php echo $active ? 'Désactiver' : 'Activer 1 mois'; ?></button>
						</form>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody></table>
			<?php else : ?><p>Aucun ambassadeur inscrit pour l'instant.</p><?php endif; ?>

			<h2 style="margin-top:28px;">🗺️ Zones supplémentaires (offre payante)</h2>
			<p style="max-width:820px;color:#50575e;">Règle de base : <strong>1 seule zone</strong> par ambassadeur. Pour en couvrir plusieurs, il <strong>achète des zones en plus</strong> (1 zone par paiement, autant qu'il veut). Dès qu'il paie, son quota augmente <strong>automatiquement</strong> (webhook PayPal) et il choisit sa nouvelle zone dans son espace. Tu peux aussi accorder des zones à la main ci‑dessous.</p>
			<form method="post" action="<?php echo esc_url( $post ); ?>" style="margin-bottom:14px;">
				<input type="hidden" name="action" value="ag_zone_price_save">
				<?php wp_nonce_field( 'ag_zone_admin', '_n' ); ?>
				Prix d'une zone en plus : <input type="number" name="price" value="<?php echo esc_attr( ag_zone_price() ); ?>" step="1" min="0" style="width:90px;"> €
				&nbsp; Lien PayPal : <input type="url" name="url" value="<?php echo esc_attr( get_option( 'ag_zone_paypal_url', '' ) ); ?>" placeholder="https://www.paypal.com/.../zone-49" style="width:360px;">
				<?php submit_button( 'Enregistrer', 'secondary', 'submit', false ); ?>
			</form>
			<?php if ( ! empty( $amb_users ) ) : ?>
			<table class="widefat striped" style="max-width:820px;"><thead><tr><th>Ambassadeur</th><th>Zones occupées</th><th>Quota (1 + payées)</th><th>Zones en plus</th></tr></thead><tbody>
				<?php foreach ( $amb_users as $au ) :
					$mz = ag_zone_of_owner( $au->user_email );
					$ex = ag_zone_extra( $au->ID );
				?>
				<tr>
					<td><strong><?php echo esc_html( $au->display_name ?: $au->user_email ); ?></strong></td>
					<td><?php echo $mz ? esc_html( implode( ', ', $mz ) ) : '—'; ?> <strong>(<?php echo count( $mz ); ?>)</strong></td>
					<td><?php echo (int) ( 1 + $ex ); ?></td>
					<td>
						<form method="post" action="<?php echo esc_url( $post ); ?>" style="margin:0;display:flex;gap:6px;align-items:center;">
							<input type="hidden" name="action" value="ag_zone_extra_set"><?php wp_nonce_field( 'ag_zone_admin', '_n' ); ?>
							<input type="hidden" name="uid" value="<?php echo (int) $au->ID; ?>">
							<input type="number" name="extra" value="<?php echo (int) $ex; ?>" min="0" style="width:70px;">
							<button class="button button-small">💾</button>
						</form>
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
	$zmsg = 'added';
	if ( $dept && $owner ) {
		$mine = ag_zone_of_owner( $owner );
		if ( ! in_array( $dept, $mine, true ) && count( $mine ) >= ag_zone_quota( $owner ) ) {
			$zmsg = 'quota'; // 1 zone max sauf zones payées/accordées : on n'attribue pas au-delà du quota
		} else {
			$rec = function_exists( 'ag_ambassadeur_record' ) ? ag_ambassadeur_record( $owner ) : null;
			ag_zone_add_owner( $dept, $owner, $rec['name'] ?? '' );
		}
	}
	wp_safe_redirect( admin_url( 'admin.php?page=ag-zones&zmsg=' . $zmsg ) ); exit;
} );
/* ── Admin : régler le nombre de zones supplémentaires (offre payante) ── */
add_action( 'admin_post_ag_zone_extra_set', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_zone_admin' ) ) wp_die( 'no' );
	$uid = (int) ( $_POST['uid'] ?? 0 );
	$n   = max( 0, (int) ( $_POST['extra'] ?? 0 ) );
	if ( $uid ) update_user_meta( $uid, 'ag_zone_extra', $n );
	wp_safe_redirect( admin_url( 'admin.php?page=ag-zones&zmsg=extra' ) ); exit;
} );
add_action( 'admin_post_ag_zone_price_save', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_zone_admin' ) ) wp_die( 'no' );
	update_option( 'ag_zone_price', max( 0, (float) ( $_POST['price'] ?? 49 ) ) );
	update_option( 'ag_zone_paypal_url', esc_url_raw( wp_unslash( $_POST['url'] ?? '' ) ) );
	wp_safe_redirect( admin_url( 'admin.php?page=ag-zones&zmsg=extra' ) ); exit;
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

/* ── CRM recrutement : enregistrer une fournée de contacts ────────── */
add_action( 'admin_post_ag_recruits_add', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_recruit_crm' ) ) wp_die( 'no' );
	$raw = (string) wp_unslash( $_POST['lines'] ?? '' );
	foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) continue;
		if ( preg_match( '/^\s*([+0-9 ().-]{6,})\s*[,;:–-]?\s*(.*)$/', $line, $m ) ) {
			ag_recruits_add_line( sanitize_text_field( $m[1] ), sanitize_text_field( $m[2] ) );
		} else {
			ag_recruits_add_line( sanitize_text_field( $line ), '' );
		}
	}
	wp_safe_redirect( admin_url( 'admin.php?page=ag-zones#recruits' ) ); exit;
} );
/* ── CRM recrutement : éditer / supprimer une fiche ───────────────── */
add_action( 'admin_post_ag_recruit_update', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_recruit_crm' ) ) wp_die( 'no' );
	$id   = sanitize_text_field( wp_unslash( $_POST['id'] ?? '' ) );
	$del  = ! empty( $_POST['delete'] );
	$list = ag_recruits_get();
	foreach ( $list as $k => $r ) {
		if ( ( $r['id'] ?? '' ) !== $id ) continue;
		if ( $del ) { unset( $list[ $k ] ); break; }
		if ( array_key_exists( 'name', $_POST ) )   $list[ $k ]['name']   = sanitize_text_field( wp_unslash( $_POST['name'] ) );
		if ( array_key_exists( 'note', $_POST ) )   $list[ $k ]['note']   = sanitize_text_field( wp_unslash( $_POST['note'] ) );
		if ( array_key_exists( 'status', $_POST ) ) {
			$st = sanitize_text_field( wp_unslash( $_POST['status'] ) );
			if ( isset( ag_recruit_statuses()[ $st ] ) ) $list[ $k ]['status'] = $st;
		}
		break;
	}
	update_option( 'ag_recruits', array_values( $list ), false );
	wp_safe_redirect( admin_url( 'admin.php?page=ag-zones#recruits' ) ); exit;
} );

/* ── Parcours guidé : l'ambassadeur confirme avoir rejoint le groupe Telegram ── */
add_action( 'admin_post_ag_onboard_tg_ack', function () {
	if ( ! is_user_logged_in() || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_zone_front' ) ) wp_die( 'no' );
	update_user_meta( get_current_user_id(), 'ag_onboard_tg', 1 );
	wp_safe_redirect( home_url( '/espace-ambassadeur?zone=tg_ok#demarrage' ) ); exit;
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
	wp_safe_redirect( home_url( '/espace-ambassadeur?zone=' . $res . '#demarrage' ) ); exit;
} );

/* ── Handler front (ambassadeur) : prendre / changer de zone (1 seule zone) ── */
add_action( 'admin_post_ag_zone_request', function () {
	if ( ! is_user_logged_in() || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_zone_front' ) ) wp_die( 'no' );
	$u = wp_get_current_user();
	$email = strtolower( $u->user_email );
	$name  = $u->display_name ?: $email;
	$dept  = ag_dept_norm( wp_unslash( $_POST['dept'] ?? '' ) );
	if ( '' === ag_amb_phone( $u->ID ) ) { wp_safe_redirect( home_url( '/espace-ambassadeur?zone=need_phone#demarrage' ) ); exit; }
	$res = 'err';
	if ( $dept && isset( ag_dept_names()[ $dept ] ) ) {
		$mine = ag_zone_of_owner( $email );
		if ( in_array( $dept, $mine, true ) ) {
			$res = 'mine';
		} elseif ( count( $mine ) >= ag_zone_quota( $email ) ) {
			$res = 'quota'; // quota atteint : doit libérer une zone ou en acheter une de plus
		} else {
			$add = ag_zone_add_owner( $dept, $email, $name ); // claimed | joined (pas de retrait : multi-zones possible si payé)
			$res = $add;
			if ( function_exists( 'ag_push' ) ) ag_push( '🗺️ Zone ' . $dept, $name . ' couvre le ' . $dept . ( 'joined' === $add ? ' — partage 50/50' : '' ) . '.' );
		}
	}
	wp_safe_redirect( home_url( '/espace-ambassadeur?zone=' . $res . '#demarrage' ) ); exit;
} );

/* ── Handler front : libérer (quitter) une de ses zones ──────────── */
add_action( 'admin_post_ag_zone_release', function () {
	if ( ! is_user_logged_in() || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_zone_front' ) ) wp_die( 'no' );
	$u = wp_get_current_user();
	$dept = ag_dept_norm( wp_unslash( $_POST['dept'] ?? '' ) );
	if ( $dept ) ag_zone_remove_owner( $dept, strtolower( $u->user_email ) );
	wp_safe_redirect( home_url( '/espace-ambassadeur?zone=released#demarrage' ) ); exit;
} );

/* ── Chasseur Pro : activation admin + lien d'abonnement PayPal ── */
add_action( 'admin_post_ag_chasseur_toggle', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_zone_admin' ) ) wp_die( 'no' );
	$uid = (int) ( $_POST['uid'] ?? 0 );
	$on  = ! empty( $_POST['on'] );
	if ( $uid ) update_user_meta( $uid, 'ag_chasseur_until', $on ? ( time() + 35 * DAY_IN_SECONDS ) : 0 );
	wp_safe_redirect( admin_url( 'admin.php?page=ag-zones&zmsg=chasseur' ) ); exit;
} );
add_action( 'admin_post_ag_chasseur_link', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_zone_admin' ) ) wp_die( 'no' );
	update_option( 'ag_chasseur_paypal_url', esc_url_raw( wp_unslash( $_POST['url'] ?? '' ) ) );
	wp_safe_redirect( admin_url( 'admin.php?page=ag-zones&zmsg=chasseur' ) ); exit;
} );

/* ── Carte des zones : tableau dense par pays (FR / MA), heatmap par opportunités ── */
if ( ! function_exists( 'ag_zones_stats' ) ) {
	function ag_zones_stats() {
		$names = ag_dept_names();
		$stats = array();
		foreach ( array_keys( $names ) as $d ) {
			$stats[ $d ] = array( 'prospects' => 0, 'clients' => 0, 'opportunites' => 0, 'score_sum' => 0, 'score_count' => 0, 'last_ts' => 0 );
		}
		foreach ( (array) get_option( 'ag_prospects', array() ) as $p ) {
			$st = $p['status'] ?? 'nouveau';
			if ( in_array( $st, array( 'refus', 'ne_pas_contacter', 'ignore' ), true ) ) continue;
			$d = ag_prospect_dept( $p );
			if ( '' === $d || ! isset( $stats[ $d ] ) ) continue;
			if ( 'client' === $st ) $stats[ $d ]['clients']++;
			else $stats[ $d ]['prospects']++;
			if ( empty( $p['owner_email'] ) ) $stats[ $d ]['opportunites']++;
			if ( function_exists( 'ag_prospect_score' ) ) {
				$stats[ $d ]['score_sum'] += (int) ag_prospect_score( $p );
				$stats[ $d ]['score_count']++;
			}
			$ts = (int) ( $p['ts'] ?? 0 );
			if ( $ts > $stats[ $d ]['last_ts'] ) $stats[ $d ]['last_ts'] = $ts;
		}
		return $stats;
	}
}

add_action( 'admin_menu', function () {
	add_submenu_page( 'ag-ambassadeurs', 'Carte des zones', '🗺️ Carte des zones', 'manage_options', 'ag-zones-map', 'ag_zones_map_render' );
}, 25 );

if ( ! function_exists( 'ag_zones_map_render' ) ) {
	function ag_zones_map_render() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$names = ag_dept_names();
		$stats = ag_zones_stats();

		// Heatmap : couleur de fond selon le nombre d'opportunités (prospects actifs SANS ambassadeur).
		$heat = function ( $n ) {
			if ( $n >= 16 ) return '#fde2e2';
			if ( $n >= 6 )  return '#ffe9c9';
			if ( $n >= 1 )  return '#fff7c2';
			return '#f6f7f7';
		};

		// Split par pays (FR = clés numériques, MA = préfixe MA-).
		$fr = array(); $ma = array();
		foreach ( $names as $code => $nom ) {
			if ( 0 === strpos( $code, 'MA-' ) ) $ma[ $code ] = $nom;
			else $fr[ $code ] = $nom;
		}

		$render_country = function ( $title, $list ) use ( $stats, $heat ) {
			$tot_p = 0; $tot_c = 0; $tot_o = 0; $tot_z = 0; $tot_cov = 0;
			foreach ( $list as $code => $nom ) {
				$s = $stats[ $code ] ?? array();
				$tot_p += (int) ( $s['prospects'] ?? 0 );
				$tot_c += (int) ( $s['clients'] ?? 0 );
				$tot_o += (int) ( $s['opportunites'] ?? 0 );
				$tot_z++;
				if ( ag_zone_owners( $code ) ) $tot_cov++;
			}
			?>
			<h2 style="margin-top:24px;"><?php echo esc_html( $title ); ?> <span style="color:#646970;font-weight:400;font-size:.8em;">— <?php echo (int) $tot_cov; ?>/<?php echo (int) $tot_z; ?> zones couvertes · <?php echo (int) $tot_p; ?> prospects actifs · <?php echo (int) $tot_c; ?> clients · <?php echo (int) $tot_o; ?> opportunités libres</span></h2>
			<table class="widefat striped" style="max-width:1100px;">
				<thead><tr>
					<th>Zone</th>
					<th>Ambassadeurs</th>
					<th>Prospects actifs</th>
					<th>Clients</th>
					<th>Opportunités libres</th>
					<th>Score moyen</th>
					<th>Dernier contact</th>
					<th></th>
				</tr></thead>
				<tbody>
				<?php foreach ( $list as $code => $nom ) :
					$s = $stats[ $code ] ?? array();
					$opp = (int) ( $s['opportunites'] ?? 0 );
					$bg = $heat( $opp );
					$owners = ag_zone_owners( $code );
					$avg = ! empty( $s['score_count'] ) ? round( $s['score_sum'] / $s['score_count'] ) : 0;
					$last = ! empty( $s['last_ts'] ) ? wp_date( 'd/m/Y', (int) $s['last_ts'] ) : '—';
					$amb_names = array_map( function ( $o ) { return $o['name'] ?: $o['email']; }, $owners );
				?>
					<tr style="background:<?php echo esc_attr( $bg ); ?>;">
						<td><strong><?php echo esc_html( $code ); ?></strong><br><small style="color:#50575e;"><?php echo esc_html( $nom ); ?></small></td>
						<td><?php if ( $owners ) : ?><strong><?php echo count( $owners ); ?></strong> <small style="color:#50575e;"><?php echo esc_html( implode( ', ', $amb_names ) ); ?></small><?php else : ?><span style="color:#b32d2e;font-weight:700;">— libre —</span><?php endif; ?></td>
						<td style="font-weight:700;"><?php echo (int) ( $s['prospects'] ?? 0 ); ?></td>
						<td style="font-weight:700;color:#1e7e34;"><?php echo (int) ( $s['clients'] ?? 0 ); ?></td>
						<td style="font-weight:700;color:<?php echo $opp >= 16 ? '#b32d2e' : ( $opp >= 6 ? '#bd7b00' : '#1d2327' ); ?>;"><?php echo (int) $opp; ?></td>
						<td><?php echo $avg ? (int) $avg : '—'; ?></td>
						<td style="font-size:.85em;color:#50575e;"><?php echo esc_html( $last ); ?></td>
						<td><a class="button button-small" href="<?php echo esc_url( admin_url( 'admin.php?page=ag-prospects&fq=' . rawurlencode( $nom ) ) ); ?>">Voir prospects</a></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<?php
		};
		?>
		<div class="wrap">
			<h1>🗺️ Carte des zones — entreprises à démarcher & clients</h1>
			<p style="max-width:1100px;color:#50575e;">Une vue par <strong>pays → zone</strong>. La couleur indique le nombre d'<strong>opportunités libres</strong> (prospects actifs sans ambassadeur assigné) : <span style="background:#fde2e2;padding:2px 8px;border-radius:4px;">rouge ≥ 16</span> <span style="background:#ffe9c9;padding:2px 8px;border-radius:4px;">orange 6-15</span> <span style="background:#fff7c2;padding:2px 8px;border-radius:4px;">jaune 1-5</span> <span style="background:#f6f7f7;padding:2px 8px;border-radius:4px;">vide</span>. Clique « Voir prospects » pour filtrer la prospection sur la zone.</p>
			<p style="max-width:1100px;color:#50575e;">⚙️ Pour <strong>ajouter ou retirer un ambassadeur</strong> d'une zone : <a href="<?php echo esc_url( admin_url( 'admin.php?page=ag-zones' ) ); ?>">Prospection → 🗺️ Zones</a>.</p>
			<?php $render_country( '🇫🇷 France', $fr ); ?>
			<?php $render_country( '🇲🇦 Maroc', $ma ); ?>
		</div>
		<?php
	}
}
