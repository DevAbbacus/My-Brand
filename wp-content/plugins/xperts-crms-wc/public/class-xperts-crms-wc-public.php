<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://techxperts.co.in/
 * @since      1.0.0
 *
 * @package    Xperts_Crms_Wc
 * @subpackage Xperts_Crms_Wc/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Xperts_Crms_Wc
 * @subpackage Xperts_Crms_Wc/public
 * @author     Rajeev <rajeev@techxperts.co.in>
 */
class Xperts_Crms_Wc_Public {

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
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

    }

    /**
     * Register the stylesheets for the public-facing side of the site.
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

        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/xperts-crms-wc-public.css', array(), $this->version, 'all' );

    }

    /**
     * Register the JavaScript for the public-facing side of the site.
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

        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/xperts-crms-wc-public.js', array( 'jquery' ), $this->version, false );

    }

    public function front_init()
    {
        require_once plugin_dir_path( __FILE__ ).'../vendor/autoload.php';

        if('get'===strtolower($_SERVER['REQUEST_METHOD']) && isset($_GET['xperts_api']) && $_GET['xperts_api']=='crms') {

        }

        if('post'===strtolower($_SERVER['REQUEST_METHOD']) && isset($_GET['xperts_api']) && $_GET['xperts_api']=='crms') {
            
            

           $fp = fopen(__DIR__.'/log.txt','a+');

            $body = file_get_contents( 'php://input');

           fwrite($fp,$body);

            if(!empty($body)){
                $crms_response = json_decode($body);

                if($crms_response->action->subject_type=='Item') {
                    if ($crms_response->action->action_type == 'destroy') {
                        if ($crms_response->action->subject->type == 'Product') {
                            $product_id = wc_get_product_id_by_sku($crms_response->action->subject->id);
                            if ($product_id) {
                                $product = wc_get_product($product_id);
                                $product->set_status('trash');
                                $product->save();
                            }
                        }
                    }

                    if ($crms_response->action->action_type == 'update' || $crms_response->action->action_type == 'create') {
                        if ($crms_response->action->subject->type == 'Product') {
                            if (isset($crms_response->action->subject->custom_fields->publish_on_my_brand) and $crms_response->action->subject->custom_fields->publish_on_my_brand == 'Yes') {
                                $product_id = wc_get_product_id_by_sku($crms_response->action->subject->id);
                                if ($product_id) {
                                    fwrite($fp, 'found sku');
                                    $this->saveProductFromHook($product_id, $crms_response->action->subject);
                                    $this->updateProductUrlsInCRMS($product_id);
                                } else {
                                    fwrite($fp, 'not found  sku');
                                    $product_id = wp_insert_post([
                                        'post_author' => 2,
                                        'post_title' => isset($crms_response->action->subject->custom_fields->product_en) ? $crms_response->action->subject->custom_fields->product_en : 'Missing title..',
                                        'post_status' => 'publish',
                                        'post_type' => 'product',
                                    ]);
                                    if (!is_wp_error($product_id)) {
                                        fwrite($fp, 'no error');
                                        $this->saveProductFromHook($product_id, $crms_response->action->subject);
                                        $this->updateProductUrlsInCRMS($product_id);
                                    } else {
                                         $fp = fopen(__DIR__.'/log.txt','a+');
                                        fwrite($fp, print_r($product_id, true));
                                        fclose($fp);
                                    }
                                }
                            }
                        }

                    }
                }
            }

            fclose($fp);
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
            
        } //Ends IF
    }

    private function getProductColorsFromCustomFields($custom_fields)
    {
        $term_ids = [];
        global $wpdb;
        $color_mappings = $wpdb->get_results("select * from {$wpdb->prefix}crms_color_map ");
        foreach ($color_mappings as $color_map)
        {
            if(isset($custom_fields->{$color_map->crms_color}) and $custom_fields->{$color_map->crms_color}=='Yes'){
                $terms = unserialize($color_map->color_mappings);
                $term_ids = array_merge($term_ids, $terms);
            }
        }
        $term_ids = array_unique($term_ids);
        return $term_ids;
    }

    private function getProductTermsFromCustomFields($custom_fields)
    {
        $term_ids = [];
        global $wpdb;
        $category_mappings = $wpdb->get_results("select * from {$wpdb->prefix}crms_category_map ");
        foreach ($category_mappings as $category_map)
        {
            if(isset($custom_fields->{$category_map->crms_category}) and $custom_fields->{$category_map->crms_category}=='Yes'){
                $terms = unserialize($category_map->category_mappings);
                $term_ids = array_merge($term_ids, $terms);
            }
        }
        $term_ids = array_unique($term_ids);
        return $term_ids;
    }

    private function saveProductFromHook($product_id, $subject)
    {
        $product = wc_get_product($product_id);
        $product->set_sku($subject->id);
        if(isset($subject->rental_rate) and isset($subject->rental_rate->price)) {
            $product->set_price(floatval($subject->rental_rate->price));
            $product->set_regular_price(floatval($subject->rental_rate->price));
        }

        if(isset($subject->custom_fields->product_en)) {
            $product->set_name($subject->custom_fields->product_en);
        }
        if(isset($subject->custom_fields->{'e-shop_description_en'})) {
            $product->set_short_description($subject->custom_fields->{'e-shop_description_en'});
        }
        if(isset($subject->custom_fields->gewicht_product_kg)) {
            $product->set_weight($subject->custom_fields->gewicht_product_kg);
        }
        if(isset($subject->custom_fields->hoogte_product_cm)) {
            $product->set_height($subject->custom_fields->hoogte_product_cm);
        }
        if(isset($subject->custom_fields->breedte_product_cm)) {
            $product->set_width($subject->custom_fields->breedte_product_cm);
        }
        if(isset($subject->custom_fields->lengte_product_cm)) {
            $product->set_length($subject->custom_fields->lengte_product_cm);
        }
        $product->save();

        /**
         * Set Categories
         */
        $terms = $this->getProductTermsFromCustomFields($subject->custom_fields);
        wp_set_post_terms( $product_id, $terms, 'product_cat');

        /**
         * Set Colors
         */
        $colors = $this->getProductColorsFromCustomFields($subject->custom_fields);

        wp_set_object_terms( $product_id, $colors, 'pa_color', true );
        $product_attributes = [];
        foreach ($colors as $color){
            $product_attributes['pa_color'] = [
                'name' => 'pa_color',
                'value'        => get_term($color,'pa_color')->slug,
                'position'     => '',
                'is_visible'   => 0,
                'is_variation' => 1,
                'is_taxonomy'  => 1
            ];
        }
        update_post_meta( $product_id, '_product_attributes', $product_attributes );

        //https://wpml.org/documentation/support/wpml-coding-api/wpml-hooks-reference/#hook-605256
        $product_id_fr = apply_filters( 'wpml_object_id', $product_id, 'product', FALSE, 'fr' );
        if($product_id_fr) {
            $product_fr = wc_get_product($product_id_fr);
            if(isset($subject->custom_fields->product_fr)) {
                $product_fr->set_name($subject->custom_fields->product_fr);
            }
            if(isset($subject->custom_fields->{'e-shop_description_fr'})) {
                $product_fr->set_short_description($subject->custom_fields->{'e-shop_description_fr'});
            }
            $product_fr->save();
        } else {
            $product_id_fr = $this->mwm_wpml_translate_post($product_id,'product','fr');
            $product_fr = wc_get_product($product_id_fr);
            if(isset($subject->custom_fields->product_fr)) {
                $product_fr->set_name($subject->custom_fields->product_fr);
            }
            if(isset($subject->custom_fields->{'e-shop_description_fr'})) {
                $product_fr->set_short_description($subject->custom_fields->{'e-shop_description_fr'});
            }
            $product_fr->save();
        }

        $product_id_nl = apply_filters( 'wpml_object_id', $product_id, 'product', FALSE, 'nl' );
        if($product_id_nl) {
            $product_nl = wc_get_product($product_id_nl);
            if(isset($subject->name)) {
                $product_nl->set_name($subject->name);
            }
            if(isset($subject->custom_fields->{'e-shop_description_nl'})) {
                $product_nl->set_short_description($subject->custom_fields->{'e-shop_description_nl'});
            }
            $product_nl->save();
        } else {
            $product_id_nl = $this->mwm_wpml_translate_post($product_id,'product','nl');
            $product_nl = wc_get_product($product_id_nl);
            if(isset($subject->name)) {
                $product_nl->set_name($subject->name);
            }
            if(isset($subject->custom_fields->{'e-shop_description_nl'})) {
                $product_nl->set_short_description($subject->custom_fields->{'e-shop_description_nl'});
            }
            $product_nl->save();
        }
        
        //Attache image
        //$fp = fopen(__DIR__.'/log.txt','a+');
        //Attache image
        if(!get_post_thumbnail_id($product_id))
        {
            //fwrite($fp,'inside get_post_thumbnail_id');
            global $wpdb;
            $thumb_id = $wpdb->get_row($wpdb->prepare("select * from {$wpdb->prefix}posts where post_type='attachment' and (guid like %s or guid like %s) limit 1 ",'%'.$subject->id.'.png%','%'.$subject->id.'.jpg%'));
            if($thumb_id)
            {
                //fwrite($fp,'inside $thumb_id');
                set_post_thumbnail($product_id, $thumb_id->ID);
                set_post_thumbnail($product_id_nl, $thumb_id->ID);
                set_post_thumbnail($product_id_fr, $thumb_id->ID);
            }
        }
        //fclose($fp);
        
    }

    function mwm_wpml_translate_post( $post_id, $post_type, $lang ){

        global $sitepress;

        $def_trid = $sitepress->get_element_trid($post_id);
        // Insert translated post
        $post_translated_title = get_post( $post_id )->post_title . ' (' . $lang . ')';
        $post_translated_id = wp_insert_post( array( 'post_title' => $post_translated_title, 'post_type' => $post_type ,'post_status'=>'publish' ) );

        $sitepress->set_element_language_details($post_translated_id, 'post_product', $def_trid, $lang);

        // Return translated post ID
        return $post_translated_id;
    }

    private function updateProductUrlsInCRMS($product_id)
    {
        $product = wc_get_product($product_id);

        $client = new GuzzleHttp\Client(
            [
                'base_uri' => CRMS_API_URL,
                'headers' => [
                    'X-SUBDOMAIN' => CRMS_SUBDOMAIN,
                    'X-AUTH-TOKEN' => CRMS_API_KEY
                ]
            ]
        );
        
        $permalink = $product->get_permalink();

        $response = $client->request('PUT', 'products/'.$product->get_sku(),[
            'json' => [
                'product' => [
                    'custom_fields' => [
                        'link_url_item_en_mybrand' => $product->get_permalink(),
                        'link_url_item_nl_mybrand' => apply_filters( 'wpml_permalink', $permalink, 'nl' ),
                        'link_url_item_fr_mybrand' => apply_filters( 'wpml_permalink', $permalink, 'fr' ),
                    ],
                ]
            ]
        ]);
    }

}
