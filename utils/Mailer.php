<?php
require_once 'utils/Session.php';
require_once 'utils/Validator.php';
require_once 'utils/Security.php';
require_once 'utils/Mailer.php';  // Make sure this is included

// Check and load Composer's autoloader if available
if (file_exists(ROOT_PATH . '/vendor/autoload.php')) {
    require_once ROOT_PATH . '/vendor/autoload.php';
}

// Always declare the namespace aliases at the file level
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if PHPMailer is available, otherwise use default email functions
function hasPhpMailer() {
    return class_exists('PHPMailer\PHPMailer\PHPMailer');
}

// Send email with PHPMailer if available
function sendEmailWithPhpMailer($to, $subject, $message) {
    try {
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = MAIL_ENCRYPTION;
        $mail->Port       = MAIL_PORT;
        
        // Recipients
        $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
        $mail->addAddress($to);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->AltBody = strip_tags($message);
        
        return $mail->send();
    } catch (Exception $e) {
        // Log error
        error_log('Mailer Error: ' . $mail->ErrorInfo);
        return false;
    }
}

// Send activation email
function sendActivationEmail($user_email, $user_name, $activation_token) {
    $activation_link = BASE_URL . '/activate?token=' . $activation_token . '&email=' . urlencode($user_email);
    
    $subject = "Activate Your Account - " . APP_NAME;
    
    $message = "
    <html>
    <head>
        <title>Account Activation</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .button { display: inline-block; padding: 10px 20px; background-color: #4CAF50; color: white; 
                     text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { margin-top: 30px; font-size: 12px; color: #777; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h2>Welcome to " . APP_NAME . "</h2>
            <p>Hello " . htmlspecialchars($user_name) . ",</p>
            <p>Thank you for registering. Please click the button below to activate your account:</p>
            <p><a href='" . $activation_link . "' class='button'>Activate Your Account</a></p>
            <p>If the button doesn't work, copy and paste this URL into your browser:</p>
            <p>" . $activation_link . "</p>
            <p>This link will expire in " . (ACTIVATION_TOKEN_EXPIRY / 3600) . " hours.</p>
            <div class='footer'>
                <p>Best regards,<br>The " . APP_NAME . " Team</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Use PHPMailer if available, otherwise use default function
    if (hasPhpMailer()) {
        return sendEmailWithPhpMailer($user_email, $subject, $message);
    } else {
        return sendEmail($user_email, $subject, $message);
    }
}

// Send password reset email
function sendPasswordResetEmail($user_email, $user_name, $reset_token) {
    $reset_link = BASE_URL . '/reset-password?token=' . $reset_token . '&email=' . urlencode($user_email);
    
    $subject = "Password Reset - " . APP_NAME;
    
    $message = "
    <html>
    <head>
        <title>Password Reset</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .button { display: inline-block; padding: 10px 20px; background-color: #4CAF50; color: white; 
                     text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { margin-top: 30px; font-size: 12px; color: #777; }
            .warning { color: #ff6600; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h2>" . APP_NAME . " - Password Reset</h2>
            <p>Hello " . htmlspecialchars($user_name) . ",</p>
            <p>You requested a password reset. Please click the button below to reset your password:</p>
            <p><a href='" . $reset_link . "' class='button'>Reset Your Password</a></p>
            <p>If the button doesn't work, copy and paste this URL into your browser:</p>
            <p>" . $reset_link . "</p>
            <p>This link will expire in " . (RESET_TOKEN_EXPIRY / 3600) . " hour(s).</p>
            <p class='warning'>If you didn't request this password reset, please ignore this email or contact support if you're concerned.</p>
            <div class='footer'>
                <p>Best regards,<br>The " . APP_NAME . " Team</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Use PHPMailer if available, otherwise use default function
    if (hasPhpMailer()) {
        return sendEmailWithPhpMailer($user_email, $subject, $message);
    } else {
        return sendEmail($user_email, $subject, $message);
    }
}

// Send OTP email for password reset
function sendOTPEmail($user_email, $user_name, $otp) {
    $subject = "Password Reset OTP - " . APP_NAME;
    
    $message = "
    <html>
    <head>
        <title>Password Reset OTP</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .otp-code { font-size: 24px; font-weight: bold; letter-spacing: 5px; margin: 20px 0; 
                       padding: 10px; background-color: #f5f5f5; text-align: center; }
            .footer { margin-top: 30px; font-size: 12px; color: #777; }
            .warning { color: #ff6600; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h2>" . APP_NAME . " - Password Reset</h2>
            <p>Hello " . htmlspecialchars($user_name) . ",</p>
            <p>You requested a password reset. Please use the following verification code (OTP):</p>
            <div class='otp-code'>" . $otp . "</div>
            <p>This code will expire in 1 hour.</p>
            <p class='warning'>If you didn't request this password reset, please ignore this email or contact support if you're concerned.</p>
            <div class='footer'>
                <p>Best regards,<br>The " . APP_NAME . " Team</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Use PHPMailer if available, otherwise use default function
    if (hasPhpMailer()) {
        return sendEmailWithPhpMailer($user_email, $subject, $message);
    } else {
        return sendEmail($user_email, $subject, $message);
    }
}

// Add these functions to utils/Mailer.php

// Send email notification for shared note
function sendNoteSharedEmail($recipient_email, $recipient_name, $owner_name, $note_title, $permission_type) {
    $subject = "Note Shared With You - " . APP_NAME;
    
    $message = "
    <html>
    <head>
        <title>Note Shared With You</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .button { display: inline-block; padding: 10px 20px; background-color: #4a89dc; color: white; 
                     text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { margin-top: 30px; font-size: 12px; color: #777; }
            .permission { font-weight: bold; color: " . ($permission_type == 'edit' ? '#4a89dc' : '#6c757d') . "; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h2>Note Shared With You</h2>
            <p>Hello " . htmlspecialchars($recipient_name) . ",</p>
            <p><strong>" . htmlspecialchars($owner_name) . "</strong> has shared a note with you:</p>
            <p style='font-size: 18px; padding: 10px; background-color: #f8f9fa; border-left: 4px solid #4a89dc;'>\"" . htmlspecialchars($note_title) . "\"</p>
            <p>You have <span class='permission'>" . ($permission_type == 'edit' ? 'edit' : 'view-only') . "</span> access to this note.</p>
            <p>Log in to " . APP_NAME . " to access this shared note:</p>
            <p><a href='" . BASE_URL . "/notes/shared' class='button'>View Shared Notes</a></p>
            <div class='footer'>
                <p>Best regards,<br>The " . APP_NAME . " Team</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Use PHPMailer if available, otherwise use default function
    if (hasPhpMailer()) {
        return sendEmailWithPhpMailer($recipient_email, $subject, $message);
    } else {
        return sendEmail($recipient_email, $subject, $message);
    }
}

// Send email notification for share permission changes
function sendSharePermissionChangedEmail($recipient_email, $recipient_name, $owner_name, $note_title, $new_permission) {
    $subject = "Note Sharing Permissions Updated - " . APP_NAME;
    
    $message = "
    <html>
    <head>
        <title>Note Sharing Permissions Updated</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .button { display: inline-block; padding: 10px 20px; background-color: #4a89dc; color: white; 
                     text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { margin-top: 30px; font-size: 12px; color: #777; }
            .permission { font-weight: bold; color: " . ($new_permission == 'edit' ? '#4a89dc' : '#6c757d') . "; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h2>Note Sharing Permissions Updated</h2>
            <p>Hello " . htmlspecialchars($recipient_name) . ",</p>
            <p><strong>" . htmlspecialchars($owner_name) . "</strong> has updated your access permissions for the following note:</p>
            <p style='font-size: 18px; padding: 10px; background-color: #f8f9fa; border-left: 4px solid #4a89dc;'>\"" . htmlspecialchars($note_title) . "\"</p>
            <p>Your new permission level is: <span class='permission'>" . ($new_permission == 'edit' ? 'Edit Access' : 'View-Only Access') . "</span></p>
            <p>Log in to " . APP_NAME . " to access this shared note:</p>
            <p><a href='" . BASE_URL . "/notes/shared' class='button'>View Shared Notes</a></p>
            <div class='footer'>
                <p>Best regards,<br>The " . APP_NAME . " Team</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Use PHPMailer if available, otherwise use default function
    if (hasPhpMailer()) {
        return sendEmailWithPhpMailer($recipient_email, $subject, $message);
    } else {
        return sendEmail($recipient_email, $subject, $message);
    }
}

// Helper function to use plain PHP mail function when PHPMailer is not available
function sendEmail($to, $subject, $message) {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: " . MAIL_FROM_NAME . " <" . MAIL_FROM_ADDRESS . ">" . "\r\n";
    
    return mail($to, $subject, $message, $headers);
}