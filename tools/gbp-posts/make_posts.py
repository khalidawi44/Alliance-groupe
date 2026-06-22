#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
4 posts Google Business « AG Starter PRO » — posés sur LA VRAIE carte Canva de Fabrice.
Base : export HD de la carte (design DAHNUOKxM_c) -> /tmp/canva/carte-hd.png (768x1376).
On garde le décor noir & or + le lion, on ajoute titre (Playfair) + sous-titre + QR /contact + 07 44 82 95 16.
Sortie : alliance-groupe-theme/assets/images/gbp/post-<slug>.png
"""
import os, qrcode
from PIL import Image, ImageDraw, ImageFont

HERE   = os.path.dirname(os.path.abspath(__file__))
ROOT   = os.path.abspath(os.path.join(HERE, "..", ".."))
FONTS  = os.path.join(HERE, "fonts")
ASSETS = os.path.join(ROOT, "alliance-groupe-theme", "assets", "images")
OUTDIR = os.path.join(ASSETS, "gbp")
CARD   = "/tmp/canva/carte-hd.png"
os.makedirs(OUTDIR, exist_ok=True)

# Palette
GOLD    = (212, 180, 92)
GOLD_HI = (236, 212, 142)
GOLD_DIM= (160, 132, 64)
CREAM   = (238, 230, 212)
WHITE   = (255, 255, 255)
INK     = (12, 12, 20)

CONTACT_URL = "https://alliancegroupe-inc.com/contact"
PHONE       = "07 44 82 95 16"

# Carte en fond, agrandie à 1080 de large
base0 = Image.open(CARD).convert("RGB")
W = 1080
H = int(round(base0.height * W / base0.width))   # ~1935
BASE = base0.resize((W, H), Image.LANCZOS)

def F(name, size): return ImageFont.truetype(os.path.join(FONTS, name), size)

def text_w(d, txt, font, tr=0):
    return sum(d.textlength(c, font=font) for c in txt) + tr*(len(txt)-1) if tr else d.textlength(txt, font=font)

def tracked(d, cx, y, txt, font, fill, tr):
    x = cx - text_w(d, txt, font, tr)/2
    for c in txt:
        d.text((x, y), c, font=font, fill=fill); x += d.textlength(c, font=font) + tr

def fit(d, txt, fontfile, max_px, start, minsz, max_lines=2):
    sz = start
    while sz >= minsz:
        f = F(fontfile, sz); words = txt.split(); lines=[]; cur=""
        for w in words:
            t=(cur+" "+w).strip()
            if d.textlength(t, font=f) <= max_px or not cur: cur=t
            else: lines.append(cur); cur=w
        if cur: lines.append(cur)
        if len(lines) <= max_lines: return f, lines, sz
        sz -= 2
    return F(fontfile, minsz), [txt], minsz

def make_qr(url, box):
    qr = qrcode.QRCode(error_correction=qrcode.constants.ERROR_CORRECT_M, box_size=10, border=2)
    qr.add_data(url); qr.make(fit=True)
    return qr.make_image(fill_color=INK, back_color="white").convert("RGB").resize((box, box), Image.NEAREST)

CX = W // 2

def plate(img, box, radius=22, alpha=155):
    """Cartouche translucide noir + liseré or, posé sur la carte."""
    lay = Image.new("RGBA", img.size, (0,0,0,0))
    dd = ImageDraw.Draw(lay)
    dd.rounded_rectangle(box, radius=radius, fill=(6,6,11,alpha))
    img.alpha_composite(lay)
    d = ImageDraw.Draw(img)
    d.rounded_rectangle(box, radius=radius, outline=GOLD, width=2)
    d.rounded_rectangle([box[0]+5,box[1]+5,box[2]-5,box[3]-5], radius=radius-4, outline=GOLD_DIM, width=1)

def build(slug, headline, subline):
    img = BASE.copy().convert("RGBA")

    # ===== CARTOUCHE HAUT : kicker + titre (au-dessus du lion) =====
    plate(img, [150, 548, 930, 766])
    d = ImageDraw.Draw(img)
    tracked(d, CX, 572, "ALLIANCE GROUPE", F("playfair-700.ttf",24), GOLD, 9)
    d.line([(CX-60,606),(CX+60,606)], fill=GOLD_DIM, width=1)
    font, lines, sz = fit(d, headline, "playfair-900.ttf", 690, 70, 42, 2)
    lh = sz*1.04
    y = 690 - (len(lines)-1)*lh/2
    for ln in lines:
        w = d.textlength(ln, font=font)
        d.text((CX-w/2+2, y+2), ln, font=font, fill=(0,0,0))
        d.text((CX-w/2, y), ln, font=font, fill=GOLD_HI)
        y += lh

    # ===== CARTOUCHE BAS : sous-titre + QR + téléphone (sous le lion) =====
    plate(img, [150, 1000, 930, 1402])
    d = ImageDraw.Draw(img)
    sf, slines, _ = fit(d, subline, "playfair-italic.ttf", 680, 30, 23, 2)
    y = 1022
    for ln in slines:
        w = d.textlength(ln, font=sf); d.text((CX-w/2, y), ln, font=sf, fill=CREAM); y += 38

    # QR vers /contact
    qbox = 168; card = qbox + 24
    qx = CX - card//2; qy = 1088
    d.rounded_rectangle([qx, qy, qx+card, qy+card], radius=14, fill=WHITE)
    img.paste(make_qr(CONTACT_URL, qbox).convert("RGBA"), (qx+12, qy+12))
    d.rounded_rectangle([qx, qy, qx+card, qy+card], radius=14, outline=GOLD, width=2)

    tracked(d, CX, qy+card+14, "APPELEZ  OU  SCANNEZ", F("playfair-400.ttf",15), GOLD_DIM, 6)
    pf = F("playfair-700.ttf", 33)
    pw = d.textlength(PHONE, font=pf)
    d.text((CX-pw/2, qy+card+38), PHONE, font=pf, fill=GOLD_HI)
    cf = F("playfair-400.ttf", 19)
    cap = "alliancegroupe-inc.com/contact"
    cw = d.textlength(cap, font=cf); d.text((CX-cw/2, qy+card+82), cap, font=cf, fill=GOLD)

    path = os.path.join(OUTDIR, f"post-{slug}.png")
    img.convert("RGB").save(path, "PNG"); print("OK", path); return path

POSTS = [
    ("devis",       "Devis gratuit",      "Estimé sous 24 h, sans engagement."),
    ("conseil",     "Conseil offert",     "30 min avec un expert, offert."),
    ("sites-490",   "Site web dès 490 €", "Design premium, livré clé en main."),
    ("realisation", "Nos réalisations",   "Nos créations web sur-mesure."),
]

if __name__ == "__main__":
    print("Carte:", base0.size, "-> sortie", (W, H))
    for slug, h, sub in POSTS: build(slug, h, sub)
    print("Terminé :", OUTDIR)
