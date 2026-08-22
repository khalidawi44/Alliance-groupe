# -*- coding: utf-8 -*-
"""
Alliance Groupe — les deux visuels securite, sur le bouclier realiste.

Meme mise en scene que ag_bouclier_realiste.py (studio, marbre, profondeur de
champ), plus l'element qui raconte l'offre :

    audit       trois lignes d'analyse qui balaient la piece
    ransomware  des tirs qui convergent, s'arretent net, et font des etincelles

    blender -b -P ag_pub_securite_v2.py
"""

import bpy, io, math, os
from mathutils import Vector

BASE = os.path.dirname(os.path.abspath(__file__)) if "__file__" in globals() else os.getcwd()
SRC  = os.path.join(BASE, "ag_bouclier_realiste.py")
NS   = {"__name__": "ag_bouclier_lib", "__file__": SRC}
exec(io.open(SRC, encoding="utf-8").read(), NS)

mat_emissif = NS["mat_emissif"]
OR, ORANGE  = NS["OR"], NS["ORANGE"]
ROUGE       = (0.78, 0.14, 0.14)


def trait(cible, direction, longueur, rayon, force, couleur, nom, ecart=0.06):
    d = Vector(direction).normalized()
    centre = Vector(cible) + d * (longueur / 2.0 + ecart)
    bpy.ops.mesh.primitive_cylinder_add(vertices=14, radius=rayon,
                                        depth=longueur, location=centre)
    ob = bpy.context.object
    ob.name = nom
    ob.rotation_euler = d.to_track_quat("Z", "Y").to_euler()
    ob.data.materials.append(mat_emissif("AG_" + nom, couleur, force))
    return ob


def eclats(cible, graines, force=45.0, prefixe="Eclat"):
    for i, (dx, dz, r) in enumerate(graines):
        bpy.ops.mesh.primitive_ico_sphere_add(
            radius=r, subdivisions=2,
            location=(cible[0] + dx, cible[1] - 0.07, cible[2] + dz))
        e = bpy.context.object
        e.name = "%s%d" % (prefixe, i)
        e.data.materials.append(mat_emissif("AG_%s%d" % (prefixe, i), ORANGE, force))


def barre(z, demi_largeur, epaisseur, force, y=-0.98, nom="Analyse"):
    bpy.ops.mesh.primitive_plane_add(size=1.0, location=(0, y, z))
    b = bpy.context.object
    b.name = nom
    b.rotation_euler = (math.radians(90), 0, 0)
    b.scale = (demi_largeur, epaisseur, 1.0)
    bpy.ops.object.transform_apply(scale=True)
    b.data.materials.append(mat_emissif("AG_" + nom, OR, force))
    return b


def scene_audit():
    NS["construire"]()
    barre(0.46, 1.02, 0.016, 12.0, nom="Analyse0")
    barre(0.10, 1.16, 0.013, 5.0,  nom="Analyse1")
    barre(-0.28, 1.06, 0.011, 2.4, nom="Analyse2")
    return "pub-securite-audit.png"


def scene_ransomware():
    NS["construire"]()
    GAUCHE = (-0.78, -0.24, 0.58)
    DROITE = (0.78, -0.24, 0.54)
    tirs = [
        ((-0.84, -0.26, 0.40),  GAUCHE, 2.40, 0.020, 17.0, ROUGE),
        ((-0.76, -0.30, -0.15), GAUCHE, 1.90, 0.016, 12.0, ROUGE),
        ((-0.55, -0.24, 0.80),  GAUCHE, 1.50, 0.014,  8.0, ORANGE),
        ((0.84, -0.26, 0.15),   DROITE, 2.20, 0.019, 15.0, ROUGE),
        ((0.62, -0.24, 0.73),   DROITE, 1.60, 0.014,  9.0, ORANGE),
    ]
    for i, (cible, direction, lg, r, f, c) in enumerate(tirs):
        trait(cible, direction, lg, r, f, c, "Tir%d" % i)

    eclats((-0.84, -0.26, 0.40), [(-0.09, 0.07, 0.034), (0.05, -0.05, 0.026),
                                  (-0.03, 0.14, 0.020), (0.10, 0.11, 0.017)])
    eclats((0.84, -0.26, 0.15), [(0.09, 0.09, 0.031), (-0.05, -0.07, 0.023),
                                 (0.04, 0.16, 0.019)], prefixe="Gerbe")
    return "pub-securite-ransomware.png"


if __name__ == "__main__":
    for fabrique in (scene_audit, scene_ransomware):
        nom = fabrique()
        print("RENDU", NS["rendre"](nom, 1080, 150), flush=True)
