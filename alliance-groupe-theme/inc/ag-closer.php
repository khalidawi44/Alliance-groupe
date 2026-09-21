<?php
/**
 * ag-closer.php — AG Closer : l'agent commercial autonome.
 *
 * Ce qui existait deja : le robot TROUVE les prospects (Google Places), les
 * range dans le CRM, les attribue a une zone, et PREVIENT un humain qu'il y
 * a des relances a faire. Puis il s'arrete. Personne n'ecrit, et les
 * prospects dorment. Ce module ferme la boucle : il redige et il envoie.
 *
 * Une sequence de quatre messages espaces, ecrits un par un par l'IA a
 * partir de ce qu'on sait reellement du prospect — son metier, sa ville,
 * l'etat de son site. Puis l'agent lache. Un prospect qui repond sort de la
 * sequence a la seconde ou il repond : un humain prend le relais.
 *
 * ── CE QUE CET AGENT NE FAIT PAS ─────────────────────────────────────────
 * Il ne signe rien a la place de personne. Il mene jusqu'au contrat et
 * envoie le lien de signature ; le client signe lui-meme, avec une preuve
 * scellee (inc/ag-signature.php). Signer pour autrui est un faux, et un
 * contrat obtenu ainsi ne vaut rien le jour ou il faut le faire valoir.
 *
 * ── LES GARDE-FOUS, ET POURQUOI ──────────────────────────────────────────
 * · INTERRUPTEUR MAITRE a OFF par defaut. Un demarchage ne demarre jamais
 *   parce qu'un fichier a ete deploye.
 * · Plafond quotidien (defaut 20). Au-dela, ce n'est plus de la prospection.
 * · Jamais deux messages au meme prospect en moins de 48 h.
 * · Jamais un prospect « client », « refus », « ne plus contacter »,
 *   « ignore », « interesse », ni un qui a deja repondu.
 * · AVOCATS : email uniquement, jamais de SMS ni d'appel a froid. C'est leur
 *   deontologie (RIN/CNB), et une faute ferme la profession entiere.
 * · Chaque message dit qu'il est automatise et porte un lien d'opposition en
 *   UN clic, sans rien a ecrire.
 * · Tout est journalise. Sans journal, pas de preuve.
 *
 * ── CE QUE DIT LE DROIT ──────────────────────────────────────────────────
 * · B2B / email : prospection possible sans consentement prealable (interet
 *   legitime), si l'objet concerne la fonction de la personne et que
 *   l'opposition est immediate. C'est le cas ici.
 * · B2C / telephone : BLOCTEL obligatoire. Ce module n'appelle personne.
 * · Transparence : le destinataire doit comprendre qu'il s'agit d'un envoi
 *   automatise. C'est ecrit dans chaque message.
 *
 * @package Alliance_Groupe
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! defined( 'AG_CLOSER_VER' ) ) { define( 'AG_CLOSER_VER', '1.0.0' ); }

/* ── 1. Reglages ─────────────────────────────────────────────────────── */

