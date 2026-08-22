# -*- coding: utf-8 -*-
"""
Alliance Groupe — « Allégorie de Naples » : le tableau qui prend vie.

Transforme allegorie-naples.jpg en scène 3D animée, rendue en boucle parfaite,
pour remplacer la photo figée de la section .tab du thème.

Ce que Blender apporte que le CSS ne peut pas :
  · un vrai relief — le tableau est déplacé en 3D, la caméra tourne autour,
    donc les plans se décalent les uns par rapport aux autres (parallaxe réelle)
  · des rais de lumière volumétriques qui traversent la scène
  · de la poussière d'or qui dérive DEVANT le tableau

    Blender → onglet Scripting → console >>> :
    exec(open(r"C:\\Users\\Utilisateur\\Documents\\GitHub\\Alliance-groupe\\blender\\ag_allegorie_anim.py").read()); print(construire())
    puis :  rendre()

Sortie : rendus/allegorie-naples-anim.mp4  +  allegorie-naples-poster.jpg
"""

import bpy, os, math

# ─── Réglages ────────────────────────────────────────────────────────────────
PROFONDEUR = 0.42     # force du relief. 0 = plat. Au-delà de 0.7 ça se déforme.
SECONDES   = 8        # durée de la boucle
FPS        = 30
LARGEUR    = 1920
HAUTEUR    = 1080
POUSSIERE  = True     # la poussière d'or devant le tableau
NUIT       = (0.020, 0.020, 0.039)
OR         = (0.831, 0.706, 0.361)

_ICI    = os.path.dirname(bpy.data.filepath or os.getcwd())
_REPO   = os.path.dirname(_ICI) if os.path.basename(_ICI).lower() == "blender" else _ICI
IMAGE   = os.path.join(_REPO, "alliance-groupe-theme", "assets", "images",
                       "cinematique", "allegorie-naples.jpg")
OUT_DIR = os.path.join(_REPO, "blender", "rendus")


def srgb_to_linear(c):
    return tuple((v / 12.92 if v <= 0.04045 else ((v + 0.055) / 1.055) ** 2.4) for v in c)


def trouver_image():
    """Le chemin peut varier selon d'où le script est lancé — on cherche."""
    if os.path.isfile(IMAGE):
        return IMAGE
    for base in (_ICI, _REPO, os.getcwd()):
        for racine, _, fichiers in os.walk(base):
            for f in fichiers:
                if f.lower() == "allegorie-naples.jpg":
                    return os.path.join(racine, f)
    raise FileNotFoundError(
        "allegorie-naples.jpg introuvable. Enregistre d'abord le .blend dans le "
        "dossier blender/ du dépôt, ou corrige la variable IMAGE en haut du script.")


def purge():
    bpy.ops.object.select_all(action="SELECT")
    bpy.ops.object.delete(use_global=False)
    for coll in (bpy.data.meshes, bpy.data.materials, bpy.data.textures,
                 bpy.data.images, bpy.data.lights, bpy.data.cameras, bpy.data.worlds):
        for item in list(coll):
            if item.users == 0:
                try: coll.remove(item)
                except Exception: pass


def noeud(nt, type_):
    return next((n for n in nt.nodes if n.type == type_), None)


def set_in(node, noms, valeur, index=None):
    """Les noms de sockets sont traduits selon la langue de l'interface."""
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


