<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wrap">
    <h1><?php esc_html_e( 'Indicators Management', 'zc-dmt' ); ?>
        <a href="#" class="page-title-action" id="add-indicator-btn"><?php esc_html_e( 'Add New Indicator', 'zc-dmt' ); ?></a>
    </h1>

    <form method="post" id="indicators-list-form">
        <?php wp_nonce_field( 'bulk_indicators', 'bulk_indicators_nonce' ); ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <td id="cb" class="manage-column check-column"><input type="checkbox"></td>
                    <th class="manage-column"><?php esc_html_e( 'Name', 'zc-dmt' ); ?></th>
                    <th class="manage-column"><?php esc_html_e( 'Slug', 'zc-dmt' ); ?></th>
                    <th class="manage-column"><?php esc_html_e( 'Data Source', 'zc-dmt' ); ?></th>
                    <th class="manage-column"><?php esc_html_e( 'Status', 'zc-dmt' ); ?></th>
                    <th class="manage-column"><?php esc_html_e( 'Last Updated', 'zc-dmt' ); ?></th>
                    <th class="manage-column"><?php esc_html_e( 'Actions', 'zc-dmt' ); ?></th>
                </tr>
            </thead>
            <tbody id="indicators-table-body">
                <?php
                // Placeholder rows; will be populated via JS/AJAX 
                ?>
            </tbody>
        </table>
    </form>

    <!-- Modal -->
    <div id="indicator-modal" class="hidden">
        <div class="indicator-modal-content">
            <h2 id="modal-title"><?php esc_html_e( 'Add Indicator', 'zc-dmt' ); ?></h2>
            <form id="indicator-form">
                <?php wp_nonce_field( 'save_indicator', 'indicator_nonce' ); ?>
                <table class="form-table">
                    <tr>
                        <th><label for="indicator-name"><?php esc_html_e( 'Indicator Name', 'zc-dmt' ); ?></label></th>
                        <td><input type="text" id="indicator-name" name="name" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th><label for="indicator-slug"><?php esc_html_e( 'Slug', 'zc-dmt' ); ?></label></th>
                        <td><input type="text" id="indicator-slug" name="slug" class="regular-text" readonly></td>
                    </tr>
                    <tr>
                        <th><label for="indicator-source"><?php esc_html_e( 'Data Source', 'zc-dmt' ); ?></label></th>
                        <td>
                            <select id="indicator-source" name="source" required>
                                <!-- Options populated via JS -->
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="indicator-status"><?php esc_html_e( 'Status', 'zc-dmt' ); ?></label></th>
                        <td>
                            <select id="indicator-status" name="status">
                                <option value="active"><?php esc_html_e( 'Active', 'zc-dmt' ); ?></option>
                                <option value="inactive"><?php esc_html_e( 'Inactive', 'zc-dmt' ); ?></option>
                            </select>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <button type="submit" class="button button-primary" id="save-indicator-btn"><?php esc_html_e( 'Save Indicator', 'zc-dmt' ); ?></button>
                    <button type="button" class="button" id="cancel-indicator-btn"><?php esc_html_e( 'Cancel', 'zc-dmt' ); ?></button>
                </p>
            </form>
        </div>
    </div>
</div>
