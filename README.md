<div align="center">

# 💬 Connectapre - Smart Contact Button

[![WordPress Plugin Version](https://img.shields.io/badge/version-1.0.0-blue.svg)](https://github.com/Sujitale07/converso-wordpress-plugin)
[![WordPress](https://img.shields.io/badge/WordPress-5.9+-blue.svg)](https://wordpress.org/)
[![PHP Version](https://img.shields.io/badge/PHP-7.4+-purple.svg)](https://php.net/)
[![License](https://img.shields.io/badge/license-GPLv2-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

**Smart WhatsApp integration for WordPress with intelligent agent routing, dynamic greetings, and conversion tracking.**

[Features](#-features) • [Installation](#-installation) • [Usage](#-usage) • [FAQ](#-faq) • [Roadmap](#-roadmap)

</div>

---

## ⚠️ Disclaimer

**WhatsApp** is a registered trademark of **Meta Platforms, Inc.**  
This plugin is not affiliated with, endorsed, or sponsored by Meta or WhatsApp.

---

## ✨ Features

Connectapre adds a smart click-to-chat button for WhatsApp, routing visitors to the right agent based on country, business hours, login status, scroll position, and more. With multi-agent support, dynamic greetings, and conversion tracking, Connectapre ensures conversations start at the right moment.

### 🎯 Highlights

- **🎨 20+ Pre-built Button Styles** - Choose from beautiful, pre-designed WhatsApp button skins
- **👥 Multi-Agent Support** - Store unlimited WhatsApp agents with photos, greetings, and locations
- **🌍 Smart Routing** - Route visitors based on location, business hours, and login status
- **⚡ Dynamic Fields** - Create reusable `{placeholders}` for personalized message templates
- **📊 Conversion Tracking** - Track clicks and visitor engagement
- **🎛️ Flexible Positioning** - Place the widget in any corner of your site
- **🌐 Translation Ready** - Includes starter translation template for easy localization
- **📱 Responsive Design** - Works seamlessly on desktop and mobile devices

---

## 📦 Installation

### Method 1: WordPress Admin

1. Download the plugin ZIP file
2. Navigate to **Plugins → Add New** in WordPress admin
3. Click **Upload Plugin** and select the ZIP file
4. Click **Install Now** and then **Activate**

### Method 2: Manual Installation

1. Upload the `connectapre` folder to `/wp-content/plugins/`
2. Activate **Connectapre - Smart Contact Button** in **Plugins → Installed Plugins**
3. Open **Connectapre - Smart Contact Button** in the admin menu to configure

---

## 🚀 Usage

### 📋 General Tab

Configure the core settings:

- **Business Information** - Set your business name and CTA text
- **Timing Options** - Control when the button appears (seconds after load, scroll percentage)
- **Visibility Controls** - Toggle "Hide When Offline" and "Enable WhatsApp CTA"

### 👤 Agents Tab

Manage your WhatsApp representatives:

- Add unlimited agents with photos, phone numbers, greetings, and locations
- Mark a default agent using the radio button
- Use "Is Offline" checkbox to manage agent availability
- Front-end logic automatically routes to available agents

### 🔧 Dynamic Fields Tab

Create personalized message templates:

- Define reusable tokens like `{FirstName}` or `{Referrer}`
- Auto-generated callables for use in templates
- Perfect for custom integrations and personalized greetings

### 🎨 Styling & Position Tab

Customize the appearance:

- Select from 20 predesigned WhatsApp button skins
- Position widget: top-left, top-right, bottom-left, or bottom-right
- Real-time preview before saving
- Fully customizable to match your brand

---

## 🔌 External Services

This plugin connects to third-party APIs for enhanced functionality:

| Service | Purpose | Data Sent | Links |
|---------|---------|-----------|-------|
| **Photon API** (Komoot) | Location search in admin settings | Search queries | [Terms](https://www.komoot.com/terms-of-service) • [Privacy](https://www.komoot.com/privacy) |
| **Nominatim API** (OpenStreetMap) | Reverse geocoding for agent routing | Visitor GPS coordinates | [Usage Policy](https://operations.osmfoundation.org/policies/nominatim/) • [Privacy](https://wiki.openstreetmap.org/wiki/Privacy_Policy) |
| **IP-API.com** | Fallback location detection | Visitor IP address | [Terms & Privacy](https://ip-api.com/docs/legal) |
| **WhatsApp** (wa.me) | Chat initiation | Redirect to WhatsApp | [Terms](https://www.whatsapp.com/legal/terms-of-service) • [Privacy](https://www.whatsapp.com/legal/privacy-policy) |

---

## ❓ FAQ

### Does the WhatsApp button respect the "Enable WhatsApp CTA" toggle?

In the current 1.0.0 build, the button always renders once the plugin is active. A future release will wire the toggle into the front-end output.

### Can I extend the saved options on the front end?

Yes! All settings are stored via the WordPress options API (`get_option`). Developers can consume those values in custom templates or filters.

### Is the plugin translation-ready?

Absolutely! A starter translation template (`languages/connectapre-smart-contact-button-en_US.po`) is included for easy localization.

### Does it work with page builders?

Yes, Connectapre works with all major page builders including Elementor, Divi, Beaver Builder, and more.

---

## 📝 Changelog

### Version 1.0.0
- 🎉 Initial release with admin settings
- 👥 Agent manager with multi-agent support
- 🔧 Dynamic fields system
- 🎨 20 button presets
- 📊 Basic conversion tracking

---

## 🗺️ Roadmap

We're constantly improving Connectapre! Here's what's coming:

- [ ] **Front-end Toggle** - Respect enable/disable toggle on the front end
- [ ] **Advanced Targeting** - Business hours, country-based routing, login state detection
- [ ] **Auto-Greetings** - Integrate dynamic fields into automatic WhatsApp messages
- [ ] **Enhanced Tracking** - Advanced conversion tracking and analytics
- [ ] **Uninstall Cleanup** - Proper data cleanup on plugin removal
- [ ] **A/B Testing** - Test different button styles and positions
- [ ] **Custom Triggers** - Exit intent, time on page, and more

---

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

---

## 📄 License

This plugin is licensed under the **GPLv2 or later**.

```
Copyright (C) 2026 Sujit Ale

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
```

---

## 👨‍💻 Author

**Sujit Ale** - [@sujitale07](https://github.com/Sujitale07)

---

<div align="center">

**Made with ❤️ for the WordPress community**

[⬆ Back to Top](#-connectapre---smart-contact-button)

</div>
