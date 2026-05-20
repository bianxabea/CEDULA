/**
 * Admin - My Approval Requests (AMORA-style)
 */
document.addEventListener('DOMContentLoaded', function() {
    const API_BASE = typeof API_BASE !== 'undefined' ? API_BASE : '';
    const container = document.getElementById('approvals-container');
    const paginationEl = document.getElementById('pagination');
    const statusFilters = document.querySelectorAll('.status-filter');
    const searchInput = document.getElementById('searchInput');
    let currentPage = 1;
    let currentStatus = 'pending';
    let currentSearch = '';
    let perPage = 10;
    let searchTimeout = null;

    function escapeHtml(s) {
        if (s == null) return '';
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }
    function formatDate(s) {
        if (!s) return '—';
        const d = new Date(s);
        return isNaN(d.getTime()) ? s : d.toLocaleString();
    }

    function loadApprovals(page, status) {
        if (!container) return;
        container.innerHTML = '<div class="loading-state"><i class="fa-solid fa-spinner fa-spin"></i><p>Loading...</p></div>';
        if (status === 'all') {
            Promise.all([
                fetch(API_BASE + 'approval_list.php?status=pending&per_page=50' + (currentSearch ? '&search=' + encodeURIComponent(currentSearch) : ''), { credentials: 'same-origin' }).then(r => r.json()),
                fetch(API_BASE + 'approval_list.php?status=approved&per_page=50' + (currentSearch ? '&search=' + encodeURIComponent(currentSearch) : ''), { credentials: 'same-origin' }).then(r => r.json()),
                fetch(API_BASE + 'approval_list.php?status=rejected&per_page=50' + (currentSearch ? '&search=' + encodeURIComponent(currentSearch) : ''), { credentials: 'same-origin' }).then(r => r.json())
            ]).then(function(arr) {
                let all = [];
                arr.forEach(function(data) { if (data.success && data.approvals) all = all.concat(data.approvals); });
                all.sort(function(a, b) { return new Date(b.created_at) - new Date(a.created_at); });
                displayApprovals(all);
                paginationEl.innerHTML = '';
            }).catch(function() {
                container.innerHTML = '<div class="empty-state"><p>Error loading requests.</p></div>';
            });
            return;
        }
        let url = API_BASE + 'approval_list.php?page=' + page + '&status=' + encodeURIComponent(status) + '&per_page=' + perPage;
        if (currentSearch) url += '&search=' + encodeURIComponent(currentSearch);
        fetch(url, { credentials: 'same-origin' })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    displayApprovals(data.approvals);
                    displayPagination(data.pagination);
                } else {
                    container.innerHTML = '<div class="empty-state"><p>' + escapeHtml(data.error || 'Failed') + '</p></div>';
                }
            })
            .catch(function() {
                container.innerHTML = '<div class="empty-state"><p>Network error.</p></div>';
            });
    }

    function actionLabel(actionType) {
        if (actionType === 'delete_user') return 'Delete user';
        if (actionType === 'delete_restaurant') return 'Delete restaurant';
        if (actionType === 'delete_menu_item') return 'Delete menu item';
        return actionType || 'Request';
    }

    function displayApprovals(approvals) {
        if (!approvals || approvals.length === 0) {
            container.innerHTML = '<div class="empty-state"><p>No requests found.</p></div>';
            return;
        }
        container.innerHTML = approvals.map(function(a) {
            var reviewer = (a.reviewer_firstName && a.reviewer_lastName) ? (a.reviewer_firstName + ' ' + a.reviewer_lastName) : null;
            var reviewBlock = a.status !== 'pending' ? (
                '<div class="approval-card-meta" style="margin-top:8px;padding-top:8px;border-top:1px solid #eee;">' +
                '<strong>Reviewed by:</strong> ' + escapeHtml(reviewer || '—') + '<br>' +
                '<strong>At:</strong> ' + formatDate(a.reviewed_at) + '<br>' +
                (a.review_notes ? '<strong>Notes:</strong> ' + escapeHtml(a.review_notes) : '') +
                '</div>'
            ) : '<div class="approval-card-meta" style="margin-top:8px;"><em>Awaiting superadmin review...</em></div>';
            return '<div class="approval-card">' +
                '<div class="approval-card-title">' + actionLabel(a.action_type) + ' (target: ' + escapeHtml(a.target_type) + ' #' + escapeHtml(a.target_id) + ')</div>' +
                '<span class="approval-status ' + a.status + '">' + a.status + '</span>' +
                '<div class="approval-card-meta">Submitted: ' + formatDate(a.created_at) + '</div>' +
                (a.reason ? '<p><strong>Reason:</strong> ' + escapeHtml(a.reason) + '</p>' : '') +
                reviewBlock +
                '</div>';
        }).join('');
    }

    function displayPagination(p) {
        if (!paginationEl || !p) return;
        var totalPgs = p.total_pages || 1;
        renderPagination(
            paginationEl, currentPage, totalPgs, perPage,
            function(newPage) { currentPage = newPage; loadApprovals(currentPage, currentStatus); },
            function(newLimit) { perPage = newLimit; currentPage = 1; loadApprovals(1, currentStatus); }
        );
    }

    statusFilters.forEach(function(btn) {
        if (btn.dataset.status === currentStatus) btn.classList.add('active');
        btn.addEventListener('click', function() {
            statusFilters.forEach(function(f) { f.classList.remove('active'); });
            this.classList.add('active');
            currentStatus = this.dataset.status;
            currentPage = 1;
            loadApprovals(currentPage, currentStatus);
        });
    });
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                currentSearch = searchInput.value.trim();
                currentPage = 1;
                loadApprovals(currentPage, currentStatus);
            }, 400);
        });
    }
    loadApprovals(1, currentStatus);
});
