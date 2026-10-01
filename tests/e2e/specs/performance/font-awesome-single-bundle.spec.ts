import { test, expect } from "@playwright/test";

/**
 * @area assets
 * @tier fresh
 * @guards themegrill/accelerate-pro#83 ACCELERATE-004
 * @source accelerate-free-pro-senior-dev-audit.html#ACCELERATE-004; themegrill/flash#120 (same fix)
 * @why inc/functions.php enqueued v4-shims, all, solid, regular and brands on
 *      every page, and `all` already contains every rule and @font-face of the
 *      other three. v4-shims stays: the theme's markup uses v4 class names
 *      (`fa fa-*`) that only the shim maps, so the target is two, not one.
 *      Asserts each of `all` and the shim is requested exactly once (minified
 *      or, with SCRIPT_DEBUG, not), and that a v4 solid, regular and brand icon
 *      still resolve to a loaded Font Awesome face. The probe icons are added to
 *      the page, so this does not depend on what content the site has.
 */
test("Font Awesome loads only the full bundle and the v4 shim, and icons still render @assets @performance @fresh", async ({
  page,
}) => {
  const requested: string[] = [];
  page.on("request", (req) => {
    const m = req.url().match(/\/fontawesome\/css\/([a-z0-9-]+?)(?:\.min)?\.css/);
    if (m) requested.push(m[1]);
  });

  await page.goto("/");
  await page.waitForLoadState("networkidle");
  // An array, not a Set: the same file requested twice must fail too.
  expect(requested.sort(), "Font Awesome stylesheets requested").toEqual(["all", "v4-shims"]);

  // One v4 class name per style the removed stylesheets used to cover.
  const probes = { "fa-user": "Font Awesome 6 Free", "fa-calendar-o": "Font Awesome 6 Free", "fa-facebook": "Font Awesome 6 Brands" };
  for (const [icon, family] of Object.entries(probes)) {
    const rendered = await page.evaluate(async (cls) => {
      const el = document.createElement("i");
      el.className = `fa ${cls}`;
      document.body.append(el);
      const cs = getComputedStyle(el, "::before");
      const glyph = cs.content.replace(/^["']|["']$/g, "");
      // Read now: the computed style is live and empties once the element is removed.
      const family = cs.fontFamily;
      const font = `${cs.fontWeight} 16px ${family}`;
      let faces: string[] = [];
      try {
        if (glyph && glyph !== "none") faces = (await document.fonts.load(font, glyph)).map((f) => f.family);
      } catch {
        faces = [];
      }
      el.remove();
      return { glyph, family, faces };
    }, icon);
    expect(rendered.glyph, `${icon} has no glyph`).not.toMatch(/^(none)?$/);
    expect(rendered.family, `${icon} font family`).toContain(family);
    expect(rendered.faces.length, `${icon} glyph has no loaded font face`).toBeGreaterThan(0);
  }
});
