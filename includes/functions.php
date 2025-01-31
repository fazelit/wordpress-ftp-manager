<?php
if (!defined('ABSPATH')) exit;


function custom_upload_directory( $uploads ) {
    $uploads['baseurl'] = FTP_UPLOAD_URL; // تغییر آدرس لینک آپلود
    $uploads['url'] = FTP_UPLOAD_URL; // تغییر URL پوشه آپلود
    return $uploads;
}
add_filter( 'upload_dir', 'custom_upload_directory' );

function ftp_create_directory($ftp_conn, $path) {
    $parts = explode("/", $path);
	$ftp_home= ftp_pwd($ftp_conn);
    $full_path = $ftp_home;

    foreach ($parts as $part) {
        if (empty($part)) continue;

        $full_path .= "/$part"; // مسیر را به‌روز کنیم

        // تلاش برای تغییر مسیر، اگر نشد یعنی پوشه وجود ندارد و باید ساخته شود
        if (!@ftp_chdir($ftp_conn, $full_path)) {
            if (!@ftp_mkdir($ftp_conn, $full_path)) {
                error_log("خطا: پوشه '$full_path' ایجاد نشد.");
                return false;
            }
            ftp_chmod($ftp_conn, 0777, $full_path); // تنظیم دسترسی
        }
    }

	ftp_chdir($ftp_conn, $ftp_home);

    return true;
}



function custom_ftp_upload($file) {
    // مشخصات سرور FTP
    $ftp_server = FTP_SERVER; // آدرس سرور FTP
    $ftp_port = FTP_PORT; // پورت FTP (21)
    $ftp_user_name = FTP_USER; // نام کاربری FTP
    $ftp_user_pass = FTP_PASS; // رمز عبور FTP
    $ftp_upload_path = FTP_UPLOAD_PATH;   // مسیر پوشه آپلود در سرور FTP
	$ftp_upload_url = FTP_UPLOAD_URL;

    // اتصال به سرور FTP
    $ftp_conn = ftp_connect($ftp_server, $ftp_port);
    if (!$ftp_conn) {
        $file['error']="خطا: اتصال به سرور FTP امکان‌پذیر نیست.";
        return $file;
    }

    // ورود به سرور FTP
    $login = ftp_login($ftp_conn, $ftp_user_name, $ftp_user_pass);
    if (!$login) {
        $file['error']="خطا: ورود به سرور FTP ناموفق بود.";
        ftp_close($ftp_conn);

        return $file;
    }

    // فعال کردن حالت passive
    ftp_pasv($ftp_conn, true);

    // دریافت مسیر پوشه سال/ماه از وردپرس
    $wp_upload_dir = wp_upload_dir();
    $date_subdir = str_replace(trailingslashit($wp_upload_dir['basedir']), '', dirname($file['file']));

    // مسیر کامل در FTP
    $remote_path = $ftp_upload_path . "/" . $date_subdir;
    $remote_file = $remote_path . "/" . basename($file['file']);

    // ایجاد پوشه‌های لازم در FTP (اگر وجود نداشته باشد)
    $folders = explode('/', $date_subdir);
    $current_path = $ftp_upload_path;
	$file_path = $file['file']; // ذخیره مسیر فایل در یک متغیر مستقل

	if (!ftp_create_directory($ftp_conn, $remote_path)) {
		ftp_close($ftp_conn);

		register_shutdown_function(function() use ($file_path) {
			unlink($file_path);
		});
		$file['error']="مشکل در ایجاد پوشه در سرور ftp";
		return $file;
	}
	

    // آپلود فایل به سرور FTP
    if (ftp_put($ftp_conn, $remote_file, $file['file'], FTP_BINARY)) {
        error_log("فایل با موفقیت به سرور FTP منتقل شد: " . $remote_file);

        // تنظیم آدرس صحیح فایل در هاست دانلود
        $file['url'] = rtrim($ftp_upload_url, '/') . '/' . $date_subdir . '/' . basename($file['file']);
        
        
    } else {
        $file['error']="خطا: آپلود فایل به سرور FTP ناموفق بود.";
		// // حذف اطلاعات فایل از دیتابیس وردپرس
		// unlink($file['file']);
    }

    

		// در اینجا صبر کنید تا وردپرس پردازش‌های لازم را انجام دهد، سپس حذف کنید
		register_shutdown_function(function() use ($file_path) {
			unlink($file_path);
		});

    // بستن اتصال FTP
    ftp_close($ftp_conn);

    return $file;
}
add_filter('wp_handle_upload', 'custom_ftp_upload', 10, 1);

