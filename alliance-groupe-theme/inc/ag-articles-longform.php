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

define( 'AG_LONGFORM_VER', 4 );

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

	$sites    = esc_url( home_url( '/sites-express' ) );

	/* ───────────── Prix d’un site internet à Nantes ───────────── */
	$bank['prix-site-internet-nantes-2026'] = '
<p>« Combien coûte un site internet ? » C’est la première question de tout dirigeant — et la réponse honnête est : ça dépend. Mais « ça dépend » n’aide personne. Voici donc des fourchettes de prix réelles en 2026, ce qui les fait varier, et comment éviter les deux pièges classiques : payer trop cher, ou payer peu pour un site qui ne rapporte rien.</p>

<h2>Les fourchettes de prix réelles en 2026</h2>
<ul>
	<li><strong>Site vitrine (3 à 8 pages)</strong> : de 1 500 à 4 000 € pour un travail sur-mesure et soigné. En dessous, on est souvent sur un template peu personnalisé.</li>
	<li><strong>Site vitrine premium / plus de pages</strong> : 4 000 à 8 000 € (design poussé, rédaction, SEO de départ).</li>
	<li><strong>E-commerce</strong> : à partir de 4 000 € et jusqu’à 15 000 € et plus selon le nombre de produits, les paiements, la logistique.</li>
	<li><strong>Solutions « à 500 € »</strong> : possibles via des packs standardisés — utiles pour démarrer vite, à condition d’en connaître les limites.</li>
</ul>
<p>À Nantes comme ailleurs, l’écart de prix ne reflète pas un caprice : il reflète le <strong>temps</strong>, l’<strong>expertise</strong> et le <strong>sur-mesure</strong>.</p>

<h2>Ce qui fait varier le prix</h2>
<ul>
	<li><strong>Sur-mesure ou template</strong> : un design unique demande plus de travail qu’un thème adapté.</li>
	<li><strong>Le nombre de pages</strong> et la complexité (espace membre, réservation, paiement…).</li>
	<li><strong>La rédaction et les visuels</strong> : un site sans contenu de qualité ne convertit pas.</li>
	<li><strong>Le SEO de départ</strong> : structure, balises, vitesse, données structurées.</li>
	<li><strong>L’accompagnement</strong> : formation, maintenance, sécurité, sauvegardes.</li>
</ul>

<h2>Attention au « pas cher » qui coûte cher</h2>
<p>Un site à bas prix qui n’est ni trouvé sur Google, ni pensé pour convertir, ni sécurisé, finit par coûter plus cher : refonte anticipée, clients perdus, piratage. À l’inverse, payer 10 000 € pour une vitrine de 5 pages est excessif. Le bon curseur : un site <strong>proportionné à vos objectifs</strong>, qui vous rapporte plus qu’il ne coûte.</p>

<h2>Abonnement ou paiement unique ?</h2>
<p>Deux modèles coexistent : l’achat (vous payez une fois, le site vous appartient) et l’abonnement (mensualité incluant hébergement, maintenance, sécurité). L’abonnement lisse le budget et garantit un site tenu à jour ; l’achat revient moins cher sur la durée si vous gérez la maintenance. Le mauvais choix, c’est l’achat « one-shot » <em>sans aucune</em> maintenance derrière.</p>

<h2>Questions fréquentes</h2>
<h3>Un site est-il vraiment rentable pour une petite entreprise ?</h3>
<p>Oui, s’il est trouvé et s’il convertit : un seul client gagné rembourse souvent plusieurs mois d’investissement. Un site invisible, lui, ne rapporte rien — quel que soit son prix.</p>
<h3>Y a-t-il des coûts récurrents ?</h3>
<p>Oui : nom de domaine, hébergement, et idéalement maintenance/sécurité. Comptez ces postes dès le départ.</p>

<p>Découvrez nos <a href="' . $sites . '">formules claires</a> et nos <a href="' . $realis . '">réalisations</a>, ou demandez un <a href="' . $audit . '">devis gratuit</a> adapté à votre projet.</p>
';

	/* ───────────── Prix d’un site professionnel ───────────── */
	$bank['prix-site-internet-professionnel-2026'] = '
<p>Un site « professionnel » n’est pas juste un site plus cher : c’est un site pensé comme un <strong>outil commercial</strong>, pas comme une carte de visite en ligne. Voici ce que vous payez réellement quand vous investissez dans un site professionnel en 2026, et pourquoi l’écart de prix avec un site « fait maison » est justifié.</p>

<h2>Ce que « professionnel » veut dire</h2>
<p>Un site professionnel réunit quatre qualités qu’un site amateur néglige presque toujours : il est <strong>trouvé</strong> (SEO), il <strong>convertit</strong> (parcours pensé pour l’action), il est <strong>rapide et sécurisé</strong>, et il <strong>reflète</strong> votre image avec un design sur-mesure. Chacune demande un vrai travail.</p>

<h2>Fourchettes 2026</h2>
<ul>
	<li><strong>Site pro vitrine</strong> : 2 000 à 5 000 € selon le sur-mesure et le contenu.</li>
	<li><strong>Site pro avec fonctionnalités</strong> (réservation, espace client, multilingue) : 5 000 à 10 000 €.</li>
	<li><strong>E-commerce professionnel</strong> : 5 000 à 20 000 € selon l’ampleur.</li>
