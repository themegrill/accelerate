import { expect, type Page, type TestInfo } from "@playwright/test";

/**
 * Shared helpers for specs that need wp-admin. One worker runs at a time
 * (playwright.config.ts), because several specs change site state and put it
 * back afterwards.
 */

export const adminUser = process.env.TGQA_ADMIN_USER ?? "";
export const adminPass = process.env.TGQA_ADMIN_PASS ?? "";

/** Skip, with a reason in the report, when no admin credentials were supplied. */
export function requireAdmin(testInfo: TestInfo): void {
  testInfo.skip(
    !adminUser || !adminPass,
    "needs TGQA_ADMIN_USER / TGQA_ADMIN_PASS in .themegrill-qa/.env.local",
  );
}

export async function login(page: Page): Promise<void> {
  await page.goto("/wp-login.php");
  await page.locator("#user_login").fill(adminUser);
  await page.locator("#user_pass").fill(adminPass);
  await page.locator("#wp-submit").click();
  await expect(page.locator("#wpadminbar")).toBeVisible();
}

/** A REST nonce for the logged-in session, so page.request can call wp/v2. */
export async function restNonce(page: Page): Promise<string> {
  const res = await page.request.get("/wp-admin/admin-ajax.php?action=rest-nonce");
  expect(res.ok(), "could not get a REST nonce — is the admin logged in?").toBeTruthy();
  return (await res.text()).trim();
}

/** Create a published page through the REST API and return its id and link. */
export async function createPage(
  page: Page,
  title: string,
  content: string,
): Promise<{ id: number; link: string }> {
  const nonce = await restNonce(page);
  const res = await page.request.post("/?rest_route=/wp/v2/pages", {
    headers: { "X-WP-Nonce": nonce },
    data: { title, content, status: "publish" },
  });
  expect(res.ok(), `creating the test page failed: HTTP ${res.status()}`).toBeTruthy();
  const body = await res.json();
  return { id: body.id, link: body.link };
}

// POST with a method override rather than DELETE: some hosts (this Local site's
// nginx among them) answer a bare DELETE with 405 before WordPress sees it.
export async function deletePage(page: Page, id: number): Promise<void> {
  const nonce = await restNonce(page);
  const res = await page.request.post(`/?rest_route=/wp/v2/pages/${id}&force=true`, {
    headers: { "X-WP-Nonce": nonce, "X-HTTP-Method-Override": "DELETE" },
  });
  expect(res.ok(), `deleting test page ${id} failed: HTTP ${res.status()}`).toBeTruthy();
}
