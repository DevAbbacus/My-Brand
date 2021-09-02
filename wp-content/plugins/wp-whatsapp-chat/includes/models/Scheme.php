<?php

include_once (QLWAPP_PLUGIN_DIR . 'includes/models/QLWAPP_Model.php');

class QLWAPP_Scheme extends QLWAPP_Model {

  protected $table = 'scheme';

  function get_args() {

    $args = array(
        'brand' => '',
        'text' => '',
        'link' => '',
        'message' => '',
        'label' => '',
        'name' => '',
    );
    return $args;
  }

  function save($scheme = NULL) {
    return parent::save_data($this->table, $scheme);
  }

}
