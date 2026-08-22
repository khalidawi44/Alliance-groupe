# -*- coding: utf-8 -*-
"""
Alliance Groupe — emblème sécurité 3D (bouclier + cadenas), or sur nuit.

Reproductible : relancer ce script reconstruit la scène à l'identique.
    Blender (onglet Scripting → Run)  ou  blender -b -P ag_embleme_securite.py

Sorties (dans OUT_DIR) :
    ag-embleme-securite.png    rendu carré 1024×1024, Cycles, fond nuit
    ag-embleme-securite-alpha.png  même rendu, fond transparent
    ag-embleme-securite.glb    modèle web léger (Draco) pour le Voyage

Charte (BLENDER-SETUP.md §5) :
    or #D4B45C · orange #F37A1F · fond nuit #0a0a0f
"""

import bpy, bmesh, math, os
from mathutils import Vector

# ─── Réglages ────────────────────────────────────────────────────────────────
OUT_DIR   = os.path.join(os.path.dirname(bpy.data.filepath or os.getcwd()), "rendus")
RES       = 1024          # carré, comme les fiches catalogue
SAMPLES   = 256           # 128 pour une préview rapide, 512 pour la version finale
OR        = (0.831, 0.706, 0.361)   # #D4B45C
ORANGE    = (0.953, 0.478, 0.122)   # #F37A1F
NUIT      = (0.039, 0.039, 0.059)   # #0a0a0f


def srgb_to_linear(c):
    return tuple((v / 12.92 if v <= 0.04045 else ((v + 0.055) / 1.055) ** 2.4) for v in c)


def purge():
    """Repart d'une scène vide sans toucher aux préférences."""
    bpy.ops.object.select_all(action="SELECT")
    bpy.ops.object.delete(use_global=False)
    for coll in (bpy.data.meshes, bpy.data.materials, bpy.data.curves,
                 bpy.data.lights, bpy.data.cameras):
        for item in list(coll):
            if item.users == 0:
                coll.remove(item)


def set_input(node, names, value, index=None):
    """Les noms de sockets changent entre versions ET sont traduits selon la
    langue de l'interface. On essaie les noms, puis l'index en dernier recours."""
    for n in names:
        if n in node.inputs:
            node.inputs[n].default_value = value
            return True
    if index is not None and index < len(node.inputs):
        try:
            node.inputs[index].default_value = value
            return True
        except Exception:
            pass
    return False


def principled(mat):
    """Récupère le nœud Principled par son TYPE, jamais par son nom :
    en interface traduite il s'appelle « BSDF Principled », « Principled BSDF »,
    ou autre chose selon la langue."""
    nt = mat.node_tree
    node = next((n for n in nt.nodes if n.type == "BSDF_PRINCIPLED"), None)
    if node is None:
        node = nt.nodes.new("ShaderNodeBsdfPrincipled")
        sortie = next((n for n in nt.nodes if n.type == "OUTPUT_MATERIAL"), None)
        if sortie is None:
            sortie = nt.nodes.new("ShaderNodeOutputMaterial")
        nt.links.new(node.outputs[0], sortie.inputs[0])
    return node


# ─── Matériaux ───────────────────────────────────────────────────────────────
def mat_or(nom="AG_Or", rugosite=0.22, teinte=None):
    m = bpy.data.materials.new(nom)
    m.use_nodes = True
    bsdf = principled(m)
    base = srgb_to_linear(teinte or OR)
    set_input(bsdf, ["Base Color", "Couleur de base"], (*base, 1.0), 0)
    set_input(bsdf, ["Metallic", "Métallique"], 1.0, 1)
    set_input(bsdf, ["Roughness", "Rugosité"], rugosite, 2)
    set_input(bsdf, ["Specular IOR Level", "Specular", "Spéculaire"], 0.5)
    set_input(bsdf, ["Anisotropic", "Anisotropie"], 0.35)
    return m


def mat_emissif(nom, couleur, force=9.0):
    m = bpy.data.materials.new(nom)
    m.use_nodes = True
    nt = m.node_tree
    nt.nodes.clear()
    out = nt.nodes.new("ShaderNodeOutputMaterial")
    emi = nt.nodes.new("ShaderNodeEmission")
    set_input(emi, ["Color", "Couleur"], (*srgb_to_linear(couleur), 1.0), 0)
    set_input(emi, ["Strength", "Intensité"], force, 1)
    nt.links.new(emi.outputs[0], out.inputs[0])
    return m


