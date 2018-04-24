=== LegalForms ===
Contributors: legalthings
Donate link:
Tags:
License: MIT
License URI: https://raw.githubusercontent.com/legalthings/legalform-wp-plugin/master/LICENSE
Requires at least: 3.5
Stable tag: 1.2.9
Tested up to: 4.9.5

LegalForms plugin

== Description ==

Create a shortcode to load a LegalDocx template from a given LegalThings installation and show it on your WordPress site. After the form is filled out, it will be posted to a LegalThings LegalFlow of your choice.

Note: This plugin sends form data to LegalThings, a third party.

Usage:
```
[legalforms template="template_name" flow="flow_name" material="true"]
```

== Installation ==

Upload the LegalForms plugin to your blog, activate it, then enter your LegalThings installation URL in the settings.

== Frequently Asked Questions ==


== Screenshots ==


== Changelog ==
= 1.2.9 =
- Bug fixes

= 1.2.8 =
- Added support for terms and services (optional)
- Option to automatically go to the next step in the flow
- Ability to go from register / login back to the form
- Reset password

= 1.2.7 =
- Fixed support for non-standard plugin directories
- Added loading spinner
- Added option to disable plugin Bootstrap

= 1.2.6 =
- Bug fixes

= 1.2.5 =
- Bug fixes related to validation

= 1.2.4 =
- Bug fixes

= 1.2.3 =
- Code optimizations in expectation of delegated access

= 1.2.2 =
- Isolate css

= 1.2.1 =
 - Add support for LegalFlow aliases

= 1.2 =
- Support for standard login credentials
- fix datetimepicker

= 1.1 =
- Split login and register into different views

= 1.0.1 =
- fix dropdown
- fix datepicker

= 1.0 =
- Initial Revision
