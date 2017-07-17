<?php

namespace GeorgGriesser\WordPress\Velocious\VelociousAssets;

use GeorgGriesser\WordPress\Velocious\VelociousAssets\Pages\Main_Page;

class Page_Controller
{

    public function init()
    {
        $main_page = new Main_Page();
        $main_page->init();
    }
}