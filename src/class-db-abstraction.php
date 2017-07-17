<?php

namespace GeorgGriesser\WordPress\Velocious\VelociousAssets;

use GeorgGriesser\WordPress\Velocious\VelociousAssets\Assets\Asset;
use GeorgGriesser\WordPress\Velocious\VelociousAssets\Assets\Script;
use GeorgGriesser\WordPress\Velocious\VelociousAssets\Assets\Style;

class DB_Abstraction
{

    /**
     * Returns array of all saved assets
     *
     * @return Asset[]
     */
    public function get_assets($post_type)
    {
        $asset_list = array();

        $args = array(
            'posts_per_page' => -1,
            'post_type' => $post_type,
            'post_status' => 'any',
            'orderby' => 'ID',
            'order'   => 'ASC',
        );

        $wp_query = new \WP_Query($args);

        /**
         * @var \WP_Post[] $wp_posts
         */
        $wp_posts = $wp_query->get_posts();

        foreach ($wp_posts as $wp_post) {

            if ($post_type == Api_Controller::CPT_NAME_SCRIPTS) {
                $asset = new Script($wp_post->post_title, $wp_post->ID);

            } elseif ($post_type == Api_Controller::CPT_NAME_STYLES) {
                $asset = new Style($wp_post->post_title, $wp_post->ID);

            } else {
                $asset = new Asset($wp_post->post_title, $wp_post->ID);

            }

            array_push($asset_list, $asset);
        }

        return $asset_list;
    }


    /**
     * @param Asset $asset
     * @return int|\WP_Error
     */
    public function save_asset($asset)
    {
        if ($asset->get_id() == '') {
            return wp_insert_post($asset->asset_as_wp_post_array(), true);
        } else {
            return wp_update_post($asset->asset_as_wp_post_array(), true);
        }
    }

    /**
     * Delete Asset Post
     *
     * @param Asset $asset
     */
    public function delete_asset($asset)
    {
        wp_delete_post($asset->get_id(), true);
    }


    /**
     * @param Asset $asset
     * @param String $meta_key
     * @return mixed
     */
    public function get_asset_option_value($asset, $meta_key)
    {
        return get_post_meta($asset->get_id(), Api_Controller::ASSET_META_PREFIX . $meta_key)[0];
    }

    /**
     * @param Asset $asset
     * @param String $meta_key
     * @param $meta_value
     */
    public function update_asset_option_value($asset, $meta_key, $meta_value)
    {
        update_post_meta($asset->get_id(), Api_Controller::ASSET_META_PREFIX . $meta_key, $meta_value);
    }
}