#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Diaporama vidéo Google Ads « Alliance Groupe » — Naples + Nantes + Offres, sur toute la durée du son (60 s).
Photos du dépôt (villes + équipe/Naples + sécurité Nantes) + 4 offres + carte de contact.
Fondu enchaîné + léger zoom Ken Burns + habillage noir & or (Playfair).
Sortie : alliance-groupe-theme/assets/videos/ads/alliance-promo-<fmt>.mp4 (son = napoli.m4a)
"""
import os, math, subprocess, imageio_ffmpeg
from PIL import Image, ImageDraw, ImageFont

HERE   = os.path.dirname(os.path.abspath(__file__))
ROOT   = os.path.abspath(os.path.join(HERE, "..", ".."))
FONTS  = os.path.join(ROOT, "tools", "gbp-posts", "fonts")
ASSETS = os.path.join(ROOT, "alliance-groupe-theme", "assets", "images")
OUTDIR = os.path.join(ROOT, "alliance-groupe-theme", "assets", "videos", "ads")
AUDIO  = os.path.join(HERE, "napoli.m4a")
os.makedirs(OUTDIR, exist_ok=True)
FF = imageio_ffmpeg.get_ffmpeg_exe()

FPS = 30
DURATION = 60.0
XF = 0.7  # fondu enchaîné (s)

GOLD=(212,180,92); GOLD_HI=(245,226,162); GOLD_LO=(150,116,46); GOLD_DIM=(120,98,50)
CREAM=(238,230,210); WHITE=(255,255,255); BLACK=(8,8,13); NAVY=(22,22,36); INK=(10,10,16)
PHONE="07 44 82 95 16"; URLT="alliancegroupe-inc.com"

def img(p): return os.path.join(ASSETS, p)
LOGO = Image.open(img("ag-logo.png")).convert("RGBA")
_m = Image.new("L", LOGO.size, 0); ImageDraw.Draw(_m).ellipse([2,2,LOGO.width-2,LOGO.height-2], fill=255)
LOGO.putalpha(_m)

def lerp(a,b,t): return tuple(int(a[i]+(b[i]-a[i])*t) for i in range(3))
def F(name,size,base): return ImageFont.truetype(os.path.join(FONTS,name), int(size*base/1080))

def radial(W,H):
    n=240; g=Image.new("RGB",(n,n)); px=g.load(); cx=cy=(n-1)/2; mx=math.hypot(cx,cy)
    for y in range(n):
        for x in range(n):
            t=min(1.0,(math.hypot(x-cx,y-cy)/mx)**1.3); px[x,y]=lerp(NAVY,BLACK,t)
    return g.resize((W,H),Image.LANCZOS)

def cover(im, W, H):
    """Recadre une photo pour remplir WxH (cover)."""
    iw,ih=im.size; s=max(W/iw,H/ih); nw,nh=int(iw*s+1),int(ih*s+1)
    im=im.resize((nw,nh),Image.LANCZOS)
    return im.crop(((nw-W)//2,(nh-H)//2,(nw-W)//2+W,(nh-H)//2+H))

def ctext(d,cx,y,t,f,fill,stroke=0,sf=(0,0,0)):
    w=d.textlength(t,font=f); d.text((cx-w/2,y),t,font=f,fill=fill,stroke_width=stroke,stroke_fill=sf)

def tracked(d,cx,y,t,f,fill,tr):
    tot=sum(d.textlength(c,font=f) for c in t)+tr*(len(t)-1); x=cx-tot/2
    for c in t: d.text((x,y),c,font=f,fill=fill); x+=d.textlength(c,font=f)+tr

def grad_text(im,d,cx,y,text,font,shadow=True):
    w=int(d.textlength(text,font=font)); asc,desc=font.getmetrics(); h=asc+desc
    if shadow: d.text((cx-w/2+2,y+2),text,font=font,fill=(0,0,0))
    grad=Image.new("RGB",(w+4,h)); gd=ImageDraw.Draw(grad)
    for yy in range(h):
        t=yy/max(1,h-1); col=lerp(GOLD_HI,(255,244,205),t*2) if t<0.5 else lerp((255,244,205),GOLD_LO,(t-0.5)*2)
        gd.line([(0,yy),(grad.width,yy)],fill=col)
    mask=Image.new("L",(w+4,h),0); ImageDraw.Draw(mask).text((0,0),text,font=font,fill=255)
    grad.putalpha(mask); im.paste(grad,(int(cx-w/2),int(y)),grad)

def frame_border(W,H):
    lay=Image.new("RGBA",(W,H),(0,0,0,0)); d=ImageDraw.Draw(lay)
    m=int(min(W,H)*0.035); d.rectangle([m,m,W-m,H-m],outline=GOLD,width=max(2,int(min(W,H)*0.0026)))
    return lay

def vgrad(W,H,top_a=0,bot_a=210):
    """Voile dégradé bas (légende lisible)."""
    g=Image.new("L",(1,H))
    for y in range(H):
        t=y/(H-1); a=int(top_a+(bot_a-top_a)*max(0,(t-0.45)/0.55)**1.4)
        g.putpixel((0,y),a)
    g=g.resize((W,H))
    blk=Image.new("RGBA",(W,H),(0,0,0,0)); blk.putalpha(g); return blk

# ---------- rendu d'une diapo (image format-size) ----------
def slide_photo(W,H,base, photo, cap, brand=True):
    im=cover(Image.open(img(photo)).convert("RGB"),W,H)
    im=Image.eval(im, lambda p:int(p*0.92))   # léger assombrissement
    im=im.convert("RGBA")
    im.alpha_composite(vgrad(W,H))
    d=ImageDraw.Draw(im)
    if brand:
        tracked(d,W//2,int(H*0.055),"ALLIANCE GROUPE",F("playfair-700.ttf",26,base),GOLD_HI,int(base*0.008))
    if cap:
        ctext(d,W//2,int(H*0.84),cap,F("playfair-700.ttf",44,base),CREAM,stroke=2)
    im.alpha_composite(frame_border(W,H))
    return im.convert("RGB")

def slide_title(W,H,base, word, photo, sub=None):
    im=cover(Image.open(img(photo)).convert("RGB"),W,H)
    im=Image.eval(im,lambda p:int(p*0.55)).convert("RGBA")
    d=ImageDraw.Draw(im)
    asc,desc=F("playfair-900.ttf",120,base).getmetrics()
    grad_text(im,d,W//2,H//2-(asc+desc)//2-int(H*0.02),word,F("playfair-900.ttf",120,base))
    if sub: ctext(d,W//2,H//2+int(H*0.10),sub,F("playfair-italic.ttf",34,base),CREAM,stroke=2)
    im.alpha_composite(frame_border(W,H))
    return im.convert("RGB")

def slide_offer(W,H,base,bg, title, sub):
    im=bg.copy().convert("RGBA"); d=ImageDraw.Draw(im)
    tracked(d,W//2,int(H*0.20),"AG STARTER PRO",F("playfair-700.ttf",24,base),GOLD,int(base*0.01))
    asc,desc=F("playfair-900.ttf",92,base).getmetrics()
    grad_text(im,d,W//2,int(H*0.36),title,F("playfair-900.ttf",92,base))
    ctext(d,W//2,int(H*0.55),sub,F("playfair-italic.ttf",34,base),CREAM)
    # petit diamant
    cx,cy=W//2,int(H*0.66); w=int(base*0.03); h=int(base*0.036)
    d.polygon([(cx,cy-h),(cx+w,cy),(cx,cy+h),(cx-w,cy)],fill=GOLD)
    ctext(d,W//2,int(H*0.72),PHONE,F("playfair-900.ttf",46,base),GOLD_HI,stroke=2)
    im.alpha_composite(frame_border(W,H))
    return im.convert("RGB")

def slide_logo(W,H,base,bg):
    im=bg.copy().convert("RGBA"); d=ImageDraw.Draw(im)
    eh=int(base*0.30); ew=int(LOGO.width*eh/LOGO.height)
    im.alpha_composite(LOGO.resize((ew,eh),Image.LANCZOS),(W//2-ew//2,int(H*0.22)))
    tracked(d,W//2,int(H*0.58),"ALLIANCE GROUPE",F("playfair-900.ttf",56,base),GOLD_HI,int(base*0.012))
    ctext(d,W//2,int(H*0.68),"Création de site web & Cybersécurité",F("playfair-italic.ttf",34,base),CREAM)
    ctext(d,W//2,int(H*0.75),"Naples · Nantes",F("playfair-700.ttf",30,base),GOLD)
    im.alpha_composite(frame_border(W,H))
    return im.convert("RGB")

def slide_end(W,H,base,bg):
    im=bg.copy().convert("RGBA"); d=ImageDraw.Draw(im)
    eh=int(base*0.20); ew=int(LOGO.width*eh/LOGO.height)
    im.alpha_composite(LOGO.resize((ew,eh),Image.LANCZOS),(W//2-ew//2,int(H*0.16)))
    tracked(d,W//2,int(H*0.45),"DEVIS GRATUIT",F("playfair-900.ttf",48,base),GOLD_HI,int(base*0.012))
    grad_text(im,d,W//2,int(H*0.55),PHONE,F("playfair-900.ttf",66,base))
    ctext(d,W//2,int(H*0.70),URLT+"/contact",F("playfair-700.ttf",32,base),GOLD)
    im.alpha_composite(frame_border(W,H))
    return im.convert("RGB")

# ---------- séquence ----------
SEQ = [
    ('logo',),
    ('title','NAPLES','cities/baie_naples_nuit.jpg','Notre berceau'),
    ('photo','cities/naples-1.jpg','Naples'),
    ('photo','team/naples-1.jpg',"L'esprit de famille"),
    ('photo','team/reunion-naples.jpg','Une équipe engagée'),
    ('photo','cities/naples-2.jpg','La baie de Naples'),
    ('photo','team/siege-naples.jpg','Notre siège'),
    ('title','NANTES','cities/nantes-1.jpg','Votre agence locale'),
    ('photo','cities/nantes-2.jpg','Nantes'),
    ('photo','team/fabrizio-nantes.jpg','Proches de vous'),
    ('photo','securite/nantes-cyber.jpg','Cybersécurité à Nantes'),
    ('photo','cities/nantes-3.jpg','Au cœur de la ville'),
    ('title','NOS OFFRES','cities/naples-3.jpg',None),
    ('offer','Devis gratuit','Estimé sous 24 h'),
    ('offer','Conseil offert','30 min avec un expert'),
    ('offer','Site dès 490 €','Design premium, clé en main'),
    ('offer','Nos réalisations','Des sites qui convertissent'),
    ('end',),
]

def render(fmt, W, H):
    base=min(W,H); bg=radial(W,H)
    # construire toutes les diapos
    slides=[]
    for s in SEQ:
        if s[0]=='logo':   slides.append(slide_logo(W,H,base,bg))
        elif s[0]=='end':  slides.append(slide_end(W,H,base,bg))
        elif s[0]=='title':slides.append(slide_title(W,H,base,s[1],s[2],s[3]))
        elif s[0]=='photo':slides.append(slide_photo(W,H,base,s[1],s[2]))
        elif s[0]=='offer':slides.append(slide_offer(W,H,base,bg,s[1],s[2]))
    n=len(slides)
    dur=DURATION/n   # durée égale par diapo
    # pré-agrandir pour Ken Burns
    S=1.10
    big=[sl.resize((int(W*S),int(H*S)),Image.LANCZOS) for sl in slides]

    def kb(i, p, pan):
        bw,bh=big[i].size
        cw=int(W*(1-0.09*p)); ch=int(H*(1-0.09*p))
        ox=int((bw-cw)/2 + pan*(bw-cw)*0.5); oy=int((bh-ch)/2)
        ox=max(0,min(bw-cw,ox)); oy=max(0,min(bh-ch,oy))
        return big[i].crop((ox,oy,ox+cw,oy+ch)).resize((W,H),Image.LANCZOS)

    out=os.path.join(OUTDIR,f"alliance-promo-{fmt}.mp4")
    tmp=os.path.join(OUTDIR,f"_tmp-{fmt}.mp4")
    cmd=[FF,"-y","-f","rawvideo","-pix_fmt","rgb24","-s",f"{W}x{H}","-r",str(FPS),"-i","-",
         "-c:v","libx264","-pix_fmt","yuv420p","-profile:v","high","-crf","20","-preset","medium",tmp]
    proc=subprocess.Popen(cmd,stdin=subprocess.PIPE,stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL)
    nframes=int(DURATION*FPS)
    pans=[(-1 if i%2 else 1) for i in range(n)]
    for fr in range(nframes):
        t=fr/FPS; idx=min(n-1,int(t/dur)); local=t-idx*dur; p=local/dur
        cur=kb(idx,p,pans[idx])
        # fondu enchaîné vers la diapo suivante
        if idx<n-1 and local>dur-XF:
            a=(local-(dur-XF))/XF
            nxt=kb(idx+1,0.0,pans[idx+1])
            cur=Image.blend(cur,nxt,a)
        proc.stdin.write(cur.tobytes())
    proc.stdin.close(); proc.wait()
    # muxer l'audio (60 s) avec fondu sortie
    cmd2=[FF,"-y","-i",tmp,"-i",AUDIO,"-map","0:v:0","-map","1:a:0",
          "-af","afade=t=out:st=58.5:d=1.5","-c:v","copy","-c:a","aac","-b:a","192k",
          "-shortest","-movflags","+faststart",out]
    subprocess.run(cmd2,stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL)
    os.remove(tmp)
    print("OK",out,os.path.getsize(out)//1024,"Ko")

if __name__=="__main__":
    render("horizontale-1920x1080",1920,1080)
    render("carre-1080x1080",1080,1080)
    print("Terminé :",OUTDIR)
