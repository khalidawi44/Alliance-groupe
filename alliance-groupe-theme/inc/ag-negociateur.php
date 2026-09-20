<?php
/**
 * ENZO — NEGOCIATION (bureau de Nantes)
 * ---------------------------------------------------------------------------
 * Le maillon qui manquait entre « ca m'interesse » et un contrat signe.
 *
 * Hugo ouvre la porte, Alessia lit la reponse. Quand cette reponse n'est ni un
 * oui ni un non mais une OBJECTION — « c'est cher », « je reflechis », « est-ce
 * que ca comprend... » — quelqu'un doit repondre. C'est Enzo.
 *
 * Ce qu'il peut faire :
 *  · repondre a l'objection avec ce qui est VRAI du pack concerne ;
 *  · rappeler le prix ferme, sans jamais le baisser ;
 *  · offrir UN geste, pris dans une liste que Fabrice a ecrite d'avance ;
 *  · conclure et passer la main a Camille (juridique) quand le client dit oui.
 *
 * Ce qu'il ne peut PAS faire, et qui est verifie APRES redaction, en PHP, sans
 * faire confiance a l'IA :
 *  · promettre un resultat (meme liste que le juriste) ;
 *  · ecrire un montant inferieur au plancher — ce qui interdit du meme coup
 *    d'ecrire une mensualite, donc de se tromper en calculant devant un client ;
 *  · prononcer le mot remise, reduction, rabais ;
 *  · envoyer un contrat : il n'en a pas le droit, c'est le metier de Camille.
 *
 * Un message qui echoue a l'un de ces controles n'est PAS envoye : il part en
 * alerte chez Fabrice. Mieux vaut un prospect qui attend qu'une phrase qu'on
 * devra tenir.
 *
 * @package alliance-groupe-theme
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! defined( 'AG_NEGO_VER' ) ) { define( 'AG_NEGO_VER', '1.0.0' ); }

/* ── 1. Les limites, fixees par la maison ────────────────────────────── */

if ( ! function_exists( 'ag_nego_on' ) ) {
	/** Eteint par defaut. Rien ne part tant que ce n'est pas arme a la main. */
	function ag_nego_on() { return (bool) get_option( 'ag_nego_on', 0 ); }
}

if ( ! function_exists( 'ag_nego_tours_max' ) ) {
	/**
	 * Au-dela, Enzo se tait et previent Fabrice. Une negociation qui s'eternise
	 * n'est plus une negociation : c'est du harcelement poli.
	 */
	function ag_nego_tours_max() { return max( 1, (int) get_option( 'ag_nego_tours_max', 3 ) ); }
}

if ( ! function_exists( 'ag_nego_plancher' ) ) {
	/** Aucun montant ecrit ne peut descendre sous ce plancher, en euros. */
	function ag_nego_plancher() { return max( 0, (int) get_option( 'ag_nego_plancher', 490 ) ); }
}

if ( ! function_exists( 'ag_nego_gestes' ) ) {
	/**
	 * Les seuls gestes autorises. Ce sont des choses que la maison sait
	 * FAIRE — pas des baisses de prix. Un geste qu'on ne peut pas tenir coute
	 * plus cher que la vente qu'il fait gagner.
	 *
	 * @return string[]
	 */
	function ag_nego_gestes() {
		$brut = (string) get_option( 'ag_nego_gestes', '' );
		if ( '' === trim( $brut ) ) {
			return array(
				'Paiement en quatre fois sans frais',
				'Reprise de vos textes et de vos photos existants, sans supplement',
				'Un point d\'accompagnement de trente minutes apres la mise en ligne',
			);
		}
		$l = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $brut ) ) );
		return array_values( $l );
	}
}

/* ── 2. Le controle : ce qui ne partira pas ──────────────────────────── */

