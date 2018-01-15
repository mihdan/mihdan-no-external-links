<?php
/**
 * Markup for WP No External Links Referrer Warning page.
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
        <meta http-equiv="refresh" content="5; url=<?php echo get_home_url(); ?>"/>
    </head>
    <body style="margin:0;">
        <div align="center" style="margin-top: 15em;">
            <?php echo
                __(
                    'You have been redirected through this website from a suspicious source.
                     We prevented it and you are going to be redirected to our ',
                    $this->plugin_name
                ) .
                '<a href="' . get_home_url() . '">' .
                __( 'safe web site.', $this->plugin_name ) .
                '</a>';
            ?>
        </div>
    </body>
</html>

<?php

die();
