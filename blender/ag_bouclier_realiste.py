# -*- coding: utf-8 -*-
"""
Alliance Groupe — bouclier or, version realiste.

Ce que la premiere version n'avait pas et qui faisait « plastique » :

  1. RIEN A REFLETER. Un metal, on ne le voit pas : on voit ce qu'il reflete.
     Sur un fond noir absolu, l'or n'a aucune information a renvoyer, donc il
     tombe a plat. Ici on construit un vrai studio : degrade d'environnement +
     trois softbox invisibles a la camera mais bien presentes dans les reflets.
  2. AUCUNE MICRO-SURFACE. Une rugosite constante n'existe pas dans la nature.
     Ici : martelage (Voronoi), rayures d'atelier (Wave distordue), grain fin,
     et une rugosite qui varie sur toute la piece.
  3. AUCUNE USURE. Les aretes se polissent a l'usage, les creux s'encrassent.
     On lit la convexite de la geometrie (Pointiness) pour faire les deux.
  4. UNE SEULE PIECE LISSE. On ajoute un jonc borde qui suit la silhouette,
     des rivets, et un bombe : de la matiere pour accrocher la lumiere.

    blender -b -P ag_bouclier_realiste.py
"""

import bpy, bmesh, math, os
from mathutils import Vector

OUT_DIR = os.path.join(
    os.path.dirname(os.path.abspath(__file__)) if "__file__" in globals() else os.getcwd(),
    "rendus")
RES     = 1080
SAMPLES = 220
OR      = (0.831, 0.706, 0.361)
ORANGE  = (0.953, 0.478, 0.122)
NUIT    = (0.039, 0.039, 0.059)

PROFIL = [(0.00, 1.00), (0.62, 0.86), (0.86, 0.55), (0.90, 0.10),
          (0.72, -0.46), (0.38, -0.86), (0.00, -1.06)]
ECHELLE_X = 1.15


def srgb_to_linear(c):
    return tuple((v / 12.92 if v <= 0.04045 else ((v + 0.055) / 1.055) ** 2.4) for v in c)


def contour(marge=1.0):
    pts = [(-x, y) for x, y in reversed(PROFIL[1:])] + [(x, y) for x, y in PROFIL]
    return [Vector((x * ECHELLE_X * marge, 0.0, y * marge)) for x, y in pts]


def purge():
    bpy.ops.object.select_all(action="SELECT")
    bpy.ops.object.delete(use_global=False)
    for coll in (bpy.data.meshes, bpy.data.materials, bpy.data.curves,
                 bpy.data.lights, bpy.data.cameras, bpy.data.worlds):
        for item in list(coll):
            if item.users == 0:
                try: coll.remove(item)
                except Exception: pass


def entree(node, noms, valeur, index=None):
    for n in noms:
        if n in node.inputs:
            node.inputs[n].default_value = valeur
            return True
    if index is not None and index < len(node.inputs):
        try:
            node.inputs[index].default_value = valeur
            return True
        except Exception:
            pass
    return False


def principled(mat):
    nt = mat.node_tree
    n = next((x for x in nt.nodes if x.type == "BSDF_PRINCIPLED"), None)
    if n is None:
        n = nt.nodes.new("ShaderNodeBsdfPrincipled")
        s = next((x for x in nt.nodes if x.type == "OUTPUT_MATERIAL"), None) or \
            nt.nodes.new("ShaderNodeOutputMaterial")
        nt.links.new(n.outputs[0], s.inputs[0])
    return n


