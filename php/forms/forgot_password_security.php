<?php
/**
 * Forgot Password - Step 4: Security Questions
 * Verify user identity via security questions (Standardized Dropdowns)
 */
session_start();
require_once __DIR__ . '/../includes/path_helper.php';
$basePath = getBasePath(__FILE__);
require_once '../database/db_connect.php';

// Check if OTP was verified
if (!isset($_SESSION['reset_user_id']) || !isset($_SESSION['otp_verified'])) {
    header('Location: forgot_password.php');
    exit;
}

$user_id = $_SESSION['reset_user_id'];
$error = '';

// Fetch user's identity details for persistent display
$stmt = $conn->prepare("SELECT id, firstName, lastName, username, email FROM users WHERE id = ?");
$stmt->bind_param('s', $user_id);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $q1 = trim($_POST['secure_question'] ?? '');
    $a1 = trim($_POST['secure_answer'] ?? '');
    $q2 = trim($_POST['secure_question2'] ?? '');
    $a2 = trim($_POST['secure_answer2'] ?? '');
    $q3 = trim($_POST['secure_question3'] ?? '');
    $a3 = trim($_POST['secure_answer3'] ?? '');

    if (!$q1 || !$a1 || !$q2 || !$a2 || !$q3 || !$a3) {
        $error = "Please answer all security questions.";
    } else {
        // Fetch saved questions and hashed answers
        $stmt = $conn->prepare("SELECT secure_question, secure_answer, secure_question2, secure_answer2, secure_question3, secure_answer3 FROM users WHERE id = ?");
        $stmt->bind_param('s', $user_id);
        $stmt->execute();
        $saved = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // Verify questions match AND answers match
        $match = ($q1 === $saved['secure_question'] && password_verify($a1, $saved['secure_answer']) &&
                  $q2 === $saved['secure_question2'] && password_verify($a2, $saved['secure_answer2']) &&
                  $q3 === $saved['secure_question3'] && password_verify($a3, $saved['secure_answer3']));

        if ($match) {
            $_SESSION['security_verified'] = true;
            header('Location: forgot_password_reset.php');
            exit;
        } else {
            $error = "The selected questions or provided answers do not match our records.";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo $basePath; ?>css/serve_asset.php?file=design-system.css">
    <link rel="stylesheet" href="<?php echo $basePath; ?>css/serve_asset.php?file=login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <title>Security Check - Pizza Crust Delight</title>
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
        .identity-header {
            background: #eff6ff;
            padding: 1.25rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            text-align: left;
            border-left: 5px solid var(--primary-color);
        }
        .password-container { position: relative; }
        .pw-toggle {
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            color: #94a3b8; cursor: pointer; transition: color 0.2s;
        }
        .pw-toggle:hover { color: var(--primary-color); }
        .input-field { padding-right: 40px !important; width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0; }
    </style>
</head>

<body>
    <nav class="navbar">
        <div class="navbar-left">
            <img src="../../images/logo4.png" alt="Pizza Crust Delight" class="logo">
            <span class="navbar-text">Pizza Crust Delight</span>
        </div>
        <div class="navbar-right">
            <a href="./login.php" class="nav-link">Back to Login</a>
        </div>
    </nav>

    <main>
        <div class="form-container" style="max-width: 600px;">
            <h2>Security Verification</h2>
            
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

            <p>Please select your security questions and provide the answers you set during registration.</p>

            <?php if ($error): ?>
                <div class="error-message" style="display:block;"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Security Question 1</label>
                    <select name="secure_question" class="input-field" required>
                        <option value="">-- Select Question --</option>
                        <option value="Who is your bestfriend in elementary?">Who is your bestfriend in elementary?</option>
                        <option value="What is the name of your pet?">What is the name of your pet?</option>
                        <option value="Who is your favorite teacher in highschool?">Who is your favorite teacher in highschool?</option>
                        <option value="What was your first car?">What was your first car?</option>
                        <option value="In what city were you born?">In what city were you born?</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Answer 1</label>
                    <div class="password-container">
                        <input type="password" name="secure_answer" class="input-field" required placeholder="Your answer">
                        <i class="fa-solid fa-eye pw-toggle" onclick="toggleVisibility(this)"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label>Security Question 2</label>
                    <select name="secure_question2" class="input-field" required>
                        <option value="">-- Select Question --</option>
                        <option value="What is your mother's maiden name?">What is your mother's maiden name?</option>
                        <option value="What elementary school did you attend?">What elementary school did you attend?</option>
                        <option value="What is your favorite food?">What is your favorite food?</option>
                        <option value="What was your childhood nickname?">What was your childhood nickname?</option>
                        <option value="What is the name of your best friend?">What is the name of your best friend?</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Answer 2</label>
                    <div class="password-container">
                        <input type="password" name="secure_answer2" class="input-field" required placeholder="Your answer">
                        <i class="fa-solid fa-eye pw-toggle" onclick="toggleVisibility(this)"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label>Security Question 3</label>
                    <select name="secure_question3" class="input-field" required>
                        <option value="">-- Select Question --</option>
                        <option value="What is your father's middle name?">What is your father's middle name?</option>
                        <option value="What street did you grow up on?">What street did you grow up on?</option>
                        <option value="What is your favorite movie?">What is your favorite movie?</option>
                        <option value="What is the name of your first pet?">What is the name of your first pet?</option>
                        <option value="What year did you graduate high school?">What year did you graduate high school?</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Answer 3</label>
                    <div class="password-container">
                        <input type="password" name="secure_answer3" class="input-field" required placeholder="Your answer">
                        <i class="fa-solid fa-eye pw-toggle" onclick="toggleVisibility(this)"></i>
                    </div>
                </div>

                <button type="submit" class="btn-primary" style="margin-top: 1.5rem;">Verify Security Answers</button>
            </form>
        </div>
    </main>

    <script>
        function toggleVisibility(icon) {
            const input = icon.previousElementSibling;
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>
</html>
