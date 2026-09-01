<?php
/**
 * Authentication Helper Functions
 * 
 * This file contains all helper functions for:
 * - OTP generation and verification
 * - SMS sending via Telerivet API
 * - Device detection and fingerprinting
 * - Session management
 * 
 * @package SecureLoginSystem
 * @author Your Name
 * @version 1.0.0
 */

// Load database configuration and dependencies
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Generate a random One-Time Password (OTP)
 * 
 * Creates a numeric OTP of specified length, padded with leading zeros if necessary.
 * Default length is 6 digits (e.g., 123456).
 * 
 * @param int $length Length of OTP to generate (default: 6)
 * @return string Generated OTP code
 * 
 * @example
 * $otp = generateOTP(); // Returns something like "042357"
 * $otp = generateOTP(4); // Returns something like "0912"
 */
function generateOTP($length = 6) {
    // Generate random number between 0 and 10^length - 1
    // Then pad with leading zeros to ensure exact length
    return str_pad(rand(0, pow(10, $length)-1), $length, '0', STR_PAD_LEFT);
}

/**
 * Send OTP via SMS using Telerivet API
 * 
 * Sends an SMS message containing the OTP to the specified phone number.
 * Uses Telerivet API credentials from environment variables.
 * 
 * @param string $phone_number Recipient phone number in international format (+country code)
 * @param string $otp The OTP code to send
 * @param string $name Recipient's name for personalization
 * @return bool True if SMS sent successfully, false otherwise
 * 
 * @throws Exception If Telerivet API call fails
 * 
 * @example
 * $success = sendOTPSMS('+639171234567', '123456', 'John Doe');
 */
