/**
 * Order Food page: load restaurants, load menu, add to cart (persisted to DB), checkout.
 * Uses BASE_URL and relative paths for API calls.
 */
(function () {
    const base = window.BASE_URL || '';
    const api = base + '/php/database';

    let restaurants = [];
    let menu = [];
    let currentRestaurantId = null;
    let currentRestaurantName = '';
    let paymentMethods = [];

    const $ = (id) => document.getElementById(id);
    const restaurantsView = $('restaurantsView');
    const menuView = $('menuView');
    const checkoutView = $('checkoutView');
    const checkoutTotal = $('checkoutTotal');
    const paymentMethodsList = $('paymentMethodsList');

    function showView(name) {
        restaurantsView.style.display = name === 'restaurants' ? 'block' : 'none';
        menuView.style.display = name === 'menu' ? 'block' : 'none';
        checkoutView.style.display = name === 'checkout' ? 'block' : 'none';
    }

    /* ── Toast notification ── */
    function showToast(message, type) {
        // Remove any existing toast
        const existing = document.querySelector('.toast-notification');
        if (existing) existing.remove();

        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.style.cssText = `
            position: fixed; bottom: 2rem; right: 2rem; z-index: 2000;
            padding: 0.75rem 1.25rem; border-radius: 10px; font-size: 0.9rem;
            font-weight: 600; box-shadow: 0 4px 16px rgba(0,0,0,0.15);
            display: flex; align-items: center; gap: 0.5rem;
            animation: slideInRight 0.3s ease-out;
            ${type === 'success'
                ? 'background: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0;'
                : 'background: #fee2e2; color: #dc2626; border: 1px solid #fecaca;'}
        `;
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        toast.innerHTML = `<i class="fa-solid ${icon}"></i> ${message}`;
        document.body.appendChild(toast);

        // Add animation keyframes if not present
        if (!document.getElementById('toastAnimStyle')) {
            const style = document.createElement('style');
            style.id = 'toastAnimStyle';
            style.textContent = '@keyframes slideInRight { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }';
            document.head.appendChild(style);
        }

        setTimeout(() => {
            toast.style.transition = 'opacity 0.3s ease-out';
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 2500);
    }

    /* ── Add to cart (persisted to DB) ── */
    async function addToCart(item, qty) {
        const num = parseInt(qty, 10) || 1;
        const fd = new FormData();
        fd.append('menu_item_id', item.id);
        fd.append('quantity', num);

        try {
            const res = await fetch(api + '/cart_add.php', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                showToast(`${escapeHtml(item.name)} added to cart!`, 'success');
                if (typeof window.updateNavCartBadge === 'function') window.updateNavCartBadge();
            } else {
                showToast(data.error || 'Failed to add item', 'error');
            }
        } catch (e) {
            showToast('Network error. Please try again.', 'error');
        }
    }

    async function loadRestaurants() {
        try {
            const res = await fetch(api + '/restaurants_list.php');
            const data = await res.json();
            if (data.success && data.restaurants) {
                restaurants = data.restaurants;
                const list = $('restaurantsList');
                if (data.restaurants.length === 0) {
                    list.innerHTML = `<div class="empty-state" style="padding: 2rem; text-align: center; border: 2px dashed var(--border-light, #e5e7eb); border-radius: 12px;">
                        <i class="fa-solid fa-store" style="font-size: 2rem; color: var(--text-muted); margin-bottom: 0.5rem;"></i>
                        <p style="color: var(--text-muted); margin: 0;">No restaurants available at the moment.</p>
                    </div>`;
                    return;
                }
                list.innerHTML = data.restaurants.map(r => {
                    return `<div class="restaurant-card" data-id="${r.id}" data-name="${escapeHtml(r.name)}">
                        <h3>${escapeHtml(r.name)}</h3>
                        <p>${escapeHtml(r.description || r.address || '')}</p>
                    </div>`;
                }).join('');
                list.querySelectorAll('.restaurant-card').forEach(el => {
                    el.addEventListener('click', () => {
                        currentRestaurantId = parseInt(el.dataset.id, 10);
                        currentRestaurantName = el.dataset.name;
                        loadMenu(currentRestaurantId);
                        showView('menu');
                    });
                });
            }
        } catch (e) {
            console.error('Failed to load restaurants:', e);
        }
    }

    async function loadMenu(restaurantId) {
        try {
            const res = await fetch(api + '/menu_list.php?restaurant_id=' + restaurantId);
            const data = await res.json();
            if (data.success && data.menu) {
                menu = data.menu;
                $('menuRestaurantName').textContent = currentRestaurantName;
                const list = $('menuList');

                const availableItems = data.menu.filter(m => m.is_available == 1);
                if (availableItems.length === 0) {
                    list.innerHTML = `<div class="empty-state" style="padding: 2rem; text-align: center; border: 2px dashed var(--border-light, #e5e7eb); border-radius: 12px;">
                        <i class="fa-solid fa-utensils" style="font-size: 2rem; color: var(--text-muted); margin-bottom: 0.5rem;"></i>
                        <p style="color: var(--text-muted); margin: 0;">No menu items available from this restaurant.</p>
                    </div>`;
                    return;
                }

                list.innerHTML = data.menu.map(m => {
                    const avail = m.is_available == 1;
                    return `<div class="menu-item ${!avail ? 'unavailable' : ''}" data-id="${m.id}">
                        <h4>${escapeHtml(m.name)}</h4>
                        ${m.description ? `<p class="muted small">${escapeHtml(m.description)}</p>` : ''}
                        <span class="price">₱${parseFloat(m.price).toFixed(2)}</span>
                        ${avail ? `<div class="menu-item-actions">
                            <input type="number" min="1" value="1">
                            <button type="button" class="btn-add" data-menu-id="${m.id}">Add</button>
                            <button type="button" class="btn-fav" data-menu-id="${m.id}" title="Favorite">♥</button>
                        </div>` : `<div class="menu-item-actions"><span class="muted small">Unavailable</span></div>`}
                    </div>`;
                }).join('');

                list.querySelectorAll('.btn-add').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const mid = parseInt(btn.dataset.menuId, 10);
                        const item = menu.find(m => m.id == mid);
                        if (!item) return;
                        const input = btn.closest('.menu-item').querySelector('input[type="number"]');
                        // Disable button temporarily to prevent double clicks
                        btn.disabled = true;
                        btn.textContent = '...';
                        addToCart({ id: item.id, name: item.name, price: item.price }, input.value)
                            .then(() => {
                                btn.disabled = false;
                                btn.textContent = 'Add';
                                input.value = 1;
                            });
                    });
                });

                list.querySelectorAll('.btn-fav').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.preventDefault();
                        toggleFavorite(btn.dataset.menuId, btn);
                    });
                });

                // Mark already-favorited items
                (async function setFavoritedHearts() {
                    try {
                        const r = await fetch(api + '/favorites_list.php');
                        const d = await r.json();
                        if (d.favorites) {
                            const ids = d.favorites.map(f => String(f.menu_item_id));
                            list.querySelectorAll('.btn-fav').forEach(b => {
                                b.classList.toggle('favorited', ids.includes(b.dataset.menuId));
                            });
                        }
                    } catch (_) { }
                })();
            }
        } catch (e) {
            console.error('Failed to load menu:', e);
        }
    }

    async function toggleFavorite(menuItemId, btn) {
        try {
            const fd = new FormData();
            fd.append('menu_item_id', menuItemId);
            fd.append('action', 'toggle');
            const res = await fetch(api + '/favorite_toggle.php', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) btn.classList.toggle('favorited', data.favorited);
        } catch (_) { }
    }

    async function loadPaymentMethods() {
        try {
            const res = await fetch(api + '/payment_methods_list.php');
            const data = await res.json();
            paymentMethods = (data.success && data.payment_methods) ? data.payment_methods : [];
            if (!paymentMethodsList) return;
            paymentMethodsList.innerHTML = '';
            paymentMethods.forEach(pm => {
                const label = document.createElement('label');
                label.innerHTML = `<input type="radio" name="payment_method_id" value="${pm.id}"> ${escapeHtml(pm.label)}`;
                paymentMethodsList.appendChild(label);
            });
            const cash = document.createElement('label');
            cash.innerHTML = '<input type="radio" name="payment_method_id" value="" checked> Cash on Delivery';
            paymentMethodsList.appendChild(cash);
        } catch (_) { }
    }

    /* ── Checkout (reads cart from DB) ── */
    async function loadCheckoutCart() {
        try {
            const res = await fetch(api + '/cart_get.php');
            const data = await res.json();
            if (!data.success || data.items.length === 0) {
                showToast('Your cart is empty. Add items first.', 'error');
                showView('restaurants');
                return;
            }
            if (checkoutTotal) checkoutTotal.textContent = data.total.toFixed(2);
            showView('checkout');
        } catch (e) {
            showToast('Failed to load cart. Try again.', 'error');
        }
    }

    $('backToRestaurants').addEventListener('click', () => { showView('restaurants'); });
    $('backToMenu').addEventListener('click', () => { showView('menu'); });
    $('checkoutForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const delivery_address = $('delivery_address').value.trim();
        const notes = $('notes').value.trim();
        const payment_method_id = document.querySelector('input[name="payment_method_id"]:checked');
        const pmId = payment_method_id && payment_method_id.value ? payment_method_id.value : null;

        // Get items from DB cart
        let cartItems = [];
        let rid = null;
        try {
            const cartRes = await fetch(api + '/cart_get.php');
            const cartData = await cartRes.json();
            if (!cartData.success || cartData.items.length === 0) {
                showToast('Your cart is empty!', 'error');
                return;
            }
            cartItems = cartData.items.map(ci => ({
                menu_item_id: ci.menu_item_id,
                quantity: ci.quantity,
                unit_price: parseFloat(ci.price)
            }));
            rid = cartData.items[0].restaurant_id;
        } catch (_) {
            showToast('Failed to read cart. Try again.', 'error');
            return;
        }

        const payload = {
            restaurant_id: rid,
            delivery_address,
            notes,
            payment_method_id: pmId ? parseInt(pmId, 10) : null,
            items: cartItems
        };

        const submitBtn = $('checkoutForm').querySelector('.submitBtn');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Placing order...';
        try {
            const res = await fetch(api + '/place_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.success) {
                showView('restaurants');
                showToast('Order #' + data.order_id + ' placed successfully!', 'success');
                submitBtn.textContent = 'Place Order';
                submitBtn.disabled = false;
                return;
            }
            showToast(data.error || 'Failed to place order', 'error');
        } catch (err) {
            showToast('Network error. Try again.', 'error');
        }
        submitBtn.textContent = 'Place Order';
        submitBtn.disabled = false;
    });

    function escapeHtml(s) {
        if (!s) return '';
        const div = document.createElement('div');
        div.textContent = s;
        return div.innerHTML;
    }

    // Initialize Order Food Flow
    loadRestaurants();
    loadPaymentMethods();
    showView('restaurants');
})();
