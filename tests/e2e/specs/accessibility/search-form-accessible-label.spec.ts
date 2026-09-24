import { test, expect } from "@playwright/test";

/**
 * @area accessibility
 * @tier fresh
 * @source accelerate-free-pro-senior-dev-audit.html#ACCELERATE-011
 * @why searchform.php:11 has only a `placeholder` attribute, no <label> or
 *      aria-label — placeholder text is not a valid accessible-name
 *      substitute per WCAG. Tested against 404.php, which always calls
 *      get_search_form() regardless of widget placement or login state
 *      (unlike the homepage widget area, see ACCELERATE-023).
 */
// Quarantined: ACCELERATE-011 is not fixed yet. Drop `.fixme` in the PR that fixes it.
test.fixme("the search form input has an accessible name @accessibility @search @fresh", async ({
  page,
}) => {
  await page.goto("/this-page-does-not-exist-tgqa/");
  const searchInput = page.locator('#content #search-form input[name="s"]');
  await expect(searchInput).toBeVisible();

  const accessibleName = await searchInput.evaluate((el) => {
    const input = el as HTMLInputElement;
    return input.labels && input.labels.length > 0
      ? input.labels[0].textContent
      : input.getAttribute("aria-label");
  });

  expect(
    accessibleName,
    "search input needs a <label> or aria-label — a placeholder alone is not a valid accessible name (ACCELERATE-011)",
  ).toBeTruthy();
});
