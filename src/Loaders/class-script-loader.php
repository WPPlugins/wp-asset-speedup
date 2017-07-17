<?php

namespace GeorgGriesser\WordPress\Velocious\VelociousAssets\Loaders;

use GeorgGriesser\WordPress\Velocious\VelociousAssets\Api_Controller;
use GeorgGriesser\WordPress\Velocious\VelociousAssets\Assets\Asset;
use GeorgGriesser\WordPress\Velocious\VelociousAssets\Repositories\Repository;

class Script_Loader extends Asset_Loader
{

    /**
     * Post Type used by asset
     *
     * @var string $post_type
     */
    protected $post_type = Api_Controller::CPT_NAME_SCRIPTS;

    /**
     * Name for fitting repositories transient
     *
     * @var string $transient_name
     */
    protected $transient_name = Api_Controller::TRANSIENT_NAME_SCRIPTS;


    public function init()
    {
        add_action('wp_enqueue_scripts', array($this, 'load_header'), PHP_INT_MAX);
        add_action('wp_enqueue_scripts', array($this, 'load_footer'), PHP_INT_MAX);
        add_action('wp_enqueue_scripts', array($this, 'load_inline'), PHP_INT_MAX - 1); // we need the registered script, which load_remove will deque
        add_filter('script_loader_tag', array($this, 'load_defer'), 10, 3);
        add_filter('script_loader_tag', array($this, 'load_async'), 10, 3);
        add_action('wp_enqueue_scripts', array($this, 'load_remove'), PHP_INT_MAX);
    }


    public function load_header()
    {
        // Include the others, as we want them to be included in Header
        $asset_slugs = array_merge(
            $this->get_asset_slugs('load_header'),
            $this->get_asset_slugs('load_defer'),
            $this->get_asset_slugs('load_async')
        );

        foreach ($asset_slugs as $asset_slug) {
            wp_scripts()->add_data($asset_slug, 'group', 0);
        }
    }

    public function load_footer()
    {
        // Include the others, as we want them to be included in Header
        $asset_slugs = $this->get_asset_slugs('load_footer');

        foreach ($asset_slugs as $asset_slug) {
            wp_scripts()->add_data($asset_slug, 'group', 1);
        }
    }

    public function load_inline()
    {
        $asset_slugs = $this->get_asset_slugs('load_inline');

        foreach ($asset_slugs as $asset_slug) {

            $src = wp_scripts()->query($asset_slug)->src;

            $this->render_inline($src);
        }
    }

    private function render_inline($src)
    {
        echo sprintf('<script type="text/javascript">%1$s</script>', file_get_contents($src)) . "\n";
    }

    public function load_defer($tag, $handle, $src)
    {
        $asset_slugs = $this->get_asset_slugs('load_defer');

        if (in_array($handle, $asset_slugs)) {
            return sprintf('<script type="text/javascript" src="%s" defer="defer"></script>', $src) . "\n";
        }
        return $tag;
    }

    public function load_async($tag, $handle, $src)
    {
        $asset_slugs = $this->get_asset_slugs('load_async');

        if (in_array($handle, $asset_slugs)) {
            return sprintf('<script type="text/javascript" src="%s" async></script>', $src) . "\n";
        }
        return $tag;
    }

    public function load_remove()
    {
        // Include the others, as we want them to be removed too
        $asset_slugs = array_merge(
            $this->get_asset_slugs('load_inline'),
            $this->get_asset_slugs('load_remove')
        );

        foreach ($asset_slugs as $asset_slug) {
            wp_deregister_script($asset_slug);
        }
    }
}