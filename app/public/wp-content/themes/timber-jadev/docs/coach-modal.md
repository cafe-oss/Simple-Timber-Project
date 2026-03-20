# Coach Modal

A single reusable `<dialog>` popup that displays coach details when "Learn More" is clicked.

## How It Works

1. Each coach's "Learn More" button stores data in `data-*` attributes
2. `openCoachModal(btn)` reads those attributes, populates the dialog, and calls `showModal()`
3. One `<dialog id="coach-modal">` is shared across all coaches

## Files

| File | Role |
|------|------|
| `views/page-coaches.twig` | Button with `data-*` attrs + dialog markup |
| `assets/js/main.js` | `openCoachModal()` function |

## Data Attributes on Button

| Attribute | Description |
|-----------|-------------|
| `data-coach-title` | Coach name |
| `data-coach-image` | Coach image URL (optional) |
| `data-coach-style` | Coaching style text |
| `data-coach-content` | Full bio/details (HTML, escaped via `e('html_attr')`) |

## Closing the Modal

- Click the **X** button
- Click the **backdrop** (outside the dialog)
- Press **Escape**
