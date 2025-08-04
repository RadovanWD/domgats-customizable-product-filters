# DomGats Customizable Product Filters

![License: GPL v2 or later](https://img.shields.io/badge/License-GPL%20v2%20or%20later-blue.svg)
![Version: 1.0.0](https://img.shields.io/badge/Version-1.0.0-brightgreen.svg)
![WordPress: 5.0+](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)
![Tested up to: 6.4](https://img.shields.io/badge/Tested%20up%20to-6.4-lightgrey.svg)

**Contributors:** Radovan Gataric (DomGat)  
**Tags:** woocommerce, products, filter, ajax, elementor, custom filter

A powerful, AJAX-driven product filtering plugin for WordPress and WooCommerce. Initially built for Elementor, this plugin is on a path to become a builder-agnostic, standalone filtering powerhouse.

---

## 📖 Table of Contents

* [✨ Features](#-features)
* [🚀 Getting Started](#-getting-started)
* [🔧 How to Use](#-how-to-use)
* [🔮 The Vision & Roadmap](#-the-vision--roadmap)
* [🤝 Contributing](#-contributing)
* [📜 License](#-license)

---

## ✨ Features

* **Blazing Fast AJAX Filtering:** No page reloads. Your users get instant results.
* **Elementor Loop Grid Integration:** Designed to work perfectly with Elementor Pro's Loop Grid and Loop Carousel widgets.
* **Multiple Filter Instances:** Use different filters on the same page (e.g., one for main products, another for "add-ons").
* **Smart Tag Filtering:** Dynamically disables tags that would result in no products, guiding the user to valid selections.
* **Responsive & Mobile Ready:** Includes "Load More" functionality for mobile and a Flickity-powered carousel for desktop.

---

## 🚀 Getting Started

Follow these steps to get the filter up and running on your site.

### Installation

1.  Download the latest release from the [GitHub repository](https://github.com/your-repo-link).
2.  In your WordPress dashboard, navigate to **Plugins > Add New**.
3.  Click **Upload Plugin** and select the `.zip` file you downloaded.
4.  Activate the plugin.

---

## 🔧 How to Use

The plugin is designed for simplicity. To connect the filter UI with your product list, you need to do two things:

### 1. Place the Shortcode

Place one of the following shortcodes in a **Text Editor** or **Shortcode** widget directly *above* your Elementor Loop Grid.

* For your main products:
    ```
    [product_tag_filter]
    ```
* For your "add-on" products:
    ```
    [add_ons_tag_filter]
    ```

### 2. Configure the Loop Grid Widget

Select your Loop Grid widget and go to the **Advanced** tab.

1.  **CSS Classes:** Add the class `ahh-maa-product-grid`.
2.  **Attributes:** Add a custom attribute to link the grid to the filter.
    * **Key:** `data-filter-type`
    * **Value:** `products` (to link to `[product_tag_filter]`) or `addons` (to link to `[add_ons_tag_filter]`).

!

That's it! The plugin will now automatically control this grid.

---

## 🔮 The Vision & Roadmap

This plugin started as a specific solution for an Elementor-based project, but the goal is much bigger. The vision is to evolve **DomGats Customizable Product Filters** into a comprehensive, independent filtering solution for all of WordPress.

### Phase 1: Solid Foundations (Current)

* ✅ Robust AJAX filtering for WooCommerce product tags.
* ✅ Seamless integration with Elementor Pro Loop Grids.
* ✅ Clean, extensible, object-oriented code.

### Phase 2: Builder Agnostic

The next major step is to break free from the Elementor-only dependency.

* **Integrations:** Add dedicated support for other major page builders.
    * [ ] Divi Builder
    * [ ] Bricks Builder
    * [ ] Beaver Builder
    * [ ] Oxygen Builder
* **Generic Shortcode Target:** Create a generic CSS class or selector system that can target any post loop, regardless of the builder.

### Phase 3: Standalone Powerhouse

This phase will make the plugin a complete, all-in-one solution for displaying and filtering content.

* **Built-in Render Engine:** The plugin will provide its own beautiful, customizable post grids, lists, and carousels via shortcode. You won't need a page builder to display your products.
* **Advanced Filters:** Move beyond just tags.
    * [ ] Filter by Price (Range Slider)
    * [ ] Filter by Category (Checkboxes/Dropdown)
    * [ ] Filter by Custom Taxonomies & Attributes
    * [ ] Search Box Filter
    * [ ] Rating Filter
* **Admin Settings Panel:** A dedicated settings page in the WordPress admin to manage styles, integrations, and filter settings without touching code.

### Additional Ideas & Suggestions

* **Multiple Filter UI Styles:** Offer different looks for the filter UI, like checkboxes, radio buttons, and color swatches.
* **URL-based Filtering:** Update the URL with query parameters as filters are selected, allowing users to share links to specific filter results.
* **Developer API:** Introduce a rich set of WordPress hooks (actions and filters) to allow developers to extend the plugin's functionality easily.

---

## 🤝 Contributing

Contributions are what make the open-source community such an amazing place to learn, inspire, and create. Any contributions you make are **greatly appreciated**.

You can contribute by:
* Reporting a bug.
* Discussing the current state of the code.
* Submitting a fix via a Pull Request.
* Proposing new features.

---

## 📜 License

This project is licensed under the GPL v2 or later. See the `LICENSE` file for more details.

