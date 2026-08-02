<?php
/**
 * AG Composants — bibliothèque de composants web (façon uiverse.io).
 *
 * Espace DÉDIÉ, indépendant du reste du site : les visiteurs parcourent des
 * composants (boutons, cartes, patterns, loaders…), les CONFIGURENT en direct
 * (couleur, rayon, taille, texte), COPIENT le HTML/CSS ou TÉLÉCHARGENT un ZIP
 * prêt à installer. Les développeurs connectés peuvent PROPOSER leurs propres
 * créations (modérées) et un classement « Créateur du mois » récompense les
 * meilleures (likes + téléchargements) avec des trophées.
 *
 * Options :
 *   ag_composants_user   : array des composants proposés par les membres
 *   ag_composants_stats  : compteurs { id => [likes, downloads, like_ips] }
 *   ag_composants_page_v1 : flag de création de la page publique
 *
 * Point d'entrée public : ag_composants_render() (template page-composants.php)
 * ZIP : ?ag_composant_zip=<id>&acc=..&rad=..&sz=..&label=..
 *
 * @package Alliance_Groupe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ─────────────────────────────────────────────────────────────
 * 1. Bibliothèque de base (composants « maison », libres)
 * ───────────────────────────────────────────────────────────── */
if ( ! function_exists( 'ag_composants_cats' ) ) {
	function ag_composants_cats() {
		return array(
			'button'  => '🔘 Boutons',
			'card'    => '🃏 Cartes',
			'loader'  => '⏳ Loaders',
			'badge'   => '🏷️ Badges',
			'input'   => '⌨️ Champs',
			'pattern' => '🌈 Fonds',
		);
	}
}

