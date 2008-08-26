=== Plugin Name ===
Contributors: denishua
Tags: Feed,RSS,Friend
Donate link: http://fairyfish.net/donate/
Requires at least: 2.0
Tested up to: 2.61
Stable tag: 0.3

Top Friends is a WordPress blogroll enhancement plugin. The plugin will fetch your friends¡¯ feeds, and then display the feed¡¯s name and status icon base on last update time and latest two posts of the feed.

== Description ==
<p>Top Friends is a WordPress blogroll enhancement plugin. The plugin will fetch your friends' feeds, and then display the feed's name and status icon base on last update time and latest two posts of the feed.</p>

== Installation ==

1. Upload the folder advanced-post-image to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. There is no admin panel for the plugin currently. So you should manually to edit the source code to configuration the plugin.

`$google_ajax_feed_apikey` is the API Key, you can apply it here.
`$top_friends_feeds` is the feed array that you want to fetch. Please input your feeds base on PHP array syntax.

1. Place `<?php top_friends(); ?>` in your templates