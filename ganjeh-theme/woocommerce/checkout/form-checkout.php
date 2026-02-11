<?php
/**
 * Checkout Form Template - Minimal Design
 *
 * @package Ganjeh
 */

defined('ABSPATH') || exit;

if (!is_user_logged_in()) {
    wp_redirect(wc_get_page_permalink('myaccount'));
    exit;
}

do_action('woocommerce_before_checkout_form', $checkout);

// Get current user data
$current_user = wp_get_current_user();
$user_phone = get_user_meta($current_user->ID, 'billing_phone', true) ?: $current_user->user_login;
$user_name = trim($current_user->first_name . ' ' . $current_user->last_name) ?: $current_user->display_name;

// Get saved addresses
$saved_addresses = ganjeh_get_user_addresses($current_user->ID);
$states = WC()->countries->get_states('IR');
$states_json = json_encode($states);
?>

<div class="checkout-page">
    <!-- Header -->
    <header class="checkout-header">
        <a href="<?php echo wc_get_cart_url(); ?>" class="back-btn">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        <h1><?php _e('تکمیل خرید', 'ganjeh'); ?></h1>
        <div class="spacer"></div>
    </header>

    <!-- Step Indicator -->
    <div class="steps">
        <div class="step done">
            <div class="step-num">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <span><?php _e('سبد خرید', 'ganjeh'); ?></span>
        </div>
        <div class="step-line done"></div>
        <div class="step active">
            <div class="step-num">۲</div>
            <span><?php _e('پرداخت', 'ganjeh'); ?></span>
        </div>
    </div>

    <form name="checkout" method="post" class="checkout-form woocommerce-checkout" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data">

        <!-- Shipping Info with Address Management -->
        <div class="checkout-section" x-data="addressManager()">
            <div class="section-header">
                <h3><?php _e('آدرس تحویل', 'ganjeh'); ?></h3>
            </div>

            <!-- Selected Address Display (Compact) -->
            <div class="selected-address-display" x-show="selectedAddress && !showAddForm" @click="openModal()">
                <div class="selected-address-info">
                    <div class="selected-address-title" x-text="selectedAddress?.title"></div>
                    <div class="selected-address-text">
                        <span x-text="getStateName(selectedAddress?.state)"></span>،
                        <span x-text="selectedAddress?.city"></span> -
                        <span x-text="selectedAddress?.address"></span>
                    </div>
                </div>
                <button type="button" class="change-address-btn">
                    <?php _e('تغییر', 'ganjeh'); ?>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
            </div>

            <!-- No Address Message -->
            <div class="no-address" x-show="addresses.length === 0 && !showAddForm">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p><?php _e('آدرسی ثبت نشده', 'ganjeh'); ?></p>
                <button type="button" class="btn-add-first" @click="showAddForm = true">
                    <?php _e('افزودن آدرس', 'ganjeh'); ?>
                </button>
            </div>

            <!-- Add New Address Form (Inline) -->
            <div class="add-address-form" x-show="showAddForm" x-collapse>
                <div class="form-header">
                    <h4><?php _e('آدرس جدید', 'ganjeh'); ?></h4>
                    <button type="button" class="close-form-btn" @click="showAddForm = false; resetForm();" x-show="addresses.length > 0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="form-fields">
                    <div class="form-field">
                        <label><?php _e('عنوان آدرس', 'ganjeh'); ?></label>
                        <input type="text" x-model="newAddress.title" class="form-input" placeholder="<?php _e('مثلاً: خانه، محل کار', 'ganjeh'); ?>">
                    </div>
                    <div class="form-field">
                        <label><?php _e('استان', 'ganjeh'); ?> <span class="required">*</span></label>
                        <select x-model="newAddress.state" class="form-input">
                            <option value=""><?php _e('انتخاب استان', 'ganjeh'); ?></option>
                            <?php foreach ($states as $key => $state) : ?>
                                <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($state); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-field">
                        <label><?php _e('شهر', 'ganjeh'); ?> <span class="required">*</span></label>
                        <input type="text" x-model="newAddress.city" class="form-input">
                    </div>
                    <div class="form-field">
                        <label><?php _e('آدرس کامل', 'ganjeh'); ?> <span class="required">*</span></label>
                        <textarea x-model="newAddress.address" class="form-input" rows="2" placeholder="<?php _e('خیابان، کوچه، پلاک، واحد', 'ganjeh'); ?>"></textarea>
                    </div>
                    <div class="form-field">
                        <label><?php _e('کد پستی', 'ganjeh'); ?> <span class="required">*</span></label>
                        <input type="text" x-model="newAddress.postcode" class="form-input" maxlength="10" dir="ltr" inputmode="numeric" placeholder="<?php _e('۱۰ رقم', 'ganjeh'); ?>">
                    </div>
                </div>

                <button type="button" class="save-address-btn" @click="saveAddress()" :disabled="saving">
                    <span x-show="!saving"><?php _e('ذخیره آدرس', 'ganjeh'); ?></span>
                    <span x-show="saving" class="loading-spinner"></span>
                </button>
                <p class="form-message" x-show="message" :class="{ 'success': success, 'error': !success }" x-text="message"></p>
            </div>

            <!-- Receiver Info -->
            <div class="receiver-info">
                <h4><?php _e('اطلاعات گیرنده', 'ganjeh'); ?></h4>
                <div class="form-fields">
                    <div class="form-field">
                        <label for="billing_full_name"><?php _e('نام و نام خانوادگی', 'ganjeh'); ?> <span class="required">*</span></label>
                        <input type="text" name="billing_full_name" id="billing_full_name" class="form-input" value="<?php echo esc_attr($user_name); ?>" required>
                    </div>
                    <div class="form-field">
                        <label for="billing_phone"><?php _e('موبایل', 'ganjeh'); ?></label>
                        <input type="tel" name="billing_phone" id="billing_phone" class="form-input" value="<?php echo esc_attr($user_phone); ?>" dir="ltr" readonly style="background:#f3f4f6;">
                    </div>
                </div>
            </div>

            <!-- Hidden fields for WooCommerce - Billing -->
            <input type="hidden" name="billing_country" value="IR">
            <input type="hidden" name="billing_email" value="<?php echo esc_attr($current_user->user_email ?: $user_phone . '@ganjeh.local'); ?>">
            <input type="hidden" name="billing_first_name" value="">
            <input type="hidden" name="billing_last_name" value="">
            <input type="hidden" name="billing_state" id="billing_state" x-effect="$el.value = selectedAddress ? selectedAddress.state : newAddress.state" value="<?php echo !empty($saved_addresses) ? esc_attr($saved_addresses[0]['state']) : ''; ?>">
            <input type="hidden" name="billing_city" id="billing_city" x-effect="$el.value = selectedAddress ? selectedAddress.city : newAddress.city" value="<?php echo !empty($saved_addresses) ? esc_attr($saved_addresses[0]['city']) : ''; ?>">
            <input type="hidden" name="billing_address_1" id="billing_address_1" x-effect="$el.value = selectedAddress ? selectedAddress.address : newAddress.address" value="<?php echo !empty($saved_addresses) ? esc_attr($saved_addresses[0]['address']) : ''; ?>">
            <input type="hidden" name="billing_postcode" id="billing_postcode" x-effect="$el.value = selectedAddress ? selectedAddress.postcode : newAddress.postcode" value="<?php echo !empty($saved_addresses) ? esc_attr($saved_addresses[0]['postcode']) : ''; ?>">

            <!-- Hidden fields for WooCommerce - Shipping (mirror billing) -->
            <input type="hidden" name="shipping_country" value="IR">
            <input type="hidden" name="shipping_first_name" value="">
            <input type="hidden" name="shipping_last_name" value="">
            <input type="hidden" name="shipping_state" id="shipping_state" x-effect="$el.value = selectedAddress ? selectedAddress.state : newAddress.state" value="<?php echo !empty($saved_addresses) ? esc_attr($saved_addresses[0]['state']) : ''; ?>">
            <input type="hidden" name="shipping_city" id="shipping_city" x-effect="$el.value = selectedAddress ? selectedAddress.city : newAddress.city" value="<?php echo !empty($saved_addresses) ? esc_attr($saved_addresses[0]['city']) : ''; ?>">
            <input type="hidden" name="shipping_address_1" id="shipping_address_1" x-effect="$el.value = selectedAddress ? selectedAddress.address : newAddress.address" value="<?php echo !empty($saved_addresses) ? esc_attr($saved_addresses[0]['address']) : ''; ?>">
            <input type="hidden" name="shipping_postcode" id="shipping_postcode" x-effect="$el.value = selectedAddress ? selectedAddress.postcode : newAddress.postcode" value="<?php echo !empty($saved_addresses) ? esc_attr($saved_addresses[0]['postcode']) : ''; ?>">

            <!-- Address Selection Modal -->
            <div class="address-modal-overlay" x-show="showModal" x-transition.opacity @click="closeModal()"></div>
            <div class="address-modal" x-show="showModal" x-transition:enter="slide-up" x-transition:leave="slide-down">
                <div class="modal-header">
                    <h4><?php _e('انتخاب آدرس', 'ganjeh'); ?></h4>
                    <button type="button" class="modal-close-btn" @click="closeModal()">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <template x-for="addr in addresses" :key="addr.id">
                        <div class="modal-address-item" :class="{ 'selected': selectedAddress?.id === addr.id }" @click="selectAndClose(addr)">
                            <div class="modal-address-radio">
                                <div class="radio-circle" :class="{ 'checked': selectedAddress?.id === addr.id }"></div>
                            </div>
                            <div class="modal-address-content">
                                <div class="modal-address-title" x-text="addr.title"></div>
                                <div class="modal-address-detail">
                                    <span x-text="getStateName(addr.state)"></span>، <span x-text="addr.city"></span>
                                </div>
                                <div class="modal-address-text" x-text="addr.address"></div>
                            </div>
                            <button type="button" class="modal-delete-btn" @click.stop="deleteAddress(addr.id)">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>
                <div class="modal-footer">
                    <button type="button" class="modal-add-btn" @click="closeModal(); showAddForm = true;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <?php _e('افزودن آدرس جدید', 'ganjeh'); ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- Shipping Methods -->
        <?php
        $cart_subtotal = WC()->cart->get_subtotal();
        $free_shipping_threshold = 5000000;
        $is_free_eligible = ($cart_subtotal >= $free_shipping_threshold);
        $post_cost = $is_free_eligible ? 0 : 90000;
        $express_cost = 200000; // always paid
        $collection_cost = $is_free_eligible ? 0 : 90000;

        // اطلاعات تکمیلی روش‌های ارسال — قابل ویرایش از پیشخوان > تنظیمات سایت > نکات ارسال
        $shipping_tooltips = function_exists('ganjeh_get_shipping_tooltips') ? ganjeh_get_shipping_tooltips() : [];
        ?>
        <div class="checkout-section" x-data="shippingManager()" x-init="init()">
            <h3><?php _e('روش ارسال', 'ganjeh'); ?></h3>
            <div class="shipping-methods">
                <label class="shipping-method" id="shipping-post" x-show="!isTehran" x-transition onclick="selectShipping('post', <?php echo $post_cost; ?>)">
                    <input type="radio" name="ganjeh_shipping_method" value="post" class="shipping-method-input">
                    <span class="method-radio"></span>
                    <span class="method-info">
                        <span class="method-label"><?php _e('ارسال از طریق پست', 'ganjeh'); ?></span>
                        <span class="method-desc"><?php _e('ارسال به سراسر کشور · حداکثر ۷ روز کاری', 'ganjeh'); ?></span>
                    </span>
                    <span class="method-cost"><?php echo $post_cost > 0 ? wc_price($post_cost) : __('رایگان', 'ganjeh'); ?></span>
                    <span class="method-tooltip" onclick="toggleTooltip(this, event)">
                        <svg class="tooltip-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd"/></svg>
                        <span class="tooltip-popup"><?php echo esc_html($shipping_tooltips['post']); ?></span>
                    </span>
                </label>

                <label class="shipping-method" id="shipping-express" x-show="isTehran" x-transition onclick="selectShipping('express', <?php echo $express_cost; ?>)">
                    <input type="radio" name="ganjeh_shipping_method" value="express" class="shipping-method-input">
                    <span class="method-radio"></span>
                    <span class="method-info">
                        <span class="method-label"><?php _e('پیک فوری در تهران', 'ganjeh'); ?></span>
                        <span class="method-desc"><?php _e('تحویل چند ساعته · مناطق ۲۲ گانه تهران', 'ganjeh'); ?></span>
                    </span>
                    <span class="method-cost"><?php echo wc_price($express_cost); ?></span>
                    <span class="method-tooltip" onclick="toggleTooltip(this, event)">
                        <svg class="tooltip-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd"/></svg>
                        <span class="tooltip-popup"><?php echo esc_html($shipping_tooltips['express']); ?></span>
                    </span>
                </label>

                <label class="shipping-method" id="shipping-collection" x-show="isTehran" x-transition onclick="selectShipping('collection', <?php echo $collection_cost; ?>)">
                    <input type="radio" name="ganjeh_shipping_method" value="collection" class="shipping-method-input">
                    <span class="method-radio"></span>
                    <span class="method-info">
                        <span class="method-label"><?php _e('ارسال عادی', 'ganjeh'); ?></span>
                        <span class="method-desc"><?php _e('حداکثر ۵ روز کاری · مناطق ۲۲ گانه تهران', 'ganjeh'); ?></span>
                    </span>
                    <span class="method-cost"><?php echo $collection_cost > 0 ? wc_price($collection_cost) : __('رایگان', 'ganjeh'); ?></span>
                    <span class="method-tooltip" onclick="toggleTooltip(this, event)">
                        <svg class="tooltip-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd"/></svg>
                        <span class="tooltip-popup"><?php echo esc_html($shipping_tooltips['collection']); ?></span>
                    </span>
                </label>

                <label class="shipping-method" id="shipping-pickup" x-show="isTehran" x-transition onclick="selectShipping('pickup', 0)">
                    <input type="radio" name="ganjeh_shipping_method" value="pickup" class="shipping-method-input">
                    <span class="method-radio"></span>
                    <span class="method-info">
                        <span class="method-label"><?php _e('تحویل حضوری', 'ganjeh'); ?></span>
                        <span class="method-desc"><?php _e('حداقل ۲۴ ساعت بعد · مناطق ۲۲ گانه تهران', 'ganjeh'); ?></span>
                    </span>
                    <span class="method-cost"><?php _e('رایگان', 'ganjeh'); ?></span>
                    <span class="method-tooltip" onclick="toggleTooltip(this, event)">
                        <svg class="tooltip-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd"/></svg>
                        <span class="tooltip-popup"><?php echo esc_html($shipping_tooltips['pickup']); ?></span>
                    </span>
                </label>
            </div>
        </div>

        <!-- Payment Methods -->
        <div class="checkout-section">
            <h3><?php _e('روش پرداخت', 'ganjeh'); ?></h3>
            <div id="payment" class="payment-methods">
                <?php
                $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
                if (!empty($available_gateways)) :
                    $first_gateway = true;
                    foreach ($available_gateways as $gateway) :
                        // Auto-select first gateway if none chosen
                        $is_selected = $gateway->chosen || $first_gateway;
                ?>
                    <label class="payment-method <?php echo $is_selected ? 'selected' : ''; ?>">
                        <input type="radio" name="payment_method" value="<?php echo esc_attr($gateway->id); ?>" <?php if ($is_selected) echo 'checked="checked"'; ?> class="payment-method-input">
                        <span class="method-radio"></span>
                        <span class="method-info">
                            <span class="method-label"><?php echo $gateway->get_title(); ?></span>
                            <?php if ($gateway->get_description()) : ?>
                                <span class="method-desc"><?php echo wp_kses_post($gateway->get_description()); ?></span>
                            <?php endif; ?>
                        </span>
                        <?php if ($gateway->get_icon()) : ?>
                            <span class="method-icon"><?php echo $gateway->get_icon(); ?></span>
                        <?php endif; ?>
                    </label>
                <?php
                        $first_gateway = false;
                    endforeach;
                else :
                ?>
                    <p class="no-payment"><?php _e('هیچ درگاه پرداختی فعال نیست', 'ganjeh'); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Coupon Code -->
        <div class="checkout-section coupon-section" x-data="couponHandler()">
            <div class="coupon-toggle" @click="open = !open">
                <div class="coupon-icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </div>
                <span><?php _e('کد تخفیف دارید؟', 'ganjeh'); ?></span>
                <svg class="chevron w-5 h-5" :class="{ 'rotate': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
            <div class="coupon-form" x-show="open" x-collapse>
                <div class="coupon-input-wrap">
                    <input type="text" x-model="couponCode" class="coupon-input" placeholder="<?php _e('کد تخفیف را وارد کنید', 'ganjeh'); ?>" dir="ltr">
                    <button type="button" class="apply-coupon-btn" :disabled="loading" @click="applyCoupon()">
                        <span x-show="!loading"><?php _e('اعمال', 'ganjeh'); ?></span>
                        <span x-show="loading" class="loading-spinner"></span>
                    </button>
                </div>
                <p class="coupon-message" x-show="message" :class="{ 'success': success, 'error': !success }" x-text="message"></p>
                <?php
                // Show applied coupons
                $applied_coupons = WC()->cart->get_applied_coupons();
                if (!empty($applied_coupons)) :
                ?>
                <div class="applied-coupons">
                    <?php foreach ($applied_coupons as $coupon_code) : ?>
                    <div class="applied-coupon">
                        <span class="coupon-tag">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <?php echo esc_html($coupon_code); ?>
                        </span>
                        <button type="button" class="remove-coupon" @click="removeCoupon('<?php echo esc_attr($coupon_code); ?>')">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Order Notes -->
        <div class="checkout-section order-notes-section">
            <div class="order-notes-toggle" onclick="toggleOrderNotes()">
                <div class="order-notes-icon">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <span><?php _e('توضیحات سفارش (اختیاری)', 'ganjeh'); ?></span>
                <svg class="order-notes-chevron" id="order-notes-chevron" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
            <div class="order-notes-body" id="order-notes-body">
                <textarea name="order_comments" id="order_comments" class="order-notes-textarea" rows="3" placeholder="<?php _e('اگر توضیح خاصی درباره سفارش دارید اینجا بنویسید...', 'ganjeh'); ?>"></textarea>
            </div>
        </div>

        <!-- Totals -->
        <div class="checkout-totals">
            <div class="total-row">
                <span><?php _e('جمع سفارش', 'ganjeh'); ?></span>
                <span><?php echo wc_price(WC()->cart->get_subtotal()); ?></span>
            </div>
            <?php if (WC()->cart->get_discount_total() > 0) : ?>
            <div class="total-row discount">
                <span><?php _e('تخفیف', 'ganjeh'); ?></span>
                <span>- <?php echo wc_price(WC()->cart->get_discount_total()); ?></span>
            </div>
            <?php endif; ?>
            <div class="total-row shipping">
                <span><?php _e('هزینه ارسال', 'ganjeh'); ?></span>
                <span id="shipping-cost-display"><?php echo wc_price(90000); ?></span>
            </div>
            <div class="total-row final">
                <span><?php _e('قابل پرداخت', 'ganjeh'); ?></span>
                <span><?php echo WC()->cart->get_total(); ?></span>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="checkout-bar">
            <div class="bar-total">
                <span class="label"><?php _e('مبلغ نهایی', 'ganjeh'); ?></span>
                <span class="value"><?php echo WC()->cart->get_total(); ?></span>
            </div>
            <?php wp_nonce_field('woocommerce-process_checkout', 'woocommerce-process-checkout-nonce'); ?>
            <button type="button" class="pay-btn" id="place_order" onclick="handlePaymentClick()">
                <?php _e('پرداخت', 'ganjeh'); ?>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <button type="submit" name="woocommerce_checkout_place_order" id="place_order_submit" style="display:none;"></button>
        </div>

    </form>

    <!-- Cross-sell Popup Modal -->
    <div x-data="crossSellPopup()" x-cloak>
        <!-- Overlay -->
        <div class="crosssell-overlay" x-show="showPopup" x-transition.opacity @click="proceedToPayment()"></div>

        <!-- Modal -->
        <div class="crosssell-modal" x-show="showPopup" x-transition:enter="slide-up" x-transition:leave="slide-down">
            <div class="crosssell-header">
                <div class="crosssell-title">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>
                    </svg>
                    <h4><?php _e('چیزی یادت نرفته؟', 'ganjeh'); ?></h4>
                </div>
                <button type="button" class="crosssell-close" @click="proceedToPayment()">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="crosssell-body">
                <!-- Loading State -->
                <div class="crosssell-loading" x-show="loading">
                    <div class="loading-spinner-lg"></div>
                    <span><?php _e('در حال بارگذاری...', 'ganjeh'); ?></span>
                </div>

                <!-- Products Grid -->
                <div class="crosssell-products" x-show="!loading && products.length > 0">
                    <template x-for="product in products" :key="product.id">
                        <div class="crosssell-product" :class="{ 'added': addedProducts.includes(product.id) }">
                            <div class="product-image">
                                <img :src="product.image" :alt="product.name">
                                <div class="product-badge" x-show="product.discount > 0">
                                    <span x-text="product.discount + '%'"></span>
                                </div>
                            </div>
                            <div class="product-info">
                                <h5 class="product-name" x-text="product.name"></h5>
                                <div class="product-price">
                                    <span class="sale-price" x-html="product.price"></span>
                                </div>
                            </div>
                            <button type="button" class="add-to-cart-btn"
                                    @click="addToCart(product)"
                                    :disabled="addingProduct === product.id"
                                    :class="{ 'added': addedProducts.includes(product.id) }">
                                <span x-show="addingProduct !== product.id && !addedProducts.includes(product.id)">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    <?php _e('افزودن', 'ganjeh'); ?>
                                </span>
                                <span x-show="addingProduct === product.id" class="loading-spinner-sm"></span>
                                <span x-show="addedProducts.includes(product.id)">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <?php _e('اضافه شد', 'ganjeh'); ?>
                                </span>
                            </button>
                        </div>
                    </template>
                </div>

                <!-- No Products -->
                <div class="crosssell-empty" x-show="!loading && products.length === 0">
                    <p><?php _e('محصول پیشنهادی وجود ندارد', 'ganjeh'); ?></p>
                </div>
            </div>

            <div class="crosssell-footer">
                <button type="button" class="skip-btn" @click="proceedToPayment()">
                    <?php _e('نه ممنون، ادامه پرداخت', 'ganjeh'); ?>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <button type="button" class="continue-btn" x-show="addedProducts.length > 0" @click="proceedToPayment()">
                    <?php _e('ادامه با', 'ganjeh'); ?>
                    <span x-text="addedProducts.length"></span>
                    <?php _e('محصول جدید', 'ganjeh'); ?>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.checkout-page { min-height: 100vh; background: #f9fafb; padding-bottom: 100px; }
.checkout-header { position: sticky; top: 0; z-index: 40; background: white; padding: 12px 16px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #f3f4f6; }
.checkout-header h1 { font-size: 16px; font-weight: 700; color: #1f2937; margin: 0; }
.back-btn, .spacer { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; color: #374151; text-decoration: none; }

/* Steps */
.steps { display: flex; align-items: center; justify-content: center; padding: 20px 16px; background: white; border-bottom: 1px solid #f3f4f6; }
.step { display: flex; flex-direction: column; align-items: center; gap: 6px; }
.step-num { width: 32px; height: 32px; border-radius: 50%; background: #e5e7eb; color: #9ca3af; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 700; }
.step.active .step-num, .step.done .step-num { background: #4CB050; color: white; }
.step span { font-size: 12px; color: #6b7280; font-weight: 500; }
.step.active span, .step.done span { color: #4CB050; font-weight: 600; }
.step-line { width: 60px; height: 2px; background: #e5e7eb; margin: 0 12px; margin-bottom: 22px; }
.step-line.done { background: #4CB050; }

/* Sections */
.checkout-section { margin: 16px; padding: 16px; background: white; border-radius: 16px; }
.checkout-section h3 { font-size: 14px; font-weight: 700; color: #1f2937; margin: 0 0 16px; }

/* Form Fields */
.form-fields { display: flex; flex-direction: column; gap: 14px; }
.form-field { display: flex; flex-direction: column; gap: 6px; }
.form-field label { font-size: 13px; font-weight: 500; color: #374151; }
.form-field label .required { color: #ef4444; }
.form-input { width: 100%; padding: 12px 14px; border: 1px solid #e5e7eb; border-radius: 10px; font-size: 14px; color: #1f2937; background: white; transition: all 0.2s; }
.form-input:focus { outline: none; border-color: #4CB050; }
select.form-input { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: left 12px center; background-size: 16px; padding-left: 36px; }
textarea.form-input { resize: none; }

/* Shipping Methods */
.shipping-methods { display: flex; flex-direction: column; gap: 10px; }
.shipping-method { display: flex; align-items: center; gap: 12px; padding: 14px; background: #f9fafb; border: 2px solid transparent; border-radius: 12px; cursor: pointer; transition: all 0.2s; }
.shipping-method.selected { border-color: #4CB050; background: #f0fdf4; }
.shipping-method-input { display: none; }
.method-radio { width: 20px; height: 20px; border: 2px solid #d1d5db; border-radius: 50%; position: relative; flex-shrink: 0; }
.shipping-method.selected .method-radio, .payment-method.selected .method-radio { border-color: #4CB050; }
.shipping-method.selected .method-radio::after, .payment-method.selected .method-radio::after { content: ''; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 10px; height: 10px; background: #4CB050; border-radius: 50%; }
.method-info { flex: 1; display: flex; flex-direction: column; gap: 2px; }
.method-label { font-size: 14px; font-weight: 600; color: #1f2937; }
.method-cost { font-size: 13px; color: #4CB050; font-weight: 500; }
.method-desc { font-size: 12px; color: #6b7280; }
.no-shipping { padding: 16px; background: #fffbeb; color: #92400e; border-radius: 10px; text-align: center; font-size: 13px; margin: 0; }

/* Shipping Tooltip */
.method-tooltip { position: relative; display: inline-flex; align-items: center; flex-shrink: 0; z-index: 10; }
.method-tooltip .tooltip-icon { width: 20px; height: 20px; color: #9ca3af; cursor: pointer; transition: color 0.2s; }
.method-tooltip:hover .tooltip-icon { color: #4CB050; }
.method-tooltip .tooltip-popup { display: none; position: absolute; top: calc(100% + 10px); left: 0; width: 260px; padding: 12px 14px; background: #1f2937; color: #fff; font-size: 12px; line-height: 1.8; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.2); text-align: right; z-index: 50; white-space: normal; word-wrap: break-word; }
.method-tooltip .tooltip-popup::after { content: ''; position: absolute; bottom: 100%; left: 6px; border: 6px solid transparent; border-bottom-color: #1f2937; }
.method-tooltip:hover .tooltip-popup,
.method-tooltip.active .tooltip-popup { display: block; }
.shipping-method { position: relative; overflow: visible; }
.shipping-methods { overflow: visible; }
@media (max-width: 640px) {
    .method-tooltip .tooltip-popup { width: 220px; }
    .method-tooltip .tooltip-popup::after { left: 8px; }
}

/* Payment Methods */
.payment-methods { display: flex; flex-direction: column; gap: 10px; }
.payment-method { display: flex; align-items: center; gap: 12px; padding: 14px; background: #f9fafb; border: 2px solid transparent; border-radius: 12px; cursor: pointer; transition: all 0.2s; }
.payment-method.selected { border-color: #4CB050; background: #f0fdf4; }
.payment-method-input { display: none; }
.method-icon { flex-shrink: 0; }
.method-icon img { max-height: 24px; width: auto; }
.no-payment { padding: 16px; background: #fef2f2; color: #991b1b; border-radius: 10px; text-align: center; font-size: 13px; margin: 0; }

/* Totals */
.checkout-totals { margin: 16px; padding: 16px; background: white; border-radius: 16px; }
.total-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; font-size: 14px; color: #4b5563; }
.total-row.discount { color: #4CB050; }
.total-row.shipping { color: #6b7280; }
.total-row.shipping.free span:last-child { color: #4CB050; font-weight: 500; }
.total-row.final { border-top: 1px solid #e5e7eb; margin-top: 8px; padding-top: 12px; font-size: 16px; font-weight: 700; color: #1f2937; }

/* Bottom Bar */
.checkout-bar { position: fixed; bottom: 0; left: 50%; transform: translateX(-50%); width: 100%; max-width: 515px; background: white; border-top: 1px solid #e5e7eb; padding: 16px; display: flex; align-items: center; justify-content: space-between; gap: 16px; box-shadow: 0 -4px 20px rgba(0,0,0,0.08); z-index: 50; }
.bar-total { display: flex; flex-direction: column; gap: 2px; }
.bar-total .label { font-size: 12px; color: #6b7280; }
.bar-total .value { font-size: 18px; font-weight: 700; color: #1f2937; }
.pay-btn { display: flex; align-items: center; gap: 8px; padding: 14px 32px; background: linear-gradient(135deg, #4CB050, #3d9142); color: white; border: none; border-radius: 12px; font-size: 15px; font-weight: 600; cursor: pointer; }

/* Coupon Section */
.coupon-section { padding: 0 !important; overflow: hidden; }
.coupon-toggle { display: flex; align-items: center; gap: 10px; padding: 16px; cursor: pointer; }
.coupon-icon { width: 36px; height: 36px; background: #f0fdf4; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #4CB050; }
.coupon-toggle span { flex: 1; font-size: 14px; font-weight: 600; color: #1f2937; }
.coupon-toggle .chevron { color: #9ca3af; transition: transform 0.2s; }
.coupon-toggle .chevron.rotate { transform: rotate(180deg); }
.coupon-form { padding: 0 16px 16px; border-top: 1px solid #f3f4f6; }
.coupon-input-wrap { display: flex; gap: 10px; margin-top: 12px; }
.coupon-input { flex: 1; padding: 12px 14px; border: 1px solid #e5e7eb; border-radius: 10px; font-size: 14px; text-transform: uppercase; }
.coupon-input:focus { outline: none; border-color: #4CB050; }
.apply-coupon-btn { padding: 12px 20px; background: #4CB050; color: white; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; min-width: 70px; display: flex; align-items: center; justify-content: center; }
.apply-coupon-btn:disabled { opacity: 0.7; cursor: not-allowed; }
.loading-spinner { width: 18px; height: 18px; border: 2px solid rgba(255,255,255,0.3); border-top-color: white; border-radius: 50%; animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.coupon-message { margin: 10px 0 0; padding: 10px 12px; border-radius: 8px; font-size: 13px; }
.coupon-message.success { background: #f0fdf4; color: #166534; }
.coupon-message.error { background: #fef2f2; color: #991b1b; }
.applied-coupons { margin-top: 12px; display: flex; flex-wrap: wrap; gap: 8px; }
.applied-coupon { display: flex; align-items: center; gap: 6px; padding: 6px 10px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; }
.coupon-tag { display: flex; align-items: center; gap: 4px; font-size: 13px; font-weight: 600; color: #166534; text-transform: uppercase; }
.coupon-tag svg { color: #4CB050; }
.remove-coupon { background: none; border: none; padding: 2px; cursor: pointer; color: #991b1b; display: flex; }

/* Order Notes */
.order-notes-section { padding: 0 !important; overflow: hidden; }
.order-notes-toggle { display: flex; align-items: center; gap: 10px; padding: 14px 16px; cursor: pointer; transition: background 0.2s; }
.order-notes-toggle:hover { background: #f9fafb; }
.order-notes-icon { color: #6b7280; display: flex; }
.order-notes-toggle > span { flex: 1; font-size: 14px; font-weight: 500; color: #4b5563; }
.order-notes-chevron { color: #9ca3af; transition: transform 0.3s; flex-shrink: 0; }
.order-notes-chevron.open { transform: rotate(180deg); }
.order-notes-body { display: none; padding: 0 16px 16px; }
.order-notes-body.open { display: block; }
.order-notes-textarea { width: 100%; padding: 12px; border: 1px solid #e5e7eb; border-radius: 10px; font-size: 14px; line-height: 1.7; resize: vertical; min-height: 80px; font-family: inherit; direction: rtl; transition: border-color 0.2s; }
.order-notes-textarea:focus { outline: none; border-color: #4CB050; box-shadow: 0 0 0 2px rgba(76,176,80,0.12); }

/* Hide WooCommerce defaults */
.woocommerce-form-coupon-toggle, .woocommerce-form-login-toggle { display: none; }

/* Address Section */
.section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
.section-header h3 { margin: 0; }

/* Selected Address Display */
.selected-address-display { display: flex; align-items: center; gap: 12px; padding: 14px; background: #f0fdf4; border: 2px solid #4CB050; border-radius: 12px; cursor: pointer; }
.selected-address-info { flex: 1; min-width: 0; }
.selected-address-title { font-size: 14px; font-weight: 700; color: #1f2937; margin-bottom: 4px; }
.selected-address-text { font-size: 13px; color: #4b5563; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.change-address-btn { display: flex; align-items: center; gap: 4px; padding: 8px 12px; background: white; color: #4CB050; border: 1px solid #4CB050; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; white-space: nowrap; }

/* No Address */
.no-address { text-align: center; padding: 24px 16px; color: #6b7280; }
.no-address svg { margin: 0 auto 10px; color: #d1d5db; }
.no-address p { margin: 0 0 12px; font-size: 13px; }
.btn-add-first { padding: 10px 20px; background: #4CB050; color: white; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; }

/* Address Modal */
.address-modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 100; }
.address-modal { position: fixed; bottom: 0; left: 50%; transform: translateX(-50%); width: 100%; max-width: 515px; max-height: 80vh; background: white; border-radius: 20px 20px 0 0; z-index: 101; display: flex; flex-direction: column; }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 16px 20px; border-bottom: 1px solid #f3f4f6; }
.modal-header h4 { margin: 0; font-size: 16px; font-weight: 700; color: #1f2937; }
.modal-close-btn { padding: 6px; background: #f3f4f6; border: none; border-radius: 8px; color: #6b7280; cursor: pointer; display: flex; }
.modal-body { flex: 1; overflow-y: auto; padding: 12px 16px; }
.modal-address-item { display: flex; gap: 12px; padding: 14px; background: #f9fafb; border: 2px solid transparent; border-radius: 12px; cursor: pointer; margin-bottom: 10px; position: relative; }
.modal-address-item.selected { border-color: #4CB050; background: #f0fdf4; }
.modal-address-radio { padding-top: 2px; }
.radio-circle { width: 20px; height: 20px; border: 2px solid #d1d5db; border-radius: 50%; position: relative; }
.radio-circle.checked { border-color: #4CB050; }
.radio-circle.checked::after { content: ''; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 10px; height: 10px; background: #4CB050; border-radius: 50%; }
.modal-address-content { flex: 1; min-width: 0; }
.modal-address-title { font-size: 14px; font-weight: 700; color: #1f2937; margin-bottom: 2px; }
.modal-address-detail { font-size: 12px; color: #4CB050; font-weight: 500; margin-bottom: 2px; }
.modal-address-text { font-size: 12px; color: #6b7280; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.modal-delete-btn { padding: 6px; background: white; border: 1px solid #fecaca; border-radius: 6px; color: #991b1b; cursor: pointer; }
.modal-footer { padding: 12px 16px 20px; border-top: 1px solid #f3f4f6; }
.modal-add-btn { width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; padding: 14px; background: #f0fdf4; color: #4CB050; border: 2px dashed #4CB050; border-radius: 12px; font-size: 14px; font-weight: 600; cursor: pointer; }
[x-cloak] { display: none !important; }

/* Add Address Form */
.add-address-form { padding-top: 16px; border-top: 1px solid #f3f4f6; }
.form-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
.form-header h4 { margin: 0; font-size: 14px; font-weight: 600; color: #1f2937; }
.close-form-btn { padding: 6px; background: #f3f4f6; border: none; border-radius: 8px; color: #6b7280; cursor: pointer; display: flex; }
.save-address-btn { width: 100%; margin-top: 16px; padding: 14px; background: #4CB050; color: white; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; }
.save-address-btn:disabled { opacity: 0.7; cursor: not-allowed; }
.form-message { margin-top: 12px; padding: 10px 12px; border-radius: 8px; font-size: 13px; text-align: center; }
.form-message.success { background: #f0fdf4; color: #166534; }
.form-message.error { background: #fef2f2; color: #991b1b; }

/* Receiver Info */
.receiver-info { margin-top: 20px; padding-top: 20px; border-top: 1px solid #f3f4f6; }
.receiver-info h4 { margin: 0 0 16px; font-size: 14px; font-weight: 600; color: #1f2937; }

/* Cross-sell Popup Styles */
.crosssell-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 200; backdrop-filter: blur(2px); }
.crosssell-modal { position: fixed; bottom: 0; left: 50%; transform: translateX(-50%); width: 100%; max-width: 515px; max-height: 85vh; background: white; border-radius: 24px 24px 0 0; z-index: 201; display: flex; flex-direction: column; box-shadow: 0 -10px 40px rgba(0,0,0,0.2); }
.crosssell-header { display: flex; justify-content: space-between; align-items: center; padding: 20px; border-bottom: 1px solid #f3f4f6; }
.crosssell-title { display: flex; align-items: center; gap: 10px; }
.crosssell-title svg { color: #4CB050; }
.crosssell-title h4 { margin: 0; font-size: 15px; font-weight: 700; color: #1f2937; }
.crosssell-close { padding: 8px; background: #f3f4f6; border: none; border-radius: 10px; color: #6b7280; cursor: pointer; display: flex; transition: all 0.2s; }
.crosssell-close:hover { background: #e5e7eb; }
.crosssell-body { flex: 1; overflow-y: auto; padding: 16px; min-height: 200px; }
.crosssell-loading { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; padding: 40px 20px; color: #6b7280; font-size: 14px; }
.loading-spinner-lg { width: 32px; height: 32px; border: 3px solid #e5e7eb; border-top-color: #4CB050; border-radius: 50%; animation: spin 0.8s linear infinite; }
.loading-spinner-sm { width: 16px; height: 16px; border: 2px solid rgba(255,255,255,0.3); border-top-color: white; border-radius: 50%; animation: spin 0.8s linear infinite; }
.crosssell-products { display: flex; flex-direction: column; gap: 12px; }
.crosssell-product { display: flex; align-items: center; gap: 12px; padding: 12px; background: #f9fafb; border: 2px solid transparent; border-radius: 14px; transition: all 0.2s; }
.crosssell-product.added { border-color: #4CB050; background: #f0fdf4; }
.product-image { position: relative; width: 70px; height: 70px; flex-shrink: 0; border-radius: 10px; overflow: hidden; background: white; }
.product-image img { width: 100%; height: 100%; object-fit: cover; }
.product-badge { position: absolute; top: 4px; right: 4px; background: #ef4444; color: white; font-size: 11px; font-weight: 700; padding: 2px 6px; border-radius: 6px; }
.product-info { flex: 1; min-width: 0; }
.product-name { margin: 0 0 6px; font-size: 13px; font-weight: 600; color: #1f2937; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.product-price { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.sale-price { font-size: 14px; font-weight: 700; color: #4CB050; }
.regular-price { font-size: 12px; color: #9ca3af; text-decoration: line-through; }
.add-to-cart-btn { display: flex; align-items: center; justify-content: center; gap: 4px; padding: 10px 14px; background: #4CB050; color: white; border: none; border-radius: 10px; font-size: 13px; font-weight: 600; cursor: pointer; white-space: nowrap; transition: all 0.2s; min-width: 90px; }
.add-to-cart-btn:hover { background: #3d9142; }
.add-to-cart-btn:disabled { opacity: 0.8; cursor: not-allowed; }
.add-to-cart-btn.added { background: #166534; }
.crosssell-empty { text-align: center; padding: 40px 20px; color: #6b7280; }
.crosssell-footer { display: flex; flex-direction: column; gap: 10px; padding: 16px 20px 24px; border-top: 1px solid #f3f4f6; background: white; }
.skip-btn { display: flex; align-items: center; justify-content: center; gap: 6px; padding: 14px 20px; background: #f3f4f6; color: #4b5563; border: none; border-radius: 12px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
.skip-btn:hover { background: #e5e7eb; }
.continue-btn { display: flex; align-items: center; justify-content: center; gap: 6px; padding: 14px 20px; background: linear-gradient(135deg, #4CB050, #3d9142); color: white; border: none; border-radius: 12px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
.continue-btn:hover { opacity: 0.95; }

/* Slide animations */
.slide-up { animation: slideUp 0.3s ease-out; }
.slide-down { animation: slideDown 0.3s ease-out; }
@keyframes slideUp { from { transform: translate(-50%, 100%); } to { transform: translate(-50%, 0); } }
@keyframes slideDown { from { transform: translate(-50%, 0); } to { transform: translate(-50%, 100%); } }
</style>

<script>
// Select shipping method
// Tooltip toggle (for mobile tap)
// Order notes toggle
function toggleOrderNotes() {
    var body = document.getElementById('order-notes-body');
    var chevron = document.getElementById('order-notes-chevron');
    body.classList.toggle('open');
    chevron.classList.toggle('open');
}

function toggleTooltip(el, event) {
    event.stopPropagation();
    event.preventDefault();
    document.querySelectorAll('.method-tooltip.active').forEach(function(t) {
        if (t !== el) t.classList.remove('active');
    });
    el.classList.toggle('active');
}
document.addEventListener('click', function() {
    document.querySelectorAll('.method-tooltip.active').forEach(function(t) {
        t.classList.remove('active');
    });
});

function selectShipping(method, cost) {
    // Update UI
    document.querySelectorAll('.shipping-method').forEach(el => el.classList.remove('selected'));
    document.getElementById('shipping-' + method).classList.add('selected');

    // Check the radio
    document.querySelector('input[name="ganjeh_shipping_method"][value="' + method + '"]').checked = true;

    // Update shipping cost display immediately
    const shippingCostEl = document.getElementById('shipping-cost-display');
    if (shippingCostEl) {
        shippingCostEl.innerHTML = cost > 0 ? formatPrice(cost) : '<?php _e('رایگان', 'ganjeh'); ?>';
    }

    // Save to session via AJAX
    fetch(ganjeh.ajax_url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            action: 'ganjeh_set_shipping_method',
            method: method,
            cost: cost,
            nonce: ganjeh.nonce
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Update total display
            const totalEl = document.querySelector('.total-row.final span:last-child');
            const barTotalEl = document.querySelector('.bar-total .value');
            if (totalEl) totalEl.innerHTML = data.data.total;
            if (barTotalEl) barTotalEl.innerHTML = data.data.total;
            // Update shipping cost from server response
            if (shippingCostEl) shippingCostEl.innerHTML = data.data.shipping_cost;
        }
    });
}

// Format price in Persian
function formatPrice(amount) {
    return new Intl.NumberFormat('fa-IR').format(amount) + ' تومان';
}

// Set default shipping on page load
document.addEventListener('DOMContentLoaded', function() {
    // Check if user is in Tehran to pick correct default
    const state = document.getElementById('billing_state')?.value || '';
    const city = document.getElementById('billing_city')?.value || '';
    const isTehranInit = (state === 'THR') && (city.includes('تهران') || city.toLowerCase().includes('tehran'));

    if (isTehranInit) {
        selectShipping('collection', <?php echo $collection_cost; ?>);
    } else {
        selectShipping('post', <?php echo $post_cost; ?>);
    }
});

// Handle payment method selection
document.querySelectorAll('.payment-method-input').forEach(input => {
    input.addEventListener('change', function() {
        document.querySelectorAll('.payment-method').forEach(el => el.classList.remove('selected'));
        this.closest('.payment-method').classList.add('selected');
    });
});

// Cross-sell popup state
let crossSellShown = false;

// Validate form fields
function validateCheckoutForm() {
    const billingState = document.getElementById('billing_state').value;
    const billingCity = document.getElementById('billing_city').value;
    const billingAddress = document.getElementById('billing_address_1').value;

    if (!billingState || !billingCity || !billingAddress) {
        alert('<?php _e('لطفاً آدرس تحویل را انتخاب یا وارد کنید', 'ganjeh'); ?>');
        return false;
    }
    return true;
}

// Prepare form data before submit
function prepareFormData() {
    const fullName = document.getElementById('billing_full_name').value.trim();
    const nameParts = fullName.split(' ');
    document.querySelector('input[name="billing_first_name"]').value = nameParts[0] || '';
    document.querySelector('input[name="billing_last_name"]').value = nameParts.slice(1).join(' ') || '';
}

// Submit the checkout form
function submitCheckoutForm() {
    prepareFormData();
    document.getElementById('place_order_submit').click();
}

// Handle payment button click
function handlePaymentClick() {
    if (!validateCheckoutForm()) {
        return;
    }

    // If cross-sell already shown, submit directly
    if (crossSellShown) {
        submitCheckoutForm();
        return;
    }

    // Mark as shown and check for cross-sell products
    crossSellShown = true;
    window.dispatchEvent(new CustomEvent('show-crosssell-popup'));
}

// Address Manager Alpine component
function addressManager() {
    return {
        addresses: <?php echo json_encode($saved_addresses); ?>,
        states: <?php echo $states_json; ?>,
        selectedAddress: <?php echo !empty($saved_addresses) ? json_encode($saved_addresses[0]) : 'null'; ?>,
        showAddForm: false,
        showModal: false,
        saving: false,
        message: '',
        success: false,
        newAddress: {
            title: '',
            state: '',
            city: '',
            address: '',
            postcode: ''
        },

        init() {
            // Set hidden fields on init
            this.updateHiddenFields();
        },

        updateHiddenFields() {
            const addr = this.selectedAddress || this.newAddress;
            if (addr) {
                // Billing fields
                document.getElementById('billing_state').value = addr.state || '';
                document.getElementById('billing_city').value = addr.city || '';
                document.getElementById('billing_address_1').value = addr.address || '';
                document.getElementById('billing_postcode').value = addr.postcode || '';
                // Shipping fields (mirror billing)
                document.getElementById('shipping_state').value = addr.state || '';
                document.getElementById('shipping_city').value = addr.city || '';
                document.getElementById('shipping_address_1').value = addr.address || '';
                document.getElementById('shipping_postcode').value = addr.postcode || '';
                // Notify shipping manager about address change
                window.dispatchEvent(new CustomEvent('address-changed'));
            }
        },

        getStateName(stateCode) {
            return this.states[stateCode] || stateCode;
        },

        openModal() {
            this.showModal = true;
            document.body.style.overflow = 'hidden';
        },

        closeModal() {
            this.showModal = false;
            document.body.style.overflow = '';
        },

        selectAndClose(addr) {
            this.selectedAddress = addr;
            this.updateHiddenFields();
            this.closeModal();
        },

        selectAddress(addr) {
            this.selectedAddress = addr;
            this.updateHiddenFields();
            this.showAddForm = false;
        },

        resetForm() {
            this.newAddress = { title: '', state: '', city: '', address: '', postcode: '' };
            this.message = '';
        },

        saveAddress() {
            if (!this.newAddress.state || !this.newAddress.city || !this.newAddress.address || !this.newAddress.postcode) {
                this.message = '<?php _e('لطفاً همه فیلدها را پر کنید', 'ganjeh'); ?>';
                this.success = false;
                return;
            }

            this.saving = true;
            this.message = '';

            const formData = new FormData();
            formData.append('action', 'ganjeh_save_address');
            formData.append('title', this.newAddress.title || '<?php _e('آدرس جدید', 'ganjeh'); ?>');
            formData.append('state', this.newAddress.state);
            formData.append('city', this.newAddress.city);
            formData.append('address', this.newAddress.address);
            formData.append('postcode', this.newAddress.postcode);

            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(r => r.json())
            .then(data => {
                this.saving = false;
                if (data.success) {
                    this.addresses = data.data.addresses;
                    this.selectedAddress = data.data.address;
                    this.updateHiddenFields();
                    this.showAddForm = false;
                    this.resetForm();
                    this.message = data.data.message;
                    this.success = true;
                } else {
                    this.message = data.data?.message || '<?php _e('خطا در ذخیره آدرس', 'ganjeh'); ?>';
                    this.success = false;
                }
            })
            .catch(() => {
                this.saving = false;
                this.message = '<?php _e('خطا در ذخیره آدرس', 'ganjeh'); ?>';
                this.success = false;
            });
        },

        deleteAddress(addressId) {
            if (!confirm('<?php _e('آیا از حذف این آدرس مطمئن هستید؟', 'ganjeh'); ?>')) return;

            const formData = new FormData();
            formData.append('action', 'ganjeh_delete_address');
            formData.append('address_id', addressId);

            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    this.addresses = data.data.addresses;
                    if (this.selectedAddress?.id === addressId) {
                        this.selectedAddress = this.addresses[0] || null;
                    }
                    if (this.addresses.length === 0) {
                        this.showAddForm = true;
                    }
                }
            });
        }
    }
}

// Shipping Manager Alpine component - Show courier/pickup only for Tehran
function shippingManager() {
    return {
        isTehran: false,

        init() {
            this.checkTehran();
            // Watch for address changes
            const observer = new MutationObserver(() => this.checkTehran());
            const stateField = document.getElementById('billing_state');
            const cityField = document.getElementById('billing_city');
            if (stateField) observer.observe(stateField, { attributes: true, attributeFilter: ['value'] });
            if (cityField) observer.observe(cityField, { attributes: true, attributeFilter: ['value'] });

            // Also listen for custom event from address manager
            window.addEventListener('address-changed', () => this.checkTehran());

            // Interval check as fallback
            setInterval(() => this.checkTehran(), 500);
        },

        checkTehran() {
            const state = document.getElementById('billing_state')?.value || '';
            const city = document.getElementById('billing_city')?.value || '';

            // Check if state is Tehran (THR) and city contains تهران
            const isTehranState = state === 'THR';
            const isTehranCity = city.includes('تهران') || city.toLowerCase().includes('tehran');

            const wasTehran = this.isTehran;
            this.isTehran = isTehranState && isTehranCity;

            // If switching to Tehran and post was selected, switch to collection (ارسال عادی)
            if (!wasTehran && this.isTehran) {
                const selectedMethod = document.querySelector('input[name="ganjeh_shipping_method"]:checked')?.value;
                if (selectedMethod === 'post') {
                    selectShipping('collection', <?php echo $collection_cost; ?>);
                }
            }

            // If switching away from Tehran and a Tehran-only method was selected, switch to post
            if (wasTehran && !this.isTehran) {
                const selectedMethod = document.querySelector('input[name="ganjeh_shipping_method"]:checked')?.value;
                if (['express', 'collection', 'pickup'].includes(selectedMethod)) {
                    selectShipping('post', <?php echo $post_cost; ?>);
                }
            }
        }
    }
}

// Coupon handler Alpine component
function couponHandler() {
    return {
        open: false,
        loading: false,
        message: '',
        success: false,
        couponCode: '',

        applyCoupon() {
            if (!this.couponCode.trim()) {
                this.message = '<?php _e('لطفاً کد تخفیف را وارد کنید', 'ganjeh'); ?>';
                this.success = false;
                return;
            }

            this.loading = true;
            this.message = '';

            // Use WooCommerce's built-in coupon application with security nonce
            const formData = new FormData();
            formData.append('coupon_code', this.couponCode.trim());
            formData.append('security', '<?php echo wp_create_nonce('apply-coupon'); ?>');

            fetch('<?php echo esc_url(WC_AJAX::get_endpoint('apply_coupon')); ?>', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(r => r.text())
            .then(html => {
                this.loading = false;
                // Check if response contains error
                if (html.includes('woocommerce-error') || html.includes('error') || html.includes('نامعتبر') || html.includes('وجود ندارد')) {
                    this.message = '<?php _e('کد تخفیف نامعتبر است', 'ganjeh'); ?>';
                    this.success = false;
                } else {
                    this.message = '<?php _e('کد تخفیف اعمال شد', 'ganjeh'); ?>';
                    this.success = true;
                    setTimeout(() => location.reload(), 1000);
                }
            })
            .catch(() => {
                this.loading = false;
                this.message = '<?php _e('خطا در اعمال کد تخفیف', 'ganjeh'); ?>';
                this.success = false;
            });
        },

        removeCoupon(code) {
            const formData = new FormData();
            formData.append('coupon', code);
            formData.append('security', '<?php echo wp_create_nonce('remove-coupon'); ?>');

            fetch('<?php echo esc_url(WC_AJAX::get_endpoint('remove_coupon')); ?>', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(() => location.reload());
        }
    }
}

// Cross-sell Popup Alpine component
function crossSellPopup() {
    return {
        showPopup: false,
        loading: false,
        products: [],
        addedProducts: [],
        addingProduct: null,
        checkingProducts: false,
        scrollPosition: 0,

        init() {
            // Listen for show popup event
            window.addEventListener('show-crosssell-popup', () => {
                this.checkAndShowPopup();
            });
        },

        lockScroll() {
            this.scrollPosition = window.pageYOffset;
            document.body.style.position = 'fixed';
            document.body.style.top = `-${this.scrollPosition}px`;
            document.body.style.width = '100%';
        },

        unlockScroll() {
            document.body.style.position = '';
            document.body.style.top = '';
            document.body.style.width = '';
            window.scrollTo(0, this.scrollPosition);
        },

        checkAndShowPopup() {
            // First fetch products, then decide to show popup or proceed
            this.checkingProducts = true;

            const formData = new FormData();
            formData.append('action', 'ganjeh_get_crosssell_products');
            formData.append('nonce', ganjeh.nonce);

            fetch(ganjeh.ajax_url, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(r => r.json())
            .then(data => {
                this.checkingProducts = false;
                console.log('Cross-sell response:', data);

                if (data.success && data.data.products && data.data.products.length > 0) {
                    // Has products, show popup
                    this.products = data.data.products;
                    this.showPopup = true;
                    this.loading = false;
                    this.lockScroll();
                } else {
                    // No products, proceed directly to payment
                    console.log('No cross-sell products found, proceeding to payment');
                    this.proceedToPayment();
                }
            })
            .catch((err) => {
                console.error('Cross-sell error:', err);
                this.checkingProducts = false;
                this.proceedToPayment();
            });
        },

        closePopup() {
            this.showPopup = false;
            this.unlockScroll();
        },

        addToCart(product) {
            if (this.addedProducts.includes(product.id)) return;

            this.addingProduct = product.id;

            const formData = new FormData();
            formData.append('action', 'ganjeh_add_crosssell_to_cart');
            formData.append('product_id', product.id);
            formData.append('nonce', ganjeh.nonce);

            fetch(ganjeh.ajax_url, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(r => r.json())
            .then(data => {
                this.addingProduct = null;
                if (data.success) {
                    this.addedProducts.push(product.id);
                    // Update totals display if provided
                    if (data.data.cart_total) {
                        const totalEl = document.querySelector('.total-row.final span:last-child');
                        const barTotalEl = document.querySelector('.bar-total .value');
                        if (totalEl) totalEl.innerHTML = data.data.cart_total;
                        if (barTotalEl) barTotalEl.innerHTML = data.data.cart_total;
                    }
                }
            })
            .catch(() => {
                this.addingProduct = null;
            });
        },

        proceedToPayment() {
            this.closePopup();
            // Submit the checkout form
            submitCheckoutForm();
        }
    }
}
</script>

<?php do_action('woocommerce_after_checkout_form', $checkout); ?>
