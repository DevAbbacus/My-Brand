<?php 
function wcmca_get_woo_version_number() 
{
        // If get_plugins() isn't available, require it
	if ( ! function_exists( 'get_plugins' ) )
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	
        // Create the plugins folder and file variables
	$plugin_folder = get_plugins( '/' . 'woocommerce' );
	$plugin_file = 'woocommerce.php';
	
	// If the plugin version number is set, return it 
	if ( isset( $plugin_folder[$plugin_file]['Version'] ) ) {
		return $plugin_folder[$plugin_file]['Version'];

	} else {
	// Otherwise return null
		return NULL;
	}
}
function wcmca_get_file_version( $file ) 
{

		// Avoid notices if file does not exist
		if ( ! file_exists( $file ) ) {
			return '';
		}

		// We don't need to write to the file, so just open for reading.
		$fp = fopen( $file, 'r' );

		// Pull only the first 8kiB of the file in.
		$file_data = fread( $fp, 8192 );

		// PHP will close file handle, but we are good citizens.
		fclose( $fp );

		// Make sure we catch CR-only line endings.
		$file_data = str_replace( "\r", "\n", $file_data );
		$version   = '';

		if ( preg_match( '/^[ \t\/*#@]*' . preg_quote( '@version', '/' ) . '(.*)$/mi', $file_data, $match ) && $match[1] )
			$version = _cleanup_header_comment( $match[1] );

		return $version ;
	}
function wcmca_array_element_contains_substring( $substring, $array)
{
	if(!isset($array))
		return false;
	
	$array = !is_array($array) ? array($array) : $array;
	
	foreach($array as $elem)
		if (is_string($elem) && strpos($elem, $substring) !== false)
			return true;
}
$wcmca_result = get_option("_".$wcmca_id);
$wcmca_notice = !$wcmca_result || $wcmca_result != md5($_SERVER['SERVER_NAME']);
$wcmca_notice = false;
/* if($wcmca_notice)
	remove_action( 'plugins_loaded', 'wcmca_setup'); */
if(!$wcmca_notice)
	wcmca_setup();
function wcmca_get_value_if_set($data, $nested_indexes, $default)
{
	if(!isset($data))
		return $default;
	
	$nested_indexes = is_array($nested_indexes) ? $nested_indexes : array($nested_indexes);
	//$current_value = null;
	foreach($nested_indexes as $index)
	{
		if(!isset($data[$index]))
			return $default;
		
		$data = $data[$index];
		//$current_value = $data[$index];
	}
	
	return $data;
}
function wcmca_is_html($string)
{
  return preg_match("/<[^<]+>/",$string,$m) != 0;
}	
function wcmca_random_string($length = 15)
{
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}	
function wcmca_var_debug_dump($log)
{
	 if ( is_array( $log ) || is_object( $log ) ) 
	 {
         error_log( print_r( $log, true ) );
      } 
	  else if(is_bool($log))
	  {
		 error_log( $log ? 'true' : 'false' );  
	  }	  
	  else{
         error_log( $log );
      }

}
?>