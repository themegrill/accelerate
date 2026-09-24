# Accelerate — QA Knowledge

Draft, not final. See "Needs a human" at the bottom before trusting the
critical-flow ordering, expected-behaviour claims without a citation, or the
known-non-issues list.

## Product

- Free 1.5.4 / Pro 2.3.13. Pro is **not** a child theme or Free+plugin — it is
  a fully independent, standalone fork that duplicates and substantially
  extends the Free codebase (`inc/customizer.php` 662→3,639 lines,
  `inc/functions.php` 736→1,620 lines). Some low-level files (e.g.
  `inc/admin/meta-boxes.php`) are shared byte-for-byte, so a bug in one is
  usually a bug in both.
- `style.css` declares `Tested up to: 6.8`, `Requires PHP: 5.6` in both
  products — stale relative to this environment (WP 7.1.1 / PHP 8.5.2), no
  fatals observed at the newer versions.
- License headers differ: Free = GPLv2-or-later, Pro = GPLv3 (unreconciled
  discrepancy, not a confirmed violation).

## Settings surfaces (source-derived)

- Customizer is the only settings surface — no `register_setting`/
  `add_settings_field` options page. `inc/customizer.php`: 15 sections, 18
  registered settings in Free (89 in Pro), all confirmed to carry a
  `sanitize_callback`.
- One admin page: `add_theme_page` (Accelerate dashboard) —
  `inc/admin/class-accelerate-dashboard.php:38`.
- No REST routes, no custom post types, no shortcodes registered by the
  theme itself.
- One AJAX handler: `wp_ajax_import_button` —
  `inc/admin/class-accelerate-welcome-notice.php:10` (demo-import trigger
  from the welcome notice).

## Persistence

- Central accessor: `accelerate_options( $id, $default = false )`
  (`inc/functions.php:16`), wraps `get_theme_mod()` for every Customizer
  setting — this is what a save-verification check should read back.
- `get_option()` keys in use: `accelerate_theme_installed_time`,
  `accelerate_upgrade_notice_start_time`, `accelerate_admin_notice_welcome`
  (all admin-notice state, not user-facing settings).
- Post meta: the page-layout meta box (`inc/admin/meta-boxes.php:80-109`,
  byte-identical in Free and Pro) writes the raw `$_POST` value straight to
  post meta with **no sanitization** — see Known Issues, ACCELERATE-006.

## Capability boundaries

| Action | Capability required | Evidence |
|---|---|---|
| Theme dashboard / welcome & upgrade notices | `manage_options` | `inc/admin/class-accelerate-welcome-notice.php:36,85,106` |
| Notice-dismiss gating (publish-posts tier) | `publish_posts` | `inc/admin/class-accelerate-notice.php:62,81`; `class-accelerate-upgrade-notice.php:8` |
| Page-layout meta box save | `edit_page` / `edit_post` | `inc/admin/meta-boxes.php:92,95` |
| CTA & Recent Work widget raw-HTML fields | `unfiltered_html` | `inc/widgets/accelerate-call-to-action-widget.php:53,58`; `accelerate-recent-work-widget.php:53` |
| Demo-importer auto-activation | **broken**: checks the non-existent `activate_plugin` (singular) instead of `activate_plugins`, so this always evaluates false, even for Administrators | `inc/admin/class-accelerate-welcome-notice.php:128,169` (ACCELERATE-005) |
| Author-profile social-link fields (Pro) | `edit_user` on one's own account — WordPress grants this to every role, including Subscriber | `accelerate-pro/inc/functions.php:1472-1486` (ACCELERATE-001) |

## Migrations / upgrade handling

- No versioned schema-upgrade routine (no `db_version` pattern anywhere).
  `accelerate_plugin_version_compare()` (`inc/functions.php:723`) only
  compares a *bundled plugin's* version, not the theme's own.
- The "upgrade notice" banner is time-based only: keyed off the
  `accelerate_upgrade_notice_start_time` option plus nonce-gated dismiss
  handlers (`class-accelerate-upgrade-notice.php`, `class-accelerate-notice.php`).

## Fragile areas (git history, most-modified files across the last 400 merged commits)

1. `readme.txt` (102 touches), `style.css` (74) — version-bump/changelog
   churn, expected, not a defect signal on its own.
2. **`inc/functions.php` (42 touches)** — the theme's main enqueue/render/
   option logic. Highest genuine fragility; this is also where
   ACCELERATE-001/003/004/009 all live.
3. **`inc/customizer.php` (30 touches)** — Customizer registration; second-
   highest, and where ACCELERATE-003/015 live.
4. `inc/admin/class-accelerate-admin.php` (29), `inc/admin/class-accelerate-theme-review-notice.php` (11).
5. `header.php` / `inc/header-functions.php` (8 each) — also where
   ACCELERATE-010/021 live.
