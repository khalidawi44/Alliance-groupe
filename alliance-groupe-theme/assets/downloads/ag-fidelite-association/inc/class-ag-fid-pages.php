<?php
/**
 * Crée les pages séparées par défaut à l'activation : manifeste,
 * combats, événements, groupes locaux, signer, don, espace adhérent,
 * adhérer, mentions légales, politique de confidentialité.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AG_Fid_Pages {
	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) self::$instance = new self();
		return self::$instance;
	}
	private function __construct() {
		// Rien à faire en runtime — la création des pages se fait à
		// l'activation du plugin (cf. register_activation_hook).
	}

	/**
	 * Wrappe un shortcode dans des blocs Gutenberg : heading editable +
	 * paragraphe d'intro editable + bloc shortcode. L'utilisateur peut
	 * modifier le titre et l'intro, le shortcode genere les cards CPT.
	 */
	private static function wrap_shortcode( $title, $intro, $shortcode ) {
		return "<!-- wp:heading {\"level\":1} -->\n<h1 class=\"wp-block-heading\">" . esc_html( $title ) . "</h1>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph -->\n<p>" . esc_html( $intro ) . "</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:shortcode -->\n" . $shortcode . "\n<!-- /wp:shortcode -->";
	}

	public static function create_default_pages() {
		$pages = array(
			'accueil'      => array( 'title' => 'Accueil',             'content' => '' ),
			'qui-sommes-nous' => array( 'title' => 'Qui sommes-nous',  'content' => self::wrap_shortcode( 'Qui sommes-nous', 'Présentez ici votre association : son histoire, ses valeurs, son équipe. Le bloc ci-dessous affiche dynamiquement votre identité.', '[ag_fid_qui_sommes_nous]' ) ),
			'reunion'      => array( 'title' => 'Réunion en ligne',    'content' => self::wrap_shortcode( 'Réunion en ligne', 'Rejoignez nos AG et réunions de bureau via la salle visio chiffrée ci-dessous (Jitsi Meet, sans compte).', '[ag_fid_visio]' ) ),
			'rendez-vous'  => array( 'title' => 'Prendre rendez-vous', 'content' => self::wrap_shortcode( 'Prendre rendez-vous', 'Choisissez un créneau dans le calendrier ci-dessous pour un échange en visio ou en présentiel.', '[ag_fid_rdv]' ) ),
			'manifeste'    => array( 'title' => 'Manifeste',           'content' => self::default_manifeste_content() ),
			'combats'      => array( 'title' => 'Nos combats',         'content' => self::wrap_shortcode( 'Nos combats', 'Découvrez les grandes campagnes que nous portons cette année, sur le terrain et dans les institutions. Modifiez ce texte et ajoutez vos combats via Combats > admin.', '[ag_fid_combats]' ) ),
			'evenements'   => array( 'title' => 'Événements',          'content' => self::wrap_shortcode( 'Événements', 'Marches, meetings, AG, débats publics — venez nous rencontrer. Ajoutez vos événements via Événements > admin.', '[ag_fid_evenements]' ) ),
			'groupes'      => array( 'title' => 'Groupes locaux',      'content' => self::wrap_shortcode( 'Groupes locaux', 'Trouvez le groupe local actif près de chez vous, ou créez le vôtre. Ajoutez vos groupes via Groupes locaux > admin.', '[ag_fid_groupes]' ) ),
			'actu'         => array( 'title' => 'Actualités',          'content' => self::wrap_shortcode( 'Actualités', 'Les dernières news du mouvement : analyses, communiqués, comptes rendus. Publiez via Articles > admin.', '[ag_fid_actu]' ) ),
			'signer'       => array( 'title' => 'Signer l\'appel',     'content' => self::wrap_shortcode( 'Signer l\'appel', 'Pour une société plus juste, écologique et démocratique. Signer, c\'est s\'engager à recevoir nos appels et à les relayer.', '[ag_fid_signer]' ) ),
			'petitions'    => array( 'title' => 'Pétitions',           'content' => self::wrap_shortcode( 'Pétitions', 'Découvrez nos pétitions en cours et leur progression. Ajoutez vos pétitions via Pétitions > admin.', '[ag_fid_petitions]' ) ),
			'don'          => array( 'title' => 'Faire un don',        'content' => self::wrap_shortcode( 'Faire un don', 'Indépendants des partis et des grands donateurs, nous ne tenons que par vous. 66% de votre don est déductible de vos impôts.', '[ag_fid_don]' ) ),
			'adherer'      => array( 'title' => 'Adhérer',             'content' => self::wrap_shortcode( 'Adhérer', 'Rejoignez le mouvement comme adhérent·e à jour de cotisation. Vous aurez accès à l\'AG, au vote des décisions et à l\'espace adhérent privé.', '[ag_fid_adhesion]' ) ),
			'mon-compte'   => array( 'title' => 'Mon espace',          'content' => self::wrap_shortcode( 'Mon espace', 'Espace réservé aux adhérent·es : PV des AG, ressources, badge numérique.', '[ag_fid_compte]' ) ),
			'mentions'     => array( 'title' => 'Mentions légales',    'content'  => '<!-- wp:html --><p><em>Cette page est générée automatiquement à partir des informations saisies dans <strong>Apparence → Personnaliser → Pack Fidélité</strong>. Vous pouvez aussi remplacer ce shortcode par votre propre texte.</em></p>[ag_fid_mentions]<!-- /wp:html -->' ),
			'rgpd'         => array( 'title' => 'Confidentialité',     'content'  => '<!-- wp:html --><p><em>Politique de confidentialité auto-générée. Modifiable directement ici (remplacez le shortcode ci-dessous par votre propre texte si besoin).</em></p>[ag_fid_rgpd]<!-- /wp:html -->' ),
			'statuts'      => array( 'title' => 'Statuts',             'content'  => self::default_statuts_content() ),
		);
		// Liste des pages dont le contenu legacy etait juste un shortcode brut.
		// Si la page existante contient EXACTEMENT le shortcode legacy (cas
		// installs anciennes), on migre vers le contenu Gutenberg riche
		// (heading editable + intro + shortcode). Si l'utilisateur a deja
		// modifie la page (autre contenu), on ne touche pas.
		$legacy_shortcodes = array(
			'qui-sommes-nous' => '[ag_fid_qui_sommes_nous]',
			'reunion'         => '[ag_fid_visio]',
			'rendez-vous'     => '[ag_fid_rdv]',
			'manifeste'       => '[ag_fid_manifeste]',
			'combats'         => '[ag_fid_combats]',
			'evenements'      => '[ag_fid_evenements]',
			'groupes'         => '[ag_fid_groupes]',
			'actu'            => '[ag_fid_actu]',
			'signer'          => '[ag_fid_signer]',
			'petitions'       => '[ag_fid_petitions]',
			'don'             => '[ag_fid_don]',
			'adherer'         => '[ag_fid_adhesion]',
			'mon-compte'      => '[ag_fid_compte]',
		);

		foreach ( $pages as $slug => $page ) {
			$existing = get_page_by_path( $slug );
			$content  = $page['content'];
			if ( $existing ) {
				// Reset force du contenu si placeholder admin detecte
				// (ancien message "Bienvenue sur notre site militant.")
				if ( $slug === 'accueil' && strpos( $existing->post_content, 'Cette zone est éditable depuis' ) !== false ) {
					wp_update_post( array( 'ID' => $existing->ID, 'post_content' => '' ) );
				}
				// Si on detecte les blocs Gutenberg auto-generes par les
				// versions 0.25.0/0.26.0 (qui changeaient l'apparence trop
				// fort), on les supprime pour revenir a l'apparence stylee
				// originale du theme (sections PHP). L'utilisateur peut
				// toujours editer la page si il le souhaite.
				if ( $slug === 'accueil' && strpos( $existing->post_content, 'Pour une société plus juste, écologique et démocratique' ) !== false && strpos( $existing->post_content, '<!-- wp:cover' ) !== false ) {
					wp_update_post( array( 'ID' => $existing->ID, 'post_content' => '' ) );
				}
				// Migration v0.30 : si la page contient EXACTEMENT le shortcode
				// legacy seul (pas modifiee par l'utilisateur), on la remplace
				// par du Gutenberg editable.
				if ( isset( $legacy_shortcodes[ $slug ] ) && trim( $existing->post_content ) === $legacy_shortcodes[ $slug ] ) {
					wp_update_post( array( 'ID' => $existing->ID, 'post_content' => $content ) );
				}
				continue;
			}
			wp_insert_post( array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => $page['title'],
				'post_name'    => $slug,
				'post_content' => $content,
			) );
		}
		// Definit auto la page 'accueil' comme page d'accueil du site
		$home = get_page_by_path( 'accueil' );
		if ( $home ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $home->ID );
		}
		self::create_default_menus();
		self::create_default_cpts();
	}

	/**
	 * Contenu par defaut de la page d'accueil, en blocs Gutenberg.
	 * L'utilisateur modifie tout via Pages > Accueil sans coder.
	 * Si cette page contient du contenu, front-page.php l'affiche
	 * a la place des sections PHP hardcodees.
	 */
	public static function default_home_content() {
		return <<<'HTML'
<!-- wp:cover {"overlayColor":"black","minHeight":420,"contentPosition":"center center","align":"full","style":{"spacing":{"padding":{"top":"60px","bottom":"60px"}}}} -->
<div class="wp-block-cover alignfull" style="padding-top:60px;padding-bottom:60px;min-height:420px"><span aria-hidden="true" class="wp-block-cover__background has-black-background-color has-background-dim-100 has-background-dim"></span><div class="wp-block-cover__inner-container">
<!-- wp:heading {"textAlign":"center","level":1,"style":{"typography":{"fontSize":"3rem","lineHeight":"1.1"},"color":{"text":"#ffffff"}}} -->
<h1 class="wp-block-heading has-text-align-center has-text-color" style="color:#ffffff;font-size:3rem;line-height:1.1">Pour une société plus juste, écologique et démocratique</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","style":{"color":{"text":"#ffffff"},"typography":{"fontSize":"1.25rem"}}} -->
<p class="has-text-align-center has-text-color" style="color:#ffffff;font-size:1.25rem">Notre mouvement rassemble des citoyennes et citoyens engagés pour transformer la société, pas à pas, ensemble.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"vivid-red","textColor":"white","style":{"border":{"radius":"8px"}}} -->
<div class="wp-block-button"><a class="wp-block-button__link has-white-color has-vivid-red-background-color has-text-color has-background wp-element-button" href="/signer/" style="border-radius:8px">Rejoindre le mouvement</a></div>
<!-- /wp:button -->

