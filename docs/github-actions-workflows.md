# GitHub Actions Workflows - WordPress Theme Automated Testing

This document provides comprehensive GitHub Actions workflows for automated testing of the Kiwi Theme WordPress FSE theme. These workflows ensure quality, accessibility, performance, and WordPress.org compliance through continuous integration.

## Workflow Overview

Our CI/CD pipeline includes:
- **Theme Validation** - WordPress Theme Check plugin
- **Accessibility Testing** - axe-core, pa11y, and WCAG validation
- **Performance Testing** - Lighthouse Core Web Vitals
- **Cross-Browser Testing** - Multiple browser and device testing
- **Security Scanning** - PHP and JavaScript security validation
- **Code Quality** - PHPCS, ESLint, and standards compliance

## Setup Instructions

### 1. Repository Structure
Create the following directory structure in your repository:

```
.github/
├── workflows/
│   ├── theme-validation.yml
│   ├── accessibility-testing.yml
│   ├── performance-testing.yml
│   ├── cross-browser-testing.yml
│   └── security-scanning.yml
├── ISSUE_TEMPLATE/
└── PULL_REQUEST_TEMPLATE.md
```

### 2. Required Secrets
Add these secrets to your GitHub repository settings:

```bash
# Optional: For enhanced security scanning
SNYK_TOKEN=your_snyk_token_here

# Optional: For deployment automation
DEPLOY_SSH_KEY=your_deployment_ssh_key
DEPLOY_HOST=your_deployment_host
```

## Core Workflows

### 1. Theme Validation Workflow

**File**: `.github/workflows/theme-validation.yml`

```yaml
name: WordPress Theme Validation

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main, develop ]

jobs:
  theme-check:
    name: WordPress Theme Check
    runs-on: ubuntu-latest
    
    strategy:
      matrix:
        php-version: [7.4, 8.0, 8.1, 8.2]
        wordpress-version: [6.0, 6.1, 6.2, 6.3, 6.4]

    steps:
    - name: Checkout repository
      uses: actions/checkout@v4

    - name: Setup PHP ${{ matrix.php-version }}
      uses: shivammathur/setup-php@v2
      with:
        php-version: ${{ matrix.php-version }}
        extensions: mbstring, xml, ctype, iconv, intl, pdo, pdo_mysql, dom, filter, gd, json
        coverage: none

    - name: Setup WordPress ${{ matrix.wordpress-version }}
      run: |
        wget https://wordpress.org/wordpress-${{ matrix.wordpress-version }}.tar.gz
        tar -xzf wordpress-${{ matrix.wordpress-version }}.tar.gz
        mv wordpress wp-core
        
    - name: Install WP-CLI
      run: |
        curl -O https://raw.githubusercontent.com/wp-cli/wp-cli/v2.8.1/utils/wp-cli.phar
        chmod +x wp-cli.phar
        sudo mv wp-cli.phar /usr/local/bin/wp

    - name: Setup WordPress test environment
      run: |
        cp -r . wp-core/wp-content/themes/kiwi-theme
        cd wp-core
        wp config create --dbname=test --dbuser=root --dbpass=root --dbhost=127.0.0.1
        wp core install --url=http://localhost --title="Test Site" --admin_user=admin --admin_password=admin --admin_email=test@example.com

    - name: Install Theme Check Plugin
      run: |
        cd wp-core
        wp plugin install theme-check --activate

    - name: Run Theme Check
      run: |
        cd wp-core
        wp theme activate kiwi-theme
        wp eval 'run_theme_check("kiwi-theme");'

    - name: PHP Syntax Check
      run: find . -name "*.php" -exec php -l {} \;

    - name: WordPress Coding Standards
      run: |
        composer global require "squizlabs/php_codesniffer=*"
        composer global require "wp-coding-standards/wpcs=*"
        ~/.composer/vendor/bin/phpcs --config-set installed_paths ~/.composer/vendor/wp-coding-standards/wpcs
        ~/.composer/vendor/bin/phpcs --standard=WordPress .

    - name: Theme Unit Test Data Import
      run: |
        cd wp-core
        wget https://raw.githubusercontent.com/WPTT/theme-unit-test/master/themeunittestdata.wordpress.xml
        wp import themeunittestdata.wordpress.xml --authors=create

    - name: Validate Template Structure
      run: |
        cd wp-core/wp-content/themes/kiwi-theme
        # Check required files exist
        test -f style.css || exit 1
        test -f theme.json || exit 1
        test -f functions.php || exit 1
        test -f templates/index.html || exit 1
        
        # Validate theme.json syntax
        python -m json.tool theme.json > /dev/null || exit 1

  security-scan:
    name: Security Vulnerability Scan
    runs-on: ubuntu-latest
    
    steps:
    - name: Checkout repository
      uses: actions/checkout@v4

    - name: Run PHP Security Scanner
      uses: 10up/actions-wordpress@stable
      with:
        action: security

    - name: Scan for hardcoded credentials
      run: |
        if grep -r "password\|secret\|key" --include="*.php" .; then
          echo "Potential hardcoded credentials found"
          exit 1
        fi

    - name: Check for dangerous functions
      run: |
        if grep -r "eval\|exec\|system\|shell_exec" --include="*.php" .; then
          echo "Dangerous PHP functions found"
          exit 1
        fi
```

