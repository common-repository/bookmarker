<?php
/*
Plugin Name: Bookmarker
Plugin URI:  http://blog.noizeramp.com/tag/bookmarker/
Description: Posts the permalink to several social bookmarks services (del.icio.us, simpy) with title as description, categories as tags and excerpt as extended description.
Version:     0.4
Author:      Aleksey Gureev
Author URI:  http://blog.noizeramp.com/
*/

/*  Copyright 2004 Aleksey Gureev (spyromus@noizeramp.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

define('NCG_DELICIOUS_ID', 'ncg_delicious');
define('NCG_BOOKMARKER_ID', 'ncg_bookmarker');

if (function_exists('is_plugin_page') && is_plugin_page())
{
	include('ncg_bookmarker/ncg_bookmarker_options.php');
} else
{

/**
 * Collects information about post for posting services.
 */
function ncg_bookmarker_get_post_info($postID)
{
		$post = &wp_get_single_post($postID);

		$tagsArray = array();
		foreach ($post->post_category as $catID)
		{
			$catName = get_cat_name($catID);
			$tagsArray[] = $catName;
		}

		if (count($tagsArray) > 0)
		{
			// Build post data
			$link = urlencode(get_permalink($postID));
			$title = urlencode($post->post_title);
			$tags = urlencode(implode(' ', $tagsArray));
			
			// Build extended description
			$extended = strip_tags($post->post_excerpt?$post->post_excerpt:$post->post_content);
			if (strlen($extended) > 255) $extended = substr($extended, 0, 252) . "...";
			$extended = urlencode($extended);

			$info = array('link' => $link, 'title' => $title, 'tags' => $tags, 'extended' => $extended);
		}
		
		return $info;
}

/**
 * Posts the permalink to selected services.
 * Entry point for WordPress.
 */
function ncg_bookmarker_post($postID)
{
	global $post_cache;
	
	$options = get_option(NCG_BOOKMARKER_ID);
	if ($options)
	{
		if (!class_exists("HttpClient")) require_once('ncg_bookmarker/HttpClient.class.php');

		$info = ncg_bookmarker_get_post_info($postID);

		ncg_bookmarker_post_delicious($info, $options['delicious']);
		ncg_bookmarker_post_simpy($info, $options['simpy']);
	}
}

/**
 * Posts bookmark to delicious.
 *
 * @param info    post information (array).
 * @param options delicious options.
 */
function ncg_bookmarker_post_delicious($info, $options)
{	
	if (!$options) $options = get_option(NCG_DELICIOUS_ID);
	
	$doPosting = $options['doPosting'];
	$username = $options['username'];
	$password = $options['password'];

	if ($doPosting && $username && $password)
	{
		$link = $info['link'];
		$tags = $info['tags'];
		$title = $info['title'];
		$extended = $info['extended'];
		
		$get = '/api/posts/add?url=' . $link . '&tags=' . $tags . '&description=' . $title . '&extended=' . $extended;

		// Post to delicious
		$client = new HttpClient('del.icio.us');
		$client->setAuthorization($username, $password);
		$client->get($get);
	}
}

/**
 * Posts bookmark to simpy.
 *
 * @param info    post information (array).
 * @param options simpy options.
 */
function ncg_bookmarker_post_simpy($info, $options)
{	
	$doPosting = $options['doPosting'];
	$username = $options['username'];
	$password = $options['password'];

	if ($doPosting && $username && $password)
	{
		$link = $info['link'];
		$tags = strtr($info['tags'], '+', ',');
		$title = $info['title'];
		$extended = $info['extended'];
			
		$get = '/simpy/api/rest/SaveLink.do?title=' . $title . '&href=' . $link . '&accessType=1&tags=' . $tags . '&note=' . $extended;

		$client = new HttpClient('www.simpy.com');
		$client->setAuthorization($username, $password);
		$client->setDebug(true);
		$client->get($get);
	}
}

function ncg_bookmarker_add_menu()
{
	if (function_exists('add_options_page'))
	{
		add_options_page('Bookmarker Configuration', 'Bookmarker', 8, __FILE__);
	}
}

function ncg_bookmarker_edit_post($postID)
{
	global $post_cache, $wpdb;
	$post_cache[$postID] = &$wpdb->get_row("SELECT * FROM $wpdb->posts WHERE ID=$postID");
}
		
add_action('edit_post', 'ncg_bookmarker_edit_post', 1);
add_action('admin_head', 'ncg_bookmarker_add_menu');
add_action('publish_post', 'ncg_bookmarker_post');

}
?>
