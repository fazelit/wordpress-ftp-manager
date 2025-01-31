<?php
if (!defined('ABSPATH')) exit;

function ftp_upload_manager_menu() {
    add_options_page('تنظیمات FTP', 'مدیریت FTP', 'manage_options', 'ftp-upload-manager', 'ftp_upload_manager_settings_page');
}
add_action('admin_menu', 'ftp_upload_manager_menu');

function ftp_upload_manager_settings_page() {
    if (!current_user_can('manage_options')) return;

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        update_option('ftp_server', sanitize_text_field($_POST['ftp_server']));
        update_option('ftp_port', sanitize_text_field($_POST['ftp_port']));
        update_option('ftp_user', sanitize_text_field($_POST['ftp_user']));
        update_option('ftp_pass', sanitize_text_field($_POST['ftp_pass']));
        update_option('ftp_upload_path', sanitize_text_field($_POST['ftp_upload_path']));
        update_option('ftp_upload_url', sanitize_text_field($_POST['ftp_upload_url']));
        echo '<div class="updated"><p>تنظیمات ذخیره شد.</p></div>';
    }

    $ftp_server = get_option('ftp_server', '');
    $ftp_port = get_option('ftp_port', '21');
    $ftp_user = get_option('ftp_user', '');
    $ftp_pass = get_option('ftp_pass', '');
    $ftp_upload_path = get_option('ftp_upload_path', '');
    $ftp_upload_url = get_option('ftp_upload_url', '');

    echo '<div class="wrap">
            <h2>تنظیمات FTP</h2>
            <form method="post">
                <table class="form-table">
                    <tr><th>آدرس سرور FTP</th><td><input type="text" name="ftp_server" value="' . esc_attr($ftp_server) . '" class="regular-text"></td></tr>
                    <tr><th>پورت سرور FTP</th><td><input type="text" name="ftp_port" value="' . esc_attr($ftp_port) . '" class="regular-text"></td></tr>
                    <tr><th>نام کاربری</th><td><input type="text" name="ftp_user" value="' . esc_attr($ftp_user) . '" class="regular-text"></td></tr>
                    <tr><th>رمز عبور</th><td><input type="password" name="ftp_pass" value="' . esc_attr($ftp_pass) . '" class="regular-text"></td></tr>
                    <tr><th>مسیر آپلود</th><td><input type="text" name="ftp_upload_path" value="' . esc_attr($ftp_upload_path) . '" class="regular-text"></td></tr>
                    <tr><th>آدرس دانلود فایل‌ها</th><td><input type="text" name="ftp_upload_url" value="' . esc_attr($ftp_upload_url) . '" class="regular-text"></td></tr>
                </table>
                <p><input type="submit" class="button-primary" value="ذخیره تنظیمات"></p>
            </form>
          </div>';
}
?>
