import { test, expect, type Frame, type Page } from "@playwright/test";
import { createPage, deletePage, login, requireAdmin } from "../../utils/wp";

/**
 * @area block-editor
 * @tier fresh
 * @guards accelerate-pro#47
 * @source accelerate-pro#47 (reported by iamprazol)
 * @why The block editor never used the Customizer: it loaded a static stylesheet
 *      and a hardcoded Roboto link on a hook whose styles never reach the editor
 *      iframe, so no theme font loaded in the canvas, and the title, form fields
 *      and the Customizer primary color differed from the front end. Guards
 *      that the canvas carries the same fonts stylesheet as the front end, that
 *      the theme's editor styles reach the iframe the supported way, and that the
 *      title, a heading, text, a link and a search field compute the same font,
 *      size, weight and color in the editor as on the published page. Reads the
 *      stylesheet links, so it needs no network access to Google.
 */
const content = [
  '<!-- wp:heading --><h2 class="wp-block-heading">TGQA heading</h2><!-- /wp:heading -->',
  '<!-- wp:paragraph --><p>TGQA text with <a href="https://example.com">a link</a>.</p><!-- /wp:paragraph -->',
  '<!-- wp:search {"label":"Search","buttonText":"Search"} /-->',
].join("\n");

const targets = {
  heading: "h2.wp-block-heading",
  text: "p",
  link: "p a",
  search: ".wp-block-search__input",
};

async function styles(root: Page | Frame, scope: string, title: string) {
  return root.locator(scope).first().evaluate(
    (el, { targets, title }) => {
      const read = (node: Element | null) => {
        if (!node) return null;
        const c = getComputedStyle(node);
        return `${c.fontFamily.split(",")[0].replace(/["']/g, "")} ${c.fontSize} ${c.fontWeight} ${c.color}`;
      };
      const out: Record<string, string | null> = { title: read(document.querySelector(title)) };
      for (const [name, selector] of Object.entries(targets)) out[name] = read(el.querySelector(selector));
      return out;
    },
    { targets, title },
  );
}

const fontsHref = (href: string | null) => (href ?? "").replace(/^https?:/, "").replace(/&(amp;|#038;)?ver=[^&]*/, "");

test("the block editor uses the front end's fonts, typography and colors @block-editor @fresh", async ({
  page,
}, testInfo) => {
  requireAdmin(testInfo);
  test.setTimeout(180_000);
  const iframeWarnings: string[] = [];
  page.on("console", (message) => {
    if (/accelerate.*added to the iframe incorrectly/.test(message.text())) iframeWarnings.push(message.text());
  });
  await login(page);
  let pageId = 0;
  try {
    const created = await createPage(page, "TGQA editor styles", content);
    pageId = created.id;

    await page.goto(created.link);
    const front = await styles(page, ".entry-content", ".entry-title");
    const frontFonts = await page.locator("#accelerate_googlefonts-css").evaluateAll((links) => links[0]?.getAttribute("href") ?? null);

    await page.goto(`/wp-admin/post.php?post=${pageId}&action=edit`, { waitUntil: "domcontentloaded" });
    const canvas = page.frameLocator('iframe[name="editor-canvas"]');
    await expect(canvas.locator(".editor-styles-wrapper .wp-block-search__input")).toBeVisible({ timeout: 90_000 });
    const frame = page.frame({ name: "editor-canvas" })!;

    await expect(canvas.locator("#accelerate-block-editor-styles-css"), "the theme's editor styles must reach the canvas").toBeAttached({ timeout: 15_000 });
    const editorFonts = await canvas.locator("#accelerate-editor-googlefonts-css").evaluateAll((links) => links[0]?.getAttribute("href") ?? null);
    expect(editorFonts && fontsHref(editorFonts), "the editor canvas must load the front end's fonts stylesheet").toBe(frontFonts && fontsHref(frontFonts));

    const editor = await styles(frame, ".editor-styles-wrapper", ".editor-post-title__input");
    for (const name of Object.keys(front)) {
      expect(front[name], `${name} is missing on the front end`).not.toBeNull();
      expect(editor[name], `${name}: editor (font size weight color) differs from the front end`).toBe(front[name]);
    }
    expect(iframeWarnings).toEqual([]);
  } finally {
    if (pageId) await deletePage(page, pageId);
  }
});
