# Fiche Google « Alliance Groupe » — débloquer la validation

> Situation : la fiche existe, mais Google affiche **« Pas d'autres méthodes de validation »**
> (toutes les tentatives épuisées). Cause la plus probable : **nom non conforme**
> (« Alliance Groupe — Agence Web & IA ») + trop de tentatives rapprochées.

## À FAIRE dans l'ordre

1. **NE PAS recréer de fiche** (un doublon = suspension des deux).
2. **NE PAS relancer la validation** tant que la fiche n'est pas nettoyée (ça reverrouille).
3. **Corriger la fiche** (Éditer la fiche) :
   - **Nom** → `Alliance Groupe` (sans « Agence Web & IA » : descriptifs interdits).
   - **Catégorie** : Concepteur de sites Web (OK).
   - **Adresse** : si pas de bureau public → **zone desservie** Nantes / Loire-Atlantique, sans adresse perso affichée.
   - **Téléphone** `07 44 82 95 16` + **site** `https://alliancegroupe-inc.com` (identiques au site).
4. **Préparer les preuves** (photo/PDF) : SIRET / avis SIRENE INSEE (ou Kbis), une facture pro,
   le site en ligne, l'email pro `contact@alliancegroupe-inc.com`.
5. **Contacter l'assistance Google Business Profile** : bouton « contactez l'assistance »
   → demander une **vérification manuelle** (chat / appel / vidéo). Coller le message ci-dessous.
6. **Patienter** : une fois la fiche propre, Google **repropose souvent une méthode** après
   quelques jours. Ne pas forcer entre-temps.

---

## Message à coller à l'assistance Google (FR)

> Bonjour,
>
> Je suis le propriétaire de l'établissement **Alliance Groupe** (concepteur de sites Web,
> Nantes / Loire-Atlantique, site https://alliancegroupe-inc.com, tél. 07 44 82 95 16).
>
> Ma fiche d'établissement affiche « Échec de la validation » puis « Pas d'autres méthodes
> de validation ». Je n'ai plus aucune option de validation proposée, alors que mon entreprise
> est bien réelle et en activité.
>
> J'ai corrigé la fiche pour qu'elle soit conforme (nom exact « Alliance Groupe », catégorie
> « Concepteur de sites Web », zone desservie Nantes). Je peux fournir tous les justificatifs :
> immatriculation (SIRET), factures, site web en ligne, email professionnel.
>
> Pouvez-vous **rouvrir la validation** ou procéder à une **vérification manuelle / par vidéo**
> de mon établissement ? Je reste disponible pour un appel ou une visioconférence.
>
> Merci d'avance pour votre aide.
> Fabrizio — Alliance Groupe

---

## Message court (chat / formulaire avec peu de place)

> Fiche « Alliance Groupe » (concepteur de sites Web, Nantes) bloquée : « Pas d'autres
> méthodes de validation ». Entreprise bien réelle, fiche corrigée et conforme. Merci de
> rouvrir la validation ou de faire une vérification manuelle / vidéo — je fournis SIRET,
> factures et site en ligne.

---

## En parallèle (déjà en place sur le site)
- Page **/avis-clients** (module `inc/ag-temoignages.php`) : formulaire d'avis + affichage
  + note moyenne + schema Review/AggregateRating → recueille de vrais témoignages tout de suite.
- Dès que la fiche Google est validée : récupérer le lien « Demander des avis » → le coller
  dans wp-admin **⭐ Avis Google** (module `inc/ag-avis.php`) → QR + relances automatiques.

> ⚠️ Note honnête : pour le type « LocalBusiness/Organization », Google **n'affiche pas**
> toujours les étoiles issues d'avis sur votre propre site (avis « auto-déclarés »).
> La page /avis-clients sert surtout à **rassurer et convertir** les visiteurs et à
> **collecter** des témoignages ; les étoiles publiques dans Google viendront de la **fiche**.
