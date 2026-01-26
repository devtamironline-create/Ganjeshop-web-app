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
    </style>
</head>
<body <?php body_class('bg-gray-100 font-vazir'); ?> x-data="{ mobileMenu: false, searchOpen: false }">

<!-- App Container - Mobile-first max-width (515px = 15% larger than 448px) -->
<div id="app" class="app-container mx-auto bg-white min-h-screen relative shadow-xl">

    <!-- Promo Banner -->
    <?php get_template_part('template-parts/header/promo-banner'); ?>

    <!-- Main Header -->
    <header class="sticky top-0 z-40 bg-white">
        <!-- Logo & Cart Row -->
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
            <!-- Logo (Right Side in RTL) -->
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
            <a href="<?php echo home_url('/'); ?>" class="flex items-center">
                <?php if ($logo_url) : ?>
                    <img src="<?php echo esc_url($logo_url); ?>" alt="<?php bloginfo('name'); ?>" style="width: <?php echo esc_attr($logo_width); ?>px; height: auto;">
                <?php else : ?>
                    <span class="text-lg font-bold text-secondary"><?php bloginfo('name'); ?></span>
                <?php endif; ?>
            </a>

            <!-- Cart Icon (Left Side in RTL) -->
            <a href="<?php echo wc_get_cart_url(); ?>" class="relative p-2">
                <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <?php if (WC()->cart && WC()->cart->get_cart_contents_count() > 0) : ?>
                    <span class="ganjeh-cart-count absolute -top-1 -left-1 bg-primary text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold">
                        <?php echo WC()->cart->get_cart_contents_count(); ?>
                    </span>
                <?php endif; ?>
            </a>
        </div>

        <!-- Search Bar -->
        <div class="px-4 py-3">
            <form role="search" method="get" action="<?php echo home_url('/'); ?>" class="relative flex items-center">
                <input
                    type="search"
                    name="s"
                    placeholder="<?php _e('جستجو در گنجه مارکت', 'ganjeh'); ?>"
                    value="<?php echo get_search_query(); ?>"
                    class="w-full bg-gray-100 rounded-xl py-3 pr-4 pl-12 text-right text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20"
                >
                <input type="hidden" name="post_type" value="product">
                <button type="submit" class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-primary transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>
            </form>
        </div>
    </header>
