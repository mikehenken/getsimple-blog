<?php
# get correct id for plugin
$thisfile = basename(__FILE__, ".php");
define('BLOGFILE', $thisfile);

# add in this plugin's language file
i18n_merge($thisfile) || i18n_merge($thisfile, 'en_US');

# register plugin
register_plugin(
	$thisfile, // ID of plugin, should be filename minus php
	i18n_r(BLOGFILE.'/PLUGIN_TITLE'), 	
	'1.0.1', 		
	'Mike Henken',
	'http://michaelhenken.com/', 
	i18n_r(BLOGFILE.'/PLUGIN_DESC'),
	'pages',
	'blog_Admin'  
);

add_action('pages-sidebar','createSideMenu',array($thisfile, i18n_r(BLOGFILE.'/PLUGIN_SIDE')));
add_filter('content', 'blog_display_posts');
define('BLOGSETTINGS', GSDATAOTHERPATH  . 'blog_settings.xml');
define('BLOGCATEGORYFILE', GSDATAOTHERPATH  . 'blog_categories.xml');
define('BLOGRSSFILE', GSDATAOTHERPATH  . 'blog_rss.xml');
define('BLOGPLUGINFOLDER', GSPLUGINPATH.'blog/');
define('BLOGPOSTSFOLDER', GSDATAPATH.'blog/');

//Include Blog class
require_once(BLOGPLUGINFOLDER.'class/Blog.php');

/** 
* Show admin plugin navigation bar
* 
* @return void echos
*/  
function showAdminNav()
{
	?>
	<div style="width:100%;margin:0 -15px -15px -10px;padding:0px;">
		<h3  class="floated"><?php i18n(BLOGFILE.'/PLUGIN_TITLE'); ?></h3>
		<div class="edit-nav clearfix">
			<p>
				<a href="load.php?id=blog&help" <?php echo (isset($_GET['help']) ? 'class="current"' : false); ?>><?php i18n(BLOGFILE.'/HELP'); ?></a>
				<a href="load.php?id=blog&settings" <?php echo (isset($_GET['settings']) ? 'class="current"' : false); ?>><?php i18n(BLOGFILE.'/SETTINGS'); ?></a>
				<a href="load.php?id=blog&auto_importer" <?php echo (isset($_GET['auto_importer']) ? 'class="current"' : false); ?>><?php i18n(BLOGFILE.'/RSS_FEEDS'); ?></a>
				<a href="load.php?id=blog&categories" <?php echo (isset($_GET['categories']) ? 'class="current"' : false); ?>><?php i18n(BLOGFILE.'/CATEGORIES'); ?></a>
				<a href="load.php?id=blog&create_post" <?php echo (isset($_GET['create_post']) ? 'class="current"' : false); ?>><?php i18n(BLOGFILE.'/CREATE_POST'); ?></a>
				<a href="load.php?id=blog&manage" <?php echo (isset($_GET['manage']) ? 'class="current"' : false); ?>><?php i18n(BLOGFILE.'/MANAGE_POSTS'); ?></a>
			</p>
		</div>
	</div>
	</div>
	<div class="main" style="margin-top:-10px;">
	<?php
}

