<?php
/**
 * Main template file
 *
 * @package Ganjeh
 */

get_header();
?>

<main id="main-content" class="pb-20">
    <?php if (have_posts()) : ?>
        <div class="container">
            <?php while (have_posts()) : the_post(); ?>
                <article <?php post_class('card mb-4'); ?>>
                    <?php if (has_post_thumbnail()) : ?>
                        <div class="card-image">
                            <?php the_post_thumbnail('ganjeh-product-thumb'); ?>
                        </div>
                    <?php endif; ?>
                    <div class="card-content p-4">
                        <h2 class="text-lg font-bold mb-2">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h2>
                        <?php the_excerpt(); ?>
                    </div>
                </article>
            <?php endwhile; ?>

            <?php the_posts_pagination(); ?>
        </div>
    <?php else : ?>
        <div class="container text-center py-12">
            <p class="text-gray-500"><?php _e('محتوایی یافت نشد.', 'ganjeh'); ?></p>
        </div>
    <?php endif; ?>
</main>

<?php
get_footer();
