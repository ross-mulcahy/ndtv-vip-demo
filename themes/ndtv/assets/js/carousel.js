/**
 * Prev/next buttons for horizontally scrolling video rails (.ndtv-carousel).
 * The rails scroll natively without this; the buttons are an enhancement.
 */
( function () {
	document.querySelectorAll( '.ndtv-carousel .wp-block-post-template' ).forEach( function ( track ) {
		var nav = document.createElement( 'div' );
		nav.className = 'ndtv-carousel__nav';

		[ [ 'prev', -1, 'Previous' ], [ 'next', 1, 'Next' ] ].forEach( function ( item ) {
			var button = document.createElement( 'button' );
			button.type = 'button';
			button.className = 'ndtv-carousel__btn is-' + item[ 0 ];
			button.setAttribute( 'aria-label', item[ 2 ] );
			button.addEventListener( 'click', function () {
				track.scrollBy( { left: item[ 1 ] * track.clientWidth * 0.8, behavior: 'smooth' } );
			} );
			nav.appendChild( button );
		} );

		var update = function () {
			nav.children[ 0 ].disabled = track.scrollLeft <= 2;
			nav.children[ 1 ].disabled = track.scrollLeft + track.clientWidth >= track.scrollWidth - 2;
		};
		track.addEventListener( 'scroll', update, { passive: true } );
		window.addEventListener( 'resize', update );
		track.parentNode.appendChild( nav );
		update();
	} );
} )();
