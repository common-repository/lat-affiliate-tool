<?php

$prop_display = array(
    'image',
    'title',
    'rating'
);

$prop_display = array_merge($prop_display, $properties_selected);
$prop_display[] = 'buy';

$doc = new DOMDocument('1.0');

$root = $doc->createElement('div');
$root_id = $doc->createAttribute('id');
$root_id->value = 'products-added-list';
$root_class = $doc->createAttribute('class');
$root_class->value = 'style-2';
$root->appendChild($root_id);
$root->appendChild($root_class);

$title_div_wrapper = $doc->createElement('div');
$title_div_wrapper_class = $doc->createAttribute('class');
$title_div_wrapper_class->value = 'title_div_wrapper';
$title_div_wrapper->appendChild($title_div_wrapper_class);

foreach ($prop_display as $prop) {
    $title_row = $doc->createElement('div');
    $title_row_class = $doc->createAttribute('class');
    $title_row_class->value = 'product-prop';
    if ($prop == 'image') {
        $title_row_class->value .= ' prop-image';
        $title_row_text = $doc->createTextNode("Image");
    } else if ($prop == 'title') {
        $title_row_class->value .= ' prop-title';
        $title_row_text = $doc->createTextNode("Title");
    } else if ($prop == 'rating') {
        $title_row_class->value .= ' prop-rating';
        $title_row_text = $doc->createTextNode("Rating");
    } else if ($prop == 'Offers.Listings.Price') {
        $title_row_class->value .= ' prop-price';
        $title_row_text = $doc->createTextNode("Price");
    } else if ($prop == 'Offers.Listings.MerchantInfo') {
        $title_row_class->value .= ' prop-merchant';
        $title_row_text = $doc->createTextNode("Merchant");
    } else if ($prop == 'buy') {
        $title_row_class->value .= ' prop-buy';
        $title_row_text = $doc->createTextNode("Buy");
    }
    $title_row->appendChild($title_row_class);
    $title_row->appendChild($title_row_text);
    $title_div_wrapper->appendChild($title_row);
}

$root->appendChild($title_div_wrapper);
if (count($products_list) > 0) {
    foreach ($products_list as $product) {
        $product_div_wrapper = $doc->createElement('div');
        $product_div_wrapper_class = $doc->createAttribute('class');
        $product_div_wrapper_class->value = 'product_div_wrapper';
        $product_div_wrapper->appendChild($product_div_wrapper_class);

        foreach ($prop_display as $prop) {
            $product_row = $doc->createElement('div');
            $product_row_class = $doc->createAttribute('class');
            $product_row_class->value = 'product-prop';
            if ($prop == 'image') {
                $product_row_class->value .= ' prop-image';
                $product_img = $doc->createElement('img');
                $src_attr = $doc->createAttribute('src');
                $src_attr->value = $product['Images']['Primary']['Medium']['URL'];
                $width_attr = $doc->createAttribute('width');
                $width_attr->value = $product['Images']['Primary']['Medium']['width'];
                $height_attr = $doc->createAttribute('height');
                $height_attr->value = $product['Images']['Primary']['Medium']['height'];
                $product_img->appendChild($src_attr);
                $product_img->appendChild($width_attr);
                $product_img->appendChild($height_attr);
                $product_row->appendChild($product_img);
            } else if ($prop == 'title') {
                $product_row_class->value .= ' prop-title';
                $product_row_text = $doc->createTextNode($product['ItemInfo']['Title']['DisplayValue']);
                $product_row->appendChild($product_row_text);
            } else if ($prop == 'rating') {
                $product_row_class->value .= ' prop-rating';

                $rate = isset($table_data['ratings'][$product['ASIN']]) ? $table_data['ratings'][$product['ASIN']] : 0;
                $product_rating_wrapper = $doc->createElement('div');
                $product_rating_wrapper_class = $doc->createAttribute('class');
                $product_rating_wrapper_class->value = 'product-rating-wrapper';

                $product_rating_span = $doc->createElement('span');
                $product_rating_span_class = $doc->createAttribute('class');
                $product_rating_span_class->value = 'product-rating';
                $product_rating_span_style = $doc->createAttribute('style');
                $product_rating_span_style->value = "width:" . ($rate * 20) . "%";
                $product_rating_span->appendChild($product_rating_span_class);
                $product_rating_span->appendChild($product_rating_span_style);
                $product_rating_wrapper->appendChild($product_rating_wrapper_class);
                $product_rating_wrapper->appendChild($product_rating_span);
                $product_row->appendChild($product_rating_wrapper);
            } else if ($prop == 'buy') {
                $product_row_class->value .= ' prop-buy-buton';
                $buy_btn = $doc->createElement('button', 'Buy');
                $buy_btn_class = $doc->createAttribute('class');
                $buy_btn_class->value = 'button button-buy';
                $buy_btn_product_url = $doc->createAttribute('data-product_url');
                $buy_btn_product_url->value = $product['DetailPageURL'];
                $buy_btn->appendChild($buy_btn_class);
                $buy_btn->appendChild($buy_btn_product_url);
                $product_row->appendChild($buy_btn);
            } else {
                if (isset($product['Offers'])) {
                    if (isset($product['Offers']['Listings'])) {
                        if (count($product['Offers']['Listings']) > 0) {
                                if ($prop == 'Offers.Listings.Price') {
                                    if (isset($product['Offers']['Listings'][0]['Price'])) {
                                        if (isset($product['Offers']['Listings'][0]['Price']['DisplayAmount'])) {
                                            $product_row_class->value .= ' prop-price';
                                            $product_row_text = $doc->createTextNode($product['Offers']['Listings'][0]['Price']['DisplayAmount']);
                                            $product_row->appendChild($product_row_text);
                                        }
                                    }
                                }
                                if ($prop == 'Offers.Listings.MerchantInfo') {
                                    if (isset($product['Offers']['Listings'][0]['MerchantInfo'])) {
                                        if (isset($product['Offers']['Listings'][0]['MerchantInfo']['Name'])) {
                                            $product_row_class->value .= ' prop-merchant';
                                            $product_row_text = $doc->createTextNode($product['Offers']['Listings'][0]['MerchantInfo']['Name']);
                                            $product_row->appendChild($product_row_text);
                                        }
                                    }
                                }
                        }
                    }
                }
            }
            $product_row->appendChild($product_row_class);
            $product_div_wrapper->appendChild($product_row);
        }

        $root->appendChild($product_div_wrapper);
    }
}

$doc->appendChild($root);
$html .= $doc->saveHTML();