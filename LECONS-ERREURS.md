# LEÇONS ET ERREURS — à relire avant chaque débogage

But : garder la trace de chaque erreur commise et de ce qui l'a résolue, pour ne pas la refaire.
Lu au besoin par toute nouvelle session. Ajouter en haut à chaque nouvelle leçon (date + titre court).

---

## Méthode générale (à appliquer d'office)

1. Quand un bug ne se reproduit PAS de mon côté mais persiste chez Fabrice : arrêter de deviner, ajouter un DIAGNOSTIC VISIBLE dans l'outil (un compteur à l'écran). Une capture de ce compteur vaut dix allers-retours.
2. Vérifier d'ABORD la version déployée (badge) avant de chercher un bug de logique. Souvent le bug est déjà corrigé mais pas reçu (cache).
3. Reproduire l'ÉTAT EXACT du terrain, pas un état proche. Un test qui passe sur un état voisin ne prouve rien.
4. Un test automatisé qui « passe » peut être un faux positif de test. Vérifier que le montage de test est fidèle (mock vraiment actif, entrées réelles).
5. Toujours savoir QUELLE branche le site lit avant de parler de déploiement.

---

## 2026-11-04 — Anti-robot (DataDome) : prévenir AVANT, et User-Agent = ajouter pas remplacer

ERREUR 7 : envoyé Fabrice chasser sur BlaBlaCar (cible à anti-robot DataDome) sans prévenir du risque de blocage.
ERREUR 8 : la consigne du programme disait « AJOUTER » l'identifiant au User-Agent ; j'ai fait une règle Burp qui REMPLACE tout le User-Agent par le seul identifiant → plus aucune signature de navigateur → DataDome l'a pris pour un robot et a bloqué son IP.
RÉSOLU PAR : (1) détecteur anti-robot dans `scan()` (DataDome/Cloudflare-challenge/Akamai `_abck`/PerimeterX/Imperva + texte « blocked ») qui AVERTIT dès la 1re étape, avec la marche à suivre (navigateur Burp only, vitesse humaine, changer d'IP si bloqué) ; (2) étape ⓪ « Anti-robot ? » en tête de la route bug bounty ; (3) `FULLUA` = vrai User-Agent de navigateur + identifiant AJOUTÉ à la fin (jamais en remplacement). Faux positif évité : un Cloudflare bénin (`cf-ray` seul, sans challenge) ne déclenche pas.
LEÇONS : (a) toujours vérifier l'anti-robot d'une cible AVANT d'envoyer quelqu'un dessus ; (b) un User-Agent custom seul = signal de robot = blocage, il faut l'AJOUTER à un vrai UA ; (c) une cible à anti-robot (DataDome…) n'est pas pour un débutant → orienter vers un programme sans anti-robot ou l'entraînement PortSwigger Web Security Academy (gratuit, sans blocage).

## 2026-11-04 — L'étape « fait » disparaît au refresh (saga du mot de passe)

ERREUR 1 : dire « refais le test » plusieurs fois sans preuve.
RÉSOLU PAR : ajout d'un compteur visible dans la bannière de reprise (nombre d'étapes « fait » remises + mémoire « X local / Y serveur »). Sa capture a montré la vraie panne en une fois.
LEÇON : face à un bug non reproductible, rendre l'état visible dans l'outil au lieu de deviner.

ERREUR 2 : chercher un bug de logique alors que Fabrice tournait sur une vieille version.
CAUSE : l'assistant est un fichier .html statique servi à une URL fixe. Le navigateur (ou un cache/CDN) garde l'ancienne copie même après « SYNC GitHub » et même avec Ctrl+F5.
RÉSOLU PAR : cache-bust `?v=<filemtime>` ajouté à tous les liens « Ouvrir l'assistant » (helper `ag_defi_url()` dans ag-pentest-bridge.php). Dès que le fichier change, l'URL change, le navigateur retélécharge.
LEÇON : pour un .html statique, toujours un cache-bust. Et vérifier le badge AVANT de déboguer la logique.

ERREUR 3 (la vraie cause) : la reprise jetait une étape pourtant enregistrée côté serveur.
CAUSE : dans `loadSiteState`, la fusion local/serveur était gardée par `srvTs >= locTs`. Or au refresh, le local est réécrit avec un timestamp toujours plus récent que le serveur, donc la session serveur (plus complète) était ignorée, puis le ré-enregistrement local retombait à une seule étape.
RÉSOLU PAR : remplacer le choix « local OU serveur » par une UNION local + serveur par commande (jamais de garde par timestamp). On garde toute étape présente d'un côté ou de l'autre, préférence à celle qui a du texte.
LEÇON : pour fusionner deux sources d'un même état, ne jamais choisir l'une par sa date. Toujours faire l'union par une clé stable (ici la commande), sinon on perd des données.

ERREUR 4 : un test automatisé passait alors que le bug existait.
CAUSE : le mock de `agApi` était posé via `addInitScript` (avant le chargement), donc écrasé par la déclaration `function agApi` de la page. Le vrai agApi échouait en `file://` et renvoyait « non enregistré ».
RÉSOLU PAR : poser le mock APRÈS le `load` de la page (via `page.evaluate`), pour qu'il gagne sur la déclaration.
LEÇON : vérifier que le montage de test est réellement actif. Un « ça passe » peut être un artefact de test.

ERREUR 5 : le correctif poussé sur la branche de travail n'arrivait pas sur le site.
CAUSE : le site lit `main` (ag-github-sync.php), et `main` et la branche de travail ont des histoires git NON liées (pas d'ancêtre commun), donc un merge ff-only est impossible.
RÉSOLU PAR : repartir de `origin/main` (reset dur), appliquer seulement les fichiers modifiés (`git checkout <branche> -- <fichiers>`), commiter, pousser sur `main`. Puis revenir sur la branche de travail.
LEÇON : pousser la branche ne suffit pas. Vérifier la branche lue par le site et y porter les fichiers, même sans ancêtre commun.

ERREUR 6 : quiz de connaissances, 2 réponses fausses (confusion « collabore avec » / « fait partie de l'équipe »).
RÉSOLU PAR : vérifier à la source avant de répondre, ne pas répondre de mémoire sur un point précis.
LEÇON : sur une question factuelle, puiser dans la source, pas dans l'impression.