# ─── Le materiau : la ou se joue tout le realisme ────────────────────────────
def mat_or_realiste(nom="AG_Or_Realiste", teinte=None, rugosite=0.22,
                    martelage=0.85, usure=1.0):
    m = bpy.data.materials.new(nom)
    m.use_nodes = True
    nt = m.node_tree
    bsdf = principled(m)
    base = srgb_to_linear(teinte or OR)
    creux = srgb_to_linear((0.36, 0.27, 0.13))     # l'or encrasse dans les creux

    def n(t, **kw):
        x = nt.nodes.new(t)
        for k, v in kw.items():
            setattr(x, k, v)
        return x

    coord = n("ShaderNodeTexCoord")
    geo   = n("ShaderNodeNewGeometry")

    # 1. martelage — la piece a ete forgee, pas moulee
    mart = n("ShaderNodeTexVoronoi", feature="F1", distance="EUCLIDEAN")
    mart.inputs["Scale"].default_value = 27.0
    try: mart.inputs["Smoothness"].default_value = 1.0
    except Exception: pass
    nt.links.new(coord.outputs["Object"], mart.inputs["Vector"])

    # 2. rayures d'atelier — orientees, irregulieres
    ray = n("ShaderNodeTexWave", wave_type="BANDS", bands_direction="DIAGONAL",
            wave_profile="SIN")
    ray.inputs["Scale"].default_value = 3.4
    ray.inputs["Distortion"].default_value = 24.0
    ray.inputs["Detail"].default_value = 8.0
    ray.inputs["Detail Scale"].default_value = 3.0
    nt.links.new(coord.outputs["Object"], ray.inputs["Vector"])

    # 3. grain fin — ce qui casse le reflet parfait
    grain = n("ShaderNodeTexNoise")
    grain.inputs["Scale"].default_value = 260.0
    grain.inputs["Detail"].default_value = 4.0
    nt.links.new(coord.outputs["Object"], grain.inputs["Vector"])

    # 4. grandes variations de poli
    large = n("ShaderNodeTexNoise")
    large.inputs["Scale"].default_value = 5.5
    large.inputs["Detail"].default_value = 6.0
    nt.links.new(coord.outputs["Object"], large.inputs["Vector"])

    # 5. convexite : aretes polies, creux encrasses
    conv = n("ShaderNodeValToRGB")
    conv.color_ramp.elements[0].position = 0.44
    conv.color_ramp.elements[1].position = 0.58
    nt.links.new(geo.outputs["Pointiness"], conv.inputs["Fac"])

    # couleur : or propre sur les aretes, or terni dans les creux
    mixc = n("ShaderNodeMixRGB", blend_type="MIX")
    mixc.inputs["Color1"].default_value = (*creux, 1.0)
    mixc.inputs["Color2"].default_value = (*base, 1.0)
    fac_usure = n("ShaderNodeMapRange")
    fac_usure.inputs["From Min"].default_value = 0.0
    fac_usure.inputs["From Max"].default_value = 1.0
    fac_usure.inputs["To Min"].default_value = 1.0 - 0.55 * usure
    fac_usure.inputs["To Max"].default_value = 1.0
    nt.links.new(conv.outputs["Color"], fac_usure.inputs["Value"])
    nt.links.new(fac_usure.outputs["Result"], mixc.inputs["Fac"])
    nt.links.new(mixc.outputs["Color"], bsdf.inputs[0])

    # rugosite : base + grandes variations + rayures, moins le poli des aretes
    r_large = n("ShaderNodeMapRange")
    r_large.inputs["To Min"].default_value = max(0.04, rugosite - 0.09)
    r_large.inputs["To Max"].default_value = rugosite + 0.13
    nt.links.new(large.outputs["Fac"], r_large.inputs["Value"])

    r_ray = n("ShaderNodeMath", operation="MULTIPLY_ADD")
    r_ray.inputs[1].default_value = 0.11
    nt.links.new(ray.outputs["Fac"], r_ray.inputs[0])
    nt.links.new(r_large.outputs["Result"], r_ray.inputs[2])

    r_poli = n("ShaderNodeMath", operation="MULTIPLY_ADD")
    r_poli.inputs[1].default_value = -0.07 * usure
    nt.links.new(conv.outputs["Color"], r_poli.inputs[0])
    nt.links.new(r_ray.outputs[0], r_poli.inputs[2])

    r_clamp = n("ShaderNodeClamp")
    r_clamp.inputs["Min"].default_value = 0.03
    r_clamp.inputs["Max"].default_value = 0.62
    nt.links.new(r_poli.outputs[0], r_clamp.inputs["Value"])
    entree(bsdf, ["Metallic", "Métallique"], 1.0, 1)
    nt.links.new(r_clamp.outputs[0], bsdf.inputs[2])

    # relief : martelage → grain → rayures, chaines l'un dans l'autre
    b1 = n("ShaderNodeBump"); b1.inputs["Strength"].default_value = 0.075 * martelage
    b1.inputs["Distance"].default_value = 0.05
    nt.links.new(mart.outputs["Distance"], b1.inputs["Height"])

    b2 = n("ShaderNodeBump"); b2.inputs["Strength"].default_value = 0.055
    nt.links.new(grain.outputs["Fac"], b2.inputs["Height"])
    nt.links.new(b1.outputs["Normal"], b2.inputs["Normal"])

    b3 = n("ShaderNodeBump"); b3.inputs["Strength"].default_value = 0.016
    nt.links.new(ray.outputs["Fac"], b3.inputs["Height"])
    nt.links.new(b2.outputs["Normal"], b3.inputs["Normal"])
    entree(bsdf, ["Normal", "Normale"], None) if False else None
    nt.links.new(b3.outputs["Normal"], bsdf.inputs[bsdf.inputs.find("Normal")]
                 if bsdf.inputs.find("Normal") >= 0 else bsdf.inputs[-1])

    # brossage : l'or n'est pas isotrope
    tan = n("ShaderNodeTangent", direction_type="RADIAL", axis="Y")
    i_ani = bsdf.inputs.find("Anisotropic")
    if i_ani >= 0:
        bsdf.inputs[i_ani].default_value = 0.45
    i_tan = bsdf.inputs.find("Tangent")
    if i_tan >= 0:
        nt.links.new(tan.outputs["Tangent"], bsdf.inputs[i_tan])
    return m


