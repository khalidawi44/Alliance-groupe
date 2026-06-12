<?php
/**
 * AG Prefill — pré-remplissage automatique du thème.
 *
 * Quand un ZIP est généré par le « Créateur de site » d'Alliance Groupe, on
 * glisse un fichier `ag-prefill.json` à la racine du thème. À la première
 * activation (ou au premier chargement admin), on applique ces valeurs aux
 * réglages du thème (nom du business, métier, slogan, couleur) pour que le
 * site soit DÉJÀ personnalisé — l'utilisateur a l'impression de l'avoir
 * créé chez nous.
 *
 * Idempotent : on n'applique qu'une seule fois (flag option), ensuite le
 * client est libre de tout modifier dans Apparence > Personnaliser.
 *
 * @package AG_Starter_Avocat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ag_avocat_apply_prefill' ) ) :
	/**
	 * Lit ag-prefill.json (racine du thème) et applique les valeurs une fois.
	 */
	function ag_avocat_apply_prefill() {
		// Déjà appliqué ? on ne touche plus à rien.
		if ( get_option( 'ag_avocat_prefill_done' ) ) {
			return;
		}

		$file = get_template_directory() . '/ag-prefill.json';
		if ( ! file_exists( $file ) ) {
			return;
		}

		$raw  = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$data = json_decode( (string) $raw, true );
		if ( ! is_array( $data ) ) {
			update_option( 'ag_avocat_prefill_done', 1 ); // fichier invalide : on évite de boucler.
			return;
		}

		$business = isset( $data['business'] ) ? sanitize_text_field( $data['business'] ) : '';
		$metier   = isset( $data['metier'] ) ? sanitize_text_field( $data['metier'] ) : '';
		$slogan   = isset( $data['slogan'] ) ? sanitize_text_field( $data['slogan'] ) : '';
		$accent   = isset( $data['accent'] ) ? sanitize_hex_color( $data['accent'] ) : '';
		$prefix   = isset( $data['prefix'] ) ? sanitize_text_field( $data['prefix'] ) : '';

		// Titre du site = nom du business.
		if ( $business ) {
			update_option( 'blogname', $business );
		}

		// Hero : préfixe (métier) + marque (nom du business) + slogan.
		// Ex. « Boucherie » / « Maison Martin ».
		$hero_prefix = $prefix ? $prefix : $metier;
		if ( $hero_prefix ) {
			set_theme_mod( 'ag_hero_prefix', $hero_prefix );
		}
		if ( $business ) {
			set_theme_mod( 'ag_hero_brand', $business );
		}
		if ( $slogan ) {
			set_theme_mod( 'ag_hero_subtitle', $slogan );
		}
		if ( $accent ) {
			set_theme_mod( 'ag_color_accent', $accent );
		}

		// Mémorise le métier (réutilisable par les plugins Premium/Business).
		if ( $metier ) {
			update_option( 'ag_avocat_metier', $metier );
			set_theme_mod( 'ag_coach_metier_nom', $metier );
		}

		update_option( 'ag_avocat_prefill_done', 1 );
	}
endif;

// À l'activation du thème ET en filet de sécurité au 1er chargement admin.
add_action( 'after_switch_theme', 'ag_avocat_apply_prefill' );
add_action( 'admin_init', 'ag_avocat_apply_prefill' );

