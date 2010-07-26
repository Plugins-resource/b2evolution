<?php
/**
 * This file is part of the evoCore framework - {@link http://evocore.net/}
 * See also {@link http://sourceforge.net/projects/evocms/}.
 *
 * @copyright (c)2009 by Francois PLANQUE - {@link http://fplanque.net/}
 * Parts of this file are copyright (c)2009 by The Evo Factory - {@link http://www.evofactory.com/}.
 *
 * {@internal License choice
 * - If you have received this file as part of a package, please find the license.txt file in
 *   the same folder or the closest folder above for complete license terms.
 * - If you have received this file individually (e-g: from http://evocms.cvs.sourceforge.net/)
 *   then you must choose one of the following licenses before using the file:
 *   - GNU General Public License 2 (GPL) - http://www.opensource.org/licenses/gpl-license.php
 *   - Mozilla Public License 1.1 (MPL) - http://www.opensource.org/licenses/mozilla1.1.php
 * }}
 *
 * {@internal Open Source relicensing agreement:
 * The Evo Factory grants Francois PLANQUE the right to license
 * The Evo Factory's contributions to this file and the b2evolution project
 * under any OSI approved OSS license (http://www.opensource.org/licenses/).
 * }}
 *
 * @package evocore
 *
 * {@internal Below is a list of authors who have contributed to design/coding of this file: }}
 * @author evofactory-test
 * @author fplanque: Francois Planque.
 *
 * @version $Id$
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

// Load Userfield class:
load_class( 'users/model/_userfield.class.php', 'Userfield' );

/**
 * @var User
 */
global $current_User;

// Check minimum permission:
$current_User->check_perm( 'users', 'view', true );

// Set options path:
$AdminUI->set_path( 'users', 'usersettings', 'userfields' );

// Get action parameter from request:
param_action();

if( param( 'ufdf_ID', 'integer', '', true) )
{// Load userfield from cache:
	$UserfieldCache = & get_UserFieldCache();
	if( ($edited_Userfield = & $UserfieldCache->get_by_ID( $ufdf_ID, false )) === false )
	{	// We could not find the user field to edit:
		unset( $edited_Userfield );
		forget_param( 'ufdf_ID' );
		$Messages->add( sprintf( T_('Requested &laquo;%s&raquo; object does not exist any longer.'), T_('User field') ), 'error' );
		$action = 'nil';
	}
}


