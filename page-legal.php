<?php
/**
 * 
 * Template Name: Legal page
 * 
 */


$context = Timber::context();

$timber_post     = Timber::get_post();
$context['p'] = $timber_post;
Timber::render( array( 'page-legal.twig', 'page.twig' ), $context /*, 43200*/);