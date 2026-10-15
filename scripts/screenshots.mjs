// Screenshots of Harbour Coffee, as used in Promo Bar's docs. Run against a freshly seeded demo with the demo users:
//
//   php artisan demo:users && php artisan demo:seed
//   cd scripts && npm install && npm run screenshots
//
// DEMO_URL     the demo's address (default http://localhost:8000)
// SCREENSHOTS  where to write them (default ./screenshots)
// CHROME       a Chrome or Chromium to drive (default: Google Chrome on macOS)
import { chromium } from 'playwright-core';
import { mkdirSync } from 'node:fs';

const base = (process.env.DEMO_URL || 'http://localhost:8000').replace(/\/$/, '');
const out = process.env.SCREENSHOTS || new URL('./screenshots', import.meta.url).pathname;
const chrome = process.env.CHROME || '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
mkdirSync(`${out}/images`, { recursive: true });

// Local demos often use a self-signed certificate (Herd, Valet).
const browser = await chromium.launch({ args: ['--ignore-certificate-errors'], executablePath: chrome });
const desktop = { viewport: { width: 1280, height: 800 }, deviceScaleFactor: 2, colorScheme: 'light', reducedMotion: 'reduce' };

async function settle(page, ms = 900) {
    await page.evaluate(() => document.fonts.ready);
    await page.waitForTimeout(ms);
}

// Hold rotating bars still, so the shot is the first bar.
async function pause(page) {
    await page.evaluate(() => document.querySelectorAll('[data-promo-bar-pause]').forEach((b) => b.offsetParent && b.click()));
}

/*
|--------------------------------------------------------------------------
| The shop
|--------------------------------------------------------------------------
*/

// A guest with two bags in the basket: the sale, free delivery, the newsletter.
const guest = await browser.newContext(desktop);
const shop = await guest.newPage();
await shop.goto(`${base}/demo-basket?to=/shop`);
await settle(shop);
await shop.screenshot({ path: `${out}/images/shop.png` });

// The same guest on a phone.
const phone = await browser.newContext({ ...desktop, viewport: { width: 390, height: 844 }, deviceScaleFactor: 3, storageState: await guest.storageState() });
const phonePage = await phone.newPage();
await phonePage.goto(`${base}/shop`);
await settle(phonePage);
await phonePage.screenshot({ path: `${out}/images/shop-mobile.png` });

// Ada, a member, signed in: the members' bar instead of the newsletter.
const ada = await browser.newContext(desktop);
const member = await ada.newPage();
await member.goto(`${base}/demo-login?as=ada@example.com&to=/`);
await member.waitForURL(`${base}/`);
await settle(member);
await member.screenshot({ path: `${out}/images/member.png` });

/*
|--------------------------------------------------------------------------
| The Control Panel
|--------------------------------------------------------------------------
*/

const admin = await browser.newContext({ ...desktop, viewport: { width: 1440, height: 900 } });
const cp = await admin.newPage();
await cp.goto(`${base}/demo-login?to=/cp/promo-bars`);
await cp.waitForURL('**/cp/promo-bars');
await settle(cp, 1500);
await cp.screenshot({ path: `${out}/images/cp-bars.png` });

await cp.getByRole('link', { name: 'Autumn sale', exact: true }).click();
await cp.waitForURL('**/cp/promo-bars/*');
await settle(cp, 1500);
await cp.screenshot({ path: `${out}/images/cp-bar.png` });

await cp.getByRole('tab', { name: 'Style' }).click();
await settle(cp, 800);
await cp.screenshot({ path: `${out}/images/cp-bar-style.png` });

await cp.goto(`${base}/cp/promo-bars`);
await settle(cp, 1200);
await cp.getByRole('link', { name: 'Free delivery', exact: true }).click();
await cp.waitForURL('**/cp/promo-bars/*');
await settle(cp, 1500);
await cp.getByRole('tab', { name: 'Where' }).click();
await settle(cp, 800);
await cp.screenshot({ path: `${out}/images/cp-where.png` });

await cp.goto(`${base}/cp/addons/promo-bar-statamic/settings`);
await settle(cp, 1500);
await cp.getByRole('tab', { name: 'Styles' }).click();
await settle(cp, 1000);
await cp.screenshot({ path: `${out}/images/cp-styles.png` });

await cp.getByRole('tab', { name: 'Analytics' }).click();
await settle(cp, 800);
await cp.screenshot({ path: `${out}/images/cp-analytics.png` });

await browser.close();
console.log(`Written to ${out}`);
