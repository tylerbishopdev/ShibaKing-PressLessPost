<?php
/*
Plugin Name: Post Extractor
Description: A plugin to extract post data and create .mdx files
Version: 1.0
Author: Your Name
*/

function post_extractor_activate() {
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

            // Extract post data
            $id = get_the_ID();
            $date = get_the_date('c', $id);
            $guid = get_the_guid($id);
            $modified = get_the_modified_date('c', $id);
            $slug = get_post_field('post_name', $id);
            $status = get_post_status($id);
            $type = get_post_type($id);
            $link = get_permalink($id);
            $title = get_the_title($id);
            $content = apply_filters('the_content', get_post_field('post_content', $id));

            // The author's data
            $author_id = get_post_field('post_author', $id);
            $author_data = get_userdata($author_id);
            $author_name = $author_data->display_name;
            $author_url = get_author_posts_url($author_id);
            $author_email = $author_data->user_email;
            $author_website = $author_data->user_url;
            $author_description = get_the_author_meta('description', $author_id);

            // Featured image
            $featured_image = get_the_post_thumbnail_url($id, 'full');

            // Post excerpt
            $excerpt = get_the_excerpt($id);

            // Build frontmatter and content
            $mdx_content = "---\n";
            $mdx_content .= "id: \"$id\"\n";
            $mdx_content .= "date: \"$date\"\n";
            $mdx_content .= "guid: \"$guid\"\n";
            $mdx_content .= "modified: \"$modified\"\n";
            $mdx_content .= "slug: \"$slug\"\n";
            $mdx_content .= "status: \"$status\"\n";
            $mdx_content .= "type: \"$type\"\n";
            $mdx_content .= "link: \"$link\"\n";
            $mdx_content .= "title: \"$title\"\n";
            $mdx_content .= "author: \"$author_name\"\n";
            $mdx_content .= "author_url: \"$author_url\"\n";
            $mdx_content .= "author_email: \"$author_email\"\n";
            $mdx_content .= "author_website: \"$author_website\"\n";
            $mdx_content .= "author_description: \"$author_description\"\n";
            $mdx_content .= "featured_image: \"$featured_image\"\n";
            $mdx_content .= "description: \"$excerpt\"\n";
            $mdx_content .= "---\n\n";
            $mdx_content .= $content;

            // Write to file
            $filename = '/Users/Shared/WordPress Local/mdx' .
             $slug . '.mdx';
            file_put_contents($filename, $mdx_content);
        }
        wp_reset_postdata();
    }
}
register_activation_hook(__FILE__, 'post_extractor_activate');