# ─── Le tableau, en relief ───────────────────────────────────────────────────
def tableau(chemin):
    img = bpy.data.images.load(chemin, check_existing=True)
    w, h = img.size
    ratio = (h / w) if w else 0.5625

    bpy.ops.mesh.primitive_grid_add(x_subdivisions=220, y_subdivisions=int(220 * ratio),
                                    size=2.0, location=(0, 0, 0))
    ob = bpy.context.object
    ob.name = "Tableau"
    ob.scale = (1.0, ratio, 1.0)
    bpy.ops.object.transform_apply(scale=True)
    ob.rotation_euler = (math.radians(90), 0, 0)   # face à la caméra

    # matériau : le tableau s'éclaire lui-même, la lumière ne fait qu'ajouter
    m = bpy.data.materials.new("AG_Allegorie")
    m.use_nodes = True
    nt = m.node_tree
    nt.nodes.clear()
    sortie = nt.nodes.new("ShaderNodeOutputMaterial")
    melange = nt.nodes.new("ShaderNodeMixShader")
    emi = nt.nodes.new("ShaderNodeEmission")
    diff = nt.nodes.new("ShaderNodeBsdfDiffuse")
    tex = nt.nodes.new("ShaderNodeTexImage")
    tex.image = img
    tex.interpolation = "Cubic"

    nt.links.new(tex.outputs[0], emi.inputs[0])
    nt.links.new(tex.outputs[0], diff.inputs[0])
    set_in(emi, ["Strength", "Intensité"], 1.15, 1)
    set_in(melange, ["Fac", "Facteur"], 0.30, 0)
    nt.links.new(diff.outputs[0], melange.inputs[1])
    nt.links.new(emi.outputs[0], melange.inputs[2])
    nt.links.new(melange.outputs[0], sortie.inputs[0])
    ob.data.materials.append(m)

    # le relief : la luminance du tableau devient de la profondeur
    if PROFONDEUR > 0:
        t = bpy.data.textures.new("AG_Relief", type="IMAGE")
        t.image = img
        try: t.use_interpolation = True
        except Exception: pass
        d = ob.modifiers.new("Relief", "DISPLACE")
        d.texture = t
        d.texture_coords = "UV"
        d.mid_level = 0.5
        d.strength = PROFONDEUR
        d.direction = "Z"
        liss = ob.modifiers.new("Adoucir", "SMOOTH")
        liss.factor = 0.9
        liss.iterations = 12          # casse les pics de la luminance

    for p in ob.data.polygons:
        p.use_smooth = True
    return ob, ratio


# ─── Poussière d'or ──────────────────────────────────────────────────────────
def poussiere(pivot):
    m = bpy.data.materials.new("AG_Poussiere")
    m.use_nodes = True
    nt = m.node_tree
    nt.nodes.clear()
    sortie = nt.nodes.new("ShaderNodeOutputMaterial")
    emi = nt.nodes.new("ShaderNodeEmission")
    set_in(emi, ["Color", "Couleur"], (*srgb_to_linear(OR), 1.0), 0)
    set_in(emi, ["Strength", "Intensité"], 6.0, 1)
    nt.links.new(emi.outputs[0], sortie.inputs[0])

    bpy.ops.mesh.primitive_ico_sphere_add(radius=0.0038, subdivisions=1, location=(0, 0, 0))
    grain = bpy.context.object
    grain.name = "Grain"
    grain.data.materials.append(m)

    bpy.ops.mesh.primitive_cube_add(size=1.0, location=(0, -0.62, 0))
    nuage = bpy.context.object
    nuage.name = "Poussiere"
    nuage.scale = (1.55, 0.34, 0.95)
    bpy.ops.object.transform_apply(scale=True)
    nuage.display_type = "WIRE"
    nuage.hide_render = True

    ps = nuage.modifiers.new("Grains", "PARTICLE_SYSTEM").particle_system
    st = ps.settings
    st.type = "EMITTER"
    st.count = 900
    st.frame_start = -240          # déjà en vol quand la boucle démarre
    st.frame_end = 1
    st.lifetime = 4000
    st.emit_from = "VOLUME"
    st.physics_type = "NEWTON"
    st.normal_factor = 0.0
    st.factor_random = 0.012
    st.effector_weights.gravity = 0.0
    st.render_type = "OBJECT"
    st.instance_object = grain
    st.particle_size = 1.0
    st.size_random = 0.75
    grain.parent = nuage
    nuage.parent = pivot
    return nuage


# ─── Lumière, monde, caméra ──────────────────────────────────────────────────
def ambiance():
    monde = bpy.data.worlds.new("AG_Monde")
    monde.use_nodes = True
    fond = noeud(monde.node_tree, "BACKGROUND")
    if fond:
        fond.inputs[0].default_value = (*srgb_to_linear(NUIT), 1.0)
        fond.inputs[1].default_value = 1.0
    bpy.context.scene.world = monde

    d = bpy.data.lights.new("Rayon", type="SPOT")
    d.energy = 900
    d.spot_size = math.radians(72)
    d.spot_blend = 0.85
    d.shadow_soft_size = 0.35
    d.color = (1.0, 0.87, 0.66)
    o = bpy.data.objects.new("Rayon", d)
    o.location = (-2.4, -2.9, 2.1)
    o.rotation_euler = (math.radians(52), 0, math.radians(-38))
    bpy.context.collection.objects.link(o)

    d2 = bpy.data.lights.new("Contre", type="AREA")
    d2.size = 3.0
    d2.energy = 190
    d2.color = OR
    o2 = bpy.data.objects.new("Contre", d2)
    o2.location = (2.3, 1.8, 0.7)
    o2.rotation_euler = (math.radians(104), 0, math.radians(128))
    bpy.context.collection.objects.link(o2)
    return o


