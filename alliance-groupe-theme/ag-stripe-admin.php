<?php
/**
 * Alliance Groupe — Stripe Payment Links admin page
 *
 * Provides a minimal "Configuration Stripe AG" screen under the
 * Réglages menu so the user can paste the 2 Stripe Payment Link
 * URLs (Premium / Business) without editing any code. The
 * values are stored in standard wp_options entries read by
 * templates/page-templates.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'AG_STRIPE_ADMIN_LOADED' ) ) {
	return;
}
define( 'AG_STRIPE_ADMIN_LOADED', true );

/**
 * Register the submenu under Réglages.
 */
add_action( 'admin_menu', function () {
	add_options_page(
		'Configuration Stripe AG',
		'Stripe AG',
		'manage_options',
		'ag-stripe-config',
		'ag_stripe_admin_render'
	);
} );

/**
 * Register the 2 options through the Settings API.
 */
add_action( 'admin_init', function () {
	$fields = array(
		'ag_stripe_premium_url'  => array(
			'label'       => 'Pack Premium — 99€',
			'description' => 'URL du Payment Link Stripe pour le Pack Premium. Ex : https://buy.stripe.com/xxxxxxx',
		),
		'ag_stripe_business_url' => array(
			'label'       => 'Pack Business — 199€',
			'description' => 'URL du Payment Link Stripe pour le Pack Business.',
		),
		'ag_stripe_question_single_url' => array(
			'label'       => 'Question Flash — 45€ (1 question)',
			'description' => 'URL du Payment Link Stripe pour 1 Question Flash (45€, réponse écrite sous 48h).',
		),
		'ag_stripe_question_pack_url'   => array(
			'label'       => 'Pack 3 Questions — 120€',
			'description' => 'URL du Payment Link Stripe pour le pack de 3 Questions Flash (120€, utilisables sur 90 jours).',
		),
		'ag_stripe_question_sub_url'    => array(
			'label'       => 'Abonnement Expert — 199€/mois',
			'description' => 'URL du Payment Link Stripe pour l\'abonnement mensuel Questions Expert (199€/mois, jusqu\'à 8 questions/mois).',
		),
	);
	foreach ( $fields as $key => $meta ) {
		register_setting(
			'ag_stripe_config',
			$key,
			array(
				'type'              => 'string',
				'sanitize_callback' => 'ag_stripe_sanitize_url',
				'default'           => 'STRIPE_PLACEHOLDER',
				'show_in_rest'      => false,
			)
		);
	}
} );

/**
 * Sanitize the Stripe URL input : empty string or the placeholder
 * both map back to STRIPE_PLACEHOLDER so the front-end fallback
 * kicks in. Real URLs must use one of the allowed Stripe hosts
 * (official or AG custom domain) — anything else is rejected.
 *
 * Allowed hosts :
 *   - buy.stripe.com            (default Payment Links)
 *   - checkout.stripe.com       (Checkout sessions)
 *   - paiement.alliancegroupe-inc.com  (AG custom domain)
 *
 * Additional hosts can be plugged in via the `ag_stripe_allowed_hosts`
 * filter without editing this file.
 *
 * @param string $value Raw value.
 * @return string
 */
function ag_stripe_sanitize_url( $value ) {
	$value = trim( (string) $value );
	if ( '' === $value || 'STRIPE_PLACEHOLDER' === $value ) {
		return 'STRIPE_PLACEHOLDER';
	}
	$url = esc_url_raw( $value );
	if ( ! $url ) {
		add_settings_error( 'ag_stripe_config', 'bad_url', 'URL invalide ignorée : ' . esc_html( $value ) );
		return 'STRIPE_PLACEHOLDER';
	}
	$host    = wp_parse_url( $url, PHP_URL_HOST );
	$allowed = apply_filters( 'ag_stripe_allowed_hosts', array(
		'buy.stripe.com',
		'checkout.stripe.com',
		'paiement.alliancegroupe-inc.com',
	) );
	if ( ! in_array( $host, $allowed, true ) ) {
		add_settings_error(
			'ag_stripe_config',
			'bad_host',
			sprintf(
				'Hôte rejeté (%s). Hôtes acceptés : %s',
				esc_html( $host ),
				esc_html( implode( ', ', $allowed ) )
			)
		);
		return 'STRIPE_PLACEHOLDER';
	}
	return $url;
}

