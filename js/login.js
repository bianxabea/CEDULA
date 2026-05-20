(function () {
    window.BASE_URL = window.BASE_URL || 'http://localhost/CEDULA';
    window.LOGIN_API = window.LOGIN_API || window.BASE_URL + '/php/database/login.php';
    window.CHECK_ID_API = window.CHECK_ID_API || window.BASE_URL + '/php/database/check_id.php';
})();
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('loginForm');
    const modal = document.getElementById('validationModal');
    const validationMessage = document.getElementById('validationMess');
    const countdownElement = document.getElementById('countdown');
    const registerLink = document.getElementById('register');
    const homeLink = document.getElementById('home');
    const toggleIcon = document.getElementById('togglePassword');
    const forgotPwLink = document.querySelector('.forgot-pw');

    // *** NEW MODAL ELEMENTS ***
    const userNotFoundModal = document.getElementById('userNotFoundModal');
    const userNotFoundOkBtn = document.getElementById('userNotFoundOkBtn');

    // Access control check (Lockout logic)
    async function updateRegisterAccess(isRestricted) {
        try {
            const formData = new FormData();
            formData.append('isForm', false);
            formData.append('isRegisterRestrict', isRestricted);

            const response = await fetch(window.LOGIN_API, {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                console.error('Failed to update access');
            }
        } catch (error) {
            console.error('Error updating access:', error);
        }
    }

    function showModal(failedAttempts, lockoutTime) {
        localStorage.setItem('failedAttempts', failedAttempts);
        toggleForgotPwLink(failedAttempts);

        // Only show the lockout modal if there is actually an active lockout
        if (!lockoutTime || lockoutTime <= Math.floor(Date.now() / 1000)) {
            return; // No active lockout, don't show modal
        }

        const remainingTime = Math.max(0, lockoutTime - Math.floor(Date.now() / 1000));
        countdownElement.textContent = remainingTime;
        modal.style.display = 'flex';
        disableFormAndRegister();
        window.addEventListener('pointermove', disableBackButton);
        localStorage.setItem('lockoutTime', lockoutTime);

        const interval = setInterval(() => {
            const newTime = Math.max(0, lockoutTime - Math.floor(Date.now() / 1000));
            countdownElement.textContent = newTime;
            if (newTime <= 0) {
                clearInterval(interval);
                modal.style.display = 'none';
                enableFormAndRegister();
                localStorage.removeItem('lockoutTime');
                localStorage.removeItem('failedAttempts');
                window.removeEventListener('pointermove', disableBackButton);
            }
        }, 1000);
    }

    function toggleForgotPwLink(failedAttempts) {
        if (failedAttempts >= 2) {
            forgotPwLink.style.display = 'block';
        } else {
            forgotPwLink.style.display = 'none';
        }
    }

    function enableFormAndRegister() {
        updateRegisterAccess(false);
        form.querySelectorAll('input, button').forEach((element) => {
            element.disabled = false;
            if (element.type === 'submit') {
                element.classList.remove('submitBtnDisabled');
                element.classList.add('submitBtn');
            }
        });
        if (registerLink) {
            registerLink.style.pointerEvents = 'auto';
            registerLink.setAttribute('href', '../forms/signup.php');
            registerLink.classList.remove('disabled-link');
            registerLink.classList.add('nav-link');
        }
        if (homeLink) {
            homeLink.style.pointerEvents = 'auto';
            homeLink.setAttribute('href', '../forms/homepage.php');
            homeLink.classList.remove('disabled-link');
            homeLink.classList.add('nav-link');
        }
        if (forgotPwLink) {
            forgotPwLink.setAttribute('href', '#');
            forgotPwLink.style.pointerEvents = 'auto';
            forgotPwLink.classList.remove('disabled-link');
            forgotPwLink.classList.add('forgot-pw');
        }
    }

    const disableBackButton = () => {
        history.pushState(null, null, location.href);
        window.onpopstate = function () {
            history.go(1);
        };
    };

    function disableFormAndRegister() {
        updateRegisterAccess(true);
        form.querySelectorAll('input, button').forEach((element) => {
            element.disabled = true;
            if (element.type === 'submit') {
                element.classList.remove('submitBtn');
                element.classList.add('submitBtnDisabled');
            }
        });
        if (registerLink) {
            registerLink.style.pointerEvents = 'none';
            registerLink.classList.remove('nav-link');
            registerLink.classList.add('disabled-link');
            registerLink.removeAttribute('href');
        }
        if (homeLink) {
            homeLink.style.pointerEvents = 'none';
            homeLink.classList.remove('nav-link');
            homeLink.classList.add('disabled-link');
            homeLink.removeAttribute('href');
        }
        if (forgotPwLink) {
            forgotPwLink.removeAttribute('href');
            forgotPwLink.style.pointerEvents = 'none';
            forgotPwLink.classList.remove('forgot-pw');
            forgotPwLink.classList.add('disabled-link');
        }
    }

    window.togglePassword = function () {
        const passwordInput = document.getElementById('password');
        const isPasswordVisible = passwordInput.type === 'text';
        passwordInput.type = isPasswordVisible ? 'password' : 'text';
        toggleIcon.style.fill = isPasswordVisible ? 'none' : 'currentColor';
    }

    // Login Form Submit
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const usernameValidationMessage = document.getElementById('validationMess');
        const passwordValidationMessage = document.getElementById('validationMessPw');

        usernameValidationMessage.innerText = '';
        passwordValidationMessage.innerText = '';

        const formData = new FormData(form);
        formData.append('isForm', 'true');
        formData.append('isRegisterRestrict', 'false');

        try {
            const response = await fetch(window.LOGIN_API, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });

            const text = await response.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('Login response not JSON:', text);
                passwordValidationMessage.style.display = 'block';
                passwordValidationMessage.innerText = 'Server error. Please try again.';
                return;
            }

            if (data.requireUsername) {
                usernameValidationMessage.style.display = 'block';
                usernameValidationMessage.innerText = data.requireUsername;
                showModal(data.failed_attempts, data.lockout_time);
            }
            if (data.requirePw) {
                passwordValidationMessage.style.display = 'block';
                passwordValidationMessage.innerText = data.requirePw;
                showModal(data.failed_attempts, data.lockout_time);
            }
            if (data.error) {
                passwordValidationMessage.style.display = 'block';
                passwordValidationMessage.innerText = data.error;
                showModal(data.failed_attempts, data.lockout_time);
            }
            if (data.redirect) {
                localStorage.clear();
                window.location.href = data.redirect;
            }
        } catch (error) {
            console.error('Login request failed:', error);
            passwordValidationMessage.style.display = 'block';
            passwordValidationMessage.innerText = 'Network error or server unavailable. Please try again.';
        }
    });

    // Page Load Init — check both PHP data attributes and localStorage
    let initLockoutTime = null;
    let initFailedAttempts = 0;

    // First priority: server-side lockout data (survives session, more reliable)
    if (modal && modal.dataset.lockoutActive === 'true') {
        initLockoutTime = parseInt(modal.dataset.lockoutTime, 10);
        initFailedAttempts = parseInt(modal.dataset.failedAttempts || 0, 10);
    }
    // Fallback: localStorage (survives page reload within same browser)
    if (!initLockoutTime) {
        const storedLockoutTime = localStorage.getItem('lockoutTime');
        const storedFailedAttempts = localStorage.getItem('failedAttempts');
        if (storedLockoutTime) {
            initLockoutTime = parseInt(storedLockoutTime, 10);
            initFailedAttempts = parseInt(storedFailedAttempts || 0, 10);
        }
    }

    if (initLockoutTime && initLockoutTime > Math.floor(Date.now() / 1000)) {
        showModal(initFailedAttempts, initLockoutTime);
    } else {
        // Lockout expired, clean up
        localStorage.removeItem('lockoutTime');
    }
    toggleForgotPwLink(initFailedAttempts);

    // --- FORGOT PASSWORD LOGIC (OTP flow when FORGOT_PASSWORD_API is set) ---
    var modal2 = document.getElementById("forgotPasswordModal");
    var forgotPasswordLink = document.getElementById("forgotPasswordLink");
    var resetIdInput = document.getElementById("reset_id");
    var displayIdSpan = document.getElementById("display_id");
    var displayUsernameSpan = document.getElementById("display_username");

    // Step 3 Variables
    var secureQuestionLabel1 = document.getElementById("secure_question_label1");
    var secureQuestionLabel2 = document.getElementById("secure_question_label2");
    var secureQuestionLabel3 = document.getElementById("secure_question_label3");
    var secureAnswer1 = document.getElementById("secure_answer1");
    var secureAnswer2 = document.getElementById("secure_answer2");
    var secureAnswer3 = document.getElementById("secure_answer3");

    // Step 4 Variables
    var newPwInput = document.getElementById("forgot_new_password");
    var confirmPwInput = document.getElementById("forgot_confirm_password");
    var pwStrengthSpan = document.getElementById("forgotPwStrength");
    var pwMatchSpan = document.getElementById("forgotPwMatch");
    var toggleNewPw = document.getElementById("toggleForgotNewPassword");
    var toggleConfirmPw = document.getElementById("toggleForgotConfirmPassword");

    if (userNotFoundOkBtn) {
        userNotFoundOkBtn.onclick = function () {
            userNotFoundModal.style.display = 'none';
        };
    }

    var closeModalEl = document.getElementById("forgotModalClose") || document.querySelector("#forgotPasswordModal .close");
    if (closeModalEl) closeModalEl.onclick = function () { modal2.style.display = "none"; };
    window.onclick = function (event) {
        if (event.target === modal2) modal2.style.display = "none";
    };

    // Password Strength Checker (Step 4)
    function checkForgotPwStrength() {
        if (!newPwInput || !pwStrengthSpan) return;
        var val = newPwInput.value;
        if (val.length < 8) {
            pwStrengthSpan.textContent = "Too short"; pwStrengthSpan.style.color = "#c00"; return;
        }
        var score = 0;
        if (/[A-Z]/.test(val)) score++;
        if (/[a-z]/.test(val)) score++;
        if (/\d/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        if (score === 4) {
            pwStrengthSpan.textContent = "Strong Password"; pwStrengthSpan.style.color = "rgb(5, 172, 33)";
        } else if (score >= 2) {
            pwStrengthSpan.textContent = "Medium Password"; pwStrengthSpan.style.color = "#ff8c00";
        } else {
            pwStrengthSpan.textContent = "Weak Password"; pwStrengthSpan.style.color = "#f50606";
        }
    }

    function checkForgotPwMatch() {
        if (!newPwInput || !confirmPwInput || !pwMatchSpan) return;
        if (newPwInput.value !== confirmPwInput.value) {
            pwMatchSpan.textContent = "Passwords do not match"; pwMatchSpan.style.color = "#f50606";
        } else {
            pwMatchSpan.textContent = "Passwords matched"; pwMatchSpan.style.color = "rgb(5, 172, 33)";
        }
    }

    if (newPwInput) newPwInput.addEventListener('input', checkForgotPwStrength);
    if (confirmPwInput) confirmPwInput.addEventListener('input', checkForgotPwMatch);
    if (newPwInput) newPwInput.addEventListener('input', checkForgotPwMatch); // Check match on new pw change too

    if (toggleNewPw) toggleNewPw.onclick = function () {
        newPwInput.type = newPwInput.type === 'password' ? 'text' : 'password';
    };
    if (toggleConfirmPw) toggleConfirmPw.onclick = function () {
        confirmPwInput.type = confirmPwInput.type === 'password' ? 'text' : 'password';
    };

    const toggleForgotOtp = document.getElementById('toggleForgotOtp');
    if (toggleForgotOtp) {
        toggleForgotOtp.onclick = function() {
            const otpInp = document.getElementById('forgot_otp');
            otpInp.type = otpInp.type === 'password' ? 'text' : 'password';
        };
    }

    // Security answer show/hide toggles (Step 3)
    [['toggleForgotAns1','secure_answer1'],['toggleForgotAns2','secure_answer2'],['toggleForgotAns3','secure_answer3']].forEach(function(pair) {
        var btn = document.getElementById(pair[0]);
        var inp = document.getElementById(pair[1]);
        if (btn && inp) {
            btn.onclick = function () {
                inp.type = inp.type === 'password' ? 'text' : 'password';
                // Swap between open/closed eye SVG paths
                btn.innerHTML = inp.type === 'text'
                    ? '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>'
                    : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
            };
        }
    });

    if (window.FORGOT_PASSWORD_API && document.getElementById("forgotStep1")) {
        var step1 = document.getElementById("forgotStep1");
        var step2 = document.getElementById("forgotStep2");
        var step3 = document.getElementById("forgotStep3");
        var step4 = document.getElementById("forgotStep4");
        var stepTitle = document.getElementById("forgotStepTitle");
        var step1Msg = document.getElementById("forgotStep1Msg");
        var step2Msg = document.getElementById("forgotStep2Msg");
        var step3Msg = document.getElementById("forgotStep3Msg");
        var step4Msg = document.getElementById("forgotStep4Msg");
        var sendOtpBtn = document.getElementById("forgotSendOtpBtn");
        var otpSentTo = document.getElementById("forgotOtpSentTo");
        var otpInputWrap = document.getElementById("forgotOtpInputWrap");
        var forgotOtpInput = document.getElementById("forgot_otp");
        var resendHint = document.getElementById("forgotResendHint");
        var resendOtpBtn = document.getElementById("forgotResendOtpBtn");
        var verifyOtpBtn = document.getElementById("forgotVerifyOtpBtn");
        var step3Btn = document.getElementById("forgotStep3Btn");
        var step4Btn = document.getElementById("forgotStep4Btn");
        var headers = document.querySelectorAll('.fp-user-info-header');
        var resendCountdown = null;

        function showStep(n) {
            [step1, step2, step3, step4].forEach(function (s) { if (s) s.style.display = "none"; });
            step1Msg.textContent = ""; step2Msg.textContent = ""; step3Msg.textContent = ""; step4Msg.textContent = "";
            var titles = ["Step 1: Verify your User ID", "Step 2: Account Confirmation", "Step 3: Answer Security Questions", "Step 4: Set new password"];
            if (stepTitle) stepTitle.textContent = titles[n - 1] || "";
            var el = [step1, step2, step3, step4][n - 1];
            if (el) el.style.display = "block";

            if (n > 1) {
                headers.forEach(h => {
                    h.style.display = 'block';
                    const extras = h.querySelectorAll('.fp-extra-info');
                    // Show username and email in all steps for clarity
                    extras.forEach(ex => ex.style.display = 'flex');

                    // Show ID row in Account Confirmation (Step 2) and Set New Password (Step 4)
                    const idRows = h.querySelectorAll('.fp-id-row');
                    idRows.forEach(row => row.style.display = (n === 2 || n === 4) ? 'flex' : 'none');
                });
            } else {
                headers.forEach(h => h.style.display = 'none');
            }
        }

        function clearResendTimer() {
            if (resendCountdown) { clearInterval(resendCountdown); resendCountdown = null; }
            if (resendOtpBtn) { resendOtpBtn.disabled = false; resendOtpBtn.textContent = "Resend OTP"; }
            if (resendHint) resendHint.textContent = "";
        }

        forgotPasswordLink.onclick = function () {
            modal2.style.display = "flex";
            resetIdInput.value = "";
            if (forgotOtpInput) forgotOtpInput.value = "";

            // Clear inputs
            if (secureAnswer1) secureAnswer1.value = "";
            if (secureAnswer2) secureAnswer2.value = "";
            if (secureAnswer3) secureAnswer3.value = "";

            if (newPwInput) newPwInput.value = "";
            if (confirmPwInput) confirmPwInput.value = "";
            if (pwStrengthSpan) pwStrengthSpan.textContent = "";
            if (pwMatchSpan) pwMatchSpan.textContent = "";

            clearResendTimer();
            if (otpSentTo) otpSentTo.style.display = "none";
            if (otpInputWrap) otpInputWrap.style.display = "none";

            // Reset Send Button and Hint visibility
            if (sendOtpBtn) sendOtpBtn.style.display = "block";
            var hint = document.querySelector(".forgot-email-hint");
            if (hint) hint.style.display = "block";

            showStep(1);
            setTimeout(function () { resetIdInput.focus(); }, 100);
        };

        function formatIdInput(event) {
            var input = event.target;
            var value = input.value.replace(/\D/g, ''); // Remove non-digits
            if (value.length > 4) {
                value = value.slice(0, 4) + '-' + value.slice(4, 8); // Auto-add dash and limit length
            }
            input.value = value;
        }

        if (resetIdInput) {
            resetIdInput.addEventListener('input', formatIdInput);
        }

        document.getElementById("forgotStep1Btn").onclick = function () {
            var id = (resetIdInput.value || "").trim();
            step1Msg.textContent = "";

            if (!id) {
                step1Msg.textContent = "Enter your User ID.";
                step1Msg.style.color = "#c00";
                return;
            }

            // Format validation from Register page
            var idPattern = /^[0-9]{4}-[0-9]{4}$/;
            if (!idPattern.test(id)) {
                step1Msg.textContent = "The ID must be in the format xxxx-xxxx.";
                step1Msg.style.color = "#c00";
                return;
            }

            var fd = new FormData(); fd.append("action", "verify_user_id"); fd.append("user_id", id);
            fetch(window.FORGOT_PASSWORD_API, { method: "POST", body: fd })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.status === "success" || data.success === true) {
                        const user = data.user || data;
                        const userId = user.id || data.user_id;
                        const username = user.username || "";
                        const email = user.email || "";

                        // Populate persistent headers with labeled layout
                        document.querySelectorAll('.fp-val-id').forEach(el => el.textContent = userId);
                        document.querySelectorAll('.fp-val-username').forEach(el => el.textContent = '@' + username);
                        document.querySelectorAll('.fp-val-email').forEach(el => el.textContent = email);

                        showStep(2);
                    } else {
                        step1Msg.textContent = data.message || "User ID not found.";
                        step1Msg.style.color = "#c00";
                    }
                })
                .catch(function () { step1Msg.textContent = "Network error."; step1Msg.style.color = "#c00"; });
        };

        sendOtpBtn.onclick = function () {
            sendOtpBtn.disabled = true;
            var originalText = sendOtpBtn.textContent;
            sendOtpBtn.textContent = "Sending...";

            var fd = new FormData(); fd.append("action", "send_otp");
            fetch(window.FORGOT_PASSWORD_API, { method: "POST", body: fd })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    sendOtpBtn.disabled = false;
                    sendOtpBtn.textContent = originalText;
                    if (data.status === "success" || data.status === "existing_otp") {
                        // Hide send button and hint
                        sendOtpBtn.style.display = "none";
                        var hint = document.querySelector(".forgot-email-hint");
                        if (hint) hint.style.display = "none";

                        // Show OTP inputs
                        if (otpInputWrap) otpInputWrap.style.display = "block";
                        if (forgotOtpInput) forgotOtpInput.value = "";

                        // Timer logic
                        var sec = data.remaining_seconds || 60;
                        if (resendOtpBtn) { resendOtpBtn.disabled = true; resendOtpBtn.textContent = "Resend OTP (" + sec + "s)"; }
                        clearResendTimer();
                        resendCountdown = setInterval(function () {
                            sec--;
                            if (sec <= 0) { clearResendTimer(); return; }
                            if (resendOtpBtn) resendOtpBtn.textContent = "Resend OTP (" + sec + "s)";
                        }, 1000);

                        // Show validation message BELOW Verify OTP button (using step2Msg)
                        step2Msg.textContent = "Code sent to " + (data.email || "your email") + ".";
                        step2Msg.style.color = "#0a0"; // Green

                        // If it's an existing OTP or error-like success, handle color?
                        if (data.status === "existing_otp") {
                            step2Msg.textContent = "Code already sent. Please check your email.";
                            step2Msg.style.color = "#c00"; // Red as per user request ("if already sent... red")
                        }
                    } else {
                        step2Msg.textContent = data.message || "Could not send OTP.";
                        step2Msg.style.color = "#c00";
                    }
                })
                .catch(function () { step2Msg.textContent = "Network error."; step2Msg.style.color = "#c00"; });
        };

        if (resendOtpBtn) {
            resendOtpBtn.onclick = function () {
                step2Msg.textContent = "";
                resendOtpBtn.disabled = true;
                resendOtpBtn.textContent = "Sending...";

                var fd = new FormData(); fd.append("action", "send_otp");
                fetch(window.FORGOT_PASSWORD_API, { method: "POST", body: fd })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.status === "success" || data.status === "existing_otp") {
                            // Timer logic
                            var sec = data.remaining_seconds || 60;
                            resendOtpBtn.disabled = true;
                            resendOtpBtn.textContent = "Resend OTP (" + sec + "s)";
                            clearResendTimer();
                            resendCountdown = setInterval(function () {
                                sec--;
                                if (sec <= 0) { clearResendTimer(); return; }
                                resendOtpBtn.textContent = "Resend OTP (" + sec + "s)";
                            }, 1000);

                            step2Msg.textContent = data.status === "success" ? "New OTP sent." : "Check your email for the existing OTP.";
                            step2Msg.style.color = data.status === "success" ? "#0a0" : "#c00";
                        } else {
                            resendOtpBtn.disabled = false;
                            resendOtpBtn.textContent = "Resend OTP";
                            step2Msg.textContent = data.message || "Could not resend OTP.";
                            step2Msg.style.color = "#c00";
                        }
                    })
                    .catch(function () {
                        resendOtpBtn.disabled = false;
                        resendOtpBtn.textContent = "Resend OTP";
                        step2Msg.textContent = "Network error.";
                        step2Msg.style.color = "#c00";
                    });
            };
        }

        if (verifyOtpBtn) verifyOtpBtn.onclick = function () {
            var otp = (forgotOtpInput && forgotOtpInput.value || "").trim();
            step2Msg.textContent = "";
            if (otp.length !== 6) { step2Msg.textContent = "Enter the 6-digit code."; step2Msg.style.color = "#c00"; return; }
            var fd = new FormData(); fd.append("action", "verify_otp"); fd.append("otp", otp);
            fetch(window.FORGOT_PASSWORD_API, { method: "POST", body: fd })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.status === "success") {
                        clearResendTimer();
                        showStep(3);
                    } else {
                        step2Msg.textContent = data.message || "Invalid or expired OTP.";
                        step2Msg.style.color = "#c00";
                    }
                })
                .catch(function () { step2Msg.textContent = "Network error."; step2Msg.style.color = "#c00"; });
        };

        if (step3Btn) step3Btn.onclick = function () {
            var q1 = document.getElementById("forgotQ1").value;
            var a1 = (secureAnswer1 && secureAnswer1.value || "").trim();
            var q2 = document.getElementById("forgotQ2").value;
            var a2 = (secureAnswer2 && secureAnswer2.value || "").trim();
            var q3 = document.getElementById("forgotQ3").value;
            var a3 = (secureAnswer3 && secureAnswer3.value || "").trim();
            step3Msg.textContent = "";

            if (!q1 || !a1 || !q2 || !a2 || !q3 || !a3) {
                step3Msg.textContent = "Please select and answer all 3 questions.";
                step3Msg.style.color = "#c00";
                return;
            }

            var fd = new FormData();
            fd.append("action", "verify_security_question");
            fd.append("question1", q1);
            fd.append("answer1", a1);
            fd.append("question2", q2);
            fd.append("answer2", a2);
            fd.append("question3", q3);
            fd.append("answer3", a3);

            fetch(window.FORGOT_PASSWORD_API, { method: "POST", body: fd })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.status === "success") {
                        showStep(4);
                        if (newPwInput) newPwInput.focus();
                    } else {
                        step3Msg.textContent = data.message || "Verification failed.";
                        step3Msg.style.color = "#c00";
                    }
                })
                .catch(function () { step3Msg.textContent = "Network error."; step3Msg.style.color = "#c00"; });
        };

        if (step4Btn) step4Btn.onclick = function () {
            var np = (newPwInput && newPwInput.value) || "";
            var cp = (confirmPwInput && confirmPwInput.value) || "";
            step4Msg.textContent = "";

            if (np.length < 8 || np.length > 25) {
                step4Msg.textContent = "Password must be 8–25 characters.";
                step4Msg.style.color = "#c00";
                return;
            }
            // Check complexity (at least 2 types of characters + special is recommended, but sticking to basic length + match for submit blocking to avoid being too strict if not asked, but user asked for SAME as register so we should probably respect complexity if we can.
            // For now, let's rely on the strength indicator visual and just enforce length/match for submission, unless 'Weak' is strictly forbidden.
            // Register page doesn't explicitly block 'Weak' in code seen, just visual.
            if (np !== cp) {
                // pwMatchSpan already shows the error in real-time, so no need to duplicate text in step4Msg
                return;
            }

            var fd = new FormData();
            fd.append("action", "change_password");
            fd.append("new_password", np);
            fd.append("confirm_password", cp);
            fetch(window.FORGOT_PASSWORD_API, { method: "POST", body: fd })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.status === "success") {
                        step4Msg.textContent = data.message || "Password changed. You can login now.";
                        step4Msg.style.color = "#0a0";
                        setTimeout(function () {
                            modal2.style.display = "none";
                            var vp = document.getElementById("validationMessPw");
                            if (vp) { vp.textContent = "Password changed. You can login now."; vp.style.display = "block"; }
                        }, 1500);
                    } else {
                        step4Msg.textContent = data.message || "Failed to change password.";
                        step4Msg.style.color = "#c00";
                    }
                })
                .catch(function () { step4Msg.textContent = "Network error."; step4Msg.style.color = "#c00"; });
        };
    } else {
        forgotPasswordLink.onclick = function () {
            modal2.style.display = "flex";
            if (resetIdInput) { resetIdInput.value = ""; setTimeout(function () { resetIdInput.focus(); }, 100); }
        };
    }
});