def mat_emissif(nom, couleur, force=9.0):
    m = bpy.data.materials.new(nom)
    m.use_nodes = True
    nt = m.node_tree
    nt.nodes.clear()
    out = nt.nodes.new("ShaderNodeOutputMaterial")
    emi = nt.nodes.new("ShaderNodeEmission")
    emi.inputs[0].default_value = (*srgb_to_linear(couleur), 1.0)
    emi.inputs[1].default_value = force
    nt.links.new(emi.outputs[0], out.inputs[0])
    return m


def mat_acier(nom="AG_Acier"):
    m = bpy.data.materials.new(nom)
    m.use_nodes = True
    nt = m.node_tree
    bsdf = principled(m)
    entree(bsdf, ["Base Color"], (*srgb_to_linear((0.14, 0.145, 0.16)), 1.0), 0)
    entree(bsdf, ["Metallic"], 1.0, 1)
    entree(bsdf, ["Roughness"], 0.30, 2)
    coord = nt.nodes.new("ShaderNodeTexCoord")
    grain = nt.nodes.new("ShaderNodeTexNoise")
    grain.inputs["Scale"].default_value = 190.0
    nt.links.new(coord.outputs["Object"], grain.inputs["Vector"])
    b = nt.nodes.new("ShaderNodeBump")
    b.inputs["Strength"].default_value = 0.12
    nt.links.new(grain.outputs["Fac"], b.inputs["Height"])
    i = bsdf.inputs.find("Normal")
    if i >= 0:
        nt.links.new(b.outputs["Normal"], bsdf.inputs[i])
    return m


# ─── Geometrie ───────────────────────────────────────────────────────────────
def rayon_contour(theta, pts):
    """Rayon du contour a un angle donne. L'ecusson est etoile autour de son
    centre, donc un seul segment repond — on prend la premiere intersection."""
    dx, dz = math.cos(theta), math.sin(theta)
    meilleur = None
    n = len(pts)
    for i in range(n):
        ax, az = pts[i].x, pts[i].z
        bx, bz = pts[(i + 1) % n].x, pts[(i + 1) % n].z
        ex, ez = bx - ax, bz - az
        den = ex * dz - ez * dx
        if abs(den) < 1e-12:
            continue
        u = (az * dx - ax * dz) / den        # position le long du segment
        if u < -1e-9 or u > 1 + 1e-9:
            continue
        s_ = (ax + u * ex) * dx + (az + u * ez) * dz
        if s_ > 1e-6 and (meilleur is None or s_ < meilleur):
            meilleur = s_
    return meilleur or 1.0


def profil_creux(r):
    """Le profil de la plaque, en profondeur, du centre vers le bord.
    Un bouclier n'est pas une plaque : il bombe, il a une gorge, il a un bord
    qui retombe. C'est ce relief qui donne des reflets qui racontent quelque
    chose au lieu d'un aplat."""
    bombe = -0.34 * (1.0 - r * r)
    gorge = 0.050 * math.exp(-((r - 0.780) / 0.048) ** 2)
    levre = -0.052 * math.exp(-((r - 0.900) / 0.052) ** 2)
    chute = 0.07 * max(0.0, (r - 0.966) / 0.034) ** 2
    return bombe + gorge + levre + chute


