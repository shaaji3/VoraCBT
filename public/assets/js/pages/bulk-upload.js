const statusEl = document.getElementById('import-status');
const form = document.getElementById('import-form');
const previewBtn = document.getElementById('preview-import-btn');
const commitBtn = document.getElementById('commit-import-btn');

function setStatus(message, tone = 'secondary') {
    if (!statusEl) return;
    statusEl.className = `alert alert-${tone}`;
    statusEl.textContent = message;
}

async function sendMultipart(endpoint) {
    const fileInput = document.getElementById('import-file');
    const typeSelect = document.getElementById('import-type');
    const file = fileInput?.files?.[0] || null;

    if (!file) {
        throw new Error('Please select a CSV file first.');
    }

    const data = new FormData();
    data.append('file', file);
    data.append('type', typeSelect.value);

    const token = window.localStorage.getItem('auth_token');
    const headers = token ? { Authorization: `Bearer ${token}` } : {};

    const response = await fetch(`/api${endpoint}`, {
        method: 'POST',
        body: data,
        headers,
    });

    const payload = await response.json();
    if (!response.ok) {
        throw new Error(payload?.data?.message || payload?.message || 'Request failed.');
    }

    return payload;
}

document.addEventListener('DOMContentLoaded', () => {
    if (!form) return;

    previewBtn?.addEventListener('click', async () => {
        try {
            setStatus('Uploading for preview…', 'info');
            const payload = await sendMultipart('/admin/students/import/preview');
            const summary = payload?.data?.summary || payload?.data || {};
            setStatus(`Preview complete. Rows detected: ${summary.total_rows ?? 'n/a'}.`, 'success');
        } catch (error) {
            setStatus(error.message, 'danger');
        }
    });

    commitBtn?.addEventListener('click', async () => {
        try {
            setStatus('Committing import…', 'info');
            const payload = await sendMultipart('/admin/students/import/commit');
            const summary = payload?.data?.summary || payload?.data || {};
            setStatus(`Commit complete. Imported: ${summary.imported ?? summary.created ?? 'n/a'}.`, 'success');
        } catch (error) {
            setStatus(error.message, 'danger');
        }
    });
});
