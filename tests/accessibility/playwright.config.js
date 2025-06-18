// @ts-check
const { defineConfig } = require('@playwright/test');

/**
 * Playwright configuration for accessibility testing
 * 
 * @see https://playwright.dev/docs/test-configuration
 */
module.exports = defineConfig({
  testDir: './tests/accessibility',
  
  /* Run tests in files in parallel */
  fullyParallel: true,
  
  /* Fail the build on CI if you accidentally left test.only in the source code. */
  forbidOnly: !!process.env.CI,
  
  /* Retry on CI only */
  retries: process.env.CI ? 2 : 0,
  
  /* Opt out of parallel tests on CI. */
  workers: process.env.CI ? 1 : undefined,
  
  /* Reporter to use. See https://playwright.dev/docs/test-reporters */
  reporter: [
    ['html', { outputFolder: 'tests/reports/accessibility' }],
    ['json', { outputFile: 'tests/reports/accessibility-results.json' }]
  ],
  
  /* Shared settings for all the projects below. See https://playwright.dev/docs/api/class-testoptions. */
  use: {
    /* Base URL to use in actions like `await page.goto('/')`. */
    baseURL: 'http://localhost:8080',
    
    /* Collect trace when retrying the failed test. See https://playwright.dev/docs/trace-viewer */
    trace: 'on-first-retry',
    
    /* Take screenshot on failure */
    screenshot: 'only-on-failure',
    
    /* Record video on failure */
    video: 'retain-on-failure'
  },

  /* Configure projects for major browsers */
  projects: [
    {
      name: 'chromium',
      use: { ...require('@playwright/test').devices['Desktop Chrome'] },
    },
    
    {
      name: 'firefox', 
      use: { ...require('@playwright/test').devices['Desktop Firefox'] },
    },
    
    {
      name: 'webkit',
      use: { ...require('@playwright/test').devices['Desktop Safari'] },
    },
    
    /* Test against mobile viewports. */
    {
      name: 'Mobile Chrome',
      use: { ...require('@playwright/test').devices['Pixel 5'] },
    },
    
    {
      name: 'Mobile Safari',
      use: { ...require('@playwright/test').devices['iPhone 12'] },
    }
  ],

  /* Run your local dev server before starting the tests */
  webServer: {
    command: 'echo "WordPress development server should be running on localhost:8080"',
    url: 'http://localhost:8080',
    reuseExistingServer: !process.env.CI,
    timeout: 120 * 1000,
  }
});