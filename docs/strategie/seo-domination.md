# Plan SEO de domination — Alliance Groupe (Nantes / Loire-Atlantique + Naples)

> Objectif patron : « être incontournable dans les moteurs ». Traduction lucide :
> **incontournable = contenu + SEO local + autorité + technique + temps**. Pas de magie,
> pas de black-hat (qui pénalise). On vise une domination défendable sur 90 jours puis 12 mois.
> Cap : **gagner d'abord le SEO LOCAL (le plus rentable, le plus rapide), puis les pages métiers, puis l'autorité.**

Date : 2026-06-13. Site : alliancegroupe-inc.com (WordPress, thème autonome, Yoast + IndexNow).

---

## 0) Ce qui est DÉJÀ en place (ne pas refaire) vs ce qui MANQUE

### Déjà fait (capital existant)
- **Schema Organization + ProfessionalService par bureau** (Nantes + Naples) — bonne base d'entité.
- **FAQPage sur l'accueil** — éligible aux rich results et aux citations IA.
- **Sitemap Yoast** + **titres/descriptions par page** + **IndexNow** (push instantané Bing/Yandex, accélère l'indexation).
- **Test de sécurité gratuit (« Tester mon site »)** + **Audit SEO gratuit** (`inc/ag-audit-seo.php`, 12 checks, score /100) → **lead magnets déjà codés** = aimants SEO + portes d'entrée prospection.
- GA4 (`G-RSQ6Y8DHK4`) + Consent Mode RGPD, AdSense/ads.txt, robots.txt ouvert.
- CRM + chasse Google Places + notifications Telegram/SMS (pour transformer le trafic SEO en RDV).

### Ce qui MANQUE (le cœur du chantier)
1. **Pages locales dédiées** (Nantes, St-Herblain, Rezé, St-Nazaire) — aujourd'hui absentes.
2. **Pages « création site web [métier] »** indexables (les templates existent comme produit, pas comme pages SEO).
3. **Schema LocalBusiness multi-ville** (on a ProfessionalService, mais pas le `LocalBusiness` géolocalisé avec `geo`, `openingHours`, `areaServed` par fiche).
4. **Google Business Profile** optimisé + stratégie d'avis (le facteur n°1, voir §3).
5. **Blog actif** ciblant l'informationnel + cyber/IA (presque tout le trafic GEO/IA passe par là).
6. **Schema Service + BreadcrumbList + Review/AggregateRating** sur les pages offres.
7. **Stratégie de netlinking** (annuaires qualifiés, presse locale, partenariats).
8. **Suivi Search Console** structuré (positions, CTR, requêtes) — la boussole.

---

## 1) MOTS-CLÉS CIBLES (priorisés volume × intention × faisabilité)

> [FAIT] Le marché « agence web Nantes » est dense : Digital Unicorn, NOIISE, Kalelia, Nobilito,
> L'Agence123, Kagency (26 ans), LATELIER — plusieurs établis depuis 2001-2010.
> Source : SERP « création site web Nantes » (juin 2026). **Conséquence stratégique : on n'attaque PAS
> le head term de front, on gagne par le local fin (quartiers/villes) + métiers + cyber (peu disputé).**
>
> [FAIT] Les requêtes **transactionnelles** sont peu touchées par les AI Overviews ; les **informationnelles**
> perdent 18-34 % de CTR. Source : Searchlab / SearchEngineJournal (2026). **Conséquence : le blog vise
> la citation IA (GEO), pas le clic brut ; l'argent est sur le transactionnel local + métier.**

Légende intention : **T** = transactionnel (chaud), **C** = commercial (comparaison), **I** = informationnel.
Volumes = **[HYPOTHÈSE]** (pas de Keyword Planner ici) ordre de grandeur à valider dans Search Console / Planner.

### Priorité 1 — Transactionnel local (ROI immédiat) → pages piliers + GBP
| Mot-clé | Intention | Vol. est. [HYP] | Page cible |
|---|---|---|---|
| création site internet Nantes | T | moyen-élevé | Pilier `/creation-site-internet-nantes` |
| agence web Nantes | C | élevé (très concurrentiel) | Accueil + pilier Nantes |
| refonte site WordPress Nantes | T | faible-moyen | `/refonte-site-wordpress-nantes` |
| création site internet Saint-Herblain / Rezé / Saint-Nazaire | T | faible (peu disputé = gagnable) | 1 page locale / ville |
| sécurité informatique PME Nantes | T | faible-moyen (en hausse) | `/securite-informatique-pme-nantes` |
| audit de sécurité site web | T | moyen | `/audit-securite-site-web` (lie au test gratuit) |
| maintenance site WordPress Nantes | T | faible (récurrent = MRR) | `/maintenance-site-wordpress` |

### Priorité 2 — Métiers (longue traîne transactionnelle, faible concurrence)
| Mot-clé | Page cible |
|---|---|
| création site internet avocat | `/creation-site-internet-avocat` (avocats = email/courrier only, cf. déonto) |
| site internet restaurant | `/site-internet-restaurant` |
| site internet artisan (+ plombier/électricien) | `/creation-site-internet-artisan` |
| site internet coach (sportif/business) | `/site-internet-coach` |
| site internet barbier / salon de coiffure | `/site-internet-barbier` |
| site internet association | `/site-internet-association` (lien offre gratuite asso) |

### Priorité 3 — Informationnel / longue traîne (BLOG → GEO + autorité, voir §4)
- « combien coûte un site internet en 2026 » (I, fort volume, capte le chaud en bas de page)
- « NIS2 PME que faire / suis-je concerné » (I, **en explosion**, cf. §below)
- « comment savoir si mon site est piraté / sécurisé » (I → CTA test gratuit)
- « WordPress ou site sur-mesure » / « refaire son site soi-même ou agence »
- « avis Google : comment en obtenir (légalement) » (I, lie notre expertise)
- « site internet pas cher : bonne ou mauvaise idée »
- « IA et site web : ce que ça change pour une PME en 2026 »

> [FAIT] **NIS2 : échéance 17 octobre 2026, ~10 000 PME/ETI françaises classées entités essentielles/importantes,
> audit triennal obligatoire ; les cyber-assureurs exigent depuis 2023-24 un audit < 24 mois sous peine de refus
> de couverture.** Sources : copwell.fr, mobilecube.fr, mytrustpartner (2026).
> **PARI fort : NIS2 est le meilleur angle SEO + commercial de l'année. On crée un hub de contenu NIS2/cyber-PME
> qui draine du trafic chaud vers l'audit (porte d'entrée → sur-mesure → maintenance récurrente).**

