<?php
/**
 * Markup for Mihdan: No External Links Referrer Warning page.
 *
 * @since         4.0.0
 * @package       mihdan-no-external-links
 * @subpackage    mihdan-no-external-links/Public/Partials
 * @author        SteamerDevelopment
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
