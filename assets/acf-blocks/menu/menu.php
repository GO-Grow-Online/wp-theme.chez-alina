<?php
$context = Timber::context();

$context['b_prop'] = $block;
$context['b'] = get_fields();
$context['is_preview'] = $is_preview;

Timber::render( 'twig/acf-blocks/menu.twig', $context );