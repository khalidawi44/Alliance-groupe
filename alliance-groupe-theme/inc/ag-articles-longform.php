<?php
/**
 * AG — Articles « longform ».
 *
 * Le générateur d'articles (ag-seo-autopub.php) n'INSÈRE que les slugs absents :
 * il ne met jamais à jour un article déjà publié. Ce module ÉTOFFE le contenu
 * d'articles existants (le point n°1 du SEO : profondeur de contenu).
 *
 * Versionné via l'option `ag_longform_ver` : à chaque hausse de AG_LONGFORM_VER,
 * les `post_content` des slugs listés sont réécrits une fois. Les images à la une
 * (gérées à part) et le hero (choisi par slug dans single.php) ne sont pas touchés.
 *
 * Pour étoffer d'autres articles : ajouter au $bank + incrémenter AG_LONGFORM_VER.
 *
 * @package AllianceGroupe
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'AG_LONGFORM_VER', 2 );

/**
 * Banque de contenus étoffés : slug => HTML complet (~1000 mots).
 * Liens internes via home_url() vers les pages « argent ».
 */
function ag_longform_bank() {
	$creation = esc_url( home_url( '/service-creation-web' ) );
	$audit    = esc_url( home_url( '/tester-mon-site' ) );
	$seo      = esc_url( home_url( '/service-seo' ) );
	$wp_secu  = esc_url( home_url( '/wordpress-est-il-securise' ) );
	$ranco    = esc_url( home_url( '/resilience-ransomware' ) );
	$contact  = esc_url( home_url( '/contact' ) );
	$realis   = esc_url( home_url( '/realisations' ) );

	$bank = array();

	/* ───────────── Sauvegardes : la règle 3-2-1 ───────────── */
	$bank['sauvegardes-regle-3-2-1'] = '
<p>Un site web sans sauvegarde fiable, c’est une entreprise qui joue sa vitrine à pile ou face. Une mise à jour qui tourne mal, une extension qui casse, un piratage, une erreur de manipulation ou une panne chez l’hébergeur — et des mois de travail peuvent disparaître en quelques secondes. La bonne nouvelle : il existe une méthode simple, éprouvée par les professionnels de l’informatique, pour ne <em>jamais</em> tout perdre. Elle tient en trois chiffres : <strong>3-2-1</strong>.</p>

<h2>La règle 3-2-1, en une phrase</h2>
<p>Une sauvegarde vraiment sûre repose sur : <strong>3 copies</strong> de vos données, sur <strong>2 supports différents</strong>, dont <strong>1 conservée hors site</strong>. C’est le standard utilisé aussi bien pour un serveur d’entreprise que pour un site vitrine de PME. Chaque chiffre supprime un risque précis.</p>

<h3>3 copies de vos données</h3>
<p>L’original en production, plus deux sauvegardes. Pourquoi deux ? Parce qu’une sauvegarde unique peut être corrompue, incomplète, ou écrasée par une sauvegarde défectueuse. Avec deux copies indépendantes, la probabilité de tout perdre au même moment devient infime.</p>

<h3>2 supports différents</h3>
<p>Ne mettez pas vos deux sauvegardes au même endroit : une copie chez votre hébergeur <em>et</em> une copie sur un stockage distinct (cloud, disque externe). Si un support tombe — panne, compte suspendu, hébergeur en difficulté — l’autre reste disponible.</p>

<h3>1 copie hors site</h3>
<p>Le maillon que presque tout le monde oublie. Une sauvegarde stockée sur le même serveur que le site ne protège de rien : si le serveur est piraté ou détruit, la sauvegarde part avec. Une copie <strong>hors site</strong> vous sauve en cas de sinistre, de rançongiciel qui chiffre tout, ou de compromission complète de l’hébergement.</p>

<h2>Pourquoi c’est vital pour une PME</h2>
<ul>
	<li><strong>Rançongiciel</strong> : vos fichiers sont chiffrés. Une sauvegarde hors site permet de restaurer sans payer — voir <a href="' . $ranco . '">résilience face aux rançongiciels</a>.</li>
	<li><strong>Erreur humaine</strong> : suppression accidentelle, réglage modifié par mégarde.</li>
	<li><strong>Mise à jour qui casse</strong> : sur WordPress, une simple mise à jour peut provoquer un écran blanc.</li>
	<li><strong>Panne / faillite de l’hébergeur</strong> : plus fréquent qu’on ne le croit.</li>
	<li><strong>Piratage</strong> : restaurer une version saine est souvent la sortie la plus rapide.</li>
</ul>

<h2>Appliquer la règle 3-2-1 à WordPress</h2>
<p>Une sauvegarde WordPress complète contient <strong>deux choses</strong> : les <em>fichiers</em> (thème, extensions, médias) et la <em>base de données</em> (pages, articles, réglages, commandes). Sauvegarder l’un sans l’autre ne sert à rien. Concrètement : une sauvegarde automatique de l’hébergeur, une extension qui envoie une archive vers un cloud externe (votre copie hors site), et une archive téléchargée avant chaque grosse intervention. Chez Alliance Groupe, chaque <a href="' . $creation . '">site que nous créons</a> est livré avec ce dispositif déjà en place.</p>

<h2>Le point oublié : tester ses sauvegardes</h2>
<p>Une sauvegarde jamais testée n’est pas une sauvegarde — c’est une hypothèse. Beaucoup d’entreprises découvrent le jour du sinistre que leurs archives étaient corrompues. Testez une restauration au moins une fois, sur un environnement de test.</p>

<h2>Questions fréquentes</h2>
<h3>WordPress sauvegarde-t-il automatiquement ?</h3>
<p>Non. WordPress seul ne sauvegarde rien : c’est à vous ou à votre agence de mettre en place hébergeur + extension + copie hors site.</p>
<h3>Combien de temps garder ses sauvegardes ?</h3>
<p>Au minimum 30 jours d’historique : certaines compromissions passent inaperçues plusieurs semaines.</p>

<p>Pas sûr d’être protégé ? Commencez par un <a href="' . $audit . '">diagnostic gratuit</a> — nous vérifions l’état de vos sauvegardes et votre exposition aux risques.</p>
';

	/* ───────────── NIS2 : PME concernée en 2026 ───────────── */
	$bank['nis2-pme-concernee-2026'] = '
<p>Vous entendez parler de « NIS2 » sans savoir si votre entreprise est concernée ? Vous n’êtes pas seul. Cette directive européenne sur la cybersécurité s’applique depuis sa transposition en droit français, et elle élargit fortement le nombre d’organisations visées. Voici, sans jargon, ce qu’une PME doit comprendre en 2026.</p>

<h2>NIS2, c’est quoi exactement ?</h2>
<p>NIS2 est une directive de l’Union européenne qui impose des mesures de cybersécurité et une obligation de déclaration des incidents à un large éventail d’entités. Objectif : relever le niveau de sécurité global face à la multiplication des cyberattaques, en ne laissant plus la sécurité au bon vouloir de chacun.</p>

<h2>Ma PME est-elle concernée ?</h2>
<p>NIS2 vise deux catégories — « entités essentielles » et « entités importantes » — dans de nombreux secteurs : énergie, santé, transports, eau, infrastructures numériques, administration, mais aussi la fabrication, l’agroalimentaire, la gestion des déchets, les services postaux ou la recherche. Le critère de taille compte (souvent à partir de 50 salariés ou 10 M€ de chiffre d’affaires), mais <strong>attention</strong> : même une petite structure peut être concernée si elle est un fournisseur critique d’une entité régulée.</p>
<p>C’est le point clé pour les PME : <strong>l’effet chaîne d’approvisionnement</strong>. Si vous êtes sous-traitant, prestataire IT, ou fournisseur d’une grande entreprise soumise à NIS2, celle-ci vous imposera contractuellement des exigences de sécurité. Vous subissez alors NIS2 « par ricochet », même sans y être directement soumis.</p>

<h2>Quelles obligations concrètes ?</h2>
<ul>
	<li><strong>Analyse des risques</strong> et politique de sécurité des systèmes d’information.</li>
	<li><strong>Gestion des incidents</strong> : détection, réponse, et déclaration à l’autorité compétente dans des délais courts.</li>
	<li><strong>Continuité d’activité</strong> : sauvegardes, plan de reprise, gestion de crise (voir notre guide <a href="' . home_url( '/sauvegardes-regle-3-2-1' ) . '">sauvegardes 3-2-1</a>).</li>
	<li><strong>Sécurité de la chaîne d’approvisionnement</strong> : évaluer la sécurité de vos fournisseurs.</li>
	<li><strong>Hygiène de base</strong> : mises à jour, authentification forte (2FA), chiffrement, contrôle des accès.</li>
	<li><strong>Responsabilité de la direction</strong> : les dirigeants peuvent être tenus responsables en cas de manquement.</li>
</ul>

<h2>Par où commencer quand on est une petite structure ?</h2>
<p>Inutile de viser la conformité parfaite du jour au lendemain. Commencez par les fondamentaux, qui couvrent déjà l’essentiel du risque :</p>
<ol>
	<li>Cartographier vos données et vos accès : qui accède à quoi ?</li>
	<li>Activer l’authentification à deux facteurs partout où c’est possible.</li>
	<li>Mettre en place des sauvegardes testées, selon la règle 3-2-1.</li>
	<li>Tenir à jour systèmes, sites et extensions (un <a href="' . $wp_secu . '">WordPress bien tenu</a> est déjà un grand pas).</li>
	<li>Formaliser une procédure simple en cas d’incident.</li>
</ol>

<h2>Questions fréquentes</h2>
<h3>Que risque-t-on en cas de non-conformité ?</h3>
<p>Des sanctions administratives potentiellement lourdes pour les entités régulées, et surtout la perte de contrats si vos clients exigent la conformité de leurs fournisseurs.</p>
<h3>Un site web est-il concerné ?</h3>
<p>Votre site fait partie de votre système d’information : sécurité, sauvegardes et gestion des accès entrent dans le périmètre. C’est un bon point de départ, concret et visible.</p>

<p>Vous voulez savoir où vous en êtes ? Notre <a href="' . $audit . '">diagnostic gratuit</a> évalue le socle de sécurité de votre site, et nous vous aidons à bâtir un plan réaliste. <a href="' . $contact . '">Parlons-en</a>.</p>
';

	/* ───────────── RGPD : la checklist site web ───────────── */
	$bank['rgpd-site-web-checklist'] = '
<p>Le RGPD n’est pas qu’une affaire de grands groupes : dès que votre site collecte la moindre donnée personnelle — un formulaire de contact, une newsletter, un cookie de statistiques — vous êtes concerné. Bonne nouvelle : la mise en conformité d’un site vitrine de PME est largement à votre portée. Voici la checklist claire, point par point.</p>

<h2>Ce que le RGPD exige, en bref</h2>
<p>Le Règlement général sur la protection des données encadre la collecte et l’usage des données personnelles des résidents européens. Trois principes guident tout : <strong>transparence</strong> (dire ce que vous collectez et pourquoi), <strong>minimisation</strong> (ne collecter que le nécessaire) et <strong>sécurité</strong> (protéger ce que vous détenez).</p>

<h2>La checklist de conformité d’un site</h2>
<h3>1. Une bannière cookies conforme</h3>
<p>Si vous utilisez des cookies non essentiels (Google Analytics, Meta Pixel, publicité), il faut un consentement <strong>préalable, libre et explicite</strong> : aucun cookie de suivi ne doit se déposer avant le clic sur « Accepter », et refuser doit être aussi simple qu’accepter.</p>
<h3>2. Une politique de confidentialité claire</h3>
<p>Elle indique quelles données vous collectez, pourquoi, combien de temps vous les conservez, avec qui vous les partagez, et comment exercer ses droits. Une page dédiée, accessible depuis le pied de page.</p>
<h3>3. Des formulaires bien conçus</h3>
<p>Ne demandez que les champs utiles (minimisation). Ajoutez une mention d’information et, le cas échéant, une case à cocher <strong>non pré-cochée</strong> pour le consentement (ex. newsletter). Le consentement doit être un acte positif.</p>
<h3>4. La sécurité des données</h3>
<p>HTTPS obligatoire, accès protégés, mots de passe forts, mises à jour à jour, et sauvegardes. La sécurité fait partie intégrante du RGPD — un <a href="' . $wp_secu . '">site bien sécurisé</a> est un site plus conforme.</p>
<h3>5. Le registre et les droits des personnes</h3>
<p>Tenez un registre simple des traitements, et permettez à chacun d’accéder, rectifier ou supprimer ses données (une adresse de contact dédiée suffit souvent pour une PME).</p>
<h3>6. Les sous-traitants</h3>
<p>Hébergeur, outil d’emailing, service d’analyse : assurez-vous qu’ils sont eux-mêmes conformes et, idéalement, que les données restent dans l’UE.</p>

<h2>Les erreurs les plus fréquentes</h2>
<ul>
	<li>Charger Google Analytics ou le pixel Meta <em>avant</em> le consentement.</li>
	<li>Une case de consentement pré-cochée.</li>
	<li>Pas de politique de confidentialité, ou un copier-coller générique inexact.</li>
	<li>Un formulaire qui demande dix informations quand deux suffisent.</li>
</ul>

<h2>Questions fréquentes</h2>
<h3>Une PME risque-t-elle vraiment une sanction ?</h3>
<p>Les contrôles existent, souvent déclenchés par une plainte. Mais au-delà de l’amende, la conformité est surtout une question de <strong>confiance</strong> : un visiteur rassuré convertit mieux.</p>
<h3>Un simple site vitrine est-il concerné ?</h3>
<p>Oui, dès qu’il y a un formulaire, une newsletter ou des statistiques. Le niveau d’exigence est simplement proportionné au risque.</p>

<p>Nous intégrons la conformité RGPD à chaque <a href="' . $creation . '">site que nous créons</a>. Pour vérifier l’état du vôtre, demandez un <a href="' . $audit . '">diagnostic gratuit</a>.</p>
';

	/* ───────────── Phishing : protéger sa PME ───────────── */
	$bank['phishing-proteger-pme'] = '
<p>Le phishing (ou hameçonnage) est la <strong>première porte d’entrée</strong> des cyberattaques contre les PME. Pas besoin de pirater un pare-feu sophistiqué : il suffit qu’un salarié clique sur un faux email et saisisse ses identifiants. Voici comment reconnaître ces attaques et protéger votre entreprise, sans être expert.</p>

<h2>Le phishing, comment ça marche ?</h2>
<p>L’attaquant se fait passer pour un tiers de confiance — votre banque, un fournisseur, un service public, ou même votre propre dirigeant — pour vous pousser à agir dans l’urgence : cliquer sur un lien, ouvrir une pièce jointe, communiquer un mot de passe ou effectuer un virement. Le ressort est toujours <strong>psychologique</strong> : peur, urgence, autorité, appât du gain.</p>

<h2>Reconnaître un email piégé</h2>
<ul>
	<li><strong>Adresse d’expéditeur douteuse</strong> : un nom connu mais un domaine bizarre (banque-securite-verif.com).</li>
	<li><strong>Urgence et menace</strong> : « votre compte sera suspendu sous 24 h ».</li>
	<li><strong>Lien trompeur</strong> : le texte affiche un site officiel mais l’URL réelle (au survol) mène ailleurs.</li>
	<li><strong>Pièce jointe inattendue</strong> : facture, bon de livraison, CV que vous n’attendiez pas.</li>
	<li><strong>Fautes, mise en page approximative, formule impersonnelle</strong> (« Cher client »).</li>
	<li><strong>Demande inhabituelle</strong> : virement urgent, changement de RIB fournisseur.</li>
</ul>

<h2>La « fraude au président » et la fraude au RIB</h2>
<p>Deux variantes qui coûtent très cher aux PME. Dans la fraude au président, un email imite le dirigeant et réclame un virement confidentiel et urgent. Dans la fraude au RIB, un faux email de fournisseur annonce un changement de coordonnées bancaires. La parade est simple et non technique : <strong>toute demande de paiement ou de changement de RIB se vérifie par un second canal</strong> (un appel au numéro connu, jamais celui indiqué dans l’email).</p>

<h2>Protéger concrètement votre entreprise</h2>
<ol>
	<li><strong>Authentification à deux facteurs (2FA)</strong> partout : même si un mot de passe fuite, le compte reste protégé. C’est la mesure la plus rentable.</li>
	<li><strong>Sensibiliser l’équipe</strong> : quelques règles simples et un rappel régulier valent tous les logiciels.</li>
	<li><strong>Procédure de double validation</strong> pour les virements et changements de RIB.</li>
	<li><strong>Mots de passe uniques</strong> via un gestionnaire de mots de passe.</li>
	<li><strong>Mises à jour et sauvegardes</strong> : pour limiter les dégâts si un poste est compromis.</li>
	<li><strong>Filtre anti-spam</strong> et vérification des noms de domaine (SPF, DKIM, DMARC) pour réduire l’usurpation de votre propre domaine.</li>
</ol>

<h2>J’ai cliqué / saisi mes identifiants, que faire ?</h2>
<p>Agissez vite : changez immédiatement le mot de passe concerné (et partout où il était réutilisé), activez la 2FA, prévenez votre banque en cas de données bancaires, et surveillez les accès. Si un rançongiciel se déclenche, vos <a href="' . home_url( '/sauvegardes-regle-3-2-1' ) . '">sauvegardes</a> deviennent votre meilleure assurance.</p>

<h2>Questions fréquentes</h2>
<h3>Un antivirus suffit-il ?</h3>
<p>Non. Le phishing vise l’humain, pas la machine. La 2FA et la vigilance sont bien plus efficaces qu’un logiciel seul.</p>
<h3>Comment savoir si mon site sert à usurper mon identité ?</h3>
<p>Un domaine mal configuré facilite l’usurpation. Notre <a href="' . $audit . '">diagnostic gratuit</a> vérifie ces points.</p>

<p>Envie de mettre votre PME à l’abri ? <a href="' . $contact . '">Parlons de votre sécurité</a> — on commence toujours par un audit gratuit.</p>
';

	/* ───────────── Refonte : 7 signes ───────────── */
	$bank['refonte-site-web-7-signes'] = '
<p>Un site web vieillit — parfois vite. Le problème, c’est qu’il fait fuir des clients <em>sans bruit</em> : pas de plainte, juste des prospects qui repartent. Voici 7 signes clairs qu’une refonte s’impose, et ce que chacun coûte réellement à votre entreprise.</p>

<h2>1. Il n’est pas (vraiment) adapté au mobile</h2>
<p>Plus de 6 visites sur 10 se font sur smartphone. Un site qu’il faut zoomer, où les boutons sont minuscules ou le menu illisible, perd la majorité de ses prospects — et Google, qui indexe « mobile d’abord », le déclasse.</p>

<h2>2. Il est lent</h2>
<p>Au-delà de 3 secondes de chargement, vous perdez près de la moitié des visiteurs. La vitesse est à la fois un facteur d’expérience et de classement Google. Images non compressées, hébergement bas de gamme, trop d’extensions : les causes sont connues, et corrigeables.</p>

<h2>3. Le design fait « daté »</h2>
<p>Le design, c’est la confiance. Un site qui semble bloqué dix ans en arrière renvoie l’image d’une entreprise elle-même dépassée — même si votre travail est excellent. La première impression se joue en quelques secondes.</p>

<h2>4. Il n’est pas sécurisé</h2>
<p>Pas de HTTPS (le petit cadenas), plus de mises à jour, aucune sauvegarde : c’est un risque de piratage <em>et</em> une pénalité Google, qui signale les sites « non sécurisés » aux visiteurs. Voir notre guide <a href="' . $wp_secu . '">WordPress est-il sécurisé ?</a>.</p>

<h2>5. Vous ne pouvez rien modifier vous-même</h2>
<p>Devoir rappeler un prestataire (et payer) pour changer un horaire ou un prix, c’est le signe d’un site qui vous appartient sur le papier mais pas dans les faits. Un site moderne se met à jour en autonomie, simplement.</p>

<h2>6. Il n’apporte aucun client</h2>
<p>Un beau site invisible sur Google ne sert à rien. Si votre site ne génère ni appels, ni formulaires, ni devis, c’est qu’il n’a pas été pensé pour convertir ni pour être trouvé. Une refonte intègre le <a href="' . $seo . '">référencement</a> et des appels à l’action dès la conception.</p>

<h2>7. Il ne reflète plus votre offre</h2>
<p>Votre entreprise a évolué : nouveaux services, nouveau positionnement, nouvelle clientèle. Si votre site parle encore de ce que vous faisiez il y a trois ans, il travaille contre vous.</p>

<h2>Refonte ou nouveau site : comment décider ?</h2>
<p>Si 2 ou 3 de ces signes vous parlent, une simple mise à jour peut suffire. Si vous en cochez 5 ou plus, repartir sur une base saine est souvent plus rapide et moins cher que de rafistoler l’existant. Le bon réflexe : un diagnostic honnête avant de dépenser.</p>

<h2>Questions fréquentes</h2>
<h3>Une refonte fait-elle perdre mon référencement ?</h3>
<p>Pas si elle est bien menée : on conserve les URLs ou on met en place des redirections 301, et on améliore le contenu. Une refonte propre <em>gagne</em> généralement des positions.</p>
<h3>Combien de temps ça prend ?</h3>
<p>Pour un site vitrine de PME, comptez quelques semaines selon le volume de pages et de contenu.</p>

<p>Vous hésitez ? Regardez nos <a href="' . $realis . '">réalisations</a>, puis demandez un <a href="' . $audit . '">diagnostic gratuit</a> de votre site actuel. On vous dira franchement s’il faut refondre ou non.</p>
';

	/* ───────────── SEO local : Google Maps ───────────── */
	$bank['seo-local-google-maps-nantes'] = '
<p>Quand un client cherche « [votre métier] près de moi » ou « [votre métier] à Nantes », Google affiche trois fiches en haut de page, sur une carte : le fameux « pack local ». Y figurer vaut de l’or — c’est souvent la première chose que voit un prospect prêt à acheter. Voici comment en faire partie.</p>

<h2>Le pack local, c’est quoi ?</h2>
<p>Ce sont les 3 établissements mis en avant avec la carte Google Maps, leurs avis et leur bouton « Itinéraire / Appeler ». Ils captent une part énorme des clics sur les recherches locales, avant même les résultats classiques. Le classement y obéit à des règles différentes du SEO traditionnel.</p>

<h2>Les 3 piliers du classement local</h2>
<h3>1. Une fiche Google Business Profile complète</h3>
<p>C’est la base absolue, et c’est gratuit. Renseignez tout : nom exact, catégorie précise, adresse ou zone desservie, téléphone, horaires, description, et surtout <strong>des photos</strong> (établissement, équipe, réalisations). Une fiche complète et active est privilégiée par Google.</p>
<h3>2. Les avis</h3>
<p>Nombre, fraîcheur, note moyenne et <strong>réponses</strong> aux avis pèsent près de la moitié du classement local. Demandez systématiquement un avis à vos clients satisfaits — un lien ou un QR code suffit — et répondez à chacun, positif comme négatif.</p>
<h3>3. La cohérence NAP</h3>
<p>Votre <strong>N</strong>om, <strong>A</strong>dresse et téléphone (<strong>P</strong>hone) — le « NAP » — doivent être <strong>strictement identiques</strong> partout sur le web (site, annuaires, réseaux). La moindre incohérence brouille Google et fait perdre des places.</p>

<h2>Ce que votre site doit apporter en plus</h2>
<ul>
	<li><strong>Une page par zone / métier</strong> : « [service] à Nantes », « [service] à Saint-Nazaire »… avec un vrai contenu local, pas une page dupliquée.</li>
	<li><strong>Des données structurées LocalBusiness</strong> (schema) pour aider Google à comprendre qui vous êtes et où.</li>
	<li><strong>La cohérence NAP</strong> affichée clairement (pied de page, page contact).</li>
	<li><strong>Des avis intégrés</strong> pour rassurer et convertir.</li>
</ul>

<h2>Un plan simple pour démarrer</h2>
<ol>
	<li>Créer / revendiquer votre fiche Google Business Profile et la compléter à 100 %.</li>
	<li>Obtenir 5 à 10 premiers avis clients cette semaine.</li>
	<li>Vérifier la cohérence NAP sur vos principaux annuaires.</li>
	<li>Renforcer les pages locales de votre site et leur maillage interne.</li>
	<li>Publier régulièrement (photos, actualités) sur la fiche.</li>
</ol>

<h2>Questions fréquentes</h2>
<h3>Combien de temps pour apparaître dans le pack local ?</h3>
<p>Souvent plus vite que le SEO classique : une fiche bien optimisée avec quelques avis peut remonter en quelques semaines.</p>
<h3>Faut-il une adresse physique ?</h3>
<p>Pas toujours : une zone desservie peut suffire pour les activités qui se déplacent chez le client.</p>

<p>Nous intégrons le schema local et des pages géolocalisées à nos <a href="' . $creation . '">sites</a>, et nous vous accompagnons sur le <a href="' . $seo . '">référencement</a>. Pour un état des lieux, demandez un <a href="' . $audit . '">diagnostic gratuit</a>.</p>
';

	return $bank;
}

/**
 * Applique les contenus étoffés une seule fois par version.
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
	if ( (int) get_option( 'ag_longform_ver', 0 ) < AG_LONGFORM_VER ) {
		ag_longform_apply();
	}
}, 99 );
