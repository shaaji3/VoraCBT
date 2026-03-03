import { ApiClient } from '../utils/ApiClient.js';

const api = new ApiClient();

const form = document.getElementById('question-create-form');
const statusEl = document.getElementById('question-create-status');

function setStatus(message, tone = 'secondary') {
    if (!statusEl) return;
    statusEl.className = `alert alert-${tone}`;
    statusEl.textContent = message;
}

function parseOptions(raw) {
    if (!raw || raw.trim() === '') return [];
    try {
        const parsed = JSON.parse(raw);
        return Array.isArray(parsed) ? parsed : [];
    } catch {
        return raw.split('\n').map((x) => x.trim()).filter(Boolean);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (!form || !statusEl) return;

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const type = form.dataset.questionType || 'mcq';
        const prompt = document.getElementById('question-prompt').value.trim();
        const difficulty = document.getElementById('question-difficulty').value;
        const tags = document.getElementById('question-tags').value
            .split(',')
            .map((item) => item.trim())
            .filter(Boolean);
        const optionsRaw = document.getElementById('question-options').value;
        const options = parseOptions(optionsRaw);

        if (!prompt) {
            setStatus('Prompt is required.', 'danger');
            return;
        }

        const payload = {
            type,
            content: {
                prompt,
                options,
            },
            metadata: {
                difficulty,
                tags,
            },
        };

        try {
            setStatus('Saving question…', 'info');
            await api.post('/teacher/questions', payload);
            setStatus('Question created successfully.', 'success');
            form.reset();
        } catch (error) {
            setStatus(error.message || 'Failed to create question.', 'danger');
        }
    });
});
