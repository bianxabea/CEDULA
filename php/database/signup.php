<?php
session_start();
require '../database/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get input values
    $id = $_POST['id'];
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $middleInitial = $_POST['middleInitial'];
    $extension = $_POST['extension'];
    $purok = $_POST['purok'];
    $barangay = $_POST['barangay'];
    $city = $_POST['city'];
    $province = $_POST['province'];
    $zipCode = $_POST['zipCode'];
    $country = $_POST['country'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $birthdate = $_POST['birthdate'];
    $sex = $_POST['sex'];
    $age = date_diff(date_create($birthdate), date_create('today'))->y;
    $secure_question = $_POST['secure_question'];
    $secure_answerRaw = $_POST['secure_answer'];
    $secure_question2 = $_POST['secure_question2'];
    $secure_answer2Raw = $_POST['secure_answer2'];
    $secure_question3 = $_POST['secure_question3'];
    $secure_answer3Raw = $_POST['secure_answer3'];

    // Hash the user's password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Normalize security answers: trim whitespace + convert to lowercase
    // so the forgot-password check is case-insensitive.
    // Example: user types "My Dog" → stored as hash of "my dog"
    $secure_answerHashed = password_hash(strtolower(trim($secure_answerRaw)), PASSWORD_DEFAULT);
    $secure_answer2Hashed = password_hash(strtolower(trim($secure_answer2Raw)), PASSWORD_DEFAULT);
    $secure_answer3Hashed = password_hash(strtolower(trim($secure_answer3Raw)), PASSWORD_DEFAULT);

    // --- CHECK FOR DUPLICATES (Server-side safety check) ---
    // 1. Check ID
    $stmt = $conn->prepare("SELECT 1 FROM users WHERE id = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo "This ID is already registered.";
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();

    // 2. Check Username
    $stmt = $conn->prepare("SELECT 1 FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo "This username is already taken.";
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();

    // 3. Check Email
    $stmt = $conn->prepare("SELECT 1 FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo "This email is already registered.";
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();

    // --- END DUPLICATE CHECK ---

    // New users are always consumers and start as 'pending' for approval
    $role = 'consumer';
    $status = 'pending';

    // Prepare SQL statement (includes role, status and all security questions)
    $sql = "INSERT INTO users (
                id, firstName, lastName, middleInitial, extension,
                sex, purok, barangay, city, province, zipCode, country,
                username, email, password, birthdate, age,
                secure_question, secure_answer, secure_question2, secure_answer2,
                secure_question3, secure_answer3, role, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param(
            'ssssssssssssssssissssssss', // 25 params (age is 17th)
            $id,
            $firstName,
            $lastName,
            $middleInitial,
            $extension,
            $sex,
            $purok,
            $barangay,
            $city,
            $province,
            $zipCode,
            $country,
            $username,
            $email,
            $hashedPassword,
            $birthdate,
            $age,
            $secure_question,
            $secure_answerHashed,
            $secure_question2,
            $secure_answer2Hashed,
            $secure_question3,
            $secure_answer3Hashed,
            $role,
            $status
        );

        if ($stmt->execute()) {
            // Create a registration request in user_block_requests
            $req_stmt = $conn->prepare("INSERT INTO user_block_requests (requester_id, target_id, reason, request_type, status) VALUES (?, ?, ?, 'registration', 'pending')");
            $reason = "New User Registration";
            $req_stmt->bind_param('sss', $id, $id, $reason);
            $req_stmt->execute();
            $req_stmt->close();

            echo "User successfully registered! Your account is awaiting approval.";
        }
        else {
            if ($conn->errno == 1062) {
                echo "An error occurred: Duplicate entry for a unique field.";
            }
            else {
                echo "Signup failed. Please try again. Error: " . $stmt->error;
            }
        }

        $stmt->close();
    }
    else {
        die("Database Error: " . $conn->error);
    }

    $conn->close();
}
?>