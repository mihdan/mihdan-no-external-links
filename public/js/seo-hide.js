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

	function basePropertyOf( object ) {
		return function ( key ) {
			return object == null ? undefined : object[ key ];
		};
	}

	var reEscapedHtml = /&(?:amp|lt|gt|quot|#39);/g,
		reHasEscapedHtml = RegExp( reEscapedHtml.source ),
		htmlUnescapes = {
			'&amp;': '&',
			'&lt;': '<',
			'&gt;': '>',
			'&quot;': '"',
			'&#39;': "'"
		},
		unescapeHtmlChar = basePropertyOf( htmlUnescapes );

	function decodeHTMLEntities( text ) {
		return ( text && reHasEscapedHtml.test( text ) )
			? text.replace( reEscapedHtml, unescapeHtmlChar )
			: text;
	}
} )( window, document );
