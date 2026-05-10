<?php
/**
 * Guide d'utilisation — page admin centralisee qui documente CHAQUE
 * element editable du theme + plugin Pack Fidelite Association.
 *
 * Apparait dans le menu admin gauche : "Pack Fidelite > 📖 Guide d'utilisation".
 * A jour avec la version courante du plugin.
 *
 * REGLE INTERNE : a chaque ajout / suppression / changement d'un champ
 * Customizer, CPT, ou shortcode, mettre a jour ce fichier (la liste
 * $sections ci-dessous) pour refleter la nouvelle realite.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AG_Fid_Guide {

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ), 25 );
	}

	public static function register_menu() {
		add_submenu_page(
			'ag-fid-recommendations',
			__( 'Guide d\'utilisation', 'ag-fidelite-association' ),
			'📖 ' . __( 'Guide d\'utilisation', 'ag-fidelite-association' ),
			'edit_posts',
			'ag-fid-guide',
			array( __CLASS__, 'render' )
		);
	}

	/**
	 * Source de verite : a maintenir a jour quand on ajoute / retire
	 * un champ editable. Chaque entree = une zone du site.
	 */
	private static function get_sections() {
		return array(

			// ─── ACCUEIL ─────────────────────────────────────────────
			array(
				'icon'  => '🏠',
				'title' => 'Page d\'accueil',
				'desc'  => '⭐ NOUVEAU (v0.32) : les TITRES + INTROS de chaque section de l\'accueil viennent maintenant DIRECTEMENT des pages correspondantes. Vous editez la page (Pages > Combats par ex), son TITRE devient le H2 de la section, son EXTRAIT devient l\'intro. Une seule edition par section, pas de Customizer a fouiller.',
				'rows'  => array(
					array( 'Slogan + titre + sous-titre du hero',                   'Apparence > Personnaliser > Hero' ),
					array( 'Image de fond + overlay + alignement du hero',         'Apparence > Personnaliser > Hero' ),
					array( 'Boutons CTA principal + secondaire',                   'Apparence > Personnaliser > Hero' ),
					array( 'Compteur de signataires (chiffre + objectif)',         'Apparence > Personnaliser > Hero' ),
					array( 'Bandeau d\'urgence/alerte en haut',                    'Apparence > Personnaliser > Bandeau d\'urgence' ),
					array( 'Titre + intro section MANIFESTE (sur l\'accueil)',     'Pages > Manifeste : titre de la page + extrait' ),
					array( 'Titre + intro section COMBATS (sur l\'accueil)',       'Pages > Nos combats : titre + extrait' ),
					array( 'Titre + intro section ÉVÉNEMENTS (sur l\'accueil)',    'Pages > Événements : titre + extrait' ),
					array( 'Titre + intro section GROUPES (sur l\'accueil)',       'Pages > Groupes locaux : titre + extrait' ),
					array( 'Titre + intro section ACTUALITÉS (sur l\'accueil)',    'Pages > Actualités : titre + extrait' ),
					array( 'Titre + intro section SIGNER (sur l\'accueil)',        'Pages > Signer l\'appel : titre + extrait' ),
					array( 'Titre + intro section DON (sur l\'accueil)',           'Pages > Faire un don : titre + extrait' ),
					array( 'Titres + textes des 4 bandeaux parallax',              'Apparence > Personnaliser > Contenu accueil' ),
					array( 'Images de fond des 4 bandeaux parallax',               'Apparence > Personnaliser > Contenu accueil' ),
					array( 'Compteurs stats groupes locaux (3 chiffres)',          'Apparence > Personnaliser > Contenu accueil' ),
					array( 'Texte RGPD du formulaire signature',                   'Apparence > Personnaliser > Contenu accueil' ),
					array( 'Montants de don + libellé "Libre"',                    'Apparence > Personnaliser > Don' ),
					array( 'Cards combats : titre, description, emoji, couleur',   'Combats > admin (chaque combat = 1 entrée + sa metabox emoji/couleur)' ),
					array( 'Cards événements : titre, date, lieu, contenu',        'Événements > admin' ),
					array( 'Articles d\'actualité (3 derniers affichés)',          'Articles > admin' ),
					array( 'Membres équipe (12 photos/noms/rôles)',                'Apparence > Personnaliser > Équipe' ),
					array( 'Sections visibles/masquées (on/off par section)',      'Apparence > Personnaliser > Sections visibles' ),
					array( 'Couleurs + typo + logo',                                'Apparence > Personnaliser > Couleurs / Typo / Logo' ),
					array( 'Animations (counters animés, fade-up sections, CTA pulse)', 'Apparence > Personnaliser > Animations (on/off)' ),
				),
			),

			// ─── PAGES DEDIEES ───────────────────────────────────────
			array(
				'icon'  => '📄',
				'title' => 'Pages dédiées (Manifeste, Combats, Événements, etc.)',
				'desc'  => 'Chaque page dédiée a un layout Gutenberg : titre H1 éditable + paragraphe d\'intro éditable + bloc shortcode (qui affiche le contenu dynamique CPT en dessous). Vous pouvez modifier le titre et l\'intro librement, ajouter d\'autres blocs, ou même supprimer le shortcode pour mettre votre propre contenu.',
				'rows'  => array(
					array( 'Manifeste (texte long du manifeste)',           'Pages > Manifeste (Gutenberg complet)' ),
					array( 'Qui sommes-nous',                                'Pages > Qui sommes-nous (titre + intro éditables)' ),
					array( 'Réunion en ligne (visio Jitsi)',                'Pages > Réunion en ligne' ),
					array( 'Prendre rendez-vous (Cal.com)',                 'Pages > Prendre rendez-vous' ),
					array( 'Combats (page archive)',                         'Pages > Nos combats' ),
					array( 'Événements (page archive + calendrier)',         'Pages > Événements' ),
					array( 'Groupes locaux (carte + liste)',                'Pages > Groupes locaux' ),
					array( 'Actualités (liste articles)',                    'Pages > Actualités' ),
					array( 'Signer l\'appel (formulaire)',                  'Pages > Signer l\'appel' ),
					array( 'Pétitions',                                      'Pages > Pétitions' ),
					array( 'Faire un don',                                   'Pages > Faire un don' ),
					array( 'Adhérer (formulaire adhésion)',                 'Pages > Adhérer' ),
					array( 'Mon espace (espace adhérent)',                  'Pages > Mon espace' ),
					array( 'Mentions légales (auto-générées)',              'Pages > Mentions légales (ou Customizer > Identité)' ),
					array( 'Politique de confidentialité (auto-générée)',   'Pages > Confidentialité' ),
					array( 'Statuts loi 1901',                               'Pages > Statuts (Gutenberg)' ),
				),
			),

			// ─── HEADER / FOOTER / MENUS ─────────────────────────────
			array(
				'icon'  => '🧭',
				'title' => 'Menus, header, footer',
				'desc'  => 'La structure de navigation et le pied de page sont editables sans coder.',
				'rows'  => array(
					array( 'Logo (header + footer)',                         'Apparence > Personnaliser > Identité du site (logo)' ),
					array( 'Liens du menu principal',                        'Apparence > Menus > "AG Fidélité — Principal"' ),
					array( 'Liens du menu footer',                           'Apparence > Menus > "AG Fidélité — Footer"' ),
					array( 'Adresse, email, téléphone (footer)',             'Apparence > Personnaliser > Identité' ),
					array( 'Réseaux sociaux (9 plateformes)',                'Apparence > Personnaliser > Réseaux sociaux' ),
					array( 'Texte copyright + slogan footer',                'Apparence > Personnaliser > Identité' ),
				),
			),

			// ─── PACK FIDELITE — outils association ──────────────────
			array(
				'icon'  => '⚙️',
				'title' => 'Pack Fidélité — outils association',
				'desc'  => 'Champs liés à la gestion d\'association loi 1901.',
				'rows'  => array(
					array( 'Nom officiel + SIRET + RNA + Président·e + IBAN + cotisation', 'Apparence > Personnaliser > Pack Fidélité > Identité' ),
					array( 'Salle visio AG (slug Jitsi + restriction adhérents)',         'Apparence > Personnaliser > Pack Fidélité > Visio AG' ),
					array( 'Compte Cal.com (username + slug événement)',                  'Apparence > Personnaliser > Pack Fidélité > Rendez-vous' ),
					array( 'Rôles utilisateur (sympathisant / adhérent / militant / bureau)', 'Utilisateurs > admin (rôles créés automatiquement à l\'activation)' ),
				),
			),

			// ─── BLOCKS PATTERNS REUTILISABLES ───────────────────────
			array(
				'icon'  => '🧩',
				'title' => 'Blocs réutilisables (Gutenberg patterns)',
				'desc'  => 'Insérez des sections pré-stylées du thème dans n\'importe quelle page : ouvrez l\'éditeur Gutenberg, cliquez le "+", onglet "Compositions" / "Patterns", catégorie "AG Association — sections".',
				'rows'  => array(
					array( 'AG — Hero (slogan + titre + CTAs)',                'Patterns > AG Association — sections' ),
					array( 'AG — Bandeau parallax (titre + texte)',            'Patterns > AG Association — sections' ),
					array( 'AG — Section (titre + intro)',                     'Patterns > AG Association — sections' ),
					array( 'AG — Grille combats (6 cartes colorées)',          'Patterns > AG Association — sections' ),
					array( 'AG — Cartes de dons (montants + libre)',           'Patterns > AG Association — sections' ),
					array( 'AG — CTA Signer la pétition',                      'Patterns > AG Association — sections' ),
				),
			),

			// ─── MISE A JOUR & SUPPORT ───────────────────────────────
			array(
				'icon'  => '🔄',
				'title' => 'Mise à jour & support',
				'desc'  => 'Le thème et le plugin se mettent à jour automatiquement (1h max). Vous pouvez forcer la vérif manuellement.',
				'rows'  => array(
					array( 'Forcer mise à jour du thème',     admin_url( 'themes.php?ag_asso_check_theme=1' ) ),
					array( 'Forcer mise à jour du plugin',    admin_url( 'plugins.php?ag_fid_check_update=1' ) ),
					array( 'Recommandations d\'extensions',   admin_url( 'admin.php?page=ag-fid-recommendations' ) ),
					array( 'Démarrage rapide (wizard)',       admin_url( 'themes.php?page=ag-asso-welcome' ) ),
				),
			),
		);
	}

	public static function render() {
		?>
		<div class="wrap" style="max-width:1100px;">
			<h1 style="display:flex;align-items:center;gap:10px;">
				📖 Guide d'utilisation
				<small style="font-size:.55em;color:#888;font-weight:normal;">v<?php echo esc_html( defined( 'AG_FID_VERSION' ) ? AG_FID_VERSION : '?' ); ?></small>
			</h1>

			<div class="notice notice-info inline" style="border-left-color:#E10F1A;padding:14px 18px;margin:10px 0 24px;">
				<p style="margin:0;font-size:1.05rem;"><strong>Cette page liste TOUT ce que vous pouvez modifier sur votre site sans coder.</strong></p>
				<p style="margin:6px 0 0;color:#555;">Chaque ligne indique une zone du site et où la modifier. Cliquez les liens pour aller directement au bon endroit.</p>
			</div>

			<style>
				.ag-fid-guide-section { background:#fff; border:1px solid #e0e0e0; border-radius:8px; padding:18px 22px; margin-bottom:18px; }
				.ag-fid-guide-section h2 { margin:0 0 6px; font-size:1.25rem; display:flex; align-items:center; gap:8px; }
				.ag-fid-guide-section .desc { color:#666; margin:0 0 14px; font-style:italic; }
				.ag-fid-guide-section table { width:100%; border-collapse:collapse; }
				.ag-fid-guide-section table td { padding:8px 12px; border-bottom:1px solid #f0f0f0; vertical-align:top; }
				.ag-fid-guide-section table td:first-child { width:55%; font-weight:500; }
				.ag-fid-guide-section table td:last-child { color:#444; }
				.ag-fid-guide-section table td a { font-weight:600; color:#E10F1A; }
				.ag-fid-guide-section table tr:last-child td { border-bottom:none; }
			</style>

			<?php foreach ( self::get_sections() as $section ) : ?>
				<div class="ag-fid-guide-section">
					<h2><?php echo esc_html( $section['icon'] ); ?> <?php echo esc_html( $section['title'] ); ?></h2>
					<p class="desc"><?php echo esc_html( $section['desc'] ); ?></p>
					<table>
						<?php foreach ( $section['rows'] as $row ) :
							list( $what, $where ) = $row;
							// Si $where ressemble a une URL admin → lien clickable
							$is_url = ( strpos( $where, admin_url() ) === 0 );
							?>
							<tr>
								<td><?php echo esc_html( $what ); ?></td>
								<td>
									<?php if ( $is_url ) : ?>
										<a href="<?php echo esc_url( $where ); ?>">→ Aller</a>
									<?php else : ?>
										<?php echo esc_html( $where ); ?>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</table>
				</div>
			<?php endforeach; ?>

			<div class="ag-fid-guide-section" style="background:#fffbe6;border-color:#f0d000;">
				<h2>💡 Vous ne trouvez pas ?</h2>
				<p>Si vous voulez modifier quelque chose qui n'est pas dans cette liste, ouvrez la page concernée avec <strong>l'éditeur de blocs Gutenberg</strong> (Pages > votre page) — vous pouvez librement ajouter / supprimer / déplacer des blocs.</p>
				<p>Pour les sections design (hero, parallax, combats grid, dons, signer CTA) : utilisez les <strong>blocs réutilisables AG Association</strong> dans n'importe quelle page (catégorie "Compositions" / "Patterns" de l'éditeur).</p>
			</div>
		</div>
		<?php
	}
}

AG_Fid_Guide::init();
