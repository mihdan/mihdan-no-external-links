<?php
/**
 * Markup for Mihdan: No External Links Redirect page.
 *
 * @since         4.0.0
 * @package       Mihdan_NoExternalLinks
 * @subpackage    Mihdan_NoExternalLinks/Public/Partials
 * @author        mihdan
 */
?>
<!doctype html>
<html>
    <head>
	    <meta charset="UTF-8">
	    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	    <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <meta name="robots" content="noindex,nofollow"/>
	    <title><?php _e( 'Redirecting...', $this->plugin_name ); ?></title>
    </head>
    <body style="margin:0;">
        <div align="center" style="margin-top: 15em;">
            <?php
            if ( $this->options->redirect_message && $url ) {
                echo str_replace( '%linkurl%', $url, $this->options->redirect_message );
            } elseif ( $url ) {
                echo __( 'You were going to the redirect link, but something did not work properly.<br>
                          Please, click ', $this->plugin_name ) .
                         '<a href="' . $url . '">' . __( 'HERE ', $this->plugin_name ) . '</a>' .
                         __( ' to go to ', $this->plugin_name ) . $url . __( ' manually. ', $this->plugin_name );
            } else {
                _e( 'Sorry, no url redirect specified. Can\'t complete request.', $this->plugin_name );
            }
            ?>
        </div>
    </body>
</html>
<?php
die();
