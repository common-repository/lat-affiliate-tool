<?php

/**
 * Register a custom post type called "paa_table".
 *
 * @see get_post_type_labels() for label keys.
 */
function latat_table_post_type() {
  $labels = array(
    'name' => _x('Table products list', 'Table product list', 'latat'),
  );

  $args = array(
    'labels' => $labels,
    'public' => false,
    'publicly_queryable' => false,
    'show_ui' => false,
    'show_in_menu' => false,
    'query_var' => false,
    'capability_type' => 'post',
    'has_archive' => true,
    'hierarchical' => false,
    'menu_position' => null,
    'supports' => array('title', 'editor'),
  );

  register_post_type('paa_table', $args);
}

add_action('init', 'latat_table_post_type');