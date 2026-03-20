# Product-Specific Sections

---

## Setup

This section explains how the system was built so you can understand, maintain,
or rebuild it from scratch if needed.

---

### Step 1 — File structure created

The following files were added to the theme:

  views/
  └── components/
  │   └── steps-grid.twig                        ← new component (3-column step grid)
  └── woocommerce/
      └── product-sections/                      ← new folder
          └── tonal-2.twig                       ← Tonal 2 product sections

No plugin, no database — pure Twig files.

---

### Step 2 — single-product.twig wired up

One line was added at the bottom of:

  views/woocommerce/single-product.twig

After the closing </article> tag and before {% endblock %}:

```twig
{# ── Product-specific sections ────────────────────────────────────────── #}
{# If views/woocommerce/product-sections/{slug}.twig exists it is         #}
{# included automatically. Missing files are silently ignored.            #}
{% include 'woocommerce/product-sections/' ~ post.slug ~ '.twig' ignore missing %}
```

How it works:
- `post.slug` is the product's URL slug (e.g. tonal-2, tonal-1-refurb)
- Twig builds the path: woocommerce/product-sections/tonal-2.twig
- `ignore missing` means if no file exists for that product, nothing breaks
- Every other product page is completely unaffected

---

### Step 3 — woocommerce.php (no changes needed)

The include is driven entirely from within Twig using `post.slug` which is
already available via Timber's context. No extra PHP is needed.

woocommerce.php already sets:

```php
$context['post'] = Timber::get_post();
```

Timber's Post object exposes `post.slug` in Twig automatically.

---

### Step 4 — Create a product file (ongoing)

When you need sections on a new product:

1. Find the product slug
     WP Admin → Products → Edit → look at the Permalink field
     e.g. https://yoursite.com/products/tonal-1-refurb → slug is tonal-1-refurb

2. Create the file
     views/woocommerce/product-sections/tonal-1-refurb.twig

3. Add the rows you need using the snippets in this doc below

4. Save — sections appear instantly, no cache flush needed

---

### Components involved

  ┌─────────────────────────────────────────┬──────────────────────────────────────────────────────┐
  │ File                                    │ Role                                                 │
  ├─────────────────────────────────────────┼──────────────────────────────────────────────────────┤
  │ views/woocommerce/single-product.twig   │ The `ignore missing` include — entry point           │
  ├─────────────────────────────────────────┼──────────────────────────────────────────────────────┤
  │ views/woocommerce/product-sections/     │ Folder holding one .twig file per product            │
  ├─────────────────────────────────────────┼──────────────────────────────────────────────────────┤
  │ views/components/row-testimonial-carousel.twig │ Row 1 — press logo slider               │
  ├─────────────────────────────────────────┼──────────────────────────────────────────────────────┤
  │ views/components/row-testimonial-card.twig     │ Row 1 — user testimonial cards          │
  ├─────────────────────────────────────────┼──────────────────────────────────────────────────────┤
  │ views/components/row-text-left.twig     │ Row 2, 5, 6 — left-aligned text block               │
  ├─────────────────────────────────────────┼──────────────────────────────────────────────────────┤
  │ views/components/row-banner-carousel.twig      │ Row 2, 6 — card carousel                │
  ├─────────────────────────────────────────┼──────────────────────────────────────────────────────┤
  │ views/components/vertical-slider-tabs.twig     │ Row 3 — vertical tab + media panel      │
  ├─────────────────────────────────────────┼──────────────────────────────────────────────────────┤
  │ views/components/row-table.twig         │ Row 4 — comparison table + optional FAQ              │
  ├─────────────────────────────────────────┼──────────────────────────────────────────────────────┤
  │ views/components/steps-grid.twig        │ Row 5 — 3-column step grid (new)                    │
  ├─────────────────────────────────────────┼──────────────────────────────────────────────────────┤
  │ views/components/footer-banner.twig     │ Row 5 — full-width image banner with CTA             │
  └─────────────────────────────────────────┴──────────────────────────────────────────────────────┘

---

## How It Works (summary)

  Browser requests /products/tonal-2
       ↓
  woocommerce.php → Timber::render('woocommerce/single-product.twig', $context)
       ↓
  single-product.twig renders the product hero (images + cart column)
       ↓
  {% include 'woocommerce/product-sections/tonal-2.twig' ignore missing %}
       ↓
  tonal-2.twig includes each row component with its data
       ↓
  Components render in order — Row 1, Row 2, Row 3 ...

---

## Available Section Rows

Per-product section pages live in:

  views/woocommerce/product-sections/{product-slug}.twig

`single-product.twig` automatically includes the matching file based on the
product's URL slug. If no file exists for a product, nothing extra renders.

  {% include 'woocommerce/product-sections/' ~ post.slug ~ '.twig' ignore missing %}

