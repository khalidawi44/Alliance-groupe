import qrcode, base64
B='https://alliancegroupe-inc.com'
def b64(p): return base64.b64encode(open(p,'rb').read()).decode()
LION64=b64('img/lion-head.png')

GRAD='''<defs><linearGradient id="%s" x1="0" y1="0" x2="1" y2="1">
<stop offset="0" stop-color="#fff1b8"/><stop offset=".28" stop-color="#e6c56b"/><stop offset=".55" stop-color="#b88a2c"/><stop offset=".78" stop-color="#f0d68a"/><stop offset="1" stop-color="#9a7224"/></linearGradient></defs>'''
_n=[0]
def gid():
    _n[0]+=1; return f'g{_n[0]}'

def gtitle(lines, size=34, w=368, font="Cinzel", weight=700, lh=1.12, italic=False, anchor='middle'):
    """Titre or métallique en SVG (le dégradé tient à l'export PDF)."""
    i=gid(); h=int(size*lh*len(lines)+size*0.35)
    x={'middle':w/2,'start':0}[anchor]
    ts=''.join(f'<tspan x="{x}" dy="{size*lh if k else size}">{t}</tspan>' for k,t in enumerate(lines))
    st='font-style:italic;' if italic else ''
    return (f'<svg class="gt" width="{w}" height="{h}" viewBox="0 0 {w} {h}">{GRAD%i}'
            f'<text x="{x}" y="0" text-anchor="{anchor}" fill="url(#{i})" stroke="#3a2a08" stroke-width=".35" '
            f'style="font-family:{font},serif;font-weight:{weight};font-size:{size}px;letter-spacing:.02em;{st}filter:drop-shadow(0 2px 3px rgba(0,0,0,.8))">{ts}</text></svg>')

def diamond(s=46):
    i=gid()
    return (f'<svg width="{s}" height="{s*0.8:.0f}" viewBox="0 0 100 80">{GRAD%i}'
      f'<defs><radialGradient id="h{i}" cx=".5" cy=".4" r=".6"><stop offset="0" stop-color="#fff" stop-opacity=".9"/><stop offset="1" stop-color="#fff" stop-opacity="0"/></radialGradient></defs>'
      f'<polygon points="20,8 80,8 98,28 50,78 2,28" fill="url(#{i})" stroke="#fff3c4" stroke-width="1.2"/>'
      f'<polygon points="20,8 35,28 50,8 65,28 80,8" fill="#fff6d0" opacity=".55"/>'
      f'<polyline points="2,28 98,28" stroke="#6b4b12" stroke-width="1" fill="none"/>'
      f'<polyline points="35,28 50,78 65,28" stroke="#6b4b12" stroke-width="1" fill="none"/>'
      f'<polyline points="2,28 50,78 98,28" stroke="#fff3c4" stroke-width=".6" fill="none"/>'
      f'<ellipse cx="50" cy="30" rx="40" ry="26" fill="url(#h{i})" opacity=".5"/></svg>')

def corner(i):
    return (f'<svg viewBox="0 0 90 90" width="90" height="90">{GRAD%i}<g fill="none" stroke="url(#{i})" stroke-linecap="round">'
      '<path d="M6 84 V14 Q6 6 14 6 H84" stroke-width="2"/>'
      '<path d="M12 84 V20 Q12 12 20 12 H84" stroke-width=".8"/>'
      '<path d="M20 60 C20 34 34 20 60 20" stroke-width="1.2"/>'
      '<path d="M18 30 C26 30 30 26 30 18 C30 12 24 10 21 14 C18 18 23 22 26 19" stroke-width="1.4"/>'
      '<path d="M40 22 C44 14 54 12 60 16 C54 16 48 18 44 24" stroke-width="1"/>'
      '<path d="M22 40 C14 44 12 54 16 60 C16 54 18 48 24 44" stroke-width="1"/>'
      '<path d="M30 30 L44 44" stroke-width=".8"/></g>'
      f'<g fill="url(#{i})"><path d="M30 30 L36 33 L33 36 Z"/><circle cx="60" cy="20" r="2.2"/><circle cx="20" cy="60" r="2.2"/><path d="M84 6 l4 -3 l-1 4 z"/></g></svg>')