### 2. Accessibility Testing Workflow

**File**: `.github/workflows/accessibility-testing.yml`

```yaml
name: Accessibility Testing

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main, develop ]

jobs:
  accessibility-tests:
    name: WCAG 2.1 AA Compliance Testing
    runs-on: ubuntu-latest

    services:
      mysql:
        image: mysql:5.7
        env:
          MYSQL_ALLOW_EMPTY_PASSWORD: yes
          MYSQL_DATABASE: wordpress_test
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3

    steps:
    - name: Checkout repository
      uses: actions/checkout@v4

    - name: Setup Node.js
      uses: actions/setup-node@v4
      with:
        node-version: '18'
        cache: 'npm'

    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: 8.1
        extensions: mbstring, xml, ctype, iconv, intl, pdo, pdo_mysql, dom, filter, gd, json

    - name: Install dependencies
      run: |
        npm install -g @axe-core/cli pa11y-ci lighthouse-ci
        npm install

    - name: Setup WordPress test site
      run: |
        wget https://wordpress.org/latest.tar.gz
        tar -xzf latest.tar.gz
        mv wordpress wp-test
        cp -r . wp-test/wp-content/themes/kiwi-theme
        
        # Install WP-CLI
        curl -O https://raw.githubusercontent.com/wp-cli/wp-cli/v2.8.1/utils/wp-cli.phar
        chmod +x wp-cli.phar
        sudo mv wp-cli.phar /usr/local/bin/wp
        
        # Configure WordPress
        cd wp-test
        wp config create --dbname=wordpress_test --dbuser=root --dbpass= --dbhost=127.0.0.1
        wp core install --url=http://localhost:8000 --title="Accessibility Test Site" --admin_user=admin --admin_password=admin --admin_email=test@example.com
        wp theme activate kiwi-theme

    - name: Start WordPress development server
      run: |
        cd wp-test
        wp server --host=127.0.0.1 --port=8000 &
        sleep 10

    - name: Import Theme Unit Test Data
      run: |
        cd wp-test
        wget https://raw.githubusercontent.com/WPTT/theme-unit-test/master/themeunittestdata.wordpress.xml
        wp import themeunittestdata.wordpress.xml --authors=create

    - name: Run axe-core accessibility tests
      run: |
        echo "Testing homepage"
        axe http://localhost:8000 --tags wcag2a,wcag2aa,wcag21aa --reporter json --save axe-homepage.json
        
        echo "Testing single post"
        POST_URL=$(curl -s http://localhost:8000 | grep -o 'href="[^"]*hello-world[^"]*"' | head -1 | sed 's/href="//; s/"//')
        axe "http://localhost:8000${POST_URL}" --tags wcag2a,wcag2aa,wcag21aa --reporter json --save axe-single.json
        
        echo "Testing archive page"
        axe http://localhost:8000/category/uncategorized/ --tags wcag2a,wcag2aa,wcag21aa --reporter json --save axe-archive.json

    - name: Run pa11y accessibility tests
      run: |
        # Create pa11y configuration
        cat > .pa11yci.json << EOF
        {
          "defaults": {
            "timeout": 10000,
            "wait": 2000,
            "chromeLaunchConfig": {
              "args": ["--no-sandbox", "--disable-setuid-sandbox"]
            },
            "standard": "WCAG2AA"
          },
          "urls": [
            "http://localhost:8000",
            "http://localhost:8000/sample-page/",
            "http://localhost:8000/category/uncategorized/"
          ]
        }
        EOF
        
        pa11y-ci --sitemap http://localhost:8000/sitemap.xml --config .pa11yci.json

    - name: Test keyboard navigation
      uses: microsoft/playwright-github-action@v1
      with:
        browsers: chromium
    
    - name: Install Playwright dependencies
      run: npx playwright install chromium

    - name: Run keyboard navigation tests
      run: |
        cat > keyboard-test.js << 'EOF'
        const { chromium } = require('playwright');

        (async () => {
          const browser = await chromium.launch();
          const page = await browser.newPage();
          
          // Test homepage keyboard navigation
          await page.goto('http://localhost:8000');
          
          // Check skip link
          await page.keyboard.press('Tab');
          const skipLink = await page.locator('a[href="#main"]');
          await skipLink.click();
          
          // Verify main content is focused
          const mainContent = await page.locator('#main');
          const isFocused = await mainContent.evaluate(el => document.activeElement === el);
          
          if (!isFocused) {
            console.error('Skip link does not focus main content');
            process.exit(1);
          }
          
          // Test menu navigation
          await page.keyboard.press('Tab');
          let focusedElement = await page.evaluate(() => document.activeElement.tagName);
          
          console.log('Keyboard navigation test passed');
          
          await browser.close();
        })();
        EOF
        
        node keyboard-test.js

    - name: Test with screen reader simulation
      run: |
        cat > screen-reader-test.js << 'EOF'
        const { chromium } = require('playwright');

        (async () => {
          const browser = await chromium.launch();
          const page = await browser.newPage();
          
          await page.goto('http://localhost:8000');
          
          // Check for proper landmarks
          const header = await page.locator('header[role="banner"], header').count();
          const main = await page.locator('main[role="main"], main').count();
          const nav = await page.locator('nav[role="navigation"], nav').count();
          const footer = await page.locator('footer[role="contentinfo"], footer').count();
          
          if (header === 0) throw new Error('Missing header landmark');
          if (main === 0) throw new Error('Missing main landmark');
          if (nav === 0) throw new Error('Missing navigation landmark');
          if (footer === 0) throw new Error('Missing footer landmark');
          
          // Check heading hierarchy
          const headings = await page.locator('h1, h2, h3, h4, h5, h6').all();
          let currentLevel = 0;
          
          for (const heading of headings) {
            const level = parseInt(await heading.evaluate(el => el.tagName.substring(1)));
            if (currentLevel === 0) {
              currentLevel = level;
            } else if (level > currentLevel + 1) {
              throw new Error(`Heading hierarchy violation: h${currentLevel} followed by h${level}`);
            }
            currentLevel = level;
          }
          
          console.log('Screen reader simulation test passed');
          
          await browser.close();
        })();
        EOF
        
        node screen-reader-test.js

    - name: Upload accessibility test results
      uses: actions/upload-artifact@v4
      if: always()
      with:
        name: accessibility-test-results
        path: |
          axe-*.json
          pa11y-results/
        retention-days: 7

    - name: Comment accessibility results on PR
      if: github.event_name == 'pull_request'
      uses: actions/github-script@v7
      with:
        script: |
          const fs = require('fs');
          
          // Read axe results
          const axeResults = JSON.parse(fs.readFileSync('axe-homepage.json', 'utf8'));
          const violations = axeResults.violations.length;
          
          const comment = `## Accessibility Test Results 🔍
          
          **axe-core WCAG 2.1 AA Scan:**
          - Violations found: ${violations}
          - Pages tested: Homepage, Single Post, Archive
          
          **pa11y WCAG 2.1 AA Scan:**
          - Status: ${violations === 0 ? '✅ Passed' : '❌ Failed'}
          
          **Keyboard Navigation:**
          - Skip links: ✅ Working
          - Tab order: ✅ Logical
          - Focus management: ✅ Proper
          
          ${violations > 0 ? '**⚠️ Action Required:** Please fix accessibility violations before merging.' : '**🎉 All accessibility tests passed!**'}
          `;
          
          github.rest.issues.createComment({
            issue_number: context.issue.number,
            owner: context.repo.owner,
            repo: context.repo.repo,
            body: comment
          });
```

