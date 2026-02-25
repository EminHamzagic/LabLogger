<?php
/**
 * Lab Logger Class
 * Handles experiment data logging and blockchain integration
 */

if (!defined('ABSPATH')) {
    exit;
}

class BLL_Lab_Logger {
    
    private $blockchain_client;
    
    public function __construct() {
        $this->blockchain_client = new BLL_Blockchain_Client();
        $this->init_hooks();
    }
    
    private function init_hooks() {
        // AJAX handlers for logged-in users
        add_action('wp_ajax_bll_submit_experiment', array($this, 'ajax_submit_experiment'));
        add_action('wp_ajax_bll_verify_experiment', array($this, 'ajax_verify_experiment'));
        add_action('wp_ajax_bll_get_experiments', array($this, 'ajax_get_experiments'));
        
        // AJAX handlers for non-logged-in users (if you want to allow public submissions)
        add_action('wp_ajax_nopriv_bll_submit_experiment', array($this, 'ajax_submit_experiment'));
        add_action('wp_ajax_nopriv_bll_get_experiments', array($this, 'ajax_get_experiments'));
        
        // AJAX handler for experiment details
        add_action('wp_ajax_bll_get_experiment_details', array($this, 'ajax_get_experiment_details'));
        add_action('wp_ajax_nopriv_bll_get_experiment_details', array($this, 'ajax_get_experiment_details'));
    }
    
    /**
     * Calculate hash of experiment data
     */
    private function calculate_hash($data) {
        $data_string = json_encode($data, JSON_UNESCAPED_UNICODE);
        return hash('sha256', $data_string);
    }
    