def plaque(mat, anneaux=44, secteurs=180):
    pts = contour()
    me = bpy.data.meshes.new("Bouclier")
    bm = bmesh.new()

    centre = bm.verts.new((0.0, profil_creux(0.0), 0.0))
    grille = []
    for k in range(1, anneaux + 1):
        r = k / float(anneaux)
        y = profil_creux(r)
        rangee = []
        for j in range(secteurs):
            th = 2.0 * math.pi * j / secteurs
            R = rayon_contour(th, pts)
            rangee.append(bm.verts.new((R * r * math.cos(th), y, R * r * math.sin(th))))
        grille.append(rangee)

    for j in range(secteurs):
        bm.faces.new((centre, grille[0][j], grille[0][(j + 1) % secteurs]))
    for k in range(anneaux - 1):
        for j in range(secteurs):
            j2 = (j + 1) % secteurs
            bm.faces.new((grille[k][j], grille[k + 1][j],
                          grille[k + 1][j2], grille[k][j2]))
    bmesh.ops.recalc_face_normals(bm, faces=bm.faces[:])
    bm.to_mesh(me)
    bm.free()

    ob = bpy.data.objects.new("Bouclier", me)
    bpy.context.collection.objects.link(ob)

    sol = ob.modifiers.new("Epaisseur", "SOLIDIFY")
    sol.thickness = 0.13
    sol.offset = 0.0
    sol.use_rim = True
    bev = ob.modifiers.new("Chanfrein", "BEVEL")
    bev.width = 0.014
    bev.segments = 4
    bev.limit_method = "ANGLE"
    bev.angle_limit = math.radians(35)

    ob.data.materials.append(mat)
    for pol in ob.data.polygons:
        pol.use_smooth = True
    return ob


def jonc(mat, marge=0.90, y=None, epaisseur=0.036):
    """Le cordon qui borde l'ecusson : c'est lui qui accroche la lumiere."""
    if y is None:
        y = profil_creux(marge) - 0.065
    cu = bpy.data.curves.new("Jonc", "CURVE")
    cu.dimensions = "3D"
    cu.bevel_depth = epaisseur
    cu.bevel_resolution = 8
    cu.resolution_u = 6
    sp = cu.splines.new("POLY")
    pts = contour(marge)
    sp.points.add(len(pts) - 1)
    for i, p in enumerate(pts):
        sp.points[i].co = (p.x, y, p.z, 1.0)
    sp.use_cyclic_u = True
    ob = bpy.data.objects.new("Jonc", cu)
    bpy.context.collection.objects.link(ob)
    ob.data.materials.append(mat)
    return ob


def rivets(mat, marge=0.90, y=None, rayon=0.026, combien=12):
    """Des rivets espaces le long du jonc : de la ponctuation, et des reflets."""
    if y is None:
        y = profil_creux(marge) - 0.098
    pts = contour(marge)
    n = len(pts)
    poses = []
    for k in range(combien):
        t = k * (n / float(combien))
        i, f = int(t) % n, t - int(t)
        a, b = pts[i], pts[(i + 1) % n]
        poses.append(a.lerp(b, f))
    for i, p in enumerate(poses):
        bpy.ops.mesh.primitive_uv_sphere_add(radius=rayon, segments=24, ring_count=12,
                                             location=(p.x, y, p.z))
        r = bpy.context.object
        r.name = "Rivet%02d" % i
        r.scale = (1.0, 0.62, 1.0)
        bpy.ops.object.transform_apply(scale=True)
        r.data.materials.append(mat)
        for pol in r.data.polygons:
            pol.use_smooth = True


def cadenas(mat_corps, mat_anse, chaud):
    bpy.ops.mesh.primitive_cube_add(size=1.0, location=(0.0, -0.62, -0.06))
    corps = bpy.context.object
    corps.name = "Cadenas_Corps"
    corps.scale = (0.46, 0.20, 0.36)
    bpy.ops.object.transform_apply(scale=True)
    b = corps.modifiers.new("Chanfrein", "BEVEL")
    b.width = 0.075; b.segments = 8; b.limit_method = "ANGLE"
    s = corps.modifiers.new("Lissage", "SUBSURF")
    s.levels = 2; s.render_levels = 3
    corps.data.materials.append(mat_corps)

    bpy.ops.mesh.primitive_torus_add(major_radius=0.258, minor_radius=0.054,
                                     major_segments=72, minor_segments=28,
                                     location=(0.0, -0.62, 0.30),
                                     rotation=(math.pi / 2, 0.0, 0.0))
    anse = bpy.context.object
    anse.name = "Cadenas_Anse"
    me = anse.data
    bm = bmesh.new(); bm.from_mesh(me)
    bmesh.ops.bisect_plane(bm, geom=bm.verts[:] + bm.edges[:] + bm.faces[:],
                           plane_co=(0, 0, 0), plane_no=(0, 0, -1), clear_inner=True)
    bm.to_mesh(me); bm.free()
    anse.data.materials.append(mat_anse)

    bpy.ops.mesh.primitive_cylinder_add(radius=0.046, depth=0.30,
                                        location=(0.0, -0.76, -0.02),
                                        rotation=(math.pi / 2, 0, 0), vertices=48)
    trou = bpy.context.object
    trou.name = "Serrure"
    trou.data.materials.append(chaud)

    for ob in (corps, anse, trou):
        for p in ob.data.polygons:
            p.use_smooth = True
    return corps, anse, trou


