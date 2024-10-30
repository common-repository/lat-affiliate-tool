<?php

$html .= '<div class="products-added-list-table-wrapper style-1"><table id="products-added-list" class="widefat fixed striped style-1" cellspacing="0" data-item_ids="' . implode(';', $table_data['item_ids']) . '" data-properties_display="' .
         implode
	(';', $properties_selected) . '" width="100%">';
$html .= '<thead><tr> <th id="columnname" class="manage-column column-columnname" scope="col" width="76">Image</th><th id="columnname" class="manage-column column-columnname" scope="col">Title</th><th>Rating</th>';
//foreach ($properties_selected as $property) {
//    if ($property == 'Offers.Listings.Price') {
//        $html .= '<th id="column-price" class="manage-column custom-column column-price" scope="col">Price</th>';
//    }
//    if ($property == 'Offers.Listings.MerchantInfo') {
//        $html .= '<th id="column-merchant" class="manage-column custom-column column-merchant" scope="col">Merchant</th>';
//    }
//}
//$html .= '<th></th></tr></thead>';
//
//$html .= '<tfoot><tr><th id="columnname" class="manage-column column-columnname" scope="col">Image</th><th id="columnname" class="manage-column column-columnname" scope="col">Title</th><th>Rating</th>';
//foreach ($properties_selected as $property) {
//    if ($property == 'Offers.Listings.Price') {
//        $html .= '<th id="column-price" class="manage-column custom-column column-price" scope="col">Price</th>';
//    }
//    if ($property == 'Offers.Listings.MerchantInfo') {
//        $html .= '<th id="column-merchant" class="manage-column custom-column column-merchant" scope="col">Merchant</th>';
//    }
//}
foreach ($table_data['table_columns'] as $column) {
	if ($column['checked'] == 'true') {
		$html .= '<th id="column-price" class="manage-column custom-column column-price" scope="col">' .  $column['name'] . '</th>';
	}
}
$html .= '<th></th></tr></thead>';

$html .= '<tfoot><tr><th id="columnname" class="manage-column column-columnname" scope="col">Image</th><th id="columnname" class="manage-column column-columnname" scope="col">Title</th><th>Rating</th>';
foreach ($table_data['table_columns'] as $column) {
	if ($column['checked'] == 'true') {
		$html .= '<th id="column-price" class="manage-column custom-column column-price" scope="col">' .  $column['name'] . '</th>';
	}
}
$html .= '<th></th></tr></tfoot>';

$html .= '<tbody>';
if (count($products_list) > 0) {
    foreach ($products_list as $product) {
        // var_dump($product);
        $is_top = isset($table_data['top_selected']) && $table_data['top_selected'] == $product['ASIN'];
        $html .= '<tr class="' . ($is_top ? 'is-top' : '') . '">';
        $html .= '<td><img src="' . $product['Images']['Primary']['Small']['URL'] . '" width="' . $product['Images']['Primary']['Small']['Width'] . '" height="' . $product['Images']['Primary']['Small']['Height'] . '"/></td>';
        $html .= '<td>' . $product['ItemInfo']['Title']['DisplayValue'] . '</td>';

        // Rating.
        $rate = isset($table_data['ratings'][$product['ASIN']]) ? $table_data['ratings'][$product['ASIN']] : 0;
        $html .= '<td><div class="product-rating-wrapper"><span class="product-rating" style="width:' . ($rate * 20) . '%"></span></div></td>';
		
        // Table seleted column
	    foreach ($table_data['table_columns'] as $key => $column) {
	    	if ($column['checked'] == 'true') {
			    if ( $column['buit-in'] == 'true' ) {
			    	if ($key == 'Offers.Listings.Price') {
					    if (
						    isset( $product['Offers'] )
						    && isset( $product['Offers']['Listings'] )
						    && count( $product['Offers']['Listings'] ) > 0
						    && isset( $product['Offers']['Listings'][0]['Price'] )
						    && isset( $product['Offers']['Listings'][0]['Price']['DisplayAmount'] )
					    ) {
						    $html .= '<td class="custom-column column-price" scope="col">' . $product['Offers']['Listings'][0]['Price']['DisplayAmount'] . '</td>';
					    } else {
						    $html .= '<td class="custom-column column-price" scope="col">-</td>';
					    }
				    }
				    if ($key == 'Offers.Listings.MerchantInfo') {
					    if (
						    isset( $product['Offers'] )
						    && isset( $product['Offers']['Listings'] )
						    && count( $product['Offers']['Listings'] ) > 0
						    && $key == 'Offers.Listings.MerchantInfo'
						    && isset( $product['Offers']['Listings'][0]['MerchantInfo'] )
						    && isset( $product['Offers']['Listings'][0]['MerchantInfo']['Name'] )
					    ) {
						    $html .= '<td class="custom-column column-merchant" scope="col">' . $product['Offers']['Listings'][0]['MerchantInfo']['Name'] . '</td>';
					    } else {
						    $html .= '<td class="custom-column column-merchant" scope="col">-</td>';
					    }
				    }
			    } else {
				    $html .= '<td class="custom-column column-' . $key  . '" scope="col">' . (! empty( $table_data['custom_columns_data'][$key][$product['ASIN']] ) ?
						    $table_data['custom_columns_data'][$key][$product['ASIN']] : '-') . '</td>';
			    }
		    }
	    }
	    $buy_btn_text = isset($table_data['buy-btn-text'][$product['ASIN']]) ? $table_data['buy-btn-text'][$product['ASIN']] : 'Check on Amazon →';
        $html .= '<td><a class="button button-buy" href="' . $product['DetailPageURL'] . '">' . $buy_btn_text . '</a></td>';
        $html .= '</tr>';
    }
} else {
    $html .= '<tr class="no-items"><td class="colspanchange" colspan="' + (2 + count($properties_selected)) + '">No items found.</td></tr>';
}
$html .= '</tbody>';
$html .= '</table></div>';