# ─── Géométrie ───────────────────────────────────────────────────────────────
def bouclier():
    """Silhouette d'écusson : épaules carrées en haut, pointe en bas."""
    prof = [(0.00, 1.00), (0.62, 0.86), (0.86, 0.55), (0.90, 0.10),
            (0.72, -0.46), (0.38, -0.86), (0.00, -1.06)]
    pts = [Vector((-x, 0.0, y)) for x, y in reversed(prof[1:])] + \
          [Vector((x, 0.0, y)) for x, y in prof]

    me = bpy.data.meshes.new("Bouclier")
    bm = bmesh.new()
    verts = [bm.verts.new(p) for p in pts]
    bm.faces.new(verts)
    bmesh.ops.recalc_face_normals(bm, faces=bm.faces[:])
    bm.to_mesh(me)
    bm.free()

    ob = bpy.data.objects.new("Bouclier", me)
    bpy.context.collection.objects.link(ob)
    ob.scale = (1.15, 1.0, 1.15)

    sol = ob.modifiers.new("Epaisseur", "SOLIDIFY")
    sol.thickness = 0.13
    sol.offset = 0.0
    bev = ob.modifiers.new("Chanfrein", "BEVEL")
    bev.width = 0.028
    bev.segments = 5
    bev.limit_method = "ANGLE"
    sub = ob.modifiers.new("Lissage", "SUBSURF")
    sub.levels = 2
    sub.render_levels = 3
    ob.data.materials.append(mat_or("AG_Or_Bouclier", 0.24))
    for p in ob.data.polygons:
        p.use_smooth = True
    return ob


def cadenas():
    """Corps arrondi + anse : le cadenas posé au centre du bouclier."""
    bpy.ops.mesh.primitive_cube_add(size=1.0, location=(0.0, -0.26, -0.06))
    corps = bpy.context.object
    corps.name = "Cadenas_Corps"
    corps.scale = (0.44, 0.20, 0.36)
    bpy.ops.object.transform_apply(scale=True)
    b = corps.modifiers.new("Chanfrein", "BEVEL")
    b.width = 0.085
    b.segments = 8
    b.limit_method = "ANGLE"
    s = corps.modifiers.new("Lissage", "SUBSURF")
    s.levels = 2
    s.render_levels = 3
    corps.data.materials.append(mat_or("AG_Or_Cadenas", 0.18))

    bpy.ops.mesh.primitive_torus_add(
        major_radius=0.245, minor_radius=0.052,
        major_segments=64, minor_segments=24,
        location=(0.0, -0.26, 0.30), rotation=(math.pi / 2, 0.0, 0.0))
    anse = bpy.context.object
    anse.name = "Cadenas_Anse"
    # on coupe la moitié basse du tore : il ne reste que l'arche
    me = anse.data
    bm = bmesh.new(); bm.from_mesh(me)
    bmesh.ops.bisect_plane(bm, geom=bm.verts[:] + bm.edges[:] + bm.faces[:],
                           plane_co=(0, 0, 0), plane_no=(0, 0, -1), clear_inner=True)
    bm.to_mesh(me); bm.free()
    anse.data.materials.append(mat_or("AG_Or_Anse", 0.16))

    for ob in (corps, anse):
        for p in ob.data.polygons:
            p.use_smooth = True

    # le trou de serrure, en orange lumineux
    bpy.ops.mesh.primitive_cylinder_add(radius=0.055, depth=0.5,
                                        location=(0.0, -0.40, -0.02),
                                        rotation=(math.pi / 2, 0, 0), vertices=32)
    trou = bpy.context.object
    trou.name = "Cadenas_Serrure"
    trou.data.materials.append(mat_emissif("AG_Orange_Serrure", ORANGE, 12.0))
    return corps, anse, trou


