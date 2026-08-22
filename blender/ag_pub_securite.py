# -*- coding: utf-8 -*-
"""
Alliance Groupe — deux visuels publicitaires « sécurité », carrés, or sur nuit.

Même langage visuel que pub-express-* : objet doré posé sur un marbre noir
poli, fond nuit, éclairage studio. Aucun texte incrusté — le texte reste dans
l'annonce Meta, pas dans l'image (moins de rejets, et on peut le changer sans
refaire le rendu).

    A. ag-pub-audit.png        le bouclier balayé par une ligne d'analyse
    B. ag-pub-ransomware.png   le bouclier encaisse l'attaque et tient

Réutilise entièrement ag_embleme_securite.py : mêmes matériaux, même bouclier,
même studio. Relancer le script reconstruit les deux scènes à l'identique.

    blender -b -P ag_pub_securite.py
"""

import bpy, io, math, os
from mathutils import Vector

BASE = os.path.dirname(os.path.abspath(__file__)) if "__file__" in globals() else os.getcwd()
SRC = os.path.join(BASE, "ag_embleme_securite.py")

NS = {"__name__": "ag_emb_lib", "__file__": SRC}
exec(io.open(SRC, encoding="utf-8").read(), NS)

srgb_to_linear = NS["srgb_to_linear"]
set_input      = NS["set_input"]
principled     = NS["principled"]
mat_or         = NS["mat_or"]
mat_emissif    = NS["mat_emissif"]
OR             = NS["OR"]
ORANGE         = NS["ORANGE"]

RES     = 1080
SAMPLES = 96
OUT_DIR = os.path.join(BASE, "rendus")
ROUGE   = (0.72, 0.13, 0.13)


# ─── Le socle : marbre noir poli ─────────────────────────────────────────────
def marbre(z=-0.30):
    bpy.ops.mesh.primitive_plane_add(size=40.0, location=(0, 0, z))
    sol = bpy.context.object
    sol.name = "Marbre"

    m = bpy.data.materials.new("AG_MarbreNoir")
    m.use_nodes = True
    bsdf = principled(m)
    set_input(bsdf, ["Base Color", "Couleur de base"], (0.012, 0.012, 0.016, 1.0), 0)
    set_input(bsdf, ["Metallic", "Métallique"], 0.0, 1)
    set_input(bsdf, ["Roughness", "Rugosité"], 0.20, 2)
    set_input(bsdf, ["Specular IOR Level", "Specular", "Spéculaire"], 0.85)
    sol.data.materials.append(m)
    return sol


def calmer_lumieres():
    """Le marbre est un miroir : les sources studio s'y refletent en flaques
    blanches. On baisse, on remonte, on eloigne — la flaque devient un halo."""
    reglages = {"Cle": (430, (-3.1, -3.4, 3.1)),
                "Rim": (300, (2.2, 3.0, 2.6)),
                "Fill": (90, (3.4, -2.6, 1.4))}
    for ob in bpy.context.scene.objects:
        if ob.type != "LIGHT" or ob.name not in reglages:
            continue
        energie, position = reglages[ob.name]
        ob.data.energy = energie
        ob.location = position


def cadrer(echelle=0.55, hauteur=0.30):
    """Recule l'emblème pour laisser respirer le marbre et le fond."""
    pivot = bpy.data.objects.new("Cadre", None)
    bpy.context.collection.objects.link(pivot)
    for ob in list(bpy.context.scene.objects):
        if ob.type == "MESH" and ob.name != "Marbre" and ob.parent is None:
            ob.parent = pivot
    pivot.scale = (echelle, echelle, echelle)
    pivot.location = (0, 0, hauteur)
    return pivot


def lueur(couleur=None, force=3.0):
    """Une lueur basse et large derriere le bouclier — un souffle de couleur sur
    le fond, jamais un disque plein qui vole la vedette a l'or."""
    d = bpy.data.lights.new("Lueur", type="AREA")
    d.shape = "ELLIPSE"
    d.size = 3.4
    d.size_y = 1.0
    d.energy = force * 18
    d.color = couleur or ORANGE
    o = bpy.data.objects.new("Lueur", d)
    o.location = (0, 2.4, 1.5)
    o.rotation_euler = (math.radians(-68), 0, 0)
    bpy.context.collection.objects.link(o)
    return o


