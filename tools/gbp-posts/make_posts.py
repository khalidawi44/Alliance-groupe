#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Génère 4 posts Google Business « AG Starter PRO » (noir & or).
Base : carte Canva de Fabrice (identité noir/or, lion AG, Playfair Display).
Sortie : alliance-groupe-theme/assets/images/gbp/post-<slug>.png  (1080x1080)
"""
import os, math, qrcode
from PIL import Image, ImageDraw, ImageFont

HERE   = os.path.dirname(os.path.abspath(__file__))
ROOT   = os.path.abspath(os.path.join(HERE, "..", ".."))
FONTS  = os.path.join(HERE, "fonts")
ASSETS = os.path.join(ROOT, "alliance-groupe-theme", "assets", "images")
OUTDIR = os.path.join(ASSETS, "gbp")
os.makedirs(OUTDIR, exist_ok=True)

SS   = 2                 # supersampling
W    = 1080 * SS
H    = 1080 * SS
def s(px): return int(round(px * SS))

# ---- Palette Alliance ----
BLACK   = (10, 10, 15)
NAVY    = (20, 20, 34)
GOLD     = (212, 180, 92)     # champagne #D4B45C
GOLD_HI  = (232, 206, 134)    # or clair
GOLD_DIM = (150, 124, 60)
CREAM    = (236, 227, 207)
WHITE    = (255, 255, 255)
INK      = (12, 12, 20)       # QR sombre (quasi noir)

CONTACT_URL = "https://alliancegroupe-inc.com/contact"
PHONE       = "07 44 82 95 16"

def F(name, size):
    return ImageFont.truetype(os.path.join(FONTS, name), s(size))

# ---------- helpers ----------
def radial_bg():
    """Fond noir avec halo navy central, généré petit puis agrandi (lisse)."""
    n = 220
    g = Image.new("RGB", (n, n))
    px = g.load()
    cx = cy = (n - 1) / 2.0
    maxd = math.hypot(cx, cy)
    for y in range(n):
        for x in range(n):
            d = math.hypot(x - cx, y - cy) / maxd      # 0 centre -> 1 coin
            t = min(1.0, d ** 1.25)
            r = int(NAVY[0] + (BLACK[0] - NAVY[0]) * t)
            gg = int(NAVY[1] + (BLACK[1] - NAVY[1]) * t)
            b = int(NAVY[2] + (BLACK[2] - NAVY[2]) * t)
            px[x, y] = (max(3, r - 6), max(3, gg - 6), max(4, b - 4))
    return g.resize((W, H), Image.LANCZOS)

def text_w(d, txt, font, tracking=0):
    if not tracking:
        return d.textlength(txt, font=font)
    return sum(d.textlength(c, font=font) for c in txt) + tracking * (len(txt) - 1)

def draw_tracked(d, cx, y, txt, font, fill, tracking):
    total = text_w(d, txt, font, tracking)
    x = cx - total / 2
    for c in txt:
        d.text((x, y), c, font=font, fill=fill)
        x += d.textlength(c, font=font) + tracking

def fit_lines(d, txt, fontfile, max_px, start, minsz, max_lines=2):
    """Réduit la taille jusqu'à tenir en <= max_lines lignes."""
    size = start
    while size >= minsz:
        font = F(fontfile, size)
        words = txt.split()
        lines, cur = [], ""
        for w in words:
            test = (cur + " " + w).strip()
            if d.textlength(test, font=font) <= max_px or not cur:
                cur = test
            else:
                lines.append(cur); cur = w
        if cur: lines.append(cur)
        if len(lines) <= max_lines:
            return font, lines, size
        size -= 2
    return F(fontfile, minsz), [txt], minsz

def corner(d, x, y, dx, dy, L, gap):
    """Équerre déco art-déco + petit losange dans le coin."""
    for off, col, wdt in ((0, GOLD, s(3)), (s(9), GOLD_DIM, s(1))):
        d.line([(x + dx*off, y + dy*off), (x + dx*(off+L), y + dy*off)], fill=col, width=wdt)
        d.line([(x + dx*off, y + dy*off), (x + dx*off, y + dy*(off+L))], fill=col, width=wdt)
    # petit losange
    c = s(26); ds = s(7)
    px, py = x + dx*c, y + dy*c
    d.polygon([(px, py-ds), (px+ds, py), (px, py+ds), (px-ds, py)], outline=GOLD, width=s(2))

def diamond(d, cx, cy, r):
    d.polygon([(cx, cy-r), (cx+r, cy), (cx, cy+r), (cx-r, cy)], fill=GOLD)
    d.line([(cx-r, cy), (cx+r, cy)], fill=BLACK, width=s(1))

def make_qr(url, box):
    qr = qrcode.QRCode(version=None, error_correction=qrcode.constants.ERROR_CORRECT_M,
                       box_size=10, border=2)
    qr.add_data(url); qr.make(fit=True)
    img = qr.make_image(fill_color=INK, back_color="white").convert("RGB")
    return img.resize((box, box), Image.NEAREST)

