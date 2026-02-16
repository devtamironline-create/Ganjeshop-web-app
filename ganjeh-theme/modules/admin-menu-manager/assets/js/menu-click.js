/**
 * مدیریت باز/بسته شدن منوها با کلیک
 * نسخه 5.0.0 - منوی موبایل با کنترل مستقیم inline style
 */
(function($) {
    'use strict';

    $(document).ready(function() {

        var isMobile = window.innerWidth <= 782;

        /**
         * تنظیم رفتار کلیک برای منوها (زیرمنوها)
         */
        function setupMenuClickBehavior() {
            var $adminMenu = $('#adminmenu');

            $adminMenu.find('li.menu-top').each(function() {
                var $menuItem = $(this);
                var $link = $menuItem.find('> a.menu-top');
                var $submenu = $menuItem.find('.wp-submenu');

                if ($submenu.length > 0) {
                    $link.off('click.dstmenu');
                    $link.on('click.dstmenu', function(e) {
                        e.preventDefault();
                        e.stopPropagation();

                        var isOpen = $menuItem.hasClass('dst-menu-open');
                        $adminMenu.find('li.menu-top').removeClass('dst-menu-open');

                        if (!isOpen) {
                            $menuItem.addClass('dst-menu-open');
                        }
                        return false;
                    });
                }
            });

            $(document).on('click.dstmenu', function(e) {
                if (!$(e.target).closest('#adminmenu').length) {
                    $adminMenu.find('li.menu-top').removeClass('dst-menu-open');
                }
            });

            $adminMenu.find('.wp-submenu').on('click.dstmenu', function(e) {
                e.stopPropagation();
            });
        }

        /**
         * غیرفعال کردن کامل سیستم responsive وردپرس
         */
        function killWpResponsive() {
            // حذف کلاس‌های WP
            $('body').removeClass('wp-responsive-open');

            // حذف دکمه WP
            $('#wp-responsive-toggle').remove();

            // غیرفعال کردن event های WP
            $(document).off('click.wp-responsive');
            $(window).off('resize.wp-responsive');

            // حذف inline style هایی که WP روی adminmenuwrap گذاشته
            var $wrap = $('#adminmenuwrap');
            if ($wrap.length) {
                $wrap.removeAttr('style');
            }
        }

        /**
         * تنظیم منوی موبایل
         */
        function setupMobileMenu() {
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

            $('body').append($toggle).append($overlay);

            // کلیک دکمه همبرگر - toggle
            $toggle.on('click touchend', function(e) {
                e.preventDefault();
                e.stopPropagation();

                if ($('body').hasClass('dst-mobile-menu-open')) {
                    closeMobileMenu();
                } else {
                    openMobileMenu();
                }
            });

            // بستن با کلیک روی اورلی
            $overlay.on('click touchend', function(e) {
                e.preventDefault();
                closeMobileMenu();
            });

            // بستن با Escape
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && $('body').hasClass('dst-mobile-menu-open')) {
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
            $('body').css('overflow', 'hidden');
        }

        /**
         * بستن منوی موبایل
         */
        function closeMobileMenu() {
            $('body').removeClass('dst-mobile-menu-open');
            $('.dst-mobile-menu-toggle').removeClass('is-open');
            $('.dst-mobile-overlay').removeClass('is-visible');
            $('body').css('overflow', '');
        }

        /**
         * بررسی سایز صفحه
         */
        function checkScreenSize() {
            isMobile = window.innerWidth <= 782;
            if (!isMobile) {
                closeMobileMenu();
            }
        }

        /**
         * راه‌اندازی اولیه
         */

        // 1. اول WP responsive رو بکش
        killWpResponsive();

        // 2. منو بسته شروع بشه
        closeMobileMenu();

        // 3. تنظیم رفتار کلیک زیرمنوها
        setupMenuClickBehavior();

        // 4. ساخت دکمه همبرگر
        setupMobileMenu();

        // هر 500ms چک کن WP دوباره responsive رو فعال نکرده باشه
        var wpKillInterval = setInterval(function() {
            if (window.innerWidth <= 782) {
                $('body').removeClass('wp-responsive-open');
                $('#wp-responsive-toggle').remove();
            }
        }, 500);

        // بعد از 5 ثانیه interval رو متوقف کن
        setTimeout(function() {
            clearInterval(wpKillInterval);
        }, 5000);

        // بررسی سایز در resize
        $(window).on('resize', function() {
            checkScreenSize();
            $('body').removeClass('wp-responsive-open');
        });

        // مراقبت از تغییرات منو
        var menuObserverTimeout = null;
        var observer = new MutationObserver(function(mutations) {
            var hasNewNodes = mutations.some(function(m) {
                return m.addedNodes.length > 0;
            });
            if (hasNewNodes) {
                if (menuObserverTimeout) clearTimeout(menuObserverTimeout);
                menuObserverTimeout = setTimeout(setupMenuClickBehavior, 100);
            }
        });

        var menuElement = document.getElementById('adminmenu');
        if (menuElement) {
            observer.observe(menuElement, {
                childList: true,
                subtree: false
            });
        }

        // تنظیمات صفحه تنظیمات منو
        $('.dst-menu-mode-option').on('click', function() {
            $(this).find('input[type="radio"]').prop('checked', true);
            $('.dst-menu-mode-option').removeClass('selected');
            $(this).addClass('selected');
        });

    });

})(jQuery);
