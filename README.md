# UM Fields with Links
Extension to Ultimate Member to include a Link in the Registration and Profile Form's Field Value and/or Field Label.

## UM Settings
1. UM Settings -> Appearance -> Profile
2. Field Label With Link - meta_key, url, title, icon (one set per line) - Enter the meta_key comma separated with the url, title and UM icon. Placeholder in the url: <code>{userid}</code>, UM Forms label placeholder: <code>{link], {/link}</code>
3. Field Value With Link - meta_key, url, title, icon (one set per line) - Enter the meta_key comma separated with the url, title and UM icon. Placeholders in the url: <code>{userid}, {value}</code>
4. Plugin is using the UM Settings -> Access -> Other -> "Allow external link redirect confirm" if clicked.

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

## Installation
Download the zip file and install as a WP Plugin, activate the plugin.
