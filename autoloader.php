<?php

if (!defined('ABSPATH')) {
    exit;
}

spl_autoload_register(function ($class) {

    // project-specific namespace prefix
    $prefix = 'GeorgGriesser\\WordPress\\Velocious\\VelociousAssets\\';

    // base directory for the namespace prefix
    $base_dir = __DIR__ . '/src/';

    // does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }

    // get the relative class name
    $relative_class = substr($class, $len);

    // split relative_class as we only need to manipulate the latest element
    $relative_class_parts = explode('\\', $relative_class);
    $relative_class_parts_size = count($relative_class_parts);

    // manipulate the latest element
    $relative_class_parts[$relative_class_parts_size - 1] = strtolower($relative_class_parts[$relative_class_parts_size - 1]);
    $relative_class_parts[$relative_class_parts_size - 1] = str_replace('_', '-', $relative_class_parts[$relative_class_parts_size - 1]);
    $relative_class_parts[$relative_class_parts_size - 1] = 'class-' . $relative_class_parts[$relative_class_parts_size - 1];

    // glue relative_class together again
    $relative_class = implode('\\', $relative_class_parts);

    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // if the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});