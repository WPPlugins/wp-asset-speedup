<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

use GeorgGriesser\WordPress\Velocious\VelociousAssets;

require_once "autoloader.php";

$rest_api_controller = new VelociousAssets\Rest_Api_Controller();
$rest_api_controller->delete_assets_rest();