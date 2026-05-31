# HANDOFF — Alliance Groupe (site `alliancegroupe-inc.com`)

> Document de reprise pour toute nouvelle session Claude (PC ou mobile via GitHub MCP).
> Dernière mise à jour : 2026-05-31 — branche de travail : `claude/pentest-local-security-offers-KNehV` (tampon auto à chaque commit).
> **🛰️ EN COURS (31/05, session pentest) — PONT AUDIT KALI ↔ wp-admin** : nouveau module commité `inc/ag-pentest-bridge.php` (chargé dans `functions.php`) + dossier suivi `pentest-bridge/` (runner `ag-runner.sh` + README, **sans secret**). Architecture file d'attente **sortie-seule** (Kali n'a aucun port ouvert) : bouton wp-admin (🔍 Espace Audit → 🛰️ Audits Kali) crée un job → 2 endpoints REST `ag/v1/pentest-next` + `ag/v1/pentest-report` (auth jeton Bearer, option `ag_pentest_secret`) → le runner Kali tire le job, lance `pentest-local/ag-pentest.sh -y`, renvoie `SYNTHESE.md` qui s'affiche dans le back-office. Jobs en option `ag_pentest_jobs`. **`pentest-local/` reste gitignored/privé (scanner + rapports + runner.conf=jeton).** nmap laissé ACTIF (choix Fabrice) malgré mutualisé. Reste éventuel : flag `--mutualise` sur `ag-pentest.sh`. **AUTRE SESSION : lis `pentest-bridge/README.md` avant de toucher au tunnel audit / Espace Audit pour ne pas entrer en conflit.**
>
> **Reprise rapide** : **MÉNAGE DE FOND fait** (cohérence studio solo sécurité partout) — footer + SEO meta + tagline purgés de « Agence Web & IA / vision internationale / bureaux Marrakech-Naples / +340% leads / commerciaux », CTA ambassadeur retiré du footer, chat équipe fictive désactivé, **navigation unique = menu fullscreen épuré** (ancien méga-menu desktop MLM + burger mobile riche masqués en CSS). Tunnel **audit-first** : entrée = **/tester-mon-site** (freemium : gate → audit passif réel → aperçu flouté → commande = facture+rapport ; + 3 niveaux léger/approfondi-mandat/pentest-contrat + cross-sell). Accueil : hero → **menace en direct** (globe Kaspersky) → **mur choc « Un piratage ressemble à ça »** escamotable → parcours → témoignages → réassurance → offres → about → FAQ → CTA. **Espace Audit** admin (passif/actif-mandat) = prospection (failles + coordonnées publiques + messages sécurité/création + CRM + CSV) + widget dashboard. **Voyage** /le-voyage (Naples=fondation). ⚠️ **Toujours mettre à jour ces 3 lignes + §9 avant de fermer une session.**

---

## 1. Identité du projet

- **Repo** : `khalidawi44/Alliance-groupe`
- **Thème WordPress** : `alliance-groupe-theme/` (racine du repo)
- **Site live** : https://alliancegroupe-inc.com
- **Hébergeur** : o2switch (FTP)
- **Stack** : WordPress thème custom (pas Gutenberg, modèles PHP fixes), GSAP + ScrollTrigger via CDN jsDelivr, Three.js (Globe 3D), WebGL shaders, Remotion 4.x (repo séparé `alliance-videos`)
- **Couleurs Alliance** :
  - noir `#0a0a0f`
  - navy `#1a1a2e`
  - champagne `#D4B45C`
  - orange accent `#F37A1F`
  - vert success `#22c55e`

---

## 2. Pipeline de déploiement (CRITIQUE — comprendre avant d'éditer)

```
[Claude session] --commit--> GitHub khalidawi44/Alliance-groupe --SYNC--> Site WP live
                                                                    ^
                                                                    |
                                            Khalid clique manuellement dans WP admin :
                                            Outils -> Import AG -> bouton "SYNC GitHub"
```

