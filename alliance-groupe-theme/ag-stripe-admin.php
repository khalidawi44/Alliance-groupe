<?php
/**
 * Alliance Groupe — Liens de paiement (admin page)
 *
 * Provides a "Liens de paiement" screen under the Réglages menu so the
 * user can paste any HTTPS payment link (Stripe, bank, SumUp, Lydia…)
 * for each offer without editing any code. The values are stored in
 * standard wp_options entries read by the front-end templates.
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
		'Liens de paiement',
		'Liens de paiement',
		'manage_options',
		'ag-stripe-config',
		'ag_stripe_admin_render'
	);
} );

/**
 * Register the 2 options through the Settings API.
 */
add_action( 'admin_init', function () {
	$ag_pp = function_exists( 'ag_creator_price' ) ? (int) ag_creator_price() : 69;
	$fields = array(
		'ag_stripe_business_url' => array(
			'label'       => 'Template Premium — ' . $ag_pp . '€',
			'description' => 'Lien de paiement Stripe (' . $ag_pp . '€) de la licence Premium. C\'est CE lien qu\'utilisent le créateur de site et les fiches métier. Ex : https://buy.stripe.com/xxxxxxx',
		),
		'ag_stripe_premium_url'  => array(
			'label'       => 'Ancien Pack Premium (inutilisé)',
			'description' => 'Plus utilisé dans le modèle 2 niveaux (Gratuit + Premium). Tu peux laisser vide.',
		),
		'ag_stripe_question_single_url' => array(
			'label'       => 'Question Flash — 45€ (1 question)',
			'description' => 'URL du lien de paiement pour 1 Question Flash (45€, réponse écrite sous 48h).',
		),
		'ag_stripe_question_pack_url'   => array(
			'label'       => 'Pack 3 Questions — 120€',
			'description' => 'URL du lien de paiement pour le pack de 3 Questions Flash (120€, utilisables sur 90 jours).',
		),
		'ag_stripe_question_sub_url'    => array(
			'label'       => 'Abonnement Expert — 199€/mois',
			'description' => 'URL du lien de paiement pour l\'abonnement mensuel Questions Expert (199€/mois, jusqu\'à 8 questions/mois).',
		),
		'ag_stripe_consult_express_url' => array(
			'label'       => 'Consultation Express — 30 min (59€)',
			'description' => 'Lien de paiement pour un appel/visio de 30 min. Redirection après paiement : ' . esc_url( home_url( '/consultation?paid=1&offre=express' ) ),
		),
		'ag_stripe_consult_strategique_url' => array(
			'label'       => 'Consultation Stratégique — 60 min (119€)',
			'description' => 'Lien de paiement pour un appel/visio de 60 min. Redirection après paiement : ' . esc_url( home_url( '/consultation?paid=1&offre=strategique' ) ),
		),
		'ag_stripe_express_essentiel_url' => array(
			'label'       => 'Site Express — Essentiel (490€)',
			'description' => 'URL du lien de paiement pour le pack Site Express Essentiel. Configure l\'URL de redirection après paiement vers : ' . esc_url( home_url( '/sites-express?paid=essentiel#brief' ) ),
		),
		'ag_stripe_express_pro_url' => array(
			'label'       => 'Site Express — Pro (890€)',
			'description' => 'URL du lien de paiement pour le pack Site Express Pro. Redirection après paiement : ' . esc_url( home_url( '/sites-express?paid=pro#brief' ) ),
		),
		'ag_refais_boost_url' => array(
			'label'       => 'Maquette IA en ligne' . ( function_exists( 'ag_refais_boost_offre' )
				? ' — ' . ag_refais_boost_offre()['prix'] . ag_refais_boost_offre()['unite']
				: '' ) . ' (ABONNEMENT)',
			'description' => 'Micro-offre proposée juste après une maquette « Refais mon site par l\'IA » : on met la maquette en ligne sous 24 h, nom de domaine compris, montant déduit du site complet. '
				. '⚠️ Ce lien doit être créé en mode <strong>ABONNEMENT mensuel</strong> (récurrent), pas en paiement unique : c\'est lui qui finance l\'hébergement et le renouvellement du domaine. '
				. '⚠️ Créer un lien NEUF — ne pas réutiliser celui du rapport de sécurité ni celui de la maintenance : chaque produit a besoin du sien pour que les paiements se rapprochent. '
				. 'Tant que ce champ est vide, le bouton affiche « En parler au téléphone » et renvoie vers /contact : rien n\'est cassé, mais rien n\'encaisse.',
		),
		'ag_stripe_express_boutique_url' => array(
			'label'       => 'Site Express — Boutique (1490€)',
			'description' => 'URL du lien de paiement pour le pack Site Express Boutique. Redirection après paiement : ' . esc_url( home_url( '/sites-express?paid=boutique#brief' ) ),
		),
		'ag_stripe_maint_serenite_url' => array(
			'label'       => 'Maintenance — Sérénité (29€/mois)',
			'description' => 'lien de paiement en mode ABONNEMENT (récurrent mensuel). Redirection : ' . esc_url( home_url( '/sites-express?abo=ok#maintenance' ) ),
		),
		'ag_stripe_maint_croissance_url' => array(
			'label'       => 'Maintenance — Croissance (59€/mois)',
			'description' => 'lien de paiement en mode ABONNEMENT mensuel.',
		),
		'ag_stripe_maint_performance_url' => array(
			'label'       => 'Maintenance — Performance (99€/mois)',
			'description' => 'lien de paiement en mode ABONNEMENT mensuel.',
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
 * Sanitize the payment link input : empty string or the placeholder
 * both map back to STRIPE_PLACEHOLDER so the front-end fallback
 * kicks in. Any valid HTTPS payment link is accepted (Stripe, bank,
 * SumUp, Lydia…) — only the https:// scheme is required.
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
	// On accepte TOUT lien de paiement HTTPS valide : ta banque, Stripe,
	// SumUp, Lydia... Securite : on exige juste le https://.
	if ( 'https' !== wp_parse_url( $url, PHP_URL_SCHEME ) ) {
		add_settings_error(
			'ag_stripe_config',
			'not_https',
			'Le lien de paiement doit commencer par https:// : ' . esc_html( $value )
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
	$c_express = get_option( 'ag_stripe_consult_express_url', 'STRIPE_PLACEHOLDER' );
	$c_strat   = get_option( 'ag_stripe_consult_strategique_url', 'STRIPE_PLACEHOLDER' );

	$state_badge = function ( $value ) {
		if ( 'STRIPE_PLACEHOLDER' === $value || '' === $value ) {
			return '<span style="display:inline-block;padding:3px 10px;background:#fff8c2;color:#8a6d00;border:1px solid #e8d676;border-radius:12px;font-size:.8rem;font-weight:700;">⚠ Placeholder (fallback /contact actif)</span>';
		}
		return '<span style="display:inline-block;padding:3px 10px;background:#e7f5e9;color:#1a7a33;border:1px solid #a7d8b3;border-radius:12px;font-size:.8rem;font-weight:700;">✓ Lien actif</span>';
	};

	?>
	<div class="wrap">
		<h1>Liens de paiement</h1>
		<p style="font-size:.95rem;color:#50575e;max-width:760px;">
			Collez ici vos <strong>liens de paiement</strong> pour chaque offre.
			Vous pouvez utiliser <strong>n'importe quel service</strong> : votre banque,
			Stripe, SumUp, Lydia… (un simple lien en <code>https://</code> suffit).
			Tant qu'un champ est vide, le bouton
			correspondant retombe sur le formulaire <em>/contact</em> ou le brief (le
			contact est quand même capturé).
		</p>

		<div style="max-width:760px;margin-top:16px;padding:18px 20px;background:#fff;border:1px solid #ccd0d4;border-left:4px solid #D4B45C;">
			<strong>Comment créer un lien de paiement Stripe ?</strong>
			<ol style="margin:8px 0 0 22px;">
				<li>Connectez-vous à votre <a href="https://dashboard.stripe.com/" target="_blank" rel="noopener">dashboard Stripe</a>.</li>
				<li><em>Paiements</em> &gt; <em>Liens de paiement</em> &gt; <em>Créer un lien</em> (Payment Links). Pour un abonnement : choisissez « Récurrent ».</li>
				<li>Créez le produit (ex. « AG Starter Premium »), montant TTC en EUR.</li>
				<li>Copiez le lien <code>https://buy.stripe.com/xxxxxxx</code> puis collez-le ci-dessous.</li>
				<li>Répétez pour chaque offre. ⚙️ Webhook : <code><?php echo esc_html( home_url( '/wp-json/ag/v1/stripe-webhook' ) ); ?></code> (évènement <code>checkout.session.completed</code>) → génère la clé automatiquement.</li>
			</ol>
		</div>

		<form method="post" action="options.php" style="max-width:760px;margin-top:24px;">
			<?php settings_fields( 'ag_stripe_config' ); ?>

			<table class="form-table" role="presentation">
				<?php $ag_pp = function_exists( 'ag_creator_price' ) ? (int) ag_creator_price() : 69; ?>
				<tr>
					<th scope="row">
						<label for="ag_stripe_business_url">Template Premium — <?php echo (int) $ag_pp; ?>€</label>
					</th>
					<td>
						<input type="url" name="ag_stripe_business_url" id="ag_stripe_business_url"
							value="<?php echo esc_attr( $business ); ?>"
							class="regular-text code"
							placeholder="https://buy.stripe.com/...">
						<p class="description">
							Lien Stripe de la licence <strong>Premium (<?php echo (int) $ag_pp; ?>€)</strong> — utilisé par le créateur de site et les fiches métier.<br>
							<?php echo $state_badge( $business ); // phpcs:ignore ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="ag_stripe_premium_url">Ancien Premium <span style="color:#999;font-weight:400;">(inutilisé)</span></label>
					</th>
					<td>
						<input type="url" name="ag_stripe_premium_url" id="ag_stripe_premium_url"
							value="<?php echo esc_attr( $premium ); ?>"
							class="regular-text code"
							placeholder="(laisser vide)">
						<p class="description">
							Plus utilisé dans le modèle 2 niveaux (Gratuit + Premium). Tu peux laisser vide.
						</p>
					</td>
				</tr>

				<tr><td colspan="2" style="padding:16px 0 4px;"><h2 style="margin:0;font-size:1.15rem;color:#1d2327;">Questions Flash — consultations écrites</h2><p style="color:#50575e;font-size:.9rem;margin:4px 0 0;">Liens de paiement pour les offres de réponse écrite sous 48h proposées sur la page <em>/questions-flash</em>.</p></td></tr>

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

				<tr><td colspan="2" style="padding:16px 0 4px;"><h2 style="margin:0;font-size:1.15rem;color:#1d2327;">Consultations (appels payants)</h2><p style="color:#50575e;font-size:.9rem;margin:4px 0 0;">Liens de paiement pour les appels/visios payants proposés sur la page <em>/consultation</em>. Après paiement, le client est invité à choisir un créneau par retour de contact (pas d'outil payant).</p></td></tr>
				<tr>
					<th scope="row"><label for="ag_stripe_consult_express_url">Consultation Express — 30 min (59€)</label></th>
					<td>
						<input type="url" name="ag_stripe_consult_express_url" id="ag_stripe_consult_express_url"
							value="<?php echo esc_attr( $c_express ); ?>" class="regular-text code"
							placeholder="https://buy.stripe.com/...">
						<p class="description"><?php echo $state_badge( $c_express ); // phpcs:ignore ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="ag_stripe_consult_strategique_url">Consultation Stratégique — 60 min (119€)</label></th>
					<td>
						<input type="url" name="ag_stripe_consult_strategique_url" id="ag_stripe_consult_strategique_url"
							value="<?php echo esc_attr( $c_strat ); ?>" class="regular-text code"
							placeholder="https://buy.stripe.com/...">
						<p class="description"><?php echo $state_badge( $c_strat ); // phpcs:ignore ?></p>
					</td>
				</tr>
			</table>

			<?php submit_button( 'Enregistrer les liens de paiement' ); ?>
		</form>

		<div style="max-width:760px;margin-top:24px;padding:18px 20px;background:#f0f6fc;border:1px solid #c3dffb;border-radius:6px;">
			<strong>Astuce :</strong> pour vider une URL et revenir au fallback <em>/contact</em>,
			laissez le champ vide et cliquez sur <em>Enregistrer</em>. Tout lien de paiement
			valide en <code>https://</code> est accepté (Stripe, banque, SumUp, Lydia…) ;
			une URL non sécurisée est rejetée avec un message d'erreur en haut de page.
		</div>

		<hr style="margin:40px 0 24px;border:none;border-top:1px solid #ddd;">

		<h2 style="font-size:1.4rem;">📋 Descriptions produit prêtes à coller dans Stripe</h2>
		<p style="color:#50575e;max-width:780px;">
			Pour chaque lien de paiement Stripe, copiez-collez la description correspondante
			dans le champ <em>« Description du produit »</em> de Stripe lors de la création.
			Chaque description rappelle votre offre de site sur-mesure pour faire remonter
			les acheteurs vers le ticket le plus élevé.
		</p>

		<?php
		$thank_you_url    = home_url( '/merci-achat' );
		$contact_url      = home_url( '/contact' );
		$tel              = '+33744829516';

		$ag_pp = function_exists( 'ag_creator_price' ) ? (int) ag_creator_price() : 69;
		$products = array(
			'premium' => array(
				'name'  => 'AG Starter Premium — ' . $ag_pp . '€',
				'price' => number_format( $ag_pp, 2, ',', ' ' ) . ' €',
				'desc'  => "Plugin WordPress AG Starter Premium : notre design le plus abouti — animations, blocs Gutenberg premium, customizer etendu, sticky header, polices Google Fonts, support email. Compatible avec tous les themes AG Starter. Paiement unique. 💎 Besoin d'un site totalement sur-mesure ? Appel gratuit : alliancegroupe-inc.com/contact",
				'success' => "Merci pour votre achat ! Votre plugin Premium arrive par email sous 5 min. 💎 Site sur-mesure : alliancegroupe-inc.com/contact",
			),
		);
		?>

		<div style="background:#fff8e6;border:1px solid #f5c64d;border-left:4px solid #D4B45C;padding:18px 22px;border-radius:6px;margin-bottom:24px;max-width:780px;">
			<strong>🎯 Page de remerciement à utiliser comme « URL de retour après paiement » dans Stripe :</strong><br>
			<code style="background:#fff;padding:4px 8px;border-radius:3px;display:inline-block;margin-top:6px;font-size:.95rem;">
				<?php echo esc_html( $thank_you_url ); ?>?pack=premium
			</code><br>
			<small style="color:#665;">Une seule licence payante : le Premium.</small>
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
					<strong>Prix :</strong> <?php echo esc_html( $prod['price'] ); ?> · paiement unique ·
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
