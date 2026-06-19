=== AG Starter Companion ===

Contributors: adminag
Tags: starter sites, demo content, one click import, theme setup
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.13.0
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Companion plugin for the AG Starter themes (Restaurant, Artisan, Coach, Avocat). One-click import of the pages, menu and settings for a ready-to-use website.

== Description ==

AG Starter Companion is the official companion plugin for the free AG Starter themes published by AGthemes (Alliance Group). It provides a feature that themes alone are not allowed to offer under the WordPress.org guidelines: automated demo content import.

In one click, the plugin creates for you:

* The essential pages of the active theme (Home + 4 sector pages tailored to the theme: menu/booking for a restaurant, services/portfolio for an artisan, programs/testimonials for a coach).
* A primary menu containing all of these pages, automatically assigned to the theme "primary" location.
* The "static front page" setting using the "Home" page (which triggers the rendering of the theme front-page.php template).
* Pretty permalinks in the /%postname%/ format if they were not already enabled.

Everything runs 100% locally: no internet connection is required, no external API call, no data sent anywhere.

= Supported themes =

* AG Starter Restaurant (https://wordpress.org/themes/ag-starter-restaurant/)
* AG Starter Artisan (https://wordpress.org/themes/ag-starter-artisan/)
* AG Starter Coach (https://wordpress.org/themes/ag-starter-coach/)
* AG Starter Avocat (https://wordpress.org/themes/ag-starter-avocat/) — also creates 6 areas of expertise through the ag_domaine custom post type

The plugin automatically detects which AG Starter theme is active and adapts the imported content accordingly. If no AG Starter theme is active, the plugin stays dormant and displays nothing.

= Who is it for? =

This plugin is designed for people who do not code and who want a ready-to-use website a few minutes after installing their theme. Perfect for:

* A restaurant owner who wants to launch an online storefront without an agency
* An artisan who wants to display their services and service areas
* A coach or consultant who wants to present their offers and take appointments

= Features =

* One-click import (a single button under Appearance > AG Setup)
* Automatic detection of the active AG Starter theme
* Reset available at any time
* No SQL table creation
* GPL v2+ compliant
* Translation ready (text domain ag-starter-companion)
* No dependency on any other plugin
* No tracking, no advertising, no email collection

== Installation ==

1. Install one of the AG Starter themes (Restaurant, Artisan, Coach or Avocat) and activate it.
2. In WordPress, go to Plugins > Add New and search for "AG Starter Companion".
3. Click "Install Now", then "Activate".
4. A notice invites you to launch the setup. Click it, or go directly to Appearance > AG Setup.
5. Click the "Import demo content" button. That's all.

You can re-run the import or reset the content at any time from the same page.

== Frequently Asked Questions ==

= Does the plugin modify my existing data? =

No. If a page already exists with the same slug (e.g. "contact"), it is kept. The plugin does not delete anything without explicit confirmation.

= Can I use the plugin without an AG Starter theme? =

No. The plugin only runs if one of the AG Starter themes is in use. Otherwise it displays nothing and stays dormant.

= Does the import create demo blog posts? =

No. Only the necessary static pages + the menu. Your existing posts are preserved.

= Does the plugin connect to the internet? =

No. Everything is 100% local. No API call, no downloaded file.

= Can I reset the demo content? =

Yes. A "Reset" button appears after the first import. It deletes the created pages and menu, and sets "Show latest posts" back as the front page.

= Does the plugin collect any data? =

No. No tracking, no telemetry, no email. The plugin is entirely static.

= Under which license is it published? =

GPL v2 or later. You are free to use, modify and redistribute it.

= Who created this plugin? =

AGthemes, the theme division of Alliance Group (a web and AI agency based in Nantes, Naples and Marrakech). More info at https://alliancegroupe-inc.com

== Screenshots ==

1. The Appearance > AG Setup admin page, with the one-click import button.
2. The welcome notice that appears after activating the plugin.

== Changelog ==

= 1.13.0 =
* Description and readme translated to English for the WordPress.org Plugin Directory.
* Removed the manual load_plugin_textdomain call (translations are loaded automatically since WordPress 4.6).

= 1.2.0 =
* Added support for the AG Starter Avocat theme.
* When AG Starter Avocat is active, importing automatically creates 6 areas of expertise (Business law, Labor law, Family law, Real estate law, Criminal law, Tax law) through the ag_domaine custom post type, with icons and sample cases.
* The reset also removes the demo areas of expertise to allow a clean re-import.

= 1.1.0 =
* First support for the AG Starter Avocat theme (without the CPT at first).
* Plugin description updated to mention the 4 themes.

= 1.0.0 =
* Initial version.
* Support for the AG Starter Restaurant, Artisan and Coach themes.
* One-click import: 5 pages + menu + static front page + permalinks.
* Reset available.
* Automatic detection of the active theme.
* Translation ready.

== Upgrade Notice ==

= 1.0.0 =
First public release. Install and enjoy your AG Starter themes in one click.

== Credits ==

Plugin created by AGthemes (Alliance Group — https://alliancegroupe-inc.com).
No external dependency. GPL v2 or later license.
