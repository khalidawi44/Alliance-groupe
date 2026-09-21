<?php
/**
 * LA BOITE — Alessia releve le courrier (le maillon qui manquait)
 * ---------------------------------------------------------------------------
 * Sans ce fichier, le cabinet sait parler et pas ecouter.
 *
 * Hugo envoie. Le prospect repond. Sa reponse arrive dans la boite
 * `contact@`, puis dans la messagerie personnelle de Fabrice — et le site n'en
 * sait rien. Alessia attendait qu'on lui pousse les reponses sur une adresse
 * technique que personne n'alimentait. Resultat : ni qualification, ni
 * negociation, ni contrat. La chaine s'arretait la.
 *
 * Ce module va CHERCHER le courrier au lieu de l'attendre.
 *
 * Trois promesses tenues par le code, pas par la bonne volonte :
 *
 * 1. LA BOITE N'EST JAMAIS MODIFIEE. La connexion est ouverte en lecture
 *    seule (OP_READONLY). Aucun message n'est marque lu, deplace ou
 *    supprime — Fabrice retrouve sa boite exactement comme il l'a laissee.
 *    Pour ne pas traiter deux fois le meme message, on retient son
 *    identifiant ici, dans le site.
 *
 * 2. ON NE LIT QUE CE QUI NOUS REGARDE. Seuls les messages dont l'expediteur
 *    figure deja dans le fichier de prospects sont transmis a Alessia. Le
 *    courrier personnel, les factures, les impots : ignores, jamais stockes.
 *
 * 3. RIEN N'ECHOUE EN SILENCE. Si l'extension IMAP manque, si le mot de passe
 *    est refuse, si la connexion tombe, c'est ecrit en clair a l'ecran et
 *    pousse en alerte. Une chaine automatique qui se tait quand elle casse
 *    est pire qu'une chaine manuelle.
 *
 * @package alliance-groupe-theme
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! defined( 'AG_BOITE_VER' ) ) { define( 'AG_BOITE_VER', '1.0.0' ); }

/** Fenetre de lecture : au-dela, un message est considere comme traite ou perdu. */
if ( ! defined( 'AG_BOITE_JOURS' ) ) { define( 'AG_BOITE_JOURS', 3 ); }

/** Plafond par releve : une boucle qui s'emballe ne doit pas noyer le serveur. */
if ( ! defined( 'AG_BOITE_MAX' ) ) { define( 'AG_BOITE_MAX', 20 ); }

/* ── 1. Reglages ─────────────────────────────────────────────────────── */

if ( ! function_exists( 'ag_boite_on' ) ) {
	function ag_boite_on() { return (bool) get_option( 'ag_boite_on', 0 ); }
}

if ( ! function_exists( 'ag_boite_opt' ) ) {
	/**
	 * Par defaut, on reprend les identifiants de l'envoi : c'est la MEME boite.
	 * Faire ressaisir les memes informations, c'est inviter la faute de frappe.
	 */
	function ag_boite_opt( $cle, $defaut = '' ) {
		$v = trim( (string) get_option( 'ag_boite_' . $cle, '' ) );
		if ( '' !== $v ) { return $v; }
		if ( 'user' === $cle && function_exists( 'ag_smtp_opt' ) ) { return ag_smtp_opt( 'user' ); }
		return $defaut;
	}
}

if ( ! function_exists( 'ag_boite_pass' ) ) {
	/** Constante de wp-config prioritaire, puis reglage propre, puis celui de l'envoi. */
	function ag_boite_pass() {
		if ( defined( 'AG_BOITE_PASS' ) && '' !== (string) AG_BOITE_PASS ) { return (string) AG_BOITE_PASS; }
		$v = (string) get_option( 'ag_boite_pass', '' );
		if ( '' !== $v ) { return $v; }
		return function_exists( 'ag_smtp_pass' ) ? ag_smtp_pass() : '';
	}
}

