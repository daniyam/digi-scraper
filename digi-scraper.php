<?php
/**
 * The plugin digi-scraper file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://www.beyranplugins.ir/
 * @since             1.0.0
 * @package           digi-scraper
 *
 * @wordpress-plugin
 * Plugin Name:       دیجی اسکراپر
 * Plugin URI:        http://www.beyranplugins.ir
 * Description:       گرفتن اطلاعات محصول از سایت های بزرگی مثل دیجی کالا و تبدیل و ایجاد فوری محصول در فروشگاه ووکامرسی.
 * Version:           4.11
 * Author:            رحیم بیرانوند
 * Author URI:        http://www.beyranplugins.ir/
 * Text Domain:       woo-digi-scraper
 * Domain Path:       /languages
 */
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
//define( 'DGSCRAPER_PLUGIN_VERSION', '1.0.0' );

//Plugin URL
if ( ! defined( 'DGSCRAPER_PLUGIN_URL' ) ) {
    define( 'DGSCRAPER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
//Plugin Path
if ( ! defined( 'DGSCRAPER_PLUGIN_PATH' ) ) {
    define( 'DGSCRAPER_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}

//Plugin Name
if ( ! defined( 'DGSCRAPER_PLUGIN_NAME' ) ) {
    define('DGSCRAPER_PLUGIN_NAME','DiGi Scraper');
}

//Api path
if ( ! defined( 'DGSCRAPER_API_URL' ) ) {
    define('DGSCRAPER_API_URL','https://api.digikala.com/v2/product/');
}

//Category Api path
if ( ! defined( 'DGSCRAPER_CAT_API_URL' ) ) {
    define('DGSCRAPER_CAT_API_URL','https://api.digikala.com/v1/categories/');
}

//Brand Api path
if ( ! defined( 'DGSCRAPER_BRAND_API_URL' ) ) {
    define('DGSCRAPER_BRAND_API_URL','https://api.digikala.com/v1/brands/');
}

//Seller Api path
if ( ! defined( 'DGSCRAPER_SELLER_API_URL' ) ) {
    define('DGSCRAPER_SELLER_API_URL','https://api.digikala.com/v1/sellers/');
}

//IP Api path
if ( ! defined( 'DGSCRAPER_IP' ) ) {
    define('DGSCRAPER_IP','');
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-digi_scraper-activator.php
 */
function activate_digi_scraper() {
    require_once DGSCRAPER_PLUGIN_PATH . 'includes/class-digi_scraper-activator.php';
    if ( extension_loaded('ionCube Loader')) {
        Digi_Scraper_Activator::activate();
    }
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-digi_scraper-deactivator.php
 */
function deactivate_digi_scraper() {

    require_once DGSCRAPER_PLUGIN_PATH . 'includes/class-digi_scraper-deactivator.php';
    Digi_Scraper_Deactivator::deactivate();

}

register_activation_hook(__FILE__, 'activate_digi_scraper');
register_deactivation_hook(__FILE__, 'deactivate_digi_scraper');


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_digi_scraper_extended() {

    /**
     * The core plugin class that is used to define internationalization,
     * admin-specific hooks, and public-facing site hooks.
     */
    require DGSCRAPER_PLUGIN_PATH . 'includes/class-digi_scraper_extended.php';
    $plugin = new Digi_Scraper_extended();
    $plugin->run();

}


/**
 * Check plugin requirement on plugins loaded, this plugin requires woocommerce to be installed and active.
 *
 * @since    1.0.0
 */
add_action( 'plugins_loaded', 'dgscraper_initializ_plugin' );
function dgscraper_initializ_plugin() {

    $wc_active = in_array( 'woocommerce/woocommerce.php', get_option( 'active_plugins' ) );
    if ( current_user_can('activate_plugins') && $wc_active !== true ) {
        add_action('admin_notices', 'dgscraper_plugin_admin_notice');
    }
    if(!extension_loaded('ionCube Loader'))
    {
        add_action('admin_notices', 'dgscraper_plugin_admin_notice_ioncube');
    }
    else {

        if(ini_get("allow_url_fopen") !=1)
        {
            add_action('admin_notices', 'dgscraper_plugin_admin_notice_alulfopen');
        }
        else
        {
            run_digi_scraper_extended();
            add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'dgscraper_plugin_links' );
        }
    }

}


/**
 * Show admin notice in case of woocommerce plugin is missing.
 *
 * @since    1.0.0
 */
function dgscraper_plugin_admin_notice() {

    $dgscraper_plugin = esc_html__('دیجی اسکراپر','woo-digi-scraper');
    $wc_plugin = esc_html__('ووکارمرس','woo-digi-scraper');
    ?>
    <div class="error">
        <p>
            <?php echo sprintf( __( 'برای استفاده از افزونه %1$s لازم است افزونه %2$s نصب و فعالسازی شود.', 'woo-digi-scraper' ), '<strong>' . esc_html( $dgscraper_plugin ) . '</strong>', '<strong>' . esc_html( $wc_plugin ) . '</strong>' );?>
        </p>
    </div>
    <?php

}

function dgscraper_plugin_admin_notice_alulfopen()
{
    $dgscraper_plugin = esc_html__('دیجی اسکراپر','woo-digi-scraper');
    $dgscraper_aurlfopen = esc_html__('allow_url_fopen','woo-digi-scraper');
    ?>
    <div class="error">
        <p>
            <?php echo sprintf( __( 'برای استفاده از افزونه %1$s لازم است قابلیت %2$s بر روی سرور شما فعال شود.', 'woo-digi-scraper' ), '<strong>' . esc_html( $dgscraper_plugin ) . '</strong>', '<strong>' . esc_html( $dgscraper_aurlfopen ) . '</strong>' );?>
        </p>
    </div>
    <?php
}

function dgscraper_plugin_admin_notice_ioncube()
{
    $dgscraper_plugin = esc_html__('دیجی اسکراپر','woo-digi-scraper');
    $dgscraper_ioncube = esc_html__('ionCube Loader','woo-digi-scraper');
    ?>
    <div class="error">
        <p>
            <?php echo sprintf( __( 'برای استفاده از افزونه %1$s لازم است %2$s بر روی سرور شما فعال شود.', 'woo-digi-scraper' ), '<strong>' . esc_html( $dgscraper_plugin ) . '</strong>', '<strong>' . esc_html( $dgscraper_ioncube ) . '</strong>' );?>
        </p>
    </div>
    <?php
}


/**
 * Settings link on plugin listing page
 */
function dgscraper_plugin_links( $links ) {

    $vpe_links = array(
        '<a href="'.admin_url('admin.php?page=woo-digi-scraper&tab=dgscraper_new').'">'.esc_html__( 'تنظیمات', 'woo-digi-scraper' ).'</a>'
    );
    return array_merge( $links, $vpe_links );

}