if ( ! function_exists( 'ag_nego_montants' ) ) {
	/**
	 * Tous les montants en euros trouves dans un texte, en entiers.
	 * « 1 490 € », « 890€ », « 122,50 euros » -> 1490, 890, 122.
	 *
	 * @return int[]
	 */
	function ag_nego_montants( $texte ) {
		$t = str_replace( array( "\xc2\xa0", "\xe2\x80\xaf" ), ' ', (string) $texte ); // espaces insecables
		$out = array();
		if ( preg_match_all( '/(\d[\d  ]*)(?:[,.]\d+)?\s*(?:€|euros?)/ui', $t, $m ) ) {
			foreach ( $m[1] as $n ) {
				$out[] = (int) preg_replace( '/[^\d]/', '', $n );
			}
		}
		return $out;
	}
}

if ( ! function_exists( 'ag_nego_controle' ) ) {
	/**
	 * Relit ce qu'Enzo vient d'ecrire. En PHP, pas dans la consigne donnee a
	 * l'IA : une consigne se contourne, un test ne se contourne pas.
	 *
	 * @return string[] les refus. Vide = le message peut partir.
	 */
	function ag_nego_controle( $corps, $objet = '' ) {
		$refus = array();
		$tout  = mb_strtolower( $objet . ' ' . $corps );

		if ( function_exists( 'ag_promesses_interdites' ) ) {
			foreach ( ag_promesses_interdites() as $motif => $genre ) {
				if ( false !== mb_strpos( $tout, $motif ) ) {
					$refus[] = 'Promesse de resultat (« ' . $motif . ' » — ' . $genre . ').';
				}
			}
		}

		foreach ( array( 'remise', 'reduction', 'réduction', 'rabais', 'promo', 'solde' ) as $mot ) {
			if ( false !== mb_strpos( $tout, $mot ) ) {
				$refus[] = 'Baisse de prix annoncee (« ' . $mot . ' ») : le prix est ferme, les gestes sont en nature.';
			}
		}

		$plancher = ag_nego_plancher();
		foreach ( ag_nego_montants( $corps ) as $montant ) {
			if ( $montant > 0 && $montant < $plancher ) {
				$refus[] = 'Montant ecrit sous le plancher (' . $montant . ' € < ' . $plancher . ' €). '
					. 'Rappel : on n\'ecrit pas non plus le detail des mensualites.';
			}
		}

		if ( mb_strlen( trim( (string) $corps ) ) < 40 ) {
			$refus[] = 'Message vide ou trop court pour etre envoye.';
		}
		if ( mb_strlen( (string) $corps ) > 2200 ) {
			$refus[] = 'Message trop long : personne ne lit une page de traitement d\'objection.';
		}

		return $refus;
	}
}

/* ── 3. La redaction ─────────────────────────────────────────────────── */

