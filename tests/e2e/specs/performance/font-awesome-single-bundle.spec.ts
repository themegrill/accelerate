import { test, expect } from "@playwright/test";

/**
 * @area performance
 * @tier fresh
 * @source accelerate-free-pro-senior-dev-audit.html#ACCELERATE-004
 * @why inc/functions.php:76-113 enqueues 5 overlapping Font Awesome
 *      stylesheets (v4-shims, all, solid, regular, brands) unconditionally
 *      on every page, even though `all.css` already contains solid+regular+
 *      brands. Lighthouse measured this + Google Fonts as ~two-thirds of the
 *      homepage's byte weight. Only one `/fontawesome/css/*.css` request
 *      should be needed.
 */
test("only one Font Awesome stylesheet loads per page @performance @fresh", async ({
  page,
}) => {
  const fontAwesomeRequests: string[] = [];
  page.on("request", (req) => {
    if (/\/fontawesome\/css\//.test(req.url())) {
      fontAwesomeRequests.push(req.url());
    }
  });

  await page.goto("/");
  await page.waitForLoadState("networkidle");

  expect(
    fontAwesomeRequests.length,
    `expected at most 1 Font Awesome stylesheet request, got ${fontAwesomeRequests.length}: ${fontAwesomeRequests.join(", ")} (ACCELERATE-004)`,
  ).toBeLessThanOrEqual(1);
});
