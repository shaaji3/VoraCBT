import { chromium } from 'playwright';

async function run() {
  const browser = await chromium.launch();
  const page = await browser.newPage();

  await page.goto('http://127.0.0.1:8080/login');
  await page.fill('#identity', 'admin@example.com');
  await page.fill('#password', 'password');
  await page.click('#loginBtn');

  await page.waitForTimeout(1000);
  // If 2FA is active this should exist
  if (await page.locator('#otp').count()) {
    await page.fill('#otp', '123456');
    await page.click('#verifyBtn');
  }

  // Smoke route checks (token/cookies/environment dependent)
  const routes = [
    '/admin/roles-permissions',
    '/admin/settings',
    '/admin/questions/import',
    '/teacher/grading',
    '/student/results',
  ];

  for (const route of routes) {
    await page.goto(`http://127.0.0.1:8080${route}`);
    await page.waitForTimeout(300);
  }

  await browser.close();
}

run();
