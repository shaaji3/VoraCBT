import { ApiClient } from '../utils/ApiClient.js';

class AdminDashboard {
    constructor() {
        this.api = new ApiClient();
        this.tableBody = document.getElementById('session-table-body');
        this.status = document.getElementById('dashboard-status');
        this.refreshButton = document.getElementById('btn-refresh-sessions');
        this.searchInput = document.getElementById('dashboard-filter-user');
        this.sourceFilter = document.getElementById('dashboard-filter-source');
        this.examFilter = document.getElementById('dashboard-filter-exam');
        this.logs = [];
        this.modal = null;
        this.modalBody = document.getElementById('session-log-modal-body');
        this.modalTitle = document.getElementById('session-log-modal-title');
    }

    async init() {
        if (!this.tableBody) {
            return;
        }

        const modalEl = document.getElementById('session-log-modal');
        if (modalEl) {
            this.modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        }

        this.refreshButton?.addEventListener('click', () => this.loadSessions());
        this.searchInput?.addEventListener('input', () => this.renderRows(this.applyFilters()));
        this.sourceFilter?.addEventListener('change', () => this.renderRows(this.applyFilters()));
        this.examFilter?.addEventListener('change', () => this.renderRows(this.applyFilters()));

        await this.loadSessions();
    }

    async loadSessions() {
        try {
            this.setStatus('Loading live session feed…', 'info');
            const payload = await this.api.get('/admin/logs?limit=100');
            this.logs = Array.isArray(payload?.data) ? payload.data : [];
            this.populateFilterOptions();
            const filtered = this.applyFilters();
            this.renderRows(filtered);

            if (filtered.length === 0) {
                this.setStatus('No recent proctoring/admin events found for the selected filters.', 'secondary');
            } else {
                this.setStatus(`Loaded ${filtered.length} event(s).`, 'success');
            }
        } catch (error) {
            this.logs = [];
            this.renderRows([]);
            this.setStatus('Unable to load session feed. Verify admin login/token and try again.', 'danger');
        }
    }

    populateFilterOptions() {
        if (this.sourceFilter) {
            const current = this.sourceFilter.value;
            const sources = [...new Set(this.logs.map((row) => String(row.event_source || '').trim()).filter(Boolean))].sort();
            this.sourceFilter.innerHTML = '<option value="">All Sources</option>' + sources.map((value) => `<option value="${this.escape(value)}">${this.escape(value)}</option>`).join('');
            this.sourceFilter.value = sources.includes(current) ? current : '';
        }

        if (this.examFilter) {
            const current = this.examFilter.value;
            const exams = [...new Set(this.logs.map((row) => String(row.exam_template_id || '').trim()).filter(Boolean))].sort();
            this.examFilter.innerHTML = '<option value="">All Exams</option>' + exams.map((value) => `<option value="${this.escape(value)}">${this.escape(value)}</option>`).join('');
            this.examFilter.value = exams.includes(current) ? current : '';
        }
    }

    applyFilters() {
        const q = String(this.searchInput?.value || '').trim().toLowerCase();
        const source = String(this.sourceFilter?.value || '').trim();
        const exam = String(this.examFilter?.value || '').trim();

        return this.logs.filter((row) => {
            const user = String(row.user_id || '').toLowerCase();
            const summary = String(row.summary || '').toLowerCase();
            const eventName = String(row.event_name || '').toLowerCase();
            const matchesQuery = !q || user.includes(q) || summary.includes(q) || eventName.includes(q);
            const matchesSource = !source || String(row.event_source || '') === source;
            const matchesExam = !exam || String(row.exam_template_id || '') === exam;
            return matchesQuery && matchesSource && matchesExam;
        });
    }

    renderRows(rows) {
        if (rows.length === 0) {
            this.tableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-secondary py-4">No live session events available.</td>
                </tr>
            `;
            return;
        }

        this.tableBody.innerHTML = rows.map((row) => {
            const userId = this.escape(row.user_id ?? 'unknown');
            const source = this.escape(row.event_source ?? 'system');
            const exam = this.escape(row.exam_template_id ?? '-');
            const createdAt = this.escape(row.occurred_at ?? row.created_at ?? '-');
            const logId = this.escape(row.id ?? '');
            return `
                <tr>
                    <td>${userId}</td>
                    <td><span class="badge text-bg-secondary">${source}</span></td>
                    <td>${exam}</td>
                    <td>${createdAt}</td>
                    <td><button class="btn btn-sm btn-outline-primary" type="button" data-action="session-log" data-log-id="${logId}">Session Log</button></td>
                </tr>
            `;
        }).join('');
        this.wireRowActions(rows);
    }

    wireRowActions(rows) {
        this.tableBody.querySelectorAll('[data-action="session-log"]').forEach((button) => {
            button.addEventListener('click', async () => {
                const logId = button.getAttribute('data-log-id');
                const selected = rows.find((item) => String(item.id) === String(logId));
                if (!selected) return;

                const userId = selected.user_id ? String(selected.user_id) : '';
                this.modalTitle.textContent = `Session Log: ${userId || 'system event'}`;
                this.modalBody.innerHTML = '<p class="text-secondary mb-0">Loading related events…</p>';
                this.modal?.show();

                const timeline = await this.fetchTimeline(userId, selected.exam_template_id);
                this.modalBody.innerHTML = this.renderTimeline(timeline, selected);
            });
        });
    }

    async fetchTimeline(userId, examTemplateId) {
        const params = new URLSearchParams({ limit: '20' });
        if (userId) params.set('user_id', userId);
        if (examTemplateId) params.set('exam_template_id', String(examTemplateId));

        try {
            const payload = await this.api.get(`/admin/logs?${params.toString()}`);
            return Array.isArray(payload?.data) ? payload.data : [];
        } catch {
            return [];
        }
    }

    renderTimeline(timeline, selected) {
        const fallback = timeline.length === 0 ? [selected] : timeline;
        return `
            <ul class="list-group list-group-flush">
                ${fallback.map((row) => `
                    <li class="list-group-item px-0">
                        <div class="d-flex justify-content-between gap-2">
                            <div>
                                <div class="fw-semibold">${this.escape(row.event_name ?? row.summary ?? 'Event')}</div>
                                <div class="small text-secondary">${this.escape(row.summary ?? '')}</div>
                                <div class="small text-secondary">Source: ${this.escape(row.event_source ?? '-')} · Severity: ${this.escape(row.severity ?? '-')}</div>
                            </div>
                            <div class="small text-secondary text-nowrap">${this.escape(row.occurred_at ?? row.created_at ?? '-')}</div>
                        </div>
                        ${row.details ? `<pre class="small bg-light border rounded p-2 mt-2 mb-0">${this.escape(String(row.details))}</pre>` : ''}
                    </li>
                `).join('')}
            </ul>
        `;
    }

    setStatus(message, type = 'secondary') {
        if (!this.status) {
            return;
        }

        this.status.className = `alert alert-${type} border mt-3 mb-0`;
        this.status.textContent = message;
    }

    escape(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }
}

document.addEventListener('DOMContentLoaded', () => new AdminDashboard().init());
