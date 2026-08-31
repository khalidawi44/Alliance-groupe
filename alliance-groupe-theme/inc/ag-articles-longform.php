<?php
/**
 * AG — Articles « longform ».
 *
 * Le générateur d'articles (ag-seo-autopub.php) n'INSÈRE que les slugs absents :
 * il ne met jamais à jour un article déjà publié. Ce module sert à ÉTOFFER le
 * contenu d'articles existants (le point n°1 du SEO : profondeur de contenu).
 *
 * Fonctionnement : versionné via l'option `ag_longform_ver`. Quand la version du
 * code est supérieure à celle stockée, on met à jour le `post_content` des slugs
 * listés, puis on enregistre la nouvelle version → s'exécute UNE fois, sans
 * retoucher les images à la une (gérées à part) ni le hero (choisi par slug).
 *
 * @package AllianceGroupe
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'AG_LONGFORM_VER', 1 );

/**
 * Banque de contenus étoffés : slug => HTML complet (~1000–1500 mots).
 * Les liens internes utilisent home_url() pour pointer vers les pages « argent ».
 */
function ag_longform_bank() {
	$creation = esc_url( home_url( '/service-creation-web' ) );
	$audit    = esc_url( home_url( '/tester-mon-site' ) );
	$seo      = esc_url( home_url( '/service-seo' ) );
	$wp_secu  = esc_url( home_url( '/wordpress-est-il-securise' ) );
	$ranco    = esc_url( home_url( '/resilience-ransomware' ) );
	$contact  = esc_url( home_url( '/contact' ) );

	$bank = array();

	/* ───────────── Sauvegardes : la règle 3-2-1 ───────────── */
	$bank['sauvegardes-regle-3-2-1'] = '
<p>Un site web sans sauvegarde fiable, c’est une entreprise qui joue sa vitrine à pile ou face. Une mise à jour qui tourne mal, une extension qui casse, un piratage, une erreur de manipulation ou une panne chez l’hébergeur — et des mois de travail peuvent disparaître en quelques secondes. La bonne nouvelle : il existe une méthode simple, éprouvée par les professionnels de l’informatique, pour ne <em>jamais</em> tout perdre. Elle tient en trois chiffres : <strong>3-2-1</strong>.</p>

<h2>La règle 3-2-1, en une phrase</h2>
<p>La règle 3-2-1 dit qu’une sauvegarde vraiment sûre repose sur : <strong>3 copies</strong> de vos données, sur <strong>2 supports différents</strong>, dont <strong>1 conservée hors site</strong>. C’est le standard utilisé aussi bien pour un serveur d’entreprise que pour un site vitrine de PME. Chaque chiffre supprime un risque précis.</p>

<h3>3 copies de vos données</h3>
<p>L’original qui tourne en production, plus deux sauvegardes. Pourquoi deux ? Parce qu’une sauvegarde unique peut elle-même être corrompue, incomplète, ou écrasée par une sauvegarde défectueuse. Avec deux copies indépendantes, la probabilité de tout perdre au même moment devient infime.</p>

<h3>2 supports différents</h3>
<p>Ne mettez pas vos deux sauvegardes au même endroit. Par exemple : une copie chez votre hébergeur <em>et</em> une copie sur un stockage cloud distinct (Google Drive, un espace S3, un disque externe). Si un support tombe — panne matérielle, compte suspendu, hébergeur en difficulté — l’autre reste disponible.</p>

<h3>1 copie hors site</h3>
<p>C’est le maillon que presque tout le monde oublie. Une sauvegarde stockée sur le même serveur que le site ne protège de rien : si le serveur est piraté ou détruit, la sauvegarde part avec. Une copie <strong>hors site</strong> (physiquement ailleurs) vous sauve en cas de sinistre, de rançongiciel qui chiffre tout, ou de compromission complète de l’hébergement.</p>

<h2>Pourquoi c’est vital pour une PME</h2>
<p>Les incidents qui détruisent un site ne sont pas rares — ils sont quotidiens :</p>
<ul>
	<li><strong>Rançongiciel</strong> : vos fichiers sont chiffrés et rendus inaccessibles tant que vous ne payez pas. Une sauvegarde hors site permet de restaurer sans céder au chantage. On en parle en détail dans notre article <a href="' . $ranco . '">résilience face aux rançongiciels</a>.</li>
	<li><strong>Erreur humaine</strong> : une suppression accidentelle, une mise à jour qui casse la mise en page, un réglage modifié par mégarde.</li>
	<li><strong>Extension ou thème défectueux</strong> : sur WordPress, une simple mise à jour peut provoquer un écran blanc. Avec une sauvegarde récente, vous revenez en arrière en minutes.</li>
	<li><strong>Panne ou faillite de l’hébergeur</strong> : ça arrive plus souvent qu’on ne le croit, et les petits hébergeurs ne garantissent pas toujours vos données.</li>
	<li><strong>Piratage</strong> : un site mal entretenu se fait injecter du code malveillant. Restaurer une version saine est souvent la sortie la plus rapide.</li>
</ul>

<h2>Appliquer la règle 3-2-1 à un site WordPress</h2>
<p>Concrètement, une sauvegarde WordPress complète doit contenir <strong>deux choses</strong> : les <em>fichiers</em> (thème, extensions, médias) et la <em>base de données</em> (vos pages, articles, réglages, commandes). Sauvegarder l’un sans l’autre ne sert à rien. Voici comment décliner le 3-2-1 :</p>
<ul>
	<li><strong>Copie 1</strong> — la sauvegarde automatique de votre hébergeur (quotidienne idéalement).</li>
	<li><strong>Copie 2</strong> — une extension de sauvegarde (type UpdraftPlus, BlogVault ou solution équivalente) qui envoie une archive complète vers un cloud <strong>externe</strong> (Google Drive, Dropbox, stockage objet). C’est votre copie hors site.</li>
	<li><strong>Copie 3</strong> — une archive téléchargée périodiquement sur un disque que vous contrôlez, avant chaque grosse intervention (refonte, migration, mise à jour majeure).</li>
</ul>
<p>Chez Alliance Groupe, chaque <a href="' . $creation . '">site que nous créons</a> est livré avec une stratégie de sauvegarde de ce type déjà en place — parce qu’un beau site sans filet de sécurité n’est pas un site professionnel.</p>

<h2>La fréquence : à quel rythme sauvegarder ?</h2>
<p>Cela dépend de la vitesse à laquelle votre site change :</p>
<ul>
	<li><strong>Site vitrine</strong> qui bouge peu : une sauvegarde hebdomadaire complète + avant chaque intervention suffit.</li>
	<li><strong>Site avec blog actif</strong> : sauvegarde quotidienne pour ne pas perdre les derniers articles.</li>
	<li><strong>E-commerce</strong> : sauvegarde <strong>en continu ou quotidienne</strong> impérative — chaque commande perdue est un client mécontent et un litige potentiel.</li>
</ul>

<h2>Le point que tout le monde oublie : tester ses sauvegardes</h2>
<p>Une sauvegarde jamais testée n’est pas une sauvegarde — c’est une hypothèse. Beaucoup d’entreprises découvrent le jour du sinistre que leurs archives étaient corrompues, incomplètes, ou impossibles à restaurer. <strong>Testez une restauration au moins une fois</strong>, sur un environnement de test, pour vérifier que le processus fonctionne réellement et combien de temps il prend. C’est le réflexe qui sépare les amateurs des professionnels.</p>

<h2>Les erreurs de sauvegarde les plus fréquentes</h2>
<ul>
	<li>Ne sauvegarder que les fichiers <em>ou</em> que la base de données (il faut les deux).</li>
	<li>Stocker la seule sauvegarde sur le serveur du site (aucune protection en cas de piratage).</li>
	<li>Compter sur « l’hébergeur qui gère » sans jamais l’avoir vérifié.</li>
	<li>Ne jamais tester une restauration.</li>
	<li>Oublier de sauvegarder avant une mise à jour ou une refonte.</li>
</ul>

<h2>Questions fréquentes</h2>
<h3>WordPress fait-il des sauvegardes automatiquement ?</h3>
<p>Non. WordPress seul ne sauvegarde rien. C’est à vous (ou à votre agence) de mettre en place l’hébergeur, une extension et une copie hors site. Sans cela, vous n’avez aucun filet.</p>

<h3>Combien de temps faut-il garder ses sauvegardes ?</h3>
<p>Conservez au minimum 30 jours d’historique. Certaines compromissions passent inaperçues plusieurs semaines : avoir d’anciennes versions permet de revenir à un point réellement sain.</p>

<h3>Une sauvegarde protège-t-elle du piratage ?</h3>
<p>Elle ne l’<em>empêche</em> pas, mais elle vous permet de vous en remettre vite. La prévention (mises à jour, pare-feu, mots de passe forts) reste indispensable — voir notre guide <a href="' . $wp_secu . '">WordPress est-il sécurisé ?</a>.</p>

<h2>En résumé</h2>
<p>La règle 3-2-1 — 3 copies, 2 supports, 1 hors site — est la façon la plus simple et la plus fiable de ne jamais perdre votre site. Elle se met en place une fois, puis tourne toute seule. Si vous n’êtes pas certain d’être protégé aujourd’hui, commencez par un <a href="' . $audit . '">diagnostic gratuit de votre site</a> : nous vérifions notamment l’état de vos sauvegardes et votre exposition aux risques. Et si vous voulez repartir sur des bases saines, <a href="' . $contact . '">parlons de votre projet</a>.</p>
';

	return $bank;
}

/**
 * Applique les contenus étoffés une seule fois (versionné).
 */
function ag_longform_apply() {
	if ( (int) get_option( 'ag_longform_ver', 0 ) >= AG_LONGFORM_VER ) {
		return;
	}
	foreach ( ag_longform_bank() as $slug => $html ) {
		$post = get_page_by_path( $slug, OBJECT, 'post' );
		if ( ! $post ) { continue; }
		wp_update_post( array(
			'ID'           => $post->ID,
			'post_content' => trim( $html ),
		) );
	}
	update_option( 'ag_longform_ver', AG_LONGFORM_VER );
}
add_action( 'admin_init', 'ag_longform_apply' );
add_action( 'init', function () {
	// Filet : applique aussi hors admin (une seule fois, grâce au verrou d'option).
	if ( (int) get_option( 'ag_longform_ver', 0 ) < AG_LONGFORM_VER ) {
		ag_longform_apply();
	}
}, 99 );
