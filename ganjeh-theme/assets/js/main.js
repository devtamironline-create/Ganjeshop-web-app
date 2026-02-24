/**
 * Ganjeh Market - Main JavaScript
 *
 * @package Ganjeh
 */

document.addEventListener('DOMContentLoaded', function () {
    // Initialize all Swiper sliders
    initHeroSlider();
    initProductSwipers();
    initCategorySlider();
    initSubcategoriesSwiper();

    // Initialize cart functionality
    initCart();

    // Initialize quantity inputs
    initQuantityInputs();

    // Initialize lazy loading
    initLazyLoad();
});

/**
 * Hero Slider
 */
function initHeroSlider() {
    const heroSlider = document.querySelector('.hero-slider');
    if (!heroSlider || typeof Swiper === 'undefined') return;

    new Swiper('.hero-slider', {
        slidesPerView: 1,
        spaceBetween: 0,
        loop: true,
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
        },
        pagination: {
            el: '.hero-pagination',
            clickable: true,
        },
        effect: 'slide',
        speed: 500,
        direction: 'horizontal',
    });
}

/**
 * Product Swipers (Homepage carousels)
 */
function initProductSwipers() {
    if (typeof Swiper === 'undefined') return;

    document.querySelectorAll('.products-swiper').forEach(function (el) {
        new Swiper(el, {
            slidesPerView: 'auto',
            spaceBetween: 12,
            freeMode: true,
            grabCursor: true,
            resistance: true,
            resistanceRatio: 0.5,
        });
    });
}

/**
 * Category Hero Slider
 */
function initCategorySlider() {
    const categorySlider = document.querySelector('.category-hero-slider');
    if (!categorySlider || typeof Swiper === 'undefined') return;

    new Swiper('.category-hero-slider', {
        slidesPerView: 1,
        spaceBetween: 0,
        loop: true,
        autoplay: {
            delay: 4000,
            disableOnInteraction: false,
        },
        pagination: {
            el: '.category-slider-pagination',
            clickable: true,
        },
    });
}

/**
 * Subcategories Swiper (in category page)
 */
function initSubcategoriesSwiper() {
    const subcatSwiper = document.querySelector('.subcategories-swiper');
    if (!subcatSwiper || typeof Swiper === 'undefined') return;

    new Swiper('.subcategories-swiper', {
        slidesPerView: 'auto',
        spaceBetween: 10,
        freeMode: true,
        grabCursor: true,
    });
}

/**
 * Cart Functions
 */
function initCart() {
    // Update cart count in header
    document.addEventListener('cart-updated', function (e) {
        const cartCount = document.querySelector('.ganjeh-cart-count');
        if (cartCount && e.detail) {
            cartCount.textContent = e.detail.cart_count;
            cartCount.classList.remove('hidden');

            // Show toast
            showToast(ganjeh.i18n.added_to_cart, 'success');
        }
    });
}

/**
 * Quantity Input Controls
 */
function initQuantityInputs() {
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-quantity-btn]');
        if (!btn) return;

        const container = btn.closest('[data-quantity-container]');
        const input = container.querySelector('input[type="number"]');
        const action = btn.dataset.quantityBtn;
        const min = parseInt(input.min) || 1;
        const max = parseInt(input.max) || 999;
        let value = parseInt(input.value) || min;

        if (action === 'plus' && value < max) {
            value++;
        } else if (action === 'minus' && value > min) {
            value--;
        }

        input.value = value;
        input.dispatchEvent(new Event('change', { bubbles: true }));
    });
}

/**
 * Lazy Loading Images
 */
function initLazyLoad() {
    if ('loading' in HTMLImageElement.prototype) {
        // Browser supports native lazy loading
        const images = document.querySelectorAll('img[loading="lazy"]');
        images.forEach((img) => {
            if (img.dataset.src) {
                img.src = img.dataset.src;
            }
        });
    } else {
        // Fallback to Intersection Observer
        const lazyImages = document.querySelectorAll('img[data-src]');

        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                        imageObserver.unobserve(img);
                    }
                });
            });

            lazyImages.forEach((img) => imageObserver.observe(img));
        } else {
            // Fallback: load all images
            lazyImages.forEach((img) => {
                img.src = img.dataset.src;
            });
        }
    }
}

/**
 * Toast Notification
 */
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(100%)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

/**
 * Format number to Persian
 */
function toPersianNumber(num) {
    const persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    return String(num).replace(/[0-9]/g, (d) => persianDigits[d]);
}

/**
 * Format price
 */
function formatPrice(price) {
    return toPersianNumber(price.toLocaleString()) + ' تومان';
}

// Expose functions globally
window.ganjehApp = {
    showToast,
    toPersianNumber,
    formatPrice,
};
