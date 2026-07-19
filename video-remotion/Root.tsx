/**
 * Root Remotion — enregistre les vidéos.
 *
 * Dans le repo `alliance-videos` :
 *  - copie AGShort.tsx, scripts.ts et ce Root.tsx dans src/
 *  - dans src/index.ts : `import {registerRoot} from 'remotion'; import {RemotionRoot} from './Root'; registerRoot(RemotionRoot);`
 *    (ou ajoute juste les <Composition> ci-dessous à ton Root existant)
 *  - copie les images de fond dans public/ (naples-1.jpg, naples-3.jpg, nantes-3.jpg, naples-2.jpg)
 *  - `npx remotion studio`  pour prévisualiser/éditer
 *  - `npx remotion render AG-Recrutement out/recrutement.mp4`  pour exporter
 */
import React from 'react';
import {Composition} from 'remotion';
import {AGShort, totalFrames} from './AGShort';
import {videoRecrutement, videoVente247, videoLuxe, videoNaplesSuite, videoNaplesComplet, videoLongue, videoHeader} from './scripts';

const FPS = 30;
const W = 1080;
const H = 1920;

export const RemotionRoot: React.FC = () => {
	return (
		<>
			<Composition
				id="AG-Header"
				component={AGShort}
				fps={FPS}
				width={W}
				height={H}
				durationInFrames={totalFrames(videoHeader.scenes, FPS)}
				defaultProps={videoHeader}
			/>
			<Composition
				id="AG-Recrutement"
				component={AGShort}
				fps={FPS}
				width={W}
				height={H}
				durationInFrames={totalFrames(videoRecrutement.scenes, FPS)}
				defaultProps={videoRecrutement}
			/>
			<Composition
				id="AG-Vente-247"
				component={AGShort}
				fps={FPS}
				width={W}
				height={H}
				durationInFrames={totalFrames(videoVente247.scenes, FPS)}
				defaultProps={videoVente247}
			/>
			<Composition
				id="AG-Luxe"
				component={AGShort}
				fps={FPS}
				width={W}
				height={H}
				durationInFrames={totalFrames(videoLuxe.scenes, FPS)}
				defaultProps={videoLuxe}
			/>
			<Composition
				id="AG-Naples-Suite"
				component={AGShort}
				fps={FPS}
				width={W}
				height={H}
				durationInFrames={totalFrames(videoNaplesSuite.scenes, FPS)}
				defaultProps={videoNaplesSuite}
			/>
			<Composition
				id="AG-Naples-Complet"
				component={AGShort}
				fps={FPS}
				width={W}
				height={H}
				durationInFrames={totalFrames(videoNaplesComplet.scenes, FPS)}
				defaultProps={videoNaplesComplet}
			/>
			<Composition
				id="AG-Long"
				component={AGShort}
				fps={FPS}
				width={W}
				height={H}
				durationInFrames={totalFrames(videoLongue.scenes, FPS)}
				defaultProps={videoLongue}
			/>
		</>
	);
};