</ul>

<h2>Le détail de la facture</h2>
<p>Quand un prestataire sérieux chiffre un site, il facture surtout du temps humain : cadrage et stratégie, design, intégration, rédaction et SEO, tests, formation. Les logiciels sont souvent gratuits (WordPress) ; ce que vous payez, c’est l’<strong>expertise</strong> qui les met au service de votre business.</p>

<h2>Pourquoi c’est un investissement, pas une dépense</h2>
<p>Un site pro bien conçu génère des demandes pendant que vous travaillez, rassure vos prospects, et vous fait gagner du temps (devis, prise de rendez-vous, réponses aux questions fréquentes). Comparez son coût non pas à un site gratuit, mais à ce qu’un <em>commercial</em> vous coûterait pour le même résultat.</p>

<h2>Les signaux d’un devis sérieux</h2>
<ul>
	<li>Il pose des questions sur vos objectifs avant de chiffrer.</li>
	<li>Il inclut le SEO, la sécurité et la formation, pas seulement le « design ».</li>
	<li>Il précise ce qui vous appartient (le site est-il vraiment à vous ?).</li>
	<li>Il prévoit la maintenance et les sauvegardes.</li>
</ul>

<h2>Questions fréquentes</h2>
<h3>Pourquoi un tel écart entre deux devis ?</h3>
<p>Parce qu’un devis « site 5 pages » peut recouvrir un template livré brut… ou un site sur-mesure optimisé et accompagné. Comparez le contenu, pas seulement le chiffre.</p>
<h3>Le moins cher est-il un mauvais choix ?</h3>
<p>Pas forcément pour démarrer. Mais assurez-vous qu’il n’oublie ni le SEO, ni la sécurité, ni la possibilité d’évoluer.</p>

<p>Chez Alliance Groupe, nous créons des <a href="' . $creation . '">sites professionnels sur-mesure</a> pensés pour convertir. Demandez un <a href="' . $audit . '">devis gratuit</a> transparent.</p>
';

	/* ───────────── Prix site artisan ───────────── */
	$bank['site-internet-artisan-prix'] = '
<p>Plombier, électricien, menuisier, paysagiste, coiffeur… pour un artisan, un site internet n’est pas un luxe : c’est souvent le premier réflexe d’un client qui cherche « [métier] près de chez moi ». Alors combien ça coûte, et surtout, comment être sûr que ça rapporte ? Voici l’essentiel, sans blabla.</p>

<h2>Combien coûte un site pour un artisan ?</h2>
<ul>
	<li><strong>Site vitrine simple</strong> (présentation, services, zone d’intervention, contact) : 1 000 à 3 000 €.</li>
	<li><strong>Site vitrine + devis en ligne / galerie de réalisations</strong> : 2 500 à 4 500 €.</li>
	<li><strong>Formules packagées à partir de ~500 €</strong> : utiles pour démarrer vite avec l’essentiel.</li>
</ul>
<p>Pour un artisan, l’objectif n’est pas un site « vitrine à tout faire » mais un site <strong>local et efficace</strong> : être trouvé sur sa zone et déclencher l’appel.</p>

<h2>Ce qui compte vraiment pour un artisan</h2>
<ul>
	<li><strong>Le référencement local</strong> : apparaître pour « [métier] + votre ville » et sur Google Maps (voir <a href="' . home_url( '/seo-local-google-maps-nantes' ) . '">notre guide SEO local</a>).</li>
	<li><strong>Le téléphone bien visible</strong>, cliquable sur mobile, en haut de chaque page.</li>
	<li><strong>Des photos de vos réalisations</strong> : rien ne rassure plus qu’un travail montré.</li>
	<li><strong>Les avis clients</strong> mis en avant.</li>
	<li><strong>La zone d’intervention</strong> clairement indiquée.</li>
</ul>

<h2>Le vrai retour sur investissement</h2>
<p>Un artisan facture souvent plusieurs centaines d’euros par intervention. Si le site apporte ne serait-ce qu’un ou deux chantiers par mois, il est rentabilisé en quelques semaines. Le calcul n’est pas « combien coûte le site » mais « combien de clients il me ramène ».</p>

<h2>Les erreurs à éviter</h2>
<ul>
	<li>Un site joli mais introuvable sur Google.</li>
	<li>Pas de numéro cliquable sur mobile (la majorité de vos visiteurs).</li>
	<li>Zéro photo de chantier.</li>
	<li>Un site jamais mis à jour ni sécurisé.</li>
</ul>

<h2>Questions fréquentes</h2>
<h3>Un artisan a-t-il vraiment besoin d’un site avec Google ?</h3>
<p>Une fiche Google est indispensable, mais un site renforce votre crédibilité, capte les recherches et vous rend moins dépendant des annuaires payants.</p>
<h3>Puis-je commencer petit ?</h3>
<p>Oui : une formule d’entrée bien faite (local + photos + contact) suffit pour démarrer, et évoluera avec votre activité.</p>

<p>Voir nos <a href="' . $sites . '">formules</a> pensées pour les artisans, ou demander un <a href="' . $audit . '">devis gratuit</a>.</p>
';

	/* ───────────── PME sans site perdent des clients ───────────── */
	$bank['pme-sans-site-web-perdent-clients'] = '
