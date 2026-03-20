# Timber Jadev Theme Documentation

## Overview

WordPress theme built with [Timber](https://timber.github.io/docs/v2/) (Twig templating) and Tailwind CSS. Uses a **Controller + View** pattern: PHP files prepare data, Twig templates render HTML.

**Stack:** WordPress, Timber v2, Tailwind CSS, Swiper.js, jQuery Slim

---

## Directory Structure

```
timber-jadev/
|-- functions.php              # Theme setup, enqueues, menus, global context
|-- page-coaches.php           # PHP controller for Coaches page
|-- src/StarterSite.php        # Main theme class (extends Timber\Site)
|-- src/output.css             # Compiled Tailwind CSS
|-- views/
|   |-- base.twig              # Base layout (header, main, footer)
|   |-- page-*.twig            # Page-specific templates
|   |-- components/            # Reusable UI components
|   |-- partial/               # Shared partials
|-- assets/js/                 # JavaScript files
|-- assets/css/                # CSS files
```

---

## Architecture

```
WordPress Request -> PHP Controller -> Timber::context() -> Timber::render('template.twig', $context) -> HTML
```

### Base Template Blocks

| Block | Purpose |
|-------|---------|
| `content` | Main page content (primary override) |
| `head` | Additional `<head>` content |
| `header` | Site header/navigation |
| `footer` | Site footer |

---

## Global Context

Available in every Twig template:

| Variable | Description |
|----------|-------------|
| `site` | Site info (name, url, etc.) |
| `menu` | Default WordPress menu |
| `header_menus` | Primary navigation menu |
| `footer_menus` | Footer menus (About, Support, Legal) |
| `is_front_page` | Whether current page is homepage |
| `post` | Current post/page object |

---

## Components Reference

All in `views/components/`, included via `{% include "components/name.twig" with { ... } %}`.

Most components accept `rct_darkbg` (`"bg-background"`) for dark backgrounds.

| Component | Description |
|-----------|-------------|
| `button.twig` | Reusable button/link (`link`, `label`, `aria_label`, `add_class`) |
| `breadcrumb.twig` | Breadcrumb nav (uses global `post`) |
| `image-text.twig` | Side-by-side image + text with optional button |
| `row-content-text.twig` | Centered content with title, description, CTAs |
| `row-equipment-card.twig` | Grid of equipment/feature cards |
| `row-testimonial-card.twig` | Grid of testimonial cards |
| `row-testimonial-carousel.twig` | Testimonial slider with nav controls |
| `hero-texts.twig` | Text-only hero with up to two buttons |
| `hero-image-text.twig` | Hero with background image + text overlay |
| `row-media.twig` | Video player with play/pause controls |
| `row-faq.twig` | Expandable accordion FAQ |
| `vertical-slider-tabs.twig` | Vertical tabs with synced media slider |
| `row-text-left.twig` | Left-aligned text with optional button |
| `row-banner-carousel.twig` | Banner card carousel with progress bar |
| `footer-banner.twig` | Full-width banner with bg image + CTA |
| `footer-email.twig` | Email signup form |
| `row-table.twig` | Data table with optional FAQ below |

---

## Page Templates

Each custom page needs a **Twig view** (`views/page-{slug}.twig`) and optionally a **PHP controller** (`page-{slug}.php`) if custom data is needed.

| PHP Controller | Twig View | Slug |
|----------------|-----------|------|
| `page-coaches.php` | `page-coaches.twig` | `coaches` |
| *(default)* | `page-workouts.twig` | `workouts` |
| *(default)* | `page-membership.twig` | `membership` |
| *(default)* | `page-pilates.twig` | `pilates` |
| *(default)* | `page-installation.twig` | `installation` |
| *(default)* | `page-transformations.twig` | `transformations` |
| *(default)* | `page-try-tonal.twig` | `try-tonal` |
| *(default)* | `page-resource-hub.twig` | `resource-hub` |

---

## Adding a New Page

1. Create a WordPress page with the desired slug (e.g., `my-page`)
2. Create `views/page-my-page.twig` extending `base.twig`
3. *(Optional)* Create `page-my-page.php` if you need custom queries

```twig
{% extends "base.twig" %}
{% block content %}
    <article class="post-type-{{post.post_type}}" id="post-{{post.ID}}">
        {% include "components/hero-texts.twig" with { 'title_row1': "My Page" } %}
    </article>
{% endblock %}
```

---

## Navigation Menus

| Location | Label | Usage |
|----------|-------|-------|
| `primary` | Primary Menu | Header nav |
| `about-footer` | About Tonal Menu | Footer |
| `support-footer` | Support Menu | Footer |
| `legal-footer` | Legal Menu | Footer |

---

## Assets

| Handle | File | Description |
|--------|------|-------------|
| `jadev-style` | `src/output.css` | Compiled Tailwind CSS |
| `swiper-css` | `assets/css/swiper-bundle.min.css` | Swiper CSS |
| `jadev-main` | `assets/js/main.js` | Theme JavaScript |
| `jadev-jquery` | jQuery 3.7.1 Slim (CDN) | jQuery |
| `swiper-element` | `assets/js/swiper-element-bundle.min.js` | Swiper Web Component |
