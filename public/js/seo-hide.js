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

					link = decodeHTMLEntities( base64Decode( link ) );

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

	function base64Decode( str ) {
		// Going backwards: from bytestream, to percent-encoding, to original string.
		return decodeURIComponent(atob(str).split('').map(function(c) {
			return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
		}).join(''));
	}
} )( window, document );