<p>« Je n’ai pas besoin de site, mes clients viennent par le bouche-à-oreille. » On l’entend souvent. Le problème : le bouche-à-oreille lui-même passe désormais par Google. Quand on vous recommande, la première chose que fait votre futur client, c’est <strong>vous chercher en ligne</strong>. S’il ne trouve rien — ou pire, un profil fantôme — le doute s’installe. Voici ce qu’une PME perd réellement sans site.</p>

<h2>1. Vous perdez les clients qui vous cherchent</h2>
<p>Même recommandé, un prospect vérifie : avis, réalisations, services, sérieux. Sans site, vous laissez cette vérification au hasard — ou à vos concurrents mieux référencés.</p>

<h2>2. Vous êtes invisible sur les recherches « métier + ville »</h2>
<p>Chaque jour, des gens cherchent exactement ce que vous proposez, près de chez eux. Sans présence en ligne optimisée, ces recherches profitent à d’autres. C’est du chiffre d’affaires qui part ailleurs, en silence.</p>

<h2>3. Vous paraissez moins crédible</h2>
<p>À prestation égale, l’entreprise avec un site soigné inspire davantage confiance. L’absence de site (ou un site daté) envoie le signal inverse — souvent injustement.</p>

<h2>4. Vous dépendez des plateformes</h2>
<p>Sans site, votre visibilité repose sur des annuaires ou réseaux qui changent leurs règles, prennent des commissions, ou vous noient parmi les concurrents. Un site, c’est le seul actif digital que <strong>vous</strong> possédez.</p>

<h2>5. Vous travaillez plus pour rien</h2>
<p>Répondre dix fois aux mêmes questions (horaires, tarifs, zone, services) prend du temps. Un site répond à votre place, 24h/24, et pré-qualifie les demandes.</p>

<h2>« Mais je n’ai pas le temps / le budget »</h2>
<p>C’est justement l’intérêt : un site bien fait <em>vous fait gagner</em> du temps et s’autofinance dès les premiers clients qu’il rapporte. Et démarrer ne coûte pas une fortune — voir nos <a href="' . $sites . '">formules</a>.</p>

<h2>Questions fréquentes</h2>
<h3>Les réseaux sociaux ne suffisent-ils pas ?</h3>
<p>Ils sont utiles mais vous ne les possédez pas, et ils ressortent mal sur les recherches Google. Le duo gagnant : un site (que vous possédez) + une fiche Google + des réseaux.</p>
<h3>Par où commencer ?</h3>
<p>Un site vitrine local simple, une fiche Google, et de vrais avis. C’est suffisant pour arrêter de perdre des clients.</p>

<p>Faites le point avec un <a href="' . $audit . '">diagnostic gratuit</a>, ou découvrez comment nous <a href="' . $creation . '">créons des sites qui rapportent</a>.</p>
';

	/* ───────────── Site qui ne génère aucun lead ───────────── */
	$bank['site-web-ne-genere-aucun-lead-raisons-solutions'] = '
<p>Vous avez un site, il est même plutôt joli — mais il ne génère aucun appel, aucun formulaire, aucun devis. Frustrant, et surtout coûteux. La bonne nouvelle : un site qui ne convertit pas souffre presque toujours des mêmes causes, toutes corrigeables. Passons-les en revue.</p>

<h2>1. Personne ne le trouve</h2>
<p>La cause n°1. Un site invisible sur Google ne peut pas générer de leads, aussi beau soit-il. Vérifiez votre <a href="' . $seo . '">référencement</a> : ciblez-vous les bons mots-clés ? Vos pages sont-elles indexées ? Êtes-vous présent sur les recherches locales ?</p>

<h2>2. Le message n’est pas clair</h2>
<p>En 5 secondes, un visiteur doit comprendre : ce que vous faites, pour qui, et quoi faire ensuite. Un site qui parle de vous (« Bienvenue sur notre site ») plutôt que du besoin du client fait fuir. Mettez le bénéfice client en avant, dès le haut de page.</p>

<h2>3. Aucun appel à l’action clair</h2>
<p>Où doit cliquer le visiteur ? Si la réponse n’est pas évidente sur chaque page (« Demander un devis », « Appeler », « Réserver »), il ne fera rien. Un bouton visible, répété, contrasté, change tout.</p>

<h2>4. Trop de friction</h2>
<p>Un formulaire de 12 champs, un numéro non cliquable, un temps de chargement de 6 secondes : chaque obstacle fait perdre des prospects. Simplifiez : moins de champs, contact en un clic, site rapide sur mobile.</p>

<h2>5. Pas de preuve, pas de confiance</h2>
<p>Sans avis, sans réalisations, sans éléments rassurants, le visiteur hésite. Ajoutez des témoignages, des <a href="' . $realis . '">réalisations</a>, des logos clients, des garanties.</p>

<h2>6. Le site n’est pas pensé pour convertir</h2>
<p>Beaucoup de sites sont conçus comme des brochures, pas comme des machines à générer des contacts. La structure elle-même doit guider vers l’action — c’est un choix de conception, dès le départ.</p>

