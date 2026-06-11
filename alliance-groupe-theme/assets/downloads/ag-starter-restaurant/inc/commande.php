<?php
/**
 * Commande en ligne — système type « Uber Eats » pour le restaurant.
 *
 * - S'appuie sur la carte (mêmes plats / prix que [ag_restaurant_carte]).
 * - Chaque plat avec un prix reçoit un bouton « + Ajouter ».
 * - Panier flottant (compteur + total) -> tiroir latéral -> validation.
 * - Modes : Livraison (frais + minimum) ou À emporter.
 * - À la validation : crée une commande (lead partagé ag_devis_lead),
 *   prévient le restaurant par email + push (ag_push) et affiche un
 *   numéro de commande. Option « Commander sur WhatsApp » en plus.
 *
 * Tout est autonome et lisible quel que soit le fond du thème.
 *
 * @package AG_Starter_Restaurant
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AG_Restaurant_Commande {

	public static function init() {
		add_shortcode( 'ag_restaurant_commande', array( __CLASS__, 'cart_ui' ) );
		add_action( 'customize_register', array( __CLASS__, 'customize' ) );
		add_action( 'admin_post_nopriv_ag_restaurant_order', array( __CLASS__, 'handle_submit' ) );
		add_action( 'admin_post_ag_restaurant_order', array( __CLASS__, 'handle_submit' ) );
	}

	/* ---------- Réglages ---------- */

	public static function is_on() {
		return (bool) get_theme_mod( 'ag_order_on', true );
	}
	public static function delivery_on() {
		return (bool) get_theme_mod( 'ag_order_delivery', true );
	}
	public static function takeaway_on() {
		return (bool) get_theme_mod( 'ag_order_takeaway', true );
	}
	public static function fee() {
		return (float) get_theme_mod( 'ag_order_delivery_fee', 2.5 );
	}
	public static function min_order() {
		return (float) get_theme_mod( 'ag_order_min', 15 );
	}
	public static function whatsapp() {
		return preg_replace( '/[^0-9]/', '', (string) get_theme_mod( 'ag_order_whatsapp', '' ) );
	}
	public static function notify_email() {
		$e = sanitize_email( get_theme_mod( 'ag_order_email', '' ) );
		return $e ? $e : get_option( 'admin_email' );
	}

	public static function customize( $wp ) {
		$wp->add_section( 'ag_restaurant_order', array(
			'title'       => '🛒 ' . __( 'Commande en ligne', 'ag-starter-restaurant' ),
			'priority'    => 28,
			'description' => __( 'Active la commande type « Uber Eats » sur la page Notre carte. Les plats et prix viennent de la section « Notre carte (menu) ».', 'ag-starter-restaurant' ),
		) );

		$add = function( $id, $args ) use ( $wp ) {
			$wp->add_setting( $id, array(
				'default'           => $args['default'],
				'sanitize_callback' => $args['sanitize'],
			) );
			$wp->add_control( $id, array(
				'label'   => $args['label'],
				'section' => 'ag_restaurant_order',
				'type'    => $args['type'],
			) );
		};

		$add( 'ag_order_on', array( 'default' => true, 'type' => 'checkbox', 'label' => __( 'Activer la commande en ligne', 'ag-starter-restaurant' ), 'sanitize' => 'wp_validate_boolean' ) );
		$add( 'ag_order_delivery', array( 'default' => true, 'type' => 'checkbox', 'label' => __( 'Proposer la livraison', 'ag-starter-restaurant' ), 'sanitize' => 'wp_validate_boolean' ) );
		$add( 'ag_order_takeaway', array( 'default' => true, 'type' => 'checkbox', 'label' => __( 'Proposer « À emporter »', 'ag-starter-restaurant' ), 'sanitize' => 'wp_validate_boolean' ) );
		$add( 'ag_order_delivery_fee', array( 'default' => 2.5, 'type' => 'text', 'label' => __( 'Frais de livraison (€)', 'ag-starter-restaurant' ), 'sanitize' => array( __CLASS__, 'sanitize_amount' ) ) );
		$add( 'ag_order_min', array( 'default' => 15, 'type' => 'text', 'label' => __( 'Minimum de commande livraison (€)', 'ag-starter-restaurant' ), 'sanitize' => array( __CLASS__, 'sanitize_amount' ) ) );
		$add( 'ag_order_phone', array( 'default' => '', 'type' => 'text', 'label' => __( 'Téléphone du restaurant (affiché)', 'ag-starter-restaurant' ), 'sanitize' => 'sanitize_text_field' ) );
		$add( 'ag_order_whatsapp', array( 'default' => '', 'type' => 'text', 'label' => __( 'WhatsApp (format international, ex : 33612345678)', 'ag-starter-restaurant' ), 'sanitize' => 'sanitize_text_field' ) );
		$add( 'ag_order_email', array( 'default' => '', 'type' => 'text', 'label' => __( 'Email de réception des commandes (vide = admin)', 'ag-starter-restaurant' ), 'sanitize' => 'sanitize_email' ) );
		$add( 'ag_order_pay_note', array( 'default' => 'Paiement à la livraison ou sur place (espèces / carte).', 'type' => 'text', 'label' => __( 'Note de paiement', 'ag-starter-restaurant' ), 'sanitize' => 'sanitize_text_field' ) );
		$add( 'ag_order_hours', array( 'default' => "Lun – Ven : 12h–14h30 · 19h–22h30\nSam – Dim : 19h – 23h", 'type' => 'textarea', 'label' => __( 'Horaires d\'ouverture (affichés à l\'arrivée)', 'ag-starter-restaurant' ), 'sanitize' => 'sanitize_textarea_field' ) );
	}

	public static function sanitize_amount( $v ) {
		$v = str_replace( ',', '.', (string) $v );
		return (string) max( 0, (float) preg_replace( '/[^0-9.]/', '', $v ) );
	}

	/* ---------- Données : plats à partir de la carte ---------- */

	public static function item_id( $section, $name ) {
		return sanitize_title( $section . '-' . $name );
	}

	/** Carte parsée -> map id => array(name, price float, section). */
	public static function price_map() {
		$map = array();
		if ( ! class_exists( 'AG_Restaurant_Carte' ) ) return $map;
		$sections = AG_Restaurant_Carte::get_sections();
		foreach ( $sections as $sec ) {
			foreach ( $sec['items'] as $it ) {
				if ( '' === $it['name'] ) continue;
				$price = self::num_price( $it['price'] );
				if ( null === $price ) continue; // plat sans prix = pas commandable
				$id = self::item_id( $sec['title'], $it['name'] );
				$map[ $id ] = array( 'name' => $it['name'], 'price' => $price, 'section' => $sec['title'] );
			}
		}
		return $map;
	}

	public static function num_price( $p ) {
		$p = str_replace( ',', '.', trim( (string) $p ) );
		if ( preg_match( '/^\d+(\.\d+)?$/', $p ) ) return (float) $p;
		return null;
	}

	/** Bouton « + Ajouter » pour un plat (appelé depuis la carte). */
	public static function add_button( $id, $name, $price ) {
		return sprintf(
			'<button type="button" class="ag-ord-add" data-id="%s" data-name="%s" data-price="%s">+ Ajouter</button>',
			esc_attr( $id ),
			esc_attr( $name ),
			esc_attr( number_format( (float) $price, 2, '.', '' ) )
		);
	}

	/* ---------- Interface panier (flottant + tiroir + validation) ---------- */

	public static function cart_ui() {
		if ( ! self::is_on() ) return '';

		$delivery = self::delivery_on();
		$takeaway = self::takeaway_on();
		if ( ! $delivery && ! $takeaway ) $takeaway = true; // au moins un mode

		$fee      = self::fee();
		$min      = self::min_order();
		$wa        = self::whatsapp();
		$phone    = trim( (string) get_theme_mod( 'ag_order_phone', '' ) );
		$pay_note = trim( (string) get_theme_mod( 'ag_order_pay_note', 'Paiement à la livraison ou sur place (espèces / carte).' ) );
		$brand    = get_theme_mod( 'ag_hero_brand', get_bloginfo( 'name' ) );
		$ordered  = isset( $_GET['ordered'] ) ? sanitize_text_field( wp_unslash( $_GET['ordered'] ) ) : '';
		$hours    = trim( (string) get_theme_mod( 'ag_order_hours', '' ) );
		$resa_pg  = get_page_by_path( 'reservation' );
		$resa_url = $resa_pg ? get_permalink( $resa_pg->ID ) : home_url( '/reservation/' );
		$fee_lbl  = $fee > 0 ? number_format( $fee, 2, ',', ' ' ) . ' € de frais' : 'Frais offerts';

		ob_start();
		?>
		<div class="ag-ord" data-fee="<?php echo esc_attr( number_format( $fee, 2, '.', '' ) ); ?>" data-min="<?php echo esc_attr( number_format( $min, 2, '.', '' ) ); ?>" data-delivery="<?php echo $delivery ? '1' : '0'; ?>" data-takeaway="<?php echo $takeaway ? '1' : '0'; ?>" data-wa="<?php echo esc_attr( $wa ); ?>" data-brand="<?php echo esc_attr( $brand ); ?>" data-resa="<?php echo esc_url( $resa_url ); ?>">

			<!-- Écran d'accueil : choix du parcours -->
			<div class="ag-ord-intro" id="ag-ord-intro" hidden>
				<div class="ag-ord-intro__card">
					<button type="button" class="ag-ord-intro__x" id="ag-ord-intro-x" aria-label="Fermer">✕</button>
					<p class="ag-ord-intro__hi">Bienvenue<?php echo $brand ? ' chez ' . esc_html( $brand ) : ''; ?></p>
					<h3 class="ag-ord-intro__h">Comment souhaitez-vous en profiter ?</h3>
					<div class="ag-ord-intro__opts">
						<button type="button" class="ag-ord-opt" data-intent="reserver"><span>🍽️</span><b>Réserver une table</b><small>Manger sur place</small></button>
						<?php if ( $delivery ) : ?>
							<button type="button" class="ag-ord-opt" data-intent="livraison"><span>🛵</span><b>Livraison</b><small>Livré chez vous · <?php echo esc_html( $fee_lbl ); ?></small></button>
						<?php endif; ?>
						<?php if ( $takeaway ) : ?>
							<button type="button" class="ag-ord-opt" data-intent="emporter"><span>🥡</span><b>À emporter</b><small>Je viens le chercher</small></button>
						<?php endif; ?>
						<button type="button" class="ag-ord-opt" data-intent="consulter"><span>📖</span><b>Voir la carte</b><small>Simplement consulter</small></button>
					</div>
					<?php if ( $hours ) : ?>
						<div class="ag-ord-intro__hours">🕒 <?php echo nl2br( esc_html( $hours ) ); ?></div>
					<?php endif; ?>
				</div>
			</div>

			<!-- Barre de mode (après le choix) -->
			<div class="ag-ord-modebar" id="ag-ord-modebar" hidden>
				<span class="ag-ord-modebar__txt" id="ag-ord-modetxt"></span>
				<button type="button" class="ag-ord-modebar__btn" id="ag-ord-modechange">Changer</button>
			</div>

			<?php if ( '1' === $ordered ) : ?>
				<div class="ag-ord-done" id="ag-ord-done">
					<div class="ag-ord-done__card">
						<div style="font-size:2.4rem">✅</div>
						<h3>Commande envoyée !</h3>
						<p>Merci. Le restaurant a reçu votre commande<?php echo $phone ? ' — un appel de confirmation peut suivre au ' . esc_html( $phone ) : ''; ?>.</p>
						<a href="<?php echo esc_url( remove_query_arg( array( 'ordered', 'ref' ) ) ); ?>" class="ag-ord-done__btn">Continuer</a>
					</div>
				</div>
			<?php endif; ?>

			<!-- Panier flottant -->
			<button type="button" class="ag-ord-fab" id="ag-ord-fab" aria-label="Voir mon panier" hidden>
				<span class="ag-ord-fab__ic">🛒</span>
				<span class="ag-ord-fab__count" id="ag-ord-count">0</span>
				<span class="ag-ord-fab__total" id="ag-ord-fabtotal">0,00 €</span>
			</button>

			<!-- Tiroir panier -->
			<div class="ag-ord-overlay" id="ag-ord-overlay" hidden></div>
			<aside class="ag-ord-drawer" id="ag-ord-drawer" aria-hidden="true" aria-label="Votre commande">
				<div class="ag-ord-drawer__head">
					<strong>Votre commande</strong>
					<button type="button" class="ag-ord-x" id="ag-ord-close" aria-label="Fermer">✕</button>
				</div>

				<div class="ag-ord-empty" id="ag-ord-empty">
					<div style="font-size:2.2rem;opacity:.6">🍽️</div>
					<p>Votre panier est vide.<br>Ajoutez des plats depuis la carte.</p>
				</div>

				<div class="ag-ord-body" id="ag-ord-body" hidden>
					<ul class="ag-ord-lines" id="ag-ord-lines"></ul>

					<form class="ag-ord-form" id="ag-ord-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="ag_restaurant_order">
						<?php wp_nonce_field( 'ag_restaurant_order' ); ?>
						<input type="hidden" name="ag_cart" id="ag-ord-cartjson" value="">

						<div class="ag-ord-modes">
							<?php if ( $delivery ) : ?>
								<label class="ag-ord-mode"><input type="radio" name="ag_mode" value="livraison" checked> 🛵 Livraison</label>
							<?php endif; ?>
							<?php if ( $takeaway ) : ?>
								<label class="ag-ord-mode"><input type="radio" name="ag_mode" value="emporter" <?php echo $delivery ? '' : 'checked'; ?>> 🥡 À emporter</label>
							<?php endif; ?>
						</div>

						<div class="ag-ord-totals">
							<div class="ag-ord-trow"><span>Sous-total</span><span id="ag-ord-sub">0,00 €</span></div>
							<div class="ag-ord-trow" id="ag-ord-feerow"><span>Livraison</span><span id="ag-ord-fee">—</span></div>
							<div class="ag-ord-trow ag-ord-trow--tot"><span>Total</span><span id="ag-ord-total">0,00 €</span></div>
							<p class="ag-ord-min" id="ag-ord-minmsg" hidden></p>
						</div>

						<div class="ag-ord-fields">
							<label>Prénom / Nom *<input type="text" name="cust_name" required placeholder="Ex : Karim B."></label>
							<label>Téléphone *<input type="tel" name="cust_phone" required placeholder="06 12 34 56 78"></label>
							<label>Email<input type="email" name="cust_email" placeholder="vous@email.fr"></label>

							<div class="ag-ord-addr" id="ag-ord-addr">
								<label>Adresse de livraison *<input type="text" name="cust_addr" placeholder="N° et rue"></label>
								<div class="ag-ord-addr2">
									<label>Code postal<input type="text" name="cust_zip" placeholder="44000"></label>
									<label>Ville<input type="text" name="cust_city" placeholder="Nantes"></label>
								</div>
							</div>

							<label>Créneau souhaité
								<select name="cust_slot">
									<option value="Dès que possible">Dès que possible</option>
									<option value="Dans 30 min">Dans 30 min</option>
									<option value="Dans 1 h">Dans 1 h</option>
									<option value="Plus tard (préciser)">Plus tard (je précise en note)</option>
								</select>
							</label>
							<label>Note (allergies, instructions…)<textarea name="cust_note" rows="2"></textarea></label>
						</div>

						<p class="ag-ord-pay"><?php echo esc_html( $pay_note ); ?></p>
						<button type="submit" class="ag-ord-validate" id="ag-ord-validate">Valider ma commande</button>
						<?php if ( $wa ) : ?>
							<a href="#" class="ag-ord-wa" id="ag-ord-wa" target="_blank" rel="noopener">💬 Commander sur WhatsApp</a>
						<?php endif; ?>
					</form>
				</div>
			</aside>

			<?php self::styles(); ?>
			<?php self::script(); ?>
		</div>
		<?php
		return ob_get_clean();
	}

	private static function styles() {
		?>
		<style>
		/* IMPORTANT : display:flex écrase l'attribut [hidden] -> on le rétablit. */
		.ag-ord-intro[hidden],.ag-ord-modebar[hidden],.ag-ord-fab[hidden]{display:none !important}
		/* Écran d'accueil (choix du parcours) */
		.ag-ord-intro{position:fixed;inset:0;z-index:99994;display:flex;align-items:center;justify-content:center;background:rgba(5,4,2,.82);backdrop-filter:blur(5px);padding:18px}
		.ag-ord-intro__card{position:relative;background:linear-gradient(180deg,#1c1813,#14110b);border:1px solid rgba(201,162,75,.4);border-radius:18px;padding:30px 26px 24px;max-width:440px;width:100%;text-align:center;color:#ece4d2;box-shadow:0 30px 80px rgba(0,0,0,.6)}
		.ag-ord-intro__x{position:absolute;top:12px;right:14px;background:none;border:0;color:#9a8e72;font-size:1.2rem;cursor:pointer;line-height:1}
		.ag-ord-intro__hi{margin:0;color:#c9a24b;letter-spacing:2px;text-transform:uppercase;font-size:.74rem;font-weight:700}
		.ag-ord-intro__h{margin:6px 0 20px;font-family:'Playfair Display',Georgia,serif;font-size:1.5rem;color:#f3ead4}
		.ag-ord-intro__opts{display:grid;grid-template-columns:1fr 1fr;gap:12px}
		.ag-ord-opt{display:flex;flex-direction:column;align-items:center;gap:3px;padding:16px 10px;background:rgba(255,255,255,.03);border:1px solid rgba(201,162,75,.35);border-radius:14px;cursor:pointer;color:#ece4d2;transition:.15s}
		.ag-ord-opt:hover{background:#c9a24b;border-color:#c9a24b;color:#13110c}
		.ag-ord-opt span{font-size:1.7rem;line-height:1}
		.ag-ord-opt b{font-size:.98rem;margin-top:4px}
		.ag-ord-opt small{font-size:.76rem;opacity:.8;line-height:1.25}
		.ag-ord-intro__hours{margin:18px 0 0;color:#b8ad8f;font-size:.85rem;line-height:1.6;border-top:1px solid rgba(255,255,255,.08);padding-top:14px}
		@media(max-width:420px){.ag-ord-intro__opts{grid-template-columns:1fr}}
		/* Barre de mode (sticky, au-dessus de la carte) */
		.ag-ord-modebar{position:sticky;top:0;z-index:60;display:flex;align-items:center;justify-content:space-between;gap:12px;background:linear-gradient(180deg,#241e15,#1a160f);border:1px solid rgba(201,162,75,.35);border-radius:12px;padding:10px 16px;margin:0 auto 14px;max-width:780px;color:#f3ead4;font-size:.95rem;font-weight:700;box-shadow:0 8px 24px rgba(0,0,0,.35)}
		.ag-ord-modebar__txt{display:flex;align-items:center;gap:6px}
		.ag-ord-modebar__btn{background:#c9a24b;border:0;color:#13110c;border-radius:999px;padding:6px 16px;font-size:.84rem;font-weight:800;cursor:pointer;white-space:nowrap}
		.ag-ord-modebar__btn:hover{filter:brightness(1.08)}
		body.ag-has-modebar .ag-carte-nav{top:54px}
		/* Mode « consultation » : on masque les boutons Ajouter */
		body.ag-ord-browse .ag-ord-add{display:none}
		/* Panier flottant (compact) */
		.ag-ord-fab{position:fixed;right:16px;bottom:16px;z-index:99980;display:flex;align-items:center;gap:8px;background:#c9a24b;color:#13110c;border:0;border-radius:999px;padding:9px 15px;font-weight:800;font-size:.9rem;cursor:pointer;box-shadow:0 8px 24px rgba(0,0,0,.4);}
		.ag-ord-fab:hover{filter:brightness(1.06)}
		.ag-ord-fab.is-bump{animation:agFabBump .35s ease}
		@keyframes agFabBump{0%,100%{transform:scale(1)}40%{transform:scale(1.12)}}
		.ag-ord-fab__ic{font-size:1.05rem}
		.ag-ord-fab__count{background:#13110c;color:#f3d889;border-radius:999px;min-width:19px;height:19px;display:inline-flex;align-items:center;justify-content:center;font-size:.76rem;padding:0 5px}
		.ag-ord-fab__total{font-weight:800}
		/* Overlay + tiroir (compact) */
		.ag-ord-overlay{position:fixed;inset:0;background:rgba(5,4,2,.55);backdrop-filter:blur(2px);z-index:99985}
		.ag-ord-drawer{position:fixed;top:0;right:0;height:100%;width:min(340px,88vw);max-width:88vw;background:#17130d;color:#ece4d2;z-index:99990;transform:translateX(102%);transition:transform .28s cubic-bezier(.22,.9,.3,1);display:flex;flex-direction:column;box-shadow:-16px 0 46px rgba(0,0,0,.55);border-left:1px solid rgba(201,162,75,.3)}
		.ag-ord-drawer.is-open{transform:translateX(0)}
		.ag-ord-drawer__head{display:flex;align-items:center;justify-content:space-between;padding:14px 16px;border-bottom:1px solid rgba(201,162,75,.22);font-size:1.02rem;color:#f3ead4}
		.ag-ord-x{background:none;border:0;color:#cbb98e;font-size:1.3rem;cursor:pointer;line-height:1}
		.ag-ord-empty{padding:48px 22px;text-align:center;color:#a99e80;font-size:.92rem}
		.ag-ord-body{flex:1;overflow-y:auto;padding:12px 14px 20px}
		.ag-ord-lines{list-style:none;margin:0 0 16px;padding:0}
		.ag-ord-line{display:flex;align-items:center;gap:10px;padding:12px 0;border-bottom:1px solid rgba(255,255,255,.07)}
		.ag-ord-line__name{flex:1;font-weight:600;color:#f1e9d4;font-size:.96rem}
		.ag-ord-line__price{color:#e9d49a;font-weight:700;white-space:nowrap;font-size:.95rem}
		.ag-ord-qty{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.05);border:1px solid rgba(201,162,75,.35);border-radius:999px;padding:3px 6px}
		.ag-ord-qty button{width:26px;height:26px;border-radius:50%;border:0;background:#c9a24b;color:#13110c;font-weight:900;font-size:1.05rem;cursor:pointer;line-height:1}
		.ag-ord-qty span{min-width:18px;text-align:center;font-weight:700;color:#f3ead4}
		.ag-ord-modes{display:flex;gap:10px;margin:4px 0 14px}
		.ag-ord-mode{flex:1;display:flex;align-items:center;justify-content:center;gap:6px;padding:11px;border:1px solid rgba(201,162,75,.4);border-radius:10px;cursor:pointer;font-weight:600;color:#e7dcbf;font-size:.92rem}
		.ag-ord-mode input{accent-color:#c9a24b}
		.ag-ord-totals{background:rgba(255,255,255,.03);border:1px solid rgba(201,162,75,.22);border-radius:12px;padding:12px 14px;margin-bottom:14px}
		.ag-ord-trow{display:flex;justify-content:space-between;padding:4px 0;color:#cdc3a6;font-size:.95rem}
		.ag-ord-trow--tot{border-top:1px solid rgba(255,255,255,.1);margin-top:6px;padding-top:8px;color:#f3ead4;font-weight:800;font-size:1.1rem}
		.ag-ord-min{color:#ffb4a0;font-size:.85rem;margin:8px 0 0}
		.ag-ord-fields label{display:block;color:#d8cdb0;font-size:.85rem;font-weight:600;margin-bottom:10px}
		.ag-ord-fields input,.ag-ord-fields select,.ag-ord-fields textarea{width:100%;padding:10px;margin-top:4px;border:1px solid rgba(201,162,75,.45);border-radius:8px;background:#f5efe1;color:#2a2017;font-size:.95rem;box-sizing:border-box}
		.ag-ord-fields input::placeholder,.ag-ord-fields textarea::placeholder{color:#9a8e72}
		.ag-ord-addr2{display:grid;grid-template-columns:1fr 1fr;gap:10px}
		.ag-ord-pay{color:#a99e80;font-size:.82rem;text-align:center;margin:8px 0 12px}
		.ag-ord-validate{width:100%;background:#c9a24b;color:#13110c;border:0;font-weight:800;font-size:1.05rem;padding:15px;border-radius:10px;cursor:pointer}
		.ag-ord-validate:hover{filter:brightness(1.07)}
		.ag-ord-validate:disabled{opacity:.5;cursor:not-allowed}
		.ag-ord-wa{display:block;text-align:center;margin-top:10px;color:#25D366;font-weight:700;text-decoration:none;padding:11px;border:1px solid rgba(37,211,102,.5);border-radius:10px}
		/* Bouton Ajouter dans la carte */
		.ag-ord-add{margin-top:8px;background:transparent;border:1px solid #c9a24b;color:#f3d889;font-weight:700;font-size:.85rem;padding:6px 16px;border-radius:999px;cursor:pointer;transition:.15s}
		.ag-ord-add:hover{background:#c9a24b;color:#13110c}
		.ag-ord-add.is-added{background:#c9a24b;color:#13110c}
		/* Confirmation */
		.ag-ord-done{position:fixed;inset:0;z-index:99995;display:flex;align-items:center;justify-content:center;background:rgba(5,4,2,.78);backdrop-filter:blur(4px);padding:20px}
		.ag-ord-done__card{background:#17130d;border:1px solid rgba(201,162,75,.4);border-radius:18px;padding:34px 28px;max-width:420px;text-align:center;color:#ece4d2;box-shadow:0 30px 80px rgba(0,0,0,.6)}
		.ag-ord-done__card h3{color:#f3ead4;font-family:'Playfair Display',Georgia,serif;font-size:1.6rem;margin:10px 0 8px}
		.ag-ord-done__card p{color:#b8ad8f;margin:0 0 18px;line-height:1.5}
		.ag-ord-done__btn{display:inline-block;background:#c9a24b;color:#13110c;font-weight:800;text-decoration:none;padding:12px 28px;border-radius:999px}
		@media(max-width:560px){.ag-ord-fab{left:14px;right:14px;justify-content:center}.ag-ord-addr2{grid-template-columns:1fr}}
		</style>
		<?php
	}

	private static function script() {
		?>
		<script>
		(function(){
			var root=document.querySelector('.ag-ord'); if(!root) return;
			var FEE=parseFloat(root.getAttribute('data-fee'))||0;
			var MIN=parseFloat(root.getAttribute('data-min'))||0;
			var HAS_DELIVERY=root.getAttribute('data-delivery')==='1';
			var WA=root.getAttribute('data-wa')||'';
			var BRAND=root.getAttribute('data-brand')||'';
			var RESA=root.getAttribute('data-resa')||'';
			var KEY='ag_resto_cart', IKEY='ag_resto_intent';
			var cart={};
			try{ cart=JSON.parse(localStorage.getItem(KEY))||{}; }catch(e){ cart={}; }

			var intro=document.getElementById('ag-ord-intro'),
				introX=document.getElementById('ag-ord-intro-x'),
				modebar=document.getElementById('ag-ord-modebar'),
				modetxt=document.getElementById('ag-ord-modetxt'),
				modechange=document.getElementById('ag-ord-modechange'),
				fab=document.getElementById('ag-ord-fab'),
				count=document.getElementById('ag-ord-count'),
				fabtotal=document.getElementById('ag-ord-fabtotal'),
				drawer=document.getElementById('ag-ord-drawer'),
				overlay=document.getElementById('ag-ord-overlay'),
				linesEl=document.getElementById('ag-ord-lines'),
				emptyEl=document.getElementById('ag-ord-empty'),
				bodyEl=document.getElementById('ag-ord-body'),
				subEl=document.getElementById('ag-ord-sub'),
				feeEl=document.getElementById('ag-ord-fee'),
				feeRow=document.getElementById('ag-ord-feerow'),
				totalEl=document.getElementById('ag-ord-total'),
				minMsg=document.getElementById('ag-ord-minmsg'),
				addrEl=document.getElementById('ag-ord-addr'),
				form=document.getElementById('ag-ord-form'),
				cartJson=document.getElementById('ag-ord-cartjson'),
				validate=document.getElementById('ag-ord-validate'),
				waBtn=document.getElementById('ag-ord-wa');

			function eur(n){ return n.toFixed(2).replace('.',',')+' €'; }
			function nItems(){ var n=0; for(var k in cart) n+=cart[k].q; return n; }
			function subtotal(){ var s=0; for(var k in cart) s+=cart[k].p*cart[k].q; return s; }
			function mode(){ var r=form.querySelector('input[name=ag_mode]:checked'); return r?r.value:'emporter'; }
			function save(){ try{ localStorage.setItem(KEY,JSON.stringify(cart)); }catch(e){} }

			function syncAddBtns(){
				document.querySelectorAll('.ag-ord-add').forEach(function(b){
					var id=b.getAttribute('data-id');
					if(cart[id]){ b.classList.add('is-added'); b.textContent='✓ Ajouté ('+cart[id].q+')'; }
					else{ b.classList.remove('is-added'); b.textContent='+ Ajouter'; }
				});
			}

			function render(){
				var n=nItems();
				count.textContent=n;
				fabtotal.textContent=eur(subtotal());
				fab.hidden = n===0 && !drawer.classList.contains('is-open');
				if(n===0){ fab.hidden=true; }

				// lignes
				linesEl.innerHTML='';
				var keys=Object.keys(cart);
				if(keys.length===0){ emptyEl.hidden=false; bodyEl.hidden=true; }
				else{
					emptyEl.hidden=true; bodyEl.hidden=false;
					keys.forEach(function(id){
						var it=cart[id];
						var li=document.createElement('li'); li.className='ag-ord-line';
						li.innerHTML='<span class="ag-ord-line__name"></span>'
							+'<span class="ag-ord-qty"><button type="button" data-dec="'+id+'">−</button><span>'+it.q+'</span><button type="button" data-inc="'+id+'">+</button></span>'
							+'<span class="ag-ord-line__price">'+eur(it.p*it.q)+'</span>';
						li.querySelector('.ag-ord-line__name').textContent=it.n;
						linesEl.appendChild(li);
					});
				}
				// totaux
				var sub=subtotal(), m=mode(), fee=(m==='livraison')?FEE:0;
				subEl.textContent=eur(sub);
				if(m==='livraison'){ feeRow.hidden=false; feeEl.textContent=fee>0?eur(fee):'Offerte'; addrEl.hidden=false; }
				else{ feeRow.hidden=true; addrEl.hidden=true; }
				totalEl.textContent=eur(sub+fee);
				// minimum livraison
				if(m==='livraison' && MIN>0 && sub<MIN){
					minMsg.hidden=false;
					minMsg.textContent='Minimum '+eur(MIN)+' pour la livraison — il manque '+eur(MIN-sub)+'.';
					validate.disabled=true;
				}else{ minMsg.hidden=true; validate.disabled=(keys.length===0); }
				syncAddBtns();
				save();
			}

			function openDrawer(){ drawer.classList.add('is-open'); drawer.setAttribute('aria-hidden','false'); overlay.hidden=false; document.body.style.overflow='hidden'; }
			function closeDrawer(){ drawer.classList.remove('is-open'); drawer.setAttribute('aria-hidden','true'); overlay.hidden=true; document.body.style.overflow=''; render(); }
			function bumpFab(){ fab.classList.remove('is-bump'); void fab.offsetWidth; fab.classList.add('is-bump'); }

			// ── Parcours (écran d'accueil) ──────────────────────────────
			function setRadio(v){ var r=form.querySelector('input[name=ag_mode][value="'+v+'"]'); if(r) r.checked=true; }
			function placeModebar(){
				if(!modebar || modebar.dataset.moved) return;
				var menu=document.querySelector('.ag-carte-menu'), card=document.querySelector('.ag-carte-card');
				if(menu && card){ menu.insertBefore(modebar, card); modebar.dataset.moved='1'; }
			}
			function applyIntent(intent, persist){
				if(intent==='reserver'){ if(RESA) window.location.href=RESA; return; }
				if(intro) intro.hidden=true;
				if(intent==='consulter'){
					document.body.classList.add('ag-ord-browse');
					if(modetxt) modetxt.textContent='📖 Vous consultez la carte';
				}else{
					document.body.classList.remove('ag-ord-browse');
					setRadio(intent);
					if(modetxt) modetxt.textContent=(intent==='livraison'?'🛵 Vous commandez en livraison':'🥡 Vous commandez à emporter');
				}
				placeModebar();
				if(modebar) modebar.hidden=false;
				document.body.classList.add('ag-has-modebar');
				if(persist){ try{ sessionStorage.setItem(IKEY,intent); }catch(e){} }
				render();
			}
			function showIntro(){ if(intro){ intro.hidden=false; } }
			if(intro){ intro.querySelectorAll('.ag-ord-opt').forEach(function(b){ b.addEventListener('click', function(){ applyIntent(b.getAttribute('data-intent'), true); }); }); }
			if(introX){ introX.addEventListener('click', function(){ applyIntent('consulter', true); }); }
			if(modechange){ modechange.addEventListener('click', showIntro); }

			// Ajouter depuis la carte
			document.addEventListener('click', function(e){
				var add=e.target.closest('.ag-ord-add');
				if(add){
					var id=add.getAttribute('data-id');
					if(!cart[id]) cart[id]={n:add.getAttribute('data-name'),p:parseFloat(add.getAttribute('data-price'))||0,q:0};
					cart[id].q++;
					render(); bumpFab();
					return;
				}
				var inc=e.target.closest('[data-inc]');
				if(inc){ var i=inc.getAttribute('data-inc'); if(cart[i]){ cart[i].q++; render(); } return; }
				var dec=e.target.closest('[data-dec]');
				if(dec){ var d=dec.getAttribute('data-dec'); if(cart[d]){ cart[d].q--; if(cart[d].q<=0) delete cart[d]; render(); } return; }
			});

			fab.addEventListener('click', openDrawer);
			document.getElementById('ag-ord-close').addEventListener('click', closeDrawer);
			overlay.addEventListener('click', closeDrawer);
			form.querySelectorAll('input[name=ag_mode]').forEach(function(r){ r.addEventListener('change', render); });

			// WhatsApp
			function waText(){
				var lines=['Bonjour, je souhaite commander chez '+BRAND+' :',''];
				for(var k in cart){ lines.push('• '+cart[k].q+'× '+cart[k].n+' ('+eur(cart[k].p*cart[k].q)+')'); }
				var m=mode(), fee=(m==='livraison')?FEE:0;
				lines.push('', 'Mode : '+(m==='livraison'?'Livraison':'À emporter'));
				lines.push('Total : '+eur(subtotal()+fee));
				return encodeURIComponent(lines.join('\n'));
			}
			if(waBtn){ waBtn.addEventListener('click', function(e){ e.preventDefault(); if(nItems()===0) return; window.open('https://wa.me/'+WA+'?text='+waText(),'_blank'); }); }

			// Validation -> on sérialise le panier
			form.addEventListener('submit', function(e){
				if(nItems()===0){ e.preventDefault(); return; }
				var m=mode();
				if(m==='livraison' && MIN>0 && subtotal()<MIN){ e.preventDefault(); return; }
				if(m==='livraison'){
					var addr=form.querySelector('input[name=cust_addr]');
					if(addr && !addr.value.trim()){ e.preventDefault(); addr.focus(); addr.style.borderColor='#e06b5a'; return; }
				}
				var payload=[]; for(var k in cart) payload.push({id:k,q:cart[k].q});
				cartJson.value=JSON.stringify(payload);
				try{ localStorage.removeItem(KEY); }catch(err){}
			});

			// Au chargement : un paramètre ?go=... (depuis une carte de l'accueil)
			// applique directement le choix ; sinon on restaure le choix de la
			// session ; sinon on propose l'écran d'accueil.
			var go=null; try{ go=new URLSearchParams(location.search).get('go'); }catch(e){}
			var saved=null; try{ saved=sessionStorage.getItem(IKEY); }catch(e){}
			if(go && /^(livraison|emporter|consulter)$/.test(go)){ applyIntent(go, true); }
			else if(nItems()>0){ applyIntent((saved && saved!=='consulter')?saved:(HAS_DELIVERY?'livraison':'emporter'), false); }
			else if(saved){ applyIntent(saved, false); }
			else { showIntro(); }

			render();
		})();
		</script>
		<?php
	}

	/* ---------- Réception de la commande ---------- */

	public static function handle_submit() {
		check_admin_referer( 'ag_restaurant_order' );

		$raw  = isset( $_POST['ag_cart'] ) ? wp_unslash( $_POST['ag_cart'] ) : '[]';
		$cart = json_decode( $raw, true );
		$map  = self::price_map();

		$lines = array();
		$subtotal = 0.0;
		if ( is_array( $cart ) ) {
			foreach ( $cart as $row ) {
				$id  = isset( $row['id'] ) ? sanitize_title( $row['id'] ) : '';
				$qty = isset( $row['q'] ) ? max( 0, (int) $row['q'] ) : 0;
				if ( ! $id || ! $qty || ! isset( $map[ $id ] ) ) continue;
				$qty   = min( $qty, 50 );
				$price = $map[ $id ]['price'];
				$subtotal += $price * $qty;
				$lines[] = sprintf( '%dx %s — %s €', $qty, $map[ $id ]['name'], number_format( $price * $qty, 2, ',', ' ' ) );
			}
		}

		$mode  = ( isset( $_POST['ag_mode'] ) && 'livraison' === $_POST['ag_mode'] ) ? 'livraison' : 'emporter';
		$fee   = ( 'livraison' === $mode ) ? self::fee() : 0.0;
		$total = $subtotal + $fee;

		$name  = sanitize_text_field( $_POST['cust_name'] ?? '' );
		$phone = sanitize_text_field( $_POST['cust_phone'] ?? '' );
		$email = sanitize_email( $_POST['cust_email'] ?? '' );
		$addr  = sanitize_text_field( $_POST['cust_addr'] ?? '' );
		$zip   = sanitize_text_field( $_POST['cust_zip'] ?? '' );
		$city  = sanitize_text_field( $_POST['cust_city'] ?? '' );
		$slot  = sanitize_text_field( $_POST['cust_slot'] ?? '' );
		$note  = sanitize_textarea_field( $_POST['cust_note'] ?? '' );

		// Panier vide ou invalide -> on renvoie sans rien créer.
		$back = wp_get_referer() ?: home_url( '/carte/' );
		if ( empty( $lines ) ) {
			wp_safe_redirect( $back );
			exit;
		}

		$ref = 'CMD-' . strtoupper( wp_generate_password( 5, false, false ) );

		$body  = "Commande $ref\n";
		$body .= "Mode : " . ( 'livraison' === $mode ? 'Livraison' : 'À emporter' ) . "\n";
		$body .= str_repeat( '-', 28 ) . "\n";
		$body .= implode( "\n", $lines ) . "\n";
		$body .= str_repeat( '-', 28 ) . "\n";
		$body .= 'Sous-total : ' . number_format( $subtotal, 2, ',', ' ' ) . " €\n";
		if ( $fee > 0 ) $body .= 'Livraison : ' . number_format( $fee, 2, ',', ' ' ) . " €\n";
		$body .= 'TOTAL : ' . number_format( $total, 2, ',', ' ' ) . " €\n";
		$body .= str_repeat( '-', 28 ) . "\n";
		$body .= "Client : $name\nTél : $phone\n";
		if ( $email ) $body .= "Email : $email\n";
		if ( 'livraison' === $mode ) $body .= "Adresse : $addr $zip $city\n";
		if ( $slot ) $body .= "Créneau : $slot\n";
		if ( $note ) $body .= "Note : $note\n";

		$cpt = post_type_exists( 'ag_devis_lead' ) ? 'ag_devis_lead' : 'page';
		$lead_id = wp_insert_post( array(
			'post_type'   => $cpt,
			'post_status' => 'private',
			'post_title'  => 'Commande ' . $ref . ' — ' . ( $name ?: 'client' ) . ' (' . number_format( $total, 2, ',', ' ' ) . ' €)',
			'post_content'=> $body,
		) );
		if ( $lead_id && ! is_wp_error( $lead_id ) ) {
			update_post_meta( $lead_id, '_ag_lead_type', 'commande' );
			update_post_meta( $lead_id, '_ag_order_ref', $ref );
			update_post_meta( $lead_id, '_ag_order_total', $total );
			update_post_meta( $lead_id, '_ag_order_mode', $mode );
			update_post_meta( $lead_id, '_ag_resa_phone', $phone );
			update_post_meta( $lead_id, '_ag_resa_email', $email );
		}

		wp_mail(
			self::notify_email(),
			'🛒 Nouvelle commande ' . $ref . ' — ' . get_bloginfo( 'name' ),
			$body
		);

		if ( function_exists( 'ag_push' ) ) {
			ag_push( "🛒 Nouvelle commande\n" . $body );
		}

		// Crédit fidélité automatique (si le client est membre).
		do_action( 'ag_restaurant_order_placed', $email, $phone, $total );

		wp_safe_redirect( add_query_arg( array( 'ordered' => '1', 'ref' => $ref ), $back ) );
		exit;
	}
}
AG_Restaurant_Commande::init();
