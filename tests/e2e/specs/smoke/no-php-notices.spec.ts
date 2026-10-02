import { test, expect } from "@playwright/test";

/**
 * @area assets
 * @tier fresh
 * @guards themegrill/accelerate-pro#21
 * @source themegrill/radiate#73 (same fix); themegrill/spacious#151
 * @why accelerate_scripts_styles_method() registered html5shiv with
 *      wp_script_add_data( 'html5shiv', 'conditional', 'lte IE 8' ), which
 *      WordPress 6.9+ deprecates, so every front-end page printed a
 *      "Deprecated: WP_Dependencies->add_data()" notice when WP_DEBUG_DISPLAY
 *      is on. Only observable where debug display is enabled; on a site with it
 *      off this passes regardless. Asserts no PHP notice markup anywhere in the
 *      response (it is printed inside <head>). #57 then skipped the
 *      conditional on 6.9+ but kept the enqueue, so html5shiv.js loaded for
 *      every visitor; the response must not reference it either.
 */
test("front page prints no PHP notices or deprecations @assets @smoke @fresh", async ({ page }) => {
  // Read the raw response: PHP prints the notice while wp_head() runs, before
  // <body>, so a body locator would miss it.
  const response = await page.goto("/");
  expect(response?.ok(), `homepage returned HTTP ${response?.status()}`).toBeTruthy();
  const html = await response!.text();
  expect(html).not.toMatch(/(?:^|>|\s)(?:<b>)?(?:Deprecated|Notice|Warning|Fatal error)(?:<\/b>)?:\s/);
  expect(html, "IE-only html5shiv.js is printed for every visitor").not.toContain("/js/html5shiv.js");
});
