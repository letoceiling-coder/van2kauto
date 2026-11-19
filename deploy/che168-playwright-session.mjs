import { chromium } from 'playwright-core';
import { writeFileSync } from 'fs';

const executableCandidates = [
  process.env.CHROME_PATH,
  '/snap/bin/chromium',
  '/usr/bin/chromium-browser',
  '/usr/bin/chromium',
  '/usr/bin/google-chrome-stable',
].filter(Boolean);
const fs = await import('node:fs');
const executablePath =
  executableCandidates.find((p) => fs.existsSync(p)) || executableCandidates[0];

const url = process.env.CHE168_WARM_URL || 'https://global.che168.com/en/used-cars';
const apiProbe =
  'https://globalapi.che168.com/api/v1/search?_appid=global.m&language=en&fromsource=0&pageindex=1&pagesize=1&sort=0&vehicle_list=1&report_status=1,3';

const browser = await chromium.launch({
  executablePath,
  headless: true,
  args: [
    '--no-sandbox',
    '--disable-setuid-sandbox',
    '--disable-dev-shm-usage',
    '--disable-blink-features=AutomationControlled',
  ],
});

const context = await browser.newContext({
  userAgent:
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
  locale: 'en-US',
  viewport: { width: 1280, height: 720 },
});

const page = await context.newPage();
await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 120000 });

// Stage 1: inline script challenge (auto)
await page.waitForTimeout(2000);

const title = await page.title();
const hasCaptcha =
  (await page.locator('#captcha, .captcha').count()) > 0 ||
  title.includes('Security Verification');

if (hasCaptcha > 0 || title.includes('Security Verification')) {
  const token = process.env.CHE168_EDGEONE_CAPTCHA_TOKEN;
  if (token) {
    await context.addCookies([
      {
        name: 'EO-Bot-Captcha-Token',
        value: token,
        domain: '.che168.com',
        path: '/',
      },
    ]);
    await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 120000 });
    await page.waitForTimeout(2000);
  } else {
    for (const frame of page.frames()) {
      try {
        await frame.locator('#tcaptcha_transform, .tcaptcha-transform, input[type=checkbox]').first().click({
          timeout: 3000,
        });
      } catch {
        /* continue */
      }
    }
    await page.waitForTimeout(15000);
  }
}

await page.goto(url, { waitUntil: 'networkidle', timeout: 120000 }).catch(() => {});

const cookies = await context.cookies();
const device = cookies.find((c) => c.name === 'che_sessionvid')?.value || '';
const cookieHeader = cookies.map((c) => `${c.name}=${c.value}`).join('; ');

let apiOk = false;
let apiPreview = '';
if (device) {
  const apiUrl = `${apiProbe}&deviceid=${encodeURIComponent(device)}`;
  const resp = await page.request.get(apiUrl, {
    headers: {
      Accept: 'application/json',
      Referer: url,
      Cookie: cookieHeader,
    },
  });
  const body = await resp.text();
  apiPreview = body.slice(0, 120);
  apiOk = body.trimStart().startsWith('{') && body.includes('returncode');
}

const out = {
  title: await page.title(),
  url: page.url(),
  device,
  cookie_count: cookies.length,
  cookie_len: cookieHeader.length,
  api_ok: apiOk,
  api_preview: apiPreview,
  cookies: cookieHeader,
};

writeFileSync('/tmp/che168-playwright-result.json', JSON.stringify(out, null, 2));
console.log(JSON.stringify({ ...out, cookies: `[${cookieHeader.length} chars]` }));
await browser.close();
process.exit(apiOk ? 0 : 2);