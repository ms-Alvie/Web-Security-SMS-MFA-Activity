<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM login_sessions WHERE user_id = ? ORDER BY login_time DESC LIMIT 10");
$stmt->execute([$_SESSION['user_id']]);
$login_history = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Secure Login System</title>
    <link rel="stylesheet" href="css/style.css?v=2.0">
    <style>
        body {
            padding: 40px 20px;
            min-height: 100vh;
            position: relative;
        }
        
        /* Logout button on page background */
        .logout-btn-page {
            position: fixed;
            top: 30px;
            right: 30px;
            background: rgba(255, 255, 255, 0.95);
            color: #667eea;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            z-index: 1000;
        }
        
        .logout-btn-page:hover {
            background: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }
        
        .dashboard {
            max-width: 900px;
            margin: 0 auto;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            position: relative;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }
        .header h1 {
            color: white;
            margin: 0 0 15px 0;
            font-size: 32px;
        }
        .phone-info {
            background: rgba(255, 255, 255, 0.2);
            padding: 12px 16px;
            border-radius: 8px;
            margin-top: 10px;
            font-size: 16px;
            backdrop-filter: blur(10px);
        }
        .device-list {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        }
        .device-list h2 {
            margin-top: 0;
            color: #333;
            border-bottom: 3px solid #667eea;
            padding-bottom: 15px;
            font-size: 24px;
            margin-bottom: 25px;
        }
        
        /* Table-like layout */
        .sessions-table {
            display: table;
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
        }
        
        .table-header {
            display: table-row;
            font-weight: 600;
            color: #666;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .table-header > div {
            display: table-cell;
            padding: 12px 15px;
            background: #f8f9ff;
            border-top: 2px solid #667eea;
        }
        
        .table-header > div:first-child {
            border-radius: 8px 0 0 8px;
        }
        
        .table-header > div:last-child {
            border-radius: 0 8px 8px 0;
        }
        
        .device-row {
            display: table-row;
            transition: all 0.3s;
        }
        
        .device-row > div {
            display: table-cell;
            padding: 20px 15px;
            vertical-align: middle;
            background: white;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .device-row:hover > div {
            background: #f8f9ff;
            transform: scale(1.01);
        }
        
        .device-row:first-of-type > div {
            border-top: 1px solid #f0f0f0;
        }
        
        .device-row > div:first-child {
            border-left: 3px solid transparent;
            border-radius: 8px 0 0 8px;
        }
        
        .device-row:hover > div:first-child {
            border-left-color: #667eea;
        }
        
        .device-row > div:last-child {
            border-radius: 0 8px 8px 0;
        }
        
        .device-type {
            display: inline-block;
            padding: 6px 14px;
            background: linear-gradient(135deg, #e3f2fd 0%, #e8eaf6 100%);
            color: #1565C0;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
        
        .verified-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            margin-left: 8px;
        }
        
        .verified-badge.trusted {
            background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 100%);
            color: #2e7d32;
        }
        
        .verified-badge.untrusted {
            background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
            color: #e65100;
        }
        
        .ip-address {
            color: #666;
            font-size: 14px;
            font-family: 'Courier New', monospace;
        }
        
        .timestamp {
            color: #999;
            font-size: 14px;
            font-weight: 500;
        }
        
        .no-sessions {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        .no-sessions-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <!-- Logout button on purple background -->
    <a href="logout.php" class="logout-btn-page">Logout</a>
    
    <div class="dashboard">
        <div class="header">
            <h1>👋 Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
            <div class="phone-info">
                📱 Registered Phone: <strong><?php echo htmlspecialchars($_SESSION['user_phone']); ?></strong>
            </div>
        </div>
        
        <div class="device-list">
            <h2>🔐 Recent Login Sessions</h2>
            <?php if (count($login_history) > 0): ?>
                <div class="sessions-table">
                    <div class="table-header">
                        <div>Device & Status</div>
                        <div>IP Address</div>
                        <div>Login Time</div>
                    </div>
                    
                    <?php foreach ($login_history as $session): ?>
                        <div class="device-row">
                            <div>
                                <span class="device-type">
                                    <?php 
                                    $ua = $session['user_agent'];
                                    if (stripos($ua, 'Mobile') !== false || stripos($ua, 'Android') !== false) {
                                        echo '📱 Mobile';
                                    } elseif (stripos($ua, 'Tablet') !== false || stripos($ua, 'iPad') !== false) {
                                        echo '📱 Tablet';
                                    } else {
                                        echo '💻 Desktop';
                                    }
                                    ?>
                                </span>
                                <span class="verified-badge <?php echo $session['is_verified'] ? 'trusted' : 'untrusted'; ?>">
                                    <?php echo $session['is_verified'] ? '✓ Verified' : '⚠ Pending'; ?>
                                </span>
                            </div>
                            <div class="ip-address">
                                🌐 <?php echo htmlspecialchars($session['ip_address']); ?>
                            </div>
                            <div class="timestamp">
                                🕐 <?php echo date('M j, Y g:i A', strtotime($session['login_time'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-sessions">
                    <div class="no-sessions-icon">📊</div>
                    <p>No login sessions found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
