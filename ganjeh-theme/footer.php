    <!-- Footer -->
    <footer class="site-footer">
        <?php
        $footer_phone = get_theme_mod('ganjeh_footer_phone', '021-12345678');
        $footer_address = get_theme_mod('ganjeh_footer_address', 'تهران، خیابان نمونه، پلاک ۱۲۳');
        $footer_email = get_theme_mod('ganjeh_footer_email', 'info@ganjemarket.com');
        $instagram = get_theme_mod('ganjeh_social_instagram', '');
        $telegram = get_theme_mod('ganjeh_social_telegram', '');
        $whatsapp = get_theme_mod('ganjeh_social_whatsapp', '');
        ?>

        <!-- Contact Info -->
        <div class="footer-contact">
            <div class="footer-contact-item">
                <div class="contact-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                </div>
                <div class="contact-details">
                    <span class="contact-label"><?php _e('تماس با ما', 'ganjeh'); ?></span>
                    <a href="tel:<?php echo esc_attr($footer_phone); ?>" class="contact-value" dir="ltr"><?php echo esc_html($footer_phone); ?></a>
                </div>
            </div>

            <div class="footer-contact-item">
                <div class="contact-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div class="contact-details">
                    <span class="contact-label"><?php _e('آدرس', 'ganjeh'); ?></span>
                    <span class="contact-value"><?php echo esc_html($footer_address); ?></span>
                </div>
            </div>
        </div>

        <!-- Footer Menus -->
        <div class="footer-menus">
            <?php if (has_nav_menu('footer-menu-1')) : ?>
            <div class="footer-menu-col">
                <?php
                $menu_name = wp_get_nav_menu_name('footer-menu-1');
                if ($menu_name) : ?>
                    <h4 class="footer-menu-title"><?php echo esc_html($menu_name); ?></h4>
                <?php endif; ?>
                <?php wp_nav_menu([
                    'theme_location' => 'footer-menu-1',
                    'container' => false,
                    'menu_class' => 'footer-menu-list',
                    'depth' => 1,
                ]); ?>
            </div>
            <?php endif; ?>

            <?php if (has_nav_menu('footer-menu-2')) : ?>
            <div class="footer-menu-col">
                <?php
                $menu_name = wp_get_nav_menu_name('footer-menu-2');
                if ($menu_name) : ?>
                    <h4 class="footer-menu-title"><?php echo esc_html($menu_name); ?></h4>
                <?php endif; ?>
                <?php wp_nav_menu([
                    'theme_location' => 'footer-menu-2',
                    'container' => false,
                    'menu_class' => 'footer-menu-list',
                    'depth' => 1,
                ]); ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Social Links -->
        <?php if ($instagram || $telegram || $whatsapp) : ?>
        <div class="footer-social">
            <span class="social-label"><?php _e('ما را در شبکه‌های اجتماعی دنبال کنید', 'ganjeh'); ?></span>
            <div class="social-links">
                <?php if ($instagram) : ?>
                <a href="<?php echo esc_url($instagram); ?>" class="social-link instagram" target="_blank" rel="noopener">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                    </svg>
                </a>
                <?php endif; ?>
                <?php if ($telegram) : ?>
                <a href="<?php echo esc_url($telegram); ?>" class="social-link telegram" target="_blank" rel="noopener">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                    </svg>
                </a>
                <?php endif; ?>
                <?php if ($whatsapp) : ?>
                <a href="<?php echo esc_url($whatsapp); ?>" class="social-link whatsapp" target="_blank" rel="noopener">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Copyright -->
        <div class="footer-copyright">
            <p><?php printf(__('© %s گنجه مارکت. تمامی حقوق محفوظ است.', 'ganjeh'), date('Y')); ?></p>
        </div>
    </footer>

    <style>
    .site-footer {
        background: #f9fafb;
        border-top: 1px solid #e5e7eb;
        padding: 24px 16px 100px;
    }

    .footer-contact {
        display: flex;
        flex-direction: column;
        gap: 16px;
        margin-bottom: 24px;
    }

    .footer-contact-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        background: white;
        padding: 14px 16px;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
    }

    .contact-icon {
        width: 40px;
        height: 40px;
        background: #f0fdf4;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .contact-icon svg {
        width: 20px;
        height: 20px;
        color: #4CB050;
    }

    .contact-details {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .contact-label {
        font-size: 12px;
        color: #6b7280;
    }

    .contact-value {
        font-size: 14px;
        font-weight: 500;
        color: #1f2937;
        text-decoration: none;
    }

    a.contact-value:hover {
        color: #4CB050;
    }

    .footer-menus {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    .footer-menu-col {
        background: white;
        padding: 16px;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
    }

    .footer-menu-title {
        font-size: 14px;
        font-weight: 700;
        color: #1f2937;
        margin: 0 0 12px;
    }

    .footer-menu-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .footer-menu-list li {
        margin-bottom: 8px;
    }

    .footer-menu-list li:last-child {
        margin-bottom: 0;
    }

    .footer-menu-list a {
        font-size: 13px;
        color: #6b7280;
        text-decoration: none;
        transition: color 0.2s;
    }

    .footer-menu-list a:hover {
        color: #4CB050;
    }

    .footer-social {
        text-align: center;
        margin-bottom: 20px;
    }

    .social-label {
        display: block;
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 12px;
    }

    .social-links {
        display: flex;
        justify-content: center;
        gap: 12px;
    }

    .social-link {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }

    .social-link svg {
        width: 22px;
        height: 22px;
    }

    .social-link.instagram {
        background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888);
        color: white;
    }

    .social-link.telegram {
        background: #0088cc;
        color: white;
    }

    .social-link.whatsapp {
        background: #25d366;
        color: white;
    }

    .social-link:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .footer-copyright {
        text-align: center;
    }

    .footer-copyright p {
        font-size: 12px;
        color: #9ca3af;
        margin: 0;
    }
    </style>

    <!-- Bottom Navigation (hide on product, cart, checkout pages) -->
    <?php if (!is_singular('product') && !is_cart() && !is_checkout()) : ?>
        <?php get_template_part('template-parts/components/bottom-nav'); ?>
    <?php endif; ?>

    <!-- Cart Toast Notification -->
    <div id="cart-toast" class="cart-toast" x-data="cartToast()" @show-cart-toast.window="show($event.detail)">
        <div class="cart-toast-content" x-show="visible" x-transition:enter="toast-enter" x-transition:leave="toast-leave">
            <div class="toast-icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <span class="toast-message" x-text="message"></span>
            <a :href="cartUrl" class="toast-btn"><?php _e('مشاهده سبد', 'ganjeh'); ?></a>
            <button type="button" class="toast-close" @click="hide()">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    <style>
    .cart-toast {
        position: fixed;
        bottom: 80px;
        left: 50%;
        transform: translateX(-50%);
        width: calc(100% - 32px);
        max-width: 480px;
        z-index: 9998;
        pointer-events: none;
    }
    .cart-toast-content {
        display: flex;
        align-items: center;
        gap: 12px;
        background: #1f2937;
        color: white;
        padding: 14px 16px;
        border-radius: 14px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        pointer-events: auto;
    }
    .toast-icon {
        width: 28px;
        height: 28px;
        background: #4CB050;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .toast-icon svg {
        color: white;
    }
    .toast-message {
        flex: 1;
        font-size: 14px;
        font-weight: 500;
    }
    .toast-btn {
        padding: 8px 16px;
        background: white;
        color: #1f2937;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
    }
    .toast-close {
        background: none;
        border: none;
        color: #9ca3af;
        cursor: pointer;
        padding: 4px;
    }
    .toast-enter { animation: slideUp 0.3s ease; }
    .toast-leave { animation: slideDown 0.3s ease; }
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes slideDown {
        from { opacity: 1; transform: translateY(0); }
        to { opacity: 0; transform: translateY(20px); }
    }
    </style>

    <script>
    function cartToast() {
        return {
            visible: false,
            message: '',
            cartUrl: '<?php echo wc_get_cart_url(); ?>',
            timeout: null,

            show(data) {
                this.message = data.message || '<?php _e('به سبد خرید اضافه شد', 'ganjeh'); ?>';
                if (data.cart_url) this.cartUrl = data.cart_url;
                this.visible = true;

                if (this.timeout) clearTimeout(this.timeout);
                this.timeout = setTimeout(() => this.hide(), 5000);
            },

            hide() {
                this.visible = false;
            }
        };
    }

    // Global function to show cart toast
    window.showCartToast = function(data) {
        window.dispatchEvent(new CustomEvent('show-cart-toast', { detail: data }));
    };
    </script>

    <!-- Login/Register Modal -->
    <?php if (!is_user_logged_in()) : ?>
    <div id="auth-modal" class="auth-modal" x-data="authModal()" @open-auth.window="openModal($event.detail)">
        <div class="auth-overlay" @click="closeModal()"></div>
        <div class="auth-content">
            <div class="auth-header">
                <h3 x-text="step === 'phone' ? 'ورود / ثبت نام' : 'کد تایید'"></h3>
                <button type="button" @click="closeModal()">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="auth-body">
                <!-- Step 1: Phone Number -->
                <div x-show="step === 'phone'" x-transition>
                    <p class="auth-desc">شماره موبایل خود را وارد کنید</p>
                    <div class="auth-input-group">
                        <input type="tel" x-model="mobile" @keyup.enter="sendOtp()" placeholder="09123456789" maxlength="11" dir="ltr" class="auth-input">
                    </div>
                    <button type="button" @click="sendOtp()" :disabled="loading" class="auth-btn">
                        <span x-show="!loading">دریافت کد تایید</span>
                        <svg x-show="loading" class="auth-spinner" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </div>

                <!-- Step 2: OTP Verification -->
                <div x-show="step === 'otp'" x-transition>
                    <p class="auth-desc">کد ارسال شده به <span x-text="mobile" dir="ltr"></span> را وارد کنید</p>

                    <!-- 4-digit OTP boxes -->
                    <div class="otp-boxes" dir="ltr">
                        <template x-for="(digit, index) in otpDigits" :key="index">
                            <input
                                type="text"
                                maxlength="1"
                                class="otp-box"
                                :value="digit"
                                @input="handleOtpInput($event, index)"
                                @keydown="handleOtpKeydown($event, index)"
                                @paste="handleOtpPaste($event)"
                                x-ref="otpInput"
                            >
                        </template>
                    </div>

                    <!-- Name field for new users -->
                    <div x-show="isNewUser" class="auth-input-group" style="margin-top: 16px;">
                        <input type="text" x-model="fullName" placeholder="نام و نام خانوادگی" class="auth-input">
                    </div>

                    <div class="auth-timer" x-show="timer > 0">
                        <span>ارسال مجدد تا</span>
                        <span x-text="formatTimer(timer)"></span>
                    </div>
                    <button type="button" x-show="timer === 0" @click="sendOtp()" class="auth-resend">ارسال مجدد کد</button>
                    <button type="button" @click="verifyOtp()" :disabled="loading" class="auth-btn">
                        <span x-show="!loading">تایید</span>
                        <svg x-show="loading" class="auth-spinner" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                    <button type="button" @click="step = 'phone'" class="auth-back">تغییر شماره</button>
                </div>

                <!-- Message -->
                <div class="auth-message" x-show="message" :class="messageType" x-text="message"></div>
            </div>
        </div>
    </div>

    <style>
    .auth-modal {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 9999;
        display: none;
        align-items: flex-end;
        justify-content: center;
    }
    .auth-modal.show { display: flex; }
    .auth-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
    }
    .auth-content {
        position: relative;
        width: 100%;
        max-width: 400px;
        background: white;
        border-radius: 20px 20px 0 0;
        max-height: 90vh;
        overflow-y: auto;
        animation: slideUp 0.3s ease;
    }
    @keyframes slideUp {
        from { transform: translateY(100%); }
        to { transform: translateY(0); }
    }
    .auth-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        border-bottom: 1px solid #f3f4f6;
    }
    .auth-header h3 {
        font-size: 16px;
        font-weight: 700;
        color: #1f2937;
        margin: 0;
    }
    .auth-header button {
        background: none;
        border: none;
        padding: 4px;
        cursor: pointer;
        color: #6b7280;
    }
    .auth-body {
        padding: 24px 20px 32px;
    }
    .auth-desc {
        font-size: 14px;
        color: #6b7280;
        margin: 0 0 20px;
        text-align: center;
    }
    .auth-input-group {
        margin-bottom: 16px;
    }
    .auth-input {
        width: 100%;
        padding: 14px 16px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        font-size: 15px;
        text-align: center;
        transition: all 0.2s;
    }
    .auth-input:focus {
        outline: none;
        border-color: #4CB050;
        box-shadow: 0 0 0 3px rgba(76, 176, 80, 0.1);
    }
    .otp-boxes {
        display: flex;
        gap: 12px;
        justify-content: center;
        margin-bottom: 16px;
    }
    .otp-box {
        width: 56px;
        height: 56px;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        font-size: 24px;
        font-weight: 700;
        text-align: center;
        transition: all 0.2s;
    }
    .otp-box:focus {
        outline: none;
        border-color: #4CB050;
        box-shadow: 0 0 0 3px rgba(76, 176, 80, 0.1);
    }
    .auth-btn {
        width: 100%;
        padding: 14px;
        background: linear-gradient(135deg, #4CB050, #3d9142);
        border: none;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 600;
        color: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 8px;
    }
    .auth-btn:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }
    .auth-spinner {
        width: 24px;
        height: 24px;
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    .auth-timer {
        text-align: center;
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 12px;
    }
    .auth-resend {
        display: block;
        width: 100%;
        background: none;
        border: none;
        font-size: 13px;
        color: #4CB050;
        cursor: pointer;
        margin-bottom: 12px;
    }
    .auth-back {
        display: block;
        width: 100%;
        background: none;
        border: none;
        font-size: 13px;
        color: #6b7280;
        cursor: pointer;
        margin-top: 16px;
    }
    .auth-message {
        padding: 12px;
        border-radius: 10px;
        font-size: 13px;
        text-align: center;
        margin-top: 16px;
    }
    .auth-message.success {
        background: #d1fae5;
        color: #065f46;
    }
    .auth-message.error {
        background: #fee2e2;
        color: #991b1b;
    }
    </style>

    <script>
    function authModal() {
        return {
            step: 'phone',
            mobile: '',
            otpDigits: ['', '', '', ''],
            fullName: '',
            loading: false,
            message: '',
            messageType: '',
            timer: 0,
            timerInterval: null,
            isNewUser: false,
            pendingAction: null,

            openModal(actionData) {
                document.getElementById('auth-modal').classList.add('show');
                this.reset();
                if (actionData) {
                    this.pendingAction = actionData;
                }
            },

            closeModal() {
                document.getElementById('auth-modal').classList.remove('show');
            },

            reset() {
                this.step = 'phone';
                this.otpDigits = ['', '', '', ''];
                this.fullName = '';
                this.message = '';
                this.timer = 0;
                this.isNewUser = false;
                this.pendingAction = null;
                if (this.timerInterval) clearInterval(this.timerInterval);
            },

            handleOtpInput(event, index) {
                const value = event.target.value.replace(/[^0-9]/g, '');
                this.otpDigits[index] = value.slice(-1);
                event.target.value = this.otpDigits[index];

                if (value && index < 3) {
                    const inputs = document.querySelectorAll('.otp-box');
                    inputs[index + 1].focus();
                }
            },

            handleOtpKeydown(event, index) {
                if (event.key === 'Backspace' && !this.otpDigits[index] && index > 0) {
                    const inputs = document.querySelectorAll('.otp-box');
                    inputs[index - 1].focus();
                }
            },

            handleOtpPaste(event) {
                event.preventDefault();
                const paste = (event.clipboardData || window.clipboardData).getData('text');
                const digits = paste.replace(/[^0-9]/g, '').slice(0, 4).split('');
                const inputs = document.querySelectorAll('.otp-box');

                digits.forEach((digit, i) => {
                    this.otpDigits[i] = digit;
                    if (inputs[i]) inputs[i].value = digit;
                });

                if (digits.length > 0 && inputs[Math.min(digits.length, 3)]) {
                    inputs[Math.min(digits.length, 3)].focus();
                }
            },

            formatTimer(seconds) {
                const m = Math.floor(seconds / 60);
                const s = seconds % 60;
                return `${m}:${s.toString().padStart(2, '0')}`;
            },

            startTimer() {
                this.timer = 120;
                if (this.timerInterval) clearInterval(this.timerInterval);
                this.timerInterval = setInterval(() => {
                    this.timer--;
                    if (this.timer <= 0) {
                        clearInterval(this.timerInterval);
                    }
                }, 1000);
            },

            sendOtp() {
                if (!this.mobile || this.mobile.length < 10) {
                    this.message = 'شماره موبایل را وارد کنید';
                    this.messageType = 'error';
                    return;
                }

                this.loading = true;
                this.message = '';

                fetch(ganjeh.ajax_url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'ganjeh_send_otp',
                        mobile: this.mobile,
                        nonce: ganjeh.nonce
                    })
                })
                .then(r => r.json())
                .then(data => {
                    this.loading = false;
                    if (data.success) {
                        this.isNewUser = data.data.is_new_user;
                        this.step = 'otp';
                        this.startTimer();
                        this.message = data.data.message;
                        this.messageType = 'success';
                        setTimeout(() => this.message = '', 3000);
                    } else {
                        this.message = data.data.message;
                        this.messageType = 'error';
                    }
                })
                .catch(() => {
                    this.loading = false;
                    this.message = 'خطا در ارسال. لطفاً دوباره تلاش کنید';
                    this.messageType = 'error';
                });
            },

            verifyOtp() {
                const otp = this.otpDigits.join('');
                if (otp.length < 4) {
                    this.message = 'کد تایید را کامل وارد کنید';
                    this.messageType = 'error';
                    return;
                }

                if (this.isNewUser && !this.fullName.trim()) {
                    this.message = 'نام و نام خانوادگی را وارد کنید';
                    this.messageType = 'error';
                    return;
                }

                this.loading = true;
                this.message = '';

                const params = {
                    action: 'ganjeh_verify_otp',
                    mobile: this.mobile,
                    otp: otp,
                    nonce: ganjeh.nonce
                };

                if (this.isNewUser && this.fullName.trim()) {
                    params.full_name = this.fullName.trim();
                }

                fetch(ganjeh.ajax_url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams(params)
                })
                .then(r => r.json())
                .then(data => {
                    this.loading = false;
                    if (data.success) {
                        this.message = data.data.message;
                        this.messageType = 'success';

                        const action = this.pendingAction;
                        setTimeout(() => {
                            this.closeModal();

                            // Execute pending action after login
                            if (action && action.type === 'add_to_cart') {
                                if (action.isVariable) {
                                    // Open variation sheet for variable products
                                    const sheet = document.getElementById('variation-sheet');
                                    if (sheet) {
                                        sheet.classList.add('show');
                                    } else {
                                        location.reload();
                                    }
                                } else {
                                    // Add simple product to cart directly
                                    this.addToCartAfterLogin(action.productId, action.quantity || 1);
                                }
                            } else {
                                location.reload();
                            }
                        }, 1000);
                    } else {
                        this.message = data.data.message;
                        this.messageType = 'error';
                    }
                })
                .catch(() => {
                    this.loading = false;
                    this.message = 'خطا در تایید. لطفاً دوباره تلاش کنید';
                    this.messageType = 'error';
                });
            },

            addToCartAfterLogin(productId, quantity) {
                fetch(ganjeh.ajax_url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'ganjeh_add_to_cart',
                        product_id: productId,
                        quantity: quantity,
                        nonce: ganjeh.nonce
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const cartCount = document.querySelector('.ganjeh-cart-count');
                        if (cartCount) cartCount.textContent = data.data.cart_count;
                        window.showCartToast && window.showCartToast(data.data);
                    } else {
                        alert(data.data.message);
                    }
                })
                .catch(() => {
                    location.reload();
                });
            },

            };
    }

    // Global function to open auth modal
    window.openAuthModal = function(actionData) {
        window.dispatchEvent(new CustomEvent('open-auth', { detail: actionData || null }));
    };
    </script>
    <?php endif; ?>

</div><!-- #app -->

<?php wp_footer(); ?>
</body>
</html>
