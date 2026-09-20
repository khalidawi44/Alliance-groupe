<?php
/**
 * ag-commercial-admin.php — les deux ecrans de pilotage de l'agent commercial.
 *
 * Le moteur est ailleurs (ag-closer.php pour le demarchage, ag-signature.php
 * pour le contrat et sa signature). Ici : les manettes, et surtout ce qu'il
 * faut savoir avant de les toucher.
 *
 * Un systeme qui ecrit a des inconnus et qui engage une entreprise ne doit
 * pas se piloter a l'aveugle : chaque interrupteur porte, a cote de lui, ce
 * qu'il declenche et ce qu'il coute.
 *
 * @package Alliance_Groupe
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ── Menus ───────────────────────────────────────────────────────────── */

add_action( 'admin_menu', function () {
	add_submenu_page(
		'ag-prospects', 'Agent commercial', '🤝 Agent commercial',
		'manage_options', 'ag-closer', 'ag_closer_ecran'
	);
	add_submenu_page(
		'ag-hub', 'Contrats signes', '✍️ Contrats signes',
		'manage_options', 'ag-contrats', 'ag_contrats_ecran'
	);
}, 30 );

/* ── Controle avant vol : le domaine est-il en etat d'envoyer ? ──────── */

if ( ! function_exists( 'ag_closer_diagnostic' ) ) {
	/**
	 * Verifie l'authentification email du domaine d'envoi.
	 *
	 * Pourquoi c'est dans le chemin critique : un demarchage a froid depuis un
	 * domaine sans DKIM part en indesirables, et surtout il abime la
	 * reputation de TOUT le domaine. Ce ne sont pas les emails de prospection
	 * qu'on perd alors — ce sont les devis, les factures et les cles de
	 * licence, qui cessent d'arriver sans que personne ne comprenne pourquoi.
	 * Le mal est lent a faire et tres lent a defaire.
	 *
	 * @param string $domaine Le domaine reellement utilise a l'envoi.
	 * @return array array( 'spf'=>..., 'dmarc'=>..., 'dkim'=>..., 'ok'=>bool, 'graves'=>string[] )
	 */
	function ag_closer_diagnostic( $domaine ) {
		$domaine = trim( strtolower( (string) $domaine ) );
		$out = array( 'domaine' => $domaine, 'spf' => '', 'dmarc' => '', 'dkim' => '', 'graves' => array(), 'tiedes' => array() );
		if ( '' === $domaine || ! function_exists( 'dns_get_record' ) ) {
			$out['graves'][] = 'Impossible d\'interroger le DNS depuis ce serveur : verification a faire a la main.';
			$out['ok'] = false;
			return $out;
		}

		/* SPF — qui a le droit d'envoyer en votre nom. */
		foreach ( (array) @dns_get_record( $domaine, DNS_TXT ) as $r ) {
			$t = (string) ( $r['txt'] ?? '' );
			if ( 0 === stripos( $t, 'v=spf1' ) ) { $out['spf'] = $t; break; }
		}
		if ( '' === $out['spf'] ) {
			$out['graves'][] = 'Aucun SPF : n\'importe qui peut envoyer en votre nom, et vos envois seront refuses.';
		} elseif ( false !== strpos( $out['spf'], '~all' ) ) {
			$out['tiedes'][] = 'SPF en « ~all » (tolerant). Acceptable, mais « -all » protege mieux sur un sous-domaine dedie au demarchage.';
		}

		/* DMARC — ce qu'on demande aux boites de faire des messages douteux. */
		foreach ( (array) @dns_get_record( '_dmarc.' . $domaine, DNS_TXT ) as $r ) {
			$t = (string) ( $r['txt'] ?? '' );
			if ( 0 === stripos( $t, 'v=DMARC1' ) ) { $out['dmarc'] = $t; break; }
		}
		if ( '' === $out['dmarc'] ) {
			$out['graves'][] = 'Aucun DMARC : rien n\'indique aux boites quoi faire d\'un message usurpe.';
		} else {
			if ( false !== stripos( $out['dmarc'], 'p=none' ) ) {
				$out['tiedes'][] = 'DMARC en « p=none » : vous observez, vous ne protegez pas encore.';
			}
			if ( false === stripos( $out['dmarc'], 'rua=' ) ) {
				$out['tiedes'][] = 'DMARC sans « rua= » : aucun rapport ne vous revient, donc vous ne verrez jamais un probleme arriver.';
			}
		}

		/* DKIM — la signature. C'est elle qui manque le plus souvent, et c'est
		   elle qui pese le plus lourd sur un envoi a froid. */
		$selecteurs = array( 'default', 'hostingermail1', 'hostingermail2', 'hostingermail-a', 'hostingermail-b',
			'dkim', 'mail', 'selector1', 'selector2', 'k1', 's1', 's2', 'smtp', 'google', 'mandrill' );
		foreach ( $selecteurs as $s ) {
			$rr = (array) @dns_get_record( $s . '._domainkey.' . $domaine, DNS_TXT );
			if ( ! empty( $rr ) ) { $out['dkim'] = $s; break; }
		}
		if ( '' === $out['dkim'] ) {
			$out['graves'][] = 'Aucun DKIM trouve : vos messages ne sont signes par rien. '
				. 'C\'est le premier critere qui envoie un demarchage a froid en indesirables.';
		}

		$out['ok'] = empty( $out['graves'] );
		return $out;
	}
}

