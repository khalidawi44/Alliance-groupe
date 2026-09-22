import qrcode, qrcode.image.svg, io, sys
B='https://alliancegroupe-inc.com'
def qr_svg(url):
    q=qrcode.QRCode(border=1,error_correction=qrcode.constants.ERROR_CORRECT_M); q.add_data(url); q.make(fit=True)
    m=q.get_matrix(); n=len(m)
    rects=''.join(f'<rect x="{x}" y="{y}" width="1.02" height="1.02"/>' for y,r in enumerate(m) for x,v in enumerate(r) if v)
    return f'<svg viewBox="0 0 {n} {n}" xmlns="http://www.w3.org/2000/svg" shape-rendering="crispEdges"><rect width="{n}" height="{n}" fill="#fff"/><g fill="#0a0a0f">{rects}</g></svg>'

def eur(v):
    s=f'{v:,.0f}'.replace(',',' ') if abs(v-round(v))<.01 else f'{v:,.2f}'.replace(',',' ').replace('.',',')
    return s+' €'

CSS='''
@page{size:420px 880px;margin:0}
*{box-sizing:border-box;margin:0;padding:0}
:root{--noir:#0a0a0f;--carte:#15151c;--carte2:#1c1c25;--or:#d4b45c;--or2:#f0d98f;--or3:#a8832f;--txt:#ece8dc;--mut:#a39f93;--ligne:rgba(212,180,92,.28)}
html,body{background:var(--noir)}
body{font-family:Manrope,system-ui,sans-serif;color:var(--txt);-webkit-print-color-adjust:exact;print-color-adjust:exact}
a{color:inherit;text-decoration:none}
.p{width:420px;height:880px;position:relative;overflow:hidden;page-break-after:always;padding:30px 26px 58px;
 background:radial-gradient(120% 60% at 100% 0,rgba(212,180,92,.10),transparent 60%),radial-gradient(90% 50% at 0 100%,rgba(212,180,92,.07),transparent 60%),var(--noir)}
.p::before{content:"";position:absolute;inset:10px;border:1px solid var(--ligne);border-radius:18px;pointer-events:none}
.p::after{content:"";position:absolute;left:50%;top:4px;width:14px;height:14px;transform:translateX(-50%) rotate(45deg);background:var(--noir);border:1px solid var(--or)}
.hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px}
.hd img{height:30px}
.hd span{font:800 9px/1 Manrope;letter-spacing:.22em;color:var(--or);text-transform:uppercase}
.num{font:600 italic 13px Playfair Display,serif;color:var(--or)}
.kick{display:inline-block;font:800 9.5px/1 Manrope;letter-spacing:.2em;text-transform:uppercase;color:var(--or2);border:1px solid var(--ligne);background:rgba(212,180,92,.08);padding:7px 12px;border-radius:99px;margin-bottom:12px}
h1,h2,h3{font-family:"Playfair Display",Georgia,serif;font-weight:800;letter-spacing:-.01em}
h2{font-size:30px;line-height:1.05;margin-bottom:8px}
h2 em,h1 em{font-style:italic;font-weight:600;color:#e3c375}
.lead{color:var(--mut);font-size:13.5px;line-height:1.5;margin-bottom:16px}
.or{color:#e3c375}
.btn{display:flex;align-items:center;justify-content:center;gap:8px;background:linear-gradient(135deg,var(--or2),var(--or) 50%,var(--or3));color:#16110a;font:800 14px/1 Manrope;padding:14px 18px;border-radius:99px;box-shadow:0 10px 26px -10px rgba(212,180,92,.7)}
.btn.ghost{background:transparent;color:var(--or2);border:1.5px solid var(--or);box-shadow:none}
.pied{position:absolute;left:26px;right:26px;bottom:22px;display:flex;justify-content:space-between;font:600 9px/1 Manrope;color:#6f6b62;letter-spacing:.06em}
.pied b{color:var(--or);font-weight:800}
/* cover */
.cover{text-align:center;padding-top:74px}
.cover::before{inset:10px;border-color:rgba(212,180,92,.5)}
.cover .lion{width:210px;height:210px;border-radius:50%;margin:6px auto 18px;display:block;box-shadow:0 0 0 1px var(--or),0 0 60px -6px rgba(212,180,92,.45)}
.cover .ag{font:800 11px/1 Manrope;letter-spacing:.42em;color:var(--or);margin-bottom:12px}
.cover h1{font-size:42px;line-height:.98;margin-bottom:14px}
.cover .acc{font:600 italic 17px/1.35 "Playfair Display",serif;color:var(--txt);margin:0 8px 22px}
.taux{display:inline-flex;align-items:center;gap:12px;border:1px solid var(--or);border-radius:16px;padding:10px 18px;margin-bottom:20px;background:rgba(212,180,92,.07)}
.taux b{font:800 40px/1 "Playfair Display",serif}
.taux span{font:700 12px/1.3 Manrope;text-align:left;color:var(--txt)}
.steps{display:flex;gap:8px;margin-top:18px}
.steps div{flex:1;background:var(--carte);border:1px solid var(--ligne);border-radius:14px;padding:10px 6px;font:700 11px/1.3 Manrope;color:var(--txt)}
.steps i{display:block;font:800 italic 20px/1 "Playfair Display",serif;margin-bottom:5px;color:var(--or)}
/* offres */
.off{display:block;background:var(--carte);border:1px solid var(--ligne);border-radius:16px;overflow:hidden;margin-bottom:9px;position:relative}
.off img{width:100%;height:62px;object-fit:cover;display:block;border-bottom:1px solid var(--or)}
.off .in{padding:8px 14px 9px;display:grid;grid-template-columns:1fr auto;gap:2px 10px;align-items:end}
.off h3{font-size:21px}
.off .prix{font:800 22px/1 "Playfair Display",serif;text-align:right}
.off ul{list-style:none;grid-column:1/-1;font-size:11px;color:var(--mut);line-height:1.45;margin-top:2px}
.off li::before{content:"✓ ";color:var(--or);font-weight:800}
.off .gain{grid-column:1/-1;display:flex;justify-content:space-between;align-items:center;margin-top:6px;padding-top:6px;border-top:1px dashed var(--ligne);font-size:11.5px}
.off .gain b{color:var(--or2);font-weight:800;font-size:14px}
.off .gain u{text-decoration:none;color:var(--or);font-weight:800}
.badge{position:absolute;top:10px;right:10px;background:var(--or);color:#16110a;font:800 9px/1 Manrope;letter-spacing:.12em;padding:6px 10px;border-radius:99px;text-transform:uppercase}
.note{font-size:11px;color:var(--mut);line-height:1.45;text-align:center}
.note b{color:var(--or2)}
/* templates */
.grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px}
.tp{display:block;background:var(--carte);border:1px solid var(--ligne);border-radius:14px;overflow:hidden}
.tp img{width:100%;height:92px;object-fit:cover;object-position:top;display:block}
.tp div{padding:9px 11px 10px}
.tp b{font:800 16px/1.1 "Playfair Display",serif;display:block}
.tp span{font-size:10.5px;color:var(--mut);display:block;margin:2px 0 5px}
.tp u{text-decoration:none;font:800 10.5px Manrope;color:var(--or)}
.box{background:var(--carte);border:1px solid var(--ligne);border-radius:16px;padding:14px 16px;margin-bottom:12px}
.box h3{font-size:19px;margin-bottom:5px}
.box p{font-size:12.5px;line-height:1.5;color:var(--mut)}
.box p b{color:var(--txt)}
.row{display:flex;gap:12px;align-items:center}
.row img{width:92px;height:92px;border-radius:12px;object-fit:cover;flex:none}
.lnk{display:inline-block;margin-top:8px;font:800 12px Manrope;color:var(--or)}
.cible{display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-top:8px}
.cible span{font-size:11.5px;background:var(--carte2);border-radius:10px;padding:8px 10px;color:var(--txt)}
/* gains */
.big{text-align:center;border:1px solid var(--or);border-radius:20px;padding:16px;margin-bottom:14px;background:radial-gradient(100% 100% at 50% 0,rgba(212,180,92,.16),transparent 70%),var(--carte)}
.big b{display:block;font:800 64px/1 "Playfair Display",serif}
.big span{font:700 12.5px Manrope;color:var(--txt)}
.cas{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px}
.cas .box{margin:0;padding:12px 13px}
.cas h3{font-size:15px}
.cas .tag{font:800 9px/1 Manrope;letter-spacing:.14em;text-transform:uppercase;color:#16110a;background:var(--or);padding:5px 8px;border-radius:99px;display:inline-block;margin-bottom:8px}
.cas .tag.g{background:transparent;border:1px solid var(--or);color:var(--or)}
.cas p{font-size:11.5px}
ol.l{list-style:none;counter-reset:s}
ol.l li{counter-increment:s;position:relative;padding:0 0 12px 40px;font-size:13px;line-height:1.45;color:var(--txt)}
ol.l li::before{content:counter(s);position:absolute;left:0;top:-2px;width:28px;height:28px;border-radius:50%;border:1px solid var(--or);display:grid;place-items:center;font:800 italic 14px "Playfair Display",serif;color:var(--or)}
ol.l li small{display:block;color:var(--mut);font-size:11.5px}
.qr{display:flex;gap:14px;align-items:center;background:var(--carte);border:1px solid var(--ligne);border-radius:16px;padding:12px}
.qr svg{width:104px;height:104px;border-radius:8px;flex:none;border:3px solid #fff}
.qr p{font-size:12px;line-height:1.45;color:var(--mut)}
.qr p b{color:var(--txt);font-size:13px;display:block;margin-bottom:3px}
.os{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px}
.os .box{margin:0}
.os h3{font-size:16px}
.os p{font-size:11.5px}
.regle{display:flex;gap:12px;margin-bottom:12px;background:var(--carte);border:1px solid var(--ligne);border-left:4px solid var(--or);border-radius:14px;padding:14px}
.regle i{font:800 italic 30px/1 "Playfair Display",serif;color:var(--or);flex:none;width:26px}
.regle h3{font-size:17px;margin-bottom:4px}
.regle p{font-size:12px;line-height:1.5;color:var(--mut)}
.liens{display:grid;gap:7px}
.liens a{display:flex;justify-content:space-between;align-items:center;background:var(--carte);border:1px solid var(--ligne);border-radius:12px;padding:10px 13px;font:700 12.5px Manrope}
.liens a small{font:600 10px Manrope;color:var(--mut)}
.liens a::after{content:"→";color:var(--or);font-weight:800;margin-left:8px}
.liens a.main{background:linear-gradient(135deg,var(--or2),var(--or) 50%,var(--or3));color:#16110a;border:0}
.liens a.main small{color:#3b2e12}.liens a.main::after{color:#16110a}
'''

