Post Extractor WordPress Plugin
===============================

The Post Extractor is a WordPress plugin designed to extract the data from your WordPress posts and convert them into .mdx format.

Features
--------

*   Extracts post data including title, content, date, slug, and author information.
*   Converts extracted post data into .mdx format.
*   Exports .mdx files to a specified directory.
*   Automatically detects the fields needed to provide standard content WordPress sites would want to show visitors; including title, content, date, slug, and author information.
*   Allows you to specify the directory where the .mdx files will be saved.
*   Formats all data for Frontmatter; including coveted meta data and schema often added by plugins for SEO that is parsed incorrectly or lost in most api recipes and no-code builders

Installation
------------

1.  Download the plugin files and upload them to your `/wp-content/plugins/` directory.
2.  Activate the plugin through the 'Plugins' screen in WordPress.

Usage
-----

1.  After the plugin has been activated, go to the settings page by clicking on 'Post Extractor' under the 'Settings' menu in your WordPress admin dashboard.
2.  In the settings page, enter the full path of the directory where you want the .mdx files to be saved in the 'Output Path' field and click 'Save Changes'.
3.  The plugin will automatically generate .mdx files for all of your posts and save them in the specified directory.

Note
----

*   The specified directory must be writable by WordPress for the plugin to work properly.
*   The plugin extracts and exports data for all posts on activation. If you add or update posts, you will need to deactivate and reactivate the plugin to export the new or updated posts.