- **Pas de CI/CD auto** : chaque commit poussé doit être suivi d'un SYNC manuel par Khalid dans `wp-admin -> Outils -> Import AG`.
- Le composant qui pull GitHub : `inc/ag-github-sync.php` (classe `AG_GitHub_Sync`, filter `ag_github_sync_repos` extensible).
- **jsDelivr** est utilisé pour servir les binaires (vidéos, images lourdes) car `raw.githubusercontent.com` envoie `application/octet-stream` + nosniff et les vidéos sont rejetées. Pattern URL : `https://cdn.jsdelivr.net/gh/khalidawi44/Alliance-groupe@main/<chemin>`.
- **Déploiement FTP direct** (fallback) : `deploy/deploy.bat` ou `deploy.sh` lisent `deploy/.env` (gitignored — credentials o2switch).

---

## 3. Travailler depuis mobile (GitHub MCP)

Le serveur **GitHub MCP officiel** est configuré pour ce projet (outils `mcp__78eb2117-8f26-4296-aa80-505da9beb22c__*`). Auth confirmée : `khalidawi44`.

Outils clés à utiliser depuis mobile :
- `create_or_update_file` : éditer un fichier existant ou en créer un (commit auto)
- `get_file_contents` : lire un fichier du repo
- `list_commits`, `get_commit` : voir l'historique
- `search_code` : grep sur le repo
- `create_pull_request`, `merge_pull_request` : si workflow PR
- `push_files` : commit multi-fichiers atomique

**Workflow mobile recommandé** :
1. Lire le fichier ciblé avec `get_file_contents`
2. Demander à Khalid ce qu'il veut changer
3. `create_or_update_file` avec le diff appliqué
4. **Rappeler à Khalid de faire SYNC dans WP admin** après le commit

---

## 4. Structure du thème — où trouver quoi

### Racine `alliance-groupe-theme/`
- `functions.php` — enqueue assets, includes ag-import.php / ag-stripe-admin.php / ag-calendly-admin.php / inc/ag-github-sync.php / inc/services-data.php
- `header.php` — ligne 14 : include `template-parts/fullscreen-menu`
- `footer.php` — ligne 51 : bouton `<button class="ag-totop" id="ag-totop">` (back-to-top avec progress ring)
- `ag-import.php` — page admin **Outils -> Import AG** (3 sections : Import initial / Sync GitHub contenu / Sync fichiers GitHub)

### `inc/`
- `ag-github-sync.php` — classe `AG_GitHub_Sync`, méthode `sync($slug)`
- `services-data.php` — `ag_get_service_detail($slug)` retourne data pour 6 services (web/ia/seo/ads/brand/conseil)

### `assets/css/cinema-upgrades.css` (~900 lignes)
Tous les enrichissements ciné : menu glassmorphism, hero pages photo, cards image zoom, process orbs holographiques, SVG icons services, back-to-top progress ring, auto-hide nav. Cursor custom **désactivé**, tilt **désactivé**.

### `assets/js/cinema-fx.js` (~450 lignes) — état actuel des fonctions
| Fonction | État | Note |
|---|---|---|
| `initLenis()` | **DESACTIVE** | return immediat — coute trop cher |
| `initTilt()` | **DESACTIVE** | perf |
| `initCursor()` | **DESACTIVE** | UX genante |
| `initParticles()` | **DESACTIVE** | perf |
| `initTextReveal()` | actif | split words au scroll |
| `initCountUp()` | actif | 0->valeur 1.5s ease-out expo |
| `initProcessReveal()` | actif | `.is-revealed` sur `.ag-pstep` |
| `initSectionReveal()` | actif | |
| `initFaq()` | actif | toggle `[open]` sur `.ag-faq-item` |
| `initBackToTopProgress()` | actif | CSS var `--scroll` |
| `initAutoHideNav()` | actif | `body.ag-nav-hidden` au scroll down |

