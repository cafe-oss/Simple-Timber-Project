# Vertical Slider Tabs Component

A reusable tabbed content component with vertical scrolling effect and zoom animation for active slides.

---

## Overview

This component displays a vertical list of tabs on one side and corresponding media (images/videos) on the other. Features include:

- Vertical rolling scroll effect with centered active tab
- Zoom animation on active media
- Auto-play with configurable interval
- Video auto-play for active slides
- Keyboard accessibility
- Touch swipe support
- Responsive mobile layout
- Progress bar indicator

---

## File Structure

```
your-theme/
├── views/components/
│   └── vertical-slider-tabs.twig
├── src/components/
│   └── slider.css (append the CSS)
└── assets/js/
    └── main.js (add the JS function)
```

---

## Installation

### 1. Twig Component

Create `views/components/vertical-slider-tabs.twig`:

```twig
{#
  Vertical Slider Tabs Component

  Variables:
  - rct_title: Section title (optional)
  - rct_subtitle: Section subtitle (optional)
  - rct_darkbg: Boolean for dark background
  - rct_items: Array of tab items with structure:
    - title: Tab button title
    - description: Tab content description
    - media_type: 'image' or 'video'
    - media_src: Image URL or video URL
    - media_alt: Alt text for images
    - poster: Video poster image (optional)
  - rct_tab_position: 'left' or 'right' (default: 'left')
#}

<section
  class="vertical-slider-tabs section-spacing section-padding {{ rct_darkbg ? 'bg-[rgb(28,28,26)]' : 'bg-background' }} text-text"
  data-component="vertical-slider-tabs"
  style="
    --scheme-disabled-accent: 73 73 72;
    --scheme-disabled: 110 110 110;
    --scheme-accent: 17 221 196;
    --scheme-on-accent: 28 28 26;
    --scheme-background: {{ rct_darkbg ? '28 28 26' : '244 244 244' }};
    --scheme-text: {{ rct_darkbg ? '244 244 244' : '28 28 26' }};
    --section-padding-sm: 3rem;
    --section-padding-md: 4rem;
    --section-padding-lg: 5rem;
  ">
  <div class="wrapper">
    {# Section Header #}
    {% if rct_title or rct_subtitle %}
      <div class="pb-10 md:pb-16">
        {% if rct_subtitle %}
          <p class="text-eyebrow font-mono uppercase tracking-wider mb-2 text-[rgb(var(--scheme-disabled))]">
            {{ rct_subtitle|e }}
          </p>
        {% endif %}
        {% if rct_title %}
          <h2 class="text-heading-lg font-expanded font-black uppercase leading-heading">
            {{ rct_title|e }}
          </h2>
        {% endif %}
      </div>
    {% endif %}

    {# Main Content Container #}
    <div class="relative flex flex-col lg:flex-row {{ rct_tab_position == 'right' ? 'lg:flex-row-reverse' : '' }} gap-8 lg:gap-12 min-h-[500px] lg:min-h-[600px]">

      {# Tabs List - Vertical Scrolling #}
      <div
        class="rct-tabs-container lg:w-1/2 relative"
        role="tablist"
        aria-orientation="vertical">

        <div class="rct-tabs-wrapper overflow-hidden lg:h-[500px]">
          <div class="rct-tabs-scroll" data-rct-tabs-scroll>
            {% for item in rct_items %}
              <div
                class="rct-tab-item py-6 lg:py-8 transition-all duration-500 {{ loop.first ? 'rct-tab-active' : '' }}"
                role="tab"
                data-rct-tab-index="{{ loop.index0 }}"
                aria-selected="{{ loop.first ? 'true' : 'false' }}"
                aria-controls="rct-panel-{{ loop.index0 }}"
                tabindex="{{ loop.first ? '0' : '-1' }}">

                <div class="border-l-2 pl-4 lg:pl-6 transition-all duration-500 rct-tab-border">
                  <button
                    class="rct-tab-button text-heading-md font-expanded font-black uppercase leading-heading text-left w-full transition-colors duration-300"
                    type="button">
                    {{ item.title|default('Tab Title')|e }}
                  </button>

                  <div class="rct-tab-content mt-3 transition-all duration-500 overflow-hidden">
                    <div class="richtext-prose text-[rgb(var(--scheme-text))] opacity-80">
                      <p>{{ item.description|default('')|e }}</p>
                    </div>
                  </div>
                </div>

                {# Mobile Media - Shows below each tab on mobile #}
                <div class="rct-mobile-media lg:hidden mt-4 pl-4 transition-all duration-500 overflow-hidden">
                  {% if item.media_type == 'video' %}
                    <video
                      playsinline
                      muted
                      loop
                      class="w-full rounded-general object-cover aspect-video"
                      preload="metadata"
                      poster="{{ item.poster|default('')|e }}">
                      <source src="{{ item.media_src|default('')|e }}" type="video/mp4">
                    </video>
                  {% else %}
                    <img
                      src="{{ item.media_src|default('')|e }}"
                      alt="{{ item.media_alt|default('')|e }}"
                      class="w-full rounded-general object-cover"
                      loading="lazy">
                  {% endif %}
                </div>
              </div>
            {% endfor %}
          </div>
        </div>

        {# Navigation Arrows #}
        <div class="hidden lg:flex items-center gap-4 mt-8">
          <button
            class="rct-slider-prev slider-button-prev"
            aria-label="Previous slide"
            type="button">
          </button>
          <button
            class="rct-slider-next slider-button-next"
            aria-label="Next slide"
            type="button">
          </button>
        </div>
      </div>

      {# Media Panel - Vertical Slider with Zoom Effect #}
      <div class="rct-media-container hidden lg:block lg:w-1/2 relative overflow-hidden rounded-general">
        <div class="rct-media-slider h-full" data-rct-media-slider>
          {% for item in rct_items %}
            <div
              class="rct-media-slide absolute inset-0 transition-all duration-700 {{ loop.first ? 'rct-slide-active' : '' }}"
              id="rct-panel-{{ loop.index0 }}"
              role="tabpanel"
              aria-labelledby="rct-tab-{{ loop.index0 }}"
              data-rct-slide-index="{{ loop.index0 }}">

              {% if item.media_type == 'video' %}
                <video
                  playsinline
                  muted
                  loop
                  class="w-full h-full object-cover rounded-general rct-media-element"
                  preload="metadata"
                  poster="{{ item.poster|default('')|e }}">
                  <source src="{{ item.media_src|default('')|e }}" type="video/mp4">
                </video>
              {% else %}
                <img
                  src="{{ item.media_src|default('')|e }}"
                  alt="{{ item.media_alt|default('')|e }}"
                  class="w-full h-full object-cover rounded-general rct-media-element"
                  loading="lazy">
              {% endif %}
            </div>
          {% endfor %}
        </div>

        {# Progress indicator #}
        <div class="absolute bottom-4 left-4 right-4 h-1 bg-[rgb(var(--scheme-disabled))] rounded-full overflow-hidden">
          <div class="rct-progress-fill h-full bg-[rgb(var(--scheme-accent))] transition-all duration-500 rounded-full" style="width: 0%;"></div>
        </div>
      </div>
    </div>
  </div>
</section>
```

