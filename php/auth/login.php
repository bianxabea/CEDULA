<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';

if (isset($_SESSION['user']) && !empty($_SESSION['user'])) {
    $role = $_SESSION['user']['role'] ?? 'consumer';
    $redirect = getBaseUrl() . '/php/auth/dashboard.php';
    if ($role === 'admin') {
        $redirect = getBaseUrl() . '/php/admin/index.php';
    }
    elseif ($role === 'superadmin') {
        $redirect = getBaseUrl() . '/php/superadmin/index.php';
    }
    header('Location: ' . $redirect);
    exit;
}

$login_error = null;
if (isset($_SESSION['login_error'])) {
    $login_error = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}
if (isset($_GET['error'])) {
    if ($_GET['error'] === 'blocked_consumer') {
        $login_error = "Your account has been blocked. Please contact admin or superadmin.";
    }
    elseif ($_GET['error'] === 'blocked_admin' || $_GET['error'] === 'blocked') {
        $login_error = "Your account has been blocked. Please contact superadmin.";
    }
}

$lockoutActive = false;
$lockoutTime = 0;
$failedAttempts = 0;
if (isset($_SESSION['lockout_time']) && $_SESSION['lockout_time'] > time()) {
    $lockoutActive = true;
    $lockoutTime = $_SESSION['lockout_time'];
    $failedAttempts = $_SESSION['failed_attempts'] ?? 0;
}
$baseUrl = getBaseUrl();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pizza Crust Delight</title>
    <link rel="stylesheet" href="../../css/serve_asset.php?file=design-system.css">
    <link rel="stylesheet" href="../../css/serve_asset.php?file=login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="public-layout">
    <?php include __DIR__ . '/../includes/layout/navbar.php'; ?>

    <main class="page-content" style="display: flex; margin: 0; padding: 0;">
        <div id="validationModal" <?php if ($lockoutActive): ?>data-lockout-active="true" data-lockout-time="<?php echo $lockoutTime; ?>" data-failed-attempts="<?php echo $failedAttempts; ?>"<?php
