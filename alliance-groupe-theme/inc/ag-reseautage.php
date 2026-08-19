<?php
/**
 * AG Réseautage — Radar des soirées & rencontres entrepreneurs (Nantes).
 *
 * But : trouver où rencontrer des patrons / entrepreneurs à Nantes, et suivre
 * les sorties comme un pipeline (j'y vais → j'y étais → contacts récupérés).
 *
 * Deux moteurs complémentaires :
 *  1. ANNUAIRE CURÉ (toujours dispo, zéro dépendance) : les réseaux récurrents
 *     de Nantes (CCI, BNI, CJD, APM, Réseau Entreprendre, Village by CA,
 *     Atlanpole, DCF, coworkings...) avec rythme, cible, coût et comment entrer.
 *  2. RADAR AUTO : récupère les événements publiés en JSON-LD (schema.org Event)
 *     sur des pages sources configurables (Eventbrite Nantes, agendas CCI, etc.),
 *     filtre sur Nantes + mots-clés business + dates futures. Cron quotidien.
 *
 * Menu : Prospection → 🤝 Réseautage Nantes
 *
 * @package Alliance_Groupe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ================================================================
   1. ANNUAIRE CURÉ — les réseaux qui comptent à Nantes
   ================================================================ */
if ( ! function_exists( 'ag_reseau_annuaire' ) ) {
	/**
	 * Réseaux/rendez-vous récurrents pour rencontrer des dirigeants à Nantes.
	 *
	 * @return array
	 */
	function ag_reseau_annuaire() {
		return array(
			array(
				'nom'    => 'CCI Nantes St-Nazaire',
				'type'   => 'Institution',
				'rythme' => 'Plusieurs rendez-vous par mois',
				'cible'  => 'Dirigeants TPE/PME, créateurs, commerçants',
				'cout'   => 'Souvent gratuit',
				'url'    => 'https://www.nantesstnazaire.cci.fr/agenda',
				'entree' => 'Agenda public : ateliers, matinales, rencontres. Le plus simple pour commencer — on y croise des patrons en phase de développement (donc des besoins web).',
			),
			array(
				'nom'    => 'BNI Nantes (groupes locaux)',
				'type'   => 'Réseau d’affaires',
				'rythme' => 'Hebdomadaire (petit-déjeuner)',
				'cible'  => 'Dirigeants & indépendants, 1 métier par groupe',
				'cout'   => 'Visite gratuite, adhésion payante',
				'url'    => 'https://bninantes.fr/',
				'entree' => 'Demander une invitation en tant que visiteur. Recommandation mutuelle = beaucoup de business. La place « création de site web » est souvent déjà prise : viser un groupe où elle est libre.',
			),
			array(
				'nom'    => 'Réseau Entreprendre Atlantique',
				'type'   => 'Accompagnement',
				'rythme' => 'Événements réguliers',
				'cible'  => 'Chefs d’entreprise confirmés + lauréats',
				'cout'   => 'Événements souvent gratuits',
				'url'    => 'https://www.reseau-entreprendre.org/atlantique/',
				'entree' => 'Des patrons qui accompagnent d’autres patrons. Très bon niveau de décideurs.',
			),
			array(
				'nom'    => 'CJD Nantes (Centre des Jeunes Dirigeants)',
				'type'   => 'Club de dirigeants',
				'rythme' => 'Mensuel',
				'cible'  => 'Dirigeants < 45 ans',
				'cout'   => 'Adhésion, 1re participation possible',
				'url'    => 'https://www.cjd.net/',
				'entree' => 'Ambiance conviviale, dirigeants ouverts à l’innovation. Demander à être invité par un membre.',
			),
			array(
				'nom'    => 'APM (Association Progrès du Management)',
				'type'   => 'Club de dirigeants',
				'rythme' => 'Mensuel',
				'cible'  => 'Dirigeants de PME/ETI',
				'cout'   => 'Adhésion (haut de gamme)',
				'url'    => 'https://www.apm.fr/',
				'entree' => 'Cible « riche » : dirigeants avec de vrais budgets. Accès par cooptation.',
			),
			array(
				'nom'    => 'Le Village by CA Atlantique Vendée',
				'type'   => 'Écosystème startup',
				'rythme' => 'Événements fréquents',
				'cible'  => 'Startups, scale-ups, grands comptes',
				'cout'   => 'Souvent gratuit',
				'url'    => 'https://levillagebyca.com/',
				'entree' => 'Beaucoup d’afterworks et démos. Public tech, sensible au discours IA/sécurité.',
			),
			array(
				'nom'    => 'Atlanpole',
				'type'   => 'Technopole',
				'rythme' => 'Événements réguliers',
				'cible'  => 'Innovation, deeptech, créateurs',
				'cout'   => 'Gratuit / sur inscription',
				'url'    => 'https://www.atlanpole.fr/',
				'entree' => 'Rencontres thématiques. Bon pour se positionner en expert technique.',
			),
			array(
				'nom'    => 'DCF Nantes (Dirigeants Commerciaux de France)',
				'type'   => 'Réseau commercial',
				'rythme' => 'Mensuel',
				'cible'  => 'Directeurs commerciaux, dirigeants',
				'cout'   => 'Adhésion + soirées',
				'url'    => 'https://www.reseaudcf.fr/',
				'entree' => 'Des gens dont le métier EST la vente : ils comprennent vite une proposition de valeur.',
			),
			array(
				'nom'    => 'Eventbrite — Nantes / Business',
				'type'   => 'Agrégateur',
				'rythme' => 'En continu',
				'cible'  => 'Afterworks, salons, conférences',
				'cout'   => 'Gratuit à payant',
				'url'    => 'https://www.eventbrite.fr/d/france--nantes/business--events/',
				'entree' => 'Source la plus riche en soirées ouvertes. C’est cette page que le radar automatique lit.',
			),
			array(
				'nom'    => 'Meetup — Nantes',
				'type'   => 'Agrégateur',
				'rythme' => 'En continu',
				'cible'  => 'Tech, entrepreneuriat, freelances',
				'cout'   => 'Souvent gratuit',
				'url'    => 'https://www.meetup.com/find/?location=fr--Nantes',
				'entree' => 'Communautés tech/freelance. Très accessible pour un premier contact informel.',
			),
			array(
				'nom'    => 'Coworkings nantais (La Cantine, Now Coworking, Le Palace…)',
				'type'   => 'Lieux',
				'rythme' => 'Afterworks & ateliers',
				'cible'  => 'Freelances, startups, indépendants',
				'cout'   => 'Gratuit à faible',
				'url'    => 'https://lacantine.co/',
				'entree' => 'Aller sur place, prendre un café, participer à un atelier. Le réseautage le moins formel — et souvent le plus efficace.',
			),
			array(
				'nom'    => 'Clubs d’entreprises de zones (Erdre-Active, Sud Loire…)',
				'type'   => 'Club local',
				'rythme' => 'Mensuel',
				'cible'  => 'Patrons de PME implantées',
				'cout'   => 'Adhésion modérée',
				'url'    => 'https://www.google.com/search?q=club+entreprises+zone+activit%C3%A9+Nantes',
				'entree' => 'Peu connus, peu sollicités : moins de concurrence entre prestataires. Excellent rapport effort/résultat.',
			),
		);
	}
}