if ( ! function_exists( 'ag_nego_redige' ) ) {
	/**
	 * Fait ecrire la reponse a l'objection.
	 *
	 * @param array  $p     le prospect
	 * @param string $texte ce que le prospect vient d'ecrire
	 * @param int    $tour  numero de l'echange (1 = premiere objection)
	 * @return array objet, corps, accord
	 */
	function ag_nego_redige( $p, $texte, $tour = 1 ) {
		$packs = function_exists( 'ag_sites_express_packs' ) ? (array) ag_sites_express_packs() : array();
		$cat   = '';
		foreach ( $packs as $cle => $pk ) {
			$cat .= '- ' . $pk['nom'] . ' (' . $pk['prix'] . ', ' . $pk['delai'] . ') : '
				. implode( ', ', array_slice( (array) ( $pk['cles'] ?? $pk['feats'] ?? array() ), 0, 4 ) ) . "\n";
		}
		$gestes = "- " . implode( "\n- ", ag_nego_gestes() );
		$nom    = trim( (string) ( $p['name'] ?? '' ) );
		$ville  = trim( (string) ( $p['city'] ?? $p['ville'] ?? '' ) );

		$system = "Tu es le negociateur d'Alliance Groupe, agence de creation de sites internet "
			. "installee a Naples et a Nantes. Tu reponds a une objection d'un prospect professionnel, "
			. "par courriel, en francais, au vouvoiement.\n\n"
			. "CE QUE TU VENDS (rien d'autre n'existe) :\n" . $cat . "\n"
			. "LES SEULS GESTES QUE TU PEUX OFFRIR :\n" . $gestes . "\n\n"
			. "REGLES ABSOLUES :\n"
			. "1. Le prix des packs est FERME. Tu ne le baisses jamais, tu n'ecris jamais les mots "
			. "remise, reduction, rabais, promo.\n"
			. "2. Tu n'ecris JAMAIS de montant inferieur a " . ag_nego_plancher() . " euros. "
			. "Si tu parles du paiement en plusieurs fois, tu dis « en quatre fois sans frais » "
			. "SANS jamais calculer ni ecrire le montant d'une mensualite.\n"
			. "3. Tu ne promets AUCUN resultat : ni position sur Google, ni nombre de clients, "
			. "ni chiffre d'affaires. Tu n'inventes aucun chiffre, aucune reference, aucun temoignage.\n"
			. "4. Tu n'inventes aucune prestation absente de la liste ci-dessus.\n"
			. "5. Tu reponds VRAIMENT a l'objection posee. Tu ne repetes pas l'argumentaire.\n"
			. "6. Au maximum un geste par message, et seulement s'il repond a l'objection.\n"
			. "7. Huit lignes maximum. Pas de flatterie, pas de fausse familiarite, "
			. "pas de « je me permets de revenir vers vous ».\n"
			. "8. Tu ne dis jamais que tu es un humain, et tu ne signes d'aucun prenom.\n\n"
			. "REPONDS EN JSON STRICT, sans texte autour :\n"
			. '{"objet":"...","corps":"...","accord":true|false}' . "\n"
			. "\"accord\" vaut true UNIQUEMENT si le prospect vient d'accepter clairement d'acheter.";

		$user = "Entreprise : " . ( $nom ?: 'non precise' ) . ( $ville ? ' (' . $ville . ')' : '' ) . "\n"
			. "Echange numero " . (int) $tour . " sur " . ag_nego_tours_max() . ".\n"
			. "Ce que le prospect vient d'ecrire :\n\"\"\"\n" . mb_substr( (string) $texte, 0, 1500 ) . "\n\"\"\"";

		$rep = function_exists( 'ag_ia_call' )
			? ag_ia_call( $system, $user, array(
				'model'       => function_exists( 'ag_ia_model' ) ? ag_ia_model( 'smart' ) : '',
				'max_tokens'  => 900,
				'temperature' => 0.5,
				'timeout'     => 60,
			) )
			: new WP_Error( 'ag_nego_ia', 'IA indisponible' );

		if ( is_wp_error( $rep ) ) {
			return array( 'objet' => '', 'corps' => '', 'accord' => false, 'erreur' => $rep->get_error_message() );
		}

		$brut = is_array( $rep ) ? (string) ( $rep['texte'] ?? '' ) : (string) $rep;
		$brut = trim( preg_replace( '/^```(?:json)?|```$/m', '', $brut ) );
		$j    = json_decode( $brut, true );

		if ( ! is_array( $j ) ) {
			return array( 'objet' => '', 'corps' => '', 'accord' => false,
				'erreur' => 'Reponse illisible de l\'IA (JSON attendu).' );
		}

		return array(
			'objet'  => trim( (string) ( $j['objet'] ?? '' ) ),
			'corps'  => trim( (string) ( $j['corps'] ?? '' ) ),
			'accord' => ! empty( $j['accord'] ),
		);
	}
}

/* ── 4. L'envoi ──────────────────────────────────────────────────────── */

