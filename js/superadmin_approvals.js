/**
 * Superadmin - Approval Requests: list, approve/reject (AMORA-style)
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
    let reviewModalApprovalId = null;

    function escapeHtml(s) {
        if (s == null) return '';
        var d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }
    function formatDate(s) {
        if (!s) return '—';
        var d = new Date(s);
        return isNaN(d.getTime()) ? s : d.toLocaleString();
    }
    function actionLabel(actionType) {
        if (actionType === 'delete_user') return 'Delete user';
        if (actionType === 'delete_restaurant') return 'Delete restaurant';
        if (actionType === 'delete_menu_item') return 'Delete menu item';
        return actionType || 'Request';
    }

    function loadApprovals(page, status) {
        if (!container) return;
        container.innerHTML = '<div class="loading-state"><i class="fa-solid fa-spinner fa-spin"></i><p>Loading...</p></div>';
        if (status === 'all') {
            var search = currentSearch ? '&search=' + encodeURIComponent(currentSearch) : '';
            Promise.all([
                fetch(API_BASE + 'approval_list.php?status=pending&per_page=50' + search, { credentials: 'same-origin' }).then(function(r) { return r.json(); }),
                fetch(API_BASE + 'approval_list.php?status=approved&per_page=50' + search, { credentials: 'same-origin' }).then(function(r) { return r.json(); }),
                fetch(API_BASE + 'approval_list.php?status=rejected&per_page=50' + search, { credentials: 'same-origin' }).then(function(r) { return r.json(); })
            ]).then(function(arr) {
                var all = [];
                arr.forEach(function(data) { if (data.success && data.approvals) all = all.concat(data.approvals); });
                all.sort(function(a, b) { return new Date(b.created_at) - new Date(a.created_at); });
                displayApprovals(all);
                paginationEl.innerHTML = '';
            }).catch(function() {
                container.innerHTML = '<div class="empty-state"><p>Error loading.</p></div>';
            });
            return;
        }
        var url = API_BASE + 'approval_list.php?page=' + page + '&status=' + encodeURIComponent(status) + '&per_page=' + perPage;
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

    function displayApprovals(approvals) {
        if (!approvals || approvals.length === 0) {
            container.innerHTML = '<div class="empty-state"><p>No requests found.</p></div>';
            return;
        }
        container.innerHTML = approvals.map(function(a) {
            var reviewer = (a.reviewer_firstName && a.reviewer_lastName) ? (a.reviewer_firstName + ' ' + a.reviewer_lastName) : null;
            var requester = (a.requester_firstName && a.requester_lastName) ? (a.requester_firstName + ' ' + a.requester_lastName) : a.requested_by;
            var reviewBlock = a.status !== 'pending' ? (
                '<div class="approval-card-meta" style="margin-top:8px;padding-top:8px;border-top:1px solid #eee;">' +
                'Reviewed by ' + escapeHtml(reviewer || '—') + ' at ' + formatDate(a.reviewed_at) +
                (a.review_notes ? '<br>Notes: ' + escapeHtml(a.review_notes) : '') + '</div>'
            ) : (
                '<div class="approval-card-actions" style="margin-top:8px;">' +
                '<button type="button" class="btn-sm btn-view review-approve" data-id="' + a.id + '"><i class="fa-solid fa-check"></i> Approve</button> ' +
                '<button type="button" class="btn-sm btn-request-delete review-reject" data-id="' + a.id + '"><i class="fa-solid fa-times"></i> Reject</button>' +
                '</div>'
            );
            return '<div class="approval-card" data-id="' + a.id + '">' +
                '<div class="approval-card-title">' + actionLabel(a.action_type) + ' — ' + escapeHtml(a.target_type) + ' #' + escapeHtml(a.target_id) + '</div>' +
                '<span class="approval-status ' + a.status + '">' + a.status + '</span>' +
                '<div class="approval-card-meta">By ' + escapeHtml(requester) + ' · ' + formatDate(a.created_at) + '</div>' +
                (a.reason ? '<p><strong>Reason:</strong> ' + escapeHtml(a.reason) + '</p>' : '') +
                reviewBlock +
                '</div>';
        }).join('');

        container.querySelectorAll('.review-approve').forEach(function(btn) {
            btn.addEventListener('click', function() { openReviewModal(parseInt(this.dataset.id, 10), 'approve'); });
        });
        container.querySelectorAll('.review-reject').forEach(function(btn) {
            btn.addEventListener('click', function() { openReviewModal(parseInt(this.dataset.id, 10), 'reject'); });
        });
    }

    function displayPagination(p) {
        if (!paginationEl || !p) return;
        var totalPgs = p.total_pages || 1;
        renderPagination(
            paginationEl,
            currentPage,
            totalPgs,
            perPage,
            function(newPage) { currentPage = newPage; loadApprovals(currentPage, currentStatus); },
            function(newLimit) { perPage = newLimit; currentPage = 1; loadApprovals(1, currentStatus); }
        );
    }

    function openReviewModal(approvalId, action) {
        reviewModalApprovalId = approvalId;
        var modal = document.getElementById('reviewApprovalModal');
        var title = document.getElementById('reviewApprovalTitle');
        var msg = document.getElementById('reviewApprovalMessage');
        if (title) title.textContent = action === 'approve' ? 'Approve request' : 'Reject request';
        if (msg) msg.textContent = action === 'approve' ? 'Add optional notes and confirm approval.' : 'Add optional notes and confirm rejection.';
        var notes = document.getElementById('reviewApprovalNotes');
        if (notes) notes.value = '';
        if (modal) modal.classList.add('show');
    }

    function submitReview(action) {
        if (reviewModalApprovalId == null) return;
        var notesEl = document.getElementById('reviewApprovalNotes');
        var notes = notesEl && notesEl.value ? notesEl.value.trim() : '';
        var fd = new FormData();
        fd.append('approval_id', reviewModalApprovalId);
        fd.append('action', action);
        fd.append('review_notes', notes);
        fetch(API_BASE + 'approval_review.php', { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                document.getElementById('reviewApprovalModal').classList.remove('show');
                reviewModalApprovalId = null;
                showNotification(data.success ? 'Success' : 'Error', data.message || data.error || (data.success ? 'Done.' : 'Failed'));
                if (data.success) loadApprovals(currentPage, currentStatus);
            })
            .catch(function() {
                showNotification('Error', 'Network error.');
            });
    }

    function showNotification(title, message) {
        var modal = document.getElementById('notificationModal');
        var titleEl = document.getElementById('notificationTitle');
        var msgEl = document.getElementById('notificationMessage');
        if (titleEl) titleEl.textContent = title;
        if (msgEl) msgEl.textContent = message;
        if (modal) {
            modal.classList.add('show');
            modal.classList.toggle('error', title === 'Error');
            modal.classList.toggle('success', title === 'Success');
        }
        var close = function() {
            modal.classList.remove('show');
            document.getElementById('notificationFooterBtn').removeEventListener('click', close);
        };
        document.getElementById('notificationFooterBtn').addEventListener('click', close);
    }

    document.getElementById('reviewApprovalCancel') && document.getElementById('reviewApprovalCancel').addEventListener('click', function() {
        document.getElementById('reviewApprovalModal').classList.remove('show');
        reviewModalApprovalId = null;
    });
    document.getElementById('reviewApprovalApprove') && document.getElementById('reviewApprovalApprove').addEventListener('click', function() { submitReview('approve'); });
    document.getElementById('reviewApprovalReject') && document.getElementById('reviewApprovalReject').addEventListener('click', function() { submitReview('reject'); });

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
