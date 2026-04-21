=== AG Starter Companion ===

Contributors: adminag
Tags: starter sites, demo content, one click import, theme setup, french
Requires at least: 6.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.4.0
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Plugin compagnon pour les themes AG Starter (Restaurant, Artisan, Coach, Avocat). Importe en un clic les pages, le menu et les reglages pour un site pret a l'emploi, 100% en francais.

== Description ==

AG Starter Companion est le plugin compagnon officiel des themes gratuits AG Starter publies par AGthèmes (Alliance Group). Il apporte une fonctionnalite que les themes seuls ne peuvent pas offrir selon les regles WordPress.org : l'import automatise de contenu demo.

En un clic, le plugin cree pour vous :

* Les pages essentielles du theme actif (Accueil + 4 pages sectorielles adaptees : carte/reservation pour un restaurant, prestations/realisations pour un artisan, accompagnements/temoignages pour un coach).
* Un menu principal contenant toutes ces pages, automatiquement assigne a l'emplacement "primary" du theme.
* Le reglage "page d'accueil statique" utilisant la page "Accueil" (ce qui declenche le rendu du template front-page.php du theme).
* L'activation des permaliens au format /%postname%/ si ce n'etait pas deja le cas.

Tout est 100% local : aucune connexion internet n'est necessaire, aucun appel API externe, aucune donnee envoyee nulle part.

= Themes supportes =

* AG Starter Restaurant (https://wordpress.org/themes/ag-starter-restaurant/)
* AG Starter Artisan (https://wordpress.org/themes/ag-starter-artisan/)
* AG Starter Coach (https://wordpress.org/themes/ag-starter-coach/)
* AG Starter Avocat (https://wordpress.org/themes/ag-starter-avocat/) — cree aussi 6 Domaines d'expertise via le CPT ag_domaine

Le plugin detecte automatiquement quel theme AG Starter est actif et adapte le contenu importe en consequence. Si aucun theme AG Starter n'est actif, le plugin se met en veille et n'affiche rien.

= Pour qui ? =

Ce plugin est pense pour les personnes qui ne codent pas et qui veulent un site pret a l'emploi en quelques minutes apres l'installation de leur theme. Parfait pour :

* Un restaurateur qui veut lancer une vitrine en ligne sans agence
* Un artisan qui veut afficher ses prestations et zones d'intervention
* Un coach ou consultant qui veut presenter ses offres et prendre des rendez-vous

= Fonctionnalites =

* Import en un clic (un seul bouton dans Apparence > Configuration AG)
* Detection automatique du theme AG Starter actif
* Reinitialisation possible a tout moment
* Aucune creation de table SQL
* Conforme aux standards GPL v2+
* Prepare pour la traduction (text-domain ag-starter-companion)
* Aucune dependance a un autre plugin
* Aucun tracking, aucune publicite, aucun email collecte

== Installation ==

1. Installez l'un des themes AG Starter (Restaurant, Artisan, Coach ou Avocat) et activez-le.
2. Dans WordPress, allez dans Extensions > Ajouter et cherchez "AG Starter Companion".
3. Cliquez sur "Installer maintenant", puis "Activer".
4. Une notice apparait vous invitant a lancer la configuration. Cliquez dessus, ou allez directement dans Apparence > Configuration AG.
5. Cliquez sur le bouton "Importer le contenu demo". C'est tout.

Vous pouvez relancer l'import ou reinitialiser le contenu a tout moment depuis la meme page.

== Frequently Asked Questions ==

= Le plugin modifie-t-il mes donnees existantes ? =

Non. Si une page existe deja avec le meme slug (ex: "contact"), elle est conservee. Le plugin ne supprime rien sans confirmation explicite.

= Puis-je utiliser le plugin sans theme AG Starter ? =

Non. Le plugin ne s'active que si l'un des themes AG Starter est en cours d'utilisation. Sinon il n'affiche rien et reste dormant.

= L'import cree-t-il des articles de blog de demo ? =

Non. Uniquement les pages statiques necessaires + le menu. Vos articles existants sont preserves.

= Est-ce que le plugin se connecte a internet ? =

Non. Tout est 100% local. Aucun appel API, aucun fichier telecharge.

= Puis-je reinitialiser le contenu demo ? =

Oui. Un bouton "Reinitialiser" apparait apres le premier import. Il supprime les pages creees et le menu, et remet "Afficher les articles" comme page d'accueil.

= Le plugin collecte-t-il des donnees ? =

Non. Aucun tracking, aucun telemetry, aucun email. Le plugin est totalement statique.

= Sous quelle licence est-il publie ? =

GPL v2 ou ulterieure. Vous pouvez l'utiliser, le modifier et le redistribuer librement.

= Qui a cree ce plugin ? =

AGthèmes, la division theme d'Alliance Group (agence web et IA basee a Nantes, Naples et Marrakech). Plus d'infos sur https://alliancegroupe-inc.com

== Screenshots ==

1. La page d'administration Apparence > Configuration AG, avec le bouton d'import en un clic.
2. La notice d'accueil qui apparait apres l'activation du plugin.

== Changelog ==

= 1.2.0 =
* Ajout du support du theme AG Starter Avocat.
* Quand AG Starter Avocat est actif, l'importer cree automatiquement 6 Domaines d'expertise (Droit des affaires, Droit du travail, Droit de la famille, Droit immobilier, Droit penal, Droit fiscal) via le CPT ag_domaine, avec icones et exemples de cas.
* La reinitialisation supprime aussi les domaines de demo pour permettre une re-importation propre.

= 1.1.0 =
* Premier support du theme AG Starter Avocat (sans CPT au depart).
* Description du plugin mise a jour pour mentionner les 4 themes.

= 1.0.0 =
* Version initiale.
* Support des themes AG Starter Restaurant, Artisan et Coach.
* Import en un clic : 5 pages + menu + page d'accueil statique + permaliens.
* Reinitialisation possible.
* Detection automatique du theme actif.
* Prepare pour la traduction.

== Upgrade Notice ==

= 1.0.0 =
Premiere version publique. Installez et profitez de vos themes AG Starter en un clic.

== Credits ==

Plugin cree par AGthèmes (Alliance Group — https://alliancegroupe-inc.com).
Aucune dependance externe. Licence GPL v2 ou ulterieure.
