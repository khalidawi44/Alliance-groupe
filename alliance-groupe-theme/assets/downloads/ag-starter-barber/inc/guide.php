<?php
/**
 * Guide d'utilisation — page admin centralisee qui documente CHAQUE
 * element editable du theme AG Starter Barber.
 *
 * Apparait dans le menu admin gauche : "Apparence > 📖 Guide d'utilisation".
 *
 * REGLE INTERNE : a chaque ajout / suppression / changement d'un champ
 * Customizer ou setting du theme, mettre a jour ce fichier (la liste
 * $sections ci-dessous) pour refleter la nouvelle realite.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AG_Barber_Guide {

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ), 25 );
	}

	public static function register_menu() {
		add_theme_page(
			__( 'Guide d\'utilisation', 'ag-starter-barber' ),
			'📖 ' . __( 'Guide d\'utilisation', 'ag-starter-barber' ),
			'edit_posts',
			'ag-barber-guide',
			array( __CLASS__, 'render' )
		);
	}

	private static function get_sections() {
		return array(

			// ─── ACCUEIL ─────────────────────────────────────────────
			array(
				'icon'  => '🏠',
				'title' => 'Page d\'accueil',
				'desc'  => '⭐ Les TITRES + INTROS de chaque section de l\'accueil viennent maintenant DIRECTEMENT des pages correspondantes. Vous editez la page (Pages > File d\'attente, Tarifs...), son TITRE devient le H2 de la section, son premier paragraphe devient l\'intro. Une seule edition par section.',
				'rows'  => array(
					array( 'Hero — slogan, titre, sous-titre, prix vedette',     'Apparence > Personnaliser > Hero' ),
					array( 'Hero — boutons CTA primaire + secondaire',           'Apparence > Personnaliser > Hero' ),
					array( 'Titre + intro section FILE D\'ATTENTE (accueil)',     'Pages > File d\'attente : titre + 1er paragraphe' ),
					array( 'Titre + intro section TARIFS (accueil)',             'Pages > Tarifs : titre + 1er paragraphe' ),
					array( 'Titre + intro section COMMENT ÇA MARCHE (accueil)',  'Pages > Comment ça marche : titre + 1er paragraphe' ),
					array( 'Cards tarifs (services + prix + temps)',             'Apparence > Personnaliser > File d\'attente > Services' ),
					array( 'Étapes "comment ça marche" (3 etapes)',              'Apparence > Personnaliser > Comment ça marche' ),
					array( 'Couleurs + typo + logo',                              'Apparence > Personnaliser > Couleurs / Identité' ),
				),
			),

			// ─── FILE D'ATTENTE ─────────────────────────────────────
			array(
				'icon'  => '🎫',
				'title' => 'Système de file d\'attente',
				'desc'  => 'Le coeur du theme : QR code, ticket virtuel, temps d\'attente en temps reel. Tout est gere depuis un menu dedie.',
				'rows'  => array(
					array( 'Tickets en cours / appeler le suivant',             'File d\'attente (menu admin)' ),
					array( 'Liste des prestations + prix + duree',              'Apparence > Personnaliser > File d\'attente > Services' ),
					array( 'Nom du salon + horaires affiches',                  'Apparence > Personnaliser > File d\'attente > Identite' ),
					array( 'QR code (genere automatiquement)',                  'File d\'attente > QR code' ),
				),
			),

			// ─── PAGES DEDIEES ───────────────────────────────────────
			array(
				'icon'  => '📄',
				'title' => 'Pages dédiées',
				'desc'  => 'Chaque section de l\'accueil peut avoir une page WP du meme nom (slug). Le titre + premier paragraphe de la page deviennent l\'entete de la section. Vous pouvez aussi remplir la page entiere avec des blocs Gutenberg pour avoir une page detaillee accessible via le menu.',
				'rows'  => array(
					array( 'File d\'attente',         'Pages > File d\'attente (slug : file-attente)' ),
					array( 'Tarifs',                  'Pages > Tarifs (slug : tarifs)' ),
					array( 'Comment ça marche',       'Pages > Comment ça marche (slug : comment-ca-marche)' ),
					array( 'Mentions légales',        'Pages > Mentions légales' ),
				),
			),

			// ─── HEADER / FOOTER / MENUS ─────────────────────────────
			array(
				'icon'  => '🧭',
				'title' => 'Menus, header, footer',
				'desc'  => 'La structure de navigation et le pied de page sont editables sans coder.',
				'rows'  => array(
					array( 'Logo (header + footer)',                         'Apparence > Personnaliser > Identité du site (logo)' ),
					array( 'Liens du menu principal',                        'Apparence > Menus > "Menu principal"' ),
					array( 'Adresse, téléphone, horaires (footer)',          'Apparence > Personnaliser > Identité' ),
				),
			),

			// ─── MISE A JOUR & SUPPORT ───────────────────────────────
			array(
				'icon'  => '🔄',
				'title' => 'Mise à jour & support',
				'desc'  => 'Le thème se met à jour automatiquement. Vous pouvez forcer la vérification manuellement.',
				'rows'  => array(
					array( 'Forcer mise à jour du thème',     admin_url( 'themes.php?ag_barber_check_theme=1' ) ),
					array( 'AG Starter Companion (plugin)',   admin_url( 'plugin-install.php?s=AG+Starter+Companion&tab=search' ) ),
				),
			),
		);
	}

	public static function render() {
		?>
		<div class="wrap" style="max-width:1100px;">
			<h1 style="display:flex;align-items:center;gap:10px;">
				📖 Guide d'utilisation
				<small style="font-size:.55em;color:#888;font-weight:normal;">v<?php echo esc_html( wp_get_theme()->get( 'Version' ) ); ?></small>
			</h1>

			<div class="notice notice-info inline" style="border-left-color:#D4B45C;padding:14px 18px;margin:10px 0 24px;">
				<p style="margin:0;font-size:1.05rem;"><strong>Cette page liste TOUT ce que vous pouvez modifier sur votre site sans coder.</strong></p>
				<p style="margin:6px 0 0;color:#555;">Chaque ligne indique une zone du site et où la modifier. Cliquez les liens pour aller directement au bon endroit.</p>
			</div>

			<style>
				.ag-barber-guide-section { background:#fff; border:1px solid #e0e0e0; border-radius:8px; padding:18px 22px; margin-bottom:18px; }
				.ag-barber-guide-section h2 { margin:0 0 6px; font-size:1.25rem; display:flex; align-items:center; gap:8px; }
				.ag-barber-guide-section .desc { color:#666; margin:0 0 14px; font-style:italic; }
				.ag-barber-guide-section table { width:100%; border-collapse:collapse; }
				.ag-barber-guide-section table td { padding:8px 12px; border-bottom:1px solid #f0f0f0; vertical-align:top; }
				.ag-barber-guide-section table td:first-child { width:55%; font-weight:500; }
				.ag-barber-guide-section table td:last-child { color:#444; }
				.ag-barber-guide-section table td a { font-weight:600; color:#D4B45C; }
				.ag-barber-guide-section table tr:last-child td { border-bottom:none; }
			</style>

			<?php foreach ( self::get_sections() as $section ) : ?>
				<div class="ag-barber-guide-section">
					<h2><?php echo esc_html( $section['icon'] ); ?> <?php echo esc_html( $section['title'] ); ?></h2>
					<p class="desc"><?php echo esc_html( $section['desc'] ); ?></p>
					<table>
						<?php foreach ( $section['rows'] as $row ) :
							list( $what, $where ) = $row;
							$is_url = ( strpos( $where, admin_url() ) === 0 );
							?>
							<tr>
								<td><?php echo esc_html( $what ); ?></td>
								<td>
									<?php if ( $is_url ) : ?>
										<a href="<?php echo esc_url( $where ); ?>">→ Aller</a>
									<?php else : ?>
										<?php echo esc_html( $where ); ?>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</table>
				</div>
			<?php endforeach; ?>

			<div class="ag-barber-guide-section" style="background:#fffbe6;border-color:#f0d000;">
				<h2>💡 Vous ne trouvez pas ?</h2>
				<p>Si vous voulez modifier quelque chose qui n'est pas dans cette liste, ouvrez la page concernée avec <strong>l'éditeur de blocs Gutenberg</strong> (Pages > votre page) — vous pouvez librement ajouter / supprimer / déplacer des blocs.</p>
			</div>
		</div>
		<?php
	}
}

AG_Barber_Guide::init();
