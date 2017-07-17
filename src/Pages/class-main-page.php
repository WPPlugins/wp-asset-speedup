<?php

namespace GeorgGriesser\WordPress\Velocious\VelociousAssets\Pages;

class Main_Page
{

    public function init()
    {
        add_action('admin_menu', array($this, 'add_page'));
    }

    public function add_page()
    {
        $page_hook = add_menu_page('Velocious Assets', 'Velocious', 'manage_options', 'velocious', array($this, 'render'), '', 65);

        add_action('load-' . $page_hook, array($this, 'add_remaining_callbacks'));
    }

    public function add_remaining_callbacks()
    {
        add_action('admin_head', array($this, 'add_js_variables'));

        add_action('admin_enqueue_scripts', array($this, 'add_styles'));
        add_action('admin_enqueue_scripts', array($this, 'add_scripts'));
        add_action('admin_head', array($this, 'add_angular_load'));
    }

    public function add_styles()
    {
        wp_enqueue_style('velocious-assets', get_velocious_assets_plugin_style_url('styles.css'));
    }

    public function add_js_variables()
    {
        printf(
            '
            <script type="text/javascript">
                var velociousRestURL = %1$s;
                var velociousNonce = %2$s;
            </script>
            ',
            json_encode(home_url(), JSON_UNESCAPED_SLASHES),
            json_encode(wp_create_nonce('wp_rest'))
        );
    }

    public function add_scripts()
    {
        $angular_path = get_velocious_assets_plugin_anuglar_url();

        wp_register_script('velocious_systemjs.config.js', $angular_path . 'wp.systemjs.config.js');
        wp_localize_script('velocious_systemjs.config.js', 'urlpath_mine', $angular_path);

        $scripts = array(
            'velocious_shim.min.js' => 'node_modules/core-js/client/shim.min.js',
            'velocious_zone.js' => 'node_modules/zone.js/dist/zone.js',
            'velocious_reflect.js' => 'node_modules/reflect-metadata/Reflect.js',
            'velocious_system.src.js' => 'node_modules/systemjs/dist/system.src.js',
            'velocious_systemjs.config.js' => 'wp.systemjs.config.js',
        );

        foreach ($scripts as $handle => $source) {
            wp_enqueue_script($handle, $angular_path . $source);
        }

        if (!defined('VELOCIOUS_ASSETS_DEBUG') || false === VELOCIOUS_ASSETS_DEBUG) {
            wp_enqueue_script('velocious_polyfills', $angular_path . 'dist/polyfills.js');
        }
    }

    public function add_angular_load()
    {
        if (defined('VELOCIOUS_ASSETS_DEBUG') && true === VELOCIOUS_ASSETS_DEBUG) {
            $app_entrypoint = get_velocious_assets_plugin_anuglar_url('app/main.js');
        } else {
            $app_entrypoint = get_velocious_assets_plugin_anuglar_url('dist/app.js');
        }

        printf(
            "
            <script>
                System.import('%s').catch(function(err){ console.error(err); });
            </script>
            ", $app_entrypoint
        );
    }

    public function render()
    {
        $template = '
            %1$s
            <my-app>%2$s</my-app>
        ';

        printf($template,
            (defined('VELOCIOUS_ASSETS_DEBUG') && true === VELOCIOUS_ASSETS_DEBUG) ? '<h2 style="color: red;">Developement Mode</h2>' : '',
            'Loading...'
        );
    }
}