<?php
if ( ! class_exists('RA_WC_PixelEventTracker')):

	if ( ! class_exists('RA_WC_Pixel_Utils')) {
		include_once(dirname(__FILE__) . '/pixel-utils.php');
	}

	if ( ! class_exists('RA_WC_Pixel')) {
		include_once(dirname(__FILE__) . '/pixel.php');
	}

    class RA_WC_PixelEventTracker {
		private $pixel;
		private static $isEnabled = true;
		const RA_PRIORITY_HIGH    = 2;
		const RA_PRIORITY_LOW     = 11;

		public function __construct($pixel_id, $user_info) {
			$this->pixel = new RA_WC_Pixel($pixel_id, $user_info);
			add_action('wp_head', array($this, 'apply_filters'));
			add_action('wp_head', array($this, 'inject_base_pixel'));
			add_action('woocommerce_after_single_product', [ $this, 'inject_view_content_event' ]);
			add_action('woocommerce_add_to_cart', [ $this, 'inject_add_to_cart_event' ], 40, 4 );
			add_action('woocommerce_ajax_added_to_cart', [ $this, 'add_filter_for_add_to_cart_fragments' ] );
			if ( get_option( 'woocommerce_cart_redirect_after_add' === 'yes') ) {
				add_filter( 'woocommerce_add_to_cart_redirect', [ $this, 'set_last_product_added_to_cart_upon_redirect' ], 10, 2 );
				add_action( 'woocommerce_after_cart',           [ $this, 'inject_add_to_cart_redirect_event' ], 10, 2 );
			}
			add_action('woocommerce_thankyou', array( $this, 'inject_gateway_purchase_event' ), self::RA_PRIORITY_HIGH);
			add_action('woocommerce_payment_complete', array( $this, 'inject_purchase_event' ), self::RA_PRIORITY_HIGH);
        }

        public function apply_filters() {
			self::$isEnabled = apply_filters(
				'ra_integration_pixel_enabled',
				self::$isEnabled
			);
		}

		public function inject_base_pixel(){
			if ( self::$isEnabled ) {
				echo $this->pixel->pixel_base_code();
		    }
        }

        public function inject_view_content_event() {
			global $post;
			if ( ! self::$isEnabled || ! isset( $post->ID ) ) {
				return;
			}

			$product = wc_get_product( $post->ID );

			if ( ! $product instanceof \WC_Product ) {
				return;
			}

			// if product is variable or grouped, fire the pixel with content_type: product_group
			if ( $product->is_type( [ 'variable', 'grouped' ] ) ) {
				$content_type = 'product_group';
			} else {
				$content_type = 'product';
			}

			$this->pixel->inject_event( 'ViewContent', [
				'content_name' => $product->get_title(),
				'content_ids'  => wp_json_encode( \RA_WC_Pixel_Utils::get_fb_content_ids( $product ) ),
				'content_type' => $content_type,
				'value'        => $product->get_price(),
				'currency'     => get_woocommerce_currency(),
			] );
		}

		public function inject_add_to_cart_event( $cart_item_key, $product_id, $quantity, $variation_id ) {
			if ( ! self::$isEnabled || ! $product_id || ! $quantity ) {
				return;
			}

			$product = wc_get_product( $variation_id ?: $product_id );

			if ( ! $product instanceof \WC_Product ) {
				return;
			}

			$this->pixel->inject_event( 'AddToCart', [
				'content_ids'  => $this->get_cart_content_ids(),
				'content_type' => 'product',
				'contents'     => $this->get_cart_contents(),
				'value'        => $this->get_cart_total(),
				'currency'     => get_woocommerce_currency(),
			] );
		}

		private function get_cart_content_ids() {
			$product_ids = [ [] ];
			if ( $cart = WC()->cart ) {
				foreach ( $cart->get_cart() as $item ) {
					if ( isset( $item['data'] ) && $item['data'] instanceof \WC_Product ) {
						$product_ids[] = \RA_WC_Pixel_Utils::get_fb_content_ids( $item['data'] );
					}
				}
			}

			return wp_json_encode( array_unique( array_merge( ... $product_ids ) ) );
		}

		private function get_cart_contents() {
			$cart_contents = [];
			if ( $cart = WC()->cart ) {
				foreach ( $cart->get_cart() as $item ) {
					if ( ! isset( $item['data'], $item['quantity'] ) || ! $item['data'] instanceof \WC_Product ) {
						continue;
					}
					$content = new \stdClass();
					$content->id       = \RA_WC_Pixel_Utils::get_fb_retailer_id( $item['data'] );
					$content->quantity = $item['quantity'];
					$cart_contents[] = $content;
				}
			}
			return wp_json_encode( $cart_contents );
		}

		private function get_cart_total() {
			return WC()->cart ? WC()->cart->total : 0;
		}

		public function inject_purchase_event( $order_id ) {
			if ( ! self::$isEnabled || $this->pixel->check_last_event( 'Purchase' ) ) {
				return;
			}
			$order        = new \WC_Order( $order_id );
			$content_type = 'product';
			$product_ids  = [ [] ];

			foreach ( $order->get_items() as $item ) {
				if ( $product = isset( $item['product_id'] ) ? wc_get_product( $item['product_id'] ) : null ) {
					$product_ids[] = \RA_WC_Pixel_Utils::get_fb_content_ids( $product );
					if ( 'product_group' !== $content_type && $product->is_type( 'variable' ) ) {
						$content_type = 'product_group';
					}
				}
			}

			$product_ids = wp_json_encode( array_merge( ... $product_ids ) );
			$this->pixel->inject_event( 'Purchase', [
				'num_items'    => $this->get_cart_num_items(),
				'content_ids'  => $product_ids,
				'content_type' => $content_type,
				'value'        => $order->get_total(),
				'currency'     => get_woocommerce_currency(),
			] );
		}

		private function get_cart_num_items() {
			return WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
		}

		public function inject_gateway_purchase_event( $order_id ) {
			if ( ! self::$isEnabled ||
			  $this->pixel->check_last_event( 'Purchase' ) ) {
				return;
			}

			$order   = new WC_Order( $order_id );
			$payment = $order->get_payment_method();
			$this->inject_purchase_event( $order_id );
		}

		public function add_filter_for_add_to_cart_fragments() {
			if ( 'no' === get_option( 'woocommerce_cart_redirect_after_add' ) ) {
				add_filter( 'woocommerce_add_to_cart_fragments', [ $this, 'add_add_to_cart_event_fragment' ] );
			}
		}

		public function add_add_to_cart_event_fragment( $fragments ) {
			if ( self::$isEnabled ) {
				$script = $this->pixel->get_event_script( 'AddToCart', [
					'content_ids'  => $this->get_cart_content_ids(),
					'content_type' => 'product',
					'contents'     => $this->get_cart_contents(),
					'value'        => $this->get_cart_total(),
					'currency'     => get_woocommerce_currency(),
				] );

				$fragments['div.ra-pixel-event-placeholder'] = '<div class="ra-pixel-event-placeholder">' . $script . '</div>';
			}

			return $fragments;
		}

		public function set_last_product_added_to_cart_upon_redirect( $redirect, $product ) {
			if ( $product instanceof \WC_Product ) {
				WC()->session->set( 'ra_for_woocommerce_last_product_added_to_cart', $product->get_id() );
			}

			return $redirect;
		}

		public function inject_add_to_cart_redirect_event() {
			if ( ! self::$isEnabled ) {
				return;
			}
			$last_product_id = WC()->session->get( 'ra_for_woocommerce_last_product_added_to_cart', 0 );

			if ( $last_product_id > 0 ) {
				$this->inject_add_to_cart_event( '', $last_product_id, 1, 0 );
				WC()->session->set( 'ra_for_woocommerce_last_product_added_to_cart', 0 );
			}
		}

    }
endif;
?>