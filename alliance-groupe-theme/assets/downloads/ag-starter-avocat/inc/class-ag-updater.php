<?php
/**
 * Auto-updater: checks the AG licence API for Pro theme updates
 * and injects them into the WordPress update system.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class AG_Theme_Updater {

    /** @var string Theme slug (e.g. 'ag-starter-restaurant'). */
    private $slug;

    /** @var string Current theme version. */
    private $version;

    public function __construct( $slug, $version ) {
        $this->slug    = $slug;
        $this->version = $version;

        add_filter( 'pre_set_site_transient_update_themes', array( $this, 'check_update' ) );
        add_filter( 'themes_api', array( $this, 'theme_info' ), 20, 3 );
    }

    /**
     * Inject update info into the WordPress update transient.
     */
    public function check_update( $transient ) {
        if ( empty( $transient->checked ) ) {
            return $transient;
        }

        // Use cached result (12h)
        $cache_key = 'ag_update_' . $this->slug;
        $cached    = get_transient( $cache_key );

        if ( false === $cached ) {
            $cached = $this->remote_check();
            if ( $cached ) {
                set_transient( $cache_key, $cached, 12 * HOUR_IN_SECONDS );
            }
        }

        if ( $cached && ! empty( $cached['update_available'] ) && ! empty( $cached['new_version'] ) ) {
            if ( version_compare( $this->version, $cached['new_version'], '<' ) ) {
                $update = array(
                    'theme'       => $this->slug,
                    'new_version' => $cached['new_version'],
                    'url'         => 'https://alliancegroupe-inc.com/templates-wordpress',
                    'requires'    => $cached['requires'] ?? '6.0',
                    'requires_php'=> $cached['requires_php'] ?? '7.4',
                );

                if ( ! empty( $cached['download_url'] ) ) {
                    $update['package'] = $cached['download_url'];
                }

                $transient->response[ $this->slug ] = $update;
            }
        }

        return $transient;
    }

    /**
     * Provide theme details for the "View version details" modal.
     */
    public function theme_info( $result, $action, $args ) {
        if ( 'theme_information' !== $action ) return $result;
        if ( ! isset( $args->slug ) || $args->slug !== $this->slug ) return $result;

        $cache_key = 'ag_update_' . $this->slug;
        $cached    = get_transient( $cache_key );
        if ( ! $cached || empty( $cached['new_version'] ) ) return $result;

        $info = new stdClass();
        $info->name        = wp_get_theme( $this->slug )->get( 'Name' );
        $info->slug        = $this->slug;
        $info->version     = $cached['new_version'];
        $info->author      = '<a href="https://alliancegroupe-inc.com">Alliance Groupe</a>';
        $info->homepage    = 'https://alliancegroupe-inc.com/templates-wordpress';
        $info->requires    = $cached['requires'] ?? '6.0';
        $info->requires_php= $cached['requires_php'] ?? '7.4';
        $info->sections    = array(
            'description' => 'Version Premium avec fonctionnalités avancées : layouts supplémentaires, header variants, footer personnalisable, animations, polices premium.',
            'changelog'   => 'Mise a jour vers la version Premium. Activez votre clé de licence dans Apparence → Licence AG pour télécharger.',
        );

        if ( ! empty( $cached['download_url'] ) ) {
            $info->download_link = $cached['download_url'];
        }

        return $info;
    }

    /**
     * Call the remote update-check API.
     */
    private function remote_check() {
        $key    = class_exists( 'AG_Licence_Client' ) ? AG_Licence_Client::get_key() : '';
        $domain = class_exists( 'AG_Licence_Client' ) ? AG_Licence_Client::get_domain() : '';

        $url = add_query_arg( array(
            'theme_slug'      => $this->slug,
            'current_version' => $this->version,
            'licence_key'     => $key,
            'domain'          => $domain,
        ), AG_Licence_Client::API_URL . '/update-check' );

        $resp = wp_remote_get( $url, array( 'timeout' => 15 ) );
        if ( is_wp_error( $resp ) ) return null;

        $body = json_decode( wp_remote_retrieve_body( $resp ), true );
        return is_array( $body ) ? $body : null;
    }
}
