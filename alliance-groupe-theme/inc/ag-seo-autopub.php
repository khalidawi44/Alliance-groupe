<?php
/**
 * AG SEO Autopub — publication AUTOMATIQUE d'un article de blog par semaine,
 * piochée dans une banque d'articles SEO prêts (cyber / web / local / SEO).
 * Entretient le blog sans y penser → fraîcheur de contenu = signal SEO.
 *
 * Réglages : Promo app → « ✍️ Blog auto » (on/off, fréquence, publier maintenant).
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Banque d'articles (publiés 1 par 1, dans l'ordre, jamais en double). */
if ( ! function_exists( 'ag_autopub_bank' ) ) {
	function ag_autopub_bank() {
		$audit = home_url( '/tester-mon-site' );
		$cyber = home_url( '/securite-informatique-pme-nantes' );
		$web   = home_url( '/creation-site-internet-nantes' );
		$ctc   = home_url( '/contact' );
		$cta   = "<p><a href=\"" . esc_url( $audit ) . "\">🔍 Tester mon site gratuitement &rarr;</a> &nbsp; <a href=\"" . esc_url( $ctc ) . "\">Demander un devis &rarr;</a></p>\n";
		return array(
			array( 'slug' => 'refonte-site-web-7-signes', 'cat' => 'Conseils Digital',
				'title' => 'Refonte de site web : 7 signes qu’il est temps',
				'excerpt' => 'Votre site est-il dépassé ? 7 signes clairs qu’une refonte s’impose (et ce que ça change pour vos clients).',
				'content' => "<p>Un site qui vieillit fait fuir les clients sans bruit. Voici 7 signaux à surveiller.</p>\n<h2>1. Il n’est pas adapté au mobile</h2><p>Plus de 60 % des visites sont mobiles : un site illisible sur téléphone perd la majorité de ses prospects.</p>\n<h2>2. Il est lent</h2><p>Au-delà de 3 secondes de chargement, vous perdez la moitié des visiteurs — et Google vous déclasse.</p>\n<h2>3. Le design fait « daté »</h2><p>Le design, c’est la confiance : un site vieillot donne une image vieillotte de votre entreprise.</p>\n<h2>4. Il n’est pas sécurisé (pas de HTTPS, plus de mises à jour)</h2><p>Risque de piratage et pénalité Google. <a href=\"" . esc_url( $cyber ) . "\">Voir notre accompagnement sécurité</a>.</p>\n<h2>5. Vous ne pouvez rien modifier vous-même</h2>\n<h2>6. Il n’apporte aucun client</h2><p>Invisible sur Google = inutile. Une refonte intègre le SEO dès le départ.</p>\n<h2>7. Il ne reflète plus votre offre</h2>\n<p>Si 2-3 de ces signes vous parlent, parlons-en : <a href=\"" . esc_url( $web ) . "\">création / refonte de site à Nantes</a>.</p>\n" . $cta ),
			array( 'slug' => 'wordpress-est-il-securise', 'cat' => 'Cybersécurité',
				'title' => 'WordPress est-il sécurisé ? (et comment le blinder)',
				'excerpt' => 'WordPress fait tourner 40 % du web. Est-il sûr ? Oui, s’il est bien configuré. Voici comment le blinder.',
				'content' => "<p>WordPress n’est pas « non sécurisé » : ce sont les sites <em>mal entretenus</em> qui se font pirater. La bonne nouvelle : c’est évitable.</p>\n<h2>Les 5 failles les plus courantes</h2><ul><li>Extensions/thèmes non mis à jour</li><li>Mots de passe faibles, pas de double authentification</li><li>Page de connexion exposée (force brute)</li><li>Pas de pare-feu applicatif</li><li>Aucune sauvegarde</li></ul>\n<h2>Comment le blinder</h2><p>Mises à jour régulières, 2FA, limitation des tentatives de connexion, pare-feu, et surtout sauvegardes automatiques testées.</p>\n<h2>Vérifiez l’état du vôtre</h2><p>Notre <a href=\"" . esc_url( $audit ) . "\">test gratuit</a> détecte ces points en 1 minute (score /100).</p>\n" . $cta ),
			array( 'slug' => 'rgpd-site-web-checklist', 'cat' => 'Cybersécurité',
				'title' => 'RGPD : votre site est-il en règle ? (checklist)',
				'excerpt' => 'La checklist RGPD pour votre site : cookies, formulaires, mentions, durée de conservation. Évitez les sanctions.',
				'content' => "<p>Le RGPD s’applique à tout site qui collecte des données (un simple formulaire de contact compte). Voici la checklist essentielle.</p>\n<h2>La checklist</h2><ul><li>Bandeau cookies conforme (consentement réel, refus aussi simple qu’accepter)</li><li>Page Politique de confidentialité claire</li><li>Mentions légales à jour</li><li>Formulaires : finalité indiquée + base légale</li><li>Durée de conservation définie</li><li>Données hébergées dans l’UE de préférence</li><li>HTTPS partout</li></ul>\n<h2>Le risque</h2><p>Sanctions CNIL jusqu’à 4 % du chiffre d’affaires, et perte de confiance.</p>\n<h2>On vérifie pour vous</h2>" . $cta ),
			array( 'slug' => 'site-lent-comment-accelerer', 'cat' => 'Conseils Digital',
				'title' => 'Pourquoi votre site est lent (et comment l’accélérer)',
				'excerpt' => 'Un site lent perd des clients et du référencement. Les causes fréquentes et les solutions concrètes.',
				'content' => "<p>La vitesse est un facteur de classement Google ET de conversion. Voici quoi regarder.</p>\n<h2>Les causes fréquentes</h2><ul><li>Images trop lourdes (non compressées / mauvais format)</li><li>Trop d’extensions</li><li>Hébergement bas de gamme</li><li>Pas de cache</li></ul>\n<h2>Les solutions</h2><p>Compression d’images (WebP), cache, hébergement adapté, nettoyage des extensions, et un thème léger. Les Core Web Vitals doivent être au vert.</p>\n<h2>Mesurez votre vitesse</h2><p>Notre <a href=\"" . esc_url( $audit ) . "\">diagnostic gratuit</a> inclut un coup d’œil performance.</p>\n" . $cta ),
			array( 'slug' => 'vitrine-ou-ecommerce', 'cat' => 'Conseils Digital',
				'title' => 'Site vitrine ou e-commerce : lequel pour votre activité ?',
				'excerpt' => 'Vitrine ou boutique en ligne ? Le bon choix selon votre activité, votre budget et vos objectifs.',
				'content' => "<p>Pas besoin d’une boutique en ligne pour réussir sur le web. Tout dépend de votre objectif.</p>\n<h2>Le site vitrine</h2><p>Idéal pour les artisans, professions libérales, prestataires de services : présenter, rassurer, générer des contacts/devis.</p>\n<h2>L’e-commerce</h2><p>Indispensable si vous vendez des produits en ligne, avec paiement, stock, livraison.</p>\n<h2>Le bon réflexe</h2><p>Commencez par l’objectif : des appels/devis ? une vitrine suffit. Des ventes en ligne ? e-commerce. <a href=\"" . esc_url( $web ) . "\">On vous conseille gratuitement</a>.</p>\n" . $cta ),
			array( 'slug' => 'seo-local-google-maps-nantes', 'cat' => 'Conseils Digital',
				'title' => 'Comment apparaître sur Google Maps (SEO local)',
				'excerpt' => 'Le guide pour ressortir dans le « pack local » de Google Maps et capter les clients de votre ville.',
				'content' => "<p>Quand un client cherche « [votre métier] près de moi », Google affiche 3 fiches. Voici comment en faire partie.</p>\n<h2>1. Fiche Google Business Profile complète</h2><p>Catégories précises, description, photos, horaires, zone desservie.</p>\n<h2>2. Les avis</h2><p>Nombre, fraîcheur et réponses : c’est près de la moitié du classement local.</p>\n<h2>3. La cohérence (NAP)</h2><p>Nom, adresse, téléphone identiques partout sur le web.</p>\n<h2>4. Un site local optimisé</h2><p>Pages géolocalisées + schema LocalBusiness (déjà en place sur nos sites).</p>\n" . $cta ),
			array( 'slug' => 'phishing-proteger-pme', 'cat' => 'Cybersécurité',
				'title' => 'Phishing : protéger votre PME des arnaques par email',
				'excerpt' => 'Le phishing est la 1re porte d’entrée des cyberattaques. Comment reconnaître et bloquer les emails piégés.',
				'content' => "<p>La majorité des piratages commencent par un simple email. Former vos équipes coûte bien moins cher qu’une attaque.</p>\n<h2>Reconnaître un email de phishing</h2><ul><li>Expéditeur qui imite une marque connue</li><li>Urgence / menace (« votre compte va être bloqué »)</li><li>Lien qui ne mène pas au vrai site (survolez avant de cliquer)</li><li>Pièce jointe inattendue</li></ul>\n<h2>Se protéger</h2><p>Double authentification, sensibilisation des équipes, filtrage anti-spam, et procédure en cas de doute.</p>\n<h2>Audit de votre exposition</h2>" . $cta ),
			array( 'slug' => 'sauvegardes-regle-3-2-1', 'cat' => 'Cybersécurité',
				'title' => 'Sauvegardes : la règle 3-2-1 expliquée simplement',
				'excerpt' => 'La règle 3-2-1 des sauvegardes : la méthode simple qui sauve votre entreprise en cas d’attaque ou de panne.',
				'content' => "<p>Une bonne sauvegarde, c’est la différence entre un incident et une catastrophe. La règle 3-2-1 est le standard.</p>\n<h2>La règle 3-2-1</h2><ul><li><strong>3</strong> copies de vos données</li><li><strong>2</strong> supports différents</li><li><strong>1</strong> copie hors site (ailleurs)</li></ul>\n<h2>Et surtout : testez-les</h2><p>Une sauvegarde jamais testée est une sauvegarde qui peut ne pas fonctionner le jour J.</p>\n<h2>On met ça en place pour vous</h2>" . $cta ),
			array( 'slug' => 'nom-domaine-hebergement-2026', 'cat' => 'Conseils Digital',
				'title' => 'Nom de domaine et hébergement : bien choisir en 2026',
				'excerpt' => 'Comment choisir son nom de domaine et son hébergement : les critères qui comptent vraiment (sécurité, perf, UE).',
				'content' => "<p>Deux fondations souvent négligées qui impactent votre image, votre vitesse et votre sécurité.</p>\n<h2>Le nom de domaine</h2><p>Court, mémorisable, en .fr ou .com. Évitez les tirets à rallonge. Réservez les variantes proches.</p>\n<h2>L’hébergement</h2><p>Privilégiez un hébergement rapide, sécurisé, avec sauvegardes et idéalement dans l’UE (RGPD). Le « pas cher » se paie en lenteur et en pannes.</p>\n<h2>On gère tout pour vous</h2><p>Nom de domaine, hébergement, sécurité, maintenance : un seul interlocuteur. <a href=\"" . esc_url( $web ) . "\">En savoir plus</a>.</p>\n" . $cta ),
			array( 'slug' => 'ransomware-que-faire', 'cat' => 'Cybersécurité',
				'title' => 'Rançongiciel (ransomware) : que faire si on est touché ?',
				'excerpt' => 'Frappé par un ransomware ? Les bons réflexes immédiats (et comment éviter d’en arriver là).',
				'content' => "<p>Un rançongiciel chiffre vos fichiers et exige une rançon. Voici la conduite à tenir.</p>\n<h2>Les premiers réflexes</h2><ul><li>Isoler les machines touchées du réseau</li><li>Ne pas payer (aucune garantie, finance le crime)</li><li>Prévenir, conserver les preuves, déposer plainte</li><li>Restaurer depuis une sauvegarde saine</li></ul>\n<h2>La meilleure défense = la prévention</h2><p>Sauvegardes 3-2-1, mises à jour, sensibilisation, et un audit régulier de votre exposition. <a href=\"" . esc_url( $cyber ) . "\">Notre accompagnement sécurité</a>.</p>\n" . $cta ),
		);
	}
}

