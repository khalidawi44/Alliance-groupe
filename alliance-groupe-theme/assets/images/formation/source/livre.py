# -*- coding: utf-8 -*-
"""Formation Ambassadeur — version LIVRE A5 (recto/verso, imprimable)."""
import importlib.util, sys
spec=importlib.util.spec_from_file_location('b2','build2.py'); m=importlib.util.module_from_spec(spec); spec.loader.exec_module(m)
B, gtitle, divider, diamond, frame, qr_gold, eur = m.B, m.gtitle, m.divider, m.diamond, m.frame, m.qr_gold, m.eur
OFFRES, TPL = m.OFFRES, m.TPL

CSS = m.CSS.split('@page')[0] + '''
@page{size:148mm 210mm;margin:0}
*{box-sizing:border-box;margin:0;padding:0}
:root{--or:#d9b95e;--or2:#f3dc92;--or3:#a57c2c;--txt:#efe9d8;--mut:#b2ab98;--ligne:rgba(217,185,94,.45)}
html,body{background:#0b0a09}
body{font-family:Manrope,system-ui,sans-serif;color:var(--txt);-webkit-print-color-adjust:exact;print-color-adjust:exact}
a{color:inherit;text-decoration:none}
.p{width:148mm;height:210mm;position:relative;overflow:hidden;page-break-after:always;padding:16mm 14mm 18mm;background:#0b0a09 url(img/bg.jpg) center/cover}
.p.r{padding-left:18mm;padding-right:12mm}   /* page de droite : marge de reliure à gauche */
.p.v{padding-left:12mm;padding-right:18mm}   /* page de gauche : marge de reliure à droite */
.fr{position:absolute;inset:0;pointer-events:none}
.fr::before{content:"";position:absolute;inset:7mm;border:1.4px solid var(--or);border-radius:2px;box-shadow:0 0 10px rgba(217,185,94,.22)}
.fr::after{content:"";position:absolute;inset:9mm;border:.6px solid rgba(217,185,94,.55)}
.fr .c{position:absolute;width:78px;height:78px}
.fr .c svg{width:78px;height:78px}
.fr .tl{top:4mm;left:4mm}.fr .tr{top:4mm;right:4mm;transform:scaleX(-1)}.fr .bl{bottom:4mm;left:4mm;transform:scaleY(-1)}.fr .br{bottom:4mm;right:4mm;transform:scale(-1,-1)}
.fr .topd{position:absolute;top:2mm;left:50%;transform:translateX(-50%);background:#0b0a09;padding:0 5px;line-height:0}
.gt{display:block;margin:0 auto;overflow:visible}
.dv{display:block;margin:5px auto 9px}
.hd{display:flex;align-items:center;justify-content:space-between;margin:0 2px 8px;font:700 7.5px/1 Cinzel,serif;letter-spacing:.18em;color:#8a7f66}
.hd b{color:var(--or);font-weight:700}
.fo{position:absolute;left:14mm;right:14mm;bottom:9mm;display:flex;align-items:center;justify-content:center;gap:10px;font:700 8px/1 Cinzel,serif;letter-spacing:.14em;color:#8a7f66}
.fo em{font-style:normal;color:var(--or2);font-size:10px}
.sub{text-align:center;font:italic 500 15.5px/1.4 "Cormorant Garamond",serif;color:var(--txt);margin:0 4px 10px}
.sub b{color:var(--or2);font-weight:700;font-style:normal;font-family:Cinzel,serif;font-size:11.5px;letter-spacing:.04em}
.card{position:relative;background:linear-gradient(180deg,rgba(26,23,17,.92),rgba(12,11,9,.95));border:1px solid var(--or);border-radius:5px;box-shadow:inset 0 0 0 2.5px rgba(11,10,9,.9),inset 0 0 0 3.5px rgba(217,185,94,.32),0 8px 20px -12px #000}
.card::before,.card::after{content:"◆";position:absolute;top:-7px;font-size:9px;color:var(--or);background:#0b0a09;padding:0 3px;line-height:12px}
.card::before{left:12px}.card::after{right:12px}
.chk{list-style:none}
.chk li{position:relative;padding-left:16px;font-size:11.5px;line-height:1.55;color:var(--txt)}
.chk li::before{content:"✓";position:absolute;left:0;top:0;color:var(--or2);font-weight:800}
.btn{display:flex;align-items:center;justify-content:center;gap:8px;background:linear-gradient(180deg,#f6e3a0,#d9b95e 45%,#a57c2c);color:#1a1307;font:700 12px/1 Cinzel,serif;letter-spacing:.06em;padding:11px 14px;border-radius:3px;border:1px solid #fff0bb;box-shadow:0 0 0 2px #0b0a09,0 0 0 3px var(--or)}
/* sommaire */
.som{list-style:none;margin:2px 6px}
.som li{display:flex;align-items:baseline;gap:8px;padding:9px 0;font:700 12px Cinzel,serif;letter-spacing:.04em;color:var(--txt);border-bottom:1px dotted rgba(217,185,94,.35)}
.som li span{flex:1}
.som li b{color:var(--or2);font-size:12px}
.som li i{font-style:normal;color:var(--or);width:16px}
/* offres : format livre, 3 cartes horizontales */
.offb{display:flex;gap:11px;margin-bottom:13px;padding:0;align-items:stretch}
.offb .ph{width:96px;flex:none;background-size:cover;background-position:center 35%;border-right:1px solid var(--or);border-radius:4px 0 0 4px}
.offb .in{flex:1;padding:11px 14px 12px}
.offb .tt{display:flex;justify-content:space-between;align-items:baseline}
.offb .nm{font:700 17px/1 Cinzel,serif;color:var(--or2);letter-spacing:.04em}
.offb .pr{font:700 20px/1 Cinzel,serif;color:#fff3c8}
.offb .chk{margin-top:5px}
.offb .gain{display:flex;justify-content:space-between;align-items:center;margin-top:6px;padding-top:6px;border-top:1px solid rgba(217,185,94,.28);font-size:10.5px}
.offb .gain b{color:var(--or2);font:700 13px Cinzel,serif}
.offb .gain u{text-decoration:none;font:700 9.5px Cinzel,serif;color:var(--or);letter-spacing:.06em}
.rib{position:absolute;top:7px;right:-5px;background:linear-gradient(180deg,#f6e3a0,#c9a24a);color:#1a1307;font:700 7.5px/1 Cinzel,serif;letter-spacing:.1em;padding:5px 8px}
.note{font:italic 500 13px/1.4 "Cormorant Garamond",serif;color:var(--mut);text-align:center}
.note b{color:var(--or2);font-style:normal;font-family:Cinzel,serif;font-size:10px}
/* templates 2 colonnes */
.grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px}
.tp .ph{height:74px;background-size:cover;background-position:top;border-bottom:1px solid var(--or);border-radius:4px 4px 0 0}
.tp .t{padding:7px 9px 8px}
.tp b{display:block;font:700 13px/1.1 Cinzel,serif;color:var(--or2)}
.tp span{display:block;font-size:10.5px;color:var(--mut);margin:3px 0 4px}
.tp u{text-decoration:none;font:700 8.5px Cinzel,serif;color:var(--or);letter-spacing:.06em}
.box{padding:13px 15px;margin-bottom:13px}
.box h3{font:700 14.5px/1.15 Cinzel,serif;color:var(--or2);margin-bottom:4px;letter-spacing:.03em}
.box p{font-size:12.5px;line-height:1.55;color:var(--mut)}
.box p b{color:var(--txt)}
.lnk{display:inline-block;margin-top:8px;font:700 10.5px Cinzel,serif;color:var(--or);letter-spacing:.06em}
.row{display:flex;gap:11px;align-items:center}
.row img{width:74px;height:74px;border-radius:3px;object-fit:cover;flex:none;border:1px solid var(--or)}
.cible{display:grid;grid-template-columns:1fr 1fr 1fr;gap:5px;margin-top:7px}
.cible span{font-size:11px;border:1px solid rgba(217,185,94,.3);border-radius:3px;padding:6px 7px;color:var(--txt);background:rgba(0,0,0,.35);text-align:center}
.cas{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px}
.cas .box{margin:0}
.tag{display:inline-block;font:700 7.5px/1 Cinzel,serif;letter-spacing:.12em;padding:4px 7px;margin-bottom:7px;background:linear-gradient(180deg,#f6e3a0,#c9a24a);color:#1a1307;border-radius:2px}
.tag.g{background:none;border:1px solid var(--or);color:var(--or2)}
.big{text-align:center;padding:10px 10px 12px;margin:0 0 10px}
.big span{font:700 10px/1.3 Cinzel,serif;color:var(--txt);letter-spacing:.05em}
.qrf{display:inline-block;padding:8px 8px 0;background:#0b0a09;border:1.4px solid var(--or);box-shadow:inset 0 0 0 2.5px #0b0a09,inset 0 0 0 3.5px rgba(217,185,94,.5);border-radius:3px}
.qri{line-height:0}
.qrl{font:700 10px/1 Cinzel,serif;letter-spacing:.22em;color:var(--or2);text-align:center;padding:7px 0 8px}
.qrrow{display:flex;gap:12px;align-items:center;padding:11px}
.qrrow p{font-size:12px;line-height:1.5;color:var(--mut)}
.qrrow p b{display:block;font:700 12px/1.2 Cinzel,serif;color:var(--or2);margin-bottom:4px}
.os{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px}
.os .box{margin:0}
.regle{display:flex;gap:12px;padding:15px;margin-bottom:14px}
.regle i{font:700 25px/1 Cinzel,serif;color:var(--or2);font-style:normal;flex:none;width:22px;text-align:center}
.regle h3{font:700 13.5px/1.2 Cinzel,serif;color:var(--or2);margin-bottom:4px}
.regle p{font-size:12.5px;line-height:1.55;color:var(--mut)}
.liens{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.liens a{display:flex;flex-direction:column;gap:3px;padding:12px 13px;font:700 11.5px Cinzel,serif;letter-spacing:.03em;color:var(--txt)}
.liens a small{font:600 9px Manrope;color:var(--mut);letter-spacing:0}
.liens a.main{background:linear-gradient(180deg,#f6e3a0,#d9b95e 45%,#a57c2c);color:#1a1307}
.liens a.main small{color:#3b2c0d}
.liens a.card::before,.liens a.card::after{display:none}
/* notes */
.notes{margin-top:6px}
.notes div{height:29px;border-bottom:1px dotted rgba(217,185,94,.35)}
/* couvertures */
.cov{text-align:center;padding:26mm 16mm 18mm}
.cov .lion{width:162px;height:162px;border-radius:50%;display:block;margin:0 auto 10px;box-shadow:0 0 0 2px var(--or),0 0 0 6px #0b0a09,0 0 0 7px rgba(217,185,94,.6),0 0 45px rgba(217,185,94,.3)}
.taux{display:flex;align-items:center;justify-content:center;gap:12px;margin:8px auto 14px;padding:9px 14px;width:74%}
.taux span{font:700 11px/1.35 Cinzel,serif;color:var(--txt);text-align:left;letter-spacing:.04em}
.steps{display:flex;gap:9px;margin-top:12px}
.steps div{flex:1;padding:13px 7px 11px;font:600 11px/1.35 Manrope;color:var(--txt);text-align:center}
.steps i{display:block;font:700 16px/1 Cinzel,serif;color:var(--or2);font-style:normal;margin-bottom:4px}
.dos{text-align:center;padding:34mm 16mm}
.dos .lion{width:110px;height:110px;border-radius:50%;display:block;margin:0 auto 14px;box-shadow:0 0 0 2px var(--or),0 0 0 5px #0b0a09,0 0 0 6px rgba(217,185,94,.55)}
.dos p{font:italic 500 13px/1.5 "Cormorant Garamond",serif;color:var(--mut);margin:0 10px}
.dos .co{margin-top:16px;font:700 10.5px/1.9 Cinzel,serif;letter-spacing:.1em;color:var(--or2)}
'''

