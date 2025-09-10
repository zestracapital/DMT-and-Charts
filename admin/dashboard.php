<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get database statistics safely
$stats = [];
try {
    if ( class_exists( 'ZC_DMT_Database' ) ) {
        $stats = ZC_DMT_Database::get_stats();
    }
} catch ( Exception $e ) {
    $stats = [
        'sources' => 0,
        'active_sources' => 0,
        'indicators' => 0,
        'active_indicators' => 0,
        'data_points' => 0,
        'imports' => 0,
        'successful_imports' => 0
    ];
}
?>

<div class="wrap">
    <h1><?php _e( 'Economic DMT Dashboard', 'zc-dmt' ); ?></h1>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; text-align: center;">
            <h2 style="margin: 0; color: white;"><?php echo intval( $stats['sources'] ?? 0 ); ?></h2>
            <p style="margin: 5px 0 0; color: rgba(255,255,255,0.9);">Data Sources</p>
        </div>

        <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 20px; border-radius: 8px; text-align: center;">
            <h2 style="margin: 0; color: white;"><?php echo intval( $stats['indicators'] ?? 0 ); ?></h2>
            <p style="margin: 5px 0 0; color: rgba(255,255,255,0.9);">Indicators</p>
        </div>

        <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 20px; border-radius: 8px; text-align: center;">
            <h2 style="margin: 0; color: white;"><?php echo number_format( intval( $stats['data_points'] ?? 0 ) ); ?></h2>
            <p style="margin: 5px 0 0; color: rgba(255,255,255,0.9);">Data Points</p>
        </div>

        <div style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; padding: 20px; border-radius: 8px; text-align: center;">
            <h2 style="margin: 0; color: white;"><?php echo intval( $stats['imports'] ?? 0 ); ?></h2>
            <p style="margin: 5px 0 0; color: rgba(255,255,255,0.9);">Imports</p>
        </div>
    </div>

    <div style="background: white; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; margin: 20px 0;">
        <h2>🎯 About DMT (Data Management Tool)</h2>
        <p><strong>This plugin handles ONLY data management:</strong></p>
        <ul>
            <li>✅ Manage data sources (FRED API, CSV files, manual entry)</li>
            <li>✅ Import and organize economic indicators</li>
            <li>✅ Provide REST APIs for chart systems</li>
            <li>❌ NO chart rendering (use Charts plugin for visualization)</li>
        </ul>

        <h3>Quick Actions</h3>
        <p>
            <a href="<?php echo admin_url('admin.php?page=zc-dmt-sources'); ?>" class="button button-primary">Manage Data Sources</a>
            <a href="<?php echo admin_url('admin.php?page=zc-dmt-import'); ?>" class="button">Import CSV Data</a>
            <a href="<?php echo admin_url('admin.php?page=zc-dmt-indicators'); ?>" class="button">View Indicators</a>
        </p>
    </div>

    <div style="background: white; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px;">
        <h2>📡 REST API Endpoints</h2>
        <p>These endpoints provide data for the Charts plugin:</p>
        <ul>
            <li><code><?php echo rest_url('zc-dmt/v1/sources'); ?></code> - Get all data sources</li>
            <li><code><?php echo rest_url('zc-dmt/v1/indicators'); ?></code> - Get all indicators</li>
            <li><code><?php echo rest_url('zc-dmt/v1/search'); ?></code> - Search indicators</li>
        </ul>
    </div>
</div>