/* Publie le prochain article non encore présent. Retourne le titre publié ou ''. */
if ( ! function_exists( 'ag_autopub_publish_next' ) ) {
	function ag_autopub_publish_next() {
		foreach ( ag_autopub_bank() as $a ) {
			if ( get_page_by_path( $a['slug'], OBJECT, 'post' ) ) continue;
			$cat = function_exists( 'ag_seo_blog_cat' ) ? ag_seo_blog_cat( $a['cat'] ) : 0;
			$id = wp_insert_post( array(
				'post_title'    => $a['title'],
				'post_name'     => $a['slug'],
				'post_content'  => $a['content'],
				'post_excerpt'  => $a['excerpt'],
				'post_status'   => 'publish',
				'post_type'     => 'post',
				'post_author'   => 1,
				'post_category' => $cat ? array( $cat ) : array(),
			) );
			if ( $id && ! is_wp_error( $id ) ) {
				update_option( 'ag_autopub_last', current_time( 'd/m/Y' ) );
				if ( function_exists( 'ag_push' ) ) ag_push( '✍️ Nouvel article publié', $a['title'] );
				return $a['title'];
			}
		}
		return ''; // banque épuisée
	}
}

/* Planification hebdomadaire. */
add_filter( 'cron_schedules', function ( $s ) {
	if ( ! isset( $s['ag_weekly'] ) ) $s['ag_weekly'] = array( 'interval' => 7 * DAY_IN_SECONDS, 'display' => 'Une fois par semaine (AG)' );
	return $s;
} );
add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'ag_autopub_cron' ) ) {
		wp_schedule_event( time() + 3 * DAY_IN_SECONDS, 'ag_weekly', 'ag_autopub_cron' );
	}
} );
add_action( 'ag_autopub_cron', function () {
	if ( '1' !== get_option( 'ag_autopub_on', '1' ) ) return;
	ag_autopub_publish_next();
} );

