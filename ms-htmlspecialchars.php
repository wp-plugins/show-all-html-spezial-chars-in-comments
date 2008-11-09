<?
/*
Plugin Name: msHTMLspecialChars
Plugin URI: http://www.zauberpage.de/show-all-html-spezial-chars-in-comments.html
Description: Show all HTML spezial chars in comments
Version: 0.1
Author: Maik Schindler
Author URI: http://www.zauberpage.de
*/

$installpath = get_option('siteurl').'/wp-content/plugins/ms-htmlspecialchars-comments';
if(!is_admin()) return;
add_filter('comment_text', 'msHTMLspecialchars');
function msHTMLspecialchars()
{
	global $comment;
	echo nl2br(htmlspecialchars(get_comment_text()));
}

?>