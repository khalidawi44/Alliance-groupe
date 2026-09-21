<?php
/**
 * LA CHAINE DE MONTAGE — compléter une fiche prospect, en passif
 * ---------------------------------------------------------------------------
 * Le problème résolu : Matteo (Google Places) ramène le nom, la ville, le
 * téléphone et le SITE — mais JAMAIS l'email (l'API ne le donne pas) et pas
 * d'analyse. Résultat : 81 fiches sur 96 hors de portée de Hugo, et rien de
 * vrai à dire à celles qui restent.
 *
 * Ce module prend une fiche à moitié vide et la complète, en RELISANT ce que
 * le site publie déjà (aucun appel Google, aucune intrusion) :
 *   1. l'email de contact  → ag_audit_extract_contacts()  (déjà écrit)
 *   2. une note de sécurité → ag_audit_get()              (déjà écrit, PASSIF)
 *   3. UN constat factuel   → ag_enrich_constat()          (ci-dessous)
 *
 * ─────────────────────────────────────────────────────────────────────────
 * LA LIGNE ROUGE, tenue par le code et pas par la bonne volonté.
 *
 * L'audit passif voit des choses de deux natures :
 *   · des défauts VISIBLES DE DEHORS, que n'importe quel visiteur constate
 *     (pas de cadenas, site illisible sur téléphone, lenteur) ;
 *   · des FAILLES EXPLOITABLES (.git exposé, version divulguée, comptes
 *     énumérables, xmlrpc ouvert).
 *
 * Hugo écrit à des inconnus. On ne met JAMAIS une faille exploitable dans un
 * message à un inconnu : en droit c'est une menace déguisée, en fait on ne
 * sait pas qui relève cette boîte. Le constat se construit donc par LISTE
 * BLANCHE (ag_enrich_constat) : seuls les défauts de surface deviennent une
 * phrase, tout le reste est muet. Un contrôle exploitable ajouté plus tard à
 * l'audit ne fuitera pas — il n'est pas dans la liste, donc il est ignoré.
 *
 * Les failles exploitables restent pour le rapport d'audit COMPLET, remis à
 * un client qui a SIGNÉ une autorisation. C'est la prestation, pas l'hameçon.
 *
 * @package alliance-groupe-theme
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! defined( 'AG_ENRICH_VER' ) ) { define( 'AG_ENRICH_VER', '1.0.0' ); }

/** Fiches complétées par nuit : on avance au pas, on ne martèle pas les sites. */
if ( ! defined( 'AG_ENRICH_PAR_NUIT' ) ) { define( 'AG_ENRICH_PAR_NUIT', 20 ); }

/** On ne ré-analyse pas un site avant ce délai (jours). */
if ( ! defined( 'AG_ENRICH_FRAICHEUR' ) ) { define( 'AG_ENRICH_FRAICHEUR', 90 ); }

/* ── 1. Choisir la meilleure adresse ─────────────────────────────────── */

