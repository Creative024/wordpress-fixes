#!/bin/sh
# Clear WP Super Cache without FTP, through the plugin's own REST route.
# {"id": N} clears one page (static and dynamic copies); {"all": true} clears everything.
# Re-saving the post does NOT clear the static copy. If the site is behind Cloudflare,
# purge Cloudflare afterwards too: the edge keeps serving its copy until you do.
#
# Reads an existing Application Password from the local environment and passes it to
# curl through standard input, keeping it out of curl's process arguments. Do not use set -x.
#
# Usage: securely populate WP_USER and WP_APP_PASSWORD first, then:
#        sh clear-cache-rest.sh https://example.com 123
#        sh clear-cache-rest.sh https://example.com all
# Requires Python 3 and curl. No live site is changed until you run this script.
set -eu
SITE="${1:?site url}"
WHAT="${2:?post id or all}"
: "${WP_USER:?Set WP_USER through your secure local environment}"
: "${WP_APP_PASSWORD:?Set WP_APP_PASSWORD through your secure local environment}"
case "$SITE" in https://*) ;; *) echo 'Use an HTTPS site URL.' >&2; exit 1 ;; esac
case "$WHAT" in
  all) BODY='{"all":true}' ;;
  ''|0*|*[!0-9]*) echo 'Use a positive post ID or all.' >&2; exit 1 ;;
  *) BODY="{\"id\":$WHAT}" ;;
esac
SITE=${SITE%/}
python3 -c 'import json,os; print("user = " + json.dumps(os.environ["WP_USER"] + ":" + os.environ["WP_APP_PASSWORD"]))' |
curl -q --config - --fail-with-body --silent --show-error --connect-timeout 10 --max-time 30 -A "wordpress-fixes/1.0" \
	-H 'Content-Type: application/json' -X POST -d "$BODY" \
	"$SITE/wp-json/wp-super-cache/v1/cache"
echo
