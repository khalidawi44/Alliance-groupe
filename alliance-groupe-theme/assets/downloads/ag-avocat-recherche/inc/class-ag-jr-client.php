<?php
/**
 * AG Recherche Juridique — Espace CLIENT du cabinet.
 *
 * Deux espaces complémentaires :
 *   - Espace JURIDIQUE  → réservé au cabinet (avocat) : recherche, dossiers,
 *     arguments. (Géré par AG_Recherche_Juridique.)
 *   - Espace CLIENT     → ce module : le cabinet INVITE ses clients (création
 *     du compte + email avec lien de mot de passe). Le client se connecte à son
 *     espace perso et verse ses pièces (documents), en toute confidentialité.
 *     L'avocat les retrouve dans son wp-admin. Pas d'inscription publique.
 *
 * Sécurité / déontologie :
 *   - Comptes créés UNIQUEMENT sur invitation du cabinet (pas d'auto-inscription).
 *   - Fichiers stockés dans un dossier PRIVÉ (uploads/ag-cabinet-prive/),
 *     interdit d'accès direct (.htaccess) ; téléchargement uniquement via un
 *     point d'accès authentifié qui vérifie le propriétaire (client) ou le
 *     cabinet. Aucune URL publique.
 *   - Rôle dédié « ag_cabinet_client » : lecture seule, PAS d'accès wp-admin.
 *   - Rappel secret professionnel ; hébergement en Union européenne.
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
		// Pas d'inscription publique : les comptes sont créés sur INVITATION du
		// cabinet. Les clients ne font que se connecter.
		add_action( 'admin_post_nopriv_ag_jr_client_login',    array( $this, 'handle_login' ) );
		add_action( 'admin_post_ag_jr_client_login',           array( $this, 'handle_login' ) );
		add_action( 'admin_post_ag_jr_client_logout',          array( $this, 'handle_logout' ) );
		add_action( 'admin_post_ag_jr_client_upload',          array( $this, 'handle_upload' ) );
		add_action( 'admin_post_ag_jr_piece_dl',               array( $this, 'handle_download' ) );
		// Côté cabinet : invitation d'un nouveau client.
		add_action( 'admin_post_ag_jr_client_invite',          array( $this, 'handle_invite' ) );

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
	 *  TRAITEMENTS — INVITATION (cabinet) / CONNEXION / DÉCONNEXION
	 * ============================================================ */

	/**
	 * Le cabinet invite un client : création du compte (rôle client, pré-validé)
	 * + envoi d'un email d'invitation avec lien pour définir son mot de passe.
	 */
	public function handle_invite() {
		if ( ! current_user_can( AG_Recherche_Juridique::CAP ) ) {
			wp_die( 'Accès refusé.', 403 );
		}
		check_admin_referer( 'ag_jr_client_invite' );
		$prenom = sanitize_text_field( wp_unslash( $_POST['prenom'] ?? '' ) );
		$nom    = sanitize_text_field( wp_unslash( $_POST['nom'] ?? '' ) );
		$email  = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
		$tel    = sanitize_text_field( wp_unslash( $_POST['tel'] ?? '' ) );
		$back   = admin_url( 'admin.php?page=ag-jr-invite' );

		if ( ! $prenom || ! $nom || ! is_email( $email ) ) {
			wp_safe_redirect( add_query_arg( 'ag_inv', 'champs', $back ) );
			exit;
		}
		if ( email_exists( $email ) || username_exists( $email ) ) {
			wp_safe_redirect( add_query_arg( 'ag_inv', 'existe', $back ) );
			exit;
		}

		$uid = wp_insert_user( array(
			'user_login'   => $email,
			'user_email'   => $email,
			'user_pass'    => wp_generate_password( 24, true, true ),
			'first_name'   => $prenom,
			'last_name'    => $nom,
			'display_name' => trim( $prenom . ' ' . $nom ),
			'role'         => self::ROLE,
		) );
		if ( is_wp_error( $uid ) ) {
			wp_safe_redirect( add_query_arg( 'ag_inv', 'creation', $back ) );
			exit;
		}
		if ( $tel ) {
			update_user_meta( $uid, 'ag_jr_client_tel', $tel );
		}
		// Compte créé par le cabinet → de confiance, immédiatement actif.
		update_user_meta( $uid, 'ag_jr_client_approved', 1 );
		update_user_meta( $uid, 'ag_jr_client_invited_by', get_current_user_id() );

		// Lien « définir le mot de passe » (généré une fois, réutilisé pour
		// l'email ET le secours affiché dans l'admin si l'email ne part pas).
		$set_url = $this->build_set_password_url( $uid );
		$sent    = $this->send_invitation( $uid, $prenom, $email, $set_url );
		$this->notify_cabinet( sprintf( '👤 Client invité : %s (%s)', trim( $prenom . ' ' . $nom ), $email ) );

		// On mémorise le lien de secours pour l'afficher une fois au retour.
		set_transient( 'ag_jr_invite_' . get_current_user_id(), array(
			'email' => $email,
			'url'   => $set_url,
			'sent'  => $sent ? 1 : 0,
		), 600 );

		wp_safe_redirect( add_query_arg( 'ag_inv', 'ok', $back ) );
		exit;
	}

	/** Construit le lien standard WordPress de définition du mot de passe. */
	private function build_set_password_url( $uid ) {
		$user = get_userdata( $uid );
		if ( ! $user ) {
			return '';
		}
		$key = get_password_reset_key( $user );
		if ( is_wp_error( $key ) ) {
			return '';
		}
		return network_site_url( 'wp-login.php?action=rp&key=' . rawurlencode( $key ) . '&login=' . rawurlencode( $user->user_login ), 'login' );
	}

	/** Envoie l'email d'invitation avec le lien « définir mon mot de passe ». */
	private function send_invitation( $uid, $prenom, $email, $set_url ) {
		if ( ! $set_url ) {
			return false;
		}
		$blog = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		$msg  = "Bonjour {$prenom},\n\n";
		$msg .= "Votre cabinet vous a ouvert un espace client confidentiel pour suivre votre dossier et déposer vos pièces en toute sécurité.\n\n";
		$msg .= "1. Définissez votre mot de passe :\n{$set_url}\n\n";
		$msg .= "2. Connectez-vous ensuite à votre espace :\n" . $this->page_url() . "\n\n";
		$msg .= "Vos documents sont stockés de façon privée — secret professionnel garanti.\n\n";
		$msg .= "Le cabinet {$blog}.";
		return wp_mail( $email, "Votre espace client — {$blog}", $msg );
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
			. '<p class="ag-jr-muted">Accès réservé aux clients du cabinet. Connectez-vous avec l\'email et le mot de passe liés à l\'invitation reçue par courriel.</p></div>';

		// Connexion (les comptes sont créés sur invitation du cabinet).
		$h .= '<div class="ag-jr-card" style="max-width:460px;margin:0 auto"><h3>Se connecter</h3>'
			. '<form method="post" action="' . $action . '">'
			. '<input type="hidden" name="action" value="ag_jr_client_login">'
			. wp_nonce_field( 'ag_jr_client_login', 'ag_jr_client_nonce', true, false )
			. '<label>Email<input type="email" name="email" required></label>'
			. '<label>Mot de passe<input type="password" name="password" required></label>'
			. '<button class="ag-jr-cbtn" type="submit">Connexion</button>'
			. '<p class="ag-jr-muted"><a href="' . esc_url( wp_lostpassword_url( $this->page_url() ) ) . '">Mot de passe oublié ?</a></p>'
			. '<p class="ag-jr-muted">Pas encore d\'accès ? Votre cabinet vous enverra une invitation par email.</p>'
			. '</form></div>';

		return $h;
	}

	private function notices() {
		$map_ok = array(
			'connecte' => 'Vous êtes connecté.',
			'verse'    => 'Votre pièce a bien été versée. Le cabinet en est informé.',
		);
		$map_err = array(
			'nonce'    => 'Session expirée, merci de réessayer.',
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
		add_submenu_page( 'ag-jr', 'Inviter un client', '➕ Inviter un client', AG_Recherche_Juridique::CAP, 'ag-jr-invite', array( $this, 'page_invite' ) );
		add_submenu_page( 'ag-jr', 'Clients', '👤 Clients', AG_Recherche_Juridique::CAP, 'users.php?role=' . self::ROLE );
	}

	/** Page admin : inviter un nouveau client (création + email d'invitation). */
	public function page_invite() {
		if ( ! current_user_can( AG_Recherche_Juridique::CAP ) ) {
			return;
		}
		$msgs = array(
			'ok'       => array( 'updated', 'Invitation envoyée. Le client va recevoir un email pour définir son mot de passe.' ),
			'champs'   => array( 'error', 'Merci d\'indiquer le prénom, le nom et un email valide.' ),
			'existe'   => array( 'error', 'Un compte existe déjà avec cet email.' ),
			'creation' => array( 'error', 'Impossible de créer le compte, réessayez.' ),
		);
		echo '<div class="wrap"><h1>➕ Inviter un client</h1>';
		if ( isset( $_GET['ag_inv'] ) && isset( $msgs[ $_GET['ag_inv'] ] ) ) {
			$m = $msgs[ $_GET['ag_inv'] ];
			echo '<div class="notice notice-' . esc_attr( $m[0] ) . ' is-dismissible"><p>' . esc_html( $m[1] ) . '</p></div>';
		}
		// Lien de secours : affiché une fois après une invitation, surtout utile
		// si l'email n'est pas parti (hébergeur sans SMTP). À transmettre au
		// client par un canal sûr (en main propre, SMS…).
		$tkey = 'ag_jr_invite_' . get_current_user_id();
		$inv  = get_transient( $tkey );
		if ( is_array( $inv ) && ! empty( $inv['url'] ) ) {
			delete_transient( $tkey );
			$warn = empty( $inv['sent'] );
			echo '<div class="notice notice-' . ( $warn ? 'warning' : 'info' ) . '"><p>'
				. ( $warn
					? '⚠️ L\'email n\'a peut-être pas pu partir (hébergement sans SMTP). '
					: '✉️ Email d\'invitation envoyé à <strong>' . esc_html( $inv['email'] ) . '</strong>. ' )
				. 'Lien de secours « définir le mot de passe » à transmettre au client si besoin (valable ~24 h) :</p>'
				. '<p><input type="text" class="large-text code" readonly onclick="this.select()" value="' . esc_attr( $inv['url'] ) . '"></p>'
				. '<p class="description">À envoyer par un canal sûr. Ce lien ne sera plus affiché ensuite.</p></div>';
		}
		echo '<p>Créez l\'accès d\'un client à son espace confidentiel. Il recevra un email pour choisir son mot de passe, puis pourra déposer ses pièces.</p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="max-width:480px">';
		echo '<input type="hidden" name="action" value="ag_jr_client_invite">';
		wp_nonce_field( 'ag_jr_client_invite' );
		echo '<table class="form-table"><tbody>';
		echo '<tr><th><label for="ag-inv-prenom">Prénom</label></th><td><input name="prenom" id="ag-inv-prenom" type="text" class="regular-text" required></td></tr>';
		echo '<tr><th><label for="ag-inv-nom">Nom</label></th><td><input name="nom" id="ag-inv-nom" type="text" class="regular-text" required></td></tr>';
		echo '<tr><th><label for="ag-inv-email">Email</label></th><td><input name="email" id="ag-inv-email" type="email" class="regular-text" required></td></tr>';
		echo '<tr><th><label for="ag-inv-tel">Téléphone (facultatif)</label></th><td><input name="tel" id="ag-inv-tel" type="tel" class="regular-text"></td></tr>';
		echo '</tbody></table>';
		submit_button( 'Envoyer l\'invitation' );
		echo '</form></div>';
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
