<?php
/**
 * Forgot Password - Step 4: Reset Password
 * Allow user to set new password after OTP verification
 */
session_start();
require_once '../database/db_connect.php';

// Check if OTP and Security Questions are verified
if (!isset($_SESSION['reset_user_id']) || !isset($_SESSION['security_verified'])) {
    header('Location: forgot_password.php');
    exit;
}

$user_id = $_SESSION['reset_user_id'];
$error = '';
$success = '';

// Fetch user details for display
$stmt = $conn->prepare("SELECT id, firstName, lastName, username, email FROM users WHERE id = ?");
$stmt->bind_param('s', $user_id);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($new_password)) {
        $error = 'Please enter a new password';
    }
    elseif (strlen($new_password) < 8) {
        $error = 'Password must be at least 8 characters long';
    }
    elseif ($new_password !== $confirm_password) {
        $error = 'Passwords do not match';
    }
    else {
        // Update password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param('ss', $hashed_password, $user_id);

        if ($stmt->execute()) {
            $success = 'Password reset successfully!';

            // Clear session
            unset($_SESSION['reset_user_id']);
            unset($_SESSION['otp_verified']);
            unset($_SESSION['security_verified']);

            // Redirect to login after 3 seconds
            echo '<meta http-equiv="refresh" content="3;url=login.php">';
        }
        else {
            $error = 'Failed to reset password. Please try again.';
        }
        $stmt->close();
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
    <title>Reset Password - Pizza Crust Delight</title>
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
            <h2>Reset Password</h2>

            <div class="account-info-card">
                <div class="account-info-row">
                    <span class="account-info-label">ACCOUNT ID:</span>
                    <span class="account-info-value"><?php echo htmlspecialchars($userData['id']); ?></span>
                </div>
                <div class="account-info-row">
                    <span class="account-info-label">Username:</span>
                    <span class="account-info-value">@<?php echo htmlspecialchars($userData['username']); ?></span>
                </div>
                <div class="account-info-row">
                    <span class="account-info-label">Email Address:</span>
                    <span class="account-info-value"><?php echo htmlspecialchars($userData['email']); ?></span>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php
endif; ?>

            <?php if ($success): ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($success); ?>
                    <p>Redirecting to login page...</p>
                </div>
            <?php
else: ?>
                <form method="POST" class="forgot-form">
                    <p>Enter your new password below</p>

                    <div class="form-group">
                        <label for="new_password">New Password:</label>
                        <div class="password-container">
                            <input type="password" id="new_password" name="new_password" required
                                   minlength="8" placeholder="Enter new password" autocomplete="new-password">
                            <svg class="eye-icon toggle-password" data-target="new_password" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password:</label>
                        <div class="password-container">
                            <input type="password" id="confirm_password" name="confirm_password" required
                                   minlength="8" placeholder="Confirm new password" autocomplete="new-password">
                            <svg class="eye-icon toggle-password" data-target="confirm_password" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </div>
                    </div>

                    <div class="password-requirements">
                        <p><strong>Password Requirements:</strong></p>
                        <ul>
                            <li>At least 8 characters long</li>
                            <li>Contains letters and numbers</li>
                            <li>Not similar to your username</li>
                        </ul>
                    </div>

                    <button type="submit" class="btn-primary">Reset Password</button>
                </form>
            <?php
endif; ?>
        </div>
    </main>



    <script>
        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(toggle => {
            toggle.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);

                if (input.type === 'password') {
                    input.type = 'text';
                    this.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
                } else {
                    input.type = 'password';
                    this.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
                }
            });
        });
    </script>
</body>
</html>
