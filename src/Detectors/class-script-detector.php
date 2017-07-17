<?php

namespace GeorgGriesser\WordPress\Velocious\VelociousAssets\Detectors;

use GeorgGriesser\WordPress\Velocious\VelociousAssets\Api_Controller;
use GeorgGriesser\WordPress\Velocious\VelociousAssets\Assets\Asset;
use GeorgGriesser\WordPress\Velocious\VelociousAssets\Assets\Script;

class Script_Detector extends Asset_Detector
{
    /**
     * Post type used for repository
     *
     * @var String $post_type
     */
    protected $post_type = Api_Controller::CPT_NAME_SCRIPTS;

    /**
     * Transient Name for repository
     *
     * @var String $transient_name
     */
    protected $transient_name = Api_Controller::TRANSIENT_NAME_SCRIPTS;


    public function __construct()
    {
        parent::__construct();
    }

    /**
     * searches for assets and stores them
     */
    public function store_assets()
    {
        $asset_handles = $this->detect_asset_handles();

        foreach ($asset_handles as $asset_handle) {
            $this->store_asset($asset_handle);
        }
    }

    private function store_asset($asset_handle)
    {
        $dependencies = wp_scripts()->query($asset_handle)->deps;

        if (!empty($dependencies)) {

            foreach ($dependencies as $dependency_handle) {
                $this->store_asset($dependency_handle);
            }
        }

        if (!$this->repository->has_asset($asset_handle)) {
            $this->repository->add_asset(new Script($asset_handle));

            $asset = $this->repository->get_asset($asset_handle);

            /**
             * @var Script $asset
             */
            if ($this->is_served_in_footer($asset)) {

                $asset->set_load_footer(1);
            } else {

                $asset->set_load_header(1);
            }
        }
    }

    /**
     * Return all enqueued handles
     *
     * @return String[] array
     */
    protected function detect_asset_handles()
    {
        global $wp_scripts;
        return $wp_scripts->queue;
    }

    /**
     * Checks if script is served in footer
     *
     * @param Asset $asset
     * @return bool
     */
    protected function is_served_in_footer($asset)
    {
        global $wp_scripts;
        $footer_scripts = $wp_scripts->in_footer;

        foreach ($footer_scripts as $footer_script) {
            if ($footer_script == $asset->get_slug()) {
                return true;
            }
        }

        return false;
    }
}