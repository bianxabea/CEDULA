<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../css/serve_asset.php?file=design-system.css">
    <link rel="stylesheet" href="../../css/serve_asset.php?file=signup.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Register - Pizza Crust Delight</title>
</head>

<body class="public-layout">
    <?php include __DIR__ . '/../includes/layout/navbar.php'; ?>

    <main class="page-content" style="display: flex; margin: 0; padding: 0;">
        <div class="left-side">
            <img class="form-img" src="../../images/pizza_login_bg.png" alt="Food Delivery">
        </div>
        <div class="right-side">
            <form class="signup-form" id="signUpForm" method="POST">
            <h2 id="formTitle">Join Pizza Crust Delight</h2>

            <!-- Success Modal -->
            <div id="successModal" class="modal-simple-alert">
                <div class="modal-simple-alert-content">
                    <svg class="success-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="#10B981" width="4em" height="4em">
                        <path d="M256 512C397.4 512 512 397.4 512 256C512 114.6 397.4 0 256 0C114.6 0 0 114.6 0 256C0 397.4 114.6 512 256 512zM369 209L241 337c-9.4 9.4-24.6 9.4-33.9 0l-64-64c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l47 47L335 175c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9z"/>
                    </svg>
                    <span class="modal-simple-alert-text">User successfully registered!</span>
                    <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.5rem;">Redirecting to login...</p>
                </div>
            </div>

            <span class="validation-message" id="serverError"></span>

            <div class="form-step active">
                    <fieldset>
                        <legend>Personal Information (Step 1 of 4)</legend>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="id">Id No<span style="color: red">*</span>:</label>
                                <input type="text" id="id" name="id" placeholder="xxxx-xxxx">
                                <span class="validation-message" id="idError"></span>
                            </div>
                            <div class="form-group">
                                <label for="firstName">First Name<span style="color: red">*</span>:</label>
                                <input type="text" id="firstName" name="firstName" placeholder="">
                                <span class="validation-message" id="firstNameError"></span>
                            </div>
                            <div class="form-group">
                                <label for="lastName">Last Name<span style="color: red">*</span>:</label>
                                <input type="text" id="lastName" name="lastName" placeholder="">
                                <span class="validation-message" id="lastNameError"></span>
                            </div>
                            <div class="form-group">
                                <label for="middleInitial">Initial<span style="color: red"> (optional)</span>:</label>
                                <input type="text" id="middleInitial" name="middleInitial" placeholder="optional">
                                <span class="validation-message" id="middleInitialError"></span>
                            </div>
                            <div class="form-group">
                                <label for="extension">Extension<span style="color: red"> (optional)</span>:</label>
                                <input type="text" id="extension" name="extension" placeholder="optional">
                                <span class="validation-message" id="extensionError"></span>
                            </div>
                            <div class="form-group">
                                <label for="sex">Sex<span style="color: red">*</span>:</label>
                                <select id="sex" name="sex">
                                    <option value="">Sex</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>
                                <span class="validation-message" id="sexError"></span>
                            </div>
                            <div class="form-group">
                                <label for="birthdate">Birthdate<span style="color: red">*</span>:</label>
                                <input type="date" id="birthdate" name="birthdate">
                                <span class="validation-message" id="birthdateError"></span>
                            </div>
                            <div class="form-group">
                                <label for="age">Age:</label>
                                <input type="number" id="age" name="age" readonly>
                            </div>
                        </div>
                    </fieldset>
                </div>

                <div class="form-step">
                    <fieldset>
                        <legend>Address (Step 2 of 4)</legend>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="purok">Purok<span style="color: red">*</span>:</label>
                                <input type="text" id="purok" name="purok" placeholder="">
                                <span class="validation-message" id="purokError"></span>
                            </div>
                            <div class="form-group">
                                <label for="barangay">Barangay<span style="color: red">*</span>:</label>
                                <input type="text" id="barangay" name="barangay" placeholder="">
                                <span class="validation-message" id="barangayError"></span>
                            </div>
                            <div class="form-group">
                                <label for="city">City/Municipality<span style="color: red">*</span>:</label>
                                <input type="text" id="city" name="city" placeholder="">
                                <span class="validation-message" id="cityError"></span>
                            </div>
                            <div class="form-group">
                                <label for="province">Province<span style="color: red">*</span>:</label>
                                <input type="text" id="province" name="province" placeholder="">
                                <span class="validation-message" id="provinceError"></span>
                            </div>
                            <div class="form-group">
                                <label for="zipCode">Zip Code<span style="color: red">*</span>:</label>
                                <input type="number" id="zipCode" name="zipCode" placeholder="">
                                <span class="validation-message" id="zipCodeError"></span>
                            </div>
                            <div class="form-group">
                                <label for="country">Country<span style="color: red">*</span>:</label>
                                <input type="text" id="country" name="country" placeholder="">
                                <span class="validation-message" id="countryError"></span>
                            </div>
                        </div>
                    </fieldset>
                </div>

                <div class="form-step">
                    <fieldset>
                        <legend>Credentials (Step 3 of 4)</legend>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="username">Username<span style="color: red">*</span>:</label>
                                <input type="text" id="username" name="username" placeholder="">
                                <span class="validation-message" id="usernameError"></span>
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address<span style="color: red">*</span>:</label>
                                <input type="email" id="email" name="email" placeholder="">
                                <span class="validation-message" id="emailError"></span>
                            </div>
                            <div class="form-group">
                                <label for="password">Password<span style="color: red">*</span>:</label>
                                <div class="password-container">
                                    <input type="password" id="password" name="password" placeholder="" autocomplete="new-password">
                                    <svg id="togglePassword" class="eye-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </div>
                                <span id="pwStrength"></span>
                                <span class="validation-message" id="passwordError"></span>
                            </div>
                            <div class="form-group">
                                <label for="repassword">Re-enter Password<span style="color: red">*</span>:</label>
                                <div class="password-container">
                                    <input type="password" id="repassword" name="repassword" placeholder="" autocomplete="new-password">
                                    <svg id="toggleRePassword" class="eye-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </div>
                                <span id="pwMatch"></span>
                                <span class="validation-message" id="repasswordError"></span>
                            </div>
                        </div>
                    </fieldset>
                </div>

                <div class="form-step">
                    <fieldset>
                        <legend>Security Questions (Step 4 of 4)</legend>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="secure_question">Security Question 1<span style="color: red">*</span>:</label>
                                <select id="secure_question" name="secure_question">
                                    <option value="">-- Choose a Question --</option>
                                    <option value="Who is your bestfriend in elementary?">Who is your bestfriend in elementary?</option>
                                    <option value="What is the name of your pet?">What is the name of your pet?</option>
                                    <option value="Who is your favorite teacher in highschool?">Who is your favorite teacher in highschool?</option>
                                    <option value="What was your first car?">What was your first car?</option>
                                    <option value="In what city were you born?">In what city were you born?</option>
                                </select>
                                <span class="validation-message" id="secure_questionError"></span>
                            </div>

                            <div class="form-group">
                                <label for="secure_answer">Your Answer 1<span style="color: red">*</span>:</label>
                                <div class="password-container">
                                    <input type="password" id="secure_answer" name="secure_answer" placeholder="Enter your answer">
                                    <svg id="toggleSecureAnswer1" class="eye-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </div>
                                <span class="validation-message" id="secure_answerError"></span>
                            </div>

                            <div class="form-group">
                                <label for="secure_question2">Security Question 2<span style="color: red">*</span>:</label>
                                <select id="secure_question2" name="secure_question2">
                                    <option value="">-- Choose a Question --</option>
                                    <option value="What is your mother's maiden name?">What is your mother's maiden name?</option>
                                    <option value="What elementary school did you attend?">What elementary school did you attend?</option>
                                    <option value="What is your favorite food?">What is your favorite food?</option>
                                    <option value="What was your childhood nickname?">What was your childhood nickname?</option>
                                    <option value="What is the name of your best friend?">What is the name of your best friend?</option>
                                </select>
                                <span class="validation-message" id="secure_question2Error"></span>
                            </div>

                            <div class="form-group">
                                <label for="secure_answer2">Your Answer 2<span style="color: red">*</span>:</label>
                                <div class="password-container">
                                    <input type="password" id="secure_answer2" name="secure_answer2" placeholder="Enter your answer">
                                    <svg id="toggleSecureAnswer2" class="eye-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </div>
                                <span class="validation-message" id="secure_answer2Error"></span>
                            </div>

                            <div class="form-group">
                                <label for="secure_question3">Security Question 3<span style="color: red">*</span>:</label>
                                <select id="secure_question3" name="secure_question3">
                                    <option value="">-- Choose a Question --</option>
                                    <option value="What is your father's middle name?">What is your father's middle name?</option>
                                    <option value="What street did you grow up on?">What street did you grow up on?</option>
                                    <option value="What is your favorite movie?">What is your favorite movie?</option>
                                    <option value="What is the name of your first pet?">What is the name of your first pet?</option>
                                    <option value="What year did you graduate high school?">What year did you graduate high school?</option>
                                </select>
                                <span class="validation-message" id="secure_question3Error"></span>
                            </div>

                            <div class="form-group">
                                <label for="secure_answer3">Your Answer 3<span style="color: red">*</span>:</label>
                                <div class="password-container">
                                    <input type="password" id="secure_answer3" name="secure_answer3" placeholder="Enter your answer">
                                    <svg id="toggleSecureAnswer3" class="eye-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </div>
                                <span class="validation-message" id="secure_answer3Error"></span>
                            </div>
                        </div>
                    </fieldset>
                </div>

                <div class="form-navigation-buttons">
                    <button type="button" id="prevBtn" class="btn btn-secondary" style="display: none;">Previous</button>
                    <button type="button" id="nextBtn" class="btn btn-primary" style="margin-left: auto;">Next</button>
                    <button type="submit" id="submitBtn" class="btn btn-primary" style="display: none; margin-left: auto;">Submit</button>
                </div>

            </form>
        </div>
    </main>

    <?php include __DIR__ . '/../includes/layout/footer.php'; ?>
    <script src="../../js/serve_asset.php?file=signup.js"></script>
</body>

</html>