if ( ! function_exists( 'ag_boite_dispo' ) ) {
	/**
	 * L'extension IMAP de PHP est-elle la ? Tous les hebergeurs ne l'activent
	 * pas, et on ne le saura qu'en le demandant a la machine.
	 *
	 * @return true|string true, ou la raison de l'indisponibilite.
	 */
	function ag_boite_dispo() {
		if ( ! function_exists( 'imap_open' ) ) {
			return 'L\'extension IMAP de PHP n\'est pas activee sur ce serveur. '
				. 'Demandez-la au support de l\'hebergeur (elle s\'appelle « imap »), '
				. 'ou faites suivre la boite vers un service qui appelle l\'adresse technique du site.';
		}
		return true;
	}
}

/* ── 2. Lire sans rien abimer ────────────────────────────────────────── */

if ( ! function_exists( 'ag_boite_corps' ) ) {
	/**
	 * Le texte utile d'un message : on prefere la version texte brut, et on
	 * coupe la citation du message precedent — sinon l'IA qualifie notre
	 * propre demarchage au lieu de la reponse du prospect.
	 */
	function ag_boite_corps( $flux, $num, $structure ) {
		$texte = '';

		if ( isset( $structure->parts ) && is_array( $structure->parts ) ) {
			foreach ( $structure->parts as $i => $partie ) {
				if ( 0 === (int) $partie->type ) { /* 0 = text */
					$sous = strtolower( (string) ( $partie->subtype ?? '' ) );
					$brut = imap_fetchbody( $flux, $num, (string) ( $i + 1 ) );
					$brut = ag_boite_decode( $brut, (int) ( $partie->encoding ?? 0 ) );
					if ( 'plain' === $sous ) { $texte = $brut; break; }
					if ( '' === $texte ) { $texte = wp_strip_all_tags( $brut ); }
				}
			}
		}
		if ( '' === $texte ) {
			$texte = ag_boite_decode( imap_body( $flux, $num ), (int) ( $structure->encoding ?? 0 ) );
			if ( false !== stripos( $texte, '<html' ) ) { $texte = wp_strip_all_tags( $texte ); }
		}

		return ag_boite_utile( $texte );
	}
}

if ( ! function_exists( 'ag_boite_utile' ) ) {
	/**
	 * Ne garder que ce que le prospect a REELLEMENT ecrit.
	 *
	 * Une reponse par courriel traine derriere elle le message d'origine. Si on
	 * transmet le tout, l'IA qualifie notre propre demarchage au lieu de la
	 * reponse — et elle y lit une intention d'achat qui n'existe pas. Cette
	 * fonction est isolee pour pouvoir etre eprouvee sans serveur de courrier.
	 */
	function ag_boite_utile( $texte ) {
		/* « Le 20/09/2026 a 21:00, Alliance Groupe a ecrit : » et tout ce qui suit. */
		$texte = preg_replace( '/\R\s*(Le |On |De\s*:).{0,160}?(a écrit|a ecrit|wrote)\s*:.*/su', '', (string) $texte );
		/* Les separateurs poses par Outlook et consorts. */
		$texte = preg_split( '/\R-{3,}\s*(Message d\'origine|Original Message|Forwarded message)/ui', $texte )[0];

		$garde = array();
		foreach ( (array) preg_split( '/\R/u', (string) $texte ) as $l ) {
			if ( preg_match( '/^\s*>/', $l ) ) { continue; }          /* citation */
			if ( preg_match( '/^\s*--\s*$/', $l ) ) { break; }        /* signature */
			$garde[] = $l;
		}

		return mb_substr( trim( implode( "\n", $garde ) ), 0, 4000 );
	}
}

if ( ! function_exists( 'ag_boite_decode' ) ) {
	function ag_boite_decode( $brut, $encodage ) {
		if ( 3 === (int) $encodage ) { $brut = base64_decode( $brut ); }
		elseif ( 4 === (int) $encodage ) { $brut = quoted_printable_decode( $brut ); }
		if ( ! mb_check_encoding( $brut, 'UTF-8' ) ) {
			$brut = mb_convert_encoding( $brut, 'UTF-8', 'ISO-8859-1, Windows-1252, UTF-8' );
		}
		return (string) $brut;
	}
}

