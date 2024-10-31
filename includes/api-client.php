<?php


class RA_WC_API_Client
{
    private $store_id;
    private $store_secret;

    public function __construct($store_id, $store_secret)
    {
        $this->store_id = $store_id;
        $this->store_secret = $store_secret;
    }

    public function get_status()
    {
        $query = $this->generate_request_signature();

        $response = wp_remote_get(
            $url = RA_WC_Config::get_api_endpoint(RA_WC_Config::STATUS_ENDPOINT) . '?' . $query,
            $args = array(
                'timeout' => RA_WC_Config::REQUEST_TIMEOUT,
                'sslverify' => RA_WC_Config::get_request_ssl_verify()
            )
        );
        $data = wp_remote_retrieve_body($response);

        if (wp_remote_retrieve_response_code($response) != 200 || empty($data)) {
            error_log("Get Adwisely account status error: \n" . print_r($response, TRUE));
            return;
        }

        error_log(print_r($data, true));
        return json_decode($data, true);
    }

    public function get_account_login_url()
    {
        return RA_WC_Config::get_app_host() . RA_WC_Config::CALLBACKS_ENDPOINT . '?' . $this->generate_request_signature();
    }

    public function get_install_callback_url()
    {
        return RA_WC_Config::get_app_host() . RA_WC_Config::INSTALL_CALLBACK . '?' . $this->generate_request_signature();
    }

    private function generate_request_signature()
    {
        $ts = time();
        $query_string = "store_id=" . $this->store_id . "&ts=" . $ts;
        $sig = hash_hmac('sha256', $query_string, $this->store_secret);
        return $query_string . '&hmac=' . $sig;
    }

    public static function connect($site_url, $admin_id, $ra_store_secret)
    {
        $data = array(
            'site_url' => $site_url,
            'admin_id' => $admin_id,
            'ra_store_secret' => $ra_store_secret
        );
        error_log(RA_WC_Config::get_api_endpoint(RA_WC_Config::CONNECT_ENDPOINT) . ' [POST] ' . var_export($data, true));
        $params = array(
            'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
            'body' => json_encode($data),
            'method' => 'POST',
            'timeout' => RA_WC_Config::REQUEST_TIMEOUT,
            'sslverify' => RA_WC_Config::get_request_ssl_verify()
        );
        $response = wp_remote_post(
            $url = RA_WC_Config::get_api_endpoint(RA_WC_Config::CONNECT_ENDPOINT),
            $args = $params
        );

        $data = wp_remote_retrieve_body($response);

        if (wp_remote_retrieve_response_code($response) != 200 || empty($data)) {
            error_log("Connect Adwisely account error: \n" . print_r($response, TRUE));
            return;
        }

        error_log(print_r($data, true));
        return json_decode($data, true);
    }
}

?>