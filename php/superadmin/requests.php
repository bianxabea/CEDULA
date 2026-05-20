<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
requireRole('superadmin');
$pageTitle = 'Requests';
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
        /* ── Controls Bar ── */
        .controls-bar {
            display: flex; gap: 0.75rem; margin-bottom: 1.25rem;
            flex-wrap: wrap; align-items: center;
        }
        .search-input {
            flex: 1; min-width: 200px;
            padding: 0.55rem 0.75rem 0.55rem 2.25rem;
            border: 1px solid var(--border-color, #e2e8f0); border-radius: 8px;
            font-size: 0.9rem; background: var(--bg-card, #fff);
            color: var(--text-heading); transition: border-color 0.2s;
        }
        .search-input:focus { outline: none; border-color: var(--sa-primary, #7c3aed); box-shadow: 0 0 0 3px rgba(124,58,237,0.1); }
        .search-wrap { position: relative; flex: 1; min-width: 200px; }
        .search-wrap i { position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.85rem; }

        .filter-select {
            padding: 0.55rem 0.75rem; border: 1px solid var(--border-color); border-radius: 8px;
            font-size: 0.85rem; background: var(--bg-card); color: var(--text-heading);
            cursor: pointer; min-width: 140px;
        }
        .filter-select:focus { outline: none; border-color: var(--sa-primary, #7c3aed); }

        /* ── Result Info ── */
        .result-info { font-size: 0.82rem; color: var(--text-muted); margin-bottom: 0.5rem; }

        /* ── Pagination ── */
        .pagination {
            display: flex; align-items: center; justify-content: center;
            gap: 0.25rem; margin-top: 1.25rem;  padding-top: 1rem;
            border-top: 1px solid var(--border-color, #e2e8f0);
        }
        .pg-btn {
            padding: 0.4rem 0.75rem; border: 1px solid var(--border-color);
            border-radius: 6px; background: var(--bg-card); color: var(--text-heading);
            font-size: 0.85rem; cursor: pointer; transition: all 0.2s; font-weight: 500;
        }
        .pg-btn:hover:not(:disabled):not(.active) { border-color: var(--sa-primary, #7c3aed); color: var(--sa-primary); }
        .pg-btn.active { background: var(--sa-primary, #7c3aed); color: #fff; border-color: var(--sa-primary); }
        .pg-btn:disabled { opacity: 0.4; cursor: not-allowed; }
        .pg-info { font-size: 0.82rem; color: var(--text-muted); margin: 0 0.5rem; }
    </style>
</head>
<body class="dashboard-layout superadmin-v2">
    <?php include __DIR__ . '/../includes/layout/navbar.php'; ?>

    <?php $currentPage = 'superadmin_requests';
include __DIR__ . '/../includes/layout/sidebar.php'; ?>

    <main class="page-content">
        <header style="margin-bottom: 1.5rem;">
            <h1 class="page-title" style="font-size: 1.75rem; margin-bottom: 0.25rem;">Requests</h1>
            <p class="page-subtitle text-muted" style="margin: 0; font-size: 0.95rem;">Review registration and block/unblock requests.</p>
        </header>

        <article class="sa-box">
            <header class="sa-box-header">
                <h2>All Requests</h2>
            </header>
            <div class="sa-box-content">
                <!-- Controls -->
                <div class="controls-bar">
                    <div class="search-wrap">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" class="search-input" id="searchInput" placeholder="Search by name, username, or reason...">
                    </div>
                    <select class="filter-select" id="statusFilter">
                        <option value="all">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <select class="filter-select" id="typeFilter">
                        <option value="all">All Types</option>
                        <option value="registration">Registration</option>
                        <option value="block">Block</option>
                        <option value="unblock">Unblock</option>
                    </select>
                </div>

                <div class="result-info" id="resultInfo"></div>

                <!-- Table -->
                <div id="requestsTableContainer" style="overflow-x: auto;">
                    <div style="padding: 2rem; text-align: center; color: var(--sa-text-muted);">Loading...</div>
                </div>

                <!-- Pagination -->
                <div class="pagination" id="paginationBar"></div>
            </div>
        </article>
    </main>
    <?php include __DIR__ . '/../includes/layout/footer.php'; ?>

    <script src="../../js/pagination_util.js"></script>
    <script>
        const api = '../../php/database';
        let PER_PAGE = 10;
        let allRequests = [];
        let filteredRequests = [];
        let currentPage = 1;

        function loadRequests() {
            fetch(api + '/superadmin_requests_list.php')
                .then(r => r.json())
                .then(data => {
                    if (!data.success) return;
                    allRequests = data.requests || [];
                    applyFilters();
                })
                .catch(() => {
                    document.getElementById('requestsTableContainer').innerHTML =
                        '<div style="padding:2rem; text-align:center; color:var(--sa-text-muted);">Failed to load requests.</div>';
                });
        }

        function applyFilters() {
            const search = document.getElementById('searchInput').value.toLowerCase().trim();
            const statusVal = document.getElementById('statusFilter').value;
            const typeVal = document.getElementById('typeFilter').value;

            filteredRequests = allRequests.filter(r => {
                // Status filter
                if (statusVal !== 'all' && r.status !== statusVal) return false;
                // Type filter
                if (typeVal !== 'all' && r.request_type !== typeVal) return false;
                // Search
                if (search) {
                    const haystack = [
                        r.requester_name, r.target_name,
                        r.target_username, r.reason, r.request_type, r.status
                    ].join(' ').toLowerCase();
                    if (!haystack.includes(search)) return false;
                }
                return true;
            });

            currentPage = 1;
            renderTable();
            updatePagination();
        }

        function renderTable() {
            const container = document.getElementById('requestsTableContainer');
            const info = document.getElementById('resultInfo');
            const total = filteredRequests.length;
            const start = (currentPage - 1) * PER_PAGE;
            const end = Math.min(start + PER_PAGE, total);
            const pageData = filteredRequests.slice(start, end);

            info.textContent = total === 0
                ? 'No requests found.'
                : `Showing ${start + 1}–${end} of ${total} request${total > 1 ? 's' : ''}`;

            if (total === 0) {
                container.innerHTML = `
                    <div style="padding: 3rem 2rem; text-align: center; color: var(--sa-text-muted);">
                        <i class="fa-solid fa-filter-circle-xmark" style="font-size: 2.5rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                        <p style="margin:0; font-weight: 500;">No matching requests.</p>
                    </div>`;
                return;
            }

            let html = `<table class="sa-table">
                <thead><tr>
                    <th>Requester</th>
                    <th>Target User</th>
                    <th>Type</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr></thead><tbody>`;

            pageData.forEach(r => {
                const typeStyle = r.request_type === 'registration'
                    ? 'background:#eff6ff; color:#3b82f6; border:1px solid #bfdbfe;'
                    : r.request_type === 'unblock'
                        ? 'background:#ecfdf5; color:#10b981; border:1px solid #a7f3d0;'
                        : 'background:#fef2f2; color:#ef4444; border:1px solid #fecaca;';

                const statusClass = r.status === 'pending' ? 'status-pending'
                    : r.status === 'approved' ? 'status-ok' : 'status-trash';

                html += `<tr>
                    <td style="font-weight:500;">${escapeHtml(r.requester_name)}</td>
                    <td>${escapeHtml(r.target_name)}
                        <div class="text-muted" style="font-size:0.8rem; margin-top:2px;">@${escapeHtml(r.target_username)}</div></td>
                    <td><span class="status-badge" style="font-size:0.75rem; font-weight:600; padding:4px 10px; ${typeStyle}">${(r.request_type || 'N/A').toUpperCase()}</span></td>
                    <td class="text-muted" style="max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${escapeHtml(r.reason)}</td>
                    <td><span class="status-badge ${statusClass}">${capitalize(r.status)}</span></td>
                    <td class="text-muted" style="font-size:0.85rem; white-space:nowrap;">${new Date(r.created_at).toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'})}</td>
                    <td>`;

                if (r.status === 'pending') {
                    const isReg = r.request_type === 'registration';
                    const btnText = isReg ? 'Approve' : (r.request_type === 'unblock' ? 'Approve' : 'Approve');
                    const btnColor = isReg ? 'var(--sa-primary)' : (r.request_type === 'unblock' ? 'var(--sa-success)' : 'var(--sa-success)');
                    html += `<div style="display:flex; gap:4px;">
                        <button class="btn-secondary" style="padding:4px 10px; font-size:0.75rem; color:${btnColor}; border-color:${btnColor};" onclick="handleRequest(${r.request_id}, 'approve')">${btnText}</button>
                        <button class="btn-secondary" style="padding:4px 10px; font-size:0.75rem; border-color:red; color:red;" onclick="handleRequest(${r.request_id}, 'reject')">Reject</button>
                    </div>`;
                } else {
                    html += `<span class="text-muted" style="font-size:0.85rem;">Processed</span>`;
                }

                html += `</td></tr>`;
            });

            html += '</tbody></table>';
            container.innerHTML = html;
        }

        function updatePagination() {
            const bar = document.getElementById('paginationBar');
            const total = filteredRequests.length;
            const totalPages = Math.max(1, Math.ceil(total / PER_PAGE));
            window.renderPagination(
                bar, currentPage, totalPages, PER_PAGE,
                function(newPage) { goPage(newPage); },
                function(newLimit) { PER_PAGE = newLimit; currentPage = 1; renderTable(); updatePagination(); }
            );
        }

        function goPage(p) {
            const totalPages = Math.max(1, Math.ceil(filteredRequests.length / PER_PAGE));
            if (p < 1 || p > totalPages) return;
            currentPage = p;
            renderTable();
            updatePagination();
            document.getElementById('requestsTableContainer').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function handleRequest(id, action) {
            if (!confirm(`Are you sure you want to ${action} this request?`)) return;
            const fd = new FormData();
            fd.append('request_id', id);
            fd.append('action', action);
            fetch(api + '/superadmin_request_action.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        if (d.superadmin_swap) {
                            alert("CRITICAL SECURITY ACTION: You have approved the unblocking of another Superadmin. As the system only allows one active Superadmin, your account has been blocked and you will now be logged out. Please log in with the other Superadmin's credentials.");
                            window.location.href = '../auth/logout.php';
                            return;
                        }
                        loadRequests();
                    } else {
                        alert(d.error || 'Action failed.');
                    }
                });
        }

        function escapeHtml(s) { const d = document.createElement('div'); d.textContent = s || ''; return d.innerHTML; }
        function capitalize(s) { return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''; }

        // Event listeners
        document.getElementById('searchInput').addEventListener('input', applyFilters);
        document.getElementById('statusFilter').addEventListener('change', applyFilters);
        document.getElementById('typeFilter').addEventListener('change', applyFilters);

        // Init
        loadRequests();
    </script>
</body>
</html>
