<?php
$context = Timber::context();


$block_id = get_field('menu_id', 'options');
$block_content = get_post($block_id)->post_content;
$parsed_blocks = parse_blocks( $block_content );

if ( $parsed_blocks ) {
    foreach ( $parsed_blocks as $block ) {
        echo render_block( $block );
    }
}



$context['b'] = get_fields();
$context['is_preview'] = $is_preview;

Timber::render( 'twig/acf-blocks/menu_nav.twig', $context );