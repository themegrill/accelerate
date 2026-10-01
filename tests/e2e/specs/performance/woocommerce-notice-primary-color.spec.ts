import { test, expect } from "@playwright/test";

/**
 * @area assets
 * @tier fresh
 * @guards themegrill/accelerate-pro#68 (free side)
 * @source themegrill/accelerate#85
 * @why accelerate_custom_css() printed `;},.woocommerce .woocommerce-message {`
 *      for a non-default primary colour. The stray comma makes the selector
 *      invalid, so the browser drops the whole rule and the notice border stays
 *      WooCommerce green. Asserts the rule is actually parsed (CSSOM), which
 *      needs no product or cart. The colour is only previewed, never saved.
 */
test("the primary colour reaches the WooCommerce notice border rule @assets @fresh", async ({ page }) => {
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
  // The setting refreshes the preview, which then prints accelerate_custom_css().
  await page.evaluate(() => (window as any).wp.customize("accelerate[accelerate_primary_color]").set("#e91e63"));

  const pink = "rgb(233, 30, 99)";
  await expect
    .poll(
      async () => {
        const frame = await (await page.locator("#customize-preview iframe").last().elementHandle())?.contentFrame();
        return frame?.evaluate((colour) => {
          const found = { customCssLoaded: false, noticeBorder: false };
          for (const sheet of Array.from(document.styleSheets)) {
            let rules: CSSRuleList;
            try {
              rules = sheet.cssRules;
            } catch {
              continue;
            }
            for (const rule of Array.from(rules) as CSSStyleRule[]) {
              if (rule.selectorText?.includes(".woocommerce span.onsale") && rule.style.backgroundColor === colour) found.customCssLoaded = true;
              if (rule.selectorText === ".woocommerce .woocommerce-message" && rule.style.borderTopColor === colour) found.noticeBorder = true;
            }
          }
          return found;
        }, pink);
      },
      { timeout: 60_000, message: "the notice border rule was not parsed with the primary colour" },
    )
    .toEqual({ customCssLoaded: true, noticeBorder: true });
});
