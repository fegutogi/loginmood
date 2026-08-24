import { defineConfig, devices } from '@playwright/test';

const baseURL = process.env.LOGINMOOD_QA_BASE_URL || 'http://127.0.0.1:9400';

export default defineConfig( {
	testDir: './tests/e2e',
	fullyParallel: false,
	workers: 1,
	retries: 0,
	timeout: 30000,
	snapshotPathTemplate: '{testDir}/{testFilePath}-snapshots/{arg}-{projectName}{ext}',
	expect: {
		toHaveScreenshot: { maxDiffPixelRatio: 0.01 },
	},
	use: {
		baseURL,
		trace: 'retain-on-failure',
		screenshot: 'only-on-failure',
	},
	reporter: [ [ 'line' ], [ 'html', { open: 'never' } ] ],
	projects: [
		{ name: 'desktop-chromium', use: { ...devices['Desktop Chrome'] } },
		{ name: 'mobile-chromium', use: { ...devices['Pixel 7'] } },
		{ name: 'firefox', use: { ...devices['Desktop Firefox'] } },
		{ name: 'webkit', use: { ...devices['Desktop Safari'] } },
	],
} );
