# HANDOFF — Alliance Groupe (site `alliancegroupe-inc.com`)

> Document de reprise pour toute nouvelle session Claude (PC ou mobile via GitHub MCP).
> Dernière mise à jour : 2026-05-29 — branche de travail : `claude/conversation-linking-bug-O6HUR` (tampon auto à chaque commit).
> **Reprise rapide** : mise en place de la **liaison automatique entre conversations** — hook `SessionStart` (`.claude/hooks/session-start.sh`) qui injecte branche + commits + travail non commité + cet en-tête à chaque nouvelle session. ⚠️ **Toujours mettre à jour ces 3 lignes + §9 avant de fermer une session** : c'est ce que la prochaine conversation lira.

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

## 9. Taches restantes (état au 29 mai)

### ✅ Fait
- **API SMS Free active** (alertes SMS OK sur la ligne pro `07 44 82 95 16`). WhatsApp CallMeBot = optionnel.
- **Telegram** équipe (interne) + canal clients configurés dans Réglages.
- **Continuité entre conversations** (29/05) : hook `SessionStart` (`.claude/hooks/session-start.sh`) + tampon auto de l'en-tête `HANDOFF.md` à **chaque commit** (pre-commit via `scripts/install-git-hooks.sh` + `scripts/stamp-handoff.sh`, réinstallés à chaque session). Lock `ag-starter-artisan` régénéré (débloquait les commits).
- **Audit + correctifs sécurité** (29/05, mergés sur `main`) : voir **`SECURITY-FIXES.md`** (10 findings, 8 corrigés). Faits : clé HMAC + IV AES, API licences (resend/download/rate-limit), webhook Stripe obligatoire, commission PayPal sur email, sync GitHub durcie (auto-sync conservée : `TRUSTED_REPOS` + intégrité tarball), cookies secure + anti-SSRF, auto-pull durci.

### 🔒 Sécurité — à activer par Khalid/Fabrice (voir `SECURITY-SETUP.md`)
- **`wp-config.php`** : définir `AG_LICENCE_HMAC_KEY` (64 car.) et **`AG_STRIPE_WEBHOOK_SECRET`** (⚠️ sinon webhook Stripe = 503, plus de licence auto Stripe).
- **GitHub** : activer 2FA + protéger `main` (block force pushes + restrict deletions, **sans** « require PR »). Repo gardé **public** (jsDelivr).
- **Déployer** : Outils → Import AG → SYNC GitHub + purge cache.

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
