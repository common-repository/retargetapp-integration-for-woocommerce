<?php

include_once(dirname(__FILE__) . '/config/config.php');

class RA_WC_Utils
{
    /**
     * Activation / Deactivation / Deletion hook
     */
    public static function plugin_status_changed($status)
    {
        error_log(RA_WC_Config::get_api_host() . RA_WC_Config::STATUS_ENDPOINT . ' [POST] ' . ' - ' . "plugin status updated to '" . $status . "'.");
        $store_id = get_option("ra_wc_store_id");
        $store_secret = get_option("ra_wc_store_secret");

        if (!$store_id || !$store_secret) {
            if ($status !== RA_WC_Config::PLUGIN_STATUS_DELETED) {
                error_log("unable to update plugin status due to '" . RA_WC_Config::OPT_STORE_ID . "' or '" . RA_WC_Config::OPT_STORE_SECRET . "' not found; plugin has not connected to ra yet");
                return;
            }
        }

        $data = array(
            'store_id' => $store_id,
            'ra_store_secret' => $store_secret,
            'plugin_status' => $status
        );
        $params = array(
            'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
            'body' => json_encode($data),
            'method' => 'POST',
            'timeout' => RA_WC_Config::REQUEST_TIMEOUT,
            'sslverify' => RA_WC_Config::get_request_ssl_verify()
        );
        $response = wp_remote_post(
            $url = RA_WC_Config::get_api_endpoint(RA_WC_Config::STATUS_ENDPOINT),
            $args = $params
        );

        if (is_array($response) && !empty($response['body'])) {
            $response_data = json_decode($response['body'], true);
            $response_status = $response_data['status'];
            if (isset($response_status)) {
                if ($response_status == 'updated') {
                    error_log('plugin status has been updated successfully');

                    /*
                     * Remove all ra options from db
                     */
                    if ($status === RA_WC_Config::PLUGIN_STATUS_DELETED) {
                        self::clear();
                    }

                } elseif ($response_status == 'error') {
                    error_log('unable to update plugin status due to: ' . $response_data['msg']);
                } else {
                    error_log('status unknown');
                }
            } else {
                error_log('unable to fetch status from response');
            }
        } else {
            error_log(print_r($response, TRUE));
            return;
        }
    }

    public static function clear() {
        global $wpdb;
        error_log('starting to remove ra plugin options from the database');
        $api_key_id = get_option(RA_WC_Config::OPT_WC_API_KEY_ID, null);
        if ($api_key_id !== null) {
            $wpdb->delete(
                $wpdb->prefix . 'woocommerce_api_keys',
                array('key_id' => $api_key_id),
                array('%d')
            );
        }

        $options_to_delete = array(
            RA_WC_Config::OPT_STORE_ID,
            RA_WC_Config::OPT_STORE_SECRET,
            RA_WC_Config::OPT_PIXEL_CONFIG,
            RA_WC_Config::OPT_WC_API_KEY_ID,
            RA_WC_Config::OPT_REDIRECT
        );
        foreach ($options_to_delete as $option) {
            if (get_option($option)) delete_option($option);
        }

        error_log('ra plugin options have been removed successfully');
    }
}
