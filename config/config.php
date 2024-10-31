<?php

class RA_WC_Config
{

    const PLUGIN_VERSION = '1.0';
    const PLUGIN_SLUG = 'wc-ra-integration';
    const API_PATH = '/wc-api/v1.0';

    const REQUEST_TIMEOUT = 18;

    const CALLBACKS_ENDPOINT = '/callbacks/wc';
    const STATUS_ENDPOINT = '/status';
    const CONNECT_ENDPOINT = '/connect';
    const INSTALL_CALLBACK = '/woocommerce/install-callback';

    const OPT_STORE_ID = 'ra_wc_store_id';
    const OPT_STORE_SECRET = 'ra_wc_store_secret';
    const OPT_WC_API_KEY_ID = 'ra_wc_api_key_id';
    const OPT_PIXEL_CONFIG = 'ra_wc_pixel_config';
    const OPT_REDIRECT = 'ra_wc_redirect';

    const PLUGIN_STATUS_ACTIVE = "active";
    const PLUGIN_STATUS_INACTIVE = "inactive";
    const PLUGIN_STATUS_DELETED = "deleted";

    public static function get_api_host()
    {
        $env_var = getenv("RA_API_HOST");
        return $env_var ? $env_var : 'http://app.adwisely.com';
    }

    public static function get_api_endpoint($endpoint)
    {
        return self::get_api_host() . self::API_PATH . $endpoint;
    }

    public static function get_app_host()
    {
        $env_var = getenv("RA_APP_HOST");
        return $env_var ? $env_var : 'http://app.adwisely.com';
    }

    public static function get_intercom_app_id()
    {
        $env_var = getenv("RA_INTERCOM_APP_ID");
        return $env_var ? $env_var : 'jx5y0q3b';
    }

    public static function get_request_ssl_verify()
    {
        $env_var = getenv("RA_SSL_VERIFY");
        return !(!is_null($env_var) && $env_var == 0);
    }
}
