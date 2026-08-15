<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: GMap Ex
Description: Extract business information from Google Maps including name, address, phone, website, and ratings. Search by business type and location, view results on an interactive map, and export data to Excel.
Version: 1.0.1
Requires at least: 2.3.*
Author: PerfexCRM Developer
Author URI: https://perfexcrm.com
*/

define('GOOGLE_MAPS_EXTRACTOR_MODULE_NAME', 'google_maps_extractor');
define('GOOGLE_MAPS_EXTRACTOR_MODULE_PATH', __DIR__);

register_language_files(GOOGLE_MAPS_EXTRACTOR_MODULE_NAME, [GOOGLE_MAPS_EXTRACTOR_MODULE_NAME]);

hooks()->add_action('admin_init', 'google_maps_extractor_module_init_menu_items');
hooks()->add_action('app_admin_head', 'google_maps_extractor_add_head_components');
hooks()->add_action('app_admin_footer', 'google_maps_extractor_load_js');

function google_maps_extractor_module_init_menu_items()
{
    $CI = &get_instance();

    if (has_permission('google_maps_extractor', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('google-maps-extractor', [
            'slug'     => 'google-maps-extractor',
            'name'     => _l('google_maps_extractor'),
            'icon'     => 'fa fa-map-marker',
            'href'     => '#',
            'position' => 35,
        ]);

        $CI->app_menu->add_sidebar_children_item('google-maps-extractor', [
            'slug'     => 'google-maps-extractor-main',
            'name'     => 'Search',
            'icon'     => 'fa fa-search',
            'href'     => admin_url('google_maps_extractor'),
            'position' => 1,
        ]);

        $CI->app_menu->add_sidebar_children_item('google-maps-extractor', [
            'slug'     => 'google-maps-extractor-history',
            'name'     => 'History',
            'icon'     => 'fa fa-history',
            'href'     => admin_url('google_maps_extractor/history'),
            'position' => 2,
        ]);

        $CI->app_menu->add_sidebar_children_item('google-maps-extractor', [
            'slug'     => 'google-maps-extractor-saved',
            'name'     => 'Saved',
            'icon'     => 'fa fa-list',
            'href'     => admin_url('google_maps_extractor/saved'),
            'position' => 3,
        ]);

        if (is_admin()) {
            $CI->app_menu->add_sidebar_children_item('google-maps-extractor', [
                'slug'     => 'google-maps-extractor-settings',
                'name'     => _l('settings'),
                'icon'     => 'fa fa-cog',
                'href'     => admin_url('google_maps_extractor/settings'),
                'position' => 4,
            ]);
        }
    }
}

function google_maps_extractor_add_head_components()
{
    $CI = &get_instance();
    
    if ($CI->uri->segment(2) == 'google_maps_extractor') {
        echo '<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600&display=swap" rel="stylesheet">';
        echo '<link href="' . module_dir_url(GOOGLE_MAPS_EXTRACTOR_MODULE_NAME, 'assets/css/style.css') . '?v=' . time() . '" rel="stylesheet" type="text/css" />';
    }
}

function google_maps_extractor_load_js()
{
    $CI = &get_instance();
    
    if ($CI->uri->segment(2) == 'google_maps_extractor') {
        $google_maps_api_key = get_option('google_maps_extractor_api_key');
        echo '<script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>';
        echo '<script src="' . module_dir_url(GOOGLE_MAPS_EXTRACTOR_MODULE_NAME, 'assets/js/extractor.js') . '?v=' . time() . '"></script>';
        if (!empty($google_maps_api_key)) {
            echo '<script async defer src="https://maps.googleapis.com/maps/api/js?key=' . htmlspecialchars($google_maps_api_key) . '&libraries=places&callback=initMap"></script>';
        }
    }
}

function google_maps_extractor_permissions()
{
    $capabilities = [];
    $capabilities['capabilities'] = [
        'view'   => _l('permission_view'),
        'create' => _l('permission_create'),
        'edit'   => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];
    register_staff_capabilities('google_maps_extractor', $capabilities, _l('google_maps_extractor'));
}

register_activation_hook(GOOGLE_MAPS_EXTRACTOR_MODULE_NAME, 'google_maps_extractor_activation_hook');

function google_maps_extractor_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

register_deactivation_hook(GOOGLE_MAPS_EXTRACTOR_MODULE_NAME, 'google_maps_extractor_deactivation_hook');

function google_maps_extractor_deactivation_hook()
{
}

register_uninstall_hook(GOOGLE_MAPS_EXTRACTOR_MODULE_NAME, 'google_maps_extractor_uninstall_hook');

function google_maps_extractor_uninstall_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/uninstall.php');
}

hooks()->add_action('admin_init', 'google_maps_extractor_permissions');
