<?php 
  include 'wp-config.php';
    
  global $wpdb;

  $users_from_wp = get_users( array( 'fields' => array( 'user_email' ) ) );

    
  $user_from_wp = array();
  foreach ($users_from_wp as $key => $from_wp) {    
  $result = $wpdb->get_results ( "SELECT * FROM wp_crms_member WHERE email = '".$from_wp->user_email."'");
    if (!empty($result)) {
      $user_from_wp[] = $result[0];
    }
  }

  $users_from_crms = array();
  foreach ($user_from_wp as $key => $value) {
      $url = "https://api.current-rms.com/api/v1/members/".$value->id;
      $ch = curl_init( $url );
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
      curl_setopt($ch, CURLOPT_HTTP_VERSION, "CURL_HTTP_VERSION_1_1");
      curl_setopt( $ch, CURLOPT_HTTPHEADER, array('X-SUBDOMAIN:o3group','X-AUTH-TOKEN:PDW-dNfDiZd5RbLLbHYT','Content-Type:application/json'));
      curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
      $result = curl_exec($ch);   
      curl_close($ch);
      $result_decode = json_decode($result);
      $users_from_crms[] = $result_decode;
  }

    
    //echo "<pre>";print_r($users_from_crms);exit();
  foreach ($users_from_crms as $key => $from_crms) {
    $member_detail = $from_crms->member;
    

    if (empty($emails)) {
      $emails = $member_detail->emails[0]->address;
      
    } else {
      $emails = $member_detail->identity->email;
    }
    


    $wp_result = $wpdb->get_results ( "SELECT * FROM www0_users WHERE user_email = '".$emails."'");
    $wp_user_id = $wp_result[0]->ID;

    /*$user_info = get_user_meta($wp_user_id);
      $wcmca_additional_addresses_decode = $user_info['_wcmca_additional_addresses'][0];
      echo "<pre>";print_r($wcmca_additional_addresses_decode);exit();
      $wcmca_additional_addresses_decode = json_decode();*/

    $primary_address = $member_detail->primary_address;

    $req_array  = array('billing' => array('address_1' => $primary_address->street,
                        'postcode' => $primary_address->postcode,
                        'city' => $primary_address->city,
                        'county' => $primary_address->county,
                        'country_name' => $primary_address->country_name
                        ));;
    
    $json_encode_req_array =  json_encode($req_array);

    $curl = curl_init();
  curl_setopt_array($curl, array(

    CURLOPT_URL => "https://my-brand.be/wp-json/wc/v3/customers/".$wp_user_id."?consumer_key=ck_be206cc24a71a758827c3c01ff16d032b6f3fed5&consumer_secret=cs_c32e0d0a6ce4be55be0724b58bd3203f585947d6",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "PUT",
    CURLOPT_POSTFIELDS => $json_encode_req_array,
    CURLOPT_SSL_VERIFYPEER => 0,
    CURLOPT_SSL_VERIFYHOST => FALSE,
    CURLOPT_HTTPHEADER => array(
      "Authorization: Basic ",
      "Content-Type: application/json",
      "cache-control: no-cache"
    ),
  ));
  $response = curl_exec($curl);
  $response_decode = json_decode($response);

    echo "<pre>";print_r($response_decode);exit();
  }
  
?>