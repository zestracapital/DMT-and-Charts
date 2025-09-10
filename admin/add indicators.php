<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Handle form submission
$message = '';
$message_type = '';

if ( isset( $_POST['submit_indicator'] ) && wp_verify_nonce( $_POST['add_indicator_nonce'], 'add_indicator' ) ) {
    $indicator_name = sanitize_text_field( $_POST['indicator_name'] );
    $indicator_slug = sanitize_text_field( $_POST['indicator_slug'] );
    $indicator_source = sanitize_text_field( $_POST['indicator_source'] );
    $indicator_description = sanitize_textarea_field( $_POST['indicator_description'] );
    $indicator_status = sanitize_text_field( $_POST['indicator_status'] );
    
    if ( !empty( $indicator_name ) && !empty( $indicator_source ) ) {
        // TODO: Add database insertion logic here
        $message = 'Indicator added successfully!';
        $message_type = 'success';
    } else {
        $message = 'Please fill in all required fields.';
        $message_type = 'error';
    }
}
?>

<div class="wrap">
    <h1><?php esc_html_e( 'Add New Indicator', 'zc-dmt' ); ?></h1>
    
    <?php if ( !empty( $message ) ) : ?>
        <div class="notice notice-<?php echo esc_attr( $message_type ); ?> is-dismissible">
            <p><?php echo esc_html( $message ); ?></p>
        </div>
    <?php endif; ?>
    
    <div style="background: white; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; margin: 20px 0;">
        <h2><?php esc_html_e( 'Indicator Information', 'zc-dmt' ); ?></h2>
        
        <form method="post" action="">
            <?php wp_nonce_field( 'add_indicator', 'add_indicator_nonce' ); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="indicator-name"><?php esc_html_e( 'Indicator Name', 'zc-dmt' ); ?> <span class="description">(required)</span></label>
                    </th>
                    <td>
                        <input type="text" id="indicator-name" name="indicator_name" class="regular-text" required />
                        <p class="description"><?php esc_html_e( 'Enter the display name for this indicator.', 'zc-dmt' ); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="indicator-slug"><?php esc_html_e( 'Slug', 'zc-dmt' ); ?></label>
                    </th>
                    <td>
                        <input type="text" id="indicator-slug" name="indicator_slug" class="regular-text" readonly />
                        <p class="description"><?php esc_html_e( 'Unique identifier for this indicator (auto-generated from name).', 'zc-dmt' ); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="indicator-source"><?php esc_html_e( 'Data Source', 'zc-dmt' ); ?> <span class="description">(required)</span></label>
                    </th>
                    <td>
                        <select id="indicator-source" name="indicator_source" required>
                            <option value=""><?php esc_html_e( 'Select Data Source', 'zc-dmt' ); ?></option>
                            <option value="world-bank"><?php esc_html_e( 'World Bank API', 'zc-dmt' ); ?></option>
                            <option value="fed-api"><?php esc_html_e( 'Federal Reserve API', 'zc-dmt' ); ?></option>
                            <option value="yahoo-finance"><?php esc_html_e( 'Yahoo Finance API', 'zc-dmt' ); ?></option>
                            <option value="manual"><?php esc_html_e( 'Manual Entry', 'zc-dmt' ); ?></option>
                            <option value="custom"><?php esc_html_e( 'Custom Source', 'zc-dmt' ); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e( 'Choose the data source for this indicator.', 'zc-dmt' ); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="indicator-description"><?php esc_html_e( 'Description', 'zc-dmt' ); ?></label>
                    </th>
                    <td>
                        <textarea id="indicator-description" name="indicator_description" rows="4" cols="50"></textarea>
                        <p class="description"><?php esc_html_e( 'Optional description for this indicator.', 'zc-dmt' ); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label><?php esc_html_e( 'Status', 'zc-dmt' ); ?></label>
                    </th>
                    <td>
                        <input type="radio" id="status-active" name="indicator_status" value="active" checked />
                        <label for="status-active"><?php esc_html_e( 'Active', 'zc-dmt' ); ?></label>
                        <br>
                        <input type="radio" id="status-inactive" name="indicator_status" value="inactive" />
                        <label for="status-inactive"><?php esc_html_e( 'Inactive', 'zc-dmt' ); ?></label>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="submit_indicator" class="button button-primary" value="<?php esc_attr_e( 'Add Indicator', 'zc-dmt' ); ?>" />
                <a href="<?php echo admin_url( 'admin.php?page=zc-dmt-indicators' ); ?>" class="button"><?php esc_html_e( 'Back to Indicators', 'zc-dmt' ); ?></a>
            </p>
        </form>
    </div>
    
    <div style="background: white; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px;">
        <h3><?php esc_html_e( 'Tips', 'zc-dmt' ); ?></h3>
        <ul>
            <li><?php esc_html_e( 'Use descriptive names for your indicators', 'zc-dmt' ); ?></li>
            <li><?php esc_html_e( 'The slug will be auto-generated from the indicator name', 'zc-dmt' ); ?></li>
            <li><?php esc_html_e( 'Choose the appropriate data source for automatic data fetching', 'zc-dmt' ); ?></li>
            <li><?php esc_html_e( 'Active indicators will appear in API endpoints and chart selection', 'zc-dmt' ); ?></li>
        </ul>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#indicator-name').on('input', function() {
        var name = $(this).val();
        var slug = name.toLowerCase().replace(/[^a-z0-9\s-]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-').replace(/^-+|-+$/g, '');
        $('#indicator-slug').val(slug);
    });
});
</script>

<style>
.form-table th { width: 200px; }
.notice { margin: 15px 0; padding: 12px; border-left: 4px solid; }
.notice-success { border-left-color: #46b450; background: #fff; }
.notice-error { border-left-color: #dc3232; background: #fff; }
.description { color: #666; font-style: italic; }
</style>