if ( ! function_exists( 'ag_composants_seed' ) ) {
	/**
	 * Composants intégrés. Chaque composant utilise des variables CSS avec
	 * valeur de repli — var(--acc,#c9a96e) — JAMAIS déclarées ailleurs, pour
	 * que le configurateur puisse les surcharger d'une seule ligne.
	 *
	 * Champ cfg : quelles molettes s'appliquent (acc/rad/sz/label).
	 */
	function ag_composants_seed() {
		$c = array();

		$c[] = array(
			'id' => 'btn-neon', 'title' => 'Bouton Néon', 'cat' => 'button', 'author' => 'Alliance Groupe',
			'cfg' => array( 'acc', 'rad', 'sz', 'label' ), 'label' => 'Cliquez ici',
			'html' => '<button class="agc-btn-neon">{label}</button>',
			'css'  => ".agc-btn-neon{cursor:pointer;font:600 var(--sz,16px)/1 system-ui,sans-serif;color:var(--acc,#c9a96e);background:transparent;padding:.85em 1.8em;border:2px solid var(--acc,#c9a96e);border-radius:var(--rad,10px);position:relative;overflow:hidden;transition:.35s;text-shadow:0 0 8px color-mix(in srgb,var(--acc,#c9a96e) 60%,transparent)}\n.agc-btn-neon:hover{color:#08101f;background:var(--acc,#c9a96e);box-shadow:0 0 12px var(--acc,#c9a96e),0 0 32px color-mix(in srgb,var(--acc,#c9a96e) 55%,transparent)}",
		);

		$c[] = array(
			'id' => 'btn-sweep', 'title' => 'Bouton Balayage', 'cat' => 'button', 'author' => 'Alliance Groupe',
			'cfg' => array( 'acc', 'rad', 'sz', 'label' ), 'label' => 'Découvrir',
			'html' => '<button class="agc-btn-sweep">{label}</button>',
			'css'  => ".agc-btn-sweep{cursor:pointer;font:600 var(--sz,16px)/1 system-ui,sans-serif;color:#fff;background:var(--acc,#c9a96e);padding:.9em 2em;border:0;border-radius:var(--rad,12px);position:relative;overflow:hidden;isolation:isolate;transition:.3s}\n.agc-btn-sweep::before{content:'';position:absolute;inset:0;z-index:-1;transform:translateX(-100%);background:linear-gradient(120deg,transparent,rgba(255,255,255,.55),transparent);transition:.6s}\n.agc-btn-sweep:hover::before{transform:translateX(100%)}\n.agc-btn-sweep:hover{filter:brightness(1.08)}",
		);

		$c[] = array(
			'id' => 'btn-3d', 'title' => 'Bouton 3D', 'cat' => 'button', 'author' => 'Alliance Groupe',
			'cfg' => array( 'acc', 'rad', 'sz', 'label' ), 'label' => 'Valider',
			'html' => '<button class="agc-btn-3d">{label}</button>',
			'css'  => ".agc-btn-3d{cursor:pointer;font:700 var(--sz,16px)/1 system-ui,sans-serif;color:#08101f;background:var(--acc,#c9a96e);padding:.85em 1.9em;border:0;border-radius:var(--rad,12px);box-shadow:0 6px 0 color-mix(in srgb,var(--acc,#c9a96e) 55%,#000);transition:.08s}\n.agc-btn-3d:active{transform:translateY(5px);box-shadow:0 1px 0 color-mix(in srgb,var(--acc,#c9a96e) 55%,#000)}",
		);

		$c[] = array(
			'id' => 'btn-glass', 'title' => 'Bouton Verre', 'cat' => 'button', 'author' => 'Alliance Groupe',
			'cfg' => array( 'acc', 'rad', 'sz', 'label' ), 'label' => 'En savoir plus',
			'html' => '<button class="agc-btn-glass">{label}</button>',
			'css'  => ".agc-btn-glass{cursor:pointer;font:600 var(--sz,16px)/1 system-ui,sans-serif;color:#fff;padding:.9em 2em;border:1px solid rgba(255,255,255,.35);border-radius:var(--rad,14px);background:color-mix(in srgb,var(--acc,#c9a96e) 22%,transparent);backdrop-filter:blur(8px);box-shadow:inset 0 1px 0 rgba(255,255,255,.35),0 8px 24px rgba(0,0,0,.25);transition:.3s}\n.agc-btn-glass:hover{background:color-mix(in srgb,var(--acc,#c9a96e) 40%,transparent);transform:translateY(-2px)}",
		);

		$c[] = array(
			'id' => 'btn-border', 'title' => 'Bouton Bordure animée', 'cat' => 'button', 'author' => 'Alliance Groupe',
			'cfg' => array( 'acc', 'rad', 'sz', 'label' ), 'label' => 'Commencer',
			'html' => '<button class="agc-btn-border">{label}</button>',
			'css'  => "@property --agc-a{syntax:'<angle>';inherits:false;initial-value:0deg}\n.agc-btn-border{cursor:pointer;font:600 var(--sz,16px)/1 system-ui,sans-serif;color:#fff;padding:.95em 2.1em;border:0;border-radius:var(--rad,12px);position:relative;background:#0e1424;z-index:0}\n.agc-btn-border::before{content:'';position:absolute;inset:-2px;z-index:-1;border-radius:inherit;padding:2px;background:conic-gradient(from var(--agc-a),var(--acc,#c9a96e),transparent 40%,var(--acc,#c9a96e));-webkit-mask:linear-gradient(#000 0 0) content-box,linear-gradient(#000 0 0);-webkit-mask-composite:xor;mask-composite:exclude;animation:agc-spin 3s linear infinite}\n@keyframes agc-spin{to{--agc-a:360deg}}",
		);

		$c[] = array(
			'id' => 'btn-pill', 'title' => 'Bouton Flèche', 'cat' => 'button', 'author' => 'Alliance Groupe',
			'cfg' => array( 'acc', 'rad', 'sz', 'label' ), 'label' => 'Continuer',
			'html' => '<button class="agc-btn-pill">{label}<span class="agc-btn-pill__i">→</span></button>',
			'css'  => ".agc-btn-pill{cursor:pointer;display:inline-flex;align-items:center;gap:.5em;font:600 var(--sz,16px)/1 system-ui,sans-serif;color:#08101f;background:var(--acc,#c9a96e);padding:.8em 1.4em .8em 1.7em;border:0;border-radius:var(--rad,999px);transition:.3s}\n.agc-btn-pill__i{transition:.3s;transform:translateX(0)}\n.agc-btn-pill:hover{padding-right:1.9em;box-shadow:0 8px 22px color-mix(in srgb,var(--acc,#c9a96e) 45%,transparent)}\n.agc-btn-pill:hover .agc-btn-pill__i{transform:translateX(5px)}",
		);

		$c[] = array(
			'id' => 'card-glass', 'title' => 'Carte Glassmorphism', 'cat' => 'card', 'author' => 'Alliance Groupe',
			'cfg' => array( 'acc', 'rad' ),
			'html' => '<div class="agc-card-glass"><div class="agc-card-glass__badge">PRO</div><h3>Titre de la carte</h3><p>Un court paragraphe de présentation, clair et élégant.</p><span class="agc-card-glass__link">En savoir plus →</span></div>',
			'css'  => ".agc-card-glass{width:260px;padding:22px;border-radius:var(--rad,18px);color:#fff;background:color-mix(in srgb,var(--acc,#c9a96e) 14%,rgba(20,26,44,.6));border:1px solid rgba(255,255,255,.18);backdrop-filter:blur(10px);box-shadow:0 20px 40px rgba(0,0,0,.35);transition:.35s}\n.agc-card-glass:hover{transform:translateY(-6px)}\n.agc-card-glass h3{margin:.4em 0 .3em;font:700 20px system-ui}\n.agc-card-glass p{margin:0 0 14px;font:400 14px/1.5 system-ui;opacity:.85}\n.agc-card-glass__badge{display:inline-block;font:700 11px system-ui;letter-spacing:.08em;color:#08101f;background:var(--acc,#c9a96e);padding:3px 10px;border-radius:999px}\n.agc-card-glass__link{font:600 14px system-ui;color:var(--acc,#c9a96e)}",
		);

		$c[] = array(
			'id' => 'card-spotlight', 'title' => 'Carte Bordure dégradé', 'cat' => 'card', 'author' => 'Alliance Groupe',
			'cfg' => array( 'acc', 'rad' ),
			'html' => '<div class="agc-card-spot"><h3>Offre Premium</h3><p class="agc-card-spot__p">Tout ce qu\'il faut pour convertir vos visiteurs en clients.</p><div class="agc-card-spot__price">49€<span>/mois</span></div></div>',
			'css'  => ".agc-card-spot{width:250px;padding:26px 22px;border-radius:var(--rad,18px);position:relative;color:#fff;background:#0e1424;z-index:0}\n.agc-card-spot::before{content:'';position:absolute;inset:0;z-index:-1;border-radius:inherit;padding:1.5px;background:linear-gradient(135deg,var(--acc,#c9a96e),transparent 60%);-webkit-mask:linear-gradient(#000 0 0) content-box,linear-gradient(#000 0 0);-webkit-mask-composite:xor;mask-composite:exclude}\n.agc-card-spot h3{margin:0 0 .4em;font:700 19px system-ui;color:var(--acc,#c9a96e)}\n.agc-card-spot__p{margin:0 0 16px;font:400 14px/1.5 system-ui;opacity:.82}\n.agc-card-spot__price{font:800 34px system-ui}\n.agc-card-spot__price span{font:500 14px system-ui;opacity:.7}",
		);

		$c[] = array(
			'id' => 'loader-ring', 'title' => 'Loader Anneau', 'cat' => 'loader', 'author' => 'Alliance Groupe',
			'cfg' => array( 'acc', 'sz' ),
			'html' => '<span class="agc-loader-ring"></span>',
			'css'  => ".agc-loader-ring{width:var(--sz,44px);height:var(--sz,44px);display:inline-block;border-radius:50%;border:4px solid color-mix(in srgb,var(--acc,#c9a96e) 25%,transparent);border-top-color:var(--acc,#c9a96e);animation:agc-rot .8s linear infinite}\n@keyframes agc-rot{to{transform:rotate(360deg)}}",
		);

		$c[] = array(
			'id' => 'loader-dots', 'title' => 'Loader Points', 'cat' => 'loader', 'author' => 'Alliance Groupe',
			'cfg' => array( 'acc', 'sz' ),
			'html' => '<span class="agc-loader-dots"><i></i><i></i><i></i></span>',
			'css'  => ".agc-loader-dots{display:inline-flex;gap:.5em}\n.agc-loader-dots i{width:calc(var(--sz,14px));height:calc(var(--sz,14px));border-radius:50%;background:var(--acc,#c9a96e);animation:agc-bounce 1s ease-in-out infinite}\n.agc-loader-dots i:nth-child(2){animation-delay:.15s}\n.agc-loader-dots i:nth-child(3){animation-delay:.3s}\n@keyframes agc-bounce{0%,100%{transform:translateY(0);opacity:.5}50%{transform:translateY(-70%);opacity:1}}",
		);

		$c[] = array(
			'id' => 'badge-shiny', 'title' => 'Badge Brillant', 'cat' => 'badge', 'author' => 'Alliance Groupe',
			'cfg' => array( 'acc', 'rad', 'sz', 'label' ), 'label' => 'Nouveau',
			'html' => '<span class="agc-badge-shiny">{label}</span>',
			'css'  => ".agc-badge-shiny{display:inline-block;font:700 var(--sz,13px)/1 system-ui;letter-spacing:.06em;color:#08101f;padding:.5em 1em;border-radius:var(--rad,999px);background:linear-gradient(120deg,var(--acc,#c9a96e),#fff5d6,var(--acc,#c9a96e));background-size:200% 100%;animation:agc-shine 2.6s linear infinite}\n@keyframes agc-shine{to{background-position:200% 0}}",
		);

		$c[] = array(
			'id' => 'input-glow', 'title' => 'Champ Lumineux', 'cat' => 'input', 'author' => 'Alliance Groupe',
			'cfg' => array( 'acc', 'rad' ),
			'html' => '<input class="agc-input-glow" type="text" placeholder="Votre email…">',
			'css'  => ".agc-input-glow{font:400 15px system-ui;color:#fff;background:#0e1424;border:1px solid rgba(255,255,255,.2);border-radius:var(--rad,10px);padding:.8em 1em;outline:0;width:240px;transition:.3s}\n.agc-input-glow::placeholder{color:rgba(255,255,255,.5)}\n.agc-input-glow:focus{border-color:var(--acc,#c9a96e);box-shadow:0 0 0 3px color-mix(in srgb,var(--acc,#c9a96e) 30%,transparent)}",
		);

		$c[] = array(
			'id' => 'input-float', 'title' => 'Champ Label flottant', 'cat' => 'input', 'author' => 'Alliance Groupe',
			'cfg' => array( 'acc', 'rad' ),
			'html' => '<label class="agc-float"><input class="agc-float__i" type="text" placeholder=" "><span class="agc-float__l">Nom complet</span></label>',
			'css'  => ".agc-float{position:relative;display:inline-block}\n.agc-float__i{font:400 15px system-ui;color:#fff;background:#0e1424;border:1px solid rgba(255,255,255,.2);border-radius:var(--rad,10px);padding:1.1em 1em .5em;outline:0;width:240px}\n.agc-float__l{position:absolute;left:1em;top:1em;color:rgba(255,255,255,.55);font:400 15px system-ui;pointer-events:none;transition:.2s}\n.agc-float__i:focus+.agc-float__l,.agc-float__i:not(:placeholder-shown)+.agc-float__l{top:.35em;font-size:11px;color:var(--acc,#c9a96e)}\n.agc-float__i:focus{border-color:var(--acc,#c9a96e)}",
		);

		$c[] = array(
			'id' => 'pattern-gradient', 'title' => 'Fond Dégradé animé', 'cat' => 'pattern', 'author' => 'Alliance Groupe',
			'cfg' => array( 'acc', 'rad' ),
			'html' => '<div class="agc-pat-grad"></div>',
			'css'  => ".agc-pat-grad{width:100%;height:130px;border-radius:var(--rad,16px);background:linear-gradient(-45deg,var(--acc,#c9a96e),#1b2440,#2a1f3d,var(--acc,#c9a96e));background-size:400% 400%;animation:agc-grad 9s ease infinite}\n@keyframes agc-grad{0%{background-position:0 50%}50%{background-position:100% 50%}100%{background-position:0 50%}}",
		);

		$c[] = array(
			'id' => 'pattern-dots', 'title' => 'Fond Grille de points', 'cat' => 'pattern', 'author' => 'Alliance Groupe',
			'cfg' => array( 'acc', 'rad' ),
			'html' => '<div class="agc-pat-dots"></div>',
			'css'  => ".agc-pat-dots{width:100%;height:130px;border-radius:var(--rad,16px);background-color:#0e1424;background-image:radial-gradient(color-mix(in srgb,var(--acc,#c9a96e) 70%,transparent) 1.5px,transparent 1.5px);background-size:18px 18px}",
		);

		$c[] = array(
			'id' => 'btn-outline-fill', 'title' => 'Bouton Remplissage', 'cat' => 'button', 'author' => 'Alliance Groupe',
			'cfg' => array( 'acc', 'rad', 'sz', 'label' ), 'label' => 'Télécharger',
			'html' => '<button class="agc-btn-ofill">{label}</button>',
			'css'  => ".agc-btn-ofill{cursor:pointer;font:600 var(--sz,16px)/1 system-ui;color:var(--acc,#c9a96e);background:transparent;padding:.9em 2em;border:2px solid var(--acc,#c9a96e);border-radius:var(--rad,10px);position:relative;overflow:hidden;z-index:0;transition:color .35s}\n.agc-btn-ofill::before{content:'';position:absolute;inset:0;z-index:-1;background:var(--acc,#c9a96e);transform:scaleX(0);transform-origin:left;transition:transform .35s}\n.agc-btn-ofill:hover{color:#08101f}\n.agc-btn-ofill:hover::before{transform:scaleX(1)}",
		);

		return $c;
	}
}

