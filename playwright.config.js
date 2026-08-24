import { defineConfig, devices } from '@playwright/test';

const baseURL = process.env.LOGINMOOD_QA_BASE_URL || 'http://127.0.0.1:9400';
// macOS reference images render with small font and antialiasing differences on Linux runners.
const visualMaxDiffPixelRatio = process.env.CI ? 0.04 : 0.01;

export default defineConfig( {
	testDir: './tests/e2e',
	fullyParallel: false,
	workers: 1,
	retries: 0,
	timeout: 30000,
	snapshotPathTemplate: '{testDir}/{testFilePath}-snapshots/{arg}-{projectName}{ext}',
	expect: {
		toHaveScreenshot: { maxDiffPixelRatio: visualMaxDiffPixelRatio },
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
