<?php

use Amazon\ProductAdvertisingAPI\v1\ApiException;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\api\DefaultApi;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\GetItemsRequest;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\PartnerType;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchItemsResource;
use Amazon\ProductAdvertisingAPI\v1\Configuration;
use Moment\Moment;

if (!class_exists('LATAT_Ultils')) {
  class LATAT_Ultils {
    public function __construct() {

    }

    public function decodeData($data) {
      return json_decode(rawurldecode(base64_decode($data)), true);
    }

    public function encodeData($data) {
      return base64_encode(rawurlencode(json_encode($data)));
    }

    public function _getCredentials() {
      $this->credentials = get_option(LATAT_PAA_CREDENTIAL_OPTION_NAME);
      # New configuration
      $this->config = new Configuration();

      # Please add your access key here
      $this->config->setAccessKey($this->credentials['access_key']);

      # Please add your secret key here
      $this->config->setSecretKey($this->credentials['secret_key']);

      # Please add your partner tag (store/tracking id) here
      $this->partnerTag = $this->credentials['partner_tag'];
      /*
       * PAAPI host and region to which you want to send request
       * For more details refer: https://webservices.amazon.com/paapi5/documentation/common-request-parameters.html#host-and-region
       */
      $this->config->setHost('webservices.amazon.com');
      $this->config->setRegion('us-east-1');

      $this->apiInstance = new DefaultApi(
        new GuzzleHttp\Client(),
        $this->config
      );
    }

    public function updateItemsPrice($table_data) {
      $this->_getCredentials();

      $itemIds = array_map(function ($p) {
        return $p['ASIN'];
      }, $table_data['products']);

      // no items, return
      if (count($itemIds) == 0) {
        echo 'no update';
        return $table_data;
      }

      // updated today, return
      $now = new Moment();
      if ($table_data['updated_at'] != '' && $now->from($table_data['updated_at'])->getDays() == 0) {
        return $table_data;
      }

      $resources = array(
        SearchItemsResource::ITEM_INFOTITLE,
        SearchItemsResource::ITEM_INFOFEATURES,
        SearchItemsResource::IMAGESPRIMARYLARGE,
        SearchItemsResource::OFFERSLISTINGSPRICE,
        SearchItemsResource::OFFERSLISTINGSMERCHANT_INFO,
      );

      $getItemsRequest = new GetItemsRequest();
      $getItemsRequest->setItemIds($itemIds);
      $getItemsRequest->setPartnerTag($this->partnerTag);
      $getItemsRequest->setPartnerType(PartnerType::ASSOCIATES);
      $getItemsRequest->setResources($resources);

      try {
        $getItemsResponse = $this->apiInstance->getItems($getItemsRequest);
        // if has results
        if ($getItemsResponse->getItemsResult() !== null) {
          function mapPriceToProducts($item, $table_data) {
            // product not exists
            $exists_index = array_search($item . 'ASIN', array_column($table_data['products'], 'ASIN'));
            if ($exists_index == FALSE) {
              return;
            }

            // update price
            if ($item->getOffers() !== null
              and $item->getOffers() !== null
              and $item->getOffers()->getListings() !== null
              and $item->getOffers()->getListings()[0]->getPrice() !== null
              and $item->getOffers()->getListings()[0]->getPrice()->getDisplayAmount() !== null) {
              $table_data['products'][$exists_index]['price'] = $item->getOffers()->getListings()[0]->getPrice()->getDisplayAmount();
            }
          }
          array_map("mapPriceToProducts", $getItemsResponse->getItemsResult()->getItems());
        }
        // update database
        $table_data['updated_at'] = $now->format();
        $update_data = array(
          'ID' => $table_data['ID'],
          'post_content' => sanitize_text_field($this->encodeData($table_data)),
        );
        $update_result = wp_update_post($update_data, true);
      } catch (ApiException $exception) {}

      return $table_data;
    }
  }
}