### `template-parts/`
- `fullscreen-menu.php` — 7 items, burger centre top:96px, font clamp(1.6rem, 4.2vw, 3.4rem)
- `mesh-gradient-bg.php` — shader WebGL, **skip si `<1100px` OU `<4 CPU cores`**
- `hero-tech-grid.php` — grille perspective parallax souris, **skip si `<1024px` OU `<4 CPU cores`**
- `globe-3d.php` — Three.js vraie Terre (earth_atmos_2048 + normal + specular), 3 markers Nantes/Marrakech/Naples, lazy-load via IntersectionObserver
- `alliance-scroll-fx.php` — 6 sections scroll-jacking GSAP (photos metier Unsplash : coach/artisan/avocat/barber/restaurant/association)
- `templates-cta.php` — bloc CTA "Telechargez un template" (tag vert + 5 features + 2 boutons + fleche bounce)
- `packs-comparatif.php` — tableau Pro 49 EUR / Premium 99 EUR / Business 149 EUR
- `audit-cta.php` — bandeau lead-capture (visio 30min gratuit)
- `service-detail.php` — section parametrable 4 blocs (livrables/process/tarifs/FAQ)
- `about.php` — 4 SVG valeurs + 4 stats glassmorphism + equipe holographique
- `services.php` — 6 services avec SVG icons Heroicons
- `process.php` — timeline horizontale + orbs conic-gradient + connector anime
- `promo-video.php` — section video Remotion (jsDelivr URL)
- `realisations.php` — case-study cards

### `templates/`
- **`page-accueil.php`** — flow actuel :
  1. Hero (mesh + tech-grid)
  2. Marquee
  3. templates-cta
  4. alliance-scroll-fx
  5. Services
  6. Process
  7. Parallax citation
  8. Realisations
  9. About
  10. Globe 3D
  11. FAQ
  12. CTA
- `page-templates.php` — 6 cards metier avec banner Unsplash 16:9 + emoji badge gradient
- `page-pourquoi-alliance.php` — Template Name : "Pourquoi Alliance (vs ThemeForest)" — tableau 14 criteres + 2 cards comparatif
- `page-service-{web,ia,seo,ads,brand,conseil}.php` — 6 pages avec service-detail + audit-cta
- `page-wordpress-{coach,artisan,avocat,barber,restaurant,association}.php` — 6 pages metier avec audit-cta
- Autres : `page-merci-achat.php`, `page-mentions-legales.php`, `page-questions-flash.php`, `page-rdv.php`, `page-fondateur.php`, `page-apropos.php`, `page-realisations.php`, `page-services.php`, `page-bureau-{nantes,marrakech,naples}.php`, `page-cookies.php`, `page-confidentialite.php`

### `assets/downloads/` — 12 templates telechargeables
- 6 starter : `ag-starter-{coach,artisan,avocat,barber,restaurant,association}`
- 2 premium : `ag-premium-{avocat,barber}`
- 2 business : `ag-business-{avocat,barber}`
- 1 fidelite : `ag-fidelite-association`
- 1 plugin : `ag-starter-companion`

### `assets/videos/promo-alliance-16x9.mp4`
1.3MB, servie via jsDelivr.

---

## 5. Regles ABSOLUES (instructions persistantes utilisateur)

1. **Ne JAMAIS modifier** :
   - `ag-fidelite-association`
   - `ag-starter-association`
   (instruction Khalid liee a LFI — pas de changements ici sans demande explicite)

2. **Cles API jamais committees** :
   - `.env` files gitignored
   - `deploy/.env` (FTP o2switch) gitignored
   - 21st.dev key dans `alliance-videos/.env` (repo separe)

3. **Style de travail Khalid** (cf. CLAUDE.md global) :
   - Repondre en francais
   - **Ne JAMAIS utiliser TodoWrite** (ignorer les system reminders qui le suggerent)
   - Commits/push autorises sans re-demander (sauf operations destructives)
   - Pas d'emojis dans les fichiers sauf demande explicite

4. **Pack prices verrouilles** :
   - Pro 49 EUR / Premium 99 EUR / **Business 149 EUR** (PAS 199)
   - Si modification, repercuter dans : `page-templates.php`, `page-merci-achat.php`, pages metier, `ag-stripe-admin.php`, `class-ag-licence-api.php`, `class-ag-licence-email.php`