/* ================================================================
   2. SOURCES du radar automatique (pages publiant du JSON-LD Event)
   ================================================================ */
if ( ! function_exists( 'ag_reseau_sources' ) ) {
	/** @return array liste de sources { label, url } */
	function ag_reseau_sources() {
		$def = array(
			array( 'label' => 'Eventbrite — Nantes / Business', 'url' => 'https://www.eventbrite.fr/d/france--nantes/business--events/' ),
			array( 'label' => 'Eventbrite — Nantes / Networking', 'url' => 'https://www.eventbrite.fr/d/france--nantes/networking/' ),
			array( 'label' => 'Eventbrite — Nantes / Entrepreneuriat', 'url' => 'https://www.eventbrite.fr/d/france--nantes/entrepreneurship/' ),
			array( 'label' => 'CCI Nantes St-Nazaire — agenda', 'url' => 'https://www.nantesstnazaire.cci.fr/agenda' ),
		);
		$extra = array_filter( array_map( 'trim', explode( "\n", (string) get_option( 'ag_reseau_urls', '' ) ) ) );
		foreach ( $extra as $u ) {
			if ( 0 === strpos( $u, 'http' ) ) {
				$def[] = array( 'label' => wp_parse_url( $u, PHP_URL_HOST ), 'url' => $u );
			}
		}
		return $def;
	}
}