//TODO: مشکل حذف فایل های محلی بعد ارسال و اگر حذف کنیم فایل ها ftp پاک نمی شن
function custom_after_file_upload($attachment_id) {
    // عملیات مورد نظر خود را اینجا انجام دهید.
    // می‌توانید از $attachment_id برای انجام کارهایی مانند تولید اندازه‌های تصویر، تغییر متادیتا و غیره استفاده کنید.

	 // مشخصات سرور FTP
	 $ftp_server = FTP_SERVER; // آدرس سرور FTP
	 $ftp_port = FTP_PORT; // پورت FTP (21)
	 $ftp_user_name = FTP_USER; // نام کاربری FTP
	 $ftp_user_pass = FTP_PASS; // رمز عبور FTP
	 $ftp_upload_path = FTP_UPLOAD_PATH;   // مسیر پوشه آپلود در سرور FTP
	 $ftp_upload_url = FTP_UPLOAD_URL;
 
	 // اتصال به سرور FTP
	 $ftp_conn = ftp_connect($ftp_server, $ftp_port);
	 if (!$ftp_conn) {
		 $file['error']="خطا: اتصال به سرور FTP امکان‌پذیر نیست.";
		 return $file;
	 }
 
	 // ورود به سرور FTP
	 $login = ftp_login($ftp_conn, $ftp_user_name, $ftp_user_pass);
	 if (!$login) {
		 $file['error']="خطا: ورود به سرور FTP ناموفق بود.";
		 ftp_close($ftp_conn);
 
		 return $file;
	 }
 
	 // فعال کردن حالت passive
	 ftp_pasv($ftp_conn, true);


    // اگر شناسه فایل تولید شد، از آن استفاده کنید
    if ($attachment_id) {
        // دریافت مسیر فایل اصلی از طریق شناسه پیوست
        $file_path = get_attached_file($attachment_id);

		 // دریافت مسیر پوشه سال/ماه از وردپرس
		 $wp_upload_dir = wp_upload_dir();
		 $date_subdir = str_replace(trailingslashit($wp_upload_dir['basedir']), '', dirname($file_path));
		 // مسیر کامل در FTP
		 $remote_path = $ftp_upload_path . "/" . $date_subdir;

        // تولید اندازه‌های تصویر
        $metadata = wp_generate_attachment_metadata($attachment_id, $file_path);

        // ذخیره متادیتا در دیتابیس
        wp_update_attachment_metadata($attachment_id, $metadata);

        // اگر متادیتا برای اندازه‌ها موجود است
        if (!empty($metadata['sizes'])) {
            foreach ($metadata['sizes'] as $size) {
                // دریافت مسیر فایل اندازه کوچک‌شده در هاست اصلی
                $size_file = dirname($file_path) . "/" . $size['file'];
                
                // مسیر در سرور FTP (باید این متغیرها به درستی تنظیم شوند)
                $remote_size_file = $remote_path . "/" . $size['file'];
                // آپلود نسخه‌های کوچک‌تر
                if (file_exists($size_file)) {
                    if (ftp_put($ftp_conn, $remote_size_file, $size_file, FTP_BINARY)) {
                        error_log("فایل '{$size_file}' با موفقیت به سرور FTP منتقل شد."); 
                    } else {
                        $file['error'] = "خطا: آپلود فایل '{$size['file']}' ناموفق بود.";
                    }
					register_shutdown_function(function() use ($size_file) {
						 // 🔹 حذف فایل 
						if(unlink($size_file)){
							error_log("فایل '{$size_file}' حذف شد"); 
						}else{
							error_log("فایل '{$size_file}' مشکل در حذف !!!!"); 
						}
					});
					
                }
            }
        }
    } else { 
        $file['error'] = "خطا: شناسه فایل در دیتابیس تولید نشد.";
    }

    // هر عملیات دیگری که مد نظر دارید می‌توانید در اینجا انجام دهید
}

add_action('add_attachment', 'custom_after_file_upload');