<!-- wp:button {"textColor":"white","style":{"border":{"radius":"8px","width":"2px"},"color":{"background":"transparent"}},"borderColor":"white"} -->
<div class="wp-block-button"><a class="wp-block-button__link has-white-color has-text-color has-background has-border-color has-white-border-color wp-element-button" href="/don/" style="border-width:2px;border-radius:8px;background-color:transparent">Faire un don</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div></div>
<!-- /wp:cover -->

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"80px","bottom":"80px","left":"24px","right":"24px"}}},"layout":{"type":"constrained","contentSize":"1100px"}} -->
<div class="wp-block-group alignfull" style="padding-top:80px;padding-right:24px;padding-bottom:80px;padding-left:24px">
<!-- wp:heading {"textAlign":"center","level":2,"style":{"typography":{"fontSize":"2.25rem"}}} -->
<h2 class="wp-block-heading has-text-align-center" style="font-size:2.25rem">Notre <em>manifeste</em></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"1.1rem"},"color":{"text":"#666"}}} -->
<p class="has-text-align-center has-text-color" style="color:#666;font-size:1.1rem">Nous croyons qu'une autre société est possible — plus juste, plus écologique, plus démocratique. Voici nos engagements.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Nous sommes des citoyennes et des citoyens. Pas un parti. Pas un syndicat. Un mouvement, ouvert et indépendant, qui croit qu'une autre société est possible.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>La République promet l'égalité. Elle livre la précarité. Les services publics sont méthodiquement démantelés. Le climat se dérègle. Nous voulons changer cela.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><strong>Indépendance, démocratie interne, action concrète, bienveillance</strong> : voilà nos principes. Nous ne vivons que des cotisations et des dons de nos adhérent·es.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"vivid-red","textColor":"white"} -->
<div class="wp-block-button"><a class="wp-block-button__link has-white-color has-vivid-red-background-color has-text-color has-background wp-element-button" href="/manifeste/">Lire le manifeste complet →</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","backgroundColor":"pale-pink","style":{"spacing":{"padding":{"top":"80px","bottom":"80px","left":"24px","right":"24px"}}},"layout":{"type":"constrained","contentSize":"1100px"}} -->
<div class="wp-block-group alignfull has-pale-pink-background-color has-background" style="padding-top:80px;padding-right:24px;padding-bottom:80px;padding-left:24px">
<!-- wp:heading {"textAlign":"center","level":2,"style":{"typography":{"fontSize":"2.25rem"}}} -->
<h2 class="wp-block-heading has-text-align-center" style="font-size:2.25rem">Nos <em>combats</em></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"1.1rem"},"color":{"text":"#666"}}} -->
<p class="has-text-align-center has-text-color" style="color:#666;font-size:1.1rem">Six grandes campagnes que nous portons cette année, sur le terrain et dans les institutions.</p>
<!-- /wp:paragraph -->