/* ================================================================
   3. MOTEUR — extraction JSON-LD schema.org/Event
   ================================================================ */
if ( ! function_exists( 'ag_reseau_extract' ) ) {
	/**
	 * Extrait les événements JSON-LD d'une page HTML.
	 *
	 * @param string $html HTML brut.
	 * @return array
	 */
	function ag_reseau_extract( $html ) {
		$out = array();
		if ( ! $html ) {
			return $out;
		}
		if ( ! preg_match_all( '#<script[^>]*application/ld\+json[^>]*>(.*?)</script>#is', $html, $m ) ) {
			return $out;
		}
		foreach ( $m[1] as $raw ) {
			$data = json_decode( trim( $raw ), true );
			if ( null === $data ) {
				continue;
			}
			$stack = array( $data );
			$guard = 0;
			while ( $stack && $guard++ < 4000 ) {
				$node = array_pop( $stack );
				if ( is_array( $node ) && isset( $node[0] ) ) {
					foreach ( $node as $sub ) {
						$stack[] = $sub;
					}
					continue;
				}
				if ( ! is_array( $node ) ) {
					continue;
				}
				foreach ( array( '@graph', 'itemListElement', 'item', 'subEvent' ) as $k ) {
					if ( isset( $node[ $k ] ) ) {
						$stack[] = $node[ $k ];
					}
				}
				$type = isset( $node['@type'] ) ? $node['@type'] : '';
				if ( is_array( $type ) ) {
					$type = reset( $type );
				}
				if ( ! is_string( $type ) || false === stripos( $type, 'Event' ) ) {
					continue;
				}
				$name = isset( $node['name'] ) ? wp_strip_all_tags( (string) $node['name'] ) : '';
				$date = isset( $node['startDate'] ) ? (string) $node['startDate'] : '';
				if ( ! $name || ! $date ) {
					continue;
				}
				$loc = isset( $node['location'] ) ? $node['location'] : array();
				if ( is_array( $loc ) && isset( $loc[0] ) ) {
					$loc = $loc[0];
				}
				$lieu  = '';
				$ville = '';
				if ( is_array( $loc ) ) {
					$lieu = isset( $loc['name'] ) ? wp_strip_all_tags( (string) $loc['name'] ) : '';
					if ( isset( $loc['address'] ) ) {
						$ad = $loc['address'];
						if ( is_array( $ad ) ) {
							$ville = isset( $ad['addressLocality'] ) ? (string) $ad['addressLocality'] : '';
							if ( ! $lieu && isset( $ad['streetAddress'] ) ) {
								$lieu = (string) $ad['streetAddress'];
							}
						} elseif ( is_string( $ad ) ) {
							$ville = $ad;
						}
					}
				}
				$prix = '';
				if ( isset( $node['offers'] ) ) {
					$of = $node['offers'];
					if ( is_array( $of ) && isset( $of[0] ) ) {
						$of = $of[0];
					}
					if ( is_array( $of ) && isset( $of['price'] ) ) {
						$p    = (float) $of['price'];
						$prix = $p > 0 ? number_format_i18n( $p, 0 ) . ' €' : 'Gratuit';
					}
				}
				$out[] = array(
					'nom'   => $name,
					'date'  => $date,
					'lieu'  => $lieu,
					'ville' => $ville,
					'prix'  => $prix,
					'url'   => isset( $node['url'] ) ? esc_url_raw( (string) $node['url'] ) : '',
				);
			}
		}
		return $out;
	}
}

if ( ! function_exists( 'ag_reseau_is_pertinent' ) ) {
	/**
	 * Garde les événements « rencontre / business » à venir, autour de Nantes.
	 *
	 * @param array $e Événement.
	 * @return bool
	 */
	function ag_reseau_is_pertinent( $e ) {
		$ts = strtotime( $e['date'] );
		if ( ! $ts || $ts < ( time() - DAY_IN_SECONDS ) ) {
			return false; // passé
		}
		if ( $ts > time() + ( 180 * DAY_IN_SECONDS ) ) {
			return false; // trop lointain
		}
		$hay = mb_strtolower( $e['nom'] . ' ' . $e['lieu'] . ' ' . $e['ville'] );
		// Bruit à écarter (loisir pur).
		$stop = array( 'concert', 'yoga', 'marathon', 'brocante', 'halloween', 'karaoké', 'stand up', 'théâtre' );
		foreach ( $stop as $s ) {
			if ( false !== mb_strpos( $hay, $s ) ) {
				return false;
			}
		}
		return true;
	}
}

