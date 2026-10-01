import { test, expect } from "@playwright/test";
import { addLegacyWidget, createPage, deletePage, login, removeWidget, requireAdmin } from "../../utils/wp";

/**
 * @area widgets
 * @tier fresh
 * @guards accelerate-pro#54
 * @source accelerate-pro#54 (reported by iamprazol)
 * @why The widget always saves button_url (as "" when left empty), so the
 *      isset() fallback to "#" never applied and the button rendered href="",
 *      which reloads the current page on click.
 */
test("TG: Call to Action button falls back to # when no link is set @widgets @fresh", async ({ page }, testInfo) => {
  requireAdmin(testInfo);
  test.setTimeout(120_000);
  await login(page);
  let hostId = 0;
  let widget = "";
  try {
    const host = await createPage(page, "TGQA call to action host", "");
    hostId = host.id;
    widget = await addLegacyWidget(page, "accelerate_call_to_action_widget", "accelerate_right_sidebar", {
      text_main: "TGQA call to action",
      text_additional: "",
      button_text: "Get Started",
      button_url: "",
    });

    await page.goto(host.link);
    const button = page.locator(`#${widget} a.read-more`);
    await expect(button, "the call to action button is not rendered").toBeVisible();
    await expect(button).toHaveAttribute("href", "#");
  } finally {
    if (widget) await removeWidget(page, widget);
    if (hostId) await deletePage(page, hostId);
  }
});
