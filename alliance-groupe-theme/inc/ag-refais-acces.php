<?php
/**
 * ag-refais-acces.php — Le mur d'email de « Refais mon site par l'IA ».
 *
 * Deux mécanismes, un seul module :
 *
 * A. MUR D'EMAIL (double opt-in). Le visiteur voit sa maquette immédiatement,
 *    mais floutée. Pour l'obtenir nette, la garder 30 jours et la partager, il
 *    laisse son adresse ; il reçoit un email de confirmation ; le clic sur ce
 *    lien débloque tout. On ne récolte donc que des adresses VALIDÉES, seules
 *    exploitables ensuite pour la prospection (art. L.34-5 CPCE / RGPD).
 *
 * B. ATTRIBUTION AMBASSADEUR. Si le visiteur est arrivé par le lien d'un
 *    ambassadeur (`?ref=XX`, cookie `ag_ref` déjà posé par ag-espaces.php), le
 *    prospect créé lui est attribué automatiquement. C'est ce qui rend l'outil
 *    utile à l'équipe : chacun a quelque chose à envoyer par SMS, et le lead
 *    lui revient.
 *
 * Le flou est une COURTOISIE, pas un verrou : quelqu'un qui sait ouvrir les
 * outils de développement verra la maquette. Ce n'est pas grave — la valeur
 * réellement réservée est le lien permanent, l'envoi par email et la suite du
 * parcours. Ne jamais présenter ce flou comme une protection.
 *
 * Réutilise : le générateur (ag-refais-mon-site.php), le CRM
 * (ag_prospect_add_record), les emails brandés (ag_email_wrap/_button), les
 * notifications (ag_push), l'annuaire ambassadeurs (ag_ambassadeur_by_ref).
 * Rien n'est réimplémenté ici.
 *
 * @package Alliance_Groupe
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Index « email du visiteur -> maquette confirmee », lu au moment du paiement. */
if ( ! defined( 'AG_REFAIS_OPT_INDEX' ) ) { define( 'AG_REFAIS_OPT_INDEX', 'ag_refais_index_email' ); }

/** Journal des commandes payees de la micro-offre. */
if ( ! defined( 'AG_REFAIS_OPT_CMD' ) ) { define( 'AG_REFAIS_OPT_CMD', 'ag_refais_commandes' ); }

/** Durée de conservation d'une maquette et de son lien partageable. */
if ( ! defined( 'AG_REFAIS_GARDE_JOURS' ) ) { define( 'AG_REFAIS_GARDE_JOURS', 30 ); }

/** Clé de stockage d'une maquette. */
function ag_refais_key( $token ) {
	return 'ag_refais_mk_' . preg_replace( '/[^a-f0-9]/', '', (string) $token );
}

/** Jeton aléatoire non devinable (32 caractères hexadécimaux). */
function ag_refais_new_token() {
	return bin2hex( random_bytes( 16 ) );
}

/** Lit une maquette conservée. Renvoie null si expirée ou inconnue. */
function ag_refais_get( $token ) {
	if ( ! preg_match( '/^[a-f0-9]{32}$/', (string) $token ) ) { return null; }
	$mk = get_transient( ag_refais_key( $token ) );
	return is_array( $mk ) ? $mk : null;
}

/** Écrit (ou met à jour) une maquette conservée. */
function ag_refais_put( $token, array $mk ) {
	set_transient( ag_refais_key( $token ), $mk, AG_REFAIS_GARDE_JOURS * DAY_IN_SECONDS );
}

/* ── A. Le générateur conserve sa maquette et renvoie un jeton ───────────── */
add_filter( 'ag_refais_result_payload', function ( $payload, $html, $page ) {
	$token = ag_refais_new_token();
	ag_refais_put( $token, array(
		'html'      => (string) $html,
		'title'     => (string) ( $page['title'] ?? '' ),
		'src'       => (string) ( $page['url'] ?? '' ),
		'confirme'  => false,
		'email'     => '',
		'ref'       => isset( $_COOKIE['ag_ref'] ) ? preg_replace( '/[^A-Za-z0-9]/', '', wp_unslash( $_COOKIE['ag_ref'] ) ) : '',
		'ts'        => time(),
	) );
	$payload['token']  = $token;
	$payload['locked'] = true;
	return $payload;
}, 10, 3 );