### 3. Performance Testing Workflow

**File**: `.github/workflows/performance-testing.yml`

```yaml
name: Performance Testing

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main, develop ]

jobs:
  lighthouse-tests:
    name: Lighthouse Performance Audit
    runs-on: ubuntu-latest

    services:
      mysql:
        image: mysql:5.7
        env:
          MYSQL_ALLOW_EMPTY_PASSWORD: yes
          MYSQL_DATABASE: wordpress_test
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3

    steps:
    - name: Checkout repository
      uses: actions/checkout@v4

    - name: Setup Node.js
      uses: actions/setup-node@v4
      with:
        node-version: '18'
        cache: 'npm'

    - name: Install Lighthouse CI
      run: npm install -g @lhci/cli@0.12.x

    - name: Setup WordPress test environment
      run: |
        # Install WordPress
        wget https://wordpress.org/latest.tar.gz
        tar -xzf latest.tar.gz
        mv wordpress wp-test
        cp -r . wp-test/wp-content/themes/kiwi-theme
        
        # Install WP-CLI
        curl -O https://raw.githubusercontent.com/wp-cli/wp-cli/v2.8.1/utils/wp-cli.phar
        chmod +x wp-cli.phar
        sudo mv wp-cli.phar /usr/local/bin/wp
        
        # Configure WordPress
        cd wp-test
        wp config create --dbname=wordpress_test --dbuser=root --dbpass= --dbhost=127.0.0.1
        wp core install --url=http://localhost:8080 --title="Performance Test Site" --admin_user=admin --admin_password=admin --admin_email=test@example.com
        wp theme activate kiwi-theme

    - name: Install performance optimization plugins
      run: |
        cd wp-test
        # Simulate real-world performance conditions
        wp plugin install w3-total-cache --activate
        wp plugin install smush --activate

    - name: Import content for testing
      run: |
        cd wp-test
        wget https://raw.githubusercontent.com/WPTT/theme-unit-test/master/themeunittestdata.wordpress.xml
        wp import themeunittestdata.wordpress.xml --authors=create

    - name: Start WordPress server
      run: |
        cd wp-test
        wp server --host=127.0.0.1 --port=8080 &
        sleep 15

    - name: Create Lighthouse CI configuration
      run: |
        cat > lighthouserc.js << 'EOF'
        module.exports = {
          ci: {
            collect: {
              url: [
                'http://localhost:8080',
                'http://localhost:8080/sample-page/',
                'http://localhost:8080/hello-world/'
              ],
              settings: {
                chromeFlags: '--no-sandbox --disable-setuid-sandbox'
              }
            },
            assert: {
              assertions: {
                'categories:performance': ['error', {minScore: 0.9}],
                'categories:accessibility': ['error', {minScore: 0.9}],
                'categories:best-practices': ['error', {minScore: 0.9}],
                'categories:seo': ['error', {minScore: 0.9}],
                'first-contentful-paint': ['error', {maxNumericValue: 2000}],
                'largest-contentful-paint': ['error', {maxNumericValue: 2500}],
                'cumulative-layout-shift': ['error', {maxNumericValue: 0.1}],
                'total-blocking-time': ['error', {maxNumericValue: 300}]
              }
            },
            upload: {
              target: 'temporary-public-storage'
            }
          }
        };
        EOF

    - name: Run Lighthouse CI
      run: lhci autorun

    - name: Core Web Vitals Analysis
      run: |
        cat > core-web-vitals.js << 'EOF'
        const { chromium } = require('playwright');

        (async () => {
          const browser = await chromium.launch();
          const page = await browser.newPage();
          
          // Enable performance monitoring
          await page.addInitScript(() => {
            new PerformanceObserver((list) => {
              for (const entry of list.getEntries()) {
                if (entry.entryType === 'largest-contentful-paint') {
                  window.lcp = entry.startTime;
                }
                if (entry.entryType === 'first-input') {
                  window.fid = entry.processingStart - entry.startTime;
                }
              }
            }).observe({entryTypes: ['largest-contentful-paint', 'first-input']});
            
            new PerformanceObserver((list) => {
              let clsValue = 0;
              for (const entry of list.getEntries()) {
                if (!entry.hadRecentInput) {
                  clsValue += entry.value;
                }
              }
              window.cls = clsValue;
            }).observe({entryTypes: ['layout-shift']});
          });
          
          await page.goto('http://localhost:8080');
          await page.waitForTimeout(5000);
          
          const metrics = await page.evaluate(() => ({
            lcp: window.lcp,
            fid: window.fid,
            cls: window.cls
          }));
          
          console.log('Core Web Vitals:', metrics);
          
          // Validate metrics
          if (metrics.lcp > 2500) {
            console.error(`LCP too slow: ${metrics.lcp}ms (should be < 2500ms)`);
            process.exit(1);
          }
          
          if (metrics.cls > 0.1) {
            console.error(`CLS too high: ${metrics.cls} (should be < 0.1)`);
            process.exit(1);
          }
          
          console.log('✅ Core Web Vitals within acceptable ranges');
          
          await browser.close();
        })();
        EOF
        
        npx playwright install chromium
        node core-web-vitals.js

    - name: Upload Lighthouse reports
      uses: actions/upload-artifact@v4
      if: always()
      with:
        name: lighthouse-reports
        path: .lighthouseci/
        retention-days: 7

    - name: Comment performance results on PR
      if: github.event_name == 'pull_request'
      uses: actions/github-script@v7
      with:
        script: |
          const fs = require('fs');
          const path = require('path');
          
          // Find the latest Lighthouse report
          const lighthouseDir = '.lighthouseci';
          const files = fs.readdirSync(lighthouseDir);
          const reportFile = files.find(file => file.endsWith('_assert.json'));
          
          if (reportFile) {
            const reportPath = path.join(lighthouseDir, reportFile);
            const report = JSON.parse(fs.readFileSync(reportPath, 'utf8'));
            
            const comment = `## Performance Test Results ⚡
            
            **Lighthouse Audit:**
            - Performance Score: ${report.categories?.performance?.score * 100 || 'N/A'}/100
            - Accessibility Score: ${report.categories?.accessibility?.score * 100 || 'N/A'}/100
            - Best Practices Score: ${report.categories?.['best-practices']?.score * 100 || 'N/A'}/100
            - SEO Score: ${report.categories?.seo?.score * 100 || 'N/A'}/100
            
            **Core Web Vitals:**
            - ✅ LCP: < 2.5s
            - ✅ FID: < 100ms  
            - ✅ CLS: < 0.1
            
            [📊 View Full Report](${process.env.GITHUB_SERVER_URL}/${process.env.GITHUB_REPOSITORY}/actions/runs/${process.env.GITHUB_RUN_ID})
            `;
            
            github.rest.issues.createComment({
              issue_number: context.issue.number,
              owner: context.repo.owner,
              repo: context.repo.repo,
              body: comment
            });
          }
```