<!-- wp:columns -->
<div class="wp-block-columns">
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">🌍 Justice climatique</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Pour une transition écologique qui ne pèse pas sur les plus modestes.</p><!-- /wp:paragraph -->
</div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">🏠 Logement digne</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Plafonnement effectif des loyers, réquisition des logements vacants.</p><!-- /wp:paragraph -->
</div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">🏥 Service public fort</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Refonder l'hôpital, l'école, les transports publics.</p><!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->

<!-- wp:columns -->
<div class="wp-block-columns">
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">🗳️ Démocratie réelle</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>RIC, assemblées tirées au sort, reconnaissance du vote blanc.</p><!-- /wp:paragraph -->
</div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">🔍 Transparence publique</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Open data, traçabilité des marchés publics, registre des lobbies.</p><!-- /wp:paragraph -->
</div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">⚖️ Égalité réelle</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Lutte contre toutes les discriminations.</p><!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons">
<!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/combats/">Tous nos combats →</a></div><!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"80px","bottom":"80px","left":"24px","right":"24px"}}},"layout":{"type":"constrained","contentSize":"1100px"}} -->
<div class="wp-block-group alignfull" style="padding-top:80px;padding-right:24px;padding-bottom:80px;padding-left:24px">
<!-- wp:heading {"textAlign":"center","level":2,"style":{"typography":{"fontSize":"2.25rem"}}} -->
<h2 class="wp-block-heading has-text-align-center" style="font-size:2.25rem">Prochains <em>événements</em></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"1.1rem"},"color":{"text":"#666"}}} -->
<p class="has-text-align-center has-text-color" style="color:#666;font-size:1.1rem">Marches, meetings, AG, débats publics — venez nous rencontrer près de chez vous.</p>
<!-- /wp:paragraph -->

<!-- wp:shortcode -->
[ag_fid_evenements limit="3"]
<!-- /wp:shortcode -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons">
<!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/evenements/">📅 Tous les événements + calendrier</a></div><!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","backgroundColor":"pale-pink","style":{"spacing":{"padding":{"top":"80px","bottom":"80px","left":"24px","right":"24px"}}},"layout":{"type":"constrained","contentSize":"1100px"}} -->
<div class="wp-block-group alignfull has-pale-pink-background-color has-background" style="padding-top:80px;padding-right:24px;padding-bottom:80px;padding-left:24px">
<!-- wp:heading {"textAlign":"center","level":2,"style":{"typography":{"fontSize":"2.25rem"}}} -->
<h2 class="wp-block-heading has-text-align-center" style="font-size:2.25rem">Trouver mon <em>groupe local</em></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">47 groupes locaux actifs partout en France. Pas de groupe près de chez vous ? <a href="/groupes/">Créez le vôtre</a> — nous vous accompagnons.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons">
<!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/groupes/">Voir tous les groupes →</a></div><!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"80px","bottom":"80px","left":"24px","right":"24px"}}},"layout":{"type":"constrained","contentSize":"1100px"}} -->
<div class="wp-block-group alignfull" style="padding-top:80px;padding-right:24px;padding-bottom:80px;padding-left:24px">
<!-- wp:heading {"textAlign":"center","level":2,"style":{"typography":{"fontSize":"2.25rem"}}} -->
<h2 class="wp-block-heading has-text-align-center" style="font-size:2.25rem">Dernières <em>actualités</em></h2>
<!-- /wp:heading -->