switch( $action )
{

	case 'new':
		// Check permission:
		$current_User->check_perm( 'users', 'edit', true );

		if( ! isset($edited_Userfield) )
		{	// We don't have a model to use, start with blank object:
			$edited_Userfield = new Userfield();
		}
		else
		{	// Duplicate object in order no to mess with the cache:
			$edited_Userfield = duplicate( $edited_Userfield ); // PHP4/5 abstraction
			$edited_Userfield->ID = 0;
		}
		break;

	case 'edit':
		// Check permission:
		$current_User->check_perm( 'users', 'edit', true );

		// Make sure we got an ufdf_ID:
		param( 'ufdf_ID', 'integer', true );
 		break;

	case 'create': // Record new Userfield
	case 'create_new': // Record Userfield and create new
	case 'create_copy': // Record Userfield and create similar
		// Insert new user field...:
		$edited_Userfield = new Userfield();

		// Check that this action request is not a CSRF hacked request:
		$Session->assert_received_crumb( 'userfield' );

		// Check permission:
		$current_User->check_perm( 'users', 'edit', true );

		// load data from request
		if( $edited_Userfield->load_from_Request() )
		{	// We could load data from form without errors:

			// While inserting into DB, ID property of Userfield object will be set to autogenerated ID
			// So far as we set ID manualy, we need to preserve this value
			// When assignment of wrong value will be fixed, we can skip this
			$entered_userfield_id = $edited_Userfield->ID;

			// Insert in DB:
			$DB->begin();
			// because of manual assigning ID,
			// member function Userfield::dbexists() is overloaded for proper functionality
			$q = $edited_Userfield->dbexists();
			if($q)
			{	// We have a duplicate entry:

				param_error( 'ufdf_ID',
					sprintf( T_('This user field already exists. Do you want to <a %s>edit the existing user field</a>?'),
						'href="?ctrl=userfields&amp;action=edit&amp;ufdf_ID='.$q.'"' ) );
			}
			else
			{
				$edited_Userfield->dbinsert();
				$Messages->add( T_('New User field created.'), 'success' );
			}
			$DB->commit();

			if( empty($q) )
			{	// What next?
			switch( $action )
				{
					case 'create_copy':
						// Redirect so that a reload doesn't write to the DB twice:
						header_redirect( '?ctrl=userfields&action=new&ufdf_ID='.$entered_userfield_id, 303 ); // Will EXIT
						// We have EXITed already at this point!!
						break;
					case 'create_new':
						// Redirect so that a reload doesn't write to the DB twice:
						header_redirect( '?ctrl=userfields&action=new', 303 ); // Will EXIT
						// We have EXITed already at this point!!
						break;
					case 'create':
						// Redirect so that a reload doesn't write to the DB twice:
						header_redirect( '?ctrl=userfields', 303 ); // Will EXIT
						// We have EXITed already at this point!!
						break;
				}
			}
		}
		break;

	case 'update':
		// Edit user field form...:

		// Check that this action request is not a CSRF hacked request:
		$Session->assert_received_crumb( 'userfield' );

		// Check permission:
		$current_User->check_perm( 'users', 'edit', true );

		// Make sure we got an ufdf_ID:
		param( 'ufdf_ID', 'integer', true );

		// load data from request
		if( $edited_Userfield->load_from_Request() )
		{	// We could load data from form without errors:

			// Update in DB:
			$DB->begin();

			$edited_Userfield->dbupdate();
			$Messages->add( T_('User field updated.'), 'success' );

			$DB->commit();

			header_redirect( '?ctrl=userfields', 303 ); // Will EXIT
			// We have EXITed already at this point!!
		}
		break;

	case 'delete':
		// Delete user field:

		// Check that this action request is not a CSRF hacked request:
		$Session->assert_received_crumb( 'userfield' );

		// Check permission:
		$current_User->check_perm( 'users', 'edit', true );

		// Make sure we got an ufdf_ID:
		param( 'ufdf_ID', 'integer', true );

		if( param( 'confirm', 'integer', 0 ) )
		{ // confirmed, Delete from DB:
			$msg = sprintf( T_('User field &laquo;%s&raquo; deleted.'), $edited_Userfield->dget('name') );
			$edited_Userfield->dbdelete( true );
			unset( $edited_Userfield );
			forget_param( 'ufdf_ID' );
			$Messages->add( $msg, 'success' );
			// Redirect so that a reload doesn't write to the DB twice:
			header_redirect( '?ctrl=userfields', 303 ); // Will EXIT
			// We have EXITed already at this point!!

		}
		else
		{	// not confirmed, Check for restrictions:
			if( ! $edited_Userfield->check_delete( sprintf( T_('Cannot delete user field &laquo;%s&raquo;'), $edited_Userfield->dget('name') ) ) )
			{	// There are restrictions:
				$action = 'view';
			}
		}
		break;

}

$AdminUI->breadcrumbpath_init( false );  // fp> I'm playing with the idea of keeping the current blog in the path here...
$AdminUI->breadcrumbpath_add( T_('Users'), '?ctrl=users' );
$AdminUI->breadcrumbpath_add( T_('Settings'), '?ctrl=usersettings' );
$AdminUI->breadcrumbpath_add( T_('User fields configuration'), '?ctrl=userfields' );

// Display <html><head>...</head> section! (Note: should be done early if actions do not redirect)
$AdminUI->disp_html_head();

// Display title, menu, messages, etc. (Note: messages MUST be displayed AFTER the actions)
$AdminUI->disp_body_top();

$AdminUI->disp_payload_begin();

/**
 * Display payload:
 */
switch( $action )
{
	case 'nil':
		// Do nothing
		break;


	case 'delete':
		// We need to ask for confirmation:
		$edited_Userfield->confirm_delete(
				sprintf( T_('Delete user field &laquo;%s&raquo;?'), $edited_Userfield->dget('name') ),
				'userfield', $action, get_memorized( 'action' ) );
		/* no break */
	case 'new':
	case 'create':
	case 'create_new':
	case 'create_copy':
	case 'edit':
	case 'update':	// we return in this state after a validation error
		$AdminUI->disp_view( 'users/views/_userfield.form.php' );
		break;


	default:
		// No specific request, list all user fields:
		// Cleanup context:
		forget_param( 'ufdf_ID' );
		// Display user fields list:
		$AdminUI->disp_view( 'users/views/_userfields.view.php' );
		break;

}

$AdminUI->disp_payload_end();

// Display body bottom, debug info and close </html>:
$AdminUI->disp_global_footer();

/*
 * $ Log: userfields.ctrl.php,v $
 * Revision 1.5  2009/09/16 18:29:35  fplanque
 * doc
 *
 * Revision 1.4  2009/09/16 18:27:19  fplanque
 * Readded with -kkv option
 *
 * efy-sergey
 *
 * Revision 1.1  2009/09/11 18:34:06  fplanque
 * userfields editing module.
 * needs further cleanup but I think it works.
 *
 */
?>
