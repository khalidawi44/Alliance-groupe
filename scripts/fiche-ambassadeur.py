#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
fiche-ambassadeur.py — la fiche de formation A4 recto/verso d'un ambassadeur.

Une seule feuille a donner en main propre le jour du recrutement : le parcours
de demarrage, ce qu'il vend, ce qu'il gagne, ce qu'il n'a pas le droit de faire.
Chaque etape porte un QR vers LA page exacte a ouvrir — pas la page d'accueil,
la page precise, pour qu'il n'ait jamais a chercher.

Les chiffres viennent du code (inc/ag-offres.php, inc/ag-ambassadeurs.php,
inc/ag-zones.php). Si une offre bouge, ils bougent ici AUSSI : sinon la fiche
promet au nouveau quelque chose que le site ne tient pas.

Usage :
    python3 scripts/fiche-ambassadeur.py                    # fiche universelle
    python3 scripts/fiche-ambassadeur.py --prenom "Sarah"   # nominative
    python3 scripts/fiche-ambassadeur.py --ref ABC123       # + liens perso

Sortie : docs/fiches/fiche-ambassadeur[-prenom].pdf
"""

import argparse, base64, io, os, subprocess, sys, tempfile
import qrcode
from qrcode.constants import ERROR_CORRECT_M

SITE = "https://alliancegroupe-inc.com"
OR   = "#B8952F"   # or, assombri pour rester lisible a l'encre sur papier blanc
ENCRE = "#141414"

CHROME = "/opt/pw-browsers/chromium-1194/chrome-linux/chrome"


def qr_data_uri(url: str, px: int = 300) -> str:
    """PNG encode en data-URI. Genere en local : aucun appel reseau, donc la
    fiche se reconstruit meme hors ligne (le Kit Print, lui, passe par
    api.qrserver.com — ici on n'en depend pas)."""
    q = qrcode.QRCode(version=None, error_correction=ERROR_CORRECT_M, box_size=10, border=2)
    q.add_data(url)
    q.make(fit=True)
    img = q.make_image(fill_color="black", back_color="white").resize((px, px))
    buf = io.BytesIO()
    img.save(buf, format="PNG")
    return "data:image/png;base64," + base64.b64encode(buf.getvalue()).decode()


def court(url: str) -> str:
    return url.replace("https://", "").replace("alliancegroupe-inc.com", "…-inc.com")


def construire(prenom: str, ref: str) -> str:
    lien_vente  = f"{SITE}/sites-express" + (f"?ref={ref}" if ref else "")
    lien_parrain = f"{SITE}/ambassadeurs" + (f"?parrain={ref}" if ref else "")

    qr = {
        "espace":    qr_data_uri(f"{SITE}/espace-ambassadeur/"),
        "programme": qr_data_uri(f"{SITE}/programme-ambassadeur/"),
        "contrat":   qr_data_uri(f"{SITE}/contrat-ambassadeur/"),
        "vente":     qr_data_uri(lien_vente),
        "studio":    qr_data_uri(f"{SITE}/studio/"),
        "maquette":  qr_data_uri(f"{SITE}/refais-mon-site/"),
        "classement":qr_data_uri(f"{SITE}/classement/"),
        "recruter":  qr_data_uri(lien_parrain),
    }

    titre = f"Fiche ambassadeur — {prenom}" if prenom else "Fiche ambassadeur"
    perso = ("<p class=\"perso\">Tes deux QR du verso (<b>Vendre</b> et <b>Recruter</b>) portent "
             f"déjà ton code <b>{ref}</b> : toute vente passée par eux t'est attribuée.</p>") if ref else ""

    return f"""<!doctype html><html lang="fr"><head><meta charset="utf-8"><title>{titre}</title>
<style>
  @page {{ size:A4; margin:0; }}
  *{{box-sizing:border-box;margin:0;padding:0}}
  body{{font-family:"Helvetica Neue",Helvetica,Arial,sans-serif;color:{ENCRE};
       font-size:9.3pt;line-height:1.42;-webkit-print-color-adjust:exact;print-color-adjust:exact}}
  .page{{width:210mm;height:297mm;padding:11mm 12mm 9mm;page-break-after:always;
        display:flex;flex-direction:column}}
  .page:last-child{{page-break-after:auto}}

  header{{display:flex;justify-content:space-between;align-items:flex-end;
         border-bottom:2.5pt solid {OR};padding-bottom:3mm;margin-bottom:5mm}}
  .marque{{font-size:15pt;font-weight:800;letter-spacing:.14em}}
  .marque span{{color:{OR}}}
  .sous{{font-size:8pt;letter-spacing:.09em;text-transform:uppercase;color:#6a6a6a}}
  .nom{{text-align:right;font-size:10.5pt;font-weight:700}}
  .nom small{{display:block;font-size:7.5pt;font-weight:400;color:#6a6a6a;letter-spacing:.05em}}

  h2{{font-size:11.5pt;font-weight:800;letter-spacing:.02em;margin:0 0 2.5mm;
     display:flex;align-items:center;gap:2.5mm}}
  h2::before{{content:"";width:3.5mm;height:3.5mm;background:{OR};
             clip-path:polygon(1mm 0,100% 0,100% calc(100% - 1mm),calc(100% - 1mm) 100%,0 100%,0 1mm)}}
  .chap{{margin-bottom:5mm}}
  .intro{{color:#4a4a4a;margin-bottom:3mm}}

  /* Les 4 etapes du demarrage */
  .etapes{{display:grid;grid-template-columns:repeat(4,1fr);gap:2.5mm}}
  .et{{border:.7pt solid #d8d8d8;border-top:2.2pt solid {OR};padding:2.5mm 2.5mm 3mm}}
  .et > b:first-child{{display:block;font-size:8pt;color:{OR};letter-spacing:.1em;margin-bottom:1mm}}
  .et h3{{font-size:9.2pt;margin-bottom:1.2mm}}
  .et p{{font-size:8.1pt;color:#555;line-height:1.35}}

  /* Blocs a QR */
  .qrs{{display:grid;gap:3mm}}
  .qrs.trois{{grid-template-columns:repeat(3,1fr)}}
  .qrs.quatre{{grid-template-columns:repeat(4,1fr)}}
  .card{{border:.7pt solid #d8d8d8;padding:3mm;display:flex;gap:3mm;align-items:flex-start}}
  .card img{{width:19mm;height:19mm;flex:0 0 19mm}}
  .card .txt h3{{font-size:9.4pt;margin-bottom:1mm}}
  .card .txt p{{font-size:8.1pt;color:#555;line-height:1.35}}
  .card .txt .u{{font-size:7pt;color:{OR};margin-top:1.2mm;word-break:break-all}}
  .card.large img{{width:26mm;height:26mm;flex:0 0 26mm}}

  table{{width:100%;border-collapse:collapse;font-size:8.6pt}}
  th,td{{border:.6pt solid #d8d8d8;padding:1.6mm 2mm;text-align:left}}
  td+td+td{{white-space:nowrap}}
  th{{background:#f4f1e8;font-size:7.6pt;letter-spacing:.07em;text-transform:uppercase}}
  td.g{{font-weight:800;color:{OR};text-align:right;white-space:nowrap}}

  .regles{{display:grid;grid-template-columns:1fr 1fr;gap:3mm}}
  .box{{border:.7pt solid #d8d8d8;padding:3mm}}
  .box.rouge{{border-color:#c0392b;background:#fdf4f3}}
  .box h3{{font-size:9.4pt;margin-bottom:1.8mm}}
  .box.rouge h3{{color:#c0392b}}
  .box ul{{list-style:none}}
  .box li{{font-size:8.2pt;line-height:1.4;padding-left:4mm;position:relative;margin-bottom:1.3mm}}
  .box li::before{{content:"▸";position:absolute;left:0;color:{OR}}}
  .box.rouge li::before{{content:"✕";color:#c0392b;font-size:7.5pt}}

  .semaine{{display:grid;grid-template-columns:1fr 1fr .85fr;gap:3mm}}
  .semaine .col ul{{list-style:none}}
  .semaine .col li{{font-size:8.3pt;line-height:1.35;padding-left:5.5mm;position:relative;margin-bottom:2.2mm}}
  .semaine .col li::before{{content:"";position:absolute;left:0;top:.4mm;
        width:3.4mm;height:3.4mm;border:.8pt solid #9a9a9a}}
  .reperes{{border:.7pt solid #d8d8d8;border-left:2.2pt solid {OR};padding:2.5mm 3mm}}
  .reperes h3{{font-size:9pt;margin-bottom:2mm}}
  .reperes p{{font-size:8pt;color:#555;margin-bottom:2.6mm}}
  .reperes .l{{display:block;border-bottom:.6pt solid #bbb;height:3.6mm}}
  .perso{{background:#f4f1e8;border-left:2.2pt solid {OR};padding:2.2mm 3mm;font-size:8.3pt;margin-bottom:4mm}}
  footer{{margin-top:auto;border-top:.7pt solid #d8d8d8;padding-top:2.5mm;
         font-size:7.4pt;color:#6a6a6a;display:flex;justify-content:space-between}}
</style></head><body>

<!-- ═════════════ RECTO ═════════════ -->
<div class="page">
  <header>
    <div><div class="marque">ALLIANCE <span>GROUPE</span></div>
         <div class="sous">Fiche ambassadeur · le démarrage</div></div>
    <div class="nom">{prenom or "&nbsp;"}<small>Recto — à faire une seule fois</small></div>
  </header>

  {perso}

  <div class="chap">
    <h2>Commence ici</h2>
    <p class="intro">Tout se passe dans <b>ton espace</b>. Scanne, connecte-toi, et un assistant
    te guide étape par étape. Tu n'as rien d'autre à installer.</p>
    <div class="card large">
      <img src="{qr['espace']}" alt="">
      <div class="txt">
        <h3>Ton espace ambassadeur</h3>
        <p>C'est ton tableau de bord : l'assistant de démarrage, tes prospects, tes ventes,
        tes commissions, et les <b>liens personnels</b> à partager (vente et recrutement).
        Mets-le en favori sur ton téléphone.</p>
        <p style="margin-top:1.5mm"><b>À savoir :</b> ton compte est d'abord
        <b>en attente de validation</b> (vérification d'identité + contrat). Tu peux tout
        préparer pendant ce temps ; tu pourras déclarer des ventes dès qu'il est validé —
        tu reçois un email.</p>
        <div class="u">{court(SITE + "/espace-ambassadeur/")}</div>
      </div>
    </div>
  </div>

  <div class="chap">
    <h2>L'assistant te fait faire 4 choses</h2>
    <div class="etapes">
      <div class="et"><b>ÉTAPE 1</b><h3>Rejoins le groupe Telegram</h3>
        <p>Obligatoire. C'est là que passent les annonces, l'entraide et les prospects.
        Reviens ensuite cliquer « J'ai rejoint ».</p></div>
      <div class="et"><b>ÉTAPE 2</b><h3>Vérifie ton numéro</h3>
        <p>Un numéro = un ambassadeur. Pas de multi-comptes. C'est ça qui débloque ta zone.</p></div>
      <div class="et"><b>ÉTAPE 3</b><h3>Choisis ta zone</h3>
        <p><b>Un seul département</b>, en général le tien. Seul dessus = tous les prospects
        pour toi ; à plusieurs = partage 50/50.</p></div>
      <div class="et"><b>ÉTAPE 4</b><h3>Tu peux prospecter</h3>
        <p>Les prospects de ta zone arrivent <b>automatiquement</b> dans ton espace.
        Tu n'as plus qu'à les contacter.</p></div>
    </div>
  </div>

  <div class="chap">
    <h2>Puis forme-toi, et signe</h2>
    <div class="qrs trois">
      <div class="card"><img src="{qr['programme']}" alt="">
        <div class="txt"><h3>Le Programme</h3>
          <p>La formation complète : comment vendre un site, comment recruter ton équipe,
          quoi poster sur TikTok / Insta. Lis-le en entier une fois.</p>
          <div class="u">{court(SITE + "/programme-ambassadeur/")}</div></div></div>
      <div class="card"><img src="{qr['contrat']}" alt="">
        <div class="txt"><h3>Ton contrat</h3>
          <p>Ce qui te lie à Alliance Groupe : ton statut, ta commission, ce que la maison
          s'engage à faire. Lis-le avant de signer.</p>
          <div class="u">{court(SITE + "/contrat-ambassadeur/")}</div></div></div>
      <div class="card"><img src="{qr['classement']}" alt="">
        <div class="txt"><h3>Le classement</h3>
          <p>Jour, mois, général. Sers-t'en comme d'un compteur : il dit où tu en es
          par rapport aux autres.</p>
          <div class="u">{court(SITE + "/classement/")}</div></div></div>
    </div>
  </div>

  <div class="chap">
    <h2>Ta première semaine</h2>
    <p class="intro">Coche au fur et à mesure. Sept cases, et tu es lancée.</p>
    <div class="semaine">
      <div class="col"><ul>
        <li>Compte créé, les 4 étapes de l'assistant terminées</li>
        <li>Groupe Telegram rejoint — présente-toi en deux lignes</li>
        <li>Programme lu en entier, contrat lu et signé</li>
        <li>Lien de vente copié depuis mon espace et mis dans mes notes de téléphone</li>
      </ul></div>
      <div class="col"><ul>
        <li>Premier « Refais son site » fait sur le site d'un pro que je connais</li>
        <li>Une vidéo créée au Studio et postée une fois</li>
        <li>Dix pros contactés dans ma zone (message, pas appel, pour commencer)</li>
      </ul></div>
      <div class="reperes">
        <h3>Mes repères</h3>
        <p>Ma zone <span class="l"></span></p>
        <p>Mon lien de vente <span class="l"></span></p>
        <p>Compte validé le <span class="l"></span></p>
        <p>Ma 1<sup>re</sup> vente le <span class="l"></span></p>
      </div>
    </div>
  </div>

  <footer><span>Alliance Groupe · advise.alliance.group@gmail.com</span>
          <span>Recto 1/2 — garde cette feuille près de toi</span></footer>
</div>

<!-- ═════════════ VERSO ═════════════ -->
<div class="page">
  <header>
    <div><div class="marque">ALLIANCE <span>GROUPE</span></div>
         <div class="sous">Fiche ambassadeur · le quotidien</div></div>
    <div class="nom">{prenom or "&nbsp;"}<small>Verso — tous les jours</small></div>
  </header>

  <div class="chap">
    <h2>Ce que tu vends, et ce que tu gagnes</h2>
    <p class="intro">Trois formules, un seul métier : donner à un pro un site dont il n'a pas honte.
    Ta commission est de <b>10 % de chaque vente</b>.</p>
    <table>
      <tr><th>Formule</th><th>Pour qui</th><th>Prix</th><th style="text-align:right">Ta commission</th></tr>
      <tr><td><b>Essentiel</b> — site 1 page, livré en 5 jours</td>
          <td>L'artisan, l'indépendant qui n'a rien</td><td>490 €</td><td class="g">49 €</td></tr>
      <tr><td><b>Pro</b> — jusqu'à 6 pages, blog, prise de RDV, livré en 8 jours</td>
          <td>Le commerce, le cabinet qui veut grandir</td><td>890 €</td><td class="g">89 €</td></tr>
      <tr><td><b>Boutique</b> — e-commerce, 30 produits, paiement en ligne, 12 jours</td>
          <td>Celui qui veut vendre en ligne</td><td>1 490 €</td><td class="g">149 €</td></tr>
    </table>
    <p class="intro" style="margin-top:2.5mm"><b>Comment tu es payée :</b> tu déclares la vente
    dans ton espace (« Déclarer une vente »). Elle est vérifiée, puis la commission est validée.
    Une vente passée par ton lien personnel t'est attribuée automatiquement.</p>
  </div>

  <div class="chap">
    <h2>Tes quatre outils</h2>
    <div class="qrs quatre">
      <div class="card" style="flex-direction:column;gap:2mm"><img src="{qr['vente']}" alt="">
        <div class="txt"><h3>Vendre</h3>
          <p>La page des 3 formules. <b>Partage CE lien</b> — pas l'accueil : c'est lui qui
          t'attribue la vente.</p></div></div>
      <div class="card" style="flex-direction:column;gap:2mm"><img src="{qr['maquette']}" alt="">
        <div class="txt"><h3>Refais son site</h3>
          <p>Ton meilleur ouvre-porte : colle l'adresse du site d'un prospect, l'IA en fait
          une version moderne en 60 s. Montre-lui.</p></div></div>
      <div class="card" style="flex-direction:column;gap:2mm"><img src="{qr['studio']}" alt="">
        <div class="txt"><h3>Le Studio</h3>
          <p>Fabrique une vidéo ou un visuel à ta marque, avec ton lien dedans, et poste
          sur TikTok / Insta / Snap.</p></div></div>
      <div class="card" style="flex-direction:column;gap:2mm"><img src="{qr['recruter']}" alt="">
        <div class="txt"><h3>Recruter</h3>
          <p>Tu peux faire entrer d'autres ambassadeurs. Partage ce lien : ils arrivent
          rattachés à toi.</p></div></div>
    </div>
  </div>

  <div class="chap">
    <h2>Les règles</h2>
    <div class="regles">
      <div class="box"><h3>Ce que tu dois tenir</h3><ul>
        <li><b>Reste active.</b> Sans activité pendant <b>30 jours</b>, ta zone est libérée
        et rendue à quelqu'un d'autre. Tu es prévenue avant.</li>
        <li><b>Déclare chaque vente</b> dans ton espace, même celle que tu as signée à la main.</li>
        <li><b>Un prospect assigné à quelqu'un d'autre ne se contacte pas.</b> Un seul
        ambassadeur par prospect : c'est ce qui évite qu'un pro reçoive trois appels.</li>
        <li><b>Ne promets que ce qui est écrit</b> sur la page de la formule. Rien de plus.</li>
      </ul></div>
      <div class="box rouge"><h3>Ce qui est interdit</h3><ul>
        <li><b>Les avocats : email ou courrier uniquement.</b> Jamais d'appel ni de SMS à froid —
        c'est leur déontologie, et une faute te ferme la profession entière.</li>
        <li><b>Pas de second compte</b>, même au nom d'un proche. Un numéro = un ambassadeur.</li>
        <li><b>Pas de faux avis</b>, pas de fausse urgence, pas de prix inventé.</li>
        <li><b>On ne touche jamais au vrai site d'un prospect.</b> La maquette IA est une
        simulation — dis-le lui.</li>
      </ul></div>
    </div>
  </div>

  <div class="chap">
    <h2>Si tu ne devais retenir qu'une chose</h2>
    <p class="intro">Scanne le site d'un pro avec « Refais son site », montre-lui le résultat
    sur ton téléphone, et tais-toi. C'est l'écran qui vend, pas le discours.
    Une question, un doute, un prospect difficile : le groupe Telegram, tout de suite.</p>
  </div>

  <footer><span>Alliance Groupe · advise.alliance.group@gmail.com</span>
          <span>Verso 2/2 · zone supplémentaire : 49 €</span></footer>
</div>
</body></html>"""


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--prenom", default="", help="Prénom imprimé en tête de fiche")
    ap.add_argument("--ref", default="", help="Code de parrainage : personnalise les QR Vendre/Recruter")
    ap.add_argument("--sortie", default="", help="Chemin du PDF")
    a = ap.parse_args()

    html = construire(a.prenom.strip(), a.ref.strip())
    racine = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
    dossier = os.path.join(racine, "docs", "fiches")
    os.makedirs(dossier, exist_ok=True)
    suffixe = "-" + a.prenom.strip().lower().replace(" ", "-") if a.prenom.strip() else ""
    pdf = a.sortie or os.path.join(dossier, f"fiche-ambassadeur{suffixe}.pdf")

    with tempfile.NamedTemporaryFile("w", suffix=".html", delete=False, encoding="utf-8") as f:
        f.write(html)
        tmp = f.name

    subprocess.run([CHROME, "--headless", "--disable-gpu", "--no-sandbox",
                    "--no-pdf-header-footer", f"--print-to-pdf={pdf}", "file://" + tmp],
                   check=True, capture_output=True)
    os.unlink(tmp)
    print(f"✅ {pdf}  ({os.path.getsize(pdf) // 1024} Ko)")


if __name__ == "__main__":
    main()
