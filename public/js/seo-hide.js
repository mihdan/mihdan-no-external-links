( function ( w, d ) {
    Array.from( d.getElementsByClassName( 'waslinkname' ) ).forEach(
        ( el, index, array ) => {
            el.addEventListener(
                'click',
                ( e ) => {
                    let link   = el.getAttribute( 'data-link' );
                    let target = el.getAttribute( 'data-target' );

                    if ( ! link ) {
                        return;
                    }

					link = decodeHTMLEntities( atob ( link ) );

                    if ( target && '_blank' === target ) {
                        w.open( link );
                    } else {
                        d.location.href = link;
                    }
                }
            );
        }
    );

	/**
	 * Decode HTML entities.
	 *
	 * Example:
	 * - &#038; -> &
	 * - &amp; -> &
	 *
	 * @param text String for decoding.
	 * @returns {string}
	 */
	function decodeHTMLEntities( text ) {
		const textArea = document.createElement( 'textarea' );
		textArea.innerHTML = text;

		return textArea.value;
	}
} )( window, document );
