<!DOCTYPE html>
<html <?php language_attributes(); ?> dir="rtl">
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#4CB050">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
    <style>
        .app-container { max-width: 515px; }
        body.admin-bar .hf-header-sticky,
        body.admin-bar .hf-header-fixed,
        body.admin-bar header[style*="position: fixed"],
        body.admin-bar header[style*="position:fixed"],
        body.admin-bar header.fixed,
        body.admin-bar header.sticky,
        body.admin-bar header.sticky {
            top: 0 !important;
        }
    </style>
</head>
<body <?php body_class('bg-gray-100 font-vazir'); ?> x-data="{ mobileMenu: false, searchOpen: false }">

<!-- App Container - Mobile-first max-width (515px = 15% larger than 448px) -->
<div id="app" class="app-container mx-auto bg-white min-h-screen relative shadow-xl overflow-hidden">

    <!-- Promo Banner -->
    <?php get_template_part('template-parts/header/promo-banner'); ?>

    <!-- Main Header -->
    <header class="sticky top-0 z-40 bg-white shadow-sm">
        <?php
        // First try theme settings (modules), then customizer
        $theme_settings = get_option('dst_theme_settings', []);
        $logo_url = !empty($theme_settings['logo_url']) ? $theme_settings['logo_url'] : '';

        // Fallback to customizer if no logo in theme settings
        if (empty($logo_url)) {
            $logo_id = get_theme_mod('ganjeh_logo');
            if ($logo_id) {
                $logo_url = wp_get_attachment_url($logo_id);
            }
        }

        $logo_width = get_theme_mod('ganjeh_logo_width', 120);
        ?>

        <!-- Logo & Cart Row -->
        <div class="header-top">
            <!-- Logo (Right Side in RTL) -->
            <a href="<?php echo home_url('/'); ?>" class="header-logo">
                <?php if ($logo_url) : ?>
                    <img src="<?php echo esc_url($logo_url); ?>" alt="<?php bloginfo('name'); ?>">
                <?php else : ?>
                    <span class="header-logo-text"><?php bloginfo('name'); ?></span>
                <?php endif; ?>
            </a>

            <!-- Header Icons (Left Side in RTL) -->
            <div class="header-icons">
                <!-- Profile Icon -->
                <?php if (is_user_logged_in()) : ?>
                    <a href="<?php echo wc_get_page_permalink('myaccount'); ?>" class="header-profile">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </a>
                <?php else : ?>
                    <button type="button" class="header-profile" @click="$dispatch('open-auth')">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </button>
                <?php endif; ?>

                <!-- Cart Icon -->
                <a href="<?php echo wc_get_cart_url(); ?>" class="header-cart">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <?php $cart_count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0; ?>
                    <span class="ganjeh-cart-count" <?php echo $cart_count === 0 ? 'style="display:none;"' : ''; ?>><?php echo $cart_count; ?></span>
                </a>
            </div>
        </div>

        <!-- AJAX Search Bar -->
        <div class="header-search" x-data="ajaxSearch()">
            <div class="relative">
                <form role="search" @submit.prevent>
                    <input
                        type="search"
                        name="s"
                        x-model="query"
                        @input.debounce.300ms="search"
                        @focus="showResults = query.length >= 2"
                        placeholder="<?php _e('جستجو در گنجه مارکت...', 'ganjeh'); ?>"
                        autocomplete="off"
                        class="ajax-search-input"
                    >
                    <input type="hidden" name="post_type" value="product">

                    <!-- Loading Spinner -->
                    <div class="search-icon" x-show="!loading">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <div class="search-icon" x-show="loading" x-cloak>
                        <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </div>
                </form>

                <!-- Search Results Dropdown -->
                <div class="search-results" x-show="showResults && (categories.length > 0 || products.length > 0)" x-cloak @click.outside="showResults = false">

                    <!-- Categories -->
                    <template x-if="categories.length > 0">
                        <div class="search-section">
                            <div class="search-section-title">دسته‌بندی‌ها</div>
                            <template x-for="cat in categories" :key="cat.id">
                                <a :href="cat.url" class="search-item">
                                    <div class="search-item-image cat-icon">
                                        <template x-if="cat.image">
                                            <img :src="cat.image" :alt="cat.name">
                                        </template>
                                        <template x-if="!cat.image">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6z"/>
                                            </svg>
                                        </template>
                                    </div>
                                    <div class="search-item-info">
                                        <span class="search-item-name" x-text="cat.name"></span>
                                        <span class="search-item-meta" x-text="cat.count + ' محصول'"></span>
                                    </div>
                                </a>
                            </template>
                        </div>
                    </template>

                    <!-- Products -->
                    <template x-if="products.length > 0">
                        <div class="search-section">
                            <div class="search-section-title">محصولات</div>
                            <template x-for="product in products" :key="product.id">
                                <a :href="product.url" class="search-item">
                                    <div class="search-item-image">
                                        <template x-if="product.image">
                                            <img :src="product.image" :alt="product.name">
                                        </template>
                                        <template x-if="!product.image">
                                            <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </template>
                                    </div>
                                    <div class="search-item-info">
                                        <span class="search-item-name" x-text="product.name"></span>
                                        <span class="search-item-price" x-html="product.price"></span>
                                    </div>
                                </a>
                            </template>
                        </div>
                    </template>

                </div>

                <!-- No Results -->
                <div class="search-results search-no-results" x-show="showResults && query.length >= 2 && !loading && categories.length === 0 && products.length === 0" x-cloak>
                    <div class="no-results-text">
                        <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        نتیجه‌ای یافت نشد
                    </div>
                </div>
            </div>
        </div>
    </header>

    <style>
    /* Header Styles */
    .header-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 6px 16px;
    }
    .header-logo {
        display: flex;
        align-items: center;
    }
    .header-logo img {
        height: 44px;
        width: auto;
    }
    .header-logo-text {
        font-size: 18px;
        font-weight: 700;
        color: #1f2937;
    }
    .header-icons {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .header-cart,
    .header-profile {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        background: #f3f4f6;
        border-radius: 12px;
        color: #374151;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
    }
    .header-cart:hover,
    .header-profile:hover {
        background: #e5e7eb;
    }
    .header-cart .ganjeh-cart-count {
        position: absolute;
        top: -4px;
        right: -4px;
        min-width: 18px;
        height: 18px;
        background: var(--color-primary, #4CB050);
        color: white;
        font-size: 10px;
        font-weight: 700;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 4px;
    }
    .header-search {
        padding: 0 16px 12px;
    }

    /* Search Input */
    .ajax-search-input {
        width: 100%;
        background: #f3f4f6;
        border-radius: 12px;
        padding: 12px 16px 12px 44px;
        text-align: right;
        color: #374151;
        font-size: 14px;
        border: 2px solid transparent;
        transition: all 0.2s;
    }
    .ajax-search-input:focus {
        outline: none;
        border-color: var(--color-primary, #4CB050);
        background: white;
    }
    .ajax-search-input::placeholder {
        color: #9ca3af;
    }
    .search-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
    }
    .search-results {
        position: absolute;
        top: calc(100% + 8px);
        left: 0;
        right: 0;
        background: white;
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        z-index: 100;
        max-height: 400px;
        overflow-y: auto;
    }
    .search-section {
        padding: 8px 0;
    }
    .search-section:not(:last-child) {
        border-bottom: 1px solid #f3f4f6;
    }
    .search-section-title {
        font-size: 11px;
        font-weight: 600;
        color: #9ca3af;
        padding: 8px 16px 4px;
    }
    .search-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 16px;
        text-decoration: none;
        transition: background 0.15s;
    }
    .search-item:hover {
        background: #f9fafb;
    }
    .search-item-image {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        background: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        flex-shrink: 0;
    }
    .search-item-image.cat-icon {
        width: 36px;
        height: 36px;
        background: #ecfdf5;
        color: var(--color-primary, #4CB050);
    }
    .search-item-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .search-item-info {
        flex: 1;
        min-width: 0;
    }
    .search-item-name {
        display: block;
        font-size: 13px;
        font-weight: 500;
        color: #374151;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .search-item-meta {
        font-size: 11px;
        color: #9ca3af;
    }
    .search-item-price {
        font-size: 12px;
        font-weight: 600;
        color: var(--color-primary, #4CB050);
    }
    .search-no-results {
        padding: 30px 16px;
        text-align: center;
    }
    .no-results-text {
        font-size: 13px;
        color: #9ca3af;
    }
    [x-cloak] { display: none !important; }
    </style>

    <script>
    function ajaxSearch() {
        return {
            query: '',
            loading: false,
            showResults: false,
            products: [],
            categories: [],

            search() {
                if (this.query.length < 2) {
                    this.showResults = false;
                    this.products = [];
                    this.categories = [];
                    return;
                }

                this.loading = true;
                this.showResults = true;

                fetch(ganjeh.ajax_url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'ganjeh_search',
                        query: this.query
                    })
                })
                .then(r => r.json())
                .then(data => {
                    this.loading = false;
                    if (data.success) {
                        this.products = data.data.products;
                        this.categories = data.data.categories;
                    }
                })
                .catch(() => {
                    this.loading = false;
                });
            },

        }
    }
    </script>
