import { test, expect } from "@playwright/test";

/**
 * @area mobile-menu
 * @tier fresh
 * @guards themegrill/accelerate-pro#101
 * @source themegrill/accelerate-pro#101 (reported by subin-shk)
 * @why The sub-toggle handler in js/navigation.js toggled `.fa-caret-right`,
 *      which the icon never has (it is created as fa-caret-down), so the caret
 *      never changed. A submenu is added to the first menu item before the
 *      script runs, so this does not depend on the site's menu. Checks the
 *      glyph actually drawn, not just the classes.
 */
test("the submenu caret turns up when a mobile submenu opens and back down when it closes @mobile-menu @fresh", async ({
  page,
}) => {
  // Registered before jQuery's ready handler, so the theme sees a real parent item.
  await page.addInitScript(() =>
    document.addEventListener("DOMContentLoaded", () => {
      const item = document.querySelector("#site-navigation ul li");
      if (!item) return;
      item.classList.add("menu-item-has-children");
      item.insertAdjacentHTML("beforeend", '<ul class="sub-menu"><li><a href="#tgqa-child">Injected child</a></li></ul>');
    }),
  );
  await page.setViewportSize({ width: 375, height: 812 });
  await page.goto("/");

  await page.locator("#site-navigation .menu-toggle").click();
  const toggle = page.locator("#site-navigation .sub-toggle").first();
  const icon = toggle.locator("i.fa");
  const submenu = toggle.locator("xpath=../ul").first();
  const glyph = () => icon.evaluate((el) => getComputedStyle(el, "::before").content.replace(/["']/g, "").codePointAt(0)?.toString(16));

  await expect(icon).toHaveClass(/\bfa-caret-down\b/);
  expect(await glyph(), "closed: caret-down glyph").toBe("f0d7");

  await toggle.click();
  await expect(submenu).toBeVisible();
  await expect(icon).toHaveClass(/\bfa-caret-up\b/);
  await expect(icon).not.toHaveClass(/\bfa-caret-down\b/);
  expect(await glyph(), "open: caret-up glyph").toBe("f0d8");

  await toggle.click();
  await expect(submenu).toBeHidden();
  await expect(icon).toHaveClass(/\bfa-caret-down\b/);
  await expect(icon).not.toHaveClass(/\bfa-caret-up\b/);
  expect(await glyph(), "closed again: caret-down glyph").toBe("f0d7");
});

/**
 * @area mobile-menu
 * @tier fresh
 * @guards themegrill/accelerate-pro#53
 * @source themegrill/accelerate-pro#53; themegrill/radiate-pro#107 (same fix, same spec)
 * @why The touch-submenu handler in js/navigation.js called
 *      container.querySelectorAll() with no null check, so any page without
 *      #site-navigation threw "Cannot read properties of null". On a real site
 *      that is the Legacy Widget preview on Appearance > Widgets; here the nav
 *      is removed while the page parses, before the footer script runs.
 */
test("a page without the primary navigation throws no script error @mobile-menu @fresh", async ({ page }) => {
  const errors: string[] = [];
  page.on("pageerror", (e) => errors.push(e.message));
  await page.addInitScript(() => {
    const original = Document.prototype.getElementById;
    // The observer uses the unwrapped lookup, so only the page's own lookups are recorded below.
    new MutationObserver(() => original.call(document, "site-navigation")?.remove()).observe(document, {
      childList: true,
      subtree: true,
    });
    // Record what each lookup of the nav returned, so the test proves the script ran with it already gone.
    const lookups: boolean[] = [];
    (window as unknown as { tgqaNavLookups: boolean[] }).tgqaNavLookups = lookups;
    Document.prototype.getElementById = function (id: string) {
      const found = original.call(this, id);
      if ("site-navigation" === id) lookups.push(null === found);
      return found;
    };
  });

  await page.goto("/");
  await page.waitForLoadState("networkidle");
  const lookups = await page.evaluate(() => (window as unknown as { tgqaNavLookups: boolean[] }).tgqaNavLookups);
  expect(lookups.length, "navigation.js never looked up #site-navigation").toBeGreaterThan(0);
  expect(lookups.every(Boolean), "#site-navigation still existed when the script looked it up").toBe(true);
  expect(errors, "script errors on a page without #site-navigation").toEqual([]);
});
