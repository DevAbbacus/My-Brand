<?php
// Before removing this file, please verify the PHP ini setting `auto_prepend_file` does not point to this.

if (file_exists('/customers/4/7/4/my-brand.be/httpd.www/wp-content/plugins/wordfence/waf/bootstrap.php')) {
	define("WFWAF_LOG_PATH", '/customers/4/7/4/my-brand.be/httpd.www/wp-content/wflogs/');
	include_once '/customers/4/7/4/my-brand.be/httpd.www/wp-content/plugins/wordfence/waf/bootstrap.php';
}
?>