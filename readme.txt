=== Plugin Name ===
Contributors: Alex
Tags: google,trends, browser, widget, trendy, google.com
Requires at least: 2.0.2
Tested up to: 2.5.2
Stable tag: 2.0.1

Google latest hot trends on your wordpress sidebar.

== Description ==

Do you want to add google trends in your sidebar, no easy ways. Just fallow this easy steps in `Installation` and you are all set.

* Update to 1.2 for a linking directly to Google Trends website.

* Update 2.0 added an search capability to the plugin. Now it can search the articles database for those keywords/phrases that link to the articles directly. ( Requested by Da Master )

If he dosen't find any post that contains the trend keyword it links back to google trends. 

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload `wp_trends.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. This is the function that you can use in your templates: getTrends($nr,$before,$after,$islink,$PostLink);

* The $nr is for how many trends do you whant to show up in your bar . Eq: 10
* The $before parameter is for the tag that is in front of the trends. Eq: <li>   
* The $after parameter is for the tag that is after the trends. Eq: </li>
* The $islink is the switch for the links. You can find the link target in the source code.
* The $PostLink is the switch for post searching. If it's set on true it makes the plugin search in the database for tags and words that match the trend title.

== Frequently Asked Questions ==

Q:Can i skip the sidebar editing part and just put it in from the dashbord?

A:No, you cannot. You have to edit `sidebar.php` manualy.

== Screenshots ==

1. Plugin in action
