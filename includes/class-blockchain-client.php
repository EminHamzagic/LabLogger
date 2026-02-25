<?php
/**
 * Blockchain Client Class
 * Handles communication with Solana blockchain via Node.js backend
 */

if (!defined('ABSPATH')) {
    exit;
}

class BLL_Blockchain_Client {
    
    private $backend_url;
    private $network;
    
    public function __construct() {
        // Hardcoded backend URL
        $this->backend_url = 'https://solana-logger-backend.onrender.com';
        $this->network = get_option('bll_network', 'devnet');
    }
    
    /**
     * Make HTTP request to backend
     */
    private function make_request($endpoint, $method = 'GET', $data = null) {
        $url = rtrim($this->backend_url, '/') . '/' . ltrim($endpoint, '/');
        
        $headers = array(
            'Content-Type' => 'application/json'
        );
        
        // Add authentication header if SECRET_KEY is defined
        if (defined('BLL_SECRET_KEY')) {
            $headers['X-API-Key'] = BLL_SECRET_KEY;
        }
        
        $args = array(
            'method' => $method,
            'timeout' => 30,
            'headers' => $headers
        );
        
        if ($data !== null && $method === 'POST') {
            $args['body'] = json_encode($data);
        }
        
        $response = wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        if ($status_code !== 200) {
            $error_data = json_decode($body, true);
            $error_message = isset($error_data['error']) ? $error_data['error'] : 'Unknown error';
            return new WP_Error('blockchain_error', $error_message, array('status' => $status_code));
        }
        
        return json_decode($body, true);
    }
    
    /**
     * Log experiment data to blockchain
     */
    public function log_to_blockchain($data) {
        $result = $this->make_request('/log', 'POST', $data);
        
        if (is_wp_error($result)) {
            error_log('Blockchain logging failed: ' . $result->get_error_message());
            return $result;
        }
        
        if (!isset($result['success']) || !$result['success']) {
            return new WP_Error('blockchain_error', 'Blockchain logging failed');
        }
        
        return $result;
    }
    
    /**
     * Verify a transaction on blockchain
     */
    public function verify_transaction($signature) {
        $result = $this->make_request('/verify/' . $signature, 'GET');
        
        if (is_wp_error($result)) {
            error_log('Blockchain verification failed: ' . $result->get_error_message());
            return $result;
        }
        
        if (!isset($result['success']) || !$result['success']) {
            return new WP_Error('blockchain_error', 'Transaction verification failed');
        }
        
        return $result;
    }
    
    /**
     * Get wallet information
     */
    public function get_wallet_info() {
        $result = $this->make_request('/wallet', 'GET');
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        return $result;
    }
    
    /**
     * Check backend health
     */
    public function health_check() {
        $result = $this->make_request('/health', 'GET');
        
        if (is_wp_error($result)) {
            return array(
                'status' => 'error',
                'message' => $result->get_error_message()
            );
        }
        
        return array(
            'status' => 'ok',
            'data' => $result
        );
    }
    
    /**
     * Test connection to backend
     */
    public function test_connection() {
        $health = $this->health_check();
        
        if ($health['status'] !== 'ok') {
            return array(
                'success' => false,
                'message' => 'Cannot connect to blockchain backend: ' . $health['message']
            );
        }
        
        $wallet = $this->get_wallet_info();
        
        if (is_wp_error($wallet)) {
            return array(
                'success' => false,
                'message' => 'Connected to backend but wallet error: ' . $wallet->get_error_message()
            );
        }
        
        return array(
            'success' => true,
            'message' => 'Successfully connected to blockchain backend',
            'wallet' => $wallet['publicKey'] ?? 'unknown',
            'balance' => $wallet['balance'] ?? 0,
            'network' => $wallet['network'] ?? 'unknown'
        );
    }
}