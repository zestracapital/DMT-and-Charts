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
            'connected_at' => current_time( 'mysql' ),
            'folder_id' => null
        ];
        update_option( 'zc_dmt_drive_accounts', $accounts );
        wp_send_json_success( 'Google Drive account connected successfully' );
    }
    public function list_drive_accounts() {
        check_ajax_referer( 'zc_dmt_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Insufficient permissions' );
        }
        $accounts = get_option( 'zc_dmt_drive_accounts', [] );
        wp_send_json_success( $accounts );
    }
    public function remove_drive_account() {
        check_ajax_referer( 'zc_dmt_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Insufficient permissions' );
        }
        $email = sanitize_email( $_POST['email'] );
        if ( empty( $email ) ) {
            wp_send_json_error( 'Invalid email' );
        }
        $accounts = get_option( 'zc_dmt_drive_accounts', [] );
        if ( isset( $accounts[$email] ) ) {
            unset( $accounts[$email] );
            update_option( 'zc_dmt_drive_accounts', $accounts );
            wp_send_json_success( 'Account removed successfully' );
        } else {
            wp_send_json_error( 'Account not found' );
        }
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

    public function add_admin_menu() {
        add_menu_page(
            'Data Management Tool',
            'DMT',
            'manage_options',
            'zc-dmt',
            array( $this, 'render_admin_page' ),
            'dashicons-chart-area',
            30
        );
        
        add_submenu_page(
            'zc-dmt',
            'Add Indicators',
            'Add Indicators',
            'manage_options',
            'zc-dmt-add-indicators',
            array( $this, 'render_add_indicators_page' )
        );
        
        add_submenu_page(
            'zc-dmt',
            'Manual Calculations',
            'Manual Calculations',
            'manage_options',
            'zc-dmt-manual-calculations',
            array( $this, 'render_manual_calculations_page' )
        );
    }

    public function render_add_indicators_page() {
        include_once plugin_dir_path( __FILE__ ) . 'admin/add indicators.php';
    }

    public function render_manual_calculations_page() {
        include_once plugin_dir_path( __FILE__ ) . 'admin/manual calculations.php';
    }

    public function safe_activate() {
        try {
    }
}
