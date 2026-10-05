import { test, expect } from "@playwright/test";

/**
 * @area homepage
 * @tier fresh
 * @guards themegrill/accelerate-pro#93 themegrill/accelerate-pro#97
 * @source themegrill/accelerate-pro#111 (same fix, same spec)
 * @why WordPress paints the custom background on body.custom-background, but in
 *      the default Wide layout #page is full-width and white, so it covered the
 *      whole body: the colour and image saved and printed, yet never showed in
 *      the preview or on the front end. Boxed layout always worked. The values
 *      are only previewed, never saved.
 */
test("the custom background shows in the Wide layout @homepage @fresh", async ({ page }) => {
  const user = process.env.TGQA_ADMIN_USER ?? "";
  const pass = process.env.TGQA_ADMIN_PASS ?? "";
  test.skip(!user || !pass, "needs TGQA_ADMIN_USER / TGQA_ADMIN_PASS");
  test.setTimeout(120_000);

  await page.goto("/wp-login.php");
  await page.locator("#user_login").fill(user);
  await page.locator("#user_pass").fill(pass);
  await page.locator("#wp-submit").click();
  await expect(page.locator("#wpadminbar")).toBeVisible();

  await page.goto("/wp-admin/customize.php");
  await expect(page.locator("#customize-theme-controls")).toBeVisible({ timeout: 90_000 });
  const preview = page.frameLocator("#customize-preview iframe").last();
  await expect(preview.locator("#page")).toBeVisible({ timeout: 60_000 });
  test.skip(
    !(await preview.locator("body.wide").count()),
    "the site uses the Boxed layout, where the body background was never covered",
  );

  await page.evaluate(() => (window as any).wp.customize("background_color").set("#ff0000"));
  await expect(preview.locator("body")).toHaveCSS("background-color", "rgb(255, 0, 0)");
  await expect(preview.locator("#page")).toHaveCSS("background-color", "rgba(0, 0, 0, 0)");

  // An image on its own, no colour. Any URL works: only the computed style is read.
  await page.evaluate(() => {
    const api = (window as any).wp.customize;
    api("background_color").set("");
    api("background_image").set(`${api.settings.url.home}/wp-content/themes/accelerate/screenshot.jpg`);
  });
  await expect(preview.locator("body")).toHaveCSS("background-image", /accelerate\/screenshot\.jpg/);
  await expect(preview.locator("#page")).toHaveCSS("background-color", "rgba(0, 0, 0, 0)");
});
