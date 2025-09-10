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
                <tr>
                    <th scope="row" class="check-column"><input type="checkbox" name="indicator[]" value="1"></th>
                    <td><strong>GDP Growth Rate</strong></td>
                    <td>gdp-growth-rate</td>
                    <td>World Bank API</td>
                    <td><?php esc_html_e( 'Active', 'zc-dmt' ); ?></td>
                    <td>2025-09-10</td>
                    <td>
                        <a href="#" class="edit-indicator" data-id="1"><?php esc_html_e( 'Edit', 'zc-dmt' ); ?></a> |
                        <a href="#" class="delete-indicator" data-id="1"><?php esc_html_e( 'Delete', 'zc-dmt' ); ?></a>
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="check-column"><input type="checkbox" name="indicator[]" value="2"></th>
                    <td><strong>Inflation Rate</strong></td>
                    <td>inflation-rate</td>
                    <td>Federal Reserve API</td>
                    <td><?php esc_html_e( 'Inactive', 'zc-dmt' ); ?></td>
                    <td>2025-09-08</td>
                    <td>
                        <a href="#" class="edit-indicator" data-id="2"><?php esc_html_e( 'Edit', 'zc-dmt' ); ?></a> |
                        <a href="#" class="delete-indicator" data-id="2"><?php esc_html_e( 'Delete', 'zc-dmt' ); ?></a>
                    </td>
                </tr>
            </tbody>
        </table>
    </form>

    <!-- Modal -->
    <div id="indicator-modal" class="hidden">
        <div class="indicator-modal-content">
            <span class="close-modal">&times;</span>
            <h2 id="modal-title"><?php esc_html_e( 'Add New Indicator', 'zc-dmt' ); ?></h2>
            <form id="indicator-form">
                <?php wp_nonce_field( 'save_indicator', 'indicator_nonce' ); ?>
                <table class="form-table">
                    <tr>
                        <th><label for="indicator-name"><?php esc_html_e( 'Indicator Name', 'zc-dmt' ); ?></label></th>
                        <td>
                            <input type="text" id="indicator-name" name="name" class="regular-text" required>
                            <p class="description"><?php esc_html_e( 'Enter the display name for this indicator.', 'zc-dmt' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="indicator-slug"><?php esc_html_e( 'Slug', 'zc-dmt' ); ?></label></th>
                        <td>
                            <input type="text" id="indicator-slug" name="slug" class="regular-text" readonly>
                            <p class="description"><?php esc_html_e( 'Unique identifier for this indicator (auto-generated from name).', 'zc-dmt' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="indicator-source"><?php esc_html_e( 'Data Source', 'zc-dmt' ); ?></label></th>
                        <td>
                            <select id="indicator-source" name="source" required>
                                <option value=""><?php esc_html_e( 'Select Data Source', 'zc-dmt' ); ?></option>
                                <option value="world-bank"><?php esc_html_e( 'World Bank API', 'zc-dmt' ); ?></option>
                                <option value="fed-api"><?php esc_html_e( 'Federal Reserve API', 'zc-dmt' ); ?></option>
                                <option value="yahoo-finance"><?php esc_html_e( 'Yahoo Finance API', 'zc-dmt' ); ?></option>
                                <option value="custom"><?php esc_html_e( 'Custom Source', 'zc-dmt' ); ?></option>
                            </select>
                            <p class="description"><?php esc_html_e( 'Choose the data source for this indicator.', 'zc-dmt' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="indicator-description"><?php esc_html_e( 'Description', 'zc-dmt' ); ?></label></th>
                        <td>
                            <textarea id="indicator-description" name="description" rows="3" cols="50"></textarea>
                            <p class="description"><?php esc_html_e( 'Optional description for this indicator.', 'zc-dmt' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="indicator-status"><?php esc_html_e( 'Status', 'zc-dmt' ); ?></label></th>
                        <td>
                            <input type="radio" id="status-active" name="status" value="active" checked>
                            <label for="status-active"><?php esc_html_e( 'Active', 'zc-dmt' ); ?></label>
                            <input type="radio" id="status-inactive" name="status" value="inactive">
                            <label for="status-inactive"><?php esc_html_e( 'Inactive', 'zc-dmt' ); ?></label>
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

<style>
#indicator-modal {
    position: fixed;
    z-index: 999999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.indicator-modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 60%;
    max-width: 600px;
    position: relative;
}

.close-modal {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close-modal:hover {
    color: black;
}

.hidden {
    display: none !important;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Add New Indicator button click
    $('#add-indicator-btn').click(function(e) {
        e.preventDefault();
        $('#modal-title').text('<?php esc_html_e( 'Add New Indicator', 'zc-dmt' ); ?>');
        $('#indicator-form')[0].reset();
        $('#indicator-modal').removeClass('hidden');
    });

    // Close modal
    $('.close-modal, #cancel-indicator-btn').click(function() {
        $('#indicator-modal').addClass('hidden');
    });

    // Generate slug from name
    $('#indicator-name').on('input', function() {
        var name = $(this).val();
        var slug = name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
        $('#indicator-slug').val(slug);
    });

    // Form submission (placeholder - will need actual AJAX implementation)
    $('#indicator-form').submit(function(e) {
        e.preventDefault();
        alert('Indicator saved! (This is a placeholder - needs backend implementation)');
        $('#indicator-modal').addClass('hidden');
    });
});
</script>