if ( ! function_exists( 'ag_nego_envoyer' ) ) {
	/**
	 * Redige, controle, envoie. Met a jour le prospect par reference.
	 *
	 * @return array ok, raison, accord
	 */
	function ag_nego_envoyer( &$p, $texte ) {
		$to = sanitize_email( (string) ( $p['email'] ?? '' ) );
		if ( ! is_email( $to ) ) {
			return array( 'ok' => false, 'raison' => 'pas d\'adresse', 'accord' => false );
		}
		if ( ! empty( $p['closer_stop'] ) ) {
			return array( 'ok' => false, 'raison' => 'opposition enregistree', 'accord' => false );
		}

		$tour = (int) ( $p['nego_tour'] ?? 0 ) + 1;
		if ( $tour > ag_nego_tours_max() ) {
			return array( 'ok' => false, 'raison' => 'nombre d\'echanges atteint', 'accord' => false );
		}

		$msg = ag_nego_redige( $p, $texte, $tour );
		if ( ! empty( $msg['erreur'] ) ) {
			return array( 'ok' => false, 'raison' => $msg['erreur'], 'accord' => false );
		}

		$refus = ag_nego_controle( $msg['corps'], $msg['objet'] );
		if ( $refus ) {
			ag_nego_journal( array(
				'ts' => time(), 'nom' => (string) ( $p['name'] ?? '' ), 'email' => $to,
				'tour' => $tour, 'objet' => (string) $msg['objet'],
				'bloque' => implode( ' / ', $refus ),
				'extrait' => mb_substr( (string) $msg['corps'], 0, 200 ),
			) );
			if ( function_exists( 'ag_push' ) ) {
				ag_push( '⛔ Enzo bloque un message',
					(string) ( $p['name'] ?? $to ) . "\n" . implode( "\n", $refus )
					. "\nRien n'a ete envoye : a reprendre a la main." );
			}
			return array( 'ok' => false, 'raison' => implode( ' / ', $refus ), 'accord' => false );
		}

		$stop = function_exists( 'ag_closer_stop_url' ) ? ag_closer_stop_url( (string) ( $p['id'] ?? '' ) ) : '';
		$html = '<p style="font-family:Arial,sans-serif;font-size:15px;line-height:1.65;color:#e8e6e0;white-space:pre-line;">'
			. esc_html( $msg['corps'] ) . '</p>';
		/* Meme pied que le demarchage : la mention et l'opposition ne sont pas
		   facultatives parce qu'on est en train de negocier. */
		$html .= '<p style="font-family:Arial,sans-serif;font-size:12px;line-height:1.6;color:#8a8a94;'
			. 'border-top:1px solid rgba(255,255,255,.12);padding-top:14px;margin-top:22px;">'
			. 'Ce message vous est adresse par un systeme automatise d\'Alliance Groupe.'
			. ( $stop ? '<br><a href="' . esc_url( $stop ) . '" style="color:#D4B45C;">Ne plus recevoir de message de notre part</a>' : '' )
			. '</p>';

		$sujet   = $msg['objet'] ?: 'Votre question';
		$corps   = function_exists( 'ag_email_wrap' ) ? ag_email_wrap( $sujet, $html ) : $html;
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		if ( function_exists( 'ag_closer_expediteur' ) ) {
			list( $from_mail, $from_nom ) = ag_closer_expediteur();
			if ( $from_mail ) {
				$headers[] = 'From: ' . $from_nom . ' <' . $from_mail . '>';
				$headers[] = 'Reply-To: ' . $from_mail;
			}
		}

		if ( ! wp_mail( $to, $sujet, $corps, $headers ) ) {
			return array( 'ok' => false, 'raison' => 'l\'envoi a echoue', 'accord' => false );
		}

		$p['nego_tour']    = $tour;
		$p['nego_last']    = time();
		$p['last_contact'] = gmdate( 'Y-m-d H:i' );

		ag_nego_journal( array(
			'ts' => time(), 'nom' => (string) ( $p['name'] ?? '' ), 'email' => $to,
			'tour' => $tour, 'objet' => $sujet, 'bloque' => '',
			'extrait' => mb_substr( (string) $msg['corps'], 0, 200 ),
		) );

		return array( 'ok' => true, 'raison' => '', 'accord' => (bool) $msg['accord'] );
	}
}