if ( ! function_exists( 'ag_enrich_meilleur_email' ) ) {
	/**
	 * Parmi les emails trouvés sur le site, garder le plus défendable.
	 *
	 * Priorité : une adresse de FONCTION sur le domaine du site
	 * (contact@, info@…) — moins sensible au RGPD qu'une adresse nominative,
	 * et elle arrive chez la bonne personne. Puis toute adresse du domaine.
	 * En dernier une adresse gratuite (beaucoup d'artisans n'ont que ça).
	 *
	 * @param array  $emails  liste trouvée
	 * @param string $site    l'URL du site (pour reconnaître « son » domaine)
	 * @return string  l'email retenu, ou ''
	 */
	function ag_enrich_meilleur_email( $emails, $site ) {
		$emails = array_values( array_unique( array_filter( array_map( 'strtolower', (array) $emails ) ) ) );
		if ( ! $emails ) { return ''; }

		$domaine = strtolower( (string) wp_parse_url( $site, PHP_URL_HOST ) );
		$domaine = preg_replace( '/^www\./', '', $domaine );

		$fonctions = array( 'contact', 'info', 'bonjour', 'hello', 'accueil', 'commercial', 'devis' );
		$gratuits  = array( 'gmail.', 'orange.', 'wanadoo.', 'free.', 'hotmail.', 'outlook.', 'yahoo.', 'laposte.', 'sfr.', 'live.' );

		$duDomaine = array();
		foreach ( $emails as $e ) {
			$hote = substr( strrchr( $e, '@' ), 1 );
			if ( $domaine && $hote && ( $hote === $domaine || str_ends_with( $hote, '.' . $domaine ) ) ) {
				$duDomaine[] = $e;
			}
		}

		// 1. Adresse de fonction sur le domaine du site.
		foreach ( $duDomaine as $e ) {
			$avant = substr( $e, 0, (int) strpos( $e, '@' ) );
			foreach ( $fonctions as $f ) { if ( str_starts_with( $avant, $f ) ) { return $e; } }
		}
		// 2. N'importe quelle adresse du domaine.
		if ( $duDomaine ) { return $duDomaine[0]; }
		// 3. À défaut, une adresse (même gratuite) — on prend la première non nominative si possible.
		foreach ( $emails as $e ) {
			$avant = substr( $e, 0, (int) strpos( $e, '@' ) );
			foreach ( $fonctions as $f ) { if ( str_starts_with( $avant, $f ) ) { return $e; } }
		}
		return $emails[0];
	}
}

/* ── 2. Le constat — LISTE BLANCHE, rien d'autre ne passe ─────────────── */

if ( ! function_exists( 'ag_enrich_constat' ) ) {
	/**
	 * Traduit l'audit en UNE phrase vraie, visible de dehors, non exploitable.
	 * Ne renvoie que ce qui est dans la liste blanche ci-dessous.
	 *
	 * @param array  $audit  retour de ag_audit_get()
	 * @param string $site   l'URL (vide = pas de site)
	 * @return string  le constat, ou '' si rien de mentionnable
	 */
	function ag_enrich_constat( $audit, $site ) {
		if ( '' === trim( (string) $site ) ) {
			return "n'apparaît avec aucun vrai site quand un client la cherche sur Google";
		}

		$checks = array();
		foreach ( (array) ( $audit['checks'] ?? array() ) as $c ) {
			$checks[ (string) ( $c['name'] ?? '' ) ] = (string) ( $c['status'] ?? '' );
		}
		$rate = function ( $nom ) use ( $checks ) {
			return isset( $checks[ $nom ] ) && in_array( $checks[ $nom ], array( 'fail', 'warn' ), true );
		};

		/* LISTE BLANCHE : nom du contrôle → phrase. Défauts de SURFACE only.
		   Tout contrôle absent d'ici (donc toute faille exploitable) est muet. */
		$ordre = array(
			array( fn() => $rate( 'Connexion sécurisée (HTTPS)' ) || $rate( 'Certificat SSL' ),
				"son site s'ouvre sur un avertissement de sécurité dans les navigateurs (pas de cadenas), ce qui fait fuir des visiteurs" ),
			array( fn() => $rate( 'Compatible mobile (viewport)' ),
				"son site n'est pas adapté aux téléphones — or la plupart des clients cherchent depuis leur mobile" ),
			array( fn() => ( (int) ( $audit['time_ms'] ?? 0 ) > 5000 ),
				"son site met plus de cinq secondes à s'afficher, et une page lente perd la moitié des visiteurs" ),
			array( fn() => $rate( 'Balise <title>' ) || $rate( 'Titre H1' ),
				"il manque à son site le titre que Google affiche dans ses résultats, donc il ressort mal dans les recherches" ),
			array( fn() => $rate( 'Meta description' ),
				"sa fiche dans Google n'a pas de description, ce qui donne un résultat vide et peu cliqué" ),
		);

		foreach ( $ordre as $c ) {
			if ( call_user_func( $c[0] ) ) { return $c[1]; }
		}
		return ''; // rien de mentionnable en surface : Hugo parlera d'autre chose
	}
}

/* ── 3. Compléter UNE fiche ──────────────────────────────────────────── */

