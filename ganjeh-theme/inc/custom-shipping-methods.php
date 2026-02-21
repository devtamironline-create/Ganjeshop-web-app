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

/**
 * Auto-fill shipping costs in admin manual order creation
 */
function ganjeh_admin_shipping_auto_cost() {
    $screen = get_current_screen();
    if (!$screen || !in_array($screen->id, array('shop_order', 'woocommerce_page_wc-orders'))) {
        return;
    }

    $costs = array(
        'ganjeh_post'       => 90000,
        'ganjeh_express'    => 200000,
        'ganjeh_collection' => 90000,
        'ganjeh_pickup'     => 0,
    );
    ?>
    <script>
    jQuery(function($) {
        var shippingCosts = <?php echo wp_json_encode($costs); ?>;

        // Listen for shipping method changes in order items
        $('#woocommerce-order-items').on('change', 'select.shipping_method, select[name^="shipping_method"]', function() {
            var method = $(this).val();
            if (method && shippingCosts.hasOwnProperty(method)) {
                var $row = $(this).closest('tr, .shipping');
                var $costInput = $row.find('input.line_total, input[name^="shipping_cost"]');
                if ($costInput.length) {
                    $costInput.val(shippingCosts[method]).trigger('change');
                }
            }
        });

        // Also watch for dynamically added shipping lines via MutationObserver
        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                $(mutation.addedNodes).find('select.shipping_method, select[name^="shipping_method"]').each(function() {
                    var $select = $(this);
                    $select.off('change.ganjeh_cost').on('change.ganjeh_cost', function() {
                        var method = $(this).val();
                        if (method && shippingCosts.hasOwnProperty(method)) {
                            var $row = $(this).closest('tr, .shipping');
                            var $costInput = $row.find('input.line_total, input[name^="shipping_cost"]');
                            if ($costInput.length) {
                                $costInput.val(shippingCosts[method]).trigger('change');
                            }
                        }
                    });
                });
            });
        });

        var orderItems = document.getElementById('woocommerce-order-items');
        if (orderItems) {
            observer.observe(orderItems, { childList: true, subtree: true });
        }

        // Hook into WooCommerce's AJAX response for adding shipping
        $(document.body).on('wc_backbone_modal_loaded', function() {
            setTimeout(function() {
                $('.wc-backbone-modal select.shipping_method, .wc-backbone-modal select[name="method_id"]').on('change', function() {
                    var method = $(this).val();
                    if (method && shippingCosts.hasOwnProperty(method)) {
                        var $modal = $(this).closest('.wc-backbone-modal');
                        var $costInput = $modal.find('input#cost, input[name="cost"]');
                        if ($costInput.length && !$costInput.val()) {
                            $costInput.val(shippingCosts[method]).trigger('change');
                        }
                    }
                });
            }, 100);
        });

        // Also handle the "Add shipping" modal specifically
        $(document.body).on('wc_backbone_modal_response', function(e, target) {
            // After modal response, costs should already be set
        });

        // Direct hook: when WooCommerce renders shipping method select in modal
        $(document).on('change', '.wc-backbone-modal-content select[name="method_id"]', function() {
            var method = $(this).val();
            if (method && shippingCosts.hasOwnProperty(method)) {
                var $costInput = $(this).closest('.wc-backbone-modal-content').find('input[name="cost"]');
                if ($costInput.length) {
                    $costInput.val(shippingCosts[method]);
                }
            }
        });
    });
    </script>
    <?php
}
add_action('admin_footer', 'ganjeh_admin_shipping_auto_cost');

/**
 * Per-product shipping method restrictions
 *
 * Adds checkboxes to the product Shipping tab so the admin can choose
 * which shipping methods are allowed for each product. On checkout,
 * methods not allowed by ANY product in the cart are hidden.
 */

/**
 * All available shipping methods with labels
 */
function ganjeh_get_all_shipping_methods_list() {
    return [
        'post'       => 'ارسال پستی',
        'express'    => 'پیک فوری در تهران',
        'collection' => 'ارسال عادی',
        'pickup'     => 'تحویل حضوری',
    ];
}

/**
 * Add shipping restriction checkboxes to product Shipping tab
 */
function ganjeh_product_shipping_restrictions() {
    global $post;
    $product_id = $post->ID;
    $methods = ganjeh_get_all_shipping_methods_list();
    $saved = get_post_meta($product_id, '_ganjeh_allowed_shipping', true);

    // Default: all methods allowed
    if (!is_array($saved) || empty($saved)) {
        $saved = array_keys($methods);
    }

    echo '<div class="options_group" style="border-top:1px solid #eee;padding-top:12px;">';
    echo '<p class="form-field" style="padding:0 12px;"><strong>' . __('روش‌های ارسال مجاز', 'ganjeh') . '</strong></p>';
    echo '<p class="form-field" style="padding:0 12px;color:#666;font-size:12px;margin-top:-8px;">' . __('روش‌هایی که تیک نخورده باشند، برای سفارش‌هایی که شامل این محصول هستند نمایش داده نمی‌شوند.', 'ganjeh') . '</p>';

    foreach ($methods as $key => $label) {
        $checked = in_array($key, $saved) ? 'checked' : '';
        echo '<p class="form-field" style="padding:0 12px 4px;">';
        echo '<label style="display:inline-flex;align-items:center;gap:6px;cursor:pointer;">';
        echo '<input type="checkbox" name="_ganjeh_allowed_shipping[]" value="' . esc_attr($key) . '" ' . $checked . '>';
        echo '<span>' . esc_html($label) . '</span>';
        echo '</label>';
        echo '</p>';
    }

    echo '</div>';
}
add_action('woocommerce_product_options_shipping', 'ganjeh_product_shipping_restrictions');

/**
 * Save per-product shipping restrictions
 */
function ganjeh_save_product_shipping_restrictions($post_id) {
    if (isset($_POST['_ganjeh_allowed_shipping'])) {
        $allowed = array_map('sanitize_text_field', $_POST['_ganjeh_allowed_shipping']);
        update_post_meta($post_id, '_ganjeh_allowed_shipping', $allowed);
    } else {
        // No checkbox checked = no methods allowed (edge case)
        update_post_meta($post_id, '_ganjeh_allowed_shipping', []);
    }
}
add_action('woocommerce_process_product_meta', 'ganjeh_save_product_shipping_restrictions');

/**
 * Get restricted shipping methods for current cart
 *
 * Returns array of method keys that should be HIDDEN because
 * at least one product in the cart doesn't allow them.
 */
function ganjeh_get_cart_restricted_shipping() {
    if (!WC()->cart) {
        return [];
    }

    $all_methods = array_keys(ganjeh_get_all_shipping_methods_list());
    $allowed_by_all = $all_methods; // Start with all methods allowed

    foreach (WC()->cart->get_cart() as $cart_item) {
        $product_id = $cart_item['product_id'];
        $saved = get_post_meta($product_id, '_ganjeh_allowed_shipping', true);

        // If no restriction set, all methods are allowed for this product
        if (!is_array($saved) || empty($saved)) {
            continue;
        }

        // Intersect: only keep methods allowed by ALL products
        $allowed_by_all = array_intersect($allowed_by_all, $saved);
    }

    // Restricted = all methods minus what's allowed
    return array_diff($all_methods, $allowed_by_all);
}
