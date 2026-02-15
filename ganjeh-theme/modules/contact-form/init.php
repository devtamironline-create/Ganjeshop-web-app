<?php
/**
 * Contact Form Module - Init
 * 
 * این فایل هنگام لود ماژول اجرا می‌شود
 * 
 * @package Developer_Starter
 * @subpackage Modules/Contact_Form
 */

defined('ABSPATH') || exit;

/**
 * کلاس اصلی ماژول فرم تماس
 */
class DST_Contact_Form {
    
    /**
     * تنظیمات ماژول
     */
    private $config;
    
    /**
     * مسیر ماژول
     */
    private $module_path;
    
    /**
     * URL ماژول
     */
    private $module_url;
    
    /**
     * سازنده
     */
    public function __construct() {
        // گرفتن اطلاعات ماژول
        $module = dst_get_module('contact-form');
        
        if ($module) {
            $this->config      = $module['config'];
            $this->module_path = $module['path'];
            $this->module_url  = $module['url'];
        }
        
        // هوک‌ها
        add_action('init', [$this, 'register_shortcode']);
        add_action('wp_ajax_dst_contact_submit', [$this, 'handle_submission']);
        add_action('wp_ajax_nopriv_dst_contact_submit', [$this, 'handle_submission']);
    }
    
    /**
     * ثبت شورت‌کد
     */
    public function register_shortcode() {
        add_shortcode('dst_contact_form', [$this, 'render_form']);
    }
    
    /**
     * نمایش فرم
     */
    public function render_form($atts) {
        $atts = shortcode_atts([
            'title'       => 'تماس با ما',
            'button_text' => 'ارسال پیام',
        ], $atts);
        
        ob_start();
        ?>
        
        <div class="dst-contact-form-wrapper">
            <?php if ($atts['title']): ?>
                <h3><?php echo esc_html($atts['title']); ?></h3>
            <?php endif; ?>
            
            <form class="dst-contact-form" id="dst-contact-form">
                
                <?php wp_nonce_field('dst_contact_form', 'dst_contact_nonce'); ?>
                
                <div class="form-group">
                    <label for="dst_name">نام و نام خانوادگی *</label>
                    <input type="text" 
                           id="dst_name" 
                           name="name" 
                           required 
                           placeholder="نام خود را وارد کنید">
                </div>
                
                <div class="form-group">
                    <label for="dst_email">ایمیل *</label>
                    <input type="email" 
                           id="dst_email" 
                           name="email" 
                           required 
                           placeholder="example@email.com">
                </div>
                
                <div class="form-group">
                    <label for="dst_phone">تلفن</label>
                    <input type="tel" 
                           id="dst_phone" 
                           name="phone" 
                           placeholder="09123456789">
                </div>
                
                <div class="form-group">
                    <label for="dst_message">پیام *</label>
                    <textarea id="dst_message" 
                              name="message" 
                              required 
                              rows="5" 
                              placeholder="پیام خود را بنویسید..."></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <?php echo esc_html($atts['button_text']); ?>
                    </button>
                </div>
                
                <div class="form-message" style="display: none;"></div>
                
            </form>
        </div>
        
        <?php
        return ob_get_clean();
    }
    
    /**
     * پردازش ارسال فرم
     */
    public function handle_submission() {
        // چک nonce
        check_ajax_referer('dst_contact_form', 'dst_contact_nonce');
        
        // دریافت داده‌ها
        $name    = sanitize_text_field($_POST['name'] ?? '');
        $email   = sanitize_email($_POST['email'] ?? '');
        $phone   = sanitize_text_field($_POST['phone'] ?? '');
        $message = sanitize_textarea_field($_POST['message'] ?? '');
        
        // اعتبارسنجی
        $errors = [];
        
        if (empty($name)) {
            $errors[] = 'نام الزامی است';
        }
        
        if (empty($email) || !is_email($email)) {
            $errors[] = 'ایمیل معتبر نیست';
        }
        
        if (empty($message)) {
            $errors[] = 'پیام الزامی است';
        }
        
        if (!empty($errors)) {
            wp_send_json_error([
                'message' => implode('<br>', $errors),
            ]);
        }
        
        // ذخیره در دیتابیس (اختیاری)
        $contact_data = [
            'name'    => $name,
            'email'   => $email,
            'phone'   => $phone,
            'message' => $message,
            'date'    => current_time('mysql'),
            'ip'      => $_SERVER['REMOTE_ADDR'] ?? '',
        ];
        
        // ارسال ایمیل
        $to      = $this->config['settings']['email'] ?? get_option('admin_email');
        $subject = 'پیام جدید از فرم تماس';
        $body    = $this->get_email_template($contact_data);
        $headers = ['Content-Type: text/html; charset=UTF-8'];
        
        $sent = wp_mail($to, $subject, $body, $headers);
        
        if ($sent) {
            // هوک بعد از ارسال موفق
            do_action('dst_contact_form_submitted', $contact_data);
            
            wp_send_json_success([
                'message' => $this->config['settings']['success_message'] 
                    ?? 'پیام شما با موفقیت ارسال شد',
            ]);
        } else {
            wp_send_json_error([
                'message' => $this->config['settings']['error_message'] 
                    ?? 'خطا در ارسال پیام. لطفاً دوباره تلاش کنید.',
            ]);
        }
    }
    
    /**
     * قالب ایمیل
     */
    private function get_email_template($data) {
        $template = '
        <html dir="rtl">
        <body style="font-family: Tahoma; direction: rtl; text-align: right;">
            <h2 style="color: #2563eb;">پیام جدید از فرم تماس</h2>
            
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 10px; border: 1px solid #ddd; background: #f5f5f5;">
                        <strong>نام:</strong>
                    </td>
                    <td style="padding: 10px; border: 1px solid #ddd;">
                        %name%
                    </td>
                </tr>
                <tr>
                    <td style="padding: 10px; border: 1px solid #ddd; background: #f5f5f5;">
                        <strong>ایمیل:</strong>
                    </td>
                    <td style="padding: 10px; border: 1px solid #ddd;">
                        %email%
                    </td>
                </tr>
                <tr>
                    <td style="padding: 10px; border: 1px solid #ddd; background: #f5f5f5;">
                        <strong>تلفن:</strong>
                    </td>
                    <td style="padding: 10px; border: 1px solid #ddd;">
                        %phone%
                    </td>
                </tr>
                <tr>
                    <td style="padding: 10px; border: 1px solid #ddd; background: #f5f5f5;">
                        <strong>پیام:</strong>
                    </td>
                    <td style="padding: 10px; border: 1px solid #ddd;">
                        %message%
                    </td>
                </tr>
                <tr>
                    <td style="padding: 10px; border: 1px solid #ddd; background: #f5f5f5;">
                        <strong>تاریخ:</strong>
                    </td>
                    <td style="padding: 10px; border: 1px solid #ddd;">
                        %date%
                    </td>
                </tr>
                <tr>
                    <td style="padding: 10px; border: 1px solid #ddd; background: #f5f5f5;">
                        <strong>IP:</strong>
                    </td>
                    <td style="padding: 10px; border: 1px solid #ddd;">
                        %ip%
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ';
        
        foreach ($data as $key => $value) {
            $template = str_replace("%{$key}%", esc_html($value), $template);
        }
        
        return $template;
    }
}

/**
 * راه‌اندازی ماژول
 */
new DST_Contact_Form();
