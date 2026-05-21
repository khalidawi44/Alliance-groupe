/**
 * Scripts vidéo (données) — change juste le texte/les images ici pour créer
 * de nouvelles vidéos. Chaque objet = une vidéo. Format 1080x1920, 30 fps.
 *
 * Images de fond : copie-les dans `public/` du repo alliance-videos
 * (depuis alliance-groupe-theme/assets/images/cities/) ou mets une URL https.
 * b-roll IA conseillé : remplace les .jpg par tes clips/rendus luxe (Naples, Ferrari).
 */
import type {AGShortProps} from './AGShort';

/* 1) RECRUTEMENT — du quartier à la réussite + 10% + classement */
export const videoRecrutement: AGShortProps = {
	brand: 'ALLIANCE GROUPE',
	scenes: [
		{kind: 'hook',  bg: 'naples-1.jpg', headline: 'On m’a dit que mon quartier ne réussit pas.', durationInFrames: 75},
		{kind: 'point', bg: 'naples-3.jpg', caption: 'Programme Racines 🌱', headline: 'Le talent est partout. Les opportunités, non.', durationInFrames: 75},
		{kind: 'point', bg: 'nantes-3.jpg', caption: 'Sans être expert', headline: 'Vends des sites. Touche 10 %.', durationInFrames: 75},
		{kind: 'point', headline: 'Plus tu vends, plus tu montes au classement.', caption: 'Primes chaque mois 🏆', durationInFrames: 75},
		{kind: 'cta',   headline: 'Rejoins l’équipe.', ctaLabel: 'alliancegroupe-inc.com', durationInFrames: 60},
	],
};

/* 2) VENTE — ton site travaille 24/7 (e-commerce) */
export const videoVente247: AGShortProps = {
	brand: 'ALLIANCE GROUPE',
	scenes: [
		{kind: 'hook',  bg: 'nantes-3.jpg', headline: '3h du matin. Tout le monde dort.', durationInFrames: 70},
		{kind: 'point', bg: 'nantes-3.jpg', headline: 'Sauf ton site.', caption: 'Une commande tombe 🛒', durationInFrames: 70},
		{kind: 'point', bg: 'naples-2.jpg', headline: 'Un site pro, livré en quelques jours.', caption: 'Dès 490 € · sans rendez-vous', durationInFrames: 80},
		{kind: 'cta',   headline: 'Lance ton site.', ctaLabel: 'Sites Express →', durationInFrames: 60},
	],
};

/* 3) AVANT / APRÈS — crédibilité (montre un vieux site puis le tien) */
export const videoAvantApres: AGShortProps = {
	brand: 'ALLIANCE GROUPE',
	scenes: [
		{kind: 'hook',  headline: 'Ce commerce perdait des clients à cause de ça.', caption: 'Avant', durationInFrames: 75},
		{kind: 'point', headline: 'Le même, refait par nous.', caption: 'Après', durationInFrames: 75},
		{kind: 'point', headline: 'Design premium. Optimisé mobile. SEO.', durationInFrames: 70},
		{kind: 'cta',   headline: 'On refait le tien ?', ctaLabel: 'alliancegroupe-inc.com', durationInFrames: 60},
	],
};