/* ─────────────────────────────────────────────────────────────
 * 2. Données membres, stats, helpers
 * ───────────────────────────────────────────────────────────── */
if ( ! function_exists( 'ag_composants_user_all' ) ) {
	function ag_composants_user_all() {
		return array_values( (array) get_option( 'ag_composants_user', array() ) );
	}
}

if ( ! function_exists( 'ag_composants_all' ) ) {
	/** Seed + composants membres APPROUVÉS (pour l'affichage public). */
	function ag_composants_all( $include_pending = false ) {
		$list = ag_composants_seed();
		foreach ( ag_composants_user_all() as $u ) {
			if ( $include_pending || ( isset( $u['status'] ) && 'approved' === $u['status'] ) ) {
				$list[] = $u;
			}
		}
		return $list;
	}
}

if ( ! function_exists( 'ag_composants_get' ) ) {
	function ag_composants_get( $id, $include_pending = false ) {
		foreach ( ag_composants_all( $include_pending ) as $c ) {
			if ( $c['id'] === $id ) {
				return $c;
			}
		}
		return null;
	}
}

if ( ! function_exists( 'ag_composants_stats' ) ) {
	function ag_composants_stats( $id ) {
		$all = (array) get_option( 'ag_composants_stats', array() );
		$s   = isset( $all[ $id ] ) ? $all[ $id ] : array();
		return array(
			'likes'     => (int) ( $s['likes'] ?? 0 ),
			'downloads' => (int) ( $s['downloads'] ?? 0 ),
		);
	}
}

if ( ! function_exists( 'ag_composants_bump' ) ) {
	/** Incrémente likes/downloads. Pour like : 1 seule fois par IP. */
	function ag_composants_bump( $id, $field, $ip = '' ) {
		$all = (array) get_option( 'ag_composants_stats', array() );
		if ( ! isset( $all[ $id ] ) ) {
			$all[ $id ] = array( 'likes' => 0, 'downloads' => 0, 'like_ips' => array() );
		}
		if ( 'likes' === $field ) {
			$h = $ip ? md5( $ip ) : '';
			$ips = (array) ( $all[ $id ]['like_ips'] ?? array() );
			if ( $h && in_array( $h, $ips, true ) ) {
				return (int) $all[ $id ]['likes']; // déjà liké
			}
			if ( $h ) {
				$ips[] = $h;
				$all[ $id ]['like_ips'] = array_slice( $ips, -2000 );
			}
			$all[ $id ]['likes'] = (int) ( $all[ $id ]['likes'] ?? 0 ) + 1;
			update_option( 'ag_composants_stats', $all );
			return (int) $all[ $id ]['likes'];
		}
		$all[ $id ]['downloads'] = (int) ( $all[ $id ]['downloads'] ?? 0 ) + 1;
		update_option( 'ag_composants_stats', $all );
		return (int) $all[ $id ]['downloads'];
	}
}

if ( ! function_exists( 'ag_composants_leaderboard' ) ) {
	/**
	 * Classement des créateurs (membres) par score = likes + téléchargements
	 * de leurs composants approuvés. $month = 'YYYY-MM' pour filtrer le mois.
	 */
	function ag_composants_leaderboard( $month = '' ) {
		$by = array();
		foreach ( ag_composants_user_all() as $u ) {
			if ( 'approved' !== ( $u['status'] ?? '' ) ) {
				continue;
			}
			if ( $month && gmdate( 'Y-m', (int) ( $u['ts'] ?? 0 ) ) !== $month ) {
				continue;
			}
			$email = $u['author_email'] ?? '';
			if ( ! $email ) {
				continue;
			}
			$st = ag_composants_stats( $u['id'] );
			if ( ! isset( $by[ $email ] ) ) {
				$by[ $email ] = array( 'email' => $email, 'name' => $u['author'] ?? $email, 'count' => 0, 'likes' => 0, 'downloads' => 0, 'score' => 0 );
			}
			$by[ $email ]['count']++;
			$by[ $email ]['likes']     += $st['likes'];
			$by[ $email ]['downloads'] += $st['downloads'];
			$by[ $email ]['score']     += $st['likes'] + $st['downloads'];
		}
		usort( $by, function ( $a, $b ) {
			return $b['score'] <=> $a['score'];
		} );
		return array_values( $by );
	}
}

