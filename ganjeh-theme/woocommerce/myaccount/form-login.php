<?php
/**
 * Login Form Template
 *
 * @package Ganjeh
 */

defined('ABSPATH') || exit;
?>

<div class="ganjeh-login min-h-screen flex flex-col">

    <!-- Logo -->
    <div class="flex-shrink-0 px-4 py-8 text-center">
        <?php if (has_custom_logo()) : ?>
            <?php the_custom_logo(); ?>
        <?php else : ?>
            <img src="<?php echo GANJEH_URI; ?>/assets/images/logo.svg" alt="<?php bloginfo('name'); ?>" class="h-16 mx-auto">
        <?php endif; ?>
    </div>

    <!-- Form Container -->
    <div class="flex-1 px-4">

        <?php do_action('woocommerce_before_customer_login_form'); ?>

        <div x-data="{ activeTab: 'login' }">

            <!-- Tab Buttons -->
            <div class="flex bg-gray-100 rounded-xl p-1 mb-6">
                <button
                    type="button"
                    class="flex-1 py-3 rounded-lg text-sm font-medium transition-colors"
                    :class="activeTab === 'login' ? 'bg-white text-secondary shadow-sm' : 'text-gray-500'"
                    @click="activeTab = 'login'"
                >
                    <?php _e('ورود', 'ganjeh'); ?>
                </button>
                <?php if ('yes' === get_option('woocommerce_enable_myaccount_registration')) : ?>
                    <button
                        type="button"
                        class="flex-1 py-3 rounded-lg text-sm font-medium transition-colors"
                        :class="activeTab === 'register' ? 'bg-white text-secondary shadow-sm' : 'text-gray-500'"
                        @click="activeTab = 'register'"
                    >
                        <?php _e('ثبت نام', 'ganjeh'); ?>
                    </button>
                <?php endif; ?>
            </div>

            <!-- Login Form -->
            <div x-show="activeTab === 'login'">
                <h2 class="text-lg font-bold text-secondary text-center mb-2">
                    <?php _e('ورود به حساب کاربری', 'ganjeh'); ?>
                </h2>
                <p class="text-sm text-gray-500 text-center mb-6">
                    <?php _e('لطفاً اطلاعات خود را وارد کنید', 'ganjeh'); ?>
                </p>

                <form method="post" class="space-y-4">

                    <?php do_action('woocommerce_login_form_start'); ?>

                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                            <?php _e('نام کاربری یا ایمیل', 'ganjeh'); ?> <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="username"
                            id="username"
                            class="input"
                            autocomplete="username"
                            required
                        >
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            <?php _e('رمز عبور', 'ganjeh'); ?> <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="input"
                            autocomplete="current-password"
                            required
                        >
                    </div>

                    <?php do_action('woocommerce_login_form'); ?>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                            <input type="checkbox" name="rememberme" class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary">
                            <?php _e('مرا به خاطر بسپار', 'ganjeh'); ?>
                        </label>
                        <a href="<?php echo esc_url(wp_lostpassword_url()); ?>" class="text-sm text-primary hover:underline">
                            <?php _e('فراموشی رمز عبور', 'ganjeh'); ?>
                        </a>
                    </div>

                    <?php wp_nonce_field('woocommerce-login', 'woocommerce-login-nonce'); ?>

                    <button type="submit" name="login" value="1" class="w-full btn-primary">
                        <?php _e('ورود', 'ganjeh'); ?>
                    </button>

                    <?php do_action('woocommerce_login_form_end'); ?>

                </form>
            </div>

            <!-- Register Form -->
            <?php if ('yes' === get_option('woocommerce_enable_myaccount_registration')) : ?>
                <div x-show="activeTab === 'register'" x-cloak>
                    <h2 class="text-lg font-bold text-secondary text-center mb-2">
                        <?php _e('ثبت نام', 'ganjeh'); ?>
                    </h2>
                    <p class="text-sm text-gray-500 text-center mb-6">
                        <?php _e('برای ثبت نام اطلاعات زیر را تکمیل کنید', 'ganjeh'); ?>
                    </p>

                    <form method="post" class="space-y-4" <?php do_action('woocommerce_register_form_tag'); ?>>

                        <?php do_action('woocommerce_register_form_start'); ?>

                        <?php if ('no' === get_option('woocommerce_registration_generate_username')) : ?>
                            <div>
                                <label for="reg_username" class="block text-sm font-medium text-gray-700 mb-2">
                                    <?php _e('نام کاربری', 'ganjeh'); ?> <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    name="username"
                                    id="reg_username"
                                    class="input"
                                    autocomplete="username"
                                    required
                                >
                            </div>
                        <?php endif; ?>

                        <div>
                            <label for="reg_email" class="block text-sm font-medium text-gray-700 mb-2">
                                <?php _e('ایمیل', 'ganjeh'); ?> <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="email"
                                name="email"
                                id="reg_email"
                                class="input"
                                autocomplete="email"
                                required
                            >
                        </div>

                        <?php if ('no' === get_option('woocommerce_registration_generate_password')) : ?>
                            <div>
                                <label for="reg_password" class="block text-sm font-medium text-gray-700 mb-2">
                                    <?php _e('رمز عبور', 'ganjeh'); ?> <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="password"
                                    name="password"
                                    id="reg_password"
                                    class="input"
                                    autocomplete="new-password"
                                    required
                                >
                            </div>
                        <?php else : ?>
                            <p class="text-sm text-gray-500"><?php _e('رمز عبور به ایمیل شما ارسال خواهد شد.', 'ganjeh'); ?></p>
                        <?php endif; ?>

                        <?php do_action('woocommerce_register_form'); ?>

                        <?php wp_nonce_field('woocommerce-register', 'woocommerce-register-nonce'); ?>

                        <button type="submit" name="register" value="1" class="w-full btn-primary">
                            <?php _e('ثبت نام', 'ganjeh'); ?>
                        </button>

                        <?php do_action('woocommerce_register_form_end'); ?>

                    </form>
                </div>
            <?php endif; ?>

        </div>

        <?php do_action('woocommerce_after_customer_login_form'); ?>

    </div>

    <!-- Terms -->
    <div class="flex-shrink-0 px-4 py-6 text-center">
        <p class="text-xs text-gray-400">
            <?php printf(
                __('ورود شما به منزله پذیرش %sقوانین و مقررات%s است.', 'ganjeh'),
                '<a href="' . home_url('/terms') . '" class="text-primary hover:underline">',
                '</a>'
            ); ?>
        </p>
    </div>

</div>
