<?php
/**
 * Database management class for DMT
 * Handles table creation, updates, and database operations
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ZC_DMT_Database {

    /**
     * Database version
     */
    const DB_VERSION = '1.0.0';

    /**
     * Create all required database tables
     */
    public static function create_tables() {
        global $wpdb;

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        $charset_collate = $wpdb->get_charset_collate();

        // Create data sources table
        self::create_sources_table( $charset_collate );

        // Create indicators table
        self::create_indicators_table( $charset_collate );

        // Create series data table
        self::create_series_table( $charset_collate );

        // Create import log table
        self::create_imports_table( $charset_collate );

        // Create metadata table
        self::create_metadata_table( $charset_collate );

        // Update database version
        update_option( 'zc_dmt_db_version', self::DB_VERSION );
    }

    /**
     * Create data sources table
     */
    private static function create_sources_table( $charset_collate ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'zc_dmt_sources';

        $sql = "CREATE TABLE $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            source_key varchar(64) NOT NULL,
            source_type enum('fred_api','csv_upload','manual_entry','external_api','google_sheets','excel_import') NOT NULL,
            name varchar(191) NOT NULL,
            description text DEFAULT NULL,
            credentials longtext DEFAULT NULL,
            config longtext DEFAULT NULL,
            last_sync datetime DEFAULT NULL,
            auto_sync tinyint(1) DEFAULT 0,
            sync_frequency varchar(32) DEFAULT 'daily',
            status enum('active','inactive','error','syncing') DEFAULT 'active',
            error_message text DEFAULT NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY source_key (source_key),
            KEY source_type (source_type),
            KEY status (status),
            KEY auto_sync (auto_sync)
        ) $charset_collate;";

        dbDelta( $sql );
    }

    /**
     * Create indicators table
     */
    private static function create_indicators_table( $charset_collate ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'zc_dmt_indicators';

        $sql = "CREATE TABLE $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            slug varchar(128) NOT NULL,
            display_name varchar(191) NOT NULL,
            category varchar(128) DEFAULT NULL,
            subcategory varchar(128) DEFAULT NULL,
            description text DEFAULT NULL,
            frequency enum('daily','weekly','monthly','quarterly','yearly','irregular') DEFAULT NULL,
            units varchar(64) DEFAULT NULL,
            source_id bigint(20) UNSIGNED NOT NULL,
            external_code varchar(191) DEFAULT NULL,
            metadata longtext DEFAULT NULL,
            is_active tinyint(1) DEFAULT 1,
            last_updated datetime DEFAULT NULL,
            data_points_count int(11) DEFAULT 0,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug),
            KEY source_id (source_id),
            KEY category (category),
            KEY is_active (is_active),
            KEY frequency (frequency),
            FOREIGN KEY (source_id) REFERENCES {$wpdb->prefix}zc_dmt_sources(id) ON DELETE CASCADE
        ) $charset_collate;";

        dbDelta( $sql );
    }

    /**
     * Create time series data table
     */
    private static function create_series_table( $charset_collate ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'zc_dmt_series';

        $sql = "CREATE TABLE $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            indicator_slug varchar(128) NOT NULL,
            obs_date date NOT NULL,
            value decimal(20,6) DEFAULT NULL,
            value_text varchar(255) DEFAULT NULL,
            source_id bigint(20) UNSIGNED NOT NULL,
            import_batch varchar(64) DEFAULT NULL,
            revision_date datetime DEFAULT NULL,
            notes text DEFAULT NULL,
            last_updated_at datetime NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY unique_observation (indicator_slug, obs_date),
            KEY indicator_slug (indicator_slug),
            KEY obs_date (obs_date),
            KEY source_id (source_id),
            KEY import_batch (import_batch),
            KEY value_index (value),
            FOREIGN KEY (source_id) REFERENCES {$wpdb->prefix}zc_dmt_sources(id) ON DELETE CASCADE
        ) $charset_collate;";

        dbDelta( $sql );
    }

    /**
     * Create import log table
     */
    private static function create_imports_table( $charset_collate ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'zc_dmt_imports';

        $sql = "CREATE TABLE $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            source_id bigint(20) UNSIGNED NOT NULL,
            import_type varchar(64) NOT NULL,
            file_name varchar(191) DEFAULT NULL,
            file_size bigint(20) DEFAULT NULL,
            records_total int(11) DEFAULT 0,
            records_imported int(11) DEFAULT 0,
            records_updated int(11) DEFAULT 0,
            records_failed int(11) DEFAULT 0,
            status enum('pending','processing','completed','failed','cancelled') DEFAULT 'pending',
            progress_percent decimal(5,2) DEFAULT 0.00,
            log_data longtext DEFAULT NULL,
            error_details longtext DEFAULT NULL,
            started_at datetime DEFAULT NULL,
            completed_at datetime DEFAULT NULL,
            created_by bigint(20) UNSIGNED DEFAULT NULL,
            PRIMARY KEY (id),
            KEY source_id (source_id),
            KEY status (status),
            KEY import_type (import_type),
            KEY created_by (created_by),
            KEY started_at (started_at),
            FOREIGN KEY (source_id) REFERENCES {$wpdb->prefix}zc_dmt_sources(id) ON DELETE CASCADE
        ) $charset_collate;";

        dbDelta( $sql );
    }

    /**
     * Create metadata table for flexible key-value storage
     */
    private static function create_metadata_table( $charset_collate ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'zc_dmt_metadata';

        $sql = "CREATE TABLE $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            object_type varchar(32) NOT NULL,
            object_id bigint(20) UNSIGNED NOT NULL,
            meta_key varchar(191) NOT NULL,
            meta_value longtext DEFAULT NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY object_type_id (object_type, object_id),
            KEY meta_key (meta_key)
        ) $charset_collate;";

        dbDelta( $sql );
    }

    /**
     * Insert default data sources
     */
    public static function insert_default_sources() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'zc_dmt_sources';
        $now = current_time( 'mysql' );

        $default_sources = [
            [
                'source_key' => 'fred_default',
                'source_type' => 'fred_api',
                'name' => 'Federal Reserve Economic Data (FRED)',
                'description' => 'Primary source for US economic indicators from the St. Louis Federal Reserve',
                'status' => 'inactive',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'source_key' => 'manual_entry',
                'source_type' => 'manual_entry', 
                'name' => 'Manual Data Entry',
                'description' => 'Manually entered economic data points',
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'source_key' => 'csv_uploads',
                'source_type' => 'csv_upload',
                'name' => 'CSV File Imports',
                'description' => 'Data imported from CSV files',
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now
            ]
        ];

        foreach ( $default_sources as $source ) {
            // Check if source already exists
            $exists = $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE source_key = %s",
                $source['source_key']
            ));

            if ( ! $exists ) {
                $wpdb->insert( $table_name, $source );
            }
        }
    }

    /**
     * Insert sample indicators
     */
    public static function insert_sample_indicators() {
        global $wpdb;

        $sources_table = $wpdb->prefix . 'zc_dmt_sources';
        $indicators_table = $wpdb->prefix . 'zc_dmt_indicators';
        $series_table = $wpdb->prefix . 'zc_dmt_series';

        $now = current_time( 'mysql' );

        // Get manual entry source ID
        $manual_source_id = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM $sources_table WHERE source_key = %s",
            'manual_entry'
        ));

        if ( ! $manual_source_id ) {
            return; // No manual source available
        }

        $sample_indicators = [
            [
                'slug' => 'gdp_us_sample',
                'display_name' => 'US GDP (Sample Data)',
                'category' => 'GDP',
                'subcategory' => 'National Accounts',
                'description' => 'Sample US Gross Domestic Product data for testing',
                'frequency' => 'quarterly',
                'units' => 'Billions of Dollars',
                'source_id' => $manual_source_id,
                'external_code' => 'GDP_SAMPLE',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'slug' => 'unemployment_us_sample',
                'display_name' => 'US Unemployment Rate (Sample)',
                'category' => 'Employment',
                'subcategory' => 'Labor Market',
                'description' => 'Sample US unemployment rate for testing',
                'frequency' => 'monthly',
                'units' => 'Percent',
                'source_id' => $manual_source_id,
                'external_code' => 'UNRATE_SAMPLE',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'slug' => 'cpi_us_sample',
                'display_name' => 'US Consumer Price Index (Sample)',
                'category' => 'Inflation',
                'subcategory' => 'Price Indices',
                'description' => 'Sample US CPI data for testing',
                'frequency' => 'monthly',
                'units' => 'Index 1982-84=100',
                'source_id' => $manual_source_id,
                'external_code' => 'CPIAUCSL_SAMPLE',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ]
        ];

        foreach ( $sample_indicators as $indicator ) {
            // Check if indicator already exists
            $exists = $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM $indicators_table WHERE slug = %s",
                $indicator['slug']
            ));

            if ( ! $exists ) {
                $wpdb->insert( $indicators_table, $indicator );

                // Insert sample time series data
                self::insert_sample_series_data( $indicator['slug'], $manual_source_id );
            }
        }
    }

    /**
     * Insert sample time series data for an indicator
     */
    private static function insert_sample_series_data( $indicator_slug, $source_id ) {
        global $wpdb;

        $series_table = $wpdb->prefix . 'zc_dmt_series';
        $now = current_time( 'mysql' );

        // Generate 24 months of sample data
        for ( $i = 23; $i >= 0; $i-- ) {
            $date = date( 'Y-m-d', strtotime( "-$i months" ) );

            // Generate realistic sample values based on indicator type
            if ( strpos( $indicator_slug, 'gdp' ) !== false ) {
                $base_value = 20000 + ( $i * 50 ) + rand( -100, 100 );
            } elseif ( strpos( $indicator_slug, 'unemployment' ) !== false ) {
                $base_value = 4.5 + ( $i * 0.1 ) + ( rand( -10, 10 ) / 10 );
            } elseif ( strpos( $indicator_slug, 'cpi' ) !== false ) {
                $base_value = 280 + ( $i * 0.5 ) + ( rand( -5, 5 ) / 10 );
            } else {
                $base_value = 100 + ( $i * 0.2 ) + rand( -5, 5 );
            }

            $wpdb->insert( $series_table, [
                'indicator_slug' => $indicator_slug,
                'obs_date' => $date,
                'value' => $base_value,
                'source_id' => $source_id,
                'import_batch' => 'sample_data_' . date( 'Y_m_d' ),
                'last_updated_at' => $now
            ]);
        }
    }

    /**
     * Drop all DMT tables (for uninstall)
     */
    public static function drop_tables() {
        global $wpdb;

        $tables = [
            $wpdb->prefix . 'zc_dmt_metadata',
            $wpdb->prefix . 'zc_dmt_imports',
            $wpdb->prefix . 'zc_dmt_series',
            $wpdb->prefix . 'zc_dmt_indicators',
            $wpdb->prefix . 'zc_dmt_sources'
        ];

        foreach ( $tables as $table ) {
            $wpdb->query( "DROP TABLE IF EXISTS $table" );
        }

        // Delete options
        delete_option( 'zc_dmt_db_version' );
        delete_option( 'zc_dmt_version' );
    }

    /**
     * Get table statistics
     */
    public static function get_stats() {
        global $wpdb;

        return [
            'sources' => $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}zc_dmt_sources" ),
            'active_sources' => $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}zc_dmt_sources WHERE status = 'active'" ),
            'indicators' => $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}zc_dmt_indicators" ),
            'active_indicators' => $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}zc_dmt_indicators WHERE is_active = 1" ),
            'data_points' => $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}zc_dmt_series" ),
            'imports' => $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}zc_dmt_imports" ),
            'successful_imports' => $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}zc_dmt_imports WHERE status = 'completed'" )
        ];
    }
}
