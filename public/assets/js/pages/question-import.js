const statusEl = document.getElementById('question-import-status');
const previewBtn = document.getElementById('question-import-preview');
const commitBtn = document.getElementById('question-import-commit');

function setStatus(message, tone = 'secondary') {
    if (!statusEl) return;
    statusEl.className = `alert alert-${tone}`;
    statusEl.textContent = message;
}

async function send(action) {
    const fileInput = document.getElementById('question-import-file');
    const file = fileInput?.files?.[0];
    if (!file) throw new Error('Please select a CSV or Excel (.xlsx) file.');

    const form = new FormData();
    form.append('file', file);

    const response = await fetch(`/api/teacher/questions/import/${action}`, {
        method: 'POST',
        body: form,
        credentials: 'same-origin',
    });

    const payload = await response.json().catch(() => ({}));
    if (!response.ok) {
        throw new Error(payload?.error?.message || payload?.message || `Import ${action} failed.`);
    }

    return payload;
}

previewBtn?.addEventListener('click', async () => {
    try {
        setStatus('Previewing import…', 'info');
        const payload = await send('preview');
        const summary = payload?.data || {};
        setStatus(`Preview: total=${summary.total_rows ?? 0}, valid=${summary.valid_rows ?? 0}, invalid=${summary.invalid_rows ?? 0}`, 'success');
    } catch (error) {
        setStatus(error.message || 'Preview failed.', 'danger');
    }
});

commitBtn?.addEventListener('click', async () => {
    try {
        setStatus('Committing import…', 'info');
        const payload = await send('commit');
        const summary = payload?.data || {};
        setStatus(`Import complete: created=${summary.created ?? 0}, failed=${summary.failed ?? 0}`, 'success');
    } catch (error) {
        setStatus(error.message || 'Commit failed.', 'danger');
    }
});
