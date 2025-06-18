// @ts-check
const { test, expect } = require('@playwright/test');
const { injectAxe, checkA11y, getViolations } = require('@axe-core/playwright');

/**
 * Accessibility tests using axe-core
 * 
 * These tests validate WCAG 2.1 AA compliance across different pages and components
 */

test.describe('Theme Accessibility Tests', () => {
  
  test.beforeEach(async ({ page }) => {
    // Navigate to the homepage and inject axe-core
    await page.goto('/');
    await injectAxe(page);
  });

  test('Homepage should have no accessibility violations', async ({ page }) => {
    await checkA11y(page, null, {
      rules: {
        // Configure axe-core rules for WCAG 2.1 AA compliance
        'color-contrast': { enabled: true },
        'keyboard-navigation': { enabled: true },
        'focus-management': { enabled: true },
        'aria-usage': { enabled: true },
        'semantic-structure': { enabled: true }
      }
    });
  });

  test('Blog archive page should be accessible', async ({ page }) => {
    await page.goto('/blog/');
    await injectAxe(page);
    
    await checkA11y(page, null, {
      tags: ['wcag2a', 'wcag2aa', 'wcag21aa']
    });
  });

  test('Single blog post should be accessible', async ({ page }) => {
    // Navigate to a sample post (assumes WordPress has sample content)
    await page.goto('/hello-world/');
    await injectAxe(page);
    
    await checkA11y(page, null, {
      tags: ['wcag2a', 'wcag2aa', 'wcag21aa']
    });
  });

  test('Navigation should be keyboard accessible', async ({ page }) => {
    // Test keyboard navigation through main navigation
    await page.keyboard.press('Tab');
    
    // Check that skip link is focused first
    const skipLink = page.locator('.skip-link');
    if (await skipLink.count() > 0) {
      await expect(skipLink).toBeFocused();
    }
    
    // Continue tabbing through navigation
    await page.keyboard.press('Tab');
    
    // Verify focus indicators are visible
    const focusedElement = page.locator(':focus');
    await expect(focusedElement).toBeVisible();
  });

  test('Skip link should work correctly', async ({ page }) => {
    // Press Tab to focus skip link
    await page.keyboard.press('Tab');
    
    const skipLink = page.locator('.skip-link');
    if (await skipLink.count() > 0) {
      // Activate skip link
      await page.keyboard.press('Enter');
      
      // Verify main content is focused
      const mainContent = page.locator('main, [role="main"]');
      await expect(mainContent).toBeFocused();
    }
  });

  test('Color contrast meets WCAG AA standards', async ({ page }) => {
    await checkA11y(page, null, {
      rules: {
        'color-contrast': { enabled: true }
      }
    });
    
    // Get any color contrast violations
    const violations = await getViolations(page, null, {
      rules: {
        'color-contrast': { enabled: true }
      }
    });
    
    // Assert no color contrast violations
    expect(violations.filter(v => v.id === 'color-contrast')).toHaveLength(0);
  });

  test('Images should have appropriate alt text', async ({ page }) => {
    await checkA11y(page, null, {
      rules: {
        'image-alt': { enabled: true }
      }
    });
  });

  test('Form elements should have proper labels', async ({ page }) => {
    // Navigate to search or contact form if available
    const searchForm = page.locator('form[role="search"], .wp-block-search');
    
    if (await searchForm.count() > 0) {
      await checkA11y(page, null, {
        rules: {
          'label': { enabled: true },
          'aria-input-field-name': { enabled: true }
        }
      });
    }
  });

  test('Headings should have proper hierarchy', async ({ page }) => {
    await checkA11y(page, null, {
      rules: {
        'heading-order': { enabled: true }
      }
    });
    
    // Additional check for h1 presence
    const h1Elements = page.locator('h1');
    await expect(h1Elements).toHaveCount(1);
  });

  test('Links should have descriptive text', async ({ page }) => {
    await checkA11y(page, null, {
      rules: {
        'link-name': { enabled: true }
      }
    });
  });

  test('ARIA landmarks should be present', async ({ page }) => {
    // Check for required landmarks
    await expect(page.locator('[role="banner"], header')).toHaveCount(1);
    await expect(page.locator('[role="main"], main')).toHaveCount(1);
    await expect(page.locator('[role="contentinfo"], footer')).toHaveCount(1);
    
    // Check for navigation landmark
    const navElements = page.locator('[role="navigation"], nav');
    await expect(navElements).toHaveCountGreaterThan(0);
  });

  test('Mobile accessibility should be maintained', async ({ page, browserName }) => {
    // Set mobile viewport
    await page.setViewportSize({ width: 375, height: 667 });
    
    await checkA11y(page, null, {
      tags: ['wcag2a', 'wcag2aa', 'wcag21aa']
    });
    
    // Test touch targets are appropriately sized (minimum 44px)
    const clickableElements = page.locator('a, button, input[type="submit"], input[type="button"]');
    const count = await clickableElements.count();
    
    for (let i = 0; i < count; i++) {
      const element = clickableElements.nth(i);
      const box = await element.boundingBox();
      
      if (box) {
        // Check minimum touch target size (44px recommended by WCAG)
        expect(box.width).toBeGreaterThanOrEqual(24); // Minimum for small elements
        expect(box.height).toBeGreaterThanOrEqual(24);
      }
    }
  });

  test('Dark mode should maintain accessibility', async ({ page }) => {
    // Check if theme supports dark mode
    const darkModeToggle = page.locator('[data-theme-toggle], .dark-mode-toggle');
    
    if (await darkModeToggle.count() > 0) {
      // Toggle to dark mode
      await darkModeToggle.click();
      
      // Wait for theme change
      await page.waitForTimeout(500);
      
      // Run accessibility checks in dark mode
      await checkA11y(page, null, {
        rules: {
          'color-contrast': { enabled: true }
        }
      });
    }
  });
});