endif; ?>>
            <div class="modal-content">
                <svg class="error-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="#e66868" width="2em" height="2em">
                    <path d="M256 512C397.4 512 512 397.4 512 256C512 114.6 397.4 0 256 0C114.6 0 0 114.6 0 256C0 397.4 114.6 512 256 512zM232 128C232 119.2 239.2 112 248 112H264C272.8 112 280 119.2 280 128V288C280 296.8 272.8 304 264 304H248C239.2 304 232 296.8 232 288V128zM256 384C238.3 384 224 369.7 224 352C224 334.3 238.3 320 256 320C273.7 320 288 334.3 288 352C288 369.7 273.7 384 256 384z"/>
                </svg>
                <div class="text">
                    <span>Too Many Failed Attempts</span>
                    <div id="timer">Try Again in <span id="countdown">0</span> seconds</div>
                </div>
            </div>
        </div>

        <div id="userNotFoundModal" class="modal-simple-alert">
            <div class="modal-simple-alert-content">
                <svg class="error-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="#e66868" width="2em" height="2em">
                    <path d="M256 512C397.4 512 512 397.4 512 256C512 114.6 397.4 0 256 0C114.6 0 0 114.6 0 256C0 397.4 114.6 512 256 512zM232 128C232 119.2 239.2 112 248 112H264C272.8 112 280 119.2 280 128V288C280 296.8 272.8 304 264 304H248C239.2 304 232 296.8 232 288V128zM256 384C238.3 384 224 369.7 224 352C224 334.3 238.3 320 256 320C273.7 320 288 334.3 288 352C288 369.7 273.7 384 256 384z"/>
                </svg>
                <span class="modal-simple-alert-text">User ID not found!</span>
                <button id="userNotFoundOkBtn" class="submitBtn">Okay</button>
            </div>
        </div>

        <!-- OTP Response Modal (Success, Existing OTP, or Error) -->
        <div id="otpResponseModal" class="otp-response-modal" style="display:none;">
            <div class="otp-response-content">
                <!-- Success Icon -->
                <div class="otp-response-icon success-icon" id="otpSuccessIcon" style="display: none;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="#4CAF50" width="64" height="64">
                        <path d="M256 512C397.4 512 512 397.4 512 256C512 114.6 397.4 0 256 0C114.6 0 0 114.6 0 256C0 397.4 114.6 512 256 512zM369 209L241 337c-9.4 9.4-24.6 9.4-33.9 0l-64-64c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l47 47L335 175c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9z"/>
                    </svg>
                </div>
                <!-- Error/Info Icon -->
                <div class="otp-response-icon error-icon" id="otpErrorIcon" style="display: none;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="#ffa500" width="64" height="64">
                        <path d="M256 512C397.4 512 512 397.4 512 256C512 114.6 397.4 0 256 0C114.6 0 0 114.6 0 256C0 397.4 114.6 512 256 512zM232 128C232 119.2 239.2 112 248 112H264C272.8 112 280 119.2 280 128V288C280 296.8 272.8 304 264 304H248C239.2 304 232 296.8 232 288V128zM256 384C238.3 384 224 369.7 224 352C224 334.3 238.3 320 256 320C273.7 320 288 334.3 288 352C288 369.7 273.7 384 256 384z"/>
                    </svg>
                </div>
                <h3 id="otpResponseTitle"></h3>
                <p id="otpResponseMessage"></p>
                <p class="otp-response-email" id="otpResponseEmail" style="display: none;"></p>
                <button type="button" id="closeOtpResponseModal" class="submitBtn">OK</button>
            </div>
        </div>

        <div class="left-side">
            <img class="form-img" src="../../images/pizza_login_bg.png" alt="Food Delivery">
        </div>
        <div class="right-side">
            <form class="login-form" id="loginForm" method="POST" action="<?php echo $baseUrl; ?>/php/database/login.php">
                <h2>Welcome back</h2>
                <div class="form-row">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" placeholder="Enter your username">
                        <span id="validationMess" class="userValidationMess"></span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-container">
                            <input type="password" id="password" name="password" placeholder="••••••••" autocomplete>
                            <svg id="togglePassword" class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" onclick="togglePassword()" viewBox="0 0 24 24">
                                <path d="M1 12s4.5-8 11-8 11 8 11 8-4.5 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                        </div>
                        <span id="validationMessPw" class="userValidationMess" style="<?php echo $login_error ? 'display: block;' : ''; ?>"><?php if ($login_error)
    echo htmlspecialchars($login_error); ?></span>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; margin-top: 1rem; padding: 12px;">Login</button>
                <a href="#" id="forgotPasswordLink" class="forgot-pw">Forgot Password? Reset Here</a>
            </form>
        </div>
    </main>

    <?php include __DIR__ . '/../includes/layout/footer.php'; ?>
    <div id="forgotPasswordModal" class="modal2" style="display: none">
        <div class="modal2-content">
            <span class="close" id="forgotModalClose">&times;</span>
            <h2>Forgot Password</h2>
            <p id="forgotStepTitle" class="forgot-step-title">Step 1: Verify your User ID</p>
            <div id="forgotStep1" class="forgot-step">
                <label for="reset_id">Enter your User ID:</label>
                <input type="text" id="reset_id" name="reset_id" placeholder="e.g. 12345" autocomplete="username">
                <span id="forgotStep1Msg" class="forgot-msg"></span>
                <div style="display:flex; justify-content:center; margin-top:1rem;">
                    <button type="button" id="forgotStep1Btn" class="submitBtn" style="width:100%; max-width:260px; padding:12px 24px; font-size:1rem; border-radius:10px; background:linear-gradient(135deg,#c51332,#e03355); box-shadow:0 4px 15px rgba(197,19,50,.35); border:none; color:#fff; font-weight:700; cursor:pointer; transition:all .2s;">
                        <i class="fa-solid fa-id-card" style="margin-right:6px;"></i>Verify &amp; Continue
                    </button>
                </div>
            </div>
            <div id="forgotStep2" class="forgot-step" style="display: none;">
                <div class="fp-user-info-header" style="background: #f8fafc; padding: 1.25rem; border-radius: 15px; margin-bottom: 1.5rem; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); display:none;">
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <div class="fp-id-row" style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 8px; border-bottom: 1px dashed #e2e8f0; margin-bottom: 4px;">
                            <span style="font-size: 0.85rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.025em;">Account ID:</span>
                            <span class="fp-val-id" style="font-family: 'Monaco', 'Consolas', monospace; font-weight: 800; color: #1e293b; background: #f1f5f9; padding: 4px 10px; border-radius: 6px; font-size: 1rem;"></span>
                        </div>
                        <div class="fp-extra-info" style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.85rem; font-weight: 700; color: #64748b;">Username:</span>
                            <span class="fp-val-username" style="font-weight: 600; color: #334155;"></span>
                        </div>
                        <div class="fp-extra-info" style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.85rem; font-weight: 700; color: #64748b;">Email Address:</span>
                            <span class="fp-val-email" style="font-weight: 600; color: #334155;"></span>
                        </div>
                    </div>
                </div>

                <p class="forgot-email-hint">We will send a 6-digit code to the email above.</p>
                <div style="display:flex; justify-content:center; margin-top:.5rem;">
                    <button type="button" id="forgotSendOtpBtn" class="submitBtn" style="width:100%; max-width:260px; padding:12px 24px; font-size:1rem; border-radius:10px; background:linear-gradient(135deg,#c51332,#e03355); box-shadow:0 4px 15px rgba(197,19,50,.35); border:none; color:#fff; font-weight:700; cursor:pointer; transition:all .2s;">
                        <i class="fa-solid fa-envelope" style="margin-right:6px;"></i>Send OTP
                    </button>
                </div>
                <span id="forgotOtpSentTo" class="forgot-email-sent" style="display: none;"></span>
                <div id="forgotOtpInputWrap" style="display: none;">
                    <label for="forgot_otp">Enter 6-digit code:</label>
                    <div class="password-container">
                        <input type="password" id="forgot_otp" maxlength="6" pattern="[0-9]*" inputmode="numeric" placeholder="000000" style="padding-right: 45px;">
                        <svg id="toggleForgotOtp" class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="cursor:pointer;" onclick="toggleForgotOtpVisibility()">
                            <path d="M1 12s4.5-8 11-8 11 8 11 8-4.5 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                    </div>
                    <span id="forgotResendHint" class="forgot-resend-hint"></span>
                    <div style="display:flex; gap:.5rem; justify-content:center; margin-top:.75rem;">
                        <button type="button" id="forgotResendOtpBtn" class="btn-secondary" disabled style="flex:1; max-width:130px;">Resend OTP (60s)</button>
                        <button type="button" id="forgotVerifyOtpBtn" class="submitBtn" style="flex:1; max-width:130px; padding:10px 16px; font-size:.9rem; border-radius:10px; background:linear-gradient(135deg,#c51332,#e03355); box-shadow:0 4px 15px rgba(197,19,50,.35); border:none; color:#fff; font-weight:700; cursor:pointer;">
                            <i class="fa-solid fa-shield-halved" style="margin-right:4px;"></i>Verify OTP
                        </button>
                    </div>
                </div>
                <span id="forgotStep2Msg" class="forgot-msg"></span>
            </div>
            <div id="forgotStep3" class="forgot-step" style="display: none;">
                <div class="fp-user-info-header" style="background: #f8fafc; padding: 1.25rem; border-radius: 15px; margin-bottom: 1.5rem; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); display:none;">
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <div class="fp-id-row" style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 8px; border-bottom: 1px dashed #e2e8f0; margin-bottom: 4px;">
                            <span style="font-size: 0.85rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.025em;">Account ID:</span>
                            <span class="fp-val-id" style="font-family: 'Monaco', 'Consolas', monospace; font-weight: 800; color: #1e293b; background: #f1f5f9; padding: 4px 10px; border-radius: 6px; font-size: 1rem;"></span>
                        </div>
                        <div class="fp-extra-info" style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.85rem; font-weight: 700; color: #64748b;">Username:</span>
                            <span class="fp-val-username" style="font-weight: 600; color: #334155;"></span>
                        </div>
                        <div class="fp-extra-info" style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.85rem; font-weight: 700; color: #64748b;">Email Address:</span>
                            <span class="fp-val-email" style="font-weight: 600; color: #334155;"></span>
                        </div>
                    </div>
                </div>

                <div class="form-group" style="text-align: left;">
                    <label>Security Question 1</label>
                    <select id="forgotQ1" class="form-control" style="margin-bottom: 10px; width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #cbd5e1;">
                        <option value="">-- Choose a Question --</option>
                        <option value="Who is your bestfriend in elementary?">Who is your bestfriend in elementary?</option>
                        <option value="What is the name of your pet?">What is the name of your pet?</option>
                        <option value="Who is your favorite teacher in highschool?">Who is your favorite teacher in highschool?</option>
                        <option value="What was your first car?">What was your first car?</option>
                        <option value="In what city were you born?">In what city were you born?</option>
                    </select>
                    <div class="password-container">
                        <input type="password" id="secure_answer1" name="secure_answer1" placeholder="Your Answer" style="margin-bottom: 0;">
                        <svg id="toggleForgotAns1" class="eye-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </div>
                </div>

                <div class="form-group" style="text-align: left;">
                    <label>Security Question 2</label>
                    <select id="forgotQ2" class="form-control" style="margin-bottom: 10px; width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #cbd5e1;">
                        <option value="">-- Choose a Question --</option>
                        <option value="What is your mother's maiden name?">What is your mother's maiden name?</option>
                        <option value="What elementary school did you attend?">What elementary school did you attend?</option>
                        <option value="What is your favorite food?">What is your favorite food?</option>
                        <option value="What was your childhood nickname?">What was your childhood nickname?</option>
                        <option value="What is the name of your best friend?">What is the name of your best friend?</option>
                    </select>
                    <div class="password-container">
                        <input type="password" id="secure_answer2" name="secure_answer2" placeholder="Your Answer" style="margin-bottom: 0;">
                        <svg id="toggleForgotAns2" class="eye-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </div>
                </div>

                <div class="form-group" style="text-align: left;">
                    <label>Security Question 3</label>
                    <select id="forgotQ3" class="form-control" style="margin-bottom: 10px; width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #cbd5e1;">
                        <option value="">-- Choose a Question --</option>
                        <option value="What is your father's middle name?">What is your father's middle name?</option>
                        <option value="What street did you grow up on?">What street did you grow up on?</option>
                        <option value="What is your favorite movie?">What is your favorite movie?</option>
                        <option value="What is the name of your first pet?">What is the name of your first pet?</option>
                        <option value="What year did you graduate high school?">What year did you graduate high school?</option>
                    </select>
                    <div class="password-container">
                        <input type="password" id="secure_answer3" name="secure_answer3" placeholder="Your Answer" style="margin-bottom: 0;">
                        <svg id="toggleForgotAns3" class="eye-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </div>
                </div>

                <span id="forgotStep3Msg" class="forgot-msg"></span>
                <div style="display:flex; justify-content:center; margin-top:1rem;">
                    <button type="button" id="forgotStep3Btn" class="submitBtn" style="width:100%; max-width:280px; padding:12px 24px; font-size:1rem; border-radius:10px; background:linear-gradient(135deg,#c51332,#e03355); box-shadow:0 4px 15px rgba(197,19,50,.35); border:none; color:#fff; font-weight:700; cursor:pointer; transition:all .2s;">
                        <i class="fa-solid fa-shield-halved" style="margin-right:6px;"></i>Verify &amp; Set New Password
                    </button>
                </div>
            </div>
            <div id="forgotStep4" class="forgot-step" style="display: none;">
                <div class="fp-user-info-header" style="background: #f8fafc; padding: 1.25rem; border-radius: 15px; margin-bottom: 1.5rem; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); display:none;">
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <div class="fp-id-row" style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 8px; border-bottom: 1px dashed #e2e8f0; margin-bottom: 4px;">
                            <span style="font-size: 0.85rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.025em;">Account ID:</span>
                            <span class="fp-val-id" style="font-family: 'Monaco', 'Consolas', monospace; font-weight: 800; color: #1e293b; background: #f1f5f9; padding: 4px 10px; border-radius: 6px; font-size: 1rem;"></span>
                        </div>
                        <div class="fp-extra-info" style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.85rem; font-weight: 700; color: #64748b;">Username:</span>
                            <span class="fp-val-username" style="font-weight: 600; color: #334155;"></span>
                        </div>
                        <div class="fp-extra-info" style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.85rem; font-weight: 700; color: #64748b;">Email Address:</span>
                            <span class="fp-val-email" style="font-weight: 600; color: #334155;"></span>
                        </div>
                    </div>
                </div>

                <label for="forgot_new_password">New password (8–25 characters):</label>
                <div class="password-container">
                    <input type="password" id="forgot_new_password" minlength="8" maxlength="25" placeholder="New password" autocomplete="new-password">
                    <svg id="toggleForgotNewPassword" class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4.5-8 11-8 11 8 11 8-4.5 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/>
                    </svg>
                </div>
                <span id="forgotPwStrength" class="validation-message" style="display:block; margin-bottom:10px; font-size:0.85em;"></span>

                <label for="forgot_confirm_password">Confirm password:</label>
                <div class="password-container">
                    <input type="password" id="forgot_confirm_password" minlength="8" maxlength="25" placeholder="Confirm" autocomplete="new-password">
                    <svg id="toggleForgotConfirmPassword" class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4.5-8 11-8 11 8 11 8-4.5 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/>
                    </svg>
                </div>
                <span id="forgotPwMatch" class="validation-message" style="display:block; margin-bottom:10px; font-size:0.85em;"></span>

                <span id="forgotStep4Msg" class="forgot-msg"></span>
                <div style="display:flex; justify-content:center; margin-top:1rem;">
                    <button type="button" id="forgotStep4Btn" class="submitBtn" style="width:100%; max-width:260px; padding:12px 24px; font-size:1rem; border-radius:10px; background:linear-gradient(135deg,#c51332,#e03355); box-shadow:0 4px 15px rgba(197,19,50,.35); border:none; color:#fff; font-weight:700; cursor:pointer; transition:all .2s;">
                        <i class="fa-solid fa-key" style="margin-right:6px;"></i>Change Password
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.BASE_URL = '<?php echo $baseUrl; ?>';
        window.LOGIN_API = '../database/login.php';
        window.CHECK_ID_API = '../database/check_id.php';
        window.FORGOT_PASSWORD_API = '../database/forgot_password.php';

        function toggleForgotOtpVisibility() {
            const otpInput = document.getElementById('forgot_otp');
            const icon = document.getElementById('toggleForgotOtp');
            if (otpInput.type === 'password') {
                otpInput.type = 'text';
            } else {
                otpInput.type = 'password';
            }
        }
    </script>
    <script src="../../js/serve_asset.php?file=login.js&v=<?php echo time(); ?>"></script>
</body>
</html>