---

### 2. CSS Styles

Add to your `slider.css` or create a new `vertical-slider-tabs.css`:

```css
/* ========================================
   Vertical Slider Tabs Component
   ======================================== */

/* Tabs Container */
.rct-tabs-container {
  position: relative;
}

.rct-tabs-wrapper {
  position: relative;
}

@media (min-width: 1024px) {
  .rct-tabs-wrapper {
    mask-image: linear-gradient(
      to bottom,
      transparent 0%,
      black 10%,
      black 90%,
      transparent 100%
    );
  }
}

.rct-tabs-scroll {
  transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Tab Item Styles */
.rct-tab-item {
  cursor: pointer;
  transform: scale(0.95);
  opacity: 0.5;
  transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

.rct-tab-item.rct-tab-active {
  transform: scale(1);
  opacity: 1;
}

.rct-tab-item:hover:not(.rct-tab-active) {
  opacity: 0.75;
}

/* Tab Border Animation */
.rct-tab-border {
  border-color: rgb(var(--scheme-disabled));
  border-image: linear-gradient(
    rgb(var(--scheme-disabled)) 0%,
    rgb(var(--scheme-disabled)) 100%
  ) 1;
}

.rct-tab-active .rct-tab-border {
  border-color: rgb(var(--scheme-text));
  border-image: linear-gradient(
    rgb(var(--scheme-text)) var(--border-progress, 100%),
    rgb(var(--scheme-disabled)) 0%
  ) 1;
}

/* Tab Button */
.rct-tab-button {
  color: rgb(var(--scheme-disabled));
}

.rct-tab-active .rct-tab-button {
  color: rgb(var(--scheme-text));
}

/* Tab Content - Collapsed by default */
.rct-tab-content {
  max-height: 0;
  opacity: 0;
}

.rct-tab-active .rct-tab-content {
  max-height: 200px;
  opacity: 1;
}

/* Mobile Media - Hidden by default */
.rct-mobile-media {
  max-height: 0;
  opacity: 0;
}

.rct-tab-active .rct-mobile-media {
  max-height: 400px;
  opacity: 1;
}

/* Media Container */
.rct-media-container {
  height: 500px;
  background-color: rgb(var(--scheme-disabled-accent));
}

/* Media Slides */
.rct-media-slide {
  opacity: 0;
  transform: scale(1.1) translateY(20px);
  pointer-events: none;
  z-index: 0;
}

.rct-media-slide.rct-slide-active {
  opacity: 1;
  transform: scale(1) translateY(0);
  pointer-events: auto;
  z-index: 1;
}

.rct-media-slide.rct-slide-prev {
  opacity: 0;
  transform: scale(0.9) translateY(-100%);
}

.rct-media-slide.rct-slide-next {
  opacity: 0;
  transform: scale(0.9) translateY(100%);
}

/* Media Element Zoom Effect */
.rct-media-element {
  transition: transform 0.8s cubic-bezier(0.4, 0, 0.2, 1);
}

.rct-slide-active .rct-media-element {
  animation: rctZoomIn 5s ease-out forwards;
}

@keyframes rctZoomIn {
  0% {
    transform: scale(1);
  }
  100% {
    transform: scale(1.05);
  }
}

/* Progress Fill */
.rct-progress-fill {
  transition: width 0.3s ease-out;
}

/* Navigation Buttons */
.rct-slider-prev,
.rct-slider-next {
  position: relative;
  z-index: 1;
  display: inline-flex;
  height: 2.5rem;
  width: 2.5rem;
  cursor: pointer;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background-color: transparent;
  border: 1px solid rgb(var(--scheme-disabled));
  transition: all 0.3s ease;
}

.rct-slider-prev:hover,
.rct-slider-next:hover {
  background-color: rgb(var(--scheme-accent));
  border-color: rgb(var(--scheme-accent));
}

.rct-slider-prev::after,
.rct-slider-next::after {
  display: block;
  width: 1.25rem;
  height: 1.25rem;
  background-color: rgb(var(--scheme-text));
  content: "";
  mask-position: center center;
  mask-repeat: no-repeat;
  mask-size: contain;
  transition: background-color 0.3s ease;
}

.rct-slider-prev:hover::after,
.rct-slider-next:hover::after {
  background-color: rgb(var(--scheme-on-accent));
}

.rct-slider-prev::after {
  mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='currentColor'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M5 15l7-7 7 7'/%3E%3C/svg%3E");
}

.rct-slider-next::after {
  mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='currentColor'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
}

.rct-slider-prev.slider-button-disabled,
.rct-slider-next.slider-button-disabled {
  cursor: not-allowed;
  opacity: 0.3;
}

/* Responsive Adjustments */
@media (max-width: 1023px) {
  .rct-tabs-wrapper {
    height: auto;
    overflow: visible;
  }

  .rct-tab-item {
    transform: scale(1);
    opacity: 1;
  }

  .rct-tab-item:not(.rct-tab-active) .rct-tab-content {
    max-height: 0;
    opacity: 0;
  }

  .rct-tab-item:not(.rct-tab-active) .rct-mobile-media {
    max-height: 0;
    opacity: 0;
  }
}
```

