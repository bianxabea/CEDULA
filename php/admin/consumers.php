<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin');
$pageTitle = 'Consumer Management';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Pizza Crust Delight</title>
    <link rel="stylesheet" href="../../css/design-system.css">
    <link rel="stylesheet" href="../../css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .users-table { width: 100%; border-collapse: collapse; background: var(--bg-card); border-radius: 8px; overflow: hidden; }
        .users-table th, .users-table td { padding: 12px; text-align: left; border-bottom: 1px solid var(--border-color); }
        .users-table th { background: var(--bg-body); font-weight: 600; }
        .action-btn { padding: 6px 12px; border-radius: 4px; border: none; cursor: pointer; font-size: 0.85rem; margin-right: 4px; }
        .btn-edit { background: #3b82f6; color: white; }
        .btn-block { background: #ef4444; color: white; }
        .blocked-row { opacity: 0.6; background: #fee2e2; }

        /* Modal Form Styles */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .section-heading { grid-column: 1 / -1; color: var(--primary-color); border-bottom: 2px solid #eee; padding-bottom: 0.5rem; margin-top: 1rem; margin-bottom: 0.5rem; }
        .required { color: red; margin-left: 2px; }
        .hint { font-size: 0.8rem; color: #666; font-weight: normal; }

        @media (max-width: 600px) {
            .form-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body class="dashboard-layout">
    <?php include __DIR__ . '/../includes/layout/navbar.php'; ?>

    <?php $currentPage = 'admin_consumers';
include __DIR__ . '/../includes/layout/sidebar.php'; ?>

    <main class="page-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <h1 class="page-title" style="margin-bottom: 0;">Consumer Management</h1>
                    <span id="userCountBadge" class="status-badge no-dot" style="background: var(--primary-color); color: white; padding: 0.2rem 0.8rem; font-size: 0.9rem; border-radius: 20px;">0 Consumers</span>
                </div>
                <button class="btn-primary" onclick="openUserModal()">Add Consumer</button>
            </div>

            <!-- Search and Filters -->
            <div class="card" style="margin-bottom: 1rem; padding: 1rem;">
                <div style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
                    <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
                        <label>Search Consumers</label>
                        <input type="text" id="searchInput" placeholder="Search by name, username, or email..." onkeyup="handleSearch(event)">
                    </div>
                    <div class="form-group" style="width: 150px; margin-bottom: 0;">
                        <label>Status</label>
                        <select id="filterStatus" onchange="applyFilters()">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="blocked">Blocked</option>
                        </select>
                    </div>
                    <button class="btn-secondary" onclick="resetFilters()">Reset</button>
                </div>
            </div>

            <div id="usersTableContainer">Loading...</div>

            <div id="paginationContainer" style="margin-top: 1rem; display: flex; justify-content: flex-end; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div id="paginationInfo" class="hint">Showing 0-0 of 0 consumers</div>
                <div id="paginationControls" style="display: flex; gap: 0.5rem; align-items: center;">
                    <!-- Page buttons will be injected here -->
                </div>
            </div>
    </main>
    <?php include __DIR__ . '/../includes/layout/footer.php'; ?>

    <!-- Consumer Modal (Add/Edit) -->
    <style>
        #consumerModal .modal2-content {
            padding: 2.5rem;
            border: 1px solid rgba(229, 57, 53, 0.15);
            box-shadow: 0 20px 50px -10px rgba(0, 0, 0, 0.1), 0 0 0 1px rgba(229, 57, 53, 0.05);
            border-radius: var(--radius-xl, 1.5rem);
            background: #ffffff;
        }

        #consumerForm fieldset {
            border: 1px solid #f3f4f6;
            border-radius: var(--radius-lg, 1rem);
            padding: 2rem;
            margin-bottom: 0.5rem;
            background: #fafafa;
            transition: all 0.2s ease;
        }

        #consumerForm fieldset:hover {
            border-color: rgba(229, 57, 53, 0.2);
            box-shadow: 0 4px 12px rgba(0,0,0,0.02);
        }

        #consumerForm legend {
            font-weight: 600;
            color: var(--pza-primary, #E53935);
            padding: 0.25rem 1rem;
            font-size: 1.05rem;
            background: #ffffff;
            border: 1px solid #f3f4f6;
            border-radius: 999px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            letter-spacing: 0.01em;
        }
    </style>
    <div id="consumerModal" class="modal2">
        <div class="modal2-content" style="max-width: 800px; width: 95%;">
             <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                <h2 id="modalTitle">Add Consumer</h2>
                <button onclick="closeConsumerModal()" style="background:none; border:none; font-size:1.5rem; cursor:pointer;">&times;</button>
            </div>

            <form id="consumerForm" style="width: 100%; text-align: left; max-height: 80vh; overflow-y: auto; padding-right: 10px;">
                <input type="hidden" name="id" id="consumerId">
                <input type="hidden" name="role" id="role" value="consumer">

                <!-- STEP 1: Personal Info -->
                <div class="form-step" id="step0">
                    <fieldset>
                        <legend>Personal Information (Step 1 of 3)</legend>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Id No<span class="required">*</span></label>
                                <input type="text" name="custom_id" id="customId" placeholder="xxxx-xxxx">
                                <span class="validation-message" id="customIdError"></span>
                            </div>
                            <div class="form-group">
                                <label>First Name<span class="required">*</span></label>
                                <input type="text" name="firstName" id="firstName">
                                <span class="validation-message" id="firstNameError"></span>
                            </div>
                            <div class="form-group">
                                <label>Last Name<span class="required">*</span></label>
                                <input type="text" name="lastName" id="lastName">
                                <span class="validation-message" id="lastNameError"></span>
                            </div>
                            <div class="form-group">
                                <label>Middle Initial</label>
                                <input type="text" name="middleInitial" id="middleInitial">
                                <span class="validation-message" id="middleInitialError"></span>
                            </div>
                            <div class="form-group">
                                <label>Extension</label>
                                <input type="text" name="extension" id="extension">
                                <span class="validation-message" id="extensionError"></span>
                            </div>
                            <div class="form-group">
                                <label>Sex<span class="required">*</span></label>
                                <select name="sex" id="sex">
                                    <option value="">Select</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>
                                <span class="validation-message" id="sexError"></span>
                            </div>
                            <div class="form-group">
                                <label>Birthdate<span class="required">*</span></label>
                                <input type="date" name="birthdate" id="birthdate">
                                <span class="validation-message" id="birthdateError"></span>
                            </div>
                            <div class="form-group">
                                <label>Age</label>
                                <input type="number" name="age" id="age" readonly>
                            </div>
                        </div>
                    </fieldset>
                </div>

                <!-- STEP 2: Address -->
                <div class="form-step" id="step1" style="display:none;">
                    <fieldset>
                        <legend>Address (Step 2 of 3)</legend>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Purok</label>
                                <input type="text" name="purok" id="purok">
                                <span class="validation-message" id="purokError"></span>
                            </div>
                            <div class="form-group">
                                <label>Barangay</label>
                                <input type="text" name="barangay" id="barangay">
                                <span class="validation-message" id="barangayError"></span>
                            </div>
                            <div class="form-group">
                                <label>City/Municipality</label>
                                <input type="text" name="city" id="city">
                                <span class="validation-message" id="cityError"></span>
                            </div>
                            <div class="form-group">
                                <label>Province</label>
                                <input type="text" name="province" id="province">
                                <span class="validation-message" id="provinceError"></span>
                            </div>
                            <div class="form-group">
                                <label>Zip Code</label>
                                <input type="text" name="zipCode" id="zipCode">
                                <span class="validation-message" id="zipCodeError"></span>
                            </div>
                            <div class="form-group">
                                <label>Country</label>
                                <input type="text" name="country" id="country" value="Philippines">
                                <span class="validation-message" id="countryError"></span>
                            </div>
                        </div>
                    </fieldset>
                </div>

                <!-- STEP 3: Credentials -->
                <div class="form-step" id="step2" style="display:none;">
                    <fieldset>
                        <legend>Credentials (Step 3 of 3)</legend>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Username<span class="required">*</span></label>
                                <input type="text" name="username" id="username">
                                <span class="validation-message" id="usernameError"></span>
                            </div>
                            <div class="form-group">
                                <label>Email<span class="required">*</span></label>
                                <input type="email" name="email" id="email">
                                <span class="validation-message" id="emailError"></span>
                            </div>
                            <div class="form-group">
                                <label>Password <span id="pwHint" class="hint">(Default)</span></label>
                                <input type="password" name="password" id="password" autocomplete="new-password">
                                <span id="pwStrength" style="font-size:0.85em;"></span>
                                <span class="validation-message" id="passwordError"></span>
                            </div>
                            <div class="form-group">
                                <label>Re-enter Password</label>
                                <input type="password" name="repassword" id="repassword" autocomplete="new-password">
                                <span id="pwMatch" style="font-size:0.85em;"></span>
                                <span class="validation-message" id="repasswordError"></span>
                            </div>
                        </div>
                    </fieldset>
                </div>



                <div class="form-navigation-buttons" style="display:flex; justify-content:space-between; margin-top:20px; border-top: 1px solid #eee; padding-top: 1rem;">
                    <button type="button" id="prevBtn" onclick="prevStep()" class="btn-secondary" style="display:none;">Previous</button>
                    <div style="margin-left: auto;">
                        <button type="button" onclick="closeConsumerModal()" class="btn-secondary" style="margin-right: 0.5rem;">Cancel</button>
                        <button type="button" id="nextBtn" onclick="nextStep()" class="btn-primary">Next</button>
                        <button type="submit" id="submitBtn" class="btn-primary" style="display:none;">Save Consumer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Block Request Modal -->
    <div id="blockModal" class="modal2">
        <div class="modal2-content" style="max-width: 450px;">
            <div style="text-align: center; margin-bottom: 1.5rem;">
                <div style="width: 60px; height: 60px; background: #fee2e2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                    <i class="fa-solid fa-triangle-exclamation" style="font-size: 1.75rem; color: #dc2626;"></i>
                </div>
                <h2 style="font-size: 1.5rem; color: #1f2937; margin-bottom: 0.5rem;">Request to Block Consumer</h2>
                <p style="color: #6b7280; font-size: 0.95rem;">This action will restrict the consumer's access. A super admin must approve this request.</p>
            </div>

            <form id="blockForm" style="width: 100%; text-align: left;">
                <input type="hidden" name="target_id" id="targetId">
                <div class="form-group">
                    <label style="font-weight: 600; color: #374151; margin-bottom: 0.5rem; display: block;">Reason for blocking <span class="required">*</span></label>
                    <textarea name="reason" id="blockReason" rows="4" required placeholder="Please provide a valid reason..." style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-family: inherit; resize: vertical;"></textarea>
                </div>
                <div style="display: flex; gap: 0.75rem; margin-top: 1.5rem;">
                    <button type="button" onclick="document.getElementById('blockModal').style.display='none'" class="btn-secondary" style="flex: 1; justify-content: center;">Cancel</button>
                    <button type="submit" id="submitRequestBtn" class="btn-primary" style="background: #dc2626; flex: 1; justify-content: center; box-shadow: 0 2px 4px rgba(220, 38, 38, 0.2);">
                        Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Success/Error Modal -->
    <div id="messageModal" class="modal2" style="z-index: 1050;">
        <div class="modal2-content" style="max-width: 400px; text-align: center; padding: 2rem;">
            <div id="msgIconContainer" style="width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                <i id="msgIcon" class="fa-solid" style="font-size: 1.75rem;"></i>
            </div>
            <h2 id="msgTitle" style="font-size: 1.5rem; margin-bottom: 0.5rem;"></h2>
            <p id="msgBody" style="color: #6b7280; margin-bottom: 1.5rem;"></p>
            <button onclick="closeMessageModal()" class="btn-primary" style="width: 100%; justify-content: center;">Okay</button>
        </div>
    </div>

    <!-- Privileges Modal -->
    <div id="privModal" class="modal2">
        <div class="modal2-content">
            <h2>User Privileges</h2>
            <p id="privUserName" style="font-weight: bold; margin-bottom: 1rem;"></p>
            <div id="privContent" style="width: 100%;"></div>
            <button onclick="document.getElementById('privModal').style.display='none'" class="btn-primary" style="margin-top: 1rem;">Close</button>
        </div>
    </div>

    <script src="../../js/admin_user_validation.js?v=<?php echo time(); ?>"></script>
    <script src="../../js/pagination_util.js"></script>
    <script>
        function toggleAnswerVisibility(icon) {
            const input = icon.previousElementSibling;
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
    <script>
        const api = '../../php/database';

        // Pagination & Filter State
        let currentPage = 1;
        let limit = 10;
        let currentSearch = '';
        let currentStatus = '';
        let editingUserId = '';
        // Stores consumer objects keyed by ID so the Edit button can
        // look up the full record without embedding JSON in HTML.
        let usersMap = {};

        function loadUsers(page = 1) {
            currentPage = page;
            const params = new URLSearchParams({
                page: currentPage,
                limit: limit,
                search: currentSearch,
                status: currentStatus
            });

            fetch(api + '/admin_consumers_list.php?' + params.toString())
                .then(r => r.json())
                .then(data => {
                    if (!data.success) return;

                    let html = '<table class="data-table"><thead><tr><th>Name</th><th>Username</th><th>Email</th><th>Status</th><th>Actions</th><th>Privileges</th></tr></thead><tbody>';

                    // Build a lookup map so editUser() can find the
                    // full consumer object by ID instead of via inline JSON.
                    usersMap = {};
                    data.consumers.forEach(u => { usersMap[u.id] = u; });

                    if (data.consumers.length === 0) {
                        html += '<tr><td colspan="6" style="text-align:center; padding:2rem;">No consumers found matching your criteria.</td></tr>';
                    } else {
                        data.consumers.forEach(u => {
                            const isBlocked = u.is_blocked == 1;
                            html += `<tr class="${isBlocked ? 'blocked-row' : ''}">
                                <td>${escapeHtml(u.firstName + ' ' + u.lastName)}</td>
                                <td>${escapeHtml(u.username)}</td>
                                <td>${escapeHtml(u.email)}</td>
                                <td>${isBlocked ? '<span class="status-badge no-dot status-trash">Blocked</span>' : '<span class="status-badge no-dot status-ok">Active</span>'}</td>
                                <td>
                                    <button class="btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;" onclick="editUser('${u.id}')">
                                        <i class="fa-solid fa-pen-to-square" style="margin-right:0.25rem;"></i> Edit
                                    </button>
                                    ${!isBlocked ?
                                        `<button class="btn-secondary" style="padding: 0.4rem 0.8rem; font-size: 0.85rem; color: var(--error-color); border-color: var(--error-color);" onclick="openBlockModal('${u.id}', 'block')"><i class="fa-solid fa-ban" style="margin-right:0.25rem;"></i>Request Block</button>` :
                                        `<button class="btn-secondary" style="padding: 0.4rem 0.8rem; font-size: 0.85rem; color: var(--success-color); border-color: var(--success-color);" onclick="openBlockModal('${u.id}', 'unblock')"><i class="fa-solid fa-unlock" style="margin-right:0.25rem;"></i>Request Unblock</button>`
                                    }
                                </td>
                                <td>
                                    <button class="btn-secondary" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;" onclick="viewPrivileges('consumer', '${escapeHtml(u.username)}')">
                                        <i class="fa-solid fa-eye" style="margin-right:0.25rem;"></i> View
                                    </button>
                                </td>
                            </tr>`;
                        });
                    }
                    html += '</tbody></table>';
                    document.getElementById('usersTableContainer').innerHTML = html;

                    updatePagination(data.pagination);
                });
        }

        function updatePagination(p) {
            const controls = document.getElementById('paginationControls');
            const badge = document.getElementById('userCountBadge');

            // Update Badge
            badge.textContent = `${p.total_users} Consumer${p.total_users !== 1 ? 's' : ''}`;

            window.renderPagination(
                controls, currentPage, p.total_pages || 1, limit,
                function(newPage) { loadUsers(newPage); },
                function(newLimit) { limit = newLimit; loadUsers(1); }
            );

            // Hide the old simple info text since renderPagination now shows its own "Page X of Y"
            const info = document.getElementById('paginationInfo');
            if (info) info.style.display = 'none';
        }

        // Search & Filter Handlers
        let searchTimeout;
        function handleSearch(e) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                currentSearch = e.target.value;
                loadUsers(1);
            }, 300);
        }

        function applyFilters() {
            currentStatus = document.getElementById('filterStatus').value;
            loadUsers(1);
        }

        function resetFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('filterStatus').value = '';
            currentSearch = '';
            currentStatus = '';
            loadUsers(1);
        }

        const privilegesConfig = {
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

        function viewPrivileges(role, name) {
            document.getElementById('privUserName').textContent = name + ' (' + role + ')';
            const privs = privilegesConfig[role] || privilegesConfig['consumer'];

            const listHtml = privs.map(p => `
                <div style="display:flex; align-items:center; gap:12px; padding:10px 12px; border-radius:8px;
                     background:${p.has ? '#f0fdf4' : '#f8fafc'};
                     border:1px solid ${p.has ? '#bbf7d0' : '#e2e8f0'}; margin-bottom:4px; text-align:left;">
                    <i class="fa-solid ${p.has ? 'fa-circle-check' : 'fa-circle-xmark'}"
                       style="color:${p.has ? '#16a34a' : '#94a3b8'}; font-size:17px; flex-shrink:0;"></i>
                    <span style="font-size:0.875rem; font-weight:500;
                          color:${p.has ? '#166534' : '#64748b'};
                          text-decoration:${p.has ? 'none' : 'line-through'};">${p.desc}</span>
                </div>`
            ).join('');

            const contentDiv = document.getElementById('privContent');
            contentDiv.innerHTML = `<div style="display:inline-block; text-align:left; width:100%;">${listHtml}</div>`;
            contentDiv.style.display = 'block';
            document.getElementById('privModal').style.display = 'flex';
        }

        function calculateAge() {
            // Handled by AdminUserValidation module
        }

        let currentStep = 0;
        const totalSteps = 3;

        function showStep(n) {
            for (let i = 0; i < totalSteps; i++) {
                const el = document.getElementById('step' + i);
                if (el) el.style.display = 'none';
            }
            const activeStep = document.getElementById('step' + n);
            if (activeStep) activeStep.style.display = 'block';

            if (n === 0) {
                document.getElementById('prevBtn').style.display = 'none';
            } else {
                document.getElementById('prevBtn').style.display = 'inline-block';
            }

            if (n === totalSteps - 1) {
                document.getElementById('nextBtn').style.display = 'none';
                document.getElementById('submitBtn').style.display = 'inline-block';
            } else {
                document.getElementById('nextBtn').style.display = 'inline-block';
                document.getElementById('submitBtn').style.display = 'none';
            }
        }

        async function nextStep() {
            const isEdit = !!editingUserId;
            AdminUserValidation.clearAllErrors();
            let isValid = true;

            if (currentStep === 0) {
                isValid = await AdminUserValidation.validatePersonalInfo();
            } else if (currentStep === 1) {
                isValid = AdminUserValidation.validateAddress();
            } else if (currentStep === 2) {
                isValid = await AdminUserValidation.validateCredentials(isEdit);
            }

            if (isValid) {
                currentStep++;
                showStep(currentStep);
            }
        }

        function prevStep() {
            if (currentStep > 0) {
                currentStep--;
                showStep(currentStep);
            }
        }

        function openUserModal() {
            editingUserId = '';
            AdminUserValidation.setEditMode(false);
            document.getElementById('modalTitle').textContent = 'Add Consumer';
            document.getElementById('consumerForm').reset();
            document.getElementById('consumerId').value = '';
            document.getElementById('customId').value = '';
            document.getElementById('pwHint').textContent = '(Required)';
            AdminUserValidation.clearAllErrors();

            currentStep = 0;
            showStep(0);

            document.getElementById('consumerModal').style.display = 'flex';
        }

        function closeConsumerModal() {
            document.getElementById('consumerModal').style.display = 'none';
        }

        function editUser(userId) {
            const u = usersMap[userId];
            if (!u) return;
            editingUserId = u.id;
            AdminUserValidation.setEditMode(true);
            document.getElementById('modalTitle').textContent = 'Edit Consumer';
            document.getElementById('consumerId').value = u.id;
            document.getElementById('customId').value = u.id;

            // Populate fields
            const fields = ['firstName', 'lastName', 'middleInitial', 'extension', 'sex', 'birthdate', 'purok', 'barangay', 'city', 'province', 'zipCode', 'country', 'username', 'email'];
            fields.forEach(f => {
                if (document.getElementById(f)) document.getElementById(f).value = u[f] || '';
            });

            // Populate security questions if present
            if (document.getElementById('sq1')) document.getElementById('sq1').value = u.secure_question || '';
            if (document.getElementById('sq2')) document.getElementById('sq2').value = u.secure_question2 || '';
            if (document.getElementById('sq3')) document.getElementById('sq3').value = u.secure_question3 || '';

            if (u.birthdate) {
                const bd = new Date(u.birthdate), today = new Date();
                let age = today.getFullYear() - bd.getFullYear();
                const m = today.getMonth() - bd.getMonth();
                if (m < 0 || (m === 0 && today.getDate() < bd.getDate())) age--;
                document.getElementById('age').value = age;
            }

            document.getElementById('password').value = '';
            document.getElementById('pwHint').textContent = '(Leave blank to keep current)';
            AdminUserValidation.clearAllErrors();

            currentStep = 0;
            showStep(0);

            document.getElementById('consumerModal').style.display = 'flex';
        }



        function openBlockModal(id, type = 'block') {
            document.getElementById('targetId').value = id;
            document.getElementById('blockReason').value = '';

            // Dynamic labels
            const title = type === 'block' ? 'Request to Block Consumer' : 'Request to Unblock Consumer';
            const subtitle = type === 'block' ?
                "This action will restrict the consumer's access. A super admin must approve this request." :
                "This action will restore the consumer's access. A super admin must approve this request.";
            const btnText = type === 'block' ? 'Submit Block Request' : 'Submit Unblock Request';
            const btnBg = type === 'block' ? '#dc2626' : '#16a34a';
            const btnShadow = type === 'block' ? 'rgba(220, 38, 38, 0.2)' : 'rgba(22, 163, 74, 0.2)';
            const icon = type === 'block' ? 'fa-triangle-exclamation' : 'fa-unlock';
            const iconBg = type === 'block' ? '#fee2e2' : '#dcfce7';
            const iconColor = type === 'block' ? '#dc2626' : '#16a34a';

            const modal = document.getElementById('blockModal');
            modal.querySelector('h2').textContent = title;
            modal.querySelector('p').textContent = subtitle;

            const submitBtn = document.getElementById('submitRequestBtn');
            submitBtn.innerHTML = btnText;
            submitBtn.style.background = btnBg;
            submitBtn.style.boxShadow = `0 2px 4px ${btnShadow}`;

            const iconContainer = modal.querySelector('div > div');
            iconContainer.style.background = iconBg;
            iconContainer.querySelector('i').className = `fa-solid ${icon}`;
            iconContainer.querySelector('i').style.color = iconColor;

            // Add hidden type to form if not exists
            let typeInput = document.getElementById('requestTypeInput');
            if (!typeInput) {
                typeInput = document.createElement('input');
                typeInput.type = 'hidden';
                typeInput.name = 'request_type';
                typeInput.id = 'requestTypeInput';
                document.getElementById('blockForm').appendChild(typeInput);
            }
            typeInput.value = type;

            modal.style.display = 'flex';
        }

        document.getElementById('blockForm').onsubmit = function(e) {
            e.preventDefault();
            const fd = new FormData(this);
            fetch(api + '/admin_request_block.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(d => {
                    document.getElementById('blockModal').style.display = 'none';
                    const type = fd.get('request_type');
                    if (d.success) {
                        showMessageModal('success', 'Request Submitted', `The ${type} request has been sent for approval.`);
                    } else {
                        showMessageModal('error', 'Request Failed', d.error || 'An error occurred.');
                    }
                })
                .catch(() => {
                    document.getElementById('blockModal').style.display = 'none';
                    showMessageModal('error', 'Error', 'A network error occurred.');
                });
        };

        document.getElementById('consumerForm').onsubmit = async function(e) {
            e.preventDefault();
            const isEdit = !!editingUserId;
            if (!(await AdminUserValidation.validateAll(isEdit))) { showStep(0); return; }
            const fd = new FormData(this);
            fd.set('id', editingUserId);
            fetch(api + '/admin_consumer_save.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        closeConsumerModal();
                        showMessageModal('success', 'Consumer Saved', 'Consumer data has been updated successfully.');
                        loadUsers(currentPage);
                    } else {
                        showMessageModal('error', 'Save Failed', d.error || 'An error occurred.');
                    }
                })
                .catch(() => {
                    showMessageModal('error', 'Error', 'A network error occurred.');
                });
        };

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
                icon.className = 'fa-solid fa-check';
                icon.style.color = '#16a34a';
                titleEl.style.color = '#16a34a';
            } else {
                iconContainer.style.background = '#fee2e2';
                icon.className = 'fa-solid fa-xmark';
                icon.style.color = '#dc2626';
                titleEl.style.color = '#dc2626';
            }

            modal.style.display = 'flex';
        }

        function closeMessageModal() {
            document.getElementById('messageModal').style.display = 'none';
        }

        function escapeHtml(s) { const d = document.createElement('div'); d.textContent = s || ''; return d.innerHTML; }

        loadUsers();

        // Initialize validation module for consumer form
        AdminUserValidation.init(document.getElementById('consumerForm'), {
            apiBase: api,
            roleSelector: '#role',
            securitySection: '#securitySection'
        });
    </script>
</body>
</html>
