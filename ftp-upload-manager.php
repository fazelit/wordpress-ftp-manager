<?php
/*
Plugin Name: FTP Upload Manager Media
Plugin URI: https://github.com/fazelit/wordpress-ftp-manager
Description: آپلود رسانه‌ها در سرور FTP به جای هاست محلی.
Version: 1.0.0
Author: ابوفاضل فولادی
Author URI: https://yektadg.com/
License: GPL2
*/

if (!defined('ABSPATH')) exit; // جلوگیری از دسترسی مستقیم

// تعریف ثابت‌های FTP از wp-config.php
if (!defined('FTP_SERVER')) define('FTP_SERVER', get_option('ftp_server'));
if (!defined('FTP_PORT')) define('FTP_PORT', get_option('ftp_port'));
if (!defined('FTP_USER')) define('FTP_USER', get_option('ftp_user'));
if (!defined('FTP_PASS')) define('FTP_PASS', get_option('ftp_pass'));
if (!defined('FTP_UPLOAD_PATH')) define('FTP_UPLOAD_PATH', get_option('ftp_upload_path'));
if (!defined('FTP_UPLOAD_URL')) define('FTP_UPLOAD_URL', get_option('ftp_upload_url'));

// افزودن صفحه تنظیمات
require_once plugin_dir_path(__FILE__) . 'admin/settings.php';

// افزودن توابع آپلود
require_once plugin_dir_path(__FILE__) . 'includes/functions.php';

// فعال‌سازی افزونه
function ftp_upload_manager_activate() { }
register_activation_hook(__FILE__, 'ftp_upload_manager_activate');

// غیرفعال‌سازی افزونه
function ftp_upload_manager_deactivate() { }
register_deactivation_hook(__FILE__, 'ftp_upload_manager_deactivate');
?>