/* ── Ecran 1 : le demarchage ─────────────────────────────────────────── */

if ( ! function_exists( 'ag_closer_ecran' ) ) {
	function ag_closer_ecran() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }

		$msg = '';
		if ( isset( $_POST['ag_closer_save'] ) && check_admin_referer( 'ag_closer' ) ) {
			/* Le domaine reellement utilise a l'envoi : celui de l'adresse
			   dediee si elle est renseignee, sinon celui du site. */
			$from_test = sanitize_email( wp_unslash( $_POST['from_mail'] ?? '' ) );
			$dom_test  = $from_test ? substr( strrchr( $from_test, '@' ), 1 ) : wp_parse_url( home_url(), PHP_URL_HOST );
			$diag_test = ag_closer_diagnostic( (string) $dom_test );

			/* On n'arme pas l'agent sur un domaine qui n'est pas en etat, sauf
			   decision explicite. Ce n'est pas de la prudence de principe :
			   c'est la delivrabilite des factures qui est en jeu. */
			$veut = empty( $_POST['on'] ) ? 0 : 1;
			if ( $veut && ! $diag_test['ok'] && empty( $_POST['malgre_tout'] ) ) {
				$veut = 0;
				$msg  = 'Agent NON allume : le domaine d\'envoi n\'est pas en etat (voir le controle ci-dessous). '
					. 'Corrigez le DNS, ou cochez « j\'allume quand meme » si vous assumez le risque.';
			}
			update_option( 'ag_closer_on', $veut, false );
			update_option( 'ag_closer_cap_jour', max( 1, (int) ( $_POST['cap'] ?? 20 ) ), false );
			update_option( 'ag_closer_from_mail', sanitize_email( wp_unslash( $_POST['from_mail'] ?? '' ) ), false );
			update_option( 'ag_closer_from_nom', sanitize_text_field( wp_unslash( $_POST['from_nom'] ?? '' ) ), false );
			if ( '' === $msg ) { $msg = 'Reglages enregistres.'; }
		}
		if ( isset( $_POST['ag_closer_tour'] ) && check_admin_referer( 'ag_closer' ) ) {
			$r   = ag_closer_tour();
			$msg = 'Tour effectue : ' . (int) $r['envoyes'] . ' message(s) — ' . esc_html( (string) $r['raison'] ) . '.';
		}

		/* Apercu : on fait ecrire l'agent pour un VRAI prospect, et on n'envoie
		   rien. Laisser une IA ecrire en votre nom a des inconnus sans avoir
		   jamais lu une seule de ses phrases, c'est signer un chèque en blanc. */
		$apercu = null;
		if ( isset( $_POST['ag_closer_apercu'] ) && check_admin_referer( 'ag_closer' ) ) {
			foreach ( (array) get_option( 'ag_prospects', array() ) as $p_ap ) {
				$et_ap = ag_closer_eligible( $p_ap );
				if ( ! $et_ap ) { continue; }
				$apercu = ag_closer_redige( $p_ap, $et_ap );
				$apercu['pour']  = (string) ( $p_ap['name'] ?? '' );
				$apercu['email'] = (string) ( $p_ap['email'] ?? '' );
				$apercu['etape'] = (int) ( $p_ap['closer_step'] ?? 0 ) + 1;
				break;
			}
			if ( null === $apercu ) { $msg = 'Aucun prospect eligible : rien a montrer.'; }
		}

		$on   = ag_closer_on();
		$jour = (array) get_option( 'ag_closer_jour', array() );
		$nb   = ( ( $jour['d'] ?? '' ) === gmdate( 'Ymd' ) ) ? (int) ( $jour['n'] ?? 0 ) : 0;
		list( $from_mail, $from_nom ) = ag_closer_expediteur();

		/* Combien de prospects la sequence pourrait-elle toucher ? */
		$eligibles = 0; $sans_email = 0;
		foreach ( (array) get_option( 'ag_prospects', array() ) as $p ) {
			if ( ag_closer_eligible( $p ) ) { $eligibles++; }
			elseif ( ! is_email( (string) ( $p['email'] ?? '' ) ) ) { $sans_email++; }
		}
		?>
		<div class="wrap">
			<h1>🤝 Agent commercial</h1>
			<?php if ( $msg ) : ?><div class="notice notice-success"><p><?php echo esc_html( $msg ); ?></p></div><?php endif; ?>

			<div class="notice <?php echo $on ? 'notice-warning' : 'notice-info'; ?>">
				<p><strong><?php echo $on ? 'L\'agent est ALLUME.' : 'L\'agent est eteint.'; ?></strong>
				<?php echo $on
					? 'Il ecrit a de vraies personnes, en votre nom, sans que vous relisiez.'
					: 'Rien ne part. Vous pouvez tout regler avant d\'allumer.'; ?></p>
			</div>

			<?php if ( ! $from_mail ) : ?>
				<div class="notice notice-error"><p>
					<strong>Aucune adresse d'envoi dediee.</strong> Les messages partiront de l'adresse par defaut du site.
					Une vague de plaintes sur du demarchage peut alors faire tomber la delivrabilite de
					<strong>tous</strong> vos emails — devis, factures et cles de licence comprises.
					Un sous-domaine dedie (par exemple <code>contact@pro.alliancegroupe-inc.com</code>), avec son propre
					SPF/DKIM/DMARC, isole ce risque.
				</p></div>
			<?php endif; ?>

			<?php
			$dom  = $from_mail ? substr( strrchr( $from_mail, '@' ), 1 ) : wp_parse_url( home_url(), PHP_URL_HOST );
			$diag = ag_closer_diagnostic( (string) $dom );
			?>
			<h2>Controle du domaine d'envoi — <code><?php echo esc_html( (string) $dom ); ?></code></h2>
			<table class="widefat" style="max-width:860px;margin-bottom:14px">
				<tr><td style="width:22%"><strong>SPF</strong></td>
					<td><?php echo $diag['spf'] ? '<code>' . esc_html( $diag['spf'] ) . '</code>' : '<span style="color:#b32d2e">absent</span>'; ?></td></tr>
				<tr><td><strong>DKIM</strong></td>
					<td><?php echo $diag['dkim']
						? 'signe (selecteur <code>' . esc_html( $diag['dkim'] ) . '</code>)'
						: '<span style="color:#b32d2e">absent</span>'; ?></td></tr>
				<tr><td><strong>DMARC</strong></td>
					<td><?php echo $diag['dmarc'] ? '<code>' . esc_html( $diag['dmarc'] ) . '</code>' : '<span style="color:#b32d2e">absent</span>'; ?></td></tr>
			</table>
			<?php if ( ! empty( $diag['graves'] ) ) : ?>
				<div class="notice notice-error inline"><p><strong>Le domaine n'est pas en etat d'envoyer du demarchage :</strong></p>
					<ul style="list-style:disc;margin-left:20px">
						<?php foreach ( $diag['graves'] as $g ) : ?><li><?php echo esc_html( $g ); ?></li><?php endforeach; ?>
					</ul>
					<p>Tant que ce n'est pas corrige, l'agent ne s'allume pas — sauf decision explicite ci-dessous.
					Ce qui se perd n'est pas le demarchage : c'est la delivrabilite de vos devis et de vos factures.</p>
				</div>
			<?php endif; ?>
			<?php if ( ! empty( $diag['tiedes'] ) ) : ?>
				<div class="notice notice-warning inline"><p><strong>Ameliorable :</strong></p>
					<ul style="list-style:disc;margin-left:20px">
						<?php foreach ( $diag['tiedes'] as $t ) : ?><li><?php echo esc_html( $t ); ?></li><?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<table class="widefat" style="max-width:720px;margin-bottom:22px">
				<tr><td>Prospects que la sequence peut toucher maintenant</td><td><strong><?php echo (int) $eligibles; ?></strong></td></tr>
				<tr><td>Prospects sans adresse email (hors de portee)</td><td><?php echo (int) $sans_email; ?></td></tr>
				<tr><td>Messages envoyes aujourd'hui</td><td><?php echo (int) $nb; ?> / <?php echo (int) ag_closer_cap(); ?></td></tr>
			</table>

			<form method="post">
				<?php wp_nonce_field( 'ag_closer' ); ?>
				<table class="form-table">
					<tr><th scope="row">Agent</th><td>
						<label><input type="checkbox" name="on" value="1" <?php checked( $on ); ?>> Allume</label>
						<p class="description">Eteint, rien ne part. Le reglage survit a un deploiement.</p>
						<?php if ( ! $diag['ok'] ) : ?>
							<p style="margin-top:10px">
								<label style="color:#b32d2e">
									<input type="checkbox" name="malgre_tout" value="1">
									J'allume quand meme, en connaissant le risque pour la delivrabilite du domaine
								</label>
							</p>
						<?php endif; ?>
					</td></tr>
					<tr><th scope="row">Plafond par jour</th><td>
						<input type="number" name="cap" min="1" max="200" value="<?php echo (int) ag_closer_cap(); ?>">
						<p class="description">Au-dela, ce n'est plus de la prospection. 20 est un rythme tenable.</p>
					</td></tr>
					<tr><th scope="row">Adresse d'envoi</th><td>
						<input type="email" name="from_mail" class="regular-text" value="<?php echo esc_attr( $from_mail ); ?>" placeholder="contact@pro.alliancegroupe-inc.com">
						<p class="description">Fortement recommande : un sous-domaine dedie, jamais le domaine principal.</p>
					</td></tr>
					<tr><th scope="row">Nom affiche</th><td>
						<input type="text" name="from_nom" class="regular-text" value="<?php echo esc_attr( $from_nom ); ?>">
					</td></tr>
				</table>
				<p>
					<button class="button button-primary" name="ag_closer_save" value="1">Enregistrer</button>
					<button class="button" name="ag_closer_apercu" value="1">Voir ce que l'agent ecrirait</button>
					<button class="button" name="ag_closer_tour" value="1"
						onclick="return confirm('Envoyer maintenant les messages dus, dans la limite du plafond du jour ?')">
						Faire un tour maintenant</button>
				</p>
				<p class="description">
					« Voir ce que l'agent ecrirait » redige pour un vrai prospect et <strong>n'envoie rien</strong>.
					Lisez-le avant d'allumer : c'est votre nom qui sera au bas de ces messages.
				</p>
			</form>

			<?php if ( $apercu ) : ?>
				<h2>Apercu — rien n'a ete envoye</h2>
				<div style="max-width:760px;background:#fff;border:1px solid #c3c4c7;border-left:4px solid #D4B45C;padding:18px 22px">
					<p style="margin:0 0 12px;color:#666;font-size:12px">
						Destinataire : <strong><?php echo esc_html( $apercu['pour'] ); ?></strong>
						&lt;<?php echo esc_html( $apercu['email'] ); ?>&gt; · message <?php echo (int) $apercu['etape']; ?> sur 4
					</p>
					<p style="margin:0 0 14px"><strong>Objet :</strong> <?php echo esc_html( (string) $apercu['objet'] ); ?></p>
					<div style="white-space:pre-line;font:15px/1.6 Georgia,serif;border-top:1px solid #eee;padding-top:14px">
						<?php echo esc_html( (string) $apercu['corps'] ); ?>
					</div>
					<p style="margin:16px 0 0;font-size:12px;color:#666;border-top:1px solid #eee;padding-top:12px">
						S'y ajoutent automatiquement : la mention « message automatise » et le lien d'opposition en un clic.
					</p>
				</div>
				<p class="description" style="max-width:760px">
					Chaque message est reecrit pour chaque prospect — celui-ci n'est pas un gabarit.
					Si le ton ne vous va pas, dites-le moi : les consignes de redaction sont modifiables.
				</p>
			<?php endif; ?>

			<h2>La sequence</h2>
			<table class="widefat" style="max-width:860px">
				<tr><th>#</th><th>Delai</th><th>Intention</th></tr>
				<?php foreach ( ag_closer_sequence() as $i => $e ) : ?>
					<tr>
						<td><?php echo (int) ( $i + 1 ); ?></td>
						<td><?php echo 0 === (int) $e['delai'] ? 'tout de suite' : '+' . (int) $e['delai'] . ' j'; ?></td>
						<td><?php echo esc_html( (string) $e['intention'] ); ?></td>
					</tr>
				<?php endforeach; ?>
			</table>
			<p class="description" style="max-width:860px">
				Apres le quatrieme message, l'agent lache definitivement. Un prospect qui repond sort de la sequence
				immediatement. Chaque message porte un lien d'opposition en un clic, et dit qu'il est automatise.
				Les avocats ne recoivent que des emails, jamais de SMS ni d'appel.
			</p>

			<h2>Journal des envois</h2>
			<table class="widefat striped">
				<tr><th>Quand</th><th>A qui</th><th>Etape</th><th>Objet</th><th>Debut du message</th></tr>
				<?php $j = ag_closer_journal(); if ( empty( $j ) ) : ?>
					<tr><td colspan="5">Aucun envoi pour l'instant.</td></tr>
				<?php else : foreach ( array_slice( $j, 0, 60 ) as $l ) : ?>
					<tr>
						<td><?php echo esc_html( date_i18n( 'd/m H:i', (int) ( $l['ts'] ?? 0 ) ) ); ?></td>
						<td><?php echo esc_html( (string) ( $l['nom'] ?? '' ) ); ?><br>
							<small><?php echo esc_html( (string) ( $l['email'] ?? '' ) ); ?></small></td>
						<td><?php echo (int) ( $l['etape'] ?? 0 ); ?>/4</td>
						<td><?php echo esc_html( (string) ( $l['objet'] ?? '' ) ); ?></td>
						<td><small><?php echo esc_html( (string) ( $l['extrait'] ?? '' ) ); ?>…</small></td>
					</tr>
				<?php endforeach; endif; ?>
			</table>
		</div>
		<?php
	}
}