if ( ! function_exists( 'ag_enrich_fiche' ) ) {
	/**
	 * Complète une fiche par référence. Ne touche jamais une donnée saisie à
	 * la main : on ajoute, on n'écrase pas.
	 *
	 * @return array  ce qui a été fait : email_trouve, audit_fait, constat
	 */
	function ag_enrich_fiche( &$p ) {
		$bilan = array( 'email_trouve' => false, 'audit_fait' => false, 'constat' => false );
		$site  = trim( (string) ( $p['website'] ?? '' ) );

		/* a) l'email, si absent et si un site existe */
		if ( '' === trim( (string) ( $p['email'] ?? '' ) ) && '' !== $site
			&& function_exists( 'ag_audit_extract_contacts' ) ) {
			$contacts = ag_audit_extract_contacts( $site );
			$mail     = ag_enrich_meilleur_email( $contacts['emails'] ?? array(), $site );
			if ( is_email( $mail ) ) {
				$p['email']        = $mail;
				$p['email_source'] = 'site';
				$bilan['email_trouve'] = true;
			}
			// bonus utile pour le contrat plus tard, jamais écrasé
			if ( empty( $p['siret'] ) && ! empty( $contacts['siret'] ) ) { $p['siret'] = $contacts['siret']; }
		}

		/* b) l'audit, si absent ou périmé, et si un site existe */
		$vieux = (int) ( $p['audit_ts'] ?? 0 );
		$perime = ( 0 === $vieux ) || ( ( time() - $vieux ) > AG_ENRICH_FRAICHEUR * DAY_IN_SECONDS );
		if ( '' !== $site && $perime && function_exists( 'ag_audit_get' ) ) {
			$a = ag_audit_get( $site );
			if ( is_array( $a ) && isset( $a['score'] ) ) {
				$p['audit_score'] = (int) $a['score'];
				$p['audit_crit']  = (int) ( $a['critical'] ?? 0 );
				$p['audit_ts']    = time();
				$bilan['audit_fait'] = true;
			}
		}

		/* c) le constat — une phrase vraie que Hugo POURRA citer, ou rien */
		$audit = ( '' !== $site && function_exists( 'ag_audit_get' ) ) ? ag_audit_get( $site ) : array();
		$constat = ag_enrich_constat( $audit, $site );
		if ( '' !== $constat ) {
			$p['constat']    = $constat;
			$bilan['constat'] = true;
		}

		$p['enrich_ts'] = time(); // « déjà passé en revue » : on ne rouvre pas demain
		return $bilan;
	}
}

/* ── 4. Le tour de nuit ──────────────────────────────────────────────── */

if ( ! function_exists( 'ag_enrich_a_faire' ) ) {
	/** Une fiche mérite un passage si on ne l'a jamais vue, ou pas depuis la fraîcheur. */
	function ag_enrich_a_faire( $p ) {
		if ( ! empty( $p['closer_stop'] ) ) { return false; }
		if ( in_array( (string) ( $p['status'] ?? '' ), array( 'client', 'ne_pas_contacter', 'refus' ), true ) ) { return false; }
		$sans_email = '' === trim( (string) ( $p['email'] ?? '' ) );
		$sans_audit = 0 === (int) ( $p['audit_ts'] ?? 0 );
		$jamais_vue = 0 === (int) ( $p['enrich_ts'] ?? 0 );
		return $sans_email || $sans_audit || $jamais_vue;
	}
}

