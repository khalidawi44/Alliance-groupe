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
		.ag-recipe-meta{display:flex;flex-wrap:wrap;gap:10px;margin:18px 0 6px}
		.ag-recipe-meta span{background:color-mix(in srgb,var(--ag-color-accent,#c9a24b) 12%,transparent);border:1px solid color-mix(in srgb,var(--ag-color-accent,#c9a24b) 35%,transparent);border-radius:999px;padding:7px 16px;font-weight:700;font-size:.92rem}
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
		if ( get_option( 'ag_recipe_seed_v3' ) ) return;

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
		update_option( 'ag_recipe_seed_v3', 1 );
	}

	private static function demo_recipes() {
		// $intro = array de paragraphes ; $meta = array(temps, portions, difficulté)
		$mk = function( $intro, $meta, $ingredients, $steps, $tips, $secret ) {
			$c = '';
			foreach ( (array) $intro as $par ) {
				$c .= '<!-- wp:paragraph --><p>' . $par . '</p><!-- /wp:paragraph -->' . "\n";
			}
			$c .= '<!-- wp:html --><div class="ag-recipe-meta"><span>⏱️ ' . $meta[0] . '</span><span>👥 ' . $meta[1] . '</span><span>🔥 ' . $meta[2] . '</span></div><!-- /wp:html -->' . "\n";
			$c .= '<!-- wp:heading --><h2>Ingrédients</h2><!-- /wp:heading -->' . "\n";
			$c .= '<!-- wp:list --><ul><li>' . implode( '</li><li>', $ingredients ) . '</li></ul><!-- /wp:list -->' . "\n";
			$c .= '<!-- wp:heading --><h2>Préparation</h2><!-- /wp:heading -->' . "\n";
			$c .= '<!-- wp:list {"ordered":true} --><ol><li>' . implode( '</li><li>', $steps ) . '</li></ol><!-- /wp:list -->' . "\n";
			$c .= '<!-- wp:heading --><h2>Les astuces du chef</h2><!-- /wp:heading -->' . "\n";
			$c .= '<!-- wp:list --><ul><li>' . implode( '</li><li>', $tips ) . '</li></ul><!-- /wp:list -->' . "\n";
			$c .= '[secret]' . "\n" . $secret . "\n" . '[/secret]';
			return $c;
		};
		return array(
			array(
				'slug'  => 'pate-a-pizza-napolitaine',
				'title' => 'La vraie pâte à pizza napolitaine',
				'img'   => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=1200&q=80',
				'content' => $mk(
					array(
						"La pizza napolitaine, ce n'est pas qu'une garniture : c'est avant tout une <strong>pâte vivante</strong>, légère, à la fois croustillante sur les bords (le fameux <em>cornicione</em>) et fondante au centre. Chez nous, elle lève lentement, comme à Naples.",
						"Dans cette recette, on vous donne <strong>la base que tout le monde peut réussir</strong> à la maison, même sans four à bois. Les proportions exactes et les tours de main du chef, eux, sont réservés (voir plus bas).",
					),
					array( '30 min + repos', '4 pizzas', 'Facile' ),
					array(
						'500 g de farine type 00 (à défaut, une T45 de qualité)',
						'325 ml d\'eau à température ambiante',
						'10 g de sel fin',
						'2 g de levure de boulanger fraîche',
						'Un filet d\'huile d\'olive vierge extra',
					),
					array(
						'Dans un grand saladier, mélangez la farine et l\'eau jusqu\'à absorption, puis laissez reposer 20 minutes (autolyse) : la pâte se forme presque toute seule.',
						'Ajoutez le sel puis la levure émiettée. Pétrissez 8 à 10 minutes, jusqu\'à obtenir une pâte lisse et élastique qui se décolle des parois.',
						'Couvrez et laissez pointer 1 heure à température ambiante, puis divisez en 4 pâtons réguliers.',
						'Boulez chaque pâton, déposez-les dans une boîte farinée et laissez maturer (idéalement au frais, voir astuces).',
						'Étalez à la main du centre vers les bords pour garder l\'air dans le cornicione, garnissez, puis enfournez à la température la plus haute de votre four.',
					),
					array(
						'Une pâte qui colle un peu = une pizza moelleuse : ne sur-farinez pas.',
						'Préchauffez votre plaque (ou une pierre) à fond : c\'est ce qui remplace le four à bois.',
						'Sortez les pâtons 1 h avant de les étaler pour qu\'ils soient à température.',
					),
					"🔓 Le secret du chef (réservé) :\n- L'hydratation EXACTE et la pincée de sucre qui change tout\n- La maturation longue de 48 h à 4 °C (le vrai goût napolitain)\n- Le geste précis pour étaler sans rouleau et la cuisson reproductible chez vous, minute par minute."
				),
			),
			array(
				'slug'  => 'sauce-tomate-maison',
				'title' => 'Notre sauce tomate maison (la vraie)',
				'img'   => 'https://images.unsplash.com/photo-1606756790138-261d2b21cd75?w=1200&q=80',
				'content' => $mk(
					array(
						"Une bonne pizza ou de bonnes pâtes commencent par une <strong>sauce tomate honnête</strong> : peu d'ingrédients, mais les bons, et surtout la bonne cuisson. Pas de concentré, pas de sucre à outrance.",
						"Voici notre base. Le dosage précis qui fait notre signature est réservé (plus bas).",
					),
					array( '40 min', '6 personnes', 'Très facile' ),
					array(
						'800 g de tomates San Marzano (pelées, en boîte de qualité)',
						'3 cuillères à soupe d\'huile d\'olive vierge extra',
						'2 gousses d\'ail',
						'Quelques feuilles de basilic frais',
						'Sel, poivre',
					),
					array(
						'Faites chauffer l\'huile d\'olive à feu doux et faites-y blondir l\'ail écrasé sans le brûler (sinon il devient amer).',
						'Ajoutez les tomates écrasées à la main, salez légèrement.',
						'Laissez mijoter à feu doux 25 à 30 minutes, en remuant de temps en temps, jusqu\'à ce que la sauce nappe la cuillère.',
						'Ajoutez le basilic ciselé en fin de cuisson, rectifiez l\'assaisonnement.',
					),
					array(
						'San Marzano = moins d\'acidité, plus de douceur naturelle.',
						'Une sauce trop liquide détrempe la pâte : laissez-la bien réduire.',
						'Le basilic se met à la fin pour garder son parfum.',
					),
					"🔓 Réservé : la touche de sucre dosée au gramme, l'épice secrète de la maison et le temps de mijotage exact pour une sauce qui ne rend jamais d'eau sur la pâte."
				),
			),
			array(
				'slug'  => 'tiramisu-du-chef',
				'title' => 'Le tiramisu du chef',
				'img'   => 'https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?w=1200&q=80',
				'content' => $mk(
					array(
						"Le tiramisu, c'est un dessert de <strong>partage</strong> : crémeux, aérien, à peine sucré, avec ce parfum de café qui réveille. Le nôtre tient parfaitement à la découpe… sans gélatine.",
						"On vous livre la base ci-dessous. Le secret de la tenue et de l'équilibre est réservé (plus bas).",
					),
					array( '30 min + 4 h de repos', '6 personnes', 'Facile' ),
					array(
						'250 g de mascarpone',
						'3 œufs frais (jaunes et blancs séparés)',
						'80 g de sucre',
						'1 tasse de café fort, refroidi',
						'Cacao amer non sucré',
						'Biscuits à la cuillère (boudoirs)',
					),
					array(
						'Blanchissez les jaunes d\'œufs avec le sucre jusqu\'à ce que le mélange double de volume.',
						'Incorporez délicatement le mascarpone pour obtenir une crème lisse.',
						'Montez les blancs en neige ferme et incorporez-les en deux fois, sans casser la mousse.',
						'Trempez rapidement les biscuits dans le café, dressez une couche de biscuits, une couche de crème, et répétez.',
						'Réservez au frais au moins 4 heures. Saupoudrez de cacao juste avant de servir.',
					),
					array(
						'Ne trempez pas trop les biscuits : un aller-retour suffit.',
						'Le repos au frais est obligatoire : c\'est lui qui donne la tenue.',
						'Cacao au dernier moment, sinon il s\'humidifie.',
					),
					"🔓 Réservé : l'alcool utilisé et son dosage, le ratio sucre/mascarpone exact et l'astuce pro pour une mousse qui se tient parfaitement, sans gélatine."
				),
			),
		);
	}
}
AG_Restaurant_Recettes::init();
