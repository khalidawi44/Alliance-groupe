<?php
/**
 * Recettes — aimant à clients (lead magnet).
 *
 * - Section « Nos recettes » sur l'accueil : cartes IMAGE -> articles/tutos.
 * - Chaque recette montre un teaser ; les INGRÉDIENTS SECRETS + la recette
 *   complète sont masqués par un shortcode [secret]…[/secret].
 * - Pour débloquer : PAYER (lien configurable) OU RÉSERVER / SE FAIRE LIVRER
 *   (réserver/commander débloque toutes les recettes — offert).
 *
 * Tout est réglable par le client : Personnaliser > « Recettes (aimant clients) ».
 *
 * @package AG_Starter_Restaurant
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AG_Restaurant_Recettes {

	const CAT      = 'recettes';
	const COOKIE   = 'ag_recipe_unlocked';

	public static function init() {
		add_shortcode( 'secret', array( __CLASS__, 'shortcode_secret' ) );
		add_action( 'customize_register', array( __CLASS__, 'customize' ) );
		add_action( 'after_setup_theme', array( __CLASS__, 'maybe_seed' ) );
		// Mise en page de l'article recette (photo en haut + cadre lisible).
		add_filter( 'the_content', array( __CLASS__, 'recipe_content' ), 6 );
		// Réserver / commander débloque les recettes.
		add_action( 'ag_restaurant_order_placed', array( __CLASS__, 'unlock_cookie' ) );
		add_action( 'ag_restaurant_reservation_done', array( __CLASS__, 'unlock_cookie' ) );
	}

	/** Le post appartient-il à la catégorie « recettes » ? */
	public static function is_recipe( $id ) {
		$cat = get_category_by_slug( self::CAT );
		return $cat ? has_category( (int) $cat->term_id, $id ) : false;
	}

	/** Met en page l'article recette : grande photo + cadre + style étapes. */
	public static function recipe_content( $content ) {
		if ( is_admin() || ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) return $content;
		$id = get_the_ID();
		if ( ! self::is_recipe( $id ) ) return $content;

		$img = self::recipe_img( $id );
		$hero = ( $img && ! has_post_thumbnail( $id ) )
			? '<figure class="ag-recipe-hero"><img src="' . esc_url( $img ) . '" alt="' . esc_attr( get_the_title( $id ) ) . '" loading="lazy"></figure>'
			: '';

		$css = '<style>
		.ag-recipe-article{max-width:760px;margin:0 auto}
		.ag-recipe-hero{margin:0 0 26px;border-radius:18px;overflow:hidden;box-shadow:0 18px 50px rgba(0,0,0,.35)}
		.ag-recipe-hero img{display:block;width:100%;height:auto;aspect-ratio:16/9;object-fit:cover}
		.ag-recipe-article h2{font-family:"Playfair Display",Georgia,serif;color:var(--ag-color-accent,#c9a24b)!important;font-size:1.5rem;margin:30px 0 12px;padding-bottom:8px;border-bottom:2px solid color-mix(in srgb,var(--ag-color-accent,#c9a24b) 35%,transparent)}
		.ag-recipe-article ul{list-style:none;padding:0;margin:0 0 10px}
		.ag-recipe-article ul li{padding:9px 0 9px 30px;position:relative;border-bottom:1px solid rgba(255,255,255,.08)}
		.ag-recipe-article ul li::before{content:"🍴";position:absolute;left:0;top:8px;font-size:.95rem}
		.ag-recipe-article ol{counter-reset:step;list-style:none;padding:0;margin:0 0 10px}
		.ag-recipe-article ol li{counter-increment:step;position:relative;padding:6px 0 18px 56px;line-height:1.6}
		.ag-recipe-article ol li::before{content:counter(step);position:absolute;left:0;top:2px;width:38px;height:38px;border-radius:50%;background:var(--ag-color-accent,#c9a24b);color:var(--ag-color-on-accent,#fff);display:flex;align-items:center;justify-content:center;font-weight:800;font-family:"Playfair Display",Georgia,serif}
		</style>';

		return $css . '<div class="ag-recipe-article">' . $hero . $content . '</div>';
	}

	/* ---------------- Réglages ---------------- */

	public static function enabled()    { return (bool) get_theme_mod( 'ag_recipe_on', true ); }
	public static function price()      { return trim( (string) get_theme_mod( 'ag_recipe_price', '2,90' ) ); }
	public static function pay_url()    { return trim( (string) get_theme_mod( 'ag_recipe_pay_url', '' ) ); }
	public static function unlock_code(){ return trim( (string) get_theme_mod( 'ag_recipe_unlock_code', 'CHEF' ) ); }
	public static function count()      { return max( 1, (int) get_theme_mod( 'ag_recipe_count', 6 ) ); }
	public static function intro()      { return trim( (string) get_theme_mod( 'ag_recipe_intro', 'Nos recettes maison en pas-à-pas. Le secret du chef (ingrédients cachés) se débloque en réservant, en commandant… ou en le soutenant.' ) ); }

	public static function customize( $wp ) {
		$wp->add_section( 'ag_recettes', array(
			'title'       => '🍳 ' . __( 'Recettes (aimant clients)', 'ag-starter-restaurant' ),
			'priority'    => 26,
			'description' => __( 'Affiche vos recettes/tutos sur l\'accueil. Dans un article, entourez les ingrédients secrets + la recette complète par [secret]…[/secret]. Réserver/commander (ou payer) les débloque.', 'ag-starter-restaurant' ),
		) );
		$add = function( $id, $label, $type, $default, $sanitize ) use ( $wp ) {
			$wp->add_setting( $id, array( 'default' => $default, 'sanitize_callback' => $sanitize ) );
			$wp->add_control( $id, array( 'label' => $label, 'section' => 'ag_recettes', 'type' => $type ) );
		};
		$add( 'ag_recipe_on', __( 'Afficher les recettes sur l\'accueil', 'ag-starter-restaurant' ), 'checkbox', true, 'wp_validate_boolean' );
		$add( 'ag_recipe_intro', __( 'Texte d\'introduction', 'ag-starter-restaurant' ), 'textarea', 'Nos recettes maison en pas-à-pas. Le secret du chef (ingrédients cachés) se débloque en réservant, en commandant… ou en le soutenant.', 'sanitize_textarea_field' );
		$add( 'ag_recipe_count', __( 'Nombre de recettes affichées', 'ag-starter-restaurant' ), 'number', 6, 'absint' );
		$add( 'ag_recipe_price', __( 'Prix pour débloquer une recette (€)', 'ag-starter-restaurant' ), 'text', '2,90', 'sanitize_text_field' );
		$add( 'ag_recipe_pay_url', __( 'Lien de paiement (Stripe / PayPal)', 'ag-starter-restaurant' ), 'text', '', 'esc_url_raw' );
		$add( 'ag_recipe_unlock_code', __( 'Code de déblocage (donné après paiement)', 'ag-starter-restaurant' ), 'text', 'CHEF', 'sanitize_text_field' );
	}

	/* ---------------- Déblocage ---------------- */

	public static function is_unlocked() {
		if ( current_user_can( 'edit_posts' ) ) return true; // l'admin voit tout
		if ( isset( $_COOKIE[ self::COOKIE ] ) && '1' === $_COOKIE[ self::COOKIE ] ) return true;
		$code = self::unlock_code();
		if ( $code && isset( $_GET['unlock'] ) && hash_equals( strtoupper( $code ), strtoupper( sanitize_text_field( wp_unslash( $_GET['unlock'] ) ) ) ) ) {
			self::unlock_cookie();
			return true;
		}
		return false;
	}

	public static function unlock_cookie() {
		if ( headers_sent() ) return;
		setcookie( self::COOKIE, '1', time() + YEAR_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/' );
		$_COOKIE[ self::COOKIE ] = '1';
	}

	/* ---------------- Shortcode [secret] ---------------- */

	public static function shortcode_secret( $atts, $content = '' ) {
		if ( self::is_unlocked() ) {
			return '<div class="ag-rec-unlocked">' . do_shortcode( wpautop( $content ) ) . '</div>';
		}
		$price = self::price();
		$pay   = self::pay_url();
		$resa  = ( $p = get_page_by_path( 'reservation' ) ) ? get_permalink( $p ) : home_url( '/reservation/' );
		$carte = ( $c = get_page_by_path( 'carte' ) ) ? get_permalink( $c ) : home_url( '/carte/' );

		ob_start();
		?>
		<div class="ag-rec-lock">
			<div class="ag-rec-lock__ic">🔒</div>
			<h3>La recette complète est réservée</h3>
			<p>Les <strong>ingrédients secrets du chef</strong> et les étapes détaillées sont cachés. Débloquez-les :</p>
			<div class="ag-rec-lock__btns">
				<?php if ( $pay ) : ?>
					<a class="ag-rec-btn ag-rec-btn--pay" href="<?php echo esc_url( $pay ); ?>" target="_blank" rel="noopener">💳 Débloquer pour <?php echo esc_html( $price ); ?> €</a>
				<?php endif; ?>
				<a class="ag-rec-btn" href="<?php echo esc_url( $resa ); ?>">🍽️ Réserver une table</a>
				<a class="ag-rec-btn" href="<?php echo esc_url( add_query_arg( 'go', 'livraison', $carte ) ); ?>">🛵 Se faire livrer</a>
			</div>
			<p class="ag-rec-lock__fine">✨ <strong>Réserver ou commander débloque TOUTES les recettes — offert.</strong></p>
		</div>
		<style>
		.ag-rec-lock{position:relative;margin:24px 0;padding:32px 26px;text-align:center;border-radius:16px;border:1px dashed color-mix(in srgb, var(--ag-color-accent,#c9a24b) 55%, transparent);background:color-mix(in srgb, var(--ag-color-accent,#c9a24b) 7%, transparent);color:var(--ag-color-text,#2b2a26)}
		.ag-rec-lock__ic{font-size:2.4rem;line-height:1}
		.ag-rec-lock h3{font-family:'Playfair Display',Georgia,serif;color:var(--ag-color-heading,#14843b);margin:8px 0 6px;font-size:1.5rem}
		.ag-rec-lock p{color:var(--ag-color-muted,#6e6a60);max-width:520px;margin:0 auto 16px;line-height:1.6}
		.ag-rec-lock__btns{display:flex;flex-wrap:wrap;gap:12px;justify-content:center;margin:6px 0 14px}
		.ag-rec-btn{display:inline-block;text-decoration:none;font-weight:700;padding:13px 22px;border-radius:999px;border:1px solid var(--ag-color-accent,#c9a24b);color:var(--ag-color-accent,#c9a24b);background:transparent;transition:.15s}
		.ag-rec-btn:hover{background:var(--ag-color-accent,#c9a24b);color:var(--ag-color-on-accent,#fff)}
		.ag-rec-btn--pay{background:var(--ag-color-accent,#c9a24b);color:var(--ag-color-on-accent,#fff)}
		.ag-rec-lock__fine{font-size:.9rem;color:var(--ag-color-muted,#6e6a60)!important}
		.ag-rec-lock__fine strong{color:var(--ag-color-accent,#c9a24b)}
		</style>
		<?php
		return ob_get_clean();
	}

	/* ---------------- Grille accueil ---------------- */

	public static function get_recipes() {
		$cat = get_category_by_slug( self::CAT );
		$args = array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => self::count(),
		);
		if ( $cat ) $args['category__in'] = array( (int) $cat->term_id );
		return get_posts( $args );
	}

	public static function has_recipes() {
		return (bool) self::get_recipes();
	}

	public static function recipe_img( $id ) {
		$img = get_the_post_thumbnail_url( $id, 'large' );
		if ( ! $img ) $img = get_post_meta( $id, '_ag_recipe_img', true );
		return $img;
	}

	public static function grid() {
		$recipes = self::get_recipes();
		if ( ! $recipes ) return '';

		ob_start();
		?>
		<section class="ag-container ag-recettes-wrap" id="ag-recettes">
			<div class="ag-services-grid-header ag-anim">
				<h2 class="ag-services-grid-title">Nos <span>recettes</span></h2>
				<p class="ag-services-grid-lead"><?php echo esc_html( self::intro() ); ?></p>
			</div>
			<div class="ag-rec-grid">
				<?php foreach ( $recipes as $r ) :
					$img = self::recipe_img( $r->ID );
				?>
					<a class="ag-rec-card ag-anim" href="<?php echo esc_url( get_permalink( $r->ID ) ); ?>">
						<span class="ag-rec-card__img"<?php echo $img ? ' style="background-image:url(\'' . esc_url( $img ) . '\')"' : ''; ?>>
							<span class="ag-rec-card__badge">🔒 Secret du chef</span>
						</span>
						<span class="ag-rec-card__body">
							<span class="ag-rec-card__title"><?php echo esc_html( get_the_title( $r->ID ) ); ?></span>
							<span class="ag-rec-card__go">Voir la recette →</span>
						</span>
					</a>
				<?php endforeach; ?>
			</div>
		</section>
		<style>
		.ag-recettes-wrap{padding:60px 24px}
		.ag-rec-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:22px;max-width:1180px;margin:0 auto}
		@media(max-width:980px){.ag-rec-grid{grid-template-columns:repeat(2,1fr)}}
		@media(max-width:560px){.ag-rec-grid{grid-template-columns:1fr}}
		.ag-rec-card{display:flex;flex-direction:column;border-radius:16px;overflow:hidden;text-decoration:none;background:var(--ag-color-panel,#fff);border:1px solid var(--ag-color-border,#ececec);box-shadow:0 8px 24px rgba(0,0,0,.08);transition:transform .18s ease,box-shadow .18s ease}
		.ag-rec-card:hover{transform:translateY(-4px);box-shadow:0 16px 40px rgba(0,0,0,.16)}
		.ag-rec-card__img{position:relative;display:block;aspect-ratio:16/11;background:linear-gradient(135deg,color-mix(in srgb,var(--ag-color-accent,#c9a24b) 30%,#000),var(--ag-color-accent,#c9a24b));background-size:cover;background-position:center}
		.ag-rec-card__badge{position:absolute;top:12px;left:12px;background:rgba(0,0,0,.6);color:#fff;font-size:.74rem;font-weight:700;padding:5px 12px;border-radius:999px;backdrop-filter:blur(4px)}
		.ag-rec-card__body{padding:18px 18px 20px}
		.ag-rec-card__title{display:block;font-family:'Playfair Display',Georgia,serif;font-size:1.2rem;color:var(--ag-color-heading,#111);margin-bottom:8px;line-height:1.25}
		.ag-rec-card__go{color:var(--ag-color-accent,#c9a24b);font-weight:700;font-size:.92rem}
		</style>
		<?php
		return ob_get_clean();
	}

	/* ---------------- Recettes de démo (1re activation) ---------------- */

	public static function maybe_seed() {
		if ( get_option( 'ag_recipe_seed_v2' ) ) return;

		$cat_id = 0;
		$cat = get_category_by_slug( self::CAT );
		if ( $cat ) {
			$cat_id = (int) $cat->term_id;
		} else {
			$res = wp_insert_term( 'Recettes', 'category', array( 'slug' => self::CAT ) );
			if ( ! is_wp_error( $res ) ) $cat_id = (int) $res['term_id'];
		}

		foreach ( self::demo_recipes() as $demo ) {
			$existing = get_page_by_path( $demo['slug'], OBJECT, 'post' );
			if ( $existing ) {
				// Rafraîchit le contenu de démo (mise en page photo + étapes).
				wp_update_post( array( 'ID' => $existing->ID, 'post_content' => $demo['content'] ) );
				if ( ! empty( $demo['img'] ) ) update_post_meta( $existing->ID, '_ag_recipe_img', $demo['img'] );
				continue;
			}
			$pid = wp_insert_post( array(
				'post_type'     => 'post',
				'post_status'   => 'publish',
				'post_title'    => $demo['title'],
				'post_name'     => $demo['slug'],
				'post_content'  => $demo['content'],
				'post_category' => $cat_id ? array( $cat_id ) : array(),
			) );
			if ( $pid && ! is_wp_error( $pid ) && ! empty( $demo['img'] ) ) {
				update_post_meta( $pid, '_ag_recipe_img', $demo['img'] );
			}
		}
		update_option( 'ag_recipe_seed_v2', 1 );
	}

	private static function demo_recipes() {
		$mk = function( $intro, $ingredients, $steps, $secret ) {
			$c  = '<!-- wp:paragraph --><p>' . $intro . '</p><!-- /wp:paragraph -->' . "\n";
			$c .= '<!-- wp:heading --><h2>Ingrédients</h2><!-- /wp:heading -->' . "\n";
			$c .= '<!-- wp:list --><ul><li>' . implode( '</li><li>', $ingredients ) . '</li></ul><!-- /wp:list -->' . "\n";
			$c .= '<!-- wp:heading --><h2>Préparation</h2><!-- /wp:heading -->' . "\n";
			$c .= '<!-- wp:list {"ordered":true} --><ol><li>' . implode( '</li><li>', $steps ) . '</li></ol><!-- /wp:list -->' . "\n";
			$c .= '[secret]' . "\n" . $secret . "\n" . '[/secret]';
			return $c;
		};
		return array(
			array(
				'slug'  => 'pate-a-pizza-napolitaine',
				'title' => 'La vraie pâte à pizza napolitaine',
				'img'   => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=1200&q=80',
				'content' => $mk(
					"La base d'une bonne pizza, c'est la pâte : croustillante dehors, moelleuse dedans. Voici les ingrédients et les grandes étapes.",
					array( 'Farine type 00', 'Eau', 'Sel fin', 'Levure de boulanger' ),
					array( 'Mélangez la farine et l\'eau, laissez reposer 20 min (autolyse).', 'Ajoutez le sel puis la levure, pétrissez jusqu\'à une pâte lisse.', 'Façonnez des boules (pâtons) et laissez lever à couvert.', 'Étalez à la main, garnissez, puis enfournez très chaud.' ),
					"Le secret du chef :\n- La proportion exacte d'eau (hydratation) et la pincée de sucre\n- Une maturation longue de 48 h à 4 °C\n- Le geste pour étaler sans rouleau, et la température de cuisson reproductible chez vous.\n\nRecette complète détaillée, gramme par gramme."
				),
			),
			array(
				'slug'  => 'sauce-tomate-maison',
				'title' => 'Notre sauce tomate maison',
				'img'   => 'https://images.unsplash.com/photo-1606756790138-261d2b21cd75?w=1200&q=80',
				'content' => $mk(
					"Une sauce simple en apparence… mais tout est dans l'équilibre et la cuisson.",
					array( 'Tomates San Marzano', 'Huile d\'olive vierge', 'Ail', 'Basilic frais', 'Sel' ),
					array( 'Faites revenir l\'ail dans l\'huile d\'olive sans le brûler.', 'Ajoutez les tomates écrasées et le sel.', 'Laissez mijoter à feu doux.', 'Ajoutez le basilic en fin de cuisson.' ),
					"Les ingrédients cachés : la touche de sucre, l'épice secrète et le temps de mijotage exact pour une sauce qui ne rend pas d'eau sur la pâte."
				),
			),
			array(
				'slug'  => 'tiramisu-du-chef',
				'title' => 'Le tiramisu du chef',
				'img'   => 'https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?w=1200&q=80',
				'content' => $mk(
					"Crémeux, léger, jamais trop sucré. Voici la base de notre tiramisu maison.",
					array( 'Mascarpone', 'Œufs frais', 'Sucre', 'Café fort', 'Cacao amer', 'Biscuits' ),
					array( 'Séparez les blancs des jaunes ; blanchissez les jaunes avec le sucre.', 'Incorporez le mascarpone, puis les blancs montés en neige.', 'Trempez les biscuits dans le café, dressez en couches.', 'Réfrigérez plusieurs heures, saupoudrez de cacao avant de servir.' ),
					"Le secret : l'alcool utilisé, le ratio sucre/mascarpone exact et l'astuce pour une mousse qui tient sans gélatine."
				),
			),
		);
	}
}
AG_Restaurant_Recettes::init();