/* Réglages : sous-menu sous « 📢 Promo app ». */
add_action( 'admin_init', function () {
	if ( isset( $_POST['ag_autopub_save'] ) && check_admin_referer( 'ag_autopub' ) ) {
		update_option( 'ag_autopub_on', isset( $_POST['ag_autopub_on'] ) ? '1' : '0' );
	}
	if ( isset( $_POST['ag_autopub_now'] ) && check_admin_referer( 'ag_autopub' ) ) {
		$t = ag_autopub_publish_next();
		add_settings_error( 'ag_autopub', 'pub', $t ? ( '✅ Publié : ' . $t ) : 'Banque d’articles épuisée — dis-le-moi, j’en ajoute.', $t ? 'updated' : 'error' );
	}
} );
add_action( 'admin_menu', function () {
	add_submenu_page( 'ag-app-promo', 'Blog auto', '✍️ Blog auto', 'manage_options', 'ag-blog-auto', 'ag_autopub_render' );
}, 20 );
if ( ! function_exists( 'ag_autopub_render' ) ) {
	function ag_autopub_render() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		settings_errors( 'ag_autopub' );
		$on   = '1' === get_option( 'ag_autopub_on', '1' );
		$bank = ag_autopub_bank();
		$rest = 0; foreach ( $bank as $a ) { if ( ! get_page_by_path( $a['slug'], OBJECT, 'post' ) ) $rest++; }
		$next = wp_next_scheduled( 'ag_autopub_cron' );
		echo '<div class="wrap"><h1>✍️ Blog automatique</h1>';
		echo '<p style="max-width:820px;color:#50575e;">Publie <strong>1 article SEO par semaine</strong> automatiquement (cyber, web, SEO local) pour entretenir le blog et nourrir ton référencement — sans rien faire. Les articles maillent vers tes pages piliers et le test gratuit.</p>';
		echo '<p><strong>' . (int) $rest . '</strong> article(s) restant(s) dans la banque · dernière publi : ' . esc_html( get_option( 'ag_autopub_last', '—' ) ) . ' · prochaine : ' . ( $next ? esc_html( wp_date( 'd/m/Y', $next ) ) : '—' ) . '</p>';
		echo '<form method="post">';
		wp_nonce_field( 'ag_autopub' );
		echo '<p><label><input type="checkbox" name="ag_autopub_on" ' . checked( $on, true, false ) . '> <strong>Activer la publication automatique hebdomadaire</strong></label></p>';
		echo '<button name="ag_autopub_save" value="1" class="button button-primary">Enregistrer</button> ';
		echo '<button name="ag_autopub_now" value="1" class="button">Publier le prochain maintenant</button>';
		echo '</form>';
		echo '<p style="margin-top:12px;color:#50575e;font-size:.9em;">💡 Chaque article est publié une seule fois. Quand la banque est vide, demande-moi d’en ajouter (ou de brancher une génération IA).</p>';
		echo '</div>';
	}
}