def courbes(action):
    """Blender 5 a supprime action.fcurves : les courbes vivent dans les
    channelbags des strips. On gere les deux formes."""
    if hasattr(action, "fcurves"):
        try:
            return list(action.fcurves)
        except Exception:
            pass
    out = []
    for couche in getattr(action, "layers", []):
        for strip in getattr(couche, "strips", []):
            for sac in getattr(strip, "channelbags", []):
                out.extend(sac.fcurves)
    return out


def camera_animee(ratio):
    pivot = bpy.data.objects.new("Pivot", None)
    bpy.context.collection.objects.link(pivot)

    d = bpy.data.cameras.new("AG_Camera")
    d.lens = 52
    cam = bpy.data.objects.new("AG_Camera", d)
    cam.location = (0, -2.62, 0)
    cam.rotation_euler = (math.radians(90), 0, 0)
    bpy.context.collection.objects.link(cam)
    cam.parent = pivot
    bpy.context.scene.camera = cam

    fin = SECONDES * FPS
    sc = bpy.context.scene
    sc.frame_start = 1
    sc.frame_end = fin

    # boucle parfaite : l'image 1 et l'image finale sont identiques
    poses = [
        (1,        (0.0, 0.0, 0.0),  (0.0,  0.0,  0.0),   2.62),
        (fin // 2, (0.0, 0.0, 0.0),  (math.radians(2.2), 0.0, math.radians(-2.6)), 2.30),
        (fin,      (0.0, 0.0, 0.0),  (0.0,  0.0,  0.0),   2.62),
    ]
    for f, loc, rot, dist in poses:
        pivot.location = loc
        pivot.rotation_euler = rot
        cam.location = (0, -dist, 0)
        pivot.keyframe_insert("location", frame=f)
        pivot.keyframe_insert("rotation_euler", frame=f)
        cam.keyframe_insert("location", frame=f)

    for ob in (pivot, cam):
        if ob.animation_data and ob.animation_data.action:
            for fc in courbes(ob.animation_data.action):
                for kp in fc.keyframe_points:
                    kp.interpolation = "BEZIER"
                    kp.easing = "EASE_IN_OUT"
    return pivot, cam


def format_sortie(sc, fmt):
    """Blender 5 : le format video passe d'abord par media_type='VIDEO'."""
    im = sc.render.image_settings
    if hasattr(im, "media_type"):
        im.media_type = "VIDEO" if fmt == "FFMPEG" else "IMAGE"
    im.file_format = fmt


def reglages():
    sc = bpy.context.scene
    sc.render.engine = "BLENDER_EEVEE_NEXT" if "BLENDER_EEVEE_NEXT" in [
        e.identifier for e in sc.render.bl_rna.properties["engine"].enum_items
    ] else "BLENDER_EEVEE"
    sc.render.fps = FPS
    sc.render.resolution_x = LARGEUR
    sc.render.resolution_y = HAUTEUR
    sc.render.resolution_percentage = 100
    try:
        sc.eevee.use_bloom = True
        sc.eevee.bloom_intensity = 0.045
    except Exception:
        pass
    try:
        sc.eevee.use_volumetric_lights = True
        sc.world.node_tree  # brume légère pour les rais
    except Exception:
        pass
    format_sortie(sc, "FFMPEG")
    sc.render.ffmpeg.format = "MPEG4"
    sc.render.ffmpeg.codec = "H264"
    sc.render.ffmpeg.constant_rate_factor = "HIGH"
    sc.render.ffmpeg.ffmpeg_preset = "GOOD"
    sc.render.film_transparent = False


def construire():
    purge()
    chemin = trouver_image()
    _, ratio = tableau(chemin)
    ambiance()
    pivot, _ = camera_animee(ratio)
    if POUSSIERE:
        poussiere(pivot)
    reglages()
    os.makedirs(OUT_DIR, exist_ok=True)
    return {
        "image": chemin,
        "images_totales": bpy.context.scene.frame_end,
        "sortie": OUT_DIR,
        "suite": "tape maintenant :  rendre()",
    }


def rendre():
    sc = bpy.context.scene
    sc.render.filepath = os.path.join(OUT_DIR, "allegorie-naples-anim")
    bpy.ops.render.render(animation=True)

    sc.frame_set(1)
    format_sortie(sc, "JPEG")
    sc.render.image_settings.quality = 90
    sc.render.filepath = os.path.join(OUT_DIR, "allegorie-naples-poster.jpg")
    bpy.ops.render.render(write_still=True)
    format_sortie(sc, "FFMPEG")
    return OUT_DIR


if __name__ == "__main__":
    print(construire())
    if bpy.app.background:
        print("Rendu :", rendre())
