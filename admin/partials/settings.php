<?php
/**
 * Markup for Mihdan: No External Links Settings page.
 *
 * @since         4.0.0
 * @package       Mihdan_NoExternalLinks
 * @subpackage    Mihdan_NoExternalLinks/Admin/Partials
 * @author        mihdan
 */
?>

<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<?php settings_errors(); ?>

	<?php $active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : ''; ?>

	<h2 class="nav-tab-wrapper">
		<a href="?page=<?php echo $this->plugin_name; ?>-settings" class="nav-tab<?php echo '' == $active_tab ? ' nav-tab-active' : ''; ?>">
			General
		</a>
		<a href="?page=<?php echo $this->plugin_name; ?>-settings&tab=links" class="nav-tab<?php echo 'links' == $active_tab ? ' nav-tab-active' : ''; ?>">
			Links
		</a>
		<a href="?page=<?php echo $this->plugin_name; ?>-settings&tab=include_exclude" class="nav-tab<?php echo 'include_exclude' == $active_tab ? ' nav-tab-active' : ''; ?>">
			Include / Exclude
		</a>
		<a href="?page=<?php echo $this->plugin_name; ?>-settings&tab=advanced" class="nav-tab<?php echo 'advanced' == $active_tab ? ' nav-tab-active' : ''; ?>">
			Advanced
		</a>
	</h2>

	<form action="<?php echo admin_url( 'options.php' ); ?>" method="post">
		<?php
		if ( 'links' == $active_tab ) {
			settings_fields( $this->plugin_name . '-settings-links' );
			do_settings_sections( $this->plugin_name . '-settings-links' );
		} elseif ( 'include_exclude' == $active_tab ) {
			settings_fields( $this->plugin_name . '-settings-include-exclude' );
			do_settings_sections( $this->plugin_name . '-settings-include-exclude' );
		} elseif ( 'advanced' == $active_tab ) {
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
