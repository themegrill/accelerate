import { expect, type Page, type TestInfo } from "@playwright/test";

/**
 * Shared helpers for specs that need wp-admin. Specs that change site state
 * put it back in a finally block.
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
  // The dashboard can take several seconds to load on a busy site.
  await expect(page.locator("#wpadminbar")).toBeVisible({ timeout: 30_000 });
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

/** Remove a widget through the REST API (method override, as for pages above). */
export async function removeWidget(page: Page, id: string): Promise<void> {
  const res = await page.request.post(`/?rest_route=/wp/v2/widgets/${id}&force=true`, {
    headers: { "X-WP-Nonce": await restNonce(page), "X-HTTP-Method-Override": "DELETE" },
  });
  expect(res.ok(), `removing test widget ${id} failed: HTTP ${res.status()}`).toBeTruthy();
}

/**
 * Add a classic (legacy) widget to a sidebar through the REST API, the way the block Widgets
 * editor does: encode the form, create the widget, then place it (over HTTP a new widget
 * lands in Inactive Widgets). `fields` are the widget form's field names and values.
 */
export async function addLegacyWidget(
  page: Page,
  idBase: string,
  sidebar: string,
  fields: Record<string, string | number>,
): Promise<string> {
  const headers = { "X-WP-Nonce": await restNonce(page) };
  const form = Object.entries(fields)
    .map(([k, v]) => `widget-${idBase}[1][${k}]=${encodeURIComponent(String(v))}`)
    .join("&");
  const encoded = await page.request.post(`/?rest_route=/wp/v2/widget-types/${idBase}/encode`, { headers, data: { form_data: form } });
  expect(encoded.ok(), `encoding the ${idBase} widget failed: HTTP ${encoded.status()}`).toBeTruthy();
  const created = await page.request.post("/?rest_route=/wp/v2/widgets", {
    headers,
    data: { id_base: idBase, sidebar, instance: (await encoded.json()).instance },
  });
  expect(created.ok(), `creating the ${idBase} widget failed: HTTP ${created.status()}`).toBeTruthy();
  const id: string = (await created.json()).id;
  try {
    const placed = await page.request.post(`/?rest_route=/wp/v2/widgets/${id}`, {
      headers: { ...headers, "X-HTTP-Method-Override": "PUT" },
      data: { sidebar },
    });
    expect(placed.ok(), `placing the ${idBase} widget failed: HTTP ${placed.status()}`).toBeTruthy();
  } catch (error) {
    // The caller never gets the id, so its cleanup cannot remove this widget.
    // A cleanup failure must not hide the placement error, which is the real cause.
    await removeWidget(page, id).catch(() => {});
    throw error;
  }
  return id;
}
