=== Architect - Complete Theme Builder for Elementor ===
Contributors: nomade123456
Donate link: https://wpsmartwidgets.com/donate/
Tags: elementor, theme builder, header, footer, popup
Requires at least: 6.2
Tested up to: 7.1
Requires PHP: 7.4
Requires Plugins: elementor
Stable tag: 1.0.0-beta.3
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

A theme builder for the free version of Elementor. Build headers, footers, single and archive templates and popups, and choose where each one appears.

== Description ==

Architect adds a theme builder to the free version of Elementor. Design your own headers, footers, single and archive layouts and popups with the regular Elementor editor, then choose exactly where each one appears on your site.

It works without Elementor Pro. If Elementor Pro is active, Architect steps aside and only its widgets and dynamic tags remain (see below).

= Theme Builder =

* Five template types: **Header**, **Footer**, **Single**, **Archive** and **Popup**.
* **Display Location** conditions, to include or exclude: Entire Site, Front Page, 404 Page, All Singular, Single of any post type, specific pages and posts, All Archives, Blog Archive, Search Results, Author Archive, Date Archive, post type archives, taxonomy archives and specific terms.
* Display Location has its own tab in the editor, a shortcut in the Publish menu, and a reminder when you publish a template that isn't assigned anywhere.
* A **Display Location** column in Templates > Saved Templates shows where every template appears.
* Preview a Single template against any real post with **Preview As** and **Apply & Preview**.
* Page Layout (Default, Full Width, Canvas) and Hide Title settings for Single and Archive templates.
* Templates follow your translation plugin: WPML and Polylang are supported.

= Popup Builder =

* Entrance and exit animations, position, width, height, margin, background, overlay color and a styled close button.
* Triggers: on page load, after a delay, on scroll to a percentage, or on click of any CSS selector.
* Limit how many times a popup is shown to each visitor's browser.
* Prevent page scrolling, close on overlay click or Escape, and close other popups first.
* Add the `archt-popup-close` class to any widget to turn it into a close button.

= Element Display Conditions =

Available on Elementor v3 elements.

* Show or hide any widget or container based on a custom field or a shortcode.
* Operators: equal, not equal, less than, greater than, empty, not empty, contains and doesn't contain.
* Match all conditions or any of them. Conditional elements stay visible, and outlined, in the editor.

= Custom CSS and Custom Attributes =

Available on Elementor v3 elements.

* Custom CSS in the Advanced tab of your elements, with the `selector` keyword.
* Custom attributes per element, written as `key|value`.

= Widgets =

* **Single:** Post Title, Featured Image, Post Content, Post Excerpt, Post Meta, Post Navigation.
* **Archive:** Archive Title, Archive Description, Posts Loop, Archive Pagination.
* **Navigation Menu:** horizontal, vertical or expanded (sidebar) layout, dropdowns, hover effects, animations and optional schema markup.

= Dynamic Tags =

* **Site:** Site Title, Site Tagline, Site Logo, Site URL.
* **Archive:** Archive Title, Archive Description, Archive URL.
* **Post:** Post Title, Post URL, Post Date, Post Excerpt, Post Content, Post Featured Image, Post Terms, Pages URL.
* **Custom data:** Custom Field, Image Custom Field, Repeater, Shortcode, Author Meta, User Meta, Taxonomy Meta.

