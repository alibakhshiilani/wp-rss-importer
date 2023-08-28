<?php
add_action('admin_menu', 'wp_rss_importer_menu');

function wp_rss_importer_menu() {
    add_menu_page(
        'WP RSS Importer Settings',
        'RSS Importer',
        'manage_options',
        'wp-rss-importer-settings',
        'wp_rss_importer_settings_page'
    );
}

function wp_rss_importer_settings_page() {
    if (isset($_POST['save_feeds'])) {
        update_option('wp_rss_feeds', $_POST['feed_urls']);
    }

    $feed_urls = get_option('wp_rss_feeds', array());
    ?>
    <div class="wrap">
        <h2>WP RSS Importer Settings</h2>
        <form method="post" action="">
            <label for="feed_urls">Feed URLs (one URL per line):</label><br>
            <textarea id="feed_urls" name="feed_urls" rows="5" cols="50"><?php echo implode("\n", $feed_urls); ?></textarea><br>
            <input type="submit" name="save_feeds" class="button-primary" value="Save Feeds">
        </form>
    </div>
    <?php
}

add_action('init', 'wp_rss_importer_init');

function wp_rss_importer_init() {
    if (!wp_next_scheduled('wp_rss_importer_cron')) {
        wp_schedule_event(time(), 'hourly', 'wp_rss_importer_cron');
    }
    add_action('wp_rss_importer_cron', 'import_rss_posts');
}

function import_rss_posts() {
    $feed_urls = get_option('wp_rss_feeds', array());

    foreach ($feed_urls as $feed_url) {
        $rss = fetch_rss($feed_url);
        
        foreach ($rss->items as $item) {
            $post_data = array(
                'post_title' => $item['title'],
                'post_content' => $item['description'],
                'post_status' => 'publish',
            );
            $post_id = wp_insert_post($post_data);
            add_post_meta($post_id, 'wp_rss_importer_post_link', $item['link'], true);
        }
    }
}