/* ── A. Le visiteur laisse son adresse : on envoie la confirmation ───────── */
function ag_refais_optin() {
	if ( ! isset( $_POST['_n'] ) || ! wp_verify_nonce( $_POST['_n'], 'ag_refais' ) ) {
		wp_send_json_error( array( 'msg' => 'Session expirée, recharge la page.' ) );
	}
	$token = sanitize_text_field( wp_unslash( $_POST['token'] ?? '' ) );
	$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$name  = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );

	$mk = ag_refais_get( $token );
	if ( ! $mk ) {
		wp_send_json_error( array( 'msg' => 'Cette maquette a expiré. Relance la génération.' ) );
	}
	if ( ! is_email( $email ) ) {
		wp_send_json_error( array( 'msg' => 'Cette adresse email ne semble pas valide.' ) );
	}

	$mk['email'] = $email;
	$mk['name']  = $name;
	ag_refais_put( $token, $mk );

	$lien = add_query_arg( 'ag_refais_ok', $token, home_url( '/refais-mon-site' ) );
	$site = $mk['src'] ? wp_parse_url( $mk['src'], PHP_URL_HOST ) : 'ton site';

	/* D. La suite payante, annoncée sobrement dans l'email de confirmation.
	   Elle ne remplace pas le lien : on ne vend rien avant d'avoir donné. */
	$offre      = function_exists( 'ag_refais_boost_offre' ) ? ag_refais_boost_offre() : null;
	$offre_html = '';
	if ( $offre ) {
		$offre_html = '<p style="font-family:Arial,sans-serif;font-size:14px;line-height:1.6;color:#e8e6e0;'
			. 'border-top:1px solid rgba(212,180,92,.2);padding-top:16px;margin-top:20px;">'
			. '<strong style="color:#D4B45C;">' . esc_html( $offre['nom'] ) . ' — ' . esc_html( $offre['prix'] )
			. esc_html( (string) ( $offre['unite'] ?? '' ) ) . '</strong><br>'
			. esc_html( $offre['desc'] ) . ' ' . esc_html( $offre['delai'] ) . '.<br>'
			. '<span style="color:#b0b0bc;font-size:13px;">' . esc_html( $offre['deduit'] ) . '</span><br>'
			. '<span style="color:#b0b0bc;font-size:13px;">' . esc_html( (string) ( $offre['engagement'] ?? '' ) ) . '</span></p>';
	}

	$corps = '<p style="font-family:Arial,sans-serif;font-size:15px;line-height:1.6;color:#e8e6e0;">'
		. 'Bonjour,</p>'
		. '<p style="font-family:Arial,sans-serif;font-size:15px;line-height:1.6;color:#e8e6e0;">'
		. 'Voici la maquette que notre IA a imaginée à partir de <strong>' . esc_html( (string) $site ) . '</strong>. '
		. 'Un seul clic pour la voir en grand, sans flou :</p>'
		. ag_email_button( 'Voir ma maquette', $lien )
		. '<p style="font-family:Arial,sans-serif;font-size:13px;line-height:1.6;color:#b0b0bc;">'
		. 'Ce lien reste valable ' . (int) AG_REFAIS_GARDE_JOURS . ' jours. Tu peux le partager avec qui tu veux.<br>'
		. 'Rappel : nous n\'avons jamais touché à ton vrai site. C\'est une simulation.</p>'
		. $offre_html
		. '<p style="font-family:Arial,sans-serif;font-size:13px;line-height:1.6;color:#7a7a86;">'
		. 'Si tu n\'as rien demandé, ignore simplement ce message : sans ce clic, ton adresse n\'est pas enregistrée.</p>';

	$ok = wp_mail(
		$email,
		'Ta maquette est prête — un clic pour la voir',
		ag_email_wrap( 'Ta maquette est prête', $corps ),
		array( 'Content-Type: text/html; charset=UTF-8' )
	);

	if ( ! $ok ) {
		wp_send_json_error( array( 'msg' => "L'email n'a pas pu partir. Réessaie dans un instant." ) );
	}
	wp_send_json_success( array( 'msg' => 'Regarde ta boîte mail : un clic et la maquette s\'ouvre en grand. Pense aux indésirables.' ) );
}
add_action( 'wp_ajax_ag_refais_optin', 'ag_refais_optin' );
add_action( 'wp_ajax_nopriv_ag_refais_optin', 'ag_refais_optin' );

