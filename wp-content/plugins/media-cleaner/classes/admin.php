<?php

class Meow_WPMC_Admin extends MeowCommon_Admin {

  public function __construct( $allow_setup ) {
    parent::__construct( WPMC_PREFIX, WPMC_ENTRY, WPMC_DOMAIN, class_exists( 'MeowPro_WPMC_Core' ) );
    if ( $allow_setup ) {
      add_action( 'admin_menu', array( $this, 'app_menu' ) );
    }

    // Load the scripts only if they are needed by the current screen
    $page = isset( $_GET["page"] ) ? $_GET["page"] : null;
    $is_wpmc_screen = in_array( $page, [ 'wpmc_dashboard', 'wpmc_settings' ] );
    $is_meowapps_dashboard = $page === 'meowapps-main-menu';
    if ( $is_meowapps_dashboard || $is_wpmc_screen ) {
      add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
    }
  }

  function admin_enqueue_scripts() {

    // Load the scripts
    $physical_file = WPMC_PATH . '/app/index.js';
    $cache_buster = file_exists( $physical_file ) ? filemtime( $physical_file ) : WPMC_VERSION;
    wp_register_script( 'wpmc_media_cleaner-vendor', WPMC_URL . 'app/vendor.js',
      ['wp-element', 'wp-i18n'], $cache_buster
    );
    wp_register_script( 'wpmc_media_cleaner', WPMC_URL . 'app/index.js',
      ['wpmc_media_cleaner-vendor', 'wp-i18n'], $cache_buster
    );
    if ( function_exists( 'wp_set_script_translations' ) ) {
      wp_set_script_translations( 'wpmc_media_cleaner', 'media-cleaner' );
    }
    wp_enqueue_script('wpmc_media_cleaner' );

    // Load the fonts
    wp_register_style( 'meow-neko-ui-lato-font', '//fonts.googleapis.com/css2?family=Lato:wght@100;300;400;700;900&display=swap');
    wp_enqueue_style( 'meow-neko-ui-lato-font' );

    // Localize and options
    wp_localize_script( 'wpmc_media_cleaner', 'wpmc_media_cleaner', array_merge( [
      'api_url' => rest_url( 'media-cleaner/v1' ),
      'rest_url' => rest_url(),
      'plugin_url' => WPMC_URL,
      'prefix' => WPMC_PREFIX,
      'domain' => WPMC_DOMAIN,
      'is_pro' => class_exists( 'MeowPro_WPMC_Core' ),
      'is_registered' => !!$this->is_registered(),
      'rest_nonce' => wp_create_nonce( 'wp_rest' )
    ], $this->get_all_options() ) );
  }

  function app_menu() {
    add_submenu_page( 'meowapps-main-menu', 'Cleaner', 'Cleaner', 'read', 'wpmc_settings', 
      array( $this, 'admin_settings' )
    );
  }

  public function admin_settings() {
    echo '<div id="wpmc-admin-settings"></div>';
  }

  function get_all_options() {
    return [
      'wpmc_method' => get_option( 'wpmc_method', 'media' ),
      'wpmc_content' => get_option( 'wpmc_content', true ),
      'wpmc_filesystem_content' => get_option( 'wpmc_filesystem_content', false ),
      'wpmc_media_library' => get_option( 'wpmc_media_library', true ),
      'wpmc_live_content' => get_option( 'wpmc_live_content', false ),
      'wpmc_debuglogs' => get_option( 'wpmc_debuglogs', false ),
      'wpmc_images_only' => get_option( 'wpmc_images_only', false ),
      'wpmc_thumbnails_only' => get_option( 'wpmc_thumbnails_only', false ),
      'wpmc_dirs_filter' => get_option( 'wpmc_dirs_filter', '' ),
      'wpmc_files_filter' => get_option( 'wpmc_files_filter', '' ),
      'wpmc_hide_thumbnails' => get_option( 'wpmc_hide_thumbnails' ),
      'wpmc_hide_warning' => get_option( 'wpmc_hide_warning' ),
      'wpmc_medias_buffer' => get_option( 'wpmc_medias_buffer', 100 ),
      'wpmc_posts_buffer' => get_option( 'wpmc_posts_buffer', 5 ),
      'wpmc_analysis_buffer' => get_option( 'wpmc_analysis_buffer', 100 ),
      'wpmc_file_op_buffer' => get_option( 'wpmc_file_op_buffer', 20 ),
      'wpmc_delay' => get_option( 'wpmc_delay', 100 ),
      'wpmc_shortcodes_disabled' => get_option( 'wpmc_shortcodes_disabled' ),
      'wpmc_posts_per_page' => get_option( 'wpmc_posts_per_page', 10 ),
    ];
  }

}

?>