<?php
/**
 * Forgot Password - Step 3: Verify OTP
 * Verify the OTP and allow password reset
 */
session_start();
require_once '../database/db_connect.php';

// Check if user is verified and OTP was sent
if (!isset($_SESSION['reset_user_id']) || !isset($_SESSION['otp_sent'])) {
    header('Location: forgot_password.php');
    exit;
}

$user_id = $_SESSION['reset_user_id'];
$user_data = null;

// Fetch user details for display
$stmt = $conn->prepare("SELECT id, firstName, lastName, username, email FROM users WHERE id = ?");
$stmt->bind_param('s', $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $user_data = $result->fetch_assoc();
}
$stmt->close();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['verify_otp'])) {
        $otp_code = trim($_POST['otp_code']);

        if (empty($otp_code)) {
            $error = 'Please enter the verification code';
        }
        elseif (strlen($otp_code) !== 6) {
            $error = 'Invalid verification code format';
        }
        else {
            // Verify OTP
            $stmt = $conn->prepare("SELECT id FROM password_reset_otp
                                   WHERE user_id = ? AND otp_code = ? AND used = 0
                                   AND expires_at > NOW()");
            $stmt->bind_param('ss', $user_id, $otp_code);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Mark OTP as used
                $otp_id = $result->fetch_assoc()['id'];
                $stmt = $conn->prepare("UPDATE password_reset_otp SET used = 1 WHERE id = ?");
                $stmt->bind_param('i', $otp_id);
                $stmt->execute();
                $stmt->close();

                $_SESSION['otp_verified'] = true;
                unset($_SESSION['otp_sent']);
                header('Location: forgot_password_security.php');
                exit;
            }
            else {
                $error = 'Invalid or expired verification code';
            }
            $stmt->close();
        }
    }
    elseif (isset($_POST['resend_otp'])) {
        // Redirect to resend page
        header('Location: forgot_password_send_otp.php');
        exit;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../css/serve_asset.php?file=design-system.css">
    <link rel="stylesheet" href="../../css/serve_asset.php?file=login.css">
    <title>Verify Code - Pizza Crust Delight</title>
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
            <h2>Enter Verification Code</h2>

            <?php if ($user_data): ?>
            <div class="account-info-card">
                <div class="account-info-row">
                    <span class="account-info-label">ACCOUNT ID:</span>
                    <span class="account-info-value"><?php echo htmlspecialchars($user_data['id']); ?></span>
                </div>
                <div class="account-info-row">
                    <span class="account-info-label">Username:</span>
                    <span class="account-info-value">@<?php echo htmlspecialchars($user_data['username']); ?></span>
                </div>
                <div class="account-info-row">
                    <span class="account-info-label">Email Address:</span>
                    <span class="account-info-value"><?php echo htmlspecialchars($user_data['email']); ?></span>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php
endif; ?>

            <?php if ($user_data): ?>
            <div class="user-info" style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: left;">
                <p style="margin: 5px 0;"><strong>ID:</strong> <?php echo htmlspecialchars($user_id); ?></p>
                <p style="margin: 5px 0;"><strong>Name:</strong> <?php echo htmlspecialchars($user_data['firstName'] . ' ' . $user_data['lastName']); ?></p>
                <p style="margin: 5px 0;"><strong>Email:</strong> <?php echo htmlspecialchars($user_data['email']); ?></p>
            </div>
            <?php
endif; ?>

            <form method="POST" class="forgot-form">
                <p>Enter the 6-digit verification code sent to your email.</p>
                <p id="timer-text" style="color: #666; font-size: 0.9em; margin-bottom: 15px;">Resend available in <span id="countdown">60</span>s</p>

                <div class="form-group">
                    <label for="otp_code">Verification Code:</label>
                    <input type="password" id="otp_code" name="otp_code" required
                           maxlength="6" pattern="[0-9]{6}"
                           placeholder="Enter 6-digit code" autocomplete="one-time-code">
                </div>

                <div class="form-buttons">
                    <button type="submit" name="verify_otp" class="btn-primary">Verify Code</button>
                    <button type="submit" name="resend_otp" id="resend_btn" class="btn-secondary" disabled>Resend Code</button>
                </div>
            </form>

            <p class="info-text">
                <strong>Didn't receive the code?</strong> Check your spam folder or wait for the timer to resend.
            </p>
        </div>
    </main>



    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var timeLeft = 60;
            var countdownElement = document.getElementById('countdown');
            var timerTextElement = document.getElementById('timer-text');
            var resendBtn = document.getElementById('resend_btn');

            var timerId = setInterval(function() {
                timeLeft--;
                countdownElement.textContent = timeLeft;

                if (timeLeft <= 0) {
                    clearInterval(timerId);
                    resendBtn.disabled = false;
                    resendBtn.textContent = "Resend Code";
                    timerTextElement.style.display = 'none';
                } else {
                    resendBtn.textContent = "Resend Code (" + timeLeft + "s)";
                }
            }, 1000);
        });
    </script>
</body>
</html>