if ( ! function_exists( 'ag_closer_on' ) ) {
	/** L'agent est-il allume ? Defaut : NON. */
	function ag_closer_on() { return (bool) get_option( 'ag_closer_on', 0 ); }
}
if ( ! function_exists( 'ag_closer_cap' ) ) {
	/** Plafond de messages par jour, tous prospects confondus. */
	function ag_closer_cap() { return max( 1, (int) get_option( 'ag_closer_cap_jour', 20 ) ); }
}
if ( ! function_exists( 'ag_closer_expediteur' ) ) {
	/**
	 * L'adresse d'envoi.
	 *
	 * ATTENTION, et c'est le point le plus couteux de tout ce module :
	 * demarcher depuis le domaine principal peut faire tomber la
	 * delivrabilite de TOUS les emails de la maison — devis, factures, cles
	 * de licence comprises. Un sous-domaine dedie, avec son propre
	 * SPF/DKIM/DMARC, isole le risque.
	 *
	 * @return array array( email, nom )
	 */
	function ag_closer_expediteur() {
		$mail = sanitize_email( (string) get_option( 'ag_closer_from_mail', '' ) );
		$nom  = trim( (string) get_option( 'ag_closer_from_nom', '' ) );
		if ( ! is_email( $mail ) ) { $mail = ''; }
		if ( '' === $nom ) { $nom = 'Alliance Groupe'; }
		return array( $mail, $nom );
	}
}
if ( ! function_exists( 'ag_closer_sequence' ) ) {
	/**
	 * La sequence : quatre messages, puis on lache.
	 *
	 * Les delais sont en jours depuis le message precedent. C'est l'INTENTION
	 * qui change d'un message a l'autre, pas le gabarit : un prospect qui
	 * recoit quatre fois la meme phrase reformulee le voit tout de suite.
	 */
	function ag_closer_sequence() {
		return array(
			array( 'delai' => 0, 'intention' => "Premier contact. Tu as REGARDE son site (ou constate qu'il n'en a pas). Dis UNE chose precise et vraie sur ce que tu as vu, puis propose de lui montrer a quoi ressemblerait le sien. Trois phrases maximum." ),
			array( 'delai' => 3, 'intention' => "Relance courte. Il n'a pas repondu. Pas de reproche, pas de « je me permets de revenir vers vous ». Une seule phrase utile, et une question fermee a laquelle on repond par oui ou non." ),
			array( 'delai' => 5, 'intention' => "Apport de valeur. Donne-lui quelque chose d'utile meme s'il n'achete jamais : un constat concret sur sa visibilite, ou ce que ses concurrents font et qu'il ne fait pas. AUCUNE relance commerciale dans ce message." ),
			array( 'delai' => 7, 'intention' => "Derniere. Annonce clairement que c'est le dernier message et qu'il n'entendra plus parler de nous sauf s'il le demande. Laisse la porte ouverte sans insister. Deux phrases." ),
		);
	}
}
if ( ! function_exists( 'ag_closer_est_avocat' ) ) {
	/** Avocat : email ou courrier uniquement. Meme test que le reste du CRM. */
	function ag_closer_est_avocat( $p ) {
		return false !== stripos( ( $p['type'] ?? '' ) . ' ' . ( $p['name'] ?? '' ), 'avocat' );
	}
}
if ( ! function_exists( 'ag_closer_est_pro_web' ) ) {
	/**
	 * Ce prospect vit-il DEJA du web ? On ne vend pas un site a un webmaster.
	 * On teste le metier ET le nom (souvent « … Web », « Agence … »).
	 * Filtrable : ag_closer_metiers_web pour ajuster la liste par site.
	 */
	function ag_closer_est_pro_web( $p ) {
		$mots = apply_filters( 'ag_closer_metiers_web', array(
			'webmaster', 'web master', 'agence web', 'agence digitale', 'agence de communication',
			'developpeur web', 'développeur web', 'creation site', 'création site', 'creation de site',
			'création de site', 'referencement', 'référencement', 'consultant seo', 'freelance web',
			'webdesign', 'web design', 'integrateur web', 'intégrateur web', 'community manager',
			'growth', 'marketing digital', 'wordpress',
		) );
		$foin = mb_strtolower( ( $p['type'] ?? '' ) . ' ' . ( $p['name'] ?? '' ) );
		foreach ( $mots as $m ) {
			if ( false !== mb_strpos( $foin, mb_strtolower( $m ) ) ) { return true; }
		}
		return false;
	}
}

/* ── 2. Le lien « ne plus me contacter » ─────────────────────────────── */

if ( ! function_exists( 'ag_closer_stop_token' ) ) {
	/** Jeton d'opposition, lie a l'identifiant du prospect. Non devinable. */
	function ag_closer_stop_token( $id ) {
		$cle = defined( 'AUTH_SALT' ) ? AUTH_SALT : ( defined( 'AUTH_KEY' ) ? AUTH_KEY : 'ag-closer' );
		return substr( hash_hmac( 'sha256', 'stop|' . $id, $cle ), 0, 24 );
	}
}
if ( ! function_exists( 'ag_closer_stop_url' ) ) {
	function ag_closer_stop_url( $id ) {
		return add_query_arg( array( 'ag_stop' => rawurlencode( (string) $id ), 'k' => ag_closer_stop_token( $id ) ), home_url( '/' ) );
	}
}

/* Un clic suffit. Pas de formulaire, pas de compte, pas de « confirmez votre
   choix » : l'opposition doit etre plus facile que le message ne l'a ete. */
