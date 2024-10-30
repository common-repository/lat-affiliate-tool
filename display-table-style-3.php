<?php

$doc = new DOMDocument('1.0');
$wrapper = $doc->createElement('div');
$wrapper->setAttribute('class', 'products-added-list-block-wrapper style-3');

$custom_css = $doc->createElement('style', $table_data['custom_css']);
$custom_css->setAttribute('type', 'text/css');
$wrapper->appendChild($custom_css);

if (count($table_data['products']) > 0) {
  foreach ($table_data['products'] as $product) {
    $block = $doc->createElement('div');
    $block->setAttribute('class', 'products-added-list-block');

    // Img wrapper.
    $img_wrapper = $doc->createElement('div');
    $img_wrapper->setAttribute('class', 'img-wrapper');

    // Image
    $img = $doc->createElement('img');
    $img->setAttribute('src', $product['image']);
    $img->setAttribute('alt', $product['title']);
    $img->setAttribute('title', $product['title']);

    // Add image to a link
    $img_link = $doc->createElement('a');
    $img_link->setAttribute('href', $product['detail_page_url']);
    $img_link->setAttribute('target', '_blank');
    $img_link->setAttribute('rel', 'noopener nofollow noreferrer sponsored');
    $img_link->appendChild($img);

    // append link to wrapper
    $img_wrapper->appendChild($img_link);
    $block->appendChild($img_wrapper);

    // Title wrapper.
    $title_wrapper = $doc->createElement('div');
    $title_wrapper->setAttribute('class', 'title-wrapper');
    $title_inner = $doc->createElement('div');
    $title_inner->setAttribute('class', 'title-inner');

    if ($product['custom_badge']['enabled'] == 'true') {
      $badge_text = $product['custom_badge']['text'];
      $custom_badge = $doc->createElement('div', $badge_text ? $badge_text : 'TOP CHOICE');
      $custom_badge->setAttribute('class', 'top-badge');
      if ($product['custom_badge']['bg'] != '') {
        $style = 'background-color: ' . $product['custom_badge']['bg'] . '; border-color: ' . $product['custom_badge']['bg'];
        $custom_badge->setAttribute('style', $style);
      }
      $title_inner->appendChild($custom_badge);
    }

    $title = $doc->createElement('p', htmlentities($product['title']));
    $title->setAttribute('class', 'product-title');
    $title_inner->appendChild($title);

    $title_wrapper->appendChild($title_inner);
    $block->appendChild($title_wrapper);

    // Details wrapper.
    $detail_wrapper = $doc->createElement('div');
    $detail_wrapper->setAttribute('class', 'detail-wrapper');

    // Product highlights
    if ($product['product_hl']) {
      $tmp_dom = new DOMDocument('1.0');
      $tmp_dom->loadHTML($product['product_hl']);
      appendHTML($detail_wrapper, stripslashes($product['product_hl']));
    } else {
      // $detail_wrapper->appendChild($doc->createElement('div', '-'));
    }

    $block->appendChild($detail_wrapper);

    // Buy button wrapper.
    $buy_btn_wrapper = $doc->createElement('div');
    $buy_btn_wrapper->setAttribute('class', 'buy-btn-wrapper');

    $price_wrapper = $doc->createElement('div');
    $price_wrapper->setAttribute('class', 'price-wrapper');

    if ($product['price'] != '$') {
      $price = $doc->createElement('div', $product['price']);
      $price->setAttribute('class', 'price');
      $price_wrapper->appendChild($price);
      if ($product['prime'] == 'true') {
        $prime_logo = $doc->createElement('div', 'Prime');
        $prime_logo->setAttribute('class', 'prime-logo');
        $price_wrapper->appendChild($prime_logo);
      }
    }

    $buy_btn_text = isset($product['buy_btn']) && $product['buy_btn'] != '' ? $product['buy_btn'] : 'Check on Amazon →';
    $buy_btn = $doc->createElement('a', $buy_btn_text);
    $buy_btn->setAttribute('class', 'button button-buy');
    $buy_btn->setAttribute('href', $product['detail_page_url']);
    $buy_btn->setAttribute('target', '_blank');
    $buy_btn->setAttribute('rel', 'noopener nofollow noreferrer sponsored');

    $buy_btn_wrapper->appendChild($price_wrapper);
    $buy_btn_wrapper->appendChild($buy_btn);
    $block->appendChild($buy_btn_wrapper);

    $wrapper->appendChild($block);
  }
}

$doc->appendChild($wrapper);
$html .= $doc->saveHTML();