/* ── Nettoyage sécurité du code proposé par les membres ── */
if ( ! function_exists( 'ag_composants_clean_css' ) ) {
	function ag_composants_clean_css( $css ) {
		$css = (string) wp_unslash( $css );
		// Retire tout ce qui peut exécuter du JS ou casser le <style>.
		$css = preg_replace( '#</?\s*style#i', '', $css );
		$css = preg_replace( '#expression\s*\(#i', '', $css );
		$css = preg_replace( '#@import[^;]*;?#i', '', $css );
		$css = preg_replace( '#behavior\s*:#i', '', $css );
		$css = preg_replace( '#javascript\s*:#i', '', $css );
		$css = preg_replace( '#url\(\s*[\'"]?\s*javascript:#i', 'url(', $css );
		$css = preg_replace( '#<[^>]*>#', '', $css ); // pas de balises dans du CSS
		return trim( mb_substr( $css, 0, 8000 ) );
	}
}

if ( ! function_exists( 'ag_composants_clean_html' ) ) {
	function ag_composants_clean_html( $html ) {
		$html = (string) wp_unslash( $html );
		$allowed = array(
			'div' => array( 'class' => 1, 'style' => 1 ), 'span' => array( 'class' => 1, 'style' => 1 ),
			'p' => array( 'class' => 1, 'style' => 1 ), 'a' => array( 'class' => 1, 'style' => 1, 'href' => 1, 'target' => 1, 'rel' => 1 ),
			'button' => array( 'class' => 1, 'style' => 1, 'type' => 1 ), 'i' => array( 'class' => 1, 'style' => 1 ), 'b' => array( 'class' => 1 ),
			'strong' => array( 'class' => 1 ), 'em' => array( 'class' => 1 ), 'small' => array( 'class' => 1 ),
			'ul' => array( 'class' => 1 ), 'li' => array( 'class' => 1 ), 'label' => array( 'class' => 1, 'for' => 1 ),
			'input' => array( 'class' => 1, 'type' => 1, 'placeholder' => 1, 'value' => 1, 'name' => 1 ),
			'h1' => array( 'class' => 1 ), 'h2' => array( 'class' => 1 ), 'h3' => array( 'class' => 1 ), 'h4' => array( 'class' => 1 ),
			'svg' => array( 'class' => 1, 'viewbox' => 1, 'width' => 1, 'height' => 1, 'fill' => 1, 'xmlns' => 1 ),
			'path' => array( 'd' => 1, 'fill' => 1, 'stroke' => 1, 'stroke-width' => 1 ),
			'circle' => array( 'cx' => 1, 'cy' => 1, 'r' => 1, 'fill' => 1, 'stroke' => 1 ),
			'g' => array( 'fill' => 1 ), 'br' => array(),
		);
		// href : pas de javascript:
		$html = preg_replace( '#href\s*=\s*([\'"])\s*javascript:[^\'"]*\1#i', 'href="#"', $html );
		$html = wp_kses( $html, $allowed );
		return trim( mb_substr( $html, 0, 6000 ) );
	}
}

/* ─────────────────────────────────────────────────────────────
 * 3. AJAX : like, téléchargement (compteur), proposition
 * ───────────────────────────────────────────────────────────── */
add_action( 'wp_ajax_ag_comp_like', 'ag_comp_like_cb' );
add_action( 'wp_ajax_nopriv_ag_comp_like', 'ag_comp_like_cb' );
if ( ! function_exists( 'ag_comp_like_cb' ) ) {
	function ag_comp_like_cb() {
		if ( ! isset( $_POST['_n'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_n'] ) ), 'ag_composants' ) ) {
			wp_send_json_error();
		}
		$id = sanitize_text_field( wp_unslash( $_POST['id'] ?? '' ) );
		if ( ! $id || ! ag_composants_get( $id ) ) {
			wp_send_json_error();
		}
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		wp_send_json_success( array( 'likes' => ag_composants_bump( $id, 'likes', $ip ) ) );
	}
}

add_action( 'wp_ajax_ag_comp_dl', 'ag_comp_dl_cb' );
add_action( 'wp_ajax_nopriv_ag_comp_dl', 'ag_comp_dl_cb' );
if ( ! function_exists( 'ag_comp_dl_cb' ) ) {
	function ag_comp_dl_cb() {
		if ( ! isset( $_POST['_n'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_n'] ) ), 'ag_composants' ) ) {
			wp_send_json_error();
		}
		$id = sanitize_text_field( wp_unslash( $_POST['id'] ?? '' ) );
		if ( ! $id || ! ag_composants_get( $id ) ) {
			wp_send_json_error();
		}
		wp_send_json_success( array( 'downloads' => ag_composants_bump( $id, 'downloads' ) ) );
	}
}

add_action( 'wp_ajax_ag_comp_submit', 'ag_comp_submit_cb' );
if ( ! function_exists( 'ag_comp_submit_cb' ) ) {
	function ag_comp_submit_cb() {
		if ( ! is_user_logged_in() || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_n'] ) ), 'ag_composants' ) ) {
			wp_send_json_error( array( 'msg' => 'Connexion requise.' ) );
		}
		$title = sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) );
		$cat   = sanitize_key( wp_unslash( $_POST['cat'] ?? '' ) );
		$html  = ag_composants_clean_html( $_POST['html'] ?? '' );
		$css   = ag_composants_clean_css( $_POST['css'] ?? '' );
		if ( ! $title || ! isset( ag_composants_cats()[ $cat ] ) || ! $html || ! $css ) {
			wp_send_json_error( array( 'msg' => 'Titre, catégorie, HTML et CSS sont obligatoires.' ) );
		}
		$u    = wp_get_current_user();
		$list = (array) get_option( 'ag_composants_user', array() );
		$list[] = array(
			'id'           => 'u-' . substr( md5( $u->ID . microtime() . $title ), 0, 10 ),
			'title'        => $title,
			'cat'          => $cat,
			'html'         => $html,
			'css'          => $css,
			'cfg'          => array(), // les composants membres : copie/téléchargement tels quels
			'author'       => $u->display_name ? $u->display_name : $u->user_login,
			'author_email' => $u->user_email,
			'status'       => 'pending',
			'ts'           => time(),
		);
		update_option( 'ag_composants_user', array_slice( $list, -3000 ) );
		if ( function_exists( 'ag_push' ) ) {
			ag_push( '🧩 Nouveau composant proposé', $title . ' — ' . $u->user_email . ' (à valider)' );
		}
		wp_send_json_success( array( 'msg' => 'Merci ! Ton composant est envoyé. Il apparaîtra après validation.' ) );
	}
}

/* Admin : approuver / rejeter / supprimer */
add_action( 'admin_post_ag_comp_moderate', 'ag_comp_moderate_cb' );
if ( ! function_exists( 'ag_comp_moderate_cb' ) ) {
	function ag_comp_moderate_cb() {
		if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_n'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_n'] ) ), 'ag_comp_admin' ) ) {
			wp_die( 'no' );
		}
		$id     = sanitize_text_field( wp_unslash( $_POST['id'] ?? '' ) );
		$action = sanitize_key( wp_unslash( $_POST['do'] ?? '' ) );
		$list   = (array) get_option( 'ag_composants_user', array() );
		foreach ( $list as $k => $u ) {
			if ( ( $u['id'] ?? '' ) === $id ) {
				if ( 'approve' === $action ) {
					$list[ $k ]['status'] = 'approved';
				} elseif ( 'reject' === $action ) {
					$list[ $k ]['status'] = 'rejected';
				} elseif ( 'delete' === $action ) {
					unset( $list[ $k ] );
				}
				break;
			}
		}
		update_option( 'ag_composants_user', array_values( $list ) );
		wp_safe_redirect( admin_url( 'admin.php?page=ag-composants&done=1' ) );
		exit;
	}
}