6. Notable individual commits: `f0e734e fix security capability issue (#56)`,
   `63ef834 fix - jQuery deprecated message`, `6aa32d4 Fix - Menu CSS for
   accessibility`, and `0be99a2 Revert "Fix - menu out of viewport"` — the
   revert signals the mobile-menu/viewport area has resisted a fix before
   (consistent with ACCELERATE-021 still being open).

## Critical flows — TODO: confirm ordering with a human

Proposed from source + the audit's Final Developer Summary. Not yet
confirmed by a maintainer — treat the order as a guess.

1. **demo-import** — welcome notice → theme activation → demo import
   (admin-only; the auto-activate step is currently broken, ACCELERATE-005)
2. **customizer** — change a color (Primary/Header/Footer/Social) →
   live-preview propagates → Save & Publish → front-end reflects it
3. **mobile-menu** — header logo, custom menu, mobile-menu toggle open/close
   at narrow viewports (**currently keyboard-unreachable**, ACCELERATE-021)
4. **slider** — homepage slider (Customizer-configured slides) render +
   link/title/text
5. **blog** — archive/single rendering, comments, related posts
6. **widgets** — sidebar widgets: Recent Work, Call to Action, Featured
   Single Page, Image Service, Custom Tag Cloud
7. **woocommerce** — shop archive → single product → cart → checkout
   (default WooCommerce templates only; theme ships no `woocommerce/`
   overrides)
8. **search** — form submit → results / no-results
9. **author-bio** — author bio block on single posts (social-link icons)
10. **contact-forms** — Contact page third-party forms (Contact Form 7 /
    Everest Forms) — plugin-owned behavior, theme just provides the page
    template
11. **i18n** — string translation via Polylang/WPML for strings that have a
    matching `.po` entry
12. **homepage** — front page renders with a clean console and one h1
    (the baseline every other flow assumes)
13. **assets** — front-end stylesheet/script enqueues (Font Awesome, Google
    Fonts); five overlapping Font Awesome stylesheets today, ACCELERATE-004

## Expected behaviour (live-verified 2026-09-21 — safe to treat as ground truth)

- Homepage: clean console, zero JS errors, both Free and Pro.
- Skip-link present, keyboard-reachable, correctly focuses `#main`.
- Exactly one `<h1>` per template type, both themes.
- Elementor opens cleanly on an Accelerate page with zero console errors.
- Contact Form 7 + Everest Forms on the Contact page: empty-field validation
  blocks submission on both; a real valid submission succeeds on both; no
  theme conflict.
- Customizer live preview: a color change propagates to the preview iframe
  via `postMessage` in ~293ms, no full page reload.
- WooCommerce Shop archive: 182 queries (170 on the homepage), no
  theme-attributable N+1 — all queries QM attributes to "Theme" are core's
  own batched `WHERE id IN (...)` widget queries.
- Polylang: the theme's own gettext loading works correctly for any string
  that has a matching `.po` entry. Many current-version strings fall back to
  English because the bundled translation files are roughly a decade stale
  (ACCELERATE-022) — that's a translation-**content** gap, not a
  loading-mechanism bug.
- Lighthouse (performance category only): Free 70/100 (FCP 4.8s, LCP 5.0s,
  616KB/42 requests); Pro 74/100 (FCP 4.4s, LCP 4.4s, 626KB/44 requests).

## Known issues — confirmed/potential defects as of the 2026-09-21 audit

Source: `accelerate-free-pro-senior-dev-audit.html` (23 findings total, one
directory above the theme root). Don't re-report these from scratch — pull
the finding ID and evidence, and use `write-spec` to graduate the ones that
get picked up as regression coverage.

