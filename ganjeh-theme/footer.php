    <!-- Footer -->
    <footer class="bg-white border-t border-gray-100 pb-20">
        <div class="px-4 py-6">
            <!-- Copyright -->
            <p class="text-center text-xs text-gray-400">
                <?php printf(__('© %s گنجه مارکت. تمامی حقوق محفوظ است.', 'ganjeh'), date('Y')); ?>
            </p>
        </div>
    </footer>

    <!-- Bottom Navigation (hide on single product pages) -->
    <?php if (!is_singular('product')) : ?>
        <?php get_template_part('template-parts/components/bottom-nav'); ?>
    <?php endif; ?>

</div><!-- #app -->

<?php wp_footer(); ?>
</body>
</html>
