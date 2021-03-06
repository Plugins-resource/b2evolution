<?php
/**
 * This is the BODY header include template.
 *
 * For a quick explanation of b2evo 2.0 skins, please start here:
 * {@link http://b2evolution.net/man/skin-structure}
 *
 * This is meant to be included in a page template.
 *
 * @package evoskins
 * @subpackage bootstrap_manual
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );


global $Settings, $disp, $cat;
?>

<div class="container">
	<div id="header" class="row<?php echo $Settings->get( 'site_skins_enabled' ) ? ' site_skins' : ''; ?>">
		<div class="col-md-12">

			<div class="PageTop">
			<?php
				// ------------------------- "Page Top" CONTAINER EMBEDDED HERE --------------------------
				// Display container and contents:
				skin_container( NT_('Page Top'), array(
						// The following params will be used as defaults for widgets included in this container:
						'block_start'         => '<div class="widget $wi_class$">',
						'block_end'           => '</div>',
						'block_display_title' => false,
						'list_start'          => '<ul>',
						'list_end'            => '</ul>',
						'item_start'          => '<li>',
						'item_end'            => '</li>',
					) );
				// ----------------------------- END OF "Page Top" CONTAINER -----------------------------
			?>
			</div>

			<?php
				// ------------------------- "Header" CONTAINER EMBEDDED HERE --------------------------
				// Display container and contents:
				skin_container( NT_('Header'), array(
						// The following params will be used as defaults for widgets included in this container:
						'block_start'       => '<div class="widget $wi_class$">',
						'block_end'         => '</div>',
						'block_title_start' => '<h1>',
						'block_title_end'   => '</h1>',
					) );
				// ----------------------------- END OF "Header" CONTAINER -----------------------------
			?>

		</div>
	</div>

	<?php
	global $hide_widget_container_menu;
	if( empty( $hide_widget_container_menu ) )
	{ // Display this widget container only when it is not disabled
	?>
	<div class="row">
		<div class="col-md-12">
			<ul class="nav nav-tabs">
			<?php
				// ------------------------- "Menu" CONTAINER EMBEDDED HERE --------------------------
				// Display container and contents:
				// Note: this container is designed to be a single <ul> list
				skin_container( NT_('Menu'), array(
						// The following params will be used as defaults for widgets included in this container:
						'block_start'         => '',
						'block_end'           => '',
						'block_display_title' => false,
						'list_start'          => '',
						'list_end'            => '',
						'item_start'          => '<li>',
						'item_end'            => '</li>',
						'item_selected_start' => '<li class="active">',
						'item_selected_end'   => '</li>',
						'item_title_before'   => '',
						'item_title_after'    => '',
					) );
				// ----------------------------- END OF "Menu" CONTAINER -----------------------------
			?>
			</ul>
		</div>
	</div>
	<?php } ?>

	<div class="row">
		<div class="<?php echo $Skin->is_left_navigation_visible() ? 'col-md-9 pull-right' : 'col-md-12' ?>">
		<!-- =================================== START OF MAIN AREA =================================== -->
		<?php
			if( ! in_array( $disp, array( 'login', 'lostpassword', 'register', 'activateinfo' ) ) )
			{ // Don't display the messages here because they are displayed inside wrapper to have the same width as form
				// ------------------------- MESSAGES GENERATED FROM ACTIONS -------------------------
				messages( array(
						'block_start' => '<div class="action_messages">',
						'block_end'   => '</div>',
					) );
				// --------------------------------- END OF MESSAGES ---------------------------------
			}	

			if( ! empty( $cat ) )
			{ // Display breadcrumbs if some category is selected
				skin_widget( array(
						// CODE for the widget:
						'widget' => 'breadcrumb_path',
						// Optional display params
						'block_start'      => '<ol class="breadcrumb">',
						'block_end'        => '</ol>',
						'separator'        => '',
						'item_mask'        => '<li><a href="$url$">$title$</a></li>',
						'item_active_mask' => '<li class="active">$title$</li>',
					) );
			}

			// ------------------------ TITLE FOR THE CURRENT REQUEST ------------------------
			request_title( array(
					'title_before'      => '<h1 class="page_title">',
					'title_after'       => '</h1>',
					'title_single_disp' => false,
					'title_page_disp'   => false,
					'format'            => 'htmlbody',
					'category_text'     => '',
					'categories_text'   => '',
					'catdir_text'       => '',
					'front_text'        => '',
					'posts_text'        => '',
					'edit_text_create'  => T_('Post a new topic'),
					'edit_text_update'  => T_('Edit post'),
					'register_text'     => '',
					'login_text'        => '',
					'lostpassword_text' => '',
					'account_activation' => '',
					'msgform_text'      => '',
				) );
			// ----------------------------- END OF REQUEST TITLE ----------------------------