=== Plugin Name ===
Contributors: kadirmalak
Donate link: http://example.com/
Tags: slider, nivo-slider, nivo, dvs
Requires at least: 3.5
Tested up to: 3.5
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create sliders using Nivo-Slider (http://nivo.dev7studios.com/)

== Description ==

Uses custom post types to manage slider items, shortcode to insert.

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload `dvs-slider` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. (Optionally) Create image size for slider: Place `add_image_size('for_slider', 600, 400, true);` in your template's `functions.php` to create an image size for slider. Name it whatever you want. You may also change width and height. **Note:** To enable post thumbnails, the current theme must include `<?php add_theme_support( 'post-thumbnails' ); ?>`
1. From Settings -> Dvs Slider, enter image size name. This image size will be used while fetching slider images.
1. Using Dvs Slider Images menu, create slider items. Don't forget to enter *caption*, *link*, *order* and *featured image*.
1. Use shortcode `[dvs_slider]` in your page/post/etc to insert slider.
1. Published slider images will be shown in slider in order.

== Frequently Asked Questions ==

= Does it really need Wordpress 3.5 to work? =

I don't know, I haven't tested it. Probably it'll work with 3.x versions.

= How can i modify initial slider options etc... ? =

`js/init.js` -> you may change nivoSlider default options
`partials/slider.php` -> actual slider template

== Screenshots ==

1. Sample result
2. Slider image management
3. Inserting new slider image
4. Using slider shortcode

== Changelog ==

= 1.0 =
* Initial commit

== Upgrade Notice ==

= 1.0 =
Initial commit.

