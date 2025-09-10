<?php
if(!defined('ABSPATH')) exit;
?>
<div class="wrap">
    <h1><?php _e('Data Sources Management','zc-dmt'); ?></h1>
    <?php settings_errors(); ?>
    <form method="post" action="">
        <?php wp_nonce_field('zc_dmt_save_source','zc_dmt_nonce'); ?>
        <table class="form-table">
            <tr valign="top">
                <th><?php _e('FRED API Key','zc-dmt'); ?></th>
                <td>
                    <input type="text" name="zc_dmt_fred_api_key" value="<?php echo esc_attr(get_option('zc_dmt_fred_api_key')); ?>" class="regular-text" />
                    <p class="description"><?php _e('Enter your FRED API key.','zc-dmt'); ?></p>
                </td>
            </tr>
        </table>
        <?php submit_button(__('Save API Key','zc-dmt')); ?>
    </form>
</div>