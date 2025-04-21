# Plugin Description

This is a custom WordPress plugin developed to power a structured e-learning platform. It allows administrators to create and manage courses as custom post types directly from the WordPress admin interface, assign users to specific courses, and restrict access accordingly. The plugin also includes front-end rendering tools to display content-rich learning environments through shortcodes and Elementor compatibility.

The plugin is designed to work in conjunction with a **support plugin** installed on a separate WooCommerce-based webshop. This webshop plugin automatically completes relevant product orders and communicates with the e-learning platform via a REST API to create or update users and assign course access based on purchases.

## Features

- Create and manage custom post types (CPTs) for courses directly in the admin panel  
- Assign users to courses manually or through REST API integration  
- Front-end grid rendering of courses, modules, and knowledge base items via shortcodes  
- Access control for logged-in users based on assigned courses  
- Redirection logic for unauthorized access and login workflows  
- Admin profile extension for course access management per user  
- Admin tools to edit or delete custom post types  
- Elementor and Polylang compatible  
- Mobile-friendly sidebar navigation and user menu shortcodes  
- Kennisbank (knowledge base) post type for supplementary content

## Plugin Architecture

The plugin is built to be modular and extensible, using object-oriented principles and clear separation of admin and front-end logic.

- **Custom Post Types**:  
  Dynamically created via admin interface (e.g. `course-1`, `course-2`, etc.)  
  Plus a fixed `kennisbank` post type

- **Shortcodes**:  
  - `[academy_custom_post_grid]`: overview of available courses  
  - `[academy_module_grid]`: list of lessons (posts) in current course  
  - `[academy_kennisbank_grid]`: display of knowledge base articles  
  - `[associated_pages_list]`: dynamic navigation menu for current course  
  - `[user_menu]`: mobile-friendly profile/logout/language switcher menu

- **User Course Access**:  
  Stored in user meta field `user_courses` as a comma-separated list of course IDs  
  Automatically checked during page loads and archive views

- **REST API Endpoint**:  
  - `POST /wp-json/(restricted)`  
  Accepts user details and a course list (from webshop plugin)

- **Polylang Integration**:  
  All menu labels and page references can be translated via `pll_register_string`

- **Elementor Support**:  
  Registered custom post types are made available to Elementor automatically

## Technologies Used

| Area             | Technology / Approach                                      |
|------------------|------------------------------------------------------------|
| CMS Core         | WordPress plugin API, custom post types, meta fields       |
| Admin UI         | WordPress admin pages, options API, file uploads           |
| Access Control   | `current_user_can`, user meta, `template_redirect`         |
| REST API         | Custom `register_rest_route` endpoint                      |
| External Sync    | JSON requests from webshop plugin (WooCommerce)            |
| Front-end        | Shortcodes, Polylang, Elementor compatibility              |
| Media Handling   | WP Media Library (via JS uploader in admin panel)          |

## File Overview

- **Total files**: 28  
- **Total lines of code**: ~2,100  
- **Languages**: PHP, JavaScript (jQuery), HTML, CSS  

**Key directories and files**:
```
academy-plugin/
├── admin/
│   ├── classes/
│   │   ├── class-core.php
│   │   ├── class-cpt-handler.php
│   │   ├── class-course-access.php
│   │   ├── class-acces-restriction.php
│   │   ├── class-custom-rest-api.php
│   │   ├── class-user-profiles.php
│   │   ├── class-custom-emails.php
│   │   └── class-kennisbank.php
│   ├── pages/
│   │   ├── academy-page.php
│   │   ├── edit-cpt.php
│   │   └── tabs/tab-courses.php
│   └── js/
│       └── academy-admin.js
├── public/
│   ├── classes/
│   │   ├── class-academy-cpt-grid.php
│   │   ├── class-academy-module-grid.php
│   │   └── class-academy-kennisbank-grid.php
│   └── js/
│       └── academy-public.js
├── academy-plugin.php
```

## Integration with Support Plugin

A separate **support plugin** runs on a WooCommerce shop and ensures automatic synchronization after purchase:

- When an order is completed and contains a product from the `academy` category:
  - The SKU of the product is interpreted as the course ID (e.g., `c-1`)
  - User info and course ID are sent via a `POST` request to the e-learning site
  - If the user doesn’t exist, a new one is created and a password email is sent
  - The main plugin grants course access and saves it in the user’s profile

This creates a seamless purchase-to-access workflow between the webshop and the learning platform.

## Security

- All admin actions are protected using WordPress nonces and capability checks  
- REST API access is public by default, but can be limited with `permission_callback` if needed  
- Input validation and sanitization are enforced on all user and course data  
- Output is escaped using `esc_html`, `esc_attr`, and `wp_kses`  
- Admin interfaces are only visible to users with `manage_options` capability  

## Intended Use Case

This plugin is tailored for training platforms or academies that require:

- WordPress-based content management  
- Restricted access per user/course  
- Automatic access delivery after product purchase (via WooCommerce)  
- A custom dashboard and layout that fits within existing websites

## Author

**Tristan Riethorst**  
[bytris.nl](https://bytris.nl)  
Plugin developed for (restricted for privacy reasons)
