<?php
/**
 * AG — Recrutement International (manuel assisté).
 *
 * Outil pour recruter des ambassadeurs dans les pays francophones, en
 * postant MANUELLEMENT dans les forums/groupes (toute automation = ban
 * forums + risque legal). Le module fournit :
 *  - 10 pays francophones avec canaux cures (forums, groupes FB, Reddit,
 *    Telegram) + rappel des regles propres a chaque canal.
 *  - Messages prets, adaptes par pays (ton + prime en devise locale).
 *  - Lien parrain auto-genere par recruteur, avec UTM par canal pour
 *    tracker les sources.
 *  - Marque "j'ai poste ici" pour eviter les doubles posts.
 *
 * Sous-menu : Ambassadeurs > Recrutement International.
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ---------------------------------------------------------------------------
 * Donnees pays : nom, drapeau, prime locale (devise), ton, canaux.
 * ------------------------------------------------------------------------- */
if ( ! function_exists( 'ag_ri_countries' ) ) {
	function ag_ri_countries() {
		return array(
			'FR' => array(
				'name'  => 'France', 'flag' => '🇫🇷', 'prime' => '25 €', 'tone' => 'Direct, jeune, terre-a-terre. Souligne "argent en plus", "complement de revenu".',
				'channels' => array(
					array( 'slug' => 'reddit-jobs-fr', 'name' => 'Reddit · r/jobs_FR', 'url' => 'https://www.reddit.com/r/jobs_FR/', 'rules' => 'Section opportunites/freelance. Pas de spam, post detaille, reponds aux commentaires.' ),
					array( 'slug' => 'reddit-forhire-fr', 'name' => 'Reddit · r/forhire_FR', 'url' => 'https://www.reddit.com/r/forhire_FR/', 'rules' => 'Tag [HIRING] pour proposer une opportunite.' ),
					array( 'slug' => 'doctissimo-argent', 'name' => 'Doctissimo · Argent & budget', 'url' => 'https://forum.doctissimo.fr/famille/argent-budget/liste_sujet-1.htm', 'rules' => 'Sujet dedie "complement de revenu". Pas de spam dans les autres rubriques.' ),
					array( 'slug' => 'jvc-1825', 'name' => 'JeuxVideo.com · 18-25', 'url' => 'https://www.jeuxvideo.com/forums/0-51-0-1-0-1-0-bons-plans.htm', 'rules' => 'Section Bons plans uniquement. Pose-toi, ton "khey", reponds aux questions.' ),
					array( 'slug' => 'tg-jobs-fr', 'name' => 'Telegram · groupes emploi FR', 'url' => 'https://t.me/s/jobs_fr', 'rules' => 'Rejoindre 2-3 groupes "Jobs France", poster 1 fois, ne pas spammer.' ),
					array( 'slug' => 'fb-petits-boulots', 'name' => 'Facebook · Petits boulots France', 'url' => 'https://www.facebook.com/search/groups?q=petits%20boulots%20france', 'rules' => 'Lire les regles du groupe, demander aux admins si autorise.' ),
				),
			),
			'BE' => array(
				'name' => 'Belgique', 'flag' => '🇧🇪', 'prime' => '25 €', 'tone' => 'Comme la France, ton chill.',
				'channels' => array(
					array( 'slug' => 'reddit-belgium', 'name' => 'Reddit · r/belgium', 'url' => 'https://www.reddit.com/r/belgium/', 'rules' => 'Tag [Question] ou [Job]. Pas de pub directe.' ),
					array( 'slug' => 'fb-emploi-be', 'name' => 'Facebook · Emploi Bruxelles/Liege/Charleroi', 'url' => 'https://www.facebook.com/search/groups?q=emploi%20belgique', 'rules' => 'Section opportunites/freelance.' ),
				),
			),
			'CH' => array(
				'name' => 'Suisse', 'flag' => '🇨🇭', 'prime' => '≈ 25 CHF', 'tone' => 'Pro, serieux, qualite.',
				'channels' => array(
					array( 'slug' => 'reddit-ch', 'name' => 'Reddit · r/Switzerland', 'url' => 'https://www.reddit.com/r/Switzerland/', 'rules' => 'Anglais ou francais. Tag [Question].' ),
					array( 'slug' => 'comm-fr-ch', 'name' => 'Comm-fr · Suisse romande', 'url' => 'https://www.comm-fr.org/', 'rules' => 'Section "petites annonces" uniquement.' ),
				),
			),
			'QC' => array(
				'name' => 'Québec', 'flag' => '🇨🇦', 'prime' => '≈ 38 CAD', 'tone' => 'Chaleureux, "salut" plutot que "bonjour".',
				'channels' => array(
					array( 'slug' => 'reddit-qc', 'name' => 'Reddit · r/Quebec', 'url' => 'https://www.reddit.com/r/Quebec/', 'rules' => 'Tag [Discussion]. Eviter la pub trop directe.' ),
					array( 'slug' => 'fb-emploi-mtl', 'name' => 'Facebook · Emploi Montréal/Québec', 'url' => 'https://www.facebook.com/search/groups?q=emploi%20montreal', 'rules' => 'Lire les regles, demander aux admins.' ),
					array( 'slug' => 'mpv', 'name' => 'Mamanpourlavie · Forum', 'url' => 'https://www.mamanpourlavie.com/forum/', 'rules' => 'Pour les meres cherchant un complement de revenu. Ton respectueux.' ),
				),
			),
			'MA' => array(
				'name' => 'Maroc', 'flag' => '🇲🇦', 'prime' => '≈ 270 MAD', 'tone' => 'Respectueux ("salam alaykum"), opportunite-business, soulignier le "en ligne, gratuit".',
				'channels' => array(
					array( 'slug' => 'bladi', 'name' => 'Bladi.net · Forum (Emploi/Business)', 'url' => 'https://www.bladi.net/forum/forums/economie-et-business.83/', 'rules' => 'Section economie/business uniquement.' ),
					array( 'slug' => 'reddit-ma', 'name' => 'Reddit · r/Morocco', 'url' => 'https://www.reddit.com/r/Morocco/', 'rules' => 'Tag [Discussion] ou [Help]. Souvent en anglais ou darija.' ),
					array( 'slug' => 'fb-rec-maroc', 'name' => 'Facebook · Recrutement Maroc (villes)', 'url' => 'https://www.facebook.com/search/groups?q=recrutement%20maroc', 'rules' => 'Casa/Rabat/Marrakech/Agadir/Fes/Tanger. Lire les regles.' ),
					array( 'slug' => 'tg-emploi-ma', 'name' => 'Telegram · canaux emploi Maroc', 'url' => 'https://t.me/s/emploi_maroc', 'rules' => 'Rejoindre les canaux publics, poster 1 fois.' ),
				),
			),
			'DZ' => array(
				'name' => 'Algérie', 'flag' => '🇩🇿', 'prime' => '≈ 3 700 DZD', 'tone' => 'Comme Maroc. Souligner "100% en ligne, sans avance d\'argent".',
				'channels' => array(
					array( 'slug' => 'reddit-dz', 'name' => 'Reddit · r/algeria', 'url' => 'https://www.reddit.com/r/algeria/', 'rules' => 'Tag [Discussion]. Souvent en anglais.' ),
					array( 'slug' => 'fb-emploi-dz', 'name' => 'Facebook · Travail Algérie (Alger/Oran/...)', 'url' => 'https://www.facebook.com/search/groups?q=emploi%20algerie', 'rules' => 'Lire les regles. Forte demande pour complement.' ),
					array( 'slug' => 'tg-emploi-dz', 'name' => 'Telegram · canaux emploi Algérie', 'url' => 'https://t.me/s/emploi_algerie', 'rules' => 'Canaux publics, 1 post.' ),
				),
			),
			'TN' => array(
				'name' => 'Tunisie', 'flag' => '🇹🇳', 'prime' => '≈ 80 TND', 'tone' => 'Decontracte, opportunite.',
				'channels' => array(
					array( 'slug' => 'reddit-tn', 'name' => 'Reddit · r/Tunisia', 'url' => 'https://www.reddit.com/r/Tunisia/', 'rules' => 'Tag [Discussion].' ),
					array( 'slug' => 'fb-jobs-tn', 'name' => 'Facebook · Jobs Tunisie', 'url' => 'https://www.facebook.com/search/groups?q=jobs%20tunisie', 'rules' => 'Tunis/Sfax/Sousse. Lire les regles.' ),
				),
			),
			'SN' => array(
				'name' => 'Sénégal', 'flag' => '🇸🇳', 'prime' => '≈ 16 500 FCFA', 'tone' => 'Chaleureux, communautaire ("bonjour", "ma cherie/mon ami").',
				'channels' => array(
					array( 'slug' => 'fb-emploi-sn', 'name' => 'Facebook · Emploi Sénégal Dakar/Thiès/Saint-Louis', 'url' => 'https://www.facebook.com/search/groups?q=emploi%20senegal', 'rules' => 'Lire les regles. Forte communaute.' ),
					array( 'slug' => 'reddit-sn', 'name' => 'Reddit · r/Senegal', 'url' => 'https://www.reddit.com/r/Senegal/', 'rules' => 'Tag [Discussion].' ),
				),
			),
			'CI' => array(
				'name' => 'Côte d\'Ivoire', 'flag' => '🇨🇮', 'prime' => '≈ 16 500 FCFA', 'tone' => 'Convivial, "akwaba".',
				'channels' => array(
					array( 'slug' => 'abidjan-net', 'name' => 'Abidjan.net · Forums emploi', 'url' => 'https://news.abidjan.net/', 'rules' => 'Section emploi/opportunites uniquement.' ),
					array( 'slug' => 'fb-emploi-ci', 'name' => 'Facebook · Emploi Abidjan/Bouaké', 'url' => 'https://www.facebook.com/search/groups?q=emploi%20abidjan', 'rules' => 'Lire les regles du groupe.' ),
				),
			),
			'CM' => array(
				'name' => 'Cameroun', 'flag' => '🇨🇲', 'prime' => '≈ 16 500 FCFA', 'tone' => 'Direct, opportunite, jeune.',
				'channels' => array(
					array( 'slug' => 'fb-emploi-cm', 'name' => 'Facebook · Emploi Yaoundé/Douala', 'url' => 'https://www.facebook.com/search/groups?q=emploi%20cameroun', 'rules' => 'Lire les regles, eviter les groupes "annonces uniquement".' ),
					array( 'slug' => '237actu', 'name' => '237actu · Commentaires emploi', 'url' => 'https://237actu.com/', 'rules' => 'Sous les articles emploi/business uniquement.' ),
				),
			),
		);
	}
}

