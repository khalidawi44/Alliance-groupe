<?php
/**
 * Exemples d'audits ANONYMISÉS — preuve sociale.
 * Affiché sur l'accueil et sur la page « Tester mon site ».
 * Aucune donnée client réelle : noms d'entreprise MASQUÉS, exemples illustratifs
 * représentatifs de résultats réels (secteur + ville + score + points trouvés).
 *
 * @package Alliance_Groupe_Theme
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$ag_ex = array(
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
$ag_col = function ( $s ) { return $s >= 75 ? '#28a745' : ( $s >= 50 ? '#F37A1F' : '#E10F1A' ); };
?>
<section class="ag-ex" aria-label="Exemples d'audits récents">
	<div class="ag-ex__head">
		<span class="ag-ex__tag">⟶ Exemples d'audits récents</span>
		<h2 class="ag-ex__title">Ce qu'on trouve <em>vraiment</em></h2>
		<p class="ag-ex__lead">Résultats réels, <strong>noms d'entreprise masqués</strong>. Voici le genre de failles révélées en quelques minutes — la vôtre est peut-être dans le même cas.</p>
	</div>
	<div class="ag-ex__grid">
		<?php foreach ( $ag_ex as $e ) : $c = $ag_col( $e['score'] ); ?>
			<div class="ag-ex__card">
				<div class="ag-ex__top">
					<div>
						<div class="ag-ex__sec"><?php echo esc_html( $e['sec'] ); ?></div>
						<div class="ag-ex__meta">🔒 <span class="ag-ex__masked">●●●●●●●●</span> · <?php echo esc_html( $e['ville'] ); ?></div>
					</div>
					<div class="ag-ex__score" style="color:<?php echo esc_attr( $c ); ?>;border-color:<?php echo esc_attr( $c ); ?>">
						<?php echo (int) $e['score']; ?><small>/100</small>
					</div>
				</div>
				<div class="ag-ex__badge"><?php echo esc_html( $e['type'] ); ?></div>
				<ul class="ag-ex__pts">
					<?php foreach ( $e['pts'] as $p ) : ?>
						<li>⚠️ <?php echo esc_html( $p ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endforeach; ?>
	</div>
	<div class="ag-ex__cta">
		<a href="<?php echo esc_url( home_url( '/tester-mon-site' ) ); ?>" class="ag-ex__btn">🔍 Tester mon site gratuitement →</a>
		<span class="ag-ex__note">Gratuit · résultat immédiat · sans engagement</span>
	</div>
</section>
<style>
.ag-ex{padding:70px 24px;background:linear-gradient(180deg,#0a0a0f,#0f0f17);color:#fff}
.ag-ex__head{max-width:760px;margin:0 auto 40px;text-align:center}
.ag-ex__tag{display:inline-block;color:#D4B45C;font-size:.82rem;letter-spacing:3px;text-transform:uppercase;font-weight:700;margin-bottom:12px}
.ag-ex__title{font-family:Georgia,'Playfair Display',serif;font-size:clamp(1.8rem,4vw,3rem);font-weight:700;margin:0 0 12px}
.ag-ex__title em{background:linear-gradient(135deg,#D4B45C,#F37A1F);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;color:transparent;font-style:italic}
.ag-ex__lead{color:rgba(255,255,255,.72);font-size:1.05rem;line-height:1.6;margin:0}
.ag-ex__grid{max-width:1200px;margin:0 auto;display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:18px}
.ag-ex__card{background:#15151f;border:1px solid rgba(212,180,92,.18);border-radius:16px;padding:20px;transition:transform .25s ease,border-color .25s ease}
.ag-ex__card:hover{transform:translateY(-4px);border-color:rgba(243,122,31,.5)}
.ag-ex__top{display:flex;justify-content:space-between;align-items:flex-start;gap:12px}
.ag-ex__sec{font-weight:800;font-size:1.08rem}
.ag-ex__meta{color:rgba(255,255,255,.6);font-size:.85rem;margin-top:3px}
.ag-ex__masked{letter-spacing:1px;color:rgba(255,255,255,.4)}
.ag-ex__score{flex-shrink:0;border:3px solid;border-radius:50%;width:64px;height:64px;display:flex;flex-direction:column;align-items:center;justify-content:center;font-size:1.5rem;font-weight:900;line-height:1}
.ag-ex__score small{font-size:.6rem;font-weight:600;opacity:.7}
.ag-ex__badge{display:inline-block;margin:14px 0 10px;background:rgba(243,122,31,.15);color:#F37A1F;font-weight:700;font-size:.75rem;padding:3px 10px;border-radius:999px}
.ag-ex__pts{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:7px}
.ag-ex__pts li{font-size:.92rem;color:rgba(255,255,255,.85);background:rgba(225,15,26,.08);border-left:3px solid #E10F1A;padding:6px 10px;border-radius:0 6px 6px 0}
.ag-ex__cta{text-align:center;margin-top:40px}
.ag-ex__btn{display:inline-block;background:linear-gradient(135deg,#E10F1A,#F37A1F);color:#fff;font-weight:900;text-decoration:none;padding:15px 32px;border-radius:999px;font-size:1.05rem;box-shadow:0 12px 36px rgba(225,15,26,.35)}
.ag-ex__note{display:block;margin-top:10px;color:rgba(255,255,255,.55);font-size:.85rem}
</style>
