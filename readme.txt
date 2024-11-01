=== Sirdata CMP ===
Contributors: AC WEB AGENCY
Tested up to: 6.6.1
Requires at least: 6.0
Requires PHP: 7.4
Stable Tag: 1.2.6
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html
Text Domain: sirdata-cmp

Manage consent and handle data processing.

== Icons ==
- icon-128x128.png
- icon-256x256.png

== External Services ==

This plugin relies on the following third-party service to function correctly:

**Service Name**: SirdataCMP
- **Description**: This plugin uses Sirdata to manage consent frameworks and handle data processing. When a user interacts with consent prompts, their data may be sent to Sirdata for processing.
- **Service URL**: [SirdataCMP](https://cmp.sirdata.io/)
- **Privacy Policy**: [SirdataCMP Privacy Policy](https://www.sirdata.com/fr/Vie-Privee-Marketing?_gl=1*1epkuje*_gcl_au*NzE4MzAxNzQ4LjE3MTgwMjkxMDIuNDIwMjcxNzk2LjE3MTkyMTg0ODMuMTcxOTIxODQ4Mw..*_ga*MTIyOTc1MjExMC4xNzE4MDI5MTAy*_ga_J0V9M015VY*MTcyMzA5NzA0Mi42LjEuMTcyMzA5NzA3Mi4wLjAuMTA4Mzg3NzgyNg..)

**Scripts Used**:
- `https://cache.consentframework.com/js/pa/$partner_id/c/$config_id/stub`: This script is used to load the initial stub for the consent framework. It helps in initializing the consent prompt on the user's site.
- `https://choices.consentframework.com/js/pa/$partner_id/c/$config_id/cmp`: This script is responsible for loading the full consent management platform interface. It handles user interactions with the consent prompts and sends the necessary data to Sirdata.
- `https://gateway.sirdata.io/api/v1/public/cmp-api/external/register`: This endpoint is used to create account into  SirdataCMP.

By using this plugin, you agree to the terms and conditions and privacy policy of SirdataCMP.

== Frequently Asked Questions ==

= Where find my Partner ID and my Config ID ?  =

You have to login on your SirdataCMP account and take your IDs in your account.

== Changelog ==

= 1.0 =
* First version with only consent manager