/* ---------------------------------------------------------------------------
 * Templates de message par pays. {prenom_recruteur}, {parrain_link}, {prime}.
 * ------------------------------------------------------------------------- */
if ( ! function_exists( 'ag_ri_templates' ) ) {
	function ag_ri_templates( $code ) {
		$tpl = array(
			'FR' => array(
				"Salut, opportunite de complement de revenu serieuse : deviens ambassadeur Alliance Groupe (agence web). Tu touches *10% de commission a vie* sur chaque site vendu.\n\n📊 Exemple chiffre :\n• 1 site Pro vendu (890 €) = *89 € pour toi*\n• 1 vente/semaine = *4 624 €/an*\n• 6 ventes/semaine = *2 312 €/mois* = *27 768 €/an*\n\nSans plafond, paye sur PayPal, 100% en ligne. Lien : {parrain_link}",
				"Hello, je gagne un complement avec Alliance Groupe (agence web) — 10% de commission a vie sur chaque vente. 1 vente Pro = 89 €. 6/semaine = 27 768 €/an net. Aucun frais. Inscription : {parrain_link}",
			),
			'BE' => array(
				"Hello, Alliance Groupe (agence web FR) ouvre son reseau en Belgique. *10% de commission a vie* sur les ventes.\n📊 1 site Pro (890 €) = 89 € pour toi. 6 ventes/semaine = *2 312 €/mois* = *27 768 €/an*. Sans plafond. Inscription : {parrain_link}",
			),
			'CH' => array(
				"Bonjour, Alliance Groupe (agence web) recrute des ambassadeurs en Suisse romande. *10% de commission a vie* sur chaque vente.\n📊 1 vente Pro ≈ 87 CHF / 6 ventes par semaine ≈ *2 270 CHF/mois* (~27 240 CHF/an). 100% en ligne. Infos : {parrain_link}",
			),
			'QC' => array(
				"Salut, j'ai trouve un programme cool : Alliance Groupe (agence web francaise) cherche des ambassadeurs au Québec. *10% de commission a vie* sur les ventes.\n📊 1 vente Pro ≈ 134 CAD / 6 ventes par semaine ≈ *3 480 CAD/mois* (~41 700 CAD/an). Aucune avance. Inscription : {parrain_link}",
				"Yo, complement de revenu de la maison : ambassadeur Alliance Groupe, 10% a vie sur tes ventes. 1 site ≈ 134 CAD. Bien parti = *3 000+ CAD/mois*. Detail : {parrain_link}",
			),
			'MA' => array(
				"Salam alaykum. Alliance Groupe (agence web francaise) ouvre son reseau d'ambassadeurs au Maroc. *10% de commission a vie* sur les ventes.\n📊 1 site Pro (890 €) ≈ *970 MAD pour toi*\n• 1 vente/semaine ≈ *50 000 MAD/an*\n• 6 ventes/semaine ≈ *25 000 MAD/mois* (*300 000 MAD/an*)\n100% en ligne, gratuit, sans avance. Inscription : {parrain_link}",
				"Bonjour, opportunite serieuse : Alliance Groupe recrute des ambassadeurs au Maroc (Casa, Rabat, Marrakech, Agadir, Fes...). 10% a vie sur chaque vente — 1 site ≈ 970 MAD, 6 ventes/semaine ≈ 25 000 MAD/mois. Sans frais. {parrain_link}",
			),
			'DZ' => array(
				"Salam, Alliance Groupe (agence web FR) ouvre son reseau d'ambassadeurs en Algérie. *10% de commission a vie* sur les ventes.\n📊 1 site Pro (890 €) ≈ *13 350 DZD pour toi*\n• 1 vente/semaine ≈ *690 000 DZD/an*\n• 6 ventes/semaine ≈ *345 000 DZD/mois* (~4,1 M DZD/an)\nSans avance, 100% en ligne. Inscription : {parrain_link}",
				"Bonjour, programme serieux pour un complement de revenu en Algérie : 10% a vie sur chaque vente. 1 site ≈ 13 350 DZD. Bien parti = *plusieurs centaines de milliers de DZD/mois*. Detail : {parrain_link}",
			),
			'TN' => array(
				"Salam, Alliance Groupe (agence web) ouvre son reseau en Tunisie. *10% de commission a vie* sur les ventes.\n📊 1 site Pro (890 €) ≈ *290 TND pour toi*\n• 6 ventes/semaine ≈ *7 500 TND/mois* (~90 000 TND/an)\nGratuit, en ligne. Inscription : {parrain_link}",
			),
			'SN' => array(
				"Bonjour, Alliance Groupe (agence web francaise) recrute des ambassadeurs au Sénégal. *10% de commission a vie* sur les ventes.\n📊 1 site Pro (890 €) ≈ *58 400 FCFA pour toi*\n• 6 ventes/semaine ≈ *1,5 M FCFA/mois* (~18 M FCFA/an)\nC'est gratuit, 100% en ligne. Inscription : {parrain_link}",
			),
			'CI' => array(
				"Akwaba ! Alliance Groupe (agence web) ouvre son reseau d'ambassadeurs en Côte d'Ivoire. *10% de commission a vie* sur les ventes.\n📊 1 site Pro (890 €) ≈ *58 400 FCFA pour toi*\n• 6 ventes/semaine ≈ *1,5 M FCFA/mois* (~18 M FCFA/an)\n100% en ligne, sans avance. Inscription : {parrain_link}",
			),
			'CM' => array(
				"Bonjour, Alliance Groupe (agence web FR) recrute des ambassadeurs au Cameroun. *10% de commission a vie* sur les ventes.\n📊 1 site Pro (890 €) ≈ *58 400 FCFA pour toi*\n• 6 ventes/semaine ≈ *1,5 M FCFA/mois* (~18 M FCFA/an)\nGratuit, en ligne. Inscription : {parrain_link}",
			),
		);
		$set = $tpl[ $code ] ?? array( $tpl['FR'][0] );
		return $set;
	}
}

