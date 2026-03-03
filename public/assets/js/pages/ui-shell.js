function activateNavLinks() {
    const path = window.location.pathname;
    const links = document.querySelectorAll('[data-route-prefix]');

    links.forEach((link) => {
        const prefix = link.getAttribute('data-route-prefix') || '';
        const isActive = path === prefix || (prefix !== '/' && path.startsWith(prefix));

        if (isActive) {
            link.classList.add('active', 'text-primary', 'bg-primary-soft');
            link.classList.remove('text-secondary');
        } else {
            link.classList.remove('active', 'text-primary', 'bg-primary-soft');
            if (link.classList.contains('nav-link') || link.classList.contains('btn-link')) {
                link.classList.add('text-secondary');
            }
        }
    });
}

function showToast(message) {
    const toastEl = document.getElementById('ui-shell-toast');
    if (!toastEl) {
        return;
    }

    toastEl.querySelector('.toast-body').textContent = message;
    bootstrap.Toast.getOrCreateInstance(toastEl).show();
}

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function inferNotificationRole(panel) {
    const explicit = panel.getAttribute('data-ui-shell-notification-role');
    if (explicit) {
        return explicit;
    }

    const path = window.location.pathname;
    if (path.startsWith('/student')) return 'student';
    if (path.startsWith('/admin') || path.startsWith('/teacher')) return 'staff';
    return 'anonymous';
}

async function fetchNotificationItems(role) {
    try {
        if (role === 'student') {
            const response = await fetch('/api/student/dashboard/overview', { credentials: 'same-origin' });
            if (!response.ok) throw new Error('failed');
            const payload = await response.json();
            const data = payload?.data || {};
            const upcoming = Array.isArray(data.upcoming) ? data.upcoming.slice(0, 3) : [];
            const inProgress = Array.isArray(data.in_progress) ? data.in_progress.slice(0, 2) : [];

            return [
                ...inProgress.map((item) => ({ text: `In progress: ${item.title || 'Exam'} (${item.status || 'active'})` })),
                ...upcoming.map((item) => ({ text: `Upcoming: ${item.title || 'Exam'} (${item.subject || 'General'})` })),
            ];
        }

        const [logsResp, gradingResp] = await Promise.all([
            fetch('/api/admin/logs?limit=3', { credentials: 'same-origin' }),
            fetch('/api/teacher/grading/pending?limit=3', { credentials: 'same-origin' }),
        ]);

        const items = [];

        if (logsResp.ok) {
            const payload = await logsResp.json();
            const rows = Array.isArray(payload?.data?.data) ? payload.data.data : [];
            rows.forEach((row) => {
                items.push({ text: `Log: ${row.summary || row.event_name || 'Recent system event'}` });
            });
        }

        if (gradingResp.ok) {
            const payload = await gradingResp.json();
            const rows = Array.isArray(payload?.data?.items) ? payload.data.items : [];
            rows.forEach((row) => {
                items.push({ text: `Manual grading pending: Session ${row.session_id || '-'} (${row.exam_title || 'Exam'})` });
            });
        }

        return items.slice(0, 6);
    } catch {
        return [];
    }
}

function renderNotifications(panel, items) {
    const listEl = panel.querySelector('[data-ui-notification-list]');
    const countEl = panel.querySelector('[data-ui-notification-count]');
    const dot = panel.querySelector('[data-ui-notification-dot]');

    if (!listEl) {
        return;
    }

    if (items.length === 0) {
        listEl.innerHTML = '<div class="list-group-item py-2 small text-secondary">No new notifications.</div>';
        if (countEl) countEl.textContent = '0';
        if (dot) dot.classList.add('d-none');
        return;
    }

    listEl.innerHTML = items.map((item) => `<div class="list-group-item py-2 small">${escapeHtml(item.text || 'Notification')}</div>`).join('');
    if (countEl) countEl.textContent = String(items.length);
    if (dot) dot.classList.remove('d-none');
}

function wireNotificationPanels() {
    document.querySelectorAll('[data-ui-shell-notifications]').forEach(async (panel) => {
        const markReadBtn = panel.querySelector('[data-ui-mark-read]');
        const dot = panel.querySelector('[data-ui-notification-dot]');
        const role = inferNotificationRole(panel);

        const items = await fetchNotificationItems(role);
        renderNotifications(panel, items);

        markReadBtn?.addEventListener('click', (event) => {
            event.preventDefault();
            const countEl = panel.querySelector('[data-ui-notification-count]');
            const listEl = panel.querySelector('[data-ui-notification-list]');

            if (dot) {
                dot.classList.add('d-none');
            }
            if (countEl) {
                countEl.textContent = '0';
            }
            if (listEl) {
                listEl.innerHTML = '<div class="list-group-item py-2 small text-secondary">No new notifications.</div>';
            }

            showToast('Notifications marked as read.');
        });
    });
}

function wireInlineActions() {
    const actionMessages = {
    };

    document.querySelectorAll('[data-inline-action]').forEach((link) => {
        link.addEventListener('click', (event) => {
            event.preventDefault();
            const action = link.getAttribute('data-inline-action') || '';
showToast(actionMessages[action] || 'Opening this page...');
        });
    });
}

function wireLogoutConfirmation() {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    document.querySelectorAll('[data-ui-shell-logout]').forEach((link) => {
        link.addEventListener('click', async (event) => {
            event.preventDefault();
            const confirmed = window.confirm('Are you sure you want to log out?');
            if (!confirmed) {
                return;
            }

            try {
                const response = await fetch('/auth/logout', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify({}),
                });

                if (!response.ok) {
                    throw new Error('Logout failed');
                }

                window.location.href = '/login';
            } catch (error) {
                showToast('Unable to log out right now. Please retry.');
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    activateNavLinks();
    wireNotificationPanels();
    wireLogoutConfirmation();
    wireInlineActions();
});
