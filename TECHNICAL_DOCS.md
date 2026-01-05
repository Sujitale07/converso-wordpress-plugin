# Converso - Technical Documentation

## 1. Introduction
**Converso** is a WordPress plugin designed to enhance WhatsApp chat functionality on websites. It intelligently displays the most appropriate support agent to visitors based on various criteria such as geolocation, business hours, and more. The plugin features a modular architecture, a customizable frontend interface, and a robust admin settings area.

## 2. Architecture Overview
The plugin follows a modular, object-oriented architecture using PHP namespaces (`Converso\`).

- **Entry Point**: `converso-plugin.php`
- **Core Singleton**: `Converso` class (`includes/class-converso.php`)
- **Autoloader**: Custom PSR-4 style autoloader (`includes/core/class-loader.php`)

### Directory Structure
```
converso/
├── assets/                 # CSS, JS, and image resources
├── includes/
│   ├── admin/              # Admin dashboard logic
│   ├── core/               # Core system files (Autoloader, Logger)
│   ├── frontend/           # Frontend rendering and logic
│   │   ├── buttons/        # Button strategy classes
│   │   └── positions/      # Position strategy classes
│   ├── helpers/            # Helper utilities
│   └── modules/            # Business logic modules (Agents, Settings, etc.)
├── languages/              # Translation files
├── templates/              # (Optional) View templates
├── converso-plugin.php     # Main plugin file
└── uninstall.php           # Cleanup logic
```

## 3. Core Components

### 3.1 Initialization
The plugin is initialized via the `Converso` singleton class.
- **File**: `includes/class-converso.php`
- **Method**: `Converso::get_instance()`
- **Responsibility**: Initializes the autoloader, sets up hooks, and instantiates the Admin or Frontend components based on the context (`is_admin()`).

### 3.2 Admin Area
The admin interface is managed by the `Converso\Admin\Admin` class.
- **File**: `includes/admin/class-admin.php`
- **Menu Slug**: `converso`
- **Tabs**:
  - **General**: General plugin settings.
  - **Agents**: Management of support agents.
  - **Dynamic Fields**: Configuration for dynamic text replacement.
  - **Styling & Position**: Customization of the chat button appearance.

The admin logic is split into **Modules** located in `includes/modules/`. Each module (e.g., `Agents`, `General`) handles the rendering and saving of its specific settings.

### 3.3 Frontend Logic
The frontend display is handled by `Converso\Frontend\Frontend`.
- **File**: `includes/frontend/class-frontend.php`
- **Key Actions**:
  - `wp_enqueue_scripts`: Loads `converso.js` and styles.
  - `wp_footer`: Renders the chat button.
  - `wp_ajax_converso_reverse_geo`: Handles geolocation logic.

#### Button & Position Strategy
The plugin uses the **Strategy Pattern** for rendering buttons and their positions.
- **Buttons**: Classes in `includes/frontend/buttons/` (e.g., `BtnOne`, `BtnTwo`) implement the button HTML.
- **Positions**: Classes in `includes/frontend/positions/` (e.g., `BottomRight`, `TopLeft`) handle the CSS positioning logic.

### 3.4 Geolocation & Agent Selection
The plugin uses AJAX to dynamically select an agent.
1. **Client-side**: `converso.js` captures the user's interaction or load event.
2. **Server-side**: `converso_reverse_geo` AJAX handler receives coordinates (optional) or uses IP geolocation.
3. **Logic**: `Helper::filter_agent()` selects the best agent based on the user's location (City/State/Country) matching the agent's assigned rules.

## 4. Data Storage
The plugin primarily uses the WordPress Options API to store configuration.

| Option Name | Description |
|-------------|-------------|
| `converso_enable_whatsapp` | Boolean flag to enable/disable the plugin. |
| `converso_agents_data` | Array of configured agents and their rules. |
| `converso_sap_button_style_data` | Selected button style ID (e.g., `btn-1`). |
| `converso_sap_button_position_data` | Selected position ID (e.g., `bottom-right`). |
| `converso_settings` | General settings array. |

## 5. Key Classes & Methods

### `Converso\Helpers\Helper`
Static utility class for common operations.
- `get_client_location($lat, $lon)`: Determines user location.
- `filter_agent($agents, $location)`: Filters the list of agents based on location matches.
- `decode_dynamic_fields($agent)`: Replaces placeholders in agent data.

### `Converso\Frontend\Frontend`
- `render_converso_wp_button()`: Orchestrates the rendering of the button using the selected Strategy classes.
- `converso_reverse_geo()`: AJAX handler for resolving the correct agent and generating the WhatsApp link.

## 6. JavaScript API
**File**: `assets/js/frontend/converso.js`
- Handles the AJAX request to `converso_ajax.ajax_url`.
- Manages the user interaction with the chat button.

## 7. Hooks & Filters
Currently, the plugin relies on standard WordPress hooks (`wp_footer`, `admin_menu`, `init`). Custom hooks can be added in future versions for extensibility.
