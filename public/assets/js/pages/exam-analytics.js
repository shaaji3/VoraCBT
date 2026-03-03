import { ApiClient } from '../utils/ApiClient.js';

const api = new ApiClient();

const statusEl = document.getElementById('analytics-status-banner');
const tableBody = document.getElementById('analytics-table-body');
const applyBtn = document.getElementById('analytics-apply');
const refreshBtn = document.getElementById('analytics-refresh');

const fromInput = document.getElementById('analytics-from');
const toInput = document.getElementById('analytics-to');
const statusInput = document.getElementById('analytics-status');

const totalSessions = document.getElementById('analytics-total-sessions');
const averageScore = document.getElementById('analytics-average-score');
const passRate = document.getElementById('analytics-pass-rate');

function setStatus(message, tone = 'secondary') {
    statusEl.className = `alert alert-${tone}`;
    statusEl.textContent = message;
}

function render(payload) {
    const rows = payload?.sessions_by_status || [];
    const filteredRows = statusInput.value ? rows.filter((r) => r.status === statusInput.value) : rows;
    const total = filteredRows.reduce((sum, row) => sum + Number(row.count || 0), 0);

    totalSessions.textContent = String(total);
    averageScore.textContent = payload?.average_score != null ? `${payload.average_score}%` : '-';
    passRate.textContent = payload?.pass_rate != null ? `${payload.pass_rate}%` : '-';

    if (filteredRows.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="3" class="text-center text-secondary py-4">No analytics records for selected filters.</td></tr>';
        return;
    }

    tableBody.innerHTML = filteredRows.map((row) => {
        const count = Number(row.count || 0);
        const share = total > 0 ? `${((count / total) * 100).toFixed(1)}%` : '0%';
        return `<tr><td>${row.status}</td><td>${count}</td><td>${share}</td></tr>`;
    }).join('');
}

async function load() {
    setStatus('Loading analytics…');
    const params = new URLSearchParams();
    if (fromInput.value) params.set('from', fromInput.value);
    if (toInput.value) params.set('to', toInput.value);

    const response = await api.get(`/admin/analytics/summary?${params.toString()}`);
    render(response?.data || {});
    setStatus('Analytics loaded.', 'success');
}

document.addEventListener('DOMContentLoaded', async () => {
    if (!statusEl || !tableBody) return;

    applyBtn?.addEventListener('click', () => load().catch((e) => setStatus(e.message || 'Failed to load analytics.', 'danger')));
    refreshBtn?.addEventListener('click', () => load().catch((e) => setStatus(e.message || 'Failed to refresh analytics.', 'danger')));

    try {
        await load();
    } catch (error) {
        setStatus(error.message || 'Failed to load analytics.', 'danger');
    }
});