add_action( 'template_redirect', function () {
	if ( empty( $_GET['ag_stop'] ) || empty( $_GET['k'] ) ) { return; }
	$id = sanitize_text_field( wp_unslash( $_GET['ag_stop'] ) );
	$k  = sanitize_text_field( wp_unslash( $_GET['k'] ) );
	if ( ! hash_equals( ag_closer_stop_token( $id ), $k ) ) {
		wp_die( 'Lien invalide.', 'Lien invalide', array( 'response' => 400 ) );
	}

	$list = (array) get_option( 'ag_prospects', array() );
	$nom  = '';
	foreach ( $list as $i => $p ) {
		if ( ( $p['id'] ?? '' ) !== $id ) { continue; }
		$nom = (string) ( $p['name'] ?? '' );
		$list[ $i ]['status']      = 'ne_pas_contacter';
		$list[ $i ]['closer_stop'] = time();
		$list[ $i ]['notes']       = trim( ( $p['notes'] ?? '' ) . "\n" . gmdate( 'd/m/Y' ) . ' — opposition demandee depuis un message automatise.' );
		update_option( 'ag_prospects', $list, false );
		break;
	}
	if ( function_exists( 'ag_activity_log' ) ) {
		ag_activity_log( '🚫 Opposition : ' . ( $nom ? $nom : $id ) . ' ne sera plus contacte.' );
	}

	/* Page vue par quelqu'un qui vient de nous dire stop. C'est la page la plus
	   sensible de toute la chaine : elle doit donner l'adresse PUBLIQUE de la
	   maison, pas l'adresse d'administration du site. */
	$lg_stop   = function_exists( 'ag_company_legal' ) ? (array) ag_company_legal() : array();
	$mail_stop = trim( (string) ( $lg_stop['email'] ?? '' ) ) ?: (string) get_option( 'admin_email' );

	nocache_headers();
	wp_die(
		'<p style="font:16px/1.6 system-ui;max-width:34em">C\'est fait. Votre adresse est retirée : vous ne recevrez plus aucun message de notre part.</p>'
		. '<p style="font:14px/1.6 system-ui;color:#666;max-width:34em">Aucune autre action n\'est nécessaire. Si vous receviez malgré tout un message après aujourd\'hui, écrivez à '
		. esc_html( $mail_stop ) . ' : ce serait une erreur de notre part, et nous la corrigerions.</p>',
		'Vous ne serez plus contacté', array( 'response' => 200 )
	);
}, 1 );

/* ── 2 bis. Les adresses de la MAISON — on ne se demarche pas soi-meme ── */

if ( ! function_exists( 'ag_closer_maison' ) ) {
	/**
	 * L'ensemble des adresses qui SONT la maison (Fabrice, le site, les boites
	 * de notification, l'expediteur du demarchage). Hugo ne doit jamais ecrire
	 * a l'une d'elles : ce sont nos propres adresses, pas des prospects. Le
	 * constat qui a declenche ce garde-fou : Hugo a demarche fabrice.doucet44@,
	 * contact@alliancegroupe-inc.com et l'adresse de notification — nous nous
	 * ecrivions a nous-memes.
	 *
	 * Filtrable (ag_closer_adresses_maison) pour en ajouter par site, et
	 * completee par l'option `ag_closer_ne_pas_demarcher` (une adresse ou un
	 * domaine par ligne) que Fabrice remplit lui-meme pour ecarter des entrees
	 * de test sans toucher au code.
	 *
	 * @return array liste d'adresses en minuscules (dedupliquee)
	 */
	function ag_closer_maison() {
		$m = array();

		// Boite de notification de la maison (la ou arrivent devis, contrats…).
		$m[] = (string) apply_filters( 'ag_calendar_notify_email', get_option( 'ag_calendar_email', 'advise.alliance.group@gmail.com' ) );
		// Adresse d'administration WordPress.
		$m[] = (string) get_option( 'admin_email' );
		// Identite legale publiee (contact@…).
		if ( function_exists( 'ag_company_legal' ) ) {
			$lg  = (array) ag_company_legal();
			$m[] = (string) ( $lg['email'] ?? '' );
		}
		// Compte et expediteur SMTP authentifie.
		if ( function_exists( 'ag_smtp_opt' ) ) {
			$m[] = ag_smtp_opt( 'user' );
			$m[] = ag_smtp_opt( 'from' );
		}
		// Adresse d'envoi du demarchage lui-meme.
		list( $from_mail ) = ag_closer_expediteur();
		$m[] = (string) $from_mail;
		// Adresses connues du proprietaire (constat du 21/09 : Hugo se les envoyait).
		$m[] = 'fabrice.doucet44@gmail.com';
		$m[] = 'advise.alliance.group@gmail.com';
		$m[] = 'contact@alliancegroupe-inc.com';

		$m = apply_filters( 'ag_closer_adresses_maison', $m );

		$out = array();
		foreach ( (array) $m as $addr ) {
			$addr = strtolower( trim( (string) $addr ) );
			if ( is_email( $addr ) ) { $out[] = $addr; }
		}
		return array_values( array_unique( $out ) );
	}
}

