import { chromium } from 'playwright';
import fs from 'node:fs';

async function run() {
  fs.mkdirSync('artifacts/visual', { recursive: true });

  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 1366, height: 900 } });

  const targets = [
    ['login', '/login'],
    ['admin-dashboard', '/admin/dashboard'],
    ['admin-analytics', '/admin/analytics'],
    ['teacher-questions', '/teacher/questions'],
    ['student-results', '/student/results'],
  ];

  for (const [name, route] of targets) {
    await page.goto(`http://127.0.0.1:8080${route}`);
    await page.screenshot({ path: `artifacts/visual/${name}.png`, fullPage: true });
  }

  await browser.close();
}

run();
