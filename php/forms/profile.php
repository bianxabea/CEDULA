<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$user = $_SESSION['user'];
$userRole = $user['role'] ?? 'consumer';
$pageTitle = 'My Profile';

// Calculate age from birthdate
$age = '';
if (!empty($user['birthdate'])) {
    $birth = new DateTime($user['birthdate']);
    $today = new DateTime();
    $age = $birth->diff($today)->y;
}

$fullName = trim(
    htmlspecialchars($user['firstName'] ?? '') . ' ' .
    htmlspecialchars($user['middleInitial'] ?? '') . ' ' .
    htmlspecialchars($user['lastName'] ?? '') . ' ' .
    htmlspecialchars($user['extension'] ?? '')
);
$initials = '';
if (!empty($user['firstName']))
    $initials .= strtoupper($user['firstName'][0]);
if (!empty($user['lastName']))
    $initials .= strtoupper($user['lastName'][0]);
if (!$initials)
    $initials = '?';

$roleIcons = ['superadmin' => 'fa-crown', 'admin' => 'fa-shield-halved', 'consumer' => 'fa-user'];
$roleIcon = $roleIcons[$userRole] ?? 'fa-user';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Pizza Crust Delight</title>
    <link rel="stylesheet" href="../../css/design-system.css">
    <link rel="stylesheet" href="../../css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* ── New Profile Layout ───────────────────── */
        .profile-page { display: flex; flex-direction: column; gap: 1.5rem; }

        /* ── Cover Banner ── */
        .profile-banner {
            position: relative;
            background: linear-gradient(135deg, #c51332 0%, #8b0d23 60%, #1e293b 100%);
            border-radius: 16px;
            padding: 2.5rem 2rem 1.5rem;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
        }
        .profile-banner::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2V36h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        /* ── Avatar + Name INSIDE Banner ── */
        .profile-identity {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            position: relative;
            z-index: 2;
        }
        .profile-avatar {
            width: 100px; height: 100px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(8px);
            border: 4px solid rgba(255,255,255,0.4);
            display: flex; align-items: center; justify-content: center;
            font-size: 2.5rem; font-weight: 800; color: #fff;
            flex-shrink: 0;
        }
        .profile-name-block h1 { margin: 0; font-size: 1.4rem; font-weight: 800; color: #ffffff; }
        .profile-name-block p { margin: 0.15rem 0 0; font-size: 0.85rem; color: rgba(255,255,255,0.7); }
        .profile-name-block .role-pill {
            display: inline-flex; align-items: center; gap: 0.3rem;
            margin-top: 0.5rem; padding: 0.2rem 0.65rem;
            border-radius: 20px; font-size: 0.75rem; font-weight: 700;
            background: rgba(255,255,255,0.2); color: #ffffff;
        }

        /* ── Two Column Cards Grid ── */
        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
        }
        @media (max-width: 768px) { .profile-grid { grid-template-columns: 1fr; } }

        .profile-card {
            background: var(--bg-card, #fff);
            border-radius: 14px;
            border: 1px solid var(--border-color, #e2e8f0);
            box-shadow: 0 1px 4px rgba(0,0,0,0.04);
            overflow: hidden;
        }
        .profile-card.full-width { grid-column: 1 / -1; }

        .pc-header {
            display: flex; align-items: center; gap: 0.6rem;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border-color, #e2e8f0);
            background: var(--bg-body, #f8fafc);
        }
        .pc-header i { color: #c51332; font-size: 0.95rem; }
        .pc-header h3 { margin: 0; font-size: 0.95rem; font-weight: 700; color: var(--text-heading, #1e293b); }

        .pc-body { padding: 1.25rem; }

        /* Info rows inside cards */
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.6rem 0;
            border-bottom: 1px solid rgba(0,0,0,0.04);
        }
        .info-row:last-child { border-bottom: none; }
        .info-row .ir-label { font-size: 0.82rem; color: var(--text-muted, #64748b); font-weight: 500; }
        .info-row .ir-value { font-size: 0.9rem; color: var(--text-heading, #1e293b); font-weight: 600; text-align: right; }
        .ir-value.empty { color: var(--text-muted, #94a3b8); font-style: italic; font-weight: 400; }

        /* Security Form */
        .pw-field { margin-bottom: 1rem; }
        .pw-field label { display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.3rem; text-transform: uppercase; letter-spacing: 0.5px; }
        .pw-input-wrap { position: relative; }
        .pw-field input {
            width: 100%; padding: 0.6rem 2.5rem 0.6rem 0.75rem;
            border: 1px solid var(--border-color); border-radius: 8px;
            background: var(--bg-body); color: var(--text-heading);
            font-size: 0.9rem; transition: border-color 0.2s;
        }
        .pw-field input:focus { outline: none; border-color: #c51332; box-shadow: 0 0 0 3px rgba(197,19,50,0.1); }
        .pw-toggle-btn {
            position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer; color: var(--text-muted); padding: 0;
        }
        .pw-toggle-btn:hover { color: #c51332; }

        .pw-actions { display: flex; justify-content: flex-end; padding-top: 0.75rem; border-top: 1px solid var(--border-color); margin-top: 0.5rem; }

        @media (max-width: 600px) {
            .profile-identity { flex-direction: column; align-items: center; text-align: center; }
            .profile-name-block { text-align: center; }
        }
    </style>
</head>
<body class="dashboard-layout <?php
$roleClass = 'consumer-theme-v2';
if (isset($user['role'])) {
    if ($user['role'] === 'superadmin')
        $roleClass = 'superadmin-v2';
    elseif ($user['role'] === 'admin')
        $roleClass = 'admin-theme-v2';
}
echo $roleClass;
?>">
    <?php include __DIR__ . '/../includes/layout/navbar.php'; ?>

    <?php $currentPage = 'profile';
include __DIR__ . '/../includes/layout/sidebar.php'; ?>

    <main class="page-content">
        <div class="profile-page">

            <!-- ╔═══ Cover Banner with Avatar + Identity ═══╗ -->
            <div class="profile-banner">
                <div class="profile-identity">
                    <div class="profile-avatar"><?php echo $initials; ?></div>
                    <div class="profile-name-block">
                        <h1><?php echo $fullName; ?></h1>
                        <p>@<?php echo htmlspecialchars($user['username']); ?> &middot; <?php echo htmlspecialchars($user['email']); ?></p>
                        <span class="role-pill">
                            <i class="fa-solid <?php echo $roleIcon; ?>"></i>
                            <?php echo ucfirst($userRole); ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- ╔═══ Cards Grid ═══╗ -->
            <div class="profile-grid">

                <!-- Personal Info Card -->
                <div class="profile-card">
                    <div class="pc-header">
                        <i class="fa-regular fa-id-card"></i>
                        <h3>Personal Information</h3>
                    </div>
                    <div class="pc-body">
                        <div class="info-row">
                            <span class="ir-label">First Name</span>
                            <span class="ir-value"><?php echo htmlspecialchars($user['firstName']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="ir-label">Last Name</span>
                            <span class="ir-value"><?php echo htmlspecialchars($user['lastName']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="ir-label">Middle Initial</span>
                            <span class="ir-value <?php echo empty($user['middleInitial']) ? 'empty' : ''; ?>"><?php echo htmlspecialchars($user['middleInitial'] ?? '') ?: 'Not set'; ?></span>
                        </div>
                        <div class="info-row">
                            <span class="ir-label">Extension</span>
                            <span class="ir-value <?php echo empty($user['extension']) ? 'empty' : ''; ?>"><?php echo htmlspecialchars($user['extension'] ?? '') ?: 'Not set'; ?></span>
                        </div>
                        <div class="info-row">
                            <span class="ir-label">Sex</span>
                            <span class="ir-value"><?php echo ucfirst(htmlspecialchars($user['sex'] ?? 'Not set')); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="ir-label">Birthdate</span>
                            <span class="ir-value"><?php echo !empty($user['birthdate']) ? date('F j, Y', strtotime($user['birthdate'])) : 'Not set'; ?></span>
                        </div>
                        <div class="info-row">
                            <span class="ir-label">Age</span>
                            <span class="ir-value"><?php echo $age ?: 'N/A'; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Address Card -->
                <div class="profile-card">
                    <div class="pc-header">
                        <i class="fa-solid fa-map-location-dot"></i>
                        <h3>Address Details</h3>
                    </div>
                    <div class="pc-body">
                        <div class="info-row">
                            <span class="ir-label">Purok / Street</span>
                            <span class="ir-value <?php echo empty($user['purok']) ? 'empty' : ''; ?>"><?php echo htmlspecialchars($user['purok'] ?? '') ?: 'Not set'; ?></span>
                        </div>
                        <div class="info-row">
                            <span class="ir-label">Barangay</span>
                            <span class="ir-value <?php echo empty($user['barangay']) ? 'empty' : ''; ?>"><?php echo htmlspecialchars($user['barangay'] ?? '') ?: 'Not set'; ?></span>
                        </div>
                        <div class="info-row">
                            <span class="ir-label">City / Municipality</span>
                            <span class="ir-value <?php echo empty($user['city']) ? 'empty' : ''; ?>"><?php echo htmlspecialchars($user['city'] ?? '') ?: 'Not set'; ?></span>
                        </div>
                        <div class="info-row">
                            <span class="ir-label">Province</span>
                            <span class="ir-value <?php echo empty($user['province']) ? 'empty' : ''; ?>"><?php echo htmlspecialchars($user['province'] ?? '') ?: 'Not set'; ?></span>
                        </div>
                        <div class="info-row">
                            <span class="ir-label">Zip Code</span>
                            <span class="ir-value <?php echo empty($user['zipCode']) ? 'empty' : ''; ?>"><?php echo htmlspecialchars($user['zipCode'] ?? '') ?: 'Not set'; ?></span>
                        </div>
                        <div class="info-row">
                            <span class="ir-label">Country</span>
                            <span class="ir-value <?php echo empty($user['country']) ? 'empty' : ''; ?>"><?php echo htmlspecialchars($user['country'] ?? '') ?: 'Not set'; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Account Info Card -->
                <div class="profile-card">
                    <div class="pc-header">
                        <i class="fa-solid fa-at"></i>
                        <h3>Account Details</h3>
                    </div>
                    <div class="pc-body">
                        <div class="info-row">
                            <span class="ir-label">User ID</span>
                            <span class="ir-value" style="font-family: monospace; font-size: 0.85rem;"><?php echo htmlspecialchars($user['id']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="ir-label">Username</span>
                            <span class="ir-value">@<?php echo htmlspecialchars($user['username']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="ir-label">Email</span>
                            <span class="ir-value"><?php echo htmlspecialchars($user['email']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="ir-label">Role</span>
                            <span class="ir-value" style="display:flex; align-items:center; gap:8px;">
                                <?php echo ucfirst($userRole); ?>
                                <button type="button" onclick="viewPrivileges('<?php echo $userRole; ?>')" style="background:none; border:1px solid #c51332; color:#c51332; border-radius:6px; padding:2px 8px; font-size:0.75rem; cursor:pointer; display:flex; align-items:center; gap:4px;">
                                    <i class="fa-solid fa-shield-halved"></i> Privileges
                                </button>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="ir-label">Status</span>
                            <span class="ir-value" style="color: #16a34a;"><i class="fa-solid fa-circle" style="font-size: 0.5rem; vertical-align: middle; margin-right: 0.3rem;"></i>Active</span>
                        </div>
                    </div>
                </div>

                <!-- Security / Change Password Card -->
                <div class="profile-card">
                    <div class="pc-header">
                        <i class="fa-solid fa-lock"></i>
                        <h3>Change Password</h3>
                    </div>
                    <div class="pc-body">
                        <form id="profileForm">
                            <div class="pw-field">
                                <label>Current Password</label>
                                <div class="pw-input-wrap">
                                    <input type="password" name="current_password" id="current_password" required placeholder="Enter current password">
                                    <button type="button" class="pw-toggle-btn" data-target="current_password"><i class="fa-solid fa-eye"></i></button>
                                </div>
                            </div>
                            <div class="pw-field">
                                <label>New Password <span style="font-weight:400; font-size:0.7rem; color:var(--text-muted);">(8-25 characters)</span></label>
                                <div class="pw-input-wrap">
                                    <input type="password" name="new_password" id="new_password" placeholder="Min 8 characters">
                                    <button type="button" class="pw-toggle-btn" data-target="new_password"><i class="fa-solid fa-eye"></i></button>
                                </div>
                                <div id="pwStrength" style="display:none; margin-top:0.35rem; font-size:0.8rem; font-weight:600;"></div>
                                <div id="pwRequirements" style="margin-top:0.35rem; font-size:0.7rem; color:var(--text-muted); line-height:1.6;"></div>
                            </div>
                            <div class="pw-field">
                                <label>Confirm Password</label>
                                <div class="pw-input-wrap">
                                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Re-enter new password">
                                    <button type="button" class="pw-toggle-btn" data-target="confirm_password"><i class="fa-solid fa-eye"></i></button>
                                </div>
                                <div id="pwMatch" style="display:none; margin-top:0.35rem; font-size:0.8rem; font-weight:600;"></div>
                            </div>
                            <p id="pwMsg" style="color: #c51332; font-size: 0.85rem; min-height: 1.25rem; margin: 0.5rem 0;"></p>
                            <div class="pw-actions">
                                <button type="submit" class="submitBtn" style="padding: 0.5rem 1.5rem; font-size: 0.9rem;">
                                    <i class="fa-solid fa-floppy-disk" style="margin-right: 0.4rem;"></i> Update Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div><!-- /profile-grid -->
        </div><!-- /profile-page -->
    </main>
    <?php include __DIR__ . '/../includes/layout/footer.php'; ?>

    <!-- Privileges Modal (AMORA-style) -->
    <div id="privilegesModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:1200; align-items:center; justify-content:center;">
        <div style="background:white; padding:1.75rem; border-radius:14px; width:90%; max-width:420px; box-shadow:0 15px 40px rgba(0,0,0,0.15);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                <h3 style="margin:0; display:flex; align-items:center; gap:8px; color:#1e293b;">
                    <i class="fa-solid fa-shield-halved" style="color:#c51332;"></i>
                    <span id="privRoleTitle">Role</span> Privileges
                </h3>
                <span id="closePrivilegesModal" onclick="document.getElementById('privilegesModal').style.display='none'" style="cursor:pointer; font-size:1.5rem; color:#94a3b8; line-height:1;">&times;</span>
            </div>
            <p style="color:#64748b; font-size:0.875rem; margin:0 0 1rem;">Your accessible modules and permissions:</p>
            <div id="privilegesList" style="display:flex; flex-direction:column; gap:0.5rem; max-height:280px; overflow-y:auto;"></div>
            <div style="text-align:right; margin-top:1.25rem; padding-top:1rem; border-top:1px solid #e2e8f0;">
                <button onclick="document.getElementById('privilegesModal').style.display='none'" class="submitBtn" style="padding:0.45rem 1.25rem; font-size:0.875rem;">Close</button>
            </div>
        </div>
    </div>

    <!-- Message Modal -->
    <div id="messageModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:1100; align-items:center; justify-content:center;">
        <div style="background:white; padding:1.5rem; border-radius:12px; width:90%; max-width:350px; text-align:center; box-shadow:0 10px 25px rgba(0,0,0,0.1);">
            <div id="msgIconContainer" style="width:50px; height:50px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 1rem;">
                <i id="msgIcon" class="fa-solid" style="font-size:1.5rem;"></i>
            </div>
            <h3 id="msgTitle" style="margin-bottom:0.25rem; font-size: 1.1rem;"></h3>
            <p id="msgBody" style="color:var(--text-muted); margin-bottom:1.5rem; font-size: 0.9rem;"></p>
            <button onclick="document.getElementById('messageModal').style.display='none'" class="submitBtn" style="width:100%; padding: 0.5rem;">Okay</button>
        </div>
    </div>

    <script>
        // Sidebar toggle
        (function(){ var o=document.getElementById('sidebarOverlay'),t=document.getElementById('sidebarToggle'); if(t&&o){ t.addEventListener('click',function(){ document.body.classList.toggle('sidebar-open'); o.classList.toggle('is-open',document.body.classList.contains('sidebar-open')); }); o.addEventListener('click',function(){ document.body.classList.remove('sidebar-open'); o.classList.remove('is-open'); }); } })();

        // Password toggle (eye icon)
        document.querySelectorAll('.pw-toggle-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const input = document.getElementById(btn.dataset.target);
                const icon = btn.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.className = 'fa-solid fa-eye-slash';
                } else {
                    input.type = 'password';
                    icon.className = 'fa-solid fa-eye';
                }
            });
        });

        // Password strength checker
        const api = '../../php/database';
        const newPwInput = document.getElementById('new_password');
        const confirmPwInput = document.getElementById('confirm_password');
        const pwStrengthEl = document.getElementById('pwStrength');
        const pwMatchEl = document.getElementById('pwMatch');
        const pwReqEl = document.getElementById('pwRequirements');

        function checkPasswordStrength(password) {
            let score = 0;
            if (password.length >= 8) score++;
            if (/[a-z]/.test(password)) score++;
            if (/[A-Z]/.test(password)) score++;
            if (/[0-9]/.test(password)) score++;
            if (/[^a-zA-Z0-9]/.test(password)) score++;
            return score;
        }

        function updateRequirements(password) {
            const checks = [
                { test: password.length >= 8, label: '8+ chars' },
                { test: /[A-Z]/.test(password), label: 'Uppercase' },
                { test: /[a-z]/.test(password), label: 'Lowercase' },
                { test: /[0-9]/.test(password), label: 'Number' },
                { test: /[^a-zA-Z0-9]/.test(password), label: 'Special' },
            ];
            pwReqEl.innerHTML = checks.map(c =>
                `<span style="display:inline-flex; align-items:center; gap:0.2rem; margin-right:0.5rem; color:${c.test ? '#16a34a' : '#9ca3af'};">
                    <i class="fa-solid ${c.test ? 'fa-circle-check' : 'fa-circle-xmark'}" style="font-size:0.65rem;"></i> ${c.label}
                </span>`
            ).join('');
        }

        newPwInput.addEventListener('input', () => {
            const pw = newPwInput.value;
            if (pw.length === 0) {
                pwStrengthEl.style.display = 'none';
                pwReqEl.innerHTML = '';
            } else {
                const score = checkPasswordStrength(pw);
                pwStrengthEl.style.display = 'block';
                if (score <= 2) { pwStrengthEl.textContent = 'Weak'; pwStrengthEl.style.color = '#f50606'; }
                else if (score === 3) { pwStrengthEl.textContent = 'Medium'; pwStrengthEl.style.color = '#E99002'; }
                else { pwStrengthEl.textContent = 'Strong'; pwStrengthEl.style.color = '#2BB673'; }
                updateRequirements(pw);
            }
            checkPasswordMatch();
        });

        function checkPasswordMatch() {
            const newPw = newPwInput.value;
            const confirmPw = confirmPwInput.value;
            if (confirmPw.length > 0 || newPw.length > 0) {
                pwMatchEl.style.display = 'block';
                if (newPw === confirmPw && newPw.length > 0) {
                    pwMatchEl.textContent = 'Passwords match';
                    pwMatchEl.style.color = '#2BB673';
                } else {
                    pwMatchEl.textContent = 'Passwords do not match';
                    pwMatchEl.style.color = '#f50606';
                }
            } else {
                pwMatchEl.style.display = 'none';
            }
        }
        confirmPwInput.addEventListener('input', checkPasswordMatch);

        function showMessageModal(type, title, message) {
            const modal = document.getElementById('messageModal');
            const iconContainer = document.getElementById('msgIconContainer');
            const icon = document.getElementById('msgIcon');
            const titleEl = document.getElementById('msgTitle');
            const bodyEl = document.getElementById('msgBody');
            titleEl.textContent = title;
            bodyEl.textContent = message;
            if (type === 'success') {
                iconContainer.style.background = '#dcfce7';
                icon.className = 'fa-solid fa-check'; icon.style.color = '#16a34a'; titleEl.style.color = '#16a34a';
            } else {
                iconContainer.style.background = '#fee2e2';
                icon.className = 'fa-solid fa-xmark'; icon.style.color = '#dc2626'; titleEl.style.color = '#dc2626';
            }
            modal.style.display = 'flex';
        }

        document.getElementById('profileForm').onsubmit = function(e) {
            e.preventDefault();
            const np = newPwInput.value;
            const cp = confirmPwInput.value;
            const msg = document.getElementById('pwMsg');
            msg.textContent = '';
            if (!np) { msg.textContent = 'Please enter a new password.'; return; }
            if (np.length < 8 || np.length > 25) { msg.textContent = 'Password must be between 8 and 25 characters.'; return; }
            if (np.includes('  ')) { msg.textContent = 'Password must not contain double spaces.'; return; }
            if (np !== cp) { msg.textContent = 'Passwords do not match.'; return; }

            const fd = new FormData(this);
            fetch(api + '/update_password.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        showMessageModal('success', 'Password Updated', 'Your password has been changed successfully.');
                        this.reset();
                        msg.textContent = '';
                        pwStrengthEl.style.display = 'none';
                        pwMatchEl.style.display = 'none';
                        pwReqEl.innerHTML = '';
                    } else {
                        showMessageModal('error', 'Update Failed', d.error || 'Could not update password.');
                    }
                })
                .catch(() => showMessageModal('error', 'Error', 'Network error occurred.'));
        };
        // Privileges Modal logic (AMORA-style — shows ALL modules, ✓ granted / ✗ denied)
        const privilegesConfig = {
            'superadmin': [
                { desc: 'Access My Profile',                          has: true  },
                { desc: 'Change Password',                             has: true  },
                { desc: 'Manage All Users (Admins & Consumers)',       has: true  },
                { desc: 'Approve / Reject Registration Requests',      has: true  },
                { desc: 'Block / Unblock Any User',                    has: true  },
                { desc: 'Manage All Restaurants & Menus',              has: true  },
                { desc: 'View & Manage All Orders',                    has: true  },
                { desc: 'View Full Login Audit Logs',                  has: true  },
                { desc: 'Assign / Change User Roles',                  has: true  },
                { desc: 'Full System Administration',                  has: true  },
            ],
            'admin': [
                { desc: 'Access My Profile',                          has: true  },
                { desc: 'Change Password',                             has: true  },
                { desc: 'Manage Consumer Accounts',                    has: true  },
                { desc: 'Approve / Reject Registration Requests',      has: true  },
                { desc: 'Block / Unblock Consumers',                   has: true  },
                { desc: 'Manage Restaurants & Menu Items',             has: true  },
                { desc: 'View & Manage Orders',                        has: true  },
                { desc: 'View Login History (Own)',                     has: true  },
                { desc: 'Manage All Users (Admins)',                   has: false },
                { desc: 'Assign / Change User Roles',                  has: false },
                { desc: 'Full System Administration',                  has: false },
            ],
            'consumer': [
                { desc: 'Access My Profile',                          has: true  },
                { desc: 'Change Password',                             has: true  },
                { desc: 'Browse & Order Food',                         has: true  },
                { desc: 'Manage Cart & Checkout',                      has: true  },
                { desc: 'View Personal Order History',                 has: true  },
                { desc: 'Save Favourite Restaurants',                  has: true  },
                { desc: 'Manage Payment Methods',                      has: true  },
                { desc: 'Manage Consumer Accounts',                    has: false },
                { desc: 'Approve / Reject Registration Requests',      has: false },
                { desc: 'Block / Unblock Users',                       has: false },
                { desc: 'Manage Restaurants & Menu Items',             has: false },
                { desc: 'Full System Administration',                  has: false },
            ],
        };

        function viewPrivileges(role) {
            const modal = document.getElementById('privilegesModal');
            if (!modal) return;
            document.getElementById('privRoleTitle').textContent = role.charAt(0).toUpperCase() + role.slice(1);
            const privs = privilegesConfig[role] || privilegesConfig['consumer'];
            document.getElementById('privilegesList').innerHTML = privs.map(p => `
                <div style="display:flex; align-items:center; gap:12px; padding:10px 12px; border-radius:8px;
                     background:${p.has ? '#f0fdf4' : '#f8fafc'};
                     border:1px solid ${p.has ? '#bbf7d0' : '#e2e8f0'}; margin-bottom:4px;">
                    <i class="fa-solid ${p.has ? 'fa-circle-check' : 'fa-circle-xmark'}"
                       style="color:${p.has ? '#16a34a' : '#94a3b8'}; font-size:17px; flex-shrink:0;"></i>
                    <span style="font-size:0.875rem; font-weight:500;
                          color:${p.has ? '#166534' : '#64748b'};
                          text-decoration:${p.has ? 'none' : 'line-through'};">${p.desc}</span>
                </div>`
            ).join('');
            modal.style.display = 'flex';
        }
        document.getElementById('closePrivilegesModal').addEventListener('click', () => {
            document.getElementById('privilegesModal').style.display = 'none';
        });
    </script>
</body>
</html>
