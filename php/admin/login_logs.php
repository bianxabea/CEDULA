<?php
/**
 * Admin — Login History (AMORA-style port)
 * Accessible by admin role. Shows all login/logout events (filtered by their own
 * session data via superadmin_logs_list.php). Uses CEDULA's standardized layout.
 */
session_start();
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin');
require_once __DIR__ . '/../includes/path_helper.php';

$pageTitle  = 'Login History';
$basePath   = getBasePath(__FILE__);
$baseUrl    = getBaseUrl();
$user       = $_SESSION['user'] ?? [];
$userRole   = $user['role'] ?? 'admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - Pizza Crust Delight</title>
    <link rel="stylesheet" href="<?php echo $basePath; ?>css/serve_asset.php?file=design-system.css">
    <link rel="stylesheet" href="<?php echo $basePath; ?>css/serve_asset.php?file=dashboard.css">
    <link rel="stylesheet" href="<?php echo $basePath; ?>css/serve_asset.php?file=admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .filter-row { display:flex; gap:.75rem; flex-wrap:wrap; align-items:center; margin-bottom:1.25rem; }
        .filter-row input { padding:.5rem .75rem; border:1px solid var(--border-color); border-radius:8px; font-size:.875rem; background:var(--bg-card); color:var(--text-heading); }
        .filter-row input:focus { outline:none; border-color:#c51332; }
        .logs-table { width:100%; border-collapse:collapse; }
        .logs-table th, .logs-table td { padding:11px 14px; text-align:left; border-bottom:1px solid var(--border-color); font-size:.875rem; }
        .logs-table th { background:var(--bg-body); font-weight:600; color:var(--text-heading); }
        .logs-table tr:hover td { background:rgba(197,19,50,.03); }
    </style>
</head>
<body class="dashboard-layout admin-theme-v2">
    <?php include __DIR__ . '/../includes/layout/navbar.php'; ?>
    <div class="dashboard-container">
        <div class="sidebar-overlay" id="sidebarOverlay" aria-hidden="true"></div>
        <?php $currentPage = 'admin_login_logs'; include __DIR__ . '/../includes/layout/sidebar.php'; ?>

        <main class="dashboard-main">
            <header class="island-header reveal-up">
                <div class="island-welcome">
                    <h1 class="page-title">
                        <i class="fa-solid fa-clock-rotate-left" style="color:#c51332; margin-right:.5rem;"></i>Login History
                    </h1>
                    <p class="page-subtitle" style="margin:0;">All user login and logout session events.</p>
                </div>
            </header>

            <div class="widget-card reveal-up" style="background:var(--bg-card); border-radius:12px; box-shadow:var(--shadow-sm); padding:1.5rem; border:1px solid var(--border-color); margin-top:1.5rem;">
                <div class="filter-row">
                    <input type="text" id="filterSearch" placeholder="Search by name or username…" oninput="debounceLoad()">
                    <select id="filterRole" class="input-field" style="width:auto;" onchange="loadLogs(1)">
                        <option value="">All Roles</option>
                        <option value="superadmin">Superadmin</option>
                        <option value="admin">Admin</option>
                        <option value="consumer">Consumer</option>
                    </select>
                    <input type="date" id="filterDate" style="padding:.5rem .75rem; border:1px solid var(--border-color); border-radius:8px; background:var(--bg-card); color:var(--text-heading);" onchange="loadLogs(1)">
                    <button class="btn-secondary" onclick="resetFilters()">Reset</button>
                    <span id="totalLabel" class="text-muted" style="font-size:.85rem; margin-left:auto;"></span>
                </div>

                <div id="logsContainer"><p class="text-muted">Loading…</p></div>

                <div style="display:flex; justify-content:space-between; align-items:center; margin-top:1rem; flex-wrap:wrap; gap:.75rem;">
                    <span id="pageInfo" class="text-muted" style="font-size:.85rem;"></span>
                    <div style="display:flex; gap:4px;">
                        <button id="prevBtn" class="btn-secondary" style="padding:4px 12px; font-size:.85rem;" onclick="changePage(-1)" disabled>Previous</button>
                        <button id="nextBtn" class="btn-secondary" style="padding:4px 12px; font-size:.85rem;" onclick="changePage(1)" disabled>Next</button>
                    </div>
                </div>
            </div>
        </main>
        <?php include __DIR__ . '/../includes/layout/footer.php'; ?>
    </div>

    <script>
    (function(){
        var t=document.getElementById('sidebarToggle'), o=document.getElementById('sidebarOverlay');
        if(t&&o){ t.addEventListener('click',function(){ document.body.classList.toggle('sidebar-open'); o.classList.toggle('is-open',document.body.classList.contains('sidebar-open')); }); o.addEventListener('click',function(){ document.body.classList.remove('sidebar-open'); o.classList.remove('is-open'); }); }
    })();

    const API = '<?php echo $baseUrl; ?>/php/database/superadmin_logs_list.php';
    let currentPage = 1, totalPages = 1, debounceTimer;

    function debounceLoad() { clearTimeout(debounceTimer); debounceTimer = setTimeout(() => loadLogs(1), 300); }

    function loadLogs(page = 1) {
        currentPage = page;
        const search = document.getElementById('filterSearch').value;
        const date   = document.getElementById('filterDate').value;
        const role   = document.getElementById('filterRole').value;
        const params = new URLSearchParams({ search, date, role, page });
        document.getElementById('logsContainer').innerHTML = '<p class="text-muted">Loading…</p>';
        fetch(API + '?' + params)
            .then(r => r.json())
            .then(data => {
                if (!data.success) { document.getElementById('logsContainer').innerHTML = '<p class="text-muted">Failed to load.</p>'; return; }
                totalPages = data.pagination?.total_pages ?? 1;
                updatePagination();
                document.getElementById('totalLabel').textContent = `Total: ${data.pagination?.total ?? 0} records`;
                if (!data.logs?.length) {
                    document.getElementById('logsContainer').innerHTML = '<div style="text-align:center;padding:2rem;color:var(--text-muted);"><i class="fa-solid fa-inbox" style="font-size:2rem;margin-bottom:1rem;"></i><p style="margin:0">No records found.</p></div>';
                    return;
                }
                let html = '<table class="logs-table"><thead><tr><th>User</th><th>Role</th><th>Login Time</th><th>Logout Time</th><th>Duration</th></tr></thead><tbody>';
                data.logs.forEach(l => {
                    const login  = new Date(l.login_time);
                    const logout = l.logout_time ? new Date(l.logout_time) : null;
                    const fmtL   = login.toLocaleString();
                    const fmtLo  = logout ? logout.toLocaleString() : '<span class="text-muted">—</span>';
                    let dur = '<span class="text-muted">Active</span>';
                    if (logout) { const m = Math.floor((logout-login)/60000); dur = `${Math.floor(m/60)}h ${m%60}m`; }
                    const rc = l.role==='superadmin'?'status-trash': l.role==='admin'?'status-ok':'status-pending';
                    html += `<tr>
                        <td><div style="font-weight:500">${esc(l.firstName+' '+l.lastName)}</div><div class="text-muted" style="font-size:.8rem">@${esc(l.username)}</div></td>
                        <td><span class="status-badge ${rc}">${l.role}</span></td>
                        <td style="font-size:.85rem">${fmtL}</td>
                        <td style="font-size:.85rem">${fmtLo}</td>
                        <td style="font-size:.85rem;font-weight:500">${dur}</td>
                    </tr>`;
                });
                html += '</tbody></table>';
                document.getElementById('logsContainer').innerHTML = html;
            })
            .catch(() => { document.getElementById('logsContainer').innerHTML = '<p class="text-muted">Error loading logs.</p>'; });
    }

    function updatePagination() {
        document.getElementById('pageInfo').textContent = `Page ${currentPage} of ${totalPages}`;
        document.getElementById('prevBtn').disabled = currentPage <= 1;
        document.getElementById('nextBtn').disabled = currentPage >= totalPages;
    }
    function changePage(d) { const n = currentPage+d; if(n>=1&&n<=totalPages) loadLogs(n); }
    function resetFilters() { document.getElementById('filterSearch').value=''; document.getElementById('filterDate').value=''; document.getElementById('filterRole').value=''; loadLogs(1); }
    function esc(s) { const d=document.createElement('div'); d.textContent=s||''; return d.innerHTML; }

    loadLogs();
    </script>
</body>
</html>
