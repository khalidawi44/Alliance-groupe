<?php
/**
 * Email sender for licence delivery.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class AG_Licence_Email {

    /**
     * Send the licence key to the buyer.
     *
     * @param string $email     Recipient.
     * @param string $clear_key The clear-text licence key.
     * @param string $tier      pro|premium|business
     */
    public static function send_licence( $email, $clear_key, $tier ) {
        $tier_labels = array(
            'pro'      => 'Pack Pro (49 €)',
            'business' => 'Pack Business (149 €)',
        );
        $label = isset( $tier_labels[ $tier ] ) ? $tier_labels[ $tier ] : ucfirst( $tier );

        $subject = 'Votre clé de licence AG Starter — ' . $label;

        $body = "Bonjour,\n\n"
              . "Merci pour votre achat ! Voici votre clé de licence pour le " . $label . " :\n\n"
              . "═══════════════════════════════════════\n"
              . "  " . $clear_key . "\n"
              . "═══════════════════════════════════════\n\n"
              . "COMMENT ACTIVER VOTRE LICENCE :\n\n"
              . "1. Connectez-vous à votre WordPress (wp-admin)\n"
              . "2. Allez dans Apparence → Licence AG Starter\n"
              . "3. Collez la clé ci-dessus dans le champ \"Clé de licence\"\n"
              . "4. Cliquez sur \"Activer\"\n"
              . "5. WordPress vous proposera automatiquement la mise à jour vers la version Pro\n"
              . "6. Cliquez sur \"Mettre à jour\" — c'est fait !\n\n"
              . "IMPORTANT :\n"
              . "- Cette clé est valable pour 1 site (1 domaine)\n"
              . "- Conservez cet email précieusement\n"
              . "- Pour changer de domaine, désactivez d'abord la licence puis réactivez-la sur le nouveau site\n\n"
              . "BESOIN D'AIDE ?\n"
              . "Répondez simplement à cet email ou contactez-nous :\n"
              . "- Email : contact@alliancegroupe-inc.com\n"
              . "- Téléphone : 06.23.52.60.74\n\n"
              . "À très vite,\n"
              . "L'équipe Alliance Groupe\n"
              . "https://alliancegroupe-inc.com\n";

        $headers = array( 'From: Alliance Groupe <contact@alliancegroupe-inc.com>' );

        wp_mail( $email, $subject, $body, $headers );
    }
}
