=== FX Mega Menu Builder ===
Contributors: tossraj
Tags: mega menu, menu builder, nav menu, custom menu, navigation
Requires at least: 5.6
Tested up to: 7.0
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A React-powered drag-and-drop Visual Editor to easily design custom WordPress mega menus.

== Description ==

A powerful, user-friendly WordPress plugin that enables you to design and display custom mega menus using a modern drag-and-drop Visual Editor built on React. Easily construct column layouts, import menus/pages, and style custom menus with background colors and link configurations tailored for desktop and mobile viewports.

[![WordPress Plugin](https://img.shields.io/badge/WordPress-Plugin-blue.svg)](https://wordpress.org/plugins/)
[![GPLv2 Licensed](https://img.shields.io/badge/License-GPL%20v2%20or%20later-orange.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

---

## Key Features

- **React-Powered Visual Editor**: Clean, responsive layout builder inside the WordPress administration dashboard.
- **Dynamic Columns**: Drag-and-drop column grid system with automated padding and gap calculations.
- **AJAX Search Selector**: Search and select any posts, pages, categories, or custom taxonomies dynamically.
- **Custom Swatch Color Pickers**: Custom popover color pickers (powered by `react-colorful`) to configure backgrounds easily.
- **Mobile Responsive Adjustments**:
  - Independent **Mobile Drawer Background** and **Mobile Link Wrapper Background** settings.
  - Aligned toggle buttons with separated tap targets for reliable sub-menu opening on mobile.
- **Theme Style Inheritance**: Designed to inherit typography and text styles from your default active theme for native styling.
- **Fully Sanitized & Secure**: Prepared SQL queries and sanitized settings options to keep your WordPress site safe.

---

## Installation

1. **Download & Upload**:
   Download the plugin ZIP file and upload the `mega-menu-builder` folder to the `/wp-content/plugins/` directory of your WordPress site.
2. **Activation**:
   Navigate to **Plugins** in the WordPress dashboard and click **Activate** under **Mega Menu Builder**.
3. **Configure Settings**:
   Navigate to the **Mega Menu Builder** section in the admin sidebar menu to create your mega menu layout configurations.

---

## Database Settings Structure

FX Mega Menu Builder stores configuration objects in the `mmb_menus` option key in the `wp_options` table. Supported settings keys are:
- `width`: Max-width (e.g. `100%`, `1200px`, `container`).
- `background`: Desktop background color (hex string).
- `mobileBackground`: Mobile panel drawer background color (hex string).
- `mobileLinkBackground`: Mobile link container background color (hex string).
- `position`: Alignment orientation (`left`, `center`, `right`, `full`).
- `animation`: Entrance animation style (`fade`, `slide`, `zoom`, `none`).
- `showOnHover`: Expand trigger (`true` for hover, `false` for click).

---

## Author

**Shiv Singh**  
GitHub: [@tossraj](https://github.com/tossraj)

---

## License

This plugin is open-source software licensed under the [GNU General Public License v2 or later](https://www.gnu.org/licenses/gpl-2.0.html).
