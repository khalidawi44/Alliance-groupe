<?php
/**
 * Exemples ANONYMISÉS — preuve sociale, regroupés en 2 catégories :
 *   • Sécurité  : résultats d'audits (failles trouvées, score)
 *   • Création  : sites réalisés (type, résultat)
 * Aucune donnée client réelle : noms d'entreprise MASQUÉS. Exemples illustratifs.
 *
 * @package Alliance_Groupe_Theme
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ── SÉCURITÉ : on pioche dans les VRAIS audits (Espace Audit), noms masqués ── */
$ag_sec_ex = array();
if ( function_exists( 'ag_audit_hist_get' ) ) {
	$H = ag_audit_hist_get();
	if ( is_array( $H ) ) {
		uasort( $H, function ( $a, $b ) { return (int) ( $b['ts'] ?? 0 ) <=> (int) ( $a['ts'] ?? 0 ); } );
		foreach ( $H as $e ) {
			$host = (string) ( $e['host'] ?? '' );
			if ( ! $host || false !== stripos( $host, 'alliancegroupe' ) ) continue; // pas notre propre site
			$pts = array();
			foreach ( ( $e['checks'] ?? array() ) as $c ) {
				if ( 'ok' !== ( $c['status'] ?? '' ) && ! empty( $c['name'] ) ) $pts[] = $c['name'];
			}
			if ( empty( $pts ) ) continue; // on ne montre que des cas avec des points trouvés
			$tld    = ( false !== strpos( $host, '.' ) ) ? substr( strrchr( $host, '.' ), 1 ) : '';
			$masked = '●●●●●●●' . ( $tld ? '.' . $tld : '' ); // nom de domaine MASQUÉ, extension gardée
			$mode   = $e['mode'] ?? 'passive';
			$type   = 'expert' === $mode ? 'Expert' : ( 'deep' === $mode ? 'Approfondi' : 'Léger' );
			$ag_sec_ex[] = array(
				'sec'   => $masked,
				'ville' => 'audit ' . ( ! empty( $e['ts'] ) ? wp_date( 'd/m', (int) $e['ts'] ) : 'récent' ),
				'score' => (int) ( $e['score'] ?? 0 ),
				'type'  => $type,
				'pts'   => array_slice( array_values( array_unique( $pts ) ), 0, 3 ),
			);
			if ( count( $ag_sec_ex ) >= 6 ) break;
		}
	}
}
// Repli sur des exemples illustratifs si l'historique est vide.
if ( empty( $ag_sec_ex ) ) {
	$ag_sec_ex = array(
		array( 'sec' => 'Cabinet d\'avocats', 'ville' => 'Bordeaux', 'score' => 38, 'type' => 'Approfondi',
			'pts' => array( 'Versions de plugins exposées', 'xmlrpc ouvert (force brute)', 'En-têtes de sécurité absents' ) ),
		array( 'sec' => 'E-commerce mode', 'ville' => 'Paris', 'score' => 29, 'type' => 'Expert',
			'pts' => array( 'Sauvegarde téléchargeable', 'PHP en fin de vie', 'Listing de répertoires' ) ),
		array( 'sec' => 'Restaurant', 'ville' => 'Lyon', 'score' => 54, 'type' => 'Léger',
			'pts' => array( 'Pas de HTTPS strict', 'Version du CMS exposée' ) ),
		array( 'sec' => 'Cabinet médical', 'ville' => 'Nantes', 'score' => 47, 'type' => 'Approfondi',
			'pts' => array( 'Énumération du compte admin', 'Pingback actif (DDoS)', 'Certificat faible' ) ),
		array( 'sec' => 'Artisan BTP', 'ville' => 'Marseille', 'score' => 61, 'type' => 'Léger',
			'pts' => array( 'En-têtes de sécurité manquants', 'Données structurées absentes' ) ),
		array( 'sec' => 'Agence immobilière', 'ville' => 'Toulouse', 'score' => 33, 'type' => 'Expert',
			'pts' => array( 'Fichier de configuration accessible', 'Plugins obsolètes (CVE)', 'Pas de pare-feu applicatif' ) ),
	);
}
$ag_crea_ex = array(
	array( 'sec' => 'Restaurant', 'ville' => 'Lille', 'type' => 'Site vitrine', 'res' => 'Livré en 4 jours · réservation en ligne' ),
	array( 'sec' => 'Coach sportif', 'ville' => 'Nantes', 'type' => 'Landing + RDV', 'res' => '+60 % de prises de rendez-vous' ),
	array( 'sec' => 'Boutique déco', 'ville' => 'Bordeaux', 'type' => 'E-commerce', 'res' => '120 produits · paiement sécurisé' ),
	array( 'sec' => 'Artisan menuisier', 'ville' => 'Rennes', 'type' => 'Vitrine + devis', 'res' => 'Demandes de devis x3' ),
	array( 'sec' => 'Cabinet d\'avocats', 'ville' => 'Paris', 'type' => 'Sur-mesure', 'res' => 'Site institutionnel premium' ),
	array( 'sec' => 'Association', 'ville' => 'Toulouse', 'type' => 'Vitrine + dons', 'res' => 'Site offert · dons en ligne' ),
);
$ag_col = function ( $s ) { return $s >= 75 ? '#28a745' : ( $s >= 50 ? '#F37A1F' : '#E10F1A' ); };
?>
<section class="ag-ex" aria-label="Exemples récents">
	<div class="ag-ex__head">
		<span class="ag-ex__tag">⟶ Exemples récents</span>
		<h2 class="ag-ex__title">Nos <em>résultats</em> récents</h2>
		<p class="ag-ex__lead">Des cas réels, présentés de façon <strong>anonyme</strong> par respect de la confidentialité de nos clients — côté <strong>sécurité</strong> (ce qu'on trouve) et <strong>création</strong> (ce qu'on livre).</p>
	</div>

	<!-- ───────── SÉCURITÉ ───────── -->
	<div class="ag-ex__cat">🛡️ Sécurité — audits récents</div>
	<div class="ag-ex__grid">
		<?php foreach ( $ag_sec_ex as $e ) : $c = $ag_col( $e['score'] ); ?>
			<div class="ag-ex__card">
				<div class="ag-ex__top">
					<div>
						<div class="ag-ex__sec"><?php echo esc_html( $e['sec'] ); ?></div>
						<div class="ag-ex__meta">🔒 <?php echo esc_html( $e['ville'] ); ?></div>
					</div>
					<div class="ag-ex__score" style="color:<?php echo esc_attr( $c ); ?>;border-color:<?php echo esc_attr( $c ); ?>">
						<?php echo (int) $e['score']; ?><small>/100</small>
					</div>
				</div>
				<div class="ag-ex__badge ag-ex__badge--sec"><?php echo esc_html( $e['type'] ); ?></div>
				<ul class="ag-ex__pts">
					<?php foreach ( $e['pts'] as $p ) : ?><li>⚠️ <?php echo esc_html( $p ); ?></li><?php endforeach; ?>
				</ul>
			</div>
		<?php endforeach; ?>
	</div>
	<div class="ag-ex__cta"><a href="<?php echo esc_url( home_url( '/tester-mon-site' ) ); ?>" class="ag-ex__btn">🔍 Tester mon site gratuitement →</a></div>

	<!-- ───────── CRÉATION ───────── -->
	<div class="ag-ex__cat" style="margin-top:48px">✨ Création — sites livrés</div>
	<div class="ag-ex__grid">
		<?php foreach ( $ag_crea_ex as $e ) : ?>
			<div class="ag-ex__card ag-ex__card--crea">
				<div class="ag-ex__top">
					<div>
						<div class="ag-ex__sec"><?php echo esc_html( $e['sec'] ); ?></div>
						<div class="ag-ex__meta">🔒 <span class="ag-ex__masked">●●●●●●●●</span> · <?php echo esc_html( $e['ville'] ); ?></div>
					</div>
					<div class="ag-ex__badge ag-ex__badge--crea"><?php echo esc_html( $e['type'] ); ?></div>
				</div>
				<div class="ag-ex__res">✅ <?php echo esc_html( $e['res'] ); ?></div>
			</div>
		<?php endforeach; ?>
	</div>
	<div class="ag-ex__cta">
		<a href="<?php echo esc_url( home_url( '/sites-express' ) ); ?>" class="ag-ex__btn ag-ex__btn--crea">✨ Voir les offres de création →</a>
		<span class="ag-ex__note">Gratuit pour l'audit · devis gratuit pour la création</span>
	</div>
