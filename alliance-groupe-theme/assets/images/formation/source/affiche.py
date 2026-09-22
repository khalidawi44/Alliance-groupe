import importlib.util
spec=importlib.util.spec_from_file_location('b2','build2.py'); m=importlib.util.module_from_spec(spec); spec.loader.exec_module(m)
B=m.B
CSS=m.CSS.replace('@page{size:420px 880px;margin:0}','@page{size:A4;margin:0}')
html=f'''<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><title>Affiche — Deviens ambassadeur</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;700;800;900&family=Cormorant+Garamond:ital,wght@1,500;1,700&family=Manrope:wght@400;600;700;800&display=swap">
<style>{CSS}
.a4{{width:794px;height:1123px;padding:76px 70px 80px;text-align:center}}
.a4 .fr::before{{inset:20px}} .a4 .fr::after{{inset:28px}}
.a4 .fr .c{{width:150px;height:150px}} .a4 .fr .c svg{{width:150px;height:150px}}
.a4 .fr .tl{{top:12px;left:12px}}.a4 .fr .tr{{top:12px;right:12px}}.a4 .fr .bl{{bottom:12px;left:12px}}.a4 .fr .br{{bottom:12px;right:12px}}
.a4 .lion{{width:210px;height:210px;border-radius:50%;display:block;margin:0 auto 14px;box-shadow:0 0 0 2px var(--or),0 0 0 7px #0b0a09,0 0 0 8px rgba(217,185,94,.6),0 0 60px rgba(217,185,94,.35)}}
.a4 .sub{{font-size:23px;margin:14px 60px 22px}}
.a4 .taux{{width:400px;padding:14px 18px;margin:0 auto 26px}}
.a4 .taux span{{font-size:16px;white-space:nowrap}}
.a4 .pied{{left:70px;right:70px;bottom:48px;font-size:11px}}
</style></head><body><section class="p cov a4">{m.frame()}
<img class="lion" src="img/lion.jpg" alt="">
{m.gtitle(['ALLIANCE GROUPE'],17,420)}
{m.gtitle(['DEVIENS','AMBASSADEUR'],58,640,lh=1.06,weight=800)}
{m.divider(420)}
<p class="sub">Recommande nos sites web aux pros autour de toi<br>et touche <b>une commission sur chaque vente.</b></p>
<div class="taux card">{m.gtitle(['10 %'],56,150,weight=800,lh=1)}<span>SUR CHAQUE VENTE<br>PAYÉ PAR PAYPAL</span></div>
{m.qr_gold(B+'/candidature-ambassadeur/',240,'SCANNE POUR POSTULER')}
<p class="note" style="margin-top:18px;font-size:17px">Inscription gratuite, sans engagement · alliancegroupe-inc.com/ambassadeurs</p>
<div class="pied"><span><b>ALLIANCE GROUPE</b> · NAPLES — NANTES</span></div></section></body></html>'''
open('affiche.html','w').write(html)
print('ok')
