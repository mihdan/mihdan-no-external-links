<?php
/**
 * Markup for Mihdan: No External Links Logs page.
 *
 * @since         4.0.0
 * @package       Mihdan_NoExternalLinks
 * @subpackage    Mihdan_NoExternalLinks/Admin/Partials
 * @author        mihdan
 */
?>

<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ) ?></h1>

    <form method="post">
        <?php
        $this->logs_table->prepare_items();
        $this->logs_table->display();
        ?>
    </form>
</div>
