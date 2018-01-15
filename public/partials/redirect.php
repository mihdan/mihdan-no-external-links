<?php
/**
 * Markup for WP No External Links Redirect page.
 *
 * @since         4.0.0
 * @package       WP_NoExternalLinks
 * @subpackage    WP_NoExternalLinks/Public/Partials
 * @author        SteamerDevelopment
 */
?>

<html>
    <head>
        <title><?php _e( 'Redirecting...', $this->plugin_name ); ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <meta name="robots" content="noindex,nofollow"/>
        <?php if ( $url ) {
            echo '<meta http-equiv="refresh" content="';
            if ( $this->options->redirect_time ) {
                echo $this->options->redirect_time;
            } else {
                echo '0';
            }
            echo '; url=' . $url . '" />';
        }
        ?>
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
