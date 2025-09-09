<?php
/**
 * Plugin Name: Zestra Capital - Data Management Tool (DMT)
 * Plugin URI: https://client.zestracapital.com
 * Description: Complete data management system for economic indicators. Handles data sources, CSV imports, API integrations, and provides clean data APIs.
 * Version: 1.0.0
 * Author: Zestra Capital
 * Author URI: https://zestracapital.com
 * Text Domain: zc-dmt
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * Network: false
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define( 'ZC_DMT_VERSION', '1.0.0' );
define( 'ZC_DMT_PLUGIN_FILE', __FILE__ );
define( 'ZC_DMT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ZC_DMT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ZC_DMT_TEXT_DOMAIN', 'zc-dmt' );

/**
 * Main DMT Plugin Class
 * Handles initialization, activation, deactivation
 */
class ZC_Data_Management_Tool {

    /**
     * Single instance of the class
     */
    private static $instance = null;

    /**
     * Get single instance
     */
    public static function instance() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Core hooks
        add_action( 'init', [ $this, 'init' ] );
        add_action( 'plugins_loaded', [ $this, 'load_textdomain' ] );

        // Admin hooks
        add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ] );
        add_action( 'admin_init', [ $this, 'admin_init' ] );

        // REST API
        add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );

        // AJAX handlers
        add_action( 'wp_ajax_zc_dmt_import_csv', [ $this, 'handle_csv_import' ] );
        add_action( 'wp_ajax_zc_dmt_test_connection', [ $this, 'test_data_source' ] );
        add_action( 'wp_ajax_zc_dmt_sync_data', [ $this, 'sync_data_source' ] );

        // Activation/Deactivation
        register_activation_hook( __FILE__, [ $this, 'activate' ] );
        register_deactivation_hook( __FILE__, [ $this, 'deactivate' ] );
    }

    /**
     * Initialize plugin
     */
    public function init() {
        // Load includes
        $this->load_includes();

        // Initialize database if needed
        $this->maybe_create_tables();

        // Fire init hook for other plugins
        do_action( 'zc_dmt_initialized' );
    }

    /**
     * Load plugin includes
     */
    private function load_includes() {
        require_once ZC_DMT_PLUGIN_DIR . 'includes/class-database.php';
        require_once ZC_DMT_PLUGIN_DIR . 'includes/class-data-sources.php';
        require_once ZC_DMT_PLUGIN_DIR . 'includes/class-indicators.php';
        require_once ZC_DMT_PLUGIN_DIR . 'includes/class-csv-importer.php';
        require_once ZC_DMT_PLUGIN_DIR . 'includes/class-fred-api.php';
        require_once ZC_DMT_PLUGIN_DIR . 'includes/class-rest-api.php';
    }

    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain( 
            ZC_DMT_TEXT_DOMAIN, 
            false, 
            dirname( plugin_basename( __FILE__ ) ) . '/languages' 
        );
    }

    /**
     * Create database tables if needed
     */
    private function maybe_create_tables() {
        $current_version = get_option( 'zc_dmt_db_version', '0' );

        if ( version_compare( $current_version, ZC_DMT_VERSION, '<' ) ) {
            ZC_DMT_Database::create_tables();
            update_option( 'zc_dmt_db_version', ZC_DMT_VERSION );
        }
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            __( 'Economic DMT', ZC_DMT_TEXT_DOMAIN ),
            __( 'Economic DMT', ZC_DMT_TEXT_DOMAIN ),
            'manage_options',
            'zc-dmt-dashboard',
            [ $this, 'render_dashboard_page' ],
            'dashicons-database-import',
            25
        );

        // Submenus
        add_submenu_page(
            'zc-dmt-dashboard',
            __( 'Dashboard', ZC_DMT_TEXT_DOMAIN ),
            __( 'Dashboard', ZC_DMT_TEXT_DOMAIN ),
            'manage_options',
            'zc-dmt-dashboard',
            [ $this, 'render_dashboard_page' ]
        );

        add_submenu_page(
            'zc-dmt-dashboard',
            __( 'Data Sources', ZC_DMT_TEXT_DOMAIN ),
            __( 'Data Sources', ZC_DMT_TEXT_DOMAIN ),
            'manage_options',
            'zc-dmt-sources',
            [ $this, 'render_sources_page' ]
        );

        add_submenu_page(
            'zc-dmt-dashboard',
            __( 'Indicators', ZC_DMT_TEXT_DOMAIN ),
            __( 'Indicators', ZC_DMT_TEXT_DOMAIN ),
            'manage_options',
            'zc-dmt-indicators',
            [ $this, 'render_indicators_page' ]
        );

        add_submenu_page(
            'zc-dmt-dashboard',
            __( 'CSV Import', ZC_DMT_TEXT_DOMAIN ),
            __( 'CSV Import', ZC_DMT_TEXT_DOMAIN ),
            'manage_options',
            'zc-dmt-import',
            [ $this, 'render_import_page' ]
        );

        add_submenu_page(
            'zc-dmt-dashboard',
            __( 'Import History', ZC_DMT_TEXT_DOMAIN ),
            __( 'Import History', ZC_DMT_TEXT_DOMAIN ),
            'manage_options',
            'zc-dmt-history',
            [ $this, 'render_history_page' ]
        );

        add_submenu_page(
            'zc-dmt-dashboard',
            __( 'Settings', ZC_DMT_TEXT_DOMAIN ),
            __( 'Settings', ZC_DMT_TEXT_DOMAIN ),
            'manage_options',
            'zc-dmt-settings',
            [ $this, 'render_settings_page' ]
        );
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function admin_scripts( $hook ) {
        // Only load on DMT pages
        if ( strpos( $hook, 'zc-dmt' ) === false ) {
            return;
        }

        // Admin CSS
        wp_enqueue_style( 
            'zc-dmt-admin', 
            ZC_DMT_PLUGIN_URL . 'assets/css/admin.css', 
            [], 
            ZC_DMT_VERSION 
        );

        // Admin JS
        wp_enqueue_script( 
            'zc-dmt-admin', 
            ZC_DMT_PLUGIN_URL . 'assets/js/admin.js', 
            [ 'jquery', 'wp-util' ], 
            ZC_DMT_VERSION, 
            true 
        );

        // Localize script
        wp_localize_script( 'zc-dmt-admin', 'zcDMT', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'rest_url' => rest_url( 'zc-dmt/v1/' ),
            'nonce' => wp_create_nonce( 'zc_dmt_nonce' ),
            'text_domain' => ZC_DMT_TEXT_DOMAIN,
            'strings' => [
                'loading' => __( 'Loading...', ZC_DMT_TEXT_DOMAIN ),
                'error' => __( 'Error occurred', ZC_DMT_TEXT_DOMAIN ),
                'success' => __( 'Success!', ZC_DMT_TEXT_DOMAIN ),
                'confirm_delete' => __( 'Are you sure you want to delete this?', ZC_DMT_TEXT_DOMAIN ),
            ]
        ]);

        // Media uploader (for CSV imports)
        wp_enqueue_media();
    }

    /**
     * Admin init
     */
    public function admin_init() {
        // Register settings
        $this->register_settings();
    }

    /**
     * Register plugin settings
     */
    private function register_settings() {
        register_setting( 'zc_dmt_settings', 'zc_dmt_fred_api_key' );
        register_setting( 'zc_dmt_settings', 'zc_dmt_auto_sync' );
        register_setting( 'zc_dmt_settings', 'zc_dmt_sync_frequency' );
        register_setting( 'zc_dmt_settings', 'zc_dmt_data_retention' );
    }

    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        if ( class_exists( 'ZC_DMT_REST_API' ) ) {
            $rest_api = new ZC_DMT_REST_API();
            $rest_api->register_routes();
        }
    }

    /**
     * Handle CSV import AJAX
     */
    public function handle_csv_import() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'], 'zc_dmt_nonce' ) ) {
            wp_die( 'Security check failed' );
        }

        // Check permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }

        if ( class_exists( 'ZC_DMT_CSV_Importer' ) ) {
            $importer = new ZC_DMT_CSV_Importer();
            $result = $importer->handle_upload();
            wp_send_json( $result );
        }

        wp_send_json_error( 'CSV Importer not available' );
    }

    /**
     * Test data source connection
     */
    public function test_data_source() {
        // Verify nonce and permissions
        if ( ! wp_verify_nonce( $_POST['nonce'], 'zc_dmt_nonce' ) || ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Security check failed' );
        }

        $source_id = intval( $_POST['source_id'] );

        if ( class_exists( 'ZC_DMT_Data_Sources' ) ) {
            $sources = new ZC_DMT_Data_Sources();
            $result = $sources->test_connection( $source_id );
            wp_send_json( $result );
        }

        wp_send_json_error( 'Data Sources handler not available' );
    }

    /**
     * Sync data source
     */
    public function sync_data_source() {
        // Verify nonce and permissions
        if ( ! wp_verify_nonce( $_POST['nonce'], 'zc_dmt_nonce' ) || ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Security check failed' );
        }

        $source_id = intval( $_POST['source_id'] );

        if ( class_exists( 'ZC_DMT_Data_Sources' ) ) {
            $sources = new ZC_DMT_Data_Sources();
            $result = $sources->sync_data( $source_id );
            wp_send_json( $result );
        }

        wp_send_json_error( 'Data Sources handler not available' );
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Create database tables
        if ( class_exists( 'ZC_DMT_Database' ) ) {
            ZC_DMT_Database::create_tables();
        }

        // Set default options
        add_option( 'zc_dmt_version', ZC_DMT_VERSION );
        add_option( 'zc_dmt_activated_time', current_time( 'timestamp' ) );

        // Flush rewrite rules
        flush_rewrite_rules();

        // Fire activation hook
        do_action( 'zc_dmt_activated' );
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clear scheduled events
        wp_clear_scheduled_hook( 'zc_dmt_sync_data' );

        // Flush rewrite rules
        flush_rewrite_rules();

        // Fire deactivation hook
        do_action( 'zc_dmt_deactivated' );
    }

    /**
     * Render admin pages (placeholders for now)
     */
    public function render_dashboard_page() {
        include ZC_DMT_PLUGIN_DIR . 'admin/dashboard.php';
    }

    public function render_sources_page() {
        include ZC_DMT_PLUGIN_DIR . 'admin/sources.php';
    }

    public function render_indicators_page() {
        include ZC_DMT_PLUGIN_DIR . 'admin/indicators.php';
    }

    public function render_import_page() {
        include ZC_DMT_PLUGIN_DIR . 'admin/import.php';
    }

    public function render_history_page() {
        include ZC_DMT_PLUGIN_DIR . 'admin/history.php';
    }

    public function render_settings_page() {
        include ZC_DMT_PLUGIN_DIR . 'admin/settings.php';
    }
}

/**
 * Initialize the plugin
 */
function zc_dmt_init() {
    return ZC_Data_Management_Tool::instance();
}

// Start the plugin
zc_dmt_init();

/**
 * Helper function to get DMT instance
 */
function zc_dmt() {
    return ZC_Data_Management_Tool::instance();
}
