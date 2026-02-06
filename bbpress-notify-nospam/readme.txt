=== bbPress Notify (No-Spam) ===
Contributors: useStrict
Donate link: https://www.paypal.me/usestrict
Author URI: https://www.usestrict.net/
Plugin URI: https://usestrict.net/2013/02/bbpress-notify-nospam/
Tags: bbpress, buddyboss, email notification, forum notifications, no spam
Requires at least: 3.1
Tested up to: 6.9
Text Domain: bbpress-notify-nospam
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Stable Tag: 3.0.1
Requires PHP: 5.6
Powerful, customizable email notifications for bbPress and BuddyBoss forums — without the spam.


== Description ==
**bbPress Notify (No-Spam)** is the ultimate notification plugin for **bbPress** and **BuddyBoss** forums.  
It replaces the limited default subscription system with a flexible, no-spam solution that gives your users **personalized, reliable email updates** about new topics and replies.  

Stop flooding inboxes. With bbpnns you control exactly who gets notified, when, and how. Perfect for community managers, membership sites, and anyone who wants professional-grade forum notifications.  

<strong>New!</strong> Join our Facebook Group to discuss all things bbpnns: <a href="https://www.facebook.com/groups/bbpressnotifynospam" target="_blank">Better Forum Notifications with bbPress Notify (No-Spam)</a>.  

= Key Features =

- 📧 **Send professional emails** — Choose HTML, plain text, or multipart with image support.  
- 🎯 **Target the right audience** — Notify by user roles (admins, moderators, members, etc.).  
- 🚀 **Faster performance** — Background notifications prevent post-submission timeouts.  
- 🧪 **Preview before sending** — Dry-run mode shows exactly who will receive notifications.  
- 🔌 **Developer-friendly** — Dozens of filters and actions for easy customization.  
- ✅ **BuddyBoss compatible** — Works out of the box with BuddyBoss forums.  

<a href="https://usestrict.net/bbpress-notify-no-spam-documentation/" target="_blank">📖 View the full documentation</a>  


== Premium Add-Ons ==

Take bbpnns to the next level with powerful extensions:  

- **Reply by Email** – Post new topics or replies directly from your inbox.  
- **Bulk Mailer** – Send thousands of notifications via Mailgun or SendGrid without server timeouts.  
- **Digests** – Give users daily, weekly, or monthly summaries to reduce inbox noise.  
- **Opt-Out** – One-click unsubscribe options for CAN-SPAM and CASL compliance.  
- **Membership & LMS Bridges** – Integrate with BuddyPress, BuddyBoss, MemberPress, Ultimate Member, LearnDash, AccessAlly, Private Groups, and more.  

👉 Browse all premium add-ons here: <a href="https://usestrict.net/product-category/premium-wordpress-plugins/?utm_source=bbpnns-readme&utm_medium=wporg&utm_campaign=bbpnns" target="_blank">bbpnns Premium Add-Ons</a>  


== Installation ==

1. Upload the entire plugin folder to `/wp-content/plugins/`.  
2. Activate the plugin through the “Plugins” menu in WordPress.  
3. Go to **bbPress Notify (No-Spam) > Settings** in the main admin menu to configure.  


== Frequently Asked Questions ==

= Does it work with BuddyBoss? =  
Yes! BuddyBoss forked bbPress and kept all the inner workings. Anything that works with bbPress works with BuddyBoss.  

= Why am I not receiving notifications of my own topics/replies? =  
Check the setting “Notify authors of their own posts” under both Topics and Replies.  

= Where are the settings located? =  
As of version 2.0, settings are in their own **bbPress Notify (No-Spam)** menu item in the Admin dashboard.  

