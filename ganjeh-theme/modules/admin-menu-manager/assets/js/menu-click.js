/**
 * مدیریت باز/بسته شدن منوها با کلیک
 * نسخه 4.1.0 - شامل منوی موبایل
 */
(function($) {
    'use strict';

    $(document).ready(function() {

        /**
         * تنظیم رفتار کلیک برای منوها
         */
        function setupMenuClickBehavior() {
            var $adminMenu = $('#adminmenu');

            // پیدا کردن منوهایی که زیرمنو دارند
            $adminMenu.find('li.menu-top').each(function() {
                var $menuItem = $(this);
                var $link = $menuItem.find('> a.menu-top');
                var $submenu = $menuItem.find('.wp-submenu');

                // اگر زیرمنو دارد
                if ($submenu.length > 0) {

                    // حذف event های قبلی
                    $link.off('click.dstmenu');

                    // اضافه کردن event جدید
                    $link.on('click.dstmenu', function(e) {
                        e.preventDefault();
                        e.stopPropagation();

                        var isOpen = $menuItem.hasClass('dst-menu-open');

                        // بستن همه منوهای باز
                        $adminMenu.find('li.menu-top').removeClass('dst-menu-open');

                        // اگر بسته بود، باز کن
                        if (!isOpen) {
                            $menuItem.addClass('dst-menu-open');
                        }

                        return false;
                    });
                }
            });

            // بستن منو با کلیک خارج از آن
            $(document).on('click.dstmenu', function(e) {
                // اگر کلیک خارج از منو بود
                if (!$(e.target).closest('#adminmenu').length) {
                    $adminMenu.find('li.menu-top').removeClass('dst-menu-open');
                }
            });

            // جلوگیری از بسته شدن با کلیک داخل زیرمنو
            $adminMenu.find('.wp-submenu').on('click.dstmenu', function(e) {
                e.stopPropagation();
            });
        }

        /**
         * تنظیم منوی موبایل
         */
        function setupMobileMenu() {
            // اگر قبلا اضافه شده، خارج شو
            if ($('.dst-mobile-menu-toggle').length) return;

            // ساخت دکمه همبرگر
            var $toggle = $('<button class="dst-mobile-menu-toggle" aria-label="منو">' +
                '<svg class="icon-menu" viewBox="0 0 24 24" fill="none" stroke="currentColor">' +
                '<line x1="3" y1="6" x2="21" y2="6"/>' +
                '<line x1="3" y1="12" x2="21" y2="12"/>' +
                '<line x1="3" y1="18" x2="21" y2="18"/>' +
                '</svg>' +
                '<svg class="icon-close" viewBox="0 0 24 24" fill="none" stroke="currentColor">' +
                '<line x1="18" y1="6" x2="6" y2="18"/>' +
                '<line x1="6" y1="6" x2="18" y2="18"/>' +
                '</svg>' +
                '</button>');

            // ساخت اورلی
            var $overlay = $('<div class="dst-mobile-overlay"></div>');

            // اضافه به body
            $('body').append($toggle).append($overlay);

            // هندلر کلیک دکمه
            $toggle.on('click', function() {
                var isOpen = $(this).hasClass('is-open');

                if (isOpen) {
                    closeMobileMenu();
                } else {
                    openMobileMenu();
                }
            });

            // بستن با کلیک روی اورلی
            $overlay.on('click', function() {
                closeMobileMenu();
            });

            // بستن با کلید Escape
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && $('body').hasClass('dst-mobile-menu-open')) {
                    closeMobileMenu();
                }
            });

            // بستن منو وقتی روی لینک کلیک می‌شود
            $('#adminmenu a').on('click', function() {
                // اگر لینک واقعی است (نه زیرمنو)
                var href = $(this).attr('href');
                if (href && href !== '#' && !$(this).parent().hasClass('wp-has-submenu')) {
                    closeMobileMenu();
                }
            });
        }

        /**
         * باز کردن منوی موبایل
         */
        function openMobileMenu() {
            $('body').addClass('dst-mobile-menu-open');
            $('.dst-mobile-menu-toggle').addClass('is-open');
            $('.dst-mobile-overlay').addClass('is-visible');

            // غیرفعال کردن اسکرول body
            $('body').css('overflow', 'hidden');
        }

        /**
         * بستن منوی موبایل
         */
        function closeMobileMenu() {
            $('body').removeClass('dst-mobile-menu-open');
            $('.dst-mobile-menu-toggle').removeClass('is-open');
            $('.dst-mobile-overlay').removeClass('is-visible');

            // فعال کردن اسکرول body
            $('body').css('overflow', '');
        }

        /**
         * بررسی سایز صفحه
         */
        function checkScreenSize() {
            if (window.innerWidth > 782) {
                closeMobileMenu();
            }
        }

        /**
         * راه‌اندازی اولیه
         */
        setupMenuClickBehavior();
        setupMobileMenu();

        // بررسی سایز در resize
        $(window).on('resize', checkScreenSize);

        /**
         * اگر منوی جدیدی اضافه شد (توسط AJAX یا پلاگین)
         * دوباره تنظیم کن - با debounce
         */
        var menuObserverTimeout = null;
        var observer = new MutationObserver(function(mutations) {
            var hasNewNodes = mutations.some(function(m) {
                return m.addedNodes.length > 0;
            });

            if (hasNewNodes) {
                // Debounce - فقط یک بار اجرا شود
                if (menuObserverTimeout) clearTimeout(menuObserverTimeout);
                menuObserverTimeout = setTimeout(setupMenuClickBehavior, 100);
            }
        });

        // شروع نظارت بر تغییرات
        var menuElement = document.getElementById('adminmenu');
        if (menuElement) {
            observer.observe(menuElement, {
                childList: true,
                subtree: false // فقط فرزندان مستقیم
            });
        }

        /**
         * تنظیمات صفحه تنظیمات منو
         */
        $('.dst-menu-mode-option').on('click', function() {
            $(this).find('input[type="radio"]').prop('checked', true);
            $('.dst-menu-mode-option').removeClass('selected');
            $(this).addClass('selected');
        });

    });

})(jQuery);
