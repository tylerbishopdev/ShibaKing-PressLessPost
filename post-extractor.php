<?php
/**
 * Plugin Name: $hiba-UnPressPoster
 * Plugin URI: https://tyler.farm
 * Description: This plugin extracts specified fields from all posts and saves them as .mdx files in a compressed folder.
 * Version: 1.0
 * Author: $hibaking
 * Author URI: https://tyler.farm
 **/

function post_extractor_menu()
{
    add_options_page(
        'Post Extractor',
        'Post Extractor',
        'manage_options',
        'post-extractor',
        'post_extractor_options_page'
    );
}
add_action('admin_menu', 'post_extractor_menu');

function post_extractor_options_page()
{
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    ?>
    <div class="wrap">
        <h1>Post Extractor</h1>
        <form method="post" action="options-general.php?page=post-extractor">
            <?php wp_nonce_field('post_extractor_export', 'post_extractor_nonce'); ?>
            <p>Click the button below to export all posts as .mdx files in a compressed folder.</p>
            <input type="submit" name="submit" id="submit" class="button button-primary" value="We'll See">
        </form>
    </div>
    <?php

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && wp_verify_nonce($_POST['post_extractor_nonce'], 'post_extractor_export')) {
        post_extractor_export_posts();
    }
}

function post_extractor_export_posts()
{
    // Create a unique temporary directory for this export
    $upload_dir = wp_upload_dir();
    $temp_dir = trailingslashit($upload_dir['basedir']) . 'post_extractor_exports/' . uniqid();
    wp_mkdir_p($temp_dir);

    // Query the posts
    $args = array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => -1,
    );
    $query = new WP_Query($args);

    // Loop through the posts
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();

            // Extract the desired data

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

            // ...

            // Write the .mdx file
            $filename = trailingslashit($temp_dir) . get_post_field('post_name') . '.mdx';
            file_put_contents($filename, $mdx_content);
        }
    }

    // Create the zip file
    $zip_file = trailingslashit($upload_dir['basedir']) . 'post_extractor_exports/export_' . time() . '.zip';
    $zip = new ZipArchive();
    if ($zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($temp_dir), RecursiveIteratorIterator::LEAVES_ONLY);
        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($temp_dir) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
        $zip->close();
    }

    // Delete the temporary directory
    post_extractor_delete_temp_dir($temp_dir);

    // Output the zip file to the browser
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . basename($zip_file));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($zip_file));
    readfile($zip_file);

    // Delete the zip file
    unlink($zip_file);

    // Terminate the script to avoid the admin footer being appended to the file download
    die();
}

function post_extractor_delete_temp_dir($dir)
{
    if (!file_exists($dir)) {
        return true;
    }

    if (!is_dir($dir)) {
        return unlink($dir);
    }

    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }

        if (!post_extractor_delete_temp_dir($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }