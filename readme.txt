=== Connectapre - Smart Contact Button ===
Contributors: sujitale07
Tags: whatsapp, chat, support, customer-support, live-chat
Requires at least: 5.9
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Disclaimer ==

WhatsApp is a registered trademark of Meta Platforms, Inc.
This plugin is not affiliated with, endorsed, or sponsored by Meta or WhatsApp.


== Description ==

Connectapre adds a smart click-to-chat button for WhatsApp, routing visitors to the right agent based on country, business hours, login status, scroll position, and more. With multi-agent support, dynamic greetings, and conversion tracking, Connectapre ensures conversations start at the right moment.

=== Highlights ===
- Configure the basics: business name, CTA copy, display delay, scroll trigger, and offline visibility.
- Store unlimited WhatsApp agents with photos, greetings, locations, and a default pick.
- Define reusable dynamic fields that generate `{placeholders}` for message templates.
- Choose from 20 pre-built WhatsApp button styles and position them in any corner.
- Works with the default WordPress media library and settings API.

A starter translation template (`languages/connectapre-smart-contact-button-en_US.po`) is included so you can localize the interface quickly.

== Installation ==

1. Upload the `connectapre` folder to `/wp-content/plugins/`.
2. Activate “Connectapre - Smart Contact Button” in **Plugins → Installed Plugins**.
3. Open **Connectapre - Smart Contact Button** in the admin menu to configure the tabs.

== Usage ==

**General tab**
- Fill in the business info and CTA text.
- Set timing options (seconds after load, % scrolled).
- Toggle “Hide When Offline” and “Enable WhatsApp CTA”.

**Agents tab**
- Add rows for each representative, including photo, phone, greeting, and location.
- Use the radio button to mark the default agent.
- The “Is Offline” checkbox stores status metadata (front-end logic can hook into it).

**Dynamic Fields tab**
- Create reusable tokens such as `{FirstName}` or `{Referrer}` by filling Name and Value.
- Callables are auto-generated so you can reference them in templates or custom integrations.

**Styling & Position tab**
- Pick one of 20 predesigned WhatsApp button skins.
- Anchor the widget to top-left, top-right, bottom-left, or bottom-right.
- Preview updates in real time before saving.

== Frequently Asked Questions ==

= Does the WhatsApp button respect the “Enable WhatsApp CTA” toggle? =
In the current 1.0.0 build the button always renders once the plugin is active. A future release will wire the toggle into the front-end output.

= Can I extend the saved options on the front end? =
Yes. All settings are stored via the WordPress options API (`get_option`). Developers can consume those values in custom templates or filters.

== External services ==

This plugin connects to several third-party APIs to provide location-based agent routing and location search features.

1. **Photon API (photon.komoot.io)**
   - **Usage**: Handles search queries in the admin settings to help you find and select locations for your agents.
   - **Data Sent**: Search query strings (e.g., "New York").
   - **Links**: [Terms of Use](https://www.komoot.com/terms-of-service), [Privacy Policy](https://www.komoot.com/privacy)

2. **Nominatim API (nominatim.openstreetmap.org)**
   - **Usage**: Converts visitor GPS coordinates (latitude/longitude) into address details (reverse geocoding) to route them to the nearest agent.
   - **Data Sent**: Visitor's latitude and longitude.
   - **Links**: [Usage Policy](https://operations.osmfoundation.org/policies/nominatim/), [Privacy Policy](https://wiki.openstreetmap.org/wiki/Privacy_Policy)

3. **IP-API.com**
   - **Usage**: Provides fallback location detection (Country, City) based on the visitor's IP address when GPS access is denied or unavailable.
   - **Data Sent**: Visitor's IP address.
   - **Links**: [Terms & Privacy](https://ip-api.com/docs/legal)

4. **WhatsApp (wa.me)**
   - **Usage**: The core functionality of this plugin redirects visitors to `https://wa.me/` to initiate chats.
   - **Data Sent**: User is redirected to WhatsApp servers.
   - **Links**: [Terms of Service](https://www.whatsapp.com/legal/terms-of-service), [Privacy Policy](https://www.whatsapp.com/legal/privacy-policy)



== Changelog ==

= 1.0.0 =
* Initial release with admin settings, agent manager, dynamic fields, and 20 button presets.

== Upgrade Notice ==

= 1.0.0 =
First public release. Double-check styling and positioning after updating.

== Roadmap ==

- Respect the enable/disable toggle on the front end.
- Add visitor targeting (business hours, country, login state).
- Integrate dynamic fields into automatic WhatsApp greeting messages.
- Implement uninstall cleanup and conversion tracking options.
