import { ApiClient } from '../utils/ApiClient.js';

const api = new ApiClient();

const form = document.getElementById('question-create-form');
const statusEl = document.getElementById('question-create-status');

function setStatus(message, tone = 'secondary') {
    if (!statusEl) return;
    statusEl.className = `alert alert-${tone}`;
    statusEl.textContent = message;
}

function parseJson(raw, fallback = null) {
    if (!raw || raw.trim() === '') return fallback;
    try {
        return JSON.parse(raw);
    } catch {
        return fallback;
    }
}

function parseLines(raw) {
    if (!raw || raw.trim() === '') return [];
    return raw.split('\n').map((x) => x.trim()).filter(Boolean);
}

function buildContent(type, prompt, optionsRaw) {
    const jsonPayload = parseJson(optionsRaw, null);

    switch (type) {
        case 'true_false': {
            const parsed = jsonPayload && typeof jsonPayload === 'object' ? jsonPayload : {};
            const answer = typeof parsed.correct_answer === 'boolean' ? parsed.correct_answer : true;
            return { prompt, correct_answer: answer };
        }
        case 'drag_drop': {
            const parsed = jsonPayload && typeof jsonPayload === 'object' ? jsonPayload : {};
            return {
                prompt,
                items: Array.isArray(parsed.items) ? parsed.items : [],
                targets: Array.isArray(parsed.targets) ? parsed.targets : [],
                correct_mapping: parsed.correct_mapping && typeof parsed.correct_mapping === 'object' ? parsed.correct_mapping : {},
            };
        }
        case 'case_study': {
            const parsed = jsonPayload && typeof jsonPayload === 'object' ? jsonPayload : {};
            return {
                case_text: parsed.case_text || prompt,
                questions: Array.isArray(parsed.questions) ? parsed.questions : [],
            };
        }
        case 'fill_in_the_blank': {
            const parsed = jsonPayload && typeof jsonPayload === 'object' ? jsonPayload : null;
            if (parsed) return parsed;
            return { text: prompt, blanks: { 1: { correct: parseLines(optionsRaw) } } };
        }
        case 'matching': {
            const parsed = jsonPayload && typeof jsonPayload === 'object' ? jsonPayload : null;
            if (parsed) return parsed;
            const pairs = parseLines(optionsRaw).map((line) => {
                const [left, right] = line.split('=>').map((x) => x.trim());
                return { left, right };
            }).filter((p) => p.left && p.right);
            return { text: prompt, pairs };
        }
        case 'passage': {
            const parsed = jsonPayload && typeof jsonPayload === 'object' ? jsonPayload : {};
            return { passage: prompt, questions: Array.isArray(parsed.questions) ? parsed.questions : [] };
        }
        case 'mcq':
        default: {
            const parsed = parseJson(optionsRaw, null);
            const options = Array.isArray(parsed)
                ? parsed.map((opt, i) => (typeof opt === 'string' ? { id: String(i + 1), text: opt } : opt))
                : parseLines(optionsRaw).map((line, i) => ({ id: String(i + 1), text: line }));
            const correctOptions = options.length > 0 ? [options[0].id] : [];
            return { prompt, options, correct_options: correctOptions };
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (!form || !statusEl) return;

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const type = form.dataset.questionType || 'mcq';
        const prompt = document.getElementById('question-prompt').value.trim();
        const difficulty = document.getElementById('question-difficulty').value;
        const learningObjective = document.getElementById('question-learning-objective').value.trim();
        const tags = document.getElementById('question-tags').value
            .split(',')
            .map((item) => item.trim())
            .filter(Boolean);
        const optionsRaw = document.getElementById('question-options').value;

        if (!prompt) {
            setStatus('Prompt is required.', 'danger');
            return;
        }

        const payload = {
            type,
            content: buildContent(type, prompt, optionsRaw),
            metadata: {
                difficulty,
                tags,
                learning_objective: learningObjective || null,
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
