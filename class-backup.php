<?php
/**
 * Backup management class for DMT
 * Handles Google Drive OAuth and backup rotation
 */

if ( ! defined( 'ABSPATH' ) ) exit;

use Google\Client;
use Google\Service\Drive;

class ZC_DMT_Backup {
    /**
     * Connect to Google Drive via OAuth2
     */
    public function connect_drive() {
        // OAuth client setup
    }

    /**
     * Create backup file and upload to all connected drives
     */
    public function create_backup() {
        $accounts = get_option('zc_dmt_drive_accounts', []);
        $max_backups = intval(get_option('zc_dmt_max_backups', 10));

        foreach ( $accounts as $account ) {
            $service = $this->get_drive_service( $account );
            if ( ! $service ) continue; // Skip if invalid

            // Export data
            $file_content = $this->generate_backup_content();
            $filename = 'dmt-backup-' . date('Ymd-His') . '.json';

            // Upload file
            $file = new Drive\DriveFile();
            $file->setName( $filename );
            $file->setParents( [ $account['folder_id'] ] );

            $created = $service->files->create( $file, [
                'data' => $file_content,
                'mimeType' => 'application/json',
                'uploadType' => 'multipart'
            ]);

            // Rotate backups
            $this->rotate_backups( $service, $account['folder_id'], $max_backups );
        }
    }

    /**
     * Rotate backup files to maintain max count
     */
    private function rotate_backups( $service, $folder_id, $max ) {
        // List files in folder sorted by createdTime asc
        $params = [
            'q' => sprintf("'%s' in parents", $folder_id),
            'orderBy' => 'createdTime',
            'fields' => 'files(id,name,createdTime)',
        ];
        $files = $service->files->listFiles( $params )->getFiles();

        if ( count($files) > $max ) {
            $to_delete = count($files) - $max;
            for ( $i = 0; $i < $to_delete; $i++ ) {
                try { $service->files->delete( $files[$i]->getId() ); }
                catch (Exception $e) { error_log('Backup rotation error: '.$e->getMessage()); }
            }
        }
    }

    /**
     * Generate backup content (JSON)
     */
    private function generate_backup_content() {
        // Gather data sources, indicators, series
        $data = [];
        if ( class_exists('ZC_DMT_Database') ) {
            global $wpdb;
            // Simple select, for brevity
            $data['sources'] = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}zc_dmt_sources", ARRAY_A);
            $data['indicators'] = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}zc_dmt_indicators", ARRAY_A);
        }
        return json_encode($data);
    }

    /**
     * Get authenticated Google Drive service for account
     */
    private function get_drive_service( $account ) {
        // Initialize Google Client with stored tokens
        return null; // Placeholder
    }
}