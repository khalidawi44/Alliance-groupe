<?php
/**
 * Single domaine d'expertise template.
 *
 * @package AG_Starter_Avocat
 */

get_header();
?>

<main id="ag-main" class="ag-main" role="main">
	<div class="ag-container ag-container--narrow">

		<?php
		while ( have_posts() ) :
			the_post();
			$icon     = get_post_meta( get_the_ID(), '_ag_domaine_icon', true );
			$examples = get_post_meta( get_the_ID(), '_ag_domaine_examples', true );
			?>
			<article <?php post_class( 'ag-domaine-single' ); ?>>
				<header style="text-align:center;padding:2rem 0;border-bottom:1px solid #1f2740;margin-bottom:2rem;">
					<div style="font-size:3rem;margin-bottom:0.5rem;"><?php echo esc_html( $icon ? $icon : '⚖️' ); ?></div>
					<p style="color:#c9a96e;font-size:0.85rem;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.5rem;">
						<?php esc_html_e( 'Domaine d\'expertise', 'ag-starter-avocat' ); ?>
					</p>
					<h1 class="ag-entry-title" style="font-size:2rem;"><?php the_title(); ?></h1>
				</header>

				<?php if ( has_post_thumbnail() ) : ?>
					<div class="ag-entry-thumb" style="margin-bottom:1.5rem;">
						<?php the_post_thumbnail( 'large', array( 'style' => 'border-radius:6px;' ) ); ?>
					</div>
				<?php endif; ?>

				<div class="ag-entry-content">
					<?php the_content(); ?>
				</div>

				<?php if ( $examples ) : ?>
					<aside style="margin-top:2.5rem;padding:1.75rem;background:#131826;border:1px solid #1f2740;border-left:3px solid #c9a96e;border-radius:4px;">
						<h2 style="color:#c9a96e;font-size:1.1rem;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:0.75rem;">
							<?php esc_html_e( 'Exemples de cas traités', 'ag-starter-avocat' ); ?>
						</h2>
						<ul style="list-style:none;padding:0;margin:0;">
							<?php foreach ( array_filter( array_map( 'trim', explode( "\n", $examples ) ) ) as $ex ) : ?>
								<li style="padding:0.5rem 0 0.5rem 1.25rem;color:#cccccc;position:relative;border-bottom:1px solid rgba(31,39,64,0.5);">
									<span style="position:absolute;left:0;color:#c9a96e;font-weight:700;">›</span>
									<?php echo esc_html( $ex ); ?>
								</li>
							<?php endforeach; ?>
						</ul>
					</aside>
				<?php endif; ?>

				<div style="text-align:center;margin-top:2.5rem;padding-top:2rem;border-top:1px solid #1f2740;">
					<p style="color:#aaaaaa;margin-bottom:1rem;">
						<?php esc_html_e( 'Vous avez un dossier dans ce domaine ?', 'ag-starter-avocat' ); ?>
					</p>
					<a href="<?php echo esc_url( home_url( '/#ag-rdv' ) ); ?>" class="ag-btn">
						<?php esc_html_e( 'Prendre rendez-vous →', 'ag-starter-avocat' ); ?>
					</a>
				</div>
			</article>
		<?php endwhile; ?>

		<nav style="margin-top:2.5rem;padding-top:1.5rem;border-top:1px solid #1f2740;text-align:center;">
			<a href="<?php echo esc_url( home_url( '/#ag-domaines' ) ); ?>" style="color:#c9a96e;text-decoration:none;font-weight:700;">
				← <?php esc_html_e( 'Tous les domaines d\'expertise', 'ag-starter-avocat' ); ?>
			</a>
		</nav>
	</div>
</main>

<?php
get_footer();
