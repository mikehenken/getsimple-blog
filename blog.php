<?php
$thisfile = basename(__FILE__, ".php");
define('BLOGFILE', $thisfile);
define('BLOGPLUGINNAME', i18n_r(BLOGFILE.'/PLUGIN_TITLE'));
require_once("blog/inc/common.php");

# add in this plugin's language file
if(file_exists(BLOGSETTINGS))
{
	$settings_lang = getXML(BLOGSETTINGS);
	$LANG = $settings_lang->lang;
}
else
{
	$LANG = "en_US";
}
i18n_merge($thisfile) || i18n_merge($LANG);

# register plugin
register_plugin(
	$thisfile, // ID of plugin, should be filename minus php
	i18n_r(BLOGFILE.'/PLUGIN_TITLE'), 	
	'1.4.2', 		
	'Mike Henken',
	'http://michaelhenken.com/', 
	i18n_r(BLOGFILE.'/PLUGIN_DESC'),
	'pages',
	'blog_admin_controller'  
);
add_action('pages-sidebar','createSideMenu',array($thisfile, i18n_r(BLOGFILE.'/PLUGIN_SIDE')));

/** 
* Handles conditionals for admin functions
* 
* @return void
*/  
function blog_admin_controller()
{
	$Blog = new Blog;
	getBlogUserPermissions();
	global $blogUserPermissions;
	showAdminNav();
	if(isset($_GET['edit_post']) && $blogUserPermissions['blogeditpost'] == true)
	{
		editPost($_GET['edit_post']);
	}
	elseif(isset($_GET['create_post']) && $blogUserPermissions['blogcreatepost'] == true)
	{
		editPost();
	}
	elseif(isset($_GET['categories']) && $blogUserPermissions['blogcategories'] == true)
	{
		if(isset($_GET['edit_category']))
		{
			$add_category = $Blog->saveCategory($_POST['new_category']);
			if($add_category == true)
			{
				echo '<div class="updated">';
				i18n(BLOGFILE.'/CATEGORY_ADDED');
				echo '</div>';
			}
			else
			{
				echo '<div class="error">';
				i18n(BLOGFILE.'/CATEGORY_ERROR');
				echo '</div>';
			}
		}
		if(isset($_GET['delete_category']))
		{
			$Blog->deleteCategory($_GET['delete_category']);
		}
		edit_categories();
	}
	elseif(isset($_GET['auto_importer']) && $blogUserPermissions['blogrssimporter'] == true)
	{
		if(isset($_POST['post-rss']))
		{
			$post_data = array();
			$post_data['name'] = $_POST['post-rss'];
			$post_data['category'] = $_POST['post-category'];
			$add_feed = $Blog->saveRSS($post_data);
			if($add_feed == true)
			{
				echo '<div class="updated">';
				i18n(BLOGFILE.'/FEED_ADDED');
				echo '</div>';
			}
			else
			{
				echo '<div class="error">';
				i18n(BLOGFILE.'/FEED_ERROR');
				echo '</div>';
			}
		}
		elseif(isset($_GET['delete_rss']))
		{
			$delete_feed = $Blog->deleteRSS($_GET['delete_rss']);
			if($delete_feed == true)
			{
				echo '<div class="updated">';
				i18n(BLOGFILE.'/FEED_DELETED');
				echo '</div>';
			}
			else
			{
				echo '<div class="error">';
				i18n(BLOGFILE.'/FEED_DELETE_ERROR');
				echo '</div>';
			}
		}
		edit_rss();
	}
	elseif(isset($_GET['settings']) && $blogUserPermissions['blogsettings'] == true)
	{
		show_settings_admin();
	}
	elseif(isset($_GET['help']) && $blogUserPermissions['bloghelp'] == true)
	{
		show_help_admin();
	}
	elseif(isset($_GET['custom_fields']) && $blogUserPermissions['blogcustomfields'] == true)
	{
		$CustomFields = new customFields;
		if(isset($_POST['save_custom_fields']))
		{
			$saveCustomFields = $CustomFields->saveCustomFields();
			if($saveCustomFields)
			{
				echo '<div class="updated">'.i18n_r(BLOGFILE.'/EDIT_OK').'</div>';
			}
		}
		show_custom_fields();
	}
	else
	{
		if(isset($_GET['save_post']))
		{
			savePost();
		}
		elseif(isset($_GET['delete_post']) && $blogUserPermissions['blogdeletepost'] == true)
		{
			$post_id = urldecode($_GET['delete_post']);
			$delete_post = $Blog->deletePost($post_id);
			if($delete_post == true)
			{
				echo '<div class="updated">';
				i18n(BLOGFILE.'/POST_DELETED');
				echo '</div>';
			}
			else
			{
				echo '<div class="error">';
				i18n(BLOGFILE.'/FEED_DELETE_ERROR');
				echo '</div>';
			}
		}
		show_posts_admin();
	}
}
/** 
* Conditionals to display posts/search/archive/tags/category/importer on front end of website
* 
* @return void
*/  
function blog_display_posts() 
{
	GLOBAL $content, $blogSettings;
	
	$Blog = new Blog;
	$slug = base64_encode(return_page_slug());
	$blogSettings = $Blog->getSettingsData();
	$blog_slug = base64_encode($blogSettings["blogurl"]);
	if($slug == $blog_slug)
	{
		$content = '';
		ob_start();
		if($blogSettings["displaycss"] == 'Y')
		{
			echo "<style>\n";
			echo $blogSettings["csscode"];
			echo "\n</style>";
		}
		switch(true)
		{
			case (isset($_GET['post']) == true) :
				$post_file = BLOGPOSTSFOLDER.$_GET['post'].'.xml';
				show_blog_post($post_file);
				break;
			case (isset($_POST['search_blog']) == true) :
				search_posts($_POST['keyphrase']);
				break;
			case (isset($_GET['archive']) == true) :
				$archive = $_GET['archive'];
				show_blog_archive($archive);
				break;
			case (isset($_GET['tag']) == true) :
				$tag = $_GET['tag'];
				show_blog_tag($tag);
				break;
			case (isset($_GET['category']) == true) :
				$category = $_GET['category'];      
				show_blog_category($category);	
				break;
			case (isset($_GET['import'])) :
				auto_import();
				break;
			default :
				show_all_blog_posts();
				break;
		}
		$content = ob_get_contents();
	    ob_end_clean();		
	}
		return $content; // legacy support for non filter hook calls to this function
}