# ─── Le studio : ce que le metal va refleter ─────────────────────────────────
def environnement(force=0.46, haut=(0.055, 0.058, 0.075), bas=(0.014, 0.012, 0.012)):
    monde = bpy.data.worlds.new("AG_Studio")
    monde.use_nodes = True
    nt = monde.node_tree
    nt.nodes.clear()
    out = nt.nodes.new("ShaderNodeOutputWorld")
    fond = nt.nodes.new("ShaderNodeBackground")
    grad = nt.nodes.new("ShaderNodeTexGradient")
    grad.gradient_type = "LINEAR"
    map_ = nt.nodes.new("ShaderNodeMapping")
    map_.inputs["Rotation"].default_value = (0.0, math.radians(-90), 0.0)
    coord = nt.nodes.new("ShaderNodeTexCoord")
    ramp = nt.nodes.new("ShaderNodeValToRGB")
    ramp.color_ramp.elements[0].position = 0.30
    ramp.color_ramp.elements[0].color = (*srgb_to_linear(bas), 1.0)
    ramp.color_ramp.elements[1].position = 0.85
    ramp.color_ramp.elements[1].color = (*srgb_to_linear(haut), 1.0)
    nt.links.new(coord.outputs["Generated"], map_.inputs["Vector"])
    nt.links.new(map_.outputs["Vector"], grad.inputs["Vector"])
    nt.links.new(grad.outputs["Fac"], ramp.inputs["Fac"])
    nt.links.new(ramp.outputs["Color"], fond.inputs[0])
    fond.inputs[1].default_value = force
    nt.links.new(fond.outputs[0], out.inputs[0])
    bpy.context.scene.world = monde
    return monde


def softbox(nom, loc, rot, taille, force, couleur=(1, 1, 1)):
    """Un panneau lumineux qu'on ne voit pas, mais qui se lit dans l'or.
    C'est ca, un rendu produit : des reflets qui racontent une piece."""
    bpy.ops.mesh.primitive_plane_add(size=1.0, location=loc, rotation=rot)
    ob = bpy.context.object
    ob.name = nom
    ob.scale = (taille[0], taille[1], 1.0)
    bpy.ops.object.transform_apply(scale=True)
    ob.data.materials.append(mat_emissif("AG_Box_" + nom, couleur, force))
    ob.visible_camera = False       # present dans les reflets, absent du cadre
    ob.visible_shadow = False
    return ob


def studio():
    environnement()
    softbox("Cle",   (-2.9, -3.2, 2.3), (math.radians(56), 0, math.radians(-42)),
            (3.4, 1.5), 15.0, (1.0, 0.94, 0.86))
    softbox("Rim",   (2.5, 2.4, 1.9),  (math.radians(122), 0, math.radians(146)),
            (2.6, 1.1), 11.0, ORANGE)
    softbox("Bande", (2.6, -2.4, 0.1), (math.radians(90), 0, math.radians(62)),
            (0.5, 3.4), 7.0, (0.80, 0.86, 1.0))
    softbox("Haut",  (0.0, -0.6, 3.4), (0, 0, 0),
            (2.2, 2.2), 4.0, (1.0, 0.97, 0.92))
    softbox("Sol",   (0.0, -4.2, -0.9), (math.radians(56), 0, 0),
            (3.0, 1.0), 2.2, (1.0, 0.90, 0.78))