if ( ! function_exists( 'ag_closer_email_bloque' ) ) {
	/**
	 * Cette adresse doit-elle etre ecartee du demarchage ? Vrai si c'est une
	 * adresse de la maison, ou si elle correspond a une entree (adresse exacte
	 * ou domaine) de la liste `ag_closer_ne_pas_demarcher` remplie par Fabrice.
	 */
	function ag_closer_email_bloque( $email ) {
		$email = strtolower( trim( (string) $email ) );
		if ( '' === $email || ! is_email( $email ) ) { return true; } // pas d'adresse valide = on n'ecrit pas

		if ( in_array( $email, ag_closer_maison(), true ) ) { return true; }

		$dom = (string) substr( strrchr( $email, '@' ), 1 );
		$brut = (string) get_option( 'ag_closer_ne_pas_demarcher', '' );
		foreach ( preg_split( '/[\r\n,;]+/', $brut ) as $ligne ) {
			$ligne = strtolower( trim( $ligne ) );
			if ( '' === $ligne ) { continue; }
			if ( $ligne === $email ) { return true; }                       // adresse exacte
			if ( false === strpos( $ligne, '@' ) && $ligne === $dom ) { return true; } // domaine entier
		}
		return false;
	}
}

/* ── 3. Qui peut etre demarche, et quand ─────────────────────────────── */

if ( ! function_exists( 'ag_closer_eligible' ) ) {
	/**
	 * L'etape a envoyer a ce prospect, ou false.
	 *
	 * @return array|false
	 */
	function ag_closer_eligible( $p ) {
		$seq  = ag_closer_sequence();
		$step = (int) ( $p['closer_step'] ?? 0 );

		if ( $step >= count( $seq ) )                       { return false; } // sequence finie
		if ( ! empty( $p['closer_stop'] ) )                 { return false; } // opposition
		if ( ! empty( $p['replied'] ) )                     { return false; } // il a repondu
		if ( ! is_email( (string) ( $p['email'] ?? '' ) ) ) { return false; } // rien pour ecrire
		if ( ag_closer_email_bloque( (string) ( $p['email'] ?? '' ) ) ) { return false; } // la maison ou une adresse ecartee : on ne s'ecrit pas a soi-meme

		$interdits = array( 'client', 'refus', 'ne_pas_contacter', 'ignore', 'interesse' );
		if ( in_array( (string) ( $p['status'] ?? '' ), $interdits, true ) ) { return false; }

		$dernier = (int) ( $p['closer_last'] ?? 0 );
		if ( 0 === $dernier ) { return $seq[ $step ]; }                      // jamais touche
		if ( ( time() - $dernier ) < 2 * DAY_IN_SECONDS ) { return false; }  // garde-fou absolu
		if ( ( time() - $dernier ) < $seq[ $step ]['delai'] * DAY_IN_SECONDS ) { return false; }
		return $seq[ $step ];
	}
}

/* ── 4. Le message ───────────────────────────────────────────────────── */

