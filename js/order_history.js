(function () {
    const api = (window.BASE_URL || '') + '/php/database';
    const list = document.getElementById('ordersList');
    const paginationEl = document.getElementById('orderPagination');
    const searchInput = document.getElementById('orderSearch');
    const filterTabs = document.querySelectorAll('.order-history-filters .filter-tab');
    if (!list) return;

    let currentPage = 1;
    let perPage = 10;
    let statusFilter = '';
    let searchQuery = '';
    let searchTimeout = null;

    function escapeHtml(s) {
        if (!s) return '';
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    function statusClass(s) {
        if (s === 'delivered') return 'status-ok';
        if (s === 'cancelled') return 'status-cancel';
        return 'status-pending';
    }

    function formatStatus(s) {
        return (s || '').replace(/_/g, ' ');
    }

    function itemPreviewText(preview) {
        if (!preview || !Array.isArray(preview) || preview.length === 0) return '—';
        if (preview.length === 1) return escapeHtml(preview[0]);
        return escapeHtml(preview[0]) + ', ' + escapeHtml(preview[1]) + (preview.length > 2 ? '…' : '');
    }

    function buildUrl() {
        const params = new URLSearchParams();
        if (statusFilter) params.set('status', statusFilter);
        if (searchQuery) params.set('search', searchQuery);
        params.set('page', String(currentPage));
        params.set('per_page', String(perPage));
        return api + '/orders_list.php?' + params.toString();
    }

    function renderPagination(pagination) {
        if (!paginationEl || !pagination || pagination.total_pages <= 1) {
            if (paginationEl) paginationEl.innerHTML = '';
            return;
        }
        const { page, total_pages, total } = pagination;
        window.renderPagination(
            paginationEl, page, total_pages, perPage,
            function(newPage) { currentPage = newPage; loadOrders(); },
            function(newLimit) { perPage = newLimit; currentPage = 1; loadOrders(); }
        );
    }

    function loadOrders() {
        list.innerHTML = '<div class="orders-loading"><p>Loading orders…</p></div>';
        fetch(buildUrl(), { credentials: 'same-origin' })
            .then(r => r.json())
            .then(data => {
                if (!data.success) {
                    list.innerHTML = '<div class="empty-state"><p class="muted">Could not load orders.</p></div>';
                    if (paginationEl) paginationEl.innerHTML = '';
                    return;
                }
                const orders = data.orders || [];
                const pagination = data.pagination || {};
                if (orders.length === 0) {
                    list.innerHTML = '<div class="empty-state"><p class="muted">No orders found.</p><p><a href="order_food.php">Order food</a> to get started.</p></div>';
                    renderPagination(null);
                    return;
                }
                list.innerHTML = '';
                orders.forEach(o => {
                    const card = document.createElement('div');
                    card.className = 'order-card';
                    const preview = itemPreviewText(o.item_preview);
                    card.innerHTML =
                        '<div class="order-card-header">' +
                        '<span class="order-card-id">Order #' + escapeHtml(String(o.id)) + '</span>' +
                        '<span class="order-card-date">' + escapeHtml(o.created_at || '') + '</span>' +
                        '<span class="status-badge ' + statusClass(o.status) + '">' + formatStatus(o.status) + '</span>' +
                        '</div>' +
                        '<div class="order-card-body">' +
                        '<p class="order-card-restaurant">' + escapeHtml(o.restaurant_name || '') + '</p>' +
                        '<p class="order-card-items">' + preview + '</p>' +
                        '<p class="order-card-total">₱' + parseFloat(o.total_amount || 0).toFixed(2) + '</p>' +
                        '</div>' +
                        '<div class="order-card-actions">' +
                        '<a href="track_order.php?order_id=' + encodeURIComponent(o.id) + '" class="btn-track">Track order</a>' +
                        '<a href="track_order.php?order_id=' + encodeURIComponent(o.id) + '" class="btn-details">View details</a>' +
                        '</div>';
                    list.appendChild(card);
                });
                renderPagination(pagination);
            })
            .catch(() => {
                list.innerHTML = '<div class="empty-state"><p class="muted">Could not load orders. Check your connection and try again.</p></div>';
                if (paginationEl) paginationEl.innerHTML = '';
            });
    }

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function () {
                searchQuery = searchInput.value.trim();
                currentPage = 1;
                loadOrders();
            }, 300);
        });
    }
    filterTabs.forEach(tab => {
        tab.addEventListener('click', function () {
            filterTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            statusFilter = this.getAttribute('data-status') || '';
            currentPage = 1;
            loadOrders();
        });
    });

    loadOrders();
})();
