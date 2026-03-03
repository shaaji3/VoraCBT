import { ApiClient } from '../utils/ApiClient.js';

const api = new ApiClient();

const form = document.getElementById('settings-form');
const statusEl = document.getElementById('settings-status');

function setStatus(message, tone = 'secondary') {
    if (!statusEl) return;
    statusEl.className = `alert alert-${tone}`;
    statusEl.textContent = message;
}

function fillForm(settings) {
    form.platform_name.value = settings.platform_name || '';
    form.support_email.value = settings.support_email || '';
    form.session_timeout_minutes.value = settings.session_timeout_minutes || 120;
    form.allow_result_download.checked = Boolean(settings.allow_result_download);
}

document.addEventListener('DOMContentLoaded', async () => {
    if (!form || !statusEl) return;

    try {
        setStatus('Loading settings…');
        const response = await api.get('/admin/settings');
        fillForm(response?.data || {});
        setStatus('Settings loaded.', 'success');
    } catch (error) {
        setStatus(error.message || 'Unable to load settings.', 'danger');
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const payload = {
            platform_name: form.platform_name.value,
            support_email: form.support_email.value,
            session_timeout_minutes: Number(form.session_timeout_minutes.value),
            allow_result_download: form.allow_result_download.checked,
        };

        try {
            setStatus('Saving settings…', 'info');
            await api.post('/admin/settings', payload);
            setStatus('Settings saved.', 'success');
        } catch (error) {
            setStatus(error.message || 'Unable to save settings.', 'danger');
        }
    });
});
