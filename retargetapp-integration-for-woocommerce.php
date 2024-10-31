<?php
/**
 * Plugin Name: Adwisely Integration for WooCommerce
 * Description: Adwisely (ex-RetargetApp) combines expertise in advertising with advanced technologies to make advertising easy and efficient. With Adwisely, you get high ROAS and more sales effortlessly
 * Version:     1.0.6
 * Author URI:  https://adwisely.com
 * WC requires at least: 2.3
 * WC tested up to: 6.6.1
 *
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 */
defined('ABSPATH') || exit;

include_once(dirname(__FILE__) . '/config/config.php');
include_once(dirname(__FILE__) . '/includes/api-client.php');
include_once(dirname(__FILE__) . '/includes/api.php');
include_once(dirname(__FILE__) . '/includes/pixel.php');
include_once(dirname(__FILE__) . '/includes/account.php');
include_once(dirname(__FILE__) . '/utils.php');

if (!class_exists('RetargetApp_WooCommerce_Integration')) :

    class RetargetApp_WooCommerce_Integration
    {
        private static $instance = null;

        private $pixel_integration = null;
        private $account = null;

        /**
         * @return RetargetApp_WooCommerce_Integration
         */
        public static function get_instance()
        {
            if (!self::$instance) {
                $pixel_integration = RA_WC_PixelIntegration::get_instance();
                $account = RA_WC_Account::get_instance();

                $class = __CLASS__;
                error_log("Initialize " . __CLASS__);
                self::$instance = new $class($account, $pixel_integration);
            }
            return self::$instance;
        }

        /**
         * Construct the plugin.
         * @param $account RA_WC_Account
         * @param $pixel_integration RA_WC_PixelIntegration
         */
        private function __construct($account, $pixel_integration)
        {
            $this->account = $account;
            $this->pixel_integration = $pixel_integration;

            if (is_admin()) {
                if ( ! class_exists( 'WooCommerce' ) ) {
                    add_action( 'admin_notices', function() {
                        echo '<div class="error"><p><strong>' . sprintf( esc_html__( 'Adwisely Integration for WooCommerce requires the WooCommerce plugin to be installed and active. You can download %s here.', 'woocommerce' ), '<a href="https://wordpress.org/plugins/woocommerce/" target="_blank">WooCommerce</a>' ) . '</strong></p></div>';
                    } );
				    return;
			    }
                add_action('admin_menu', array($this, 'add_admin_menu'));
                add_action('admin_notices', array($this, 'add_admin_notice'));
                add_action('in_admin_header', array($this, 'remove_admin_notices'), 1);
                add_action('wp_ajax_ra_connect', array($this, 'handle_wp_ajax_ra_connect'));
            } else {
                $this->pixel_integration->inject_pixel();
            }

            add_action('rest_api_init', array($this, 'register_ra_api_controller'));
        }

        /**
         * Remove all admin notices if current page is plugin's page
         */
        public function remove_admin_notices()
        {
            if (self::is_ra_wc_plugin_page()) {
                remove_all_actions('admin_notices');
            }
        }

        /**
         * Add RetargetApp Integration item into main admin menu
         */
        public function add_admin_menu()
        {
            $icon = "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iNDBweCIgaGVpZ2h0PSIzN3B4IiB2aWV3Qm94PSIwIDAgNDAgMzciIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+CiAgICA8ZGVmcz4KICAgICAgICA8bGluZWFyR3JhZGllbnQgeDE9IjM4LjQ2MzUxMiUiIHkxPSI1MCUiIHgyPSIxMDIuNjU4MzM1JSIgeTI9IjgxLjgwNzIyODclIiBpZD0ibGluZWFyR3JhZGllbnQtMSI+CiAgICAgICAgICAgIDxzdG9wIHN0b3AtY29sb3I9IiMwN0FFM0EiIG9mZnNldD0iMCUiPjwvc3RvcD4KICAgICAgICAgICAgPHN0b3Agc3RvcC1jb2xvcj0iIzA4N0MzNyIgb2Zmc2V0PSIxMDAlIj48L3N0b3A+CiAgICAgICAgPC9saW5lYXJHcmFkaWVudD4KICAgICAgICA8cGF0aCBkPSJNMjcuOTMyMTA0NSwyMi4xNDI3NTUxIEMyNy45MjczODE1LDI2LjU0NTQ4NjQgMjQuMzgzMTQ0MywzMC4xMTM0MTkzIDIwLjAwOTY1MywzMC4xMTgxNzM4IEwzMS42MjEyNTA0LDM4LjUyNDkyOTQgQzM5LjMyNDQ1ODMsMzIuOTg5NjExIDQyLjExOTI0MDEsMjIuNzc2Njg1OSAzOC4zMTg1NTYyLDE0LjA1MTA3NTkgQzM0LjUxNzg3MjIsNS4zMjU0NjU4OCAyNS4xNjIyNDI3LDAuNDc1OTk3MjI1IDE1Ljg5OTA5MiwyLjQyOTk5OTE3IEM2LjYzNTk0MTMxLDQuMzg0MDAxMTEgMC4wMDEyNTkzNDE1OCwxMi42MDY1MjI5IDAsMjIuMTM0MTI4NCBMMCwzMC4xMjI0ODcyIEwxMi4wODI5MTY4LDM4Ljg2OTk5ODkgTDEyLjA4MjkxNjgsMjIuMTM0MTI4NCBDMTIuMDgyOTE2OCwxNy43MjcwNDQxIDE1LjYzMTgzNzUsMTQuMTU0Mzk2MyAyMC4wMDk2NTMsMTQuMTU0Mzk2MyBDMjQuMzg3NDY4NSwxNC4xNTQzOTYzIDI3LjkzNjM4OTIsMTcuNzI3MDQ0MSAyNy45MzYzODkyLDIyLjEzNDEyODQgTDI3LjkzMjEwNDUsMjIuMTQyNzU1MSBaIiBpZD0icGF0aC0yIj48L3BhdGg+CiAgICA8L2RlZnM+CiAgICA8ZyBpZD0iTGF5b3V0cyIgc3Ryb2tlPSJub25lIiBzdHJva2Utd2lkdGg9IjEiIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCI+CiAgICAgICAgPGcgaWQ9IkxheW91dC0vLUhlYWRlciIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoLTQwLjAwMDAwMCwgLTEwLjAwMDAwMCkiPgogICAgICAgICAgICA8ZyBpZD0iQmFzaWM6LUljb25zLS8tUkEtU2lnbi0mYW1wOy1sb2dvLS8tTGFyZ2Utc2lnbjotZ3JlZW4iIHRyYW5zZm9ybT0idHJhbnNsYXRlKDQwLjAwMDAwMCwgOC4wMDAwMDApIj4KICAgICAgICAgICAgICAgIDxtYXNrIGlkPSJtYXNrLTMiIGZpbGw9IndoaXRlIj4KICAgICAgICAgICAgICAgICAgICA8dXNlIHhsaW5rOmhyZWY9IiNwYXRoLTIiPjwvdXNlPgogICAgICAgICAgICAgICAgPC9tYXNrPgogICAgICAgICAgICAgICAgPHVzZSBpZD0iTWFzayIgZmlsbD0idXJsKCNsaW5lYXJHcmFkaWVudC0xKSIgZmlsbC1ydWxlPSJldmVub2RkIiB4bGluazpocmVmPSIjcGF0aC0yIj48L3VzZT4KICAgICAgICAgICAgICAgIDxnIGlkPSJCYXNpYzotQ29sb3ItLy1QcmltYXJ5LS8tR3JhZGllbnQ6LUdyZWVuLXNvbGlkIiBtYXNrPSJ1cmwoI21hc2stMykiIGZpbGw9InVybCgjbGluZWFyR3JhZGllbnQtMSkiIGZpbGwtcnVsZT0iZXZlbm9kZCI+CiAgICAgICAgICAgICAgICAgICAgPHJlY3QgaWQ9IkdyYWRpZW50LUdyZWVuLXNvbGlkIiB4PSIwIiB5PSIwIiB3aWR0aD0iNDAiIGhlaWdodD0iNDAiPjwvcmVjdD4KICAgICAgICAgICAgICAgIDwvZz4KICAgICAgICAgICAgPC9nPgogICAgICAgIDwvZz4KICAgIDwvZz4KPC9zdmc+";
            add_menu_page(
                'Adwisely Integration',
                'Adwisely',
                'manage_options',
                RA_WC_Config::PLUGIN_SLUG,
                array($this, 'render'),
                $icon
            );
        }

        public function add_admin_notice()
        {
            error_log("Add notice");
            if (!self::is_ra_wc_plugin_page()) {
                $plugin_page = admin_url('/admin.php?page=' . RA_WC_Config::PLUGIN_SLUG);
                if (get_option(RA_WC_Config::OPT_REDIRECT)) {
                    delete_option(RA_WC_Config::OPT_REDIRECT);
                    exit(wp_redirect($plugin_page));
                }
                if (!$this->account->is_connected()) {
                    echo '<div class="notice notice-error"><p>You did not set up Adwisely yet. Click <a href=' . $plugin_page . '>here</a> to continue.</p></div>';
                }
            }
        }


        /**
         * Function to register our new routes from the controller.
         */
        public function register_ra_api_controller()
        {
            $controller = new RA_WC_API();
            $controller->register_routes();
        }

        public function handle_wp_ajax_ra_connect()
        {
            $login_url = $this->account->connect();

            if (is_null($login_url)) {
                wp_send_json_error(array(), 500);
            } else {
                wp_send_json(array('login_url' => $login_url));
            }
        }


        public function render()
        {
            if (!$this->account->is_connected()) {
                $permalink_structure = get_option('permalink_structure', null);
                include_once(dirname(__FILE__) . '/templates/connect.php');
                return;
            }

            $client = $this->account->get_client();
            $status = $client->get_status();
            if (array_key_exists('pixel_config', $status)) {
                $pixel_id = $status['pixel_config']['pixel_id'];
                if (isset($pixel_id) && is_numeric($pixel_id) && intval($pixel_id) > 0) {
                    $this->pixel_integration->pixel_id = $pixel_id;
                }
                $catalog_id = $status['pixel_config']['catalog_id'];
                if (isset($catalog_id) && is_numeric($catalog_id) && intval($catalog_id) > 0) {
                    $this->pixel_integration->catalog_id = $catalog_id;
                }
                $advanced_matching = $status['pixel_config']['advanced_matching'];
                if (isset($advanced_matching)) {
                    $this->pixel_integration->advanced_matching = boolval($advanced_matching);
                }
            }

            $login_url = $client->get_account_login_url();

            include_once(dirname(__FILE__) . '/templates/status.php');
        }

        public static function is_ra_wc_plugin_page()
        {
            global $plugin_page;
            return $plugin_page === RA_WC_Config::PLUGIN_SLUG;
        }
    }

    add_action('plugins_loaded', array('RetargetApp_WooCommerce_Integration', 'get_instance'));

    // Register activation hook
    register_activation_hook(
        __FILE__,
        function () {
            error_log("Activate " . __CLASS__);
            RA_WC_Utils::plugin_status_changed(RA_WC_Config::PLUGIN_STATUS_ACTIVE);
            update_option(RA_WC_Config::OPT_REDIRECT, 'true');
        }
    );

    // Register deactivation hook
    register_deactivation_hook(
        __FILE__,
        function () {
            error_log("Deactivate " . __CLASS__);
            RA_WC_Utils::plugin_status_changed(RA_WC_Config::PLUGIN_STATUS_INACTIVE);
        }
    );

    // Register uninstall hook
    register_uninstall_hook(__FILE__, "wc_ra_handle_uninstall");

    function wc_ra_handle_uninstall()
    {
        error_log("Uninstall " . __CLASS__);
        RA_WC_Utils::plugin_status_changed(RA_WC_Config::PLUGIN_STATUS_DELETED);
    }

endif;
?>