def marbre(z=-1.13):
    bpy.ops.mesh.primitive_plane_add(size=40.0, location=(0, 0, z))
    sol = bpy.context.object
    sol.name = "Marbre"
    m = bpy.data.materials.new("AG_MarbreNoir")
    m.use_nodes = True
    nt = m.node_tree
    bsdf = principled(m)
    entree(bsdf, ["Base Color"], (0.010, 0.010, 0.013, 1.0), 0)
    entree(bsdf, ["Metallic"], 0.0, 1)
    entree(bsdf, ["Specular IOR Level", "Specular"], 0.85)
    coord = nt.nodes.new("ShaderNodeTexCoord")
    veine = nt.nodes.new("ShaderNodeTexNoise")
    veine.inputs["Scale"].default_value = 2.6
    veine.inputs["Detail"].default_value = 8.0
    nt.links.new(coord.outputs["Object"], veine.inputs["Vector"])
    rr = nt.nodes.new("ShaderNodeMapRange")
    rr.inputs["To Min"].default_value = 0.09
    rr.inputs["To Max"].default_value = 0.19
    nt.links.new(veine.outputs["Fac"], rr.inputs["Value"])
    nt.links.new(rr.outputs["Result"], bsdf.inputs[2])
    sol.data.materials.append(m)
    return sol


def camera(loc=(0.0, -7.05, -0.04), lens=80, focus=7.0, ouverture=4.5):
    d = bpy.data.cameras.new("AG_Camera")
    d.lens = lens
    if ouverture:
        d.dof.use_dof = True
        d.dof.focus_distance = focus
        d.dof.aperture_fstop = ouverture
    o = bpy.data.objects.new("AG_Camera", d)
    o.location = loc
    o.rotation_euler = (math.radians(88.5), 0.0, 0.0)
    bpy.context.collection.objects.link(o)
    bpy.context.scene.camera = o
    return o


def reglages():
    sc = bpy.context.scene
    sc.render.engine = "CYCLES"
    sc.cycles.samples = SAMPLES
    sc.cycles.use_denoising = True
    try:
        sc.cycles.max_bounces = 12
        sc.cycles.glossy_bounces = 8
    except Exception:
        pass
    sc.render.resolution_x = RES
    sc.render.resolution_y = RES
    sc.render.resolution_percentage = 100
    if hasattr(sc.render.image_settings, "media_type"):
        sc.render.image_settings.media_type = "IMAGE"
    sc.render.image_settings.file_format = "PNG"
    sc.render.image_settings.color_mode = "RGB"
    vues = [v.name for v in sc.view_settings.bl_rna.properties["view_transform"].enum_items]
    sc.view_settings.view_transform = ("AgX" if "AgX" in vues else
                                       "Filmic" if "Filmic" in vues else "Standard")
    sc.view_settings.look = "None"
    sc.view_settings.exposure = 0.35


def construire():
    purge()
    or_plaque = mat_or_realiste("AG_Or_Plaque", rugosite=0.24, martelage=1.0)
    or_jonc   = mat_or_realiste("AG_Or_Jonc", rugosite=0.15, martelage=0.35)
    or_rivet  = mat_or_realiste("AG_Or_Rivet", rugosite=0.12, martelage=0.20)
    or_anse   = mat_or_realiste("AG_Or_Anse", rugosite=0.13, martelage=0.25)
    or_corps  = mat_or_realiste("AG_Or_Cadenas", rugosite=0.17, martelage=0.30)
    chaud     = mat_emissif("AG_Serrure", ORANGE, 7.0)

    plaque(or_plaque)
    jonc(or_jonc)
    rivets(or_rivet)
    cadenas(or_corps, or_anse, chaud)
    studio()
    marbre()
    camera()
    reglages()
    os.makedirs(OUT_DIR, exist_ok=True)
    return {"objets": len(bpy.context.scene.objects), "sortie": OUT_DIR}


def rendre(nom="ag-bouclier-realiste.png", res=None, samples=None):
    sc = bpy.context.scene
    if res:
        sc.render.resolution_x = sc.render.resolution_y = res
    if samples:
        sc.cycles.samples = samples
    sc.render.filepath = os.path.join(OUT_DIR, nom)
    bpy.ops.render.render(write_still=True)
    return sc.render.filepath


if __name__ == "__main__":
    print("SCENE", construire(), flush=True)
    if bpy.app.background:
        import sys
        essai = "--essai" in sys.argv
        print("RENDU", rendre("ag-bouclier-essai.png", 640, 56) if essai
              else rendre(), flush=True)
