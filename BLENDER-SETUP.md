# BLENDER — création 3D avec Claude (BlenderMCP, temps réel)

> But : piloter Blender **en direct** depuis Claude Code pour produire la 3D Alliance Groupe :
> 1) **logo tête de lion + AG** animé (intro or/orange), 2) **assets pour les shorts** (rendus image/clip réinjectés dans Remotion), 3) **modèles `.glb`** pour le **Voyage** (`templates/page-experience.php`), 4) **vidéo 3D complète** (caméra animée).

## ⚠️ Où ça tourne
- BlenderMCP = un **add-on Blender** + un **serveur MCP** qui écoute sur `localhost:9876` **sur TON PC**.
- Le conteneur Claude Code **web (cloud)** ne peut PAS l'atteindre. → Le temps réel se fait depuis **Claude Code installé sur ton PC**.
- Ce repo sert de mémoire : specs, scripts Python (`bpy`), et ce guide. On les réutilise à chaque session.

## 1. Installer l'add-on dans Blender (une fois)
1. Télécharger `addon.py` du projet **blender-mcp** (github.com/ahujasid/blender-mcp).
2. Blender → **Edit → Preferences → Add-ons → Install…** → choisir `addon.py`.
3. Cocher **« Interface: Blender MCP »** pour l'activer.
4. Dans la vue 3D, appuyer sur **N** → onglet **BlenderMCP** → **Start MCP Server** (port 9876).

## 2. Brancher Claude Code (sur le PC) au serveur
Pré-requis : **uv** installé (`pip install uv` ou voir astral.sh/uv).

Dans un terminal sur le PC :
```
claude mcp add blender -- uvx blender-mcp
```
(ou, app Desktop, ajouter dans la config MCP :
`"blender": { "command": "uvx", "args": ["blender-mcp"] }`)

Vérifier : ouvrir Claude Code sur le PC → l'outil `blender` doit apparaître. Garder Blender ouvert + serveur démarré.

## 3. Méthode hybride (recommandée)
- **Temps réel** (BlenderMCP) : exploration, placement, matériaux, cadrage caméra, itérations rapides.
- **Scripts `bpy` versionnés** (dossier `blender/` de ce repo) : ce qui doit être **reproductible** (générer le lion, exporter les `.glb`, lancer le rendu). On les lance dans Blender (onglet *Scripting* → *Run*) ou en CLI :
  ```
  blender -b scene.blend -P blender/export_glb.py
  blender -b scene.blend -P blender/render_intro.py
  ```

## 4. Cibles de prod
| Cible | Sortie | Destination |
|---|---|---|
| Logo lion 3D animé | `.mp4`/`.webm` + frames PNG alpha | intro shorts Remotion + hero site |
| Assets shorts | PNG/clips (fond transparent) | `public/` du repo `alliance-videos`, intégrés dans `scripts.ts` |
| Modèles Voyage | **`.glb`** (Draco, légers) | `alliance-groupe-theme/assets/images/img_3d/` |
| Vidéo 3D complète | `.mp4` 1080×1920 | publication directe |

## 5. Charte (cohérence marque)
- Couleurs : or `#D4B45C`, orange `#F37A1F`, fond nuit `#0a0a0f`.
- Lion = élégant, premium, pas agressif (confiance/sécurité). Lettres **AG** associées.
- `.glb` Voyage : **légers** (lazy-load par station, `pixelRatio` capé) — privilégier low-poly + normal maps.
