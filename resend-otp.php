<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['pending_user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['pending_user_id'];
$device_id = $_SESSION['pending_device_id'];

$stmt = $pdo->prepare("SELECT phone_number, full_name FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($user) {
    $otp = generateOTP();
    saveOTP($user_id, $otp, $device_id, $pdo);
    
    if (sendOTPSMS($user['phone_number'], $otp, $user['full_name'])) {
        $_SESSION['otp_message'] = 'New OTP has been sent to your phone.';
    } else {
        $_SESSION['otp_error'] = 'Failed to send OTP. Please try again.';
    }
}

header('Location: verify-otp.php');
exit();
