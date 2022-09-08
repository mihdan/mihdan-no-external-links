<?php
/**
 * Markup for Mihdan: No External Links Masks page.
 *
 * @since         4.2.0
 * @package       mihdan-no-external-links
 * @subpackage    mihdan-no-external-links/Admin/Partials
 * @author        mihdan
 */

?>

<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<form method="post">
		<?php
		$this->masks_table->prepare_items();
		$this->masks_table->display();
		?>
	</form>
</div>
