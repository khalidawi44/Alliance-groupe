Depose ici les 2 fichiers GSAP pour un hebergement 100% local (optionnel) :

  - gsap.min.js
  - ScrollTrigger.min.js

(Version GSAP 3.13, dossier "minified" du telechargement GSAP.)

Des qu'ils sont presents ici (et > 1 Ko), le theme bascule automatiquement
du CDN vers ces fichiers locaux (functions.php : enqueue ag-gsap / ag-gsap-st).
Les noms doivent etre EXACTEMENT ceux ci-dessus (sensible a la casse).

Sans ces fichiers, le theme utilise le CDN jsDelivr (fonctionne aussi).