# ─── A. Audit : la ligne d'analyse ───────────────────────────────────────────
def scene_audit():
    NS["construire"]()
    marbre()
    cadrer()
    calmer_lumieres()
    lueur(ORANGE, 2.2)

    # la barre d'analyse, devant le bouclier
    bpy.ops.mesh.primitive_plane_add(size=1.0, location=(0, -0.62, 0.22))
    barre = bpy.context.object
    barre.name = "Analyse"
    barre.rotation_euler = (math.radians(90), 0, 0)
    barre.scale = (0.80, 0.011, 1.0)
    bpy.ops.object.transform_apply(scale=True)
    barre.data.materials.append(mat_emissif("AG_Analyse", OR, 11.0))

    # deux barres fantômes plus faibles : la trace du balayage
    for i, (dz, f) in enumerate(((0.15, 3.4), (0.29, 1.3))):
        bpy.ops.mesh.primitive_plane_add(size=1.0, location=(0, -0.62, 0.22 + dz))
        t = bpy.context.object
        t.name = "Trace%d" % i
        t.rotation_euler = (math.radians(90), 0, 0)
        t.scale = (0.74, 0.009, 1.0)
        bpy.ops.object.transform_apply(scale=True)
        t.data.materials.append(mat_emissif("AG_Trace%d" % i, OR, f))
    return "ag-pub-audit.png"


# ─── B. Ransomware : l'attaque qui ricoche ───────────────────────────────────
def trait(cible, direction, longueur=1.2, rayon=0.011, force=16.0,
          couleur=None, ecart=0.05, nom="Trait"):
    """Un trait de lumiere qui fonce sur le bouclier et s'arrete net.
    Un projectile sombre sur fond noir serait invisible : ici l'attaque
    se voit parce qu'elle brule."""
    d = Vector(direction).normalized()
    centre = Vector(cible) + d * (longueur / 2.0 + ecart)
    bpy.ops.mesh.primitive_cylinder_add(vertices=12, radius=rayon,
                                        depth=longueur, location=centre)
    ob = bpy.context.object
    ob.name = nom
    ob.rotation_euler = d.to_track_quat("Z", "Y").to_euler()
    ob.data.materials.append(mat_emissif("AG_" + nom, couleur or ORANGE, force))
    return ob


def eclats(cible, graines, force=55.0):
    """La gerbe d'etincelles au point d'impact."""
    for i, (dx, dz, r) in enumerate(graines):
        bpy.ops.mesh.primitive_ico_sphere_add(
            radius=r, subdivisions=2,
            location=(cible[0] + dx, cible[1] - 0.04, cible[2] + dz))
        e = bpy.context.object
        e.name = "Eclat%d" % i
        e.data.materials.append(mat_emissif("AG_Eclat%d" % i, ORANGE, force))


def scene_ransomware():
    NS["construire"]()
    marbre()
    cadrer()
    calmer_lumieres()
    lueur(ROUGE, 3.0)

    GAUCHE = (-0.78, -0.22, 0.60)
    DROITE = (0.78, -0.22, 0.56)

    # les tirs : ils convergent, ils n'arrivent pas tous a la meme hauteur
    tirs = [
        ((-0.46, -0.10, 0.52), GAUCHE, 1.45, 0.013, 19.0, ROUGE),
        ((-0.42, -0.10, 0.22), GAUCHE, 1.10, 0.010, 13.0, ROUGE),
        ((-0.30, -0.10, 0.74), GAUCHE, 0.85, 0.009,  9.0, ORANGE),
        ((0.46, -0.10, 0.38),  DROITE, 1.30, 0.012, 17.0, ROUGE),
        ((0.34, -0.10, 0.70),  DROITE, 0.95, 0.009, 10.0, ORANGE),
    ]
    for i, (cible, direction, lg, r, f, c) in enumerate(tirs):
        trait(cible, direction, lg, r, f, c, nom="Tir%d" % i)

    # deux impacts seulement : une gerbe partout, ca fait guirlande
    eclats((-0.46, -0.10, 0.52), [(-0.05, 0.04, 0.020), (0.03, -0.03, 0.015),
                                  (-0.02, 0.08, 0.012), (0.06, 0.06, 0.010)])
    eclats((0.46, -0.10, 0.38), [(0.05, 0.05, 0.018), (-0.03, -0.04, 0.013),
                                 (0.02, 0.09, 0.011)])
    return "ag-pub-ransomware.png"


def rendre(nom):
    sc = bpy.context.scene
    sc.cycles.samples = SAMPLES
    sc.cycles.use_denoising = True
    sc.render.resolution_x = RES
    sc.render.resolution_y = RES
    sc.render.film_transparent = False
    sc.render.image_settings.color_mode = "RGB"
    os.makedirs(OUT_DIR, exist_ok=True)
    sc.render.filepath = os.path.join(OUT_DIR, nom)
    bpy.ops.render.render(write_still=True)
    return sc.render.filepath


if __name__ == "__main__":
    for fabrique in (scene_audit, scene_ransomware):
        print("RENDU", rendre(fabrique()), flush=True)
