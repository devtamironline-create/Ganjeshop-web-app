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

    // Initialize cart functionality
    initCart();

    // Initialize search
    initSearch();

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
 * Search Functionality
 */
function initSearch() {
    const searchInput = document.querySelector('input[name="s"]');
    if (!searchInput) return;

    let searchTimeout;
    const searchResults = document.createElement('div');
    searchResults.className = 'search-results absolute top-full left-0 right-0 bg-white rounded-xl shadow-lg mt-2 max-h-80 overflow-y-auto z-50 hidden';
    searchInput.parentNode.appendChild(searchResults);

    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimeout);
        const query = this.value.trim();

        if (query.length < 2) {
            searchResults.classList.add('hidden');
            return;
        }

        searchTimeout = setTimeout(function () {
            fetch(ganjeh.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'ganjeh_product_search',
                    s: query,
                    nonce: ganjeh.nonce,
                }),
            })
                .then((r) => r.json())
                .then((data) => {
                    if (data.success && data.data.products.length > 0) {
                        searchResults.innerHTML = data.data.products
                            .map(
                                (p) => `
                            <a href="${p.permalink}" class="flex items-center gap-3 p-3 hover:bg-gray-50 transition-colors">
                                <img src="${p.image}" alt="${p.name}" class="w-12 h-12 object-cover rounded-lg">
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-medium text-gray-800 truncate">${p.name}</h4>
                                    <p class="text-sm text-primary">${p.price}</p>
                                </div>
                            </a>
                        `
                            )
                            .join('');
                        searchResults.classList.remove('hidden');
                    } else {
                        searchResults.innerHTML = `
                            <div class="p-4 text-center text-gray-500 text-sm">
                                محصولی یافت نشد
                            </div>
                        `;
                        searchResults.classList.remove('hidden');
                    }
                });
        }, 300);
    });

    // Close search results when clicking outside
    document.addEventListener('click', function (e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.classList.add('hidden');
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
