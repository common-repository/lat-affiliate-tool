<?php
/*
Plugin Name: LAT Affiliate Tool
Plugin URI: https://productupdates.org/
Description: Increase the value of your affiliate page and your earned commissions!
Version: 1.2.3
Author: LAT Team
Author URI: https://tranngocthuy.com/
Text Domain: lat-affiliate-tool
 */
/**
 * The main LAT Affiliate Tool plugin file.
 * @package LAT Affiliate Tool
 * @version 1.0
 * @link https://tranngocthuy.com/ LAT Affiliate Tool Homepage
 */
/*
Copyright (c) 2020 LAT Team
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('ABSPATH') or die('No script kiddies please!');
require 'vendor/autoload.php';
// define('PLUGIN_ASSETS_URL', plugins_url('assets', __FILE__));
define('LATAT_PAA_CREDENTIAL_OPTION_NAME', 'latat_configurations');
require 'register_table_list_post_type.php';
require 'class-latat-admin-pages.php';
require 'class-action-handlers.php';
require 'latat-shortcode.php';

// add_filter('page_template', 'wpa3397_page_template');
// function wpa3397_page_template($page_template) {
//   if (is_singular('paa_table')) {
//     $page_template = dirname(__FILE__) . '/custom-page-template.php';
//   }
//   return $page_template;
// }