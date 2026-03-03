import { ApiClient } from '../utils/ApiClient.js';

const api = new ApiClient();

const statusEl = document.getElementById('roles-status');
const bodyEl = document.getElementById('roles-permissions-body');
const saveBtn = document.getElementById('save-roles-btn');

let roles = [];
let permissions = [];
let grants = {};

function setStatus(message, tone = 'secondary') {
    if (!statusEl) return;
    statusEl.className = `alert alert-${tone}`;
    statusEl.textContent = message;
}

function render() {
    if (!bodyEl) return;
    if (permissions.length === 0 || roles.length === 0) {
        bodyEl.innerHTML = '<tr><td colspan="2" class="text-center text-secondary py-4">No roles/permissions configured.</td></tr>';
        return;
    }

    bodyEl.innerHTML = permissions.map((permission) => {
        const controls = roles.map((role) => {
            const checked = Array.isArray(grants[role.id]) && grants[role.id].includes(permission.id) ? 'checked' : '';
            return `<label class="form-check form-check-inline me-3"><input class="form-check-input grant-input" data-role-id="${role.id}" data-permission-id="${permission.id}" type="checkbox" ${checked}><span class="form-check-label">${role.name}</span></label>`;
        }).join('');

        return `<tr><td><strong>${permission.name}</strong><div class="small text-secondary">${permission.slug}</div></td><td>${controls}</td></tr>`;
    }).join('');

    document.querySelectorAll('.grant-input').forEach((input) => {
        input.addEventListener('change', (event) => {
            const roleId = event.target.dataset.roleId;
            const permissionId = event.target.dataset.permissionId;
            grants[roleId] = grants[roleId] || [];
            if (event.target.checked && !grants[roleId].includes(permissionId)) {
                grants[roleId].push(permissionId);
            }
            if (!event.target.checked) {
                grants[roleId] = grants[roleId].filter((id) => id !== permissionId);
            }
        });
    });
}

async function loadData() {
    setStatus('Loading roles and permissions…', 'secondary');
    const response = await api.get('/admin/roles-permissions');
    roles = response?.data?.roles || [];
    permissions = response?.data?.permissions || [];
    grants = response?.data?.grants || {};
    render();
    setStatus('Roles and permissions loaded.', 'success');
}

async function saveData() {
    saveBtn.disabled = true;
    setStatus('Saving role permissions…', 'info');
    try {
        await api.post('/admin/roles-permissions', { grants });
        setStatus('Role permissions saved.', 'success');
    } catch (error) {
        setStatus(error.message || 'Failed to save role permissions.', 'danger');
    } finally {
        saveBtn.disabled = false;
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    if (!statusEl || !bodyEl || !saveBtn) return;
    saveBtn.addEventListener('click', saveData);
    try {
        await loadData();
    } catch (error) {
        setStatus(error.message || 'Failed to load roles and permissions.', 'danger');
    }
});
