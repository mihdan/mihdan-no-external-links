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
		<style>
			@keyframes mihdan-no-external-links-animate-stripes {
				100% { background-position: -100px 0; }
			}
			html {
				font-size: 62.5%;
			}
			body {
				background: #fff;
				color: #3c434a;
				font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif;
				font-size: 1.6em;
				line-height: 1.4;
				min-width: 300px;
				margin: 0;
			}
			.mihdan-no-external-links-container {
				text-align:center;
				margin-top: 15em;
			}
			.mihdan-no-external-links-timer {
				text-align: center;
				width: 300px;
				margin-left: auto;
				margin-right: auto;
				font-size: 2.1rem;
			}
			.mihdan-no-external-links-progress {
				width: 300px;
				margin-left: auto;
				margin-right: auto;
				margin-bottom: 20px;
			}
			.mihdan-no-external-links-progress__bar {
				width: 100%;
			}
			.mihdan-no-external-links-progress__bar[value] {
				height: 16px;
				-webkit-appearance: none;
				appearance: none;
				border-radius: 2px;
			}
			.mihdan-no-external-links-progress__bar[value]::-webkit-progress-bar {
				background-color: #eee;
				border-radius: 2px;
			}
			.mihdan-no-external-links-progress__bar[value]::-webkit-progress-value {
				background-image:
					-webkit-linear-gradient(-45deg,
					transparent 33%, rgba(0, 0, 0, .1) 33%,
					rgba(0,0, 0, .1) 66%, transparent 66%),
					-webkit-linear-gradient(top,
					rgba(255, 255, 255, .25),
					rgba(0, 0, 0, .25)),
					-webkit-linear-gradient(left, #09c, #f44);

				border-radius: 2px;
				background-size: 35px 20px, 100% 100%, 100% 100%;
				animation: mihdan-no-external-links-animate-stripes 5s linear infinite;
			}
		</style>
	</head>
	<body>
	<div class="mihdan-no-external-links-container">
		<div class="mihdan-no-external-links-timer">
			<span class="mihdan-no-external-links-timer__percent">0</span>% / <span class="mihdan-no-external-links-timer__time"><?php echo (int) $this->options->redirect_time; ?></span> <?php esc_html_e( 'sec', $this->plugin_name ); ?>
		</div>
		<div class="mihdan-no-external-links-progress">
			<progress max="100" value="0" class="mihdan-no-external-links-progress__bar"></progress>
		</div>
		<div class="mihdan-no-external-links-content">
			<?php
			$url = $url ?? null;
			// phpcs:ignore Generic.Commenting.DocComment.MissingShort
			if ( $this->options->redirect_message && $url ) {
				$allowed_html = [
					'a'      => [
						'href'   => true,
						'title'  => true,
						'rel'    => true,
						'target' => true,
						'class'  => true,
					],
					'p'      => [
						'class' => true,
					],
					'div'    => [
						'class' => true,
					],
					'style'  => [
						'type' => true,
					],
					'script' => [
						'type' => true,
					],
				];

				echo wp_kses(
					preg_replace(
						'#%linkurl%#',
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
	</div>
	<script>
		document.addEventListener( 'DOMContentLoaded', function() {
			let bar = document.querySelector('.mihdan-no-external-links-progress__bar');
			let sec = document.querySelector('.mihdan-no-external-links-timer__time');
			let percent = document.querySelector('.mihdan-no-external-links-timer__percent');
			let time = <?php echo (int) $this->options->redirect_time; ?>;
			let startTime = Date.now();
			let current;
			let width = 0;

			let interval = setInterval(function() {
				let elapsedTime = Date.now() - startTime;
				current = (elapsedTime / 1000).toFixed(3);

				if ( current > time ) {
					clearInterval( interval );
					current = time;
					document.getElementById('external').click();
				}

				width = 100 * current / time;
				bar.value = width;

				percent.innerHTML = width.toFixed();
				sec.innerHTML     = ( time - Number( current ).toFixed(0) );
			}, 250);
		});
	</script>
	</body>
	</html>
<?php
die();
