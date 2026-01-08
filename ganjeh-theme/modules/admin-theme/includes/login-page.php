<?php
/**
 * Login Page Customization
 * 
 * سفارشی‌سازی صفحه ورود به پیشخوان
 * 
 * @package Developer_Starter
 * @subpackage Modules/Admin_Theme
 */

defined('ABSPATH') || exit;

/**
 * تغییر URL لوگو
 */
add_filter('login_headerurl', function() {
    return home_url('/');
});

/**
 * تغییر تایتل لوگو
 */
add_filter('login_headertext', function() {
    return get_bloginfo('name') . ' - ' . get_bloginfo('description');
});

/**
 * تغییر پیام‌های خطا
 */
add_filter('login_errors', function($error) {
    // امنیت بیشتر - عدم نمایش اطلاعات دقیق
    if (strpos($error, 'incorrect') !== false || strpos($error, 'Invalid') !== false) {
        return '<strong>خطا:</strong> نام کاربری یا رمز عبور اشتباه است.';
    }
    return $error;
});

/**
 * پیام خوش‌آمدگویی سفارشی
 */
add_filter('login_message', function($message) {
    if (empty($message)) {
        $message = '<div class="message">';
        $message .= '<p>به پیشخوان ' . get_bloginfo('name') . ' خوش آمدید! 👋</p>';
        $message .= '</div>';
    }
    return $message;
});

/**
 * افزودن JavaScript سفارشی
 */
add_action('login_footer', function() {
    ?>
    <script>
    (function() {
        'use strict';
        
        // انیمیشن هنگام فوکوس
        document.querySelectorAll('input[type="text"], input[type="password"]').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                if (!this.value) {
                    this.parentElement.classList.remove('focused');
                }
            });
        });
        
        // Show/Hide Password
        const form = document.getElementById('loginform');
        if (form) {
            const passField = form.querySelector('input[type="password"]');
            if (passField) {
                const toggleBtn = document.createElement('button');
                toggleBtn.type = 'button';
                toggleBtn.className = 'toggle-password';
                toggleBtn.textContent = '👁️';
                toggleBtn.style.cssText = `
                    position: absolute;
                    left: 12px;
                    top: 50%;
                    transform: translateY(-50%);
                    background: none;
                    border: none;
                    cursor: pointer;
                    font-size: 18px;
                    opacity: 0.6;
                    transition: opacity 0.3s;
                `;
                
                passField.parentElement.style.position = 'relative';
                passField.parentElement.appendChild(toggleBtn);
                
                toggleBtn.addEventListener('click', function() {
                    const type = passField.type === 'password' ? 'text' : 'password';
                    passField.type = type;
                    this.textContent = type === 'password' ? '👁️' : '🔒';
                });
                
                toggleBtn.addEventListener('mouseover', function() {
                    this.style.opacity = '1';
                });
                
                toggleBtn.addEventListener('mouseout', function() {
                    this.style.opacity = '0.6';
                });
            }
        }
        
        // Loading state
        if (form) {
            form.addEventListener('submit', function() {
                form.classList.add('loading');
            });
        }
        
        // Shake animation on error
        const errorDiv = document.getElementById('login_error');
        if (errorDiv) {
            errorDiv.style.animation = 'shake 0.5s';
        }
        
        // Add shake keyframe
        const style = document.createElement('style');
        style.textContent = `
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                25% { transform: translateX(-10px); }
                75% { transform: translateX(10px); }
            }
        `;
        document.head.appendChild(style);
        
    })();
    </script>
    <?php
});

/**
 * تغییر متن دکمه ورود
 */
add_filter('gettext', function($translated_text, $text, $domain) {
    if ($text === 'Log In') {
        return 'ورود';
    }
    if ($text === 'Username or Email Address') {
        return 'نام کاربری یا ایمیل';
    }
    if ($text === 'Password') {
        return 'رمز عبور';
    }
    if ($text === 'Remember Me') {
        return 'مرا به خاطر بسپار';
    }
    if ($text === 'Lost your password?') {
        return 'رمز عبور را فراموش کرده‌اید؟';
    }
    return $translated_text;
}, 20, 3);

