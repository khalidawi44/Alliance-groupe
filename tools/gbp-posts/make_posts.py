#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
4 posts Google Business « AG Starter PRO » — sur LA VRAIE carte Canva de Fabrice.
Décor de la carte conservé. On ajoute UNIQUEMENT :
  - « ALLIANCE GROUPE »
  - le titre (Playfair Display, dégradé doré)
  - le QR code vers /contact avec le LOGO AG au centre
  - le téléphone 07 44 82 95 16 en dessous
Sortie : alliance-groupe-theme/assets/images/gbp/post-<slug>.png
"""
import os, qrcode
from qrcode.constants import ERROR_CORRECT_H
from PIL import Image, ImageDraw, ImageFont

HERE   = os.path.dirname(os.path.abspath(__file__))
ROOT   = os.path.abspath(os.path.join(HERE, "..", ".."))
FONTS  = os.path.join(HERE, "fonts")
ASSETS = os.path.join(ROOT, "alliance-groupe-theme", "assets", "images")
OUTDIR = os.path.join(ASSETS, "gbp")
CARD   = os.path.join(HERE, "carte-base.png")
os.makedirs(OUTDIR, exist_ok=True)

GOLD    = (212, 180, 92)
GOLD_HI = (245, 226, 162)
GOLD_LO = (150, 116, 46)
GOLD_DIM= (160, 132, 64)
CREAM   = (238, 230, 212)
WHITE   = (255, 255, 255)
INK     = (12, 12, 20)

CONTACT_URL = "https://alliancegroupe-inc.com/contact"
PHONE       = "07 44 82 95 16"

base0 = Image.open(CARD).convert("RGB")
W = 1080
H = int(round(base0.height * W / base0.width))   # ~1935
BASE = base0.resize((W, H), Image.LANCZOS)
CX = W // 2

# logo AG (cercle) pour le centre du QR
LOGO = Image.open(os.path.join(ASSETS, "ag-logo.png")).convert("RGBA")
_m = Image.new("L", LOGO.size, 0)
ImageDraw.Draw(_m).ellipse([2, 2, LOGO.width-2, LOGO.height-2], fill=255)
LOGO.putalpha(_m)

def F(name, size): return ImageFont.truetype(os.path.join(FONTS, name), size)

def text_w(d, t, f, tr=0):
    return sum(d.textlength(c, font=f) for c in t)+tr*(len(t)-1) if tr else d.textlength(t, font=f)

def tracked(d, cx, y, t, f, fill, tr):
    x = cx - text_w(d, t, f, tr)/2
    for c in t:
        d.text((x, y), c, font=f, fill=fill); x += d.textlength(c, font=f)+tr

def lerp(a, b, t): return tuple(int(a[i]+(b[i]-a[i])*t) for i in range(3))

def grad_text(img, d, cx, y, text, font):
    w = int(d.textlength(text, font=font)); asc, desc = font.getmetrics(); h = asc+desc
    d.text((cx-w/2+2, y+2), text, font=font, fill=(0, 0, 0))   # ombre
    grad = Image.new("RGB", (w+4, h)); gd = ImageDraw.Draw(grad)
    for yy in range(h):
        t = yy/max(1, h-1)
        col = lerp(GOLD_HI, (255,244,205), t*2) if t < 0.5 else lerp((255,244,205), GOLD_LO, (t-0.5)*2)
        gd.line([(0, yy), (grad.width, yy)], fill=col)
    mask = Image.new("L", (w+4, h), 0); ImageDraw.Draw(mask).text((0, 0), text, font=font, fill=255)
    img.paste(grad, (int(cx-w/2), int(y)), mask)

def fit(d, txt, fontfile, max_px, start, minsz):
    sz = start
    while sz > minsz and d.textlength(txt, font=F(fontfile, sz)) > max_px:
        sz -= 2
    return F(fontfile, sz)

def qr_with_logo(url, box):
    qr = qrcode.QRCode(error_correction=ERROR_CORRECT_H, box_size=10, border=2)
    qr.add_data(url); qr.make(fit=True)
    img = qr.make_image(fill_color=INK, back_color="white").convert("RGB").resize((box, box), Image.NEAREST)
    # logo au centre, sur pastille blanche arrondie
    lsz = int(box*0.26); pad = int(lsz*0.16)
    pl = lsz+pad*2
    d = ImageDraw.Draw(img)
    px = (box-pl)//2; py = (box-pl)//2
    d.rounded_rectangle([px, py, px+pl, py+pl], radius=int(pl*0.18), fill="white")
    logo = LOGO.resize((lsz, lsz), Image.LANCZOS)
    img.paste(logo, (px+pad, py+pad), logo)
    return img

def build(slug, title):
    img = BASE.copy()
    d = ImageDraw.Draw(img)

    # --- ALLIANCE GROUPE (au-dessus du lion) ---
    tracked(d, CX, 632, "ALLIANCE GROUPE", F("playfair-700.ttf", 30), GOLD, 11)
    d.line([(CX-70, 678), (CX+70, 678)], fill=GOLD_DIM, width=1)
    d.polygon([(CX, 671), (CX+7, 678), (CX, 685), (CX-7, 678)], fill=GOLD)

    # --- TITRE (dégradé doré) ---
    tf = fit(d, title, "playfair-900.ttf", 720, 84, 48)
    asc, desc = tf.getmetrics()
    grad_text(img, d, CX, 706, title, tf)

    # --- QR avec logo au centre (zone basse, sous le lion) ---
    qbox = 250
    qx = CX - qbox//2; qy = 962
    pad = 18
    d.rounded_rectangle([qx-pad, qy-pad, qx+qbox+pad, qy+qbox+pad], radius=18, fill=WHITE)
    img.paste(qr_with_logo(CONTACT_URL, qbox), (qx, qy))
    d.rounded_rectangle([qx-pad, qy-pad, qx+qbox+pad, qy+qbox+pad], radius=18, outline=GOLD, width=3)

    # --- TÉLÉPHONE en dessous (or vif + contour foncé pour rester lisible) ---
    pf = F("playfair-900.ttf", 50)
    pw = d.textlength(PHONE, font=pf)
    d.text((CX-pw/2, qy+qbox+pad+30), PHONE, font=pf, fill=GOLD_HI,
           stroke_width=3, stroke_fill=(0, 0, 0))

    path = os.path.join(OUTDIR, f"post-{slug}.png")
    img.save(path, "PNG"); print("OK", path); return path

POSTS = [
    ("devis",       "Devis gratuit"),
    ("conseil",     "Conseil offert"),
    ("sites-490",   "Site web dès 490 €"),
    ("realisation", "Nos réalisations"),
]

if __name__ == "__main__":
    for slug, t in POSTS: build(slug, t)
    print("Terminé :", OUTDIR)