/* ── A + B. Le clic dans l'email confirme, crée le lead, attribue, débloque ─ */
function ag_refais_confirme() {
	if ( empty( $_GET['ag_refais_ok'] ) ) { return; }
	$token = sanitize_text_field( wp_unslash( $_GET['ag_refais_ok'] ) );
	$mk    = ag_refais_get( $token );

	if ( ! $mk ) {
		wp_safe_redirect( add_query_arg( 'ag_refais_err', 'expire', home_url( '/refais-mon-site' ) ) );
		exit;
	}

	// Première confirmation seulement : on ne recrée pas le prospect à chaque
	// ouverture du lien (le client le partage, il sera cliqué plusieurs fois).
	if ( empty( $mk['confirme'] ) ) {
		$mk['confirme'] = true;
		$mk['date_ok']  = time();
		ag_refais_put( $token, $mk );

		// B. Attribution à l'ambassadeur dont le lien a amené le visiteur.
		$owner_email = '';
		$owner_name  = '';
		if ( ! empty( $mk['ref'] ) && function_exists( 'ag_ambassadeur_by_ref' ) ) {
			$amb = ag_ambassadeur_by_ref( $mk['ref'] );
			if ( $amb ) {
				$owner_email = (string) ( $amb['email'] ?? '' );
				$owner_name  = (string) ( $amb['name'] ?? '' );
			}
		}

		$host = $mk['src'] ? (string) wp_parse_url( $mk['src'], PHP_URL_HOST ) : '';
		$nom  = $mk['name'] ? $mk['name'] : ( $host ? $host : $mk['email'] );

		if ( function_exists( 'ag_prospect_add_record' ) ) {
			ag_prospect_add_record( array(
				'name'        => $nom,
				'email'       => $mk['email'],
				'website'     => $mk['src'],
				'status'      => 'interesse',
				'source'      => 'refais-mon-site',
				'owner_email' => $owner_email,
				'owner_name'  => $owner_name,
				'notes'       => 'Maquette IA générée et adresse CONFIRMÉE (double opt-in). Maquette : '
					. add_query_arg( 'ag_refais_voir', $token, home_url( '/refais-mon-site' ) )
					. ( $owner_email ? ' — amené par ' . $owner_name : '' ),
			) );
		}

		if ( function_exists( 'ag_push' ) ) {
			ag_push( 'Maquette confirmée : ' . $mk['email'] . ( $host ? ' — ' . $host : '' )
				. ( $owner_name ? ' (via ' . $owner_name . ')' : '' ) );
		}

		/*
		 * Index « email -> maquette ». Sans lui, un paiement PayPal ne porte
		 * qu'une adresse : impossible de savoir QUELLE maquette le client
		 * vient d'acheter, ni de retrouver son ambassadeur. On garde les 200
		 * dernieres, la fenetre entre la maquette et l'achat se compte en
		 * jours.
		 */
		$index = (array) get_option( AG_REFAIS_OPT_INDEX, array() );
		$index[ strtolower( $mk['email'] ) ] = array(
			'token' => $token,
			'site'  => (string) $mk['src'],
			'name'  => (string) ( $mk['name'] ?? '' ),
			'ref'   => (string) ( $mk['ref'] ?? '' ),
			'ts'    => time(),
		);
		if ( count( $index ) > 200 ) {
			uasort( $index, function ( $a, $b ) { return (int) $b['ts'] - (int) $a['ts']; } );
			$index = array_slice( $index, 0, 200, true );
		}
		update_option( AG_REFAIS_OPT_INDEX, $index, false );
	}

	wp_safe_redirect( add_query_arg( 'ag_refais_voir', $token, home_url( '/refais-mon-site' ) ) );
	exit;
}
add_action( 'template_redirect', 'ag_refais_confirme', 1 );