/**
 * افزودن لینک‌های سفارشی
 */
add_action('login_footer', function() {
    ?>
    <div style="text-align: center; margin-top: 20px;">
        <p style="color: rgba(255,255,255,0.7); font-size: 13px;">
            © <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. تمامی حقوق محفوظ است.
        </p>
    </div>
    <?php
}, 20);

/**
 * حذف "Powered by WordPress"
 */
add_filter('login_footer_text', function() {
    return '';
});

/**
 * تغییر فاویکون صفحه ورود
 */
add_action('login_head', function() {
    $favicon = get_site_icon_url();
    if ($favicon) {
        echo '<link rel="icon" href="' . esc_url($favicon) . '">';
    }
});

/**
 * رمزنگاری پیشرفته برای امنیت بیشتر
 */
add_filter('authenticate', function($user, $username, $password) {
    // اگر درخواست Ajax نیست
    if (!wp_doing_ajax() && !empty($username) && !empty($password)) {
        // لاگ تلاش‌های ناموفق
        if (is_wp_error($user)) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            error_log(sprintf(
                'Login attempt failed - Username: %s, IP: %s',
                $username,
                $ip
            ));
        }
    }
    return $user;
}, 30, 3);

/**
 * افزودن Meta Tags
 */
add_action('login_head', function() {
    ?>
    <meta name="robots" content="noindex, nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <?php
});

/**
 * سفارشی‌سازی فرم فراموشی رمز عبور
 */
add_action('lostpassword_form', function() {
    ?>
    <p class="description" style="margin-bottom: 16px;">
        لینک بازیابی رمز عبور به ایمیل شما ارسال می‌شود.
    </p>
    <?php
});

/**
 * سفارشی‌سازی ایمیل بازیابی رمز عبور
 */
add_filter('retrieve_password_message', function($message, $key, $user_login, $user_data) {
    $message = sprintf(__('سلام %s،'), $user_data->display_name) . "\r\n\r\n";
    $message .= __('درخواست بازیابی رمز عبور برای حساب کاربری شما دریافت شد:') . "\r\n\r\n";
    $message .= network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') . "\r\n\r\n";
    $message .= __('اگر این درخواست را شما ارسال نکرده‌اید، این ایمیل را نادیده بگیرید.') . "\r\n\r\n";
    $message .= sprintf(__('با تشکر،') . "\r\n%s", get_bloginfo('name'));
    
    return $message;
}, 10, 4);

/**
 * محدودیت تعداد تلاش‌های ورود (امنیت)
 */
add_action('wp_login_failed', function($username) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $attempts_key = 'login_attempts_' . md5($ip);
    
    $attempts = get_transient($attempts_key) ?: 0;
    $attempts++;
    
    set_transient($attempts_key, $attempts, HOUR_IN_SECONDS);
    
    if ($attempts >= 5) {
        wp_die(
            'تعداد تلاش‌های ورود شما از حد مجاز گذشته است. لطفاً یک ساعت دیگر تلاش کنید.',
            'خطای امنیتی',
            ['response' => 403]
        );
    }
});

/**
 * پاک کردن شمارنده بعد از ورود موفق
 */
add_action('wp_login', function() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $attempts_key = 'login_attempts_' . md5($ip);
    delete_transient($attempts_key);
}, 10, 0);

/**
 * افزودن گزینه "ورود با Google" (اختیاری)
 */
add_action('login_form', function() {
    // فقط اگر پلاگین OAuth نصب باشه
    if (class_exists('WP_OAuth')) {
        ?>
        <div class="social-login">
            <p class="social-login-title">یا ورود با:</p>
            <div class="social-buttons">
                <a href="<?php echo wp_login_url(); ?>?oauth=google" class="social-button google">
                    Google
                </a>
            </div>
        </div>
        <?php
    }
});