    /**
     * Save experiment to database
     */
    private function save_experiment($experiment_data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'lab_experiments';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'experiment_id' => $experiment_data['experiment_id'],
                'experiment_name' => $experiment_data['experiment_name'],
                'student_name' => $experiment_data['student_name'],
                'experiment_data' => json_encode($experiment_data['data']),
                'data_hash' => $experiment_data['data_hash'],
                'blockchain_signature' => $experiment_data['blockchain_signature'],
                'blockchain_verified' => $experiment_data['blockchain_verified'],
                'created_at' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s')
        );
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to save experiment to database');
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Get experiment by ID
     */
    public function get_experiment($id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'lab_experiments';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $id
        ), ARRAY_A);
    }
    
    /**
     * Get all experiments
     */
    public function get_all_experiments($limit = 50, $offset = 0) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'lab_experiments';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $limit,
            $offset
        ), ARRAY_A);
    }
    
    /**
     * AJAX: Submit experiment
     */
    public function ajax_submit_experiment() {
        check_ajax_referer('bll_frontend_nonce', 'nonce');
        
        // Get and validate input
        $experiment_name = sanitize_text_field($_POST['experiment_name'] ?? '');
        $student_name = sanitize_text_field($_POST['student_name'] ?? '');
        $measurements = $_POST['measurements'] ?? array();
        $observations = sanitize_textarea_field($_POST['observations'] ?? '');
        
        if (empty($experiment_name)) {
            wp_send_json_error(array('message' => 'Experiment name is required'));
        }
        
        // Generate unique experiment ID
        $experiment_id = 'exp-' . time() . '-' . wp_generate_password(8, false);
        
        // Prepare experiment data
        $experiment_data = array(
            'measurements' => $measurements,
            'observations' => $observations,
            'metadata' => array(
                'timestamp' => current_time('mysql'),
                'user_ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            )
        );
        
        // Calculate hash
        $data_hash = $this->calculate_hash($experiment_data);
        
        // Log to blockchain
        $blockchain_result = $this->blockchain_client->log_to_blockchain(array(
            'experimentId' => $experiment_id,
            'experimentName' => $experiment_name,
            'hash' => $data_hash,
            'timestamp' => current_time('c')
        ));
        
        if (is_wp_error($blockchain_result)) {
            // Save to database even if blockchain fails (with flag)
            $save_data = array(
                'experiment_id' => $experiment_id,
                'experiment_name' => $experiment_name,
                'student_name' => $student_name,
                'data' => $experiment_data,
                'data_hash' => $data_hash,
                'blockchain_signature' => null,
                'blockchain_verified' => 0
            );
            
            $db_id = $this->save_experiment($save_data);
            
            wp_send_json_success(array(
                'message' => 'Experiment saved locally, but blockchain logging failed: ' . $blockchain_result->get_error_message(),
                'experiment_id' => $experiment_id,
                'db_id' => $db_id,
                'blockchain_verified' => false,
                'warning' => true
            ));
        }
        
        // Save to database with blockchain signature
        $save_data = array(
            'experiment_id' => $experiment_id,
            'experiment_name' => $experiment_name,
            'student_name' => $student_name,
            'data' => $experiment_data,
            'data_hash' => $data_hash,
            'blockchain_signature' => $blockchain_result['signature'],
            'blockchain_verified' => 1
        );
        
        $db_id = $this->save_experiment($save_data);
        
        if (is_wp_error($db_id)) {
            wp_send_json_error(array('message' => $db_id->get_error_message()));
        }
        
        wp_send_json_success(array(
            'message' => 'Experiment logged successfully!',
            'experiment_id' => $experiment_id,
            'db_id' => $db_id,
            'blockchain_signature' => $blockchain_result['signature'],
            'explorer_url' => $blockchain_result['explorerUrl'],
            'blockchain_verified' => true
        ));
    }
    
    /**
     * AJAX: Verify experiment
     */
    public function ajax_verify_experiment() {
        check_ajax_referer('bll_frontend_nonce', 'nonce');
        
        $id = intval($_POST['id'] ?? 0);
        
        if (!$id) {
            wp_send_json_error(array('message' => 'Invalid experiment ID'));
        }
        
        $experiment = $this->get_experiment($id);
        
        if (!$experiment) {
            wp_send_json_error(array('message' => 'Experiment not found'));
        }
        
        // Recalculate hash from stored data
        $stored_data = json_decode($experiment['experiment_data'], true);
        $calculated_hash = $this->calculate_hash($stored_data);
        
        // Check if hashes match
        $hash_matches = ($calculated_hash === $experiment['data_hash']);
        
        // Verify on blockchain if signature exists
        $blockchain_verified = false;
        $blockchain_data = null;
        
        if (!empty($experiment['blockchain_signature'])) {
            $blockchain_result = $this->blockchain_client->verify_transaction($experiment['blockchain_signature']);
            
            if (!is_wp_error($blockchain_result)) {
                $blockchain_verified = true;
                $blockchain_data = $blockchain_result;
                
                // Check if blockchain hash matches
                if (isset($blockchain_data['memo']['dataHash'])) {
                    $blockchain_hash_matches = ($blockchain_data['memo']['dataHash'] === $experiment['data_hash']);
                } else {
                    $blockchain_hash_matches = false;
                }
            }
        }
        
        $verification_result = array(
            'hash_matches' => $hash_matches,
            'blockchain_verified' => $blockchain_verified,
            'blockchain_hash_matches' => $blockchain_hash_matches ?? false,
            'calculated_hash' => $calculated_hash,
            'stored_hash' => $experiment['data_hash'],
            'blockchain_signature' => $experiment['blockchain_signature'],
            'blockchain_data' => $blockchain_data,
            'integrity' => ($hash_matches && ($blockchain_verified ? $blockchain_hash_matches : true)) ? 'intact' : 'compromised'
        );
        
        wp_send_json_success($verification_result);
    }
    
    /**
     * AJAX: Get experiments list
     */
    public function ajax_get_experiments() {
        check_ajax_referer('bll_frontend_nonce', 'nonce');
        
        $limit = intval($_POST['limit'] ?? 50);
        $offset = intval($_POST['offset'] ?? 0);
        
        $experiments = $this->get_all_experiments($limit, $offset);
        
        // Format data for frontend
        $formatted = array_map(function($exp) {
            return array(
                'id' => $exp['id'],
                'experiment_id' => $exp['experiment_id'],
                'experiment_name' => $exp['experiment_name'],
                'student_name' => $exp['student_name'],
                'blockchain_verified' => (bool)$exp['blockchain_verified'],
                'blockchain_signature' => $exp['blockchain_signature'],
                'created_at' => $exp['created_at'],
                'data_hash' => $exp['data_hash']
            );
        }, $experiments);
        
        wp_send_json_success($formatted);
    }
    
    /**
     * AJAX: Get single experiment details
     */
    public function ajax_get_experiment_details() {
        check_ajax_referer('bll_frontend_nonce', 'nonce');
        
        $id = intval($_POST['id'] ?? 0);
        
        if (!$id) {
            wp_send_json_error(array('message' => 'Invalid experiment ID'));
        }
        
        $experiment = $this->get_experiment($id);
        
        if (!$experiment) {
            wp_send_json_error(array('message' => 'Experiment not found'));
        }
        
        wp_send_json_success($experiment);
    }
}

// Initialize
new BLL_Lab_Logger();