# ---------- gabarit commun ----------
def base_canvas():
    img = radial_bg()
    d = ImageDraw.Draw(img)
    # double cadre or
    m1 = s(40); m2 = s(52)
    d.rectangle([m1, m1, W-m1, H-m1], outline=GOLD, width=s(3))
    d.rectangle([m2, m2, W-m2, H-m2], outline=GOLD_DIM, width=s(1))
    # ornements de coin
    L = s(60); inset = s(40)
    corner(d, inset, inset,  1,  1, L, 0)
    corner(d, W-inset, inset, -1,  1, L, 0)
    corner(d, inset, H-inset, 1, -1, L, 0)
    corner(d, W-inset, H-inset, -1, -1, L, 0)
    return img, d

# emblème lion (ag-logo) — détouré en cercle pour supprimer le bord carré du fond
_raw = Image.open(os.path.join(ASSETS, "ag-logo.png")).convert("RGBA")
_mask = Image.new("L", _raw.size, 0)
ImageDraw.Draw(_mask).ellipse([2, 2, _raw.width-2, _raw.height-2], fill=255)
LION = _raw.copy(); LION.putalpha(_mask)

def build(slug, headline, subline):
    img, d = base_canvas()
    cx = W // 2

    # --- bandeau haut ---
    draw_tracked(d, cx, s(78), "ALLIANCE GROUPE", F("playfair-700.ttf", 27), GOLD, s(10))
    draw_tracked(d, cx, s(116), "STARTER PRO", F("playfair-400.ttf", 16), GOLD_DIM, s(12))
    d.line([(cx-s(70), s(146)), (cx+s(70), s(146))], fill=GOLD_DIM, width=s(1))
    diamond(d, cx, s(146), s(5))

    # --- emblème ---
    eh = s(186); ew = int(LION.width * eh / LION.height)
    lion = LION.resize((ew, eh), Image.LANCZOS)
    img.paste(lion, (cx - ew//2, s(168)), lion)

    # --- titre (Playfair Black) ---
    font, lines, size = fit_lines(d, headline, "playfair-900.ttf", W-s(190), 78, 46, 2)
    lh = size * 1.06
    y = s(430) - (len(lines)-1)*lh/2
    for ln in lines:
        w = d.textlength(ln, font=font)
        # ombre douce + texte or
        d.text((cx - w/2 + s(2), y + s(2)), ln, font=font, fill=(0,0,0))
        d.text((cx - w/2, y), ln, font=font, fill=GOLD_HI)
        y += lh

    # --- sous-titre (Playfair italic) ---
    sf, slines, _ = fit_lines(d, subline, "playfair-italic.ttf", W-s(240), 31, 24, 2)
    y = s(545)
    for ln in slines:
        w = d.textlength(ln, font=sf)
        d.text((cx - w/2, y), ln, font=sf, fill=CREAM)
        y += 31 * 1.18 * SS / SS * SS  # ~ line height
        y = int(y)

    # --- bloc téléphone ---
    draw_tracked(d, cx, s(660), "APPELEZ-NOUS", F("playfair-400.ttf", 16), GOLD_DIM, s(8))
    pf = F("playfair-700.ttf", 40)
    pw = d.textlength(PHONE, font=pf)
    pad = s(34); ph = s(66)
    px0 = cx - pw/2 - pad; px1 = cx + pw/2 + pad
    py0 = s(690); py1 = py0 + ph
    d.rounded_rectangle([px0, py0, px1, py1], radius=ph//2, outline=GOLD, width=s(2))
    d.text((cx - pw/2, py0 + (ph - (pf.getbbox(PHONE)[3]-pf.getbbox(PHONE)[1]))/2 - pf.getbbox(PHONE)[1]),
           PHONE, font=pf, fill=GOLD_HI)

    # --- QR code vers /contact ---
    qbox = s(176); card = qbox + s(28)
    qx = cx - card//2; qy = s(792)
    d.rounded_rectangle([qx, qy, qx+card, qy+card], radius=s(16), fill=WHITE)
    qr = make_qr(CONTACT_URL, qbox)
    img.paste(qr, (qx + s(14), qy + s(14)))
    # cadre or autour du QR
    d.rounded_rectangle([qx, qy, qx+card, qy+card], radius=s(16), outline=GOLD, width=s(2))

    # --- légende QR ---
    cf = F("playfair-400.ttf", 21)
    cap1 = "Scannez pour nous écrire"
    w1 = d.textlength(cap1, font=cf)
    d.text((cx - w1/2, qy + card + s(14)), cap1, font=cf, fill=CREAM)
    cf2 = F("playfair-700.ttf", 21)
    cap2 = "alliancegroupe-inc.com/contact"
    w2 = d.textlength(cap2, font=cf2)
    d.text((cx - w2/2, qy + card + s(44)), cap2, font=cf2, fill=GOLD)

    out = img.resize((1080, 1080), Image.LANCZOS)
    path = os.path.join(OUTDIR, f"post-{slug}.png")
    out.save(path, "PNG")
    print("OK", path)
    return path

POSTS = [
    ("devis",       "Devis gratuit",      "Votre projet web estimé sous 24 h, sans engagement."),
    ("conseil",     "Conseil offert",     "30 min avec un expert pour bâtir votre présence en ligne."),
    ("sites-490",   "Site web dès 490 €", "Design premium, livré clé en main, prêt à convertir."),
    ("realisation", "Nos réalisations",   "Découvrez nos créations sur-mesure signées Alliance Groupe."),
]

if __name__ == "__main__":
    for slug, h, sub in POSTS:
        build(slug, h, sub)
    print("Terminé :", OUTDIR)
