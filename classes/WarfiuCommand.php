<?php
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

/**
 * CLI tool for warifu
 *
 * @since 1.0.0
 * @package warifu
 */
class WarifuCommand extends WP_Cli_Command {

	/**
	 * Validate license
	 *
	 * ## Example
	 *
	 * wp warifu validate
	 */
	public function validate() {
		WP_CLI::line( 'Start validation. This process take while...' );
		$start = current_time( true );
		do_action( 'warifu_do_cron' );
		$end = current_time( true );
		WP_CLI::success( sprintf( 'Done! (%s seconds)', round( ( $end - $start ) / 1000 ) ) );
	}
}