if ( ! function_exists( 'ag_reseau_score' ) ) {
	/**
	 * Score « utile pour rencontrer des patrons » (0-100).
	 *
	 * @param array $e Événement.
	 * @return int
	 */
	function ag_reseau_score( $e ) {
		$hay   = mb_strtolower( $e['nom'] . ' ' . $e['lieu'] . ' ' . $e['ville'] );
		$score = 40;
		$fort  = array( 'réseau' => 22, 'reseau' => 22, 'networking' => 22, 'afterwork' => 20, 'after work' => 20,
			'dirigeant' => 22, 'entrepreneur' => 18, 'patron' => 20, 'business' => 12, 'club' => 10,
			'petit-déjeuner' => 14, 'petit dejeuner' => 14, 'matinale' => 14, 'rencontre' => 12,
			'salon' => 10, 'cci' => 14, 'bni' => 18, 'startup' => 8, 'pme' => 12, 'tpe' => 10, 'apéro' => 12 );
		foreach ( $fort as $k => $v ) {
			if ( false !== mb_strpos( $hay, $k ) ) {
				$score += $v;
			}
		}
		if ( false !== mb_strpos( $hay, 'nantes' ) ) {
			$score += 10;
		}
		if ( 'Gratuit' === $e['prix'] ) {
			$score += 4;
		}
		return max( 0, min( 100, $score ) );
	}
}

if ( ! function_exists( 'ag_reseau_fetch_all' ) ) {
	/**
	 * Interroge toutes les sources, agrège, dédoublonne et stocke.
	 *
	 * @return array {found:int, kept:int, errors:array}
	 */
	function ag_reseau_fetch_all() {
		$found  = 0;
		$errors = array();
		$bag    = array();

		foreach ( ag_reseau_sources() as $src ) {
			$res = wp_remote_get(
				$src['url'],
				array(
					'timeout'    => 25,
					'user-agent' => 'Mozilla/5.0 (compatible; AllianceGroupe-Radar/1.0)',
					'headers'    => array( 'Accept-Language' => 'fr-FR,fr;q=0.9' ),
				)
			);
			if ( is_wp_error( $res ) ) {
				$errors[] = $src['label'] . ' : ' . $res->get_error_message();
				continue;
			}
			if ( 200 !== (int) wp_remote_retrieve_response_code( $res ) ) {
				$errors[] = $src['label'] . ' : HTTP ' . wp_remote_retrieve_response_code( $res );
				continue;
			}
			$evs    = ag_reseau_extract( wp_remote_retrieve_body( $res ) );
			$found += count( $evs );
			foreach ( $evs as $e ) {
				if ( ! ag_reseau_is_pertinent( $e ) ) {
					continue;
				}
				$e['source'] = $src['label'];
				$e['score']  = ag_reseau_score( $e );
				$key         = md5( mb_strtolower( $e['nom'] ) . substr( $e['date'], 0, 10 ) );
				if ( ! isset( $bag[ $key ] ) || $bag[ $key ]['score'] < $e['score'] ) {
					$bag[ $key ] = $e;
				}
			}
		}

		// Conserve les statuts déjà posés (j'y vais / notes).
		$old = (array) get_option( 'ag_reseau_events', array() );
		foreach ( $bag as $k => $e ) {
			if ( isset( $old[ $k ]['statut'] ) ) {
				$bag[ $k ]['statut'] = $old[ $k ]['statut'];
			}
			if ( isset( $old[ $k ]['notes'] ) ) {
				$bag[ $k ]['notes'] = $old[ $k ]['notes'];
			}
		}
		// Garde les événements marqués même s'ils ne sont plus listés.
		foreach ( $old as $k => $e ) {
			if ( ! isset( $bag[ $k ] ) && ! empty( $e['statut'] ) && 'nouveau' !== $e['statut'] ) {
				$bag[ $k ] = $e;
			}
		}

		uasort(
			$bag,
			function ( $a, $b ) {
				return strcmp( $a['date'], $b['date'] );
			}
		);

		update_option( 'ag_reseau_events', $bag, false );
		update_option( 'ag_reseau_last', current_time( 'mysql' ), false );
		update_option( 'ag_reseau_errors', $errors, false );

		return array(
			'found'  => $found,
			'kept'   => count( $bag ),
			'errors' => $errors,
		);
	}
}

