<?php
/**
 * AG — Réglage des images « sécurité » du site (sans code).
 *
 * La pop-up « un piratage ressemble à ça » et le hero du parcours sécurité
 * utilisent des images codées en dur (option de secours sinon fichier du thème).
 * Ici : une page d'admin pour les remplacer facilement par une image de la
 * Médiathèque — pour adoucir le visuel cyber « qui fait peur ».
 *
 * Options pilotées : ag_tester_img_menace, ag_tester_img_resilience.
 * + Interrupteur ag_menace_pop_off pour masquer complètement la pop-up piratage.
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', function () {
	add_submenu_page(
		'options-general.php',
		'Images sécurité',
		'🛡️ Images sécurité',
		'manage_options',
		'ag-images-secu',
		'ag_images_secu_page'
	);
} );

/** Charge le sélecteur de médias WordPress sur notre page. */
add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( 'settings_page_ag-images-secu' === $hook ) {
		wp_enqueue_media();
	}
} );

function ag_images_secu_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( isset( $_POST['ag_images_secu_save'] ) && check_admin_referer( 'ag_images_secu' ) ) {
		update_option( 'ag_tester_img_menace', esc_url_raw( wp_unslash( $_POST['ag_tester_img_menace'] ?? '' ) ) );
		update_option( 'ag_tester_img_resilience', esc_url_raw( wp_unslash( $_POST['ag_tester_img_resilience'] ?? '' ) ) );
		update_option( 'ag_menace_pop_off', empty( $_POST['ag_menace_pop_off'] ) ? 0 : 1 );
		echo '<div class="notice notice-success is-dismissible"><p>Images enregistrées. Pense à purger le cache si besoin.</p></div>';
	}

	$menace = (string) get_option( 'ag_tester_img_menace', '' );
	$resi   = (string) get_option( 'ag_tester_img_resilience', '' );
	$off    = (int) get_option( 'ag_menace_pop_off', 0 );

	$rows = array(
		array( 'ag_tester_img_menace', 'Pop-up « piratage » (mur plein écran)', $menace, 'Image de fond de la pop-up cyber sur l\'accueil. Laisse vide pour aucune image (juste l\'animation).' ),
		array( 'ag_tester_img_resilience', 'Hero du parcours sécurité', $resi, 'Grande image de la section sécurité sur l\'accueil.' ),
	);

	echo '<div class="wrap"><h1>🛡️ Images sécurité</h1>';
	echo '<p>Adoucis le visuel cyber : 1) <strong>Médias → Ajouter</strong> ton image, 2) reviens ici, 3) clique « Choisir une image » (ou colle l\'URL), 4) Enregistre.</p>';
	echo '<form method="post">';
	wp_nonce_field( 'ag_images_secu' );
	echo '<table class="form-table"><tbody>';
	foreach ( $rows as $r ) {
		list( $opt, $label, $val, $help ) = $r;
		echo '<tr><th><label for="' . esc_attr( $opt ) . '">' . esc_html( $label ) . '</label></th><td>';
		echo '<input type="url" id="' . esc_attr( $opt ) . '" name="' . esc_attr( $opt ) . '" value="' . esc_attr( $val ) . '" class="large-text code ag-img-field" placeholder="https://…/wp-content/uploads/…jpg"> ';
		echo '<button type="button" class="button ag-img-pick" data-target="' . esc_attr( $opt ) . '">Choisir une image</button>';
		echo '<p class="description">' . esc_html( $help ) . '</p>';
		echo '<div class="ag-img-prev" data-for="' . esc_attr( $opt ) . '">' . ( $val ? '<img src="' . esc_url( $val ) . '" style="max-height:90px;margin-top:8px;border-radius:6px;border:1px solid #ddd;">' : '' ) . '</div>';
		echo '</td></tr>';
	}
	echo '<tr><th>Pop-up piratage</th><td><label><input type="checkbox" name="ag_menace_pop_off" value="1"' . checked( $off, 1, false ) . '> Masquer complètement la pop-up « un piratage ressemble à ça » (si elle fait trop peur).</label></td></tr>';
	echo '</tbody></table>';
	submit_button( 'Enregistrer', 'primary', 'ag_images_secu_save' );
	echo '</form></div>';
	?>
	<script>
	jQuery(function($){
		$('.ag-img-pick').on('click', function(e){
			e.preventDefault();
			var target = $(this).data('target');
			var frame = wp.media({ title:'Choisir une image', button:{text:'Utiliser cette image'}, multiple:false });
			frame.on('select', function(){
				var url = frame.state().get('selection').first().toJSON().url;
				$('#'+target).val(url);
				$('.ag-img-prev[data-for="'+target+'"]').html('<img src="'+url+'" style="max-height:90px;margin-top:8px;border-radius:6px;border:1px solid #ddd;">');
			});
			frame.open();
		});
	});
	</script>
	<?php
}
