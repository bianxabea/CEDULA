<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
requireRole('superadmin');
$pageTitle = 'User Management';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Pizza Crust Delight</title>
    <link rel="stylesheet" href="../../css/design-system.css">
    <link rel="stylesheet" href="../../css/dashboard.css">
    <link rel="stylesheet" href="../../css/superadmin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .users-table { width: 100%; border-collapse: collapse; background: var(--bg-card); border-radius: 8px; overflow: hidden; }
        .users-table th, .users-table td { padding: 12px; text-align: left; border-bottom: 1px solid var(--border-color); }
        .users-table th { background: var(--bg-body); font-weight: 600; }
        .action-btn { padding: 6px 12px; border-radius: 4px; border: none; cursor: pointer; font-size: 0.85rem; margin-right: 4px; }
        .btn-edit { background: #3b82f6; color: white; }
        .btn-block { background: #ef4444; color: white; }
        .btn-unblock { background: #10b981; color: white; }
        .btn-priv { background: #8b5cf6; color: white; }
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
<body class="dashboard-layout superadmin-v2">
    <?php include __DIR__ . '/../includes/layout/navbar.php'; ?>

    <!-- Sidebar -->
    <?php $currentPage = 'superadmin_users';
include __DIR__ . '/../includes/layout/sidebar.php'; ?>

    <main class="page-content">
            <header style="margin-bottom: 2rem;">
                <h1 class="page-title" style="font-size: 1.75rem; margin-bottom: 0.25rem;">User Management</h1>
                <p class="page-subtitle text-muted" style="margin: 0; font-size: 0.95rem;">Manage identities, roles, and access across the platform.</p>
            </header>

            <article class="sa-box" style="margin-bottom: 2rem;">
                <header class="sa-box-header">
                    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center; width: 100%;">
                        <input type="text" id="searchInput" placeholder="Search by name, username, or email..." class="input-field" style="min-width: 200px; flex: 1;" onkeyup="handleSearch(event)">
                        <select id="filterRole" class="input-field" style="width: auto;" onchange="applyFilters()">
                            <option value="">All Roles</option>
                            <option value="consumer">Consumer</option>
                            <option value="admin">Admin</option>
                            <option value="superadmin">Superadmin</option>
                        </select>
                        <select id="filterStatus" class="input-field" style="width: auto;" onchange="applyFilters()">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="blocked">Blocked</option>
                        </select>
                        <button class="btn-secondary" onclick="resetFilters()">Reset</button>
                        <button class="btn-primary" onclick="openUserModal()" style="white-space: nowrap;"><i class="fa-solid fa-plus" style="margin-right: 8px;"></i> Add User</button>
                    </div>
                </header>

                <div class="sa-box-content no-pad">
                    <div id="usersTableContainer">
                        <div style="padding: 2rem; text-align: center; color: var(--sa-text-muted);">Loading...</div>
                    </div>
                </div>
            </article>

            <div id="paginationContainer" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-top: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span id="userCountBadge" class="status-badge status-ok" style="background: var(--sa-primary-ultralight); color: var(--sa-primary);">0 Users</span>
                    <span id="paginationInfo" class="text-muted" style="font-size: 0.85rem;">Showing 0-0 of 0 users</span>
                </div>
                <div id="paginationControls" style="display: flex; gap: 0.25rem; align-items: center;">
                    <!-- Page buttons will be injected here -->
                </div>
            </div>
    </main>
    <?php include __DIR__ . '/../includes/layout/footer.php'; ?>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal2">
        <div class="modal2-content" style="max-width: 400px;">
            <h2 style="color: var(--error-color);">Confirm Delete</h2>
            <p>Are you sure you want to permanently delete <span id="deleteUserName" style="font-weight: bold;"></span>?</p>
            <p class="hint" style="margin-top: 0.5rem;">This action cannot be undone and may fail if the user has active orders or dependencies.</p>
            <div style="text-align: right; margin-top: 1.5rem;">
                <button onclick="closeDeleteModal()" class="btn-secondary">Cancel</button>
                <button id="confirmDeleteBtn" class="btn-primary" style="background: var(--error-color); border-color: var(--error-color);">Delete User</button>
            </div>
        </div>
    </div>

    <!-- Response Modal -->
    <div id="responseModal" class="modal2" style="z-index: 1050;">
        <div class="modal2-content" style="max-width: 400px; text-align: center;">
            <div id="responseIcon" style="font-size: 3rem; margin-bottom: 1rem;"></div>
            <h2 id="responseTitle">Success</h2>
            <p id="responseMessage"></p>
            <div style="margin-top: 1.5rem;">
                <button onclick="closeResponseModal()" class="btn-primary">OK</button>
            </div>
        </div>
    </div>

    <!-- User Modal (Add/Edit) -->
    <style>
        #userModal .modal2-content {
            padding: 2.5rem;
            border: 1px solid rgba(229, 57, 53, 0.15);
            box-shadow: 0 20px 50px -10px rgba(0, 0, 0, 0.1), 0 0 0 1px rgba(229, 57, 53, 0.05);
            border-radius: var(--radius-xl, 1.5rem);
            background: #ffffff;
        }
        
        #userForm fieldset {
            border: 1px solid #f3f4f6;
            border-radius: var(--radius-lg, 1rem);
            padding: 2rem;
            margin-bottom: 0.5rem;
            background: #fafafa;
            transition: all 0.2s ease;
        }
        
        #userForm fieldset:hover {
            border-color: rgba(229, 57, 53, 0.2);
            box-shadow: 0 4px 12px rgba(0,0,0,0.02);
        }
        
        #userForm legend {
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
    <div id="userModal" class="modal2">
        <div class="modal2-content" style="max-width: 800px; width: 95%;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                <h2 id="modalTitle">Add User</h2>
                <button onclick="closeUserModal()" style="background:none; border:none; font-size:1.5rem; cursor:pointer;">&times;</button>
            </div>

            <form id="userForm" style="width: 100%; text-align: left; max-height: 80vh; overflow-y: auto; padding-right: 10px;">
                <input type="hidden" name="id" id="userId">

                <!-- STEP 1: Personal Info -->
                <div class="form-step" id="step0">
                    <fieldset>
                        <legend>Personal Information (Step 1 of 4)</legend>
                        <div class="form-grid">
                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label>Role<span class="required">*</span></label>
                                <select name="role" id="role">
                                    <option value="consumer">Consumer</option>
                                    <option value="admin">Admin</option>
                                    <option value="superadmin">Superadmin</option>
                                </select>
                                <span class="hint" style="display:block; margin-top:0.35rem; font-size: 0.85rem; color: #6b7280;">(Note: Security questions are not applicable for Admin and Superadmin roles)</span>
                            </div>
                            <div class="form-group">
                                <label>Id No <span class="hint">(Leave blank to auto-generate)</span></label>
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
                        <legend>Address (Step 2 of 4)</legend>
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
                        <legend>Credentials (Step 3 of 4)</legend>
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
                        <button type="button" onclick="closeUserModal()" class="btn-secondary" style="margin-right: 0.5rem;">Cancel</button>
                        <button type="button" id="nextBtn" onclick="nextStep()" class="btn-primary">Next</button>
                        <button type="submit" id="submitBtn" class="btn-primary" style="display:none;">Save User</button>
                    </div>
                </div>
            </form>
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
        const currentUserId = '<?php echo $_SESSION['user']['id']; ?>';

        // Pagination & Filter State
        let currentPage = 1;
        let limit = 10;
        let currentSearch = '';
        let currentRole = '';
        let currentStatus = '';
        // Stores user objects keyed by ID so the Edit button can
        // look up the full record without embedding JSON in HTML.
        let usersMap = {};
        // The ID of the user currently being edited (set by editUser,
        // cleared by openUserModal). Used by the submit handler to
        // guarantee the ID reaches the backend.
        let editingUserId = '';

        function loadUsers(page = 1) {
            currentPage = page;
            const params = new URLSearchParams({
                page: currentPage,
                limit: limit,
                search: currentSearch,
                role: currentRole,
                status: currentStatus
            });

            fetch(api + '/superadmin_users_list.php?' + params.toString())
                .then(r => r.json())
                .then(data => {
                    if (!data.success) return;

                    let html = '<table class="sa-table"><thead><tr><th>Name</th><th>Username</th><th>Role</th><th>Status</th><th>Actions</th><th>Privileges</th></tr></thead><tbody>';

                    // Build a lookup map so editUser() can find the
                    // full user object by ID instead of via inline JSON.
                    usersMap = {};
                    data.users.forEach(u => { usersMap[u.id] = u; });

                    if (data.users.length === 0) {
                        html += '<tr><td colspan="6" style="text-align:center; padding:2rem; color: var(--sa-text-muted);">No users found matching your criteria.</td></tr>';
                    } else {
                        data.users.forEach(u => {
                            const isBlocked = u.is_blocked == 1;
                            const isSelf = u.id === currentUserId;
                            const rowClass = isBlocked ? 'blocked-row' : '';

                            let roleClass = 'status-pending';
                            if (u.role === 'admin') roleClass = 'status-ok';
                            if (u.role === 'superadmin') roleClass = 'status-trash';

                            html += `<tr class="${rowClass}">
                                <td style="font-weight: 500;">${escapeHtml(u.firstName + ' ' + u.lastName)}</td>
                                <td class="text-muted">@${escapeHtml(u.username)}</td>
                                <td><span class="status-badge ${roleClass}">${u.role}</span></td>
                                <td>${isBlocked ? '<span class="status-badge" style="background: var(--sa-danger); color: white;">Blocked</span>' : '<span class="status-badge status-ok">Active</span>'}</td>
                                <td>
                                    <div style="display: flex; gap: 4px;">
                                        <button class="btn-secondary" style="padding: 4px 10px; font-size: 0.75rem;" onclick="editUser('${u.id}')">Edit</button>
                                        ${!isSelf ? (isBlocked ?
                                            `<button class="btn-secondary" style="padding: 4px 10px; font-size: 0.75rem; color: var(--sa-success); border-color: var(--sa-success);" onclick="blockUser('${u.id}', 'unblock')">Unblock</button>` :
                                            `<button class="btn-secondary" style="padding: 4px 10px; font-size: 0.75rem; color: var(--sa-danger); border-color: #fca5a5;" onclick="blockUser('${u.id}', 'block')">Block</button>`) : ''}
                                        ${!isSelf ? `<button class="btn-secondary" style="padding: 4px 10px; font-size: 0.75rem; color: var(--sa-danger); border-color: #fca5a5;" onclick="openDeleteModal('${u.id}', '${escapeHtml(u.firstName + ' ' + u.lastName)}')">Delete</button>` : ''}
                                    </div>
                                </td>
                                <td>
                                    <button class="btn-secondary" style="padding: 4px 10px; font-size: 0.75rem;" onclick="viewPrivileges('${u.role}', '${escapeHtml(u.username)}')">View</button>
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
            badge.textContent = `${p.total_users} User${p.total_users !== 1 ? 's' : ''}`;
            
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
            currentRole = document.getElementById('filterRole').value;
            currentStatus = document.getElementById('filterStatus').value;
            loadUsers(1);
        }

        function resetFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('filterRole').value = '';
            document.getElementById('filterStatus').value = '';
            currentSearch = '';
            currentRole = '';
            currentStatus = '';
            loadUsers(1);
        }

        // User Management Handlers
        let userToDelete = null;
        function openDeleteModal(id, name) {
            userToDelete = id;
            document.getElementById('deleteUserName').textContent = name;
            document.getElementById('deleteModal').style.display = 'flex';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
            userToDelete = null;
        }

        function showResponse(success, message) {
            const title = document.getElementById('responseTitle');
            const msg = document.getElementById('responseMessage');
            const icon = document.getElementById('responseIcon');

            title.textContent = success ? 'Success' : 'Error';
            title.style.color = success ? 'var(--success-color)' : 'var(--error-color)';
            msg.textContent = message;
            icon.innerHTML = success ? '<i class="fa-solid fa-circle-check" style="color: var(--success-color);"></i>' : '<i class="fa-solid fa-circle-xmark" style="color: var(--error-color);"></i>';

            document.getElementById('responseModal').style.display = 'flex';
        }

        function closeResponseModal() {
            document.getElementById('responseModal').style.display = 'none';
        }

        document.getElementById('confirmDeleteBtn').onclick = function() {
            if (!userToDelete) return;
            const fd = new FormData();
            fd.append('user_id', userToDelete);
            fetch(api + '/superadmin_user_delete.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(d => {
                    closeDeleteModal();
                    if (d.success) {
                        showResponse(true, 'User has been successfully deleted.');
                        loadUsers(currentPage);
                    } else {
                        showResponse(false, d.error || 'Failed to delete user.');
                    }
                })
                .catch(() => {
                    closeDeleteModal();
                    showResponse(false, 'A network error occurred.');
                });
        };

        function blockUser(userId, action) {
            if (!userId) {
                showResponse(false, 'Cannot ' + action + ': user ID is missing.');
                return;
            }
            if (!confirm(`Are you sure you want to ${action} this user?`)) return;
            // Use URLSearchParams for reliable POST encoding
            fetch(api + '/superadmin_user_block.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'user_id=' + encodeURIComponent(userId) + '&action=' + encodeURIComponent(action),
                    credentials: 'same-origin'
                })
                .then(r => {
                    if (!r.ok) throw new Error('Server returned ' + r.status);
                    return r.json();
                })
                .then(d => {
                    if (d.success) {
                        if (d.superadmin_swap) {
                            alert("CRITICAL SECURITY ACTION: You have unblocked another Superadmin. As the system only allows one active Superadmin, your account has been blocked and you will now be logged out. Please log in with the other Superadmin's credentials.");
                            window.location.href = '../auth/logout.php';
                            return;
                        }
                        showResponse(true, `User has been ${action}ed successfully.`);
                        loadUsers(currentPage);
                    } else {
                        showResponse(false, d.error || `Failed to ${action} user.`);
                    }
                })
                .catch(err => {
                    showResponse(false, 'Request failed: ' + err.message);
                });
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

            // Check role. If role is admin/superadmin, skip step 3 (Security)
            const role = document.getElementById('role') ? document.getElementById('role').value : 'consumer';
            if (n >= totalSteps - 1) {
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
            document.getElementById('modalTitle').textContent = 'Add User';
            document.getElementById('userForm').reset();
            document.getElementById('userId').value = '';
            document.getElementById('pwHint').textContent = '(Required for new user)';
            document.getElementById('role').value = 'consumer';
            document.getElementById('role').dispatchEvent(new Event('change'));
            AdminUserValidation.clearAllErrors();
            
            currentStep = 0;
            showStep(0);
            
            document.getElementById('userModal').style.display = 'flex';
        }

        // Add event listener to update the Next/Save button dynamically when role changes on Step 2
        document.addEventListener("DOMContentLoaded", () => {
            const roleEl = document.getElementById('role');
            if (roleEl) {
                roleEl.addEventListener('change', () => {
                    showStep(currentStep);
                });
            }
        });

        function closeUserModal() {
            document.getElementById('userModal').style.display = 'none';
        }

        function editUser(userId) {
            const u = usersMap[userId];
            if (!u) return;
            // Store the ID in a JS variable — this is the single source
            // of truth for "which user are we editing" and cannot be
            // cleared by form resets or toggleSecurity().
            editingUserId = u.id;
            document.getElementById('modalTitle').textContent = 'Edit User';
            document.getElementById('userId').value = u.id;
            document.getElementById('customId').value = u.id;

            const fields = ['firstName', 'lastName', 'middleInitial', 'extension', 'sex', 'birthdate', 'purok', 'barangay', 'city', 'province', 'zipCode', 'country', 'username', 'email', 'role'];
            fields.forEach(f => {
                if (document.getElementById(f)) document.getElementById(f).value = u[f] || '';
            });

            // Tell the validation module the role changed
            const roleEl = document.getElementById('role');
            if (roleEl) roleEl.dispatchEvent(new Event('change'));

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

            document.getElementById('userModal').style.display = 'flex';
        }

        document.getElementById('userForm').onsubmit = async function(e) {
            e.preventDefault();
            const isEdit = !!editingUserId;
            const role = document.getElementById('role').value;

            if (!(await AdminUserValidation.validateAll(isEdit))) {
                showStep(0);
                return;
            }

            if (!isEdit && role === 'superadmin') {
                if (!confirm("CRITICAL: Creating a new Superadmin will BLOCK your current account and log you out for security reasons. The new account will become the primary Superadmin. Do you wish to proceed?")) {
                    return;
                }
            }

            const fd = new FormData(this);
            fd.set('id', editingUserId);
            fetch(api + '/superadmin_user_save.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        closeUserModal();
                        if (d.superadmin_swap) {
                            alert("Account swapped successfully. You will now be logged out. Please log in with the new Superadmin credentials.");
                            window.location.href = '../auth/logout.php';
                            return;
                        }
                        showResponse(true, 'User data has been saved successfully.');
                        loadUsers(currentPage);
                    } else {
                        showResponse(false, d.error || 'Failed to save user data.');
                    }
                })
                .catch(() => {
                    showResponse(false, 'A network error occurred while saving.');
                });
        };

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

        function escapeHtml(s) { const d = document.createElement('div'); d.textContent = s || ''; return d.innerHTML; }

        loadUsers();

        // Initialize validation module
        AdminUserValidation.init(document.getElementById('userForm'), {
            apiBase: api,
            roleSelector: '#role',
            securitySection: '#securitySection'
        });
    </script>
</body>
</html>
