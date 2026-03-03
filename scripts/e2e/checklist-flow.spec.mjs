import { chromium } from 'playwright';

const BASE_URL = process.env.E2E_BASE_URL || 'http://127.0.0.1:8080';

async function tryLogin(page) {
  await page.goto(`${BASE_URL}/login`, { waitUntil: 'domcontentloaded' });

  const identityInput = page.locator('#identity, input[name="identity"], input[type="email"], input[type="text"]');
  const passwordInput = page.locator('#password, input[name="password"], input[type="password"]');

  const hasIdentity = (await identityInput.count()) > 0;
  const hasPassword = (await passwordInput.count()) > 0;

  if (!hasIdentity || !hasPassword) {
    console.warn('[checklist-flow] Login form not detected at /login; continuing with route smoke checks.');
    return;
  }

  await identityInput.first().fill('admin@example.com');
  await passwordInput.first().fill('password');

  const loginButton = page.locator('#loginBtn, button[type="submit"]');
  if ((await loginButton.count()) > 0) {
    await loginButton.first().click();
  } else {
    await page.keyboard.press('Enter');
  }

  await page.waitForTimeout(1000);

  const otpInput = page.locator('#otp, input[name="otp"], input[name="code"]');
  if ((await otpInput.count()) > 0) {
    await otpInput.first().fill('123456');
    const verifyBtn = page.locator('#verifyBtn, button[type="submit"]');
    if ((await verifyBtn.count()) > 0) {
      await verifyBtn.first().click();
    }
  }
}

async function run() {
  const browser = await chromium.launch();
  const page = await browser.newPage();

  try {
    await tryLogin(page);

    // Smoke route checks (token/cookies/environment dependent)
    const routes = [
      '/admin/roles-permissions',
      '/admin/settings',
      '/admin/questions/import',
      '/teacher/grading',
      '/student/results',
    ];

    for (const route of routes) {
      await page.goto(`${BASE_URL}${route}`, { waitUntil: 'domcontentloaded' });
      await page.waitForTimeout(300);
    }
  } finally {
    await browser.close();
  }
}

run();
