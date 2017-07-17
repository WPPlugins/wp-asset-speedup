<?php
/*
Plugin Name: WP Asset SpeedUp
Plugin URI:  https://www.velocious.io/plugins/wp-asset-speedup/
Description: Speed Up your WordPress page the easy way and optimize your site's assets.
Version:     1.0.0
Author:      Georg Griesser
Author URI:  https://www.georggriesser.com
License:     GPL3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
*/

if (!defined('ABSPATH')) {
    exit;
}

use GeorgGriesser\WordPress\Velocious\VelociousAssets;

require_once "autoloader.php";

$plugin = new VelociousAssets\Api_Controller();
$plugin->init();

$pages = new VelociousAssets\Page_Controller();
$pages->init();

$rest_api = new VelociousAssets\Rest_Api_Controller();
$rest_api->init();

function get_velocious_assets_plugin_url()
{
    return plugins_url('', __FILE__);
}

function get_velocious_assets_plugin_anuglar_url($path = '')
{
    return plugins_url('angular/' . $path, __FILE__);
}

function get_velocious_assets_plugin_style_url($path = '') {
    return plugins_url('css/' . $path, __FILE__);
}

function get_velocious_assets_plugin_script_url($path = '') {
    return plugins_url('js/' . $path, __FILE__);
}

/*
 * Loads Constants defined in debug_constants.php
 *
 * (This file is removed from production)
 */
if (file_exists(dirname(__FILE__) . '/debug_constants.php')) {
    require_once('debug_constants.php');
}

