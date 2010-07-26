<?php
/**
 * This file implements Comment handling functions.
  *
 * This file is part of the b2evolution/evocms project - {@link http://b2evolution.net/}.
 * See also {@link http://sourceforge.net/projects/evocms/}.
 *
 * @copyright (c)2003-2010 by Francois PLANQUE - {@link http://fplanque.net/}.
 * Parts of this file are copyright (c)2004-2005 by Daniel HAHLER - {@link http://thequod.de/contact}.
 *
 * @license http://b2evolution.net/about/license.html GNU General Public License (GPL)
 *
 * {@internal Open Source relicensing agreement:
 * Daniel HAHLER grants Francois PLANQUE the right to license
 * Daniel HAHLER's contributions to this file and the b2evolution project
 * under any OSI approved OSS license (http://www.opensource.org/licenses/).
 * }}
 *
 * @package evocore
 *
 * @todo implement CommentCache based on LinkCache
 *
 * {@internal Below is a list of authors who have contributed to design/coding of this file: }}
 * @author cafelog (team)
 * @author blueyed: Daniel HAHLER.
 * @author fplanque: Francois PLANQUE.
 *
 * @version $Id$
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

load_class( 'comments/model/_comment.class.php', 'Comment' );

/**
 * Generic comments/trackbacks/pingbacks counting
 *
 * @todo check this in a multiblog page...
 * @todo This should support visibility: at least in the default front office (_feedback.php), there should only the number of visible comments/trackbacks get used ({@link Item::feedback_link()}).
 *
 * @param integer
 * @param string what to count
 */
function generic_ctp_number( $post_id, $mode = 'comments', $status = 'published' )
{
	global $DB, $debug, $postdata, $cache_ctp_number, $preview;

	if( $preview )
	{ // we are in preview mode, no comments yet!
		return 0;
	}

	/*
	 * Make sure cache is loaded for current display list:
	 */
	if( !isset($cache_ctp_number) )
	{
		global $postIDlist, $postIDarray;

		// if( $debug ) echo "LOADING generic_ctp_number CACHE for posts: $postIDlist<br />";

		if( ! empty( $postIDlist ) )	// This can happen when displaying a featured post of something that's not in the MainList
		{
			foreach( $postIDarray as $tmp_post_id)
			{	// Initializes each post to nocount!
				$cache_ctp_number[$tmp_post_id] = array(
						'comments' => array( 'published' => 0, 'draft' => 0, 'deprecated' => 0, 'total' => 0 ),
						'trackbacks' => array( 'published' => 0, 'draft' => 0, 'deprecated' => 0, 'total' => 0 ),
						'pingbacks' => array( 'published' => 0, 'draft' => 0, 'deprecated' => 0, 'total' => 0 ),
						'feedbacks' => array( 'published' => 0, 'draft' => 0, 'deprecated' => 0, 'total' => 0 )
					);
			}

			$query = 'SELECT comment_post_ID, comment_type, comment_status, COUNT(*) AS type_count
								 FROM T_comments
								 WHERE comment_post_ID IN ('.$postIDlist.')
								 GROUP BY comment_post_ID, comment_type, comment_status';

			foreach( $DB->get_results( $query ) as $row )
			{
				// detail by status, tyep and post:
				$cache_ctp_number[$row->comment_post_ID][$row->comment_type.'s'][$row->comment_status] = $row->type_count;

				// Total for type on post:
				$cache_ctp_number[$row->comment_post_ID][$row->comment_type.'s']['total'] += $row->type_count;

				// Total for status on post:
				$cache_ctp_number[$row->comment_post_ID]['feedbacks'][$row->comment_status] += $row->type_count;

				// Total for post:
				$cache_ctp_number[$row->comment_post_ID]['feedbacks']['total'] += $row->type_count;
			}
		}
	}
	/*	else
	{
		echo "cache set";
	}*/


	if( !isset($cache_ctp_number[$post_id]) )
	{ // this should be extremely rare...
		// echo "CACHE not set for $post_id";

		// Initializes post to nocount!
		$cache_ctp_number[intval($post_id)] = array(
				'comments' => array( 'published' => 0, 'draft' => 0, 'deprecated' => 0, 'total' => 0 ),
				'trackbacks' => array( 'published' => 0, 'draft' => 0, 'deprecated' => 0, 'total' => 0 ),
				'pingbacks' => array( 'published' => 0, 'draft' => 0, 'deprecated' => 0, 'total' => 0 ),
				'feedbacks' => array( 'published' => 0, 'draft' => 0, 'deprecated' => 0, 'total' => 0 )
			);

		$query = 'SELECT comment_post_ID, comment_type, comment_status, COUNT(*) AS type_count
							  FROM T_comments
							 WHERE comment_post_ID = '.intval($post_id).'
							 GROUP BY comment_post_ID, comment_type, comment_status';

		foreach( $DB->get_results( $query ) as $row )
		{
			// detail by status, tyep and post:
			$cache_ctp_number[$row->comment_post_ID][$row->comment_type.'s'][$row->comment_status] = $row->type_count;

			// Total for type on post:
			$cache_ctp_number[$row->comment_post_ID][$row->comment_type.'s']['total'] += $row->type_count;

			// Total for status on post:
			$cache_ctp_number[$row->comment_post_ID]['feedbacks'][$row->comment_status] += $row->type_count;

			// Total for post:
			$cache_ctp_number[$row->comment_post_ID]['feedbacks']['total'] += $row->type_count;
		}
	}

	if( ($mode != 'comments') && ($mode != 'trackbacks') && ($mode != 'pingbacks') )
	{
		$mode = 'feedbacks';
	}

	if( ($status != 'published') && ($status != 'draft') && ($status != 'deprecated') )
	{
		$status = 'total';
	}

	// pre_dump( $cache_ctp_number[$post_id] );

	return $cache_ctp_number[$post_id][$mode][$status];
}