def page(inner, n, title):
    """Page intérieure numérotée. Les paires/impaires alternent la marge de reliure."""
    side = 'r' if n % 2 else 'v'
    return (f'<section class="p {side}">{frame()}'
            f'<div class="hd"><span>{title.upper()}</span><span><b>ALLIANCE GROUPE</b></span></div>'
            f'{inner}<div class="fo"><span>—</span><em>{n}</em><span>—</span></div></section>')

def build(t):
    r=t/100; P=[]
    # 1 — couverture
    P.append(f'''<section class="p cov">{frame()}
<img class="lion" src="img/lion.jpg" alt="">
{gtitle(['ALLIANCE GROUPE'],12,300)}
{gtitle(['FORMATION','AMBASSADEUR'],34,420,lh=1.08,weight=800)}
{divider(280)}
<p class="sub">Tu partages ton lien, un client achète,<br><b style="font-size:13px">TU ES PAYÉ.</b></p>
<div class="taux card">{gtitle([f'{t} %'],38,100,weight=800,lh=1)}<span>DE COMMISSION<br>SUR CHAQUE VENTE</span></div>
<div style="margin:10px 0 6px">{diamond(38)}</div>
<p class="note" style="font-size:14px">Le guide de l’ambassadeur<br><b>ÉDITION {t} %</b></p>
<p class="note" style="margin-top:14px;font-size:12px">Naples — Nantes · alliancegroupe-inc.com</p></section>''')
    # 2 — mot d'accueil + sommaire
    P.append(page(f'''{gtitle(['BIENVENUE'],23,380)}{divider(200)}
<p class="sub" style="margin-bottom:12px">Ce petit livre tient dans une poche.<br>Il dit tout ce qu’il faut savoir pour vendre.</p>
<div class="steps" style="margin:0 0 12px">
<div class="card"><i>I</i>Copie ton lien dans ton espace</div><div class="card"><i>II</i>Envoie-le à un pro</div><div class="card"><i>III</i>Il achète : tu touches {t} %</div></div>
<div class="box card" style="margin-bottom:12px"><h3>SOMMAIRE</h3>
<ul class="som">
<li><i>I</i><span>Les sites Express</span><b>3</b></li>
<li><i>II</i><span>Un modèle pour chaque pro</span><b>4</b></li>
<li><i>III</i><span>Sur-mesure &amp; sécurité</span><b>5</b></li>
<li><i>IV</i><span>Comment tu gagnes</span><b>6</b></li>
<li><i>V</i><span>Ton application</span><b>7</b></li>
<li><i>VI</i><span>Les 3 règles d’or</span><b>8</b></li>
<li><i>VII</i><span>Tous tes liens</span><b>9</b></li>
<li><i>VIII</i><span>Mes notes</span><b>10</b></li>
</ul></div>
<p class="note">Les titres en doré sont <b>CLIQUABLES</b> sur écran.<br>Sur papier, sers-toi des codes à scanner.</p>''',2,'Bienvenue'))
    # 3 — sites express
    cards=''.join(f'''<a class="offb card" href="{B}/sites-express"><div class="ph" style="background-image:url(img/{k}-top.jpg)"></div>
{'<span class="rib">LE + CHOISI</span>' if best else ''}<div class="in"><div class="tt"><div class="nm">{nom.upper()}</div><div class="pr">{eur(p)}</div></div>
<ul class="chk">{''.join(f'<li>{f}</li>' for f in feats)}</ul>
<div class="gain"><span>Ta commission : <b>{eur(p*r)}</b></span><u>VOIR L’OFFRE ❯</u></div></div></a>''' for k,nom,p,feats,best in OFFRES)
    P.append(page(f'''{gtitle(['LES SITES EXPRESS'],23,380)}{divider(200)}
<p class="sub">Prix fixe, affiché · payable en 4× PayPal</p>{cards}
<p class="note">Ensuite, la <b>MAINTENANCE</b> (dès 49 €/mois) garde le site du client à jour.</p>''',3,'I · Ce que tu vends'))
    # 4 — templates
    grid=''.join(f'<a class="tp card" href="{B}/{u}"><div class="ph" style="background-image:url(img/{k}.jpg)"></div><div class="t"><b>{n.upper()}</b><span>{d}</span><u>VOIR LE MODÈLE ❯</u></div></a>' for k,n,d,u in TPL)
    P.append(page(f'''{gtitle(['UN MODÈLE','POUR CHAQUE PRO'],21,380,lh=1.15)}{divider(200)}
<p class="sub">Montre au client son futur site, dans son métier.</p><div class="grid">{grid}</div>
<p class="note">Modèles <b>GRATUITS</b> : ils ouvrent la conversation.<br>Pour qu’on s’occupe de tout : un <b>SITE EXPRESS</b>.</p>''',4,'II · Les modèles'))
    # 5 — sur-mesure & sécurité
    P.append(page(f'''{gtitle(['SUR-MESURE &amp; SÉCURITÉ'],21,380)}{divider(200)}
<p class="sub">Pour les gros projets, et les pros qui ont déjà un site.</p>
<div class="box card"><h3>LE SUR-MESURE</h3><p>Un projet qui sort du cadre (site complexe, application, refonte complète) ? On fait un <b>devis</b>. Plus le projet est gros, plus tes <b>{t} %</b> pèsent lourd.</p><a class="lnk" href="{B}/sur-mesure">DEMANDER UN DEVIS ❯</a></div>
<a class="box card row" href="{B}/audit-securite"><img src="img/securite.jpg" alt=""><div><h3>AUDIT &amp; SÉCURITÉ</h3><p>Le client a déjà un site ? On vérifie qu’il n’expose pas son entreprise à un piratage. <b>Test gratuit</b> pour commencer.</p><span class="lnk">VOIR L’AUDIT ❯</span></div></a>
<div class="box card"><h3>À QUI VENDRE ?</h3><p>À tout pro <b>sans site</b>, ou avec un site <b>vieux, lent ou négligé</b>.</p>
<div class="cible"><span>Restaurants</span><span>Coiffeurs</span><span>Artisans, BTP</span><span>Coachs</span><span>Aide à domicile</span><span>Commerces</span></div>
<a class="lnk" href="{B}/tester-mon-site">ASTUCE : FAIS-LUI TESTER SON SITE ❯</a></div>''',5,'III · Aller plus loin'))
    # 6 — comment tu gagnes
    P.append(page(f'''{gtitle(['LE LIEN FAIT TOUT'],23,380)}{divider(200)}
<p class="sub">Le client clique, il achète : la vente est pour toi.</p>
<div class="big card">{gtitle([f'{t} %'],46,200,weight=800,lh=1)}<span>SUR CHAQUE VENTE · PAYÉ PAR PAYPAL APRÈS VALIDATION</span></div>
<div class="cas"><div class="box card"><span class="tag">AUTOMATIQUE</span><h3>PAR TON LIEN</h3><p>Commission <b>enregistrée toute seule</b>, rien à déclarer. Le client a <b>30 jours</b> pour acheter.</p></div>
<div class="box card"><span class="tag g">À DÉCLARER</span><h3>EN DIRECT</h3><p>Signé avec toi <b>de la main à la main</b> ? Alors seulement, tu déclares.</p><a class="lnk" href="{B}/ambassadeurs">DÉCLARER ❯</a></div></div>
<div class="box card"><h3>UNE PAGE PRÉCISE ?</h3><p>Ajoute ton code à la fin de n’importe quel lien du site :<br><b style="font-family:monospace;color:var(--or2)">…/wordpress-barber?ref=TONCODE</b></p></div>
<div class="box card" style="margin-bottom:0"><h3>LE CLASSEMENT</h3><p>Les meilleurs ambassadeurs du mois, en direct.</p><a class="lnk" href="{B}/classement">VOIR LE CLASSEMENT ❯</a></div>''',6,'IV · Comment tu gagnes'))
    # 7 — application
    P.append(page(f'''{gtitle(['TOUT DANS TA POCHE'],23,380)}{divider(200)}
<p class="sub">L’appli « Ambassadeurs » : ton lien, tes ventes, tes gains.</p>
<div class="os"><div class="box card"><h3>ANDROID</h3><p>Ouvre le lien dans <b>Chrome</b>, puis menu <b>⋮</b> → <b>Installer l’application</b>.</p></div>
<div class="box card"><h3>IPHONE</h3><p>Ouvre le lien dans <b>Safari</b>, puis <b>Partager</b> → <b>Sur l’écran d’accueil</b>.</p></div></div>
<div class="card qrrow" style="margin-bottom:10px">{qr_gold(B+'/espace-ambassadeur/?install=amb',120)}<p><b>INSTALLE-LA MAINTENANT</b>Scanne ce code avec l’appareil photo de ton téléphone : l’installation démarre toute seule.</p></div>
<div class="box card" style="margin-bottom:0"><h3>LE STUDIO CRÉATIF</h3><p>Crée des <b>vidéos et visuels</b> prêts à partager sur tes réseaux, en un clic.</p><a class="lnk" href="{B}/studio">OUVRIR LE STUDIO ❯</a></div>''',7,'V · Ton application'))
    # 8 — règles d'or
    P.append(page(f'''{gtitle(['LES 3 RÈGLES D’OR'],23,380)}{divider(200)}
<p class="sub">C’est ce qui fait qu’on nous fait confiance, à toi comme à nous.</p>
<div class="regle card"><i>I</i><div><h3>HONNÊTE, JAMAIS DE SPAM</h3><p>Pas de messages en masse, pas de fausses promesses. Tu recommandes ce que tu connais, à des gens qui peuvent en avoir besoin.</p></div></div>
<div class="regle card"><i>II</i><div><h3>AVOCATS : E-MAIL OU COURRIER</h3><p>Leur profession encadre la prospection : <b style="color:var(--txt)">pas d’appel, pas de SMS, pas de démarchage</b> sur place. Un e-mail ou un courrier, courtois.</p></div></div>
<div class="regle card"><i>III</i><div><h3>DIS QUE C’EST TOI</h3><p>Présente-toi comme ambassadeur d’Alliance Groupe. La transparence rassure, et elle fait vendre.</p></div></div>
<div style="text-align:center;margin:6px 0 8px">{diamond(36)}</div>
<p class="note">Une question ? <b>CONTACT@ALLIANCEGROUPE-INC.COM</b></p>''',8,'VI · Les règles'))
    # 9 — liens
    L=[('espace-ambassadeur/','Mon espace','ton lien, tes gains',1),('espace-ambassadeur/?install=amb','Installer l’appli','Android & iPhone',0),
       ('sites-express','Sites Express','490 · 890 · 1 490 €',0),('templates-wordpress','Modèles par métier','6 métiers',0),
       ('sur-mesure','Sur-mesure','sur devis',0),('audit-securite','Audit & sécurité','sites existants',0),
       ('tester-mon-site','Tester un site','gratuit',0),('ambassadeurs','Déclarer en direct','main à la main',0),
       ('classement','Le classement','du mois',0),('studio','Studio créatif','visuels & vidéos',0)]
    ls=''.join(f'<a class="card {"main" if mm else ""}" href="{B}/{u}"><span>{n.upper()}</span><small>{d}</small></a>' for u,n,d,mm in L)
    P.append(page(f'''{gtitle(['TOUS TES LIENS'],23,380)}{divider(200)}
<p class="sub">Sur écran : touche un bouton.<br>Sur papier : tape l’adresse ou scanne page 11.</p>
<div class="liens">{ls}</div>
<p class="note" style="margin-top:10px">Toutes les adresses commencent par <b>ALLIANCEGROUPE-INC.COM</b></p>''',9,'VII · Tes liens'))
    # 10 — notes
    P.append(page(f'''{gtitle(['MES NOTES'],23,380)}{divider(200)}
<p class="sub">Les pros à contacter, les rendez-vous, les rappels.</p>
<div class="box card notes">{''.join('<div></div>' for _ in range(13))}</div>''',10,'VIII · Mes notes'))
    # 11 — à toi de jouer (page de droite)
    P.append(page(f'''{gtitle(['À TOI DE JOUER'],23,380)}{divider(200)}
<p class="sub">Un lien, un client, <b>{t} % POUR TOI.</b><br>C’est aussi simple que ça.</p>
<div style="text-align:center;margin:8px 0 10px">{qr_gold(B+'/espace-ambassadeur/',168,'SCANNE-MOI')}</div>
<p class="note" style="margin-bottom:12px">Ton espace ambassadeur, depuis ton téléphone.</p>
<a class="btn" href="{B}/espace-ambassadeur/" style="margin:0 12mm">OUVRIR MON ESPACE ❯</a>''',11,'Pour commencer'))
    # 12 — dos de couverture
    P.append(f'''<section class="p dos">{frame()}
<img class="lion" src="img/lion.jpg" alt="">
{gtitle(['ALLIANCE GROUPE'],15,320)}
{divider(220)}
<p>Studio web &amp; sécurité.<br>De Naples à Nantes.</p>
<div class="co">ALLIANCEGROUPE-INC.COM<br>CONTACT@ALLIANCEGROUPE-INC.COM</div>
<div style="margin-top:18px">{diamond(34)}</div>
<p class="note" style="margin-top:14px">Guide de l’ambassadeur · Édition {t} %<br>Document interne — ne pas diffuser au public.</p></section>''')
    return ('<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8">'
            f'<title>Formation Ambassadeur {t} % — livre</title>'
            '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;700;800;900&family=Cormorant+Garamond:ital,wght@1,500;1,700&family=Manrope:wght@400;600;700;800&display=swap">'
            f'<style>{CSS}</style></head><body>{"".join(P)}</body></html>')

if __name__=='__main__':
    for t in (10,20,50):
        open(f'livre-{t}.html','w').write(build(t))
    print('ok')
