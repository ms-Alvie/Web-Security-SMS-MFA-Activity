# 🔐 Secure Login System with SMS OTP Verification

<div align="center">

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white)
![Telerivet](https://img.shields.io/badge/Telerivet-SMS-blue?style=for-the-badge)

**A modern, secure authentication system with Two-Factor Authentication via SMS**

[Features](#-features) • [Demo](#-demo) • [Installation](#-installation) • [Usage](#-usage) • [Security](#-security) • [Documentation](#-documentation)

</div>

---

## 📋 Table of Contents

- [Overview](#-overview)
- [Features](#-features)
- [Technology Stack](#-technology-stack)
- [System Requirements](#-system-requirements)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Usage Guide](#-usage-guide)
- [Project Structure](#-project-structure)
- [Security Features](#-security-features)
- [API Integration](#-api-integration)
- [Testing](#-testing)
- [Troubleshooting](#-troubleshooting)
- [Contributing](#-contributing)
- [License](#-license)

---

## 🎯 Overview

A comprehensive PHP-based authentication system implementing **Two-Factor Authentication (2FA)** using SMS One-Time Passwords (OTP). When users log in from new devices, they receive a verification code via SMS through the Telerivet API, adding an extra layer of security beyond traditional password authentication.

### Why This Project?

- ✅ **Enhanced Security**: Implements industry-standard 2FA
- ✅ **Device Trust**: Remember trusted devices to reduce friction
- ✅ **Session Management**: Track and monitor login history
- ✅ **Modern UI/UX**: Clean, responsive design with animations
- ✅ **Production-Ready**: Follows security best practices

---

## ✨ Features

### Core Functionality
- 🔐 **User Registration** with phone number validation
- 📱 **SMS OTP Verification** via Telerivet API
- 🖥️ **Trusted Device Management** - Skip OTP on known devices
- 📊 **Login History Dashboard** - View all session activity
- ⏱️ **OTP Expiration** - Configurable time limits
- 🔄 **OTP Resend** - Request new codes if needed

### Security Features
- 🛡️ **Password Hashing** using bcrypt
- 🔒 **Prepared Statements** to prevent SQL injection
- ✅ **Input Validation** and sanitization
- 🎯 **Device Fingerprinting** for session tracking
- ⚡ **Session Management** with secure cookies
- 🚫 **One-Time Use OTPs** - Codes expire after use

### User Experience
- 💅 **Modern, Responsive Design**
- 🎨 **Smooth Animations** and transitions
- 📱 **Mobile-Friendly** interface
- ⚡ **Real-Time Validation** feedback
- 🎯 **Password Strength Indicator**
- 🌈 **Beautiful Gradient UI**

---

## 🛠️ Technology Stack

| Component | Technology |
|-----------|-----------|
| **Backend** | PHP 7.4+ |
| **Database** | MySQL 5.7+ / MariaDB |
| **SMS Gateway** | Telerivet API |
| **Dependencies** | Composer |
| **Environment** | XAMPP / WAMP / LAMP |
| **Libraries** | `vlucas/phpdotenv`, `telerivet/telerivet-php-client` |

---

## 💻 System Requirements

- **PHP**: 7.4 or higher
- **MySQL**: 5.7 or higher
- **Web Server**: Apache (via XAMPP/WAMP) or Nginx
- **Composer**: Latest version
- **Telerivet Account**: For SMS functionality

---

## 📥 Installation

### Step 1: Clone or Download

```bash
cd C:\xampp\htdocs\
# Your project folder is already here
```

### Step 2: Install Dependencies

```bash
cd login-system
composer install
```

### Step 3: Database Setup

1. Start XAMPP Control Panel
2. Start **Apache** and **MySQL**
3. Open phpMyAdmin: `http://localhost/phpmyadmin`
4. Create database:
   ```sql
   CREATE DATABASE login_system;
   ```
5. Import the schema:
   - Click on `login_system` database
   - Go to **SQL** tab
   - Copy contents from `database.sql`
   - Click **Go**

### Step 4: Environment Configuration

1. Copy `.env.example` to `.env`:
   ```bash
   copy .env.example .env
   ```

2. Edit `.env` with your credentials:
   ```env
   # Database Configuration
   DB_HOST=localhost
   DB_NAME=login_system
   DB_USER=root
   DB_PASS=

   # Telerivet Configuration  
   TELERIVET_API_KEY=your_actual_api_key_here
   TELERIVET_PROJECT_ID=your_actual_project_id_here

   # Application Settings
   APP_NAME="Secure Login System"
   OTP_EXPIRY_MINUTES=30
   ```

### Step 5: Telerivet Setup

1. Sign up at [telerivet.com](https://telerivet.com)
2. Create a new project
3. Navigate to **API Keys** section
4. Generate an API key
5. Copy your **Project ID** from project settings
6. Add both to your `.env` file

---

## ⚙️ Configuration

### Environment Variables

| Variable | Description | Example |
|----------|-------------|---------|
| `DB_HOST` | Database host | `localhost` |
| `DB_NAME` | Database name | `login_system` |
| `DB_USER` | Database username | `root` |
| `DB_PASS` | Database password | (empty for XAMPP) |
| `TELERIVET_API_KEY` | Telerivet API key | `sk_...` |
| `TELERIVET_PROJECT_ID` | Telerivet project ID | `PJ...` |
| `APP_NAME` | Application name | `"Secure Login System"` |
| `OTP_EXPIRY_MINUTES` | OTP validity duration | `30` |

---

## 📖 Usage Guide

### User Registration

1. Navigate to `http://localhost/login-system`
2. Click "Create one here"
3. Fill in:
   - **Full Name**: Your name
   - **Phone Number**: International format (`+639171234567`)
   - **Email**: (Optional)
   - **Password**: Minimum 8 characters
4. Click "Create Account"

### First Login (New Device)

1. Enter your **phone number** and **password**
2. System detects new device
3. OTP is sent via SMS
4. Enter the 6-digit code
5. Access granted - device now trusted

### Subsequent Logins (Trusted Device)

1. Enter credentials
2. **No OTP required** - immediate access
3. Trusted device recognized automatically

### Dashboard Features

- View welcome message with your name
- See registered phone number
- Browse login history:
  - Device type (Mobile/Desktop/Tablet)
  - Verification status
  - IP address
  - Timestamp
- Logout securely

---

## 📁 Project Structure

```
login-system/
├── .env                    # Environment configuration (DO NOT COMMIT)
├── .env.example           # Environment template
├── .gitignore             # Git ignore rules
├── composer.json          # PHP dependencies
├── database.sql           # Database schema
├── README.md             # This file
│
├── config/
│   └── database.php      # PDO database connection
│
├── includes/
│   └── functions.php     # Helper functions (OTP, SMS, device detection)
│
├── css/
│   └── style.css        # Modern responsive styles
│
├── vendor/              # Composer dependencies
│
├── index.php           # Landing page (redirects to login)
├── register.php        # User registration
├── login.php          # User login
├── verify-otp.php     # OTP verification
├── resend-otp.php     # Resend OTP
├── dashboard.php      # User dashboard
└── logout.php         # Logout handler
```

---

## 🔒 Security Features

### Authentication
- **Password Hashing**: Uses `PASSWORD_DEFAULT` (bcrypt)
- **Prepared Statements**: All database queries use PDO prepared statements
- **Input Validation**: Server-side validation for all user inputs
- **Session Management**: Secure PHP sessions with HTTPOnly cookies

### SMS OTP Security
- **One-Time Use**: OTPs marked as used after verification
- **Time-Limited**: Configurable expiration (default 30 minutes)
- **Device-Specific**: OTPs tied to specific device fingerprints
- **Secure Storage**: OTPs stored securely in database

### Device Fingerprinting
- **SHA-256 Hashing**: Device ID generated from User Agent + IP
- **Trusted Device List**: Previously verified devices skip OTP
- **Session Tracking**: Complete audit trail of all logins

### Best Practices Implemented
- ✅ No hardcoded credentials
- ✅ Environment variables for sensitive data
- ✅ `.env` excluded from version control
- ✅ Error logging without exposing details
- ✅ HTTPS recommended for production

---

## 📡 API Integration

### Telerivet SMS API

```php
// Example: Sending OTP via Telerivet
$telerivet = new \Telerivet_API($api_key);
$project = $telerivet->initProjectById($project_id);

$result = $project->sendMessage([
    'to_number' => '+639171234567',
    'content' => 'Your OTP is: 123456'
]);
```

### Phone Number Format

All phone numbers must be in international format:

| Country | Format | Example |
|---------|--------|---------|
| Philippines | +63XXXXXXXXXX | +639171234567 |
| USA | +1XXXXXXXXXX | +12025551234 |
| UK | +44XXXXXXXXXX | +447911123456 |

---

## 🧪 Testing

### Manual Testing Checklist

- [✅] Register new user with valid phone number
- [✅] Attempt registration with duplicate phone number
- [✅] Login from new browser (should trigger OTP)
- [✅] Receive SMS with OTP code
- [✅] Verify OTP successfully
- [✅] Login from same browser (should skip OTP)
- [✅] View dashboard with login history
- [✅] Test logout functionality
- [✅] Try expired OTP
- [✅] Test resend OTP feature

### Test Mode (Telerivet)

For development without sending real SMS:
1. Telerivet projects start in test mode
2. Messages appear in dashboard but don't send
3. Copy OTP from Telerivet dashboard
4. Use for verification

---

## 🐛 Troubleshooting

### Common Issues

#### Connection Failed Error
**Symptom**: "Connection failed: SQLSTATE[HY000] [2002]"

**Solution**:
```bash
# 1. Start MySQL in XAMPP Control Panel
# 2. Verify MySQL is running on port 3306
# 3. Check .env database credentials
```

#### SMS Not Sending
**Symptom**: OTP not received on phone

**Solutions**:
1. **Test Mode**: Check Telerivet dashboard for message
2. **API Keys**: Verify correct API key and Project ID in `.env`
3. **Phone Format**: Use international format (+CountryCode)
4. **SMS Credits**: Check Telerivet balance
5. **Gateway Setup**: Configure Android phone or SMS provider

#### Invalid OTP Error
**Symptom**: Correct OTP shows as invalid

**Solution**:
```bash
# Already fixed in code - expiry checked in PHP, not MySQL
# If issue persists, check system date/time is correct
```

#### Composer Not Found
**Symptom**: "composer: command not found"

**Solution**:
```bash
# Download and install Composer from getcomposer.org
# Or use full path: php composer.phar install
```

---

## 📚 Documentation

### Code Documentation

All functions are fully documented with PHPDoc comments:

```php
/**
 * Generate a random One-Time Password (OTP)
 * 
 * @param int $length Length of OTP (default: 6)
 * @return string Generated OTP code
 */
function generateOTP($length = 6) {
    // Implementation
}
```

### Database Schema

#### users
- `id`: Primary key
- `phone_number`: UNIQUE, NOT NULL
- `email`: Optional
- `password`: Hashed (bcrypt)
- `full_name`: User's full name
- `created_at`: Registration timestamp

#### login_sessions
- `id`: Primary key
- `user_id`: Foreign key to users
- `device_info`: Device fingerprint hash
- `ip_address`: Login IP
- `user_agent`: Browser/device info
- `login_time`: Session timestamp
- `is_verified`: OTP verified flag

#### otp_codes
- `id`: Primary key
- `user_id`: Foreign key to users
- `otp_code`: 6-digit code
- `device_id`: Device fingerprint
- `created_at`: Generation time
- `expires_at`: Expiration time
- `is_used`: One-time use flag

---

## 🤝 Contributing

This is an educational project for SOECS-ITELEC07. For improvements:

1. Fork the repository
2. Create feature branch (`git checkout -b feature/improvement`)
3. Commit changes (`git commit -m 'Add improvement'`)
4. Push to branch (`git push origin feature/improvement`)
5. Open Pull Request

---

## 📄 License

This project is for educational purposes as part of the SOECS-ITELEC07 course activity.

---

## 👤 Author

**Your Name**
- School: Divine Word College of Legazpi (South Campus)
- Course: SOECS-ITELEC07
- Project: MFA SMS Integration Activity

---

## 🙏 Acknowledgments

- **Telerivet** - SMS Gateway API
- **vlucas/phpdotenv** - Environment variable management
- **SOECS-ITELEC07** - Course instructors and materials

---

## 📞 Support

For issues or questions:
1. Check [Troubleshooting](#-troubleshooting) section
2. Review [Documentation](#-documentation)
3. Contact course instructor

---

<div align="center">

**Made with ❤️ for SOECS-ITELEC07**

⭐ Star this repository if you found it helpful!

</div>
