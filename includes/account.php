<?php

class RA_WC_Account
{
    private static $instance = null;

    /**
     * @return RA_WC_Account
     */
    public static function get_instance()
    {
        if (!self::$instance) {
            $class = __CLASS__;

            error_log("Initialize " . __CLASS__);
            self::$instance = new $class(get_option(RA_WC_Config::OPT_STORE_ID, null), get_option(RA_WC_Config::OPT_STORE_SECRET, null));
        }
        return self::$instance;
    }

    private $ra_store_id;
    private $ra_store_secret;

    private function __construct($ra_store_id, $ra_store_secret)
    {
        $this->ra_store_id = $ra_store_id;
        $this->ra_store_secret = $ra_store_secret;
    }


    public function is_connected()
    {
        return $this->ra_store_id && $this->ra_store_secret;
    }

    public function connect()
    {
        $ra_store_secret = $this->generate_secret();
        $wp_user = wp_get_current_user();
        $admin_id = $wp_user->ID;


        $data = RA_WC_API_Client::connect(
            get_site_url(),
            $admin_id,
            $ra_store_secret
        );

        // Process errors. Null returned if error happened
        if (is_null($data)) {
            RA_WC_Utils::clear();
            return;
        }

        $this->connect_account($data['ra_store_id'], $ra_store_secret);

        return $this->get_client()->get_install_callback_url();
    }

    private function connect_account($ra_store_id, $ra_store_secret)
    {
        update_option(RA_WC_Config::OPT_STORE_ID, $ra_store_id);
        $this->ra_store_id = $ra_store_id;
    }

    private function generate_secret()
    {
        $this->ra_store_secret = wc_rand_hash();
        update_option(RA_WC_Config::OPT_STORE_SECRET, $this->ra_store_secret);
        return $this->ra_store_secret;
    }

    public function get_client()
    {
        if (!$this->is_connected()) {
            return null;
        }

        return new RA_WC_API_Client($this->ra_store_id, $this->ra_store_secret);
    }

    public function __get($name)
    {
        if (in_array($name, ['ra_store_id', 'ra_store_secret'])) {
            return $this->$name;
        }
        return null;
    }
}