def frame():
    i=gid(); c=corner(i)
    return (f'<div class="fr"><div class="c tl">{c}</div><div class="c tr">{c}</div><div class="c bl">{c}</div><div class="c br">{c}</div>'
            f'<div class="topd">{diamond(30)}</div></div>')

def divider(w=300):
    i=gid()
    return (f'<svg class="dv" width="{w}" height="18" viewBox="0 0 300 18">{GRAD%i}<g stroke="url(#{i})" fill="none">'
      '<path d="M0 9 H118" stroke-width="1"/><path d="M182 9 H300" stroke-width="1"/>'
      '<path d="M118 9 C128 1 136 1 142 9 C136 17 128 17 118 9Z" stroke-width="1"/><path d="M182 9 C172 1 164 1 158 9 C164 17 172 17 182 9Z" stroke-width="1"/></g>'
      f'<polygon points="150,2 157,9 150,16 143,9" fill="url(#{i})"/></svg>')

def qr_gold(url, size=150, label='SCANNE-MOI'):
    q=qrcode.QRCode(border=2,error_correction=qrcode.constants.ERROR_CORRECT_H); q.add_data(url); q.make(fit=True)
    m=q.get_matrix(); n=len(m); i=gid()
    c=n/2; r=n*0.105
    rects=''.join(f'<rect x="{x}" y="{y}" width="1.03" height="1.03"/>' for y,row in enumerate(m) for x,v in enumerate(row) if v and ((x+.5-c)**2+(y+.5-c)**2)>(r+.6)**2)
    svg=(f'<svg width="{size}" height="{size}" viewBox="0 0 {n} {n}" shape-rendering="crispEdges">{GRAD%i}'
         f'<rect width="{n}" height="{n}" fill="url(#{i})"/><g fill="#0b0a08">{rects}</g>'
         f'<circle cx="{c}" cy="{c}" r="{r}" fill="#0b0a08" stroke="url(#{i})" stroke-width=".35" shape-rendering="geometricPrecision"/>'
         f'<image href="data:image/png;base64,{LION64}" x="{c-r*0.8}" y="{c-r*0.86}" width="{r*1.6}" height="{r*1.7}"/></svg>')
    return f'<div class="qrf"><div class="qri">{svg}</div><div class="qrl">{label}</div></div>'

def eur(v):
    return (f'{v:,.0f}'.replace(',',' ') if abs(v-round(v))<.01 else f'{v:,.2f}'.replace(',',' ').replace('.',','))+' €'

