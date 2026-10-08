# Architect - Complete Theme Builder for Elementor

A theme builder for the free version of Elementor. Design your own headers, footers, single and archive layouts and popups, then choose exactly where each one appears.

> **Public beta (1.0.0-beta.3).** Architect is being prepared for WordPress.org. Bug reports, test results on your themes and setups, and feature ideas are very welcome in [Issues](../../issues).

## Key Features

### Theme Builder
- Five template types: **Header**, **Footer**, **Single**, **Archive** and **Popup**, built with the regular Elementor editor.
- Display Location conditions to include or exclude: Entire Site, Front Page, 404, All Singular, Single of any post type, specific pages and posts, All Archives, Blog, Search, Author, Date, post type and taxonomy archives, and specific terms.
- Display Location lives in its own **Conditions** tab in the editor, with a reminder when you publish a template that isn't assigned anywhere, and a shortcut in the Publish menu.
- A **Display Location** column in Templates > Saved Templates, so you can see where every template shows at a glance.
- Single templates can be previewed against any real post with **Preview As** and **Apply & Preview**.
- Page Layout (Default, Full Width, Canvas) and Hide Title settings for Single and Archive templates.

### Popup Builder
- Entrance and exit animations, position, width, height, margin, background, overlay and a fully styled close button.
- Triggers: on page load, after a delay, on scroll, or on click of any CSS selector.
- Show a popup a limited number of times per visitor, prevent page scrolling, close on overlay click or Escape, and close other popups first.
- Add the `archt-popup-close` class to any widget to turn it into a close button.

### Element Display Conditions (Elementor v3 elements)
- Show or hide any widget or container based on custom fields (dynamic tags) or shortcodes.
- Operators: equal, not equal, less than, greater than, empty, not empty, contains, doesn't contain.
- Match all conditions or any of them. Conditional elements stay visible and outlined in the editor.

### Custom CSS and Custom Attributes (Elementor v3 elements)
- Custom CSS per element and per page, with the `selector` keyword.
- Custom attributes per element using `key|value`, with unsafe attributes filtered out.

### Widgets
- **Single:** Post Title, Featured Image, Post Content, Post Excerpt, Post Meta, Post Navigation.
- **Archive:** Archive Title, Archive Description, Posts Loop, Archive Pagination.
- **Navigation Menu:** horizontal, vertical or expanded (sidebar) layout, dropdowns, hover effects, animations and schema markup.

## Available Dynamic Tags
1. **Site:** Site Title, Site Tagline, Site Logo, Site URL.
2. **Archive:** Archive Title, Archive Description, Archive URL.
3. **Post:** Post Title, Post URL, Post Date, Post Excerpt, Post Content, Post Featured Image, Post Terms, Pages URL.
4. **Custom data:** Custom Field, Image Custom Field, Repeater, Shortcode, Author Meta, User Meta, Taxonomy Meta.

The post and custom data tags are shared with [Better Post & Filter Widgets for Elementor](https://wordpress.org/plugins/better-post-filter-widgets-for-elementor/). When both plugins are active, they are registered only once.

## Compatibility
- **Elementor v4:** atomic widgets and Global Classes render correctly inside every template, and the widgets and dynamic tags work in both v3 and v4. Display Conditions, Custom CSS and Custom Attributes are available on v3 elements only for now.
- **Elementor Pro:** when Pro is active, Architect's theme builder, popups, Custom CSS and Custom Attributes step aside, and only the widgets and dynamic tags Pro doesn't have remain.
- **Translation plugins:** WPML and Polylang.
- **Themes:** on classic themes, only the theme's site header and footer are swapped for your templates, so its page wrappers and layout stay intact. The `archt/header/before`, `archt/header/after`, `archt/footer/before` and `archt/footer/after` hooks give themes and plugins a place to add their markup.

## Crafted for Seamless Elementor Integration
- Blends into Elementor's native interface and uses its own controls, icons and resources.
- No branding, no upsells and no extra admin pages.
- Lightweight, with no external dependencies.

## Requirements
- WordPress 6.2 or later
- PHP 7.4 or later
- Elementor (free) 3.0 or later

## Installation
1. Download the latest release from [Releases](../../releases), or clone this repository into `wp-content/plugins/`.
2. Activate **Architect - Complete Theme Builder for Elementor** in Plugins.
3. Go to Templates > Saved Templates > Add New, and choose Header, Footer, Single, Archive or Popup.
4. Build the template, then set where it appears in the editor's **Conditions** tab.

## Documentation

Guides and the full list of settings are in the [documentation](https://wpsmartwidgets.com/doc/architect-theme-builder-for-elementor/).

## Contributing

Bug reports, test results on your themes and setups, translations and pull requests are welcome. Open an [issue](../../issues) to discuss an idea first. Features that aren't in the plugin yet, such as WooCommerce widgets, are good candidates for contributions.

## License

This plugin is licensed under the [GPLv3 or later](https://www.gnu.org/licenses/gpl-3.0.html) license.

## Contact

Contributor: [WP Smart Widgets](https://wpsmartwidgets.com)
