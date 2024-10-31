=== Multi-Site User Replicator 3000 ===
Contributors: jroakes
Donate link: http://visiblecompany.com
Tags: user, multi-site, sites, mass edit,
Requires at least: 3.1 - Should work for previos versions.
Tested: 3.1
Stable tag: Trunk

WP3.1 multisite "mu-plugin" that allows you to add a user to all sites or no sites. Just drop in mu-plugins.

== Description ==
This plugin will give you the ability to pull up any (non-Super Admin) user and in one click add that user(with a global role) to all sites in your MU install.  Likewise, if you want to remove a user from all sites it is one click as well.  CAUTION: Wordpress has an optional mechanism when deleting a user from a site that will allow you to assign that users posts to another user.  I have ignored this for this release.  Please take a DB backups until you are used to this plugin and Multi-Site.

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload `user-replicator-3000.php` to the `/wp-content/mu-plugins/` directory
2. Find "Multi-Site User Replicator 3000" options at the bottom of Network Admin->Edit User

== Frequently Asked Questions ==

* Will this plugin re-assign the posts of the deleted user? Not Now.
* What will happen if I remove the plugin from the mu-plugins directory? Nothing.  The users will still be assigned and the minimal data in the database will remain.
* Can you bulk update a group of users at a time, but not all? Not Now.
* The user has been deleted from some sites and I want to re-add to all.  How do I do this?  "Remove User From All Sites" and then "Add User to All Sites" 

== Screenshots ==

1. Add User: Add user to all sites step.
2. Delete User: Delete user to all sites step."


== Changelog ==

= 0.1 = 
* initial release