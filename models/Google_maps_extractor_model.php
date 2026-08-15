<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Google_maps_extractor_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function save_business($data)
    {
        $existing = $this->db->get_where(db_prefix() . 'google_maps_extracted_businesses', [
            'place_id'     => $data['place_id'],
            'extracted_by' => $data['extracted_by']
        ])->row();

        if ($existing) {
            return $existing->id;
        }

        $this->db->insert(db_prefix() . 'google_maps_extracted_businesses', [
            'name'            => $data['name'],
            'address'         => isset($data['address']) ? $data['address'] : '',
            'phone'           => isset($data['phone']) ? $data['phone'] : '',
            'website'         => isset($data['website']) ? $data['website'] : '',
            'rating'          => isset($data['rating']) ? $data['rating'] : null,
            'total_reviews'   => isset($data['total_reviews']) ? $data['total_reviews'] : 0,
            'place_id'        => $data['place_id'],
            'latitude'        => isset($data['latitude']) ? $data['latitude'] : null,
            'longitude'       => isset($data['longitude']) ? $data['longitude'] : null,
            'business_type'   => isset($data['business_type']) ? $data['business_type'] : '',
            'search_location' => isset($data['search_location']) ? $data['search_location'] : '',
            'photo_url'       => isset($data['photo_url']) ? $data['photo_url'] : '',
            'extracted_by'    => $data['extracted_by'],
            'extracted_at'    => $data['extracted_at'],
        ]);

        return $this->db->insert_id();
    }

    public function get_business($id)
    {
        return $this->db->get_where(db_prefix() . 'google_maps_extracted_businesses', ['id' => $id])->row();
    }

    public function get_saved_businesses($staff_id, $limit = null)
    {
        $this->db->where('extracted_by', $staff_id);
        $this->db->order_by('extracted_at', 'DESC');
        
        if ($limit) {
            $this->db->limit($limit);
        }
        
        return $this->db->get(db_prefix() . 'google_maps_extracted_businesses')->result_array();
    }

    public function delete_business($id, $staff_id)
    {
        $this->db->where('id', $id);
        $this->db->where('extracted_by', $staff_id);
        return $this->db->delete(db_prefix() . 'google_maps_extracted_businesses');
    }

    public function delete_businesses($ids, $staff_id)
    {
        $this->db->where_in('id', $ids);
        $this->db->where('extracted_by', $staff_id);
        return $this->db->delete(db_prefix() . 'google_maps_extracted_businesses');
    }

    public function clear_all_businesses($staff_id)
    {
        $this->db->where('extracted_by', $staff_id);
        return $this->db->delete(db_prefix() . 'google_maps_extracted_businesses');
    }

    public function mark_as_converted($id, $lead_id)
    {
        $this->db->where('id', $id);
        return $this->db->update(db_prefix() . 'google_maps_extracted_businesses', [
            'is_converted_to_lead' => 1,
            'lead_id'              => $lead_id,
        ]);
    }

    public function save_search($data)
    {
        $this->db->insert(db_prefix() . 'google_maps_search_history', $data);
        return $this->db->insert_id();
    }

    public function get_recent_searches($staff_id, $limit = 10)
    {
        $this->db->where('staff_id', $staff_id);
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit);
        
        return $this->db->get(db_prefix() . 'google_maps_search_history')->result_array();
    }

    public function delete_search($id, $staff_id)
    {
        $this->db->where('id', $id);
        $this->db->where('staff_id', $staff_id);
        return $this->db->delete(db_prefix() . 'google_maps_search_history');
    }

    public function delete_searches($ids, $staff_id)
    {
        $this->db->where_in('id', $ids);
        $this->db->where('staff_id', $staff_id);
        return $this->db->delete(db_prefix() . 'google_maps_search_history');
    }

    public function clear_all_searches($staff_id)
    {
        $this->db->where('staff_id', $staff_id);
        return $this->db->delete(db_prefix() . 'google_maps_search_history');
    }

    public function get_all_businesses($filters = [])
    {
        if (!empty($filters['business_type'])) {
            $this->db->like('business_type', $filters['business_type']);
        }
        
        if (!empty($filters['location'])) {
            $this->db->like('search_location', $filters['location']);
        }
        
        if (!empty($filters['min_rating'])) {
            $this->db->where('rating >=', $filters['min_rating']);
        }
        
        $this->db->order_by('extracted_at', 'DESC');
        
        return $this->db->get(db_prefix() . 'google_maps_extracted_businesses')->result_array();
    }

    public function mark_as_exported($ids)
    {
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        
        $this->db->where_in('id', $ids);
        return $this->db->update(db_prefix() . 'google_maps_extracted_businesses', ['is_exported' => 1]);
    }
}
