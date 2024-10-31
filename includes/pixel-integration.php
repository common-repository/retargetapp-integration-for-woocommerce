<?php
if (!defined('ABSPATH')) {
    exit;
}

include_once(dirname(__FILE__) . '/pixel.php');
include_once(dirname(__FILE__) . '/pixel-utils.php');
include_once(dirname(__FILE__) . '/pixel-event-tracker.php');

class RA_WC_PixelIntegration
{

    private static $instance;

    private $pixel_id;
    private $catalog_id;
    private $advanced_matching = false;
    private $events_tracker;

    public static function get_instance()
    {
        if (!self::$instance) {

            $pixel_config = get_option(RA_WC_Config::OPT_PIXEL_CONFIG, array());
            $class = __CLASS__;
            error_log("Initialize " . __CLASS__);
            self::$instance = new $class($pixel_config);
        }
        return self::$instance;
    }

    private function __construct($pixel_config)
    {
        if (array_key_exists('pixel_id', $pixel_config)) {
            $this->pixel_id = $pixel_config['pixel_id'];
        }
        if (array_key_exists('catalog_id', $pixel_config)) {
            $this->catalog_id = $pixel_config['catalog_id'];
        }
        if (array_key_exists('advanced_matching', $pixel_config)) {
            $this->advanced_matching = $pixel_config['advanced_matching'];
        }
    }

    public function __get($name)
    {
        if (in_array($name, ['pixel_id', 'advanced_matching', 'catalog_id'])) {
            return $this->$name;
        }
        return null;
    }

    public function __set($name, $value)
    {
        if ($name == 'pixel_id' && (is_numeric($value) && intval($value) > 0)) {
            $this->pixel_id = strval($value);
        }
        if ($name == 'catalog_id' && (is_numeric($value) && intval($value) > 0)) {
            $this->catalog_id = strval($value);
        }
        if ($name == 'advanced_matching' && (is_bool($value))) {
            $this->advanced_matching = $value;
        }
        $pixel_config = array(
            'pixel_id' => $this->pixel_id,
            'advanced_matching' => $this->advanced_matching
        );
        update_option(
            $option = RA_WC_Config::OPT_PIXEL_CONFIG,
            $value = $pixel_config,
            $autoload = true
        );
    }

    public function inject_pixel()
    {
        if (!$this->is_pixel_installed()) {
            return;
        }

        $user_info = RA_WC_Pixel_Utils::get_user_info($this->advanced_matching);
        $this->events_tracker = new RA_WC_PixelEventTracker($this->pixel_id, $user_info);
    }

    public function remove_pixel()
    {
        $this->pixel_id = $this->advanced_matching = $this->catalog_id = null;
        delete_option(RA_WC_Config::OPT_PIXEL_CONFIG);
    }

    private function is_pixel_installed()
    {
        return isset($this->pixel_id);
    }
}
