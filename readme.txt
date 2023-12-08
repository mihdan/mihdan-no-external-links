=== No External Links ===
Author: mihdan
Contributors: mihdan, kaggdesign
Tags: seo, seo-hide, link, links, publisher, post, posts, comments
Requires at least: 5.7.4
Tested up to: 6.4
Stable tag: 5.1.2.1
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Convert external links into internal links, site wide or post/page specific. Add NoFollow, Click logging, and more...

== Description ==
No External Links converts external links to internal links. Featuring *Full Page or Targeted Content Filtering*, *Custom Redirect Page/Message*, *Encoded Masks*, *External Link Click Logging*, *Individual Link Exclusion*, *Post/Page Specific Exclusion*, and many more...

= Example =
Links like "*https://wordpress.org*" will be masked into
"*http://www.example.com/goto/https://wordpress.org*".

= Warning =
No External Links may conflict with cache plugins.
Usually adding the redirect page to the caching plugin exclusions works fine, but there are no guarantees.
Create a [support topic](https://wordpress.org/support/plugin/mihdan-no-external-links) if you need assistance resolving a caching issue.
**_Please provide as much detail as possible, for example, what version of WordPress & PHP you are using. Which caching plugin you are using. The more information you include the better._**

= Details =
No External Links is designed for specialists who sell different kinds of advertisements on their web site and care about the number of outgoing links that can be found by search engines. Now you can make all external links internal.

= How To Use =
Just do everything like you would normally, and as long as the plugin is active, external links will be automatically masked.

= Recommended Settings =
The default settings that are used on a fresh install of the plugin are what we recommend.

= Support =
Need help with anything? Please create a [support topic](https://wordpress.org/support/plugin/mihdan-no-external-links).
**_Please provide as much detail as possible, for example, what version of WordPress & PHP you are using. Examples of links that do not work. If you are using a caching plugin, please specify which one. The more information you include the better._**

= Feature Request =
Want a feature added to this plugin? Create a [support topic](https://wordpress.org/support/plugin/mihdan-no-external-links).
We are always looking to add features to improve our plugin.

= Localization =
We apologize that the latest version has changed so much that existing localizations no longer work.
If you would like to contribute to the translations please get in touch.

= Note =
No External Links **does not** make any changes to your database, it just processes the output. So you will not see these changes within the WYSIWYG editor.

== Installation ==

= From your WordPress dashboard =
1. Visit 'Plugins > Add New'
2. Search for 'No External Links'
3. Activate No External Links from your Plugins page.
4. [Optional] Configure No External Links settings.

= From WordPress.org =
1. Download No External Links.
2. Upload the 'mihdan-noexternallinks' directory to your '/wp-content/plugins/' directory, using your favorite method (ftp, sftp, scp, etc...)
3. Activate No External Links from your Plugins page.
4. [Optional] Configure No External Links settings.

== Changelog ==

= 5.1.2 (08.12.2023) =
* Added a code editor to modify the redirect page
* Added progress bar to redirect page
* Fixed display error when an custom page for redirect is specified

= 5.1.1 (06.12.2023) =
* Fixed fatal errors on admin pages
* Fixed bugs in SEO Hide module

= 5.1.0 (25.11.2023) =
* Tested with WordPress 6.4+.
* Added PHP 8.3 support
* Added excluded list for SEO hide

= 5.0.7 (03.05.2023) =
* Fixed ampersand conversion in links
* Fixed PHP notices

= 5.0.6 (02.05.2023) =
* Resolve #[20](https://github.com/mihdan/mihdan-no-external-links/issues/20)
* Resolve #[25](https://github.com/mihdan/mihdan-no-external-links/issues/25)
* Resolve #[30](https://github.com/mihdan/mihdan-no-external-links/issues/30)

= 5.0.5 (18.04.2023) =
* Tested with WordPress 6.2.
* Added PHP 8.2 support
* Removed settings for the dead "Link Shrink" service
* Removed custom link parser functionality

= 5.0.4 (13.09.2022) =
* Added possibility to specify any page of the site as a redirect page

= 5.0.3 (12.09.2022) =
* Fixed fatal errors on logging pages
* Fixed errors in translations in the WordPress admin

= 5.0.2 (09.09.2022) =
* Added polyfill for the deprecated mcrypt PHP module

= 5.0.1 (08.09.2022) =
* Code refactoring to conform coding and security standards.

= 5.0.0 (14.04.2022) =
* Code refactoring
* Set minimum compatibility PHP version to 7.4
* PHP 8.1 compatibility

= 4.8.0 (07.04.2022) =
* Added a link to plugin settings on all plugins page
* Fixed security issues

= 4.7.4 (13.12.2022) =
* Fixed a bug with SEO hide

= 4.7.3 (07.09.2021) =
* Check if `wp_referer()` exists to work properly with `rel="noreferrer"` links

= 4.7.2 (05.04.2021) =
* Added a new settings page with all the author's plugins

= 4.7.1 (04.04.2021) =
* Fixed a bug with SEO hide
* Fixed a bug with the name of the main plugin file

= 4.7.0 (03.04.2021) =
* Hiding links using SEO hide method

= 4.6.0 (02.04.2021) =
* Added support for WordPress 5.7
* Added support for HTTP status codes (301, 302, 307)
* Added script for auto deploy to wp.org

= 4.5.2 (30.04.2020) =
* Added support for WordPress 5.4
* Fixed bug with headers

= 4.5.1 (19.01.2020) =
* Added output buffering test for Site Health
* Added domain & advert type fields for adf.ly
* Added domain *.wordpress.org to exclude list
* Fixed bug with output buffer notice
* Fixed bug with &#038; and &amp; in URL
* Fixed bug with adf.ly support
* Updated referrer-warning.php template
* Updated referrer.php template
* Updated yourls support
* Updated layout for links settings page
* Prevented error 404 on redirect page

= 4.5 (2019-04-16) =
* Removed possible SQL injection in admin panel scripts

= 4.4 (2019-04-12) =
* RegEx pattern to DomDocument
* WPCS
* Fix bug on log page

= 4.3.8 (2019-02-02) =
* Built-in support for yourls link shortening

= 4.3.7 (2019-01-31) =
* Fix bug with cyr2lat plugin: remove `sanitize_title` from menu items

= 4.3.6 (2018-12-07) =
* Add assets for plugin

= 4.3.5 (2018-08-09) =
* Fix bug with `Mihdan_NoExternalLinks_Admin_Log_Table`

= 4.3.4 (2018-08-08) =
* Added new lines to the translation

= 4.3.3 (2018-08-08) =
* Change text domain from `mihdan-noexternallinks` to `mihdan-no-external-links`

= 4.3.2 =
* Update README.txt

= 4.3.1 =
* Bump version

= 4.3 =
* Forked from https://wordpress.org/plugins/wp-noexternallinks/

= 4.2.2 =
Several bug fixes.

= 4.2.1 =
Fixed several PHP Compatibility issues.

= 4.2.0 =
Added many new features including:
* AES-256 Link Encryption
* Link Anonymizer
* Domain Specific Targeting
* Bot Specific Targeting
* Improved Logging functionality
* Built-in support for link shortening (Adf.ly, Bitly, Link Shrink, Shorte.st)

And several bug fixes.

= 4.1.0 =
Re-coded Custom Parser functionality.

= 4.0.2 =
Fixed a PHP Compatibility issue.

= 4.0.1 =
Bug fixes.

= 4.0.0 =
Plugin rewritten. Major overhaul of the code base.

= 3.5.9.9 =
Added custom filter with name "wp_noexternallinks". Please use it for custom fields and so on.

= 3.5.9.8 =
Fixed custom parser load.

= 3.5.9.7 =
Add support for custom location of wp-content dir.

= 3.5.9.6 =
Fix for RSS masking.

= 3.5.9.5 =
Added support for relative links, beginning with slash (/).

= 3.5.9.4 =
Added masking options for RSS feed.

= 3.5.9.3 =
Added noindex comment option for yandex search engine.

= 3.5.9.2 =
Parser logic optimization and fixes.

= 3.5.9.10 =
Disabled full masking when running cron job to avoid collisions.

= 3.5.9.1 =
Fixed bug when statistic was not written.

= 3.5.9 =
Updated filter to support multiline links code.

= 3.5.8 =
Custom parser file moved to uploads directory to avoid deletion.

= 3.5.7 =
Custom parser file moved to uploads directory to avoid deletion.

= 3.5.6 =
Fixed bug with writing click stats to database.

= 3.5.5 =
Divided code to smaller functions for easier overwrite with custom modes.

= 3.5.4 =
Fixed "rel=follow" feature. Added icon for admin menu.

= 3.5.3 =
Do not disable error reporting on server any more.

= 3.5.20 =
minor text fixes

= 3.5.2 =
Some refactoring.

= 3.5.19 =
minor XSS fix (thanks to DefenseCode WebScanner), more debug, fix possible bug with numeric masking

= 3.5.18 =
added index on links table

= 3.5.17 =
fix for better compatibility with php7

= 3.5.16 =
minor security fix

= 3.5.15 =
fix masking issues with mixed http/https

= 3.5.14 =
fallback to 3.5.11

= 3.5.13 =
bugged versions

= 3.5.12 =
bugged versions

= 3.5.11 =
minor improvements

= 3.5.10 =
Fixed issues with cron job

= 3.5.1 =
Added option for developers - now you can extend plugin with custom parsing functions! Just rename "custom-parser.sample.php" to "custom-parser.php" and extend the class (see sample file for details). Your modifications will stay even after plugin upgrade!

= 3.5 =
Redesigned user friendly admin area!

= 3.4.5 =
Added option to disable links masking when link is made by admin and has **rel="follow"** attribute

= 3.4.4 =
Added exclusion for polugin from WP Super Cache.

= 3.4.3 =
Added detection and prevention of possible spoofing attacks. See new option in plugin settings. It is enabled by default.

= 3.4.2 =
Fixed displaying error where there are no stats for today.

= 3.4.1 =
Fixed displaying error where there are no stats for today.

= 3.4 =
Replaced direct SQL queries with WPDB interface.

= 3.3.9.2 =
Now debug mode does not mess up web site. Also added some text to options page.

= 3.3.9.1 =
Added some more debug.

= 3.3.9 =
Updated for correct work with enabled statistics and Hyper Cache plugin.

= 3.3.8 =
Correct redirection links with GET parameters, sometimes damaged by wordpress output.

= 3.3.7 =
Critical update for 3.3.6.

= 3.3.6 =
More output for debug mode.

= 3.3.5 =
Little update so that plugin won't cause harmless warning.

= 3.3.4 =
Now you can customize link view if you chose "remove links" or "turn links into text". Use CSS classes "waslinkname" and "waslinkurl" for it.

= 3.3.3b =
Exclusions list fix, possible fix for not found 'is_user_logged_in' function.

= 3.3.2 =
Imporovements for option "Mask ALL links in document", debug mode.

= 3.3.1 =
Hotfix for some blogs which crashed on checking if page is RSS feed, improvements for option "Mask ALL links in document" - now it doesn'n mask RSS and posts with option "don't mask links".

= 3.3 =
Additional protect from masking links in RSS, fix for admin panel in wordpress 3.4.2, Perfomance fixes.

= 3.2 =
Two new options, little backslashes fix, error reporting fix.

= 3.1.1 =
Improved compatibility with some shitty servers.

= 3.1.0 =
Added masking links with digital short codes.

= 3.0.4 =
Fixed when some options in checkboxes couldn't be changed.

= 3.0.3 =
Removed some extra info, added some error handlers, repaired broken system for flushing click stats.

= 3.0.2 =
Removed test message "failed to update options" when nothing changed in options. Also, fixed issue when, if link masking was disabled for post, it was also disabled for comments.

= 3.0.1 =
Fixed option update issue.

= 3.0.0 =
Code improvements, added .po translation, clicks stats and option to mask Everything.

= 2.172 =
fixed javascript error when redirects ended with ";"

= 2.171 =
Added automatic exclusion of internal links (#smth) from masking.

= 2.17 =
Several bugfixes for low possible case scenarios...

= 2.16 =
Javascript links aren't broken by plugin now, thanks to [Andu](http://anduriell.es).

= 2.15 =
Fixed for some servers with setup which replaces "//" with"/".

= 2.14 =
Absolute  file paths used now instead of relative.

= 2.13 =
Fixed language inclusion problem which apperared in some cases.

= 2.12 =
Fully compatible with PHP4.

= 2.11 =
Removed "public" keyword in class functions definitions. Probably will be more compatible with PHP4.

= 2.10 =
Plugin was rewrited for faster performance, fixed adding targer="_blank" for internal links.

= 2.05 =
Fixed internationalization, added Belarusian language.

= 2.04 =
Changed default settings, removed "disable links masking".

= 2.03 =
Fixed broken exclusions list.

= 2.02 =
Updated to execute later then other link filters, preventing possible problems with other plugins.

= 2.01 =
Little bugfix, for fixing errors when empty exlusions.

= 2.0 =
Many significant changes, including urls and post exclusion from masking, another rewrite structure, and new options.

= 0.071 =
Russian translation corrected.

= 0.07 =
Better work for sites wihout mod_rewrite.

= 0.06 =
Bugfix for email links.

= 0.05 =
Bugfix for wrong html parsing.

= 0.04 =
Activation/Deactivation improved, optimization, localization settings now stored as options.

= 0.03 =
Bugfix.

= 0.02 =
Multilanguagal release.

= 0.01 =
First release.

== System Requirements ==

* WordPress 5.0+
* PHP 7.4+

== Known Issues ==

There are no known errors at this time.

