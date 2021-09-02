<?php

include_once (QLWAPP_PLUGIN_DIR . 'includes/models/QLWAPP_Model.php');

class QLWAPP_Box extends QLWAPP_Model {

  protected $table = 'box';

  function get_args() {
    $args = array(
        'enable' => 'yes',
        'auto_open' => 'no',
        'auto_delay_open' => 1000,
        'header' => '<h3>Hello!</h3><p>Click one of our representatives below to chat on WhatsApp or send us an email to <a href="mailto:' . get_bloginfo('admin_email') . '">' . get_bloginfo('admin_email') . '</a></p>',
        'footer' => '<p>Powered by <a target="_blank" href="' . QLWAPP_PURCHASE_URL . '">WhatsApp Chat</a>',
        'response' => esc_html__('Write a response', 'wp-whatsapp-chat')
//              ,'contactstimeout' => 'no' 
    );
    return $args;
  }

  function sanitize($settings) {

    if (isset($settings['header'])) {
      $settings['header'] = wp_kses_post($settings['header']);
    } 
     if (isset($settings['auto_open'])) {
      $settings['auto_open'] = wp_kses_post($settings['auto_open']);
    }
    if (isset($settings['auto_delay_open'])) {
      $settings['auto_delay_open'] = wp_kses_post($settings['auto_delay_open']);
    }
    if (isset($settings['footer'])) {
      $settings['footer'] = wp_kses_post($settings['footer']);
    }
    return $settings;
  }

  function save($box_data = NULL) {
    return parent::save_data($this->table, $this->sanitize($box_data));
  }

}
