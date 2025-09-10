<?php /**
 * REST API routes for DMT plugin
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
class ZC_DMT_REST_API {
    
    private $indicators;
    
    public function __construct() {
        // Load the indicators class
        require_once plugin_dir_path( __FILE__ ) . 'class-indicators.php';
        $this->indicators = new ZC_DMT_Indicators();
    }
    
    public function register_routes() {
        // Data sources
        register_rest_route( 'zc-dmt/v1', '/sources', [
            'methods' => 'GET',
            'callback' => [ $this, 'get_sources' ],
            'permission_callback' => '__return_true',
        ]);
        
        // Indicators - GET all indicators
        register_rest_route( 'zc-dmt/v1', '/indicators', [
            'methods' => 'GET',
            'callback' => [ $this, 'get_indicators' ],
            'permission_callback' => '__return_true',
        ]);
        
        // Indicators - POST create new indicator
        register_rest_route( 'zc-dmt/v1', '/indicators', [
            'methods' => 'POST',
            'callback' => [ $this, 'create_indicator' ],
            'permission_callback' => [ $this, 'check_permissions' ],
        ]);
        
        // Indicators - PUT update indicator by ID
        register_rest_route( 'zc-dmt/v1', '/indicators/(?P<id>\\d+)', [
            'methods' => 'PUT',
            'callback' => [ $this, 'update_indicator' ],
            'permission_callback' => [ $this, 'check_permissions' ],
        ]);
        
        // Indicators - DELETE indicator by ID
        register_rest_route( 'zc-dmt/v1', '/indicators/(?P<id>\\d+)', [
            'methods' => 'DELETE',
            'callback' => [ $this, 'delete_indicator' ],
            'permission_callback' => [ $this, 'check_permissions' ],
        ]);
        
        // Search
        register_rest_route( 'zc-dmt/v1', '/search', [
            'methods' => 'GET',
            'callback' => [ $this, 'search_indicators' ],
            'permission_callback' => '__return_true',
        ]);
        
        // Data by indicator
        register_rest_route( 'zc-dmt/v1', '/data/(?P<slug>[a-zA-Z0-9_-]+)', [
            'methods' => 'GET',
            'callback' => [ $this, 'get_indicator_data' ],
            'permission_callback' => '__return_true',
        ]);
    }
    
    public function check_permissions( $request ) {
        return current_user_can( 'manage_options' );
    }
    
    public function get_sources( $request ) {
        global $wpdb;
        $table = $wpdb->prefix . 'zc_dmt_sources';
        $items = $wpdb->get_results( "SELECT id, source_key, source_type, name, status FROM $table ORDER BY created_at DESC", ARRAY_A );
        return rest_ensure_response( $items );
    }
    
    public function get_indicators( $request ) {
        return $this->indicators->get_all_indicators( $request );
    }
    
    public function create_indicator( $request ) {
        return $this->indicators->create_indicator( $request );
    }
    
    public function update_indicator( $request ) {
        $id = $request['id'];
        return $this->indicators->update_indicator( $id, $request );
    }
    
    public function delete_indicator( $request ) {
        $id = $request['id'];
        return $this->indicators->delete_indicator( $id, $request );
    }
    
    public function search_indicators( $request ) {
        global $wpdb;
        $q = sanitize_text_field( $request->get_param('q') );
        if ( strlen($q) < 2 ) return rest_ensure_response([]);
        $like = '%' . $wpdb->esc_like($q) . '%';
        $table = $wpdb->prefix . 'zc_dmt_indicators';
        $sql = $wpdb->prepare(
            "SELECT slug, display_name FROM $table WHERE (display_name LIKE %s OR slug LIKE %s) AND is_active = 1 ORDER BY display_name LIMIT 20",
            $like, $like
        );
        $items = $wpdb->get_results( $sql, ARRAY_A );
        return rest_ensure_response( $items );
    }
    
    public function get_indicator_data( $request ) {
        global $wpdb;
        $slug = sanitize_text_field($request['slug']);
        $table = $wpdb->prefix . 'zc_dmt_series';
        $rows = $wpdb->get_results( $wpdb->prepare(
            "SELECT obs_date, value FROM $table WHERE indicator_slug = %s ORDER BY obs_date ASC", $slug
        ), ARRAY_A );
        $labels = array(); $data = array();
        foreach($rows as $row){ $labels[] = $row['obs_date']; $data[] = floatval($row['value']); }
        return rest_ensure_response([
            'indicator' => $slug,
            'labels' => $labels,
            'data' => $data
        ]);
    }
}
?>