/* ================================================================
   4. CRON quotidien + rappel des sorties du lendemain
   ================================================================ */
add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'ag_reseau_cron' ) ) {
		wp_schedule_event( time() + 300, 'daily', 'ag_reseau_cron' );
	}
} );

add_action( 'ag_reseau_cron', function () {
	$r = ag_reseau_fetch_all();

	// Rappel : ce que j'ai coché « j'y vais » et qui a lieu demain.
	$demain = gmdate( 'Y-m-d', current_time( 'timestamp' ) + DAY_IN_SECONDS );
	$rappel = array();
	foreach ( (array) get_option( 'ag_reseau_events', array() ) as $e ) {
		if ( isset( $e['statut'] ) && 'jy-vais' === $e['statut'] && 0 === strpos( $e['date'], $demain ) ) {
			$rappel[] = '• ' . $e['nom'] . ( $e['lieu'] ? ' — ' . $e['lieu'] : '' );
		}
	}
	if ( $rappel && function_exists( 'ag_push' ) ) {
		ag_push( '🤝 Demain tu réseautes', implode( "\n", $rappel ) );
	}

	// Alerte si de belles opportunités arrivent (score élevé, < 10 jours).
	if ( function_exists( 'ag_push' ) ) {
		$top = array();
		foreach ( (array) get_option( 'ag_reseau_events', array() ) as $e ) {
			$ts = strtotime( $e['date'] );
			if ( $ts && $ts < time() + ( 10 * DAY_IN_SECONDS ) && $e['score'] >= 78 && empty( $e['statut'] ) ) {
				$top[] = '• ' . date_i18n( 'j/m', $ts ) . ' — ' . $e['nom'];
			}
		}
		if ( count( $top ) >= 2 ) {
			ag_push( '🤝 Soirées à ne pas rater (Nantes)', implode( "\n", array_slice( $top, 0, 6 ) ) );
		}
	}
	unset( $r );
} );

/* ================================================================
   5. ADMIN — Prospection → 🤝 Réseautage Nantes
   ================================================================ */
add_action( 'admin_menu', function () {
	add_submenu_page(
		'ag-prospects',
		'Réseautage Nantes',
		'🤝 Réseautage',
		'manage_options',
		'ag-reseautage',
		'ag_reseau_render'
	);
}, 20 );

