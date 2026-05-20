<?php
/**
 * My Requests (Admin)
 * View status of block requests submitted by this admin.
 */
session_start();
require_once '../database/db_connect.php';
require_once '../includes/auth.php';
requireRole('admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requests - Pizza Crust Delight</title>
    <link rel="stylesheet" href="../../css/design-system.css">
    <link rel="stylesheet" href="../../css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .data-table { width: 100%; border-collapse: collapse; background: var(--bg-card); border-radius: 8px; overflow: hidden; }
        .data-table th, .data-table td { padding: 12px; text-align: left; border-bottom: 1px solid var(--border-color); }
        .data-table th { background: var(--bg-body); font-weight: 600; }
    </style>
</head>
<body class="dashboard-layout">
    <?php include __DIR__ . '/../includes/layout/navbar.php'; ?>

    <?php $currentPage = 'admin_requests';
include __DIR__ . '/../includes/layout/sidebar.php'; ?>

    <main class="page-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <div>
                    <h1 class="page-title" style="margin-bottom: 0;">Requests</h1>
                    <p class="page-subtitle">Manage consumer registration and block requests.</p>
                </div>
                <span id="requestCountBadge" class="status-badge no-dot" style="background: var(--primary-color); color: white; padding: 0.2rem 0.8rem; font-size: 0.9rem; border-radius: 20px;">0 Requests</span>
            </div>

            <!-- Search and Filters -->
            <div class="card" style="margin-bottom: 1rem; padding: 1rem;">
                <div style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
                    <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
                        <label>Search Targets</label>
                        <input type="text" id="searchInput" placeholder="Search by name, username, or email..." onkeyup="handleSearch(event)">
                    </div>
                    <div class="form-group" style="width: 150px; margin-bottom: 0;">
                        <label>Status</label>
                        <select id="filterStatus" onchange="applyFilters()">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <button class="btn-secondary" onclick="resetFilters()">Reset</button>
                </div>
            </div>

            <div id="requestsTableContainer">Loading...</div>

            <div id="paginationContainer" style="margin-top: 1rem; display: flex; justify-content: flex-end; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div id="paginationInfo" class="hint">Showing 0-0 of 0 requests</div>
                <div id="paginationControls" style="display: flex; gap: 0.5rem; align-items: center;">
                    <!-- Page buttons will be injected here -->
                </div>
            </div>
    </main>
    <?php include __DIR__ . '/../includes/layout/footer.php'; ?>

    <script src="../../js/pagination_util.js"></script>
    <script>
        const api = '../../php/database';

        let currentPage = 1;
        let limit = 10;
        let currentSearch = '';
        let currentStatus = '';

        function loadRequests(page = 1) {
            currentPage = page;
            const params = new URLSearchParams({
                page: currentPage,
                limit: limit,
                search: currentSearch,
                status: currentStatus
            });

            fetch(api + '/admin_requests_list.php?' + params.toString())
                .then(r => r.json())
                .then(data => {
                    if (!data.success) return;

                    let html = '<table class="data-table"><thead><tr><th>Target User</th><th>Reason</th><th>Date</th><th>Status</th><th>Notes</th><th>Action</th></tr></thead><tbody>';

                    if (data.requests.length === 0) {
                        html += '<tr><td colspan="5" style="text-align:center; padding:2rem;">No requests found matching your criteria.</td></tr>';
                    } else {
                        data.requests.forEach(r => {
                            let statusClass = 'status-pending';
                            if (r.status === 'approved') statusClass = 'status-ok';
                            if (r.status === 'rejected') statusClass = 'status-trash';

                             html += `<tr>
                                 <td>
                                     <div style="font-weight:600">${escapeHtml(r.firstName + ' ' + r.lastName)}</div>
                                     <div class="muted small">@${escapeHtml(r.username)}</div>
                                     <div style="margin-top: 0.25rem;">
                                         <span class="status-badge no-dot" style="font-size: 0.75rem;
                                            ${r.request_type === 'registration' ? 'background: #dbeafe; color: #2563eb;' :
                                              r.request_type === 'unblock' ? 'background: #dcfce7; color: #16a34a;' :
                                              'background: #fee2e2; color: #dc2626;'}">
                                             ${r.request_type.toUpperCase()}
                                         </span>
                                     </div>
                                 </td>
                                 <td>${escapeHtml(r.reason)}</td>
                                 <td>${new Date(r.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</td>
                                 <td><span class="status-badge no-dot ${statusClass}">${r.status.charAt(0).toUpperCase() + r.status.slice(1)}</span></td>
                                 <td class="muted small">${escapeHtml(r.review_notes || '-')}</td>
                                 <td>
                                     ${r.request_type === 'registration' && r.status === 'pending' ? `
                                         <div style="display:flex; gap:0.5rem;">
                                             <button class="btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; background: var(--success-color);" onclick="handleRequest(${r.id}, 'approve')">Approve</button>
                                             <button class="btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="handleRequest(${r.id}, 'reject')">Reject</button>
                                         </div>
                                     ` : '<span class="muted small">--</span>'}
                                 </td>
                             </tr>`;
                        });
                    }
                    html += '</tbody></table>';
                    document.getElementById('requestsTableContainer').innerHTML = html;

                    updatePagination(data.pagination);
                });
        }

        function updatePagination(p) {
            const controls = document.getElementById('paginationControls');
            const badge = document.getElementById('requestCountBadge');

            badge.textContent = `${p.total_requests} Request${p.total_requests !== 1 ? 's' : ''}`;

            window.renderPagination(
                controls, currentPage, p.total_pages || 1, limit,
                function(newPage) { loadRequests(newPage); },
                function(newLimit) { limit = newLimit; loadRequests(1); }
            );

            const info = document.getElementById('paginationInfo');
            if (info) info.style.display = 'none';
        }

        let searchTimeout;
        function handleSearch(e) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                currentSearch = e.target.value;
                loadRequests(1);
            }, 300);
        }

        function applyFilters() {
            currentStatus = document.getElementById('filterStatus').value;
            loadRequests(1);
        }

        function resetFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('filterStatus').value = '';
            currentSearch = '';
            currentStatus = '';
            loadRequests(1);
        }

        function handleRequest(id, action) {
            if (!confirm(`Are you sure you want to ${action} this registration?`)) return;
            const fd = new FormData();
            fd.append('request_id', id);
            fd.append('action', action);
            fetch(api + '/admin_request_action.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(d => {
                    if (d.success) loadRequests(currentPage);
                    else alert(d.error);
                });
        }

        function escapeHtml(s) { const d = document.createElement('div'); d.textContent = s || ''; return d.innerHTML; }

        loadRequests();
    </script>
</body>
</html>
