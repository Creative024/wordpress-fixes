# WordPress fixes from real client sites

Small, working fixes for problems I hit while building and running WordPress sites for small businesses in Louisville, Kentucky. Each one fails silently: no error message, no warning in the dashboard. The page just looks wrong, a form quietly stops working, or a number in your reports is off.

Every fix says what you will see, why it happens and what to do. Most are a few lines you can drop into a child theme, a must-use plugin or your terminal.

I am Shaun Wilson, a one-person web design and WordPress studio in Louisville ([shaunwilsondesigns.com](https://shaunwilsondesigns.com/)).

## The fixes

| Folder / file | Problem | Works with |
|---|---|---|
| [`gravity-forms/turnstile-send-beside-challenge.css`](gravity-forms/turnstile-send-beside-challenge.css) | A blank band above the Send button that is really the Cloudflare Turnstile challenge space. Hiding it blocks real inquiries | Gravity Forms + Gravity Forms Cloudflare Turnstile add-on |
| [`kadence/glue-without-mid-word-breaks.css`](kadence/glue-without-mid-word-breaks.css) | Gluing the last two words of a heading to stop widows makes the browser split a word in half | Kadence theme (any theme that sets `word-break: break-word`) |
| [`wordpress/write-post-content-safely.php`](wordpress/write-post-content-safely.php) | Saving page content from a script silently deletes inline `<style>` and `<script>`, turns `>` into `&gt;` in Additional CSS, or gets a 403 from ModSecurity | WordPress core, WP-CLI |
| [`wordpress/hand-built-page-filters.php`](wordpress/hand-built-page-filters.php) | Inline JavaScript dies because `&&` became `&#038;&#038;`; code text shows en dashes and curly quotes | WordPress core |
| [`wp-super-cache/check-gzip-and-cart.sh`](wp-super-cache/check-gzip-and-cart.sh) | In Apache rewrite mode, browsers download a gzip file instead of the page, and shoppers with a cart get cached pages | WP Super Cache, WooCommerce, Apache |
| [`wp-super-cache/clear-cache-rest.sh`](wp-super-cache/clear-cache-rest.sh) | Clearing one page's cache without FTP or the dashboard | WP Super Cache |
| [`cloudflare/scanner-rule-image-exemption.md`](cloudflare/scanner-rule-image-exemption.md) | A firewall rule that stops vulnerability scanners also blocks the logo in your emails and your own scripts | Cloudflare custom rules |
| [`javascript/reveal-on-first-scroll.js`](javascript/reveal-on-first-scroll.js) | Scroll-reveal animations that leave content invisible in background tabs, screenshots, crawler renders and print | Any site |

## How to use them

- **CSS files:** paste into Appearance > Customize > Additional CSS, or your child theme's `style.css`. Change selectors such as `.entry-content` to match your theme.
- **PHP files:** put `hand-built-page-filters.php` in `wp-content/mu-plugins/` and edit the page ID list. Run `write-post-content-safely.php` with WP-CLI using an existing authorized administrator: `wp --user=YOUR_ADMIN_LOGIN eval-file write-post-content-safely.php 123 new-content.html`. It checks capabilities, makes a private backup and uses the normal WordPress save path.
- **Shell scripts:** `sh check-gzip-and-cart.sh https://example.com/`. The cache-clearing script reads an existing Application Password from the local environment and passes it through standard input, keeping it out of curl process arguments. It requires HTTPS, Python 3 and curl 7.76 or newer. Do not put passwords in shell history or use shell tracing.
- Test on a staging copy first. Back up before you change anything.

## Write-ups

Related WordPress troubleshooting guides and tools: [shaunwilsondesigns.com/resources/](https://shaunwilsondesigns.com/resources/).

## License

GPL-2.0-or-later, the same license as WordPress. Use, change and share freely.

## Need help with a site that is doing something strange?

That is most of my work: [shaunwilsondesigns.com/broken-wordpress/](https://shaunwilsondesigns.com/broken-wordpress/).
