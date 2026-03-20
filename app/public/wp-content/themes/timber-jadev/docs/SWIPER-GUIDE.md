# Swiper Carousel Guide

This guide documents how Swiper carousels are set up, configured, and used in the timber-jadev theme.

## Table of Contents

1. [Overview](#overview)
2. [File Structure](#file-structure)
3. [Setup (functions.php)](#setup-functionsphp)
4. [About swiper-element-bundle.min.js](#about-swiper-element-bundleminjs)
5. [Carousel Types](#carousel-types)
6. [Creating a New Carousel](#creating-a-new-carousel)
7. [Configuration Options](#configuration-options)
8. [Progress Bar](#progress-bar)
9. [Troubleshooting](#troubleshooting)

---

## Overview

This project uses **Swiper Element** (Web Components version) instead of the traditional Swiper JS library. This allows you to use `<swiper-container>` and `<swiper-slide>` as native HTML elements.

**Key Differences from Traditional Swiper:**
- Uses custom HTML elements (`<swiper-container>`, `<swiper-slide>`)
- Configuration is done via JavaScript `Object.assign()` or HTML attributes
- Must call `.initialize()` manually when `init="false"` is set
- Events are prefixed with `swiper` (e.g., `swiperinit`, `swiperslidechange`)

---

## File Structure

```
wp-content/themes/timber-jadev/
├── assets/
│   ├── css/
│   │   └── swiper-bundle.min.css      # Swiper styles
│   └── js/
│       ├── main.js                     # Carousel initialization logic
│       └── swiper-element-bundle.min.js # Swiper Element library
├── views/
│   └── components/
│       ├── row-testimonial-carousel.twig  # Testimonial carousel template
│       └── row-banner-carousel.twig       # Banner/feature carousel template
└── functions.php                       # Script/style enqueuing
```

---

## Setup (functions.php)

Swiper assets are enqueued in `functions.php`:

```php
// Swiper CSS
function jadev_enqueue_style()
{
    wp_enqueue_style(
        'swiper-css',
        get_stylesheet_directory_uri() . '/assets/css/swiper-bundle.min.css',
        array(),
        '11.0.0'
    );
}
add_action('wp_enqueue_scripts', 'jadev_enqueue_style');

// Swiper Element JS
function jadev_enqueue_scripts()
{
    wp_enqueue_script(
        'swiper-element',
        get_stylesheet_directory_uri() . '/assets/js/swiper-element-bundle.min.js',
        array(),
        '11.0.0',
        true
    );

    // Main JS (depends on Swiper being loaded)
    wp_enqueue_script(
        'jadev-main',
        get_stylesheet_directory_uri() . '/assets/js/main.js',
        array('jadev-jquery'),
        false,
        true
    );
}
add_action('wp_enqueue_scripts', 'jadev_enqueue_scripts');
```

---

## About swiper-element-bundle.min.js

### What is it?

`swiper-element-bundle.min.js` is the **Web Components version of Swiper**. It registers custom HTML elements that you can use directly in your markup.

### Why use it?

| Feature | Traditional Swiper | Swiper Element |
|---------|-------------------|----------------|
| HTML | `<div class="swiper">` | `<swiper-container>` |
| Initialization | `new Swiper('.swiper', {...})` | `swiperEl.initialize()` |
| Declarative config | No | Yes (HTML attributes) |
| Shadow DOM | No | Yes (encapsulated styles) |

### Where to get it?

Download from the official Swiper releases or use CDN:
- NPM: `npm install swiper`
- CDN: `https://cdn.jsdelivr.net/npm/swiper@11/swiper-element-bundle.min.js`

### Current Version

The project uses Swiper version **11.0.0**.

---

## Carousel Types

This project has two carousel types, differentiated by the `data-carousel-type` attribute:

### 1. Testimonial Carousel

**Attribute:** `data-carousel-type="testimonial"`

**Behavior:**
- Centered slides with scale effect (active: 100%, others: 80%)
- 3 slides visible on desktop, 1 on mobile
- Loop enabled
- Custom prev/next navigation

**Usage:**
```twig
{% include "components/row-testimonial-carousel.twig" with {
    'rct_title': "What people are saying"
} %}
```

### 2. Banner Carousel

**Attribute:** `data-carousel-type="banner"`

**Behavior:**
- Partial slide visible (peek effect)
- No scale effect
- Responsive slides per view
- Custom prev/next navigation

**Usage:**
```twig
{% include "components/row-banner-carousel.twig" with {
    'rct_title': "Features"
} %}
```

---

## Creating a New Carousel

### Step 1: Create the Twig Template

Create a new file in `views/components/`:

```twig
{# row-my-carousel.twig #}
<div class="wrapper">
  <div class="relative">
    <swiper-container
      class="z-default"
      init="false"
      data-carousel-type="my-carousel">

      {# Slides #}
      <swiper-slide>
        <article>Slide 1 content</article>
      </swiper-slide>
      <swiper-slide>
        <article>Slide 2 content</article>
      </swiper-slide>
      {# Add more slides... #}

    </swiper-container>

    {# Navigation Buttons #}
    <div class="navigation-wrapper">
      <button type="button" class="my-carousel-slider-prev" aria-label="Previous slide"></button>
      <button type="button" class="my-carousel-slider-next" aria-label="Next slide"></button>
    </div>
  </div>
</div>
```

**Important:**
- Set `init="false"` to prevent auto-initialization
- Use unique `data-carousel-type` value
- Use unique button class names (e.g., `my-carousel-slider-prev`)

### Step 2: Add JavaScript Initialization

Add a new function in `assets/js/main.js`:

```javascript
function initializeMyCarousel() {
  const swiperEl = document.querySelector("swiper-container[data-carousel-type='my-carousel']");

  if (!swiperEl) return;

  // Set parameters
  Object.assign(swiperEl, {
    slidesPerView: 1,
    spaceBetween: 16,
    loop: false,
    breakpoints: {
      768: {
        slidesPerView: 2,
      },
      1024: {
        slidesPerView: 3,
      },
    },
  });

  // Setup navigation after init
  swiperEl.addEventListener("swiperinit", function (e) {
    const swiper = e.detail[0];
    const container = swiperEl.closest('.wrapper');

    container.querySelector(".my-carousel-slider-prev")?.addEventListener("click", () => swiper.slidePrev());
    container.querySelector(".my-carousel-slider-next")?.addEventListener("click", () => swiper.slideNext());
  });

  // Initialize
  swiperEl.initialize();
}
```

### Step 3: Register in initializeSwiper()

```javascript
function initializeSwiper() {
  initializeTestimonialSwiper();
  initializeBannerSwiper();
  initializeMyCarousel(); // Add your new carousel
}
```

### Step 4: Include in Page Template

```twig
{% include "components/row-my-carousel.twig" %}
```

---

## Configuration Options

### Common Swiper Parameters

```javascript
Object.assign(swiperEl, {
  // Basic
  slidesPerView: 3,           // Number of slides visible (can be decimal: 1.5)
  spaceBetween: 10,           // Gap between slides (px)
  loop: true,                 // Infinite loop
  centeredSlides: true,       // Active slide is centered
  initialSlide: 0,            // Starting slide index

  // Speed
  speed: 300,                 // Transition duration (ms)

  // Autoplay
  autoplay: {
    delay: 3000,
    disableOnInteraction: false,
  },

  // Pagination
  pagination: {
    el: '.swiper-pagination',
    clickable: true,
    type: 'bullets',          // 'bullets', 'fraction', 'progressbar'
  },

  // Breakpoints (responsive)
  breakpoints: {
    0: {
      slidesPerView: 1,
    },
    768: {
      slidesPerView: 2,
    },
    1024: {
      slidesPerView: 3,
    },
  },
});
```

### Swiper Element Events

```javascript
// Initialization complete
swiperEl.addEventListener("swiperinit", (e) => {
  const swiper = e.detail[0];
});

// Slide changed
swiperEl.addEventListener("swiperslidechange", () => {});

// Transition ended
swiperEl.addEventListener("swiperslidetransitionend", () => {});

// Touch ended
swiperEl.addEventListener("swipertouchend", () => {});
```

### Accessing the Swiper Instance

```javascript
// After initialization
swiperEl.addEventListener("swiperinit", (e) => {
  const swiper = e.detail[0];

  // Now you can use swiper methods
  swiper.slideTo(2);
  swiper.slideNext();
  swiper.slidePrev();

  // Access properties
  console.log(swiper.activeIndex);
  console.log(swiper.slides);
});
```

---

## Progress Bar

The banner carousel includes a progress bar that shows the user's scroll position within the carousel.

### HTML Structure

Add this after the `</swiper-container>` closing tag:

```html
<div class="mt-8 flex items-center gap-4">
  <!-- Progress bar track -->
  <div class="banner-progress-bar rounded-full overflow-hidden"
       style="background-color: #e5e5e5; width: calc(100% - 100px); height: 4px;">
    <!-- Progress bar fill -->
    <div class="banner-progress-fill rounded-full transition-all duration-300"
         style="width: 0%; height: 100%; background-color: #1c1c1a;"></div>
  </div>

  <!-- Navigation buttons -->
  <div class="flex gap-2">
    <button type="button" class="banner-slider-prev" aria-label="Previous slide"></button>
    <button type="button" class="banner-slider-next" aria-label="Next slide"></button>
  </div>
</div>
```

**Key elements:**
- `.banner-progress-bar` - The track (background)
- `.banner-progress-fill` - The fill that animates based on scroll position

**Important:** Use inline styles for `width` and `height` since Tailwind arbitrary values like `w-[calc(100%-100px)]` may not be compiled if not scanned.

### JavaScript Implementation

```javascript
function initializeBannerSwiper() {
  const swiperEl = document.querySelector("swiper-container[data-carousel-type='banner']");

  if (!swiperEl) return;

  const container = swiperEl.closest('.wrapper');
  const progressFill = container?.querySelector(".banner-progress-fill");

  // Set parameters
  Object.assign(swiperEl, {
    slidesPerView: 1.1,
    spaceBetween: 24,
    centeredSlides: true,
    initialSlide: 1,
    breakpoints: {
      0: { slidesPerView: 2.2 },
      768: { slidesPerView: 3.2 },
    },
  });

  // Progress update function using Swiper's built-in progress
  function updateProgress(swiper) {
    if (!progressFill || !swiper) return;
    const progress = swiper.progress * 100;
    progressFill.style.width = Math.min(Math.max(progress, 0), 100) + '%';
  }

  swiperEl.addEventListener("swiperinit", function (e) {
    const swiper = e.detail[0];
    updateProgress(swiper);

    container?.querySelector(".banner-slider-prev")?.addEventListener("click", () => swiper.slidePrev());
    container?.querySelector(".banner-slider-next")?.addEventListener("click", () => swiper.slideNext());
  });

  // Update progress on slide change
  swiperEl.addEventListener("swiperslidechange", function (e) {
    const swiper = e.detail[0];
    updateProgress(swiper);
  });

  swiperEl.initialize();
}
```

### Progress Calculation

The progress bar uses Swiper's built-in `swiper.progress` property:

```javascript
const progress = swiper.progress * 100;
```

- `swiper.progress` returns a value from `0` to `1`
- `0` = at the beginning
- `1` = at the end
- This automatically accounts for `slidesPerView`, breakpoints, and scroll position

### Adding Progress Bar to Other Carousels

1. Add the HTML structure (track + fill) with unique class names
2. Query the progress fill element in your init function
3. Create an `updateProgress(swiper)` function
4. Call it in both `swiperinit` and `swiperslidechange` events

Example for a custom carousel:

```javascript
const progressFill = container?.querySelector(".my-carousel-progress-fill");

function updateProgress(swiper) {
  if (!progressFill || !swiper) return;
  progressFill.style.width = (swiper.progress * 100) + '%';
}

swiperEl.addEventListener("swiperinit", (e) => {
  updateProgress(e.detail[0]);
});

swiperEl.addEventListener("swiperslidechange", (e) => {
  updateProgress(e.detail[0]);
});
```

---

## Troubleshooting

### Carousel not initializing

1. Check if `init="false"` is set on `<swiper-container>`
2. Verify `.initialize()` is being called in JavaScript
3. Check browser console for errors
4. Ensure `swiper-element-bundle.min.js` is loaded before `main.js`

### Navigation buttons not working

1. Verify button class names match between Twig and JS
2. Check if buttons are inside the correct container (for scoped queries)
3. Ensure event listeners are added inside `swiperinit` callback

### Multiple carousels conflicting

1. Use unique `data-carousel-type` attributes
2. Use unique button class names for each carousel type
3. Scope button queries to the carousel's container:
   ```javascript
   const container = swiperEl.closest('.wrapper');
   container.querySelector(".my-carousel-slider-prev")
   ```

### Styles not applying

1. Verify `swiper-bundle.min.css` is enqueued
2. Check if custom styles conflict with Swiper's default styles
3. Note: Swiper Element uses Shadow DOM, some styles may need `::part()` selectors

### Breakpoints not working as expected

Remember: Base config is overridden by breakpoints. If you set:
```javascript
{
  slidesPerView: 1,
  breakpoints: {
    0: { slidesPerView: 2 }
  }
}
```
The `0` breakpoint (2 slides) will override the base (1 slide) at all widths.

---

## Resources

- [Swiper Official Docs](https://swiperjs.com/)
- [Swiper Element Documentation](https://swiperjs.com/element)
- [Swiper API Reference](https://swiperjs.com/swiper-api)
- [Swiper Demos](https://swiperjs.com/demos)
