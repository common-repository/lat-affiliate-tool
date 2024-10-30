<?php

use Amazon\ProductAdvertisingAPI\v1\ApiException;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\api\DefaultApi;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\GetItemsRequest;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\PartnerType;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchItemsRequest;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchItemsResource;
use Amazon\ProductAdvertisingAPI\v1\Configuration;

if (!class_exists('LATAT_Action_Handlers')) {
  class LATAT_Action_Handlers {
    public function __construct() {
      $this->credentials = get_option(LATAT_PAA_CREDENTIAL_OPTION_NAME);
    }

    public function setConfigApi() {
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

      $apiInstance = new DefaultApi(
        new GuzzleHttp\Client(),
        $this->config
      );
      return $apiInstance;
    }

    public function search_products($asin = null) {
      if (!$this->credentials) {
        echo json_encode(array(
          'status' => 'error',
          'message' => 'Invalid API tokens.',
          'products' => array(),
        ));
        die();
      }

      $apiInstance = $this->setConfigApi();

      # Request initialization
      $search_by = isset($_POST['search_by']) ? sanitize_text_field($_POST['search_by']) : 'keywords';

      # Specify keywords
      $keyword = isset($_POST['keywords']) ? sanitize_text_field($_POST['keywords']) : '';

      /*
       * Specify the category in which search request is to be made
       * For more details, refer: https://webservices.amazon.com/paapi5/documentation/use-cases/organization-of-items-on-amazon/search-index.html
       */
      $searchIndex = "All";

      # Specify item count to be returned in search result
      $itemPage = (isset($_POST['item-page']) && $_POST['item-page'] > 0) ? absint($_POST['item-page']) : 1;

      /*
       * Choose resources you want from SearchItemsResource enum
       * For more details, refer: https://webservices.amazon.com/paapi5/documentation/search-items.html#resources-parameter
       */
      $resources = array(
        SearchItemsResource::ITEM_INFOTITLE,
        SearchItemsResource::ITEM_INFOFEATURES,
        SearchItemsResource::IMAGESPRIMARYLARGE,
        SearchItemsResource::OFFERSLISTINGSPRICE,
        SearchItemsResource::OFFERSLISTINGSMERCHANT_INFO,
        SearchItemsResource::OFFERSLISTINGSDELIVERY_INFOIS_PRIME_ELIGIBLE,
      );

      # Forming the request "searchItemsRequest"
      $searchItemsRequest = new SearchItemsRequest();
      $searchItemsRequest->setSearchIndex($searchIndex);
      $searchItemsRequest->setKeywords($keyword);
      $searchItemsRequest->setItemCount(10);
      $searchItemsRequest->setItemPage($itemPage);
      $searchItemsRequest->setPartnerTag($this->partnerTag);
      $searchItemsRequest->setPartnerType(PartnerType::ASSOCIATES);
      $searchItemsRequest->setResources($resources);

      # Forming the request "getItemsRequest"
      $itemIds = explode(',', $keyword);
      $itemIds = array_map(function ($item) {
        return strtoupper(trim($item));
      }, $itemIds);
      $itemIds = array_slice($itemIds, 0, 10);

      $getItemsRequest = new GetItemsRequest();
      $getItemsRequest->setItemIds($itemIds);
      $getItemsRequest->setPartnerTag($this->partnerTag);
      $getItemsRequest->setPartnerType(PartnerType::ASSOCIATES);
      $getItemsRequest->setResources($resources);

      # Sending the request
      $result = array(
        'status' => 'ok',
        'message' => 'API called successfully',
        'count' => 0,
        'products' => array(),
      );
      try {
        switch ($search_by) {
        default:
        case 'keywords':
          $searchItemsResponse = $apiInstance->searchItems($searchItemsRequest);
          // if has results
          if ($searchItemsResponse->getSearchResult() !== null) {
            $result['count'] = $searchItemsResponse->getSearchResult()->getTotalResultCount();
            // map result to products
            $result['products'] = array_map(function ($item) {
              return json_decode($item, true);
            }, $searchItemsResponse->getSearchResult()->getItems());
          }
          // if error
          if ($searchItemsResponse->getErrors() != null) {
            $result['status'] = 'error';
            $result['message'] = $searchItemsResponse->getErrors()[0]->getMessage();
          }
          break;

        case 'asin':
          $getItemsResponse = $apiInstance->getItems($getItemsRequest);
          // if has results
          if ($getItemsResponse->getItemsResult() !== null) {
            // map result to products
            $result['products'] = array_map(function ($item) {
              return json_decode($item, true);
            }, $getItemsResponse->getItemsResult()->getItems());
            // count results
            $result['count'] = count($result['products']);
          }
          // if error
          if ($getItemsResponse->getErrors() != null) {
            $result['status'] = 'error';
            $result['message'] = $getItemsResponse->getErrors()[0]->getMessage();
          }
          # code...
          break;
        }
      } catch (ApiException $exception) {
        echo '<pre>' . print_r($exception) . '</pre>';
        $error_message = '[' . $exception->getResponseObject()->getErrors()[0]->getCode() . "] " . $exception->getResponseObject()->getErrors()[0]->getMessage();
        $result['status'] = 'error';
        $result['message'] = $error_message ? $error_message : 'API request failed, please try again later!';
      } catch (Exception $exception) {
        $result['status'] = 'error';
        $result['message'] = $exception->getMessage();
      }

      if (count($result['products'])) {
        $result['products'] = array_map(function ($product) {
          $parsed_product = array(
            'ASIN' => $product['ASIN'],
            'detail_page_url' => $product['DetailPageURL'],
            'image' => $product['Images']['Primary']['Large']['URL'],
            'title' => $product['ItemInfo']['Title']['DisplayValue'],
            'price' => '$' . $product['Offers']['Listings'][0]['Price']['Amount'],
            'merchant' => $product['Offers']['Listings'][0]['MerchantInfo']['Name'],
            'prime' => $product['Offers']['Listings'][0]['DeliveryInfo']['IsPrimeEligible'],
          );
          return $parsed_product;
        }, $result['products']);
      }

      echo json_encode($result);
      die();
    }

    public function save_table() {
      $post_id = isset($_POST['post_id']) && !empty($_POST['post_id']) ? absint(intval($_POST['post_id'])) : false;

      $table = array(
        'post_type' => 'paa_table',
        'post_title' => isset($_POST['post_title']) ? sanitize_text_field($_POST['post_title']) : 'Untitled',
        'post_content' => sanitize_text_field($_POST['post_content']),
        'post_status' => 'publish',
        'comment_status' => 'closed', // if you prefer
        'ping_status' => 'closed', // if you prefer
      );

      $update_result = null;

      if (!$post_id) {
        $post_id = wp_insert_post($table, true);
        if (is_wp_error($post_id)) {
          $update_result = $post_id;
        }
      } else {
        $table['ID'] = $post_id;
        $update_result = wp_update_post($table, true);
      }

      if (is_wp_error($update_result)) {
        $errors = $update_result->get_error_messages();
        echo json_encode(
          array(
            'success' => false,
            'errors' => $errors,
          )
        );
      } else {
        echo json_encode(array(
          'success' => true,
          'table_id' => $post_id,
        ));
      }
      die();
    }

    public function getProductWithAsin($asin)
    {
      $apiInstance = $this->setConfigApi();

      # Config Item Request With ASIN
      $resources = array(
        SearchItemsResource::ITEM_INFOTITLE,
        SearchItemsResource::ITEM_INFOFEATURES,
        SearchItemsResource::IMAGESPRIMARYLARGE,
        SearchItemsResource::OFFERSLISTINGSPRICE,
        SearchItemsResource::OFFERSLISTINGSMERCHANT_INFO,
        SearchItemsResource::OFFERSLISTINGSDELIVERY_INFOIS_PRIME_ELIGIBLE,
      );

      try {
        $getItemsRequest = new GetItemsRequest();
        $getItemsRequest->setItemIds([$asin]);
        $getItemsRequest->setPartnerTag($this->partnerTag);
        $getItemsRequest->setPartnerType(PartnerType::ASSOCIATES);
        $getItemsRequest->setResources($resources);
        $getItemsResponse = $apiInstance->getItems($getItemsRequest);
        // if has results
        $parsed_product = [];
        if ($getItemsResponse->getItemsResult() !== null) {
          // map result to products
          $result = $getItemsResponse->getItemsResult()->getItems();
          $product = $result[0];
          $parsed_product = array(
            'ASIN' => $product->getASIN(),
            'detail_page_url' => $product->getDetailPageURL(),
            'image' => $product->getImages()->getPrimary()->getLarge()->getURL(),
          );
        }
        // if error
        if ($getItemsResponse->getErrors() != null) {
          echo $getItemsResponse->getErrors()[0]->getMessage();
        }
        return ($parsed_product);
      } catch (\Exception $e) {
        var_dump($e->getMessage());
        echo 'Not ASIN found';
      }
    }
  }

  $latat = new LATAT_Action_Handlers();
  add_action('wp_ajax_search_products', array($latat, 'search_products'));
  add_action('wp_ajax_save_table', [$latat, 'save_table']);
}