if ( ! function_exists( 'ag_boite_vus' ) ) {
	/** Les identifiants deja traites — 500 derniers. C'est NOTRE memoire, pas un drapeau pose dans la boite. */
	function ag_boite_vus( $ajout = null ) {
		$v = (array) get_option( 'ag_boite_vus', array() );
		if ( null === $ajout ) { return $v; }
		array_unshift( $v, (string) $ajout );
		update_option( 'ag_boite_vus', array_slice( array_unique( $v ), 0, 500 ), false );
		return $v;
	}
}

if ( ! function_exists( 'ag_boite_relever' ) ) {
	/**
	 * Le cycle complet : connexion, lecture, transmission a Alessia.
	 *
	 * @return array lus, transmis, ignores, erreur
	 */
	function ag_boite_relever() {
		$bilan = array( 'lus' => 0, 'transmis' => 0, 'ignores' => 0, 'total' => null, 'erreur' => '' );

		$dispo = ag_boite_dispo();
		if ( true !== $dispo ) { $bilan['erreur'] = $dispo; return $bilan; }

		$user = ag_boite_opt( 'user' );
		$pass = ag_boite_pass();
		$host = ag_boite_opt( 'host', 'imap.hostinger.com' );
		$port = (int) ( ag_boite_opt( 'port', '993' ) ?: 993 );
		if ( '' === $user || '' === $pass ) {
			$bilan['erreur'] = 'Identifiant ou mot de passe manquant.';
			return $bilan;
		}

		$boite = '{' . $host . ':' . $port . '/imap/ssl}INBOX';

		/* OP_READONLY : la garantie, au niveau du protocole, qu'aucun message
		   ne sera marque lu ni deplace. Ce n'est pas une intention, c'est un
		   drapeau que le serveur fait respecter. */
		$flux = @imap_open( $boite, $user, $pass, OP_READONLY, 1 );
		if ( ! $flux ) {
			$err = imap_last_error();
			$bilan['erreur'] = 'Connexion refusee : ' . ( $err ?: 'raison inconnue' )
				. ' — le mot de passe de la boite est-il le bon ?';
			imap_errors(); /* on vide la pile pour ne pas polluer la suite */
			return $bilan;
		}

		/* Combien la boite contient-elle, en tout ? Sans ce chiffre, « 0 message
		   parcouru » veut dire deux choses opposees — la boite est vide, ou la
		   recherche a echoue — et on ne saurait pas laquelle. */
		$bilan['total'] = (int) @imap_num_msg( $flux );

		$depuis = gmdate( 'j M Y', time() - ( AG_BOITE_JOURS * DAY_IN_SECONDS ) );
		$nums   = @imap_search( $flux, 'SINCE "' . $depuis . '"', SE_UID );
		if ( ! $nums ) {
			$err = imap_last_error();
			imap_errors();
			if ( $err ) { $bilan['erreur'] = 'Recherche refusee par le serveur : ' . $err; }
			imap_close( $flux );
			update_option( 'ag_boite_derniere', time(), false );
			return $bilan;
		}

		$nums = array_slice( array_reverse( $nums ), 0, AG_BOITE_MAX );
		$vus  = ag_boite_vus();

		foreach ( $nums as $uid ) {
			$entete = @imap_headerinfo( $flux, imap_msgno( $flux, $uid ) );
			if ( ! $entete ) { continue; }
			$bilan['lus']++;

			$id = (string) ( $entete->message_id ?? ( 'uid-' . $uid ) );
			if ( in_array( $id, $vus, true ) ) { continue; }

			$exp = '';
			if ( ! empty( $entete->from[0]->mailbox ) && ! empty( $entete->from[0]->host ) ) {
				$exp = strtolower( $entete->from[0]->mailbox . '@' . $entete->from[0]->host );
			}
			if ( ! is_email( $exp ) ) { continue; }

			/* Seuls les prospects. Le reste du courrier ne nous regarde pas et
			   n'est ni lu en entier, ni stocke, ni envoye a une IA. */
			$connu = false;
			foreach ( (array) get_option( 'ag_prospects', array() ) as $p ) {
				if ( strtolower( (string) ( $p['email'] ?? '' ) ) === $exp ) { $connu = true; break; }
			}
			if ( ! $connu ) { $bilan['ignores']++; continue; }

			$msgno  = imap_msgno( $flux, $uid );
			$struct = @imap_fetchstructure( $flux, $msgno );
			$texte  = $struct ? ag_boite_corps( $flux, $msgno, $struct ) : '';
			if ( '' === trim( $texte ) ) { ag_boite_vus( $id ); continue; }

			$sujet = isset( $entete->subject )
				? (string) iconv_mime_decode( $entete->subject, 0, 'UTF-8' ) : '';

			if ( function_exists( 'ag_reponses_traiter' ) ) {
				$r = ag_reponses_traiter( $exp, $texte, $sujet );
				ag_boite_journal( array(
					'ts'    => time(),
					'de'    => $exp,
					'sujet' => $sujet,
					'suite' => is_array( $r )
						? trim( (string) ( $r['intention'] ?? '' )
							. ( ! empty( $r['nego'] ) ? ' · Enzo : ' . $r['nego'] : '' )
							. ( ! empty( $r['contrat'] ) ? ' · Contrat : ' . $r['contrat'] : '' ) )
						: 'non traite',
				) );
				$bilan['transmis']++;
			}
			ag_boite_vus( $id );
		}

		imap_close( $flux );
		update_option( 'ag_boite_derniere', time(), false );
		return $bilan;
	}
}