if ( ! function_exists( 'ag_avocat_ensure_legal_pages' ) ) :
	/**
	 * Crée automatiquement les 3 pages légales obligatoires d'un site d'avocat
	 * (template « déontologie-ready ») : Mentions légales, Politique de
	 * confidentialité (RGPD) et Cookies. Leurs liens apparaissent alors tout
	 * seuls dans le bandeau du pied de page.
	 *
	 * Contenu pré-rédigé avec des champs « [À COMPLÉTER] » que le cabinet
	 * personnalise. Idempotent : on ne crée chaque page qu'une fois (et jamais
	 * si une page du même slug existe déjà), via le flag ag_avocat_legal_pages_v1.
	 */
	function ag_avocat_ensure_legal_pages() {
		if ( get_option( 'ag_avocat_legal_pages_v1' ) ) {
			return;
		}
		if ( ! function_exists( 'wp_insert_post' ) || ! current_user_can_safe() ) {
			// current_user_can_safe : sur after_switch_theme l'utilisateur est admin ;
			// sur admin_init on ne crée que si capacité d'édition (filet de sécurité).
			return;
		}

		$brand = get_option( 'blogname' );
		$pages = array(
			'mentions-legales' => array(
				'title'   => 'Mentions légales',
				'content' => "<h2>Éditeur du site</h2>\n"
					. "<p>Le présent site est édité par <strong>" . esc_html( $brand ) . "</strong>, avocat(e) inscrit(e) au Barreau de <strong>[À COMPLÉTER]</strong>.<br>\n"
					. "Adresse : [À COMPLÉTER]<br>\nTéléphone : [À COMPLÉTER] — Email : [À COMPLÉTER]<br>\n"
					. "N° TVA intracommunautaire / SIRET : [À COMPLÉTER]<br>\nN° RPVA : [À COMPLÉTER]</p>\n"
					. "<h2>Directeur de la publication</h2>\n<p>[À COMPLÉTER — nom de l'avocat responsable].</p>\n"
					. "<h2>Déontologie</h2>\n<p>L'avocat est soumis aux règles déontologiques de la profession (RIN, code de déontologie), "
					. "au secret professionnel et au contrôle de son Ordre. Les présentes mentions respectent les règles de communication des avocats.</p>\n"
					. "<h2>Hébergement</h2>\n<p>Ce site est hébergé au sein de l'<strong>Union Européenne</strong> par [À COMPLÉTER — nom et adresse de l'hébergeur], "
					. "afin de garantir la protection des données et le respect du secret professionnel.</p>\n"
					. "<h2>Propriété intellectuelle</h2>\n<p>L'ensemble des contenus de ce site est protégé. Toute reproduction sans autorisation est interdite.</p>",
			),
			'confidentialite' => array(
				'title'   => 'Politique de confidentialité (RGPD)',
				'content' => "<h2>Responsable du traitement</h2>\n"
					. "<p>Le responsable du traitement des données est <strong>" . esc_html( $brand ) . "</strong> — contact : [À COMPLÉTER — email].</p>\n"
					. "<h2>Données collectées</h2>\n<p>Via le formulaire de prise de rendez-vous / contact, nous collectons les données que vous nous transmettez "
					. "(nom, email, téléphone, objet de votre demande). Ces informations sont couvertes par le <strong>secret professionnel de l'avocat</strong>.</p>\n"
					. "<h2>Finalité &amp; base légale</h2>\n<p>Ces données servent uniquement à répondre à votre demande et à assurer le suivi de votre dossier. "
					. "Base légale : votre consentement et/ou l'exécution de mesures précontractuelles.</p>\n"
					. "<h2>Durée de conservation</h2>\n<p>Les données de contact sont conservées le temps nécessaire au traitement de votre demande, "
					. "puis archivées conformément aux obligations légales de la profession, avant suppression.</p>\n"
					. "<h2>Vos droits</h2>\n<p>Vous disposez d'un droit d'accès, de rectification, d'effacement, de limitation et d'opposition. "
					. "Pour les exercer : [À COMPLÉTER — email]. Vous pouvez aussi saisir la <strong>CNIL</strong> (www.cnil.fr).</p>\n"
					. "<h2>Sécurité</h2>\n<p>Les données sont hébergées dans l'Union Européenne et protégées par des mesures techniques appropriées "
					. "(HTTPS, accès restreint). Aucune donnée n'est revendue ni transmise à des tiers à des fins commerciales.</p>",
			),
			'cookies' => array(
				'title'   => 'Cookies',
				'content' => "<h2>Utilisation des cookies</h2>\n"
					. "<p>Ce site respecte votre vie privée : il <strong>n'utilise aucun cookie de traçage publicitaire ni de profilage</strong>.</p>\n"
					. "<h2>Cookies strictement nécessaires</h2>\n<p>Seuls des cookies techniques, indispensables au bon fonctionnement du site "
					. "(par exemple la sécurisation du formulaire de contact), peuvent être déposés. Ils ne nécessitent pas votre consentement "
					. "et ne servent pas à vous suivre.</p>\n"
					. "<h2>Mesure d'audience</h2>\n<p>[À COMPLÉTER si vous ajoutez un outil de statistiques — privilégiez une solution respectueuse "
					. "du RGPD et hébergée dans l'UE.]</p>",
			),
		);

		foreach ( $pages as $slug => $p ) {
			if ( get_page_by_path( $slug ) ) {
				continue; // page déjà présente : on n'écrase jamais.
			}
			wp_insert_post( array(
				'post_title'   => $p['title'],
				'post_name'    => $slug,
				'post_content' => $p['content'],
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_author'  => get_current_user_id() ?: 1,
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
			) );
		}

		update_option( 'ag_avocat_legal_pages_v1', 1 );
	}
endif;

if ( ! function_exists( 'current_user_can_safe' ) ) :
	/** Garde simple : autorise la création si admin ou si édition de pages permise. */
	function current_user_can_safe() {
		return ! function_exists( 'current_user_can' ) || current_user_can( 'manage_options' ) || current_user_can( 'edit_pages' );
	}
endif;

add_action( 'after_switch_theme', 'ag_avocat_ensure_legal_pages' );
add_action( 'admin_init', 'ag_avocat_ensure_legal_pages' );
