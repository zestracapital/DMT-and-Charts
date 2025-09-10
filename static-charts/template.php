<?php
/**
 * Static Charts Template
 * Variables available: $indicators (CSV list), $chart_type
 */
?>
<canvas id="static-chart-<?php echo uniqid(); ?>"></canvas>
<script>
jQuery(function($){
    var id = $('canvas').last().attr('id');
    if(typeof drawStaticChart==='function'){
        drawStaticChart({
            canvasId: id,
            indicators: "<?php echo esc_js($indicators); ?>".split(','),
            type: "<?php echo esc_js($chart_type); ?>"
        });
    }
});
</script>