if ( ! function_exists( 'ag_closer_redige' ) ) {
	/**
	 * Fait ecrire le message par l'IA. Sans IA, on retombe sur un gabarit
	 * sobre — mieux vaut un message banal qu'aucun message, mais on ne
	 * pretend pas que c'est la meme chose.
	 *
	 * @return array array( 'objet' => string, 'corps' => string )
	 */
	function ag_closer_redige( $p, $etape ) {
		$nom    = (string) ( $p['name'] ?? '' );
		$metier = (string) ( $p['type'] ?? '' );
		$ville  = (string) ( $p['city'] ?? '' );
		$site   = (string) ( $p['website'] ?? '' );
		$avocat = ag_closer_est_avocat( $p );
		$etat   = '';
		if ( function_exists( 'ag_site_kind' ) ) {
			$k    = ag_site_kind( $site );
			$etat = is_array( $k ) ? (string) ( $k[0] ?? '' ) : (string) $k;
		}

		if ( ! function_exists( 'ag_ia_ready' ) || ! ag_ia_ready() ) {
			return ag_closer_gabarit( $p, $etape );
		}

		/* L'ANGLE : on ne propose pas la meme chose a tout le monde.
		   - Un pro du web n'a pas besoin qu'on lui fasse un site : pour lui,
		     l'angle est la SECURITE (on vend un audit/durcissement), sauf si
		     son propre site est visiblement a la traine.
		   - Sans vrai site → creation.
		   - Avec un vrai site → refonte/amelioration selon ce que le robot a vu.
		   Le constat factuel (ag-enrichir, deja filtre : jamais de faille
		   exploitable) guide ce que Hugo peut mentionner. */
		$pro_web = function_exists( 'ag_closer_est_pro_web' ) && ag_closer_est_pro_web( $p );
		if ( $pro_web )                { $angle = 'securite'; }
		elseif ( 'real' !== $etat && ! $site ) { $angle = 'creation'; }
		elseif ( 'real' !== $etat )    { $angle = 'creation'; }
		else                           { $angle = 'refonte'; }

		$consigne_angle = '';
		if ( 'securite' === $angle ) {
			$consigne_angle = "ANGLE — SECURITE (le destinataire est un professionnel du web ou du numerique) :\n"
				. "- Ne lui propose PAS de lui creer un site : c'est son metier, ce serait ridicule.\n"
				. "- Parle-lui d'egal a egal, en confrere technique. Propose un regard de SECURITE sur les sites\n"
				. "  qu'il gere pour ses propres clients (audit, durcissement), ou sur son infrastructure.\n"
				. "- INTERDIT : lister des failles precises ou exploitables. Tu peux evoquer un constat de SURFACE\n"
				. "  visible de tous (pas de certificat, en-tetes manquants) mais JAMAIS un mode operatoire.\n"
				. "- Si son propre site est visiblement a la traine, tu peux le mentionner avec tact, sans le vexer.\n";
		} elseif ( 'creation' === $angle ) {
			$consigne_angle = "ANGLE — CREATION : il n'a pas de vrai site. Propose de lui montrer a quoi ressemblerait le sien.\n";
		} else {
			$consigne_angle = "ANGLE — AMELIORATION : il a deja un site. Propose de l'ameliorer sur ce que le constat signale,\n"
				. "sans denigrer son travail actuel.\n";
		}

		$systeme = "Tu ecris un email de prospection B2B en francais, pour Alliance Groupe, une petite agence qui cree des sites et securise des sites pour des artisans, des independants et des professionnels.\n"
			. "REGLES ABSOLUES :\n"
			. "- Vouvoiement. Le tutoiement est interdit.\n"
			. "- Aucun chiffre invente : pas de « +320 % », pas de « nos clients gagnent X ». Si tu n'as pas la donnee, tu n'en parles pas.\n"
			. "- N'invente AUCUNE experience ni reference client : pas de « nous avons observe chez nos clients », "
			. "pas de « en travaillant avec vos confreres », pas de « beaucoup de nos clients ». Vous demarrez, tu ne "
			. "revendiques rien que tu ne puisses prouver. Parle au conditionnel et de facon generale (« beaucoup de "
			. "sites », « en general »), jamais d'un vecu client que la maison n'a pas.\n"
			. "- Aucune fausse urgence, aucune fausse familiarite (« comme convenu », « suite a notre echange »). Vous ne vous etes jamais parle.\n"
			. "- Pas de flatterie creuse, pas de « j'espere que vous allez bien ».\n"
			. "- Court : un email de prospection long n'est pas lu.\n"
			. "- Ecris comme un professionnel ecrit a un autre, pas comme un service marketing.\n"
			. $consigne_angle
			. ( $avocat ? "- Le destinataire est AVOCAT : ton confraternel, sobre, aucune sollicitation agressive, aucune promesse de resultat.\n" : '' )
			. "SORTIE : premiere ligne « OBJET: ... », puis une ligne vide, puis le corps en texte brut. Pas de HTML, pas de signature (elle est ajoutee ensuite).";

		$contexte = 'Entreprise : ' . $nom . "\n"
			. ( $metier ? 'Metier : ' . $metier . "\n" : '' )
			. ( $ville ? 'Ville : ' . $ville . "\n" : '' )
			. ( $site ? 'Site actuel : ' . $site . "\n" : "Site actuel : aucun site trouve.\n" )
			. ( 'real' === $etat
				? "Constat : il a un vrai site.\n"
				: ( $site ? "Constat : ce qu'il a n'est pas un vrai site (page de reseau social, annuaire, ou page vide).\n" : "Constat : rien en ligne.\n" ) )
			/* Constat factuel produit par la chaine de montage (ag-enrichir),
			   deja filtre par liste blanche : jamais une faille exploitable. */
			. apply_filters( 'ag_closer_contexte_extra', '', $p )
			. "\nIntention de CE message : " . $etape['intention'];

		$opts = array( 'max_tokens' => 700, 'temperature' => 0.75, 'timeout' => 45 );
		if ( function_exists( 'ag_ia_model' ) ) { $opts['model'] = ag_ia_model( 'fast' ); }

		$txt = ag_ia_call( $systeme, $contexte, $opts );
		if ( is_wp_error( $txt ) || '' === trim( (string) $txt ) ) {
			return ag_closer_gabarit( $p, $etape );
		}

		$txt   = trim( (string) $txt );
		$objet = '';
		if ( preg_match( '/\AOBJET\s*:\s*(.+)\R/u', $txt, $m ) ) {
			$objet = trim( $m[1] );
			$txt   = trim( substr( $txt, strlen( $m[0] ) ) );
		}
		if ( '' === $objet ) { $objet = $nom ? ( 'Votre visibilite en ligne — ' . $nom ) : 'Votre visibilite en ligne'; }
		return array( 'objet' => $objet, 'corps' => $txt );
	}
}

