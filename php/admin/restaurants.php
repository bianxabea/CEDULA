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
    <title>Restaurants - Pizza Crust Delight</title>
    <link rel="stylesheet" href="../../css/design-system.css">
    <link rel="stylesheet" href="../../css/dashboard.css">
    <link rel="stylesheet" href="../../css/serve_asset.php?file=admin.css">
    <link rel="stylesheet" href="../../css/order_food.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

</head>
<body class="dashboard-layout">
    <?php include __DIR__ . '/../includes/layout/navbar.php'; ?>

    <?php $currentPage = 'admin_restaurants';
include __DIR__ . '/../includes/layout/sidebar.php'; ?>

    <main class="page-content">
            <h1 class="page-title">Manage Stores</h1>
            <p class="page-subtitle">Add, edit, or deactivate restaurants.</p>

            <button type="button" id="addRestaurantBtn" class="submitBtn" style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem; padding: 0.5rem 1rem;">
                <i class="fa-solid fa-plus"></i> Add Restaurant
            </button>

            <div id="restaurantsList" class="table-container"></div>

            <!-- Edit/Add Modal -->
            <div id="restaurantModal" class="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
                <div class="modal-content" style="background:var(--bg-card); padding:1.5rem; border-radius:var(--radius-lg); width:90%; max-width:450px; box-shadow:var(--shadow-lg);">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                        <h2 id="modalTitle" style="margin:0; font-size:1.25rem;">Add Restaurant</h2>
                        <button type="button" id="closeModal" style="background:none; border:none; font-size:1.25rem; cursor:pointer; color:var(--text-muted);">&times;</button>
                    </div>
                    <form id="restaurantForm">
                        <input type="hidden" name="id" id="rest_id" value="">
                        <div class="form-group" style="margin-bottom: 0.75rem;">
                            <label for="rest_name" style="font-weight:500; margin-bottom:0.25rem; display:block; font-size: 0.9rem;">Restaurant Name</label>
                            <input type="text" name="name" id="rest_name" class="input-field" required placeholder="e.g. Burger King" style="padding: 0.5rem;">
                        </div>
                        <div class="form-group" style="margin-bottom: 0.75rem;">
                            <label for="rest_desc" style="font-weight:500; margin-bottom:0.25rem; display:block; font-size: 0.9rem;">Description</label>
                            <textarea name="description" id="rest_desc" class="input-field" rows="2" placeholder="Brief description..." style="padding: 0.5rem;"></textarea>
                        </div>
                        <div class="form-group" style="margin-bottom: 0.75rem;">
                            <label for="rest_address" style="font-weight:500; margin-bottom:0.25rem; display:block; font-size: 0.9rem;">Address</label>
                            <input type="text" name="address" id="rest_address" class="input-field" placeholder="Full address" style="padding: 0.5rem;">
                        </div>
                        <div class="form-group" style="margin-bottom: 0.75rem;">
                            <label for="rest_image" style="font-weight:500; margin-bottom:0.25rem; display:block; font-size: 0.9rem;">Image URL (Optional)</label>
                            <input type="url" name="image_path" id="rest_image" class="input-field" placeholder="https://example.com/image.jpg" style="padding: 0.5rem;">
                        </div>
                        <div class="form-group" style="display:flex; align-items:center; gap:0.5rem; margin-bottom: 1rem;">
                            <input type="checkbox" name="is_active" id="rest_active" value="1" checked style="width:auto; transform:scale(1.1);">
                            <label for="rest_active" style="cursor:pointer; font-size: 0.9rem;">Active / Open for Orders</label>
                        </div>
                        <div style="display:flex; justify-content:flex-end; gap:0.75rem; margin-top:1.5rem;">
                            <button type="button" id="cancelModal" class="btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">Cancel</button>
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
        let currentPage = 1;
        let totalPages = 1;

        function load(page = 1) {
            currentPage = page;
            fetch(api + '/admin_restaurants.php?page=' + page, { credentials: 'same-origin' })
                .then(r => r.json())
                .then(data => {
                    if (!data.success) return;

                    totalPages = data.pagination?.total_pages || 1;

                    if (!data.restaurants.length) {
                        document.getElementById('restaurantsList').innerHTML = `
                            <div class="empty-state" style="padding: 2rem; text-align: center; border: 2px dashed var(--border-light); border-radius: var(--radius-lg);">
                                <i class="fa-solid fa-store" style="font-size: 2rem; color: var(--border-medium); margin-bottom: 0.5rem;"></i>
                                <p style="color: var(--text-muted); margin: 0; font-size: 0.9rem;">No restaurants found.</p>
                            </div>`;
                        return;
                    }

                    let html = '<table class="orders-table" style="width:100%; border-collapse:separate; border-spacing:0 0.25rem;"><thead><tr style="text-align:left; color:var(--text-muted); font-size:0.85rem;"> <th style="padding:0.75rem;">Image</th> <th style="padding:0.75rem;">Details</th> <th style="padding:0.75rem;">Address</th> <th style="padding:0.75rem;">Status</th> <th style="padding:0.75rem; text-align:right;">Actions</th> </tr></thead><tbody>';

                    data.restaurants.forEach(r => {
                        const icon = r.image_path
                            ? `<img src="${escapeHtml(r.image_path)}" style="width:40px; height:40px; object-fit:cover; border-radius:6px;">`
                            : `<div style="width:40px; height:40px; background:var(--bg-body); border-radius:6px; display:flex; align-items:center; justify-content:center; color:var(--text-muted); font-size: 0.9rem;"><i class="fa-solid fa-store"></i></div>`;

                        const isActive = r.is_active == 1;
                        const statusBadge = isActive
                            ? `<span class="status-badge no-dot status-ok" style="font-size: 0.8rem; padding: 0.2rem 0.6rem;">Active</span>`
                            : `<span class="status-badge no-dot status-trash" style="font-size: 0.8rem; padding: 0.2rem 0.6rem;">Inactive</span>`;

                        const toggleBtnKey = isActive ? 'Deactivate' : 'Activate';
                        const toggleIcon = isActive ? 'fa-toggle-on' : 'fa-toggle-off';
                        const toggleColor = isActive ? 'var(--primary-color)' : 'var(--text-muted)';

                        html += `<tr style="background:var(--bg-card); box-shadow:var(--shadow-sm); border-radius:6px;">
                            <td style="padding:0.75rem; border-top-left-radius:6px; border-bottom-left-radius:6px;">${icon}</td>
                            <td style="padding:0.75rem;">
                                <div style="font-weight:600; font-size:0.95rem; margin-bottom:0.1rem;">${escapeHtml(r.name)}</div>
                                <div style="font-size:0.8rem; color:var(--text-muted);">${escapeHtml(r.description || 'No description')}</div>
                            </td>
                            <td style="padding:0.75rem; color:var(--text-muted); font-size:0.85rem;">${escapeHtml(r.address || '-')}</td>
                            <td style="padding:0.75rem;">${statusBadge}</td>
                            <td style="padding:0.75rem; text-align:right; border-top-right-radius:6px; border-bottom-right-radius:6px;">
                                <a href="javascript:void(0)" style="color:var(--primary-color); font-weight:600; font-size:0.85rem; text-decoration:none; cursor:pointer;" onclick='openEditModal(${JSON.stringify(r)})'>Edit</a>
                            </td>
                        </tr>`;
                    });
                    html += '</tbody></table>';

                    if (totalPages > 1) {
                         html += `<div class="pagination-controls" style="display:flex; justify-content:space-between; align-items:center; margin-top:1rem;">
                            <span class="pagination-info" style="color:var(--text-muted); font-size: 0.85rem;">Page ${currentPage} of ${totalPages}</span>
                            <div style="display:flex; gap:0.5rem;">
                                <button class="btn-secondary" style="padding: 0.3rem 0.8rem; font-size: 0.85rem;" onclick="load(currentPage-1)" ${currentPage<=1?'disabled':''}>Previous</button>
                                <button class="btn-secondary" style="padding: 0.3rem 0.8rem; font-size: 0.85rem;" onclick="load(currentPage+1)" ${currentPage>=totalPages?'disabled':''}>Next</button>
                            </div>
                        </div>`;
                    }

                    document.getElementById('restaurantsList').innerHTML = html;
                });
        }

        function toggleRest(id, status) {
            const fd = new FormData();
            fd.append('action', 'toggle_active');
            fd.append('id', id);
            fd.append('status', status);
            fetch(api + '/admin_restaurants.php', { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        showMessageModal('success', 'Status Updated', 'Restaurant status has been updated.');
                        load(currentPage);
                    } else {
                        showMessageModal('error', 'Update Failed', d.error || 'Could not update status.');
                    }
                });
        }

        function openEditModal(r) {
            document.getElementById('modalTitle').textContent = 'Edit Restaurant';
            document.getElementById('rest_id').value = r.id;
            document.getElementById('rest_name').value = r.name || '';
            document.getElementById('rest_desc').value = r.description || '';
            document.getElementById('rest_address').value = r.address || '';
            document.getElementById('rest_image').value = r.image_path || '';
            document.getElementById('rest_active').checked = r.is_active == 1;
            document.getElementById('restaurantModal').style.display = 'flex';
        }

        document.getElementById('addRestaurantBtn').onclick = () => {
            document.getElementById('modalTitle').textContent = 'Add Restaurant';
            document.getElementById('restaurantForm').reset();
            document.getElementById('rest_id').value = '';
            document.getElementById('rest_active').checked = true;
            document.getElementById('restaurantModal').style.display = 'flex';
        };

        const closeEls = [document.getElementById('closeModal'), document.getElementById('cancelModal')];
        closeEls.forEach(el => el.onclick = () => document.getElementById('restaurantModal').style.display = 'none');

        document.getElementById('restaurantForm').onsubmit = (e) => {
            e.preventDefault();
            const fd = new FormData(e.target);
            fd.append('action', 'save');
            // Checkbox handling: if unchecked, it's not in FormData, so let's handle it manually or rely on PHP checking isset
            // But PHP 'save' likely expects 'is_active'.
            if (!document.getElementById('rest_active').checked) {
                fd.append('is_active', 0);
            }

            fetch(api + '/admin_restaurants.php', { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(r => r.json())
                .then(d => {
                    document.getElementById('restaurantModal').style.display = 'none';
                    if (d.success) {
                        showMessageModal('success', 'Success', 'Restaurant saved successfully.');
                        load(currentPage);
                    } else {
                        showMessageModal('error', 'Error', d.error || 'Could not save restaurant.');
                    }
                })
                .catch(() => {
                     document.getElementById('restaurantModal').style.display = 'none';
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

        load();
    </script>
</body>
</html>