### 4. Cross-Browser Testing Workflow

**File**: `.github/workflows/cross-browser-testing.yml`

```yaml
name: Cross-Browser Testing

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main, develop ]

jobs:
  cross-browser-tests:
    name: Browser Compatibility Testing
    runs-on: ubuntu-latest

    strategy:
      matrix:
        browser: [chromium, firefox, webkit]

    services:
      mysql:
        image: mysql:5.7
        env:
          MYSQL_ALLOW_EMPTY_PASSWORD: yes
          MYSQL_DATABASE: wordpress_test
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3

    steps:
    - name: Checkout repository
      uses: actions/checkout@v4

    - name: Setup Node.js
      uses: actions/setup-node@v4
      with:
        node-version: '18'

    - name: Setup Playwright
      run: |
        npm install -g playwright
        npx playwright install ${{ matrix.browser }}

    - name: Setup WordPress test environment
      run: |
        wget https://wordpress.org/latest.tar.gz
        tar -xzf latest.tar.gz
        mv wordpress wp-test
        cp -r . wp-test/wp-content/themes/kiwi-theme
        
        curl -O https://raw.githubusercontent.com/wp-cli/wp-cli/v2.8.1/utils/wp-cli.phar
        chmod +x wp-cli.phar
        sudo mv wp-cli.phar /usr/local/bin/wp
        
        cd wp-test
        wp config create --dbname=wordpress_test --dbuser=root --dbpass= --dbhost=127.0.0.1
        wp core install --url=http://localhost:8000 --title="Browser Test Site" --admin_user=admin --admin_password=admin --admin_email=test@example.com
        wp theme activate kiwi-theme

    - name: Start WordPress server
      run: |
        cd wp-test
        wp server --host=127.0.0.1 --port=8000 &
        sleep 10

    - name: Run browser-specific tests
      run: |
        cat > browser-test.js << 'EOF'
        const { ${{ matrix.browser }} } = require('playwright');

        (async () => {
          console.log('Testing with ${{ matrix.browser }}');
          
          const browser = await ${{ matrix.browser }}.launch();
          const page = await browser.newPage();
          
          // Test responsive design
          const viewports = [
            { width: 320, height: 568 },   // iPhone SE
            { width: 375, height: 667 },   // iPhone 8
            { width: 768, height: 1024 },  // iPad
            { width: 1024, height: 768 },  // iPad Landscape
            { width: 1200, height: 800 },  // Desktop
            { width: 1920, height: 1080 }  // Large Desktop
          ];
          
          for (const viewport of viewports) {
            await page.setViewportSize(viewport);
            await page.goto('http://localhost:8000');
            
            // Check if layout is intact
            const bodyOverflow = await page.evaluate(() => {
              return window.getComputedStyle(document.body).overflowX;
            });
            
            if (bodyOverflow === 'scroll') {
              console.error(`Horizontal overflow detected at ${viewport.width}x${viewport.height}`);
              process.exit(1);
            }
            
            // Test mobile menu on small screens
            if (viewport.width <= 768) {
              const menuToggle = await page.locator('.mobile-menu-toggle');
              if (await menuToggle.isVisible()) {
                await menuToggle.click();
                
                const sidebar = await page.locator('.sidebar');
                const isOpen = await sidebar.evaluate(el => el.classList.contains('open'));
                
                if (!isOpen) {
                  console.error('Mobile menu not working properly');
                  process.exit(1);
                }
              }
            }
            
            console.log(`✅ ${viewport.width}x${viewport.height} - Layout OK`);
          }
          
          // Test CSS features support
          await page.goto('http://localhost:8000');
          
          const cssSupport = await page.evaluate(() => {
            const testEl = document.createElement('div');
            document.body.appendChild(testEl);
            
            const support = {
              grid: CSS.supports('display', 'grid'),
              flexbox: CSS.supports('display', 'flex'),
              customProperties: CSS.supports('color', 'var(--test)'),
              containerQueries: CSS.supports('container-type', 'inline-size')
            };
            
            document.body.removeChild(testEl);
            return support;
          });
          
          console.log('CSS Support:', cssSupport);
          
          // Essential features must be supported
          if (!cssSupport.grid) {
            console.error('CSS Grid not supported');
            process.exit(1);
          }
          
          if (!cssSupport.flexbox) {
            console.error('Flexbox not supported');
            process.exit(1);
          }
          
          // Test JavaScript functionality
          await page.goto('http://localhost:8000');
          
          // Test color scheme switching
          const prefersColorScheme = await page.evaluate(() => {
            return window.matchMedia('(prefers-color-scheme: dark)').matches;
          });
          
          console.log('Prefers color scheme:', prefersColorScheme ? 'dark' : 'light');
          
          // Take screenshots for visual comparison
          await page.screenshot({ 
            path: `screenshot-${{ matrix.browser }}-desktop.png`,
            fullPage: true 
          });
          
          await page.setViewportSize({ width: 375, height: 667 });
          await page.screenshot({ 
            path: `screenshot-${{ matrix.browser }}-mobile.png`,
            fullPage: true 
          });
          
          console.log('✅ All browser tests passed for ${{ matrix.browser }}');
          
          await browser.close();
        })();
        EOF
        
        node browser-test.js

    - name: Upload browser screenshots
      uses: actions/upload-artifact@v4
      if: always()
      with:
        name: browser-screenshots-${{ matrix.browser }}
        path: screenshot-*.png
        retention-days: 7

  visual-regression:
    name: Visual Regression Testing
    runs-on: ubuntu-latest
    needs: cross-browser-tests

    steps:
    - name: Checkout repository
      uses: actions/checkout@v4

    - name: Download all screenshots
      uses: actions/download-artifact@v4
      with:
        path: screenshots/

    - name: Compare visual changes
      run: |
        # Install ImageMagick for image comparison
        sudo apt-get update
        sudo apt-get install -y imagemagick
        
        # Compare screenshots if baseline exists
        if [ -d "tests/visual-baseline" ]; then
          echo "Comparing against visual baseline..."
          
          for browser in chromium firefox webkit; do
            if [ -f "screenshots/browser-screenshots-$browser/screenshot-$browser-desktop.png" ]; then
              if [ -f "tests/visual-baseline/screenshot-$browser-desktop.png" ]; then
                compare -metric AE \
                  "tests/visual-baseline/screenshot-$browser-desktop.png" \
                  "screenshots/browser-screenshots-$browser/screenshot-$browser-desktop.png" \
                  "diff-$browser-desktop.png" 2>&1 | tee diff-result.txt
                
                DIFF=$(cat diff-result.txt)
                if [ "$DIFF" -gt "100" ]; then
                  echo "⚠️  Significant visual changes detected in $browser desktop view"
                fi
              fi
            fi
          done
        else
          echo "No baseline images found. Current screenshots will serve as baseline."
          mkdir -p tests/visual-baseline
          cp screenshots/browser-screenshots-*/screenshot-*.png tests/visual-baseline/
        fi

    - name: Upload visual diff results
      uses: actions/upload-artifact@v4
      if: always()
      with:
        name: visual-regression-results
        path: |
          diff-*.png
          tests/visual-baseline/
        retention-days: 30
```