= People are getting timeouts when posting. Why? =  
If many users are notified at once, the server may time out. Enable **Background Notifications** to offload the sending to wp-cron. For very large communities, consider the [Bulk Mailer add-on](https://usestrict.net/product/bbpress-notify-no-spam-bulk-mailer/?utm_source=bbpnns-readme&utm_medium=wporg&utm_campaign=bbpnns).  

= Can users opt out of notifications? =  
Yes — with the [Opt-Out add-on](https://usestrict.net/product/bbpress-notify-no-spam-opt-out-add-on/?utm_source=bbpnns-readme&utm_medium=wporg&utm_campaign=bbpnns), users can unsubscribe with one click.  

= Does it integrate with membership or LMS plugins? =  
Yes — we support popular tools like BuddyPress, BuddyBoss, MemberPress, LearnDash, AccessAlly, Ultimate Member, and Private Groups. See the Premium Add-Ons section for details.  

= Can I customize the notifications? =  
Absolutely. Developers have access to dozens of filters and actions. Non-coders can use settings screens to adjust templates and recipients.  

---

== Screenshots ==
1. General settings tab  
2. Topics settings tab  
3. Replies settings tab  
4. Support tab  

== Changelog ==
= 3.0.1 =
* Make magic properties work with isset() and other functions.

= 3.0.0 =
* Refactor: Move main class into `includes/controller/` and add lightweight loader.
* Tests: Add modern PHPUnit tests and robust test bootstrap.
* Style: Run PHPCS autofix and apply plugin-focused style fixes.

= 2.20.1 =
* Fix notices for malformed user stdClass.

= 2.20 =
* Fix vulnerabilities.
* Fix _load_textdomain_just_in_time warnings.

= 2.19.5=
* Fix wrong textdomain.
* Update tested up to.

= 2.19.4 =
* Fix deprecation notice.

= 2.19.3 =
* Fixed forum-url tag only working for replies, not topics.

= 2.19.2 =
* Add support for forum-url tags.

= 2.19.1 =
* Fix dynamic property creation deprecation messages.

= 2.19 =
* Add Action Scheduler support.

= 2.18.5 =
* Fix load_plugin_textdomain to work with 6.7.1.

= 2.18.4 =
* Address vulnerable code.
* Updated tested up to.

= 2.18.3 =
* Safely handle users with missing roles.
* Update tested up to.

= 2.18.2 =
* Fix missing fields required by add-ons.
* Update tested up to.

= 2.18.1 =
* Fix missing user fields from tag replacements.

= 2.18 =
* Streamline amount of user data loaded to reduce memory footprint.
* Fix deprecated notices under PHP8. 

= 2.17.10 =
* Also remove BuddyBoss notifications when inside ajax requests.

= 2.17.9 =
* Remove notifications action set by BuddyBoss.

= 2.17.8 =
* Allow adjusting dry-run post status via filter.

= 2.17.7 =
* Fix notices on add-ons page.

= 2.17.6 =
* Make sure we get an actual user from the database in user_ok_role. Return false otherwise.

= 2.17.5 =
* Return false in user_ok_role if user not logged in.

= 2.17.4 =
* Change approach to checking for user role as it was sometimes failing.

= 2.17.3 =
* Fix missing bridge warnings when using WPFusion or BP Moderation Tools.

= 2.17.2 =
* Make sure blocked users do not get notifications.

= 2.17.1 =
* Made a couple of tweaks to support PHP 8. See https://wordpress.org/support/topic/php-8-compatibility-changes/

= 2.17 =
* Added support for author details tags: author-first_name, author-last_name, author-display_name, author-user_nicename.

= 2.16.1 =
* Added more bridge warnings in the settings.

= 2.16 =
* Adjusted translation text-domain.
* Adjusted support instructions and tested-up-to version.

= 2.15.3 =
* Disabling autoembed for buddyboss during mailout as it makes images lazy-load.

= 2.15.2 =
* Fix possible memory leak if admin creates a topic in the back end and forgets to assign a forum.

= 2.15.1 =
* Fixed bug loading users by roles when no role was selected.

= 2.15 =
* Added From Name and From Email fields to Support > General
* Added filter 'bbpnns_from_name' to filter From Name
* Added Filter 'bbpnns_from_email_address' to filter From Email
* Improved performance loading users by role.

= 2.14 =
* Elegant fix for UTF-8 subject line issues.

= 2.13.3 =
* Added filters for topic/reply subject and body to help with translation, etc.
* bbpnns_raw_{$type}_subject - $type is either topic or reply
* bbpnns_raw_{$type}_body - $type is either topic or reply

= 2.13.2 =
* Force replace long dash entity with regular dash before sending out the message.

= 2.13.1 =
* Fix catcheable error in get_topmost_forum_link method.

= 2.13 =
* Introducing topmost-forum tag.
* Fixed call to get WP error message on edge case.

= 2.12.1 =
* Check that subscribed users really do exist before adding them to the recipient list.

= 2.12 =
* Prefer mb_encode_mimeheader() over iconv_mime_encode() for UTF-8 subject lines, if available. 
* Added extra debugging information to Support tab.

= 2.11.1 =
* Attempt to fix UTF-8 subject line encoding for some email clients.

= 2.11 =
* Removing autoembed for topics and replies as they get filtered out of the final notification email.

= 2.10 =
* Added context to tag methods so we can support different tags for email subject line and body.

= 2.9.5.1 =
* Fixed typo in previous commit.

= 2.9.5 =
* Also stop core bbPress Notifications if roles or author-notifications are in effect.

= 2.9.4 =
* Better control/stopping of core bbPress Notifications.

= 2.9.3 =
* Add X-Auto-Response-Suppress: All header to the mailout.

= 2.9.2 =
* Introduced bbpnns() function as wrapper to get global $bbPress_Notify_noSpam object.
* Making $bbPress_Notify_noSpam->load_lib() public for ease of use.
* Changed priorities for bbp_new_topic/bbp_new_reply so they can be overridden in time.  

= 2.9.1 =
* Fixed bad private forum redirect for sites that don't use pretty permalinks. 

= 2.9 =
* Added option to automatically subcribe new users to all forums.

= 2.8.3.1 =
* Fixed pesky wpautop adding extra br tags.

= 2.8.3 =
* Add $user_info parameter to bbpnns_skip_notification notification to reduce DB lookups.

= 2.8.2 =
* Added filter bbpnns_redirect_url to allow adjusting the redirect URL for non-public forums.

= 2.8.1 =
* Enhancement - better user control when running in the background.

= 2.8 =
* Added new feature - Auto subscribe forum users to newly created topic so they also get replies notifications. See bbPress Notify (No-Spam) > Settings > Topics tab > bbPress Forums Subscriptions Override section.
* Added i18n files. 

= 2.7 =
* Work around membership plugins blocking content during mailouts.

= 2.6.1 =
* Added support for bbpress 2.6 moderation functionality.

= 2.6 =
* Changed behaviour of notify_authors checkbox: Originally it would only remove authors if they were already in the recipient list. Now it will also add authors if they're not in recipients and the setting is checked. 

= 2.5.7 =
* Call bbp_new_topic and bbp_new_reply in Dry Run with full param list to avoid breaking third-party plugins. 

= 2.5.6 =
* Fix login controller's maybe_add_redirect() to use given URL instead of pulling the permalink from the DB.

= 2.5.5 =
* Fix dry-run sending messages when run with background notifications enabled.

= 2.5.4 =
* Pass reply-url through login query string logic.

= 2.5.3 =
* Add safeguards to dry-run to keep bbpress from sending notifications.

= 2.5.2 =
* Fix nonce handling in old 1.x to 2.x db conversion.

= 2.5.1 -
* Better encoding of href variables in convert_images_and_links().

= 2.5 =
* Added ability to include forum subscribers in a reply notification.
* Improved trace messages.

= 2.4 =
* Added feature: Dry run tests to help identify which settings are adding/dropping which users from the recipient list.

= 2.3.1 =
* Encode href variable in convert_images_and_links() if necessary.

= 2.3 =
* Added action bbpnns_doing_notify_on_save.

= 2.2.1=
* Fix: Lines ending in <br> were being wrapped in <p></p> tags.

= 2.2 =
* Enhancement: Call `set_time_limit()` when running as cron to try to avoid timeouts.
* Enhancement: No longer embed images as some sites don't use PHPMailer to allow attaching them.
* Enhancement: Add support for [date] tag in email subjects and bodies. Accepted parameter is `format` and values are those accepted by the `date()` function. It defaults to WP's date and time values in Settings > General.

= 2.1.13.2 =
* Fix: make new argument forum_id optional for backwards compatibility with other plugins/customizations.

= 2.1.13.1 =
* Fix: missed one instance of the bbpnns_topic_url filter in previous commit. 

= 2.1.13 =
* Added feature: Topic and reply urls in private forums now go through the login screen instead of throwing a 404 error.

= 2.1.12 =
* Replace filename with basename in image attachments as filename did not have the extension.

= 2.1.11 =
* Make render_template() public.

= 2.1.10 =
* Set default has_sidebar value for settings screen.

= 2.1.9 =
* Fix: Edge case where add_settings_error() was being called too soon.

= 2.1.8 =
* Fix: Fixed undefined property notice in Settings screen.

= 2.1.7 =
* Fix: Properly handle Subject line entity decoding when UTF-8 subject line option is selected.

= 2.1.6 =
* Fix: Adjusted priority of 'init' as it was causing weird issues in some cases.

= 2.1.5 =
* Fix: Add missed do_action( 'bbpnns_register_settings' ) call to admin_core.

= 2.1.4 =
* Fix: Race condition between bbpnns and Moderation Tools for bbPress plugin.

= 2.1.3 =
* Enhancement: Some third-party plugins are suppressing the database update notice with the button. Added a shortcut to force the upgrade. Use ?bbpnns_force_convert=1 query parameter in any Admin screen to force the update.

= 2.1.2 =
* Fix: Some 1.x installs had non-array values for recipient roles. Normalizing them to avoid errors.

= 2.1.1 =
* Fix: Role recipients settings field was not accepting an empty list.

= 2.1 =
* Fix: Normalizing recipient roles from bad 1.x -> 2.0 conversion. This also fixes cases where some add-ons can't display the user preferences in their profile screens.
* Fix: Normalizing background notifications settings that were unified - previously we had one for topics and one for replies.
* Added: Converting entities to their characters in subject line if UTF-8 subject is enabled.
* Fixed: Removing duplicate notifications (bbpnns + bbpress core) in some scenarios.

= 2.0.5.1=
* Removed debugging left behind in previous commit.

= 2.0.5 =
* Fix: better handling of roles in topics and replies settings tabs.

= 2.0.4.1 =
* Fix: Corrected instance of legacy options check.

= 2.0.4 =
* Fix: Occasionally the background notifications settings checkbox would not uncheck. 

= 2.0.3 =
* Fix: Typo in previous commit causing set_notice() errors.

= 2.0.2 =
* Fix: Defer conversion check to 'init' action as some installs were croaking with 'undefined function add_settings_error'
* Enhancement: Add support for certain add-ons to display their settings in the main bbpnns settings screen.

= 2.0.1 =
* Fix: 1.x -> 2.x converter bug not saving settings correctly.

= 2.0 =
* Major rewrite, added better settings screen and add-on interface.

= 1.18.6 =
* Improvement: Support for environments that do not provide mb_convert_encoding().

= 1.18.5 =
* Fix: Fix PHP notice by setting default value to wp_mail_error property in case third-party mailers fail and don't call the wp_mail_error action.

= 1.18.4 =
* Fix: Correctly handle DOMDOcument calls on PHP older than 5.3.6.

= 1.18.3 =
* Fix: Added back the filters to stop default notifications in some cases where removing the core notification action wasn't working.

= 1.18.2 =
* Fix: Future Publish was not working.
* Fix: Better handling of blocking bbPress core notifications if Overrides is on, to make sure we don't send out multiple messages (ours plus the default one).
* Cleanup: Commented out some notifications code that are no longer relevant.

= 1.18.1 =
* Updated Tested up to
* Added improved admin notice code and bbpnns-rbe April/2018 promo.

= 1.18 =
* Added support for topic-content and topic-excerpt tags in replies.
* Added check and warning of needed bridge plugins to play nicely with supported membership/permission plugins.

= 1.17 =
* Fix: notify_on_save was not handling future dated publishing at all.

= 1.16.2 =
* Fix DOMDocument to work with non-UTF8 characters. Thanks to @yinbit for the testing environment.

= 1.16.1 =
* Don't assume UTF-8 loading the text in DOMDocument to process image CIDs and convert links.

= 1.16 =
* Add support for embedded images in notifications.
* Capture case when topic_id does not get passed to notify_new_reply()

= 1.15.11 =
* Adjust parameters for send_notification().

= 1.15.10 =
* Add post type, topic/reply id and forum id to send_notification() so they can be used in bbpnns_filter_email_body_for_user and bbpnns_filter_email_subject_for_user filters.

= 1.15.9.1 =
* Fix: Removed debugging left behind in 1.15.9

= 1.15.9 =
* Decode quotes in topics and body.

= 1.15.8 =
* Refactor topic-url code in reply notifications to improve performance.

= 1.15.7 =
* Added support for topic-title, topic-author, and topic-author-email tags in the reply subject.

= 1.15.6 =
* Remove surety message.

= 1.15.5 =
* Fix: apply bbpnns_topic_url filter when processing topic_url inside a reply as well.

= 1.15.4 =
* Added: bbpnns_core_subscribers filter.

= 1.15.3 =
* Added: topic-title, topic-author, and topic-author-email tags are now available in replies.

= 1.15.2 =
* Fix: unchecked iconv function was breaking some installs.

= 1.15.1 =
* Fix: Plain text mailouts had broken UTF-8 characters.

= 1.15 =
* Added: bbpnns_is_in_effect filter to help identify if Core Overrides are on or if a user belongs to a notifiable role.

= 1.14.3 =
* Fix: Correctly handling encoded entities.
* Fix: Check that iconv_mime_encode is available before trying to use it.
* Added: bbpnns signature in email headers to help with troubleshooting.

= 1.14.2 =
* Fix: Multipart messages are now working nicely with Mailgun and regular wp_mail calls.
* Added: HTML to text converter is now handling images, replacing the html with their alt value.

= 1.14.1 =
* Fix: Mailgun is replacing our multipart/alternative header boundary, so now admins can chose whether to send HTML, Plain Text, or Multipart messages.

= 1.14 =
* New: WYSIWYG emails, complete with automatic multipart text version for non HTML clients.
* New: Added user-name tags support.

= 1.13.1 =
* Fix: Bad copy/paste on previous commit, which replaced the body with the subject line.

= 1.13 =
* New: Added tags to get topic and reply author email.

= 1.12 =
* New: Take over notifications for bbPress' Core Subscriptions
* New: Decide whether authors must receive their own notifications or not

= 1.11.1 =
* ISIPP/SuretyMail partnership announcement.

= 1.11 =
* Added: calling set_time_out(0) if doing cron. This should help people who are not getting all mailouts sent due to too many recipients.

= 1.10 =
* Minor bug fix: [topic-forum] and [reply-forum] tags were missing from list of available tags, although functionality was fine.
* Add: [topic-url] is now available in replies, too.

= 1.9.4 =
* New Feature: No longer add topic/reply author to the recipient list.

= 1.9.3 =
* Fix: Replace <code>mb_internal_encoding()</code> with <code>iconv_get_encoding()</code> as at least one host didn't have <code>mb_string</code> enabled.
* Add: Admin option to enable or disable Subject line encoding. Admin -> Settings -> Forums -> E-mail Notifications -> Encode Topic and Reply Subject line.
* Add: uninstaller.


= 1.9.2 =
* Fix filters bbpnns_filter_email_subject_in_build and bbpnns_filter_email_body_in_build to pass $type and $post_id

= 1.9.1 =
* New action: bbpnns_email_failed_single_user, allows for better handling of failed emails. Params: $user_info, $filtered_subject, $filtered_body, $recipient_headers
* New action: bbpnns_before_wp_mail, executed immediately before wp_mail() call. Params: $user_info, $filtered_subject, $filtered_body, $recipient_headers
* New action: bbpnns_after_wp_mail, executed immediately after wp_mail() call. Params: $user_info, $filtered_subject, $filtered_body, $recipient_headers

= 1.9 =
* New Filter: bbpnns_skip_notification
* New Filter: bbpnns_available_tags
* New Action: bbpnns_after_email_sent_single_user
* New Action: bbpnns_after_email_sent_all_users
* Change: Only filter subject and body if user is OK to receive message
* Change: Reduce DB calls by one per user
* Change: stop using PHP4-style pass-by-reference. PHP5 always passes by reference now.
* Change: Improve Encoding of subject line

= 1.8.2.1 =
* Fix: added a workaround for emails with UTF-8 Characters in the subject line that weren't being sent.

= 1.8.2 =
* Added: support for people using wpMandrill and getting emails without newlines. We turn on nl2br momentarily while sending out our emails.
This option can be overridden by using the filter 'bbpnns_handle_mandrill_nl2br'.

= 1.8.1 =
* Fix: no longer return if wp_mail fails for a given email address. This was an issue for people using wpMandrill with an address in the blacklist.

= 1.8 =
* New Filter: bbpnns_post_status_blacklist
* New Filter: bbpnns_post_status_whitelist
* New Action: bbpnns_before_topic_settings
* New Action: bbpnns_after_topic_settings
* New Action: bbpnns_after_reply_settings
* New Action: bbpnns_register_settings

= 1.7.3 =
* Remove admin message as it's not getting dismissed properly.
* Update tested up to.

= 1.7.2 =
* Fix parameters for 'bbp_new_reply' filter
* Added call to 'bbp_get_reply_forum_id()' in case the forum_id was blank (should no longer happen with 'bbp_new_reply' filter fix)

= 1.7.1 =
* Notify about existence of Opt-Out add-on

= 1.7 =
* Added support for Opt-Out add-on
* Added labels to all input fields

= 1.6.7 =
* Added support for tags [topic-forum], and [reply-forum]. ([Towfiq I.](https://wordpress.org/support/topic/feature-forum-name-in-email))

= 1.6.6.1 =
* Removed Pro message.

= 1.6.6 =
* Added subject filter in _build_email: bbpnns_filter_email_subject_in_build
* Added body filter in _build_email: bbpnns_filter_email_body_in_build
* Renamed filter: bbpnns-filter-recipients => bbpnns_filter_recipients_before_send
* Renamed filter: bbpnns-filter-email-subject => bbpnns_filter_email_subject_for_user
* Renamed filter: bbpnns-filter-email-body => bbpnns_filter_email_body_for_user

= 1.6.5 =
* Added user-contributed filters: bbpress_reply_notify_recipients, and bbpress_topic_notify_recipients

= 1.6.4 =
* Added filters: bbpnns-filter-recipients, bbpnns-filter-email-subject, and bbpnns-filter-email-body

= 1.6.3.1 =
* Fixed: buggy dismiss link in previous commit.

= 1.6.3 =
* Added notice about bbPress Notify Pro project at Kickstarter.

= 1.6.2 =
* Fix bug where topic and reply post_types were not set in time to send post.
* Only send notification if post_status is publish, besides not being spam.
* Adjustments to notify_on_save
* Added tests for notify_on_save

= 1.6.1 =
* Passing $post_id and $title variables to filters added in 1.6.

= 1.6 =
* Added support for filters 'bbpnns_topic_url', 'bbpnns_reply_url', and 'bbpnns_topic_reply'

= 1.5.5 =
* Improved Tests
* Renamed some variables.

= 1.5.4 =
* Fix: Make sure bbPress is installed and avoid race conditions when loading.

= 1.5.3 =
* Fix: corrected missing newlines in topic/reply content email.

= 1.5.2 =
* Fix: admin-only emails not working due to missed boolean casting.

= 1.5.1 =
* Fixed bug, 'hidden forum override reply' setting not registered
* Added filters: bbpnns_skip_topic_notification, bbpnns_skip_reply_notification, bpnns_excerpt_size, bbpnns_extra_headers

= 1.5 =
* Added override option to only send emails to Admins in case a Forum is hidden.
* Added tests

= 1.4.2 =
* Tweak: make sure we have unique recipients. In some installs, duplicate emails were being sent.

= 1.4.1 =
* Fixed: preg_replace error in some installs.

= 1.4 =
* Fixed: Strict notices.
* Added: Settings link in Plugins page.
* Added: Logging failed wp_mail call.
* Added: Option to send notifications when adding/updating a topic or reply in the admin.
* Added: Enforce replacement of <br> tags for newlines.

= 1.3 =
* New: Added background notifications

= 1.2.2 =
* Fixed: bug that was sending emails to everyone if no role was saved.
* Fixed: no longer using 'blogadmin' as default, but 'administrator' upon install.

= 1.2.1 =
* Added back old plugin deactivation
* Bug fix for topic author not displaying when anonymous by Rick Tuttle

= 1.2 =
* Improved role handling by Paul Schroeder.

= 1.1.2 =
* Fixed edge case where user doesn't select any checkbox in recipients list.
* Array casting in foreach blocks.

= 1.1.1 =
* Fixed load_plugin_textdomain call.

= 1.1 =
* Fixed methods called as functions.

= 1.0 =
* No-spam version forked.


== Upgrade Notice ==
No special notices at this time.
