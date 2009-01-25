<?
/*
Plugin Name: msHTMLspecialChars
Plugin URI: http://www.zauberpage.de/show-all-html-spezial-chars-in-comments.html
Description: Show all HTML special chars in your comments
Version: 0.2
Author: Maik Schindler
Author URI: http://www.zauberpage.de
*/

$installpath = get_option('siteurl').'/wp-content/plugins/show-all-html-spezial-chars-in-comments';
if(!is_admin()) return;
add_filter('comment_text', 'msHTMLspecialchars');
function msHTMLspecialchars()
{
	global $comment;
	echo nl2br(htmlspecialchars(get_comment_text()));
}



 
/**
 * add dashboard widget with a funktion wp_add_dashboard_widget()
 */
function ms_wp_dashboard_setup() {
	wp_add_dashboard_widget( 'ms_wp_dashboard_comments', __( 'Recent Comments' ).' Plugin', 'ms_wp_dashboard_recent_comments' );
}
 
/**
 * view widget in dashbord
 */
add_action('wp_dashboard_setup', 'ms_wp_dashboard_setup');

/**
 * Display recent comments dashboard widget content.
 * copy from wp_dashboard_recent_comments()
 * @since unknown
 */
function ms_wp_dashboard_recent_comments() {
	global $wpdb;

	if ( current_user_can('edit_posts') )
		$allowed_states = array('0', '1');
	else
		$allowed_states = array('1');

	// Select all comment types and filter out spam later for better query performance.
	$comments = array();
	$start = 0;

	while ( count( $comments ) < 5 && $possible = $wpdb->get_results( "SELECT * FROM $wpdb->comments ORDER BY comment_date_gmt DESC LIMIT $start, 50" ) ) {

		foreach ( $possible as $comment ) {
			if ( count( $comments ) >= 5 )
				break;
			if ( in_array( $comment->comment_approved, $allowed_states ) )
				$comments[] = $comment;
		}

		$start = $start + 50;
	}

	if ( $comments ) :
?>

		<div id="the-comment-list" class="list:comment">
<?php
		foreach ( $comments as $comment )
			ms_wp_dashboard_recent_comments_row( $comment );
?>

		</div>

<?php
		if ( current_user_can('edit_posts') ) { ?>
			<p class="textright"><a href="edit-comments.php" class="button"><?php _e('View all'); ?></a></p>
<?php	}

		wp_comment_reply( -1, false, 'dashboard', false );

	else :
?>

	<p><?php _e( 'No comments yet.' ); ?></p>

<?php
	endif; // $comments;
}
function ms_wp_dashboard_recent_comments_row( &$comment, $show_date = true ) {
	$GLOBALS['comment'] =& $comment;

	$comment_post_url = get_edit_post_link( $comment->comment_post_ID );
	$comment_post_title = get_the_title( $comment->comment_post_ID );
	$comment_post_link = "<a href='$comment_post_url'>$comment_post_title</a>";
	$comment_link = '<a class="comment-link" href="' . get_comment_link() . '">#</a>';

	$delete_url = clean_url( wp_nonce_url( "comment.php?action=deletecomment&p=$comment->comment_post_ID&c=$comment->comment_ID", "delete-comment_$comment->comment_ID" ) );
	$approve_url = clean_url( wp_nonce_url( "comment.php?action=approvecomment&p=$comment->comment_post_ID&c=$comment->comment_ID", "approve-comment_$comment->comment_ID" ) );
	$unapprove_url = clean_url( wp_nonce_url( "comment.php?action=unapprovecomment&p=$comment->comment_post_ID&c=$comment->comment_ID", "unapprove-comment_$comment->comment_ID" ) );
	$spam_url = clean_url( wp_nonce_url( "comment.php?action=deletecomment&dt=spam&p=$comment->comment_post_ID&c=$comment->comment_ID", "delete-comment_$comment->comment_ID" ) );

	$actions = array();

	$actions_string = '';
	if ( current_user_can('edit_post', $comment->comment_post_ID) ) {
		$actions['approve'] = "<a href='$approve_url' class='dim:the-comment-list:comment-$comment->comment_ID:unapproved:e7e7d3:e7e7d3:new=approved vim-a' title='" . __( 'Approve this comment' ) . "'>" . __( 'Approve' ) . '</a>';
		$actions['unapprove'] = "<a href='$unapprove_url' class='dim:the-comment-list:comment-$comment->comment_ID:unapproved:e7e7d3:e7e7d3:new=unapproved vim-u' title='" . __( 'Unapprove this comment' ) . "'>" . __( 'Unapprove' ) . '</a>';
		$actions['edit'] = '<a href="comment.php?action=editcomment&amp;c={$comment->comment_ID}" title="'. __('Edit comment') .'">'. __('Edit') .'</a>';
		$actions['quickedit'] = '<a onclick="commentReply.open(\''.$comment->comment_ID.'\',\''.$comment->comment_post_ID.'\',\'edit\');return false;" class="vim-q" title="'.__('Quick Edit').'" href="#">' . __('Quick&nbsp;Edit') . '</a>';

		$actions['reply'] = '<a onclick="commentReply.open(\''.$comment->comment_ID.'\',\''.$comment->comment_post_ID.'\');return false;" class="vim-r hide-if-no-js" title="'.__('Reply to this comment').'" href="#">' . __('Reply') . '</a>';
		$actions['spam'] = "<a href='$spam_url' class='delete:the-comment-list:comment-$comment->comment_ID::spam=1 vim-s vim-destructive' title='" . __( 'Mark this comment as spam' ) . "'>" . _c( 'Spam|verb' ) . '</a>';
		$actions['delete'] = "<a href='$delete_url' class='delete:the-comment-list:comment-$comment->comment_ID delete vim-d vim-destructive'>" . __('Delete') . '</a>';

		$actions = apply_filters( 'comment_row_actions', $actions, $comment );

		$i = 0;
		foreach ( $actions as $action => $link ) {
			++$i;
			( ( ('approve' == $action || 'unapprove' == $action) && 2 === $i ) || 1 === $i ) ? $sep = '' : $sep = ' | ';

			// Reply and quickedit need a hide-if-no-js span
			if ( 'reply' == $action || 'quickedit' == $action )
				$action .= ' hide-if-no-js';

			$actions_string .= "<span class='$action'>$sep$link</span>";
		}
	}

?>

		<div id="comment-<?php echo $comment->comment_ID; ?>" <?php comment_class( array( 'comment-item', wp_get_comment_status($comment->comment_ID) ) ); ?>>
			<?php if ( !$comment->comment_type || 'comment' == $comment->comment_type ) : ?>

			<?php echo get_avatar( $comment, 50 ); ?>
			<h4 class="comment-meta"><?php printf( __( 'From %1$s on %2$s%3$s' ), '<cite class="comment-author">'. get_comment_author() .'</cite>'
								.(get_comment_author_url() ? ' <cite class="comment-author"><a href="'. get_comment_author_url() .'">'. get_comment_author_url() .'</a></cite>' : '' ), $comment_post_link." ".$comment_link, ' <span class="approve">' . __( '[Pending]' ) . '</span>' ); ?></h4>

			<?php
			else :
				switch ( $comment->comment_type ) :
				case 'pingback' :
					$type = __( 'Pingback' );
					break;
				case 'trackback' :
					$type = __( 'Trackback' );
					break;
				default :
					$type = ucwords( $comment->comment_type );
				endswitch;
				$type = wp_specialchars( $type );
			?>

			<h4 class="comment-meta"><?php printf( __( '%1$s on %2$s' ), "<strong>$type</strong>", $comment_post_link ); ?></h4>
			<p class="comment-author"><?php comment_author_link(); ?></p>

			<?php endif; // comment_type ?>
			<blockquote><p><?php msHTMLspecialchars(); ?></p></blockquote>
			<p class="row-actions"><?php echo $actions_string; ?></p>

			<div id="inline-<?php echo $comment->comment_ID; ?>" class="hidden">
				<textarea class="comment" rows="3" cols="10"><?php echo $comment->comment_content; ?></textarea>
				<div class="author-email"><?php echo attribute_escape( $comment->comment_author_email ); ?></div>
				<div class="author"><?php echo attribute_escape( $comment->comment_author ); ?></div>
				<div class="author-url"><?php echo attribute_escape( $comment->comment_author_url ); ?></div>
				<div class="comment_status"><?php echo $comment->comment_approved; ?></div>
			</div>

		</div>
<?php
}

?>