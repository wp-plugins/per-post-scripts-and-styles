=== Per Post Scripts &amp; Styles ===
Author: philipwalton
Contributors: philipwalton, davidosomething
Tags: Javascript, CSS, script, stylesheet, post
Requires at least: 3.0
Tested up to: 3.4.2
Stable tag: 1.2

Properly and safely add specific Javascript and CSS stylesheets to single posts, pages, and custom post types.

== Description ==

Properly and safely add specific Javascript and CSS stylesheets to single posts, pages, and custom post types.

**Features:**

* Dynamic URLs with `%HOME_URL%`, `%SITE_URL%`, and `%THEME_URL%` variables
* Script dependency support as well as script duplication checking
* The option to load scripts in either the header and footer
* Attach scripts to posts, pages, and custom post types
* Load attached scripts and styles not just on that post's page, but on the home page or any other page where that post is displayed.

For complete documentation, visit [philipwalton.com](http://philipwalton.com/2011/09/25/per-post-scripts-and-styles/)

== Installation ==

1. Upload the per-post-scripts-and-styles folder to `/wp-content/plugins/` on your server
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Customize the settings you want from the 'Per Post Scripts &amp; Styles' Settings submenu
4. Add scripts and stylesheet URLs directly to posts from the edit post page.

== Changelog ==

= 1.2 =
* Add support for THEME_URL

= 1.1 =
* Add the ability to include a footer script block
* Add support for WordPress 3.3
* Add support for child themes

= 1.0.1 =
* Fix a bug where meta boxes weren't properly showing up on custom post type edit pages

= 1.0 =
* First publicly released version
