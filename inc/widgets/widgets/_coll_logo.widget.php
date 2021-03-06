<?php
/**
 * This file implements the xyz Widget class.
 *
 * This file is part of the evoCore framework - {@link http://evocore.net/}
 * See also {@link https://github.com/b2evolution/b2evolution}.
 *
 * @license GNU GPL v2 - {@link http://b2evolution.net/about/gnu-gpl-license}
 *
 * @copyright (c)2003-2015 by Francois Planque - {@link http://fplanque.com/}
 *
 * @package evocore
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

load_class( 'widgets/model/_widget.class.php', 'ComponentWidget' );

/**
 * ComponentWidget Class
 *
 * A ComponentWidget is a displayable entity that can be placed into a Container on a web page.
 *
 * @package evocore
 */
class coll_logo_Widget extends ComponentWidget
{
	/**
	 * Constructor
	 */
	function coll_logo_Widget( $db_row = NULL )
	{
		// Call parent constructor:
		parent::ComponentWidget( $db_row, 'core', 'coll_logo' );
	}


	/**
	 * Get name of widget
	 */
	function get_name()
	{
		return T_('Image / Blog logo');
	}


	/**
	 * Get a very short desc. Used in the widget list.
	 *
	 * MAY be overriden by core widgets. Example: menu link widget.
	 */
	function get_short_desc()
	{
		$this->load_param_array();
		if( !empty($this->param_array['logo_file'] ) )
		{
			return $this->param_array['logo_file'];
		}
		else
		{
			return $this->get_name();
		}
	}


  /**
	 * Get short description
	 */
	function get_desc()
	{
		return T_('Include an image/logo from the blog\'s file root.');
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
				'logo_file' => array(
					'label' => T_('Image filename'),
					'note' => T_('The image/logo file must be uploaded to the root of the Blog\'s media dir'),
					'defaultvalue' => 'logo.png',
					'valid_pattern' => array( 'pattern'=>'~^[a-z0-9_\-][a-z0-9_.\-]*$~i',
																		'error'=>T_('Invalid filename.') ),
				),
			), parent::get_param_definitions( $params )	);

		return $r;

	}


	/**
	 * Display the widget!
	 *
	 * @param array MUST contain at least the basic display params
	 */
	function display( $params )
	{
		global $Blog;

		$this->init_display( $params );

		// Collection logo:
		echo $this->disp_params['block_start'];

		$title = '<a href="'.$Blog->get( 'url', 'raw' ).'">'
							.'<img src="'.$Blog->get_media_url().$this->disp_params['logo_file'].'" alt="'.$Blog->dget( 'name', 'htmlattr' ).'" />'
							.'</a>';
		$this->disp_title( $title );

		echo $this->disp_params['block_end'];

		return true;
	}
}

?>