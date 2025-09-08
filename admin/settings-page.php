<?php
/**
 * Admin Settings Page
 */
global $wpdb;
$table_settings = $wpdb->prefix . 'itsi_invoice_settings';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_admin_referer('itsi_save_settings')) {
    foreach ($_POST['itsi'] as $key => $value) {
        $wpdb->replace($table_settings, [
            'option_name'  => sanitize_text_field($key),
            'option_value' => sanitize_textarea_field($value),
        ]);
    }
    echo '<div class="updated"><p>Settings saved.</p></div>';
}

$settings = $wpdb->get_results("SELECT option_name, option_value FROM $table_settings", OBJECT_K);
?>

<div class="wrap">
    <h1>Invoice Settings</h1>
    <form method="post">
        <?php wp_nonce_field('itsi_save_settings'); ?>
        <table class="form-table">
            <tr>
                <th>Invoice Prefix</th>
                <td><input type="text" name="itsi[invoice_prefix]" value="<?php echo esc_attr($settings['invoice_prefix']->option_value ?? 'INV-'); ?>" /></td>
            </tr>
            <tr>
                <th>Start Number</th>
                <td><input type="number" name="itsi[invoice_start]" value="<?php echo esc_attr($settings['invoice_start']->option_value ?? '1000'); ?>" /></td>
            </tr>
            <tr>
                <th>Company Name</th>
                <td><input type="text" name="itsi[company_name]" value="<?php echo esc_attr($settings['company_name']->option_value ?? ''); ?>" /></td>
            </tr>
            <tr>
                <th>Company Address</th>
                <td><textarea name="itsi[company_address]"><?php echo esc_textarea($settings['company_address']->option_value ?? ''); ?></textarea></td>
            </tr>
            <tr>
                <th>Company Contact</th>
                <td><input type="text" name="itsi[company_contact]" value="<?php echo esc_attr($settings['company_contact']->option_value ?? ''); ?>" /></td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>
</div>