The post and custom data tags are shared with [Better Post & Filter Widgets for Elementor](https://wordpress.org/plugins/better-post-filter-widgets-for-elementor/). When both plugins are active, each tag is registered only once.

= Compatibility =

* **Elementor v4:** atomic widgets and Global Classes render correctly inside every template, and the widgets and dynamic tags work in both v3 and v4. Display Conditions, Custom CSS and Custom Attributes are available on v3 elements only for now.
* **Elementor Pro:** when Pro is active, the theme builder, popups, Custom CSS and Custom Attributes step aside so nothing conflicts. The widgets and dynamic tags Pro doesn't have remain available.
* **Themes:** on classic themes, only the theme's site header and footer are swapped for your templates, so its page wrappers and layout stay intact.

= Developers =

Action hooks `archt/header/before`, `archt/header/after`, `archt/footer/before` and `archt/footer/after` let themes and plugins add markup around your header and footer. Filters are available for the blocked custom attributes (`archt/element/attributes/black_list`), the archive title (`archt/core_elements/get_the_archive_title`) and the taxonomies offered by the Post Terms tag (`archt_taxonomy_args`). The Navigation Menu supports the standard WordPress menu filters, such as `nav_menu_link_attributes` and `nav_menu_css_class`.

= Documentation =

Guides and the full list of settings are on the [Architect documentation](https://wpsmartwidgets.com/doc/architect-theme-builder-for-elementor/).

= Contributing =

Architect is open source and is developed in the open on [GitHub](https://github.com/dara-mtl/architect-complete-theme-builder-for-elementor). Bug reports, test results on your themes and setups, translations and code contributions are welcome, and more contributors mean more can be built. Features that are not in the plugin yet, such as WooCommerce widgets, are good candidates for contributions.

== Installation ==

1. Make sure Elementor is installed and active.
2. Go to Plugins > Add New, search for "Architect - Complete Theme Builder for Elementor", then click Install Now and Activate. To install a downloaded copy, use Plugins > Add New > Upload Plugin.
3. Go to Templates > Saved Templates > Add New and choose Header, Footer, Single, Archive or Popup.
4. Build the template with the Elementor editor.
5. Open the Conditions tab in the editor panel and set Display On under Display Location.
6. Publish. Visit your site to see it.

== Frequently Asked Questions ==

= Does Architect require Elementor Pro? =

No. Architect works with the free version of Elementor 3.0 or later.

= What happens if I also have Elementor Pro? =

Elementor Pro has its own theme builder, so Architect steps aside. The theme builder, popups, Custom CSS and Custom Attributes come from Elementor Pro. Architect's widgets and dynamic tags stay available. A notice in the WordPress admin explains this.

= Can I replace my theme's header and footer? =

Yes. Create a Header or Footer template and set its Display Location, for example Entire Site. Where no header or footer template applies, your theme's own is used.

= What if two templates match the same page? =

Only one header, footer and Single or Archive template is used per page. If two templates of the same type match, the most recently created one is used. Use Do Not Display On to keep templates from overlapping. Popups are different: every popup whose Display Location matches will be available on the page.

= Does it support custom post types? =

Yes. You can build a Single template for any public post type, and archive templates for post type archives and taxonomy archives.

= Does it support WooCommerce? =

Not officially. Architect doesn't include WooCommerce widgets, such as product price, add to cart or product gallery, and WooCommerce pages are not tested. Because Single templates work for any public post type, you may be able to build a basic product layout with the general widgets and dynamic tags, but it isn't supported.

WooCommerce support is something the maintainers can't take on alone. If you would like to help, see Contributing above.

= Can I contribute a feature? =

Yes. Architect is open source, and contributions are welcome on [GitHub](https://github.com/dara-mtl/architect-complete-theme-builder-for-elementor). Open an issue to discuss an idea first, or send a pull request.

= Does it work with Elementor v4? =

Yes. Atomic widgets and Global Classes render in every template, and Architect's widgets and dynamic tags work in v4. Display Conditions, Custom CSS and Custom Attributes are available on v3 elements only for now.

= Which translation plugins are supported? =

WPML and Polylang. Templates assigned to a page or term show on its translation.

= How do I add custom CSS? =

It works the same way as in Elementor Pro. Open any widget or container in Elementor, go to its Advanced tab and find the Custom CSS section. Use the `selector` keyword to target the element.

= How do I show a popup only a few times? =

In the popup template's settings, open Triggers and set Show X Times Per User. The popup counts each time it opens, using the visitor's browser storage. The count is per browser, so the same person on another browser or device, or in a private window, is counted separately, and clearing site data resets it. If the browser blocks storage, the limit is skipped. Set it to 0 for unlimited.

= Where do I get help? =

See the [documentation](https://wpsmartwidgets.com/doc/architect-theme-builder-for-elementor/), or ask in the plugin's support forum.

== Screenshots ==

1. Choosing where a template appears, in the Conditions tab.
2. Building a header with the Navigation Menu widget.
3. A Single template, previewed with a real post.
4. An archive template with the Posts Loop widget.
5. The popup triggers and animation settings.
6. Display Location column in Saved Templates.

== Changelog ==

= 1.0.0 =
* First public release.
* Header, Footer, Single, Archive and Popup templates with Display Location conditions, in their own editor tab.
* Reminder when publishing a template with no Display Location, and a Display Location shortcut in the Publish menu.
* Display Location column in Templates > Saved Templates.
* Popup builder with animations, position, size, margin, background, overlay, close button, and load, delay, scroll, click and frequency triggers.
* Element display conditions based on custom fields or shortcodes, outlined in the editor.
* Custom CSS and Custom Attributes for Elementor v3 elements.
* Single and Archive widgets, a Navigation Menu widget with horizontal, vertical and expanded layouts, and Site, Archive and Post dynamic tags.
* Elementor v4 atomic styles and Global Classes render in every template.
* Action hooks `archt/header/before`, `archt/header/after`, `archt/footer/before` and `archt/footer/after`.
* Compatible with WPML and Polylang.

== Upgrade Notice ==

= 1.0.0 =
First public release of Architect - Complete Theme Builder for Elementor.
