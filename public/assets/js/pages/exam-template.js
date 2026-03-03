import { ApiClient } from '../utils/ApiClient.js';

const statusEl = document.getElementById('exam-template-status');
const cancelBtn = document.getElementById('exam-template-cancel');
const saveBtn = document.getElementById('exam-template-save');
const questionListEl = document.getElementById('exam-template-question-list');
const totalPointsEl = document.getElementById('exam-template-total-points');
const addBtn = document.getElementById('exam-template-add-question');

const api = new ApiClient();
let repositoryItems = [];
let repositoryModal = null;

function setStatus(message, tone = 'secondary') {
    if (!statusEl) return;
    statusEl.className = `alert alert-${tone}`;
    statusEl.textContent = message;
}

function showToast(message) {
    const toastEl = document.getElementById('ui-shell-toast');
    if (!toastEl) return;
    toastEl.querySelector('.toast-body').textContent = message;
    bootstrap.Toast.getOrCreateInstance(toastEl).show();
}

function getQuestionCards() {
    return Array.from(questionListEl?.querySelectorAll('[data-question-id]') || []);
}

function updateTotalPoints() {
    if (!totalPointsEl) return;
    const total = getQuestionCards().reduce((sum, card) => {
        const marks = Number(card.getAttribute('data-marks') || '0');
        return sum + (Number.isFinite(marks) ? marks : 0);
    }, 0);
    totalPointsEl.textContent = `${total} pts`;
}

function removeQuestionCard(button) {
    const card = button.closest('[data-question-id]');
    if (card) {
        card.remove();
        updateTotalPoints();
    }
    setStatus('Question removed from template draft.', 'warning');
}

function wireQuestionCardActions() {
    questionListEl?.querySelectorAll('[data-action="remove-question-card"]').forEach((button) => {
        if (button.dataset.wired === '1') return;
        button.dataset.wired = '1';
        button.addEventListener('click', () => removeQuestionCard(button));
    });
}

function collectTemplateDraft() {
    return {
        title: document.getElementById('exam-title')?.value?.trim() || '',
        subject: document.getElementById('subject')?.value || '',
        classes: document.getElementById('classes')?.value || '',
        duration_minutes: Number(document.getElementById('duration')?.value || 0),
        randomize_questions: Boolean(document.getElementById('randomizeQuestions')?.checked),
        shuffle_answers: Boolean(document.getElementById('shuffleAnswers')?.checked),
        show_results_immediately: Boolean(document.getElementById('showResults')?.checked),
        selected_questions: getQuestionCards().map((card) => card.getAttribute('data-question-id')),
        saved_at: new Date().toISOString(),
    };
}

function persistDraft() {
    window.localStorage.setItem('exam_template_draft', JSON.stringify(collectTemplateDraft()));
    setStatus('Draft saved locally without leaving this page.', 'success');
    showToast('Exam template draft saved.');
}

function hydrateDraft() {
    const raw = window.localStorage.getItem('exam_template_draft');
    if (!raw) return;

    try {
        const payload = JSON.parse(raw);
        if (payload.title) document.getElementById('exam-title').value = payload.title;
        if (payload.subject) document.getElementById('subject').value = payload.subject;
        if (payload.classes) document.getElementById('classes').value = payload.classes;
        if (payload.duration_minutes) document.getElementById('duration').value = String(payload.duration_minutes);

        if (typeof payload.randomize_questions === 'boolean') document.getElementById('randomizeQuestions').checked = payload.randomize_questions;
        if (typeof payload.shuffle_answers === 'boolean') document.getElementById('shuffleAnswers').checked = payload.shuffle_answers;
        if (typeof payload.show_results_immediately === 'boolean') document.getElementById('showResults').checked = payload.show_results_immediately;

        setStatus('Recovered your last draft. Continue editing.', 'info');
    } catch {
        setStatus('Unable to restore previous draft data.', 'warning');
    }
}

function clearDraft() {
    if (!window.confirm('Discard current draft changes?')) return;

    document.getElementById('exam-title').value = '';
    document.getElementById('subject').selectedIndex = 0;
    document.getElementById('classes').selectedIndex = 0;
    document.getElementById('duration').value = '';

    ['randomizeQuestions', 'shuffleAnswers', 'showResults'].forEach((id) => {
        const element = document.getElementById(id);
        if (element) element.checked = false;
    });

    window.localStorage.removeItem('exam_template_draft');
    setStatus('Draft cleared. You are still on this page.', 'secondary');
}