To add sections to a new product, create:

  views/woocommerce/product-sections/your-product-slug.twig

---

## Available Section Rows

Each row is a set of existing Twig component includes. Use them in any order
inside the product file. All components live in views/components/.

---

### Row Type 1 — testimonial_section

Components rendered:
  1. components/row-testimonial-carousel.twig  (press logo carousel — slides hardcoded in component)
  2. components/row-testimonial-card.twig      (user testimonial card grid)

```twig
{% include 'components/row-testimonial-carousel.twig' with {
    rct_darkbg: '',           {# '' = light  |  'bg-[rgb(28,28,26)]' = dark #}
    rct_title:  'Section heading'
} %}

{% include 'components/row-testimonial-card.twig' with {
    rct_darkbg:   '',
    rct_title:    'What people are saying',
    testimonials: [
        {
            testimonial_name:                   'First Last',
            testimonial_description:            'Quote text here.',
            testimonial_image_src:              'https://example.com/avatar.jpg',
            testimonial_image_alt_attribute:    'Alt text',
            testimonial_image_srcset_attribute: ''
        },
        { ... }   {# repeat for each card #}
    ]
} %}
```

Fields reference — row-testimonial-card.twig:

  ┌────────────────────────────────────┬──────────────────────────────────────────────────────────────┐
  │ Variable                           │ Description                                                  │
  ├────────────────────────────────────┼──────────────────────────────────────────────────────────────┤
  │ rct_darkbg                         │ '' for light bg, 'bg-[rgb(28,28,26)]' for dark               │
  ├────────────────────────────────────┼──────────────────────────────────────────────────────────────┤
  │ rct_title                          │ Section heading (optional)                                   │
  ├────────────────────────────────────┼──────────────────────────────────────────────────────────────┤
  │ testimonials[].testimonial_name    │ Reviewer name                                                │
  ├────────────────────────────────────┼──────────────────────────────────────────────────────────────┤
  │ testimonials[].testimonial_description │ Quote text (no wrapping quotes needed)                   │
  ├────────────────────────────────────┼──────────────────────────────────────────────────────────────┤
  │ testimonials[].testimonial_image_src │ Avatar image URL                                           │
  ├────────────────────────────────────┼──────────────────────────────────────────────────────────────┤
  │ testimonials[].testimonial_image_alt_attribute │ Alt text for avatar                              │
  ├────────────────────────────────────┼──────────────────────────────────────────────────────────────┤
  │ testimonials[].testimonial_image_srcset_attribute │ srcset string (optional, can be '')           │
  └────────────────────────────────────┴──────────────────────────────────────────────────────────────┘

---

### Row Type 2 & 6 — text_banner

Components rendered:
  1. components/row-text-left.twig       (heading + description + button)
  2. components/row-banner-carousel.twig (card carousel with image, title, description, button)

```twig
{% include 'components/row-text-left.twig' with {
    rct_darkbg:      '',
    rct_title:       'Section heading',
    rct_description: 'Supporting paragraph text.',
    rct_button: {
        rct_button_text:      'Button label',
        rct_button_url:       '/target-page',
        rct_button_arialabel: 'Accessible label'
    }
} %}

{% include 'components/row-banner-carousel.twig' with {
    rct_darkbg:   '',
    rct_title:    '',          {# optional carousel heading #}
    banner_cards: [
        {
            card_title:               'Card heading',
            card_description:         'Card body text.',
            card_image_src:           'https://example.com/image.jpg',
            card_image_alt_attribute: 'Alt text',
            card_button: {
                card_button_text:      'Learn more',
                card_button_url:       '/page',
                card_button_arialabel: 'Learn more about ...'
            }
        },
        { ... }   {# repeat for each card #}
    ]
} %}
```

Fields reference — row-text-left.twig:

  ┌──────────────────────────────┬───────────────────────────────────────────────────────────────┐
  │ Variable                     │ Description                                                   │
  ├──────────────────────────────┼───────────────────────────────────────────────────────────────┤
  │ rct_darkbg                   │ '' light  |  'bg-[rgb(28,28,26)]' dark                        │
  ├──────────────────────────────┼───────────────────────────────────────────────────────────────┤
  │ rct_title                    │ Section heading (optional)                                    │
  ├──────────────────────────────┼───────────────────────────────────────────────────────────────┤
  │ rct_description              │ Paragraph text (optional)                                     │
  ├──────────────────────────────┼───────────────────────────────────────────────────────────────┤
  │ rct_button.rct_button_text   │ CTA label                                                     │
  ├──────────────────────────────┼───────────────────────────────────────────────────────────────┤
  │ rct_button.rct_button_url    │ CTA href                                                      │
  ├──────────────────────────────┼───────────────────────────────────────────────────────────────┤
  │ rct_button.rct_button_arialabel │ Aria label for accessibility                               │
  └──────────────────────────────┴───────────────────────────────────────────────────────────────┘

