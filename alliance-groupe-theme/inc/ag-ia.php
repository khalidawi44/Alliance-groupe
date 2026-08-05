<?php
/**
 * ag-ia.php — Petit noyau partagé pour appeler l'IA Claude.
 *
 * Un SEUL endroit qui parle à l'API Anthropic, réutilisé par les modules
 * « nouveauté » (refais-mon-site, concierge, devis instantané, journal, nuit).
 * La clé est déjà stockée dans l'option `ag_ai_key` (voir ag-appels-offres.php) :
 * on NE recrée PAS de champ, on réutilise l'existant.
 *
 * Tout dégrade proprement quand la clé est absente : les modules affichent
 * un message clair au lieu de casser le site.
 *
 * @package Alliance_Groupe
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! defined( 'AG_IA_MODEL' ) ) {
	define( 'AG_IA_MODEL', 'claude-opus-4-8' ); // « smart » : analyses internes (marchés publics…).
}
if ( ! defined( 'AG_IA_MODEL_FAST' ) ) {
	define( 'AG_IA_MODEL_FAST', 'claude-haiku-4-5' ); // « fast » : outils grand public (chat, devis, maquette).
}

/**
 * Renvoie le modèle à utiliser selon le niveau voulu.
 * Réglable sans toucher au code via les options `ag_ia_model_fast` / `_smart`.
 *
 * @param string $tier 'fast' (défaut, économique) ou 'smart' (qualité max).
 * @return string  Identifiant de modèle Claude.
 */
function ag_ia_model( $tier = 'fast' ) {
	if ( 'smart' === $tier ) {
		$m = trim( (string) get_option( 'ag_ia_model_smart', '' ) );
		return '' !== $m ? $m : AG_IA_MODEL;
	}
	$m = trim( (string) get_option( 'ag_ia_model_fast', '' ) );
	return '' !== $m ? $m : AG_IA_MODEL_FAST;
}

/** La clé API Claude (option partagée `ag_ai_key`). */
function ag_ia_key() {
	return trim( (string) get_option( 'ag_ai_key', '' ) );
}

/**
 * Champ « Clé API Claude » dans Réglages → Général : un endroit CENTRAL, toujours
 * accessible (contrairement à Prospection → Appels d'offres qui peut être en 404),
 * qui alimente TOUS les outils IA (concierge, devis, refais-mon-site, gardien de
 * nuit). Écrit la même option `ag_ai_key` — pas de doublon de données.
 */
add_action( 'admin_init', function () {
	register_setting( 'general', 'ag_ai_key', array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
		'default'           => '',
		'show_in_rest'      => false,
	) );
	add_settings_field( 'ag_ai_key_general', '🤖 Clé API Claude (IA)', function () {
		$v  = trim( (string) get_option( 'ag_ai_key', '' ) );
		$ok = '' !== $v;
		echo '<input type="password" name="ag_ai_key" value="' . esc_attr( $v ) . '" placeholder="sk-ant-…" autocomplete="off" style="width:100%;max-width:520px;font-family:monospace">';
		echo '<p class="description">Active le <strong>concierge IA</strong>, le <strong>devis instantané</strong>, <strong>« refais mon site »</strong> et le résumé du <strong>gardien de nuit</strong>. Clé sur <a href="https://console.anthropic.com" target="_blank" rel="noopener">console.anthropic.com</a> → API Keys (pense à ajouter des crédits : Billing → Add credits). ';
		echo $ok ? '<strong style="color:#1f7a3d">✓ Clé enregistrée.</strong>' : '<strong style="color:#b32d2e">Non configurée pour l\'instant.</strong>';
		echo '</p>';
	}, 'general' );
} );

/** Vrai si l'IA est branchée (clé présente). */
function ag_ia_ready() {
	return '' !== ag_ia_key();
}

/**
 * Appel texte simple à Claude.
 *
 * @param string $system  Consigne système.
 * @param mixed  $user    Message utilisateur (string) ou tableau de blocs de contenu.
 * @param array  $opts    max_tokens, model, temperature, tools, tool_choice, messages (override).
 * @return string|WP_Error  Le texte de la réponse, ou WP_Error.
 */
