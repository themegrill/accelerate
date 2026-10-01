import { test, expect } from "@playwright/test";

/**
 * @area assets
 * @tier fresh
 * @guards themegrill/accelerate-pro#83 ACCELERATE-004
 * @source accelerate-free-pro-senior-dev-audit.html#ACCELERATE-004; themegrill/flash#120 (same fix)
 * @why inc/functions.php enqueued v4-shims, all, solid, regular and brands on
 *      every page, and `all` already contains every rule and @font-face of the
 *      other three. v4-shims stays: the theme's markup uses v4 class names
 *      (`fa fa-*`) that only the shim maps, so the target is exactly two, not
 *      one. Asserts `all` + the shim (minified or, with SCRIPT_DEBUG, not), and
 *      that a solid and a regular v4 icon in the post meta still render from a
 *      loaded Font Awesome face.
 */
test("Font Awesome loads only the full bundle and the v4 shim, and icons still render @assets @performance @fresh", async ({
  page,
}) => {
  const requested = new Set<string>();
  page.on("request", (req) => {
    const m = req.url().match(/\/fontawesome\/css\/([a-z0-9-]+?)(?:\.min)?\.css/);
    if (m) requested.add(m[1]);
  });

  await page.goto("/");
  await page.waitForLoadState("networkidle");
  expect([...requested].sort(), "Font Awesome stylesheets requested").toEqual(["all", "v4-shims"]);

  // fa-user is solid (900); fa-calendar-o only exists through the v4 shim, as regular (400).
  const meta = page.locator(".entry-meta").first();
  test.skip(!(await meta.count()), "no post on the homepage to carry the meta icons");
  for (const icon of ["fa-user", "fa-calendar-o"]) {
    const el = meta.locator(`i.fa.${icon}`);
    await expect(el, `${icon} is missing from the post meta`).toHaveCount(1);
    const rendered = await el.evaluate(async (e) => {
      await document.fonts.ready;
      const cs = getComputedStyle(e, "::before");
      const glyph = cs.content.replace(/^["']|["']$/g, "");
      return { glyph, family: cs.fontFamily, loaded: glyph !== "" && glyph !== "none" && document.fonts.check(`${cs.fontWeight} 16px ${cs.fontFamily}`, glyph) };
    });
    expect(rendered.family, `${icon} font family`).toContain("Font Awesome 6 Free");
    expect(rendered.loaded, `${icon} glyph "${rendered.glyph}" has no loaded font face`).toBe(true);
  }
});
