<?php
session_start();

$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
$projectRoot = str_replace('\\', '/', dirname(__DIR__));
$docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? $projectRoot);
$basePath = str_starts_with($projectRoot, $docRoot) ? substr($projectRoot, strlen($docRoot)) : '';
define('BASE_URL', $baseUrl . $basePath);
define('APP_NAME', 'Mindalano Specialist Hospital - Appointment System');

define('UPLOAD_PATH', __DIR__ . '/../uploads/appointments/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('MAX_IMAGES_PER_APPOINTMENT', 5);

date_default_timezone_set('Asia/Manila');
