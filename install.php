<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

if (!$CI->db->table_exists(db_prefix() . 'google_maps_extracted_businesses')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "google_maps_extracted_businesses` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(255) NOT NULL,
        `address` text,
        `phone` varchar(100),
        `website` varchar(500),
        `rating` decimal(2,1),
        `total_reviews` int(11) DEFAULT 0,
        `place_id` varchar(255),
        `latitude` decimal(10,8),
        `longitude` decimal(11,8),
        `business_type` varchar(255),
        `search_location` varchar(255),
        `photo_url` varchar(500),
        `extracted_by` int(11) NOT NULL,
        `extracted_at` datetime NOT NULL,
        `is_exported` tinyint(1) DEFAULT 0,
        `is_converted_to_lead` tinyint(1) DEFAULT 0,
        `lead_id` int(11) DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `place_id` (`place_id`),
        KEY `extracted_by` (`extracted_by`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'google_maps_search_history')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "google_maps_search_history` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `keyword` varchar(255) NOT NULL,
        `location` varchar(255) NOT NULL,
        `radius` int(11) DEFAULT 5000,
        `results_count` int(11) DEFAULT 0,
        `staff_id` int(11) NOT NULL,
        `created_at` datetime NOT NULL,
        PRIMARY KEY (`id`),
        KEY `staff_id` (`staff_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

add_option('google_maps_extractor_api_key', '');
add_option('google_maps_extractor_default_radius', '5000');
add_option('google_maps_extractor_default_location', 'Pune, India');
