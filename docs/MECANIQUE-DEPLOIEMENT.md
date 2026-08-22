# Mécanique de déploiement — de la conversation aux sites en ligne

> **À qui s'adresse ce document :** à toute session Claude qui travaille sur ce dépôt
> (notamment l'autre session en cours). Il décrit **le chemin complet** que parcourt une
> modification : de ce que Fabrice dit dans une conversation jusqu'au site que voit un visiteur.
> Tout ce qui suit a été vérifié dans le code du dépôt, pas supposé.

---

## 1. Le point de départ : la conversation est volatile, le dépôt est la seule mémoire

Sur Claude Code web, **chaque conversation démarre dans un conteneur neuf**. Le conteneur est
détruit à la fin. Conséquence directe :

- Un fichier écrit mais **non commité** → perdu.
- Un commit fait mais **non poussé** → perdu.
- Une décision prise en conversation et écrite nulle part → perdue pour la session suivante.

C'est pourquoi le dépôt sert de mémoire partagée. Deux hooks assurent la continuité :

| Hook | Fichier | Rôle |
|---|---|---|
| `SessionStart` | `.claude/hooks/session-start.sh` | À chaque ouverture de session, injecte l'état réel : branche courante, 8 derniers commits, travail non commité, version active, templates en focus, en-tête de `HANDOFF.md`. **C'est ce qui relie les conversations entre elles.** |
| `pre-commit` | installé par `scripts/install-git-hooks.sh` | Avant chaque commit : tamponne la date dans `HANDOFF.md` (`scripts/stamp-handoff.sh`), lance `scripts/check-all-locks.sh` (intégrité des templates verrouillés), puis `scripts/check-staged-releases.sh` (alerte si un template a été édité **sans** rebuild de son zip). |

**Règle de fin de session :** mettre à jour l'en-tête + §9 de `HANDOFF.md`, commiter, pousser.
Sinon la session suivante repart à l'aveugle.

---

## 2. La chaîne complète, en une image

```
  Fabrice parle en conversation
            │
            ▼
  La session écrit les fichiers dans SON conteneur (éphémère)
            │
            ▼  git commit   ← le hook pre-commit tamponne HANDOFF.md + vérifie les locks
  Commit local sur la branche de travail (claude/…)
            │
            ▼  git push -u origin <branche>
  Branche de travail sur GitHub          ← ne déclenche RIEN côté sites
            │
            ▼  git push origin HEAD:main  (fast-forward)
  ══════════ main sur GitHub ══════════   ← LA SEULE RÉFÉRENCE QUE LES SITES REGARDENT
            │
      ┌─────┴───────────────────────────────────┐
      ▼                                         ▼
  Site Alliance Groupe                     Sites vendus (Gwen, avocat, barber…)
  (le thème EST le dépôt)                  (chacun a son propre updater)
      │                                         │
  cron 5 min : compare le SHA de main       poll du .json sur raw.githubusercontent
  ≠ ? → télécharge le tarball,              version différente ? → télécharge le .zip
  extrait alliance-groupe-theme/,           et s'installe lui-même
  écrit dans le thème actif
      │                                         │
      ▼                                         ▼
  alliancegroupe-inc.com à jour            gwen-services.alliancegroupe-inc.com à jour
```

**Le point à retenir avant tout le reste : rien n'arrive sur un site tant que ce n'est pas sur `main`.**
Un commit qui dort sur la branche de travail est invisible pour les sites, même poussé sur GitHub.

---

## 3. Discipline de branche

- On développe sur la branche de travail (aujourd'hui `claude/network-access-params-mwh0me` —
  toujours vérifier avec `git branch --show-current`, elle change).
- Puis **merge fast-forward dans `main`** :

```bash
git push -u origin <branche>          # sauvegarde la branche
git fetch origin main
git push origin HEAD:main             # fast-forward
```

### ⚠️ `main` avance tout seul — toujours fetch avant de pousser

Un robot GitHub Actions (voir §5) commite directement sur `main` : ce sont les commits
`gwen : images, logo et videos (depot automatique)`. Ils tombent sans prévenir. Si le
`push origin HEAD:main` est refusé en *non-fast-forward*, ce n'est **pas** un conflit avec
l'autre session, c'est simplement `main` qui a avancé. Le geste correct :

```bash
git fetch origin main
git rebase origin/main
git push -f origin <branche>          # la branche seulement — jamais main en force
git push origin HEAD:main
```

**Ne jamais force-pusher `main`.** On rebase la branche de travail, puis on fast-forward.

---

## 4. Comment le code arrive sur le site Alliance Groupe (automatique, ≤ 5 min)

Moteur : `alliance-groupe-theme/inc/ag-github-sync.php`, classe `AG_GitHub_Sync`.

1. Un cron WordPress (`ag_github_sync_cron`, intervalle `ag_every_five_minutes`) tourne
   **toutes les 5 minutes**, déclenché par n'importe quelle visite du site.
2. Il interroge `https://api.github.com/repos/khalidawi44/Alliance-groupe/commits/main`
   et compare le **SHA distant** au SHA local stocké en option.
3. Identiques → il ne se passe rien (log « déjà à jour »).
4. Différents → il télécharge le **tarball** de `main`, en extrait le sous-dossier
   `alliance-groupe-theme/`, et écrit dans le thème actif (`get_stylesheet_directory()`),
   après avoir fait une sauvegarde.

Garde-fous codés en dur — utiles à connaître avant d'ajouter un type de fichier :

- **Dépôt de confiance unique** : `TRUSTED_REPOS = array( 'khalidawi44/Alliance-groupe' )`.
  La sync écrit du PHP qui sera exécuté : élargir cette liste est une décision de code délibérée,
  jamais pilotée par une donnée ou un filtre.
- **Whitelist d'extensions** (`ALLOWED_EXT`) : php, css, js, json, md, html, mp4, webm, png, jpg,
  jpeg, svg, webp, gif, ico, woff, woff2, ttf, otf, txt, glb, gltf, bin, hdr, ktx2, mp3, wav, m4a, ogg.
  **Un fichier avec une extension hors liste n'arrivera jamais sur le site**, même poussé sur `main`.
- **Fichiers jamais écrasés** (`PROTECTED`) : `wp-config.php`, `.env`, `.htaccess`, `.user.ini`,
  `php.ini`, `.htpasswd`, `web.config`, `.git`.
- Le même cron importe aussi les **nouveaux articles/pages** depuis le manifest (idempotent :
  un slug déjà présent est ignoré).

Le bouton **Apparence / Outils → SYNC GitHub** ne fait rien de plus que forcer ce cycle
immédiatement. Sans clic, ça part tout seul en moins de 5 minutes. Après coup : purger le cache
du site + Ctrl/Cmd+Maj+R.

---

## 5. Le robot images de Gwen (GitHub Actions)

Fichier : `.github/workflows/gwen-images.yml`. Déclencheur : un push sur `main` touchant
`gwen-inbox/**` (Fabrice y dépose ses photos depuis son téléphone).

Le robot : optimise l'image pour le web (Pillow), la renomme proprement, la déplace dans
`alliance-groupe-theme/assets/downloads/ag-gwen-services/assets/`, **release le thème**
(bump de version + rebuild du zip + json) et **pousse sur `main`**.

C'est ce robot qui fait avancer `main` sans intervention humaine (cf. §3). C'est aussi
la voie normale par laquelle une photo de Gwen atteint son site — il n'y a rien à faire à la main.

---

## 6. Comment une MAJ arrive sur un site VENDU (Gwen, avocat, barber…) — mécanisme différent

Chaque template vendu embarque son propre updater, ex.
`assets/downloads/ag-gwen-services/inc/theme-updater.php` :

```php
const JSON_URL = 'https://raw.githubusercontent.com/khalidawi44/Alliance-groupe/main/
                  alliance-groupe-theme/assets/downloads/ag-gwen-services.json';
```

Le thème installé chez le client :
1. lit ce `.json` sur **`main`** (cache 1 h),
2. compare la version annoncée à la sienne,
3. si elle est plus récente, **télécharge le `.zip` et s'installe lui-même** (écriture directe,
   sans WP-Cron ni prompt FTP), throttle 2 h,
