<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://www.beyranplugins.ir/
 * @since      1.0.0
 *
 * @package    digi-scraper/admin/
 * @subpackage product-parsing/
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @since      1.0.0
 * @package    digi-scraper
 * @subpackage digi-scraper/includes
 * @author     rahim beiranvand <rbkhoram701@gmail.com>
 */

class Digi_scraper_extended_Admin {

    use Digi_list_parser;

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * wordpress background process.
     *
     * @since    1.0.0
     * @access   private
     * @var      object $backend_process from Digi_Background_process class.
     */
    protected $backend_process;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version The version of this plugin.
     * @since    1.0.0
     */
    function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->backend_process = new Digi_Background_process();

    }

     /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    function enqueue_styles() {

        $page = filter_input(INPUT_GET, 'page');
        if ((isset($page) && !empty($page) && 'woo-digi-scraper' === $page) || (isset($page) && !empty($page) && 'woo-digi-scraps' === $page)) {
            wp_enqueue_style($this->plugin_name . 'switcher', DGSCRAPER_PLUGIN_URL . 'admin/css/switcher.css', array(), $this->version, 'all');
            wp_enqueue_style($this->plugin_name.'tipitip', DGSCRAPER_PLUGIN_URL . 'admin/css/tipitip.css', array(), $this->version, 'all');
            wp_enqueue_style($this->plugin_name.'bootstrap', DGSCRAPER_PLUGIN_URL . 'admin/css/bootstrap.css', array(), $this->version, 'all');
            wp_enqueue_style($this->plugin_name . 'chosen', DGSCRAPER_PLUGIN_URL . 'admin/css/chosen.css', array(), $this->version, 'all');
            wp_enqueue_style($this->plugin_name . 'fontawesome', DGSCRAPER_PLUGIN_URL . 'admin/css/font-awesome/css/font-awesome.min.css', array(), '4.5', 'all');
            wp_enqueue_style('dgscss', DGSCRAPER_PLUGIN_URL . 'admin/css/dgscss-21.css', array(), $this->version, 'all');
        }
        if (isset($page) && !empty($page) && 'woo-digi-scraps' === $page)
        {
            wp_enqueue_style('dgsmanager', DGSCRAPER_PLUGIN_URL . 'admin/css/dgsmanager-21.css', array(), $this->version, 'all');
        }
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    function enqueue_scripts() {
        $page = filter_input(INPUT_GET, 'page');
        if ((isset($page) && !empty($page) && 'woo-digi-scraper' === $page) || (isset($page) && !empty($page) && 'woo-digi-scraps' === $page)) {
            wp_enqueue_script($this->plugin_name, DGSCRAPER_PLUGIN_URL . 'admin/js/jquery.easing.min.js', array('jquery'), $this->version, true);
            wp_enqueue_script('wp-pointer'); //enqueue script for notice pointer
            wp_enqueue_style('wp-jquery-ui-dialog');
            wp_enqueue_script('j-switcher', DGSCRAPER_PLUGIN_URL . 'admin/js/jquery.switcher.min.js', array('jquery'), $this->version, true);
            wp_enqueue_script('j-chosen', DGSCRAPER_PLUGIN_URL . 'admin/js/chosen.jquery.js', array('jquery'), $this->version, true);
            wp_enqueue_script('j-tipitip', DGSCRAPER_PLUGIN_URL . 'admin/js/tipitip.min.js', array('jquery'), $this->version, true);
            wp_enqueue_script('j-bootstrap', DGSCRAPER_PLUGIN_URL . 'admin/js/bootstrap.min.js', array('jquery'), $this->version, true);
            wp_enqueue_script('j-angular', 'https://ajax.googleapis.com/ajax/libs/angularjs/1.6.9/angular.min.js', array(), $this->version, true);
            wp_enqueue_script('j-angular-santize', 'https://ajax.googleapis.com/ajax/libs/angularjs/1.6.9/angular-sanitize.js', array(), $this->version, true);
            wp_enqueue_script('js-dgsjs', DGSCRAPER_PLUGIN_URL . 'admin/js/dgsjs-21.js', array('jquery'), $this->version, true);

            $localize_arr = array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'loader_url' => DGSCRAPER_PLUGIN_URL.'admin/images/fancybox_loading_2x.gif',
                'fetch_products_wait_msg' => esc_html__('Please wait while the products are loading !!', 'woocommerce-quick-cart-for-multiple-variations'),
                'wait_msg' => esc_html__('Please wait...', 'woocommerce-quick-cart-for-multiple-variations')
            );
            wp_localize_script('js-dgsjs', 'DGscraper_js_object', $localize_arr);
        }
    }

// Function for welocme screen page

    function welcome_dg_scraper_screen_do_activation_redirect() {

        if (!get_transient('_welcome_screen_DG_scraper_redirect_data')) {
            return;
        }
// Delete the redirect transient
        delete_transient('_welcome_screen_DG_scraper_redirect_data');
// if activating from network, or bulk
        if (is_network_admin() || isset($_GET['activate-multi'])) {
            return;
        }
// Redirect to extra cost welcome  page
        wp_safe_redirect(html_entity_decode(add_query_arg(array('page' => 'woo-digi-scraper&tab=dgscraper_new'), admin_url('admin.php'))));
    }

    /**
     * Digiscraper menu.
     */
