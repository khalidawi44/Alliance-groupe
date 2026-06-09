<?php
/**
 * Notre carte (menu du restaurant) — affichage elegant + editable.
 *
 * - Shortcode [ag_restaurant_carte]
 * - Remplace le contenu de la page de slug "carte" par la vraie carte.
 * - Editable dans Apparence > Personnaliser > « Notre carte (menu) » :
 *   format texte simple ("## Section" + "Nom | Description | Prix").
 *
 * @package AG_Starter_Restaurant
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AG_Restaurant_Carte {

	const OPT = 'ag_restaurant_carte_menu';

	public static function init() {
		add_shortcode( 'ag_restaurant_carte', array( __CLASS__, 'render' ) );
		add_filter( 'the_content', array( __CLASS__, 'maybe_replace' ) );
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
			'description' => __( 'Une ligne « ## Titre » par section. Un plat par ligne : Nom | Description | Prix (prix vide = aucun prix).', 'ag-starter-restaurant' ),
		) );
		$wp->add_setting( self::OPT, array(
			'default'           => self::default_menu(),
			'sanitize_callback' => array( __CLASS__, 'sanitize' ),
		) );
		$wp->add_control( self::OPT, array(
			'label'       => __( 'Votre carte', 'ag-starter-restaurant' ),
			'section'     => 'ag_restaurant_carte',
			'type'        => 'textarea',
			'input_attrs' => array( 'rows' => 16 ),
		) );
	}

	public static function sanitize( $v ) {
		return wp_kses_post( $v );
	}

	public static function maybe_replace( $content ) {
		if ( is_admin() || ! is_page() || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}
		$post = get_post();
		if ( ! $post || 'carte' !== $post->post_name ) {
			return $content;
		}
		// La carte EST le contenu : on remplace le texte placeholder.
		return self::render();
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
		<section class="ag-carte-menu" style="max-width:780px;margin:30px auto 60px;padding:0 20px;">
			<style>
			/* Anti-fondu : force toute la page (titre + carte) en pleine
			   opacite, sans transform/filtre (certains effets premium peuvent
			   l'attenuer sur les pages internes). */
			body.ag-premium-mode .ag-plain-page,
			body.ag-premium-mode .ag-plain-page *,
			.ag-carte-menu, .ag-carte-menu *{opacity:1 !important;animation:none !important;transform:none !important;filter:none !important;}
			.ag-carte-card{background:linear-gradient(180deg,rgba(227,187,79,.05),rgba(255,255,255,.02));border:1px solid rgba(227,187,79,.32);border-radius:16px;padding:44px 48px;box-shadow:0 20px 60px rgba(0,0,0,.45);}
			@media(max-width:560px){.ag-carte-card{padding:32px 22px;}}
			.ag-carte-top{text-align:center;margin-bottom:40px;}
			.ag-carte-top__sub{color:#f0c34e !important;letter-spacing:5px;text-transform:uppercase;font-size:.85rem;font-weight:700;margin:0;}
			.ag-carte-top__rule{width:90px;height:2px;background:#f0c34e;margin:14px auto 0;}
			.ag-carte-section{margin-bottom:46px;}
			.ag-carte-section:last-child{margin-bottom:0;}
			.ag-carte-section__title{font-family:'Playfair Display',Georgia,serif;color:#ffd766 !important;font-size:2.1rem;text-align:center;margin:0 0 8px;letter-spacing:.4px;}
			.ag-carte-section__rule{width:70px;height:2px;background:#f0c34e;margin:0 auto 28px;}
			.ag-carte-item{margin-bottom:22px;}
			.ag-carte-line{display:flex;align-items:baseline;gap:10px;}
			.ag-carte-name{color:#fffaf0 !important;font-weight:700;font-size:1.14rem;}
			.ag-carte-dots{flex:1 1 auto;border-bottom:1px dotted rgba(240,195,78,.7);transform:none;align-self:center;min-width:18px;}
			.ag-carte-price{color:#ffd766 !important;font-weight:800;white-space:nowrap;font-size:1.12rem;}
			.ag-carte-desc{color:#e2d6b3 !important;font-style:italic;font-size:.96rem;margin-top:4px;line-height:1.5;}
			</style>
			<div class="ag-carte-card">
			<div class="ag-carte-top">
				<p class="ag-carte-top__sub">La Carte</p>
				<div class="ag-carte-top__rule"></div>
			</div>
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
			</div>
		</section>
		<?php
		return ob_get_clean();
	}
}
AG_Restaurant_Carte::init();