if ( ! function_exists( 'ag_ri_build_message' ) ) {
	function ag_ri_build_message( $code, $prenom, $parrain_link, $variation = 0 ) {
		$countries = ag_ri_countries();
		$prime = $countries[ $code ]['prime'] ?? '25 €';
		$set   = ag_ri_templates( $code );
		$idx   = ( (int) $variation ) % max( 1, count( $set ) );
		$msg   = $set[ $idx ];
		$msg   = str_replace( '{prenom_recruteur}', (string) $prenom, $msg );
		$msg   = str_replace( '{parrain_link}', (string) $parrain_link, $msg );
		$msg   = str_replace( '{prime}', $prime, $msg );
		return $msg;
	}
}

/* ---------------------------------------------------------------------------
 * Lien parrain avec UTM par canal.
 * ------------------------------------------------------------------------- */
if ( ! function_exists( 'ag_ri_parrain_link' ) ) {
	function ag_ri_parrain_link( $ref, $country_code, $channel_slug ) {
		$base = function_exists( 'home_url' ) ? home_url( '/ambassadeurs' ) : '/ambassadeurs';
		return add_query_arg( array_filter( array(
			'parrain'      => $ref ?: null,
			'utm_source'   => 'forum',
			'utm_medium'   => strtolower( $country_code ),
			'utm_campaign' => 'recrut-intl',
			'utm_term'     => $channel_slug,
		) ), $base );
	}
}