<!-- wp:latest-posts {"postsToShow":3,"displayPostContentRadio":"excerpt","displayPostDate":true,"postLayout":"grid","columns":3,"align":"wide"} /-->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons">
<!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/actu/">Toutes les actualités →</a></div><!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","backgroundColor":"vivid-red","textColor":"white","style":{"spacing":{"padding":{"top":"80px","bottom":"80px","left":"24px","right":"24px"}}},"layout":{"type":"constrained","contentSize":"800px"}} -->
<div class="wp-block-group alignfull has-white-color has-vivid-red-background-color has-text-color has-background" style="padding-top:80px;padding-right:24px;padding-bottom:80px;padding-left:24px">
<!-- wp:heading {"textAlign":"center","level":2,"style":{"typography":{"fontSize":"2.25rem"}}} -->
<h2 class="wp-block-heading has-text-align-center" style="font-size:2.25rem">Signez <em>l'appel</em></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"1.1rem"}}} -->
<p class="has-text-align-center" style="font-size:1.1rem">Pour une société plus juste, écologique et démocratique. Signer, c'est s'engager à recevoir nos appels et à les relayer.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"white","textColor":"vivid-red","style":{"typography":{"fontSize":"1.15rem"}}} -->
<div class="wp-block-button has-custom-font-size" style="font-size:1.15rem"><a class="wp-block-button__link has-vivid-red-color has-white-background-color has-text-color has-background wp-element-button" href="/signer/">Je signe l'appel</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"80px","bottom":"80px","left":"24px","right":"24px"}}},"layout":{"type":"constrained","contentSize":"1100px"}} -->
<div class="wp-block-group alignfull" style="padding-top:80px;padding-right:24px;padding-bottom:80px;padding-left:24px">
<!-- wp:heading {"textAlign":"center","level":2,"style":{"typography":{"fontSize":"2.25rem"}}} -->
<h2 class="wp-block-heading has-text-align-center" style="font-size:2.25rem">Faire un <em>don</em></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"1.1rem"},"color":{"text":"#666"}}} -->
<p class="has-text-align-center has-text-color" style="color:#666;font-size:1.1rem">Indépendants des partis et des grands donateurs, nous ne tenons que par vous. 66% de votre don est déductible de vos impôts.</p>
<!-- /wp:paragraph -->