if ( ! function_exists( 'ag_closer_gabarit' ) ) {
	/** Repli sans IA : sobre, honnete, sans un seul chiffre. */
	function ag_closer_gabarit( $p, $etape ) {
		$nom   = (string) ( $p['name'] ?? '' );
		$ville = (string) ( $p['city'] ?? '' );
		$corps = "Bonjour,\n\n"
			. 'Je suis tombe sur ' . ( $nom ? $nom : 'votre entreprise' ) . ( $ville ? ' a ' . $ville : '' ) . ", et je n'ai pas trouve de site a jour.\n\n"
			. "Nous construisons des sites pour des artisans et des independants. Si vous voulez voir a quoi ressemblerait le votre, je peux vous en montrer une maquette, sans engagement.\n\n"
			. "Si cela ne vous interesse pas, le lien en bas de ce message me le fait savoir definitivement.";
		return array( 'objet' => ( $nom ? 'Votre site — ' . $nom : 'Votre site' ), 'corps' => $corps );
	}
}

/* ── 5. L'envoi ──────────────────────────────────────────────────────── */

if ( ! function_exists( 'ag_closer_envoyer' ) ) {
	/**
	 * Envoie UN message a UN prospect et met a jour sa fiche (par reference).
	 *
	 * @return bool
	 */
	function ag_closer_envoyer( &$p, $etape ) {
		$to = sanitize_email( (string) ( $p['email'] ?? '' ) );
		if ( ! is_email( $to ) ) { return false; }

		$msg  = ag_closer_redige( $p, $etape );
		$stop = ag_closer_stop_url( (string) ( $p['id'] ?? '' ) );

		$corps_html = '<p style="font-family:Arial,sans-serif;font-size:15px;line-height:1.65;color:#e8e6e0;white-space:pre-line;">'
			. esc_html( $msg['corps'] ) . '</p>';
		if ( 0 === (int) ( $p['closer_step'] ?? 0 ) && function_exists( 'ag_email_button' ) ) {
			$corps_html .= ag_email_button( 'Voir une maquette de mon site', home_url( '/refais-mon-site' ) );
		}
		/* La mention et le lien d'opposition ne sont PAS optionnels : c'est ce
		   qui separe une prospection d'un spam, en droit comme en fait. */
		$corps_html .= '<p style="font-family:Arial,sans-serif;font-size:12px;line-height:1.6;color:#8a8a94;'
			. 'border-top:1px solid rgba(255,255,255,.12);padding-top:14px;margin-top:22px;">'
			. 'Ce message vous est adresse par un systeme automatise d\'Alliance Groupe, parce que votre entreprise figure dans un annuaire public. '
			. 'Vos coordonnees ne sont ni revendues ni cedees.<br>'
			. '<a href="' . esc_url( $stop ) . '" style="color:#D4B45C;">Ne plus jamais recevoir de message de notre part</a> — un seul clic, rien a ecrire.</p>';

		$sujet   = (string) $msg['objet'];
		$html    = function_exists( 'ag_email_wrap' ) ? ag_email_wrap( $sujet, $corps_html ) : $corps_html;
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		list( $from_mail, $from_nom ) = ag_closer_expediteur();
		if ( $from_mail ) {
			$headers[] = 'From: ' . $from_nom . ' <' . $from_mail . '>';
			$headers[] = 'Reply-To: ' . $from_mail;
		}

		if ( ! wp_mail( $to, $sujet, $html, $headers ) ) { return false; }

		$p['closer_step']   = (int) ( $p['closer_step'] ?? 0 ) + 1;
		$p['closer_last']   = time();
		$p['last_contact']  = gmdate( 'Y-m-d H:i' );
		$p['contact_count'] = (int) ( $p['contact_count'] ?? 0 ) + 1;
		if ( in_array( (string) ( $p['status'] ?? '' ), array( '', 'nouveau', 'sans_reponse' ), true ) ) {
			$p['status'] = 'contacte';
		}
		ag_closer_journal( array(
			'ts'      => time(),
			'nom'     => (string) ( $p['name'] ?? '' ),
			'email'   => $to,
			'etape'   => (int) $p['closer_step'],
			'objet'   => $sujet,
			'extrait' => function_exists( 'mb_substr' ) ? mb_substr( (string) $msg['corps'], 0, 180 ) : substr( (string) $msg['corps'], 0, 180 ),
		) );
		return true;
	}
}