/* ---------------------------------------------------------------------------
 * "Marque comme poste" : par recruteur (current_user) et par canal.
 * ------------------------------------------------------------------------- */
if ( ! function_exists( 'ag_ri_posts_get' ) ) {
	function ag_ri_posts_get() {
		$d = get_option( 'ag_ri_posts', array() );
		return is_array( $d ) ? $d : array();
	}
}
if ( ! function_exists( 'ag_ri_post_key' ) ) {
	function ag_ri_post_key( $user_email, $country, $channel_slug ) {
		return strtolower( $user_email ) . '|' . strtoupper( $country ) . '|' . $channel_slug;
	}
}
if ( ! function_exists( 'ag_ri_is_posted' ) ) {
	function ag_ri_is_posted( $user_email, $country, $channel_slug ) {
		$posts = ag_ri_posts_get();
		$key   = ag_ri_post_key( $user_email, $country, $channel_slug );
		return isset( $posts[ $key ] );
	}
}

add_action( 'admin_post_ag_ri_toggle', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_ri' ) ) wp_die( 'no' );
	$country = strtoupper( sanitize_text_field( wp_unslash( $_POST['country'] ?? '' ) ) );
	$slug    = sanitize_text_field( wp_unslash( $_POST['channel'] ?? '' ) );
	$email   = strtolower( wp_get_current_user()->user_email );
	if ( '' === $country || '' === $slug || '' === $email ) wp_die( 'bad' );
	$posts = ag_ri_posts_get();
	$key   = ag_ri_post_key( $email, $country, $slug );
	if ( isset( $posts[ $key ] ) ) { unset( $posts[ $key ] ); }
	else { $posts[ $key ] = array( 'ts' => time(), 'date' => current_time( 'd/m/Y H:i' ) ); }
	update_option( 'ag_ri_posts', $posts, false );
	$back = isset( $_POST['_back'] ) ? esc_url_raw( wp_unslash( $_POST['_back'] ) ) : admin_url( 'admin.php?page=ag-recrut-intl' );
	wp_safe_redirect( $back ); exit;
} );

