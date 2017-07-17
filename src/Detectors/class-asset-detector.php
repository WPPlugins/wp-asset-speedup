<?php

namespace GeorgGriesser\WordPress\Velocious\VelociousAssets\Detectors;

use GeorgGriesser\WordPress\Velocious\VelociousAssets\Api_Controller;
use GeorgGriesser\WordPress\Velocious\VelociousAssets\Assets\Asset;
use GeorgGriesser\WordPress\Velocious\VelociousAssets\Repositories\Repository;

abstract class Asset_Detector
{
    /**
     * @var Repository
     */
    protected $repository;

    /**
     * Post type used for repository
     *
     * @var String $post_type
     */
    protected $post_type = Api_Controller::CPT_NAME_ASSETS;

    /**
     * Transient Name for repository
     *
     * @var String $transient_name
     */
    protected $transient_name = Api_Controller::TRANSIENT_NAME_ASSETS;


    public function __construct()
    {
        $this->repository = new Repository($this->post_type, $this->transient_name);

        // fill repository with already stored assets so they are not stored again
        $this->repository->load_assets();
    }

    /**
     * store assets when they are available
     */
    public function init()
    {
        add_action('wp_footer', array($this, 'store_assets'));
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
        if (!$this->repository->has_asset($asset_handle)) {
            $this->repository->add_asset(new Asset($asset_handle));
        }
    }

    /**
     * @return Asset[] array
     */
    abstract protected function detect_asset_handles();
}