/**
 * Render the admin page.
 */
function ag_stripe_admin_render() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$premium  = get_option( 'ag_stripe_premium_url', 'STRIPE_PLACEHOLDER' );
	$business = get_option( 'ag_stripe_business_url', 'STRIPE_PLACEHOLDER' );
	$q_single = get_option( 'ag_stripe_question_single_url', 'STRIPE_PLACEHOLDER' );
	$q_pack   = get_option( 'ag_stripe_question_pack_url', 'STRIPE_PLACEHOLDER' );
	$q_sub    = get_option( 'ag_stripe_question_sub_url', 'STRIPE_PLACEHOLDER' );

	$state_badge = function ( $value ) {
		if ( 'STRIPE_PLACEHOLDER' === $value || '' === $value ) {
			return '<span style="display:inline-block;padding:3px 10px;background:#fff8c2;color:#8a6d00;border:1px solid #e8d676;border-radius:12px;font-size:.8rem;font-weight:700;">⚠ Placeholder (fallback /contact actif)</span>';
		}
		return '<span style="display:inline-block;padding:3px 10px;background:#e7f5e9;color:#1a7a33;border:1px solid #a7d8b3;border-radius:12px;font-size:.8rem;font-weight:700;">✓ Stripe actif</span>';
	};

	?>
	<div class="wrap">
		<h1>Configuration Stripe AG</h1>
		<p style="font-size:.95rem;color:#50575e;max-width:760px;">
			Collez ici les URLs des Payment Links Stripe pour les 2 packs
			<strong>Premium</strong> et <strong>Business</strong>.
			Tant qu'une URL est à <code>STRIPE_PLACEHOLDER</code> (ou vide), le bouton
			correspondant sur la page <em>/templates-wordpress</em> retombe sur le
			formulaire <em>/contact</em> avec le pack en paramètre (le lead est quand
			même capturé).
		</p>

		<div style="max-width:760px;margin-top:16px;padding:18px 20px;background:#fff;border:1px solid #ccd0d4;border-left:4px solid #D4B45C;">
			<strong>Comment créer un Payment Link Stripe ?</strong>
			<ol style="margin:8px 0 0 22px;">
				<li>Connectez-vous à votre <a href="https://dashboard.stripe.com/payment-links" target="_blank" rel="noopener">dashboard Stripe</a>.</li>
				<li>Cliquez sur <em>Payment links</em> &gt; <em>Nouveau lien de paiement</em>.</li>
				<li>Créez un produit (ex. « AG Starter Premium »), tarif unique, montant 99,00&nbsp;€ TTC.</li>
				<li>Copiez le lien <code>https://buy.stripe.com/xxxxxxx</code> puis collez-le ci-dessous.</li>
				<li>Répétez pour Business (199&nbsp;€).</li>
			</ol>
		</div>

		<form method="post" action="options.php" style="max-width:760px;margin-top:24px;">
			<?php settings_fields( 'ag_stripe_config' ); ?>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">
						<label for="ag_stripe_premium_url">Pack Premium — 99€</label>
					</th>
					<td>
						<input type="url" name="ag_stripe_premium_url" id="ag_stripe_premium_url"
							value="<?php echo esc_attr( $premium ); ?>"
							class="regular-text code"
							placeholder="https://buy.stripe.com/...">
						<p class="description">
							<?php echo $state_badge( $premium ); // phpcs:ignore ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="ag_stripe_business_url">Pack Business — 199€</label>
					</th>
					<td>
						<input type="url" name="ag_stripe_business_url" id="ag_stripe_business_url"
							value="<?php echo esc_attr( $business ); ?>"
							class="regular-text code"
							placeholder="https://buy.stripe.com/...">
						<p class="description">
							<?php echo $state_badge( $business ); // phpcs:ignore ?>
						</p>
					</td>
				</tr>

				<tr><td colspan="2" style="padding:16px 0 4px;"><h2 style="margin:0;font-size:1.15rem;color:#1d2327;">Questions Flash — consultations écrites</h2><p style="color:#50575e;font-size:.9rem;margin:4px 0 0;">Payment Links Stripe pour les offres de réponse écrite sous 48h proposées sur la page <em>/questions-flash</em>.</p></td></tr>

				<tr>
					<th scope="row">
						<label for="ag_stripe_question_single_url">1 Question Flash — 45€</label>
					</th>
					<td>
						<input type="url" name="ag_stripe_question_single_url" id="ag_stripe_question_single_url"
							value="<?php echo esc_attr( $q_single ); ?>"
							class="regular-text code"
							placeholder="https://buy.stripe.com/...">
						<p class="description">
							<?php echo $state_badge( $q_single ); // phpcs:ignore ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="ag_stripe_question_pack_url">Pack 3 Questions — 120€</label>
					</th>
					<td>
						<input type="url" name="ag_stripe_question_pack_url" id="ag_stripe_question_pack_url"
							value="<?php echo esc_attr( $q_pack ); ?>"
							class="regular-text code"
							placeholder="https://buy.stripe.com/...">
						<p class="description">
							<?php echo $state_badge( $q_pack ); // phpcs:ignore ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="ag_stripe_question_sub_url">Abonnement Expert — 199€/mois</label>
					</th>
					<td>
						<input type="url" name="ag_stripe_question_sub_url" id="ag_stripe_question_sub_url"
							value="<?php echo esc_attr( $q_sub ); ?>"
							class="regular-text code"
							placeholder="https://buy.stripe.com/...">
						<p class="description">
							<?php echo $state_badge( $q_sub ); // phpcs:ignore ?>
							<br><em style="color:#666;">Doit être un Payment Link en mode « subscription » (abonnement mensuel).</em>
						</p>
					</td>
				</tr>
			</table>

			<?php submit_button( 'Enregistrer les URLs Stripe' ); ?>
		</form>

		<div style="max-width:760px;margin-top:24px;padding:18px 20px;background:#f0f6fc;border:1px solid #c3dffb;border-radius:6px;">
			<strong>Astuce :</strong> pour vider une URL et revenir au fallback <em>/contact</em>,
			laissez le champ vide et cliquez sur <em>Enregistrer</em>. Les hôtes acceptés sont
			<code>buy.stripe.com</code> et <code>checkout.stripe.com</code>, toute autre URL est
			rejetée avec un message d'erreur en haut de page.
		</div>

		<hr style="margin:40px 0 24px;border:none;border-top:1px solid #ddd;">

		<h2 style="font-size:1.4rem;">📋 Descriptions produit prêtes à coller dans Stripe</h2>
		<p style="color:#50575e;max-width:780px;">
			Pour chaque Payment Link Stripe, copiez-collez la description correspondante
			dans le champ <em>« Description du produit »</em> de Stripe lors de la création.
			Chaque description rappelle votre offre de site sur-mesure pour faire remonter
			les acheteurs vers le ticket le plus élevé.
		</p>

		<?php
		$thank_you_url    = home_url( '/merci-achat' );
		$contact_url      = home_url( '/contact' );
		$tel              = '+33623526074';

		$products = array(
			'premium' => array(
				'name'  => 'AG Starter Premium — 99€',
				'price' => '99,00 €',
				'desc'  => "Plugin WordPress AG Starter Premium : design travaille, animations, 10 blocs Gutenberg premium, customizer etendu (50+ reglages), sticky header, polices Google Fonts, support email 60 jours. Compatible 5 themes (Restaurant/Artisan/Coach/Avocat/Barber). Paiement unique. 💎 Besoin d'un site sur-mesure qui genere +340% de leads ? Appel gratuit : alliancegroupe-inc.com/contact",
				'success' => "Merci pour votre achat ! Votre plugin Premium arrive par email sous 5 min. 💎 Site sur-mesure (+340% leads en 3 mois) : alliancegroupe-inc.com/contact",
			),
			'business' => array(
				'name'  => 'AG Starter Business — 199€',
				'price' => '199,00 €',
				'desc'  => "Pack tout-en-un AG Starter : tout Premium + installation assistee en visio 1h, maintenance WP 1 an, audit SEO mensuel, rapport perf trimestriel, support 2h, white-label, integration CRM (HubSpot/Pipedrive/Brevo), appel strategique avec Fabrizio. Paiement unique. 💎 Site totalement sur-mesure (+340% leads en 3 mois) : alliancegroupe-inc.com/contact",
				'success' => "Merci pour votre achat du Pack Business ! Notre equipe vous contacte sous 24h ouvrees pour planifier l'installation et l'appel strategique avec Fabrizio. Tel : 06.23.52.60.74",
			),
		);
		?>

		<div style="background:#fff8e6;border:1px solid #f5c64d;border-left:4px solid #D4B45C;padding:18px 22px;border-radius:6px;margin-bottom:24px;max-width:780px;">
			<strong>🎯 Page de remerciement à utiliser comme « URL de réussite » dans Stripe :</strong><br>
			<code style="background:#fff;padding:4px 8px;border-radius:3px;display:inline-block;margin-top:6px;font-size:.95rem;">
				<?php echo esc_html( $thank_you_url ); ?>?pack=premium
			</code><br>
			<small style="color:#665;">Remplacez <code>premium</code> par <code>business</code> selon le pack.</small>
			<p style="margin:10px 0 0;color:#665;font-size:.92rem;">
				Cette page affiche un message de confirmation + un gros call-to-action vers
				votre offre de site sur-mesure. <strong>Important</strong> : créez d'abord la
				page WordPress qui utilise le template <em>« Merci pour votre achat »</em>
				avec le slug <code>merci-achat</code> (Pages → Ajouter → choisir le template).
			</p>
		</div>

		<?php foreach ( $products as $key => $prod ) : ?>
			<div style="max-width:780px;margin-bottom:32px;padding:0;background:#fff;border:1px solid #ccd0d4;border-radius:8px;overflow:hidden;">
				<div style="padding:14px 22px;background:#1d2327;color:#fff;display:flex;justify-content:space-between;align-items:center;">
					<strong style="font-size:1.05rem;"><?php echo esc_html( $prod['name'] ); ?></strong>
					<span style="font-size:.8rem;color:#9aa0a6;text-transform:uppercase;letter-spacing:1px;">Pack <?php echo esc_html( $key ); ?></span>
				</div>

				<div style="padding:18px 22px;border-bottom:1px solid #eee;">
					<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
						<strong style="color:#1d2327;">📝 Description du produit</strong>
						<button type="button" class="button button-small ag-copy-btn" data-target="ag-desc-<?php echo esc_attr( $key ); ?>">📋 Copier</button>
					</div>
					<textarea id="ag-desc-<?php echo esc_attr( $key ); ?>" readonly rows="14" style="width:100%;font-family:Menlo,Consolas,monospace;font-size:.85rem;line-height:1.5;background:#f6f7f7;border:1px solid #ddd;padding:12px;border-radius:4px;resize:vertical;"><?php echo esc_textarea( $prod['desc'] ); ?></textarea>
				</div>

				<div style="padding:18px 22px;border-bottom:1px solid #eee;">
					<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
						<strong style="color:#1d2327;">💌 Message de confirmation après paiement</strong>
						<button type="button" class="button button-small ag-copy-btn" data-target="ag-success-<?php echo esc_attr( $key ); ?>">📋 Copier</button>
					</div>
					<textarea id="ag-success-<?php echo esc_attr( $key ); ?>" readonly rows="6" style="width:100%;font-family:Menlo,Consolas,monospace;font-size:.85rem;line-height:1.5;background:#f6f7f7;border:1px solid #ddd;padding:12px;border-radius:4px;resize:vertical;"><?php echo esc_textarea( $prod['success'] ); ?></textarea>
				</div>

				<div style="padding:14px 22px;background:#f6f7f7;font-size:.88rem;color:#50575e;">
					<strong>Prix Stripe :</strong> <?php echo esc_html( $prod['price'] ); ?> · paiement unique ·
					<strong>Devise :</strong> EUR ·
					<strong>URL de réussite recommandée :</strong>
					<code><?php echo esc_html( $thank_you_url . '?pack=' . $key ); ?></code>
				</div>
			</div>
		<?php endforeach; ?>

		<script>
		(function(){
			document.querySelectorAll('.ag-copy-btn').forEach(function(btn){
				btn.addEventListener('click', function(){
					var ta = document.getElementById(btn.getAttribute('data-target'));
					if (!ta) return;
					ta.select();
					try {
						document.execCommand('copy');
						var original = btn.textContent;
						btn.textContent = '✓ Copié !';
						btn.style.background = '#28a745';
						btn.style.borderColor = '#28a745';
						btn.style.color = '#fff';
						setTimeout(function(){
							btn.textContent = original;
							btn.style.background = '';
							btn.style.borderColor = '';
							btn.style.color = '';
						}, 1500);
					} catch(e) {}
				});
			});
		})();
		</script>
	</div>
	<?php
}
