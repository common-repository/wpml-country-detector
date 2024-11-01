=== WPML Country Detector ===
Contributors: brooksX
Tags: wpml, country detector, geo, maxmind, translation, i18n, l10n, redirect
Requires at least: 3.1
Tested up to: 3.9.2
Stable tag: 0.2
License: GPLv2 or later

WPML Addon for detecting the user country and show his country flag in the language switcher. Redirect user to his country page

== Description ==

This plugin is a WPML addon that detects the user country and shows his country flag next to the available languages.
You can also redirect visitors based on visitor country if user locale translation exists. E.g if you have a language with locale code
en_US, the code can be devided into the language part 'en' and the geographical part 'US'.
This plugin has an option to redirect according to the geographical parts of the locale codes of the languages served on your WPML translated site.

The Native WPML language switcher CSS for the footer and widget are used to preserve brand consistency.
= How usefull is this?  =
To get a clear picture of how usefull this is, see the language switcher used by <a href="http://paypal.com" title="paypal language switcher">pay pal</a>
= How to use it =
There are two ways of showing the langauge switcher.
1- <b>Using the widget</b> (available in wp-admin>appearance>widgets)
2- <b>Enabling the WPML Country Detector  switcher in the  footer</b> from the settings in WPML>languages
<a href="http://shop.zanto.org/shop/wpml-country-detector/">learn more</a>
= Quick Support =
for quick support, submit a support ticket <a href="http://zanto.org/support">here</a> 
= Want More? =
To keep up to date with the latest WordPress translation, localization and Internationalization news, subscribe to our blog at <a href="http://zanto.org" title="WordPress translation, Internationalization and localization"> Zanto</a> 
or follow Zanto on <a title="multilingual plugin developement" href="http://twitter.com/wpzanto">twitter</a> and influence what gets developed next! 
For more free and premium multilingual plugins for WPML, and  Zanto visit our<a href="http://shop.zanto.org" title="wordpress multilingual plugins"> Multilingual plugins page</a>. all GPL 


= Features =
* Language Switcher Widget that shows user country flag
* Footer language Swicher with user country flag and available langauges
* User redirection to the appropriate language locale based on the user country 


== Installation ==

-Upload the Plugin to your blog and activate it like any other WordPress plugin.
Note: WPML plugin must be installed first.
- Go to WPML > Languages and configure the settings under "Country Detector Addon Options"
- To use country redirect option, make sure browser language redirect is disabled.

== Screenshots ==

1. Settings to enable the footer country detector switcher
2. country detector widget in appearance > widgets

== Changelog ==
= 0.1 =
* Initial commit
= 0.2 =
* Bug fixes ip wasn't being used
