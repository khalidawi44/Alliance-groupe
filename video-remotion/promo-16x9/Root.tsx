import React from 'react';
import {Composition} from 'remotion';
import {AGEngine, totalFrames} from './Engine';
import {promo} from './script';

const FPS = 30;

export const RemotionRoot: React.FC = () => {
	return (
		<Composition
			id="AG-Promo-16x9"
			component={AGEngine}
			fps={FPS}
			width={1920}
			height={1080}
			durationInFrames={totalFrames(promo.scenes, FPS)}
			defaultProps={promo}
		/>
	);
};
