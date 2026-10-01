import { test, expect, type Page } from "@playwright/test";

const adminUser = process.env.TGQA_ADMIN_USER ?? "";
const adminPass = process.env.TGQA_ADMIN_PASS ?? "";

async function nonce(page: Page): Promise<string> {
  return (await (await page.request.get("/wp-admin/admin-ajax.php?action=rest-nonce")).text()).trim();
}

/**
 * Add a TG: Featured Widget to the right sidebar through the REST API, the way the block
 * Widgets editor does for a legacy widget: encode the form, create it, then place it.
 */
async function addFeaturedWidget(page: Page, pageIds: number[]): Promise<string> {
  const headers = { "X-WP-Nonce": await nonce(page) };
  const field = (k: string, v: string | number) => `widget-accelerate_recent_work_widget[1][${k}]=${encodeURIComponent(String(v))}`;
  const form = [field("title", "TGQA featured"), field("text", "")];
  for (let i = 0; i < 4; i++) form.push(field(`page_id${i}`, pageIds[i] ?? 0));

  const encoded = await page.request.post("/?rest_route=/wp/v2/widget-types/accelerate_recent_work_widget/encode", { headers, data: { form_data: form.join("&") } });
  expect(encoded.ok(), `encoding the widget failed: HTTP ${encoded.status()}`).toBeTruthy();
  const created = await page.request.post("/?rest_route=/wp/v2/widgets", {
    headers,
    data: { id_base: "accelerate_recent_work_widget", sidebar: "accelerate_right_sidebar", instance: (await encoded.json()).instance },
  });
  expect(created.ok(), `creating the widget failed: HTTP ${created.status()}`).toBeTruthy();
  const id: string = (await created.json()).id;
  // Over HTTP the new widget lands in Inactive Widgets; assigning the sidebar is a separate update.
  const placed = await page.request.post(`/?rest_route=/wp/v2/widgets/${id}`, { headers: { ...headers, "X-HTTP-Method-Override": "PUT" }, data: { sidebar: "accelerate_right_sidebar" } });
  expect(placed.ok(), `placing the widget failed: HTTP ${placed.status()}`).toBeTruthy();
  return id;
}

// POST with a method override: some hosts answer a bare DELETE with 405 before WordPress sees it.
async function remove(page: Page, route: string): Promise<void> {
  await page.request.post(`/?rest_route=${route}&force=true`, { headers: { "X-WP-Nonce": await nonce(page), "X-HTTP-Method-Override": "DELETE" } });
}

/**
 * @area homepage
 * @tier fresh
 * @guards themegrill/accelerate-pro#103
 * @source themegrill/accelerate-pro#103 (reported by subin-shk)
 * @why A TG: Featured Widget item whose page has no featured image only holds
 *      the title label, which is position:absolute, so the item collapsed to
 *      0px and all four piled onto the same spot, with the labels sticking out
 *      above the widget. Pages and widget are made through the REST API and
 *      removed afterwards.
 */
test("TG: Featured Widget items without a featured image keep their own column @homepage @fresh", async ({ page }) => {
  test.skip(!adminUser || !adminPass, "needs TGQA_ADMIN_USER / TGQA_ADMIN_PASS");
  test.setTimeout(120_000);
  await page.goto("/wp-login.php");
  await page.locator("#user_login").fill(adminUser);
  await page.locator("#user_pass").fill(adminPass);
  await page.locator("#wp-submit").click();
  await expect(page.locator("#wpadminbar")).toBeVisible();

  const pages: number[] = [];
  let widget = "";
  try {
    for (let i = 1; i <= 5; i++) {
      const res = await page.request.post("/?rest_route=/wp/v2/pages", { headers: { "X-WP-Nonce": await nonce(page) }, data: { title: `TGQA plain ${i}`, status: "publish" } });
      expect(res.ok(), `creating a page failed: HTTP ${res.status()}`).toBeTruthy();
      pages.push((await res.json()).id);
    }
    widget = await addFeaturedWidget(page, pages.slice(0, 4));
    await page.goto(`/?page_id=${pages[4]}`);
    await expect(page.locator(`#${widget}`), "the widget is not rendered in the sidebar").toBeVisible();

    const result = await page.locator(`#${widget}`).evaluate((el) => {
      const box = (e: Element) => e.getBoundingClientRect();
      const hit = (a: DOMRect, b: DOMRect) => a.left < b.right && b.left < a.right && a.top < b.bottom && b.top < a.bottom;
      const title = box(el.querySelector(".widget-title")!);
      const items = [...el.querySelectorAll(":scope > .tg-one-fourth")];
      const labels = items.map((i) => box(i.querySelector(".recent_work_title")!));
      let overlaps = 0;
      for (let a = 0; a < labels.length; a++) for (let b = a + 1; b < labels.length; b++) if (hit(labels[a], labels[b])) overlaps++;
      return { count: items.length, heights: items.map((i) => Math.round(box(i).height)), columns: new Set(items.map((i) => Math.round(box(i).left))).size, overlaps, overTitle: labels.filter((l) => hit(l, title)).length };
    });
    expect(result.count, "items rendered").toBe(4);
    expect(Math.min(...result.heights), "an item collapsed to 0px").toBeGreaterThan(0);
    expect(result.columns, "items side by side").toBe(4);
    expect(result.overlaps, "item titles overlapping each other").toBe(0);
    expect(result.overTitle, "item titles over the widget title").toBe(0);
  } finally {
    if (widget) await remove(page, `/wp/v2/widgets/${widget}`);
    for (const id of pages) await remove(page, `/wp/v2/pages/${id}`);
  }
});