<h2>Le diagnostic en 3 questions</h2>
<ol>
	<li>Combien de visiteurs par mois ? (Si très peu → problème de visibilité / SEO.)</li>
	<li>Beaucoup de visiteurs mais peu de contacts ? (→ problème de message / conversion.)</li>
	<li>Le site est-il rapide et clair sur mobile ? (→ souvent la fuite silencieuse.)</li>
</ol>

<h2>Questions fréquentes</h2>
<h3>Faut-il refaire tout le site ?</h3>
<p>Pas toujours. Parfois quelques ajustements (message, appels à l’action, vitesse, SEO) suffisent. Un diagnostic tranche.</p>
<h3>Combien de temps pour voir des résultats ?</h3>
<p>Les corrections de conversion se voient vite ; la visibilité SEO se construit sur quelques mois.</p>

<p>Envie de savoir pourquoi <em>votre</em> site ne convertit pas ? Demandez un <a href="' . $audit . '">diagnostic gratuit</a> : on identifie les blocages concrets et les priorités.</p>
';

	/* ───────────── Automatisation : gagner 15 h/semaine ───────────── */
	$bank['automatisation-gagner-15-heures-semaine-ia'] = '
<p>Devis, relances, réponses aux mêmes questions, prise de rendez-vous, saisie… Une part énorme du temps d’un dirigeant part dans des tâches répétitives à faible valeur. L’automatisation et l’IA permettent d’en récupérer une grande partie — jusqu’à une quinzaine d’heures par semaine pour certaines activités. Voici où et comment, concrètement.</p>

<h2>Ce que l’automatisation peut prendre en charge</h2>
<ul>
	<li><strong>Réponses aux questions fréquentes</strong> : un assistant sur votre site répond 24h/24 (horaires, tarifs, services) et pré-qualifie les demandes.</li>
	<li><strong>Prise de rendez-vous</strong> : un agenda en ligne évite les allers-retours d’emails.</li>
	<li><strong>Devis</strong> : un formulaire intelligent génère un premier chiffrage instantané.</li>
	<li><strong>Relances</strong> : emails automatiques après un devis, un achat, ou un panier abandonné.</li>
	<li><strong>Saisie et synchronisation</strong> : les données passent d’un outil à l’autre sans copier-coller.</li>
</ul>

<h2>Où l’IA change vraiment la donne</h2>
<p>Au-delà des automatisations classiques, l’IA rédige des brouillons (emails, fiches produits, publications), résume des documents, trie et priorise les demandes, et personnalise les réponses. Le gain n’est pas de « remplacer » l’humain, mais de lui retirer le travail mécanique pour le concentrer sur la relation client et le métier. Découvrez notre <a href="' . home_url( '/service-ia' ) . '">accompagnement IA</a>.</p>

<h2>Par où commencer sans se disperser</h2>
<ol>
	<li>Listez vos tâches répétitives d’une semaine et le temps qu’elles prennent.</li>
	<li>Repérez les 2-3 plus chronophages et automatisables.</li>
	<li>Commencez par une seule automatisation, mesurez le temps gagné.</li>
	<li>Étendez progressivement, en gardant un contrôle humain sur les points sensibles.</li>
</ol>

<h2>Les pièges à éviter</h2>
<ul>
	<li>Vouloir tout automatiser d’un coup.</li>
	<li>Automatiser un processus… déjà mauvais (corrigez-le d’abord).</li>
	<li>Supprimer tout contact humain là où il fait la différence.</li>
</ul>

<h2>Questions fréquentes</h2>
<h3>Est-ce réservé aux grandes entreprises ?</h3>
<p>Non, au contraire : une PME, où le dirigeant fait tout, gagne proportionnellement le plus de temps.</p>
<h3>Est-ce que ça déshumanise la relation client ?</h3>
<p>Bien fait, c’est l’inverse : en déléguant le répétitif, vous avez plus de temps pour vos clients.</p>

<p>Envie de savoir ce qui est automatisable chez vous ? <a href="' . $contact . '">Parlons-en</a> — on identifie ensemble vos premières heures gagnées.</p>
';

	$ia_srv   = esc_url( home_url( '/service-ia' ) );

	/* ───────────── WordPress est-il sécurisé ? ───────────── */
	$bank['wordpress-est-il-securise'] = '
<p>WordPress fait tourner plus de 40 % du web — ce qui en fait aussi une cible privilégiée. D’où la question légitime : est-il vraiment sûr ? La réponse courte : <strong>oui, s’il est bien configuré et entretenu</strong>. WordPress n’est pas « non sécurisé » ; ce sont les sites <em>mal entretenus</em> qui se font pirater. Voici comment se situer du bon côté.</p>

<h2>Pourquoi les sites WordPress se font pirater</h2>
<p>Dans l’immense majorité des cas, ce n’est pas le cœur de WordPress qui est en cause, mais son environnement : une extension ou un thème obsolète, un mot de passe faible, l’absence de pare-feu ou de sauvegarde. Les attaques sont d’ailleurs presque toujours <strong>automatisées</strong> : des robots scannent le web à la recherche de failles connues. Un site à jour passe sous leur radar.</p>

<h2>Les 5 failles les plus courantes</h2>
<ul>
	<li><strong>Extensions / thèmes non mis à jour</strong> : la porte d’entrée n°1.</li>
	<li><strong>Mots de passe faibles</strong> et absence de double authentification (2FA).</li>
	<li><strong>Page de connexion exposée</strong>, vulnérable aux attaques par force brute.</li>
	<li><strong>Pas de pare-feu applicatif (WAF)</strong> pour filtrer le trafic malveillant.</li>
	<li><strong>Aucune sauvegarde</strong> testée pour restaurer après incident.</li>