if ( ! function_exists( 'ag_closer_journal' ) ) {
	/** Journal des envois — 200 derniers. Sans journal, pas de preuve. */
	function ag_closer_journal( $ligne = null ) {
		$j = (array) get_option( 'ag_closer_journal', array() );
		if ( null === $ligne ) { return $j; }
		array_unshift( $j, $ligne );
		update_option( 'ag_closer_journal', array_slice( $j, 0, 200 ), false );
		return $j;
	}
}

/* ── 6. Le tour ──────────────────────────────────────────────────────── */

if ( ! function_exists( 'ag_closer_tour' ) ) {
	/**
	 * Un passage : au plus ce que le plafond du jour autorise encore.
	 *
	 * @return array array( 'envoyes' => int, 'raison' => string )
	 */
	function ag_closer_tour() {
		if ( ! ag_closer_on() ) { return array( 'envoyes' => 0, 'raison' => 'agent eteint' ); }

		$jour = (array) get_option( 'ag_closer_jour', array() );
		$auj  = gmdate( 'Ymd' );
		if ( ( $jour['d'] ?? '' ) !== $auj ) { $jour = array( 'd' => $auj, 'n' => 0 ); }
		$reste = ag_closer_cap() - (int) ( $jour['n'] ?? 0 );
		if ( $reste <= 0 ) { return array( 'envoyes' => 0, 'raison' => 'plafond du jour atteint' ); }

		$list    = (array) get_option( 'ag_prospects', array() );
		$envoyes = 0;
		foreach ( $list as $i => $p ) {
			if ( $envoyes >= $reste ) { break; }
			$etape = ag_closer_eligible( $p );
			if ( ! $etape ) { continue; }
			$copie = $p;
			if ( ag_closer_envoyer( $copie, $etape ) ) {
				$list[ $i ] = $copie;
				$envoyes++;
			}
		}
		if ( $envoyes ) {
			update_option( 'ag_prospects', $list, false );
			$jour['n'] = (int) ( $jour['n'] ?? 0 ) + $envoyes;
			update_option( 'ag_closer_jour', $jour, false );
			if ( function_exists( 'ag_activity_log' ) ) {
				ag_activity_log( '🤝 AG Closer : ' . $envoyes . ' message(s) envoye(s).' );
			}
		}
		return array( 'envoyes' => $envoyes, 'raison' => $envoyes ? 'ok' : 'aucun prospect eligible' );
	}
}

/* Toutes les heures. Le plafond quotidien fait le reste : l'agent avance au
   pas, il ne vide pas le CRM en une nuit. */
add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'ag_closer_cron' ) ) {
		wp_schedule_event( time() + 300, 'hourly', 'ag_closer_cron' );
	}
} );
add_action( 'ag_closer_cron', 'ag_closer_tour' );
