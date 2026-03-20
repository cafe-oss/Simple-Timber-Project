<?php

use Timber\Timber;

$context = Timber::context();

$context['coaches'] = Timber::get_posts([
    'post_type' => 'coaches',
    'posts_per_page' => -1,
]);

Timber::render('page-coaches.twig', $context);