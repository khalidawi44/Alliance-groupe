<?php
/**
 * Guide d'utilisation — page admin centralisee qui documente CHAQUE
 * element editable du theme AG Starter Avocat.
 *
 * Apparait dans : "Apparence > 📖 Guide d'utilisation".
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AG_Avocat_Guide {

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ), 25 );
	}

	public static function register_menu() {
		add_theme_page(
			__( 'User guide', 'ag-starter-avocat' ),
			'📖 ' . __( 'User guide', 'ag-starter-avocat' ),
			'edit_posts',
			'ag-avocat-guide',
			array( __CLASS__, 'render' )
		);
	}

	private static function get_sections() {
		return array(

			array(
				'icon'  => '🏠',
				'title' => __( 'Home page', 'ag-starter-avocat' ),
				'desc'  => __( 'The TITLES + INTROS of each home section now come DIRECTLY from the matching pages. Edit the page (Pages > Expertise, Fees, Firm, Appointment): its TITLE becomes the section H2 and its first paragraph becomes the intro.', 'ag-starter-avocat' ),
				'rows'  => array(
					array( __( 'Hero — prefix + firm name + subtitle', 'ag-starter-avocat' ), __( 'Appearance > Customize > Hero', 'ag-starter-avocat' ) ),
					array( __( 'Hero — CTA button + URL', 'ag-starter-avocat' ), __( 'Appearance > Customize > Hero', 'ag-starter-avocat' ) ),
					array( __( 'Title + intro of the EXPERTISE section (home)', 'ag-starter-avocat' ), __( 'Pages > Expertise: title + first paragraph', 'ag-starter-avocat' ) ),
					array( __( 'Title + intro of the FEES section (home)', 'ag-starter-avocat' ), __( 'Pages > Fees: title + first paragraph', 'ag-starter-avocat' ) ),
					array( __( 'Title + intro of the FIRM section (home)', 'ag-starter-avocat' ), __( 'Pages > Firm: title + first paragraph', 'ag-starter-avocat' ) ),
					array( __( 'Title + intro of the APPOINTMENT section (home)', 'ag-starter-avocat' ), __( 'Pages > Appointment: title + first paragraph', 'ag-starter-avocat' ) ),
					array( __( 'Attorney bio (photo, name, bar, bio)', 'ag-starter-avocat' ), __( 'Appearance > Customize > The Attorney', 'ag-starter-avocat' ) ),
					array( __( 'Fee rates (3 tiers)', 'ag-starter-avocat' ), __( 'Appearance > Customize > Fees', 'ag-starter-avocat' ) ),
					array( __( 'Address, hours, contact, emergency', 'ag-starter-avocat' ), __( 'Appearance > Customize > Firm', 'ag-starter-avocat' ) ),
					array( __( 'Google Maps map (embed)', 'ag-starter-avocat' ), __( 'Appearance > Customize > Firm > Map embed', 'ag-starter-avocat' ) ),
					array( __( 'Appointment form GDPR text', 'ag-starter-avocat' ), __( 'Appearance > Customize > Appointment > GDPR', 'ag-starter-avocat' ) ),
				),
			),

			array(
				'icon'  => '⚖️',
				'title' => __( 'Practice areas (CPT)', 'ag-starter-avocat' ),
				'desc'  => __( 'Each practice area is a Custom Post Type with its icon, its example cases and its detailed page.', 'ag-starter-avocat' ),
				'rows'  => array(
					array( __( 'List of areas (title, excerpt, icon, examples)', 'ag-starter-avocat' ), __( 'Areas > admin (CPT ag_domaine)', 'ag-starter-avocat' ) ),
					array( __( 'Background image per area', 'ag-starter-avocat' ), __( 'Areas > featured image', 'ag-starter-avocat' ) ),
				),
			),

			array(
				'icon'  => '📄',
				'title' => __( 'Dedicated pages', 'ag-starter-avocat' ),
				'desc'  => __( 'Each home section has a WP page. The title + first paragraph become the section header on the home page.', 'ag-starter-avocat' ),
				'rows'  => array(
					array( __( 'Practice areas', 'ag-starter-avocat' ), __( 'Pages > Expertise (slug: expertise)', 'ag-starter-avocat' ) ),
					array( __( 'Fees', 'ag-starter-avocat' ), __( 'Pages > Fees (slug: honoraires)', 'ag-starter-avocat' ) ),
					array( __( 'Firm', 'ag-starter-avocat' ), __( 'Pages > Firm (slug: cabinet)', 'ag-starter-avocat' ) ),
					array( __( 'Book an appointment', 'ag-starter-avocat' ), __( 'Pages > Appointment (slug: rendez-vous)', 'ag-starter-avocat' ) ),
					array( __( 'Legal notice', 'ag-starter-avocat' ), __( 'Pages > Legal notice', 'ag-starter-avocat' ) ),
				),
			),

			array(
				'icon'  => '🧭',
				'title' => __( 'Menus, header, footer', 'ag-starter-avocat' ),
				'desc'  => __( 'Navigation and footer editable without coding.', 'ag-starter-avocat' ),
				'rows'  => array(
					array( __( 'Logo (header + footer)', 'ag-starter-avocat' ), __( 'Appearance > Customize > Site Identity (logo)', 'ag-starter-avocat' ) ),
					array( __( 'Main menu links', 'ag-starter-avocat' ), __( 'Appearance > Menus > "Main menu"', 'ag-starter-avocat' ) ),
					array( __( 'Address, email, phone (footer)', 'ag-starter-avocat' ), __( 'Appearance > Customize > Identity', 'ag-starter-avocat' ) ),
				),
			),

			array(
				'icon'  => '🔄',
				'title' => __( 'Updates & support', 'ag-starter-avocat' ),
				'desc'  => __( 'Automatic updates. Manual check available.', 'ag-starter-avocat' ),
				'rows'  => array(
					array( __( 'Force theme update', 'ag-starter-avocat' ), admin_url( 'themes.php?ag_avocat_check_theme=1' ) ),
				),
			),
		);
	}

	public static function render() {
		?>
		<div class="wrap" style="max-width:1100px;">
			<h1 style="display:flex;align-items:center;gap:10px;">
				📖 <?php esc_html_e( 'User guide', 'ag-starter-avocat' ); ?>
				<small style="font-size:.55em;color:#888;font-weight:normal;">v<?php echo esc_html( wp_get_theme()->get( 'Version' ) ); ?></small>
			</h1>

			<div class="notice notice-info inline" style="border-left-color:#D4B45C;padding:14px 18px;margin:10px 0 24px;">
				<p style="margin:0;font-size:1.05rem;"><strong><?php esc_html_e( 'This page lists EVERYTHING you can change without coding.', 'ag-starter-avocat' ); ?></strong></p>
				<p style="margin:6px 0 0;color:#555;"><?php esc_html_e( 'Each row shows an area of the site and where to change it.', 'ag-starter-avocat' ); ?></p>
			</div>

			<style>
				.ag-avocat-guide-section { background:#fff; border:1px solid #e0e0e0; border-radius:8px; padding:18px 22px; margin-bottom:18px; }
				.ag-avocat-guide-section h2 { margin:0 0 6px; font-size:1.25rem; display:flex; align-items:center; gap:8px; }
				.ag-avocat-guide-section .desc { color:#666; margin:0 0 14px; font-style:italic; }
				.ag-avocat-guide-section table { width:100%; border-collapse:collapse; }
				.ag-avocat-guide-section table td { padding:8px 12px; border-bottom:1px solid #f0f0f0; vertical-align:top; }
				.ag-avocat-guide-section table td:first-child { width:55%; font-weight:500; }
				.ag-avocat-guide-section table td:last-child { color:#444; }
				.ag-avocat-guide-section table td a { font-weight:600; color:#D4B45C; }
				.ag-avocat-guide-section table tr:last-child td { border-bottom:none; }
			</style>

			<?php foreach ( self::get_sections() as $section ) : ?>
				<div class="ag-avocat-guide-section">
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
										<a href="<?php echo esc_url( $where ); ?>"><?php esc_html_e( '→ Go', 'ag-starter-avocat' ); ?></a>
									<?php else : ?>
										<?php echo esc_html( $where ); ?>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</table>
				</div>
			<?php endforeach; ?>

			<div class="ag-avocat-guide-section" style="background:#fffbe6;border-color:#f0d000;">
				<h2>💡 <?php esc_html_e( "Can't find it?", 'ag-starter-avocat' ); ?></h2>
				<p><?php echo wp_kses_post( __( 'If you want to change something not in this list, open the relevant page with the <strong>Gutenberg block editor</strong> (Pages > your page).', 'ag-starter-avocat' ) ); ?></p>
			</div>
		</div>
		<?php
	}
}

AG_Avocat_Guide::init();
