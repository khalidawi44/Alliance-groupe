<?php
/**
 * Template Name: Résilience Ransomware
 *
 * Page publique de l'offre « Test de résilience ransomware » (légale, non-destructive).
 * Contenu marketing autonome (styles inline) — utilise le header/footer du thème.
 * Source du contenu : pentest-bridge/OFFRE-resilience-ransomware.md
 *
 * @package Alliance_Groupe_Theme
 */
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

$ag_contact = home_url( '/contact' );
$or  = '#F37A1F';
$rouge = '#E10F1A';
?>
<main class="ag-ranso" style="max-width:1080px;margin:0 auto;padding:0 18px 60px;font-family:inherit;color:#1a1a1a">

	<!-- HERO -->
	<section style="text-align:center;padding:56px 0 28px">
		<div style="display:inline-block;background:rgba(225,15,26,.08);color:<?php echo $rouge; ?>;font-weight:800;border-radius:999px;padding:6px 16px;font-size:.85rem;letter-spacing:.04em">🛡️ CYBERSÉCURITÉ · TEST DE RÉSILIENCE</div>
		<h1 style="font-size:clamp(2rem,5vw,3.2rem);line-height:1.1;margin:18px 0 12px;font-weight:900">Et si un ransomware frappait<br><em style="color:<?php echo $rouge; ?>;font-style:normal">votre entreprise demain ?</em></h1>
		<p style="font-size:1.2rem;max-width:720px;margin:0 auto 26px;color:#444">On le simule <strong>sans jamais toucher à vos vraies données</strong> : vos sauvegardes tiennent-elles ? Votre antivirus détecte-t-il l'attaque ? Combien de temps seriez-vous à l'arrêt ? Vous recevez un rapport clair et un plan d'action.</p>
		<a href="<?php echo esc_url( $ag_contact ); ?>" style="display:inline-block;background:linear-gradient(135deg,<?php echo $rouge; ?>,<?php echo $or; ?>);color:#fff;font-weight:900;text-decoration:none;padding:16px 34px;border-radius:999px;font-size:1.1rem;box-shadow:0 12px 36px rgba(225,15,26,.35)">Demander mon test →</a>
		<div style="margin-top:12px;color:#777;font-size:.9rem">À partir de 490 € · Réponse sous 24 h · 100 % légal &amp; non-destructif</div>
	</section>

	<!-- LE PROBLEME -->
	<section style="background:#0f1115;color:#eaeaea;border-radius:20px;padding:36px 30px;margin:30px 0">
		<h2 style="font-size:1.7rem;font-weight:900;margin:0 0 18px">90 % des entreprises se croient protégées… jusqu'au jour J</h2>
		<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px">
			<div style="background:#1a1d24;border-radius:14px;padding:18px"><strong style="color:<?php echo $or; ?>">Vos sauvegardes</strong><br>Se restaurent-elles vraiment, et en combien de temps ?</div>
			<div style="background:#1a1d24;border-radius:14px;padding:18px"><strong style="color:<?php echo $or; ?>">Votre antivirus / EDR</strong><br>Détecte-t-il l'attaque à temps, ou trop tard ?</div>
			<div style="background:#1a1d24;border-radius:14px;padding:18px"><strong style="color:<?php echo $or; ?>">Vos équipes</strong><br>Savent-elles quoi faire dans la première heure ?</div>
			<div style="background:#1a1d24;border-radius:14px;padding:18px"><strong style="color:<?php echo $or; ?>">La propagation</strong><br>Un intrus peut-il se répandre sur toutes les machines ?</div>
		</div>
	</section>

	<!-- CE QU'ON FAIT / NE FAIT PAS -->
	<section style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:20px;margin:30px 0">
		<div style="border:2px solid #16a34a;border-radius:16px;padding:24px;background:#f1fdf5">
			<h3 style="margin:0 0 14px;color:#15803d;font-size:1.25rem">✅ Ce qu'on fait</h3>
			<ul style="margin:0;padding-left:20px;line-height:1.8">
				<li>Simuler le <strong>comportement</strong> d'un ransomware (outils reconnus) sur <strong>machine de test isolée</strong></li>
				<li><strong>Tester la restauration</strong> d'une sauvegarde (copie) et la chronométrer</li>
				<li>Vérifier si l'<strong>EDR/antivirus détecte et bloque</strong></li>
				<li>Mesurer la <strong>propagation possible</strong></li>
				<li>Remettre un <strong>rapport + plan d'action chiffré</strong></li>
			</ul>
		</div>
		<div style="border:2px solid <?php echo $rouge; ?>;border-radius:16px;padding:24px;background:#fef2f2">
			<h3 style="margin:0 0 14px;color:<?php echo $rouge; ?>;font-size:1.25rem">❌ Ce qu'on ne fait jamais</h3>
			<ul style="margin:0;padding-left:20px;line-height:1.8">
				<li>Chiffrer vos vrais fichiers ou serveurs de production</li>
				<li>Déployer un vrai malware sur votre réseau</li>
				<li>Voler ou exfiltrer vos données clients</li>
				<li>Toute action irréversible ou destructrice</li>
				<li>Vous laisser sans recommandations</li>
			</ul>
		</div>
	</section>
	<p style="text-align:center;color:#666;font-size:.95rem;margin:-6px 0 26px">Prestation réalisée <strong>uniquement sous mandat écrit signé</strong> (périmètre, machines de test, dates). Conforme RGPD — aucune donnée réelle manipulée en clair.</p>

	<!-- DEROULE -->
	<section style="margin:34px 0">
		<h2 style="text-align:center;font-size:1.8rem;font-weight:900;margin:0 0 24px">Comment ça se passe — 4 étapes</h2>
		<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:16px">
			<div style="border:1px solid #e5e7eb;border-radius:14px;padding:20px"><div style="font-weight:900;color:<?php echo $or; ?>;font-size:1.4rem">1.</div><strong>Cadrage</strong> (0,5 j)<br><span style="color:#555">Réunion, mandat signé, inventaire (sauvegardes, EDR, machines de test).</span></div>
			<div style="border:1px solid #e5e7eb;border-radius:14px;padding:20px"><div style="font-weight:900;color:<?php echo $or; ?>;font-size:1.4rem">2.</div><strong>Simulation en labo isolé</strong> (1 j)<br><span style="color:#555">On rejoue les techniques d'un ransomware réel sur une VM dédiée. On observe ce que l'EDR voit.</span></div>
			<div style="border:1px solid #e5e7eb;border-radius:14px;padding:20px"><div style="font-weight:900;color:<?php echo $or; ?>;font-size:1.4rem">3.</div><strong>Test de restauration</strong> (0,5 j)<br><span style="color:#555">On restaure une copie de sauvegarde et on chronomètre (temps de reprise réel).</span></div>
			<div style="border:1px solid #e5e7eb;border-radius:14px;padding:20px"><div style="font-weight:900;color:<?php echo $or; ?>;font-size:1.4rem">4.</div><strong>Rapport + restitution</strong> (0,5 j)<br><span style="color:#555">Note /100, failles classées, plan d'action chiffré, présentation à l'équipe.</span></div>
		</div>
	</section>

	<!-- FORMULES -->
	<section style="margin:34px 0">
		<h2 style="text-align:center;font-size:1.8rem;font-weight:900;margin:0 0 6px">Formules</h2>
		<p style="text-align:center;color:#777;margin:0 0 22px">Tarifs TTC indicatifs · devis gratuit adapté à votre structure</p>
		<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:18px">
			<div style="border:1px solid #e5e7eb;border-radius:16px;padding:24px;text-align:center">
				<div style="font-weight:800;color:#555">Essentiel</div>
				<div style="font-size:2rem;font-weight:900;margin:8px 0"><?php echo '490'; ?> €</div>
				<div style="color:#666;font-size:.92rem">TPE / asso / 1 site<br>Audit sauvegardes + restauration testée + rapport</div>
			</div>
			<div style="border:2px solid <?php echo $or; ?>;border-radius:16px;padding:24px;text-align:center;box-shadow:0 10px 30px rgba(243,122,31,.18);position:relative">
				<div style="position:absolute;top:-12px;left:50%;transform:translateX(-50%);background:<?php echo $or; ?>;color:#fff;font-weight:800;font-size:.75rem;padding:3px 12px;border-radius:999px">POPULAIRE</div>
				<div style="font-weight:800;color:#555">Pro</div>
				<div style="font-size:2rem;font-weight:900;margin:8px 0">1 490 €</div>
				<div style="color:#666;font-size:.92rem">PME / commerce / cabinet<br>Les 4 phases (simulation EDR incluse), 1 poste-test</div>
			</div>
			<div style="border:1px solid #e5e7eb;border-radius:16px;padding:24px;text-align:center">
				<div style="font-weight:800;color:#555">Avancé</div>
				<div style="font-size:2rem;font-weight:900;margin:8px 0">2 900 €</div>
				<div style="color:#666;font-size:.92rem">Multi-postes / données sensibles<br>Pro + propagation réseau + 3 postes + atelier équipe</div>
			</div>
			<div style="border:1px solid #e5e7eb;border-radius:16px;padding:24px;text-align:center">
				<div style="font-weight:800;color:#555">Collectivité / sur mesure</div>
				<div style="font-size:2rem;font-weight:900;margin:8px 0">sur devis</div>
				<div style="color:#666;font-size:.92rem">Bailleur / collectivité / &gt;20 postes<br>Périmètre dédié + accompagnement remédiation</div>
			</div>
		</div>
		<p style="text-align:center;margin-top:18px;color:#555">Option <strong>« Sérénité »</strong> : re-test annuel + veille = <strong>29 €/mois</strong>.</p>
	</section>

	<!-- CTA FINAL -->
	<section style="text-align:center;background:linear-gradient(135deg,#0f1115,#1a1d24);color:#fff;border-radius:20px;padding:44px 26px;margin:34px 0">
		<h2 style="font-size:1.9rem;font-weight:900;margin:0 0 10px">Testez avant qu'un pirate ne le fasse</h2>
		<p style="color:#cfcfcf;max-width:620px;margin:0 auto 22px">Un rapport clair, 5 corrections prioritaires, et la tranquillité de savoir que vous tiendrez le choc.</p>
		<a href="<?php echo esc_url( $ag_contact ); ?>" style="display:inline-block;background:linear-gradient(135deg,<?php echo $rouge; ?>,<?php echo $or; ?>);color:#fff;font-weight:900;text-decoration:none;padding:16px 36px;border-radius:999px;font-size:1.1rem">Demander mon test de résilience →</a>
	</section>

</main>
<?php
get_footer();
