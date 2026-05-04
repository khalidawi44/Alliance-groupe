<?php
/**
 * Shortcodes affichés sur les pages séparées (créées par AG_Fid_Pages).
 * Chaque shortcode rend la liste/le formulaire correspondant.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AG_Fid_Shortcodes {
	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) self::$instance = new self();
		return self::$instance;
	}
	private function __construct() {
		add_shortcode( 'ag_fid_combats',     array( $this, 'render_combats' ) );
		add_shortcode( 'ag_fid_evenements',  array( $this, 'render_evenements' ) );
		add_shortcode( 'ag_fid_groupes',     array( $this, 'render_groupes' ) );
		add_shortcode( 'ag_fid_actu',        array( $this, 'render_actu' ) );
		add_shortcode( 'ag_fid_petitions',   array( $this, 'render_petitions' ) );
		add_shortcode( 'ag_fid_signer',      array( $this, 'render_signer' ) );
		add_shortcode( 'ag_fid_don',         array( $this, 'render_don' ) );
		add_shortcode( 'ag_fid_adhesion',    array( $this, 'render_adhesion' ) );
		add_shortcode( 'ag_fid_compte',      array( $this, 'render_compte' ) );
		add_shortcode( 'ag_fid_qui_sommes_nous', array( $this, 'render_about' ) );
		add_shortcode( 'ag_fid_manifeste',   array( $this, 'render_manifeste' ) );
		add_shortcode( 'ag_fid_mentions',    array( $this, 'render_mentions' ) );
		add_shortcode( 'ag_fid_rgpd',        array( $this, 'render_rgpd' ) );
	}

	private function render_cpt_grid( $cpt, $empty_msg ) {
		$q = new WP_Query( array( 'post_type' => $cpt, 'posts_per_page' => 30 ) );
		if ( ! $q->have_posts() ) {
			return '<p>' . esc_html( $empty_msg ) . '</p>';
		}
		ob_start();
		echo '<div class="ag-fid-grid">';
		while ( $q->have_posts() ) {
			$q->the_post();
			?>
			<article class="ag-fid-card">
				<?php if ( has_post_thumbnail() ) : ?>
					<div class="ag-fid-card__img"><?php the_post_thumbnail( 'medium' ); ?></div>
				<?php endif; ?>
				<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
				<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?></p>
			</article>
			<?php
		}
		echo '</div>';
		wp_reset_postdata();
		return ob_get_clean();
	}

	public function render_combats()    { return $this->render_cpt_grid( 'ag_combat', 'Aucun combat publié.' ); }
	public function render_evenements() {
		$q = new WP_Query( array(
			'post_type'      => 'ag_evenement',
			'posts_per_page' => 30,
			'meta_key'       => '_ag_event_date',
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
		) );
		if ( ! $q->have_posts() ) {
			return '<p>Aucun événement à venir.</p>';
		}
		// Calendrier mensuel (mois en cours + suivant)
		$months_fr = array( 1=>'Janvier',2=>'Février',3=>'Mars',4=>'Avril',5=>'Mai',6=>'Juin',7=>'Juillet',8=>'Août',9=>'Septembre',10=>'Octobre',11=>'Novembre',12=>'Décembre' );
		$months_short = array( 1=>'JAN',2=>'FÉV',3=>'MAR',4=>'AVR',5=>'MAI',6=>'JUIN',7=>'JUIL',8=>'AOÛT',9=>'SEPT',10=>'OCT',11=>'NOV',12=>'DÉC' );

		// Group events by date
		$events_by_date = array();
		while ( $q->have_posts() ) {
			$q->the_post();
			$date  = get_post_meta( get_the_ID(), '_ag_event_date',  true );
			$time  = get_post_meta( get_the_ID(), '_ag_event_time',  true );
			$end   = get_post_meta( get_the_ID(), '_ag_event_end',   true );
			$city  = get_post_meta( get_the_ID(), '_ag_event_city',  true );
			$place = get_post_meta( get_the_ID(), '_ag_event_place', true );
			if ( ! $date ) continue;
			$events_by_date[ $date ][] = array(
				'id'    => get_the_ID(),
				'title' => get_the_title(),
				'time'  => $time,
				'end'   => $end,
				'city'  => $city,
				'place' => $place,
				'desc'  => get_the_excerpt(),
				'url'   => get_permalink(),
			);
		}
		wp_reset_postdata();
		ksort( $events_by_date );

		ob_start(); ?>
		<div class="ag-evt-wrap">
			<?php
			// Mini-calendrier du mois en cours avec dates surlignees
			$now    = current_time( 'timestamp' );
			$year   = (int) date( 'Y', $now );
			$month  = (int) date( 'n', $now );
			$first  = mktime( 0, 0, 0, $month, 1, $year );
			$nb_days = (int) date( 't', $first );
			$first_dow = (int) date( 'N', $first ); // 1 = lundi
			$today_d   = (int) date( 'j', $now );
			?>
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
				<?php foreach ( $events_by_date as $date => $items ) :
					$ts = strtotime( $date );
					$jour    = (int) date( 'j', $ts );
					$mois_n  = (int) date( 'n', $ts );
					$annee   = date( 'Y', $ts );
					$jour_lbl = strtoupper( substr( $months_fr[ (int) date( 'N', $ts ) <= 7 ? (int) date( 'N', $ts ) : 1 ], 0, 3 ) );
					$dow_fr = array( 1=>'LUN',2=>'MAR',3=>'MER',4=>'JEU',5=>'VEN',6=>'SAM',7=>'DIM' );
					$dow = $dow_fr[ (int) date( 'N', $ts ) ];
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
		<?php
		return ob_get_clean();
	}
	public function render_groupes()    { return $this->render_cpt_grid( 'ag_groupe', 'Aucun groupe local référencé.' ); }
	public function render_petitions()  { return $this->render_cpt_grid( 'ag_petition', 'Aucune pétition active.' ); }
	public function render_actu()       { return $this->render_cpt_grid( 'post', 'Aucun article.' ); }

	public function render_manifeste() {
		ob_start(); ?>
		<div class="ag-fid-manifeste-text">
			<p class="ag-fid-lead"><strong>Nous sommes des citoyennes et des citoyens.</strong> Pas un parti. Pas un syndicat. Un mouvement, ouvert et indépendant, qui croit qu'une autre société est possible — plus juste, plus écologique, plus démocratique.</p>

			<h2>Notre constat</h2>
			<p>La République promet l'égalité. Elle livre la précarité. Le mérite y est devenu un mirage : la naissance pèse plus que le travail. Les services publics, qui faisaient la fierté du pays, sont méthodiquement démantelés. Le climat se dérègle, et les solutions arrivent par décret au lieu de naître du débat.</p>

			<h2>Nos principes</h2>
			<ul>
				<li><strong>Indépendance.</strong> Aucun parti, aucun lobby, aucun grand donateur. Nous vivons des cotisations et des dons des adhérents.</li>
				<li><strong>Démocratie interne.</strong> Toutes les décisions importantes sont votées en assemblée générale. Les statuts sont publics, les comptes aussi.</li>
				<li><strong>Action concrète.</strong> Nous menons des campagnes thématiques mesurables, avec des objectifs chiffrés et des bilans publics.</li>
				<li><strong>Bienveillance.</strong> Nous combattons les idées, jamais les personnes. Aucun racisme, aucun sexisme, aucune homophobie ne sont tolérés dans nos rangs.</li>
			</ul>

			<h2>Nos priorités 2026</h2>
			<ol>
				<li>Justice climatique pour tou·tes — y compris les plus modestes.</li>
				<li>Logement digne et abordable, partout, pour tout le monde.</li>
				<li>Refondation des services publics — santé, école, transports, énergie.</li>
				<li>Démocratie permanente — RIC, assemblées tirées au sort, transparence.</li>
				<li>Égalité réelle — femmes/hommes, origines, handicaps, orientations.</li>
				<li>Souveraineté citoyenne — sortir de l'emprise des géants du numérique.</li>
			</ol>

			<p class="ag-fid-cta-line">Vous partagez nos valeurs ? <a href="<?php echo esc_url( home_url( '/signer/' ) ); ?>">Signez l'appel</a> ou <a href="<?php echo esc_url( home_url( '/adherer/' ) ); ?>">adhérez au mouvement</a>.</p>
		</div>
		<?php
		return ob_get_clean();
	}

	public function render_about() {
		$team_bios = array(
			1 => 'Avocat en droit du travail à Paris depuis 12 ans, Yacine pilote toute notre veille juridique et accompagne les groupes locaux dans leurs actions en justice. Bénévole à 100%.',
			2 => 'Comptable agréée, Léa garantit la transparence financière du mouvement. Tous nos comptes sont publics et certifiés chaque année par un commissaire aux comptes.',
			3 => 'Documentaliste de profession, Mehdi tient à jour notre base de données militante (RGPD-compliant) et coordonne la rédaction des prises de position publiques.',
			4 => 'Coordinatrice salariée à mi-temps, Sophie est le point de contact des 47 groupes locaux : lancement, formation, soutien logistique, médiation interne.',
			5 => 'Graphiste indépendante reconvertie au militantisme, Thomas signe tous nos visuels de campagne. Ancien des Décodeurs du Monde.',
			6 => 'Étudiante en sciences politiques et porte-parole jeunes, Aïcha anime les actions universitaires et le réseau des jeunes engagé·es (650 membres).',
		);
		$colors = array( '#E10F1A', '#FFD23F', '#0A0A0D', '#1F8A3D', '#3B5998', '#8B1A8B' );
		ob_start(); ?>
		<div class="ag-asso-about">
			<header class="ag-asso-about__hero">
				<h1>Qui sommes-nous</h1>
				<p class="ag-asso-about__hero-sub">Un mouvement citoyen, indépendant des partis, financé par ses adhérents.</p>
			</header>

			<?php
			$pres_photo = get_theme_mod( 'ag_asso_about_president_photo', '' );
			$pres_name  = get_theme_mod( 'ag_asso_about_president_name', 'Camille Lefèvre' );
			$pres_role  = get_theme_mod( 'ag_asso_about_president_role', 'Présidente — fondatrice' );
			$pres_bio   = get_theme_mod( 'ag_asso_about_president_bio',  'Engagée dans l\'éducation populaire depuis 15 ans, Camille a fondé le mouvement en 2018 après avoir constaté l\'urgence d\'un cadre citoyen indépendant des partis. Ancienne enseignante en zone REP, elle porte une vision d\'une démocratie vivante, où chacune et chacun peut peser sur les décisions qui le concernent.' );
			$pres_initials = '';
			foreach ( explode( ' ', $pres_name ) as $part ) { if ( $part ) $pres_initials .= mb_strtoupper( mb_substr( $part, 0, 1 ) ); }
			?>
			<section class="ag-asso-president">
				<?php if ( $pres_photo ) : ?>
					<img class="ag-asso-president__photo" src="<?php echo esc_url( $pres_photo ); ?>" alt="<?php echo esc_attr( $pres_name ); ?>">
				<?php else : ?>
					<div class="ag-asso-president__photo--placeholder"><?php echo esc_html( $pres_initials ?: 'CL' ); ?></div>
				<?php endif; ?>
				<div class="ag-asso-president__body">
					<p class="ag-asso-president__role"><?php echo esc_html( $pres_role ); ?></p>
					<h2><?php echo esc_html( $pres_name ); ?></h2>
					<?php echo wpautop( esc_html( $pres_bio ) ); ?>
				</div>
			</section>

			<section class="ag-asso-histoire">
				<?php
				$histoire_fallback = array(
					1 => array( 'year' => '2018',         'title' => 'La naissance du mouvement', 'text' => 'Quelques dizaines de citoyennes et citoyens se réunissent dans une salle des fêtes de banlieue. Le constat est partagé : ni les partis, ni les institutions n\'écoutent plus. L\'association est déclarée en préfecture le 14 juillet 2018.' ),
					2 => array( 'year' => '2021',         'title' => 'Première grande victoire',  'text' => 'Après 18 mois de mobilisation et 47 000 signatures recueillies, notre pétition pour la transparence des marchés publics aboutit à une loi locale. Le mouvement compte désormais 12 groupes locaux dans toute la France.' ),
					3 => array( 'year' => 'Aujourd\'hui', 'title' => 'Un mouvement national',     'text' => 'Plus de 2 000 adhérents, 47 groupes locaux, 6 grandes campagnes thématiques en cours. Nous restons indépendants : aucun parti, aucun lobby — uniquement nos cotisations et vos dons.' ),
				);
				for ( $i = 1; $i <= 3; $i++ ) :
					$photo = get_theme_mod( "ag_asso_about_histoire_photo_$i", '' );
					$year  = get_theme_mod( "ag_asso_about_histoire_year_$i",  $histoire_fallback[ $i ]['year']  );
					$title = get_theme_mod( "ag_asso_about_histoire_title_$i", $histoire_fallback[ $i ]['title'] );
					$text  = get_theme_mod( "ag_asso_about_histoire_text_$i",  $histoire_fallback[ $i ]['text']  );
					?>
					<div class="ag-asso-histoire__step">
						<div class="ag-asso-histoire__photo<?php echo $photo ? '' : ' ag-asso-histoire__photo--placeholder'; ?>" <?php if ( $photo ) echo 'style="background-image:url(' . esc_url( $photo ) . ');"'; ?>>
							<?php if ( ! $photo ) : ?><span class="ag-asso-histoire__photo-year"><?php echo esc_html( $year ); ?></span><?php endif; ?>
						</div>
						<div class="ag-asso-histoire__body">
							<?php if ( $year ) : ?>
								<span class="ag-asso-histoire__year"><?php echo esc_html( $year ); ?></span>
							<?php endif; ?>
							<h3 class="ag-asso-histoire__title"><?php echo esc_html( $title ); ?></h3>
							<p class="ag-asso-histoire__text"><?php echo esc_html( $text ); ?></p>
						</div>
					</div>
				<?php endfor; ?>
			</section>

			<section class="ag-asso-team">
				<h2 class="ag-asso-section__title">Notre <em>équipe</em></h2>
				<p class="ag-asso-section__lead">Les bénévoles et salarié·es qui font vivre l'association au quotidien.</p>
				<div class="ag-asso-team__grid ag-asso-team__grid--detailed">
					<?php
					$team_fallback = array(
						1 => array( 'name' => 'Yacine Bouzid',   'role' => 'Vice-président — pôle juridique' ),
						2 => array( 'name' => 'Léa Marchand',    'role' => 'Trésorière' ),
						3 => array( 'name' => 'Mehdi El Amrani', 'role' => 'Secrétaire général' ),
						4 => array( 'name' => 'Sophie Tremblay', 'role' => 'Coordination groupes locaux' ),
						5 => array( 'name' => 'Thomas Vasseur',  'role' => 'Responsable communication' ),
						6 => array( 'name' => 'Aïcha Diallo',    'role' => 'Animation jeunes engagés' ),
					);
					for ( $i = 1; $i <= 6; $i++ ) :
						$photo = get_theme_mod( "ag_asso_about_team_photo_$i", '' );
						$name  = get_theme_mod( "ag_asso_about_team_name_$i", $team_fallback[ $i ]['name'] );
						$role  = get_theme_mod( "ag_asso_about_team_role_$i", $team_fallback[ $i ]['role'] );
						if ( ! $name && ! $photo ) continue;
						$initials = '';
						foreach ( explode( ' ', $name ) as $part ) { if ( $part ) $initials .= mb_strtoupper( mb_substr( $part, 0, 1 ) ); }
						$color = $colors[ ( $i - 1 ) % count( $colors ) ];
						?>
						<article class="ag-asso-team__card">
							<?php if ( $photo ) : ?>
								<img class="ag-asso-team__photo" src="<?php echo esc_url( $photo ); ?>" alt="<?php echo esc_attr( $name ); ?>">
							<?php else : ?>
								<div class="ag-asso-team__photo ag-asso-team__photo--placeholder" style="background:<?php echo esc_attr( $color ); ?>;">
									<span><?php echo esc_html( $initials ); ?></span>
								</div>
							<?php endif; ?>
							<h4 class="ag-asso-team__name"><?php echo esc_html( $name ); ?></h4>
							<p class="ag-asso-team__role"><?php echo esc_html( $role ); ?></p>
							<p class="ag-asso-team__bio"><?php echo esc_html( isset( $team_bios[ $i ] ) ? $team_bios[ $i ] : '' ); ?></p>
						</article>
					<?php endfor; ?>
				</div>
			</section>

			<section class="ag-asso-about__values">
				<div class="ag-asso-container">
					<h2 class="ag-asso-section__title">Nos <em>engagements</em></h2>
					<div class="ag-asso-values-grid">
						<div class="ag-asso-value">
							<span class="ag-asso-value__num">100%</span>
							<h4>Indépendance</h4>
							<p>Aucun parti, aucun lobby. Financement exclusivement par cotisations et dons d'adhérent·es. Plafond de don à 1 500€/an pour éviter les dépendances.</p>
						</div>
						<div class="ag-asso-value">
							<span class="ag-asso-value__num">∞</span>
							<h4>Transparence</h4>
							<p>Comptes certifiés publiés chaque année. Liste des donateurs publics consultable en ligne. PV d'AG accessibles à tous les adhérents.</p>
						</div>
						<div class="ag-asso-value">
							<span class="ag-asso-value__num">1p=1v</span>
							<h4>Démocratie interne</h4>
							<p>Une personne, une voix. Aucune voix double, aucun droit de veto. Toutes les décisions stratégiques sont validées en AG.</p>
						</div>
					</div>
				</div>
			</section>
		</div>
		<?php
		return ob_get_clean();
	}

	public function render_signer() {
		ob_start(); ?>
		<form class="ag-fid-form" method="post">
			<?php wp_nonce_field( 'ag_fid_signer', 'ag_fid_signer_nonce' ); ?>
			<div class="ag-fid-form__row">
				<input type="text" name="prenom" placeholder="Prénom" required>
				<input type="text" name="nom" placeholder="Nom" required>
			</div>
			<input type="email" name="email" placeholder="Email" required>
			<input type="text" name="cp" placeholder="Code postal" required>
			<label><input type="checkbox" required> J'accepte le traitement RGPD de mes données.</label>
			<button type="submit" class="ag-fid-btn ag-fid-btn--primary">Je signe</button>
		</form>
		<?php
		return ob_get_clean();
	}

	public function render_don() {
		ob_start(); ?>
		<div class="ag-fid-don-grid">
			<?php foreach ( array( 5, 20, 50, 100, 250 ) as $a ) : ?>
				<a href="#" class="ag-fid-don-card">
					<span class="ag-fid-don-card__amount"><?php echo $a; ?>€</span>
					<span class="ag-fid-don-card__note">Coût réel : <?php echo round( $a * 0.34 ); ?>€ après réduction d'impôt 66%</span>
				</a>
			<?php endforeach; ?>
		</div>
		<p class="ag-fid-don-info">Reçu fiscal envoyé automatiquement par email.</p>
		<?php
		return ob_get_clean();
	}

	public function render_adhesion() {
		$cot = get_theme_mod( 'ag_fid_cotisation', '20' );
		ob_start(); ?>
		<div class="ag-fid-adhesion">
			<h3>Cotisation annuelle : <?php echo esc_html( $cot ); ?>€</h3>
			<p>Adhérer vous donne accès aux votes en AG, aux PV internes, à la liste des groupes locaux et au forum interne.</p>
			<form class="ag-fid-form" method="post">
				<?php wp_nonce_field( 'ag_fid_adhesion', 'ag_fid_adhesion_nonce' ); ?>
				<div class="ag-fid-form__row">
					<input type="text" name="prenom" placeholder="Prénom" required>
					<input type="text" name="nom" placeholder="Nom" required>
				</div>
				<input type="email" name="email" placeholder="Email" required>
				<input type="tel" name="tel" placeholder="Téléphone (facultatif)">
				<input type="text" name="adresse" placeholder="Adresse complète" required>
				<select name="role" required>
					<option value="ag_sympathisant">Sympathisant (gratuit)</option>
					<option value="ag_adherent">Adhérent (<?php echo esc_html( $cot ); ?>€)</option>
					<option value="ag_militant">Militant (<?php echo esc_html( $cot ); ?>€ + engagement)</option>
				</select>
				<label><input type="checkbox" required> J'accepte les statuts et le RGPD.</label>
				<button type="submit" class="ag-fid-btn ag-fid-btn--primary">Adhérer</button>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}

	public function render_compte() {
		if ( ! is_user_logged_in() ) {
			return '<p>Veuillez vous <a href="' . esc_url( wp_login_url( get_permalink() ) ) . '">connecter</a> pour accéder à votre espace.</p>';
		}
		$user = wp_get_current_user();
		$role_label = '';
		foreach ( $user->roles as $r ) {
			$role_label = wp_roles()->roles[ $r ]['name'] ?? $r;
			break;
		}
		ob_start(); ?>
		<div class="ag-fid-compte">
			<h3>Bonjour <?php echo esc_html( $user->display_name ); ?></h3>
			<p>Statut : <strong><?php echo esc_html( $role_label ); ?></strong></p>
			<ul>
				<li><a href="<?php echo esc_url( admin_url( 'profile.php' ) ); ?>">Modifier mon profil</a></li>
				<li><a href="<?php echo esc_url( home_url( '/petitions' ) ); ?>">Mes pétitions signées</a></li>
				<?php if ( current_user_can( 'read_private_posts' ) ) : ?>
					<li><a href="<?php echo esc_url( home_url( '/?post_type=ag_pv' ) ); ?>">PV & Comptes-rendus</a> <small>(adhérents)</small></li>
				<?php endif; ?>
				<li><a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>">Se déconnecter</a></li>
			</ul>
		</div>
		<?php
		return ob_get_clean();
	}

	public function render_mentions() {
		$org    = get_theme_mod( 'ag_fid_org_name', get_theme_mod( 'ag_asso_name', 'Mouvement Citoyen Solidaire' ) );
		$siret  = get_theme_mod( 'ag_fid_org_siret', '' );
		$rna    = get_theme_mod( 'ag_fid_org_rna', '' );
		$pres   = get_theme_mod( 'ag_fid_president', '' );
		$addr   = get_theme_mod( 'ag_asso_address', '' );
		$email  = get_theme_mod( 'ag_asso_email', '' );
		$phone  = get_theme_mod( 'ag_asso_phone', '' );
		$dpo    = get_theme_mod( 'ag_asso_dpo_email', '' );
		$host   = get_theme_mod( 'ag_asso_host', 'OVH SAS — 2 rue Kellermann, 59100 Roubaix' );
		ob_start(); ?>
		<div class="ag-fid-legal">
			<h2>Éditeur du site</h2>
			<p>
				<strong><?php echo esc_html( $org ); ?></strong><br>
				Association loi 1901 à but non lucratif<br>
				<?php if ( $rna ) echo 'Numéro RNA : ' . esc_html( $rna ) . '<br>'; ?>
				<?php if ( $siret ) echo 'SIRET : ' . esc_html( $siret ) . '<br>'; ?>
				<?php if ( $addr ) echo 'Siège social : ' . esc_html( $addr ) . '<br>'; ?>
				<?php if ( $pres ) echo 'Directeur·rice de la publication : ' . esc_html( $pres ) . '<br>'; ?>
				<?php if ( $email ) echo 'Email : <a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a><br>'; ?>
				<?php if ( $phone ) echo 'Téléphone : ' . esc_html( $phone ); ?>
			</p>

			<h2>Hébergement</h2>
			<p><?php echo esc_html( $host ); ?></p>

			<h2>Propriété intellectuelle</h2>
			<p>L'ensemble des contenus (textes, images, vidéos) publiés sur ce site sont la propriété de <?php echo esc_html( $org ); ?> ou de leurs auteurs respectifs. Toute reproduction est autorisée sous licence Creative Commons BY-NC-SA 4.0, à condition de citer la source.</p>

			<h2>Données personnelles</h2>
			<p>Pour toute question relative à vos données personnelles, contactez notre Délégué·e à la Protection des Données <?php if ( $dpo ) echo 'à <a href="mailto:' . esc_attr( $dpo ) . '">' . esc_html( $dpo ) . '</a>'; ?>. Voir aussi notre <a href="<?php echo esc_url( home_url( '/rgpd/' ) ); ?>">politique de confidentialité</a>.</p>
		</div>
		<?php
		return ob_get_clean();
	}

	public function render_rgpd() {
		$org   = get_theme_mod( 'ag_fid_org_name', get_theme_mod( 'ag_asso_name', 'Mouvement Citoyen Solidaire' ) );
		$dpo   = get_theme_mod( 'ag_asso_dpo_email', '' );
		ob_start(); ?>
		<div class="ag-fid-legal">
			<p><em>Dernière mise à jour : <?php echo esc_html( date_i18n( 'j F Y' ) ); ?></em></p>

			<h2>Quelles données collectons-nous ?</h2>
			<ul>
				<li><strong>Adhésion / signature de pétition :</strong> nom, prénom, email, code postal, téléphone (facultatif).</li>
				<li><strong>Don :</strong> en plus du formulaire d'adhésion, l'adresse postale (obligation fiscale pour le reçu).</li>
				<li><strong>Navigation :</strong> uniquement des cookies techniques. Pas de tracking publicitaire.</li>
			</ul>

			<h2>À quoi servent vos données ?</h2>
			<ul>
				<li>Vous tenir informé·e de nos campagnes et événements (newsletter).</li>
				<li>Établir votre reçu fiscal annuel (dons et cotisations).</li>
				<li>Vous mettre en lien avec votre groupe local le plus proche.</li>
				<li>Statistiques internes anonymisées (jamais revendues).</li>
			</ul>

			<h2>Vos droits</h2>
			<p>Conformément au RGPD, vous disposez d'un droit d'accès, de rectification, d'effacement, de portabilité et d'opposition au traitement de vos données. Pour exercer ces droits, contactez<?php if ( $dpo ) echo ' notre DPO à <a href="mailto:' . esc_attr( $dpo ) . '">' . esc_html( $dpo ) . '</a>'; ?>. Vous pouvez aussi déposer une réclamation auprès de la CNIL.</p>

			<h2>Durée de conservation</h2>
			<p>Vos données sont conservées tant que vous êtes adhérent·e + 3 ans après. Les pièces comptables (dons) sont conservées 10 ans, conformément à la loi.</p>

			<h2>Sous-traitants</h2>
			<p>Nous n'utilisons que des prestataires européens conformes RGPD : OVH (hébergement, France), Stripe (paiement, Irlande), MailPoet (newsletter, France). <?php echo esc_html( $org ); ?> ne vend ni ne loue jamais ses données.</p>
		</div>
		<?php
		return ob_get_clean();
	}
}
