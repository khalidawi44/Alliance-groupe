#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Assemble la vidéo promo 60s AVEC les clips animés Picsart (personnages) + photos + cartes + musique.
- Diapos animées : clips Picsart (réunion/équipe/siège/Nantes/cyber) recadrés + habillage (marque, légende, cadre or).
- Diapos fixes : photos villes + titres + 4 cartes + logo + carte de contact (léger zoom).
- Fondus enchaînés (xfade) + son napoli.m4a (60s).
Sortie : alliance-groupe-theme/assets/videos/ads/alliance-promo-anim-<fmt>.mp4
"""
import os, importlib.util, subprocess, imageio_ffmpeg
from PIL import Image, ImageDraw

HERE   = os.path.dirname(os.path.abspath(__file__))
spec=importlib.util.spec_from_file_location('ss', os.path.join(HERE,'make_slideshow.py'))
ss=importlib.util.module_from_spec(spec); spec.loader.exec_module(ss)

ROOT=ss.ROOT; OUTDIR=ss.OUTDIR; FF=imageio_ffmpeg.get_ffmpeg_exe()
AUDIO=os.path.join(HERE,'napoli.m4a')
CLIPS=os.path.join(HERE,'..','..','/tmp')  # placeholder, set below
CLIPDIR="/tmp/claude-0/-home-user-Alliance-groupe/779788b2-2805-5655-8463-96c10037ac45/scratchpad/clips"
SEG=os.path.join(HERE,'_seg'); os.makedirs(SEG,exist_ok=True)

FPS=30; TOTAL=60.0; XF=0.7

# clip animé = (fichier clip, légende)
ANIM={
 'reunion-naples':'Une équipe engagée',
 'naples-1':"L'esprit de famille",
 'siege-naples':'Notre siège',
 'fabrizio-nantes':'Proches de vous',
 'nantes-cyber':'Cybersécurité à Nantes',
}

SEQ=[
 ('logo',),
 ('title','NAPLES','cities/baie_naples_nuit.jpg','Notre berceau'),
 ('photo','cities/naples-1.jpg','La baie de Naples'),
 ('anim','reunion-naples'),
 ('anim','naples-1'),
 ('photo','cities/naples-2.jpg','Les ruelles napolitaines'),
 ('anim','siege-naples'),
 ('title','NANTES','cities/nantes-1.jpg','Votre agence locale'),
 ('photo','cities/nantes-2.jpg','Nantes'),
 ('anim','fabrizio-nantes'),
 ('anim','nantes-cyber'),
 ('photo','cities/nantes-3.jpg','Au cœur de la ville'),
 ('title','NOS OFFRES','cities/naples-3.jpg',None),
 ('card','gbp/post-devis.png'),
 ('card','gbp/post-conseil.png'),
 ('card','gbp/post-sites-490.png'),
 ('card','gbp/post-realisation.png'),
 ('end',),
]

def overlay_anim(W,H,base,cap):
    """Habillage transparent pour un clip animé : marque haut + légende bas + voile + cadre or."""
    im=Image.new("RGBA",(W,H),(0,0,0,0))
    im.alpha_composite(ss.vgrad(W,H))
    d=ImageDraw.Draw(im)
    ss.tracked(d,W//2,int(H*0.055),"ALLIANCE GROUPE",ss.F("playfair-700.ttf",26,base),ss.GOLD_HI,int(base*0.008))
    if cap: ss.ctext(d,W//2,int(H*0.84),cap,ss.F("playfair-700.ttf",44,base),ss.CREAM,stroke=2)
    im.alpha_composite(ss.frame_border(W,H))
    return im

def still_png(W,H,base,bg,s):
    if s[0]=='logo':   im=ss.slide_logo(W,H,base,bg)
    elif s[0]=='end':  im=ss.slide_end(W,H,base,bg)
    elif s[0]=='title':im=ss.slide_title(W,H,base,s[1],s[2],s[3])
    elif s[0]=='photo':im=ss.slide_photo(W,H,base,s[1],s[2])
    elif s[0]=='card': im=ss.slide_card(W,H,base,bg,s[1])
    return im

def build_segment(i, s, W, H, base, bg, d):
    seg=os.path.join(SEG,f"seg{i:02d}.mp4"); df=f"{d:.4f}"
    if s[0]=='anim':
        clip=os.path.join(CLIPDIR,f"{s[1]}.mp4")
        ov=os.path.join(SEG,f"ov{i:02d}.png"); overlay_anim(W,H,base,ANIM[s[1]]).save(ov)
        if H>W:
            # VERTICAL : clip posé (non rogné) sur le fond or, centré + marque/légende dans les marges
            bgp=os.path.join(SEG,"bgv.png")
            if not os.path.exists(bgp): bg.save(bgp)
            vf=(f"[1:v]scale={W}:-2,fps={FPS}[fg];"
                f"[0:v][fg]overlay=(W-w)/2:(H-h)/2[b];"
                f"[b][2:v]overlay=0:0,format=yuv420p[v]")
            cmd=[FF,"-y","-loop","1","-i",bgp,"-stream_loop","3","-i",clip,"-i",ov,
                 "-filter_complex",vf,"-map","[v]","-an","-t",df,
                 "-r",str(FPS),"-c:v","libx264","-pix_fmt","yuv420p","-profile:v","high","-crf","20","-preset","medium",seg]
        else:
            # horizontal/carré : cover + overlay
            vf=(f"[0:v]scale={W}:{H}:force_original_aspect_ratio=increase,crop={W}:{H},setsar=1,fps={FPS}[bg];"
                f"[bg][1:v]overlay=0:0,format=yuv420p[v]")
            cmd=[FF,"-y","-stream_loop","2","-i",clip,"-i",ov,"-filter_complex",vf,"-map","[v]","-an","-t",df,
                 "-r",str(FPS),"-c:v","libx264","-pix_fmt","yuv420p","-profile:v","high","-crf","20","-preset","medium",seg]
    else:
        # Ken Burns par Pillow (rapide, fiable) -> pipe vers ffmpeg
        im=still_png(W,H,base,bg,s)
        big=im.resize((int(W*1.10),int(H*1.10)),Image.LANCZOS)
        frames=int(round(d*FPS)); bw,bh=big.size
        cmd=[FF,"-y","-f","rawvideo","-pix_fmt","rgb24","-s",f"{W}x{H}","-r",str(FPS),"-i","-",
             "-c:v","libx264","-pix_fmt","yuv420p","-profile:v","high","-crf","20","-preset","medium",seg]
        proc=subprocess.Popen(cmd,stdin=subprocess.PIPE,stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL)
        for fr in range(frames):
            p=fr/max(1,frames-1); cw=int(W*(1-0.09*p)); ch=int(H*(1-0.09*p))
            ox=(bw-cw)//2; oy=(bh-ch)//2
            fr_im=big.crop((ox,oy,ox+cw,oy+ch)).resize((W,H),Image.LANCZOS)
            proc.stdin.write(fr_im.convert("RGB").tobytes())
        proc.stdin.close(); proc.wait()
        return seg
    subprocess.run(cmd,stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL,check=True)
    return seg

def render(fmt,W,H):
    base=min(W,H); bg=ss.radial(W,H); n=len(SEQ)
    d=(TOTAL+(n-1)*XF)/n
    segs=[build_segment(i,s,W,H,base,bg,d) for i,s in enumerate(SEQ)]
    # xfade en chaîne
    inputs=[];
    for sgp in segs: inputs+=["-i",sgp]
    fc=""; prev="[0:v]"
    for k in range(1,n):
        o=f"{k*(d-XF):.4f}"; out=f"[x{k}]"
        fc+=f"{prev}[{k}:v]xfade=transition=fade:duration={XF}:offset={o}{out};"
        prev=out
    vlab=prev
    out=os.path.join(OUTDIR,f"alliance-promo-anim-{fmt}.mp4")
    cmd=[FF,"-y"]+inputs+["-i",AUDIO,"-filter_complex",fc.rstrip(';'),
         "-map",vlab,"-map",f"{n}:a:0","-af","afade=t=out:st=58.5:d=1.5",
         "-t",f"{TOTAL}","-c:v","libx264","-pix_fmt","yuv420p","-profile:v","high","-crf","20","-preset","medium",
         "-c:a","aac","-b:a","192k","-movflags","+faststart",out]
    subprocess.run(cmd,stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL,check=True)
    print("OK",out,os.path.getsize(out)//1024,"Ko")

if __name__=="__main__":
    render("horizontale-1920x1080",1920,1080)
    render("carre-1080x1080",1080,1080)
    print("Terminé")
