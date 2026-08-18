import type {AGProps} from './Engine';

// Promo Alliance Groupe 16:9 (~16 s à 30 fps). Web + Sécurité + IA.
export const promo: AGProps = {
	brand: 'ALLIANCE GROUPE',
	scenes: [
		{kind: 'hook',  bg: 'naples-1.jpg',   caption: 'De Naples à Nantes',    headline: 'Je crée & sécurise votre site web.', durationInFrames: 100},
		{kind: 'point', bg: 'naples-night.jpg', caption: "Propulsé par l'IA",    headline: 'Devis en 30 secondes. Livré en 5 jours.', durationInFrames: 100},
		{kind: 'point', bg: 'nantes-3.jpg',   caption: '🛡️ Sécurité incluse',   headline: 'Un site rapide, référencé, protégé.', durationInFrames: 100},
		{kind: 'point', bg: 'naples-1.jpg',   caption: 'Dès 490 €',             headline: 'Un seul interlocuteur, du conseil à la livraison.', durationInFrames: 100},
		{kind: 'cta',   headline: 'Testez votre site — gratuit.', ctaLabel: 'alliancegroupe-inc.com', durationInFrames: 80},
	],
};