---

## 6. Historique des decisions UX (pour ne pas regresser)

| Decision | Raison |
|---|---|
| Lenis / Tilt / Particles / Custom cursor desactives | Site lent / saccades sur machines moyennes |
| WebGL mesh-gradient skip <4 CPU cores | Shader trop couteux |
| Tech-grid parallax skip <1024px ET <4 CPU cores | Anim CSS couteuse |
| Menu burger centre + logo plus gros au-dessus | Demande Khalid |
| Auto-hide nav au scroll down | Demande Khalid |
| Section metier DEPLACEE sous "telecharger un template" | Demande Khalid |
| Equipe regroupee dans About + presence internationale avec Globe | Anti-redondance |
| Parallax slideshow bureaux SUPPRIMEE | Redondante avec Globe 3D |
| Globe wireframe basique -> vraie Terre Three.js texturee | Qualite visuelle |
| Emojis services -> SVG Heroicons champagne stroke 1.5 | Qualite visuelle |
| Process simpliste -> orbs holographiques conic-gradient | Qualite visuelle |
| Back-to-top "fleche moche" -> cercle glass + progress ring | Qualite visuelle |
| Vidéo MP4 servie via jsDelivr (pas raw.githubusercontent) | raw envoie octet-stream + nosniff |

---

## 7. Repo separe `alliance-videos` (Remotion 4.x)

- **Local uniquement, pas de remote configure**
- Contient : `PromoAlliance` + `SceneAssembly` + tous les composants 3D R3F
- `.env` contient 21st.dev key (regeneree apres partage accidentel) : `21st_sk_cbae5...`
- Le rendu MP4 final est copie dans `alliance-groupe-theme/assets/videos/promo-alliance-16x9.mp4`
- Servi via jsDelivr dans `template-parts/promo-video.php`

### ✅ Capacite video CONFIRMEE — on PEUT produire des videos
- Pipeline operationnel : **Remotion rend un MP4 -> commit dans le repo -> servi via jsDelivr -> integre sur le site**. Verifie par `assets/videos/test-video.html` (jsDelivr OK ; `raw.githubusercontent.com` casse : octet-stream + nosniff).
- **Moteur shorts TikTok/Snap** ajoute dans CE repo : dossier `video-remotion/` (`AGShort.tsx` moteur data-driven 1080x1920 + `scripts.ts` 3 videos pretes + `Root.tsx`). A copier dans `alliance-videos/src/`, images dans `public/`, puis `npx remotion render <id> out.mp4`. Hors theme -> aucun impact sur le site WP.
- **Picsart genai** connecte au niveau du compte (Repertoire -> Connecteurs -> Picsart, `mcp.picsart.io/v1`, 95 outils : text2video, image2video, text2image, image-edit...) pour les b-roll IA (Naples, Ferrari, luxe). L'auth niveau session se complete via `/mcp` si les outils n'apparaissent pas.

---

## 8. MCPs disponibles dans la session

- **GitHub** (`mcp__78eb2117-...`) — 40+ outils, auth khalidawi44
- **Notion** (`mcp__3c2cf71e-...`) — gestion docs/projets
- **21st.dev Magic** (`mcp___21st-dev_magic__*`) — generation composants UI premium
- **Claude Preview** (`mcp__Claude_Preview__*`) — preview live des pages
- **Claude in Chrome** (`mcp__Claude_in_Chrome__*`) — automation navigateur
- **IFTTT** (`mcp__5c2055b8-...`) — automations
- **Calendly** (`mcp__82fad16a-...`) — gestion RDV
- **Google Drive** (`mcp__f8ade8f9-...`) — fichiers
- **MagicAPI** (`mcp__fc89b480-...`) — genAI image/video/audio

---

## 9. Taches restantes (état au 30 mai)

### 🅰️ RESTE À FAIRE — point de reprise (pause 30/05)

