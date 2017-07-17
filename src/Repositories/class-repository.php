<?php

namespace GeorgGriesser\WordPress\Velocious\VelociousAssets\Repositories;

use GeorgGriesser\WordPress\Velocious\VelociousAssets\Assets\Asset;
use GeorgGriesser\WordPress\Velocious\VelociousAssets\DB_Abstraction;

class Repository
{

    /**
     * @var DB_Abstraction
     */
    protected $db_abstraction;
    /**
     * Array used for storing the assets
     *
     * @var Asset[] $asset_list
     */
    protected $asset_list;

    /**
     * Post type used for repository
     *
     * @var String $post_type
     */
    protected $post_type;

    /**
     * Transient Name for repository
     *
     * @var String $transient_name
     */
    protected $transient_name;


    public function __construct($post_type, $transient_name)
    {
        $this->db_abstraction = new DB_Abstraction();
        $this->asset_list = array();
        $this->post_type = $post_type;
        $this->transient_name = $transient_name;
    }

    public function init()
    {
        add_action('transition_post_status', array($this, 'delete_repository_transient_on_post_transitions'), 10, 3);
    }

    /**
     * Loads all assets from database
     *
     */
    public function load_assets()
    {
        if (false === ($this->asset_list = get_transient($this->transient_name))) {

            $this->asset_list = $this->db_abstraction->get_assets($this->post_type);

            $this->set_repository_transient();
        }
    }

    /**
     * Adds new asset to repository
     *
     * @param Asset $asset
     */
    public function add_asset($asset)
    {
        if (false === $this->has_asset($asset->get_slug())) {
            $asset->save();

            $this->delete_repository_transient();

            array_push($this->asset_list, $asset);
        }
    }

    /**
     * Returns all assets
     *
     * @return array|Asset[]
     */
    public function get_assets($asset_property = '', $hide_items = false)
    {
        $assets = $this->asset_list;

        if ($this->needs_filter($asset_property)) {

            $assets = $this->filter_assets_by_property($assets, $asset_property);
        }

        if ($hide_items) {
            $assets = $this->filter_hidden_assets($assets);
        }

        return (array) $assets;
    }


    /**
     * Check if assets should be filtered by property
     *
     * @param $asset_property
     * @return bool
     */
    private function needs_filter($asset_property) {
        return ($asset_property != '' && $asset_property != 'all');
    }


    /**
     * @param Asset[] $assets
     * @param $asset_property
     * @return Asset[] array
     */
    private function filter_assets_by_property($assets, $asset_property) {
        $assets_tmp = array();

        $asset_property = 'get_' . $asset_property;

        foreach ($assets as $key => $asset) {
            if ($asset->$asset_property() == 1) {
                $assets_tmp[] = $asset;
            }
        }

        return $assets_tmp;
    }

    /**
     * Hide some Assets from users, as those Assets are not to be manipulated
     *
     * @param $assets
     * @return array
     */
    private function filter_hidden_assets($assets)
    {
        $hidden_assets = array(
            $this->get_asset('loadCSS'),
            $this->get_asset('cssRelPreload')
        );

        $assets = array_diff($assets, $hidden_assets);

        /**
         * array_diff keeps the original array keys of the first array. As angular will
         * not work with such an array parsed to json we need to reorder the arrays keys
         */
        $assets = array_values($assets);

        return $assets;
    }

    /**
     * Returns asset from repository depending on slug
     *
     * @param $slug
     * @return bool|Asset
     */
    public function get_asset($slug)
    {
        $key = $this->has_asset($slug);

        if (is_int($key)) {
            return $this->asset_list[$key];
        }

        return false;
    }

    /**
     * Deletes an asset from repository
     *
     * @param String $slug WP-slug used for asset
     */
    public function delete_asset($slug)
    {
        $has_asset = $this->has_asset($slug);

        if (is_int($has_asset)) {
            $this->asset_list[$has_asset]->delete();
            unset($this->asset_list[$has_asset]);
            $this->delete_repository_transient();
        }
    }

    /**
     * Checks if an assets exists in repository
     *
     * @param String $slug WP-slug used for asset
     * @return bool|int|string
     */
    public function has_asset($slug)
    {
        if (!empty($this->asset_list)) {

            foreach ($this->asset_list as $key => $asset) {

                if ($asset->get_slug() == $slug) {
                    return $key;
                }
            }
        }
        return false;
    }

    /**
     * Returns current repositories size
     *
     * @return Integer
     */
    public function get_repository_size()
    {
        return count($this->asset_list);
    }

    /**
     * Depending on Database Settings and Server Configuration it may still
     * be possible that two same Assets are stored.
     * This does not break anything, but users might be irritated if an Asset is
     * listed 2 times, so we remove duplicates on each REST-Call
     *
     */
    public function remove_duplicate_assets()
    {
        $this->asset_list = array_unique($this->asset_list);
    }

    /**
     * Delete Transient when assets are deleted or manipulated from inside wp
     *
     */
    public function delete_repository_transient_on_post_transitions($new_status, $old_status, $post)
    {
        if ($new_status != $old_status) {
            $this->delete_repository_transient();
        }
    }


    /**
     * Sets transient for asset repository
     */
    public function set_repository_transient()
    {
        set_transient($this->transient_name, $this->asset_list, 24 * HOUR_IN_SECONDS);
    }

    /**
     * Deletes transient for asset repository
     */
    public function delete_repository_transient()
    {
        delete_transient($this->transient_name);
    }

}