# ─── Éclairage, caméra, rendu ────────────────────────────────────────────────
def studio():
    monde = bpy.data.worlds.new("AG_Nuit")
    monde.use_nodes = True
    fond = next((n for n in monde.node_tree.nodes if n.type == "BACKGROUND"), None)
    if fond is not None:
        fond.inputs[0].default_value = (*srgb_to_linear(NUIT), 1.0)
        fond.inputs[1].default_value = 1.0
    bpy.context.scene.world = monde

    def aire(nom, loc, rot, taille, energie, couleur=(1, 1, 1)):
        d = bpy.data.lights.new(nom, type="AREA")
        d.size = taille
        d.energy = energie
        d.color = couleur
        o = bpy.data.objects.new(nom, d)
        o.location = loc
        o.rotation_euler = rot
        bpy.context.collection.objects.link(o)
        return o

    # clé chaude à gauche, contre-jour orange derrière, remplissage froid à droite
    aire("Cle",    (-2.6, -3.0, 2.2), (math.radians(62), 0, math.radians(-40)), 4.0, 900, (1.0, 0.93, 0.82))
    aire("Rim",    (1.9, 2.6, 1.5),   (math.radians(115), 0, math.radians(150)), 3.0, 700, ORANGE)
    aire("Fill",   (3.0, -2.2, 0.2),  (math.radians(85), 0, math.radians(58)),  5.0, 160, (0.75, 0.82, 1.0))


def camera():
    d = bpy.data.cameras.new("AG_Camera")
    d.lens = 85                       # focale longue : peu de déformation, rendu produit
    o = bpy.data.objects.new("AG_Camera", d)
    o.location = (0.0, -6.2, 0.25)
    o.rotation_euler = (math.radians(88.5), 0.0, 0.0)
    bpy.context.collection.objects.link(o)
    bpy.context.scene.camera = o
    return o


def reglages_rendu():
    sc = bpy.context.scene
    sc.render.engine = "CYCLES"
    try:
        sc.cycles.device = "GPU"
    except Exception:
        pass
    sc.cycles.samples = SAMPLES
    sc.cycles.use_denoising = True
    sc.render.resolution_x = RES
    sc.render.resolution_y = RES
    sc.render.resolution_percentage = 100
    sc.render.image_settings.file_format = "PNG"
    sc.render.image_settings.color_mode = "RGBA"
    sc.view_settings.view_transform = "Filmic" if "Filmic" in [
        v.name for v in sc.view_settings.bl_rna.properties["view_transform"].enum_items] else "Standard"
    sc.view_settings.look = "None"


def construire():
    purge()
    bouclier()
    cadenas()
    studio()
    camera()
    reglages_rendu()
    os.makedirs(OUT_DIR, exist_ok=True)
    return {"objets": len(bpy.context.scene.objects), "sortie": OUT_DIR}


def rendre():
    sc = bpy.context.scene
    sc.render.film_transparent = False
    sc.render.filepath = os.path.join(OUT_DIR, "ag-embleme-securite.png")
    bpy.ops.render.render(write_still=True)

    sc.render.film_transparent = True
    sc.render.filepath = os.path.join(OUT_DIR, "ag-embleme-securite-alpha.png")
    bpy.ops.render.render(write_still=True)
    sc.render.film_transparent = False
    return sc.render.filepath


def exporter_glb():
    """Modèle web léger pour assets/images/img_3d/ (page Voyage)."""
    chemin = os.path.join(OUT_DIR, "ag-embleme-securite.glb")
    bpy.ops.object.select_all(action="DESELECT")
    for ob in bpy.context.scene.objects:
        if ob.type == "MESH":
            ob.select_set(True)
    try:
        bpy.ops.export_scene.gltf(
            filepath=chemin, export_format="GLB", use_selection=True,
            export_apply=True, export_draco_mesh_compression_enable=True,
            export_draco_mesh_compression_level=6)
    except TypeError:
        # anciennes versions : pas d'option Draco
        bpy.ops.export_scene.gltf(filepath=chemin, export_format="GLB",
                                  use_selection=True, export_apply=True)
    return chemin


if __name__ == "__main__":
    infos = construire()
    print("Scène construite :", infos)
    if bpy.app.background:
        print("Rendu   :", rendre())
        print("Export  :", exporter_glb())
