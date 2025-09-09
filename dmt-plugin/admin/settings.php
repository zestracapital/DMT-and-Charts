<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="wrap">
    <h1><?php _e( 'DMT Settings', 'zc-dmt' ); ?></h1>
    <form method="post" action="options.php">
        <?php settings_fields( 'zc_dmt_settings' ); ?>
        <?php do_settings_sections( 'zc_dmt_settings' ); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php _e( 'FRED API Key', 'zc-dmt' ); ?></th>
                <td>
                    <input type="text" name="zc_dmt_fred_api_key" value="<?php echo esc_attr( get_option('zc_dmt_fred_api_key') ); ?>" class="regular-text" />
                    <p class="description"><?php _e('Enter your FRED API key from https://fred.stlouisfed.org/', 'zc-dmt'); ?></p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e( 'Auto Sync', 'zc-dmt' ); ?></th>
                <td>
                    <label><input type="checkbox" name="zc_dmt_auto_sync" value="1" <?php checked(1, get_option('zc_dmt_auto_sync'), true); ?> /> <?php _e('Enable automatic daily sync of active sources', 'zc-dmt'); ?></label>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e( 'Sync Frequency', 'zc-dmt' ); ?></th>
                <td>
                    <select name="zc_dmt_sync_frequency">
                        <?php $freqs = ['hourly','twicedaily','daily']; foreach($freqs as $f): ?>
                            <option value="<?php echo $f; ?>" <?php selected($f, get_option('zc_dmt_sync_frequency')); ?>><?php echo ucfirst($f); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php _e('Frequency for auto sync: hourly, twice daily, or daily.', 'zc-dmt'); ?></p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e( 'Data Retention (days)', 'zc-dmt' ); ?></th>
                <td>
                    <input type="number" name="zc_dmt_data_retention" value="<?php echo esc_attr( get_option('zc_dmt_data_retention', 365) ); ?>" class="small-text" min="30" />
                    <p class="description"><?php _e('Number of days to retain historical data before cleanup.', 'zc-dmt'); ?></p>
                </td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>
</div>
