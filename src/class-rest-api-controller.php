<?php

namespace GeorgGriesser\WordPress\Velocious\VelociousAssets;

use GeorgGriesser\WordPress\Velocious\VelociousAssets\Repositories\Repository;

use GeorgGriesser\WordPress\Velocious\VelociousAssets\Assets\Script;

class Rest_Api_Controller
{

    public function __construct()
    {

    }

    public function init()
    {
        add_action('rest_api_init', array($this, 'add_routes'));
    }

    public function add_routes()
    {
        register_rest_route('velocious-assets/v1', '/get/(?P<asset_type>[a-z]+)/(?P<asset_property>[a-zA-Z0-9-_]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_assets_rest'),
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            }
        ));

        register_rest_route('velocious-assets/v1', '/update/(?P<asset_type>[a-z]+)/(?P<asset_slug>[a-zA-Z0-9-_]+)/(?P<asset_property>[a-zA-Z0-9-_]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'update_assets_rest'),
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            }
        ));

        register_rest_route('velocious-assets/v1', '/delete', array(
            'methods' => 'GET',
            'callback' => array($this, 'delete_assets_rest'),
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            }
        ));

        register_rest_route('velocious-assets/v1', '/scan', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_pages_to_scan_rest'),
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            }
        ));

        register_rest_route('velocious-assets/v1', '/scan/(?P<page_id>[0-9]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'scan_page_rest'),
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            }
        ));
    }

    public function get_assets_rest($data)
    {
        if ($data['asset_type'] == 'scripts') {
            $cpt_name = Api_Controller::CPT_NAME_SCRIPTS;
            $transient_name = Api_Controller::TRANSIENT_NAME_SCRIPTS;
        } elseif ($data['asset_type'] == 'styles') {
            $cpt_name = Api_Controller::CPT_NAME_STYLES;
            $transient_name = Api_Controller::TRANSIENT_NAME_STYLES;
        } else {
            return new \WP_Error('velocious_wrong_asset_type', sprintf('Asset type (%s) not valid.', $data['asset_type']), array('status' => 404));
        }

        $repository = new Repository($cpt_name, $transient_name);
        $repository->init();
        $repository->load_assets();

        $repository->remove_duplicate_assets();

        return $repository->get_assets($data['asset_property'], true);
    }

    public function update_assets_rest($data)
    {
        if ($data['asset_type'] == 'scripts') {
            $cpt_name = Api_Controller::CPT_NAME_SCRIPTS;
            $transient_name = Api_Controller::TRANSIENT_NAME_SCRIPTS;
        } elseif ($data['asset_type'] == 'styles') {
            $cpt_name = Api_Controller::CPT_NAME_STYLES;
            $transient_name = Api_Controller::TRANSIENT_NAME_STYLES;
        } else {
            return new \WP_Error('velocious_wrong_asset_type', sprintf('Asset type (%s) not valid.', $data['asset_type']), array('status' => 404));
        }

        $repository = new Repository($cpt_name, $transient_name);
        $repository->init();
        $repository->load_assets();

        if ($repository->has_asset($data['asset_slug']) === false) {
            return new \WP_Error('velocious_wrong_asset_slug', sprintf('Asset slug (%s) not found.', $data['asset_slug']), array('status' => 404));
        }

        $asset = $repository->get_asset($data['asset_slug']);

        /**
         * @var Script $asset
         */

        $function = 'set_' . $data['asset_property'];

        if (!method_exists($asset, $function)) {
            return new \WP_Error('velocious_wrong_asset_property', sprintf('Function %s is not defined for this asset type (%s).', $function, $data['asset_type']), array('status' => 404));
        }

        $asset->$function(1);
        $asset->save();

        return $repository->get_asset($data['asset_slug']);
    }

    public function delete_assets_rest()
    {
        $this->delete_repository_assets(Api_Controller::CPT_NAME_SCRIPTS, Api_Controller::TRANSIENT_NAME_SCRIPTS);
        $this->delete_repository_assets(Api_Controller::CPT_NAME_STYLES, Api_Controller::TRANSIENT_NAME_STYLES);

        return '';
    }

    private function delete_repository_assets($repository_cpt_name, $repository_transient_name)
    {
        $repository = new Repository($repository_cpt_name, $repository_transient_name);
        $repository->init();
        $repository->load_assets();

        $asset_list = $repository->get_assets();

        foreach ($asset_list as $asset) {
            $repository->delete_asset($asset->get_slug());
        }
    }

    public function get_pages_to_scan_rest()
    {
        $args = array(
            'public' => true
        );

        $post_types = get_post_types($args);

        $posts = $this->get_recent_posts($post_types);

        $ids = $this->get_recent_posts_ids($posts);

        return $ids;

        // TODO: Add homePage and one Archives
    }


    /**
     * @param String[] $post_types
     *
     * @return \WP_Post[]
     */
    private function get_recent_posts($post_types)
    {
        $posts = array();

        foreach ($post_types as $post_type) {
            $post_type_posts = $this->get_recent_posts_by_post_type($post_type);

            if ($post_type_posts != false) {
                foreach ($post_type_posts as $post) {
                    $posts[] = $post;
                }
            }


        }
        return $posts;
    }

    /**
     * @param String $post_type
     *
     * @return \WP_Post[]|false
     */
    private function get_recent_posts_by_post_type($post_type)
    {
        $args = array(
            'numberposts' => 3,
            'post_status' => 'publish',
            'post_type' => $post_type
        );

        return wp_get_recent_posts($args, OBJECT);
    }

    /**
     * @param \WP_Post[] $posts
     *
     * @return array
     */
    private function get_recent_posts_ids($posts)
    {
        $ids = array();

        foreach ($posts as $post) {
            $ids[] = $post->ID;
        }

        return $ids;
    }


    public function scan_page_rest($data)
    {

        if (false === get_post_status($data['page_id'])) {
            return new \WP_Error('velocious_wrong_id', sprintf('ID (%s) not valid.', $data['page_id']), array('status' => 404));
        }

        $url = get_permalink($data['page_id']);

        file_get_contents($url);

        return 'done';
    }
}