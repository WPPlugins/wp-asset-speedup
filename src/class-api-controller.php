<?php

namespace GeorgGriesser\WordPress\Velocious\VelociousAssets;

use GeorgGriesser\WordPress\Velocious\VelociousAssets\Detectors\Script_Detector;
use GeorgGriesser\WordPress\Velocious\VelociousAssets\Detectors\Style_Detector;
use GeorgGriesser\WordPress\Velocious\VelociousAssets\Loaders\Script_Loader;
use GeorgGriesser\WordPress\Velocious\VelociousAssets\Loaders\Style_Loader;
use GeorgGriesser\WordPress\Velocious\VelociousAssets\Repositories\Repository;

class Api_Controller
{
    const CPT_NAME_ASSETS = 'velocious-assets';
    const CPT_NAME_STYLES = 'velocious-styles';
    const CPT_NAME_SCRIPTS = 'velocious-scripts';
    const TRANSIENT_NAME_ASSETS = 'velocious-assets-repository-transient';
    const TRANSIENT_NAME_STYLES = 'velocious-styles-repository-transient';
    const TRANSIENT_NAME_SCRIPTS = 'velocious-scripts-repository-transient';
    const ASSET_META_PREFIX = 'velocious_assets_meta_';

    public function __construct()
    {

    }

    public function init()
    {
        add_action('init', array($this, 'registerCustomPostTypes'));


        $this->startRepositories();

        add_action('plugins_loaded', array($this, 'startDetectors'));

        $this->startLoaders();
    }

    public function registerCustomPostTypes()
    {
        $this->registerCustomPostType(self::CPT_NAME_ASSETS);
        $this->registerCustomPostType(self::CPT_NAME_STYLES);
        $this->registerCustomPostType(self::CPT_NAME_SCRIPTS);
    }

    private function registerCustomPostType($cpt_name)
    {
        $labels = array(
            'name' => _x($cpt_name, 'Post Type General Name', 'text_domain'),
            'singular_name' => _x($cpt_name, 'Post Type Singular Name', 'text_domain'),
            'menu_name' => __($cpt_name, 'text_domain'),
        );
        $args = array(
            'labels' => $labels,
            'supports' => array('title',),
            'hierarchical' => false,
            'public' => false,
            'show_ui' => false,
            'show_in_menu' => false,
            'menu_position' => 5,
            'show_in_admin_bar' => false,
            'show_in_nav_menus' => false,
            'can_export' => true,
            'has_archive' => false,
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'capability_type' => 'page',
        );
        register_post_type($cpt_name, $args);
    }

    private function startRepositories()
    {
        if (is_admin()) {

            // We need repositories to delete transients on post transitions
            $script_repository = new Repository(Api_Controller::CPT_NAME_SCRIPTS, Api_Controller::TRANSIENT_NAME_SCRIPTS);
            $script_repository->init();

            $script_repository = new Repository(Api_Controller::CPT_NAME_SCRIPTS, Api_Controller::TRANSIENT_NAME_SCRIPTS);
            $script_repository->init();
        }
    }

    public function startDetectors()
    {
        if (!is_admin() && !is_user_logged_in()) {
            $script_detector = new Script_Detector();
            $script_detector->init();

            $style_detector = new Style_Detector();
            $style_detector->init();
        }
    }

    private function startLoaders()
    {
        if (!is_admin()) {
            $script_loader = new Script_Loader();
            $script_loader->init();

            $style_loader = new Style_Loader();
            $style_loader->init();
        }
    }
}