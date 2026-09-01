<?php
/**
 * AG Article — « Failles des thèmes & plugins WordPress » (pilier sécurité).
 *
 * Crée UN article de blog (idempotent, versionné) sur les vulnérabilités
 * récentes des thèmes/plugins WordPress à forte diffusion (Avada, Elementor,
 * Slider Revolution…). Angle commercial : votre différenciateur sécurité →
 * audit AG-Audit. Contenu factuel et sourcé (liens externes vers Patchstack,
 * The Hacker News, BleepingComputer).
 *
 * Rendu par single.php (hero mappé via le slug). Catégorie « Sécurité ».
 * Chargé via require dans inc/ag-redirections.php.
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AG_ARTICLE_FAILLES_VER', 1 );
define( 'AG_ARTICLE_FAILLES_SLUG', 'failles-theme-plugin-wordpress' );

if ( ! function_exists( 'ag_article_failles_content' ) ) {
	function ag_article_failles_content() {
		$audit = esc_url( home_url( '/tester-mon-site' ) );
		$secu  = esc_url( home_url( '/securite-informatique-pme-nantes' ) );
		$ctc   = esc_url( home_url( '/contact' ) );

		$h  = "<p>Un thème ou une extension WordPress, c'est du code que vous n'avez pas écrit et qui tourne sur <strong>votre</strong> site. Quand il contient une faille, votre site devient une porte ouverte — même si vous n'avez rien fait de mal. Et les thèmes les plus populaires sont les plus visés, précisément parce qu'ils sont partout. Voici trois cas récents (2026), ce qu'ils veulent dire pour votre entreprise, et comment savoir si vous êtes exposé.</p>\n\n";

		$h .= "<h2>Pourquoi les thèmes et plugins « stars » sont des cibles</h2>\n";
		$h .= "<p>Un pirate ne cherche pas votre site en particulier : il cherche une faille présente sur des milliers de sites d'un coup. Un thème vendu un million de fois ou une extension installée sur des millions de sites, c'est un seul bug qui ouvre des milliers de portes. Les attaques sont <strong>automatisées</strong> : des robots scannent le web en continu, repèrent la version de votre thème/plugin, et frappent celles qui sont vulnérables — souvent quelques jours seulement après la publication de la faille.</p>\n\n";

		$h .= "<h2>Trois failles WordPress récentes (2026)</h2>\n";

		$h .= "<h3>Avada — prise de contrôle du site à distance</h3>\n";
		$h .= "<p>Avada est l'un des thèmes WordPress premium les plus vendus au monde. En août 2026, une faille critique (<strong>CVE-2026-18431</strong>) a été divulguée : elle permet à un attaquant <strong>non authentifié</strong> d'écrire un fichier sur le serveur et d'exécuter du code à distance (RCE) — autrement dit, de prendre le contrôle du site sans mot de passe. Sont concernées les versions <strong>jusqu'à 7.16</strong> (avec Fusion Builder ≤ 3.16). Correctif : mettre Avada et Fusion Builder à jour vers la dernière version publiée. <em>(Sources : <a href=\"https://patchstack.com/database/wordpress/theme/avada/vulnerabilities\" target=\"_blank\" rel=\"noopener nofollow\">Patchstack</a>, <a href=\"https://thehackernews.com/2026/08/five-critical-wordpress-plugin-and.html\" target=\"_blank\" rel=\"noopener nofollow\">The Hacker News</a>, <a href=\"https://www.bleepingcomputer.com/news/security/critical-avada-wordpress-theme-flaw-enables-zero-click-rce/\" target=\"_blank\" rel=\"noopener nofollow\">BleepingComputer</a>.)</em></p>\n";

		$h .= "<h3>Elementor Pro — un simple formulaire suffit</h3>\n";
		$h .= "<p>Elementor équipe des millions de sites. La faille <strong>CVE-2026-32475</strong> (score CVSS 9,0) touche le champ « Téléversement de fichier » des formulaires : en envoyant deux morceaux de fichier pour le même champ, un attaquant non authentifié contourne le filtre d'extensions et dépose un fichier PHP exécutable. Il suffit qu'une page publiée contienne un formulaire avec un champ d'upload — une configuration courante. Versions affectées : <strong>jusqu'à 4.2.1</strong>. <strong>Corrigé en 4.2.2</strong> (19 août 2026). <em>(Source : <a href=\"https://thehackernews.com/2026/08/elementor-pro-flaw-could-let.html\" target=\"_blank\" rel=\"noopener nofollow\">The Hacker News</a> / Patchstack.)</em></p>\n";

		$h .= "<h3>Slider Revolution — un classique toujours d'actualité</h3>\n";
		$h .= "<p>Slider Revolution (RevSlider) est présent sur des millions de sites, souvent intégré dans des thèmes achetés « clé en main ». Il a fait l'objet de plusieurs vulnérabilités, dont <strong>CVE-2026-6692</strong> en 2026. Le piège classique : le plugin est livré <em>caché</em> dans un thème, l'utilisateur ne sait même pas qu'il l'a, et ne le met donc jamais à jour. <em>(Sources : <a href=\"https://patchstack.com/database/wordpress/plugin/revslider/vulnerabilities\" target=\"_blank\" rel=\"noopener nofollow\">Patchstack</a>, <a href=\"https://www.it-connect.tech/wordpress-update-slider-revolution-now-to-patch-cve-2026-6692/\" target=\"_blank\" rel=\"noopener nofollow\">IT-Connect</a>.)</em></p>\n\n";

		$h .= "<p>Et ce ne sont pas des cas isolés : fin août 2026, cinq failles <strong>critiques</strong> (score 9,8 à 10) ont été publiées la même semaine sur des extensions très répandues (Avada, GiveWP, Pods, TranslatePress, WPMU DEV). Le message est clair : <strong>être connu et populaire ne protège de rien</strong>.</p>\n\n";

		$h .= "<h2>Ce que ça veut dire pour votre entreprise</h2>\n";
		$h .= "<p>Un site compromis, ce n'est pas juste « technique ». C'est concret :</p>\n";
		$h .= "<ul>\n";
		$h .= "<li><strong>Vol de données</strong> : coordonnées clients, messages de contact, parfois données de paiement.</li>\n";
		$h .= "<li><strong>Défiguration ou redirection</strong> : vos visiteurs envoyés vers un site frauduleux.</li>\n";
		$h .= "<li><strong>Blacklist Google</strong> : un site infecté peut être marqué « site dangereux » et disparaître des résultats du jour au lendemain.</li>\n";
		$h .= "<li><strong>Responsabilité RGPD</strong> : en cas de fuite de données personnelles, c'est vous le responsable de traitement.</li>\n";
		$h .= "<li><strong>Coût de remédiation</strong> : nettoyer un site piraté coûte bien plus cher que de le maintenir à jour.</li>\n";
		$h .= "</ul>\n\n";

		$h .= "<h2>Êtes-vous exposé ? La check-list rapide</h2>\n";
		$h .= "<ul>\n";
		$h .= "<li>Votre thème est-il <strong>Avada, Divi, Elementor</strong> ou un thème « premium » acheté sur une marketplace ?</li>\n";
		$h .= "<li>Savez-vous quelle <strong>version</strong> de votre thème et de vos extensions tourne aujourd'hui ?</li>\n";
		$h .= "<li>Les mises à jour sont-elles faites <strong>régulièrement</strong> (et testées) ?</li>\n";
		$h .= "<li>Avez-vous des plugins <strong>cachés dans le thème</strong> (comme Slider Revolution) que vous ne mettez jamais à jour ?</li>\n";
		$h .= "<li>Avez-vous une <strong>sauvegarde récente et testée</strong>, capable de tout restaurer ?</li>\n";
		$h .= "</ul>\n";
		$h .= "<p>Si vous hésitez sur une seule de ces questions, vous êtes probablement exposé sans le savoir.</p>\n\n";

		$h .= "<h2>Que faire maintenant</h2>\n";
		$h .= "<ol>\n";
		$h .= "<li><strong>Mettez à jour</strong> le cœur WordPress, le thème et toutes les extensions — en priorité Avada, Elementor et Slider Revolution s'ils sont présents.</li>\n";
		$h .= "<li><strong>Faites l'inventaire</strong> de ce qui tourne réellement sur votre site (thème, extensions, versions).</li>\n";
		$h .= "<li><strong>Sauvegardez</strong> automatiquement et vérifiez que la restauration fonctionne.</li>\n";
		$h .= "<li><strong>Faites auditer</strong> votre site : un diagnostic repère les versions vulnérables et les portes ouvertes avant les pirates.</li>\n";
		$h .= "</ol>\n\n";

		$h .= "<div style=\"margin:2em 0;padding:1.6em 1.4em;border-radius:16px;border:1px solid rgba(212,180,92,.3);background:linear-gradient(160deg,#14141f,#0b0b13);text-align:center\">";
		$h .= "<p style=\"color:#e7dcc2;font-size:1.08rem;margin:0 0 1em\">Alliance Groupe est un studio web <strong>et cybersécurité</strong> à Nantes. On vérifie gratuitement si votre site utilise une version vulnérable — sans rien casser.</p>";
		$h .= "<a href=\"" . $audit . "\" style=\"display:inline-block;margin:4px 6px;padding:13px 24px;border-radius:999px;font-weight:800;text-decoration:none;color:#0a0a0f;background:linear-gradient(90deg,#D4B45C,#F37A1F)\">🔍 Tester mon site gratuitement</a>";
		$h .= "<a href=\"" . $ctc . "\" style=\"display:inline-block;margin:4px 6px;padding:11px 22px;border-radius:999px;font-weight:700;text-decoration:none;color:#D4B45C;border:2px solid #D4B45C\">Parler à un expert</a>";
		$h .= "</div>\n\n";

		$h .= "<h2>Questions fréquentes</h2>\n";
		$h .= "<h3>Je n'ai rien fait de spécial, pourquoi mon site serait-il visé ?</h3>\n";
		$h .= "<p>Vous n'êtes pas visé personnellement. Des robots scannent le web en masse à la recherche de versions vulnérables connues. Si la vôtre en fait partie, elle est trouvée automatiquement, sans que personne ne vous « cible ».</p>\n";
		$h .= "<h3>Mettre à jour peut-il casser mon site ?</h3>\n";
		$h .= "<p>C'est le vrai frein — d'où l'intérêt d'une maintenance qui teste les mises à jour avant de les appliquer, avec une sauvegarde prête à restaurer. Ne pas mettre à jour est un risque bien plus grand.</p>\n";
		$h .= "<h3>Comment savoir quelle version j'utilise ?</h3>\n";
		$h .= "<p>Un <a href=\"" . $audit . "\">diagnostic gratuit</a> détecte le thème, les extensions et leurs versions depuis l'extérieur, et vous dit lesquelles sont à risque. Voir aussi notre page <a href=\"" . $secu . "\">sécurité informatique pour PME</a>.</p>\n";

		return $h;
	}
}

/* Création idempotente de l'article. */
if ( ! function_exists( 'ag_article_failles_ensure' ) ) {
	function ag_article_failles_ensure() {
		if ( (int) get_option( 'ag_article_failles_done', 0 ) >= AG_ARTICLE_FAILLES_VER ) return;
		if ( ! function_exists( 'wp_insert_post' ) ) return;
		if ( get_page_by_path( AG_ARTICLE_FAILLES_SLUG, OBJECT, 'post' ) ) {
			update_option( 'ag_article_failles_done', AG_ARTICLE_FAILLES_VER );
			return;
		}
		$post_id = wp_insert_post( array(
			'post_title'   => 'Failles des thèmes & plugins WordPress : Avada, Elementor, Slider Revolution — êtes-vous exposé ?',
			'post_name'    => AG_ARTICLE_FAILLES_SLUG,
			'post_excerpt' => 'Avada (CVE-2026-18431), Elementor Pro (CVE-2026-32475), Slider Revolution : des failles critiques récentes sur des thèmes/plugins ultra-répandus. Ce que ça change pour votre site, et comment savoir si vous êtes exposé.',
			'post_content' => ag_article_failles_content(),
			'post_status'  => 'publish',
			'post_type'    => 'post',
			'post_author'  => 1,
		) );
		if ( $post_id && ! is_wp_error( $post_id ) ) {
			$cat = get_cat_ID( 'Sécurité' );
			if ( ! $cat ) {
				$new = wp_insert_term( 'Sécurité', 'category' );
				if ( ! is_wp_error( $new ) ) $cat = (int) $new['term_id'];
			}
			if ( $cat ) wp_set_post_categories( $post_id, array( (int) $cat ) );
		}
		update_option( 'ag_article_failles_done', AG_ARTICLE_FAILLES_VER );
	}
}
add_action( 'admin_init', 'ag_article_failles_ensure' );
add_action( 'init', function () {
	if ( (int) get_option( 'ag_article_failles_done', 0 ) < AG_ARTICLE_FAILLES_VER ) ag_article_failles_ensure();
}, 99 );
