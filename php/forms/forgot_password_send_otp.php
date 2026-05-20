<?php
/**
 * Forgot Password - Step 2: Send OTP
 * Generates and sends OTP to user's email
 */
session_start();
require_once '../database/db_connect.php';
require_once '../config/email_config.php';

// Check if user is verified
if (!isset($_SESSION['reset_user_id'])) {
    header('Location: forgot_password.php');
    exit;
}

$user_id = $_SESSION['reset_user_id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get user email
    $stmt = $conn->prepare("SELECT id, firstName, lastName, username, email FROM users WHERE id = ?");
    $stmt->bind_param('s', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $email = $user['email'];

        // Check if there's a recent OTP (within 1 minute) to prevent spam
        $stmt = $conn->prepare("SELECT id, resend_count, last_resend_at FROM password_reset_otp
                               WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE) AND used = 0");
        $stmt->bind_param('s', $user_id);
        $stmt->execute();
        $recent_otp = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($recent_otp && $recent_otp['resend_count'] >= 3) {
            $error = 'Too many OTP requests. Please wait 15 minutes before trying again.';
        }
        else {
            // Generate 6-digit OTP
            $otp_code = sprintf('%06d', mt_rand(0, 999999));
            $expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';

            // Delete any existing unused OTPs for this user
            $stmt = $conn->prepare("DELETE FROM password_reset_otp WHERE user_id = ? AND used = 0");
            $stmt->bind_param('s', $user_id);
            $stmt->execute();
            $stmt->close();

            // Insert new OTP
            $resend_count = $recent_otp ? $recent_otp['resend_count'] + 1 : 1;
            $stmt = $conn->prepare("INSERT INTO password_reset_otp
                                   (user_id, otp_code, expires_at, resend_count, ip_address)
                                   VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('sssis', $user_id, $otp_code, $expires_at, $resend_count, $ip_address);

            if ($stmt->execute()) {
                // Send OTP via email
                if (sendOTPEmail($email, $otp_code)) {
                    $success = 'Verification code sent to your email!';
                    $_SESSION['otp_sent'] = true;
                    $_SESSION['otp_expires_at'] = $expires_at;
                }
                else {
                    $error = 'Failed to send email. Please try again.';
                }
            }
            else {
                $error = 'Failed to generate verification code. Please try again.';
            }
            $stmt->close();
        }
    }
    else {
        $error = 'User not found';
    }
}

// Connection will be closed at end of file after display
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../css/serve_asset.php?file=design-system.css">
    <link rel="stylesheet" href="../../css/serve_asset.php?file=login.css">
    <title>Send Verification Code - Pizza Crust Delight</title>
    <style>
        .account-info-card {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
            text-align: left;
        }
        .account-info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .account-info-row:last-child {
            border-bottom: none;
        }
        .account-info-label {
            color: #64748b;
            font-weight: 500;
            font-size: 0.95rem;
        }
        .account-info-value {
            color: #0f172a;
            font-weight: 600;
            font-size: 0.95rem;
            text-align: right;
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/../includes/layout/navbar.php'; ?>

    <main>
        <div class="form-container">
            <h2>Send Verification Code</h2>

            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($success); ?>
                    <meta http-equiv="refresh" content="3;url=forgot_password_verify_otp.php">
                </div>
                <p class="info-text">Redirecting to verification page...</p>
            <?php else: ?>
                <?php
                // Fetch and display user info
                $stmt = $conn->prepare("SELECT id, username, email FROM users WHERE id = ?");
                $stmt->bind_param('s', $user_id);
                $stmt->execute();
                $user_display = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                ?>
                
                <div class="account-info-card">
                    <div class="account-info-row">
                        <span class="account-info-label">ACCOUNT ID:</span>
                        <span class="account-info-value"><?php echo htmlspecialchars($user_display['id']); ?></span>
                    </div>
                    <div class="account-info-row">
                        <span class="account-info-label">Username:</span>
                        <span class="account-info-value">@<?php echo htmlspecialchars($user_display['username']); ?></span>
                    </div>
                    <div class="account-info-row">
                        <span class="account-info-label">Email Address:</span>
                        <span class="account-info-value"><?php echo htmlspecialchars($user_display['email']); ?></span>
                    </div>
                </div>

                <form method="POST" class="forgot-form">
                    <p>Click the button below to send a 6-digit verification code to your email address.</p>
                    <p class="info-text"><strong>Note:</strong> The code will expire in 15 minutes.</p>

                    <button type="submit" class="btn-primary">Send Verification Code</button>
                </form>
            <?php endif; ?>
        </div>
    </main>

    <?php $conn->close(); ?>
</body>
</html>
