<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
requireRole('superadmin');
$pageTitle = 'Login Logs';
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
        .filters { display: flex; gap: 1rem; margin-bottom: 1rem; align-items: center; }
        .logs-table { width: 100%; border-collapse: collapse; background: var(--bg-card); border-radius: 8px; overflow: hidden; }
        .logs-table th, .logs-table td { padding: 12px; text-align: left; border-bottom: 1px solid var(--border-color); }
        .logs-table th { background: var(--bg-body); font-weight: 600; }

        /* Print / PDF styles */
        @media print {
            body * { visibility: hidden; }
            #printArea, #printArea * { visibility: visible; }
            #printArea { position: absolute; left: 0; top: 0; width: 100%; }
            #printArea table { width: 100%; border-collapse: collapse; font-size: 11px; }
            #printArea th, #printArea td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
            #printArea th { background: #f0f0f0 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .print-header { visibility: visible; text-align: center; margin-bottom: 1rem; }
            .print-header h2 { margin: 0; font-size: 16px; }
            .print-header p { margin: 0.25rem 0 0; font-size: 11px; color: #666; }
        }
    </style>
</head>
<body class="dashboard-layout superadmin-v2">
    <?php include __DIR__ . '/../includes/layout/navbar.php'; ?>

    <!-- Sidebar -->
    <?php $currentPage = 'superadmin_logs';
include __DIR__ . '/../includes/layout/sidebar.php'; ?>

    <main class="page-content">
            <header style="margin-bottom: 2rem;">
                <h1 class="page-title" style="font-size: 1.75rem; margin-bottom: 0.25rem;">Security Logs</h1>
                <p class="page-subtitle text-muted" style="margin: 0; font-size: 0.95rem;">Audit events, authentications, and system actions.</p>
            </header>

            <article class="sa-box">
                <header class="sa-box-header" style="flex-wrap: wrap; gap: 1rem;">
                    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center; flex: 1;">
                        <input type="text" id="filterSearch" class="input-field" placeholder="Search by name or username" style="min-width: 200px; flex: 1;" onkeyup="debounceLoadLogs()">
                        <select id="filterRole" class="input-field" style="width: auto;" onchange="loadLogs(1)">
                            <option value="">All Roles</option>
                            <option value="superadmin">Superadmin</option>
                            <option value="admin">Admin</option>
                            <option value="consumer">Consumer</option>
                        </select>
                        <div style="display:flex; align-items:center; gap:0.5rem;">
                            <label style="font-size: 0.85rem; color: var(--sa-text-muted);">From:</label>
                            <input type="date" id="filterStartDate" class="input-field" style="width: auto;" onchange="loadLogs(1)">
                        </div>
                        <div style="display:flex; align-items:center; gap:0.5rem;">
                            <label style="font-size: 0.85rem; color: var(--sa-text-muted);">To:</label>
                            <input type="date" id="filterEndDate" class="input-field" style="width: auto;" onchange="loadLogs(1)">
                        </div>
                        <button class="btn-secondary" onclick="resetFilters()">Reset</button>
                        <!-- <button class="btn-secondary" onclick="exportCSV()" style="white-space: nowrap;"><i class="fa-solid fa-file-csv" style="margin-right: 6px;"></i>Export CSV</button>
                        <button class="btn-secondary" onclick="exportPDF()" style="white-space: nowrap;"><i class="fa-solid fa-file-pdf" style="margin-right: 6px;"></i>Export PDF</button> -->
                    </div>
                </header>

                <div class="sa-box-content">
                    <div id="printArea">
                        <div class="print-header">
                            <h2>Security Logs Report</h2>                        </div>
                        <div id="logsTableContainer">
                            <div style="padding: 2rem; text-align: center; color: var(--sa-text-muted);">Loading...</div>
                        </div>
                    </div>
                </div>
            </article>

            <div id="paginationContainer" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-top: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span id="logCountBadge" class="status-badge no-dot" style="background: var(--sa-primary-ultralight); color: var(--sa-primary);">0 Logs</span>
                    <span id="paginationInfo" class="text-muted" style="font-size: 0.85rem;">Showing 0-0 of 0 logs</span>
                </div>
                <div id="paginationControls" style="display: flex; gap: 0.25rem; align-items: center;"></div>
            </div>
    </main>
    <?php include __DIR__ . '/../includes/layout/footer.php'; ?>

    <script src="../../js/pagination_util.js"></script>
    <script>
        const api = '../../php/database';
        let currentPage = 1;
        let limit = 10;
        let totalPages = 1;
        let debounceTimer;

        function debounceLoadLogs() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => loadLogs(1), 300);
        }

        function loadLogs(page = 1) {
            currentPage = page;
            const startDate = document.getElementById('filterStartDate').value;
            const endDate = document.getElementById('filterEndDate').value;
            const search = document.getElementById('filterSearch').value;
            const role = document.getElementById('filterRole').value;

            const params = new URLSearchParams({ startDate, endDate, search, role, page, limit });

            document.getElementById('logsTableContainer').innerHTML = '<p class="muted">Loading...</p>';

            fetch(api + '/superadmin_logs_list.php?' + params)
                .then(r => r.json())
                .then(data => {
                    if (!data.success) return;

                    updatePagination(data.pagination);

                    if (data.logs.length === 0) {
                        document.getElementById('logsTableContainer').innerHTML = '<div style="padding: 3rem 2rem; text-align: center; color: var(--sa-text-muted);"><i class="fa-solid fa-history" style="font-size: 2.5rem; color: var(--sa-border); margin-bottom: 1rem;"></i><p style="margin:0; font-weight: 500;">No logs found.</p></div>';
                        return;
                    }

                    let html = '<table class="sa-table"><thead><tr><th>User</th><th>Role</th><th>Login Time</th><th>Logout Time</th><th>Duration</th></tr></thead><tbody>';
                    data.logs.forEach(l => {
                        const loginDate = new Date(l.login_time);
                        const logoutDate = l.logout_time ? new Date(l.logout_time) : null;

                        const fmtLogin = loginDate.toLocaleString();
                        const fmtLogout = logoutDate ? logoutDate.toLocaleString() : '<span class="text-muted">--</span>';

                        let duration = '<span class="text-muted">Currently Active</span>';
                        if (logoutDate) {
                            const diffMs = logoutDate - loginDate;
                            const diffMins = Math.floor(diffMs / 60000);
                            const hrs = Math.floor(diffMins / 60);
                            const mins = diffMins % 60;
                            duration = `${hrs}h ${mins}m`;
                        }

                        let roleClass = 'status-pending';
                        if (l.role === 'admin') roleClass = 'status-ok';
                        if (l.role === 'superadmin') roleClass = 'status-trash';

                        html += `<tr>
                            <td>
                                <div style="font-weight: 500;">${escapeHtml(l.firstName + ' ' + l.lastName)}</div>
                                <div class="text-muted" style="font-size: 0.8rem; margin-top:2px;">@${escapeHtml(l.username)}</div>
                            </td>
                            <td><span class="status-badge ${roleClass}">${l.role}</span></td>
                            <td class="text-muted" style="font-size: 0.85rem;">${fmtLogin}</td>
                            <td class="text-muted" style="font-size: 0.85rem;">${fmtLogout}</td>
                            <td style="font-size: 0.85rem; font-weight: 500;">${duration}</td>
                        </tr>`;
                    });
                    html += '</tbody></table>';
                    document.getElementById('logsTableContainer').innerHTML = html;
                });
        }

        function resetFilters() {
            document.getElementById('filterSearch').value = '';
            document.getElementById('filterRole').value = '';
            document.getElementById('filterStartDate').value = '';
            document.getElementById('filterEndDate').value = '';
            loadLogs(1);
        }

        function updatePagination(p) {
            if (!p) return;
            const controls = document.getElementById('paginationControls');
            const badge = document.getElementById('logCountBadge');
            
            badge.textContent = `${p.total_records} Log${p.total_records !== 1 ? 's' : ''}`;
            
            window.renderPagination(
                controls, currentPage, p.total_pages || 1, limit,
                function(newPage) { loadLogs(newPage); },
                function(newLimit) { limit = newLimit; loadLogs(1); }
            );
            
            const info = document.getElementById('paginationInfo');
            if (info) info.style.display = 'none';
        }

        function escapeHtml(s) { const d = document.createElement('div'); d.textContent = s || ''; return d.innerHTML; }

        function exportPDF() {
            window.print();
        }

        function exportCSV() {
            // Fetch ALL logs (no pagination) by requesting a large page
            const startDate = document.getElementById('filterStartDate').value;
            const endDate = document.getElementById('filterEndDate').value;
            const search = document.getElementById('filterSearch').value;
            const role = document.getElementById('filterRole').value;

            // We'll fetch page by page until we have all
            let allLogs = [];
            let page = 1;

            function fetchPage(p) {
                const params = new URLSearchParams({ startDate, endDate, search, role, page: p });
                return fetch(api + '/superadmin_logs_list.php?' + params)
                    .then(r => r.json())
                    .then(data => {
                        if (!data.success) return;
                        allLogs = allLogs.concat(data.logs);
                        if (p < data.pagination.total_pages) {
                            return fetchPage(p + 1);
                        }
                    });
            }

            fetchPage(1).then(() => {
                if (allLogs.length === 0) {
                    alert('No logs to export.');
                    return;
                }

                // Build CSV
                const headers = ['User', 'Username', 'Role', 'Login Time', 'Logout Time', 'Duration'];
                let csv = headers.join(',') + '\n';

                allLogs.forEach(l => {
                    const loginDate = new Date(l.login_time);
                    const logoutDate = l.logout_time ? new Date(l.logout_time) : null;
                    const fmtLogin = loginDate.toLocaleString();
                    const fmtLogout = logoutDate ? logoutDate.toLocaleString() : 'Active';

                    let duration = 'Active';
                    if (logoutDate) {
                        const diffMs = logoutDate - loginDate;
                        const diffMins = Math.floor(diffMs / 60000);
                        const hrs = Math.floor(diffMins / 60);
                        const mins = diffMins % 60;
                        duration = hrs + 'h ' + mins + 'm';
                    }

                    const row = [
                        '"' + (l.firstName + ' ' + l.lastName).replace(/"/g, '""') + '"',
                        '"' + (l.username || '').replace(/"/g, '""') + '"',
                        l.role,
                        '"' + fmtLogin + '"',
                        '"' + fmtLogout + '"',
                        '"' + duration + '"'
                    ];
                    csv += row.join(',') + '\n';
                });

                // Download
                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'login_logs_' + new Date().toISOString().slice(0, 10) + '.csv';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            });
        }

        loadLogs();
    </script>
</body>
</html>
