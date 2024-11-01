
## Nutshell Gravity Forms ##

* Contributors: radboris, zwilson, fsimmons
* Tags: api, forms, gravityforms, Nutshell, crm
* Requires at least: 3.0.1
* Tested up to: 5.3
* Stable tag: 1.1.18
* License: GPLv2 or later
* License URI: http://www.gnu.org/licenses/gpl-2.0.html

<a href="https://www.gulosolutions.com/?utm_source=github&utm_medium=website&utm_campaign=links">![Image](https://github.com/GuloSolutions/gravityforms-nutshell-integration/blob/master/public/images/banner-github.png)</a>


### Description ###

WordPress plugin that integrates GravityForms and Nutshell API. Update Nutshell user account on form submission with selected fields.

The plugin creates Nutshell entries form GravityForms submissions. It allows users to select form field as notes for nutshell entries. Provide email for each available form. The info will be routed to the specific Nutshell user

### Installation ###

1. Include the plugin in the main composer file under the package and require keys:
   #### package
   ```
   {
      "url": "https://github.com/GuloSolutions/gravityforms-nutshell-integration.git",
      "type": "git"
   }
   ```

   #### require
   ```
     "gulo-solutions/gravityforms-nutshell-integration": "dev-master"
   ```
1. run `composer update` or `composer install`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Under the Settings tab, add the Nutshell API keys. View the active GravityForms on the site and enter a Nutshell user email  that will be associated with the form. Subsequent form submissions will be directed to this email. You can also choose which form fields will be designated as note to the Nutshell user
