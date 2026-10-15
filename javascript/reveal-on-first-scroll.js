/**
 * Scroll-reveal animation with visible content as the initial state.
 *
 * The common pattern (add a "hidden" class at page load, remove it when an
 * IntersectionObserver fires) fails anywhere the observer never fires: background tabs,
 * crawler and screenshot renders, print, embedded panes. The content stays invisible.
 *
 * This version: the finished, visible state is the default. The hidden state is armed
 * only on the visitor's first real scroll, and only for elements still well below the
 * fold. Anything already near the screen simply stays visible.
 *
 * CSS:
 *   .reveal-armed { opacity: 0; transform: translateY(24px); }
 *   .reveal-armed.reveal-in { opacity: 1; transform: none; transition: opacity .6s, transform .6s; }
 *   @media print { .reveal-armed { opacity: 1 !important; transform: none !important; } }
 *
 * Mark elements with data-reveal.
 */
( function () {
	'use strict';
	var armed = false;

	function arm() {
		if ( armed ) {
			return;
		}
		armed = true;
		window.removeEventListener( 'scroll', arm );

		if ( window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
			return;
		}

		if ( ! ( 'IntersectionObserver' in window ) ) {
			return; // Everything stays visible.
		}
		var limit = window.innerHeight * 1.5;
		var io = new IntersectionObserver(
			function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( entry.isIntersecting ) {
						entry.target.classList.add( 'reveal-in' );
						io.unobserve( entry.target );
						// Drop the stagger delay once the entrance is done so hover effects are instant.
						setTimeout( function () {
							entry.target.classList.remove( 'reveal-armed', 'reveal-in' );
						}, 800 );
					}
				} );
			},
			{ rootMargin: '0px 0px -10% 0px' }
		);

		document.querySelectorAll( '[data-reveal]' ).forEach( function ( el ) {
			if ( el.getBoundingClientRect().top > limit ) {
				el.classList.add( 'reveal-armed' );
				io.observe( el );
			}
		} );
	}

	window.addEventListener( 'scroll', arm, { passive: true } );
}() );
