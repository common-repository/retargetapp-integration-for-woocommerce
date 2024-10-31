<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
include_once(ABSPATH . 'wp-includes/pluggable.php');

if ( ! class_exists('RA_WC_Pixel_Utils') ) :

	/**
	 * Helper functions
	 */
	class RA_WC_Pixel_Utils {
	    const RA_RETAILER_ID_PREFIX = 'wc_post_id_';
	    const PLUGIN_VERSION = RetargetApp_WooCommerce_Integration::PLUGIN_VERSION;

	    public static function isWoocommerceIntegration() {
			return class_exists( 'WooCommerce' );
		}

		public static function getCurrentUser(){
		    global $current_user;
			if ( ! empty( $current_user ) ) {
                if ( $current_user instanceof WP_User ) {
                    return $current_user;

                } elseif ( is_object( $current_user ) && isset( $current_user->ID ) ) {
                    $cur_id = $current_user->ID;
                    wp_set_current_user( $cur_id );
                    return $current_user;

                } else {
                    return null;
                }
            }
            $user_id = apply_filters( 'determine_current_user', false );
            if ( ! $user_id ) {
                return null;
            }
            wp_set_current_user( $user_id );
            return $current_user;
		}

		public static function get_user_info( $use_pii ) {
			$user = RA_WC_Pixel_Utils::getCurrentUser();
			if (null === $user || 0 === $user->ID || $use_pii === false ) {
				// User not logged in or admin chose not to send PII.
				return array();
			} else {
				return array_filter(
					array(
						// Keys documented in
						// https://developers.facebook.com/docs/facebook-pixel/pixel-with-ads/
						// /conversion-tracking#advanced_match
						'em' => $user->user_email,
						'fn' => $user->user_firstname,
						'ln' => $user->user_lastname,
					),
					function ( $value ) {
						return $value !== null && $value !== '';
					}
				);
			}
		}

		public static function wc_enqueue_js( $code ) {
			global $wc_queued_js;

			if ( function_exists( 'wc_enqueue_js' ) && empty( $wc_queued_js ) ) {
				wc_enqueue_js( $code );
			} else {
				$wc_queued_js = $code . "\n" . $wc_queued_js;
			}
		}

		public static function get_fb_content_ids( $woo_product ) {
			return array( self::get_fb_retailer_id( $woo_product ) );
		}

		public static function get_fb_retailer_id( $woo_product ) {
			$woo_id = $woo_product->get_id();

			// Call $woo_product->get_id() instead of ->id to account for Variable
			// products, which have their own variant_ids.
			return $woo_product->get_sku() ? $woo_product->get_sku() . '_' .
			$woo_id : self::RA_RETAILER_ID_PREFIX . $woo_id;
		}

		public static function is_valid_id( $pixel_id ) {
			return isset($pixel_id) && is_numeric($pixel_id) && (int) $pixel_id > 0;
		}
    }
endif;
?>