# Plan: Multiple Themes, Theme Switching and HTML Zip Upload

Goal: run several themes on the storefront and the admin panel, switch between them, and let an admin upload an HTML template as a `.zip` that is configured into a working storefront theme automatically.

## 1. What Already Exists

| Piece | Where | Status |
|---|---|---|
| Several storefront themes | `config/themes.php` → `shop.*` | Works, but a theme can only be added by editing the config file |
| Each channel picks its own theme | `channels.theme` column, `packages/Webkul/Shop/src/Http/Middleware/Theme.php` | Works |
| Theme gallery with an Activate button | `packages/Webkul/Admin/src/Http/Controllers/Appearance/ThemeController.php` (`index`, `impact`, `activate`), `packages/Webkul/Theme/src/ThemeCatalog.php` | Works |
| Child themes that fall back to a parent | the `parent` key, handled in `Themes::loadThemes()` (`packages/Webkul/Theme/src/Themes.php`) | Works, and it is what makes the zip upload realistic |
| Admin themes | `themes.admin` and `themes.admin-default` | Fixed: `ThemeViewFinder::setActiveTheme()` always forces `admin-default`, so there is no switching |
| Uploading a theme | — | Missing |

## 2. A Limit on the Zip Upload

A plain HTML template cannot automatically become a full store. Product listings, the cart, checkout and the customer account are Blade and Vue components wired to the backend, and no converter can work out that a `<div>` in someone's HTML is meant to be the cart.

The reliable approach: **an uploaded zip becomes a child theme of `default`.** The zip provides the look: header, footer, home page, CSS, JS, images and fonts. Every page it does not provide falls back to the default theme through the existing `parent` mechanism. The store keeps working after any upload, and the template changes only what it covers.

## 3. Phase 1: Register Themes From the Database

1. **New entity `Theme` in the Theme package**: Contract, Model, Proxy and Repository, registered in `config/concord.php`.
2. **Migration for a `themes` table** with these columns:
   - `id`
   - `code` (unique)
   - `panel` (`shop` or `admin`)
   - `name`, `version`, `parent`
   - `source` (`upload`)
   - `manifest` (json)
   - `status`
   - timestamps

   It must work on MySQL 8.0, MariaDB 10.11 and PostgreSQL 16.
3. **Merge at boot**: in `ThemeServiceProvider::register()`, merge the database rows into `config('themes.shop')` and `config('themes.admin')`. Cache the result under one key and clear that key whenever a theme is added, updated or removed. `Themes` reads config in its constructor, so the gallery, the channel middleware and `ThemeCatalog` pick up uploaded themes with no other changes.
4. **Config wins**: when a database row and a config entry share a code, the config entry is kept, so an upload can never shadow `default`.

## 4. Phase 2: Admin Theme Switching

1. **Add a resolver** in place of the hard-coded line in `ThemeViewFinder::setActiveTheme()`. It uses the first of these that is set:
   1. The logged-in admin user's own preference, stored in a new nullable column `admins.theme`.
   2. A store-wide setting, the `core_config` key `general.design.admin_theme.code` under Configuration.
   3. `config('themes.admin-default')`, as today.
2. **Add a theme picker** to the admin's account page and to Configuration, listing every entry in `themes.admin`.
3. **If the chosen admin theme's views or build are missing**, fall back to `default` so a broken theme can never lock anyone out of the panel.
4. **Uploaded zips are storefront-only.** Admin themes stay developer-built: a Laravel package with `parent => 'default'`, built with Vite. Turning arbitrary HTML into the admin panel would break the datagrids and forms.

## 5. Phase 3: Storefront Switching Options

- **Per channel**: already works through Appearance → Themes → Activate.
- **Preview before activating**: add a signed, admin-only `?preview_theme={code}` that the `Theme` middleware honours. The existing `PreviewChannel` middleware is the pattern to follow.
- **Scheduled switching (optional)**: for example a holiday theme between two dates. Add a `channel_theme_schedules` table and a scheduled command that calls the existing `activate` path.

## 6. Phase 4: HTML Zip Upload With Auto-Configure

The upload button lives in the existing Appearance → Themes gallery.

- **Route**: `POST admin/appearance/themes/upload`, route name `admin.appearance.themes.upload`.
- **Permission**: a new ACL permission, `appearance.themes.upload`.
- **Processing**: a queued job, so a large zip does not time out the request.

### 6.1 Validate