OFFRES=[('essentiel','Essentiel',490,['Site 1 page premium, à la marque du client','Formulaire de contact + Google Maps','Livré en 5 jours'],False),
        ('pro','Pro',890,['Jusqu’à 6 pages + blog / actualités','Prise de RDV en ligne, SEO Google','Livré en 8 jours'],True),
        ('boutique','Boutique',1490,['Boutique en ligne, jusqu’à 30 produits','Paiement CB & PayPal, gestion des stocks','Livré en 12 jours'],False)]
TPL=[('avocat','Avocat','Cabinet, juriste','wordpress-avocat'),('restaurant','Restaurant','Bistrot, bar, café','wordpress-restaurant'),
     ('barber','Barbier','Coiffeur, barber shop','wordpress-barber'),('coach','Coach','Sport, mental, formateur','wordpress-coach'),
     ('artisan','Artisan','Plombier, électricien, BTP','wordpress-artisan'),('domicile','Aide à domicile','Seniors, enfants','wordpress-domicile')]

def page(inner, n, cls=''):
    return f'<section class="p {cls}"><div class="hd"><img src="img/logo.png" alt="Alliance Groupe"><span>Formation ambassadeur</span><span class="num">{n:02d} / 07</span></div>{inner}<div class="pied"><span><b>ALLIANCE GROUPE</b> · De Naples à Nantes</span><span>alliancegroupe-inc.com</span></div></section>'

