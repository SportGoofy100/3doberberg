<?php
get_header(); ?>
<main id="primary" class="site-main">
<h1>Alle Werke</h1>
<?php if ( have_posts() ) : ?>
<div class="werke-grid">
<?php while ( have_posts() ) : the_post(); ?>
<article <?php post_class('werk-item'); ?>>
    <a href="<?php the_permalink(); ?>">
        <?php if ( has_post_thumbnail() ) the_post_thumbnail('medium'); ?>
        <h2><?php the_title(); ?></h2>
    </a>
</article>
<?php endwhile; ?>
</div>
<?php the_posts_pagination(); ?>
<?php else : ?><p>Keine Werke gefunden.</p><?php endif; ?>
</main>
<?php get_footer(); ?>