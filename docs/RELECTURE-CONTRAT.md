# Dossier de relecture — Contrat client Alliance Groupe

> À remettre à un professionnel du droit. But : faire relire le **modèle de contrat**
> que le système envoie automatiquement, avant d'armer la signature autonome.
>
> ⚠️ Ce document est une **aide à la préparation**, pas un avis juridique. Seul un
> avocat peut valider. Une fois sa relecture faite, Fabrice coche lui-même le verrou
> (voir §1) — personne d'autre ne peut affirmer à sa place qu'un juriste a lu le texte.

---

## 1. Le verrou, et pourquoi il existe

Toute la chaîne commerciale tourne seule (repérage → démarchage → réponse → négociation),
**sauf une porte** : l'envoi d'un contrat *à signer* et la contre-signature automatique.

- Réglage technique : option `ag_sign_contrat_relu` (fonction `ag_sign_pret()`).
- Où l'activer : **WordPress → ✍️ Contrats signés**, case « le modèle de contrat a été relu
  par un professionnel du droit ».
- Tant qu'elle est décochée : le « Directeur » arme tout le reste mais **laisse la signature
  en attente**. La chaîne va jusqu'à la négociation et s'arrête à la porte de la signature.

**Ne cochez cette case qu'après la relecture effective.** C'est une affirmation
contractuelle, pas une préférence.

---

## 2. Le contrat, article par article

Le contrat est généré par `ag_sign_redige_contrat()` (`inc/ag-signature.php`). En-tête :
identité légale complète (raison sociale, dirigeant, forme, SIREN/SIRET, TVA, RCS, adresse).

| Article | Contenu | À vérifier par l'avocat |
|---|---|---|
| **1 — Objet** | Description de la prestation (pack choisi + détails). | L'objet est-il assez précis pour être opposable ? |
| **2 — Prix et modalités de paiement** | Montant total + modalités (acompte / échéances selon le pack). | Mentions TVA, acompte, échéancier conformes ? |
| **3 — Délai d'exécution** (si renseigné) | Délai de livraison. | Sanction du retard à prévoir ? |
| **4 — Droit de rétractation** | 14 jours pour un **consommateur** ; **exclu** pour un professionnel agissant dans le cadre de son activité. | Rédaction conforme au Code de la consommation (art. L221-18 s.) ? Cas du pro « hors champ habituel » (≤ 5 salariés) à couvrir ? |
| **5 — Conditions générales** | Renvoi aux CGV publiées sur `/contrat-client`, que le client déclare avoir lues. | **Les CGV de `/contrat-client` doivent être relues aussi** (voir §4). |
| **6 — Signature électronique** | Saisie du nom + acceptation expresse + vérification email par **code à usage unique** ; empreinte numérique du document, date, heure et IP conservées comme preuve. | La valeur probante décrite est-elle suffisante (eIDAS / art. 1366-1367 C. civ.) ? Faut-il un tiers de confiance / horodatage qualifié pour les montants élevés ? |

La signature manuscrite affichée est une **marque de la maison**, pas la preuve juridique :
la valeur vient de l'article 6 (OTP + empreinte + horodatage + IP).

---

## 3. Les garde-fous déjà codés (à confirmer comme suffisants)

Le système ne signe **jamais** à la place du client, et n'envoie un contrat que si **tout**
est réuni. Points à valider avec l'avocat :

1. **Signature par le client lui-même**, avec preuve scellée. Le robot mène au contrat et
   envoie le lien ; le client signe. (Signer pour autrui = faux.)
2. **Contrat auto uniquement sur intention d'achat explicite et certaine** (« c'est d'accord »,
   « je prends »). Une question de prix ne déclenche rien.
3. **Anti-écart de prix** : si le client cite un montant qui ne correspond pas au tarif du pack
   (tolérance 10 %), aucun contrat ne part seul — un humain regarde.
4. **Pas de promesse de résultat ni de chiffre inventé** dans les messages (guards `ag_promesses_interdites()`).
5. **Avocats démarchés** : email/courrier uniquement, ton confraternel (déontologie RIN/CNB).

Question à l'avocat : ces garde-fous couvrent-ils le risque de **vente à distance conclue par
un automate** (consentement, information précontractuelle, preuve du parcours) ?

---

## 4. Ce qui doit être relu EN MÊME TEMPS que le contrat

- **Les CGV `/contrat-client`** — le contrat y renvoie (article 5). Elles doivent être à jour et cohérentes.
- **Les mentions légales du site** — doivent correspondre à l'identité du contrat.
- **La politique de confidentialité / RGPD** — le contrat conserve nom, email, IP à titre de preuve :
  base légale, durée de conservation, information du signataire.

---

## 5. Après la relecture

1. Corriger le texte si l'avocat le demande : le modèle est dans `inc/ag-signature.php`
   (`ag_sign_redige_contrat()`) et les CGV dans la page `/contrat-client`.
2. **Seulement alors**, cocher `ag_sign_contrat_relu` (écran ✍️ Contrats signés).
3. Relancer le « Directeur » → il armera enfin la signature automatique.

Tant que cette relecture n'est pas faite, la chaîne reste volontairement bloquée à la
porte de la signature. C'est le seul verrou que le système ne force pas — par sécurité.
