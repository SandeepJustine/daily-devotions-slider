=== Daily Devotions Slider ===
Contributors: Joseph Justine
Donate link: https://yourwebsite.com/donate
Tags: devotion, bible, church, slider, daily devotion, scripture
Requires at least: 5.6
Tested up to: 6.8
Stable tag: 1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A beautiful slider to display daily devotions with flexible scheduling options.

== Description ==

The Daily Devotions Slider plugin allows churches and religious organizations to:

* Create and manage daily devotions with Bible verses and authors
* Set flexible schedules (weekly, monthly, or yearly)
* Display devotions in an elegant, responsive slider
* Navigate between devotions with smooth transitions
* Show either all devotions or only today's scheduled items

== Features ==

* **Custom Post Type** for devotions with:
  - Bible verse field
  - Author field
  - Flexible scheduling system
* **Beautiful Slider Display** with:
  - Smooth CSS transitions
  - Responsive design
  - Multiple navigation options
* **Flexible Scheduling**:
  - Weekly (every Monday, Tuesday, etc.)
  - Monthly (specific day each month)
  - Yearly (specific date each year)
* **Multiple Display Options**:
  - Auto-playing slider
  - Navigation arrows (customizable position)
  - Dot indicators
* **Admin Features**:
  - Clean interface
  - Schedule information in list view
  - Quick editing

== Installation ==

1. Upload the `daily-devotions-slider` folder to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Devotions → Add New to create your first devotion
4. Use the shortcode `[devotion_slider]` in posts/pages or widgets

== Frequently Asked Questions ==

= How do I change the slider appearance? =
You can either:
1. Add custom CSS to your theme
2. Use the built-in shortcode parameters
3. Override the templates in your theme

= Can I show devotions from specific categories? =
Yes! Use the `category` parameter in the shortcode:
`[devotion_slider category="weekly"]`

= How do I make the slider auto-play? =
Use the `autoplay` and `interval` parameters:
`[devotion_slider autoplay="true" interval="7000"]`

= Where are the navigation buttons positioned? =
By default, they appear slightly over the edges of the content. You can adjust this with CSS.

== Screenshots ==

1. Devotion editor with scheduling options
2. Frontend slider display with navigation
3. Admin list view showing schedule information
4. Mobile-responsive design example

== Changelog ==

= 1.0 =
* Initial release with all core features

== Upgrade Notice ==

1.0 - First stable release. No upgrade needed for new installations.

== Shortcode Parameters ==

* `autoplay` - Enable auto-playing (true/false)
* `interval` - Auto-play interval in milliseconds (default: 5000)
* `show_nav` - Show navigation arrows (true/false)
* `show_dots` - Show dot indicators (true/false)
* `category` - Filter by category slug
* `mode` - Display mode ('all' or 'scheduled')

Example:
`[devotion_slider autoplay="true" interval="7000" show_dots="false"]`

== Custom CSS Classes ==

You can target these elements for custom styling:
* `.devotions-slider` - Main container
* `.devotion-slide` - Individual devotion
* `.slider-prev` - Previous button
* `.slider-next` - Next button
* `.slider-dots` - Dot navigation container
* `.schedule-badge` - Schedule indicator badge

== Roadmap ==

* Devotion categories and tags
* Email subscriptions
* PDF export functionality
* REST API endpoints
* Bulk import/export

== Contributing ==

Contributions are welcome! Please submit pull requests to our GitHub repository:
https://github.com/SandeepJustine/daily-devotions-slider
