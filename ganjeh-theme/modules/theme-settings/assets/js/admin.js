/**
 * Theme Settings - Admin JavaScript
 */

jQuery(document).ready(function($) {
    
    // Color Picker با ذخیره مقدار
    $('.dst-color-picker').each(function() {
        var $input = $(this);
        var currentVal = $input.val();
        
        $input.wpColorPicker({
            change: function(event, ui) {
                // ذخیره مقدار جدید
                $(this).val(ui.color.toString());
                updateColorPreview();
            },
            clear: function() {
                updateColorPreview();
            }
        });
        
        // مطمئن شو مقدار اولیه حفظ بشه
        if (currentVal) {
            $input.val(currentVal);
        }
    });
    
    // Image Upload
    $('.dst-image-upload').each(function() {
        var $container = $(this);
        var $input = $container.find('input[type="hidden"]');
        var $preview = $container.find('.dst-image-preview');
        var $uploadBtn = $container.find('.dst-upload-btn');
        var $removeBtn = $container.find('.dst-remove-btn');
        
        // Upload
        $uploadBtn.on('click', function(e) {
            e.preventDefault();
            
            // ذخیره مقادیر Color Picker قبل از باز کردن Media
            saveColorPickerValues();
            
            var frame = wp.media({
                title: 'انتخاب تصویر',
                button: { text: 'انتخاب' },
                multiple: false
            });
            
            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                $input.val(attachment.url);
                $preview.html('<img src="' + attachment.url + '" alt="">');
                $removeBtn.show();
                
                // بازگردانی مقادیر Color Picker
                restoreColorPickerValues();
            });
            
            frame.on('close', function() {
                // بازگردانی مقادیر Color Picker حتی اگر کنسل شد
                restoreColorPickerValues();
            });
            
            frame.open();
        });
        
        // Remove
        $removeBtn.on('click', function(e) {
            e.preventDefault();
            $input.val('');
            $preview.html('');
            $(this).hide();
        });
    });
    
    // ذخیره مقادیر Color Picker
    var savedColorValues = {};
    
    function saveColorPickerValues() {
        savedColorValues = {};
        $('.dst-color-picker').each(function() {
            var id = $(this).attr('id');
            var val = $(this).val();
            if (id && val) {
                savedColorValues[id] = val;
            }
        });
    }
    
    function restoreColorPickerValues() {
        $.each(savedColorValues, function(id, val) {
            var $input = $('#' + id);
            if ($input.length && val) {
                $input.val(val);
                $input.wpColorPicker('color', val);
            }
        });
    }
    
    // پیش‌نمایش رنگ‌ها
    function updateColorPreview() {
        var colors = {
            primary: $('#primary_color').val() || '#3C50E0',
            secondary: $('#secondary_color').val() || '#10B981',
            accent: $('#accent_color').val() || '#F59E0B',
            text: $('#text_color').val() || '#1e293b',
            textLight: $('#text_light_color').val() || '#64748b',
            bg: $('#background_color').val() || '#ffffff',
            headerBg: $('#header_bg_color').val() || '#ffffff',
            headerText: $('#header_text_color').val() || '#1e293b',
            footerBg: $('#footer_bg_color').val() || '#1e293b',
            footerText: $('#footer_text_color').val() || '#e2e8f0'
        };
        
        // آپدیت پیش‌نمایش
        $('.dst-color-preview-box').each(function() {
            var type = $(this).data('preview');
            if (type && colors[type]) {
                $(this).css('background-color', colors[type]);
            }
        });
        
        // آپدیت پیش‌نمایش کلی
        if ($('.dst-theme-preview').length) {
            $('.dst-theme-preview').css({
                '--preview-primary': colors.primary,
                '--preview-secondary': colors.secondary,
                '--preview-accent': colors.accent,
                '--preview-text': colors.text,
                '--preview-bg': colors.bg
            });
        }
    }
    
    // اجرای اولیه پیش‌نمایش
    updateColorPreview();
    
    // Code Editor Enhancement
    $('textarea.code').each(function() {
        var $textarea = $(this);
        
        // Tab key support
        $textarea.on('keydown', function(e) {
            if (e.keyCode === 9) { // Tab
                e.preventDefault();
                var start = this.selectionStart;
                var end = this.selectionEnd;
                var value = $(this).val();
                
                $(this).val(value.substring(0, start) + '    ' + value.substring(end));
                this.selectionStart = this.selectionEnd = start + 4;
            }
        });
    });
    
    // Confirm before leaving with unsaved changes
    var formChanged = false;
    
    $('.dst-theme-settings-wrap form').on('change', 'input, select, textarea', function() {
        formChanged = true;
    });
    
    $('.dst-theme-settings-wrap form').on('submit', function() {
        formChanged = false;
    });
    
    $(window).on('beforeunload', function() {
        if (formChanged) {
            return 'تغییرات ذخیره نشده‌ای دارید. آیا مطمئن هستید؟';
        }
    });
    
});