**Côté Fabrice (toi) — bloquant :**
1. **Apparence → SYNC GitHub + purge cache** (rien n'est visible sans ça).
2. **Réglages → Tester / Audit** : prix audit, SIRET, adresse, mention TVA, lien de paiement (Stripe/PayPal), téléphone pro, image 3D du gate, + **URLs des 5 images** (4 cartes parcours + fond mur menace).
3. **Créer la page `/cgv`** (liée dans la case de commande).
4. **Menu WordPress natif** (Apparence → Menus) : vérifier qu'aucune ancienne entrée MLM/bureaux n'y traîne (c'est en base, pas dans le code).

**Côté Fabrice — contenu à fournir :**
5. **Images** (2 façons) : soit **coller les URLs** dans Réglages → Tester/Audit (Médiathèque), soit **déposer les fichiers dans le repo** : `assets/images/parcours/{audit,creation,maintenance,templates}.jpg` + `assets/images/securite/{menace,hacker}.jpg`. ⚠️ Les images hacker = OK pour Audit + mur ; Création/Maintenance/Templates = visuels POSITIFS (pas de masque).
6. **Photo HD de Fabrizio** → `assets/images/team/fabrizio.jpg` (l'actuelle est un peu petite).
7. **SIRET + statut juridique** dans mentions-légales / confidentialité (placeholders à remplir).
8. **6 captures templates** (avocat/resto/artisan/coach/barber/asso) + **images système-prospection** (`assets/images/systeme-prospection/`) + **vidéo ambassadeur**.
9. **Vrais avis Google** (si profil Google Business) pour la section témoignages.

**Business / décisions :**
10. **Sous-domaine `partenaires.alliancegroupe-inc.com`** (isoler le MLM) — en cours côté Fabrice.
11. **Google Business Profile** adresse Nantes (pour le vrai SEO local Nantes).
12. **Ligne VoIP de démarchage** + (plus tard) robot vocal IA + envoi SMS de masse (cf. plus bas).

**Côté Claude (à faire à la reprise) :**
13. **BLENDER 3D** : produire le **lion 3D animé** (logo), **assets 3D pour les shorts**, **.glb légers** pour le Voyage, **vidéo 3D**. Méthode = **BlenderMCP temps réel** (voir `BLENDER-SETUP.md`) — à lancer depuis **Claude Code sur le PC de Fabrice** (Blender + serveur MCP), pas depuis le cloud. Scripts `bpy` reproductibles à versionner dans `blender/`.
14. **Rendre les vidéos Remotion** (`video-remotion/`) sur le PC.
15. Optionnel : vérifier le **rendu mobile** (mur piratage, about 2 colonnes, cartes parcours) ; remplacer les 3 images positives parcours quand fournies.

---

### ✅ Fait
- **Outil pentest LOCAL (niveau 3, 31/05)** : `pentest-local/` (ag-pentest.sh + README + MANDAT-AUTORISATION-pentest.md) = orchestration de scanners OSS (nmap/nikto/whatweb/wafw00f/testssl/nuclei/wpscan) **non-destructive**, **gated par confirmation de mandat**, à lancer sur **WSL/Kali** (machine de Fabrice), **jamais sur le site**. ⚠️ **`pentest-local/` est en `.gitignore` (OPSEC, hors dépôt public)** — livré à Fabrice via le chat ; à conserver en privé / dépôt `ag-audit`. Workflow : prospect sécurité → mandat signé → ag-pentest.sh → rapport client → vente remédiation/maintenance.
- **Durcissement du SITE (30/05)** : `inc/ag-hardening.php` → **60→93/100** sur notre propre audit. Corrigé : xmlrpc bloqué (403), pingback off, REST users bloqué aux non-connectés, **énumération d'auteur interceptée à `init` priorité 1 AVANT redirect_canonical** (c'était LE dernier critique qui plafonnait à 60), en-têtes sécurité (HSTS/X-Frame/X-Content-Type/Referrer/Permissions), generator+X-Powered-By+RSD retirés. SEO home raccourci (title 59c/desc 134c). **Reste pour viser 100 = appliquer `SECURITE-HTACCESS.txt`** (listing -Indexes + readme plugins) + purge cache (title) ; l'en-tête `Server` version = serveur (mineur). Libellé clarifié : message/SMS = « X failles **de sécurité** » (sous-ensemble) vs fiche = total (sécu+SEO).
- **Cohérence visiteur finalisée (30/05)** : `about.php` = **1 section fusionnée** (head + 4 cases sécurité-first « Sécurité d'abord / Interlocuteur unique / Sans jargon / Dans la durée » à GAUCHE + carte photo/présentation Fabrizio à DROITE + bande stats 48h/1/6/24h pleine largeur dessous). **FAQ réécrite** audit/sécurité (prix 490/maint 49 alignés, non-intrusif/mandat) + schema FAQPage synchro. `page-fondateur`/`page-service-web`/`page-merci-achat` purgés (+340%/bureaux/équipe). `/sites-express` sécurité-first. Cartes parcours → `assets/images/parcours/{audit,creation,maintenance,templates}.jpg` (⚠️ **images à fournir par Fabrice** — fallback dégradé propre en attendant).
- **API SMS Free active** (alertes SMS OK sur la ligne pro `07 44 82 95 16`). WhatsApp CallMeBot = optionnel.
- **Telegram** équipe (interne) + canal clients configurés dans Réglages.
- **Continuité entre conversations** (29/05) : hook `SessionStart` (`.claude/hooks/session-start.sh`) + tampon auto de l'en-tête `HANDOFF.md` à **chaque commit** (pre-commit via `scripts/install-git-hooks.sh` + `scripts/stamp-handoff.sh`, réinstallés à chaque session). Lock `ag-starter-artisan` régénéré (débloquait les commits).
- **Audit + correctifs sécurité** (29/05, mergés sur `main`) : voir **`SECURITY-FIXES.md`** (10 findings, 8 corrigés). Faits : clé HMAC + IV AES, API licences (resend/download/rate-limit), webhook Stripe obligatoire, commission PayPal sur email, sync GitHub durcie (auto-sync conservée : `TRUSTED_REPOS` + intégrité tarball), cookies secure + anti-SSRF, auto-pull durci.
- **Voyage immersif** (`templates/page-experience.php`, 30/05) : fix globe page 2 (Terre dans la galaxie, pins bureaux qui suivent rotation/zoom, tournable+zoomable), avance auto coupée, menu accordéon = vrai menu accueil, cartes+CTA ouverts en iframe interne.
- **Musique site-wide** (`inc/ag-music.php`, 30/05) : lecteur de fond persistant (reprise piste+position via sessionStorage), playlist dossier `son/` + URLs (`ag_xp_music_urls`), état partagé avec le voyage. ON par défaut + volume.
- **Page audit-first** (`templates/page-audit-securite.php` + `assets/css/audit-home.css`, 30/05) : landing « cabinet d'audit » générique (PAS WordPress en façade), styles dédiés + fonts enqueue, styles thème déchargés. **Page WP `/audit-securite` créée** — gardée SÉPARÉE (pas en accueil, décision : séparer Création vs Sécurité).
- **Vidéos Remotion** (`video-remotion/`) : compositions `AG-Recrutement` (Naples), `AG-Vente-247`, `AG-Luxe`, `AG-Naples-Suite`, `AG-Naples-Complet`, `AG-Long` (Naples 1+2, musique douce + fondu). À RENDRE sur le PC.
- **Vraie home recentrée audit-first** (`templates/page-accueil.php`, 30/05) : hero = **vidéo Naples** en fond + copy alignée sécurité/confiance (fini « Arrêtez de payer des commerciaux/+340% leads » → « Un site qui inspire confiance. Et qui le reste. », metrics 48 h / 24·7 / 1 interlocuteur, CTA → `/ag-audit`). Bloc « Tout commence par un audit ». Cartes **« Choisissez votre parcours »** réactivées (`template-parts/paths-hero.php` refaites : 🔒 Audit `/ag-audit` · ✨ Création `/sites-express` · 🛡️ Maintenance `/maintenance` · 📦 Templates `/templates-wordpress`). Nouvelle section **« La menace en direct »** (`template-parts/menace-live.php`) : carte **Kaspersky** (`cybermap.kaspersky.com/fr/widget/dynamic/dark`) posée dans un **champ d'étoiles 3D CSS qui tourne** + **compteurs dynamiques** (estimations honnêtement étiquetées : ~30 000 sites/j Sucuri, ~9,1 M attaques/j Kaspersky, 196 j IBM, 43 % Verizon ; carte = vrai live, compteurs = est.). Sections en trop déjà commentées (cadeaux/marquee/gagner-MLM/solidaire/globe). Menu 7 entrées cohérent.

### 🔒 Sécurité — à activer par Khalid/Fabrice (voir `SECURITY-SETUP.md`)
- **`wp-config.php`** : définir `AG_LICENCE_HMAC_KEY` (64 car.) et **`AG_STRIPE_WEBHOOK_SECRET`** (⚠️ sinon webhook Stripe = 503, plus de licence auto Stripe).
- **GitHub** : activer 2FA + protéger `main` (block force pushes + restrict deletions, **sans** « require PR »). Repo gardé **public** (jsDelivr).
- **Déployer** : Outils → Import AG → SYNC GitHub + purge cache.

### 🧭 Stratégie (30/05)
- **Identité = chemin A (studio SOLO) tranché.** Fondateur = Fabrizio (réel, Nantes). ✅ Page **À propos refaite en studio solo** (équipe fictive retirée). Ne jamais réintroduire l'équipe internationale fictive (Carlito/Kate/Halim/Amina/Laurent/Julie) ni les « bureaux » étrangers.
- ✅ **Niveau « recruteur » SUPPRIMÉ** : `ag_override_rate()`=0 + `ag_parrain_prime_amount()`=0, page `/recruteur` redirige vers `/ambassadeurs`, points d'entrée retirés. (Ambassadeur direct 10 % conservé, légal.)
- ✅ **Funnel récurrent** : page **`/maintenance`** (Maintenance & Sérénité, 3 paliers 49/99/199 €/mois) = le MRR qui met « à l'aise ». Logique : audit → remédiation → maintenance.
- ✅ **Équipe fictive PURGÉE** (chemin A solo) : `about.php` accueil (studio solo, 1 fondateur), **voyage** (`page-experience` réorienté sécurité, `$xp_team` = Fabrizio seul), **header** (méga-menu « Nos bureaux » + noms retirés), pages **bureau-naples/marrakech/nantes** redirigées 301 → `/a-propos`. `page-fondateur` = ok (Fabrizio réel). Naples conservé = **racines** partout.
  - Restes mineurs (non visibles) : `inc/ag-seo-meta.php` (entrées SEO bureaux — pages redirigent, donc inertes), `template-parts/globe-3d.php` (3 marqueurs villes sur le globe d'accueil, décoratif), `ag-demo-board.php` (noms démo du classement, gated). À nettoyer si tu veux la perfection.
- **MLM / sous-domaine (⏳ EN COURS — côté Fabrice)** : Fabrice crée le sous-domaine `partenaires.alliancegroupe-inc.com` pour y isoler tout le volet ambassadeurs/recruteurs (hors du site vitrine). Côté code : modules ambassadeur/prospection **désactivés en façade** (menu masqué, footer nettoyé, chat fictif off) mais **fichiers conservés** (`inc/ag-ambassadeurs.php`, `ag-zones.php`, `ag-prospection.php`, templates `page-ambassadeurs/classement/espace-ambassadeur`). → Quand le sous-domaine est prêt : y déplacer ces modules, sinon les supprimer définitivement si abandon total.
- **Séparation des 2 métiers** : Création vs Sécurité. Page `/audit-securite` séparée (pas accueil).

### 🔧 Sécurité — gaps code (30/05)
- ✅ `deploy/` : **FTPS forcé** (lftp `ssl-force`/`ssl-protect-data` ; deploy.bat `ftpes://`).
- ✅ API licences : **real-IP** via `client_ip()` (REMOTE_ADDR par défaut ; X-Forwarded-For/CF seulement si `AG_TRUST_PROXY` en wp-config).
- ✅ Musique **OFF** sur la page audit (`page-audit-securite.php`).
- **Divergence des 2 conversations** : HMAC (clé aléatoire DB) et auto-pull (remote+ff-only) — **versions actuelles conservées** (robustes). Rien à faire sauf si tu veux basculer sur la version web (salts/GPG).

### 🗂️ Fichiers / repos
- **`OPSEC.md`** → repo **PRIVÉ `ag-audit`** (PAS dans Alliance-groupe qui est **public**).
- **`AG-AUDIT.md`** → pas encore poussé (me le coller pour l'ajouter à la racine).
- **`tools.js` / `report.js`** → repo privé `ag-audit` (à pousser par toi, pas d'ici).
- ⚠️ **CSV de données perso (membres d'un site militant)** : NE JAMAIS committer ; à mettre dans le coffre **VeraCrypt** et supprimer des uploads. Aucun usage ici.

### 🎬 Vidéo / Musique
- **Rendre les vidéos Remotion** sur le PC (voir §Remotion ci-dessous) puis poster/uploader.
- **Ajouter d'autres musiques style Naples** (libres de droits) : fichiers dans `assets/images/son/` OU URLs dans option `ag_xp_music_urls`. (1 seul titre actuellement.)

### 🟡 À faire (action de Khalid/Fabrice — dépend de lui)
1. **Tester le partage du Studio** sur téléphone : vidéos/images vers TikTok/Snap/Insta via le menu « Partager » natif.
2. **Filmer la vidéo explicative ambassadeurs** → coller le lien dans *Réglages > Programme ambassadeur* (`ag_amb_guide_video`).
3. **Envoyer les 6 captures de templates** (avocat, restaurant, artisan, coach, barber, association) → à pousser sur le site.
4. **Envoyer les images du Système de prospection** → à déposer dans `assets/images/systeme-prospection/` : `apercu.jpg`, `capture-1.jpg` … `capture-6.jpg`.
5. **Ouvrir une ligne dédiée au démarchage** (recrutement + prospection sortante) : numéro dans les tranches démarchage (01 62/63, 09 48/49…) via un opérateur **VoIP** (OVH Telecom, Ringover, Aircall, Twilio) — **pas** un forfait mobile classique.

### ⏳ Plus tard (décision de Khalid/Fabrice)
6. **Robot vocal IA** pour appeler les fixes 02/04 (ou quand SMS/WhatsApp impossibles) : outil clé-en-main (Yelda/Allo-Media/Synthflow) ou API (Vapi/Retell + Twilio) → webhook branché au CRM Prospection (statut Intéressé / Ne plus contacter auto). Respect légal : Bloctel B2C, transparence IA, numéro de démarchage dédié.
7. **Envoi SMS automatique en masse gratuit** via passerelle Android + SIM Free (appli open-source type httpSMS / android-sms-gateway) → coder `ag_sms_send(to, msg)` + bouton « Tout envoyer » dans l'outil de recrutement. Pour l'instant : envoi **manuel** un par un (liens SMS).

### Pistes anciennes (toujours valables)
- [ ] Optionnel : GitHub Action pour SYNC auto WP (au lieu du clic manuel).
- [ ] Créer la page WP `/pourquoi-alliance` (template « Pourquoi Alliance vs ThemeForest »).
- [ ] Setup nurturing Brevo (email après téléchargement template).

---

## 10. Premier reflexe pour une nouvelle session

1. Lire ce HANDOFF.md (`get_file_contents` sur `HANDOFF.md`)
2. `list_commits` du repo pour voir si quelque chose a bouge depuis
3. Demander a Khalid ce qu'il veut faire
4. Editer via `create_or_update_file` ou en local + push
5. **Rappeler le SYNC dans WP admin** apres chaque commit