Fields reference — banner_cards[] items:

  ┌──────────────────────────────────────┬───────────────────────────────────────────────────────┐
  │ Variable                             │ Description                                           │
  ├──────────────────────────────────────┼───────────────────────────────────────────────────────┤
  │ card_title                           │ Card heading                                          │
  ├──────────────────────────────────────┼───────────────────────────────────────────────────────┤
  │ card_description                     │ Card body text                                        │
  ├──────────────────────────────────────┼───────────────────────────────────────────────────────┤
  │ card_image_src                       │ Card image URL                                        │
  ├──────────────────────────────────────┼───────────────────────────────────────────────────────┤
  │ card_image_alt_attribute             │ Alt text                                              │
  ├──────────────────────────────────────┼───────────────────────────────────────────────────────┤
  │ card_button.card_button_text         │ Link label                                            │
  ├──────────────────────────────────────┼───────────────────────────────────────────────────────┤
  │ card_button.card_button_url          │ Link href                                             │
  ├──────────────────────────────────────┼───────────────────────────────────────────────────────┤
  │ card_button.card_button_arialabel    │ Aria label                                            │
  └──────────────────────────────────────┴───────────────────────────────────────────────────────┘

---

### Row Type 3 — vertical_tabs

Components rendered:
  1. components/vertical-slider-tabs.twig

Left column: scrollable tab list with title + description per tab.
Right column: media panel (image or video) that swaps as tabs are clicked.

```twig
{% include 'components/vertical-slider-tabs.twig' with {
    rct_darkbg:       '',
    rct_title:        'Section heading',
    rct_subtitle:     'Optional subtitle',
    rct_description:  'Optional intro paragraph.',
    rct_tab_position: 'left',    {# 'left' or 'right' — which side the tab list sits #}
    rct_items: [
        {
            title:       'Tab label',
            description: 'Tab body text shown when active.',
            media_type:  'image',        {# 'image' or 'video' #}
            media_src:   'https://example.com/image.jpg',
            media_alt:   'Alt text',
            poster:      ''              {# video poster URL, leave '' for images #}
        },
        {
            title:       'Second tab',
            description: 'Description for second tab.',
            media_type:  'video',
            media_src:   'https://example.com/clip.mp4',
            media_alt:   '',
            poster:      'https://example.com/poster.jpg'
        },
        { ... }   {# repeat for each tab #}
    ]
} %}
```

Fields reference — rct_items[] per tab:

  ┌─────────────┬──────────────────────────────────────────────────────────────────────────────┐
  │ Variable    │ Description                                                                  │
  ├─────────────┼──────────────────────────────────────────────────────────────────────────────┤
  │ title       │ Tab button label                                                             │
  ├─────────────┼──────────────────────────────────────────────────────────────────────────────┤
  │ description │ Text shown below the tab title when that tab is active                       │
  ├─────────────┼──────────────────────────────────────────────────────────────────────────────┤
  │ media_type  │ 'image' renders an <img>, 'video' renders a muted autoplay <video>           │
  ├─────────────┼──────────────────────────────────────────────────────────────────────────────┤
  │ media_src   │ Image URL or .mp4 video URL                                                  │
  ├─────────────┼──────────────────────────────────────────────────────────────────────────────┤
  │ media_alt   │ Alt text (images only)                                                       │
  ├─────────────┼──────────────────────────────────────────────────────────────────────────────┤
  │ poster      │ Poster image URL shown before video plays (videos only, optional)            │
  └─────────────┴──────────────────────────────────────────────────────────────────────────────┘

---

### Row Type 4 — table_section

Components rendered:
  1. components/row-table.twig

Comparison table with optional FAQ accordion below it.

```twig
{% include 'components/row-table.twig' with {
    rct_darkbg: '',
    rct_table_heading: [
        'Feature',
        'Tonal 2',
        'Tonal 1 Refurb'
    ],
    rct_table_data: [
        ['Max resistance',  '200 lbs',   '200 lbs'],
        ['Display',         '24" screen', '24" screen'],
        ['Smart features',  'Yes',        'Yes']
    ],
    rct_faqs: [
        { title: 'Question text?', texts: 'Answer text.' },
        { title: 'Another question?', texts: 'Another answer.' }
    ]
} %}
```

