<?php
/**
 * Plugin Name: Blockchain Lab Logger
 * Plugin URI: https://github.com/yourusername/blockchain-lab-logger
 * Description: Log physics/science experiment data with blockchain verification using Solana. Records are stored in WordPress and verified on Solana blockchain for data integrity.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: blockchain-lab-logger
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('BLL_VERSION', '1.0.0');
define('BLL_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BLL_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include config file
$config_file = BLL_PLUGIN_DIR . 'includes/config.php';
if (file_exists($config_file)) {
    require_once $config_file;
} else {
    // Show admin notice if config file doesn't exist
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p><strong>Blockchain Lab Logger:</strong> Configuration file not found. Please copy <code>includes/config-sample.php</code> to <code>includes/config.php</code> and set your SECRET_KEY.</p></div>';
    });
}

// Include required files
require_once BLL_PLUGIN_DIR . 'includes/class-blockchain-client.php';
require_once BLL_PLUGIN_DIR . 'includes/class-admin-settings.php';
require_once BLL_PLUGIN_DIR . 'includes/class-lab-logger.php';

/**
 * Main plugin class
 */
class Blockchain_Lab_Logger {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_hooks();
    }
    
    private function init_hooks() {
        // Activation/Deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Initialize components
        add_action('plugins_loaded', array($this, 'init'));
        
        // Enqueue scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        
        // Add shortcodes
        add_shortcode('lab_logger', array($this, 'render_shortcode'));
        add_shortcode('lab_experiments_table', array($this, 'render_table_shortcode'));
    }
    
    public function activate() {
        // Create custom database table for experiment logs
        global $wpdb;
        $table_name = $wpdb->prefix . 'lab_experiments';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            experiment_id varchar(100) NOT NULL,
            experiment_name varchar(255) NOT NULL,
            student_name varchar(255) DEFAULT NULL,
            experiment_data longtext NOT NULL,
            data_hash varchar(64) NOT NULL,
            blockchain_signature varchar(255) DEFAULT NULL,
            blockchain_verified tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY experiment_id (experiment_id),
            KEY blockchain_signature (blockchain_signature)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Set default options
        if (!get_option('bll_backend_url')) {
            add_option('bll_backend_url', 'https://solana-logger-backend.onrender.com');
        }
        if (!get_option('bll_network')) {
            add_option('bll_network', 'devnet');
        }
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    public function init() {
        // Initialize admin settings
        if (is_admin()) {
            new BLL_Admin_Settings();
        }
        
        // Load text domain for translations
        load_plugin_textdomain('blockchain-lab-logger', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    public function enqueue_admin_assets($hook) {
        // Only load on our plugin pages
        if (strpos($hook, 'blockchain-lab-logger') === false && $hook !== 'post.php' && $hook !== 'post-new.php') {
            return;
        }
        
        wp_enqueue_style(
            'bll-admin-styles',
            BLL_PLUGIN_URL . 'assets/css/admin-styles.css',
            array(),
            BLL_VERSION
        );
        
        wp_enqueue_script(
            'bll-admin-script',
            BLL_PLUGIN_URL . 'assets/js/admin-script.js',
            array('jquery'),
            BLL_VERSION,
            true
        );
        
        wp_localize_script('bll-admin-script', 'bllAdmin', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('bll_admin_nonce')
        ));
    }
    
    public function enqueue_frontend_assets() {
        wp_enqueue_style(
            'bll-frontend-styles',
            BLL_PLUGIN_URL . 'assets/css/frontend-styles.css',
            array(),
            BLL_VERSION
        );
        
        wp_enqueue_style(
            'bll-table-styles',
            BLL_PLUGIN_URL . 'assets/css/table-styles.css',
            array(),
            BLL_VERSION
        );
        
        wp_enqueue_script(
            'bll-frontend-script',
            BLL_PLUGIN_URL . 'assets/js/frontend-script.js',
            array('jquery'),
            BLL_VERSION,
            true
        );
        
        wp_localize_script('bll-frontend-script', 'bllFrontend', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('bll_frontend_nonce')
        ));
    }
    
    public function render_shortcode($atts) {
        $atts = shortcode_atts(array(
            'title' => 'Lab Experiment Logger',
            'show_history' => 'yes'
        ), $atts);
        
        ob_start();
        include BLL_PLUGIN_DIR . 'templates/lab-logger-form.php';
        return ob_get_clean();
    }
    
    public function render_table_shortcode($atts) {
        $atts = shortcode_atts(array(
            'title' => 'All Lab Experiments',
            'show_stats' => 'yes',
            'show_search' => 'yes',
            'show_pagination' => 'yes'
        ), $atts);
        
        ob_start();
        include BLL_PLUGIN_DIR . 'templates/experiments-table.php';
        return ob_get_clean();
    }
}

// Initialize the plugin
function blockchain_lab_logger() {
    return Blockchain_Lab_Logger::get_instance();
}

// Start the plugin
blockchain_lab_logger();