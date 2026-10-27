/**
 * NDTV Player front end.
 *
 * - Streams with only HLS (the live channels) are attached with hls.js, which is
 *   loaded on demand. Safari plays HLS natively.
 * - Video tiles marked with data-ndtv-preview play a short muted clip on hover,
 *   like the tiles on ndtv.com.
 */
( function () {
	const settings = window.ndtvPlayer || {};
	let hlsLoader = null;

	function loadHls() {
		if ( window.Hls ) {
			return Promise.resolve( window.Hls );
		}
		if ( ! hlsLoader ) {
			hlsLoader = new Promise( ( resolve, reject ) => {
				const script = document.createElement( 'script' );
				script.src = settings.hlsUrl;
				script.onload = () => resolve( window.Hls );
				script.onerror = reject;
				document.head.appendChild( script );
			} );
		}
		return hlsLoader;
	}

	function attachHls( video ) {
		const src = video.dataset.hls;
		if ( video.canPlayType( 'application/vnd.apple.mpegurl' ) ) {
			video.src = src;
			return Promise.resolve();
		}
		return loadHls().then( ( Hls ) => {
			if ( ! Hls || ! Hls.isSupported() ) {
				return;
			}
			const hls = new Hls( { capLevelToPlayerSize: true } );
			hls.loadSource( src );
			hls.attachMedia( video );
		} );
	}

	document.querySelectorAll( 'video[data-hls]:not([src])' ).forEach( ( video ) => {
		const start = () =>
			attachHls( video )
				.then( () => video.play().catch( () => {} ) )
				.catch( () => {} );

		if ( video.autoplay ) {
			start();
		} else {
			video.addEventListener( 'play', start, { once: true } );
		}
	} );

	if ( ! window.matchMedia( '(hover: hover)' ).matches ) {
		return;
	}

	document.querySelectorAll( '[data-ndtv-preview]' ).forEach( ( tile ) => {
		let clip = null;

		tile.addEventListener( 'mouseenter', () => {
			clip = document.createElement( 'video' );
			clip.className = 'ndtv-preview-clip';
			clip.src = tile.dataset.ndtvPreview;
			clip.muted = true;
			clip.loop = true;
			clip.playsInline = true;
			clip.setAttribute( 'aria-hidden', 'true' );
			tile.appendChild( clip );
			clip.play().catch( () => {} );
		} );

		tile.addEventListener( 'mouseleave', () => {
			if ( clip ) {
				clip.remove();
				clip = null;
			}
		} );
	} );
} )();
