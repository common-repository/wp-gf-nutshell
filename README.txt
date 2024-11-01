=== Nutshell Gravity Forms WordPress Plugin ===

Contributors: radboris, zwilson, fsimmons
Donate link: https://www.gulosolutions.com/
Tags: api, forms, gravityforms, Nutshell, crm
Requires at least: 3.0.1
Tested up to: 5.3
Stable tag: 1.1.18
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WordPress plugin that integrates GravityForms and the Nutshell API. Update Nutshell user accounts on form submission with selected fields.

== Description ==

The plugin creates Nutshell entries form GravityForms submissions. It allows users to select form field as notes for nutshell entries. Provide email for each available form. The info will be routed to the specific Nutshell user

== Installation ==

* Include the plugin in the main composer file under the package and require keys:
   ```
   {
      "url": "https://github.com/GuloSolutions/gravityforms-nutshell-integration.git",
      "type": "git"
   }
   ```

   ```
     "gulo-solutions/gravityforms-nutshell-integration": "dev-master"
   ```
1. run `composer update` or `composer install`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Under the Settings tab, add the Nutshell API keys. View the active GravityForms on the site and enter a Nutshell user email  that will be associated with the form. Subsequent form submissions will be directed to this email. You can also choose which form fields will be designated as note to the Nutshell user

== Frequently Asked Questions ==

* What needs to be done in a future release?

  Add Nutshell tags to Contacts from admin

== Upgrade Notice ==

== Screenshots ==

== Changelog ==

1.1.18 - 2019-11-26

* Fixed: tags duplicate display

* Added: singleton

1.1.17 - 2019-11-22

* Fixed: tags display

* Fixed: transients loading tags

* Add checks for API info

1.1.16 - 2019-11-08

* Fixed: display Contact tags if no tags have been selected yet

1.1.15 - 2019-11-08

* Add checks for form fields
* Append email and phone number to existing contact info

1.1.14 - 2019-11-06

* Add id for submit button
* Remove extra `output` var admin setting

1.1.13 - 2019-11-05

* Remove pre_render GF hook
* Add checks for Gravity Forms classes before activation
* Disable plugin if GF is inactive

1.1.12 - 2019-10-22

* Added chosen JS lib for backend dropdown

1.1.11 - 2019-10-01

* Fix field mapping
* Add custom fields tp backend UI
* Add source url as note

1.1.10 - 2019-09-27

* Add webpack and scripts

1.1.9 - 2019-09-27

* Fix tag array passed to Nutshell

1.1.8 - 2019-09-25

* Fix plugin tag issue

1.1.7 - 2019-09-25

* Fix Nutshell tag name issue when tag is more than a single word

1.1.6 - 2019-09-25

* Fix parsing issue

1.1.5 - 2019-09-20

* Add dropdown fields for settings derived from the Nutshell API, including users and tags
* Add Nutshell API methods to allow appending tags and source url to a form and Nutshell Contact

1.1.4 - 2019-08-01

* Add settings link

1.1.3 - 2019-08-01

* Admin form improvements: fixed: placeholder text and value text are now separate
* Author name fixed

1.1.2 - 2019-07-31

- Minor improvements and cleanup
- Add utm code

1.1.1 - 2019-05-22

* Changes to the admin interface, plugin name, sanitization of input, admin forms
* Catch NUTSHELL API errors if no key or missing user

1.1.0 - 2019-04-09

* Latest tag only stable

1.0.3 - 2019-04-09

* Add more Nutshell API methods
* Remove API info after uninstall
* Run frontend functionality if API keys exist

1.0.2 - 2019-03-04

* Remove unused imports

1.0.1 - 2019-03-04

* Add API creds in admin section

1.0.0 - 2019-03-01

* Initial release
