<?php

$doc = new DOMDocument( '1.0' );
if ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) {
	$wrapper = $doc->createElement( 'amp-base-carousel' );
	$wrapper->setAttribute( 'layout', 'responsive' );
	$wrapper->setAttribute( 'type', 'slides' );
	$wrapper->setAttribute( 'width', '1' );
	$wrapper->setAttribute( 'height', '1' );
	$wrapper->setAttribute( 'true', 'true' );
	$wrapper->setAttribute( 'heights', '(min-width: 1000px) calc(60% * 1), (min-width: 600px) calc(60% * 1), (min-width: 400px) calc(120% * 1), calc(150% * 1)' );
	$wrapper->setAttribute( 'visible-count', '(min-width: 1000px) 4, (min-width: 660px) 3, (min-width: 400px) 2, 1' );
	// visible-count="(min-width: 600px) 4, (min-width: 400px) 3, 2"
	$wrapper->setAttribute( 'class', 'products-added-list-block-wrapper style-4' );
} else {
	$wrapper = $doc->createElement( 'div' );
	$wrapper->setAttribute( 'class', 'products-added-list-block-wrapper style-4' );
}

if ( count( $products_list ) > 0 ) {
	foreach ( $products_list as $product ) {
		if ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) {
			$block_wrapper = $doc->createElement( 'div' );
			$block_wrapper->setAttribute( 'class', 'products-added-list-block-wrapper' );
		}
		$block = $doc->createElement( 'div' );
		$is_top = isset($table_data['top_selected']) && $table_data['top_selected'] == $product['ASIN'];
		
		$block->setAttribute( 'class', 'products-added-list-block' . ( ( $is_top ) ? ' is-top' : '') );
		
		// Img wrapper.
		$img_wrapper = $doc->createElement( 'div' );
		$img_wrapper->setAttribute( 'class', 'img-wrapper' );
		
		if ( $is_top ) {
			$img_top_badge = $doc->createElement( 'div', 'BEST CHOICE' );
			$img_top_badge->setAttribute( 'class', 'top-badge img-top-badge' );
			$img_wrapper->appendChild( $img_top_badge );
		}
		
		$img = $doc->createElement( 'img' );
		$img->setAttribute( 'src', $product['Images']['Primary']['Medium']['URL'] );
		$img->setAttribute( 'width', $product['Images']['Primary']['Medium']['Width'] );
		$img->setAttribute( 'height', $product['Images']['Primary']['Medium']['Height'] );
		$img_wrapper->appendChild( $img );
		
		// Rating under the thumb.
		// $rate = isset( $table_data['ratings'][$product['ASIN']] ) ? $table_data['ratings'][$product['ASIN']] : 0;
		// $rating = $doc->createElement( 'div' );
		// $rating->setAttribute( 'class', 'product-rating-wrapper' );
		
		// $rating_span = $doc->createElement( 'span' );
		// $rating_span->setAttribute( 'class', 'product-rating' );
		// $rating_span->setAttribute( 'style', "width:" . ($rate * 20) . "%" );
		// $rating->appendChild( $rating_span );
		// $img_wrapper->appendChild( $rating );
		
		$block->appendChild( $img_wrapper );
		
		// Rating wrapper.
		$rating_wrapper = $doc->createElement( 'div' );
		$rating_wrapper->setAttribute( 'class', 'rating-wrapper' );

		$rate = isset( $table_data['ratings'][$product['ASIN']] ) ? $table_data['ratings'][$product['ASIN']] : 0;
		$rating = $doc->createElement( 'div' );
		$rating->setAttribute( 'class', 'product-rating-wrapper' );

		$rating_span = $doc->createElement( 'span' );
		$rating_span->setAttribute( 'class', 'product-rating' );
		$rating_span->setAttribute( 'style', "width:" . ($rate * 20) . "%" );
		$rating->appendChild( $rating_span );
		$rating_wrapper->appendChild( $rating );
		$block->appendChild( $rating_wrapper );
		
		// Title wrapper.
		$title_wrapper = $doc->createElement( 'div' );
		$title_wrapper->setAttribute( 'class', 'title-wrapper' );
//
//		if ( $is_top ) {
//			$title_top_badge = $doc->createElement( 'div', 'BEST CHOICE' );
//			$title_top_badge->setAttribute( 'class', 'top-badge' );
//			$title_wrapper->appendChild( $title_top_badge );
//		}
		
		$title = $doc->createElement( 'p', $product['ItemInfo']['Title']['DisplayValue'] );
		$title->setAttribute( 'class', 'product-title' );
		$title_wrapper->appendChild( $title );
		$block->appendChild( $title_wrapper );
		
		// Rating wrapper.
//		$rating_wrapper = $doc->createElement( 'div' );
//		$rating_wrapper->setAttribute( 'class', 'rating-wrapper' );
//
//		$rate = isset( $table_data['ratings'][$product['ASIN']] ) ? $table_data['ratings'][$product['ASIN']] : 0;
//		$rating = $doc->createElement( 'div' );
//		$rating->setAttribute( 'class', 'product-rating-wrapper' );
//
//		$rating_span = $doc->createElement( 'span' );
//		$rating_span->setAttribute( 'class', 'product-rating' );
//		$rating_span->setAttribute( 'style', "width:" . ($rate * 20) . "%" );
//		$rating->appendChild( $rating_span );
//		$rating_wrapper->appendChild( $rating );
//		$block->appendChild( $rating_wrapper );
		
		// Details wrapper.
		$detail_wrapper = $doc->createElement( 'div' );
		$detail_wrapper->setAttribute( 'class', 'detail-wrapper' );
		
		$list_details = $doc->createElement( 'ul' );
		foreach ($table_data['table_columns'] as $key => $column) {
			if ($column['checked'] == 'true') {
				$item_detail = $doc->createElement( 'li' );
				if ( $column['buit-in'] == 'true' ) {
					if ( isset( $product['Offers'] ) ) {
						if ( isset( $product['Offers']['Listings'] ) ) {
							if ( count( $product['Offers']['Listings'] ) > 0 ) {
								if ( $key == 'Offers.Listings.Price' ) {
									if ( isset( $product['Offers']['Listings'][0]['Price'] ) ) {
										if ( isset( $product['Offers']['Listings'][0]['Price']['DisplayAmount'] ) ) {
											$item_detail_span = $doc->createElement( 'span', $product['Offers']['Listings'][0]['Price']['DisplayAmount'] );
										}
									}
								}
								if ( $key == 'Offers.Listings.MerchantInfo' ) {
									if ( isset( $product['Offers']['Listings'][0]['MerchantInfo'] ) ) {
										if ( isset( $product['Offers']['Listings'][0]['MerchantInfo']['Name'] ) ) {
											$item_detail_span = $doc->createElement( 'span', $product['Offers']['Listings'][0]['MerchantInfo']['Name'] );
										}
									}
								}
							}
						}
					}
				} else {
					$item_detail_span = $doc->createElement( 'span', ! empty( $table_data['custom_columns_data'][$key][$product['ASIN']] ) ?
						$table_data['custom_columns_data'][$key][$product['ASIN']] : '-' );
				}
				$item_detail->appendChild( $item_detail_span );
				$list_details->appendChild( $item_detail );
			}
		}
		$detail_wrapper->appendChild( $list_details );
		$block->appendChild( $detail_wrapper );
		
		// Buy button wrapper.
		$buy_btn_wrapper = $doc->createElement( 'div' );
		$buy_btn_wrapper->setAttribute( 'class', 'buy-btn-wrapper' );
		
		$buy_btn_text = isset($table_data['buy-btn-text'][$product['ASIN']]) ? $table_data['buy-btn-text'][$product['ASIN']] : 'Check on Amazon →';
		$buy_btn = $doc->createElement( 'button', $buy_btn_text);
		$buy_btn->setAttribute( 'class', 'button button-buy' );
		$buy_btn->setAttribute( 'data-target_url', $product['DetailPageURL'] );
		
		$buy_btn_wrapper->appendChild( $buy_btn );
		$block->appendChild( $buy_btn_wrapper );
		
		
		if ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) {
			$block_wrapper->appendChild($block);
			$wrapper->appendChild($block_wrapper);
		} else {
			$wrapper->appendChild($block);
		}
	}
}

$doc->appendChild($wrapper);
$html .= $doc->saveHTML();