/** 
* Handles conditionals for admin functions
* 
* @return void
*/  
function blog_admin()
{
	$Blog = new Blog;
	showAdminNav();

	if(isset($_GET['edit_post']))
	{
		editPost($_GET['edit_post']);
	}
	elseif(isset($_GET['create_post']))
	{
		editPost();
	}
	elseif(isset($_GET['categories']))
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
	elseif(isset($_GET['auto_importer']))
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
	elseif(isset($_GET['settings']))
	{
		show_settings_admin();
	}
	elseif(isset($_GET['help']))
	{
		show_help_admin();
	}
	else
	{
		if(isset($_GET['save_post']))
		{
			savePost();
		}
		elseif(isset($_GET['delete_post']))
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
* Shows blog posts in admin panel
* 
* @return void
*/  
function show_posts_admin()
{
	$Blog = new Blog;
	$all_posts = $Blog->listPosts();
	if($all_posts == false)
	{
		echo '<strong>'.i18n_r(BLOGFILE.'/NO_POSTS').'. <a href="load.php?id=blog&create_post">'.i18n_r(BLOGFILE.'/CLICK_TO_CREATE').'</a>';
	}
	else
	{
		?>
		<table class="edittable highlight paginate">
			<tr>
				<th><?php i18n(BLOGFILE.'/PAGE_TITLE'); ?></th>
				<th style="text-align:right;" ><?php i18n(BLOGFILE.'/DATE'); ?></th>
				<th></th>
			</tr>
		<?php
		foreach($all_posts as $post_name)
		{
			$post = $Blog->getPostData($post_name);
			?>
				<tr>
					<td class="blog_post_title"><a title="Edit Page: Agents" href="load.php?id=blog&edit_post=<?php echo $post->slug; ?>" ><?php echo $post->title; ?></a></td>
					<td style="text-align:right;"><span><?php echo $post->date; ?></span></td>
					<td class="delete" ><a class="delconfirm" href="load.php?id=blog&delete_post=<?php echo $post->slug; ?>" title="Delete Post: <?php echo $post->title; ?>" >X</a></td>
				</tr>
			<?php
		}
		echo '</table>';
	}
}

/** 
* Settings panel for admin area
* 
* @return void
*/  
function show_settings_admin()
{
	$Blog = new Blog;
	if(isset($_POST['blog_settings']))
	{
		$prettyurls = isset($_POST['pretty_urls']) ? $_POST['pretty_urls'] : '';
		$Blog->saveSettings($_POST['blog_url'], $_POST['language'], $_POST['excerpt_length'], $_POST['show_excerpt'], $_POST['posts_per_page'], $_POST['recent_posts'], $prettyurls, $_POST['auto_importer'], $_POST['auto_importer_pass'], $_POST['show_tags'], $_POST['rss_title'], $_POST['rss_description']);
	}
	?>
	<h3><?php i18n(BLOGFILE.'/BLOG_SETTINGS'); ?></h3>
	<form class="largeform" action="load.php?id=blog&settings" method="post" accept-charset="utf-8">
		<div class="leftsec">
			<p>
				<label for="page-url"><?php i18n(BLOGFILE.'/PAGE_URL'); ?>:</label>
				<select class="text" name="blog_url">
					<?php
					$pages = get_available_pages();
					foreach ($pages as $page) 
					{
						$slug = $page['slug'];
						if ($slug == $Blog->getSettingsData("blogurl"))
						{
							echo "<option value=\"$slug\" selected=\"selected\">$slug</option>\n";
						}
						else
						{
							echo "<option value=\"$slug\">$slug</option>\n";	
						}
					}
					?>
				</select>
			</p>
		</div>
		<div class="rightsec">
			<p>
				<label for="language"><?php i18n(BLOGFILE.'/LANGUAGE'); ?></label>
				<select class="text" name="language">
					<?php
					$languages = $Blog->blog_get_languages();
					foreach ($languages as $lang) 
					{
						if ($key == $Blog->getSettingsData("lang"))
						{
							echo '<option value="'.$lang.'" selected="selected">'.$lang.'</option>';
						}
						else
						{
							echo '<option value="'.$lang.'">'.$lang.'</option>';
						}
					}
					?>
				</select>
			</p>
		</div>
		<div class="clear"></div>
		<div class="leftsec">
			<p>
				<label for="excerpt_length"><?php i18n(BLOGFILE.'/EXCERPT_LENGTH'); ?>:</label>
				<input class="text" type="text" name="excerpt_length" value="<?php echo $Blog->getSettingsData("excerptlength"); ?>" />
			</p>
		</div>
		<div class="rightsec">
			<p>
				<label for="show_excerpt"><?php i18n(BLOGFILE.'/EXCERPT_OPTION'); ?>:</label>
				<input name="show_excerpt" type="radio" value="Y" <?php if ($Blog->getSettingsData("postformat") == 'Y') echo 'checked="checked"'; ?> style="vertical-align: middle;" />
				&nbsp;<?php i18n(BLOGFILE.'/FULL_TEXT'); ?>
				<span style="margin-left: 30px;">&nbsp;</span>
				<input name="show_excerpt" type="radio" value="N" <?php if ($Blog->getSettingsData("postformat") != 'Y') echo 'checked="checked"'; ?> style="vertical-align: middle;" />
				&nbsp;<?php i18n(BLOGFILE.'/EXCERPT'); ?>
			</p>
		</div>
		<div class="clear"></div>
		<div class="leftsec">
			<p>
				<label for="posts_per_page"><?php i18n(BLOGFILE.'/POSTS_PER_PAGE'); ?>:</label>
				<input class="text" type="text" name="posts_per_page" value="<?php echo $Blog->getSettingsData("postperpage"); ?>" />
			</p>
		</div>
		<div class="rightsec">
			<p>
				<label for="recent_posts"><?php i18n(BLOGFILE.'/RECENT_POSTS'); ?>:</label>
				<input class="text" type="text" name="recent_posts" value="<?php echo $Blog->getSettingsData("recentposts"); ?>" />
			</p>
		</div>
		<div class="clear"></div>
		<div class="leftsec">
			<p>
				<label for="auto_importer"><?php i18n(BLOGFILE.'/RSS_IMPORTER'); ?>:</label>
				<input name="auto_importer" type="radio" value="Y" <?php if ($Blog->getSettingsData("autoimporter") == 'Y') echo 'checked="checked"'; ?> style="vertical-align: middle;" />
				&nbsp;<?php i18n(BLOGFILE.'/YES'); ?>
				<span style="margin-left: 30px;">&nbsp;</span>
				<input name="auto_importer" type="radio" value="N" <?php if ($Blog->getSettingsData("autoimporter") != 'Y') echo 'checked="checked"'; ?> style="vertical-align: middle;" />
				&nbsp;<?php i18n(BLOGFILE.'/NO'); ?>
			</p>
		</div>
		<div class="rightsec">
			<p>
				<label for="recent_posts"><?php i18n(BLOGFILE.'/RSS_IMPORTER_PASS'); ?>:</label>
				<input class="text" type="text" name="auto_importer_pass" value="<?php echo $Blog->getSettingsData("autoimporterpass"); ?>" />
			</p>
		</div>
		<div class="clear"></div>
		<div class="leftsec">
			<p>
				<label for="posts_per_page"><?php i18n(BLOGFILE.'/DISPLAY_TAGS_UNDER_POST'); ?>:</label>
				<input name="show_tags" type="radio" value="Y" <?php if ($Blog->getSettingsData("displaytags") == 'Y') echo 'checked="checked"'; ?> style="vertical-align: middle;" />
				&nbsp;<?php i18n(BLOGFILE.'/YES'); ?>
				<span style="margin-left: 30px;">&nbsp;</span>
				<input name="show_tags" type="radio" value="N" <?php if ($Blog->getSettingsData("displaytags") != 'Y') echo 'checked="checked"'; ?> style="vertical-align: middle;" />
				&nbsp;<?php i18n(BLOGFILE.'/NO'); ?>
			</p>
		</div>
		<div class="rightsec">
			<p>
				<label for="posts_per_page"><?php i18n(BLOGFILE.'/DISPLAY_TAGS_UNDER_POST'); ?>:</label>
				<input name="show_tags" type="radio" value="Y" <?php if ($Blog->getSettingsData("displaytags") == 'Y') echo 'checked="checked"'; ?> style="vertical-align: middle;" />
				&nbsp;<?php i18n(BLOGFILE.'/YES'); ?>
				<span style="margin-left: 30px;">&nbsp;</span>
				<input name="show_tags" type="radio" value="N" <?php if ($Blog->getSettingsData("displaytags") != 'Y') echo 'checked="checked"'; ?> style="vertical-align: middle;" />
				&nbsp;<?php i18n(BLOGFILE.'/NO'); ?>
			</p>
		</div>
		<div class="clear"></div>
		<div class="leftsec">
			<p>
				<label for="rss_title"><?php i18n(BLOGFILE.'/RSS_TITLE'); ?>:</label>
				<input class="text" type="text" name="rss_title" value="<?php echo $Blog->getSettingsData("rsstitle"); ?>" />
			</p>
		</div>
		<div class="rightsec">
			<p>
				<label for="rss_description"><?php i18n(BLOGFILE.'/RSS_DESCRIPTION'); ?>:</label>
				<input class="text" type="text" name="rss_description" value="<?php echo $Blog->getSettingsData("rssdescription"); ?>" />
			</p>
		</div>
		<div class="clear"></div>
		<?php global $PRETTYURLS; if ($PRETTYURLS == 1) { ?>
			<p class="inline">
				<input name="pretty_urls" type="checkbox" value="Y" <?php if ($Blog->getSettingsData("prettyurls") == 'Y') echo 'checked'; ?> />&nbsp;
				<label for="pretty_urls"><?php i18n(BLOGFILE.'/PRETTY_URLS'); ?></label> - <span style="color:red;font-weight:bold;">Not Working in v1.0!</span> - 
				<span class="hint"><?php i18n(BLOGFILE.'/PRETTY_URLS_PARA'); ?><span><</span>
			</p>
		<?php } ?>
		<p>
		<span>
		<input class="submit" type="submit" name="blog_settings" value="<?php i18n(BLOGFILE.'/SAVE_SETTINGS'); ?>" />
		</span>
		&nbsp;&nbsp;<?php i18n(BLOGFILE.'/OR'); ?>&nbsp;&nbsp;
		<a href="load.php?id=blog&cancel" class="cancel"><?php i18n(BLOGFILE.'/CANCEL'); ?></a>
		</p>
	</form>
	<h3><?php i18n(BLOGFILE.'/AUTO_IMPORTER_TITLE'); ?></h3>
	<p>
		<?php i18n(BLOGFILE.'/AUTO_IMPORTER_DESC'); ?>
	</p>
<?php
}

/** 
* Edit/Create post screen
* 
* @param $post_id string the id of the post to edit. Null if creating new page
* @return void
*/  
function editPost($post_id=null)
{
	global $SITEURL;
	$Blog = new Blog;
	if($post_id != null)
	{
		$blog_data = getXML(BLOGPOSTSFOLDER.$post_id.'.xml');
	}
	else
	{
		$blog_data = $Blog->getXMLnodes();
	}
	?>
	<h3 class="floated">
	  <?php
	  if ($post_id == null)
	  {
	  	i18n(BLOGFILE.'/ADD_P');
	  }
	  else
	  {
	  	i18n(BLOGFILE.'/EDIT');
	  }
	  ?>
	</h3>
	<div class="edit-nav" >
		<?php
		if ($post_id != null && file_exists(BLOGPOSTSFOLDER.$blog_data->slug.'.xml')) 
		{
			$url = $Blog->get_blog_url('post');
			?>
			<a href="<?php echo $url.$blog_data->slug; ?>" target="_blank">
				<?php i18n(BLOGFILE.'/VIEW'); echo ' '; i18n(BLOGFILE.'/POST'); ?>
			</a>
			<?php
		}
		?>
		<a href="#" id="metadata_toggle">
			<?php i18n(BLOGFILE.'/POST_OPTIONS'); ?>
		</a>
		<div class="clear"></div>
	</div>
	<form class="largeform" action="load.php?id=blog&save_post" method="post" accept-charset="utf-8">
	<?php if($post_id != null) { echo "<p><input name=\"post-current_slug\" type=\"hidden\" value=\"$blog_data->slug\" /></p>"; } ?>
	<div id="metadata_window" style="display:none;text-align:left;">
		<div class="leftopt">
			<p>
				<label for="post-slug"><?php i18n(BLOGFILE.'/POST_SLUG'); ?>:</label>
				<input class="text short" id="post-slug" name="post-slug" type="text" value="<?php echo $blog_data->slug; ?>" />
			</p>
			<p>
				<label for="post-date"><?php i18n(BLOGFILE.'/POST_DATE'); ?>:</label>
				<input class="text short" id="post-date" name="post-date" type="text" value="<?php echo $blog_data->date; ?>" style="width: 60%;" />
				<input class="text short" id="post-time" name="post-time" type="text" value="<?php echo $blog_data->time; ?>" style="width: 30%; float:right;" />
			</p>
		</div>
		<div class="rightopt">
			<p>
				<label for="post-tags"><?php i18n(BLOGFILE.'/POST_TAGS'); ?>:</label>
				<input class="text short" id="post-tags" name="post-tags" type="text" value="<?php echo $blog_data->tags; ?>" />
			</p>
			<p class="inline" id="post-private-wrap">
				<br />
				<label for="post-private"><?php i18n(BLOGFILE.'/POST_PRIVATE'); ?>:</label>
				&nbsp;&nbsp;
				<input type="checkbox" id="post-private" name="post-private" <?php echo $blog_data->private; ?> />
			</p>
		</div>
		<div class="clear"></div>
		<div class="leftopt">     
			<p>
			<label for="post-category"><?php i18n(BLOGFILE.'/POST_CATEGORY'); ?>:</label>		
			<select class="text" name="post-category">	
				<?php category_dropdown($blog_data->category); ?>
			</select>
			</p>
		</div>
		<div class="clear"></div>
		</div>
		<p>
			<input class="text title" name="post-title" id="post-title" type="text" value="<?php echo $blog_data->title; ?>" />
		</p>
		<p>
			<textarea name="post-content"><?php echo $blog_data->content; ?></textarea>
		</p>
		<p>
			<input name="post" type="submit" class="submit" value="<?php i18n(BLOGFILE.'/SAVE_POST'); ?>" />
			&nbsp;&nbsp;<?php i18n(BLOGFILE.'/OR'); ?>&nbsp;&nbsp;
			<a href="load.php?id=news_manager&cancel" class="cancel"><?php i18n(BLOGFILE.'/CANCEL'); ?></a>
			<?php
			if ($post_id != null) 
			{
				?>
				/
				<a href="load.php?id=blog" class="cancel">
					<?php i18n(BLOGFILE.'/DELETE'); ?>
				</a>
				<?php
			}
			?>
		</p>
	</form>
	<script>
	  $(document).ready(function(){
	    $("#post-title").focus();
	  });
	</script>
	<?php
	include BLOGPLUGINFOLDER."ckeditor.php";
}

/** 
* Show Category management area
* 
* @return void
*/  
function edit_categories()
{
	  $category_file = getXML(BLOGCATEGORYFILE);
?>
	<h3><?php i18n(BLOGFILE.'/MANAGE_CATEGORIES'); ?></h3>
	<form class="largeform" action="load.php?id=blog&categories&edit_category" method="post">
	  <div class="leftsec">
	    <p>
	      <label for="page-url"><?php i18n(BLOGFILE.'/ADD_CATEGORY'); ?></label>
		  <input class="text" type="text" name="new_category" value="" />
	    </p>
	  </div>
	  <div class="clear"></div>
	  <table class="highlight">
	  <tr>
	  <th><?php i18n(BLOGFILE.'/CATEGORY_NAME'); ?></th><th><?php i18n(BLOGFILE.'/DELETE'); ?></th>
	  </tr>
	  <?php
	foreach($category_file->category as $category)
	{
	echo '
	<tr><td>'.$category.'</td><td><a href="load.php?id=blog&categories&delete_category='.$category.'">X</a></td></tr>
	';
	}
	  ?>
	  </table>
	  <p>
	    <span>
	      <input class="submit" type="submit" name="category_edit" value="Add Category" />
	    </span>
	    &nbsp;&nbsp;<?php i18n(BLOGFILE.'/OR'); ?>&nbsp;&nbsp;
	    <a href="load.php?id=blog" class="cancel"><?php i18n(BLOGFILE.'/CANCEL'); ?></a>
	  </p>
	</form>
<?php
}

/** 
* RSS Feed management area
* 
* @return void
*/  
function edit_rss()
{
	  $rss_file = getXML(BLOGRSSFILE);
?>
	<h3 class="floated"><?php i18n(BLOGFILE.'/MANAGE_FEEDS'); ?></h3>
	<div class="edit-nav" >
		<a href="#" id="metadata_toggle">
			<?php i18n(BLOGFILE.'/ADD_FEED'); ?>
		</a>
	</div>
	  <div class="clear"></div>
	<div id="metadata_window" style="display:none;text-align:left;">
		<form class="largeform" action="load.php?id=blog&auto_importer&add_rss" method="post">
		    <p style="float:left;width:150px;clear:both">
		      <label for="page-url"><?php i18n(BLOGFILE.'/ADD_NEW_FEED'); ?></label>
			  <input class="text" type="text" name="post-rss" value="" style="padding-bottom:5px;" />
		    </p>
		    <p style="float:left;width:100px;margin-left:20px;">
		    	<label for="page-url"><?php i18n(BLOGFILE.'/BLOG_CATEGORY'); ?></label>
				<select class="text" name="post-category">	
					<?php category_dropdown($blog_data->category); ?>
				</select>
		    </p>
		    <p style="float:left;width:200px;margin-left:0px;clear:both">
		    <span>
		      <input class="submit" type="submit" name="rss_edit" value="Add RSS" style="width:auto;" />
		    </span>
		    &nbsp;&nbsp;<?php i18n(BLOGFILE.'/OR'); ?>&nbsp;&nbsp;
		    <a href="load.php?id=blog" class="cancel"><?php i18n(BLOGFILE.'/CANCEL'); ?></a>
		  </p>
		</form>
	</div>
	  <div class="clear"></div>
	  <table class="highlight">
	  <tr>
	  <th><?php i18n(BLOGFILE.'/RSS_FEED'); ?></th><th><?php i18n(BLOGFILE.'/FEED_CATEGORY'); ?></th><th><?php i18n(BLOGFILE.'/DELETE_FEED'); ?></th>
	  </tr>
	  <?php
	foreach($rss_file->rssfeed as $feed)
	{
		$rss_atts = $feed->attributes();
	echo '
	<tr><td>'.$feed->feed.'</td><td>'.$feed->category.'</td><td><a href="load.php?id=blog&auto_importer&delete_rss='.$feed['id'].'">X</a></td></tr>
	';
	}
	  ?>
	  </table>
<?php
}

/** 
* Echos all categories to place into select menu
* 
* @return void
*/  
function category_dropdown($current_category=null)
{
	$category_file = getXML(BLOGCATEGORYFILE);	
	$current_category = to7bit($current_category, 'UTF-8');
	foreach($category_file->category as $category_item)	
	{		
		$category_item = to7bit($category_item, 'UTF-8');
		if($category_item == $current_category)
		{
			echo '<option value="'.$current_category.'" selected>'.$current_category.'</option>';	
		}
		else
		{
			echo '<option value="'.$category_item.'">'.$category_item.'</option>';	
		}	
	}	
	if($current_category == null)
	{
		echo '<option value="" selected></option>';	
	}
	else
	{
		echo '<option value=""></option>';	
	}
}

/** 
* Saves A Post
* 
* @return void success or error message
*/  
function savePost()
{
	$Blog = new Blog;
	$xmlNodes = $Blog->getXMLnodes(true);
	foreach($xmlNodes as $key => $value)
	{
		if(!isset($_POST["post-".$key]))
		{
			$post_value = '';
		}
		else
		{
			$post_value = $_POST["post-".$key];
		}
		$post_data[$key] = $post_value;
	}
	$savePost = $Blog->savePost($post_data);
	$generateRSS = $Blog->generateRSSFeed();
	if($savePost != false)
	{
		echo '<div class="updated">';
		i18n(BLOGFILE.'/POST_ADDED');
		echo '</div>';
	}
	else
	{
		echo '<div class="error">';
		i18n(BLOGFILE.'/POST_ERROR');
		echo '</div>';
	}
}

/** 
* Conditionals to display posts/search/archive/tags/category/importer on front end of website
* 
* @return void
*/  
function blog_display_posts($content) 
{
	$Blog = new Blog;
	$slug = base64_encode(return_page_slug());
	$blog_slug = base64_encode($Blog->getSettingsData("blogurl"));
	if($slug == $blog_slug)
	{
		if(isset($_GET['post']))
		{
			$post_file = BLOGPOSTSFOLDER.$_GET['post'].'.xml';
			show_blog_post($post_file);
		}
		elseif (isset($_POST['search_blog'])) 
		{
			search_posts($_POST['keyphrase']);
		} 
		elseif (isset($_GET['archive'])) 
		{
			$archive = $_GET['archive'];
			show_blog_archive($archive);
		} 
		elseif(isset($_GET['tag'])) 
		{
			$tag = $_GET['tag'];
			show_blog_tag($tag);
		} 
		elseif (isset($_GET['category'])) 
		{      
			$category = $_GET['category'];      
			show_blog_category($category);	 
		}    
		elseif(isset($_GET['import']))
		{
			auto_import();
		}
		else 
		{
			show_all_blog_posts();
		}
	}
	else
	{
		return $content;
	}
}

/** 
* show individual blog post
* 
* @param $slug slug of post to display
* @param $excerpt bool Whether an excerpt should be displayed. It would be false or null if a user was on the blog details page rather then a results or list all page
* @return void
*/  
function show_blog_post($slug, $excerpt=false)
{
	$Blog = new Blog;
	global $SITEURL;
	$post = getXML($slug);
	$url = $Blog->get_blog_url('post').$post->slug;
	?>
	<h3 class="blog_post_title"><a href="<?php echo $url; ?>" class="blog_post_link"><?php echo $post->title; ?></a></h3>
	<p class="blog_post_content">
		<?php
		if($excerpt == false || $excerpt == true && $Blog->getSettingsData("postformat") == "Y")
		{
			echo html_entity_decode($post->content);
		}
		else
		{
			if($excerpt == true && $Blog->getSettingsData("postformat") == "N")
			{
				if($Blog->getSettingsData("excerptlength") == '')
				{
					$excerpt_length = 250;
				}
				else
				{
					$excerpt_length = $Blog->getSettingsData("excerptlength");
				}
				echo $Blog->create_excerpt(html_entity_decode($post->content), 0, $excerpt_length);
			}
		}
		if(isset($_GET['post']))
		{
			echo '<p class="blog_go_back"><a href="javascript:history.back()">&lt;&lt; '.i18n_r(BLOGFILE.'/GO_BACK').'</a></p>';
		}
		?>
	</p>
	<?php
	if(!empty($post->tags) && $Blog->getSettingsData("displaytags") != 'N')
	{
		$tag_url = $Blog->get_blog_url('tag').$post->slug;
		$tags = explode(",", $post->tags);
		?>
		<p class="blog_tags"><b><?php i18n(BLOGFILE.'/TAGS'); ?> :</b> 
		<?php
		foreach($tags as $tag)
		{
			echo '<a href="'.$tag_url.$tag.'">'.$tag.'</a> ';
		}
		echo  '</p>';
	}
	?>
	<?php
}

/** 
* Shows blog categories list
* 
* @return void
*/  
function show_blog_categories()
{
	$Blog = new Blog;
	$categories = getXML(BLOGCATEGORYFILE);
	$url = $Blog->get_blog_url();
	foreach($categories as $category)
	{
		echo '<li><a href="'.$url.'?category='.$category.'">'.$category.'</a></li>';
	}
	echo '<li><a href="'.$url.'">';
	i18n(BLOGFILE.'/ALL_CATEOGIRES');
	echo '</a></li>';
}

/** 
* Shows posts from a requested category
* 
* @param $category the category to show posts from
* @return void
*/  
function show_blog_category($category)
{
	$Blog = new Blog;
	$all_posts = $Blog->listPosts();
	$count = 0;
	foreach($all_posts as $file)
	{
		$data = getXML($file);
		if($data->category == $category)
		{
			$count++;
			show_blog_post($file, true);
		}
	}
	if($count < 1)
	{
		echo '<p class="blog_category_noposts">'.i18n_r(BLOGFILE.'/NO_POSTS').'</p>';
	}
}

/** 
* Show blog search bar
* 
* @return void
*/  
function show_blog_search()
{
	$Blog = new Blog;
	$url = $Blog->get_blog_url();
	?>
	<form id="blog_search" action="<?php echo $url; ?>" method="post">
		<input type="text" class="text" name="keyphrase" />
		<input type="submit" class="submit" name="search_blog" value="<?php i18n(BLOGFILE.'/SEARCH'); ?>" />
	</form>
	<?php
}

/** 
* Show Blog archives list
* 
* @return void
*/  
function show_blog_archives()
{
	$Blog = new Blog;
	$posts = $Blog->listPosts();
	$archives = $Blog->get_blog_archives();
	if (!empty($archives)) 
	{
		echo '<ul>';
		foreach ($archives as $archive=>$title) 
		{
			$url = $Blog->get_blog_url('archive') . $archive;
			echo "<li><a href=\"$url\">$title</a></li>";
		}
		echo '</ul>';
	}
}

/** 
* Show Posts from requested archive
* 
* @return void
*/  
function show_blog_archive($archive)
{
	$Blog = new Blog;
	$posts = $Blog->listPosts();
	foreach ($posts as $file) 
	{
		$data = getXML($file);
		$date = strtotime($data->date);
		if (date('Ym', $date) == $archive)
		{
			show_blog_post($file, true);
		}
	}
}

/** 
* Show recent posts list
* 
* @return void
*/  
function show_blog_recent_posts()
{
	$Blog = new Blog;
	$posts = $Blog->listPosts();
	if (!empty($posts)) 
	{
		echo '<ul>';
		$posts = array_slice($posts, 0, $Blog->getSettingsData("recentposts"), TRUE);
		foreach ($posts as $file) 
		{
			$data = getXML($file);
			$url = $Blog->get_blog_url('post') . $data->slug;
			$title = strip_tags(strip_decode($data->title));
			echo "<li><a href=\"$url\">$title</a></li>";
		}
		echo '</ul>';
	}
}

/** 
* Show posts for requested tag
* 
* @return void
*/  
function show_blog_tag($tag)
{
	$Blog = new Blog;
	$all_posts = $Blog->listPosts();
	foreach ($all_posts as $file) 
	{
		$data = getXML($file);
		$tags = explode(',', $data->tags);
		if (in_array($tag, $tags))
		{
			show_blog_post($file, true);	
		}
	}
}

/** 
* Show all postts
* 
* @return void
*/  
function show_all_blog_posts()
{
	$Blog = new Blog;
	$all_posts = $Blog->listPosts();
	if(isset($_GET['page']))
	{
		$page = $_GET['page'];
	}
	else
	{
		$page = 0;
	}
	show_posts_page($page);
}

/** 
* Display blog posts results from a search
* 
* @return void
*/  
function search_posts($keyphrase)
{
	$Blog = new Blog;
	$posts = $Blog->searchPosts($keyphrase);
	if (!empty($posts)) 
	{
		echo '<p class="blog_search_header">';
			i18n(BLOGFILE.'/FOUND');
		echo '</p>';
		foreach ($posts as $file)
		{
			show_blog_post($file, TRUE);
		}
	} 
	else 
	{
		echo '<p class="blog_search_header">';
			i18n(BLOGFILE.'/NOT_FOUND');
		echo '</p>';
	}
}

/** 
* RSS Feed Auto Importer
* Auto imports RSS feeds. Can be launched by a cron job 
* 
* @return void
*/  
function auto_import()
{
	$Blog = new Blog;
	if($_GET['import'] == urldecode($Blog->getSettingsData("autoimporterpass")) && $Blog->getSettingsData("autoimporter") =='Y')
	{
		ini_set("memory_limit","350M");

		require_once(BLOGPLUGINFOLDER.'magpierss/rss_fetch.inc');

		$rss_feed_file = getXML(BLOGRSSFILE);
		foreach($rss_feed_file->rssfeed as $the_fed)
		{
		    $rss_uri = $the_fed->feed;
		    $rss_category = $the_fed->category;
		        
		    $rss = fetch_rss($rss_uri);
		    $items = array_slice($rss->items, 0);
		    foreach ($items as $item )
		    {
		        $post_data['title']         = $item['title'];
		        $post_data['slug']          = '';
		        $post_data['date']          = $item['pubdate'];
		        $post_data['time']          = '';
		        $post_data['private']       = '';
		        $post_data['tags']          = '';
		        $post_data['category']      = $rss_category;
		        $post_data['content']       = $item['summary'].'<p class="blog_auto_import_readmore"><a href="'.$item['link'].'" target="_blank">'.i18n_r(BLOGFILE.'/READ_FULL_ARTICLE').'</a></p>';
		        $post_data['excerpt']       = '';
		        $post_data['thumbnail']     = '';
		        $post_data['current_slug']  = '';

		        $Blog->savePost($post_data);
		    }
		}
	}
}

/** 
* RSS Feed Auto Importer
* Auto imports RSS feeds. Can be launched by a cron job 
* 
* @return void
*/  
/*******************************************************
 * @function nm_show_page
 * param $index - page index (pagination)
 * @action show posts on news page
 */
function show_posts_page($index=0) 
{
	$Blog = new Blog;
	$posts = $Blog->listPosts();
	if(!empty($posts))
	{
		$pages = array_chunk($posts, intval($Blog->getSettingsData("postperpage")), TRUE);
		if (is_numeric($index) && $index >= 0 && $index < sizeof($pages))
		{
			$posts = $pages[$index];
		}
		else
		{
			$posts = array();	
		}
		$count = 0;
		foreach ($posts as $file)
		{
			$count++;
			show_blog_post($file, true);

			if (sizeof($pages) > 1)
			{
				show_blog_navigation($index, sizeof($pages), $count);
				if($count == $Blog->getSettingsData("postsperpage"))
				{
					$count = 0;
				}
			}
		}
	} 
	else 
	{
	echo '<p>' . i18n(BLOGFILE.'/NO_POSTS') . '</p>';
	}
}

/** 
* Blog posts navigation (pagination)
* 
* @param $index the current page index
* @param $total total number of pages
* @param $count current post
* @return void
*/  
function show_blog_navigation($index, $total, $count) 
{
	$Blog = new Blog;
	$url = $Blog->get_blog_url('page');
	echo '<div class="blog_page_navigation">';
	if ($index < $total && $count >= $Blog->getSettingsData("postperpage")) 
	{
	?>
		<div class="left">
		<a href="<?php echo $url . ($index+1); ?>">
			&larr; <?php i18n(BLOGFILE.'/OLDER_POSTS'); ?>
		</a>
		</div>
		<?php
		if ($index > 0) 
		{
		?>
			<div class="right">
			<a href="<?php echo ($index > 1) ? $url . ($index-1) : substr($url, 0, -6); ?>">
				<?php i18n(BLOGFILE.'/NEWER_POSTS'); ?> &rarr;
			</a>
			</div>
		<?php
		}
	}
	echo '</div>';
}

function show_help_admin()
{
	global $SITEURL; 
	?>
	<h3>
		<?php i18n(BLOGFILE.'/PLUGIN_TITLE'); ?> <?php i18n(BLOGFILE.'/HELP'); ?>
	</h3>

	<h2 style="font-size:16px;"><?php i18n(BLOGFILE.'/FRONT_END_FUNCTIONS'); ?></h2>
	<p>
		<label><?php i18n(BLOGFILE.'/HELP_CATEGORIES'); ?><?php i18n(BLOGFILE.'/RSS_LOCATION'); ?>:</label>
		<?php highlight_string('<?php show_blog_categories(); ?>'); ?>
	</p>
	<p>
		<label><?php i18n(BLOGFILE.'/HELP_SEARCH'); ?>:</label>
		<?php highlight_string('<?php show_blog_search(); ?>'); ?>
	</p>
	<p>
		<label><?php i18n(BLOGFILE.'/HELP_ARCHIVES'); ?>:</label>
		<?php highlight_string('<?php show_blog_archives(); ?>'); ?>
	</p>
	<p>
		<label><?php i18n(BLOGFILE.'/HELP_RECENT'); ?>:</label>
		<?php highlight_string('<?php show_blog_recent_posts(); ?>'); ?>
	</p>
	<p>
		<label><?php i18n(BLOGFILE.'/RSS_LOCATION'); ?> :</label>
		<a href="<?php echo $SITEURL."rss.rss"; ?>" target="_blank"><?php echo $SITEURL."rss.rss"; ?>
	</p>
	<?php
}
