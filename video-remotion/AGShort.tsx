/**
 * AGShort — moteur de "shorts" verticaux Alliance Groupe (Remotion 4.x).
 *
 * Une SEULE composition, pilotée par des données : tu décris des "scènes"
 * (texte + image de fond) et la vidéo se génère, à ta marque (or/orange),
 * format 1080x1920, prête pour TikTok / Snapchat / Reels.
 *
 * Dépend uniquement du coeur de Remotion (aucun package en plus).
 * À copier dans le repo `alliance-videos` (dossier src/), puis l'enregistrer
 * dans le Root (voir Root.tsx) et lancer `npx remotion studio` ou render.
 *
 * Les images de fond vont dans `public/` du repo alliance-videos
 * (copie depuis alliance-groupe-theme/assets/images/cities/...), ou une URL https.
 */
import React from 'react';
import {
	AbsoluteFill,
	Img,
	OffthreadVideo,
	Series,
	Audio,
	useCurrentFrame,
	useVideoConfig,
	interpolate,
	spring,
	staticFile,
} from 'remotion';

const GOLD = '#D4B45C';
const ORANGE = '#F37A1F';

export type AGScene = {
	headline: string;          // grande phrase (hook / point fort)
	caption?: string;          // petite ligne au-dessus
	bg?: string;               // 'naples-1.jpg' (public/) ou 'https://...'
	durationInFrames?: number; // défaut : 3s
	kind?: 'hook' | 'point' | 'cta';
	ctaLabel?: string;         // bouton final (si kind === 'cta')
};

export type AGShortProps = {
	scenes: AGScene[];
	brand?: string; // filigrane marque
	music?: string; // 'music.mp3' (public/) ou URL https — optionnel
};

const resolveSrc = (s?: string): string | null => {
	if (!s) return null;
	return /^https?:\/\//.test(s) ? s : staticFile(s);
};

/** Le fond est-il une vidéo (clip IA Picsart) plutôt qu'une image ? */
const isVideo = (s?: string): boolean => !!s && /\.(mp4|webm|mov|m4v)(\?|$)/i.test(s);

/** Calcule la durée totale (utile pour la prop durationInFrames du <Composition>). */
export const totalFrames = (scenes: AGScene[], fps = 30): number =>
	scenes.reduce((sum, s) => sum + (s.durationInFrames ?? Math.round(fps * 3)), 0);

const SceneView: React.FC<{scene: AGScene}> = ({scene}) => {
	const frame = useCurrentFrame();
	const {fps, durationInFrames} = useVideoConfig();
	const src = resolveSrc(scene.bg);
	const isCta = scene.kind === 'cta';

	// Ken Burns (léger zoom continu)
	const zoom = interpolate(frame, [0, durationInFrames], [1.08, 1.18]);
	// Fondu entrée/sortie
	const opacity = interpolate(
		frame,
		[0, 8, durationInFrames - 8, durationInFrames],
		[0, 1, 1, 0],
		{extrapolateLeft: 'clamp', extrapolateRight: 'clamp'}
	);
	// Texte qui monte (spring)
	const intro = spring({frame, fps, config: {damping: 200}});
	const ty = interpolate(intro, [0, 1], [60, 0]);

	return (
		<AbsoluteFill style={{opacity, backgroundColor: '#07070a'}}>
			{src && (
				<AbsoluteFill style={{transform: `scale(${zoom})`}}>
					{isVideo(scene.bg) ? (
						<OffthreadVideo src={src} muted style={{width: '100%', height: '100%', objectFit: 'cover'}} />
					) : (
						<Img src={src} style={{width: '100%', height: '100%', objectFit: 'cover'}} />
					)}
				</AbsoluteFill>
			)}
			{/* Voile pour la lisibilité du texte */}
			<AbsoluteFill
				style={{
					background:
						'linear-gradient(180deg, rgba(5,6,12,.55), rgba(5,6,12,.30) 45%, rgba(5,6,12,.88))',
				}}
			/>
			<AbsoluteFill
				style={{
					justifyContent: isCta ? 'center' : 'flex-end',
					alignItems: 'center',
					padding: '0 90px 240px',
					textAlign: 'center',
				}}
			>
				<div style={{transform: `translateY(${ty}px)`, opacity: intro}}>
					{scene.caption && (
						<div
							style={{
								color: '#fff',
								fontSize: 40,
								fontWeight: 600,
								marginBottom: 18,
								textShadow: '0 2px 18px rgba(0,0,0,.85)',
								fontFamily: 'Inter, system-ui, sans-serif',
							}}
						>
							{scene.caption}
						</div>
					)}
					<div
						style={{
							fontFamily: 'Georgia, "Playfair Display", serif',
							fontWeight: 800,
							fontSize: isCta ? 96 : 84,
							lineHeight: 1.05,
							backgroundImage: `linear-gradient(135deg, ${GOLD}, ${ORANGE})`,
							WebkitBackgroundClip: 'text',
							backgroundClip: 'text',
							color: 'transparent',
							WebkitTextFillColor: 'transparent',
							filter: 'drop-shadow(0 4px 22px rgba(0,0,0,.7))',
						}}
					>
						{scene.headline}
					</div>
					{isCta && scene.ctaLabel && (
						<div
							style={{
								marginTop: 48,
								display: 'inline-block',
								padding: '26px 54px',
								borderRadius: 999,
								background: `linear-gradient(135deg, ${GOLD}, ${ORANGE})`,
								color: '#10100a',
								fontSize: 44,
								fontWeight: 800,
								fontFamily: 'Inter, system-ui, sans-serif',
							}}
						>
							{scene.ctaLabel}
						</div>
					)}
				</div>
			</AbsoluteFill>
		</AbsoluteFill>
	);
};

const MusicTrack: React.FC<{src: string}> = ({src}) => {
	const {fps, durationInFrames} = useVideoConfig();
	const frame = useCurrentFrame();
	const fade = Math.round(fps * 1.2); // fondu d'1,2 s en entrée et en sortie
	// Musique DOUCE : volume max bas (0.18) + fondu début/fin.
	const volume = interpolate(
		frame,
		[0, fade, durationInFrames - fade, durationInFrames],
		[0, 0.18, 0.18, 0],
		{extrapolateLeft: 'clamp', extrapolateRight: 'clamp'}
	);
	return <Audio src={src} volume={volume} />;
};

export const AGShort: React.FC<AGShortProps> = ({scenes, brand = 'ALLIANCE GROUPE', music}) => {
	const {fps} = useVideoConfig();
	const def = Math.round(fps * 3);
	const musicSrc = resolveSrc(music);

	return (
		<AbsoluteFill style={{backgroundColor: '#07070a'}}>
			{musicSrc && <MusicTrack src={musicSrc} />}
			<Series>
				{scenes.map((sc, i) => (
					<Series.Sequence key={i} durationInFrames={sc.durationInFrames ?? def}>
						<SceneView scene={sc} />
					</Series.Sequence>
				))}
			</Series>
			{/* Filigrane marque en haut */}
			<AbsoluteFill style={{justifyContent: 'flex-start', alignItems: 'center', padding: 70, pointerEvents: 'none'}}>
				<div
					style={{
						color: 'rgba(255,255,255,.9)',
						letterSpacing: 8,
						fontSize: 28,
						fontWeight: 700,
						fontFamily: 'Inter, system-ui, sans-serif',
						textShadow: '0 2px 12px rgba(0,0,0,.85)',
					}}
				>
					{brand}
				</div>
			</AbsoluteFill>
		</AbsoluteFill>
	);
};