/* ─────────────────────────────────────────────────────────────
 * 4. Téléchargement ZIP : ?ag_composant_zip=<id>&acc=&rad=&sz=&label=
 * ───────────────────────────────────────────────────────────── */
add_action( 'template_redirect', 'ag_composant_zip_stream' );
if ( ! function_exists( 'ag_composant_zip_stream' ) ) {
	function ag_composant_zip_stream() {
		if ( empty( $_GET['ag_composant_zip'] ) ) {
			return;
		}
		$id = sanitize_text_field( wp_unslash( $_GET['ag_composant_zip'] ) );
		$c  = ag_composants_get( $id );
		if ( ! $c || ! class_exists( 'ZipArchive' ) ) {
			status_header( 404 );
			exit;
		}
		$acc   = isset( $_GET['acc'] ) ? sanitize_hex_color( wp_unslash( $_GET['acc'] ) ) : '';
		$rad   = isset( $_GET['rad'] ) ? preg_replace( '/[^0-9a-z%.]/i', '', wp_unslash( $_GET['rad'] ) ) : '';
		$sz    = isset( $_GET['sz'] ) ? preg_replace( '/[^0-9a-z%.]/i', '', wp_unslash( $_GET['sz'] ) ) : '';
		$label = isset( $_GET['label'] ) ? sanitize_text_field( wp_unslash( $_GET['label'] ) ) : '';

		$html = str_replace( '{label}', $label ? $label : ( $c['label'] ?? '' ), $c['html'] );
		$css  = ag_composants_apply_vars( $c, $acc, $rad, $sz );

		ag_composants_bump( $id, 'downloads' );

		$root = strtok( $c['html'], ' ' ); // (info) non utilisé, garde-fou
		$page = "<!doctype html>\n<html lang=\"fr\">\n<head>\n<meta charset=\"utf-8\">\n<meta name=\"viewport\" content=\"width=device-width,initial-scale=1\">\n<title>" . esc_html( $c['title'] ) . " — Alliance Groupe</title>\n<link rel=\"stylesheet\" href=\"style.css\">\n</head>\n<body style=\"display:grid;place-items:center;min-height:100vh;background:#0a0e1a;margin:0\">\n" . $html . "\n</body>\n</html>\n";
		$readme = "Composant : " . $c['title'] . "\nCatégorie : " . ( ag_composants_cats()[ $c['cat'] ] ?? $c['cat'] ) . "\nAuteur : " . ( $c['author'] ?? 'Alliance Groupe' ) . "\nSource : Alliance Groupe — " . home_url( '/composants' ) . "\n\nINSTALLATION\n1. Copie le contenu de style.css dans ta feuille de style.\n2. Colle le HTML de index.html là où tu veux le composant.\n3. Personnalise les couleurs via les variables CSS (--acc, --rad, --sz).\n\nLicence : libre d'utilisation sur tes projets. Un lien retour est apprécié.\n";

		$tmp = wp_tempnam( 'agcomp' );
		$zip = new ZipArchive();
		if ( true !== $zip->open( $tmp, ZipArchive::OVERWRITE ) ) {
			status_header( 500 );
			exit;
		}
		$zip->addFromString( 'index.html', $page );
		$zip->addFromString( 'style.css', $css . "\n" );
		$zip->addFromString( 'README.txt', $readme );
		$zip->close();

		$data = file_get_contents( $tmp );
		@unlink( $tmp );
		nocache_headers();
		header( 'Content-Type: application/zip' );
		header( 'Content-Disposition: attachment; filename="alliance-' . $id . '.zip"' );
		header( 'Content-Length: ' . strlen( $data ) );
		echo $data; // @codingStandardsIgnoreLine (binaire)
		exit;
	}
}

if ( ! function_exists( 'ag_composants_apply_vars' ) ) {
	/** Préfixe le CSS avec une ligne qui fixe les variables choisies. */
	function ag_composants_apply_vars( $c, $acc = '', $rad = '', $sz = '' ) {
		$root = ag_composants_root_class( $c );
		$decls = array();
		if ( $acc ) {
			$decls[] = '--acc:' . $acc;
		}
		if ( $rad ) {
			$decls[] = '--rad:' . $rad;
		}
		if ( $sz ) {
			$decls[] = '--sz:' . $sz;
		}
		$prefix = '';
		if ( $root && $decls ) {
			$prefix = '.' . $root . '{' . implode( ';', $decls ) . "}\n";
		}
		return $prefix . $c['css'];
	}
}

if ( ! function_exists( 'ag_composants_root_class' ) ) {
	/** Récupère la 1re classe du HTML (le sélecteur racine à surcharger). */
	function ag_composants_root_class( $c ) {
		if ( preg_match( '/class="([^"\s]+)/', $c['html'], $m ) ) {
			return $m[1];
		}
		return '';
	}
}

/* ─────────────────────────────────────────────────────────────
 * 5. Rendu public : ag_composants_render()
 * ───────────────────────────────────────────────────────────── */
