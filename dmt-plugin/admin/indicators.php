<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wrap">
    <h1><?php _e( 'Indicators Management', 'zc-dmt' ); ?></h1>
    
    <!-- Add New Indicator Button -->
    <div class="page-title-action">
        <button id="add-new-indicator" class="button button-primary"><?php _e( 'Add New Indicator', 'zc-dmt' ); ?></button>
    </div>
    
    <!-- Indicators List Table -->
    <div class="indicators-table-container">
        <table class="wp-list-table widefat fixed striped indicators">
            <thead>
                <tr>
                    <th scope="col" id="cb" class="manage-column column-cb check-column">
                        <input id="cb-select-all-1" type="checkbox">
                    </th>
                    <th scope="col" class="manage-column column-name column-primary"><?php _e( 'Name', 'zc-dmt' ); ?></th>
                    <th scope="col" class="manage-column column-slug"><?php _e( 'Slug', 'zc-dmt' ); ?></th>
                    <th scope="col" class="manage-column column-source"><?php _e( 'Data Source', 'zc-dmt' ); ?></th>
                    <th scope="col" class="manage-column column-status"><?php _e( 'Status', 'zc-dmt' ); ?></th>
                    <th scope="col" class="manage-column column-date"><?php _e( 'Last Updated', 'zc-dmt' ); ?></th>
                    <th scope="col" class="manage-column column-actions"><?php _e( 'Actions', 'zc-dmt' ); ?></th>
                </tr>
            </thead>
            <tbody id="the-list">
                <!-- Sample data - replace with dynamic data -->
                <tr>
                    <th scope="row" class="check-column">
                        <input id="cb-select-1" type="checkbox" name="indicator[]" value="1">
                    </th>
                    <td class="column-name column-primary">
                        <strong>GDP Growth Rate</strong>
                        <div class="row-actions">
                            <span class="edit"><a href="#" class="edit-indicator" data-id="1"><?php _e( 'Edit', 'zc-dmt' ); ?></a> | </span>
                            <span class="trash"><a href="#" class="delete-indicator" data-id="1"><?php _e( 'Delete', 'zc-dmt' ); ?></a></span>
                        </div>
                    </td>
                    <td class="column-slug">gdp-growth-rate</td>
                    <td class="column-source">World Bank API</td>
                    <td class="column-status">
                        <span class="status-active"><?php _e( 'Active', 'zc-dmt' ); ?></span>
                    </td>
                    <td class="column-date">2025-09-10</td>
                    <td class="column-actions">
                        <button class="button button-small edit-indicator" data-id="1"><?php _e( 'Edit', 'zc-dmt' ); ?></button>
                        <button class="button button-small delete-indicator" data-id="1"><?php _e( 'Delete', 'zc-dmt' ); ?></button>
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="check-column">
                        <input id="cb-select-2" type="checkbox" name="indicator[]" value="2">
                    </th>
                    <td class="column-name column-primary">
                        <strong>Inflation Rate</strong>
                        <div class="row-actions">
                            <span class="edit"><a href="#" class="edit-indicator" data-id="2"><?php _e( 'Edit', 'zc-dmt' ); ?></a> | </span>
                            <span class="trash"><a href="#" class="delete-indicator" data-id="2"><?php _e( 'Delete', 'zc-dmt' ); ?></a></span>
                        </div>
                    </td>
                    <td class="column-slug">inflation-rate</td>
                    <td class="column-source">Federal Reserve API</td>
                    <td class="column-status">
                        <span class="status-inactive"><?php _e( 'Inactive', 'zc-dmt' ); ?></span>
                    </td>
                    <td class="column-date">2025-09-08</td>
                    <td class="column-actions">
                        <button class="button button-small edit-indicator" data-id="2"><?php _e( 'Edit', 'zc-dmt' ); ?></button>
                        <button class="button button-small delete-indicator" data-id="2"><?php _e( 'Delete', 'zc-dmt' ); ?></button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <!-- Hidden Modal Form for Add/Edit -->
    <div id="indicator-modal" class="indicator-modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-title"><?php _e( 'Add New Indicator', 'zc-dmt' ); ?></h2>
                <span class="close" id="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <form id="indicator-form" method="post">
                    <input type="hidden" id="indicator-id" name="indicator_id" value="">
                    <input type="hidden" id="action" name="action" value="save_indicator">
                    <?php wp_nonce_field( 'save_indicator', 'indicator_nonce' ); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="indicator-name"><?php _e( 'Indicator Name', 'zc-dmt' ); ?></label>
                            </th>
                            <td>
                                <input type="text" id="indicator-name" name="indicator_name" class="regular-text" required>
                                <p class="description"><?php _e( 'Enter the display name for this indicator.', 'zc-dmt' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="indicator-slug"><?php _e( 'Slug', 'zc-dmt' ); ?></label>
                            </th>
                            <td>
                                <input type="text" id="indicator-slug" name="indicator_slug" class="regular-text" required>
                                <p class="description"><?php _e( 'Unique identifier for this indicator (auto-generated from name).', 'zc-dmt' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="data-source"><?php _e( 'Data Source', 'zc-dmt' ); ?></label>
                            </th>
                            <td>
                                <select id="data-source" name="data_source" required>
                                    <option value=""><?php _e( 'Select Data Source', 'zc-dmt' ); ?></option>
                                    <option value="world-bank"><?php _e( 'World Bank API', 'zc-dmt' ); ?></option>
                                    <option value="federal-reserve"><?php _e( 'Federal Reserve API', 'zc-dmt' ); ?></option>
                                    <option value="yahoo-finance"><?php _e( 'Yahoo Finance API', 'zc-dmt' ); ?></option>
                                    <option value="custom"><?php _e( 'Custom Source', 'zc-dmt' ); ?></option>
                                </select>
                                <p class="description"><?php _e( 'Choose the data source for this indicator.', 'zc-dmt' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="indicator-description"><?php _e( 'Description', 'zc-dmt' ); ?></label>
                            </th>
                            <td>
                                <textarea id="indicator-description" name="indicator_description" rows="4" class="large-text"></textarea>
                                <p class="description"><?php _e( 'Optional description for this indicator.', 'zc-dmt' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="indicator-status"><?php _e( 'Status', 'zc-dmt' ); ?></label>
                            </th>
                            <td>
                                <select id="indicator-status" name="indicator_status">
                                    <option value="active"><?php _e( 'Active', 'zc-dmt' ); ?></option>
                                    <option value="inactive"><?php _e( 'Inactive', 'zc-dmt' ); ?></option>
                                </select>
                            </td>
                        </tr>
                    </table>
                    
                    <div class="modal-footer">
                        <button type="button" class="button" id="cancel-indicator"><?php _e( 'Cancel', 'zc-dmt' ); ?></button>
                        <button type="submit" class="button button-primary" id="save-indicator"><?php _e( 'Save Indicator', 'zc-dmt' ); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
/* Modal Styles */
.indicator-modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.4);
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 0;
    border: 1px solid #888;
    width: 60%;
    max-width: 600px;
    border-radius: 4px;
}

.modal-header {
    padding: 15px 20px;
    background-color: #f1f1f1;
    border-bottom: 1px solid #ddd;
    border-radius: 4px 4px 0 0;
    position: relative;
}

.modal-header h2 {
    margin: 0;
    font-size: 18px;
}

.close {
    position: absolute;
    right: 15px;
    top: 15px;
    color: #aaa;
    font-size: 24px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover {
    color: #000;
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    padding: 15px 20px;
    text-align: right;
    border-top: 1px solid #ddd;
    background-color: #f9f9f9;
}

.modal-footer .button {
    margin-left: 10px;
}

/* Table Status Styles */
.status-active {
    color: #46b450;
    font-weight: bold;
}

.status-inactive {
    color: #dc3232;
    font-weight: bold;
}

/* Page Title Action */
.page-title-action {
    float: right;
    margin-top: -5px;
}

.indicators-table-container {
    clear: both;
    margin-top: 20px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Modal functionality
    $('#add-new-indicator').click(function() {
        $('#modal-title').text('<?php _e( "Add New Indicator", "zc-dmt" ); ?>');
        $('#indicator-form')[0].reset();
        $('#indicator-id').val('');
        $('#indicator-modal').show();
    });
    
    $('.edit-indicator').click(function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        $('#modal-title').text('<?php _e( "Edit Indicator", "zc-dmt" ); ?>');
        $('#indicator-id').val(id);
        // Load indicator data here
        $('#indicator-modal').show();
    });
    
    $('#close-modal, #cancel-indicator').click(function() {
        $('#indicator-modal').hide();
    });
    
    // Auto-generate slug from name
    $('#indicator-name').on('input', function() {
        var name = $(this).val();
        var slug = name.toLowerCase()
            .replace(/[^\w\s-]/g, '')
            .replace(/[\s_-]+/g, '-')
            .replace(/^-+|-+$/g, '');
        $('#indicator-slug').val(slug);
    });
    
    // Form submission
    $('#indicator-form').submit(function(e) {
        e.preventDefault();
        // Handle form submission via AJAX here
        alert('Form submission functionality to be implemented');
        $('#indicator-modal').hide();
    });
    
    // Delete functionality
    $('.delete-indicator').click(function(e) {
        e.preventDefault();
        if (confirm('<?php _e( "Are you sure you want to delete this indicator?", "zc-dmt" ); ?>')) {
            // Handle deletion here
            alert('Delete functionality to be implemented');
        }
    });
    
    // Select all functionality
    $('#cb-select-all-1').change(function() {
        $('input[name="indicator[]"]').prop('checked', this.checked);
    });
});
</script>
