<?php get_header(); ?>
			<?php //get_sidebar('top'); ?>
			<h2> <?php the_title(); ?> </h2>
			<?php

			if (have_posts()) {
				/* Start the Loop */
				while (have_posts()) {
					the_post();
					the_content();
					//get_template_part( SD_PLUGIN_PATH . '/templates/content.php', get_post_type() );
				}
			} else {
				//theme_404_content();
			}
			?>
			<?php //get_sidebar('bottom'); ?>
<?php get_footer(); ?>