/* ---------------------------------------------------------------------------
 * Menu admin (sous-menu Ambassadeurs).
 * ------------------------------------------------------------------------- */
add_action( 'admin_menu', function () {
	add_submenu_page( 'ag-ambassadeurs', 'Recrutement International', '🌍 Recrutement International', 'manage_options', 'ag-recrut-intl', 'ag_ri_render' );
}, 20 );

if ( ! function_exists( 'ag_ri_render' ) ) {
	function ag_ri_render() {
		if ( ! current_user_can( 'manage_options' ) ) return;

		$countries = ag_ri_countries();
		$nonce     = wp_create_nonce( 'ag_ri' );
		$post      = admin_url( 'admin-post.php' );
		$back_url  = admin_url( 'admin.php?page=ag-recrut-intl' );

		// Recruteur cible : par défaut, l'utilisateur courant ; sinon, choisi via select.
		$current = wp_get_current_user();
		$amb_list = array();
		foreach ( (array) get_option( 'ag_ambassadeurs', array() ) as $a ) {
			if ( ! empty( $a['email'] ) ) $amb_list[ strtolower( $a['email'] ) ] = $a['name'] ?? $a['email'];
		}
		$picked = isset( $_GET['rec'] ) ? strtolower( sanitize_email( wp_unslash( $_GET['rec'] ) ) ) : strtolower( $current->user_email );
		$picked_name = $amb_list[ $picked ] ?? $current->display_name;
		$picked_ref  = function_exists( 'ag_ambassadeur_ref' ) ? ag_ambassadeur_ref( $picked ) : '';
		$active = isset( $_GET['c'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_GET['c'] ) ) ) : 'FR';
		if ( ! isset( $countries[ $active ] ) ) $active = 'FR';

		?>
		<div class="wrap">
			<h1>🌍 Recrutement International — Alliance Groupe</h1>

			<div style="background:#fcf9e8;border:1px solid #e6dca0;border-radius:8px;padding:12px 16px;margin:10px 0;max-width:980px;font-size:13px;">
				⚖️ <strong>Règle d'or :</strong> on poste <strong>à la main</strong>, dans les <strong>sections autorisées</strong> de chaque forum, en <strong>respectant leurs règles</strong>. Automatiser = ban garanti + risque pour le domaine. Cet outil fait juste gagner du temps.
			</div>

			<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" style="margin:10px 0;">
				<input type="hidden" name="page" value="ag-recrut-intl">
				<input type="hidden" name="c" value="<?php echo esc_attr( $active ); ?>">
				<label>Génère le pack pour : </label>
				<select name="rec" onchange="this.form.submit()">
					<option value="<?php echo esc_attr( $current->user_email ); ?>" <?php selected( $picked, strtolower( $current->user_email ) ); ?>>Moi (<?php echo esc_html( $current->display_name ); ?>)</option>
					<?php foreach ( $amb_list as $em => $nm ) : if ( strtolower( $em ) === strtolower( $current->user_email ) ) continue; ?>
						<option value="<?php echo esc_attr( $em ); ?>" <?php selected( $picked, $em ); ?>><?php echo esc_html( $nm . ' (' . $em . ')' ); ?></option>
					<?php endforeach; ?>
				</select>
				<?php if ( ! $picked_ref ) : ?><span style="color:#b32d2e;margin-left:8px;">⚠ Pas de code parrain pour ce recruteur — le lien sera sans `?parrain=`.</span><?php endif; ?>
			</form>

			<div style="margin:10px 0 14px;">
				<?php foreach ( $countries as $cc => $c ) :
					$url = add_query_arg( array( 'page' => 'ag-recrut-intl', 'c' => $cc, 'rec' => $picked ), admin_url( 'admin.php' ) );
					$on  = ( $cc === $active );
				?>
					<a href="<?php echo esc_url( $url ); ?>" style="display:inline-block;margin:0 6px 6px 0;padding:6px 14px;border-radius:100px;text-decoration:none;font-weight:700;font-size:.88rem;background:<?php echo $on ? '#1d2327' : '#f0f0f1'; ?>;color:<?php echo $on ? '#fff' : '#1d2327'; ?>;"><?php echo esc_html( $c['flag'] . ' ' . $c['name'] ); ?></a>
				<?php endforeach; ?>
			</div>

			<?php
			$c = $countries[ $active ];
			?>
			<div style="background:#fff;border:1px solid #ccd0d4;border-radius:10px;padding:18px 22px;max-width:980px;">
				<h2 style="margin-top:0;"><?php echo esc_html( $c['flag'] . ' ' . $c['name'] ); ?> — Prime affichée : <strong><?php echo esc_html( $c['prime'] ); ?></strong></h2>
				<p style="color:#50575e;font-size:.9rem;"><em>Ton conseillé : <?php echo esc_html( $c['tone'] ); ?></em></p>

				<?php $tpls = ag_ri_templates( $active ); ?>
				<h3 style="margin-top:18px;">📝 Messages prêts (clique « Copier »)</h3>
				<?php foreach ( $tpls as $i => $tpl_msg ) : ?>
					<?php $msg = ag_ri_build_message( $active, $picked_name, ag_ri_parrain_link( $picked_ref, $active, 'generic' ), $i ); ?>
					<div style="background:#f6f7f7;border:1px solid #dcdcde;border-radius:8px;padding:10px 14px;margin:8px 0;">
						<textarea readonly rows="3" style="width:100%;font-family:inherit;font-size:.92em;background:#fff;border:1px solid #e0e0e2;border-radius:6px;padding:8px;" id="ag_ri_msg_<?php echo (int) $i; ?>"><?php echo esc_textarea( $msg ); ?></textarea>
						<button class="button button-small" type="button" onclick="(function(t){t.select();document.execCommand('copy');})(document.getElementById('ag_ri_msg_<?php echo (int) $i; ?>'));this.textContent='✓ Copié';setTimeout(()=>this.textContent='📋 Copier',1500);">📋 Copier</button>
						<span style="color:#50575e;font-size:.82em;margin-left:6px;">Le lien remplace automatiquement par celui du canal ci-dessous.</span>
					</div>
				<?php endforeach; ?>

				<h3 style="margin-top:18px;">📍 Où poster (canaux curés)</h3>
				<table class="widefat striped"><thead><tr><th>Canal</th><th>Règles à respecter</th><th>Ton lien (UTM tracké)</th><th>Statut</th></tr></thead><tbody>
				<?php foreach ( $c['channels'] as $ch ) :
					$lk      = ag_ri_parrain_link( $picked_ref, $active, $ch['slug'] );
					$msg_ch  = ag_ri_build_message( $active, $picked_name, $lk, 0 );
					$posted  = ag_ri_is_posted( $picked, $active, $ch['slug'] );
					$msgid   = 'ag_ri_chmsg_' . esc_attr( $ch['slug'] );
					$lkid    = 'ag_ri_chlk_' . esc_attr( $ch['slug'] );
				?>
					<tr>
						<td><a href="<?php echo esc_url( $ch['url'] ); ?>" target="_blank" rel="noopener"><strong><?php echo esc_html( $ch['name'] ); ?></strong> ↗</a></td>
						<td style="font-size:.86em;color:#50575e;"><?php echo esc_html( $ch['rules'] ); ?></td>
						<td style="font-size:.82em;">
							<input type="text" readonly value="<?php echo esc_attr( $lk ); ?>" id="<?php echo $lkid; ?>" style="width:100%;font-family:monospace;font-size:.92em;">
							<button class="button button-small" type="button" onclick="(function(t){t.select();document.execCommand('copy');})(document.getElementById('<?php echo $lkid; ?>'));this.textContent='✓ Lien';setTimeout(()=>this.textContent='📋 Lien',1500);">📋 Lien</button>
							<details style="margin-top:4px;"><summary style="cursor:pointer;font-size:.92em;">Message + ce lien</summary>
								<textarea readonly rows="3" style="width:100%;margin-top:4px;" id="<?php echo $msgid; ?>"><?php echo esc_textarea( $msg_ch ); ?></textarea>
								<button class="button button-small" type="button" onclick="(function(t){t.select();document.execCommand('copy');})(document.getElementById('<?php echo $msgid; ?>'));this.textContent='✓ Tout';setTimeout(()=>this.textContent='📋 Tout copier',1500);">📋 Tout copier</button>
							</details>
						</td>
						<td>
							<form method="post" action="<?php echo esc_url( $post ); ?>" style="margin:0;">
								<input type="hidden" name="action" value="ag_ri_toggle">
								<input type="hidden" name="_n" value="<?php echo esc_attr( $nonce ); ?>">
								<input type="hidden" name="country" value="<?php echo esc_attr( $active ); ?>">
								<input type="hidden" name="channel" value="<?php echo esc_attr( $ch['slug'] ); ?>">
								<input type="hidden" name="_back" value="<?php echo esc_attr( add_query_arg( array( 'page' => 'ag-recrut-intl', 'c' => $active, 'rec' => $picked ), admin_url( 'admin.php' ) ) ); ?>">
								<button class="button button-small" type="submit" style="background:<?php echo $posted ? '#1e7e34' : '#fff'; ?>;color:<?php echo $posted ? '#fff' : '#1d2327'; ?>;border-color:<?php echo $posted ? '#1e7e34' : '#8c8f94'; ?>;"><?php echo $posted ? '✓ Posté' : '○ À poster'; ?></button>
							</form>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody></table>
			</div>

			<p style="max-width:980px;color:#50575e;font-size:13px;margin-top:14px;">
				📈 Les inscriptions arrivées via ces liens sont attribuées automatiquement au recruteur (cookie <code>?parrain=</code> 30 j) — tu retrouves les filleuls dans <a href="<?php echo esc_url( admin_url( 'admin.php?page=ag-ambassadeurs' ) ); ?>">Ambassadeurs</a> et le classement.
			</p>
		</div>
		<?php
	}
}
