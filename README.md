# Menu Items Display — WordPress Plugin

Simple plugin jo ek "Menu Item" custom post type register karta hai, admin meta fields (price, availability) add karta hai, Bootstrap CSS enqueue karta hai (CDN se) aur ek shortcode provide karta hai: `[menu_items]` — jisse menu items Bootstrap card layout mein dikhte hain.

## Features
- "Menu Item" custom post type (`menu_item`)
- Meta box: Price (float) aur Availability (checkbox)
- Frontend shortcode `[menu_items]` — Bootstrap cards mein items dikhata hai
- Bootstrap CSS CDN ko front-end pe enqueue karta hai (theme agar Bootstrap already use kar raha ho to optional)
- Activation/deactivation pe rewrite rules flush karta hai

## Requirements
- WordPress 5.0+
- PHP 7.2+ (recommend 7.4+)
- Theme that supports Bootstrap (optional; plugin enqueues Bootstrap from CDN by default)

## Installation
1. Folder banaye: `wp-content/plugins/wp-menu-items`
2. Plugin file save karein: `wp-menu-items/wp-menu-items-plugin.php` (provided file)
3. WordPress admin → Plugins → Activate "Menu Items Display"

## Quick Start
1. Admin menu mein "Menu Items" section milega — Add New se items add karein.
2. "Menu Details" meta box mein `Price` aur `Available` set karein.
3. Kisi page/post mein shortcode add karein:
   - Simple: `[menu_items]`
   - Limit items: `[menu_items count="6"]`

Example:
```html
<!-- Add this to any page or post -->
[menu_items count="6"]
```

## Shortcode behavior
- Shortcode function: `mid_menu_items_shortcode`
- Default `count` = 10
- Displays published `menu_item` posts
- Each card shows:
  - Title (link to single post)
  - "Unavailable" badge if availability meta is not set to `1`
  - Trimmed description/excerpt (≈120 chars)
  - Price formatted with two decimals (e.g. $9.99)
  - "View" button linking to post

## Developer notes / Customization
- Post type slug: `menu_item`. Archive slug: `menu`.
- Meta keys:
  - Price: `_mid_price` (stored as float)
  - Availability: `_mid_available` (`'1'` when available)
- To change Bootstrap version or stop enqueueing plugin's Bootstrap, edit or remove `mid_enqueue_assets()` in plugin:
  - To prevent plugin from loading Bootstrap, add in your theme/plugin:
    ```php
    add_action( 'wp_enqueue_scripts', function() {
        wp_dequeue_style( 'mid-bootstrap' );
    }, 20 );
    ```
- To customize output markup, you can copy and adapt the shortcode function logic in your theme (or create a custom shortcode) by calling `do_shortcode()` or re-implementing based on `mid_menu_items_shortcode`.
- To query programmatically:
  ```php
  $q = new WP_Query([
    'post_type' => 'menu_item',
    'posts_per_page' => 6,
    'post_status' => 'publish',
  ]);
  ```

## Internationalization
- Currently strings are hard-coded in plugin. If you need translations, wrap user-facing strings with `__()` / `_e()` and load a text-domain (not implemented in v1).

## Known limitations & TODO
- No category/taxonomy for menu items (you can add a taxonomy if you need categorization).
- No frontend filters (availability/category) in shortcode — can be added later.
- No single-template override shipped; uses default single post template of the theme.

## Activation / Deactivation
- On activation plugin registers CPT and flushes rewrite rules.
- On deactivation plugin flushes rewrite rules.

## Uninstall
- This plugin does not delete posts or metadata on uninstall. If you need full cleanup, implement `register_uninstall_hook()` to remove CPT entries and meta.

## Changelog
- 1.0 — Initial release: CPT, meta box, shortcode, bootstrap enqueue, activation flush.

## License
GPLv2 or later.

## Support
- For issues / feature requests, open an issue in the repository or contact the plugin author.
- If you want, main plugin code ko customize kar doon: categories add karna, REST endpoints banana, single template provide karna, ya shortcode filters add karna — bataiye kya chahiye.

```