if ( ! function_exists( 'ag_nego_journal' ) ) {
	/** Journal des negociations — 200 dernieres, bloquees comprises. */
	function ag_nego_journal( $ligne = null ) {
		$j = (array) get_option( 'ag_nego_journal', array() );
		if ( null === $ligne ) { return $j; }
		array_unshift( $j, $ligne );
		update_option( 'ag_nego_journal', array_slice( $j, 0, 200 ), false );
		return $j;
	}
}

/* ── 5. L'ecran ──────────────────────────────────────────────────────── */

add_action( 'admin_menu', function () {
	add_submenu_page(
		'ag-prospects', 'Negociation', '🤝 Enzo — Negociation',
		'manage_options', 'ag-negociateur', 'ag_nego_ecran'
	);
}, 31 );

if ( ! function_exists( 'ag_nego_ecran' ) ) {
	function ag_nego_ecran() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		$msg = '';

		if ( isset( $_POST['ag_nego_save'] ) && check_admin_referer( 'ag_nego' ) ) {
			update_option( 'ag_nego_on', isset( $_POST['on'] ) ? 1 : 0, false );
			update_option( 'ag_nego_tours_max', max( 1, absint( $_POST['tours'] ?? 3 ) ), false );
			update_option( 'ag_nego_plancher', absint( $_POST['plancher'] ?? 490 ), false );
			update_option( 'ag_nego_gestes', sanitize_textarea_field( wp_unslash( $_POST['gestes'] ?? '' ) ), false );
			$msg = 'Reglages enregistres.';
		}

		$essai = null;
		if ( isset( $_POST['ag_nego_essai'] ) && check_admin_referer( 'ag_nego' ) ) {
			$objection = sanitize_textarea_field( wp_unslash( $_POST['objection'] ?? '' ) );
			if ( '' === trim( $objection ) ) {
				$msg = 'Ecrivez d\'abord l\'objection a traiter.';
			} else {
				$essai = ag_nego_redige( array( 'name' => 'Entreprise d\'essai' ), $objection, 1 );
				$essai['refus'] = empty( $essai['erreur'] )
					? ag_nego_controle( (string) $essai['corps'], (string) $essai['objet'] )
					: array();
			}
		}
		?>
		<div class="wrap">
			<h1>🤝 Enzo — Negociation</h1>
			<p class="description" style="max-width:56em">
				Enzo repond aux objections : le prix, le doute, la question sur ce qui est compris.
				Il n'envoie jamais de contrat — quand le prospect dit oui, il passe la main a Camille.
			</p>

			<?php if ( $msg ) : ?><div class="notice notice-info"><p><?php echo esc_html( $msg ); ?></p></div><?php endif; ?>

			<form method="post">
				<?php wp_nonce_field( 'ag_nego' ); ?>
				<table class="form-table">
					<tr><th scope="row">Enzo est en poste</th><td>
						<label><input type="checkbox" name="on" value="1" <?php checked( ag_nego_on() ); ?>>
							Oui — repondre seul aux objections</label>
						<p class="description">Eteint, les objections vous remontent et vous repondez vous-meme.</p>
					</td></tr>
					<tr><th scope="row">Echanges maximum</th><td>
						<input type="number" name="tours" min="1" max="6" style="width:6em"
							value="<?php echo esc_attr( ag_nego_tours_max() ); ?>">
						<p class="description">Au-dela, il se tait et vous previent. Insister davantage ne vend rien.</p>
					</td></tr>
					<tr><th scope="row">Plancher de prix</th><td>
						<input type="number" name="plancher" min="0" step="10" style="width:8em"
							value="<?php echo esc_attr( ag_nego_plancher() ); ?>"> €
						<p class="description">Aucun montant inferieur ne peut etre ecrit. Cela lui interdit aussi
							d'ecrire une mensualite — donc de se tromper en calculant devant un client.</p>
					</td></tr>
					<tr><th scope="row">Gestes autorises</th><td>
						<textarea name="gestes" rows="4" class="large-text" placeholder="un par ligne"><?php
							echo esc_textarea( (string) get_option( 'ag_nego_gestes', implode( "\n", ag_nego_gestes() ) ) );
						?></textarea>
						<p class="description">Des choses que vous savez faire, jamais une baisse de prix.
							Un geste qu'on ne tient pas coute plus cher que la vente qu'il fait gagner.</p>
					</td></tr>
				</table>
				<p><button class="button button-primary" name="ag_nego_save" value="1">Enregistrer</button></p>

				<h2>L'essayer sans rien envoyer</h2>
				<p>
					<textarea name="objection" rows="3" class="large-text"
						placeholder="Ex : C'est trop cher pour moi, j'ai eu un devis a 300 euros ailleurs."></textarea>
				</p>
				<p><button class="button" name="ag_nego_essai" value="1">Voir ce qu'Enzo repondrait</button></p>
			</form>

			<?php if ( $essai ) : ?>
				<h2>Reponse d'essai — rien n'a ete envoye</h2>
				<?php if ( ! empty( $essai['erreur'] ) ) : ?>
					<div class="notice notice-error"><p><?php echo esc_html( $essai['erreur'] ); ?></p></div>
				<?php else : ?>
					<div style="background:#fff;border:1px solid #dcdcde;padding:16px 18px;max-width:56em">
						<p><strong>Objet :</strong> <?php echo esc_html( $essai['objet'] ); ?></p>
						<p style="white-space:pre-line;font:15px/1.7 Georgia,serif"><?php echo esc_html( $essai['corps'] ); ?></p>
						<p class="description">Accord detecte : <?php echo $essai['accord'] ? 'oui' : 'non'; ?></p>
					</div>
					<?php if ( ! empty( $essai['refus'] ) ) : ?>
						<div class="notice notice-error" style="max-width:56em"><p>
							<strong>Ce message serait BLOQUE :</strong><br>
							<?php echo esc_html( implode( ' / ', $essai['refus'] ) ); ?>
						</p></div>
					<?php else : ?>
						<p style="color:#1a7f37"><strong>Ce message passerait les controles.</strong></p>
					<?php endif; ?>
				<?php endif; ?>
			<?php endif; ?>

			<h2>Journal</h2>
			<table class="widefat striped" style="max-width:72em">
				<thead><tr><th>Quand</th><th>Prospect</th><th>Echange</th><th>Objet</th><th>Etat</th></tr></thead>
				<tbody>
				<?php $j = ag_nego_journal(); if ( ! $j ) : ?>
					<tr><td colspan="5">Rien encore.</td></tr>
				<?php else : foreach ( array_slice( $j, 0, 40 ) as $l ) : ?>
					<tr>
						<td><?php echo esc_html( date_i18n( 'd/m H:i', (int) ( $l['ts'] ?? 0 ) ) ); ?></td>
						<td><?php echo esc_html( (string) ( $l['nom'] ?? '' ) ); ?><br>
							<small><?php echo esc_html( (string) ( $l['email'] ?? '' ) ); ?></small></td>
						<td><?php echo (int) ( $l['tour'] ?? 0 ); ?></td>
						<td><?php echo esc_html( (string) ( $l['objet'] ?? '' ) ); ?></td>
						<td><?php echo empty( $l['bloque'] )
							? '<span style="color:#1a7f37">envoye</span>'
							: '<span style="color:#b32d2e">bloque — ' . esc_html( (string) $l['bloque'] ) . '</span>'; ?></td>
					</tr>
				<?php endforeach; endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
