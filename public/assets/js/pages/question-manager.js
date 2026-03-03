import { ApiClient } from '../utils/ApiClient.js';

class QuestionManager {
    constructor() {
        this.api = new ApiClient();
        this.tableBody = document.getElementById('question-table-body');
        this.searchInput = document.getElementById('question-search');
        this.items = [];
        this.filtered = [];
    }

    async init() {
        if (!this.tableBody) return;

        const initialQuery = new URLSearchParams(window.location.search).get('q') || '';
        if (this.searchInput) {
            this.searchInput.value = initialQuery;
        }

        this.attachListeners();
        await this.loadQuestions(initialQuery);
    }

    attachListeners() {
        this.searchInput?.addEventListener('input', this.debounce((event) => {
            const query = event.target.value || '';
            this.filterRows(query);
            this.syncQueryParam(query);
            this.renderRows();
        }, 200));
    }

    syncQueryParam(query) {
        const url = new URL(window.location.href);
        if (query.trim()) {
            url.searchParams.set('q', query.trim());
        } else {
            url.searchParams.delete('q');
        }
        window.history.replaceState({}, '', `${url.pathname}${url.search}`);
    }

    async loadQuestions(initialQuery = '') {
        this.tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-secondary py-4">Loading…</td></tr>';
        const response = await this.api.get('/teacher/questions/repository?limit=100');
        this.items = Array.isArray(response?.data?.items) ? response.data.items : [];
        this.filterRows(initialQuery);
        this.renderRows();
    }

    filterRows(query) {
        const q = query.trim().toLowerCase();
        if (!q) {
            this.filtered = [...this.items];
            return;
        }

        this.filtered = this.items.filter((item) => {
            const prompt = String(item.prompt || '').toLowerCase();
            const type = String(item.type || '').toLowerCase();
            return prompt.includes(q) || type.includes(q);
        });
    }

    renderRows() {
        if (this.filtered.length === 0) {
            this.tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-secondary py-4">No questions found.</td></tr>';
            return;
        }

        this.tableBody.innerHTML = this.filtered.map((item) => `
            <tr>
                <td class="small">${item.id}</td>
                <td>${item.type}</td>
                <td>${this.escape(item.prompt || '')}</td>
                <td>${item.version ?? '-'}</td>
                <td>${item.updated_at ?? '-'}</td>
                <td>
                    <button class="btn btn-sm btn-outline-danger" data-action="delete" data-id="${item.id}">Archive</button>
                </td>
            </tr>
        `).join('');

        this.tableBody.querySelectorAll('[data-action="delete"]').forEach((btn) => {
            btn.addEventListener('click', async (event) => {
                const id = event.currentTarget.dataset.id;
                if (!window.confirm('Archive this question?')) return;
                try {
                    await this.api.delete(`/teacher/questions/${encodeURIComponent(id)}`);
                    await this.loadQuestions(this.searchInput?.value || '');
                    const toastEl = document.getElementById('ui-shell-toast');
                    if (toastEl) {
                        toastEl.querySelector('.toast-body').textContent = 'Question archived successfully.';
                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                    }
                } catch (error) {
                    const toastEl = document.getElementById('ui-shell-toast');
                    if (toastEl) {
                        toastEl.querySelector('.toast-body').textContent = error.message || 'Failed to archive question.';
                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                    }
                }
            });
        });
    }

    escape(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    debounce(func, wait) {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => func(...args), wait);
        };
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const manager = new QuestionManager();
    manager.init().catch((error) => {
        if (manager.tableBody) {
            manager.tableBody.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4">${error.message || 'Failed to load questions.'}</td></tr>`;
        }
    });
});
