<?php
/**
 * Plugin Name: Hand-built page filters
 * Description: Stops WordPress rewriting the HTML of chosen hand-built pages (wpautop, convert_chars, wptexturize, smilies).
 *
 * Symptoms this fixes, all silent:
 * - Inline JavaScript dies with "Invalid or unexpected token": convert_chars turned a && b
 *   into a &#038;&#038; b. wptexturize skips <script>; convert_chars does not.
 * - Code, terminal or CLI text on the page shows en dashes and curly quotes: wptexturize
 *   runs after blocks render, so --status becomes an en dash. It only skips pre/code/kbd/tt.
 * - Stray <p> and <br> inside hand-written markup: wpautop.
 *
 * Check a page with: curl -s URL | grep -c '&#038;&#038;'   (should print 0)
 *
 * @package WordPressFixes
 */

defined( 'ABSPATH' ) || exit;

/**
 * IDs of pages whose HTML is written by hand. Edit this list.
 *
 * @return int[]
 */
function wpfixes_hand_built_ids() {
	return array( 123, 456 );
}

add_action(
	'wp',
	function () {
		if ( is_singular() && in_array( get_queried_object_id(), wpfixes_hand_built_ids(), true ) ) {
			foreach ( array( 'wpautop', 'convert_chars', 'wptexturize', 'convert_smilies' ) as $filter ) {
				remove_filter( 'the_content', $filter );
			}
		}
	}
);

/**
 * For code-like text outside <code>, write the characters wptexturize changes as entities.
 *
 * @param string $text Plain text.
 * @return string
 */
function wpfixes_code_text( $text ) {
	return strtr(
		esc_html( $text ),
		array(
			'-'      => '&#45;',
			'&#039;' => '&#39;',
			'&quot;' => '&#34;',
			'.'      => '&#46;',
		)
	);
}