</ul>

<h2>Comment blinder un site WordPress</h2>
<ol>
	<li><strong>Mises à jour régulières</strong> du cœur, des extensions et du thème.</li>
	<li><strong>Authentification à deux facteurs</strong> sur tous les comptes administrateurs.</li>
	<li><strong>Limitation des tentatives de connexion</strong> et masquage / protection de la page de login.</li>
	<li><strong>Pare-feu et surveillance</strong> pour bloquer les attaques connues.</li>
	<li><strong>Sauvegardes automatiques testées</strong> — la règle <a href="' . home_url( '/sauvegardes-regle-3-2-1' ) . '">3-2-1</a> est votre filet.</li>
	<li><strong>Droits d’accès maîtrisés</strong> : un compte = une personne, le minimum de privilèges.</li>
	<li><strong>HTTPS</strong> partout et hébergement de qualité.</li>
</ol>
<p>Attention aussi au <a href="' . home_url( '/phishing-proteger-pme' ) . '">phishing</a> : la meilleure sécurité technique ne protège pas d’un mot de passe donné à un faux email.</p>

<h2>Questions fréquentes</h2>
<h3>WordPress est-il moins sûr qu’un site sur-mesure ?</h3>
<p>Non. Un WordPress bien tenu est très sûr ; sa popularité le rend simplement plus visé, donc plus exigeant en entretien.</p>
<h3>Un petit site vitrine est-il vraiment ciblé ?</h3>
<p>Oui, car les attaques sont automatisées et ne visent personne en particulier : elles cherchent des failles, pas des victimes précises.</p>

<p>Vous voulez savoir où en est votre site ? Notre <a href="' . $audit . '">diagnostic gratuit</a> détecte ces points en une minute. Et chaque <a href="' . $creation . '">site que nous créons</a> est livré sécurisé.</p>
';

	/* ───────────── Comment savoir si mon site est sécurisé ───────────── */
	$bank['site-web-securise-comment-savoir'] = '
<p>Comment savoir si votre site web est réellement sécurisé, sans être informaticien ? Bonne nouvelle : quelques vérifications simples donnent déjà une image fiable. Voici la checklist que vous pouvez faire vous-même en dix minutes, et les signaux qui doivent alerter.</p>

<h2>1. Le cadenas HTTPS</h2>
<p>Dans la barre d’adresse, votre site doit afficher <strong>https://</strong> (et non http://). Sans HTTPS, les données transitent en clair et Google signale le site comme « non sécurisé » — un repoussoir immédiat pour vos visiteurs. C’est le minimum absolu.</p>

<h2>2. Les mises à jour</h2>
<p>Un site (surtout WordPress) doit être tenu à jour : cœur, thème, extensions. Un site qui n’a pas été mis à jour depuis des mois accumule des failles connues. Si vous ne savez pas quand a eu lieu la dernière mise à jour, c’est déjà un signal.</p>

<h2>3. Les sauvegardes</h2>
<p>Posez-vous une question simple : « si mon site disparaît demain, ai-je une copie récente ailleurs ? » Si la réponse n’est pas un « oui » certain, vous n’êtes pas protégé. Voir la règle <a href="' . home_url( '/sauvegardes-regle-3-2-1' ) . '">3-2-1</a>.</p>

<h2>4. Les mots de passe et les accès</h2>
<p>Mots de passe forts et uniques, double authentification activée, et un compte administrateur par personne. « admin / admin123 » partagé par toute l’équipe est une invitation au piratage.</p>

<h2>5. Les signaux d’alerte</h2>
<ul>
	<li>Google affiche « Site peut-être piraté » ou « non sécurisé ».</li>
	<li>Des redirections étranges, du contenu que vous n’avez pas publié.</li>
	<li>Le site est anormalement lent ou consomme des ressources.</li>
	<li>Des emails d’avertissement de votre hébergeur.</li>
</ul>

<h2>Aller plus loin : l’audit</h2>
<p>Ces vérifications donnent une première image, mais certaines failles ne se voient pas à l’œil nu (version obsolète, absence de pare-feu, configuration serveur). Un <a href="' . $audit . '">diagnostic de sécurité</a> automatisé détecte ces points en profondeur et vous donne un score clair.</p>

<h2>Questions fréquentes</h2>
<h3>Le cadenas HTTPS suffit-il à dire qu’un site est sûr ?</h3>
<p>Non : il chiffre la connexion, mais ne dit rien des mises à jour, mots de passe ou sauvegardes. C’est nécessaire, pas suffisant.</p>
<h3>À quelle fréquence vérifier ?</h3>
<p>Un contrôle rapide chaque mois, et une vraie vérification après tout changement important.</p>

<p>Pour un état des lieux complet et gratuit, lancez notre <a href="' . $audit . '">test de site</a> — vous obtenez un score et les points à corriger. Plus de détails dans notre guide <a href="' . $wp_secu . '">WordPress est-il sécurisé ?</a>.</p>
';

	/* ───────────── Concurrents qui volent vos clients sur Google ───────────── */
	$bank['seo-local-concurrents-volent-clients-google'] = '