/**
 * Get a Comment by ID. Exits if the requested comment does not exist!
 *
 * @param integer
 * @return Comment
 */
function & Comment_get_by_ID( $comment_ID )
{
	$CommentCache = & get_CommentCache();
	return $CommentCache->get_by_ID( $comment_ID );
}


/*
 * last_comments_title(-)
 *
 * @movedTo _obsolete092.php
 */


/***** Comment tags *****/

/**
 * comments_number(-)
 *
 * @deprecated deprecated by {@link Item::feedback_link()}, used in _edit_showposts.php
 */
function comments_number( $zero='#', $one='#', $more='#', $post_ID = NULL )
{
	if( $zero == '#' ) $zero = T_('Leave a comment');
	if( $one == '#' ) $one = T_('1 comment');
	if( $more == '#' ) $more = T_('%d comments');

	// original hack by dodo@regretless.com
	if( empty( $post_ID ) )
	{
		global $id;
		$post_ID = $id;
	}
	$number = generic_ctp_number( $post_ID, 'comments' );
	if ($number == 0)
	{
		$blah = $zero;
	}
	elseif ($number == 1)
	{
		$blah = $one;
	}
	elseif ($number  > 1)
	{
		$n = $number;
		$more = str_replace('%d', $n, $more);
		$blah = $more;
	}
	echo $blah;
}

/**
 * Get advanced perm for comment moderation on this blog
 * 
 * @param int blog ID
 * @return array statuses - current user has permission to moderate comments with these statuses
 */
function get_allowed_statuses( $blog )
{
	global $current_User;
	$statuses = array();

	if( $current_User->check_perm( 'blog_draft_comments', 'edit', false, $blog ) )
	{
		$statuses[] = 'draft';
	}

	if( $current_User->check_perm( 'blog_published_comments', 'edit', false, $blog ) )
	{
		$statuses[] = 'published';
	}

	if( $current_User->check_perm( 'blog_deprecated_comments', 'edit', false, $blog ) )
	{
		$statuses[] = 'deprecated';
	}

	return $statuses;
}

/**
 * Create comment form submit buttons
 * 
 * Note: Publsih in only displayed when comment is in draft status
 *
 * @param $Form
 * @param $edited_Comment
 * 
 */
