<?php
/**
 * This file implements a class derived of the generic Skin class in order to provide custom code for
 * the skin in this folder.
 *
 * This file is part of the b2evolution project - {@link http://b2evolution.net/}
 *
 * @package skins
 * @subpackage evocamp
 *
 * @version $Id$
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

/**
 * Specific code for this skin.
 *
 * ATTENTION: if you make a new skin you have to change the class name below accordingly
 */
class evocamp_Skin extends Skin
{
  /**
	 * Get default name for the skin.
	 * Note: the admin can customize it.
	 */
	function get_default_name()
	{
		return 'evoCamp';
	}


  /**
	 * Get default type for the skin.
	 */
	function get_default_type()
	{
		return 'normal';
	}


	/**
	 * Get ready for displaying the skin.
	 *
	 * This may register some CSS or JS...
	 */
	function display_init()
	{
		// call parent:
		parent::display_init();

		// Colorbox (a lightweight Lightbox alternative) allows to zoom on images and do slideshows with groups of images:
		if ($this->get_setting("colorbox")) 
		{
			require_js_helper( 'colorbox' );
		}
	}
	
	/**
	 * Get definitions for editable params
	 *
	 * @see Plugin::GetDefaultSettings()
	 * @param local params like 'for_editing' => true
	 */
	function get_param_definitions( $params )
	{
		$r = array_merge( array(
				'colorbox' => array(
					'label' => T_('Colorbox enabled'),
					'note' => T_('Check if colorbox enabled'),
					'defaultvalue' => true,
					'type'	=>	'checkbox',
					'for_editing'	=>	true,
				)
			), parent::get_param_definitions( $params )	);

		return $r;
	}

}

/*
 * $Log$
 * Revision 1.3  2011/09/09 23:26:47  lxndral
 * Add _skins.class.php to all skins  (Easy task)
 *
 * Revision 1.2  2011/09/08 13:42:37  lxndral
 * Add _skins.class.php to all skins  (Easy task)
 *
 * Revision 1.1  2011/09/04 02:30:20  fplanque
 * colorbox integration (MIT license)
 *
 */
?>