<p>Vos concurrents apparaissent en haut de Google quand un client cherche votre métier dans votre ville — et vous, non. Résultat : ils captent des clients qui auraient pu être les vôtres. Ce n’est pas une fatalité, ni forcément une question de budget. Voici pourquoi ils passent devant, et comment reprendre l’avantage.</p>

<h2>Pourquoi vos concurrents ressortent (et pas vous)</h2>
<ul>
	<li><strong>Une fiche Google Business Profile mieux optimisée</strong> : plus complète, avec plus de photos et surtout plus d’avis.</li>
	<li><strong>Plus d’avis clients</strong>, plus récents, et auxquels ils répondent.</li>
	<li><strong>Un site pensé pour le local</strong> : pages par ville/métier, cohérence NAP, données structurées.</li>
	<li><strong>De l’ancienneté et des liens</strong> : ils sont installés depuis plus longtemps.</li>
</ul>
<p>Bonne nouvelle : les trois premiers points se rattrapent vite, indépendamment de l’ancienneté.</p>

<h2>Le plan de contre-attaque</h2>
<h3>1. Reprendre la main sur votre fiche Google</h3>
<p>Complétez-la à 100 % : catégorie précise, description, horaires, zone, photos régulières. Une fiche active est favorisée. (Voir notre <a href="' . home_url( '/seo-local-google-maps-nantes' ) . '">guide du pack local</a>.)</p>
<h3>2. Lancer une machine à avis</h3>
<p>Les avis pèsent près de la moitié du classement local. Demandez systématiquement un avis à chaque client satisfait (lien direct, QR code) et répondez à tous. C’est le levier le plus rapide pour dépasser un concurrent.</p>
<h3>3. Renforcer votre site sur le local</h3>
<p>Créez de vraies pages « métier + ville », affichez votre NAP partout de façon cohérente, et intégrez le schema LocalBusiness. Un <a href="' . $seo . '">accompagnement SEO</a> structure tout ça.</p>
<h3>4. Surveiller la concurrence</h3>
<p>Regardez ce que font les mieux classés : combien d’avis, quelles pages, quels mots-clés. Vous saurez exactement l’écart à combler.</p>

<h2>Combien de temps pour les rattraper ?</h2>
<p>Sur le pack local, une fiche optimisée et une dizaine d’avis peuvent vous faire remonter en quelques semaines. Le référencement organique classique demande plus de patience, mais le local est souvent le plus rapide à bouger.</p>

<h2>Questions fréquentes</h2>
<h3>Faut-il un gros budget pour dépasser un concurrent local ?</h3>
<p>Non. La fiche Google et les avis sont gratuits ; c’est surtout une question de méthode et de régularité.</p>
<h3>Et si le concurrent triche avec de faux avis ?</h3>
<p>Signalez les avis manifestement faux, et concentrez-vous sur vos vrais avis : sur la durée, l’authenticité gagne.</p>

<p>Envie de savoir pourquoi vos concurrents passent devant ? Demandez un <a href="' . $audit . '">diagnostic gratuit</a> : on compare votre présence à la leur et on liste les priorités.</p>
';

	/* ───────────── Template ou sur-mesure ───────────── */
	$bank['template-wordpress-ou-sur-mesure'] = '
<p>Faut-il partir d’un template WordPress tout prêt, ou faire créer un site sur-mesure ? C’est l’une des premières décisions — et elle engage le budget, le délai et les résultats. Il n’y a pas de mauvais choix dans l’absolu, seulement un choix adapté (ou non) à votre situation. Voici comment trancher.</p>

<h2>Le template : rapide et économique</h2>
<p><strong>Avantages</strong> : coût réduit, mise en ligne rapide, aperçu immédiat du rendu. Idéal pour démarrer vite, tester une activité, ou quand le budget est serré.</p>
<p><strong>Limites</strong> : personnalisation restreinte, design partagé avec d’autres sites, surcouche de fonctionnalités souvent lourde (donc plus lente), et adaptations parfois coûteuses quand on sort du cadre prévu.</p>

<h2>Le sur-mesure : unique et évolutif</h2>
<p><strong>Avantages</strong> : design à votre image, exactement les fonctionnalités utiles (ni plus ni moins), performances optimisées, et une base saine pour évoluer. Le site devient un vrai outil, pas un compromis.</p>
<p><strong>Limites</strong> : budget et délai plus élevés qu’un template brut.</p>

<h2>La 3ᵉ voie (souvent la meilleure)</h2>
<p>Entre les deux : une base solide <strong>fortement personnalisée</strong>, pensée pour la performance et le SEO. On garde l’efficacité d’une fondation éprouvée, avec un design et des fonctionnalités adaptés à votre métier. C’est l’approche que nous privilégions pour la plupart des PME : le meilleur rapport résultat/budget.</p>

<h2>Comment décider ? 4 questions</h2>
<ol>
	<li><strong>Votre image doit-elle se démarquer ?</strong> Si oui, évitez le template brut.</li>
	<li><strong>Avez-vous des besoins spécifiques</strong> (réservation, espace client, process métier) ? Le sur-mesure s’impose vite.</li>
	<li><strong>Quel horizon ?</strong> Un site amené à grandir mérite une base évolutive.</li>
	<li><strong>Quel budget / délai ?</strong> Pour démarrer très vite et pas cher, un template packagé peut suffire — en connaissant ses limites.</li>
