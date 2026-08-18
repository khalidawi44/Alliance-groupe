<?php
/**
 * Guide d'utilisation — page admin centralisee qui documente CHAQUE
 * element editable du theme Gwen Services.
 *
 * Apparait dans : "Apparence > 📖 Guide d'utilisation".
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AG_Domicile_Guide {

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ), 25 );
	}

	public static function register_menu() {
		add_theme_page(
			__( 'Guide d\'utilisation', 'ag-gwen-services' ),
			'📖 ' . __( 'Guide d\'utilisation', 'ag-gwen-services' ),
			'edit_posts',
			'ag-domicile-guide',
			array( __CLASS__, 'render' )
		);
	}

	private static function get_sections() {
		return array(

			array(
				'icon'  => '🏠',
				'title' => 'Page d\'accueil',
				'desc'  => '⭐ Le plus simple : allez dans Apparence > 🎯 Configuration métier et choisissez votre spécialité (seniors, familles, handicap). Tout l\'accueil (hero, services, témoignages) s\'adapte. Vous pouvez ensuite affiner les textes du hero et le contenu des pages.',
				'rows'  => array(
					array( 'Choisir la spécialité (seniors / familles / handicap)', 'Apparence > 🎯 Configuration métier' ),
					array( 'Hero — préfixe + nom + sous-titre',                   'Apparence > Personnaliser > Hero' ),
					array( 'Hero — bouton CTA + URL',                             'Apparence > Personnaliser > Hero' ),
					array( 'Image du hero',                                       'Apparence > 🎯 Configuration métier (ou Personnaliser)' ),
					array( 'Grille de services de l\'accueil',                    'Apparence > 🎯 Configuration métier' ),
					array( 'Témoignages affichés',                               'Apparence > 🎯 Configuration métier (3 avis)' ),
					array( 'Couleurs + typo + logo',                              'Apparence > Personnaliser > Couleurs / Identité' ),
				),
			),

			array(
				'icon'  => '📄',
				'title' => 'Pages dédiées',
				'desc'  => 'Le thème crée automatiquement les pages ci-dessous (avec un modèle dédié). Modifiez leur contenu dans Pages.',
				'rows'  => array(
					array( 'Prestations',          'Pages > Nos prestations (slug : prestations)' ),
					array( 'Zones d\'intervention', 'Pages > Zones d\'intervention (slug : zones-intervention)' ),
					array( 'Témoignages',          'Pages > Témoignages (slug : realisations)' ),
					array( 'À propos',             'Pages > À propos (slug : qui-sommes-nous)' ),
					array( 'Devis',                'Pages > Demander un devis (slug : devis)' ),
					array( 'Contact',              'Pages > Contact (slug : contact)' ),
					array( 'Mentions légales',     'Pages > Mentions légales' ),
				),
			),

			array(
				'icon'  => '🧭',
				'title' => 'Menus, header, footer',
				'desc'  => 'Navigation et pied de page éditables sans coder.',
				'rows'  => array(
					array( 'Logo',                                           'Apparence > Personnaliser > Identité du site (logo)' ),
					array( 'Liens du menu principal',                        'Apparence > Menus > "Menu principal"' ),
					array( 'Adresse, téléphone, email (footer)',             'Apparence > Personnaliser > Identité' ),
				),
			),

			array(
				'icon'  => '🔄',
				'title' => 'Mise à jour & support',
				'desc'  => 'Mises à jour automatiques. Vérification manuelle possible.',
				'rows'  => array(
					array( 'Forcer mise à jour du thème',     admin_url( 'themes.php?ag_domicile_check_theme=1' ) ),
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
				.ag-domicile-guide-section { background:#fff; border:1px solid #e0e0e0; border-radius:8px; padding:18px 22px; margin-bottom:18px; }
				.ag-domicile-guide-section h2 { margin:0 0 6px; font-size:1.25rem; display:flex; align-items:center; gap:8px; }
				.ag-domicile-guide-section .desc { color:#666; margin:0 0 14px; font-style:italic; }
				.ag-domicile-guide-section table { width:100%; border-collapse:collapse; }
				.ag-domicile-guide-section table td { padding:8px 12px; border-bottom:1px solid #f0f0f0; vertical-align:top; }
				.ag-domicile-guide-section table td:first-child { width:55%; font-weight:500; }
				.ag-domicile-guide-section table td:last-child { color:#444; }
				.ag-domicile-guide-section table td a { font-weight:600; color:#D4B45C; }
				.ag-domicile-guide-section table tr:last-child td { border-bottom:none; }
			</style>

			<?php foreach ( self::get_sections() as $section ) : ?>
				<div class="ag-domicile-guide-section">
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

			<div class="ag-domicile-guide-section" style="background:#fffbe6;border-color:#f0d000;">
				<h2>💡 Vous ne trouvez pas ?</h2>
				<p>Si vous voulez modifier quelque chose qui n'est pas dans cette liste, ouvrez la page concernée avec <strong>l'éditeur de blocs Gutenberg</strong> (Pages > votre page).</p>
			</div>
		</div>
		<?php
	}
}

AG_Domicile_Guide::init();
