import { ApiClient } from '../utils/ApiClient.js';

const api = new ApiClient();
const statusEl = document.getElementById('grading-status');
const sessionsBody = document.getElementById('grading-sessions-body');
const form = document.getElementById('grading-form');

function setStatus(message, tone = 'secondary') {
    if (!statusEl) return;
    statusEl.className = `alert alert-${tone}`;
    statusEl.textContent = message;
}

async function loadSessionAnswers(sessionId) {
    const response = await api.get(`/teacher/grading/${encodeURIComponent(sessionId)}/answers`);
    return response?.data?.items || [];
}

function renderSessions(items) {
    if (!sessionsBody) return;
    if (items.length === 0) {
        sessionsBody.innerHTML = '<tr><td colspan="4" class="text-center text-secondary py-4">No pending sessions.</td></tr>';
        return;
    }

    sessionsBody.innerHTML = items.map((item) => `
        <tr data-session-id="${item.session_id}" style="cursor:pointer;">
            <td>${item.session_id}</td>
            <td>${item.first_name || ''} ${item.last_name || ''}</td>
            <td>${item.exam_title || '-'}</td>
            <td>${item.submitted_at || '-'}</td>
        </tr>
    `).join('');

    sessionsBody.querySelectorAll('tr[data-session-id]').forEach((row) => {
        row.addEventListener('click', async () => {
            const sessionId = row.dataset.sessionId;
            try {
                const answers = await loadSessionAnswers(sessionId);
                if (answers.length === 0) {
                    setStatus('No ungraded answers in selected session.', 'warning');
                    return;
                }

                const first = answers[0];
                document.getElementById('answer-id').value = first.answer_id || '';
                document.getElementById('expected-updated-at').value = first.updated_at || '';
                setStatus(`Loaded ${answers.length} ungraded answer(s) for session ${sessionId}.`, 'info');
            } catch (error) {
                setStatus(error.message || 'Failed to load session answers.', 'danger');
            }
        });
    });
}

async function loadPending() {
    const response = await api.get('/teacher/grading/pending?limit=25');
    const items = response?.data?.items || [];
    renderSessions(items);
    setStatus(`Loaded ${items.length} pending session(s).`, 'success');
}

document.addEventListener('DOMContentLoaded', async () => {
    if (!statusEl || !form || !sessionsBody) return;
    try {
        setStatus('Loading pending grading queue…');
        await loadPending();
    } catch (error) {
        setStatus(error.message || 'Failed to load pending queue.', 'danger');
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const answerId = document.getElementById('answer-id').value.trim();
        const score = Number(document.getElementById('score').value);
        const feedback = document.getElementById('feedback').value.trim();
        const moderationStatus = document.getElementById('moderation-status').value;
        const moderationNote = document.getElementById('moderation-note').value.trim();
        const expectedUpdatedAt = document.getElementById('expected-updated-at').value.trim();

        if (!answerId) {
            setStatus('Answer ID is required.', 'danger');
            return;
        }

        if (!expectedUpdatedAt) {
            setStatus('Select a session row first to load lock token.', 'warning');
            return;
        }

        try {
            setStatus('Saving score…', 'info');
            const response = await api.post(`/teacher/grading/${encodeURIComponent(answerId)}/score`, {
                score,
                feedback,
                moderation_status: moderationStatus,
                moderation_note: moderationNote,
                expected_updated_at: expectedUpdatedAt,
            });
            document.getElementById('expected-updated-at').value = response?.data?.updated_at || '';
            setStatus('Score saved successfully.', 'success');
            await loadPending();
        } catch (error) {
            setStatus(error.message || 'Failed to save score.', 'danger');
        }
    });
});