// delete
function delete_all_image_sizes_from_ftp($post_id) {
	 // مشخصات سرور FTP
	 $ftp_server = FTP_SERVER; // آدرس سرور FTP
	 $ftp_port = FTP_PORT; // پورت FTP (21)
	 $ftp_user = FTP_USER; // نام کاربری FTP
	 $ftp_pass = FTP_PASS; // رمز عبور FTP
	 $ftp_upload_path = FTP_UPLOAD_PATH;   // مسیر پوشه آپلود در سرور FTP
	 $ftp_upload_url = FTP_UPLOAD_URL;

    // دریافت اطلاعات فایل اصلی
    $meta = wp_get_attachment_metadata($post_id);
    if (!$meta || !isset($meta['file'])) {
        return;
    }

    // دریافت نام فایل اصلی
    $original_file = basename($meta['file']);
	$wp_upload_dir = wp_upload_dir();
	$date_subdir = str_replace(trailingslashit($wp_upload_dir['basedir']), '', dirname($meta['file']));
    $remote_file_path = $ftp_upload_path . "/"  . $date_subdir ;

	$remote_file = $remote_file_path . "/" . $original_file;
	


    // اتصال به سرور FTP
    $ftp_conn = ftp_connect($ftp_server, $ftp_port);
    if (!$ftp_conn) {
        error_log("خطا: اتصال به سرور FTP ناموفق بود.");
        return;
    }

    // ورود به FTP
    $login = ftp_login($ftp_conn, $ftp_user, $ftp_pass);
    if (!$login) {
        error_log("خطا: ورود به FTP انجام نشد.");
        ftp_close($ftp_conn);
        return;
    }

    // حذف فایل اصلی از FTP
    if (ftp_delete($ftp_conn, $remote_file)) {
        error_log("فایل اصلی حذف شد: " . $remote_file);
    } else {
        error_log("خطا: حذف فایل اصلی از FTP ناموفق بود: " . $remote_file);
    }

    // حذف نسخه‌های تغییر اندازه داده شده
    if (isset($meta['sizes'])) {
        foreach ($meta['sizes'] as $size) {
    
			// مسیر در سرور FTP (باید این متغیرها به درستی تنظیم شوند)
			$resized_file = $remote_file_path . "/" . $size['file'];

            if (ftp_delete($ftp_conn, $resized_file)) {
                error_log("نسخه تغییر اندازه داده شده حذف شد: " . $resized_file);
            } else {
                error_log("خطا: حذف نسخه تغییر اندازه داده شده ناموفق بود: " . $resized_file);
            }
        }
    }

    // بستن اتصال FTP
    ftp_close($ftp_conn);
}

// اجرای تابع هنگام حذف یک تصویر از وردپرس
add_action('delete_attachment', 'delete_all_image_sizes_from_ftp');

// حذف فایل از FTP هنگام حذف از وردپرس
function delete_file_from_ftp($file_path) {
	error_log("run delete_file_from_ftp: ". $file_path);
    // اطلاعات سرور FTP
    $ftp_server = FTP_SERVER; // آدرس سرور FTP
    $ftp_port = FTP_PORT; // پورت (معمولاً 21)
    $ftp_user = FTP_USER; // نام کاربری
    $ftp_pass = FTP_PASS; // رمز عبور
    $ftp_upload_path = FTP_UPLOAD_PATH; // مسیر پوشه آپلود در FTP

    // دریافت نام فایل
    $file_name = basename($file_path);
    $wp_upload_dir = wp_upload_dir();
    $date_subdir = str_replace(trailingslashit($wp_upload_dir['basedir']), '', dirname($file_path));
    $remote_file_path = rtrim($ftp_upload_path, '/') . "/" . trim($date_subdir, '/');
    $remote_file = $remote_file_path . "/" . $file_name;

    // اتصال به FTP
    $ftp_conn = ftp_connect($ftp_server, $ftp_port);
    if (!$ftp_conn) {
        error_log("❌ خطا: اتصال به سرور FTP ناموفق بود!");
        return $file_path;
    }

    // ورود به FTP
    $login = ftp_login($ftp_conn, $ftp_user, $ftp_pass);
    if (!$login) {
        error_log("❌ خطا: ورود به سرور FTP انجام نشد!");
        ftp_close($ftp_conn);
        return $file_path;
    }

    // استفاده از حالت Passive برای جلوگیری از مشکلات ارتباطی
    ftp_pasv($ftp_conn, true);

    // حذف فایل از FTP
    if (ftp_delete($ftp_conn, $remote_file)) {
        error_log("✅ فایل از FTP حذف شد: " . $remote_file);
    } else {
        error_log("❌ خطا: حذف فایل از FTP ناموفق بود. مسیر: " . $remote_file);
    }

    // بستن اتصال FTP
    ftp_close($ftp_conn);

    return $file_path;
}

// اجرای تابع هنگام حذف فایل
add_filter('wp_delete_file', 'delete_file_from_ftp');

// اجرای تابع هنگام حذف یک پیوست (برای اطمینان از اجرای حذف)
add_action('delete_attachment', function($post_id) {
    $file = get_attached_file($post_id);
    if ($file) {
        delete_file_from_ftp($file);
    }
});