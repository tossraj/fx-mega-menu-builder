/**
 * Binrik Navigation Menu Designer — Frontend JavaScript
 * Handles hover/click trigger and mobile toggling
 */
( function () {
    'use strict';

    const data = window.binrikNmdFrontend || {};
    const menus = data.menus || {};

    // Helper
    function closest( el, sel ) {
        while ( el && el !== document ) {
            if ( el.matches( sel ) ) return el;
            el = el.parentElement;
        }
        return null;
    }

    // Check if a nav item should open on click (showOnHover=false)
    function isClickTrigger( panelEl ) {
        const li = closest( panelEl, '.binrik-nmd-has-mega-menu' );
        if ( ! li ) return false;
        // Find linked menu config — check data attribute set by PHP or fall back
        return li.dataset.binrikNmdClick === '1';
    }

    function openMenu( li ) {
        closeAll();
        li.classList.add( 'binrik-nmd-open' );
    }

    function closeMenu( li ) {
        li.classList.remove( 'binrik-nmd-open' );
    }

    function closeAll() {
        document.querySelectorAll( '.binrik-nmd-has-mega-menu.binrik-nmd-open' ).forEach( el => {
            el.classList.remove( 'binrik-nmd-open' );
            const btn = el.querySelector( '.binrik-nmd-toggle-btn' );
            if ( btn ) btn.setAttribute( 'aria-expanded', 'false' );
        } );
    }

    document.addEventListener( 'DOMContentLoaded', function () {
        const items = document.querySelectorAll( '.binrik-nmd-has-mega-menu' );

        items.forEach( function ( li ) {
            const panel = li.querySelector( '.binrik-nmd-mega-panel' );
            if ( ! panel ) return;

            const clickMode = isClickTrigger( panel );

            if ( clickMode ) {
                // ── Click mode ──
                const link = li.querySelector( '.binrik-nmd-top-link' );
                const arrow = li.querySelector( '.binrik-nmd-toggle-btn' );
                const toggle = function ( e ) {
                    if ( ! li.classList.contains( 'binrik-nmd-open' ) ) {
                        e.preventDefault();
                        openMenu( li );
                    }
                };
                if ( link ) link.addEventListener( 'click', toggle );
                if ( arrow ) arrow.addEventListener( 'click', toggle );
            } else {
                // ── Hover mode ──
                let hoverTimer;

                li.addEventListener( 'mouseenter', function () {
                    if ( window.matchMedia( '(max-width: 768px)' ).matches ) return;
                    clearTimeout( hoverTimer );
                    openMenu( li );
                } );

                li.addEventListener( 'mouseleave', function () {
                    if ( window.matchMedia( '(max-width: 768px)' ).matches ) return;
                    hoverTimer = setTimeout( function () {
                        closeMenu( li );
                    }, 150 );
                } );

                panel.addEventListener( 'mouseenter', function () {
                    if ( window.matchMedia( '(max-width: 768px)' ).matches ) return;
                    clearTimeout( hoverTimer );
                } );

                panel.addEventListener( 'mouseleave', function () {
                    if ( window.matchMedia( '(max-width: 768px)' ).matches ) return;
                    hoverTimer = setTimeout( function () {
                        closeMenu( li );
                    }, 150 );
                } );
            }
        } );

        // Close on outside click
        document.addEventListener( 'click', function ( e ) {
            if ( ! closest( e.target, '.binrik-nmd-has-mega-menu' ) ) {
                closeAll();
            }
        } );

        // Close on Escape key
        document.addEventListener( 'keydown', function ( e ) {
            if ( e.key === 'Escape' ) closeAll();
        } );

        // ── Mobile toggle ──
        const mediaQuery = window.matchMedia( '(max-width: 768px)' );

        function handleMobile( mq ) {
            if ( mq.matches ) {
                items.forEach( function ( li ) {
                    li.classList.remove( 'binrik-nmd-open' );
                    const arrow = li.querySelector( '.binrik-nmd-toggle-btn' );
                    if ( arrow && ! arrow.dataset.binrikNmdMobileBound ) {
                        arrow.dataset.binrikNmdMobileBound = '1';
                        arrow.addEventListener( 'click', function ( e ) {
                            e.preventDefault();
                            e.stopPropagation();
                            const isOpen = li.classList.contains( 'binrik-nmd-open' );
                            closeAll();
                            if ( ! isOpen ) {
                                li.classList.add( 'binrik-nmd-open' );
                                arrow.setAttribute( 'aria-expanded', 'true' );
                            } else {
                                arrow.setAttribute( 'aria-expanded', 'false' );
                            }
                        } );
                    }
                } );
            }
        }

        handleMobile( mediaQuery );
        mediaQuery.addEventListener( 'change', handleMobile );
    } );
} )();
