<?php
/**
 * CSS to file
 *
 * @package xts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

/**
 * CSS to file class
 *
 * @since 1.0.0
 */
class WOODMART_Stylesstorage {
	public $data_name;
	public $storage;
	public $id;
	public $theme_version;

	public $data;
	public $css;

	public $check_credentials;

	public $is_file_exists = false;
	public $is_css_exists  = false;

	public function __construct( $data_name, $storage = 'option', $id = '', $check_credentials = true ) {
		$this->data_name         = $data_name;
		$this->storage           = $storage;
		$this->id                = $id;
		$this->check_credentials = $check_credentials;
		$this->theme_version     = woodmart_get_theme_info( 'Version' );

		$this->data = $this->get_data( 'xts-' . $this->data_name . '-data' );

		if ( isset( $this->data['css'] ) && $this->data['css'] ) {
			$this->css = $this->data['css'];
		}

		$this->check_css_status();
	}

	/**
	 * Check css status.
	 *
	 * @since 1.0.0
	 */
	public function check_css_status() {
		$data_status = $this->get_data( 'xts-' . $this->data_name . '-status' );

		if ( isset( $this->data['theme_version'] ) && version_compare( $this->theme_version, $this->data['theme_version'], '==' ) && 'valid' === $data_status ) {
			if ( isset( $this->data['path'] ) && file_exists( $this->data['path'] ) && apply_filters( 'woodmart_styles_storage_file', true ) ) {
				$this->is_file_exists = true;
			}

			if ( isset( $this->data['css'] ) && $this->data['css'] && apply_filters( 'woodmart_styles_storage_db_css', true ) ) {
				$this->is_css_exists = true;
			}
		}
	}

	/**
	 * Is css exists.
	 *
	 * @since 1.0.0
	 */
	public function is_css_exists() {
		return $this->is_css_exists;
	}

	/**
	 * Print styles.
	 *
	 * @since 1.0.0
	 */
	public function print_styles() {
		if ( $this->is_file_exists ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'file_css' ), 10000 );
		} else {
			add_action( 'wp_head', array( $this, 'inline_css' ), 10000 );
		}
	}

	/**
	 * FIle css.
	 *
	 * @since 1.0.0
	 */
	public function file_css() {
		if ( isset( $this->data['url'] ) && $this->data['url'] ) {
			if ( is_ssl() ) {
				$this->data['url'] = str_replace( 'http://', 'https://', $this->data['url'] );
			}

			wp_enqueue_style( 'xts-style-' . $this->data_name, $this->data['url'], array(), $this->theme_version );
		}
	}

	/**
	 * Inline css.
	 *
	 * @since 1.0.0
	 */
	public function inline_css() {
		if ( $this->css ) {
			?>
			<style data-type="wd-style-<?php echo esc_attr( $this->data_name ); ?>">
				<?php echo $this->css; // phpcs:ignore ?>
			</style>
			<?php
		}
	}

	/**
	 * Reset data.
	 *
	 * @since 1.0.0
	 */
	public function reset_data() {
		$this->update_data( 'xts-' . $this->data_name . '-status', 'invalid' );
		$this->delete_data( 'xts-' . $this->data_name . '-credentials' );
	}

	/**
	 * Write file.
	 *
	 * @since 1.0.0
	 *
	 * @param $css
	 */
	public function write( $css ) {
		if ( ! $css ) {
			return;
		}

		$this->css = $css;

		$this->write_file( $css );

		$this->update_data(
			'xts-' . $this->data_name . '-data',
			array(
				'url'           => $this->get_file_info( 'url', $this->data_name ),
				'path'          => $this->get_file_info( 'path', $this->data_name ),
				'css'           => $css,
				'theme_version' => woodmart_get_theme_info( 'Version' ),
			)
		);

		$this->update_data( 'xts-' . $this->data_name . '-status', 'valid' );
	}

	/**
	 * Write file.
	 *
	 * @param $css
	 */
	private function write_file( $css ) {
		if ( function_exists( 'WP_Filesystem' ) ) {
			WP_Filesystem();
		}
		/**
		 * File system
		 *
		 * @var WP_Filesystem_Base $wp_filesystem
		 */
		global $wp_filesystem;

		if ( ( $this->check_credentials && ( function_exists( 'request_filesystem_credentials' ) && ! $this->check_credentials() ) ) || ! $wp_filesystem ) {
			return;
		}

		if ( $this->data && $this->data['path'] ) {
			$wp_filesystem->delete( $this->data['path'] );
			$this->delete_data( 'xts-' . $this->data_name . '-data' );
		}

		$wp_filesystem->put_contents( $this->get_file_info( 'path', $this->data_name ), $css );
	}

	/**
	 * Get data.
	 *
	 * @param string $name Option name.
	 *
	 * @return mixed|string|void
	 */
	private function get_data( $name ) {
		$results = '';

		if ( 'option' === $this->storage ) {
			$results = get_option( $name );
		} elseif ( 'post_meta' === $this->storage && $this->id ) {
			$results = get_post_meta( $this->id, $name );
		}

		return $results;
	}

	/**
	 * Update data.
	 *
	 * @param string $name Option name.
	 * @param mixed  $data      Data.
	 *
	 * @return mixed|string|void
	 */
	private function update_data( $name, $data ) {
		if ( 'option' === $this->storage ) {
			update_option( $name, $data );
		} elseif ( 'post_meta' === $this->storage && $this->id ) {
			update_post_meta( $this->id, $name, $data );
		}
	}

	/**
	 * Delete data.
	 *
	 * @param string $name Option name.
	 *
	 * @return mixed|string|void
	 */
	private function delete_data( $name ) {
		if ( 'option' === $this->storage ) {
			delete_option( $name );
		} elseif ( 'post_meta' === $this->storage && $this->id ) {
			delete_post_meta( $this->id, $name );
		}
	}

	/**
	 * Get file info.
	 *
	 * @since 1.0.0
	 *
	 * @param string $target    File info target.
	 * @param string $data_name File name.
	 *
	 * @return string
	 */
	public function get_file_info( $target, $data_name ) {
		$data_name = 'xts-' . $data_name . '.css';
		$uploads   = wp_upload_dir();

		if ( 'path' === $target ) {
			return $uploads['path'] . '/' . $data_name;
		}

		return $uploads['url'] . '/' . $data_name;
	}

	/**
	 * Check credentials.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function check_credentials() {
		$data_status        = $this->get_data( 'xts-' . $this->data_name . '-status' );
		$credentials_status = $this->get_data( 'xts-' . $this->data_name . '-credentials' );

		if ( ( 'valid' === $data_status || 'requested' === $credentials_status ) && ! $_POST ) {
			return false;
		}

		$this->update_data( 'xts-' . $this->data_name . '-credentials', 'requested' );

		echo '<div class="woodmart-request-credentials">';
		$credentials = request_filesystem_credentials( false, '', false, false, array_keys( $_POST ) ); // phpcs:ignore
		echo '</div>';

		if ( ! $credentials ) {
			return false;
		}

		if ( ! WP_Filesystem( $credentials ) ) {
			return false;
		}

		return true;
	}
}
