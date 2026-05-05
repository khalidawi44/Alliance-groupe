<?php
/**
 * Archive Evenements (CPT ag_evenement) — rend directement le calendrier
 * + cartes date/heure stylees, sans dependre du shortcode.
 *
 * @package AG_Starter_Association
 */
get_header();
?>
<main id="main">
    <section class="ag-asso-section" style="padding-top:60px;">
        <div class="ag-asso-container">
            <h1 class="ag-asso-section__title">Nos <em>événements</em></h1>
            <p class="ag-asso-section__lead">Marches, meetings, assemblées générales, ateliers — tous nos rendez-vous à venir.</p>

            <?php
            $q = new WP_Query( array(
                'post_type'      => 'ag_evenement',
                'posts_per_page' => 50,
                'meta_key'       => '_ag_event_date',
                'orderby'        => 'meta_value',
                'order'          => 'ASC',
            ) );
            $months_fr   = array( 1=>'Janvier',2=>'Février',3=>'Mars',4=>'Avril',5=>'Mai',6=>'Juin',7=>'Juillet',8=>'Août',9=>'Septembre',10=>'Octobre',11=>'Novembre',12=>'Décembre' );
            $months_short= array( 1=>'JAN',2=>'FÉV',3=>'MAR',4=>'AVR',5=>'MAI',6=>'JUIN',7=>'JUIL',8=>'AOÛT',9=>'SEPT',10=>'OCT',11=>'NOV',12=>'DÉC' );
            $dow_fr      = array( 1=>'LUN',2=>'MAR',3=>'MER',4=>'JEU',5=>'VEN',6=>'SAM',7=>'DIM' );

            $events_by_date = array();
            while ( $q->have_posts() ) {
                $q->the_post();
                $date = get_post_meta( get_the_ID(), '_ag_event_date', true );
                if ( ! $date ) $date = date( 'Y-m-d', strtotime( get_the_date( 'Y-m-d' ) ) );
                $events_by_date[ $date ][] = array(
                    'title' => get_the_title(),
                    'time'  => get_post_meta( get_the_ID(), '_ag_event_time',  true ),
                    'end'   => get_post_meta( get_the_ID(), '_ag_event_end',   true ),
                    'city'  => get_post_meta( get_the_ID(), '_ag_event_city',  true ),
                    'place' => get_post_meta( get_the_ID(), '_ag_event_place', true ),
                    'desc'  => get_the_excerpt(),
                    'url'   => get_permalink(),
                );
            }
            wp_reset_postdata();
            ksort( $events_by_date );

            $now    = current_time( 'timestamp' );
            $year   = (int) date( 'Y', $now );
            $month  = (int) date( 'n', $now );
            $first  = mktime( 0, 0, 0, $month, 1, $year );
            $nb_days = (int) date( 't', $first );
            $first_dow = (int) date( 'N', $first );
            $today_d   = (int) date( 'j', $now );
            ?>
            <div class="ag-evt-wrap">
                <aside class="ag-evt-calendar">
                    <div class="ag-evt-cal-header">
                        <h3><?php echo esc_html( $months_fr[ $month ] . ' ' . $year ); ?></h3>
                    </div>
                    <table class="ag-evt-cal-grid">
                        <thead><tr><th>L</th><th>M</th><th>M</th><th>J</th><th>V</th><th>S</th><th>D</th></tr></thead>
                        <tbody><tr>
                            <?php
                            for ( $i = 1; $i < $first_dow; $i++ ) echo '<td></td>';
                            for ( $d = 1; $d <= $nb_days; $d++ ) {
                                $datestr = sprintf( '%04d-%02d-%02d', $year, $month, $d );
                                $has_evt = isset( $events_by_date[ $datestr ] );
                                $is_today = ( $d === $today_d );
                                $cls = '';
                                if ( $has_evt )  $cls .= ' has-evt';
                                if ( $is_today ) $cls .= ' is-today';
                                echo '<td class="' . esc_attr( trim( $cls ) ) . '">';
                                if ( $has_evt ) {
                                    echo '<a href="#evt-' . esc_attr( $datestr ) . '">' . $d . '</a>';
                                } else {
                                    echo $d;
                                }
                                echo '</td>';
                                if ( ( $first_dow + $d - 1 ) % 7 === 0 && $d < $nb_days ) echo '</tr><tr>';
                            }
                            ?>
                        </tr></tbody>
                    </table>
                    <p class="ag-evt-cal-legend"><span class="ag-evt-dot"></span> Date avec événement</p>
                </aside>

                <div class="ag-evt-list">
                    <?php if ( empty( $events_by_date ) ) : ?>
                        <p>Aucun événement à venir pour le moment.</p>
                    <?php endif; ?>
                    <?php foreach ( $events_by_date as $date => $items ) :
                        $ts = strtotime( $date );
                        $jour   = (int) date( 'j', $ts );
                        $mois_n = (int) date( 'n', $ts );
                        $annee  = date( 'Y', $ts );
                        $dow    = $dow_fr[ (int) date( 'N', $ts ) ];
                        foreach ( $items as $ev ) : ?>
                            <article class="ag-evt-card" id="evt-<?php echo esc_attr( $date ); ?>">
                                <div class="ag-evt-date">
                                    <span class="ag-evt-date__dow"><?php echo esc_html( $dow ); ?></span>
                                    <span class="ag-evt-date__day"><?php echo esc_html( $jour ); ?></span>
                                    <span class="ag-evt-date__month"><?php echo esc_html( $months_short[ $mois_n ] ); ?></span>
                                    <span class="ag-evt-date__year"><?php echo esc_html( $annee ); ?></span>
                                </div>
                                <div class="ag-evt-body">
                                    <div class="ag-evt-meta">
                                        <?php if ( $ev['time'] ) : ?>
                                            <span class="ag-evt-meta__time">⏱ <?php echo esc_html( $ev['time'] ); ?><?php if ( $ev['end'] ) echo ' – ' . esc_html( $ev['end'] ); ?></span>
                                        <?php endif; ?>
                                        <?php if ( $ev['city'] ) : ?>
                                            <span class="ag-evt-meta__loc">📍 <?php echo esc_html( $ev['city'] ); ?><?php if ( $ev['place'] ) echo ' — ' . esc_html( $ev['place'] ); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <h3 class="ag-evt-title"><a href="<?php echo esc_url( $ev['url'] ); ?>"><?php echo esc_html( $ev['title'] ); ?></a></h3>
                                    <p class="ag-evt-desc"><?php echo wp_kses_post( $ev['desc'] ); ?></p>
                                    <a class="ag-evt-cta" href="<?php echo esc_url( $ev['url'] ); ?>">M'inscrire / en savoir plus →</a>
                                </div>
                            </article>
                        <?php endforeach;
                    endforeach; ?>
                </div>
            </div>
        </div>
    </section>
</main>
<?php get_footer(); ?>
