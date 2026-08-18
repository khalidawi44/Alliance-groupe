import React from 'react';
import {
	AbsoluteFill,
	Img,
	Series,
	useCurrentFrame,
	useVideoConfig,
	interpolate,
	staticFile,
} from 'remotion';

const GOLD = '#D4B45C';
const ORANGE = '#F37A1F';

export type AGScene = {
	headline: string;
	caption?: string;
	bg?: string;
	durationInFrames?: number;
	kind?: 'hook' | 'point' | 'cta';
	ctaLabel?: string;
};

export type AGProps = {
	scenes: AGScene[];
	brand?: string;
};

const resolveSrc = (s?: string): string | null => {
	if (!s) return null;
	return /^https?:\/\//.test(s) ? s : staticFile(s);
};

export const totalFrames = (scenes: AGScene[], fps = 30): number =>
	scenes.reduce((sum, s) => sum + (s.durationInFrames ?? Math.round(fps * 3)), 0);

const SceneView: React.FC<{scene: AGScene}> = ({scene}) => {
	const frame = useCurrentFrame();
	const {durationInFrames} = useVideoConfig();
	const src = resolveSrc(scene.bg);
	const isCta = scene.kind === 'cta';

	// Ken Burns doux, clampé.
	const zoom = interpolate(frame, [0, durationInFrames], [1.06, 1.14], {
		extrapolateLeft: 'clamp',
		extrapolateRight: 'clamp',
	});
	// Fondu entrée/sortie de la scène.
	const opacity = interpolate(
		frame,
		[0, 10, durationInFrames - 10, durationInFrames],
		[0, 1, 1, 0],
		{extrapolateLeft: 'clamp', extrapolateRight: 'clamp'}
	);
	// Entrée du texte DÉTERMINISTE (aucun spring qui saute).
	const inT = interpolate(frame, [0, 14], [0, 1], {
		extrapolateLeft: 'clamp',
		extrapolateRight: 'clamp',
	});
	const ty = interpolate(frame, [0, 20], [34, 0], {
		extrapolateLeft: 'clamp',
		extrapolateRight: 'clamp',
	});

	return (
		<AbsoluteFill style={{opacity, backgroundColor: '#07070a'}}>
			{src && (
				<AbsoluteFill style={{transform: `scale(${zoom})`}}>
					<Img src={src} style={{width: '100%', height: '100%', objectFit: 'cover'}} />
				</AbsoluteFill>
			)}
			{/* Voile lisibilité */}
			<AbsoluteFill
				style={{
					background:
						'linear-gradient(180deg, rgba(5,6,12,.55), rgba(5,6,12,.28) 45%, rgba(5,6,12,.82))',
				}}
			/>
			<AbsoluteFill
				style={{
					justifyContent: 'center',
					alignItems: 'center',
					padding: '0 160px',
					textAlign: 'center',
				}}
			>
				<div
					style={{
						transform: `translateY(${ty}px) translateZ(0)`,
						opacity: inT,
						willChange: 'transform, opacity',
						maxWidth: 1400,
					}}
				>
					{scene.caption && (
						<div
							style={{
								color: '#fff',
								fontSize: 44,
								fontWeight: 600,
								marginBottom: 22,
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
							fontSize: isCta ? 104 : 92,
							lineHeight: 1.06,
							backgroundImage: `linear-gradient(135deg, ${GOLD}, ${ORANGE})`,
							WebkitBackgroundClip: 'text',
							backgroundClip: 'text',
							color: 'transparent',
							WebkitTextFillColor: 'transparent',
							paddingBottom: '0.12em',
						}}
					>
						{scene.headline}
					</div>
					{isCta && scene.ctaLabel && (
						<div
							style={{
								marginTop: 54,
								display: 'inline-block',
								padding: '26px 60px',
								borderRadius: 999,
								background: `linear-gradient(135deg, ${GOLD}, ${ORANGE})`,
								color: '#10100a',
								fontSize: 46,
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

export const AGEngine: React.FC<AGProps> = ({scenes, brand = 'ALLIANCE GROUPE'}) => {
	const {fps} = useVideoConfig();
	const def = Math.round(fps * 3);
	return (
		<AbsoluteFill style={{backgroundColor: '#07070a'}}>
			<Series>
				{scenes.map((sc, i) => (
					<Series.Sequence key={i} durationInFrames={sc.durationInFrames ?? def}>
						<SceneView scene={sc} />
					</Series.Sequence>
				))}
			</Series>
			{/* Filigrane marque en haut */}
			<AbsoluteFill style={{justifyContent: 'flex-start', alignItems: 'center', padding: 56, pointerEvents: 'none'}}>
				<div
					style={{
						color: 'rgba(255,255,255,.92)',
						letterSpacing: 10,
						fontSize: 30,
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
