<?php
/**
 * Markup for WP No External Links Settings page.
 *
 * @since         4.0.0
 * @package       WP_NoExternalLinks
 * @subpackage    WP_NoExternalLinks/Admin/Partials
 * @author        SteamerDevelopment
 */
?>

<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ) ?></h1>

    <?php settings_errors() ?>

    <?php $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : '' ?>

    <h2 class="nav-tab-wrapper">
        <a href="?page=<?php echo $this->plugin_name ?>-settings"
           class="nav-tab<?php echo $active_tab == '' ? ' nav-tab-active' : '' ?>">
            General
        </a>
        <a href="?page=<?php echo $this->plugin_name ?>-settings&tab=links"
           class="nav-tab<?php echo $active_tab == 'links' ? ' nav-tab-active' : '' ?>">
            Links
        </a>
        <a href="?page=<?php echo $this->plugin_name ?>-settings&tab=include_exclude"
           class="nav-tab<?php echo $active_tab == 'include_exclude' ? ' nav-tab-active' : '' ?>">
            Include / Exclude
        </a>
        <a href="?page=<?php echo $this->plugin_name ?>-settings&tab=advanced"
           class="nav-tab<?php echo $active_tab == 'advanced' ? ' nav-tab-active' : '' ?>">
            Advanced
        </a>
    </h2>

    <form action="options.php" method="post">
        <?php
        if ( $active_tab == 'links' ) {
            settings_fields( $this->plugin_name . '-settings-links' );
            do_settings_sections( $this->plugin_name . '-settings-links' );
        } elseif ( $active_tab == 'include_exclude' ) {
            settings_fields( $this->plugin_name . '-settings-include-exclude' );
            do_settings_sections( $this->plugin_name . '-settings-include-exclude' );
        } elseif ( $active_tab == 'advanced' ) {
            settings_fields( $this->plugin_name . '-settings-advanced' );
            do_settings_sections( $this->plugin_name . '-settings-advanced' );
        } else {
            settings_fields( $this->plugin_name . '-settings' );
            do_settings_sections( $this->plugin_name . '-settings' );
        }

        submit_button();
        ?>
    </form>
</div>