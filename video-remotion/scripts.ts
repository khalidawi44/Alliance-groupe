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