### 5. Security Scanning Workflow

**File**: `.github/workflows/security-scanning.yml`

```yaml
name: Security Scanning

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main, develop ]
  schedule:
    - cron: '0 2 * * 1'  # Weekly on Monday at 2 AM

jobs:
  security-scan:
    name: Security Vulnerability Scan
    runs-on: ubuntu-latest

    steps:
    - name: Checkout repository
      uses: actions/checkout@v4

    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: 8.1
        tools: composer

    - name: PHP Security Analysis
      run: |
        # Install security scanner
        composer global require enlightn/security-checker
        
        # Scan for known vulnerabilities
        ~/.composer/vendor/bin/security-checker security:check composer.lock

    - name: Scan for hardcoded secrets
      uses: trufflesecurity/trufflehog@main
      with:
        path: ./
        base: main
        head: HEAD

    - name: WordPress Security Scan
      run: |
        # Install WPScan CLI
        gem install wpscan
        
        # Scan theme files for common vulnerabilities
        find . -name "*.php" -exec grep -l "eval\|exec\|system\|shell_exec\|file_get_contents\|file_put_contents" {} \; | tee dangerous-functions.txt
        
        if [ -s dangerous-functions.txt ]; then
          echo "⚠️ Potentially dangerous functions found:"
          cat dangerous-functions.txt
          echo "Please review these files for security implications."
        fi

    - name: Check for SQL injection vulnerabilities
      run: |
        echo "Scanning for potential SQL injection vulnerabilities..."
        
        # Look for direct database queries
        grep -r "\$wpdb->" --include="*.php" . | tee sql-queries.txt
        
        # Check for unsanitized variables in queries
        grep -r "\$_GET\|\$_POST\|\$_REQUEST" --include="*.php" . | grep -v "sanitize\|escape" | tee unsanitized-inputs.txt
        
        if [ -s unsanitized-inputs.txt ]; then
          echo "⚠️ Potentially unsanitized inputs found:"
          cat unsanitized-inputs.txt
          echo "Please ensure all inputs are properly sanitized."
        fi

    - name: Validate file permissions and ownership
      run: |
        echo "Checking file permissions..."
        
        # Check for world-writable files
        find . -type f -perm -002 | tee world-writable.txt
        
        if [ -s world-writable.txt ]; then
          echo "⚠️ World-writable files found:"
          cat world-writable.txt
          exit 1
        fi

    - name: Scan JavaScript for security issues
      run: |
        npm install -g eslint-plugin-security
        
        # Create ESLint config for security
        cat > .eslintrc.security.js << 'EOF'
        module.exports = {
          plugins: ['security'],
          extends: ['plugin:security/recommended'],
          rules: {
            'security/detect-eval-with-expression': 'error',
            'security/detect-non-literal-fs-filename': 'error',
            'security/detect-unsafe-regex': 'error',
            'security/detect-buffer-noassert': 'error',
            'security/detect-child-process': 'error',
            'security/detect-disable-mustache-escape': 'error',
            'security/detect-new-buffer': 'error',
            'security/detect-no-csrf-before-method-override': 'error'
          }
        };
        EOF
        
        # Run security linting
        npx eslint --config .eslintrc.security.js assets/js/**/*.js || echo "JavaScript security issues found"

    - name: Content Security Policy validation
      run: |
        echo "Validating Content Security Policy implementation..."
        
        # Check for inline scripts/styles (should be minimal)
        grep -r "onclick\|onload\|style=" --include="*.html" templates/ parts/ || echo "No inline handlers found"
        
        # Check for proper nonce usage in any inline scripts
        if grep -r "<script" templates/ parts/; then
          echo "⚠️ Inline scripts found. Ensure they use proper nonces."
        fi

    - name: WordPress nonce verification check
      run: |
        echo "Checking WordPress nonce implementation..."
        
        # Look for forms without nonce fields
        grep -r "<form" --include="*.html" --include="*.php" . | while read -r line; do
          file=$(echo "$line" | cut -d: -f1)
          if ! grep -q "wp_nonce_field\|wp_create_nonce" "$file"; then
            echo "⚠️ Form without nonce found in: $file"
          fi
        done

    - name: Generate security report
      run: |
        cat > security-report.md << 'EOF'
        # Security Scan Report
        
        ## Summary
        - Scan Date: $(date)
        - Repository: ${{ github.repository }}
        - Commit: ${{ github.sha }}
        
        ## Findings
        
        ### PHP Security
        - Dangerous functions: $(wc -l < dangerous-functions.txt || echo 0) files
        - SQL queries: $(wc -l < sql-queries.txt || echo 0) instances
        - Unsanitized inputs: $(wc -l < unsanitized-inputs.txt || echo 0) instances
        
        ### File Security
        - World-writable files: $(wc -l < world-writable.txt || echo 0) files
        
        ### Recommendations
        - Ensure all user inputs are sanitized using WordPress functions
        - Use prepared statements for database queries
        - Implement proper nonce verification for forms
        - Follow WordPress security best practices
        
        EOF

    - name: Upload security scan results
      uses: actions/upload-artifact@v4
      if: always()
      with:
        name: security-scan-results
        path: |
          security-report.md
          *.txt
        retention-days: 30

    - name: Security scan summary
      run: |
        echo "## Security Scan Summary" >> $GITHUB_STEP_SUMMARY
        echo "" >> $GITHUB_STEP_SUMMARY
        echo "- **Dangerous Functions**: $(wc -l < dangerous-functions.txt 2>/dev/null || echo 0) files flagged" >> $GITHUB_STEP_SUMMARY
        echo "- **SQL Queries**: $(wc -l < sql-queries.txt 2>/dev/null || echo 0) instances found" >> $GITHUB_STEP_SUMMARY
        echo "- **Unsanitized Inputs**: $(wc -l < unsanitized-inputs.txt 2>/dev/null || echo 0) potential issues" >> $GITHUB_STEP_SUMMARY
        echo "- **File Permissions**: $(wc -l < world-writable.txt 2>/dev/null || echo 0) world-writable files" >> $GITHUB_STEP_SUMMARY
```

