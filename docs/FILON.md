# FILON COMMERCIAL ACTUEL — canal email à froid

> Décision d'architecte (19/10/2026), fondée sur une recherche marché sourcée + l'audit du tunnel.
> À relire avant tout réglage de la chasse ou du démarchage. Mettre à jour quand les signaux disent de pivoter.

## Contrainte qui fixe le filon
Le canal email ne peut joindre QUE les prospects qui ont déjà un site (c'est de là qu'on extrait `contact@`). Donc on vise le croisement : **(a) a un site** + **(b) forte valeur client / quelque chose à perdre** + **(c) une cause récente qui force à bouger**.

## Segments à pousser (par ordre de potentiel)
1. **E-commerce / marchands avec site transactionnel**, > 10 salariés OU > 2 M€ CA. Angle = **mise en conformité accessibilité (EAA)**. Urgence réelle et datée.
2. **Agences immobilières / gestion locative** (Nantes dense). Angle = refonte / performance / mandats perdus.
3. **Hôtellerie / restauration / tourisme** avec réservation en ligne (littoral 44/56). Angle = réservation directe accessible + perf mobile + SEO local.
4. **Santé privée de groupe** (centres dentaires, vétérinaires, labos). Angle = sécurité / RGPD + refonte. Jamais de scan actif sans mandat.
5. **Avocats** (secondaire, email/courrier only, RIN). Angle = confidentialité / image, ton sobre, **pas** d'urgence réglementaire inventée.

Micro-commerçants (< 10 sal ET < 2 M€) = **exemptés EAA** → ne jamais leur agiter l'amende (argument faux, interdit). Pour eux, bascule angle refonte / perf / sécurité.

## Fenêtre de tir principale (réelle, sourcée)
**EAA / accessibilité numérique** : en vigueur depuis le 28/06/2025, **sans période de transition pour l'e-commerce**. Bilan DGCCRF du 04/09/2026 : plus de la moitié des e-commerçants contrôlés en manquement ; SignalConso ouvert aux signalements depuis déc. 2025 ; amendes 5e classe 7 500 €/infraction (15 000 € en récidive). Référentiel RGAA 4.1 / WCAG 2.2 AA. Argument utilisable **tel quel** (ne rien inventer, ne pas dire « vous allez être contrôlé demain »). Fenêtre « contrôles en cours » se périme ~fin 2027.
Sources : DGCCRF (bilan 04/09/2026) ; décret 2023-931 ; loi 2023-171 ; directive 2019/882.

NIS2 : transposition encore en cours (2026), surtout PME > 50 sal, pas de date ferme à brandir → angle « anticipation » seulement.

## Message (déontologie)
- Vendre l'issue / le risque, pas « un site à 490 € ». CTA unique : « je vous envoie le rapport ? ». Opt-out visible. 75-150 mots.
- Piloter au **taux de réponse**, pas à l'ouverture (Apple MPP fausse l'ouverture).
- L'angle « amende EAA » ne part QU'À des cibles qualifiées en taille (> 10 sal ou > 2 M€). En envoi de masse non qualifié : rester sur l'angle honnête « voici le rapport de votre site » (accessibilité / perf / sécurité), vrai pour tout le monde.

## Lead magnet qui colle au filon
Le rapport « Tester mon site » (score /100 + failles). **À enrichir d'un bloc accessibilité passif** (checks RGAA observables sans intrusion : `alt` manquants, contraste, labels de formulaire, attribut `lang`, structure de titres) → sortir un **score accessibilité /100**. 100 % passif, aucun scan actif.

## Cap 60 jours
- Pousser #1 (e-commerce EAA) en premier. ~40-50 mails/jour segmentés, petits lots (< 50 destinataires convertissent ~2,7× mieux), séquence 3 touches J0 / J4 / J10, depuis `contact@` (délivrabilité 9,5/10).
- **Signaux « c'est le bon filon »** (à suivre dans le CRM / 📈 Tunnel) :
  - taux de réponse ≥ 5 % sur la séquence #1 ;
  - demandes du rapport « Tester mon site » ≥ 3-4 % ;
  - « intéressé » ≥ 2 % et 2-3 RDV qualifiés / 100 envois ;
  - 1re signature visée sous ~150-200 envois ; viser l'escalade audit → sur-mesure → maintenance récurrente (MRR 29/59/99).
- Si après 300-400 envois #1 le taux de réponse < 3 % → basculer l'effort sur #2 (immobilier, refonte).

## Enacté côté code (19-20/10)
- Enrichissement au budget de temps (vide le backlog d'emails, remplit la file d'Hugo).
- Scoreur recentré : priorité aux prospects qui ONT un site perfectible + accroche vraie + **accessibilité faible** (angle EAA).
- Chasse (`ag_cm_bank`) recentrée sur les secteurs du filon (commerce/e-commerce, immobilier, hôtellerie, santé de groupe).
- **FAIT — Lead magnet #1 :** `ag_a11y_checks()` dans `inc/ag-audit-seo.php` → **score d'accessibilité /100** (RGAA, 100 % passif : langue, alt, étiquettes de champs, intitulés de liens, zoom, structure). Affiché dans le rapport « Tester mon site » (2e jauge + détail), rappel EAA, score dans l'email. L'enrich mémorise `a11y_score`, le Scoreur priorise les sites peu accessibles (boucle du filon fermée).
- **Reste à faire (proposé) :** séquences de message #1/#2 côté démarchage (angle honnête par défaut « voici votre rapport » ; angle amende EAA réservé aux cibles qualifiées en taille).
