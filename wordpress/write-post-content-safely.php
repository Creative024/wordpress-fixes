<?php
/**
 * Write post_content that contains inline <style> or <script> without WordPress removing it.
 *
 * Three silent traps this avoids (none of them shows an error):
 *
 * 1. wp_update_post() / wp_insert_post() run KSES when there is no logged-in user with
 *    unfiltered_html (WP-CLI without --user, cron, a script hit from a URL). KSES deletes
 *    every <style> and <script> block. The page keeps its markup and renders unstyled.
 * 2. wp_update_custom_css_post() goes through the same path and turns ">" into "&gt;",
 *    so every child-combinator selector in Additional CSS quietly stops matching.
 * 3. Servers running the OWASP Core Rule Set (ModSecurity) return 403 to REST requests
 *    whose body carries inline <style> or <script>. The request never reaches PHP.
 *
 * Usage: wp --user=YOUR_ADMIN_LOGIN eval-file write-post-content-safely.php 123 /path/to/new-content.html
 * The selected account must already have edit_post and unfiltered_html capabilities.
 *
 * @package WordPressFixes
 */

defined( 'ABSPATH' ) || exit;
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

/**
 * Save trusted content as the selected authorized user, with a verified private backup.
 *
 * @param int    $post_id Post ID.
 * @param string $html    New content.
 * @return string Path of the backup file.
 */
function wpfixes_write_post_content( $post_id, $html ) {
	$post = get_post( $post_id );
	if ( ! $post ) {
		WP_CLI::error( 'No such post.' );
	}
	if ( ! current_user_can( 'edit_post', $post_id ) || ! current_user_can( 'unfiltered_html' ) ) {
		WP_CLI::error( 'Use --user with an existing account allowed to edit this post and save unfiltered HTML.' );
	}

	// Keep a unique, private backup outside the WordPress web root; stop if it fails.
	$temp_dir = realpath( sys_get_temp_dir() );
	$web_root = realpath( ABSPATH );
	if ( ! $temp_dir || ! $web_root || strpos( $temp_dir . DIRECTORY_SEPARATOR, $web_root . DIRECTORY_SEPARATOR ) === 0 ) {
		WP_CLI::error( 'A temporary directory outside the web root is required.' );
	}
	$backup = tempnam( $temp_dir, 'wpfixes-post-' . $post_id . '-' );
	if ( false === $backup || ! chmod( $backup, 0600 ) || file_put_contents( $backup, $post->post_content, LOCK_EX ) !== strlen( $post->post_content ) ) {
		WP_CLI::error( 'Could not create the private backup. Content was not changed.' );
	}

	// Use the normal WordPress save path so revisions, hooks, modified dates and caches work.
	// Slashing once preserves literal backslashes and quotes in trusted source HTML.
	$result = wp_update_post( wp_slash( array( 'ID' => $post_id, 'post_content' => $html ) ), true );
	if ( is_wp_error( $result ) ) {
		WP_CLI::error( 'WordPress could not save the content. Backup: ' . $backup );
	}
	if ( get_post_field( 'post_content', $post_id, 'raw' ) !== $html ) {
		WP_CLI::error( 'A save filter changed the content; review the result before continuing. Backup: ' . $backup );
	}

	return $backup;
}

if ( defined( 'WP_CLI' ) && WP_CLI && ! empty( $args ) && count( $args ) === 2 ) {
	if ( ! ctype_digit( (string) $args[0] ) || (int) $args[0] < 1 || ! is_readable( $args[1] ) ) {
		WP_CLI::error( 'Supply a positive post ID and a readable trusted HTML file.' );
	}
	$wpfixes_html = file_get_contents( $args[1] ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	if ( false === $wpfixes_html ) {
		WP_CLI::error( 'Could not read the source file. Content was not changed.' );
	}
	$wpfixes_bak  = wpfixes_write_post_content( (int) $args[0], $wpfixes_html );
	WP_CLI::success( 'Content written. Backup: ' . $wpfixes_bak );
}
