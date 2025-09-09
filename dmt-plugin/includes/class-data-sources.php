<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ZC_DMT_Data_Sources {
    public function test_connection( $source_id ) {
        return [ 'success' => true, 'message' => 'Test connection - placeholder' ];
    }

    public function sync_data( $source_id ) {
        return [ 'success' => true, 'message' => 'Sync data - placeholder' ];
    }
}