def build(t):
    r=t/100
    P=[]
    P.append(f'''<section class="p cover">
<img class="lion" src="img/lion.jpg" alt="">
<div class="ag">ALLIANCE GROUPE</div>
<h1>Formation<br><em>Ambassadeur</em></h1>
<p class="acc">Tu partages ton lien, un client achète, tu es payé.</p>
<div class="taux"><b class="or">{t} %</b><span>de commission<br>sur chaque vente</span></div>
<a class="btn" href="{B}/espace-ambassadeur/">Ouvrir mon espace ambassadeur →</a>
<div class="steps"><div><i>1</i>Copie ton lien dans ton espace</div><div><i>2</i>Envoie-le à un pro</div><div><i>3</i>Il achète : tu touches {t} %</div></div>
<div class="pied"><span><b>ALLIANCE GROUPE</b> · De Naples à Nantes</span><span>Lecture : 5 minutes</span></div></section>''')
    cards=''.join(f'''<a class="off" href="{B}/sites-express"><img src="img/{k}-top.jpg" alt="">{'<span class="badge">Le + choisi</span>' if best else ''}
<div class="in"><h3>{nom}</h3><div class="prix or">{eur(p)}</div><ul>{''.join(f'<li>{f}</li>' for f in feats)}</ul>
<div class="gain"><span>Ta commission : <b>{eur(p*r)}</b></span><u>Voir l’offre →</u></div></div></a>''' for k,nom,p,feats,best in OFFRES)
    P.append(page(f'''<span class="kick">Ce que tu vends</span><h2>Les sites <em>Express</em></h2>
<p class="lead" style="margin-bottom:12px">Prix fixe, affiché, sans surprise. Payable en 4× PayPal.</p>{cards}
<p class="note">Ensuite, la <b>maintenance mensuelle</b> (dès 49 €/mois) garde le site du client à jour.</p>''',1))
    grid=''.join(f'<a class="tp" href="{B}/{u}"><img src="img/{k}.jpg" alt=""><div><b>{n}</b><span>{d}</span><u>Voir le modèle →</u></div></a>' for k,n,d,u in TPL)
    P.append(page(f'''<span class="kick">Par métier</span><h2>Un modèle pour <em>chaque pro</em></h2>
<p class="lead">Montre au client son futur site, dans son métier. Ça parle tout de suite.</p><div class="grid">{grid}</div>
<p class="note">Ces modèles sont <b>gratuits</b> : ils ouvrent la conversation. Le client qui veut qu’on s’occupe de tout passe par un <b>site Express</b>.</p>''',2))
    P.append(page(f'''<span class="kick">Pour aller plus loin</span><h2>Sur-mesure <em>&amp; sécurité</em></h2>
<p class="lead">Pour les gros projets, et pour les pros qui ont déjà un site.</p>
<div class="box"><h3>Le sur-mesure</h3><p>Un projet qui sort du cadre (site complexe, application, refonte complète) ? On fait un <b>devis</b>. Plus le projet est gros, plus ta commission de <b>{t} %</b> est grosse.</p><a class="lnk" href="{B}/sur-mesure">Demander un devis sur-mesure →</a></div>
<a class="box row" href="{B}/audit-securite" style="display:flex"><img src="img/securite.jpg" alt=""><div><h3>Audit &amp; sécurité</h3><p>Le client a déjà un site ? On vérifie qu’il n’expose pas son entreprise à un piratage. <b>Test gratuit</b> pour commencer.</p><span class="lnk">Voir l’audit →</span></div></a>
<div class="box"><h3>À qui vendre ?</h3><p>À tout pro <b>sans site</b>, ou avec un site <b>vieux, lent ou négligé</b>.</p>
<div class="cible"><span>🍽️ Restaurants, bars</span><span>✂️ Coiffeurs, barbiers</span><span>🔧 Artisans, BTP</span><span>🏋️ Coachs, sport</span><span>🏠 Aide à domicile</span><span>🛍️ Commerces</span></div>
<a class="lnk" href="{B}/tester-mon-site">Astuce : fais-lui tester son site gratuitement →</a></div>''',3))
    P.append(page(f'''<span class="kick">Comment tu gagnes</span><h2>Le lien <em>fait tout</em></h2>
<p class="lead">Ton lien personnel est dans ton espace. Le client clique, il achète : la vente est pour toi.</p>
<div class="big"><b class="or">{t} %</b><span>sur chaque vente · payé par PayPal après validation</span></div>
<div class="cas"><div class="box"><span class="tag">Automatique</span><h3>Vente par ton lien</h3><p>Commission <b>enregistrée toute seule</b>. Rien à déclarer. Le client a <b>30 jours</b> pour acheter.</p></div>
<div class="box"><span class="tag g">À déclarer</span><h3>Vente en direct</h3><p>Le client a signé avec toi, <b>de la main à la main</b> ? Alors seulement, tu la déclares.</p><a class="lnk" href="{B}/ambassadeurs">Déclarer →</a></div></div>
<div class="box"><h3>Une page précise ?</h3><p>Ajoute ton code à la fin de n’importe quel lien du site :<br><b style="font-family:monospace;color:var(--or2)">…/wordpress-barber<span style="color:var(--txt)">?ref=</span>TONCODE</b></p></div>
<div class="box"><h3>Le classement</h3><p>Les meilleurs ambassadeurs du mois, en direct.</p><a class="lnk" href="{B}/classement">Voir le classement →</a></div>''',4))
    P.append(page(f'''<span class="kick">Ton application</span><h2>Tout dans <em>ta poche</em></h2>
<p class="lead">Installe l’appli « Ambassadeurs » : ton lien, tes ventes et tes gains en un geste.</p>
<a class="btn" href="{B}/espace-ambassadeur/?install=amb" style="margin-bottom:14px">📲 Installer l’application</a>
<div class="os"><div class="box"><h3>Android</h3><p>Ouvre le lien dans <b>Chrome</b>, puis menu <b>⋮</b> → <b>Installer l’application</b>.</p></div>
<div class="box"><h3>iPhone</h3><p>Ouvre le lien dans <b>Safari</b>, puis <b>Partager</b> → <b>Sur l’écran d’accueil</b>.</p></div></div>
<div class="qr">{qr_svg(B+'/espace-ambassadeur/?install=amb')}<p><b>Tu lis ce PDF sur un ordinateur ?</b>Scanne ce code avec l’appareil photo de ton téléphone : l’installation démarre.</p></div>
<div class="box" style="margin-top:12px"><h3>Le Studio créatif</h3><p>Crée des <b>vidéos et visuels</b> prêts à partager sur tes réseaux, en un clic.</p><a class="lnk" href="{B}/studio">Ouvrir le Studio →</a></div>''',5))
    P.append(page('''<span class="kick">Les 3 règles d’or</span><h2>On vend <em>proprement</em></h2>
<p class="lead">C’est ce qui fait qu’on nous fait confiance, à toi comme à nous.</p>
<div class="regle"><i>1</i><div><h3>Honnête, jamais de spam</h3><p>Pas de messages en masse, pas de fausses promesses. Tu recommandes ce que tu connais, à des gens qui peuvent en avoir besoin.</p></div></div>
<div class="regle"><i>2</i><div><h3>Avocats : e-mail ou courrier</h3><p>Leur profession encadre la prospection : <b style="color:var(--txt)">pas d’appel, pas de SMS, pas de démarchage</b> sur place. Uniquement un e-mail ou un courrier, courtois.</p></div></div>
<div class="regle"><i>3</i><div><h3>Dis que c’est toi</h3><p>Présente-toi comme ambassadeur d’Alliance Groupe. La transparence rassure et elle fait vendre.</p></div></div>
<p class="note" style="margin-top:6px">Un doute ? Une question ? Écris-nous : <b>contact@alliancegroupe-inc.com</b></p>''',6))
    L=[('espace-ambassadeur/','Mon espace ambassadeur','ton lien, tes gains',1),('espace-ambassadeur/?install=amb','Installer l’application','Android & iPhone',0),
       ('sites-express','Sites Express','490 € · 890 € · 1 490 €',0),('templates-wordpress','Modèles par métier','6 métiers',0),
       ('sur-mesure','Sur-mesure','sur devis',0),('audit-securite','Audit & sécurité','sites existants',0),('tester-mon-site','Tester un site','gratuit',0),
       ('ambassadeurs','Déclarer une vente en direct','main à la main',0),('classement','Le classement','du mois',0),('studio','Studio créatif','visuels & vidéos',0)]
    ls=''.join(f'<a class="{"main" if m else ""}" href="{B}/{u}"><span>{n}</span><small>{d}</small></a>' for u,n,d,m in L)
    P.append(page(f'''<span class="kick">Tous tes liens</span><h2>À garder <em>sous la main</em></h2>
<p class="lead" style="margin-bottom:12px">Touche un bouton, la page s’ouvre.</p><div class="liens">{ls}<a href="mailto:contact@alliancegroupe-inc.com"><span>Une question ?</span><small>contact@alliancegroupe-inc.com</small></a></div>''',7))
    P.append(f'''<section class="p cover" style="padding-top:120px"><img class="lion" src="img/lion.jpg" alt="" style="width:170px;height:170px">
<h1 style="font-size:36px">À toi de <em>jouer</em></h1><p class="acc">Un lien, un client, {t} % pour toi.<br>C’est aussi simple que ça.</p>
<a class="btn" href="{B}/espace-ambassadeur/">Ouvrir mon espace →</a>
<div class="qr" style="margin-top:22px;text-align:left">{qr_svg(B+'/espace-ambassadeur/')}<p><b>Ton espace, depuis ton téléphone</b>Scanne ce code pour ouvrir ton espace ambassadeur.</p></div>
<div class="pied"><span><b>ALLIANCE GROUPE</b> · De Naples à Nantes</span><span>alliancegroupe-inc.com</span></div></section>''')
    return f'<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><title>Formation Ambassadeur {t} % — Alliance Groupe</title><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,800;1,600;1,800&family=Manrope:wght@400;600;700;800&display=swap"><style>{CSS}</style></head><body>{"".join(P)}</body></html>'

for t in (10,20,50):
    open(f'formation-{t}.html','w').write(build(t))
print('ok')
