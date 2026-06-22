#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
4 posts Google Business « AG Starter PRO » — style carte PREMIUM de Fabrice.
Texte posé DIRECTEMENT sur le noir & or (pas de cadre/cartouche autour des textes) :
- titre en DÉGRADÉ doré métallique (Playfair Display)
- liste à coches dorées
- diamant + bouton CTA + 07 44 82 95 16 + QR vers /contact
Sortie : alliance-groupe-theme/assets/images/gbp/post-<slug>.png  (1080x1350)
"""
import os, math, qrcode
from PIL import Image, ImageDraw, ImageFont

HERE   = os.path.dirname(os.path.abspath(__file__))
ROOT   = os.path.abspath(os.path.join(HERE, "..", ".."))
FONTS  = os.path.join(HERE, "fonts")
ASSETS = os.path.join(ROOT, "alliance-groupe-theme", "assets", "images")
OUTDIR = os.path.join(ASSETS, "gbp")
os.makedirs(OUTDIR, exist_ok=True)

SS = 2
W, Hh = 1080*SS, 1350*SS
def s(px): return int(round(px*SS))

# Palette
BLACK   = (8, 8, 13)
NAVY    = (22, 22, 36)
GOLD    = (212, 180, 92)
GOLD_HI = (245, 226, 162)
GOLD_LO = (150, 116, 46)
GOLD_DIM= (120, 98, 50)
CREAM   = (236, 228, 208)
WHITE   = (255, 255, 255)
INK     = (10, 10, 16)

CONTACT_URL = "https://alliancegroupe-inc.com/contact"
PHONE       = "07 44 82 95 16"

def F(name, size): return ImageFont.truetype(os.path.join(FONTS, name), s(size))

def lerp(a, b, t): return tuple(int(a[i]+(b[i]-a[i])*t) for i in range(3))

def radial_bg():
    n = 240
    g = Image.new("RGB", (n, n)); px = g.load()
    cx = cy = (n-1)/2; mx = math.hypot(cx, cy)
    for y in range(n):
        for x in range(n):
            t = min(1.0, (math.hypot(x-cx, y-cy)/mx)**1.3)
            px[x, y] = lerp(NAVY, BLACK, t)
    return g.resize((W, Hh), Image.LANCZOS)

def text_w(d, t, f, tr=0):
    return sum(d.textlength(c, font=f) for c in t)+tr*(len(t)-1) if tr else d.textlength(t, font=f)

def tracked(d, cx, y, t, f, fill, tr):
    x = cx - text_w(d, t, f, tr)/2
    for c in t:
        d.text((x, y), c, font=f, fill=fill); x += d.textlength(c, font=f)+tr

def grad_text(img, d, cx, y, text, font, shadow=True):
    """Texte en dégradé doré métallique, centré sur cx."""
    w = int(d.textlength(text, font=font))
    asc, desc = font.getmetrics(); h = asc+desc
    if shadow:
        d.text((cx-w/2+s(2), y+s(2)), text, font=font, fill=(0,0,0))
    grad = Image.new("RGB", (w+s(4), h))
    gd = ImageDraw.Draw(grad)
    for yy in range(h):
        t = yy/max(1, h-1)
        col = lerp(GOLD_HI, (255,244,205), t*2) if t < 0.5 else lerp((255,244,205), GOLD_LO, (t-0.5)*2)
        gd.line([(0, yy), (grad.width, yy)], fill=col)
    mask = Image.new("L", (w+s(4), h), 0)
    ImageDraw.Draw(mask).text((0, 0), text, font=font, fill=255)
    img.paste(grad, (int(cx-w/2), int(y)), mask)

def fit(d, txt, fontfile, max_px, start, minsz, max_lines=2):
    sz = start
    while sz >= minsz:
        f = F(fontfile, sz); words = txt.split(); lines=[]; cur=""
        for w in words:
            t = (cur+" "+w).strip()
            if d.textlength(t, font=f) <= max_px or not cur: cur = t
            else: lines.append(cur); cur = w
        if cur: lines.append(cur)
        if len(lines) <= max_lines: return f, lines, sz
        sz -= 2
    return F(fontfile, minsz), [txt], minsz

def check_badge(d, cx, cy, r):
    d.ellipse([cx-r, cy-r, cx+r, cy+r], outline=GOLD, width=s(2))
    d.line([(cx-r*0.45, cy+r*0.02), (cx-r*0.08, cy+r*0.42)], fill=GOLD_HI, width=s(3))
    d.line([(cx-r*0.08, cy+r*0.42), (cx+r*0.5, cy-r*0.4)], fill=GOLD_HI, width=s(3))

def diamond(d, cx, cy, w, h):
    top=(cx, cy-h/2); bot=(cx, cy+h/2); l=(cx-w/2, cy-h*0.12); r=(cx+w/2, cy-h*0.12)
    ml=(cx-w*0.22, cy-h*0.12); mr=(cx+w*0.22, cy-h*0.12)
    d.polygon([top, l, bot, r], fill=GOLD)
    for p in (l, ml, mr, r):
        d.line([top, p], fill=GOLD_LO, width=s(1)); d.line([p, bot], fill=GOLD_LO, width=s(1))
    d.line([l, r], fill=GOLD_LO, width=s(1))

def corner(d, x, y, dx, dy, L):
    for off, col, wd in ((0, GOLD, s(3)), (s(9), GOLD_DIM, s(1))):
        d.line([(x+dx*off, y+dy*off), (x+dx*(off+L), y+dy*off)], fill=col, width=wd)
        d.line([(x+dx*off, y+dy*off), (x+dx*off, y+dy*(off+L))], fill=col, width=wd)
    c = s(24); ds = s(6); px, py = x+dx*c, y+dy*c
    d.polygon([(px, py-ds), (px+ds, py), (px, py+ds), (px-ds, py)], outline=GOLD, width=s(2))

def make_qr(url, box):
    qr = qrcode.QRCode(error_correction=qrcode.constants.ERROR_CORRECT_M, box_size=10, border=2)
    qr.add_data(url); qr.make(fit=True)
    return qr.make_image(fill_color=INK, back_color="white").convert("RGB").resize((box, box), Image.NEAREST)

BG = radial_bg()
CX = W//2

def build(slug, title, subtitle, checks, cta):
    img = BG.copy()
    d = ImageDraw.Draw(img)

    # cadre + coins
    m1, m2 = s(34), s(46)
    d.rectangle([m1, m1, W-m1, Hh-m1], outline=GOLD, width=s(3))
    d.rectangle([m2, m2, W-m2, Hh-m2], outline=GOLD_DIM, width=s(1))
    L, inset = s(58), s(34)
    corner(d, inset, inset, 1, 1, L); corner(d, W-inset, inset, -1, 1, L)
    corner(d, inset, Hh-inset, 1, -1, L); corner(d, W-inset, Hh-inset, -1, -1, L)

    # eyebrow
    tracked(d, CX, s(92), "AG STARTER PRO", F("playfair-700.ttf", 24), GOLD, s(10))
    # dashes + losange
    for sgn in (-1, 1):
        d.line([(CX+sgn*s(70), s(132)), (CX+sgn*s(150), s(132))], fill=GOLD_DIM, width=s(1))
    d.polygon([(CX, s(126)), (CX+s(6), s(132)), (CX, s(138)), (CX-s(6), s(132))], fill=GOLD)

    # TITRE dégradé
    font, lines, sz = fit(d, title, "playfair-900.ttf", W-s(180), 86, 50, 2)
    lh = sz*1.04
    y = s(200) - (len(lines)-1)*lh/2
    for ln in lines:
        grad_text(img, d, CX, y, ln, font); y += lh

    # sous-titre
    sf, slines, _ = fit(d, subtitle, "playfair-italic.ttf", W-s(220), 31, 25, 2)
    y = s(305)
    for ln in slines:
        w = d.textlength(ln, font=sf); d.text((CX-w/2, y), ln, font=sf, fill=CREAM); y += s(40)

    # filet
    d.line([(CX-s(120), s(382)), (CX+s(120), s(382))], fill=GOLD_DIM, width=s(1))

    # CHECKLIST (texte direct sur le noir, coches dorées)
    cf = F("playfair-400.ttf", 33)
    # largeur du bloc = plus longue ligne, centré
    bw = max(d.textlength(c, font=cf) for c in checks)
    bx = CX - (bw + s(56))/2 + s(28)     # x du texte (après la coche)
    y = s(430)
    for c in checks:
        check_badge(d, bx - s(34), y + s(20), s(16))
        d.text((bx, y), c, font=cf, fill=CREAM)
        y += s(58)

    # diamant
    diamond(d, CX, s(740), s(46), s(54))

    # CTA bouton or plein, texte noir
    bf = F("playfair-700.ttf", 32)
    bw2 = d.textlength(cta, font=bf); padx = s(40); bh = s(70)
    bx0 = CX-bw2/2-padx; bx1 = CX+bw2/2+padx; by0 = s(800); by1 = by0+bh
    d.rounded_rectangle([bx0, by0, bx1, by1], radius=bh//2, fill=GOLD)
    d.rounded_rectangle([bx0, by0, bx1, by1], radius=bh//2, outline=GOLD_HI, width=s(2))
    asc, desc = bf.getmetrics()
    d.text((CX-bw2/2, by0+(bh-(asc+desc))/2), cta, font=bf, fill=(20,16,8))

    # ===== FOOTER : QR (gauche) + contact (droite) =====
    fy = s(960)
    qbox = s(150); card = qbox+s(22)
    qx = s(110); qy = fy
    d.rounded_rectangle([qx, qy, qx+card, qy+card], radius=s(12), fill=WHITE)
    img.paste(make_qr(CONTACT_URL, qbox), (qx+s(11), qy+s(11)))
    d.rounded_rectangle([qx, qy, qx+card, qy+card], radius=s(12), outline=GOLD, width=s(2))

    tx = qx+card+s(40)
    d.text((tx, fy+s(6)), "APPELEZ-NOUS", font=F("playfair-400.ttf", 19), fill=GOLD_DIM)
    grad_text(img, ImageDraw.Draw(img), tx + d.textlength(PHONE, font=F('playfair-700.ttf',44))/2,
              fy+s(34), PHONE, F("playfair-700.ttf", 44), shadow=False)
    d.text((tx, fy+s(104)), "Scannez ou écrivez-nous :", font=F("playfair-italic.ttf", 20), fill=CREAM)
    d.text((tx, fy+s(134)), "alliancegroupe-inc.com/contact", font=F("playfair-700.ttf", 21), fill=GOLD)

    out = img.resize((1080, 1350), Image.LANCZOS)
    p = os.path.join(OUTDIR, f"post-{slug}.png"); out.save(p, "PNG"); print("OK", p); return p

POSTS = [
    ("devis", "Devis gratuit", "Votre projet web chiffré clairement",
     ["Réponse sous 24 h", "Sans engagement", "Estimation détaillée", "Conseil offert inclus"],
     "DEMANDER MON DEVIS"),
    ("conseil", "Conseil offert", "30 min avec un expert Alliance Groupe",
     ["Audit de votre présence", "Stratégie sur-mesure", "Sans engagement", "Par téléphone ou visio"],
     "RÉSERVER MON CONSEIL"),
    ("sites-490", "Site web dès 490 €", "Design premium, livré clé en main",
     ["Création complète", "Optimisé mobile & SEO", "Mise en ligne incluse", "Livraison rapide"],
     "VOIR L'OFFRE"),
    ("realisation", "Nos réalisations", "Des sites sur-mesure qui convertissent",
     ["Restaurant · Artisan · Coach", "Avocat · Barber · Association", "Design haut de gamme", "+340% de leads en moyenne"],
     "VOIR NOS RÉALISATIONS"),
]

if __name__ == "__main__":
    for p in POSTS: build(*p)
    print("Terminé :", OUTDIR)
