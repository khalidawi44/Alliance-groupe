# Pilote de rendu serveur : construit la scene puis rend en PNG, image par image.
import bpy, os, io, time, sys

SRC = "/root/agrepo/blender/ag_allegorie_anim.py"
NS = {"__name__": "ag_anim", "__file__": SRC}
exec(io.open(SRC, encoding="utf-8").read(), NS)

# reglages serveur
NS["SECONDES"] = 8
NS["FPS"] = 24
NS["LARGEUR"] = 1280
NS["HAUTEUR"] = 720
NS["OUT_DIR"] = "/root/agrepo/blender/rendus"

NS["construire"]()

sc = bpy.context.scene
sc.eevee.taa_render_samples = 8
NS["format_sortie"](sc, "PNG")
sc.render.image_settings.color_mode = "RGB"
sc.render.image_settings.compression = 15

dossier = "/root/agrepo/blender/frames"
os.makedirs(dossier, exist_ok=True)

debut, fin = sc.frame_start, sc.frame_end
print("TOTAL", fin, flush=True)
t0 = time.time()
for f in range(debut, fin + 1):
    cible = os.path.join(dossier, "f_%04d.png" % f)
    if os.path.exists(cible) and os.path.getsize(cible) > 1000:
        continue
    sc.frame_set(f)
    sc.render.filepath = cible
    bpy.ops.render.render(write_still=True)
    print("FRAME %d/%d  %.1fs ecoulees" % (f, fin, time.time() - t0), flush=True)
print("FINI", round(time.time() - t0, 1), flush=True)