if ( ! function_exists( 'ag_boite_journal' ) ) {
	/** Journal des relevees — 200 dernieres. */
	function ag_boite_journal( $ligne = null ) {
		$j = (array) get_option( 'ag_boite_journal', array() );
		if ( null === $ligne ) { return $j; }
		array_unshift( $j, $ligne );
		update_option( 'ag_boite_journal', array_slice( $j, 0, 200 ), false );
		return $j;
	}
}

/* ── 3. Le rythme ────────────────────────────────────────────────────── */

add_filter( 'cron_schedules', function ( $s ) {
	if ( ! isset( $s['ag_dix_minutes'] ) ) {
		$s['ag_dix_minutes'] = array( 'interval' => 600, 'display' => 'Toutes les 10 minutes' );
	}
	return $s;
} );

add_action( 'init', function () {
	if ( ag_boite_on() && ! wp_next_scheduled( 'ag_boite_cron' ) ) {
		wp_schedule_event( time() + 120, 'ag_dix_minutes', 'ag_boite_cron' );
	}
	if ( ! ag_boite_on() && wp_next_scheduled( 'ag_boite_cron' ) ) {
		wp_clear_scheduled_hook( 'ag_boite_cron' );
	}
} );

add_action( 'ag_boite_cron', function () {
	if ( ! ag_boite_on() ) { return; }
	$b = ag_boite_relever();
	if ( '' !== $b['erreur'] ) {
		update_option( 'ag_boite_erreur', array( 'quand' => time(), 'message' => $b['erreur'] ), false );
		/* Une seule alerte par heure : previenir n'est pas harceler. */
		$dernier = (int) get_option( 'ag_boite_alerte_le', 0 );
		if ( function_exists( 'ag_push' ) && ( time() - $dernier ) > HOUR_IN_SECONDS ) {
			ag_push( '📪 La boite n\'est plus relevee', $b['erreur'] );
			update_option( 'ag_boite_alerte_le', time(), false );
		}
	} else {
		delete_option( 'ag_boite_erreur' );
	}
} );

/* ── 4. L'ecran ──────────────────────────────────────────────────────── */

add_action( 'admin_menu', function () {
	add_submenu_page(
		'ag-prospects', 'Releve de la boite', '📬 Releve de la boite',
		'manage_options', 'ag-boite', 'ag_boite_ecran'
	);
}, 32 );