</ol>

<h2>Questions fréquentes</h2>
<h3>Un template peut-il bien se référencer ?</h3>
<p>Oui s’il est léger et bien optimisé — mais beaucoup de templates « tout-en-un » sont lourds, ce qui pénalise la vitesse et le SEO.</p>
<h3>Peut-on commencer en template puis passer au sur-mesure ?</h3>
<p>Oui, c’est une stratégie valable : démarrer vite, puis investir une fois l’activité validée.</p>

<p>Pas sûr de votre choix ? Regardez nos <a href="' . $realis . '">réalisations</a> et parlons de votre projet : nous recommandons la solution <em>adaptée</em>, pas la plus chère. <a href="' . $creation . '">Découvrir notre approche</a>.</p>
';

	/* ───────────── Site pour coach sportif ───────────── */
	$bank['site-wordpress-coach-sportif-guide'] = '
<p>Coach sportif, personal trainer, prof de yoga ou de pilates : votre métier repose sur la confiance et la régularité. Un bon site web devient votre meilleur commercial — il attire des clients, remplit votre agenda et vous fait gagner du temps. Voici ce qu’un site de coach efficace doit contenir, et les erreurs à éviter.</p>

<h2>Ce qu’un client cherche sur le site d’un coach</h2>
<ul>
	<li><strong>Qui vous êtes</strong> : votre approche, vos spécialités, votre certification.</li>
	<li><strong>Des preuves</strong> : témoignages, transformations, avis — la confiance avant tout.</li>
	<li><strong>Vos offres et tarifs</strong> clairs (séance, pack, en ligne / en présentiel).</li>
	<li><strong>Comment démarrer</strong> : réserver une séance ou un appel découverte, facilement.</li>
</ul>

<h2>Les fonctionnalités qui font la différence</h2>
<ul>
	<li><strong>Réservation en ligne</strong> : l’agenda se remplit sans échanges d’emails.</li>
	<li><strong>Paiement en ligne</strong> pour les packs et abonnements.</li>
	<li><strong>Espace membre</strong> (optionnel) : programmes, vidéos, suivi.</li>
	<li><strong>Blog / conseils</strong> : attire du trafic via des recherches « comment… » et démontre votre expertise.</li>
	<li><strong>Intégration réseaux sociaux</strong> : Instagram est souvent votre vitrine, le site votre point de conversion.</li>
</ul>

<h2>Être trouvé localement</h2>
<p>La plupart de vos clients sont proches de vous. Optimisez votre présence locale : fiche Google, mots-clés « coach sportif + ville », avis clients. Un client qui cherche « coach sportif [votre ville] » doit vous trouver. (Voir notre <a href="' . home_url( '/seo-local-google-maps-nantes' ) . '">guide SEO local</a>.)</p>

<h2>Les erreurs fréquentes</h2>
<ul>
	<li>Un site qui parle de séances mais ne permet pas d’en réserver.</li>
	<li>Aucun témoignage ni preuve de résultats.</li>
	<li>Des tarifs cachés qui font fuir (ou au contraire, tout miser sur le prix).</li>
	<li>Un site illisible sur mobile, alors que c’est là que vos prospects vous découvrent.</li>
</ul>

<h2>Questions fréquentes</h2>
<h3>Instagram ne suffit-il pas ?</h3>
<p>Instagram attire, mais ne convertit pas aussi bien : pas de réservation fluide, pas de référencement Google, et vous ne le possédez pas. Le site est votre base.</p>
<h3>Faut-il proposer du coaching en ligne sur le site ?</h3>
<p>Si vous en faites, oui : c’est un revenu complémentaire sans limite géographique, facile à vendre via le site.</p>

<p>Envie d’un site qui remplit votre agenda ? Voir nos <a href="' . $realis . '">réalisations</a> ou demander un <a href="' . $audit . '">devis gratuit</a> adapté à votre activité de coach.</p>
';

	/* ───────────── Débuter avec l’IA : déléguer sa gestion web ───────────── */
	$bank['debutant-ia-deleguer-gestion-web-gain-temps'] = '
<p>Vous entendez parler d’intelligence artificielle partout, mais vous ne savez pas par où commencer ni si c’est « pour vous » ? Ce guide est fait pour les débutants : concrètement, l’IA peut déléguer une partie de la gestion de votre présence web et vous rendre des heures chaque semaine — sans compétence technique.</p>

<h2>L’IA, pour un débutant, c’est quoi au juste ?</h2>
<p>Oubliez la science-fiction. Au quotidien, l’IA est un <strong>assistant</strong> qui comprend le langage naturel : vous lui demandez quelque chose en français, il exécute. Rédiger un email, résumer un document, répondre à un client, proposer des idées de publications, trier des demandes… autant de tâches qu’il prend en charge, vous laissant le rôle de décideur.</p>

<h2>Ce que vous pouvez déléguer dès maintenant</h2>
<ul>
	<li><strong>Les réponses aux questions fréquentes</strong> de vos clients (via un assistant sur votre site).</li>
	<li><strong>La rédaction</strong> : brouillons d’emails, fiches produits, publications réseaux.</li>
	<li><strong>Le tri et la priorisation</strong> des demandes entrantes.</li>
	<li><strong>Les résumés</strong> de longs documents ou d’échanges.</li>
	<li><strong>La relance</strong> automatique après un devis ou un achat.</li>
