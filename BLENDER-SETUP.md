# BLENDER — création 3D avec Claude (BlenderMCP, temps réel)

> But : piloter Blender **en direct** depuis Claude Code pour produire la 3D Alliance Groupe :
> 1) **logo tête de lion + AG** animé (intro or/orange), 2) **assets pour les shorts** (rendus image/clip réinjectés dans Remotion), 3) **modèles `.glb`** pour le **Voyage** (`templates/page-experience.php`), 4) **vidéo 3D complète** (caméra animée).

## ⚠️ Où ça tourne
- BlenderMCP = un **add-on Blender** + un **serveur MCP** qui écoute sur `localhost:9876` **sur TON PC**.
- Le conteneur Claude Code **web (cloud)** ne peut PAS l'atteindre. → Le temps réel se fait depuis **Claude Code installé sur ton PC**.
- Ce repo sert de mémoire : specs, scripts Python (`bpy`), et ce guide. On les réutilise à chaque session.

## 0. ⚠️ Version de Blender — vérifier AVANT tout
BlenderMCP exige **Blender 3.0 ou plus récent** (et Python 3.10+).
Vérifier : **Aide → À propos**, ou le numéro en bas à droite de la fenêtre.

- **Blender 2.9x → l'add-on ne s'installera pas.** Mettre Blender à jour.
- Sans mise à jour, tout n'est pas perdu : les scripts du dossier `blender/`
  tournent **sans add-on ni serveur**, depuis l'onglet **Scripting**
  (*Ouvrir* le `.py` → *Run*) ou en ligne de commande
  (`blender -b -P blender/ag_embleme_securite.py`). Seul le pilotage
  *en direct* par Claude demande l'add-on.

## 1. Installer l'add-on dans Blender (une fois)
1. Télécharger `addon.py` du projet **blender-mcp** (github.com/ahujasid/blender-mcp).
2. Blender → **Edit → Preferences → Add-ons → Install…** → choisir `addon.py`.
3. Cocher **« Interface: Blender MCP »** pour l'activer.
4. Placer la souris **dans la vue 3D**, appuyer sur **N** : une barre latérale
   s'ouvre à droite du viewport, avec des onglets verticaux (Élément, Outil,
   Vue…). Cliquer sur l'onglet **BlenderMCP**, puis sur **Connect to Claude**
   (port 9876).
   *Pas d'onglet BlenderMCP ? L'add-on n'est pas installé ou pas coché —
   revoir les étapes 1 à 3, et la version de Blender (§0).*

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

## 4 bis. Scripts déjà écrits
| Script | Ce qu'il fait |
|---|---|
| `blender/ag_embleme_securite.py` | bouclier + cadenas or — PNG carré 1024, PNG alpha, `.glb` Draco |

## 5. Charte (cohérence marque)
- Couleurs : or `#D4B45C`, orange `#F37A1F`, fond nuit `#0a0a0f`.
- Lion = élégant, premium, pas agressif (confiance/sécurité). Lettres **AG** associées.
- `.glb` Voyage : **légers** (lazy-load par station, `pixelRatio` capé) — privilégier low-poly + normal maps.
