<?php
/**
 * LE CABINET — qui fait quoi, dans l'ordre
 * ---------------------------------------------------------------------------
 * Alliance Groupe travaille entre NAPLES et NANTES. Chaque etape du parcours
 * porte donc un nom et un bureau, comme dans un vrai cabinet : on ne dit plus
 * « le module ag-closer est arme », on dit « Hugo demarche ».
 *
 * Ce fichier ne fait RIEN tourner. C'est l'organigramme : une seule source
 * pour les noms, les postes et l'etat de chaque poste. Les ecrans s'en
 * servent pour parler la meme langue que Fabrice.
 *
 * ⚠️ Ces prenoms sont INTERNES. Les messages qui partent chez un prospect sont
 * signes « Alliance Groupe » et portent la mention qu'ils sont automatises.
 * Faire signer un courriel par un collaborateur qui n'existe pas, ce serait
 * mentir au client des la premiere phrase — et se le faire reprocher au
 * premier litige. Les noms servent a NOUS reperer, pas a habiller la machine.
 *
 * @package alliance-groupe-theme
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! defined( 'AG_EQUIPE_VER' ) ) { define( 'AG_EQUIPE_VER', '1.0.0' ); }

if ( ! function_exists( 'ag_equipe' ) ) {
	/**
	 * L'organigramme, dans l'ordre du parcours client.
	 *
	 * 'arme' est une fonction : l'etat est lu au moment ou on l'affiche, jamais
	 * mis en cache — un tableau de bord qui ment sur ce qui tourne est pire
	 * que pas de tableau de bord.
	 *
	 * @return array
	 */
	function ag_equipe() {
		return array(
			'prospection' => array(
				'prenom'  => 'Matteo',
				'poste'   => 'Prospection',
				'bureau'  => 'Naples',
				'mission' => 'Cherche les entreprises sans vrai site, les note, et remplit le fichier.',
				'ecran'   => 'admin.php?page=ag-prospects',
				'arme'    => function () { return (bool) get_option( 'ag_chasse_auto_on', 0 ); },
			),
			'demarchage' => array(
				'prenom'  => 'Hugo',
				'poste'   => 'Premier contact',
				'bureau'  => 'Nantes',
				'mission' => 'Ecrit le premier message et les relances, quatre etapes, puis s\'arrete.',
				'ecran'   => 'admin.php?page=ag-closer',
				'arme'    => function () { return function_exists( 'ag_closer_on' ) && ag_closer_on(); },
			),
			'reponses' => array(
				'prenom'  => 'Alessia',
				'poste'   => 'Reponses entrantes',
				'bureau'  => 'Naples',
				'mission' => 'Releve la boite, lit les reponses, comprend l\'intention, range le dossier.',
				'ecran'   => 'admin.php?page=ag-boite',
				/* Elle est en poste si on lui APPORTE le courrier : soit le site
				   releve la boite lui-meme, soit un service pousse les reponses
				   sur l'adresse technique. Sans l'un des deux, elle ne voit rien
				   et toute la suite du parcours reste a l'arret. */
				'arme'    => function () {
					return ( function_exists( 'ag_boite_on' ) && ag_boite_on() )
						|| '' !== trim( (string) get_option( 'ag_inbound_token', '' ) );
				},
			),
			'negociation' => array(
				'prenom'  => 'Enzo',
				'poste'   => 'Negociation',
				'bureau'  => 'Nantes',
				'mission' => 'Repond aux objections et au prix, dans des limites fixees d\'avance.',
				'ecran'   => 'admin.php?page=ag-negociateur',
				'arme'    => function () { return function_exists( 'ag_nego_on' ) && ag_nego_on(); },
			),
			'offre' => array(
				'prenom'  => 'Giulia',
				'poste'   => 'Offre et production',
				'bureau'  => 'Naples',
				'mission' => 'Compose le pack, ce qu\'il contient, le delai annonce.',
				'ecran'   => 'admin.php?page=ag-hub',
				'arme'    => function () { return function_exists( 'ag_sites_express_packs' ); },
			),
			'juridique' => array(
				'prenom'  => 'Camille',
				'poste'   => 'Juridique',
				'bureau'  => 'Nantes',
				'mission' => 'Redige le contrat, le relit, et BLOQUE tout ce qui promet un resultat.',
				'ecran'   => 'admin.php?page=ag-juriste',
				'arme'    => function () { return function_exists( 'ag_sign_pret' ) && ag_sign_pret(); },
			),
			'signature' => array(
				'prenom'  => 'Margot',
				'poste'   => 'Signature et archives',
				'bureau'  => 'Nantes',
				'mission' => 'Envoie a signer, verifie l\'identite par code, scelle la preuve.',
				'ecran'   => 'admin.php?page=ag-contrats',
				'arme'    => function () { return function_exists( 'ag_sign_pret' ) && ag_sign_pret(); },
			),
			'coach' => array(
				'prenom'  => 'Salvo',
				'poste'   => 'Coach de l\'equipe',
				'bureau'  => 'Naples',
				'mission' => 'Envoie chaque matin la feuille de route : qui relancer, quoi finir.',
				'ecran'   => 'admin.php?page=ag-hub',
				'arme'    => function () { return (bool) get_option( 'ag_coach_on', 0 ); },
			),
		);
	}
}

