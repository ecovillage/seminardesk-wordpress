<?php
/**
 * The template for single post of CPT sd_cpt_facilitator
 * 
 * @package SeminardeskPlugin
 */

use Inc\Utils\TemplateUtils as Utils;

get_header();
?>
<main id="site-content" role="main">
    <?php
    if (have_posts()) {
        while (have_posts()) {
            the_post();
            ?>
            <header class="entry-header has-text-align-center">
                <div class="entry-header-inner section-inner medium">
                    <?php 
                    the_title( '<h1 class="archive-title">', '</h1>' );
                    ?>
                </div>
            </header>
            <div class="post-meta-wrapper post-meta-single post-meta-single-top">
                <?php
                echo !empty( $post->sd_data['pictureUrl'] ) ? '<p>' . Utils::get_img_remote($post->sd_data['pictureUrl'], '100') . '</p>' : null;
                $about = Utils::get_value_by_language( $post->sd_data['about'] );
                echo !empty($about) ? '<p>' . $about . '</p>' : null;
                ?>
            </div>
            <?php
        }
    } else {
        ?>
        <div class="entry-header-inner section-inner small has-text-align-center">
            <h5>
                <?php
                _e('<strong>Sorry, event does not exist.</strong>', 'seminardesk');
                ?>
            </h5>
            <br>
        </div>
        <?php
    }
    ?>

</main><!-- #site-content -->

<?php
get_footer();