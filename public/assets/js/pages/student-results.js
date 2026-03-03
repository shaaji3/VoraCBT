import { ApiClient } from '../utils/ApiClient.js';

const api = new ApiClient();
const statusEl = document.getElementById('results-status');
const bodyEl = document.getElementById('results-table-body');

function setStatus(message, tone = 'secondary') {
    if (!statusEl) return;
    statusEl.className = `alert alert-${tone}`;
    statusEl.textContent = message;
}

function renderRows(items) {
    if (!bodyEl) return;
    if (items.length === 0) {
        bodyEl.innerHTML = '<tr><td colspan="5" class="text-center text-secondary py-4">No completed results yet.</td></tr>';
        return;
    }

    bodyEl.innerHTML = items.map((item) => `
        <tr>
            <td>${item.title}</td>
            <td>${item.subject}</td>
            <td>${item.score} / ${item.total_marks}</td>
            <td>${item.percent === null ? '-' : `${item.percent}%`}</td>
            <td>${item.completed_at || '-'}</td>
        </tr>
    `).join('');
}

document.addEventListener('DOMContentLoaded', async () => {
    if (!statusEl || !bodyEl) return;

    try {
        setStatus('Loading results…');
        const response = await api.get('/student/results');
        const items = response?.data?.items || [];
        renderRows(items);
        setStatus(`Loaded ${items.length} result(s).`, 'success');
    } catch (error) {
        setStatus(error.message || 'Failed to load results.', 'danger');
    }
});
