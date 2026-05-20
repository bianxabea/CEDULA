<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
requireRole(['admin', 'superadmin']);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout_action'])) {
    session_unset();
    session_destroy();
    header('Location: ' . getBaseUrl() . '/php/forms/login.php');
    exit;
}
$base = getBaseUrl();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Items - Pizza Crust Delight</title>
    <link rel="stylesheet" href="../../css/design-system.css">
    <link rel="stylesheet" href="../../css/dashboard.css">
    <link rel="stylesheet" href="../../css/serve_asset.php?file=admin.css">
    <link rel="stylesheet" href="../../css/order_food.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

</head>
<body class="dashboard-layout">
    <?php include __DIR__ . '/../includes/layout/navbar.php'; ?>

    <?php $currentPage = 'admin_menu';
include __DIR__ . '/../includes/layout/sidebar.php'; ?>

    <main class="page-content">
            <h1 class="page-title">Menu Items</h1>
            <p class="page-subtitle">Manage menu items for your restaurants.</p>

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; flex-wrap:wrap; gap:1rem;">
                <div class="form-group" style="margin-bottom:0;">
                    <select id="restFilter" class="input-field" style="padding:0.5rem 1rem; min-width:200px; font-size: 0.9rem;">
                        <option value="">All Restaurants</option>
                    </select>
                </div>
                <button type="button" id="addMenuBtn" class="submitBtn" style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem; padding: 0.5rem 1rem;">
                    <i class="fa-solid fa-plus"></i> Add Menu Item
                </button>
            </div>

            <div id="menuList" class="table-container"></div>

            <!-- Edit/Add Modal -->
            <div id="menuModal" class="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
                <div class="modal-content" style="background:var(--bg-card); padding:1.5rem; border-radius:var(--radius-lg); width:90%; max-width:450px; box-shadow:var(--shadow-lg);">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                        <h2 id="menuModalTitle" style="margin:0; font-size:1.25rem;">Add Menu Item</h2>
                        <button type="button" id="closeMenuModal" style="background:none; border:none; font-size:1.25rem; cursor:pointer; color:var(--text-muted);">&times;</button>
                    </div>
                    <form id="menuForm">
                        <input type="hidden" name="id" id="menu_id" value="">
                        <div class="form-group" style="margin-bottom: 0.75rem;">
                            <label for="menu_restaurant_id" style="font-weight:500; margin-bottom:0.25rem; display:block; font-size: 0.9rem;">Restaurant</label>
                            <select name="restaurant_id" id="menu_restaurant_id" class="input-field" required style="padding: 0.5rem;"></select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0.75rem;">
                            <label for="menu_name" style="font-weight:500; margin-bottom:0.25rem; display:block; font-size: 0.9rem;">Item Name</label>
                            <input type="text" name="name" id="menu_name" class="input-field" required placeholder="e.g. Cheese Burger" style="padding: 0.5rem;">
                        </div>
                        <div class="form-group" style="margin-bottom: 0.75rem;">
                            <label for="menu_desc" style="font-weight:500; margin-bottom:0.25rem; display:block; font-size: 0.9rem;">Description</label>
                            <textarea name="description" id="menu_desc" class="input-field" rows="2" placeholder="Brief description..." style="padding: 0.5rem;"></textarea>
                        </div>
                        <div class="form-group" style="margin-bottom: 0.75rem;">
                            <label for="menu_price" style="font-weight:500; margin-bottom:0.25rem; display:block; font-size: 0.9rem;">Price (₱)</label>
                            <input type="number" step="0.01" min="0" name="price" id="menu_price" class="input-field" required placeholder="0.00" style="padding: 0.5rem;">
                        </div>
                        <div class="form-group" style="margin-bottom: 0.75rem;">
                            <label for="menu_image" style="font-weight:500; margin-bottom:0.25rem; display:block; font-size: 0.9rem;">Image URL (Optional)</label>
                            <input type="url" name="image_path" id="menu_image" class="input-field" placeholder="https://example.com/image.jpg" style="padding: 0.5rem;">
                        </div>
                        <div class="form-group" style="display:flex; align-items:center; gap:0.5rem; margin-bottom: 1rem;">
                            <input type="checkbox" name="is_available" id="menu_available" value="1" checked style="width:auto; transform:scale(1.1);">
                            <label for="menu_available" style="cursor:pointer; font-size: 0.9rem;">Available</label>
                        </div>
                        <div style="display:flex; justify-content:flex-end; gap:0.75rem; margin-top:1.5rem;">
                            <button type="button" id="cancelMenuModal" class="btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">Cancel</button>
                            <button type="submit" class="submitBtn" style="padding: 0.5rem 1rem; font-size: 0.9rem;">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Message Modal -->
            <div id="messageModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:1100; align-items:center; justify-content:center;">
                <div style="background:white; padding:1.5rem; border-radius:12px; width:90%; max-width:350px; text-align:center; box-shadow:0 10px 25px rgba(0,0,0,0.1);">
                    <div id="msgIconContainer" style="width:50px; height:50px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 1rem;">
                        <i id="msgIcon" class="fa-solid" style="font-size:1.5rem;"></i>
                    </div>
                    <h3 id="msgTitle" style="margin-bottom:0.25rem; font-size: 1.1rem;"></h3>
                    <p id="msgBody" style="color:var(--text-muted); margin-bottom:1.5rem; font-size: 0.9rem;"></p>
                    <button onclick="document.getElementById('messageModal').style.display='none'" class="submitBtn" style="width:100%; padding: 0.5rem;">Okay</button>
                </div>
            </div>

    </main>
    <?php include __DIR__ . '/../includes/layout/footer.php'; ?>
    <script>
        const api = '<?php echo $base; ?>' + '/php/database';
        let restaurants = [];
        let currentPage = 1;

        // Load restaurants for dropdowns
        fetch(api + '/admin_restaurants.php', { credentials: 'same-origin' })
            .then(r => r.json())
            .then(data => {
                if (data.success && data.restaurants) {
                    restaurants = data.restaurants;
                    const sel = document.getElementById('restFilter');
                    const sel2 = document.getElementById('menu_restaurant_id');

                    // Clear existing options except first
                    sel.innerHTML = '<option value="">All Restaurants</option>';
                    sel2.innerHTML = ''; // Start empty

                    restaurants.forEach(r => {
                        sel.innerHTML += `<option value="${r.id}">${escapeHtml(r.name)}</option>`;
                        sel2.innerHTML += `<option value="${r.id}">${escapeHtml(r.name)}</option>`;
                    });

                    document.getElementById('restFilter').onchange = () => loadMenu(1);
                    loadMenu();
                }
            });

        function loadMenu(page = 1) {
            currentPage = page;
            const rid = document.getElementById('restFilter').value;
            let url = api + '/admin_menu.php?page=' + page;
            if (rid) url += '&restaurant_id=' + rid;

            fetch(url, { credentials: 'same-origin' })
                .then(r => r.json())
                .then(data => {
                    if (!data.success) return;

                    const totalPages = data.pagination?.total_pages || 1;

                    if (!data.menu.length) {
                        document.getElementById('menuList').innerHTML = `
                            <div class="empty-state" style="padding: 2rem; text-align: center; border: 2px dashed var(--border-light); border-radius: var(--radius-lg);">
                                <i class="fa-solid fa-utensils" style="font-size: 2rem; color: var(--border-medium); margin-bottom: 0.5rem;"></i>
                                <p style="color: var(--text-muted); margin: 0; font-size: 0.9rem;">No menu items found.</p>
                            </div>`;
                        return;
                    }

                    const getRestName = (id) => (restaurants.find(r => r.id == id) || {}).name || 'Unknown';

                    let html = '<table class="orders-table" style="width:100%; border-collapse:separate; border-spacing:0 0.25rem;"><thead><tr style="text-align:left; color:var(--text-muted); font-size:0.85rem;"> <th style="padding:0.75rem;">Image</th> <th style="padding:0.75rem;">Details</th> <th style="padding:0.75rem;">Price</th> <th style="padding:0.75rem;">Status</th> <th style="padding:0.75rem; text-align:right;">Actions</th> </tr></thead><tbody>';

                    data.menu.forEach(m => {
                        const icon = m.image_path
                            ? `<img src="${escapeHtml(m.image_path)}" style="width:40px; height:40px; object-fit:cover; border-radius:6px;">`
                            : `<div style="width:40px; height:40px; background:var(--bg-body); border-radius:6px; display:flex; align-items:center; justify-content:center; color:var(--text-muted); font-size: 0.9rem;"><i class="fa-solid fa-utensils"></i></div>`;

                        const isAvail = m.is_available == 1;
                        const statusBadge = isAvail
                            ? `<span class="status-badge no-dot status-ok" style="font-size: 0.8rem; padding: 0.2rem 0.6rem;">Available</span>`
                            : `<span class="status-badge no-dot status-trash" style="font-size: 0.8rem; padding: 0.2rem 0.6rem;">Unavailable</span>`;

                        const toggleBtnKey = isAvail ? 'Mark Unavailable' : 'Mark Available';

                        html += `<tr style="background:var(--bg-card); box-shadow:var(--shadow-sm); border-radius:6px;">
                            <td style="padding:0.75rem; border-top-left-radius:6px; border-bottom-left-radius:6px;">${icon}</td>
                            <td style="padding:0.75rem;">
                                <div style="font-weight:600; font-size:0.95rem; margin-bottom:0.1rem;">${escapeHtml(m.name)}</div>
                                <div style="font-size:0.8rem; color:var(--text-muted);"><i class="fa-solid fa-store" style="font-size:0.8em; margin-right:4px;"></i> ${escapeHtml(getRestName(m.restaurant_id))}</div>
                            </td>
                            <td style="padding:0.75rem;"><span style="font-weight:700; color:var(--primary-color);">₱${parseFloat(m.price).toFixed(2)}</span></td>
                            <td style="padding:0.75rem;">${statusBadge}</td>
                            <td style="padding:0.75rem; text-align:right; border-top-right-radius:6px; border-bottom-right-radius:6px;">
                                <button class="btn-secondary" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;" onclick='openEditModal(${JSON.stringify(m)})'>
                                    Edit
                                </button>
                            </td>
                        </tr>`;
                    });
                    html += '</tbody></table>';

                     if (totalPages > 1) {
                         html += `<div class="pagination-controls" style="display:flex; justify-content:space-between; align-items:center; margin-top:1rem;">
                            <span class="pagination-info" style="color:var(--text-muted); font-size: 0.85rem;">Page ${currentPage} of ${totalPages}</span>
                            <div style="display:flex; gap:0.5rem;">
                                <button class="btn-secondary" style="padding: 0.3rem 0.8rem; font-size: 0.85rem;" onclick="loadMenu(currentPage-1)" ${currentPage<=1?'disabled':''}>Previous</button>
                                <button class="btn-secondary" style="padding: 0.3rem 0.8rem; font-size: 0.85rem;" onclick="loadMenu(currentPage+1)" ${currentPage>=totalPages?'disabled':''}>Next</button>
                            </div>
                        </div>`;
                    }

                    document.getElementById('menuList').innerHTML = html;
                });
        }

        function toggleAvail(id, status) {
            const fd = new FormData();
            fd.append('action', 'toggle_available');
            fd.append('id', id);
            fd.append('status', status);
            fetch(api + '/admin_menu.php', { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        showMessageModal('success', 'Status Updated', 'Menu item status has been updated.');
                        loadMenu(currentPage);
                    } else {
                        showMessageModal('error', 'Update Failed', d.error || 'Could not update status.');
                    }
                });
        }

        function openEditModal(m) {
            document.getElementById('menuModalTitle').textContent = 'Edit Menu Item';
            document.getElementById('menu_id').value = m.id;
            document.getElementById('menu_restaurant_id').value = m.restaurant_id;
            document.getElementById('menu_name').value = m.name || '';
            document.getElementById('menu_desc').value = m.description || '';
            document.getElementById('menu_price').value = m.price || '';
            document.getElementById('menu_image').value = m.image_path || '';
            document.getElementById('menu_available').checked = m.is_available == 1;
            document.getElementById('menuModal').style.display = 'flex';
        }

        document.getElementById('addMenuBtn').onclick = () => {
            document.getElementById('menuModalTitle').textContent = 'Add Menu Item';
            document.getElementById('menuForm').reset();
            document.getElementById('menu_id').value = '';
            document.getElementById('menu_available').checked = true;
            document.getElementById('menuModal').style.display = 'flex';
        };

        const closeEls = [document.getElementById('closeMenuModal'), document.getElementById('cancelMenuModal')];
        closeEls.forEach(el => el.onclick = () => document.getElementById('menuModal').style.display = 'none');

        document.getElementById('menuForm').onsubmit = (e) => {
            e.preventDefault();
            const fd = new FormData(e.target);
            fd.append('action', 'save');
            if (!document.getElementById('menu_available').checked) {
                fd.append('is_available', 0);
            }

            fetch(api + '/admin_menu.php', { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(r => r.json())
                .then(d => {
                    document.getElementById('menuModal').style.display = 'none';
                    if (d.success) {
                        showMessageModal('success', 'Success', 'Menu item saved successfully.');
                        loadMenu(currentPage);
                    } else {
                        showMessageModal('error', 'Error', d.error || 'Could not save menu item.');
                    }
                })
                .catch(() => {
                     document.getElementById('menuModal').style.display = 'none';
                     showMessageModal('error', 'Error', 'Network error occurred.');
                });
        };

        function showMessageModal(type, title, message) {
            const modal = document.getElementById('messageModal');
            const iconContainer = document.getElementById('msgIconContainer');
            const icon = document.getElementById('msgIcon');
            const titleEl = document.getElementById('msgTitle');
            const bodyEl = document.getElementById('msgBody');

            titleEl.textContent = title;
            bodyEl.textContent = message;

            if (type === 'success') {
                iconContainer.style.background = '#dcfce7';
                icon.className = 'fa-solid fa-check';
                icon.style.color = '#16a34a';
                titleEl.style.color = '#16a34a';
            } else {
                iconContainer.style.background = '#fee2e2';
                icon.className = 'fa-solid fa-xmark';
                icon.style.color = '#dc2626';
                titleEl.style.color = '#dc2626';
            }

            modal.style.display = 'flex';
        }

        function escapeHtml(s) { const d = document.createElement('div'); d.textContent = s || ''; return d.innerHTML; }
    </script>
</body>
</html>
