<?php
include_once 'pixel.php';
include_once 'pixel-integration.php';
include_once 'pixel-utils.php';

include_once 'account.php';

class RA_WC_API extends WP_REST_Controller
{

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes()
    {
        $version = '1';

        $plugin_name = basename(plugin_dir_path(dirname(__FILE__, 2)));
        $namespace = 'wc-ra-integration/v' . $version;

        $endpoints = array(
            array('site-info', 'get_site_info', WP_REST_Server::READABLE),
            array('generate-credentials', 'generate_credentials', WP_REST_Server::READABLE),
            array('products-count', 'get_products_count', WP_REST_Server::READABLE),
            array('pixel', 'get_pixel', WP_REST_Server::READABLE),
            array('pixel', 'set_pixel', WP_REST_Server::CREATABLE),
            array('pixel', 'unset_pixel', WP_REST_Server::DELETABLE),
        );

        foreach ($endpoints as $endpoint) {
            register_rest_route($namespace, '/' . $endpoint[0] . '/', array(
                'callback' => array($this, $endpoint[1]),
                'methods' => $endpoint[2],
                'permission_callback' => array($this, 'validate_hmac')
            ));
        }
    }

    public function validate_hmac($request)
    {
        $timestamp_now = time();
        $timestamp_from_ra = $request['ts'];

        if (($timestamp_now - $timestamp_from_ra) > 30000) {
            error_log("RA validation HMAC error by ts timeout");
            return false;
        };

        $ra_store_secret = RA_WC_Account::get_instance()->ra_store_secret;
        $hmac = $request['hmac'];

        $admin_id = $request['admin_id'];
        $format = 'admin_id=%s&ts=%s';

        $string_to_encode = sprintf($format, $admin_id, $timestamp_from_ra);
        $hmac_to_compare = hash_hmac("sha256", $string_to_encode, $ra_store_secret);

        error_log("Generate HMAC with params: secret: " . $ra_store_secret .
            ", ts: " . $timestamp_from_ra . ", admin_id: " . $admin_id . ". HMAC: " . $hmac_to_compare);

        $valid = $hmac_to_compare == $hmac;

        if (!$valid) {
            error_log("RA validation hmac error. HMAC mismatch");
        }

        return $valid;
    }

    public function get_site_info($request)
    {
        error_log("RA WC API -  get_site_info request");
        $wp_user = get_userdata($request['admin_id']);
        $wp_user_phone_number = get_user_meta($user_id=$request['admin_id'], $key="billing_phone", $single=true);
        $data = array(
            'admin_email' => $wp_user->user_email,
            'admin_display_name' => $wp_user->display_name,
            'admin_first_name' => $wp_user->user_firstname,
            'admin_last_name' => $wp_user->user_lastname,
            'admin_phone_number' => $wp_user_phone_number,
            'timezone_string' => get_option('timezone_string'),
            'gmt_offset' => get_option('gmt_offset')
        );
        return new WP_REST_Response($data, 200);
    }

    public function generate_credentials($request)
    {
        global $wpdb;
        error_log("RA WC API -  generate_credentials");

        $api_key_id = get_option(RA_WC_Config::OPT_WC_API_KEY_ID, null);

        if ($api_key_id !== null) {
            $wpdb->delete(
                $wpdb->prefix . 'woocommerce_api_keys',
                array('key_id' => $api_key_id),
                array('%d')
            );
        }

        $description = sprintf('Key for Adwisely integration');
        $user_id = $request['admin_id'];

        // Created API keys.
        $consumer_key = 'ck_' . wc_rand_hash();
        $consumer_secret = 'cs_' . wc_rand_hash();
        $scopes = 'read';

        $wpdb->insert(
            $wpdb->prefix . 'woocommerce_api_keys',
            array(
                'user_id' => $user_id,
                'description' => $description,
                'permissions' => $scopes,
                'consumer_key' => wc_api_hash($consumer_key),
                'consumer_secret' => $consumer_secret,
                'truncated_key' => substr($consumer_key, -7),
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s',)
        );

        $api_key_id = $wpdb->insert_id;
        update_option(RA_WC_Config::OPT_WC_API_KEY_ID, $api_key_id);

        $wc_version = get_option('woocommerce_version');

        if (version_compare($wc_version, '3.0.0') < 0) {
            $wc_api_version = 'v1';
        } else if (version_compare($wc_version, '3.0.0') >= 0 && version_compare($wc_version, '3.5.0') < 0) {
            $wc_api_version = 'v2';
        } else {
            $wc_api_version = 'v3';
        }

        $data = array(
            'consumer_key' => $consumer_key,
            'consumer_secret' => $consumer_secret,
            'scopes' => $scopes,
            'wc_api_version' => $wc_api_version
        );
        return new WP_REST_Response($data, 200);
    }

    public function get_products_count($request)
    {
        error_log("RA WC API -  get_products_count");
        $products_count = wp_count_posts('product');
        $data = array('products_count' => $products_count->publish);
        return new WP_REST_Response($data, 200);
    }

    public function get_pixel($request)
    {
        error_log("RA WC API -  get_pixel");
        $data = array(
            'pixel_id' => RA_WC_PixelIntegration::get_instance()->pixel_id,
            'catalog_id' => RA_WC_PixelIntegration::get_instance()->catalog_id,
            'advanced_matching' => RA_WC_PixelIntegration::get_instance()->advanced_matching
        );
        return new WP_REST_Response($data, 200);
    }

    public function set_pixel($request)
    {
        error_log("RA WC API -  set_pixel");
        $pixel_id = $request['pixel_id'];
        $catalog_id = $request['catalog_id'];
        $advanced_matching = $request['enable_advanced_matching'];
        $pixel_integration = RA_WC_PixelIntegration::get_instance();
        $success = false;
        if (isset($pixel_id) && is_numeric($pixel_id) && intval($pixel_id) > 0) {
            $pixel_integration->pixel_id = $pixel_id;
            $success = true;
        }
        if (isset($catalog_id) && is_numeric($catalog_id) && intval($catalog_id) > 0) {
            $pixel_integration->catalog_id = $catalog_id;
        }
        if (isset($advanced_matching)) {
            $pixel_integration->advanced_matching = boolval($advanced_matching);
        }
        if (!$success) {
            error_log('Unable to install pixel to shop. Pixel ID or catalog ID not found.');
            $data = array(
                'status' => 'failed',
                'error' => 'wc-ra-plugin-pixel-installation-failed'
            );
            return new WP_REST_Response($data, 400);
        }
        return new WP_REST_Response(array('status' => 'success'), 200);
    }

    public function unset_pixel($request)
    {
        error_log("RA WC API -  unset_pixel");
        RA_WC_PixelIntegration::get_instance()->remove_pixel();
        return new WP_REST_Response($status = 200);
    }
}
