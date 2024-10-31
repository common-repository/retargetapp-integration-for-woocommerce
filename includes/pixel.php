<?php
if (!class_exists('RA_WC_Pixel')) :
    class RA_WC_Pixel
    {
        const PIXEL_RENDER = 'pixel_render';

        private $user_info;
        private $last_event;
        private $pixel_id;

        private static $render_cache = array();

        private static $default_pixel_basecode = "
<script type='text/javascript'>
    !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
    n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
    document,'script','https://connect.facebook.net/en_US/fbevents.js');
</script>
";

        public function __construct($pixel_id, $user_info = array())
        {
            $this->pixel_id = $pixel_id;
            $this->user_info = $user_info;
            $this->last_event = '';
        }

        public function pixel_base_code()
        {
            $pixel_id = $this->pixel_id;

            if (
                (
                    isset(self::$render_cache[self::PIXEL_RENDER]) &&
                    self::$render_cache[self::PIXEL_RENDER] === true
                ) ||
                !isset($pixel_id) ||
                $pixel_id === 0
            ) {
                return;
            }

            self::$render_cache[self::PIXEL_RENDER] = true;
            $params = $this->add_version_info();

            return sprintf(
                "
<!-- RA Pixel Integration Begin -->
%s
<script>
%s
fbq( 'track', 'PageView', %s);
document.addEventListener( 'DOMContentLoaded', function() {
    jQuery && jQuery( function( $ ) {
        // insert placeholder for events injected when a product is added to the cart through Ajax
        $( document.body ).append( '<div class=\"ra-pixel-event-placeholder\"></div>' );
    } );
}, false );
</script>
<!-- RA Pixel Integration end -->
    ",
                self::$default_pixel_basecode,
                $this->pixel_init_code(),
                json_encode($params, JSON_PRETTY_PRINT | JSON_FORCE_OBJECT)
                );
        }

        private function get_version_info()
        {
            global $wp_version;

            if (RA_WC_Pixel_Utils::isWoocommerceIntegration()) {
                return array(
                    'source' => 'woocommerce',
                    'version' => WC()->version,
                    'pluginVersion' => RA_WC_Config::PLUGIN_VERSION,
                );
            }

            return array(
                'source' => 'wordpress',
                'version' => $wp_version,
                'pluginVersion' => RA_WC_Config::PLUGIN_VERSION,
            );
        }

        private function add_version_info($params = array())
        {
            // if any parameter is passed in the pixel, do not overwrite it
            return array_replace($this->get_version_info(), $params);
        }

        private function pixel_init_code()
        {
            $version_info = $this->get_version_info();
            $agent_string = sprintf(
                '%s-%s-%s',
                $version_info['source'],
                $version_info['version'],
                $version_info['pluginVersion']
            );

            $params = array(
                'agent' => $agent_string,
            );

            return apply_filters(
                'ra_pixel_init',
                sprintf(
                    "fbq('init', '%s', %s, %s);\n",
                    esc_js($this->pixel_id),
                    json_encode($this->user_info, JSON_PRETTY_PRINT | JSON_FORCE_OBJECT),
                    json_encode($params, JSON_PRETTY_PRINT | JSON_FORCE_OBJECT)
                )
            );
        }

        public function inject_event($event_name, $params, $method = 'track')
        {
            if (RA_WC_Pixel_Utils::isWoocommerceIntegration()) {
                RA_WC_Pixel_Utils::wc_enqueue_js($this->get_event_code($event_name, $params, $method));
            } else {
                // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
                printf($this->get_event_script($event_name, $params, $method));
            }
        }

        public function get_event_code($event_name, $params, $method = 'track')
        {
            $this->last_event = $event_name;
            return $this->build_event($event_name, $params, $method);
        }

        public function get_event_script($event_name, $params, $method = 'track')
        {
            $output = '
<!-- RA Pixel Event Code -->
<script>
%s
</script>
<!-- End RA Pixel Event Code -->
';
            return sprintf($output, $this->get_event_code($event_name, $params, $method));
        }

        public function build_event($event_name, $params, $method = 'track')
        {
            $params = $this->add_version_info($params);
            return sprintf(
                "/* RA Pixel Integration Event Tracking */\n" .
                "fbq('%s', '%s', %s);",
                esc_js($method),
                esc_js($event_name),
                json_encode($params, JSON_PRETTY_PRINT | JSON_FORCE_OBJECT)
            );
        }

        public function check_last_event($event_name)
        {
            return $event_name === $this->last_event;
        }
    }
endif;
?>