if ( ! function_exists( 'ag_boite_ecran' ) ) {
	function ag_boite_ecran() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		$msg = '';

		if ( isset( $_POST['ag_boite_save'] ) && check_admin_referer( 'ag_boite' ) ) {
			update_option( 'ag_boite_on', isset( $_POST['on'] ) ? 1 : 0, false );
			update_option( 'ag_boite_host', sanitize_text_field( wp_unslash( $_POST['host'] ?? '' ) ), false );
			update_option( 'ag_boite_port', absint( $_POST['port'] ?? 993 ), false );
			update_option( 'ag_boite_user', sanitize_text_field( wp_unslash( $_POST['user'] ?? '' ) ), false );
			$mdp = (string) wp_unslash( $_POST['pass'] ?? '' );
			if ( '' !== $mdp ) { update_option( 'ag_boite_pass', $mdp, false ); }
			$msg = 'Reglages enregistres.';
		}

		$bilan = null;
		if ( isset( $_POST['ag_boite_now'] ) && check_admin_referer( 'ag_boite' ) ) {
			$bilan = ag_boite_relever();
		}

		$dispo   = ag_boite_dispo();
		$erreur  = (array) get_option( 'ag_boite_erreur', array() );
		$dernier = (int) get_option( 'ag_boite_derniere', 0 );
		$apwc    = defined( 'AG_BOITE_PASS' ) && '' !== (string) AG_BOITE_PASS;
		?>
		<div class="wrap">
			<h1>📬 Releve de la boite</h1>
			<p class="description" style="max-width:56em">
				Le site va chercher les reponses dans <strong><?php echo esc_html( ag_boite_opt( 'user', 'la boite' ) ); ?></strong>
				toutes les dix minutes et les passe a Alessia, qui qualifie et enchaine.
				Sans cela, le cabinet sait parler et pas ecouter.
			</p>

			<?php if ( $msg ) : ?><div class="notice notice-info"><p><?php echo esc_html( $msg ); ?></p></div><?php endif; ?>

			<?php if ( true !== $dispo ) : ?>
				<div class="notice notice-error"><p><strong>Impossible en l'etat.</strong><br>
					<?php echo esc_html( $dispo ); ?></p></div>
			<?php endif; ?>

			<?php if ( ! empty( $erreur['message'] ) ) : ?>
				<div class="notice notice-error"><p><strong>Derniere erreur</strong>
					(<?php echo esc_html( date_i18n( 'd/m/Y H:i', (int) $erreur['quand'] ) ); ?>) :<br>
					<code><?php echo esc_html( $erreur['message'] ); ?></code></p></div>
			<?php endif; ?>

			<?php if ( $bilan ) : ?>
				<div class="notice notice-<?php echo $bilan['erreur'] ? 'error' : 'success'; ?>"><p>
					<?php if ( $bilan['erreur'] ) : echo esc_html( $bilan['erreur'] ); else : ?>
						Boite ouverte : <strong><?php echo (int) $bilan['total']; ?></strong> message(s) au total,
						dont <?php echo (int) $bilan['lus']; ?> dans la fenetre de <?php echo (int) AG_BOITE_JOURS; ?> jours ·
						<strong><?php echo (int) $bilan['transmis']; ?></strong> transmis a Alessia ·
						<?php echo (int) $bilan['ignores']; ?> ignore(s) (expediteur inconnu du fichier).
						<?php if ( 0 === (int) $bilan['total'] ) : ?>
							<br><strong>La boite est vide.</strong> Une redirection qui ne garde pas de copie
							vide la boite a mesure : le courrier part chez vous et ne reste pas ici, donc
							le site n'a rien a lire. Dans hPanel, la redirection doit <em>conserver une copie</em>
							dans la boite — sinon le cabinet restera sourd.
						<?php endif; ?>
					<?php endif; ?>
				</p></div>
			<?php endif; ?>

			<p class="description">
				Derniere releve :
				<?php echo $dernier ? esc_html( date_i18n( 'd/m/Y H:i', $dernier ) ) : 'jamais'; ?>.
			</p>

			<form method="post">
				<?php wp_nonce_field( 'ag_boite' ); ?>
				<table class="form-table">
					<tr><th scope="row">Relever la boite</th><td>
						<label><input type="checkbox" name="on" value="1" <?php checked( ag_boite_on() ); ?>>
							Oui — toutes les dix minutes</label>
					</td></tr>
					<tr><th scope="row">Serveur</th><td>
						<input type="text" name="host" class="regular-text"
							value="<?php echo esc_attr( ag_boite_opt( 'host', 'imap.hostinger.com' ) ); ?>">
						<input type="number" name="port" style="width:7em;margin-left:8px"
							value="<?php echo esc_attr( ag_boite_opt( 'port', '993' ) ); ?>">
						<p class="description">IMAP en SSL. Chez Hostinger : <code>imap.hostinger.com</code>, port <code>993</code>.</p>
					</td></tr>
					<tr><th scope="row">Identifiant</th><td>
						<input type="text" name="user" class="regular-text"
							value="<?php echo esc_attr( (string) get_option( 'ag_boite_user', '' ) ); ?>"
							placeholder="<?php echo esc_attr( function_exists( 'ag_smtp_opt' ) ? ag_smtp_opt( 'user' ) : '' ); ?>">
						<p class="description">Laisse vide, on reprend celui de l'envoi : c'est la meme boite.</p>
					</td></tr>
					<tr><th scope="row">Mot de passe</th><td>
						<?php if ( $apwc ) : ?>
							<p class="description">Defini dans <code>wp-config.php</code> (<code>AG_BOITE_PASS</code>).</p>
						<?php else : ?>
							<input type="password" name="pass" class="regular-text" autocomplete="new-password"
								placeholder="laisser vide = celui de l'envoi">
						<?php endif; ?>
					</td></tr>
				</table>
				<p>
					<button class="button button-primary" name="ag_boite_save" value="1">Enregistrer</button>
					<button class="button" name="ag_boite_now" value="1">Relever maintenant</button>
				</p>
			</form>

			<h2>Ce que le site fait, et ne fait pas, dans votre boite</h2>
			<ul style="max-width:56em;list-style:disc;padding-left:22px">
				<li><strong>Il ne modifie rien.</strong> La connexion est ouverte en lecture seule :
					aucun message n'est marque lu, deplace ou supprime. C'est le serveur qui
					l'impose, pas nous qui le promettons.</li>
				<li><strong>Il ne lit que les prospects.</strong> Un message dont l'expediteur n'est pas
					dans le fichier est ignore : ni lu en entier, ni conserve, ni transmis a l'IA.</li>
				<li><strong>Il ne remonte pas le temps.</strong> Fenetre de <?php echo (int) AG_BOITE_JOURS; ?> jours,
					<?php echo (int) AG_BOITE_MAX; ?> messages par releve au maximum.</li>
				<li><strong>Il ne traite jamais deux fois le meme message</strong> : leur identifiant est retenu
					ici, dans le site — aucun marquage n'est pose dans la boite.</li>
			</ul>

			<h2>Journal</h2>
			<table class="widefat striped" style="max-width:72em">
				<thead><tr><th>Quand</th><th>De</th><th>Objet</th><th>Ce qui a suivi</th></tr></thead>
				<tbody>
				<?php $j = ag_boite_journal(); if ( ! $j ) : ?>
					<tr><td colspan="4">Rien encore.</td></tr>
				<?php else : foreach ( array_slice( $j, 0, 40 ) as $l ) : ?>
					<tr>
						<td><?php echo esc_html( date_i18n( 'd/m H:i', (int) ( $l['ts'] ?? 0 ) ) ); ?></td>
						<td><?php echo esc_html( (string) ( $l['de'] ?? '' ) ); ?></td>
						<td><?php echo esc_html( (string) ( $l['sujet'] ?? '' ) ); ?></td>
						<td><?php echo esc_html( (string) ( $l['suite'] ?? '' ) ); ?></td>
					</tr>
				<?php endforeach; endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