---

### 3. JavaScript

Add to your `main.js`:

```javascript
// Call this function on DOMContentLoaded
document.addEventListener("DOMContentLoaded", function () {
  initializeVerticalSliderTabs();
});

// Vertical Slider Tabs - supports multiple instances
function initializeVerticalSliderTabs() {
  const sections = document.querySelectorAll('[data-component="vertical-slider-tabs"]');

  if (!sections.length) return;

  sections.forEach(function (section) {
    const tabItems = section.querySelectorAll('.rct-tab-item');
    const mediaSlides = section.querySelectorAll('.rct-media-slide');
    const tabsScroll = section.querySelector('[data-rct-tabs-scroll]');
    const progressFill = section.querySelector('.rct-progress-fill');
    const prevBtn = section.querySelector('.rct-slider-prev');
    const nextBtn = section.querySelector('.rct-slider-next');

    if (!tabItems.length) return;

    let currentIndex = 0;
    let autoPlayTimer = null;
    const autoPlayDelay = 5000; // 5 seconds
    const totalTabs = tabItems.length;

    // Update progress bar
    function updateProgress() {
      if (!progressFill) return;
      const progress = ((currentIndex + 1) / totalTabs) * 100;
      progressFill.style.width = progress + '%';
    }

    // Update button states
    function updateButtonState() {
      if (prevBtn) {
        prevBtn.classList.toggle('slider-button-disabled', currentIndex === 0);
      }
      if (nextBtn) {
        nextBtn.classList.toggle('slider-button-disabled', currentIndex === totalTabs - 1);
      }
    }

    // Set active tab and slide
    function setActiveTab(index) {
      if (index < 0 || index >= totalTabs) return;

      currentIndex = index;

      // Update tabs
      tabItems.forEach((tab, i) => {
        const isActive = i === index;
        tab.classList.toggle('rct-tab-active', isActive);
        tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
        tab.setAttribute('tabindex', isActive ? '0' : '-1');
      });

      // Update media slides
      mediaSlides.forEach((slide, i) => {
        slide.classList.remove('rct-slide-active', 'rct-slide-prev', 'rct-slide-next');

        if (i === index) {
          slide.classList.add('rct-slide-active');
          // Play video if exists
          const video = slide.querySelector('video');
          if (video) {
            video.currentTime = 0;
            video.play().catch(() => {});
          }
        } else if (i < index) {
          slide.classList.add('rct-slide-prev');
          const video = slide.querySelector('video');
          if (video) video.pause();
        } else {
          slide.classList.add('rct-slide-next');
          const video = slide.querySelector('video');
          if (video) video.pause();
        }
      });

      // Scroll tabs into view (vertical centering)
      if (tabsScroll && window.innerWidth >= 1024) {
        const tabHeight = tabItems[0].offsetHeight;
        const containerHeight = section.querySelector('.rct-tabs-wrapper').offsetHeight;
        const scrollOffset = (index * tabHeight) - (containerHeight / 2) + (tabHeight / 2);
        tabsScroll.style.transform = `translateY(${-Math.max(0, scrollOffset)}px)`;
      }

      updateProgress();
      updateButtonState();
      resetAutoPlay();
    }

    // Navigate to next/prev
    function goToNext() {
      if (currentIndex < totalTabs - 1) {
        setActiveTab(currentIndex + 1);
      }
    }

    function goToPrev() {
      if (currentIndex > 0) {
        setActiveTab(currentIndex - 1);
      }
    }

    // Auto-play functionality
    function startAutoPlay() {
      if (autoPlayTimer) clearInterval(autoPlayTimer);
      autoPlayTimer = setInterval(() => {
        if (currentIndex < totalTabs - 1) {
          setActiveTab(currentIndex + 1);
        } else {
          setActiveTab(0); // Loop back to start
        }
      }, autoPlayDelay);
    }

    function resetAutoPlay() {
      if (autoPlayTimer) clearInterval(autoPlayTimer);
      startAutoPlay();
    }

    // Event listeners for tabs
    tabItems.forEach((tab, index) => {
      tab.addEventListener('click', () => setActiveTab(index));

      // Keyboard navigation
      tab.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowDown' || e.key === 'ArrowRight') {
          e.preventDefault();
          goToNext();
          tabItems[currentIndex]?.focus();
        } else if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') {
          e.preventDefault();
          goToPrev();
          tabItems[currentIndex]?.focus();
        } else if (e.key === 'Home') {
          e.preventDefault();
          setActiveTab(0);
          tabItems[0]?.focus();
        } else if (e.key === 'End') {
          e.preventDefault();
          setActiveTab(totalTabs - 1);
          tabItems[totalTabs - 1]?.focus();
        }
      });
    });

    // Navigation button listeners
    if (prevBtn) {
      prevBtn.addEventListener('click', goToPrev);
    }
    if (nextBtn) {
      nextBtn.addEventListener('click', goToNext);
    }

    // Pause autoplay on hover
    section.addEventListener('mouseenter', () => {
      if (autoPlayTimer) clearInterval(autoPlayTimer);
    });

    section.addEventListener('mouseleave', () => {
      startAutoPlay();
    });

    // Touch/swipe support for mobile
    let touchStartY = 0;
    let touchEndY = 0;

    section.addEventListener('touchstart', (e) => {
      touchStartY = e.changedTouches[0].screenY;
    }, { passive: true });

    section.addEventListener('touchend', (e) => {
      touchEndY = e.changedTouches[0].screenY;
      handleSwipe();
    }, { passive: true });

    function handleSwipe() {
      const swipeThreshold = 50;
      const diff = touchStartY - touchEndY;

      if (Math.abs(diff) > swipeThreshold) {
        if (diff > 0) {
          goToNext(); // Swipe up
        } else {
          goToPrev(); // Swipe down
        }
      }
    }

    // Initialize
    setActiveTab(0);
    startAutoPlay();
  });
}
```

