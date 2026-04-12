<?php
/**
 * The sidebar containing the main widget area.
 *
 * @package AG_Starter_Restaurant
 */

if ( ! is_active_sidebar( 'sidebar-1' ) ) {
	return;
}
?>

<aside id="ag-sidebar" class="ag-sidebar" role="complementary" aria-label="<?php esc_attr_e( 'Barre laterale', 'ag-starter-restaurant' ); ?>">
	<div class="ag-container">
		<?php dynamic_sidebar( 'sidebar-1' ); ?>
	</div>
</aside>