---

## 2) ARCHITECTURE & PAGES (cocon sémantique)

Principe : **1 intention = 1 page** (anti-cannibalisation). 3 cocons.

### Cocon A — Web / création (tête : Accueil + pilier Nantes)
```
/ (accueil, entité + services + FAQ)
└─ /creation-site-internet-nantes   (PILIER)
   ├─ /refonte-site-wordpress-nantes
   ├─ /maintenance-site-wordpress
   ├─ /creation-site-internet-avocat
   ├─ /site-internet-restaurant
   ├─ /creation-site-internet-artisan
   ├─ /site-internet-coach
   ├─ /site-internet-barbier
   └─ /site-internet-association
   └─ pages locales : /agence-web-saint-herblain, /-reze, /-saint-nazaire
```

### Cocon B — Cybersécurité (tête : pilier sécurité PME)
```
/securite-informatique-pme-nantes   (PILIER)
├─ /audit-securite-site-web   (← « Tester mon site » gratuit en CTA)
├─ /pentest-test-intrusion
├─ /conformite-nis2-pme   (hub NIS2, aimant 2026)
└─ blog cyber (cf §4)
```

### Cocon C — Blog (autorité + GEO)
Tous les articles renvoient au pilier pertinent (A ou B) + 1 CTA (test gratuit ou devis).