| ID | Sev | Status | Product | Summary |
|---|---|---|---|---|
| ACCELERATE-001 | High | Confirmed | Pro | Stored XSS via author-profile social-link fields — any Subscriber, `inc/functions.php:1472-1486` |
| ACCELERATE-002 | High | Confirmed | Free | Stored XSS via unescaped `get_the_title()` in the Recent Work widget |
| ACCELERATE-003 | Medium | Confirmed | Both | Customizer color sanitize callback fails open → CSS/script injection, admin-gated |
| ACCELERATE-004 | Medium | Confirmed | Both | 5 overlapping Font Awesome stylesheets + Google Fonts loaded unconditionally on every page; ~⅔ of page weight |
| ACCELERATE-005 | Low | Confirmed | Free | Demo-importer auto-activate capability check uses non-existent `activate_plugin` (singular), always false |
| ACCELERATE-006 | Low | Confirmed | Both | Unsanitized `$_POST` written straight to post meta (no live XSS path today) |
| ACCELERATE-007 | Low | Confirmed | Both | Unguarded array-key access in `meta-boxes.php` → PHP 8.x warnings |
| ACCELERATE-008 | Low | Confirmed | Both | Deprecated `wp_script_add_data(..., 'conditional', ...)` → WP 6.9+ deprecation notice |
| ACCELERATE-009 | Low | Confirmed | Pro | `waypoints`/`counterup` scripts enqueued unconditionally regardless of usage |
| ACCELERATE-010 | Low | Potential | Free | Slider title/text echoed without output escaping (save-time sanitization only) |
| ACCELERATE-011 | Low | Confirmed | Free | Search form input has no accessible label |
| ACCELERATE-012 | Info | Potential | Free | Tag Cloud widget title echoed raw (body-text context, low risk) |
| ACCELERATE-013 | Info | Confirmed | Free | `extract()` used in all 5 custom widget classes |
| ACCELERATE-014 | Info | Confirmed | Pro | Freemius `admin_init` hook not capability-gated (no input processed, no real risk) |
| ACCELERATE-015 | Info | Confirmed | Pro | Typo in internal setting id (`hedaer` vs `header`), cosmetic |
| ACCELERATE-016 | Info | Confirmed | Both | Declared "Tested up to 6.8" / "Requires PHP 5.6" stale vs. this environment |
| ACCELERATE-017 | Info | Unverified | Pro | Freemius SDK is an uninitialized git submodule in this checkout — telemetry behavior unverifiable |
| ACCELERATE-018 | Info | Confirmed | Both | GPL license header discrepancy, Free v2-or-later vs Pro v3 |
| ACCELERATE-019 | Info | Recommendation | Both | No `woocommerce/` template-override directory in either theme |
| ACCELERATE-020 | Info | Recommendation | Both | Narrow `apply_filters()` surface (2 in Free, 4 in Pro) vs. 15 `do_action()` each |
| ACCELERATE-021 | **Medium** | **Confirmed** | **Both** | **Mobile nav toggle is a non-focusable `<h3>`, entire primary menu unreachable by keyboard at narrow viewports — live-confirmed** |
| ACCELERATE-022 | Info | Confirmed | Both | Bundled translations ~decade stale; newer strings fall back to English under any i18n plugin |
| ACCELERATE-023 | Info | Unverified | Free | Theme's own search-widget markup absent for a logged-in visitor on the homepage — root cause not traced |

Full technical detail, evidence, and repro steps for each ID are in the
source audit HTML — read that file directly rather than re-deriving.

## Known non-issues — TODO: confirm with a human

Proposed candidates the audit already characterized as no/low risk, pending
maintainer sign-off (do not treat as final without one):

- ACCELERATE-014 — Freemius hook fires broadly but reads no user input.
- ACCELERATE-019 — no `woocommerce/` overrides is a scope choice, not a bug;
  default templates confirmed working (shop/cart returned HTTP 200).
- ACCELERATE-020 — narrow filter surface is an extensibility recommendation.
- ACCELERATE-012 — Tag Cloud widget title is body-text context only,
  `strip_tags()` at save time already blocks script/tag injection.

## What must survive an upgrade — TODO: unknown, no human input yet

No versioned migration routine exists to anchor this against (see
Migrations above). A human needs to say which user-configured state
(Customizer settings, widget placements, page-layout meta) must be
preserved across a version bump.

## Sources

- Full source read of `accelerate/` v1.5.4 at this theme's root.
- Local audit report `accelerate-free-pro-senior-dev-audit.html` (dated
  2026-09-21, 23 findings, tested against WP 7.1.1 / PHP 8.5.2 / WooCommerce
  11.1.0), one directory above the theme root in the local WordPress project
  workspace — used as the primary source for critical flows, expected
  behaviour, and known issues, since it reflects live-verified behavior
  rather than inferred documentation categories.
- Supporting screenshots, same location: `phase3-accelerate-home-desktop.png`,
  `phase3-accelerate-checkout.png`, `phase3-accelerate-footer-widgets*.png`,
  `phase3-accelerate-mobile-menu-open.png`, `phase3-accelerate-shop-tablet768.png`,
  `accelerate-768-header.png`.
- Remote docs (`docs.themegrill.com`) were attempted and **not used**:
  neither the `doc_category` REST taxonomy nor the page/docs sitemaps
  resolve to Accelerate-specific articles — the `/accelerate/` page exists
  and has real content, but isn't indexed as its own REST or sitemap entity
  on that site. Flagged rather than guessed at; re-attempt if the docs site
  changes its structure.

## Needs a human

- Critical-flow ordering above: proposed, unconfirmed.
- Known non-issues: proposed, unconfirmed.
- Upgrade-safety requirements: unknown.
- Whether to treat ACCELERATE-001/002/003 (the three confirmed/potential
  XSS findings) as release blockers before any further feature work — the
  audit deliberately doesn't assign an overall score, but these are the
  three highest-value candidates for immediate `write-spec` graduation.