CSS='''
@page{size:420px 880px;margin:0}
*{box-sizing:border-box;margin:0;padding:0}
:root{--or:#d9b95e;--or2:#f3dc92;--or3:#a57c2c;--txt:#efe9d8;--mut:#b2ab98;--ligne:rgba(217,185,94,.45)}
html,body{background:#0b0a09}
body{font-family:Manrope,system-ui,sans-serif;color:var(--txt);-webkit-print-color-adjust:exact;print-color-adjust:exact}
a{color:inherit;text-decoration:none}
.p{width:420px;height:880px;position:relative;overflow:hidden;page-break-after:always;padding:40px 30px 64px;background:#0b0a09 url(img/bg.jpg) center/cover}
.fr{position:absolute;inset:0;pointer-events:none}
.fr::before{content:"";position:absolute;inset:12px;border:1.6px solid var(--or);border-radius:3px;box-shadow:0 0 12px rgba(217,185,94,.25)}
.fr::after{content:"";position:absolute;inset:18px;border:.7px solid rgba(217,185,94,.6)}
.fr .c{position:absolute;width:90px;height:90px}
.fr .tl{top:6px;left:6px}.fr .tr{top:6px;right:6px;transform:scaleX(-1)}.fr .bl{bottom:6px;left:6px;transform:scaleY(-1)}.fr .br{bottom:6px;right:6px;transform:scale(-1,-1)}
.fr .topd{position:absolute;top:0;left:50%;transform:translateX(-50%);background:#0b0a09;padding:0 6px;line-height:0}
.gt{display:block;margin:0 auto;overflow:visible}
.dv{display:block;margin:6px auto 10px}
.hd{display:flex;align-items:center;justify-content:space-between;margin:2px 4px 10px}
.hd img{height:26px}
.hd span{font:700 8.5px/1 Cinzel,serif;letter-spacing:.2em;color:var(--or)}
.sub{text-align:center;font:italic 500 16px/1.35 "Cormorant Garamond",serif;color:var(--txt);margin:0 6px 12px}
.sub b{color:var(--or2);font-weight:700}
.card{position:relative;background:linear-gradient(180deg,rgba(26,23,17,.92),rgba(12,11,9,.95));border:1px solid var(--or);border-radius:6px;box-shadow:inset 0 0 0 3px rgba(11,10,9,.9),inset 0 0 0 4px rgba(217,185,94,.35),0 10px 24px -12px #000}
.card::before,.card::after{content:"◆";position:absolute;top:-8px;font-size:10px;color:var(--or);background:#0b0a09;padding:0 3px;line-height:14px}
.card::before{left:14px}.card::after{right:14px}
.btn{display:flex;align-items:center;justify-content:center;gap:8px;background:linear-gradient(180deg,#f6e3a0,#d9b95e 45%,#a57c2c 100%);color:#1a1307;font:700 13px/1 Cinzel,serif;letter-spacing:.06em;padding:13px 16px;border-radius:4px;border:1px solid #fff0bb;box-shadow:0 0 0 2px #0b0a09,0 0 0 3px var(--or),0 10px 22px -8px rgba(217,185,94,.6)}
.chk{list-style:none}
.chk li{position:relative;padding-left:18px;font-size:11.5px;line-height:1.5;color:var(--txt)}
.chk li::before{content:"✓";position:absolute;left:0;top:0;color:var(--or2);font-weight:800}
.pied{position:absolute;left:34px;right:34px;bottom:27px;text-align:center;font:700 7.5px/1 Cinzel,serif;color:#8a7f66;letter-spacing:.12em}
.pied span+span{display:none}
.pied b{color:var(--or)}
/* cover */
.cov{text-align:center;padding-top:52px}
.cov .lion{width:190px;height:190px;border-radius:50%;display:block;margin:0 auto 8px;box-shadow:0 0 0 2px var(--or),0 0 0 6px #0b0a09,0 0 0 7px rgba(217,185,94,.6),0 0 50px rgba(217,185,94,.35)}
.taux{display:flex;align-items:center;justify-content:center;gap:12px;margin:6px auto 16px;padding:10px 14px;width:270px}
.taux span{font:700 12px/1.35 Cinzel,serif;color:var(--txt);text-align:left;letter-spacing:.04em}
.steps{display:flex;gap:8px;margin-top:16px}
.steps div{flex:1;padding:12px 6px 10px;font:600 10.5px/1.3 Manrope;color:var(--txt);text-align:center}
.steps i{display:block;font:700 18px/1 Cinzel,serif;color:var(--or2);font-style:normal;margin-bottom:5px}
/* offres */
.off{display:block;margin-bottom:11px;overflow:visible}
.off .ph{height:62px;background-size:cover;background-position:center 30%;border-bottom:1px solid var(--or);border-radius:5px 5px 0 0}
.off .in{padding:8px 13px 10px;display:grid;grid-template-columns:1fr auto;gap:0 10px;align-items:center}
.off .nm{font:700 18px/1 Cinzel,serif;color:var(--or2);letter-spacing:.04em}
.off .pr{font:700 21px/1 Cinzel,serif;color:#fff3c8;text-shadow:0 0 12px rgba(217,185,94,.5)}
.off .chk{grid-column:1/-1;margin-top:5px}
.off .gain{grid-column:1/-1;display:flex;justify-content:space-between;align-items:center;margin-top:7px;padding-top:7px;border-top:1px solid rgba(217,185,94,.3);font-size:11px}
.off .gain b{color:var(--or2);font:700 14px Cinzel,serif}
.off .gain u{text-decoration:none;font:700 10.5px Cinzel,serif;color:var(--or);letter-spacing:.06em}
.rib{position:absolute;top:8px;right:-6px;background:linear-gradient(180deg,#f6e3a0,#c9a24a);color:#1a1307;font:700 8.5px/1 Cinzel,serif;letter-spacing:.12em;padding:6px 10px;box-shadow:0 3px 8px rgba(0,0,0,.6)}
.note{font:italic 500 13px/1.35 "Cormorant Garamond",serif;color:var(--mut);text-align:center}
.note b{color:var(--or2);font-style:normal;font-family:Cinzel,serif;font-size:11px}
/* templates */
.grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px}
.tp{display:block}
.tp .ph{height:84px;background-size:cover;background-position:top;border-bottom:1px solid var(--or);border-radius:5px 5px 0 0}
.tp .t{padding:8px 10px 9px}
.tp b{display:block;font:700 13.5px/1.1 Cinzel,serif;color:var(--or2)}
.tp span{display:block;font-size:10px;color:var(--mut);margin:3px 0 5px}
.tp u{text-decoration:none;font:700 9.5px Cinzel,serif;color:var(--or);letter-spacing:.06em}
.box{padding:13px 15px;margin-bottom:13px}
.box h3{font:700 15px/1.15 Cinzel,serif;color:var(--or2);margin-bottom:5px;letter-spacing:.03em}
.box p{font-size:12px;line-height:1.5;color:var(--mut)}
.box p b{color:var(--txt)}
.lnk{display:inline-block;margin-top:7px;font:700 10.5px Cinzel,serif;color:var(--or);letter-spacing:.06em}
.row{display:flex;gap:12px;align-items:center}
.row img{width:84px;height:84px;border-radius:4px;object-fit:cover;flex:none;border:1px solid var(--or)}
.cible{display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-top:8px}
.cible span{font-size:11px;border:1px solid rgba(217,185,94,.3);border-radius:4px;padding:7px 9px;color:var(--txt);background:rgba(0,0,0,.35)}
.cas{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:13px}
.cas .box{margin:0;padding:12px}
.tag{display:inline-block;font:700 8px/1 Cinzel,serif;letter-spacing:.12em;padding:5px 8px;margin-bottom:8px;background:linear-gradient(180deg,#f6e3a0,#c9a24a);color:#1a1307;border-radius:2px}
.tag.g{background:none;border:1px solid var(--or);color:var(--or2)}
.big{text-align:center;padding:12px 10px 14px;margin:4px 0 14px}
.big span{font:700 11px/1.3 Cinzel,serif;color:var(--txt);letter-spacing:.05em}
.qrf{display:inline-block;padding:9px 9px 0;background:#0b0a09;border:1.5px solid var(--or);box-shadow:inset 0 0 0 3px #0b0a09,inset 0 0 0 4px rgba(217,185,94,.5),0 0 22px rgba(217,185,94,.25);border-radius:3px}
.qri{line-height:0}
.qrl{font:700 11px/1 Cinzel,serif;letter-spacing:.24em;color:var(--or2);text-align:center;padding:8px 0 9px}
.qrrow{display:flex;gap:14px;align-items:center;padding:12px}
.qrrow p{font-size:12px;line-height:1.45;color:var(--mut)}
.qrrow p b{display:block;font:700 13px/1.2 Cinzel,serif;color:var(--or2);margin-bottom:4px}
.os{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:13px}
.os .box{margin:0}
.regle{display:flex;gap:12px;padding:14px;margin-bottom:14px}
.regle i{font:700 26px/1 Cinzel,serif;color:var(--or2);font-style:normal;flex:none;width:24px;text-align:center}
.regle h3{font:700 14px/1.2 Cinzel,serif;color:var(--or2);margin-bottom:5px}
.regle p{font-size:12px;line-height:1.5;color:var(--mut)}
.liens{display:grid;gap:8px}
.liens a{display:flex;justify-content:space-between;align-items:center;padding:10px 13px;font:700 11.5px Cinzel,serif;letter-spacing:.03em;color:var(--txt)}
.liens a small{font:600 9.5px Manrope;color:var(--mut);letter-spacing:0}
.liens a::after{content:"❯";color:var(--or);margin-left:8px;font-size:10px}
.liens a.card::before,.liens a.card::after{display:none}
.liens a.main{background:linear-gradient(180deg,#f6e3a0,#d9b95e 45%,#a57c2c);color:#1a1307}
.liens a.main small{color:#3b2c0d}.liens a.main::after{color:#1a1307}
'''
OFFRES=[('essentiel','Essentiel',490,['Site 1 page premium, à la marque du client','Formulaire de contact + Google Maps','Livré en 5 jours'],False),
        ('pro','Pro',890,['Jusqu’à 6 pages + blog / actualités','Prise de RDV en ligne, SEO Google','Livré en 8 jours'],True),
        ('boutique','Boutique',1490,['Boutique en ligne, jusqu’à 30 produits','Paiement CB & PayPal, gestion des stocks','Livré en 12 jours'],False)]
