
wp-content\themes\timber-jadev\views\woocommerce\cart.twig
```php
{% extends 'base.twig' %}

{% block content %}
    <div class="wrapper py-12 px-5">
        <h1 class="text-heading-ml mb-8">Your Cart</h1>
        {{ function('woocommerce_content') }}
    </div>
{% endblock %}
```

wp-content\themes\timber-jadev\views\woocommerce\checkout.twig
```php
{% extends 'base.twig' %}

{% block content %}
    <div class="wrapper py-12 px-5">
        <h1 class="text-heading-ml mb-8">Checkout</h1>
        {{ function('woocommerce_content') }}
    </div>
{% endblock %}
