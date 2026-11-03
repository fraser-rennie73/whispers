# Whispers from the Void

Whispers from the Void is a PHP and MySQL web application for running the
Zoomthulhu/Call of Cthulhu-inspired campaign. It combines articles and
categories, player accounts, comments, file uploads, investigator sheets, and
campaign tools in one browser-based site.

## Features

- Campaign articles and category navigation
- Investigator and character-sheet pages
- Editable character values stored in MySQL
- Player accounts and session-based access
- Comments and campaign content permissions
- File uploads and attachment handling
- Printable character-sheet layouts
- Supporting scripts for campaign and table-use tools

## Requirements

- PHP with the MySQLi extension
- MySQL/MariaDB
- A web server such as Apache or Nginx
- PHP support for file uploads if attachment features will be used

The `Scripts/vendor` directory contains bundled third-party PHP libraries.

## Local setup

1. Clone the repository and place it in your web server document root.
2. Create the MySQL database and tables used by the application.
3. Configure the values in `Scripts/config.php`:

   ```php
   define('DB_HOST', 'localhost');
   define('DB_USERNAME', 'your-database-user');
   define('DB_PASSWORD', 'your-database-password');
   define('DB_DBNAME', 'your-database-name');
   ```

4. Keep `Scripts/config.php` out of source control when it contains deployment
   credentials.
5. Start Apache, Nginx, or PHP's development server:

   ```powershell
   php -S localhost:8000
   ```

6. Open `http://localhost:8000/Index.php`.

The database schema and migration files are not currently included in this
repository. An existing compatible database is therefore required when running
the application outside its deployment environment.

## Project layout

- `Index.php` - main site entry point
- `Scripts/` - PHP application logic, database access, uploads, and vendored
  dependencies
- `CharacterSheet/` - investigator sheet interface, styles, and scripts
- `Styles/` - site stylesheets
- `Images/` - campaign artwork, portraits, maps, and interface assets
- `Tools/` - JavaScript utilities for campaign tools
- `*.html` - character, campaign, privacy, and investigator pages
- `*.php` - standalone pages and legacy utility/test endpoints

## Development notes

Several pages are legacy standalone PHP or HTML files and include relative
assets directly. When adding or moving a page, preserve its relative paths or
update its stylesheet, image, and script references.

This project has no Composer package script, build step, or automated test
suite in the repository root. Use a local PHP/MySQL environment for manual
validation.
