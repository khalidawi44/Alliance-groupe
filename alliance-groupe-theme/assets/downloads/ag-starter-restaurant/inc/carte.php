<?php
/**
 * Notre carte (menu du restaurant) — affichage elegant + editable.
 *
 * - Shortcode [ag_restaurant_carte]
 * - Injecte automatiquement la carte sur la page de slug "carte".
 * - Editable dans Apparence > Personnaliser > « Notre carte (menu) » :
 *   un format texte simple ("## Section" + "Nom | Description | Prix").
 *
 * @package AG_Starter_Restaurant
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AG_Restaurant_Carte {

	const OPT = 'ag_restaurant_carte_menu';

	public static function init() {
		add_shortcode( 'ag_restaurant_carte', array( __CLASS__, 'render' ) );
		add_filter( 'the_content', array( __CLASS__, 'maybe_append' ) );
		add_action( 'customize_register', array( __CLASS__, 'customize' ) );
	}

	public static function default_menu() {
		return "## Entrées\n"
			. "Soupe à l'oignon gratinée | Comté affiné, croûtons maison | 8\n"
			. "Œuf parfait | Crème de champignons, lard fumé | 11\n"
			. "Foie gras maison | Chutney de figues, pain brioché | 16\n\n"
			. "## Plats\n"
			. "Filet de bœuf, sauce au poivre | Gratin dauphinois, légumes du marché | 24\n"
			. "Suprême de volaille fermière | Risotto crémeux au parmesan | 19\n"
			. "Pavé de cabillaud | Beurre blanc, écrasé de pommes de terre | 21\n"
			. "Risotto aux cèpes (végétarien) | Parmesan, huile de truffe | 17\n\n"
			. "## Desserts\n"
			. "Fondant au chocolat | Cœur coulant, glace vanille | 9\n"
			. "Tarte fine aux pommes | Caramel beurre salé | 8\n"
			. "Crème brûlée à la vanille | | 7\n\n"
			. "## Formules\n"
			. "Menu du midi | Entrée + plat ou plat + dessert, en semaine | 18\n"
			. "Menu complet | Entrée + plat + dessert | 29";
	}

	public static function customize( $wp ) {
		$wp->add_section( 'ag_restaurant_carte', array(
			'title'       => '🍽️ ' . __( 'Notre carte (menu)', 'ag-starter-restaurant' ),
			'priority'    => 27,
			'description' => __( 'Une section par ligne « ## Titre ». Un plat par ligne : Nom | Description | Prix (laissez vide pour aucun prix).', 'ag-starter-restaurant' ),
		) );
		$wp->add_setting( self::OPT, array(
			'default'           => self::default_menu(),
			'sanitize_callback' => array( __CLASS__, 'sanitize' ),
		) );
		$wp->add_control( self::OPT, array(
			'label'   => __( 'Votre carte', 'ag-starter-restaurant' ),
			'section' => 'ag_restaurant_carte',
			'type'    => 'textarea',
			'input_attrs' => array( 'rows' => 16 ),
		) );
	}

	public static function sanitize( $v ) {
		return wp_kses_post( $v );
	}

	public static function maybe_append( $content ) {
		if ( is_admin() || ! is_page() || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}
		$post = get_post();
		if ( ! $post || 'carte' !== $post->post_name ) {
			return $content;
		}
		if ( false !== strpos( $content, 'ag-carte-menu' ) ) {
			return $content;
		}
		return $content . self::render();
	}

	private static function parse( $raw ) {
		$sections = array();
		$idx = -1;
		foreach ( preg_split( '/\r\n|\r|\n/', (string) $raw ) as $line ) {
			$line = trim( $line );
			if ( '' === $line ) continue;
			if ( '##' === substr( $line, 0, 2 ) ) {
				$sections[] = array( 'title' => trim( substr( $line, 2 ) ), 'items' => array() );
				$idx = count( $sections ) - 1;
			} elseif ( $idx >= 0 ) {
				$parts = array_map( 'trim', explode( '|', $line ) );
				$sections[ $idx ]['items'][] = array(
					'name'  => isset( $parts[0] ) ? $parts[0] : '',
					'desc'  => isset( $parts[1] ) ? $parts[1] : '',
					'price' => isset( $parts[2] ) ? $parts[2] : '',
				);
			}
		}
		return $sections;
	}

	private static function fmt_price( $p ) {
		$p = trim( $p );
		if ( '' === $p ) return '';
		// Nombre seul -> ajoute " €".
		if ( preg_match( '/^\d+([.,]\d+)?$/', $p ) ) {
			return $p . ' €';
		}
		return $p;
	}

	public static function render() {
		$sections = self::parse( get_theme_mod( self::OPT, self::default_menu() ) );
		if ( empty( $sections ) ) return '';

		ob_start();
		?>
		<section class="ag-carte-menu" style="max-width:760px;margin:30px auto 50px;padding:0 20px;">
			<style>
			.ag-carte-section{margin-bottom:42px;}
			.ag-carte-section__title{font-family:'Playfair Display',Georgia,serif;color:#c9a24b;font-size:1.9rem;text-align:center;margin:0 0 6px;letter-spacing:.4px;}
			.ag-carte-section__rule{width:64px;height:2px;background:#c9a24b;opacity:.55;margin:0 auto 26px;}
			.ag-carte-item{margin-bottom:18px;}
			.ag-carte-line{display:flex;align-items:baseline;gap:10px;}
			.ag-carte-name{color:#f3ead4;font-weight:600;font-size:1.06rem;}
			.ag-carte-dots{flex:1 1 auto;border-bottom:1px dotted rgba(201,162,75,.45);transform:translateY(-4px);min-width:18px;}
			.ag-carte-price{color:#c9a24b;font-weight:700;white-space:nowrap;font-size:1.04rem;}
			.ag-carte-desc{color:#b3a88c;font-style:italic;font-size:.93rem;margin-top:3px;line-height:1.5;}
			</style>
			<?php foreach ( $sections as $sec ) : ?>
				<div class="ag-carte-section">
					<h2 class="ag-carte-section__title"><?php echo esc_html( $sec['title'] ); ?></h2>
					<div class="ag-carte-section__rule"></div>
					<?php foreach ( $sec['items'] as $it ) : if ( '' === $it['name'] ) continue; ?>
						<div class="ag-carte-item">
							<div class="ag-carte-line">
								<span class="ag-carte-name"><?php echo esc_html( $it['name'] ); ?></span>
								<span class="ag-carte-dots"></span>
								<?php $price = self::fmt_price( $it['price'] ); if ( '' !== $price ) : ?>
									<span class="ag-carte-price"><?php echo esc_html( $price ); ?></span>
								<?php endif; ?>
							</div>
							<?php if ( '' !== $it['desc'] ) : ?>
								<div class="ag-carte-desc"><?php echo esc_html( $it['desc'] ); ?></div>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>
		</section>
		<?php
		return ob_get_clean();
	}
}
AG_Restaurant_Carte::init();
