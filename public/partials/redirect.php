<?php
/**
 * Markup for Mihdan: No External Links Redirect page.
 *
 * @since         4.0.0
 * @package       mihdan-no-external-links
 * @subpackage    mihdan-no-external-links/Public/Partials
 * @author        mihdan
 */

// phpcs:disable WordPress.WP.I18n.NonSingularStringLiteralDomain
?>
	<!doctype html>
	<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta
			name="viewport"
			content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
		<meta http-equiv="X-UA-Compatible" content="ie=edge">
		<meta name="robots" content="noindex,nofollow"/>
		<title><?php esc_html_e( 'Redirecting...', $this->plugin_name ); ?></title>
	</head>
	<body style="margin:0;">
	<div style="text-align:center; margin-top: 15em;">
		<?php
		$url = $url ?? null;
		// phpcs:ignore Generic.Commenting.DocComment.MissingShort
		if ( $this->options->redirect_message && $url ) {
			$allowed_html = [
				'a' => [
					'href'   => true,
					'title'  => true,
					'rel'    => true,
					'target' => true,
				],
			];
			echo wp_kses(
				str_replace(
					'%linkurl%',
					esc_url( $url ),
					$this->options->redirect_message
				),
				$allowed_html
			);
		} elseif ( $url ) {
			$message = __( 'You were going to the redirect link, but something did not work properly.<br> Please, click ', $this->plugin_name );
			echo (
				esc_html( $message ) .
				'<a href="' . esc_url( $url ) . '">' . esc_html__( 'HERE ', $this->plugin_name ) . '</a>' .
				esc_html__( ' to go to ', $this->plugin_name ) . esc_url( $url ) . esc_html__( ' manually. ', $this->plugin_name )
			);
		} else {
			esc_html_e( 'Sorry, no url redirect specified. Can\'t complete request.', $this->plugin_name );
		}
		?>
	</div>
	</body>
	</html>
<?php
die();
