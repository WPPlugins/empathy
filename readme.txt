=== Empathy ===
Contributors: mgsisk
Donate link: http://groups.google.com/group/empathy-discussion/
Tags: post, page, meta, icon, emoticon, widget, themes, extra, fun, taxonomy, emotion, mood
Requires at least: 2.8
Tested up to: 2.8.6
Stable tag: 1.0.3

You're emotional. Empathy understands. Make your emotions an integral part of your site with the Empathy mood and emotion plugin.

== Description ==

Make your emotions an integral part of your site with Empathy.

= New in 1.0.3 =

- Uninstalling Empathy now removes user-specific settings.
- Empathy now prevents you from mistakenly adding an existing emotion. Form information is maintained if an error occurs when attempting to add an emotion.
- Includes updated.POT file for translations.

= Feature Highlights =

- **Your Emotions:** Canned mood lists are a thing of the past; with Empathy's emotion taxonomy you can create exactly the moods and emotions that fit you. And because Empathy utilizes WordPress' own Taxonomy API, each emotion can have it's own unique descriptions, post counts, archive pages (allowing users to browse your posts by emotion), and site feeds (allowing users to subscribe to your emotions).
- **Your Themes:** Empathy's emotion theme manager allows you to create, edit, and manage any number of themes for your emotions, each with it's own unique imagery (in gif, jpg, png, or swf format). Authors can select a unique theme to use for their own emotions or stick with the active theme and specify a current emotion to display on their author archive page.
- **Your Rules:** Empathy's emotion widgets, shortcodes, and site-integration settings allow you to start using it immediately, without having to hack template tags into your existing theme. If hacking themes is your thing, though, Empathy has a complete set of new template tags for displaying and working with your emotions.

== Installation ==

1. Upload the `empathy` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Start using Empathy!

Please note that Empathy requires PHP 5. If you don't know what version of PHP you're currently using you may want to contact your web host before installing Empathy.

== Frequently Asked Questions ==

= Where can I get help with this? =

Please see [the official discussion group ](http://groups.google.com/group/empathy-discussion) for information, documentation, and support.

== Screenshots ==

1. Emotions 
2. Themes
3. Theme Editor
4. Settings

== Changelog ==

= 1.0.3 (November 17, 2009) =

- Updated mgs\_plugin\_core to properly handled filters.
- Updated uninstallation. Running the uninstall now removes user-specific settings.
- Updated the Emotions administration page. Empathy now properly checks for existing emotions when attempting to add a new one, and maintains form information if an error occurs when attempting to add an emotion.
- Additional minor tweaks to various administration functions.
- Includes updated .POT file for translations.

= 1.0.2 (November 5, 2009) =

- Add _Uninstal Empathy_ to the Tools page. Removes all information, files, and settings related to Empathy.
- Tweaked the Batch Emotions page layout to more closely match that of other WordPress pages.
- Includes .POT file for translations.

= 1.0.1 (November 3, 2009) =

- Added Tools page:
	- Use the Batch Emotions tool to quickly change the emotions associated with posts (individually or in bulk).

= 1 (November 1, 2009) =

- Initial public release.
- Includes Emotions page:
	- Create emotions with unique titles, descriptions, and post counts. Set emotions as children of other emotions to keep them organized.
	- Edit existing emotions to modify emotion names, slugs, parents, and descriptions.
	- Set or remove emotions as default emotions (individually or in bulk) that will be selected by default when you create a new post or page.
	- Delete existing emotions (individually or in bulk) and all of the imagery associated with them.
- Includes Themes page:
	- Create new, uniquely-named themes to add imagery to your emotions.
	- Set the active theme that will be used throughout your site for emotions.
	- Edit existing themes to upload new imagery for each emotion (individually or in bulk).
	- Delete imagery associated with a theme (individually or in bulk), or delete an entire theme.
- Includes Settings page:
	- Toggle site integration.
		- Adjust the location of post emotions.
		- Adjust how post emotions are displayed.
	- Toggle displaying emotionally-related posts on single-post pages.
- Includes new user options. Authors can select their own Empathy theme and set their current emotion from the WordPress Profile administration page.
- Includes new widgets: Emotions, Emotion Cloud, Site Emotion
- Includes new shortcodes: [empathy], [empath\_cloud], [empathy\_related]
- Includes new template tags: in\_empathy, is\_empathy, get\_empathy, the\_empathy, get\_empathy\_info, the\_empathy\_info, the\_empathy\_object, the\_author\_emotion, empathy\_list\_emotions, empathy\_dropdown\_emotions, empathy\_emotion\_cloud, empathy\_related\_posts, empathy\_site\_emotion

== Additional Requirements ==

Empathy requires PHP 5.

== Special Thanks ==

To the following artists for creating the included default imagery:

- [2s-Space Emotions v2 by kirozeng](http://kirozeng.deviantart.com/art/2s-space-Emotions-v2-72785912)
- [Manto Emoticons by Manto](http://365icon.com/icon-styles/emoticons/manto-emotion-icons-emoticons/)
- [Tango Emoticons by Furyo-kun](http://furyo-kun.deviantart.com/art/Tango-Emotes-121853363)