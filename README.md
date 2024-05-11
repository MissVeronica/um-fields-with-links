# UM Fields with Links
Extension to Ultimate Member to include a Link in the Registration and Profile Form's Field Value and/or Field Label.

## UM Settings -> Appearance -> Profile
1. Field Label With Link - meta_key, url, title, icon (one set per line) - Enter the meta_key comma separated with the url, title and UM icon. Placeholder in the url: <code>{userid}</code>, UM Forms label placeholder: <code>{link], {/link}</code>
2. Field Value With Link - meta_key, url, title, icon (one set per line) - Enter the meta_key comma separated with the url, title and UM icon. Placeholders in the url: <code>{userid}, {value}</code>

## UM Settings -> Advanced -> Developers -> Redirect
1. Plugin is also using the "Allow external link redirect confirm" if clicked.

## Examples ##
1. Field Label With Link - meta_key, url, title, icon (one per line)
2. UM Forms Builder Labels: <code>First Key {link} link {/link} in label</code> - <code>Last key {link} Name</code>

<code>first_key, https://ultimatemember.com/, UM Home Page, um-faicon-external-link
last_key, /user/{userid}/, Test User link, um-faicon-external-link</code>

2. Field Value With Link - meta_key, url, title, icon (one per line)

<code>first_key, https://ultimatemember.com/, UM Link , um-faicon-external-link
last_key, /userinfopage/{userid}/, Link Title , um-faicon-external-link
another_key, /page/showme.php?text={value}, my page , um-faicon-external-link</code>

## Updates
1. Version 2.0.0 Included also Registration forms.
2. Version 2.1.0 Updated to UM 2.8.5
3. Version 2.2.0 Updated code for label links
4. Version 2.2.1 Code improvements for labels
5. Version 2.3.0 Supports UM Field icon in the label.
6. Version 2.4.0 Support for custom fields in Members Directory
7. Version 2.5.0 Code improvements for fields in Members Directory
8. Version 2.5.1 Code improvements
9. Version 2.6.0 Support for radio and checkbox UM fields

## Installation
Download the zip file and install as a WP Plugin, activate the plugin.
