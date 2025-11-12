=== Converso – WhatsApp Chat Plugin ===
Contributors: sujitmagar
Tags: whatsapp, chat, support, customer-support, live-chat
Requires at least: 5.9
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==

Converso adds a customizable WhatsApp call-to-action button to your WordPress site. Use the admin dashboard to manage multiple agents, craft dynamic greeting placeholders, and pick from twenty button styles that can be anchored to any corner of the screen.

=== Highlights ===
- Configure the basics: business name, CTA copy, display delay, scroll trigger, and offline visibility.
- Store unlimited WhatsApp agents with photos, greetings, locations, and a default pick.
- Define reusable dynamic fields that generate `{placeholders}` for message templates.
- Choose from 20 pre-built WhatsApp button styles and position them in any corner.
- Works with the default WordPress media library and settings API.

A starter translation template (`languages/converso-en_US.po`) is included so you can localize the interface quickly.

== Installation ==

1. Upload the `converso` folder to `/wp-content/plugins/`.
2. Activate “Converso – WhatsApp Chat Plugin” in **Plugins → Installed Plugins**.
3. Open **Converso** in the admin menu to configure the tabs.

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

== Screenshots ==

1. Converso settings overview with tab navigation.
2. Agent repeater with profile photo picker.
3. Dynamic fields manager showing callable placeholders.
4. Button style gallery and position selector.

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