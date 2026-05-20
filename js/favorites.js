(function () {
    const api = (window.BASE_URL || '') + '/php/database';
    const list = document.getElementById('favoritesList');
    const searchInput = document.getElementById('favoritesSearch');
    if (!list) return;

    let favorites = [];

    function escapeHtml(s) {
        if (!s) return '';
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    function matchesSearch(f, q) {
        if (!q) return true;
        const lower = q.toLowerCase();
        return (f.name && f.name.toLowerCase().includes(lower)) ||
            (f.restaurant_name && f.restaurant_name.toLowerCase().includes(lower));
    }

    function renderCards(items) {
        if (!items.length) {
            list.innerHTML = '<div class="empty-state favorites-empty"><p class="muted">No favorites match your search.</p><p>Try a different search or <a href="order_food.php">browse restaurants</a> to add more.</p></div>';
            return;
        }
        list.innerHTML = items.map(f => {
            const avail = f.is_available == 1;
            const orderUrl = 'order_food.php' + (f.restaurant_id ? '?restaurant_id=' + encodeURIComponent(f.restaurant_id) : '');
            return '<div class="menu-item favorite-card ' + (!avail ? 'unavailable' : '') + '" data-menu-id="' + escapeHtml(String(f.menu_item_id)) + '">' +
                '<button type="button" class="btn-fav btn-fav-card favorited" data-menu-id="' + escapeHtml(String(f.menu_item_id)) + '" title="Remove from favorites" aria-label="Remove from favorites"><i class="fa-solid fa-heart" aria-hidden="true"></i></button>' +
                '<h4>' + escapeHtml(f.name) + '</h4>' +
                '<p class="muted small">' + escapeHtml(f.restaurant_name) + '</p>' +
                '<span class="price">₱' + parseFloat(f.price).toFixed(2) + '</span>' +
                '<div class="menu-item-actions">' +
                '<a href="' + orderUrl + '" class="btn-order-again">Order again</a>' +
                '</div>' +
                '</div>';
        }).join('');

        list.querySelectorAll('.btn-fav-card').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const menuId = btn.dataset.menuId;
                const fd = new FormData();
                fd.append('menu_item_id', menuId);
                fd.append('action', 'remove');
                fetch(api + '/favorite_toggle.php', { method: 'POST', body: fd, credentials: 'same-origin' })
                    .then(r => r.json())
                    .then(d => {
                        if (d.success) {
                            favorites = favorites.filter(f => String(f.menu_item_id) !== menuId);
                            if (!favorites.length) {
                                list.innerHTML = '<div class="empty-state"><p class="muted">No favorites yet.</p><p><a href="order_food.php">Browse restaurants</a> and tap the heart on items to save them here.</p></div>';
                            } else {
                                renderCards(favorites.filter(f => matchesSearch(f, (searchInput && searchInput.value) ? searchInput.value.trim() : '')));
                            }
                        }
                    });
            });
        });
    }

    function doSearch() {
        const q = (searchInput && searchInput.value) ? searchInput.value.trim() : '';
        const filtered = q ? favorites.filter(f => matchesSearch(f, q)) : favorites;
        renderCards(filtered);
    }

    fetch(api + '/favorites_list.php', { credentials: 'same-origin' })
        .then(r => r.json())
        .then(data => {
            if (!data.success || !data.favorites) {
                list.innerHTML = '<div class="empty-state"><p class="muted">Could not load favorites.</p></div>';
                return;
            }
            favorites = data.favorites;
            if (!favorites.length) {
                list.innerHTML = '<div class="empty-state"><p class="muted">No favorites yet.</p><p><a href="order_food.php">Browse restaurants</a> and tap the heart on items to save them here.</p></div>';
                return;
            }
            doSearch();
        })
        .catch(() => { list.innerHTML = '<div class="empty-state"><p class="muted">Could not load favorites. Try again later.</p></div>'; });

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            if (!favorites.length) return;
            doSearch();
        });
    }
})();
