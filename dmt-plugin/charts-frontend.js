var ZCCharts = {

    initDynamicChart: function(chartId) {
        var container = document.getElementById(chartId);
        if (!container) return;

        var searchInput = container.querySelector('.zci-search-input');
        var searchResults = container.querySelector('.zci-search-results');
        var selectedIndicators = container.querySelector('.zci-selected-indicators');
        var canvas = container.querySelector('.zci-chart-canvas');

        var selected = [];
        var chart = null;
        var searchTimeout = null;

        // Search functionality
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                var query = this.value.trim();
                clearTimeout(searchTimeout);

                if (query.length < 2) {
                    searchResults.style.display = 'none';
                    return;
                }

                searchTimeout = setTimeout(function() {
                    fetch(zcCharts.restUrl + 'search?q=' + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(data => {
                        searchResults.innerHTML = '';
                        if (data && data.length > 0) {
                            data.forEach(function(item) {
                                var li = document.createElement('li');
                                li.textContent = item.display_name + ' (' + item.slug + ')';
                                li.dataset.slug = item.slug;
                                li.dataset.name = item.display_name;
                                li.addEventListener('click', function() {
                                    ZCCharts.addIndicator(item.slug, item.display_name, chartId);
                                });
                                searchResults.appendChild(li);
                            });
                            searchResults.style.display = 'block';
                        } else {
                            searchResults.innerHTML = '<li>No indicators found</li>';
                            searchResults.style.display = 'block';
                        }
                    });
                }, 300);
            });
        }

        // Hide search results when clicking outside
        document.addEventListener('click', function(e) {
            if (!container.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        }).catch(function(err) {
    console.error('ZCCharts render error:', err);
    // Display friendly message
    var errorDiv = document.createElement('div');
    errorDiv.className = 'zci-chart-error';
    errorDiv.style.color='#666';
    errorDiv.style.padding='20px';
    errorDiv.textContent = 'Chart data is not available at the moment.';
    container.appendChild(errorDiv);
});
    },

    initStaticChart: function(chartId) {
    console.log('ZCCharts: initStaticChart called for', chartId);

        var container = document.getElementById(chartId);
        if (!container) return;

        var indicators = container.dataset.indicators;
    console.log('ZCCharts: rendering static chart for', indicators, 'type', type);

        var type = container.dataset.type || 'line';

        if (!indicators) return;

        var indicatorList = indicators.split(',').map(s => s.trim());
        this.renderChart(chartId, indicatorList, type);
    },

    addIndicator: function(slug, name, chartId) {
        var container = document.getElementById(chartId);
        var selectedDiv = container.querySelector('.zci-selected-indicators');
        var searchResults = container.querySelector('.zci-search-results');
        var searchInput = container.querySelector('.zci-search-input');

        // Check if already added
        if (container.querySelector('[data-slug="' + slug + '"]')) return;

        // Add indicator tag
        var tag = document.createElement('span');
        tag.className = 'zci-indicator-tag';
        tag.dataset.slug = slug;
        tag.innerHTML = name + ' <span class="remove">×</span>';

        tag.querySelector('.remove').addEventListener('click', function() {
            tag.remove();
            ZCCharts.updateChart(chartId);
        });

        selectedDiv.appendChild(tag);

        // Clear search
        searchInput.value = '';
        searchResults.style.display = 'none';

        // Update chart
        this.updateChart(chartId);
    },

    updateChart: function(chartId) {
        var container = document.getElementById(chartId);
        var tags = container.querySelectorAll('.zci-indicator-tag');
        var indicators = [];

        tags.forEach(function(tag) {
            indicators.push(tag.dataset.slug);
        });

        if (indicators.length > 0) {
            this.renderChart(chartId, indicators);
        }
    },

    renderChart: function(chartId, indicators, type) {
        var container = document.getElementById(chartId);
        var canvas = container.querySelector('.zci-chart-canvas');

        if (!canvas || indicators.length === 0) return;

        type = type || 'line';

        // Fetch data for all indicators
        Promise.all(indicators.map(slug => 
            fetch(zcCharts.restUrl + 'data/' + slug).then(r => r.json())
        )).then(responses => {
            var datasets = [];
            var labels = [];
            var colors = ['#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899'];

            responses.forEach((data, index) => {
                if (data.labels && data.data) {
                    // Use the first indicator's labels as base
                    if (index === 0) {
                        labels = data.labels;
                    }

                    datasets.push({
                        label: data.indicator,
                        data: data.data,
                        borderColor: colors[index % colors.length],
                        backgroundColor: colors[index % colors.length] + '20',
                        tension: 0.2,
                        fill: false
                    });
                }
            });

            // Destroy existing chart
            if (container._chartInstance) {
                container._chartInstance.destroy();
            }

            // Create new chart
            var ctx = canvas.getContext('2d');
            container._chartInstance = new Chart(ctx, {
                type: type,
                data: {
                    labels: labels,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        x: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Time'
                            }
                        },
                        y: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Value'
                            }
                        }
                    }
                }
            });
        }).catch(function(err) {
    console.error('ZCCharts render error:', err);
    // Display friendly message
    var errorDiv = document.createElement('div');
    errorDiv.className = 'zci-chart-error';
    errorDiv.style.color='#666';
    errorDiv.style.padding='20px';
    errorDiv.textContent = 'Chart data is not available at the moment.';
    container.appendChild(errorDiv);
});
    }
};
// Auto-initialize charts on page load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all static charts
    document.querySelectorAll('.zci-static-chart').forEach(function(el) {
        if (el.id) {
            ZCCharts.initStaticChart(el.id);
        }
    });

    // Initialize all dynamic charts
    document.querySelectorAll('.zci-dynamic-chart').forEach(function(el) {
        if (el.id) {
            ZCCharts.initDynamicChart(el.id);
        }
    });
});
