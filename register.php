<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($full_name) || empty($phone_number) || empty($password)) {
        $error = 'All fields are required!';
    } elseif (!preg_match('/^\+?[0-9]{10,15}$/', $phone_number)) {
        $error = 'Invalid phone number format! Use international format (e.g., +1234567890)';
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format!';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters!';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match!';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE phone_number = ?");
        $stmt->execute([$phone_number]);
        
        if ($stmt->rowCount() > 0) {
            $error = 'Phone number already registered!';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (full_name, phone_number, email, password) VALUES (?, ?, ?, ?)");
            
            if ($stmt->execute([$full_name, $phone_number, $email, $hashed_password])) {
                $success = 'Registration successful! <a href="login.php">Login here</a>';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Secure Login System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>📝 Create Account</h1>
        <p class="subtitle">Join us today - it only takes a minute</p>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <strong>⚠️ Error:</strong> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <strong>✅ Success!</strong> <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" id="registerForm">
            <div class="form-group">
                <label for="full_name">👤 Full Name</label>
                <input 
                    type="text" 
                    id="full_name" 
                    name="full_name" 
                    placeholder="John Doe"
                    required
                    value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>"
                >
            </div>
            
            <div class="form-group">
                <label for="phone_number">📱 Phone Number</label>
                <input 
                    type="tel" 
                    id="phone_number" 
                    name="phone_number" 
                    placeholder="+639171234567"
                    required 
                    pattern="\+?[0-9]{10,15}"
                    title="Use international format: +[country code][number]"
                    value="<?php echo isset($_POST['phone_number']) ? htmlspecialchars($_POST['phone_number']) : ''; ?>"
                >
                <small style="color: #666; font-size: 12px;">Format: +[country code][your number]</small>
            </div>
            
            <div class="form-group">
                <label for="email">📧 Email Address (Optional)</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email"
                    placeholder="john@example.com"
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                >
            </div>
            
            <div class="form-group">
                <label for="password">🔒 Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="Minimum 8 characters"
                    required 
                    minlength="8"
                >
                <div class="password-strength" id="passwordStrength" style="display: none;">
                    <div class="password-strength-bar" id="strengthBar"></div>
                </div>
                <small id="strengthText" style="font-size: 12px; display: none;"></small>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">🔒 Confirm Password</label>
                <input 
                    type="password" 
                    id="confirm_password" 
                    name="confirm_password" 
                    placeholder="Re-enter your password"
                    required
                >
            </div>
            
            <button type="submit" class="btn" id="registerBtn">
                Create Account
            </button>
        </form>
        
        <p class="text-center">
            Already have an account? <a href="login.php">Sign in here</a>
        </p>
    </div>
    
    <script>
        // Password strength indicator
        const password = document.getElementById('password');
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');
        const strengthContainer = document.getElementById('passwordStrength');
        
        password.addEventListener('input', function() {
            const val = this.value;
            const length = val.length;
            
            if (length === 0) {
                strengthContainer.style.display = 'none';
                strengthText.style.display = 'none';
                return;
            }
            
            strengthContainer.style.display = 'block';
            strengthText.style.display = 'block';
            
            let strength = 0;
            if (length >= 8) strength++;
            if (/[a-z]/.test(val) && /[A-Z]/.test(val)) strength++;
            if (/[0-9]/.test(val)) strength++;
            if (/[^a-zA-Z0-9]/.test(val)) strength++;
            
            strengthBar.className = 'password-strength-bar';
            if (strength <= 1) {
                strengthBar.classList.add('strength-weak');
                strengthText.textContent = '💪 Weak password';
                strengthText.style.color = '#e74c3c';
            } else if (strength <= 2) {
                strengthBar.classList.add('strength-medium');
                strengthText.textContent = '👍 Medium password';
                strengthText.style.color = '#f39c12';
            } else {
                strengthBar.classList.add('strength-strong');
                strengthText.textContent = '🔥 Strong password';
                strengthText.style.color = '#27ae60';
            }
        });
        
        // Form validation
        const form = document.getElementById('registerForm');
        const confirmPassword = document.getElementById('confirm_password');
        
        form.addEventListener('submit', function(e) {
            if (password.value !== confirmPassword.value) {
                e.preventDefault();
                alert('❌ Passwords do not match!');
                return false;
            }
            
            // Add loading state
            const btn = document.getElementById('registerBtn');
            btn.classList.add('loading');
            btn.disabled = true;
        });
        
        // Real-time password match indicator
        confirmPassword.addEventListener('input', function() {
            if (this.value && password.value !== this.value) {
                this.style.borderColor = '#e74c3c';
            } else if (this.value && password.value === this.value) {
                this.style.borderColor = '#27ae60';
            } else {
                this.style.borderColor = '#e1e1e1';
            }
        });
    </script>
</body>
</html>
