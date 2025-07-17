# DMG Read More Link for WordPress

![License: GPL v3 or later](https://img.shields.io/badge/License-GPLv3%20or%20later-blue.svg?style=flat-square)
![Requires PHP: >= 7.4](https://img.shields.io/badge/PHP-%3E%3D%207.4-blue.svg?style=flat-square)
![WordPress Tested: 6.8.2](https://img.shields.io/badge/WordPress-tested%20up%20to%206.8.2-brightgreen.svg?style=flat-square)
![GitHub issues](https://img.shields.io/github/issues/NewJenk/DMG-Read-More-Link?style=flat-square)
![GitHub stars](https://img.shields.io/github/stars/NewJenk/DMG-Read-More-Link?style=flat-square)

A dynamic WordPress block that allows you to easily search for and link to other posts on your site, prepended with 'Read More: '.

---

## Overview

This plugin provides a `DMG Read More Link` block for the WordPress editor. It allows you to quickly find any post on your site—by searching for its title or entering its ID—and insert a link to it.

The block is **dynamic**, meaning it only stores the Post ID. The post title and link are fetched on the front-end when the page loads. This ensures that if you update a post's title or slug, all links created with this block will update automatically across your site.

The plugin is architected for performance and clean code, using the registry pattern and optimised database queries. It has been tested on sites with over 50,000 posts to ensure a smooth and fast user experience.

## How to Use

1.  In the WordPress block editor, add the **DMG Read More Link** block.
2.  Open the block's settings in the sidebar (or click the cog icon in the block's toolbar).
3.  Use the search box to find a post by typing a search term or a Post ID.
4.  Click on the desired post from the results list. A link will be inserted into your content, for example: `Read More: My Awesome Blog Post`.
5.  To change the linked post, simply select a different one from the list.
6.  To remove the link, click the small 'X' icon next to the selected post's title in the sidebar.

## Features

* **Dynamic Link Generation:** Only the Post ID is saved. The plugin fetches the latest post title and URL on page load, ensuring links are always up to date even if post slugs or titles change.
* **Smart Post Search:** Find posts by searching for their title or by entering a specific Post ID. The interface clearly indicates which search method is being used.
* **Extreme Scalability:** Architected for enterprise-level performance. The plugin uses a custom, hidden taxonomy for instant lookups, ensuring it runs flawlessly on sites with tens of millions of posts.
* **Integrated Admin View:** A dedicated screen under the main **"Settings"** menu displays all posts and pages with the block. The entire view is built using standard WordPress UI components, providing a fast, familiar and bloat-free experience with tabs to easily switch between content types.
* **Powerful WP-CLI Commands:** Includes a full suite of `wp dmg-read-more` commands (see below), allowing developers and administrators to find posts with the block directly from the command line, with advanced filtering by date range.
* **Well-Organised Codebase:** Built using modern, object-oriented principles within a class-based structure for neat, organised and maintainable code.
* **Translation Ready:** All user-facing strings are fully internationalised and ready for translation into any language.
* **Developer Friendly:** Core functionality, such as post fetching (`FetchPosts`) and the list of posts (`ListPosts`), has been built into reusable components that can easily be adapted for other blocks or post types.

## WP-CLI Integration

A common challenge is finding all instances of a specific block across a large website. Searching through `post_content` is notoriously slow and inefficient, especially on sites with thousands or millions of posts.

To solve this, the plugin implements a high-performance architecture and a powerful **WP-CLI command**.

The plugin assigns a term from a dedicated, hidden taxonomy whenever the `DMG Read More Link` block is used. This allows the command, `wp dmg-read-more search`, to leverage WordPress's highly optimised and indexed taxonomy tables for near-instant lookups, even on databases with tens of millions of posts.

The command includes advanced filtering, allowing developers to find all instances of the block within specific date ranges.

### Command Options

If no date flags are provided, the command defaults to searching within the **last 30 days**.

* `--date-before=<date>`
    Finds items published before this date (format: Y-m-d).
* `--date-after=<date>`
    Finds items published after this date (format: Y-m-d).

### Examples

* **Search for items from the last 30 days (default behaviour):**
    ```bash
    wp dmg-read-more search
    ```

* **Search for items published after 15th January 2025:**
    ```bash
    wp dmg-read-more search --date-after=2025-01-15
    ```

* **Search for items published during January 2025:**
    ```bash
    wp dmg-read-more search --date-after=2025-01-01 --date-before=2025-01-31
    ```

## FAQ

### Does this plugin depend on any others?

Nope.

### Does this create new database tables?

Nope.

### Does this modify existing database tables?

Nope.

## Development Standards & Naming Conventions

To ensure consistency and prevent conflicts, please adhere to the following naming conventions. This guide reflects the specific standards used throughout the plugin.

| Type | Convention | Example Usage |
| :--- | :--- | :--- |
| **PHP Namespace** | `DMG\RML` | All PHP classes must be within this namespace. |
| **PHP Constants** | `DMG_RML` | Prefix for all defined PHP constants, e.g., `DMG_RML_PLUGIN_PATH`. |
| **Prefix** | `dmg_rml` | Prefix for internal non-public PHP functions, filters, `post_meta` keys  |
| **Asset Prefix & Text Domain** | `dmg-rml` | Used as the text domain for internationalisation, block namespaces, and prefix for all custom CSS classes, e.g., `.dmg-rml-wrap`. |
| **Plugin Name** | DMG Read More Link | The full name of the plugin for use in user-facing text. |
| **Plugin URL** | `https://github.com/NewJenk/DMG-Read-More-Link` | The primary URL for the plugin. |
| **Plugin Description** | A dynamic WordPress block that allows you to easily search for and link to other posts on your site, prepended with 'Read More: '. | The description for the plugin. |

## Requirements

* **WordPress:** Version 6.0 or higher (Tested up to **6.8.2**)
* **PHP:** Version 7.4 or higher

## Installation

**1. GitHub Download (ZIP):**

* Go to the [Releases page](https://github.com/NewJenk/DMG-Read-More-Link/releases) of this repository.
* Download the latest `.zip` file.
* In your WordPress admin dashboard, navigate to `Plugins` > `Add New` > `Upload Plugin`.
* Choose the downloaded ZIP file and click `Install Now`.
* Activate the plugin.

**2. Manual Upload (FTP/SFTP):**

* Go to the [Releases page](https://github.com/NewJenk/DMG-Read-More-Link/releases) of this repository.
* Download the latest `.zip` file and extract it.
* Upload the entire extracted folder to your WordPress `wp-content/plugins/` directory.
* In your WordPress admin dashboard, navigate to `Plugins` > `Installed Plugins`.
* Find "DMG Read More Link" and click `Activate`.