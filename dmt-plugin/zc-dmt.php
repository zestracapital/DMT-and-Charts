<?php
/**
 * Plugin Name: Zestra Capital - Data Management Tool (DMT)
 * Plugin URI: https://client.zestracapital.com
 * Description: Complete data management system for economic indicators. Handles data sources, CSV imports, API integrations, and provides clean data APIs.
 * Version: 1.0.2
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
define( 'ZC_DMT_GOOGLE_CLIENT_ID', 'AIzaSyDREI6BL2PebxRMpZf9g-TEkVVel4F7wy4' );

define( 'ZC_DMT_VERSION', '1.0.2' );
define( 'ZC_DMT_PLUGIN_FILE', __FILE__ );
define( 'ZC_DMT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ZC_DMT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ZC_DMT_TEXT_DOMAIN', 'zc-dmt' );

/**
 * Main DMT Plugin Class
 */
class ZC_Data_Management_Tool {

    private static $instance = null;

    public static function instance() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_hooks();
    }

    private function init_hooks() {
        // Handle Data Sources form submission
        if(isset($_POST['zc_dmt_nonce']) && wp_verify_nonce($_POST['zc_dmt_nonce'],'zc_dmt_save_source')){
            if(current_user_can('manage_options')){
                update_option('zc_dmt_fred_api_key', sanitize_text_field($_POST['zc_dmt_fred_api_key']));
                add_settings_error('zc_dmt','settings_updated',__('FRED API Key saved.','zc-dmt'),'updated');
            }
        }

        add_action( 'init', [ $this, 'init' ] );
        add_action( 'plugins_loaded', [ $this, 'load_textdomain' ] );
        add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ] );
        add_action( 'admin_init', [ $this, 'admin_init' ] );
        add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );

        // AJAX handlers
        add_action( 'wp_ajax_zc_dmt_save_drive_token', [ $this, 'save_drive_token' ] );
        add_action( 'wp_ajax_zc_dmt_manual_backup', [ $this, 'manual_backup' ] );

        // Cron hooks
        add_action( 'zc_dmt_daily_backup', [ $this, 'scheduled_backup' ] );
        add_filter( 'cron_schedules', [ $this, 'add_cron_schedules' ] );

        register_activation_hook( __FILE__, [ $this, 'safe_activate' ] );
        register_deactivation_hook( __FILE__, [ $this, 'deactivate' ] );
    }

    public function init() {
        $this->safe_load_includes();
        $this->maybe_create_tables();
        do_action( 'zc_dmt_initialized' );
    }

    private function safe_load_includes() {
        $includes = [
            'class-database.php',
            'class-data-sources.php',
            'class-indicators.php',
            'class-csv-importer.php',
            'class-fred-api.php',
            'class-rest-api.php',
            'class-backup.php'
        ];

        foreach ( $includes as $file ) {
            $file_path = ZC_DMT_PLUGIN_DIR . $file;
            if ( file_exists( $file_path ) ) {
                require_once $file_path;
            } else {
                error_log( "ZC DMT: Missing include file: " . $file );
            }
        }
    }

    public function load_textdomain() {
        load_plugin_textdomain( 
            ZC_DMT_TEXT_DOMAIN, 
            false, 
            dirname( plugin_basename( __FILE__ ) ) . '/languages' 
        );
    }

    private function maybe_create_tables() {
        try {
            $current_version = get_option( 'zc_dmt_db_version', '0' );

            if ( version_compare( $current_version, ZC_DMT_VERSION, '<' ) && class_exists( 'ZC_DMT_Database' ) ) {
                ZC_DMT_Database::create_tables();
                ZC_DMT_Database::insert_default_sources();
                ZC_DMT_Database::insert_sample_indicators();
                update_option( 'zc_dmt_db_version', ZC_DMT_VERSION );
            }
        } catch ( Exception $e ) {
            error_log( 'ZC DMT Database Error: ' . $e->getMessage() );
        }
    }

    public function add_admin_menu() {
        add_menu_page(
            __( 'Economic DMT', ZC_DMT_TEXT_DOMAIN ),
            __( 'Economic DMT', ZC_DMT_TEXT_DOMAIN ),
            'manage_options',
            'zc-dmt-dashboard',
            [ $this, 'render_dashboard_page' ],
            'dashicons-database-import',
            25
        );

        add_submenu_page( 'zc-dmt-dashboard', __( 'Dashboard', ZC_DMT_TEXT_DOMAIN ), __( 'Dashboard', ZC_DMT_TEXT_DOMAIN ), 'manage_options', 'zc-dmt-dashboard', [ $this, 'render_dashboard_page' ] );
        add_submenu_page( 'zc-dmt-dashboard', __( 'Data Sources', ZC_DMT_TEXT_DOMAIN ), __( 'Data Sources', ZC_DMT_TEXT_DOMAIN ), 'manage_options', 'zc-dmt-sources', [ $this, 'render_sources_page' ] );
        add_submenu_page( 'zc-dmt-dashboard', __( 'Indicators', ZC_DMT_TEXT_DOMAIN ), __( 'Indicators', ZC_DMT_TEXT_DOMAIN ), 'manage_options', 'zc-dmt-indicators', [ $this, 'render_indicators_page' ] );
        add_submenu_page( 'zc-dmt-dashboard', __( 'CSV Import', ZC_DMT_TEXT_DOMAIN ), __( 'CSV Import', ZC_DMT_TEXT_DOMAIN ), 'manage_options', 'zc-dmt-import', [ $this, 'render_import_page' ] );
        add_submenu_page( 'zc-dmt-dashboard', __( 'Import History', ZC_DMT_TEXT_DOMAIN ), __( 'Import History', ZC_DMT_TEXT_DOMAIN ), 'manage_options', 'zc-dmt-history', [ $this, 'render_history_page' ] );
        add_submenu_page( 'zc-dmt-dashboard', __( 'Settings', ZC_DMT_TEXT_DOMAIN ), __( 'Settings', ZC_DMT_TEXT_DOMAIN ), 'manage_options', 'zc-dmt-settings', [ $this, 'render_settings_page' ] );
    }

    public function admin_scripts( $hook ) {
        if ( strpos( $hook, 'zc-dmt' ) === false ) {
            return;
        }

        // Google API Client
        wp_enqueue_script( 'gapi-client', 'https://apis.google.com/js/api.js', [], null, true );

        // Backup Admin JS
        wp_enqueue_script( 'zc-dmt-backup-admin', ZC_DMT_PLUGIN_URL . 'backup-admin.js', [ 'jquery', 'gapi-client' ], ZC_DMT_VERSION, true );

        wp_localize_script( 'zc-dmt-backup-admin', 'zcDmtBackup', [
            'oauthClientId' => defined('ZC_DMT_GOOGLE_CLIENT_ID') ? ZC_DMT_GOOGLE_CLIENT_ID : 'YOUR_GOOGLE_CLIENT_ID',
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'zc_dmt_nonce' )
        ]);

        wp_localize_script( 'jquery', 'zcDMT', [
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
    }

    public function admin_init() {
        $this->register_settings();
        $this->maybe_schedule_backups();
    }

    private function register_settings() {
        register_setting( 'zc_dmt_settings', 'zc_dmt_fred_api_key' );
        register_setting( 'zc_dmt_settings', 'zc_dmt_auto_sync' );
        register_setting( 'zc_dmt_settings', 'zc_dmt_sync_frequency' );
        register_setting( 'zc_dmt_settings', 'zc_dmt_data_retention' );
        register_setting( 'zc_dmt_settings', 'zc_dmt_max_backups' );
        register_setting( 'zc_dmt_settings', 'zc_dmt_backup_schedule' );
    }

    private function maybe_schedule_backups() {
        $schedule = get_option( 'zc_dmt_backup_schedule', 'none' );

        if ( $schedule !== 'none' ) {
            if ( ! wp_next_scheduled( 'zc_dmt_daily_backup' ) ) {
                wp_schedule_event( time(), $schedule, 'zc_dmt_daily_backup' );
            }
        } else {
            wp_clear_scheduled_hook( 'zc_dmt_daily_backup' );
        }
    }

    public function add_cron_schedules( $schedules ) {
        $schedules['weekly'] = [
            'interval' => WEEK_IN_SECONDS,
            'display' => __( 'Weekly', ZC_DMT_TEXT_DOMAIN )
        ];
        return $schedules;
    }

    public function register_rest_routes() {
        if ( class_exists( 'ZC_DMT_REST_API' ) ) {
            $rest_api = new ZC_DMT_REST_API();
            $rest_api->register_routes();
        }
    }

    // AJAX Handlers
    public function save_drive_token() {
        check_ajax_referer( 'zc_dmt_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Insufficient permissions' );
        }

        $token = sanitize_text_field( $_POST['token'] );
        $email = sanitize_email( $_POST['email'] );
        $name = sanitize_text_field( $_POST['name'] );

        if ( empty( $token ) || empty( $email ) ) {
            wp_send_json_error( 'Invalid token or email' );
        }

        $accounts = get_option( 'zc_dmt_drive_accounts', [] );
        $accounts[$email] = [
            'token' => $token,
            'name' => $name,
            'connected_at' => current_time( 'mysql' )
        ];

        update_option( 'zc_dmt_drive_accounts', $accounts );
        wp_send_json_success( 'Google Drive account connected successfully' );
    }

    public function manual_backup() {
        check_ajax_referer( 'zc_dmt_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Insufficient permissions' );
        }

        if ( class_exists( 'ZC_DMT_Backup' ) ) {
            $backup = new ZC_DMT_Backup();
            $result = $backup->create_backup();

            if ( $result ) {
                wp_send_json_success( 'Backup created successfully' );
            } else {
                wp_send_json_error( 'Backup failed' );
            }
        } else {
            wp_send_json_error( 'Backup class not available' );
        }
    }

    public function scheduled_backup() {
        if ( class_exists( 'ZC_DMT_Backup' ) ) {
            $backup = new ZC_DMT_Backup();
            $backup->create_backup();
        }
    }

    public function safe_activate() {
        try {
            add_option( 'zc_dmt_version', ZC_DMT_VERSION );
            add_option( 'zc_dmt_activated_time', current_time( 'timestamp' ) );

            if ( class_exists( 'ZC_DMT_Database' ) ) {
                ZC_DMT_Database::create_tables();
                ZC_DMT_Database::insert_default_sources();
                ZC_DMT_Database::insert_sample_indicators();
            }

            flush_rewrite_rules();
            do_action( 'zc_dmt_activated' );

        } catch ( Exception $e ) {
            error_log( 'ZC DMT Activation Error: ' . $e->getMessage() );
            set_transient( 'zc_dmt_activation_error', $e->getMessage(), 300 );
        }
    }

    public function deactivate() {
        wp_clear_scheduled_hook( 'zc_dmt_daily_backup' );
        flush_rewrite_rules();
        do_action( 'zc_dmt_deactivated' );
    }

    // Admin page renderers
    public function render_dashboard_page() {
        $this->safe_include_admin_page( 'dashboard.php' );
    }

    public function render_sources_page() {
        $this->safe_include_admin_page( 'sources.php' );
    }

    public function render_indicators_page() {
        $this->safe_include_admin_page( 'indicators.php' );
    }

    public function render_import_page() {
        $this->safe_include_admin_page( 'import.php' );
    }

    public function render_history_page() {
        $this->safe_include_admin_page( 'history.php' );
    }

    public function render_settings_page() {
        $this->safe_include_admin_page( 'settings.php' );
    }

    private function safe_include_admin_page( $page ) {
        $file_path = ZC_DMT_PLUGIN_DIR . $page;
        if ( file_exists( $file_path ) ) {
            include $file_path;
        } else {
            echo '<div class="wrap"><h1>Page Not Found</h1><p>Admin template missing: ' . esc_html( $page ) . '</p></div>';
        }
    }
}

// Initialize plugin
function zc_dmt_init() {
    return ZC_Data_Management_Tool::instance();
}
zc_dmt_init();

function zc_dmt() {
    return ZC_Data_Management_Tool::instance();
}

// Show activation error notice if any
add_action( 'admin_notices', function() {
    $error = get_transient( 'zc_dmt_activation_error' );
    if ( $error ) {
        echo '<div class="notice notice-warning is-dismissible"><p><strong>ZC DMT:</strong> ' . esc_html( $error ) . '</p></div>';
        delete_transient( 'zc_dmt_activation_error' );
    }
});