if ( ! function_exists( 'ag_enrich_tour' ) ) {
	/**
	 * Complète jusqu'à AG_ENRICH_PAR_NUIT fiches. Priorité aux fiches SANS
	 * email et AVEC site : ce sont celles qui débloquent le plus de prospects
	 * pour Hugo.
	 *
	 * @return array  vues, emails, audits
	 */
	function ag_enrich_tour( $limite = AG_ENRICH_PAR_NUIT ) {
		$list = (array) get_option( 'ag_prospects', array() );
		$bilan = array( 'vues' => 0, 'emails' => 0, 'audits' => 0 );

		// Ordre de priorité : d'abord celles qu'un email débloquerait.
		$ordre = array_keys( $list );
		usort( $ordre, function ( $a, $b ) use ( $list ) {
			$pa = ( '' === trim( (string) ( $list[ $a ]['email'] ?? '' ) ) && '' !== trim( (string) ( $list[ $a ]['website'] ?? '' ) ) ) ? 0 : 1;
			$pb = ( '' === trim( (string) ( $list[ $b ]['email'] ?? '' ) ) && '' !== trim( (string) ( $list[ $b ]['website'] ?? '' ) ) ) ? 0 : 1;
			return $pa <=> $pb;
		} );

		foreach ( $ordre as $i ) {
			if ( $bilan['vues'] >= $limite ) { break; }
			if ( ! ag_enrich_a_faire( $list[ $i ] ) ) { continue; }
			$copie = $list[ $i ];
			$r = ag_enrich_fiche( $copie );
			$list[ $i ] = $copie;
			$bilan['vues']++;
			if ( $r['email_trouve'] ) { $bilan['emails']++; }
			if ( $r['audit_fait'] ) { $bilan['audits']++; }
		}

		if ( $bilan['vues'] ) {
			update_option( 'ag_prospects', $list, false );
			update_option( 'ag_enrich_derniere', array( 'ts' => time(), 'bilan' => $bilan ), false );
			if ( function_exists( 'ag_activity_log' ) ) {
				ag_activity_log( '🔎 Enrichissement : ' . $bilan['vues'] . ' fiche(s), '
					. $bilan['emails'] . ' email(s), ' . $bilan['audits'] . ' audit(s).' );
			}
		}
		return $bilan;
	}
}

if ( ! function_exists( 'ag_enrich_on' ) ) {
	function ag_enrich_on() { return (bool) get_option( 'ag_enrich_on', 0 ); }
}

add_action( 'init', function () {
	if ( ag_enrich_on() && ! wp_next_scheduled( 'ag_enrich_cron' ) ) {
		wp_schedule_event( strtotime( 'tomorrow 3:00' ), 'daily', 'ag_enrich_cron' );
	}
	if ( ! ag_enrich_on() && wp_next_scheduled( 'ag_enrich_cron' ) ) {
		wp_clear_scheduled_hook( 'ag_enrich_cron' );
	}
} );

add_action( 'ag_enrich_cron', function () {
	if ( ag_enrich_on() ) { ag_enrich_tour(); }
} );

/* ── 5. Nourrir Hugo : le constat entre dans son contexte ────────────── */

/* Hugo lit $p['constat'] s'il existe. Le filtre ci-dessous est le SEUL point
   où le constat rejoint le message ; il ne laisse jamais passer une faille
   (le constat a déjà été filtré par liste blanche à la création). */
add_filter( 'ag_closer_contexte_extra', function ( $extra, $p ) {
	$constat = trim( (string) ( $p['constat'] ?? '' ) );
	if ( '' !== $constat ) {
		$extra .= "Constat vrai et vérifiable à mentionner (une seule chose, sans dramatiser) : "
			. $constat . "\n";
	}
	return $extra;
}, 10, 2 );

/* ── 6. L'écran ──────────────────────────────────────────────────────── */

add_action( 'admin_menu', function () {
	add_submenu_page(
		'ag-prospects', 'Compléter les fiches', '🔎 Compléter les fiches',
		'manage_options', 'ag-enrichir', 'ag_enrich_ecran'
	);
}, 33 );

