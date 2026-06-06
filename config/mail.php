<?php
// Email configuration using PHPMailer + Gmail SMTP
// To install PHPMailer, run: composer require phpmailer/phpmailer
// Or download from: https://github.com/PHPMailer/PHPMailer

define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'your-email@gmail.com'); // CHANGE THIS
define('MAIL_PASSWORD', 'your-app-password');     // CHANGE THIS - use Gmail App Password
define('MAIL_FROM', 'your-email@gmail.com');      // CHANGE THIS
define('MAIL_FROM_NAME', 'Mindalano Specialist Hospital');