Fields reference:

  ┌─────────────────────────┬──────────────────────────────────────────────────────────────────┐
  │ Variable                │ Description                                                      │
  ├─────────────────────────┼──────────────────────────────────────────────────────────────────┤
  │ rct_darkbg              │ '' light  |  'bg-[rgb(28,28,26)]' dark                           │
  ├─────────────────────────┼──────────────────────────────────────────────────────────────────┤
  │ rct_table_heading       │ Array of column header strings                                   │
  ├─────────────────────────┼──────────────────────────────────────────────────────────────────┤
  │ rct_table_data          │ Array of rows, each row is an array of cell strings              │
  ├─────────────────────────┼──────────────────────────────────────────────────────────────────┤
  │ rct_faqs[].title        │ FAQ accordion question                                           │
  ├─────────────────────────┼──────────────────────────────────────────────────────────────────┤
  │ rct_faqs[].texts        │ FAQ accordion answer (supports HTML)                             │
  └─────────────────────────┴──────────────────────────────────────────────────────────────────┘

Note: rct_faqs is optional — pass an empty array [] to render the table only.

---

### Row Type 5 — text_steps_banner

Components rendered:
  1. components/row-text-left.twig    (heading + description + button)
  2. components/steps-grid.twig       (3-column step grid, text only)
  3. components/footer-banner.twig    (full-width image banner with overlay text + CTA)

```twig
{% include 'components/row-text-left.twig' with {
    rct_darkbg:      '',
    rct_title:       'Section heading',
    rct_description: 'Supporting text.',
    rct_button: {
        rct_button_text:      'Button label',
        rct_button_url:       '/page',
        rct_button_arialabel: 'Accessible label'
    }
} %}

{% include 'components/steps-grid.twig' with {
    rct_darkbg: '',
    steps: [
        { label: 'Step 1', title: 'Order online',      description: 'Choose your configuration and place your order.' },
        { label: 'Step 2', title: 'Schedule delivery', description: 'Pick a delivery window that works for you.' },
        { label: 'Step 3', title: 'Start training',    description: 'Your trainer installs it and you start day one.' }
    ]
} %}

{% include 'components/footer-banner.twig' with {
    rct_darkbg:      '',
    rct_title:       'Banner heading',
    rct_description: 'Banner subtext.',
    rct_button_text: 'CTA label',
    rct_button_url:  '/page',
    rct_image: {
        rct_image_url: 'https://example.com/banner.jpg'
    }
} %}
```

Fields reference — steps-grid.twig:

  ┌──────────────────────┬────────────────────────────────────────────────────────────────────┐
  │ Variable             │ Description                                                        │
  ├──────────────────────┼────────────────────────────────────────────────────────────────────┤
  │ rct_darkbg           │ '' light  |  'bg-[rgb(28,28,26)]' dark                             │
  ├──────────────────────┼────────────────────────────────────────────────────────────────────┤
  │ steps[].label        │ Step label shown in accent colour e.g. 'Step 1'                    │
  ├──────────────────────┼────────────────────────────────────────────────────────────────────┤
  │ steps[].title        │ Step heading                                                       │
  ├──────────────────────┼────────────────────────────────────────────────────────────────────┤
  │ steps[].description  │ Step body text                                                     │
  └──────────────────────┴────────────────────────────────────────────────────────────────────┘

Fields reference — footer-banner.twig:

  ┌──────────────────────┬────────────────────────────────────────────────────────────────────┐
  │ Variable             │ Description                                                        │
  ├──────────────────────┼────────────────────────────────────────────────────────────────────┤
  │ rct_darkbg           │ '' light  |  'bg-[rgb(28,28,26)]' dark                             │
  ├──────────────────────┼────────────────────────────────────────────────────────────────────┤
  │ rct_title            │ Overlay heading                                                    │
  ├──────────────────────┼────────────────────────────────────────────────────────────────────┤
  │ rct_description      │ Overlay subtext                                                    │
  ├──────────────────────┼────────────────────────────────────────────────────────────────────┤
  │ rct_button_text      │ CTA button label                                                   │
  ├──────────────────────┼────────────────────────────────────────────────────────────────────┤
  │ rct_button_url       │ CTA href                                                           │
  ├──────────────────────┼────────────────────────────────────────────────────────────────────┤
  │ rct_image.rct_image_url │ Full-width background image URL                                │
  └──────────────────────┴────────────────────────────────────────────────────────────────────┘

---

## Adding a New Product's Sections

1. Find the product URL slug in WP Admin → Products → Edit → Permalink.
2. Create the file:
     views/woocommerce/product-sections/{slug}.twig
3. Copy the skeleton from tonal-2.twig and remove rows you don't need.
4. Fill in real content. Save. The sections appear automatically.

No PHP changes are needed.

---

## Dark Background Toggle

Every component accepts rct_darkbg. Two valid values:

  ''                      → light background (rgb 244 244 244), dark text
  'bg-[rgb(28,28,26)]'    → dark background, light text

The CSS custom properties --scheme-background and --scheme-text are set
automatically based on this value inside each component.
