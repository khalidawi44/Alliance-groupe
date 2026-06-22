#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Vidéos Google Ads « Alliance Groupe » — motion design noir & or (Playfair Display).
Génère 2 formats prêts pour YouTube / éléments vidéo Google Ads :
  - horizontale 1920x1080 (16:9)
  - carrée     1080x1080 (1:1)
~15 s, sans audio (textes animés / dégradés dorés / lion AG).
Sortie : alliance-groupe-theme/assets/videos/ads/alliance-ad-<fmt>.mp4
"""
import os, math, subprocess, imageio_ffmpeg
from PIL import Image, ImageDraw, ImageFont

HERE   = os.path.dirname(os.path.abspath(__file__))
ROOT   = os.path.abspath(os.path.join(HERE, "..", ".."))
FONTS  = os.path.join(ROOT, "tools", "gbp-posts", "fonts")
ASSETS = os.path.join(ROOT, "alliance-groupe-theme", "assets", "images")
OUTDIR = os.path.join(ROOT, "alliance-groupe-theme", "assets", "videos", "ads")
os.makedirs(OUTDIR, exist_ok=True)
FF = imageio_ffmpeg.get_ffmpeg_exe()

FPS = 30
BLACK=(8,8,13); NAVY=(22,22,36)
GOLD=(212,180,92); GOLD_HI=(245,226,162); GOLD_LO=(150,116,46); GOLD_DIM=(120,98,50)
CREAM=(238,230,210); WHITE=(255,255,255)
PHONE="07 44 82 95 16"; URL="alliancegroupe-inc.com/contact"

LION = Image.open(os.path.join(ASSETS, "ag-logo.png")).convert("RGBA")
_m = Image.new("L", LION.size, 0)
ImageDraw.Draw(_m).ellipse([2,2,LION.width-2,LION.height-2], fill=255)
LION.putalpha(_m)

def lerp(a,b,t): return tuple(int(a[i]+(b[i]-a[i])*t) for i in range(3))

def radial(W,H):
    n=240; g=Image.new("RGB",(n,n)); px=g.load(); cx=cy=(n-1)/2; mx=math.hypot(cx,cy)
    for y in range(n):
        for x in range(n):
            t=min(1.0,(math.hypot(x-cx,y-cy)/mx)**1.3); px[x,y]=lerp(NAVY,BLACK,t)
    return g.resize((W,H), Image.LANCZOS)

def ease(t): return t*t*(3-2*t)  # smoothstep

def fade_alpha(tl, dur, fin=0.45, fout=0.5):
    if tl<fin: a=tl/fin
    elif tl>dur-fout: a=max(0.0,(dur-tl)/fout)
    else: a=1.0
    return ease(max(0.0,min(1.0,a)))

class Pen:
    def __init__(self, base):
        self.base=base
    def F(self,name,sz): return ImageFont.truetype(os.path.join(FONTS,name), int(sz*self.base/1080))

def text_w(d,t,f,tr=0): return sum(d.textlength(c,font=f) for c in t)+tr*(len(t)-1) if tr else d.textlength(t,font=f)
def tracked(d,cx,y,t,f,fill,tr):
    x=cx-text_w(d,t,f,tr)/2
    for c in t: d.text((x,y),c,font=f,fill=fill); x+=d.textlength(c,font=f)+tr
def ctext(d,cx,y,t,f,fill):
    w=d.textlength(t,font=f); d.text((cx-w/2,y),t,font=f,fill=fill)

def grad_layer(layer,cx,y,text,font):
    d=ImageDraw.Draw(layer); w=int(d.textlength(text,font=font))
    asc,desc=font.getmetrics(); h=asc+desc
    grad=Image.new("RGB",(w+4,h)); gd=ImageDraw.Draw(grad)
    for yy in range(h):
        t=yy/max(1,h-1)
        col=lerp(GOLD_HI,(255,244,205),t*2) if t<0.5 else lerp((255,244,205),GOLD_LO,(t-0.5)*2)
        gd.line([(0,yy),(grad.width,yy)],fill=col)
    mask=Image.new("L",(w+4,h),0); ImageDraw.Draw(mask).text((0,0),text,font=font,fill=255)
    grad.putalpha(mask); layer.alpha_composite(grad,(int(cx-w/2),int(y)))

def check_badge(d,cx,cy,r):
    d.ellipse([cx-r,cy-r,cx+r,cy+r],outline=GOLD,width=max(2,int(r*0.13)))
    d.line([(cx-r*0.45,cy+r*0.02),(cx-r*0.08,cy+r*0.42)],fill=GOLD_HI,width=max(2,int(r*0.2)))
    d.line([(cx-r*0.08,cy+r*0.42),(cx+r*0.5,cy-r*0.4)],fill=GOLD_HI,width=max(2,int(r*0.2)))

def diamond(d,cx,cy,w,h):
    top=(cx,cy-h/2);bot=(cx,cy+h/2);l=(cx-w/2,cy-h*0.12);r=(cx+w/2,cy-h*0.12)
    d.polygon([top,l,bot,r],fill=GOLD)

# ---- SCÈNES (t local en s) -> dessine sur un calque RGBA, renvoie offset slide dy ----
def scene_logo(layer, P, W, H, tl, dur):
    d=ImageDraw.Draw(layer); cx=W//2; cy=H//2
    a_in=ease(min(1.0,tl/0.6)); sc=0.86+0.14*a_in
    eh=int(P.base*0.30*sc); ew=int(LION.width*eh/LION.height)
    lg=LION.resize((ew,eh),Image.LANCZOS); layer.alpha_composite(lg,(cx-ew//2, int(cy-eh*0.78)))
    tracked(d,cx,cy+int(P.base*0.12),"ALLIANCE GROUPE",P.F("playfair-900.ttf",58),GOLD_HI,int(P.base*0.012))
    ctext(d,cx,cy+int(P.base*0.22),"Agence web & cybersécurité · Nantes",P.F("playfair-italic.ttf",30),CREAM)

def scene_creation(layer,P,W,H,tl,dur):
    d=ImageDraw.Draw(layer); cx=W//2; cy=H//2
    tracked(d,cx,cy-int(P.base*0.20),"VOTRE SITE, SIGNÉ",P.F("playfair-400.ttf",26),GOLD_DIM,int(P.base*0.01))
    grad_layer(layer,cx,cy-int(P.base*0.12),"Création de site web",P.F("playfair-900.ttf",78))
    ctext(d,cx,cy+int(P.base*0.04),"& Cybersécurité",P.F("playfair-700.ttf",46),CREAM)
    ctext(d,cx,cy+int(P.base*0.16),"Design premium, rapide et bien référencé.",P.F("playfair-italic.ttf",28),CREAM)

def scene_checks(layer,P,W,H,tl,dur):
    d=ImageDraw.Draw(layer); cx=W//2; cy=H//2
    tracked(d,cx,cy-int(P.base*0.24),"CE QUE VOUS OBTENEZ",P.F("playfair-400.ttf",26),GOLD_DIM,int(P.base*0.01))
    items=["Design haut de gamme","Optimisé mobile & SEO","Sécurité & maintenance","Livraison clé en main"]
    f=P.F("playfair-400.ttf",36); bw=max(d.textlength(c,font=f) for c in items)
    bx=cx-(bw+P.base*0.06)/2+P.base*0.05; y=cy-int(P.base*0.12); step=int(P.base*0.085)
    for i,c in enumerate(items):
        ia=ease(max(0.0,min(1.0,(tl-0.3-i*0.28)/0.4)))
        if ia<=0: continue
        ov=Image.new("RGBA",layer.size,(0,0,0,0)); od=ImageDraw.Draw(ov)
        check_badge(od,bx-int(P.base*0.035),y+int(P.base*0.022),int(P.base*0.018))
        od.text((bx,y),c,font=f,fill=CREAM)
        al=ov.split()[3].point(lambda p:int(p*ia)); ov.putalpha(al); layer.alpha_composite(ov)
        y+=step

def scene_offre(layer,P,W,H,tl,dur):
    d=ImageDraw.Draw(layer); cx=W//2; cy=H//2
    grad_layer(layer,cx,cy-int(P.base*0.16),"Site web dès 490 €",P.F("playfair-900.ttf",76))
    ctext(d,cx,cy-int(P.base*0.01),"clé en main",P.F("playfair-italic.ttf",30),CREAM)
    diamond(d,cx,cy+int(P.base*0.075),int(P.base*0.04),int(P.base*0.048))
    grad_layer(layer,cx,cy+int(P.base*0.12),"+340% de leads",P.F("playfair-700.ttf",52))

def scene_end(layer,P,W,H,tl,dur):
    d=ImageDraw.Draw(layer); cx=W//2; cy=H//2
    eh=int(P.base*0.16); ew=int(LION.width*eh/LION.height)
    lg=LION.resize((ew,eh),Image.LANCZOS); layer.alpha_composite(lg,(cx-ew//2,int(cy-P.base*0.30)))
    tracked(d,cx,cy-int(P.base*0.10),"DEVIS GRATUIT",P.F("playfair-900.ttf",44),GOLD_HI,int(P.base*0.012))
    ctext(d,cx,cy-int(P.base*0.005),"Parlons de votre projet",P.F("playfair-italic.ttf",30),CREAM)
    grad_layer(layer,cx,cy+int(P.base*0.07),PHONE,P.F("playfair-900.ttf",62))
    ctext(d,cx,cy+int(P.base*0.20),URL,P.F("playfair-700.ttf",30),GOLD)

# slot: (fn, t0, t1)
SCENES=[(scene_logo,0.0,3.0),(scene_creation,3.0,6.1),(scene_checks,6.1,9.6),
        (scene_offre,9.6,12.2),(scene_end,12.2,15.2)]
DURATION=15.2

def render(fmt, W, H):
    bg=radial(W,H); P=Pen(min(W,H))
    out=os.path.join(OUTDIR,f"alliance-ad-{fmt}.mp4")
    cmd=[FF,"-y","-f","rawvideo","-pix_fmt","rgb24","-s",f"{W}x{H}","-r",str(FPS),"-i","-",
         "-c:v","libx264","-pix_fmt","yuv420p","-profile:v","high","-crf","19",
         "-preset","medium","-movflags","+faststart",out]
    proc=subprocess.Popen(cmd,stdin=subprocess.PIPE,stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL)
    nframes=int(DURATION*FPS)
    # cadre or léger
    frame_border=Image.new("RGBA",(W,H),(0,0,0,0))
    bd=ImageDraw.Draw(frame_border); m=int(min(W,H)*0.035)
    bd.rectangle([m,m,W-m,H-m],outline=GOLD,width=max(2,int(min(W,H)*0.0028)))
    for i in range(nframes):
        t=i/FPS
        frame=bg.copy().convert("RGBA")
        frame.alpha_composite(frame_border)
        for fn,t0,t1 in SCENES:
            if t0<=t<t1:
                tl=t-t0; a=fade_alpha(tl,t1-t0)
                if a<=0: continue
                layer=Image.new("RGBA",(W,H),(0,0,0,0))
                dy=int((1-ease(min(1.0,tl/0.5)))*min(W,H)*0.02)
                tmp=Image.new("RGBA",(W,H),(0,0,0,0))
                fn(tmp,P,W,H,tl,t1-t0)
                if dy: layer.alpha_composite(tmp,(0,dy))
                else: layer=tmp
                if a<1: al=layer.split()[3].point(lambda p:int(p*a)); layer.putalpha(al)
                frame.alpha_composite(layer)
        proc.stdin.write(frame.convert("RGB").tobytes())
    proc.stdin.close(); proc.wait()
    print("OK", out, os.path.getsize(out)//1024, "Ko")
    return out

if __name__=="__main__":
    render("carre-1080x1080",1080,1080)
    render("horizontale-1920x1080",1920,1080)
    print("Terminé :", OUTDIR)
