import { test, expect, type Page, type TestInfo } from "@playwright/test";
import { login, requireAdmin, restNonce, createPage, deletePage } from "../../utils/wp";

const PLUGIN = "elementor/elementor";

const contextOptions = ({ project }: TestInfo) => ({
  baseURL: project.use.baseURL,
  ignoreHTTPSErrors: project.use.ignoreHTTPSErrors,
});

type Provisioned ={ pageId: number; previous: "active" | "inactive" | "absent" };

// Elementor is not part of the fresh blueprint, so the spec brings it in and puts the site back.
async function provisionElementor(page: Page): Promise<Provisioned["previous"]> {
  const nonce = await restNonce(page);
  const headers = { "X-WP-Nonce": nonce };

  const current = await page.request.get(`/?rest_route=/wp/v2/plugins/${PLUGIN}`, { headers });
  if (current.ok()) {
    const status = (await current.json()).status as string;
    if (status === "active") return "active";
    const res = await page.request.post(`/?rest_route=/wp/v2/plugins/${PLUGIN}`, {
      headers,
      data: { status: "active" },
    });
    expect(res.ok(), `activating Elementor failed: HTTP ${res.status()}`).toBeTruthy();
    return "inactive";
  }

  const res = await page.request.post("/?rest_route=/wp/v2/plugins", {
    headers,
    data: { slug: "elementor", status: "active" },
  });
  expect(res.ok(), `installing Elementor failed: HTTP ${res.status()}`).toBeTruthy();
  return "absent";
}

async function restoreElementor(page: Page, previous: Provisioned["previous"]): Promise<void> {
  if (previous === "active") return;
  const nonce = await restNonce(page);
  const headers = { "X-WP-Nonce": nonce };
  await page.request.post(`/?rest_route=/wp/v2/plugins/${PLUGIN}`, {
    headers,
    data: { status: "inactive" },
  });
  if (previous === "absent") {
    await page.request.post(`/?rest_route=/wp/v2/plugins/${PLUGIN}`, {
      headers: { ...headers, "X-HTTP-Method-Override": "DELETE" },
    });
  }
}

// Saves the page through Elementor's own editor endpoint, the only supported way to give a page widget data.
async function saveElementorPage(page: Page, id: number): Promise<void> {
  const editor = await page.request.get(`/wp-admin/post.php?post=${id}&action=elementor`);
  expect(editor.ok(), `opening the Elementor editor failed: HTTP ${editor.status()}`).toBeTruthy();
  const nonce = (await editor.text()).match(/admin-ajax\.php\\?",\\?"nonce\\?":\\?"([a-f0-9]+)/)?.[1];
  expect(nonce, "could not read Elementor's ajax nonce from the editor page").toBeTruthy();

  const elements = [
    {
      id: "a1b2c3d",
      elType: "container",
      settings: {},
      isInner: false,
      elements: [
        {
          id: "b1c2d3e",
          elType: "widget",
          widgetType: "wp-widget-search",
          settings: { wp: { title: "Search Title" } },
          elements: [],
        },
      ],
    },
  ];
  const res = await page.request.post("/wp-admin/admin-ajax.php", {
    form: {
      action: "elementor_ajax",
      _nonce: nonce!,
      editor_post_id: String(id),
      actions: JSON.stringify({
        save: { action: "save_builder", data: { status: "publish", elements, settings: {} } },
      }),
    },
  });
  expect(res.ok(), `saving the Elementor page failed: HTTP ${res.status()}`).toBeTruthy();
  expect((await res.json()).success, "Elementor refused to save the page").toBe(true);
}

test.describe("Elementor WordPress widgets @elementor @fresh", () => {
  test.describe.configure({ mode: "serial" });

  let state: Provisioned | undefined;

  test.beforeAll(async ({ browser }, testInfo) => {
    requireAdmin(testInfo);
    const context = await browser.newContext(contextOptions(testInfo));
    const page = await context.newPage();
    await login(page);
    const previous = await provisionElementor(page);
    const { id, link } = await createPage(page, "tgqa-elementor-widget-wrapper", "");
    state = { pageId: id, previous };
    await saveElementorPage(page, id);
    testInfo.annotations.push({ type: "page", description: link });
    await context.close();
  });

  test.afterAll(async ({ browser }, testInfo) => {
    if (!state) return;
    const context = await browser.newContext(contextOptions(testInfo));
    const page = await context.newPage();
    await login(page);
    await deletePage(page, state.pageId);
    await restoreElementor(page, state.previous);
    await context.close();
  });

  /**
   * @area elementor
   * @tier fresh
   * @source write-spec 2026-10-05
   * @why The theme's elementor/widgets/wordpress/widget_args filter built
   *      `<aside class="widget ">` for any widget outside the theme's own list,
   *      so core and WooCommerce widgets lost the class their styles hang off
   *      (widget_search, widget_shopping_cart). Asserts the class from the
   *      widget's own registered classname; says nothing about the styling it
   *      unlocks or about the theme's own widgets.
   */
  test("a core widget in Elementor keeps its widget class @elementor @fresh", async ({ page }) => {
    await page.goto(`/?page_id=${state!.pageId}`);
    const wrapper = page.locator(".elementor-widget-wp-widget-search aside");
    await expect(wrapper).toHaveCount(1);
    await expect(wrapper).toHaveClass(/\bwidget_search\b/);
  });

  /**
   * @area elementor
   * @tier fresh
   * @source write-spec 2026-10-05
   * @why With the widget class restored, the sidebar title-icon rules start to
   *      match inside Elementor, but they only set the Font Awesome family
   *      under #secondary and the footer, so the icon rendered as a missing
   *      glyph box. Asserts the family only, not the glyph or its colour.
   */
  test("a titled widget in Elementor draws its title icon with Font Awesome @elementor @fresh", async ({
    page,
  }) => {
    await page.goto(`/?page_id=${state!.pageId}`);
    const title = page.locator(".elementor-widget-wp-widget-search aside h3 span");
    await expect(title).toHaveText("Search Title");
    const family = await title.evaluate((el) => getComputedStyle(el, "::before").fontFamily);
    expect(family).toContain("FontAwesome");
  });
});