- Accept `.zip` only, with a size limit (for example 50 MB).
- Open it with `ZipArchive`, which the importer already uses.

### 6.2 Security Checks Before Extracting

- Reject any entry with `../`, an absolute path or a symlink.
- Cap the total uncompressed size to guard against zip bombs.
- Allow only these extensions: `html`, `css`, `js`, `json`, `png`, `jpg`, `jpeg`, `webp`, `svg`, `gif`, `woff`, `woff2`, `ttf`, `eot`, `ico`, `map`. Reject `.php`, `.phtml`, `.phar`, `.htaccess`, `.blade.php` and anything else.
- **Never write uploaded content out as a Blade file as-is.** HTML containing `@php` or `{{ }}` would run as code on the server. Generated views wrap the uploaded markup in `@verbatim`, or escape `@` and `{{`, and only the generator inserts Blade.
- Sanitise any SVG that is inlined.

### 6.3 Read the Manifest

- If the zip has a `theme.json` at its root, read the name, code, version and which page each HTML file maps to.
- If not, guess: `index.html` becomes the home page, and the other `*.html` files are listed for the admin to map by hand.

### 6.4 Convert

The converter uses `DOMDocument`:

- Pull the `<link rel="stylesheet">` and `<script>` tags out of `<head>` and rewrite their relative URLs to the theme's asset URL.
- Split out `<header>`, `<nav>`, `<footer>` and `<main>`.
- Replace marker attributes with real Bagisto components, using the table below. This is what "auto-configure" means in practice, and it is documented for template authors.

| Marker in the HTML | Becomes |
|---|---|
| `data-bagisto="logo"` | The channel logo |
| `data-bagisto="search"` | The search form component |
| `data-bagisto="cart"` | The mini-cart, with its item count |
| `data-bagisto="account"` | The login or account menu |
| `data-bagisto="categories"` | The category menu |
| `data-bagisto="products" data-type="new"` | A product carousel |
| `data-bagisto="sections"` | The Appearance editor's sections, so the home page stays editable |

An element without a marker stays static HTML.

### 6.5 Generate the Theme

- **Views**: written to `storage/app/themes/{code}/views`. They are the header and footer overrides of the default layout, plus a home page. They go in `storage/` because the web server can write there, unlike `resources/`.
- **Assets**: copied to `storage/app/public/themes/{code}` and served through `storage:link`.
- **Database row**: `parent = 'default'`, `vite = null`, `status = installed`.

### 6.6 Assets Without Vite

An uploaded theme has no Vite manifest, so `Theme::setBagistoVite()` needs a static mode. When `vite` is null, it loads the parent's bundle, which keeps the Vue components and checkout working, then the theme's own CSS and JS with `asset()` on top.

### 6.7 Finish

- The theme appears in the gallery as *Installed*. The admin previews it (Phase 3), then activates it per channel.
- Uploading a new version of the same code keeps the previous version's files, for a one-click rollback.
- Deleting is blocked while any channel uses the theme.

## 7. Phase 5: Tests and Project Checks

### 7.1 Pest Tests

- The upload accepts a valid zip.
- The upload rejects path traversal, PHP files, symlinks and oversized zips.
- Uploaded markup containing `@php` or `{{ }}` is not executed.
- Each marker maps to the right component.
- A theme added through the database appears in the gallery and can be activated.
- The admin theme resolver uses the user preference first, then the store setting, then the default.
- A missing admin theme falls back to `default`.

### 7.2 Project Checks

- Every new translation key is in all 23 locales, and `php artisan bagisto:translations:check` passes.
- `vendor/bin/pint --dirty` is clean.
- Every method, property and constant has a docblock.
- Route names are snake_case.

### 7.3 Playwright

- Upload, preview and activate a theme in the gallery.

## 8. Decisions Needed

1. **Admin switching**: per admin user, store-wide, or both? Recommended: both, with the user preference winning and the store setting as the fallback.
2. **Who may upload**: uploaded JS runs on the storefront, so treat the upload permission as fully trusted, as the project already does for custom scripts.
3. **Scheduled switching**: is it needed, or is per-channel enough?

## 9. Order and Prerequisites

1. **Build order**: Phase 1 → Phase 4 → Phase 3 → Phase 2. The database registry unblocks everything else, and the upload is the main feature.
2. **Write access**: the web server user (`www-data`) needs write access to `storage/` and `bootstrap/cache/` before any of this works.
