# Agent commercial autonome — cadrage

> Document de conception. Aucun code ici. À valider par Fabrice avant construction.
> Écrit le 18/09/2026.

## 1. Ce qui existe DÉJÀ (ne pas reconstruire)

| Brique | Fichier | Ce qu'elle fait |
|---|---|---|
| Robot de chasse | `inc/ag-prospection.php` → `ag_run_auto_prospection()` | Trouve les prospects (Google Places), détecte ceux sans vrai site, les range dans le CRM, les attribue à une zone |
| Cron de relance | `ag_relance_cron` | **Compte** les prospects à relancer et **prévient un humain**. Ne contacte personne. |
| Libération auto | idem | Un prospect assigné sans réponse depuis 30 j repart à un autre ambassadeur |
| Devis instantané | `inc/ag-devis-instant.php` | Génère un devis à partir d'un besoin décrit, capture le lead |
| Passerelle SMS | `inc/ag-sms-gateway.php` | `ag_sms_send()` — **codé, bloqué faute de SIM physique** |
| Robot vocal | `inc/ag-voice-webhook.php` | Appels — existe |
| CGV / contrat | `templates/page-contrat-client.php` | Affiche un **modèle** de contrat. Mention : « à faire valider par un avocat » |
| Signature électronique | — | **N'EXISTE PAS** |

**Le chaînon manquant est précis :** le robot trouve et range, puis il s'arrête et attend qu'un humain écrive. Personne n'écrit. Les prospects dorment.

## 2. Ce que l'agent ferait

Une séquence de 4 messages espacés, rédigés un par un par l'IA à partir du réel
(métier, ville, état du site constaté), puis arrêt définitif.

| Étape | Délai | Intention |
|---|---|---|
| 1 | J0 | Un constat précis et vrai sur son site. Proposition de lui montrer une maquette. |
| 2 | J+3 | Relance courte, une question fermée. |
| 3 | J+8 | Apport de valeur, **sans relance commerciale**. |
| 4 | J+15 | Dernière. On annonce qu'on s'arrête. |

Un prospect qui répond **sort de la séquence immédiatement** : un humain prend le relais.

## 3. Les garde-fous (non négociables)

- **Interrupteur maître à OFF par défaut.** Un démarchage ne démarre jamais parce qu'un fichier a été déployé.
- **Plafond quotidien** (défaut 20). Au-delà, ce n'est plus de la prospection.
- **Jamais 2 messages au même prospect en moins de 48 h.**
- **Jamais** un prospect `client`, `refus`, `ne_pas_contacter`, `ignore`, ou qui a déjà répondu.
- **Avocats : email uniquement.** Jamais de SMS ni d'appel à froid (déontologie RIN/CNB).
- **Chaque message** dit qu'il est automatisé et porte un lien d'opposition en **un clic, sans rien à écrire**.
- **Journal complet** : qui, quand, quel message, quel canal. Sans journal, pas de preuve.
- **Aucun chiffre inventé** dans les messages (règle maison du 03/09).

## 4. La signature — ce qui est possible et ce qui ne l'est pas

**L'agent ne signera jamais à la place de quelqu'un.**

- Signer au nom du prospect = faux. Le contrat ne vaut rien, et c'est pénalement qualifié.
- Signer au nom d'Alliance Groupe sans acte humain = Fabrice se réveille engagé sur des contrats qu'il n'a pas voulus.

**Ce qui est faisable, et qui donne le même résultat commercial :**

1. L'agent mène jusqu'au contrat et envoie un **lien de signature** au client.
2. Le client signe **lui-même** : nom saisi, case « lu et approuvé », email vérifié par code.
3. On scelle la preuve : **hash SHA-256 du document + horodatage + IP + user-agent + preuve de vérification email**.
4. Fabrice contresigne d'un clic (ou automatiquement, s'il active explicitement cette règle).

C'est ce que fait un prestataire de signature (eIDAS niveau « simple »), et c'est ce qui tient devant un juge.

## 5. Ce que Fabrice doit savoir avant d'allumer

- **B2B, email** : prospection possible sans consentement préalable (intérêt légitime), si l'objet concerne la fonction de la personne et que l'opposition est immédiate. ✅ C'est le cas.
- **B2C, téléphone** : **Bloctel obligatoire**. Ce module n'appelle personne, précisément pour ça.
- **Transparence** : le destinataire doit pouvoir comprendre qu'il parle à un système automatisé. C'est écrit dans chaque message.
- **Réputation d'expéditeur** : envoyer depuis le domaine principal peut faire tomber la délivrabilité de TOUS les emails (devis, factures, licences). **Recommandation : un sous-domaine dédié**, avec SPF/DKIM/DMARC à lui.
- **Rétractation** : 14 jours pour un contrat conclu à distance avec un non-professionnel.

## 6. Décisions qui n'appartiennent qu'à Fabrice

1. **On allume ou pas ?** Le code peut exister éteint.
2. **Quel domaine d'envoi ?** Principal (risqué) ou sous-domaine dédié (recommandé).
3. **Contresignature auto ou manuelle ?** Par défaut : manuelle.
4. **Le contrat a-t-il été relu par un avocat ?** `page-contrat-client.php` dit lui-même que non.
