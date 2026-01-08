    <!-- Footer -->
    <footer class="bg-white border-t border-gray-100 pb-20">
        <div class="px-4 py-6">
            <!-- Logo -->
            <div class="flex justify-center mb-6">
                <?php if (has_custom_logo()) : ?>
                    <?php the_custom_logo(); ?>
                <?php else : ?>
                    <img src="<?php echo GANJEH_URI; ?>/assets/images/logo.svg" alt="<?php bloginfo('name'); ?>" class="h-12">
                <?php endif; ?>
            </div>

            <!-- Tagline -->
            <p class="text-center text-gray-500 text-sm mb-6">
                <?php echo get_bloginfo('description'); ?>
            </p>

            <!-- Footer Links -->
            <?php if (has_nav_menu('footer')) : ?>
                <nav class="mb-6">
                    <?php
                    wp_nav_menu([
                        'theme_location' => 'footer',
                        'container'      => false,
                        'menu_class'     => 'flex flex-wrap justify-center gap-4 text-sm text-gray-600',
                        'depth'          => 1,
                    ]);
                    ?>
                </nav>
            <?php endif; ?>

            <!-- Social Links -->
            <div class="flex justify-center gap-4 mb-6">
                <?php
                $socials = [
                    'instagram' => get_option('ganjeh_instagram'),
                    'telegram'  => get_option('ganjeh_telegram'),
                    'whatsapp'  => get_option('ganjeh_whatsapp'),
                ];
                foreach ($socials as $name => $url) :
                    if ($url) :
                ?>
                    <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener" class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center text-gray-600 hover:bg-primary hover:text-white transition-colors">
                        <?php get_template_part('template-parts/icons/social', $name); ?>
                    </a>
                <?php
                    endif;
                endforeach;
                ?>
            </div>

            <!-- Copyright -->
            <p class="text-center text-xs text-gray-400">
                <?php printf(__('© %s گنجه مارکت. تمامی حقوق محفوظ است.', 'ganjeh'), date('Y')); ?>
            </p>
        </div>
    </footer>

    <!-- Bottom Navigation -->
    <?php get_template_part('template-parts/components/bottom-nav'); ?>

</div><!-- #app -->

<?php wp_footer(); ?>
</body>
</html>
