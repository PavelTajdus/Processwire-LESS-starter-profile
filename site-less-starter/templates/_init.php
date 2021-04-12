<?php

namespace ProcessWire;

// Variables
$title = $page->get('headline|title'); // headline if available, otherwise title
$view->set('title', $title);

$homepage = $pages->get('/');
$view->set('homepage', $homepage);

$nav_items = $homepage->children->prepend($homepage);
$view->set('nav_items', $nav_items);

$meta_description = $sanitizer->truncate($page->get('summary|body'), 300, 'sentence');
$view->set('meta_description', $meta_description);

$page_image = "";
if ($page->images && $page->images->first()) {
    $page_image = $page->images->first()->size(400, 400)->httpUrl;
} elseif ($homepage->images->first()) {
    $page_image = $homepage->images->first()->size(400, 400)->httpUrl;
}
$view->set('page_image', $page_image);

// LESS to sigle CSS file
$less = $modules->get('RockLESS');
$dir = $config->paths->templates;

$options = [
    'parserOptions' => ['compress' => true],
    'monitorFiles' => [
        $dir . "less/variables.less",
        $dir . "less/custom.less",
    ],
]; // optional
$css_file = $config->paths->templates . "public/app.css";
$result = $less->saveCSS($css_file, [
    $dir . "node_modules/uikit/src/less/uikit.less",
    $dir . "less/variables.less",
    $dir . "less/custom.less",
], $options);

$public_path = $config->paths->templates . 'public/';
$public_url = $config->urls->templates . 'public/';
$css_url = template_file_version($public_path, $public_url, 'app.css');
$view->set('css', $css_url);

// JS to single file
// https://github.com/matthiasmullie/minify
use MatthiasMullie\Minify;

$sourcePath = $dir . "node_modules/uikit/dist/js/uikit.js";
$minifier = new Minify\JS($sourcePath);
// we can even add another file, they'll then be
$minifier->add($dir . "node_modules/uikit/dist/js/uikit-icons.js");
$minifier->add($dir . "js/custom.js");
// save minified file to disk
$minifiedPath = $public_path . 'app.min.js';
$minifier->minify($minifiedPath);

$js_url = template_file_version($public_path, $public_url, 'app.min.js');
$view->set('js', $js_url);


/**
 * @param string $path_to_file path without filename
 * @param string $filename name of file to add version
 * @return string $filename
 */

function template_file_version($path_to_file, $url_to_file, $filename)
{
    if (file_exists($path_to_file . $filename)) {
        $filename = $filename . "?v" . date("His", filemtime($path_to_file . $filename)); // Time of last change 23h 59m 59s in format 235959
    } else {
        return false;
    }

    return $url_to_file . $filename;
}