</ul>
<p>Pour aller plus loin sur le temps gagné, voir notre article <a href="' . home_url( '/automatisation-gagner-15-heures-semaine-ia' ) . '">automatisation : gagner 15 h/semaine</a>.</p>

<h2>Comment démarrer sans se tromper</h2>
<ol>
	<li><strong>Choisissez une seule tâche</strong> chronophage et répétitive.</li>
	<li><strong>Testez sur cette tâche</strong> pendant une semaine, mesurez le temps gagné.</li>
	<li><strong>Gardez le contrôle</strong> : relisez avant d’envoyer, surtout au début.</li>
	<li><strong>Étendez</strong> progressivement à d’autres tâches.</li>
</ol>

<h2>Les craintes légitimes (et la réalité)</h2>
<ul>
	<li><strong>« Je vais perdre le contact humain »</strong> : au contraire, vous libérez du temps <em>pour</em> vos clients.</li>
	<li><strong>« C’est trop technique »</strong> : les outils modernes se pilotent en langage courant.</li>
	<li><strong>« Ça va dire des bêtises »</strong> : d’où l’importance de garder une validation humaine sur les points sensibles.</li>
</ul>

<h2>Questions fréquentes</h2>
<h3>Faut-il être une grande entreprise ?</h3>
<p>Non. Un indépendant ou une petite équipe, qui fait tout, gagne proportionnellement le plus.</p>
<h3>Combien ça coûte pour débuter ?</h3>
<p>On peut commencer avec des outils abordables, voire gratuits, sur une tâche ciblée. L’enjeu est la méthode, pas le budget.</p>

<p>Envie qu’on identifie ensemble votre première tâche à déléguer ? Découvrez notre <a href="' . $ia_srv . '">accompagnement IA</a> ou <a href="' . $contact . '">parlons-en</a>.</p>
';

	/* ───────────── IA & génération de leads ───────────── */
	$bank['ia-revolution-generation-leads-2025'] = '
<p>Générer des contacts qualifiés — des « leads » — est le nerf de la guerre de toute entreprise. L’intelligence artificielle change la donne : elle permet d’attirer, de qualifier et de relancer des prospects de façon plus rapide, plus personnalisée et à moindre coût. Voici comment, concrètement, sans hype.</p>

<h2>Attirer : être trouvé au bon moment</h2>
<p>L’IA aide à produire plus vite un contenu utile (articles, réponses aux questions que se posent vos clients), qui attire un trafic qualifié depuis Google. Elle aide aussi à repérer les sujets et mots-clés qui comptent pour votre audience. Résultat : plus de visiteurs réellement intéressés, sans y passer vos soirées.</p>

<h2>Convertir : l’assistant qui ne dort jamais</h2>
<p>Un assistant IA sur votre site répond aux visiteurs 24h/24, les oriente, et <strong>pré-qualifie</strong> les demandes : il pose les bonnes questions et ne vous transmet que les prospects sérieux, avec le contexte. Fini les formulaires qui refroidissent : la conversation engage davantage. (Si votre site ne convertit pas aujourd’hui, voir <a href="' . home_url( '/site-web-ne-genere-aucun-lead-raisons-solutions' ) . '">pourquoi un site ne génère aucun lead</a>.)</p>

<h2>Qualifier et prioriser</h2>
<p>Tous les leads ne se valent pas. L’IA note et trie les contacts selon leur intention et leur potentiel, pour que vous concentriez votre temps sur ceux qui sont prêts à acheter. Vous arrêtez de courir après des prospects tièdes.</p>

<h2>Relancer sans y penser</h2>
<p>La plupart des ventes se jouent dans le suivi. L’IA déclenche des relances personnalisées au bon moment (après un devis, une visite, un téléchargement), avec un message adapté à chaque situation — un travail impossible à tenir manuellement pour une petite équipe.</p>

<h2>Personnaliser à grande échelle</h2>
<p>Message générique = résultats génériques. L’IA adapte le discours au profil et au comportement de chaque prospect, ce qui augmente nettement les taux de réponse — tout en restant gérable côté temps.</p>

<h2>Par où commencer</h2>
<ol>
	<li>Installez un assistant qui pré-qualifie les demandes sur votre site.</li>
	<li>Mettez en place une relance automatique après devis.</li>
	<li>Mesurez : plus de leads ? mieux qualifiés ? puis étendez.</li>
</ol>

<h2>Questions fréquentes</h2>
<h3>L’IA remplace-t-elle le commercial ?</h3>
<p>Non : elle lui retire le travail répétitif (tri, relances) pour qu’il se concentre sur la relation et la vente.</p>
<h3>Est-ce réservé aux gros budgets ?</h3>
<p>Non : les briques abordables suffisent pour démarrer sur un seul cas d’usage à fort impact.</p>

<p>Envie de transformer votre site en machine à leads ? Découvrez notre <a href="' . $ia_srv . '">accompagnement IA</a> ou demandez un <a href="' . $audit . '">diagnostic gratuit</a>.</p>
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
