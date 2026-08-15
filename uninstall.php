<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

$CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'google_maps_extracted_businesses`');
$CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'google_maps_search_history`');

delete_option('google_maps_extractor_api_key');
delete_option('google_maps_extractor_default_radius');
delete_option('google_maps_extractor_default_location');
