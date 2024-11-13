<?php
/**
 * 
 * Template Name: Menu
 * 
 */


$context = Timber::context();

$timber_post = Timber::get_post();
$context['p'] = $timber_post;

Timber::render( array( 'page-menu.twig', 'page.twig' ), $context /*, 43200*/);