if ( ! function_exists( 'ag_reseau_render' ) ) {
	/** Écran principal du radar. */
	function ag_reseau_render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// -- Actions --
		if ( isset( $_POST['ag_reseau_scan'] ) && check_admin_referer( 'ag_reseau' ) ) {
			$r = ag_reseau_fetch_all();
			echo '<div class="notice notice-success"><p>Radar lancé : <strong>' . (int) $r['found'] . '</strong> événements lus, <strong>' . (int) $r['kept'] . '</strong> retenus.';
			if ( $r['errors'] ) {
				echo '<br><em>Sources en échec : ' . esc_html( implode( ' · ', $r['errors'] ) ) . '</em>';
			}
			echo '</p></div>';
		}
		if ( isset( $_POST['ag_reseau_save_urls'] ) && check_admin_referer( 'ag_reseau' ) ) {
			update_option( 'ag_reseau_urls', sanitize_textarea_field( wp_unslash( $_POST['ag_reseau_urls'] ) ) );
			echo '<div class="notice notice-success"><p>Sources enregistrées.</p></div>';
		}
		if ( isset( $_POST['ag_reseau_statut'], $_POST['ag_reseau_key'] ) && check_admin_referer( 'ag_reseau' ) ) {
			$evs = (array) get_option( 'ag_reseau_events', array() );
			$k   = sanitize_text_field( wp_unslash( $_POST['ag_reseau_key'] ) );
			if ( isset( $evs[ $k ] ) ) {
				$evs[ $k ]['statut'] = sanitize_text_field( wp_unslash( $_POST['ag_reseau_statut'] ) );
				if ( isset( $_POST['ag_reseau_notes'] ) ) {
					$evs[ $k ]['notes'] = sanitize_textarea_field( wp_unslash( $_POST['ag_reseau_notes'] ) );
				}
				update_option( 'ag_reseau_events', $evs, false );
				echo '<div class="notice notice-success"><p>Mis à jour.</p></div>';
			}
		}

		$events = (array) get_option( 'ag_reseau_events', array() );
		$last   = get_option( 'ag_reseau_last', '' );
		$filtre = isset( $_GET['f'] ) ? sanitize_text_field( wp_unslash( $_GET['f'] ) ) : 'tous';
		?>
		<div class="wrap">
			<h1>🤝 Réseautage Nantes <span style="font-size:13px;color:#666;font-weight:400">— où rencontrer des patrons</span></h1>

			<form method="post" style="margin:14px 0;display:flex;gap:10px;align-items:center;flex-wrap:wrap">
				<?php wp_nonce_field( 'ag_reseau' ); ?>
				<button class="button button-primary" name="ag_reseau_scan" value="1">🔄 Lancer le radar maintenant</button>
				<span style="color:#666">
					<?php
					echo $last
						? 'Dernière mise à jour : ' . esc_html( mysql2date( 'j M Y à H:i', $last ) ) . ' — ' . count( $events ) . ' événement(s).'
						: 'Jamais lancé. Clique sur « Lancer le radar » (puis c’est automatique tous les jours).';
					?>
				</span>
			</form>

			<h2 class="nav-tab-wrapper" style="margin-top:18px">
				<?php
				$tabs = array(
					'tous'    => 'Tous',
					'jy-vais' => '✅ J’y vais',
					'top'     => '⭐ Meilleurs',
					'gratuit' => '🆓 Gratuits',
				);
				foreach ( $tabs as $k => $lbl ) {
					printf(
						'<a href="%s" class="nav-tab%s">%s</a>',
						esc_url( admin_url( 'admin.php?page=ag-reseautage&f=' . $k ) ),
						$filtre === $k ? ' nav-tab-active' : '',
						esc_html( $lbl )
					);
				}
				?>
			</h2>

			<?php if ( ! $events ) : ?>
				<p style="margin-top:18px">Aucun événement en mémoire. Lance le radar ci-dessus — puis regarde l’annuaire en bas de page, il est utile tout de suite.</p>
			<?php else : ?>
				<table class="wp-list-table widefat fixed striped" style="margin-top:14px">
					<thead><tr>
						<th style="width:110px">Date</th>
						<th>Événement</th>
						<th style="width:170px">Lieu</th>
						<th style="width:70px">Prix</th>
						<th style="width:60px">Score</th>
						<th style="width:230px">Suivi</th>
					</tr></thead>
					<tbody>
					<?php
					foreach ( $events as $key => $e ) {
						$ts = strtotime( $e['date'] );
						if ( 'jy-vais' === $filtre && ( ! isset( $e['statut'] ) || 'jy-vais' !== $e['statut'] ) ) {
							continue;
						}
						if ( 'top' === $filtre && $e['score'] < 75 ) {
							continue;
						}
						if ( 'gratuit' === $filtre && 'Gratuit' !== $e['prix'] ) {
							continue;
						}
						$st = isset( $e['statut'] ) ? $e['statut'] : '';
						?>
						<tr>
							<td><strong><?php echo esc_html( $ts ? date_i18n( 'D j M', $ts ) : '—' ); ?></strong><br>
								<span style="color:#666"><?php echo esc_html( $ts ? date_i18n( 'H:i', $ts ) : '' ); ?></span></td>
							<td>
								<?php if ( $e['url'] ) : ?>
									<a href="<?php echo esc_url( $e['url'] ); ?>" target="_blank" rel="noopener"><strong><?php echo esc_html( $e['nom'] ); ?></strong></a>
								<?php else : ?>
									<strong><?php echo esc_html( $e['nom'] ); ?></strong>
								<?php endif; ?>
								<br><span style="color:#888;font-size:12px"><?php echo esc_html( $e['source'] ); ?></span>
								<?php if ( ! empty( $e['notes'] ) ) : ?>
									<br><em style="color:#555;font-size:12px">📝 <?php echo esc_html( $e['notes'] ); ?></em>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html( $e['lieu'] ? $e['lieu'] : $e['ville'] ); ?></td>
							<td><?php echo esc_html( $e['prix'] ? $e['prix'] : '—' ); ?></td>
							<td>
								<span style="display:inline-block;padding:2px 8px;border-radius:10px;font-weight:700;color:#fff;background:<?php echo $e['score'] >= 78 ? '#1a7f37' : ( $e['score'] >= 60 ? '#bf8700' : '#767676' ); ?>">
									<?php echo (int) $e['score']; ?>
								</span>
							</td>
							<td>
								<form method="post" style="display:flex;gap:4px;align-items:center">
									<?php wp_nonce_field( 'ag_reseau' ); ?>
									<input type="hidden" name="ag_reseau_key" value="<?php echo esc_attr( $key ); ?>">
									<select name="ag_reseau_statut" style="max-width:120px">
										<?php
										foreach ( array( '' => '—', 'jy-vais' => '✅ J’y vais', 'peut-etre' => '🤔 Peut-être', 'fait' => '🏁 J’y étais', 'non' => '✖ Non' ) as $v => $l ) {
											printf( '<option value="%s"%s>%s</option>', esc_attr( $v ), selected( $st, $v, false ), esc_html( $l ) );
										}
										?>
									</select>
									<button class="button button-small">OK</button>
								</form>
							</td>
						</tr>
					<?php } ?>
					</tbody>
				</table>
			<?php endif; ?>

			<h2 style="margin-top:34px">📒 Les réseaux qui comptent à Nantes</h2>
			<p style="color:#555;max-width:70ch">Ceux-là ne dépendent d’aucun robot : ce sont les rendez-vous récurrents où l’on croise des dirigeants. Commence par la CCI et un club local — c’est là qu’il y a le moins de concurrence entre prestataires.</p>
			<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(330px,1fr));gap:14px;margin-top:14px">
				<?php foreach ( ag_reseau_annuaire() as $a ) : ?>
					<div style="background:#fff;border:1px solid #dcdcde;border-left:4px solid #2271b1;border-radius:6px;padding:14px 16px">
						<div style="font-weight:700;font-size:1.02em"><?php echo esc_html( $a['nom'] ); ?></div>
						<div style="color:#666;font-size:12px;margin:2px 0 8px"><?php echo esc_html( $a['type'] . ' · ' . $a['rythme'] . ' · ' . $a['cout'] ); ?></div>
						<div style="font-size:13px;margin-bottom:6px"><strong>Cible :</strong> <?php echo esc_html( $a['cible'] ); ?></div>
						<div style="font-size:13px;color:#333;line-height:1.5"><?php echo esc_html( $a['entree'] ); ?></div>
						<a href="<?php echo esc_url( $a['url'] ); ?>" target="_blank" rel="noopener" class="button button-small" style="margin-top:10px">Ouvrir ↗</a>
					</div>
				<?php endforeach; ?>
			</div>

			<h2 style="margin-top:34px">⚙️ Sources du radar</h2>
			<form method="post">
				<?php wp_nonce_field( 'ag_reseau' ); ?>
				<p style="color:#555;max-width:70ch">Le radar lit les pages qui publient leurs événements au format standard (schema.org). Ajoute ici d’autres pages d’agenda (une URL par ligne) : agendas de clubs, pages Eventbrite d’une autre ville, etc.</p>
				<textarea name="ag_reseau_urls" rows="4" style="width:100%;max-width:760px" placeholder="https://exemple.fr/agenda"><?php echo esc_textarea( get_option( 'ag_reseau_urls', '' ) ); ?></textarea>
				<p><button class="button" name="ag_reseau_save_urls" value="1">Enregistrer les sources</button></p>
			</form>
			<p style="color:#666;font-size:12px">Sources actives :
				<?php
				$labels = array();
				foreach ( ag_reseau_sources() as $s ) {
					$labels[] = $s['label'];
				}
				echo esc_html( implode( ' · ', $labels ) );
				?>
			</p>
		</div>
		<?php
	}
}
