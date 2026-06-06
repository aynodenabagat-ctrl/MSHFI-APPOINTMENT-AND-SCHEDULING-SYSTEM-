<?php
session_start();

define('BASE_URL', 'http://localhost/capstone');
define('APP_NAME', 'Mindalano Specialist Hospital - Appointment System');

define('UPLOAD_PATH', __DIR__ . '/../uploads/appointments/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('MAX_IMAGES_PER_APPOINTMENT', 5);

date_default_timezone_set('Asia/Manila');