### Règles de maillage
- Chaque page enfant lie **vers son pilier** (ancre exacte 1×) + 2-3 pages sœurs.
- Le pilier lie vers **toutes** ses enfants (table des matières).
- **Breadcrumb** sur toutes les pages (+ schema BreadcrumbList).
- **À fusionner / éviter** : ne pas créer 2 pages « création site web » + « agence web » qui se cannibalisent — l'accueil porte « agence web Nantes », le pilier porte « création site internet Nantes ».
- Pages locales = **contenu unique par ville** (références locales, secteurs, mini-FAQ ville) — pas de duplication par variable, Google pénalise les pages-doorway clonées.

---

## 3) SEO LOCAL (priorité n°1 — le plus rentable)

> [FAIT] Pondération des facteurs locaux 2026 (étude Whitespark/Guest-suite) :
> **GBP 32 % · on-page 19 % · avis 16 % · liens 15 % · comportemental 8 % · citations 7 %.**
> La **récence des avis** et le fait d'**être ouvert au moment de la recherche** montent fort.
> Sources : guest-suite.com, passion-referencement.fr, clickrank.ai (2026).

### Google Business Profile (32 % du jus local = on attaque ici en premier)
- **Catégorie principale** : « Concepteur de sites Web » ; secondaires : « Agence de marketing Internet », « Consultant en sécurité », « Service de référencement ».
- **NAP** strictement identique partout (nom, adresse Nantes, tél `07 44 82 95 16`).
- **Zone desservie** : Nantes, St-Herblain, Rezé, St-Nazaire, Vertou, Carquefou (areaServed).
- **Posts GBP hebdo** (offre/actu/cas client) — signal de fraîcheur.
- **Photos** : ≥ 10 (équipe, réalisations, logo), +1-2/semaine.
- **Section Q&R** : pré-remplir 5-6 questions (prix, délai, sécurité, NIS2).
- **Messagerie GBP activée** → notifier via le système Telegram/SMS existant.
- Si bureau Naples physique : fiche GBP séparée (NAP IT distinct).

