import { test, expect } from "@playwright/test";

/**
 * @area homepage
 * @tier fresh
 * @source accelerate-free-pro-senior-dev-audit.html — Final Developer Summary #1/#8
 * @why The 2026-09-21 audit live-confirmed a clean console and exactly one h1
 *      per template on the homepage. This is the baseline health check the
 *      rest of the suite assumes still holds.
 */
test("homepage renders with no console errors and exactly one h1 @homepage @smoke @fresh", async ({
  page,
}) => {
  const consoleErrors: string[] = [];
  page.on("console", (msg) => {
    if (msg.type() === "error") consoleErrors.push(msg.text());
  });

  const response = await page.goto("/");
  expect(response?.ok()).toBeTruthy();

  await expect(page.locator("h1")).toHaveCount(1);
  expect(
    consoleErrors,
    `unexpected console errors: ${consoleErrors.join("; ")}`,
  ).toEqual([]);
});
