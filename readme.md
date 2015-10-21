=== Eventuous Plugin for WordPress ===

== Description ==

Plugin to get events from Eventuo.us Event Engine.

Visit Eventuo.us to create an account.  Visit your profile page once you have obtained an account to set up your Integrations.  You'll need at least one organization or venue.  Create an API key for it on that page.

== Installation ==

1. Upload the `eventuous` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress.  Input your API tokens.
3. Use the shortcode `[eventuous]` in your posts or pages.  Include Type and Template.  Type is the type of return you are expecting.  At this time only "events" is funcitonal but will add more soon.  This returns an event list.  The event list can be filtered by addding start_date and end_date params to the url string of the page calling the plugin.  Template is the folder under your /templates/ directory to use for styling and settings.

[eventuous type="events" template="example"]

== Changelog ==

= 0.1 =
This is the first release of the plugin.
