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
        add_action('rest_api_init', [__CLASS__, 'register_rest_routes']);
    }
    
    public static function register_rest_routes() {
        register_rest_route('zc-dmt/v1', '/data/(?P<slug>[a-zA-Z0-9\-_]+)', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_indicator_data'],
            'permission_callback' => '__return_true'
        ]);
    }
    
    public static function get_indicator_data($request) {
        $slug = $request->get_param('slug');
        
        // Sample data - in real implementation, this would fetch from database
        $sample_data = [
            'cpi_us_sample' => [
                'labels' => ['2020-01', '2020-02', '2020-03', '2020-04', '2020-05', '2020-06'],
                'data' => [258.811, 258.678, 258.115, 256.389, 256.394, 257.797]
            ]
        ];
        
        if (isset($sample_data[$slug])) {
            return new WP_REST_Response($sample_data[$slug], 200);
        }
        
        return new WP_Error('not_found', 'Indicator data not found', ['status' => 404]);
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
                    border: 1px solid #ddd;
                    border-radius: 8px;
                    padding: 20px;
                    margin: 20px 0;
                    background: #fff;
                }
                .zci-chart-controls {
                    margin-bottom: 15px;
                    display: flex;
                    gap: 10px;
                    align-items: center;
                }
                .zci-search-input {
                    flex: 1;
                    padding: 8px 12px;
                    border: 1px solid #ccc;
                    border-radius: 4px;
                }
                .zci-search-results {
                    max-height: 200px;
                    overflow-y: auto;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    margin: 10px 0;
                    display: none;
                }
                .zci-search-results li {
                    padding: 8px 12px;
                    cursor: pointer;
                    border-bottom: 1px solid #eee;
                }
                .zci-search-results li:hover {
                    background: #f5f5f5;
                }
                .zci-selected-indicators {
                    margin: 15px 0;
                }
                .zci-indicator-tag {
                    display: inline-block;
                    background: #0073aa;
                    color: white;
                    padding: 4px 8px;
                    margin: 2px;
                    border-radius: 12px;
                    font-size: 12px;
                }
                .zci-indicator-tag .remove {
                    margin-left: 5px;
                    cursor: pointer;
                    font-weight: bold;
                }
                .zci-static-chart {
                    border: 1px solid #ddd;
                    border-radius: 8px;
                    padding: 20px;
                    margin: 20px 0;
                    background: #fff;
                }
                ');
            }
        }
    }
    
    public static function dynamic_shortcode($atts) {
        $atts = shortcode_atts([
            'title' => 'Economic Chart',
            'height' => '600px',
            'theme' => 'light',
            'show_search' => 'true',
            'show_compare' => 'true'
        ], $atts, 'economic_chart_dynamic');
        
        $chart_id = 'zci-dynamic-' . wp_generate_uuid4();
        
        ob_start();
        ?>
        <div class="zci-dynamic-chart" data-theme="<?php echo esc_attr($atts['theme']); ?>" id="<?php echo esc_attr($chart_id); ?>" style="height: <?php echo esc_attr($atts['height']); ?>;">
            <h3><?php echo esc_html($atts['title']); ?></h3>
            <?php if ($atts['show_search'] === 'true'): ?>
            <div class="zci-chart-controls">
                <input class="zci-search-input" placeholder="Search economic indicators..." type="text"/>
                <button class="button" type="button">Add Indicator</button>
            </div>
            <div class="zci-chart-container"><canvas class="zci-chart-canvas" style="height:400px;width:100%"></canvas></div>
            <ul class="zci-search-results"></ul>
            <div class="zci-selected-indicators"></div>
            <?php endif; ?>
        </div>
        
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
            return 'No indicators specified for static chart';
        }
        
        $chart_id = 'zci-static-' . wp_generate_uuid4();
        
        ob_start();
        ?>
        <div class="zci-static-chart" data-indicators="<?php echo esc_attr($atts['indicators']); ?>" data-theme="<?php echo esc_attr($atts['theme']); ?>" data-type="<?php echo esc_attr($atts['type']); ?>" id="<?php echo esc_attr($chart_id); ?>" style="height: <?php echo esc_attr($atts['height']); ?>;">
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