/* ── Ecran 2 : les contrats et leur signature ────────────────────────── */

if ( ! function_exists( 'ag_contrats_ecran' ) ) {
	function ag_contrats_ecran() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }

		$msg = '';
		if ( isset( $_POST['ag_sign_save'] ) && check_admin_referer( 'ag_sign' ) ) {
			update_option( 'ag_sign_contrat_relu', empty( $_POST['relu'] ) ? 0 : 1, false );
			update_option( 'ag_sign_auto_contresigne', empty( $_POST['auto'] ) ? 0 : 1, false );
			$msg = 'Reglages enregistres.';
		}
		if ( isset( $_POST['ag_contresigne'] ) && check_admin_referer( 'ag_sign' ) ) {
			$id   = sanitize_text_field( wp_unslash( $_POST['ag_contresigne'] ) );
			$list = ag_sign_all();
			foreach ( $list as $i => $s ) {
				if ( ( $s['id'] ?? '' ) !== $id ) { continue; }
				$list[ $i ]['statut']          = 'contresigne';
				$list[ $i ]['contresigne_le']  = time();
				$list[ $i ]['contresigne_par'] = wp_get_current_user()->display_name;
				$msg = 'Contrat ' . $id . ' contresigne.';
				break;
			}
			ag_sign_save( $list );
		}

		$relu = ag_sign_pret();
		$auto = ag_sign_contresigne_auto();
		?>
		<div class="wrap">
			<h1>✍️ Contrats signes</h1>
			<?php if ( $msg ) : ?><div class="notice notice-success"><p><?php echo esc_html( $msg ); ?></p></div><?php endif; ?>

			<?php if ( ! $relu ) : ?>
				<div class="notice notice-error"><p>
					<strong>Aucun contrat ne peut partir.</strong> Le modele
					(<code>templates/page-contrat-client.php</code>) porte lui-meme la mention
					« modele a faire valider par un avocat ». Tant que cette case n'est pas cochee, le systeme refuse
					d'envoyer quoi que ce soit a signer — envoyer un document que personne n'a relu se paie au premier litige.
				</p></div>
			<?php endif; ?>

			<form method="post">
				<?php wp_nonce_field( 'ag_sign' ); ?>
				<table class="form-table">
					<tr><th scope="row">Modele de contrat</th><td>
						<label><input type="checkbox" name="relu" value="1" <?php checked( $relu ); ?>>
							Je declare que le modele a ete relu par un avocat</label>
						<p class="description">Verrou volontaire. Personne d'autre que vous ne peut faire cette declaration.</p>
					</td></tr>
					<tr><th scope="row">Signature d'Alliance Groupe</th><td>
						<label><input type="checkbox" name="auto" value="1" <?php checked( $auto ); ?>>
							<strong>Contresignature automatique</strong> — le systeme engage Alliance Groupe sans intervention</label>
						<p class="description">
							C'est votre entreprise, et c'est votre mandat a donner : vous pouvez confier ce pouvoir a un
							systeme que vous controlez. Ce que cela veut dire concretement : un contrat signe par un client
							a 3 h du matin est <strong>conclu</strong> a 3 h du matin, sans que vous l'ayez lu. Vous etes
							engage sur le prix, le delai et le perimetre qui y figurent.<br>
							Decoche, vous gardez la main : le client signe, et le contrat n'est conclu qu'apres votre clic.
						</p>
					</td></tr>
				</table>
				<p><button class="button button-primary" name="ag_sign_save" value="1">Enregistrer</button></p>
			</form>

			<h2>Les contrats</h2>
			<table class="widefat striped">
				<tr><th>Reference</th><th>Client</th><th>Objet</th><th>Montant</th><th>Etat</th><th>Preuve</th><th></th></tr>
				<?php
				$list = array_reverse( ag_sign_all() );
				if ( empty( $list ) ) : ?>
					<tr><td colspan="7">Aucun contrat pour l'instant.</td></tr>
				<?php else : foreach ( $list as $s ) :
					$st = (string) ( $s['statut'] ?? '' );
					$pr = (array) ( $s['preuve'] ?? array() );
					?>
					<tr>
						<td><code><?php echo esc_html( (string) ( $s['id'] ?? '' ) ); ?></code><br>
							<small><?php echo esc_html( date_i18n( 'd/m/Y', (int) ( $s['created'] ?? 0 ) ) ); ?></small></td>
						<td><?php echo esc_html( (string) ( $s['client_entreprise'] ?: ( $s['client_nom'] ?? '' ) ) ); ?><br>
							<small><?php echo esc_html( (string) ( $s['client_email'] ?? '' ) ); ?></small></td>
						<td><?php echo esc_html( (string) ( $s['objet'] ?? '' ) ); ?></td>
						<td><?php echo esc_html( (string) ( $s['montant'] ?? '' ) ); ?></td>
						<td><?php
							$libelles = array(
								'envoye'      => '📤 Envoye, pas encore signe',
								'signe'       => '✍️ Signe par le client',
								'contresigne' => '✅ Conclu',
								'annule'      => '🚫 Annule',
							);
							echo esc_html( $libelles[ $st ] ?? $st );
						?></td>
						<td><?php if ( ! empty( $pr['signe_le'] ) ) : ?>
							<small>
								Par <strong><?php echo esc_html( (string) ( $pr['nom_saisi'] ?? '' ) ); ?></strong><br>
								le <?php echo esc_html( date_i18n( 'd/m/Y a H:i', (int) $pr['signe_le'] ) ); ?><br>
								IP <?php echo esc_html( (string) ( $pr['ip'] ?? '' ) ); ?><br>
								email verifie par code<br>
								<code style="font-size:10px"><?php echo esc_html( substr( (string) ( $s['empreinte'] ?? '' ), 0, 32 ) ); ?>…</code>
							</small>
						<?php else : ?><small>—</small><?php endif; ?></td>
						<td><?php if ( 'signe' === $st ) : ?>
							<form method="post" style="margin:0">
								<?php wp_nonce_field( 'ag_sign' ); ?>
								<button class="button button-primary" name="ag_contresigne"
									value="<?php echo esc_attr( (string) ( $s['id'] ?? '' ) ); ?>">Contresigner</button>
							</form>
						<?php endif; ?></td>
					</tr>
				<?php endforeach; endif; ?>
			</table>
			<p class="description" style="max-width:820px">
				La preuve conservee pour chaque signature : le nom saisi par le signataire, la date et l'heure, son adresse IP,
				son navigateur, le fait que son adresse email a ete verifiee par un code a usage unique, et l'empreinte
				SHA-256 du contrat tel qu'il lui a ete presente. C'est ce qui permet de montrer, plus tard, qui a signe quoi —
				et que le document n'a pas bouge depuis.
			</p>
		</div>
		<?php
	}
}
