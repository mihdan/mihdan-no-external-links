( function ( w, d ) {
    Array.from( d.getElementsByClassName( 'waslinkname' ) ).forEach(
        ( el, index, array ) => {
            el.addEventListener(
                'click',
                ( e ) => {
                    const link   = el.getAttribute( 'data-link' );
                    const target = el.getAttribute( 'target' );

                    if ( ! link ) {
                        return;
                    }

                    if ( target && '_blank' === target ) {
                        w.open( atob( link ) );
                    } else {
                        d.location.href = atob( link );
                    }
                }
            );
        }
    );
} )( window, document );