<?php
/**
 * The template for displaying comments.
 *
 * @package AG_Starter_Restaurant
 */

if ( post_password_required() ) {
	return;
}
?>

<div id="comments" class="ag-comments">

	<?php if ( have_comments() ) : ?>
		<h2>
			<?php
			$ag_comment_count = get_comments_number();
			if ( '1' === $ag_comment_count ) {
				esc_html_e( '1 commentaire', 'ag-starter-restaurant' );
			} else {
				/* translators: %s: number of comments. */
				printf( esc_html( _n( '%s commentaire', '%s commentaires', $ag_comment_count, 'ag-starter-restaurant' ) ), esc_html( number_format_i18n( $ag_comment_count ) ) );
			}
			?>
		</h2>

		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'short_ping'  => true,
					'avatar_size' => 48,
				)
			);
			?>
		</ol>

		<?php
		the_comments_pagination(
			array(
				'prev_text' => esc_html__( 'Precedent', 'ag-starter-restaurant' ),
				'next_text' => esc_html__( 'Suivant', 'ag-starter-restaurant' ),
			)
		);

		if ( ! comments_open() ) :
			?>
			<p><?php esc_html_e( 'Les commentaires sont fermes.', 'ag-starter-restaurant' ); ?></p>
			<?php
		endif;

	endif;

	comment_form();
	?>

</div>