---

## Usage

### Basic Usage

```twig
{% include "components/vertical-slider-tabs.twig" with {
  rct_title: "Why Choose Us",
  rct_items: [
    {
      title: "Feature One",
      description: "Description for feature one.",
      media_type: "image",
      media_src: "/path/to/image.jpg",
      media_alt: "Feature one image"
    },
    {
      title: "Feature Two",
      description: "Description for feature two.",
      media_type: "image",
      media_src: "/path/to/image2.jpg",
      media_alt: "Feature two image"
    }
  ]
} %}
```

### With Video and Dark Background

```twig
{% include "components/vertical-slider-tabs.twig" with {
  rct_title: "Our Process",
  rct_subtitle: "How It Works",
  rct_darkbg: true,
  rct_tab_position: "right",
  rct_items: [
    {
      title: "Step One",
      description: "First step description.",
      media_type: "video",
      media_src: "/path/to/video.mp4",
      poster: "/path/to/poster.jpg"
    },
    {
      title: "Step Two",
      description: "Second step description.",
      media_type: "image",
      media_src: "/path/to/image.png",
      media_alt: "Step two"
    }
  ]
} %}
```

---

## Variables Reference

| Variable | Type | Default | Description |
|----------|------|---------|-------------|
| `rct_title` | string | null | Section heading |
| `rct_subtitle` | string | null | Section eyebrow text |
| `rct_darkbg` | boolean | false | Enable dark background |
| `rct_tab_position` | string | "left" | Tab position: "left" or "right" |
| `rct_items` | array | [] | Array of tab items |

