<?php
if (!defined('WP_UNINSTALL_PLUGIN')) exit;
delete_option('ftp_server');
delete_option('ftp_port');
delete_option('ftp_user');
delete_option('ftp_pass');
delete_option('ftp_upload_path');
delete_option('ftp_upload_url');
?>
