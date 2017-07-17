<?php

namespace GeorgGriesser\WordPress\Velocious\VelociousAssets\Detectors;

use GeorgGriesser\WordPress\Velocious\VelociousAssets\Api_Controller;
use GeorgGriesser\WordPress\Velocious\VelociousAssets\Assets\Asset;
use GeorgGriesser\WordPress\Velocious\VelociousAssets\Assets\Style;

class Style_Detector extends Asset_Detector
{
    /**
     * Post type used for repository
     *
     * @var String $post_type
     */
    protected $post_type = Api_Controller::CPT_NAME_STYLES;

    /**
     * Transient Name for repository
     *
     * @var String $transient_name
     */
    protected $transient_name = Api_Controller::TRANSIENT_NAME_STYLES;


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
        $dependencies = wp_styles()->query($asset_handle)->deps;

        if (!empty($dependencies)) {

            foreach ($dependencies as $dependency_handle) {
                $this->store_asset($dependency_handle);
            }
        }

        if (!$this->repository->has_asset($asset_handle)) {
            $this->repository->add_asset(new Style($asset_handle));

            $asset = $this->repository->get_asset($asset_handle);

            /**
             * @var Style $asset
             */
            $asset->set_load_header(1);
        }
    }

    /**
     * @return Asset[] array
     */
    protected function detect_asset_handles()
    {
        global $wp_styles;
        return $wp_styles->queue;
    }
}