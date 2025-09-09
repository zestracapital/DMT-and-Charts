<?php
/**
 * Chart shortcode system for DMT plugin
 * Provides dynamic and static chart shortcodes
 */

if (!defined('ABSPATH')) exit;

class ZC_DMT_Charts {

    public static function init() {
        add_shortcode('economic_chart_dynamic', [__CLASS__, 'dynamic_shortcode']);
        add_shortcode('economic_chart_static', [__CLASS__, 'static_shortcode']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_chart_assets']);
    }

    public static function enqueue_chart_assets() {
        // Only enqueue when shortcodes are used
        global $post;
        if (is_a($post, 'WP_Post')) {
            if (has_shortcode($post->post_content, 'economic_chart_dynamic') || 
                has_shortcode($post->post_content, 'economic_chart_static')) {

                wp_dequeue_script('chartjs');
                wp_enqueue_script(
                    'chartjs-umd',
                    'https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js',
                    [],
                    '4.4.1',
                    true
                );
                wp_enqueue_script('zc-dmt-charts', ZC_DMT_PLUGIN_URL . 'charts-frontend.js', ['chartjs-umd', 'jquery'], ZC_DMT_VERSION, true);
                wp_enqueue_script('zc-dmt-charts', ZC_DMT_PLUGIN_URL . 'charts-frontend.js', ['chartjs', 'jquery'], ZC_DMT_VERSION, true);

                wp_localize_script('zc-dmt-charts', 'zcCharts', [
                    'restUrl' => rest_url('zc-dmt/v1/'),
                    'nonce' => wp_create_nonce('wp_rest')
                ]);

                wp_add_inline_style('wp-block-library', '
                .zci-dynamic-chart {
                    border: 1px solid #e5e7eb;
                    border-radius: 10px;
                    padding: 20px;
                    background: #fff;
                    margin: 20px 0;
                }
                .zci-chart-controls {
                    display: flex;
                    gap: 10px;
                    margin-bottom: 15px;
                    flex-wrap: wrap;
                }
                .zci-search-input {
                    flex: 1;
                    min-width: 250px;
                    padding: 8px;
                    border: 1px solid #ccc;
                    border-radius: 4px;
                }
                .zci-search-results {
                    list-style: none;
                    margin: 0;
                    padding: 0;
                    max-height: 150px;
                    overflow-y: auto;
                    border: 1px solid #ccc;
                    display: none;
                }
                .zci-search-results li {
                    padding: 8px;
                    cursor: pointer;
                    border-bottom: 1px solid #eee;
                }
                .zci-search-results li:hover {
                    background: #f5f5f5;
                }
                .zci-selected-indicators {
                    margin: 10px 0;
                }
                .zci-indicator-tag {
                    display: inline-block;
                    background: #007cba;
                    color: white;
                    padding: 4px 8px;
                    margin: 2px;
                    border-radius: 4px;
                    font-size: 12px;
                }
                .zci-indicator-tag .remove {
                    margin-left: 5px;
                    cursor: pointer;
                }
                .zci-static-chart canvas {
                    width: 100% !important;
                    height: auto !important;
                }
                ');
            }
        }
    }

    public static function dynamic_shortcode($atts) {
        $atts = shortcode_atts([
            'title' => 'Economic Dashboard',
            'height' => '600px',
            'theme' => 'light',
            'show_search' => 'true',
            'show_compare' => 'true'
        ], $atts, 'economic_chart_dynamic');

        $chart_id = 'zci-dynamic-' . wp_generate_uuid4();

        ob_start();
        ?>
        <div id="<?php echo esc_attr($chart_id); ?>" class="zci-dynamic-chart" data-theme="<?php echo esc_attr($atts['theme']); ?>" style="height: <?php echo esc_attr($atts['height']); ?>;">
            <h3><?php echo esc_html($atts['title']); ?></h3>

            <?php if ($atts['show_search'] === 'true'): ?>
            <div class="zci-chart-controls">
                <input type="text" class="zci-search-input" placeholder="Search economic indicators..." />
                <button type="button" class="button">Add Indicator</button>
            </div>
            <ul class="zci-search-results"></ul>
            <div class="zci-selected-indicators"></div>
            <?php endif; ?>

            <canvas class="zci-chart-canvas" style="height: calc(100% - 120px);"></canvas>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof ZCCharts !== 'undefined') {
                ZCCharts.initDynamicChart('<?php echo esc_js($chart_id); ?>');
            }
        });
        </script>
        <?php
        return ob_get_clean();
    }

    public static function static_shortcode($atts) {
        $atts = shortcode_atts([
            'indicators' => '',
            'title' => '',
            'height' => '400px',
            'type' => 'line',
            'theme' => 'light'
        ], $atts, 'economic_chart_static');

        if (empty($atts['indicators'])) {
            return '<p><em>No indicators specified for static chart</em></p>';
        }

        $chart_id = 'zci-static-' . wp_generate_uuid4();

        ob_start();
        ?>
        <div id="<?php echo esc_attr($chart_id); ?>" class="zci-static-chart" data-indicators="<?php echo esc_attr($atts['indicators']); ?>" data-type="<?php echo esc_attr($atts['type']); ?>" data-theme="<?php echo esc_attr($atts['theme']); ?>" style="height: <?php echo esc_attr($atts['height']); ?>;">
            <?php if (!empty($atts['title'])): ?>
                <h3><?php echo esc_html($atts['title']); ?></h3>
            <?php endif; ?>
            <canvas class="zci-chart-canvas" style="width: 100%; height: calc(100% - 40px);"></canvas>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof ZCCharts !== 'undefined') {
                ZCCharts.initStaticChart('<?php echo esc_js($chart_id); ?>');
            }
        });
        </script>
        <?php
        return ob_get_clean();
    }
}

// Initialize shortcodes
ZC_DMT_Charts::init();
