<?php
/**
 * AG Recherche Juridique — Espace CLIENT du cabinet.
 *
 * Deux espaces complémentaires :
 *   - Espace JURIDIQUE  → réservé au cabinet (avocat) : recherche, dossiers,
 *     arguments. (Géré par AG_Recherche_Juridique.)
 *   - Espace CLIENT     → ce module : les clients du cabinet s'inscrivent, se
 *     connectent à leur espace perso et versent leurs pièces (documents), en
 *     toute confidentialité. L'avocat les retrouve dans son wp-admin.
 *
 * Sécurité / déontologie :
 *   - Fichiers stockés dans un dossier PRIVÉ (uploads/ag-cabinet-prive/),
 *     interdit d'accès direct (.htaccess) ; téléchargement uniquement via un
 *     point d'accès authentifié qui vérifie le propriétaire (client) ou le
 *     cabinet. Aucune URL publique.
 *   - Rôle dédié « ag_cabinet_client » : lecture seule, PAS d'accès wp-admin.
 *   - Consentement RGPD obligatoire à l'inscription ; rappel secret pro.
 *
 * @package ag-avocat-recherche
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AG_JR_Client {

	const ROLE       = 'ag_cabinet_client';
	const CPT_PIECE  = 'ag_jr_piece';
	const PAGE_SLUG  = 'espace-client';
	const PRIV_DIR   = 'ag-cabinet-prive';
	const MAX_BYTES  = 15728640; // 15 Mo

	private static $inst = null;

	public static function instance() {
		if ( null === self::$inst ) {
			self::$inst = new self();
		}
		return self::$inst;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'register' ) );
		add_action( 'init', array( $this, 'ensure_page' ) );

		add_shortcode( 'ag_espace_client', array( $this, 'shortcode' ) );

		// Liens de menu (entrée publique vers l'espace client).
		add_filter( 'wp_nav_menu_items', array( $this, 'nav_menu_link' ), 21, 2 );

		// Traitements (formulaires classiques, robustes, sans dépendance JS).
		add_action( 'admin_post_nopriv_ag_jr_client_register', array( $this, 'handle_register' ) );
		add_action( 'admin_post_ag_jr_client_register',        array( $this, 'handle_register' ) );
		add_action( 'admin_post_nopriv_ag_jr_client_login',    array( $this, 'handle_login' ) );
		add_action( 'admin_post_ag_jr_client_login',           array( $this, 'handle_login' ) );
		add_action( 'admin_post_ag_jr_client_logout',          array( $this, 'handle_logout' ) );
		add_action( 'admin_post_ag_jr_client_upload',          array( $this, 'handle_upload' ) );
		add_action( 'admin_post_ag_jr_piece_dl',               array( $this, 'handle_download' ) );

		// Côté cabinet (wp-admin) : sous-menus + colonnes + métabox pièce.
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 20 );
		add_filter( 'manage_' . self::CPT_PIECE . '_posts_columns', array( $this, 'piece_columns' ) );
		add_action( 'manage_' . self::CPT_PIECE . '_posts_custom_column', array( $this, 'piece_column' ), 10, 2 );
		add_action( 'add_meta_boxes', array( $this, 'piece_metabox' ) );
		add_action( 'save_post_' . self::CPT_PIECE, array( $this, 'piece_save' ) );

		// Empêche les clients d'entrer dans le wp-admin.
		add_action( 'admin_init', array( $this, 'block_admin' ) );
		add_filter( 'show_admin_bar', array( $this, 'maybe_hide_admin_bar' ) );
	}

	/* ============================================================
	 *  ACTIVATION / ENREGISTREMENT
	 * ============================================================ */
	public static function on_activate() {
		self::instance()->register();
		self::instance()->ensure_page();
		self::ensure_priv_dir();
		flush_rewrite_rules();
	}

	public function register() {
		// Rôle client (lecture seule).
		if ( ! get_role( self::ROLE ) ) {
			add_role( self::ROLE, 'Client du cabinet', array( 'read' => true ) );
		}

		// CPT « Pièce » (privé, visible seulement par le cabinet en admin).
		register_post_type( self::CPT_PIECE, array(
			'labels' => array(
				'name'          => 'Pièces clients',
				'singular_name' => 'Pièce',
				'edit_item'     => 'Pièce versée',
				'search_items'  => 'Rechercher une pièce',
				'not_found'     => 'Aucune pièce versée pour le moment.',
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => false,
			'capability_type' => 'post',
			'map_meta_cap'    => true,
			'supports'        => array( 'title' ),
			'menu_icon'       => 'dashicons-media-document',
		) );
	}

	/** Crée la page « Espace client » (une seule fois). */
	public function ensure_page() {
		if ( get_option( 'ag_jr_client_page_done' ) ) {
			return;
		}
		if ( ! get_page_by_path( self::PAGE_SLUG ) ) {
			wp_insert_post( array(
				'post_title'   => 'Espace client',
				'post_name'    => self::PAGE_SLUG,
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '[ag_espace_client]',
			) );
		}
		update_option( 'ag_jr_client_page_done', 1 );
	}

	/* ============================================================
	 *  DOSSIER PRIVÉ (stockage des pièces hors du web)
	 * ============================================================ */
	private static function priv_path() {
		$up = wp_upload_dir();
		return trailingslashit( $up['basedir'] ) . self::PRIV_DIR;
	}

	private static function ensure_priv_dir() {
		$dir = self::priv_path();
		if ( ! is_dir( $dir ) ) {
			wp_mkdir_p( $dir );
		}
		// Verrouillage : interdiction d'accès direct par le web.
		$ht = $dir . '/.htaccess';
		if ( ! file_exists( $ht ) ) {
			@file_put_contents( $ht, "Require all denied\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n" );
		}
		$idx = $dir . '/index.php';
		if ( ! file_exists( $idx ) ) {
			@file_put_contents( $idx, "<?php // Silence is golden.\n" );
		}
		return $dir;
	}

	/* ============================================================
	 *  HELPERS
	 * ============================================================ */
	private function page_url() {
		$p = get_page_by_path( self::PAGE_SLUG );
		return $p ? get_permalink( $p ) : home_url( '/' . self::PAGE_SLUG . '/' );
	}

	private function is_client( $user = null ) {
		$user = $user ? $user : wp_get_current_user();
		return ( $user && in_array( self::ROLE, (array) $user->roles, true ) );
	}

	/** Notifie le cabinet (Telegram/SMS interne si dispo, sinon email admin). */
	private function notify_cabinet( $msg ) {
		if ( function_exists( 'ag_push' ) ) {
			ag_push( $msg );
		}
		$to = get_option( 'admin_email' );
		if ( $to ) {
			wp_mail( $to, 'Espace client cabinet', wp_strip_all_tags( $msg ) );
		}
	}

	private function redirect_notice( $type, $code ) {
		$url = add_query_arg( array( 'ag_' . $type => $code ), $this->page_url() );
		wp_safe_redirect( $url );
		exit;
	}

	/* ============================================================
	 *  TRAITEMENTS — INSCRIPTION / CONNEXION / DÉCONNEXION
	 * ============================================================ */
	public function handle_register() {
		if ( ! isset( $_POST['ag_jr_client_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['ag_jr_client_nonce'] ), 'ag_jr_client_register' ) ) {
			$this->redirect_notice( 'err', 'nonce' );
		}
		$prenom = sanitize_text_field( wp_unslash( $_POST['prenom'] ?? '' ) );
		$nom    = sanitize_text_field( wp_unslash( $_POST['nom'] ?? '' ) );
		$email  = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
		$tel    = sanitize_text_field( wp_unslash( $_POST['tel'] ?? '' ) );
		$pass   = (string) ( $_POST['password'] ?? '' );
		$rgpd   = ! empty( $_POST['rgpd'] );

		if ( ! $prenom || ! $nom || ! is_email( $email ) || strlen( $pass ) < 8 || ! $rgpd ) {
			$this->redirect_notice( 'err', 'champs' );
		}
		if ( email_exists( $email ) || username_exists( $email ) ) {
			$this->redirect_notice( 'err', 'existe' );
		}

		$uid = wp_insert_user( array(
			'user_login'   => $email,
			'user_email'   => $email,
			'user_pass'    => $pass,
			'first_name'   => $prenom,
			'last_name'    => $nom,
			'display_name' => trim( $prenom . ' ' . $nom ),
			'role'         => self::ROLE,
		) );
		if ( is_wp_error( $uid ) ) {
			$this->redirect_notice( 'err', 'creation' );
		}
		if ( $tel ) {
			update_user_meta( $uid, 'ag_jr_client_tel', $tel );
		}
		update_user_meta( $uid, 'ag_jr_client_rgpd', current_time( 'mysql' ) );

		// Validation par le cabinet : en option (défaut = accès immédiat).
		$need_valid = (int) get_option( 'ag_jr_client_validation', 0 );
		update_user_meta( $uid, 'ag_jr_client_approved', $need_valid ? 0 : 1 );

		$this->notify_cabinet( sprintf( '👤 Nouveau client inscrit : %s (%s)%s', trim( $prenom . ' ' . $nom ), $email, $need_valid ? ' — à valider' : '' ) );

		// Email de bienvenue.
		wp_mail( $email, 'Bienvenue dans votre espace client',
			"Bonjour {$prenom},\n\nVotre espace client est créé. Vous pouvez vous connecter et déposer vos pièces en toute confidentialité.\n\n" . $this->page_url() . "\n\nLe cabinet." );

		// Connexion automatique.
		wp_set_current_user( $uid );
		wp_set_auth_cookie( $uid, true );
		$this->redirect_notice( 'ok', $need_valid ? 'inscrit_valid' : 'inscrit' );
	}

	public function handle_login() {
		if ( ! isset( $_POST['ag_jr_client_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['ag_jr_client_nonce'] ), 'ag_jr_client_login' ) ) {
			$this->redirect_notice( 'err', 'nonce' );
		}
		$creds = array(
			'user_login'    => sanitize_text_field( wp_unslash( $_POST['email'] ?? '' ) ),
			'user_password' => (string) ( $_POST['password'] ?? '' ),
			'remember'      => true,
		);
		$user = wp_signon( $creds, is_ssl() );
		if ( is_wp_error( $user ) ) {
			$this->redirect_notice( 'err', 'login' );
		}
		$this->redirect_notice( 'ok', 'connecte' );
	}

	public function handle_logout() {
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( wp_unslash( $_GET['_wpnonce'] ), 'ag_jr_client_logout' ) ) {
			$this->redirect_notice( 'err', 'nonce' );
		}
		wp_logout();
		wp_safe_redirect( $this->page_url() );
		exit;
	}

	/* ============================================================
	 *  TRAITEMENT — DÉPÔT DE PIÈCE
	 * ============================================================ */
	private function allowed_types() {
		return array(
			'pdf'  => 'application/pdf',
			'jpg'  => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'png'  => 'image/png',
			'webp' => 'image/webp',
			'doc'  => 'application/msword',
			'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'odt'  => 'application/vnd.oasis.opendocument.text',
			'txt'  => 'text/plain',
		);
	}

	public function handle_upload() {
		if ( ! is_user_logged_in() || ! $this->is_client() ) {
			$this->redirect_notice( 'err', 'auth' );
		}
		if ( ! isset( $_POST['ag_jr_client_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['ag_jr_client_nonce'] ), 'ag_jr_client_upload' ) ) {
			$this->redirect_notice( 'err', 'nonce' );
		}
		$uid = get_current_user_id();
		if ( ! (int) get_user_meta( $uid, 'ag_jr_client_approved', true ) ) {
			$this->redirect_notice( 'err', 'attente' );
		}
		if ( empty( $_FILES['piece'] ) || ! isset( $_FILES['piece']['tmp_name'] ) || ! is_uploaded_file( $_FILES['piece']['tmp_name'] ) ) {
			$this->redirect_notice( 'err', 'fichier' );
		}
		$file = $_FILES['piece'];
		if ( ! empty( $file['error'] ) ) {
			$this->redirect_notice( 'err', 'upload' );
		}
		if ( $file['size'] > self::MAX_BYTES ) {
			$this->redirect_notice( 'err', 'taille' );
		}
		$check = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'], $this->allowed_types() );
		$ext   = $check['ext'] ? $check['ext'] : strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
		if ( ! $ext || ! array_key_exists( $ext, $this->allowed_types() ) ) {
			$this->redirect_notice( 'err', 'type' );
		}

		$dir = self::ensure_priv_dir() . '/' . $uid;
		if ( ! is_dir( $dir ) ) {
			wp_mkdir_p( $dir );
		}
		$safe   = sanitize_file_name( $file['name'] );
		$unique = wp_unique_filename( $dir, $safe );
		$dest   = $dir . '/' . $unique;
		if ( ! @move_uploaded_file( $file['tmp_name'], $dest ) ) {
			$this->redirect_notice( 'err', 'ecriture' );
		}
		@chmod( $dest, 0600 );

		$label = sanitize_text_field( wp_unslash( $_POST['label'] ?? '' ) );
		$title = $label ? $label : $safe;

		$pid = wp_insert_post( array(
			'post_type'   => self::CPT_PIECE,
			'post_status' => 'private',
			'post_title'  => $title,
			'post_author' => $uid,
		) );
		if ( $pid && ! is_wp_error( $pid ) ) {
			update_post_meta( $pid, '_ag_jr_client', $uid );
			update_post_meta( $pid, '_ag_jr_path', self::PRIV_DIR . '/' . $uid . '/' . $unique );
			update_post_meta( $pid, '_ag_jr_filename', $safe );
			update_post_meta( $pid, '_ag_jr_mime', $check['type'] ? $check['type'] : '' );
			update_post_meta( $pid, '_ag_jr_size', (int) $file['size'] );
			update_post_meta( $pid, '_ag_jr_status', 'recu' );
		}

		$u = wp_get_current_user();
		$this->notify_cabinet( sprintf( '📎 %s a versé une pièce : « %s »', $u->display_name, $title ) );
		$this->redirect_notice( 'ok', 'verse' );
	}

	/* ============================================================
	 *  TÉLÉCHARGEMENT SÉCURISÉ (client propriétaire OU cabinet)
	 * ============================================================ */
	public function handle_download() {
		$id = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;
		if ( ! $id || ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( wp_unslash( $_GET['_wpnonce'] ), 'ag_jr_piece_dl_' . $id ) ) {
			wp_die( 'Lien invalide.', 403 );
		}
		if ( ! is_user_logged_in() ) {
			wp_die( 'Accès refusé.', 403 );
		}
		$post = get_post( $id );
		if ( ! $post || self::CPT_PIECE !== $post->post_type ) {
			wp_die( 'Introuvable.', 404 );
		}
		$owner   = (int) get_post_meta( $id, '_ag_jr_client', true );
		$is_cab  = current_user_can( AG_Recherche_Juridique::CAP );
		if ( ! $is_cab && get_current_user_id() !== $owner ) {
			wp_die( 'Accès refusé.', 403 );
		}
		$rel  = (string) get_post_meta( $id, '_ag_jr_path', true );
		$path = self::priv_path() . '/' . ltrim( str_replace( self::PRIV_DIR . '/', '', $rel ), '/' );
		// Garde anti-traversée.
		$base = realpath( self::priv_path() );
		$real = realpath( $path );
		if ( ! $real || ! $base || strpos( $real, $base ) !== 0 || ! is_file( $real ) ) {
			wp_die( 'Fichier introuvable.', 404 );
		}
		$name = (string) get_post_meta( $id, '_ag_jr_filename', true );
		$mime = (string) get_post_meta( $id, '_ag_jr_mime', true );
		nocache_headers();
		header( 'Content-Type: ' . ( $mime ? $mime : 'application/octet-stream' ) );
		header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $name ? $name : basename( $real ) ) . '"' );
		header( 'Content-Length: ' . filesize( $real ) );
		readfile( $real );
		exit;
	}

	private function dl_url( $pid ) {
		return wp_nonce_url( admin_url( 'admin-post.php?action=ag_jr_piece_dl&id=' . (int) $pid ), 'ag_jr_piece_dl_' . (int) $pid );
	}

	/* ============================================================
	 *  FRONT — SHORTCODE [ag_espace_client]
	 * ============================================================ */
	public function shortcode( $atts ) {
		$out  = '<div class="ag-jr-client">';
		$out .= $this->styles();
		$out .= $this->notices();

		if ( is_user_logged_in() && current_user_can( AG_Recherche_Juridique::CAP ) && ! $this->is_client() ) {
			// Le cabinet est connecté : on l'oriente, on ne montre pas l'UI client.
			$out .= '<div class="ag-jr-card"><h2>Espace client</h2><p>Vous êtes connecté en tant que cabinet. Les pièces versées par vos clients sont dans votre tableau de bord.</p>'
				. '<p><a class="ag-jr-cbtn" href="' . esc_url( admin_url( 'edit.php?post_type=' . self::CPT_PIECE ) ) . '">Voir les pièces clients →</a></p></div>';
			return $out . '</div>';
		}

		if ( $this->is_client() ) {
			$out .= $this->dashboard();
		} else {
			$out .= $this->auth_forms();
		}
		return $out . '</div>';
	}

	private function dashboard() {
		$u   = wp_get_current_user();
		$uid = $u->ID;
		$approved = (int) get_user_meta( $uid, 'ag_jr_client_approved', true );

		$logout = wp_nonce_url( admin_url( 'admin-post.php?action=ag_jr_client_logout' ), 'ag_jr_client_logout' );

		$h  = '<div class="ag-jr-card">';
		$h .= '<div class="ag-jr-chead"><div><h2>Bonjour ' . esc_html( $u->first_name ? $u->first_name : $u->display_name ) . '</h2>'
			. '<p class="ag-jr-muted">Votre espace confidentiel — secret professionnel garanti.</p></div>'
			. '<a class="ag-jr-link" href="' . esc_url( $logout ) . '">Se déconnecter</a></div>';

		if ( ! $approved ) {
			$h .= '<p class="ag-jr-warn">Votre compte est en attente de validation par le cabinet. Vous pourrez verser vos pièces dès qu\'il sera validé.</p>';
		}
		$h .= '</div>';

		// Formulaire de dépôt.
		if ( $approved ) {
			$h .= '<div class="ag-jr-card"><h3>📎 Verser une pièce</h3>'
				. '<form method="post" enctype="multipart/form-data" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">'
				. '<input type="hidden" name="action" value="ag_jr_client_upload">'
				. wp_nonce_field( 'ag_jr_client_upload', 'ag_jr_client_nonce', true, false )
				. '<label>Intitulé (facultatif)<input type="text" name="label" placeholder="Ex. Contrat de bail, attestation…"></label>'
				. '<label>Fichier<input type="file" name="piece" required accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.odt,.txt"></label>'
				. '<p class="ag-jr-muted">PDF, images, Word ou texte. 15 Mo maximum. Vos fichiers sont stockés de façon privée.</p>'
				. '<button class="ag-jr-cbtn" type="submit">Envoyer la pièce</button>'
				. '</form></div>';
		}

		// Liste des pièces du client.
		$pieces = get_posts( array(
			'post_type'      => self::CPT_PIECE,
			'post_status'    => 'private',
			'author'         => $uid,
			'posts_per_page' => 100,
			'orderby'        => 'date',
			'order'          => 'DESC',
		) );
		$h .= '<div class="ag-jr-card"><h3>Mes pièces (' . count( $pieces ) . ')</h3>';
		if ( ! $pieces ) {
			$h .= '<p class="ag-jr-muted">Aucune pièce versée pour l\'instant.</p>';
		} else {
			$labels = array( 'recu' => 'Reçue', 'en_cours' => 'En cours', 'traite' => 'Traitée' );
			$h .= '<ul class="ag-jr-pieces">';
			foreach ( $pieces as $p ) {
				$st  = (string) get_post_meta( $p->ID, '_ag_jr_status', true );
				$st  = isset( $labels[ $st ] ) ? $labels[ $st ] : 'Reçue';
				$h  .= '<li><span class="ag-jr-pname">' . esc_html( $p->post_title ) . '</span>'
					. '<span class="ag-jr-pdate">' . esc_html( get_the_date( 'd/m/Y', $p ) ) . '</span>'
					. '<span class="ag-jr-pstatus">' . esc_html( $st ) . '</span>'
					. '<a class="ag-jr-link" href="' . esc_url( $this->dl_url( $p->ID ) ) . '">Télécharger</a></li>';
			}
			$h .= '</ul>';
		}
		$h .= '</div>';
		return $h;
	}

	private function auth_forms() {
		$action = esc_url( admin_url( 'admin-post.php' ) );
		$h  = '<div class="ag-jr-card"><h2>Espace client du cabinet</h2>'
			. '<p class="ag-jr-muted">Connectez-vous ou créez votre compte pour suivre votre dossier et déposer vos pièces en toute confidentialité.</p></div>';

		$h .= '<div class="ag-jr-grid">';

		// Connexion.
		$h .= '<div class="ag-jr-card"><h3>Se connecter</h3>'
			. '<form method="post" action="' . $action . '">'
			. '<input type="hidden" name="action" value="ag_jr_client_login">'
			. wp_nonce_field( 'ag_jr_client_login', 'ag_jr_client_nonce', true, false )
			. '<label>Email<input type="email" name="email" required></label>'
			. '<label>Mot de passe<input type="password" name="password" required></label>'
			. '<button class="ag-jr-cbtn" type="submit">Connexion</button>'
			. '<p class="ag-jr-muted"><a href="' . esc_url( wp_lostpassword_url( $this->page_url() ) ) . '">Mot de passe oublié ?</a></p>'
			. '</form></div>';

		// Inscription.
		$h .= '<div class="ag-jr-card"><h3>Créer mon compte</h3>'
			. '<form method="post" action="' . $action . '">'
			. '<input type="hidden" name="action" value="ag_jr_client_register">'
			. wp_nonce_field( 'ag_jr_client_register', 'ag_jr_client_nonce', true, false )
			. '<div class="ag-jr-row"><label>Prénom<input type="text" name="prenom" required></label>'
			. '<label>Nom<input type="text" name="nom" required></label></div>'
			. '<label>Email<input type="email" name="email" required></label>'
			. '<label>Téléphone (facultatif)<input type="tel" name="tel"></label>'
			. '<label>Mot de passe (8 caractères min.)<input type="password" name="password" minlength="8" required></label>'
			. '<label class="ag-jr-check"><input type="checkbox" name="rgpd" value="1" required> J\'accepte que mes informations soient utilisées par le cabinet pour le suivi de mon dossier (RGPD). Hébergement en Union européenne, secret professionnel garanti.</label>'
			. '<button class="ag-jr-cbtn" type="submit">Créer mon espace</button>'
			. '</form></div>';

		$h .= '</div>';
		return $h;
	}

	private function notices() {
		$map_ok = array(
			'inscrit'       => 'Votre espace est créé, bienvenue !',
			'inscrit_valid' => 'Votre compte est créé. Il sera actif dès validation par le cabinet.',
			'connecte'      => 'Vous êtes connecté.',
			'verse'         => 'Votre pièce a bien été versée. Le cabinet en est informé.',
		);
		$map_err = array(
			'nonce'    => 'Session expirée, merci de réessayer.',
			'champs'   => 'Merci de remplir tous les champs (mot de passe 8 caractères, consentement RGPD).',
			'existe'   => 'Un compte existe déjà avec cet email. Connectez-vous.',
			'creation' => 'Impossible de créer le compte, réessayez.',
			'login'    => 'Email ou mot de passe incorrect.',
			'auth'     => 'Veuillez vous connecter.',
			'attente'  => 'Votre compte attend la validation du cabinet.',
			'fichier'  => 'Aucun fichier reçu.',
			'upload'   => 'Erreur pendant l\'envoi, réessayez.',
			'taille'   => 'Fichier trop lourd (15 Mo maximum).',
			'type'     => 'Type de fichier non autorisé (PDF, image, Word ou texte).',
			'ecriture' => 'Impossible d\'enregistrer le fichier.',
		);
		$h = '';
		if ( isset( $_GET['ag_ok'] ) && isset( $map_ok[ $_GET['ag_ok'] ] ) ) {
			$h .= '<div class="ag-jr-flash ag-jr-flash--ok">' . esc_html( $map_ok[ $_GET['ag_ok'] ] ) . '</div>';
		}
		if ( isset( $_GET['ag_err'] ) && isset( $map_err[ $_GET['ag_err'] ] ) ) {
			$h .= '<div class="ag-jr-flash ag-jr-flash--err">' . esc_html( $map_err[ $_GET['ag_err'] ] ) . '</div>';
		}
		return $h;
	}

	private function styles() {
		return '<style>
.ag-jr-client{max-width:920px;margin:0 auto;padding:8px 0;font-size:16px}
.ag-jr-client .ag-jr-card{background:rgba(255,255,255,.04);border:1px solid rgba(212,180,92,.25);border-radius:14px;padding:22px 24px;margin:0 0 18px}
.ag-jr-client h2{margin:0 0 6px}
.ag-jr-client h3{margin:0 0 14px;color:#D4B45C}
.ag-jr-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}
@media(max-width:680px){.ag-jr-grid{grid-template-columns:1fr}}
.ag-jr-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.ag-jr-client label{display:block;margin:0 0 12px;font-weight:600;font-size:.92rem}
.ag-jr-client input[type=text],.ag-jr-client input[type=email],.ag-jr-client input[type=tel],.ag-jr-client input[type=password],.ag-jr-client input[type=file]{display:block;width:100%;margin-top:5px;padding:11px 12px;border:1px solid rgba(212,180,92,.35);border-radius:9px;background:rgba(0,0,0,.15);color:inherit;font-weight:400;box-sizing:border-box}
.ag-jr-check{font-weight:400;font-size:.85rem;display:flex;gap:8px;align-items:flex-start}
.ag-jr-check input{margin-top:3px}
.ag-jr-cbtn{display:inline-block;border:0;cursor:pointer;padding:12px 26px;border-radius:999px;background:linear-gradient(135deg,#D4B45C,#F37A1F);color:#10100a;font-weight:800;text-decoration:none}
.ag-jr-link{color:#D4B45C;text-decoration:none;font-weight:700}
.ag-jr-muted{color:rgba(160,160,170,.95);font-size:.86rem}
.ag-jr-warn{color:#F1D98B;background:rgba(212,180,92,.12);border:1px solid rgba(212,180,92,.4);padding:10px 14px;border-radius:8px}
.ag-jr-chead{display:flex;justify-content:space-between;align-items:flex-start;gap:14px;flex-wrap:wrap}
.ag-jr-flash{padding:12px 16px;border-radius:9px;margin:0 0 16px;font-weight:600}
.ag-jr-flash--ok{background:rgba(40,167,69,.14);border:1px solid rgba(40,167,69,.5);color:#9be7ad}
.ag-jr-flash--err{background:rgba(220,53,69,.14);border:1px solid rgba(220,53,69,.5);color:#f3a3ab}
.ag-jr-pieces{list-style:none;padding:0;margin:0}
.ag-jr-pieces li{display:flex;align-items:center;gap:12px;flex-wrap:wrap;padding:11px 0;border-bottom:1px solid rgba(255,255,255,.08)}
.ag-jr-pname{font-weight:700;flex:1;min-width:140px}
.ag-jr-pdate,.ag-jr-pstatus{font-size:.82rem;color:rgba(160,160,170,.95)}
.ag-jr-pstatus{background:rgba(212,180,92,.14);border:1px solid rgba(212,180,92,.4);padding:2px 10px;border-radius:999px;color:#F1D98B}
</style>';
	}

	/* ============================================================
	 *  MENU PUBLIC : entrée vers l'espace client
	 * ============================================================ */
	public function nav_menu_link( $items, $args ) {
		$loc = isset( $args->theme_location ) ? $args->theme_location : '';
		if ( 'primary' !== $loc && 'menu-1' !== $loc && 'main' !== $loc ) {
			return $items;
		}
		// Le cabinet a déjà « Espace juridique » : on ne lui ajoute pas l'entrée client.
		if ( is_user_logged_in() && current_user_can( AG_Recherche_Juridique::CAP ) && ! $this->is_client() ) {
			return $items;
		}
		$page  = get_page_by_path( self::PAGE_SLUG );
		$url   = $page ? get_permalink( $page ) : $this->page_url();
		$label = $this->is_client() ? '👤 Mon espace' : '👤 Espace client';
		$cur   = ( $page && is_page( $page->ID ) ) ? ' current-menu-item' : '';
		return $items . '<li class="menu-item ag-jr-client-menu' . $cur . '"><a href="' . esc_url( $url ) . '">' . $label . '</a></li>';
	}

	/* ============================================================
	 *  CÔTÉ CABINET (wp-admin)
	 * ============================================================ */
	public function admin_menu() {
		add_submenu_page( 'ag-jr', 'Pièces clients', '📎 Pièces clients', AG_Recherche_Juridique::CAP, 'edit.php?post_type=' . self::CPT_PIECE );
		add_submenu_page( 'ag-jr', 'Clients', '👤 Clients', AG_Recherche_Juridique::CAP, 'users.php?role=' . self::ROLE );
	}

	public function piece_columns( $cols ) {
		$new = array();
		foreach ( $cols as $k => $v ) {
			$new[ $k ] = $v;
			if ( 'title' === $k ) {
				$new['ag_client'] = 'Client';
				$new['ag_dl']     = 'Fichier';
				$new['ag_status'] = 'Statut';
			}
		}
		return $new;
	}

	public function piece_column( $col, $post_id ) {
		if ( 'ag_client' === $col ) {
			$uid = (int) get_post_meta( $post_id, '_ag_jr_client', true );
			$u   = $uid ? get_userdata( $uid ) : null;
			echo $u ? esc_html( $u->display_name . ' (' . $u->user_email . ')' ) : '—';
		} elseif ( 'ag_dl' === $col ) {
			echo '<a href="' . esc_url( $this->dl_url( $post_id ) ) . '">⬇ Télécharger</a>';
		} elseif ( 'ag_status' === $col ) {
			$labels = array( 'recu' => 'Reçue', 'en_cours' => 'En cours', 'traite' => 'Traitée' );
			$st     = (string) get_post_meta( $post_id, '_ag_jr_status', true );
			echo esc_html( isset( $labels[ $st ] ) ? $labels[ $st ] : 'Reçue' );
		}
	}

	public function piece_metabox() {
		add_meta_box( 'ag_jr_piece_box', 'Pièce versée', array( $this, 'render_piece_box' ), self::CPT_PIECE, 'normal', 'high' );
	}

	public function render_piece_box( $post ) {
		wp_nonce_field( 'ag_jr_piece_save', 'ag_jr_piece_box_nonce' );
		$uid    = (int) get_post_meta( $post->ID, '_ag_jr_client', true );
		$u      = $uid ? get_userdata( $uid ) : null;
		$status = (string) get_post_meta( $post->ID, '_ag_jr_status', true );
		$dossier = (int) get_post_meta( $post->ID, '_ag_jr_dossier', true );
		$dossiers = get_posts( array( 'post_type' => AG_Recherche_Juridique::CPT_DOSSIER, 'posts_per_page' => 200, 'orderby' => 'title', 'order' => 'ASC' ) );

		echo '<p><strong>Client :</strong> ' . ( $u ? esc_html( $u->display_name . ' — ' . $u->user_email ) : '—' ) . '</p>';
		echo '<p><a class="button button-primary" href="' . esc_url( $this->dl_url( $post->ID ) ) . '">⬇ Télécharger la pièce</a></p>';

		echo '<p><label for="ag_jr_status"><strong>Statut</strong></label><br><select name="ag_jr_status" id="ag_jr_status">';
		foreach ( array( 'recu' => 'Reçue', 'en_cours' => 'En cours', 'traite' => 'Traitée' ) as $k => $lbl ) {
			echo '<option value="' . esc_attr( $k ) . '"' . selected( $status, $k, false ) . '>' . esc_html( $lbl ) . '</option>';
		}
		echo '</select></p>';

		echo '<p><label for="ag_jr_dossier"><strong>Rattacher à un dossier</strong></label><br><select name="ag_jr_dossier" id="ag_jr_dossier"><option value="0">— Aucun —</option>';
		foreach ( $dossiers as $d ) {
			echo '<option value="' . (int) $d->ID . '"' . selected( $dossier, $d->ID, false ) . '>' . esc_html( $d->post_title ) . '</option>';
		}
		echo '</select></p>';
	}

	public function piece_save( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! isset( $_POST['ag_jr_piece_box_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['ag_jr_piece_box_nonce'] ), 'ag_jr_piece_save' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( isset( $_POST['ag_jr_status'] ) ) {
			update_post_meta( $post_id, '_ag_jr_status', sanitize_key( wp_unslash( $_POST['ag_jr_status'] ) ) );
		}
		if ( isset( $_POST['ag_jr_dossier'] ) ) {
			update_post_meta( $post_id, '_ag_jr_dossier', (int) $_POST['ag_jr_dossier'] );
		}
	}

	/* ============================================================
	 *  PROTECTION : clients hors wp-admin
	 * ============================================================ */
	public function block_admin() {
		if ( wp_doing_ajax() ) {
			return;
		}
		if ( is_user_logged_in() && $this->is_client() && ! current_user_can( 'edit_posts' ) ) {
			wp_safe_redirect( $this->page_url() );
			exit;
		}
	}

	public function maybe_hide_admin_bar( $show ) {
		return ( is_user_logged_in() && $this->is_client() && ! current_user_can( 'edit_posts' ) ) ? false : $show;
	}
}