// custom menu for digi scraper menu

    public function digi_scraper_menu_traking_fbg() {
        global $GLOBALS;
        if (empty($GLOBALS['admin_page_hooks']['woo-dgscraper'])) {
            add_menu_page(
                esc_html__('دیجی اسکراپر', 'woo-digi-scraper'), esc_html__('دیجی اسکراپر', 'woo-digi-scraper'), null, 'woo-dgscraper', array($this, 'digi_menu_customer_io'), DGSCRAPER_PLUGIN_URL . 'admin/images/menu-icon.png', 25
            );
        }
    }


    // custom submenu for extra flate rate shipping

    public function add_new_menu_items_traking_fbg() {
        add_submenu_page("woo-dgscraper", esc_html__('دیجی اسکراپر/جدید', 'woo-digi-scraper'), esc_html__('اسکراپ جدید', 'woo-digi-scraper'), "manage_options", "woo-digi-scraper", 'config_scrap_process');
        add_submenu_page("woo-dgscraper", esc_html__('مدیریت اسکراپ ها', 'woo-digi-scraper'), esc_html__('مدیریت اسکراپ ها', 'woo-digi-scraper'), "manage_options", "woo-digi-scraps", 'woo_digi_scraps_managment');

        function config_scrap_process() {

            $url = admin_url('admin.php?page=woo-digi-scraper&tab=dgscraper_new');
            $tab = filter_input(INPUT_GET, 'tab');
            //include_once('partials/header/plugin-header.php');
            if (!empty($tab)) {
                if ('dgscraper_new' === $tab) dgscraper_new_config();
                /*if ('wqcmv_variant_purchase_extended_get_started_method' === $tab) get_started_dots_plugin_settings();
                if ('introduction_variant_extended' === $tab) introduction_variant_extended();*/
            } else {
                wp_redirect($url);
                exit;
            }
            //include_once('partials/header/plugin-sidebar.php');
        }

    }

    function is_url($url)
    {
        $response = array();
        //Check if URL is empty
        if (!empty($url)) {
            $response = get_headers($url);
        }
        return (bool)in_array("HTTP/1.1 200 OK", $response, true);
    }

    /**
     * ajax action for get scraping linls how user enter it.
     *
     * @return nothis but save links
     * @since    1.0.0
     */
    function digi_get_user_scraping_links() {
        $action = filter_input(INPUT_POST, 'action');
        if (isset($action) && 'digi_get_user_scraping_links' === $action) {

            if (!check_ajax_referer( 'scraping_link','nonce' )){
                wp_die();
            }

            if(!empty($_POST['p_single_links']))
            {
                $singleLinks = wp_strip_all_tags($_POST['p_single_links']);
                $singleLinks_split = explode(',',$singleLinks);
                foreach ($singleLinks_split as $single_link)
                {
                    $link_split = explode("/",$single_link);
                    $l_part2 = $link_split[2];
                    if($l_part2 != 'www.digikala.com')
                    {
                        $result = array(
                            'single_invalid' => 1,
                        );
                        wp_send_json_success($result);
                    }
                }
                update_option('p_single_links',$singleLinks);
            }
            else
            {
                delete_option('p_single_links');
            }
            if(!empty($_POST['p_list_links']))
            {
                $listLinks = wp_strip_all_tags($_POST['p_list_links']);
                $listLinks_split = explode(',',$listLinks);
                foreach ($listLinks_split as $list_link)
                {
                    $link_split = explode("/",$list_link);
                    $l_part2 = $link_split[2];
                    if($l_part2 != 'www.digikala.com')
                    {
                        $result = array(
                            'list_invalid' => 1,
                        );
                        wp_send_json_success($result);
                    }
                }
                update_option('p_list_links',$listLinks);
            }
            else
            {
                delete_option('p_list_links');
            }
            if(empty($_POST['p_single_links']) && empty($_POST['p_list_links']))
            {
                $result = array(
                    'empty_links' => 1,
                );
                wp_send_json_error($result);
                wp_die();
            }
            $result = array(
                'links_is_ok' => 1,
            );
            wp_send_json_success($result);
            //wp_send_json_error();
            wp_die();
        }
    }

    /**
     * ajax action for set scraping configuratin what product parameter get within scraping .
     *
     * @return save config option
     * @since    1.0.0
     */
    function digi_get_user_scraping_config() {
        $action = filter_input(INPUT_POST, 'action');
        if (isset($action) && 'digi_get_user_scraping_config' === $action) {

            if (!check_ajax_referer( 'scraping_conf','nonce' )){
                wp_die();
            }

            $index_image_opt = $_POST['index_image_opt'];
            $gallery_image_opt = $_POST['gallery_image_opt'];
            $count_of_gallery_image_opt = $_POST['count_of_gallery_image_opt'];
            $remove_watermark_image_opt = $_POST['remove_watermark_image_opt'];
            $product_price_opt = $_POST['product_price_opt'];
            $short_description_opt = $_POST['short_description_opt'];
            $description_opt = $_POST['description_opt'];
            $properties_tab_opt = $_POST['properties_tab_opt'];
            $important_properties_opt = $_POST['important_properties_opt'];
            $properties_opt = $_POST['properties_opt'];
            $product_code_opt = $_POST['product_code_opt'];
            $update_future_price_opt = $_POST['update_future_price_opt'];


             //setup our option values
            $digi_config_options = array(
                "get_product_name" => 1,
                "get_product_index_image" => $index_image_opt,
                "get_product_gallery_image" => $gallery_image_opt,
                "count_of_scrap_gallery_image" => $count_of_gallery_image_opt,
                "remove_watermark_image" => $remove_watermark_image_opt,
                "get_product_price" => $product_price_opt,
                "get_product_excerpt" => $short_description_opt,
                "get_product_description" => $description_opt,
                "get_product_properties" => $properties_opt,
                "get_product_properties_tab_info" => $properties_tab_opt,
                "get_product_important_properties" => $important_properties_opt,
                "get_product_code" => $product_code_opt,
                "create_marketing_link" => 1,
                "future_price_update" => $update_future_price_opt,
            );

            update_option('digi_scraper_options', $digi_config_options);

            $result = array(
                'config_is_ok' => 1,
            );
            wp_send_json_success($result);
            wp_die();
        }
    }

    /**
     * ajax action for set import configuratin and start scraping  .
     *
     * @return scrap and import product to woocommerce from other site
     * @since    1.0.0
     */
    function digi_start_scrap() {
        $action = filter_input(INPUT_POST, 'action');
        if (isset($action) && 'digi_start_scrap' === $action) {

            if (!check_ajax_referer( 'scraping_imp','nonce' )){
                wp_die();
            }

            $before_title = '';
            $after_title = '';
            $dgs_scrape_title_regex_finds =  '';
            $dgs_scrape_title_regex_replaces = '';
            $dgs_product_tags = '';
            $dgs_scrape_content_regex_finds =  '';
            $dgs_scrape_content_regex_replaces = '';

            $marketing_enable = $_POST['marketing_enable'];
            $marketing_link_value = $_POST['marketing_link_value'];
            $show_featured_image_directly = $_POST['show_featured_image_directly'];
            $show_gallery_image_directly = $_POST['show_gallery_image_directly'];
             if(isset($_POST['selected_category']))
            {
                $selected_category = $_POST['selected_category'];
            }
            else
            {
                $selected_category = '';
            }
            $products_status = $_POST['products_status'];
            $import_speed = $_POST['import_speed'];
            if(isset($_POST['before_title']))
            {
                $before_title = $_POST['before_title'];
            }
            if(isset($_POST['after_title']))
            {
                $after_title = $_POST['after_title'];
            }
            if(isset($_POST['dgs_scrape_title_regex_finds']))
            {
                $dgs_scrape_title_regex_finds =  $_POST['dgs_scrape_title_regex_finds'];
            }
            if(isset($_POST['dgs_scrape_title_regex_replaces']))
            {
                $dgs_scrape_title_regex_replaces = $_POST['dgs_scrape_title_regex_replaces'];
            }
            if(isset($_POST['dgs_product_tags']))
            {
                $dgs_product_tags = $_POST['dgs_product_tags'];
            }
            if(isset($_POST['dgs_scrape_content_regex_finds']))
            {
                $dgs_scrape_content_regex_finds =  $_POST['dgs_scrape_content_regex_finds'];
            }
            if(isset($_POST['dgs_scrape_content_regex_replaces']))
            {
                $dgs_scrape_content_regex_replaces = $_POST['dgs_scrape_content_regex_replaces'];
            }

            //setup our option values
            $import_options=array(
                "create_marketing_link" => $marketing_enable,
                "marketing_link_const" => $marketing_link_value,
                "show_featured_image_directly" => $show_featured_image_directly,
                "show_gallery_image_directly" => $show_gallery_image_directly,
                "import_product_to_categories" => $selected_category,
                "digi_scrap_speed" => $import_speed,
                "product_imported_status" => $products_status,
                "before_title" => $before_title,
                "after_title" => $after_title,
                "dgs_scrape_title_regex_finds" => $dgs_scrape_title_regex_finds,
                "dgs_scrape_title_regex_replaces" => $dgs_scrape_title_regex_replaces,
                "dgs_product_tags" => $dgs_product_tags,
                "dgs_scrape_content_regex_finds" => $dgs_scrape_content_regex_finds,
                "dgs_scrape_content_regex_replaces" => $dgs_scrape_content_regex_replaces,
            );

            update_option('digi_import_options', $import_options);

            $result = array(
                'config_is_ok' => 1,
            );
            wp_send_json_success($result);
            wp_die();
        }
    }

    /**
     * ajax action for start scraping and importing product .
     *
     * @return scrap and import product to woocommerce from other site
     * @since    1.0.0
     */
    function digi_start_import() {
        $action = filter_input(INPUT_POST, 'action');
        if (isset($action) && 'digi_start_import' === $action) {

            if (!check_ajax_referer( 'scraping_imp','nonce' )){
                wp_die();
            }

            $clear = get_option('clear_sync');
            if($clear)
            {
                // Remove the "clear" flag we had manually set
                delete_option('clear_sync');
            }

            if(get_option('registered_scrap_task')) {

                $option_names = get_option('registered_scrap_task', true);
                foreach ($option_names as $option_name) {
                    if (get_option($option_name)) {
                        $scrap_task = get_option($option_name, true);
                        $scrap_name = $scrap_task['scrap-name'];
                        $running = $scrap_task['running'];
                        if ($running == 1)
                        {
                            if (in_array($scrap_name, $option_names))
                            {
                                unset($option_names[array_search($scrap_name,$option_names)]);
                                $updated_option = array_values($option_names);
                                update_option('registered_scrap_task', $updated_option);
                            }
                            delete_option($scrap_name);
                        }
                    }
                }
            }

            /*delete old batch*/

            global $wpdb;
            $table = $wpdb->options;
            $sql = "DELETE from $table WHERE option_name LIKE '%digi_backend_process%'";
            $deleted = $wpdb->query( $sql );

            $product_single_links = array();
            $product_list_links = array();
            $list_links = array();

            if(get_option('p_single_links'))
            {
                $single_links = get_option('p_single_links',true);
                $product_single_links = explode(",",$single_links);
            }
            if(get_option('p_list_links'))
            {
                $archive_links = explode(",",get_option('p_list_links',true));
                $product_link = array();
                foreach ($archive_links as $archive_link)
                {
                    array_push($product_list_links , $this->get_archive_products_link($archive_link));
                }

                foreach ($product_list_links as $product_list_link)
                {
                    foreach ($product_list_link as $link)
                    {
                        array_push($list_links , $link);
                    }
                }
            }

            $product_links = array_merge($list_links,$product_single_links);


            $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
            $scrap_name = 'scrap-'.substr(str_shuffle($permitted_chars), 0, 4);

            $number_of_products = count($product_links);
            $scrap_detailes = get_option('progress_status_details', true);
            $scrap_detailes['scrap-name'] = $scrap_name;
            $scrap_detailes['running'] = 1;
            $scrap_detailes['number_of_products_in_the_queue'] = $number_of_products;
            $scrap_detailes['number_of_products_imported'] = 0;
            $scrap_detailes['product_name_being_imported'] = '';
            $scrap_detailes['rate_of_progress'] = 1;
            update_option('progress_status_details',$scrap_detailes);


            $scrap_product_codes = array();
            foreach ( $product_links as $product_link ) {
                $this->backend_process->push_to_queue( $product_link );
                $link_code = explode("/",$product_link);
                $product_code = $link_code[4];
                array_push($scrap_product_codes, $product_code);
            }
            $scrap_config_options = get_option('digi_scraper_options',true);
            $scrap_import_options = get_option('digi_import_options',true);
            $new_scrap_task = array(
                'scrap-name' => $scrap_name,
                'running' => 1,
                'product_codes' => $scrap_product_codes,
                'scrap-config-options' => $scrap_config_options,
                'scrap-import-options' => $scrap_import_options,
                'number-of-product-in-queue' => $number_of_products,
                'number-of-product-imported' => 0,
                'rate-of-progress' => 0,
                'last-execute-time' => '',
                'scrap-completed' => 0,
            );

            if(get_option('registered_scrap_task'))
            {
                $previous_task = get_option('registered_scrap_task', true);
                array_push($previous_task , $scrap_name);
                update_option('registered_scrap_task', $previous_task);
            }
            else
            {
                $new_task = array($scrap_name);
                update_option('registered_scrap_task', $new_task);
            }

            add_option($scrap_name , $new_scrap_task);

            $this->backend_process->save()->dispatch();

            $result = array(
                'import_is_ok' => 1,
            );
            wp_send_json_success($result);
            wp_die();
        }
    }

    /**
     * ajax action for check import and scraping progress status .
     *
     * @return return scrap progress step
     * @since    1.0.0
     */
    function digi_progress_status_checker() {
        $action = filter_input(INPUT_POST, 'action');
        if (isset($action) && 'digi_progress_status_checker' === $action) {

            if (!check_ajax_referer( 'scraping_imp','nonce' )){
                wp_die();
            }

            $scrap_detailes = get_option('progress_status_details', true);
            $progress_runinng = $scrap_detailes['running'];
            $importing_product_name = $scrap_detailes['product_name_being_imported'];
            $importing_product_name = ltrim($importing_product_name);
            $importing_product_name = rtrim($importing_product_name);
            $importing_product_name = substr($importing_product_name,0,49);
            $rate_of_progress = $scrap_detailes['rate_of_progress'].'%';
            $number_of_products_imported = $scrap_detailes['number_of_products_imported'];
            $result = array(
                'progress_runinng' => $progress_runinng,
                'importing_product_name' => $importing_product_name,
                'rate_of_progress' => $rate_of_progress,
                'number_of_products_imported' => $number_of_products_imported,
            );
            wp_send_json_success($result);
            wp_die();
        }
    }


    /**
     * ajax action for stop import and scraping process .
     *
     * @return return scrap progress step
     * @since    1.0.0
     */
    function digi_stop_import() {
        $action = filter_input(INPUT_POST, 'action');
        if (isset($action) && 'digi_stop_import' === $action) {

            if (!check_ajax_referer( 'scraping_imp','nonce' )){
                wp_die();
            }

            $scrap_detailes = get_option('progress_status_details', true);
            $scrap_detailes['running'] = 0;
            update_option('progress_status_details' , $scrap_detailes);

            $this->backend_process->cancel_proce();

            $result = array(
                'stop-process' => 1,
            );
            wp_send_json_success($result);
            wp_die();
        }
    }

    /**
     * ajax action for remove scrap information in database .
     *
     * @return return boolean
     * @since    1.0.0
     */
    function digi_remove_scrap() {
        $action = filter_input(INPUT_POST, 'action');
        if (isset($action) && 'digi_remove_scrap' === $action) {

            if(isset($_POST['scrap_name']))
            {
                $scrap_name = $_POST['scrap_name'];
                $option_names = get_option('registered_scrap_task', true);
                if (in_array($scrap_name, $option_names))
                {
                    unset($option_names[array_search($scrap_name,$option_names)]);
                    $updated_option = array_values($option_names);
                    update_option('registered_scrap_task', $updated_option);
                }
                delete_option($scrap_name);
            }
            $result = array(
                'remove' => 1,
            );
            wp_send_json_success($result);
            wp_die();
        }
    }


    /**
     * ajax action for resume stoped scrap .
     *
     * @return return redirect to new scrap page
     * @since    1.0.0
     */
    function digi_resume_scrap() {
        $action = filter_input(INPUT_POST, 'action');
        if (isset($action) && 'digi_resume_scrap' === $action) {

           $status_other_task = get_option('progress_status_details', true);
           if($status_other_task['running'] == 1)
           {
               $result = array(
                       'other_task_running' => 1,
               );
               wp_send_json_success($result);
           }
            $url = admin_url('admin.php?page=woo-digi-scraps');
            if(isset($_POST['scrap_name']))
            {

                $scrap_name = $_POST['scrap_name'];

                $current_scrap_property = get_option($scrap_name, true);

                $scrap_config_options = $current_scrap_property['scrap-config-options'];
                $scrap_import_options = $current_scrap_property['scrap-import-options'];
                update_option('digi_scraper_options',$scrap_config_options);
                update_option('digi_import_options',$scrap_import_options);

                /*updtae progress status*/

                $current_scrap_state = array(
                    "scrap-name" => $scrap_name,
                    "running" => 1,
                    "number_of_products_in_the_queue" => $current_scrap_property['number-of-product-in-queue'],
                    "number_of_products_imported" => $current_scrap_property['number-of-product-imported'],
                    "rate_of_progress" => $current_scrap_property['rate-of-progress'],
                    "product_name_being_imported" => '',
                );

                update_option('progress_status_details', $current_scrap_state);

                $num_of_imported = $current_scrap_property['number-of-product-imported'];

                /*change running value in current scrap*/
                $current_scrap_property['running'] = 1;
                update_option($scrap_name, $current_scrap_property);


                /*generate product link and push to the queue*/

                $product_codes = $current_scrap_property['product_codes'];

                $step_index = 1;
                foreach($product_codes as $product_code)
                {
                    if($step_index > $num_of_imported)
                    {
                        $new_link = 'https://www.digikala.com/product/'.$product_code;
                        $this->backend_process->push_to_queue( $new_link );
                    }

                    $step_index++;

                }
                $this->backend_process->save()->dispatch();

                $url = admin_url('admin.php?page=woo-digi-scraper&tab=dgscraper_new');

            }
            $result = array(
                'redirect_link' => $url,
            );
            wp_send_json_success($result);
            wp_die();
        }
    }

}

