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
 * Add shipping restrictions to General tab AND as meta box (covers all product types)
 */
function ganjeh_shipping_restrictions_meta_box() {
    $screens = ['product'];
    foreach ($screens as $screen) {
        add_meta_box(
            'ganjeh_shipping_restrictions',
            __('روش‌های ارسال مجاز', 'ganjeh'),
            'ganjeh_shipping_restrictions_meta_box_content',
            $screen,
            'side',
            'high'
        );
    }
}
add_action('add_meta_boxes', 'ganjeh_shipping_restrictions_meta_box');
add_action('add_meta_boxes_product', 'ganjeh_shipping_restrictions_meta_box');

function ganjeh_shipping_restrictions_meta_box_content($post) {
    $methods = ganjeh_get_all_shipping_methods_list();
    $saved = get_post_meta($post->ID, '_ganjeh_allowed_shipping', true);

    if (!is_array($saved) || empty($saved)) {
        $saved = array_keys($methods);
    }

    wp_nonce_field('ganjeh_shipping_restrictions', 'ganjeh_shipping_restrictions_nonce');

    echo '<p style="color:#666;font-size:12px;margin:0 0 10px;">' . __('تیک روش‌هایی که برای این محصول مجاز هستند را بزنید.', 'ganjeh') . '</p>';

    foreach ($methods as $key => $label) {
        $checked = in_array($key, $saved) ? 'checked' : '';
        echo '<label style="display:block;margin:6px 0;cursor:pointer;">';
        echo '<input type="checkbox" name="_ganjeh_allowed_shipping[]" value="' . esc_attr($key) . '" ' . $checked . '> ';
        echo esc_html($label);
        echo '</label>';
    }
}

/**
 * Also inject via JS in admin footer as fallback for HPOS or block editor
 */
function ganjeh_shipping_restrictions_admin_fallback() {
    $screen = get_current_screen();
    if (!$screen || $screen->post_type !== 'product') {
        return;
    }

    global $post;
    if (!$post) return;

    $methods = ganjeh_get_all_shipping_methods_list();
    $saved = get_post_meta($post->ID, '_ganjeh_allowed_shipping', true);
    if (!is_array($saved) || empty($saved)) {
        $saved = array_keys($methods);
    }
    ?>
    <script>
    jQuery(function($){
        // If meta box already exists, skip
        if ($('#ganjeh_shipping_restrictions').length) return;

        // Build the meta box HTML
        var html = '<div id="ganjeh_shipping_restrictions" class="postbox">';
        html += '<div class="postbox-header"><h2 class="hndle"><?php echo esc_js(__('روش‌های ارسال مجاز', 'ganjeh')); ?></h2></div>';
        html += '<div class="inside">';
        html += '<p style="color:#666;font-size:12px;margin:0 0 10px;"><?php echo esc_js(__('تیک روش‌هایی که برای این محصول مجاز هستند را بزنید.', 'ganjeh')); ?></p>';
        <?php foreach ($methods as $key => $label) :
            $checked = in_array($key, $saved) ? ' checked' : '';
        ?>
        html += '<label style="display:block;margin:6px 0;cursor:pointer;"><input type="checkbox" name="_ganjeh_allowed_shipping[]" value="<?php echo esc_attr($key); ?>"<?php echo $checked; ?>> <?php echo esc_js($label); ?></label>';
        <?php endforeach; ?>
        html += '</div></div>';

        // Insert after product categories or at top of side
        var $target = $('#side-sortables, #postbox-container-1 .meta-box-sortables').first();
        if ($target.length) {
            $target.prepend(html);
        }
    });
    </script>
    <?php
}
add_action('admin_footer', 'ganjeh_shipping_restrictions_admin_fallback');

/**
 * Save per-product shipping restrictions
 */
function ganjeh_save_product_shipping_restrictions($post_id) {
    // Save from meta box (has nonce) or from WooCommerce product save (has _ganjeh_allowed_shipping)
    if (isset($_POST['ganjeh_shipping_restrictions_nonce'])) {
        if (!wp_verify_nonce($_POST['ganjeh_shipping_restrictions_nonce'], 'ganjeh_shipping_restrictions')) {
            return;
        }
    }

    if (isset($_POST['_ganjeh_allowed_shipping'])) {
        $allowed = array_map('sanitize_text_field', $_POST['_ganjeh_allowed_shipping']);
        update_post_meta($post_id, '_ganjeh_allowed_shipping', $allowed);
    } elseif (isset($_POST['ganjeh_shipping_restrictions_nonce'])) {
        // Only clear if nonce is present (meaning checkboxes were rendered but none checked)
        update_post_meta($post_id, '_ganjeh_allowed_shipping', []);
    }
}
add_action('woocommerce_process_product_meta', 'ganjeh_save_product_shipping_restrictions');
add_action('save_post_product', 'ganjeh_save_product_shipping_restrictions');

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
