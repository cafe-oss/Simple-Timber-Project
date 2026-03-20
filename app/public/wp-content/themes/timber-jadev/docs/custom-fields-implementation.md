1. The template is automatically loaded - Your existing page.php already looks for page-{slug}.twig, so it will use this template for /tonal-equipment-overview/                                              
  2. ACF fields are accessed via post.meta():
  {{ post.meta('description_row1') }}
  {{ post.meta('title_row2') }}
  {{ post.meta('description_row2') }}

  Common ACF Field Types in Twig
  ┌──────────────────────┬───────────────────────────────────────────────┐
  │    ACF Field Type    │                  Twig Usage                   │
  ├──────────────────────┼───────────────────────────────────────────────┤
  │ Text/Textarea        │ {{ post.meta('field_name') }}                 │
  ├──────────────────────┼───────────────────────────────────────────────┤
  │ WYSIWYG              │ `{{ post.meta('field_name')                   │
  ├──────────────────────┼───────────────────────────────────────────────┤
  │ Image (return ID)    │ {{ Image(post.meta('image_field')).src }}     │
  ├──────────────────────┼───────────────────────────────────────────────┤
  │ Image (return array) │ {{ post.meta('image_field').url }}            │
  ├──────────────────────┼───────────────────────────────────────────────┤
  │ Repeater             │ {% for item in post.meta('repeater_field') %} │
  ├──────────────────────┼───────────────────────────────────────────────┤
  │ True/False           │ {% if post.meta('boolean_field') %}           │
  └──────────────────────┴───────────────────────────────────────────────┘