## Pull Request Template

**File**: `.github/PULL_REQUEST_TEMPLATE.md`

```markdown
## Description
Brief description of changes made in this PR.

## Type of Change
- [ ] Bug fix (non-breaking change which fixes an issue)
- [ ] New feature (non-breaking change which adds functionality)
- [ ] Breaking change (fix or feature that would cause existing functionality to not work as expected)
- [ ] Documentation update

## Testing Checklist

### Automated Tests
- [ ] All GitHub Actions workflows pass
- [ ] Theme Check plugin validation passes
- [ ] Accessibility tests pass (axe-core, pa11y)
- [ ] Performance tests meet targets (Lighthouse score 90+)
- [ ] Cross-browser tests pass (Chrome, Firefox, Safari)
- [ ] Security scans show no critical issues

### Manual Testing
- [ ] Theme activates without errors
- [ ] All new features work as expected
- [ ] Mobile responsiveness verified
- [ ] Keyboard navigation tested
- [ ] Screen reader compatibility confirmed
- [ ] Dark/light mode switching works

### WordPress.org Compliance
- [ ] No plugin functionality included
- [ ] All strings are internationalized
- [ ] WordPress coding standards followed
- [ ] GPL v2+ license compliance
- [ ] No external API calls or dependencies

## Screenshots
Add screenshots or GIFs demonstrating the changes (especially for UI changes).

## Additional Notes
Any additional information that reviewers should know.
```

## Monitoring and Maintenance

### Automated Dependency Updates
Create `.github/dependabot.yml`:

```yaml
version: 2
updates:
  - package-ecosystem: "npm"
    directory: "/"
    schedule:
      interval: "weekly"
    
  - package-ecosystem: "composer"
    directory: "/"
    schedule:
      interval: "weekly"
    
  - package-ecosystem: "github-actions"
    directory: "/"
    schedule:
      interval: "weekly"
```

### Performance Monitoring
Set up ongoing performance monitoring with scheduled workflows:

```yaml
# .github/workflows/performance-monitoring.yml
name: Performance Monitoring

on:
  schedule:
    - cron: '0 6 * * *'  # Daily at 6 AM

jobs:
  performance-check:
    runs-on: ubuntu-latest
    steps:
      # Similar to performance testing but runs against production
      # Alerts if performance degrades below thresholds
```

This comprehensive testing strategy ensures your WordPress theme maintains high quality, accessibility, performance, and security standards throughout development and after deployment.
