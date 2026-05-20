<?php
session_start();
require __DIR__ . '/db_connect.php';

function handleFailedLogin($isUsernameEmpty, $isPwEmpty, &$response, $usernameOrEmail = '')
{
    $_SESSION['failed_attempts']++;
    $response['failed_attempts'] = $_SESSION['failed_attempts'];

    // Lockout durations in seconds for specific failed attempts
    $lockoutDurations = [3 => 15, 6 => 30, 9 => 60]; // 9 attempts = 60 seconds

    // Set lockout time if failed attempts reach specific thresholds
    if (array_key_exists($_SESSION['failed_attempts'], $lockoutDurations)) {
        $_SESSION['lockout_time'] = time() + $lockoutDurations[$_SESSION['failed_attempts']];

    // *** THIS IS THE FIX for 60-second timer ***
    // Use 60s value for 9 or more attempts
    }
    elseif ($_SESSION['failed_attempts'] >= 9) {
        $_SESSION['lockout_time'] = time() + $lockoutDurations[9]; // Was hard-coded to 15
    }

    // Return appropriate error messages
    if ($isUsernameEmpty) {
        $response['requireUsername'] = 'Username is required.';
    }

    if ($isPwEmpty) {
        $response['requirePw'] = 'Password is required.';
    }

    if (!$isUsernameEmpty && !$isPwEmpty) {
        $response['error'] = "Password did not match, try again.";
    }

    $response['lockout_time'] = $_SESSION['lockout_time'];
}



// *** THIS IS THE FIX for the JSON Error ***
// First, check what kind of request this is.
// JS FormData sends booleans as strings "true" and "false"
$isFormSubmission = isset($_POST['isForm']) && $_POST['isForm'] === 'true';

if ($isFormSubmission) {
    // --- START: LOGIN FORM LOGIC ---

    // Initialize or update session variables
    if (!isset($_SESSION['failed_attempts'])) {
        $_SESSION['failed_attempts'] = 0;
        $_SESSION['lockout_time'] = 0;
    }

    $response = [
        'error' => '',
        'requirePw' => '',
        'requireUsername' => '',
        'failed_attempts' => $_SESSION['failed_attempts'],
        'lockout_time' => $_SESSION['lockout_time']
    ];

    $usernameOrEmail = isset($_POST['username']) ? trim((string)$_POST['username']) : '';
    $password = isset($_POST['password']) ? (string)$_POST['password'] : '';
    $isPwEmpty = $password === '';
    $isUsernameEmpty = $usernameOrEmail === '';

    // If username or password is empty, return corresponding error messages
    if ($isPwEmpty || $isUsernameEmpty) {
        handleFailedLogin($isUsernameEmpty, $isPwEmpty, $response, $usernameOrEmail);
        echo json_encode($response);
        exit;
    }

    $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param('ss', $usernameOrEmail, $usernameOrEmail);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $storedHash = $user['password'] ?? '';
            if ($storedHash !== '' && password_verify($password, $storedHash)) {
                // Check if user is blocked or pending approval
                if ((int)($user['is_blocked'] ?? 0) === 1) {
                    $role = $user['role'] ?? 'consumer';
                    if ($role === 'consumer') {
                        $response['error'] = "Your account has been blocked. Please contact admin or superadmin.";
                    } else {
                        $response['error'] = "Your account has been blocked. Please contact superadmin.";
                    }
                    echo json_encode($response);
                    exit;
                }

                // AMORA-style: is_blocked=2 means pending approval
                if ((int)($user['is_blocked'] ?? 0) === 2) {
                    $response['error'] = "Your account is pending approval. Please wait for an administrator to approve it.";
                    echo json_encode($response);
                    exit;
                }

                $status = $user['status'] ?? '';
                // Treat empty string (from invalid ENUM values like 'registered') as approved
                if ($status === '') $status = 'approved';
                if ($status === 'pending') {
                    $role = $user['role'] ?? 'consumer';
                    // Same logic: only superadmin can approve admin accounts
                    if ($role === 'consumer') {
                        $response['error'] = "Your account is awaiting approval. Please contact admin or superadmin.";
                    } else {
                        $response['error'] = "Your account is awaiting approval. Please contact superadmin.";
                    }
                    echo json_encode($response);
                    exit;
                }
                elseif ($status === 'rejected') {
                    $response['error'] = "Your registration request has been rejected. Please contact support.";
                    echo json_encode($response);
                    exit;
                }

                if (!array_key_exists('role', $user)) {
                    $user['role'] = 'consumer';
                }
                $_SESSION['user'] = $user;
                $_SESSION['failed_attempts'] = 0;
                $_SESSION['lockout_time'] = 0; // Reset lockout on success

                // Log successful login
                require_once __DIR__ . '/../includes/auth.php';
                logUserAction($user['id'], 'login');

                $base = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/CEDULA';

                // Check for missing security questions (First Login Setup)
                if (empty($user['secure_question'])) {
                    $response['redirect'] = $base . '/php/forms/security_setup.php';
                }
                // Role-based redirection
                elseif ($user['role'] === 'admin') {
                    $response['redirect'] = $base . '/php/admin/index.php';
                }
                elseif ($user['role'] === 'superadmin') {
                    $response['redirect'] = $base . '/php/superadmin/index.php';
                }
                else {
                    $response['redirect'] = $base . '/php/auth/dashboard.php';
                }
            }
            else {
                handleFailedLogin(false, false, $response, $usernameOrEmail);
            }
        }
        else {
            handleFailedLogin(false, false, $response, $usernameOrEmail);
        }

        $stmt->close();
    }
    else {
        $response['error'] = "Error preparing query.";
    }

    // Always send a valid JSON response for form submissions
    echo json_encode($response);
    exit;

// --- END: LOGIN FORM LOGIC ---

}
else {
    // --- START: updateRegisterAccess LOGIC ---

    // This logic is for restricting/unrestricting access via .htaccess
    if (!isset($_POST['isRegisterRestrict'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing isRegisterRestrict parameter']);
        exit;
    }

    // Path to .htaccess in the /forms/ directory
    $htaccessPath = $_SERVER['DOCUMENT_ROOT'] . '/CEDULA/php/forms/.htaccess';

    // Restrict access
    if ($_POST['isRegisterRestrict'] === 'true') {
        $htaccessContent = <<<HTACCESS
<FilesMatch "^(homepage|signup)\.php$">
    Order Deny,Allow
    Deny from all
</FilesMatch>
HTACCESS;
        file_put_contents($htaccessPath, $htaccessContent);
        echo json_encode(['status' => 'File access restricted']);

    // Unrestrict access
    }
    else {
        if (file_exists($htaccessPath)) {
            unlink($htaccessPath); // Remove .htaccess file to unrestrict access
        }
        echo json_encode(['status' => 'File access unrestricted']);
    }
    exit; // Important: exit after handling this request.

// --- END: updateRegisterAccess LOGIC ---
}
?>