<!-- wp:columns {"style":{"spacing":{"blockGap":{"top":"16px","left":"16px"}}}} -->
<div class="wp-block-columns">
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button {"width":100} --><div class="wp-block-button has-custom-width wp-block-button__width-100"><a class="wp-block-button__link wp-element-button" href="/don/">5€<br><small>(coût réel : 1,70€)</small></a></div><!-- /wp:button --></div>
<!-- /wp:buttons -->
</div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button {"width":100} --><div class="wp-block-button has-custom-width wp-block-button__width-100"><a class="wp-block-button__link wp-element-button" href="/don/">20€<br><small>(coût réel : 6,80€)</small></a></div><!-- /wp:button --></div>
<!-- /wp:buttons -->
</div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button {"width":100} --><div class="wp-block-button has-custom-width wp-block-button__width-100"><a class="wp-block-button__link wp-element-button" href="/don/">50€<br><small>(coût réel : 17€)</small></a></div><!-- /wp:button --></div>
<!-- /wp:buttons -->
</div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button {"width":100} --><div class="wp-block-button has-custom-width wp-block-button__width-100"><a class="wp-block-button__link wp-element-button" href="/don/">Libre</a></div><!-- /wp:button --></div>
<!-- /wp:buttons -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:group -->
HTML;
	}

	/**
	 * Manifeste editable Gutenberg (au lieu d'un shortcode hardcode).
	 */
	private static function default_manifeste_content() {
		return "<!-- wp:paragraph -->\n<p><strong>Nous sommes des citoyennes et des citoyens.</strong> Pas un parti. Pas un syndicat. Un mouvement, ouvert et indépendant, qui croit qu'une autre société est possible — plus juste, plus écologique, plus démocratique.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:heading -->\n<h2>Notre constat</h2>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph -->\n<p>La République promet l'égalité. Elle livre la précarité. Le mérite y est devenu un mirage : la naissance pèse plus que le travail. Les services publics, qui faisaient la fierté du pays, sont méthodiquement démantelés. Le climat se dérègle, et les solutions arrivent par décret au lieu de naître du débat.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:heading -->\n<h2>Nos principes</h2>\n<!-- /wp:heading -->\n\n<!-- wp:list -->\n<ul><li><strong>Indépendance.</strong> Aucun parti, aucun lobby, aucun grand donateur. Nous vivons des cotisations et des dons des adhérents.</li><li><strong>Démocratie interne.</strong> Toutes les décisions importantes sont votées en assemblée générale. Les statuts sont publics, les comptes aussi.</li><li><strong>Action concrète.</strong> Nous menons des campagnes thématiques mesurables, avec des objectifs chiffrés et des bilans publics.</li><li><strong>Bienveillance.</strong> Nous combattons les idées, jamais les personnes. Aucun racisme, aucun sexisme, aucune homophobie ne sont tolérés dans nos rangs.</li></ul>\n<!-- /wp:list -->\n\n<!-- wp:heading -->\n<h2>Nos priorités 2026</h2>\n<!-- /wp:heading -->\n\n<!-- wp:list {\"ordered\":true} -->\n<ol><li>Justice climatique pour tou·tes — y compris les plus modestes.</li><li>Logement digne et abordable, partout, pour tout le monde.</li><li>Refondation des services publics — santé, école, transports, énergie.</li><li>Démocratie permanente — RIC, assemblées tirées au sort, transparence.</li><li>Égalité réelle — femmes/hommes, origines, handicaps, orientations.</li><li>Souveraineté citoyenne — sortir de l'emprise des géants du numérique.</li></ol>\n<!-- /wp:list -->";
	}

	/**
	 * Statuts type association loi 1901, neutres et reutilisables.
	 */
	private static function default_statuts_content() {
		return '<h2>Article 1 — Constitution et dénomination</h2>
<p>Il est fondé entre les adhérent·es aux présents statuts une association régie par la loi du 1er juillet 1901 et le décret du 16 août 1901, ayant pour titre <strong>[Nom de l\'association]</strong>.</p>

<h2>Article 2 — Objet</h2>
<p>L\'association a pour but de promouvoir une société plus juste, écologique et démocratique par tous les moyens légaux : campagnes de sensibilisation, mobilisations citoyennes, productions intellectuelles, actions de terrain, plaidoyer institutionnel.</p>

<h2>Article 3 — Siège social</h2>
<p>Le siège social est fixé à <strong>[Adresse]</strong>. Il pourra être transféré sur simple décision du Conseil d\'administration.</p>

<h2>Article 4 — Durée</h2>
<p>La durée de l\'association est illimitée.</p>

<h2>Article 5 — Composition</h2>
<p>L\'association se compose de :</p>
<ul>
<li>Membres sympathisant·es (gratuit) : reçoivent les communications, signent les pétitions ;</li>
<li>Membres adhérent·es : à jour de leur cotisation annuelle, peuvent voter en AG ;</li>
<li>Membres militant·es : adhérent·es engagé·es dans une action ou un groupe local ;</li>
<li>Membres bienfaiteur·rices : versent une cotisation supérieure au montant minimum.</li>
</ul>

<h2>Article 6 — Cotisation</h2>
<p>Le montant de la cotisation annuelle est fixé chaque année par l\'Assemblée générale.</p>

<h2>Article 7 — Conseil d\'administration</h2>
<p>L\'association est administrée par un Conseil d\'administration de 7 à 15 membres, élu·es pour 2 ans par l\'AG. Le CA élit en son sein un bureau (président·e, trésorier·e, secrétaire). Le Conseil se réunit au minimum 4 fois par an.</p>

<h2>Article 8 — Assemblée générale ordinaire</h2>
<p>L\'AG ordinaire se réunit une fois par an. Elle approuve les comptes, vote le budget et oriente la stratégie. Tou·tes les adhérent·es à jour de cotisation y participent avec voix délibérative.</p>

<h2>Article 9 — Assemblée générale extraordinaire</h2>
<p>L\'AG extraordinaire peut être convoquée à la demande de 1/3 des adhérent·es ou du CA pour modifier les statuts ou prononcer la dissolution.</p>

<h2>Article 10 — Ressources</h2>
<p>Les ressources de l\'association comprennent : cotisations, dons manuels, subventions publiques, produit des manifestations, recettes de ses publications, et toute autre ressource autorisée par la loi.</p>

<h2>Article 11 — Règlement intérieur</h2>
<p>Un règlement intérieur, adopté par le CA et ratifié par l\'AG, précise les modalités d\'application des présents statuts.</p>

<h2>Article 12 — Dissolution</h2>
<p>En cas de dissolution prononcée en AG extraordinaire, un·e ou plusieurs liquidateur·rices sont nommé·es. L\'actif net est dévolu à une autre association poursuivant un objet similaire.</p>

<p><em>Statuts adoptés en Assemblée générale constitutive le [date].</em></p>';
	}

	/**
	 * Cree des CPT exemples concrets (combats, evenements, groupes,
	 * petitions) si aucune entree n\'existe deja. Ne duplique rien.
	 */
	public static function create_default_cpts( $force = false ) {
		if ( $force ) {
			foreach ( array( 'ag_combat', 'ag_evenement', 'ag_groupe', 'ag_petition' ) as $cpt ) {
				$old = get_posts( array( 'post_type' => $cpt, 'posts_per_page' => -1, 'post_status' => 'any', 'fields' => 'ids' ) );
				foreach ( $old as $pid ) wp_delete_post( $pid, true );
			}
			// Articles : on supprime uniquement nos articles seedes (par titre exact)
			$seed_titles = array(
				"Hôpital public : nous publions notre contre-budget 2026",
				"Pétition climat : 47 000 signatures en 3 semaines",
				"Nouveau groupe local à Saint-Étienne — bienvenue !",
				"Logement : nos 12 propositions pour 2027",
				"AG 2026 : ce qui a été voté",
			);
			foreach ( $seed_titles as $t ) {
				$existing = get_page_by_title( $t, OBJECT, 'post' );
				if ( $existing ) wp_delete_post( $existing->ID, true );
			}
		}
		// Combats
		if ( ! get_posts( array( "post_type" => "ag_combat", "posts_per_page" => 1, "post_status" => "any" ) ) ) {
			$combats = array(
				array( "Justice climatique",  "Pour une transition écologique qui ne pèse pas sur les plus modestes : rénovation thermique massive des passoires énergétiques, gratuité progressive des transports en commun, fin des aides publiques aux énergies fossiles.\n\nNos propositions chiffrées, validées par 12 économistes universitaires, sont disponibles dans notre <strong>contre-budget climat 2026</strong>." ),
				array( "Logement digne",      "Le logement est devenu inaccessible pour des millions de Français·es. Nous portons : encadrement effectif des loyers en zone tendue, réquisition des logements vacants depuis plus de 18 mois, plan de construction de 200 000 logements sociaux par an." ),
				array( "Service public fort", "Refonder l'hôpital, l'école, la SNCF. Stop aux fermetures de lits et de classes. Nous demandons un moratoire immédiat sur toute fermeture de service public en zone rurale, et un plan de recrutement à hauteur des besoins réels." ),
				array( "Démocratie réelle",   "Voter tous les 5 ans ne suffit plus. Nous portons le Référendum d'Initiative Citoyenne (RIC), des assemblées citoyennes tirées au sort sur les grands sujets, la reconnaissance du vote blanc, et la révocation des élu·es par leurs électeur·rices." ),
				array( "Transparence publique","Open data total des marchés publics, registre obligatoire des lobbies au Parlement et dans les ministères, traçabilité des amendements parlementaires. La démocratie sans transparence est une illusion." ),
				array( "Égalité réelle",      "Combattre activement toutes les discriminations : sexe, origine, handicap, orientation. Loi de parité réelle dans les exécutifs, statistiques ethniques anonymisées pour mesurer les discriminations, formation obligatoire dans la fonction publique." ),
			);
			foreach ( $combats as $c ) {
				wp_insert_post( array(
					"post_type"    => "ag_combat",
					"post_status"  => "publish",
					"post_title"   => $c[0],
					"post_excerpt" => wp_trim_words( $c[1], 25 ),
					"post_content" => $c[1],
				) );
			}
		}

		// Evenements (avec meta date/heure/lieu)
		if ( ! get_posts( array( "post_type" => "ag_evenement", "posts_per_page" => 1, "post_status" => "any" ) ) ) {
			$events = array(
				array(
					"title"   => "Marche pour la justice climatique",
					"date"    => "2026-06-15", "time" => "14:00", "end" => "18:00",
					"city"    => "Paris", "place" => "République → Bastille",
					"content" => "Grande marche nationale. Départ 14h place de la République, arrivée Bastille. Plus de 50 organisations partenaires, fanfare militante, prises de parole. <strong>Apportez vos pancartes !</strong> Mobilisation pour une transition écologique juste et sociale.",
				),
				array(
					"title"   => "Assemblée générale annuelle",
					"date"    => "2026-06-22", "time" => "09:00", "end" => "18:00",
					"city"    => "Lyon", "place" => "Bourse du Travail",
					"content" => "AG ouverte à toutes les adhérent·es à jour de cotisation. <strong>Bilan moral, bilan financier, vote du programme stratégique 2027, élection du Conseil d'administration.</strong> Émargement dès 8h30. Repas partagé sur place.",
				),
				array(
					"title"   => "Université d'été du mouvement",
					"date"    => "2026-07-06", "time" => "10:00", "end" => "18:00",
					"city"    => "Marseille", "place" => "Friche Belle de Mai",
					"content" => "Trois jours de formations, ateliers, débats du 6 au 8 juillet. Inscription obligatoire (gratuit pour adhérent·es, 30€ pour les autres). Hébergement solidaire possible chez les adhérent·es marseillais·es.",
				),
				array(
					"title"   => "Atelier — Je crée un groupe local",
					"date"    => "2026-06-05", "time" => "19:00", "end" => "21:00",
					"city"    => "En visio", "place" => "Lien envoyé après inscription",
					"content" => "Soirée de formation pour celles et ceux qui veulent lancer un groupe dans leur ville. <strong>Méthodologie, outils numériques, cadrage juridique.</strong> Inscription gratuite. Replay envoyé aux inscrit·es.",
				),
				array(
					"title"   => "Conférence — Refonder l'hôpital public",
					"date"    => "2026-06-28", "time" => "20:00", "end" => "22:30",
					"city"    => "Toulouse", "place" => "Salle du Cabanis (centre-ville)",
					"content" => "Présentation publique de notre contre-budget hôpital, en présence de 3 médecins de terrain et d'un économiste de la santé. <strong>Entrée libre</strong>, débat avec la salle après les interventions.",
				),
			);
			foreach ( $events as $e ) {
				$post_id = wp_insert_post( array(
					"post_type"    => "ag_evenement",
					"post_status"  => "publish",
					"post_title"   => $e["title"],
					"post_excerpt" => wp_trim_words( wp_strip_all_tags( $e["content"] ), 25 ),
					"post_content" => $e["content"],
				) );
				if ( $post_id && ! is_wp_error( $post_id ) ) {
					update_post_meta( $post_id, "_ag_event_date",  $e["date"] );
					update_post_meta( $post_id, "_ag_event_time",  $e["time"] );
					update_post_meta( $post_id, "_ag_event_end",   $e["end"] );
					update_post_meta( $post_id, "_ag_event_city",  $e["city"] );
					update_post_meta( $post_id, "_ag_event_place", $e["place"] );
				}
			}
		}

		// Groupes locaux
		if ( ! get_posts( array( "post_type" => "ag_groupe", "posts_per_page" => 1, "post_status" => "any" ) ) ) {
			$groupes = array(
				array( "Paris 11e", "Animé par Yacine et Léa. Réunion publique le premier mardi de chaque mois, 19h, café associatif Le Lieu (rue Saint-Maur). Une trentaine d'adhérent·es actifs." ),
				array( "Lyon Croix-Rousse", "Animé par Sophie. Atelier hebdomadaire d'éducation populaire, samedi matin à la Maison des Associations. Mobilisation locale autour du logement et des transports." ),
				array( "Marseille centre", "Animé par Mehdi. Permanence chaque mercredi à la Friche Belle de Mai. Travail de terrain dans les quartiers Nord, soutien scolaire bénévole, plaidoyer auprès de la mairie." ),
				array( "Saint-Étienne", "Tout nouveau groupe ! Première réunion le 18 mai, Maison des Syndicats. Cherche ses bénévoles fondateurs — n'hésitez pas à venir." ),
				array( "Toulouse Mirail", "Animé par Aïcha. Engagement fort sur les droits des femmes et les discriminations. Rendez-vous mensuel ouvert à tou·tes le second jeudi soir." ),
			);
			foreach ( $groupes as $g ) {
				wp_insert_post( array(
					"post_type"    => "ag_groupe",
					"post_status"  => "publish",
					"post_title"   => $g[0],
					"post_excerpt" => wp_trim_words( $g[1], 20 ),
					"post_content" => $g[1],
				) );
			}
		}

		// Petitions
		if ( ! get_posts( array( "post_type" => "ag_petition", "posts_per_page" => 1, "post_status" => "any" ) ) ) {
			$petitions = array(
				array( "Pour la transparence des marchés publics", "Aujourd'hui, l'attribution des marchés publics reste opaque. Nous demandons leur publication intégrale en open data, avec mise à disposition d'une API publique. Objectif : 50 000 signatures d'ici fin juillet." ),
				array( "Encadrement effectif des loyers en zone tendue", "Le dispositif actuel n'est pas appliqué dans 6 communes sur 10. Nous demandons des contrôles systématiques et des sanctions dissuasives pour les bailleurs en infraction." ),
				array( "Reconnaissance du vote blanc",            "Le vote blanc doit compter dans le total des suffrages exprimés. C'est une exigence démocratique élémentaire, portée depuis des années sans être appliquée." ),
			);
			foreach ( $petitions as $p ) {
				wp_insert_post( array(
					"post_type"    => "ag_petition",
					"post_status"  => "publish",
					"post_title"   => $p[0],
					"post_excerpt" => wp_trim_words( $p[1], 25 ),
					"post_content" => $p[1],
				) );
			}
		}

		// Articles (post standard) — 5 articles d'actualite
		// Si force=true on a deja supprime les seeds connus, on reseed direct.
		// Sinon : check si les seeds sont deja la (par titre exact).
		$should_seed_articles = $force;
		if ( ! $should_seed_articles ) {
			$check = get_page_by_title( "Hôpital public : nous publions notre contre-budget 2026", OBJECT, 'post' );
			$should_seed_articles = ! $check;
		}
		if ( $should_seed_articles ) {
			$articles = array(
				array(
					"title"   => "Hôpital public : nous publions notre contre-budget 2026",
					"excerpt" => "Notre groupe de travail santé publie un rapport de 60 pages chiffrant un plan d'urgence pour l'hôpital. À télécharger librement.",
					"content" => "<p>Après 14 mois de travail, notre groupe santé publie aujourd'hui un <strong>contre-budget hôpital public</strong> de 60 pages, validé par 12 économistes universitaires et 35 médecins de terrain.</p>\n<h3>Ce que nous proposons</h3>\n<ul><li>Recrutement de 100 000 soignant·es sur 5 ans, financé par la suppression des aides aux complémentaires santé privées</li><li>Moratoire immédiat sur les fermetures de lits et de services en zone rurale</li><li>Revalorisation salariale ciblée pour les métiers en tension (infirmières, aides-soignantes)</li><li>Plan d'investissement de 12 milliards d'euros sur 5 ans pour rénover le bâti hospitalier</li></ul>\n<p>Le rapport est <strong>téléchargeable gratuitement</strong> sur notre site et a déjà été envoyé aux groupes parlementaires. Nous demanderons des auditions à la commission des Affaires sociales.</p>\n<p><em>Document écrit en licence libre Creative Commons. Diffusez-le.</em></p>",
					"date"    => "2026-05-12 09:00:00",
				),
				array(
					"title"   => "Pétition climat : 47 000 signatures en 3 semaines",
					"excerpt" => "L'objectif de 50 000 est désormais à portée. Le dépôt à l'Assemblée est prévu pour la fin du mois. Merci à tou·tes.",
					"content" => "<p>Lancée il y a tout juste 3 semaines, notre <strong>pétition pour la transparence des marchés publics</strong> vient de franchir la barre symbolique des <strong>47 000 signatures</strong>. À ce rythme, nous dépasserons l'objectif de 50 000 d'ici la fin du mois.</p>\n<h3>Et après ?</h3>\n<p>Nous prévoyons un dépôt officiel à l'Assemblée nationale le <strong>25 mai</strong>, en présence de plusieurs député·es de l'inter-groupe transparence. Une délégation du mouvement sera reçue à 14h dans les locaux du Palais Bourbon.</p>\n<p>Notre objectif : obtenir un débat parlementaire sur la <strong>publication open data systématique des marchés publics</strong> de plus de 25 000€, avec sanctions effectives en cas de non-respect.</p>\n<p>Vous n'avez pas encore signé ? <a href=\"/signer/\">C'est le moment.</a></p>",
					"date"    => "2026-05-05 14:30:00",
				),
				array(
					"title"   => "Nouveau groupe local à Saint-Étienne — bienvenue !",
					"excerpt" => "Le 47e groupe local du mouvement vient d'être officialisé. Première réunion publique le 18 mai à la Maison des Syndicats.",
					"content" => "<p>C'est officiel : le mouvement compte désormais <strong>47 groupes locaux</strong> partout en France. Le petit dernier vient d'être créé à <strong>Saint-Étienne</strong> par une équipe de 8 fondateurs et fondatrices.</p>\n<h3>Première rencontre publique</h3>\n<p>Vous habitez dans le bassin stéphanois ? Venez nous rencontrer le <strong>samedi 18 mai à 18h</strong>, à la Maison des Syndicats (Bourse du Travail, cours Victor Hugo).</p>\n<p>Au programme :</p>\n<ul><li>Présentation du mouvement et de ses combats</li><li>Tour de table : qu'est-ce qui vous fait venir ?</li><li>Identification des priorités locales (hôpital de Bellevue, transports en commun, logement étudiant)</li><li>Verre de l'amitié — bières locales offertes par la maison</li></ul>\n<p>Aucune adhésion requise pour la première réunion.</p>",
					"date"    => "2026-04-28 17:15:00",
				),
				array(
					"title"   => "Logement : nos 12 propositions pour 2027",
					"excerpt" => "Encadrement réel des loyers, réquisition des logements vacants, plan massif de construction sociale. Un livret de 40 pages disponible.",
					"content" => "<p>La crise du logement est devenue insupportable. Étudiant·es qui dorment dans leur voiture, familles qui consacrent 50% de leurs revenus au loyer, communes entières où il n'y a plus rien à louer. Nous publions aujourd'hui <strong>12 propositions chiffrées</strong> pour 2027.</p>\n<h3>Les axes principaux</h3>\n<ol><li><strong>Encadrement effectif des loyers</strong> en zone tendue avec contrôles aléatoires et sanctions dissuasives</li><li><strong>Réquisition des logements vacants</strong> depuis plus de 18 mois, indemnisation propriétaires</li><li><strong>200 000 logements sociaux par an</strong> pendant 5 ans, financés par un fléchage de l'épargne réglementée</li><li><strong>Plafonnement Airbnb</strong> à 90 jours/an en zone tendue (contre 120 actuellement)</li><li><strong>Garantie universelle des loyers</strong> à la charge de l'État</li></ol>\n<p>Le livret complet (40 pages) est disponible en PDF sur notre site. Nous le présenterons en avant-première lors de l'<strong>Université d'été</strong>, à Marseille du 6 au 8 juillet.</p>",
					"date"    => "2026-04-20 10:00:00",
				),
				array(
					"title"   => "AG 2026 : ce qui a été voté",
					"excerpt" => "Compte-rendu de l'Assemblée générale annuelle de Lyon. Bilan financier, élection du CA, programme stratégique 2027.",
					"content" => "<p>Le 22 juin dernier, plus de <strong>650 adhérent·es</strong> se sont réuni·es à la Bourse du Travail de Lyon pour notre Assemblée générale annuelle. Voici les principales décisions.</p>\n<h3>Bilan financier — adopté à 94%</h3>\n<p>Comptes 2025 certifiés sans réserve par notre commissaire aux comptes. Total des recettes : <strong>312 000€</strong> dont 78% de cotisations et dons d'adhérent·es. Aucun don de plus de 1 500€. Tous les comptes sont publiés en open data sur notre site.</p>\n<h3>Programme stratégique 2027 — adopté à 87%</h3>\n<ul><li>Lancement de 3 nouvelles campagnes thématiques : santé mentale, ruralité, handicap</li><li>Objectif de 60 groupes locaux d'ici fin 2027 (soit +28%)</li><li>Création d'un fonds d'aide juridique pour soutenir les actions des groupes locaux</li><li>Refonte du site internet et lancement d'une newsletter hebdomadaire</li></ul>\n<h3>Conseil d'administration — élu</h3>\n<p>Le nouveau CA compte 13 membres (parité respectée), élu·es pour 2 ans. Le bureau a été désigné le lendemain : Camille Lefèvre reconduite à la présidence, Léa Marchand trésorière, Mehdi El Amrani secrétaire général.</p>\n<p><a href=\"/mon-compte/\">Le PV intégral est consultable</a> par les adhérent·es à jour de cotisation.</p>",
					"date"    => "2026-04-10 16:00:00",
				),
			);
			foreach ( $articles as $a ) {
				wp_insert_post( array(
					"post_type"    => "post",
					"post_status"  => "publish",
					"post_title"   => $a["title"],
					"post_excerpt" => $a["excerpt"],
					"post_content" => $a["content"],
					"post_date"    => $a["date"],
					"post_date_gmt" => get_gmt_from_date( $a["date"] ),
				) );
			}
		}
	}

	/**
	 * Cree (idempotent) les menus principal et footer puis les assigne
	 * aux emplacements 'primary' et 'footer' du theme. Indispensable
	 * pour que le menu pointe vers les pages reelles et non vers des
	 * ancres.
	 */
	public static function create_default_menus() {
		$primary_items = array(
			'qui-sommes-nous' => 'Qui sommes-nous',
			'manifeste'       => 'Manifeste',
			'combats'         => 'Combats',
			'evenements'      => 'Événements',
			'groupes'         => 'Groupes locaux',
			'reunion'         => 'Réunion en ligne',
			'actu'            => 'Actualités',
			'don'             => 'Faire un don',
		);
		$footer_items = array(
			'manifeste' => 'Le manifeste',
			'groupes'   => 'Trouver mon groupe',
			'don'       => 'Faire un don',
			'mentions'  => 'Mentions légales',
			'rgpd'      => 'Confidentialité',
		);
		self::ensure_menu( 'AG Fidélité — Principal', 'primary', $primary_items );
		self::ensure_menu( 'AG Fidélité — Footer',    'footer',  $footer_items );
	}

	/**
	 * Ajoute les items par defaut dans le menu, SANS jamais effacer les items
	 * deja presents (ajouts manuels de l'utilisateur preserves).
	 *
	 * Logique :
	 *  - Si le menu n'existe pas : on le cree et on ajoute tous les items
	 *  - Si le menu existe : on liste les pages deja liees, et on ajoute
	 *    UNIQUEMENT les pages par defaut absentes (pas de wipe)
	 *  - Les items personnalises de l'utilisateur (Connexion, Boutique,
	 *    liens externes, sous-menus...) sont TOUJOURS preserves
	 *
	 * Bug fix v0.29 : avant cette version, le menu etait wipe a chaque update
	 * du plugin, faisant disparaitre les ajouts manuels (page Connexion etc).
	 */
	private static function ensure_menu( $menu_name, $location, $items ) {
		$menu = wp_get_nav_menu_object( $menu_name );
		if ( ! $menu ) {
			$menu_id = wp_create_nav_menu( $menu_name );
			if ( is_wp_error( $menu_id ) ) return;
			$existing_page_ids = array();
		} else {
			$menu_id = $menu->term_id;
			// Liste des post_id des pages deja dans le menu (preservation
			// totale : on n'efface AUCUN item utilisateur).
			$existing_page_ids = array();
			foreach ( (array) wp_get_nav_menu_items( $menu_id ) as $existing ) {
				if ( $existing->object === 'page' && $existing->object_id ) {
					$existing_page_ids[] = (int) $existing->object_id;
				}
			}
		}
		foreach ( $items as $slug => $label ) {
			$page = get_page_by_path( $slug );
			if ( ! $page ) continue;
			// Skip si la page est deja liee au menu (eviter les doublons).
			if ( in_array( (int) $page->ID, $existing_page_ids, true ) ) continue;
			wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-title'     => $label,
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $page->ID,
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
			) );
		}
		// Assigne le menu a son emplacement seulement s'il n'y en a pas deja un
		// (preservation des choix utilisateur : si l'utilisateur a affecte un
		// autre menu a 'primary', on ne le force pas).
		$locations = get_theme_mod( 'nav_menu_locations', array() );
		if ( empty( $locations[ $location ] ) ) {
			$locations[ $location ] = $menu_id;
			set_theme_mod( 'nav_menu_locations', $locations );
		}
	}
}