/**
 * Function for add custom pointer
 * @return unknown
 */
function dgscraper_new_config() {

    $scrap_detailes = get_option('progress_status_details', true);
    $progress_runinng = $scrap_detailes['running'];
    $importing_product_name = $scrap_detailes['product_name_being_imported'];
    $importing_product_name = ltrim($importing_product_name);
    $importing_product_name = rtrim($importing_product_name);
    $importing_product_name = substr($importing_product_name,0,49);
    $rate_of_progress = $scrap_detailes['rate_of_progress'].'%';
    $progress_text = 'درحال درون ریزی است !';
    if($progress_runinng == 1)
    {
        $task_name = $scrap_detailes['scrap-name'];
        if (get_option($task_name)) {
            $current_task = get_option($task_name, true);
            if (array_key_exists("schedule_task_options", $current_task)) {
               $progress_text = 'درحال به روزرسانی است !';
            }
            else
            {
                $progress_text = 'درحال درون ریزی است !';
            }
        }
    }



    ?>

    <div id="overlay" class="cover <?php if($progress_runinng == 1){echo 'blur-in';}  ?>">
     <div class="dgscraper-main-container">
        <h2 class="dgscraper-wp-notices" style="margin-top: 0px;"></h2>
        <div class="dgscraper-header">
            <div class="dgscraper-logo"></div>
            <div class="dgscraper-title">
                <p>دیجی اسکراپر</p>
                <h4>اسکراپ و درون ریزی محصولات از سایت های بزرگ</h4>
            </div>
            <div class="dgscraper-links">
                <a href="https://www.zhaket.com/store/web/httpmarketwpirstore" target="_blank">پشتیبانی</a> | <a href="https://www.zhaket.com/store/web/httpmarketwpirstore" target="_blank">مستندات</a>
            </div>
        </div>
        <div class="dgscraper-body" >

            <!-- multistep form -->
            <form id="msform">
                <!-- progressbar -->
                <ul id="progressbar">
                    <li class="active">لینک محصولات</li>
                    <li>تنظیمات اسکراپ</li>
                    <li>درون ریزی</li>
                </ul>
                <!-- fieldsets -->

                <?php
                if(get_option('p_single_links'))
                {
                    $single_links = get_option('p_single_links',true);
                }
                if(get_option('p_list_links'))
                {
                    $list_links = get_option('p_list_links',true);
                }



                ?>

                <fieldset data-section="scraping_links">
                    <h2 class="fs-title">ابتدا ، لینک محصولاتی را که میخواهید اسکراپ کنید را وارد کنید.</h2>
                    <div class="product-single-link">
                        <div class="label-link">لینک محصولات :</div>
                        <textarea rows="10" cols="80" name="p-single-links" id="p-single-links" placeholder="لینک محصولات رو با کاما از هم تفکیک کنید : https://www.digikala.com/product/dkp-100989,https://www.digikala.com/product/dkp-100147" style="width: 100%; margin-bottom: 15px;"><?php
                            if(isset($single_links))
                            {
                                echo $single_links;
                            }
                            ?></textarea>
                    </div>
                    <div class="product-list-link">
                        <div class="label-link">لینک لیستی از محصولات :</div>
                        <textarea rows="2" cols="80" name="p-list-links" id="p-list-links" placeholder="لینک دسته ای از محصولات دیجیکالا رو مانند نمونه وارد کنید : https://www.digikala.com/search/category-men-shirts/" style="width: 100%; margin-bottom: 15px;"><?php if(isset($list_links))
                             {
                                 echo $list_links;
                             }
                             ?></textarea>
                    </div>
                    <?php if( Zhaket_Guard_DGS::is_activated() === true ) {
                        // License is activated
                       ?>
                    <input type="button" name="next" class="next action-button" value="تنظیمات اسکراپ" />
                    <?php }
                    else
                    {
                        ?>
                        <div class="error-msg" id="info-msg">
                            <i class="fa fa-info-circle"></i>
                           افزونه نیاز به فعالسازی دارد!
                        </div>
                        <?php
                    }
                    ?>
                    <?php wp_nonce_field( 'scraping_link','scraping_link_nonce' ); ?>

                    <div class="overlay_scraping_links">
                        <div class="spinner_dgs">
                            <div class="spinner-container container1">
                                <div class="circle1"></div>
                                <div class="circle2"></div>
                                <div class="circle3"></div>
                                <div class="circle4"></div>
                            </div>
                            <div class="spinner-container container2">
                                <div class="circle1"></div>
                                <div class="circle2"></div>
                                <div class="circle3"></div>
                                <div class="circle4"></div>
                            </div>

                        </div>
                    </div>

                </fieldset>
                <fieldset data-section="scraping_config">
                    <h2 class="fs-title">مولفه هایی از محصول که میخواهید اسکراپ کنید ، را مشخص کنید.</h2>
                    <!--<h3 class="fs-subtitle">Your presence on the social network</h3>-->
                    <div class="input scrap-configuration-fields">
                        <?php

                        if(get_option('digi_scraper_options'))
                        {
                           $scraping_config_options = get_option('digi_scraper_options', true);
                        }
                        ?>
                        <div class="first-coulmn">
                            <div class="scrap-configuration-field">
                                <div class="field-title">
                                    <label class="scrap-configuration-field-label">اسکراپ تصویر اصلی</label>
                                    <a class="tipitip-trigger dgscraper-help" data-position="north-west" data-class="dgs-tooltip"
                                       data-content="با فعالسازی این گزینه تصویر اصلی محصول اسکراپ شده و به عنوان تصویر شاخص محصول ووکامرسی شما درون ریزی میشود.">
                                        ?
                                    </a>
                                </div>
                                <div class="field-value">
                                    <div class="form-check form-check-inline dgs-checkbox">
                                        <input class="form-check-input" type="checkbox" name="scrap-index-image" id="scrap-index-image" value="<?php echo $scraping_config_options['get_product_index_image']; ?>" <?php checked( $scraping_config_options['get_product_index_image'], 1 ); ?> >
                                    </div>
                                </div>
                            </div>
                            <div class="scrap-configuration-field">
                                <div class="field-title">
                                    <label class="scrap-configuration-field-label">اسکراپ تصاویر گالری</label>
                                    <a class="tipitip-trigger dgscraper-help" data-position="north-west" data-class="dgs-tooltip"
                                       data-content="با فعالسازی این گزینه تصاویر گالری محصول اسکراپ شده و به عنوان تصاویر گالری محصول ووکامرسی شما درون ریزی میشود.">
                                        ?
                                    </a>
                                </div>
                                <div class="field-value">
                                    <div class="form-check form-check-inline dgs-checkbox">
                                        <input class="form-check-input" type="checkbox" name="scrap-gallery-image" id="scrap-gallery-image" value="<?php echo $scraping_config_options['get_product_gallery_image']; ?>" <?php checked( $scraping_config_options['get_product_gallery_image'], 1 ); ?> >
                                    </div>
                                </div>
                            </div>
                            <div class="scrap-configuration-field">
                                <div class="field-title">
                                    <label class="scrap-configuration-field-label">اسکراپ تب مشخصات محصول</label>
                                    <a class="tipitip-trigger dgscraper-help" data-position="north-west" data-class="dgs-tooltip"
                                       data-content="با فعالسازی این گزینه تب مشخصات محصول با حفظ ساختار هایپر تکست سایت مبدا به صورت یک تب جدید در محصول ووکامرس نمایش داده می شود ، با فعال بودن این گزینه ، گزینه ی اسکراپ مشخصات محصول غیرفعال در نظر گرفته می شود!">
                                        ?
                                    </a>
                                </div>
                                <div class="field-value">
                                    <div class="form-check form-check-inline dgs-checkbox">
                                        <input class="form-check-input" type="checkbox" name="scrap-properties-tab" id="scrap-properties-tab" value="<?php echo $scraping_config_options['get_product_properties_tab_info']; ?>" <?php checked( $scraping_config_options['get_product_properties_tab_info'], 1 ); ?> >
                                    </div>
                                </div>
                            </div>
                            <div class="scrap-configuration-field">
                                <div class="field-title">
                                    <label class="scrap-configuration-field-label"> اسکراپ ویژگی های اصلی محصول</label>
                                    <a class="tipitip-trigger dgscraper-help" data-position="north-west" data-class="dgs-tooltip"
                                       data-content="با فعالسازی این گزینه ویژگی های اصلی قبل از دکمه ی خرید محصول قرار خواهند گرفت!">
                                        ?
                                    </a>
                                </div>
                                <div class="field-value">
                                    <div class="form-check form-check-inline dgs-checkbox">
                                        <input class="form-check-input" type="checkbox" name="scrap-important-properties" id="scrap-important-properties" value="<?php echo $scraping_config_options['get_product_important_properties']; ?>" <?php checked( $scraping_config_options['get_product_important_properties'], 1 ); ?> >
                                    </div>
                                </div>
                            </div>
                            <div class="scrap-configuration-field">
                                <div class="field-title">
                                    <label class="scrap-configuration-field-label">حذف خودکار واتر مارک</label>
                                    <a class="tipitip-trigger dgscraper-help" data-position="north-west" data-class="dgs-tooltip"
                                       data-content="با فعالسازی این گزینه واتر مارک تصاویر به صورت خودکار حذف می شود، فعالسازی این قابلیت سرعت فرایند درون ریزی را کاهش می دهد.">
                                        ?
                                    </a>
                                </div>
                                <div class="field-value">
                                    <?php if (extension_loaded('imagick')){ ?>
                                    <div class="form-check form-check-inline dgs-checkbox">
                                        <input class="form-check-input" type="checkbox" name="remove-watermark-image" id="remove-watermark-image" value="<?php echo $scraping_config_options['remove_watermark_image']; ?>" <?php checked( $scraping_config_options['remove_watermark_image'], 1 ); ?> >
                                    </div>
                                   <?php }
                                    else
                                    {
                                        ?>
                                        <div class="error-msg" id="info-msg">
                                            <i class="fa fa-info-circle"></i>
                                           اکستنشن Imagick را فعال کنید.
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="scrap-configuration-field">
                                <div class="field-title">
                                    <label class="scrap-configuration-field-label">اسکراپ قیمت محصول</label>
                                    <a class="tipitip-trigger dgscraper-help" data-position="north-west" data-class="dgs-tooltip"
                                       data-content="با فعالسازی این گزینه قیمت محصول شما بر اساس قیمت محصول اسکراپ شده است ، پیشنهاد ما برای محصولات متغییر این است ، که قیمت ها اصلاح شوند.">
                                        ?
                                    </a>
                                </div>
                                <div class="field-value">
                                    <div class="form-check form-check-inline dgs-checkbox">
                                        <input class="form-check-input" type="checkbox" name="scrap-product-price" id="scrap-product-price" value="<?php echo $scraping_config_options['get_product_price']; ?>" <?php checked( $scraping_config_options['get_product_price'], 1 ); ?> >
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="second-coulmn">
                            <div class="scrap-configuration-field">
                                <div class="field-title">
                                    <label class="scrap-configuration-field-label">اسکراپ خلاصه توضیحات</label>
                                    <a class="tipitip-trigger dgscraper-help" data-position="north-west" data-class="dgs-tooltip"
                                       data-content="با فعالسازی این گزینه خلاصه توضیحات محصول اسکراپ شده و در توضیحات مختصر محصول ووکامرسی درون ریزی می شود.">
                                        ?
                                    </a>
                                </div>
                                <div class="field-value">
                                    <div class="form-check form-check-inline dgs-checkbox">
                                        <input class="form-check-input" type="checkbox" name="scrap-short-description" id="scrap-short-description" value="<?php echo $scraping_config_options['get_product_excerpt']; ?>" <?php checked( $scraping_config_options['get_product_excerpt'], 1 ); ?> >
                                    </div>
                                </div>
                            </div>

                            <div class="scrap-configuration-field">
                                <div class="field-title">
                                    <label class="scrap-configuration-field-label">اسکراپ توضیحات</label>
                                    <a class="tipitip-trigger dgscraper-help" data-position="north-west" data-class="dgs-tooltip"
                                       data-content="با فعالسازی این گزینه توضیحات محصول اسکراپ شده و در توضیحات محصول ووکامرسی درون ریزی می شود.">
                                        ?
                                    </a>
                                </div>
                                <div class="field-value">
                                    <div class="form-check form-check-inline dgs-checkbox">
                                        <input class="form-check-input" type="checkbox" name="scrap-description" id="scrap-description" value="<?php echo $scraping_config_options['get_product_description']; ?>" <?php checked( $scraping_config_options['get_product_description'], 1 ); ?> >
                                    </div>
                                </div>
                            </div>
                            <div class="scrap-configuration-field">
                                <div class="field-title">
                                    <label class="scrap-configuration-field-label">تعداد تصاویر گالری</label>
                                    <a class="tipitip-trigger dgscraper-help" data-position="north-west" data-class="dgs-tooltip"
                                       data-content="تعداد تصاویری که می خواهید از گالری تصاویر محصول اسکراپ شود را وارد کنید.">
                                        ?
                                    </a>
                                </div>
                                <div class="field-value">
                                    <div class="form-check form-check-inline dgs-checkbox">
                                        <input class="form-number-input" type="number" name="scrap-count-of-gallery-image" id="scrap-count-of-gallery-image" value="<?php echo $scraping_config_options['count_of_scrap_gallery_image']; ?>" >
                                    </div>
                                </div>
                            </div>
                            <div class="scrap-configuration-field">
                                <div class="field-title">
                                    <label class="scrap-configuration-field-label">اسکراپ مشخصات محصول</label>
                                    <a class="tipitip-trigger dgscraper-help" data-position="north-west" data-class="dgs-tooltip"
                                       data-content="با فعالسازی این گزینه مشخصات محصول اسکراپ شده و به عنوان ویژگی های محصول ووکامرسی درون ریزی می شود.">
                                        ?
                                    </a>
                                </div>
                                <div class="field-value">
                                    <div class="form-check form-check-inline dgs-checkbox">
                                        <input class="form-check-input" type="checkbox" name="scrap-properties" id="scrap-properties" value="<?php echo $scraping_config_options['get_product_properties']; ?>" <?php checked( $scraping_config_options['get_product_properties'], 1 ); ?>  >
                                    </div>
                                </div>
                            </div>
                            <div class="scrap-configuration-field">
                                <div class="field-title">
                                    <label class="scrap-configuration-field-label">اسکراپ کد محصول</label>
                                    <a class="tipitip-trigger dgscraper-help" data-position="north-west" data-class="dgs-tooltip"
                                       data-content="با فعالسازی این گزینه کد محصول اسکراپ شده و به عنوان کد SKU محصول ووکامرسی درون ریزی می شود.">
                                        ?
                                    </a>
                                </div>
                                <div class="field-value">
                                    <div class="form-check form-check-inline dgs-checkbox">
                                        <input class="form-check-input" type="checkbox" name="scrap-product-code" id="scrap-product-code" value="<?php echo $scraping_config_options['get_product_code']; ?>"  <?php checked( $scraping_config_options['get_product_code'], 1 ); ?> >
                                    </div>
                                </div>
                            </div>
                            <div class="scrap-configuration-field">
                                <div class="field-title">
                                    <label class="scrap-configuration-field-label">امکان به روز رسانی قیمت در آینده</label>
                                    <a class="tipitip-trigger dgscraper-help" data-position="north-west" data-class="dgs-tooltip"
                                       data-content="با فعالسازی این گزینه امکان به روز رسانی قیمت در آینده برای محصول فراهم خواهد شد.">
                                        ?
                                    </a>
                                </div>
                                <div class="field-value">
                                    <div class="form-check form-check-inline dgs-checkbox">
                                        <input class="form-check-input" type="checkbox" name="possibility-update-future-price" id="possibility-update-future-price" value="<?php echo  $scraping_config_options['future_price_update']; ?>" <?php checked( $scraping_config_options['future_price_update'], 1 ); ?> >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="scraping_config" class="overlay_scraping_links">
                        <div class="spinner_dgs">
                            <div class="spinner-container container1">
                                <div class="circle1"></div>
                                <div class="circle2"></div>
                                <div class="circle3"></div>
                                <div class="circle4"></div>
                            </div>
                            <div class="spinner-container container2">
                                <div class="circle1"></div>
                                <div class="circle2"></div>
                                <div class="circle3"></div>
                                <div class="circle4"></div>
                            </div>

                        </div>
                    </div>
                    <?php wp_nonce_field( 'scraping_conf' ,'scraping_conf_nonce' ); ?>

                    <input type="button" name="previous" class="previous action-button" value="لینک محصولات" />
                    <input type="button" name="next" class="next action-button" value="درون ریزی" />
                </fieldset>
                <fieldset data-section="scraping_import" class="scraping_import">
                    <div id="dgs-advance">پیشرفته</div>

                    <?php

                    if(get_option('digi_import_options'))
                    {
                        $digi_import_options = get_option('digi_import_options', true);
                    }
                    ?>
                    <div class="container_advance_tab" ng-app="dgsApp" ng-controller="dgsCtrl">
                        <div class="wrap">
                            <section class="question-section">
                                <h1>تغییرات محتوایی محصولات</h1>
                                <div class="cat-1">
                                    <input id="tab-1" type="radio" name="tabs" tabindex="1" checked="checked">
                                    <label for="tab-1" >ویرایش عنوان محصول</label><!-- end of tab label -->
                                    <div class="question-wrap">
                                        <div class="question">
                                            <div class="input scrap-configuration-fields">
                                                <div class="first-coulmn product-title-before-after">
                                                    <label for="question-1" >عنوان محصول در سایت شما</label>
                                                    <a class="tipitip-trigger dgscraper-help" data-position="north-west" data-class="dgs-tooltip"
                                                       data-content="کلماتی رو به قبل یا بعد از عنوان محصول اضافه کنید.">
                                                        ?
                                                    </a>
                                                    <?php
                                                    $before_title = $digi_import_options['before_title'];
                                                    $after_title = $digi_import_options['after_title'];
                                                    ?>
                                                    <p>
                                                        <input class="before_title" type="text" name="before_title" id="before_title" value="<?php echo $before_title ; ?>" placeholder="عبارت قبل از عنوان" >
                                                        <span class="orginal-title">[original_title]</span>
                                                        <input class="after_title" type="text" name="after_title" id="after_title" value="<?php echo $after_title ; ?>" placeholder="عبارت بعد از عنوان" >
                                                    </p>
                                                </div>
                                                <div class="second-coulmn">
                                                    <div class="col-sm-12">
                                                        <label for="question-1" > قواعد یافتن و جایگزینی</label>
                                                        <a class="tipitip-trigger dgscraper-help" data-position="north-west" data-class="dgs-tooltip"
                                                           data-content="کلماتی رو از عنوان حذف یا با کلمات دلخواه دیگه ای جایگزین کنید. برای حذف کلمه مورد نظر کافی است فیلد جایگزین کردن رو خالی بگذارید.">
                                                            ?
                                                        </a>
                                                    </div>
                                                    <?php
                                                    $dgs_scrape_title_regex_finds = $digi_import_options['dgs_scrape_title_regex_finds'];
                                                    $dgs_scrape_title_regex_replaces = $digi_import_options['dgs_scrape_title_regex_replaces'];
                                                    $dgs_combined_regex = array();
                                                    if(!empty($dgs_scrape_title_regex_finds))
                                                        $dgs_combined_regex = array_combine($dgs_scrape_title_regex_finds, $dgs_scrape_title_regex_replaces);
                                                    if(!empty($dgs_scrape_title_regex_finds)) : foreach($dgs_combined_regex as $regex => $replace): if(!empty($regex)): ?>
                                                        <div class="form-group">
                                                            <div class="col-sm-12"><div class="input-group">
                                                                    <div class="input-group-addon">پیدا کن</div>
                                                                    <input type="text" name="dgs_scrape_title_regex_finds[]" placeholder="مثال یافتن" class="form-control" value="<?php echo esc_html($regex); ?>">
                                                                    <span class="input-group-btn">
                                                                    <button type="button" class="btn btn-primary btn-block" ng-click="remove_field($event)">
                                                                        <i class="fa fa-trash"></i>
                                                                    </button>
                                                                </span>
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-12">
                                                                <div class="input-group">
                                                                    <div class="input-group-addon">جایگزین کردن</div>
                                                                    <input type="text" name="dgs_scrape_title_regex_replaces[]" placeholder="مثال جایگزین کردن" class="form-control" value="<?php echo esc_html($replace); ?>">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endif; endforeach; endif; ?>

                                                    <div class="form-group">
                                                        <div class="col-sm-12">
                                                            <button type="button" class="btn btn-link add_title_replace" ng-click="add_field($event, 'title_regex')"><i class="fa fa-plus-circle"></i>افزودن قاعده جایگزینی جدید</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div><!-- end of Catagory -->

                                <div class="cat-2">
                                    <input id="tab-2" type="radio" name="tabs" tabindex="7">
                                    <label for="tab-2" >ویرایش توضیحات محصول</label>
                                    <div class="question-wrap">
                                        <div class="question">
                                            <div class="input scrap-configuration-fields">
                                                <div class="first-coulmn product-title-before-after">
                                                    <label for="question-1" >افزودن برچسپ دلخواه</label>
                                                    <a class="tipitip-trigger dgscraper-help" data-position="north-west" data-class="dgs-tooltip"
                                                       data-content="کلمات دلخواه رو برای ایجاد برچسپ های محصول با کاما از هم تفکیک کنید.">
                                                        ?
                                                    </a>
                                                    <p>
                                                      <textarea rows="3" cols="80" name="dgs-product-tags" id="dgs-product-tags" placeholder="برچسپ 1،برچسپ 2 ، برچسپ 3 ، ..." style="width: 100%; margin-bottom: 15px;"><?php
                                                          $dgs_product_tags = $digi_import_options['dgs_product_tags'];
                                                          if(!empty($dgs_product_tags))
                                                          {
                                                              echo $dgs_product_tags;
                                                          }
                                                          ?></textarea>
                                                    </p>
                                                </div>
                                                <div class="second-coulmn">
                                                    <div class="col-sm-12">
                                                        <label for="question-1" > قواعد یافتن و جایگزینی</label>
                                                        <a class="tipitip-trigger dgscraper-help" data-position="north-west" data-class="dgs-tooltip"
                                                           data-content="کلماتی رو از توضیحات محصول حذف یا با کلمات دلخواه دیگه ای جایگزین کنید. برای حذف کلمه مورد نظر کافی است فیلد جایگزین کردن رو خالی بگذارید.">
                                                            ?
                                                        </a>
                                                    </div>
                                                    <?php
                                                    $dgs_scrape_content_regex_finds = $digi_import_options['dgs_scrape_content_regex_finds'];
                                                    $dgs_scrape_content_regex_replaces = $digi_import_options['dgs_scrape_content_regex_replaces'];
                                                    $dgs_combined_regex = array();
                                                    if(!empty($dgs_scrape_content_regex_finds))
                                                        $dgs_combined_regex = array_combine($dgs_scrape_content_regex_finds, $dgs_scrape_content_regex_replaces);
                                                    if(!empty($dgs_scrape_content_regex_finds)) : foreach($dgs_combined_regex as $regex => $replace): if(!empty($regex)): ?>
                                                        <div class="form-group">
                                                            <div class="col-sm-12"><div class="input-group">
                                                                    <div class="input-group-addon">پیدا کن</div>
                                                                    <input type="text" name="dgs_scrape_content_regex_finds[]" placeholder="مثال یافتن" class="form-control" value="<?php echo esc_html($regex); ?>">
                                                                    <span class="input-group-btn">
                                                                    <button type="button" class="btn btn-primary btn-block" ng-click="remove_field($event)">
                                                                        <i class="fa fa-trash"></i>
                                                                    </button>
                                                                </span>
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-12">
                                                                <div class="input-group">
                                                                    <div class="input-group-addon">جایگزین کردن</div>
                                                                    <input type="text" name="dgs_scrape_content_regex_replaces[]" placeholder="مثال جایگزین کردن" class="form-control" value="<?php echo esc_html($replace); ?>">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endif; endforeach; endif; ?>

                                                    <div class="form-group">
                                                        <div class="col-sm-12">
                                                            <button type="button" class="btn btn-link add_title_replace" ng-click="add_field($event, 'content_regex')"><i class="fa fa-plus-circle"></i>افزودن قاعده جایگزینی جدید</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </section><!-- End of Questions -->
                        </div>
                    </div>

                    <h2 class="fs-title">گزینه های زیر را برحسب نیاز مشخص کنید.</h2>
                    <!--<h3 class="fs-subtitle">We will never sell it</h3>-->
                    <div class="input scrap-configuration-fields scrap-importing-fields generate-link-market">
                        <div class="first-coulmn">
                            <div class="scrap-configuration-field">
                                <div class="field-title">
                                    <label class="scrap-configuration-field-label">ایجاد لینک بازار یابی</label>
                                    <a class="tipitip-trigger dgscraper-help" data-position="north-west" data-class="dgs-tooltip"
                                       data-content="برای ایجاد لینک بازار یابی این گزینه را فعال کنید و قسمت ثابت لینک بازاریابی و شناسه بازاریابی را در فیلد روبه رو وارد کنید.">
                                        ?
                                    </a>
                                </div>
                                <div class="field-value">
                                    <div class="form-check form-check-inline dgs-checkbox">
                                        <input class="form-check-input" type="checkbox" name="scrap-marketing-enable" id="scrap-marketing-enable" value="<?php echo $digi_import_options['create_marketing_link']; ?>" <?php checked( $digi_import_options['create_marketing_link'], 1 ); ?>  >
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="second-coulmn">
                            <div class="scrap-configuration-field marketing_link_value">
                                <div class="field-value">
                                    <input class="marketing_link_input" type="text" name="marketing_link_value" id="marketing_link_value" value="<?php echo $digi_import_options['marketing_link_const']; ?>" placeholder="sample : https://affstat.adro.co/click/76f13e8c-7d5a-48ac-8352-c08ebbc5fd84/" >
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="input scrap-configuration-fields scrap-importing-fields">
                        <div class="first-coulmn">
                            <div class="scrap-configuration-field">
                                <div class="field-title">
                                    <label class="scrap-configuration-field-label">نمایش تصویر شاخص به صورت مستقیم</label>
                                    <a class="tipitip-trigger dgscraper-help" data-position="north-west" data-class="dgs-tooltip"
                                       data-content="با فعالسازی این گزینه تصویر شاخص بر روی هاست شما آپلود نمی شود و با آدرس مستقیم از سایت مبدا بر روی سایت شما نمایش داده می شود.">
                                        ?
                                    </a>
                                </div>
                                <div class="field-value">
                                    <div class="form-check form-check-inline dgs-checkbox">
                                        <input class="form-check-input" type="checkbox" name="show-featured-image-directly" id="show-featured-image-directly" value="<?php echo $digi_import_options['show_featured_image_directly']; ?>" <?php checked( $digi_import_options['show_featured_image_directly'], 1 ); ?>  >
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="second-coulmn">
                            <div class="scrap-configuration-field">
                                <div class="field-title">
                                    <label class="scrap-configuration-field-label">نمایش تصاویر گالری به صورت مستقیم</label>
                                    <a class="tipitip-trigger dgscraper-help" data-position="north-west" data-class="dgs-tooltip"
                                       data-content="با فعالسازی این گزینه تصاویر گالری بر روی هاست شما آپلود نمی شود و با آدرس مستقیم از سایت مبدا بر روی سایت شما نمایش داده می شوند.">
                                        ?
                                    </a>
                                </div>
                                <div class="field-value">
                                    <div class="form-check form-check-inline dgs-checkbox">
                                        <input class="form-check-input" type="checkbox" name="show-gallery-image-directly" id="show-gallery-image-directly" value="<?php echo $digi_import_options['show_gallery_image_directly']; ?>" <?php checked( $digi_import_options['show_gallery_image_directly'], 1 ); ?>  >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="input scrap-configuration-fields scrap-importing-fields generate-link-market">
                        <div class="first-coulmn">
                            <div class="scrap-configuration-field">
                                <div class="field-title">
                                    <label class="scrap-configuration-field-label">دسته های محصول را انتخاب کنید</label>
                                    <a class="tipitip-trigger dgscraper-help" data-position="north-west" data-class="dgs-tooltip"
                                       data-content="دسته محصولاتی را که ، می خواهید این محصولات در این دسته ها درون ریزی شوند را انتخاب کنید. اگر این فیلد رو خالی بگذارید ، دسته بندی محصول به صورت خودکار بر اساس سایت مبدا ایجاد خواهد شد.">
                                        ?
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="second-coulmn">
                            <div class="scrap-configuration-field marketing_link_value selectd_categories">
                                <div class="field-value">
                                    <?php
									$selected = '';
                                     $selected_categories = $digi_import_options['import_product_to_categories'];
                                    $terms = get_terms('product_cat', array(
                                        'hide_empty' => false,
                                        'orderby'  => 'id'
                                        //'childless'  => true
                                    ));
                                    ?>
                                    <select class="chzn-select" multiple name="product_insert_to_categories" id="product_insert_to_categories"  data-placeholder="دسته محصولات مورد نظر را انتخاب کنید ...">
                                        <option value=""></option>
                                        <?php
                                        $html = '';

                                            foreach ($terms as $term) {
                                                if(is_array($selected_categories))
												{
										         $selected = in_array($term->term_id, $selected_categories) ? ' selected="selected" ' : '';
												}
                                                $html .= '<option value="'.$term->term_id.'"' .$selected. '>'.$term->name.'</option>';

                                            }
                                            echo $html;

                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="input scrap-configuration-fields scrap-importing-fields">
                        <div class="first-coulmn">
                            <div class="scrap-configuration-field">
                                <div class="field-title">
                                    <label class="scrap-configuration-field-label">انتخاب وضعیت محصولات</label>
                                    <a class="tipitip-trigger dgscraper-help" data-position="north-west" data-class="dgs-tooltip"
                                       data-content="یکی از حالت های ، در انتظار بازبینی ، پیشنویس و یا انتشار را برای محصولاتی که می خواهید درون ریزی کنید را انتخاب کنید.">
                                        ?
                                    </a>
                                </div>
                                <div class="field-value">
                                    <div class="form-check form-check-inline dgs-checkbox">
                                        <select name="products-status" id="products-status" >
                                            <option value="pending" <?php echo selected( $digi_import_options['product_imported_status'], 'pending', false) ?> >در انتظار بازبینی</option>
                                            <option value="draft" <?php echo selected( $digi_import_options['product_imported_status'], 'draft', false) ?> >پیشنویس</option>
                                            <option value="publish" <?php echo selected( $digi_import_options['product_imported_status'], 'publish', false) ?> >انتشار</option>
                                        </select>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="second-coulmn">
                            <div class="scrap-configuration-field">
                                <div class="field-title">
                                    <label class="scrap-configuration-field-label">تنظیم سرعت اسکراپ</label>
                                    <a class="tipitip-trigger dgscraper-help" data-position="north-west" data-class="dgs-tooltip"
                                       data-content="با این گزینه شما می توانید میزان سرعت اسکراپ و درون ریزی را تنظیم کنید ، شما با این گزینه در واقع میزان مصرف منابع سیستمی سرور خود را کنترل می کنید، به عنوان مثال اگر تعداد محصولاتی که می خواهید اسکراپ کنید زیاد است بهتر است گزینه آهسته را انتخاب کنید ، تا سرور شما از لحاظ پاسخگویی و عملکرد در حالت بهینه باشد.">
                                        ?
                                    </a>
                                </div>
                                <div class="field-value">
                                    <div class="form-check form-check-inline dgs-checkbox">
                                        <select name="import-speed" id="import-speed" >
                                            <option value="normal" <?php echo selected( $digi_import_options['digi_scrap_speed'], 'normal', false) ?> >معمولی</option>
                                            <option value="slow" <?php echo selected( $digi_import_options['digi_scrap_speed'], 'slow', false) ?> >آهسته</option>
                                            <option value="fast" <?php echo selected( $digi_import_options['digi_scrap_speed'], 'fast', false) ?> >سریع</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="overlay_scraping_links">
                        <div class="verfi-excute">
                            <input type="button" name="cancell" class="execute-cancel action-button" value="انصراف" />
                            <input type="button" name="verfi" class="execute-script action-button" value="شروع درون ریزی" />
                        </div>
                        <div class="spinner_dgs">
                            <div class="spinner-container container1">
                                <div class="circle1"></div>
                                <div class="circle2"></div>
                                <div class="circle3"></div>
                                <div class="circle4"></div>
                            </div>
                            <div class="spinner-container container2">
                                <div class="circle1"></div>
                                <div class="circle2"></div>
                                <div class="circle3"></div>
                                <div class="circle4"></div>
                            </div>

                        </div>
                    </div>
                    <?php wp_nonce_field( 'scraping_imp','scraping_imp_nonce' ); ?>
                    <input type="button" name="previous" class="previous action-button" value="تنظیمات اسکراپ" />
                    <input type="button" name="submit" class="submit action-button" value="تایید و اجرای اسکراپ" />
                </fieldset>
                <input type="hidden" name="progress_state" id="progress_state" value="<?php echo absint($progress_runinng); ?>">
            </form>

        </div>
        <div class="dgscraper-footer" >

        </div>
    </div>
    </div>

    <div class="row pop-up <?php if($progress_runinng == 1){echo 'visable';}  ?> " >
        <div class="box large-centered horizontal rounded">
            <div class="info-msg" id="info-msg">
                <i class="fa fa-info-circle"></i>
                عمل درون ریزی در پس زمینه در حال انجام است ، با آسودگی خاطر می توانید از این صفحه خارج شوید!
            </div>
            <div class="progress-container">
                <div class="progress-bar horizontal">
                    <div class="progress-track">
                        <div class="progress-fill" style="width: <?php if($progress_runinng == 1){echo $rate_of_progress;}  ?>;">
                            <span><?php if($progress_runinng == 1){echo $rate_of_progress;}  ?></span>
                        </div>
                    </div>
                </div>
                <div class="progress-text"><span>محصول</span> <span class="progress_product_name"><?php if($progress_runinng == 1){echo $importing_product_name;}  ?></span> <span class="three-point">...</span> <span><?php echo $progress_text; ?></span><span class="loading"><img src="<?php echo DGSCRAPER_PLUGIN_URL.'/admin/images/fancybox_loading.gif'; ?>" width="30" height="30"/></span>
                </div>
                <div class="success-msg" id="success-msg">
                    <i class="fa fa-check">
                    </i>
                    اسکراپ و درون ریزی محصولات با موفقیت انجام شد!
                </div>
            </div>
            <div class="action-btn">
                <button type="button" class="action-button" id="stop-import">انصراف</button>
            </div>
        </div>
    </div>



  <?php
}


/**
 * Get memory limit
 *
 * @return int
 */
function digi_get_memory_limit() {
    if ( function_exists( 'ini_get' ) ) {
        $memory_limit = ini_get( 'memory_limit' );
    } else {
        // Sensible default.
        $memory_limit = '128M';
    }

    if ( ! $memory_limit || -1 === $memory_limit ) {
        // Unlimited, set to 32GB.
        $memory_limit = 'نامحدود';
    }

    return $memory_limit;
}

/**
 * Get max execution time
 *
 * @return int
 */
function digi_get_max_execution_time() {
    if ( function_exists( 'ini_get' ) ) {
        $max_execution_time = ini_get( 'max_execution_time' );
    } else {
        // Sensible default.
        $max_execution_time = '30S';
    }

    if ( ! $max_execution_time || -1 === $max_execution_time ) {
        // Unlimited, set to 32GB.
        $max_execution_time = 'نامحدود';
    }

    return $max_execution_time;
}

function woo_digi_scraps_managment()
{
    ?>
    <div class="dgscraper-main-container scraps-managment-container">
        <h2 class="dgscraper-wp-notices" style="margin-top: 0px;"></h2>
        <div class="dgscraper-header">
            <div class="dgscraper-logo"></div>
            <div class="dgscraper-title">
                <p>دیجی اسکراپر</p>
                <h4>مدیریت اسکراپ های انجام شده</h4>
            </div>
            <div class="dgscraper-links">
                <a href="https://www.zhaket.com/store/web/httpmarketwpirstore" target="_blank">پشتیبانی</a> | <a href="https://www.zhaket.com/store/web/httpmarketwpirstore" target="_blank">مستندات</a>
            </div>
        </div>
        <div class="dgscraper-body" >
         <div class="scrap-managment-list">
             <div class="scrap-managment-page-heading">
                 <h2>اسکراپ ها</h2>

                 <?php
                 $url = admin_url('admin.php?page=woo-digi-scraper&tab=dgscraper_new');
                 ?>

                 <a class="create-new-scrap-button" href="<?php echo $url; ?>">اسکراپ جدید</a>

                 <div class="scrap-managment-page-heading-subheading"> حذف یا از سرگیری اسکراپ ها ی متوقف شده و مشاهده تعداد محصولات درون ریز شده در هر اسکراپ</div>
                 <div class="current-system-status">

                     <?php
                       $time_ex_limit_for_remove_mark = 300;
                       $current_time_can_remove_mark = false;
                       $time_ex_limit_for_regular = 120;
                       $current_time_for_regular = false;
                       $memory_limit_for_remove_mark = 1024;
                       $current_memory_can_remove_mark = false;
                       $memory_limit_for_regular = 512;
                       $current_memory_for_regular = false;
                       $current_memory = intval(digi_get_memory_limit());
                       $current_max_execution_time = intval(digi_get_max_execution_time());
                       if($current_memory == 0 || $current_memory >= $memory_limit_for_remove_mark)
                       {
                           $current_memory_can_remove_mark = true;
                       }
                       if($current_memory == 0 || $current_memory >= $memory_limit_for_regular)
                      {
                         $current_memory_for_regular = true;
                      }
                      if($current_max_execution_time == 0 || $current_max_execution_time >= $time_ex_limit_for_remove_mark)
                      {
                         $current_time_can_remove_mark = true;
                      }
                      if($current_max_execution_time == 0 || $current_max_execution_time >= $time_ex_limit_for_regular)
                      {
                         $current_time_for_regular = true;
                      }

                     ?>


                     <table>
                         <!--<caption></caption>-->
                         <thead>
                         <tr>
                             <th scope="col">قابلیت</th>
                             <th scope="col">حداقل مورد نیاز</th>
                             <th scope="col">سیستم شما</th>
                         </tr>
                         </thead>
                         <!--<tfoot>
                         <tr>
                             <th scope="row">tfoot th</th>
                             <td colspan="2">tfoot td colspan=2</td>
                             <td>tfoot td</td>
                         </tr>
                         </tfoot>-->
                         <tbody>
                         <tr>
                             <th scope="row">محدودیت زمانی PHP برای حذف واترمارک</th>
                             <td><code>max_execution_time=300</code></td>
                             <td><?php echo $current_max_execution_time.'Sُُ'; ?><span class="state-parameter fa <?php if($current_time_can_remove_mark){echo 'fa-check';} else{echo 'fa-times';} ?>"></span></td>
                         </tr>
                         <tr>
                             <th scope="row"> محدودیت زمانی PHP بدون حذف واترمارک</th>
                             <td><code>max_execution_time=120</code></td>
                             <td><?php echo $current_max_execution_time.'Sُُ'; ?><span class="state-parameter fa <?php if($current_time_for_regular){echo 'fa-check';} else{echo 'fa-times';} ?>"></span></td>
                         </tr>
                         <tr>
                             <th scope="row">محدودیت حافظه برای حذف واترمارک</th>
                             <td><code>memory_limit=1024</code></td>
                             <td><?php echo digi_get_memory_limit(); ?><span class="state-parameter fa <?php if($current_memory_can_remove_mark){echo 'fa-check';} else{echo 'fa-times';} ?>"></span></td>
                         </tr>
                         <tr>
                             <th scope="row"> محدودیت حافظه بدون حذف واترمارک</th>
                             <td><code>memory_limit=512</code></td>
                             <td><?php echo digi_get_memory_limit(); ?><span class="state-parameter fa <?php if($current_memory_for_regular){echo 'fa-check';} else{echo 'fa-times';} ?>"></span></td>
                         </tr>
                         </tbody>
                     </table>
                 </div>
             </div>
             <table class="scraps-list">
                 <thead>
                     <tr>
                         <th class=""><span>نام</span></th>
                         <th class=""><span>خلاصه</span></th>
                         <th><span>اکشن ها</span></th>
                         <th><span>وضعیت اسکراپ</span></th>
                     </tr>
                 </thead>
                 <tbody>
                 <?php

                if(get_option('registered_scrap_task'))
                {

                    $option_names = get_option('registered_scrap_task', true);
                    foreach( $option_names as $option_name ){
                        if(get_option($option_name)) {
                            $scrap_task = get_option($option_name , true);
                            $scrap_name = $scrap_task['scrap-name'];
                            $running = $scrap_task['running'];
                            $num_of_imported = $scrap_task['number-of-product-imported'];
                            $completed = $scrap_task['scrap-completed'];
                            $run_date = $scrap_task['last-execute-time'];
                            ?>
                            <tr class="scraps-list-item" id="<?php echo $scrap_name; ?>">
                                <td class="scraps-list-item-name">
                                    <a href="#/edit-widget/1/"><?php echo $scrap_name; ?></a>
                                </td>
                                <td class="scraps-list-item-date">
                                    <div class="date-time"><span>آخرین اجرا :</span><span><?php echo $run_date; ?></span></div>
                                    <div class="number-of-imported"><span><?php echo $num_of_imported; ?></span><span>محصول درون ریزی شد.</span></div>
                                </td>
                                <td class="scraps-list-item-actions">
                                    <!--<a href="#/edit-widget/1/" class="scraps-list-item-actions-edit"><i
                                                class="fa fa-edit"></i>ویرایش</a>-->
                            <?php if($running == 0) { ?>
                                    <a href="#" class="scraps-list-item-actions-remove scrap-remove" title="حذف اسکراپ" data-scrap="<?php echo $scrap_name; ?>">حذف<i
                                                class="fa fa-remove"></i></a>
                            <?php } ?>
                            <?php if($completed == 0 && $running == 0) { ?>
                                    <a href="#" class="scraps-list-item-actions-resume scrap-resume" data-resume="<?php echo $scrap_name; ?>" title="ادامه اسکراپ از نقطه ی توقف..."><i
                                                class="fa fa-repeat"></i>ادامه اسکراپ</a>
                            <?php } ?>
                                </td>
                                <td class="scrap_staus">
                                    <?php if($running == 1) { ?>
                                        <span class="badg scrap-running">در حال انجام</span>
                                    <?php } ?>
                                    <?php if($completed == 0 && $running == 0) { ?>
                                        <span class="badg scrap-stop">متوقف شده</span>
                                    <?php } ?>
                                    <?php if($completed == 1) { ?>
                                        <span class="badg scrap-completed">انجام شده</span>
                                    <?php } ?>
                                </td>
                            </tr>

                            <?php
                        }
                    }

                }
                 ?>
                 </tbody>
             </table>
             <div class="ajax-load">
                 <div class="spinner_dgs">
                     <div class="spinner-container container1">
                         <div class="circle1"></div>
                         <div class="circle2"></div>
                         <div class="circle3"></div>
                         <div class="circle4"></div>
                     </div>
                     <div class="spinner-container container2">
                         <div class="circle1"></div>
                         <div class="circle2"></div>
                         <div class="circle3"></div>
                         <div class="circle4"></div>
                     </div>

                 </div>
             </div>
         </div>
        </div>
        <div class="dgscraper-footer" >

        </div>
    </div>
    <?php
}
