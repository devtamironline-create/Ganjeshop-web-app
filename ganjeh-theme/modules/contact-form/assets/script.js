/**
 * Contact Form Module - Script
 * 
 * مدیریت Ajax و اعتبارسنجی فرم
 */

(function($) {
    'use strict';
    
    /**
     * کلاس مدیریت فرم تماس
     */
    class ContactForm {
        constructor(form) {
            this.form = form;
            this.$form = $(form);
            this.$messageBox = this.$form.find('.form-message');
            this.$submitBtn = this.$form.find('button[type="submit"]');
            
            this.init();
        }
        
        init() {
            this.$form.on('submit', (e) => this.handleSubmit(e));
        }
        
        async handleSubmit(e) {
            e.preventDefault();
            
            // چک لودینگ
            if (this.$form.hasClass('is-loading')) {
                return;
            }
            
            // مخفی کردن پیام قبلی
            this.hideMessage();
            
            // دریافت داده‌ها
            const formData = new FormData(this.form);
            formData.append('action', 'dst_contact_submit');
            
            // اعتبارسنجی
            const validation = this.validate(formData);
            if (!validation.valid) {
                this.showMessage(validation.message, 'error');
                return;
            }
            
            // نمایش لودینگ
            this.setLoading(true);
            
            try {
                // ارسال Ajax
                const response = await $.ajax({
                    url: dstConfig.ajaxUrl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                });
                
                if (response.success) {
                    this.showMessage(response.data.message, 'success');
                    this.form.reset();
                } else {
                    this.showMessage(response.data.message, 'error');
                }
                
            } catch (error) {
                this.showMessage('خطا در ارسال. لطفاً دوباره تلاش کنید.', 'error');
                console.error('Contact form error:', error);
            } finally {
                this.setLoading(false);
            }
        }
        
        validate(formData) {
            const name = formData.get('name');
            const email = formData.get('email');
            const message = formData.get('message');
            
            if (!name || name.trim().length < 3) {
                return {
                    valid: false,
                    message: 'نام باید حداقل ۳ کاراکتر باشد'
                };
            }
            
            if (!email || !this.isValidEmail(email)) {
                return {
                    valid: false,
                    message: 'لطفاً یک ایمیل معتبر وارد کنید'
                };
            }
            
            if (!message || message.trim().length < 10) {
                return {
                    valid: false,
                    message: 'پیام باید حداقل ۱۰ کاراکتر باشد'
                };
            }
            
            return { valid: true };
        }
        
        isValidEmail(email) {
            const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return regex.test(email);
        }
        
        showMessage(message, type = 'info') {
            this.$messageBox
                .removeClass('success error info')
                .addClass(type)
                .html(message)
                .slideDown(300);
        }
        
        hideMessage() {
            this.$messageBox.slideUp(300);
        }
        
        setLoading(loading) {
            if (loading) {
                this.$form.addClass('is-loading');
                this.$submitBtn.prop('disabled', true);
            } else {
                this.$form.removeClass('is-loading');
                this.$submitBtn.prop('disabled', false);
            }
        }
    }
    
    /**
     * راه‌اندازی فرم‌ها
     */
    function initContactForms() {
        $('.dst-contact-form').each(function() {
            new ContactForm(this);
        });
    }
    
    /**
     * اجرا بعد از لود صفحه
     */
    $(document).ready(initContactForms);
    
    // برای بلاک‌های Gutenberg
    if (window.acf) {
        acf.addAction('render_block_preview', initContactForms);
    }
    
})(jQuery);