function escape(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function renderRepository(items) {
    const body = document.getElementById('question-repo-modal-body');
    if (!body) return;

    if (items.length === 0) {
        body.innerHTML = '<p class="text-secondary mb-0">No repository questions found.</p>';
        return;
    }

    body.innerHTML = `
        <div class="list-group">
            ${items.map((item) => `
                <label class="list-group-item list-group-item-action d-flex gap-2 align-items-start">
                    <input class="form-check-input mt-1" type="checkbox" value="${escape(item.id)}" data-action="repo-pick">
                    <div>
                        <div class="fw-semibold">${escape(item.prompt || '(No prompt)')}</div>
                        <div class="small text-secondary">${escape(item.type || 'unknown')} · ${escape(item.metadata?.difficulty || 'medium')} · ${(item.metadata?.learning_objective || '').slice(0, 80)}</div>
                    </div>
                </label>
            `).join('')}
        </div>
    `;
}

function appendQuestionCard(item) {
    if (!questionListEl) return;
    if (questionListEl.querySelector(`[data-question-id="${CSS.escape(item.id)}"]`)) {
        return;
    }

    const marks = Number(item.metadata?.marks || 1);
    const subject = escape(item.metadata?.subject || item.metadata?.topic || 'General');
    const type = escape(item.type || 'question');
    const prompt = escape(item.prompt || '(No prompt)');

    const card = document.createElement('div');
    card.className = 'card border bg-body p-3 hover-border-primary transition-colors group';
    card.setAttribute('data-question-id', item.id);
    card.setAttribute('data-marks', String(marks));
    card.innerHTML = `
        <div class="d-flex justify-content-between align-items-start mb-2">
            <p class="mb-0 small fw-medium text-body text-truncate-2">${prompt}</p>
            <button class="btn btn-link p-0 text-secondary hover-text-danger lh-1" type="button" data-action="remove-question-card"><span class="material-symbols-outlined fs-6">delete</span></button>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-secondary bg-opacity-25 text-body fw-medium" style="font-size: 0.65rem;">${subject}</span>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-medium" style="font-size: 0.65rem;">${type}</span>
            <span class="ms-auto x-small fw-bold text-secondary">${marks} pts</span>
        </div>
    `;

    questionListEl.prepend(card);
}

async function openRepositoryPicker() {
    setStatus('Loading repository questions…', 'info');

    try {
        const payload = await api.get('/teacher/questions/repository?limit=100');
        repositoryItems = Array.isArray(payload?.data?.items) ? payload.data.items : [];
        renderRepository(repositoryItems);
        repositoryModal?.show();
        setStatus(`Repository loaded (${repositoryItems.length} questions).`, 'success');
    } catch (error) {
        setStatus(error.message || 'Failed to load repository questions.', 'danger');
    }
}

function commitRepositorySelection() {
    const selectedIds = Array.from(document.querySelectorAll('[data-action="repo-pick"]:checked')).map((el) => el.value);
    if (selectedIds.length === 0) {
        setStatus('Select at least one question to add.', 'warning');
        return;
    }

    const map = new Map(repositoryItems.map((item) => [String(item.id), item]));
    selectedIds.forEach((id) => {
        const item = map.get(id);
        if (item) appendQuestionCard(item);
    });

    wireQuestionCardActions();
    updateTotalPoints();
    repositoryModal?.hide();
    setStatus(`Added ${selectedIds.length} question(s) to template draft.`, 'success');
    showToast('Selected questions added to template.');
}

document.addEventListener('DOMContentLoaded', () => {
    if (!statusEl || !saveBtn || !cancelBtn) return;

    const modalEl = document.getElementById('question-repo-modal');
    if (modalEl) {
        repositoryModal = bootstrap.Modal.getOrCreateInstance(modalEl);
    }

    hydrateDraft();
    wireQuestionCardActions();
    updateTotalPoints();

    saveBtn.addEventListener('click', persistDraft);
    cancelBtn.addEventListener('click', clearDraft);
    addBtn?.addEventListener('click', openRepositoryPicker);
    document.getElementById('question-repo-apply')?.addEventListener('click', commitRepositorySelection);
});
