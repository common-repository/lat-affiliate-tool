<?php

require 'utils.php';
require 'class-action-handlers.php';

function latat_shortcode_wp_enqueue_scripts() {
  wp_register_style('slick-style', plugins_url('/assets/vendors/slick/slick.css', __FILE__), array(), time(), 'all');
  wp_register_style('latat-shortcode-style', plugins_url('/assets/css/shortcode.css', __FILE__), array('slick-style'), time(), 'all');
  wp_enqueue_style('shortcode.css', plugins_url('/lat-affiliate-tool/assets/css/shortcode.css'));

  wp_register_script('slick-script', plugins_url('/assets/vendors/slick/slick.js', __FILE__), array('jquery'), '', true);
  wp_register_script('latat-shortcode-script', plugins_url('/assets/js/shortcode.js', __FILE__), array('jquery', 'slick-script'), '', true);
}

add_action('wp_enqueue_scripts', 'latat_shortcode_wp_enqueue_scripts');

add_action('wp_enqueue_scripts', function () {
  if (function_exists('is_amp_endpoint')) {
    echo '<style amp-custom>body {} table#products-added-list { border: 1px solid; }</style>';
    wp_register_style('latat-shortcode-style', plugins_url('/assets/css/shortcode.css', __FILE__), array(), time(), 'all');
    wp_enqueue_style('latat-shortcode-style');
  }
});

function appendHTML(DOMNode $parent, $source) {
  $tmpDoc = new DOMDocument();
  $tmpDoc->loadHTML($source);
  foreach ($tmpDoc->getElementsByTagName('body')->item(0)->childNodes as $node) {
    $node = $parent->ownerDocument->importNode($node, true);
    $parent->appendChild($node);
  }
}

function create_shortcode_thamso($args, $content) {
  wp_enqueue_style('latat-shortcode-style');
  wp_enqueue_script('slick-script');
  wp_enqueue_script('latat-shortcode-script');
  if (function_exists('is_amp_endpoint')) {
    wp_register_style('slick-style', plugins_url('/assets/vendors/slick/slick.css', __FILE__), array(), time(), 'all');
    wp_register_style('latat-shortcode-style', plugins_url('/assets/css/shortcode.css', __FILE__), array('slick-style'), time(), 'all');
    wp_enqueue_style('latat-shortcode-style');
  }

  $table_data = array(
    'display_style' => 1,
    'products' => array(),
    'custom_data' => array(),
    'custom_css' => '',
    'ID' => $args['table_id'],
    'updated_at' => '',
  );

  $utils = new LATAT_Ultils();

  $credentials = [];
  $content_table = get_post($args['table_id']);
  $table_data = array_replace($table_data, $utils->decodeData($content_table->post_content));
  $table_data['custom_css'] = $credentials['custom_css'];

  // update price
  $table_data = $utils->updateItemsPrice($table_data);

  $html = '';
  $html .= '<div class="latat-products-list-wrapper">';
  include 'display-table-style-' . $table_data['display_style'] . '.php';
  $html .= '<i class="latat-products-list-caption">Last update on ' . date("Y-m-d") . ' // Source: Amazon Affiliates</i>';
  $html .= '</div>';

  return $html;
}

function create_shortcode_single_product($args, $content) {
  $searchProduct = new LATAT_Action_Handlers();
  $product = $searchProduct->getProductWithAsin($args['asin']);
  $html = '';
  if ($product && count($product)) {
    $html .= '<div class="latat-single-product-section">';
    $html .= "<img class='latat-single-product-img' src='". $product['image'] ."'>";
    $html .= "<a class='latat-single-product-btn' href='" . $product['detail_page_url'] . "' rel='noopener noreferrer nofollow external sponsored'
          target='_blank'";
    $html .= "style='background: ";
    $html .= $args['background'] && $args['background'] != '' ? $args['background'] : "#b64031";
    $html .= ";";
    $html .= "color: ";
    $html .= $args['color'] && $args['color'] != '' ? $args['color'] : "#fff";
    $html .= "'";
    $html .= ">";
    $html .= '<span>';
    $html .= $args['title'] && $args['title'] != '' ? $args['title'] : 'Buy now on Amazon';
    $html .= '</span>';
    $html .= "</a>";
    $html .= '</div>';
  }
  
  return $html;
}

add_shortcode('latat_table', 'create_shortcode_thamso');
add_shortcode('latat_single', 'create_shortcode_single_product');