if ( ! function_exists( 'ag_enrich_ecran' ) ) {
	function ag_enrich_ecran() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		$msg = '';

		if ( isset( $_POST['ag_enrich_save'] ) && check_admin_referer( 'ag_enrich' ) ) {
			update_option( 'ag_enrich_on', isset( $_POST['on'] ) ? 1 : 0, false );
			$msg = 'Réglages enregistrés.';
		}
		$tour = null;
		if ( isset( $_POST['ag_enrich_now'] ) && check_admin_referer( 'ag_enrich' ) ) {
			$tour = ag_enrich_tour();
			$msg  = $tour['vues'] . ' fiche(s) passées en revue · ' . $tour['emails']
				. ' email(s) trouvé(s) · ' . $tour['audits'] . ' audit(s).';
		}

		// Compte de l'état actuel.
		$list = (array) get_option( 'ag_prospects', array() );
		$total = count( $list ); $sans_email = 0; $avec_site_sans_email = 0; $a_faire = 0;
		foreach ( $list as $p ) {
			$se = '' === trim( (string) ( $p['email'] ?? '' ) );
			if ( $se ) { $sans_email++; if ( '' !== trim( (string) ( $p['website'] ?? '' ) ) ) { $avec_site_sans_email++; } }
			if ( ag_enrich_a_faire( $p ) ) { $a_faire++; }
		}
		$last = get_option( 'ag_enrich_derniere', array() );
		?>
		<div class="wrap">
			<h1>🔎 Compléter les fiches</h1>
			<p class="description" style="max-width:58em">
				La nuit, le site relit ce que publient les sites de vos prospects — <strong>sans aucun appel
				Google et sans jamais y entrer</strong> — pour y trouver l'email de contact et un constat vrai
				que Hugo pourra citer. Les fiches sans email deviennent joignables.
			</p>

			<?php if ( $msg ) : ?><div class="notice notice-info"><p><?php echo esc_html( $msg ); ?></p></div><?php endif; ?>

			<table class="widefat" style="max-width:44em;margin:16px 0">
				<tbody>
					<tr><td>Prospects au total</td><td><strong><?php echo (int) $total; ?></strong></td></tr>
					<tr><td>Sans email (hors de portée de Hugo)</td><td><strong><?php echo (int) $sans_email; ?></strong></td></tr>
					<tr><td>…dont <strong>avec un site à relire</strong> (récupérables)</td><td><strong style="color:#1a7f37"><?php echo (int) $avec_site_sans_email; ?></strong></td></tr>
					<tr><td>Fiches à passer en revue</td><td><strong><?php echo (int) $a_faire; ?></strong></td></tr>
					<?php if ( ! empty( $last['ts'] ) ) : ?>
					<tr><td>Dernier tour</td><td><?php echo esc_html( date_i18n( 'd/m/Y H:i', (int) $last['ts'] ) ); ?></td></tr>
					<?php endif; ?>
				</tbody>
			</table>

			<form method="post">
				<?php wp_nonce_field( 'ag_enrich' ); ?>
				<table class="form-table">
					<tr><th scope="row">Compléter chaque nuit</th><td>
						<label><input type="checkbox" name="on" value="1" <?php checked( ag_enrich_on() ); ?>>
							Oui — <?php echo (int) AG_ENRICH_PAR_NUIT; ?> fiches par nuit, à 3h</label>
						<p class="description">Priorité aux fiches qui ont un site mais pas d'email : ce sont
							celles qu'un passage rend joignables.</p>
					</td></tr>
				</table>
				<p>
					<button class="button button-primary" name="ag_enrich_save" value="1">Enregistrer</button>
					<button class="button" name="ag_enrich_now" value="1">Compléter maintenant (<?php echo (int) AG_ENRICH_PAR_NUIT; ?> fiches)</button>
				</p>
			</form>

			<h2>Ce qui est cherché, et ce qui ne l'est pas</h2>
			<ul style="max-width:58em;list-style:disc;padding-left:22px">
				<li><strong>Cherché :</strong> l'email de contact publié sur le site (page contact, mentions
					légales), et un défaut <em>visible de tout le monde</em> — pas de cadenas, site illisible
					sur mobile, lenteur, titre manquant dans Google.</li>
				<li><strong>Jamais mis dans un message :</strong> les failles exploitables que l'audit détecte
					par ailleurs. On ne liste pas les vulnérabilités d'un inconnu dans un courriel — c'est le
					contenu du <strong>rapport d'audit complet</strong>, remis à un client qui a signé une
					autorisation.</li>
				<li><strong>Jamais d'intrusion :</strong> on lit uniquement ce que le site publie de lui-même.</li>
				<li><strong>Rien n'est écrasé :</strong> un email ou une donnée saisie à la main est conservé.</li>
			</ul>
		</div>
		<?php
	}
}
