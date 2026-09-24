import { test, expect } from "@playwright/test";

/**
 * @area mobile-menu
 * @tier fresh
 * @source accelerate-free-pro-senior-dev-audit.html#ACCELERATE-021
 * @why Live-confirmed 2026-09-21 on both Free and Pro: the toggle is
 *      `container.getElementsByTagName('h3')[0]` (js/navigation.js:15) with
 *      an onclick-only handler (js/navigation.js:32) and no tabindex, role,
 *      or keydown handling — a plain <h3> is not in the default Tab order.
 *      This test encodes the desired, keyboard-operable behavior and is
 *      expected to fail (never reached by Tab) until ACCELERATE-021 is fixed.
 */
// Quarantined: ACCELERATE-021 is not fixed yet. Drop `.fixme` in the PR that fixes it.
test.fixme("keyboard users can reach and open the mobile menu at narrow viewports @mobile-menu @accessibility @fresh", async ({
  page,
}) => {
  await page.setViewportSize({ width: 375, height: 812 });
  await page.goto("/");

  const toggle = page.locator("#site-navigation h3.menu-toggle");
  await expect(toggle).toBeVisible();

  // A real Tab walk from page load, the same method the audit used, so an
  // element that only *looks* interactive but isn't actually focusable
  // genuinely fails this rather than being coaxed into passing.
  let reached = false;
  for (let i = 0; i < 25 && !reached; i++) {
    await page.keyboard.press("Tab");
    reached = await toggle.evaluate((el) => document.activeElement === el);
  }
  expect(
    reached,
    "the mobile menu toggle was never reached by keyboard Tab from page load (ACCELERATE-021)",
  ).toBe(true);

  const nav = page.locator("#site-navigation");
  const classBefore = await nav.getAttribute("class");
  await page.keyboard.press("Enter");
  const classAfter = await nav.getAttribute("class");
  expect(
    classAfter,
    "activating the toggle via keyboard should change the nav container's open/closed state",
  ).not.toBe(classBefore);
});
