import { test, expect, type Frame, type Page } from "@playwright/test";
import { login, requireAdmin, restNonce } from "../../utils/wp";

/**
 * @area block-editor
 * @tier fresh
 * @guards accelerate-pro#47
 * @source accelerate-pro#47 (reported by iamprazol)
 * @why The block editor never used the Customizer: it loaded a static stylesheet
 *      and a hardcoded Roboto link on a hook whose styles never reach the editor
 *      iframe, so no theme font loaded in the canvas, and the title, form fields,
 *      buttons and the Customizer primary color differed from the front end.
 *      Saves a custom primary color through the Customizer itself (restored
 *      afterwards), then checks on a post and a page
 *      that the canvas carries the front end's fonts stylesheet, that the theme's
 *      editor styles reach the iframe the supported way, and that each element
 *      computes the same font, size, weight, color and background in the editor
 *      as on the published page. Reads the stylesheet links, so it needs no
 *      network access to Google.
 */
const settings: Record<string, string> = {
  accelerate_primary_color: "#d63384",
};

const content = [
  '<!-- wp:heading --><h2 class="wp-block-heading">TGQA heading</h2><!-- /wp:heading -->',
  '<!-- wp:paragraph --><p>TGQA text with <a href="https://example.com">a link</a>.</p><!-- /wp:paragraph -->',
  '<!-- wp:list --><ul class="wp-block-list"><!-- wp:list-item --><li>TGQA list item</li><!-- /wp:list-item --></ul><!-- /wp:list -->',
  '<!-- wp:freeform --><blockquote><p>TGQA classic quote</p></blockquote><p><input type="submit" value="Go" /> <button type="button">Plain button</button></p><!-- /wp:freeform -->',
  '<!-- wp:search {"label":"Search","buttonText":"Search"} /-->',
].join("\n");

const targets: Record<string, string> = {
  heading: "h2.wp-block-heading",
  text: "p",
  link: "p a",
  list: "li",
  "classic quote": "blockquote:not(.wp-block-quote)",
  submit: "input[type=submit]",
  button: "p button",
  "search field": ".wp-block-search__input",
  "search button": ".wp-block-search__button",
};

async function styles(root: Page | Frame, scope: string, title: string) {
  return root.locator(scope).first().evaluate(
    (el, { targets, title }) => {
      const read = (node: Element | null) => {
        if (!node) return null;
        const c = getComputedStyle(node);
        return `${c.fontFamily.split(",")[0].replace(/["']/g, "")} ${c.fontSize} ${c.fontWeight} ${c.color} on ${c.backgroundColor}`;
      };
      const out: Record<string, string | null> = { title: read(document.querySelector(title)) };
      for (const [name, selector] of Object.entries(targets)) out[name] = read(el.querySelector(selector));
      return out;
    },
    { targets, title },
  );
}

/** Set Customizer values through the Customizer's own API and publish them; returns the previous values. */
async function saveCustomizer(page: Page, values: Record<string, string>): Promise<Record<string, string>> {
  await page.goto("/wp-admin/customize.php", { waitUntil: "domcontentloaded" });
  await page.waitForFunction(() => (window as any).wp?.customize?.state?.("activated")?.get(), null, { timeout: 90_000 });
  return page.evaluate(async (values) => {
    const api = (window as any).wp.customize;
    const previous: Record<string, string> = {};
    for (const [key, value] of Object.entries(values)) {
      const setting = api(`accelerate[${key}]`);
      if (!setting) throw new Error(`Customizer setting accelerate[${key}] is not registered`);
      previous[key] = setting.get();
      setting.set(value);
    }
    await new Promise((resolve, reject) => api.previewer.save().done(resolve).fail(reject));
    return previous;
  }, values);
}

async function createPost(page: Page, type: "posts" | "pages", title: string): Promise<{ id: number; link: string }> {
  const res = await page.request.post(`/?rest_route=/wp/v2/${type}`, {
    headers: { "X-WP-Nonce": await restNonce(page) },
    data: { title, content, status: "publish" },
  });
  expect(res.ok(), `creating the test ${type} failed: HTTP ${res.status()}`).toBeTruthy();
  const body = await res.json();
  return { id: body.id, link: body.link };
}

// POST with a method override: some hosts answer a bare DELETE with 405.
async function deletePost(page: Page, type: "posts" | "pages", id: number): Promise<void> {
  const res = await page.request.post(`/?rest_route=/wp/v2/${type}/${id}&force=true`, {
    headers: { "X-WP-Nonce": await restNonce(page), "X-HTTP-Method-Override": "DELETE" },
  });
  expect(res.ok(), `deleting test ${type} ${id} failed: HTTP ${res.status()}`).toBeTruthy();
}

const fontsHref = (href: string | null) => (href ?? "").replace(/^https?:/, "").replace(/&(amp;|#038;)?ver=[^&]*/, "");

test("the block editor uses the front end's fonts, typography and colors @block-editor @fresh", async ({
  page,
}, testInfo) => {
  requireAdmin(testInfo);
  test.setTimeout(420_000);
  const iframeWarnings: string[] = [];
  page.on("console", (message) => {
    if (/accelerate.*added to the iframe incorrectly/.test(message.text())) iframeWarnings.push(message.text());
  });
  await login(page);
  let previous: Record<string, string> | null = null;
  const created: { type: "posts" | "pages"; id: number }[] = [];
  try {
    previous = await saveCustomizer(page, settings);

    for (const type of ["posts", "pages"] as const) {
      const item = await createPost(page, type, `TGQA editor styles ${type}`);
      created.push({ type, id: item.id });

      await page.goto(item.link);
      const front = await styles(page, ".entry-content", ".entry-title");
      // No link when only standard (system) fonts are chosen.
      const frontFonts = await page.locator("#accelerate_googlefonts-css").evaluateAll((links) => links[0]?.getAttribute("href") ?? null);

      await page.goto(`/wp-admin/post.php?post=${item.id}&action=edit`, { waitUntil: "domcontentloaded" });
      const canvas = page.frameLocator('iframe[name="editor-canvas"]');
      await expect(canvas.locator(".editor-styles-wrapper .wp-block-search__input")).toBeVisible({ timeout: 90_000 });
      const frame = page.frame({ name: "editor-canvas" })!;

      await expect(canvas.locator("#accelerate-block-editor-styles-css"), "the theme's editor styles must reach the canvas").toBeAttached({ timeout: 15_000 });
      const editorFonts = await canvas.locator("#accelerate-editor-googlefonts-css").evaluateAll((links) => links[0]?.getAttribute("href") ?? null);
      expect.soft(editorFonts && fontsHref(editorFonts), `${type}: the editor canvas must load the front end's fonts stylesheet`).toBe(frontFonts && fontsHref(frontFonts));

      const editor = await styles(frame, ".editor-styles-wrapper", ".editor-post-title__input");
      for (const name of Object.keys(front)) {
        expect(front[name], `${type}: ${name} is missing on the front end`).not.toBeNull();
        expect.soft(editor[name], `${type}: ${name} (font size weight color on background) differs from the front end`).toBe(front[name]);
      }
    }
    expect(iframeWarnings).toEqual([]);
  } finally {
    for (const { type, id } of created) await deletePost(page, type, id);
    if (previous) await saveCustomizer(page, previous);
  }
});