if ( ! function_exists( 'ag_composants_render' ) ) {
	function ag_composants_render() {
		$cats    = ag_composants_cats();
		$all     = ag_composants_all();
		$nonce   = wp_create_nonce( 'ag_composants' );
		$month   = gmdate( 'Y-m' );
		$board   = ag_composants_leaderboard( $month );
		$logged  = is_user_logged_in();
		$ajax    = admin_url( 'admin-ajax.php' );

		// CSS global : toutes les définitions des composants (classes uniques → pas de collision).
		echo '<style id="agc-lib-css">';
		foreach ( $all as $c ) {
			echo "\n/* " . esc_html( $c['id'] ) . " */\n" . $c['css'];
		}
		echo '</style>';
		?>
		<style>
		.agc-wrap{max-width:1180px;margin:0 auto;padding:40px 20px 80px;color:#e7ecf5;font-family:system-ui,-apple-system,sans-serif}
		.agc-hero{text-align:center;margin-bottom:30px}
		.agc-hero h1{font-size:clamp(28px,5vw,46px);margin:0 0 10px;background:linear-gradient(120deg,#f0d99a,#c9a96e);-webkit-background-clip:text;background-clip:text;color:transparent}
		.agc-hero p{color:#9fb0c8;max-width:640px;margin:0 auto;font-size:17px;line-height:1.6}
		.agc-podium{display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin:26px 0 10px}
		.agc-podium__c{background:#111827;border:1px solid #223;border-radius:14px;padding:14px 18px;min-width:150px;text-align:center}
		.agc-podium__c b{display:block;color:#f0d99a}
		.agc-podium__medal{font-size:26px}
		.agc-toolbar{display:flex;gap:10px;flex-wrap:wrap;align-items:center;justify-content:space-between;margin:26px 0 18px;position:sticky;top:0;background:rgba(10,14,26,.85);backdrop-filter:blur(6px);padding:10px 0;z-index:5}
		.agc-tabs{display:flex;gap:8px;flex-wrap:wrap}
		.agc-tab{cursor:pointer;font:600 14px system-ui;color:#9fb0c8;background:#131a2b;border:1px solid #223;border-radius:999px;padding:8px 14px}
		.agc-tab.on{color:#08101f;background:#c9a96e;border-color:#c9a96e}
		.agc-tools{display:flex;gap:8px;flex-wrap:wrap}
		.agc-tools input,.agc-tools select{font:400 14px system-ui;color:#fff;background:#131a2b;border:1px solid #223;border-radius:8px;padding:8px 10px}
		.agc-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:18px}
		.agc-item{background:#0f1626;border:1px solid #1e2942;border-radius:16px;overflow:hidden;display:flex;flex-direction:column}
		.agc-prev{min-height:150px;display:grid;place-items:center;padding:22px;background:radial-gradient(circle at 50% 40%,#16203a,#0b1020)}
		.agc-meta{padding:12px 14px;border-top:1px solid #1e2942}
		.agc-meta__top{display:flex;justify-content:space-between;align-items:center;gap:8px}
		.agc-meta__top b{font-size:15px}
		.agc-meta__by{font-size:12px;color:#7f90aa}
		.agc-like{cursor:pointer;background:none;border:0;color:#9fb0c8;font:600 13px system-ui;display:inline-flex;align-items:center;gap:4px}
		.agc-like.on{color:#ff6b81}
		.agc-cfg{display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin:10px 0}
		.agc-cfg label{font-size:11px;color:#8ea0bb;display:flex;align-items:center;gap:4px}
		.agc-cfg input[type=color]{width:26px;height:26px;border:0;background:none;padding:0;border-radius:6px}
		.agc-cfg input[type=range]{width:70px}
		.agc-cfg input[type=text]{width:90px;font:400 12px system-ui;color:#fff;background:#131a2b;border:1px solid #223;border-radius:6px;padding:4px 6px}
		.agc-actions{display:flex;gap:8px;flex-wrap:wrap}
		.agc-btn{cursor:pointer;font:600 13px system-ui;border:0;border-radius:8px;padding:8px 12px}
		.agc-btn--g{background:#1b2438;color:#cfe} .agc-btn--p{background:#c9a96e;color:#08101f}
		.agc-empty{grid-column:1/-1;text-align:center;color:#7f90aa;padding:40px}
		.agc-submit{margin-top:50px;background:#0f1626;border:1px solid #1e2942;border-radius:16px;padding:24px}
		.agc-submit h2{margin:0 0 6px;color:#f0d99a}
		.agc-submit textarea,.agc-submit input,.agc-submit select{width:100%;box-sizing:border-box;font:400 14px ui-monospace,monospace;color:#fff;background:#0b1220;border:1px solid #223;border-radius:8px;padding:10px;margin:6px 0}
		.agc-submit textarea{min-height:110px}
		.agc-live{min-height:120px;display:grid;place-items:center;background:radial-gradient(circle at 50% 40%,#16203a,#0b1020);border-radius:10px;margin:10px 0}
		.agc-toast{position:fixed;bottom:20px;left:50%;transform:translateX(-50%);background:#c9a96e;color:#08101f;font:600 14px system-ui;padding:10px 18px;border-radius:10px;opacity:0;pointer-events:none;transition:.3s;z-index:99}
		.agc-toast.on{opacity:1}
		</style>

		<div class="agc-wrap">
			<div class="agc-hero">
				<h1>🧩 Composants Alliance</h1>
				<p>Des composants web uniques — boutons, cartes, loaders, fonds… <strong>Configure</strong> les couleurs, <strong>copie</strong> le HTML/CSS ou <strong>télécharge</strong> le ZIP. Gratuit. Codeurs : proposez vos créations et gagnez le titre de <strong>Créateur du mois</strong> 🏆.</p>
			</div>

			<?php if ( $board ) : ?>
			<div class="agc-podium">
				<?php $medals = array( '🥇', '🥈', '🥉' ); foreach ( array_slice( $board, 0, 3 ) as $i => $b ) : ?>
					<div class="agc-podium__c">
						<div class="agc-podium__medal"><?php echo esc_html( $medals[ $i ] ); ?></div>
						<b><?php echo esc_html( $b['name'] ); ?></b>
						<span style="font-size:12px;color:#9fb0c8"><?php echo (int) $b['score']; ?> pts · <?php echo (int) $b['count']; ?> compo · ❤️<?php echo (int) $b['likes']; ?></span>
					</div>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>

			<div class="agc-toolbar">
				<div class="agc-tabs">
					<span class="agc-tab on" data-cat="all">✨ Tous</span>
					<?php foreach ( $cats as $key => $lbl ) : ?>
						<span class="agc-tab" data-cat="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $lbl ); ?></span>
					<?php endforeach; ?>
				</div>
				<div class="agc-tools">
					<input type="search" id="agc-search" placeholder="🔍 Rechercher…">
					<select id="agc-sort">
						<option value="pop">Populaires</option>
						<option value="recent">Récents</option>
						<option value="az">A → Z</option>
					</select>
				</div>
			</div>

			<div class="agc-grid" id="agc-grid">
				<?php foreach ( $all as $idx => $c ) :
					$st   = ag_composants_stats( $c['id'] );
					$root = ag_composants_root_class( $c );
					$cfg  = (array) ( $c['cfg'] ?? array() );
					$prev = str_replace( '{label}', esc_html( $c['label'] ?? '' ), $c['html'] );
					?>
					<div class="agc-item" data-id="<?php echo esc_attr( $c['id'] ); ?>" data-cat="<?php echo esc_attr( $c['cat'] ); ?>"
						data-title="<?php echo esc_attr( mb_strtolower( $c['title'] ) ); ?>" data-pop="<?php echo (int) ( $st['likes'] + $st['downloads'] ); ?>"
						data-idx="<?php echo (int) $idx; ?>" data-root="<?php echo esc_attr( $root ); ?>"
						data-label="<?php echo esc_attr( $c['label'] ?? '' ); ?>">
						<div class="agc-prev"><div class="agc-prev__in"><?php echo $prev; // phpcs:ignore WordPress.Security.EscapeOutput ?></div></div>
						<div class="agc-meta">
							<div class="agc-meta__top">
								<b><?php echo esc_html( $c['title'] ); ?></b>
								<button class="agc-like" data-id="<?php echo esc_attr( $c['id'] ); ?>">❤️ <span><?php echo (int) $st['likes']; ?></span></button>
							</div>
							<div class="agc-meta__by">par <?php echo esc_html( $c['author'] ?? 'Alliance Groupe' ); ?> · ⬇️ <?php echo (int) $st['downloads']; ?></div>

							<?php if ( $cfg ) : ?>
							<div class="agc-cfg">
								<?php if ( in_array( 'acc', $cfg, true ) ) : ?><label>🎨<input type="color" data-k="acc" value="#c9a96e"></label><?php endif; ?>
								<?php if ( in_array( 'rad', $cfg, true ) ) : ?><label>⬛<input type="range" data-k="rad" min="0" max="40" value="10"></label><?php endif; ?>
								<?php if ( in_array( 'sz', $cfg, true ) ) : ?><label>🔠<input type="range" data-k="sz" min="10" max="48" value="16"></label><?php endif; ?>
								<?php if ( in_array( 'label', $cfg, true ) ) : ?><label>✏️<input type="text" data-k="label" value="<?php echo esc_attr( $c['label'] ?? '' ); ?>"></label><?php endif; ?>
							</div>
							<?php endif; ?>

							<div class="agc-actions">
								<button class="agc-btn agc-btn--g" data-act="html">📋 HTML</button>
								<button class="agc-btn agc-btn--g" data-act="css">📋 CSS</button>
								<button class="agc-btn agc-btn--p" data-act="zip">⬇️ ZIP</button>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
				<div class="agc-empty" id="agc-empty" style="display:none">Aucun composant ne correspond.</div>
			</div>

			<!-- Espace développeurs -->
			<div class="agc-submit" id="agc-propose">
				<h2>👨‍💻 Proposer un composant</h2>
				<?php if ( $logged ) : ?>
					<p style="color:#9fb0c8;margin:.2em 0 1em">Colle ton HTML et ton CSS. Astuce : utilise <code>var(--acc,#c9a96e)</code> pour une couleur configurable. Ta création est publiée après validation et entre au classement du mois.</p>
					<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px" class="agc-form-grid">
						<div>
							<input type="text" id="agc-f-title" placeholder="Nom du composant">
							<select id="agc-f-cat">
								<?php foreach ( $cats as $key => $lbl ) : ?><option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $lbl ); ?></option><?php endforeach; ?>
							</select>
							<textarea id="agc-f-html" placeholder="HTML — ex: &lt;button class=&quot;mon-bouton&quot;&gt;Clique&lt;/button&gt;"></textarea>
							<textarea id="agc-f-css" placeholder="CSS — ex: .mon-bouton{background:var(--acc,#c9a96e);...}"></textarea>
							<button class="agc-btn agc-btn--p" id="agc-f-send" style="padding:10px 18px">🚀 Envoyer ma création</button>
							<div id="agc-f-msg" style="margin-top:8px;font-size:13px"></div>
						</div>
						<div>
							<div style="font-size:12px;color:#8ea0bb;margin-bottom:4px">Aperçu en direct :</div>
							<div class="agc-live" id="agc-f-live"><span style="color:#556">— ton composant s'affiche ici —</span></div>
						</div>
					</div>
				<?php else : ?>
					<p style="color:#9fb0c8">Connecte-toi pour proposer tes composants et concourir pour le titre de <strong>Créateur du mois</strong>.</p>
					<a class="agc-btn agc-btn--p" style="display:inline-block;text-decoration:none;padding:10px 18px" href="<?php echo esc_url( home_url( '/connexion' ) ); ?>">Se connecter / S'inscrire</a>
				<?php endif; ?>
			</div>
		</div>

		<div class="agc-toast" id="agc-toast"></div>

		<script>
		(function(){
			var AJAX=<?php echo wp_json_encode( $ajax ); ?>, N=<?php echo wp_json_encode( $nonce ); ?>;
			var HOME=<?php echo wp_json_encode( home_url( '/' ) ); ?>;
			// données CSS brutes par composant (pour copie/zip)
			var CSS=<?php
				$map = array();
				foreach ( $all as $c ) {
					$map[ $c['id'] ] = array( 'css' => $c['css'], 'html' => $c['html'], 'root' => ag_composants_root_class( $c ) );
				}
				echo wp_json_encode( $map );
			?>;
			function toast(m){var t=document.getElementById('agc-toast');t.textContent=m;t.classList.add('on');setTimeout(function(){t.classList.remove('on')},1600);}
			function conf(item){
				var o={};item.querySelectorAll('.agc-cfg [data-k]').forEach(function(i){o[i.getAttribute('data-k')]=i.value;});return o;
			}
			function buildCss(id,o){
				var d=CSS[id];if(!d)return '';var pre='';var dc=[];
				if(o.acc)dc.push('--acc:'+o.acc);
				if(o.rad!==undefined&&o.rad!=='')dc.push('--rad:'+o.rad+'px');
				if(o.sz!==undefined&&o.sz!=='')dc.push('--sz:'+o.sz+'px');
				if(d.root&&dc.length)pre='.'+d.root+'{'+dc.join(';')+'}\n';
				return pre+d.css;
			}
			function buildHtml(id,o){
				var d=CSS[id];if(!d)return '';return d.html.replace('{label}',o.label!==undefined?o.label:'');
			}
			function copy(txt){navigator.clipboard&&navigator.clipboard.writeText(txt).then(function(){toast('Copié !');},function(){toast('Copie impossible');});}

			// live preview config
			document.querySelectorAll('.agc-item').forEach(function(item){
				var id=item.getAttribute('data-id'),root=item.getAttribute('data-root');
				var prev=item.querySelector('.agc-prev__in');
				item.querySelectorAll('.agc-cfg [data-k]').forEach(function(inp){
					inp.addEventListener('input',function(){
						var o=conf(item);
						var el=prev.firstElementChild;
						if(el){
							if(o.acc)el.style.setProperty('--acc',o.acc);
							if(o.rad!==undefined&&o.rad!=='')el.style.setProperty('--rad',o.rad+'px');
							if(o.sz!==undefined&&o.sz!=='')el.style.setProperty('--sz',o.sz+'px');
							if(o.label!==undefined){var d=CSS[id];if(d&&d.html.indexOf('{label}')>-1){prev.innerHTML=buildHtml(id,o);
								var e2=prev.firstElementChild;if(e2){if(o.acc)e2.style.setProperty('--acc',o.acc);if(o.rad)e2.style.setProperty('--rad',o.rad+'px');if(o.sz)e2.style.setProperty('--sz',o.sz+'px');}}}
						}
					});
				});
				// actions
				item.querySelectorAll('[data-act]').forEach(function(btn){
					btn.addEventListener('click',function(){
						var o=conf(item),act=btn.getAttribute('data-act');
						if(act==='html')copy(buildHtml(id,o));
						else if(act==='css')copy(buildCss(id,o));
						else if(act==='zip'){
							var q=HOME+'?ag_composant_zip='+encodeURIComponent(id);
							if(o.acc)q+='&acc='+encodeURIComponent(o.acc);
							if(o.rad)q+='&rad='+encodeURIComponent(o.rad+'px');
							if(o.sz)q+='&sz='+encodeURIComponent(o.sz+'px');
							if(o.label!==undefined)q+='&label='+encodeURIComponent(o.label);
							window.location.href=q; // le endpoint ZIP incrémente le compteur côté serveur
						}
					});
				});
				// like
				var lk=item.querySelector('.agc-like');
				lk&&lk.addEventListener('click',function(){
					if(lk.classList.contains('on'))return;
					var f=new FormData();f.append('action','ag_comp_like');f.append('_n',N);f.append('id',id);
					fetch(AJAX,{method:'POST',body:f}).then(function(r){return r.json();}).then(function(j){
						if(j&&j.success){lk.classList.add('on');lk.querySelector('span').textContent=j.data.likes;item.setAttribute('data-pop',(parseInt(item.getAttribute('data-pop'))||0)+1);}
					});
				});
			});

			// filtres + tri + recherche
			var grid=document.getElementById('agc-grid'),items=[].slice.call(grid.querySelectorAll('.agc-item'));
			var curCat='all';
			function apply(){
				var q=(document.getElementById('agc-search').value||'').toLowerCase();
				var shown=0;
				items.forEach(function(it){
					var ok=(curCat==='all'||it.getAttribute('data-cat')===curCat)&&(!q||it.getAttribute('data-title').indexOf(q)>-1);
					it.style.display=ok?'':'none';if(ok)shown++;
				});
				document.getElementById('agc-empty').style.display=shown?'none':'block';
				// tri
				var s=document.getElementById('agc-sort').value;
				var vis=items.filter(function(it){return it.style.display!=='none';});
				vis.sort(function(a,b){
					if(s==='pop')return (parseInt(b.getAttribute('data-pop'))||0)-(parseInt(a.getAttribute('data-pop'))||0);
					if(s==='recent')return (parseInt(b.getAttribute('data-idx'))||0)-(parseInt(a.getAttribute('data-idx'))||0);
					return a.getAttribute('data-title').localeCompare(b.getAttribute('data-title'));
				});
				vis.forEach(function(it){grid.appendChild(it);});
				grid.appendChild(document.getElementById('agc-empty'));
			}
			document.querySelectorAll('.agc-tab').forEach(function(t){t.addEventListener('click',function(){
				document.querySelectorAll('.agc-tab').forEach(function(x){x.classList.remove('on');});t.classList.add('on');curCat=t.getAttribute('data-cat');apply();
			});});
			document.getElementById('agc-search').addEventListener('input',apply);
			document.getElementById('agc-sort').addEventListener('change',apply);
			apply();

			// espace développeurs : aperçu live + envoi
			var fh=document.getElementById('agc-f-html');
			if(fh){
				var fc=document.getElementById('agc-f-css'),live=document.getElementById('agc-f-live'),sty=document.createElement('style');
				document.head.appendChild(sty);
				function refresh(){sty.textContent=fc.value||'';live.innerHTML=fh.value||'<span style="color:#556">— aperçu —</span>';}
				fh.addEventListener('input',refresh);fc.addEventListener('input',refresh);
				document.getElementById('agc-f-send').addEventListener('click',function(){
					var t=document.getElementById('agc-f-title').value.trim();
					var msg=document.getElementById('agc-f-msg');
					if(!t||!fh.value.trim()||!fc.value.trim()){msg.style.color='#ff8f9a';msg.textContent='Titre, HTML et CSS obligatoires.';return;}
					var f=new FormData();f.append('action','ag_comp_submit');f.append('_n',N);
					f.append('title',t);f.append('cat',document.getElementById('agc-f-cat').value);
					f.append('html',fh.value);f.append('css',fc.value);
					msg.style.color='#9fb0c8';msg.textContent='Envoi…';
					fetch(AJAX,{method:'POST',body:f}).then(function(r){return r.json();}).then(function(j){
						if(j&&j.success){msg.style.color='#8fe08f';msg.textContent=j.data.msg;document.getElementById('agc-f-title').value='';fh.value='';fc.value='';refresh();}
						else{msg.style.color='#ff8f9a';msg.textContent=(j&&j.data&&j.data.msg)||'Erreur.';}
					});
				});
			}
		})();
		</script>
		<?php
	}
}

/* ─────────────────────────────────────────────────────────────
 * 6. Page publique + menu admin + SEO
 * ───────────────────────────────────────────────────────────── */
add_action( 'admin_init', 'ag_composants_ensure_page' );
if ( ! function_exists( 'ag_composants_ensure_page' ) ) {
	function ag_composants_ensure_page() {
		if ( get_option( 'ag_composants_page_v1' ) ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$existing = get_page_by_path( 'composants' );
		if ( ! $existing ) {
			$pid = wp_insert_post( array(
				'post_title'   => 'Composants',
				'post_name'    => 'composants',
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '<!-- Rendu par templates/page-composants.php -->',
				'post_author'  => get_current_user_id() ?: 1,
			) );
			if ( $pid && ! is_wp_error( $pid ) ) {
				update_post_meta( $pid, '_wp_page_template', 'templates/page-composants.php' );
			}
		}
		update_option( 'ag_composants_page_v1', 1 );
	}
}

add_action( 'admin_menu', 'ag_composants_admin_menu', 30 );
if ( ! function_exists( 'ag_composants_admin_menu' ) ) {
	function ag_composants_admin_menu() {
		add_menu_page( 'Composants', '🧩 Composants', 'manage_options', 'ag-composants', 'ag_composants_admin_render', 'dashicons-screenoptions', 58 );
	}
}

if ( ! function_exists( 'ag_composants_admin_render' ) ) {
	function ag_composants_admin_render() {
		$users = ag_composants_user_all();
		$pending = array_filter( $users, function ( $u ) {
			return 'pending' === ( $u['status'] ?? '' );
		} );
		$board = ag_composants_leaderboard();
		$n     = wp_create_nonce( 'ag_comp_admin' );
		echo '<div class="wrap"><h1>🧩 Composants — modération & créateurs</h1>';
		echo '<p><a href="' . esc_url( home_url( '/composants' ) ) . '" target="_blank">→ Voir l\'espace public</a> · ' . count( ag_composants_seed() ) . ' composants maison · ' . count( $users ) . ' proposés</p>';

		echo '<h2>⏳ En attente de validation (' . count( $pending ) . ')</h2>';
		if ( ! $pending ) {
			echo '<p>Rien à valider.</p>';
		}
		foreach ( $pending as $u ) {
			echo '<div style="border:1px solid #ccd;border-radius:8px;padding:12px;margin:10px 0;background:#fff;max-width:900px">';
			echo '<strong>' . esc_html( $u['title'] ) . '</strong> — ' . esc_html( ag_composants_cats()[ $u['cat'] ] ?? $u['cat'] ) . ' — par ' . esc_html( $u['author'] ?? '' ) . ' (' . esc_html( $u['author_email'] ?? '' ) . ')';
			echo '<div style="display:grid;place-items:center;padding:18px;background:#0b1020;border-radius:8px;margin:8px 0"><style>' . $u['css'] . '</style>' . $u['html'] . '</div>'; // phpcs:ignore
			echo '<details><summary>Voir le code</summary><pre style="white-space:pre-wrap;background:#f6f7f9;padding:8px;border-radius:6px">' . esc_html( $u['html'] ) . "\n\n" . esc_html( $u['css'] ) . '</pre></details>';
			foreach ( array( 'approve' => '✅ Approuver', 'reject' => '🚫 Rejeter', 'delete' => '🗑 Supprimer' ) as $do => $lbl ) {
				echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:inline">';
				echo '<input type="hidden" name="action" value="ag_comp_moderate"><input type="hidden" name="_n" value="' . esc_attr( $n ) . '">';
				echo '<input type="hidden" name="id" value="' . esc_attr( $u['id'] ) . '"><input type="hidden" name="do" value="' . esc_attr( $do ) . '">';
				echo '<button class="button" style="margin-right:6px">' . esc_html( $lbl ) . '</button></form>';
			}
			echo '</div>';
		}

		echo '<h2>🏆 Classement des créateurs (tous mois)</h2>';
		if ( ! $board ) {
			echo '<p>Aucun créateur pour l\'instant.</p>';
		} else {
			echo '<table class="widefat" style="max-width:700px"><thead><tr><th>#</th><th>Créateur</th><th>Composants</th><th>❤️ Likes</th><th>⬇️ DL</th><th>Score</th></tr></thead><tbody>';
			foreach ( $board as $i => $b ) {
				echo '<tr><td>' . ( $i + 1 ) . '</td><td>' . esc_html( $b['name'] ) . '<br><small>' . esc_html( $b['email'] ) . '</small></td><td>' . (int) $b['count'] . '</td><td>' . (int) $b['likes'] . '</td><td>' . (int) $b['downloads'] . '</td><td><strong>' . (int) $b['score'] . '</strong></td></tr>';
			}
			echo '</tbody></table>';
		}
		echo '</div>';
	}
}

/* SEO : titre de la page composants */
add_filter( 'document_title_parts', 'ag_composants_seo_title' );
if ( ! function_exists( 'ag_composants_seo_title' ) ) {
	function ag_composants_seo_title( $parts ) {
		if ( is_page_template( 'templates/page-composants.php' ) ) {
			$parts['title'] = 'Composants web gratuits (boutons, cartes, CSS) — à copier';
		}
		return $parts;
	}
}
