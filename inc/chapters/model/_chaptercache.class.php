<?php
/**
 * This file implements the ChapterCache class.
 *
 * This file is part of the evoCore framework - {@link http://evocore.net/}
 * See also {@link https://github.com/b2evolution/b2evolution}.
 *
 * @license GNU GPL v2 - {@link http://b2evolution.net/about/gnu-gpl-license}
 *
 * @copyright (c)2003-2015 by Francois Planque - {@link http://fplanque.com/}
 *
 * @package evocore
 *
 * @author fplanque: Francois PLANQUE
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

load_class( 'generic/model/_genericcategorycache.class.php', 'GenericCategoryCache' );
load_class( 'chapters/model/_chapter.class.php', 'Chapter' );

/**
 * ChapterCache Class
 *
 * @package evocore
 */
class ChapterCache extends GenericCategoryCache
{
	/**
	 * Lazy filled index of url titles
	 */
	var $urlname_index = array();


	/**
	 * Constructor
	 */
	function ChapterCache()
	{
		parent::GenericCategoryCache( 'Chapter', false, 'T_categories', 'cat_', 'cat_ID', 'cat_name', 'blog_ID' );
	}


	/**
	 * Empty/reset the cache
	 */
	function clear()
	{
		$this->urlname_index = array();
		parent::clear();
	}


	/**
	 * Get an object from cache by ID
	 *
	 * Load the cache if necessary (all at once if allowed).
	 *
	 * @param integer ID of object to load
	 * @param boolean true if function should die on error
	 * @param boolean true if function should die on empty/null
 	 * @param integer|NULL NULL for all subsets
	 * @return Chapter Reference on cached object or false.
	 */
	function & get_by_ID( $req_ID, $halt_on_error = true, $halt_on_empty = true, $subset_ID = NULL )
	{
		global $DB, $Debuglog;

		if( empty($req_ID) )
		{
			if($halt_on_empty)
			{
				debug_die( "Requested Chapter from $this->dbtablename without ID!" );
			}
			$r = NULL;
			return $r;
		}

		if( !empty( $this->cache[ $req_ID ] ) )
		{ // Already in cache
			$Debuglog->add( "Accessing Chapter($req_ID) from cache", 'dataobjects' );
			return $this->cache[ $req_ID ];
		}
		elseif( !$this->all_loaded )
		{ // Not in cache, but not everything is loaded yet
			if( $this->load_all || is_null($subset_ID) )
			{ // It's ok to just load everything:
				$this->load_all();
			}
			else
			{ // Load just the requested object:
				$Debuglog->add( "Loading <strong>$this->objtype($req_ID)</strong> into cache", 'dataobjects' );
				// Note: $req_ID MUST be an unsigned integer. This is how DataObject works.
				$sql = "SELECT *
				          FROM T_categories
				         WHERE cat_ID = $req_ID
				           AND cat_blog_ID = ".$subset_ID;

				if( $row = $DB->get_row( $sql, OBJECT, 0, 'ChapterCache::get_by_ID()' ) )
				{
					if( ! $this->instantiate( $row ) )
					{
						$Debuglog->add( 'Could not add() object to cache!', 'dataobjects' );
					}
				}
				else
				{
					$Debuglog->add( 'Could not get DataObject by ID. Query: '.$sql, 'dataobjects' );
				}
			}
		}

		if( empty( $this->cache[ $req_ID ] ) )
		{ // Requested object does not exist
			// $Debuglog->add( 'failure', 'dataobjects' );
			if( $halt_on_error )
			{
				debug_die( "Requested $this->objtype does not exist!" );
			}
			$r = false;
			return $r;
		}

		return $this->cache[ $req_ID ];
	}


	/**
	 * Get an array with chapters ID that are located in the given blog from root to the given chapter
	 *
	 * @param integer Blog ID
	 * @param integer Chapter ID
	 * @return array Chapters ID
	 */
	function get_chapter_path( $blog_ID, $chapter_ID )
	{
		$ChapterCache = & get_ChapterCache();
		$ChapterCache->load_subset( $blog_ID );

		$chapter_path = array( $chapter_ID );
		if( isset( $ChapterCache->subset_cache[ $blog_ID ] ) )
		{
			$chapters = $ChapterCache->subset_cache[ $blog_ID ];
			if( isset( $chapters[ $chapter_ID ] ) )
			{
				$Chapter = $chapters[ $chapter_ID ];
				while( $Chapter->get( 'parent_ID' ) > 0 )
				{
					$chapter_path[] = $Chapter->get( 'parent_ID' );
					// Select a parent chapter
					$Chapter = $chapters[ $Chapter->get( 'parent_ID' ) ];
				}
			}
		}

		return $chapter_path;
	}


	/**
	 * Get an object from cache by urlname
	 *
	 * Load the cache if necessary (all at once if allowed).
	 *
	 * @param string ID of object to load
	 * @param boolean true if function should die on error
	 * @return reference on cached object
	 */
	function & get_by_urlname( $req_urlname, $halt_on_error = true )
	{
		global $DB, $Debuglog;

		if( !isset( $this->urlname_index[$req_urlname] ) )
		{ // not yet in cache:
			// Load just the requested object:
			$Debuglog->add( "Loading <strong>$this->objtype($req_urlname)</strong> into cache", 'dataobjects' );
			$sql = "SELECT *
			          FROM $this->dbtablename
			         WHERE cat_urlname = ".$DB->quote($req_urlname);
			$row = $DB->get_row( $sql );
			if( empty( $row ) )
			{	// Requested object does not exist
				if( $halt_on_error ) debug_die( "Requested $this->objtype does not exist!" );
				// put into index:
				$this->urlname_index[$req_urlname] = false;

				return $this->urlname_index[$req_urlname];
			}

			$this->instantiate( $row );

			// put into index:
			$this->urlname_index[$req_urlname] = & $this->cache[ $row->cat_ID ];
		}
		else
		{
			$Debuglog->add( "Retrieving <strong>$this->objtype($req_urlname)</strong> from cache" );
		}

		return $this->urlname_index[$req_urlname];
	}


