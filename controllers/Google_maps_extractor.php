<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Google_maps_extractor extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('google_maps_extractor/google_maps_extractor_model');
        
        // Add module views path
        $this->load->add_package_path(GOOGLE_MAPS_EXTRACTOR_MODULE_PATH);
        
        if (!has_permission('google_maps_extractor', '', 'view')) {
            access_denied('Google Maps Extractor');
        }
    }

    public function index()
    {
        $data['title'] = _l('google_maps_extractor');
        $data['api_key'] = get_option('google_maps_extractor_api_key');
        $data['default_radius'] = get_option('google_maps_extractor_default_radius');
        $data['default_location'] = get_option('google_maps_extractor_default_location');
        
        $this->load->view('index', $data);
    }

    public function history()
    {
        $data['title'] = _l('search_history', 'Search History');
        $data['recent_searches'] = $this->google_maps_extractor_model->get_recent_searches(get_staff_user_id(), 50);
        $this->load->view('history', $data);
    }

    public function saved()
    {
        $data['title'] = _l('saved_businesses', 'Saved Businesses');
        $data['saved_businesses'] = $this->google_maps_extractor_model->get_saved_businesses(get_staff_user_id());
        $this->load->view('saved', $data);
    }

    public function settings()
    {
        if (!is_admin()) {
            access_denied('Google Maps Extractor Settings');
        }

        if ($this->input->post()) {
            $post_data = $this->input->post();
            
            update_option('google_maps_extractor_api_key', $post_data['google_maps_api_key']);
            update_option('google_maps_extractor_default_radius', $post_data['default_radius']);
            update_option('google_maps_extractor_default_location', $post_data['default_location']);
            
            set_alert('success', _l('settings_updated'));
            redirect(admin_url('google_maps_extractor/settings'));
        }

        $data['title'] = _l('google_maps_extractor_settings');
        $data['api_key'] = get_option('google_maps_extractor_api_key');
        $data['default_radius'] = get_option('google_maps_extractor_default_radius');
        $data['default_location'] = get_option('google_maps_extractor_default_location');
        
        $this->load->view('settings', $data);
    }

    public function save_business()
    {
        if (!has_permission('google_maps_extractor', '', 'create')) {
            ajax_access_denied();
        }

        $data = $this->input->post();
        $data['extracted_by'] = get_staff_user_id();
        $data['extracted_at'] = date('Y-m-d H:i:s');

        $result = $this->google_maps_extractor_model->save_business($data);
        
        if ($result) {
            $business = $this->google_maps_extractor_model->get_business($result);
            $can_convert = true;
            $can_delete = has_permission('google_maps_extractor', '', 'delete');
            
            echo json_encode([
                'success' => true, 
                'message' => 'Lead saved successfully.',
                'business' => $business,
                'can_convert' => $can_convert,
                'can_delete' => $can_delete
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => _l('business_save_failed')]);
        }
    }

    public function save_search()
    {
        $data = [
            'keyword'       => $this->input->post('keyword'),
            'location'      => $this->input->post('location'),
            'radius'        => $this->input->post('radius') ?: 5000,
            'results_count' => $this->input->post('results_count') ?: 0,
            'staff_id'      => get_staff_user_id(),
            'created_at'    => date('Y-m-d H:i:s'),
        ];

        $this->google_maps_extractor_model->save_search($data);
        echo json_encode(['success' => true]);
    }

    public function get_saved_businesses()
    {
        $businesses = $this->google_maps_extractor_model->get_saved_businesses(get_staff_user_id());
        echo json_encode($businesses);
    }

    public function delete_business($id)
    {
        if (!has_permission('google_maps_extractor', '', 'delete')) {
            ajax_access_denied();
        }

        $result = $this->google_maps_extractor_model->delete_business($id, get_staff_user_id());
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => _l('business_deleted_successfully')]);
        } else {
            echo json_encode(['success' => false, 'message' => _l('business_delete_failed')]);
        }
    }

    public function delete_businesses()
    {
        if (!has_permission('google_maps_extractor', '', 'delete')) {
            ajax_access_denied();
        }

        $ids = json_decode($this->input->post('ids'), true);
        
        if (empty($ids)) {
            echo json_encode(['success' => false, 'message' => _l('no_items_selected')]);
            return;
        }

        $result = $this->google_maps_extractor_model->delete_businesses($ids, get_staff_user_id());
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => _l('businesses_deleted_successfully')]);
        } else {
            echo json_encode(['success' => false, 'message' => _l('business_delete_failed')]);
        }
    }

    public function clear_businesses()
    {
        if (!has_permission('google_maps_extractor', '', 'delete')) {
            ajax_access_denied();
        }

        $result = $this->google_maps_extractor_model->clear_all_businesses(get_staff_user_id());
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => _l('all_businesses_cleared')]);
        } else {
            echo json_encode(['success' => false, 'message' => _l('clear_failed')]);
        }
    }

    public function delete_search($id)
    {
        $result = $this->google_maps_extractor_model->delete_search($id, get_staff_user_id());
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => _l('search_deleted_successfully')]);
        } else {
            echo json_encode(['success' => false, 'message' => _l('search_delete_failed')]);
        }
    }

    public function delete_searches()
    {
        $ids = json_decode($this->input->post('ids'), true);
        
        if (empty($ids)) {
            echo json_encode(['success' => false, 'message' => _l('no_items_selected')]);
            return;
        }

        $result = $this->google_maps_extractor_model->delete_searches($ids, get_staff_user_id());
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => _l('searches_deleted_successfully')]);
        } else {
            echo json_encode(['success' => false, 'message' => _l('search_delete_failed')]);
        }
    }

    public function clear_searches()
    {
        $result = $this->google_maps_extractor_model->clear_all_searches(get_staff_user_id());
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => _l('all_searches_cleared')]);
        } else {
            echo json_encode(['success' => false, 'message' => _l('clear_failed')]);
        }
    }

    public function convert_to_lead($id)
    {        $business = $this->google_maps_extractor_model->get_business($id);
        
        if (!$business) {
            echo json_encode(['success' => false, 'message' => _l('business_not_found')]);
            return;
        }

        $this->load->model('leads_model');
        
        $lead_data = [
            'name'        => $business->name,
            'address'     => $business->address,
            'phonenumber' => $business->phone,
            'website'     => $business->website,
            'description' => sprintf(_l('lead_from_google_maps'), $business->business_type, $business->search_location),
            'source'      => get_option('leads_default_source'),
            'status'      => get_option('leads_default_status'),
        ];

        $lead_id = $this->leads_model->add($lead_data);
        
        if ($lead_id) {
            $this->google_maps_extractor_model->mark_as_converted($id, $lead_id);
            echo json_encode(['success' => true, 'lead_id' => $lead_id, 'message' => _l('lead_created_successfully')]);
        } else {
            echo json_encode(['success' => false, 'message' => _l('lead_creation_failed')]);
        }
    }

    public function convert_multiple_to_leads()
    {

        $ids = json_decode($this->input->post('ids'), true);
        
        if (empty($ids)) {
            echo json_encode(['success' => false, 'message' => _l('no_items_selected')]);
            return;
        }

        $this->load->model('leads_model');
        $converted = 0;
        $failed = 0;

        foreach ($ids as $id) {
            $business = $this->google_maps_extractor_model->get_business($id);
            
            if (!$business || $business->is_converted_to_lead) {
                $failed++;
                continue;
            }

            $lead_data = [
                'name'        => $business->name,
                'address'     => $business->address,
                'phonenumber' => $business->phone,
                'website'     => $business->website,
                'description' => sprintf(_l('lead_from_google_maps'), $business->business_type, $business->search_location),
                'source'      => get_option('leads_default_source'),
                'status'      => get_option('leads_default_status'),
            ];

            $lead_id = $this->leads_model->add($lead_data);
            
            if ($lead_id) {
                $this->google_maps_extractor_model->mark_as_converted($id, $lead_id);
                $converted++;
            } else {
                $failed++;
            }
        }

        if ($converted > 0) {
            echo json_encode(['success' => true, 'message' => sprintf(_l('leads_converted_successfully'), $converted)]);
        } else {
            echo json_encode(['success' => false, 'message' => _l('lead_conversion_failed')]);
        }
    }

    public function export_businesses()
    {
        $businesses = $this->google_maps_extractor_model->get_saved_businesses(get_staff_user_id());
        
        header('Content-Type: application/json');
        echo json_encode($businesses);
    }
}
