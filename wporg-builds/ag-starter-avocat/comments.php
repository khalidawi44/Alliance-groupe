<?php
/**
 * The template for displaying comments.
 *
 * @package AG_Starter_Avocat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( post_password_required() ) {
	return;
}
?>

<div id="comments" class="ag-comments">

	<?php if ( have_comments() ) : ?>
		<h2 class="ag-comments__title">
			<?php
			$ag_comment_count = get_comments_number();
			printf(
				/* translators: %s: comment count number. */
				esc_html( _n( '%s commentaire', '%s commentaires', $ag_comment_count, 'ag-starter-avocat' ) ),
				esc_html( number_format_i18n( $ag_comment_count ) )
			);
			?>
		</h2>

		<ol class="ag-comments__list">
			<?php
			wp_list_comments(
				array(
					'style'      => 'ol',
					'short_ping' => true,
					'avatar_size' => 48,
				)
			);
			?>
		</ol>

		<?php
		the_comments_pagination(
			array(
				'prev_text' => esc_html__( 'Commentaires precedents', 'ag-starter-avocat' ),
				'next_text' => esc_html__( 'Commentaires suivants', 'ag-starter-avocat' ),
			)
		);

		if ( ! comments_open() ) :
			?>
			<p class="ag-comments__closed"><?php esc_html_e( 'Les commentaires sont fermes.', 'ag-starter-avocat' ); ?></p>
			<?php
		endif;

	endif;

	comment_form();
	?>

</div>
