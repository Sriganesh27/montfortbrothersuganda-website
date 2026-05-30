<?php
// web/api/mail_helper.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require __DIR__ . '/PHPMailer/Exception.php';
require __DIR__ . '/PHPMailer/PHPMailer.php';
require __DIR__ . '/PHPMailer/SMTP.php';

function send_custom_mail($to, $subject, $message) {
    $env_path = __DIR__ . '/../.env';
    $env = [];
    if (file_exists($env_path)) {
        $env = @parse_ini_file($env_path);
    } else {
        error_log("CRITICAL ERROR: .env file not found at: " . realpath($env_path));
    }

    $mail = new PHPMailer(true);

    try {
        // FIX: Disabled debugging so it stops breaking your redirects and JSON responses
        $mail->SMTPDebug = 0; 
        
        $mail->isSMTP();
        $mail->Host       = $env['SMTP_HOST'] ?? 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $env['SMTP_USER'] ?? 'uniteallforpeace@gmail.com';
        $mail->Password   = $env['SMTP_PASS'] ?? ''; 
        
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = $env['SMTP_PORT'] ?? 465;

        // FIX: Removed the insecure SMTPOptions block entirely. 
        // PHPMailer will now use strict, secure SSL verification by default.

        $mail->setFrom($mail->Username, 'Montfort Brothers');
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        
        $template = "
        <html>
        <body style='font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;'>
            <div style='max-width: 600px; margin: auto; background: white; padding: 20px; border-radius: 8px; border: 1px solid #ddd;'>
                <h2 style='color: #004b87; border-bottom: 2px solid #004b87; padding-bottom: 10px;'>Montfort Brothers</h2>
                <div style='font-size: 16px; line-height: 1.6; color: #333;'>
                    $message
                </div>
            </div>
        </body>
        </html>";

        $mail->Body = $template;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $message));

        return $mail->send();

    } catch (Exception $e) {
        error_log("PHPMailer Error for {$to}: {$mail->ErrorInfo}");
        return false;
    }
}
?>