<?php

$prop_display = array(
    'image',
    'title',
    'rating'
);

$prop_display = array_merge($prop_display, $properties_selected);
$prop_display[] = 'buy';

$doc = new DOMDocument('1.0');
$wrapper = $doc->createElement('div');
$wrapper->setAttribute('class', 'products-added-list-table-wrapper style-2');

$srcoller = $doc->createElement('div');
$srcoller->setAttribute('class', 'products-added-list-table-scroller');

$root = $doc->createElement('table');
$root->setAttribute('id', 'products-added-list');
$root->setAttribute('class', 'style-2');

if (count($products_list) > 0) {
	
	$img_row = $doc->createElement('tr');
	$img_cell_head = $doc->createElement('th', 'Image');
	$img_row->appendChild($img_cell_head);
	foreach ($products_list as $product) {
		$img_cell_product = $doc->createElement('td');
		$is_top = isset($table_data['top_selected']) && $table_data['top_selected'] == $product['ASIN'];
		if ($is_top) $img_cell_product->setAttribute('class', 'is-top');
		
		$product_img = $doc->createElement('img');
		$product_img->setAttribute('src', $product['Images']['Primary']['Medium']['URL']);
		$product_img->setAttribute('width', isset($product['Images']['Primary']['Medium']['Width']) ? $product['Images']['Primary']['Medium']['Width'] : '');
		$product_img->setAttribute('height', isset($product['Images']['Primary']['Medium']['Height']) ? $product['Images']['Primary']['Medium']['Height'] : '');
		$img_cell_product->appendChild($product_img);
		$img_row->appendChild($img_cell_product);
	}
	$root->appendChild($img_row);
	
	// Title row.
	$title_row = $doc->createElement('tr');
	$title_cell_head = $doc->createElement('th', 'Title');
	$title_row->appendChild($title_cell_head);
	foreach ($products_list as $product) {
		$title_cell_product = $doc->createElement('td', $product['ItemInfo']['Title']['DisplayValue']);
		$is_top = isset($table_data['top_selected']) && $table_data['top_selected'] == $product['ASIN'];
		if ($is_top) $title_cell_product->setAttribute('class', 'is-top');
		$title_row->appendChild($title_cell_product);
	}
	$root->appendChild($title_row);
	
	// Rating row.
	$rating_row = $doc->createElement('tr');
	$rating_cell_head = $doc->createElement('th', 'Rating');
	$rating_row->appendChild($rating_cell_head);
	foreach ($products_list as $product) {
		$rating_cell_product = $doc->createElement('td');
		$is_top = isset($table_data['top_selected']) && $table_data['top_selected'] == $product['ASIN'];
		if ($is_top) $rating_cell_product->setAttribute('class', 'is-top');
		
		$rate = isset($table_data['ratings'][$product['ASIN']]) ? $table_data['ratings'][$product['ASIN']] : 0;
		$product_rating_wrapper = $doc->createElement('div');
		$product_rating_wrapper->setAttribute('class', 'product-rating-wrapper');
		
		$product_rating_span = $doc->createElement('span');
		$product_rating_span->setAttribute('class', 'product-rating');
		$product_rating_span->setAttribute('style', "width:" . ($rate * 20) . "%");
		
		$product_rating_wrapper->appendChild($product_rating_span);
		$rating_cell_product->appendChild($product_rating_wrapper);
		$rating_row->appendChild($rating_cell_product);
	}
	$root->appendChild($rating_row);

	foreach ($table_data['table_columns'] as $key => $column) {
		$prop_row = $doc->createElement('tr');
		if ( $column['checked'] == 'true') {
			$prop_cell_head = $doc->createElement('th', $column['name']);
			$prop_row->appendChild($prop_cell_head);
			if ( $column['buit-in'] == 'true' ) {
				foreach ($products_list as $product) {
					if ( $key == 'Offers.Listings.Price' ) {
						if (
							isset( $product['Offers'] )
							&& isset( $product['Offers']['Listings'] )
							&& count( $product['Offers']['Listings'] ) > 0
							&& isset( $product['Offers']['Listings'][0]['Price'] )
							&& isset( $product['Offers']['Listings'][0]['Price']['DisplayAmount'] )
						) {
							$prop_cell_product = $doc->createElement('td', $product['Offers']['Listings'][0]['Price']['DisplayAmount']);
						} else {
							$prop_cell_product = $doc->createElement('td', '-');
						}
					}
					if ( $key == 'Offers.Listings.MerchantInfo' ) {
						if (
							isset( $product['Offers'] )
							&& isset( $product['Offers']['Listings'] )
							&& count( $product['Offers']['Listings'] ) > 0
							&& isset( $product['Offers']['Listings'][0]['MerchantInfo'] )
							&& isset( $product['Offers']['Listings'][0]['MerchantInfo']['Name'] )
						) {
							$prop_cell_product = $doc->createElement('td', $product['Offers']['Listings'][0]['MerchantInfo']['Name']);
						} else {
							$prop_cell_product = $doc->createElement('td', '-');
						}
					}
					$is_top = isset($table_data['top_selected']) && $table_data['top_selected'] == $product['ASIN'];
					if ($is_top) $prop_cell_product->setAttribute('class', 'is-top');
					$prop_row->appendChild($prop_cell_product);
				}
			} else {
				
				foreach ($products_list as $product) {
					if ( isset( $table_data['custom_columns_data'][ $key ][ $product['ASIN'] ] ) && ! empty( $table_data['custom_columns_data'][ $key ][ $product['ASIN'] ] ) ) {
						$prop_cell_product = $doc->createElement( 'td', $table_data['custom_columns_data'][ $key ][ $product['ASIN'] ] );
					} else {
						$prop_cell_product = $doc->createElement( 'td', '-' );
					}
					$is_top = isset($table_data['top_selected']) && $table_data['top_selected'] == $product['ASIN'];
					if ($is_top) $prop_cell_product->setAttribute('class', 'is-top');
					$prop_row->appendChild($prop_cell_product);
				}
			}
			
			$root->appendChild($prop_row);
		}
	}
	
	// Buy row.
	$buy_row = $doc->createElement('tr');
	$buy_cell_head = $doc->createElement('th');
	$buy_row->appendChild($buy_cell_head);
	foreach ($products_list as $product) {
		$buy_cell_product = $doc->createElement( 'td' );
		$is_top           = isset( $table_data['top_selected'] ) && $table_data['top_selected'] == $product['ASIN'];
		if ( $is_top ) {
			$buy_cell_product->setAttribute( 'class', 'is-top' );
		}
		$buy_btn_text = isset($table_data['buy-btn-text'][$product['ASIN']]) ? $table_data['buy-btn-text'][$product['ASIN']] : 'Check on Amazon →';
		$product_buy_button = $doc->createElement( 'button', $buy_btn_text );
		$product_buy_button->setAttribute( 'class', 'button button-buy' );
		$product_buy_button->setAttribute( 'data-product_url', $product['DetailPageURL'] );
		
		$buy_cell_product->appendChild( $product_buy_button );
		$buy_row->appendChild( $buy_cell_product );
	}
	$root->appendChild($buy_row);
}

$srcoller->appendChild($root);
$wrapper->appendChild($srcoller);
$doc->appendChild($wrapper);
$html .= $doc->saveHTML();
