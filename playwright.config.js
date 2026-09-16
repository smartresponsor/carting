const { defineConfig } = require('@playwright/test');

module.exports = defineConfig({
  testDir: './tests/Browser',
  forbidOnly: true,
  retries: 0,
  workers: 1,
  reporter: 'list',
});
