<?php
/**
 * Admin Settings Class
 * Handles plugin settings and admin interface
 */

if (!defined('ABSPATH')) {
    exit;
}

class BLL_Admin_Settings {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_menu_pages'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_ajax_bll_test_connection', array($this, 'ajax_test_connection'));
    }
    
    /**
     * Add admin menu pages
     */
    public function add_menu_pages() {
        add_menu_page(
            'Blockchain Lab Logger',
            'Lab Logger',
            'manage_options',
            'blockchain-lab-logger',
            array($this, 'render_main_page'),
            'dashicons-database',
            30
        );
        
        add_submenu_page(
            'blockchain-lab-logger',
            'All Experiments',
            'All Experiments',
            'manage_options',
            'blockchain-lab-logger',
            array($this, 'render_main_page')
        );
        
        add_submenu_page(
            'blockchain-lab-logger',
            'Settings',
            'Settings',
            'manage_options',
            'blockchain-lab-logger-settings',
            array($this, 'render_settings_page')
        );
        
        add_submenu_page(
            'blockchain-lab-logger',
            'How to Use',
            'How to Use',
            'manage_options',
            'blockchain-lab-logger-help',
            array($this, 'render_help_page')
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('bll_settings', 'bll_network', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'devnet'
        ));
    }
    
    /**
     * Render main experiments page
     */
    public function render_main_page() {
        $logger = new BLL_Lab_Logger();
        $experiments = $logger->get_all_experiments(100);
        
        include BLL_PLUGIN_DIR . 'templates/admin-experiments.php';
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (isset($_POST['bll_settings_submit'])) {
            check_admin_referer('bll_settings_save');
            
            update_option('bll_network', sanitize_text_field($_POST['bll_network']));
            
            echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
        }
        
        $network = get_option('bll_network', 'devnet');
        
        include BLL_PLUGIN_DIR . 'templates/admin-settings.php';
    }
    
    /**
     * Render help page
     */
    public function render_help_page() {
        include BLL_PLUGIN_DIR . 'templates/admin-help.php';
    }
    
    /**
     * AJAX: Test blockchain connection
     */
    public function ajax_test_connection() {
        check_ajax_referer('bll_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
        }
        
        $blockchain_client = new BLL_Blockchain_Client();
        $result = $blockchain_client->test_connection();
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }
}