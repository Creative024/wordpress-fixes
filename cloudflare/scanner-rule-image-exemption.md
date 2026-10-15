# Cloudflare: a "block scanners" rule that also blocks email logos

**Symptom.** Logos and images in emails sent through an email service show as broken.
Nothing looks wrong on the website.

**Cause.** A custom firewall rule that blocks requests with an empty User-Agent or a
scripting-library User-Agent (`go-http-client`, `python-requests`, `aiohttp` and so on)
can reduce scanner noise, but it is not authentication or a reliable security boundary.
Some email services fetch your images
through a proxy that identifies itself as `Go-http-client/2.0`, so the rule blocks your
own logo. The same rule also blocks your own scripts and connectors that send no
User-Agent; those may receive a 403. Give every script of your own an
explicit User-Agent.

**Fix.** Review the existing rule, wrap its complete expression in parentheses, then
append this narrow exception for public uploaded images. Do not change authentication,
managed firewall rules, or rate limits:

```
 and not (starts_with(http.request.uri.path, "/wp-content/uploads/") and http.request.uri.path.extension in {"png" "jpg" "jpeg" "gif" "webp" "avif" "svg" "ico"})
```

If the rule is written as `A or (B and C)`, Cloudflare evaluates `and` before `or`, so check
which group the new clause joins. The form should be `(ORIGINAL_RULE) and not (IMAGE_EXCEPTION)`.

**Check.** With the scripting User-Agent: an image under `/wp-content/uploads/` returns 200,
the home page still returns 403, and `/wp-content/uploads/x.php` still returns 403.
