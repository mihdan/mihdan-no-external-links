=== Mihdan: No External Links ===
Author: mihdan
Contributors: mihdan
Tags: seo, link, links, publisher, post, posts, comments
Requires at least: 3.5.0
Tested up to: 5.5
Stable tag: 4.5.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Convert external links into internal links, site wide or post/page specific. Add NoFollow, Click logging, and more...

== Description ==
Mihdan: No External Links converts external links to internal links. Featuring *Full Page or Targeted Content Filtering*, *Custom Redirect Page/Message*, *Encoded Masks*, *External Link Click Logging*, *Individual Link Exclusion*, *Post/Page Specific Exclusion*, and many more...

= Example =
Links like "*https://wordpress.org*" will be masked into
"*http://www.example.com/goto/https://wordpress.org*".

= Warning =
Mihdan: No External Links may conflict with cache plugins.
Usually adding the redirect page to the caching plugin exclusions works fine, but there are no guarantees.
Create a [support topic](https://wordpress.org/support/plugin/mihdan-no-external-links) if you need assistance resolving a caching issue.
**_Please provide as much detail as possible, for example, what version of WordPress & PHP you are using. Which caching plugin you are using. The more information you include the better._**

= Details =
Mihdan: No External Links is designed for specialists who sell different kinds of advertisements on their web site and care about the number of outgoing links that can be found by search engines. Now you can make all external links internal.

= How To Use =
Just do everything like you would normally, and as long as the plugin is active, external links will be automatically masked.

= Custom Parser =
We do **not** recommend using this feature! - Future updates may break your custom parser, we would recommend submitting a [feature request](https://wordpress.org/support/plugin/mihdan-no-external-links) instead.
Due to a recent update, any existing custom parsers will need to be updated to fit with the new code base. (Please see FAQs below for more details)
**_Limited support will be provided for any custom parser issues._**

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
Mihdan: No External Links **does not** make any changes to your database, it just processes the output. So you will not see these changes within the WYSIWYG editor.

== Installation ==

= From your WordPress dashboard =
1. Visit 'Plugins > Add New'
2. Search for 'Mihdan: No External Links'
3. Activate Mihdan: No External Links from your Plugins page.
4. [Optional] Configure Mihdan: No External Links settings.

= From WordPress.org =
1. Download Mihdan: No External Links.
2. Upload the 'mihdan-noexternallinks' directory to your '/wp-content/plugins/' directory, using your favorite method (ftp, sftp, scp, etc...)
3. Activate Mihdan: No External Links from your Plugins page.
4. [Optional] Configure Mihdan: No External Links settings.

== Changelog ==

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
Activation \ Deactivation improved, optimization, localization settings now stored as options.

= 0.03 =
Bugfix.

= 0.02 =
Multilanguagal release.

= 0.01 =
First release.

== Frequently Asked Questions ==

= Custom Parser Migration Instructions =
Remove the following lines of code:
`if (!defined('WP_PLUGIN_DIR'))
    include_once(ABSPATH . 'wp-content/plugins/mihdan-noexternallinks/mihdan-noexternallinks-parser.php');
else
    include_once(WP_PLUGIN_DIR . '/mihdan-noexternallinks/mihdan-noexternallinks-parser.php');`

Change:
`class custom_parser extends mihdan_noexternallinks_parser`

To:
`class WP_CustomParser extends Mihdan_NoExternalLinks_Public`

Change any options references:
* `$this->options['no302']` to `$this->options->masking_type == 'javascript'`
* `$this->options['redtime']` to `$this->options->redirect_time`
* `$this->options['fullmask']` to `$this->options->mask_links == 'all'`
* `$this->options['mask_mine']` to `$this->options->mask_posts_pages`
* `$this->options['mask_comment']` to `$this->options->mask_comments`
* `$this->options['mask_author']` to `$this->options->mask_comment_author`
* `$this->options['mask_rss']` to `$this->options->mask_rss`
* `$this->options['mask_rss_comment']` to `$this->options->mask_rss_comments`
* `$this->options['add_nofollow']` to `$this->options->nofollow`
* `$this->options['add_blank']` to `$this->options->target_blank`
* `$this->options['put_noindex']` to `$this->options->noindex_tag`
* `$this->options['put_noindex_comment']` to `$this->options->noindex_comment`
* `$this->options['LINK_SEP']` to `$this->options->separator`
* `$this->options['base64']` to `$this->options->link_encoding == 'base64'`
* `$this->options['maskurl']` to `$this->options->link_encoding == 'numbers'`
* `$this->options['stats']` to `$this->options->logging`
* `$this->options['keep_stats']` to `$this->options->log_duration`
* `$this->options['remove_links']` to `$this->options->remove_all_links`
* `$this->options['link2text']` to `$this->options->links_to_text`
* `$this->options['debug']` to `$this->options->debug_mode`
* `$this->options['restrict_referer']` to `$this->options->check_referrer`
* `$this->options['exclude_links']` to `$this->options->exclusion_list`
* `$this->options['noforauth']` to `$this->options->skip_auth`
* `$this->options['dont_mask_admin_follow']` to `$this->options->skip_follow`
* `$this->options['redtxt']` to `$this->options->redirect_message`

Change table names:
* `links_stats` to `external_links_logs`
* `masklinks` to `external_links_masks`

== System Requirements ==

* WordPress 3.5+
* PHP 5.3+

== Known Issues ==

* Localization is no longer working.
* Mask links in RSS comments does not always work.

