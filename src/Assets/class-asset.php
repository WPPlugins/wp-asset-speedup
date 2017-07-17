<?php

namespace GeorgGriesser\WordPress\Velocious\VelociousAssets\Assets;

use GeorgGriesser\WordPress\Velocious\VelociousAssets\Api_Controller;
use GeorgGriesser\WordPress\Velocious\VelociousAssets\DB_Abstraction;

class Asset implements \JsonSerializable
{

    /**
     * @var DB_Abstraction $db_abstraction
     */
    protected $db_abstraction;

    /**
     * Slug used for enqueuing the script (handle) - stored as post_title
     *
     * @var string $slug
     */
    protected $slug;

    /**
     * Post-ID given by wp
     *
     * @var int $id
     */
    protected $id;

    /**
     * Post Type used by asset
     *
     * @var string $post_type
     */
    protected $post_type = Api_Controller::CPT_NAME_ASSETS;

    /**
     * Name for fitting repositories transient
     *
     * @var string $transient_name
     */
    protected $transient_name = Api_Controller::TRANSIENT_NAME_ASSETS;


    public function __construct($slug, $id = '')
    {
        $this->db_abstraction = new DB_Abstraction();
        $this->slug = $slug;
        $this->id = $id;
    }

    /**
     * Saves Asset as Custom Post
     *
     * @return int|\WP_Error
     */
    public function save()
    {
        if ($this->id) {
            $this->id = $this->db_abstraction->save_asset($this);
        } else {
            $this->id = $this->db_abstraction->save_asset($this);
            $this->set_defaults();
        }
    }

    /**
     * Deletes Asset from Database
     *
     */
    public function delete()
    {
        $this->db_abstraction->delete_asset($this);
    }

    /**
     * Populate post-meta with default values
     */
    protected function set_defaults()
    {

    }

    /**
     * @return string
     */
    public function get_slug()
    {
        return $this->slug;
    }

    /**
     * @return int
     */
    public function get_id()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function get_post_type()
    {
        return $this->post_type;
    }

    /**
     * Returns asset as array usable in wp_insert_post or wp_update_post
     *
     * @return array $asset
     */
    public function asset_as_wp_post_array()
    {
        $asset = array(
            'ID' => $this->id,
            'post_title' => $this->slug,
            'post_type' => $this->post_type
        );

        return $asset;
    }

    /**
     * Updates any given post-meta
     *
     * @param String $meta_key
     * @param int $meta_value
     */
    protected function update_option_value($meta_key, $meta_value)
    {
        $this->db_abstraction->update_asset_option_value($this, $meta_key, $meta_value);

        $this->delete_repository_transient();
    }

    /**
     * Get post-met value
     *
     * @param String $meta_key
     * @return mixed
     */
    protected function get_option_value($meta_key)
    {
        return $this->db_abstraction->get_asset_option_value($this, $meta_key);
    }

    /**
     * Deletes transient for asset repository
     */
    protected function delete_repository_transient()
    {
        delete_transient($this->transient_name);
    }

    public function jsonSerialize()
    {
        $object_as_array = array(
            'id' => $this->get_id(),
            'slug' => $this->get_slug(),
            'post_type' => $this->get_post_type(),
        );

        return $object_as_array;
    }

    public function __toString()
    {
        return $this->get_slug();
    }
}