<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://techxperts.co.in/
 * @since      1.0.0
 *
 * @package    Xperts_Crms_Wc
 * @subpackage Xperts_Crms_Wc/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Xperts_Crms_Wc
 * @subpackage Xperts_Crms_Wc/admin
 * @author     Rajeev <rajeev@techxperts.co.in>
 */
class Xperts_Crms_Wc_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Xperts_Crms_Wc_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Xperts_Crms_Wc_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/xperts-crms-wc-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Xperts_Crms_Wc_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Xperts_Crms_Wc_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/xperts-crms-wc-admin.js', array( 'jquery' ), $this->version, false );

	}

	public function admin_init()
    {

        if(isset($_GET['_wpnonce']) and !empty($_GET['_wpnonce']) and wp_verify_nonce($_GET['_wpnonce'],'bulk-crms-color-mappings'))
        {
            if(sanitize_text_field($_REQUEST['action'])=='delete') {
                $post = (array) $_REQUEST['post'];
                $post = array_map('intval',$post);
                if(count($post))
                {
                    $post = implode("','",$post);
                    global $wpdb;
                    $wpdb->query("delete from {$wpdb->prefix}crms_color_map where id in ('$post') ");
                    wp_redirect(admin_url('admin.php?page=xperts-crms-color-map&msg=3'));
                    exit;
                }
            }
        }

        if(isset($_GET['_wpnonce']) and !empty($_GET['_wpnonce']) and wp_verify_nonce($_GET['_wpnonce'],'bulk-crms-category-mappings'))
        {
            if(sanitize_text_field($_REQUEST['action'])=='delete') {
                $post = (array) $_REQUEST['post'];
                $post = array_map('intval',$post);
                if(count($post))
                {
                    $post = implode("','",$post);
                    global $wpdb;
                    $wpdb->query("delete from {$wpdb->prefix}crms_category_map where id in ('$post') ");
                    wp_redirect(admin_url('admin.php?page=xperts-crms-cmap&msg=3'));
                    exit;
                }
            }
        }

        if(isset($_GET['_wpnonce']) and isset($_REQUEST['id']) and !empty($_GET['_wpnonce']) and wp_verify_nonce($_GET['_wpnonce'],'delete-mapping_color_'.$_REQUEST['id']))
        {
            if(sanitize_text_field($_REQUEST['action'])=='delete') {
                $post = intval($_REQUEST['id']);
                if($post)
                {
                    global $wpdb;
                    $wpdb->delete($wpdb->prefix.'crms_color_map',[
                        'id'=> $post
                    ]);
                    wp_redirect(admin_url('admin.php?page=xperts-crms-color-map&msg=3'));
                    exit;
                }
            }
        }

        if(isset($_GET['_wpnonce']) and isset($_REQUEST['id']) and !empty($_GET['_wpnonce']) and wp_verify_nonce($_GET['_wpnonce'],'delete-mapping_'.$_REQUEST['id']))
        {
            if(sanitize_text_field($_REQUEST['action'])=='delete') {
                $post = intval($_REQUEST['id']);
                if($post)
                {
                    global $wpdb;
                    $wpdb->delete($wpdb->prefix.'crms_category_map',[
                        'id'=> $post
                    ]);
                    wp_redirect(admin_url('admin.php?page=xperts-crms-cmap&msg=3'));
                    exit;
                }
            }
        }

        if(isset($_POST['xperts_crms_cmap_update']) and !empty($_POST['xperts_crms_cmap_update']) and wp_verify_nonce($_POST['xperts_crms_cmap_update'],'xperts_crms_cmap_update')) {
            global $xperts_form_errors, $wpdb;
            $xperts_form_errors = new WP_Error();
            $mapping_id = isset($_POST['mapping_id'])?intval($_POST['mapping_id']):'';
            $crms_category = isset($_POST['crms_category'])?sanitize_text_field($_POST['crms_category']):'';
            $category_mappings = isset($_POST['category_mappings'])?array_map('intval',$_POST['category_mappings']):[];
            if(empty($crms_category)){
                $xperts_form_errors->add('crms_category','Please enter CRMS Document Layout Field Name');
            }
            if(count($category_mappings)<=0) {
                $xperts_form_errors->add('category_mappings','Please select some category to map');
            }

            $exists = $wpdb->get_var($wpdb->prepare("select count(id) as total from {$wpdb->prefix}crms_category_map where id!=%d and crms_category=%s",$mapping_id, $crms_category));
            if($exists)
            {
                $xperts_form_errors->add('crms_category','Document Layout Field Name already exists.');
            }

            if(!$xperts_form_errors->has_errors()){
                $wpdb->update($wpdb->prefix.'crms_category_map',[
                    'crms_category' => $crms_category,
                    'category_mappings' => serialize($category_mappings),
                ],[
                    'id'=>$mapping_id
                ]);
                wp_redirect(admin_url('admin.php?page=xperts-crms-cmap&msg=2'));
                exit;
            }
        }

        if(isset($_POST['xperts_crms_cmap_save']) and !empty($_POST['xperts_crms_cmap_save']) and wp_verify_nonce($_POST['xperts_crms_cmap_save'],'xperts_crms_cmap_save')) {

            global $xperts_form_errors, $wpdb;
            $xperts_form_errors = new WP_Error();
            $crms_category = isset($_POST['crms_category'])?sanitize_text_field($_POST['crms_category']):'';
            $crms_category = trim($crms_category);
            $category_mappings = isset($_POST['category_mappings'])?array_map('intval',$_POST['category_mappings']):[];
            if(empty($crms_category)){
                $xperts_form_errors->add('crms_category','Please enter CRMS Document Layout Field Name');
            }
            if(count($category_mappings)<=0) {
                $xperts_form_errors->add('category_mappings','Please select some category to map');
            }

            $exists = $wpdb->get_var($wpdb->prepare("select count(id) as total from {$wpdb->prefix}crms_category_map where crms_category=%s",$crms_category));
            if($exists)
            {
                $xperts_form_errors->add('crms_category','Document Layout Field Name already exists.');
            }

            if(!$xperts_form_errors->has_errors()){
                $wpdb->insert($wpdb->prefix.'crms_category_map',[
                    'crms_category' => $crms_category,
                    'category_mappings' => serialize($category_mappings),
                ]);
                wp_redirect(admin_url('admin.php?page=xperts-crms-cmap&msg=1'));
                exit;
            }
        }


        if(isset($_POST['xperts_crms_color_map_save']) and !empty($_POST['xperts_crms_color_map_save']) and wp_verify_nonce($_POST['xperts_crms_color_map_save'],'xperts_crms_color_map_save')) {

            global $xperts_form_errors, $wpdb;
            $xperts_form_errors = new WP_Error();
            $crms_color = isset($_POST['crms_color'])?sanitize_text_field($_POST['crms_color']):'';
            $crms_color = trim($crms_color);
            $color_mappings = isset($_POST['color_mappings'])?array_map('intval',$_POST['color_mappings']):[];
            if(empty($crms_color)){
                $xperts_form_errors->add('crms_color','Please enter CRMS Document Layout Field Name');
            }
            if(count($color_mappings)<=0) {
                $xperts_form_errors->add('color_mappings','Please select some color to map');
            }

            $exists = $wpdb->get_var($wpdb->prepare("select count(id) as total from {$wpdb->prefix}crms_color_map where crms_color=%s",$crms_color));
            if($exists)
            {
                $xperts_form_errors->add('crms_color','Document Layout Field Name already exists.');
            }

            if(!$xperts_form_errors->has_errors()){
                $wpdb->insert($wpdb->prefix.'crms_color_map',[
                    'crms_color' => $crms_color,
                    'color_mappings' => serialize($color_mappings),
                ]);
                wp_redirect(admin_url('admin.php?page=xperts-crms-color-map&msg=1'));
                exit;
            }
        }

        /**
         * Update Color mapping
         */
        if(isset($_POST['xperts_crms_color_map_update']) and !empty($_POST['xperts_crms_color_map_update']) and wp_verify_nonce($_POST['xperts_crms_color_map_update'],'xperts_crms_color_map_update')) {
            global $xperts_form_errors, $wpdb;
            $xperts_form_errors = new WP_Error();
            $mapping_id = isset($_POST['mapping_id'])?intval($_POST['mapping_id']):'';
            $crms_color = isset($_POST['crms_color'])?sanitize_text_field($_POST['crms_color']):'';
            $color_mappings = isset($_POST['color_mappings'])?array_map('intval',$_POST['color_mappings']):[];
            if(empty($crms_color)){
                $xperts_form_errors->add('crms_color','Please enter CRMS Document Layout Field Name');
            }
            if(count($color_mappings)<=0) {
                $xperts_form_errors->add('color_mappings','Please select some color to map');
            }

            $exists = $wpdb->get_var($wpdb->prepare("select count(id) as total from {$wpdb->prefix}crms_color_map where id!=%d and crms_color=%s",$mapping_id, $crms_color));
            if($exists)
            {
                $xperts_form_errors->add('crms_color','Document Layout Field Name already exists.');
            }

            if(!$xperts_form_errors->has_errors()){
                $wpdb->update($wpdb->prefix.'crms_color_map',[
                    'crms_color' => $crms_color,
                    'color_mappings' => serialize($color_mappings),
                ],[
                    'id'=>$mapping_id
                ]);
                wp_redirect(admin_url('admin.php?page=xperts-crms-color-map&msg=2'));
                exit;
            }
        }

    }

    /**
     * Register admin menu pages
     *
     * @since    1.0.0
     */
	public function admin_menus()
    {
        add_menu_page(
            'Current RMS WooCommerce Category Mapping',
            'CRMS Category Mapping',
             'manage_options',
             'xperts-crms-cmap',
            array($this,'category_mapping_views')
        );

        add_submenu_page(
            'xperts-crms-cmap',
            'CRMS Color Mapping',
            'CRMS Color Mapping',
            'manage_options',
            'xperts-crms-color-map',
            array($this,'color_mapping_views')
        );
    }

    public function color_mapping_views()
    {
        $action = isset($_REQUEST['action'])?esc_attr($_REQUEST['action']):'';
        switch ($action){
            case 'edit':
                global $xperts_form_errors,$wpdb;
                add_filter( 'wp_dropdown_cats', array($this,'wp_dropdown_cats_multiple'), 10, 2 );
                $mapping_id = isset($_REQUEST['mapping_id'])?intval($_REQUEST['mapping_id']):0;
                $crms_color_map = $wpdb->get_row($wpdb->prepare("select * from {$wpdb->prefix}crms_color_map where id=%d",$mapping_id));
                require_once dirname( __FILE__ ).'/partials/xperts-crms-wc-admin-color-edit.php';
                break;
            case 'add':
                global $xperts_form_errors;
                add_filter( 'wp_dropdown_cats', array($this,'wp_dropdown_cats_multiple'), 10, 2 );
                require_once dirname( __FILE__ ).'/partials/xperts-crms-wc-admin-color-add.php';
                break;
            default:

                global $wpdb;
                $limit = 20;
                $offset = 0;
                $current_page = $this->get_pagenum([]);
                if ( 1 < $current_page ) {
                    $offset = $limit * ( $current_page - 1 );
                } else {
                    $offset = 0;
                }

                $s = sanitize_text_field(isset($_REQUEST['s'])?$_REQUEST['s']:'');
                $where = 'where 1=1';
                if($s){
                    $where.=" and crms_color like '%".$wpdb->esc_like($s)."%'";
                }

                $color_mappings = $wpdb->get_results("select SQL_CALC_FOUND_ROWS * from {$wpdb->prefix}crms_color_map $where limit {$offset}, {$limit}");
                $total_items = intval($wpdb->get_var('SELECT FOUND_ROWS()'));
                $total_pages = ceil( $total_items / $limit );

                require_once dirname( __FILE__ ).'/partials/xperts-crms-wc-admin-color-list.php';
                break;
        }
    }

    public function category_mapping_views()
    {
        $action = isset($_REQUEST['action'])?esc_attr($_REQUEST['action']):'';
        switch ($action){
            case 'edit':
                global $xperts_form_errors,$wpdb;
                add_filter( 'wp_dropdown_cats', array($this,'wp_dropdown_cats_multiple'), 10, 2 );
                $mapping_id = isset($_REQUEST['mapping_id'])?intval($_REQUEST['mapping_id']):0;
                $crms_category_map = $wpdb->get_row($wpdb->prepare("select * from {$wpdb->prefix}crms_category_map where id=%d",$mapping_id));
                require_once dirname( __FILE__ ).'/partials/xperts-crms-wc-admin-edit.php';
                break;
            case 'add':
                    global $xperts_form_errors;
                    add_filter( 'wp_dropdown_cats', array($this,'wp_dropdown_cats_multiple'), 10, 2 );
                    require_once dirname( __FILE__ ).'/partials/xperts-crms-wc-admin-add.php';
                break;
            default:

                    global $wpdb;
                    $limit = 20;
                    $offset = 0;
                    $current_page = $this->get_pagenum([]);
                    if ( 1 < $current_page ) {
                        $offset = $limit * ( $current_page - 1 );
                    } else {
                        $offset = 0;
                    }

                    $s = sanitize_text_field(isset($_REQUEST['s'])?$_REQUEST['s']:'');
                    $where = 'where 1=1';
                    if($s){
                        $where.=" and crms_category like '%".$wpdb->esc_like($s)."%'";
                    }

                    $category_mappings = $wpdb->get_results("select SQL_CALC_FOUND_ROWS * from {$wpdb->prefix}crms_category_map $where limit {$offset}, {$limit}");
                    $total_items = intval($wpdb->get_var('SELECT FOUND_ROWS()'));
                    $total_pages = ceil( $total_items / $limit );

                    require_once dirname( __FILE__ ).'/partials/xperts-crms-wc-admin-list.php';
                break;
        }
    }

    /**
     * Display the pagination.
     *
     * @since 3.1.0
     *
     * @param string $which
     */
    protected function pagination(  $_pagination_args ) {
        if ( empty( $_pagination_args ) ) {
            return;
        }

        $total_items  = $_pagination_args['total_items'];
        $total_pages  = $_pagination_args['total_pages'];

        $output = '<span class="displaying-num">' . sprintf( _n( '%s item', '%s items', $total_items ), number_format_i18n( $total_items ) ) . '</span>';

        $current              = $this->get_pagenum($_pagination_args);
        $removable_query_args = wp_removable_query_args();

        $current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );

        $current_url = remove_query_arg( $removable_query_args, $current_url );

        $page_links = array();

        $total_pages_before = '<span class="paging-input">';
        $total_pages_after  = '</span></span>';

        $disable_first = $disable_last = $disable_prev = $disable_next = false;

        if ( $current == 1 ) {
            $disable_first = true;
            $disable_prev  = true;
        }
        if ( $current == 2 ) {
            $disable_first = true;
        }
        if ( $current == $total_pages ) {
            $disable_last = true;
            $disable_next = true;
        }
        if ( $current == $total_pages - 1 ) {
            $disable_last = true;
        }

        if ( $disable_first ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<a class='first-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
                esc_url( remove_query_arg( 'paged', $current_url ) ),
                __( 'First page' ),
                '&laquo;'
            );
        }

        if ( $disable_prev ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<a class='prev-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
                esc_url( add_query_arg( 'paged', max( 1, $current - 1 ), $current_url ) ),
                __( 'Previous page' ),
                '&lsaquo;'
            );
        }

        $html_current_page = sprintf(
            "%s<input class='current-page' id='current-page-selector' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' /><span class='tablenav-paging-text'>",
            '<label for="current-page-selector" class="screen-reader-text">' . __( 'Current Page' ) . '</label>',
            $current,
            strlen( $total_pages )
        );

        $html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
        $page_links[]     = $total_pages_before . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . $total_pages_after;

        if ( $disable_next ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<a class='next-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
                esc_url( add_query_arg( 'paged', min( $total_pages, $current + 1 ), $current_url ) ),
                __( 'Next page' ),
                '&rsaquo;'
            );
        }

        if ( $disable_last ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<a class='last-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
                esc_url( add_query_arg( 'paged', $total_pages, $current_url ) ),
                __( 'Last page' ),
                '&raquo;'
            );
        }

        $pagination_links_class = 'pagination-links';

        $output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';

        if ( $total_pages ) {
            $page_class = $total_pages < 2 ? ' one-page' : '';
        } else {
            $page_class = ' no-pages';
        }
        echo "<div class='tablenav-pages{$page_class}'>$output</div>";

    }

    /**
     * Get the current page number
     *
     * @since 3.1.0
     *
     * @return int
     */
    public function get_pagenum($_pagination_args) {
        $pagenum = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 0;
        if ( isset( $_pagination_args['total_pages'] ) && $pagenum > $_pagination_args['total_pages'] ) {
            $pagenum = $_pagination_args['total_pages'];
        }
        return max( 1, $pagenum );
    }

    private function get_form_select($field,$current,$default='') {
        return (isset($_REQUEST[$field]) and sanitize_text_field($_REQUEST[$field])==$current)?'selected="selected"':(($default!='' and $default==$current)?'selected="selected"':'');
    }

    private function get_form_field($field,$default='') {
        return isset($_REQUEST[$field])?sanitize_text_field($_REQUEST[$field]):$default;
    }

    public function wp_dropdown_cats_multiple( $output, $r ) {
        if( isset( $r['multiple'] ) && $r['multiple'] ) {
            $output = preg_replace( '/^<select/i', '<select multiple data-live-search="true" style="min-height:400px;" data-style="btn-info"', $output );
            $output = str_replace( "name='{$r['name']}'", "name='{$r['name']}[]'", $output );
            $selected = is_array($r['selected']) ? $r['selected'] : explode( ",", $r['selected'] );
            foreach ( array_map( 'trim', $selected ) as $value )
                $output = str_replace( "value=\"{$value}\"", "value=\"{$value}\" selected", $output );
        }
        return $output;
    }

    public function getTermNames($category_mappings)
    {
        $html='';
        $category_mappings = unserialize($category_mappings);
        foreach ($category_mappings as $term_id) {
            $html.=get_term_by('id',$term_id,'product_cat')->name.', ';
        }
        $html = substr($html,0,strlen($html)-2);
        return $html;
    }

    public function getColorNames($category_mappings)
    {
        $html='';
        $category_mappings = unserialize($category_mappings);
        foreach ($category_mappings as $term_id) {
            $html.=get_term_by('id',$term_id,'pa_color')->name.', ';
        }
        $html = substr($html,0,strlen($html)-2);
        return $html;
    }
}