TPL=[('avocat','Avocat','Cabinet, juriste','wordpress-avocat'),('restaurant','Restaurant','Bistrot, bar, café','wordpress-restaurant'),
     ('barber','Barbier','Coiffeur, barber shop','wordpress-barber'),('coach','Coach','Sport, mental, formateur','wordpress-coach'),
     ('artisan','Artisan','Plombier, électricien, BTP','wordpress-artisan'),('domicile','Aide à domicile','Seniors, enfants','wordpress-domicile')]

def pg(inner, n, cls=''):
    return (f'<section class="p {cls}">{frame()}<div class="hd"><img src="img/logo.png" alt=""><span>FORMATION AMBASSADEUR</span><span>{n:02d} · 07</span></div>'
            f'{inner}<div class="pied"><span><b>ALLIANCE GROUPE</b> · NAPLES — NANTES</span><span>ALLIANCEGROUPE-INC.COM</span></div></section>')

def build(t):
    r=t/100; P=[]
    P.append(f'''<section class="p cov">{frame()}
<img class="lion" src="img/lion.jpg" alt="">
{gtitle(['ALLIANCE GROUPE'],13,300,weight=700)}
{gtitle(['FORMATION','AMBASSADEUR'],37,360,lh=1.08,weight=800)}
{divider(260)}
<p class="sub">Tu partages ton lien, un client achète,<br><b>tu es payé.</b></p>
<div class="taux card">{gtitle([f'{t} %'],44,110,weight=800,lh=1)}<span>DE COMMISSION<br>SUR CHAQUE VENTE</span></div>
<a class="btn" href="{B}/espace-ambassadeur/">OUVRIR MON ESPACE ❯</a>
<div class="steps"><div class="card"><i>I</i>Copie ton lien dans ton espace</div><div class="card"><i>II</i>Envoie-le à un pro</div><div class="card"><i>III</i>Il achète : tu touches {t} %</div></div>
<div style="margin-top:14px">{diamond(40)}</div>
<div class="pied"><span><b>ALLIANCE GROUPE</b> · NAPLES — NANTES</span><span>LECTURE : 5 MIN</span></div></section>''')
    cards=''.join(f'''<a class="off card" href="{B}/sites-express"><div class="ph" style="background-image:url(img/{k}-top.jpg)"></div>{'<span class="rib">LE + CHOISI</span>' if best else ''}
<div class="in"><div class="nm">{nom.upper()}</div><div class="pr">{eur(p)}</div><ul class="chk">{''.join(f'<li>{f}</li>' for f in feats)}</ul>
<div class="gain"><span>Ta commission : <b>{eur(p*r)}</b></span><u>VOIR L’OFFRE ❯</u></div></div></a>''' for k,nom,p,feats,best in OFFRES)
    P.append(pg(f'''{gtitle(['LES SITES EXPRESS'],25,360)}{divider(220)}<p class="sub">Prix fixe, affiché · payable en 4× PayPal</p>{cards}
<p class="note" style="margin-top:2px">Ensuite, la <b>MAINTENANCE</b> (dès 49 €/mois) garde le site du client à jour.</p>''',1))
    grid=''.join(f'<a class="tp card" href="{B}/{u}"><div class="ph" style="background-image:url(img/{k}.jpg)"></div><div class="t"><b>{n.upper()}</b><span>{d}</span><u>VOIR LE MODÈLE ❯</u></div></a>' for k,n,d,u in TPL)
    P.append(pg(f'''{gtitle(['UN MODÈLE','POUR CHAQUE PRO'],24,360,lh=1.15)}{divider(220)}<p class="sub">Montre au client son futur site, dans son métier.</p><div class="grid">{grid}</div>
<p class="note">Modèles <b>GRATUITS</b> : ils ouvrent la conversation.<br>Pour qu’on s’occupe de tout : un <b>SITE EXPRESS</b>.</p>''',2))
    P.append(pg(f'''{gtitle(['SUR-MESURE','&amp; SÉCURITÉ'],25,360,lh=1.15)}{divider(220)}<p class="sub">Pour les gros projets, et les pros qui ont déjà un site.</p>
<div class="box card"><h3>LE SUR-MESURE</h3><p>Un projet qui sort du cadre (site complexe, application, refonte complète) ? On fait un <b>devis</b>. Plus le projet est gros, plus tes <b>{t} %</b> pèsent lourd.</p><a class="lnk" href="{B}/sur-mesure">DEMANDER UN DEVIS ❯</a></div>
<a class="box card row" href="{B}/audit-securite"><img src="img/securite.jpg" alt=""><div><h3>AUDIT &amp; SÉCURITÉ</h3><p>Le client a déjà un site ? On vérifie qu’il n’expose pas son entreprise à un piratage. <b>Test gratuit</b> pour commencer.</p><span class="lnk">VOIR L’AUDIT ❯</span></div></a>
<div class="box card"><h3>À QUI VENDRE ?</h3><p>À tout pro <b>sans site</b>, ou avec un site <b>vieux, lent ou négligé</b>.</p>
<div class="cible"><span>Restaurants, bars</span><span>Coiffeurs, barbiers</span><span>Artisans, BTP</span><span>Coachs, sport</span><span>Aide à domicile</span><span>Commerces</span></div>
<a class="lnk" href="{B}/tester-mon-site">ASTUCE : FAIS-LUI TESTER SON SITE ❯</a></div>''',3))
    P.append(pg(f'''{gtitle(['LE LIEN FAIT TOUT'],25,360)}{divider(220)}<p class="sub">Ton lien personnel est dans ton espace.<br>Le client clique, il achète : <b>la vente est pour toi.</b></p>
<div class="big card">{gtitle([f'{t} %'],64,200,weight=800,lh=1)}<span>SUR CHAQUE VENTE · PAYÉ PAR PAYPAL APRÈS VALIDATION</span></div>
<div class="cas"><div class="box card"><span class="tag">AUTOMATIQUE</span><h3>PAR TON LIEN</h3><p>Commission <b>enregistrée toute seule</b>, rien à déclarer. Le client a <b>30 jours</b> pour acheter.</p></div>
<div class="box card"><span class="tag g">À DÉCLARER</span><h3>EN DIRECT</h3><p>Signé avec toi <b>de la main à la main</b> ? Alors seulement, tu déclares.</p><a class="lnk" href="{B}/ambassadeurs">DÉCLARER ❯</a></div></div>
<div class="box card"><h3>UNE PAGE PRÉCISE ?</h3><p>Ajoute ton code à la fin de n’importe quel lien du site :<br><b style="font-family:monospace;color:var(--or2)">…/wordpress-barber?ref=TONCODE</b></p></div>
<div class="box card"><h3>LE CLASSEMENT</h3><p>Les meilleurs ambassadeurs du mois, en direct.</p><a class="lnk" href="{B}/classement">VOIR LE CLASSEMENT ❯</a></div>''',4))
    P.append(pg(f'''{gtitle(['TOUT DANS TA POCHE'],25,360)}{divider(220)}<p class="sub">L’appli « Ambassadeurs » : ton lien, tes ventes, tes gains.</p>
<a class="btn" href="{B}/espace-ambassadeur/?install=amb" style="margin:4px 4px 16px">INSTALLER L’APPLICATION ❯</a>
<div class="os"><div class="box card"><h3>ANDROID</h3><p>Ouvre le lien dans <b>Chrome</b>, puis menu <b>⋮</b> → <b>Installer l’application</b>.</p></div>
<div class="box card"><h3>IPHONE</h3><p>Ouvre le lien dans <b>Safari</b>, puis <b>Partager</b> → <b>Sur l’écran d’accueil</b>.</p></div></div>
<div class="card qrrow">{qr_gold(B+'/espace-ambassadeur/?install=amb',132)}<p><b>SUR ORDINATEUR ?</b>Scanne ce code avec l’appareil photo de ton téléphone : l’installation démarre.</p></div>
<div class="box card" style="margin-top:14px"><h3>LE STUDIO CRÉATIF</h3><p>Crée des <b>vidéos et visuels</b> prêts à partager sur tes réseaux, en un clic.</p><a class="lnk" href="{B}/studio">OUVRIR LE STUDIO ❯</a></div>''',5))
    P.append(pg(f'''{gtitle(['LES 3 RÈGLES D’OR'],25,360)}{divider(220)}<p class="sub">C’est ce qui fait qu’on nous fait confiance,<br>à toi comme à nous.</p>
<div class="regle card"><i>I</i><div><h3>HONNÊTE, JAMAIS DE SPAM</h3><p>Pas de messages en masse, pas de fausses promesses. Tu recommandes ce que tu connais, à des gens qui peuvent en avoir besoin.</p></div></div>
<div class="regle card"><i>II</i><div><h3>AVOCATS : E-MAIL OU COURRIER</h3><p>Leur profession encadre la prospection : <b style="color:var(--txt)">pas d’appel, pas de SMS, pas de démarchage</b> sur place. Un e-mail ou un courrier, courtois.</p></div></div>
<div class="regle card"><i>III</i><div><h3>DIS QUE C’EST TOI</h3><p>Présente-toi comme ambassadeur d’Alliance Groupe. La transparence rassure, et elle fait vendre.</p></div></div>
<div style="text-align:center;margin:4px 0 8px">{diamond(44)}</div>
<p class="note">Une question ? <b>CONTACT@ALLIANCEGROUPE-INC.COM</b></p>''',6))
    L=[('espace-ambassadeur/','Mon espace','ton lien, tes gains',1),('espace-ambassadeur/?install=amb','Installer l’appli','Android & iPhone',0),
       ('sites-express','Sites Express','490 · 890 · 1 490 €',0),('templates-wordpress','Modèles par métier','6 métiers',0),
       ('sur-mesure','Sur-mesure','sur devis',0),('audit-securite','Audit & sécurité','sites existants',0),('tester-mon-site','Tester un site','gratuit',0),
       ('ambassadeurs','Déclarer en direct','main à la main',0),('classement','Le classement','du mois',0),('studio','Studio créatif','visuels & vidéos',0)]
    ls=''.join(f'<a class="card {"main" if m else ""}" href="{B}/{u}"><span>{n.upper()}</span><small>{d}</small></a>' for u,n,d,m in L)
    P.append(pg(f'''{gtitle(['TOUS TES LIENS'],25,360)}{divider(220)}<p class="sub">Touche un bouton, la page s’ouvre.</p><div class="liens">{ls}<a class="card" href="mailto:contact@alliancegroupe-inc.com"><span>UNE QUESTION ?</span><small>contact@alliancegroupe-inc.com</small></a></div>''',7))
    P.append(f'''<section class="p cov" style="padding-top:64px">{frame()}
{gtitle(['À TOI','DE JOUER'],40,360,lh=1.08,weight=800)}{divider(240)}
<p class="sub">Un lien, un client, <b>{t} % pour toi.</b><br>C’est aussi simple que ça.</p>
<div style="margin:6px 0 18px">{qr_gold(B+'/espace-ambassadeur/',200,'SCANNE-MOI')}</div>
<p class="note" style="margin-bottom:16px">Ton espace ambassadeur, depuis ton téléphone.</p>
<a class="btn" href="{B}/espace-ambassadeur/" style="margin:0 20px">OUVRIR MON ESPACE ❯</a>
<div style="margin-top:18px">{diamond(48)}</div>
<div class="pied"><span><b>ALLIANCE GROUPE</b> · NAPLES — NANTES</span><span>ALLIANCEGROUPE-INC.COM</span></div></section>''')
    return f'<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><title>Formation Ambassadeur {t} % — Alliance Groupe</title><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;700;800;900&family=Cormorant+Garamond:ital,wght@1,500;1,700&family=Manrope:wght@400;600;700;800&display=swap"><style>{CSS}</style></head><body>{"".join(P)}</body></html>'

if __name__=='__main__':
    for t in (10,20,50):
        open(f'formation-{t}.html','w').write(build(t))
    print('ok')
