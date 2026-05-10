<?php
/**
 * Guide d'utilisation — page admin centralisee qui documente CHAQUE
 * element editable du theme AG Starter Restaurant.
 *
 * Apparait dans : "Apparence > 📖 Guide d'utilisation".
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AG_Resto_Guide {

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ), 25 );
	}

	public static function register_menu() {
		add_theme_page(
			__( 'Guide d\'utilisation', 'ag-starter-restaurant' ),
			'📖 ' . __( 'Guide d\'utilisation', 'ag-starter-restaurant' ),
			'edit_posts',
			'ag-resto-guide',
			array( __CLASS__, 'render' )
		);
	}

	private static function get_sections() {
		return array(

			array(
				'icon'  => '🏠',
				'title' => 'Page d\'accueil',
				'desc'  => '⭐ Les TITRES + INTROS de chaque section de l\'accueil viennent maintenant DIRECTEMENT des pages correspondantes. Vous editez la page (Pages > Carte, Reservation, Privatisation, Histoire, Actualites), son TITRE devient le H2 de la section, son premier paragraphe devient l\'intro.',
				'rows'  => array(
					array( 'Hero — préfixe + nom du restaurant + sous-titre',  'Apparence > Personnaliser > Hero' ),
					array( 'Hero — bouton CTA + URL',                          'Apparence > Personnaliser > Hero' ),
					array( 'Titre + intro section CARTE (accueil)',            'Pages > Carte : titre + 1er paragraphe' ),
					array( 'Titre + intro section RESERVATION (accueil)',      'Pages > Reservation : titre + 1er paragraphe' ),
					array( 'Titre + intro section PRIVATISATION (accueil)',    'Pages > Privatisation : titre + 1er paragraphe' ),
					array( 'Titre + intro section HISTOIRE (accueil)',         'Pages > Histoire : titre + 1er paragraphe' ),
					array( 'Titre + intro section ACTUALITÉS (accueil)',       'Pages > Actualités : titre + 1er paragraphe' ),
					array( 'Texte 2e paragraphe HISTOIRE',                     'Apparence > Personnaliser > Histoire > p2' ),
					array( 'Couleurs + typo + logo',                           'Apparence > Personnaliser > Couleurs / Identité' ),
				),
			),

			array(
				'icon'  => '📄',
				'title' => 'Pages dédiées',
				'desc'  => 'Chaque section de l\'accueil a une page WP. Le titre + premier paragraphe deviennent l\'entête de la section. Vous pouvez aussi remplir entièrement la page pour avoir une page détaillée accessible via le menu.',
				'rows'  => array(
					array( 'Carte / Menu',         'Pages > Carte (slug : carte)' ),
					array( 'Réservation',          'Pages > Reservation (slug : reservation)' ),
					array( 'Privatisation',        'Pages > Privatisation (slug : privatisation)' ),
					array( 'Histoire / À propos',  'Pages > Histoire (slug : histoire)' ),
					array( 'Actualités',           'Pages > Actualités (slug : actualites)' ),
					array( 'Mentions légales',     'Pages > Mentions légales' ),
				),
			),

			array(
				'icon'  => '📰',
				'title' => 'Articles (actualités)',
				'desc'  => 'Les 3 derniers articles sont affichés sur l\'accueil dans la section Actualités.',
				'rows'  => array(
					array( 'Articles d\'actualité',  'Articles > admin' ),
					array( 'Image à la une',         'Articles > image à la une' ),
				),
			),

			array(
				'icon'  => '🧭',
				'title' => 'Menus, header, footer',
				'desc'  => 'Navigation et pied de page éditables sans coder.',
				'rows'  => array(
					array( 'Logo',                                           'Apparence > Personnaliser > Identité du site (logo)' ),
					array( 'Liens du menu principal',                        'Apparence > Menus > "Menu principal"' ),
					array( 'Adresse, téléphone, horaires (footer)',          'Apparence > Personnaliser > Identité' ),
				),
			),

			array(
				'icon'  => '🔄',
				'title' => 'Mise à jour & support',
				'desc'  => 'Mises à jour automatiques. Vérification manuelle possible.',
				'rows'  => array(
					array( 'Forcer mise à jour du thème',     admin_url( 'themes.php?ag_resto_check_theme=1' ) ),
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
				<p style="margin:0;font-size:1.05rem;"><strong>Cette page liste TOUT ce que vous pouvez modifier sans coder.</strong></p>
				<p style="margin:6px 0 0;color:#555;">Chaque ligne indique une zone du site et où la modifier.</p>
			</div>

			<style>
				.ag-resto-guide-section { background:#fff; border:1px solid #e0e0e0; border-radius:8px; padding:18px 22px; margin-bottom:18px; }
				.ag-resto-guide-section h2 { margin:0 0 6px; font-size:1.25rem; display:flex; align-items:center; gap:8px; }
				.ag-resto-guide-section .desc { color:#666; margin:0 0 14px; font-style:italic; }
				.ag-resto-guide-section table { width:100%; border-collapse:collapse; }
				.ag-resto-guide-section table td { padding:8px 12px; border-bottom:1px solid #f0f0f0; vertical-align:top; }
				.ag-resto-guide-section table td:first-child { width:55%; font-weight:500; }
				.ag-resto-guide-section table td:last-child { color:#444; }
				.ag-resto-guide-section table td a { font-weight:600; color:#D4B45C; }
				.ag-resto-guide-section table tr:last-child td { border-bottom:none; }
			</style>

			<?php foreach ( self::get_sections() as $section ) : ?>
				<div class="ag-resto-guide-section">
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

			<div class="ag-resto-guide-section" style="background:#fffbe6;border-color:#f0d000;">
				<h2>💡 Vous ne trouvez pas ?</h2>
				<p>Si vous voulez modifier quelque chose qui n'est pas dans cette liste, ouvrez la page concernée avec <strong>l'éditeur de blocs Gutenberg</strong> (Pages > votre page).</p>
			</div>
		</div>
		<?php
	}
}

AG_Resto_Guide::init();
