# Breadcrumb Navigation Guide

This guide documents the breadcrumb navigation component implementation in the timber-jadev theme.

## Table of Contents

1. [Overview](#overview)
2. [Source & Inspiration](#source--inspiration)
3. [Purpose](#purpose)
4. [File Structure](#file-structure)
5. [Implementation Details](#implementation-details)
6. [How It Works](#how-it-works)
7. [Customization](#customization)
8. [Accessibility](#accessibility)
9. [Troubleshooting](#troubleshooting)

---

## Overview

The breadcrumb component provides hierarchical navigation links that help users understand their current location within the site structure. It displays on all pages **except the home page**.

**Example Output:**
```
Home / Equipment Overview
Home / About / Our Team
```

---

## Source & Inspiration

### Original Source

The breadcrumb HTML structure was provided by the user/designer and is based on the **Tonal website's breadcrumb pattern**. The markup follows modern accessibility standards and uses Tailwind CSS utility classes for styling.

### Reference Implementation

The original HTML structure:

```html
<div class="wrapper-fluid">
  <ol class="text-text flex flex-wrap leading-8">
    <li class="list-none">
      <a href="/" class="text-text no-underline text-body-sm">
        <span>Home</span>
      </a>
      <span class="pointer-events-none mx-6" aria-hidden="true">/</span>
    </li>
    <li class="list-none">
      <a href="/pages/equipment-overview" class="text-text no-underline text-body-sm" aria-current="page">
        <span>Equipment Overview</span>
      </a>
    </li>
  </ol>
</div>
```

### Adaptation for Timber/WordPress

The static HTML was converted to a dynamic Twig template that:
- Automatically generates breadcrumb trails from WordPress page hierarchy
- Supports parent/child page relationships
- Uses Timber's `post.ancestors` for hierarchical pages
- Conditionally displays only on non-home pages

---

## Purpose

### Why Breadcrumbs?

1. **Navigation Aid**: Helps users understand where they are in the site hierarchy
2. **Quick Navigation**: Allows users to jump back to parent pages easily
3. **SEO Benefits**: Search engines use breadcrumbs to understand site structure
4. **Reduced Bounce Rate**: Users can navigate up the hierarchy instead of leaving
5. **User Experience**: Provides context and orientation, especially on deep pages

### When It Displays

| Page Type | Breadcrumb Shown |
|-----------|------------------|
| Home Page (front-page.php) | No |
| Regular Pages | Yes |
| Child Pages | Yes (with parent links) |
| Posts | Yes |
| Archives | Yes |
| 404 Page | Yes |

---

## File Structure

```
wp-content/themes/timber-jadev/
├── views/
│   ├── base.twig                      # Main layout (includes breadcrumb)
│   └── components/
│       └── breadcrumb.twig            # Breadcrumb component
├── functions.php                       # Adds is_front_page to context
└── docs/
    └── BREADCRUMB-GUIDE.md            # This documentation
```

---

## Implementation Details

### 1. The Component (`views/components/breadcrumb.twig`)

```twig
<div class="wrapper-fluid">
  <ol class="text-text flex flex-wrap leading-8">
    <li class="list-none">
      <a href="/" class="text-text no-underline text-body-sm">
        <span>Home</span>
      </a>
      <span class="pointer-events-none mx-6" aria-hidden="true">/</span>
    </li>
    {% if post.parent %}
      {% for ancestor in post.ancestors|reverse %}
        <li class="list-none">
          <a href="{{ ancestor.link }}" class="text-text no-underline text-body-sm">
            <span>{{ ancestor.title }}</span>
          </a>
          <span class="pointer-events-none mx-6" aria-hidden="true">/</span>
        </li>
      {% endfor %}
    {% endif %}
    <li class="list-none">
      <a href="{{ post.link }}" class="text-text no-underline text-body-sm" aria-current="page">
        <span>{{ post.title }}</span>
      </a>
    </li>
  </ol>
</div>
```

### 2. Base Template Integration (`views/base.twig`)

The breadcrumb is included after the header, with a conditional to hide it on the front page:

```twig
</header>

{% if not is_front_page %}
  {% include "components/breadcrumb.twig" %}
{% endif %}

<section id="content" role="main" class="content-wrapper">
```

### 3. Context Setup (`functions.php`)

The `is_front_page` variable is added to Timber's global context:

```php
add_filter('timber/context', function ($context) {
    // ... other context setup ...

    $context['is_front_page'] = is_front_page();

    return $context;
});
```

---

## How It Works

### Breadcrumb Generation Flow

```
1. User visits a page (e.g., /about/team/)
                    │
                    ▼
2. WordPress loads page.php → renders base.twig
                    │
                    ▼
3. base.twig checks: is_front_page?
                    │
          ┌─────────┴─────────┐
          │                   │
         Yes                 No
          │                   │
          ▼                   ▼
    Skip breadcrumb    Include breadcrumb.twig
                              │
                              ▼
                    4. breadcrumb.twig renders:
                       - Home link (always)
                       - Ancestor links (if hierarchical)
                       - Current page (always)
```

### Timber Variables Used

| Variable | Description | Example |
|----------|-------------|---------|
| `post` | Current Timber\Post object | The current page |
| `post.title` | Page title | "Equipment Overview" |
| `post.link` | Page URL | "/pages/equipment-overview" |
| `post.parent` | Parent post ID (0 if none) | 42 |
| `post.ancestors` | Array of parent Timber\Post objects | [Parent, Grandparent] |
| `is_front_page` | Boolean from functions.php | true/false |

### Ancestor Order

The `|reverse` filter is applied to `post.ancestors` because Timber returns ancestors from immediate parent to root. We reverse it to display root → parent → current:

```
Without reverse: [Immediate Parent, Grandparent, Great-Grandparent]
With reverse:    [Great-Grandparent, Grandparent, Immediate Parent]
```

---

## Customization

### Changing the Separator

The current separator is a forward slash `/`. To change it:

```twig
{# Current #}
<span class="pointer-events-none mx-6" aria-hidden="true">/</span>

{# Arrow example #}
<span class="pointer-events-none mx-4" aria-hidden="true">→</span>

{# Chevron example #}
<span class="pointer-events-none mx-4" aria-hidden="true">
  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
  </svg>
</span>
```

### Adding Structured Data (Schema.org)

For better SEO, add JSON-LD structured data. Add this to `breadcrumb.twig`:

```twig
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {
      "@type": "ListItem",
      "position": 1,
      "name": "Home",
      "item": "{{ site.url }}"
    }
    {% set position = 2 %}
    {% if post.parent %}
      {% for ancestor in post.ancestors|reverse %}
        ,{
          "@type": "ListItem",
          "position": {{ position }},
          "name": "{{ ancestor.title }}",
          "item": "{{ ancestor.link }}"
        }
        {% set position = position + 1 %}
      {% endfor %}
    {% endif %}
    ,{
      "@type": "ListItem",
      "position": {{ position }},
      "name": "{{ post.title }}",
      "item": "{{ post.link }}"
    }
  ]
}
</script>
```

### Styling Variations

**Underline on hover:**
```twig
<a href="/" class="text-text no-underline hover:underline text-body-sm">
```

**Different color for current page:**
```twig
<a href="{{ post.link }}" class="text-primary no-underline text-body-sm" aria-current="page">
```

**Add background container:**
```twig
<div class="wrapper-fluid bg-gray-100 py-4">
  <ol class="text-text flex flex-wrap leading-8">
    ...
  </ol>
</div>
```

### Adding Breadcrumbs to Specific Templates Only

If you want breadcrumbs only on certain page types, modify `base.twig`:

```twig
{# Only on pages (not posts, archives, etc.) #}
{% if not is_front_page and post.post_type == 'page' %}
  {% include "components/breadcrumb.twig" %}
{% endif %}
```

---

## Accessibility

### Current Implementation

The breadcrumb follows accessibility best practices:

| Feature | Implementation | Purpose |
|---------|---------------|---------|
| Semantic HTML | `<ol>` ordered list | Screen readers announce as a list |
| Current page indicator | `aria-current="page"` | Identifies the current page to assistive tech |
| Hidden decorative elements | `aria-hidden="true"` on separators | Prevents screen readers from reading "/" |
| Descriptive link text | Page titles as link text | Clear navigation targets |

### Screen Reader Experience

A screen reader will announce:
```
"List with 3 items:
 Link: Home
 Link: About
 Current page, link: Our Team"
```

### Recommended Enhancements

Add a `<nav>` wrapper with `aria-label` for better landmark navigation:

```twig
<nav aria-label="Breadcrumb">
  <div class="wrapper-fluid">
    <ol class="text-text flex flex-wrap leading-8">
      ...
    </ol>
  </div>
</nav>
```

---

## Troubleshooting

### Breadcrumb not showing

1. **Check if on front page**: Breadcrumbs are hidden on the home page by design
2. **Verify context variable**: Ensure `is_front_page` is added in `functions.php`
3. **Check template inheritance**: Ensure your page template extends `base.twig`

### Parent pages not showing

1. **Verify page hierarchy**: In WordPress admin, check that the page has a parent set
2. **Check post.parent**: Debug with `{{ dump(post.parent) }}` in Twig
3. **Verify ancestors**: Debug with `{{ dump(post.ancestors) }}`

### Styling issues

1. **Check Tailwind compilation**: Ensure `wrapper-fluid`, `text-text`, etc. are in your compiled CSS
2. **Inspect with DevTools**: Check if styles are being overridden
3. **Verify class names**: Match the classes used in your design system

### Debug Template

Add this temporarily to see all available data:

```twig
{% if app.debug %}
  <pre>
    is_front_page: {{ is_front_page ? 'true' : 'false' }}
    post.title: {{ post.title }}
    post.parent: {{ post.parent }}
    ancestors count: {{ post.ancestors|length }}
  </pre>
{% endif %}
```

---

## Related Files

- `views/base.twig` - Main layout template
- `views/components/breadcrumb.twig` - Breadcrumb component
- `functions.php` - Context filter adding `is_front_page`
- `src/output.css` - Compiled Tailwind styles (includes utility classes)

---

## Resources

- [Timber Documentation - Posts](https://timber.github.io/docs/v2/guides/posts/)
- [WordPress is_front_page()](https://developer.wordpress.org/reference/functions/is_front_page/)
- [Schema.org BreadcrumbList](https://schema.org/BreadcrumbList)
- [WAI-ARIA Breadcrumb Pattern](https://www.w3.org/WAI/ARIA/apg/patterns/breadcrumb/)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)