function ag_ia_call( $system, $user, $opts = array() ) {
	$key = ag_ia_key();
	if ( '' === $key ) {
		return new WP_Error( 'ag_ia_nokey', "L'IA n'est pas encore branchée (clé API Claude manquante)." );
	}

	$messages = isset( $opts['messages'] ) && is_array( $opts['messages'] )
		? $opts['messages']
		: array( array( 'role' => 'user', 'content' => $user ) );

	$body = array(
		'model'      => isset( $opts['model'] ) ? $opts['model'] : AG_IA_MODEL,
		'max_tokens' => isset( $opts['max_tokens'] ) ? (int) $opts['max_tokens'] : 2000,
		'messages'   => $messages,
	);
	if ( '' !== (string) $system ) {
		$body['system'] = (string) $system;
	}
	if ( isset( $opts['temperature'] ) ) {
		$body['temperature'] = (float) $opts['temperature'];
	}
	if ( isset( $opts['tools'] ) ) {
		$body['tools'] = $opts['tools'];
	}
	if ( isset( $opts['tool_choice'] ) ) {
		$body['tool_choice'] = $opts['tool_choice'];
	}
	if ( isset( $opts['output_config'] ) ) {
		$body['output_config'] = $opts['output_config'];
	}

	$res = wp_remote_post( 'https://api.anthropic.com/v1/messages', array(
		'timeout' => isset( $opts['timeout'] ) ? (int) $opts['timeout'] : 60,
		'headers' => array(
			'x-api-key'         => $key,
			'anthropic-version' => '2023-06-01',
			'content-type'      => 'application/json',
		),
		'body'    => wp_json_encode( $body ),
	) );
	if ( is_wp_error( $res ) ) {
		return $res;
	}
	$code = (int) wp_remote_retrieve_response_code( $res );
	$json = json_decode( wp_remote_retrieve_body( $res ), true );
	if ( 200 !== $code ) {
		$msg = isset( $json['error']['message'] ) ? $json['error']['message'] : ( 'Erreur API (HTTP ' . $code . ')' );
		return new WP_Error( 'ag_ia_http', $msg );
	}
	// Renvoie la réponse brute si on veut inspecter les blocs (tool_use…).
	if ( ! empty( $opts['raw'] ) ) {
		return $json;
	}
	$txt = '';
	foreach ( (array) ( isset( $json['content'] ) ? $json['content'] : array() ) as $blk ) {
		if ( ( isset( $blk['type'] ) ? $blk['type'] : '' ) === 'text' ) {
			$txt .= $blk['text'];
		}
	}
	return $txt;
}

/**
 * Appel qui force une sortie JSON validée par un schéma (structured output).
 *
 * @param string $system Consigne système.
 * @param mixed  $user   Message utilisateur.
 * @param array  $schema JSON Schema.
 * @param array  $opts   Options (max_tokens…).
 * @return array|WP_Error Tableau décodé, ou WP_Error.
 */
function ag_ia_json( $system, $user, $schema, $opts = array() ) {
	$opts['output_config'] = array( 'format' => array( 'type' => 'json_schema', 'schema' => $schema ) );
	$txt = ag_ia_call( $system, $user, $opts );
	if ( is_wp_error( $txt ) ) {
		return $txt;
	}
	$out = json_decode( $txt, true );
	if ( ! is_array( $out ) ) {
		return new WP_Error( 'ag_ia_parse', 'Réponse IA illisible.' );
	}
	return $out;
}

/**
 * Récupère le contenu texte lisible d'une URL (pour « refais mon site »,
 * « devis »…). Utilise wp_remote_get puis nettoie le HTML.
 *
 * @param string $url  URL à lire.
 * @param int    $max  Longueur max du texte extrait.
 * @return array|WP_Error  array( title, text, url ) ou WP_Error.
 */
function ag_ia_fetch_page( $url, $max = 6000 ) {
	$url = esc_url_raw( trim( (string) $url ) );
	if ( '' === $url || ! preg_match( '#^https?://#i', $url ) ) {
		return new WP_Error( 'ag_ia_url', 'Adresse du site invalide.' );
	}
	$res = wp_remote_get( $url, array(
		'timeout'     => 20,
		'redirection' => 3,
		'user-agent'  => 'Mozilla/5.0 (compatible; AllianceGroupeBot/1.0)',
	) );
	if ( is_wp_error( $res ) ) {
		return $res;
	}
	$code = (int) wp_remote_retrieve_response_code( $res );
	if ( $code >= 400 ) {
		return new WP_Error( 'ag_ia_fetch', 'Site injoignable (HTTP ' . $code . ').' );
	}
	$html  = (string) wp_remote_retrieve_body( $res );
	$title = '';
	if ( preg_match( '#<title[^>]*>(.*?)</title>#is', $html, $m ) ) {
		$title = trim( html_entity_decode( wp_strip_all_tags( $m[1] ), ENT_QUOTES, 'UTF-8' ) );
	}
	// Retire script/style puis balises.
	$body = preg_replace( '#<(script|style|noscript)[^>]*>.*?</\1>#is', ' ', $html );
	$text = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( (string) $body ) ) );
	if ( function_exists( 'mb_substr' ) ) {
		$text = mb_substr( $text, 0, $max );
	} else {
		$text = substr( $text, 0, $max );
	}
	return array( 'title' => $title, 'text' => $text, 'url' => $url );
}
