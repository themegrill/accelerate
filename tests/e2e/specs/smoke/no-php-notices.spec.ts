import { test, expect } from "@playwright/test";

/**
 * @area homepage
 * @tier fresh
 * @guards themegrill/accelerate-pro#21
 * @source themegrill/radiate#73 (same fix); themegrill/spacious#151
 * @why accelerate_scripts_styles_method() registered html5shiv with
 *      wp_script_add_data( 'html5shiv', 'conditional', 'lte IE 8' ), which
 *      WordPress 6.9+ deprecates, so every front-end page printed a
 *      "Deprecated: WP_Dependencies->add_data()" notice when WP_DEBUG_DISPLAY
 *      is on. Only observable where debug display is enabled; on a site with it
 *      off this passes regardless. Asserts no PHP notice markup anywhere in the
 *      response (it is printed inside <head>), not the absence of html5shiv.
 */
test("front page prints no PHP notices or deprecations @homepage @smoke @fresh", async ({ page }) => {
  // Read the raw response: PHP prints the notice while wp_head() runs, before
  // <body>, so a body locator would miss it.
  const response = await page.goto("/");
  const html = await response!.text();
  expect(html).not.toMatch(/(?:^|>|\s)(?:<b>)?(?:Deprecated|Notice|Warning|Fatal error)(?:<\/b>)?:\s/);
});
