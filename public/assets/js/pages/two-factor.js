import { ApiClient } from '../utils/ApiClient.js';

const api = new ApiClient('/auth');

const form = document.getElementById('twoFactorForm');
const otp = document.getElementById('otp');
const verifyBtn = document.getElementById('verifyBtn');
const resendBtn = document.getElementById('resendBtn');
const errorEl = document.getElementById('twoFactorError');
const infoEl = document.getElementById('twoFactorInfo');
const challengeId = new URLSearchParams(window.location.search).get('challenge') || '';

function showError(message) {
    errorEl.textContent = message;
    errorEl.classList.remove('d-none');
    infoEl.classList.add('d-none');
}

function showInfo(message) {
    infoEl.textContent = message;
    infoEl.classList.remove('d-none');
    errorEl.classList.add('d-none');
}

document.addEventListener('DOMContentLoaded', () => {
    if (!form) return;
    if (!challengeId) {
        showError('Missing 2FA challenge. Please login again.');
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        verifyBtn.disabled = true;
        try {
            const response = await api.post('/2fa/verify', { code: otp.value.trim(), challenge_id: challengeId });
            showInfo('Verification successful. Redirecting…');
            window.setTimeout(() => {
                window.location.href = response?.data?.redirect || '/';
            }, 300);
        } catch (error) {
            showError(error.message || 'Verification failed.');
        } finally {
            verifyBtn.disabled = false;
        }
    });

    resendBtn?.addEventListener('click', async () => {
        resendBtn.disabled = true;
        try {
            const response = await api.post('/2fa/resend', { challenge_id: challengeId });
            showInfo(response?.data?.message || 'Code resent.');
        } catch (error) {
            showError(error.message || 'Failed to resend code.');
        } finally {
            resendBtn.disabled = false;
        }
    });
});
