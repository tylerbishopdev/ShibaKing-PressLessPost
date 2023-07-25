<?php
/*
Plugin Name: $hibaKing: PressLesssPost 
Description: A plugin to extract post data and create .mdx files formatted for use w/ Frontmatter commonly needed or used for SEO and custom taxonmoies needed to render the standard WordPress content used by sites; including the post title, author, date, featured image, and content.
Version: 1.0
Author: $hibaKing
*/

function post_extractor_activate() {
    add_option('post_extractor_output_path', '');
}
register_activation_hook(__FILE__, 'post_extractor_activate');

function post_extractor_deactivate() {
    delete_option('post_extractor_output_path');
}
register_deactivation_hook(__FILE__, 'post_extractor_deactivate');

function post_extractor_admin_menu() {
    add_options_page('Post Extractor Settings', 'Post Extractor', 'manage_options', 'post-extractor', 'post_extractor_settings_page');
}
add_action('admin_menu', 'post_extractor_admin_menu');

function post_extractor_settings_page() {
    ?>
    <div class="wrap">
        <h1>Post Extractor Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('post-extractor');
            do_settings_sections('post-extractor');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function post_extractor_admin_init() {
    register_setting('post-extractor', 'post_extractor_output_path');

    add_settings_section('post-extractor-settings', 'Settings', null, 'post-extractor');

    add_settings_field('post_extractor_output_path', 'Output Path', 'post_extractor_output_path_callback', 'post-extractor', 'post-extractor-settings');
}
add_action('admin_init', 'post_extractor_admin_init');

function post_extractor_output_path_callback() {
    $output_path = esc_attr(get_option('post_extractor_output_path'));
    echo "<input type='text' name='post_extractor_output_path' value='$output_path' />";
}

function post_extractor_export_posts() {
    // Query for all posts
    $args = array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => -1,
    );
   
    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();

            $id = get_the_ID();
            $date = get_the_date('c');
            $date_gmt = get_the_date('c');
            $guid = get_the_guid();
            $modified = get_the_modified_date('c');
            $modified_gmt = get_the_modified_date('c');
            $slug = get_post_field('post_name');
            $status = get_post_status();
            $type = get_post_type();
            $link = get_the_permalink();
            $title = get_the_title();
            $content = apply_filters('the_content', get_the_content());
            $excerpt = get_the_excerpt();
            $featured_image = get_the_post_thumbnail_url();
            $category = get_the_category();
            $author = get_the_author_meta('ID');
            $author_name = get_the_author_meta('display_name');
            $author_description = get_the_author_meta('description');
            $author_avatar_url = get_avatar_url($author);

            // Start the mdx content with the frontmatter
            $mdx_content = "---\n";
            $mdx_content .= "id: \"$id\"\n";
            $mdx_content .= "date: \"$date\"\n";
            $mdx_content .= "date_gmt: \"$date_gmt\"\n";
            $mdx_content .= "guid: \"$guid\"\n";
            $mdx_content .= "modified: \"$modified\"\n";
            $mdx_content .= "modified_gmt: \"$modified_gmt\"\n";
            $mdx_content .= "slug: \"$slug\"\n";
            $mdx_content .= "status: \"$status\"\n";
            $mdx_content .= "type: \"$type\"\n";
            $mdx_content .= "link: \"$link\"\n";
            $mdx_content .= "title: \"$title\"\n";
            $mdx_content .= "excerpt: \"$excerpt\"\n";
            $mdx_content .= "featured_image: \"$featured_image\"\n";
            $mdx_content .= "category: \"" . $category[0]->cat_name . "\"\n";
            $mdx_content .= "author: \"$author_name\"\n";
            $mdx_content .= "author_description: \"$author_description\"\n";
            $mdx_content .= "author_avatar_url: \"$author_avatar_url\"\n";
            $mdx_content .= "---\n\n";
            $mdx_content .= $content;

            // Write to file
            $output_path = esc_attr(get_option('post_extractor_output_path'));
            $filename = $output_path . '/' . $slug . '.mdx';
            file_put_contents($filename, $mdx_content);
        }
        wp_reset_postdata();
    }
}
add_action('admin_init', 'post_extractor_export_posts');
?>