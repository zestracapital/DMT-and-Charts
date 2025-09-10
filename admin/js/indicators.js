/**
 * Indicators Management JavaScript
 * Handles fetching indicators from REST endpoint, populating tables, and modal operations
 */

(function($) {
    'use strict';

    // DOM elements
    const $indicatorsTable = $('#indicators-table tbody');
    const $loadingSpinner = $('#loading-spinner');
    const $errorMessage = $('#error-message');
    const $indicatorModal = $('#indicator-modal');
    const $modalForm = $('#indicator-form');
    const $refreshButton = $('#refresh-indicators');
    const $addIndicatorButton = $('#add-indicator');

    // API endpoints
    const API_BASE = '/wp-json/dmt/v1';
    const ENDPOINTS = {
        indicators: `${API_BASE}/indicators`,
        indicator: (id) => `${API_BASE}/indicators/${id}`
    };

    /**
     * Initialize the indicators management interface
     */
    function init() {
        bindEvents();
        fetchIndicators();
    }

    /**
     * Bind event listeners
     */
    function bindEvents() {
        $refreshButton.on('click', handleRefresh);
        $addIndicatorButton.on('click', handleAddIndicator);
        $indicatorsTable.on('click', '.edit-indicator', handleEditIndicator);
        $indicatorsTable.on('click', '.delete-indicator', handleDeleteIndicator);
        $modalForm.on('submit', handleFormSubmit);
        $('.modal .close, .modal .modal-backdrop').on('click', closeModal);
    }

    /**
     * Fetch indicators from REST API
     */
    function fetchIndicators() {
        showLoading(true);
        hideError();

        $.ajax({
            url: ENDPOINTS.indicators,
            method: 'GET',
            headers: {
                'X-WP-Nonce': dmtAjax.nonce
            }
        })
        .done(function(response) {
            populateTable(response.data || response);
        })
        .fail(function(xhr) {
            handleApiError(xhr, 'Failed to fetch indicators');
        })
        .always(function() {
            showLoading(false);
        });
    }

    /**
     * Populate the indicators table with data
     * @param {Array} indicators - Array of indicator objects
     */
    function populateTable(indicators) {
        $indicatorsTable.empty();

        if (!indicators || indicators.length === 0) {
            $indicatorsTable.append(
                '<tr><td colspan="6" class="text-center">No indicators found</td></tr>'
            );
            return;
        }

        indicators.forEach(function(indicator) {
            const row = createTableRow(indicator);
            $indicatorsTable.append(row);
        });
    }

    /**
     * Create a table row for an indicator
     * @param {Object} indicator - Indicator object
     * @returns {string} HTML table row
     */
    function createTableRow(indicator) {
        const statusBadge = indicator.active ? 
            '<span class="badge badge-success">Active</span>' : 
            '<span class="badge badge-secondary">Inactive</span>';

        const lastUpdate = indicator.last_update ? 
            new Date(indicator.last_update).toLocaleDateString() : 
            'Never';

        return `
            <tr data-id="${indicator.id}">
                <td>${escapeHtml(indicator.name)}</td>
                <td>${escapeHtml(indicator.symbol)}</td>
                <td>${escapeHtml(indicator.source)}</td>
                <td>${statusBadge}</td>
                <td>${lastUpdate}</td>
                <td>
                    <button class="btn btn-sm btn-primary edit-indicator" data-id="${indicator.id}" title="Edit">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger delete-indicator" data-id="${indicator.id}" title="Delete">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    }

    /**
     * Handle refresh button click
     */
    function handleRefresh(e) {
        e.preventDefault();
        fetchIndicators();
    }

    /**
     * Handle add indicator button click
     */
    function handleAddIndicator(e) {
        e.preventDefault();
        openModal('add');
    }

    /**
     * Handle edit indicator button click
     */
    function handleEditIndicator(e) {
        e.preventDefault();
        const indicatorId = $(this).data('id');
        openModal('edit', indicatorId);
    }

    /**
     * Handle delete indicator button click
     */
    function handleDeleteIndicator(e) {
        e.preventDefault();
        const indicatorId = $(this).data('id');
        const indicatorName = $(this).closest('tr').find('td:first').text();

        if (confirm(`Are you sure you want to delete the indicator "${indicatorName}"?`)) {
            deleteIndicator(indicatorId);
        }
    }

    /**
     * Open the indicator modal
     * @param {string} mode - 'add' or 'edit'
     * @param {number|null} indicatorId - ID for edit mode
     */
    function openModal(mode, indicatorId = null) {
        $modalForm[0].reset();
        $modalForm.data('mode', mode);
        $modalForm.data('indicator-id', indicatorId);

        const modalTitle = mode === 'add' ? 'Add New Indicator' : 'Edit Indicator';
        $indicatorModal.find('.modal-title').text(modalTitle);

        if (mode === 'edit' && indicatorId) {
            loadIndicatorData(indicatorId);
        }

        $indicatorModal.addClass('show').show();
    }

    /**
     * Close the indicator modal
     */
    function closeModal() {
        $indicatorModal.removeClass('show').hide();
        $modalForm[0].reset();
    }

    /**
     * Load indicator data for editing
     * @param {number} indicatorId - Indicator ID
     */
    function loadIndicatorData(indicatorId) {
        $.ajax({
            url: ENDPOINTS.indicator(indicatorId),
            method: 'GET',
            headers: {
                'X-WP-Nonce': dmtAjax.nonce
            }
        })
        .done(function(response) {
            const indicator = response.data || response;
            populateForm(indicator);
        })
        .fail(function(xhr) {
            handleApiError(xhr, 'Failed to load indicator data');
            closeModal();
        });
    }

    /**
     * Populate form with indicator data
     * @param {Object} indicator - Indicator object
     */
    function populateForm(indicator) {
        $('#indicator-name').val(indicator.name);
        $('#indicator-symbol').val(indicator.symbol);
        $('#indicator-source').val(indicator.source);
        $('#indicator-description').val(indicator.description);
        $('#indicator-active').prop('checked', indicator.active);
        $('#indicator-fred-id').val(indicator.fred_series_id || '');
        $('#indicator-units').val(indicator.units || '');
        $('#indicator-frequency').val(indicator.frequency || '');
    }

    /**
     * Handle form submission
     */
    function handleFormSubmit(e) {
        e.preventDefault();
        
        const mode = $modalForm.data('mode');
        const indicatorId = $modalForm.data('indicator-id');
        const formData = serializeFormData();

        if (mode === 'add') {
            createIndicator(formData);
        } else {
            updateIndicator(indicatorId, formData);
        }
    }

    /**
     * Serialize form data
     * @returns {Object} Form data object
     */
    function serializeFormData() {
        return {
            name: $('#indicator-name').val().trim(),
            symbol: $('#indicator-symbol').val().trim(),
            source: $('#indicator-source').val(),
            description: $('#indicator-description').val().trim(),
            active: $('#indicator-active').is(':checked'),
            fred_series_id: $('#indicator-fred-id').val().trim(),
            units: $('#indicator-units').val().trim(),
            frequency: $('#indicator-frequency').val()
        };
    }

    /**
     * Create a new indicator
     * @param {Object} data - Indicator data
     */
    function createIndicator(data) {
        $.ajax({
            url: ENDPOINTS.indicators,
            method: 'POST',
            headers: {
                'X-WP-Nonce': dmtAjax.nonce,
                'Content-Type': 'application/json'
            },
            data: JSON.stringify(data)
        })
        .done(function(response) {
            closeModal();
            fetchIndicators();
            showSuccess('Indicator created successfully');
        })
        .fail(function(xhr) {
            handleApiError(xhr, 'Failed to create indicator');
        });
    }

    /**
     * Update an existing indicator
     * @param {number} indicatorId - Indicator ID
     * @param {Object} data - Updated indicator data
     */
    function updateIndicator(indicatorId, data) {
        $.ajax({
            url: ENDPOINTS.indicator(indicatorId),
            method: 'PUT',
            headers: {
                'X-WP-Nonce': dmtAjax.nonce,
                'Content-Type': 'application/json'
            },
            data: JSON.stringify(data)
        })
        .done(function(response) {
            closeModal();
            fetchIndicators();
            showSuccess('Indicator updated successfully');
        })
        .fail(function(xhr) {
            handleApiError(xhr, 'Failed to update indicator');
        });
    }

    /**
     * Delete an indicator
     * @param {number} indicatorId - Indicator ID
     */
    function deleteIndicator(indicatorId) {
        $.ajax({
            url: ENDPOINTS.indicator(indicatorId),
            method: 'DELETE',
            headers: {
                'X-WP-Nonce': dmtAjax.nonce
            }
        })
        .done(function(response) {
            fetchIndicators();
            showSuccess('Indicator deleted successfully');
        })
        .fail(function(xhr) {
            handleApiError(xhr, 'Failed to delete indicator');
        });
    }

    /**
     * Show/hide loading spinner
     * @param {boolean} show - Whether to show the spinner
     */
    function showLoading(show) {
        if (show) {
            $loadingSpinner.show();
            $indicatorsTable.css('opacity', '0.5');
        } else {
            $loadingSpinner.hide();
            $indicatorsTable.css('opacity', '1');
        }
    }

    /**
     * Show error message
     * @param {string} message - Error message
     */
    function showError(message) {
        $errorMessage.text(message).show();
        setTimeout(function() {
            $errorMessage.fadeOut();
        }, 5000);
    }

    /**
     * Hide error message
     */
    function hideError() {
        $errorMessage.hide();
    }

    /**
     * Show success message
     * @param {string} message - Success message
     */
    function showSuccess(message) {
        // Create success notification (you may want to implement a proper notification system)
        const $notification = $('<div class="alert alert-success alert-dismissible fade show" role="alert">')
            .html(message + '<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>');
        
        $('.wrap h1').after($notification);
        
        setTimeout(function() {
            $notification.fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
    }

    /**
     * Handle API errors
     * @param {Object} xhr - XMLHttpRequest object
     * @param {string} defaultMessage - Default error message
     */
    function handleApiError(xhr, defaultMessage) {
        let errorMessage = defaultMessage;
        
        try {
            const response = JSON.parse(xhr.responseText);
            errorMessage = response.message || response.error || defaultMessage;
        } catch (e) {
            // Use default message if parsing fails
        }
        
        showError(errorMessage);
    }

    /**
     * Escape HTML to prevent XSS
     * @param {string} text - Text to escape
     * @returns {string} Escaped text
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Initialize when document is ready
    $(document).ready(function() {
        // Only initialize if we're on the indicators page
        if ($('#indicators-table').length) {
            init();
        }
    });

})(jQuery);
