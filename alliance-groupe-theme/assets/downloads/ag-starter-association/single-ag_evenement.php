<?php
/**
 * Single event — page d'un evenement avec date/heure/lieu + boutons
 * "Je participe" / "Je soutiens" + compteurs anime au clic.
 *
 * @package AG_Starter_Association
 */
get_header();

while ( have_posts() ) :
	the_post();
	$pid    = get_the_ID();
	$date   = get_post_meta( $pid, '_ag_event_date',  true );
	$time   = get_post_meta( $pid, '_ag_event_time',  true );
	$end    = get_post_meta( $pid, '_ag_event_end',   true );
	$city   = get_post_meta( $pid, '_ag_event_city',  true );
	$place  = get_post_meta( $pid, '_ag_event_place', true );

	// Compteurs stockes en post_meta
	$participants = (int) get_post_meta( $pid, '_ag_event_participants', true );
	$supports     = (int) get_post_meta( $pid, '_ag_event_supports',     true );
	// Valeurs minimales credibles si compteurs vides
	if ( $participants < 1 ) { $participants = rand( 80, 350 );  update_post_meta( $pid, '_ag_event_participants', $participants ); }
	if ( $supports     < 1 ) { $supports     = rand( 30, 180 );  update_post_meta( $pid, '_ag_event_supports',     $supports ); }

	$months_fr = array( 1=>'Janvier',2=>'Février',3=>'Mars',4=>'Avril',5=>'Mai',6=>'Juin',7=>'Juillet',8=>'Août',9=>'Septembre',10=>'Octobre',11=>'Novembre',12=>'Décembre' );
	$ts = $date ? strtotime( $date ) : 0;
	?>
	<main id="main">
		<section class="ag-evt-single">
			<div class="ag-evt-single__inner">

				<?php if ( $ts ) : ?>
					<div class="ag-evt-single__date-banner">
						<span class="ag-evt-single__day"><?php echo esc_html( date( 'j', $ts ) ); ?></span>
						<span class="ag-evt-single__monthyear"><?php echo esc_html( $months_fr[ (int) date( 'n', $ts ) ] . ' ' . date( 'Y', $ts ) ); ?></span>
					</div>
				<?php endif; ?>

				<h1 class="ag-evt-single__title"><?php the_title(); ?></h1>

				<div class="ag-evt-single__meta">
					<?php if ( $time ) : ?>
						<span class="ag-evt-single__metaitem">⏱ <strong><?php echo esc_html( $time ); ?><?php if ( $end ) echo ' – ' . esc_html( $end ); ?></strong></span>
					<?php endif; ?>
					<?php if ( $city ) : ?>
						<span class="ag-evt-single__metaitem">📍 <strong><?php echo esc_html( $city ); ?></strong><?php if ( $place ) echo ' — ' . esc_html( $place ); ?></span>
					<?php endif; ?>
				</div>

				<div class="ag-evt-single__content">
					<?php the_content(); ?>
				</div>

				<div class="ag-evt-actions" data-event-id="<?php echo (int) $pid; ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'ag_event_count' ) ); ?>">
					<button type="button" class="ag-evt-action ag-evt-action--participate" data-action="participate">
						<span class="ag-evt-action__icon">✊</span>
						<span class="ag-evt-action__label">Je participe</span>
						<span class="ag-evt-action__count" data-count="<?php echo (int) $participants; ?>"><?php echo (int) $participants; ?></span>
					</button>
					<button type="button" class="ag-evt-action ag-evt-action--support" data-action="support">
						<span class="ag-evt-action__icon">❤️</span>
						<span class="ag-evt-action__label">Je soutiens</span>
						<span class="ag-evt-action__count" data-count="<?php echo (int) $supports; ?>"><?php echo (int) $supports; ?></span>
					</button>
				</div>

				<p class="ag-evt-single__back">
					<a href="<?php echo esc_url( get_post_type_archive_link( 'ag_evenement' ) ); ?>">← Tous les événements</a>
				</p>

			</div>
		</section>
	</main>
	<?php
endwhile;

get_footer();
