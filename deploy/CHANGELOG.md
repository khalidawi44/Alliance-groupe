# CHANGELOG — Alliance Groupe Theme

## Comment deployer

### Methode 1 : Script en 1 clic (recommandé)

**Sur Mac/Linux :**
```bash
cd deploy/
chmod +x deploy.sh
./deploy.sh
```

**Sur Windows :**
```
Double-cliquer sur deploy/deploy.bat
```

> ⚠️ **AVANT le premier deploiement** : ouvre le fichier `deploy.sh` ou `deploy.bat` et remplis tes identifiants FTP o2switch.

### Methode 2 : FileZilla / FTP manuel

1. Ouvre FileZilla (ou ton client FTP)
2. Connecte-toi a ton serveur o2switch
3. Navigue vers `/www/wp-content/themes/`
4. Glisse le dossier `alliance-groupe-theme/` depuis ton PC
5. Confirme "Remplacer" sur tous les fichiers

### Methode 3 : cPanel File Manager (o2switch)

1. Connecte-toi a ton cPanel o2switch
2. Ouvre "Gestionnaire de fichiers"
3. Va dans `/home/ton-user/www/wp-content/themes/`
4. Supprime le dossier `alliance-groupe-theme/` existant
5. Clique "Charger" et upload le ZIP du dossier
6. Extrais le ZIP

---

## Apres le deploiement

1. **Va dans Apparence > Themes** et active "Alliance Groupe Theme"
2. **Va dans Outils > Import AG** et clique "Lancer l'import"
3. L'import cree automatiquement les pages, articles, menu et reglages
4. **Upload les images de l'equipe** dans `assets/images/team/` (voir liste ci-dessous)

---

## Images a uploader manuellement

### Equipe (dans `assets/images/team/`)

| Fichier | Personne |
|---------|----------|
| `1_bureau_naples.jpg` | Fabrice — Fondateur, Naples |
| `Carlito_1-1024x576.jpg` | Carlito — Directeur Technique, Naples |
| `kate.jpg` | Kate — Directrice Artistique, Nantes |
| `halim.jpg` | Halim — Responsable SEO, Marrakech |
| `amina.jpg` | Amina — Responsable IA, Marrakech |
| `laurent.jpg` | Laurent — Responsable Commercial, Nantes |
| `julie.jpg` | Julie — Cheffe de Projet, Nantes |

> Le site detecte automatiquement les extensions : `.jpg`, `.jpeg`, `.png`, `.webp`
> Si l'image n'est pas trouvee, une initiale doree s'affiche.

---

## Versions

### v1.3.0 — Menu + Equipe + Realisations (08/04/2026)

**NOUVEAUX FICHIERS :**
- `assets/images/team/` — Dossier pour les photos de l'equipe
- `assets/images/realisations/` — Dossier pour les captures projets

**FICHIERS MODIFIES :**
- `header.php` — Mega menu desktop + menu fullscreen mobile
- `assets/css/main.css` — +200 lignes (mega menu, mobile menu, team cards)
- `assets/js/main.js` — Menu mobile fullscreen + accordeons
- `functions.php` — Overrides HEREDOC pour mega menu + mobile menu
- `template-parts/about.php` — Section equipe (7 membres avec photos)
- `template-parts/realisations.php` — 7 projets (2 reels + 5 inventes)

**DETAILS :**
- Menu desktop : sous-menus au survol avec Services (6), Realisations (7), Blog (dynamique)
- Menu mobile : fullscreen, accordeons, CTA telephone + email en bas
- Equipe : Fabrice, Carlito, Kate, Halim, Amina, Laurent, Julie
- Projets : Anna Photo, L.A Environnement, Maison Riviera, Cabinet Martin, Fitness Lab, Saveurs d'Orient, TechVision Pro

---

### v1.2.0 — Import securise (08/04/2026)

**FICHIERS MODIFIES :**
- `ag-import.php` — Verrouillage auto, rate limiting, logs d'audit
- `functions.php` — Include automatique de ag-import.php

---

### v1.1.0 — Articles SEO + template article (08/04/2026)

**NOUVEAUX FICHIERS :**
- `ag-import.php` — Script d'import (12 pages + 10 articles + menu + reglages)

**FICHIERS MODIFIES :**
- `single.php` — Template article avec CTA, auteur, articles lies
- `functions.php` — Reading time, meta SEO, theme support
- `assets/css/main.css` — Styles article CTA, auteur, tags, breadcrumb

**10 ARTICLES SEO :**
1. PME sans site web perdent des clients
2. Cout absence presence digitale
3. 5 signes site fait fuir clients
4. IA revolution generation leads
5. SEO local concurrents volent clients
6. Commercial vs site web optimise
7. Etude de cas paysagiste x4 devis
8. 7 erreurs fatales visibilite
9. Automatisation gagner 15h/semaine IA
10. Site ne genere aucun lead

---

### v1.0.0 — Theme initial (08/04/2026)

**26 FICHIERS CREES :**
```
alliance-groupe-theme/
├── style.css
├── functions.php
├── header.php
├── footer.php
├── index.php
├── single.php
├── assets/css/main.css
├── assets/js/main.js
├── template-parts/marquee.php
├── template-parts/services.php
├── template-parts/process.php
├── template-parts/realisations.php
├── template-parts/about.php
├── template-parts/faq.php
├── template-parts/cta.php
├── templates/page-accueil.php
├── templates/page-services.php
├── templates/page-realisations.php
├── templates/page-apropos.php
├── templates/page-contact.php
├── templates/page-service-web.php
├── templates/page-service-ia.php
├── templates/page-service-seo.php
├── templates/page-service-ads.php
├── templates/page-service-brand.php
└── templates/page-service-conseil.php
```
