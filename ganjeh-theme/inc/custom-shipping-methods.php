<?php
/**
 * Custom WooCommerce Shipping Methods
 *
 * Registers Ganjeh's custom shipping methods as proper WooCommerce shipping methods
 * so they appear in admin manual order creation and shipping zone settings.
 *
 * @package Ganjeh
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register custom shipping methods with WooCommerce
 */
function ganjeh_register_shipping_methods($methods) {
    $methods['ganjeh_post']       = 'Ganjeh_Shipping_Post';
    $methods['ganjeh_express']    = 'Ganjeh_Shipping_Express';
    $methods['ganjeh_collection'] = 'Ganjeh_Shipping_Collection';
    $methods['ganjeh_pickup']     = 'Ganjeh_Shipping_Pickup';
    return $methods;
}
add_filter('woocommerce_shipping_methods', 'ganjeh_register_shipping_methods');

/**
 * Initialize shipping method classes after WooCommerce loads
 */
function ganjeh_init_shipping_classes() {
    if (!class_exists('WC_Shipping_Method')) {
        return;
    }

    /**
     * ارسال پستی - Post Shipping
     */
    class Ganjeh_Shipping_Post extends WC_Shipping_Method {
        public function __construct($instance_id = 0) {
            $this->id                 = 'ganjeh_post';
            $this->instance_id        = absint($instance_id);
            $this->method_title       = 'ارسال پستی';
            $this->method_description = 'ارسال به سراسر کشور از طریق پست · حداکثر ۷ روز کاری';
            $this->supports           = array('shipping-zones', 'instance-settings');
            $this->enabled            = 'yes';
            $this->title              = 'ارسال پستی';

            $this->init();
        }

        public function init() {
            $this->init_form_fields();
            $this->init_settings();
            $this->title = $this->get_option('title', 'ارسال پستی');
        }

        public function init_form_fields() {
            $this->instance_form_fields = array(
                'title' => array(
                    'title'   => 'عنوان',
                    'type'    => 'text',
                    'default' => 'ارسال پستی',
                ),
                'cost' => array(
                    'title'   => 'هزینه (تومان)',
                    'type'    => 'number',
                    'default' => 90000,
                ),
            );
        }

        public function calculate_shipping($package = array()) {
            $cost = $this->get_option('cost', 90000);
            $this->add_rate(array(
                'id'    => $this->get_rate_id(),
                'label' => $this->title,
                'cost'  => $cost,
            ));
        }
    }

    /**
     * پیک فوری - Express Courier
     */
    class Ganjeh_Shipping_Express extends WC_Shipping_Method {
        public function __construct($instance_id = 0) {
            $this->id                 = 'ganjeh_express';
            $this->instance_id        = absint($instance_id);
            $this->method_title       = 'پیک فوری در تهران';
            $this->method_description = 'تحویل چند ساعته · مناطق ۲۲ گانه تهران';
            $this->supports           = array('shipping-zones', 'instance-settings');
            $this->enabled            = 'yes';
            $this->title              = 'پیک فوری در تهران';

            $this->init();
        }

        public function init() {
            $this->init_form_fields();
            $this->init_settings();
            $this->title = $this->get_option('title', 'پیک فوری در تهران');
        }

        public function init_form_fields() {
            $this->instance_form_fields = array(
                'title' => array(
                    'title'   => 'عنوان',
                    'type'    => 'text',
                    'default' => 'پیک فوری در تهران',
                ),
                'cost' => array(
                    'title'   => 'هزینه (تومان)',
                    'type'    => 'number',
                    'default' => 200000,
                ),
            );
        }

        public function calculate_shipping($package = array()) {
            $cost = $this->get_option('cost', 200000);
            $this->add_rate(array(
                'id'    => $this->get_rate_id(),
                'label' => $this->title,
                'cost'  => $cost,
            ));
        }
    }

    /**
     * ارسال عادی - Standard Collection Shipping
     */
    class Ganjeh_Shipping_Collection extends WC_Shipping_Method {
        public function __construct($instance_id = 0) {
            $this->id                 = 'ganjeh_collection';
            $this->instance_id        = absint($instance_id);
            $this->method_title       = 'ارسال عادی';
            $this->method_description = 'حداکثر ۵ روز کاری · مناطق ۲۲ گانه تهران';
            $this->supports           = array('shipping-zones', 'instance-settings');
            $this->enabled            = 'yes';
            $this->title              = 'ارسال عادی';

            $this->init();
        }

        public function init() {
            $this->init_form_fields();
            $this->init_settings();
            $this->title = $this->get_option('title', 'ارسال عادی');
        }

        public function init_form_fields() {
            $this->instance_form_fields = array(
                'title' => array(
                    'title'   => 'عنوان',
                    'type'    => 'text',
                    'default' => 'ارسال عادی',
                ),
                'cost' => array(
                    'title'   => 'هزینه (تومان)',
                    'type'    => 'number',
                    'default' => 90000,
                ),
            );
        }

        public function calculate_shipping($package = array()) {
            $cost = $this->get_option('cost', 90000);
            $this->add_rate(array(
                'id'    => $this->get_rate_id(),
                'label' => $this->title,
                'cost'  => $cost,
            ));
        }
    }

    /**
     * تحویل حضوری - In-Person Pickup
     */
    class Ganjeh_Shipping_Pickup extends WC_Shipping_Method {
        public function __construct($instance_id = 0) {
            $this->id                 = 'ganjeh_pickup';
            $this->instance_id        = absint($instance_id);
            $this->method_title       = 'تحویل حضوری';
            $this->method_description = 'حداقل ۲۴ ساعت بعد از ثبت سفارش';
            $this->supports           = array('shipping-zones', 'instance-settings');
            $this->enabled            = 'yes';
            $this->title              = 'تحویل حضوری';

            $this->init();
        }

        public function init() {
            $this->init_form_fields();
            $this->init_settings();
            $this->title = $this->get_option('title', 'تحویل حضوری');
        }

        public function init_form_fields() {
            $this->instance_form_fields = array(
                'title' => array(
                    'title'   => 'عنوان',
                    'type'    => 'text',
                    'default' => 'تحویل حضوری',
                ),
            );
        }

        public function calculate_shipping($package = array()) {
            $this->add_rate(array(
                'id'    => $this->get_rate_id(),
                'label' => $this->title,
                'cost'  => 0,
            ));
        }
    }
}
add_action('woocommerce_shipping_init', 'ganjeh_init_shipping_classes');
