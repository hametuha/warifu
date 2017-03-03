<?php
/** @var WP_Post $post */
defined( 'ABSPATH' ) or die();
$comments = get_comments( [
	'post_id' => $post->ID,
	'type'    => 'warifu_log',
	'number'  => null,
	'status'  => 'all',
] );
?>

<?php if ( ! $comments ) : ?>
<p class="description">
	<?php esc_html_e( 'No log found.', 'warifu' ) ?>
</p>
<?php else : ?>

<ul class="warifu-log">
	<?php foreach ( $comments as $comment ) {
		warifu_render_log( $comment );
	} ?>
</ul>

<?php endif; ?>