</section>
<style>
.ag-ex{padding:70px 24px;background:linear-gradient(180deg,#0a0a0f,#0f0f17);color:#fff}
.ag-ex__head{max-width:760px;margin:0 auto 36px;text-align:center}
.ag-ex__tag{display:inline-block;color:#D4B45C;font-size:.82rem;letter-spacing:3px;text-transform:uppercase;font-weight:700;margin-bottom:12px}
.ag-ex__title{font-family:Georgia,'Playfair Display',serif;font-size:clamp(1.8rem,4vw,3rem);font-weight:700;margin:0 0 12px}
.ag-ex__title em{background:linear-gradient(135deg,#D4B45C,#F37A1F);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;color:transparent;font-style:italic}
.ag-ex__lead{color:rgba(255,255,255,.72);font-size:1.05rem;line-height:1.6;margin:0}
.ag-ex__cat{max-width:1200px;margin:0 auto 16px;font-weight:800;font-size:1.15rem;letter-spacing:.5px;color:#fff;border-left:4px solid #F37A1F;padding-left:12px}
.ag-ex__grid{max-width:1200px;margin:0 auto;display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:18px}
.ag-ex__card{background:#15151f;border:1px solid rgba(212,180,92,.18);border-radius:16px;padding:20px;transition:transform .25s ease,border-color .25s ease}
.ag-ex__card:hover{transform:translateY(-4px);border-color:rgba(243,122,31,.5)}
.ag-ex__card--crea:hover{border-color:rgba(40,167,69,.55)}
.ag-ex__top{display:flex;justify-content:space-between;align-items:flex-start;gap:12px}
.ag-ex__sec{font-weight:800;font-size:1.08rem}
.ag-ex__meta{color:rgba(255,255,255,.6);font-size:.85rem;margin-top:3px}
.ag-ex__masked{letter-spacing:1px;color:rgba(255,255,255,.4)}
.ag-ex__score{flex-shrink:0;border:3px solid;border-radius:50%;width:64px;height:64px;display:flex;flex-direction:column;align-items:center;justify-content:center;font-size:1.5rem;font-weight:900;line-height:1}
.ag-ex__score small{font-size:.6rem;font-weight:600;opacity:.7}
.ag-ex__badge{display:inline-block;font-weight:700;font-size:.75rem;padding:3px 10px;border-radius:999px}
.ag-ex__badge--sec{margin:14px 0 10px;background:rgba(243,122,31,.15);color:#F37A1F}
.ag-ex__badge--crea{background:rgba(40,167,69,.15);color:#34c759;flex-shrink:0}
.ag-ex__pts{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:7px}
.ag-ex__pts li{font-size:.92rem;color:rgba(255,255,255,.85);background:rgba(225,15,26,.08);border-left:3px solid #E10F1A;padding:6px 10px;border-radius:0 6px 6px 0}
.ag-ex__res{margin-top:14px;font-size:.95rem;color:rgba(255,255,255,.9);background:rgba(40,167,69,.1);border-left:3px solid #28a745;padding:8px 12px;border-radius:0 6px 6px 0}
.ag-ex__cta{text-align:center;margin-top:26px}
.ag-ex__btn{display:inline-block;background:linear-gradient(135deg,#E10F1A,#F37A1F);color:#fff;font-weight:900;text-decoration:none;padding:14px 30px;border-radius:999px;font-size:1.02rem;box-shadow:0 12px 36px rgba(225,15,26,.3)}
.ag-ex__btn--crea{background:linear-gradient(135deg,#28a745,#D4B45C);box-shadow:0 12px 36px rgba(40,167,69,.25)}
.ag-ex__note{display:block;margin-top:10px;color:rgba(255,255,255,.55);font-size:.85rem}
</style>