function echo_comment_buttons( $Form, $edited_Comment )
{
	global $Blog, $current_User;
	
	// ---------- SAVE ------------
	$Form->submit( array( 'actionArray[update]', T_('Save!'), 'SaveButton' ) );
	
	// ---------- PUBLISH ---------
	if( $edited_Comment->status == 'draft'
			&& $current_User->check_perm( 'blog_post!published', 'edit', false, $Blog->ID )	// TODO: if we actually set the primary cat to another blog, we may still get an ugly perm die
			&& $current_User->check_perm( 'edit_timestamp', 'edit', false ) )
	{
		 $publish_style = 'display: inline';
	}
	else
	{
		$publish_style = 'display: none';
	}
	$Form->submit( array(
		'actionArray[update_publish]',
		/* TRANS: This is the value of an input submit button */ T_('Publish!'),
		'SaveButton',
		'',
		$publish_style
	) );
}


/**
 * JS Behaviour: Output JavaScript code to dynamically show or hide the "Publish!" 
 * button depending on the selected comment status.
 *
 * This function is used by the comment edit screen.
 */
function echo_comment_publishbt_js()
{
	global $next_action;
	?>
	<script type="text/javascript">
	jQuery( '#commentform_visibility input[type=radio]' ).click( function()
	{
		var commentpublish_btn = jQuery( '.edit_actions input[name=actionArray[update_publish]]' );

		if( this.value != 'draft' )
		{	// Hide the "Publish NOW !" button:
			commentpublish_btn.css( 'display', 'none' );
		}
		else
		{	// Show the button:
			commentpublish_btn.css( 'display', 'inline' );
		}
	} );
	</script>
	<?php
}

/*
 * $Log$
 * Revision 1.12  2010/07/26 06:52:16  efy-asimo
 * MFB v-4-0
 *
 * Revision 1.11  2010/06/01 11:33:19  efy-asimo
 * Split blog_comments advanced permission (published, deprecated, draft)
 * Use this new permissions (Antispam tool,when edit/delete comments)
 *
 * Revision 1.10  2010/03/11 10:34:53  efy-asimo
 * Rewrite CommentList to CommentList2 task
 *
 * Revision 1.9  2010/02/28 23:38:40  fplanque
 * minor changes
 *
 * Revision 1.8  2010/02/08 17:52:13  efy-yury
 * copyright 2009 -> 2010
 *
 * Revision 1.7  2010/01/29 23:07:04  efy-asimo
 * Publish Comment button
 *
 * Revision 1.6  2009/09/14 12:46:36  efy-arrin
 * Included the ClassName in load_class() call with proper UpperCase
 *
 * Revision 1.5  2009/03/27 02:08:29  sam2kb
 * Minor. Believe it or not, but this little thing produced MYSQL error on php4 because the $postIDlist was always empty.
 *
 * Revision 1.4  2009/03/08 23:57:42  fplanque
 * 2009
 *
 * Revision 1.3  2009/01/21 18:23:26  fplanque
 * Featured posts and Intro posts
 *
 * Revision 1.2  2008/01/21 09:35:27  fplanque
 * (c) 2008
 *
 * Revision 1.1  2007/06/25 10:59:41  fplanque
 * MODULES (refactored MVC)
 *
 * Revision 1.11  2007/05/09 00:58:55  fplanque
 * massive cleanup of old functions
 *
 * Revision 1.10  2007/04/26 00:11:08  fplanque
 * (c) 2007
 *
 * Revision 1.9  2007/01/26 04:49:17  fplanque
 * cleanup
 *
 * Revision 1.8  2006/08/21 16:07:43  fplanque
 * refactoring
 *
 * Revision 1.7  2006/08/19 02:15:07  fplanque
 * Half kille dthe pingbacks
 * Still supported in DB in case someone wants to write a plugin.
 *
 * Revision 1.6  2006/07/04 17:32:29  fplanque
 * no message
 *
 * Revision 1.5  2006/06/22 21:58:34  fplanque
 * enhanced comment moderation
 *
 * Revision 1.4  2006/05/04 03:08:12  blueyed
 * todo
 *
 * Revision 1.3  2006/04/22 16:30:00  blueyed
 * cleanup
 *
 * Revision 1.2  2006/03/12 23:08:58  fplanque
 * doc cleanup
 *
 * Revision 1.1  2006/02/23 21:11:57  fplanque
 * File reorganization to MVC (Model View Controller) architecture.
 * See index.hml files in folders.
 * (Sorry for all the remaining bugs induced by the reorg... :/)
 */
?>