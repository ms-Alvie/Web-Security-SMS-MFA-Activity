<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $phone_number = trim($_POST['phone_number'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($phone_number) || empty($password)) {
        $error = 'Please enter phone number and password!';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE phone_number = ?");
        $stmt->execute([$phone_number]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $device = getDeviceInfo();
            
            if (isTrustedDevice($user['id'], $device['id'], $pdo)) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_phone'] = $user['phone_number'];
                header('Location: dashboard.php');
                exit();
            } else {
                $_SESSION['pending_user_id'] = $user['id'];
                $_SESSION['pending_user_name'] = $user['full_name'];
                $_SESSION['pending_user_phone'] = $user['phone_number'];
                $_SESSION['pending_device_id'] = $device['id'];
                
                $otp = generateOTP();
                
                saveOTP($user['id'], $otp, $device['id'], $pdo);
                $session_id = saveLoginSession($user['id'], $device, $pdo);
                $_SESSION['pending_session_id'] = $session_id;
                
                if (sendOTPSMS($user['phone_number'], $otp, $user['full_name'])) {
                    header('Location: verify-otp.php');
                    exit();
                } else {
                    $error = 'Failed to send OTP. Please try again.';
                }
            }
        } else {
            $error = 'Invalid phone number or password!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Secure Login System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>🔐 Welcome Back</h1>
        <p class="subtitle">Sign in to continue to your account</p>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <strong>⚠️ Error:</strong> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" id="loginForm">
            <div class="form-group">
                <label for="phone_number">📱 Phone Number</label>
                <input 
                    type="tel" 
                    id="phone_number" 
                    name="phone_number" 
                    placeholder="+639171234567"
                    required
                    pattern="\+?[0-9]{10,15}"
                    title="Enter phone number in international format"
                >
            </div>
            
            <div class="form-group">
                <label for="password">🔒 Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="Enter your password"
                    required
                    minlength="8"
                >
            </div>
            
            <button type="submit" class="btn" id="loginBtn">
                Login
            </button>
        </form>
        
        <p class="text-center">
            Don't have an account? <a href="register.php">Create one here</a>
        </p>
    </div>
    
    <script>
        // Add loading state to button on submit
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('loginBtn');
            btn.classList.add('loading');
            btn.disabled = true;
        });
    </script>
</body>
</html>