/* ── A. La visionneuse : la maquette nette, en grand, partageable ────────── */
function ag_refais_voir() {
	if ( empty( $_GET['ag_refais_voir'] ) ) { return; }
	$token = sanitize_text_field( wp_unslash( $_GET['ag_refais_voir'] ) );
	$mk    = ag_refais_get( $token );

	if ( ! $mk || empty( $mk['confirme'] ) ) {
		wp_safe_redirect( add_query_arg( 'ag_refais_err', 'expire', home_url( '/refais-mon-site' ) ) );
		exit;
	}

	$titre = $mk['title'] ? $mk['title'] : 'Ta maquette';
	$host  = $mk['src'] ? (string) wp_parse_url( $mk['src'], PHP_URL_HOST ) : '';

	/* D. La micro-offre qui suit la maquette. Le prix vient de ag-offres.php,
	   source unique : il ne doit jamais etre reecrit ici. */
	$offre = function_exists( 'ag_refais_boost_offre' ) ? ag_refais_boost_offre() : null;
	if ( ! $offre ) {
		$offre = array(
			'nom' => 'La mettre en ligne', 'prix' => '', 'unite' => '', 'delai' => '',
			'desc' => '', 'deduit' => '', 'engagement' => '', 'feats' => array(),
			'compare' => array(), 'payable' => false,
			'url' => home_url( '/sites-express' ),
		);
	}

	// La maquette est affichée dans un iframe en bac à sable, comme à la
	// génération : elle a beau être désinfectée, on ne l'exécute jamais
	// dans le contexte du site.
	nocache_headers();
	header( 'Content-Type: text/html; charset=utf-8' );
	header( 'X-Robots-Tag: noindex, nofollow', true );
	?>
<!doctype html>
<html lang="fr"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title><?php echo esc_html( $titre ); ?> — maquette Alliance Groupe</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{background:#05050a;color:#eef1f6;font-family:Inter,-apple-system,"Segoe UI",Roboto,sans-serif}
  .bar{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;
       padding:14px 20px;border-bottom:1px solid rgba(212,180,92,.22);background:#0b0b12}
  .bar b{font-size:.95rem;color:#fff;font-weight:600}
  .bar small{display:block;color:#9aa3b4;font-size:.78rem;font-weight:400}
  .bar a{display:inline-block;background:linear-gradient(120deg,#d4b45c,#f4d06f);color:#1a1206;
         font-weight:800;text-decoration:none;border-radius:999px;padding:11px 22px;font-size:.9rem}
  iframe{display:block;width:100%;height:calc(100svh - 66px);border:0;background:#fff}
  @media(max-width:640px){
    iframe{height:calc(100svh - 104px)}
    .cmp__r{grid-template-columns:1fr;gap:2px;padding:9px 0}
  }
  .off{padding:26px 20px 34px;border-top:1px solid rgba(212,180,92,.22);background:#0b0b12}
  .off__in{max-width:640px;margin:0 auto;text-align:center}
  .off h2{font-size:1.25rem;font-weight:700;color:#fff;margin-bottom:6px}
  .off__p{color:#9aa3b4;font-size:.9rem;line-height:1.55;margin-bottom:16px}
  .off__prix{font-size:2.1rem;font-weight:800;color:#f4d06f;line-height:1}
  .off__prix span{display:block;font-size:.78rem;font-weight:600;color:#9aa3b4;margin-top:5px}
  .off ul{list-style:none;margin:18px auto;max-width:400px;text-align:left}
  .off li{position:relative;padding:6px 0 6px 26px;color:#cdd4e2;font-size:.88rem;line-height:1.45}
  .off li::before{content:"\2713";position:absolute;left:0;top:6px;color:#d4b45c;font-weight:800}
  .off__note{color:#8b93a5;font-size:.76rem;margin-top:12px;line-height:1.45}
  .off__prix small{font-size:.9rem;font-weight:600;color:#9aa3b4;margin-left:6px}
  .cmp{max-width:560px;margin:24px auto 0;text-align:left}
  .cmp__h{font-size:.72rem;letter-spacing:.18em;text-transform:uppercase;color:#9aa3b4;
          font-weight:700;text-align:center;margin-bottom:14px}
  .cmp__r{display:grid;grid-template-columns:1fr 1fr;gap:10px;padding:10px 0;
          border-bottom:1px solid rgba(255,255,255,.07)}
  .cmp__r:last-child{border-bottom:0}
  .cmp__e,.cmp__n{font-size:.83rem;line-height:1.45}
  .cmp__e{color:#8b93a5}
  .cmp__e::before{content:"\2717 ";color:#6b7280;font-weight:700}
  .cmp__n{color:#eef1f6}
  .cmp__n::before{content:"\2713 ";color:#d4b45c;font-weight:800}
  .cmp__leg{margin-top:14px;font-size:.72rem;color:#6f7789;line-height:1.5;text-align:center}
</style>
</head><body>
  <div class="bar">
    <b>&#10024; Ta maquette<small><?php echo $host ? 'imagin&eacute;e &agrave; partir de ' . esc_html( $host ) : 'propos&eacute;e par l&rsquo;IA'; ?> &middot; lien valable <?php echo (int) AG_REFAIS_GARDE_JOURS; ?> jours</small></b>
    <a href="#offre"><?php echo esc_html( $offre['nom'] ); ?> &middot; <?php echo esc_html( $offre['prix'] ); ?> &rarr;</a>
  </div>
  <iframe sandbox referrerpolicy="no-referrer" title="Maquette g&eacute;n&eacute;r&eacute;e par l&rsquo;IA"
    srcdoc="<?php echo esc_attr( '<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">' . $mk['html'] ); ?>"></iframe>

  <section class="off" id="offre">
    <div class="off__in">
      <div class="off__prix"><?php echo esc_html( $offre['prix'] ); ?><?php if ( ! empty( $offre['unite'] ) ) : ?><small><?php echo esc_html( $offre['unite'] ); ?></small><?php endif; ?><span><?php echo esc_html( $offre['delai'] ); ?></span></div>
      <h2 style="margin-top:14px"><?php echo esc_html( $offre['nom'] ); ?></h2>
      <p class="off__p"><?php echo esc_html( $offre['desc'] ); ?></p>
      <ul>
        <?php foreach ( (array) $offre['feats'] as $f ) : ?>
          <li><?php echo esc_html( $f ); ?></li>
        <?php endforeach; ?>
      </ul>
      <a href="<?php echo esc_url( $offre['url'] ); ?>"><?php echo $offre['payable'] ? 'La mettre en ligne &rarr;' : 'En parler au t&eacute;l&eacute;phone &rarr;'; ?></a>
      <p class="off__note"><?php echo esc_html( $offre['deduit'] ); ?></p>
      <?php if ( ! empty( $offre['engagement'] ) ) : ?>
        <p class="off__note"><?php echo esc_html( $offre['engagement'] ); ?></p>
      <?php endif; ?>

      <?php if ( ! empty( $offre['compare'] ) ) : ?>
      <div class="cmp">
        <div class="cmp__h">Ailleurs &middot; Ici</div>
        <?php foreach ( (array) $offre['compare'] as $c ) : ?>
          <div class="cmp__r">
            <div class="cmp__e"><?php echo esc_html( $c['eux'] ); ?></div>
            <div class="cmp__n"><?php echo esc_html( $c['nous'] ); ?></div>
          </div>
        <?php endforeach; ?>
        <p class="cmp__leg">Chez les cr&eacute;ateurs de site en ligne, comptez de 6&nbsp;&euro; &agrave; 17&nbsp;&euro; par mois, plus le nom de domaine renouvel&eacute; 11 &agrave; 15&nbsp;&euro; par an&nbsp;&mdash; et le travail reste &agrave; faire. Tarifs relev&eacute;s en septembre 2026.</p>
      </div>
      <?php endif; ?>
    </div>
  </section>
</body></html>
	<?php
	exit;
}
add_action( 'template_redirect', 'ag_refais_voir', 2 );

/* ── Le mur, côté navigateur ─────────────────────────────────────────────
   Un seul script sert les DEUX endroits où l'outil vit (l'accueil et la page
   /refais-mon-site). Chaque façade se contente d'annoncer son résultat par
   l'événement `ag-refais:result` ; le mur se pose tout seul par-dessus.      */
add_action( 'wp_footer', function () {
	if ( is_admin() ) { return; }
	if ( ! is_front_page() && ! is_page( 'refais-mon-site' ) ) { return; }
	?>
	<style>
	.agrg{position:absolute;inset:0;z-index:5;display:flex;align-items:center;justify-content:center;
	      padding:18px;text-align:center;border-radius:16px;
	      background:linear-gradient(180deg,rgba(5,5,10,.35),rgba(5,5,10,.88))}
	.agrg__box{width:100%;max-width:340px}
	.agrg__t{font-size:1.05rem;font-weight:700;color:#fff;margin-bottom:6px}
	.agrg__p{font-size:.84rem;line-height:1.5;color:#cdd4e2;margin-bottom:14px}
	.agrg input{width:100%;padding:12px 15px;margin-bottom:8px;font-size:.95rem;color:#fff;
	            background:rgba(255,255,255,.08);border:1px solid rgba(212,180,92,.4);
	            border-radius:11px;outline:none}
	.agrg input::placeholder{color:#8b93a5}
	.agrg input:focus{border-color:#d4b45c;background:rgba(255,255,255,.12)}
	.agrg button{width:100%;padding:12px 18px;font:inherit;font-weight:800;font-size:.92rem;
	             color:#1a1206;background:linear-gradient(120deg,#d4b45c,#f4d06f);
	             border:0;border-radius:999px;cursor:pointer}
	.agrg button:disabled{opacity:.55;cursor:not-allowed}
	.agrg__m{margin-top:10px;font-size:.82rem;line-height:1.45;font-weight:600;min-height:18px;color:#f4d06f}
	.agrg__l{margin-top:9px;font-size:.72rem;color:#8b93a5;line-height:1.4}
	.agrg-w{position:relative;display:block}
	iframe.agrg-flou{filter:blur(7px) saturate(.85);pointer-events:none}
	</style>
	<script>
	(function(){
		var AJAX = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>,
		    N    = <?php echo wp_json_encode( wp_create_nonce( 'ag_refais' ) ); ?>;

		document.addEventListener('ag-refais:result', function(e){
			var d = e.detail || {};
			if (!d.token || !d.frame) return;

			var frame = d.frame;
			if (!frame || !frame.parentNode) return;

			/* On enveloppe l'iframe pour que le mur se pose exactement dessus,
			   et pas sur le libelle de la colonne. Si l'enveloppe existe deja
			   (deuxieme generation), on la reutilise. */
			var host = frame.parentNode;
			if (!host.classList.contains('agrg-w')) {
				var w = document.createElement('div');
				w.className = 'agrg-w';
				host.insertBefore(w, frame);
				w.appendChild(frame);
				host = w;
			}
			frame.classList.add('agrg-flou');

			var old = host.querySelector('.agrg');
			if (old) old.remove();

			var g = document.createElement('div');
			g.className = 'agrg';
			g.innerHTML =
				'<div class="agrg__box">'
				+ '<div class="agrg__t">Ta maquette est prête</div>'
				+ '<div class="agrg__p">Laisse ton adresse : tu la reçois en grand, nette, avec un lien à garder.</div>'
				+ '<input type="text" class="agrg-n" placeholder="Ton prénom / entreprise" autocomplete="name">'
				+ '<input type="email" class="agrg-e" placeholder="Ton email" autocomplete="email" inputmode="email">'
				+ '<button type="button" class="agrg-go">Voir ma maquette →</button>'
				+ '<div class="agrg__m" role="status" aria-live="polite"></div>'
				+ '<div class="agrg__l">Un email de confirmation part immédiatement. Sans ce clic, ton adresse n’est pas enregistrée.</div>'
				+ '</div>';
			host.appendChild(g);

			var go = g.querySelector('.agrg-go'), msg = g.querySelector('.agrg__m');
			go.addEventListener('click', function(){
				var name  = g.querySelector('.agrg-n').value.trim(),
				    email = g.querySelector('.agrg-e').value.trim();
				if (!email || email.indexOf('@') < 1) {
					msg.style.color = '#ffb3b3';
					msg.textContent = 'Indique une adresse email valide.';
					return;
				}
				go.disabled = true;
				msg.style.color = '#f4d06f';
				msg.textContent = 'Envoi en cours…';

				var fd = new FormData();
				fd.append('action', 'ag_refais_optin'); fd.append('_n', N);
				fd.append('token', d.token); fd.append('email', email); fd.append('name', name);

				fetch(AJAX, { method:'POST', body:fd })
					.then(function(r){ return r.json(); })
					.then(function(j){
						go.disabled = false;
						if (j && j.success) {
							msg.style.color = '#7cffb0';
							msg.textContent = j.data.msg;
							go.style.display = 'none';
						} else {
							msg.style.color = '#ffb3b3';
							msg.textContent = (j && j.data && j.data.msg) || 'Erreur, réessaie.';
						}
					})
					.catch(function(){
						go.disabled = false;
						msg.style.color = '#ffb3b3';
						msg.textContent = 'Connexion interrompue, réessaie.';
					});
			});
		});
	})();
	</script>
	<?php
}, 20 );

/* ── Le paiement — la piece qui manquait ──────────────────────────────────
   Le lien PayPal encaissait, mais RIEN n'ecoutait derriere : ni trace, ni
   notification, ni rattachement a la maquette achetee. Le client payait et
   Fabrice ne le savait pas. Six autres modules de la maison ecoutent deja
   `ag_paypal_payment_verified` ; celui-ci s'y branche a son tour.          */

/** Le montant annonce de la micro-offre, en nombre (« 9,90 € » -> 9.90). */
function ag_refais_prix_num() {
	$o = function_exists( 'ag_refais_boost_offre' ) ? ag_refais_boost_offre() : array();
	$p = isset( $o['prix'] ) ? (string) $o['prix'] : '';
	$p = str_replace( array( ' ', ' ', '€', 'EUR' ), '', $p );
	$p = str_replace( ',', '.', $p );
	return (float) $p;
}

/** Les commandes payees, de la plus recente a la plus ancienne. */
function ag_refais_commandes() {
	$l = (array) get_option( AG_REFAIS_OPT_CMD, array() );
	return $l;
}

add_action( 'ag_paypal_payment_verified', function ( $amount, $email, $txn = '', $type = '', $resource = array() ) {

	$attendu = ag_refais_prix_num();
	if ( $attendu <= 0 ) { return; }

	/*
	 * Tolerance SERREE, a 5 centimes, la ou le reste de la maison en accorde
	 * 50. Raison : la marketplace de composants vend un palier a 9,99 EUR et
	 * l'offre est a 9,90 EUR. Avec 50 centimes, l'achat d'un composant serait
	 * pris pour une commande de mise en ligne, avec email au client et tache
	 * pour Fabrice a la cle. Un abonnement a montant fixe tombe au centime :
	 * 5 centimes suffisent largement.
	 */
	if ( abs( (float) $amount - $attendu ) > 0.05 ) { return; }

	$email = sanitize_email( (string) $email );
	$txn   = (string) $txn;

	// Un abonnement rejoue le meme evenement chaque mois, et PayPal peut
	// renvoyer deux fois la meme notification. On ne compte jamais deux fois
	// la meme transaction.
	$cmds = (array) get_option( AG_REFAIS_OPT_CMD, array() );
	foreach ( $cmds as $c ) {
		if ( '' !== $txn && (string) ( $c['txn'] ?? '' ) === $txn ) { return; }
	}

	// A quelle maquette ce paiement correspond-il ?
	$index = (array) get_option( AG_REFAIS_OPT_INDEX, array() );
	$fiche = $index[ strtolower( $email ) ] ?? array();

	$token = (string) ( $fiche['token'] ?? '' );
	$site  = (string) ( $fiche['site'] ?? '' );
	$nom   = (string) ( $fiche['name'] ?? '' );
	$ref   = (string) ( $fiche['ref'] ?? '' );
	$lien  = $token ? add_query_arg( 'ag_refais_voir', $token, home_url( '/refais-mon-site' ) ) : '';

	// Renouvellement mensuel : meme email deja servi -> on note, on ne
	// redemande pas de mise en ligne.
	$deja = false;
	foreach ( $cmds as $c ) {
		if ( strtolower( (string) ( $c['email'] ?? '' ) ) === strtolower( $email ) ) { $deja = true; break; }
	}

	$cmds[] = array(
		'email'     => $email,
		'nom'       => $nom,
		'site'      => $site,
		'token'     => $token,
		'lien'      => $lien,
		'ref'       => $ref,
		'montant'   => (float) $amount,
		'txn'       => $txn,
		'type'      => (string) $type,
		'renouv'    => $deja ? 1 : 0,
		'statut'    => $deja ? 'renouvellement' : 'a_mettre_en_ligne',
		'ts'        => time(),
	);
	update_option( AG_REFAIS_OPT_CMD, array_slice( $cmds, -500 ), false );

	// Le prospect devient client dans le CRM.
	if ( ! $deja && function_exists( 'ag_prospect_add_record' ) ) {
		ag_prospect_add_record( array(
			'name'    => $nom ? $nom : ( $site ? (string) wp_parse_url( $site, PHP_URL_HOST ) : $email ),
			'email'   => $email,
			'website' => $site,
			'status'  => 'client',
			'source'  => 'refais-mon-site',
			'notes'   => 'A paye la mise en ligne de sa maquette IA. Maquette : ' . ( $lien ? $lien : '(non retrouvee)' ),
		) );
	}

	$hote = $site ? (string) wp_parse_url( $site, PHP_URL_HOST ) : '';

	// Fabrice : ce qu'il y a a faire, et sous quel delai.
	$admin = apply_filters( 'ag_calendar_notify_email', 'advise.alliance.group@gmail.com' );
	if ( $deja ) {
		wp_mail( $admin, 'Maquette en ligne — renouvellement encaisse',
			"Renouvellement mensuel.\nClient : $email\nSite : $site\nMontant : $amount EUR\nTxn : $txn\n\nRien a faire, le site reste en ligne." );
	} else {
		wp_mail( $admin, 'MAQUETTE PAYEE — a mettre en ligne sous 24 h',
			"Un client vient de payer la mise en ligne de sa maquette.\n\n"
			. "Client  : " . ( $nom ? "$nom " : '' ) . "($email)\n"
			. "Son site actuel : " . ( $site ? $site : '(inconnu)' ) . "\n"
			. "La maquette      : " . ( $lien ? $lien : "(non retrouvee — chercher l'email dans le CRM)" ) . "\n"
			. ( $ref ? "Amene par l'ambassadeur : $ref\n" : '' )
			. "Montant : $amount EUR   Txn : $txn\n\n"
			. "A FAIRE : mettre cette maquette en ligne sur un sous-domaine, sous 24 h,\n"
			. "puis repondre au client avec son adresse." );
	}

	if ( function_exists( 'ag_push' ) ) {
		ag_push( $deja
			? 'Maquette en ligne : renouvellement ' . $amount . ' EUR — ' . $email
			: 'MAQUETTE PAYEE — a mettre en ligne sous 24 h : ' . $email . ( $hote ? ' (' . $hote . ')' : '' ) );
	}

	// Le client : accuse de reception, et ce qui se passe ensuite.
	if ( ! $deja && function_exists( 'ag_email_wrap' ) ) {
		$corps = '<p style="font-family:Arial,sans-serif;font-size:15px;line-height:1.6;color:#e8e6e0;">'
			. 'Bonjour' . ( $nom ? ' ' . esc_html( $nom ) : '' ) . ',</p>'
			. '<p style="font-family:Arial,sans-serif;font-size:15px;line-height:1.6;color:#e8e6e0;">'
			. 'C\'est enregistre. Nous mettons votre maquette en ligne '
			. '<strong>sous 24 heures</strong> et nous vous envoyons son adresse des qu\'elle est active.</p>'
			. ( $lien ? '<p style="font-family:Arial,sans-serif;font-size:14px;line-height:1.6;color:#b0b0bc;">'
				. 'La maquette commandee : <a href="' . esc_url( $lien ) . '" style="color:#D4B45C;">la revoir</a></p>' : '' )
			. '<p style="font-family:Arial,sans-serif;font-size:13px;line-height:1.6;color:#b0b0bc;">'
			. 'Une question ? Repondez simplement a cet email, ou appelez le 07 44 82 95 16.</p>';
		wp_mail( $email, 'Votre maquette sera en ligne sous 24 h',
			ag_email_wrap( 'Commande recue', $corps ),
			array( 'Content-Type: text/html; charset=UTF-8', 'From: Alliance Groupe <contact@alliancegroupe-inc.com>' ) );
	}
}, 18, 5 );

/* ── L'ecran des commandes ────────────────────────────────────────────────
   Une notification se perd, un email se noie. Il faut un endroit ou l'on
   voit, a froid, qui a paye et ce qui reste a mettre en ligne.            */
add_action( 'admin_menu', function () {
	add_submenu_page(
		'ag-hub',
		'Maquettes payees',
		'Maquettes payees',
		'manage_options',
		'ag-refais-commandes',
		'ag_refais_ecran_commandes'
	);
}, 30 );

function ag_refais_ecran_commandes() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }

	// Marquer une commande comme mise en ligne.
	if ( isset( $_POST['ag_refais_fait'], $_POST['_wpnonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'ag_refais_fait' ) ) {
		$txn  = sanitize_text_field( wp_unslash( $_POST['ag_refais_fait'] ) );
		$cmds = (array) get_option( AG_REFAIS_OPT_CMD, array() );
		foreach ( $cmds as $i => $c ) {
			if ( (string) ( $c['txn'] ?? '' ) === $txn ) { $cmds[ $i ]['statut'] = 'en_ligne'; break; }
		}
		update_option( AG_REFAIS_OPT_CMD, $cmds, false );
		echo '<div class="notice notice-success is-dismissible"><p>Commande marquee comme mise en ligne.</p></div>';
	}

	$cmds = array_reverse( ag_refais_commandes() );
	$offre = function_exists( 'ag_refais_boost_offre' ) ? ag_refais_boost_offre() : array();

	echo '<div class="wrap"><h1>Maquettes payees</h1>';

	if ( empty( $offre['payable'] ) ) {
		echo '<div class="notice notice-warning"><p><strong>Aucun lien de paiement configure.</strong> '
			. 'Le bouton affiche « En parler au telephone » et renvoie vers /contact : rien ne peut etre encaisse. '
			. 'A brancher dans <a href="' . esc_url( admin_url( 'options-general.php?page=ag-stripe-config' ) ) . '">Reglages &rarr; Liens de paiement</a>, '
			. 'en mode <strong>abonnement mensuel</strong>.</p></div>';
	}

	if ( ! $cmds ) {
		echo '<p>Aucune commande pour l\'instant. Cet ecran se remplit tout seul des qu\'un client paie.</p></div>';
		return;
	}

	echo '<table class="widefat striped"><thead><tr>'
		. '<th>Quand</th><th>Client</th><th>Son site</th><th>La maquette</th>'
		. '<th>Montant</th><th>Etat</th><th></th>'
		. '</tr></thead><tbody>';

	foreach ( $cmds as $c ) {
		$etat = (string) ( $c['statut'] ?? '' );
		$lib  = array(
			'a_mettre_en_ligne' => '<span style="color:#b32d2e;font-weight:700;">A mettre en ligne</span>',
			'en_ligne'          => '<span style="color:#1f7a3d;font-weight:700;">En ligne</span>',
			'renouvellement'    => '<span style="color:#666;">Renouvellement</span>',
		);
		echo '<tr>';
		echo '<td>' . esc_html( date_i18n( 'd/m/Y H:i', (int) ( $c['ts'] ?? 0 ) ) ) . '</td>';
		echo '<td>' . esc_html( trim( (string) ( $c['nom'] ?? '' ) . ' ' ) ) . '<br><code>' . esc_html( (string) ( $c['email'] ?? '' ) ) . '</code>'
			. ( ! empty( $c['ref'] ) ? '<br><small>via ' . esc_html( (string) $c['ref'] ) . '</small>' : '' ) . '</td>';
		echo '<td>' . ( ! empty( $c['site'] ) ? '<a href="' . esc_url( (string) $c['site'] ) . '" target="_blank" rel="noopener">' . esc_html( (string) wp_parse_url( (string) $c['site'], PHP_URL_HOST ) ) . '</a>' : '&mdash;' ) . '</td>';
		echo '<td>' . ( ! empty( $c['lien'] ) ? '<a href="' . esc_url( (string) $c['lien'] ) . '" target="_blank" rel="noopener">Voir</a>' : '<em>non retrouvee</em>' ) . '</td>';
		echo '<td>' . esc_html( number_format_i18n( (float) ( $c['montant'] ?? 0 ), 2 ) ) . '&nbsp;&euro;</td>';
		echo '<td>' . ( $lib[ $etat ] ?? esc_html( $etat ) ) . '</td>';
		echo '<td>';
		if ( 'a_mettre_en_ligne' === $etat ) {
			echo '<form method="post" style="margin:0">';
			wp_nonce_field( 'ag_refais_fait' );
			echo '<input type="hidden" name="ag_refais_fait" value="' . esc_attr( (string) ( $c['txn'] ?? '' ) ) . '">';
			echo '<button class="button button-small">C\'est en ligne</button></form>';
		}
		echo '</td></tr>';
	}
	echo '</tbody></table></div>';
}