### Item Object Structure

| Property | Type | Required | Description |
|----------|------|----------|-------------|
| `title` | string | Yes | Tab button text |
| `description` | string | No | Tab content text |
| `media_type` | string | Yes | "image" or "video" |
| `media_src` | string | Yes | URL to media file |
| `media_alt` | string | No | Alt text (for images) |
| `poster` | string | No | Poster image (for videos) |

---

## CSS Custom Properties

The component uses these CSS custom properties for theming:

```css
--scheme-disabled-accent: 73 73 72;   /* Disabled accent color */
--scheme-disabled: 110 110 110;       /* Disabled/inactive color */
--scheme-accent: 17 221 196;          /* Accent color (teal) */
--scheme-on-accent: 28 28 26;         /* Text on accent */
--scheme-background: 244 244 244;     /* Background color */
--scheme-text: 28 28 26;              /* Text color */
```

Override these in your section's inline styles to customize colors.

---

## Customization

### Change Auto-play Speed

In the JavaScript, modify `autoPlayDelay`:

```javascript
const autoPlayDelay = 8000; // 8 seconds
```

### Disable Auto-play

Remove or comment out `startAutoPlay()` call at the end of initialization.

### Change Zoom Animation

Modify the `@keyframes rctZoomIn` in CSS:

```css
@keyframes rctZoomIn {
  0% {
    transform: scale(1);
  }
  100% {
    transform: scale(1.1); /* Increase zoom amount */
  }
}
```

### Adjust Container Height

Modify `.rct-media-container` and `.rct-tabs-wrapper` heights:

```css
.rct-media-container {
  height: 600px; /* Increase height */
}

@media (min-width: 1024px) {
  .rct-tabs-wrapper {
    height: 600px; /* Match media container */
  }
}
```

---

## Dependencies

- **Tailwind CSS** - For utility classes
- **CSS Custom Properties** - For theming (ensure your theme defines the `--scheme-*` variables)

---

## Browser Support

- Chrome 60+
- Firefox 55+
- Safari 11+
- Edge 79+

---

## Accessibility

- ARIA roles: `tablist`, `tab`, `tabpanel`
- Keyboard navigation: Arrow keys, Home, End
- Focus management
- Screen reader friendly
