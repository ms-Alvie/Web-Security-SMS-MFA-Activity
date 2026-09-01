<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['pending_user_id'])) {
    header('Location: login.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $otp_input = trim($_POST['otp'] ?? '');
    $user_id = $_SESSION['pending_user_id'];
    $device_id = $_SESSION['pending_device_id'];
    
    if (empty($otp_input)) {
        $error = 'Please enter the OTP!';
    } else {
        if (verifyOTP($user_id, $otp_input, $device_id, $pdo)) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_name'] = $_SESSION['pending_user_name'];
            $_SESSION['user_phone'] = $_SESSION['pending_user_phone'];
            
            unset($_SESSION['pending_user_id']);
            unset($_SESSION['pending_user_name']);
            unset($_SESSION['pending_user_phone']);
            unset($_SESSION['pending_device_id']);
            unset($_SESSION['pending_session_id']);
            
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Invalid OTP! Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - Secure Login System</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .otp-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .otp-input {
            text-align: center;
            letter-spacing: 10px;
            font-size: 24px;
            padding: 15px;
        }
        .timer {
            text-align: center;
            color: #666;
            margin: 15px 0;
        }
        .sms-info {
            background: #e3f2fd;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="otp-container">
        <h1>OTP Verification</h1>
        <div class="sms-info">
            An OTP has been sent via SMS to your registered phone number.
            Please enter it below.
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="otp">Enter OTP</label>
                <input type="text" id="otp" name="otp" class="otp-input" maxlength="6" pattern="[0-9]{6}" required placeholder="------">
            </div>
            
            <button type="submit" class="btn" style="width: 100%;">Verify OTP</button>
        </form>
        
        <div class="timer">
            <p>OTP expires in <span id="timer"><?php echo $_ENV['OTP_EXPIRY_MINUTES'] * 60; ?></span> seconds</p>
            <a href="resend-otp.php">Resend OTP</a>
        </div>
        
        <p class="text-center"><a href="login.php">Back to Login</a></p>
    </div>
    
    <script>
        let timeLeft = <?php echo $_ENV['OTP_EXPIRY_MINUTES'] * 60; ?>;
        const timerElement = document.getElementById('timer');
        
        const countdown = setInterval(() => {
            timeLeft--;
            timerElement.textContent = timeLeft;
            
            if (timeLeft <= 0) {
                clearInterval(countdown);
                timerElement.textContent = '0';
                document.querySelector('button[type="submit"]').disabled = true;
                document.querySelector('button[type="submit"]').textContent = 'OTP Expired';
            }
        }, 1000);
    </script>
</body>
</html>
