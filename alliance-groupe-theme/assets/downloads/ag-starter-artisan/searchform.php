<?php
/**
 * The search form template.
 *
 * @package AG_Starter_Artisan
 */

?>
<form role="search" method="get" class="ag-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label>
		<span class="screen-reader-text"><?php esc_html_e( 'Rechercher :', 'ag-starter-artisan' ); ?></span>
		<input type="search" class="ag-search-field" placeholder="<?php esc_attr_e( 'Rechercher...', 'ag-starter-artisan' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s">
	</label>
	<input type="submit" class="ag-search-submit" value="<?php echo esc_attr_x( 'OK', 'submit button', 'ag-starter-artisan' ); ?>">
</form>