4. bouton manuel **« 🔄 Sync Gwen »** dans la barre d'admin, ou `?ad…sync_now=1`, pour forcer.

### ⚠️ La conséquence la plus importante de tout ce document

**Éditer les fichiers d'un template ne suffit pas.** L'updater ne regarde ni les fichiers
sources ni les commits : il regarde **la version dans le `.json`** et télécharge **le `.zip`**.
Un template modifié sans rebuild → l'acheteur ne reçoit **jamais** la MAJ, et le site reste
sur l'ancienne version en silence.

La publication se fait donc **obligatoirement** par :

```bash
bash scripts/release.sh <slug> <version>
```

qui, en une commande : bump la version (header du style.css + constante) → met à jour le `.json`
→ reconstruit le `.zip` → commite → pousse la branche → merge dans `main` → pousse `main`.
Il refuse de tourner si l'arbre de travail n'est pas propre : commiter d'abord.

Vérification à tout moment : `bash scripts/check-releases.sh` (versions cohérentes, zips = source).
Le hook pre-commit alerte déjà si un template est édité sans rebuild de son zip.

Dernier point : `raw.githubusercontent.com` et jsDelivr ont leur propre cache CDN. Après une
release, purger si besoin (`https://purge.jsdelivr.net/gh/...`) — sinon la MAJ existe sur `main`
mais met du temps à être visible côté client.

---

## 7. Les deux erreurs qui coûtent le plus cher

1. **Croire qu'un commit poussé = un site à jour.** Faux tant qu'on n'est pas sur `main` ;
   et pour un template vendu, faux tant que le `.zip` + `.json` n'ont pas été rebuildés.
2. **Pousser `main` sans fetch.** Le robot images a peut-être commité entre-temps.
   Réflexe : `git fetch origin main && git rebase origin/main` avant tout push sur `main`.

---

## 8. Checklist avant de fermer une session

```bash
php -l <chaque .php modifié>          # obligatoire, indentation = tabulations
node --check <chaque .js modifié>
git status --short                    # doit être vide
git log --oneline -1 origin/main      # doit contenir ton travail
bash scripts/check-releases.sh        # si un template a été touché
```

Puis mettre à jour `HANDOFF.md` (en-tête + §9), commiter, pousser. La session suivante
reprendra exactement là grâce au hook `SessionStart`.
