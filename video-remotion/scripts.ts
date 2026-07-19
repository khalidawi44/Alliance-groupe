/**
 * Scripts vidéo (données) — 1 SEUL clip de fond par vidéo (économie de crédits).
 * Le même clip sert pour toutes les scènes ; seul le TEXTE change.
 * La scène finale (CTA) n'a pas de fond (dégradé sombre + texte or).
 *
 * Durée : chaque vidéo = 15 s (450 images à 30 fps).
 *
 * Clips à générer dans Picsart (1080x1920) puis déposer dans `public/` :
 *   - naples.mp4         (baie de Naples, golden hour)
 *   - city-night.mp4     (skyline ville la nuit)
 *   - ferrari-night.mp4  (Ferrari rouge, rue mouillée la nuit)
 * Tu peux mettre une image .jpg à la place si tu veux tester sans crédits.
 */
import type {AGShortProps} from './AGShort';

/* 1) RECRUTEMENT — fond : naples.mp4 — 95+95+95+95+70 = 450 (15 s) */
export const videoRecrutement: AGShortProps = {
	brand: 'ALLIANCE GROUPE',
	music: 'Napoli_con_bizonnio2.m4a', // copier ce fichier dans alliance-videos/public/
	scenes: [
		{kind: 'hook',  bg: 'naples.mp4', headline: 'On m’a dit que mon quartier ne réussit pas.', durationInFrames: 95},
		{kind: 'point', bg: 'naples.mp4', caption: 'Programme Racines 🌱', headline: 'Le talent est partout. Les opportunités, non.', durationInFrames: 95},
		{kind: 'point', bg: 'naples.mp4', caption: 'Sans être expert', headline: 'Vends des sites. Touche 10 %.', durationInFrames: 95},
		{kind: 'point', bg: 'naples.mp4', caption: 'Primes chaque mois 🏆', headline: 'Plus tu vends, plus tu montes au classement.', durationInFrames: 95},
		{kind: 'cta',   headline: 'Rejoins l’équipe.', ctaLabel: 'alliancegroupe-inc.com', durationInFrames: 70},
	],
};

/* 2) VENTE 24/7 — fond : city-night.mp4 — 120+120+130+80 = 450 (15 s) */
export const videoVente247: AGShortProps = {
	brand: 'ALLIANCE GROUPE',
	scenes: [
		{kind: 'hook',  bg: 'city-night.mp4', headline: '3h du matin. Tout le monde dort.', durationInFrames: 120},
		{kind: 'point', bg: 'city-night.mp4', caption: 'Une commande tombe 🛒', headline: 'Sauf ton site.', durationInFrames: 120},
		{kind: 'point', bg: 'city-night.mp4', caption: 'Dès 490 € · sans rendez-vous', headline: 'Un site pro, livré en quelques jours.', durationInFrames: 130},
		{kind: 'cta',   headline: 'Lance ton site.', ctaLabel: 'Sites Express →', durationInFrames: 80},
	],
};

/* 3) LUXE / FLEX — fond : ferrari-night.mp4 — 120+120+120+90 = 450 (15 s) */
export const videoLuxe: AGShortProps = {
	brand: 'ALLIANCE GROUPE',
	scenes: [
		{kind: 'hook',  bg: 'ferrari-night.mp4', headline: 'Lui il livre. Moi je vends des sites.', durationInFrames: 120},
		{kind: 'point', bg: 'ferrari-night.mp4', caption: '10 % par vente', headline: 'Depuis mon téléphone.', durationInFrames: 120},
		{kind: 'point', bg: 'ferrari-night.mp4', caption: 'On te donne tout', headline: 'Pas besoin d’être expert.', durationInFrames: 120},
		{kind: 'cta',   headline: 'Rejoins l’équipe.', ctaLabel: 'alliancegroupe-inc.com', durationInFrames: 90},
	],
};

/* 4) NAPLES — LA SUITE (Partie 2 du recrutement, fond naples.mp4)
 * Prolonge l’histoire : des racines napolitaines à l’indépendance par le web.
 * 95+95+95+95+90+80 = 550 images (~18 s à 30 fps). */
export const videoNaplesSuite: AGShortProps = {
	brand: 'ALLIANCE GROUPE',
	music: 'Napoli_con_bizonnio2.m4a',
	scenes: [
		{kind: 'hook',  bg: 'naples.mp4', headline: 'Naples m’a tout appris : partir de rien, tout construire.', durationInFrames: 95},
		{kind: 'point', bg: 'naples.mp4', caption: 'De la rue au digital', headline: 'Aujourd’hui, je vends des sites web.', durationInFrames: 95},
		{kind: 'point', bg: 'naples.mp4', caption: '10 % à vie', headline: 'Chaque client me rapporte, chaque mois.', durationInFrames: 95},
		{kind: 'point', bg: 'naples.mp4', caption: 'Mon équipe grandit 🤝', headline: 'Je recrute, et je touche aussi sur leurs ventes.', durationInFrames: 95},
		{kind: 'point', bg: 'naples.mp4', caption: 'Sans diplôme', headline: 'On te donne les outils. Toi, tu oses.', durationInFrames: 90},
		{kind: 'cta',   headline: 'À ton tour d’écrire ton histoire.', ctaLabel: 'Deviens ambassadeur →', durationInFrames: 80},
	],
};

/* NAPLES — VIDÉO COMPLÈTE : Partie 1 enchaînée directement sur la Partie 2.
 * On retire le CTA de fin de la Partie 1 pour un récit continu jusqu’au CTA final. */
export const videoNaplesComplet: AGShortProps = {
	brand: 'ALLIANCE GROUPE',
	music: 'Napoli_con_bizonnio2.m4a',
	scenes: [...videoRecrutement.scenes.slice(0, -1), ...videoNaplesSuite.scenes],
};

/* HEADER / HÉROS — court (~10 s), à afficher en en-tête du site OU en réseaux.
 * Message double : Naples→Nantes, Sécurité + Création. Fonds = images que tu as
 * déjà dans public/ (naples-1.jpg, nantes-3.jpg). 80+80+80+60 = 300 images (10 s).
 * Rendu :  npx remotion render AG-Header out/header.mp4
 * (pour un fond de site en boucle : version muette, MP4 léger.) */
export const videoHeader: AGShortProps = {
	brand: 'ALLIANCE GROUPE',
	scenes: [
		{kind: 'hook',  bg: 'naples-1.jpg', caption: 'De Naples à Nantes', headline: 'Deux métiers, un seul interlocuteur.', durationInFrames: 80},
		{kind: 'point', bg: 'naples-1.jpg', caption: '🛡️ Sécurité', headline: 'Je sécurise votre site contre le piratage.', durationInFrames: 80},
		{kind: 'point', bg: 'nantes-3.jpg', caption: '✨ Création & SEO', headline: 'Je crée des sites pros, référencés sur Google.', durationInFrames: 80},
		{kind: 'cta',   headline: 'Testez votre site — gratuit.', ctaLabel: 'alliancegroupe-inc.com', durationInFrames: 60},
	],
};

/* VIDÉO LONGUE — Naples 1 + Naples 2 enchaînées, avec musique (fond naples.mp4
 * uniquement, qui existe). CTA de la Partie 1 retiré -> un seul CTA final. ~31 s. */
export const videoLongue: AGShortProps = {
	brand: 'ALLIANCE GROUPE',
	music: 'Napoli_con_bizonnio2.m4a',
	scenes: [
		...videoRecrutement.scenes.slice(0, -1),
		...videoNaplesSuite.scenes,
	],
};
