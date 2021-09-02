<?php
/**
 * Instagram API control.
 *
 * @package xts
 */

namespace XTS\Options\Controls;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

use XTS\Options\Field;

/**
 * Input type text field control.
 */
class Instagram_Api extends Field {
	/**
	 * Displays the field control HTML.
	 *
	 * @since 1.0.0
	 *
	 * @return void.
	 */
	public function render_control() {
		$code            = isset( $_GET['code'] ) ? $_GET['code'] : '';
		$redirect_uri    = admin_url( 'admin.php' );
		$message         = '';
		$message_classes = '';

		if ( $code && $this->get_field_value( 'app_id' ) && $this->get_field_value( 'app_secret' ) ) {
			$params = array(
				'app_id'        => $this->get_field_value( 'app_id' ),
				'app_secret'    => $this->get_field_value( 'app_secret' ),
				'grant_type'    => 'authorization_code',
				'redirect_uri'  => $redirect_uri,
				'code'          => $code,
			);

			$get_access_token = wp_remote_post( 'https://api.instagram.com/oauth/access_token', array( 'body' => $params ) );
			$token_data       = json_decode( $get_access_token['body'] );

			if ( is_object( $token_data ) ) {
				if ( ! property_exists( $token_data, 'error_type' ) ) {
					$token = $token_data->access_token;
					if ( $token ) {
						update_option( 'insta_access_token', $token );
						wp_redirect( $redirect_uri . '?page=xtemos_options&tab=general_section&generated' );
					}
				} elseif ( property_exists( $token_data, 'error_message' ) ) {
					$message         .= 'Error type: ' . $token_data->error_type . '<br>';
					$message         .= 'Error code: ' . $token_data->code . '<br>';
					$message         .= 'Error message: ' . $token_data->error_message;
					$message_classes .= ' xts-error';
				}
			} else {
				$message .= 'Something went wrong.';
				$message_classes .= ' xts-error';
			}
		}

		if ( isset( $_GET['generated'] ) && get_option( 'insta_access_token' ) ) {
			$message .= 'Your access token is generated.';
			$message_classes .= ' xts-success';
		}

		?>
		<div class="xts-insta-option">
			<div class="xts-insta-form-wrap">
				<label><?php esc_html_e( 'App ID', 'woodmart' ); ?></label>
				<input type="text" name="<?php echo esc_attr( $this->get_input_name( 'app_id' ) ); ?>"
					   value="<?php echo esc_attr( $this->get_field_value( 'app_id' ) ); ?>"
					   placeholder="<?php esc_attr_e( 'Enter your app id', 'woodmart' ); ?>">
			</div>

			<div class="xts-insta-form-wrap">
				<label><?php esc_html_e( 'App secret', 'woodmart' ); ?></label>
				<input type="text" name="<?php echo esc_attr( $this->get_input_name( 'app_secret' ) ); ?>"
					   value="<?php echo esc_attr( $this->get_field_value( 'app_secret' ) ); ?>"
					   placeholder="<?php esc_attr_e( 'Enter your app secret', 'woodmart' ); ?>">
			</div>
		</div>
		<p class="xts-insta-message-section xts-notice<?php echo esc_attr( $message_classes ); ?>"><?php echo wp_kses( $message, array( 'br' => array() ) ); ?></p>
		
		<input type="hidden" name="redirect_uri" value="<?php echo esc_url( $redirect_uri ); ?>">
		
		<button type="submit" class="xts-btn xts-btn-primary">
			<?php esc_html_e( 'Save and generate token' ); ?>
		</button>
		<?php
	}
}


