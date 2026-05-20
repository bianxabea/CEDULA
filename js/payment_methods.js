(function () {
    const api = (window.BASE_URL || '') + '/php/database';
    const list = document.getElementById('paymentMethodsList');
    const form = document.getElementById('addPaymentForm');

    function escapeHtml(s) {
        if (!s) return '';
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    function load() {
        fetch(api + '/payment_methods_list.php', { credentials: 'same-origin' })
            .then(r => r.json())
            .then(data => {
                if (!data.success || !data.payment_methods || !data.payment_methods.length) {
                    list.innerHTML = '<p class="pm-empty muted">No GCash numbers saved. Add one below to pay with GCash at checkout.</p>';
                    return;
                }
                const methods = data.payment_methods.filter(function (pm) { return pm.type === 'gcash'; });
                if (!methods.length) {
                    list.innerHTML = '<p class="pm-empty muted">No GCash numbers saved. Add one below.</p>';
                    return;
                }
                list.innerHTML = methods.map(function (pm) {
                    const label = escapeHtml(pm.label || '');
                    const details = pm.details ? escapeHtml(pm.details) : '';
                    const defaultBadge = pm.is_default ? '<span class="pm-badge-default">Default</span>' : '';
                    return '<div class="pm-card">' +
                        '<div class="pm-card-icon"><i class="fa-solid fa-mobile-screen-button" aria-hidden="true"></i></div>' +
                        '<div class="pm-card-body">' +
                        '<div class="pm-card-label">' + label + defaultBadge + '</div>' +
                        (details ? '<div class="pm-card-details">' + details + '</div>' : '') +
                        '<span class="pm-card-type">GCash</span>' +
                        '</div>' +
                        '<button type="button" class="btn-remove pm-card-remove" data-id="' + escapeHtml(String(pm.id)) + '" title="Remove">Remove</button>' +
                        '</div>';
                }).join('');
                list.querySelectorAll('.pm-card-remove').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        const id = btn.getAttribute('data-id');
                        const fd = new FormData();
                        fd.append('id', id);
                        fetch(api + '/payment_method_delete.php', { method: 'POST', body: fd, credentials: 'same-origin' })
                            .then(r => r.json())
                            .then(d => { if (d.success) load(); });
                    });
                });
            })
            .catch(function () { list.innerHTML = '<p class="pm-empty muted">Could not load payment methods.</p>'; });
    }

    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const fd = new FormData();
            fd.append('type', 'gcash');
            fd.append('label', (document.getElementById('pm_label').value || '').trim());
            fd.append('details', (document.getElementById('pm_details').value || '').trim());
            fd.append('is_default', form.querySelector('[name=is_default]').checked ? '1' : '0');
            if (!fd.get('label')) {
                alert('Please enter your GCash mobile number.');
                return;
            }
            fetch(api + '/payment_method_save.php', { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(r => r.json())
                .then(function (data) {
                    if (data.success) {
                        form.reset();
                        load();
                    } else {
                        alert(data.error || 'Failed to save.');
                    }
                })
                .catch(function () { alert('Network error. Try again.'); });
        });
    }

    load();
})();