if ( ! function_exists( 'ag_equipe_nom' ) ) {
	/**
	 * Le nom d'un poste, pour l'ecrire dans un journal ou une alerte.
	 * Retourne « Hugo (Premier contact) » ou la cle si le poste est inconnu.
	 */
	function ag_equipe_nom( $cle, $avec_poste = true ) {
		$e = ag_equipe();
		if ( empty( $e[ $cle ] ) ) { return (string) $cle; }
		return $avec_poste
			? $e[ $cle ]['prenom'] . ' (' . $e[ $cle ]['poste'] . ')'
			: $e[ $cle ]['prenom'];
	}
}

/* ── L'ecran : le cabinet d'un seul coup d'oeil ──────────────────────── */

add_action( 'admin_menu', function () {
	add_submenu_page(
		'ag-hub', 'Le cabinet', '👥 Le cabinet',
		'manage_options', 'ag-equipe', 'ag_equipe_ecran'
	);
}, 29 );

if ( ! function_exists( 'ag_equipe_ecran' ) ) {
	function ag_equipe_ecran() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		$equipe = ag_equipe();
		?>
		<div class="wrap">
			<h1>👥 Le cabinet</h1>
			<p class="description" style="max-width:56em">
				Le parcours complet, de l'entreprise reperee au contrat scelle. Chaque poste est
				tenu par un module du site : quand un poste est <strong>au repos</strong>, l'etape
				n'est pas faite — personne ne la rattrape a sa place.
			</p>

			<table class="widefat striped" style="max-width:66em;margin-top:18px">
				<thead><tr>
					<th style="width:15%">Collaborateur</th>
					<th style="width:16%">Poste</th>
					<th style="width:10%">Bureau</th>
					<th>Ce qu'il fait</th>
					<th style="width:12%">Etat</th>
				</tr></thead>
				<tbody>
				<?php foreach ( $equipe as $cle => $c ) :
					$arme = is_callable( $c['arme'] ) ? (bool) call_user_func( $c['arme'] ) : false; ?>
					<tr>
						<td><strong style="font-size:15px"><?php echo esc_html( $c['prenom'] ); ?></strong></td>
						<td><a href="<?php echo esc_url( admin_url( $c['ecran'] ) ); ?>"><?php echo esc_html( $c['poste'] ); ?></a></td>
						<td><?php echo esc_html( $c['bureau'] ); ?></td>
						<td><?php echo esc_html( $c['mission'] ); ?></td>
						<td><?php echo $arme
							? '<span style="color:#1a7f37;font-weight:600">en poste</span>'
							: '<span style="color:#8a8a94">au repos</span>'; ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>

			<h2 style="margin-top:28px">Ce que le cabinet ne fait pas</h2>
			<ul style="max-width:56em;list-style:disc;padding-left:22px">
				<li><strong>Ces prenoms ne sortent pas d'ici.</strong> Les messages envoyes aux prospects
					sont signes « Alliance Groupe » et disent qu'ils sont automatises. Signer du nom
					d'un collaborateur qui n'existe pas, ce serait mentir des la premiere ligne.</li>
				<li><strong>Personne ne promet un resultat.</strong> Position sur Google, chiffre d'affaires
					multiplie : la liste est commune a Camille et a Enzo, et elle bloque, elle n'avertit pas.</li>
				<li><strong>Personne ne descend sous le plancher de prix</strong> que vous avez fixe, et
					personne n'invente une prestation qui n'est pas dans un pack.</li>
				<li><strong>Rien ne part a signer</strong> tant que le modele de contrat n'est pas coche
					comme relu par un professionnel du droit.</li>
			</ul>
		</div>
		<?php
	}
}