function sendOTPSMS($phone_number, $otp, $name) {
    try {
        // Retrieve API credentials from environment variables
        $api_key = $_ENV['TELERIVET_API_KEY'];
        $project_id = $_ENV['TELERIVET_PROJECT_ID'];
        
        // Initialize Telerivet API client
        $telerivet = new \Telerivet_API($api_key);
        $project = $telerivet->initProjectById($project_id);
        
        // Construct personalized SMS message
        $message = "Hello " . $name . ", your OTP for " . $_ENV['APP_NAME'] . " is: " . $otp . ". This OTP will expire in " . $_ENV['OTP_EXPIRY_MINUTES'] . " minutes.";
        
        // Send SMS message
        $result = $project->sendMessage(array(
            'to_number' => $phone_number,
            'content' => $message
        ));
        
        return true;
    } catch (Exception $e) {
        // Log error for debugging (check PHP error log)
        error_log("SMS sending failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Get device information for fingerprinting
 * 
 * Creates a unique device identifier based on user agent and IP address.
 * This helps track login sessions and implement trusted device functionality.
 * 
 * @return array Associative array containing:
 *               - id: SHA256 hash of user agent + IP (device fingerprint)
 *               - type: Device type (Mobile, Tablet, or Desktop)
 *               - ip: IP address
 *               - user_agent: Full user agent string
 * 
 * @example
 * $device = getDeviceInfo();
 * echo $device['type']; // "Mobile"
 * echo $device['id'];   // "a1b2c3..."
 */
function getDeviceInfo() {
    // Get user agent string (browser/device information)
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // Get IP address (handle proxies and load balancers)
    $ip = $_SERVER['HTTP_CLIENT_IP'] 
       ?? $_SERVER['HTTP_X_FORWARDED_FOR'] 
       ?? $_SERVER['REMOTE_ADDR'] 
       ?? '';
    
    // Create unique device fingerprint using SHA256 hash
    $device_id = hash('sha256', $user_agent . $ip);
    
    // Detect device type based on user agent string
    $device_type = 'Unknown';
    if (strpos($user_agent, 'Mobile') !== false || strpos($user_agent, 'Android') !== false) {
        $device_type = 'Mobile';
    } elseif (strpos($user_agent, 'Tablet') !== false) {
        $device_type = 'Tablet';
    } else {
        $device_type = 'Desktop';
    }
    
    return [
        'id' => $device_id,
        'type' => $device_type,
        'ip' => $ip,
        'user_agent' => $user_agent
    ];
}

/**
 * Check if device is trusted for a user
 * 
 * Verifies if the current device has been previously verified by this user.
 * Trusted devices can skip OTP verification on subsequent logins.
 * 
 * @param int $user_id Database ID of the user
 * @param string $device_id Device fingerprint hash
 * @param PDO $pdo Database connection object
 * @return bool True if device is trusted, false otherwise
 * 
 * @example
 * if (isTrustedDevice($user_id, $device_id, $pdo)) {
 *     // Skip OTP verification
 * }
 */
function isTrustedDevice($user_id, $device_id, $pdo) {
    // Query for verified login session with matching user and device
    $stmt = $pdo->prepare("SELECT * FROM login_sessions WHERE user_id = ? AND device_info = ? AND is_verified = 1");
    $stmt->execute([$user_id, $device_id]);
    
    // Return true if at least one verified session exists
    return $stmt->rowCount() > 0;
}

/**
 * Save a new login session to database
 * 
 * Records a login attempt with device information for audit trail and
 * trusted device management.
 * 
 * @param int $user_id Database ID of the user
 * @param array $device_info Device information array from getDeviceInfo()
 * @param PDO $pdo Database connection object
 * @return int ID of the created login session
 * 
 * @example
 * $session_id = saveLoginSession($user_id, $device, $pdo);
 */
function saveLoginSession($user_id, $device_info, $pdo) {
    // Insert new login session record
    $stmt = $pdo->prepare("INSERT INTO login_sessions (user_id, device_info, ip_address, user_agent) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $device_info['id'], $device_info['ip'], $device_info['user_agent']]);
    
    // Return the auto-generated session ID
    return $pdo->lastInsertId();
}

/**
 * Save OTP code to database
 * 
 * Stores the generated OTP with expiration time for later verification.
 * 
 * @param int $user_id Database ID of the user
 * @param string $otp The OTP code to save
 * @param string $device_id Device fingerprint hash
 * @param PDO $pdo Database connection object
 * @return int ID of the created OTP record
 * 
 * @example
 * $otp_id = saveOTP($user_id, '123456', $device_id, $pdo);
 */
function saveOTP($user_id, $otp, $device_id, $pdo) {
    // Calculate expiration time based on configured minutes
    $expires_at = date('Y-m-d H:i:s', strtotime('+' . $_ENV['OTP_EXPIRY_MINUTES'] . ' minutes'));
    
    // Insert OTP record
    $stmt = $pdo->prepare("INSERT INTO otp_codes (user_id, otp_code, device_id, expires_at) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $otp, $device_id, $expires_at]);
    
    // Return the auto-generated OTP record ID
    return $pdo->lastInsertId();
}

/**
 * Verify OTP code entered by user
 * 
 * Validates the OTP code against database records, checking:
 * - User ID matches
 * - OTP code matches
 * - Device ID matches
 * - OTP hasn't been used already
 * - OTP hasn't expired
 * 
 * If valid, marks the OTP as used and marks the login session as verified.
 * 
 * @param int $user_id Database ID of the user
 * @param string $otp The OTP code entered by user
 * @param string $device_id Device fingerprint hash
 * @param PDO $pdo Database connection object
 * @return bool True if OTP is valid, false otherwise
 * 
 * @example
 * if (verifyOTP($user_id, $_POST['otp'], $device_id, $pdo)) {
 *     // OTP is valid, grant access
 * }
 */
function verifyOTP($user_id, $otp, $device_id, $pdo) {
    // Query for matching OTP (excluding expired check for now)
    $stmt = $pdo->prepare("SELECT * FROM otp_codes WHERE user_id = ? AND otp_code = ? AND device_id = ? AND is_used = 0 ORDER BY id DESC LIMIT 1");
    $stmt->execute([$user_id, $otp, $device_id]);
    $otp_record = $stmt->fetch();
    
    if ($otp_record) {
        // Check expiry in PHP to avoid timezone issues between PHP and MySQL
        $expires_at = strtotime($otp_record['expires_at']);
        $current_time = time();
        
        if ($current_time > $expires_at) {
            // OTP has expired
            return false;
        }
        
        // Mark OTP as used to prevent reuse
        $update = $pdo->prepare("UPDATE otp_codes SET is_used = 1 WHERE id = ?");
        $update->execute([$otp_record['id']]);
        
        // Mark login session as verified (trusted device)
        $session = $pdo->prepare("UPDATE login_sessions SET is_verified = 1 WHERE user_id = ? AND device_info = ?");
        $session->execute([$user_id, $device_id]);
        
        return true;
    }
    
    // OTP not found or already used
    return false;
}

