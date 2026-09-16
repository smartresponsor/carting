const { test, expect } = require('@playwright/test');

test('Carting browser test runner is executable', async () => {
  const cartWord = 'cart';

  expect(cartWord).toBe('cart');
});