### Avis Google (16 %, récence = clé) — STRICTEMENT conforme
> [FAIT] Interdit : **review gating** (filtrer les satisfaits), exiger 5 étoiles, dicter le contenu, incitation financière.
> Le simple achat ne vaut PAS consentement (CNIL). Pédagogie transparente = 40-60 % d'acceptation.
> Sources : viewup.fr, review-collect.com, CNIL (2026).
- Demande **systématique et neutre** à chaque livraison de site (« si l'expérience vous a plu, un avis nous aide »).
- **QR code / lien court** sur facture + email de fin de projet (le Kit Print existant peut porter le QR).
- **Cadence cible : 2-4 avis/mois** (la régularité > le volume d'un coup).
- **Répondre à 100 % des avis** sous 48 h (signal d'engagement).

### Citations / annuaires (7 %) + NAP
- Inscriptions NAP cohérentes : **PagesJaunes, Société.com, Google, Bing Places, Apple Plans, Yelp, Kompass, annuaire CCI Nantes/44.**
- Annuaires métier web/tech qualifiés (éviter les fermes de liens).

### Schema LocalBusiness multi-ville (MANQUE — à coder)
- Ajouter `LocalBusiness` (ou sous-type `WebDesignCompany`/`ProfessionalService`) **par page locale** avec `geo` (lat/lng), `openingHours`, `areaServed`, `telephone`, `sameAs` (réseaux + GBP).

---

## 4) CONTENU / BLOG — calendrier éditorial 12 semaines

Format : **1 article pilier-long (1500-2200 mots) / semaine**, structure Hn propre, réponse directe en intro (snippet/IA),
FAQ en bas (schema FAQPage), 1 CTA contextuel (test sécurité OU devis), maillage vers pilier. **Fréquence : 1/sem (tenable solo).**
Angle GEO : phrases-réponses courtes citables par les IA + données chiffrées originales (nos audits anonymisés).

| Sem | Sujet | Mot-clé / intention | Cocon | CTA |
|---|---|---|---|---|
| 1 | Combien coûte un site internet en 2026 (vrais prix) | « prix site internet 2026 » (I→T) | A | Devis |
| 2 | NIS2 : votre PME est-elle concernée ? (checklist) | « NIS2 PME concernée » (I, hot) | B | Test sécu |
| 3 | 7 signes que votre site WordPress est vulnérable | « site piraté que faire » (I) | B | Test sécu |
| 4 | Site internet pour avocat : règles déonto + modèle | « site internet avocat » (T) | A | Devis |
| 5 | Refaire son site soi-même ou agence ? | « refaire site soi-même » (C) | A | Devis |
| 6 | Audit de sécurité : ce que ça révèle (cas réels) | « audit sécurité site web » (T) | B | Audit |
| 7 | Site restaurant qui remplit la salle (réservation, SEO local) | « site internet restaurant » (T) | A | Devis |
| 8 | IA et site web PME : ce qui change en 2026 | « IA site web entreprise » (I) | A | Devis |
| 9 | Cyber-assurance refusée sans audit : le piège | « cyber assurance audit » (I, hot) | B | Audit |
| 10 | Maintenance WordPress : pourquoi 29-99 €/mois | « maintenance WordPress » (T→MRR) | A | Maintenance |
| 11 | Avis Google : comment en obtenir légalement (CNIL) | « obtenir avis Google légal » (I) | A | Devis |
| 12 | Création site artisan : être trouvé sur Nantes | « site internet artisan » (T) | A | Devis |

> Reconduire chaque trimestre en élargissant les villes/métiers. Mettre à jour les vieux articles (signal fraîcheur).

---

## 5) TECHNIQUE

- **Core Web Vitals** : viser LCP < 2,5 s, CLS < 0,1, INP < 200 ms. Audit PageSpeed des piliers ; images **WebP** + `width/height` (anti-CLS), lazy-load (attention : les scènes 3D / `page-experience.php` sont lourdes — ne pas les charger sur les pages SEO).
- **Mobile-first** : burger centré OK ; vérifier tap targets et lisibilité.
- **Indexation** : Search Console à brancher (propriété domaine), **soumettre le sitemap Yoast**, **IndexNow déjà actif** (push à chaque publi), surveiller « Pages » (exclusions). Vérifier que les nouvelles pages ne sont pas en `noindex`.
- **Données structurées à ajouter** : `Service` (chaque offre), `BreadcrumbList` (toutes pages), `LocalBusiness` (pages locales), `Review`/`AggregateRating` (témoignages clients réels uniquement), `Article` (blog). Valider au Rich Results Test.
- **HTTPS / sécurité comme signal** : HSTS, en-têtes (CSP, X-Frame-Options) — **et on en fait un argument** (« notre propre site passe l'audit qu'on vous vend »).
- **Hn** : 1 seul H1/page = mot-clé cible ; H2/H3 = sous-intentions et questions.
- **Images** : `alt` descriptif géolocalisé (« création site internet Nantes — réalisation restaurant »).
- **Anti-cannibalisation** : 1 intention/page (cf. §2), surveiller dans Search Console les pages qui se disputent une même requête.

---

## 6) AUTORITÉ / BACKLINKS (15 % du local + autorité globale) — éthique only

> [FAIT] Depuis 2022 Google déprécie automatiquement le link-building bas de gamme ; une mention
> dans la presse régionale (Ouest-France) + schema LocalBusiness + entité cohérente est exploitée
> par Google ET les LLM (GEO). Sources : seosupernova.fr, made2com.fr, webmarketing-com.com (2026).

- **Annuaires qualifiés** : CCI Nantes, French Tech Nantes, annuaires web/agences reconnus, Solocal/PagesJaunes pro. NAP cohérent partout.
- **Presse / médias locaux** : pitcher un angle territorial — « une agence Nantes-Naples qui sécurise les PME pour NIS2 ». Ouest-France, Presse Océan, radios/blogs locaux, news.eco régionales.
- **Contenu linkable** : publier une **étude originale** à partir des audits gratuits anonymisés (« X % des sites de PME nantaises échouent au test de sécurité ») → citée + reprise = backlinks naturels + autorité GEO.
- **Partenariats** : experts-comptables, assureurs (angle cyber-assurance), graphistes, fournisseurs locaux → échanges de visibilité / pages partenaires.
- **HARO / sollicitations** : répondre aux demandes de journalistes (cyber/web/IA) via plateformes (HARO/Sourcee/Helpareporter FR).
- **Profils sociaux** complets et cohérents (sameAs) : LinkedIn (B2B = clé), Google, etc.
- **Programme ambassadeurs** (déjà codé) : leurs partages = signaux sociaux + trafic de marque (le « brand search » renforce l'entité).

---

## 7) PLAN 90 JOURS (semaine par semaine)

### KPIs cibles à 90 jours [HYPOTHÈSE, à caler sur baseline Search Console S1]
- **GBP** : +200 % de vues, ≥ 30 appels/mois, **8-12 avis** gagnés (récents).
- **Positions** : top 3 sur 4-6 requêtes locales peu disputées (villes/métiers) ; top 10 sur « création site internet Nantos »… (Nantes).
- **Trafic organique** : x2 à x3 vs baseline (effet pages locales + blog).
- **Leads** : ≥ 15 leads/mois via test gratuit + formulaires (alimentent le CRM existant).

### Mois 1 — Fondations local + technique
- **S1** : brancher **Search Console** (baseline), audit PageSpeed des futurs piliers, créer/optimiser **GBP** (catégories, NAP, photos, areaServed). [Fabrizio : GBP + Search Console] [Codé : sitemap/IndexNow OK]
- **S2** : coder **schema LocalBusiness multi-ville** + BreadcrumbList + Service. Rédiger pilier **/creation-site-internet-nantes** + **/securite-informatique-pme-nantes**.
- **S3** : 4 pages locales (Nantes, St-Herblain, Rezé, St-Nazaire) en contenu unique. Article blog S1+S2. Lancer demande d'avis systématique.
- **S4** : pages métiers avocat + restaurant + artisan. Inscriptions annuaires (NAP). Article S3.

### Mois 2 — Métiers + contenu + autorité
- **S5-6** : pages métiers coach/barbier/asso + /refonte + /maintenance + /audit-securite-site-web. Articles S4-S6. Posts GBP hebdo.
- **S7** : **hub NIS2** (/conformite-nis2-pme) — pièce maîtresse 2026. Pitch presse locale (angle NIS2/Nantes-Naples).
- **S8** : lancer l'**étude originale** (audits anonymisés). Articles S7-S8. Relance avis.

### Mois 3 — Autorité + itération
- **S9-10** : campagne backlinks (presse, partenaires assureurs/experts-comptables, HARO). Articles S9-S10.
- **S11** : analyse Search Console (requêtes qui montent, cannibalisations), optimiser les pages à 4-15e position (quick wins CTR/contenu).
- **S12** : bilan KPIs, mise à jour des vieux articles, plan trimestre 2 (élargir villes/métiers).

### Répartition rôles
- **Fabrizio (humain)** : GBP (validation propriété), demande d'avis en clientèle, pitch presse/partenaires, validation contenu, photos. Réponses aux avis/GBP.
- **Déjà codé (à exploiter)** : test sécurité + audit SEO (lead magnets), CRM/Places/Telegram (conversion), Kit Print (QR avis), sitemap+IndexNow, schemas Org/ProfessionalService, FAQ accueil.
- **À coder (hand-off dev)** : pages piliers/locales/métiers (templates `page-*.php`), schema LocalBusiness/Service/Breadcrumb, section blog SEO-friendly, bloc avis (AggregateRating sur témoignages réels), branchement Search Console.

---

## 6 bis) RISQUES & ÉTHIQUE / LÉGAL
- **Avis** : zéro review-gating, zéro incitation financière, consentement explicite (CNIL) — sinon sanction + suppression Google.
- **Sécurité** : jamais de test intrusif (pentest) sans **mandat écrit** ; le « Tester mon site » gratuit reste **non intrusif** (analyse de surface).
- **Démarchage** : le SEO amène l'inbound (légal) ; pour l'outbound, respecter **Bloctel** (B2C) et opt-out B2B.
- **Pages locales** : contenu réellement unique par ville (pas de doorway clonées = pénalité).
- **IA** : si contenu assisté IA, relecture humaine + transparence ; pas de spam de masse.
- **Black-hat** (PBN, achat de liens en masse, cloaking) = **interdit** : gain court, pénalité durable. On joue le long terme.

---

## Sources (datées 2026)
- SEO local / facteurs : [guest-suite](https://www.guest-suite.com/blog/etude-seo-local-facteur), [passion-referencement (Whitespark)](https://www.passion-referencement.fr/etude-whitespark-2026-les-nouveaux-facteurs-de-classement-local-a-connaitre), [clickrank](https://www.clickrank.ai/local-seo-ranking-factors/)
- Marché agences Nantes : SERP [NOIISE](https://www.noiise.com/agences/nantes/creation-site-internet/), [Kagency](https://www.kagency.com/), [Nobilito](https://www.nobilito.fr/digital/sites-web/)
- NIS2 / cyber PME : [copwell](https://copwell.fr/nis2-directive-pme-france-2026/), [mobilecube](https://www.mobilecube.fr/audit-cybersecurite-obligatoire-pour-pme-en-france/), [mytrustpartner](https://courtagecyber.mytrustpartner.fr/blog/assurance-cyber-nis2-obligations-2026.html)
- AI Overviews / GEO : [Searchlab](https://searchlab.nl/en/statistics/ai-overviews-sge-statistics-2026), [Search Engine Journal](https://www.searchenginejournal.com/impact-of-ai-overviews-how-publishers-need-to-adapt/556843/)
- Avis Google conformité : [viewup](https://viewup.fr/blogs/infos/sollicitation-davis-google-en-2026-conformite-sanctions-et-strategies-de-survie), [review-collect](https://www.review-collect.com/fr/blog/guide-ultime-conformite-rgpd-collecte-avis-2025)
- Netlinking éthique : [seosupernova](https://seosupernova.fr/blog/netlinking-local), [made2com](https://www.made2com.fr/blog-communication/netlinking-ethique-strategies-durables-2026/)

---

## 3 ACTIONS À LANCER CETTE SEMAINE
1. **Brancher Google Search Console** (propriété domaine) + soumettre le sitemap Yoast → fige la baseline (positions/requêtes) : sans mesure, pas de domination.
2. **Optimiser le Google Business Profile à fond** (catégories, NAP, areaServed Nantes/St-Herblain/Rezé/St-Nazaire, 10 photos, 5 Q&R) + activer la **demande d'avis systématique conforme** (QR sur Kit Print) — c'est 32 %+16 % du jus local pour 0 € de code.
3. **Coder + publier le 1er pilier `/creation-site-internet-nantes`** avec schema Service + Breadcrumb + maillage, et planifier le pilier cyber `/securite-informatique-pme-nantes` (angle NIS2).

### Hand-off au manager-prospection
- Produire les **scripts inbound** pour les leads venant du test gratuit/audit SEO (suite : audit approfondi → sur-mesure → maintenance MRR).
- Script **NIS2** dédié (échéance 17/10/2026) pour PME/ETI : « audit < 24 mois exigé par votre cyber-assureur ».
- Script **avis** post-livraison (neutre, conforme CNIL) + relance.
- Séquence Telegram/SMS de relance des leads SEO (déjà branchée au CRM).