	/**
	 * Load a list of chapter referenced by their urlname into the cache
	 *
	 * @param array of urlnames of Chapters to load
	 */
	function load_urlname_array( $req_array )
	{
		global $DB, $Debuglog;

		$req_list = $DB->quote( $req_array );
		$Debuglog->add( "Loading <strong>$this->objtype($req_list)</strong> into cache", 'dataobjects' );
		$sql = "SELECT * FROM $this->dbtablename WHERE cat_urlname IN ( $req_list )";
		$dbIDname = $this->dbIDname;
		$objtype = $this->objtype;
		foreach( $DB->get_results( $sql ) as $row )
		{
			$this->cache[ $row->$dbIDname ] = new $objtype( $row ); // COPY!

			// put into index:
			$this->urlname_index[$row->cat_urlname] = & $this->cache[ $row->$dbIDname ];

			$Debuglog->add( "Cached <strong>$this->objtype($row->cat_urlname)</strong>" );
		}
	}



	/**
	 * Load a keyed subset of the cache
	 *
 	 * @param integer|NULL NULL for all subsets
 	 * @param string Force 'order by' setting ('manual' = 'ORDER BY cat_order')
	 */
	function load_subset( $subset_ID, $force_order_by = '' )
	{
		global $DB, $Debuglog, $Settings;

		if( $this->all_loaded || isset( $this->loaded_subsets[$subset_ID] ) )
		{ // Already loaded
			return false;
		}

		// fp> TODO: This kills other subsets. BAD if we want to handle multiple subsets independently
		$this->clear( true );

		$Debuglog->add( 'ChapterCache - Loading <strong>chapters('.$subset_ID.')</strong> into cache', 'dataobjects' );
		$sql = 'SELECT *
					FROM T_categories
					WHERE cat_blog_ID = '.$subset_ID;

		foreach( $DB->get_results( $sql, OBJECT, 'Loading chapters('.$subset_ID.') into cache' ) as $row )
		{
			// Instantiate a custom object
			$this->instantiate( $row );
		}

		$this->loaded_subsets[$subset_ID] = true;

		return true;
	}


	/**
	 * Get chapters in the given subset
	 *
	 * @param integer subset ID
	 * @return array of Chapters
	 */
	function get_chapters_by_subset( $subset_ID )
	{
		if( ! $this->loaded_subsets[$subset_ID] )
		{
			$this->load_subset( $subset_ID );
		}

		return $this->subset_cache[$subset_ID];
	}


	/**
	 * Move a chapter and its descendants to a different collection
	 *
	 * @param integer
	 * @param integer
	 * @param integer
	 */
	function move_Chapter_subtree( $chapter_ID, $src_collection_ID, $dest_collection_ID )
	{
		/**
		 * @var DB
		 */
		global $DB;

		// Make sure children have been revealed for specific subset:
		$this->reveal_children( $src_collection_ID );

		// fp>We get the Chapter AFTER reveal_children, because something is wrong with reveal_children or get_by_ID
		// I don't know what right now, but if we get Chapter earlier, we'll be stuck with an old copy of it that does NOT have the children
		// TODO: find out what's freakin wrong
		$Chapter = $this->get_by_ID($chapter_ID);

		$chapters_to_move = array();
		// Get $chapters_to_move:
		$this->recurse_move_subtree( $Chapter, $chapters_to_move );
		// pre_dump( $chapters_to_move );

		$DB->begin();

		// Move to root:
		if( $parent_Chapter = $Chapter->get_parent_Chapter() )
		{	// Was not already at root, cut it and move it:
			// echo 'Move to root';
			$Chapter->set( 'parent_ID', NULL );
			$Chapter->dbupdate();
		}

		// Move Chapters to new Blog:
		$sql = 'UPDATE T_categories
							 SET cat_blog_ID = '.$dest_collection_ID.'
						 WHERE cat_blog_ID = '.$src_collection_ID /* extra security */ .'
						 	 AND cat_ID IN ('.implode( ',', $chapters_to_move ).')';
		$DB->query( $sql );

		$DB->commit();

		// Now the cache is badly screwed. Reseting it is fair enough, because this won't happen very often.
		$this->clear();
	}


	/**
	 * Support function for move_Chapter_subtree
	 *
	 * @param Chapter
	 * @param array
	 */
	function recurse_move_subtree( & $Chapter, & $list_array )
	{
		// Add this to the list:
		$list_array[] = $Chapter->ID;

		foreach( $Chapter->children as $child_Chapter )
		{
			$this->recurse_move_subtree( $child_Chapter, $list_array );
		}
	}


	/**
	 * Instanciate a new object within this cache
 	 *
 	 * @param object|NULL
 	 * @param integer|NULL subset to use for new object
	 */
	function & new_obj( $row = NULL, $subset_ID = NULL )
	{
		// Instantiate a custom object
		$Chapter = new Chapter( $row, $subset_ID ); // Copy

		return $Chapter;
	}
}

?>