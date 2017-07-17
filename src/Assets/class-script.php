<?php

namespace GeorgGriesser\WordPress\Velocious\VelociousAssets\Assets;

use GeorgGriesser\WordPress\Velocious\VelociousAssets\Api_Controller;

class Script extends Asset
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

    private $load_header;
    private $load_footer;
    private $load_inline;
    private $load_defer;
    private $load_async;
    private $load_remove;

    public function __construct($slug, $id = null)
    {
        $this->post_type = Api_Controller::CPT_NAME_SCRIPTS;
        $this->transient_name = Api_Controller::TRANSIENT_NAME_SCRIPTS;

        parent::__construct($slug, $id);
    }

    protected function set_defaults() {
        $this->update_option_value('load_header', 0);
        $this->update_option_value('load_footer', 0);
        $this->update_option_value('load_inline', 0);
        $this->update_option_value('load_defer', 0);
        $this->update_option_value('load_async', 0);
        $this->update_option_value('load_remove', 0);
    }

    public function set_load_header($meta_value) {
        $this->set_defaults();
        $this->update_option_value('load_header', $meta_value);
    }

    public function get_load_header() {
        $this->load_header = $this->get_option_value('load_header');
        return $this->load_header;
    }

    public function set_load_footer($meta_value) {
        $this->set_defaults();
        $this->update_option_value('load_footer', $meta_value);
    }

    public function get_load_footer() {
        $this->load_footer = $this->get_option_value('load_footer');
        return $this->load_footer;
    }

    public function set_load_inline($meta_value) {
        $this->set_defaults();
        $this->update_option_value('load_inline', $meta_value);
    }

    public function get_load_inline() {
        $this->load_inline = $this->get_option_value('load_inline');
        return $this->load_inline;
    }

    public function set_load_defer($meta_value) {
        $this->set_defaults();
        $this->update_option_value('load_defer', $meta_value);
    }

    public function get_load_defer() {
        $this->load_defer = $this->get_option_value('load_defer');
        return $this->load_defer;
    }

    public function set_load_async($meta_value) {
        $this->set_defaults();
        $this->update_option_value('load_async', $meta_value);
    }

    public function get_load_async() {
        $this->load_async = $this->get_option_value('load_async');
        return $this->load_async;
    }

    public function set_load_remove($meta_value) {
        $this->set_defaults();
        $this->update_option_value('load_remove', $meta_value);
    }

    public function get_load_remove() {
        $this->load_remove = $this->get_option_value('load_remove');
        return $this->load_remove;
    }


    public function jsonSerialize()
    {
        $object_as_array = array(
            'id' => $this->get_id(),
            'slug' => $this->get_slug(),
            'post_type' => $this->get_post_type(),
            'load_header' => $this->get_load_header(),
            'load_footer' => $this->get_load_footer(),
            'load_inline' => $this->get_load_inline(),
            'load_defer' => $this->get_load_defer(),
            'load_async' => $this->get_load_async(),
            'load_remove' => $this->get_load_remove()
        );

        return $object_as_array;
    }
}