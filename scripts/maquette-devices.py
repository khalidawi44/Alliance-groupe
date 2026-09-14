#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
maquette-devices.py — la vitrine « ordinateur + tablette + telephone » d'un site livre.

Reprend le style deja pose par gwen-maquette.jpg : fond presque noir, halo a la
couleur du site presente, fenetre de navigateur a la macOS, appareils aux coins
arrondis poses avec leur ombre. On ajoute la tablette, que Gwen n'avait pas.

Les trois captures viennent du VRAI site, prises aux trois tailles reelles
(1440x900, 834x1112, 390x844) : c'est une photo de la livraison, pas un dessin.

Usage :
    python3 scripts/maquette-devices.py \
        --desktop desk.png --tablette tab.png --phone tel.png \
        --domaine elagage-vertou.fr --teinte "#7fb04a" \
        --sortie alliance-groupe-theme/assets/images/realisations/la-environnement.jpg
"""

import argparse, os
from PIL import Image, ImageDraw, ImageFilter

L, H = 1800, 1125          # meme format que gwen-maquette.jpg
FOND = (5, 6, 5)


def coins(img, r):
    """Arrondit les coins d'une image (masque alpha)."""
    m = Image.new("L", img.size, 0)
    ImageDraw.Draw(m).rounded_rectangle([0, 0, img.size[0] - 1, img.size[1] - 1], r, fill=255)
    out = img.convert("RGBA")
    out.putalpha(m)
    return out


def ombre(taille, r, flou=26, opacite=150, grossir=8):
    """Une ombre portee douce, un peu plus large que l'objet qu'elle porte."""
    w, h = taille
    c = Image.new("RGBA", (w + grossir * 2 + flou * 4, h + grossir * 2 + flou * 4), (0, 0, 0, 0))
    d = ImageDraw.Draw(c)
    d.rounded_rectangle(
        [flou * 2 - grossir, flou * 2 - grossir, flou * 2 + w + grossir, flou * 2 + h + grossir],
        r + grossir, fill=(0, 0, 0, opacite))
    return c.filter(ImageFilter.GaussianBlur(flou))


def halo(teinte, w, h, cx, cy, rayon, force=0.42):
    """Halo radial : c'est lui qui rattache l'image a la couleur du site presente."""
    p = 180
    g = Image.new("L", (p, p), 0)
    d = ImageDraw.Draw(g)
    for i in range(p // 2, 0, -1):
        v = int(255 * (1 - i / (p / 2)) ** 2.1 * force)
        d.ellipse([p // 2 - i, p // 2 - i, p // 2 + i, p // 2 + i], fill=v)
    g = g.resize((rayon * 2, rayon * 2), Image.LANCZOS)
    couche = Image.new("RGBA", (w, h), (0, 0, 0, 0))
    teint = Image.new("RGBA", (rayon * 2, rayon * 2), teinte + (255,))
    teint.putalpha(g)
    couche.alpha_composite(teint, (cx - rayon, cy - rayon))
    return couche


def cadre_navigateur(shot, larg, domaine):
    """Fenetre de navigateur : barre grise, trois pastilles, adresse du site."""
    barre = 46
    ratio = shot.size[1] / shot.size[0]
    haut = int(larg * ratio)
    contenu = shot.resize((larg, haut), Image.LANCZOS).convert("RGBA")

    f = Image.new("RGBA", (larg, haut + barre), (43, 45, 52, 255))
    d = ImageDraw.Draw(f)
    for i, c in enumerate([(255, 95, 86), (255, 189, 46), (39, 201, 63)]):
        x = 26 + i * 26
        d.ellipse([x, barre // 2 - 7, x + 14, barre // 2 + 7], fill=c)
    # La barre d'adresse : on y lit le vrai domaine, c'est ce qui prouve que le site existe.
    d.rounded_rectangle([124, 11, larg - 26, barre - 11], 12, fill=(31, 33, 39, 255))
    try:
        from PIL import ImageFont
        police = ImageFont.load_default(15)
    except Exception:
        police = None
    d.text((140, barre // 2), domaine, fill=(150, 156, 165), font=police, anchor="lm")
    f.paste(contenu, (0, barre))
    return coins(f, 14)


def cadre_appareil(shot, larg, bord, rayon, encoche=False):
    """Tablette ou telephone : une bordure sombre, et l'encoche pour le telephone."""
    ratio = shot.size[1] / shot.size[0]
    haut = int(larg * ratio)
    contenu = coins(shot.resize((larg, haut), Image.LANCZOS).convert("RGBA"), max(rayon - bord, 4))

    f = Image.new("RGBA", (larg + bord * 2, haut + bord * 2), (0, 0, 0, 0))
    d = ImageDraw.Draw(f)
    d.rounded_rectangle([0, 0, f.size[0] - 1, f.size[1] - 1], rayon, fill=(22, 24, 23, 255))
    f.alpha_composite(contenu, (bord, bord))
    if encoche:
        w = int(larg * 0.42)
        x = bord + (larg - w) // 2
        ImageDraw.Draw(f).rounded_rectangle([x, bord - 1, x + w, bord + 17], 9, fill=(14, 15, 14, 255))
    return f


def main():
    a = argparse.ArgumentParser()
    a.add_argument("--desktop", required=True)
    a.add_argument("--tablette", required=True)
    a.add_argument("--phone", required=True)
    a.add_argument("--domaine", required=True)
    a.add_argument("--teinte", default="#7fb04a")
    a.add_argument("--sortie", required=True)
    o = a.parse_args()

    t = o.teinte.lstrip("#")
    teinte = tuple(int(t[i:i + 2], 16) for i in (0, 2, 4))

    toile = Image.new("RGBA", (L, H), FOND + (255,))
    toile.alpha_composite(halo(teinte, L, H, 430, 620, 700, 0.58))
    toile.alpha_composite(halo(teinte, L, H, 1520, 330, 470, 0.30))

    nav = cadre_navigateur(Image.open(o.desktop), 1075, o.domaine)
    tab = cadre_appareil(Image.open(o.tablette), 372, 13, 26)
    tel = cadre_appareil(Image.open(o.phone), 244, 12, 32, encoche=True)

    # Du plus loin au plus proche : l'ordinateur derriere, le telephone devant.
    for img, (x, y), op in ((nav, (78, 150), 165), (tab, (1108, 268), 175), (tel, (1462, 452), 185)):
        om = ombre(img.size, 24, 30, op)
        toile.alpha_composite(om, (x - 60 - 8, y - 60 - 8 + 14))
        toile.alpha_composite(img, (x, y))

    os.makedirs(os.path.dirname(os.path.abspath(o.sortie)), exist_ok=True)
    final = toile.convert("RGB")
    if o.sortie.lower().endswith((".jpg", ".jpeg")):
        final.save(o.sortie, "JPEG", quality=88, optimize=True, progressive=True)
    else:
        final.save(o.sortie)
    print(f"✅ {o.sortie}  {final.size[0]}x{final.size[1]}  ({os.path.getsize(o.sortie)//1024} Ko)")


if __name__ == "__main__":
    main()
